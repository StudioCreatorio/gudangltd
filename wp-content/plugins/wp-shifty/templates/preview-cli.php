<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
      <head>
      	<meta name="viewport" content="width=device-width" />
      	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      	<title><?php echo esc_html__('WP Shifty Preview', 'wp-shifty');?></title>

            <style data-skip-shifty>
                  body {
                        background: #000;
                        color: #fff;
                  }

                  .cursor {
                        animation: cursor-blink 1.5s steps(2) infinite;
                  }

                  @keyframes cursor-blink {
                    0% {
                      opacity: 0;
                    }
                  }
            </style>
      </head>
      <body>
            <div class="wrapper">
                  wp-cli@<?php echo parse_url(site_url(), PHP_URL_HOST)?> ~ # <span class="cursor">_</span>
            </div>
      </body>
</html>