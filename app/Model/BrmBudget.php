<?php
App::uses('AppModel', 'Model');
/**
 * BrmBudget Model
 *
 * @property BrmTerm $BrmTerm
 * @property BrmSaccount $BrmSaccount
 */
class BrmBudget extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'target_month' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
		'account_code' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'amount' => array(
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
		'type' => array(
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
		'BrmSaccount' => array(
			'className' => 'BrmSaccount',
			'foreignKey' => 'brm_saccount_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
	 * select data from Budget table
	 *
	 * @author PanEiPhyo (20200520)
	 *
	 * @param layer_code,term_id,target_month,last_year_tm from view
	 */
	public function checkBudgetData($layer_code,$term_id,$target_month,$last_year_tm) {
		 
		$param = array();
		$sql  = "";
		$sql .= "   SELECT count(*) AS count from brm_budgets";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND brm_term_id = :term_id";
		$sql .= " AND (target_month = :target_month OR target_month = :last_year_tm)";
		
		// $param['layer_code'] = $layer_code;
		$param['term_id'] = $term_id;
		$param['target_month'] = $target_month;
		$param['last_year_tm'] = $last_year_tm;

		$data = $this->query($sql,$param);
		
		return $data[0][0]['count'];
 
	}

	/**
	 * select data from Budget table
	 *
	 * @author PanEiPhyo (20200520)
	 *
	 * @param layer_code,term_id,target_month,last_year_tm from view
	 */
	public function getNextMonthBudget($layer_code,$term_id,$month) {
		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_budgets";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND term_id = :term_id";
		$sql .= " AND target_month = :month";
		$sql .= " AND account_code != 0000000000";
		
		// $param['layer_code'] = $layer_code;
		$param['term_id'] = $term_id;
		$param['month'] = $month;

		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
	}

	/**
	 * select data from Budget table
	 *
	 * @author PanEiPhyo (20200520)
	 *
	 * @param layer_code,term_id,target_month,last_year_tm from view
	 */
	public function getTotalYearlyBudget ($layer_code,$term_id,$start_month,$end_month) {

		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_budgets";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND term_id = :term_id";
		$sql .= " AND target_month >= :start_month";
		$sql .= " AND target_month <= :end_month";
		$sql .= " AND account_code != 0000000000";
		
		$param['term_id'] = $term_id;
		$param['start_month'] = $start_month;
		$param['end_month'] = $end_month;

		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
 
	}

	/**
	 * select data from Budget table
	 *
	 * @author PanEiPhyo (20200520)
	 *
	 * @param layer_code,term_id,target_month,last_year_tm from view
	 */
	public function getYearlyBudget ($layer_code,$term_id,$start_month,$end_month,$account_code,$index_no='null',$tab='null') {
	
		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_budgets";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND brm_term_id = :brm_term_id";
		$sql .= " AND target_month >= :start_month";
		$sql .= " AND target_month <= :end_month";
		if($tab == "Logistic" && $index_no != 'NULL') {
			$sql .= " AND (logistic_index_no IN (".$index_no.")";
			if(strpos($index_no, 'NULL') !== false) {
				$sql .= " OR logistic_index_no IS NULL";
			}
			$sql .= ")";
		}else if($tab == "Logistic" && ($index_no == 'NULL')) {
			$sql .= " AND logistic_index_no IS NULL";
		}
		
		$param['brm_term_id'] = $term_id;
		$param['start_month'] = $start_month;
		$param['end_month'] = $end_month;

		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
 
	}

	/**
	 * select data from Budget table
	 *
	 * @author PanEiPhyo (20200520)
	 *
	 * @param layer_code,term_id,target_month,last_year_tm from view
	 */
	public function getMonthlyBudget ($layer_code,$term_id,$month,$account_code,$index_no='null',$tab='null') {

		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_budgets";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND term_id = :term_id";
		$sql .= " AND target_month = :month";
		if($tab == "Logistic" && $index_no != 'NULL') {
			$sql .= " AND (logistic_index_no IN (".$index_no.")";
			if(strpos($index_no, 'NULL') !== false) {
				$sql .= " OR logistic_index_no IS NULL";
			}
			$sql .= ")";
		}else if($tab == "Logistic" && $index_no == 'NULL') {
			$sql .= " AND logistic_index_no IS NULL";
		}
		
		$param['term_id'] = $term_id;
		$param['month'] = $month;

		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
 
	}
	
	/**
	 * calculate amount from Budget table for Summary Table
	 *
	 * @author KHinHninMyo (20200904)
	 *
	 * @param $layer_code,$term_id,$smonth,$emonth,$hq_id,$sub_acc_name,
	 $account_code
	 */
	public function BudgetAmt($layer_code,$term_id,$smonth,$emonth,$hq_id,$sub_acc_name,$account_code) {
		
		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount)/100000 As total from brm_budgets";
		$sql .= " WHERE layer_code = :layer_code";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND sub_acc_name = :sub_acc_name";
		$sql .= " AND term_id = :term_id";
		$sql .= " AND target_month >= :smonth";
		$sql .= " AND target_month <= :emonth";

		$param['term_id'] = $term_id;
		$param['smonth'] = $smonth;
		$param['emonth'] = $emonth;
		$param['sub_acc_name'] = $sub_acc_name;
		$param['layer_code'] = $layer_code;

		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
	}

	public function checkApprove($term_id, $hqDepCode, $layer_code){
		$isApproved = false;
		$sql = "SELECT flag FROM brm_budget_approves WHERE brm_term_id = ".$term_id." AND hlayer_code = ".$hqDepCode." AND layer_code = '".$layer_code."'";
		$data = $this->query($sql);
		
		

		if(in_array(1,array_column(array_column($data,'brm_budget_approves'),'flag'))){
			
			$sql = "SELECT flag FROM brm_budget_approves WHERE brm_term_id = ".$term_id." AND hlayer_code = ".$hqDepCode." AND dlayer_code = 0 AND layer_code = 0";
			$data = $this->query($sql);
		}

		$data = array_column(array_column($data,'brm_budget_approves'),'flag');
		
		return $data;
	}

	public function checkQuater($data,$limit,$toDate)
	{
		$sql = "";
		$sql .= "SELECT layer_code from layers";
		$sql .= " WHERE flag = 1";
		$sql .= " AND to_date >=".$toDate;
		$sql .= " AND type_order = " .Setting::LAYER_SETTING['bottomLayer'] . " AND (";
		if($limit == 1){
			$i = 1;
			foreach($data as $key => $value){
				$sql .= " parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',".$value.",'\"%')";
				if (count($data) != $i) {
					$sql .= " OR";
				} else {
					$sql .= ")";
				}
				$i++;
			}
		}elseif ($limit == 2){
			$i = 1;
			foreach($data as $key => $value){
				$sql .= " parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['middleLayer'].", '\":\"',".$value.",'\"%')";
				if (count($data) != $i) {
					$sql .= " OR";
				} else {
					$sql .= ")";
				}
				$i++;
			}
		}else{
			$i = 1;
			foreach($data as $key => $value){
				$sql .= " parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['bottomLayer'].", '\":\"',".$value.",'\"%')";
				if (count($data) != $i) {
					$sql .= " OR";
				} else {
					$sql .= ")";
				}
				$i++;
			}
		}
		$sql .= " GROUP BY layer_code";
		$sql .= " ORDER BY id DESC";
	
		$data = $this->query($sql);
		return $data;	
	}
}
