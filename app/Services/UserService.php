<?php

namespace App\Services;

use App\Models\User;


class UserService
{
    public function __construct(protected AuthService $authService) {}

    public function current(User $user): User
    {
        return $user->load('role');
    }
    
    /**
     * تحديث بيانات المستخدم
     */
    public function updateUser(User $user, array $data): User
    {
        $isEmailChanging = isset($data['email']) && $data['email'] !== $user->email;
    
        if ($isEmailChanging) {
            $data['email_verified_at'] = null; // إبطال التوثيق القديم
            
            $user->update($data); // تحديث الإيميل في الداتابيز أولاً
            
            $this->authService->sendCustomVerificationEmail($user); // إرسال التفعيل للإيميل الجديد
    
            return $user;
        }
    
        // إذا لم يتغير الإيميل، يتم تحديث باقي البيانات فقط (مثل الاسم أو الهاتف)
        $user->update($data);
    
        return $user;
    }

    /**
     * تغيير حالة المستخدم
     */
    public function changeStatus(User $user, string $status): ?User
    {
        if ($user->status === $status) {
            return null;
        }

        $user->update(['status' => $status]);
        return $user;
    }
}