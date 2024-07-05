<?php
App::uses('AppModel', 'Model');
/**
 * BrmBudgetSummary Model
 *
 * @property BrmTerm $BrmTerm
 */
class BrmBudgetSummary extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'brm_budget_summary';

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
		'hlayer_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'hlayer_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'dlayer_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'dlayer_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'layer_name_jp' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
		'brm_account_id_2' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'brm_account_name_jp_2' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'brm_account_id_5' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'brm_account_name_jp_5' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'brm_account_id_6' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'brm_account_name_jp_6' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
		)
	);
	public function updateSummaryData($ba_code,$term_id,$target_year) {
		$topLayer = Setting::LAYER_SETTING['topLayer'];
		$middleLayer = Setting::LAYER_SETTING['middleLayer'];
		
		$del_param = array();
		$del_sql  = "";
		$del_sql .= "DELETE from brm_budget_summary";
		$del_sql .= " WHERE layer_code = :ba_code";
		$del_sql .= " AND brm_term_id = :term_id";
		$del_sql .= " AND target_year = :target_year";
		
		// $param['ba_code'] = $ba_code;
		$del_param['term_id'] = $term_id;
		$del_param['ba_code'] = $ba_code;
		$del_param['target_year'] = $target_year;

		$data = $this->query($del_sql,$del_param);

		$this->query("INSERT INTO `brm_budget_summary` (brm_term_id, target_year, hlayer_code, hlayer_name, dlayer_code, dlayer_name, layer_name_jp, layer_code, logistic_index_no, brm_account_id_2, brm_account_name_jp_2, brm_account_id_5, brm_account_name_jp_5, brm_account_id_6, brm_account_name_jp_6, month_1_amt, month_2_amt, month_3_amt, month_4_amt, month_5_amt, month_6_amt, first_half, month_7_amt, month_8_amt, month_9_amt, month_10_amt, month_11_amt, month_12_amt, second_half, whole_total)
				SELECT 
				brm_term_id,
				target_year,
				layersHQ.layer_code,
				layersHQ.name_jp,
				layersDEPT.layer_code,
				layersDEPT.name_jp,
				layers.name_jp,
				brm_budget_primes.layer_code,
				logistic_index_no,
				sub_acc_id_2, sub_acc_name_jp_2,
				sub_acc_id_5, sub_acc_name_jp_5,
				sub_acc_id_6, sub_acc_name_jp_6,
				sum(month_1_amt) as month_1_amt,
				sum(month_2_amt) as month_2_amt,
				sum(month_3_amt) as month_3_amt,
				sum(month_4_amt) as month_4_amt,
				sum(month_5_amt) as month_5_amt,
				sum(month_6_amt) as month_6_amt,
				sum(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt) as first_half,
				sum(month_7_amt) as month_7_amt,
				sum(month_8_amt) as month_8_amt,
				sum(month_9_amt) as month_9_amt,
				sum(month_10_amt) as month_10_amt,
				sum(month_11_amt) as month_11_amt,
				sum(month_12_amt) as month_12_amt,
				sum(month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt) as second_half,
				sum(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt+month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt) as whole_total
				FROM brm_budget_primes
				LEFT JOIN `layers` ON (
					`layers`.`layer_code` = `brm_budget_primes`.`layer_code` AND
					`layers`.`flag` = 1 AND brm_budget_primes.flag=1
				)
				LEFT JOIN `layers` as `layersHQ` ON (
					layers.parent_id LIKE CONCAT('%', `layersHQ`.`layer_code`, '%') AND `layersHQ`.`type_order` = ".$topLayer." AND 
					`layersHQ`.`flag` = 1 AND layers.flag=1
				)
				LEFT JOIN `layers` as `layersDEPT` ON (
					layers.parent_id LIKE CONCAT('%', `layersDEPT`.`layer_code`, '%')  AND `layersDEPT`.`type_order` = ".$middleLayer." AND
					`layersDEPT`.`flag` = 1 AND layers.flag=1
				)LEFT JOIN 
					(SELECT subaccname2.name_jp as sub_acc_name_jp_2, brm_account_pairs.brm_account_id as sub_acc_id_2, brm_account_pairs.account_code as account_code, brm_account_pairs.group_code as group_code
					  FROM `brm_account_pairs`
					  INNER JOIN `brm_accounts` as subaccname2
					  ON (`brm_account_pairs`.`brm_account_id` = `subaccname2`.`id` AND
					  `subaccname2`.`flag` = '1')
					) as subacc2 ON (
					`subacc2`.`account_code` = `brm_budget_primes`.`account_code` AND
					`subacc2`.`group_code` = '02' AND brm_budget_primes.flag=1
				)LEFT JOIN 
					(SELECT subaccname5.name_jp as sub_acc_name_jp_5, brm_account_pairs.brm_account_id as sub_acc_id_5, brm_account_pairs.account_code as account_code, brm_account_pairs.group_code as group_code
					  FROM `brm_account_pairs`
					  INNER JOIN `brm_accounts` as subaccname5
					  ON (`brm_account_pairs`.`brm_account_id` = `subaccname5`.`id` AND
					  `subaccname5`.`flag` = '1')
					) as subacc5 ON (
					`subacc5`.`account_code` = `brm_budget_primes`.`account_code` AND
					`subacc5`.`group_code` = '05' AND brm_budget_primes.flag=1
				)LEFT JOIN 
					(SELECT subaccname6.name_jp as sub_acc_name_jp_6, brm_account_pairs.brm_account_id as sub_acc_id_6,
					 brm_account_pairs.account_code as account_code, brm_account_pairs.group_code as group_code
					  FROM `brm_account_pairs`
					  INNER JOIN `brm_accounts` as subaccname6
					  ON (`brm_account_pairs`.`brm_account_id` = `subaccname6`.`id` AND
					  `subaccname6`.`flag` = '1')
					) as subacc6 ON (
					`subacc6`.`account_code` = `brm_budget_primes`.`account_code` AND
					`subacc6`.`group_code` = '06') AND brm_budget_primes.flag=1
				WHERE brm_budget_primes.flag=1 AND brm_budget_primes.account_code!='0000000000' AND brm_budget_primes.layer_code = '".$ba_code."' AND brm_term_id = '".$term_id."' AND target_year = '".$target_year."'
				GROUP BY 
				logistic_index_no,sub_acc_id_2,sub_acc_id_5,sub_acc_id_6
				ORDER BY
				sub_acc_id_2,sub_acc_id_5,sub_acc_id_6,logistic_index_no;"
			);
		
		return true;
 
	}

}
