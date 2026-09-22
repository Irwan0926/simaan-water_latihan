<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi@contoh.com',
            'role' => 'pegawai',
            'is_active' => '1',
            'password' => 'RahasiaKuat123',
            'password_confirmation' => 'RahasiaKuat123',
        ], $override);
    }

    public function test_admin_bisa_menambah_pengguna(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload());

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'budi@contoh.com',
            'role' => 'pegawai',
            'is_active' => true,
        ]);
    }

    /**
     * Regresi: email dengan huruf kapital dulu ditolak aturan `lowercase`,
     * sehingga admin merasa "tidak bisa menambahkan pengguna".
     */
    public function test_email_huruf_kapital_dinormalkan_bukan_ditolak(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload([
                'email' => 'Budi.Santoso@Contoh.Com',
            ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'budi.santoso@contoh.com']);
    }

    public function test_email_dengan_spasi_dipangkas(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload([
                'email' => '  budi@contoh.com  ',
            ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'budi@contoh.com']);
    }

    /**
     * Regresi: email_verified_at bukan atribut fillable, dulu diam-diam
     * dibuang sehingga akun baru terkunci middleware 'verified'.
     */
    public function test_pengguna_baru_langsung_terverifikasi(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload());

        $baru = User::where('email', 'budi@contoh.com')->firstOrFail();

        $this->assertNotNull($baru->email_verified_at, 'Pengguna baru harus terverifikasi.');
        $this->assertTrue($baru->hasVerifiedEmail());
    }

    public function test_pengguna_baru_bisa_langsung_membuka_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload());

        $baru = User::where('email', 'budi@contoh.com')->firstOrFail();

        $this->actingAs($baru)->get(route('dashboard'))->assertOk();
    }

    public function test_password_dihash_bukan_disimpan_polos(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload());

        $baru = User::where('email', 'budi@contoh.com')->firstOrFail();

        $this->assertNotSame('RahasiaKuat123', $baru->password);
        $this->assertTrue(password_verify('RahasiaKuat123', $baru->password));
    }

    public function test_email_duplikat_ditolak(): void
    {
        User::factory()->create(['email' => 'budi@contoh.com']);

        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload());

        $response->assertSessionHasErrors('email');
    }

    public function test_konfirmasi_sandi_tidak_sama_ditolak(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload([
                'password_confirmation' => 'Berbeda123',
            ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'budi@contoh.com']);
    }

    public function test_input_lama_dikembalikan_saat_validasi_gagal(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload([
                'email' => 'bukan-email',
            ]));

        $response->assertSessionHasErrors('email');
        // Modal harus bisa dibuka ulang dengan isian sebelumnya.
        $response->assertSessionHasInput('name', 'Budi Santoso');
    }

    public function test_pegawai_tidak_boleh_menambah_pengguna(): void
    {
        $pegawai = User::factory()->create([
            'role' => 'pegawai',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($pegawai)
            ->post(route('users.store'), $this->payload())
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'budi@contoh.com']);
    }

    public function test_admin_bisa_menyimpan_zona_waktu_pengguna(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload([
                'timezone' => 'Asia/Makassar',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'budi@contoh.com',
            'timezone' => 'Asia/Makassar',
        ]);
    }

    public function test_zona_waktu_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), $this->payload([
                'timezone' => 'Bulan/Kawah',
            ]))
            ->assertSessionHasErrors('timezone');
    }
}
