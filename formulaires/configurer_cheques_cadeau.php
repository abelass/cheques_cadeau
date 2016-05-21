<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION'))
	return;

function formulaires_configurer_cheques_cadeau_saisies_dist() {
	include_spip('inc/config');
	
	$config = lire_config('cheques_cadeau', array());

	return array(
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fieldset_parametres',
				'label' => _T('cheques_cadeau:cfg_titre_parametrages')
			),

			'saisies' => array(
				array(
					'saisie' => 'choisir_objets',
					'options' => array(
						'nom' => 'objets',
						'defaut' => $config['objets'] ,
						'label' => _T('cheques_cadeau:label_objets'),
					)
				),

		),
		)
	);
}
?>
