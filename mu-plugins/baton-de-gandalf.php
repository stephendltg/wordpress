<?php
/*
 * Plugin Name: Baton de gandalf - Trompez l'ennemi.
 * Description: Change l'url wp-login.
 * Version: 1
 * Author: Grégory Viguier modifier par DELETANG Stéphen
 * Nécessite un serveur apache ainsi que WP > 3.4

 * HTACCESS ( A ajouter ):
	# BEGIN SF Move Login
	<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase /DEMO/				# <- Modifier selon repertoire du site ( voir le RewriteBase mit par wordpress )
	RewriteRule ^login/?$ $1wp-login.php [QSA,L]
	RewriteRule ^postpass/?$ $1wp-login.php?action=postpass [QSA,L]
	RewriteRule ^logout/?$ $1wp-login.php?action=logout [QSA,L]
	RewriteRule ^lostpassword/?$ $1wp-login.php?action=lostpassword [QSA,L]
	RewriteRule ^retrievepassword/?$ $1wp-login.php?action=retrievepassword [QSA,L]
	RewriteRule ^resetpass/?$ $1wp-login.php?action=resetpass [QSA,L]
	RewriteRule ^rp/?$ $1wp-login.php?action=rp [QSA,L]
	RewriteRule ^register/?$ $1wp-login.php?action=register [QSA,L]
	</IfModule>
	# END SF Move Login
 */

if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/* !---------------------------------------------------------------------------- */
/* !	OPTIONS																	 */
/* ----------------------------------------------------------------------------- */

// !Get the slugs

function _gandalf_get_slugs() {
	static $slugs = array();	// Keep the same slugs all along.
	if ( empty($slugs) ) {
		$slugs = array(
			'postpass'			=> 'postpass',
			'logout'			=> 'logout',
			'lostpassword'		=> 'lostpassword',
			'retrievepassword'	=> 'retrievepassword',
			'resetpass'			=> 'resetpass',
			'rp'				=> 'rp',
			'register'			=> 'register',
			'login'				=> 'login',
		); // modifier Htaccess en conséquence.

		// Plugins can add their own action
		$additional_slugs = apply_filters( '_gandalf_additional_slugs', array() );
		if ( !empty( $additional_slugs ) ) {
			$additional_slugs = array_keys( $additional_slugs );
			$additional_slugs = array_combine( $additional_slugs, $additional_slugs );
			$additional_slugs = array_diff_key( $additional_slugs, $slugs );	// Don't screw the default ones
			$slugs = array_merge( $slugs, $additional_slugs );
		}

		// Generic filter, change the values
		$slugs = apply_filters( '_gandalf_slugs', $slugs );
	}
	return $slugs;
	
}


// !Access to wp-login.php

function _gandalf_deny_wp_login_access() {
	return apply_filters( '_gandalf_deny_wp_login_access', 2 );	// 1: error message, 2: 404, 3: home
}


// !Access to the administration area

function _gandalf_deny_admin_access() {
	return apply_filters( '_gandalf_deny_admin_access', 0 );	// 0: nothing, 1: error message, 2: 404, 3: home
}


/* --------------------------------------------------------------------------------- */
/* !TOOLS																			 */
/* --------------------------------------------------------------------------------- */

function _gandalf_is_admin() {
	global $pagenow;
	return is_admin() && !( (defined('DOING_AJAX') && DOING_AJAX) || ($pagenow == 'admin-post.php' && !empty($_REQUEST['action'])) );
}


/* !---------------------------------------------------------------------------- */
/* !	EMERGENCY BYPASS														 */
/* ----------------------------------------------------------------------------- */

if ( defined('_gandalf_ALLOW_LOGIN_ACCESS') && _gandalf_ALLOW_LOGIN_ACCESS )
	return;


/* !---------------------------------------------------------------------------- */
/* !	FILTER URLS																 */
/* ----------------------------------------------------------------------------- */

// !Site URL

add_filter( 'site_url', '_gandalf_site_url', 10, 4);

function _gandalf_site_url( $url, $path, $scheme, $blog_id = null ) {
	if ( ($scheme === 'login' || $scheme === 'login_post') && !empty($path) && is_string($path) && strpos($path, '..') === false && strpos($path, 'wp-login.php') !== false ) {
		// Base url
		if ( empty( $blog_id ) || !is_multisite() ) {
			$url = get_option( 'siteurl' );
		} else {
			switch_to_blog( $blog_id );
			$url = get_option( 'siteurl' );
			restore_current_blog();
		}

		$url = set_url_scheme( $url, $scheme );
		return $url . _gandalf_set_path( $path );
	}
	return $url;
}


// !Network site URL

add_filter( 'network_site_url', '_gandalf_network_site_url', 10, 3);

function _gandalf_network_site_url( $url, $path, $scheme ) {
	if ( ($scheme === 'login' || $scheme === 'login_post') && !empty($path) && is_string($path) && strpos($path, '..') === false && strpos($path, 'wp-login.php') !== false ) {
		global $current_site;

		$url = set_url_scheme( 'http://' . $current_site->domain . $current_site->path, $scheme );
		return $url . _gandalf_set_path( $path );
	}
	return $url;
}


// !Logout url: wp_logout_url() add the action param after using site_url()

add_filter( 'logout_url', '_gandalf_logout_url' );
function _gandalf_logout_url( $link ) {
	return _gandalf_login_to_action( $link, 'logout' );
}


// !Forgot password url: lostpassword_url() add the action param after using site_url()

add_filter( 'lostpassword_url', '_gandalf_lostpass_url' );
function _gandalf_lostpass_url( $link ) {
	return _gandalf_login_to_action( $link, 'lostpassword' );
}


// !Redirections are hard-coded

add_filter('wp_redirect', '_gandalf_redirect', 10, 2);

function _gandalf_redirect( $location, $status ) {
	if ( site_url( reset( (explode( '?', $location )) ) ) == site_url( 'wp-login.php' ) )
		return _gandalf_site_url( $location, $location, 'login', get_current_blog_id() );

	return $location;
}


/* !---------------------------------------------------------------------------- */
/* !	IF NOT CONNECTED, DENY ACCESS TO WP-LOGIN.PHP							 */
/* ----------------------------------------------------------------------------- */

add_action( 'login_init', '_gandalf_login_init', 0 );

function _gandalf_login_init() {
	// If the user is logged in, do nothing, lets WP redirect this user to the administration area.
	if ( is_user_logged_in() )
		return;

	$uri = !empty($GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']) ? $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] : (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
	$uri = parse_url( $uri );
	$uri = !empty($uri['path']) ? str_replace( '/', '', basename($uri['path']) ) : '';

	if ( $uri === 'wp-login.php' ) {
		do_action( '_gandalf_wp_login_error' );

		// To make sure something happen.
		if ( false === has_action( '_gandalf_wp_login_error' ) ) {
			_gandalf_wp_login_error();
		}
	}
}


add_action( '_gandalf_wp_login_error', '_gandalf_wp_login_error' );

function _gandalf_wp_login_error() {
	$do = _gandalf_deny_wp_login_access();
	switch( $do ) {
		case 2:
			$redirect = $GLOBALS['wp_rewrite']->using_permalinks() ? home_url('404') : add_query_arg( 'p', '404', home_url() );
			wp_safe_redirect( esc_url( apply_filters( '_gandalf_404_error_page', $redirect ) ) );
			exit;
		case 3:
			wp_safe_redirect( home_url() );
			exit;
		default:
			wp_die( __('No no no, the login form is not here.', 'sf-move-login') );
	}
}


/* !---------------------------------------------------------------------------- */
/* !	IF NOT CONNECTED, DO NOT REDIRECT FROM ADMIN AREA TO WP-LOGIN.PHP		 */
/* ----------------------------------------------------------------------------- */

add_action( 'after_setup_theme', '_gandalf_maybe_die_before_admin_redirect' );

function _gandalf_maybe_die_before_admin_redirect() {
	// If it's not the administration area, or if it's an ajax call, no need to go further.
	if ( !_gandalf_is_admin() )
		return;

	$scheme = is_user_admin() ? 'logged_in' : apply_filters( 'auth_redirect_scheme', '' );

	if ( !wp_validate_auth_cookie( '', $scheme) && _gandalf_deny_admin_access() ) {
		do_action( '_gandalf_wp_admin_error' );

		// To make sure something happen.
		if ( false === has_action( '_gandalf_wp_admin_error' ) ) {
			_gandalf_wp_admin_error();
		}
	}
}


add_action( '_gandalf_wp_admin_error', '_gandalf_wp_admin_error' );

function _gandalf_wp_admin_error() {
	$do = _gandalf_deny_admin_access();
	switch( $do ) {
		case 1:
			wp_die( __('Cheatin&#8217; uh?') );
		case 2:
			$redirect = $GLOBALS['wp_rewrite']->using_permalinks() ? home_url('404') : add_query_arg( 'p', '404', home_url() );
			wp_safe_redirect( esc_url( apply_filters( '_gandalf_404_error_page', $redirect ) ) );
			exit;
		case 3:
			wp_safe_redirect( home_url() );
			exit;
	}
}


/* !---------------------------------------------------------------------------- */
/* !	UTILITIES																 */
/* ----------------------------------------------------------------------------- */

// !Construct the url

function _gandalf_set_path( $path ) {
	$slugs = _gandalf_get_slugs();
	// Action
	$parsed_path = parse_url( $path );
	if ( !empty( $parsed_path['query'] ) ) {
		wp_parse_str( $parsed_path['query'], $params );
		$action = !empty( $params['action'] ) ? $params['action'] : 'login';

		if ( isset( $params['key'] ) )
			$action = 'resetpass';

		if ( !isset($slugs[$action]) && false === has_filter( 'login_form_' . $action ) )
			$action = 'login';
	} else
		$action = 'login';

	// Path
	if ( isset($slugs[$action]) ) {
		$path = str_replace('wp-login.php', $slugs[$action], $path);
		$path = remove_query_arg('action', $path);
	}
	else {	// In case of a custom action
		$path = str_replace('wp-login.php', $slugs['login'], $path);
		$path = remove_query_arg('action', $path);
		$path = add_query_arg('action', $action, $path);
	}

	return '/' . ltrim( $path, '/' );
}


// !login?action=logout -> /logout

function _gandalf_login_to_action( $link, $action ) {
	$slugs = _gandalf_get_slugs();
	$need_action_param = false;

	if ( isset($slugs[$action]) ) {
		$slug = $slugs[$action];
	}
	else {	// Shouldn't happen, because this function is not used in this case.
		$slug = $slugs['login'];

		if ( false === has_filter( 'login_form_' . $action ) )
			$action = 'login';
		else		// In case of a custom action
			$need_action_param = true;
	}

	if ( $link && strpos($link, '/'.$slug) === false ) {
		$link = str_replace(array('/'.$slugs['login'], '&amp;', '?amp;', '&'), array('/'.$slug, '&', '?', '&amp;'), remove_query_arg('action', $link));
		if ( $need_action_param )		// In case of a custom action, shouldn't happen.
			$link = add_query_arg('action', $action, $link);
	}
	return $link;
}