<?php
App::uses('AppModel', 'Model');
/**
 * LaborCost Model
 *
 * @property Position $Position
 * @property User $User
 */
class LaborCost extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'target_year' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'position_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
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
		'adjust_name' => array(
			//'notBlank' => array(
				//'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			//),
		),
		// 'person_count' => array(
		// 	'numeric' => array(
		// 		'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
		// 	),
		// ),
		// 'b_person_count' => array(
		// 	'numeric' => array(
		// 		'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
		// 	),
		// ),
		'common_expense' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'b_person_total' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'labor_unit' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'corp_unit' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'yearly_labor_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'unit_labor_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'adjust_labor_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'yearly_corpo_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'unit_corpo_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'adjust_corpo_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
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
	// 	'Position' => array(
	// 		'className' => 'Position',
	// 		'foreignKey' => 'position_id',
	// 		'conditions' => '',
	// 		'fields' => '',
	// 		'order' => ''
	// 	),
	// 	'User' => array(
	// 		'className' => 'User',
	// 		'foreignKey' => 'user_id',
	// 		'conditions' => '',
	// 		'fields' => '',
	// 		'order' => ''
	// 	)
	// );

	//For labor cost details
	public function groupCodeList($target_year){
        $group_codes = $this->find('list', [
            'conditions' => [
                'LaborCost.flag' => 1, 
                'LaborCost.target_year' => $target_year
            ],
            'fields' => ['LaborCost.layer_code'],
            'group'  => ['layer_code'],
            'order' => ['LaborCost.id' => 'desc']
        ]);
        $layers = ClassRegistry::init('Layer');

        $data = $layers->find('list', [
            'conditions' => [
                'Layer.flag' => 1,
                'Layer.layer_type_id' => 3,
                'Layer.layer_code' => $group_codes
            ],
            'fields' => [
                'layer_code',
                'name_jp'
            ]
        ]);

        return $data;
    }
	
	/**
     * search data to show
     * @param $search_data,$user_id
     * @author 23.9.2022
     * @return data
     */
	public function getLaborCostData($search_data,$user_id, $buTermId, $position_id=1){
        $param = array();
		$sql  = "";
		$sql .= " SELECT LaborCost.*";
		if($position_id != 0){
			$sql .= " , Position.id,Position.position_name,Position.personnel_cost,Position.corporate_cost";
		}
        $sql .= "   FROM labor_costs as LaborCost ";
        // $sql .= "   LEFT JOIN users as User ";
        // $sql .="      ON LaborCost.user_id=User.id";
		if($position_id != 0){
			$sql .= "   LEFT JOIN positions as Position ";
			// $sql .= "     ON LaborCost.position_id=Position.id ";
			$sql .= "     ON LaborCost.position_code=Position.position_code ";
		}
        $sql .= "  WHERE LaborCost.target_year=:target_year";
        $sql .= "    AND LaborCost.layer_code=:layer_code";
		$sql .= "    AND LaborCost.user_id=:user_id";
		$sql .= "    AND bu_term_id =$buTermId";
        $sql .= "    AND LaborCost.flag=1";
        $sql .= "    AND LaborCost.new_user_name IS NULL";
		if($position_id != 0){
			$sql .= "    AND Position.target_year=:target_year";
		}
        $sql .= "    GROUP BY LaborCost.id"; 
        $sql .= "    ORDER BY LaborCost.id ASC";  
		$param['target_year']   = $search_data['target_year'];
        $param['layer_code']    = $search_data['layer_code'];
		$param['user_id']       = $user_id;
        $data = $this->query($sql, $param); 		
        return $data;
    }
	/**
     * to check data
     * @param $search_data,$user_id
     * @author SST 23.9.2022
     * @return data
     */
    public function getExistDataByTargetYrAndUserId($target_year,$layer_code,$user_id,$adjust_name=null, $buTermId, $userName = null){
        $param = array();
		$sql  = "";
		$sql .= " SELECT LaborCost.* ";
        $sql .= "   FROM labor_costs as LaborCost ";
        $sql .= "  WHERE LaborCost.target_year=:target_year";
        $sql .= "    AND LaborCost.layer_code=:layer_code";
		// $sql .= "    AND LaborCost.user_id=:user_id";
		// adjustment
        if($user_id == 0 && $adjust_name != null && $adjust_name != '0' && $adjust_name != '' &&  $userName == null){
		// if($user_id == 0 && $adjust_name != null &&  $userName == null){
            $sql .= "    AND LaborCost.user_id=:user_id AND LaborCost.adjust_name=:adjust_name";
            $param['adjust_name']   = $adjust_name;
			// new user
        }elseif($user_id == 0 && ($adjust_name == null || $adjust_name == '0' || $adjust_name == '') && $userName != null) {
		// }elseif($user_id == 0 && $adjust_name == null && $userName != null) {
        	$sql .= "    AND LaborCost.user_id=:user_id AND LaborCost.new_user_name=:new_user_name";
        	$param['new_user_name']   = $userName;
			// register user
    	}else {
        	$sql .= "    AND LaborCost.user_id=:user_id";
        }
		$sql .= "    AND LaborCost.bu_term_id=$buTermId";
        $sql .= "    AND LaborCost.flag=1"; 
		$param['target_year']   = $target_year;
		$param['layer_code']    = $layer_code;
        $param['user_id']       = $user_id;
        $data = $this->query($sql, $param);
        return $data;
    }
}
