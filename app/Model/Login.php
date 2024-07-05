<?php
App::uses('AppModel', 'Model');
/**
 * Login Model
 *
 */
class Login extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'type_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}
