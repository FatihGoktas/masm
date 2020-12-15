<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $guarded = [];

    static function app_list($u_id)
    {
        $record = Device::where("u_id", "=", $u_id)->get("app_id");
        if ($record) {
            return array_column($record->toArray(), "app_id");
        }
        return false;
    }

}
