<?php

use App\Models\KondisiProyek;

describe('KondisiProyek Model', function () {
    describe('ID Generation', function () {
        it('returns K1 when table is empty', function () {
            $id = KondisiProyek::generateNextId();

            expect($id)->toBe('K1');
        });

        it('increments correctly from existing records', function () {
            KondisiProyek::factory()->create(['id_kondisi_proyek' => 'K1']);
            KondisiProyek::factory()->create(['id_kondisi_proyek' => 'K2']);

            $id = KondisiProyek::generateNextId();

            expect($id)->toBe('K3');
        });

        it('auto-generates ID when creating without ID', function () {
            $kondisiProyek = KondisiProyek::factory()->create();

            expect($kondisiProyek->id_kondisi_proyek)->not->toBeNull();
            expect($kondisiProyek->id_kondisi_proyek)->toMatch('/^K\d+$/');
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            KondisiProyek::factory()->create(['status' => 'A']);
            KondisiProyek::factory()->create(['status' => 'A']);
            KondisiProyek::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = KondisiProyek::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });

        it('scopeSearch finds by id_kondisi_proyek', function () {
            $kondisiProyek = KondisiProyek::factory()->create(['id_kondisi_proyek' => 'K5']);

            $result = KondisiProyek::search('K5')->get();

            expect($result->first()->id_kondisi_proyek)->toBe('K5');
        });

        it('scopeSearch finds by desc_kondisi_proyek', function () {
            KondisiProyek::factory()->create(['desc_kondisi_proyek' => 'Proyek Baru']);
            KondisiProyek::factory()->create(['desc_kondisi_proyek' => 'Proyek Lanjutan']);

            $result = KondisiProyek::search('Baru')->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->desc_kondisi_proyek)->toContain('Baru');
        });
    });

    describe('Route Key', function () {
        it('uses id_kondisi_proyek as route key', function () {
            $kondisiProyek = KondisiProyek::factory()->create();

            expect($kondisiProyek->getRouteKeyName())->toBe('id_kondisi_proyek');
        });
    });
});
