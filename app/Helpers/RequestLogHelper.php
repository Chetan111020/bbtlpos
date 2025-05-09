<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
class RequestLogHelper
{
    const LOG_TYPE_TRANSACTION_CREATE = 'create';
    const LOG_TYPE_TRANSACTION_UPDATE = 'update';

    public static function LogTransaction($request, $transaction, $type){
        if($type != self::LOG_TYPE_TRANSACTION_CREATE && $type != self::LOG_TYPE_TRANSACTION_UPDATE){

        }
        else{
            File::put(storage_path('json_request_logs')."/".$transaction->id.'_'.$type.'.json', json_encode($request->all()));
        }
    }
}