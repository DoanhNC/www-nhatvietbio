<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ESetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthAdminController extends Controller
{
    public function loginView()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function loginSubmit(Request $r)
    {
        if (Auth::guard('admin')->check()) {
            throw ValidationException::withMessages(['Tài khoản đã được đăng nhập. Vui lòng đăng xuất!']);
        }

        $data = $r->validate([
            'login'    => ['required', 'string', 'max:190'], // username or email
            'password' => ['required', 'string', 'max:255'],
        ]);

        // Find user by username or email
        $u = User::where('username', $data['login'])
            ->orWhere('email', $data['login'])
            ->first();

        if (!$u || !Hash::check($data['password'], $u->password)) {
            throw ValidationException::withMessages(['login' => 'Tài khoản hoặc mật khẩu không đúng.']);
        }

        // Check if user is active
        if (!$u->status) {
            throw ValidationException::withMessages(['login' => 'Tài khoản đã bị vô hiệu hóa.']);
        }

        // Login user vào guard admin (session)
        Auth::guard('admin')->login($u);

        // Regenerate session để tránh session fixation
        $r->session()->regenerate();

        return response()->json([
            'account' => [
                'id' => $u->id,
                'username' => $u->username,
                'email' => $u->email,
                'full_name' => $u->name,
                'is_root' => $u->is_root,
                'permissions' => $u->getAllPermissions(),
            ],
        ]);
    }

    public function logout(Request $req)
    {
        // Nếu đang đăng nhập theo session (admin)
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $req->session()->invalidate();
            $req->session()->regenerateToken();
        }

        return response()->json(['status' => true]);
    }

    // ================== FORGOT PASSWORD ==================

    public function forgotPasswordView()
    {
        return view('admin.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Email không tồn tại trong hệ thống.',
            ]);
        }

        // Xóa token cũ nếu có
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Tạo token mới
        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Lấy thông tin cấu hình website
        $websiteInfo = ESetting::getWebsiteInfo();
        $baseUrl = !empty($websiteInfo['url']) ? rtrim($websiteInfo['url'], '/') : url('');
        $websiteName = !empty($websiteInfo['name']) ? $websiteInfo['name'] : config('app.name');
        $companyName = !empty($websiteInfo['company']) ? $websiteInfo['company'] : '';

        // Tạo URL reset
        $resetUrl = $baseUrl . '/admin/reset-password/' . $token . '?email=' . urlencode($request->email);

        // Áp dụng cấu hình SMTP từ database
        ESetting::applyMailConfig();

        // Gửi email với thông tin website
        Mail::send('emails.reset-password', [
            'resetUrl' => $resetUrl,
            'websiteName' => $websiteName,
            'companyName' => $companyName,
        ], function ($message) use ($request, $websiteName) {
            $message->to($request->email);
            $message->subject('Khôi phục mật khẩu - ' . $websiteName);
        });

        return response()->json([
            'message' => 'Link khôi phục mật khẩu đã được gửi đến email của bạn!',
        ]);
    }

    public function resetPasswordView(Request $request, $token)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('admin.login')->with('error', 'Link không hợp lệ.');
        }

        // Kiểm tra token có tồn tại và còn hạn không
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (!$record) {
            return redirect()->route('admin.login')->with('error', 'Link đã hết hạn hoặc không hợp lệ.');
        }

        // Kiểm tra token có đúng không
        if (!Hash::check($token, $record->token)) {
            return redirect()->route('admin.login')->with('error', 'Link không hợp lệ.');
        }

        // Kiểm tra hết hạn (60 phút)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect()->route('admin.login')->with('error', 'Link đã hết hạn. Vui lòng yêu cầu link mới.');
        }

        return view('admin.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Kiểm tra token
        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => 'Link đã hết hạn hoặc không hợp lệ.',
            ]);
        }

        if (!Hash::check($request->token, $record->token)) {
            throw ValidationException::withMessages([
                'email' => 'Link không hợp lệ.',
            ]);
        }

        // Kiểm tra hết hạn (60 phút)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'email' => 'Link đã hết hạn. Vui lòng yêu cầu link mới.',
            ]);
        }

        // Cập nhật mật khẩu
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Tài khoản không tồn tại.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Xóa token đã sử dụng
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Mật khẩu đã được thay đổi thành công!',
        ]);
    }

    /**
     * Change password for logged-in user
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không đúng.',
            ], 422);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Đổi mật khẩu thành công!',
        ]);
    }
}
