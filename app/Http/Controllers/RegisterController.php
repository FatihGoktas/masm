<?php

namespace App\Http\Controllers;

use App\Device;
use App\Register;
use Symfony\Component\Console\Helper\Helper;
use Illuminate\Http\Request;
use App\Helpers\Helpers;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();

        $key = "uid_" . $params["u_id"];
        $client_token = Helpers::getCacheValue($key);
        if (!$client_token) {
            $client_token = Register::check($params["u_id"]);
            if (!$client_token) {
                $result = $this->store_register($params);
                Helpers::setCacheValue($key, $result->client_token, 2592000);
                Helpers::setCacheValue("uid_" . $result->client_token, $params["u_id"], 2592000);
                return response()->json(
                    array(
                        "result" => "true",
                        "message" => "account created",
                        "client_token" => $result->client_token
                    ), 201
                );
            }
        }
        $this->store_device($params);
        return response()->json(
            array(
                "result" => "true",
                "message" => "account already exists",
                "client_token" => $client_token
            ), 200
        );
    }

    public function store_register($params)
    {
        $register["u_id"] = $params["u_id"];
        $register["client_token"] = Helpers::getHash($params);
        return Register::create($register);
    }

    public function store_device($params)
    {
        $key = "apps_". $params["u_id"];
        $app_list = Helpers::getCacheValue($key);
        if (!$app_list) {
            Helpers::setCacheValue($key, [$params["app_id"]]);
            Device::create($params);
        } else {
            if (!in_array($params["app_id"], $app_list)) {
                Device::create($params);
                $app_list[] = $params["app_id"];
                Helpers::setCacheValue($key, $app_list);
            }
        }
    }
}
