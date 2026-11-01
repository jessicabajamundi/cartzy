import 'dart:convert';

import 'package:http/http.dart' as http;

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

/// A product Cozy recommended in a chat bubble.
class BuddyProduct {
  final int id;
  final String name;
  final double? price;
  final double? regularPrice;
  final String image;
  final double? rating;
  final int sold;
  final bool inStock;
  final String category;
  final String store;

  const BuddyProduct({
    required this.id,
    required this.name,
    this.price,
    this.regularPrice,
    required this.image,
    this.rating,
    required this.sold,
    required this.inStock,
    required this.category,
    required this.store,
  });

  factory BuddyProduct.fromJson(Map<String, dynamic> json) => BuddyProduct(
        id: (json['id'] as num).toInt(),
        name: json['name']?.toString() ?? '',
        price: (json['price'] as num?)?.toDouble(),
        regularPrice: (json['regular_price'] as num?)?.toDouble(),
        image: ApiService.normalizeImageUrl(json['image']?.toString()),
        rating: (json['rating'] as num?)?.toDouble(),
        sold: (json['sold'] as num?)?.toInt() ?? 0,
        inStock: json['in_stock'] != false,
        category: json['category']?.toString() ?? '',
        store: json['store']?.toString() ?? '',
      );
}

class BuddyMessage {
  final bool fromUser;
  final String text;
  final List<BuddyProduct> products;

  const BuddyMessage({
    required this.fromUser,
    required this.text,
    this.products = const [],
  });
}

class BuddyReply {
  final String text;
  final String mood;
  final List<String> suggestions;
  final List<BuddyProduct> products;

  const BuddyReply({
    required this.text,
    required this.mood,
    required this.suggestions,
    required this.products,
  });

  factory BuddyReply.fromJson(Map<String, dynamic> json) => BuddyReply(
        text: json['reply']?.toString() ?? '',
        mood: json['mood']?.toString() ?? 'happy',
        suggestions: (json['suggestions'] as List? ?? const [])
            .map((s) => s.toString())
            .toList(),
        products: (json['products'] as List? ?? const [])
            .map((p) => BuddyProduct.fromJson(Map<String, dynamic>.from(p as Map)))
            .toList(),
      );
}

class BuddyService {
  /// Cozy may look things up across several steps before answering, which can
  /// outlast [ApiService]'s shorter default timeout.
  static const Duration _timeout = Duration(seconds: 90);

  static final List<BuddyMessage> _messages = [];
  static String? _chatOwner;

  /// The chat so far. Kept while the app runs, and per account, so it doesn't
  /// carry over to someone else who signs in on the same phone.
  static List<BuddyMessage> get messages {
    final owner = AuthService.currentUser?['id']?.toString() ?? AuthService.token ?? 'guest';
    if (owner != _chatOwner) {
      _messages.clear();
      _chatOwner = owner;
    }
    return _messages;
  }

  static void reset() => messages.clear();

  /// Sends the chat so far; the last entry must be the buyer's new message.
  static Future<BuddyReply> send() async {
    final token = AuthService.token;
    final response = await http
        .post(
          Uri.parse('${ApiService.baseUrl}/buddy/chat'),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
          },
          body: jsonEncode({
            'messages': [
              for (final m in messages)
                {'role': m.fromUser ? 'user' : 'assistant', 'content': m.text},
            ],
          }),
        )
        .timeout(_timeout);

    if (response.statusCode == 429) {
      throw ApiException(429, 'Too many messages');
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw ApiException(response.statusCode, 'Cozy could not reply');
    }
    return BuddyReply.fromJson(Map<String, dynamic>.from(jsonDecode(response.body) as Map));
  }
}
