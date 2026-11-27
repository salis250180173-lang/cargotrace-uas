<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function getAll(): Collection
    {
        return User::orderBy('name')->get();
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $this->auditLogService->log('CREATED', 'User', $user->id, [
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $this->auditLogService->log('UPDATED', 'User', $user->id, [
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $this->auditLogService->log('DELETED', 'User', $user->id, [
            'email' => $user->email,
        ]);

        $user->delete();
    }
}

