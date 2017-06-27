<?php
/*
Plugin Name: Frodon - Porteur de l'anneau
Description: S'occupe de la sauvegarde et de la mise en cache de votre site.
Version: 1.0
Author: Stephen DELETANG
Copyright 2014 Stephen DELETANG
*/

defined('ABSPATH') or die('Vous avez bien fait de venir. Vous entendrez aujourd\' hui tout ce qu\'il vous est n&eacute;cessaire de savoir pour comprendre les desseins de l\'ennemi.'); 
	

/*** FRODON GERE LES SAUVEGARDES ----------------------------------------*/
  
  // On plannifie la sauvegarde journalière de la BDD.
  add_action('wp', '_frodon_backup_bdd_scheduled');
  function _frodon_backup_bdd_scheduled() {
    if ( !wp_next_scheduled( '_frodon_backup_bdd_daily_event' ) ) { wp_schedule_event( time(), 'daily', '_frodon_backup_bdd_daily_event' ); }
  }
  add_action( '_frodon_backup_bdd_daily_event', '_frodon_do_backup_bdd' ); // On appelle la fonction de sauvegarde BDD.

  // On plannifie la sauvegarde journalière des repertoires du site.
  add_action('wp', '_frodon_backup_website_scheduled');
  function _frodon_backup_website_scheduled() {
    if ( !wp_next_scheduled( '_frodon_backup_website_daily_event' ) ) { wp_schedule_event( time(), 'daily', '_frodon_backup_website_daily_event' ); }
  }
  add_action( '_frodon_backup_website_daily_event', '_frodon_do_backup_website' ); // On appelle la fonction de sauvegarde des répertoires.


  /*** ON SAUVEGARDE LA BDD ----------------------------------------*/
  function _frodon_do_backup_bdd( ) {
    global $wpdb;
    $buffer          = '';  // Variable de sortie
    $backup_file     = 'db-' . date( 'd-m-Y-G-i' ); // nom du fichier de backup
    $backup_dir      = $_SERVER['DOCUMENT_ROOT'].'/backup-bdd-' . substr( md5( __FILE__ ), 0, 8 ); // nom du dossier où sera stocké tous les backup
    $htaccess_file   = $backup_dir . '/.htaccess';  // chemin vers le fichier .htaccess du dossier de backup
    $backup_max_life = 604800;  // temps maximum de vie d'un backup - temps en secondes
    /*-----------------------------------------------------------------------------------*/
    /*  Gestion du dossier backup-bdd
    /*-----------------------------------------------------------------------------------*/
    // On créé le dossier backup-bdd si il n'existe pas
    if( !is_dir( $backup_dir ) ) mkdir( $backup_dir, 0755 );
    // On ajoute un fichier .htaccess pour la sécurité
    // On interdit l'accès au dossier à partir du navigateur
    if( !file_exists( $htaccess_file ) ) {
      $htaccess_file_content  = "Order Allow, Deny\n";
      $htaccess_file_content .= "Deny from all";
      file_put_contents( $htaccess_file, $htaccess_file_content );
    }
    /*-----------------------------------------------------------------------------------*/
    /*  On boucle chacune des tables
    /*-----------------------------------------------------------------------------------*/
    foreach ( $wpdb->tables() as $table ) {
      // On recupère la totalité des données de la table
      $table_data = $wpdb->get_results('SELECT * FROM ' . $table, ARRAY_A );
      // On ajoute un commentaire pour délimiter chaque table
      $buffer .= sprintf( "# Dump of table %s \n", $table );
      $buffer .= "# ------------------------------------------------------------ \n\n";
      // On supprime la table si elle existe déjà
      $buffer .= sprintf( "DROP TABLE IF EXISTS %s ;", $table );
      // On ajoute le SQL pour créer la table avec tous les champs
      $show_create_table = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table, ARRAY_A );
      $buffer .= "\n\n" . $show_create_table['Create Table'] . ";\n\n";
      /*-----------------------------------------------------------------------------------*/
      /*  On ajoute chacune des entrées présentes dans la table
      /*-----------------------------------------------------------------------------------*/
      foreach ( $table_data as $row ) {
        $buffer .= 'INSERT INTO ' . $table . ' VALUES(';
        $values = '';
          foreach ( $row as $key => $value )
             $values .= '"' . esc_sql( $value ) . '", ';
          $buffer .= trim( $values, ', ' ) . ");\n";
      }
      $buffer .= "\n\n";
    }
    /*-----------------------------------------------------------------------------------*/
    /*  On sauvegarde le fichier
    /*-----------------------------------------------------------------------------------*/
    file_put_contents( $backup_dir . '/' . $backup_file . '.sql', $buffer );
    /*-----------------------------------------------------------------------------------*/
    /*  On zip le fichier
    /*-----------------------------------------------------------------------------------*/
    if( class_exists( 'ZipArchive' ) ) {
      $zip = new ZipArchive();
      if( $zip->open( $backup_dir . '/' . $backup_file . '.zip', ZipArchive::CREATE ) === true ) {
        // On ajoute le fichier dans l'archive
        $zip->addFile( $backup_dir . '/' . $backup_file . '.sql' );
        $zip->close();
        // On supprime le fichier d'origine
        unlink( $backup_dir . '/' . $backup_file . '.sql' );
      }
    }
    /*-----------------------------------------------------------------------------------*/
    /*  On supprime les backup qui datent de plus d'une semaine
    /*-----------------------------------------------------------------------------------*/
    foreach ( glob( $backup_dir . '/*.zip' ) as $file ) {
      if( time() - filemtime( $file ) > $backup_max_life )
        unlink($file);
    }
  }
  /*** FIN DE : ON SAUVEGARDE LA BASE DE DONNEE ----------------------------------------*/


  /*** ON SAUVEGARDE LES REPERTOIRES ----------------------------------------*/
  function _frodon_do_backup_website() {
    $backup_file     = 'website-' . date( 'd-m-Y-G-i' ); // nom de l'archive de backup
    $backup_dir      = $_SERVER['DOCUMENT_ROOT'].'/backup-website-' . substr( md5( __FILE__ ), 0, 8 ); // nom du dossier où sera stocké tous les backup
    $htaccess_file   = $backup_dir . '/.htaccess';  // chemin vers le fichier .htaccess du dossier de backup
    $backup_max_life = 259200;  // temps maximum de vie d'un backup - temps en secondes
    /*-----------------------------------------------------------------------------------*/
    /*  Gestion du dossier backup-website
    /*-----------------------------------------------------------------------------------*/
    // On créé le dossier backup-bdd si il n'existe pas
    if( !is_dir( $backup_dir ) ) mkdir( $backup_dir, 0755 );
    // On ajoute un fichier .htaccess pour la sécurité
    // On interdit l'accès au dossier à partir du navigateur
    if( !file_exists( $htaccess_file ) ) {
      $htaccess_file_content  = "Order Allow, Deny\n";
      $htaccess_file_content .= "Deny from all";
      file_put_contents( $htaccess_file, $htaccess_file_content );
    }
    /*-----------------------------------------------------------------------------------*/
    /*  On zip les fichiers du site
    /*-----------------------------------------------------------------------------------*/
    if( class_exists( 'ZipArchive' ) ) {
      // On crée une class qui permettra de parcourir les dossiers du site
      class ZipRecursif extends ZipArchive {
        public function addDirectory( $dir ) {
          foreach( glob( $dir . '/*' ) as $file ) {
            is_dir( $file ) ? $this->addDirectory( $file ) : $this->addFile( $file );
          }
        }
      }
      $zip = new ZipRecursif;
      // On check si on peut se servir de l'archive
      if( $zip->open( $backup_dir . '/' . $backup_file . '.zip' , ZipArchive::CREATE ) === true ) {
        $zip->addDirectory(ABSPATH);
        $zip->close();
      }
    }
    /*-----------------------------------------------------------------------------------*/
    /*  On supprime les backup qui datent de plus d'une semaine
    /*-----------------------------------------------------------------------------------*/
    foreach ( glob( $backup_dir . '/*.zip' ) as $file ) {
      if( time() - filemtime( $file ) > $backup_max_life )
        unlink($file);
    }
  }


?>
