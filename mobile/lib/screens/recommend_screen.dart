/// "Today" tab — weather-based fragrance recommendations.
/// States:
///   null (initial)      → prompt + NeuButton CTA
///   AsyncLoading        → neumorphic skeleton cards
///   AsyncData(result)   → GlassCard weather banner + NeuCard recommendation list
///   AsyncError          → NeuCard error panel with retry

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../app/router.dart';
import '../app/theme.dart';
import '../core/api/api_endpoints.dart';
import '../features/recommendations/data/models/recommendation_result.dart';
import '../features/recommendations/data/models/scored_fragrance.dart';
import '../features/recommendations/providers/recommendation_provider.dart';
import '../widgets/fragrance_image.dart';
import '../widgets/glass_card.dart';
import '../widgets/neu_button.dart';
import '../widgets/neu_card.dart';
import '../widgets/score_bar.dart';

class RecommendScreen extends ConsumerWidget {
  const RecommendScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(recommendationNotifierProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Today'),
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: 8),
            child: ThemeToggleButton(),
          ),
        ],
      ),
      body: state.when(
        loading: () => const _LoadingBody(),
        error: (err, _) => _ErrorBody(
          message: err.toString(),
          onRetry: () => ref
              .read(recommendationNotifierProvider.notifier)
              .fetchRecommendations(),
        ),
        data: (result) => result == null
            ? _PromptBody(
                onFetch: () => ref
                    .read(recommendationNotifierProvider.notifier)
                    .fetchRecommendations(),
              )
            : _ResultBody(result: result),
      ),
    );
  }
}

// Prompt

class _PromptBody extends StatelessWidget {
  const _PromptBody({required this.onFetch});
  final VoidCallback onFetch;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wb_sunny_outlined,
                size: 52, color: AppColors.accent.withValues(alpha: 0.7)),
            const SizedBox(height: 24),
            Text(
              'What to wear today?',
              style: AppTextStyles.displayMedium(context.inkColor),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            Text(
              'The engine reads real-time weather at your location '
              'and scores every fragrance in your collection.',
              style: AppTextStyles.bodySmall(context.muted),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 36),
            NeuButton(
              label: 'Find my scent',
              onPressed: onFetch,
              icon: const Icon(Icons.location_on_outlined),
              width: 220,
            ),
          ],
        ),
      ),
    );
  }
}

// Loading skeleton

class _LoadingBody extends StatelessWidget {
  const _LoadingBody();

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      children: const [
        _SkeletonWeather(),
        SizedBox(height: 24),
        _SkeletonCard(),
        SizedBox(height: 16),
        _SkeletonCard(),
        SizedBox(height: 16),
        _SkeletonCard(),
      ],
    );
  }
}

class _SkeletonWeather extends StatelessWidget {
  const _SkeletonWeather();

  @override
  Widget build(BuildContext context) {
    final c = context.isDark
        ? Colors.white.withValues(alpha: 0.07)
        : Colors.black.withValues(alpha: 0.07);
    return NeuCard(
      padding: const EdgeInsets.all(20),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            _bone(c, w: 80, h: 40),
            const SizedBox(height: 8),
            _bone(c, w: 110, h: 14),
          ]),
          const Spacer(),
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            _bone(c, w: 90, h: 12),
            const SizedBox(height: 6),
            _bone(c, w: 60, h: 22, r: 20),
            const SizedBox(height: 6),
            _bone(c, w: 80, h: 12),
          ]),
        ],
      ),
    );
  }
}

class _SkeletonCard extends StatelessWidget {
  const _SkeletonCard();

  @override
  Widget build(BuildContext context) {
    final c = context.isDark
        ? Colors.white.withValues(alpha: 0.07)
        : Colors.black.withValues(alpha: 0.07);

    return NeuCard(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            _bone(c, w: 22, h: 22, r: 4),
            const SizedBox(width: 12),
            _bone(c, w: 60, h: 60, r: 10),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Expanded(child: _bone(c, h: 18)),
                  ]),
                  const SizedBox(height: 6),
                  _bone(c, w: 90, h: 12),
                ],
              ),
            ),
          ]),
          const SizedBox(height: 16),
          Row(children: [Expanded(child: _bone(c, h: 6, r: 3))]),
          const SizedBox(height: 12),
          Row(children: [Expanded(child: _bone(c, h: 12))]),
          const SizedBox(height: 4),
          _bone(c, w: 200, h: 12),
        ],
      ),
    );
  }
}

/// Single neumorphic skeleton bone.
Widget _bone(Color color, {double? w, required double h, double r = 6}) =>
    Container(
      width: w,
      height: h,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(r),
      ),
    );

// Error

class _ErrorBody extends StatelessWidget {
  const _ErrorBody({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: NeuCard(
          padding: const EdgeInsets.all(28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.cloud_off_outlined, size: 44, color: context.muted),
              const SizedBox(height: 16),
              Text(
                'Could not get recommendations.',
                style: AppTextStyles.displaySmall(context.inkColor),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                message,
                style: AppTextStyles.bodySmall(context.muted),
                textAlign: TextAlign.center,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 24),
              NeuButton.ghost(
                label: 'Try again',
                onPressed: onRetry,
                icon: const Icon(Icons.refresh),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// Result

class _ResultBody extends ConsumerWidget {
  const _ResultBody({required this.result});
  final RecommendationResult result;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(recommendationNotifierProvider.notifier);

    return CustomScrollView(
      slivers: [
        // Weather banner
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
            child: GlassCard(
              padding: const EdgeInsets.all(20),
              child: _WeatherBanner(weather: result.weather),
            ),
          ),
        ),

        // Section header
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 24, 8, 12),
            child: Row(
              children: [
                Text('TOP PICKS FOR TODAY',
                    style: AppTextStyles.labelSmall(context.muted)),
                const Spacer(),
                TextButton.icon(
                  onPressed: notifier.fetchRecommendations,
                  icon: const Icon(Icons.refresh,
                      size: 14, color: AppColors.accent),
                  label: Text('Refresh',
                      style: AppTextStyles.bodySmall(AppColors.accent)),
                  style: TextButton.styleFrom(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                ),
              ],
            ),
          ),
        ),

        // Recommendation cards
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(20, 0, 20, 120),
          sliver: SliverList(
            delegate: SliverChildBuilderDelegate(
              (ctx, i) {
                final f = result.recommendations[i];
                return _RecommendationCard(
                  rank: i + 1,
                  fragrance: f,
                  onChoose: () async {
                    await notifier.chooseFragrance(f.fragranceId);
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Wearing ${f.name} today.')),
                      );
                    }
                  },
                  onViewDetail: () => context
                      .push(AppRoutes.fragranceDetailPath(f.fragranceId)),
                );
              },
              childCount: result.recommendations.length,
            ),
          ),
        ),
      ],
    );
  }
}

// Weather banner

String _seasonEmoji(String season) => switch (season.toLowerCase()) {
      'spring' => '🌸',
      'summer' => '☀️',
      'autumn' || 'fall' => '🍂',
      'winter' => '❄️',
      _ => '🌡️',
    };

class _WeatherBanner extends StatelessWidget {
  const _WeatherBanner({required this.weather});
  final WeatherContext weather;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${weather.temperatureCelsius.toStringAsFixed(1)}°C',
              style: AppTextStyles.displayLarge(AppColors.accent),
            ),
            Text(weather.condition,
                style: AppTextStyles.bodyMedium(context.inkColor)),
          ],
        ),
        const Spacer(),
        Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              weather.location,
              style: AppTextStyles.bodySmall(context.muted),
              textAlign: TextAlign.right,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 6),
            _SeasonPill(
                '${_seasonEmoji(weather.season)}  ${weather.season.toUpperCase()}'),
            const SizedBox(height: 4),
            Text(
              '${weather.humidityPercent.toStringAsFixed(0)}% humidity',
              style: AppTextStyles.bodySmall(context.muted),
            ),
          ],
        ),
      ],
    );
  }
}

class _SeasonPill extends StatelessWidget {
  const _SeasonPill(this.label);
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.accent.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(label, style: AppTextStyles.labelSmall(AppColors.accent)),
    );
  }
}

// Recommendation card

class _RecommendationCard extends StatefulWidget {
  const _RecommendationCard({
    required this.rank,
    required this.fragrance,
    required this.onChoose,
    required this.onViewDetail,
  });

  final int rank;
  final ScoredFragrance fragrance;
  final VoidCallback onChoose;
  final VoidCallback onViewDetail;

  @override
  State<_RecommendationCard> createState() => _RecommendationCardState();
}

class _RecommendationCardState extends State<_RecommendationCard> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final f = widget.fragrance;
    final imageUrl = f.cachedImagePath != null
        ? '${ApiEndpoints.storageBaseUrl}/${f.cachedImagePath}'
        : null;
    final borderC =
        context.isDark ? AppColors.borderDark : AppColors.border;

    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: NeuCard(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header: rank badge + image + name/brand
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 22,
                  height: 22,
                  decoration: BoxDecoration(
                    color: widget.rank == 1
                        ? AppColors.accent
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(4),
                    border:
                        widget.rank != 1 ? Border.all(color: borderC) : null,
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    '${widget.rank}',
                    style: AppTextStyles.labelSmall(
                      widget.rank == 1
                          ? const Color(0xFF1A1A1A)
                          : context.muted,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                FragranceImage(
                  imageUrl: imageUrl,
                  name: f.name,
                  size: 60,
                  borderRadius: 10,
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(f.name,
                          style:
                              AppTextStyles.displaySmall(context.inkColor),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 2),
                      Text(f.brand,
                          style: AppTextStyles.bodySmall(context.muted)),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Climate fit score
            Text('CLIMATE FIT',
                style: AppTextStyles.labelSmall(context.muted)),
            const SizedBox(height: 8),
            ScoreBar(score: f.score),

            const SizedBox(height: 12),

            // Reasoning
            Text(
              f.reasoning,
              style: AppTextStyles.bodySmall(context.muted),
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),

            // Matched notes
            if (f.matchedNotes.isNotEmpty) ...[
              const SizedBox(height: 10),
              Wrap(
                spacing: 6,
                runSpacing: 4,
                children:
                    f.matchedNotes.map((n) => _NoteChip(n)).toList(),
              ),
            ],

            // Score breakdown toggle
            const SizedBox(height: 12),
            GestureDetector(
              onTap: () => setState(() => _expanded = !_expanded),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Score breakdown',
                    style: AppTextStyles.bodySmall(context.muted)
                        .copyWith(decoration: TextDecoration.underline),
                  ),
                  const SizedBox(width: 4),
                  AnimatedRotation(
                    turns: _expanded ? 0.5 : 0,
                    duration: const Duration(milliseconds: 150),
                    child: Icon(Icons.keyboard_arrow_down,
                        size: 16, color: context.muted),
                  ),
                ],
              ),
            ),

            if (_expanded) ...[
              const SizedBox(height: 10),
              _BreakdownRow('Base score', f.scoreBreakdown.baseScore),
              _BreakdownRow(
                  'Sillage ×${f.scoreBreakdown.sillageMultiplier.toStringAsFixed(2)}',
                  null),
              _BreakdownRow(
                  'Favourite +${f.scoreBreakdown.favoriteBoost.toStringAsFixed(2)}',
                  null),
              _BreakdownRow('Final score', f.scoreBreakdown.finalScore,
                  highlight: true),
            ],

            const SizedBox(height: 16),

            // Action buttons
            Row(
              children: [
                Expanded(
                  child: NeuButton(
                    label: "I'm wearing this",
                    onPressed: widget.onChoose,
                    icon: const Icon(Icons.done),
                    width: double.infinity,
                  ),
                ),
                const SizedBox(width: 10),
                NeuButton.ghost(
                  label: 'Details',
                  onPressed: widget.onViewDetail,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// Score breakdown row

class _BreakdownRow extends StatelessWidget {
  const _BreakdownRow(this.label, this.value, {this.highlight = false});
  final String label;
  final double? value;
  final bool highlight;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: AppTextStyles.bodySmall(
                      highlight ? context.inkColor : context.muted)
                  .copyWith(
                      fontWeight:
                          highlight ? FontWeight.w600 : FontWeight.w400),
            ),
          ),
          if (value != null)
            Text(
              value!.toStringAsFixed(3),
              style: AppTextStyles.bodySmall(
                      highlight ? AppColors.accent : context.muted)
                  .copyWith(
                      fontWeight:
                          highlight ? FontWeight.w600 : FontWeight.w400),
            ),
        ],
      ),
    );
  }
}

// Note chip

class _NoteChip extends StatelessWidget {
  const _NoteChip(this.label);
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.accent.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(label, style: AppTextStyles.labelSmall(AppColors.accent)),
    );
  }
}
