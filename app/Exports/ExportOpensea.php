<?php

namespace App\Exports;

use App\Models\Opensea;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportOpensea implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Opensea::all();
    }
}
