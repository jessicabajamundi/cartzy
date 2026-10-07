<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Decision · Cartzy</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f6f4f8;
            margin: 0;
            padding: 40px 16px;
            color: #282133;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 4px 24px rgba(40, 33, 51, 0.08);
            border: 1px solid #e1dde7;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 24px;
            font-weight: 900;
            color: #282133;
            letter-spacing: -0.5px;
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-top: 8px;
        }
        .badge-approved {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .badge-rejected {
            background-color: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .content {
            font-size: 14px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .reason-box {
            background-color: #fff1f2;
            border-left: 4px solid #e11d48;
            padding: 14px 18px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 13px;
            color: #881337;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #564B68, #6F6382);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            text-align: center;
        }
        .footer {
            margin-top: 32px;
            border-top: 1px solid #f3eff7;
            padding-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">Cartzy</div>
            @if($status === 'approved')
                <span class="badge badge-approved">Application Approved</span>
            @else
                <span class="badge badge-rejected">Application Requires Attention</span>
            @endif
        </div>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            @if($status === 'approved')
                <p>We are pleased to inform you that your registration and identity verification for your <strong>{{ ucfirst($user->role) }}</strong> account have been successfully <strong>approved</strong> by our administration team.</p>
                <p>Your account is now fully active. You can now access all marketplace features, place orders, or start managing your store.</p>
                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ url('/login') }}" class="button">Log In to Cartzy</a>
                </div>
            @else
                <p>Thank you for submitting your application for a <strong>{{ ucfirst($user->role) }}</strong> account on Cartzy. After reviewing your submitted documents, our administration team was unable to approve your application at this time.</p>
                
                @if(!empty($reason))
                    <div class="reason-box">
                        <strong>Reason provided:</strong><br>
                        {{ $reason }}
                    </div>
                @endif

                <p>You may log in to update your identification documents and re-submit for review.</p>
                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ url('/login') }}" class="button">Review & Re-submit Documents</a>
                </div>
            @endif

            <p>If you have any questions, you can contact our customer support team.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Cartzy eCommerce Platform. All rights reserved.<br>
            This is an automated notification regarding your account status.
        </div>
    </div>
</body>
</html>
