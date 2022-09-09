<?php

namespace duncan3dc\OAuth;

class Twitter extends OAuth
{

    public function __construct(array $options = null)
    {
        $options = Helper::getOptions($options, [
            "type"      =>  "twitter",
            "username"  =>  false,
            "authkey"   =>  "",
            "secret"    =>  "",
        ]);

        parent::__construct([
            "requestUrl"    =>  "https://api.twitter.com/oauth/request_token",
            "accessUrl"     =>  "https://api.twitter.com/oauth/access_token",
            "authoriseUrl"  =>  "https://api.twitter.com/oauth/authorize",
            "type"          =>  $options["type"],
            "username"      =>  $options["username"],
            "authkey"       =>  $options["authkey"],
            "secret"        =>  $options["secret"],
        ]);
    }


    public function timeline(array $options = null)
    {
        $options = Helper::getOptions($options, [
            "mode"  =>  "extended",
            "user"  =>  false,
            "since" =>  false,
            "max"   =>  false,
            "limit" =>  200,
        ]);

        $url = "https://api.twitter.com/1.1/statuses/";
        $params = [];

        if ($val = $options["user"]) {
            $url .= "user";
            $params["user_id"] = $val;
        } else {
            $url .= "home";
        }
        $url .= "_timeline.json";

        if ($val = $options["since"]) {
            $params["since_id"] = $val;
        }
        if ($val = $options["max"]) {
            $params["max_id"] = $val;
        }
        if ($val = $options["limit"]) {
            $params["count"] = $val;
        }
        if ($val = $options["mode"]) {
            $params["tweet_mode"] = $val;
        }

        $url = Helper::url($url, $params);

        return $this->fetch($url);
    }


    public function user($user)
    {
        $url = "https://api.twitter.com/1.1/users/show.json";

        if (preg_match("/[a-z]/i", $user)) {
            $type = "screen_name";
        } else {
            $type = "user_id";
        }

        $url = Helper::url($url, [
            $type   =>  $user,
        ]);

        return $this->fetch($url);
    }


    public function tweet($status)
    {
        return $this->fetch("https://api.twitter.com/1.1/statuses/update.json", [
            "status"    =>  $status,
        ]);
    }
}
