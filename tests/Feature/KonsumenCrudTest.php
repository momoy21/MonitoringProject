<?php

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

describe('Konsumen CRUD', function () {
    describe('As Super Admin', function () {
        beforeEach(function () {
            $this->superAdmin = $this->createSuperAdminUser();
            $this->actingAs($this->superAdmin);
        });

        it('can access index page', function () {
            $response = $this->get(route('konsumen.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('konsumen.create'));

            $response->assertStatus(200);
        });

        it('can store new konsumen', function () {
            $data = [
                'konsumen' => 'PT ABC Indonesia',
                'alamat1' => 'Jl. Merdeka No. 1',
                'kode_pos' => '10110',
                'telp_kantor' => '021-1234567',
                'email' => 'info@abc.co.id',
                'status' => 'A',
            ];

            $response = $this->post(route('konsumen.store'), $data);

            $response->assertRedirect(route('konsumen.index'));
            $this->assertDatabaseHas('konsumen', [
                'konsumen' => 'PT ABC Indonesia',
            ]);
        });

        it('can show konsumen', function () {
            $konsumen = Konsumen::factory()->create();

            $response = $this->get(route('konsumen.show', $konsumen));

            $response->assertStatus(200);
            $response->assertSee($konsumen->konsumen);
        });

        it('can access edit page', function () {
            $konsumen = Konsumen::factory()->create();

            $response = $this->get(route('konsumen.edit', $konsumen));

            $response->assertStatus(200);
        });

        it('can update konsumen', function () {
            $konsumen = Konsumen::factory()->create();

            $response = $this->put(route('konsumen.update', $konsumen), [
                'konsumen' => 'Updated Company',
                'alamat1' => 'Jl. Test',
                'kode_pos' => '10200',
                'telp_kantor' => '021-9999999',
                'email' => 'test@test.com',
                'status' => 'A',
            ]);

            $response->assertRedirect(route('konsumen.index'));
            $this->assertDatabaseHas('konsumen', [
                'id_konsumen' => $konsumen->id_konsumen,
                'konsumen' => 'Updated Company',
            ]);
        });

        it('can destroy konsumen', function () {
            $konsumen = Konsumen::factory()->create();

            $response = $this->delete(route('konsumen.destroy', $konsumen));

            $response->assertRedirect(route('konsumen.index'));
            $this->assertDatabaseMissing('konsumen', [
                'id_konsumen' => $konsumen->id_konsumen,
            ]);
        });
    });

    describe('As Project Manager', function () {
        beforeEach(function () {
            $this->pm = $this->createProjectManagerUser();
            $this->actingAs($this->pm);
        });

        it('can access index page', function () {
            $response = $this->get(route('konsumen.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('konsumen.create'));

            $response->assertStatus(200);
        });

        it('can store new konsumen', function () {
            $data = [
                'konsumen' => 'PT XYZ Jaya',
                'alamat1' => 'Jl. Sudirman',
                'kode_pos' => '10200',
                'telp_kantor' => '021-1111111',
                'email' => 'xyz@xyz.co.id',
                'status' => 'A',
            ];

            $response = $this->post(route('konsumen.store'), $data);

            $response->assertRedirect(route('konsumen.index'));
            $this->assertDatabaseHas('konsumen', [
                'konsumen' => 'PT XYZ Jaya',
            ]);
        });
    });

    describe('As Guest', function () {
        it('redirects to login', function () {
            $response = $this->get(route('konsumen.index'));

            $response->assertRedirect('/login');
        });
    });
});
