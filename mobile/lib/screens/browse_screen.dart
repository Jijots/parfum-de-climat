/// "Browse" tab — paginated fragrance catalogue with live search.
/// Search is debounce-free (fires on every keystroke via Riverpod provider).
/// Pagination uses a client-side accumulator (_extraItems) so the SliverList
/// does not jump when the user loads more.

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../app/router.dart';
import '../app/theme.dart';
import '../features/collection/providers/collection_provider.dart';
import '../features/fragrances/data/models/fragrance.dart';
import '../features/fragrances/providers/fragrance_provider.dart';
import '../widgets/fragrance_image.dart';
import '../widgets/neu_button.dart';
import '../widgets/neu_card.dart';

class BrowseScreen extends ConsumerStatefulWidget {
  const BrowseScreen({super.key});

  @override
  ConsumerState<BrowseScreen> createState() => _BrowseScreenState();
}

class _BrowseScreenState extends ConsumerState<BrowseScreen> {
  final _searchCtrl = TextEditingController();
  int _currentPage = 1;
  final List<FragranceSummary> _extraItems = [];

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _onSearchChanged(String query) {
    setState(() {
      _currentPage = 1;
      _extraItems.clear();
    });
    ref.read(fragranceSearchQueryProvider.notifier).state = query;
  }

  @override
  Widget build(BuildContext context) {
    final query = ref.watch(fragranceSearchQueryProvider);
    final listState = ref.watch(
      fragranceListProvider((page: 1, search: query.isEmpty ? null : query)),
    );
    final borderC =
        context.isDark ? AppColors.borderDark : AppColors.border;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Browse'),
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: 8),
            child: ThemeToggleButton(),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(64),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
            child: TextFormField(
              controller: _searchCtrl,
              onChanged: _onSearchChanged,
              style: AppTextStyles.bodyMedium(context.inkColor),
              decoration: InputDecoration(
                hintText: 'Search fragrances, brands…',
                hintStyle: AppTextStyles.bodyMedium(context.muted),
                prefixIcon: Icon(Icons.search, size: 18, color: context.muted),
                suffixIcon: query.isNotEmpty
                    ? IconButton(
                        icon: Icon(Icons.clear, size: 18, color: context.muted),
                        onPressed: () {
                          _searchCtrl.clear();
                          _onSearchChanged('');
                        },
                      )
                    : null,
                isDense: true,
                contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16, vertical: 13),
              ),
            ),
          ),
        ),
      ),
      body: listState.when(
        loading: () => Center(
          child: CircularProgressIndicator(
              strokeWidth: 2, color: AppColors.accent),
        ),
        error: (err, _) => _ErrorBody(
          message: err.toString(),
          onRetry: () => ref.invalidate(fragranceListProvider),
        ),
        data: (paginated) {
          final items = [...paginated.data, ..._extraItems];
          final hasMore = _currentPage < paginated.lastPage;

          if (items.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(40),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.search_off, size: 44, color: context.muted),
                    const SizedBox(height: 16),
                    Text(
                      query.isEmpty
                          ? 'No fragrances found.'
                          : 'No results for "$query".',
                      style: AppTextStyles.bodyMedium(context.muted),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            );
          }

          return ListView.separated(
            padding: const EdgeInsets.only(bottom: 120),
            itemCount: items.length + (hasMore ? 1 : 0),
            separatorBuilder: (_, __) => Divider(
                height: 1, thickness: 0.5, color: borderC, indent: 76),
            itemBuilder: (context, i) {
              if (i == items.length) {
                return _LoadMoreButton(onTap: () async {
                  final nextPage = _currentPage + 1;
                  final next = await ref.read(
                    fragranceListProvider((
                      page: nextPage,
                      search: query.isEmpty ? null : query,
                    )).future,
                  );
                  if (mounted) {
                    setState(() {
                      _currentPage = nextPage;
                      _extraItems.addAll(next.data);
                    });
                  }
                });
              }
              return _BrowseTile(
                fragrance: items[i],
                onTap: () =>
                    context.push(AppRoutes.fragranceDetailPath(items[i].id)),
              );
            },
          );
        },
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
                'Could not load fragrances.',
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

// Browse tile

class _BrowseTile extends ConsumerWidget {
  const _BrowseTile({required this.fragrance, required this.onTap});
  final FragranceSummary fragrance;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final inCollection = ref
            .watch(collectionNotifierProvider)
            .valueOrNull
            ?.any((i) => i.fragranceId == fragrance.id) ??
        false;

    return ListTile(
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      leading: FragranceImage(
        imageUrl: fragrance.imageUrl,
        name: fragrance.name,
        size: 48,
        borderRadius: 8,
      ),
      title: Text(fragrance.name,
          style: AppTextStyles.bodyMedium(context.inkColor)),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(fragrance.brand,
              style: AppTextStyles.bodySmall(context.muted)),
          if (fragrance.olfactiveFamily != null) ...[
            const SizedBox(height: 4),
            Row(
              children: [
                _MiniChip(fragrance.olfactiveFamily!),
                if (!fragrance.hasClimateProfile) ...[
                  const SizedBox(width: 4),
                  _MiniChip('unscored',
                      bg: context.errorColor.withValues(alpha: 0.10),
                      fg: context.errorColor),
                ],
                if (inCollection) ...[
                  const SizedBox(width: 4),
                  const Icon(Icons.inventory_2,
                      size: 12, color: AppColors.accent),
                ],
              ],
            ),
          ],
        ],
      ),
      trailing:
          Icon(Icons.chevron_right, size: 18, color: context.muted),
      onTap: onTap,
    );
  }
}

// Load more

class _LoadMoreButton extends StatefulWidget {
  const _LoadMoreButton({required this.onTap});
  final Future<void> Function() onTap;

  @override
  State<_LoadMoreButton> createState() => _LoadMoreButtonState();
}

class _LoadMoreButtonState extends State<_LoadMoreButton> {
  bool _loading = false;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 20),
      child: Center(
        child: NeuButton.ghost(
          label: 'Load more',
          loading: _loading,
          onPressed: _loading
              ? null
              : () async {
                  setState(() => _loading = true);
                  await widget.onTap();
                  if (mounted) setState(() => _loading = false);
                },
          icon: const Icon(Icons.expand_more),
        ),
      ),
    );
  }
}

// Mini chip

class _MiniChip extends StatelessWidget {
  const _MiniChip(this.label, {this.bg, this.fg});
  final String label;
  final Color? bg;
  final Color? fg;

  @override
  Widget build(BuildContext context) {
    final bgColor = bg ??
        (context.isDark ? AppColors.borderDark : AppColors.border);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: AppTextStyles.labelSmall(fg ?? context.muted),
      ),
    );
  }
}
