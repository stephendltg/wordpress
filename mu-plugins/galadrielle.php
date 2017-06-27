<?php
/*
Plugin Name: Galadrielle - Noblesse
Description: Adapte votre site Front-End.
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

defined('ABSPATH') or die('Vous avez bien fait de venir. Vous entendrez aujourd\' hui tout ce qu\'il vous est n&eacute;cessaire de savoir pour comprendre les desseins de l\'ennemi.'); 
	


/*** UTILISATION EMAIL OU IDENTIFIANT POUR SE CONNECTER ----------------------------------------*/
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 ); // On détruit l'authentification.
add_filter( 'authenticate', '_galadrielle_email_and_login_authenticate', 20, 3 ); // On recrée le mode d'authentification de WordPress avec notre fonction
function _galadrielle_email_and_login_authenticate( $user, $username, $password ) {
    if ( trim($username) != '' )
     $user = get_user_by( 'email', $username );
    if ( $user )
     $username = $user->user_login;
    return wp_authenticate_username_password( null, $username, $password );
}

/*** REDIRECTION SUITE A UNE CONNEXION  ----------------------------------------*/
add_filter("login_redirect", create_function('', "return home_url('/');") );


/*** NETTOYAGE BARRE OUTILS ----------------------------------------*/
// add_filter('show_admin_bar', '__return_true'); // Cacher la barre d'outils a tous les utilisateurs meme admin
add_action('init','_galadrielle_remove_toolsbar'); // Cacher la barre d'outils sauf administrateur
function _galadrielle_remove_toolsbar(){
	if (!current_user_can('administrator')) { add_filter('show_admin_bar', '__return_false'); }
}

/*** INTERDIR L'ACCES AUX PAGE PROFIL ET DASHBOARD ----------------------------------------*/
add_action( 'current_screen', 'redirect_non_authorized_user' );
function redirect_non_authorized_user() {
	if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
		wp_redirect( home_url( '/' ) );
		exit();
	}
}

/*** ON REORIENTE LES FLUX RSS ( DU COUP IL N'EXISTE PLUS ) ----------------------------------------*/
// add_action('do_feed', '_galadrielle_disable_all_feeds', 1);
// add_action('do_feed_rdf', '_galadrielle_disable_all_feeds', 1);
// add_action('do_feed_rss', '_galadrielle_disable_all_feeds', 1);
// add_action('do_feed_rss2', '_galadrielle_disable_all_feeds', 1);
// add_action('do_feed_atom', '_galadrielle_disable_all_feeds', 1);
function _galadrielle_disable_all_feeds() { wp_redirect( home_url() );} // -> On renvoi vers la page d'acceuil

/*** ON NETTOIE LE HEAD ----------------------------------------*/
remove_action('wp_head', 'feed_links', 2); // Affiche les liens des flux RSS pour les Articles et les commentaires.
remove_action('wp_head', 'feed_links_extra', 3); // Affiche les liens des flux RSS supplémentaires comme les catégories de vos articles.
remove_action('wp_head', 'rsd_link'); // Affiche le lien RSD (Really Simple Discovery). Je ne l'ai jamais utilisé mais si vous êtes certain d'en avoir besoin, laissez-le.
remove_action('wp_head', 'wlwmanifest_link'); // Affiche le lien xml dont a besoin Windows Live Writer pour accéder à votre blog. 
remove_action( 'wp_head', 'index_rel_link' );// index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );// prev link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );// start link
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );// Display relational links for the posts adjacent to the current post.
remove_action('wp_head','start_post_rel_link');
remove_action('wp_head','adjacent_posts_rel_link_wp_head'); // Affiche les liens relatifs vers les articles suivants et précédents.
remove_action('wp_head','wp_shortlink_wp_head'); // Affiche l'url raccourcie de la page ou vous vous situez.

?>
