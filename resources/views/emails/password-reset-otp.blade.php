<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your cartzy Password Reset Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #FAF7F6;
            margin: 0;
            padding: 40px 16px;
            color: #2D242E;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 20px;
            padding: 42px 36px;
            box-shadow: 0 10px 30px rgba(140, 90, 80, 0.08);
            border: 1px solid #F5EAE7;
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 26px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #8C5A50 0%, #C08B7F 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        .icon-badge {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFF5F3 0%, #FBEBE8 100%);
            border: 1.5px solid #F5CFC7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            color: #8C5A50;
            font-size: 26px;
            line-height: 56px;
            text-align: center;
        }
        .header-title {
            font-size: 22px;
            font-weight: 800;
            color: #2D242E;
            text-align: center;
            margin: 0 0 8px;
        }
        .subtitle {
            font-size: 14px;
            color: #6F6382;
            text-align: center;
            line-height: 1.6;
            margin: 0 0 28px;
        }
        .otp-card {
            background: linear-gradient(135deg, #FFF5F3 0%, #FBF3F5 100%);
            border: 2px dashed #E5B2A8;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 26px;
        }
        .otp-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #8C5A50;
            margin-bottom: 8px;
        }
        .otp-code {
            font-size: 40px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #8C5A50;
            font-family: 'Courier New', Courier, monospace;
            padding-left: 10px;
        }
        .expire-text {
            font-size: 12px;
            color: #9B7E8C;
            margin-top: 8px;
            font-weight: 600;
        }
        .instructions {
            font-size: 13px;
            color: #554859;
            line-height: 1.6;
            margin-bottom: 24px;
            text-align: center;
        }
        .security-notice {
            background-color: #FFF9F7;
            border-left: 4px solid #C08B7F;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12px;
            color: #6F6382;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .footer {
            padding-top: 20px;
            border-top: 1px solid #F0E6E4;
            text-align: center;
            font-size: 12px;
            color: #9B8F9A;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrap">
            <span class="logo-text">cartzy</span>
        </div>

        <div class="icon-badge">🔒</div>

        <h1 class="header-title">Reset Your Password</h1>
        <p class="subtitle">
            Hello {{ $name ?? 'there' }},<br>
            We received a request to reset the password for your cartzy account. Use the verification code below to proceed:
        </p>

        <div class="otp-card">
            <div class="otp-label">6-Digit Password Reset Code</div>
            <div class="otp-code">{{ $otp }}</div>
            <div class="expire-text">Valid for 15 minutes</div>
        </div>

        <p class="instructions">
            Enter this code on the password reset screen to verify your identity and set a new password.
        </p>

        <div class="security-notice">
            <strong>Security Alert:</strong> If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged and your account is secure.
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} cartzy Marketplace. All rights reserved.
        </div>
    </div>
</body>
</html>
