<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ChangeUserStatusRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::with('role')->get();

        return $this->ok(
            UserResource::collection($users),
            'Users fetched successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return $this->ok(
            new UserResource($user->load('role')),
            'User fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $userToUpdate = User::findOrFail($id);
    
        $updatedUser = $this->userService->updateUser($userToUpdate, $request->validated());
    
        return $this->ok([
            'user' => new UserResource($updatedUser->fresh()->load('role'))
        ], 'User updated successfully. Verification email sent if email was changed.');
    }
    
    /**
     * Change user account status (Active/Inactive)
     */
    public function changeStatus(ChangeUserStatusRequest $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $updatedUser = $this->userService->changeStatus($user, $request->validated()['status']);

        if (!$updatedUser) {
            return $this->fail(
                "User is already {$request->status}.",
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->ok([
            'user' => new UserResource($updatedUser->load('role'))
        ], "User status changed to {$request->status} successfully");
    }
}