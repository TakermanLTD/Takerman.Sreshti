<?php
define( 'WP_CACHE', false ); // By SiteGround Optimizer

if ($_SERVER['SERVER_NAME'] === "sreshti") {
	require_once(ABSPATH . 'wp-config.dev.php');
} else {
	require_once(ABSPATH . 'wp-config.prod.php');
}