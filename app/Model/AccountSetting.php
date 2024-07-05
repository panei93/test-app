<?php
App::uses('AppModel', 'Model');
/**
 * AccountSetting Model
 *
 * @property Layer $Layer
 * @property Account $Account
 * @property Menu $Menu
 */
class AccountSetting extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
// public $validate = array(
// 	'target_year' => array(
// 		'notBlank' => array(
// 			'rule' => array('notBlank'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'layer_id' => array(
// 		'notBlank' => array(
// 			'rule' => array('notBlank'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'account_id' => array(
// 		'numeric' => array(
// 			'rule' => array('numeric'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'label_name' => array(
// 		'notBlank' => array(
// 			'rule' => array('notBlank'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'display_order' => array(
// 		'numeric' => array(
// 			'rule' => array('numeric'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'flag' => array(
// 		'notBlank' => array(
// 			'rule' => array('notBlank'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'created_by' => array(
// 		'numeric' => array(
// 			// 'rule' => array('numeric'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'updated_by' => array(
// 		'numeric' => array(
// 			// 'rule' => array('numeric'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'created_date' => array(
// 		'datetime' => array(
// 			// 'rule' => array('datetime'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// 	'updated_date' => array(
// 		'datetime' => array(
// 			// 'rule' => array('datetime'),
// 			//'message' => 'Your custom message here',
// 			//'allowEmpty' => false,
// 			//'required' => false,
// 			//'last' => false, // Stop validation after this rule
// 			//'on' => 'create', // Limit validation to 'create' or 'update' operations
// 		),
// 	),
// );

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		// 'Layer' => array(
		// 	'className' => 'Layer',
		// 	'foreignKey' => 'layer_code_id',
		// 	'conditions' => '',
		// 	'fields' => '',
		// 	'order' => ''
		// ),
		// 'Account' => array(
		// 	'className' => 'Account',
		// 	'foreignKey' => 'account_id',
		// 	'conditions' => '',
		// 	'fields' => '',
		// 	'order' => ''
		// ),
		// 'Menu' => array(
		// 	'className' => 'Menu',
		// 	'foreignKey' => 'menu_id',
		// 	'conditions' => '',
		// 	'fields' => '',
		// 	'order' => ''
		// )
	);

	// public function searchAccountSetting($conditions){
	// 	$menus = Setting::PAGE_NAME;
	// 	$menuString = implode("','",$menus);
		
	// 	$sql = '';
		
	// 	$sql .= "SELECT account_settings.*,accounts.*,menus.*,layers.*
	// 	From account_settings
	// 	LEFT JOIN layers ON account_settings.layer_code COLLATE  utf8mb4_general_ci = layers.layer_code 
	// 	LEFT JOIN menus ON menus.id = account_settings.menu_id
	// 	LEFT JOIN accounts ON accounts.id = account_settings.account_id ";
	// 	$sql .= " WHERE account_settings.flag = 1 ";
		
	// 	if(!empty($conditions)){
	// 		$sql .= "AND menus.page_name LIKE '%$conditions%' ";
	// 	} else {
	// 		$sql .= "AND menus.menu_name_en IN ('$menuString')";
	// 	}
	// 	$datas = $this->query($sql);

	// 	return $datas;
	// }

	public function choiceAccount($total,$normal){
		$sql = '';
		$sql .= 'SELECT id,account_name from accounts ';

		if(!empty($total)){
			$sql .= 'WHERE account_type = 2 ';
		}

		if(!empty($normal)){
			$sql .= 'WHERE account_type = 1 ';
		}
		$sql .= 'AND flag = 1 ';
		$datas = $this->query($sql);
		return $datas;
	}
}
