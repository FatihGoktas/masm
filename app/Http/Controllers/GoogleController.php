<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function index(Request $request)
    {
        $datetime = new Carbon('now', new \DateTimeZone("UTC"));

        $params = $request->all();
        $char = ((int)substr($params["receipt"], -1));

        if ($char != 0 && $char % 2 > 0) {
            $result = array(
                "status" => "true",
                "expire_date" => $datetime->toDateTimeString());
        } else {
            $result = array("status" => "false");
        }
        return response()->json($result, 200);
    }
}
