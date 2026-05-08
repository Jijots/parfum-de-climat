/// Temporary scaffold for routes that have not been built yet (Step 4).
/// Replaced screen-by-screen in Step 5 (UI implementation).
/// Each placeholder renders the route name so you can verify navigation
/// works correctly before the real UI is ready.

import 'package:flutter/material.dart';
import '../app/theme.dart';

class PlaceholderScreen extends StatelessWidget {
  const PlaceholderScreen({super.key, required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              title,
              style: AppTextStyles.displayMedium(context.inkColor),
            ),
            const SizedBox(height: 8),
            Text(
              'Coming in Step 5',
              style: AppTextStyles.bodySmall(context.muted),
            ),
          ],
        ),
      ),
    );
  }
}
