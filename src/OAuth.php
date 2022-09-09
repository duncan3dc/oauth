<?php

namespace duncan3dc\OAuth;

use duncan3dc\Serial\Json;

class OAuth
{
    const STATE_INIT        =   0;
    const STATE_ACCESS      =   1;
    const STATE_COMPLETE    =   2;

    public  $oauth;
    public  $type;
    public  $username;

    public  $requestUrl;
    public  $accessUrl;
    public  $authoriseUrl;

    private $data;


    public function __construct(array $options)
    {
        $options = Helper::getOptions($options, [
            "requestUrl"    =>  "",
            "accessUrl"     =>  "",
            "authoriseUrl"  =>  "",
            "type"          =>  "",
            "username"      =>  "",
            "authkey"       =>  "",
            "secret"        =>  "",
            "authtype"      =>  OAUTH_AUTH_TYPE_AUTHORIZATION,
        ]);

        $this->oauth = new \OAuth($options["authkey"], $options["secret"], OAUTH_SIG_METHOD_HMACSHA1, $options["authtype"]);
        $this->type = $options["type"];
        $this->username = $options["username"];

        $this->requestUrl = $options["requestUrl"];
        $this->accessUrl = $options["accessUrl"];
        $this->authoriseUrl = $options["authoriseUrl"];
    }


    public function authorise()
    {
        # If no action has been taken yet then request authorisation to the users account
        if ($this->get("state") == static::STATE_INIT) {
            $result = $this->oauth->getRequestToken($this->requestUrl);
            $this->set([
                "state"     =>  static::STATE_ACCESS,
                "token"     =>  $result["oauth_token"],
                "secret"    =>  $result["oauth_token_secret"],
            ]);
            $url = $this->authoriseUrl;
            if (!$url) {
                $url = $result["login_url"];
            }
            $url .= "?oauth_token=" . $result["oauth_token"];
            return $url;
        }

        # If access has been requested, then check that it was granted
        if ($this->get("state") == static::STATE_ACCESS) {
            $this->setToken();
            $result = $this->oauth->getAccessToken($this->accessUrl);
            $this->set([
                "state"     =>  static::STATE_COMPLETE,
                "token"     =>  $result["oauth_token"],
                "secret"    =>  $result["oauth_token_secret"],
            ]);
        }

        return false;
    }


    public function get($key)
    {
        $sql = Sql::getInstance();

        if ($this->data === null) {
            $this->data = $sql->select("oauth", [
                "type"      =>  $this->type,
                "username"  =>  $this->username,
            ]);
        }

        return $this->data[$key];
    }


    public function set($params, $value = null)
    {
        $sql = Sql::getInstance();

        if (!is_array($params)) {
            $params = [$params => $value];
        }

        $sql->insertOrUpdate("oauth", $params, [
            "type"      =>  $this->type,
            "username"  =>  $this->username,
        ]);

        $this->data = null;
    }


    public function setToken()
    {
        $token = $this->get("token");
        $secret = $this->get("secret");

        return $this->oauth->setToken($token, $secret);
    }


    public function fetch($url, array $data = null)
    {
        $this->setToken();

        if (is_array($data)) {
            $method = OAUTH_HTTP_METHOD_POST;
        } else {
            $method = OAUTH_HTTP_METHOD_GET;
        }

        $this->oauth->fetch($url, $data, $method, ["Accept" => "application/json"]);

        $json = $this->oauth->getLastResponse();

        return Json::decode($json);
    }
}
