<?php
// host
define('WP_HOME', 'https://sreshti.net');
define('WP_SITEURL', 'https://sreshti.net');

// database
define('DB_NAME', 'db6f6gqwqs8ses');
define('DB_USER', 'uwuqygcntnc2f');
define('DB_PASSWORD', 'teygy1bjdeau');
define('DB_HOST', 'localhost');

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// https://api.wordpress.org/secret-key/1.1/salt/

define('AUTH_KEY',         ' p]/EwSqa0sBZ[RV{+I1R9:?00FRh%lv5RSc|lb^NVL|!DU2})T1T0^dq*KR*Gi)');
define('SECURE_AUTH_KEY',  '`C %=/yD+``(+RVzz13=B+agIQKqGk-]zD[)[fstDaHRP]${t[fPkC=o?dc!y;;_');
define('LOGGED_IN_KEY',    '42>k(5ca(:QJ.Bw3M@g!i6M::M37J`L;GnJ-;@U*Xx#c3b)YW^;*AC&l/4YM8U%)');
define('NONCE_KEY',        'IIzvfAw0Z 0Ao|0>?8Lds4.+r8:*0vLd;#I&9+02VX|,Z>enrQ;.S,x6gU:~y^VI');
define('AUTH_SALT',        'bMF1/NNo)_|.`VJoTg2DAVRBcJPIg`YS-,YbleEB@T/JN/@/K[b+qkS3dp@VwF|#');
define('SECURE_AUTH_SALT', '9a5GURFta-$1EbEX%i!f.0h3!vsY:wQEPy!-uI}v0eP>*~<Z~Swu+(6IMhc6d8cf');
define('LOGGED_IN_SALT',   'B#}^d *L#uNw^G+K[{77.-u+-a*/uoFr6JId0uJkxMJcK)xh!oazIQ*RJ+*8IZ<p');
define('NONCE_SALT',       'Qnf}T|e1|9XK)cx!#~Xc-Nygm~=k-`Z U+*h!+{<6PIX;J3d&z9-~C%]0C^ PU^e');
define('WP_CACHE_KEY_SALT', 'Ge4~%O1,L1y6liy[/J*5PfB)b4wY{5.nw]qOvqc~C}k!L6/TG}eUJ@EqgFCP{0+?');
define("VIDEO_CONFERENCING_HOST_ASSIGN_PAGE", true);

$table_prefix = 'lkd_';

if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(__FILE__) . '/');
}

require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php');

define('DISALLOW_FILE_EDIT', true);
define('WP_ALLOW_REPAIR', 'true');
