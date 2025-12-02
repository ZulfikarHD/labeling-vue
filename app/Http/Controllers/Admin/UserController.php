<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Models\Workstation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola user (admin only)
 * yang mencakup CRUD operations untuk user management
 *
 * User management memungkinkan admin untuk membuat, mengedit,
 * dan menghapus akun pengguna dalam sistem
 */
class UserController extends Controller
{
    /**
     * Menampilkan daftar user dengan fitur search dan filter
     * yang mencakup pencarian berdasarkan NP dan filter berdasarkan role/status
     */
    public function index(Request $request): Response
    {
        $query = User::query()
            ->with('workstation')
            ->orderBy('np');

        // Search by NP
        if ($request->filled('search')) {
            $search = strtoupper($request->search);
            $query->where('np', 'like', "%{$search}%");
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
            'roles' => collect(UserRole::cases())->map(fn ($role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
        ]);
    }

    /**
     * Menampilkan form untuk membuat user baru
     * dengan options untuk workstation dan role
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'workstations' => Workstation::active()->orderBy('name')->get(['id', 'name']),
            'roles' => collect(UserRole::cases())->map(fn ($role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
        ]);
    }

    /**
     * Menyimpan user baru ke database
     * dengan opsi default password (Peruri + NP)
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle default password
        if ($request->boolean('use_default')) {
            $data['password'] = 'Peruri'.strtoupper($data['np']);
    }

        // Convert NP to uppercase
        $data['np'] = strtoupper($data['np']);

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk edit user
     * dengan data user yang akan diedit
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('workstation'),
            'workstations' => Workstation::active()->orderBy('name')->get(['id', 'name']),
            'roles' => collect(UserRole::cases())->map(fn ($role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
        ]);
    }

    /**
     * Memperbarui data user di database
     * NP tidak dapat diubah setelah dibuat
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Handle password update jika ada
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Menghapus user dari database
     * dengan pengecekan agar admin tidak bisa menghapus dirinya sendiri
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Cegah admin menghapus dirinya sendiri
        if ($request->user()->id === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
