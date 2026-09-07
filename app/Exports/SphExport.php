<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SphExport implements FromView, ShouldAutoSize
{
    protected $sph;

    public function __construct($sph)
    {
        $this->sph = $sph;
    }

    public function view(): View
    {
        return view('exports.excel.sph', [
            'sph' => $this->sph,
        ]);
    }
}
