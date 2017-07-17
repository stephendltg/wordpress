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
add_brackets( 'bloginfo'           , array(
                                    'charset'       => get_bloginfo( 'charset' ),
                                    'name'          => get_bloginfo( 'name' ),
                                    'url'           => esc_url( home_url( '/' ) ),
                                    'description'   => get_bloginfo('description', 'display')
                                    ) 
);
add_brackets( 'language_attributes', get_language_attributes() );
add_brackets( 'is_description'     , get_bloginfo('description', 'display') || is_customize_preview() );
add_brackets( 'is_home'            , is_front_page() && is_home() );
add_brackets( 'wp_head'            , ob_get_func('wp_head') );
add_brackets( 'nav_menu'           , wp_nav_menu( 
                                        array(
                                            'theme_location' => 'menu-1',
                                            'menu_id'        => 'primary-menu',
                                            'echo'           => 0 
                                        ) ) 
);


/*
 * Brackets - Arguments - header
 */
add_brackets( 'wp_footer'       , ob_get_func('wp_footer') );
add_brackets( 'developper-link' , esc_url( __( 'https://wordpress.org/', 'stephendltg' ) ) );
add_brackets( 'developper'      , 'WordPress' );
add_brackets( 'theme'           , 'stephendltg' );
add_brackets( 'designer'        , '<a href="http://stephendeletang.alwaysdata.net/">stephen deletang</a>' );

/*
 * Brackets - Arguments - 404
 */
$archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'stephendltg' ), convert_smilies( ':)' ) ) . '</p>';

$args = array(

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



/*
 * Brackets - Partials
 */
$partials = array(
    'get_header' => get_template_brackets('header'),
    'get_footer' => get_template_brackets('footer'),
);

 /*
 * Brackets - Renderer
 */
get_brackets( get_template_brackets('404'), $args, $partials );