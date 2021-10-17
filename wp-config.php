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
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '40Vr6wtXmy2bReDtOxb96OyXm0DXliRjy9f6YQ3m1WBOv8Kn24y457bNm056+DrQgnBXWid5NQFRoOGXYFOWkA==');
define('SECURE_AUTH_KEY',  'RqbkZtsRclvtStYAulTiagGcVPoJLclMI1MSW0qOeIWB+YSoon+BfA0SNMIqJaP3WTEkPBA40iwZlUWdRldEYA==');
define('LOGGED_IN_KEY',    'LMw+tOAsbRWiTGcqoZe7AMEeskHF1RQpHjXOfpjS9j649Ozt/FD8Wz0RmySwJT464CrKLgq8tt5ogJlFcf+yvw==');
define('NONCE_KEY',        'x6M3864ZALH6EGTgb0hxuHwMfKuiTjFt9ETGihqTHbNm+4HTromFZvAxo57TWdruU63Yfye8iSGkjO8LNz2faw==');
define('AUTH_SALT',        'r8Xc553FwMB7gthyzX6bftT5lOzTFoSWES7s/h3pPh1MWWfdojdHyKmeR6yhNfsywn17+nbRGl4W5RiKE1Sh4Q==');
define('SECURE_AUTH_SALT', 'UNnQq9Cn1kU96KnKPH5TA/RxzYlBMP6Yf4lV2ErQW9IWAjnYHAs4Mrls3M+Qlse/KpQRfosZ1BrB2mjw1gnZGw==');
define('LOGGED_IN_SALT',   'mlZllqTIL8Eg18nRBP92ktNFPzLevebDNMQo78gRCthwP1scrRfCrbHQL25VPyMiZdJ/Csm8tXyjl+Gklmdlfg==');
define('NONCE_SALT',       '0s1DEO+onI3PA+opfqVQa6CdCCMwmJGcS4cemkJv2e6AchQ8scCUCodMAUw/gKEALI/ynCLOeDvQ00b3emwM2A==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
