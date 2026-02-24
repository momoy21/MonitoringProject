<?php

use App\Models\MasterDivisi;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestHelpers;

uses(RefreshDatabase::class, TestHelpers::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('MasterDivisi CRUD', function () {
    describe('As Super Admin', function () {
        beforeEach(function () {
            $this->superAdmin = $this->createSuperAdminUser();
            $this->actingAs($this->superAdmin);
        });

        it('can access index page', function () {
            $response = $this->get(route('masterdivisi.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('masterdivisi.create'));

            $response->assertStatus(200);
        });

        it('can store new master divisi', function () {
            $data = [
                'kode_divisi' => 'DIV-TEST',
                'nama_divisi' => 'Divisi Testing',
                'status' => 'A',
            ];

            $response = $this->post(route('masterdivisi.store'), $data);

            $response->assertRedirect(route('masterdivisi.index'));
            $this->assertDatabaseHas('master_divisi', [
                'kode_divisi' => 'DIV-TEST',
                'nama_divisi' => 'Divisi Testing',
            ]);
        });

        it('can show master divisi', function () {
            $divisi = MasterDivisi::factory()->create();

            $response = $this->get(route('masterdivisi.show', $divisi));

            $response->assertStatus(200);
            $response->assertSee($divisi->nama_divisi);
        });

        it('can access edit page', function () {
            $divisi = MasterDivisi::factory()->create();

            $response = $this->get(route('masterdivisi.edit', $divisi));

            $response->assertStatus(200);
        });

        it('can update master divisi', function () {
            $divisi = MasterDivisi::factory()->create();

            $response = $this->put(route('masterdivisi.update', $divisi), [
                'nama_divisi' => 'Updated Division',
                'status' => 'A',
            ]);

            $response->assertRedirect(route('masterdivisi.index'));
            $this->assertDatabaseHas('master_divisi', [
                'kode_divisi' => $divisi->kode_divisi,
                'nama_divisi' => 'Updated Division',
            ]);
        });

        it('can destroy master divisi', function () {
            $divisi = MasterDivisi::factory()->create();

            $response = $this->delete(route('masterdivisi.destroy', $divisi));

            $response->assertRedirect(route('masterdivisi.index'));
            $this->assertDatabaseMissing('master_divisi', [
                'kode_divisi' => $divisi->kode_divisi,
            ]);
        });
    });

    describe('As Project Manager', function () {
        beforeEach(function () {
            $this->pm = $this->createProjectManagerUser();
            $this->actingAs($this->pm);
        });

        it('cannot access index page - returns 403', function () {
            $response = $this->get(route('masterdivisi.index'));

            $response->assertStatus(403);
        });
    });
});
