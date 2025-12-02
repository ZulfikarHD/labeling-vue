<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkstationRequest;
use App\Http\Requests\Admin\UpdateWorkstationRequest;
use App\Models\Workstation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola workstation/tim produksi
 * yang mencakup CRUD operations dan toggle status aktif
 *
 * Workstation digunakan untuk mengelompokkan operator dan production order
 * dalam satu unit kerja untuk memudahkan assignment dan tracking
 */
class WorkstationController extends Controller
{
    /**
     * Menampilkan daftar semua workstation
     * dengan informasi jumlah user dan status aktif
     */
    public function index(): Response
    {
        $workstations = Workstation::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Workstations/Index', [
            'workstations' => $workstations,
        ]);
    }

    /**
     * Menampilkan form untuk membuat workstation baru
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Workstations/Create');
    }

    /**
     * Menyimpan workstation baru ke database
     * dengan validasi nama unique dan status default aktif
     */
    public function store(StoreWorkstationRequest $request): RedirectResponse
    {
        Workstation::create($request->validated());

        return redirect()
            ->route('admin.workstations.index')
            ->with('success', 'Workstation berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk edit workstation
     * dengan data workstation yang akan diedit
     */
    public function edit(Workstation $workstation): Response
    {
        return Inertia::render('Admin/Workstations/Edit', [
            'workstation' => $workstation,
        ]);
    }

    /**
     * Memperbarui data workstation di database
     * termasuk nama dan status aktif
     */
    public function update(UpdateWorkstationRequest $request, Workstation $workstation): RedirectResponse
    {
        $workstation->update($request->validated());

        return redirect()
            ->route('admin.workstations.index')
            ->with('success', 'Workstation berhasil diperbarui.');
    }

    /**
     * Menghapus workstation dari database
     * dengan pengecekan apakah masih ada user yang terkait
     */
    public function destroy(Workstation $workstation): RedirectResponse
    {
        // Cek apakah ada user yang terkait dengan workstation ini
        if ($workstation->users()->exists()) {
            return redirect()
                ->route('admin.workstations.index')
                ->with('error', 'Tidak dapat menghapus workstation yang masih memiliki user.');
        }

        $workstation->delete();

        return redirect()
            ->route('admin.workstations.index')
            ->with('success', 'Workstation berhasil dihapus.');
    }

    /**
     * Toggle status aktif workstation
     * untuk mengaktifkan atau menonaktifkan workstation
     */
    public function toggleActive(Workstation $workstation): RedirectResponse
    {
        $workstation->update([
            'is_active' => ! $workstation->is_active,
        ]);

        $status = $workstation->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.workstations.index')
            ->with('success', "Workstation berhasil {$status}.");
    }
}
