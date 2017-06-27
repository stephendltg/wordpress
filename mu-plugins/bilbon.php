<?php /*
Plugin Name: Bilbon - Il faut un voleur à cette quête !
Description: Cache les clés de sécurités de wordpress
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/
defined('ABSPATH') or die('Vous apprendrez que vos difficult&eacute;s ne sont que parties des difficult&eacute;s de tout le monde occidental.'); 

strtolower(basename(__FILE__))!='index.php' && strtolower(basename(__FILE__))!='alicia.php'
or wp_die('<p>You have to rename this file before continuing because its name is not secure:</p>'.
'<p>'.trailingslashit(dirname(__FILE__)).'<b>'.basename(__FILE__).'</b></p>'.
'<p>Try this one: <input value="'.uniqid('baw-keys-').'.php" size="30"/><p>');
if(realpath(dirname(__FILE__))!=realpath(WPMU_PLUGIN_DIR))
wp_die('<p>This is not a <i>plugin</i> but a <i>mu-plugins</i>, please drop it in :<br/>' .
'<b>'.realpath(WPMU_PLUGIN_DIR).'</b><br />Thanks.</p>' );
$CP='QxhO%n(HVBl(R!$P4wT)wmYnj$eKTV8p';$KP='(&4$k3B5kM41CXxna&mwj@Kt4O3EqSTo';
$MK=__FILE__.date('Ym').$GLOBALS['blog_id'].get_site_option('siteurl');$CP.=$KP;$MK.=$KP;
$U='_';$KS=array('KEY','SALT');$KZ=array('AUTH','SECURE_AUTH','LOGGED_IN','NONCE','SECRET');
foreach($KS as $_KS)foreach($KZ as $_KZ)if(!defined($_KZ.$U.$_KS))
define($_KZ.$U.$_KS,md5('BAW'.$_KZ.$_KS.md5($MK).$MK).md5($_KZ.$_KS.$MK));else 
wp_die('<b>'.$_KZ.$U.$_KS.'</b> is already defined, please delete/comment all secret keys in your <i>wp-config.php</i> file.');
define('COOKIEHASH',md5('BAWCOOKIEHASH'.md5($MK.$CP).$MK.$CP).md5('BAWCOOKIEHASH'.$MK.$CP));
unset($U,$MK,$_KZ,$_KS,$KZ,$KS,$CP,$KP);

?>