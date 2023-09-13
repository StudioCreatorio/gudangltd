<?php

class WP_Shifty_Api {

      public static function request($endpoint, $data = array()){
            $response = wp_remote_post(WP_SHIFTY_API_URL . $endpoint, array(
                  'body' => $data,
                  'headers' => array (
                        'Wpshifty-auth' => base64_encode(json_encode(array(site_url(), get_option('wp-shifty-license'))))
                  ),
                  'sslverify' => false, 'timeout' => 90)
            );

            if (is_wp_error($response)){
                  throw new Exception($response->get_error_message(), 1);
            }

            $http_code = wp_remote_retrieve_response_code($response);
            if (substr($http_code, 0, 1) != 2){
                  throw new Exception('HTTP error: ' . $http_code, 1);
            }

            $decoded = json_decode($response['body'], true);

            if (empty($decoded) || !isset($decoded['result']) || $decoded['result'] == false){
                  throw new Exception((string)$decoded['message'], 1);
            }

            return $decoded['data'];
      }

      public static function license(){

            if (get_option('wp-shifty-license') == ''){
                  return 'inactive';
            }

            $response = wp_remote_post(WP_SHIFTY_API_URL . 'user/license', array(
                  'headers' => array (
                        'Wpshifty-auth' => base64_encode(json_encode(array(site_url(), get_option('wp-shifty-license'))))
                  ),
                  'sslverify' => false, 'timeout' => 30)
            );

            if (is_wp_error($response)){
                  return 'api-error';
            }

            $http_code = wp_remote_retrieve_response_code($response);
            if (substr($http_code, 0, 1) != 2){
                  return 'http-error';
            }

            $decoded = json_decode($response['body'], true);

            return $decoded['status'];
      }
}