<?php
defined('ABSPATH') or die("No.");
/*
Plugin Name: LDJam
Plugin URI: http://ludumdare.com/
Description: Ludum Dare Game Jam Website
Version: 0.1
Author: Mike Kasprzak
Author URI: http://www.sykhronics.com
License: TBD
*/

require_once "lib.php";				// Helper Functions //
require_once "core.php";			// LDJam Core //


function shortcode_ldjam( $atts ) {
	ld_get_vars();	// Populate the $ldvar global //
	global $ldvar;
	
	// Verify URL //
	if ( isset($_GET['u']) ) {
		$url = to_slug($_GET['u']);
		if ( $url !== $_GET['u'] ) {
			$_GET['u'] = $url;

			$link =  "//$_SERVER[HTTP_HOST]$_SERVER[REDIRECT_URL]";
			$link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
			$link .= "?" . http_build_query($_GET);
			$link = str_replace('%2F', '/', $link);		// Replace % code for / with an actual slash //

			ld_redirect( $link );
		}
	}
	
	$id = null;

	// Verify ID //
	if ( isset($_GET['i']) ) {
		$id = base_decode( base_fix($_GET['i']) );
	}
//	ld_redirect
//	print_r($_GET);
//	echo( to_slug($_GET['u']) );

	$shimmy = apcu_fetch('shimmy');

	echo( $_GET['u'] );
	echo " | " . $id;
	echo " | " . base_encode( $id );

	//print_r($_SERVER);
	
	return "<br />I am very important";
}
add_shortcode( 'ldjam', 'shortcode_ldjam' );


function shortcode_ldjam_root( $atts ) {
	ld_get_vars();	// Populate the $ldvar global //
	global $ldvar;
	
	if ( ld_is_admin() ) {
		$out = "";

		if ( strtolower($_SERVER['REQUEST_METHOD']) === "post" ) {
			print_r($_POST);
			if ( isset($_POST['event_active']) ) {
				ld_set_var('event_active', !to_bool($_POST['event_active']) ? "true" : "false" );
			}
		}
		
		$out .= '
			<form method="post">
				<input type="hidden" name="event_active" value="'.$ldvar['event_active'].'">
				<input type="submit" value="'.$ldvar['event_active'].'">
			</form>';
		
		return $out;
	}
	else {
		return "";
	}
}
add_shortcode( 'ldjam-root', 'shortcode_ldjam_root' );

function shortcode_ldjam_game( $atts ) {
	ld_get_vars();	// Populate the $ldvar global //
	global $ldvar;
	ld_get_urlcache();
	global $ld_urlcache;
	
	print_r( $ld_urlcache );

	// No Base URL Here //
	return "Game Browser";
}
add_shortcode( 'ldjam-game', 'shortcode_ldjam_game' );


/* This goes in the theme, so a shortcode isn't possible */
function ldjam_show_bar() {
	ld_get_vars();	// Populate the $ldvar global //
	global $ldvar;
	
	if ( to_bool($ldvar['event_active']) ) {
		return "On Now: <strong>{$ldvar['event']}</strong>";
	}
	
	// No bar //
	return "";
}


function ldjam_activate() {
	ld_init_vars();
	ld_init_urlcache();
	ld_init_content();
}
register_activation_hook( __FILE__, "ldjam_activate");

?>