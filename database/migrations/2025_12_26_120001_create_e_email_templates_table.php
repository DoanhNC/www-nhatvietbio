<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('e_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // slug: contact, password_reset
            $table->string('label', 100); // Display name: Liên hệ
            $table->string('subject', 255); // Email subject
            $table->json('cc_emails')->nullable(); // CC email array
            $table->json('bcc_emails')->nullable(); // BCC email array
            $table->longText('content'); // Template content (Blade/HTML)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default templates
        $now = now();
        \DB::table('e_email_templates')->insert([
            [
                'name' => 'contact',
                'label' => 'Liên hệ',
                'subject' => 'Liên hệ mới từ website',
                'cc_emails' => json_encode([]),
                'bcc_emails' => json_encode([]),
                'content' => $this->getContactTemplate(),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'password_reset',
                'label' => 'Đặt lại mật khẩu',
                'subject' => 'Đặt lại mật khẩu',
                'cc_emails' => json_encode([]),
                'bcc_emails' => json_encode([]),
                'content' => $this->getPasswordResetTemplate(),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function getContactTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4e73df; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fc; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #5a5c69; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Liên hệ mới từ website</h2>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">Họ tên:</span> {{ $name }}
            </div>
            <div class="field">
                <span class="label">Email:</span> {{ $email }}
            </div>
            <div class="field">
                <span class="label">Số điện thoại:</span> {{ $phone }}
            </div>
            <div class="field">
                <span class="label">Nội dung:</span><br>
                {{ $message }}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getPasswordResetTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4e73df; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fc; }
        .btn { display: inline-block; padding: 12px 24px; background: #4e73df; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Đặt lại mật khẩu</h2>
        </div>
        <div class="content">
            <p>Xin chào,</p>
            <p>Bạn đã yêu cầu đặt lại mật khẩu. Nhấn vào nút bên dưới để tiếp tục:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $reset_link }}" class="btn">Đặt lại mật khẩu</a>
            </p>
            <p>Link sẽ hết hạn sau 60 phút.</p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_email_templates');
    }
};
