/// Configured [Dio] instance with two interceptors:
///   1. [AuthInterceptor]   — injects the Bearer token on every outgoing
///                            request and deletes it + notifies listeners on
///                            a 401 response.
///   2. Error normalisation — [_mapDioException] converts every [DioException]
///                            to a typed [AppException] so repositories never
///                            see Dio implementation details.
/// This file contains only the [ApiClient] and [AuthInterceptor] classes.
/// Riverpod providers are in core/providers/core_providers.dart to keep the
/// dependency graph acyclic.

import 'package:dio/dio.dart';

import '../errors/app_exception.dart';
import '../storage/secure_storage.dart';
import 'api_endpoints.dart';

// Auth Interceptor

/// Injects `Authorization: Bearer <token>` on every outgoing request.
/// On a 401 response the interceptor:
///   1. Deletes the stored token (the Sanctum session is no longer valid).
///   2. Calls [onUnauthenticated] — supplied by [ApiClient]'s constructor —
///      so the GoRouter refresh fires and redirects to the login screen.
class AuthInterceptor extends Interceptor {
  AuthInterceptor({
    required SecureStorage storage,
    required void Function() onUnauthenticated,
  })  : _storage = storage,
        _onUnauthenticated = onUnauthenticated;

  final SecureStorage _storage;
  final void Function() _onUnauthenticated;

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _storage.readToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (err.response?.statusCode == 401) {
      // Delete the token so the router redirect check fails → login screen.
      _storage.deleteToken();
      _onUnauthenticated();
    }
    handler.next(err);
  }
}

// ApiClient

/// Wrapper around [Dio] that:
///   - sets [ApiEndpoints.baseUrl] as the base URL
///   - enforces 10 s connect / 30 s receive timeouts
///   - attaches [AuthInterceptor] for token injection / 401 clearing
///   - exposes [get], [post], [patch], [delete] helpers that translate any
///     [DioException] into a typed [AppException]
/// Repositories call these helpers directly — they never catch [DioException].
class ApiClient {
  ApiClient({
    required SecureStorage storage,
    required void Function() onUnauthenticated,
  }) {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiEndpoints.baseUrl,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 30),
        sendTimeout: const Duration(seconds: 30),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    _dio.interceptors.add(
      AuthInterceptor(
        storage: storage,
        onUnauthenticated: onUnauthenticated,
      ),
    );
  }

  late final Dio _dio;

  // Public HTTP helpers

  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      return await _dio.get<T>(path, queryParameters: queryParameters);
    } on DioException catch (e) {
      throw _mapDioException(e);
    }
  }

  Future<Response<T>> post<T>(String path, {Object? data}) async {
    try {
      return await _dio.post<T>(path, data: data);
    } on DioException catch (e) {
      throw _mapDioException(e);
    }
  }

  Future<Response<T>> patch<T>(String path, {Object? data}) async {
    try {
      return await _dio.patch<T>(path, data: data);
    } on DioException catch (e) {
      throw _mapDioException(e);
    }
  }

  Future<Response<T>> delete<T>(String path) async {
    try {
      return await _dio.delete<T>(path);
    } on DioException catch (e) {
      throw _mapDioException(e);
    }
  }

  // Error mapping

  /// Convert a [DioException] to a typed [AppException].
  AppException _mapDioException(DioException e) {
    // Timeout variants
    if (e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout ||
        e.type == DioExceptionType.sendTimeout) {
      return const TimeoutException();
    }

    // No internet / host unreachable
    if (e.type == DioExceptionType.connectionError) {
      return const NetworkException();
    }

    final response = e.response;
    if (response == null) return const UnexpectedException();

    final statusCode = response.statusCode ?? 0;
    final body = response.data;

    String extractMessage(String fallback) {
      if (body is Map<String, dynamic>) {
        return (body['message'] as String?) ?? fallback;
      }
      return fallback;
    }

    return switch (statusCode) {
      401 => UnauthorisedException(extractMessage('Your session has expired.')),
      403 => ForbiddenException(extractMessage('Access denied.')),
      404 => NotFoundException(extractMessage('Resource not found.')),
      422 => ValidationException(
          message: extractMessage('Validation failed.'),
          errors: _extractValidationErrors(body),
        ),
      503 || 502 => ServiceUnavailableException(
          extractMessage('Service temporarily unavailable.'),
        ),
      _ => ServerException(
          statusCode: statusCode,
          message: extractMessage('An unexpected server error occurred.'),
        ),
    };
  }

  /// Parse Laravel's 422 `errors` key into `Map<String, List<String>>`.
  Map<String, List<String>> _extractValidationErrors(dynamic body) {
    if (body is! Map<String, dynamic>) return {};
    final rawErrors = body['errors'];
    if (rawErrors is! Map<String, dynamic>) return {};
    return rawErrors.map((key, value) {
      if (value is List) {
        return MapEntry(key, value.map((e) => e.toString()).toList());
      }
      return MapEntry(key, [value.toString()]);
    });
  }
}
