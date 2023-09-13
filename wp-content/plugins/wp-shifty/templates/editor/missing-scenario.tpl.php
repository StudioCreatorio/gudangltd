<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
      <head>
      	<meta name="viewport" content="width=device-width" />
      	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      	<title><?php echo esc_html__('WP Shifty Error', 'wp-shifty');?></title>

            <style>
                  @font-face {
                    font-family: 'IsidoraSans-SemiBold';
                    src: url('<?php echo WP_SHIFTY_URI;?>/assets/fonts/IsidoraSans-SemiBold.eot');
                    src: url('<?php echo WP_SHIFTY_URI;?>/assets/fonts/IsidoraSans-SemiBold.eot?#iefix') format('embedded-opentype'),
                         url('<?php echo WP_SHIFTY_URI;?>/assets/fonts/IsidoraSans-SemiBold.ttf') format('truetype'),
                         url('<?php echo WP_SHIFTY_URI;?>/assets/fonts/IsidoraSans-SemiBold.woff') format('woff'),
                         url('<?php echo WP_SHIFTY_URI;?>/assets/fonts/IsidoraSans-SemiBold.woff2') format('woff2');
                    font-weight: normal;
                    font-style: normal;
                  }

                  body {
                        color: #d9d9d9;
                  }

                  .wrapper {
                        width: 100%;
                        height: 100vh;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        flex-wrap: wrap;
                        flex-direction: column;
                  }

                  .inner {
                        display: flex;
                        flex-direction: column;
                        flex-wrap: wrap;
                        row-gap: 15px;
                        align-items: center;
                        justify-content: center;
                        width: 50%;
                        height: 50vh;
                        max-height: 400px;
                        max-width: 400px;
                        padding: 40px;
                        background: #fff300;
                        border-bottom-left-radius: 50px;
                        border-top-right-radius: 50px;
                        box-shadow: 2px 2px 5px #d6d6d6;
                  }

                  .error-message {
                        width: 100%;
                        font-size: 40px;
                        font-family: IsidoraSans-SemiBold;
                        text-align: center;
                        color: #000;
                        text-shadow: 2px 2px 5px rgba(0,0,0,.2);
                  }

            </style>
      </head>
      <body>
            <div class="wrapper">
                  <div class="inner">
                        <div class="error-message"><?php esc_html_e('Scenario not found :(', 'wp-shifty');?></div>
                        <img src="<?php echo WP_SHIFTY_URI?>images/fox.png">
                  </div>
            </div>
      </body>
</html>