<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(
        protected UserServices $userService
    ) {}

    // ── GET /users ────────────────────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        // Hanya admin yang boleh akses
        abort_unless(Auth::user()->isAdmin(), 403);

        $stats = $this->userService->countByRole();

        return view('users.index', compact('stats'));
    }

    // ── GET /users/api/data — datatable JSON ──────────────────────────────────

    public function list(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $result = $this->userService->paginate(
            perPage: (int) $request->get('perPage', 10),
            page:    (int) $request->get('page', 1),
            search:  (string) $request->get('search', ''),
            role:    $request->get('role') ?: null,
        );

        // Transform — jangan expose password, tambah computed fields
        $result['data'] = collect($result['data'])
            ->map(fn (User $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'role_label' => $u->roleLabelAttribute,
                'is_active'  => $u->is_active,
                'is_me'      => $u->id === Auth::id(),
                'created_at' => $u->created_at->format('d M Y'),
            ])
            ->values()
            ->all();

        return response()->json($result);
    }

    // ── POST /users/store ─────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role'     => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'is_active'=> ['sometimes', 'boolean'],
        ], [
            'name.required'     => 'Nama wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'role.in'           => 'Role tidak valid.',
        ]);

        try {
            $user = $this->userService->create($validated);

            return response()->json([
                'message' => "User {$user->name} berhasil ditambahkan.",
                'data'    => ['id' => $user->id],
            ], 201);

        } catch (\Throwable) {
            return response()->json(['message' => 'Gagal menambahkan user.'], 500);
        }
    }

    // ── PUT /users/update/{id} ────────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150',
                            Rule::unique('users', 'email')->ignore($id)],
            'password'  => ['nullable', Password::min(8)->letters()->numbers()],
            'role'      => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email sudah digunakan.',
            'role.in'        => 'Role tidak valid.',
        ]);

        // Guard: jangan turunkan role admin terakhir
        if ($user->isAdmin()
            && ($validated['role'] ?? $user->role) !== User::ROLE_ADMIN
            && User::admins()->count() <= 1
        ) {
            return response()->json([
                'message' => 'Tidak dapat mengubah role admin terakhir.',
                'errors'  => ['role' => ['Tidak dapat mengubah role admin terakhir.']],
            ], 422);
        }

        try {
            $updated = $this->userService->update($user, $validated);

            return response()->json([
                'message' => "User {$updated->name} berhasil diperbarui.",
                'data'    => ['id' => $updated->id],
            ]);

        } catch (\Throwable) {
            return response()->json(['message' => 'Gagal memperbarui user.'], 500);
        }
    }

    // ── DELETE /users/destroy/{id} ────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $user = User::findOrFail($id);

        try {
            $this->userService->delete($user);

            return response()->json([
                'message' => "User {$user->name} berhasil dihapus.",
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable) {
            return response()->json(['message' => 'Gagal menghapus user.'], 500);
        }
    }

    // ── POST /users/toggle-active/{id} ────────────────────────────────────────

    public function toggleActive(int $id): JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $user = User::findOrFail($id);

        try {
            $updated = $this->userService->toggleActive($user);

            $status = $updated->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'message'   => "User {$updated->name} berhasil {$status}.",
                'is_active' => $updated->is_active,
            ]);

        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}