<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserServices
{
    public function __construct(
        protected UserRepository $repo
    ) {}

    // ── Read ──────────────────────────────────────────────────────────────────

    public function paginate(
        int    $perPage = 10,
        int    $page    = 1,
        string $search  = '',
        ?string $role   = null,
    ): array {
        return $this->repo->paginate($perPage, $page, $search, $role);
    }

    public function findById(int $id): ?User
    {
        return $this->repo->findById($id);
    }

    public function countByRole(): array
    {
        return $this->repo->countByRole();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(array $data): User
    {
        return $this->repo->create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(User $user, array $data): User
    {
        $payload = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? $user->is_active),
        ];

        // Password hanya diupdate kalau diisi
        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        return $this->repo->update($user, $payload);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(User $user): void
    {
        // Tidak boleh hapus diri sendiri
        if ($user->id === Auth::id()) {
            throw new \DomainException('Tidak dapat menghapus akun Anda sendiri.');
        }

        // Pastikan masih ada admin lain
        if ($user->isAdmin() && User::admins()->count() <= 1) {
            throw new \DomainException('Tidak dapat menghapus admin terakhir.');
        }

        $this->repo->delete($user);
    }

    // ── Toggle status aktif / nonaktif ────────────────────────────────────────

    public function toggleActive(User $user): User
    {
        if ($user->id === Auth::id()) {
            throw new \DomainException('Tidak dapat menonaktifkan akun Anda sendiri.');
        }

        return $this->repo->update($user, ['is_active' => ! $user->is_active]);
    }
}