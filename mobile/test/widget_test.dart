// Smoke test — verifies the app renders without crashing.
// Full feature tests will be added in a later step.
//
// Note: generated files (*.freezed.dart, *.g.dart) must exist before running
// this test. Run: dart run build_runner build --delete-conflicting-outputs

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:parfum_de_climat/main.dart';

void main() {
  testWidgets('App renders without crashing', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(child: ParfumDeClimatApp()),
    );
    // If no exception is thrown during pump, the test passes.
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
