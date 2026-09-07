<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ForecastExport implements FromView, ShouldAutoSize
{
    protected $stockForecast;
    protected $tigaBulanTerakhir;
    protected $bulanAktif;
    protected $tahun;
    protected $teksStokAkhir;
    protected $activePercentage;
    protected $monthTranslations;

    public function __construct(
        $stockForecast,
        $tigaBulanTerakhir,
        $bulanAktif,
        $tahun,
        $teksStokAkhir,
        $activePercentage,
        $monthTranslations
    ) {
        $this->stockForecast = $stockForecast;
        $this->tigaBulanTerakhir = $tigaBulanTerakhir;
        $this->bulanAktif = $bulanAktif;
        $this->tahun = $tahun;
        $this->teksStokAkhir = $teksStokAkhir;
        $this->activePercentage = $activePercentage;
        $this->monthTranslations = $monthTranslations;
    }

    public function view(): View
    {
        return view('exports.excel.forecast', [
            'stockForecast' => $this->stockForecast,
            'tigaBulanTerakhir' => $this->tigaBulanTerakhir,
            'bulanAktif' => $this->bulanAktif,
            'tahun' => $this->tahun,
            'teksStokAkhir' => $this->teksStokAkhir,
            'activePercentage' => $this->activePercentage,
            'monthTranslations' => $this->monthTranslations,
        ]);
    }
}
