<?php
App::uses('AppModel', 'Model');
/**
 * BrmAccount Model
 *
 * @property BrmAccountPair $BrmAccountPair
 * @property BrmAccountSetup $BrmAccountSetup
 * @property BrmBudgetPrime $BrmBudgetPrime
 * @property BrmMainBudget $BrmMainBudget
 * @property BrmMainResult $BrmMainResult
 * @property BrmSaccount $BrmSaccount
 */
class BrmAccount extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'group_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'name_jp' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		// 'name_en' => array(
		// 	'notBlank' => array(
		// 		'rule' => array('notBlank'),
		// 		//'message' => 'Your custom message here',
		// 		//'allowEmpty' => false,
		// 		//'required' => false,
		// 		//'last' => false, // Stop validation after this rule
		// 		//'on' => 'create', // Limit validation to 'create' or 'update' operations
		// 	),
		// ),
		// 'type' => array(
		// 	'notBlank' => array(
		// 		'rule' => array('notBlank'),
		// 		//'message' => 'Your custom message here',
		// 		//'allowEmpty' => false,
		// 		//'required' => false,
		// 		//'last' => false, // Stop validation after this rule
		// 		//'on' => 'create', // Limit validation to 'create' or 'update' operations
		// 	),
		// ),
		'flag' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
		'updated_by' => array(
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
		'updated_date' => array(
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'BrmAccountPair' => array(
			'className' => 'BrmAccountPair',
			'foreignKey' => 'brm_account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'BrmAccountSetup' => array(
			'className' => 'BrmAccountSetup',
			'foreignKey' => 'brm_account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'BrmBudgetPrime' => array(
			'className' => 'BrmBudgetPrime',
			'foreignKey' => 'brm_account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'BrmMainBudget' => array(
			'className' => 'BrmMainBudget',
			'foreignKey' => 'brm_account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'BrmMainResult' => array(
			'className' => 'BrmMainResult',
			'foreignKey' => 'brm_account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'BrmSaccount' => array(
			'className' => 'BrmSaccount',
			'foreignKey' => 'brm_account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	public function getAccountByHeadQuarter($hqDepCode, $target_year, $subAccName = NULL){
		
		$sql = "";
		$sql ="SELECT brm_account_setups.*, brm_accounts.id, brm_accounts.name_jp, brm_accounts.type, brm_accounts.calculation_method, brm_accounts.auto_changed
		FROM brm_account_setups
		JOIN  brm_accounts ON  brm_accounts.id = brm_account_setups.brm_account_id
		WHERE brm_account_setups.hlayer_code= :hqDepCode
		AND brm_account_setups.brm_saccount_id=0
		AND brm_account_setups.target_year= :target_year";
		if($subAccName != NULL){
			$sql .=" AND brm_accounts.name_jp= :name_jp";
		}
		$sql .=" ORDER BY brm_account_setups.order, brm_account_setups.sub_order";

		$param = array();
		$param['hqDepCode'] = $hqDepCode;
		$param['target_year'] = $target_year;
		if($subAccName != NULL){
			$param['name_jp'] = $subAccName;
		}
		
		$data = $this->query($sql,$param);
		return $data;
	}

	public function getAccountCode($acc_id){
		$sql ="SELECT account_code, brm_account_id
		FROM brm_saccounts WHERE brm_account_id = ".$acc_id. " AND flag = 1";
		$data = $this->query($sql);
		return $data;
	}

}
