<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const USERS = [
        [
            'name' => 'Suherlan',
            'email' => 'suherlan@simaan-watter.com',
            'role' => 'admin',
        ],
        [
            'name' => 'Rivansyah',
            'email' => 'rivansyah@simaan-watter.com',
            'role' => 'pegawai',
        ],
        [
            'name' => 'Dini',
            'email' => 'dini@simaan-watter.com',
            'role' => 'pegawai',
        ],
        [
            'name' => 'Hamdan',
            'email' => 'hamdan@simaan-watter.com',
            'role' => 'pegawai',
        ],
        [
            'name' => 'Ayin',
            'email' => 'ayin@simaan-watter.com',
            'role' => 'pegawai',
        ],
    ];

    public function run(): void
    {
        User::query()
            ->whereIn('email', ['admin@simaanwater.test', 'pegawai@simaanwater.test'])
            ->delete();

        foreach (self::USERS as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'is_active' => true,
                    'email_verified_at' => CarbonImmutable::parse('2026-01-01 00:00:00', 'UTC'),
                ]
            );
        }
    }
}
