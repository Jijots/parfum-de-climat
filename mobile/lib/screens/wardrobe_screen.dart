/// "Wardrobe" tab — user's fragrance collection with favourites strip.
/// Sections:
///   FAVOURITES (horizontal scroll)  — only if ≥1 favourite
///   ALL FRAGRANCES (vertical list)  — sorted alphabetically

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../app/router.dart';
import '../app/theme.dart';
import '../core/api/api_endpoints.dart';
import '../features/collection/data/models/collection_item.dart';
import '../features/collection/providers/collection_provider.dart';
import '../widgets/fragrance_image.dart';
import '../widgets/neu_button.dart';
import '../widgets/neu_card.dart';

class WardrobeScreen extends ConsumerWidget {
  const WardrobeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(collectionNotifierProvider);

    return Scaffold(
      appBar: AppBar(
        title: state.maybeWhen(
          data: (items) =>
              Text(items.isEmpty ? 'Wardrobe' : 'Wardrobe  (${items.length})'),
          orElse: () => const Text('Wardrobe'),
        ),
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
          onRetry: () => ref.invalidate(collectionNotifierProvider),
        ),
        data: (items) =>
            items.isEmpty ? const _EmptyBody() : _CollectionBody(items: items),
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
            Icon(Icons.inventory_2_outlined,
                size: 52, color: context.muted),
            const SizedBox(height: 20),
            Text(
              'Your wardrobe is empty.',
              style: AppTextStyles.displaySmall(context.inkColor),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Browse fragrances and add them to build your collection.',
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
                'Could not load your wardrobe.',
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

// Collection

class _CollectionBody extends ConsumerWidget {
  const _CollectionBody({required this.items});
  final List<CollectionItem> items;

  /// Full URL from a collection fragrance's cached or remote image.
  static String? _imageUrl(CollectionFragrance f) {
    if (f.cachedImagePath != null) {
      return '${ApiEndpoints.storageBaseUrl}/${f.cachedImagePath}';
    }
    return f.remoteImageUrl;
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(collectionNotifierProvider.notifier);
    final sorted = [...items]
      ..sort((a, b) => a.fragrance.name.compareTo(b.fragrance.name));
    final favourites = sorted.where((i) => i.isFavorite).toList();
    final borderC =
        context.isDark ? AppColors.borderDark : AppColors.border;

    return CustomScrollView(
      slivers: [
        // Favourites strip
        if (favourites.isNotEmpty) ...[
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
              child: Text('FAVOURITES',
                  style: AppTextStyles.labelSmall(context.muted)),
            ),
          ),
          SliverToBoxAdapter(
            child: SizedBox(
              height: 104,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                itemCount: favourites.length,
                separatorBuilder: (_, __) => const SizedBox(width: 14),
                itemBuilder: (context, i) {
                  final item = favourites[i];
                  return GestureDetector(
                    onTap: () => context.push(
                        AppRoutes.fragranceDetailPath(item.fragranceId)),
                    child: Column(
                      children: [
                        FragranceImage(
                          imageUrl: _imageUrl(item.fragrance),
                          name: item.fragrance.name,
                          size: 68,
                          borderRadius: 10,
                        ),
                        const SizedBox(height: 6),
                        SizedBox(
                          width: 68,
                          child: Text(
                            item.fragrance.name,
                            style: AppTextStyles.bodySmall(context.muted),
                            textAlign: TextAlign.center,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Divider(
              color: borderC,
              height: 28,
              thickness: 0.5,
              indent: 20,
              endIndent: 20,
            ),
          ),
        ],

        // All fragrances label
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 4, 20, 10),
            child: Text('ALL FRAGRANCES',
                style: AppTextStyles.labelSmall(context.muted)),
          ),
        ),

        // List
        SliverList(
          delegate: SliverChildBuilderDelegate(
            (context, i) {
              final item = sorted[i];
              return Column(
                children: [
                  ListTile(
                    contentPadding: const EdgeInsets.symmetric(
                        horizontal: 20, vertical: 6),
                    leading: FragranceImage(
                      imageUrl: _imageUrl(item.fragrance),
                      name: item.fragrance.name,
                      size: 48,
                      borderRadius: 8,
                    ),
                    title: Text(item.fragrance.name,
                        style: AppTextStyles.bodyMedium(context.inkColor)),
                    subtitle: Text(item.fragrance.brand,
                        style: AppTextStyles.bodySmall(context.muted)),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: Icon(
                            item.isFavorite ? Icons.star : Icons.star_border,
                            color: item.isFavorite
                                ? AppColors.accent
                                : context.muted,
                            size: 20,
                          ),
                          onPressed: () =>
                              notifier.toggleFavourite(item.fragranceId),
                        ),
                        IconButton(
                          icon: Icon(Icons.close,
                              size: 18, color: context.muted),
                          onPressed: () =>
                              _confirmRemove(context, item, notifier),
                        ),
                      ],
                    ),
                    onTap: () => context.push(
                        AppRoutes.fragranceDetailPath(item.fragranceId)),
                  ),
                  Divider(
                      height: 1,
                      thickness: 0.5,
                      color: borderC,
                      indent: 76),
                ],
              );
            },
            childCount: sorted.length,
          ),
        ),

        const SliverToBoxAdapter(child: SizedBox(height: 120)),
      ],
    );
  }

  void _confirmRemove(
    BuildContext context,
    CollectionItem item,
    CollectionNotifier notifier,
  ) {
    showDialog<void>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Remove from wardrobe?'),
        content:
            Text('Remove ${item.fragrance.name} from your collection?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              notifier.remove(item.fragranceId);
            },
            child: Text('Remove',
                style: TextStyle(color: AppColors.error)),
          ),
        ],
      ),
    );
  }
}
