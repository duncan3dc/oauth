<?php

namespace duncan3dc\OAuth;

use duncan3dc\Serial\Json;

/**
 * Interacting with the GitHub Api using OAuth2.
 */
class GitHub extends OAuth2
{

    /**
     * Create a new instance of the class.
     *
     * @param array $options The oauth settings to use
     */
    public function __construct(array $options)
    {
        $options = Helper::getOptions($options, [
            "username"  =>  false,
            "client"    =>  "",
            "secret"    =>  "",
            "code"      =>  "",
        ]);

        parent::__construct([
            "type"          =>  "github",
            "username"      =>  $options["username"],
            "client"        =>  $options["client"],
            "secret"        =>  $options["secret"],
            "code"          =>  $options["code"],
            "authoriseUrl"  =>  "https://github.com/login/oauth/authorize?scope=repo,admin:repo_hook,admin:org",
            "redirectUrl"   =>  "http://developer.github.com/v3/",
            "accessUrl"     =>  "https://github.com/login/oauth/access_token",
        ]);
    }


    /**
     * Ensure a url has the api.github.com domain at the start.
     *
     * @param string $url The url to check
     *
     * @return string The modified url
     */
    protected function getUrl($url)
    {
        if (substr($url, 0, 4) === "http") {
            return $url;
        }

        if (substr($url, 0, 1) !== "/") {
            $url = "/" . $url;
        }

        return "https://api.github.com" . $url;
    }


    /**
     * Send a GET request and return the response.
     *
     * @param string $url The url to issue the request to (https://api.github.com is optional)
     * @param array $data The parameters to send with the request
     * @param array $headers Any extra headers to send with the request
     *
     * @return array
     */
    public function fetch($url, array $data = null, array $headers = null)
    {
        $url = $this->getUrl($url);

        if (!is_array($headers)) {
            $headers = [];
        }
        $headers["Accept"] = "application/vnd.github.v3+json";
        $headers["User-Agent"] = $this->username;

        return parent::fetch($url, $data, $headers);
    }


    /**
     * Send a POST request and return the response.
     *
     * @param string $url The url to issue the request to (https://api.github.com is optional)
     * @param array $data The parameters to send with the request
     *
     * @return array
     */
    public function post($url, array $data)
    {
        $url = $this->getUrl($url);

        $headers = [
            "Accept"        =>  "application/vnd.github.v3+json",
            "User-Agent"    =>  $this->username,
        ];

        $url = Helper::url($url, [
            "access_token"  =>  $this->get("token"),
        ]);

        $json = Helper::curl([
            "url"       =>  $url,
            "headers"   =>  $headers,
        ], Json::encode($data));

        return Json::decode($json);
    }


    /**
     * Send a DELETE request and return the response.
     *
     * @param string $url The url to issue the request to (https://api.github.com is optional)
     *
     * @return array (usually empty)
     */
    public function delete($url)
    {
        $url = $this->getUrl($url);

        $headers = [
            "Accept"        =>  "application/vnd.github.v3+json",
            "User-Agent"    =>  $this->username,
        ];

        $url = Helper::url($url, [
            "access_token"  =>  $this->get("token"),
        ]);

        $json = Helper::curl([
            "url"       =>  $url,
            "headers"   =>  $headers,
            "custom"    =>  "DELETE",
        ]);

        return Json::decode($json);
    }
}
