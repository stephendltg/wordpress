<?php
/*
Plugin Name: Eleanor - Noblesse
Description: Adapte votre site Back-End.
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

defined('ABSPATH') or die('Vous avez bien fait de venir. Vous entendrez aujourd\' hui tout ce qu\'il vous est n&eacute;cessaire de savoir pour comprendre les desseins de l\'ennemi.'); 
	

/*** PARAMETRAGE DES COMPTES UTILISATEURS ----------------------------------------*/
add_filter('user_contactmethods','_eleanor_modify_user_contact_methods',10,1);	// add facebook and twitter account to user profil
function _eleanor_modify_user_contact_methods($user_contact) {
	// $user_contact['skype'] = __('Skype'); 
	// $user_contact['twitter'] = __('Twitter');
	// $user_contact['facebook'] = __('Facebook');
	unset($user_contact['yim']);
	unset($user_contact['jabber']);
	unset($user_contact['aim']);
	return $user_contact;
}

/*** SUPPRESSION DES COMMENTAIRES ( 'page' ou 'post') ----------------------------------------*/
add_filter('comments_open', '_eleanor_comments_closed', 10, 2);
function _eleanor_comments_closed( $open, $post_id ) {
	$post = get_post( $post_id );
	if ('page' == $post->post_type)
	$open = false;
	return $open;
}

/*** AUTORISER SHORTCODE DANS WIDGET ----------------------------------------*/ 
if ( !is_admin() ) { add_filter ('widget_text','do_shortcode'); }

/*** MODIFIER URL DU HEADER LOGIN ----------------------------------------*/
add_filter( 'login_headerurl', create_function('', "return home_url('/');") );

/*** MODIFIER TITRE HEADER LOGIN ----------------------------------------*/ 
add_filter( 'login_headertitle', create_function('', "return get_option( 'blogname' );") );
 
/*** NETTOYAGE INTERFACE ADMIN ----------------------------------------*/
add_action( 'admin_menu', create_function('', "remove_filter( 'update_footer', 'core_update_footer' );") ); // on supprime le numero version WP dans footer admin
add_filter('admin_footer_text', '_eleanor_remove_footer_admin'); // modifier texte dans footer admin
add_filter( 'contextual_help', '_eleanor_remove_help', 999, 3 ); // Supprimer menu aide dans admin 
add_filter('screen_options_show_screen', '__return_false'); // Supprimer Option d'écran
add_action('wp_dashboard_setup', '_eleanor_remove_dashboard_widgets' ); // Nettoyage tableau de bord
add_action('wp_dashboard_setup', '_eleanor_add_dashboard_widgets' ); // Ajouter un widget sur Tableau de bord
// add_action('admin_menu', '_eleanor_delete_menu_items');// Supprimer objet du menu de l'admin
// add_action( 'admin_menu', '_eleanor_delete_submenu_page', 999 ); // deleting submenu page from admin aera
add_filter('manage_posts_columns', '_eleanor_custom_post_columns');// remove column entries from list of posts
add_filter('manage_pages_columns', '_eleanor_custom_pages_columns');// remove column entries from list of page
add_action('admin_bar_menu', '_eleanor_remove_item_admin_bar' , 999);// Nettoyer the admin bar
add_filter( 'pre_get_shortlink', '__return_empty_string' ); // Retirer les liens courts
add_filter( 'media_view_strings', '_eleanor_custom_media_uploader' ); // Retirer item dans "ajouter media"
add_filter( 'widget_meta_poweredby', '__return_empty_string', 10 ); // Supprimer liens vers wordpress du widget meta.
add_filter( 'widget_title', '__return_empty_string'); // Supprimer le titre du widget meta.
add_filter( 'register', '__return_empty_string' ); // Supprimer "admin. site " du widget meta.
// add_action('widgets_init', '_eleanor_unregister_default_widgets', 11);// remove widgets from the widget page
add_action('admin_head', '_eleanor_admin_color_scheme'); // Limiter le schema couleur de la page profil.
add_filter('tiny_mce_before_init', '_eleanor_tiny_full_editor'); // Améliore l'affichage de l'éditeur.
add_filter( 'upload_mimes', '_eleanor_add_mime_types' ); // Ajout support SVG pour les media.
add_action('admin_menu','_eleanor_remove_custom_field_meta_boxes'); // Supprimer champ personnalisé sur post de type page et article ainsi que plusieurs metabox.


/* LISTES DES APPELS FONCTIONS NETTOYAGE INTERFACE ADMIN */


/*** Modifier texte dans footer admin ----------------------------------------*/
if( !function_exists('_eleanor_remove_footer_admin'))  {
	function _eleanor_remove_footer_admin(){
		return '<span>Un site développé par DELETANG Stéphen</span>';
	}
}

/*----------------------------------------------------------------------*/

/*** Supprime l'onglet aide dans la partie admin ----------------------------------------*/
if( !function_exists('_eleanor_remove_help'))  {
	function _eleanor_remove_help( $old_help, $screen_id, $screen ){
		$screen->remove_help_tabs();
	    return $old_help;
	}
}

/*----------------------------------------------------------------------*/

	
/*** Nettoyage des widget du tableau de bord ----------------------------------------*/
if( !function_exists('_eleanor_remove_dashboard_widgets'))  {
	function _eleanor_remove_dashboard_widgets(){
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);	

	}
}
/*----------------------------------------------------------------------*/

/*** Ajouter un widget dans le tableau de bord- ----------------------------------------*/
function _eleanor_dashboard_widget_function() {
		echo "
		<ul>
		<li>Date de realisation : Avril 2014</li>
		<li>Auteurs : Stéphen DELETANG</li>
		<li>Web developper : <a href='mailto:s.deletang@laposte.net'>Stephen DELETANG</a></li>
		</ul>
		";
}
if( !function_exists('_eleanor_add_dashboard_widgets'))  {
	function _eleanor_add_dashboard_widgets() {
		wp_add_dashboard_widget('wp_dashboard_widget', 'Informations techniques', '_eleanor_dashboard_widget_function');
	}
}
/*----------------------------------------------------------------------*/

/* Remove some menus froms the admin area*/
if( !function_exists('_eleanor_delete_menu_items'))  {
	function _eleanor_delete_menu_items() {
	
	/*** Remove menu http://codex.wordpress.org/Function_Reference/remove_menu_page 
	syntaxe : remove_menu_page( $menu_slug )	**/
	remove_menu_page('index.php');// Dashboard
	remove_menu_page('edit.php');// Posts
	remove_menu_page('upload.php');// Media
	remove_menu_page('link-manager.php');// Links
	remove_menu_page('edit.php?post_type=page');// Pages
	remove_menu_page('edit-comments.php');// Comments
	remove_menu_page('themes.php');// Appearance
	remove_menu_page('plugins.php');// Plugins
	remove_menu_page('users.php');// Users
	remove_menu_page('tools.php');// Tools
	remove_menu_page('options-general.php');// Settings
	}
}

if( !function_exists('_eleanor_delete_submenu_page'))  {
function _eleanor_delete_submenu_page() {
	remove_submenu_page( 'edit.php', 'edit.php' ); //Menu Tous les articles
	remove_submenu_page( 'edit.php', 'post-new.php' ); //Menu Ajouter article
	remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' ); //Menu Catégorie
	remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' ); //Menu Mots-clefs
	remove_submenu_page( 'upload.php', 'media-new.php' ); //Menu Ajouter media
	remove_submenu_page( 'upload.php', 'upload.php' ); //Menu bibliotheque
	remove_submenu_page( 'edit.php?post_type=page', 'edit.php?post_type=page' ); //Menu Toutes les pages
	remove_submenu_page( 'edit.php?post_type=page', 'post-new.php?post_type=page' ); //Menu Ajouter une page
	remove_submenu_page( 'themes.php', 'themes.php' ); //Menu Themes (choisir)
	remove_submenu_page( 'themes.php', 'customize.php' ); //Menu Personnaliser theme
	remove_submenu_page( 'themes.php', 'widgets.php' ); //Menu Gestiond des widgets
	remove_submenu_page( 'themes.php', 'nav-menus.php' ); //Menu Gestion des menus
	remove_submenu_page( 'themes.php', 'theme-editor.php' ); //Menu Edition de theme
	remove_submenu_page( 'plugins.php', 'plugins.php' ); //Menu Extensions installées
	remove_submenu_page( 'plugins.php', 'plugin-install.php' ); //Menu Installer plugin
	remove_submenu_page( 'plugins.php', 'plugin-editor.php' ); //Menu Edition de plugin
	remove_submenu_page( 'users.php', 'users.php' ); //Menu Tous les utilisateurs
	remove_submenu_page( 'users.php', 'user-new.php' ); //Menu Ajouter un utilisateur
	remove_submenu_page( 'users.php', 'profile.php' ); //Menu Votre profil
	remove_submenu_page( 'tools.php', 'tools.php' ); //Menu Outils disponniles
	remove_submenu_page( 'tools.php', 'import.php' ); //Menu Outils importer
	remove_submenu_page( 'tools.php', 'export.php' ); //Menu Outils exporter
	remove_submenu_page( 'options-general.php', 'options-general.php' ); //Menu Reglages general
	remove_submenu_page( 'options-general.php', 'options-writing.php' ); //Menu Reglages ecriture
	remove_submenu_page( 'options-general.php', 'options-reading.php' ); //Menu Reglages lecture
	remove_submenu_page( 'options-general.php', 'options-discussion.php' ); //Menu Reglages discussion
	remove_submenu_page( 'options-general.php', 'options-media.php' ); //Menu Reglages medias
	remove_submenu_page( 'options-general.php', 'options-permalink.php' ); //Menu Reglages permaliens
	}
}

/*----------------------------------------------------------------------*/

/** removing parts from column ------------------------------------------*/
/* use the column id, if you need to hide more of them
syntaxe : unset($defaults['columnID']);	*/

/** remove column entries from posts **/
if( !function_exists('_eleanor_custom_post_columns'))  {
	function _eleanor_custom_post_columns($defaults) {
		unset($defaults['comments']);// comments 
		unset($defaults['author']);// authors
		unset($defaults['tags']);// tag 
		//unset($defaults['date']);// date
		//unset($defaults['categories']);// categories	
		return $defaults;
	}
}

/** remove column entries from pages **/
if( !function_exists('_eleanor_custom_pages_columns'))  {
	function _eleanor_custom_pages_columns($defaults) {
		unset($defaults['comments']);// comments 
		unset($defaults['author']);// authors
		unset($defaults['date']);	// date 
		return $defaults;
	}
}
/*-----------------------------------------------------------------------**/


/** remove widgets from the widget page ------------------------------------*/
/* Credits : http://wpmu.org/how-to-remove-default-wordpress-widgets-and-clean-up-your-widgets-page/ 
uncomment what you want to remove	*/
if( !function_exists('_eleanor_unregister_default_widgets'))  {
	 function _eleanor_unregister_default_widgets() {
		unregister_widget('WP_Widget_Pages');
		unregister_widget('WP_Widget_Calendar');
		unregister_widget('WP_Widget_Archives');
		unregister_widget('WP_Widget_Links');
		unregister_widget('WP_Widget_Meta');
		unregister_widget('WP_Widget_Search');
		unregister_widget('WP_Widget_Text');
		unregister_widget('WP_Widget_Categories');
		unregister_widget('WP_Widget_Recent_Posts');
		unregister_widget('WP_Widget_Recent_Comments');
		unregister_widget('WP_Widget_RSS');
		unregister_widget('WP_Widget_Tag_Cloud');
		unregister_widget('WP_Nav_Menu_Widget');
		unregister_widget('Twenty_Eleven_Ephemera_Widget');
		unregister_widget('Twenty_Fourteen_Ephemera_Widget');

	 }
}

/**removings items froms admin bars 
use the last part of the ID after "wp-admin-bar-" to add some menu to the list	exemple for comments : id="wp-admin-bar-comments" so the id to use is "comments"	***********/
if( !function_exists('_eleanor_remove_item_admin_bar'))  {
	function _eleanor_remove_item_admin_bar($wp_admin_bar) {
		$wp_admin_bar->remove_menu('comments'); //remove comments
		$wp_admin_bar->remove_menu('wp-logo'); //remove the whole wordpress logo, help etc part
    	$wp_admin_bar->remove_menu('about'); // A propos de WordPress
    	$wp_admin_bar->remove_menu('wporg'); // WordPress.org
    	$wp_admin_bar->remove_menu('documentation'); // Documentation
    	$wp_admin_bar->remove_menu('support-forums');  // Forum de support
    	$wp_admin_bar->remove_menu('feedback'); // Remarque
   		// $wp_admin_bar->remove_menu('site-name'); // Nom du site
    	$wp_admin_bar->remove_menu('updates'); // Icone mise à jour
		$wp_admin_bar->remove_menu('new-content'); // bouton créer		
	}
}
/*-----------------------------------------------------------------------**/

/** Supprimer items dans "ajouter media" depuis edition post ------------------------------------*/
if( !function_exists('_eleanor_custom_media_uploader'))  {
	function _eleanor_custom_media_uploader( $strings ) {
		//unset( $strings['insertMediaTitle'] ); //Insert Media
		//unset( $strings['uploadFilesTitle'] ); //Upload Files
		//unset( $strings['mediaLibraryTitle'] ); //Media Library
		unset( $strings['createGalleryTitle'] ); //Create Gallery
		//unset( $strings['setFeaturedImageTitle'] ); //Set Featured Image
		unset( $strings['insertFromUrlTitle'] ); //Insert from URL
		return $strings;
	}
}
/*-----------------------------------------------------------------------**/

/** WordPress user profil cleanups	------------------------------------*/
	if( !function_exists('_eleanor_admin_color_scheme'))  {
	function _eleanor_admin_color_scheme() {
		global $_wp_admin_css_colors;
		$_wp_admin_css_colors = 0;
	}
}
/*----------------------------------------------------------------------- **/

/** Ajouter deuxième ligne de l'editeur Tiny MCE	------------------------------------*/
if( !function_exists('_eleanor_tiny_full_editor'))  {
	function _eleanor_tiny_full_editor($in) {
		$in['wordpress_adv_hidden'] = FALSE; // activer la deuxième ligne
		$in['block_formats'] = "Paragraph=p; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6"; // Limiter le format text
		return $in;
	}
}
/*----------------------------------------------------------------------- **/


/** Ajouter deuxième ligne de l'editeur Tiny MCE	------------------------------------*/	
if( !function_exists('_eleanor_add_mime_types'))  {
	function _eleanor_add_mime_types( $mimes ){
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}
}
/*----------------------------------------------------------------------- **/

/** upprimer champ personnalisé sur post de type page et article ainsi que plusieurs metabox 	------------------------------------*/	
if( !function_exists('_eleanor_remove_custom_field_meta_boxes'))  {
	function _eleanor_remove_custom_field_meta_boxes() {
	  remove_post_type_support( 'post','custom-fields' );
	  remove_post_type_support( 'page','custom-fields' );
	  remove_meta_box('linktargetdiv', 'link', 'normal');
	  remove_meta_box('linkxfndiv', 'link', 'normal');
	  remove_meta_box('linkadvanceddiv', 'link', 'normal');
	  remove_meta_box('trackbacksdiv', 'post', 'normal');
	  remove_meta_box('commentstatusdiv', 'post', 'normal');
	  remove_meta_box('commentsdiv', 'post', 'normal');
	  remove_meta_box('revisionsdiv', 'post', 'normal');
	  remove_meta_box('authordiv', 'post', 'normal');
	  remove_meta_box('sqpt-meta-tags', 'post', 'normal');
	}
}
/*----------------------------------------------------------------------- **/

?>
