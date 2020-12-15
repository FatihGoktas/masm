<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class Helpers
{
    public static function generateRandomString($length = 10, $type = "s") {
        switch ($type) {
            case "s": // string
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case "n": //integer
                $characters = '0123456789';
                break;
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getHash($params)
    {
        return password_hash(($params["u_id"] . $params["app_id"] . Helpers::generateRandomString(10, "n")), PASSWORD_BCRYPT);
    }

    public static function getCacheValue($key)
    {
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        return false;
    }

    public static function setCacheValue($key, $value, $ttl = 86400)
    {
        return Cache::put($key, $value, $ttl);
    }

    public static function removeCache($key)
    {
        Cache::forget($key);
    }
}

