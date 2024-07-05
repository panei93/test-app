<?php
App::uses('AppModel', 'Model');
/**
 * Position Model
 *
 * @property LaborCostDetail $LaborCostDetail
 * @property LaborCost $LaborCost
 */
class Position extends AppModel {

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
		'position_type' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'position_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'personnel_cost' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'corporate_cost' => array(
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'LaborCostDetail' => array(
			'className' => 'LaborCostDetail',
			'foreignKey' => 'position_code',
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
			'foreignKey' => 'position_code',
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
     * get salary  position for LaborCost form, 
	 * for position dropdown change function
     * @author SST 25.8.2022
     * @return data
     */
	public function getPositionBudgetData($position_code,$target_year){
        $param = array();
        $sql  = "";
        $sql .= " SELECT Position.*";
        $sql .= " FROM positions as Position";
        // $sql .= " WHERE Position.id=:position_id";
		$sql .= " WHERE Position.position_code=:position_code";
        $sql .= " AND Position.target_year=:target_year";
        $sql .= " AND Position.flag=:flag";
        
        $param['position_code']   = $position_code;
        $param['target_year']   = $target_year;
        $param['flag']          = 1;
        $data = $this->query($sql, $param);
        return $data;
    }
	public function getUserPosition($userId, $target_year){
        $param = array();
        $sql  = "";
        $sql .= " SELECT User.id, User.user_name, positions.*";
        $sql .= " FROM users as User";
		$sql .= " JOIN positions ON positions.position_code = User.position_code";
        $sql .= " WHERE positions.target_year=:target_year AND User.id = ".$userId;
        $sql .= " AND positions.flag=:flag";
        
        $param['target_year']   = $target_year;
        $param['flag']          = 1;
        $data = $this->query($sql, $param);
        return $data;
    }

}
