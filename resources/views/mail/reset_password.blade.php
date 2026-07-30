<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eef2f5;
        }

        .email-header {
            background-color: #4f46e5;
            /* لون بروفيشنال Indigo للمشاريع */
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .email-body {
            padding: 40px 30px;
            color: #334155;
            line-height: 1.6;
        }

        .email-body p {
            margin: 0 0 20px;
            font-size: 16px;
        }

        .btn-container {
            text-align: center;
            margin: 30px 0;
        }

        .btn-verify {
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 6px;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.15);
        }

        .btn-verify:hover {
            background-color: #4338ca;
        }

        .email-footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>

    <div class="email-container">
        <!-- الهيدر -->
        <div class="email-header">
            <h1>Task & Project Management System</h1>
        </div>

        <!-- محتوى الإيميل الرئيسي -->
        <div class="email-body">
            <p>Welcome, <strong>{{ $user->full_name }}</strong>!</p>
            <p>Thank you for signing up. We're excited to help you organize your workflows and manage your tasks dynamic
                and smoothly.</p>
            <p>Please click the button below to verify your email address and instantly activate your account:</p>

            <!-- زر التفعيل المرتبط بالـ React -->
            <div class="btn-container">
                <a href="{{ $resetLink }}" class="btn-verify" target="_blank">Reset Password</a>
            </div>

            <p>If you did not create this account, no further action is required; you can safely ignore this email.</p>
            <p>Best regards,<br><strong>The Management Team</strong></p>
        </div>

        <!-- الفوتر -->
        <div class="email-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

</body>

</html>
