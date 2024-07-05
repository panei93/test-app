<?php

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'BrmAccounts');
App::import('Controller', 'BrmMonthlyReport');
App::import('Controller', 'BrmActualResultSummary');

/**
 * Calculations Controller
 *
 * @property Calculation $Calculation
 * @property PaginatorComponent $Paginator
 */
class CalculationController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $uses = array('BrmMainBudget','BrmMainResult','Layer','BrmBudget','BrmActualResultSummary','BrmLogistic');
    public $components = array('Session');
    /**
     * CalculateAmt method
     *
     * @author Khin Hnin Myo (20200311)
     * @param  $target_month,$term_id,$session_hq_id,$head_quarter,$id_array,$id_string
     * @return array
     *
     */
    //public function CalculateBRDAmt($tab, $target_month, $term_id, $id, $id_array='null', $id_string='null', $term_name='null',$top_layer_type,$top_layer_name=null,$middle_layer_name=null,$bottom_layer_name=null, $bacode='null')
    public function CalculateBRDAmt($tab, $target_month, $term_id, $click_layer_code, $id_array='null', $id_string='null', $term_name='null',$top_layer_type,$top_layer_name=null,$middle_layer_name=null,$bottom_layer_name=null, $bacode='null')
    {   
        $Common = new CommonController();
        $BrmAccount = new BrmAccountsController();

        $bottom_layer_type  = Setting::LAYER_SETTING['bottomLayer'];//
        #Get only year form target_month
        $target_year = $Common->getTargetYearByMonth($target_month, $term_id);
        #Get budget start and end month
        $start_month = $Common->getMonth($target_year, $term_id, 'start');
        $end_month   = $Common->getMonth($target_year, $term_id, 'end');
    
        $last_year_tm           = date("Y-m", strtotime($target_month. "last day of - 1 year"));
        $last_year_start_month  = date("Y-m", strtotime($start_month. "last day of - 1 year"));

        $group_code = AccountGroup::BRD_GROUP;
        $account_data = $BrmAccount->getPairedAccounts($group_code);//acc_name->paired_acc-id by acc id
        $search_total       = [];
        $budget_results     = [];
        //$id_set             = [];
        $layer_set          = [];
        $conditions         = [];
        $fields             = [];
        $join               = [];
        $extra_index        = [];
        $group_by           = [];
        $con                = [];
        $target_column      = '';
        $sub_column         = '';
        $from_date  = date("Y/m/d", strtotime($last_year_start_month.'-01'));
        $to_date    = date("Y/m/d", strtotime($end_month.'-01'));
        $tax_refund_ba = Setting::TAX_REFUND_BA; #BA 9000
       
        if ($tab == $top_layer_name) {
            $sub_column = 'hlayer_code';
            $type_order =Setting::LAYER_SETTING['topLayer'];
            #for select option
            $conditions = array(
                'flag'=> 1,
                'type_order'=>$type_order,
                'NOT' => array(
                    'layer_code' => $tax_refund_ba, #don't show 全社勘定(税還付) BA and Dept
                ),
                'OR' => array(
                    'from_date >=' => $from_date,
                    'to_date >=' => $to_date,
                )
            );
            if(!empty($id_array)){//for search condition                 
                $conditions['layer_code'] = $id_array;
            }            
        }else if ($tab == 'Logistic') {
            
            $fields = array('BrmLogistic.index_name','name_jp');
            $conditions = array(
                'Layer.layer_code' => $click_layer_code,
                'BrmLogistic.target_year' => $target_year,
                'BrmLogistic.flag' => 1,
                'OR' => array(
                    'from_date >=' => $from_date,
                    'to_date >=' => $to_date,
                )
                
            );
            $con = $conditions;
            if (!empty($id_array)) {
                $search_arr = $id_array;
                $id_array = [];
                foreach($search_arr as $v) {
                    if(strpos($v, '/') !== false) {
                        $id_array = array_merge($id_array, explode('/ ', $v));
                    }else {
                        array_push($id_array, $v);
                    }   
                }
                //$conditions['index_name'] = $id_array;
                $conditions['index_no'] = $id_array;
            }
            $join = array(
                array('table' => 'brm_logistics',
                      'alias' => 'BrmLogistic',
                      'type' => 'LEFT',
                      'conditions' => array('Layer.layer_code = BrmLogistic.layer_code AND BrmLogistic.target_year='.$target_year.' AND BrmLogistic.flag=1'))
            );
            $group_by = 'BrmLogistic.index_name';
            $target_column = 'layer_code';
            $sub_column = 'transaction_key';
            $logi_order = "BrmLogistic.logistic_order,BrmLogistic.index_name";

        }else{
            //middle or bottom and below
            if($tab == $middle_layer_name){
                //$target_column = 'dlayer_code';
                $sub_column = 'dlayer_code';
                $type_order =Setting::LAYER_SETTING['middleLayer'];
            }else{//bottom layer name
                $type_order=Setting::LAYER_SETTING['bottomLayer'];
                //$target_column = 'layer_code';
                $sub_column = 'layer_code';
            }
            if(!empty($id_array)){//for search condition
                $conditions = array(
                    'flag'=> 1,
                    'OR' => array(
                        'from_date >=' => $from_date,
                        'to_date >=' => $to_date,
                    )
                    
                );
                //$layers[]=$id;    
                $layers[]=$click_layer_code;///           
                $lcodes=array();
                $lcodes = array_merge($layers,$id_array);
                $conditions['layer_code IN'] = $lcodes;
            }else{// go by clicking link                
                $conditions = array(
                    'flag'=> 1,
                    'OR' =>array(
                        'parent_id LIKE' => '%'.$click_layer_code.'%',                    
                        'layer_code'=>$click_layer_code,
                        'type_order'=>$type_order,
                        'from_date >=' => $from_date,
                        'to_date >=' => $to_date
                        //'layer_type_id'=>1//check later with top layer type id
                    )
                    
                );
            }            
            $target_column = $sub_column;
        }
        $fields = array('layer_code','name_jp');
        
        $con = $conditions;
        $order = ($tab=='Logistic') ? $logi_order : 'type_order';
        
        $layer_set = $this->Layer->find('list', array(
            'joins' => $join,
            'conditions' => $conditions,
            'fields' => $fields,
            'order' => $order,
            'group' => $group_by
        ));
        $parent_name = ($tab == $top_layer_name) ? '全社' : array_values($layer_set)[0];//layer_name
        if($tab == $top_layer_name || $tab =='Logistic'){
            $sub_data = $layer_set;//layer_code
        }else{
            unset($layer_set[array_keys($layer_set)[0]]);//remove 1st key value pair from array
            $sub_data = $layer_set;//layer_code 
            
        }
        $data_set['main_column_name'] = $target_column;
        $data_set['sub_column_name']  = $sub_column;
        if($tab == $top_layer_name){
            $data_set['layer_code'] = $click_layer_code;
        }else{
            $lcode_arr = array();
            foreach($sub_data as $key=>$value){
               array_push($lcode_arr,$key) ;
            }
            $data_set['layer_code'] =$lcode_arr;
        }
        #add extra logis if tab=Logistic

        #add extra logis if tab=Logistic

        if ($tab == 'Logistic') {
            $this->BrmLogistic->virtualFields['name'] = 'GROUP_CONCAT( BrmLogistic.index_name SEPARATOR "/ " )';
            $index_nos = $this->BrmLogistic->find('list', array(
                'fields' => array('id', 'index_no', 'name'),
                'conditions' => array(
                    'flag' => '1',
                    'index_no !=' => '',
                    'layer_code' => $click_layer_code,
                    'index_name' => array_values($sub_data),
                    'target_year' => $target_year
                ),
                'group' => 'index_no'
            ));
            $logistic_index = array_merge(call_user_func_array('array_merge', array_values($index_nos)), array_values($sub_data));
            $sub_data = $index_nos;
            $null_indexes = $this->getExtraIndex($term_id, $click_layer_code, $last_year_start_month, $end_month);
            $extra_index = array_diff($null_indexes, $logistic_index);
            Cache::write('EXTRA_LOGIS', $extra_index);
            foreach ($extra_index as $each_index) {
                if (empty($id_array)) {
                    $index_key = ($each_index != '') ? $each_index : '取引無し';
                    $halfw_value = mb_convert_kana($each_index, 'a', 'UTF-8');
                    if (in_array($halfw_value, $logistic_index)) {
                        foreach ($sub_data as $idx_key => $idx_value) {
                            if (in_array($halfw_value, $idx_value)) {
                                $sub_data[$idx_key][] = $each_index;
                            }
                        }
                    } else {
                        $sub_data[$index_key][0] = $each_index;
                    }
                }
            }
            if (!empty($id_array)) {
                foreach ($null_indexes as $value) {
                    if (in_array($value, $id_array)) {
                        $halfw_value = mb_convert_kana($value, 'a', 'UTF-8');
                        if (in_array($halfw_value, $logistic_index)) {
                            foreach ($sub_data as $idx_key => $idx_value) {
                                if (in_array($halfw_value, $idx_value)) {
                                    $sub_data[$idx_key][] = $value;
                                }
                            }
                        } else {
                            $sub_data[$value][0] = $value;
                        }
                    }
                }
                Cache::write('NULL_LOGIS', $null_indexes);
            }
            
            $temp = [];
            foreach ($sub_data as $no => $name) {
                if(strpos($no, '/ ') !== false) {
                    $withselectname = explode('/ ', $no);
                    $sub_data[trim($no)] = array_merge($name, $withselectname);
                    $temp[trim($no)] = array_merge($name, $withselectname);
                }else {
                    if($no != '取引無し') $sub_data[trim($no)][0] = trim($no);
                }
            } 
            $sub_data = array_replace($temp, $sub_data);
        }
        #Budgets
        $tm_budget      = $this->getMonthlyBudget($data_set, $term_id, $target_month);
        $yearly_budget  = $this->getYearlyBudget($data_set, $term_id, $start_month, $end_month);
        $total_budget   = $this->getYearlyBudget($data_set, $term_id, $start_month, $target_month);
        #Result
        $tm_result          = $this->getMonthlyResult($data_set, $target_month);
        $tm_previous_result = $this->getMonthlyResult($data_set, $last_year_tm);
        $total_result       = $this->getYearlyResult($data_set, $start_month, $target_month);
        $yr_previous_result = $this->getYearlyResult($data_set, $last_year_start_month, $last_year_tm);
        $already = [];
        $account_data = ($tab != 'Logistic') ? $account_data : array_slice($account_data, 0, 3);
        $checkMainBudgetEmpty   = $this->BrmMainBudget->find('count');
        $checkMainResultEmpty   = $this->BrmMainResult->find('count');
        $checkBudgetEmpty       = $this->BrmBudget->find('count');
        $checkActualResultEmpty = $this->BrmActualResultSummary->find('count');
        
        // # prepare to show the head quarter table
        //foreach ($sub_data as $main_id => $sub_name) {//layer_code=>layer_name
        foreach ($sub_data as $main_layer_code => $layer_name) {//layer_code=>layer_name
            // $sub_name = ($tab != 'Logistic') ? array($sub_name) : $sub_name;
            $layer_name = (!is_array($layer_name))? array($layer_name) : $layer_name;
            foreach ($layer_name as $each_sub) {
                if (!in_array($each_sub, $already)) {
                    $name = ($tab != 'Logistic') ? $each_sub : trim($main_layer_code);//layer_name
                    $sub_layer_code = ($tab != 'Logistic') ? $main_layer_code : $each_sub;//layer_code
                    $list[$name] = $name;//layer_name
                    foreach ($account_data as $account_name => $pair_id) {//brm_accounts->name => brm_saccounts->id
                        $search_total[$account_name]['term_id'] = $term_id;
                        $search_total[$account_name]['layer_code'] = $sub_layer_code;//layer_id or layer_code
                        $search_total[$account_name]['name'] = $name;//layer_name
                        $search_total[$account_name]['name_jp'] = $account_name;//from brm_accounts->name_jp
                        $search_total[$account_name]['target_month'] = $target_month;
                        $budget_result[$name][$account_name]['term_id'] = $term_id;
                        $budget_result[$name][$account_name]['layer_code'] = $sub_layer_code;//layer_id//
                        $budget_result[$name][$account_name]['name'] = $name;////layer_name
                        $budget_result[$name][$account_name]['name_jp'] = $account_name;//from brm_accounts->name_jp
                        $budget_result[$name][$account_name]['target_month'] = $target_month;
                        foreach ($pair_id as $each_acc_id) {//brm_saccounts->id
                            $budget_result[$name][$account_name]['budget'] += (!empty($yearly_budget[$sub_layer_code][$each_acc_id]) ? $yearly_budget[$sub_layer_code][$each_acc_id] : 0);
                            $budget_result[$name][$account_name]['monthly_budget'] += (!empty($tm_budget[$sub_layer_code][$each_acc_id]) ? $tm_budget[$sub_layer_code][$each_acc_id] : 0);
                            $budget_result[$name][$account_name]['monthly_result'] += (!empty($tm_result[$sub_layer_code][$each_acc_id]) ? $tm_result[$sub_layer_code][$each_acc_id] : 0);
                            $budget_result[$name][$account_name]['total_budget'] += (!empty($total_budget[$sub_layer_code][$each_acc_id]) ? $total_budget[$sub_layer_code][$each_acc_id] : 0);
                            $budget_result[$name][$account_name]['total_result'] += (!empty($total_result[$sub_layer_code][$each_acc_id]) ? $total_result[$sub_layer_code][$each_acc_id] : 0);
                            $budget_result[$name][$account_name]['yoy_result'] += (!empty($yr_previous_result[$sub_layer_code][$each_acc_id] && $tab!='Logistic') ? $yr_previous_result[$sub_layer_code][$each_acc_id] : 0);

                            //$yearly_budget['layer_code'][brm_saccounts->id]
                            $search_total[$account_name]['budget'] += (!empty($yearly_budget[$sub_layer_code][$each_acc_id]) ? $yearly_budget[$sub_layer_code][$each_acc_id] : 0);
                            $search_total[$account_name]['monthly_budget'] += (!empty($tm_budget[$sub_layer_code][$each_acc_id]) ? $tm_budget[$sub_layer_code][$each_acc_id] : 0);
                            $search_total[$account_name]['monthly_result'] += (!empty($tm_result[$sub_layer_code][$each_acc_id]) ? $tm_result[$sub_layer_code][$each_acc_id] : 0);
                            $search_total[$account_name]['total_budget'] += (!empty($total_budget[$sub_layer_code][$each_acc_id]) ? $total_budget[$sub_layer_code][$each_acc_id] : 0);
                            $search_total[$account_name]['total_result'] += (!empty($total_result[$sub_layer_code][$each_acc_id]) ? $total_result[$sub_layer_code][$each_acc_id] : 0);
                            $search_total[$account_name]['yoy_result'] += (!empty($yr_previous_result[$sub_layer_code][$each_acc_id] && $tab!='Logistic') ? $yr_previous_result[$sub_layer_code][$each_acc_id] : 0);
                        }
                        $budget_result[$name][$account_name]['monthly_budget_change'] = $budget_result[$name][$account_name]['monthly_result'] - $budget_result[$name][$account_name]['monthly_budget'];
                        $budget_result[$name][$account_name]['total_budget_change'] = $budget_result[$name][$account_name]['total_result'] - $budget_result[$name][$account_name]['total_budget'];
                        $budget_result[$name][$account_name]['achievement_by_year'] = $this->getRatio($budget_result[$name][$account_name]['budget'], $budget_result[$name][$account_name]['total_result']);
                        $budget_result[$name][$account_name]['yoy_change'] = ($tab != 'Logistic') ? $budget_result[$name][$account_name]['total_result'] - $budget_result[$name][$account_name]['yoy_result'] : 0;
                        
                        $search_total[$account_name]['monthly_budget_change'] = $search_total[$account_name]['monthly_result'] - $search_total[$account_name]['monthly_budget'];
                        $search_total[$account_name]['total_budget_change'] = $search_total[$account_name]['total_result'] - $search_total[$account_name]['total_budget'];
                        $search_total[$account_name]['achievement_by_year'] = $this->getRatio($search_total[$account_name]['budget'], $search_total[$account_name]['total_result']);
                        $search_total[$account_name]['yoy_change'] = ($tab != 'Logistic') ? $search_total[$account_name]['total_result'] - $search_total[$account_name]['yoy_result'] : 0;
                    
                    }
                    $already[] = $each_sub;
                }
            }
        }
        $remove_zero = $this->RemoveAMTZero($budget_result);
        $budget_results = $remove_zero[0];
        
        # select data for display
        $select_set = $this->Layer->find('list', array(
            'joins' => $join,
            'conditions' => $con,
            'fields' => $fields,
            'order' => array('layer_type_id'),
            'group' => $group_by
        ));
        if($tab == $top_layer_name){
            $select_data = $select_set;
        }else{
            unset($select_set[array_keys($select_set)[0]]);//remove 1st key value pair from array
            $select_data = $select_set;
        }
        //$select_data = $select_set;
        if ($tab == 'Logistic') {
            if ($parent_name == '') {
                $parent_name = array_keys($select_set)[0];
            }
            //$select_data = $this->getLogiSelectList($select_data, $id, $null_indexes, $target_year);
            $select_data = $this->getLogiSelectList($select_data, $click_layer_code, $null_indexes, $target_year);
        }
        $select_data = array_diff($select_data, $remove_zero[1]);        
        if (array_keys($select_data)[0] == '' && count($select_data) == 1) {
            $select_data = '';
        }
        return array($budget_results,$search_total,$start_month,$parent_name,$select_data);
    }

    public function CalculateMRAmt($hlayer_code, $headquarter, $target_month, $term_id, $term_name)
    {
        $Common = new CommonController();
        $BrmAccount = new BrmAccountsController();
        $MonthlyReport = new BrmMonthlyReportController();

        $id_set = [];
        $tmp_result = [];
        $total = [];

        #Get only year form target_month
        $target_year = $Common->getTargetYearByMonth($target_month, $term_id);
        #Get budget start and end month
        $start_month = $Common->getMonth($target_year, $term_id, 'start');
        $end_month = $Common->getMonth($target_year, $term_id, 'end');
    
        $last_year_tm = date("Y-m", strtotime($target_month. "last day of - 1 year"));
        $last_year_start_month = date("Y-m", strtotime($start_month. "last day of - 1 year"));

        $yearly_sm = ($target_month == $end_month) ? date("Y-m", strtotime($start_month. "last day of + 1 year")) : $start_month;
        $yearly_em = ($target_month == $end_month) ? date("Y-m", strtotime($end_month. "last day of + 1 year")) : $end_month;

        $group_code = AccountGroup::MR_GROUP;
        $account_data = $BrmAccount->getPairedAccounts($group_code);
        
        $from_date = date("Y/m/d", strtotime($last_year_start_month.'-01'));
        $to_date = date("Y/m/d", strtotime($end_month.'-01'));
        #get dept by headquater

        $tax_refund_ba = Setting::TAX_REFUND_BA; #BA 9000

        $id_set = $this->Layer->find('list', array(
            'fields' => array('bottom_layer.layer_code','bottom_layer.name_jp','Layer.name_jp'),
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'bottom_layer',
                    'conditions' => array(
                        'bottom_layer.flag = 1',
                        'OR' => array(
                            'bottom_layer.from_date >=' => $from_date,
                            'bottom_layer.to_date >=' => $to_date,
                        ),
                        "bottom_layer.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['middleLayer'].", '\":\"',Layer.layer_code,'\"%')",
                        'bottom_layer.type_order' => Setting::LAYER_SETTING['bottomLayer'],
                    )
                ),
            ),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.type_order' => Setting::LAYER_SETTING['middleLayer'], 
                "Layer.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',".$hlayer_code.",'\"%')",
                'NOT' => array(
                    'Layer.layer_code' => $tax_refund_ba, #don't show 全社勘定(税還付) BA and Dept
                ),
                'OR' => array(
                    'Layer.from_date >=' => $from_date,
                    'Layer.to_date >=' => $to_date,
                )
            ),
            'order' => array('Layer.type_order ASC','Layer.id ASC')
        ));
        
        $next_1m = date("Y-m", strtotime($target_month. "last day of + 1 months"));
        $next_2m = date("Y-m", strtotime($target_month. "last day of + 2 months"));
        $next_3m = date("Y-m", strtotime($target_month. "last day of + 3 months"));

        #For 3.Next 3 month forecast and annual prospects table
        $next_budgets = $this->getNextMonthBudget($hlayer_code, $term_id, $next_1m, $next_3m);
        $year_budgets = $this->getTotalYearlyBudget($hlayer_code, $term_id, $yearly_sm, $yearly_em);

        $h_next_budget = array_sum($next_budgets[$next_1m]);
        $h_next2month_budget = array_sum($next_budgets[$next_2m]);
        $h_next3month_budget = array_sum($next_budgets[$next_3m]);
        $h_yearly_budget = array_sum($year_budgets);
        $h_annual_budget = array("h_next_budget"=>round($h_next_budget/1000000),"h_next2month_budget"=>round($h_next2month_budget/1000000),"h_next3month_budget"=>round($h_next3month_budget/1000000),"h_yearly_budget"=>round($h_yearly_budget/1000000));

        /********* For user input commment *************/
        #To show saved data for related text box at tbl_total_result_overview
        $overview_cmt = $MonthlyReport->getTotalOverviewCmt($target_month, $hlayer_code);
        
        #To show saved data for related text box at tbl_forecast
        $forecast = $MonthlyReport->getForecast($target_month, $hlayer_code);

        #To show upload file name at tbl_p3_attachement
        $attached = $MonthlyReport->getAttachment($target_month, $hlayer_code);

        $total['hlayer_code']= $hlayer_code;
        $total['hlayer_name']= $headquarter;
        $total['annual_budget'] = $h_annual_budget;
        $total['overview_cmt'] 	= $overview_cmt[0];
        $total['forecast']		= $forecast[0];
        $total['attached'] 		= $attached[0];

        foreach ($id_set as $dname => $bottom_layer_data) {
            $dlayer_code = $this->Layer->find('first', array(
                'field' => 'Layer.layer_code',
                'conditions' => array(
                    'Layer.name_jp' => $dname,
                    'Layer.flag' => 1
                )
            ))['Layer']['layer_code'];
                    
            #For 3.Next 3 month forecast and annual prospects table
            $d_next_budget = $next_budgets[$next_1m][$dlayer_code];
            $d_next2month_budget = $next_budgets[$next_2m][$dlayer_code];
            $d_next3month_budget = $next_budgets[$next_3m][$dlayer_code];
            $d_yearly_budget = $year_budgets[$dlayer_code];
            $d_annual_budget = array("d_next_budget"=>round($d_next_budget/1000000),"d_next2month_budget"=>round($d_next2month_budget/1000000),"d_next3month_budget"=>round($d_next3month_budget/1000000),"d_yearly_budget"=>round($d_yearly_budget/1000000));

            $dept_data_set['main_column_name'] = 'dlayer_name';
            $dept_data_set['sub_column_name'] = 'layer_code';
            $dept_data_set['id'] = $dlayer_code;

            $tmp_result[$dname]['id'] = $dlayer_code;
            $tmp_result[$dname]['annual_budget'] 	= $d_annual_budget;
            $tmp_result[$dname]['overview_cmt'] 	= $overview_cmt[$dlayer_code];
            $tmp_result[$dname]['forecast']			= $forecast[$dlayer_code];
            $tmp_result[$dname]['attached'] 		= $attached[$dlayer_code];
          
            #Budgets
            $tm_budget      = $this->getMonthlyBudget($dept_data_set, $term_id, $target_month);
            $yearly_budget  = $this->getYearlyBudget($dept_data_set, $term_id, $start_month, $end_month);
            $total_budget   = $this->getYearlyBudget($dept_data_set, $term_id, $start_month, $target_month);
            
            #Result
            $tm_result          = $this->getMonthlyResult($dept_data_set, $target_month);
            $tm_previous_result = $this->getMonthlyResult($dept_data_set, $last_year_tm);
            $total_result       = $this->getYearlyResult($dept_data_set, $start_month, $target_month);
            $yr_previous_result = $this->getYearlyResult($dept_data_set, $last_year_start_month, $last_year_tm);
            
            // $checkMainBudgetEmpty   = $this->BrmMainBudget->find('count');
            // $checkMainResultEmpty   = $this->BrmMainResult->find('count');
            // $checkBudgetEmpty       = $this->BrmBudget->find('count');
            // $checkActualResultEmpty = $this->BrmActualResultSummary->find('count');
            #Budgets
            $tm_budget = $this->getMonthlyBudget($dept_data_set, $term_id, $target_month);
            $yearly_budget = $this->getYearlyBudget($dept_data_set, $term_id, $start_month, $end_month);
            $total_budget = $this->getYearlyBudget($dept_data_set, $term_id, $start_month, $target_month);

            #Result
            $tm_result = $this->getMonthlyResult($dept_data_set, $target_month);
            $tm_previous_result = $this->getMonthlyResult($dept_data_set, $last_year_tm);
            $total_result = $this->getYearlyResult($dept_data_set, $start_month, $target_month);
            $yr_previous_result = $this->getYearlyResult($dept_data_set, $last_year_start_month, $last_year_tm);

            # prepare to show the head quarter table
            foreach ($bottom_layer_data as $sub_id => $sub_name) {
                $blayer_name = $sub_name;
                foreach ($account_data as $account_name => $pair_id) {
                    foreach ($pair_id as $each_acc_id) {
                        $budget_amt = (!empty($yearly_budget[$sub_id][$each_acc_id]) ? $yearly_budget[$sub_id][$each_acc_id] : 0);
                        $monthly_budget_amt = (!empty($tm_budget[$sub_id][$each_acc_id]) ? $tm_budget[$sub_id][$each_acc_id] : 0);
                        $monthly_result_amt = (!empty($tm_result[$sub_id][$each_acc_id]) ? $tm_result[$sub_id][$each_acc_id] : 0);
                        $total_budget_amt = (!empty($total_budget[$sub_id][$each_acc_id]) ? $total_budget[$sub_id][$each_acc_id] : 0);
                        $total_result_amt = (!empty($total_result[$sub_id][$each_acc_id]) ? $total_result[$sub_id][$each_acc_id] : 0);
                        $yoy_result_amt = (!empty($yr_previous_result[$sub_id][$each_acc_id]) ? $yr_previous_result[$sub_id][$each_acc_id] : 0);
                        $prev_month_result = (!empty($tm_previous_result[$sub_id][$each_acc_id]) ? $tm_previous_result[$sub_id][$each_acc_id] : 0);

                        #HQ Total
                        $total['data'][$account_name]['本部合計']['tm_budget'] 		+= $monthly_budget_amt;
                        $total['data'][$account_name]['本部合計']['tm_result'] 		+= $monthly_result_amt;
                        $total['data'][$account_name]['本部合計']['tm_previous_y_r']	+= $prev_month_result;
                        $total['data'][$account_name]['本部合計']['total_tm_budget'] 	+= $total_budget_amt;
                        $total['data'][$account_name]['本部合計']['total_tm_result'] 	+= $total_result_amt;
                        $total['data'][$account_name]['本部合計']['previous_y_r'] 	+= $yoy_result_amt;
                        $total['data'][$account_name]['本部合計']['yearly_budget'] 	+= $budget_amt;

                        #Dept Total
                        $total['data'][$account_name][$dname]['tm_budget'] 		 += $monthly_budget_amt;
                        $total['data'][$account_name][$dname]['tm_result'] 		 += $monthly_result_amt;
                        $total['data'][$account_name][$dname]['tm_previous_y_r'] += $prev_month_result;
                        $total['data'][$account_name][$dname]['total_tm_budget'] += $total_budget_amt;
                        $total['data'][$account_name][$dname]['total_tm_result'] += $total_result_amt;
                        $total['data'][$account_name][$dname]['previous_y_r'] 	 += $yoy_result_amt;
                        $total['data'][$account_name][$dname]['yearly_budget'] 	 += $budget_amt;

                        #Department Data
                        $tmp_result[$dname]['data'][$account_name]['部合計']['tm_budget'] 		+= $monthly_budget_amt;
                        $tmp_result[$dname]['data'][$account_name]['部合計']['tm_result'] 		+= $monthly_result_amt;
                        $tmp_result[$dname]['data'][$account_name]['部合計']['tm_previous_y_r'] += $prev_month_result;
                        $tmp_result[$dname]['data'][$account_name]['部合計']['total_tm_budget'] += $total_budget_amt;
                        $tmp_result[$dname]['data'][$account_name]['部合計']['total_tm_result'] += $total_result_amt;
                        $tmp_result[$dname]['data'][$account_name]['部合計']['previous_y_r'] 	+= $yoy_result_amt;
                        $tmp_result[$dname]['data'][$account_name]['部合計']['yearly_budget'] 	+= $budget_amt;


                        #BA Data
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_budget'] 		+= $monthly_budget_amt;
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_result'] 		+= $monthly_result_amt;
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_previous_y_r'] += $prev_month_result;
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_tm_budget'] += $total_budget_amt;
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_tm_result'] += $total_result_amt;
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['previous_y_r'] 	+= $yoy_result_amt;
                        $tmp_result[$dname]['data'][$account_name][$blayer_name]['yearly_budget'] 	+= $budget_amt;
                    }
                    $total['data'][$account_name]['本部合計']['tm_ratio'] 		= $total['data'][$account_name]['本部合計']['tm_result'] - $total['data'][$account_name]['本部合計']['tm_budget'];
                    $total['data'][$account_name]['本部合計']['tm_yoy_change'] 	= $total['data'][$account_name]['本部合計']['tm_result'] - $total['data'][$account_name]['本部合計']['tm_previous_y_r'];
                    $total['data'][$account_name]['本部合計']['total_ratio'] 	= $total['data'][$account_name]['本部合計']['total_tm_result'] - $total['data'][$account_name]['本部合計']['total_tm_budget'];
                    $total['data'][$account_name]['本部合計']['yoy_change'] 		= $total['data'][$account_name]['本部合計']['total_tm_result'] - $total['data'][$account_name]['本部合計']['previous_y_r'];
                    $total['data'][$account_name]['本部合計']['achieve_rate'] 	= $this->getRatio($total['data'][$account_name]['本部合計']['yearly_budget'], $total['data'][$account_name]['本部合計']['total_tm_result']);
            
                    $total['data'][$account_name][$dname]['tm_ratio'] 		= $total['data'][$account_name][$dname]['tm_result'] - $total['data'][$account_name][$dname]['tm_budget'];
                    $total['data'][$account_name][$dname]['tm_yoy_change'] 	= $total['data'][$account_name][$dname]['tm_result'] - $total['data'][$account_name][$dname]['tm_previous_y_r'];
                    $total['data'][$account_name][$dname]['total_ratio'] 	= $total['data'][$account_name][$dname]['total_tm_result'] - $total['data'][$account_name][$dname]['total_tm_budget'];
                    $total['data'][$account_name][$dname]['yoy_change'] 		= $total['data'][$account_name][$dname]['total_tm_result'] - $total['data'][$account_name][$dname]['previous_y_r'];
                    $total['data'][$account_name][$dname]['achieve_rate'] 	= $this->getRatio($total['data'][$account_name][$dname]['yearly_budget'], $total['data'][$account_name][$dname]['total_tm_result']);

                    $tmp_result[$dname]['data'][$account_name]['部合計']['tm_ratio'] 		= $tmp_result[$dname]['data'][$account_name]['部合計']['tm_result'] - $tmp_result[$dname]['data'][$account_name]['部合計']['tm_budget'];
                    $tmp_result[$dname]['data'][$account_name]['部合計']['tm_yoy_change'] 	= $tmp_result[$dname]['data'][$account_name]['部合計']['tm_result'] - $tmp_result[$dname]['data'][$account_name]['部合計']['tm_previous_y_r'];
                    $tmp_result[$dname]['data'][$account_name]['部合計']['total_ratio'] 	= $tmp_result[$dname]['data'][$account_name]['部合計']['total_tm_result'] - $tmp_result[$dname]['data'][$account_name]['部合計']['total_tm_budget'];
                    $tmp_result[$dname]['data'][$account_name]['部合計']['yoy_change'] 		= $tmp_result[$dname]['data'][$account_name]['部合計']['total_tm_result'] - $tmp_result[$dname]['data'][$account_name]['部合計']['previous_y_r'];
                    $tmp_result[$dname]['data'][$account_name]['部合計']['achieve_rate'] 	= $this->getRatio($tmp_result[$dname]['data'][$account_name]['部合計']['yearly_budget'], $tmp_result[$dname]['data'][$account_name]['部合計']['total_tm_result']);
                    
                    $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_ratio'] 		= $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_result'] - $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_budget'];
                    $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_yoy_change'] 	= $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_result'] - $tmp_result[$dname]['data'][$account_name][$blayer_name]['tm_previous_y_r'];
                    $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_ratio'] 	= $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_tm_result'] - $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_tm_budget'];
                    $tmp_result[$dname]['data'][$account_name][$blayer_name]['yoy_change'] 		= $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_tm_result'] - $tmp_result[$dname]['data'][$account_name][$blayer_name]['previous_y_r'];
                    $tmp_result[$dname]['data'][$account_name][$blayer_name]['achieve_rate'] 	= $this->getRatio($tmp_result[$dname]['data'][$account_name][$blayer_name]['yearly_budget'], $tmp_result[$dname]['data'][$account_name][$blayer_name]['total_tm_result']);
                }
            }
            if (!empty($tmp_result)) {
                $chk_res = $chk_res+1;
            }
        }

        $data_array = array(
            "head_data" => $total,
            "dept_data" => $tmp_result,
            "next_1m" => $next_1m,
            "next_2m" => $next_2m,
            "next_3m" => $next_3m,
            "chk_res" => $chk_res
        );
        return $data_array;
    }

    /**
     * getRatio method
     *
     * @author PanEiPhyo (20200709)
     * @param  $yearly_budget,$total_result
     * @return array
     *
     */
    public function getRatio($yearly_budget, $total_result)
    {
        $ratio = 0;
        # Change calculation by PanEiPhyo (20200728)
        if ($yearly_budget == 0) {
            $ratio = '－';
        } else {
            $ratio = (($yearly_budget > 0 && $total_result < 0) || ($total_result > 0 && $yearly_budget < 0)) ? '－' : round(($total_result/$yearly_budget)*100).'%';
        }
        return $ratio;
    }

    public function getYearlyBudget($data_set, $term_id, $start_month, $end_month)
    {
        $conditions = array(
            'brm_term_id ' => $term_id,
            'target_month >=' => $start_month,
            'target_month <=' => $end_month,
        );
        $group = array('brm_account_id');
        array_push($group, $data_set['sub_column_name']);
        if (!empty($data_set['main_column_name'])) {
            //$conditions[$data_set['main_column_name']] = $data_set['id'];
            $conditions[$data_set['main_column_name']] = $data_set['layer_code'];
            array_push($group, $data_set['main_column_name']);
        }
        $data = $this->BrmMainBudget->find('list', array(
            'fields' => array('brm_account_id','total_amount',$data_set['sub_column_name']),
            'conditions' => $conditions,
            'group' => $group,
        ));
        return $data;
    }

    public function getMonthlyBudget($data_set, $term_id, $target_month)
    {   
        $conditions = array(
            'brm_term_id ' => $term_id,
            'target_month ' => $target_month,
        );
        $group = array('brm_account_id');
        array_push($group, $data_set['sub_column_name']);
        if (!empty($data_set['main_column_name'])) {
            //$conditions[$data_set['main_column_name']] = $data_set['id'];
            $conditions[$data_set['main_column_name']] = $data_set['layer_code'];
            array_push($group, $data_set['main_column_name']);            
        }
        $data = $this->BrmMainBudget->find('list', array(
            'fields' => array('brm_account_id','total_amount',$data_set['sub_column_name']),
            'conditions' => $conditions,
            'group' => $group,
        ));
        
        return $data;
    }

    public function getYearlyResult($data_set, $start_month, $end_month)
    {
        $conditions = array(
            'target_month >=' => $start_month,
            'target_month <=' => $end_month,
        );
        $group = array('brm_account_id');
        array_push($group, $data_set['sub_column_name']);
        if (!empty($data_set['main_column_name'])) {
            //$conditions[$data_set['main_column_name']] = $data_set['id'];
            $conditions[$data_set['main_column_name']] = $data_set['layer_code'];
            array_push($group, $data_set['main_column_name']);
        }

        $data = $this->BrmMainResult->find('list', array(
            'fields' => array('brm_account_id','total_amount',$data_set['sub_column_name']),
            'conditions' => $conditions,
            'group' => $group,
        ));

        return $data;
    }

    public function getMonthlyResult($data_set, $target_month)
    {
        $conditions = array(
            'target_month ' => $target_month,
        );
        $group = array('brm_account_id');
        array_push($group, $data_set['sub_column_name']);
        if (!empty($data_set['main_column_name'])) {
            //$conditions[$data_set['main_column_name']] = $data_set['id'];
            $conditions[$data_set['main_column_name']] = $data_set['layer_code'];
            array_push($group, $data_set['main_column_name']);

        }
        $data = $this->BrmMainResult->find('list', array(
            'fields' => array('brm_account_id','total_amount',$data_set['sub_column_name']),
            'conditions' => $conditions,
            'group' => $group,
        ));
        return $data;
    }

    public function getNextMonthBudget($hlayer_code, $term_id, $start, $end)
    {
        $data = $this->BrmMainBudget->find('list', array(
            'fields' => array('dlayer_code','total_amount','target_month'),
            'conditions' => array(
                'hlayer_code'     => $hlayer_code,
                'target_month >=' => $start,
                'target_month <=' => $end,
                'brm_term_id' => $term_id,
                'brm_account_id !=' => ''
            ),
            'group' => array('dlayer_code','target_month'),
        ));

        return $data;
    }

    public function getTotalYearlyBudget($hlayer_code, $term_id, $start_month, $end_month)
    {
        $data = $this->BrmMainBudget->find('list', array(
            'fields' => array('dlayer_code','total_amount'),
            'conditions' => array(
                'hlayer_code' => $hlayer_code,
                'target_month >=' => $start_month,
                'target_month <=' => $end_month,
                'brm_term_id' => $term_id,
                'brm_account_id !=' => ''
            ),
            'group' => 'dlayer_code',
        ));

        return $data;
    }

    public function getExtraIndex($term_id, $ba_code, $start_month, $end_month)
    {   
        $budget_index = $this->BrmMainBudget->find('list', array(
            'fields' => array('transaction_key'),
            'conditions' => array(
                'brm_term_id' => $term_id,
                'layer_code' => $ba_code,
                'target_month >=' => $start_month,
                'target_month <=' => $end_month,
                'index_name' => null,
            ),
            'group' => array('transaction_key')
        ));
        $result_index = $this->BrmMainResult->find('list', array(
            'fields' => array('transaction_key'),
            'conditions' => array(
                'layer_code' => $ba_code,
                'target_month >=' => $start_month,
                'target_month <=' => $end_month,
                'index_name' => null,
            ),
            'group' => array('transaction_key')
        ));
        $indexes = array_unique(array_merge($budget_index, $result_index));
        return $indexes;
    }

    public function getLogiSelectList($sub_data, $id, $null_indexes, $target_year)
    {
        $select_data = $sub_data;
        foreach ($sub_data as $index_name => $each_name) {
            $index_nos = $this->BrmLogistic->find('list', array(
                'fields' => 'index_no',
                'conditions' => array(
                    'index_no !=' => '',
                    'layer_code' => $id,
                    'index_name' => $index_name,
                    'target_year' => $target_year,
                ),
                'group' => 'index_no'
            ));
            
            array_push($index_nos, $each_name);
            $sub_data[$index_name] = $index_nos;
        }
        $logistic_index = call_user_func_array('array_merge', $sub_data);
        $extra_index = array_diff($null_indexes, $logistic_index);
        foreach ($extra_index as $value) {
            $select_data[$value] = $value;
        }
        return $select_data;
    }

    public function RemoveAMTZero($budget_result)
    {
        $filter_list = [];
        foreach ($budget_result as $key => $value) {
            $list = [];
            foreach ($value as $vkey => $vvalue) {
                if ($vvalue['budget']== 0 && $vvalue['monthly_budget']== 0 && $vvalue['monthly_result']== 0 && $vvalue['total_budget']== 0 && $vvalue['total_result']== 0 && $vvalue['yoy_result']== 0 && $vvalue['monthly_budget_change']== 0 && $vvalue['total_budget_change']== 0 && $vvalue['achievement_by_year']== '－' && $vvalue['yoy_change']== 0) {
                    array_push($list, $vkey);
                }
            }
            if (count($list) == count($value)) {
                unset($budget_result[$key]);
                array_push($filter_list, $key);
            }
        }
        return array($budget_result,$filter_list);
    }
}
