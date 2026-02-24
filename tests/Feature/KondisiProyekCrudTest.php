<?php

use App\Models\KondisiProyek;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestHelpers;

uses(RefreshDatabase::class, TestHelpers::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('KondisiProyek CRUD', function () {
    describe('As Super Admin', function () {
        beforeEach(function () {
            $this->superAdmin = $this->createSuperAdminUser();
            $this->actingAs($this->superAdmin);
        });

        it('can access index page', function () {
            $response = $this->get(route('kondisiproyek.index'));

            $response->assertStatus(200);
        });

        it('can access create page', function () {
            $response = $this->get(route('kondisiproyek.create'));

            $response->assertStatus(200);
        });

        it('can store new kondisi proyek', function () {
            $data = [
                'desc_kondisi_proyek' => 'Proyek Baru',
                'status' => 'A',
            ];

            $response = $this->post(route('kondisiproyek.store'), $data);

            $response->assertRedirect(route('kondisiproyek.index'));
            $this->assertDatabaseHas('kondisiproyek', [
                'desc_kondisi_proyek' => 'Proyek Baru',
            ]);
        });

        it('can show kondisi proyek', function () {
            $kondisiProyek = KondisiProyek::factory()->create();

            $response = $this->get(route('kondisiproyek.show', $kondisiProyek));

            $response->assertStatus(200);
            $response->assertSee($kondisiProyek->desc_kondisi_proyek);
        });

        it('can access edit page', function () {
            $kondisiProyek = KondisiProyek::factory()->create();

            $response = $this->get(route('kondisiproyek.edit', $kondisiProyek));

            $response->assertStatus(200);
        });

        it('can update kondisi proyek', function () {
            $kondisiProyek = KondisiProyek::factory()->create();

            $response = $this->put(route('kondisiproyek.update', $kondisiProyek), [
                'desc_kondisi_proyek' => 'Updated Description',
                'status' => 'A',
            ]);

            $response->assertRedirect(route('kondisiproyek.index'));
            $this->assertDatabaseHas('kondisiproyek', [
                'id_kondisi_proyek' => $kondisiProyek->id_kondisi_proyek,
                'desc_kondisi_proyek' => 'Updated Description',
            ]);
        });

        it('can destroy kondisi proyek', function () {
            $kondisiProyek = KondisiProyek::factory()->create();

            $response = $this->delete(route('kondisiproyek.destroy', $kondisiProyek));

            $response->assertRedirect(route('kondisiproyek.index'));
            $this->assertDatabaseMissing('kondisiproyek', [
                'id_kondisi_proyek' => $kondisiProyek->id_kondisi_proyek,
            ]);
        });
    });

    describe('As Project Manager', function () {
        beforeEach(function () {
            $this->pm = $this->createProjectManagerUser();
            $this->actingAs($this->pm);
        });

        it('cannot access index page - returns 403', function () {
            $response = $this->get(route('kondisiproyek.index'));

            $response->assertStatus(403);
        });

        it('cannot access create page - returns 403', function () {
            $response = $this->get(route('kondisiproyek.create'));

            $response->assertStatus(403);
        });

        it('cannot store new kondisi proyek - returns 403', function () {
            $data = [
                'desc_kondisi_proyek' => 'Proyek Baru',
                'status' => 'A',
            ];

            $response = $this->post(route('kondisiproyek.store'), $data);

            $response->assertStatus(403);
        });
    });
});
