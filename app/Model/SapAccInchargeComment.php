<?php
App::uses('AppModel', 'Model');
/**
 * SapAccInchargeComment Model
 *
 * @property Sap $Sap
 */
class SapAccInchargeComment extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'sap_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Sap' => array(
			'className' => 'Sap',
			'foreignKey' => 'sap_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
     * Update flag 0 => delete from admin user
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function AdminDelFlagAccInchargecomment($sap_id,$login_id) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE sap_acc_incharge_comments ";
    	$sql .= " 	 SET flag = '0', ";
    	$sql .= "		 updated_by = :updated_by ,";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE sap_id = :sap_id ";
    
    	$currentTimestamp = date('Y-m-d H:i:s');
    
    	$param['sap_id'] = $sap_id;
    	$param['updated_by'] = $login_id;
    	$param['updated_date'] = $currentTimestamp;
    
    	$this->query($sql,$param);
    
    }
}
