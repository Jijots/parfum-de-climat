/// Single source of truth for the active [ThemeMode].
/// Defaults to [ThemeMode.system] so the app inherits the device setting.
/// The user can override via the settings icon in the app header.
/// The selection is not persisted yet — re-opens default to system.

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/material.dart';

final themeModeProvider = StateProvider<ThemeMode>((ref) => ThemeMode.system);
