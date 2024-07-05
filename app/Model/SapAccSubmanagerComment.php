<?php
App::uses('AppModel', 'Model');
/**
 * SapAccSubmanagerComment Model
 *
 * @property Sap $Sap
 */
class SapAccSubmanagerComment extends AppModel {

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
     * Update del flag 0
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function Update_Del_flag($sap_id,$login_id) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE sap_acc_submanager_comments ";
    	$sql .= " 	 SET flag = 0, ";
    	$sql .= "		 updated_by = :updated_by, ";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE sap_id = :sap_id ";
    	 
    	$currentTimestamp = date('Y-m-d H:i:s');
    	$param['sap_id'] = $sap_id;
    	$param['updated_by'] = $login_id;
    	$param['updated_date'] = $currentTimestamp;  
    	$this->query($sql,$param);
    
    }
    /**
     * Update data when exist in database
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id
     */
    public function update_Acc_submanagerComment($sap_id,$mgr_comment) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE sap_acc_submanager_comments ";
    	$sql .= " 	 SET comment = :comment, ";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE sap_id = :sap_id ";
    
    	$currentTimestamp = date('Y-m-d H:i:s');
    	$param['sap_id'] = $sap_id;
    	$param['comment'] = $mgr_comment;
    	$param['updated_date'] = $currentTimestamp;   
    	$this->query($sql,$param);  
    }
    
    /**
     * Save data when not exist in database
     *
     * @author Aye Thandar Lwin
     *
     * @param sap_id,comment,user_id
     */
    public function Save_Acc_submanagerComment($sap_id,$mgr_comment,$user_id){
    	
    	$param = array();
    	
    	$sql  = "";
    	$sql .= " INSERT INTO sap_acc_submanager_comments( ";
    	$sql .= "			 			 		   sap_id, ";
    	$sql .= " 			 			           comment, ";
    	$sql .= " 			 			           flag, ";
    	$sql .= " 			 			           created_by, ";
    	$sql .= " 			 			           updated_by, ";
    	$sql .= " 			 			           created_date ";
    	$sql .= "								 ) ";
    	$sql .= "                           VALUE( ";
    	$sql .= " 						          :sap_id, ";
    	$sql .= " 						          :comment, ";
    	$sql .= " 						          :flag, ";
    	$sql .= " 						          :created_by, ";
    	$sql .= " 						          :updated_by, ";
    	$sql .= " 						          :created_date ";
    	$sql .= "						         )";
    	
    	$date = date('Y-m-d H:i:s');	
    	$param["sap_id"] = $sap_id;
    	$param["comment"] = $mgr_comment;
    	$param["flag"] = 1;
    	$param["created_by"] = $user_id;
    	$param["updated_by"] = $user_id;
    	$param["created_date"] = $date;	
    	$data = $this->query($sql, $param);  	
    }
}
