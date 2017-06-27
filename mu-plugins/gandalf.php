<?php
/*
Plugin Name: Gandalf - Vous ne passerez pas !
Description: Améliore la securité wordpress.
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

defined('ABSPATH') or die('Vous avez bien fait de venir. Vous entendrez aujourd\' hui tout ce qu\'il vous est n&eacute;cessaire de savoir pour comprendre les desseins de l\'ennemi.'); 

/*** FORCER L'ADRESSE EMAIL DE L'ADMINISTRATEUR  ----------------------------------------*/
add_filter( 'option_admin_email', '_gandalf_admin_email' ); 
function _gandalf_admin_email( $value ) { return 's.deletang@laposte.net';} 
	
/*** FORCER L'IMPOSSIBILITE DE S'INSCRIRE  ----------------------------------------*/
add_filter( 'pre_option_users_can_register', '_gandalf_users_can_register' ); 
function _gandalf_users_can_register( $value ) { return '0'; } 

/*** FORCER LE ROLE ABONNE ----------------------------------------*/
add_filter( 'pre_option_default_role', '_gandalf_default_role' ); 
function _gandalf_default_role( $value ) { return 'subscriber'; }

/*** ENLEVER LES NOTIFICATION WP ( TOUT SAUF ADMIN )  ----------------------------------------*/
add_action('admin_notices','_gandalf_update_notification_nonadmins',1);
function _gandalf_update_notification_nonadmins() {
	if (!current_user_can('administrator')) 
	remove_action('admin_notices','update_nag',3);
}

/*** DESACTIVE SELF-TRACKBACKING  ----------------------------------------*/
add_action('pre_ping','_gandalf_disable_self_pings');
function _gandalf_disable_self_pings( &$links ) {
	foreach ( $links as $l => $link )
		if ( 0 === strpos( $link, home_url() ) )
			unset($links[$l]);
}
	
/*** BLOQUER L'USURPATION D'IDENTITE  ----------------------------------------*/
add_filter('preprocess_comment', '_gandalf_preprocess_comment' );
function _gandalf_preprocess_comment( $commentdata ) {
    	if( is_user_logged_in() || $commentdata['comment_type']!='' )
			return $commentdata; 
			
    	$user = '';
    	
    	if( $commentdata['comment_author']!='' ):	
        	$user = get_user_by( 'slug', $commentdata['comment_author'] );
        	$info = 'ce pseudo';
    	endif;
    	
    	if( !$user && $commentdata['comment_author_email']!='' ):
        	$user = get_user_by( 'email', $commentdata['comment_author_email'] );
        	$info = 'cette adresse email';
    	endif;
    	
    	if( $user )
			wp_die( '<p>Impossible de continuer car ' . $info . ' correspond &agrave; un membre sur ce site. </p><p>S\'il s\'agit de vous, merci de vous connecter</a>.</p><p align="right"> <a href="' . esc_url( wp_get_referer() ) . '">Retour &raquo;</a></p>' );
    		
    	return $commentdata;
	}

/*** ENLEVER MESSAGE ERREUR DE LA FENTRE LOGIN EN CAS D'ERREUR DE CONNEXION  ----------------------------------------*/
add_filter('login_errors',create_function('$a', "return null;")); 
	 	
/*** SUPPRIMER VERSION WORDPRESS  ----------------------------------------*/
remove_action('wp_head', 'wp_generator');

/*** RETIRER NUMERO DE VERSION FLUX RSS ----------------------------------------*/
add_filter('the_generator', create_function('', "return '';"));

/*** Désactive l'effet que wordpress corrige les lettres capitales incorrect dans le contenu ----------------------------------------*/
remove_filter( 'the_content', 'capital_P_dangit' );

/*** Désactive l'effet que wordpress corrige les lettres capitales incorrect dans le titre ----------------------------------------*/
remove_filter( 'the_title', 'capital_P_dangit' );

/*** Désactive l'effet que wordpress corrige les lettres capitales incorrect dans les commentaires ----------------------------------------*/
remove_filter( 'comment_text', 'capital_P_dangit' );

/*** Empêcher les accents dans les URLs lors de l'upload d'un média ainsi eviter erreur 404 ----------------------------------------*/
add_filter('sanitize_file_name', 'remove_accents' );

/*** RETIRER LA LOCALISATION  ----------------------------------------*/
add_action( 'init', '_gandalf_remove_l1on' ); //remove the l10n.js script http://eligrey.com/blog/post/passive-localization-in-javascript
function _gandalf_remove_l1on() {
	if ( !is_admin() ) {
		wp_deregister_script('l10n');
	}
}

/*** Limiter la durée de vie cookie des commentateurs ( option cache)  72h  ----------------------------------------*/
add_filter('comment_cookie_lifetime', '_gandalf_comment_cookie_lifetime');
function _gandalf_comment_cookie_lifetime($lifetime) { return 259200; } 

?>
