/// "History" tab — chronological list of past recommendation sessions.
/// Each tile is tappable to expand the 3 scored recommendations.

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../app/router.dart';
import '../app/theme.dart';
import '../features/recommendations/data/models/recommendation_result.dart';
import '../features/recommendations/data/models/scored_fragrance.dart';
import '../features/recommendations/providers/recommendation_provider.dart';
import '../widgets/neu_button.dart';
import '../widgets/neu_card.dart';

class HistoryScreen extends ConsumerWidget {
  const HistoryScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(historyProvider(1));

    return Scaffold(
      appBar: AppBar(
        title: const Text('History'),
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: 8),
            child: ThemeToggleButton(),
          ),
        ],
      ),
      body: state.when(
        loading: () => Center(
          child: CircularProgressIndicator(
              strokeWidth: 2, color: AppColors.accent),
        ),
        error: (err, _) => _ErrorBody(
          message: err.toString(),
          onRetry: () => ref.invalidate(historyProvider(1)),
        ),
        data: (entries) =>
            entries.isEmpty ? const _EmptyBody() : _HistoryList(entries: entries),
      ),
    );
  }
}

// Empty

class _EmptyBody extends StatelessWidget {
  const _EmptyBody();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.history_outlined, size: 52, color: context.muted),
            const SizedBox(height: 20),
            Text(
              'No history yet.',
              style: AppTextStyles.displaySmall(context.inkColor),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Your recommendation sessions will appear here.',
              style: AppTextStyles.bodySmall(context.muted),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

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
              Icon(Icons.wifi_off_outlined, size: 44, color: context.muted),
              const SizedBox(height: 16),
              Text(
                'Could not load history.',
                style: AppTextStyles.displaySmall(context.inkColor),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                message,
                style: AppTextStyles.bodySmall(context.muted),
                textAlign: TextAlign.center,
                maxLines: 2,
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

// History list

class _HistoryList extends StatelessWidget {
  const _HistoryList({required this.entries});
  final List<HistoryEntry> entries;

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
      itemCount: entries.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, i) => _HistoryTile(entry: entries[i]),
    );
  }
}

// History tile

class _HistoryTile extends StatefulWidget {
  const _HistoryTile({required this.entry});
  final HistoryEntry entry;

  @override
  State<_HistoryTile> createState() => _HistoryTileState();
}

class _HistoryTileState extends State<_HistoryTile> {
  bool _expanded = false;

  String _formatDate(DateTime dt) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ];
    final local = dt.toLocal();
    final h = local.hour.toString().padLeft(2, '0');
    final m = local.minute.toString().padLeft(2, '0');
    return '${months[local.month - 1]} ${local.day}, ${local.year}  ·  $h:$m';
  }

  @override
  Widget build(BuildContext context) {
    final e = widget.entry;
    final w = e.weather;
    final borderC =
        context.isDark ? AppColors.borderDark : AppColors.border;

    return NeuCard(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Summary row
          InkWell(
            onTap: () => setState(() => _expanded = !_expanded),
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Temperature badge
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: AppColors.accent.withValues(alpha: 0.10),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          '${w.temperatureCelsius.toStringAsFixed(0)}°',
                          style: AppTextStyles.displaySmall(AppColors.accent),
                        ),
                        Text(
                          w.season.substring(0, 3).toUpperCase(),
                          style: AppTextStyles.labelSmall(AppColors.accent),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  // Details column
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _formatDate(e.createdAt),
                          style: AppTextStyles.bodySmall(context.muted),
                        ),
                        const SizedBox(height: 4),
                        Text(w.condition,
                            style: AppTextStyles.bodyMedium(context.inkColor)),
                        const SizedBox(height: 2),
                        Text(
                          w.location,
                          style: AppTextStyles.bodySmall(context.muted),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 8),
                        if (e.chosenFragrance != null)
                          Row(
                            children: [
                              const Icon(Icons.done,
                                  size: 13, color: AppColors.accent),
                              const SizedBox(width: 5),
                              Expanded(
                                child: Text(
                                  '${e.chosenFragrance!.name} — ${e.chosenFragrance!.brand}',
                                  style: AppTextStyles.bodySmall(
                                      AppColors.accent),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          )
                        else
                          Text(
                            'No fragrance chosen',
                            style: AppTextStyles.bodySmall(context.muted),
                          ),
                      ],
                    ),
                  ),
                  // Expand chevron
                  AnimatedRotation(
                    turns: _expanded ? 0.5 : 0,
                    duration: const Duration(milliseconds: 200),
                    child: Icon(Icons.keyboard_arrow_down,
                        size: 20, color: context.muted),
                  ),
                ],
              ),
            ),
          ),

          // Expanded recommendations
          if (_expanded) ...[
            Divider(height: 1, thickness: 0.5, color: borderC),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('RECOMMENDATIONS',
                      style: AppTextStyles.labelSmall(context.muted)),
                  const SizedBox(height: 12),
                  ...e.engineResponse.asMap().entries.map(
                        (entry) => _HistoryRecommendationRow(
                          rank: entry.key + 1,
                          fragrance: entry.value,
                          wasChosen: e.chosenFragrance?.id ==
                              entry.value.fragranceId,
                        ),
                      ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// Recommendation row (inside expanded tile)

class _HistoryRecommendationRow extends StatelessWidget {
  const _HistoryRecommendationRow({
    required this.rank,
    required this.fragrance,
    required this.wasChosen,
  });

  final int rank;
  final ScoredFragrance fragrance;
  final bool wasChosen;

  @override
  Widget build(BuildContext context) {
    final scorePercent = (fragrance.score * 100).round();

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: InkWell(
        onTap: () => context
            .push(AppRoutes.fragranceDetailPath(fragrance.fragranceId)),
        borderRadius: BorderRadius.circular(6),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Row(
            children: [
              // Rank number
              SizedBox(
                width: 20,
                child: Text(
                  '$rank.',
                  style: AppTextStyles.bodySmall(
                      wasChosen ? AppColors.accent : context.muted),
                ),
              ),
              const SizedBox(width: 10),
              // Name + brand
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            fragrance.name,
                            style: AppTextStyles.bodyMedium(
                              wasChosen ? context.inkColor : context.muted,
                            ).copyWith(
                              fontWeight: wasChosen
                                  ? FontWeight.w600
                                  : FontWeight.w400,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (wasChosen)
                          const Icon(Icons.done,
                              size: 14, color: AppColors.accent),
                      ],
                    ),
                    Text(fragrance.brand,
                        style: AppTextStyles.bodySmall(context.muted)),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              // Score
              Text(
                '$scorePercent%',
                style: AppTextStyles.labelSmall(
                    wasChosen ? AppColors.accent : context.muted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
