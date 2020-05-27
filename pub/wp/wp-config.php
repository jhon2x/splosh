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
define('DB_NAME', 'DB_NAME_HERE');

/** MySQL database username */
define('DB_USER', 'DB_USER_HERE');

/** MySQL database password */
define('DB_PASSWORD', 'DB_PASSWORD_HERE');

/** MySQL hostname */
define('DB_HOST', 'DB_HOST_HERE');

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
define('AUTH_KEY',         'qTN7Qj[`. 2?#Q%#hR)WCWc+#>,l@U5E7j$8m$p5M&+).V6~HfC$6!iB/iB$J~p3');
define('SECURE_AUTH_KEY',  'l*q5$R7Gr6|3IS-pKa[YLqi!.aR)fk..mSO3iSiy!?tq+cl%61&4jZ/ia8H`,[6h');
define('LOGGED_IN_KEY',    'V[=u_Px!kRER%HW?<f3A0KLWauu@4(ht-Lr6U=k%IgJW18!~$kx ~w&7^]&4@x^Q');
define('NONCE_KEY',        '$J%{MF~1TaIPDG:YWV-^ XfT@{UJmx>*cX71WX?q(0EV7OeN1L14t^`q5g4|v.Ed');
define('AUTH_SALT',        'b3|F)4]GMx$3:mqGZ<Zi}Low!bG~qR/F,[)#reMa]**|U!eC.#H*;NplLW<5Y{X(');
define('SECURE_AUTH_SALT', '#3@>^0)4^P2y;xS`g!Mha}8^ae3F`8j+`dd=5IdC<8v&:6l^$R}}#wzmXSwN(%j(');
define('LOGGED_IN_SALT',   '~(1^*Brm7TvqS$rNNkF:( ,ULp&$Ga/pfT*g]szgG;(8*x]?*6{x~$BiFZ+l$Sw1');
define('NONCE_SALT',       'mM>z&p11[_d7^sZWLU]YV=.LG2sP[ycN[p!YB=3aO>Ek_L??jcn$AAV`ljC>~r^V');

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

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
