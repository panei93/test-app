<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

/**
 * ForecastPlan Controller
 *
 * @property Account $Account
 * @property PaginatorComponent $Paginator
 */
class BrmBudgetPlanController extends AppController
{

/**
 * Components
 *
 * @var array
 */
	public $uses = array('BrmActualResultSummary','MonthlyReport','BrmBudgetPrime', 'BrmSaccount','BrmExpected','BrmBudget','RtaxFee','BrmAccount','BrmExpectedBudgetDiffAccount', 'BrmTerm','BrmBudgetSummary','BrmForecastSummary','Layer','BrmTermDeadline', 'BrmAccountSetup', 'Account');
	public $components = array('Session','PhpExcel.PhpExcel');
	public $helpers = array('Html', 'Form', 'Csv');

	public function beforeFilter()
	{
		$Common = New CommonController();
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
		
        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);
		
        if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
			$errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        } 
        $target_month 	= $this->request->query('target_month');
        $term_id 		= $this->request->query('term_id');
        $term_name 		= $this->request->query('term_name');
        $hq_name		= $this->request->query('hq_name');
        $hq_id			= $this->request->query('hq_id');
        $read_limit   	= $permissions['index']['limit'];
        
        if ($term_name != "" && $hq_name != "" && $hq_id != "") {
            $this->Session->write('TARGETMONTH', $target_month);
            $this->Session->write('TERM_ID', $term_id);
            $this->Session->write('TERM_NAME', $term_name);
            if ($read_limit == '1') {
                if ($this->Session->check('HEAD_DEPT_ID') && $this->Session->check('HEAD_DEPT_NAME')) {
                    $hq_id = $this->Session->read('HEAD_DEPT_ID');
                    $hq_name = $this->Session->read('HEAD_DEPT_NAME');
                } else {
                    $errorMsg = parent::getErrorMsg('SE073');
                    $this->Flash->set($errorMsg, array("key"=>"TermError"));
                    $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
                }
            } 
            $this->Session->write('HEAD_DEPT_NAME', $hq_name);
            $this->Session->write('HEAD_DEPT_ID', $hq_id);
        } elseif (($this->Session->read('TERM_NAME') == "") || ($this->Session->read('HEAD_DEPT_NAME') == "")) {
            $errorMsg = parent::getErrorMsg('SE081');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
	}
	
	/**
	 * index method
	 *
	 * @author Ei Thandar Kyaw on 17/07/2020
	 * @return void
	 */

	public function index()
	{
	
		$this->layout = 'phase_3_menu';
		$headQuarterId = 0;
		$user_level = $this->Session->read('ADMIN_LEVEL_ID');

		if ($this->Session->check('TERM_NAME')) {
			$term = $this->Session->read('TERM_NAME');
		}
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('BUDGET_BA_CODE')) {
			$ba_code = $this->Session->read('BUDGET_BA_CODE');
		}
		if ($this->Session->check('HEAD_DEPT_ID')) {
			$headQuarterId = $this->Session->read('HEAD_DEPT_ID');
		}
		if ($this->Session->check('HEAD_DEPT_NAME')) {
			$headQuarterName = $this->Session->read('HEAD_DEPT_NAME');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$loginId = $this->Session->read('LOGIN_ID');
		}
		if ($this->Session->check('successMsg')) {
			$successMsg = $this->Session->read('successMsg');
			$this->Session->delete('successMsg');
		}
		if ($this->Session->check('BUDGET_BA_NAME')) {
			$ba_name = $this->Session->read('BUDGET_BA_NAME');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$login_id = $this->Session->read('LOGIN_ID');
		}
		if ($this->Session->check('HEADQUARTER_DEADLINE')) {
			$hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
		}
		if ($this->Session->check('HEAD_DEPT_CODE')) {
			$hqDepCode = $this->Session->read('HEAD_DEPT_CODE');
		}
		if ($this->Session->check('SESSION_LAYER_CODE')){
			$layer_code = $this->Session->read('SESSION_LAYER_CODE');
		}
		if ($this->Session->check('LayerTypeData')){
			$layer_types = $this->Session->read('LayerTypeData');
		}
		if ($_GET['code']) {
			$ba_code = $_GET['code'];
		}
		if ($_GET['hq']) {
			$hqDepCode = $_GET['hq'];
		}
		if ($_GET['term']) {
			$term_id = $_GET['term'];
		}
		$year = $_GET['year'];

		
		if ($_GET['code']) {
			//get ba_name from layer tbl
			$ba_name = $this->Layer->find('all',array(
				'fields' => 'name_jp',
				'conditions' => array('Layer.flag'=>1 , 'layer_code'=> $layer_code)
			));
			if (!empty($ba_name[0]['Layer']['name_jp'])) {
				$ba_name = $ba_name[0]['Layer']['name_jp'];
			} else {
				$ba_name ="";
			}
		}
		
		$data = $this->getBudgetData($term_id, $term, $hqDepCode , $headQuarterName, $layer_code, $ba_name, $year, $loginId);
		
		$check_account_setup = (!empty($this->BrmAccountSetup->find('all', array(
            'conditions' => array(
                'target_year' => $year,
                'hlayer_code' => $hqDepCode
            )
        ))))? 'exit' : 'not_exit';
		
		if (!empty($data['no_data'])) {
			$subAccount = $data['subAccount'];
			$Months = $data['Months'];
			$term = $data['term'];
			$budgetData = $data['budgetData'];
			$successMsg = $data['successMsg'];
			$usedName = $data['usedName'];
			$no_data = $data['no_data'];
			$this->set(compact('subAccount', 'Months', 'term', 'ba_code', 'ba_name', 'budgetData', 'successMsg', 'usedName', 'no_data', 'layer_types'));
		} else {
			$subAccount = $data['subAccount'];
			$budgetData = $data['budgetData'];
			$saveAccName = $data['saveAccName'];
			$UploadFileData = $data['UploadFileData'];
			$Months = $data['Months'];
			$successMsg = $data['successMsg'];
			$usedName = $data['usedName'];
			$isApproved = $data['isApproved'];
			$filling_date = $data['filling_date'];
			$taxEditabled = $data['taxEditabled'];
			$deadline_date = $data['deadline_date'];
			$tax = $data['tax'];
			$this->set(compact('subAccount', 'Months', 'term', 'ba_code', 'budgetData', 'successMsg', 'usedName', 'isApproved', 'ba_name', 'filling_date', 'taxEditabled', 'deadline_date', 'tax', 'layer_code', 'check_account_setup', 'layer_types'));
		}
		

		$this->render('index');
	}
	public function prepareData($pData, $year, $layer_code){
		$data = $pData['Account'];
		$accountName = $pData['AccountNameOnly'];
		//pr($accountName);
		$accountData = array();
        foreach($data as $key=>$value){
            $accountData[$key]['brm_account_setups']['id'] = $value['AccountSetting']['id'];
            $accountData[$key]['brm_account_setups']['target_year'] = $year;
            $accountData[$key]['brm_account_setups']['hlayer_code'] = $layer_code;
            $accountData[$key]['brm_account_setups']['brm_account_id'] = $value['AccountSetting']['account_id'];
            $accountData[$key]['brm_account_setups']['brm_saccount_id'] = 0;
            $accountData[$key]['brm_account_setups']['order'] = $value['AccountSetting']['display_order'];
            $accountData[$key]['brm_account_setups']['sub_order'] = 0;
            $accountData[$key]['brm_account_setups']['flag'] = $value['Accounts']['flag'];
            $accountData[$key]['brm_account_setups']['created_by'] = $value['Accounts']['created_by'];
            $accountData[$key]['brm_account_setups']['updated_by'] = $value['Accounts']['updated_by'];
            $accountData[$key]['brm_account_setups']['created_date'] = $value['Accounts']['created_date'];
            $accountData[$key]['brm_account_setups']['updated_date'] = $value['Accounts']['updated_date'];

            $accountData[$key]['brm_accounts']['id'] = $value['AccountSetting']['account_id'];
            if($value['AccountSetting']['label_name'] != '') $accountData[$key]['brm_accounts']['name_jp'] = $value['AccountSetting']['label_name'];
            else $accountData[$key]['brm_accounts']['name_jp'] = $value['Accounts']['account_name'];
            $accountData[$key]['brm_accounts']['type'] = 0;
            $accountData[$key]['brm_accounts']['calculation_method'] = '';
			$calculation_formula = $value['Accounts']['calculation_formula'];
			$baseParam = $value['Accounts']['base_param'];
			$accountData[$key]['brm_accounts']['calculation_method'] .= '{"field":[';
            if($value['Accounts']['account_type'] == 2){
               
				
                if(strrpos($calculation_formula, '/')) {
                    $calculation_formula = str_replace('"', '', $calculation_formula);
                    $calculationFormula = explode("/", $calculation_formula);
					$accountData[$key]['brm_accounts']['type'] = 2;
					//if($calculationFormula[1] != '') $accountData[$key]['brm_accounts']['type'] = 2;
					//else $accountData[$key]['brm_accounts']['type'] = 3;
                    foreach($calculationFormula as $formula){
                        if($formula != '') $accountData[$key]['brm_accounts']['calculation_method'] .= '"'.$accountName[$formula].'",';
                    }
                    
                }else if(strrpos($calculation_formula, '+')){
                    $accountData[$key]['brm_accounts']['type'] = 1;
					$calculation_formula = str_replace('"', '', $calculation_formula);
                    $calculationFormula = explode("+", $calculation_formula);
					foreach($calculationFormula as $formula){
                        if($formula != '') $accountData[$key]['brm_accounts']['calculation_method'] .= '"'.$accountName[$formula].'",';
                    }
                }                
            }else if($value['Accounts']['account_type'] == 3){
				$accountData[$key]['brm_accounts']['type'] = 3;
					$calculation_formula = str_replace('"', '', $calculation_formula);
					$calculationFormula = explode("-", $calculation_formula);
					$accountData[$key]['brm_accounts']['calculation_method'] .= '"'.$accountName[$calculationFormula[0]].'",';
			}
			$accountData[$key]['brm_accounts']['calculation_method'] = substr($accountData[$key]['brm_accounts']['calculation_method'], 0, -1);
            $accountData[$key]['brm_accounts']['calculation_method'] .= ']}';
			$calculation_formula = str_replace('/', ',', $calculation_formula);
			$param = '{'.str_replace('+', ',', $calculation_formula).'}';
			
			if(strpos($param,"tax")){
				//exit;
			}else if($baseParam != '' && $param != str_replace('"', '', $baseParam)){
				$accountData[$key]['brm_accounts']['type'] = 0;
				$accountData[$key]['brm_accounts']['calculation_method'] = '';
			}
				
			
            $accountData[$key]['brm_accounts']['auto_changed'] = '';
			// $accName = $this->Account->find('list', array(
			// 	'fields' => array('id','account_name'),
			// 	'conditions' => array(
			// 		'base_param LIKE' => '%"'.$value['AccountSetting']['account_id'].'"%',
			// 		'flag' => 1
			// 	)
			// ));
			
			// if(count($accName) > 0) $accountData[$key]['brm_accounts']['auto_changed'] = implode(",",$accName);
            if($value['Accounts']['base_param'] != ''){

                $param = str_replace('"', '', $value['Accounts']['base_param']);
                $param1 = str_replace('{', '', $param);
                $param2 = str_replace('}', '', $param1);
                $base_param = explode(",", $param2);
                foreach($base_param as $par){
                    $accountData[$key]['brm_accounts']['auto_changed'] .= $accountName[$par].', ';
                }
                $accountData[$key]['brm_accounts']['auto_changed'] = substr($accountData[$key]['brm_accounts']['auto_changed'], 0, -2);
				
			}
        }
		return $accountData;
	}
	public function getBudgetData($term_id, $term, $hqDepCode, $headQuarterName, $layer_code, $ba_name, $year, $loginId, $save_to_tmp=false, $form_type=null)
	{	
		$taxAmount = $this->RtaxFee->find('list', array(
			'fields' => array('target_year', 'rate'),
			'conditions' => array('target_year' => $year, 'flag' => 1)));
		$tax = (!empty($taxAmount[$year])) ? $taxAmount[$year] : 0;
		if (empty($hqDepCode) || empty($layer_code)) {
			
			if (empty($hqDepCode)) {
				$errorMsg = parent::getErrorMsg('SE073');
			} elseif (empty($layer_code)) {
				$errorMsg = parent::getErrorMsg('SE086');
			}
			
			$this->Flash->error($errorMsg);
			$this->redirect(array('controller' => 'TermSelection', 'action' => 'index'));
		}
		if (($hqDepCode != '') && ($term_id != '')) {
			$headqDeadline = $this->BrmTermDeadline->find('list',array(
				'fields' => array('hlayer_code','deadline_date'),
				'conditions' => array('brm_term_id'=>$term_id, 'hlayer_code'=>$hqDepCode)
			));
			
			$hqDeadline = $headqDeadline[$hqDepCode];
			
		}
		
		$Common = New CommonController();
		$accountData = $Common->getAccountByPage($this->params, $year, $hqDepCode);
		$subAccount = $this->prepareData($accountData, $year, $hqDepCode);
		//echo 'subAccount_test';
		//pr($subAccount);
		// to get id and account name
		//$subAccount = $this->BrmAccount->getAccountByHeadQuarter($hqDepCode, $year);
		//echo 'subAccount';
		//pr($subAccount);
		//exit;
		$subAccName = array();
		foreach ($subAccount as $key => $row) {
			if (in_array($row['brm_accounts']['name_jp'], $subAccName)) {
				unset($subAccount[$key]);
			}
			$subAccName[] = $row['brm_accounts']['name_jp'];
		}
		$order = array();
		foreach ($subAccount as $key => $row) {
			$order[$key] = $row['brm_account_setups']['order'];
			// get account code
			$accountCode = $this->BrmAccount->getAccountCode($row['brm_accounts']['id']);
			foreach ($accountCode as $code) {
				$subAccount[$key]['brm_saccounts']['account_code'][] = $code['brm_saccounts']['account_code'];
			}
		}
		array_multisort($order, SORT_ASC, $subAccount);
		if (isset($_GET['forecast']) || $form_type == 'forecast') {
			
			$usedName = array(
				'name'=> '見込',
				'formName'=> '見込フォーム',
				'term'=> '期間',
				'year'=>'年度'
				
			);
		} elseif (isset($_GET['budget']) || $form_type == 'budget') {
			$usedName = array(
				'name'=> '予算',
				'formName'=> '予算フォーム',
				'term'=> '期間',
				'year'=>'年度'
				
			);
		}
		
		if (!$save_to_tmp) {
			#get necessary data from session
			$permission = $this->Session->read('PERMISSIONS');
			// $user_level = $this->Session->read('ADMIN_LEVEL_ID');
			
			#get create permission
			$create_limit = $permission['save']['limit'];
			
			if ($create_limit == 0) {
				$createLimit = true;
			} else {
				$createLimit = $Common->checkLimit($create_limit, $layer_code, $loginId, $permission);
			}
			
		} else {
			$createLimit = 'true';
		}
		$Months = $Common->get12Month($term_id);
		$budgetData = array();
		$actualData = array();
		if (sizeof($subAccount) == 0) {
			$data = array(
				'subAccount' => $subAccount,
				'Months' => $Months,
				'term' => $term,
				'layer_code' => $layer_code,
				'ba_name' => $ba_name,
				'budgetData' => $budgetData,
				'successMsg' => $successMsg,
				'usedName' => $usedName,
				'no_data' => "no_data",
			);
			return $data;
		// $this->set(compact('subAccount','Months','term','layer_code', 'ba_name','budgetData', 'successMsg', 'usedName', 'no_data'));
		} else {
			$isApproved = false;

			// check approve or not
			$isApproved = $this->BrmBudget->checkApprove($term_id, $hqDepCode, $layer_code);

			if (in_array(2,$isApproved) || $createLimit != 'true') {
				$isApproved = true;
			} else {
				$isApproved = false;
			}
			$resultExistMonths = $this->BrmActualResultSummary->find('list', array(
				'fields' => 'target_month',
				'conditions' => array(
					'hlayer_code' => $hqDepCode,
					'layer_code' => $layer_code,
				),
				'group' => 'target_month'
			));
			
			$baBudgetTax = Setting::BA_BUDGET_TAX;
			$taxEditabled = false;
			// if ba are 8028,802C,8001, allow to type for tax value in 社内税金
			if (in_array($layer_code, $baBudgetTax)) {
				$taxEditabled = true;
			}
			$restrict_hqs = Setting::TRADING_DISABLE_HQS;
			$fieldEnable = false;
			
			# if hq are 管理本部,内部監査室, 売上高、売上原価 this fields are enable to edit
			if (in_array($headQuarterName, $restrict_hqs)) {
				$fieldEnable = true;
			}

			#Get budget start and end month
			$start_month = $Common->getMonth($year, $term_id, 'start');
			#Get forecast period(eg: 2020-05) to show actual result data till to this period
			$forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);
			
			$deadline_date = $hqDeadline;
			
			foreach ($subAccount as $eachAcc) {
				$codes = $eachAcc['brm_saccounts']['account_code']; #get account codes of each sub account
				$acc_codes = "'".join("','", $codes)."'"; #change from array to string
				
				#get  account name
				$acc_name = $eachAcc['brm_accounts']['name_jp'];
				$account_type = $eachAcc['brm_accounts']['type'];
				#set data by  account name
				$budgetData[$acc_name]['acc_id'] = $eachAcc['brm_account_setups']['brm_account_id'];
				$budgetData[$acc_name]['type'] = $account_type;
				$budgetData[$acc_name]['auto_changed'] = $eachAcc['brm_accounts']['auto_changed'];
				
				$disable_accs = Setting::BUDGET_DISABLE_ACCS;
				if ($isApproved == 2) {
					$budgetData[$acc_name]['disable'] = 'disabled';
				} elseif (in_array($acc_name, $disable_accs)) {
					if ($fieldEnable && ($acc_name == '売上高' || $acc_name == '売上原価')) {
						$budgetData[$acc_name]['disable'] = '';
					} else {
						$budgetData[$acc_name]['disable'] = 'disabled';
					}
				} else {
					$budgetData[$acc_name]['disable'] = '';
				}
				
				if ($account_type == 1 || $account_type == 2 || $account_type == 3) {
					$calculate_method = json_decode($eachAcc['brm_accounts']['calculation_method'], true);
					
					$method['fields'] = $calculate_method['field'];
					
					if ($account_type == 1) {
						$method['operator'] = '+';
					} elseif ($account_type == 2) {
						$method['operator'] = '/';
					} elseif ($account_type == 3) {
						$method['operator'] = '*tax';
					}
				}
				
				$budgetData[$acc_name]['equation'] = $method;
				$budgetData[$acc_name]['calculation_method'] = $eachAcc['brm_accounts']['calculation_method'];

				if (isset($_GET['forecast']) || $form_type == 'forecast') {
					#Get expected data
					$budget_data = $this->BrmExpected->getMonthlyResult($layer_code, $term_id, $acc_codes);
					#Get budget data
					if ($acc_name == '社内税金' && in_array($layer_code, $baBudgetTax) == false) {
						$budget_data = $this->BrmExpected->getMonthlyResult('', $term_id, $acc_codes);
					} else {
						$budget_data = $this->BrmExpected->getMonthlyResult($layer_code, $term_id, $acc_codes);
					}

					$budget_data = $budget_data[0][0];

					$firstHalf = 0;
					$secHalf = 0;
					#Loop through 12 months
					$monthDisabled = 0;
					$code = $layer_code;
					for ($i=0; $i <12 ; $i++) {
						
						#Increase month by loop count
						$month = date("Y-m", strtotime($start_month. "last day of + ".$i." Month"));
						#get database field name
						$field = 'month_'.($i+1).'_amt';
						$lay_code = "'".$code."'";
						#check approve for each month

						if (in_array($month, $resultExistMonths)) {
							
							$monthDisabled = $i;
							
							if (strtotime($month) <= strtotime($forecastPeriod)) {
								
								#get actual amount if approved
								$amount = $hidAmount = $this->BrmActualResultSummary->getMonthlyResult($lay_code, $month, $acc_codes);
								//$amount = round($amount/1000, 1);
								//$amount = $amount * 1000;
		  
								$budgetData[$acc_name]['amount'][$field] = $amount;
								$budgetData[$acc_name]['disable'.$field] = 'disabled';
								if ($i <= 5) {
									//$amount = round($amount/1000, 1);
									//$amount = $amount * 1000;
									$firstHalf += $amount;
								} #add to firstHalf total for first six month
									
								else {
									//$amount = round($amount/1000, 1);
									//$amount = $amount * 1000;
									$secHalf += $amount; #add to secondHalf total for last six month
								}
							} else {
								#If not approved, get data from tbl_expected
								
								$budgetData[$acc_name]['amount'][$field] = ($budget_data[$field] != '') ? $budget_data[$field] : 0;

								if ($i <= 5) {
									//$budgetData[$acc_name]['amount'][$field] = round($budgetData[$acc_name]['amount'][$field]/1000, 1);
									//$budgetData[$acc_name]['amount'][$field] = $budgetData[$acc_name]['amount'][$field] * 1000;
									$firstHalf += $budgetData[$acc_name]['amount'][$field]; #add to firstHalf total for first six month
								} else {
									//$budgetData[$acc_name]['amount'][$field] = round($budgetData[$acc_name]['amount'][$field]/1000, 1);
									//$budgetData[$acc_name]['amount'][$field] = $budgetData[$acc_name]['amount'][$field] * 1000;
									$secHalf += $budgetData[$acc_name]['amount'][$field]; #add to secondHalf total for last six month
								}
							}
						} else {
							
							if (strtotime($month) <= strtotime($forecastPeriod)) {
						
							
								$amount = $this->BrmActualResultSummary->getMonthlyResult($lay_code, $month, $acc_codes);
								$budgetData[$acc_name]['amount'][$field] = $amount;
								$budgetData[$acc_name]['disable'.$field] = 'disabled';
								if ($i <= 5) {
									$firstHalf += $amount;
								} #add to firstHalf total for first six month
									
								else {
									$secHalf += $amount;
								} #add to secondHalf total for last six month
							} else {

								#If not approved, get data from tbl_expected
								$budgetData[$acc_name]['amount'][$field] = ($budget_data[$field] != '') ? $budget_data[$field] : 0;

								if ($i <= 5) {
									$firstHalf += $budgetData[$acc_name]['amount'][$field];
								} #add to firstHalf total for first six month
									
								else {
									$secHalf += $budgetData[$acc_name]['amount'][$field];
								} #add to secondHalf total for last six month
							}
						}

						if ($i == 5) {
							#set first half value
							$budgetData[$acc_name]['amount']['first_half'] += $firstHalf;
						}

						if ($i == 11) {
							#set second half value
							$budgetData[$acc_name]['amount']['second_half'] += $secHalf;
							#set whole total value
							$budgetData[$acc_name]['amount']['whole_total'] += $firstHalf + $secHalf;
						}
					}
					
					$current_dd = $this->BrmExpected->find('first', array(
						'fields' => 'BrmExpected.filling_date',
						'conditions' => array(
							'BrmExpected.brm_term_id' 	=> $term_id,
							'BrmExpected.target_year' 	=> $year,
							'BrmExpected.layer_code' 	=> $layer_code,
							'BrmExpected.flag'			=> 1
							)
						));
					$filling_date = $current_dd['BrmExpected']['filling_date'];

				} elseif (isset($_GET['budget']) || $form_type == 'budget') {
					#Get budget data
					if ($acc_name == '社内税金' && in_array($layer_code, $baBudgetTax) == false) {
						$budget_data = $this->BrmBudgetPrime->getMonthlyResult('', $term_id, $acc_codes, $year);
					} else {
						$budget_data = $this->BrmBudgetPrime->getMonthlyResult($layer_code, $term_id, $acc_codes, $year);
					}
					$budgetData[$acc_name]['amount'] = $budget_data[0][0];
				
					$current_dd = $this->BrmBudgetPrime->find('first', array(
						'fields' => 'BrmBudgetPrime.filling_date',
						'conditions' => array(
							'BrmBudgetPrime.brm_term_id' 	=> $term_id,
							'BrmBudgetPrime.target_year' 	=> $year,
							'BrmBudgetPrime.layer_code' 	=> $layer_code,
							'BrmBudgetPrime.flag'			=> 1
						)
					));
				
					$filling_date = $current_dd['BrmBudgetPrime']['filling_date'];
				}
			}
			$saveAccName = array();
			
			# loop and format(modify) budget data
			foreach ($budgetData as $subacc_name => $value) {
				if (($value['type'] == 0 && $value['disable'] == '') || ($value['type'] == 3)) {
					$saveAccName[$value['acc_id']] = $subacc_name;
				}
				$setDefaultValue = array();
				$fields = $value['equation']['fields']; #get calculate fields
				$operator = $value['equation']['operator']; #get operator
				$budgetData[$subacc_name]['filling_date'] = $filling_date;
				$firstHalf = 0;
				$secHalf = 0;
				$hid_firstHalf = 0;
				$hid_secHalf = 0;
				foreach ($value['amount'] as $month_index => $amount) {
					$update_amt = 0;
					$hid_update_amt = 0;
					if ($value['type'] == 1 || $value['type'] == 2) { #if total fields
						$cal_amt = 0;
						$hid_cal_amt = 0;
						foreach ($fields as $idx => $field) { #loop through calculate fields
							$monthly_amt = str_replace(',', '', $budgetData[$field]['amount'][$month_index]);
							$hid_monthly_amt = str_replace(',', '', $budgetData[$field]['hid_amount'][$month_index]);
							if ($operator == '+') {
								$monthly_amt = str_replace(',', '', $budgetData[$field]['hid_amount'][$month_index]);
								$cal_amt += $monthly_amt;
								//$cal_amt += round($monthly_amt,1);
								$hid_cal_amt += $hid_monthly_amt;
							} elseif ($operator == '/') {
								$monthly_amt = str_replace(',', '', $budgetData[$field]['hid_amount'][$month_index]);
								if ($idx == 0) { #if first loop
									
									$cal_amt = $monthly_amt;
								} else {
									$cal_amt = (($monthly_amt != 0) && $cal_amt != 0) ? $monthly_amt/$cal_amt : 0;
								}
							}
						}
						//$update_amt = ($operator == '+') ? number_format($cal_amt,1) : round($cal_amt*100).'%';
						$update_amt = ($operator == '+') ? $cal_amt : round($cal_amt*100).'%';
						if ($operator == '+' && $update_amt == 0) {
							$update_amt = number_format($update_amt, 1);
						}
						$hid_update_amt = ($operator == '+') ? number_format($hid_cal_amt, 3) : round($cal_amt*100).'%';
						
					} elseif ($value['type'] == 3) { #if not total field, multiply with 0.31 and get floor
						
						if ($amount == 0 && ($month_index == 'month_1_amt' || $month_index == 'month_2_amt' || $month_index == 'month_3_amt' || $month_index == 'month_4_amt' || $month_index == 'month_5_amt' || $month_index == 'month_6_amt' || $month_index == 'month_7_amt' || $month_index == 'month_8_amt' || $month_index == 'month_9_amt' || $month_index == 'month_10_amt' || $month_index == 'month_11_amt' || $month_index == 'month_12_amt')) {
							$profit = str_replace(',', '', $budgetData[$fields[0]]['amount'][$month_index]);
							
							if ($taxEditabled == false) {
								$update_amt = ($profit < 0) ? (floor(-$profit*$tax)) : (-floor($profit*$tax));
							} else {
								if ($layer_code == '9000') {
									$update_amt = $amount/1000;
								} else {
									if ($budgetData[$subacc_name]['disable'.$month_index] == 'disabled') {
										$update_amt = ($profit < 0) ? (floor(-$profit*$tax)) : (-floor($profit*$tax));
									} else {
										$update_amt = $amount/1000;
									}
								}
							}
							$hid_update_amt = $update_amt;
						} else {
							$update_amt = $amount/1000;
							$hid_update_amt = $amount/1000;
							if ($month_index != 'first_half' && $month_index != 'second_half' && $month_index != 'whole_total') {
								$setDefaultValue[] = $month_index;
							}
						}
						if ($month_index == 'month_1_amt' || $month_index == 'month_2_amt' || $month_index == 'month_3_amt' || $month_index == 'month_4_amt' || $month_index == 'month_5_amt' || $month_index == 'month_6_amt') {
							$firstHalf += $update_amt;
							$hid_firstHalf += $hid_update_amt;
						} elseif ($month_index == 'month_7_amt' || $month_index == 'month_8_amt' || $month_index == 'month_9_amt' || $month_index == 'month_10_amt' || $month_index == 'month_11_amt' || $month_index == 'month_12_amt') {
							$secHalf += $update_amt;
							$hid_secHalf += $hid_update_amt;
						} elseif ($month_index == 'whole_total') {
							$update_amt = $firstHalf + $secHalf;
							$hid_update_amt = $hid_firstHalf + $hid_secHalf;
						}
						if ($month_index == 'first_half') {
							$update_amt = $firstHalf;
							$hid_update_amt = $hid_firstHalf;
						}
						if ($month_index == 'second_half') {
							$update_amt = $secHalf;
							$hid_update_amt = $hid_secHalf;
						}
						
						$hidUpdate_amt = $hid_update_amt;
						//$update_amt = number_format($update_amt,1);
						$update_amt = $update_amt;
						if ($update_amt == 0) {
							$update_amt = number_format($update_amt, 1);
						}
						$hid_update_amt = number_format($hidUpdate_amt, 3);
					} else { #if not total field, divide with 1000 and format number
						//$update_amt = number_format(($budgetData[$subacc_name]['amount'][$month_index]/1000),1);
						$update_amt = $budgetData[$subacc_name]['amount'][$month_index]/1000;
						if ($update_amt == 0) {
							$update_amt = number_format($update_amt, 1);
						}
						$hid_update_amt = number_format(($budgetData[$subacc_name]['amount'][$month_index]/1000), 3);
					}
					
					#update the amount to existing data
					$budgetData[$subacc_name]['amount'][$month_index] = $update_amt;
					$budgetData[$subacc_name]['hid_amount'][$month_index] = $hid_update_amt;
					$budgetData[$subacc_name]['setDefaultValue'] = $setDefaultValue;
				}
			}
			
			$start_month = $this->BrmTerm->find('all', array(
				'conditions' => array('id' => $term_id,'flag' => 1),
				'fields' => array('start_month')));
			
			// Write data to cache
			$cache_data = array(
				'subAccount' => $subAccount,
				'budgetData' => $budgetData,
				'saveAccName' => $saveAccName,
				'UploadFileData' => $UploadFileData,
				'Months' => $Months,
				'successMsg' => $successMsg,
				'usedName' => $usedName,
				'isApproved' => $isApproved,
				'ba_name' => $ba_name,
				'filling_date' => $filling_date,
				'taxEditabled' => $taxEditabled,
				'deadline_date' => $deadline_date,
				'tax' => $tax,
			);
			

			$cache_name = 'budget_plan_'.$term_id.'_'.$year.'_'.$layer_code.'_'.$loginId;
			//Cache::write($cache_name, $budgetData);
			Cache::write($cache_name, $cache_data);
			// $cache_acc_name = 'Sub_Acc_'.$term_id.'_'.$year.'_'.$layer_code.'_'.$loginId;
			// Cache::write($cache_acc_name, $saveAccName);
			
			$UploadFileData['monthDisabled'] = $monthDisabled - ($start_month[0]['Term']['start_month'] - 1);
			$UploadFileData['Months'] = $Months;
			// Cache::write('UploadFileData', $UploadFileData);
			
			return $cache_data;
			// $this->set(compact('subAccount','Months','term','layer_code', 'budgetData', 'successMsg', 'usedName', 'isApproved', 'ba_name', 'filling_date', 'taxEditabled', 'deadline_date', 'tax'));
		}
	}

	/**
	* @author Ei Thandar Kyaw on 22/07/2020
	*/
	public function saveData()
	{ 
		if ($this -> request-> is('post')) {
			if ($this->Session->check('TERM_ID')) {
				$term_id = $this->Session->read('TERM_ID');
			}
			if ($this->Session->check('TERM_NAME')) {
				$budget_term = $this->Session->read('TERM_NAME');
			}
			if ($this->Session->check('LOGIN_ID')) {
				$loginId = $this->Session->read('LOGIN_ID');
			}
			if ($this->Session->check('SESSION_LAYER_CODE')) {
				$layer_code = $this->Session->read('SESSION_LAYER_CODE');
			}
			if ($this->Session->check('HEAD_DEPT_ID')) {
				$headQuarterId = $this->Session->read('HEAD_DEPT_ID');
			}
			if ($this->Session->check('HEAD_DEPT_CODE')) {
				$hqDepCode = $this->Session->read('HEAD_DEPT_CODE');
			}
			$Common = new CommonController;

			$param = [];
			$budgetArr = [];
			$totalArr = [];
			$fbdData = [];
			$fbdDataUpdate = [];
			$subAccId = 0;
			$deleteIDArr = [];
			$updateIDArr = [];
			$requestData = $this->request->data;
			$year = $requestData['year'];
			$Name = '';
			$sumName = '';
			$sumFunctName = '';
			$dbColName = '';
	
			if ($requestData['formType'] == 'forecast') {
				$Name = 'BrmExpected';
				$sumName = 'BrmForecastSummary';
				$dbColName = 'forecast';
			} else {
				$Name = 'BrmBudgetPrime';
				$sumName = 'BrmBudgetSummary';
				$dbColName = 'budget';
			}

			if ($requestData['filling_date'] == '') {
				$filling_date = date("Y-m-d H:i:s");
			} else {
				$filling_date = $requestData['filling_date'];
			}
			$account_pair = $Common->getPairedAccount($hqDepCode, $year);

			$baBudgetTax = Setting::BA_BUDGET_TAX;
			$taxEditabled = false;
			// if ba are 8028,802C,8001, allow to type for tax value in 社内税金
			if (in_array($layer_code, $baBudgetTax)) {
				$taxEditabled = true;
			}

			// tax is disabled
			if (!$taxEditabled) {
				
				// get value for '税引前利益'
				$beforeTax = $requestData['beforeTax'];
				// get first active month
				foreach ($requestData['budget'] as $key => $value) {
					$activeMonthName = explode('_', key($value));
					$firstActiveMonth = $activeMonthName[1];
				}
				$taxId = key($beforeTax);
				foreach ($beforeTax[$taxId] as $key => $value) {
					$cMonthName = explode('_', $key);
					$cMonth = $cMonthName[1];
					// for enable value
					if ($cMonth >= $firstActiveMonth) {
						$preTaxProfit[$key] = $value;
					} else {
						$requestData['budget'][$taxId][$key] = $value; // for disable value
						$requestData['hid_budget'][$taxId][$key] = $value; // for disable value
					}
				}
				// calculate tax for enable value
				$taxValue = $Common->getTaxValue($preTaxProfit, $year);
				
				foreach ($taxValue as $key => $value) {
					$requestData['budget'][$taxId][$key] = $value;
					$requestData['hid_budget'][$taxId][$key] = $value;
				}
			}
			foreach ($requestData['hid_budget'] as $acc_id => $amounts) {
				//list out sub accounts to update
				$deleteIDArr[] = $acc_id;

				$tmp['brm_term_id'] 	= $term_id;
				$tmp['target_year'] = $year;
				$tmp['layer_code'] 	= $layer_code;
				$tmp['filling_date'] = $filling_date;
				$tmp['brm_account_id'] 	= $acc_id;
				$tmp['account_code'] = $this->getFirstAccountCode($acc_id);
				$tmp['logistic_index_no'] 	= '';
				
				foreach ($amounts as $field => $amount) {
					$month_amt = preg_replace("/[^-0-9\.]/", "", $amount);
					$tmp[$field] 	= $month_amt*1000;
				}
				$tmp['flag'] = 1;
				$tmp['created_by'] 		= $loginId;
				$tmp['updated_by'] 		= $loginId;

				# assign to newly save data
				$budgetArr[] = $tmp;
			}
			foreach ($requestData['hid_budget_total'] as $sub_name => $total_value) {
				$total_value = str_replace(',', '', $total_value);
				$total_value = $total_value*1000;
				$totalArr[$account_pair[$sub_name]] += $total_value;
			}
			foreach ($totalArr as $subname => $amt) {
				if (!empty($subname) || $subname != '') {
					
					$tmp1['brm_term_id'] 		= $term_id;
					$tmp1['target_year'] 	= $year;
					$tmp1['layer_code'] 		= $layer_code;
					$tmp1['sub_acc_name'] 		= $subname;
					$tmp1['amount']			= $amt;
					$tmp1['updated_by'] 	= $loginId;
					$tmp1['type'] 			= $dbColName;
					
					$fbdExist = $this->BrmExpectedBudgetDiffAccount->find('first', array(
						'conditions' => array(
							'brm_term_id' => $term_id,
							'target_year' => $year,
							'layer_code' => $layer_code,
							'sub_acc_name' => $subname,
							'type' => $dbColName
						)
					));
					if (!empty($fbdExist)) {

						$dta = $fbdExist['BrmExpectedBudgetDiffAccount'];
						$tmp1['factor'] 	= $dta['factor'];
						$tmp1['deadline_date'] 	= $dta['deadline_date'];
						$tmp1['filling_date'] 	= $dta['filling_date'];
						$tmp1['created_by'] 	= $dta['created_by'];
						$tmp1['updated_by'] 	= $dta['created_by'];
						$tmp1['created_date'] 	= $dta['created_date'];
					} else {
						$tmp1['created_by'] 	= $loginId;
					}
					$fbdData[] = $tmp1;
				}
			}

			if (!empty($budgetArr) && !empty($fbdData)) {
			
				$attachDB = $this->$Name->getDataSource();
				$fbdDB = $this->BrmExpectedBudgetDiffAccount->getDataSource();
				
				try {
					$attachDB->begin();
					$fbdDB->begin();
					
					# Delete old data
					$this->$Name->deleteAll(array(
						'brm_term_id' => $term_id,
						'target_year' => $year,
						'layer_code' => $layer_code,
						'brm_account_id' => $deleteIDArr
					), false);
					
					#Save new data
					$this->$Name->saveAll($budgetArr);
					

					$this->BrmExpectedBudgetDiffAccount->deleteAll(array(
						'brm_term_id' => $term_id,
						'target_year' => $year,
						'layer_code' => $layer_code,

					), false);
					$this->BrmExpectedBudgetDiffAccount->saveAll($fbdData);

					$this->$sumName->updateSummaryData($layer_code,$term_id,$year);
					
					$attachDB->commit();
					$fbdDB->commit();

					$successMsg = parent::getSuccessMsg('SS001');
					$this->Flash->set($successMsg, array("key"=>"FYBudgetSuccess"));
				} catch (Exception $e) {
					$attachDB->rollback();
					$fbdDB->rollback();
					$errorMsg = parent::getErrorMsg('SE003');
					$this->Flash->set($errorMsg, array("key"=>"FYBudgetError"));

					CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
				}
			} else {
				$errorMsg = parent::getSuccessMsg('SE017', 'Save');
				// $errorMsg = 'There was no updated data!';
				$this->Flash->set($errorMsg, array("key"=>"FYBudgetError"));
			}

			$this->redirect(array('controller'=>'BrmBudgetPlan/?year='.$year."&".$requestData['formType'],'action'=>'index'));
		}
	}

	public function excelData()
	{
		$this->layout = null;
		$requestData = $this-> request-> data;
		
		$this->autoLayout = false;
		if ($this->Session->check('HEAD_DEPT_CODE')) {
			$headQuarterCode = $this->Session->read('HEAD_DEPT_CODE');
		}
		if ($this->Session->check('TERM_NAME')) {
			$budget_term = $this->Session->read('TERM_NAME');
		}
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('SESSION_LAYER_CODE')) {
			$layer_code = $this->Session->read('SESSION_LAYER_CODE');
		}
		if ($this->Session->check('LayerTypeData')) {
			$layer_types = $this->Session->read('LayerTypeData');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$loginId = $this->Session->read('LOGIN_ID');
		}
		if ($this->Session->check('HEAD_DEPT_NAME')) {
			$headQuarterName = $this->Session->read('HEAD_DEPT_NAME');
		}
		if ($requestData['code']) {
			$layer_code = $requestData['code'];
		}
		if ($requestData['hq']) {
			$headQuarterCode = $requestData['hq'];
		}
		if ($requestData['termId']) {
			$term_id = $requestData['termId'];
		}
		if ($requestData['formType']) {
			$form_type = $requestData['formType'];
		}
		
		$year = $requestData['year'];

		$file_name = 'BudgetPlan';
		$PHPExcel = $this->PhpExcel;

		if ($form_type == 'forecast') {
			$file_name = 'Forecast';
		} else {
			$file_name = 'Budget';
		}

		$this->DownloadExcel($term_id, $budget_term, $headQuarterName, $headQuarterCode, $layer_code, $year, $form_type, $loginId, $file_name, $PHPExcel,$layer_types);
		
		$this->redirect(array('controller'=>'BrmBudgetPlan/?year='.$year.'&'.$requestData['formType'],'action'=>'index'));
	}

	public function DownloadExcel($term_id, $budget_term, $headQuarterName, $headQuarterCode, $layer_code, $year, $form_type, $loginId, $file_name, $PHPExcel, $layer_types = null,$save_into_tmp = null)
	{
		$Common = new CommonController;
		$Months = $Common->get12Month($term_id);
		$layer_type = $layer_types[SETTING::LAYER_SETTING['bottomLayer']];

		$baArr = $this->Layer->find('all', array('conditions' => array('Layer.flag' => '1', 'Layer.layer_code' => $layer_code)));
		// get sub account name

		$Account = $this->Layer->getAccountByLayer($headQuarterCode, $year);
		// $Account = $this->BrmAccount->getAccountByHeadQuarter($headQuarterCode, $year);

		// set account order
		$order = array();
		foreach ($Account as $key => $row) {
			$order[$key] = $row['brm_account_setups']['order'];
			// get account code
			$accountCode = $this->Layer->getAccountCode($row['brm_accounts']['id']);
			
			$Account[$key]['brm_accounts']['account_code'] = $accountCode[0]['brm_saccounts']['account_code'];
		}

		array_multisort($order, SORT_ASC, $Account);
		if ($form_type == 'forecast') {
			$usedName = array(
				'name'=> '見込',
				'formName'=> '見込フォーム',
				'term'=> '期間',
				'year'=>'年度',
				'unit'=>'(単位：千円）'
				
			);
		} elseif ($form_type == 'budget') {
			$usedName = array(
				'name'=> '予算',
				'formName'=> '予算フォーム',
				'term'=> '期間',
				'year'=>'年度',
				'unit'=>'(単位：千円）'
				
			);
		}
		
		#Get data from cache
		$cache_name = 'budget_plan_'.$term_id.'_'.$year.'_'.$layer_code.'_'.$loginId;
		$budgetData = Cache::read($cache_name);
		$excelData = array();
		$i = 0;
		foreach ($budgetData['budgetData'] as $key => $monthValue) {
			$excelData[$i][] = $key;
			foreach ($monthValue['hid_amount'] as $mKey => $mValue) {
				$excelData[$i][] = preg_replace("/[^-0-9\.]/", "", $mValue);
				if ($monthValue['disable'.$mKey] == 'disabled') {
					$excelData[$i]['disabled'][] = $mKey;
				}
			}
			$excelData[$i]['type'] = $monthValue['type'];
			
			$calculation_method = json_decode($monthValue['calculation_method'], 1);
			$excelData[$i]['calculation_method'] = $calculation_method['field'];
			$excelData[$i]['setDefaultValue'] = $monthValue['setDefaultValue'];
			$i++;
		}
		$baBudgetTax = Setting::BA_BUDGET_TAX;
		$taxEditabled = false;
		// if ba are 8028,802C,8001, allow to type for tax value in 社内税金
		if (in_array($layer_code, $baBudgetTax)) {
			$taxEditabled = true;
		}
		
		// $resultExistMonths = $this->BrmActualResultSummary->find('list', array(
		// 		'fields' => 'target_month',
		// 		'conditions' => array(
		// 			'head_dept_id' => $headQuarterId,
		// 		),
		// 		'group' => 'target_month'
		// 	));

		#Get budget start and end month
		$start_month = $Common->getMonth($year, $term_id, 'start');
		// allow to tax for ba
		$baBudgetTax = Setting::BA_BUDGET_TAX;

		$objWorkSheet = $PHPExcel->createWorksheet()->setDefaultFont('Calibri', 12);

		$centerHorizontalAlign_style = array(
					'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
			);
		$rightHorizontalAlign_style = array(
					'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					)
			);
		$centerVerticalAlign_style = array(
					'alignment' => array(
							'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					)
			);
		$BStyle = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '808080'),
				),
			),
		);
		$fontStyle = array(
			'font'  => array(
				'color' => array('rgb' => 'FF0000'),
				
		));
		$objWorkSheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		// merge cell
		$objWorkSheet->getActiveSheet()->mergeCells('O1:P1');
		$objWorkSheet->getActiveSheet()->mergeCells('O2:P2');
		$objWorkSheet->getActiveSheet()->mergeCells('A3:P3');

		$objWorkSheet->getActiveSheet()->mergeCells('A7:A10');
		$objWorkSheet->getActiveSheet()->mergeCells('B7:O7');
		$objWorkSheet->getActiveSheet()->mergeCells('B8:O8');
		$objWorkSheet->getActiveSheet()->mergeCells('B9:H9');
		$objWorkSheet->getActiveSheet()->mergeCells('I9:O9');
		$objWorkSheet->getActiveSheet()->mergeCells('P7:P10');

		// set background color

		$objWorkSheet->getActiveSheet()->getStyle('A7:A10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
		$objWorkSheet->getActiveSheet()->getStyle('B7:O7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
		$objWorkSheet->getActiveSheet()->getStyle('B8:O8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
		$objWorkSheet->getActiveSheet()->getStyle('B9:O9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
		$objWorkSheet->getActiveSheet()->getStyle('B10:O10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
		$objWorkSheet->getActiveSheet()->getStyle('P7:P10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
		// set border
		$objWorkSheet->getActiveSheet()->getStyle("A7:A10")->applyFromArray($BStyle);
		$objWorkSheet->getActiveSheet()->getStyle("B7:O7")->applyFromArray($BStyle);
		$objWorkSheet->getActiveSheet()->getStyle("B8:O8")->applyFromArray($BStyle);
		$objWorkSheet->getActiveSheet()->getStyle("B9:O9")->applyFromArray($BStyle);
		$objWorkSheet->getActiveSheet()->getStyle("B10:O10")->applyFromArray($BStyle);
		$objWorkSheet->getActiveSheet()->getStyle("P7:P10")->applyFromArray($BStyle);
		
		$objWorkSheet->getActiveSheet()->getStyle("O1")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle("O2")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle("A3")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);

		$objWorkSheet->getActiveSheet()->getStyle("B7:O7")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('B7:O7')->getFont()->setBold(true);


		$objWorkSheet->getActiveSheet()->getStyle("B8:O8")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('B8:O8')->getFont()->setBold(true);

		$objWorkSheet->getActiveSheet()->getStyle("B9:O9")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('B9:O9')->getFont()->setBold(true);

		$objWorkSheet->getActiveSheet()->getStyle("B10:O10")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('B10:O10')->getFont()->setBold(true);

		$objWorkSheet->getActiveSheet()->getStyle("P6")->applyFromArray($rightHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle("P7:P10")->applyFromArray($centerHorizontalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle("P7:P10")->applyFromArray($centerVerticalAlign_style);
		$objWorkSheet->getActiveSheet()->getStyle('P7:P10')->getFont()->setBold(true);

		$sheet = $PHPExcel->getActiveSheet();
		$sheet->setCellValue('N1', __($layer_type));
		$sheet->setCellValue('O1', $baArr[0]['Layer']['name_jp'].'('.$layer_code.')');
		$sheet->setCellValue('A3', $year.__($usedName['name']).__($usedName['year']));
		$sheet->setCellValue('P6', $usedName['unit']);
		
		$sheet->setCellValue('B7', $year." ".__($usedName['year']));
		$sheet->setCellValue('B8', __($usedName['name']));
		$sheet->setCellValue('B9', __("上半期"));
		$sheet->setCellValue('I9', __("下半期"));
		$sheet->setCellValue('P7', __("年間"));
		$colName = array("A", "B", "C", "D", "E", "F", "G","H", "I", "J", "K", "L", "M", "N", "O", "P");
		foreach ($Months as $monthK=>$monthV) {
			$monthK = $monthK + 1;

			if ($monthK == 7) {
				$sheet->setCellValue('H10', __("上半期計"));
			} elseif ($monthK > 7) {
				$sheet->setCellValue($colName[$monthK].'10', __($Months[$monthK-2]));
			} else {
				$sheet->setCellValue($colName[$monthK].'10', __($monthV));
			}
			$objWorkSheet->getActiveSheet()->getStyle($colName[$monthK].'10')->applyFromArray($BStyle);
			$objWorkSheet->getActiveSheet()->getColumnDimension($colName[$monthK])->setWidth(12);
		}
		$objWorkSheet->getActiveSheet()->getColumnDimension('N')->setWidth(12);
		$objWorkSheet->getActiveSheet()->getColumnDimension('O')->setWidth(12);
		$objWorkSheet->getActiveSheet()->getColumnDimension('P')->setWidth(12);
		$objWorkSheet->getActiveSheet()->getStyle('N10')->applyFromArray($BStyle);
		$objWorkSheet->getActiveSheet()->getStyle('O10')->applyFromArray($BStyle);
		$sheet->setCellValue('N10', __($Months[11]));
		$sheet->setCellValue('O10', __("下半期計"));

		$sheet->getStyle('H10')->getAlignment()->setWrapText(true);
		$sheet->getStyle('O10')->getAlignment()->setWrapText(true);
		$sumCols = array();
		$sumColsType3 = '';
		$yearlySumCols = '';
		$totalCols = array();
		$salesCols = array();
		$col = 16;
		$dataRowNo = 0;
		
		$forecastPeriod = $this->BrmTerm->getForecastPeriod($term_id);
		// $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];
		$cellName = array();
		$unprotected_row = array();
		foreach ($excelData as $key=>$value) {
			$firstHalf = '';
			$secHalf = '';
			$wholeTotal = '';
			for ($i = 0; $i < $col; $i++) {
				if ($i <= 6) {
					$j = $i - 1;
					$month = date("Y-m", strtotime($start_month. "last day of + ".$j." Month"));
				} elseif ($i > 7) {
					$j = $i - 2;
					$month = date("Y-m", strtotime($start_month. "last day of + ".$j." Month"));
				}
				
				$setGrayColor = false;
				$row = $dataRowNo + 11;
				if ($i == 0) {
					$cellName[$value[$i]] = $row;
					$sheet->setCellValue($colName[$i].$row, $value[$i]);
				}
				// set white background color for sub account name
				$objWorkSheet->getActiveSheet()->getStyle('A'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
				$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->applyFromArray($BStyle);
				if ($i > 0) {
					if ($value['type'] == 0) {
						$showValue = $value[$i];
					} elseif ($value['type'] == 1) { // for total
						$formula = '';
						foreach ($value['calculation_method'] as $cKey=>$cValue) {
							if ($cellName[$cValue]) {
								$formula .= $colName[$i].$cellName[$cValue].'+';
							}
						}
						$formula = substr($formula, 0, -1);
						$showValue = '=SUM('.$formula.')';
					} elseif ($value['type'] == 2) {
						$formula = '';
						$value1 = '';
						$value2 = '';
						foreach ($value['calculation_method'] as $cKey=>$cValue) {
							if ($cKey == 0) {
								$value1 = $colName[$i].$cellName[$cValue];
							} else {
								$value2 = $colName[$i].$cellName[$cValue];
							}
						}
						$formula .=$value2.'/'.$value1;
						
						$showValue = '=IF('.$value1.'=0,"0%",'.$formula.')';
					} elseif ($value['type'] == 3) {
						$setDefaultValue = $value['setDefaultValue'];
						$showValue = 0;
						foreach ($setDefaultValue as $defaultKey=>$defaultValue) {
							$dValue = explode('_', $defaultValue);
							if ($i >= 8) {
								if ($i == ($dValue[1]+1)) {
									$showValue = $value[$i];
								}
							} else {
								if ($i == $dValue[1]) {
									$showValue = $value[$i];
								}
							}
						}
						$monthIndex = $i;
						if ($i >= 8) {
							$monthIndex = $i - 1;
						}
						if (!in_array('month_'.$monthIndex.'_amt', $value['disabled']) && !$taxEditabled) {
							//$tax = Setting::TAX;
							$taxAmount = $this->RtaxFee->find('list', array(
								'fields' => array('target_year', 'rate'),
								'conditions' => array('target_year' => $year, 'flag' => 1)));
								
							$tax = $taxAmount[$year];
							$showValue = '=ROUNDDOWN(-('.$colName[$i].$cellName[$value['calculation_method'][0]].'*'.$tax.'),0)';
						}
						if (strtotime($month) <= strtotime($forecastPeriod) && $layer_code == '9000') {
							$showValue = 0;
						}
					}
					if ($value['type'] == 0 || $value['type'] == 1 || $value['type'] == 3) {
						if ($i == 1 || $i == 2 || $i == 3 || $i == 4 || $i == 5 || $i == 6) {
							$firstHalf .= $colName[$i].$cellName[$value[0]].'+';
						} elseif ($i == 8 || $i == 9 || $i == 10 || $i == 11 || $i == 12 || $i == 13) {
							$secHalf .= $colName[$i].$cellName[$value[0]].'+';
						} elseif ($i == 7) {
							$formula = substr($firstHalf, 0, -1);
							$showValue = '=SUM('.$formula.')';
						} elseif ($i == 14) {
							$formula = substr($secHalf, 0, -1);
							$showValue = '=SUM('.$formula.')';
						} elseif ($i == 15) {
							$formula = substr($firstHalf.$secHalf, 0, -1);
							$showValue = '=SUM('.$formula.')';
						}
					}
					$sheet->setCellValue($colName[$i].$row, $showValue);
				}
				# set one decimal
				if ($colName[$i] != 'A') {
					if ($value['type'] == 2) {
						$sheet->getStyle($colName[$i].$row)->getNumberFormat()->setFormatCode('0%');
					} else {
						$sheet->getStyle($colName[$i].$row)->getNumberFormat()->setFormatCode('#,##0.0;[Red]-#,##0.0');
					}
					$amount = str_replace(",", "", $value[$i]);
				}
				# text align right
				if ($i != 0) {
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->applyFromArray($rightHorizontalAlign_style);
				}
				
				
				if (($i == 7 || $i >= 14) || ($value['type'] == 1 || $value['type'] == 2)) {
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->applyFromArray($BStyle);
				}
				# set gray background color for disable rows
				$disable_accs = Setting::BUDGET_DISABLE_ACCS;
				$restrict_hqs = Setting::TRADING_DISABLE_HQS;
				
				// if fiels are disable , set gray color
				if (in_array($value[0], $disable_accs) || in_array('month_'.$monthIndex.'_amt', $value['disabled'])) {
					$setGrayColor = true;
				}
				# if hq are 管理本部,内部監査室, 売上高、売上原価 this fields are set green color
				if (in_array($headQuarterName, $restrict_hqs) && ($value[0] == '売上高' || $value[0] == '売上原価')) {
					$setGrayColor = false;
				}

				if ($setGrayColor) {
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d9d9d9');
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->applyFromArray($BStyle);
				} elseif ($value['type'] == 0 || (in_array($layer_code, $baBudgetTax) && $value['type'] == 3)) {
					// set green color
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D5F4FF');
					// if month are approve , set gray color
					if (strtotime($month) <= strtotime($forecastPeriod)) {
						$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d9d9d9');
					} else {
						// set editable field in array to unlock
						// column name H, O and P are calculation field
						if ($colName[$i] != 'H' && $colName[$i] != 'O' && $colName[$i] != 'P') {
							array_push($unprotected_row, $colName[$i].$row);
						}
					}
				}
				# set bold
				if (($value['type'] == 1 || $value['type'] == 2) && $i == 0) {
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->getFont()->setBold(true);
				}
				# set white background color for first half total, second half total and yearly
				if ($i == 7 || $i == 14 || $i == 15) {
					$objWorkSheet->getActiveSheet()->getStyle($colName[$i].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffff');
				}
			}
			$dataRowNo++;
		}
		
		// lock for excel file - not allow to edit
		$sheet->getProtection()->setPassword('*****');
		$sheet->getProtection()->setSheet(true);
		$sheet->getProtection()->setInsertRows(true);
		$sheet->getProtection()->setInsertColumns(true);
		foreach ($unprotected_row as $unprotect) {
			// unlock for editable field
			$sheet->getStyle($unprotect)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
		}
		#get sheet name
		$file_name = (count($file_name) == 2) ? $file_name[1] : $file_name;
		if ($save_into_tmp) {
			//Backup Master
			$PHPExcel->save($file_name); 	
		} else {
			$PHPExcel->output($file_name.'_'.$year.".xlsx");
		}
	}

	public function getFirstAccountCode($acc_id)
	{
		$account_data = $this->BrmSaccount->find('first', array(
			'fields' => 'BrmSaccount.account_code',
			'conditions' => array(
				'BrmSaccount.brm_account_id' => $acc_id,
				'BrmSaccount.flag' => 1
			),
			'order' => 'BrmSaccount.account_code ASC'
		));

		$account_code = $account_data['BrmSaccount']['account_code'];

		return $account_code;
	}
	public function saveUploadFile()
	{
		App::import('Vendor', 'php-excel-reader/PHPExcel');
		$login_id = $this->Session->read('LOGIN_ID'); #get login id
		$date = date('Y-m-d H:i:s');
		if ($this->Session->check('HEAD_DEPT_NAME')) {
			$headQuarterName = $this->Session->read('HEAD_DEPT_NAME');
		}
		if ($this->Session->check('HEAD_DEPT_CODE')) {
			$head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
		}
		if ($this->Session->check('TERM_NAME')) {
			$term = $this->Session->read('TERM_NAME');
		}
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('SESSION_LAYER_CODE')) {
			$layer_code = $this->Session->read('SESSION_LAYER_CODE');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$loginId = $this->Session->read('LOGIN_ID');
		}
		if ($this->Session->check('Config.language')) {
			$language = $this->Session->read('Config.language');
		}
		$ba_name = $this->Session->read('BUDGET_BA_NAME');
		$year = $this->request->data['year'];
		$requestData['year'] = $this->request->data['year'];
		$requestData['formType'] = $this->request->data['formType'];
		$requestData['filling_date'] = $this->request->data['filling_date'];
		#Get data from cache to get sub account name to save to db
		$cache_name = 'budget_plan_'.$term_id.'_'.$year.'_'.$layer_code.'_'.$loginId;
		$cache_data = Cache::read($cache_name);
		$saveAccName = $cache_data['saveAccName'];
		#Get data for disabled months and months name
		//$UploadFileData = $cache_data['UploadFileData'];
		$UploadFileData = $cache_data;
		
		if ($requestData['formType'] == 'budget') {
			$Name = 'BrmBudgetPrime';
			$monthDisabled = 0;
		} else {
			$Name = 'BrmExpected';
			$monthDisabled = $UploadFileData['monthDisabled'];
		}
		$Months = $UploadFileData['Months'];
		$month_12 =array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		#Translate months name based on language

		if ($language == 'eng') {
			foreach ($Months as $mKey=>$mValue) {
				$monthIndex = substr($mValue, 0, -3);
				$Months[$mKey] = $month_12[$monthIndex - 1];
			}
			$Months[12] = 'First Half Total';
			$Months[13] = 'Second Half Total';
			$Months[14] = 'Yearly';
		} else {
			$Months[12] = '上半期計';
			$Months[13] = '下半期計';
			$Months[14] = '年間';
		}
		
		// to get id and sub account name

		$account = $this->Layer->getAccountByLayer($head_dept_code, $year);

		$accName = array();
		
		foreach ($account as $key => $row) {

			if (in_array($row['brm_accounts']['name_jp'], $accName)) {
				unset($account[$key]);
			}
			$accName[] = $row['brm_accounts']['name_jp'];
			if ($row['brm_accounts']['type'] == 0) {
				$accType[$row['brm_accounts']['name_jp']] = $row['brm_accounts']['type'];
			}
		}
		$file 			= $this->request->params['form']['budget_upload'];
		$data 			= $_FILES;
		$head_dept_code 	= key($_FILES); #not choose head_dept_name in term selection
		$file_name 		= $file['name'];
		$file_path 		= $file['tmp_name'];
		$ext 			= pathinfo($file_name, PATHINFO_EXTENSION);
		$error 			= 'true';
		
		$error = (empty($file)) ? 				parent::getErrorMsg('SE015') :
				 ($file['error'] != 0) ? 		parent::getErrorMsg('SE015') :
				 ($file['size'] >= 1048576) ? 	parent::getErrorMsg('SE020') :
				 (!($ext == "xlsx" || $ext == "xls")) ? parent::getErrorMsg("SE013", $ext) : 'true';
		if ($error == 'true') {

			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objReader->setReadDataOnly(true);

			if ($objReader->canRead($file_path)) {
				$objPHPExcel   = $objReader->load($file_path);
				$objWorksheet  = $objPHPExcel->getActiveSheet();
				$highestRow    = $objWorksheet->getHighestRow();
				$highestColumn = $objWorksheet->getHighestColumn();
				#get account name in import file
				for ($row = 11; $row <= $highestRow; $row++) {
					$accountsData = $objWorksheet->rangeToArray('A' . $row, null, true, true);
					$accountsName[] = $accountsData;
				}
				#get column title name(months name) and value in import file
				for ($row = 7; $row <= $highestRow; $row++) {
					$rowData = $objWorksheet->rangeToArray('A' . $row . ':' . 'P' . $row, null, true, false);
					if ($row == 7 || $row == 10) {
						if ($row == 7) {
							$yearyly = $rowData[0][15];
						} else {
							//months name
							$rowData[0][15] = $yearyly;
							$titleArr[] = $rowData;
						}
					} else {
						$worksheets[] = $rowData;
					} // value
				}
			}

			$error = '';
			if ($yearyly == '') {
				$error = parent::getErrorMsg('SE021');
			}
			// check months name
			foreach ($titleArr[0][0] as $tKey=>$tValue) {
				if ($tKey > 0) {
					if (!in_array($tValue, $Months) && $error == '') {
						$error = parent::getErrorMsg('SE022');
					}
				}
			}
			// check sub account name
			if ($error == '' && sizeof($accountsName) > 0) {
				foreach ($accountsName as $key => $value) {
					if (!in_array($value[0][0], $accName)) {
						$error = parent::getErrorMsg('SE103');
					}
				}
			}
			
			if ($error != '') {
				$this->Flash->set($error, array("key"=>"FYBudgetError"));
			} else {

				$baBudgetTax = Setting::BA_BUDGET_TAX;
				$taxEditabled = false;
				// if ba are 8028,802C,8001, allow to type for tax value in 社内税金
				if (in_array($layer_code, $baBudgetTax)) {
					$taxEditabled = true;
				}
				foreach ($worksheets as $key=> $value) {
					// create array to save forecast/budget
					if (in_array($value[0][0], $saveAccName)) {
						
						// create array to save to ExpectedBudgetDiffAcc
						foreach ($value[0] as $mKey=>$mValue) {
							if ($mKey == 1 || $mKey == 2 || $mKey == 3 || $mKey == 4 || $mKey == 5 || $mKey == 6 ||
							$mKey == 8 || $mKey == 9 || $mKey == 10 || $mKey == 11 || $mKey == 12 || $mKey == 13) {
								//$requestData['budget_total'][$value[0][0]] += round($mValue, 1);
								$requestData['budget_total'][$value[0][0]] += $mValue;
							}
						}
						$bugKey = array_search($value[0][0], $saveAccName);
						foreach ($value[0] as $mkey=>$mValue) {
							// check value is numeric or not
							if ($mkey > 0) {
								if ($mkey < 7) {
									$monthName = $Months[$mkey - 1];
								} else {
									$monthName = $Months[$mkey - 2];
								}
								if ($mkey != 7 && $mkey != 14 && $mkey != 15) {
									if ($value[0][$mkey] != '') {
										if (is_numeric($value[0][$mkey])) {
											if (!preg_match('/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/', $value[0][$mkey])) {
												$error .= $value[0][0]." in ".$monthName.", ";
											}
										} else {
											if ($value[0][0] != '社内税金') {
												$error .= $value[0][0]." in ".$monthName.", ";
											}
										}
									}
								}
							}
							if ($value[0][0] == '社内税金' && $mkey > 0 && $mkey < 13 && !$taxEditabled) {
								if ($mkey > 6) {
									$requestData['budget'][$bugKey]['month_'.$mkey.'_amt'] = $value[0][$mkey + 1];
								} else {
									$requestData['budget'][$bugKey]['month_'.$mkey.'_amt'] = $value[0][$mkey];
								}
							}
							if ($mkey > 0 && $mkey < 13 && $mkey > $monthDisabled) {
								if ($mkey > 6) {
									$requestData['budget'][$bugKey]['month_'.$mkey.'_amt'] = $value[0][$mkey + 1];
								} else {
									$requestData['budget'][$bugKey]['month_'.$mkey.'_amt'] = $value[0][$mkey];
								}
							}
						}
					} else {
						
					}
				}
				if ($error != '') {
					$error = substr($error, 0, -2);
					$error = parent::getErrorMsg('SE099', [__('10 digits with 3 digits decimal point'),$error,'']);
					$this->Flash->set($error, array("key"=>"FYBudgetError"));
				} else {
					$head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
					$message = $this->saveImportData($requestData);
					$this->updateTaxAmount($term_id, $term, $head_dept_code, $headQuarterName, $layer_code, $ba_name, $year, $loginId, $Name);
					$successMsg = parent::getSuccessMsg('SS001');
					$this->Flash->set($successMsg, array("key"=>"FYBudgetSuccess"));
				}
			}
		} else {
			$this->Flash->set($error, array("key"=>"FYBudgetError"));
		}
		$this->redirect(array('controller'=>'BrmBudgetPlan/?year='.$year."&".$requestData['formType'],'action'=>'index'));
	}
	public function saveImportData($requestData)
	{
		if ($this->Session->check('TERM_ID')) {
			$term_id = $this->Session->read('TERM_ID');
		}
		if ($this->Session->check('LOGIN_ID')) {
			$loginId = $this->Session->read('LOGIN_ID');
		}
		if ($this->Session->check('SESSION_LAYER_CODE')) {
			$layer_code = $this->Session->read('SESSION_LAYER_CODE');
		}
		if ($this->Session->check('HEAD_DEPT_CODE')) {
			$head_dept_code = $this->Session->read('HEAD_DEPT_CODE');
		}
		$Name = '';
		$dbColName = '';
		if ($requestData['formType'] == 'forecast') {
			$Name = 'BrmExpected';
			$dbColName = 'forecast';
		} else {
			$Name = 'BrmBudgetPrime';
			$dbColName = 'budget';
		}
		$year = $requestData['year'];
		$Common = new CommonController;
		$account_pair = $Common->getPairedAccount($head_dept_code, $year);
		
		if ($requestData['filling_date'] == '') {
			$filling_date = date("Y-m-d H:i:s");
		} else {
			$filling_date = $requestData['filling_date'];
		}
		
		foreach ($requestData['budget'] as $acc_id => $amounts) {

			//list out sub accounts to update
			$deleteIDArr[] = $acc_id;

			$tmp['brm_term_id'] 	= $term_id;
			$tmp['target_year'] = $year;
			$tmp['layer_code'] 	= $layer_code;
			$tmp['filling_date'] = $filling_date;
			$tmp['brm_account_id'] 	= $acc_id;
			$tmp['account_code'] = $this->getFirstAccountCode($acc_id);
			$tmp['logistic_index_no'] 	= '';

			foreach ($amounts as $field => $amount) {
				$month_amt = preg_replace("/[^-0-9\.]/", "", $amount);
				//$tmp[$field] 	= round($month_amt, 1)*1000;
				$tmp[$field] 	= $month_amt*1000;
			}
			$tmp['flag'] = 1;
			$tmp['created_by'] 		= $loginId;
			$tmp['updated_by'] 		= $loginId;

			# assign to newly save data
			$budgetArr[] = $tmp;
		}
		foreach ($requestData['budget_total'] as $sub_name => $total_value) {
			$total_value = str_replace(',', '', $total_value);
			$total_value = $total_value*1000;
			$totalArr[$account_pair[$sub_name]] += $total_value;
		}
		
		foreach ($totalArr as $subname => $amt) {
			if (!empty($subname) || $subname != '') {
				$deleteSubnameArr[] = $subname;
				$tmp1['brm_term_id'] 	= $term_id;
				$tmp1['target_year'] 	= $year;
				$tmp1['layer_code'] 	= $layer_code;
				$tmp1['sub_acc_name'] 	= $subname;
				$tmp1['amount']			= $amt;
				$tmp1['updated_by'] 	= $loginId;
				$tmp1['type'] 			= $dbColName;
				$fbdExist = $this->BrmExpectedBudgetDiffAccount->find('first', array(
					'conditions' => array(
						'brm_term_id' => $term_id,
						'target_year' => $year,
						'layer_code' => $layer_code,
						'sub_acc_name' => $subname,
						'type' => $dbColName
					)
				));

				if (!empty($fbdExist)) {
					$dta = $fbdExist['BrmExpectedBudgetDiffAccount'];
					$tmp1['factor'] 	= $dta['factor'];
					$tmp1['deadline_date'] 	= $dta['deadline_date'];
					$tmp1['filling_date'] 	= $dta['filling_date'];
					$tmp1['created_by'] 	= $dta['created_by'];
					$tmp1['updated_by'] 	= $dta['created_by'];
					$tmp1['created_date'] 	= $dta['created_date'];
				} else {
					$tmp1['created_by'] 	= $loginId;
				}
				$fbdData[] = $tmp1;
			}
		}
		if (!empty($budgetArr) && !empty($fbdData)) {
			$attachDB = $this->$Name->getDataSource();
			$fbdDB = $this->BrmExpectedBudgetDiffAccount->getDataSource();
			try {
				$attachDB->begin();
				$fbdDB->begin();
				# Delete old data
				$this->$Name->deleteAll(array(
					'brm_term_id' => $term_id,
					'target_year' => $year,
					'layer_code' => $layer_code,
					'brm_account_id' => $deleteIDArr
				), false);
				#Save new data
				$this->$Name->saveAll($budgetArr);

				$this->BrmExpectedBudgetDiffAccount->deleteAll(array(
					'brm_term_id' => $term_id,
					'target_year' => $year,
					'layer_code' => $layer_code,
					'sub_acc_name' => $deleteSubnameArr

				), false);
				$this->BrmExpectedBudgetDiffAccount->saveAll($fbdData);
				$attachDB->commit();
				$fbdDB->commit();
				$successMsg = parent::getSuccessMsg('SS001');
				return $successMsg;
			} catch (Exception $e) {
				$attachDB->rollback();
				$fbdDB->rollback();
				$errorMsg = parent::getErrorMsg('SE003');

				CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
				return $errorMsg;
			}
		}
	}

	/**
	 * updateTaxAmount method
	 *
	 * @author PanEiPhyo on 2021/12/16
	 * @return boolean
	 */
	public function updateTaxAmount($term_id, $term, $headQuarterId, $headQuarterName, $layer_code, $ba_name, $year, $loginId, $Name)
	{
		$subAccName = Setting::TAX_ACCNAME;
		$tax_save_data = [];
        $save_to_tmp = true; # skip session read
        $form_type = ($Name == 'BrmExpected')? 'forecast' : 'budget';
		#Get calculated tax data
		$data = $this->getBudgetData($term_id, $term, $headQuarterId, $headQuarterName, $layer_code, $ba_name, $year, $loginId,$save_to_tmp,$form_type);
		$tax_data = $data['budgetData'][$subAccName]['amount'];

		#get tax account code

		//$subAccount = $this->Layer->getAccountByLayer($headQuarterId, $year, $subAccName);
		$account = $this->Layer->getAccountByLayer($headQuarterId, $year, $subAccName);
		$acc_id = $account[0]['brm_account_setups']['brm_account_id'];
		$tax_acc_code = $this->getFirstAccountCode($acc_id);

		foreach ($tax_data as $month => $value) {
			$tax_save_data[0][$month] = $value*1000;
		}

		#save tax amounts
		if (!empty($tax_save_data) && !empty($tax_acc_code)) {
			$attachDB = $this->$Name->getDataSource();
			
			try {
				$attachDB->begin();

				# Check old data
				$exist_id = $this->$Name->find('list',array(
					'conditions' => array(
						'brm_term_id' => $term_id,
						'target_year' => $year,
						'layer_code' => $layer_code,
						'account_code' => $tax_acc_code
					)
				));

				if (count($exist_id) == 1) {
					$tax_save_data[0]['id'] = array_values($exist_id)[0];
				} else {
					# Delete old data
					$this->$Name->deleteAll(array(
						'brm_term_id' => $term_id,
						'target_year' => $year,
						'layer_code' => $layer_code,
						'account_code' => $tax_acc_code
					), false);

					if (!empty($tax_data)) {
						$tax_save_data['brm_term_id'] = $term_id;
						$tax_save_data['target_year'] = $year;
						$tax_save_data['layer_code'] = $layer_code;
						$tax_save_data['brm_account_id'] = $acc_id;
						$tax_save_data['account_code'] = $tax_acc_code;
						$tax_save_data['flag'] = 1;
						$tax_save_data['created_by'] = $loginId;
						$tax_save_data['updated_by'] = $loginId;
						
					}
				}

				#Save new data
				$this->$Name->saveAll($tax_save_data);
				$attachDB->commit();
				return true;
			} catch (Exception $e) {
				$attachDB->rollback();
				
				CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

				return false;
			}
		} else {
			return false;
		}
	}

	public function saveBulkExcelFile(){
		
        if ($this->request->is('post')) {
            $Common = new CommonController();
            $year = $this->request->data('year');
			$form_type = $this->request->data('formType');
            $file = $this->request->params['form']['btn_upload_bulk_file'];
            $messages = $Common->importBulkExcelFile($file);
            if(!empty($messages['error'])){
                $this->Flash->set($messages['error'], array('key'=>'FYBudgetError'));
            }else{
				$allSuccess = false;
				foreach($messages as $msg_key => $msg_value){
					$tmp_msg = explode('_', $msg_key);
					if(!empty($msg_value['success'])){
						$allSuccess = true;
					}else if(!empty($msg_value['error'])){
						$allSuccess = false;
						break;
					} 
				}
			
				if($allSuccess){
					// $successMsg = $tmp_msg[0].' '.__('年度計画フォームが正常に更新されました！');
					$successMsg = parent::getSuccessMsg('SS031');
					$this->Flash->set($successMsg, array('key'=>'FYBudgetSuccess'));	
				}else{
					foreach($messages as $msg_key => $msg_value){
						$tmp_msg = explode('_', $msg_key);
						if(!empty($msg_value['success'])){
							$successMsg = $tmp_msg[0].' '.__($tmp_msg[1]).':'.$msg_value['success'];
							$this->Flash->set($successMsg, array('key'=>'FYBudgetSuccess'));
						}
						if(!empty($msg_value['error'])){
							$errorMsg = $tmp_msg[0].' '.__($tmp_msg[1]).':'.$msg_value['error'];
							$this->Flash->set($errorMsg, array('key'=>'FYBudgetError'));
						}    
					}
				}
            }
            $this->redirect(array('controller'=>'BrmBudgetPlan','action'=>'index/?year='.$year.'&'.$form_type));           
        } else {
            // $this->errorCommonMsg('SE015');
			$this->Flash->set(parent::getErrorMsg('SE015'), array('key'=>'FYBudgetError'));
            $this->redirect(array('controller'=>'BrmBudgetPlan','action'=>'index/?year='.$year.'&'.$form_type));
        }
    }

	public function getMailLists() {
        #only allow ajax request
        $language = $this->Session->read('Config.language');
        parent::checkAjaxRequest($this);
        $Common     = New CommonController();
        $period     = $this->Session->read('TARGETMONTH');
        $layer_name = $this->Session->read('BUDGET_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $page       = $_POST['page'];
        $function   = $_POST['function'];
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $period, Setting::LAYER_SETTING['bottomLayer']);
        return json_encode($mails);
    }

	public function downloadBulkExcelDownload(){
        $Common = new CommonController();
        $term_id = $this->Session->read('TERM_ID');;
        $budget_term = $this->Session->read('TERM_NAME');
        $hlayer_code = $this->Session->read('HEAD_DEPT_CODE');
        $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');
        $login_id = $this->Session->read('LOGIN_ID');
        $Common->combineAsExcelSheets($term_id, $budget_term, $hlayer_code, $head_dept_name, $login_id, $this);

    }
}
