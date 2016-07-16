<?php
/**
 * Fichier gérant l'installation et désinstallation du plugin Chèque cadeau
 *
 * @plugin     Chèque cadeau
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cheques_cadeau\Installation
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cextras');
include_spip('base/cheques_cadeau');

/**
 * Fonction d'installation et de mise à jour du plugin Chèque cadeau.
 *
 * Vous pouvez :
 *
 * - créer la structure SQL,
 * - insérer du pre-contenu,
 * - installer des valeurs de configuration,
 * - mettre à jour la structure SQL 
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
**/
function cheques_cadeau_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();

	// Créer les champs extras.
	cextras_api_upgrade(cheques_cadeau_declarer_champs_extras(), $maj['create']);
	// Installer les tables nécessaires
	$maj['create'][] = array('maj_tables', array('spip_cadeau_cheques', 'spip_cadeau_cheques_liens'));

	include_spip('base/upgrade');
	
	maj_plugin($nom_meta_base_version, $version_cible, $maj);

}

/**
 * Fonction de désinstallation du plugin Chèque cadeau.
 * 
 * Vous devez :
 *
 * - nettoyer toutes les données ajoutées par le plugin et son utilisation
 * - supprimer les tables et les champs créés par le plugin. 
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
**/
function cheques_cadeau_vider_tables($nom_meta_base_version) {

	sql_drop_table("spip_cadeau_cheques");
	sql_drop_table("spip_cadeau_cheques_liens");

	# Nettoyer les versionnages et forums
	sql_delete("spip_versions",              sql_in("objet", array('cadeau_cheque')));
	sql_delete("spip_versions_fragments",    sql_in("objet", array('cadeau_cheque')));
	sql_delete("spip_forum",                 sql_in("objet", array('cadeau_cheque')));
	
	// Eliminer les champs extras.
	cextras_api_vider_tables(cheques_cadeau_declarer_champs_extras());

	effacer_meta($nom_meta_base_version);
}

?>