<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cartzy Email Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px 16px;
            color: #1f2937;
        }
        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px 36px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .brand-name {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }
        .header-title {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            text-align: center;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 28px;
        }
        .otp-box-wrap {
            background-color: #f9fafb;
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin-bottom: 28px;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .otp-code {
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 8px;
            color: #111827;
            font-family: monospace, Courier, sans-serif;
        }
        .meta-notice {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .security-badge {
            background-color: #fff7ed;
            border: 1px solid #ffedd5;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12px;
            color: #9a3412;
            line-height: 1.5;
        }
        .footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-row">
            <img src="{{ asset('images/logo.png') }}" alt="cartzy" style="height: 38px; width: auto; display: block; margin: 0 auto;">
        </div>

        <h1 class="header-title">Verify Your Email Address</h1>
        <p class="subtitle">
            Hello {{ $name ?? 'there' }},<br>
            Thank you for registering on Cartzy! Here is your 6-digit email verification code:
        </p>

        <div class="otp-box-wrap">
            <div class="otp-label">Your 6-Digit Verification Code</div>
            <div class="otp-code">{{ $otp }}</div>
        </div>

        <p class="meta-notice">
            Enter this 6-digit code on the registration screen within 15 minutes to complete creating your <strong>Cartzy</strong> account.
        </p>

        <div class="security-badge">
            🔒 <strong>Security Notice:</strong> If you did not request this verification code, please ignore this email.
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Cartzy Marketplace. All rights reserved.
        </div>
    </div>
</body>
</html>
