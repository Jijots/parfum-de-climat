/// Sealed class hierarchy for all application-level errors.
/// The API layer catches [DioException] and converts it to one of these typed
/// exceptions. Repositories and providers only throw [AppException] subtypes,
/// so UI code can switch exhaustively without leaking Dio implementation details.
/// Usage (in a provider):
///   try {
///     final user = await authRepository.login(email, password);
///   } on UnauthorisedException catch (e) {
///     // show wrong-credentials message
///   } on NetworkException catch (e) {
///     // show offline banner
///   } on AppException catch (e) {
///     // generic fallback
///   }

sealed class AppException implements Exception {
  const AppException(this.message);

  final String message;

  @override
  String toString() => '$runtimeType: $message';
}

// Network / connectivity

/// The device has no internet connection or the server is unreachable.
final class NetworkException extends AppException {
  const NetworkException([
    super.message = 'No internet connection. Please try again.',
  ]);
}

/// The request exceeded the configured timeout.
final class TimeoutException extends AppException {
  const TimeoutException([
    super.message = 'The request timed out. Please try again.',
  ]);
}

// HTTP status errors

/// HTTP 401 — missing or invalid Sanctum token.
final class UnauthorisedException extends AppException {
  const UnauthorisedException([
    super.message = 'Your session has expired. Please log in again.',
  ]);
}

/// HTTP 403 — authenticated but not allowed (e.g. non-admin accessing admin route).
final class ForbiddenException extends AppException {
  const ForbiddenException([
    super.message = 'You do not have permission to perform this action.',
  ]);
}

/// HTTP 404 — resource does not exist or does not belong to the current user.
final class NotFoundException extends AppException {
  const NotFoundException([super.message = 'The requested resource was not found.']);
}

/// HTTP 422 — Laravel validation failure. [errors] is the field → message map.
final class ValidationException extends AppException {
  const ValidationException({
    required this.errors,
    String message = 'Please correct the highlighted fields.',
  }) : super(message);

  /// Field-level error map returned by Laravel's 422 response.
  /// e.g. { 'email': ['The email has already been taken.'] }
  final Map<String, List<String>> errors;

  /// Convenience: first error message for a given [field].
  String? firstError(String field) => errors[field]?.firstOrNull;
}

/// HTTP 503 / 502 — Laravel returned a service unavailable (engine down, OWM down).
final class ServiceUnavailableException extends AppException {
  const ServiceUnavailableException([
    super.message = 'The service is temporarily unavailable. Please try again shortly.',
  ]);
}

/// Any other non-2xx HTTP response not matched above.
final class ServerException extends AppException {
  const ServerException({
    required this.statusCode,
    String message = 'An unexpected server error occurred.',
  }) : super(message);

  final int statusCode;
}

// Location

/// The user denied location permission.
final class LocationPermissionDeniedException extends AppException {
  const LocationPermissionDeniedException([
    super.message = 'Location permission is required to get weather-based recommendations.',
  ]);
}

/// Location services are disabled in device settings.
final class LocationServiceDisabledException extends AppException {
  const LocationServiceDisabledException([
    super.message = 'Please enable location services in your device settings.',
  ]);
}

// Generic

/// Catch-all for unexpected errors not covered by the typed exceptions above.
final class UnexpectedException extends AppException {
  const UnexpectedException([super.message = 'An unexpected error occurred.']);
}
