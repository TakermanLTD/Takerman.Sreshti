<?php
// host
define('WP_HOME', 'http://sreshti');
define('WP_SITEURL', 'http://sreshti');

// database
define('DB_NAME', 'takerman_sreshti');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// https://api.wordpress.org/secret-key/1.1/salt/

define('AUTH_KEY',         '2E4R9hR@+Q[nFApyW~jf?hls_6?7Ga<~]538UG3g|^e)l:kC-a^N1[8SG-/oOe+S');
define('SECURE_AUTH_KEY',  '4UMZZ2k#HGDB9cZRYYgR/{Tb=cVG*z+J1z,$R`NT7fRcBmx4?&k|+NB?bPV>O<#]');
define('LOGGED_IN_KEY',    '28|o9GbpX0t$;iVOXJUKdjoN+gV4KU{is)?!,qGgn7sbqX<q-eNC{Dno?@]RN9d:');
define('NONCE_KEY',        '&bHOcJD|q7=+&Z(*;~l@gXv6iN?bB%Fk^a{dP?og4C1B;c,SWw{bz6Q4-tvK-dsa');
define('AUTH_SALT',        '?%)=&SWmqL^qrVB]62-8qt+c^*iV(b#=C49Pb&|Vfs(Nk^d#nnqC$*rg~o|-67|9');
define('SECURE_AUTH_SALT', ';)j:.d .A)A?k6oGT}}yIZ.TIdgS$$bQw-`:4!s=]PL0 F)P4`x)qP|8m3vB8+o/');
define('LOGGED_IN_SALT',   'Ws-5{{0KP4|x)OB.ndPU|!rv:liWe_{$K|:+ Fy.zxp6+WXb-@E0;J%ECx/<*1(v');
define('NONCE_SALT',       '#wQChK->Yv/d8bB+da+TD]}>Mc8HO;n35Z5fvA#A7X?iM<$9u6,/.+I1oQqtqpY3');
define('WP_CACHE_KEY_SALT', 'Ge4~%O1,L1y6liy[/J*5PfB)b4wY{5.nw]qOvqc~C}k!L6/TG}eUJ@EqgFCP{0+?');
define("VIDEO_CONFERENCING_HOST_ASSIGN_PAGE", true);

$table_prefix = 'wp_';

// debug
define('WP_DEBUG', 'true');
define('WP_DEBUG_LOG', 'true');
define('WP_DEBUG_DISPLAY', 'false');
@ini_set('display_errors', 0);
define('SCRIPT_DEBUG', 'true');
define('FS_METHOD', 'direct');

if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(__FILE__) . '/');
}

require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php');

define('DISALLOW_FILE_EDIT', false);
define('WP_ALLOW_REPAIR', 'true');
