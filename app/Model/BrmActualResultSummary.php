<?php
App::uses('AppModel', 'Model');
/**
 * BrmActualResultSummary Model
 *
 */
class BrmActualResultSummary extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'brm_actual_result_summary';

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
	);
	/**
     * Save actual result data into brm_actual_result_summary table
     * @author WaiWaiMoe
     * @date 12/11/2021
     */
	public function saveActualResultSummary(){
		$sql  = "INSERT INTO `brm_actual_result_summary`
		(`hlayer_code`,`target_month`,`layer_code`,`account_code`,`transaction_key`,`submission_deadline_date`,destination_code,`amount`,`updated_date`)
		SELECT `hlayer_code`,`target_month`,`layer_code`,`account_code`,`transaction_key`,`submission_deadline_date`,destination_code, sum(amount) as amount,`updated_date` FROM brm_actual_results group by layer_code,account_code,target_month,submission_deadline_date,transaction_key,destination_code";
		$data = $this->query($sql);
        return $data;
	}

	/**
	 * select data from view for Actual Result Data
	 *
	 * @author PanEiPhyo (20200519)
	 *
	 * @param layer_code,target_month,last_year_tm from view
	 */
	public function checkActualData($layer_code,$target_month,$last_year_tm) {
		$param = array();
		$sql  = "";
		$sql .= "   SELECT count(*) AS count from brm_actual_result_summary";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND (target_month = :target_month OR target_month = :last_year_tm)";
		
		// $param['layer_code'] = $layer_code;
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
	 * @param layer_code,target_month,last_year_tm from view
	 */
	public function getMonthlyResult ($layer_code,$month,$account_code) {
		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_actual_result_summary";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND target_month = :month";

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
	 * @param layer_code,target_month,last_year_tm from view
	 */
	public function getYearlyResult ($layer_code,$start_month,$end_month,$account_code) {
		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_actual_result_summary";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND target_month >= :start_month";
		$sql .= " AND target_month <= :end_month";
		
		$param['start_month'] = $start_month;
		$param['end_month'] = $end_month;

		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
 
	}

	//khin
	public function getMonthlyResultByIndex ($layer_code,$month,$account_code,$index_no) {
		
		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_actual_result_summary";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND target_month = :month";
		if($index_no != 'NULL') {
			$sql .= " AND (transaction_key IN (".$index_no.")";
			if(strpos($index_no, 'NULL') !== false) {
				$sql .= " OR transaction_key IS NULL";
			}
			$sql .= ")";
		}else if($index_no == 'NULL') {
			$sql .= " AND transaction_key IS NULL";
		}
		
		$param['month'] = $month;
	
		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
 		
	}

	public function getYearlyResultByIndex($layer_code,$start_month,$end_month,$account_code,$index_no) {

		$param = array();
		$sql  = "";
		$sql .= " SELECT sum(amount) As total from brm_actual_result_summary";
		$sql .= " WHERE layer_code IN (".$layer_code.")";
		$sql .= " AND account_code IN (".$account_code.")";
		$sql .= " AND target_month >= :start_month";
		$sql .= " AND target_month <= :end_month";
		if($index_no != 'NULL') {
			$sql .= " AND (transaction_key IN (".$index_no.")";
			if(strpos($index_no, 'NULL') !== false) {
				$sql .= " OR transaction_key IS NULL";
			}
			$sql .= ")";
		}else if($index_no == 'NULL') {
			$sql .= " AND transaction_key IS NULL";
		}
		
		$param['start_month'] = $start_month;
		$param['end_month'] = $end_month;
	
		$data = $this->query($sql,$param);
		
		if (isset($data[0][0]['total'])) {
			return $data[0][0]['total'];
		} else {
			return 0;
		}
 
	}
}
