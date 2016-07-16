<?php
/**
 * Gestion du formulaire de d'édition de cadeau_cheque
 *
 * @plugin     Chèque cadeau
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cheques_cadeau\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/actions');
include_spip('inc/editer');

/**
 * Chargement du formulaire d'édition de cadeau_cheque
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @param int|string $id_cadeau_cheque
 *     Identifiant du cadeau_cheque. 'new' pour un nouveau cadeau_cheque.
 * @param array $options
 *     à definir
 * @param string $retour
 *     URL de redirection après le traitement
 * @return array
 *     Environnement du formulaire
 */
function formulaires_commande_cheque_charger_dist($id_cadeau_cheque = '', $options=array(), $retour=''){
	include_spip('inc/commandes');

	$id_commande = creer_commande_encours();
	
	if ($id_auteur = session_get('id_auteur')) {
		$auteur = sql_fetsel('nom,email', 'spip_auteurs', 'id_auteur=' . $id_auteur);
	}
	
	$valeurs = array(
		'id_commande' => $id_commande,
		'nom' => _request('nom') ? _request('nom') : (
				(isset($auteur['nom']) ? $auteur['nom'] : '')
			),
		'email' => _request('email') ? _request('email') : (
				(isset($auteur['email']) ? $auteur['email'] : '')
			),
		'id_auteur' => $id_auteur,
		'nom_beneficiaire' => _request('nom_beneficiaire'),
		'email_beneficiaire' => _request('email_beneficiaire'),
		'id_cadeau_cheque' => $id_cadeau_cheque,
		'montant' => _request('montant'),
		'cheques' => _request('cheques'),
		'message' => _request('message'),
		'new_login' => _request('new_login'),
	);
	
	$valeurs['_hidden'] .= '<input type="hidden" name="id_commande" value="' . $id_commande . '" />';
	if ($id_cadeau_cheque) {
		$valeurs['_hidden'] .= '<input type="hidden" name="id_cadeau_cheque" value="' . $id_cadeau_cheque . '" />';
	}
	
	return $valeurs;
}

/**
 * Vérifications du formulaire d'édition de cadeau_cheque
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @param int|string $id_cadeau_cheque
 *     Identifiant du cadeau_cheque. 'new' pour un nouveau cadeau_cheque.
 * @param array $options
 *     à definir
 * @param string $retour
 *     URL de redirection après le traitement
 * @return array
 *     Tableau des erreurs
 */
function formulaires_commande_cheque_verifier_dist($id_cadeau_cheque, $options=array(), $retour=''){
	include_spip('inc/session');
	$verifier = charger_fonction('verifier', 'inc/');
	
	// Les champs obligatoires.
	$obligatoires = array(
		'montant',
		'nom_beneficiaire',
		'email_beneficiaire',
	);
	
	if (!$id_auteur = session_get('id_auteur')) {
		$obligatoires = array_merge($obligatoires,array(
			'nom',
			'email',
			'new_pass',
			'new_login'
		));
		include_spip('inc/auth');
		
		//Vérifier le login
		if ($err = auth_verifier_login($auth_methode, _request('new_login'), $id_auteur)) {
			$erreurs['new_login'] = $err;
			$erreurs['message_erreur'] .= $err;
		}
		
		//Vérifier les mp
		if ($p = _request('new_pass')) {
			if ($p != _request('new_pass2')) {
				$erreurs['new_pass'] = _T('info_passes_identiques');
				$erreurs['message_erreur'] .= _T('info_passes_identiques');
			}
			elseif ($err = auth_verifier_pass($auth_methode, _request('new_login'), $p, $id_auteur)) {
				$erreurs['new_pass'] = $err;
			}
		}
	}
	
	$erreurs = array();
	foreach ($obligatoires AS $champ) {
		if (!_request($champ))
			$erreurs[$champ] = _T("info_obligatoire");
	}
	
	// Vérficiations spécifiques.
	
	// Les mails
	$emails = array('email', 'email_beneficiaire');
	foreach ($emails as $champ) {
		if ($$champ = _request($champ ) AND $erreur = $verifier($$champ, 'email')){
			$erreurs[$champ] = $erreur;
		}
	}

	if (_request('email')) {
		if ($email_utilise = sql_getfetsel('email', 'spip_auteurs', 'email=' . sql_quote($email))) {
			$erreurs['email'] = _T('cheques_cadeau:erreur_email_utilise');
		}
	}
	
	// le montant
	if ($montant = _request('montant') AND $erreur = $verifier($montant, 'entier')){
		$erreurs['montant'] = $erreur;
	}
	/* AUTEUR
	 * Si pas connecté on teste sur l'email, si présent on popose login, sinon login et mot de passe pour enregistrer
	 */
	
	if (!$erreurs AND !$id_auteur) {
		$res = formulaires_editer_objet_traiter ( 'auteur', 'new', '', '', $retour, $config_fonc, $row, $hidden );
		$id_auteur = $res ['id_auteur'];
		sql_updateq ('spip_auteurs'
			, array (
					'statut' => '6forum'
					),
			'id_auteur=' . $id_auteur );
		$auteur = sql_fetsel ( '*', 'spip_auteurs', 'id_auteur=' . $id_auteur );
		auth_loger ( $auteur );
	}
	return $erreurs;

}

/**
 * Traitement du formulaire d'édition de cadeau_cheque
 *
 * Traiter les champs postés
 *
 * @uses cheques_remplir_commande()
 *
 * @param int|string $id_cadeau_cheque
 *     Identifiant du cadeau_cheque. 'new' pour un nouveau cadeau_cheque.
 * @param array $options
 *     à definir
 * @param string $retour
 *     URL de redirection après le traitement
 * @return array
 *     Retours des traitements
 */
function formulaires_commande_cheque_traiter_dist($id_cadeau_cheque, $options=array(), $retour=''){
	
	// et la remplir les details de la commande d'après le panier en session
	if ($id_commande = _request('id_commande')){
		include_spip('action/editer_objet');
		include_spip('action/commandes_cheques');
		
		// Enregistrer les informations de la commande.
		objet_modifier('commande', $id_commande, array(
				'statut' => 'attente',
				'nom_beneficiaire' =>_request('nom_beneficiaire'),
				'email_beneficiaire' =>_request('email_beneficiaire'),
				'message' =>_request('message'),
			)
		);
		
		cheques_remplir_commande($id_commande, $id_cadeau_cheque, $options, false);
		$res['message_ok'] = _T('cheques_cadeau:message_ok_cheque_commande');
		$res['message_ok'] .= recuperer_fond('inclure/commande',array('id_commande' => $id_commande));
	}
	
	// Un lien a prendre en compte ?
	if (isset($res['redirect'])) {
		$res['redirect'] = parametre_url ($res['redirect'], "id_lien_ajoute", $id_commande, '&');
	}
	return $res;

}
