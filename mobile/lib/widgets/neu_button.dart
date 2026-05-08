import 'package:flutter/material.dart';
import '../app/theme.dart';

class NeuButton extends StatefulWidget {
  const NeuButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.loading = false,
    this.icon,
    this.width,
    bool ghost = false,
  }) : _ghost = ghost;

  /// Secondary / ghost variant — transparent background.
  const NeuButton.ghost({
    super.key,
    required this.label,
    required this.onPressed,
    this.loading = false,
    this.icon,
    this.width,
  }) : _ghost = true;

  final String label;
  final VoidCallback? onPressed;
  final bool loading;
  final Widget? icon;
  final double? width;
  final bool _ghost;

  @override
  State<NeuButton> createState() => _NeuButtonState();
}

class _NeuButtonState extends State<NeuButton> {
  bool _pressed = false;

  bool get _disabled => widget.onPressed == null || widget.loading;

  @override
  Widget build(BuildContext context) {
    final neu     = context.neu;
    final isDark  = context.isDark;

    // Colours
    final bgColor = widget._ghost
        ? Colors.transparent
        : AppColors.accent;

    final fgColor = widget._ghost
        ? context.inkColor
        : const Color(0xFF1A1A1A); // ink on gold

    // Shadows
    final shadows = _disabled
        ? <BoxShadow>[]
        : (_pressed ? neu.inset : neu.raised);

    final border = widget._ghost
        ? Border.all(
            color: isDark
                ? Colors.white.withValues(alpha: 0.12)
                : Colors.white.withValues(alpha: 0.40),
            width: 0.5,
          )
        : null;

    return GestureDetector(
      onTapDown:   _disabled ? null : (_) => setState(() => _pressed = true),
      onTapUp:     _disabled ? null : (_) => setState(() => _pressed = false),
      onTapCancel: _disabled ? null : ()  => setState(() => _pressed = false),
      onTap:       _disabled ? null : widget.onPressed,
      child: AnimatedOpacity(
        duration: const Duration(milliseconds: 150),
        opacity:  _disabled ? 0.45 : 1.0,
        child: AnimatedContainer(
          duration:   const Duration(milliseconds: 120),
          curve:      Curves.easeOut,
          width:      widget.width,
          padding:    const EdgeInsets.symmetric(horizontal: 32, vertical: 15),
          decoration: BoxDecoration(
            color:        bgColor,
            borderRadius: BorderRadius.circular(10),
            boxShadow:    shadows,
            border:       border,
          ),
          child: Row(
            mainAxisSize:     widget.width != null
                ? MainAxisSize.max
                : MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (widget.loading)
                SizedBox(
                  width: 16,
                  height: 16,
                  child: CircularProgressIndicator(
                    strokeWidth: 1.5,
                    color: fgColor,
                  ),
                )
              else ...[
                if (widget.icon != null) ...[
                  IconTheme(
                    data: IconThemeData(color: fgColor, size: 16),
                    child: widget.icon!,
                  ),
                  const SizedBox(width: 8),
                ],
                Text(
                  widget.label.toUpperCase(),
                  style: AppTextStyles.labelLarge(fgColor),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
