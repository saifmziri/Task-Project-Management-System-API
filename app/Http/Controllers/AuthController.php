<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        // server-controlled token name (not user input)
        $deviceName = $this->resolveDeviceName($request);
    
        $result = $this->authService->login($credentials, $deviceName);
    
        return $this->ok([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'User logged in successfully');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|string',
        ]);
    
        $this->authService->sendResetLink($request->email);
    
        return $this->ok(null, 'إذا كان البريد مسجلاً لدينا، فقد تم إرسال رابط إعادة تعيين كلمة السر إليه.');
    }
    
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);
    
        $this->authService->resetPassword($request->all());
    
        return $this->ok(null, 'تم إعادة تعيين كلمة السر بنجاح. يمكنك الآن تسجيل الدخول.');
    }
    
    private function resolveDeviceName(Request $request): string
    {
        $ua = strtolower((string) $request->userAgent());
    
        return match (true) {
            str_contains($ua, 'android') => 'android',
            str_contains($ua, 'iphone'), str_contains($ua, 'ios') => 'ios',
            default => 'web',
        };
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->created([
            'user' => new UserResource($result['user']),
        ], 'User registered successfully. Please check your email to verify your account.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->ok(null, 'Logout successful');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $result = $this->authService->verifyEmail($request->token);

        return $this->ok([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ], 'Email verified successfully');
    }

    public function resendVerificationEmail(ResendVerificationRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $this->authService->resendVerificationEmail($email);

        return $this->ok(null, 'إذا كان هذا البريد متاحاً وغير موثق لدينا، فقد تم إرسال رابط تفعيل جديد إليه.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());

        return $this->ok(null, 'تم تغيير كلمة المرور بنجاح.');
    }
}