/// Fragrance bottle image with graceful placeholder and error fallback.
/// Images are served from Laravel Storage (cached locally during import).
/// The URL is built from [ApiEndpoints.storageBaseUrl] + the relative path
/// returned in [FragranceSummary.imageUrl].
/// If the URL is null or the request fails, renders a minimal neumorphic
/// placeholder with the brand initial.

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../app/theme.dart';

class FragranceImage extends StatelessWidget {
  const FragranceImage({
    super.key,
    required this.imageUrl,
    this.name = '',
    this.size = 72,
    this.borderRadius = 12.0,
  });

  /// Full URL to the image. If null a placeholder is shown.
  final String? imageUrl;

  /// Fragrance name — used to derive the placeholder initial.
  final String name;

  /// Width and height of the square image widget.
  final double size;

  final double borderRadius;

  @override
  Widget build(BuildContext context) {
    final neu    = context.neu;
    final radius = BorderRadius.circular(borderRadius);

    if (imageUrl == null || imageUrl!.isEmpty) {
      return _Placeholder(
        name:     name,
        size:     size,
        radius:   radius,
        neu:      neu,
        context:  context,
      );
    }

    return ClipRRect(
      borderRadius: radius,
      child: CachedNetworkImage(
        imageUrl:    imageUrl!,
        width:       size,
        height:      size,
        fit:         BoxFit.cover,
        placeholder: (_, __) => _Placeholder(
          name:    name,
          size:    size,
          radius:  radius,
          neu:     neu,
          context: context,
          loading: true,
        ),
        errorWidget: (_, __, ___) => _Placeholder(
          name:    name,
          size:    size,
          radius:  radius,
          neu:     neu,
          context: context,
        ),
      ),
    );
  }
}

class _Placeholder extends StatelessWidget {
  const _Placeholder({
    required this.name,
    required this.size,
    required this.radius,
    required this.neu,
    required this.context,
    this.loading = false,
  });

  final String name;
  final double size;
  final BorderRadius radius;
  final NeuTheme neu;
  final BuildContext context;
  final bool loading;

  @override
  Widget build(BuildContext _) {
    final initial = name.isNotEmpty ? name[0].toUpperCase() : '·';

    return Container(
      width:  size,
      height: size,
      decoration: BoxDecoration(
        color:        neu.cardColor,
        borderRadius: radius,
        boxShadow:    neu.raisedSm,
      ),
      child: Center(
        child: loading
            ? SizedBox(
                width:  size * 0.28,
                height: size * 0.28,
                child:  CircularProgressIndicator(
                  strokeWidth: 1.0,
                  color:       AppColors.accent.withValues(alpha: 0.5),
                ),
              )
            : Text(
                initial,
                style: AppTextStyles.displaySmall(
                  AppColors.accent.withValues(alpha: 0.5),
                ),
              ),
      ),
    );
  }
}
