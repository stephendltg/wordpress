<?php

/*
Plugin Name: Dwalin - Quand Dwalin attaque, il reduit votre menace.
Description: Minifier html, balise style et script afin d'optimiser le temps de chargement des pages.
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

defined('ABSPATH') or die('C\'&eacute;tait un Nain avec une barbe bleue pass&eacute;e dans une ceinture dor&eacute;e et des yeux brillants sous son capuchon vert fonc&eacute;.'); 

class _Dwalin_Compression {

    // Paramètres
    protected $compress_css = true;
    protected $compress_js = true;
    protected $info_comment = false;
    protected $remove_comments = true;
 
    // Variables
    protected $html;
        
    public function __construct($html) { if (!empty($html)) { $this->_Dwalin_parseHTML($html); } }
    public function __toString() { return $this->html; }
        
    protected function _Dwalin_bottomComment($raw, $compressed) {
        $raw = strlen($raw);
        $compressed = strlen($compressed);
        $savings = ($raw-$compressed) / $raw * 100;
        $savings = round($savings, 2);
        return '<!--HTML compressed, size saved '.$savings.'%. From '.$raw.' bytes, now '.$compressed.' bytes-->';
    }
        
    protected function _Dwalin_minifyHTML($html) {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $overriding = false;
        $raw_tag = false;
        // Variable reused for output
        $html = '';
        foreach ($matches as $token) {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
            $content = $token[0];
                if (is_null($tag)) {
                    if ( !empty($token['script']) ) { $strip = $this->compress_js; }
                    else if ( !empty($token['style']) ) { $strip = $this->compress_css; }
                    else if ($content == '<!--wp-html-compression no compression-->') {
                       	$overriding = !$overriding;
                        // Don't print the comment
                        continue;
                    }
                    else if ($this->remove_comments) {
                        if (!$overriding && $raw_tag != 'textarea') {
                            // Remove any HTML comments, except MSIE conditional comments
                            $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                        }
                    }
                }
                else {
                    if ($tag == 'pre' || $tag == 'textarea') { $raw_tag = $tag; }
                    else if ($tag == '/pre' || $tag == '/textarea') { $raw_tag = false; }
                    else { 
                        if ($raw_tag || $overriding) {  $strip = false; }
                        else {
                            $strip = true; 
                            // Remove any empty attributes, except:
                            // action, alt, content, src
                            $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                            // Remove any space before the end of self-closing XHTML tags
                            // JavaScript excluded
                            $content = str_replace(' />', '/>', $content);
                        }
                    }
                 }
                if ($strip) { $content = $this->_Dwalin_removeWhiteSpace($content); }
                    $html .= $content;
        }
        return $html;
    }
               
    public function _Dwalin_parseHTML($html) {
        $this->html = $this->_Dwalin_minifyHTML($html);  
        if ($this->info_comment) { $this->html .= "\n" . $this->_Dwalin_bottomComment($html, $this->html); }
    }
        
    protected function _Dwalin_removeWhiteSpace($str) {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n",  '', $str);
        $str = str_replace("\r",  '', $str);
        while (stristr($str, '  ')) { $str = str_replace('  ', ' ', $str); }
        return $str;
    }
}
 
function _Dwalin_compression_finish($html) {  return new _Dwalin_Compression($html); }
function _Dwalin_compression_start() { ob_start('_Dwalin_compression_finish'); }
add_action('get_header', '_Dwalin_compression_start');