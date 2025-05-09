<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class QrExport implements FromArray
{
    public $arr;
    public function __construct($arr){
        $this->arr = $arr;
    }

    public function array():array {
        return $this->arr;
    }
}