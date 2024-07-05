<?php
App::uses('AppModel', 'Model');
/**
 * Layer Model
 *
 * @property LayerType $LayerType
 * @property Layer $ParentLayer
 * @property Layer $ChildLayer
 */
class Layer extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'layer_type_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'layer_code' => array(
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
		'name_en' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'parent_id' => array(
			// 'notBlank' => array(
			// 	'rule' => array('notBlank'),
			// 	//'message' => 'Your custom message here',
			// 	//'allowEmpty' => false,
			// 	//'required' => false,
			// 	//'last' => false, // Stop validation after this rule
			// 	//'on' => 'create', // Limit validation to 'create' or 'update' operations
			// ),
		),
		'from_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'to_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'managers' => array(
			//'notBlank' => array(
				//'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			//),
		),
		'item_1' => array(
			//'notBlank' => array(
				//'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			//),
		),
		'item_2' => array(
			//'notBlank' => array(
				//'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			//),
		),
		'form' => array(
			//'notBlank' => array(
				//'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			//),
		),
		'object' => array(
			//'notBlank' => array(
				//'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			//),
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
	// 	'LayerType' => array(
	// 		'className' => 'LayerType',
	// 		'foreignKey' => 'layer_type_id',
	// 		'conditions' => '',
	// 		'fields' => '',
	// 		'order' => ''
	// 	),
	// );

/**
 * hasMany associations
 *
 * @var array
 */
	
	//SST 5.9.2022 for group name dropdown list 
    public function getLayerCodeList($target_year){
		// $_SESSION['LOGIN_ID'] = 1741;
		$layer_type_sql = "";
        $layer_type_sql .= "SELECT User.*";
        $layer_type_sql .= "  FROM users as User where User.id = ".$_SESSION['LOGIN_ID'];
		$layer_type_id = $this->query($layer_type_sql)[0]['User']['layer_type_order'];
		
		if($layer_type_id == 3){
			$sub_query = "   AND Layer.layer_code regexp 
			(SELECT REPLACE(users.layer_code,'/','|') FROM users WHERE users.id = ".$_SESSION['LOGIN_ID'].")";
		}elseif($layer_type_id == 4){
			$sub_query = " AND Layer.layer_code regexp (
				SELECT 
				replace(group_concat(json_unquote((json_extract(layers.parent_id,'$.L3')))),',','|') FROM layers WHERE layers.layer_code regexp 
				(SELECT REPLACE(users.layer_code,'/','|') FROM users WHERE users.id =".$_SESSION['LOGIN_ID']."))";
		}else{
			$sub_query ="   AND Layer.parent_id regexp (SELECT REPLACE(users.layer_code,'/','|') FROM users WHERE users.id = ".$_SESSION['LOGIN_ID'].")";
		}
		
        $sql = "";
        $sql .= "SELECT Layer.* ";
        $sql .= "  FROM layers as Layer";
        $sql .= "  LEFT JOIN layer_types as LayerType ";
        $sql .= "    ON LayerType.id=Layer.layer_type_id ";
        $sql .= " WHERE Layer.layer_type_id = '".Setting::BU_LAYER_SETTING['topLayer']."'";//only group level
        $sql .= "   AND DATE_FORMAT(Layer.from_date, '%Y-%m') <= '".$target_year[0]."'";
        $sql .= "   AND DATE_FORMAT(Layer.to_date, '%Y-%m') >= '".$target_year[1]."'";
        $sql .= "   AND Layer.flag=1 ";
        $sql .= "   AND LayerType.flag=1 ";
		$sql .= $sub_query;
		$sql .= " GROUP By Layer.layer_code ";
        $sql .= " ORDER By Layer.layer_order, Layer.layer_code ";
        $data = $this->query($sql);
		// pr($data);
		// die();
        return $data;
    }
	//SST 15.9.2022 for excel file name
    public function getLayerName($layer_code){        
        $sql = "";
        $sql .= "SELECT Layer.* ";
        $sql .= "  FROM layers as Layer";
        $sql .= " WHERE Layer.layer_code='$layer_code'";
        $sql .= "   AND Layer.flag=1 ";
        $data = $this->query($sql);        
        return $data;
    }
    public function getLayerCodeListByPermissions($layer_code){        
        $sql = "";
        $sql .= "SELECT Layer.* ";
        $sql .= "  FROM layers as Layer";
        $sql .= " WHERE Layer.layer_code in ('$layer_code')";
        $sql .= "   AND Layer.flag=1 AND Layer.bu_status=1";
		$sql .= " ORDER BY Layer.type_order, Layer.layer_order, Layer.layer_code";
        $data = $this->query($sql);        
        return $data;
    }
	public function getAccountByLayer($headQuarterCode, $target_year, $subAccName = NULL){
		$sql = "";
		$sql ="SELECT brm_account_setups.*, brm_accounts.id, brm_accounts.name_jp, brm_accounts.type, brm_accounts.calculation_method, brm_accounts.auto_changed
		FROM layers
		JOIN brm_account_setups ON layers.layer_code = brm_account_setups.hlayer_code
		JOIN  brm_accounts ON  brm_accounts.id = brm_account_setups.brm_account_id
		WHERE layers.layer_code= :headQuarterCode
		AND brm_account_setups.brm_saccount_id=0 
		AND brm_accounts.flag= 1 
		AND brm_account_setups.target_year= :target_year";
		if($subAccName != NULL){
			$sql .=" AND brm_accounts.name_jp= :name_jp";
		}
		$sql .=" ORDER BY brm_account_setups.order, brm_account_setups.sub_order";

		$param = array();
		$param['headQuarterCode'] = $headQuarterCode;
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
	// public function getLayerCodeWithParent($getToplayerCode){      
		
	// 	$typeOrder = Setting::LAYER_SETTING['bottomLayer'];
	// 	$sql = "";
	// 	$subsql = array('0');

	// 	foreach($getToplayerCode as $code){
	// 		$subsql[] .= " parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',$code,'\"%') OR";
	// 	}
       
    //     $sql .= "SELECT Layer.layer_code ";
    //     $sql .= "  FROM layers as Layer";
    //     $sql .= " WHERE Layer.type_order  = $typeOrder ";
    //     $sql .= "   AND Layer.flag=1 ";
    //     $sql .= "   AND  ";
    //     $sql .= "   (  ";
		
    //     $sql .= "   )  ";
    //     $data = $this->query($sql);    pr($sql);die;         
    //     return $data;
    // }

	/**
	 * layer_code/layer_name For filter in Labor Cost Detail
	 * 16.9.2022
	 * Wai Lwin Aung
	 */
	// public $virtualFields = array(
	// 	'code_name' => 'CONCAT(Layer.Layer_code, "/", Layer.name_jp)'
	// );


}
