/// Full-screen fragrance detail — lives outside the ShellRoute so there is
/// no bottom navigation bar.
/// Sections (all conditional on data presence):
///   Hero image (SliverAppBar)
///   Name + brand + attribute chips + collection CTA
///   PERFORMANCE — ScoreBar for sillage, longevity, community rating
///   FRAGRANCE NOTES — top / heart / base pyramid
///   ACCORDS — strength-sorted ScoreBar list
///   ABOUT — description paragraph

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../app/theme.dart';
import '../features/collection/providers/collection_provider.dart';
import '../features/fragrances/data/models/fragrance.dart';
import '../features/fragrances/providers/fragrance_provider.dart';
import '../widgets/neu_button.dart';
import '../widgets/neu_card.dart';
import '../widgets/score_bar.dart';

class FragranceDetailScreen extends ConsumerWidget {
  const FragranceDetailScreen({super.key, required this.id});
  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(fragranceDetailProvider(id));

    return Scaffold(
      body: state.when(
        loading: () => const Center(
          child: CircularProgressIndicator(
              strokeWidth: 2, color: AppColors.accent),
        ),
        error: (err, _) => _ErrorBody(
          message: err.toString(),
          onBack: () => Navigator.of(context).pop(),
          onRetry: () => ref.invalidate(fragranceDetailProvider(id)),
        ),
        data: (fragrance) => _DetailBody(fragrance: fragrance),
      ),
    );
  }
}

// Error

class _ErrorBody extends StatelessWidget {
  const _ErrorBody(
      {required this.message, required this.onBack, required this.onRetry});
  final String message;
  final VoidCallback onBack;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Column(
        children: [
          Align(
            alignment: Alignment.topLeft,
            child: BackButton(
                onPressed: onBack, color: context.inkColor),
          ),
          Expanded(
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(40),
                child: NeuCard(
                  padding: const EdgeInsets.all(28),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.error_outline,
                          size: 44, color: context.muted),
                      const SizedBox(height: 16),
                      Text(
                        'Could not load fragrance.',
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
            ),
          ),
        ],
      ),
    );
  }
}

// Detail body

class _DetailBody extends ConsumerWidget {
  const _DetailBody({required this.fragrance});
  final Fragrance fragrance;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final items = ref.watch(collectionNotifierProvider).valueOrNull ?? [];
    final match = items.where((i) => i.fragranceId == fragrance.id).toList();
    final inCollection = match.isNotEmpty;
    final isFavourite = inCollection && match.first.isFavorite;
    final notifier = ref.read(collectionNotifierProvider.notifier);

    return CustomScrollView(
      slivers: [
        // Hero image / app bar
        SliverAppBar(
          expandedHeight: fragrance.imageUrl != null ? 280 : 120,
          pinned: true,
          backgroundColor: context.bgColor,
          surfaceTintColor: Colors.transparent,
          leading: Container(
            margin: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: context.neu.cardColor.withValues(alpha: 0.88),
              shape: BoxShape.circle,
              boxShadow: context.neu.raisedSm,
            ),
            child: BackButton(color: context.inkColor),
          ),
          actions: [
            if (inCollection)
              Container(
                margin: const EdgeInsets.only(right: 8, top: 8, bottom: 8),
                decoration: BoxDecoration(
                  color: context.neu.cardColor.withValues(alpha: 0.88),
                  shape: BoxShape.circle,
                  boxShadow: context.neu.raisedSm,
                ),
                child: IconButton(
                  icon: Icon(
                    isFavourite ? Icons.star : Icons.star_border,
                    color: isFavourite ? AppColors.accent : context.muted,
                  ),
                  onPressed: () => notifier.toggleFavourite(fragrance.id),
                ),
              ),
          ],
          flexibleSpace: FlexibleSpaceBar(
            background: fragrance.imageUrl != null
                ? Image.network(
                    fragrance.imageUrl!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) =>
                        _HeroPlaceholder(context: context),
                  )
                : _HeroPlaceholder(context: context),
          ),
        ),

        // Content
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 40),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Name + brand
                Text(fragrance.name,
                    style: AppTextStyles.displayMedium(context.inkColor)),
                const SizedBox(height: 4),
                Text(fragrance.brand,
                    style: AppTextStyles.bodyLarge(context.muted)),
                const SizedBox(height: 14),

                // Attribute chips
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    if (fragrance.concentration != null)
                      _Chip(fragrance.concentration!),
                    if (fragrance.olfactiveFamily != null)
                      _Chip(fragrance.olfactiveFamily!),
                    if (fragrance.genderTarget != null)
                      _Chip(fragrance.genderTarget!),
                    if (fragrance.releaseYear != null)
                      _Chip('${fragrance.releaseYear}'),
                    if (!fragrance.hasClimateProfile)
                      _Chip('Not scored',
                          bg: context.errorColor.withValues(alpha: 0.10),
                          fg: context.errorColor),
                  ],
                ),
                const SizedBox(height: 24),

                // Collection action
                _CollectionBar(
                  inCollection: inCollection,
                  onAdd: () => notifier.add(fragranceId: fragrance.id),
                  onRemove: () =>
                      _confirmRemove(context, notifier, fragrance),
                ),

                // Performance
                if (fragrance.performance != null) ...[
                  const SizedBox(height: 28),
                  _SectionDivider(),
                  const SizedBox(height: 24),
                  const _SectionHeader('PERFORMANCE'),
                  const SizedBox(height: 16),
                  NeuCard(
                    isInset: true,
                    padding: const EdgeInsets.all(20),
                    child: _PerformanceBars(
                        performance: fragrance.performance!),
                  ),
                ],

                // Notes pyramid
                if (fragrance.notes != null) ...[
                  const SizedBox(height: 28),
                  _SectionDivider(),
                  const SizedBox(height: 24),
                  const _SectionHeader('FRAGRANCE NOTES'),
                  const SizedBox(height: 16),
                  NeuCard(
                    isInset: true,
                    padding: const EdgeInsets.all(20),
                    child: _NotePyramid(notes: fragrance.notes!),
                  ),
                ],

                // Accords
                if (fragrance.accords.isNotEmpty) ...[
                  const SizedBox(height: 28),
                  _SectionDivider(),
                  const SizedBox(height: 24),
                  const _SectionHeader('ACCORDS'),
                  const SizedBox(height: 16),
                  NeuCard(
                    isInset: true,
                    padding: const EdgeInsets.all(20),
                    child: _AccordBars(accords: fragrance.accords),
                  ),
                ],

                // Description
                if (fragrance.description != null &&
                    fragrance.description!.isNotEmpty) ...[
                  const SizedBox(height: 28),
                  _SectionDivider(),
                  const SizedBox(height: 24),
                  const _SectionHeader('ABOUT'),
                  const SizedBox(height: 12),
                  Text(
                    fragrance.description!,
                    style: AppTextStyles.bodyMedium(context.muted)
                        .copyWith(height: 1.8),
                  ),
                ],

                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ],
    );
  }

  void _confirmRemove(
      BuildContext context, CollectionNotifier notifier, Fragrance f) {
    showDialog<void>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Remove from wardrobe?'),
        content: Text('Remove ${f.name} from your collection?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              notifier.remove(f.id);
            },
            child: Text('Remove',
                style: TextStyle(color: AppColors.error)),
          ),
        ],
      ),
    );
  }
}

// Collection bar

class _CollectionBar extends StatelessWidget {
  const _CollectionBar(
      {required this.inCollection,
      required this.onAdd,
      required this.onRemove});
  final bool inCollection;
  final VoidCallback onAdd;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    if (inCollection) {
      return Row(
        children: [
          const Icon(Icons.inventory_2, size: 16, color: AppColors.accent),
          const SizedBox(width: 8),
          Text('In your wardrobe',
              style: AppTextStyles.bodyMedium(AppColors.accent)),
          const Spacer(),
          TextButton(
            onPressed: onRemove,
            style: TextButton.styleFrom(
                foregroundColor: context.errorColor),
            child: const Text('Remove'),
          ),
        ],
      );
    }

    return NeuButton(
      label: 'Add to wardrobe',
      onPressed: onAdd,
      icon: const Icon(Icons.add),
      width: double.infinity,
    );
  }
}

// Performance bars

class _PerformanceBars extends StatelessWidget {
  const _PerformanceBars({required this.performance});
  final FragrancePerformance performance;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        if (performance.sillage != null) ...[
          _BarRow(
            context: context,
            label: 'Sillage',
            value: performance.sillage! / 10,
            valueLabel: '${performance.sillage!.toStringAsFixed(1)} / 10',
          ),
        ],
        if (performance.longevity != null) ...[
          const SizedBox(height: 16),
          _BarRow(
            context: context,
            label: 'Longevity',
            value: performance.longevity! / 10,
            valueLabel: '${performance.longevity!.toStringAsFixed(1)} / 10',
          ),
        ],
        if (performance.rating != null) ...[
          const SizedBox(height: 16),
          _BarRow(
            context: context,
            label: 'Community',
            value: performance.rating! / 5,
            valueLabel:
                '${performance.rating!.toStringAsFixed(1)} / 5'
                '${performance.votes != null ? '  (${performance.votes})' : ''}',
          ),
        ],
      ],
    );
  }
}

class _BarRow extends StatelessWidget {
  const _BarRow({
    required this.context,
    required this.label,
    required this.value,
    required this.valueLabel,
  });
  final BuildContext context;
  final String label;
  final double value;
  final String valueLabel;

  @override
  Widget build(_) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(label, style: AppTextStyles.labelSmall(context.muted)),
            const Spacer(),
            Text(valueLabel, style: AppTextStyles.bodySmall(context.muted)),
          ],
        ),
        const SizedBox(height: 8),
        ScoreBar(score: value, showLabel: false, height: 5),
      ],
    );
  }
}

// Note pyramid

class _NotePyramid extends StatelessWidget {
  const _NotePyramid({required this.notes});
  final FragranceNotes notes;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        if (notes.top.isNotEmpty)
          _NoteRow(tier: 'Top', noteNames: notes.top),
        if (notes.top.isNotEmpty && notes.heart.isNotEmpty)
          const SizedBox(height: 12),
        if (notes.heart.isNotEmpty)
          _NoteRow(tier: 'Heart', noteNames: notes.heart),
        if (notes.heart.isNotEmpty && notes.base.isNotEmpty)
          const SizedBox(height: 12),
        if (notes.base.isNotEmpty)
          _NoteRow(tier: 'Base', noteNames: notes.base),
      ],
    );
  }
}

class _NoteRow extends StatelessWidget {
  const _NoteRow({required this.tier, required this.noteNames});
  final String tier;
  final List<String> noteNames;

  @override
  Widget build(BuildContext context) {
    final borderC =
        context.isDark ? AppColors.borderDark : AppColors.border;

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 44,
          child: Text(tier,
              style: AppTextStyles.labelSmall(context.muted)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Wrap(
            spacing: 6,
            runSpacing: 5,
            children: noteNames
                .map((n) => Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        border: Border.all(color: borderC),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(n,
                          style: AppTextStyles.bodySmall(context.inkColor)),
                    ))
                .toList(),
          ),
        ),
      ],
    );
  }
}

// Accord bars

class _AccordBars extends StatelessWidget {
  const _AccordBars({required this.accords});
  final List<FragranceAccord> accords;

  @override
  Widget build(BuildContext context) {
    final sorted = [...accords]
      ..sort((a, b) => b.strength.compareTo(a.strength));

    return Column(
      children: sorted.asMap().entries.map((entry) {
        final accord = entry.value;
        return Padding(
          padding: EdgeInsets.only(
              bottom: entry.key < sorted.length - 1 ? 14 : 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Text(accord.accord,
                      style: AppTextStyles.bodySmall(context.inkColor)),
                  const Spacer(),
                  Text(
                    '${accord.strength.toStringAsFixed(0)}%',
                    style: AppTextStyles.bodySmall(context.muted),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              ScoreBar(
                score: (accord.strength / 100).clamp(0.0, 1.0),
                showLabel: false,
                height: 5,
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

// Shared helpers

class _HeroPlaceholder extends StatelessWidget {
  const _HeroPlaceholder({required this.context});
  final BuildContext context;

  @override
  Widget build(_) {
    return Container(
      color: context.neu.cardColor,
      child: Center(
        child: Icon(Icons.image_not_supported_outlined,
            size: 48, color: context.muted),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader(this.title);
  final String title;

  @override
  Widget build(BuildContext context) =>
      Text(title, style: AppTextStyles.labelSmall(context.muted));
}

class _SectionDivider extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Divider(
      color: context.isDark ? AppColors.borderDark : AppColors.border,
      thickness: 0.5,
      height: 1,
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip(this.label, {this.bg, this.fg});
  final String label;
  final Color? bg;
  final Color? fg;

  @override
  Widget build(BuildContext context) {
    final bgColor = bg ??
        (context.isDark ? AppColors.borderDark : AppColors.border);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(label,
          style: AppTextStyles.labelSmall(fg ?? context.muted)),
    );
  }
}
