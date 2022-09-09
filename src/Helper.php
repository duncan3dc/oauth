<?php

namespace duncan3dc\OAuth;

class Helper
{

    /**
     * Simulate named arguments using associative arrays.
     * Basically just merge the two arrays, giving user specified options the preference.
     * Also ensures that each paramater in the user array is valid and throws an exception if an unknown element is found.
     *
     * @param array $userSpecified The array of options passed to the function call
     * @param array $defaults The default options to be used
     *
     * @return array
     */
    public static function getOptions($userSpecified, $defaults)
    {
        $options = static::getAnyOptions($userSpecified, $defaults);

        foreach ($options as $key => $null) {
            if (array_key_exists($key, $defaults)) {
                continue;
            }
            throw new \InvalidArgumentException("Unknown parameter (" . $key . ")");
        }

        return $options;
    }


    /**
     * This is a safe version of the getOptions() method.
     * It allows any custom option key in the userSpecified array.
     *
     * @param array $userSpecified The array of options passed to the function call
     * @param array $defaults The default options to be used
     *
     * @return array
     */
    public static function getAnyOptions($userSpecified, $defaults)
    {
        $options = $defaults;
        $userSpecified = static::toArray($userSpecified);

        foreach ($userSpecified as $key => $val) {
            $options[$key] = $val;
        }

        return $options;
    }


    /**
     * Ensure that the passed parameter is a string, or an array of strings.
     *
     * @param mixed $data The value to convert to a string
     *
     * @return string|string[]
     */
    public static function toString($data)
    {
        if (is_array($data)) {
            $newData = [];
            foreach ($data as $key => $val) {
                $key = (string)$key;
                $newData[$key] = static::toString($val);
            }
        } else {
            $newData = (string)$data;
        }

        return $newData;
    }


    /**
     * Ensure that the passed parameter is an array.
     * If it is a truthy value then make it the sole element of an array.
     *
     * @param mixed $value The value to convert to an array
     *
     * @return array
     */
    public static function toArray($value)
    {
        # If it's already an array then just pass it back
        if (is_array($value)) {
            return $value;
        }

        # If it's not an array then create a new array to be returned
        $array = [];

        # If a value was passed as a string/int then include it in the new array
        if ($value) {
            $array[] = $value;
        }

        return $array;
    }


    /**
     * Run each element value through trim() and remove any elements that are falsy.
     *
     * @param array $array The array to cleanup
     *
     * @return array
     */
    public static function cleanupArray($array)
    {
        $newArray = [];

        $array = static::toArray($array);

        foreach ($array as $key => $val) {

            if (is_array($val)) {
                $val = static::cleanupArray($val);
            } else {
                $val = trim($val);
                if (!$val) {
                    continue;
                }
            }

            $newArray[$key] = $val;
        }

        return $newArray;
    }


    /**
     * Append parameters on a url (adding a question mark if none is present).
     *
     * @param string $url The base url
     * @param array $params An array of parameters to append
     *
     * @return string
     */
    public static function url($url, $params = null)
    {
        if (!is_array($params) || count($params) < 1) {
            return $url;
        }

        $pos = strpos($url, "?");

        # If there is no question mark in the url then set this as the first parameter
        if ($pos === false) {
            $url .= "?";

        # If the question mark is the last character then no appending is required
        } elseif ($pos != (strlen($url) - 1)) {

            # If the last character is not an ampersand then append one
            if (substr($url, -1) != "&") {
                $url .= "&";
            }
        }

        $url .= http_build_query($params);

        return $url;
    }


    /**
     * Simple wrapper for curl.
     *
     * $options:
     * - string "url" The url to request
     * - array "headers" An array of key/value pairs of headers to send
     * - int "connect" CURLOPT_CONNECTTIMEOUT (default: 0)
     * - int "timeout" CURLOPT_TIMEOUT (default: 0)
     * - bool "follow" CURLOPT_FOLLOWLOCATION (default: true)
     * - bool "verifyssl" CURLOPT_SSL_VERIFYPEER (default: true)
     * - string "cookies" The file to use for both CURLOPT_COOKIEFILE and CURLOPT_COOKIEJAR
     * - bool "put" Set to try to send the $body parameter as the contents of a put request
     * - string "custom" CURLOPT_CUSTOMREQUEST
     * - bool "nobody" CURLOPT_NOBODY
     * - string "useragent" CURLOPT_USERAGENT
     * - bool "returnheaders" CURLOPT_HEADER (default: false)
     * - array "curlopts" Any extra curlopt constants (as the keys) and the values to use (as the values)
     *
     * @param string|array $options Can either be a url to use with the default options, or an array of options (see above)
     * @param string|array $body Content to send in the body of the request, if an array is passed it will be run through http_build_query()
     *
     * @return string|array If "returnheaders" is false then the body of the response is returned as a string, otherwise an array of data about the response is available
     */
    public static function curl($options, $body = null)
    {
        # If the options weren't passed as an array then it is just a simple url request
        if (!is_array($options)) {
            $options = ["url" => $options];
        }

        $options = Helper::getOptions($options, [
            "url"           =>  null,
            "headers"       =>  null,
            "connect"       =>  0,
            "timeout"       =>  0,
            "follow"        =>  true,
            "verifyssl"     =>  true,
            "cookies"       =>  null,
            "put"           =>  false,
            "custom"        =>  false,
            "nobody"        =>  false,
            "useragent"     =>  "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0",
            "returnheaders" =>  false,
            "curlopts"      =>  [],
        ]);

        if (!$url = trim($options["url"])) {
            throw new \Exception("No url specified");
        }

        # If an array of post data has been passed then convert it into a query string
        if (is_array($body)) {
            $body = http_build_query($body);
        }

        $curlopts = [
            CURLOPT_URL             =>  $url,
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_NOBODY          =>  $options["nobody"],
        ];

        if ($options["put"]) {
            $file = fopen("php://memory", "w");
            fwrite($file, $body);
            rewind($file);

            $curlopts[CURLOPT_PUT] = true;
            $curlopts[CURLOPT_INFILE] = $file;
            $curlopts[CURLOPT_INFILESIZE] = strlen($body);
        } elseif ($body) {
            $curlopts[CURLOPT_POST] = true;
            $curlopts[CURLOPT_POSTFIELDS] = $body;
        }

        if ($custom = $options["custom"]) {
            $curlopts[CURLOPT_CUSTOMREQUEST] = $custom;
        }

        if ($headers = $options["headers"]) {
            $header = "";
            foreach ($headers as $key => $val) {
                $header[] = $key . ": " . $val;
            }
            $curlopts[CURLOPT_HTTPHEADER] = $header;
        }

        $curlopts[CURLOPT_CONNECTTIMEOUT] = round($options["connect"]);
        $curlopts[CURLOPT_TIMEOUT] = round($options["timeout"]);

        if ($options["follow"]) {
            $curlopts[CURLOPT_FOLLOWLOCATION] = true;
        }

        if (!$options["verifyssl"]) {
            $curlopts[CURLOPT_SSL_VERIFYPEER] = false;
        }

        if ($cookies = $options["cookies"]) {
            $curlopts[CURLOPT_COOKIEFILE]   =   $cookies;
            $curlopts[CURLOPT_COOKIEJAR]    =   $cookies;
        }

        if ($useragent = $options["useragent"]) {
            $curlopts[CURLOPT_USERAGENT] = $useragent;
        }

        if ($options["returnheaders"]) {
            $curlopts[CURLOPT_HEADER] = true;
        }

        if (count($options["curlopts"]) > 0) {
            foreach ($options["curlopts"] as $key => $val) {
                $curlopts[$key] = $val;
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curlopts);

        $result = curl_exec($curl);

        $error = curl_error($curl);

        if ($options["returnheaders"]) {
            $info = curl_getinfo($curl);
        }

        curl_close($curl);

        if ($result === false) {
            throw new \Exception($error);
        }

        if ($options["returnheaders"]) {
            $header = substr($result, 0, $info["header_size"]);
            $lines = explode("\n", $header);
            $status = array_shift($lines);
            $headers = [];
            foreach ($lines as $line) {
                if (!trim($line)) {
                    continue;
                }
                $bits = explode(":", $line);
                $key = array_shift($bits);
                $headers[$key] = trim(implode(":", $bits));
            }
            $body = substr($result, $info["header_size"]);
            return [
                "status"    =>  $status,
                "headers"   =>  $headers,
                "body"      =>  $body,
                "response"  =>  $result,
            ];
        }

        return $result;
    }
}
