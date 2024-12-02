<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'testReview' );

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
define( 'AUTH_KEY',         'iRp(<%e! S%$9^>12#4d&Y471r&q;6!.7*?k{dS]{TLkz@ N1X{pwiKeuaFf*uK;' );
define( 'SECURE_AUTH_KEY',  '99Q:RS9RgMOwIF|4qlAub/;<@N*:w{+*mh6m{|Ce5< ~8&H<9G$.!!A.})0an,@9' );
define( 'LOGGED_IN_KEY',    'fhUSAdRrk]6Qq gPBrEx/3 AA*{T/VED,lOs|.`]~y-u9[n!!nxYZoz O+oJ)+;R' );
define( 'NONCE_KEY',        '[51Agx,#}!cChXXws5]uMZG,ooSA%aeSN]im&lMXP|{p!=[?4`!&lF]h>&x,>u|7' );
define( 'AUTH_SALT',        'RM}{Gbo:bo%.Fge&#S!e=sPZfI/,~b2RrJu?^<mf&qk&}]#@m2P^>Q:$$|1(sQ(_' );
define( 'SECURE_AUTH_SALT', 'cToy-d*B%=}+7c_(Fsi|@=+$=N}ASbUN0BGlqd_3-.|Avcd1&/--`L5ZX(?Qtf@E' );
define( 'LOGGED_IN_SALT',   'Utei8A$3kdI1+rRLItP5iVg_?PLr0*& Ih@;s?PQ(4VT@F2y/YQ=qt4P 57JXd*N' );
define( 'NONCE_SALT',       'f>6%KvcWBC*}Azk`gg G~7IIjuT:`/+QASnm{[?WOa){OSzUSwNGQX2oHi9l_)nL' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
