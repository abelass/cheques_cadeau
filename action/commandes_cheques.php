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
	if ($panier and is_array($panier)){
		$details = array();
		include_spip('spip_bonux_fonctions');
		$fonction_prix = charger_fonction('prix', 'inc/');
		$fonction_prix_ht = charger_fonction('ht', 'inc/prix');
		foreach($panier as $emplette){
			$prix_ht = $fonction_prix_ht($emplette['objet'], $emplette['id_objet'],3);
			$prix = $fonction_prix($emplette['objet'], $emplette['id_objet'],3);
			if($prix_ht > 0)
				$taxe = round(($prix - $prix_ht) / $prix_ht, 3);
			else
				$taxe = 0;

			$set = array(
				'id_commande' => $id_commande,
				'objet' => 'cadeau_cheque',
				'id_objet' => $id_cadeau_cheque,
				'descriptif' => generer_info_entite('cadeau_cheque', $id_cadeau_cheque, 'titre', '*'),
				'quantite' => 1,
				'prix_unitaire_ht' => $prix_ht,
				'taxe' => 0,
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
		}
		if (!$append){
			// supprimer les details qui n'ont rien a voir avec ce panier
			sql_delete("spip_commandes_details","id_commande=".intval($id_commande)." AND ".sql_in('id_commandes_detail',$details,"NOT"));
		}
	}

}