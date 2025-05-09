<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;

class AuditController extends Controller
{
    // get all audit records from database
    public function index($type = "",$event = "",Request $request){
        
        if(strtolower($type) == "all"){
            $type = "";
        }
        if(strtolower($event) == "all"){
            $event = "";
        }
        
        if(isset($request->fromto)){
            $dates = explode(" - ",$request->fromto);
            $from_time = strtotime($dates[0]);
            $to_time = strtotime($dates[1] . " + 1 days");

            $from_date = date('Y-m-d',$from_time);
            $to_date = date('Y-m-d',$to_time);
            
            $audits = Audit::where('auditable_type','like','%'.$type.'%')
                    ->where('event','like','%'.$event.'%')
                    ->whereBetween('created_at',[$from_date,$to_date])
                    ->orderBy('created_at','desc')
                    ->get();
        }
        else{
            $audits = Audit::where('auditable_type','like','%'.$type.'%')
                    ->where('event','like','%'.$event.'%')
                    ->orderBy('created_at','desc')
                    ->get();
        }
        
        foreach($audits as $item){
            $item->visibility = false;
            $item->event = ucwords($item->event);
            $item->old_values = json_decode($item->old_values);
            $item->new_values = json_decode($item->new_values);
        }
        return json_encode($audits);
    }
    
    public function getModuleHistory($module_id, $module_name){
        
        // echo $module_id;
        // echo $module_name;
        
        $audits = Audit::where('auditable_id', $module_id)
                        ->where('auditable_type','like', "%". $module_name . "%")
                        ->orderBy('created_at','desc')->get();
                    
        if(isset($audits)){
            foreach($audits as $item){
                $item->visibility = false;
                $item->event = ucwords($item->event);
                $item->timestamp = date_format($item->created_at,'Y/m/d H:i:s');
                $old_arr = explode(",",substr($item->old_values,1,-1));
                $item->old_short = json_decode("{".$old_arr[0]."}");
                $new_arr = explode(",",substr($item->new_values,1,-1));
                $item->new_short = json_decode("{".$new_arr[0]."}");
                $item->old_values = json_decode($item->old_values);
                $item->new_values = json_decode($item->new_values);
            }
        }       
         return view('audit.show_history',compact('audits'));
        // return json_encode($audits);
    }
}