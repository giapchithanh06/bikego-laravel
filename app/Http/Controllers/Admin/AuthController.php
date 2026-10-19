<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\Station;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/** Admin login (/admin/login) and the admin home page (/admin). */
class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (optional($request->user())->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $admin = User::where('role', User::ROLE_ADMIN)->where('name', trim($data['username']))->first();

        if (! $admin || ! Hash::check($data['password'], $admin->password)) {
            return back()
                ->withErrors(['username' => 'Sai tên đăng nhập hoặc mật khẩu.'])
                ->onlyInput('username');
        }

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'bikeCount' => Bicycle::count(),
            'stationCount' => Station::count(),
        ]);
    }
}
