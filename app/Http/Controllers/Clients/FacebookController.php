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

class FacebookController extends Controller
{
    // public function redirectToFacebook(): RedirectResponse
    // {
    //     return Socialite::driver('facebook')->redirect();
    // }

    // public function handleFacebookCallback(Request $request): RedirectResponse
    // {
    //     try {
    //         $facebookUser = Socialite::driver('facebook')->user();
    //     } catch (InvalidStateException|GuzzleException) {
    //         toastr()->error('Không thể xác thực tài khoản Facebook. Vui lòng thử lại.');

    //         return redirect()->route('login');
    //     }

    //     $facebookId = $facebookUser->getId();
    //     $email = $facebookUser->getEmail();

    //     // if (! $facebookId) {
    //     //     toastr()->error('Facebook không cung cấp thông tin tài khoản hợp lệ.');

    //     //     return redirect()->route('login');
    //     // }

    //     $userByFacebookId = User::with('role')->where('facebook_id', $facebookId)->first();
    //     $userByEmail = $email ? User::where('email', $email)->first() : null;

    //     // if (! $email && ! $userByFacebookId) {
    //     //     toastr()->error('Facebook không cung cấp email. Vui lòng cho phép quyền truy cập email và thử lại.');

    //     //     return redirect()->route('login');
    //     // }

    //     if ($userByEmail && (! $userByFacebookId || $userByEmail->id !== $userByFacebookId->id)) {
    //         toastr()->error('Email này đã được đăng ký');

    //         return redirect()->route('login');
    //     }

    //     $user = $userByFacebookId;

    //     if (! $user) {
    //         $customerRole = Role::where('name', 'customer')->first();

    //         if (! $customerRole) {
    //             toastr()->error('Không thể tạo tài khoản. Vui lòng liên hệ quản trị viên.');

    //             return redirect()->route('login');
    //         }

    //         $name = trim((string) $facebookUser->getName());

    //         $user = User::create([
    //             'name' => $name !== '' ? $name : Str::before($email, '@'),
    //             'email' => $email,
    //             'password' => Hash::make(Str::random(40)),
    //             'avatar' => $facebookUser->getAvatar(),
    //             'status' => 'active',
    //             'role_id' => $customerRole->id,
    //             'facebook_id' => $facebookId,
    //         ]);
    //     }

    //     if ($user->status !== 'active' || $user->role?->name !== 'customer') {
    //         toastr()->error('Tài khoản không có quyền đăng nhập hoặc chưa được kích hoạt.');

    //         return redirect()->route('login');
    //     }

    //     Auth::login($user);
    //     $request->session()->regenerate();

    //     toastr()->success('Đăng nhập bằng Facebook thành công.');

    //     return redirect()->route('home');
    // }
}
