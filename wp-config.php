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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', 'D:\xampp81\htdocs-sanggar\wp-content\plugins\wp-madx-cache/' );
define( 'DB_NAME', 'sanggar' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'f#K>P][jv<=chnMfqe?6^DAG,.53Xx!e_WGi|8_2`F.oh;2yB|S=)~bMx5r?}<Sn' );
define( 'SECURE_AUTH_KEY',  '),bv`7GYl.q3}tg r!N)*5MruI;nLic&c9lTUIh1E8TGXH7-h7&TOi<wfNkfvtFi' );
define( 'LOGGED_IN_KEY',    'FkorS?7C`KI&JfAt[5|v$3jnN6ApQ>i4_6w99Y~6]7Z3aVrDPi5Y&(PVb+Yy95FI' );
define( 'NONCE_KEY',        'p<jsV>B?ajlsIM;+oe(k_1{?^kDPa7K:8kz-b&uN@,wxj@}9>*w0zfYt$b~*-{TX' );
define( 'AUTH_SALT',        'wMj:FoK4BPxu3$/9U}x04/=MZ(?n?j;%5n1YZrQF:AqKpe3*#QV2<}ngqqSLrg1X' );
define( 'SECURE_AUTH_SALT', 'ja1`25/&uBg1-Sq-PzSazSI ;}-&S7]fkB3k+qed_BN.*^l@<<[6(!vUw&}tf2?4' );
define( 'LOGGED_IN_SALT',   '1{CnMF (gQ,;}95cLn*T;@6kN;Jg0aV-PMXe!oz.dgKH6KxKjlL1XLp:fsYXX|J^' );
define( 'NONCE_SALT',       '7Kmw0^@ZdK@a2K2FRb;L`$Jf;_)UZLCumW5^OP(]Et4!q7YTg_oDO!dpPZW0y$hi' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'sw_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'AUTOMATIC_UPDATER_DISABLED', true );


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
