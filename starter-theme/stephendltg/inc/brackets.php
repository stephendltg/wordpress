<?php
/**
 * Brackets
 *
 * @package stephendltg
 */


if ( ! function_exists( 'mp_brackets' ) ) :


function _echo( $var, $var_dump = 0 ){

    if (!WP_DEBUG) return null;

    echo '<pre>';
    if($var_dump) var_dump($var);
    else print_r($var);
    echo '<pre>';

}

/*
    var :           {{ma_variable}}
    commentaire:    {{! mon  commentaire }}
    boucle if :     {{#test}} j'aime la soupe {{/test}}
    boucle for :    {{#test}} j'aime la soupe à la {{.}} {{/test}} itération automatique
    test si variable n'existe pas : {{^test}} j'aime la soupe {{/test}}
    partial:        {{>ma_variable}}
*/

function mp_brackets( $string , $args = array() , $partials = array() ){
    
    $p_args   = $args;
    $args     = wp_parse_args( $args );
    $partials = array_filter( wp_parse_args( $partials ) );
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

        // On nettoie les boucles not si varaibles existes
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

    /// On parse les patriales
    foreach ($partials as $k => $v) {
        if( is_string($v) )
            $string = preg_replace( '/[{]{2}>'.trim(json_encode($k),'"').'[}]{2}/i', mp_brackets( $v, $p_args), $string );
    }

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