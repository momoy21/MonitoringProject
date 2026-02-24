<?php

use App\Models\JenisProyek;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestHelpers;

uses(RefreshDatabase::class, TestHelpers::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('JenisProyek CRUD', function () {
    describe('As Super Admin', function () {
        beforeEach(function () {
            $this->superAdmin = $this->createSuperAdminUser();
            $this->actingAs($this->superAdmin);
        });

        it('can access index page', function () {
            $response = $this->get(route('jenisproyek.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('jenisproyek.create'));

            $response->assertStatus(200);
        });

        it('can store new jenis proyek', function () {
            $data = [
                'kode_jenis' => 'PJ',
                'nama_jenis' => 'Proyek Jaya',
                'status' => 'A',
            ];

            $response = $this->post(route('jenisproyek.store'), $data);

            $response->assertRedirect(route('jenisproyek.index'));
            $this->assertDatabaseHas('jenis_proyek', [
                'kode_jenis' => 'PJ',
                'nama_jenis' => 'Proyek Jaya',
            ]);
        });

        it('can show jenis proyek', function () {
            $jenisProyek = JenisProyek::factory()->create();

            $response = $this->get(route('jenisproyek.show', $jenisProyek));

            $response->assertStatus(200);
            $response->assertSee($jenisProyek->nama_jenis);
        });

        it('can access edit page', function () {
            $jenisProyek = JenisProyek::factory()->create();

            $response = $this->get(route('jenisproyek.edit', $jenisProyek));

            $response->assertStatus(200);
        });

        it('can update jenis proyek', function () {
            $jenisProyek = JenisProyek::factory()->create();

            $response = $this->put(route('jenisproyek.update', $jenisProyek), [
                'nama_jenis' => 'Updated Jenis',
                'status' => 'A',
            ]);

            $response->assertRedirect(route('jenisproyek.index'));
            $this->assertDatabaseHas('jenis_proyek', [
                'kode_jenis' => $jenisProyek->kode_jenis,
                'nama_jenis' => 'Updated Jenis',
            ]);
        });

        it('can destroy jenis proyek', function () {
            $jenisProyek = JenisProyek::factory()->create();

            $response = $this->delete(route('jenisproyek.destroy', $jenisProyek));

            $response->assertRedirect(route('jenisproyek.index'));
            $this->assertDatabaseMissing('jenis_proyek', [
                'kode_jenis' => $jenisProyek->kode_jenis,
            ]);
        });
    });

    describe('As Project Manager', function () {
        beforeEach(function () {
            $this->pm = $this->createProjectManagerUser();
            $this->actingAs($this->pm);
        });

        it('cannot access index page - returns 403', function () {
            $response = $this->get(route('jenisproyek.index'));

            $response->assertStatus(403);
        });
    });
});
