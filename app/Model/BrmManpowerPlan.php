<?php
App::uses('AppModel', 'Model');
/**
 * BrmManpowerPlan Model
 *
 * @property BrmTerm $BrmTerm
 * @property BrmPosition $BrmPosition
 */
class BrmManpowerPlan extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'brm_manpower_plan';

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
		'BrmPosition' => array(
			'className' => 'BrmPosition',
			'foreignKey' => 'brm_position_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);


	public $virtualFields = array(
		'total' => "SUM(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt+month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt)",
		'first_half_total' => "SUM(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt)"
	);

	public function getPositionData($display_no,$target_year,$ba_code){
		
		$param = array();
		$sql  = "";
		$sql .= " SELECT  Pos.id,
						  Pos.brm_field_id, 
						  Pos.position_name_jp, 
						  Pos.edit_flag,
						  MP.unit_salary, 
						  MP.month_1,
						  MP.month_2,
						  MP.month_3,
						  MP.month_4,
						  MP.month_5,
						  MP.month_6,
						  MP.month_7,
						  MP.month_8,
						  MP.month_9,
						  MP.month_10,
						  MP.month_11,
						  MP.month_12,
						  MP.1st_half,
						  MP.2nd_half,
						  MP.sub_total,
						  MP.filling_date
					FROM  brm_positions as Pos
					LEFT JOIN brm_manpower_plan as MP
					ON 	  Pos.id 		  = MP.brm_position_id
					WHERE Pos.display_no  =:display_no 
					AND   Pos.target_year =:target_year 
					AND   Pos.flag        = 1 
					AND   MP.layer_code      =:layer_code ";
	
		$param['display_no']  = $display_no;
		$param['target_year'] = $target_year;
		$param['layer_code']     = $ba_code;
		
		$data = $this->query($sql,$param);
		return $data;
		
	}

	public function getOnlyPositionData($display_no,$target_year){
		
		$param = array();
		$sql  = "";
		$sql .= " SELECT  Pos.id,
						  Pos.brm_field_id, 
						  Pos.position_name_jp, 
						  Pos.edit_flag,
						  Pos.unit_salary 
					FROM  brm_positions as Pos
					WHERE Pos.display_no  =:display_no 
					AND   Pos.target_year =:target_year 
					AND   Pos.flag        = 1 ";
	
		$param['display_no']  = $display_no;
		$param['target_year'] = $target_year;
		
		$data = $this->query($sql,$param);
		
		return $data;
		
	}


	/**
	 * select data from Manpower Plan table
	 *
	 * @author PanEiPhyo (20200921)
	 *
	 * @param ba_code,term_id,$target_year,$position_mp_id from view
	 */
	public function getMonthlyResult ($ba_code,$term_id,$target_year,$position_mp_id) {

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
		$sql .= " sum(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt+month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt)/12 As sub_total ,";
		$sql .= " unit_salary ";
		$sql .= " FROM brm_manpower_plan";
		$sql .= " WHERE layer_code = :layer_code";
		$sql .= " AND brm_term_id = :brm_term_id";
		$sql .= " AND target_year = :target_year";
		$sql .= " AND brm_position_id = :brm_position_id";

		$param['layer_code'] = $ba_code;
		$param['brm_term_id'] = $term_id;
		$param['target_year'] = $target_year;
		$param['brm_position_id'] = $position_mp_id;

		$data = $this->query($sql,$param);
 
 		return $data[0];
	}

}
