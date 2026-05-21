/// All Laravel API route constants in one place.
/// Update [baseUrl] for each environment — never hardcode URLs in repositories.
/// Usage:
///   dio.post(ApiEndpoints.login, data: {...})
///   dio.get(ApiEndpoints.fragranceDetail(id))

class ApiEndpoints {
  ApiEndpoints._();

  // Base URL
  // Switch the active URL based on your current target:
  //   Flutter WEB or Windows desktop  → http://parfum.local/api/v1
  //   iOS Simulator                   → http://parfum.local/api/v1
  //                                     (simulator routes through the Mac host's network stack,
  //                                      so the same .local mDNS hostname resolves correctly)
  //   Android emulator                → http://10.0.2.2/api/v1
  //                                     (10.0.2.2 is the emulator's alias for host localhost)
  //   Physical device (iOS/Android)   → use your machine's LAN IP (e.g. http://192.168.x.x/api/v1)
  //                                     On iOS, also add NSAllowsArbitraryLoads=true in Info.plist
  //                                     for dev, or set up HTTPS on the backend.
  static const String baseUrl =
      'http://parfum.local/api/v1';

  /// Laravel Storage origin — used to build full image URLs from relative paths.
  static const String storageBaseUrl =
      'http://parfum.local/storage';

  // Auth
  static const String register = '/auth/register';
  static const String login    = '/auth/login';
  static const String logout   = '/auth/logout';
  static const String me       = '/auth/me';

  // Fragrances (public browse)
  static const String fragrances = '/fragrances';
  static String fragranceDetail(int id) => '/fragrances/$id';

  // User collection
  static const String collection = '/collection';
  static String collectionItem(int fragranceId) => '/collection/$fragranceId';

  // Recommendations
  static const String recommend        = '/recommend';
  static const String recommendHistory = '/recommend/history';
  static String chooseFragrance(int logId) => '/recommend/$logId/choose';

  // Admin
  // These are only reachable by users with role='admin'.
  static const String adminFragrances     = '/admin/fragrances';
  static const String adminUnmappedNotes  = '/admin/fragrances/unmapped-notes';
  static const String adminNotes          = '/admin/notes';
  static String adminMapNote(int noteId)  => '/admin/fragrances/notes/$noteId/map';
}
