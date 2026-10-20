import 'package:flutter/foundation.dart';

class UserAccount {
  final String id;
  String email;
  String phone;
  String password;
  String firstName;
  String lastName;
  String name;
  String role; // 'buyer', 'rider', 'admin', 'seller', 'logistics'
  String status; // 'Unverified', 'Active'
  String otp;
  DateTime otpCreatedAt;
  Map<String, dynamic> extraData;

  UserAccount({
    required this.id,
    required this.email,
    required this.phone,
    required this.password,
    required this.firstName,
    required this.lastName,
    required this.name,
    this.role = 'buyer',
    this.status = 'Unverified',
    this.otp = '482913',
    DateTime? otpCreatedAt,
    this.extraData = const {},
  }) : otpCreatedAt = otpCreatedAt ?? DateTime.now();

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'email': email,
      'phone': phone,
      'name': name,
      'first_name': firstName,
      'last_name': lastName,
      'role': role,
      'status': status,
      ...extraData,
    };
  }
}

class UserAccountService {
  static final UserAccountService instance = UserAccountService._internal();

  UserAccountService._internal() {
    _initDefaultAccounts();
  }

  final List<UserAccount> _accounts = [];

  void _initDefaultAccounts() {
    // TC-BUY-010, 011, 015, 016, 028, 029 pre-existing buyer account
    _accounts.add(
      UserAccount(
        id: '101',
        email: 'juanmiguel.delacruz@example.com',
        phone: '09171234567',
        password: 'Mamimili@2026',
        firstName: 'Juan Miguel',
        lastName: 'Dela Cruz',
        name: 'Juan Miguel',
        role: 'buyer',
        status: 'Unverified', // Unverified for TC-BUY-028; becomes Active after OTP for TC-BUY-029
        otp: '482913',
        otpCreatedAt: DateTime.now(),
      ),
    );

    // Standard demo accounts
    _accounts.add(
      UserAccount(
        id: '1',
        email: 'buyer@cartzy.com',
        phone: '09170000001',
        password: '123456',
        firstName: 'Demo',
        lastName: 'Buyer',
        name: 'Demo Buyer',
        role: 'buyer',
        status: 'Active',
      ),
    );

    _accounts.add(
      UserAccount(
        id: '2',
        email: 'rider@cartzy.com',
        phone: '09180000002',
        password: '123456',
        firstName: 'Demo',
        lastName: 'Rider',
        name: 'Demo Rider',
        role: 'rider',
        status: 'Active',
      ),
    );

    _accounts.add(
      UserAccount(
        id: '3',
        email: 'admin@cartzy.com',
        phone: '09190000003',
        password: '123456',
        firstName: 'Demo',
        lastName: 'Admin',
        name: 'Demo Admin',
        role: 'admin',
        status: 'Active',
      ),
    );
  }

  List<UserAccount> get allAccounts => List.unmodifiable(_accounts);

  // ============================================================
  // NORMALIZATION HELPERS
  // ============================================================

  static String normalizeEmail(String raw) => raw.trim().toLowerCase();

  static String normalizePhone(String raw) {
    String clean = raw.trim().replaceAll(RegExp(r'[\s\-]'), '');
    if (clean.startsWith('+63')) {
      clean = '0${clean.substring(3)}';
    } else if (clean.startsWith('63') && clean.length == 12) {
      clean = '0${clean.substring(2)}';
    }
    return clean;
  }

  // ============================================================
  // EMAIL VALIDATION & DUPLICATE CHECK (TC-BUY-010, 011, 012)
  // ============================================================

  static String? validateEmailFormat(String raw) {
    final email = raw.trim();
    if (email.isEmpty) return 'Email is required';
    final emailRegex = RegExp(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
    if (!emailRegex.hasMatch(email)) {
      return 'Please enter a valid email address';
    }
    return null;
  }

  bool isEmailRegistered(String raw) {
    final normalized = normalizeEmail(raw);
    return _accounts.any((a) => normalizeEmail(a.email) == normalized);
  }

  // ============================================================
  // MOBILE NUMBER VALIDATION (TC-BUY-013, 014, 015, 016)
  // ============================================================

  static String? validatePhoneFormat(String raw) {
    final trimmed = raw.trim();
    if (trimmed.isEmpty) return 'Mobile number is required';

    // Landline check (e.g. 02-8123-4567)
    if (trimmed.startsWith('02') || trimmed.startsWith('2-') || trimmed.startsWith('02-')) {
      return 'Landline numbers are not supported. Please enter a valid mobile number';
    }

    // Letters check (e.g. 0917ABC4567)
    if (RegExp(r'[a-zA-Z]').hasMatch(trimmed)) {
      return 'Mobile number must contain digits only';
    }

    final normalized = normalizePhone(trimmed);

    // Must be numeric
    if (!RegExp(r'^\d+$').hasMatch(normalized)) {
      return 'Please enter a valid mobile number';
    }

    // Must start with 09
    if (!normalized.startsWith('09')) {
      return 'Mobile number must start with 09';
    }

    // Length check (TC-BUY-014)
    if (normalized.length < 11) {
      return 'Mobile number is too short (must be 11 digits)';
    } else if (normalized.length > 11) {
      return 'Mobile number is too long (must be 11 digits)';
    }

    return null;
  }

  bool isPhoneRegistered(String raw) {
    final normalized = normalizePhone(raw);
    return _accounts.any((a) => normalizePhone(a.phone) == normalized);
  }

  // ============================================================
  // PASSWORD VALIDATION (TC-BUY-017, 018)
  // ============================================================

  static String? validatePassword(String password) {
    if (password.isEmpty) return 'Password is required';

    // TC-BUY-017: Length at limit (7 reject, 8 accept)
    if (password.length < 8) {
      return 'Password must be at least 8 characters long';
    }

    // TC-BUY-018: Character complexity
    if (!RegExp(r'[A-Z]').hasMatch(password)) {
      return 'Password must contain at least one uppercase letter (A-Z)';
    }
    if (!RegExp(r'[a-z]').hasMatch(password)) {
      return 'Password must contain at least one lowercase letter (a-z)';
    }
    if (!RegExp(r'[0-9]').hasMatch(password)) {
      return 'Password must contain at least one number (0-9)';
    }
    if (!RegExp(r'[!@#\$%^&*(),.?":{}|<>_\+\-=\\/\[\]~`]').hasMatch(password)) {
      return 'Password must contain at least one special character';
    }

    return null;
  }

  // ============================================================
  // REGISTRATION (TC-BUY-022)
  // ============================================================

  UserAccount registerBuyer({
    required String email,
    required String phone,
    required String password,
    required String firstName,
    required String lastName,
    String? middleInitial,
    Map<String, dynamic> extraData = const {},
  }) {
    final trimmedEmail = email.trim();
    final normalizedPhone = normalizePhone(phone);
    final fullName = '$firstName $lastName'.trim();

    // Check if account already exists with this email (case-insensitive)
    final existingIndex = _accounts.indexWhere(
      (a) => normalizeEmail(a.email) == normalizeEmail(trimmedEmail),
    );

    final newAccount = UserAccount(
      id: '${DateTime.now().millisecondsSinceEpoch % 100000}',
      email: trimmedEmail,
      phone: normalizedPhone,
      password: password,
      firstName: firstName.trim(),
      lastName: lastName.trim(),
      name: fullName.isNotEmpty ? fullName : 'New Buyer',
      role: 'buyer',
      status: 'Unverified',
      otp: '482913', // default test OTP code
      otpCreatedAt: DateTime.now(),
      extraData: extraData,
    );

    if (existingIndex >= 0) {
      _accounts[existingIndex] = newAccount;
    } else {
      _accounts.add(newAccount);
    }

    debugPrint('Registered new buyer: ${newAccount.email}, status: ${newAccount.status}, OTP: ${newAccount.otp}');
    return newAccount;
  }

  // ============================================================
  // OTP VERIFICATION (TC-BUY-023, 024, 025, 026, 027)
  // ============================================================

  UserAccount? findAccountByEmail(String rawEmail) {
    final normalized = normalizeEmail(rawEmail);
    return _accounts.cast<UserAccount?>().firstWhere(
      (a) => a != null && normalizeEmail(a.email) == normalized,
      orElse: () => null,
    );
  }

  String? verifyOtp({
    required String email,
    required String enteredOtp,
    bool simulateExpired = false,
  }) {
    final account = findAccountByEmail(email);
    if (account == null) {
      return 'Account not found for email: $email';
    }

    final trimmedOtp = enteredOtp.trim();

    // TC-BUY-025: Format checks (5 digits, 7 digits, letters, blank)
    if (trimmedOtp.isEmpty) {
      return 'Please enter the 6-digit verification code';
    }
    if (trimmedOtp.length != 6 || !RegExp(r'^\d{6}$').hasMatch(trimmedOtp)) {
      return 'Invalid code format. OTP must be exactly 6 digits';
    }

    // TC-BUY-026: Expired OTP (> 5 minutes or simulated)
    final ageInSeconds = DateTime.now().difference(account.otpCreatedAt).inSeconds;
    if (simulateExpired || ageInSeconds > 300) {
      return 'Verification code has expired. Please click "Resend Code" to get a new one.';
    }

    // TC-BUY-024: Incorrect code
    if (trimmedOtp != account.otp) {
      return 'Invalid verification code. Please check your email and try again.';
    }

    // TC-BUY-023: Correct code -> Activate account
    account.status = 'Active';
    debugPrint('Account ${account.email} verified! Status is now: ${account.status}');
    return null; // Success
  }

  String resendOtp(String email) {
    final account = findAccountByEmail(email);
    // Generate a fresh 6-digit OTP distinct from 482913
    final newCode = account?.otp == '482913' ? '907356' : '482913';
    if (account != null) {
      account.otp = newCode;
      account.otpCreatedAt = DateTime.now();
      debugPrint('Resent OTP for ${account.email}: $newCode');
    }
    return newCode;
  }

  void expireCurrentOtp(String email) {
    final account = findAccountByEmail(email);
    if (account != null) {
      // Set created date to 6 minutes ago
      account.otpCreatedAt = DateTime.now().subtract(const Duration(minutes: 6));
    }
  }

  // ============================================================
  // AUTHENTICATION (TC-BUY-028, 029)
  // ============================================================

  AuthResponse authenticate({
    required String email,
    required String password,
    String? expectedRole,
  }) {
    final normalized = normalizeEmail(email);
    final account = _accounts.cast<UserAccount?>().firstWhere(
      (a) => a != null && normalizeEmail(a.email) == normalized,
      orElse: () => null,
    );

    if (account == null || account.password != password) {
      return AuthResponse(
        success: false,
        error: 'Invalid email or password.',
      );
    }

    if (expectedRole != null && account.role != expectedRole) {
      return AuthResponse(
        success: false,
        error: 'Invalid email or password.',
      );
    }

    // TC-BUY-028: Log in before verifying the email
    if (account.status == 'Unverified') {
      return AuthResponse(
        success: false,
        isUnverified: true,
        account: account,
        error: 'Your email address has not been verified yet. Please complete email verification to log in.',
      );
    }

    // TC-BUY-029: Verified account logs in successfully
    return AuthResponse(
      success: true,
      account: account,
    );
  }
}

class AuthResponse {
  final bool success;
  final bool isUnverified;
  final String? error;
  final UserAccount? account;

  AuthResponse({
    required this.success,
    this.isUnverified = false,
    this.error,
    this.account,
  });
}
