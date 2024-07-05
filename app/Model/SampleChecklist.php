<?php
App::uses('AppModel', 'Model');
/**
 * SampleChecklist Model
 *
 * @property Sample $Sample
 * @property Result $Result
 */
class SampleChecklist extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'sample_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'result_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'updated_by' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
	public $belongsTo = array(
		'Sample' => array(
			'className' => 'Sample',
			'foreignKey' => 'sample_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'SampleTestResult' => array(
			'className' => 'SampleTestResult',
			'foreignKey' => 'result_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
     * get checklist  comment from text 
     *
     * @author Sandi
     *
     * @param
     *
     * @return data
     */

    public function getCheckComment($layer_code,$period, $category) {

		$param = array();
  
		$sql  = "";
		$sql .= " SELECT ";
		$sql .= " tr.id,tr.sample_id,improvement_situation1,improvement_situation2 ";     
		$sql .= " FROM  sample_checklists as ch "; 
		$sql .= " JOIN sample_test_results as tr ON ch.sample_id = tr.sample_id "; 
		$sql .= " JOIN samples as sd ON tr.sample_id = sd.id  "; 
		$sql .= " WHERE sd.layer_code = :layer_code and DATE_FORMAT(sd.period,'%Y-%m') = :period and sd.category = :category ";
		$sql .= " AND tr.flag = '3' AND sd.flag >= '7' ORDER BY sd.id ASC ";    
  
		$param['layer_code'] = $layer_code;
		$param['period'] = $period;
	    $param['category'] = $category;
		$data = $this->query($sql,$param);  
		return $data;
	  }
  
	/**
	 * show table checklist first tab
	 *
	 * @author Sandi
	 *
	 * @param
	 *
	 * @return data
	 */
	public function GetChecklistData($layer_code,$period, $category) {

	$param = array();

	$sql  = "";
	$sql .= " SELECT ";
	$sql .= "  tr.point_out1,tr.report_necessary1, DATE_FORMAT(tr.deadline_date1, '%Y-%m-%d') as deadline_date1,tr.flag,";
	$sql .= "  sd.destination_name,sd.account_item,sd.period,tr.id,sd.index_no,";
	$sql .= " tr.sample_id,sd.flag ,report_necessary2,";
	$sql .= " ch.improvement_situation1,ch.flag,ch.improvement_situation2 ";
	$sql .= " FROM samples as sd ";
	$sql .= "  JOIN sample_test_results as tr ON tr.sample_id = sd.id  ";   
	$sql .= "  LEFT JOIN sample_checklists as ch ON sd.id = ch.sample_id ";
	$sql .= " WHERE sd.layer_code = :layer_code AND sd.category = :category AND  DATE_FORMAT(sd.period,'%Y-%m') = :period   ";
	$sql .= " AND  sd.flag >= 5  ORDER BY sd.id ASC ";//tr.flag = 3 AND sd.flag >= 7
	
	$currentTimestamp = date('Y-m-d H:i:s');     

	$param['layer_code'] = $layer_code;
	$param['period'] = $period;
	$param['category'] = $category;

	$data = $this->query($sql,$param);
	
	return $data;
	}

	/**
	 * show table checklist first tab
	 *
	 * @author Sandi
	 *
	 * @param
	 *
	 * @return data
	 */
	public function GetChecklistDataForallBA($period) {

	$param = array();

	$sql  = "";
	$sql .= " SELECT ";
	$sql .= "  tr.point_out1,tr.report_necessary1, DATE_FORMAT(tr.deadline_date1, '%Y-%m-%d') as deadline_date1,tr.flag,";
	$sql .= "  sd.destination_name,sd.account_item,sd.period,tr.id,sd.index_no,";
	$sql .= " tr.sample_id,sd.flag ,report_necessary2,";
	$sql .= " ch.improvement_situation1,ch.flag,ch.improvement_situation2 ";
	$sql .= " FROM samples as sd ";
	$sql .= "  JOIN sample_test_results as tr ON tr.sample_id = sd.id  ";   
	$sql .= "  LEFT JOIN sample_checklists as ch ON sd.id = ch.sample_id ";
	$sql .= " WHERE  DATE_FORMAT(sd.period,'%Y-%m') = :period   ";
	$sql .= " AND  sd.flag >= 5  ORDER BY sd.id ASC ";//tr.flag = 3 AND sd.flag >= 7
	
	$currentTimestamp = date('Y-m-d H:i:s');     

	$param['period'] = $period;

	$data = $this->query($sql,$param);
	
	return $data;
	}
	//checklist tab hide or show

	public function TabHideCh_flag($layer_code,$period, $category) {

	$param = array();

	$sql  = "";
	$sql .= " SELECT ";
	$sql .= "  tr.report_necessary2 ,tr.report_necessary1,sd.flag ";

	$sql .= " FROM samples as sd "; 
	$sql .= "  JOIN sample_test_results as tr ON tr.sample_id = sd.id  ";   
	$sql .= "  LEFT JOIN sample_checklists as ch ON sd.id = ch.sample_id ";
	$sql .= " WHERE sd.layer_code = :layer_code AND sd.category = :category AND  DATE_FORMAT(sd.period,'%Y-%m') = :period   ";
	$sql .= "  AND sd.flag >= 5 ORDER BY sd.id ASC ";//AND tr.flag = '3'
	
	$currentTimestamp = date('Y-m-d H:i:s');       

	$param['layer_code'] = $layer_code;
	$param['period'] = $period;
	$param['category'] = $category;
	$data = $this->query($sql,$param);
	
	return $data;
	}

	//check sample_id is exist or not in DB
	public function GetChkSampleId($sample_id) {

	$param = array();

	$sql  = "";
	$sql .= "   SELECT  sample_id";
	$sql .= "   FROM sample_checklists";
	$sql .= "   WHERE flag = 1";
	$sql .= "   AND sample_id = :sample_id";

	$param['sample_id'] = $sample_id;
	$data = $this->query($sql,$param);
	return $data;
	}

	public function UpdateCheckListData($sample_id,$cmt,$user_id) {
		$param = array();

		$sql  = "";
		$sql .= " UPDATE sample_checklists as ch ,sample_test_results as tr ";
		$sql .= " 	 SET ch.improvement_situation1 = :improve_1  ,tr.report_times = 1 ,";
		$sql .= "		 ch.updated_by    = :user_id,  ";  
		$sql .= "		 ch.updated_date  = :updated_date ";
		$sql .= "  WHERE ch.sample_id = :sample_id ";

		$currentTimestamp = date('Y-m-d H:i:s');
		$param['sample_id'] = $sample_id;
		$param['improve_1'] = $cmt;
		$param['user_id'] = $user_id;
		$param['updated_date'] = $currentTimestamp;

		$this->query($sql,$param);
	}

	public function UpdateCheckListData_II($sample_id,$cmt_2,$user_id) { 
	$param = array();
	if(is_array($sample_id)) {
		$sa_id = implode(',', $sample_id);
	} else {
		$sa_id = $sample_id;
	}
	$sql  = "";
	$sql .= " UPDATE sample_checklists ";
	$sql .= " 	 SET improvement_situation2 = :improve_2, flag = 1, ";
	$sql .= "		 updated_by = :updated_by, ";
	$sql .= "		 updated_date = :updated_date ";
	$sql .= "  WHERE (flag = 4 || flag = 1 ) AND sample_id IN (".$sa_id.")";

	$currentTimestamp = date('Y-m-d H:i:s');
	$param['improve_2'] = $cmt_2;
	$param['updated_by'] = $user_id;
	$param['updated_date'] = $currentTimestamp;

	$this->query($sql,$param);
	}

	/**
	 * Checklist Textarea comment have check level show
	 *
	 * @author Sandi
	 *
	 * @param
	 *
	 * @return data
	 */
	public function textCommnetStageinfo() {   
	$sql  = ""; 
	$sql .= "   SELECT  sample_id, result_id,flag";
	$sql .= "   FROM sample_checklists";
	$sql .= "   WHERE flag = '1'";      

	$data = $this->query($sql);
	return $data;
	}
	/**
	 * Flg change Test reuslt in report time add 1
	 *
	 * @author Sandi
	 *
	 * @param
	 *
	 * @return data
	 */
	public function updatecheckTestData($user_level,$period,$layer_name) {  
	$user_level ="4";
	$param = array();
	$sql  = "";
	$sql .= "UPDATE sample_test_results as tr";
	$sql .= " SET ";
	$sql .= " tr.report_times = 1";     

	$sql .= " FROM  sample_test_results as tr ";

	$sql .= " JOIN samples as sd ON tr.sample_id = sd.id  ";        
	$sql .= " WHERE sd.flag = 7 AND tr.flag = '3' ";
	$sql .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period ";

	$currentTimestamp = date('Y-m-d H:i:s');
	$param['updated_date'] = $currentTimestamp; 

	$data = $this->query($sql,$param);  
	return $data;

	}
	public function tab_butsampleID() {

	$param = array();
	$sql  = ""; 
	$sql .= "   SELECT  flag";
	$sql .= "   FROM samples";
	$sql .= "   WHERE  flag = '7'"; 
	
	$data = $this->query($sql);
	return $data;
	} 

	public function review_secondApprove($sample_id, $admin_id) {
	
	$param = array();
	$sql  = "";
	$sql .= "UPDATE sample_checklists as ch ";
	$sql .= " SET ";
	$sql .= " ch.flag = 2, updated_date=:updated_date, updated_by = :admin_id";     

	$sql .= " WHERE ch.flag = 1 AND ch.sample_id IN (".implode(',', $sample_id).") " ;
	$currentTimestamp = date('Y-m-d H:i:s');
	$param['updated_date'] = $currentTimestamp; 
	$param['admin_id'] = $admin_id;

	$this->query($sql,$param);
	if($this->getAffectedRows() > 0) {
		return true;
	} else {
		return false;
	}
	
	}

	public function thirdApproveflag($sample_id,$user_id) { 
	$param = array();
	$currentTimestamp = date('Y-m-d H:i:s');
	$sql  = "";
	$sql .= "UPDATE samples ";
	$sql .= " SET ";
	$sql .= " updated_by = :updated_by,";
	$sql .= " updated_date = :updated_date,";
	$sql .= " flag = 8 ";  
	$sql .= " WHERE flag=7 AND id IN (".implode(',',$sample_id).")" ;     
	$param['updated_by'] = $user_id;
	$param['updated_date'] = $currentTimestamp;
	$this->query($sql,$param);     
	$sample_effect_row = $this->getAffectedRows();

	$chk_list_param = [];
	$chk_list_sql = "UPDATE sample_checklists  SET flag=3, updated_date=:upd_date, updated_by=:user_id WHERE flag=2 AND sample_id IN (".implode(',',$sample_id).")";
	$chk_list_param['user_id'] = $user_id;
	$chk_list_param['upd_date'] = $currentTimestamp;
	$this->query($chk_list_sql, $chk_list_param);
	$chk_list_effect_row =  $this->getAffectedRows();
		
	if($sample_effect_row > 0 && $chk_list_effect_row > 0) {
		# if updated
		return true;
	} else {
		# if not updte
		return false;
	}
	}

	public function th_App_flagCancle($sample_id,$user_id) {    
	$param = array();
	$currentTimestamp = date('Y-m-d H:i:s');
	$sql  = "";
	$sql .= "UPDATE samples ";
	$sql .= " SET ";
	$sql .= " updated_by = :updated_by,";
	$sql .= " updated_date = :updated_date,";
	$sql .= " flag = 7 ";  
	$sql .= " WHERE flag=8 AND id IN (".implode(',',$sample_id).")" ;     
	$param['updated_by'] = $user_id;
	$param['updated_date'] = $currentTimestamp;
	$this->query($sql,$param);   
	$sample_effect_row = $this->getAffectedRows();

	$chk_list_param = [];
	$chk_list_sql = "UPDATE sample_checklists  SET flag=1, updated_date=:upd_date, updated_by=:user_id WHERE flag=3 AND sample_id IN (".implode(',',$sample_id).")";
	$chk_list_param['user_id'] = $user_id;
	$chk_list_param['upd_date'] = $currentTimestamp;
	$this->query($chk_list_sql, $chk_list_param);
	$chk_list_effect_row =  $this->getAffectedRows();
	
	if($sample_effect_row > 0 && $chk_list_effect_row > 0) {
		# if updated
		return true;
	} else {
		# if not updte
		return false;
	}
	}
	//added by Hein Htet Ko
	public function th_App_flagReject($sample_id,$user_id) {    
	$param = array();
	$currentTimestamp = date('Y-m-d H:i:s');
	$sql  = "";
	$sql .= "UPDATE samples ";
	$sql .= " SET ";
	$sql .= " updated_by = :updated_by,";
	$sql .= " updated_date = :updated_date";
	/*$sql .= " flag = 7 ";  */
	$sql .= " WHERE flag=7 AND id IN (".implode(',',$sample_id).")" ;     
	$param['updated_by'] = $user_id;
	$param['updated_date'] = $currentTimestamp;
	$this->query($sql,$param);   
	$sample_effect_row = $this->getAffectedRows();

	$chk_list_param = [];
	$chk_list_sql = "UPDATE sample_checklists  SET flag=1, updated_date=:upd_date, updated_by=:user_id WHERE flag=2 AND sample_id IN (".implode(',',$sample_id).")";
	$chk_list_param['user_id'] = $user_id;
	$chk_list_param['upd_date'] = $currentTimestamp;
	$this->query($chk_list_sql, $chk_list_param);
	$chk_list_effect_row =  $this->getAffectedRows();
	
	if($sample_effect_row > 0 && $chk_list_effect_row > 0) {
		# if updated
		return true;
	} else {
		# if not updte
		return false;
	}
	}


	/*--------------------Report second time  checklist tab2 -------------------------------*/
	public function tabChangevalide_ch() {

	$param = array();

	$sql  = "";
	$sql  = " SELECT ";
	$sql .= " tr.report_necessary2"; 
	$sql .= "  FROM  sample_test_results as tr ";         
	$sql .= " JOIN samples as sd ON tr.sample_id = sd.id ";
	$sql .= " JOIN sample_checklists as ch ON tr.id = ch.result_id ";       
	$sql .= " WHERE  sd.flag >= 6 AND tr.flag = 3 AND sd.flag = 7  AND tr.report_necessary2 = 2  ORDER BY tr.id ASC";
	
	$data   = $this->query($sql,$param);  
	return $data;
	}

	public function checkTabShow2($layer_code,$period, $category) { 
	$param = array();

	$sql  = "";
	$sql .= " SELECT ";
	$sql .= " tr.point_out2,tr.report_necessary2, DATE_FORMAT(tr.deadline_date2, '%Y-%m-%d') as deadline_date2,";   
	$sql .= " sd.destination_name,sd.period,sd.flag,tr.id,tr.sample_id, sd.flag,sd.index_no,sd.account_item,";
	$sql .= " ch.improvement_situation2,ch.flag ";
	$sql .= "  FROM  samples as sd "; 
	$sql .= " JOIN  sample_test_results as tr ON tr.sample_id = sd.id ";
	$sql .= " LEFT JOIN sample_checklists as ch ON sd.id = ch.sample_id ";       
	$sql .= " WHERE sd.layer_code = :layer_code and sd.category = :category and DATE_FORMAT(sd.period,'%Y-%m') = :period ";
	$sql .= " AND (tr.report_times = 2 OR tr.report_times = 3) AND sd.flag <> 0  ORDER BY tr.id ASC";

	$param['layer_code'] = $layer_code;
	$param['period'] = $period;
	$param['category'] = $category;

	$data   = $this->query($sql,$param);  

	return $data;
	}

	//save second time form checklist tab 2 to checklist table .
	public function second_savechecklist($param) {  
	$bind = array();
	$sample_id_arr = $param['ch_two_sampleid'];
	$one_arr = $param['checknew_commenttwo'];      

	$result_id_arr = $param['ch_two_resultid'];

	$cnt_sample = count($sample_id_arr) ;      
	$sql  = "";
	$sql .= " UPDATE sample_checklists SET improvement_situation2 = (CASE ";
	for($i=0; $i<$cnt_sample; $i++) 
	{
		$s_id = $sample_id_arr[$i];
		$cmt_one = $one_arr[$i];
		$rsl_id = $result_id_arr[$i];
		$sql .= "WHEN sample_id = ? AND result_id = ? AND flag = ? THEN ? ";
		$bind[] = $s_id;
		$bind[] = $rsl_id;
		$bind[] = 1;
		$bind[] = $cmt_one;
	}      
	$sql .= "END), ";
	$sql .= "updated_date = ? ";
	$bind[] = date("Y-m-d H:i:s");
	$sql .= " WHERE flag = 1 AND sample_id IN (".implode(',', $sample_id_arr).") ";
	$sql .= " AND id IN (".implode(',', $result_id_arr).") ";


	$data=$this->query($sql,$bind);

	return $data ;     

	}
	/*--------------------------------Warp up page  data ----------------------------------*/
// warpup page data page show first

	public function warpUpShow($period,$layer_code,$user_level, $category) {
	$param = array();

	$sql  = "";
	$sql  = " SELECT ";
	$sql .= " tr.testresult_finish, tr.flag,tr.point_out1, tr.report_necessary1, tr.report_times, DATE_FORMAT(tr.deadline_date1, '%Y-%m-%d') as deadline_date1, DATE_FORMAT(tr.deadline_date2, '%Y-%m-%d') as deadline_date2,sd.destination_name,sd.index_no, sd.account_item,sd.period,ch.id,ch.sample_id,ch.result_id,ch.improvement_situation1,ch.improvement_situation2,tr.report_necessary2,tr.point_out2, ch.flag ,sd.layer_code ,sd.flag";  

	$sql .= " FROM sample_checklists as ch";
	$sql .= " LEFT JOIN sample_test_results as tr ON ch.sample_id = tr.sample_id ";   
	$sql .= " JOIN samples as sd ON sd.id = tr.sample_id ";      
	$sql .= " WHERE tr.flag <= 3 AND sd.flag >= 5  AND  sd.layer_code =:layer_code AND sd.category =:category AND DATE_FORMAT(sd.period,'%Y-%m') = :period  ORDER BY sd.id ASC " ;

	$param['period']  = $period;
	$param['layer_code'] = $layer_code; 
	$param['category'] = $category; 
	$data = $this->query($sql,$param);

	return $data;
	}

	/* Tabe hide data warp up*/

	public function warp_tabhide($period,$layer_code,$user_level, $category) {
		$param = array();

		$sql  = "";
		$sql .= " SELECT ";
		$sql .= "  tr.report_necessary2, tr.report_times, sd.flag, ch.flag,";
		$sql .= "  ch.improvement_situation1, ch.improvement_situation2";
		$sql .= "  FROM samples as sd "; 
		$sql .= "  JOIN sample_test_results as tr ON tr.sample_id = sd.id  ";   
		$sql .= "  LEFT JOIN sample_checklists as ch ON sd.id = ch.sample_id ";
		$sql .= "  WHERE sd.layer_code = :layer_code AND sd.category = :category AND  DATE_FORMAT(sd.period,'%Y-%m') = :period AND (sd.flag = 8 OR sd.flag = 5)";
		$sql .= " ORDER BY sd.id ASC LIMIT 1";

		$param['layer_code'] = $layer_code;
		$param['period'] = $period;
		$param['category'] = $category;
		$data = $this->query($sql,$param);
		return $data;
	}

	/* second time  tag */
	public function secondtimeWarpUpShow($period,$layer_code,$user_level, $category) {
	$param = array();

	$sql  = "";
	$sql  = " SELECT ";
	$sql .= "  tr.point_out2, tr.report_necessary1, tr.report_necessary2, DATE_FORMAT(tr.deadline_date2, '%Y-%m-%d') as deadline_date2,DATE_FORMAT(tr.deadline_date3, '%Y-%m-%d') as deadline_date3,tr.point_out3,sd.destination_name,sd.account_item,sd.index_no, sd.period,ch.id,ch.sample_id,ch.result_id,ch.improvement_situation2, ch.improvement_situation1,ch.flag,sd.flag"; 

	$sql .= " FROM sample_checklists as ch ";
	$sql .= " JOIN sample_test_results as tr ON ch.sample_id = tr.sample_id ";
	$sql .= " JOIN samples as sd ON sd.id = tr.sample_id ";      

	$sql .= " WHERE tr.flag <= '3' AND sd.flag >= 5  AND  sd.layer_code =:layer_code AND sd.category =:category AND DATE_FORMAT(sd.period,'%Y-%m') = :period  ORDER BY sd.id ASC ";
	$param['layer_code'] =$layer_code;
	$param['period'] = $period;
	$param['category'] = $category;

	$data = $this->query($sql,$param);
	//pr($data);die();
	return $data;
	}

	public function warp_save_review($param, $period, $layer_code, $category) {

	# update sample_test_results
	$bind = array();
	$sample_id_arr = $param['warp_sample_id'];
	$one_arr       = $param['warp_Commentone'];        
	$two_arr       = $param['warp_Commenttwo'];
	$warp_rp2_arr  = $param["warp_rp2_check1"];  
	$admin_id    = $param['admin_id'];
	$result_id_arr = $param['warp_result_id'];
	$sample_updates = $param['sample_updates'];    
   
	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		$cnt_sample = count($sample_id_arr) ; 
		$sql  = "";
		$sql .= " UPDATE sample_test_results SET point_out2 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++)
		{
			$s_id = $sample_id_arr[$i];
			$cmt_one = $one_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] = $cmt_one;
		}
		$sql .= "END), ";
		$sql .= "deadline_date2 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) {
			$s_id = $sample_id_arr[$i];
			if(!empty($two_arr[$i])){
			$cmt_two = $two_arr[$i];
			}else{
			$cmt_two = '0000-00-00 00:00:00';

			}

			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] =$cmt_two;
		}
		$sql .= "END), ";
		
		$sql .= "report_necessary2 = (CASE ";
		
		for($i=0; $i<$cnt_sample; $i++) 
		{
			$s_id =$sample_id_arr[$i];

			$rsl_id = $result_id_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;
			if (!empty($warp_rp2_arr)) { 
			if(in_array($s_id,$warp_rp2_arr)) {
				$bind[] = 1;
			} else {
				$bind[] = 0;
			} 
			}
			else
			{
			$bind[] = 0;
			}
		}
		$sql .= "END), ";
		$sql .= "testresult_finish = (CASE ";
		$count_sample = count($sample_updates);
		for($i=0; $i<$cnt_sample; $i++) {
			$s_id = $sample_id_arr[$i];
			$sam_id = $sample_updates[$i]['sample_id'];
			$finish_flag = $sample_updates[$i]['testresult_finish'];
			$sql .= "WHEN sample_id = ? THEN ? ";
			$bind[] = $s_id;
			if($s_id == $sam_id) {
			$bind[] = $finish_flag;
			} else {
			$bind[] = 0;
			}
		}
		$sql .= "END), ";
		$sql .= "flag = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) 
		{
			$s_id = $sample_id_arr[$i];
			$cmt_two = $two_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;        
			$bind[] = 2;
		}
		$sql .= "END), ";
		$sql .= "updated_date = ?, ";
		$bind[] = date("Y-m-d H:i:s");
		$sql .= "updated_by = ? ";
		$bind[] = $admin_id;
		$sql .= " WHERE  sample_id IN (".implode(',', $sample_id_arr).") ";
		$sql .= " AND id IN (".implode(',', $result_id_arr).") "; 
		$this->query($sql,$bind);
		$effect_row = $this->getAffectedRows();
		if($effect_row < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}

		# update samples
		$count_upd = count($sample_updates);
		$qryBind = array();
		$qrySample  = "";
		$qrySample .= " UPDATE samples as sd ";
		$qrySample .= " SET ";
		$qrySample .= "sd.flag = (CASE ";
		for($f=0; $f<$count_upd; $f++) {
		$sid = $sample_updates[$f]['sample_id'];
		$flag = $sample_updates[$f]['flag'];
		$qrySample .= "WHEN sd.id = ?  THEN ? ";
		$qryBind[] = $sid;
		$qryBind[] = $flag;
		}
		$qrySample .= "END), "; 
		$qrySample .= "sd.complete_date = (CASE ";
		for($f=0; $f<$count_upd; $f++) {
		$sid = $sample_updates[$f]['sample_id'];
		$complete_date = $sample_updates[$f]['complete_date'];
		$qrySample .= "WHEN sd.id = ?  THEN ? ";
		$qryBind[] = $sid;
		$qryBind[] = $complete_date;
		}
		$qrySample .= "END), ";
		$qrySample .= "sd.updated_date = ?, ";
		$qryBind[] = date("Y-m-d H:i:s");
		$qrySample .= "sd.updated_by = ? ";
		$qryBind[] = $admin_id;
		$qrySample .= "WHERE DATE_FORMAT(sd.period,'%Y-%m') = ? AND sd.layer_code = ? AND sd.category = ? ";
		$qryBind[] = $period;
		$qryBind[] = $layer_code;
		$qryBind[] = $category;
		$qrySample .= "AND sd.id IN (".implode(',', $sample_id_arr).") ";
		$this->query($qrySample,$qryBind);
		$effect_row = $this->getAffectedRows();
		if($effect_row < 1) {
		throw new Exception("data is not update in samples", 1);
		}

		$rsl = true;//save success

		$testRslDB->commit();
		$sampleDB->commit();
	} catch (Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();
		$rsl = false;//save fail
		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $rsl;
	}

	public function sec_tab_review_save($param, $period, $layer_code, $category) {
	# update sample_test_results
	$bind = array();
	$sample_id_arr = $param['warp_sample_id'];
	$one_arr       = $param['point_out_3'];        
	$two_arr       = $param['deadline_date3'];

	$admin_id    = $param['admin_id'];
	$result_id_arr = $param['warp_result_id'];     

	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		$cnt_sample = count($sample_id_arr) ; 
		$sql  = "";
		$sql .= " UPDATE sample_test_results SET point_out3 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++)
		{
			$s_id = $sample_id_arr[$i];
			$cmt_one = $one_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] = $cmt_one;
		}
		$sql .= "END), ";
		$sql .= "deadline_date3 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) {
			$s_id = $sample_id_arr[$i];
			if(!empty($two_arr[$i])){
			$cmt_two = $two_arr[$i];
			}else{
			$cmt_two = '0000-00-00 00:00:00';

			}
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] =$cmt_two;
		}
		$sql .= "END), ";
		# when review from second tab all testresult_finish must be 1
		$sql .= "testresult_finish = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) {
			$s_id = $sample_id_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] = 1;
		}
		$sql .= "END), ";
		$sql .= "flag = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) 
		{
			$s_id = $sample_id_arr[$i];
			$cmt_two = $two_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;        
			$bind[] = 2;
		}
		$sql .= "END), ";
		$sql .= "updated_date = ?, ";
		$bind[] = date("Y-m-d H:i:s");
		$sql .= "updated_by = ? ";
		$bind[] = $admin_id;
		$sql .= " WHERE  sample_id IN (".implode(',', $sample_id_arr).") ";
		$sql .= " AND id IN (".implode(',', $result_id_arr).") "; 
		$data = $this->query($sql,$bind);
		$row = $this->getAffectedRows();
		if($row < 1) {
		throw new Exception("data is not save in sample_test_results", 1);
		}

		# update samples (flag 5 -> 9)
		# when completion flag is check
		$complete_flag = $param['complete_flag'];
		if(is_array($complete_flag)) {
		$sam_id = implode(',', $complete_flag);
		} else {
		$sam_id = $complete_flag;
		}
		$bind_9 = array();
		$qry_to_9  = "";
		$qry_to_9 .= " UPDATE samples as sd ";
		$qry_to_9 .= " SET ";
		$qry_to_9 .= " sd.flag = 9 ,updated_date = :updated_date ,updated_by= :updated_by ,complete_date = :complete_date"; 
		$qry_to_9 .= " WHERE (sd.flag = 5 OR sd.flag = 9)";  
		$qry_to_9 .= "  AND DATE_FORMAT(sd.period,'%Y-%m') = :period AND sd.layer_code = :layer_code AND sd.category = :category ";
		$qry_to_9 .= "  AND sd.id IN (".$sam_id.")"; 
		$currentTimestamp = date('Y-m-d H:i:s');
		$bind_9['period']        = $period;        
		$bind_9['layer_code']       = $layer_code;
		$bind_9['category']       = $category;
		$bind_9['updated_by']    = $admin_id;
		$bind_9['updated_date']  = $currentTimestamp;
		$bind_9['complete_date'] = $currentTimestamp;
		$this->query($qry_to_9,$bind_9);
		$sample_effect_row = $this->getAffectedRows();
		if($sample_effect_row < 1) {
		throw new Exception("data is not save in samples", 1);
		}
		$isUpdate = true;
		$testRslDB->commit();
		$sampleDB->commit();
	} catch (Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();
		$isUpdate = false;
		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $isUpdate;
	}

	/* last sample flag 8 warp form 2*/
	public function finished_updatetwo() {

	$param = array();
	$sql  = "";
	$sql .= "UPDATE samples as sd ";
	$sql .= " SET ";
	$sql .= " sd.flag = 9 "; 
	$sql .= " WHERE ";
	$sql .= "flag = 8 ";
	$currentTimestamp = date('Y-m-d H:i:s');

	$param['updated_date'] = $currentTimestamp;
	$this->query($sql,$param);
	}

	/*-- -----------------------result update 3 fields from --------------------------------*/

	//insert to Test result table from warpup page.
	public function UpdateResult($param) { 

	$sample_id_arr = $param['warp_sample_id'];
	$one_arr       = $param['warp_Commentone'];        
	$two_arr       = $param['warp_Commenttwo'];
	$warp_rp2_arr  = $param["warp_rp2_check1"];  
	$admin_id    = $param['admin_id'];
	$result_id_arr = $param['warp_result_id'];
	$all_sample_id = $param['selected_sample_id'];
	
	$currentTimestamp = date('Y-m-d H:i:s');
		
	$chkListDB = $this->getDataSource();
	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$chkListDB->begin();
		$testRslDB->begin();
		$sampleDB->begin();
		# update point_out2, report_necessary2, deadline_date2, updated_by, updated_date in sample_test_results
		$bind = array();
		$cnt_sample = count($sample_id_arr);
		$sql  = "";
		$sql .= " UPDATE sample_test_results SET point_out2 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++)
		{
			$s_id = $sample_id_arr[$i];
			$cmt_one = $one_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] = $cmt_one;
		}
		$sql .= "END), ";
		$sql .= "deadline_date2 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) {
			$s_id = $sample_id_arr[$i];
			if(!empty($two_arr[$i])){
			$cmt_two = $two_arr[$i];
			}else{
			$cmt_two = '0000-00-00 00:00:00';

			}
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;       
			$bind[] =$cmt_two;
		}

		$sql .= "END), ";
		
		$sql .= "report_necessary2 = (CASE ";
		
		for($i=0; $i<$cnt_sample; $i++) 
		{
			$s_id =$sample_id_arr[$i];

			$rsl_id = $result_id_arr[$i];
			// $s_id = $sample_id_arr[$i];
			// $cmt_two = $two_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;
			if (!empty($warp_rp2_arr)) {         
			
			if(in_array($s_id,$warp_rp2_arr)) {
				$bind[] = 1;

			} else {
				
				$bind[] = 0;
			} 
			}
			else
			{
			$bind[] = 0;
			}
		}
		$sql .= "END), ";
		$sql .= "flag = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) 
		{
			$s_id = $sample_id_arr[$i];
			$cmt_two = $two_arr[$i];
			$rsl_id = $result_id_arr[$i];
			$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
			$bind[] = $s_id;
			$bind[] = $rsl_id;        
			$bind[] = 1;

		}
		$sql .= "END), ";
		$sql .= "updated_date = ?, ";
		$bind[] = $currentTimestamp;
		$sql .= "updated_by = ? ";
		$bind[] = $admin_id;
		$sql .= " WHERE  sample_id IN (".implode(',', $sample_id_arr).") ";
		$sql .= " AND id IN (".implode(',', $result_id_arr).") "; 
		$this->query($sql,$bind);
		$effect_row = $this->getAffectedRows();
		if($effect_row < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}

		# update report_times to 2 in sample_test_results
		$rpBind = [];
	
		$rpQry = "UPDATE sample_test_results SET report_times=:rp_time, updated_date=now() ";
		$rpQry .= "WHERE sample_id IN (".implode(',', $all_sample_id).")";
		$rpBind['rp_time'] = 2;
		
		$this->query($rpQry, $rpBind);    
		$effect_row = $this->getAffectedRows();
		if($effect_row < 1) {
		throw new Exception("report_times is not update in sample_test_results", 1);
		}
		
		// sample  data table  update flag
		$param_sample = array();
		$sql1  = "";
		$sql1 .= "UPDATE samples ";
		$sql1 .= " SET ";
		$sql1 .= " created_by = :created_by,"; # edited from updated_by = :updated_by to created_by = :created_by to get latest created id for mail (by khin hnin myo)
		$sql1 .= " updated_date = :updated_date,";
		$sql1 .= " flag = 5 ";  
		$sql1 .= " WHERE (flag = 8 OR flag = 5) AND id IN (".implode(',',$sample_id_arr).")" ;     
		$param_sample['created_by'] = $admin_id; # edited from updated_by to created_by to get latest created id for mail (by khin hnin myo)
		$param_sample['updated_date'] = $currentTimestamp;

		$this->query($sql1,$param_sample);     
		$sample_effect_row = $this->getAffectedRows();
		if($sample_effect_row < 1) {
		throw new Exception("data is not update in samples", 1);
		}

		//checklist table update flag
		$chk_list_param = [];
		$chk_list_sql = "UPDATE sample_checklists  SET flag= 4, updated_date=:upd_date, updated_by=:updated_by WHERE flag= 3 AND sample_id IN (".implode(',',$all_sample_id).")";
		$chk_list_param['updated_by'] = $admin_id;
		$chk_list_param['upd_date'] = $currentTimestamp;
		$this->query($chk_list_sql, $chk_list_param);
		$chk_list_effect_row =  $this->getAffectedRows();
		if($sample_effect_row < 1) {
		throw new Exception("data is not update in sample_checklists", 1);
		}

		$return = true; //save successfully

		$chkListDB->commit();
		$testRslDB->commit();
		$sampleDB->commit();  
	} catch (Exception $e) {
		$chkListDB->rollback();
		$testRslDB->rollback();
		$sampleDB->rollback();

		$return = false; //fail to save

		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $return;
	}
	// warp up fist time sample flag  5  change save 
	public function sap_flagupdate($warp_sample_id,$layer_code,$period,$user_level) {

	$param = array();
	$sql  = "";
	$sql .= "UPDATE samples as sd ";
	$sql .= " SET ";
	$sql .= " sd.flag = 5 , sd.updated_by = :updated_by , "; 
	$sql .= " sd.updated_date = :updated_date ";

	$sql .= " WHERE sd.id= :warp_sample_id AND ";

	$sql .= "layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period ";

	$currentTimestamp = date('Y-m-d H:i:s');
	$param['updated_date'] = $currentTimestamp;
	$param['updated_by']    = $user_level;
	$param['warp_sample_id']    = $warp_sample_id;

	$param['period']        = $period;        
	$param['layer_code']       = $layer_code;  
	$this->query($sql,$param);        

	}
	//warp tab2 lastupdate
	public function lastUpdateResult($param) {  

	$bind = array();
	$sample_id_arr = $param['sec_warp_sample_id'];
	$one_arr = $param['warp_Cmttone'];        
	$two_arr = $param['warp_Cmttwo'];
	$result_id_arr = $param['sec_warp_result_id']; 
	$admin_id = $param['admin_id'];
	$warp2sampleall = $param['warp2sampleall'];    
	$currentTimestamp = date('Y-m-d H:i:s');

	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		$cnt_sample = count($sample_id_arr) ;      
		$sql  = "";
		$sql .= " UPDATE sample_test_results SET point_out3 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) 
		{
		$s_id = $sample_id_arr[$i];
		$cmt_one = $one_arr[$i];
		$rsl_id = $result_id_arr[$i];
		$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
		$bind[] = $s_id;
		$bind[] = $rsl_id;
		
		$bind[] = $cmt_one;
		}
		$sql .= "END), ";
		$sql .= "deadline_date3 = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) {
		$s_id = $sample_id_arr[$i];
		if(!empty($two_arr[$i])){
			$cmt_two = $two_arr[$i];
		}else{
			$cmt_two = '0000-00-00 00:00:00';

		}
		$rsl_id = $result_id_arr[$i];
		$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
		$bind[] = $s_id;
		$bind[] = $rsl_id;     
		$bind[] = $cmt_two;

		}
	
		$sql .= "END), ";
		$sql .= "flag = (CASE ";
		for($i=0; $i<$cnt_sample; $i++) 
		{
		$s_id = $sample_id_arr[$i];
		$cmt_two = $two_arr[$i];
		$rsl_id = $result_id_arr[$i];
		$sql .= "WHEN sample_id = ? AND id = ?  THEN ? ";
		$bind[] = $s_id;
		$bind[] = $rsl_id;        
		$bind[] = 1;
		}
		$sql .= "END), ";
		$sql .= "updated_date = ?, ";
		$bind[] = $currentTimestamp;
		$sql .= "updated_by = ? ";
		$bind[] = $admin_id;
		$sql .= " WHERE  sample_id IN (".implode(',', $sample_id_arr).") ";
		$sql .= " AND id IN (".implode(',', $result_id_arr).") ";
		$this->query($sql,$bind);
		$effect_row = $this->getAffectedRows();
		if($effect_row < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}


	$bindrp2 = [];  
	$rpQry = "UPDATE sample_test_results SET report_times=:rp_time, updated_date=now() ";
	$rpQry .= "WHERE sample_id IN (".implode(',', $warp2sampleall).")";
	$bindrp2['rp_time'] = 3;
	
	$this->query($rpQry,$bindrp2);    
	$effect_row = $this->getAffectedRows();
	if($effect_row < 1) {
		throw new Exception("report_times is not update in sample_test_results", 2);
	}
		// sample  data table  update flag
		$param_sample = array();
		$sql1  = "";
		$sql1 .= "UPDATE samples ";
		$sql1 .= " SET ";
		$sql1 .= " created_by = :created_by,"; # edited from updated_by = :updated_by to created_by = :created_by to get latest created id for mail (by khin hnin myo)
		$sql1 .= " updated_date = :updated_date,";
		$sql1 .= " flag = 5 ";  
		$sql1 .= " WHERE (flag = 8 OR flag = 5) AND id IN (".implode(',',$sample_id_arr).")" ;
		$param_sample['created_by'] = $admin_id; # edited from [updated_by] to [created_by] to get latest created id for mail (by khin hnin myo)
		$param_sample['updated_date'] = $currentTimestamp;
		$this->query($sql1,$param_sample);     
		$sample_effect_row = $this->getAffectedRows();
		if($sample_effect_row < 1) {
		throw new Exception("data is not update in samples", 1);
		}
		$result = true;
		$testRslDB->commit();
		$sampleDB->commit();
	} catch(Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();
		$result = false;
		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	
	return $result;
	}

	/* User level warp up-fist one time-----------------------------------*/

	public function wp_RequestStage($user_level,$period,$layer_code) { 


	$param = array();
	$sql  = "";
	$sql .= "UPDATE samples as sd ";
	$sql .= " SET ";
	$sql .= " sd.flag = 7 , sd.updated_by = :updated_by , "; 
	$sql .= " sd.updated_date = :updated_date ";

	$sql .= " WHERE ";
	$sql .= "flag = 9 || flag = 8 " ;
	$sql .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period ";

	$currentTimestamp = date('Y-m-d H:i:s');
	$param['updated_date'] = $currentTimestamp;
	$param['updated_by']    = $user_level;

	$param['period']        = $period;        
	$param['layer_code']       = $layer_code;  
	$this->query($sql,$param);        
	$this->query($sql,$param);

	}
	//User level warp up last  stage
	public function ApproveChecklast($admin_id,$period,$layer_code,$wp_sampleid, $category) {

	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		$param = array();
		$sql  = "";
		$sql .= "UPDATE samples as sd ";
		$sql .= " SET ";
		$sql .= " sd.flag = 10 ,"; 
		$sql .= " sd.updated_date = :updated_date ";
		$sql .= " WHERE ";
		$sql .= "flag = 9 " ;
		$sql .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period AND category = :category ";
		$sql .= "AND id IN (".implode(',', $wp_sampleid).")";
		$currentTimestamp = date('Y-m-d H:i:s');
		$param['updated_date'] = $currentTimestamp;
		// $param['updated_by']    = $admin_id;
		$param['period']        = $period;        
		$param['layer_code']       = $layer_code;
		$param['category']       = $category;
		$this->query($sql,$param);
		$complete_effect_row = $this->getAffectedRows();

		$param_backflag = array();
		$sql1  = "";
		$sql1 .= "UPDATE samples as sd ";
		$sql1 .= " SET ";
		$sql1 .= " sd.flag = 7 ,"; 
		$sql1 .= " sd.updated_date = :updated_date ";
		$sql1 .= " WHERE ";
		$sql1 .= "flag = 6 " ;
		$sql1 .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period AND category = :category ";
		$sql1 .= "AND id IN (".implode(',', $wp_sampleid).")";
		$param_backflag['updated_date'] = $currentTimestamp;
		//$param_backflag['updated_by']    = $admin_id;
		$param_backflag['period']        = $period;        
		$param_backflag['layer_code']       = $layer_code;  
		$param_backflag['category']       = $category;  
		$this->query($sql1,$param_backflag);
		$approve_effect_row = $this->getAffectedRows();

		if($complete_effect_row < 1 && $approve_effect_row < 1) {
		throw new Exception("data is not update in samples", 1);
		}

		$paramtest = array();
		$sql2  = "";
		$sql2 .= " UPDATE sample_test_results as tr ";
		$sql2 .= " SET ";
		$sql2 .= " tr.flag = 3 ,updated_date = :updated_date ,updated_by = :updated_by";
		$sql2 .= " WHERE tr.flag = 2";  
		$sql2 .= " AND tr.sample_id IN (".implode(',', $wp_sampleid).") ";
		$paramtest['updated_by']    = $admin_id;
		$paramtest['updated_date']  = $currentTimestamp;
		$this->query($sql2,$paramtest);
		$test_effect_row = $this->getAffectedRows();
		if($test_effect_row < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}

		$result = true; //save success

		$testRslDB->commit();
		$sampleDB->commit();
	} catch (Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();

		$result = false; //save fail

		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $result;
	}
	
	//User level warp up last  stage cancle
	public function ApproveChecklastcancle($admin_id,$period,$layer_code, $wp_sampleid, $category) {
	
	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		$bind = array();
		$qry  = "";
		$qry .= "UPDATE samples as sd ";
		$qry .= " SET ";
		$qry .= " sd.flag = 5 ,"; 
		$qry .= " sd.updated_date = :updated_date ";
		$qry .= " WHERE ";
		$qry .= "flag = 7 " ;
		$qry .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period AND category = :category ";
		$qry .= "AND id IN (".implode(',', $wp_sampleid).")";
		$currentTimestamp = date('Y-m-d H:i:s');
		$bind['updated_date'] = $currentTimestamp;
		// $bind['updated_by']    = $admin_id;
		$bind['period']        = $period;        
		$bind['layer_code']       = $layer_code;
		$bind['category']       = $category;
		$this->query($qry,$bind);
		$approve_effect_row = $this->getAffectedRows(); 

		$param = array();
		$sql  = "";
		$sql .= "UPDATE samples as sd  ";
		$sql .= " SET ";
		$sql .= " sd.flag = 5,"; 
		$sql .= " sd.updated_date = :updated_date ";
		$sql .= " WHERE ";
		$sql .= "flag = 10 AND (complete_date != NULL OR complete_date != '')" ;
		$sql .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period AND category = :category ";
		$sql .= "AND id IN (".implode(',', $wp_sampleid).")";
		$param['updated_date']  = $currentTimestamp;
		// $param['updated_by']    = $admin_id; 
		$param['period']        = $period;        
		$param['layer_code']       = $layer_code;
		$param['category']       = $category;
		$this->query($sql,$param);
		$complete_effect_row = $this->getAffectedRows();

		if($approve_effect_row < 1 && $complete_effect_row < 1) {
		throw new Exception("data is not update in samples", 1);
		} 

		$paramtest = array();
		$sql1  = "";
		$sql1 .= " UPDATE sample_test_results as tr ";
		$sql1 .= " SET ";
		$sql1 .= " tr.flag = 1 ,updated_date = :updated_date ,updated_by = :updated_by";
		$sql1 .= " WHERE tr.flag = 3 ";  
		$sql1 .= " AND tr.sample_id IN (".implode(',', $wp_sampleid).")";
		$paramtest['updated_by']    = $admin_id;
		$paramtest['updated_date']  = $currentTimestamp;
		$this->query($sql1,$paramtest);
		$test_effect_row = $this->getAffectedRows();
		if($test_effect_row < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}
		$rsl = true;//save success
		$testRslDB->commit();
		$sampleDB->commit();
	} catch(Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();
		$rsl = false;//save fail
		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $rsl;
	}
	/*-----------------second time warp up level--------------------------------------------*/

	public function sample_flag($period,$layer_code,$user_level) {
	$param = array();
	$sql  = ""; 
	$sql .= "   SELECT  flag";
	$sql .= "   FROM samples";
	$sql .= "   WHERE  flag = '6'";

	$data = $this->query($sql);
	return $data;

	} 

	//User level warp up second stage
	public function sec_ApproveChecklast($admin_id,$period,$layer_code, $sample_id_arr, $category) {
	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		# update samples
		$param = array();
		$sql  = "";
		$sql .= "UPDATE samples as sd ";
		$sql .= " SET ";
		$sql .= " sd.flag = 10 ,"; 
		$sql .= " sd.updated_date = :updated_date ";
		$sql .= " WHERE ";
		$sql .= "flag = 9 " ;
		$sql .= "AND layer_code = :layer_code AND  DATE_FORMAT(period,'%Y-%m') = :period AND category = :category ";
		$currentTimestamp = date('Y-m-d H:i:s');
		$param['updated_date'] = $currentTimestamp;
		//$param['updated_by']    = $admin_id;
		$param['period']        = $period;        
		$param['layer_code']       = $layer_code;  
		$param['category']       = $category;
		$this->query($sql,$param);
		$sample_row = $this->getAffectedRows();
		if($sample_row < 1) {
		throw new Exception("data is not update in samples", 1);
		}

		# update sample_test_results
		$paramtest = array();
		$sql2  = "";
		$sql2 .= " UPDATE sample_test_results as tr ";
		$sql2 .= " SET ";
		$sql2 .= " tr.flag = 3 ,updated_date = :updated_date ,updated_by = :updated_by";
		$sql2 .= " WHERE tr.flag = 2";  
		$sql2 .= " AND tr.sample_id IN (".implode(',', $sample_id_arr).") ";
		$paramtest['updated_by']    = $admin_id;
		$paramtest['updated_date']  = $currentTimestamp;
		$this->query($sql2,$paramtest);
		$tsl_row = $this->getAffectedRows();
		if($tsl_row < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}
		$isApprove = true;
		$testRslDB->commit();
		$sampleDB->commit();
	} catch (Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();
		$isApprove = false;
		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $isApprove;
	}   
	public function sec_ApproveChecklastcancle($admin_id,$period,$layer_code,$sample_id_arr, $category) {

	$tstRslModel = ClassRegistry::init('SampleTestResult');
	$testRslDB = $tstRslModel->getDataSource();
	$sampleModel = ClassRegistry::init('Sample');
	$sampleDB = $sampleModel->getDataSource();
	try {
		$testRslDB->begin();
		$sampleDB->begin();

		# update samples
		$param = array();
		$sql  = "";
		$sql .= "UPDATE samples as sd ";
		$sql .= " SET ";
		$sql .= " sd.flag = 5 ,"; 
		$sql .= " sd.updated_date = :updated_date ";
		$sql .= " WHERE ";
		$sql .= "flag = 10 AND layer_code = :layer_code AND category = :category " ;
		$sql .= "AND DATE_FORMAT(period,'%Y-%m') = :period ";
		$sql .= "AND id IN (".implode(',', $sample_id_arr).") ";
		$currentTimestamp = date('Y-m-d H:i:s');
		$param['updated_date'] = $currentTimestamp;
		//$param['updated_by']    = $admin_id;  
		$param['period']        = $period;        
		$param['layer_code']       = $layer_code;  
		$param['category']       = $category;

		$this->query($sql,$param);
		$row = $this->getAffectedRows();
		if($row < 1) {
		throw new Exception("data is not update in samples", 1);
		}

		# update sample_test_results
		$paramtest = array();
		$sql2  = "";
		$sql2 .= " UPDATE sample_test_results as tr ";
		$sql2 .= " SET ";
		$sql2 .= " tr.flag = 1 ,updated_date = :updated_date ,updated_by = :updated_by";
		$sql2 .= " WHERE tr.flag = 3";  
		$sql2 .= " AND tr.sample_id IN (".implode(',', $sample_id_arr).") ";
		$paramtest['updated_by']    = $admin_id;
		$paramtest['updated_date']  = $currentTimestamp;
		$this->query($sql2,$paramtest);
		$effect = $this->getAffectedRows();
		if($effect < 1) {
		throw new Exception("data is not update in sample_test_results", 1);
		}
		$isCancel = true;
		$testRslDB->commit();
		$sampleDB->commit();
	} catch (Exception $e) {
		$testRslDB->rollback();
		$sampleDB->rollback();
		$isCancel = false;
		CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
	}
	return $isCancel;
	}
	
/**
 * Check sample id is exist or not in check list table
 *
 * @author Aye Thandar Lwin
 *
 * @param sample_id
 * @return data
 */
	public function CheckSampleId($sample_id) {
	
		$param = array();
	
		$sql  = "";
		$sql .= " SELECT * ";
		$sql .= "  FROM sample_checklists ";
		$sql .= "  WHERE sample_id = :sample_id ";
		
		$param['sample_id'] = $sample_id;
	
		$data = $this->query($sql,$param);
		return $data;
	
	}
}
