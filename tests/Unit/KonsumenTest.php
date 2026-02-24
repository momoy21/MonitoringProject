<?php

use App\Models\Konsumen;
use App\Models\Provinsi;
use App\Models\Kota;

beforeEach(function () {
    $this->provinsi = Provinsi::factory()->create();
    $this->kota = Kota::factory()->create(['provinsi_id' => $this->provinsi->id]);
});

describe('Konsumen Model', function () {
    describe('ID Generation', function () {
        it('generates K00001 when no records exist', function () {
            $id = Konsumen::generateIdKonsumen();

            expect($id)->toBe('K00001');
        });

        it('increments correctly from existing records', function () {
            Konsumen::factory()->create(['id_konsumen' => 'K00001']);
            Konsumen::factory()->create(['id_konsumen' => 'K00002']);

            $id = Konsumen::generateIdKonsumen();

            expect($id)->toBe('K00003');
        });

        it('auto-generates ID when creating without ID', function () {
            $konsumen = Konsumen::factory()->create();

            expect($konsumen->id_konsumen)->not->toBeNull();
            expect($konsumen->id_konsumen)->toMatch('/^K\d{5}$/');
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            Konsumen::factory()->create(['status' => 'A']);
            Konsumen::factory()->create(['status' => 'A']);
            Konsumen::factory()->create(['status' => 'N']);
        });

        it('scopeActive returns only active records', function () {
            $result = Konsumen::active()->get();

            expect($result)->toHaveCount(2);
            expect($result->every->status === 'A')->toBeTrue();
        });

        it('scopeInactive returns only inactive records', function () {
            $result = Konsumen::inactive()->get();

            expect($result)->toHaveCount(1);
            expect($result->every->status === 'N')->toBeTrue();
        });

        it('scopeSearch finds by konsumen name', function () {
            Konsumen::factory()->create(['konsumen' => 'PT ABC Indonesia']);
            Konsumen::factory()->create(['konsumen' => 'PT XYZ Jaya']);

            $result = Konsumen::search('ABC')->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->konsumen)->toContain('ABC');
        });

        it('scopeSearch finds by id_konsumen', function () {
            $konsumen = Konsumen::factory()->create();

            $result = Konsumen::search($konsumen->id_konsumen)->get();

            expect($result)->toHaveCount(1);
            expect($result->first()->id_konsumen)->toBe($konsumen->id_konsumen);
        });
    });

    describe('Accessors', function () {
        it('alamat_lengkap combines address fields', function () {
            $konsumen = Konsumen::factory()->create([
                'alamat1' => 'Jl. Merdeka No. 1',
                'alamat2' => 'Lt. 2',
                'kode_pos' => '10110',
            ]);

            $result = $konsumen->alamat_lengkap;

            expect($result)->toContain('Jl. Merdeka No. 1');
        });

        it('alamat_lengkap handles missing relations', function () {
            $konsumen = Konsumen::factory()->create([
                'alamat1' => 'Jl. Test',
                'alamat2' => null,
                'kode_pos' => null,
                'provinsi_id' => null,
                'kota_id' => null,
            ]);

            expect($konsumen->alamat_lengkap)->toContain('Jl. Test');
        });

        it('kontak returns formatted contact info', function () {
            $konsumen = Konsumen::factory()->create([
                'telp_kantor' => '021-1234567',
                'fax' => '021-1234568',
                'email' => 'test@company.com',
            ]);

            $result = $konsumen->kontak;

            expect($result)->toContain('Telp: 021-1234567');
            expect($result)->toContain('Fax: 021-1234568');
            expect($result)->toContain('Email: test@company.com');
        });

        it('kontak returns dash when empty', function () {
            $konsumen = Konsumen::factory()->create([
                'telp_kantor' => null,
                'fax' => null,
                'email' => null,
            ]);

            expect($konsumen->kontak)->toBe('-');
        });

        it('alamat_lengkap returns dash when all empty', function () {
            $konsumen = Konsumen::factory()->create([
                'alamat1' => null,
                'alamat2' => null,
                'kode_pos' => null,
            ]);

            expect($konsumen->alamat_lengkap)->toBe('-');
        });
    });

    describe('Default Status', function () {
        it('sets default status to A when creating', function () {
            $konsumen = Konsumen::factory()->create();

            expect($konsumen->status)->toBe('A');
        });
    });

    describe('Route Key', function () {
        it('uses id_konsumen as route key', function () {
            $konsumen = Konsumen::factory()->create();

            expect($konsumen->getRouteKeyName())->toBe('id_konsumen');
        });
    });
});
