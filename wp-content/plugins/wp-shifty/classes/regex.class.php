<?php

class WP_Shifty_Regex {

      public static function reverse ($regex){
            $regex = preg_replace_callback('~(\\\d|\\\w|\\\W|\\\s|\\\S|\.)~', function($matches){

                  switch ($matches[1]){
                        case '\d':
                              return '[0123456789]';
                              break;
                        case '\w':
                        case '\S':
                              return '[qwertzuiopasdfghjklyxcvbnm]';
                              break;
                        case '\W':
                              return '[-/_@]';
                              break;
                        case '\s':
                              return '[\s]';
                              break;
                        case '.':
                              return '[0123456789qwertzuiopasdfghjklyxcvbnm]';
                              break;
                  }
            }, $regex);

            $regex = preg_replace_callback('~\(?\[([^\]]+)\](\*|\+|\{\d,?\d?\})?\)?~', function($matches){

                  $multiplicator = 1;
                  if (isset($matches[2])){
                        if ($matches[2] == '*' || $matches[2] == '+'){
                              $multiplicator = mt_rand(1,5);
                        }

                        else if (strpos($matches[2], '{') == 0){
                              list($min,$max) = explode(',', preg_replace('~(\{|\}|\s)~', '', $matches[2]));
                              $min = min((int)$min, 1);
                              $max = max((int)$min, (int)$max);
                              $multiplicator = mt_rand((int)$min, $max);
                        }
                  }

                  return self::random_string($multiplicator, $matches[1]);

            }, $regex);

            return preg_replace('~\?~','',$regex);
      }

      public static function random_string($multiplicator, $charset = ''){
            $str = '';
            for($i=0;$i<$multiplicator;$i++){
                  $str .= $charset[mt_rand(0,strlen($charset))];
            }
            return $str;
      }

}