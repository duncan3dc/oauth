<?php

namespace duncan3dc\OAuth;

class Lovefilm extends OAuth
{
    protected $user;


    public function __construct(array $options = null)
    {
        $options = Helper::getOptions($options, [
            "authkey"   =>  "",
            "secret"    =>  "",
        ]);

        parent::__construct([
            "type"          =>  "lovefilm",
            "requestUrl"    =>  "http://openapi.lovefilm.com/oauth/request_token",
            "accessUrl"     =>  "http://openapi.lovefilm.com/oauth/access_token",
            "authkey"       =>  $options["authkey"],
            "secret"        =>  $options["secret"],
        ]);
    }


    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        $data = $this->fetch("http://openapi.lovefilm.com/users");
        foreach ($data["resource"]["links"] as $link) {
            if ($link["title"] == "current user") {
                $this->user = $link["href"];
            }
        }

        return $this->user;
    }


    public function getData($type = null)
    {
        $url = $this->getUser();
        if ($type) {
            $url .= "/" . $type;
        }

        return $this->fetch($url);
    }
}
