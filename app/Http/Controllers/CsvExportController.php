<?php

namespace App\Http\Controllers;

use App\Exports\ExportEtherscan;
use Illuminate\Http\Request;

class CsvExportController extends Controller
{
    public function etherscan(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'string'],
        ]);

        return (new ExportEtherscan(explode(',', $request->query('ids'))))
            ->download('ERC20 Tokens.csv', \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
            ]);
    }

    public function opensea()
    {
        return 'hello opensea';
    }
}
