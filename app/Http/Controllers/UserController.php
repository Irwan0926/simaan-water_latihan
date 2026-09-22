<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();
        $authId = auth()->id();

        $adminIds = User::query()
            ->where('role', 'admin')
            ->pluck('id')
            ->all();

        $activeAdminIds = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $onlyOneAdmin = count($adminIds) === 1;
        $onlyOneActiveAdmin = count($activeAdminIds) === 1;

        $protectedDeleteIds = [];
        $deleteReasons = [];

        foreach ($users as $user) {
            if ($user->id === $authId) {
                $protectedDeleteIds[] = $user->id;
                $deleteReasons[$user->id] = 'Tidak dapat menghapus akun sendiri.';
                continue;
            }

            if ($user->role === 'admin' && $onlyOneAdmin) {
                $protectedDeleteIds[] = $user->id;
                $deleteReasons[$user->id] = 'Minimal satu admin harus tetap ada.';
                continue;
            }

            if ($user->role === 'admin' && $user->is_active && $onlyOneActiveAdmin) {
                $protectedDeleteIds[] = $user->id;
                $deleteReasons[$user->id] = 'Minimal satu admin aktif harus tetap ada.';
            }
        }

        return view('users.index', [
            'users' => $users,
            'authId' => $authId,
            'protectedDeleteIds' => $protectedDeleteIds,
            'deleteReasons' => $deleteReasons,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['timezone'] = $data['timezone'] ?? config('app.display_timezone');

        $user = new User;
        $user->fill($data);
        // email_verified_at bukan atribut fillable, jadi harus diset eksplisit.
        // Tanpa ini akun baru terkunci middleware 'verified' di dashboard.
        $user->email_verified_at = now();
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna ' . $user->name . ' berhasil ditambahkan.');
    }

    public function edit(User $user): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', $user->is_active);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'admin') {
            $remainingAdmins = User::query()
                ->where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingAdmins === 0) {
                return redirect()
                    ->route('users.index')
                    ->with('error', 'Tidak dapat menghapus admin terakhir. Minimal satu admin harus tetap ada.');
            }

            $remainingActiveAdmins = User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($user->is_active && $remainingActiveAdmins === 0) {
                return redirect()
                    ->route('users.index')
                    ->with('error', 'Tidak dapat menghapus admin aktif terakhir. Minimal satu admin harus tetap aktif.');
            }
        }

        if ($user->sales()->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Pengguna memiliki riwayat transaksi. Nonaktifkan pengguna, jangan dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
