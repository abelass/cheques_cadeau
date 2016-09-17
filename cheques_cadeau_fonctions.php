<?php
/**
 * Fonctions utiles au plugin Chèque cadeau
 *
 * @plugin     Chèque cadeau
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cheques_cadeau\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


function cheques_cadeau_devise_defaut() {
	if (function_exists('prix_objets_devise_defaut')) {
		$devise_defaut = prix_objets_devise_defaut();
	}
	else {
		$devise_defaut = 'EUR';
	}
	
	return $devise_defaut;
}
