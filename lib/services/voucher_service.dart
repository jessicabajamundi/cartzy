import 'package:flutter/material.dart';

class Voucher {
  final String id;
  final String code;
  final String title;
  final String description;
  final double discountAmount;
  final double minSpend;
  final String tagTop;
  final String tagMain;
  final String tagBottom;
  final Color stubColor;
  final bool isShippingDiscount;
  bool isClaimed;

  Voucher({
    required this.id,
    required this.code,
    required this.title,
    required this.description,
    required this.discountAmount,
    required this.minSpend,
    required this.tagTop,
    required this.tagMain,
    required this.tagBottom,
    this.stubColor = const Color(0xFF2E7D63),
    this.isShippingDiscount = true,
    this.isClaimed = true,
  });
}

class VoucherService extends ChangeNotifier {
  static final VoucherService instance = VoucherService._internal();

  VoucherService._internal() {
    _seedDefaultVouchers();
  }

  final List<Voucher> _vouchers = [];

  List<Voucher> get vouchers => List.unmodifiable(_vouchers);

  int get voucherCount => _vouchers.length;

  void _seedDefaultVouchers() {
    _vouchers.addAll([
      Voucher(
        id: 'shipping-saver',
        code: 'SHIPSAVER50',
        title: 'Shipping Saver',
        description: '₱50 off shipping when your items reach ₱1,000.',
        discountAmount: 50.0,
        minSpend: 1000.0,
        tagTop: 'SHIPPING',
        tagMain: '₱50 OFF',
        tagBottom: 'SHIPPING',
        stubColor: const Color(0xFF2E7D63),
        isShippingDiscount: true,
        isClaimed: true,
      ),
    ]);
  }

  Voucher? getBestApplicableVoucher(double subtotal) {
    if (subtotal.isNaN || subtotal <= 0) return null;
    Voucher? best;
    for (final voucher in _vouchers) {
      if (subtotal >= voucher.minSpend) {
        if (best == null || voucher.discountAmount > best.discountAmount) {
          best = voucher;
        }
      }
    }
    return best;
  }

  (bool success, String message) addVoucherCode(String code) {
    final cleaned = code.trim().toUpperCase();
    if (cleaned.isEmpty) {
      return (false, 'Please enter a voucher code.');
    }

    final exists = _vouchers.any((v) => v.code.toUpperCase() == cleaned);
    if (exists) {
      return (false, 'Voucher code already added.');
    }

    if (cleaned == 'PASKO100') {
      final voucher = Voucher(
        id: 'pasko-100',
        code: 'PASKO100',
        title: 'Pasko Holiday Saver',
        description: '₱100 off on festive orders reaching ₱1,500.',
        discountAmount: 100.0,
        minSpend: 1500.0,
        tagTop: 'HOLIDAY',
        tagMain: '₱100 OFF',
        tagBottom: 'VOUCHER',
        stubColor: const Color(0xFF8B3A3A),
        isShippingDiscount: false,
        isClaimed: true,
      );
      _vouchers.add(voucher);
      notifyListeners();
      return (true, 'Voucher PASKO100 added successfully!');
    } else if (cleaned == 'CARTZY50' || cleaned == 'WELCOME50') {
      final voucher = Voucher(
        id: 'welcome-50',
        code: cleaned,
        title: 'Welcome Cartzy Treat',
        description: '₱50 off your order when you spend ₱500.',
        discountAmount: 50.0,
        minSpend: 500.0,
        tagTop: 'WELCOME',
        tagMain: '₱50 OFF',
        tagBottom: 'VOUCHER',
        stubColor: const Color(0xFF3D3555),
        isShippingDiscount: false,
        isClaimed: true,
      );
      _vouchers.add(voucher);
      notifyListeners();
      return (true, 'Voucher $cleaned added successfully!');
    } else if (cleaned == 'FREESHIP') {
      final voucher = Voucher(
        id: 'free-ship',
        code: 'FREESHIP',
        title: 'Free Shipping Special',
        description: 'Free shipping up to ₱50 on orders reaching ₱800.',
        discountAmount: 50.0,
        minSpend: 800.0,
        tagTop: 'FREE',
        tagMain: '₱50 OFF',
        tagBottom: 'SHIPPING',
        stubColor: const Color(0xFF2E7D63),
        isShippingDiscount: true,
        isClaimed: true,
      );
      _vouchers.add(voucher);
      notifyListeners();
      return (true, 'Free shipping voucher added successfully!');
    } else {
      // General promotional voucher code support
      final voucher = Voucher(
        id: 'custom-${DateTime.now().millisecondsSinceEpoch}',
        code: cleaned,
        title: '$cleaned Special Voucher',
        description: '₱50 promo discount on orders over ₱500.',
        discountAmount: 50.0,
        minSpend: 500.0,
        tagTop: 'PROMO',
        tagMain: '₱50 OFF',
        tagBottom: 'DISCOUNT',
        stubColor: const Color(0xFF523D4D),
        isShippingDiscount: false,
        isClaimed: true,
      );
      _vouchers.add(voucher);
      notifyListeners();
      return (true, 'Voucher $cleaned added to your vouchers!');
    }
  }
}
