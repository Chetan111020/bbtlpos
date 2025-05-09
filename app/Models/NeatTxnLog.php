<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\NeatTxnProLog;
use App\User;

class NeatTxnLog extends Model
{
    use SoftDeletes;

    protected $table = 'neat_txn_logs';

    protected $fillable = [
        'id',
        'txn_id',
        'activity_by',
        'activity_title',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    
    public static function logActivity($txn_id = null, $activity_title = null)
    {
        $log = new static();
        
        $log->txn_id = $txn_id;
        $log->activity_title = $activity_title ?? 'unknown';
        $log->activity_by = Auth::id();
        $log->created_at = now();

        $log->save();

        return $log;
    }
    
    public function productLogs()
    {
        return $this->hasMany(NeatTxnProLog::class, 'neat_txn_id', 'id');
    }

     public function user()
        {
            return $this->belongsTo(User::class, 'activity_by');
        }
}
