<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'username' => 'บัญชีผู้ใช้งานของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ]);
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงส่วนงานนี้ (เฉพาะกลุ่ม: ' . implode(', ', $roles) . ')');
        }

        return $next($request);
    }
}
