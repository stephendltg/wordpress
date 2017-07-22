<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package stephendltg
 */


/*
 * Brackets - Arguments - header
 */
function stephendltg_brackets_header(){

	return array(

	'bloginfo'           => array(
	                        'charset'       => get_bloginfo( 'charset' ),
	                        'name'          => get_bloginfo( 'name' ),
	                        'url'           => esc_url( home_url( '/' ) ),
	                        'description'   => get_bloginfo('description', 'display')
	                        ),
	'language_attributes'=> get_language_attributes(),
	'is_description'     => get_bloginfo('description', 'display') || is_customize_preview(),
	'is_home'            => is_front_page() && is_home(),
	'wp_head'            => ob_get_func('wp_head'),
	'nav_menu'           => wp_nav_menu( 
	                        array(
	                            'theme_location' => 'menu-1',
	                            'menu_id'        => 'primary-menu',
	                            'echo'           => 0 
	                         ) ) 
	);

}
// On ajoute les arguments à la fonction brackets
add_brackets( mp_transient_data('brackets-args-header', 'stephendltg_brackets_header') );





/*
 * Brackets - Arguments - sidebar
 */
if( is_active_sidebar( 'sidebar-1' ) )
	add_brackets( 'sidebar-1', ob_get_func( 'dynamic_sidebar', 'sidebar-1' ) );




/*
 * Brackets - Arguments - footer
 */
function stephendltg_brackets_footer(){

	return array(

	'wp_footer'       => ob_get_func('wp_footer'),
	'developper-link' => esc_url( __( 'https://wordpress.org/', 'stephendltg' ) ),
	'developper'      => 'WordPress',
	'theme'           => 'stephendltg',
	'designer'        => '<a href="http://stephendeletang.alwaysdata.net/">stephen deletang</a>'

	);
}
// On ajoute les arguments à la fonction brackets
add_brackets( mp_transient_data('brackets-args-footer', 'stephendltg_brackets_footer') );



/*
 * Brackets - Arguments - 404
 */
function stephendltg_brackets_404(){

	$archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'stephendltg' ), convert_smilies( ':)' ) ) . '</p>';

	return  array(

	'get_search_form'       => get_search_form( false ),
	'Widget_Recent_Posts'   => ob_get_func('the_widget', 'WP_Widget_Recent_Posts' ),
	'list_categories'       => wp_list_categories( 
		                        array(
		    					'orderby'    => 'count',
		    					'order'      => 'DESC',
		    					'show_count' => 1,
		    					'title_li'   => '',
		    					'number'     => 10,
		                        'echo'       => 0
							   ) ),
	'Widget_Archives'       => ob_get_func('the_widget', 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" ),
	'Widget_Tag_Cloud'      => ob_get_func('the_widget', 'WP_Widget_Tag_Cloud' ),
	);
}
// On déclarer les arguments du template
$args = mp_transient_data('brackets-args-404', 'stephendltg_brackets_404');




/*
 * Brackets - Partials
 */
function stephendltg_brackets_partials(){

	return array(
		'get_header'  => get_template_brackets('header'),
		'get_sidebar' => get_template_brackets('sidebar'),
		'get_footer'  => get_template_brackets('footer')
	);
}
// On déclarer les partials du template
$partials = mp_transient_data('brackets-partials', 'stephendltg_brackets_partials');



 /*
 * Brackets - Renderer
 */

echo mp_transient_data('brackets-template-404', 'brackets', 60 , array( get_template_brackets('404'), $args, $partials ) );