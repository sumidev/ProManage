<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verify your ProManage account</title>
</head>
<body style="margin:0;padding:0;background-color:#f7f8f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f7f8f9;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:520px;">
                    <tr>
                        <td style="padding:0 0 20px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:32px;height:32px;background-color:#6366f1;border-radius:6px;text-align:center;vertical-align:middle;">
                                        <span style="color:#ffffff;font-size:14px;font-weight:800;line-height:32px;">P</span>
                                    </td>
                                    <td style="padding-left:10px;vertical-align:middle;">
                                        <span style="font-size:18px;font-weight:700;color:#1d2125;letter-spacing:-0.02em;">ProManage</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid #dcdfe4;border-radius:8px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.08),0 2px 4px rgba(0,0,0,0.04);">
                            <h1 style="margin:0 0 8px 0;font-size:20px;font-weight:700;color:#1d2125;line-height:1.3;">
                                Confirm your email
                            </h1>
                            <p style="margin:0 0 24px 0;font-size:14px;line-height:1.5;color:#44546f;">
                                @if(!empty($userName))
                                    Hi {{ $userName }},
                                @else
                                    Hi there,
                                @endif
                                welcome to ProManage. Please verify your email address to activate your account and continue.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding:0 0 24px 0;">
                                        <a href="{{ $verifyUrl }}"
                                           style="display:inline-block;background-color:#6366f1;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;padding:10px 24px;border-radius:6px;line-height:1.4;">
                                            Verify email
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 12px 0;font-size:13px;line-height:1.5;color:#44546f;">
                                This verification link will expire in <strong style="color:#1d2125;">60 minutes</strong>.
                            </p>
                            <p style="margin:0;font-size:12px;line-height:1.5;color:#8590a2;word-break:break-all;">
                                Button not working? Copy and paste this URL into your browser:<br>
                                <a href="{{ $verifyUrl }}" style="color:#6366f1;text-decoration:underline;">{{ $verifyUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 0 0 0;text-align:center;">
                            <p style="margin:0 0 4px 0;font-size:12px;color:#8590a2;line-height:1.5;">
                                Manage projects like a pro team.
                            </p>
                            <p style="margin:0;font-size:11px;color:#b0bac9;">
                                &copy; {{ date('Y') }} ProManage. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
