<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BrmBudgetPlan');
/**
 * TransactionPlans Controller
 *
 * @property TransactionPlan $TransactionPlan
 * @property PaginatorComponent $Paginator
 */
class BrmTradingPlanController extends AppController
{

/**
 * Components
 *
 * @var array
 */
    public $components = array('Session','PhpExcel.PhpExcel');
    public $uses = array('BrmAccount', 'BrmAccountSetup', 'BrmTermDeadline', 'BrmTerm', 'BrmLogistic', 'BrmExpected', 'BrmBudgetPrime', 'BrmActualResultSummary', 'Layer', 'BrmForecastSummary', 'BrmBudgetSummary', 'BrmBudgetPlan', 'BrmBudgetApprove');
    public $helpers = array('Html', 'Form', 'Csv');

    public function beforeFilter() {
        $Common = New CommonController();
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];
        
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);

        $this->Session->write('PERMISSIONS', $permissions);
        $layers = array_keys($permissions['index']['layers']);
        
        if((!in_array($layer_code, $layers)) || ($layer_code == "" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        } 
    }
    public function index() {
        $this->layout = 'phase_3_menu';
        $Common = new CommonController();
        $login_id = $this->Session->read('LOGIN_ID');
        $term_id = $this->Session->read('TERM_ID');
        $term_name = $this->Session->read('TERM_NAME');
        $top_layer = $this->Session->read('TOP_LAYER');
        $top_layer_code = explode(',', $top_layer)[1];
        $hq_id = $this->Session->read('HEAD_DEPT_ID');
        $hq_name = $this->Session->read('HEAD_DEPT_NAME');
        $layer_code_name = $this->Session->read('BUDGET_BA_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $language = $this->Session->read('Config.language');
        $layer_type_name = $this->Session->read('LayerTypeData')[Setting::LAYER_SETTING['bottomLayer']];
        $year = $_GET['year']; #get year from url
        $sale_acc = Setting::SALE_ACCOUNT;#sale trading account
        $start_year = substr($term_name, 0, 4);#get 1st year(budget start year) of selected_term
        $months = $Common->get12Month($term_id);#get 12 months list (eg: "1月", "2月", "3月", "4月", ...)
        #Get deadline date from brm_term_deadlines
        $deadline_date = $this->deadLineDate($term_id, $top_layer_code, $layer_code);
        #Get forecast period(eg: 2020-05) to show actual result data to this period
        $forecast_period = $this->forecastPeriod($term_id, $top_layer_code);
        #get budget start month
        $start_month = $Common->getMonth($start_year, $term_id, 'start');
        #get budget end month
        $end_month = $Common->getMonth($start_year, $term_id, 'end');
        #get month range from start to end
        $ym_range = $this->getYmRange($months, $start_month, $end_month);
        #get active logistics list from brmlogistic
        $active_logistics = $this->getActiveLogi($year, $layer_code);
        #get logistic list
        $logistic_data = array_unique(array_column($active_logistics, 'index_name'));
        #result lock col disabled or not
        $result_disabled = ($year == $start_year) ? $this->resultDisabled($year, $months, $start_month, $forecast_period) : '';
        #distinguish forecast or budget
        $modelName = ($year == $start_year) ? 'BrmExpected' : 'BrmBudgetPrime';
        $sumModelName = ($year == $start_year) ? 'BrmForecastSummary' : 'BrmBudgetSummary';
        #get destination list
        $destination = $this->getDestination();
        #Get related account datas from BrmAccountSetup, BrmSaccount, BrmAccount
        $account_datas = $this->getAccountData($year, $top_layer_code, $sale_acc[0]);
        $errormsg = ($account_datas['errorchk']) ? $account_datas['errormsg'] : "";
        unset($account_datas['errorchk']);
        unset($account_datas['errormsg']);
        #get tp data
        $tp_data = $this->getTradingData($account_datas, $active_logistics, $months, $modelName, $months, $term_id, $year, $layer_code, $sale_acc, $start_year, $start_month, $end_month, $forecast_period, $ym_range, $login_id);
        $trade_data = $tp_data['trade_data'];
        $table_months = $tp_data['table_months'];
        $approved_BA = $tp_data['approved_BA'];
        $this->Session->write('TRADE_DATA', $trade_data);
        #set permission
        $permission = $this->Session->read('PERMISSIONS');
        $create_limit = $permission['save']['limit'];
        # action or read only
        $page = ($create_limit >= 0) ? 'Enabled' : 'Disabled';
        # for excel disable
        $approved = ($approved_BA) ? 'Approved' : '';
        $disable = $approved.'_'.$page;
        $this->Session->write('DISABLED', $disable);
        $this->set(compact('term_id', 'term_name', 'layer_code', 'layer_code_name', 'deadline_date', 'trade_data', 'months', 'logistic_data', 'table_months', 'approved_BA', 'page', 'result_disabled', 'destination', 'errormsg', 'modelName', 'sumModelName', 'layer_type_name', 'disable'));
    }
    public function saveTradingData() {
        if ($this->request->is('post')) {
            $login_id = $this->Session->read('LOGIN_ID');
            $budget_term = $this->Session->read('TERM_NAME');
            $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');
            $hlayer_code = $this->Session->read('HEAD_DEPT_CODE');
            $ba_name = $this->Session->read('BUDGET_BA_NAME');
            $requestData = $this->request->data;
            $term_id = $requestData['term_id'];
            $year = $requestData['year'];
            $layer_code = $requestData['layer'];
            $trade_filling_date = $requestData['filling_date'];
            $modelName = $requestData['model_name'];
            $sumModelName = $requestData['sum_model_name'];
            $delete_id = [];
            $logi = [];
            #destination == null => if 0 -> value == 0 and 1 -> value != 0
            $null_desti_arr = array('0', '1');
            $unit = 1000;
            /*get logistic index list name from tbl_logistic table*/
            $logi_list      = explode(",",$requestData['logi_list']);
            $logi_name_list = $this->BrmLogistic->find('list',array(
                'fields' => array('BrmLogistic.index_name'),
                'conditions' => array(
                    'BrmLogistic.target_year' => $year,
                    'BrmLogistic.layer_code'     => $layer_code,
                    'BrmLogistic.flag'        => 1
                )
            ));
            
            $final_logi_list = array_diff($logi_list,$logi_name_list);
            if(!empty($final_logi_list)) {
                $logi_result = [];
                foreach($final_logi_list as $logi) {
                    $param = array();             
                    $param["target_year"]     = $year;
                    $param["layer_code"]      = $layer_code;
                    $param["index_no"]        = null;
                    $param["index_name"]      = $logi;
                    $param["logistic_order"]  = 1000;
                    $param["flag"]            = 1;
                    $param["created_by"]      = $this->Session->read('LOGIN_ID');
                    $param["updated_by"]      = $this->Session->read('LOGIN_ID');
                    $param["created_date"]    = date("Y-m-d H:i:s");
                    $param["updated_date"]    = date("Y-m-d H:i:s");
                    array_push($logi_result,$param);
                }
                #save data in tbl_logistic table
                $this->BrmLogistic->create();
                $this->BrmLogistic->saveAll($logi_result); 
            }
            foreach ($requestData['trade'] as $trade) {
                # total count of tables to show success msg
                if (!in_array($trade['logistic_index'], $logi)) array_push($logi, $trade['logistic_index']);
                foreach ($trade['data'] as $brm_account_id => $account_datas) {
                    $delete_id[] = $brm_account_id;
                    foreach ($account_datas as $account_code => $months_amt) {
                        $tmp['brm_term_id'] = $term_id;
                        $tmp['target_year'] = $year;
                        $tmp['layer_code'] = $layer_code;
                        $tmp['brm_account_id']  = $brm_account_id;
                        $tmp['account_code'] = $account_code;
                        $tmp['logistic_index_no'] = $trade['logistic_index'];
                        $tmp['trade_filling_date']  = $trade_filling_date;
                        $tmp['created_by']      = $login_id;
                        $tmp['updated_by']      = $login_id;
                        $tmp['flag'] = 1;
                        
                        if ($account_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                            foreach ($months_amt as $mkey => $mvalue) {
                                #destination == null => if 0 -> value == 0 and 1 -> value != 0
                                $destination = in_array($mvalue['destination'], $null_desti_arr) ? '' : $mvalue['destination'];
                                $diff_ba = explode('/', $destination);
                                $user_input  = $mvalue['user_input'];
                                $tmp['destination'] = $diff_ba[0];
                                $tmp['kpi_unit'] = $user_input;
                                $months_amts = array_values(preg_replace("/[^-0-9\.]/", "", $mvalue));
                                $months_amts = array_splice($months_amts, 2);
                                for ($i = 0; $i < count($months_amts); $i++) {
                                    $key = 'month_'.($i+1).'_amt';
                                    $tmp[$key]     = $months_amts[$i]*$unit;
                                }
                                # assign to newly save data
                                $save_data[] = array_merge($tmp);
                            }
                        }else {
                            if ($account_code == Setting::KPI_CODE) {
                                $tmp['kpi_unit'] = $trade['kpi_unit'];
                                $tmp['destination'] = null;
                            }else {
                                $tmp['kpi_unit'] = null;
                            }
                            $months_amts = array_values(preg_replace("/[^-0-9\.]/", "", $months_amt));
                            for ($i = 0; $i < count($months_amts); $i++) {
                                $key = 'month_'.($i+1).'_amt';
                                $tmp[$key]     = $months_amts[$i]*$unit;
                            }
                            # assign to newly save data
                            $save_data[] = array_merge($tmp);
                        }
                    }
                }
            }
            
            $BPData = new BrmBudgetPlanController();
            if (!empty($save_data)) {
                $attachDB = $this->$modelName->getDataSource();
                $sumDB = $this->$sumModelName->getDataSource();

                try {
                    $attachDB->begin();
                     $sumDB->begin(); 
                    
                    # Delete old data
                    $this->$modelName->deleteAll(array(
                        'brm_term_id' => $term_id,
                        'target_year' => $year,
                        'layer_code' => $layer_code,
                        'brm_account_id' => array_unique($delete_id)
                    ), false);
                    
                    # Save new data
                    if (!empty($save_data)) {
                        $this->$modelName->saveAll($save_data);
                    }

                    $this->$sumModelName->updateSummaryData($layer_code,$term_id,$year); 

                    $filling_date = '';
                    $calculate_tax = true;
                    
                    $manual_tax_ba = Setting::BA_BUDGET_TAX;
                    if (!in_array($layer_code, $manual_tax_ba)) {
                        $calculate_tax = $BPData->updateTaxAmount($term_id, $budget_term, $hlayer_code, $head_dept_name, $layer_code, $ba_name, $year, $login_id, $modelName);
                    }
                   
                    if ($calculate_tax) {
                        $attachDB->commit();
                        $sumDB->commit(); 
                        
                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                        $successMsg = parent::getSuccessMsg('SS026', count($logi));
                        $this->Flash->set($successMsg, array("key"=>"UserSuccess"));
                    } else {
                        $attachDB->rollback();
                        $sumDB->rollback(); 
                        
                        $errorMsg = parent::getErrorMsg('SE003');
                        $this->Flash->set($errorMsg, array("key"=>"UserError"));

                        CakeLog::write('debug', 'Budget data cannot be saved!. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }


                } catch (Exception $e) {
                    $attachDB->rollback();
                    $sumDB->rollback(); 
                    
                    $errorMsg = parent::getErrorMsg('SE003');
                    $this->Flash->set($errorMsg, array("key"=>"UserError"));

                    CakeLog::write('debug', 'data cannot be saved!. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
            } else {
                
                $errorMsg = parent::getErrorMsg('SE017', 'Save');
                $this->Flash->set($errorMsg, array("key"=>"UserError"));
            }
            $this->redirect(array('controller'=>'BrmTradingPlan/?year='.$year,'action'=>'index'));
        }
    }
    
    public function getTradingData($account_datas, $logistics, $months, $modelName, $table_months, $term_id, $year, $layer_code, $sale_acc, $start_year, $start_month, $end_month, $forecast_period, $ym_range, $login_id) {
        if(empty($logistics)) {
            $logistics[0]['index_name'] = '';
        }
        foreach ($logistics as $value) {
            $active_logistics[$value['index_name']][$value['id']] = $value['index_no'];
            if ($value['index_name'] == '取引無し') {
                $active_logistics[$value['index_name']][] = '';
            }
        }
        array_splice($table_months, 6, 0, 'first_half');
        array_push($table_months, 'second_half');
        array_push($table_months, 'whole_total');
        #month and assign amt 0 for result
        $preResult = array_combine(array_values($ym_range), array_fill(0, 12, 0));

        $trade_filling_date = date("Y/m/d");
        $trade_data = [];
        if(!$account_datas['errorchk']) {
            foreach ($account_datas as $account) {
                $data = call_user_func_array('array_merge', $account);
                $acc_name = $account['BrmAccount']['name_jp'];
                $sub_name = $data['name_jp'];
                $sub_name_total = $data['name_jp'];
                $acc_code = $data['account_code'];
                $acc_id = $data['brm_account_id'];
                foreach ($active_logistics as $index_name => $index_no) {
                    $trade_data['trade_filling_date'] = $trade_filling_date;
                    $trading_data = $this->getTPData($modelName, $months, $term_id, $year, $layer_code, $acc_id, $acc_code, $acc_name, $sub_name, $index_name, $table_months, $index_no, $start_year, $start_month, $end_month, $forecast_period, $ym_range, $preResult);
                    $chk_br[$index_name][] = $trading_data[0]['BrmExpected']['chk_br'];
                    #disabled logistics(actual amount existing)
                    $disabled_logistics = $trading_data[0]['BrmExpected'][$index_name];
                    if($disabled_logistics != '') $trade_data['disabled_logi'][$index_name] = $trading_data[0]['BrmExpected'][$index_name];
                    foreach ($trading_data as $trade) {
                        $sub_name = $data['name_jp'];
                        $destination = $trade[$modelName]['destination'];
                        $user_input  = $trade[$modelName]['kpi_unit'];
                        $sub_name_total = $data['name_jp'];
                        $sub_name = (!empty($user_input)) ? $sub_name.'/#/'.$user_input : $sub_name;
                        
                        foreach ($table_months as $each_month) {
                            $trade_data['record'][$index_name]['trading'][$acc_name]['data'][$sub_name]['amount'][$destination][$each_month] += $trade[0][$each_month];
                            $trade_data['record'][$index_name]['trading'][$acc_name]['total']['amount'][$each_month] += $trade[0][$each_month];
                            $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount'][$each_month] += $trade[0][$each_month];
                            $trade_data['total'][$acc_name]['total']['amount'][$each_month] += $trade[0][$each_month];
                            $each_gross_prof[$index_name][$each_month] += $trade[0][$each_month];
                        }
                        $trade_data['record'][$index_name]['trading'][$acc_name]['data'][$sub_name]['code'] = $acc_code;
                        $trade_data['record'][$index_name]['trading'][$acc_name]['data'][$sub_name]['acc_id'] = $acc_id;
                        $trade_data['record'][$index_name]['trading'][$acc_name]['total']['acc_id'] = $acc_id;
                        $trade_data['total'][$acc_name]['data'][$sub_name_total]['code'] = $acc_code;
                        $trade_data['total'][$acc_name]['data'][$sub_name_total]['acc_id'] = $acc_id;
                        $trade_data['total'][$acc_name]['total']['acc_id'] = $acc_id;
                    }
                }
                if($year == $start_year) {
                    $this->BrmActualResultSummary->virtualFields['result_total'] = 'SUM(amount)/1000';
                    $aresultData = $this->BrmActualResultSummary->find('list', array(
                        'fields' => array('target_month','result_total'),
                        'conditions' => array(
                            'layer_code' => $layer_code,
                            'account_code' => $acc_code,
                            'target_month >=' => $start_month,
                            'target_month <=' => $forecast_period
                        ),
                        'group' => 'target_month',
                        'order' => 'target_month',
                    ));
                    
                    $eachMonth = $start_month;
                    $month_cnt = 0;
                    while ($eachMonth <= $forecast_period) {
                        $monthlyResult = (!empty($aresultData[$eachMonth])) ? $aresultData[$eachMonth] : 0;
                        #get difference of forecast and result amount
                        $resultDiff = $monthlyResult - $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount'][$months[$month_cnt]];
                        $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount'][$months[$month_cnt]] += $resultDiff;
                        $trade_data['total'][$acc_name]['total']['amount'][$months[$month_cnt]] += $resultDiff;
                        if($month_cnt < 6) {
                            $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount']['first_half'] += $resultDiff;
                            $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount']['whole_total'] += $resultDiff;

                            $trade_data['total'][$acc_name]['total']['amount']['first_half'] += $resultDiff;
                            $trade_data['total'][$acc_name]['total']['amount']['whole_total'] += $resultDiff;
                        }else {
                            $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount']['second_half'] += $resultDiff;
                            $trade_data['total'][$acc_name]['data'][$sub_name_total]['amount']['whole_total'] += $resultDiff;

                            $trade_data['total'][$acc_name]['total']['amount']['second_half'] += $resultDiff;
                            $trade_data['total'][$acc_name]['total']['amount']['whole_total'] += $resultDiff;
                        }
                        #increase month & month count
                        $eachMonth =  date("Y-m", strtotime($eachMonth. "last day of + 1 Month"));
                        $month_cnt++;
                    }
                }
            }
            
            $trade_data['total'][$sale_acc[1]]['total']['amount'] = $this->getGrossProfit($trade_data['total'][$sale_acc[0][0]]['total']['amount'], $trade_data['total'][$sale_acc[0][1]]['total']['amount'], $table_months);
            #prepare KPI array
            foreach ($each_gross_prof as $index_name => $value) {
                $trade_data['record'][$index_name]['trading'][$sale_acc[1]]['total']['amount'] = $value;
                
                $acc_code = Setting::KPI_CODE;
                $acc_id = 0;
                $trading_data = $this->getTPData($modelName, $months, $term_id, $year, $layer_code, $acc_id, $acc_code, $acc_name, $sub_name, $index_name, $table_months, $index_no, $start_year, $start_month, $end_month, $forecast_period, $ym_range, $preResult);
                
                $kpi_unit = $tradingData[0][$modelName]['kpi_unit'];
                $trade_data['record'][$index_name]['kpi'][$kpi_unit]['amount'] = $trading_data[0][0];
                $trade_data['record'][$index_name]['kpi'][$kpi_unit]['code'] = $acc_code;
                $trade_data['record'][$index_name]['kpi'][$kpi_unit]['acc_id'] = $acc_id;
                #no amt(result and budget) in all account
                if(count(array_filter($chk_br[$index_name])) == count($account_datas)) {
                    unset($trade_data['record'][$index_name]);
                }
            }
        }
        
        $logistic_data = array_keys($active_logistics);
        $code = array('0', $layer_code);
        $approved_BA =  $this->BrmBudgetApprove->find('first', array(
            'conditions' => array(
                'BrmBudgetApprove.brm_term_id' => $term_id,
                'BrmBudgetApprove.layer_code' => $code,
                'BrmBudgetApprove.flag' => '2'
            ),
            'fields' => array('BrmBudgetApprove.id')
        ));
        $tr_data = array(
            'trade_data' => $trade_data,
            'table_months' => $table_months,
            'logistic_data' => $logistic_data,
            'approved_BA' => (!empty($approved_BA)) ? true : false
        );
        $cache_name = 'trading_plan_'.$term_id.'_'.$year.'_'.$layer_code.'_'.$login_id;
        Cache::write($cache_name, $tr_data);
        return $tr_data;
    }
    public function getGrossProfit($acc_one, $acc_two, $table_months) {
        $acc_three = array_map(function () {
            return array_sum(func_get_args());
        }, $acc_one, $acc_two);
        return array_combine($table_months, $acc_three);
    }
    public function getYmRange($months, $start_month, $end_month) {
        $eachMonth = $start_month;
        while ($eachMonth <= $end_month) {
            $m_arr[] = $eachMonth;
            #increase month
            $eachMonth =  date("Y-m", strtotime($eachMonth. "last day of + 1 Month"));
        }
        $months = array_splice($months, 0, count($m_arr));
        return array_combine($months, $m_arr);
    }
    public function getTPData($modelName, $months, $term_id, $year, $layer_code, $account_id, $account_code, $account_name, $sub_name, $index_name, $table_months, $transaction_key, $start_year, $start_month, $end_month, $forecast_period, $ym_range, $preResult) {
        #trading data
        $trading_datas = $this->$modelName->find('all', array(
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
                $modelName.'.account_code','logistic_index_no','destination',
                'brm_account_id','kpi_unit','trade_filling_date'
            ),
            'conditions' => array(
                'brm_term_id' => $term_id,
                'target_year' => $year,
                'layer_code' => $layer_code,
                $modelName.'.account_code' => $account_code,
                'logistic_index_no' => $index_name,
                'trade_filling_date <>' => '',
                $modelName.'.flag' => 1,
                
            )
        ));
        
        if(empty($trading_datas)) {
            foreach ($table_months as $m) {
                $trading_datas[0][0][$m] = 0;
            }
            $trading_datas[0][$modelName]['account_code'] = $account_code;
            $trading_datas[0][$modelName]['logistic_index_no'] = $index_name;
            $trading_datas[0][$modelName]['destination'] = 'NONEDESTI';
            $trading_datas[0][$modelName]['kpi_unit'] = '';
            $trading_datas[0][$modelName]['chk_br'] = $index_name;
        }
        #result data for forecast month
        if($year == $start_year) {
            $this->BrmActualResultSummary->virtualFields['total_amount'] = 'SUM(amount)/1000';
            if($account_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                $aresultDataByIndexes = $this->BrmActualResultSummary->find('list', array(
                    'fields' => array('target_month','total_amount','destination_code'),
                    'conditions' => array(
                        'layer_code' => $layer_code,
                        'account_code' => $account_code,
                        'target_month >=' => $start_month,
                        'target_month <=' => $forecast_period,
                        'transaction_key' => $transaction_key
                    ),
                    'group' => 'target_month, destination_code',
                    'order' => 'target_month, destination_code',
                ));
            }else {
                $aresultDataByIndexes['0'] = $this->BrmActualResultSummary->find('list', array(
                    'fields' => array('target_month','total_amount'),
                    'conditions' => array(
                        'layer_code' => $layer_code,
                        'account_code' => $account_code,
                        'target_month >=' => $start_month,
                        'target_month <=' => $forecast_period,
                        'transaction_key' => $transaction_key
                    ),
                    'group' => 'target_month',
                    'order' => 'target_month',
                ));
            }
    
            if(!empty($aresultDataByIndexes)) {
                unset($trading_datas[0][$modelName]['chk_br']);
                $trading_datas[0][$modelName][$index_name] = 'disabled';
                foreach ($aresultDataByIndexes as $destination => $resultamt) {
                    $trading_datas[0][$modelName]['disabled_destination'] = ($destination != '') ? 'disabled' : '';
                    $trading_datas[0][$modelName]['destination'] = ($destination != '') ? $destination : 'NONEDESTI';
                    $month_cnt = 0;
                    $resultamt = array_replace($preResult, $resultamt);
                    $resultamt = array_combine(array_keys($ym_range), $resultamt);
                    
                    foreach ($trading_datas[0][0] as $month => $amount) {
                        if($ym_range[$month] <= $forecast_period) {
                            $amount = $resultamt[$month] - $amount;
                            $trading_datas[0][0][$month] += $amount;
                            if($month_cnt < 6) {
                                $trading_datas[0][0]['first_half'] += $amount;
                                $trading_datas[0][0]['whole_total'] += $amount;
                            }elseif($month_cnt > 6 && $month_cnt < 13) {
                                $trading_datas[0][0]['second_half'] += $amount;
                                $trading_datas[0][0]['whole_total'] += $amount;
                            }
                        }else {
                            if($month_cnt < 6) {
                                $trading_datas[0][0]['first_half'] += $amount;
                                $trading_datas[0][0]['whole_total'] += $amount;
                            }elseif($month_cnt > 6 && $month_cnt < 13) {
                                $trading_datas[0][0]['second_half'] += $amount;
                                $trading_datas[0][0]['whole_total'] += $amount;
                            }
                        }
                        $month_cnt++;
                    }
                }
            }
        }
        return $trading_datas;
    }
    public function getAccountData($year, $top_layer_code, $sale_acc) {
        $account_datas = $this->BrmAccountSetup->find('all', array(
            'fields' => array(
                'BrmAccount.name_jp','BrmSaccount.name_jp','BrmSaccount.account_code','BrmAccountSetup.brm_account_id'
            ),
            'conditions' => array(
                'BrmAccountSetup.target_year' => $year,
                'BrmAccountSetup.hlayer_code' => $top_layer_code,
                'BrmAccountSetup.flag' => 1,
                'BrmAccountSetup.brm_saccount_id <>' => 0,
                'BrmAccount.name_jp' => $sale_acc
            ),
            'order' => array('BrmAccountSetup.brm_account_id ASC','BrmAccountSetup.order ASC', 'BrmAccountSetup.sub_order ASC')
        ));
        $account_datas['errorchk'] = false;
        if(count($account_datas) < 2) {
            $account_datas['errorchk'] = true;
            $account_datas['errormsg'] =  __('表示するアカウントはありません。');
        }
        return $account_datas;
    }
    public function deadLineDate($term_id, $top_layer_code, $layer_code) {
        $hq_deadline = $this->BrmTermDeadline->find('list', array(
            'fields' => array('hlayer_code','deadline_date'),
            'conditions' => array(
                'brm_term_id' => $term_id, 
                'hlayer_code' => $top_layer_code
            )
        ))[$top_layer_code];
        $deadline_date = ($hq_deadline == '0000-00-00 00:00:00' || empty($hq_deadline)) ? '' : date("Y/m/d", strtotime($hq_deadline));
        if (empty($layer_code) || empty($top_layer_code)) {
            $top_layer_type     = Setting::LAYER_SETTING['topLayer'];
            $layer_type_name = $_SESSION['LayerTypeData'][$top_layer_type];
            
            if(empty($deadline_date)) $errorMsg = parent::getErrorMsg('SE132', $layer_type_name);
            elseif(empty($layer_code)) $errorMsg = parent::getErrorMsg('SE065');
            
            $this->Flash->error($errorMsg);
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }else {
            return $deadline_date;
        }
    }
    public function forecastPeriod($term_id, $top_layer_code = '') {
        $forecast_period = $this->BrmTerm->find('list', array(
            'fields' => array('BrmTerm.forecast_period'),
            'conditions' => array(
                'BrmTerm.id' => $term_id,
                'BrmTerm.flag' => 1
            )
        ))[$term_id];
        return $forecast_period;
    }
    public function getActiveLogi($year, $layer_code) {
        $active_logistics = array_column($this->BrmLogistic->find('all', array(
            'fields' => array('id', 'index_no', 'index_name'),
            'conditions' => array(
                'target_year' => $year,
                'layer_code' => $layer_code,
                'flag' => 1
            ),
            'order' => 'BrmLogistic.logistic_order ASC','BrmLogistic.index_name ASC'
        )), 'BrmLogistic');

        return $active_logistics;
    }
    public function getDestination() {
        $this->Layer->virtualFields['name'] = 'CONCAT(Layer.Layer_code, IF(Layer.name_en = "", "", "/"), Layer.name_en)';
        $name = $_SESSION['Config']['language'] == 'eng' ? 'Layer.name' : 'Layer.code_name';
        $today = date("Y-m-d");
        $destination = [];
        $list = array_column($this->Layer->find('all', array(
            'conditions' => array(
                'Layer.flag' => 1,
                'LayerType.type_order' => Setting::LAYER_SETTING['bottomLayer']
            ),
            'fields' => array('Layer.layer_code', $name)
        )), 'Layer');
        
        $code_list = array_column($list, 'layer_code');
        $name_list = array_column($list, 'name');
        $destination = array_combine($code_list, $name_list);

        return $destination;
    }
    public function resultDisabled($year, $months, $start_month, $forecast_period) {
        $re_mth = $this->getYmRange($months, $start_month, $forecast_period);
        $result_col = array_combine(array_keys($re_mth), array_fill(0, count($re_mth), 'disabled'));
        return $result_col;
    }
    public function downloadExcelData() {
        $this->layout = null;
        $this->autoLayout = false;
        $budget_term = $this->Session->read('TERM_NAME');
        $login_id = $this->Session->read('LOGIN_ID');
        #get year from request
        $year = $this->request->data['year'];
        $layer_code = $this->request->data['layer_code'];
        $term_id = $this->request->data['term_id'];
        $disable = explode('_', $this->request->data['disabled_excel_row']);
        $PHPExcel = $this->PhpExcel;
        $save_into_tmp = false;
        $tmpFileName = 'TradingPlan';
        
        #clear session due to one time download
        unset($_SESSION['objworksheet']);
        $this->DownloadExcel($term_id, $budget_term, $layer_code, $year, $login_id, $tmpFileName, $PHPExcel, $save_into_tmp, $disable);

        $this->redirect(array('controller'=>'BrmTradingPlan/?year='.$year.'&'.$requestData['formType'],'action'=>'index'));
    }
    public function DownloadExcel($term_id, $budget_term, $ba_code, $year, $login_id, $file_name, $PHPExcel, $save_into_tmp, $disable=null, $hq_name=null, $one_time_download=false) {   
        $Common = new CommonController;
        $Months = $Common->get12Month($term_id);
        #Get data from cache
        $cache_name = 'trading_plan_'.$term_id.'_'.$year.'_'.(explode('/', $ba_code))[0].'_'.$login_id;
        $cache_dta = Cache::read($cache_name);
        $layer_code = explode('/', $ba_code)[0];
        if ($disable[0] == 'Approved' || $disable[1] == 'Disabled') {
            $disable_color = 'd9d9d9';
        } else {
            $disable_color = 'D5F4FF';
        }
        $color_disabled = 'd9d9d9';
        $trade_data = $cache_dta['trade_data'];
        $logistic_data = $cache_dta['logistic_data'];
        $table_months = $cache_dta['table_months'];
        $disabled_logi = array_keys($trade_data['disabled_logi']);
        $start_year = substr($budget_term, 0, 4);
        $start_month = $Common->getMonth($start_year, $term_id, 'start');
        $forecast_period = $this->forecastPeriod($term_id);
        $year_month = array_keys($this->getYmRange($Months, $start_month, $forecast_period));
        
        $destination = $this->getDestination();#to add empty logistic tables     
        $year_arr = explode('~', $budget_term);#get total years
        $sale_acc = Setting::SALE_ACCOUNT;
        $layer_type_name = $_SESSION['LayerTypeData'][Setting::LAYER_SETTING['bottomLayer']];
        if(empty($_SESSION['objworksheet'])){
            $objWorkSheet = $PHPExcel->createWorksheet()->setDefaultFont('Calibri', 12);
            $_SESSION['objworksheet'] = $objWorkSheet;            
            $objWorkSheet->setActiveSheetIndex(0);
            $objWorkSheet->getActiveSheet()->setTitle('DestinationList');
            #lock for excel file - not allow to edit for DestinationList sheet
            $objWorkSheet->getActiveSheet()->getProtection()->setPassword('*****');
            $objWorkSheet->getActiveSheet()->getProtection()->setSheet(true);
            #lock to insert new column and row
            $objWorkSheet->getActiveSheet()->getProtection()->setInsertRows(true);
            $objWorkSheet->getActiveSheet()->getProtection()->setInsertColumns(true);
            # list of destination for dropdown in new sheet
            $i=0;
            foreach ($destination as $ba => $dest) {
                $i++;
                $objWorkSheet->getActiveSheet()->setCellValue('A'.$i, $dest);
            }
            $objWorkSheet->getSheetByName('DestinationList')->setSheetState(PHPExcel_Worksheet::SHEETSTATE_VERYHIDDEN);
            $objWorkSheet->createSheet();           
        }else{
            $objWorkSheet = $_SESSION['objworksheet'];
            $objWorkSheet->createSheet();
        }
        if($one_time_download){
            #to download excel file
            if($_SESSION['count_years'] == 1){
                $_SESSION['total_years'] = 3;
            }else{
                $_SESSION['total_years'] = 3*(($year_arr[1] - $year_arr[0])+1);
            }
            $active_index = empty($_SESSION['active_index'])?1:$_SESSION['active_index'];
            $_SESSION['active_index'] = $active_index+1;            
        }else{
            $active_index = 1;
        }
        
        $objWorkSheet->setActiveSheetIndex($active_index);
        #set sheet name for one time download and others
        if($one_time_download){
            if(count($file_name) == 2){
                #get sheet name
                $file_name = $file_name[1];
            }
            $objWorkSheet->getActiveSheet()->setTitle($file_name);
        }else{
            $objWorkSheet->getActiveSheet()->setTitle('TradingPlan');            
        }
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
        $sheet->setCellValue('Q4', $layer_type_name.' : ');
        $sheet->setCellValue('S4', $ba_code);
        $sheet->setCellValue('A3', $year.__('年度 取引計画'));
        
        $sheet->setCellValue('S7', substr($year, -2).__('年度下期'));
        $sheet->setCellValue('T7', substr($year, -2).__('年度').__('年間'));
        $sheet->setCellValue('B8', substr($year, -2).__("年度 取引計画").__('（単位：千円）'));
        $colName = array("F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T");
        
        foreach ($Months as $monthK=>$monthV) {
            if ($monthK == 6) {
                $sheet->setCellValue('L7', substr($year, -2).__("年度上期"));
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
                if ($sub_acc_name != $sale_acc[1]) {
                    $have[$sub_acc_name] = $row;
                    $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                } else {
                    $haves[$sale_acc[1]] = $row;
                    if (count($have) < 2) {
                        $subname = array_keys($have)[0];
                        $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have[$subname]).')');
                    } else {
                        $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have[$sale_acc[0][0]]).'+'.$colName[$index].($have[$sale_acc[0][1]]).')');
                    }
                }
                #set formula without result period for sub account
                if ($year == $start_year && strpos($subacc_mnth, '月') && $year_month[$sub_cnt_ym] != '') {
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
                    if ($year == $start_year && strpos($acc_mnth, '月') && $year_month[$acc_cnt_ym] != '') {
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
        
        if (empty($logistic_data) && !$save_into_tmp) {
            $errorMsg = parent::getErrorMsg('SE104');
            $this->Flash->set($errorMsg, array("key"=>"UserError"));
            $this->redirect(array('controller'=>'BrmTradingPlan/?year='.$year,'action'=>'index'));
        }
        $exit_key = array_keys($trade_data['record']);
        $not_exit_key = array_values(array_diff($logistic_data, $exit_key));
		$modelName = ($year == $start_year) ? 'BrmExpected' : 'BrmBudgetPrime';
        $check_saved = array_filter($this->$modelName->find('list', array(
            'conditions' => array(
                'brm_term_id' => $term_id, 
                'target_year' => $year, 
                'layer_code' => $layer_code
            ),
            'fields' => array('logistic_index_no'),
            'group' => array('logistic_index_no')
        )));
        $show_all = true;
        if(count($check_saved) > 0){#saved
            $show_all = false;
        }
        $empty_trading = $trade_data['record'][$exit_key[0]]['trading'];
        $empty_kpi = $trade_data['record'][$exit_key[0]]['kpi'];
        $list = '=DestinationList!$A1:A'.count($destination);
        $copy_data = array_combine($table_months, array_fill(0, count($table_months), '0'));
        
        foreach ($exit_key as $each_key) {
            if ($each_key != '') {
                $objWorkSheet->getActiveSheet()->mergeCells('B'.$row.':C'.$row);
                $objWorkSheet->getActiveSheet()->mergeCells('D'.$row.':G'.$row);
                $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->applyFromArray($B1Style);
                if(in_array($each_key, $disabled_logi)) {
                    $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color_disabled);
                }else {
                    $objWorkSheet->getActiveSheet()->getStyle('D'.$row.':G'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($disable_color);
                }
                $sheet->setCellValue('B'.$row, __('取引'));
                $sheet->setCellValue('D'.$row, $each_key);
                
                $objWorkSheet->getActiveSheet()->mergeCells('H'.$row.':T'.$row);
                $objWorkSheet->getActiveSheet()->getStyle('H'.$row.':T'.$row)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FF0000');
                $sheet->setCellValue('H'.$row, __("⇒取引名をタブより選択して下さい。取引を新規追加する場合は、タブをクリック後、新規追加のブランク欄に取引名を入力してEnterをクリックして下さい。"));
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
                        if ($sub_acc_name != $sale_acc[1]) {
                            $have[$sub_acc_name] = $row;
                            $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                        } else {
                            $haves[$sale_acc[1]] = $row;
                            if (count($have) < 2) {
                                $subname = array_keys($have)[0];
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have[$subname]).')');
                            } else {
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row, '=SUM('.$colName[$index].($have[$sale_acc[0][0]]).'+'.$colName[$index].($have[$sale_acc[0][1]]).')');
                            }
                        }
                        # clone for internal pay fee(total of 売上原価)
                        if ($have[$sale_acc[0][1]] != "" && $haves[$sale_acc[1]] && $have[$sale_acc[1]] < $haves[$sale_acc[1]]) {
                            $diff = (range($have[$sale_acc[0][1]], $haves[$sale_acc[1]]));
                            $diffcnt = count($diff) - 2;
                            
                            $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$diff[0], '=SUM('.$colName[$index].($diff[1]).':'.$colName[$index].($diffcnt+$diff[0]).')');
                        }
                        $sheet->getStyle($colName[$index].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                        $index ++;
                    }

                    $data = $this->prepareArr($sub_acc_datas['data'], $copy_data, $sale_acc[2]);
                    
                    foreach ($data as $acc_name => $value) {
                        foreach ($value['amount'] as $acc => $amount) {
                            $idx_cnt = 0;

                            if (strpos($acc_name, $sale_acc[2]) !== false) {
                                $row ++;
                                $acc_name_explode = explode('/#/', $acc_name);
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
                                    if ($year == $start_year && $year_month[$acc_cnt_ym] != '') {#actual month disbale
                                        // $disable_color = 'd9d9d9';
                                        $result_lock_color = 'd9d9d9';
                                        $acc_cnt_ym++;
                                        if ($each_month != '0') {
                                            if (strpos($acc_name, $sale_acc[2]) !== false && $each_key != '取引無し') {
                                                $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($result_lock_color);
                                                $sheet->getStyle('E'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                                                $sheet->getStyle('D'.$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                            }
                                            
                                            $objWorkSheet->getActiveSheet()->getStyle('E'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($result_lock_color);
                                            $objValidation = $sheet->getCell('E'.$row)->getDataValidation();
                                            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                                            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                                            $objValidation->setAllowBlank(false);
                                            $objValidation->setShowInputMessage(false);
                                            $objValidation->setShowErrorMessage(false);
                                            $objValidation->setShowDropDown(false);
                                        }
                                    } else {#normal month enable
                                        $result_lock_color = $disable_color;
                                        $sheet->getStyle($colName[$idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                    }
                                    $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($result_lock_color);
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
        if($show_all) {
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
                    $sheet->setCellValue('H'.$row, __("⇒取引名をタブより選択して下さい。取引を新規追加する場合は、タブをクリック後、新規追加のブランク欄に取引名を入力してEnterをクリックして下さい。"));
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
                            $objWorkSheet->getActiveSheet()->getStyle($colName[$index].$row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                            $sheet->setCellValue($colName[$index].$row, $each_month);
                        
                            if($sub_acc_name != $sale_acc[1]) {
                                $have[$sub_acc_name] = $row;
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($row+1).':'.$colName[$index].($acc_cnt+$row).')');
                            }else {
                                $haves[$sale_acc[1]] = $row;
                                if(count($have) < 2) {
                                    $subname = array_keys($have)[0];
                                    $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($have[$subname]).')');
                                }else {
                                    $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$row,'=SUM('.$colName[$index].($have[$sale_acc[0][0]]).'+'.$colName[$index].($have[$sale_acc[0][1]]).')');

                                }
                            }
                            # clone for internal pay fee(total of 売上原価)
                            if($have[$sale_acc[0][1]] != "" && $haves[$sale_acc[1]] && $have[$sale_acc[0][1]] < $haves[$sale_acc[1]]) {
                                
                                $diff = (range($have[$sale_acc[0][1]], $haves[$sale_acc[1]]));
                                $diffcnt = count($diff) - 2;
                                
                                $objWorkSheet->getActiveSheet()->setCellValue($colName[$index].$diff[0],'=SUM('.$colName[$index].($diff[1]).':'.$colName[$index].($diffcnt+$diff[0]).')');
                                
                            }
                            $index ++;
                            
                        }
                        
                        $backup_amount = $sub_acc_datas['data'][array_keys($sub_acc_datas['data'])[0]]['amount'];
                        $merge_notexit = [];$cnt_notexit = 0;$chk_array = 0;
                        foreach ($sub_acc_datas['data'] as $acc_name => $value) {
                            $cnt_notexit++;
                            if (strpos($acc_name, $sale_acc[2]) !== false){
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
                                if (strpos($acc_name, $sale_acc[2]) !== false){
                                    $row ++;
                                    $acc_name = $sale_acc[2].' (   )';
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
                                          
                                            $result_lock_color = 'd9d9d9';
                                            $acc_cnt_ym++;
                                        }else{
                                            
                                            $result_lock_color = $disable_color;
                                            $sheet->getStyle($colName[$idx_cnt].$row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                                        }

                                        $objWorkSheet->getActiveSheet()->getStyle($colName[$idx_cnt].$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($result_lock_color);
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
        }
        $start=0;
        $two = [];
        $ones = [];
        foreach ($arr as $key => $value) {
            $one = [];
            foreach ($value as $keys => $values) {
                if (strpos($key, $sale_acc[2]) !== false) {
                    foreach ($values as $paykey => $payval) {
                        array_push($ones, $payval);
                    }
                    sort($ones);
                    $two[$sale_acc[2]] = implode('+', array_unique($ones));
                } else {
                    array_push($one, $values[0]);
                    $two[$key] = implode('+', $one);
                }
            }
        }
        
        $for = [];
        foreach ($total_formula as $totalkey => $totalvalue) {
            foreach ($two as $twokey => $twovalue) {
                if ($totalkey == $twokey || strpos($twokey, $sale_acc[2]) !== false) {
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
        #get sheet name
        $file_name = (count($file_name) == 2) ? $file_name[1] : $file_name;
        #for backup master
        if ($save_into_tmp && !$one_time_download) {
            $PHPExcel->save($file_name);
        } else{
            if(!$one_time_download){
                $PHPExcel->output($file_name.'.xlsx');
            }
        }
    }
    public function downloadBulkExcelDownload(){
        $Common = new CommonController();
        $term_id = $this->Session->read('TERM_ID');;
        $budget_term = $this->Session->read('TERM_NAME');
        $head_dept_id = $this->Session->read('HEAD_DEPT_CODE');
        $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');
        $login_id = $this->Session->read('LOGIN_ID');

        $Common->combineAsExcelSheets($term_id, $budget_term, $head_dept_id, $head_dept_name, $login_id, $this);
    }
    public function prepareArr($array, $copy_data, $internal_payment) {
        $chk_des = [];
        $one = [];
        foreach ($array as $key => $value) {
            if (strpos($key, $internal_payment) !== false) {
                foreach ($value as $keys => $values) {
                    foreach ($values as $des => $amt) {
                        array_push($chk_des, $des);
                        $main_key = ($key == $internal_payment)? $key.'/#//#/'.$des : $key.'/#/'.$des;
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
                $array[$internal_payment.'/#//#/'.$a]['amount'][''] = $copy_data;
                $array[$internal_payment.'/#//#/'.$a]['code'] = $one['code'];
                $array[$internal_payment.'/#//#/'.$a]['sub_id'] = $one['sub_id'];
            }
        }
        return $array;
    }
    public function UploadFile() {
        App::import('Vendor', 'php-excel-reader/PHPExcel');
        $loginId = $this->Session->read('LOGIN_ID');
        $budget_term = $this->Session->read('TERM_NAME');
        $diff_ba = explode("/", $this->request->data['layer_code']);
        $ba_code = $diff_ba[0];
        $ba_name = $diff_ba[1];
        $target_year = $this->request->data['year'];
        $term_id = $this->Session->read('TERM_ID');
        $head_dept_id = $this->Session->read('HEAD_DEPT_CODE');
        $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');

        $file           =  $this->request->params['form']['trading_upload'];
        $file_name      = $file['name'];
        $file_path      = $file['tmp_name'];
        $extension          = pathinfo($file_name, PATHINFO_EXTENSION);

        $error = (empty($file)) ? parent::getErrorMsg('SE015') :
                 ($file['error'] != 0) ? parent::getErrorMsg('SE015') :
                 ($file['size'] >= 10485760) ?  parent::getErrorMsg('SE020') :
                 (!($ext == "xlsx" || $ext == "xls")) ? parent::getErrorMsg("SE013", $ext) : false;

        if (!$error) {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader->setReadDataOnly(true);

            if ($objReader->canRead($file_path)) {
                $objPHPExcel   = $objReader->load($file_path);
                $objWorksheet  = $objPHPExcel->getSheetByName('TradingPlan');
                if(!empty($objWorksheet)){
                    $import_sheet_msg = $this->importSheet($target_year, $objWorksheet);
                    if(!empty($import_sheet_msg['success'])){
                        $successMsg = $import_sheet_msg['success'];
                        $this->Flash->set($successMsg, array('key'=>$import_sheet_msg['key']));
    
                    }
                    if(!empty($import_sheet_msg['error'])){
                        $errorMsg = $import_sheet_msg['error'];
                        $this->Flash->set($errorMsg, array('key'=>$import_sheet_msg['key']));
                    }
                }else{
                    $errorMsg = parent::getErrorMsg('SE021');
                    $this->Flash->set($errorMsg, array('key'=>'UserError'));
                }
                $this->redirect(array('controller'=>'BrmTradingPlan/?year='.$target_year,'action'=>'index'));
            }
        } else {
            #error of file size over
            $this->Flash->set($error, array("key"=>"UserError"));
        }
        $this->redirect(array('controller'=>'BrmTradingPlan/?year='.$target_year,'action'=>'index'));
    }
    public function importSheet($target_year, $objWorksheet){
        $term_id = $_SESSION['TERM_ID'];
        $budget_term = $_SESSION['TERM_NAME'];
        $head_dept_id = $_SESSION['HEAD_DEPT_CODE'];
        $head_dept_name = $_SESSION['HEAD_DEPT_NAME'];
        $ba_code = $_SESSION['SESSION_LAYER_CODE'];
        $ba_name = $_SESSION['BUDGET_BA_NAME'];
        $login_id = $_SESSION['LOGIN_ID'];
        $language = $_SESSION['Config']['language'];

        if(empty($_SESSION['TRADE_DATA'])){
            $_SESSION['TRADE_DATA'] = $this->getTradingDataAndCaching($target_year, $term_id, $budget_term, $head_dept_id, $ba_code, $ba_name, $login_id, $language)['trade_data'];
        }
        $highestRow    = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $searchValue = '⇒取引名をタブより選択して下さい。新規追加する際は財務経理部へご連絡下さい。';
        $chkTerm    = $objWorksheet->getCell("S3")->getValue();
        $diff_excelBa = explode("/", $objWorksheet->getCell("S4")->getValue());
        $chkBA = $diff_excelBa[0];
        $chkheader  = $objWorksheet->getCell("A3")->getValue();
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

                        return array(
                            'key' => 'UserError',
                            'error' => $error,
                        );
                    }
                }
            }
            
            #error check and save data prepare
            $getExcelData = $this->checkHeaderAndColumn($worksheets, $index,$target_year);
            $Common = new CommonController;
            $months = $Common->get12Month($term_id);
            if (empty($getExcelData['error'])) {
                $importData = $getExcelData['success'];
    
                #Get data from cache
                $cache_name = 'trading_plan_'.$term_id.'_'.$target_year.'_'.(explode('/', $ba_code))[0].'_'.$login_id;
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
                                    if ($months_amts[$i] != 0 || $des_n_user[0] != '') {
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
                $subid_del = [];
                // End of edited by Ei Thandar Kyaw
                foreach ($excel_data as $logistic_no => $logistic_data) {
                    $logi[] = $logistic_no;
                    foreach ($logistic_data as $sub_acc_id => $sub_acc_datas) {
                        if(!in_array($sub_acc_id, $subid_del)) $subid_del[] = $sub_acc_id;
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
                                $des = ($des == 'NONEDESTI') ? '' : $des;
                                $payfee = (strstr($des_ba, '/'))? explode('/', $des_ba)[1] : $amount['kpi_unit'];
                                
                                if(explode('/', $des_ba)[2]) $payfee = $payfee.'/'.explode('/', $des_ba)[2];
                                
                                $save = false;
                                if (strstr($des, 'view')) {
                                    $des = explode('_', $des)[0];
                                }
                                if ($acc_code == Setting::INNER_PAY_ACCOUNT_CODE) {
                                    $allZero = true;
                                    foreach ($months_amts as $mKey=>$mValue) {
                                        if ($mKey != 0 && $mKey != 7 && $mKey != 14 && $mKey != 15 && $mKey != 16) {
                                            if ($mValue != 0) {
                                                $allZero = false;
                                            }
                                        }
                                    }
                                    if ($des != '' || $payfee != '') {
                                        $save = true;
                                    }
                                    
                                    if (!$allZero && (empty($des))) {
                                        $error = parent::getErrorMsg('SE119', $logistic_no);
                                        return array(
                                            'key' => 'UserError',
                                            'error' => $error,
                                        );
                                    }
                                } else {
                                    $save = true;
                                }
                                
                                if ($save) {
                                    $save_data[] = array(
                                        'brm_term_id'      => $term_id,
                                        'target_year'  => $target_year,
                                        'layer_code'      => $ba_code,
                                        'trade_filling_date'  => $trade_filling_date,
                                        'brm_account_id'   =>  $sub_acc_id,
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
                                        'flag'         => 1,
                                        'logistic_index_no'  => $logistic_no,
                                        'destination'  => $des,
                                        'kpi_unit'     => $payfee,
                                        'created_by'   => $login_id,
                                        'updated_by'   => $login_id
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
                
                $sumModelName = '';
                $term_1st_year = substr($budget_term, 0, 4);
                if ($target_year == $term_1st_year) {
                    $modelName = 'BrmExpected';
                    $type = 'forecast';
                    $sumModelName = 'BrmForecastSummary';
                } else {
                    $modelName = 'BrmBudgetPrime';
                    $type = 'budget';
                    $sumModelName = 'BrmBudgetSummary';
                }
                
                $attachDB = $this->$modelName->getDataSource();
                $sumDB = $this->$sumModelName->getDataSource();
                $BPData = new BrmBudgetPlanController();
                try {
                    $attachDB->begin();
                    $sumDB->begin();
                    $this->$modelName->deleteAll(array(
                        'brm_term_id'     => $term_id,
                        'target_year' => $target_year,
                        'layer_code'     => $ba_code,
                        'brm_account_id'  => $subid_del
                    ), false);
                    # Save new data
                    if (!empty($save_data)) {
                        $this->$modelName->saveAll($save_data);
                    }
                    
                    $this->$sumModelName->updateSummaryData($ba_code,$term_id,$target_year);
                    $filling_date = '';
                    $manual_tax_ba = Setting::BA_BUDGET_TAX;
                    $calculate_tax = [];
                    $calculate_tax = true;
                    if (!in_array($ba_code, $manual_tax_ba)) {
                        $calculate_tax = $BPData->updateTaxAmount($term_id, $budget_term, $head_dept_id, $head_dept_name, $ba_code, $ba_name, $target_year, $login_id, $modelName);
                    }
                    $attachDB->commit();
                    $sumDB->commit();

                    if(count($logi) != 0 && $calculate_tax) {
                        $successMsg = parent::getSuccessMsg('SS001');
                        return array(
                            'key' => 'UserSuccess',
                            'success' => $successMsg,
                        );
                    }else {
                        $attachDB->rollback();
                        $errorMsg = parent::getErrorMsg('SE017', __("アップロード"));
                        return array(
                            'key' => 'UserError',
                            'error' => $errorMsg,
                        );
                    }
                } catch (Exception $e) {
                    $attachDB->rollback();
                    $sumDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $errorMsg = parent::getErrorMsg('SE003');
                    return array(
                        'key' => 'UserError',
                        'error' => $errorMsg,
                    );
                }
            } else {
                #if have excel error will show.
                $error = $getExcelData['error'];
                return array(
                    'key' => 'UserError',
                    'error' => $error,
                );
            }
        } else {
            #file format invaild
            if ($chkheaderName != __("年度 取引計画")) {
                $error = parent::getErrorMsg('SE021');
            } else {
                #error of excel budget term and ba_code
                $error = parent::getErrorMsg('SE100', __("予算年度")." , ".__("予算期間")." , ".__("コード"));
            }
            return array(
                'key' => 'UserError',
                'error' => $error,
            );
        }
    }
    public function checkHeaderAndColumn($worksheets, $index, $year=0) {
        if($year == 0){
            $target_year = $this->request->data['year'];
        }else{
            $target_year = $year;
        }

        if (!empty($_SESSION['LOGIN_ID'])) {
            $loginId = $_SESSION['LOGIN_ID'];
        }

        if (!empty($_SESSION['TERM_ID'])) {
            $term_id = $_SESSION['TERM_ID'];
        }
        if (!empty($_SESSION['SESSION_LAYER_CODE'])) {
            $ba_code = $_SESSION['SESSION_LAYER_CODE'];
        }
        if (!empty($_SESSION['TRADE_DATA'])) {
            $trade_data = $_SESSION['TRADE_DATA'];
        }


        $Common = new CommonController;
        $months = $Common->get12Month($term_id);
        $short_year = substr($target_year, -2);
        #get forecast period
        $forecast_period = $this->forecastPeriod($term_id);
        #get budget start year
        $start_month = $Common->getMonth($target_year, $term_id, 'start');
        $end_month = $Common->getMonth($target_year, $term_id, 'end');
        #get months from start_month to forecast_month
        $year_month = array_keys($this->getYmRange($months, $start_month, $forecast_period));
        $first_half_lbl = $short_year.__('年度'). __('上期');
        $second_half_lbl = $short_year.__('年度'). __('下期');
        $org_header = array(
            'tradeText'     => $short_year.__('年度 取引計画'). __('（単位：千円）'),
            'month1'        => __($months[0]),
            'month2'        => __($months[1]),
            'month3'        => __($months[2]),
            'month4'        => __($months[3]),
            'month5'        => __($months[4]),
            'month6'        => __($months[5]),
            'first_half'    => str_replace(' ', '', $first_half_lbl),
            'month7'        => __($months[6]),
            'month8'        => __($months[7]),
            'month9'        => __($months[8]),
            'month10'       => __($months[9]),
            'month11'       => __($months[10]),
            'month12'       => __($months[11]),
            'second_half'   => str_replace(' ', '', $second_half_lbl),
            'yearly'        => $short_year.__('年度'). __('年間')
        );

        $excel_header = array(
            'tradeText'     => $worksheets[1][0][0],
            'month1'        => $worksheets[1][0][4],
            'month2'        => $worksheets[1][0][5],
            'month3'        => $worksheets[1][0][6],
            'month4'        => $worksheets[1][0][7],
            'month5'        => $worksheets[1][0][8],
            'month6'        => $worksheets[1][0][9],
            'first_half'    => str_replace(' ', '', $worksheets[0][0][10]),
            'month7'        => $worksheets[1][0][11],
            'month8'        => $worksheets[1][0][12],
            'month9'        => $worksheets[1][0][13],
            'month10'       => $worksheets[1][0][14],
            'month11'       => $worksheets[1][0][15],
            'month12'       => $worksheets[1][0][16],
            'second_half'   => str_replace(' ', '', $worksheets[0][0][17]),
            'yearly'        => $worksheets[0][0][18]
        );
        $header_result   = array_diff($org_header, $excel_header);
        
        $org_subAcc_arr = [];
        $org_account_arr = [];
        $excel_subAcc_arr = [];
        $excel_account_arr = [];
        $ws_index = $index-6;
        $i = 0;
        $firstKey = $this->array_key_first($trade_data['record']);
        
        foreach ($trade_data['record'] as $logistic_index => $datas) {
            foreach ($datas['trading'] as $sub_acc_name => $sub_acc_datas) {
                #sub_acc_name push to array
                array_push($org_subAcc_arr, $sub_acc_name);
                foreach ($sub_acc_datas['data'] as $acc_name => $acc_value) {
                    foreach ($acc_value['amount'] as $des_ba => $month) {
                        $account_name   = (!empty($des_ba)) ? $acc_name."(".$des_ba.")" : $acc_name;
                        #acc_name push to array
                        array_push($org_account_arr, $acc_name);
                        $trade_value[$logistic_index]
                                    [$acc_value['acc_id']]
                                    [$acc_value['code']]
                                    [] = $des_ba;
                    }
                }
            }
            #for kpi
            $trade_value[$logistic_index][0]['0000000000']['ba'] = '';
        }
        
        $logistic_data=[];
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

        $trade_fill_date = $trade_data['trade_filling_date'];
        $trade_data_excel   = [];

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
        
        $destination = $this->getDestination();
        foreach ($trade_value as $logistic_index_no => $sub_acc_data) {
            foreach ($sub_acc_data as $sub_acc_id => $account_data) {
                if ($sub_acc_id == 2) {
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
        
        $data = ['error'=>$notMatchErr,'success'=>$trade_data_excel];
        return $data;
    }
    public function array_key_first(array $array) {
        foreach ($array as $key => $value) {
            return $key;
        }
    }
    public function array_key_last(array $array) {
        end($array);
        return key($array);
    }
    public function is_decimal3($val) {
        $decimalOnly = preg_match('/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/', $val);
        
        return ($decimalOnly == 1)? true : false;
    }
    public function getTradingDataAndCaching($year, $term_id, $term_name, $top_layer_code, $layer_code, $layer_code_name, $login_id, $language = null) {
        $Common = New CommonController();
        $start_year = substr($term_name, 0, 4);
        $forecast_period = $this->forecastPeriod($term_id, $top_layer_code);
        $start_month = $Common->getMonth($start_year, $term_id, 'start');
        $end_month = $Common->getMonth($start_year, $term_id, 'end');
        $sale_acc = Setting::SALE_ACCOUNT;
        $months = $Common->get12Month($term_id);
        $modelName = ($year == $start_year) ? 'BrmExpected' : 'BrmBudgetPrime';
        $ym_range = $this->getYmRange($months, $start_month, $end_month);

        $account_datas = $this->getAccountData($year, $top_layer_code, $sale_acc[0]);
        $errormsg = ($account_datas['errorchk']) ? $account_datas['errormsg'] : "";
        unset($account_datas['errorchk']);
        unset($account_datas['errormsg']);

        $active_logistics = $this->getActiveLogi($year, $layer_code);
        $logistic_data = array_unique(array_column($active_logistics, 'index_name'));

        $tp_data = $this->getTradingData($account_datas, $active_logistics, $months, $modelName, $months, $term_id, $year, $layer_code, $sale_acc, $start_year, $start_month, $end_month, $forecast_period, $ym_range, $login_id);
        return $tp_data;
    }
    public function getLogisticData() {
        parent::checkAjaxRequest($this);
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $year    = $this->request->data["year"]; 
        $logistics_data = $this->BrmLogistic->find('all', array(
            'fields' => array('id','target_year','layer_code','index_name','logistic_order'),
            'conditions' => array(
                'layer_code' => $layer_code,
                'target_year' => $year,
                'flag' => 1
            ),
            'group' => 'index_name',
            'order' => array('BrmLogistic.logistic_order' => 'asc','BrmLogistic.index_name' => 'asc')
        ));
        echo json_encode($logistics_data);
    }
    public function EditLogisticSetup() {
        $login_id = $this->Session->read('LOGIN_ID');
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $data = $this->request->data['data_arr'];
        
        if (!empty($data)) {
            $date = date("Y-m-d H:i:s");
            $update = [];

            foreach ($data as $key => $each_data) {
                $save_data = json_decode($each_data, true);
                $searchID  = $this->BrmLogistic->find('list', array(
                    'fields' => array('id','index_name'),
                    'conditions' => array(
                        'target_year'   => $save_data['year'],
                        'layer_code'    => $save_data['layer_code'],
                        'index_name'    => $save_data['index_name'],
                        'flag'          => 1
                    )
                ));
                $key = $key+1;
                foreach ($searchID as $id => $logistic_order) {
                    $update[] = array(
                        'id'            => $id,
                        'logistic_order'=> $key,
                        'updated_id'    => $login_id,
                        'updated_date'  => $date
                    ); 
                }              
            }
            
            $this->BrmLogistic->saveMany($update);
            $successMsg  = parent::getSuccessMsg("SS001");
            $this->Flash->set($successMsg, array("key"=>"UserSuccess"));  
            return json_encode($successMsg);
        } else {
            $errorMsg = parent::errorMsg("SS002");
            $this->Flash->set($errorMsg, array("key"=>"UserError"));
            return json_encode($errorMsg);
        }
    }
}
