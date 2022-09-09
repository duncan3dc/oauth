<?php

namespace duncan3dc\OAuth;

use duncan3dc\Serial\Json;

class Instagram extends OAuth2
{

    public function __construct(array $options)
    {
        $options = Helper::getOptions($options, [
            "client"    =>  "",
            "secret"    =>  "",
            "username"  =>  "",
        ]);

        parent::__construct([
            "type"          =>  "instagram",
            "client"        =>  $options["client"],
            "secret"        =>  $options["secret"],
            "username"      =>  $options["username"],
            "authoriseUrl"  =>  "https://api.instagram.com/oauth/authorize",
            "redirectUrl"   =>  "https://api.instagram.com/oauth/redirect",
            "accessUrl"     =>  "https://api.instagram.com/oauth/access_token",
        ]);
    }


    public function timeline(array $options = null)
    {
        $options = Helper::getOptions($options, [
            "min"   =>  false,
            "limit" =>  200,
        ]);

        $url = "https://api.instagram.com/v1/users/self/media/recent";

        $params = [];
        if ($val = $options["min"]) {
            $params["min_id"] = $val;
        }
        if ($val = $options["limit"]) {
            $params["count"] = $val;
        }

        return $this->fetch($url, $params);
    }
}
