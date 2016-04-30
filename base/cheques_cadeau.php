<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Chèque cadeau
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cheques_cadeau\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function cheques_cadeau_declarer_tables_interfaces($interfaces) {

	$interfaces['table_des_tables']['cadeau_cheques'] = 'cadeau_cheques';

	return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function cheques_cadeau_declarer_tables_objets_sql($tables) {

	$tables['spip_cadeau_cheques'] = array(
		'type' => 'cadeau_cheque',
		'principale' => "oui", 
		'table_objet_surnoms' => array('cadeaucheque'), // table_objet('cadeau_cheque') => 'cadeau_cheques' 
		'field'=> array(
			"id_cadeau_cheque"   => "bigint(21) NOT NULL",
			"titre"              => "varchar(255) NOT NULL DEFAULT ''",
			"descriptif"         => "text NOT NULL DEFAULT ''",
			"prix"               => "float (38,2) NOT NULL",
			"prix_ht"            => "float (38,2) NOT NULL",
			"date"               => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", 
			"statut"             => "varchar(20)  DEFAULT '0' NOT NULL", 
			"maj"                => "TIMESTAMP"
		),
		'key' => array(
			"PRIMARY KEY"        => "id_cadeau_cheque",
			"KEY statut"         => "statut", 
		),
		'titre' => "titre AS titre, '' AS lang",
		'date' => "date",
		'champs_editables'  => array('titre', 'descriptif', 'prix', 'prix_ht'),
		'champs_versionnes' => array('titre', 'descriptif', 'prix', 'prix_ht'),
		'rechercher_champs' => array("titre" => 8),
		'tables_jointures'  => array(),
		'statut_textes_instituer' => array(
			'prepa'    => 'texte_statut_en_cours_redaction',
			'prop'     => 'texte_statut_propose_evaluation',
			'publie'   => 'texte_statut_publie',
			'refuse'   => 'texte_statut_refuse',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut'=> array(
			array(
				'champ'     => 'statut',
				'publie'    => 'publie',
				'previsu'   => 'publie,prop,prepa',
				'post_date' => 'date', 
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'cadeau_cheque:texte_changer_statut_cadeau_cheque', 
		

	);

	return $tables;
}



?>