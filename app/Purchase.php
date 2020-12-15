<?php

namespace App;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Purchase extends Model
{
    public $guarded = [];

    static function get_subscriptions($client_token, $datetime) {
        $key = "uid_" . $client_token;
        $u_id = Helpers::getCacheValue($key);
        if (!$u_id) {
            $u_id = Register::checkByClientToken($client_token)->u_id;
            if ($u_id) Helpers::setCacheValue($key, $u_id);
        }

        $records = Purchase::where("u_id", "=", $u_id)
            ->where("status", "=", "1")
            ->where("expire_date", ">", $datetime)->get();

        if ($records) {
            return $records;
        }
        return false;
    }
}
