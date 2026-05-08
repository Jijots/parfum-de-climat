/// Read-only access to the fragrance catalogue.
/// Routes:
///   GET /api/v1/fragrances       → [list]   (paginated, filterable)
///   GET /api/v1/fragrances/{id}  → [detail]

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import 'models/fragrance.dart';

class FragranceRepository {
  const FragranceRepository({required ApiClient apiClient})
      : _api = apiClient;

  final ApiClient _api;

  /// Fetch a paginated list of fragrances.
  /// [page] is 1-indexed (default: 1).
  /// [search] filters by name or brand (passed as ?search=query).
  Future<PaginatedFragrances> list({int page = 1, String? search}) async {
    final response = await _api.get<Map<String, dynamic>>(
      ApiEndpoints.fragrances,
      queryParameters: {
        'page': page,
        if (search != null && search.isNotEmpty) 'search': search,
      },
    );
    return PaginatedFragrances.fromJson(response.data!);
  }

  /// Fetch full detail for a single fragrance, including accords.
  Future<Fragrance> detail(int id) async {
    final response = await _api.get<Map<String, dynamic>>(
      ApiEndpoints.fragranceDetail(id),
    );
    // Laravel's JsonResource wraps single resources under the 'data' key by default.
    return Fragrance.fromJson(response.data!['data'] as Map<String, dynamic>);
  }
}
