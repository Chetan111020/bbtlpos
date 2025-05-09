<?php

namespace Modules\SmartCRM\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    const STATUS = [
        'open', 'in_process', 'closed'
    ];

    const PRIORITIES = [
        'urgent', 'high', 'medium', 'low'
    ];

    const CHANNEL = [
        'call', 'chat', 'sms', 'mail', 'physical visit','other'
    ];

    protected $fillable = [
        'contact_id',
        'title',
        'status',
        'priority',
        'channel',
        'tags',
        'note',
        'scheduled_at',
        'assigned_to',
        'created_by',
    ];

    public static function format($prop){
        return ucwords(str_replace("_", " ", $prop));
    }

    public static function keyValueMap($array){
        $res = [];
        foreach($array as $item){
            $res[$item] = self::format($item);
        }
        return $res;
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function agent()
    {
        return $this->belongsTo(\App\User::class, 'assigned_to');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public static function viewOwnCustomersOnly(){
        return !auth()->user()->can('smartcrm.customer_view_all') && auth()->user()->can('smartcrm.customer_view_own');
    }
}