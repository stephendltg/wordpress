<?php
/**
 * Brackets
 *
 * @package stephendltg
 */




/**
 * Met la sortie echo d'une fonction dans une variable tampon
 *
 *
 * @param  string $function     Nom de la fonction
 * @param  $args                parametres de la fonction appelé
 * @return string               Tampon de sortie
 */
if ( ! function_exists( 'ob_get_func' ) ) :

function ob_get_func( $function = '' ){

    $function = (string) $function;
    $params   = array_slice(func_get_args(),1);

    if( !is_callable($function) )
        return;

    ob_start();
    call_user_func_array( $function, $params);
    return ob_get_clean();
}
endif;


/**
 * Afficheur en mode debug
 *
 *
 */
if ( ! function_exists( '_echo' ) ) :

function _echo( $var, $var_dump = 0 ){

    if (!WP_DEBUG) return null;

    echo '<pre>';
    if($var_dump) var_dump($var);
    else print_r($var);
    echo '<pre>';

}
endif;



/**
 * Enregistrer, récupérer ou supprimer une donnée statique.
 * Get:   Mettre juste la clé recherche en parametre
 * Set:   Mettre un second parametres avec la valeur de la clé
 * Delete: Mettre la valeur : null en second paramètres pour supprimer la clé
 */
if ( ! function_exists( 'mp_cache_data' ) ) :

function mp_cache_data( $key ) {

    static $data = array();

    $func_get_args = func_get_args();

    if ( array_key_exists( 1, $func_get_args ) ) {

        if ( null === $func_get_args[1] )
            unset( $data[ $key ] );
        else
            $data[ $key ] = $func_get_args[1];
    }

    return isset( $data[ $key ] ) ? $data[ $key ] : null;
}
endif;



/**
 * transient data
 */
if ( ! function_exists( 'mp_transient_data' ) ) :

function mp_transient_data( $transient , $function, $expiration = 60 ){

    $transient  = (string) $transient;
    $expiration = (int) $expiration;   

    if ( false === ( $transient = get_transient( $name ) ) ) {
        set_transient( $name, call_user_func($function), $time );
        $transient = get_transient( $name );
    }
    return $transient; 
}
endif;


/**
 * Parser brackets
 *
 */
if ( ! function_exists( 'brackets' ) ) :


/**
 * Lecture du template brackets
 *
 *
 * @param  string $name     Nom du template
 * @return string           Contenu du template
 */
function get_template_brackets( $name = 'index' ){

    $name = (string) $name;

    $template = current( glob(get_template_directory() . '/templates/'.$name.'.html') );
    $template = apply_filters('mp_template_brackets', $template, $name);

    if(!$template)
        return false;

    return  @file_get_contents( $template );
}

/**
 * Ajouter brackets
 *
 * @param (string)     $key clé d'identification du brackets. 
 * @param (everything) $value valeur de la clé. (si valeur null non prit en compte par le parser.)
 *
 * @return
 */
function add_brackets( $key , $value ) {

    if( !is_array($key) )
        $key = array( $key => $value );

    if( null === mp_cache_data( 'brackets') )
        mp_cache_data( 'brackets', $key );
    else
        mp_cache_data( 'brackets', array_merge( mp_cache_data( 'brackets') , $key ) );
}


/**
 * Ajouter partiales brackets
 *
 * @param (string)     $key clé d'identification du brackets. 
 * @param (everything) $value valeur de la clé. (si valeur null non prit en compte par le parser.)
 *
 * @return
 */
function add_partiales( $key , $value ) {

    if( !is_array($key) )
        $key = array( $key => $value );

    if( null === mp_cache_data( 'brackets') )
        mp_cache_data( 'partiales', array($key) );
    else
        mp_cache_data( 'partiales', array_merge( mp_cache_data('partiales') , array($key) ) );
}


/**
 * display brackets
 * @return echo
 */
function get_brackets( $string, $args, $partials ){

    echo apply_filters( 'the_brackets', brackets( $string, $args, $partials ) );
}


/**
 * Parser de template brackets
 *
 *
 * @param  string $string     Contenu d'un template
 * @param  string $args       Variable d'arguments à parser
 * @param  string $partiales  Morceaux de template
 * @param  string $display    echo ou string
 * @return string/echo        Template parser
 */
/*
    var :           {{ma_variable}}
    commentaire:    {{! mon  commentaire }}
    boucle if :     {{#test}} j'aime la soupe {{/test}}
    boucle for :    {{#test}} j'aime la soupe à la {{.}} {{/test}} itération automatique
    test si variable n'existe pas : {{^test}} j'aime la soupe {{/test}}
    partial:        {{>ma_variable}}
*/
function brackets( $string , $args = array() , $partials = array()  ){

    $args     = wp_parse_args($args, mp_cache_data('brackets') );
    $partials = array_filter( wp_parse_args( $partials, mp_cache_data('partiales') ) );
    $vars     = array();

    // init table des boucles
    $args_array = array();

    // On prépare la table des boucles ainsi que celle des variables
    foreach ($args as $key => $value) {

        if ( is_array( $value ) ){

            // On nettoie pour que seul les tableaux non multi dimenssionnel soit utilisé et filtre les valeurs ( null, '', false )
            $value = array_filter( array_map(function($value){return !is_array($value)?$value:null;}, $value ) );

            // On construit la table des arguments
            foreach ($value as $k => $v){
                $vars['/[{]{2}'. trim( json_encode($key. '.' .$k), '"') .'[}]{2}/i'] = $v;
                $args[$key.'.'.$k] = $v;
            }

            // On créer un tableau à scruter
            $args[$key] = $value;

        } else {
            $vars['/[{]{2}'. trim(json_encode($key),'"') .'[}]{2}/i'] = $value;
        }
    }

    // on filtre les valeurs ( null, '', false ) des arguments
    $vars = array_filter($vars);
    $args = array_filter($args);

    // On scrute les boucles foreach
    foreach ( $args as $key => $value) {

        $key = trim( json_encode($key), '"');

        // On nettoie les boucles not si variables existes
        $vars['/[\s]*[{]{2}[\^]'.$key.'[}]{2}(.*?)[{]{2}[\/]'.$key.'[}]{2}/si'] = ''; 

        preg_match_all( '/[{]{2}[#]'.$key.'[}]{2}(.*?)[{]{2}[\/]'.$key.'[}]{2}/si', $string, $matches, PREG_SET_ORDER );

        foreach ($matches as $match) {

            $result = '';

            if( is_array($args[$key]) ){

                foreach ($args[$key] as $value) {
                    $temp = preg_replace('/[{]{2}[.][}]{2}/i', $value, ltrim($match[1]), -1, $count );
                    $result .= ($count == 0) ? '' : $temp;
                }

            } else {
                $result = $match[1];
            } 

            // On remplace le contenu par le resultat du parsage de variable
            $string = str_replace($match[0], trim($result), $string);
        }
    }

    /// On parse les partiales
    foreach ($partials as $k => $v) {
        if( is_string($v) )
            $string = preg_replace( '/[{]{2}>'.trim(json_encode($k),'"').'[}]{2}/i', brackets( $v, $args), $string );
    }

    // On filtre les traductions
    preg_match_all( '/[{]{2}\@(.*?)\@[}]{2}/i', $string, $matches );
    // On traduit le texte ($matches[1]) selon le domaine ($matches[2])
    $matches[1] = array_map( function($v){ return esc_html__( trim($v) , 'stephendltg' ); } , $matches[1] );
    $string     = str_replace( $matches[0], $matches[1], $string );

    // On nettoie les commentaires
    $vars['/[\s]*[{]{2}!([^{]*)[}]{2}/'] = '';
    // Ajour Regex pour supprimer toutes les boucles non utilisé
    $vars['/[\s]*[{]{2}[#](.*?)[}]{2}(.*?)[{]{2}[\/](.*?)[}]{2}/si'] = '';
    // Ajour Regex pour supprimer tous les brackets sans arguments
    $vars['/[{]{2}[\w. \/^]*[}]{2}/'] = '';


    // On parse les variables
    $string = preg_replace(array_keys($vars), $vars, $string);

    return trim($string);
}
endif;