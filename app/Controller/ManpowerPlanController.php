<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'PositionMp');
App::import('Controller', 'BudgetPlan');
/**
 * ManpowerPlans Controller
 *
 * @property ManpowerPlan $ManpowerPlan
 * @property PaginatorComponent $Paginator
 */
class ManpowerPlanController extends AppController
{
    public $uses = array('ActualResultSummaryModel','ManpowerPlanModel','FieldModel','PositionMpModel','ManpowerPlanOtModel','AccountModel','SubAccountModel','BudgetPrimeModel','ExpectedModel','BudgetLogModel','BusinessAreaModel','ExpectedBudgetDiffJobModel','TermModel','TaxAmountModel','HeadDepartmentModel','BudgetSummaryModel','ForecastSummaryModel');
    public $components = array('Session','Flash','PhpExcel.PhpExcel','Paginator');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
    }

    /**
     * index method
     *
     * @author NuNuLwin (20200715)
     * @return void
     */
    public function index()
    {
        $this->layout = 'phase_3_menu';

        $user_level = $this->Session->read('ADMIN_LEVEL_ID');

        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('HEADQUARTER_DEADLINE')) {
            $hqDeadline = $this->Session->read('HEADQUARTER_DEADLINE');
        }
        
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if ($this->Session->check('PERMISSION')) {
            $permission = $this->Session->read('PERMISSION');
        }
       
        $target_year = $_GET['year'];
        $this->Session->write('TARG_YEAR', $target_year);
        if (!empty($_GET['code'])) {
            $ba_code 			= $_GET['code'];
        }
        if (!empty($_GET['hq'])) {
            $head_dept_id 		= $_GET['hq'];
        }
        if (!empty($_GET['term'])) {
            $term_id 			= $_GET['term'];
        }
        if (!empty($_GET['termName'])) {
            $budget_term 	= $_GET['termName'];
        }
        
        $mpData = $this->getManpowerData($term_id, $budget_term, $head_dept_id, $ba_code, $target_year, $loginId, $permission, $hqDeadline);
        if ($mpData == 'no_data') {
            $no_data = $mpData;
            $this->set(compact('budget_term', 'ba_code', 'target_year', 'no_data'));
        } else {
            $accounts = $mpData['accounts'];
            $mp_data = $mpData['mp_data'];
            $approveBA = $mpData['approveBA'];
            $budget_year = $mpData['budget_year'];
            $deadline_date = $mpData['deadline_date'];
            $freezeLastMonth = $mpData['freezeLastMonth'];
            $yellow_pos_name = $mpData['yellow_pos_name'];
            $field_name_first = $mpData['field_name_first'];
            $field_name_second = $mpData['field_name_second'];
            $Month_12 = $mpData['Month_12'];
            $Month_12digit = $mpData['Month_12digit'];
            $from_ba_date = $mpData['from_ba_date'];
            $compare_unit_price = $mpData['compare_unit_price'];// khin (change unit price)
            $cache_unit = $mpData['cache_unit'];

            $show_alert = $term_id.'_'.$target_year.'_'.$head_dept_id.'_'.$ba_code.'_'.implode('_',$cache_unit);
            
            $btn_cache = Cache::read($show_alert);
            if(!$btn_cache) {
                Cache::write($show_alert, 'Save');
            }

            $get_cache_resave = $term_id.'_'.$target_year.'_'.$head_dept_id.'_'.$ba_code;
            
            $btn_cache_resave = Cache::read($get_cache_resave);
            
            $ba_name_code = $this->BusinessAreaModel->find('first', array('fields' => array('ba_name_jp','ba_name_en'),
            'conditions' => array('ba_code' => $ba_code,'flag' => 1)));

            if (!empty($ba_name_code['BusinessAreaModel']['ba_name_en'])) {
                $ba_name_code = $ba_code.'/'.$ba_name_code['BusinessAreaModel']['ba_name_jp'];
            } else {
                $ba_name_code = $ba_code;
            }
            
            $this->set(compact('accounts', 'budget_term', 'ba_code', 'ba_name_code', 'target_year', 'mp_data', 'approveBA', 'budget_year', 'Month_12', 'Month_12digit', 'deadline_date', 'from_ba_date', 'term_id', 'head_dept_id', 'field_name_first', 'field_name_second','compare_unit_price', 'btn_cache','btn_cache_resave'));
        }

        $this->render('index');
    }

    public function getManpowerData($term_id, $budget_term, $head_dept_id, $ba_code, $target_year, $loginId, $permission, $hqDeadline=null)
    {
        $Common = new CommonController;
        $Position = new PositionMpController;

        $yellow_pos_name = [];

        $Month_12 = $Common->get12Month($term_id);
        $Month_12digit = $Common->get12DigitMonth($term_id);
        #get ba_code of from_date
        $from_date = $this->BusinessAreaModel->find('first', array(
                     'fields'     => array('from_date'),
                     'conditions' => array('ba_code' => $ba_code,
                                            'flag'   => 1)));
        $from_ba_date = $from_date['BusinessAreaModel']['from_date'];

        if (empty($head_dept_id) || empty($ba_code)) {
            if (empty($head_dept_id)) {
                $errorMsg = parent::getErrorMsg('SE073');
            } elseif (empty($ba_code)) {
                $errorMsg = parent::getErrorMsg('SE086');
            }
            
            $this->Flash->error($errorMsg);
            $this->redirect(array('controller' => 'TermSelection', 'action' => 'index'));
        }

        $no_data = "";
        $mp_data = array();
        $percentBA = '';

        $freezeLastMonth = '';
        

        if ($ba_code == '8003' || $ba_code == '8003/人事部') {
            $percentBA = 'hr';
        } else {
            $percentBA = 'other';
        }

        #get all position data by year
        $positionData = $this->getPositionData($target_year, $ba_code);

        #get first two fields name by year (NUNULWIN 20210420)
        $twoField = $Position->getFirstTwoFieldName($target_year, $ba_code);
        
        $field_name_first  = $twoField['0'];
        $field_name_second = $twoField['1'];
        
        #get table orders from setting file
        $tableOrders = Setting::MP_TABLE_ORDER;
        
        #check create limit for disabled
        $crLimit = $permission['BudgetingSystemCreateLimit'];
        if (($user_level==2 || $user_level==3 || $user_level==4) && ($ba_code == '8000' || $ba_code == '8001')) {
            $create = true;
        } else {
            $create = $Common->checkLimit($crLimit, $ba_code, $loginId);
        }
        $compare_unit_price = [];$cache_unit = [];
        #Check data exist or not at tbl_position_mp and tbl_field
        if (!empty($positionData)) {
            #Check BA_Code is already approved or not
            $approveBA = $this->BudgetLogModel->find('first', array(
                                            'conditions' => array(
                                                'term_id' => $term_id,
                                                'head_dept_id' => $head_dept_id,
                                                'ba_code' => $ba_code,
                                                'flag' => '2'
                                            )
                                        ));
            $approveHQ = $this->BudgetLogModel->find('first', array(
                                            'conditions' => array(
                                                'term_id' => $term_id,
                                                'head_dept_id' => $head_dept_id,
                                                'ba_code' => '0',
                                                'dept_id' => '0',
                                                'flag' => '2'
                                            )
                                        ));
            
            if (empty($approveBA) && $create == 'true' && empty($approveHQ)) {
                $approveBA = '0';
            } else {
                $approveBA = '1';
            }
            
            $getFillingDate = $this->ManpowerPlanModel->find('first', array(
                'fields' => 'filling_date',
                'conditions' => array(
                    'ManpowerPlanModel.ba_code' => $ba_code,
                    'ManpowerPlanModel.term_id' => $term_id,
                    'ManpowerPlanModel.target_year' => $target_year,
                    'ManpowerPlanModel.flag' => 1
                )
            ));
            $fill_date = $getFillingDate['ManpowerPlanModel']['filling_date'];

            $filling_date = ($fill_date == '0000-00-00 00:00:00' || empty($fill_date) || $fill_date == null) ? date('Y/m/d') : date("Y/m/d", strtotime($fill_date));
            
            $mp_data['filling_date'] = $filling_date;


            #Get forecast period(eg: 2020-05) to show actual result data till to this period
            $forecastPeriod = $this->TermModel->getForecastPeriod($term_id);
            // $forecastPeriod = $termData[0]['tbl_term']['forecast_period'];
            
            $deadline_date = ($hqDeadline == '0000-00-00 00:00:00' || empty($hqDeadline) || $hqDeadline == null) ? '' : date("Y/m/d", strtotime($hqDeadline));


            $prevOT = 0;
            $prevField = '';
            $removeField = array();
            $wholeTotal = array();

            $f = 0;
            #Prepare manpower data in result array
            foreach ($positionData as $eachPosition) {
                #merge into one array
                $p_data = call_user_func_array('array_merge', $eachPosition);
                #get data
                $p_id 			= $p_data['id'];
                $field_id		= $p_data['field_id'];
                $field_name 	= $p_data['field_name_jp'];
                
                $ot_rate 		= $p_data['overtime_rate'];
                $position_name 	= $p_data['position_name_jp'];
                $unit_salary 	= $p_data['unit_salary'];
                $display_no	 	= $p_data['display_no'];
                $edit_flag 		= $p_data['edit_flag'];
                $percentage		= $p_data['percentage'];

                $first_half = 1;
                $secnd_half = 1;
                if (!empty($percentage)) {
                    $percent = json_decode($percentage, true);
                    $first_half = $percent[$percentBA][0]/100;
                    $secnd_half = $percent[$percentBA][1]/100;
                }

                if ($ot_rate != 0) { #if OT rate is not 0, set data to OT table
                    #get OT data
                    $manpower_ot = $this->ManpowerPlanOtModel->getMonthlyResult($ba_code, $term_id, $target_year, $field_id);
                    
                    $rate_OT = $manpower_ot['tbl_manpower_plan_ot']['overtime_rate'];
                    
                    $ot_inputs = array_sum($manpower_ot[0]);

                    $ot_rate = ((!empty($rate_OT) || $rate_OT!= 0) && ($ot_inputs !=0 )) ? $rate_OT : $ot_rate;

                    if ($ot_rate == $prevOT && $field_name != $prevField) {
                        $ot_field = $prevField.'・'.$field_name.'（時間当り）';
                        $removeField[] = $prevField.'（時間当り）';
                        $removeField[] = $field_name.'（時間当り）';
                    } else {
                        $ot_field = $field_name.'（時間当り）';
                    }

                    $prevOT = $ot_rate;
                    $prevField = $field_name;

                    #set in data array
                    // $mp_data['data'][3]['table_data']['残業']['field_id'] = $field_id;
                    $mp_data['data'][3]['table_data']['残業']['sub_data'][$ot_field]['field_id'] = $field_id;
                    $mp_data['data'][3]['table_data']['残業']['sub_data'][$ot_field]['unit_price'] = number_format($ot_rate);
                    $mp_data['data'][3]['table_data']['残業']['sub_data'][$ot_field]['monthly_amt'] = $manpower_ot[0];
                }

                #get manpower data
                $manpower = $this->ManpowerPlanModel->getMonthlyResult($ba_code, $term_id, $target_year, $p_id);
                $inputs = array_sum($manpower[0]);

                $salary = $manpower['tbl_manpower_plan']['unit_salary'];

                $unit_salary = ((!empty($salary) || $salary!= 0) && ($edit_flag == 1 || $inputs!=0)) ? $salary : $unit_salary;
                
                #set in data array
                $mp_data['data'][$display_no]['table_data'][$field_name]['sub_data'][$position_name]['edit_permit'] = ($edit_flag == 1) ? true : false;
                //To check yellow color for excel import
                ($edit_flag == 1) ? array_push($yellow_pos_name, $position_name) : '';

                $mp_data['data'][$display_no]['table_data'][$field_name]['field_id'] = $field_id;
                $mp_data['data'][$display_no]['table_data'][$field_name]['vir_field_id'] = $field_id;
                $mp_data['data'][$display_no]['table_data'][$field_name]['sub_data'][$position_name]['position_id'] = $p_id;
                $mp_data['data'][$display_no]['table_data'][$field_name]['sub_data'][$position_name]['unit_price'] = number_format($unit_salary);
                $mp_data['data'][$display_no]['table_data'][$field_name]['sub_data'][$position_name]['monthly_amt'] = $manpower[0];

                $loopcnt = 0;
                foreach ($manpower[0] as $col => $monthly_mp) {
                    $loopcnt ++;
                    $pcnt = ($loopcnt<=7) ? $first_half : $secnd_half;

                    $mp_data['data'][$display_no]['table_data'][$field_name]['sub_data'][$position_name]['percentage']['first_half'] = $first_half;
                    $mp_data['data'][$display_no]['table_data'][$field_name]['sub_data'][$position_name]['percentage']['secnd_half'] = $secnd_half;

                    if ($display_no == 4) {
                        $mp_data['freeze'][$col] = '';
                        $mp_data['total']['派遣社員人件費合計']['monthly_amt'][$col] += $unit_salary*$monthly_mp*$pcnt;
                        $mp_data['data'][$display_no]['table_total']['派遣社員小計'][$col] += $monthly_mp;
                        $mp_data['data'][$display_no]['table_total']['C　金額（小計）'][$col] += $unit_salary*$monthly_mp*$pcnt;
                        $mp_data['data'][$display_no]['table_total']['D　金額（手入力）'][$col] = 0;
                    } else {
                        $mp_data['freeze'][$col] = '';
                        if ($display_no == 2) {
                            $mp_data['total']['社員人件費（小計）']['monthly_amt'][$col] -= $unit_salary*$monthly_mp*$pcnt;
                            $mp_data['total']['社員人件費（合計）']['monthly_amt'][$col] -= $unit_salary*$monthly_mp*$pcnt;
                        } else {
                            $mp_data['total']['社員人件費（小計）']['monthly_amt'][$col] += $unit_salary*$monthly_mp*$pcnt;
                            $mp_data['total']['社員人件費（合計）']['monthly_amt'][$col] += $unit_salary*$monthly_mp*$pcnt;
                        }
                        $mp_data['total']['社員人件費（手入力）']['monthly_amt'][$col] = 0;
                    }
                    $mp_data['freeze'][$col] = '';
                    if ($display_no == 2) {
                        $wholeTotal['monthly_amt'][$col] -= $unit_salary*$monthly_mp*$pcnt;
                    } else {
                        $wholeTotal['monthly_amt'][$col] += $unit_salary*$monthly_mp*$pcnt;
                    }

                    if ($display_no == 1) {
                        if ($field_name == $field_name_first || $field_name == $field_name_second) {
                            
                            // $mp_data['data'][$display_no]['table_data'][$field_name_second]['sub_total'][$field_name_second.'（含'.$field_name_first.'）小計'][$col] += $monthly_mp;
                            $mp_data['data'][$display_no]['table_data'][$field_name_second]['sub_total'][$field_name_second.'（含'.$field_name_first.'）小計'][$col] += $monthly_mp;
                            $mp_data['data'][$display_no]['table_data'][$field_name_first]['vir_field_id'] = $mp_data['data'][$display_no]['table_data'][$field_name_second]['field_id'];
                        } else {
                            $mp_data['data'][$display_no]['table_data'][$field_name]['sub_total'][$field_name.'小計'][$col] += $monthly_mp;
                        }
                        $mp_data['data'][$display_no]['table_total']['社員合計'][$col] += $monthly_mp;
                    } elseif ($display_no == 2) {
                        $mp_data['data'][$display_no]['table_total']['出向者小計'][$col] += $monthly_mp;
                    }
                }
                //khin (change unit price)
                if($p_data['unit_salary'] != $unit_salary && !$edit_flag) {
                    $compare_unit_price[$field_id.'_'.$field_name.'_'.$p_id.'_'.$position_name] = number_format($unit_salary).'_'.number_format($p_data['unit_salary']);
                    $cache_unit[] = $field_id.'_'.$p_id.'_'.number_format($unit_salary).'_'.number_format($p_data['unit_salary']);
                }
                if($p_data['overtime_rate'] != $ot_rate) {
                    $compare_unit_price[$field_id.'_'.$field_name.'_'.$field_id.'_'.$field_name] = number_format($ot_rate).'_'.number_format($p_data['overtime_rate']);
                    $cache_unit[] = $field_id.'_'.$p_id.'_'.number_format($ot_rate).'_'.number_format($p_data['overtime_rate']);
                }
            }

            #Adjust OT Rate
            foreach ($removeField as $rm_ot_field) {
                unset($mp_data['data'][3]['table_data']['残業']['sub_data'][$rm_ot_field]);
            }

            $otData = $mp_data['data'][3]['table_data']['残業']['sub_data'];

            foreach ($otData as $fieldName => $fieldData) {
                $unit_ot = $fieldData['unit_price'];
                $unit_ot = str_replace(',', '', $unit_ot);
                foreach ($fieldData['monthly_amt'] as $col => $monthly_ot) {
                    $mp_data['total']['社員人件費（小計）']['monthly_amt'][$col] += $unit_ot*$monthly_ot;
                    $mp_data['total']['社員人件費（合計）']['monthly_amt'][$col] += $unit_ot*$monthly_ot;
                    $wholeTotal['monthly_amt'][$col] += $unit_ot*$monthly_ot;

                    $mp_data['data'][3]['table_total']['A　金額（小計）'][$col] += $unit_ot*$monthly_ot;
                    $mp_data['data'][3]['table_total']['B　金額（手入力）'][$col] = 0;
                    $mp_data['data'][3]['table_total']['合計（A+B）'][$col] += $unit_ot*$monthly_ot;
                }
            }


            $mp_data['total']['社員＋派遣人件費合計'] = $wholeTotal;

            $mp_data['total']['社員人件費（手入力）']['monthly_amt']['1st_half_total'] = 0;
            $mp_data['total']['社員人件費（手入力）']['monthly_amt']['2nd_half_total'] = 0;
            $mp_data['total']['社員人件費（手入力）']['monthly_amt']['sub_total'] = 0;

            $mp_data['total']['社員人件費（合計）']['monthly_amt']['1st_half_total'] = $mp_data['total']['社員人件費（小計）']['monthly_amt']['1st_half_total'];
            $mp_data['total']['社員人件費（合計）']['monthly_amt']['2nd_half_total'] = $mp_data['total']['社員人件費（小計）']['monthly_amt']['2nd_half_total'];
            $mp_data['total']['社員人件費（合計）']['monthly_amt']['sub_total'] = ($mp_data['total']['社員人件費（合計）']['monthly_amt']['1st_half_total'] + $mp_data['total']['社員人件費（合計）']['monthly_amt']['2nd_half_total'])/2;
            
            $mp_data['total']['社員人件費（小計）']['monthly_amt']['1st_half_total'] = $mp_data['total']['社員人件費（小計）']['monthly_amt']['1st_half_total'];
            $mp_data['total']['社員人件費（小計）']['monthly_amt']['2nd_half_total'] = $mp_data['total']['社員人件費（小計）']['monthly_amt']['2nd_half_total'];
            $mp_data['total']['社員人件費（小計）']['monthly_amt']['sub_total'] = ($mp_data['total']['社員人件費（小計）']['monthly_amt']['1st_half_total'] + $mp_data['total']['社員人件費（小計）']['monthly_amt']['2nd_half_total'])/2;

            
            $mp_data['total']['派遣社員人件費合計']['monthly_amt']['1st_half_total'] = $mp_data['total']['派遣社員人件費合計']['monthly_amt']['1st_half_total'];
            $mp_data['total']['派遣社員人件費合計']['monthly_amt']['2nd_half_total'] = $mp_data['total']['派遣社員人件費合計']['monthly_amt']['2nd_half_total'];
            $mp_data['total']['派遣社員人件費合計']['monthly_amt']['sub_total'] = ($mp_data['total']['派遣社員人件費合計']['monthly_amt']['1st_half_total'] + $mp_data['total']['派遣社員人件費合計']['monthly_amt']['2nd_half_total'])/2;
            
            $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['1st_half_total'] = $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['1st_half_total'];
            $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['2nd_half_total'] = $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['2nd_half_total'];
            $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['sub_total'] = ($mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['1st_half_total'] + $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['2nd_half_total'])/2;


            #Sort by display no key
            ksort($mp_data['data']);

            $adjust_data = $this->ManpowerPlanModel->find('all', array(
                'fields' => array(
                    'ManpowerPlanModel.display_no',
                    'SUM(ManpowerPlanModel.month_1_amt) as month_1_amt',
                    'SUM(ManpowerPlanModel.month_2_amt) as month_2_amt',
                    'SUM(ManpowerPlanModel.month_3_amt) as month_3_amt',
                    'SUM(ManpowerPlanModel.month_4_amt) as month_4_amt',
                    'SUM(ManpowerPlanModel.month_5_amt) as month_5_amt',
                    'SUM(ManpowerPlanModel.month_6_amt) as month_6_amt',
                    'SUM(ManpowerPlanModel.month_7_amt) as month_7_amt',
                    'SUM(ManpowerPlanModel.month_8_amt) as month_8_amt',
                    'SUM(ManpowerPlanModel.month_9_amt) as month_9_amt',
                    'SUM(ManpowerPlanModel.month_10_amt) as month_10_amt',
                    'SUM(ManpowerPlanModel.month_11_amt) as month_11_amt',
                    'SUM(ManpowerPlanModel.month_12_amt) as month_12_amt',
                ),
                'conditions' => array(
                    'ManpowerPlanModel.term_id' => $term_id,
                    'ManpowerPlanModel.target_year' => $target_year,
                    'ManpowerPlanModel.ba_code' => $ba_code,
                    'ManpowerPlanModel.type' => Setting::MP_TYPE_ADJUST,
                ),
                'group' => array('display_no')
            ));

            foreach ($adjust_data as $adjustment) {
                $display_num = $adjustment['ManpowerPlanModel']['display_no'];
                $monthly_adj = $adjustment[0];

                $half_divider = ($display_num == 3) ? 1 : 16;
                $total_divider = ($display_num == 3) ? 1 : 12;

                $monthcnt = 0;
                foreach ($monthly_adj as $adj_month => $adj_value) {
                    $monthcnt ++;
                    $total_col = ($monthcnt < 7) ? '1st_half_total' : '2nd_half_total';

                    // $mp_data['data'][$display_num]['table_total']['A　金額（小計）'][$adj_month] = $mp_data['data'][$display_num]['table_total']['金額'][$adj_month];
                    if ($adj_value != 0) {
                        if ($display_num == 4) {
                            $mp_data['data'][$display_num]['table_total']['D　金額（手入力）'][$adj_month] = $adj_value;
                            $mp_data['data'][$display_num]['table_total']['D　金額（手入力）'][$total_col] += $adj_value/6;
                            $mp_data['data'][$display_num]['table_total']['D　金額（手入力）']['sub_total'] += $adj_value/12;
                            
                            $mp_data['total']['派遣社員人件費合計']['monthly_amt'][$adj_month] += $adj_value;
                            $mp_data['total']['派遣社員人件費合計']['monthly_amt'][$total_col] += $adj_value/6;
                            $mp_data['total']['派遣社員人件費合計']['monthly_amt']['sub_total'] += $adj_value/12;
                        } else {
                            if ($display_num == 3) {
                                $mp_data['data'][$display_num]['table_total']['B　金額（手入力）'][$adj_month] = $adj_value;
                                $mp_data['data'][$display_num]['table_total']['B　金額（手入力）'][$total_col] += $adj_value/6;
                                $mp_data['data'][$display_num]['table_total']['B　金額（手入力）']['sub_total'] += $adj_value/12;
                                
                                $mp_data['data'][$display_num]['table_total']['合計（A+B）'][$adj_month] += $adj_value;
                                $mp_data['data'][$display_num]['table_total']['合計（A+B）'][$total_col] += $adj_value/6;
                                $mp_data['data'][$display_num]['table_total']['合計（A+B）']['sub_total'] += $adj_value/12;
                                
                                $mp_data['total']['社員人件費（小計）']['monthly_amt'][$adj_month] += $adj_value;
                                $mp_data['total']['社員人件費（小計）']['monthly_amt'][$total_col] += $adj_value/6;
                                $mp_data['total']['社員人件費（小計）']['monthly_amt']['sub_total'] += $adj_value/12;
                            }
                            if ($display_num == 0) {
                                $mp_data['total']['社員人件費（手入力）']['monthly_amt'][$adj_month] = $adj_value;
                                $mp_data['total']['社員人件費（手入力）']['monthly_amt'][$total_col] += $adj_value/6;
                                $mp_data['total']['社員人件費（手入力）']['monthly_amt']['sub_total'] += $adj_value/12;
                            }

                            $mp_data['total']['社員人件費（合計）']['monthly_amt'][$adj_month] += $adj_value;
                            $mp_data['total']['社員人件費（合計）']['monthly_amt'][$total_col] += $adj_value/6;
                            $mp_data['total']['社員人件費（合計）']['monthly_amt']['sub_total'] += $adj_value/12;
                        }
                        $mp_data['total']['社員＋派遣人件費合計']['monthly_amt'][$adj_month] += $adj_value;
                        $mp_data['total']['社員＋派遣人件費合計']['monthly_amt'][$total_col] += $adj_value/6;
                        $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['sub_total'] += $adj_value/12;
                    }
                }
            }

            #Set header and footer texts for each table, copied from excel
            $mp_data['data'][1]['header'] = "";
            $mp_data['data'][1]['footer'][0]['text'] = "* 組織所属員数を記入して下さい。総合職新人の本社負担は自動計算されますので、人数をそのまま入力して下さい。";
            $mp_data['data'][1]['footer'][0]['text_color'] = "red";

            $mp_data['data'][2]['header'] = "住商出向者 （上記の内数）";
            $mp_data['data'][2]['footer'][0]['text'] = "* 住商からの出向者の人数を記入して下さい。（上記の内数です）";
            $mp_data['data'][2]['footer'][0]['text_color'] = "";

            $mp_data['data'][3]['header'] = "";
            $mp_data['data'][3]['footer'][0]['text'] = "* 残業時間は組織全体の合計残業時間数を記入してください（一人平均ではありません）。";
            $mp_data['data'][3]['footer'][0]['text_color'] = "";
            $mp_data['data'][3]['footer'][1]['text'] = "* 全社平均単価と乖離が大きい場合、金額欄に直接入力願います。";
            $mp_data['data'][3]['footer'][1]['text_color'] = "red";
            $mp_data['data'][3]['footer'][2]['text'] = "* 時短勤務等により、一ヶ月の所定労働時間を満たす事ができず勤務時間が不足する場合の付替は、不足時間を控除して下さい。";
            $mp_data['data'][3]['footer'][2]['text_color'] = "red";

            $mp_data['data'][4]['header'] = "";
            $mp_data['data'][4]['footer'][0]['text'] = "* 人員数を入力して下さい。";
            $mp_data['data'][4]['footer'][0]['text_color'] = "";
            $mp_data['data'][4]['footer'][1]['text'] = "* 会社平均単価以外の費用を使用する場合は、追加欄に単価を入力の上、人員数を入力して下さい。";
            $mp_data['data'][4]['footer'][1]['text_color'] = "red";


            #set account names for total rows
            $accounts['社員人件費（合計）'] = '俸給諸給与';
            $accounts['派遣社員人件費合計'] = '業務委託費（派遣報酬料）';
            $accounts['社員＋派遣人件費合計'] = 'total';

            #get budget start and end month
            $start_month = $Common->getMonth($target_year, $term_id, 'start');
            $end_month = $Common->getMonth($target_year, $term_id, 'end');

            if (!empty($forecastPeriod) || $forecastPeriod!='') {
                
                #for search total actual result data by loop through account name
                foreach ($accounts as $total_name => $acc_name) {
                    if ($acc_name != 'total') {
                        
                        #get account codes
                        $account_codes = $this->SubAccountModel->find('list', array(
                            'fields' => array(
                                'AccountModel.id','AccountModel.account_code'
                            ),
                            'conditions' => array(
                                'SubAccountModel.flag' => 1,
                                'SubAccountModel.sub_acc_name_jp' => $acc_name,
                                'SubAccountModel.group_code' => '01'
                            ),
                            'joins' => array(
                                array(
                                    'table' => 'tbl_account',
                                    'alias' => 'AccountModel',
                                    'type'  =>  'left',
                                    'conditions' => array(
                                    'SubAccountModel.id = AccountModel.sub_acc_id')
                                )
                            ),
                        ));

                        $account_code = array_values($account_codes);
                        $this->ActualResultSummaryModel->virtualFields['result_total'] = 'SUM(amount)';
                        $resultData = $this->ActualResultSummaryModel->find('list', array(
                            'fields' => array('target_month','result_total'),
                            'conditions' => array(
                                'head_dept_id' => $head_dept_id,
                                'ba_code' => $ba_code,
                                'account_code' => $account_code,
                                'target_month >=' => $start_month,
                                'target_month <=' => $forecastPeriod //$end_month edit by NNL
                            ),
                            'group' => 'target_month'
                        ));

                        $intervalEnd = date("Y-m", strtotime($forecastPeriod. "last day of + 1 Month"));

                        $interval = DateInterval::createFromDateString('1 months') ;
                        $lockPeriods = new DatePeriod(new DateTime($start_month), $interval, new DateTime($intervalEnd)) ;

                        foreach ($lockPeriods as $target_month) {
                            $target_month = $target_month->format('Y-m');
                            $amount = (!empty($resultData[$target_month])) ? $resultData[$target_month] * (-1) : 0;
                            // $amount = $amount * (-1);
                            #(target_month - start_month) + 1
                            $col_name = (int)abs((strtotime($target_month) - strtotime($start_month))/(60*60*24*30)) + 1;
                            #save old value from result array
                            $old_amt = $mp_data['total'][$total_name]['monthly_amt']['month_'.$col_name.'_amt'];
                            #assign new value into result array
                            $mp_data['total'][$total_name]['monthly_amt']['month_'.$col_name.'_amt'] = $amount;
                            $mp_data['freeze']['month_'.$col_name.'_amt'] = 'freeze';
                            $freezeLastMonth = 'month_'.$col_name;
                            #get difference between old and current
                            $diff = $amount - $old_amt;

                            $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['month_'.$col_name.'_amt'] += $diff;

                            if ($col_name < 7) { #first 6 month
                                $mp_data['total'][$total_name]['monthly_amt']['1st_half_total'] += $diff/6;
                                $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['1st_half_total'] += $diff/6;
                            } else { #last 6 month
                                $mp_data['total'][$total_name]['monthly_amt']['2nd_half_total'] += $diff/6;
                                $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['2nd_half_total'] += $diff/6;
                            }
                            #sub total
                            $mp_data['total'][$total_name]['monthly_amt']['sub_total'] += $diff/12;
                            $mp_data['total']['社員＋派遣人件費合計']['monthly_amt']['sub_total'] += $diff/12;

                            if ($total_name == '社員人件費（合計）') {
                                $old_total = $mp_data['total']['社員人件費（小計）']['monthly_amt']['month_'.$col_name.'_amt'];
                                $old_manual = $mp_data['total']['社員人件費（手入力）']['monthly_amt']['month_'.$col_name.'_amt'];
                                $total_diff = $amount - $old_total;
                                $manual_diff = 0 - $old_manual;

                                $mp_data['total']['社員人件費（手入力）']['monthly_amt']['month_'.$col_name.'_amt'] = 0;
                                $mp_data['total']['社員人件費（小計）']['monthly_amt']['month_'.$col_name.'_amt'] += $total_diff;
                                if ($col_name < 7) {
                                    $mp_data['total']['社員人件費（手入力）']['monthly_amt']['1st_half_total'] += $manual_diff/6;
                                    $mp_data['total']['社員人件費（小計）']['monthly_amt']['1st_half_total'] += $total_diff/6;
                                } else {
                                    $mp_data['total']['社員人件費（手入力）']['monthly_amt']['2nd_half_total'] += $manual_diff/6;
                                    $mp_data['total']['社員人件費（小計）']['monthly_amt']['2nd_half_total'] += $total_diff/6;
                                }
                                $mp_data['total']['社員人件費（手入力）']['monthly_amt']['sub_total'] += $manual_diff/12;
                                $mp_data['total']['社員人件費（小計）']['monthly_amt']['sub_total'] += $total_diff/12;
                            }
                        }
                    }
                }
            }

            $budget_year = substr($target_year, 2);

            $getBAName = $this->BusinessAreaModel->find('first', array(
                                                        'fields' => array('ba_name_jp'),
                                                        'conditions'=> array('ba_code'=>$ba_code,'flag'=>1)
        
                                                                ));
            $ba_code = $ba_code.'/'.$getBAName['BusinessAreaModel']['ba_name_jp'];
        
            $cache_name = 'manpower_plan_'.$term_id.'_'.$target_year.'_'.(explode('/', $ba_code))[0].'_'.$loginId;
            $cache_data = array(
                'accounts' => $accounts,
                'mp_data' => $mp_data,
                'approveBA' => $approveBA,
                'budget_year' => $budget_year,
                'deadline_date' => $deadline_date,
                'freezeLastMonth' => $freezeLastMonth,
                'yellow_pos_name' => $yellow_pos_name,
                'field_name_first' => $field_name_first,
                'field_name_second' => $field_name_second,
                'Month_12' => $Month_12,
                'Month_12digit' => $Month_12digit,
                'from_ba_date' => $from_ba_date,
                'budget_term' => $budget_term,
                'ba_code' => $ba_code,
                'target_year' => $target_year,
                'compare_unit_price' => $compare_unit_price,
                'cache_unit' => $cache_unit

            );

            Cache::write($cache_name, $cache_data);
            return $cache_data;
        } else {
            $no_data = "no_data";
            $data = array('no_data' => $no_data);
            return $no_data;
        }
    }

    /**
     * Save
     *
     * @author Nu Nu Lwin (20200810)
     * @throws NotFoundException
     * @param string $id
     * @return data
     */
    public function saveManpower()
    {
        $this->layout = 'phase_3_menu'; #you need to have a no html page, only the data

        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('TARG_YEAR')) { //get Menu bar
            $target_year = $this->Session->read('TARG_YEAR');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        
        $data = $this->request->data;

        $data['filling'] 	= $this->request->data('filling');

        $saveRes = $this->CommonSaveMP($data);

        if (!empty($saveRes['success'])) {
            $this->Flash->set($saveRes['success'], array("key"=>"mp_success"));
        } else if (!empty($saveRes['error'])){
            $this->Flash->set($saveRes['error'], array("key"=>"mp_error"));
        }
        $this->redirect(array('controller'=>'ManpowerPlan/?year='.$target_year,'action'=>'index'));
    }
    /**
     * Manpower plan CommonSave
     *
     * @author Nu Nu Lwin (20210119)
     * @param data
     * @return void
     */
    public function CommonSaveMP($data)
    {
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('TARG_YEAR')) { //get Menu bar
            $target_year = $this->Session->read('TARG_YEAR');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('SESSION_DEADLINE_DATE')) {
            $deadline_date = $this->Session->read('SESSION_DEADLINE_DATE');
        }
        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($this->Session->check('HEAD_DEPT_NAME')) {
            $head_dept_name = $this->Session->read('HEAD_DEPT_NAME');
        }
        $ba_name = $this->Session->read('BUDGET_BA_NAME');
        
        $BudgetPLan = new BudgetPlanController;
       
        $deadline_date = ($deadline_date == '0000-00-00 00:00:00' || empty($deadline_date) || $deadline_date == null) ? '0000-00-00 00:00:00' : date_format(date_create($deadline_date), "Y-m-d H:i:s");

        $filling_date 	= date_format(date_create($data['filling']), "Y-m-d H:i:s");

        $manpower_data 		= $data['manpower'];
        $ot_data 			= $data['manpower_ot'];
        $total_data 		= $data['manpower_total'];
        $subtot_data		= $data['manpower_subtot'];
        $manpower_adjust    = $data['adjustment'];
        $btn_type 	        = $data['btn_type'];
        //khin (change unit price)
        $get_cache_resave = $term_id.'_'.$target_year.'_'.$head_dept_id.'_'.$ba_code;
        if($btn_type != 'Save') {
            $new_uprice = explode('/',$data['hid_new_uprice']);
            foreach($new_uprice as $value) {
                $p_id = explode('_', $value)[2];
                $new_price = explode('_', $value)[4];
                $new_arr[$p_id] = $new_price;
            }

            $new_ot = explode('/',$data['hid_field_rate']);
            foreach($new_ot as $value) {
                $f_id = explode('_', $value)[2];
                $new_otrate = explode('_', $value)[4];
                $new_ot_arr[$f_id] = $new_otrate;
            }
            //NuNu (resave for budget prime table)
            Cache::write($get_cache_resave, 'ReSave');
            
        }else{
            Cache::write($get_cache_resave, '');
        }
        
        $save_data = array();
        $delete_id = array();
        $modelName = '';
        $dbColName = '';

        $sumModelName = '';
        $sumFunctName = '';

        $term_1st_year = substr($budget_term, 0, 4);
        if ($target_year == $term_1st_year) {
            $modelName = 'ExpectedModel';
            $dbColName = 'forecast';
            $sumModelName = 'ForecastSummaryModel';
        } else {
            $modelName = 'BudgetPrimeModel';
            $dbColName = 'budget';
            $sumModelName = 'BudgetSummaryModel';
        }
        
        #prepare save adjustment total data for tbl_manpower_plan
        foreach ($manpower_adjust as $display_no => $adjustData) {
            $tmp = array();

            $tmp['term_id'] 		= $term_id;
            $tmp['target_year'] 	= $target_year;
            $tmp['ba_code'] 		= $ba_code;
            $tmp['type'] 			= Setting::MP_TYPE_ADJUST; //2
            $tmp['display_no'] 		= $display_no;
            $tmp['unit_salary'] 	= 0;
            $tmp['position_mp_id'] 	= 0;
            $tmp['filling_date'] 	= $filling_date;
            $tmp['deadline_date'] 	= $deadline_date;//'';
            $tmp['flag'] 			= 1;
            $tmp['created_id'] 		= $loginId;
            $tmp['updated_id'] 		= $loginId;

            foreach ($adjustData['adjust'] as $col => $amount) {
                $amount = str_replace(',', '', $amount);
                $tmp[$col] = ($display_no == 0) ? $amount*1000 : $amount;
            }

            # assign to newly save data
            $save_data['ManpowerPlanModel']['save'][] = $tmp;
        }
        
        #prepare save data for tbl_manpower_plan
        foreach ($manpower_data as $field_id => $field_data) {
            $tmp = array();

            foreach ($field_data as $position_id => $pos_data) {
                $tmp['term_id'] 	= $term_id;
                $tmp['target_year'] = $target_year;
                $tmp['ba_code'] 	= $ba_code;
                $tmp['type'] 		= Setting::MP_TYPE_RECORD; //1
                $tmp['display_no'] 	= Setting::MP_TYPE_RECORD;
                $tmp['unit_salary'] = str_replace(',', '', $pos_data['unit_salary']);
                
                //khin (change unit price)
                if($new_arr[$position_id]){
                    $tmp['unit_salary'] = str_replace(',','',$new_arr[$position_id]);
                }else {
                    $tmp['unit_salary'] = str_replace(',','',$pos_data['unit_salary']);
                }
                $tmp['position_mp_id'] = $position_id;

                foreach ($pos_data['monthly_amt'] as $col => $amount) {
                    $tmp[$col] = str_replace(',', '', $amount);
                }
                $tmp['filling_date'] = $filling_date;
                $tmp['deadline_date'] = $deadline_date;//'';
                $tmp['flag'] = 1;
                $tmp['created_id'] = $loginId;
                $tmp['updated_id'] = $loginId;

                # assign to newly save data
                $save_data['ManpowerPlanModel']['save'][] = $tmp;
                $save_data['ManpowerPlanModel']['delete'] = array(
                    'term_id' => $term_id,
                    'target_year' => $target_year,
                    'ba_code' => $ba_code
                );
            }
        }

        #prepare save data for tbl_manpower_plan_ot
        foreach ($ot_data as $field_id => $monthly_amt) {
            $tmp = array();
            $tmp['term_id'] 	= $term_id;
            $tmp['target_year'] = $target_year;
            $tmp['ba_code'] = $ba_code;
            $tmp['field_id'] = $field_id;
            //khin (change ot rate in field)
            if($new_ot_arr[$field_id]){
                $tmp['overtime_rate'] = str_replace(',','',$new_ot_arr[$field_id]);
            }else {
                $tmp['overtime_rate'] = str_replace(',','',$monthly_amt['overtime_rate']);
            }
            foreach ($monthly_amt['monthly_amt'] as $col => $amount) {
                $tmp[$col] = str_replace(',', '', $amount);
            }
            $tmp['flag'] = 1;
            $tmp['created_id'] = $loginId;
            $tmp['updated_id'] = $loginId;

            # assign to newly save data
            $save_data['ManpowerPlanOtModel']['save'][] = $tmp;
            $save_data['ManpowerPlanOtModel']['delete'] = array(
                'term_id' => $term_id,
                'target_year' => $target_year,
                'ba_code' => $ba_code
            );
        }
        
        #prepare save data for tbl_expected (or) tbl_budget_prime
        foreach ($total_data as $acc_name => $monthly_amt) {
            $tmp = array();
            if ($acc_name != 'total') {
                $account_data = $this->SubAccountModel->find('first', array(
                    'fields' => array(
                        'AccountModel.sub_acc_id','AccountModel.account_code'
                    ),
                    'conditions' => array(
                        'SubAccountModel.flag' => 1,
                        'SubAccountModel.sub_acc_name_jp' => $acc_name,
                        'SubAccountModel.group_code' => '01'
                    ),
                    'joins' => array(
                        array(
                            'table' => 'tbl_account',
                            'alias' => 'AccountModel',
                            'type'  =>  'left',
                            'conditions' => array(
                            'SubAccountModel.id = AccountModel.sub_acc_id')
                        )
                    ),
                ));
                
                $tmp['term_id'] 			= $term_id;
                $tmp['target_year'] 		= $target_year;
                $tmp['ba_code'] 			= $ba_code;
                $tmp['sub_acc_id'] 			= $account_data['AccountModel']['sub_acc_id'];
                $tmp['account_code'] 		= $account_data['AccountModel']['account_code'];
                $tmp['logistic_index_no'] 	= '';
                $tmp['destination'] 		= '';
                $tmp['kpi_unit'] 			= '';
                foreach ($monthly_amt as $col => $amount) {
                    $rm_comma_amount = str_replace(',', '', $amount);
                    $format_amt = number_format($rm_comma_amount, 1);
                    $tmp[$col] 	= str_replace(',', '', $format_amt)*(-1000);
                }
                $tmp['filling_date']		= $filling_date;//'';
                $tmp['trade_filling_date']	= '0000-00-00 00:00:00';//'';
                $tmp['flag'] 				= 1;
                $tmp['type'] 				= '';
                $tmp['created_id'] 			= $loginId;
                $tmp['updated_id'] 			= $loginId;

                # assign to newly save data
                $delete_id[] = $account_data['AccountModel']['sub_acc_id'];
                $save_data[$modelName]['save'][] = $tmp;
                $save_data[$modelName]['delete'] = array(
                    'term_id' => $term_id,
                    'target_year' => $target_year,
                    'ba_code' => $ba_code,
                    'sub_acc_id' => $delete_id
                );
            }
        }
        #if data have both of update and save stage, store update id in array to not to delete.
        $notDeleteID = [];
        #prepare save data for tbl_expected_budget_diff_job
        foreach ($subtot_data as $total_name => $monthly_amt) {
            $tmp = array();
            $fbdExist = $this->ExpectedBudgetDiffJobModel->find('first', array(
                'conditions' => array(
                    'term_id' => $term_id,
                    'target_year' => $target_year,
                    'ba_code' => $ba_code,
                    'field_name_jp' => str_replace('小計', '', $total_name),
                    'type' => $dbColName
                )
            ));
            
            $tmp['amount'] 		= str_replace(',', '', $monthly_amt['sub_total']);
            $tmp['updated_id'] 	= $loginId;
            
            if (!empty($fbdExist)) {
                $dta 			= $fbdExist['ExpectedBudgetDiffJobModel'];
                $tmp['id'] 		= $dta['id'];
                $tmp['created_date'] 	= $dta['created_date'];
                $tmp['factor'] 	= $dta['factor'];
                $save_data['ExpectedBudgetDiffJobModel']['update'][] = $tmp;
                array_push($notDeleteID, $dta['id']);
            } else {
                $tmp['term_id'] 	= $term_id;
                $tmp['target_year'] = $target_year;
                $tmp['ba_code'] 	= $ba_code;
                $tmp['field_name_jp'] = str_replace('小計', '', $total_name);
                $tmp['type'] 		= $dbColName;
                $tmp['created_id'] 	= $loginId;
                
                $save_data['ExpectedBudgetDiffJobModel']['save'][] = $tmp;
            }

            # assign to newly save data
            $save_data['ExpectedBudgetDiffJobModel']['delete'] = array(
                'term_id' => $term_id,
                'target_year' => $target_year,
                'ba_code' => $ba_code,
                'type' => $dbColName,
                'NOT' =>array(
                    'id' => $notDeleteID)

            );
        }

        if (!empty($save_data)) {
            $modelArr = [];
            try {
                
                foreach ($save_data as $model => $data) {
                    $attachDB = $this->$model->getDataSource();
                    $attachDB->begin();
                    array_push($modelArr, $attachDB);

                    if (isset($data['save']) || !empty($data['save'])) {
                        # Delete old data
                        $this->$model->deleteAll($data['delete'], false);
                        #Save new data
                        $this->$model->saveAll($data['save']);
                    }

                    if (isset($data['update']) || !empty($data['update'])) {
                        #Update amount
                        $this->$model->saveMany($data['update']);
                    }
                }

                $manual_tax_ba = Setting::BA_BUDGET_TAX;
                $fixTax = [];
                if (!in_array($ba_code, $manual_tax_ba)) {
                   
                    //$fixTax = $this->fixTaxCalculations($term_id, $filling_date);
                    $fixTax = $BudgetPLan->updateTaxAmount($term_id, $term, $head_dept_id, $headQuarterName, $ba_code, $ba_name, $target_year, $loginId,$modelName);
                   
                } else {
                    $fixTax = true;
                }
                
                $this->$sumModelName->updateSummaryData($ba_code,$term_id,$target_year);
                
                if ($fixTax == true) {
                    foreach ($modelArr as $attachvalue) {
                        $attachvalue->commit();
                    }
                    if($btn_type == 'Save') {

                        $successMsg = parent::getSuccessMsg('SS001');
                        return $data = ['success'=>$successMsg,'error'=>''];
                        
                    }else{
                         return $data = ['success'=>'','error'=>''];
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE003');
                    return $data = ['success'=>'','error'=>$errorMsg];
                }



            } catch (Exception $e) {
                $attachDB->rollback();
                $errorMsg = parent::getErrorMsg('SE003');
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                return $data = ['success'=>'','error'=>$errorMsg];
            }
        } else {
            $errorMsg = parent::getErrorMsg('SE017', 'Save');
            return $data = ['success'=>'','error'=>$errorMsg];
        }
    }

    public function fixTaxCalculations($term_id, $filling_date, $budget_term='', $head_dept_id='', $loginId='', $trade_filling_date='', $ba_code='', $target_year='')
    {
        $Common = new CommonController();

        if ($target_year=='' && $this->Session->check('TARG_YEAR')) { //get Menu bar
            $target_year = $this->Session->read('TARG_YEAR');
        }
        if ($budget_term == '' && $this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($ba_code == '' && $this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($head_dept_id == '' && $this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        if ($loginId == '' && $this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }

        $term_1st_year = substr($budget_term, 0, 4);
        $col = 0;


        if ($target_year == $term_1st_year) {
            $modelName = 'ExpectedModel';
            $dbColName = 'forecast';

            $forecast_period = $this->TermModel->find('first', array(
                'fields'=> array('forecast_period','end_month'),
                'conditions' => array(
                    'id' => $term_id,
                    'flag' => 1,
                ),
            ))['TermModel'];

            $forecast_month = $forecast_period['forecast_period'];
            $end_month = $forecast_period['end_month'];
            $tg_month = date('n', strtotime($forecast_month));
            
            $col = $Common->getMonthColumn($tg_month, $term_id);
        } else {
            $modelName = 'BudgetPrimeModel';
            $dbColName = 'budget';
        }

        $codes_pair = $Common->getCodesPair('03');
        
        $fields_arr = [];
        for ($i=$col+1; $i <= 12; $i++) {
            array_push($fields_arr, 'SUM(month_'.$i.'_amt) As month_'.$i.'_amt');
        }
        
        $month_amt = $this->$modelName->find('all', array(
                'fields' => $fields_arr,
                'conditions' => array(
                    'term_id' => $term_id,
                    'account_code' => $codes_pair['税引前利益'],
                    'ba_code'=>$ba_code,
                    'target_year'=>$target_year,
                )
            ));
        
        $taxValue = $Common->getTaxValue($month_amt['0']['0'], $target_year);
        
        //'社内税金'(Internal tax) of id
        $subAccName = '社内税金';
        $subAccount = $this->HeadDepartmentModel->getSubAccountByHeadQuarter($head_dept_id, $target_year, $subAccName);
        $subacc_id = $subAccount[0]['tbl_account_setup']['sub_acc_id'];
        if (!empty($subacc_id)) {
            $search_tax = $this->$modelName->find('all', array(
                    'conditions' => array(
                        'term_id' => $term_id,
                        'account_code' => $this->getFirstAccountCode($subacc_id),
                        'ba_code'=>$ba_code,
                        'target_year'=>$target_year,
                    )
                ))[0][$modelName];
            
            for ($i=1; $i <= $col; $i++) {
                $taxValue['month_'.$i.'_amt'] = (!empty($search_tax['month_'.$i.'_amt']) ? $search_tax['month_'.$i.'_amt'] : 0);
            }
                    
            //list out sub accounts to update
            $deleteIDArr[] = $subacc_id;

            $tmp['term_id'] 	= $term_id;
            $tmp['target_year'] = $target_year;
            $tmp['ba_code'] 	= $ba_code;
            $tmp['filling_date'] = $filling_date;
            $tmp['sub_acc_id'] 	= $subacc_id;
            $tmp['account_code'] = $this->getFirstAccountCode($subacc_id);
            $tmp['logistic_index_no'] 	= '';
            
            if ($trade_filling_date != '') {
                $tmp['trade_filling_date'] = $trade_filling_date;
            }
            foreach ($taxValue as $field => $amount) {
                $month_amt = preg_replace("/[^-0-9\.]/", "", $amount);
                $tmp[$field] 	= $month_amt;
            }
            $tmp['flag'] = 1;
            $tmp['created_id'] 		= $loginId;
            $tmp['updated_id'] 		= $loginId;

            # assign to newly save data
            $budgetArr[] = $tmp;
            
            if (!empty($budgetArr)) {
                $attachDB = $this->$modelName->getDataSource();
                
                try {
                    $attachDB->begin();
                    # Delete old data
                    $this->$modelName->deleteAll(array(
                        'term_id' => $term_id,
                        'target_year' => $target_year,
                        'ba_code' => $ba_code,
                        'sub_acc_id' => $deleteIDArr
                    ), false);

                    #Save new data
                    $this->$modelName->saveAll($budgetArr);
                    $attachDB->commit();
                } catch (Exception $e) {
                    $attachDB->rollback();
                    
                    $errorMsg = parent::getErrorMsg('SE003');
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    return $data = ['success'=>'','error'=>$errorMsg];
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE017', 'Save');
                return $data = ['success'=>'','error'=>$errorMsg];
            }
            
            return $data = ['success'=>'OK','error'=>''];
        } else {
            return $data = ['success'=>'OK','error'=>''];
        }
    }

    public function getFirstAccountCode($sub_id)
    {
        $account_data = $this->AccountModel->find('first', array(
            'fields' => 'AccountModel.account_code',
            'conditions' => array(
                'AccountModel.sub_acc_id' => $sub_id,
                'AccountModel.flag' => 1
            ),
            'order' => 'AccountModel.account_code ASC'
        ));

        $account_code = $account_data['AccountModel']['account_code'];

        return $account_code;
    }

    /**
     * Manpower plan ExcelDownload
     *
     * @author Nu Nu Lwin (20200810)
     * @throws NotFoundException
     * @return void
     */
    public function ManpowerExcelDownload()
    {
        $deduct_arr = array();

        $Common = new CommonController;
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $loginId 	= $this->Session->read('LOGIN_ID');

        if ($this->Session->check('TERM_NAME')) {
            $budget_term = $this->Session->read('TERM_NAME');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('TARG_YEAR')) {
            $target_year = $this->Session->read('TARG_YEAR');
        }

        if ($this->Session->check('HEAD_DEPT_ID')) {
            $head_dept_id = $this->Session->read('HEAD_DEPT_ID');
        }
        $tmpFileName = 'ManpowerPlan';
        $PHPExcel = $this->PhpExcel;

        $this->DownloadExcel($term_id, $budget_term, $head_dept_id, $ba_code, $target_year, $loginId, $tmpFileName, $PHPExcel);
        
        $this->redirect(array('controller'=>'ManpowerPlan/?year='.$target_year,'action'=>'index'));
    }

    public function DownloadExcel($term_id, $budget_term, $head_dept_id, $ba_code, $target_year, $loginId, $file_name, $PHPExcel, $save_into_tmp=false)
    {
        $Common = new CommonController();

        #get ba_code of from_date (ayezarnikyaw 26102020)
        $from_date = $this->BusinessAreaModel->find('first', array(
                     'fields'	  => array('from_date'),
                     'conditions' => array('ba_code' => $ba_code,
                                            'flag'   => 1)));
        $from_ba_date = $from_date['BusinessAreaModel']['from_date'];
        $from_ba = explode("-", $from_ba_date);

        #Check BA_Code is already approved or not
        $approveBA = $this->BudgetLogModel->find('first', array(
                            'fields'     => array('flag'),
                            'conditions' => array(
                                'term_id'      => $term_id,
                                'head_dept_id' => $head_dept_id,
                                'ba_code'      => $ba_code,
                                'flag'         => '2'
                                        )
                                    ));
        $approveBAflag = $approveBA['BudgetLogModel']['flag'];

        $budget_year = substr($target_year, 2);

        $Month_12 = $Common->get12Month($term_id);

        if (empty($budget_term) || empty($ba_code)) {
            $errorMsg = parent::getErrorMsg('SE073');
            $this->Flash->error($errorMsg);
            $this->redirect(array('controller' => 'TermSelection', 'action' => 'index'));
        }

        #Start Excel Preparation
        $objPHPExcel = $PHPExcel->createWorksheet()->setDefaultFont('ＭＳ Ｐゴシック', 10);
        $sheet 		 = $PHPExcel->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
        $sheet->setShowGridlines(true);
        $objPHPExcel ->setActiveSheetIndex(0);
        
        //$objPHPExcel->setPreCalculateFormulas(FALSE);

        $cell_name = "F1";
        $sheet->getStyle($cell_name)->getFont()->setBold(true);
        $sheet->setTitle('年度人員計画');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $sheet->getStyle("E")->applyFromArray($style);
        $border_double = array(
                'borders' => array(
                        'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_DOUBLE)
                ));
        $border_dash = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                ));
        $aligncenter = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
        );
        $alignleft = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $alignright = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $aligntop = array(
                'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
                ),
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN)
                )
        );
        $negative = array(
                'font'  => array(
                    'color' => array('rgb' => 'FF0000')
                ));

        $textColor =array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'D5F4FF')
                    ));

        $disableColor =array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'F9F9F9')
                    ));

        $yellowColor =array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF99')
                    ));
        
        $monthCol = ['E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S'];

        $sheet->getColumnDimension('A')->setWidth(2);
        $sheet->getColumnDimension('B')->setWidth(3);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension("D")->setWidth(9);
        for ($i=0; $i <15 ; $i++) {
            $sheet->getColumnDimension($monthCol[$i])->setWidth(10);
        }

        $sheet->getProtection()->setPassword('*****');
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setInsertRows(true);
        $sheet->getProtection()->setInsertColumns(true);
        #End Excel Preparation
        #Read Data from Session data
        $cache_name = 'manpower_plan_'.$term_id.'_'.$target_year.'_'.$ba_code.'_'.$loginId;

        $cache_data = Cache::read($cache_name);
        if (!empty($cache_data)) {
            $accounts 		= $cache_data['accounts'];
            $budget_term 	= $cache_data['budget_term'];
            $ba_code 		= $cache_data['ba_code'];
            $mp_data 	    = $cache_data['mp_data'];
            $approveBA      = $cache_data['approveBA'];
            $budget_year    = $cache_data['budget_year'];
            $deadline_date  = $cache_data['deadline_date'];
            $field_name_first   = $cache_data['field_name_first'];
            $field_name_second  = $cache_data['field_name_second'];

            $filling_date = $mp_data['filling_date'];
            if (empty($filling_date)) {
                $filling_date = date('Y/m/d');
            }

            #Excel Title(Manpower Plan)
            $sheet->setCellValue('F1', __($target_year."年度 ").__("年度人員計画"));
            $sheet->mergeCells('F1:L1');
            $sheet->getStyle('F1:L1')->applyFromArray($aligncenter);
        
            #write Budget Term, Business Code and Deadline Date
            $sheet->setCellValue('p1', __("予算期間"));
            $sheet->mergeCells('p1:Q1');
            $sheet->getStyle('p1')->applyFromArray($alignleft);
            $sheet->getStyle('p1:Q1')->applyFromArray($border_dash);
            
            $sheet->setCellValue('p2', __("BAコード"));
            $sheet->mergeCells('p2:Q2');
            $sheet->getStyle('p2')->applyFromArray($alignleft);
            $sheet->getStyle('p2:Q2')->applyFromArray($border_dash);

            $sheet->setCellValue('p3', __("提出期日"));
            $sheet->mergeCells('p3:Q3');
            $sheet->getStyle('p3')->applyFromArray($alignleft);
            $sheet->getStyle('p3:Q3')->applyFromArray($border_dash);
            
            $sheet->setCellValue('R1', $budget_term);
            $sheet->mergeCells('R1:S1');
            $sheet->getStyle('R1')->applyFromArray($alignright);
            $sheet->getStyle('R1:S1')->applyFromArray($border_dash);
            $sheet->getStyle('R1:S1')->applyFromArray($textColor);

            $sheet->setCellValue('R2', $ba_code);
            $sheet->mergeCells('R2:S2');
            $sheet->getStyle('R2')->applyFromArray($alignright);
            $sheet->getStyle('R2:S2')->applyFromArray($border_dash);
            $sheet->getStyle('R2:S2')->applyFromArray($textColor);

            $sheet->setCellValue('R3', $deadline_date);
            $sheet->mergeCells('R3:S3');
            $sheet->getStyle('R3')->applyFromArray($alignright);
            $sheet->getStyle('R3:S3')->applyFromArray($border_dash);
            $sheet->getStyle('R3:S3')->applyFromArray($textColor);
            
            $excel_row = 5;
            # Title
            
            $sheet->setCellValue('B'.$excel_row, __("人員計画")."（".$target_year.__("年度"."）"));
            $sheet->mergeCells('B'.$excel_row.':D'.($excel_row+1));
            $sheet->getStyle('B'.$excel_row.':D'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->getFont()->setBold(true);

            $sheet->setCellValue('F'.$excel_row, __("水色セル内を入力して下さい"));
            $sheet->mergeCells('F'.$excel_row.':I'.$excel_row);
            $sheet->getStyle('F'.$excel_row.':I'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('F'.$excel_row.':I'.$excel_row)->applyFromArray($textColor);

            $e_row_array = [];
            foreach ($mp_data['data'] as $display_no => $manpower):

                $excel_row += 3;
                
            $sheet->getStyle('B:S')->getAlignment()->setWrapText(true);

            if ($manpower['header'] != ''):

                    $sheet->getStyle('E'.$excel_row.':S'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('E'.($excel_row+1).':S'.($excel_row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

            $sheet->setCellValue('B'.$excel_row, __($manpower['header']));
            $sheet->mergeCells('B'.$excel_row.':D'.($excel_row+1));
            $sheet->getStyle('B'.$excel_row.':D'.($excel_row+1))->applyFromArray($aligncenter); else:

                    $sheet->getStyle('D'.$excel_row.':S'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('D'.($excel_row+1).':S'.($excel_row+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');

            $sheet->setCellValue('D'.$excel_row, __("単価"));
            $sheet->getStyle('D'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('D'.$excel_row)->applyFromArray($border_dash);

            endif;

            $sheet->setCellValue('E'.$excel_row, $budget_year.__("年度上期"));
            $sheet->mergeCells('E'.$excel_row.':J'.$excel_row);
            $sheet->getStyle('E'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('E'.$excel_row.':J'.$excel_row)->applyFromArray($border_dash);

            $sheet->setCellValue('K'.$excel_row, __("上期"));
            $sheet->getStyle('K'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('K'.$excel_row)->applyFromArray($border_dash);

            $sheet->setCellValue('L'.$excel_row, $budget_year.__("年度下期"));
            $sheet->mergeCells('L'.$excel_row.':Q'.$excel_row);
            $sheet->getStyle('L'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('L'.$excel_row.':Q'.$excel_row)->applyFromArray($border_dash);

            $sheet->setCellValue('R'.$excel_row, __("下期"));
            $sheet->getStyle('R'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('R'.$excel_row)->applyFromArray($border_dash);

            $sheet->setCellValue('S'.$excel_row, __("年度"));
            $sheet->getStyle('S'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('S'.$excel_row)->applyFromArray($border_dash);

            if ($manpower['header'] == ''):
                    $sheet->setCellValue('D'.($excel_row+1), __("円/人月"));
            $sheet->getStyle('D'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('D'.($excel_row+1))->applyFromArray($border_dash);
            endif;

            $sheet->setCellValue('E'.($excel_row+1), __($Month_12[0]));
            $sheet->getStyle('E'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('E'.($excel_row+1))->applyFromArray($border_dash);
               
            $sheet->setCellValue('F'.($excel_row+1), __($Month_12[1]));
            $sheet->getStyle('F'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('F'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('G'.($excel_row+1), __($Month_12[2]));
            $sheet->getStyle('G'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('G'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('H'.($excel_row+1), __($Month_12[3]));
            $sheet->getStyle('H'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('H'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('I'.($excel_row+1), __($Month_12[4]));
            $sheet->getStyle('I'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('I'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('J'.($excel_row+1), __($Month_12[5]));
            $sheet->getStyle('J'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('J'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('K'.($excel_row+1), __("平均"));
            $sheet->getStyle('K'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('K'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('L'.($excel_row+1), __($Month_12[6]));
            $sheet->getStyle('L'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('L'.($excel_row+1))->applyFromArray($border_dash);
               
            $sheet->setCellValue('M'.($excel_row+1), __($Month_12[7]));
            $sheet->getStyle('M'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('M'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('N'.($excel_row+1), __($Month_12[8]));
            $sheet->getStyle('N'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('N'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('O'.($excel_row+1), __($Month_12[9]));
            $sheet->getStyle('O'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('O'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('P'.($excel_row+1), __($Month_12[10]));
            $sheet->getStyle('P'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('P'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('Q'.($excel_row+1), __($Month_12[11]));
            $sheet->getStyle('Q'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('Q'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('R'.($excel_row+1), __("平均"));
            $sheet->getStyle('R'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('R'.($excel_row+1))->applyFromArray($border_dash);

            $sheet->setCellValue('S'.($excel_row+1), __("平均"));
            $sheet->getStyle('S'.($excel_row+1))->applyFromArray($aligncenter);
            $sheet->getStyle('S'.($excel_row+1))->applyFromArray($border_dash);

            $alpharbat = ['E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S'];

            $col_12 = ['E','F','G','H','I','J','L','M','N','O','P','Q'];
            $digit_12 = $Common->get12DigitMonth($term_id);
            $start = 0;
                
            foreach ($digit_12 as $key => $month) {
                $ba_months[$month] = $col_12[$start];
                if ($month == 12) {
                    break;
                }
                $start++;
            }

            #Start main table header
            foreach ($manpower['table_data'] as $field_name => $field_data):
                    
                    $excel_row += 1;
                
            $rowcount = (count($field_data['sub_data'])+$excel_row);
            $sheet->setCellValue('B'.($excel_row+1), $field_name);
            if (($excel_row+1) < $rowcount) {
                $sheet->mergeCells('B'.($excel_row+1).':B'.$rowcount);
            }
            $sheet->getStyle('B'.($excel_row+1).':B'.$rowcount)->applyFromArray($alignleft);
            $sheet->getStyle('B'.($excel_row+1).':B'.$rowcount)->applyFromArray($border_dash);
                    
            $poscnt = 0;

            foreach ($field_data['sub_data'] as $position_name => $position_data):

                        $rowcount = count($field_data['sub_data']);
            $excel_row += 1;
            if ($poscnt == 0) { #For formula
                $s_row = ($field_name == $field_name_second)? '10' : $excel_row;
            }
            $poscnt ++;
                        
            if ($field_name == $position_name):

                            $sheet->setCellValue('B'.$excel_row, $field_name);
            $sheet->mergeCells('B'.$excel_row.':C'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':C'.$excel_row)->applyFromArray($alignleft);
            $sheet->getStyle('B'.$excel_row.':C'.$excel_row)->applyFromArray($border_dash); else:
                            $pos = $position_name;
            $position_name = (($ba_code == '8003/人事部' || $ba_code == '8003') && strpos($position_name, '（新人）') == true) ? str_replace('（新人）', '（新人・他部署）', $pos) : $pos;
                            
            $sheet->setCellValue('C'.$excel_row, $position_name);
            endif;

            if (strpos($position_name, '新人') == true) {
                #get the name between （）
                preg_match('~（(.*?)）~', $position_name, $rep_name);

                $deduct_arr[$display_no][$rep_name[1]]['price_col'] = 'D';
                $deduct_arr[$display_no][$rep_name[1]]['row_no'] = $excel_row;
                $deduct_arr[$display_no][$rep_name[1]]['first_half_prc'] = $position_data['percentage']['first_half'];
                $deduct_arr[$display_no][$rep_name[1]]['secnd_half_prc'] = $position_data['percentage']['secnd_half'];
            }

            $unit_price = str_replace(',', '', $position_data['unit_price']);
            if ($display_no != '2') {
                $sheet->setCellValue('D'.$excel_row, $unit_price);
                $sheet->getStyle('D'.$excel_row.':D'.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle('D'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);
                $sheet->getStyle('D'.$excel_row.':D'.$excel_row)->getNumberFormat()->setFormatCode('""#,##;[Red]"-"#,##');//('#,##');
                if ($position_data['edit_permit'] == true):

                                $sheet->getStyle('D'.$excel_row.':D'.$excel_row)->applyFromArray($yellowColor);
                $sheet->getStyle('D'.$excel_row.':D'.$excel_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                endif;

                $sheet->getStyle('C'.$excel_row.':C'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('C'.$excel_row.':C'.$excel_row)->applyFromArray($border_dash);
            } else {
                $sheet->mergeCells('C'.$excel_row.':D'.$excel_row);
                $sheet->getStyle('C'.$excel_row.':D'.$excel_row)->applyFromArray($alignleft);
                $sheet->getStyle('C'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);
            }
                        
            $i = 0;
            foreach ($position_data['monthly_amt'] as $month_col => $month_amt):
                            $percent = ($monthCnt < 8) ? $position_data['percentage']['first_half'] : $position_data['percentage']['secnd_half'];
            $month_amt = (!empty($month_amt) || $month_amt != '') ? $month_amt : '0.00';
                            
            $column = $alpharbat[$i];
                            
            if ($month_col != '1st_half_total' && $month_col != '2nd_half_total' && $month_col != 'sub_total'):
                                
                                $sheet->setCellValue($column.$excel_row, $month_amt); else:
                                $letter = chr(ord($column) - 1);
                                
            $formula = ($month_col == '1st_half_total')? '=SUM('.$alpharbat[0].$excel_row.':'.$letter.$excel_row.')/6' : (($month_col == '2nd_half_total')? '=SUM('.$alpharbat[7].$excel_row.':'.$letter.$excel_row.')/6' : (($month_col == 'sub_total')? '=SUM('.$alpharbat[6].$excel_row.','.$alpharbat[13].$excel_row.')/2' : ''));
                                
            $sheet->setCellValue($column.$excel_row, $formula);
                                
            endif;
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($alignright);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($border_dash);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');//('#,##0.00');

            if ($column != 'K' && $column != 'R' && $column != 'S') {
                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($textColor);

                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
            }
            #approve ba_code
            if ($approveBAflag == 2) {
                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($disableColor);
                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
            }
            $i++;
            endforeach;
            #months columns will be disable when ba of from_date is not active
            foreach ($ba_months as $key => $value):
                            if ($from_ba[1] > $key && $from_ba[0] == $target_year) {
                                $sheet->getStyle($value.$excel_row.':'.$value.$excel_row)->applyFromArray($disableColor);
                                $sheet->getStyle($value.$excel_row.':'.$value.$excel_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                            }
            endforeach;
            endforeach;
                    
            foreach ($field_data['sub_total'] as $total_name => $total_value):
                        $e_row = $excel_row; //For total formula
            array_push($e_row_array, $e_row+1);
                        
            $excel_row += 1;
            $sheet->setCellValue('B'.$excel_row, $total_name);
            $sheet->mergeCells('B'.$excel_row.':D'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);
            $i = 0;
            foreach ($total_value as $tmonth_col => $tmonth_amt):

                            $tmonth_amt = (!empty($tmonth_amt) || $tmonth_amt != '') ? $tmonth_amt : '0.00';
                            
            $column = $alpharbat[$i];
            $sheet->setCellValue($column.$excel_row, $tmonth_amt);
                            
            $sheet->setCellValue($column.$excel_row, '=SUM('.$column.$s_row.':'.$column.$e_row.')');
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($alignright);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($border_dash);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            $i++;
            endforeach;
                        
            endforeach;

            $excel_row -= 1;
            endforeach;
            $k = 0;
            foreach ($manpower['table_total'] as $tname => $tvalue):
                    
                    $k++;
            if ($k >= '2') {
                $excel_row +=1;
            } else {
                $excel_row +=2;
            }

            $sheet->setCellValue('B'.$excel_row, $tname);
            $sheet->mergeCells('B'.$excel_row.':D'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);
            $i = 0;
                    
            foreach ($tvalue as $tmonth => $tamt):
                        
                        $tamt = (!empty($tamt) || $tamt != '') ? $tamt : '0.00';

            $column = $alpharbat[$i];

            if (($display_no == 3 || $display_no == 4) && strpos($tname, '手入力')==true && $tmonth != '1st_half_total' && $tmonth != '2nd_half_total' && $tmonth != 'sub_total'):
                            
                            $sheet->setCellValue($column.$excel_row, $tamt);
                            
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($yellowColor);
            $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED); else:

                            if ($tname == '出向者小計' || $tname == '派遣社員小計') {
                                $e_row_formula = '=SUM('.$column.$s_row.':'.$column.($excel_row-1).')';
                            } elseif ($tname == '社員合計') {
                                $e_row_formula = '=SUM('.$column.implode(",".$column, $e_row_array).')';
                            } elseif ($tname == 'A　金額（小計）' || $tname == 'C　金額（小計）') {
                                $e_row_formula = '=SUMPRODUCT(D'.$s_row.':D'.($excel_row-1).','.$column.$s_row.':'.$column.($excel_row-1).')';
                            } elseif ($tname == '合計（A+B）') {
                                $e_row_formula = '=SUM('.$column.($excel_row-2).':'.$column.($excel_row-1).')';
                            }
            $letter = chr(ord($column) - 1);
            $e_row_formula = ($tmonth == '1st_half_total')? '=SUM('.$alpharbat[0].$excel_row.':'.$letter.$excel_row.')/6' : (($tmonth == '2nd_half_total')? '=SUM('.$alpharbat[7].$excel_row.':'.$letter.$excel_row.')/6' : (($tmonth == 'sub_total')? '=SUM('.$alpharbat[6].$excel_row.','.$alpharbat[13].$excel_row.')/2' : $e_row_formula));

            $sheet->setCellValue($column.$excel_row, $e_row_formula);
                            
            if ($display_no == 4):
                                $total_staff_row = $excel_row; elseif ($display_no == 3):
                                
                                $total_emp_row = $excel_row;
            endif;

            endif;

            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($alignright);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($border_dash);
                      
            if ($display_no == 1) {
                $sheet->getStyle('B'.$excel_row.':'.'S'.$excel_row)->applyFromArray($border_double);
            }
            if ($display_no == 1 || $display_no == 2) {
                $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
            } elseif ($display_no == 3) {
                $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('#,##0;[Red]"-"#,##0');//('#,##0');
            } elseif ($display_no == 4) {
                $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('#,##0;[Red]"-"#,##0');//('#,##0');
                if ($tname == '派遣社員小計') {
                    $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.00;[Red]"-"#,##0.00');
                }
            }
            $i++;
                        
            endforeach;

            endforeach;
                
            #End table header
            #Star table footer

            foreach ($manpower['footer'] as $footer):
                    $excel_row += 1;
            $sheet->setCellValue('B'.$excel_row, $footer['text']);
            $sheet->mergeCells('B'.$excel_row.':O'.$excel_row);
            if ($footer['text_color'] == 'red') {
                $sheet->getStyle('B'.$excel_row.':O'.$excel_row)->applyFromArray($negative);
            }
            endforeach;
            #End table footer
            endforeach;
            
            $excel_row += 2;
            $sheet->getStyle('E'.$excel_row.':S'.$excel_row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');
            $sheet->getStyle('E:S')->getAlignment()->setWrapText(true);
            $sheet->setCellValue('B'.$excel_row, __("人件費合計 （単位 千円）"));
            $sheet->getStyle('B'.$excel_row)->applyFromArray($aligncenter);
            $sheet->mergeCells('B'.$excel_row.':D'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->getFont()->setBold(true);

            $sheet->setCellValue('E'.($excel_row), __($Month_12[0]));
            $sheet->getStyle('E'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('E'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('F'.($excel_row), __($Month_12[1]));
            $sheet->getStyle('F'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('F'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('G'.($excel_row), __($Month_12[2]));
            $sheet->getStyle('G'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('G'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('H'.($excel_row), __($Month_12[3]));
            $sheet->getStyle('H'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('H'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('I'.($excel_row), __($Month_12[4]));
            $sheet->getStyle('I'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('I'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('J'.($excel_row), __($Month_12[5]));
            $sheet->getStyle('J'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('J'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('K'.($excel_row), __("上期").__("平均"));
            $sheet->getStyle('K'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('K'.($excel_row))->applyFromArray($border_dash);
           
            $sheet->setCellValue('L'.($excel_row), __($Month_12[6]));
            $sheet->getStyle('L'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('L'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('M'.($excel_row), __($Month_12[7]));
            $sheet->getStyle('M'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('M'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('N'.($excel_row), __($Month_12[8]));
            $sheet->getStyle('N'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('N'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('O'.($excel_row), __($Month_12[9]));
            $sheet->getStyle('O'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('O'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('P'.($excel_row), __($Month_12[10]));
            $sheet->getStyle('P'.($excel_row))->applyFromArray($aligncenter);
            $sheet->getStyle('P'.($excel_row))->applyFromArray($border_dash);

            $sheet->setCellValue('Q'.$excel_row, __($Month_12[11]));
            $sheet->getStyle('Q'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('Q'.$excel_row)->applyFromArray($border_dash);

            $sheet->setCellValue('R'.$excel_row, __("下期").__("平均"));
            $sheet->getStyle('R'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('R'.$excel_row)->applyFromArray($border_dash);

            $sheet->setCellValue('S'.$excel_row, __("年度").__("平均"));
            $sheet->getStyle('S'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('S'.$excel_row)->applyFromArray($border_dash);
            
            foreach ($mp_data['total'] as $tname => $tvalue):
                $tot_acc_name = ($tname== '派遣社員人件費合計') ? $tname.'（C＋D）' : $tname;
            if ($tname != '社員人件費（手入力）' && $tname != '社員人件費（合計）'):

                    ($tname == '社員＋派遣人件費合計')? $excel_row+=1 : $excel_row+=2;

            $sheet->setCellValue('B'.$excel_row, $tot_acc_name);
            $sheet->mergeCells('B'.$excel_row.':D'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);
                    
            $i = 0;

            foreach ($tvalue['monthly_amt'] as $tmonth => $tamt):

                        $tamt = (!empty($tamt) || $tamt != '') ? $tamt/1000 : '0.0';
                        
            $column = $alpharbat[$i];
            if ($tmonth != '1st_half_total' && $tmonth != '2nd_half_total' && $tmonth != 'sub_total') {
                if ($tname == '派遣社員人件費合計'):

                                $formula = "=SUM(".$column.($total_staff_row-1).":".$column.$total_staff_row.")/1000"; elseif ($tname == '社員人件費（小計）'):

                                $formula = "=(SUMPRODUCT(D10:D"."))/1000";
                $k = 0;
                $formula_cal = [];
                foreach ($e_row_array as $value) {
                    $value_row = $value-1;
                    $prv_row = $e_row_array[($k-1)]+1;
                    ($k == 0)? array_push($formula_cal, 'SUMPRODUCT(D10:D'.$value_row.','.$column.'10:'.$column.$value_row.')') : array_push($formula_cal, 'SUMPRODUCT(D'.$prv_row.':D'.$value_row.','.$column.$prv_row.':'.$column.$value_row.')');
                    $k++;
                }
                $sub_formula = '';
                                
                foreach ($deduct_arr as $disp_no => $value) {
                    foreach ($value as $detuct_name => $deduct_data) {
                        $prc_amt = ($i < 7) ? $deduct_data['first_half_prc'] : $deduct_data['secnd_half_prc'];
                        $r_no = ($disp_no == 1) ? $deduct_data['row_no'] : $deduct_arr[1][$detuct_name]['row_no'] ;
                        if ($prc_amt < 1) {
                            $sub_formula .= "-(".$deduct_data['price_col'].$r_no."*".$column.$deduct_data['row_no']."*".$prc_amt.")";
                        }

                        if (count($value)==1 && $disp_no==1 && $i<7) {
                            $sub_formula .= "-(".$deduct_data['price_col'].$r_no."*".$column.$deduct_data['row_no'].")";
                        }
                    }
                }
                $formula = "=(".implode('+', $formula_cal)."+SUM(".$column.$total_emp_row.")".$sub_formula.")/1000"; elseif ($tname == '社員＋派遣人件費合計'):
                                $formula = "=SUM(".$column.($excel_row-4).":".$column.($excel_row-2).")";

                endif;

                if ($mp_data['freeze'][$tmonth] == 'freeze') {
                    $sheet->setCellValue($column.$excel_row, $tamt);
                    $sheet->getStyle($column.$excel_row)->applyFromArray($disableColor);
                    $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                } else {
                    $sheet->setCellValue($column.$excel_row, $formula);
                }
                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($alignright);
                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($border_dash);
                $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            } else {
                $letter = chr(ord($column) - 1);

                $e_row_formula = ($tmonth == '1st_half_total')? '=SUM('.$alpharbat[0].$excel_row.':'.$letter.$excel_row.')/6' : (($tmonth == '2nd_half_total')? '=SUM('.$alpharbat[7].$excel_row.':'.$letter.$excel_row.')/6' : (($tmonth == 'sub_total')? '=SUM('.$alpharbat[6].$excel_row.','.$alpharbat[13].$excel_row.')/2' : $e_row_formula));
                $sheet->setCellValue($column.$excel_row, $e_row_formula);
                $sheet->getStyle($column.$excel_row)->applyFromArray($border_dash);
                $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            }
                        
            $i++;
            endforeach;

            $excel_row++;

            if ($tname == '社員人件費（小計）'):
                        
                        $sheet->setCellValue('B'.$excel_row, '社員人件費（手入力）');
            $sheet->mergeCells('B'.$excel_row.':D'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);

            $i = 0;

            foreach ($mp_data['total']['社員人件費（手入力）']['monthly_amt'] as $tmonth => $tamt):
                            
                            $tamt = (!empty($tamt) || $tamt != '') ? $tamt/1000 : '0.0';
                            
            $column = $alpharbat[$i];
            if ($tmonth != '1st_half_total' && $tmonth != '2nd_half_total' && $tmonth != 'sub_total') {
                if ($mp_data['freeze'][$tmonth] == 'freeze') {
                    $sheet->setCellValue($column.$excel_row, '0.00');
                    $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($disableColor);
                    $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
                } else {
                    $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($yellowColor);
                    $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                }
                $sheet->setCellValue($column.$excel_row, $tamt);
            } else {
                $letter = chr(ord($column) - 1);
                $e_row_formula = ($tmonth == '1st_half_total')? '=SUM('.$alpharbat[0].$excel_row.':'.$letter.$excel_row.')/6' : (($tmonth == '2nd_half_total')? '=SUM('.$alpharbat[7].$excel_row.':'.$letter.$excel_row.')/6' : (($tmonth == 'sub_total')? '=SUM('.$alpharbat[6].$excel_row.','.$alpharbat[13].$excel_row.')/2' : $e_row_formula));
                $sheet->setCellValue($column.$excel_row, $e_row_formula);
            }
                            
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($alignright);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($border_dash);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');

            $i++;
            endforeach;
                        
            $excel_row++;
            $sheet->setCellValue('B'.$excel_row, '社員人件費（合計）');
            $sheet->mergeCells('B'.$excel_row.':D'.$excel_row);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($aligncenter);
            $sheet->getStyle('B'.$excel_row.':D'.$excel_row)->applyFromArray($border_dash);

            $i = 0;

            foreach ($mp_data['total']['社員人件費（合計）']['monthly_amt'] as $tmonth => $tamt):
                            $tamt = (!empty($tamt) || $tamt != '') ? $tamt/1000 : '0.0';
                            
            $column = $alpharbat[$i];
            $formula = "=SUM(".$column.($excel_row-2).":".$column.($excel_row-1).")";
            if ($mp_data['freeze'][$tmonth] == 'freeze') {
                $sheet->setCellValue($column.$excel_row, $tamt);
                $sheet->getStyle($column.$excel_row)->applyFromArray($disableColor);
                $sheet->getStyle($column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            } else {
                $sheet->setCellValue($column.$excel_row, $formula);
            }
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($alignright);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->applyFromArray($border_dash);
            $sheet->getStyle($column.$excel_row.':'.$column.$excel_row)->getNumberFormat()->setFormatCode('""#,##0.0;[Red]"-"#,##0.0');
            $i++;
            endforeach;

            endif;
            endif;
                
            endforeach;
            
            $this->autoLayout = false;

            if ($save_into_tmp) {
                $PHPExcel->save($file_name);
            } else {
                $PHPExcel->output($file_name.".xlsx");
            }
        } else {
            if (!$save_into_tmp) {
                $this->redirect(array('controller' => 'TermSelection', 'action' => 'index'));
            }else{
                $PHPExcel->save($file_name);
            }
        }

    }

    /**
     * Manpower plan
     *
     * @author Pan Ei Phyo (20200922)
     * @param target_year
     * @return position data
     */
    public function getPositionData($target_year, $ba_code)
    {
        $not_condition = array();
        if ($ba_code != '8003') { //8003 = HR Dept
            $not_condition = array(
                'PositionMpModel.position_name_jp' => array('3級（新人・人事部）','総合職（新人・人事部）')
            );
        }

        $data = $this->PositionMpModel->find('all', array(
            'fields' => array(
                'FieldModel.field_name_jp','FieldModel.overtime_rate','FieldModel.id as field_id',
                'PositionMpModel.position_name_jp','PositionMpModel.unit_salary','PositionMpModel.id','PositionMpModel.display_no','PositionMpModel.edit_flag','PositionMpModel.percentage'
            ),
            'conditions' => array(
                'PositionMpModel.flag' => 1,
                'PositionMpModel.target_year' => $target_year,
                'FieldModel.target_year'=> $target_year,
                'NOT' => $not_condition
            ),
            'order' => array(
                'PositionMpModel.display_no ASC',
                'PositionMpModel.field_id ASC',
                'PositionMpModel.id ASC'
            )
        ));
        
        return $data;
    }
    
    /**
     * Manpower plan excel import save
     *
     * @author Nu Nu Lwin (20210119)
     * @throws NotFoundException
     * @return void
     */
    public function saveUploadFile()
    {
        App::import('Vendor', 'php-excel-reader/PHPExcel');

        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        if ($this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($this->Session->check('TARG_YEAR')) { //get Menu bar
            $target_year = $this->Session->read('TARG_YEAR');
        }
        if ($this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }

        $file 			= $this->request->params['form']['manpower_upload'];
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
                $sheet  	   = $objPHPExcel->getActiveSheet();

                $highestRow    = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $chkHeader 	= $sheet->getCell("F1")->getValue();
                $chkTerm 	= $sheet->getCell("R1")->getValue();
                $chkBA 		= $sheet->getCell("R2")->getValue();
                $chkDLDate 	= $sheet->getCell("R3")->getValue();

                $cache_name = 'manpower_plan_'.$term_id.'_'.$target_year.'_'.$ba_code.'_'.$loginId;

                $cache_data = Cache::read($cache_name);
                
                if (!empty($cache_data)) {
                    $budget_term 	= $cache_data['budget_term'];
                    $ba_code 		= $cache_data['ba_code'];
                    $deadline_date 	= $cache_data['deadline_date'];
                } else {
                    $this->redirect(array('controller' => 'TermSelection', 'action' => 'index'));
                    CakeLog::write('debug', 'Empty cache data. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
                
                $arr_row = array(); #to check user input formula function
                if (($chkHeader ==  __($target_year."年度 ").__("年度人員計画")) && ($chkTerm == $budget_term) && ($chkBA == $ba_code) && ($chkDLDate == $deadline_date)) {
                    for ($row = 8; $row <= $highestRow; $row++) {
                        $rowData = $sheet->rangeToArray('B' . $row . ':' . 'S' . $row, null, true, false);
                        $worksheets[] = $rowData;

                        $row_data = $sheet->getRowIterator($row)->current();
                        $cellIterator = $row_data->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);
                        $k = 'A';
                        foreach ($cellIterator as $cell) {
                            $cellcheck = substr($cell->getValue(), 0, 1);
                            
                            if ($cellcheck == '=') {
                                $cell_content = $cell->getOldCalculatedValue();
                                if (substr($cell_content, 0, 1) == "#") {
                                    $notMatchErr = parent::getErrorMsg('SE099', [__('9 digits with 2 digits decimal point'),$k,$row]);
                                    $this->Flash->set($notMatchErr, array("key"=>"mp_error"));
                                    $this->redirect(array('controller'=>'ManpowerPlan/?year='.$target_year,'action'=>'index'));
                                }
                            }
                            
                            array_push($arr_row, $cell_content);
                            $k++;
                        }
                    }
                    //pr($worksheets);die();
                    $getExcelData = $this->checkHeaderAndColumn($worksheets);
                    
                    if (empty($getExcelData['error'])) {
                        #prepare to save data
                        $saveRes = $this->CommonSaveMP($getExcelData['success']);
                        
                        if (!empty($saveRes['success'])) {
                            $this->Flash->set($saveRes['success'], array("key"=>"mp_success"));
                        } else {
                            $this->Flash->set($saveRes['error'], array("key"=>"mp_error"));
                            CakeLog::write('debug', 'Error occur at Save function. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    } else {
                        $this->Flash->set($getExcelData['error'], array("key"=>"mp_error"));
                        CakeLog::write('debug', 'table header and column are match. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }
                } else {
                    $chkTBD = ($chkTerm != $budget_term)? __('Budget Term'): (($chkBA != $ba_code)? __('Business Code') : (($chkDLDate != $deadline_date)? __('Deadline Date') :''));

                    if ($chkHeader != __($target_year."年度 ").__("年度人員計画")) {
                        $this->Flash->set(parent::getErrorMsg('SE021'), array("key"=>"mp_error"));
                    } else {
                        $this->Flash->set(parent::getErrorMsg('SE098', $chkTBD), array("key"=>"mp_error"));
                    }
                    
                    CakeLog::write('debug', 'budget_term, ba_code and deadline_date are not match. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
            } else {
                $this->Flash->set(parent::getErrorMsg('SE015'), array("key"=>"mp_error"));
                CakeLog::write('debug', 'cannot read the file. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            }
        } else {
            $this->Flash->set($error, array("key"=>"mp_error"));
            CakeLog::write('debug', '$file[error]!=0 or file size over 1MB or extension of file is not correct. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
        }

        $this->redirect(array('controller'=>'ManpowerPlan/?year='.$target_year,'action'=>'index'));
    }
    /**
     * Check excel import validation and prepare data to save
     *
     * @author Nu Nu Lwin (20210119)
     * @param worksheets
     * @return data
     */
    public function checkHeaderAndColumn($worksheets)
    {
        if ($target_year=='' && $this->Session->check('TARG_YEAR')) { //get Menu bar
            $target_year = $this->Session->read('TARG_YEAR');
        }
        if ($ba_code == '' && $this->Session->check('BUDGET_BA_CODE')) {
            $ba_code = $this->Session->read('BUDGET_BA_CODE');
        }
        if ($loginId == '' && $this->Session->check('LOGIN_ID')) {
            $loginId = $this->Session->read('LOGIN_ID');
        }
        if ($this->Session->check('TERM_ID')) {
            $term_id = $this->Session->read('TERM_ID');
        }
        
        $cache_name = 'manpower_plan_'.$term_id.'_'.$target_year.'_'.$ba_code.'_'.$loginId;

        $cache_data = Cache::read($cache_name);
        
        if (!empty($cache_data)) {
            $mp_data 			= $cache_data['mp_data'];
            $freezeLastMonth 	= $cache_data['freezeLastMonth'];
            $ba_code 			= $cache_data['ba_code'];
            $yellow_pos_name 	= $cache_data['yellow_pos_name'];
        }

        $budget_year 	= substr($target_year, 2);
        $Common 		= new CommonController;
        #check header
        $Month_12 		= $Common->get12Month($term_id);
        
        $org_field_arr 		= [];
        $org_position_arr 	= [];
        $excel_field_arr	= [];
        $excel_position_arr = [];
        $notMatchErr		= '';

        $header_arr1		= [];
        $header_arr2		= [];
        $header_arr3		= [];
        $header_arr4		= [];
        $header_arr5		= [];
        $header_arr6		= [];
        $header_arr7		= [];
        $header_arr8		= [];
        $org_header1 		= [__('単価'),$budget_year.__('年度上期'),__('上期'),$budget_year.__('年度下期'),__('下期'),__('年度')];

        $org_header2		= [__('円/人月'),__($Month_12[0]),__($Month_12[1]),__($Month_12[2]),__($Month_12[3]),__($Month_12[4]),__($Month_12[5]),__('平均'),__($Month_12[6]),__($Month_12[7]),__($Month_12[8]),__($Month_12[9]),__($Month_12[10]),__($Month_12[11]),__('平均'),__('平均')];

        $org_header3 		= ['住商出向者 （上記の内数）',$budget_year.__('年度上期'),__('上期'),$budget_year.__('年度下期'),__('下期'),__('年度')];

        $org_header4		= [__($Month_12[0]),__($Month_12[1]),__($Month_12[2]),__($Month_12[3]),__($Month_12[4]),__($Month_12[5]),__('平均'),__($Month_12[6]),__($Month_12[7]),__($Month_12[8]),__($Month_12[9]),__($Month_12[10]),__($Month_12[11]),__('平均'),__('平均')];


        $manpower_data 	 	= [];
        $ot_data 		 	= [];
        $total_data 	 	= ['俸給諸給与','業務委託費（派遣報酬料）','total'];
        $subtot_data	 	= [];
        $manpower_adjust 	= ['3','4','0']; //display no
        $text1 				= '* 住商からの出向者の人数を記入して下さい。（上記の内数です）';
        $text2				= '* 時短勤務等により、一ヶ月の所定労働時間を満たす事ができず勤務時間が不足する場合の付替は、不足時間を控除して下さい。';
        $alpharbat 			= ['B','C','D','E','F','G','H','I','J','k','L','M','N','O','P','Q'];

        
        foreach ($mp_data['data'] as $display_no => $value) {
            foreach ($value['table_data'] as $field => $field_value) {
                array_push($org_field_arr, $field);
                
                foreach ($field_value['sub_data'] as $position => $position_value) {
                    array_push($org_position_arr, $position);

                    if (!empty($field_value['field_id'])) {
                        $manpower_data[$field_value['field_id']][] = $position_value['position_id'];
                    } else {
                        array_push($ot_data, $position_value['field_id']);
                    }
                }
                foreach ($field_value['sub_total'] as $total => $total_value) {
                    array_push($subtot_data, $total);
                }
            }
        }
        
        array_push($subtot_data, '派遣社員小計');
        
        if (!empty($worksheets)) {
            for ($i=0; $i < count($worksheets); $i++) {
                (!empty($worksheets[$i][0][0]))? array_push($excel_field_arr, $worksheets[$i][0][0]) : '';

                (!empty($worksheets[$i][0][1]))? array_push($excel_position_arr, $worksheets[$i][0][1]) : '';

                if ($worksheets[$i][0][0] == '住商出向者 （上記の内数）') {
                    $loan_staff_thead_pos = $i;
                } elseif ($worksheets[$i][0][0] == $text1) {
                    $OT_thead_pos = $i+3;
                } elseif ($worksheets[$i][0][0] == $text2) {
                    $TS_thead_pos = $i+3;
                }
            }
            
            #Check field name
            #collected all of column B data in import file (excel_field_arr)and field name in session (org_field_arr)
            $intersect_field =  array_values(array_intersect($excel_field_arr, $org_field_arr));
            $diff1 = array_diff($org_field_arr, $intersect_field);
            $diff2 = array_diff($intersect_field, $org_field_arr);
            $check_field = array_merge($diff1, $diff2);
            
            if (empty($check_field)) {
                $intersect_position =  array_values(array_intersect($excel_position_arr, $org_position_arr));
                $diff3 = array_diff($org_position_arr, $intersect_position);
                $diff4 = array_diff($intersect_position, $org_position_arr);
                $check_position = array_merge($diff3, $diff4);
                #if has value in check_position, check this value in $org_field_arr because of can equal field name and position name.
                $sameNameField_Position = array_values(array_intersect($check_position, $org_field_arr));
                
                if (count($sameNameField_Position) != count($check_position)) {

                    #if ba_code = 8003, check 新人
                    
                    if ($ba_code == '8003' || $ba_code == '8003/人事部') {
                        foreach (array_diff($check_position, $sameNameField_Position) as $value) {
                            $notMatchErr = (strpos($value, '（新人）') == true) ? '' : parent::getErrorMsg('SE098', __('Position Name'));
                            if (!empty($notMatchErr)) {
                                $data = ['error'=>$notMatchErr,'success'=>''];
                                return $data;
                            }
                        }
                    } else {
                        $notMatchErr = parent::getErrorMsg('SE098', __('Position Name'));
                        $data = ['error'=>$notMatchErr,'success'=>''];
                        return $data;
                    }
                }
                
                for ($j=0; $j < 18; $j++) {
                    (!empty(($worksheets[0][0][$j])))?
                        array_push($header_arr1, ($worksheets[0][0][$j])) : '';

                    (!empty(($worksheets[1][0][$j])))?
                        array_push($header_arr2, ($worksheets[1][0][$j])) : '';

                    (!empty(($worksheets[$loan_staff_thead_pos][0][$j])))?
                        array_push($header_arr3, ($worksheets[$loan_staff_thead_pos][0][$j])) : '';

                    (!empty(($worksheets[($loan_staff_thead_pos+1)][0][$j])))?
                        array_push($header_arr4, ($worksheets[($loan_staff_thead_pos+1)][0][$j])) : '';

                    (!empty(($worksheets[$OT_thead_pos][0][$j])))?
                        array_push($header_arr5, ($worksheets[$OT_thead_pos][0][$j])) : '';

                    (!empty(($worksheets[($OT_thead_pos+1)][0][$j])))?
                        array_push($header_arr6, ($worksheets[($OT_thead_pos+1)][0][$j])) : '';

                    (!empty(($worksheets[$TS_thead_pos][0][$j])))?
                        array_push($header_arr7, ($worksheets[$TS_thead_pos][0][$j])) : '';

                    (!empty(($worksheets[($TS_thead_pos+1)][0][$j])))?
                        array_push($header_arr8, ($worksheets[($TS_thead_pos+1)][0][$j])) : '';
                }
                #"Header not match!";
                if ($org_header1 != $header_arr1) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }
                if ($org_header2 != $header_arr2) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }
                
                if ($org_header3 != $header_arr3) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }
                if ($org_header4 != $header_arr4) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }

                if ($org_header1 != $header_arr5) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }
                if ($org_header2 != $header_arr6) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }

                if ($org_header1 != $header_arr7) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }
                if ($org_header2 != $header_arr8) {
                    $notMatchErr = parent::getErrorMsg('SE022');
                }
            } else {
                $notMatchErr = parent::getErrorMsg('SE098', __('Field Name'));
                $data = ['error'=>$notMatchErr,'success'=>''];
                return $data;
            }
            
            if (empty($notMatchErr)) {
                $manpower_data_excel 	= [];
                $ot_data_excel 			= [];
                $total_data_excel 		= [];
                $subtot_data_excel 		= [];
                $manpower_adjust_excel	= [];
                $month = [	'month_1_amt',
                            'month_2_amt',
                            'month_3_amt',
                            'month_4_amt',
                            'month_5_amt',
                            'month_6_amt',
                            '1st_half_total',
                            'month_7_amt',
                            'month_8_amt',
                            'month_9_amt',
                            'month_10_amt',
                            'month_11_amt',
                            'month_12_amt',
                            '2nd_half_total',
                            'sub_total'];
                
                $ws_index = 1;
                
                foreach ($manpower_data as $field_id => $position) {
                    $unit_salary = [];
                    
                    for ($p=0; $p < count($position); $p++) {
                        $ws_index++;
                        if (strpos($worksheets[$ws_index][0][0], '小計') !== false) {
                            $ws_index++;
                        }
                        if ($worksheets[$ws_index][0][0] == '社員合計') {
                            $ws_index += 6;
                        }
                        if ($worksheets[$ws_index][0][0] == $text1) {
                            while ($worksheets[$ws_index][0][0] != $text2) {
                                $ws_index++;
                            }
                            if ($worksheets[$ws_index][0][0] == $text2) {
                                $ws_index += 5;
                            }
                        }
                        
                        $ws_value = $this->is_decimal($worksheets[$ws_index][0][2]);
                        $noSalaryIndex = '';
                        if ($ws_value) {
                            $unit_salary[$position[$p]]['unit_salary'] = $worksheets[$ws_index][0][2];
                            if (in_array($worksheets[$ws_index][0][1], $yellow_pos_name)) {
                                $noSalaryIndex =(empty($worksheets[$ws_index][0][2]) || $worksheets[$ws_index][0][2] == '0')? $ws_index : '';
                            }
                        } else {
                            $err_row = $ws_index+8;
                            $err_col = $alpharbat[2];
                            $notMatchErr = parent::getErrorMsg('SE099', [__('9 digits with 2 digits decimal point'),$err_col,$err_row]);
                            $data = ['error'=>$notMatchErr,'success'=>''];
                            
                            return $data;
                        }
                        
                    
                        for ($j=3; $j <= 15; $j++) {
                            if ($j == 9) {
                                continue;
                            }

                            $ws_value = $this->is_decimal($worksheets[$ws_index][0][$j]);
                            if ($ws_value) {
                                $unit_salary[$position[$p]]['monthly_amt'][$month[($j-3)]] = $worksheets[$ws_index][0][$j];

                                if (!empty($noSalaryIndex) && !empty($worksheets[$ws_index][0][$j])) {
                                    $err_row = $ws_index+8;
                                    $err_col = $alpharbat[$j];
                                    
                                    $notMatchErr = parent::getErrorMsg('SE108', ['D',$err_row,__('単価')]);
                                    $data = ['error'=>$notMatchErr,'success'=>''];
                                    return $data;
                                }
                            } else {
                                $err_row = $ws_index+8;
                                $err_col = $alpharbat[$j];
                                $notMatchErr = parent::getErrorMsg('SE099', [__('9 digits with 2 digits decimal point'),$err_col,$err_row]);
                                $data = ['error'=>$notMatchErr,'success'=>''];
                                return $data;
                            }
                        }
                        
                        $manpower_data_excel[$field_id] = $unit_salary;
                    }
                }
                
                $ws_index = 10;
                #get ws_index position
                for ($ws_index; $ws_index < count($worksheets); $ws_index++) {
                    if ($worksheets[$ws_index][0][0] == '残業') {
                        break;
                    }
                }
                
                foreach ($ot_data as $key => $value) {
                    $ot_value = [];
                    for ($j=3; $j <= 15; $j++) {
                        if ($j == 9) {
                            continue;
                        }

                        $ws_value = $this->is_decimal($worksheets[$ws_index][0][$j]);
                        
                        if ($ws_value) {
                            $ot_value[$month[($j-3)]] = $worksheets[$ws_index][0][$j];
                        } else {
                            $err_row = $ws_index+8;
                            $err_col = $alpharbat[$j];
                            $notMatchErr = parent::getErrorMsg('SE099', [__('9 digits with 2 digits decimal point'),$err_col,$err_row]);
                            $data = ['error'=>$notMatchErr,'success'=>''];
                            
                            return $data;
                        }
                    }
                    #overtime_rate
                    $ot_data_excel[$value]['overtime_rate'] = $worksheets[$ws_index][0][2];
                    $ot_data_excel[$value]['monthly_amt'] = $ot_value;
                    
                    $ws_index++;
                }
                #get ws_index position
                for ($ws_index; $ws_index < count($worksheets); $ws_index++) {
                    if ($worksheets[$ws_index][0][0] == '社員人件費（合計）') {
                        break;
                    }
                }
                
                foreach ($total_data as $key => $value) {
                    $total_data_value = [];
                    for ($j=3; $j <= 17; $j++) {
                        $total_data_value[$month[($j-3)]] = $worksheets[$ws_index][0][$j];
                    }
                    
                    $total_data_excel[$value] = $total_data_value;
                    
                    $ws_index+=2;
                }
                
                foreach ($subtot_data as $key => $value) {
                    $subtot_data_value = [];

                    for ($ws_index = 10; $ws_index < count($worksheets); $ws_index++) {
                        if ($worksheets[$ws_index][0][0] == $value) {
                            for ($j=3; $j <= 17; $j++) {
                                if ($j == 9 || $j == 16 || $j == 17) {
                                    $ws_value = $this->is_decimal(round($worksheets[$ws_index][0][$j], 2));
                                } else {
                                    $ws_value = $this->is_decimal($worksheets[$ws_index][0][$j]);
                                }

                                if ($ws_value) {
                                    $subtot_data_value[$month[($j-3)]] = $worksheets[$ws_index][0][$j];
                                } else {
                                    $err_row = $ws_index+8;
                                    $err_col = $alpharbat[$j];
                                    $notMatchErr = parent::getErrorMsg('SE099', [__('9 digits with 2 digits decimal point'),$err_col,$err_row]);
                                    $data = ['error'=>$notMatchErr,'success'=>''];
                                    return $data;
                                }
                            }
                            
                            $subtot_data_excel[$value] = $subtot_data_value;
                        } else {
                            continue;
                        }
                    }
                }

                /*manpower_adjust*/
                
                foreach ($manpower_adjust as $key => $value) {
                    $manpower_adjust_value = [];
                    for ($ws_index = 10; $ws_index < count($worksheets); $ws_index++) {
                        if (($worksheets[$ws_index][0][0] == 'B　金額（手入力）' && $value == '3')|| ($worksheets[$ws_index][0][0] == 'D　金額（手入力）' && $value == 4) || ($worksheets[$ws_index][0][0] == '社員人件費（手入力）' && $value == 0)) {
                            for ($j=3; $j <= 15; $j++) {
                                if ($j == 9) {
                                    continue;
                                }
                                if (($worksheets[$ws_index][0][0] == '社員人件費（手入力）' && $value == 0)) {
                                    $ws_value = $this->is_decimal3($worksheets[$ws_index][0][$j]);
                                } else {
                                    $ws_value = $this->is_decimal($worksheets[$ws_index][0][$j]);
                                }
                                if ($ws_value) {
                                    $manpower_adjust_value['adjust'][$month[($j-3)]] = $worksheets[$ws_index][0][$j];
                                } else {
                                    $err_row = $ws_index+8;
                                    $err_col = $alpharbat[$j];
                                    if (($worksheets[$ws_index][0][0] == '社員人件費（手入力）' && $value == 0)) {
                                        $notMatchErr = parent::getErrorMsg('SE099', [__('10 digits with 3 digits decimal point'),$err_col,$err_row]);
                                    } else {
                                        $notMatchErr = parent::getErrorMsg('SE099', [__('9 digits with 2 digits decimal point'),$err_col,$err_row]);
                                    }
                                    $data = ['error'=>$notMatchErr,'success'=>''];
                                    return $data;
                                }
                            }
                            
                            $manpower_adjust_excel[$value] = $manpower_adjust_value;
                        }
                    }
                }
            }
        } else {
            $notMatchErr = parent::getErrorMsg('SE015');
        }
        
        $collected = ['manpower'=>$manpower_data_excel,'manpower_ot'=>$ot_data_excel,'manpower_total'=>$total_data_excel,'manpower_subtot'=>$subtot_data_excel,'adjustment'=>$manpower_adjust_excel,'filling'=>$mp_data['filling_date']];
        
        $data = ['error'=>$notMatchErr,'success'=>$collected];
        return $data;
    }

    /**
     * Check decimal validation
     *
     * @author Nu Nu Lwin (20210119)
     * @param worksheets
     * @return boolean
     */
    public function is_decimal($val)
    {
        $decimalOnly = preg_match('/^\s*-?(\d{0,7})(\.\d{0,2})?\s*$/', $val);
        
        return ($decimalOnly == 1)? true : false;
    }
    /**
     * Check decimal validation
     *
     * @author Nu Nu Lwin (20210119)
     * @param worksheets
     * @return boolean
     */
    public function is_decimal3($val)
    {
        $decimalOnly = preg_match('/^\s*-?(\d{0,7})(\.\d{0,3})?\s*$/', $val);
        
        return ($decimalOnly == 1)? true : false;
    }
}
