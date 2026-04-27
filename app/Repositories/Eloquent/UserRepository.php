<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository
{
    /**
     * Paginated list — shape { data, meta } sesuai datatable.js
     */
    public function paginate(
        int    $perPage = 10,
        int    $page    = 1,
        string $search  = '',
        ?string $role   = null,
    ): array {
        $paginator = User::when($search, fn ($q) =>
                $q->where('name',  'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
            )
            ->when($role, fn ($q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function countByRole(): array
    {
        return [
            'admin' => User::admins()->count(),
            'staff' => User::staff()->count(),
            'total' => User::count(),
        ];
    }
}