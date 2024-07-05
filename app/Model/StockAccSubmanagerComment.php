<?php
App::uses('AppModel', 'Model');
/**
 * StockAccSubmanagerComment Model
 *
 * @property Stock $Stock
 */
class StockAccSubmanagerComment extends AppModel {

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
		'Stock' => array(
			'className' => 'Stock',
			'foreignKey' => 'stock_id',
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
     * @param stock_id
     */
    public function Update_Del_flag($stock_id,$login_id) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE stock_acc_submanager_comments ";
    	$sql .= " 	 SET flag = 0, ";
    	$sql .= "		 updated_by = :updated_by, ";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE stock_id = :stock_id ";
    	 
    	$currentTimestamp = date('Y-m-d H:i:s');
    	$param['stock_id'] = $stock_id;
    	$param['updated_by'] = $login_id;
    	$param['updated_date'] = $currentTimestamp;  
    	$this->query($sql,$param);
    
    }
    /**
     * Update data when exist in database
     *
     * @author Aye Thandar Lwin
     *
     * @param stock_id
     */
    public function update_Acc_submanagerComment($stock_id,$mgr_comment) {
    
    	$param = array();
    
    	$sql  = "";
    	$sql .= " UPDATE stock_acc_submanager_comments ";
    	$sql .= " 	 SET comment = :comment, ";
    	$sql .= "		 updated_date = :updated_date ";
    	$sql .= "  WHERE stock_id = :stock_id ";
    
    	$currentTimestamp = date('Y-m-d H:i:s');
    	$param['stock_id'] = $stock_id;
    	$param['comment'] = $mgr_comment;
    	$param['updated_date'] = $currentTimestamp;   
    	$this->query($sql,$param);  
    }
    
    /**
     * Save data when not exist in database
     *
     * @author Aye Thandar Lwin
     *
     * @param stock_id,comment,user_id
     */
    public function Save_Acc_submanagerComment($stock_id,$mgr_comment,$user_id){
    	
    	$param = array();
    	
    	$sql  = "";
    	$sql .= " INSERT INTO stock_acc_submanager_comments( ";
    	$sql .= "			 			 		   stock_id, ";
    	$sql .= " 			 			           comment, ";
    	$sql .= " 			 			           flag, ";
    	$sql .= " 			 			           created_by, ";
    	$sql .= " 			 			           updated_by, ";
    	$sql .= " 			 			           created_date ";
    	$sql .= "								 ) ";
    	$sql .= "                           VALUE( ";
    	$sql .= " 						          :stock_id, ";
    	$sql .= " 						          :comment, ";
    	$sql .= " 						          :flag, ";
    	$sql .= " 						          :created_by, ";
    	$sql .= " 						          :updated_by, ";
    	$sql .= " 						          :created_date ";
    	$sql .= "						         )";
    	
    	$date = date('Y-m-d H:i:s');	
    	$param["stock_id"] = $stock_id;
    	$param["comment"] = $mgr_comment;
    	$param["flag"] = 1;
    	$param["created_by"] = $user_id;
    	$param["updated_by"] = $user_id;
    	$param["created_date"] = $date;	
    	$data = $this->query($sql, $param);  	
    }
}
