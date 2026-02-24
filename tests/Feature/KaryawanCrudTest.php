<?php

use App\Models\Karyawan;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestHelpers;

uses(RefreshDatabase::class, TestHelpers::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('Karyawan CRUD', function () {
    describe('As Super Admin', function () {
        beforeEach(function () {
            $this->superAdmin = $this->createSuperAdminUser();
            $this->actingAs($this->superAdmin);
        });

        it('can access index page', function () {
            $response = $this->get(route('karyawan.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('karyawan.create'));

            $response->assertStatus(200);
        });

        it('can store new karyawan', function () {
            $data = [
                'nik' => '999',
                'nama' => 'John Doe',
                'status' => 'T',
                'aktif' => 'Y',
            ];

            $response = $this->post(route('karyawan.store'), $data);

            $response->assertRedirect(route('karyawan.index'));
            $this->assertDatabaseHas('karyawan', [
                'nik' => '999',
                'nama' => 'John Doe',
            ]);
        });

        it('can show karyawan', function () {
            $karyawan = Karyawan::factory()->create();

            $response = $this->get(route('karyawan.show', $karyawan));

            $response->assertStatus(200);
            $response->assertSee($karyawan->nama);
        });

        it('can access edit page', function () {
            $karyawan = Karyawan::factory()->create();

            $response = $this->get(route('karyawan.edit', $karyawan));

            $response->assertStatus(200);
        });

        it('can update karyawan', function () {
            $karyawan = Karyawan::factory()->create();

            $response = $this->put(route('karyawan.update', $karyawan), [
                'nama' => 'Updated Name',
                'status' => 'T',
                'aktif' => 'Y',
            ]);

            $response->assertRedirect(route('karyawan.index'));
            $this->assertDatabaseHas('karyawan', [
                'nik' => $karyawan->nik,
                'nama' => 'Updated Name',
            ]);
        });

        it('can destroy karyawan', function () {
            $karyawan = Karyawan::factory()->create();

            $response = $this->delete(route('karyawan.destroy', $karyawan));

            $response->assertRedirect(route('karyawan.index'));
            $this->assertDatabaseMissing('karyawan', [
                'nik' => $karyawan->nik,
            ]);
        });
    });

    describe('As Project Manager', function () {
        beforeEach(function () {
            $this->pm = $this->createProjectManagerUser();
            $this->actingAs($this->pm);
        });

        it('cannot access index page - returns 403', function () {
            $response = $this->get(route('karyawan.index'));

            $response->assertStatus(403);
        });
    });
});
