<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Authenticate user with credentials and generate token
     * 
     * @param array $credentials
     * @return array|null Returns ['user' => User, 'token' => string] on success, null on failure
     */
    public function authenticate(array $credentials): ?array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Create a new user with hashed password
     * 
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User
    {
        $userData['password'] = Hash::make($userData['password']);
        
        return $this->userRepository->create($userData);
    }

    /**
     * Revoke all tokens for the authenticated user
     * 
     * @param User $user
     * @return bool
     */
    public function revokeTokens(User $user): bool
    {
        $user->tokens()->delete();
        
        return true;
    }
}