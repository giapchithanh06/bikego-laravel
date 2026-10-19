<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/** Register / login / logout of normal users. */
class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        // Every self-registered account is a normal user (never admin)
        User::create([
            'name' => $request->input('name'),
            'phone' => User::normalizePhone($request->input('phone')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role' => User::ROLE_USER,
        ]);

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identifier' => ['required'],
            'password' => ['required'],
        ], [
            'identifier.required' => 'Vui lòng nhập số điện thoại hoặc email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $identifier = trim($data['identifier']);

        // The same field accepts either an email or a phone number
        $user = \Illuminate\Support\Str::contains($identifier, '@')
            ? User::where('email', $identifier)->first()
            : User::where('phone', User::normalizePhone($identifier))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()
                ->withErrors(['identifier' => 'Thông tin đăng nhập không chính xác.'])
                ->onlyInput('identifier');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
