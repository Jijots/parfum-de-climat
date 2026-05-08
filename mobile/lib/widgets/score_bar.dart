import 'package:flutter/material.dart';
import '../app/theme.dart';

class ScoreBar extends StatefulWidget {
  const ScoreBar({
    super.key,
    required this.score, // 0.0 – 1.0
    this.showLabel = true,
    this.height = 6.0,
  });

  /// Score in [0.0, 1.0].
  final double score;

  /// Whether to show the percentage label at the trailing edge.
  final bool showLabel;

  final double height;

  @override
  State<ScoreBar> createState() => _ScoreBarState();
}

class _ScoreBarState extends State<ScoreBar>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 600),
    );
    _animation = CurvedAnimation(parent: _ctrl, curve: Curves.easeOutCubic);
    _ctrl.forward();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final neu = context.neu;
    final pct = (widget.score.clamp(0.0, 1.0) * 100).round();

    return Row(
      children: [
        Expanded(
          child: LayoutBuilder(
            builder: (context, constraints) {
              return AnimatedBuilder(
                animation: _animation,
                builder: (context, _) {
                  return Stack(
                    children: [
                      // Track (recessed)
                      Container(
                        height:     widget.height,
                        decoration: BoxDecoration(
                          color:        neu.cardColor,
                          borderRadius: BorderRadius.circular(widget.height),
                          boxShadow:    [
                            BoxShadow(
                              color:       neu.shadowDark,
                              offset:      const Offset(2, 2),
                              blurRadius:  4,
                            ),
                            BoxShadow(
                              color:       neu.shadowLight,
                              offset:      const Offset(-2, -2),
                              blurRadius:  4,
                            ),
                          ],
                        ),
                      ),
                      // Fill
                      AnimatedContainer(
                        duration: Duration.zero,
                        height:   widget.height,
                        width:    constraints.maxWidth *
                            widget.score.clamp(0.0, 1.0) *
                            _animation.value,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              AppColors.accent.withValues(alpha: 0.7),
                              AppColors.accent,
                            ],
                          ),
                          borderRadius:
                              BorderRadius.circular(widget.height),
                          boxShadow: [
                            BoxShadow(
                              color:      AppColors.accent.withValues(alpha: 0.30),
                              blurRadius: 6,
                              offset:     const Offset(0, 2),
                            ),
                          ],
                        ),
                      ),
                    ],
                  );
                },
              );
            },
          ),
        ),
        if (widget.showLabel) ...[
          const SizedBox(width: 10),
          SizedBox(
            width: 34,
            child: Text(
              '$pct%',
              textAlign: TextAlign.right,
              style: AppTextStyles.labelSmall(context.muted),
            ),
          ),
        ],
      ],
    );
  }
}
