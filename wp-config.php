<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'y63613wQPveh7P' );

/** Database username */
define( 'DB_USER', 'y63613wQPveh7P' );

/** Database password */
define( 'DB_PASSWORD', 'FhzhG0VPSusagn' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '#cq/CkQ3HVISfHBL14WL<;~;&&f!0`(PC9IB6uz;QHYpI5pNmh7]jIl@?$StA?a4' );
define( 'SECURE_AUTH_KEY',   'vpz!_uW%gpdB4 Opp|D<_vG-w) 4}!ml8ic4qAzn<;ty?4p{%cvA_TI{$zlxCv}W' );
define( 'LOGGED_IN_KEY',     '9.G7PR9@9DI8nagJGC5{0+Mo==Ad^Q*@,DO/On`5f:C4#|7vcsDpp<]|Qtf&A;Jl' );
define( 'NONCE_KEY',         '<he%g8vN%.u_I=9I:<+$.N{U18m<@ZqCrxBc&`ixBP;em{18F4}O5>-rUduINc:p' );
define( 'AUTH_SALT',         '.U]4BjWqlb6(zy:G-@6HowqF5Kw8O]&>B~{Q-K-~o1C5}b=`^,c-@oatw/R7_GrB' );
define( 'SECURE_AUTH_SALT',  '%4MHl-{yC[d8<Q5reD&+x1WIXn*O#fddR/5*y.wI%4&y&D;l%9,~^:`~nD?_Q7D.' );
define( 'LOGGED_IN_SALT',    '<r9:PD9S_rIb*W/>8F@+v7-{b<#Y8.aB-}%^wd|}QoJrxGQsP83-[r2uH6`t~~Sz' );
define( 'NONCE_SALT',        'k)cZ9lE}NDH-_]C)cWKf])zooM@2)I{z;R(ZQN__j=@@x#Hdf+T6|7*F2!d&y/ZQ' );
define( 'WP_CACHE_KEY_SALT', 'hc3J[LCDnV13]s{;77zO>Wj}4q-TkY<C+kB3JS~FJ$b0U}Q?Z3Pf~+%f=t>g*1$9' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

define('WP_MEMORY_LIMIT', '256M');

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
