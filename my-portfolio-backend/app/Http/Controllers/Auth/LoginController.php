<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * تسجيل دخول المستخدم وإنشاء توكن المصادقة
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Debug request
            \Log::info('Login attempt:', ['email' => $request->email]);
            
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('auth-token')->plainTextToken;
                
                // Debug successful login
                \Log::info('Login successful:', ['user_id' => $user->id, 'token' => substr($token, 0, 10) . '...']);
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'user' => $user,
                    'token' => $token,
                ]);
            }
    
            // Debug failed login
            \Log::info('Login failed: Invalid credentials');
            
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات الاعتماد المقدمة غير صحيحة.',
            ], 401);
        } catch (\Exception $e) {
            // Log error
            \Log::error('Login exception: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء تسجيل الدخول',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تسجيل خروج المستخدم وحذف التوكن الحالي
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // حذف التوكن الحالي فقط
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}