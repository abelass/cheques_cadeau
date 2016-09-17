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
		'champs_editables'  => array('titre', 'descriptif'),
		'champs_versionnes' => array('titre', 'descriptif'),
		'rechercher_champs' => array("titre" => 8),
		'tables_jointures'  => array('spip_cadeau_cheques_liens'),
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


/**
 * Déclaration des tables secondaires (liaisons)
 *
 * @pipeline declarer_tables_auxiliaires
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function cheques_cadeau_declarer_tables_auxiliaires($tables) {

	$tables['spip_cadeau_cheques_liens'] = array(
		'field' => array(
			"id_cadeau_cheque"   => "bigint(21) DEFAULT '0' NOT NULL",
			"id_objet"           => "bigint(21) DEFAULT '0' NOT NULL",
			"objet"              => "VARCHAR(25) DEFAULT '' NOT NULL",
			"vu"                 => "VARCHAR(6) DEFAULT 'non' NOT NULL"
		),
		'key' => array(
			"PRIMARY KEY"        => "id_cadeau_cheque,id_objet,objet",
			"KEY id_cadeau_cheque" => "id_cadeau_cheque"
		)
	);

	return $tables;
}

/**
 * Déclaration des champs extras
 * 
 * @pipeline declarer_champs_extras
 * @param array $champs
 *     Description des champs
 */
function cheques_cadeau_declarer_champs_extras($champs = array()) {
	$champs['spip_commandes'] = array(
		'nom_beneficiaire' => array(
			'saisie' => 'input',//Type du champ (voir plugin Saisies)
			'options' => array(
				'nom' => 'nom_beneficiaire',
				'label' => _T('cheques_cadeau:label_nom_beneficiaire'),
				'sql' => "varchar(255) NOT NULL DEFAULT ''",
				'versionner' => true,
				'restrictions' => array('voir' => array('auteur' => ''),
					'modifier' => array('auteur' => 'admin')),
			),
		),
		'email_beneficiaire' => array(
			'saisie' => 'email',
			'options' => array(
				'nom' => 'email_beneficiaire',
				'label' => _T('cheques_cadeau:label_email_beneficiaire'),
				'sql' => "varchar(255) NOT NULL DEFAULT ''",
				'versionner' => true,
				'verifier' => array(
					'type' => 'email',
				),
				'restrictions' => array('voir' => array('auteur' => ''),
					'modifier' => array('auteur' => 'admin')),
			),
		),
		'message' => array(
			'saisie' => 'textarea',
			'options' => array(
				'nom' => 'message',
				'label' => _T('cheques_cadeau:label_message'),
				'sql' => "text NOT NULL DEFAULT ''",
				'versionner' => true,
				'verifier' => array(
					'type' => 'email',
				),
				'restrictions'=>array('voir' => array('auteur' => ''),
					'modifier' => array('auteur' => 'admin')),
			),
		),
	);
	return $champs;
}
