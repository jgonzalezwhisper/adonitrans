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
define( 'DB_NAME', 'adonitrans' );

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
define( 'AUTH_KEY',         'bk*!9Wz0vhm>u{|%-Eq]]U?s~4)fE<9NOu/!Zv=G^#4wf^gEE;=>/32|CXoj:k4_' );
define( 'SECURE_AUTH_KEY',  '$ge7jaZFw^^3h6Db;53tt1QM7V>+=;7swvqA5Ox^Y9m;L^y*Az-(z1(n7jIN,NH_' );
define( 'LOGGED_IN_KEY',    'r-UCJvxt^wG/}=WaL@KL]It-:-5JD{9<UIl)o$CngO#OWy`vO&dYG,#}3K.p*}w]' );
define( 'NONCE_KEY',        '+<D l13BCGlJnD]ClD=OjE8p9f7,yWz.gpo0h? ]78|EM o2(k0HN1YD|%bA@F*2' );
define( 'AUTH_SALT',        '};tl$OI^`V6czp)Rh ^1ee^][H`*>qU4KSKyS:Jn-Zk>>ciu%X:AG!#Yx#4Zejj,' );
define( 'SECURE_AUTH_SALT', '-{s_/G~jHPP>HB.`;Fu9m7k15@&R+I-11}M67mE?[qXln2C7B_|I1j,q1e.sL3^5' );
define( 'LOGGED_IN_SALT',   'x.ZH7Ek;gioaB4ieY MwKx4lasK%>1^MfQ%&Bt`R=j~OzPYD72LIHR/G+^/5lS0?' );
define( 'NONCE_SALT',       'A+D?#7P}%vmH6Tytuny{KGRLcg^RV, LtKp3dcKkyp#Bi4s;!9[S=Boin$Hk_V|i' );

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

define( 'WP_MEMORY_LIMIT', '512M' );
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('FORCE_SSL_ADMIN', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
