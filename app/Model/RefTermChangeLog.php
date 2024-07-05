<?php

App::uses('AppModel', 'Model');

class RefTermChangeLog extends AppModel {

     /**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = array(
		'BuTerm' => array(
			'className' => 'BuTerm',
			'foreignKey' => 'bu_term_id',
		),
     );
}

