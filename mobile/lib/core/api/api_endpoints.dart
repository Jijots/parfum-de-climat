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
  //                                     (simulator shares the Mac host's network stack,
  //                                      so the .local mDNS name resolves correctly)
  //   Android emulator                → http://10.0.2.2/api/v1
  //                                     (10.0.2.2 is the emulator's alias for host localhost)
  //   Physical Android phone (Wi-Fi)  → http://<your-machine-LAN-IP>/api/v1
  //                                     Find the IP with `ipconfig` (Windows) or
  //                                     `ifconfig | grep 192.168` (Mac/Linux).
  //                                     The debug Network Security Config already
  //                                     permits plain HTTP to any host, so no extra
  //                                     Android config is needed.
  //   Physical iPhone (Wi-Fi)         → http://<your-machine-LAN-IP>/api/v1
  //                                     Add NSAllowsArbitraryLoads=true to Info.plist
  //                                     for dev builds, or set up HTTPS on the backend.
  // Injected at build time via --dart-define=BACKEND_URL=http://192.168.x.x
  // Falls back to parfum.local for local simulator / web development.
  static const String baseUrl = String.fromEnvironment(
    'BACKEND_URL',
    defaultValue: 'https://parfumdeclimat.app/api/v1',
  );

  /// Laravel Storage origin — derived from BACKEND_URL automatically.
  static const String storageBaseUrl = String.fromEnvironment(
    'STORAGE_URL',
    defaultValue: 'https://parfumdeclimat.app/storage',
  );

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
