<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BudgetPlan');
/**
 * TransactionPlans Controller
 *
 * @property TransactionPlan $TransactionPlan
 * @property PaginatorComponent $Paginator
 */
class TradingPlanController extends AppController
{

/**
 * Components
 *
 * @var array
 */
    public $components = array('Session','PhpExcel.PhpExcel');
    public $uses = array('TradingPlanModel','AccountModel','HeadDepartmentModel','AccountSetupModel','BudgetPrimeModel','ExpectedModel','BusinessAreaModel','BudgetLogModel', 'ActualResultSummaryModel', 'SubAccountModel', 'TermModel','LogisticModel', 'HeadquartersDeadlineModel','TaxAmountModel','BudgetSummaryModel','ForecastSummaryModel');
    public $helpers = array('Html', 'Form', 'Csv');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();

        #get ba code from term selection
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $login_id = $this->Session->read('LOGIN_ID');
        }
        #get permission when login
        if ($this->Session->check('PERMISSION')) {
            $permission = $this->Session->read('PERMISSION');
        }
        if ($_GET['code']) {
            $ba_code = $_GET['code'];
        }

        #get read_limit 1 or 2 or 3 or 4
        $read_limit   = $permission['BudgetingSystemReadLimit'];
        
        #flag to show hide approve button in view
        $Common = new CommonController;
        $readLimit = $Common->checkLimit($read_limit, $ba_code, $login_id);
    
        if ($readLimit != 'true') {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'TermSelection', 'action'=>'index'));
        }
    }

    /**
     * index method
     *
     * @author PanEiPhyo (20200226)
     * @return void
     */
    public function index()
    {
        $this->layout = 'phase_3_menu';
        $Common = new CommonController;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');

        if ($this->Session->check('TERM_NAME')) {
            $forecast_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('BUDGET_BA_NAME')) {
            $ba_name = $this->Session->read('BUDGET_BA_NAME');
        }
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $headQuarterId = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if ($this->Session->check('PERMISSION')) {
            $permission = $this->Session->read('PERMISSION');
        }
        if ($this->Session->check('HEADQUARTER_DEADLINE')) {
            $hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
        }
        
        $language = $this->Session->read('Config.language');
        $year = $_GET['year']; #get year from url
        if ($_GET['code']) {
            $ba_code = $_GET['code'];
        }
        if ($_GET['hq']) {
            $headQuarterId = $_GET['hq'];
        }
        if ($_GET['term']) {
            $term_id = $_GET['term'];
        }

        # come from budget progress report link
        $tr_data = $this->getTradingDataAndCaching($year, $term_id, $forecast_term, $headQuarterId, $ba_code, $ba_name, $loginId, $language);

        $trade_data = $tr_data['trade_data'];
        $table_months = $tr_data['table_months'];
        $months = $tr_data['months'];
        $errormsg = $tr_data['errormsg'];
        $forecast_term = $tr_data['forecast_term'];
        $budget_BA = $tr_data['budget_BA'];
        $destination = $tr_data['destination'];
        $trade_filling_date = $tr_data['trade_filling_date'];
        $deadline_date = $tr_data['deadline_date'];
        $logistic_data = $tr_data['logistic_data'];
        $approved_BA = $tr_data['approved_BA'];
        $approveHQ = $tr_data['approveHQ'];
        $forecastMonth = $tr_data['forecastMonth'];
        $startMonth = $tr_data['startMonth'];
        $forecast_month = $tr_data['forecast_month'];
        $start_month = $tr_data['start_month'];
        $result_index_no = $tr_data['result_index_no'];

        $short_year = substr($year, -2); #get last two number from year eg: 2021 => 21

        # show warining alert
        if ($this->Session->check('CHECKACTUAL')) {
            $chk = $this->Session->read('CHECKACTUAL');
            $this->Session->delete('CHECKACTUAL');
        }
        # check the permission
        $createlimit = $permission['BudgetingSystemCreateLimit'];
        $createLimit = $Common->checkLimit($createlimit, $ba_code, $loginId);
        
        # disabled/enabled(input field and button )
        if ($createLimit == 'true') {
            $page = 'Enabled';
        } else {
            $page = 'Disabled';# no action and read only
        }
        # for excel disable
        if (!empty($approved_BA) || !empty($approveHQ)) {
            $approved = 'Approved';
        }

        $disable = $approved.'_'.$page;
        
        #check forecast or buget
        $chkyr = ($year == (explode('-', $start_month))[0])? true : false;
        $this->Session->write('DISABLED', $disable);
        $this->Session->write('TRADE_DATA', $trade_data);
        $this->Session->write('DESTINATION', $destination);
        //echo '<pre>';print_r($trade_data);echo '</pre>';
        $this->set(compact('trade_data', 'table_months', 'months', 'errormsg', 'forecast_term', 'budget_BA', 'short_year', 'destination', 'approved_BA', 'trade_filling_date', 'language', 'chk', 'page', 'deadline_date', 'logistic_data', 'term_id', 'approveHQ', 'forecastMonth', 'startMonth', 'forecast_month', 'start_month', 'chkyr','result_index_no', 'disable'));
    }
    
    public function getTradingDataAndCaching($year, $term_id, $forecast_term, $headQuarterId, $ba_code, $ba_name, $loginId, $language=null)
    {
        $Common = new CommonController();
        // if($_GET['hq'] && $_GET['term']){
        $headqDeadline = $this->HeadquartersDeadlineModel->find('list', array(
            'fields' => array('head_department_id','deadline_date'),
            'conditions' => array('term_id' => $term_id, 'head_department_id' => $headQuarterId)));
    
        $hqDeadline = $headqDeadline[$headQuarterId];
        // }
        if (empty($ba_code) || empty($headQuarterId)) {
            if (empty($headQuarterId)) {
                $errorMsg = parent::getErrorMsg('SE073');
            } elseif (empty($ba_code)) {
                $errorMsg = parent::getErrorMsg('SE086');
            }
            
            $this->Flash->error($errorMsg);
            $this->redirect(array('controller' => 'TermSelection', 'action' => 'index'));
        }

        $deadline_date = ($hqDeadline == '0000-00-00 00:00:00' || empty($hqDeadline)) ? '' : date("Y/m/d", strtotime($hqDeadline));
        

        #Get forecast period(eg: 2020-05) to show actual result data till to this period
        $forecastPeriod = $this->TermModel->getForecastPeriod($term_id);
        // $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];

        $term_1st_year = substr($forecast_term, 0, 4); #get 1st year of selected_term
        $start_year	 = $term_1st_year; #get budget start year
        #get budget start month
        $start_month = $Common->getMonth($start_year, $term_id, 'start');

        $tradingData = array();
        $trade_data = array();
        $data = array();
        $trade_record = array();
        $trade_total= array();
        $accounts= array();
        $errormsg = '';
        $modelName = '';
        $index_no = '';
        $destination = '';


        $budget_BA = $this->BusinessAreaModel->find('first', array('fields' => array('ba_name_jp','ba_name_en'),
            'conditions' => array('ba_code' => $ba_code,'flag' => 1)));

        if ($language == 'eng') {
            if (!empty($budget_BA['BusinessAreaModel']['ba_name_en'])) {
                $budget_BA = $ba_code.'/'.$budget_BA['BusinessAreaModel']['ba_name_en'];
            } else {
                $budget_BA = $ba_code;
            }
        } else {
            $budget_BA = $ba_code.'/'.$budget_BA['BusinessAreaModel']['ba_name_jp'];
        }

        $months = $Common->get12Month($term_id);

        $acc_data = $this->AccountSetupModel->find('all', array(
            'fields' => array(
                'SubAccountModel.sub_acc_name_jp','AccountModel.acc_name_jp','AccountModel.account_code','AccountSetupModel.sub_acc_id'
            ),
            'conditions' => array(
                'AccountSetupModel.flag' => 1,
                'AccountSetupModel.head_dept_id' => $headQuarterId,
                'AccountSetupModel.target_year' => $year,
                'SubAccountModel.sub_acc_name_jp' => array('売上高','売上原価'),
                'NOT' => array(
                    'AccountSetupModel.account_id' => 0
                )

            ),
            'order' => array('AccountSetupModel.sub_acc_id ASC','AccountSetupModel.order ASC', 'AccountSetupModel.sub_order ASC')
        ));

        /*$active_logistic=$this->LogisticModel->find('list', array(
            'fields' => array('id','index_no', 'index_name'),
            'conditions' => array(
                'ba_code' => $ba_code,
                'target_year' => $year,
                'flag' => 1
            )
        ));
       	foreach ($data_exist_logistics as $key => $value) {
       		if(in_array('', $value, true)) {
       			$active_logistic[$key][(array_search('', $value))] = $key;
       		}
       	}*/
        if ($year == $term_1st_year) {
            $modelName = 'ExpectedModel';
        } else {
            $modelName = 'BudgetPrimeModel';
        }
        //$term_id = '10';
        #for after delete table
        
        $active_logistics = $this->LogisticModel->find('all', array(
            'fields' => array('id','index_no', 'index_name'),
            'conditions' => array(
                'ba_code' => $ba_code,
                'target_year' => $year,
                'flag' => 1
            ),
            'order' => 'LogisticModel.logistic_order ASC','LogisticModel.index_name ASC'
        ));

        $active_logistic = array();
        foreach ($active_logistics as $key => $value) {
            $id = $value['LogisticModel']['id'];
            $index_no = trim($value['LogisticModel']['index_no']);
            $index_name = trim($value['LogisticModel']['index_name']);
            
            if ($index_no == '') {
                $active_logistic[$index_name][$id] = $index_name;
            } else {
                $active_logistic[$index_name][$id] = $index_no;
            }
        }

        #Add logistic_name_list by NuNu (09112021)
        $logistic_name_list = [];
        $data_exist_logistics = [];
		while($element = current($active_logistic)) {
			$loginame = key($active_logistic);
			$logino = $element;
			array_push($logino, $loginame);

			$result_exist_count = $this->ActualResultSummaryModel->find('count',array(
				'conditions' => array('transaction_key'=>$logino, 'target_month >=' => $start_month, 'target_month <=' => $forecastPeriod, 'ba_code'=>$ba_code)
			));
			$data_exist_count = $this->$modelName->find('count',array(
				'conditions' => array('logistic_index_no'=>$logino,'target_year'=>$year, 'ba_code'=>$ba_code, 'term_id' => $term_id)
			));

			if ((($year == $term_1st_year) && $result_exist_count>0) || $data_exist_count>0) {
				$data_exist_logistics[$loginame] = $element;
			}
			
		    array_push($logistic_name_list, key($active_logistic));
		    next($active_logistic);
		
		}

        if (!empty($acc_data)) {
            $table_months = $months;

            array_splice($table_months, 6, 0, 'first_half');
            array_push($table_months, 'second_half');
            array_push($table_months, 'whole_total');
            $trade_filling_date = '';

            $gross_profit = array();
            $each_gross_prof = array();

            // $index_arr = $this->$modelName->find('all', array(
            //     'fields' => array('logistic_index_no','trade_filling_date'),
            //     'conditions' => array(
            //         'term_id' => $term_id,
            //         'target_year' => $year,
            //         'ba_code' => $ba_code,
            //         'flag' => 1,
            //         'NOT' => array(
            //             'trade_filling_date' => '',
            //             'logistic_index_no' => array('','NULL'),
            //         )
            //     ),
            //     'group' => 'logistic_index_no',
            //     'order' => 'id ASC',
            // ));

            $all_indexes = array_keys($data_exist_logistics);
            $i = 0;$result_index_no = [];
            foreach ($acc_data as $account) {
                $len = count($acc_data);
                $data = call_user_func_array('array_merge', $account);
                $sub_id = $data['sub_acc_id'];
                $acc_code = $data['account_code'];
                $sub_name = $data['sub_acc_name_jp'];
                $acc_name_only = $data['acc_name_jp'];

                $aresultData = [];
                $one = [];
                if (!empty($data_exist_logistics)) {

                    foreach ($data_exist_logistics as $index_no => $extradata) {
                        $trade_filling_date = ($filling_date != null) ? date("Y/m/d", strtotime($filling_date)) : date("Y/m/d");
                        
                        $trade_data['trade_filling_date'] = $trade_filling_date;

                        $tradingData = $this->getTradingData($modelName, $months, $term_id, $year, $ba_code, $acc_code, $index_no);

                        if (!empty($tradingData)) {
                            foreach ($tradingData as $trade) {
                                if ($acc_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                                    $destination = $trade[$modelName]['destination'];
                                    $user_input  = $trade[$modelName]['kpi_unit'];
                                    if ($index_no == '取引無し' && $destination == '8000' && $user_input == '') {
                                        $user_input = null;
                                    }
                                } else {
                                    $destination = null;
                                    $user_input = null;
                                }
                                
                                if (!empty($user_input)) {
                                    $acc_name = $acc_name_only.','.$user_input.','.$destination;
                                    $one[$index_no.'/'.$acc_name_only.'/'.$destination] = $user_input;
                                } else {
                                    $acc_name = $acc_name_only;
                                }
                                foreach ($table_months as $each_month) {
                                    $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$each_month] += $trade[0][$each_month];
                                    $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$each_month] += $trade[0][$each_month];
                                    $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$each_month] += $trade[0][$each_month];
                                    $trade_data['total'][$sub_name]['total']['amount'][$each_month] += $trade[0][$each_month];
                                    $each_gross_prof[$index_no][$each_month] += $trade[0][$each_month];
                                    $gross_profit[$each_month] += $trade[0][$each_month];
                                }
                                $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['code'] = $acc_code;
                                $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['sub_id'] = $sub_id;

                                $trade_data['record'][$index_no]['trading'][$sub_name]['total']['sub_id'] = $sub_id;

                                $trade_data['total'][$sub_name]['data'][$acc_name_only]['code'] = $acc_code;
                                $trade_data['total'][$sub_name]['data'][$acc_name_only]['sub_id'] = $sub_id;

                                $trade_data['total'][$sub_name]['total']['sub_id'] = $sub_id;
                            }
                        } else {
                            $destination = $tradingData[$modelName]['destination'];
                            //$user_input  = $trade[$modelName]['kpi_unit'];
                            $user_input  = '';
                            if ($index_no == '取引無し' && $acc_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                                $destination = '8000';
                            }
                            if (!empty($user_input)) {
                                $acc_name = $acc_name_only.','.$user_input.','.$destination;
                                $one[$index_no.'/'.$acc_name_only.'/'.$destination] = $user_input;
                            } else {
                                $acc_name = $acc_name_only;
                            }
                            foreach ($table_months as $each_month) {
                                $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$each_month] += 0;
                                $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$each_month] += 0;
                                $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$each_month] += 0;
                                $trade_data['total'][$sub_name]['total']['amount'][$each_month] += 0;

                                $each_gross_prof[$index_no][$each_month] += 0;
                                $gross_profit[$each_month] += 0;
                            }
                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['code'] = $acc_code;
                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['sub_id'] = $sub_id;

                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['sub_id'] = $sub_id;

                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['code'] = $acc_code;
                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['sub_id'] = $sub_id;

                            $trade_data['total'][$sub_name]['total']['sub_id'] = $sub_id;
                        }
                        if ($year == $term_1st_year) {
                            if ($index_no == '取引無し' && $acc_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                                #get actual result for sub table
                                $this->ActualResultSummaryModel->virtualFields['total_amount'] = 'SUM(amount)/1000';
                                $transaction_key = $active_logistic[$index_no];

                                $aresultDataByIndexes = $this->ActualResultSummaryModel->find('list', array(
                                    'fields' => array('target_month','total_amount','destination_code'),
                                    'conditions' => array(
                                        'ba_code' => $ba_code,
                                        'account_code' => $acc_code,
                                        'target_month >=' => $start_month,
                                        'target_month <=' => $forecastPeriod,
                                        'transaction_key' => $transaction_key,
                                        'destination_code' => '8000'
                                    ),
                                    'group' => 'target_month,destination_code',
                                    'order' => 'destination_code,target_month',
                                ));
                                $one[$index_no.'/'.$acc_name_only.'/8000'] = '';
                            } else {
                                #get actual result for sub table
                                $this->ActualResultSummaryModel->virtualFields['total_amount'] = 'SUM(amount)/1000';
                                $transaction_key = $active_logistic[$index_no];

                                $aresultDataByIndexes = $this->ActualResultSummaryModel->find('list', array(
                                    'fields' => array('target_month','total_amount','destination_code'),
                                    'conditions' => array(
                                        'ba_code' => $ba_code,
                                        'account_code' => $acc_code,
                                        'target_month >=' => $start_month,
                                        'target_month <=' => $forecastPeriod,
                                        'transaction_key' => $transaction_key
                                    ),
                                    'group' => 'target_month,destination_code',
                                    'order' => 'destination_code,target_month',
                                ));
                            }
                           
                            if (empty($aresultDataByIndexes)) {
                                $aresultDataByIndexes[0] = array();
                            }else {
                            	$result_index_no[$index_no] = $index_no;
                            }
                            
                            foreach ($aresultDataByIndexes as $destination => $aresultDataByIndex) {
                                if ($destination == 0) {
                                    $destination = null;
                                }

                                $user_input = $one[$index_no.'/'.$acc_name_only.'/'.$destination];

                                if (!empty($user_input)) {
                                    $acc_name = $acc_name_only.','.$user_input.','.$destination;
                                } else {
                                    $acc_name = $acc_name_only;
                                }
                                #Set result amount into total table, PanEiPhyo(20210810)
                                if ((!empty($forecastPeriod) || $forecastPeriod!='') && $year == $start_year) {
                                    $forecastMonth = date('n', strtotime($forecastPeriod));
                                    $startMonth = date('n', strtotime($start_month));

                                    #set virtual field
                                    $this->ActualResultSummaryModel->virtualFields['result_total'] = 'SUM(amount)/1000';
                                    
                                    #get result data of result lock period
                                    $aresultData = $this->ActualResultSummaryModel->find('list', array(
                                        'fields' => array('target_month','result_total'),
                                        'conditions' => array(
                                            'head_dept_id' => $headQuarterId,
                                            'ba_code' => $ba_code,
                                            'account_code' => $acc_code,
                                            'target_month >=' => $start_month,
                                            'target_month <=' => $forecastPeriod //$end_month edit by NNL
                                        ),
                                        'group' => 'target_month',
                                        'order' => 'target_month ASC',
                                    ));

                                    
                                    $eachMonth = $start_month;
                                    $month_cnt = 0;
                                    #tbl_expected htl hmr ma save ya thy tae destination nae result data twy yout lr loz
                                    if (empty($trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination])) {
                                        foreach ($table_months as $each_month) {
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$each_month] += 0;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$each_month] += 0;
                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$each_month] += 0;
                                            $trade_data['total'][$sub_name]['total']['amount'][$each_month] += 0;

                                            $each_gross_prof[$index_no][$each_month] += 0;
                                            $gross_profit[$each_month] += 0;
                                        }
                                        $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['code'] = $acc_code;
			                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['sub_id'] = $sub_id;

			                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['sub_id'] = $sub_id;

			                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['code'] = $acc_code;
			                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['sub_id'] = $sub_id;

			                            $trade_data['total'][$sub_name]['total']['sub_id'] = $sub_id;
                                    }
                                    
                                    #loop through start_month to last result_lock_period
                                    while ($eachMonth <= $forecastPeriod) {
                                        #set result amount
                                        $monthlyResult = (!empty($aresultData[$eachMonth])) ? $aresultData[$eachMonth] : 0;
                                        
                                        #get difference of forecast and result amount
                                        $resultDiff = $monthlyResult - $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$months[$month_cnt]];

                                        $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$months[$month_cnt]] += $resultDiff;
                                        $trade_data['total'][$sub_name]['total']['amount'][$months[$month_cnt]] += $resultDiff;
                                        $gross_profit[$months[$month_cnt]] += $resultDiff;

                                        #show actaual data for sub table
                                        $monthlyResultByIndex = (!empty($aresultDataByIndex[$eachMonth])) ? $aresultDataByIndex[$eachMonth] : 0;
                                        
                                        $resultByIndexDiff = $monthlyResultByIndex - $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$months[$month_cnt]];

                                        $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$months[$month_cnt]] += $resultByIndexDiff;
                                        $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$months[$month_cnt]] += $resultByIndexDiff;
                                        $each_gross_prof[$index_no][$months[$month_cnt]] += $resultByIndexDiff;
                                        #add difference to total fields
                                        if ($month_cnt < 6) {
                                            #acc total
                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['first_half'] += $resultDiff;
                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['whole_total'] += $resultDiff;
                                            #sub acc total
                                            $trade_data['total'][$sub_name]['total']['amount']['first_half'] += $resultDiff;
                                            $trade_data['total'][$sub_name]['total']['amount']['whole_total'] += $resultDiff;
                                            #prepare for subacc=3
                                            $gross_profit['first_half'] += $resultDiff;
                                            $gross_profit['whole_total'] += $resultDiff;

                                            # show actaual data for sub table
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['first_half'] += $resultByIndexDiff;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['whole_total'] += $resultByIndexDiff;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['first_half'] += $resultByIndexDiff;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['whole_total'] += $resultByIndexDiff;
                                            $each_gross_prof[$index_no]['first_half'] += $resultByIndexDiff;
                                            $each_gross_prof[$index_no]['whole_total'] += $resultByIndexDiff;
                                        } else {
                                            #acc total
                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['second_half'] += $resultDiff;
                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['whole_total'] += $resultDiff;
                                            #sub acc total
                                            $trade_data['total'][$sub_name]['total']['amount']['second_half'] += $resultDiff;
                                            $trade_data['total'][$sub_name]['total']['amount']['whole_total'] += $resultDiff;
                                            #prepare for subacc=3
                                            $gross_profit['second_half'] += $resultDiff;
                                            $gross_profit['whole_total'] += $resultDiff;

                                            # show actaual data for sub table
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['second_half'] += $resultByIndexDiff;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['whole_total'] += $resultByIndexDiff;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['second_half'] += $resultByIndexDiff;
                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['whole_total'] += $resultByIndexDiff;
                                            $each_gross_prof[$index_no]['second_half'] += $resultByIndexDiff;
                                            $each_gross_prof[$index_no]['whole_total'] += $resultByIndexDiff;
                                        }
                                        
                                        #increase month & month count
                                        $eachMonth =  date("Y-m", strtotime($eachMonth. "last day of + 1 Month"));
                                        $month_cnt++;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $acc_name = $data['acc_name_jp'];
                    $trade_filling_date = date("Y/m/d");
                    $index_no = '';
                    $no_amt_logi = [];
                    if(!empty($active_logistic)) {
	                    foreach ($active_logistic as $index_no => $transaction_key) {
	                        #get actual result for sub table
	                        $this->ActualResultSummaryModel->virtualFields['total_amount'] = 'SUM(amount)/1000';
	                        $aresultDataByIndexes = $this->ActualResultSummaryModel->find('list', array(
	                            'fields' => array('target_month','total_amount','destination_code'),
	                            'conditions' => array(
	                                'ba_code' => $ba_code,
	                                'account_code' => $acc_code,
	                                'target_month >=' => $start_month,
	                                'target_month <=' => $forecastPeriod,
	                                'transaction_key' => $transaction_key
	                            ),
	                            'group' => 'target_month,destination_code',
	                            'order' => 'destination_code,target_month',
	                        ));
	                        $destination = 0;
	                        if ($year == $term_1st_year) {
	                            if (empty($aresultDataByIndexes)) {
	                                $aresultDataByIndexes[0] = array();
	                            }else {
	                            	$result_index_no[$index_no] = $index_no;
	                            }
	                            foreach ($aresultDataByIndexes as $d => $aresultDataByIndex) {
	                                if ($acc_code == '6108000000' && ($d == '' || $d == 0)) {
	                                    $destination = '1';
	                                } else {
	                                    $destination = $d;
	                                }
	                                
	                                foreach ($table_months as $each_month) {
	                                    $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$each_month] += 0;
	                                    $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$each_month] += 0;

	                                    $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$each_month] += 0;
	                                    $trade_data['total'][$sub_name]['total']['amount'][$each_month] += 0;

	                                    $each_gross_prof[$index_no][$each_month] += 0;
	                                    $gross_profit[$each_month] += 0;
	                                }
	                                $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['code'] = $acc_code;
	                                $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['sub_id'] = $sub_id;

	                                $trade_data['record'][$index_no]['trading'][$sub_name]['total']['sub_id'] = $sub_id;

	                                $trade_data['total'][$sub_name]['data'][$acc_name_only]['code'] = $acc_code;
	                                $trade_data['total'][$sub_name]['data'][$acc_name_only]['sub_id'] = $sub_id;

	                                $trade_data['total'][$sub_name]['total']['sub_id'] = $sub_id;

	                                #Set result amount into total table, PanEiPhyo(20210810)
	                                if ((!empty($forecastPeriod) || $forecastPeriod!='') && $year == $start_year) {
	                                    $forecastMonth = date('n', strtotime($forecastPeriod));
	                                    $startMonth = date('n', strtotime($start_month));

	                                    #set virtual field
	                                    $this->ActualResultSummaryModel->virtualFields['result_total'] = 'SUM(amount)/1000';
	                                    
	                                    #get result data of result lock period
	                                    $aresultData = $this->ActualResultSummaryModel->find('list', array(
	                                        'fields' => array('target_month','result_total'),
	                                        'conditions' => array(
	                                            'head_dept_id' => $headQuarterId,
	                                            'ba_code' => $ba_code,
	                                            'account_code' => $acc_code,
	                                            'target_month >=' => $start_month,
	                                            'target_month <=' => $forecastPeriod //$end_month edit by NNL
	                                        ),
	                                        'group' => 'target_month',
	                                        'order' => 'target_month ASC',
	                                    ));
	                                    
	                                    $eachMonth = $start_month;
	                                    $month_cnt = 0;

	                                    #loop through start_month to last result_lock_period
	                                    while ($eachMonth <= $forecastPeriod) {
	                                        #set result amount
	                                        $monthlyResult = (!empty($aresultData[$eachMonth])) ? $aresultData[$eachMonth] : 0;
	                                        
	                                        #get difference of forecast and result amount
	                                        $resultDiff = $monthlyResult - $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$months[$month_cnt]];

	                                        $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$months[$month_cnt]] += $resultDiff;
	                                        $trade_data['total'][$sub_name]['total']['amount'][$months[$month_cnt]] += $resultDiff;
	                                        $gross_profit[$months[$month_cnt]] += $resultDiff;

	                                        # show actaual data for sub table
	                                        $monthlyResultByIndex = (!empty($aresultDataByIndex[$eachMonth])) ? $aresultDataByIndex[$eachMonth] : 0;
	                                        
	                                        $resultByIndexDiff = $monthlyResultByIndex - $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$months[$month_cnt]];

	                                        $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$months[$month_cnt]] += $resultByIndexDiff;
	                                        $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$months[$month_cnt]] += $resultByIndexDiff;
	                                        $each_gross_prof[$index_no][$months[$month_cnt]] += $resultByIndexDiff;
	                                        #add difference to total fields
	                                        if ($month_cnt < 6) {
	                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['first_half'] += $resultDiff;
	                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['whole_total'] += $resultDiff;
	                                            $trade_data['total'][$sub_name]['total']['amount']['first_half'] += $resultDiff;
	                                            $trade_data['total'][$sub_name]['total']['amount']['whole_total'] += $resultDiff;
	                                            $gross_profit['first_half'] += $resultDiff;
	                                            $gross_profit['whole_total'] += $resultDiff;

	                                            # show actaual data for sub table
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['first_half'] += $resultByIndexDiff;
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['whole_total'] += $resultByIndexDiff;
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['first_half'] += $resultByIndexDiff;
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['whole_total'] += $resultByIndexDiff;
	                                            $each_gross_prof[$index_no]['first_half'] += $resultByIndexDiff;
	                                            $each_gross_prof[$index_no]['whole_total'] += $resultByIndexDiff;
	                                        } else {
	                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['second_half'] += $resultDiff;
	                                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount']['whole_total'] += $resultDiff;
	                                            $trade_data['total'][$sub_name]['total']['amount']['second_half'] += $resultDiff;
	                                            $trade_data['total'][$sub_name]['total']['amount']['whole_total'] += $resultDiff;
	                                            $gross_profit['second_half'] += $resultDiff;
	                                            $gross_profit['whole_total'] += $resultDiff;

	                                            # show actaual data for sub table
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['second_half'] += $resultByIndexDiff;
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination]['whole_total'] += $resultByIndexDiff;
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['second_half'] += $resultByIndexDiff;
	                                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount']['whole_total'] += $resultByIndexDiff;
	                                            $each_gross_prof[$index_no]['second_half'] += $resultByIndexDiff;
	                                            $each_gross_prof[$index_no]['whole_total'] += $resultByIndexDiff;
	                                        }

	                                        #increase month & month count
	                                        $eachMonth =  date("Y-m", strtotime($eachMonth. "last day of + 1 Month"));
	                                        $month_cnt++;
	                                    }
	                                }
	                            }
	                        } else {
	                            $no_amt_logi[$index_no] = $index_no;
	                            if ($year != $term_1st_year) {
	                                $index_no = '';
	                            }
	                            foreach ($table_months as $each_month) {
	                                $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$each_month] += 0;
	                                $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$each_month] += 0;
	                                $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$each_month] += 0;
	                                $trade_data['total'][$sub_name]['total']['amount'][$each_month] += 0;

	                                $each_gross_prof[$index_no][$each_month] += 0;
	                                $gross_profit[$each_month] += 0;
	                            }
	                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['code'] = $acc_code;
	                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['sub_id'] = $sub_id;

	                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['sub_id'] = $sub_id;

	                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['code'] = $acc_code;
	                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['sub_id'] = $sub_id;
                                $trade_data['total'][$sub_name]['total']['sub_id'] = $sub_id;
	                        }
	                    }
	                }else {
            			foreach ($table_months as $each_month) {
                            $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['amount'][$destination][$each_month] += 0;
                            $trade_data['record'][$index_no]['trading'][$sub_name]['total']['amount'][$each_month] += 0;
                            $trade_data['total'][$sub_name]['data'][$acc_name_only]['amount'][$each_month] += 0;
                            $trade_data['total'][$sub_name]['total']['amount'][$each_month] += 0;

                            $each_gross_prof[$index_no][$each_month] += 0;
                            $gross_profit[$each_month] += 0;
                        }
                        $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['code'] = $acc_code;
                        $trade_data['record'][$index_no]['trading'][$sub_name]['data'][$acc_name]['sub_id'] = $sub_id;

                        $trade_data['record'][$index_no]['trading'][$sub_name]['total']['sub_id'] = $sub_id;

                        $trade_data['total'][$sub_name]['data'][$acc_name_only]['code'] = $acc_code;
                        $trade_data['total'][$sub_name]['data'][$acc_name_only]['sub_id'] = $sub_id;
                        $trade_data['total'][$sub_name]['total']['sub_id'] = $sub_id;
	                }
                }
                
                if ($i == $len -1) { #last iteration
                    
                    foreach ($gross_profit as $month => $value) {
                        $trade_data['total']['売上総利益']['total']['amount'][$month] += $value;
                    }

                    foreach ($each_gross_prof as $index => $index_amount) {
                        foreach ($index_amount as $each_month => $each_value) {
                            $trade_data['record'][$index]['trading']['売上総利益']['total']['amount'][$each_month] += $each_value;
                        }
                    }
                }

                $i++;
            }
            $tem_trade_save = $trade_data['record'];
            $get_first_arr[''] = array_shift($tem_trade_save);
            
            if (!empty($data_exist_logistics)) {
                $acc_code = '0000000000';
                $sub_acc_id = 0;

                //foreach ($index_arr as $index_data) {
                foreach ($data_exist_logistics as $index_no => $extradata) {
                    //$index_no = $index_data[$modelName]['logistic_index_no'];
                    $tradingData = $this->getTradingData($modelName, $months, $term_id, $year, $ba_code, $acc_code, $index_no);
                    if (!empty($tradingData)) {
                        $kpi_unit = $tradingData[0][$modelName]['kpi_unit'];
                    } else {
                        $kpi_unit = '';
                    }
                    foreach ($table_months as $each_month) {
                        $trade_data['record'][$index_no]['kpi'][$kpi_unit]['amount'][$each_month] = $tradingData[0][0][$each_month];
                    }
                    $trade_data['record'][$index_no]['kpi'][$kpi_unit]['code'] = $acc_code;
                    $trade_data['record'][$index_no]['kpi'][$kpi_unit]['sub_id'] = $sub_acc_id;
                }
            } else {
                $kpi_unit = '';
                $index_no = '';
                $account_code = '0000000000';
                $sub_acc_id = '0';
                foreach ($data_exist_logistics as $index_no => $transaction_key) {
                    foreach ($table_months as $each_month) {
                        $trade_data['record'][$index_no]['kpi'][$kpi_unit]['amount'][$each_month] = 0;
                    }
                    $trade_data['record'][$index_no]['kpi'][$kpi_unit]['code'] = $account_code;
                    $trade_data['record'][$index_no]['kpi'][$kpi_unit]['sub_id'] = $sub_acc_id;
                    // unset($trade_data['record'][$no_amt_logi[$index_no]]);
                }
            }
            if (empty($trade_data['record'])) {
                $trade_data['record'] = $get_first_arr;
            }
        } else {
            $errormsg = __('表示するアカウントはありません。');
        }
        $today = date("Y-m-d");
        $destination = [];
        $active_BA = $this->BusinessAreaModel->find('all', array(
            'fields' => array('id','ba_code','ba_name_en','ba_name_jp'),
            'conditions' => array(
                'flag' => 1,
                'dept_id !=' => '',
                'head_dept_id !=' => '',
                'from_date <=' => $today,
                'to_date >=' => $today
            )
        ));

        foreach ($active_BA  as $ba) {
            if ($language == 'eng') {
                if (!empty($ba['BusinessAreaModel']['ba_name_en'])) {
                    $destination[$ba['BusinessAreaModel']['ba_code']] = $ba['BusinessAreaModel']['ba_code'].'/'.$ba['BusinessAreaModel']['ba_name_en'];
                } else {
                    $destination[$ba['BusinessAreaModel']['ba_code']] = $ba['BusinessAreaModel']['ba_code'];
                }
            } else {
                $destination[$ba['BusinessAreaModel']['ba_code']] = $ba['BusinessAreaModel']['ba_code'].'/'.$ba['BusinessAreaModel']['ba_name_jp'];
            }
        }


        $approved_BA =  $this->BudgetLogModel->find('first', array(
            'conditions' => array(
                'term_id' => $term_id,
                'head_dept_id' => $headQuarterId,
                'ba_code' => $ba_code,
                'flag' => '2'
            )
        ));
        $approveHQ = $this->BudgetLogModel->find('first', array(
                                            'conditions' => array(
                                                'term_id' => $term_id,
                                                'head_dept_id' => $headQuarterId,
                                                'ba_code' => '0',
                                                'dept_id' => '0',
                                                'flag' => '2'
                                            )
                                        ));

        $logistic_data = array_keys($active_logistic);

        $tr_data = array(
            'trade_data' => $trade_data,
            'table_months' => $table_months,
            'months' => $months,
            'errormsg' => $errormsg,
            'forecast_term' => $forecast_term,
            'budget_BA' => $budget_BA,
            'destination' => $destination,
            'trade_filling_date' => $trade_filling_date,
            'deadline_date' => $deadline_date,
            'logistic_data' => $logistic_data,
            'approved_BA' => $approved_BA,
            'approveHQ' => $approveHQ,
            'forecastMonth' => $forecastMonth,
            'startMonth' => $startMonth,
            'forecast_month' => $forecastPeriod,
            'start_month' => $start_month,
            'result_index_no' => $result_index_no
        );

        $cache_name = 'trading_plan_'.$term_id.'_'.$year.'_'.$ba_code.'_'.$loginId;
        Cache::write($cache_name, $tr_data);

        return $tr_data;
    }

    /**
     * add method
     *
     * @author PanEiPhyo (202008)
     * @param none
     * @return void
     */
    public function saveTradingData()
    {
        $Common = new CommonController;
        if ($this->request->is('post')) {
            if ($this->Session->check('TERM_ID')) {
                $term_id = $this->Session->read('TERM_ID');
            }
            if ($this->Session->check('TERM_NAME')) {
                $budget_term = $this->Session->read('TERM_NAME');
            }
            if ($this->Session->check('LOGIN_ID')) {
                $loginId = $this->Session->read('LOGIN_ID');
            }
            if ($this->Session->check('HEAD_DEPT_ID')) {
                $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
            }
            if ($this->Session->check('HEADQUARTER_DEADLINE')) {
                $hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
            }
            if ($this->Session->check('HEAD_DEPT_NAME')) {
                $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');
            }
            $save_data = [];
            $update_data = [];
            $modelName = '';

            $requestData = $this->request->data;
            
            $diff_ba = explode("/", $this->request->data['business_code']);
            $ba_code = $diff_ba[0];
            $ba_name = $diff_ba[1];
            $date = date_create($this->request->data['filling_date']);

            $trade_filling_date = date_format($date, "Y-m-d H:i:s");
            
            $year = $this->request->data['year'];
            $start_month = $Common->getMonth($year, $term_id, 'start');
            $end_month = $Common->getMonth($year, $term_id, 'end');
            $dead_date = $this->TermModel->find('first', array(
                'fields' => array('forecast_period'),
                'conditions' => array(
                    'id' => $term_id
                )
            ));
            $deadline_date = $hqDeadline;
            $forecast_period = $dead_date['TermModel']['forecast_period'];
            $year_month = $this->dateRange($start_month, $forecast_period);
            $months = $Common->get12Month($term_id);

            $term_1st_year = substr($budget_term, 0, 4);

            $sumModelName = '';
            $sumFunctName = '';

            if ($year == $term_1st_year) {
                $modelName = 'ExpectedModel';
                $type = 'forecast';
                $sumModelName = 'ForecastSummaryModel';
            } else {
                $modelName = 'BudgetPrimeModel';
                $type = 'budget';
                $sumModelName = 'BudgetSummaryModel';
            }
            $date = new DateTime("now", new DateTimeZone(Setting::TIMEZONE));
            $updated_date = $date->format('Y-m-d h:i:s');
            $subid_del = [];
            $logi = [];
            foreach ($requestData['hid_trade'] as $trade) {
                foreach ($trade['data'] as $sub_acc_id => $account_datas) {
                    $subid_del[] = $sub_acc_id;
                    if ($sub_acc_id != 0) {
                        $sub_acc_name = $this->getSubAccName($sub_acc_id);
                    }
                    foreach ($account_datas as $account_code => $months_amt) {
                        $tmp['term_id'] 	= $term_id;
                        $tmp['target_year'] = $year;
                        $tmp['ba_code'] = $ba_code;
                        $tmp['sub_acc_id'] 	= $sub_acc_id;
                        $tmp['logistic_index_no'] = $trade['logistic_index'];
                        $tmp['account_code'] = $account_code;
                        $tmp['trade_filling_date'] 	= $trade_filling_date;
                        $tmp['created_id'] 		= $loginId;
                        $tmp['updated_id'] 		= $loginId;
                        $tmp['flag'] = 1;
                        # total count of tables to show success msg
                        if (!in_array($trade['logistic_index'], $logi)) {
                            array_push($logi, $trade['logistic_index']);
                        }
                        if ($account_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                            foreach ($months_amt as $mkey => $mvalue) {
                                $destination = ($mvalue['destination'] == 0 || $mvalue['destination'] == 1)? '' : $mvalue['destination'];
                                $user_input  = $mvalue['user_input'];
                                $diff_ba = explode("/", $destination);
                                $tmp['destination'] = $diff_ba[0];
                                $tmp['kpi_unit'] = $user_input;
                                $month_amt = array_shift($mvalue);
                                $months_amts = array_values(preg_replace("/[^-0-9\.]/", "", $mvalue));
    
                                $tmp['month_1_amt'] 	= $months_amts[1]*1000;
                                $tmp['month_2_amt'] 	= $months_amts[2]*1000;
                                $tmp['month_3_amt'] 	= $months_amts[3]*1000;
                                $tmp['month_4_amt'] 	= $months_amts[4]*1000;
                                $tmp['month_5_amt'] 	= $months_amts[5]*1000;
                                $tmp['month_6_amt'] 	= $months_amts[6]*1000;
                                $tmp['month_7_amt'] 	= $months_amts[7]*1000;
                                $tmp['month_8_amt'] 	= $months_amts[8]*1000;
                                $tmp['month_9_amt'] 	= $months_amts[9]*1000;
                                $tmp['month_10_amt'] 	= $months_amts[10]*1000;
                                $tmp['month_11_amt'] 	= $months_amts[11]*1000;
                                $tmp['month_12_amt'] 	= $months_amts[12]*1000;
                                
                                # assign to newly save data
                                $save_data[] = array_merge($tmp);
                                
                                # to compare with actual result amount
                                foreach ($mvalue as $sumkey => $sumvalue) {
                                    $value = preg_replace("/[^-0-9\.]/", "", $sumvalue);
                                    $tot[$sub_acc_id][$sumkey] += $value;
                                    # save to tbl_expected_budget_diff_acc
                                    $expected[$sub_acc_name] += $value*1000;
                                }
                            }
                        } else {
                            if ($account_code == '0000000000') {
                                $tmp['kpi_unit'] = $trade['kpi_unit'];
                                $tmp['destination'] = null;
                            } else {
                                $tmp['kpi_unit'] = null;
                            }
                            
                            $months_amts = array_values(preg_replace("/[^-0-9\.]/", "", $months_amt));
                            $tmp['month_1_amt'] 	= $months_amts[0]*1000;
                            $tmp['month_2_amt'] 	= $months_amts[1]*1000;
                            $tmp['month_3_amt'] 	= $months_amts[2]*1000;
                            $tmp['month_4_amt'] 	= $months_amts[3]*1000;
                            $tmp['month_5_amt'] 	= $months_amts[4]*1000;
                            $tmp['month_6_amt'] 	= $months_amts[5]*1000;
                            $tmp['month_7_amt'] 	= $months_amts[6]*1000;
                            $tmp['month_8_amt'] 	= $months_amts[7]*1000;
                            $tmp['month_9_amt'] 	= $months_amts[8]*1000;
                            $tmp['month_10_amt'] 	= $months_amts[9]*1000;
                            $tmp['month_11_amt'] 	= $months_amts[10]*1000;
                            $tmp['month_12_amt'] 	= $months_amts[11]*1000;
                            
                            # assign to newly save data
                            $save_data[] = $tmp;
                            
                            # to compare with actual result amount
                            foreach ($months_amt as $sumkey => $sumvalue) {
                                $value = preg_replace("/[^-0-9\.]/", "", $sumvalue);
                                $tot[$sub_acc_id][$sumkey] += $value;
                                # save to tbl_expected_budget_diff_acc
                                if ($account_code != '0000000000') {
                                    $expected[$sub_acc_name] += $value*1000;
                                }
                            }
                        }
                    }
                }
            }
            #hide code of compare case that is no compare case bcos result data show in sub table)
            /*# actual result amount
            $actual = $this->getActualSummaryAmount($subid_del, $months, $year_month, $ba_code);

            # compare with actual and trading
            $compareFlg = $this->compareActualAndTrading($ba_code, $head_dept_id, $actual, $tot);

            # prepare to save tbl_budget_log
            if($compareFlg[0]) { # actual != trading
                $log['term_id'] = $term_id;
                $log['head_dept_id'] = $head_dept_id;
                $log['ba_code'] = $ba_code;
                $log['dept_id'] = $compareFlg[1];
                $log['flag'] = 3;
                $log['created_id'] = $loginId;
                $log['updated_id'] = $loginId;
                $log['created_date'] = $updated_date;
                $log['updated_date'] = $updated_date;
            }
            # prepare to delete tbl_budget_log(old data)
            $del_log['term_id'] = $term_id;
            $del_log['head_dept_id'] = $head_dept_id;
            $del_log['ba_code'] = $ba_code;
            $del_log['dept_id'] = $compareFlg[1];
            $del_log['flag'] = 3;*/
            $BPData = new BudgetPlanController();
            if (!empty($save_data)) {
                $attachDB = $this->$modelName->getDataSource();
                /* $BudgetDB = $this->BudgetLogModel->getDataSource();*/

                try {
                    $attachDB->begin();
                    // $BudgetDB->begin();
                    # Delete old data
                    $this->$modelName->deleteAll(array(
                        'term_id' => $term_id,
                        'target_year' => $year,
                        'ba_code' => $ba_code,
                        'sub_acc_id' => $subid_del
                    ), false);

                    # Save new data
                    if (!empty($save_data)) {
                        $this->$modelName->saveAll($save_data);
                    }
                    # Save to tbl_budget_log
                    /*if(!empty($log)) {
                        $this->BudgetLogModel->deleteAll(array($del_log),false);
                        $this->BudgetLogModel->saveAll($log);
                    }else {
                        $this->BudgetLogModel->deleteAll(array($del_log),false);
                    }*/
                    $filling_date = '';
                    // $calculate_tax = $MPData->fixTaxCalculations($term_id,$filling_date,$budget_term,$head_dept_id,$loginId,$trade_filling_date,$ba_code,$year);
                    $manual_tax_ba = Setting::BA_BUDGET_TAX;
                    if (!in_array($ba_code, $manual_tax_ba)) {
                        // $calculate_tax = $MPData->fixTaxCalculations($term_id, $filling_date, $budget_term, $head_dept_id, $loginId, $trade_filling_date, $ba_code, $year);
                        $calculate_tax = $BPData->updateTaxAmount($term_id, $budget_term, $head_dept_id, $head_dept_name, $ba_code, $ba_name, $year, $loginId, $modelName);
                        
                    } else {
                        $calculate_tax = true;
                    }
                    if ($calculate_tax) {
                        $attachDB->commit();
                        // $BudgetDB->commit();
                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                        $successMsg = parent::getSuccessMsg('SS026', count($logi));
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                    } else {
                        $this->Session->delete('CHECKACTUAL');
                        $attachDB->rollback();
                        // $BudgetDB->rollback();
                        $errorMsg = parent::getErrorMsg('SE003');
                        $this->Flash->set($errorMsg, array("key"=>"UserError"));

                        CakeLog::write('debug', 'Budget data cannot be saved!. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }

                    $this->$sumModelName->updateSummaryData($ba_code,$term_id,$year);

                } catch (Exception $e) {
                    $this->Session->delete('CHECKACTUAL');
                    $attachDB->rollback();
                    // $BudgetDB->rollback();
                    $errorMsg = parent::getErrorMsg('SE003');
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    CakeLog::write('debug', 'data cannot be saved!. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
            } else {
                $this->Session->delete('CHECKACTUAL');
                $errorMsg = parent::getErrorMsg('SE017', 'Save');
                $this->Flash->set($errorMsg, array("key"=>"UserError"));
            }

            $this->redirect(array('controller'=>'TradingPlan/?year='.$requestData['year'],'action'=>'index'));
        }
    }
    
    /**
     * delete method
     *
     * @author Name (YYYYMMDD)
     * @throws NotFoundException
     * @param string $id
     * @return void
     */

    public function deleteTradingData()
    {
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }

        $logistic_index_no = $this->request->data('logistic_index_no');
        $year = $this->request->data['year'];

        $term_1st_year = substr($budget_term, 0, 4);

        if ($year == $term_1st_year) {
            $modelName = 'ExpectedModel';
        } else {
            $modelName = 'BudgetPrimeModel';
        }


        $logNo = $this->$modelName->find('all', array(
                'conditions' => array('logistic_index_no' => $logistic_index_no),
                'fields' => array('flag','id')));

        foreach ($logNo as $key => $value) {
            $trade_id = $value[$modelName]['id'];

            if ($value[$modelName]['flag'] == 1) {
                $delete_data = array('id' => $trade_id, 'flag' => 0, 'updated_id' => $loginId, 'updated_date' => date("Y-m-d H:i:s"));
                $this->$modelName->save($delete_data);

                $success_del = true;
            } else {
                $success_del = flase;
            }
        }

        if ($success_del) {
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
        } else {
            $errorMsg = parent::getErrorMsg('SE050');
            $this->Flash->set($errorMsg, array("key" =>"UserError"));
        }

        $this->redirect(array('controller'=>'TradingPlan/?year='.$year,'action'=>'index'));
    }

    public function downloadExcelData()
    {
        $this->layout = null;
        $this->autoLayout = false;
        $budget_term = $this->Session->read('TERM_NAME');

        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        /*if ($this->Session->check('DISABLED')){
            $disabled = $this->Session->read('DISABLED');
            $disable = explode('_', $disabled);
        }*/
        
        #get year from request
        $year = $this->request->data['year'];
        $ba_code = $this->request->data['ba'];
        $term_id = $this->request->data['term_id'];
        $disable = explode('_', $this->request->data['disabled_excel_row']);
        $PHPExcel = $this->PhpExcel;
        $save_into_tmp = false;
        $tmpFileName = 'TradingPlan';
       
        $this->DownloadExcel($term_id, $budget_term, $ba_code, $year, $loginId, $tmpFileName, $PHPExcel, $save_into_tmp, $disable);

        $this->redirect(array('controller'=>'TradingPlan/?year='.$year.'&'.$requestData['formType'],'action'=>'index'));
    }

    public function DownloadExcel($term_id, $budget_term, $ba_code, $year, $loginId, $file_name, $PHPExcel, $save_into_tmp, $disable=null, $hq_name=null)
    {
        $title = '';

        $Common = new CommonController;
        $Months = $Common->get12Month($term_id);

        if ($disable[0] == 'Approved' || $disable[1] == 'Disabled') {
            $disable_color = 'F9F9F9';
        } else {
            $disable_color = 'D5F4FF';
        }
        
        #Get data from cache
        $cache_name = 'trading_plan_'.$term_id.'_'.$year.'_'.(explode('/', $ba_code))[0].'_'.$loginId;
        $cache_dta = Cache::read($cache_name);
        $trade_data = $cache_dta['trade_data'];
        #to add empty logistic tables
        // $destination = $trade_data['destination'];
        $destination = $this->getDestination();
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
        $B1Style = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '808080'),
                ),
            ),
        );
        $B2Style = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '808080'),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                    'color' => array('argb' => '808080'),
                ),
            ),
        );

        $B3Style = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                    'color' => array('argb' => '808080'),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                    'color' => array('argb' => '808080'),
                ),
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => '808080'),
                ),
            ),
        );

        $B4Style = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                    'color' => array('argb' => '808080'),
                ),
            ),
        );
        $alignleft = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $objWorkSheet->getActiveSheet()->getColumnDimension('A')->setWidth(3);
        $objWorkSheet->getActiveSheet()->getColumnDimension('B')->setWidth(3);
        $objWorkSheet->getActiveSheet()->getColumnDimension('C')->setWidth(3);
        $objWorkSheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objWorkSheet->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('J')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('K')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('L')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('M')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('N')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('O')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('P')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('R')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('S')->setWidth(12);
        $objWorkSheet->getActiveSheet()->getColumnDimension('T')->setWidth(12);

        $objWorkSheet->getActiveSheet()->mergeCells('A3:P3');
        $objWorkSheet->getActiveSheet()->mergeCells('A7:K7');
        $objWorkSheet->getActiveSheet()->mergeCells('L7:L8');
        $objWorkSheet->getActiveSheet()->mergeCells('M7:R7');
        $objWorkSheet->getActiveSheet()->mergeCells('S7:S8');
        $objWorkSheet->getActiveSheet()->mergeCells('T7:T8');
        $objWorkSheet->getActiveSheet()->mergeCells('B8:E8');
        $objWorkSheet->getActiveSheet()->mergeCells('B9:T9');
        $objWorkSheet->getActiveSheet()->mergeCells('Q3:R3');
        $objWorkSheet->getActiveSheet()->mergeCells('S3:T3');
        $objWorkSheet->getActiveSheet()->mergeCells('Q4:R4');
        $objWorkSheet->getActiveSheet()->mergeCells('S4:T4');
        
        $objWorkSheet->getActiveSheet()->getStyle("L7:L8")->applyFromArray($B1Style);
        $objWorkSheet->getActiveSheet()->getStyle("L7:L8")->applyFromArray($centerHorizontalAlign_style);
        $objWorkSheet->getActiveSheet()->getStyle("L7:L8")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
        $objWorkSheet->getActiveSheet()->getStyle("S7:S8")->applyFromArray($B1Style);
        $objWorkSheet->getActiveSheet()->getStyle("S7:S8")->applyFromArray($centerHorizontalAlign_style);
        $objWorkSheet->getActiveSheet()->getStyle("S7:S8")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
        $objWorkSheet->getActiveSheet()->getStyle("T7:T8")->applyFromArray($B1Style);
        $objWorkSheet->getActiveSheet()->getStyle("T7:T8")->applyFromArray($centerHorizontalAlign_style);
        $objWorkSheet->getActiveSheet()->getStyle("T7:T8")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
        $objWorkSheet->getActiveSheet()->getStyle("F8:T8")->applyFromArray($B2Style);
        $objWorkSheet->getActiveSheet()->getStyle("F8:T8")->applyFromArray($centerHorizontalAlign_style);
        $objWorkSheet->getActiveSheet()->getStyle("F8:T8")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('d5eadd');
        $objWorkSheet->getActiveSheet()->getStyle("T1:T2")->applyFromArray($rightHorizontalAlign_style);
        $objWorkSheet->getActiveSheet()->getStyle("A3")->applyFromArray($centerHorizontalAlign_style);
        $objWorkSheet->getActiveSheet()->getStyle("A3")->getFont()->setSize(16);
        
        $sheet = $PHPExcel->getActiveSheet();

        #lock for excel file - not allow to edit
        $sheet->getProtection()->setPassword('*****');
        $sheet->getProtection()->setSheet(true);
        #lock to insert new column and row
        $sheet->getProtection()->setInsertRows(true);
        $sheet->getProtection()->setInsertColumns(true);

        $sheet->setCellValue('Q3', __('予算期間').' : ');
        $sheet->setCellValue('S3', $budget_term);
        $sheet->setCellValue('Q4', __('事業領域').' : ');
        $sheet->setCellValue('S4', $ba_code);
        $sheet->setCellValue('A3', $year.__('年度 取引計画'));
        
        $sheet->setCellValue('S7', $year.__('年度下期'));
        $sheet->setCellValue('T7', $year.__('年度').__('年間'));
        $sheet->setCellValue('B8', substr($year, -2).__("年度 取引計画").__('（単位：千円）'));
        $colName = array("F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T");
        
        foreach ($Months as $monthK=>$monthV) {
            if ($monthK == 6) {
                $sheet->setCellValue('L7', $year.__("年度上期"));
            }
            if ($monthK > 6) {
                $sheet->setCellValue($colName[$monthK].'8', __($Months[$monthK-1]));
            } else {
                $sheet->setCellValue($colName[$monthK].'8', __($monthV));
            }
        }
        $sheet->setCellValue('R8', __($Months[11]));
        $sheet->setCellValue('Q8', __($Months[10]));
        $sheet->setCellValue('B9', __('取引合計'));

        $sheet->getStyle('L7')->getAlignment()->setWrapText(true);
        $sheet->getStyle('S7')->getAlignment()->setWrapText(true);
        $sheet->getStyle('T7')->getAlignment()->setWrapText(true);

        $row = 10;
        $sub_cnt = count($trade_data['total']);
        $count = 0;
        $arr = [];
        #get forecast period
        $forecastPeriod = $this->TermModel->getForecastPeriod($term_id);
        // $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];

        #get budget start year
        $term_1st_year = substr($budget_term, 0, 4);
        $start_month = $Common->getMonth($year, $term_id, 'start');
        #get months from start_month to forecast_month
        $year_month = $this->dateRange($start_month, $forecastPeriod);

        $non_res_colName = [];
        foreach ($trade_data['total'] as $sub_acc_name =>  $sub_acc_datas) {
            $count++;
            $objWorkSheet->getActiveSheet()->mergeCells('C'.$row.':E'.$row);
            $objWorkSheet->getActiveSheet()->getStyle('C'.$row.':E'.$row)->applyFromArray($B3Style);

            $sheet->setCellValue('C'.$row, $sub_acc_name);

            $index = 0;
            $acc_cnt = count($sub_acc_datas['data']);
            $sub_cnt_ym = 0;#year_month index count
            foreach ($sub_acc_datas['total']['amount'] as $subacc_mnth => $each_month) {
                if ($each_month < 0) {
                    $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getFont()->getColor()->setRGB('FF0000');
                }
                $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->applyFromArray($B3Style);
                $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
              
                $sheet->setCellValue($colName[$index].$row, $each_month);
                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                /*if($sub_acc_name == "売上高") {$subacc1_sum = $acc_cnt+$row;}
                elseif($sub_acc_name == "売上原価") {$subacc1_sum = $acc_cnt+$row;}

                if($sub_acc_name != "売上総利益") {
                    $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                }else {
                    if($row != ($subacc1_sum+1)) {
                    	$objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].'10+'.$colName[$index].($subacc1_sum+1).')');
                    }else {
                    	$objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].'10)');
                    }
                }*/
                if ($sub_acc_name != "売上総利益") {
                    $have[$sub_acc_name] = $row;
                    $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                } else {
                    $haves["売上総利益"] = $row;
                    if (count($have) < 2) {
                        $subname = array_keys($have)[0];
                        $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have[$subname]).')');
                    } else {
                        $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have['売上高']).'+'.$colName[$index].($have['売上原価']).')');
                    }
                }
                #set formula without result period for sub account
                if ($year == $term_1st_year && strpos($subacc_mnth, '月') && $year_month[$sub_cnt_ym] != '') {
                    $sheet->setCellValue($colName[$index].$row, $each_month);
                    $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f5f5f5');
                    $sub_cnt_ym++;
                }
                $index ++;
            }
                  
            foreach ($sub_acc_datas['data'] as $acc_name => $value) {
                $row ++;
                
                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':E'.$row)->applyFromArray($B1Style);
                $objWorkSheet->getActiveSheet()->mergeCells('D'.$row.':E'.$row);
                $sheet->setCellValue('D'.$row, $acc_name);
                $idx_cnt = 0;
                $acc_cnt_ym = 0;
                foreach ($value['amount'] as $acc_mnth => $each_month) {
                    if ($each_month < 0) {
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getFont()->getColor()->setRGB('FF0000');
                    }
                    $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                    $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->applyFromArray($B1Style);
                    $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                 
                    $sheet->setCellValue($colName[$idx_cnt].$row, $each_month);
                    # first half total
                    $objWorkSheet->getActiveSheet()->setCellValue($colName[6].($row), '=SUM('.$colName[0].($row).':'.$colName[5].($row).')');
                    
                    # sec half total
                    $objWorkSheet->getActiveSheet()->setCellValue($colName[13].($row), '=SUM('.$colName[7].($row).':'.$colName[12].($row).')');

                    # yearly total
                    $objWorkSheet->getActiveSheet()->setCellValue($colName[14].($row), '=SUM('.$colName[6].($row).'+'.$colName[13].($row).')');
                    #set formula without result period for account
                    if ($year == $term_1st_year && strpos($acc_mnth, '月') && $year_month[$acc_cnt_ym] != '') {
                        $sheet->setCellValue($colName[$idx_cnt].$row, $each_month);
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f5f5f5');
                        $acc_cnt_ym++;
                    } else {
                        $total_formula[$acc_name][] = $colName[$idx_cnt].$row;
                        if (!in_array($colName[$idx_cnt], $non_res_colName)) {
                            $non_res_colName[] = $colName[$idx_cnt];
                        }
                    }
                    $idx_cnt ++;
                }
            }

            $row ++;
        }
        
        $objWorkSheet->getActiveSheet()->mergeCells('F'.$row.':K'.$row);
        $objWorkSheet->getActiveSheet()->getStyle('F'.$row.':K'.$row)->getFont()->setSize(10)->getColor()->setRGB('FF0000');

        $row += 2;

        $logistic_data=[];
        $bas = (explode('/', $ba_code))[0];

        $logistic_data = $cache_dta['logistic_data'];
        
        if (empty($logistic_data) && !$save_into_tmp) {
            $errorMsg = parent::getErrorMsg('SE104');
            $this->Flash->set($errorMsg, array("key"=>"UserError"));
            $this->redirect(array('controller'=>'TradingPlan/?year='.$year,'action'=>'index'));
        }
        
        $exit_key = array_keys($trade_data['record']);
        $not_exit_key = array_values(array_diff($logistic_data, $exit_key));

        $empty_trading = $trade_data['record'][$exit_key[0]]['trading'];
        $empty_kpi = $trade_data['record'][$exit_key[0]]['kpi'];

        
        # create new sheet (Khin)
        $objWorksheet2 = $PHPExcel->createSheet();
        $objWorksheet2->setTitle('DestinationList');
        #lock for excel file - not allow to edit for DestinationList sheet
        $objWorksheet2->getProtection()->setPassword('*****');
        $objWorksheet2->getProtection()->setSheet(true);
        #lock to insert new column and row
        $objWorksheet2->getProtection()->setInsertRows(true);
        $objWorksheet2->getProtection()->setInsertColumns(true);
        # list of destination for dropdown in new sheet
        $i=0;
        foreach ($destination as $ba => $dest) {
            $i++;
            $objWorksheet2->setCellValue('A'.$i, $dest);
        }
        $list = '=DestinationList!$A1:A'.$i;
        $copy_data = array('4月'=>0,'5月'=>0,'6月'=>0,'7月'=>0,'8月'=>0,'9月'=>0,'first_half'=>0,'10月'=>0,'11月'=>0,'12月'=>0,'1月'=>0,'2月'=>0,'3月'=>0,'second_half'=>0,'whole_total'=>0);
        
        foreach ($exit_key as $each_key) {
            if ($each_key != '') {
                $objWorkSheet->getActiveSheet()->mergeCells('B'.$row.':C'.$row);
                $objWorkSheet->getActiveSheet()->mergeCells('D'.$row.':G'.$row);
                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->applyFromArray($B1Style);
                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                $sheet->setCellValue('B'.$row, __('取引'));
                $sheet->setCellValue('D'.$row, $each_key);

                $objWorkSheet->getActiveSheet()->mergeCells('H'.$row.':T'.$row);
                $objWorkSheet->getActiveSheet()->getStyle('H'.$row.':T'.$row)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FF0000');
                $sheet->setCellValue('H'.$row, __("⇒取引名をタブより選択して下さい。新規追加する際は財務経理部へご連絡下さい。"));
                $row ++;
                
                foreach ($trade_data['record'][$each_key]['trading'] as $sub_acc_name => $sub_acc_datas) {
                    $objWorkSheet->getActiveSheet()->mergeCells('C'.$row.':E'.$row);
                    $objWorkSheet->getActiveSheet()->getStyle('C'.$row.':E'.$row)->applyFromArray($B3Style);

                    $sheet->setCellValue('C'.$row, $sub_acc_name);

                    $index = 0;
                    $acc_cnt = count($sub_acc_datas['data']);
                    foreach ($sub_acc_datas['total']['amount'] as $each_month) {
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->applyFromArray($B3Style);
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        
                        $sheet->setCellValue($colName[$index].$row, $each_month);
                        if ($sub_acc_name != "売上総利益") {
                            $have[$sub_acc_name] = $row;
                            $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                        } else {
                            $haves["売上総利益"] = $row;
                            if (count($have) < 2) {
                                $subname = array_keys($have)[0];
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have[$subname]).')');
                            } else {
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have['売上高']).'+'.$colName[$index].($have['売上原価']).')');
                            }
                        }
                        # clone for internal pay fee(total of 売上原価)
                        if ($have["売上原価"] != "" && $haves["売上総利益"] && $have["売上原価"] < $haves["売上総利益"]) {
                            $diff = (range($have["売上原価"], $haves["売上総利益"]));
                            $diffcnt = count($diff) - 2;
                            
                            $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$diff[0], '=SUM('.$colName[$index].($diff[1]).':'.$colName[$index].($diffcnt+$diff[0]).')');
                        }
                        $sheet->getStyle($colName[$index].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                        $index ++;
                    }
                    $data = $this->prepareArr($sub_acc_datas['data'], $copy_data);
                    foreach ($data as $acc_name => $value) {
                        foreach ($value['amount'] as $acc => $amount) {
                            $idx_cnt = 0;

                            if (strpos($acc_name, '社内受払手数料') !== false) {
                                $row ++;
                                $acc_name_explode = explode(',', $acc_name);
                                $acc_name = $acc_name_explode[0];
                                $user_input = $acc_name_explode[1];
                                if ($user_input) {
                                    $acc_name = $acc_name.'('.$user_input.')';
                                } else {
                                    if (strpos($acc_name, '(   )') !== false) {
                                        break;
                                    } else {
                                        $acc_name = $acc_name.'(   )';
                                    }
                                }
                                $dest = $acc_name_explode[2];

                                $objWorkSheet->getActiveSheet()->mergeCells('D'.$row.':D'.$row);
                                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':D'.$row)->applyFromArray($B1Style);
                                

                                $acc = (!empty($acc) && $acc != '' && $acc != '1' && $acc != '0') ? $acc : 'Select BA';
                                $acc = ($destination[$acc] != '') ? $destination[$acc] : 'Select BA';
                                
                                $sheet->setCellValue('D'.$row, $acc_name);
                                $sheet->setCellValue('E'.$row, $acc);
                                # add the dropdown list (khin)
                                $objWorkSheet->getActiveSheet()->mergeCells('E'.$row.':E'.$row);
                                $objWorkSheet->getActiveSheet()->getStyle('E'.$row.':E'.$row)->applyFromArray($B1Style);
                                $objWorkSheet->getActiveSheet()->getStyle("E".$row)->applyFromArray($alignleft);
                                $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                                if ($disable_color == 'D5F4FF') {
                                    $objValidation = $sheet->getCell('E'.$row)->getDataValidation();
                                    $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                                    $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                                    $objValidation->setAllowBlank(false);
                                    $objValidation->setShowInputMessage(true);
                                    $objValidation->setShowErrorMessage(true);
                                    $objValidation->setShowDropDown(true);
                                    $objValidation->setFormula1($list);
                                    $sheet->getStyle('D'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                }
                                $sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

                                if($each_key == '取引無し' && $user_input == '' && $dest == '8000') {
                                	$sheet->getStyle('D'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                                	$sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                                }

                            } else {
                                $row ++;
                                $objWorkSheet->getActiveSheet()->mergeCells('D'.$row.':E'.$row);
                                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':E'.$row)->applyFromArray($B1Style);
                                $sheet->setCellValue('D'.$row, $acc_name);
                            }
                            $acc_cnt_ym = 0;
                            foreach ($amount as $month => $each_month) {
                                $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                                $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->applyFromArray($B1Style);
                                $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                               
                                if (($month != 'first_half') && ($month != 'second_half') && ($month != 'whole_total')) {
                                    if ($year == $term_1st_year && $year_month[$acc_cnt_ym] != '') {#actual month disbale
                                        $disable_color = 'F9F9F9';
                                        $acc_cnt_ym++;
                                        if ($each_month != '0') {
                                            if (strpos($acc_name, '社内受払手数料') !== false && $each_key != '取引無し') {
                                                // $sheet->setCellValue('D'.$row, "社内受払手数料(   )");
                                                $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                                                $sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                                                $sheet->getStyle('D'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                            }
                                            
                                            $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                                            $objValidation = $sheet->getCell('E'.$row)->getDataValidation();
                                            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                                            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                                            $objValidation->setAllowBlank(false);
                                            $objValidation->setShowInputMessage(false);
                                            $objValidation->setShowErrorMessage(false);
                                            $objValidation->setShowDropDown(false);

                                            // $sheet->getStyle('D'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                                        }
                                    } else {#normal month enable
                                        $disable_color = 'D5F4FF';
                                        $sheet->getStyle($colName[$idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                    }
                                    $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                                    $sheet->setCellValue($colName[$idx_cnt].$row, $each_month);
                                }

                                # first half total
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[6].($row), '=SUM('.$colName[0].($row).':'.$colName[5].($row).')');
                        
                                # sec half total
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[13].($row), '=SUM('.$colName[7].($row).':'.$colName[12].($row).')');

                                # yearly total
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[14].($row), '=SUM('.$colName[6].($row).'+'.$colName[13].($row).')');
                                
                                $formula[$each_key][$acc_name][] =  $row;
                               
                                $idx_cnt ++;
                            }
                        }
                       
                        $arr[$acc_name] =  array_column($formula, $acc_name);
                    }
                    
                    $row ++;
                }
                $kpi_idx_cnt = 0;

                if (!empty($trade_data['record'][$each_key]['kpi'])) {
                    #kpi row
                    $kpi_row[] = $row;

                    $kpi_unit = array_keys($trade_data['record'][$each_key]['kpi']);

                    $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':E'.$row)->applyFromArray($B3Style);
                    $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                    if ($disable_color == 'D5F4FF') {
                        $sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                    }
                    $sheet->setCellValue('D'.$row, 'KPI');
                    $sheet->setCellValue('E'.$row, $kpi_unit[0]);
                    $sheet->getStyle('E')->applyFromArray($alignleft);

                    foreach ($trade_data['record'][$each_key]['kpi'][$kpi_unit[0]]['amount'] as $each_month) {
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
          
                        if ($kpi_idx_cnt != 6 && $kpi_idx_cnt != 13 && $kpi_idx_cnt != 14) {
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->applyFromArray($B3Style);
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                           
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                            if ($disable_color == 'D5F4FF') {
                                $sheet->getStyle($colName[$kpi_idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                            }
                            $sheet->setCellValue($colName[$kpi_idx_cnt].$row, $each_month);
                        }

                        $kpi_idx_cnt ++;
                    }

                    $objWorkSheet->getActiveSheet()->mergeCells('S'.$row.':T'.$row);
                    $objWorkSheet->getActiveSheet()->getStyle('S'.$row)->getFont()->setSize(8)
                                    ->getColor()->setRGB('FF0000');
                }

                $row += 3 ;
            }
        }
        
        foreach($not_exit_key as $each_key){

        	if($each_key != ''){
        		$objWorkSheet->getActiveSheet ()->mergeCells ( 'B'.$row.':C'.$row);
            	$objWorkSheet->getActiveSheet ()->mergeCells ( 'D'.$row.':G'.$row);
                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->applyFromArray($B1Style);
                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                $sheet->setCellValue('B'.$row, __('取引'));
                $sheet->setCellValue('D'.$row, $each_key);

                $objWorkSheet->getActiveSheet ()->mergeCells ( 'H'.$row.':T'.$row);
                $objWorkSheet->getActiveSheet()->getStyle('H'.$row.':T'.$row)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FF0000');
                $sheet->setCellValue('H'.$row, __("⇒取引名をタブより選択して下さい。新規追加する際は財務経理部へご連絡下さい。"));
                $row ++;
                                   
                foreach ($empty_trading as $sub_acc_name => $sub_acc_datas) {
                    $objWorkSheet->getActiveSheet ()->mergeCells ( 'C'.$row.':E'.$row);
                    $objWorkSheet->getActiveSheet()->getStyle('C'.$row.':E'.$row)->applyFromArray($B3Style);

                    $sheet->setCellValue('C'.$row, $sub_acc_name);

                    $index = 0;$acc_cnt = count($sub_acc_datas['data']);
                    foreach ($sub_acc_datas['total']['amount'] as $each_month) {
                        $each_month= number_format(0, 1);
                       
                    	$objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->applyFromArray($B3Style);
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        // $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getNumberFormat()->setFormatCode('#,##0.0');
                        $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                        $sheet->setCellValue($colName[$index].$row, $each_month);
                       
                        if($sub_acc_name != "売上総利益") {
                            $have[$sub_acc_name] = $row;
                            $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                        }else {
                            $haves["売上総利益"] = $row;
                           	if(count($have) < 2) {
                           		$subname = array_keys($have)[0];
                           		$objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($have[$subname]).')');
                           	}else {
                           		$objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($have['売上高']).'+'.$colName[$index].($have['売上原価']).')');

                           	}
                        }
                        # clone for internal pay fee(total of 売上原価)
                        if($have["売上原価"] != "" && $haves["売上総利益"] && $have["売上原価"] < $haves["売上総利益"]) {
                            
                            $diff = (range($have["売上原価"], $haves["売上総利益"]));
                            $diffcnt = count($diff) - 2;
                            
                            $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$diff[0],'=SUM('.$colName[$index].($diff[1]).':'.$colName[$index].($diffcnt+$diff[0]).')');
                            
                        }
                        $index ++;
                        
                    }
                    
                    $backup_amount = $sub_acc_datas['data'][array_keys($sub_acc_datas['data'])[0]]['amount'];
                    $merge_notexit = [];$cnt_notexit = 0;$chk_array = 0;
        			foreach ($sub_acc_datas['data'] as $acc_name => $value) {
        				$cnt_notexit++;
                        if (strpos($acc_name, '社内受払手数料') !== false){
                            unset($value);
                            array_push($merge_notexit, $acc_name);
                    		if($acc_cnt == $cnt_notexit) {
                    			for ($i = 0; $i < 3; $i++) {
        				    		$value['amount'][] = $copy_data;
        				  		}
                                $value['code'] = '6108000000';
                                $value['sub'] = '2';
                    		}
                        }
                        
                        foreach ($value['amount'] as $acc => $amount) {
                            $idx_cnt = 0;
                            if (strpos($acc_name, '社内受払手数料') !== false){
                                $row ++;
                                $acc_name = '社内受払手数料 (   )';
                                $objWorkSheet->getActiveSheet ()->mergeCells( 'D'.$row.':D'.$row);
        						$objWorkSheet->getActiveSheet()->getStyle('D'.$row.':D'.$row)->applyFromArray($B1Style);
                                
        						$acc = (!empty($acc) && $acc != '' && $acc != '1' && $acc != '0') ? $acc : 'Select BA';
        						$acc = ($destination[$acc] != '') ? $destination[$acc] : 'Select BA';
        						$sheet->setCellValue('D'.$row, $acc_name);
        						$sheet->setCellValue('E'.$row, 'Select BA');
        						# add the dropdown list (khin)
        						$objWorkSheet->getActiveSheet ()->mergeCells ( 'E'.$row.':E'.$row);
        						$objWorkSheet->getActiveSheet()->getStyle('E'.$row.':E'.$row)->applyFromArray($B1Style);
        						$objWorkSheet->getActiveSheet()->getStyle("E".$row)->applyFromArray($alignleft);
        						$objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
        						if($disable_color == 'D5F4FF') {
        							$objValidation = $sheet->getCell('E'.$row)->getDataValidation();
        							$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
        							$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
        							$objValidation->setAllowBlank(false);
        							$objValidation->setShowInputMessage(true);
        							$objValidation->setShowErrorMessage(true);
        							$objValidation->setShowDropDown(true);
        							$objValidation->setFormula1($list);
        							$sheet->getStyle('D'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
        							$sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
        						}
                            
                            }else {
                                $row ++;
                                $objWorkSheet->getActiveSheet ()->mergeCells ( 'D'.$row.':E'.$row);
                                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':E'.$row)->applyFromArray($B1Style);
                                $sheet->setCellValue('D'.$row, $acc_name);
                            }
                            $acc_cnt_ym = 0;
                            foreach ($amount as $month => $each_month) {
                                
                                $each_month= number_format(0, 1);
                               
                                $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->applyFromArray($B1Style);
                                $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                // $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getNumberFormat()->setFormatCode('#,##0.0');
                                $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                                if (($month != 'first_half') && ($month != 'second_half') && ($month != 'whole_total')) {
                                	if ($year == $term_1st_year && $year_month[$acc_cnt_ym] != '') {
                                    	$disable_color = 'F9F9F9';
                                    	$acc_cnt_ym++;
                                    }else{
                                    	$disable_color = 'D5F4FF';
        		                    	$sheet->getStyle($colName[$idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
        		                    }
                                    $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                                    $sheet->setCellValue($colName[$idx_cnt].$row, $each_month);
                                }

                                # first half total
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[6].($row),'=SUM('.$colName[0].($row).':'.$colName[5].($row).')');
                        
                                # sec half total
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[13].($row),'=SUM('.$colName[7].($row).':'.$colName[12].($row).')');

                                # yearly total
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[14].($row),'=SUM('.$colName[6].($row).'+'.$colName[13].($row).')');
                                
                                $formula[$each_key][$acc_name][] =  $row;
                               
                                $idx_cnt ++;
                            }
                        }
                        $arr[$acc_name] =  array_column($formula, $acc_name);
                    }
                    $row ++;
                }
                $kpi_idx_cnt = 0;

                if(!empty($empty_kpi)){
                    #kpi row
                    $kpi_row[] = $row;

                    $kpi_unit = array_keys($empty_kpi);

                    $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':E'.$row)->applyFromArray($B3Style);
                    $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                    if($disable_color == 'D5F4FF'){

                    	$sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                    }
                    $sheet->setCellValue('D'.$row, 'KPI');
                    //$sheet->setCellValue('E'.$row, $kpi_unit[0]);
                    $sheet->getStyle('E')->applyFromArray($alignleft);

                    foreach ($empty_kpi[$kpi_unit[0]]['amount'] as $each_month) {

                        $each_month= number_format(0, 1);
                       
                        if($kpi_idx_cnt != 6 && $kpi_idx_cnt != 13 && $kpi_idx_cnt != 14) {

                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->applyFromArray($B3Style);
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            // $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getNumberFormat()->setFormatCode('#,##0.0');
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                            $sheet->setCellValue($colName[$kpi_idx_cnt].$row, $each_month);

                            if($disable_color == 'D5F4FF'){
                            	$sheet->getStyle($colName[$kpi_idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                            }
                        }

                        $kpi_idx_cnt ++;
                    }

                    $objWorkSheet->getActiveSheet ()->mergeCells ( 'S'.$row.':T'.$row);
                    $objWorkSheet->getActiveSheet()->getStyle('S'.$row)->getFont()->setSize(8)
                                    ->getColor()->setRGB('FF0000');
                }else {
                	$kpi_row[] = $row;

                    $kpi_unit = array_keys($trade_data['record'][$each_key]['kpi']);

                    $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':E'.$row)->applyFromArray($B3Style);
                    $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                    if ($disable_color == 'D5F4FF') {
                        $sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                    }
                    $sheet->setCellValue('D'.$row, 'KPI');
                    $sheet->setCellValue('E'.$row, '');
                    $sheet->getStyle('E')->applyFromArray($alignleft);
                    foreach ($copy_data as $each_month) {

                        $each_month= number_format(0, 1);
                       
                        if($kpi_idx_cnt != 6 && $kpi_idx_cnt != 13 && $kpi_idx_cnt != 14) {

                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->applyFromArray($B3Style);
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getNumberFormat()->setFormatCode('#,##0.0');
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$kpi_idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                            $sheet->setCellValue($colName[$kpi_idx_cnt].$row, $each_month);

                            if($disable_color == 'D5F4FF'){
                            	$sheet->getStyle($colName[$kpi_idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                            }
                        }

                        $kpi_idx_cnt ++;
                    }

                    $objWorkSheet->getActiveSheet ()->mergeCells ( 'S'.$row.':T'.$row);
                    $objWorkSheet->getActiveSheet()->getStyle('S'.$row)->getFont()->setSize(8)
                                    ->getColor()->setRGB('FF0000');
                }

                $row += 3 ;
        	}
            
        }
        
        $start=0;
        $two = [];
        $ones = [];
        foreach ($arr as $key => $value) {
            $one = [];
            foreach ($value as $keys => $values) {
                if (strpos($key, '社内受払手数料') !== false) {
                    foreach ($values as $paykey => $payval) {
                        array_push($ones, $payval);
                    }
                    sort($ones);
                    $two["社内受払手数料"] = implode('+', array_unique($ones));
                } else {
                    array_push($one, $values[0]);
                    $two[$key] = implode('+', $one);
                }
            }
        }
        
        $for = [];
        foreach ($total_formula as $totalkey => $totalvalue) {
            foreach ($two as $twokey => $twovalue) {
                if ($totalkey == $twokey || strpos($twokey, '社内受払手数料') !== false) {
                    $for[$twovalue] = $totalvalue;
                }
            }
        }

        foreach ($for as $aakey => $aavalue) {
            $idx = 0;
            foreach ($aavalue as $keya => $valuea) {
                if ($non_res_colName[$idx] != 'L' && $non_res_colName[$idx] != 'S' && $non_res_colName[$idx] != 'T') {
                    $for_last = $non_res_colName[$idx].(str_replace('+', '+'.$non_res_colName[$idx], $aakey));
                    $objWorkSheet->getActiveSheet()->setCellValue($valuea, '=SUM('.$for_last.')');
                }
                $idx++;
            }
        }

        $objWorkSheet->getActiveSheet()->setTitle('TradingPlan');
  
        if ($save_into_tmp) {
            $PHPExcel->save($file_name);
        } else {
            $PHPExcel->output($file_name.".xlsx");
        }
    }
    public function prepareArr($array, $copy_data)
    {
        $chk_des = [];
        $one = [];
        foreach ($array as $key => $value) {
        	if (strpos($key, '社内受払手数料') !== false) {
                foreach ($value as $keys => $values) {
                    foreach ($values as $des => $amt) {
                        array_push($chk_des, $des);
                        $main_key = ($key == '社内受払手数料')? $key.',,'.$des : $key.','.$des;
                        $array[$main_key]['amount'][$des] = $amt;
                        $array[$main_key]['code'] = $value['code'];
                        $array[$main_key]['sub_id'] = $value['sub_id'];
                        $one['code'] = $value['code'];
                        $one['sub_id'] = $value['sub_id'];
                    }
                }
                unset($array[$key]);
            }
        }
        $cnt = count($chk_des);
        if ($cnt > 0) {
            $a = 0;
            for ($i = $cnt; $i < 3; $i++) {
                $a = $i;
                $a++;
                $array['社内受払手数料,,'.$a]['amount'][''] = $copy_data;
                $array['社内受払手数料,,'.$a]['code'] = $one['code'];
                $array['社内受払手数料,,'.$a]['sub_id'] = $one['sub_id'];
            }
        }
        return $array;
    }
    public function getDestination()
    {
        $today = date("Y-m-d");
        $destination = [];
        $active_BA = $this->BusinessAreaModel->find('all', array(
            'fields' => array('id','ba_code','ba_name_en','ba_name_jp'),
            'conditions' => array(
                'flag' => 1,
                'dept_id !=' => '',
                'head_dept_id !=' => '',
                'from_date <=' => $today,
                'to_date >=' => $today
            )
        ));

        foreach ($active_BA  as $ba) {
            if ($language == 'eng') {
                if (!empty($ba['BusinessAreaModel']['ba_name_en'])) {
                    $destination[$ba['BusinessAreaModel']['ba_code']] = $ba['BusinessAreaModel']['ba_code'].'/'.$ba['BusinessAreaModel']['ba_name_en'];
                } else {
                    $destination[$ba['BusinessAreaModel']['ba_code']] = $ba['BusinessAreaModel']['ba_code'];
                }
            } else {
                $destination[$ba['BusinessAreaModel']['ba_code']] = $ba['BusinessAreaModel']['ba_code'].'/'.$ba['BusinessAreaModel']['ba_name_jp'];
            }
        }
        return $destination;
    }
    /**
     * dateRange method
     *
     * @author Khin Hnin Myo (20200920)
     *
     * @param date $first, date $last
     * @return dates
     */
    public function dateRange($first, $last, $step = '+1 month', $format = 'Y-m')
    {
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    /**
     * getActualSummaryAmount method
     *
     * @author Khin Hnin Myo (20200928)
     *
     * @param $subid_del, $months, $year_month, $ba_code
     * @return $actual
     */
    public function getActualSummaryAmount($subid_del, $months, $year_month, $ba_code)
    {
        $count = min(count($months), count($year_month));
        $yr_month = array_combine(array_slice($months, 0, $count), array_slice($year_month, 0, $count));

        // $yr_month = array_combine($months, $year_month);
        foreach ($subid_del as $id) {
            if ($id != 0) {
                $id_arr = array($id);
            
                $codes = $this->AccountModel->find('list', array(
                    'fields' => array('account_code'),
                    'conditions' => array(
                        'sub_acc_id' => $id_arr,
                        'flag' => 1
                    )
                ));
                
                $a_codes[$id] = "'".join("','", $codes)."'";
            }
        }
        $bacode = "'".$ba_code."'";

        foreach ($yr_month as $yrkey => $mvalue) {
            foreach ($a_codes as $subkey => $accvalue) {
                $actual_amt = $this->ActualResultSummaryModel->getMonthlyResult($bacode, $mvalue, $accvalue);
                $actual[$subkey][$yrkey] = round($actual_amt/1000, 1);
            }
        }
        return $actual;
    }

    /**
     * compareActualAndTrading method
     *
     * @author Khin Hnin Myo (20200928)
     *
     * @param $ba_code, $head_dept_id, $actual, $tot
     * @return array
     */
    public function compareActualAndTrading($ba_code, $head_dept_id, $actual, $tot)
    {
        $diff = [];
        $compareFlg = false;
        
        $deptid = $this->BusinessAreaModel->find('list', array(
            'fields' => array(
                'dept_id'
            ),
            'conditions' => array(
                'ba_code' => $ba_code,
                'head_dept_id' => $head_dept_id
            )
        ));
        $dept_id = (array_values($deptid))[0];

        foreach ($actual as $actualkey => $actualvalue) {
            foreach ($tot as $totkey => $totvalue) {
                if ($actualkey == $totkey) {
                    $totvalues = array_intersect_key($totvalue, $actualvalue);
                    if (array_diff($totvalues, $actualvalue) == array_diff($actualvalue, $totvalues)) {
                        $difference =  0;
                    } //equal
                    else {
                        $difference = 1;
                    } // not equal
                    array_push($diff, $difference);

                    if (in_array(1, $diff)) { // true(not equal)
                        
                        $this->Session->write('CHECKACTUAL', 'nosame');
                        $compareFlg = true;
                    }
                }
            }
        }
        
        return array($compareFlg, $dept_id);
    }
    /**
     * getSubAccName method
     *
     * @author Khin Hnin Myo (20200928)
     *
     * @param $sub_acc_id
     * @return $subname
     */
    public function getSubAccName($sub_acc_id)
    {
        $subname = $this->SubAccountModel->find('first', array(
            'fields' => array('sub_acc_name_jp'),
            'conditions' => array(
                'flag' => 1,
                'id' => $sub_acc_id
            )
        ));
        return $subname['SubAccountModel']['sub_acc_name_jp'];
    }

    public function getTradingData($modelName, $months, $term_id, $year, $ba_code, $account_code, $index_no)
    {
        $tradingData = $this->$modelName->find('all', array(
            'fields' => array(
                '(month_1_amt)/1000 as '.$months[0],
                '(month_2_amt)/1000 as '.$months[1],
                '(month_3_amt)/1000 as '.$months[2],
                '(month_4_amt)/1000 as '.$months[3],
                '(month_5_amt)/1000 as '.$months[4],
                '(month_6_amt)/1000 as '.$months[5],
                '(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt)/1000 as first_half',
                '(month_7_amt)/1000 as '.$months[6],
                '(month_8_amt)/1000 as '.$months[7],
                '(month_9_amt)/1000 as '.$months[8],
                '(month_10_amt)/1000 as '.$months[9],
                '(month_11_amt)/1000 as '.$months[10],
                '(month_12_amt)/1000 as '.$months[11],
                '(month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt)/1000 as second_half',
                '(month_1_amt+month_2_amt+month_3_amt+month_4_amt+month_5_amt+month_6_amt+month_7_amt+month_8_amt+month_9_amt+month_10_amt+month_11_amt+month_12_amt)/1000 as whole_total',
                'account_code','logistic_index_no','destination',
                'sub_acc_id','kpi_unit','trade_filling_date'
            ),
            'conditions' => array(
                'term_id' => $term_id,
                'target_year' => $year,
                'ba_code' => $ba_code,
                'account_code' => $account_code,
                'logistic_index_no' => $index_no,
                'flag' => 1,
                'NOT' => array(
                    'trade_filling_date' => '',
                )
            ),
            // 'order' => 'destination IS NOT NULL AND kpi_unit IS NULL, destination, kpi_unit'
        ));

        return $tradingData;
    }
    /**
     * getSubAccName method
     *
     * @author Aye Zar Ni Kyaw (20210121)
     *
     * @param $sub_acc_id
     * @return $subname
     */
    public function UploadFile()
    {
        App::import('Vendor', 'php-excel-reader/PHPExcel');
        $loginId = $this->Session->read('LOGIN_ID');
        $budget_term = $this->Session->read('TERM_NAME');
        $diff_ba = explode("/", $this->request->data['ba']);
        $ba_code = $diff_ba[0];
        $ba_name = $diff_ba[1];
        $target_year = $this->request->data['year'];
        $term_id = $this->Session->read('TERM_ID');
        $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');

        $file 			=  $this->request->params['form']['trading_upload'];

        $file_name 		= $file['name'];
        $file_path 		= $file['tmp_name'];
        $ext 			= pathinfo($file_name, PATHINFO_EXTENSION);

        $error = (empty($file)) ? 				parent::getErrorMsg('SE015') :
                 ($file['error'] != 0) ? 		parent::getErrorMsg('SE015') :
                 // check 10MB
                 ($file['size'] >= 10485760) ? 	parent::getErrorMsg('SE020') :
                 (!($ext == "xlsx" || $ext == "xls")) ? parent::getErrorMsg("SE013", $ext) : 'false';

        if ($error == 'false') {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader->setReadDataOnly(true);

            if ($objReader->canRead($file_path)) {
                $objPHPExcel   = $objReader->load($file_path);
                $objWorksheet  = $objPHPExcel->getActiveSheet();
                $highestRow    = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();

                $searchValue = '⇒取引名をタブより選択して下さい。新規追加する際は財務経理部へご連絡下さい。';

                $chkTerm 	= $objWorksheet->getCell("S3")->getValue();
                $diff_excelBa = explode("/", $objWorksheet->getCell("S4")->getValue());
                $chkBA = $diff_excelBa[0];
                $chkheader	= $objWorksheet->getCell("A3")->getValue();
                $chkTarget = substr($chkheader, 0, 4);
                $chkheaderName = substr($chkheader, 4);

                if (($chkTerm == $budget_term) && ($chkBA == $ba_code) && ($chkTarget == $target_year)) {
                    for ($row = 7; $row <= $highestRow; $row++) {
                        $rowData = $objWorksheet->rangeToArray('B' . $row . ':' . 'T' . $row, null, true, false);
                        $worksheets[] = $rowData;
                    }

                    #find excel of start search row
                    for ($k =7; $k <= $highestRow; $k++) {
                        $excel_row = $objWorksheet->getCell("H". $k)->getValue();
                        if ($excel_row == $searchValue) {
                            $index = $k;
                            break;
                        }
                    }
                    # show err msg for wrong formula
                    $col = range("F", "T");
                    for ($p =48; $p <= $highestRow; $p++) {
                        for ($a=0;$a<count($col);$a++) {
                            $formulas = $objWorksheet->getCell($col[$a].$p)->getOldCalculatedValue();
                            if (strpos($formulas, '#') !== false) {
                                $err = array(__('小数点以下2桁の9桁'),$col[$a],$p);
                                $error = parent::getErrorMsg('SE099', $err);
                                $this->Flash->set($error, array("key"=>"UserError"));
                                $this->redirect(array('controller'=>'TradingPlan/?year='.$target_year,'action'=>'index'));
                            }
                        }
                    }
                    #error check and save data prepare
                    $getExcelData = $this->checkHeaderAndColumn($worksheets, $index);
                    
                    $Common = new CommonController;
                    $months = $Common->get12Month($term_id);

                    if (empty($getExcelData['error'])) {
                        $importData = $getExcelData['success'];
                        // Edited by Ei Thandar Kyaw
                        // check which logistic are save
                        // if have value are != 0, will save.
                        #Get data from cache
                        $cache_name = 'trading_plan_'.$term_id.'_'.$target_year.'_'.(explode('/', $ba_code))[0].'_'.$loginId;
                        $cache_dta = Cache::read($cache_name);
                        $trade_data = $cache_dta['trade_data'];
                        // get current logistix no in view
                        $logisticInView = array();
                        foreach ($trade_data['record'] as $key=>$value) {
                            $logisticInView[] = $key;
                        }
                        // get save logistic in excel
                        $saveLogistic = array();
                        foreach ($importData as $logistic_no => $logistic_data) {
                            foreach ($logistic_data as $sub_acc_id => $sub_acc_datas) {
                                foreach ($sub_acc_datas as $acc_code => $acc_value) {
                                    foreach ($acc_value as $des_ba => $amount) {
                                    	$des_n_user = explode('/', $des_ba);
                                        $months_amts = array_values(preg_replace("/[^-0-9\.]/", "", $amount));
                                        for ($i = 1; $i <= 13; $i ++) {
                                            if ($months_amts[$i] != 0 || $des_n_user[0] != '' || $des_n_user != '') {
                                                $saveLogistic[] = $logistic_no;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // merge logistic in view and excel
                        $arrayUnique = array_unique($saveLogistic);
                        $arrayUnique = array_merge($arrayUnique, $logisticInView);
                        $logisticIndex = array_unique($arrayUnique);
                        // create array to save by logistic no
                        foreach ($importData as $logistic_no => $logistic_data) {
                            foreach ($logisticIndex as $logistic) {
                                if ($logistic_no == $logistic) {
                                    $excel_data[$logistic_no] = $importData[$logistic_no];
                                }
                            }
                        }
        
                        // End of edited by Ei Thandar Kyaw
                        foreach ($excel_data as $logistic_no => $logistic_data) {
                            $logi[] = $logistic_no;
                            foreach ($logistic_data as $sub_acc_id => $sub_acc_datas) {
                                $subid_del[] = $sub_acc_id;
                                foreach ($sub_acc_datas as $acc_code => $acc_value) {
                                    foreach ($acc_value as $des_ba => $amount) {
                                        $payfee = '';
                                        $date = date_create($amount['trade_filling_date']);
                                        $trade_filling_date = date_format($date, "Y-m-d H:i:s");
                                        $months_amts = array_values(preg_replace("/[^-0-9\.]/", "", $amount));
                                        if (strstr($des_ba, '/_')) {
                                            $des_ba = '/';
                                        }
                                        $des = (strstr($des_ba, '/'))? explode('/', $des_ba)[0] : $des_ba;
                                        $payfee = (strstr($des_ba, '/'))? explode('/', $des_ba)[1] : $amount['kpi_unit'];

                                        if(explode('/', $des_ba)[2]) $payfee = $payfee.'/'.explode('/', $des_ba)[2];
                                        
                                        $save = false;
                                        if (strstr($des, 'view')) {
                                            $des = explode('_', $des)[0];
                                        }
                                        if ($acc_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                                            $allZero = true;
                                            foreach ($months_amts as $mKey=>$mValue) {
                                                if ($mKey != 0 && $mKey != 7 && $mKey != 14 && $mKey != 15) {
                                                    if ($mValue != 0) {
                                                        $allZero = false;
                                                    }
                                                }
                                            }
                                            if ($des != '' || $payfee != '') {
                                                $save = true;
                                            }
                                            // if(!$allZero && (empty($des) || empty($payfee))){
                                            // 	$error = parent::getErrorMsg('SE119', $logistic_no);
                                            // 	$this->Flash->set($error, array("key"=>"UserError"));
                                            // 	$this->redirect(array('controller'=>'TradingPlan/?year='.$target_year,'action'=>'index'));
                                            // }
                                            if (!$allZero && (empty($des))) {
                                                $error = parent::getErrorMsg('SE119', $logistic_no);
                                                $this->Flash->set($error, array("key"=>"UserError"));
                                                $this->redirect(array('controller'=>'TradingPlan/?year='.$target_year,'action'=>'index'));
                                            }
                                        } else {
                                            $save = true;
                                        }
                                        if ($save) {
                                            $save_data[] = array(
                                                'term_id'  	   => $term_id,
                                                'target_year'  => $target_year,
                                                'ba_code'  	   => $ba_code,
                                                'trade_filling_date'  => $trade_filling_date,
                                                'sub_acc_id'   =>  $sub_acc_id,
                                                'account_code' =>  $acc_code,
                                                'month_1_amt'  =>  round($months_amts[1], 3)*1000,
                                                'month_2_amt'  =>  round($months_amts[2], 3)*1000,
                                                'month_3_amt'  =>  round($months_amts[3], 3)*1000,
                                                'month_4_amt'  =>  round($months_amts[4], 3)*1000,
                                                'month_5_amt'  =>  round($months_amts[5], 3)*1000,
                                                'month_6_amt'  =>  round($months_amts[6], 3)*1000,
                                                'month_7_amt'  =>  round($months_amts[8], 3)*1000,
                                                'month_8_amt'  =>  round($months_amts[9], 3)*1000,
                                                'month_9_amt'  =>  round($months_amts[10], 3)*1000,
                                                'month_10_amt' =>  round($months_amts[11], 3)*1000,
                                                'month_11_amt' =>  round($months_amts[12], 3)*1000,
                                                'month_12_amt' =>  round($months_amts[13], 3)*1000,
                                                'flag'  	   => 1,
                                                'logistic_index_no'  => $logistic_no,
                                                'destination'  => $des,
                                                'kpi_unit'     => $payfee,
                                                'created_id'   => $loginId,
                                                'updated_id'   => $loginId

                                            );
                                        }
                                        foreach ($amount as $key => $amountvalue) {
                                            $total[$sub_acc_id][$key] += $amountvalue;
                                        }
                                    }
                                }
                            }
                        }
                        ## unset array
                        foreach ($total as $sub_acc_id => $amount) {
                            unset($amount['kpi_unit']);
                            unset($amount['1st_half_total']);
                            unset($amount['2nd_half_total']);
                            unset($amount['sub_total']);
                            $tot[$sub_acc_id] = $amount;
                        }

                        $start_month = $Common->getMonth($target_year, $term_id, 'start');
                        $dead_date = $this->TermModel->find('first', array(
                            'fields' => array('forecast_period'),
                            'conditions' => array(
                                'id' => $term_id
                            )
                        ));
                        $forecast_period = $dead_date['TermModel']['forecast_period'];
                        $year_month = $this->dateRange($start_month, $forecast_period);

                        # actual result amount
                        /*$actual = $this->getActualSummaryAmount($subid_del, $months, $year_month, $ba_code);

                        # compare with actual and trading
                        $compareFlg = $this->compareActualAndTrading($ba_code, $head_dept_id, $actual, $tot);

                        # prepare to save tbl_budget_log
                        if($compareFlg[0]) { # actual != trading
                            $log['term_id'] = $term_id;
                            $log['head_dept_id'] = $head_dept_id;
                            $log['ba_code'] = $ba_code;
                            $log['dept_id'] = $compareFlg[1];
                            $log['flag'] = 3;
                            $log['created_id'] = $loginId;
                            $log['updated_id'] = $loginId;

                        }
                        # prepare to delete tbl_budget_log(old data)
                        $del_log['term_id'] = $term_id;
                        $del_log['head_dept_id'] = $head_dept_id;
                        $del_log['ba_code'] = $ba_code;
                        $del_log['dept_id'] = $compareFlg[1];
                        $del_log['flag'] = 3;*/

                        $term_1st_year = substr($budget_term, 0, 4);
                        if ($target_year == $term_1st_year) {
                            $modelName = 'ExpectedModel';
                            $type = 'forecast';
                        } else {
                            $modelName = 'BudgetPrimeModel';
                            $type = 'budget';
                        }

                        $attachDB = $this->$modelName->getDataSource();
                        /* $BudgetDB = $this->BudgetLogModel->getDataSource();*/
                        $BPData = new BudgetPlanController();
                        try {
                            $attachDB->begin();
                            // $BudgetDB->begin();
                            # Delete old data
                            $this->$modelName->deleteAll(array(
                                'term_id' 	  => $term_id,
                                'target_year' => $target_year,
                                'ba_code'     => $ba_code,
                                'sub_acc_id'  => $subid_del
                            ), false);
                            
                            # Save new data
                            if (!empty($save_data)) {
                                $this->$modelName->saveAll($save_data);
                            }

                            # Save to tbl_budget_log
                            /*if(!empty($log)) {
                                $this->BudgetLogModel->deleteAll(array($del_log),false);
                                $this->BudgetLogModel->saveAll($log);
                            }else {
                                $this->BudgetLogModel->deleteAll(array($del_log),false);
                            }*/

                            // upload tax calculate
                            $filling_date = '';
                            $manual_tax_ba = Setting::BA_BUDGET_TAX;
                            $calculate_tax = [];
                            if (!in_array($ba_code, $manual_tax_ba)) {
                                // $calculate_tax = $MPData->fixTaxCalculations($term_id, $filling_date, $budget_term, $head_dept_id, $loginId, $trade_filling_date, $ba_code, $target_year);
                                $calculate_tax = $BPData->updateTaxAmount($term_id, $budget_term, $head_dept_id, $head_dept_name, $ba_code, $ba_name, $target_year, $loginId, $modelName);
                            }else {
                                $calculate_tax = true;
                            }
                            $attachDB->commit();
                            // $BudgetDB->commit();
                            if(count($logi) != 0 && $calculate_tax) {
	                            $successMsg = parent::getSuccessMsg('SS001');
	                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
	                            $successMsg = parent::getSuccessMsg('SS026', count($logi));
	                            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                        	}else {
                        		$attachDB->rollback();
	                            // $BudgetDB->rollback();
	                            $errorMsg = parent::getErrorMsg('SE017', __("アップロード"));
	                            $this->Flash->set($errorMsg, array("key"=>"UserError"));
                        	}
                        } catch (Exception $e) {
                            $attachDB->rollback();
                            // $BudgetDB->rollback();
                            $errorMsg = parent::getErrorMsg('SE003');
                            $this->Flash->set($errorMsg, array("key"=>"UserError"));

                            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    } else {
                        #if have excel error will show.
                        $error = $getExcelData['error'];
                        $this->Flash->set($error, array("key"=>"UserError"));
                    }
                } else {
                    #file format invaild
                    if ($chkheaderName != __("年度 取引計画")) {
                        $error = parent::getErrorMsg('SE021');
                    } else {
                        #error of excel budget term and ba_code
                        $error = parent::getErrorMsg('SE100', __("予算年度")." , ".__("予算期間")." , ".__("BAコード"));
                    }
                    $this->Flash->set($error, array("key"=>"UserError"));
                }

                $this->redirect(array('controller'=>'TradingPlan/?year='.$target_year,'action'=>'index'));
            }
        } else {
            #error of file size over
            $this->Flash->set($error, array("key"=>"UserError"));
        }
        $this->redirect(array('controller'=>'TradingPlan/?year='.$target_year,'action'=>'index'));
    }
    /**
     * Check excel import validation
     *
     * @author Aye Zar Ni Kyaw (20210122)
     * @param worksheets
     * @return data
     */
    public function checkHeaderAndColumn($worksheets, $index)
    {
        $target_year = $this->request->data['year'];
        $loginId = $this->Session->read('LOGIN_ID');

        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('TRADE_DATA')) {
            $trade_data = $this->Session->read('TRADE_DATA');
        }


        $Common = new CommonController;
        $months = $Common->get12Month($term_id);
        $short_year = substr($target_year, -2);
        #get forecast period
        $forecastPeriod = $this->TermModel->getForecastPeriod($term_id);
        // $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];

        #get budget start year
        $start_month = $Common->getMonth($target_year, $term_id, 'start');
        #get months from start_month to forecast_month
        $year_month = $this->dateRange($start_month, $forecastPeriod);
        $first_half_lbl = $target_year.__('年度'). __('上期');
        $second_half_lbl = $target_year.__('年度'). __('下期');
        $org_header = array(
            'tradeText' 	=> $short_year.__('年度 取引計画'). __('（単位：千円）'),
            'month1' 		=> __($months[0]),
            'month2'		=> __($months[1]),
            'month3'		=> __($months[2]),
            'month4'		=> __($months[3]),
            'month5'		=> __($months[4]),
            'month6'		=> __($months[5]),
            'first_half'	=> str_replace(' ', '', $first_half_lbl),
            'month7' 		=> __($months[6]),
            'month8' 		=> __($months[7]),
            'month9'		=> __($months[8]),
            'month10' 		=> __($months[9]),
            'month11'		=> __($months[10]),
            'month12'		=> __($months[11]),
            'second_half'	=> str_replace(' ', '', $second_half_lbl),
            'yearly'		=> $target_year.__('年度'). __('年間')
        );

        $excel_header = array(
            'tradeText' 	=> $worksheets[1][0][0],
            'month1' 		=> $worksheets[1][0][4],
            'month2'		=> $worksheets[1][0][5],
            'month3'		=> $worksheets[1][0][6],
            'month4'		=> $worksheets[1][0][7],
            'month5'		=> $worksheets[1][0][8],
            'month6'		=> $worksheets[1][0][9],
            'first_half'	=> str_replace(' ', '', $worksheets[0][0][10]),
            'month7' 		=> $worksheets[1][0][11],
            'month8' 		=> $worksheets[1][0][12],
            'month9'		=> $worksheets[1][0][13],
            'month10' 		=> $worksheets[1][0][14],
            'month11'		=> $worksheets[1][0][15],
            'month12'		=> $worksheets[1][0][16],
            'second_half'	=> str_replace(' ', '', $worksheets[0][0][17]),
            'yearly'		=> $worksheets[0][0][18]
        );
        $header_result 	 = array_diff($org_header, $excel_header);

        $org_subAcc_arr = [];
        $org_account_arr = [];
        $excel_subAcc_arr = [];
        $excel_account_arr = [];
        $ws_index = $index-6;
        $i = 0;
        //$firstKey = array_key_first($trade_data['record']);
        $firstKey = $this->array_key_first($trade_data['record']);
        
        foreach ($trade_data['record'] as $logistic_index => $datas) {
            foreach ($datas['trading'] as $sub_acc_name => $sub_acc_datas) {
                #sub_acc_name push to array
                array_push($org_subAcc_arr, $sub_acc_name);
                foreach ($sub_acc_datas['data'] as $acc_name => $acc_value) {
                    foreach ($acc_value['amount'] as $des_ba => $month) {
                        $account_name	= (!empty($des_ba)) ? $acc_name."(".$des_ba.")" : $acc_name;
                        #acc_name push to array
                        array_push($org_account_arr, $acc_name);
                        $trade_value[$logistic_index]
                                    [$acc_value['sub_id']]
                                    [$acc_value['code']]
                                    [] = $des_ba;
                    }
                }
            }
            #for kpi
            $trade_value[$logistic_index][0]['0000000000']['ba'] = '';
        }
        
        $logistic_data=[];
        $active_logistic=$this->LogisticModel->find('list', array(
            'fields' => array('id','index_name'),
            'conditions' => array(
                'ba_code' => $ba_code,
                'target_year' => $target_year,
                'flag' => 1
            ),
            'group' => 'index_name',
            'order' => 'LogisticModel.logistic_order ASC','LogisticModel.index_name ASC'
        ));
        // Edited by Ei Thandar Kyaw
        $cache_name = 'trading_plan_'.$term_id.'_'.$target_year.'_'.(explode('/', $ba_code))[0].'_'.$loginId;
        $cache_dta = Cache::read($cache_name);

        $logistic_data = $cache_dta['logistic_data'];
        $exit_key = array_keys($trade_data['record']);
        $not_exit_key = array_diff($logistic_data, $exit_key);
        // add all logistic no which not include in view
        foreach ($not_exit_key as $logistic_index) {
            foreach ($trade_value as $logisticIndex => $datas) {
                if ($firstKey = $logisticIndex) {
                    foreach ($datas as $key_data=>$data) {
                        foreach ($datas as $one_key=>$one_data) {
                            foreach ($one_data as $key=>$value) {
                                if ($one_key == $key_data) {
                                    if ($key_data == 0) {
                                        $trade_value[$logistic_index][$key_data][$key]['ba'] = '';
                                    } else {
                                        $trade_value[$logistic_index][$key_data][$key][0] = '';
                                    }
                                };
                            }
                        }
                    }
                } else {
                    foreach ($datas as $key_data=>$data) {
                        foreach ($datas as $one_key=>$one_data) {
                            foreach ($one_data as $key=>$value) {
                                if ($one_key == $key_data) {
                                    if ($key_data == 0) {
                                        $trade_value[$logistic_index][$key_data][$key]['ba'] = '';
                                    } else {
                                        $trade_value[$logistic_index][$key_data][$key][0] = '';
                                    }
                                };
                            }
                        }
                    }
                }
            }
        }
        // End of edited by Ei Thandar Kyaw
        /* error check session */
        #excel data for sub_account
        for ($i=$ws_index; $i < count($worksheets); $i++) {
            (!empty($worksheets[$i][0][1]))? array_push($excel_subAcc_arr, $worksheets[$i][0][1]) : '';
        }
        #excel data for account
        for ($i=$ws_index; $i < count($worksheets); $i++) {

            #skip rows if donot have account_name
            ($worksheets[$i][0][2] == 'KPI')? $i += 4 : $i ;
            #skip rows if row is transaction
            ($worksheets[$i][0][0] == __('取引'))? $i += 1 : $i ;

            (!empty($worksheets[$i][0][2]))? array_push($excel_account_arr, $worksheets[$i][0][2]) : '';
        }

        $array_diff1 = array_diff($excel_account_arr, $org_account_arr);
        $array_diff2 = array_diff($org_account_arr, $excel_account_arr);
        $diff_acc_ba = array_merge($array_diff1, $array_diff2);

        $org_subAcc_arr = array_unique($org_subAcc_arr);
        $org_account_arr = array_unique($org_account_arr);

        $excel_subAcc_arr = array_unique($excel_subAcc_arr);
        $excel_account_arr = array_unique($excel_account_arr);
        #header check
        if (!empty($header_result)) {
            $notMatchErr = parent::getErrorMsg('SE022');
            $data = ['error'=>$notMatchErr];
            return $data;
        }
        #check sub_account_name
        elseif (count($org_subAcc_arr) != count($excel_subAcc_arr)) {
            $notMatchErr = parent::getErrorMsg('SE100', __("小勘定科目コード"));
            $data = ['error'=>$notMatchErr];
            return $data;
        }
        // #check account_name
        // else if(count($org_account_arr) != count($excel_account_arr)){

        // 	$notMatchErr = parent::getErrorMsg('SE100',__("勘定科目コード"));
        // 	$data = ['error'=>$notMatchErr];
        // 	return $data;
        // }
        #check 社内受払手数料 of ba_code
        // else if(!empty($diff_acc_ba)){
        // 	$notMatchErr = parent::getErrorMsg('SE100',__("社内受払手数料").__("の").__("事業領域"));
        // 	$data = ['error'=>$notMatchErr];
        // 	return $data;
        // }

        /*end error check session*/

        $trade_fill_date = $trade_data['trade_filling_date'];
        $trade_data_excel 	= [];

        $month = [
                    'kpi_unit',
                     $months[0],
                     $months[1],
                     $months[2],
                     $months[3],
                     $months[4],
                     $months[5],
                    '1st_half_total',
                     $months[6],
                     $months[7],
                     $months[8],
                     $months[9],
                     $months[10],
                     $months[11],
                    '2nd_half_total',
                    'sub_total',
                    'trade_filling_date',

                ];
        $colName = array("B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T");

        #show error 取引 value donot same excel and view
        foreach ($trade_value as $logistic_no => $value) {
            if ($logistic_no != '') {
                $logistic_arr [] = $logistic_no;
            }
        }

        $j = 0;
        for ($k=0; $k<= count($worksheets); $k++) {
            if ($worksheets[$k][0][0] == __('取引')) {
                if ($worksheets[$k][0][2] != $logistic_arr[$j]) {
                    $errormsg = "D".($k+7).__('取引');
                    $notMatchErr = parent::getErrorMsg('SE100', ($errormsg));
                    $data = ['error'=>$notMatchErr];
                    return $data;
                } else {
                    $j++;
                }
            }
        }
        $baCount = 0;
        $baMaxCount = 3;
        // $destination = $this->Session->read('DESTINATION');
        $destination = $this->getDestination();
        foreach ($trade_value as $logistic_index_no => $sub_acc_data) {
            foreach ($sub_acc_data as $sub_acc_id => $account_data) {
                if ($sub_acc_id == 2) {
                    //$accBa = array_key_last ($account_data);
                    $accBa = $this->array_key_last($account_data);
                }
                foreach ($account_data as $acc_id => $ba_data) {
                    if ($sub_acc_id == 2 && $acc_id == $accBa) {
                        foreach ($ba_data as $key => $ba_code) {
                            if ($key == 0) {
                                $baCount = sizeof($ba_data);
                                $baAddCount = $baMaxCount - $baCount;
                            }
                            if ($ba_code) {
                                $trade_value[$logistic_index_no][$sub_acc_id][$acc_id][$key] = $ba_code.'_view';
                            }
                        }
                        // add ba to 3
                        for ($i = 0; $i < $baAddCount; $i++) {
                            $trade_value[$logistic_index_no][$sub_acc_id][$acc_id][$baCount+$i] = '';
                        }
                    }
                }
            }
        }
        $worksheetsArr = array();
        foreach ($worksheets as $key=>$value) {
            if ($value[0][0] ==  __('取引')) {
                $worksheetsArr[$key]  = $worksheets[$key][0][2];
            }
        }
        
        $checkBa = array();
        $checkUserInput = array();
        $checkBa_UserInput = array();
        #excel of datas put ot save array
        $count = 1;
        foreach ($trade_value as $logistic_index_no => $sub_acc_data) {
            if ($logistic_index_no != '' && in_array($logistic_index_no, $worksheetsArr)) {
                $ws_index = array_search($logistic_index_no, $worksheetsArr) -3;
                foreach ($sub_acc_data as $sub_acc_id => $account_data) {
                    foreach ($account_data as $acc_id => $ba_data) {
                        foreach ($ba_data as $key => $ba_code) {
                            $ws_index++;
                            
                            if ($accBa == $acc_id) {
                                $home_payment_fee = $worksheets[$ws_index][0][2];
                                $user_input = str_replace(['社内受払手数料','(',')',' '], '', $home_payment_fee);
                                if (strstr($ba_code, '_view')) {
                                    $ba_code = array_search($worksheets[$ws_index][0][3], $destination).'_view/'.$user_input;
                                } else {
                                    $ba_code = array_search($worksheets[$ws_index][0][3], $destination).'/'.$user_input;
                                }
                                // if(strstr($ba_code, '_view')) $ba_code = array_search($worksheets[$ws_index][0][3], $destination).'_view';
                                // else $ba_code = array_search($worksheets[$ws_index][0][3], $destination);
                            }
                            
                            #skip row value if have sub_account total value
                            if ($worksheets[$ws_index][0][1] == '売上原価' ||
                            $worksheets[$ws_index][0][1] == '売上総利益') {
                                $ws_index++;
                            }
                            #skip row value
                            if ($worksheets[$ws_index][0][2] == '') {
                                $ws_index +=4;
                            }
                            $res_lock = 0;
                            for ($j=3; $j<= 18; $j++) {
                                if (is_string($worksheets[$ws_index][0][$j+1])) {
                                    #not numeric value show error
                                    foreach ($colName as $key => $col) {
                                        if ($key == $j) {
                                            $column[] = $col;
                                        }
                                    }
                                    $row[] = $ws_index+7;
                                    $errormsg = array(__("小数点以下2桁の9桁"),$column[0],$row[0]);
                                    $notMatchErr = parent::getErrorMsg('SE099', $errormsg);
                                    $data = ['error'=>$notMatchErr];
                                    return $data;
                                }
                                #社内受払手数料 of no have dest_ba_code, if  have filling value show error
                                elseif ($acc_id == Setting::INNER_PAY_ACCOUNT_CODE &&
                                        $ba_code == '' &&
                                        $worksheets[$ws_index][0][$j+1] != 0) {
                                    $row[] = $ws_index+7;
                                            
                                    $notMatchErr = parent::getErrorMsg('SE072', __("相手先")." ".__("は行で")." E".$row[0]);
                                    $data = ['error'=>$notMatchErr];
                                    return $data;
                                } else {
                                    $ws_value = $this->is_decimal3($worksheets[$ws_index][0][$j+1]);
                                    if (!$ws_value) {
                                        #not numeric value show error
                                        foreach ($colName as $key => $col) {
                                            if ($key == $j) {
                                                $column[] = $col;
                                            }
                                        }
                                        
                                        $row[] = $ws_index+7;
                                        $notMatchErr = parent::getErrorMsg('SE099', [__('10 digits with 3 digits decimal point'),++$column[0],$row[0]]);
                                        $data = ['error'=>$notMatchErr];
                                        return $data;
                                    }
                                    if ($accBa == $acc_id) {
                                        $checkBa[$logistic_index_no][$ws_index + 7] = $worksheets[$ws_index][0][3];
                                        $checkUserInput[$logistic_index_no][$ws_index + 7] = $worksheets[$ws_index][0][2];
                                        $BA_User = [$worksheets[$ws_index][0][3].",".$worksheets[$ws_index][0][2]];
                                        $checkBa_UserInput[$logistic_index_no][$ws_index + 7] = $BA_User;
                                    }
                                    if ($acc_id != '0000000000') {
                                        if ($j-3 != 0 && $j-3 != 7 && $j-3 != 14 && $j-3 != 15 && $year_month[$res_lock]) {
                                            $unit_salary[$month[($j-3)]] = 0;
                                            $res_lock++;
                                        } else {
                                            $unit_salary[$month[($j-3)]] = $worksheets[$ws_index][0][$j];
                                        }
                                    } else {
                                        $unit_salary[$month[($j-3)]] = $worksheets[$ws_index][0][$j];
                                    }
                                }
                            }
                            $unit_salary[$month[15]] = $trade_fill_date;
                            
                            if ($ba_code == '/') {
                                $ba_code = $ba_code.'_'.$count;
                                $count++;
                            }
                            $trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$ba_code] = $unit_salary;
                        }
                    }
                }
            }
        }
        // check duplicate ba in upload excel
        foreach ($checkBa_UserInput as $logisticIndex => $inputs) {
            $checkValue = array();
            
            foreach ($inputs as $key=>$value) {
                preg_match('#\((.*?)\)#', $value[0], $match);
                $matchKpi = str_replace(' ', '', $match[1]);
                
                $toCheck_Ba_Kpi = explode(',', $value[0])[0].$matchKpi;
                
                if (mb_strpos($value[0], '社内受払手数料') === false) {
                    $err = array(__('社内受払手数料'), "D".$key);
                    $notMatchErr = parent::getErrorMsg('SE118', $err);
                    $data = ['error'=>$notMatchErr];
                    
                    return $data;
                } elseif ($value[0] != 'Select BA,社内受払手数料(   )' && in_array($toCheck_Ba_Kpi, $checkValue) && $value[0] != '') {
                    $errMsg = explode(',', $value[0]);
                    $text = explode('(', $errMsg[1]);
                    $payFee = substr($text[1], 0, -1);
                    $err = array(__($payFee), __($errMsg[0]), "E".$key);
                    $notMatchErr = parent::getErrorMsg('SE105', $err);
                    $data = ['error'=>$notMatchErr];
                        
                    return $data;
                }
                if ($toCheck_Ba_Kpi != 'Select BA') {
                    $checkValue[] = $toCheck_Ba_Kpi;
                }
            }
        }
        /*foreach ($trade_data_excel as $logistic_index_no => $sub_acc_data) {
        	foreach($sub_acc_data as $sub_acc_id => $account_data){
        		foreach ($account_data as $acc_id => $ba_data) {
        			$baCount = sizeof($ba_data);
        			foreach ($ba_data as $key => $ba_code) {
                        
        				if($accBa == $acc_id){
        					// value are zero, remove from array
        					if($ba_code['1st_half_total'] == 0 && $ba_code['2nd_half_total'] == 0){
        						if(!strstr($key, '_view')) {
        							if($ba_code['kpi_unit'] == 'Select BA' && $baCount == 1){

        							}
        							else unset($trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key]);
        						}
        						if(strstr($key, '_view')){
        							$key = substr($key, 0, -5);
        							$trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key] = $ba_code;
        							$trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key]['kpi_unit'] = '';
        							unset($trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key.'_view']);
        						}else{
        							$trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key]['kpi_unit'] = '';
        						}

        					}else{
        						if(strstr($key, '_view')){
        							$key = substr($key, 0, -5);
        							$trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key] = $ba_code;
        							$trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key]['kpi_unit'] = '';
        							unset($trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key.'_view']);
        						}else{
        							$trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key]['kpi_unit'] = '';
        						}
        					}
        					if($ba_code['kpi_unit'] == 'Select BA'){
        						unset($trade_data_excel[$logistic_index_no][$sub_acc_id][$acc_id][$key]);
        					}
                            
        				}
        			}
        		}
        	}
        }*/
        
        $data = ['error'=>$notMatchErr,'success'=>$trade_data_excel];
        return $data;
    }

    public function array_key_first(array $array)
    {
        foreach ($array as $key => $value) {
            return $key;
        }
    }
    public function array_key_last(array $array)
    {
        end($array);
        return key($array);
    }

    /**
     * Check decimal validation
     *
     * @author Nu Nu Lwin (22/11/2021)
     * @param worksheets
     * @return boolean
     */
    public function is_decimal3($val)
    {
        $decimalOnly = preg_match('/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/', $val);
        
        return ($decimalOnly == 1)? true : false;
    }
}
