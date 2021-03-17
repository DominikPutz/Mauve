<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mauve_db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'WB/_=9|{]CU-kK?~Tmd!},NkS T:9TycLnbJ/{X3Xx[#pu44MHzIDd*v_p>-9.0<' );
define( 'SECURE_AUTH_KEY',  '}b{Kx* hfP[-zL&Bf[VGFRN[a!sK$9z-A7xgBj.xxdJAk]OW(GYlgkOi<!K0i?Z6' );
define( 'LOGGED_IN_KEY',    'Pu?$aHufx;3-QF0)fCTHGZ`,*mJs5qcl@mCPp&q 1OXBd4$Wmb>#;s8[;-HP-mh(' );
define( 'NONCE_KEY',        'P(02kU5$O,~C-Cq_LoM +R^@POTt+~YN:}&#tr3B89VxxW}xZR9#U(.k,9B:ppYA' );
define( 'AUTH_SALT',        'QgJ<ORZGV8X6J2!>DNTE0?|{YB MK($2B)>}wHj`$=>vi1#Z53Mo{D8!tYZc<kv/' );
define( 'SECURE_AUTH_SALT', '~KSXn?~`4OH+s)q(bTQWX@oOvLo{PT~g()oXAr300^83gbA M2fW4TBRwQ7&2fBu' );
define( 'LOGGED_IN_SALT',   'ru0,hu@F=QcMJ7EY~(!}/I]m4u|Lyu>!N([%VIABBM_MV3ii-R/x!6FkP:M3-s#,' );
define( 'NONCE_SALT',       'doFrEZY>4&!/6,^ZfOt~m6z/A!+to{R6@^:Lw(~N {UR3.15}(S;*H@8)EXX/R&>' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
