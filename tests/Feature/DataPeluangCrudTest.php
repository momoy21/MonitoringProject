<?php

use App\Models\DataPeluang;
use App\Models\Konsumen;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestHelpers;

uses(RefreshDatabase::class, TestHelpers::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('DataPeluang CRUD', function () {
    describe('As Super Admin', function () {
        beforeEach(function () {
            $this->superAdmin = $this->createSuperAdminUser();
            $this->actingAs($this->superAdmin);
        });

        it('can access index page', function () {
            $response = $this->get(route('datapeluang.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('datapeluang.create'));

            $response->assertStatus(200);
        });

        it('can store new data peluang', function () {
            $konsumen = Konsumen::factory()->create();

            $data = [
                'peluang' => 'Proyek Pengembangan Sistem',
                'id_konsumen' => $konsumen->id_konsumen,
                'kontak_person' => 'John Doe',
                'no_hp' => '081234567890',
                'lokasi' => 'Jakarta',
                'status' => 'N',
            ];

            $response = $this->post(route('datapeluang.store'), $data);

            $response->assertRedirect(route('datapeluang.index'));
            $this->assertDatabaseHas('data_peluang', [
                'peluang' => 'Proyek Pengembangan Sistem',
            ]);
        });

        it('can show data peluang', function () {
            $dataPeluang = DataPeluang::factory()->create();

            $response = $this->get(route('datapeluang.show', $dataPeluang));

            $response->assertStatus(200);
            $response->assertSee($dataPeluang->peluang);
        });

        it('can access edit page', function () {
            $dataPeluang = DataPeluang::factory()->create();

            $response = $this->get(route('datapeluang.edit', $dataPeluang));

            $response->assertStatus(200);
        });

        it('can update data peluang', function () {
            $dataPeluang = DataPeluang::factory()->create();

            $response = $this->put(route('datapeluang.update', $dataPeluang), [
                'peluang' => 'Updated Peluang',
                'id_konsumen' => $dataPeluang->id_konsumen,
                'kontak_person' => 'Jane Doe',
                'no_hp' => '089999999999',
                'lokasi' => 'Bandung',
                'status' => 'I',
            ]);

            $response->assertRedirect(route('datapeluang.index'));
            $this->assertDatabaseHas('data_peluang', [
                'id_datapeluang' => $dataPeluang->id_datapeluang,
                'peluang' => 'Updated Peluang',
            ]);
        });

        it('can destroy data peluang', function () {
            $dataPeluang = DataPeluang::factory()->create();

            $response = $this->delete(route('datapeluang.destroy', $dataPeluang));

            $response->assertRedirect(route('datapeluang.index'));
            $this->assertDatabaseMissing('data_peluang', [
                'id_datapeluang' => $dataPeluang->id_datapeluang,
            ]);
        });
    });

    describe('As Project Manager', function () {
        beforeEach(function () {
            $this->pm = $this->createProjectManagerUser();
            $this->actingAs($this->pm);
        });

        it('can access index page', function () {
            $response = $this->get(route('datapeluang.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('datapeluang.create'));

            $response->assertStatus(200);
        });

        it('can store new data peluang', function () {
            $konsumen = Konsumen::factory()->create();

            $data = [
                'peluang' => 'Proyek Baru',
                'id_konsumen' => $konsumen->id_konsumen,
                'kontak_person' => 'Test User',
                'no_hp' => '081234567890',
                'lokasi' => 'Jakarta',
                'status' => 'N',
            ];

            $response = $this->post(route('datapeluang.store'), $data);

            $response->assertRedirect(route('datapeluang.index'));
            $this->assertDatabaseHas('data_peluang', [
                'peluang' => 'Proyek Baru',
            ]);
        });
    });

    describe('As Guest', function () {
        it('redirects to login', function () {
            $response = $this->get(route('datapeluang.index'));

            $response->assertRedirect('/login');
        });
    });
});
