<?php
/**
 * Fonction du plugin Commandes
 *
 * @plugin     Commandes
 * @copyright  2014
 * @author     Ateliers CYM, Matthieu Marcillaud, Les Développements Durables
 * @licence    GPL 3
 * @package    SPIP\Commandes\Notifications
 */

// Sécurité
if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Détermine le client destinataire des notifications d'une commande
 *
 * @param int $id_commande
 *     Identifiant de la commande
 * @param array $options
 *     options
 * @return array
 *     Liste des destinataires
 */
function notifications_commande_beneficiaire_destinataires_dist($id_commande, $options) {	
	$email_beneficiaire=sql_getfetsel("email_beneficiaire","spip_commandes","id_commande=".$id_commande);
	return array($email_beneficiaire);
}

?>
