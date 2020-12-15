<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Purchase;
use App\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();
        $key = "pl_" . $params["client_token"];

        $response = Http::post('http://masm.local/api/google', ['receipt' => $params["receipt"]]);
        $result = json_decode($response->body(), true);
        if ($result["status"] == "false") {
            return response()->json(array("result" => "false", "message" => "purchase failed"), 200);
        }
        /**
         * Expire Date düzenlemesi
         */
        $result["expire_date"] = Carbon::createFromTimestamp(strtotime($result["expire_date"]), new \DateTimeZone("GMT+6"));
        $result["expire_date"] = $result["expire_date"]->toDateTimeString();

        $result["status"] = ($result["status"] == "true") ? 1 : 0;
        $result["receipt"] = $params["receipt"];

        $u_id = Helpers::getCacheValue("uid_" . $params["client_token"]);
        if (!$u_id) {
            $u_id = Register::checkByClientToken($params["client_token"])->u_id;
        }
        $result["u_id"] = $u_id;
        if ($result["status"] &&$this->store($result)->id) {
            Helpers::removeCache($key);
            return response()->json(array("result" => "true", "message" => "purchase successful"), 200);
        }
        return response()->json(array("result" => "false", "message" => "purchase failed"), 200);
    }

    public function store($data)
    {
        return Purchase::create($data);
    }

    public static function get_subscriptions(Request $request)
    {
        $datetime = new Carbon('now', new \DateTimeZone("UTC"));
        $key = "pl_" . $request->get("client_token");
        $list = Helpers::getCacheValue($key);
        if (!$list || $list->count() <= 0) {
            $list = Purchase::get_subscriptions($request->get("client_token"), $datetime->toDateTimeString());
        }
        foreach ($list as $index => $item) {
            if ($item->expire_date < $datetime->toDateTimeString()) {
                unset($list[$index]);
            }
        }
        Helpers::setCacheValue($key, $list, 120);
        return response()->json(array("result" => "true", "list" => $list));
    }

}
