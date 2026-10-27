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
        Storage::disk('public')->assertExists($created->photo_path);
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

    public function test_admin_can_export_excel_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.guest-logs.export.excel'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
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
}
