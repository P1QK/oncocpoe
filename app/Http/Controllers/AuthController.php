<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'กรุณาระบุชื่อผู้ใช้งาน (Username หรือ Email)',
            'password.required' => 'กรุณาระบุรหัสผ่าน',
        ]);

        $loginInput = trim($request->input('username'));
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $loginInput)->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'username' => 'บัญชีผู้ใช้งานนี้ถูกระงับการใช้งานชั่วคราว',
            ])->onlyInput('username');
        }

        if (Auth::attempt([$field => $loginInput, 'password' => $request->input('password')], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'username' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // --- SUPERADMIN: USER MANAGEMENT (onc_users) ---
    public function usersIndex()
    {
        $users = User::orderByRaw("FIELD(role, 'superadmin', 'admin', 'doctor', 'pharmacist', 'nurse')")
            ->orderBy('name')
            ->get();

        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username'   => ['required', 'string', 'max:50', 'unique:onc_users,username'],
            'name'       => ['required', 'string', 'max:150'],
            'email'      => ['nullable', 'email', 'max:150', 'unique:onc_users,email'],
            'role'       => ['required', 'in:superadmin,admin,doctor,pharmacist,nurse'],
            'license_no' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'password'   => ['required', 'string', 'min:4'],
        ], [
            'username.unique' => 'Username นี้มีในระบบแล้ว',
            'email.unique'    => 'Email นี้มีในระบบแล้ว',
        ]);

        User::create([
            'username'   => $validated['username'],
            'name'       => $validated['name'],
            'email'      => $validated['email'] ?: null,
            'role'       => $validated['role'],
            'license_no' => $validated['license_no'] ?? null,
            'department' => $validated['department'] ?? null,
            'is_active'  => true,
            'password'   => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'ลบผู้ใช้งาน ' . $user->name . ' เรียบร้อยแล้ว');
    }
}
