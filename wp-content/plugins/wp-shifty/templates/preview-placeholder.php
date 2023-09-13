<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
      <head>
      	<meta name="viewport" content="width=device-width" />
      	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      	<title><?php echo esc_html__('WP Shifty Preview', 'wp-shifty');?></title>

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

                  .row {
                        display: flex;
                        justify-content: center;
                  }

                  .placeholder {
                        width: 40%;
                  }

                  .inner {
                        display: flex;
                        align-content: center;
                        column-gap: 30px;
                        padding: 10px;
                        border: 3px solid;
                        border-radius: 50px;
                        width: 50%;
                        justify-content: center;
                        color: #adafb2;
                        font-family: IsidoraSans-SemiBold;
                        font-size: 1em;
                  }

                  @media (min-width: 768px){
                        .inner {
                              font-size: 4em;
                        }
                  }

                  .icon {
                        width: 1em;
                  }

            </style>
      </head>
      <body>
            <div class="wrapper">
                  <div class="row">
                        <svg class="placeholder" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 336H54a6 6 0 0 1-6-6V118a6 6 0 0 1 6-6h404a6 6 0 0 1 6 6v276a6 6 0 0 1-6 6zM128 152c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40zM96 352h320v-80l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L192 304l-39.515-39.515c-4.686-4.686-12.284-4.686-16.971 0L96 304v48z"></path></svg>
                  </div>
                  <div class="row">
                        <div class="inner">
                              <svg class="icon" aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"></path></svg>
                              <?php esc_html_e('Add a condition first', 'wp-shifty');?>
                        </div>
                  </div>
            </div>
      </body>
</html>