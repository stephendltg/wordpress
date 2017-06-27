<?php
/*
Plugin Name: Aragorn - Rodeur SOE
Description: Améliore le référencement et le partage sur les réseaux sociaux, améliorer flux rss, fils ariane.
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

defined('ABSPATH') or die('Vous avez bien fait de venir. Vous entendrez aujourd\' hui tout ce qu\'il vous est n&eacute;cessaire de savoir pour comprendre les desseins de l\'ennemi.'); 
	


/*** Ajouter extrait au post de type page , utilisé pour meta description----------------------------------------*/
add_action( 'admin_init', create_function('', "return add_post_type_support( 'page', 'excerpt' );") );
/*----------------------------------------------------------------------*/


/*** Ajouter mot clé sur page et produits , utilisé pour meta keyword ----------------------------------------*/
add_action('init', '_aragorn_tags_support_all');
function _aragorn_tags_support_all() {  
	register_taxonomy_for_object_type('post_tag', 'page');
} 
add_action('pre_get_posts', '_aragorn_tags_support_query'); // assurer tout les mots clés dans la base de donnée
function _aragorn_tags_support_query($wp_query) {
	if ($wp_query->get('tag')) $wp_query->set('post_type', 'any');
}
/*----------------------------------------------------------------------*/


/*** AJOUTER dans balise html déclaration OPENGRAPH ----------------------------------------*/
add_filter('language_attributes', '_aragorn_custom_lang_attr');
function _aragorn_custom_lang_attr($lang) {
  $lang .=' prefix="og: http://ogp.me/ns#" ';
  return $lang;
}
/*----------------------------------------------------------------------*/


/*** DEBUT >> AJOUTER CODE APRES CHARGEMENT DU THEME ----------------------------------------*/
add_action('after_setup_theme','_aragorn_say');
function _aragorn_say() {
		
  /* -> AJOUTER GOOGLE ANALYTICS */
  // add_action('wp_head', 'async_google_analytics');
	function async_google_analytics() { ?>
		<script>
		var _gaq = [['_setAccount', 'UA-38243504-1'], ['_trackPageview']];
			(function(d, t) {
				var g = d.createElement(t),
					s = d.getElementsByTagName(t)[0];
				g.async = true;
				g.src = ('https:' == location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				s.parentNode.insertBefore(g, s);
			})(document, 'script');
		</script>
	<?php }
		
  /* -> GESTION  META DESCRIPTION ET KEYWORDS ----------------------------------------*/
	add_action('wp_head', '_aragorn_meta_data',1);
	function _aragorn_meta_data(){
		$blog_description = get_bloginfo( 'description', 'display' );
		$excerpt=str_replace ('Lire la suite', '', get_the_excerpt( ) ) ;

		if ( $excerpt &&  !is_home() && !is_archive() ) {
			echo '<meta name="description" content="' . wp_strip_all_tags( $excerpt ) . '" />'. "\n" ;
		}
		elseif ( is_archive() ) { // On utilise la description de la categorie pour la meta description.
			echo '<meta name="description" content="' . wp_strip_all_tags( category_description() ). '" />'. "\n";
		}
		else {
			echo '<meta name="description" content="' . $blog_description . '" />'. "\n" ;
		}
		if ( get_the_tags() && ( is_page() || is_single() ) ) { // On ajoute les mots clés des articles, pages
			 	$keywords ='';
		 		foreach (get_the_tags() as $tag) { $keywords.=",".$tag->name; }
				echo '<meta name="keywords" content="'.substr ($keywords,1).'" />'. "\n" ;
		 }
	}

	/* -> AJOUTER OPENGRAPH pour améliorer lisibilité reseaux sociaux */
	add_action( 'wp_head', '_aragorn_wptuts_opengraph' );
	function _aragorn_wptuts_opengraph() { 

			global $post; 
			setup_postdata( $post );
			$description=str_replace ('Lire la suite', '', get_the_excerpt( ) ) ;
			$output = '<meta property="og:locale" content="fr_FR" />' . "\n"; 
			if (is_single() || is_archive() ) { // Post type article
				$output .= '<meta property="og:type" content="article" />' . "\n";
				$output .= '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '" />' . "\n"; 
				$output .= '<meta property="og:description" content="' . wp_strip_all_tags( $description ) . '" />' . "\n";
        $output .= '<meta property="og:url" content="' . get_permalink() . '" />' . "\n";  
			 }
			if (is_home() && is_front_page() ) { // page accueil "derniers articles"
				$output .= '<meta property="og:type" content="website" />' . "\n";
				$output .= '<meta property="og:title" content="' . get_bloginfo('name') . '" />' . "\n"; 
				$output .= '<meta property="og:description" content="' . wp_strip_all_tags( get_bloginfo( 'description', 'display' ) ) . '" />' . "\n";
        $output .= '<meta property="og:url" content="' . get_bloginfo('url') . '" />' . "\n";  
  			}
 			if (!is_home() && is_front_page() ) { // page accueil "page statique"
				$output .= '<meta property="og:type" content="website" />' . "\n";
				$output .= '<meta property="og:title" content="' . get_bloginfo('name') . '" />' . "\n"; 
				$output .= '<meta property="og:description" content="' . wp_strip_all_tags( $description ) . '" />' . "\n";
        $output .= '<meta property="og:url" content="' . get_permalink() . '" />' . "\n";  
  			}			
  		if (is_page() && !(is_front_page() ||is_home() ) ) { // Post de type page
				$output .= '<meta property="og:type" content="page" />' . "\n";  
				$output .= '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '" />' . "\n"; 
				$output .= '<meta property="og:description" content="' . wp_strip_all_tags( $description ) . '" />' . "\n";
        $output .= '<meta property="og:url" content="' . get_permalink() . '" />' . "\n"; 
			}
			$output .= '<meta property="og:site_name" content="'.get_bloginfo('name').'" />'."\n"; 
			if (is_single() ) { 
				$output .= '<meta property="article:published_time" content="' . get_the_time('c') . '" />' . "\n";
				$output .= '<meta property="article:modified_time" content="' . get_the_modified_time('c') . '" />' . "\n";
				$output .= '<meta property="article:author" content="' . get_the_author() . '" />' . "\n";
        $output .= '<meta property="article:section" content="' . get_the_category()[0]->cat_name . '" />' . "\n";
				$output .= '<meta property="og:updated_time" content="'. get_the_modified_time('c') .'" />'."\n"; 
				$output .= '<meta name="twitter:card" content="summary" />'."\n"; 
				$output .= '<meta name="twitter:site" content="@'. get_bloginfo('name') .'">'."\n"; 
				$output .= '<meta name="twitter:creator" content="@'. get_the_author() .'" />'."\n"; 
				$output .= '<meta name="twitter:title" content="'. esc_attr( get_the_title() ) .'" />'."\n"; 
				$output .= '<meta name="twitter:description" content="'. wp_strip_all_tags( $description ).'" />'."\n"; 
			 }
			if ( has_post_thumbnail() ) { 
				$imgsrc = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' ); 
				$output .= '<meta property="og:image" content="' . $imgsrc[0] . '" />' . "\n"; 
				$output .= '<meta name="twitter:image" content="' . $imgsrc[0] . '" />' . "\n"; 
			} 
			echo $output; 
	}
}
/*** FIN >> AJOUTER CODE APRES CHARGEMENT DU THEME ----------------------------------------*/



/**  FLUX RSS : Associé une seule categorie aux articles dans flux RSS ------------------------------------------------------------------------------------------*/
add_filter('the_category_rss', '_aragorn_xmlrsscategoryun');
function _aragorn_xmlrsscategoryun($type = null) {
  if ( empty($type) )$type = get_default_feed();
  $categories = get_the_category();
  $thelist = '';
  $cat_names = array();
  $filter = 'rss';
  if ( 'atom' == $type ) 
    $filter = 'raw';
  if ( !empty($categories) ) foreach ( (array) $categories as $category ) 
    { $cat_names[] = sanitize_term_field('name', $category->name, $category->term_id, 'category', $filter); }
  $cat_names = array($cat_names[0]);$cat_names = array_unique($cat_names);
  foreach ( $cat_names as $cat_name ) {
    if ( 'rdf' == $type )
      $thelist .= "\t\t<dc:subject><![CDATA[$cat_name]]></dc:subject>\n";
    elseif ( 'atom' == $type )
      $thelist .= sprintf( '<category scheme="%1$s" term="%2$s" />', esc_attr( apply_filters( 'get_bloginfo_rss', get_bloginfo( 'url' ) ) ), esc_attr( $cat_name ) );
    else
      $thelist .= "\t\t<category><![CDATA[" . @html_entity_decode( $cat_name, ENT_COMPAT, get_option('blog_charset') ) . "]]></category>\n";}
return $thelist;
}


/**  GESTION FILS ARIANNE ------------------------------------------------------------------------------------------
 * <?php if (function_exists('_aragorn_content_breadcrumb')) _aragorn_content_breadcrumb();?> shortcode a mettre dans theme
 */
  // Get parent categories with schema.org data
  function _aragorn_content_get_category_parents($id, $link = false,$separator = '/',$nicename = false,$visited = array()) {
  $final = '';
  $parent = get_category($id);
  if (is_wp_error($parent))
    return $parent;
  if ($nicename)
    $name = $parent->name;
  else
    $name = $parent->cat_name;
  if ($parent->parent && ($parent->parent != $parent->term_id ) && !in_array($parent->parent, $visited)) {
    $visited[] = $parent->parent;
    $final .= _aragorn_content_get_category_parents( $parent->parent, $link, $separator, $nicename, $visited );
  }
  if ($link)
    $final .= '<span typeof="v:Breadcrumb"><a href="' . get_category_link( $parent->term_id ) . '" title="Voir tous les articles de '.$parent->cat_name.'" rel="v:url" property="v:title">'.$name.'</a></span>' . $separator;
  else
    $final .= $name.$separator;
  return $final;}

  // Breadcrumb
  function _aragorn_content_breadcrumb() {
  // Global vars
  global $wp_query;
  $paged = get_query_var('paged');
  $sep = ' » ';
  $data = '<span typeof="v:Breadcrumb">';
  $dataend = '</span>';
  $final = '<div xmlns:v="http://rdf.data-vocabulary.org/#">';  
  $startdefault = $data.'<a title="'. get_bloginfo('name') .'" href="'.home_url().'" rel="v:url" property="v:title">'. get_bloginfo('name') .'</a>'.$dataend;
  $starthome = 'Accueil de '. get_bloginfo('name');

  // Breadcrumb start
  if ( is_front_page() && is_home() ){
    // Default homepage
    if ( $paged >= 1 )    
      $final .= $startdefault;
    else
      $final .= $starthome;
  } elseif ( is_front_page() ){
    //Static homepage
    $final .= $starthome;
  } elseif ( is_home() ){
    //Blog page
    if ( $paged >= 1 ) {   
      $url = get_page_link(get_option('page_for_posts'));  
      $final .= $startdefault.$sep.$data.'<a href="'.$url.'" rel="v:url" property="v:title" title="Les articles">Les articles</a>'.$dataend;}
    else
      $final .= $startdefault.$sep.'Les articles';
  } else {
    //everyting else
    $final .= $startdefault.$sep;}

  // Prevent other code to interfer with static front page et blog page
  if ( is_front_page() && is_home() ){// Default homepage
  } elseif ( is_front_page()){//Static homepage
  } elseif ( is_home()){//Blog page
  }
  //Attachment
  elseif ( is_attachment()){
    global $post;
    $parent = get_post($post->post_parent);
    $id = $parent->ID;
    $category = get_the_category($id);
    $category_id = get_cat_ID( $category[0]->cat_name );
    $permalink = get_permalink( $id );
    $title = $parent->post_title;
    $final .= _aragorn_content_get_category_parents($category_id,TRUE,$sep).$data."<a href='$permalink' rel='v:url' property='v:title' title='$title'>$title</a>".$dataend.$sep.the_title('','',FALSE);}
  // Post type
  elseif ( is_single() && !is_singular('post')){
     global $post;
     $nom = get_post_type($post);
     $archive = get_post_type_archive_link($nom);
     $mypost = $post->post_title;
     $final .= $data.'<a href="'.$archive.'" rel="v:url" property="v:title" title="'.$nom.'">'.$nom.'</a>'.$dataend.$sep.$mypost;}
  //post
  elseif ( is_single()){
    // Post categories
    $category = get_the_category();
    $category_id = get_cat_ID( $category[0]->cat_name );
    if ($category_id != 0)
      $final .= _aragorn_content_get_category_parents($category_id,TRUE,$sep);
    elseif ($category_id == 0) {
      $post_type = get_post_type();
      $tata = get_post_type_object( $post_type );
      $titrearchive = $tata->labels->menu_name;
      $urlarchive = get_post_type_archive_link( $post_type );
      $final .= $data.'<a class="breadl" href="'.$urlarchive.'" title="'.$titrearchive.'" rel="v:url" property="v:title">'.$titrearchive.'</a>'.$dataend;}
    // With Comments pages
    $cpage = get_query_var( 'cpage' );
    if (is_single() && $cpage > 0) {
      global $post;
      $permalink = get_permalink( $post->ID );
      $title = $post->post_title;
      $final .= $data."<a href='$permalink' rel='v:url' property='v:title' title='$title'>$title</a>".$dataend;
      $final .= $sep."Commentaires page $cpage";}
    // Without Comments pages
    else
      $final .= the_title('','',FALSE);}
  // Categories
  elseif ( is_category() ) {
    // Vars
    $categoryid       = $GLOBALS['cat'];
    $category         = get_category($categoryid);
    $categoryparent   = get_category($category->parent);
    //Render
    if ($category->parent != 0) 
      $final .= _aragorn_content_get_category_parents($categoryparent, true, $sep, true);
    if ( $paged <= 1 )
      $final .= single_cat_title("", false);
    else
      $final .= $data.'<a href="' . get_category_link( $category ) . '" title="Voir tous les articles de '.single_cat_title("", false).'" rel="v:url" property="v:title">'.single_cat_title("", false).'</a>'.$dataend;}
  // Page
  elseif ( is_page() && !is_home() ) {
    $post = $wp_query->get_queried_object();
    // Simple page
    if ( $post->post_parent == 0 )
      $final .= the_title('','',FALSE);
    // Page with ancestors
    elseif ( $post->post_parent != 0 ) {
      $title = the_title('','',FALSE);
      $ancestors = array_reverse(get_post_ancestors($post->ID));
      array_push($ancestors, $post->ID);
      $count = count ($ancestors);$i=0;
      foreach ( $ancestors as $ancestor ){
        if( $ancestor != end($ancestors) ){
          $name = strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) );
          $final .= $data.'<a title="'.$name.'" href="'. get_permalink($ancestor) .'" rel="v:url" property="v:title">'.$name.'</a>'.$dataend;
          $i++;
          if ($i < $ancestors)
            $final .= $sep;
        }
        else 
          $final .= strip_tags(apply_filters('single_post_title',get_the_title($ancestor)));
        }}}
  // authors
  elseif ( is_author() ) {
    if(get_query_var('author_name'))
        $curauth = get_user_by('slug', get_query_var('author_name'));
    else
        $curauth = get_userdata(get_query_var('author'));
    $final .= "Articles de l'auteur ".$curauth->nickname;}  
  // tags
  elseif ( is_tag() ){
    $final .= "Articles sur le thème ".single_tag_title("",FALSE);}
  // Search
  elseif ( is_search() ) {
    $final .= "Résultats de votre recherche sur \"".get_search_query()."\"";}    
  // Dates
  elseif ( is_date() ) {
    if ( is_day() ) {
      $year = get_year_link('');
      $final .= $data.'<a title="'.get_query_var("year").'" href="'.$year.'" rel="v:url" property="v:title">'.get_query_var("year").'</a>'.$dataend;
      $month = get_month_link( get_query_var('year'), get_query_var('monthnum') );
      $final .= $sep.$data.'<a title="'.single_month_title(' ',false).'" href="'.$month.'" rel="v:url" property="v:title">'.single_month_title(' ',false).'</a>'.$dataend;
      $final .= $sep."Archives pour ".get_the_date();}
    elseif ( is_month() ) {
      $year = get_year_link('');
      $final .= $data.'<a title="'.get_query_var("year").'" href="'.$year.'" rel="v:url" property="v:title">'.get_query_var("year").'</a>'.$dataend;
      $final .= $sep."Archives pour ".single_month_title(' ',false);}
    elseif ( is_year() )
      $final .= "Archives pour ".get_query_var('year');}
  // 404 page
  elseif ( is_404())
    $final .= "404 Page non trouvée";
  // Other Archives
  elseif ( is_archive() ){
    $posttype = get_post_type();
    $posttypeobject = get_post_type_object( $posttype );
    $taxonomie = get_taxonomy( get_query_var( 'taxonomy' ) );
    $titrearchive = $posttypeobject->labels->menu_name;
    if (!empty($taxonomie))
      $final .= $taxonomie->labels->name;
    else
      $final .= $titrearchive;}
  // Pagination
  if ( $paged >= 1 )
    $final .= $sep.'Page '.$paged;
  // The End
  $final .= '</div>';
  echo $final;}





?>
