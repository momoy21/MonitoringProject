<?php

use App\Models\MasterDivisi;
use App\Models\RABProyek;

describe('MasterDivisi Model', function () {
    describe('Scopes', function () {
        beforeEach(function () {
            MasterDivisi::factory()->create(['status' => 'A']);
            MasterDivisi::factory()->create(['status' => 'A']);
            MasterDivisi::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = MasterDivisi::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });

        it('scopeSearch finds by kode_divisi', function () {
            $divisi = MasterDivisi::factory()->create(['kode_divisi' => 'DIV-01']);

            $result = MasterDivisi::search('DIV-01')->get();

            expect($result->first()->kode_divisi)->toBe('DIV-01');
        });

        it('scopeSearch finds by nama_divisi', function () {
            MasterDivisi::factory()->create(['nama_divisi' => 'Divisi Keuangan']);
            MasterDivisi::factory()->create(['nama_divisi' => 'Divisi IT']);

            $result = MasterDivisi::search('Keuangan')->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->nama_divisi)->toContain('Keuangan');
        });
    });

    describe('Relationships', function () {
        it('has rabProyek relationship', function () {
            $divisi = MasterDivisi::factory()->create();

            expect($divisi->rabProyek())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        });
    });

    describe('Route Key', function () {
        it('uses kode_divisi as route key', function () {
            $divisi = MasterDivisi::factory()->create();

            expect($divisi->getRouteKeyName())->toBe('kode_divisi');
        });
    });
});
