<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KpiIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $indicators = [
            // Kinerja Marketing / Operasional (Sesuai PDF)
            [
                'category' => 'kinerja',
                'name' => 'Net Sales',
                'definition' => 'Mengukur pencapaian Target Net Sales.',
                'target' => '600 JT',
                'weight_percentage' => 50.00,
                'type' => 'marketing'
            ],
            [
                'category' => 'kinerja',
                'name' => '% Grw - Sales Total',
                'definition' => 'Mengukur pertumbuhan sales dibandingkan tahun sebelumnya.',
                'target' => '10%',
                'weight_percentage' => 15.00,
                'type' => 'marketing'
            ],
            [
                'category' => 'kinerja',
                'name' => 'Cost Rasio',
                'definition' => 'Membandingkan dana promosi dengan sales yang masuk.',
                'target' => '< 5%',
                'weight_percentage' => 15.00,
                'type' => 'marketing'
            ],
            [
                'category' => 'kinerja',
                'name' => 'Basic Operation Implementation',
                'definition' => 'Mengukur penerapan BASO.',
                'target' => '3 Outlet/hari (kunjungan sesuai outlet binaan)',
                'weight_percentage' => 20.00,
                'type' => 'marketing'
            ]
        ];

        foreach ($indicators as $indicator) {
            \App\Models\KpiIndicator::create($indicator);
        }
    }
}
