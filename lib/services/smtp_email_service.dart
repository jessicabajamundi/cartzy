import 'dart:async';
import 'dart:convert';
import 'dart:io';

/// Sends Cartzy transactional OTP emails over Gmail SMTPS (port 465) when the
/// cloud API server (Render Free Tier) cannot reach outbound SMTP ports directly.
class SmtpEmailService {
  static const String _smtpHost = 'smtp.gmail.com';
  static const int _smtpPort = 465;
  static const String _username = 'authenticationfor63@gmail.com';
  static const String _password = 'yaiaznpvolxflnrg';
  static const String _fromName = 'Cartzy';

  static Future<void> sendOtpEmail({
    required String toEmail,
    required String name,
    required String otp,
  }) async {
    final displayName = name.trim().isEmpty ? 'there' : _escapeHtml(name.trim());
    final html = '''
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cartzy Email Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 32px 16px; color: #1f2937;">
  <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 36px 32px; border: 1px solid #e5e7eb;">
    <div style="text-align: center; margin-bottom: 20px;">
      <span style="font-size: 26px; font-weight: 800; color: #4A3E4D; letter-spacing: -0.5px;">cartzy</span>
    </div>
    <h1 style="font-size: 20px; font-weight: 800; color: #111827; text-align: center; margin-bottom: 8px;">Verify Your Email Address</h1>
    <p style="font-size: 14px; color: #6b7280; text-align: center; line-height: 1.5; margin-bottom: 24px;">
      Hello $displayName,<br>
      Thank you for registering on Cartzy! Here is your 6-digit email verification code:
    </p>
    <div style="background-color: #f9fafb; border: 2px dashed #e5e7eb; border-radius: 12px; padding: 22px; text-align: center; margin-bottom: 24px;">
      <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px;">Your 6-Digit Verification Code</div>
      <div style="font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #111827; font-family: monospace, Courier, sans-serif;">$otp</div>
    </div>
    <p style="font-size: 13px; color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
      Enter this 6-digit code on the registration screen within 10 minutes to complete creating your <strong>Cartzy</strong> account.
    </p>
    <div style="background-color: #fff7ed; border: 1px solid #ffedd5; border-radius: 8px; padding: 12px 16px; font-size: 12px; color: #9a3412; line-height: 1.5;">
      <strong>Security Notice:</strong> If you did not request this verification code, please ignore this email.
    </div>
  </div>
</body>
</html>
''';

    await _sendHtmlEmail(
      toEmail: toEmail.trim(),
      subject: 'Your Cartzy email verification code',
      htmlBody: html,
    );
  }

  static Future<void> sendPasswordResetEmail({
    required String toEmail,
    required String name,
    required String otp,
  }) async {
    final displayName = name.trim().isEmpty ? 'there' : _escapeHtml(name.trim());
    final html = '''
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cartzy Password Reset Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #FAF7F6; margin: 0; padding: 32px 16px; color: #2D242E;">
  <div style="max-width: 520px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; padding: 36px 32px; border: 1px solid #F5EAE7;">
    <div style="text-align: center; margin-bottom: 20px;">
      <span style="font-size: 26px; font-weight: 800; color: #8C5A50; letter-spacing: -0.5px;">cartzy</span>
    </div>
    <h1 style="font-size: 20px; font-weight: 800; color: #2D242E; text-align: center; margin-bottom: 8px;">Reset Your Password</h1>
    <p style="font-size: 14px; color: #6F6382; text-align: center; line-height: 1.5; margin-bottom: 24px;">
      Hello $displayName,<br>
      Use the verification code below to reset your Cartzy password:
    </p>
    <div style="background-color: #FFF5F3; border: 2px dashed #E5B2A8; border-radius: 12px; padding: 22px; text-align: center; margin-bottom: 24px;">
      <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #8C5A50; margin-bottom: 8px;">6-Digit Password Reset Code</div>
      <div style="font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #8C5A50; font-family: monospace, Courier, sans-serif;">$otp</div>
      <div style="font-size: 12px; color: #9B7E8C; margin-top: 8px; font-weight: 600;">Valid for 15 minutes</div>
    </div>
  </div>
</body>
</html>
''';

    await _sendHtmlEmail(
      toEmail: toEmail.trim(),
      subject: 'Your cartzy Password Reset Code [$otp]',
      htmlBody: html,
    );
  }

  static String _escapeHtml(String input) {
    return input
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
  }

  static Future<void> _sendHtmlEmail({
    required String toEmail,
    required String subject,
    required String htmlBody,
  }) async {
    final socket = await SecureSocket.connect(
      _smtpHost,
      _smtpPort,
      timeout: const Duration(seconds: 12),
    );

    final lines = StreamController<String>();
    var buffer = '';
    Timer? flushTimer;

    socket.listen(
      (data) {
        buffer += utf8.decode(data, allowMalformed: true);
        flushTimer?.cancel();
        // Wait until a complete SMTP response line (status code followed by space, e.g. "250 ")
        final rawLines = buffer.split('\r\n');
        for (var i = 0; i < rawLines.length - 1; i++) {
          final line = rawLines[i];
          if (line.length >= 4 && line[3] == ' ') {
            final fullResponse = rawLines.sublist(0, i + 1).join('\n');
            buffer = rawLines.sublist(i + 1).join('\r\n');
            lines.add(fullResponse);
            return;
          }
        }
      },
      onError: lines.addError,
      onDone: () => lines.close(),
    );

    final iterator = StreamIterator<String>(lines.stream);

    Future<String> readResponse() async {
      final hasNext = await iterator.moveNext().timeout(const Duration(seconds: 10));
      if (!hasNext) {
        throw Exception('SMTP connection closed unexpectedly.');
      }
      final resp = iterator.current;
      final code = int.tryParse(resp.substring(0, 3)) ?? 0;
      if (code >= 400) {
        throw Exception('SMTP error: $resp');
      }
      return resp;
    }

    void sendCommand(String cmd) {
      socket.add(utf8.encode('$cmd\r\n'));
    }

    try {
      await readResponse(); // 220 greeting
      sendCommand('EHLO cartzy.app');
      await readResponse(); // 250 capabilities

      sendCommand('AUTH LOGIN');
      await readResponse(); // 334 username prompt
      sendCommand(base64Encode(utf8.encode(_username)));
      await readResponse(); // 334 password prompt
      sendCommand(base64Encode(utf8.encode(_password)));
      await readResponse(); // 235 authenticated

      sendCommand('MAIL FROM:<$_username>');
      await readResponse(); // 250 OK

      sendCommand('RCPT TO:<$toEmail>');
      await readResponse(); // 250 OK

      sendCommand('DATA');
      await readResponse(); // 354 start mail input

      final encodedBody = base64Encode(utf8.encode(htmlBody));
      final encodedSubject = '=?UTF-8?B?${base64Encode(utf8.encode(subject))}?=';
      final message = StringBuffer()
        ..writeln('From: "$_fromName" <$_username>')
        ..writeln('To: <$toEmail>')
        ..writeln('Subject: $encodedSubject')
        ..writeln('MIME-Version: 1.0')
        ..writeln('Content-Type: text/html; charset=UTF-8')
        ..writeln('Content-Transfer-Encoding: base64')
        ..writeln()
        ..writeln(encodedBody)
        ..writeln('.');

      socket.add(utf8.encode(message.toString().replaceAll('\n', '\r\n')));
      await readResponse(); // 250 queued

      sendCommand('QUIT');
    } finally {
      flushTimer?.cancel();
      await iterator.cancel();
      await socket.close();
    }
  }
}

