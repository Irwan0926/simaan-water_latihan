<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,pegawai'],
            'is_active' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah dipakai pengguna lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $target = $this->route('user');
            if (! $target) {
                return;
            }

            $authId = auth()->id();
            $incomingRole = $this->input('role');
            $incomingActive = $this->boolean('is_active');

            $wantsToDemote = $target->role === 'admin' && $incomingRole === 'pegawai';
            $wantsToDeactivate = $target->is_active && ! $incomingActive;

            if (! ($wantsToDemote || $wantsToDeactivate)) {
                return;
            }

            $remainingActiveAdmins = \App\Models\User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $target->id)
                ->count();

            if ($remainingActiveAdmins === 0) {
                $reason = $wantsToDeactivate
                    ? 'Tidak dapat menonaktifkan admin terakhir. Minimal satu admin harus tetap aktif.'
                    : 'Tidak dapat mengubah peran admin terakhir menjadi pegawai. Minimal satu admin harus tetap berperan admin.';

                if ($target->id === $authId) {
                    $reason = match (true) {
                        $wantsToDemote && $wantsToDeactivate
                            => 'Anda adalah admin terakhir dan tidak dapat menonaktifkan akun sendiri atau mengubah peran sendiri.',
                        $wantsToDeactivate
                            => 'Anda tidak dapat menonaktifkan akun Anda sendiri saat ini adalah satu-satunya admin aktif.',
                        $wantsToDemote
                            => 'Anda tidak dapat mengubah peran Anda sendiri ke pegawai saat ini adalah satu-satunya admin aktif.',
                    };
                }

                if ($wantsToDeactivate) {
                    $validator->errors()->add('is_active', $reason);
                }
                if ($wantsToDemote) {
                    $validator->errors()->add('role', $reason);
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'kata sandi',
            'role' => 'peran',
            'is_active' => 'status',
            'timezone' => 'zona waktu',
        ];
    }
}
