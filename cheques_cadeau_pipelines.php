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
 * agit lors de l’édition d’un élément éditorial, 
 * lorsque l’utilisateur édite les champs ou change le statut de l’objet. 
 * Il est appelé juste après l’enregistrement des données.
 *
 * @pipeline affiche_milieu
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cheques_cadeau_post_edition($flux) {
	
	if (is_array($flux) and isset($flux['args']['type']) && $flux['args']['type'] == 'commande') {
		if ($flux['data']['statut'] == 'paye' 
				and $notifications = charger_fonction('notifications', 'inc', true)
				and 
				) {
				$notifications('commande_client', $id_commande, $options);
			
		}
	}
	return $flux;
}

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