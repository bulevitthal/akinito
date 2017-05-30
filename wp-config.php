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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'akinito');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'o>-WFSPRzBRRuPgOh(65bcFqJe;Vh}*BDNpy56qjJmO;&mDsRiN[vhYBqi1mMG.w');
define('SECURE_AUTH_KEY',  '));b$}sE7#@rUhF7bK_Nbb{M%C}q:$ZX3-R5i4%*F|w9]U}Z7$GEP&1s|6XFDrKk');
define('LOGGED_IN_KEY',    'Ze {3?b|~EMiipI,MU|aCz1lkrb9[U$Bx^obt12`%GJ/Qmx>BXAE3qho{rq;!G4|');
define('NONCE_KEY',        'LHRl#Z>D.[M+&<)7Wk>0`dy6 2J4}6?jI!;270(D|IcKr1eFH=?RHJuRy=<u[68n');
define('AUTH_SALT',        '|t?OxgnJB,oLjhM2 ?XUZ,oj$qf<oHL0Zrln5r<hw#=qsZ>6QpDY]1O }I@b.B<T');
define('SECURE_AUTH_SALT', 'RA#;]@@G!Wyavw@Z^aW+psuZW%0$|f6~*;l@PoUGPdu>ry=;jac9mvu{0C1u8z<T');
define('LOGGED_IN_SALT',   '{e@ZkP~_$Sy#Re4WyV5FA],vjZBfdt-$1HcfLz=c%P,ryU&=gZgCKWlYJ7Oqz}TX');
define('NONCE_SALT',       '`d]m:b}U<88F3EKxHBf+V-ZV@1}67dOj# N!`Ky4Npo#$gLOZqdf+<NNhZx X<c:');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
set_time_limit(180);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
