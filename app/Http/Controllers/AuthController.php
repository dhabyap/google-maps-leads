<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('dashboard_authed')) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $expected = config('radar.password');

        if ($expected !== null && hash_equals((string) $expected, (string) $request->password)) {
            $request->session()->put('dashboard_authed', true);
            return redirect('/');
        }

        return back()->withErrors(['password' => 'Password salah.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('dashboard_authed');

        return redirect('/login');
    }
}
