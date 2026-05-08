import 'package:flutter/material.dart';
import '../app/theme.dart';

class NeuCard extends StatefulWidget {
  const NeuCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
    this.borderRadius = 16.0,
    this.isInset = false,
    this.onTap,
    this.width,
    this.height,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final double borderRadius;

  /// True → inset / recessed appearance. Used for active states, inputs.
  final bool isInset;

  /// If non-null, wraps in a [GestureDetector] with a press animation.
  final VoidCallback? onTap;

  final double? width;
  final double? height;

  @override
  State<NeuCard> createState() => _NeuCardState();
}

class _NeuCardState extends State<NeuCard> {
  bool _pressed = false;

  @override
  Widget build(BuildContext context) {
    final neu = context.neu;
    final isInset = widget.isInset || _pressed;

    final decoration = BoxDecoration(
      color:        neu.cardColor,
      borderRadius: BorderRadius.circular(widget.borderRadius),
      boxShadow:    isInset ? neu.inset : neu.raised,
    );

    final container = AnimatedContainer(
      duration:  const Duration(milliseconds: 120),
      curve:     Curves.easeOut,
      width:     widget.width,
      height:    widget.height,
      padding:   widget.padding,
      decoration: decoration,
      child:     widget.child,
    );

    if (widget.onTap == null) return container;

    return GestureDetector(
      onTapDown:   (_) => setState(() => _pressed = true),
      onTapUp:     (_) => setState(() => _pressed = false),
      onTapCancel: ()  => setState(() => _pressed = false),
      onTap:       widget.onTap,
      child:       container,
    );
  }
}
