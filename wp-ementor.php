<?php
/*
Plugin Name: eMentor.pl (oficjalna wtyczka dla Wordpress)
Plugin URI: https://github.com/Thinkscape/eMentor-wp
Description: Integracja WordPress z platformą publikacji i sprzedaży materiałów szkoleniowych eMentor.pl
Version: 0.8
Author: Artur Bodera (Thinkscape)
Author URI: http://www.ementor.pl
License: GPL2
*/

global $wp_version;

/**
 * Check PHP version
 */
if (version_compare( PHP_VERSION, '5.3.0' ) == -1) {
	function wp_ementor_wrong_php_ver() {
		if (current_user_can( 'manage_options' )) {
			echo 	'<div id="message" class="error"><p>'.
					'<strong>Nie można załadować wtyczki eMentor</strong><br/>'.
					'Wtyczka eMentor wymaga PHP w wersji 5.3.0 lub nowszej. Obecnie jest zainstalowana wersja '.
					PHP_VERSION.
					'</p></div>';
		}
	}

	add_action( 'admin_notices', 'wp_ementor_wrong_php_ver' );
}
/**
 * Check eMentor SDK
 */
elseif (!file_exists( __DIR__ . '/library/sdk' ) || !file_exists( __DIR__ . '/library/sdk/autoload_function.php' )) {
	function wp_ementor_no_sdk() {
		if (current_user_can( 'manage_options' )) {
			echo
				'<div id="message" class="error"><p>' .
				'<strong>Nie można załadować wtyczki eMentor</strong><br/>
				Plugin wymaga instalacji biblioteki eMentor-SDK. Pobierz ją z ' .
				'<a href="https://github.com/Thinkscape/eMentor-sdk/downloads" target="_blank">' .
				'oficjalnego repozytorium</a> i skopiuj do katalogu wp-content/plugins/ementor/library/sdk' .
				'</p></div>';
		}
	}
	add_action( 'admin_notices', 'wp_ementor_no_sdk' );
}

elseif (version_compare( $wp_version, '3.3.1' ) == -1) {
	function wp_ementor_wrong_php_ver() {
		global $wp_version;
		if (current_user_can( 'manage_options' )) {
			echo 	'<div id="message" class="error"><p>'.
					'<strong>Nie można załadować wtyczki eMentor</strong><br/>'.
					'Wtyczka eMentor wymaga Wordpress we wersji 3.1.0 lub nowszej. '.
					'Obecnie używasz wersji '.$wp_version.
					'</p></div>';
		}
	}

	add_action( 'admin_notices', 'wp_ementor_wrong_php_ver' );
}



/**
 * Init Wordpress Plugin
 */
else{
	define('EMT_ABSPATH',__DIR__);
	define('EMT_ABSPLUGIN',__FILE__);
//	define('WP_DEBUG',true);
	require_once EMT_ABSPATH.'/library/sdk/autoload_register.php';
	require_once EMT_ABSPATH.'/autoload_register.php';
	new EMT\Wordpress\Plugin();
}