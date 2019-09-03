<?php

namespace App\Imports;

use App\AdminModels\Player;
use Maatwebsite\Excel\Concerns\ToModel;
class playersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Player([
        'name'              => $row[1],
        'colours'           => $row[2],
        'injured_available' => $row[3],
        'injured_out'       => $row[4],
        'cost'              => $row[5],
        'position'          => $row[6],
        'club'              => $row[7],
        'points'            => $row[8]
        ]);
    }
}
