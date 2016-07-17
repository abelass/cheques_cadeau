<?php
/**
 * Utilisations de pipelines par Chèque cadeau
 *
 * @plugin     Chèque cadeau
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cheques_cadeau\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Ajout de contenu sur certaines pages,
 * notamment des formulaires de liaisons entre objets
 *
 * @pipeline affiche_milieu
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cheques_cadeau_affiche_milieu($flux) {
	$texte = "";
	$e = trouver_objet_exec($flux['args']['exec']);

	include_spip('inc/config');

	// cadeau_cheques sur les objets choisies
	if (!$e['edition'] AND in_array($e['table_objet_sql'], array_filter(lire_config('cheques_cadeau/objets',array())))) {
		$texte .= recuperer_fond('prive/objets/editer/liens', array(
			'table_source' => 'cadeau_cheques',
			'objet' => $e['type'],
			'id_objet' => $flux['args'][$e['id_table_objet']]
		));
	}
	
	if ($texte) {
		if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
			$flux['data'] = substr_replace($flux['data'],$texte,$p,0);
			else
				$flux['data'] .= $texte;
	}
	
	return $flux;
}

/**
 * Ait lors de l’édition d’un élément éditorial, 
 * lorsque l’utilisateur édite les champs ou change le statut de l’objet. 
 * Il est appelé juste après l’enregistrement des données.
 *
 * @post_edition
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cheques_cadeau_post_edition($flux) {
	$args = $flux['args'];
	$data = $flux['data'];

	if(
			$args['action'] == 'instituer' 
			and $args['table'] == 'spip_commandes' 
			and $data['statut'] == 'paye'
			and $id_commande = $args['id_objet']
			and $source = sql_getfetsel('source',
				'spip_commandes',
				"id_commande=$id_commande AND source LIKE '%cheque_cadeau%'")
			and $notifications = charger_fonction('notifications', 'inc', true)
	) {
		
		include_spip('inc/config');
		$config = lire_config('commandes');
		// Déterminer l'expéditeur
		$options = array();
		
		if( $config['expediteur'] != "facteur" ) {
			$options['expediteur'] = $config['expediteur_'.$config['expediteur']];
		}
		spip_log($options,'teste');
		
		// Envoyer au beneficiaire
		spip_log("traiter_notifications_commande : notification beneficiaire pour la commande $id_commande",'commandes.' . _LOG_INFO);
		$notifications('commande_beneficiaire', $id_commande, $options);

		// Si crédit enregistrer crédir à faveur du bénéficiaire
	}
	return $flux;
}

/**
 * Optimiser la base de données en supprimant les liens orphelins
 * de l'objet vers quelqu'un et de quelqu'un vers l'objet.
 *
 * @pipeline optimiser_base_disparus
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cheques_cadeau_optimiser_base_disparus($flux){
	include_spip('action/editer_liens');
	$flux['data'] += objet_optimiser_liens(array('cadeau_cheque'=>'*'),'*');
	return $flux;
}

?>