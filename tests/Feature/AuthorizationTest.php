<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestHelpers;

uses(RefreshDatabase::class, TestHelpers::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('Authorization Tests', function () {
    describe('Guest Access', function () {
        it('redirects guest from Super Admin routes to login', function () {
            $routes = [
                'bidangjasa.index',
                'bidangjasa.create',
                'kondisiproyek.index',
                'mastermanager.index',
                'karyawan.index',
            ];

            foreach ($routes as $route) {
                $response = $this->get(route($route));
                $response->assertRedirect('/login');
            }
        });

        it('redirects guest from shared routes to login', function () {
            $routes = [
                'konsumen.index',
                'datapeluang.index',
            ];

            foreach ($routes as $route) {
                $response = $this->get(route($route));
                $response->assertRedirect('/login');
            }
        });

        it('redirects guest from dashboard to login', function () {
            $response = $this->get('/dashboard');
            $response->assertRedirect('/login');
        });
    });

    describe('Project Manager Access', function () {
        it('PM cannot access Super Admin-only routes - returns 403', function () {
            $pm = $this->createProjectManagerUser();
            $this->actingAs($pm);

            $routes = [
                'bidangjasa.index',
                'bidangjasa.create',
                'kondisiproyek.index',
                'kondisiproyek.create',
                'mastermanager.index',
                'karyawan.index',
                'karyawan.create',
            ];

            foreach ($routes as $route) {
                $response = $this->get(route($route));
                $response->assertStatus(403);
            }
        });

        it('PM can access shared routes', function () {
            $pm = $this->createProjectManagerUser();
            $this->actingAs($pm);

            $routes = [
                'konsumen.index',
                'konsumen.create',
                'datapeluang.index',
                'datapeluang.create',
            ];

            foreach ($routes as $route) {
                $response = $this->get(route($route));
                $response->assertStatus(200);
            }
        });

        it('PM with specific bidang jasa can access konsumen and dataproyek', function () {
            $bidangJasaIds = $this->createBidangJasaForUser($this->createProjectManagerUser(), 2);
            $pm = $this->createProjectManagerUser($bidangJasaIds);
            $this->actingAs($pm);

            $response = $this->get(route('konsumen.index'));
            $response->assertStatus(200);

            $response = $this->get(route('datapeluang.index'));
            $response->assertStatus(200);
        });

        it('PM cannot access Super Admin CRUD operations', function () {
            $pm = $this->createProjectManagerUser();
            $this->actingAs($pm);

            $routes = [
                ['POST', 'bidangjasa.store'],
                ['PUT', 'bidangjasa.update', ['bidangjasa' => '01']],
                ['DELETE', 'bidangjasa.destroy', ['bidangjasa' => '01']],
            ];

            foreach ($routes as $route) {
                $method = $route[0];
                $routeName = $route[1];
                $params = $route[2] ?? [];

                $response = $this->call($method, route($routeName, $params));
                $response->assertStatus(403);
            }
        });
    });

    describe('Super Admin Access', function () {
        it('Super Admin can access all routes', function () {
            $superAdmin = $this->createSuperAdminUser();
            $this->actingAs($superAdmin);

            $routes = [
                'bidangjasa.index',
                'bidangjasa.create',
                'kondisiproyek.index',
                'mastermanager.index',
                'karyawan.index',
                'konsumen.index',
                'datapeluang.index',
            ];

            foreach ($routes as $route) {
                $response = $this->get(route($route));
                $response->assertStatus(200);
            }
        });

        it('Super Admin can perform CRUD on Super Admin-only resources', function () {
            $superAdmin = $this->createSuperAdminUser();
            $this->actingAs($superAdmin);

            $response = $this->post(route('bidangjasa.store'), [
                'desc_bidjasa' => 'Test',
                'status' => 'A',
            ]);
            $response->assertRedirect(route('bidangjasa.index'));
        });
    });

    describe('Unauthenticated User Role Access', function () {
        it('user without role cannot access protected routes', function () {
            $user = User::factory()->create();
            $this->actingAs($user);

            $response = $this->get(route('konsumen.index'));
            $response->assertStatus(403);
        });
    });

    describe('Route Middleware', function () {
        it('Super Admin middleware blocks non-Super Admin', function () {
            $pm = $this->createProjectManagerUser();
            $this->actingAs($pm);

            $response = $this->get(route('karyawan.index'));
            $response->assertStatus(403);
        });

        it('Role middleware allows Super Admin', function () {
            $superAdmin = $this->createSuperAdminUser();
            $this->actingAs($superAdmin);

            $response = $this->get(route('karyawan.index'));
            $response->assertStatus(200);
        });
    });
});
