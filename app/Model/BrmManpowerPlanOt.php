<?php
App::uses('AppModel', 'Model');
/**
 * BrmManpowerPlanOt Model
 *
 * @property BrmTerm $BrmTerm
 * @property BrmField $BrmField
 */
class BrmManpowerPlanOt extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'brm_manpower_plan_ot';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'brm_term_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
		'brm_field_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_1_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_2_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_3_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_4_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_5_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_6_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_7_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_8_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_9_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_10_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_11_amt' => array(
			'decimal' => array(
				'rule' => array('decimal'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'month_12_amt' => array(
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
	);

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'BrmTerm' => array(
			'className' => 'BrmTerm',
			'foreignKey' => 'brm_term_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'BrmField' => array(
			'className' => 'BrmField',
			'foreignKey' => 'brm_field_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function getMonthlyResult ($ba_code,$term_id,$target_year,$field_id) {
		
		$param = array();
		$sql  = " SELECT";
		$sql .= " sum(month_1_amt) As month_1_amt ,";
		$sql .= " sum(month_2_amt) As month_2_amt ,";
		$sql .= " sum(month_3_amt) As month_3_amt ,";
		$sql .= " sum(month_4_amt) As month_4_amt ,";
		$sql .= " sum(month_5_amt) As month_5_amt ,";
		$sql .= " sum(month_6_amt) As month_6_amt ,";
		$sql .= " sum(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt)/6 As 1st_half_total ,";
		$sql .= " sum(month_7_amt) As month_7_amt ,";
		$sql .= " sum(month_8_amt) As month_8_amt ,";
		$sql .= " sum(month_9_amt) As month_9_amt ,";
		$sql .= " sum(month_10_amt) As month_10_amt ,";
		$sql .= " sum(month_11_amt) As month_11_amt ,";
		$sql .= " sum(month_12_amt) As month_12_amt ,";
		$sql .= " sum(month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt)/6 As 2nd_half_total ,";
		$sql .= " sum(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt+month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt)/12 As sub_total";
		$sql .= ", overtime_rate ";
		$sql .= " FROM brm_manpower_plan_ot";
		$sql .= " WHERE layer_code = :layer_code";
		$sql .= " AND brm_term_id = :brm_term_id";
		$sql .= " AND target_year = :target_year";
		$sql .= " AND brm_field_id = :brm_field_id";

		$param['layer_code'] = $ba_code;
		$param['brm_term_id'] = $term_id;
		$param['target_year'] = $target_year;
		$param['brm_field_id'] = $field_id;

		$data = $this->query($sql,$param);
 
 		return $data[0];
	}
}
