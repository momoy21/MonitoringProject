<?php

use App\Models\User;
use App\Models\BidangJasa;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
});

describe('User Model', function () {
    describe('getAllowedBidangJasaIds', function () {
        it('returns empty array when bidang_jasa_ids is null', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => null,
            ]);

            expect($user->getAllowedBidangJasaIds())->toBe([]);
        });

        it('returns empty array when bidang_jasa_ids is empty string', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => '',
            ]);

            expect($user->getAllowedBidangJasaIds())->toBe([]);
        });

        it('returns parsed array from JSON string', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => json_encode(['01', '02', '03']),
            ]);

            $result = $user->getAllowedBidangJasaIds();

            expect($result)->toBe(['01', '02', '03']);
        });

        it('returns empty array when JSON is invalid', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => 'invalid-json',
            ]);

            expect($user->getAllowedBidangJasaIds())->toBe([]);
        });
    });

    describe('hasAccessToBidangJasa', function () {
        it('Super Admin has access to all bidang jasa', function () {
            $user = User::factory()->create();
            $user->assignRole('Super Admin');

            expect($user->hasAccessToBidangJasa('99'))->toBeTrue();
        });

        it('PM with empty bidang_jasa_ids has access to all', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => null,
            ]);
            $user->assignRole('Project Manager');

            expect($user->hasAccessToBidangJasa('01'))->toBeTrue();
        });

        it('PM with specific bidang_jasa_ids can access allowed', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => json_encode(['01', '02']),
            ]);
            $user->assignRole('Project Manager');

            expect($user->hasAccessToBidangJasa('01'))->toBeTrue();
            expect($user->hasAccessToBidangJasa('02'))->toBeTrue();
        });

        it('PM cannot access non-allowed bidang jasa', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => json_encode(['01', '02']),
            ]);
            $user->assignRole('Project Manager');

            expect($user->hasAccessToBidangJasa('03'))->toBeFalse();
        });

        it('User without role has access by default', function () {
            $user = User::factory()->create();

            expect($user->hasAccessToBidangJasa('01'))->toBeTrue();
        });
    });

    describe('filterBidangJasaQuery', function () {
        it('Super Admin sees all records', function () {
            $user = User::factory()->create();
            $user->assignRole('Super Admin');

            $query = BidangJasa::query();
            $result = $user->filterBidangJasaQuery($query);

            expect($result->toBase()->getBindings())->toBe([]);
        });

        it('PM with empty bidang_jasa_ids sees all records', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => null,
            ]);
            $user->assignRole('Project Manager');

            $query = BidangJasa::query();
            $result = $user->filterBidangJasaQuery($query);

            expect($result->toBase()->getBindings())->toBe([]);
        });

        it('PM with specific IDs filters query', function () {
            $user = User::factory()->create([
                'bidang_jasa_ids' => json_encode(['01', '02']),
            ]);
            $user->assignRole('Project Manager');

            $query = BidangJasa::query();
            $result = $user->filterBidangJasaQuery($query);

            $sql = $result->toBase()->toSql();
            expect($sql)->toContain('where');
        });
    });
});
