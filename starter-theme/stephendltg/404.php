<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package stephendltg
 */


// On lance le tampon de sortie pour récupérer les fonctionalités de wordpress
ob_start();

/*
 * WP_HEAD
 */
wp_head();
$wp_head = ob_get_contents();
ob_clean();

/*
 * WP_FOOTER
 */
wp_footer();
$wp_footer = ob_get_contents();
ob_clean();

/*
 * WP_Widget_Recent_Posts
 */
the_widget( 'WP_Widget_Recent_Posts' );
$Widget_Recent_Posts = ob_get_contents();
ob_clean();

/*
 * WP_Widget_Archives
 */
$archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'stephendltg' ), convert_smilies( ':)' ) ) . '</p>';
the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );
$Widget_Archives = ob_get_contents();
ob_clean();
unset($archive_content);


/*
 * WP_Widget_Tag_Cloud
 */
the_widget( 'WP_Widget_Tag_Cloud' );
$Widget_Tag_Cloud = ob_get_contents();
ob_clean();



/*
 * Brackets - Arguments
 */
$args = array(
    'language_attributes'   => get_language_attributes(),
    'bloginfo'              => array(
                            'charset'       => get_bloginfo( 'charset' ),
                            'name'          => get_bloginfo( 'name' ),
                            'url'           => esc_url( home_url( '/' ) ),
                            'description'   => get_bloginfo('description', 'display'),
                            ),
    
    'is_description'        => get_bloginfo('description', 'display') || is_customize_preview(),
    'is_home'               => is_front_page() && is_home(),
    'wp_head'               => $wp_head,
    'wp_footer'             => $wp_footer,
    'body_class'            => 'class="'. join( ' ', get_body_class() ) .'"',
    'get_search_form'       => get_search_form( false ),
    'Widget_Recent_Posts'   => $Widget_Recent_Posts,
    'list_categories'       => wp_list_categories( array(
								'orderby'    => 'count',
								'order'      => 'DESC',
								'show_count' => 1,
								'title_li'   => '',
								'number'     => 10,
                                'echo'       => 0
							) ),
    'Widget_Archives'       => $Widget_Archives,
    'Widget_Tag_Cloud'      => $Widget_Tag_Cloud,
    'nav_menu'              => wp_nav_menu( array(
                                'theme_location' => 'menu-1',
                                'menu_id'        => 'primary-menu',
                                'echo'           => 0
                            ) ),
    // Traductions
    '_'   => array(
            'Skip to content' => esc_html( 'Skip to content', 'stephendltg' ),
            'Primary Menu' => esc_html( 'Primary Menu', 'stephendltg' ),
            'page-title'   => esc_html( 'Oops! That page can&rsquo;t be found.', 'stephendltg' ),
            'page-content' => esc_html( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'stephendltg' ),
            'widget-title' => esc_html( 'Most Used Categories', 'stephendltg' ),
            'footer-link'  => esc_url( __( 'https://wordpress.org/', 'stephendltg' ) ),
            'footer-power' => sprintf( esc_html__( 'Proudly powered by %s', 'stephendltg' ), 'WordPress' ),
            'footer-author'=> sprintf( esc_html__( 'Theme: %1$s by %2$s.', 'stephendltg' ), 'stephendltg', '<a href="https://automattic.com/">stephen deletang</a>' )
            )
);


/*
 * Brackets - Partials
 */
$partials = array(
    'get_header' => @file_get_contents( get_template_directory() . '/templates/header.html'),
    'get_footer' => @file_get_contents( get_template_directory() . '/templates/footer.html'),
);


/*
 * Brackets - Render
 */
$args     = apply_filters('{{404_args}}', $args);
$template = apply_filters('{{404_template}}', get_template_directory() . '/templates/404.html');
$template = @file_get_contents( $template );
$error    = apply_filters( '{{the_404}}', mp_brackets( $template, $args, $partials ) );

/*if( strlen(404) == 0 )*/    

echo $error;