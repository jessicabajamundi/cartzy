import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/review_service.dart';

class WriteReviewDialog extends StatefulWidget {
  final String productName;
  final String? orderId;
  final double initialRating;
  final String? initialComment;
  final List<String>? initialTags;
  final String userName;

  const WriteReviewDialog({
    super.key,
    required this.productName,
    this.orderId,
    this.initialRating = 5.0,
    this.initialComment,
    this.initialTags,
    this.userName = 'Buyer',
  });

  static Future<void> show(
    BuildContext context, {
    required String productName,
    String? orderId,
    double initialRating = 5.0,
    String? initialComment,
    List<String>? initialTags,
    String userName = 'Buyer',
  }) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: WriteReviewDialog(
          productName: productName,
          orderId: orderId,
          initialRating: initialRating,
          initialComment: initialComment,
          initialTags: initialTags,
          userName: userName,
        ),
      ),
    );
  }

  @override
  State<WriteReviewDialog> createState() => _WriteReviewDialogState();
}

class _WriteReviewDialogState extends State<WriteReviewDialog> {
  late double _rating;
  late TextEditingController _commentController;
  final Set<String> _selectedTags = {};
  bool _isAnonymous = false;

  static const List<String> _availableTags = [
    'Great Quality',
    'Fast Shipping',
    'Well Packaged',
    'Value for Money',
    'True to Description',
    'Responsive Seller',
    'Highly Recommended',
  ];

  @override
  void initState() {
    super.initState();
    _rating = widget.initialRating;
    _commentController = TextEditingController(text: widget.initialComment ?? '');
    if (widget.initialTags != null) {
      _selectedTags.addAll(widget.initialTags!);
    }
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  String get _ratingLabel {
    if (_rating >= 5.0) return 'Excellent! 🌟';
    if (_rating >= 4.0) return 'Good 😊';
    if (_rating >= 3.0) return 'Fair 😐';
    if (_rating >= 2.0) return 'Poor 🙁';
    return 'Terrible 😠';
  }

  void _handleSubmit() {
    final comment = _commentController.text.trim();
    if (comment.isEmpty && _selectedTags.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please write a short review or select feedback tags.'),
          backgroundColor: CartzyColors.navy,
        ),
      );
      return;
    }

    ReviewService.instance.submitReview(
      orderId: widget.orderId,
      productName: widget.productName,
      rating: _rating,
      comment: comment.isEmpty
          ? _selectedTags.join(', ')
          : comment,
      tags: _selectedTags.toList(),
      isAnonymous: _isAnonymous,
      userName: widget.userName,
    );

    Navigator.of(context).pop();

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Thank you! Your review has been submitted successfully.'),
        backgroundColor: CartzyColors.navy,
        duration: Duration(seconds: 3),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.88,
      ),
      decoration: const BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            margin: const EdgeInsets.only(top: 12, bottom: 8),
            width: 44,
            height: 4,
            decoration: BoxDecoration(
              color: CartzyColors.border,
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Rate & Review Product',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: CartzyColors.gray),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
          ),

          const Divider(height: 1, color: CartzyColors.border),

          // Scrollable Body
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Product Preview Box
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: CartzyColors.background,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: CartzyColors.border),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 48,
                          height: 48,
                          decoration: BoxDecoration(
                            color: CartzyColors.surface,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.shopping_bag_outlined,
                            color: CartzyColors.coral,
                            size: 26,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                widget.productName,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                  color: CartzyColors.navy,
                                ),
                              ),
                              const SizedBox(height: 3),
                              const Text(
                                'Verified Purchase',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: Colors.green,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Rating Header
                  const Center(
                    child: Text(
                      'How was the product?',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.navy,
                      ),
                    ),
                  ),

                  const SizedBox(height: 10),

                  // 5 Star Rating Row
                  Center(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: List.generate(5, (index) {
                        final starNumber = index + 1;
                        final isFilled = starNumber <= _rating;
                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              _rating = starNumber.toDouble();
                            });
                          },
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 5),
                            child: Icon(
                              isFilled ? Icons.star : Icons.star_border,
                              size: 40,
                              color: isFilled ? CartzyColors.gold : CartzyColors.border,
                            ),
                          ),
                        );
                      }),
                    ),
                  ),

                  const SizedBox(height: 6),

                  // Rating Text Label
                  Center(
                    child: Text(
                      _ratingLabel,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.coral,
                      ),
                    ),
                  ),

                  const SizedBox(height: 22),

                  // Quick Tags
                  const Text(
                    'What did you like most?',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),

                  const SizedBox(height: 10),

                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _availableTags.map((tag) {
                      final selected = _selectedTags.contains(tag);
                      return FilterChip(
                        label: Text(tag),
                        selected: selected,
                        onSelected: (isSelected) {
                          setState(() {
                            if (isSelected) {
                              _selectedTags.add(tag);
                            } else {
                              _selectedTags.remove(tag);
                            }
                          });
                        },
                        selectedColor: CartzyColors.coral.withValues(alpha: 0.15),
                        checkmarkColor: CartzyColors.coral,
                        labelStyle: TextStyle(
                          fontSize: 12,
                          color: selected ? CartzyColors.coral : CartzyColors.navy,
                          fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                        ),
                        backgroundColor: CartzyColors.surface,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                          side: BorderSide(
                            color: selected ? CartzyColors.coral : CartzyColors.border,
                          ),
                        ),
                      );
                    }).toList(),
                  ),

                  const SizedBox(height: 20),

                  // Comment Input
                  const Text(
                    'Write your detailed review',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),

                  const SizedBox(height: 8),

                  TextField(
                    controller: _commentController,
                    maxLines: 4,
                    decoration: InputDecoration(
                      hintText: 'Describe your experience with this product, its quality, packaging, and usability...',
                      hintStyle: const TextStyle(fontSize: 13, color: CartzyColors.gray),
                      filled: true,
                      fillColor: CartzyColors.background,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: CartzyColors.border),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: CartzyColors.border),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: CartzyColors.coral, width: 1.5),
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Anonymous toggle
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.visibility_off_outlined, size: 20, color: CartzyColors.gray),
                          SizedBox(width: 8),
                          Text(
                            'Review Anonymously',
                            style: TextStyle(
                              fontSize: 13,
                              color: CartzyColors.navy,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                      Switch(
                        value: _isAnonymous,
                        activeColor: CartzyColors.coral,
                        onChanged: (val) {
                          setState(() {
                            _isAnonymous = val;
                          });
                        },
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),

                  // Submit Button
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton.icon(
                      onPressed: _handleSubmit,
                      icon: const Icon(Icons.rate_review, size: 20),
                      label: const Text(
                        'Submit Review',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: CartzyColors.coral,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
