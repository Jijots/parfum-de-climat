/// Registration screen: name + email + password → new account + Sanctum token.

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

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey             = GlobalKey<FormState>();
  final _nameCtrl            = TextEditingController();
  final _emailCtrl           = TextEditingController();
  final _passwordCtrl        = TextEditingController();
  final _confirmPasswordCtrl = TextEditingController();

  bool    _obscure        = true;
  bool    _obscureConfirm = true;
  String? _serverError;
  Map<String, List<String>> _fieldErrors = {};

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    _confirmPasswordCtrl.dispose();
    super.dispose();
  }

  String? _fieldError(String key) => _fieldErrors[key]?.firstOrNull;

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _serverError = null;
      _fieldErrors = {};
    });

    try {
      await ref.read(authNotifierProvider.notifier).register(
            name:                 _nameCtrl.text.trim(),
            email:                _emailCtrl.text.trim(),
            password:             _passwordCtrl.text,
            passwordConfirmation: _confirmPasswordCtrl.text,
          );
    } on ValidationException catch (e) {
      if (mounted) {
        setState(() {
          _fieldErrors = e.errors;
          _serverError = e.errors.isEmpty ? e.message : null;
        });
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
                  Text(
                    'PARFUM DE CLIMAT',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.cormorantGaramond(
                      fontSize: 11, fontWeight: FontWeight.w400,
                      letterSpacing: 4, color: context.muted,
                    ),
                  ),
                  const SizedBox(height: 40),

                  NeuCard(
                    padding: const EdgeInsets.all(32),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Text(
                            'Create account.',
                            textAlign: TextAlign.center,
                            style: AppTextStyles.displayMedium(context.inkColor),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Build your fragrance wardrobe.',
                            textAlign: TextAlign.center,
                            style: AppTextStyles.bodySmall(context.muted),
                          ),
                          const SizedBox(height: 32),

                          if (_serverError != null) ...[
                            _ErrorBanner(message: _serverError!),
                            const SizedBox(height: 20),
                          ],

                          _label('NAME', context),
                          const SizedBox(height: 6),
                          _Field(
                            controller:      _nameCtrl,
                            textInputAction: TextInputAction.next,
                            errorText:       _fieldError('name'),
                            validator: (v) => (v == null || v.trim().isEmpty)
                                ? 'Name is required.'
                                : null,
                          ),
                          const SizedBox(height: 20),

                          _label('EMAIL', context),
                          const SizedBox(height: 6),
                          _Field(
                            controller:      _emailCtrl,
                            keyboardType:    TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            errorText:       _fieldError('email'),
                            validator: (v) {
                              if (v == null || v.trim().isEmpty) return 'Email is required.';
                              if (!v.contains('@')) return 'Enter a valid email.';
                              return null;
                            },
                          ),
                          const SizedBox(height: 20),

                          _label('PASSWORD', context),
                          const SizedBox(height: 6),
                          _Field(
                            controller:      _passwordCtrl,
                            obscureText:     _obscure,
                            textInputAction: TextInputAction.next,
                            errorText:       _fieldError('password'),
                            suffixIcon: IconButton(
                              icon: Icon(_obscure
                                  ? Icons.visibility_outlined
                                  : Icons.visibility_off_outlined,
                                  size: 18, color: context.muted),
                              onPressed: () => setState(() => _obscure = !_obscure),
                            ),
                            validator: (v) => (v == null || v.length < 8)
                                ? 'At least 8 characters.'
                                : null,
                          ),
                          const SizedBox(height: 20),

                          _label('CONFIRM PASSWORD', context),
                          const SizedBox(height: 6),
                          _Field(
                            controller:      _confirmPasswordCtrl,
                            obscureText:     _obscureConfirm,
                            textInputAction: TextInputAction.done,
                            onFieldSubmitted: (_) => _submit(),
                            suffixIcon: IconButton(
                              icon: Icon(_obscureConfirm
                                  ? Icons.visibility_outlined
                                  : Icons.visibility_off_outlined,
                                  size: 18, color: context.muted),
                              onPressed: () =>
                                  setState(() => _obscureConfirm = !_obscureConfirm),
                            ),
                            validator: (v) => (v != _passwordCtrl.text)
                                ? 'Passwords do not match.'
                                : null,
                          ),
                          const SizedBox(height: 32),

                          NeuButton(
                            label:     'Create account',
                            loading:   isLoading,
                            onPressed: isLoading ? null : _submit,
                            width:     double.infinity,
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('Already have an account?',
                          style: AppTextStyles.bodySmall(context.muted)),
                      TextButton(
                        onPressed: () => context.go(AppRoutes.login),
                        child: Text('Sign in',
                            style: AppTextStyles.bodySmall(AppColors.accent)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
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

// Internal helpers

Widget _label(String text, BuildContext context) =>
    Text(text, style: AppTextStyles.labelSmall(context.muted));

class _Field extends StatelessWidget {
  const _Field({
    required this.controller,
    this.keyboardType,
    this.textInputAction,
    this.obscureText      = false,
    this.onFieldSubmitted,
    this.validator,
    this.suffixIcon,
    this.errorText,
  });

  final TextEditingController      controller;
  final TextInputType?              keyboardType;
  final TextInputAction?            textInputAction;
  final bool                        obscureText;
  final ValueChanged<String>?       onFieldSubmitted;
  final FormFieldValidator<String>? validator;
  final Widget?                     suffixIcon;
  final String?                     errorText;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller:       controller,
      keyboardType:     keyboardType,
      textInputAction:  textInputAction,
      obscureText:      obscureText,
      onFieldSubmitted: onFieldSubmitted,
      validator:        validator,
      style:            AppTextStyles.bodyMedium(context.inkColor),
      decoration: InputDecoration(
        filled:     true,
        fillColor:  context.neu.cardColor,
        errorText:  errorText,
        suffixIcon: suffixIcon,
        enabledBorder: OutlineInputBorder(
            borderSide: BorderSide.none,
            borderRadius: BorderRadius.circular(10)),
        focusedBorder: OutlineInputBorder(
            borderSide: const BorderSide(color: AppColors.accent, width: 1.5),
            borderRadius: BorderRadius.circular(10)),
        errorBorder: OutlineInputBorder(
            borderSide: BorderSide(color: context.errorColor),
            borderRadius: BorderRadius.circular(10)),
        focusedErrorBorder: OutlineInputBorder(
            borderSide: BorderSide(color: context.errorColor, width: 1.5),
            borderRadius: BorderRadius.circular(10)),
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message});
  final String message;
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color:        context.errorColor.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(8),
          border:       Border.all(color: context.errorColor.withValues(alpha: 0.25)),
        ),
        child: Text(message, style: AppTextStyles.bodySmall(context.errorColor)),
      );
}
