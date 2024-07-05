<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property AzureObject $AzureObject
 * @property Role $Role
 * @property LaborCostDetail $LaborCostDetail
 * @property LaborCost $LaborCost
 */
class User extends AppModel
{
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'login_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
 * belongsTo associations
 *
 * @var array
 */
	// public $belongsTo = array(
	// 	// 'AzureObject' => array(
	// 	// 	'className' => 'AzureObject',
	// 	// 	'foreignKey' => 'azure_object_id',
	// 	// 	'conditions' => '',
	// 	// 	'fields' => '',
	// 	// 	'order' => ''
	// 	// ),
	// 	'Role' => array(
	// 		'className' => 'Role',
	// 		// 'foreignKey' => 'role_id',
	// 		'conditions' => '',
	// 		'fields' => '',
	// 		'order' => ''
	// 	)
	// );

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'LaborCostDetail' => array(
			'className' => 'LaborCostDetail',
			'foreignKey' => 'user_id',
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
		'LaborCost' => array(
			'className' => 'LaborCost',
			'foreignKey' => 'user_id',
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

	/**
	 * search for Manager ID in BA Table
	 * @author Pan Ei Phyo
	 *
	 * @param ba_code
	 * @return data
	 *
	 */
	public function searchManagerIDInBA($ba_code)
	{
		$param = array();

		$sql = "";
		$sql .= " SELECT ";
		$sql .= " manager_id ";
		$sql .= " FROM business_area";
		$sql .= " JOIN user user ";
		$sql .= " ON business_area.manager_id = user.login_id";
		$sql .= " WHERE business_area.ba_code= :ba_code";
		$sql .= " AND user.flag= 1";

		$param['ba_code'] = $ba_code;
		$data = $this->query($sql, $param);

		return $data;
	}

	/**
	 *
	 * @author Aye Zar Ni Kyaw
	 *
	 * @param id
	 * @return data
	 *
	 */
	function UserEditData($id){
		$sql = "";
		$sql ="SELECT users.*,group_concat(layers.id) as layer_id,roles.role_name
			   FROM users
			   LEFT JOIN layers ON FIND_IN_SET(layers.layer_code,users.layer_code) AND layers.to_date >= date('Y-m-d') AND layers.flag = 1
			   LEFT JOIN roles ON roles.id = users.role_id
			   -- LEfT JOIN tbl_business_area on tbl_business_area.ba_code =users.layer_code
			   where users.id= :id";
		$param = array();
		$param['id'] = $id;
		$data = $this->query($sql,$param);
		return $data;
   } 
	
	/**
	 * search for sending email function
	 * @author Pan Ei Phyo
	 *
	 * @param ba_code
	 * @return data
	 *
	 */
   	public function searchUserEmail($ba_code,$level_id,$login_id=""){
		$param = array();
				 
		$sql = "";
		$sql.= " SELECT ";
		$sql.= " user_name,email,login_code ";
		$sql.= " FROM users";
		$sql.= " WHERE users.role_id = :level_id";

		if(!empty($ba_code)){
			$sql.= " AND users.layer_code = :ba_code";
		}
		
		$sql.= " AND users.flag = 1";

		if(empty($ba_code) && ($level_id==5))
		{
			$sql.= " AND users.login_code= :login_id";
		}

		if(!empty($ba_code)){
			 $param['ba_code'] = $ba_code;
		}
		
		$param['level_id'] = $level_id;

		if(empty($ba_code) && ($level_id==5))
		{
			$param['login_id'] = $login_id;
		}
		
		$data = $this->query($sql,$param);
		 
		return $data;
   }

	/**
	 * search for sending email function
	 * @author Pan Ei Phyo
	 *
	 * @param admin_level_id
	 * @return data
	 *
	 */
   public function searchUserEmailAllBA($level_id){
	   $param = array();
				 
		$sql = "";
		$sql.= " SELECT ";
		$sql.= " user_name,email,login_code ";
		$sql.= " FROM users";
		$sql.= " WHERE users.role_id = :level_id";
		$sql.= " AND users.flag= 1";
		 
		$param['level_id'] = $level_id;
		$data = $this->query($sql,$param);
		 
		return $data;
   }

	/**
	 * search for Manager ID in BA Table
	 * @author Pan Ei Phyo
	 *
	 * @param ba_code
	 * @return data
	 *
	 */
   // public function searchManagerIDInBA($ba_code){
   // 	$param = array();
	
   //  	$sql = "";
   //  	$sql.= " SELECT ";
   //  	$sql.= " manager_id ";
   //  	$sql.= " FROM tbl_business_area";
   //  	$sql.= " JOIN users user ";
   //  	$sql.= " ON tbl_business_area.manager_id = user.login_code";
   //  	$sql.= " WHERE tbl_business_area.layer_code= :ba_code";
   //  	$sql.= " AND user.flag= 1";
	
   //  	$param['ba_code'] = $ba_code;
   //  	$data = $this->query($sql,$param);
	
   //  	return $data;
   // }

	/**
	 * Get Last Save user Email Account Tandosha Level
	 * @author Sandi Khaing
	 *
	 * @param $get email,ba_code
	 * @return data
	 *
	 */
   public function searchAccountInchargeEmailRequest($ba_code,$get_email){
		
		$param = array();
	
		$sql = "";
		$sql.= " SELECT ";
		$sql.= "  login_code,user_name,email";
		$sql.= "   FROM users ";	 	
		$sql.= "   WHERE users.layer_code = :ba_code ";
		$sql.= "   AND users.id = :get_email ";
		$sql.= "   AND users.role_id = 4";
		$sql.= "   AND users.flag= 1";
   
		$param['ba_code'] = $ba_code;
		$param['get_email'] = $get_email;	 	

		$data = $this->query($sql,$param);	 
		return $data;
   }
  /**
  * Get Last Save user Email Account Tandosha Level  for sample register
  * @author Sandi Khaing
  *
  * @param get_user_email,
  * @return email
  *
  */
   public function search_acc_Request_email($get_user_email){	 	
		$param = array();
	
		$sql = "";
		$sql.= " SELECT ";
		$sql.= "  login_code,user_name,email";
		$sql.= "   FROM users ";	 	
		$sql.= "   WHERE  ";
		$sql.= "   users.id = :get_user_email "; 	
		$sql.= "   AND users.flag= 1";
		$param['get_user_email'] = $get_user_email;	 	

		$data = $this->query($sql,$param);	 

		
		return $data;
   }


	/**
	 * search for Emil by specific admin level
	 * @author Pan Ei Phyo
	 *
	 * @param ba_code,admin_level_id
	 * @return data
	 *
	 */
   public function searchEmailByLevel($searchTerm,$levelID,$ba_code){
	   
	   $param = array();
	   $sql = "";
	   $sql.= " SELECT ";
	   $sql.= " distinct(users.email)";
	   $sql.= " FROM users";
	   $sql.= " WHERE users.email like '".$searchTerm."%'";
	   $sql.= " AND users.role_id = :level_id";

	   if($levelID != "4"){
		   if($levelID != "3"){
			   if($levelID != "2"){
				   if ($ba_code!="") {
					   $sql.= " AND users.layer_code = :ba_code";
				   }
			   }
		   }	
	   }
	   $sql.= " AND users.flag = 1";
		

		$param['level_id'] = $levelID;

		if($levelID != "4"){
			if($levelID != "3"){
			   if($levelID != "2"){
					if ($ba_code!="") {
						$param['ba_code'] = $ba_code;
				   }
			   }
		   }
	   }

	   $data = $this->query($sql,$param);
	   return $data;
   }

	/**
	 * search for Emil by multiple admin level
	 * @author Pan Ei Phyo
	 *
	 * @param ba_code,admin_level_id array
	 * @return data
	 *
	 */
   public function searchEmailByMultiLevel($searchTerm,$levelID = '',$ba_code){
	   
	   $param = array();
	   $sql = "";
	   $sql.= " SELECT ";
	   $sql.= " distinct(users.email)";
	   $sql.= " FROM users";
	   $sql.= " WHERE users.email like '".$searchTerm."%'";
	   $sql.= " AND users.role_id IN (:level_id)";
	   if($levelID != "4"){
		   if($levelID != "3"){
			   if($levelID != "2"){
				   if ($ba_code!="") {
					   if ($ba_code!="") {
						   $sql.= " AND users.layer_code = :ba_code";
					   }
				   }
			   }
		   }
	   }
	   
	   $sql.= " AND users.flag = 1";
		
		$param['level_id'] = $levelID;

		if($levelID != "4"){
			if($levelID != "3"){
			   if($levelID != "2"){
					if ($ba_code!="") {
						$param['ba_code'] = $ba_code;
				   }
			   }
		   }
	   }	
	   $data = $this->query($sql,$param);
	   return $data;
   }
  
}