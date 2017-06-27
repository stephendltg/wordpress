<?php
/*
Plugin Name: Galadrielle
Description: Améliore le référencement et le partage sur les réseaux sociaux..
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

add_action( 'admin_menu', 'MaintenanceSettings' );

function MaintenanceSettings( )
{
	add_menu_page(
		'maintenance', // le titre de la page
		'Maintenance',            // le nom de la page dans le menu d'admin
		'manage_options',        // le rôle d'utilisateur requis pour voir cette page
		'maintenance-page',        // un identifiant unique de la page
		'MaintenanceSettingsPage'   // le nom d'une fonction qui affichera la page
	);
}

function MaintenanceSettingsPage( ) {
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $hidden_field_name_1 = 'mt_submit_hidden_1';
    $hidden_field_name_2 = 'mt_submit_hidden_2';
    $hidden_field_name_3 = 'mt_submit_hidden_3';
    $hidden_field_name_4 = 'mt_submit_hidden_4';
    $hidden_field_name_5 = 'mt_submit_hidden_5';

    // Validation form 1
    if( isset($_POST[ $hidden_field_name_1 ]) && $_POST[ $hidden_field_name_1 ] == 'Y' ) {
    	if( function_exists('wpmc_clean_domain_cache'))  {
			wpmc_clean_domain_cache() ;
			 echo '<div class="updated"><p><strong>Cache du site, purger ...</strong></p></div>';
		}
		else {
			echo '<div class="error"><p><strong>Erreur lors de la purge du site ...</strong></p></div>';
		}		
    }
    
    // Validation form 2
     if( isset($_POST[ $hidden_field_name_2 ]) && $_POST[ $hidden_field_name_2 ] == 'Y' ) {
     	global $wpdb;
     	function cache_constructor ($url) {
			$ch = curl_init($url);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data2= curl_exec($ch);
    		$info= curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		curl_close($ch);
    		
    		if($info === 200){
    			/* on gére seulement ce qui réponds */
				@mkdir( ABSPATH.'cache/'.str_replace( 'http://', '', $url ), 0755, true ); //home_url( '/' )
    			file_put_contents( ABSPATH.'cache/'.str_replace( 'http://', '', $url ).'/index.html', $data2 );
    		}
     	}
     	
     	/* Gestion des posts et pages du site */
     	$link_page_and_product = $wpdb->get_results( 
			"
				SELECT ID, post_type 
				FROM $wpdb->posts
				WHERE post_type = 'page' OR post_type ='wpshop_product'
				ORDER BY ID
			"
		);
		foreach ( $link_page_and_product as $link_page_and_product ) { 
			$id = $link_page_and_product->ID;
			/*gerer les exceptions */
			switch ($id)
			{
				case 26	:
					break;
				case 27:
					break;
				case 28:
					break;
				case 29:
					break;
				case 30:
					break;
				case 7:
					break;
				case 8:
					break;
				default:
					cache_constructor ( get_permalink($id) );
			}
		 }

		/* Gestion des categories avec gestion des slug */
		$link_category = $wpdb->get_results( 
			"
				SELECT slug
				FROM $wpdb->terms
			"
		);
		foreach ( $link_category as $link_category ) { 
			$url_category = home_url().'/boutique/'.$link_category->slug.'/' ;
			cache_constructor ( $url_category );
		 }
		echo '<div class="updated"><p><strong>Reconstruction du cache, Fait ...</strong></p></div>';
    }
    
     // Validation form 3
     if( isset($_POST[ $hidden_field_name_3 ]) && $_POST[ $hidden_field_name_3 ] == 'Y' ) {
     	if( function_exists('do_backup_website'))  {
			do_backup_website();
			echo '<div class="updated"><p><strong>Sauvegarde du répertoire, fait ...</strong></p></div>';	
		}
		else {
			echo '<div class="error"><p><strong>Sauvegarde du répertoire, Erreur ! ...</strong></p></div>';
		}				
    }
    
     // Validation form 4
     if( isset($_POST[ $hidden_field_name_4 ]) && $_POST[ $hidden_field_name_4 ] == 'Y' ) {
     	if( function_exists('do_backup_bdd'))  {
			do_backup_bdd() ;
			echo '<div class="updated"><p><strong>Sauvegarde de la base donnée, Fait ...</strong></p></div>';
		}
		else {
			echo '<div class="error"><p><strong>Sauvegarde de la base donnée, Erreur ! ...</strong></p></div>';
		}
    }
    
     // Validation form 5
     if( isset($_POST[ $hidden_field_name_5 ]) && $_POST[ $hidden_field_name_5 ] == 'Y' ) {
     	global $wpdb;
		$all_tables = $wpdb->get_results('SHOW TABLES',ARRAY_A);
		foreach ($all_tables as $tables)
		{
			$table = array_values($tables);
			$wpdb->query("OPTIMIZE TABLE ".$table[0]);
		}
		echo '<div class="updated"><p><strong>Optimisation de la table, Fait ...</strong></p></div>';
    }
   

    // Affichage de la page 

    echo '<div class="wrap">';

    // header

    echo "<h2>Maintenance du site</h2>";
    
    ?>
  <style>
  	h4, p {
  			text-align: justify;
  			margin-right: 20px;
  		}
  	table {border-collapse:collapse;}
  	td { padding : 10px; border: 1px solid #ddd;}
  	tr, td {vertical-align: center;}
  	.submit{ margin-left: 20px; }
  	.maintenance-cadre { margin-left: 20px;}
  </style> 
   
<br>
<hr/>
<h3>Option du cache statique:</h3>
<div class="maintenance-cadre">
	<h4>Le fonctionnement du cache de votre site:</h4>
	<blockquote><strong>"</strong>Le cache statique est générer par le premier internaute qui entre sur votre site et se construit à chaque page qui est visitée. Le cache se regénère automatiquement en cas de changement de produit, de pages ou de commentaires ( La page d'accueil ainsi que les pages de catégorie de produits sont purger chaque jour ). Les options suivantes permettent de gérer ce cache dans un cas particulier non pris en charge ou d'un bug de celui-ci. A ce jour, seul le cas de rupture de stock d'un produit n'est pas prit en charge, pour pallier à ceci, il suffit juste d'aller sur la page du produit en question et d'enregistrer celui-ci ( en tout état de cause un message avertit le client de l'impossibilité d'ajouter ce produit à son panier ).<strong>"</strong>
	</blockquote>
</div>
<br>
<table>
<tr>
	<td>
		<h4>En purgant le cache, vous effacerez l'ensemble des pages statiques générées par le cache.</h4>
	</td>
	<td>
		<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name_1; ?>" value="Y">
		<input type="submit" name="Submit1" class="button-primary" value="Vider le cache" />
		</form>
	</td>
</tr>
<tr>
	<td>
		<h4>Reconstruire le cache permet d'éviter un temps de chargement plus long pour le premier internaute qui visite votre site.</h4>
		<blockquote><p><small>Le reconstruction du cache prends un certains temps , veuillez patientez ...( Faire cette reconstrution à un moment où votre site est peu sollicité )</small></p></blockquote>
	</td>
	<td>
		<form name="form2" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name_2; ?>" value="Y">
		<input type="submit" name="Submit2" class="button-primary" value="Reconstruire le cache" />
		</form>
	</td>
</tr>
</table>
<br>
<br>
<br>
<hr />
<br>
<h3>Option de sauvegarde:</h3>
<br>
<table>
<tr>
	<td>
		<h4>Forcer la sauvegarde journalière du répertoire de votre site. </h4>
		<blockquote><p><small>Une sauvegarde journalière du répertoire est mise en place ( durée de vie de 3 jours ). Ces sauvegardes sont protégés contre le piratage et sont accessible via FTP. </small></p></blockquote>
	</td>
	<td>
		<form name="form3" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name_3; ?>" value="Y">
		<input type="submit" name="Submit3" class="button-primary" value="Forcer Sauvegarde répertoire" />
		</form>
	</td>
</tr>
<tr>
	<td>
		<h4>Forcer la sauvegarde journalière de la base de donnée ( Sauvegarde uniquement des tables wordpress or plugins ). </h4>
		<blockquote><p><small>Une sauvegarde journalière de la base de donnée est mise en place ( durée de vie de 7 jours ). Ces sauvegardes sont protégés contre le piratage et sont accessible via FTP.</small></p>
		<p><small>Il est conseillé d'effectuer régulièrement une sauvegarde complète via OVH ou l'interface admin de la base de donnée ( utilisateur expérimenté ).</small></p></blockquote>
	</td>
	<td>
		<form name="form4" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name_4; ?>" value="Y">
		<input type="submit" name="Submit4" class="button-primary" value="Forcer Sauvegarde SQL" />
		</form>
	</td>
</tr>
<tr>
	<td>
		<h4>Optimisation de la base de donnée ( uniquement table wordpress - périodicité: mensuel).</h4>
		<blockquote><p><small>Avant de lancer une optimisation de votre base de donnée, il est conseillé d'effectuer de faire une sauvegarde de celle-ci.</small></p></blockquote>
	</td>
	<td>
		<form name="form5" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name_5; ?>" value="Y">
		<input type="submit" name="Submit5" class="button-primary" value="Optimiser base de donnée" />
		</form>
	</td>
</tr>
</table>
<br>
<br>
<br>
<blockquote >Stéphen DELETANG</blockquote>
<hr />

</div>

<?php
}
?>