<?php
App::uses('AppModel', 'Model');
/**
 * BrmCopyTermLog Model
 *
 * @property BrmTerm $BrmTerm
 * @property CopyBrmTerm $CopyBrmTerm
 */
class BrmCopyTermLog extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'brm_term_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'copy_brm_term_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'created_by' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'created_date' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'BrmTerm' => array(
			'className' => 'BrmTerm',
			'foreignKey' => 'brm_term_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CopyBrmTerm' => array(
			'className' => 'CopyBrmTerm',
			'foreignKey' => 'copy_brm_term_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
