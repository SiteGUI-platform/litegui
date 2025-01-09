<?php
namespace LiteGUI\Traits;

trait Http {
    protected function httpPost($url, $params = [], $headers = [], $options = [] ){
        //use output buffering to capture curl connection error to debug or suppress it
        ob_start();  
        $out = fopen('php://output', 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_VERBOSE, true);  
        curl_setopt($ch, CURLOPT_STDERR, $out);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP); //only http(s) connection
        //as we allow user to use this method, do not enable CURLOPT_FOLLOWLOCATION 
        foreach ( $options AS $key => $value ){
            curl_setopt($ch, $key, is_string($value)? str_replace(['\r', '\n'], '', $value) : $value); //remove carriage return from headers/options
        }
        if ( !empty($headers) ){
            foreach ( $headers AS $i => $value ){
                $headers[ $i ] = str_replace(['\r', '\n'], '', $value);
            }    
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $response = curl_exec($ch);
        fclose($out);  
        $debug = ob_get_clean();
        //echo $debug;
        //print_r($response);
        curl_close($ch);            
        return $response;
    }

    protected function httpGet($url, $params = [], $headers = [], $options = [] ){
        $options[CURLOPT_CUSTOMREQUEST] = 'GET';
        $url .= str_contains($url, '?')? '&' : '?';
        $url .= is_array($params)? http_build_query($params) : $params;
        return $this->httpPost($url, $params, $headers, $options);
    }

    protected function httpPut($url, $params = [], $headers = [], $options = [] ){
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        return $this->httpPost($url, $params, $headers, $options);
    }
    protected function httpPatch($url, $params = [], $headers = [], $options = [] ){
        $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        return $this->httpPost($url, $params, $headers, $options);
    }
    //generic Oauth request, $params can be a query_string or json string 
    protected function httpOauth($url, $params = '', $replaces = null, $headers = '', $type = 'POST'){
        if ($replaces){
            $url = str_replace(array_keys($replaces), $replaces, $url);
            if ($params){
                $params = str_replace(array_keys($replaces), $replaces, $params);
            }
            if ($headers){
                $headers = str_replace(array_keys($replaces), $replaces, $headers);
            }
        }
        //check remaining unfulfilled placeholders (e.g: Shopee {shop_id}), this can be a json string starting with { - make sure we dont match { from the beginning
        if ($params){
            preg_match_all('/.{(.*?)}/', $params, $matches);
            if ( !empty($matches[0][0]) ){ //$matches[0] contains {holder}, $matches[1] contains holder
                foreach( $matches[1] AS $i => $holder ){
                    if ( array_key_exists($holder, $_REQUEST) ){
                        $replaces[ '{'. $holder .'}' ] = $_REQUEST[ $holder ];
                    }   
                }
                $params = str_replace(array_keys($replaces), $replaces, $params);
            }
        }
        if ( $type == 'GET' ){
            $options[CURLOPT_CUSTOMREQUEST] = 'GET';
            $url .= str_contains($url, '?')? '&' : '?';
            $url .= $params;
        }

        //check URL after all modification is done
        $host = trim(parse_url($url, PHP_URL_HOST));
        //Wont work with private IP e.g: 172.16.0.0–172.31.255.255 or non-http scheme
        if (str_starts_with($host, '192.168.') OR
            str_starts_with($host, '10.') OR
            (str_starts_with($host, '172.') AND trim(substr($host, 4, 3), '.') > 15 AND trim(substr($host, 4, 3), '.') < 32 ) OR
            (!str_starts_with($url, 'https://') AND !str_starts_with($url, 'http://') )
        ){
            return 'Cannot work with the URL: '. $url;
        } else {                
            if ( !str_contains($headers, 'Content-Type') AND $type != 'GET'){
                $headers .= '&Content-Type: application/x-www-form-urlencoded';
            }
            if ( !empty($headers) ){//headers
                $headers = explode('&', trim($headers, '&'));
            }

            return $this->httpPost(
                $url, 
                $params, 
                $headers,
                $options??null,
            );
        }    
    }
}
?>