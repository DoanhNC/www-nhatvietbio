{{-- resources/views/emails/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu</title>
</head>

<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 30px; text-align: center; background: linear-gradient(135deg, #22c55e 0%, #3b82f6 100%); border-radius: 16px 16px 0 0;">
                            <div style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                                <span style="font-size: 32px;">🔐</span>
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                Khôi phục mật khẩu
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                                Xin chào,
                            </p>
                            <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                                Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Nhấn vào nút bên dưới để tiến hành đặt lại mật khẩu:
                            </p>

                            <!-- Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 32px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}"
                                            style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #22c55e 0%, #3b82f6 100%); color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 50px; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.4);">
                                            🔑 Đặt lại mật khẩu
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alternative Link -->
                            <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Nếu nút trên không hoạt động, bạn có thể copy và paste link sau vào trình duyệt:
                            </p>
                            <p style="margin: 0 0 24px; padding: 12px 16px; background: #f3f4f6; border-radius: 8px; word-break: break-all;">
                                <a href="{{ $resetUrl }}" style="color: #3b82f6; font-size: 13px; text-decoration: none;">{{ $resetUrl }}</a>
                            </p>

                            <!-- Warning -->
                            <div style="padding: 16px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; border-left: 4px solid #f59e0b; margin-bottom: 24px;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
                                    ⏰ <strong>Lưu ý:</strong> Link này sẽ hết hạn sau <strong>60 phút</strong>.
                                </p>
                            </div>

                            <p style="margin: 0 0 12px; color: #374151; font-size: 16px; line-height: 1.6;">
                                Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này. Tài khoản của bạn vẫn an toàn.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background: #f9fafb; border-radius: 0 0 16px 16px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 8px; color: #6b7280; font-size: 14px;">
                                <strong style="color: #374151;">{{ $websiteName }}</strong>
                            </p>
                            @if(!empty($companyName))
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                {{ $companyName }}
                            </p>
                            @endif
                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0; color: #9ca3af; font-size: 11px;">
                                    © {{ date('Y') }} {{ $websiteName }}. All rights reserved.
                                </p>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>