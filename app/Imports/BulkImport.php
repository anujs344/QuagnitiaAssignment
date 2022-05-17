<?php

namespace App\Imports;

use App\Models\Bulk;
use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\withHeadingRow;

class BulkImport implements ToModel,withHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Bulk([
            'PIN'     => $row['pin'],
            'IDE'    => $row['id'],
            'Qr_data' => $row['qr_data']
        ]);
    }
}
