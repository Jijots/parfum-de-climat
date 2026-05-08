import 'dart:ui';
import 'package:flutter/material.dart';
import '../app/theme.dart';

class GlassCard extends StatelessWidget {
  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
    this.borderRadius = 16.0,
    this.blur = 20.0,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final double borderRadius;

  /// Gaussian blur sigma applied to content behind the card.
  final double blur;

  @override
  Widget build(BuildContext context) {
    final glass = context.glass;
    final radius = BorderRadius.circular(borderRadius);

    return ClipRRect(
      borderRadius: radius,
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: blur, sigmaY: blur),
        child: Container(
          padding: padding,
          decoration: BoxDecoration(
            color:        glass.background,
            borderRadius: radius,
            border:       Border.all(color: glass.border, width: 0.5),
          ),
          child: child,
        ),
      ),
    );
  }
}
