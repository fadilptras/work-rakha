<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiIndicator;

class KpiOperasionalSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            // KINERJA
            [
                'category' => 'kinerja',
                'name' => 'Kualitas',
                'definition' => 'Kualitas pekerjaan',
                'target' => '',
                'weight_percentage' => 50,
                'type' => 'operasional'
            ],
            [
                'category' => 'kinerja',
                'name' => 'Kuantitas/Target Collection',
                'definition' => 'Pencapaian target',
                'target' => '',
                'weight_percentage' => 30,
                'type' => 'operasional'
            ],
            [
                'category' => 'kinerja',
                'name' => 'Tepat Waktu',
                'definition' => 'Penyelesaian tepat waktu',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],

            // PERILAKU - Berusaha Meraih Yang Terbaik
            [
                'category' => 'perilaku_terbaik',
                'name' => 'Fokus',
                'definition' => 'Bersungguh-sungguh dalam upaya memberikan hasil kerja dengan kualitas terbaik',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_terbaik',
                'name' => 'Proaktif',
                'definition' => 'Cepat tanggap dalam mengantisipasi perubahan, mengambil inisiatif',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_terbaik',
                'name' => 'Inovatif',
                'definition' => 'Mencari cara baru dalam upaya memberikan nilai tambah',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_terbaik',
                'name' => 'Kreatif',
                'definition' => 'Berpikir kreatif atas permasalahan',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_terbaik',
                'name' => 'Agresif',
                'definition' => 'Mampu melihat peluang, gesit bertindak',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],

            // PERILAKU - Berperilaku Profesional
            [
                'category' => 'perilaku_profesional',
                'name' => 'Kompeten',
                'definition' => 'Menguasai bidang kerja',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_profesional',
                'name' => 'Mandiri',
                'definition' => 'Melaksanakan pekerjaan tanpa perlu diawasi',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_profesional',
                'name' => 'Menjaga Kepatuhan',
                'definition' => 'Disiplin dan konsisten melaksanakan SOP',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_profesional',
                'name' => 'Menjaga Kepercayaan',
                'definition' => 'Bekerja jujur dan menjaga citra perusahaan',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_profesional',
                'name' => 'Menjaga Komitmen',
                'definition' => 'Memenuhi janji, merealisasikan program',
                'target' => '',
                'weight_percentage' => 20,
                'type' => 'operasional'
            ],

            // PERILAKU - Bersikap Peduli
            [
                'category' => 'perilaku_peduli',
                'name' => 'Bersikap kooperatif',
                'definition' => 'Membantu pihak lain',
                'target' => '',
                'weight_percentage' => 25,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_peduli',
                'name' => 'Bersikap hati-hati',
                'definition' => 'Menguasai diri dan meredam emosi',
                'target' => '',
                'weight_percentage' => 25,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_peduli',
                'name' => 'Berperilaku selaras Etika',
                'definition' => 'Menjaga saling menghormati',
                'target' => '',
                'weight_percentage' => 25,
                'type' => 'operasional'
            ],
            [
                'category' => 'perilaku_peduli',
                'name' => 'Menunjukkan sikap empati',
                'definition' => 'Memperhatikan perasaan orang lain',
                'target' => '',
                'weight_percentage' => 25,
                'type' => 'operasional'
            ],
            
            // KEHADIRAN (Fixed weight 20% in summary, but internally the target is 100% days)
            [
                'category' => 'kehadiran',
                'name' => 'Kehadiran Karyawan',
                'definition' => 'Tingkat kehadiran per periode',
                'target' => '',
                'weight_percentage' => 100, // Total of this section is 100%
                'type' => 'operasional'
            ]
        ];

        foreach ($indicators as $indicator) {
            KpiIndicator::create($indicator);
        }
    }
}
