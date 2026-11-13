<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException|GuzzleException) {
            toastr()->error('Không thể xác thực tài khoản Google. Vui lòng thử lại.');

            return redirect()->route('login');
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();

        if (! $googleId || ! $email || ($googleUser->getRaw()['email_verified'] ?? false) !== true) { // nếu email chưa được xác minh, không cho phép đăng nhập
            toastr()->error('Google không cung cấp thông tin tài khoản đã xác minh.');

            return redirect()->route('login');
        }

        $userByGoogleId = User::with('role')->where('google_id', $googleId)->first();
        $userByEmail = User::with('role')->where('email', $email)->first();

        if (! $userByGoogleId && $userByEmail) {
            toastr()->error('Email này đã được đăng ký');

            return redirect()->route('login');
        }

        $user = $userByGoogleId;

        if (! $user) {
            $customerRole = Role::where('name', 'customer')->first();

            if (! $customerRole) {
                toastr()->error('Không thể tạo tài khoản. Vui lòng liên hệ quản trị viên.');

                return redirect()->route('login');
            }

            $name = trim((string) $googleUser->getName()); // Lấy tên từ Google, nếu không có thì lấy phần trước của email
            $avatar = $googleUser->getAvatar(); // Lấy avatar từ Google nếu có

            $user = User::create([
                'name' => $name !== '' ? $name : Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'avatar' => $avatar,
                'status' => 'active',
                'role_id' => $customerRole->id,
                'google_id' => $googleId,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        toastr()->success('Đăng nhập bằng Google thành công.');

        return redirect()->route('home');
    }
}
