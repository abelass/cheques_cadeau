<?php
/**
 * Fonction du plugin  Chèque cadeau
 *
 * @plugin    Chèque cadeau
 * @copyright  2016
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Cheques_cadea\Action
 */

// Sécurité
if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Remplir une commande d'apres un panier
 *
 * @param  int $id_commande
 * @param  int $id_panier
 * @param  bool $append
 *   true pour ajouter brutalement le panier a la commande, false pour verifier que commande==panier en ajoutant/supprimant uniquement les details necessaires
 */
function cheques_remplir_commande($id_commande, $id_cadeau_cheque, $options = array(), $append=true){

	include_spip('action/editer_objet');
	include_spip('inc/filtres');

	// noter le panier source dans le champ source de la commande
	objet_modifier('commande',$id_commande,array('source'=>"cheque_cadeau#$id_cadeau_cheque"));


	// Pour chaque élément du panier, on va remplir la commande
	// (ou verifier que la ligne est deja dans la commande)
	$details = array();
	include_spip('spip_bonux_fonctions');

	$set = array(
		'id_commande' => $id_commande,
		'objet' => 'cadeau_cheque',
		'id_objet' => $id_cadeau_cheque,
		'descriptif' => generer_info_entite($id_cadeau_cheque, 'cadeau_cheque', 'titre'),
		'quantite' => 1,
		'prix_unitaire_ht' => _request('montant') ? _request('montant') : 
			(isset($options['montant']) ? $options['montant'] : 0),
		'taxe' => _request('taxe') ? _request('taxe') : 
			(isset($options['taxe']) ? $options['taxe'] : 0),
		'statut' => 'attente'
	);
	$where = array();
	foreach($set as $k=>$w){
		if (in_array($k,array('id_commande','objet','id_objet'))){
			$where[] = "$k=".sql_quote($w);
		}
	}
	// est-ce que cette ligne est deja la ?
	if ($append OR !$id_commandes_detail = sql_getfetsel("id_commandes_detail","spip_commandes_details",$where)){
		// sinon création et renseignement du détail de la commande
		$id_commandes_detail = objet_inserer('commandes_detail');
	}
	if ($id_commandes_detail) {
		objet_modifier('commandes_detail', $id_commandes_detail, $set);
		$details[] = $id_commandes_detail;
	}
	
	if (!$append){
		// supprimer les details qui n'ont rien a voir avec ce panier
		sql_delete("spip_commandes_details","id_commande=".intval($id_commande)." AND ".sql_in('id_commandes_detail',$details,"NOT"));
	}
}