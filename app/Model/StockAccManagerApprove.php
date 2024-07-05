<?php
App::uses('AppModel', 'Model');
/**
 * StockAccManagerApprove Model
 *
 * @property Stock $Stock
 */
class StockAccManagerApprove extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'stock_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'approve_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'updated_by' => array(
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

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Stock' => array(
			'className' => 'Stock',
			'foreignKey' => 'stock_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	 /**
     * Update flag 0 => Account Manager Approve Cancel
     *
     * @author Aye Thandar Lwin
     *
     * @param stock_id
     */
    public function AccMgrArrpoveCancel($stock_id,$login_id) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE stock_acc_manager_approves ";
    	$sql .= " 	 SET flag = '0', ";
    	$sql .= "		 updated_by = :updated_by ";
    	$sql .= "  WHERE stock_id = :stock_id ";
    
    	$currentTimestamp = date('Y-m-d H:i:s');
    	 
    	$param['stock_id'] = $stock_id;
    	$param['updated_by'] = $login_id;
    	//$param['updated_date'] = $currentTimestamp;
    
    	$this->query($sql,$param);
    
    }
}
