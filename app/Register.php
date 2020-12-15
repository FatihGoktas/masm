<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    public $guarded = [];

    static function check($u_id)
    {
        $record = Register::where("u_id", "=", $u_id)->first();
        if ($record) {
            return $record["client_token"];
        }
        return false;
    }

    static function checkByClientToken($client_token)
    {
        $record = Register::where("client_token", "=", $client_token)->first();
        if ($record) {
            return $record;
        }
        return false;
    }

    static function checkByUid($u_id)
    {
        $record = Register::where("u_id", "=", $u_id)->first();
        if ($record) {
            return $record;
        }
        return false;
    }
}
