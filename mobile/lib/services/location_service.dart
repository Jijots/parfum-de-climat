/// Wrapper around [Geolocator] that handles the permission request flow
/// and converts platform errors into typed [AppException] subtypes.
/// The permission flow:
///   1. Check current permission status.
///   2. If [denied], request it — this shows the system dialog on first use.
///   3. If [deniedForever], direct the user to device settings.
///   4. If location services are disabled, ask the user to enable them.
/// The [currentPosition] method is the only public API — it handles the full
/// flow internally so callers (providers) only need to catch [AppException].

import 'package:flutter_riverpod/flutter_riverpod.dart';
// Hide geolocator's own LocationServiceDisabledException so our typed
// AppException subclass (app_exception.dart) is used instead.
import 'package:geolocator/geolocator.dart'
    hide LocationServiceDisabledException;

import '../core/errors/app_exception.dart';

class LocationService {
  /// Return the device's current GPS [Position].
  /// Requests permission if needed. Throws [AppException] on failure:
  ///   - [LocationServiceDisabledException] — GPS turned off in device settings
  ///   - [LocationPermissionDeniedException] — user denied permanently
  /// Uses [LocationAccuracy.high] for the best coordinate precision.
  /// The [timeLimit] of 15 s prevents hanging if GPS takes too long to acquire.
  Future<Position> currentPosition() async {
    // Check / request permission
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw const LocationServiceDisabledException();
    }

    LocationPermission permission = await Geolocator.checkPermission();

    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();

      if (permission == LocationPermission.denied) {
        // User dismissed the dialog — treat as soft denial.
        throw const LocationPermissionDeniedException(
          'Location permission is required to fetch weather data.',
        );
      }
    }

    if (permission == LocationPermission.deniedForever) {
      // The user has permanently denied permission.
      // Geolocator.openAppSettings() can be called from the UI to deep-link
      // to the app's settings page.
      throw const LocationPermissionDeniedException(
        'Location permission is permanently denied. '
        'Enable it in your device settings.',
      );
    }

    // Acquire position
    try {
      return await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 15),
        ),
      );
    } on LocationServiceDisabledException {
      throw const LocationServiceDisabledException();
    } catch (e) {
      throw UnexpectedException('Failed to get location: $e');
    }
  }

  /// Open the device's app settings page so the user can grant permission.
  /// Call this from the UI when [LocationPermissionDeniedException.message]
  /// mentions "permanently denied".
  Future<bool> openSettings() => Geolocator.openAppSettings();
}

// Provider

final locationServiceProvider = Provider<LocationService>(
  (_) => LocationService(),
);
