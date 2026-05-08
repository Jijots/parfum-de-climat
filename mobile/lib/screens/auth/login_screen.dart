/// Auth screen: email + password → Sanctum token.
/// Layout:
///   - Full-screen warm background
///   - Centred, constrained (max 400 px wide) — works on web + mobile
///   - NeuCard wrapping the form — glass-like feel without BackdropFilter cost
///   - NeuButton for submit with loading state
///   - Server-error banner (glass, accent-red border)
/// On success the GoRouter redirect fires (authChangeNotifier.notifyLogin)
/// and navigates to /recommend — no manual push needed.

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../app/router.dart';
import '../../app/theme.dart';
import '../../core/errors/app_exception.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../widgets/neu_card.dart';
import '../../widgets/neu_button.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey      = GlobalKey<FormState>();
  final _emailCtrl    = TextEditingController();
  final _passwordCtrl = TextEditingController();

  bool    _obscurePassword = true;
  String? _serverError;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _serverError = null);

    try {
      await ref.read(authNotifierProvider.notifier).login(
            email:    _emailCtrl.text.trim(),
            password: _passwordCtrl.text,
          );
    } on ValidationException catch (e) {
      if (mounted) {
        setState(() => _serverError =
            e.firstError('email') ?? e.firstError('password') ?? e.message);
      }
    } on AppException catch (e) {
      if (mounted) setState(() => _serverError = e.message);
    } catch (_) {
      if (mounted) {
        setState(() => _serverError = 'An unexpected error occurred.');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = ref.watch(authNotifierProvider).isLoading;

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 48),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 400),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Brand watermark
                  Text(
                    'PARFUM DE CLIMAT',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.cormorantGaramond(
                      fontSize:      11,
                      fontWeight:    FontWeight.w400,
                      letterSpacing: 4,
                      color:         context.muted,
                    ),
                  ),
                  const SizedBox(height: 40),

                  // Form card
                  NeuCard(
                    padding: const EdgeInsets.all(32),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Text(
                            'Welcome back.',
                            textAlign: TextAlign.center,
                            style: AppTextStyles.displayMedium(context.inkColor),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Sign in to your collection.',
                            textAlign: TextAlign.center,
                            style: AppTextStyles.bodySmall(context.muted),
                          ),
                          const SizedBox(height: 32),

                          // Server error
                          if (_serverError != null) ...[
                            _ErrorBanner(message: _serverError!),
                            const SizedBox(height: 20),
                          ],

                          // Email
                          _fieldLabel('EMAIL', context),
                          const SizedBox(height: 6),
                          _NeuInput(
                            controller:    _emailCtrl,
                            keyboardType:  TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            autocorrect:   false,
                            validator: (v) {
                              if (v == null || v.trim().isEmpty) {
                                return 'Email is required.';
                              }
                              if (!v.contains('@')) {
                                return 'Enter a valid email.';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 20),

                          // Password
                          _fieldLabel('PASSWORD', context),
                          const SizedBox(height: 6),
                          _NeuInput(
                            controller:      _passwordCtrl,
                            obscureText:     _obscurePassword,
                            textInputAction: TextInputAction.done,
                            onFieldSubmitted: (_) => _submit(),
                            validator: (v) {
                              if (v == null || v.isEmpty) {
                                return 'Password is required.';
                              }
                              return null;
                            },
                            suffix: IconButton(
                              icon:  Icon(
                                _obscurePassword
                                    ? Icons.visibility_outlined
                                    : Icons.visibility_off_outlined,
                                size:  18,
                                color: context.muted,
                              ),
                              onPressed: () => setState(
                                  () => _obscurePassword = !_obscurePassword),
                            ),
                          ),
                          const SizedBox(height: 32),

                          // Submit
                          NeuButton(
                            label:     'Sign in',
                            loading:   isLoading,
                            onPressed: isLoading ? null : _submit,
                            width:     double.infinity,
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Register link
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        "Don't have an account?",
                        style: AppTextStyles.bodySmall(context.muted),
                      ),
                      TextButton(
                        onPressed: () => context.go(AppRoutes.register),
                        child: Text(
                          'Create one',
                          style: AppTextStyles.bodySmall(AppColors.accent),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // Theme toggle
                  const Center(child: ThemeToggleButton()),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// Shared helpers

Widget _fieldLabel(String label, BuildContext context) => Text(
      label,
      style: AppTextStyles.labelSmall(context.muted),
    );

class _NeuInput extends StatelessWidget {
  const _NeuInput({
    required this.controller,
    this.keyboardType,
    this.textInputAction,
    this.obscureText = false,
    this.autocorrect = true,
    this.onFieldSubmitted,
    this.validator,
    this.suffix,
  });

  final TextEditingController controller;
  final TextInputType?         keyboardType;
  final TextInputAction?       textInputAction;
  final bool                   obscureText;
  final bool                   autocorrect;
  final ValueChanged<String>?  onFieldSubmitted;
  final FormFieldValidator<String>? validator;
  final Widget?                suffix;

  @override
  Widget build(BuildContext context) {
    final neu = context.neu;

    return TextFormField(
      controller:       controller,
      keyboardType:     keyboardType,
      textInputAction:  textInputAction,
      obscureText:      obscureText,
      autocorrect:      autocorrect,
      onFieldSubmitted: onFieldSubmitted,
      validator:        validator,
      style:            AppTextStyles.bodyMedium(context.inkColor),
      decoration: InputDecoration(
        filled:    true,
        fillColor: neu.cardColor,
        suffixIcon: suffix,
        // Override global input theme with neumorphic inset shadow.
        enabledBorder: OutlineInputBorder(
          borderSide:   BorderSide.none,
          borderRadius: BorderRadius.circular(10),
        ),
        focusedBorder: OutlineInputBorder(
          borderSide:   const BorderSide(color: AppColors.accent, width: 1.5),
          borderRadius: BorderRadius.circular(10),
        ),
        errorBorder: OutlineInputBorder(
          borderSide:   BorderSide(color: context.errorColor),
          borderRadius: BorderRadius.circular(10),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderSide:   BorderSide(color: context.errorColor, width: 1.5),
          borderRadius: BorderRadius.circular(10),
        ),
      ),
      // Paint the neumorphic inset box-shadow on the input's container.
      // There is no direct way to set BoxShadow on InputDecoration, so we
      // wrap the field in a Container with the shadow and use transparent
      // fill on the field itself.
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color:        context.errorColor.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border:       Border.all(color: context.errorColor.withValues(alpha: 0.25)),
      ),
      child: Text(
        message,
        style: AppTextStyles.bodySmall(context.errorColor),
      ),
    );
  }
}
