<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Division;
use App\Models\GuestLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestBookSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@bukutamu.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password')]
        );

        $this->division = Division::firstOrCreate(
            ['name' => 'Bidang Uji Coba'],
            ['is_active' => true]
        );
    }

    public function test_public_kiosk_page_loads_with_active_divisions(): void
    {
        $response = $this->get(route('guestbook.index'));

        $response->assertStatus(200);
        $response->assertSee('BUKU TAMU DIGITAL');
        $response->assertSee($this->division->name);
    }

    public function test_guest_lookup_autofill_returns_data_for_existing_phone(): void
    {
        GuestLog::create([
            'division_id' => $this->division->id,
            'name' => 'John Doe',
            'phone' => '089999888877',
            'institution' => 'PT Teknologi Nusantara',
            'purpose' => 'Konsultasi Perizinan',
            'photo_path' => 'guests/test.jpg',
            'visit_date' => now()->toDateString(),
            'visit_time' => now()->toTimeString(),
        ]);

        $response = $this->getJson(route('guestbook.lookup', ['phone' => '089999888877']));

        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'data' => [
                'name' => 'John Doe',
                'institution' => 'PT Teknologi Nusantara',
            ],
        ]);
    }

    public function test_guest_can_submit_log_with_base64_photo(): void
    {
        // 1x1 transparent png in base64
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $payload = [
            'name' => 'Aditya Pratama',
            'phone' => '081233445566',
            'institution' => 'Dinas Pariwisata',
            'division_id' => $this->division->id,
            'person_to_meet' => 'Kepala Seksi',
            'purpose' => 'Koordinasi event kreatif tahunan',
            'photo' => $fakeBase64,
        ];

        $response = $this->postJson(route('guestbook.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('guest_logs', [
            'name' => 'Aditya Pratama',
            'phone' => '081233445566',
            'institution' => 'Dinas Pariwisata',
            'division_id' => $this->division->id,
        ]);

        $created = GuestLog::where('phone', '081233445566')->first();
        $this->assertNotNull($created);
        $this->assertNull($created->social_media_platform);
        $this->assertNull($created->social_media_handle);
        Storage::disk('public')->assertExists($created->photo_path);
    }

    public function test_guest_can_submit_optional_social_media_and_lookup_does_not_autofill_it(): void
    {
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('guestbook.store'), [
            'name' => 'Rina Kartika',
            'phone' => '081299887766',
            'social_media_platform' => 'tiktok',
            'social_media_handle' => 'rinakartika',
            'institution' => 'Komunitas Kreatif',
            'division_id' => $this->division->id,
            'purpose' => 'Diskusi kolaborasi konten',
            'photo' => $fakeBase64,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('guest_logs', [
            'phone' => '081299887766',
            'social_media_platform' => 'tiktok',
            'social_media_handle' => '@rinakartika',
        ]);

        // Lookup must NOT autofill social media
        $lookup = $this->getJson(route('guestbook.lookup', ['phone' => '081299887766']));
        $lookup->assertStatus(200)
            ->assertJsonMissingPath('data.social_media_platform')
            ->assertJsonMissingPath('data.social_media_handle');

        // Admin can update social media
        $created = GuestLog::where('phone', '081299887766')->firstOrFail();
        $this->actingAs($this->admin)
            ->put(route('admin.guest-logs.update', $created), [
                'name' => 'Rina Kartika',
                'phone' => '081299887766',
                'social_media_platform' => 'youtube',
                'social_media_handle' => '@rinachannel',
                'institution' => 'Komunitas Kreatif',
                'division_id' => $this->division->id,
                'purpose' => 'Diskusi kolaborasi konten',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('guest_logs', [
            'id' => $created->id,
            'social_media_platform' => 'youtube',
            'social_media_handle' => '@rinachannel',
        ]);

        // Guest can submit WITHOUT selecting a division (division is optional)
        $noDivRes = $this->postJson(route('guestbook.store'), [
            'name' => 'Budi Santoso',
            'phone' => '081211223344',
            'institution' => 'Umum',
            'purpose' => 'Kunjungan informasi layanan',
            'photo' => $fakeBase64,
        ]);
        $noDivRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('guest_logs', [
            'phone' => '081211223344',
            'division_id' => null,
        ]);
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'admin@bukutamu.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        $dashboard = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Buku Tamu Ekraf Majalengka');
    }

    public function test_admin_can_view_and_filter_guest_logs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.guest-logs.index', [
            'division_id' => $this->division->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Buku Tamu');
    }

    public function test_admin_can_export_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.guest-logs.export.pdf'));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_admin_can_export_excel_xlsx(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.guest-logs.export.excel'));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_manage_divisions_crud(): void
    {
        // 1. Create
        $createRes = $this->actingAs($this->admin)->post(route('admin.divisions.store'), [
            'name' => 'Divisi Riset Baru',
            'is_active' => '1',
        ]);
        $createRes->assertRedirect();
        $this->assertDatabaseHas('divisions', ['name' => 'Divisi Riset Baru', 'is_active' => true]);

        $div = Division::where('name', 'Divisi Riset Baru')->first();

        // 2. Toggle Status
        $toggleRes = $this->actingAs($this->admin)->patch(route('admin.divisions.toggle', $div));
        $toggleRes->assertRedirect();
        $this->assertFalse((bool) $div->fresh()->is_active);

        // 3. Update
        $updateRes = $this->actingAs($this->admin)->put(route('admin.divisions.update', $div), [
            'name' => 'Divisi Riset Diperbarui',
            'is_active' => '1',
        ]);
        $updateRes->assertRedirect();
        $this->assertDatabaseHas('divisions', ['id' => $div->id, 'name' => 'Divisi Riset Diperbarui']);

        // 4. Delete
        $deleteRes = $this->actingAs($this->admin)->delete(route('admin.divisions.destroy', $div));
        $deleteRes->assertRedirect();
        $this->assertDatabaseMissing('divisions', ['id' => $div->id]);
    }

    public function test_admin_can_update_email_and_password(): void
    {
        $page = $this->actingAs($this->admin)->get(route('admin.account.edit'));
        $page->assertStatus(200)->assertSee('Pengaturan Akun');

        // Wrong current password rejected for email update
        $badEmailRes = $this->actingAs($this->admin)->put(route('admin.account.email.update'), [
            'email' => 'admin.baru@bukutamu.com',
            'current_password_email' => 'salah123',
        ]);
        $badEmailRes->assertSessionHasErrors('current_password_email', null, 'updateEmail');

        // Valid email update
        $okEmailRes = $this->actingAs($this->admin)->put(route('admin.account.email.update'), [
            'email' => 'admin.baru@bukutamu.com',
            'current_password_email' => 'password',
        ]);
        $okEmailRes->assertRedirect()->assertSessionHas('success');
        $this->assertEquals('admin.baru@bukutamu.com', $this->admin->fresh()->email);

        // Valid password update
        $okPassRes = $this->actingAs($this->admin)->put(route('admin.account.password.update'), [
            'current_password' => 'password',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);
        $okPassRes->assertRedirect()->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('passwordbaru123', $this->admin->fresh()->password));
    }
}
