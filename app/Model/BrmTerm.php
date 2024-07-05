<?php
App::uses('AppModel', 'Model');
/**
 * BrmTerm Model
 *
 * @property BrmBudgetApprove $BrmBudgetApprove
 * @property BrmBudgetPrime $BrmBudgetPrime
 * @property BrmBudgetSummary $BrmBudgetSummary
 * @property BrmBudget $BrmBudget
 * @property BrmCashFlow $BrmCashFlow
 * @property BrmCeoComment $BrmCeoComment
 * @property BrmCopyTermLog $BrmCopyTermLog
 * @property BrmCronLog $BrmCronLog
 * @property BrmForecastSummary $BrmForecastSummary
 * @property BrmInvestment $BrmInvestment
 * @property BrmMainBudget $BrmMainBudget
 * @property BrmManpowerPlan $BrmManpowerPlan
 * @property BrmManpowerPlanOt $BrmManpowerPlanOt
 * @property BrmSmExplain $BrmSmExplain
 * @property BrmTermDeadline $BrmTermDeadline
 */
class BrmTerm extends AppModel {
/**
 * Term table
 *
 * @var mixed False or table name
 */
public $useTable = 'brm_terms';
public $name = 'BrmTerm';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'term_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'budget_year' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'budget_end_year' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'term' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'start_month' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'end_month' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		// 'forecast_period' => array(
		// 	'notBlank' => array(
		// 		'rule' => array('notBlank'),
		// 		//'message' => 'Your custom message here',
		// 		//'allowEmpty' => false,
		// 		//'required' => false,
		// 		//'last' => false, // Stop validation after this rule
		// 		//'on' => 'create', // Limit validation to 'create' or 'update' operations
		// 	),
		// ),
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'BrmBudgetApprove' => array(
			'className' => 'BrmBudgetApprove',
			'foreignKey' => 'brm_term_id',
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
		'BrmBudgetPrime' => array(
			'className' => 'BrmBudgetPrime',
			'foreignKey' => 'brm_term_id',
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
		'BrmBudgetSummary' => array(
			'className' => 'BrmBudgetSummary',
			'foreignKey' => 'brm_term_id',
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
		'BrmBudget' => array(
			'className' => 'BrmBudget',
			'foreignKey' => 'brm_term_id',
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
		'BrmCashFlow' => array(
			'className' => 'BrmCashFlow',
			'foreignKey' => 'brm_term_id',
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
		'BrmCeoComment' => array(
			'className' => 'BrmCeoComment',
			'foreignKey' => 'brm_term_id',
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
		'BrmCopyTermLog' => array(
			'className' => 'BrmCopyTermLog',
			'foreignKey' => 'brm_term_id',
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
		'BrmCronLog' => array(
			'className' => 'BrmCronLog',
			'foreignKey' => 'brm_term_id',
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
		'BrmForecastSummary' => array(
			'className' => 'BrmForecastSummary',
			'foreignKey' => 'brm_term_id',
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
		'BrmInvestment' => array(
			'className' => 'BrmInvestment',
			'foreignKey' => 'brm_term_id',
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
		'BrmMainBudget' => array(
			'className' => 'BrmMainBudget',
			'foreignKey' => 'brm_term_id',
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
		'BrmManpowerPlan' => array(
			'className' => 'BrmManpowerPlan',
			'foreignKey' => 'brm_term_id',
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
		'BrmManpowerPlanOt' => array(
			'className' => 'BrmManpowerPlanOt',
			'foreignKey' => 'brm_term_id',
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
		'BrmSmExplain' => array(
			'className' => 'BrmSmExplain',
			'foreignKey' => 'brm_term_id',
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
		'BrmTermDeadline' => array(
			'className' => 'BrmTermDeadline',
			'foreignKey' => 'brm_term_id',
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

	function getForecastPeriod($term_id){
		$sql ='SELECT forecast_period from brm_terms WHERE id ='.$term_id. ' AND flag = 1';
		$data = $this->query($sql);
	 	return $data[0]['brm_terms']['forecast_period'];


	}
}
