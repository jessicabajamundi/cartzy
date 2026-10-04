import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class CartzyProductImage extends StatelessWidget {
  final String? imageUrl;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;
  final IconData fallbackIcon;
  final Color? backgroundColor;

  const CartzyProductImage({
    super.key,
    required this.imageUrl,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
    this.fallbackIcon = Icons.shopping_bag_outlined,
    this.backgroundColor,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveRadius = borderRadius ?? BorderRadius.zero;
    final bgColor = backgroundColor ?? CartzyColors.background;

    final url = imageUrl?.trim() ?? '';

    Widget imageContent;

    if (url.isEmpty) {
      imageContent = _buildFallback(bgColor);
    } else if (url.startsWith('http://') || url.startsWith('https://')) {
      imageContent = Image.network(
        url,
        width: width,
        height: height,
        fit: fit,
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return Container(
            width: width,
            height: height,
            color: bgColor,
            alignment: Alignment.center,
            child: SizedBox(
              width: 24,
              height: 24,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation<Color>(
                  CartzyColors.coral.withValues(alpha: 0.6),
                ),
                value: loadingProgress.expectedTotalBytes != null
                    ? loadingProgress.cumulativeBytesLoaded /
                        loadingProgress.expectedTotalBytes!
                    : null,
              ),
            ),
          );
        },
        errorBuilder: (context, error, stackTrace) {
          return _buildFallback(bgColor);
        },
      );
    } else {
      // Asset image
      imageContent = Image.asset(
        url,
        width: width,
        height: height,
        fit: fit,
        errorBuilder: (context, error, stackTrace) {
          return _buildFallback(bgColor);
        },
      );
    }

    return ClipRRect(
      borderRadius: effectiveRadius,
      child: imageContent,
    );
  }

  Widget _buildFallback(Color bgColor) {
    return Container(
      width: width,
      height: height,
      color: bgColor,
      alignment: Alignment.center,
      child: Icon(
        fallbackIcon,
        color: CartzyColors.coral.withValues(alpha: 0.7),
        size: (width != null && width! < 60) ? 24 : 36,
      ),
    );
  }
}
