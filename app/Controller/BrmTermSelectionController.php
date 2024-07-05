<?php

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * TermSelectionController
 * @author Hein Htet Ko
 */
class BrmTermSelectionController extends AppController
{
    public $uses = array('BrmTerm','User','LayerType', 'Layer', 'BrmTermDeadline', 'BrmMainBudget', 'BrmMainResult', 'BrmCronLog', 'Message');
    public $components = array('Session', 'Paginator', 'Flash');
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
        
        $show_menu_lists = $Common->getMenuByRole($role_id, $pagename);
        $this->Session->write('MENULISTS', $show_menu_lists);
    }
    public function index()
    { 
        $this->layout = 'phase_3_menu';
        $topLayer = Setting::LAYER_SETTING['topLayer'];
        $bottomLayer = Setting::LAYER_SETTING['bottomLayer'];
        if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
        $terms = $this->BrmTerm->find('all', array('conditions' => array('BrmTerm.flag' => '1')));
        $type_order = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $topLayer),
            'fields' => array('LayerType.id' ,'LayerType.name_jp' ,'LayerType.name_en', 'type_order'), 
        ));
        $topLayerNames = $this->Layer->find('all', array(
            'fields'=>'Layer.*',
            'conditions' => array('Layer.flag' => '1', 'layer_type_id' => $type_order['LayerType']['type_order'])));
        $bottom_type_order = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $bottomLayer),
            'fields' => array('LayerType.id' ,'LayerType.name_jp' ,'LayerType.name_en', 'type_order'), 
        ));
        $bottomLayerNames = $this->Layer->find('all', array(
            'fields'=>array('Layer.*', 'YEAR(Layer.from_date) as from_date'),
            'conditions' => array('Layer.flag' => '1', 'layer_type_id' => $bottom_type_order['LayerType']['type_order'])));
        //echo '<pre>';print_r($type_order);echo '</pre>';
        //echo '<pre>';print_r($bottom_type_order);echo '</pre>';
        $this->set(compact('terms', 'type_order', 'topLayerNames', 'bottom_type_order', 'bottomLayerNames', 'lang_name'));
        $this->render('index');
    }

    public function add()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $language = $this->Session->read('Config.language');
        $term_arr = explode(',', $this->request->data('term_id'));
        $this->Session->write('TERM_NAME', explode('(', $term_arr[1])[0]);
        $headquarter = explode(',', $this->request->data('head_dept_id'));
        $this->Session->write('TOP_LAYER', $this->request->data('head_dept_id'));
        $this->Session->write('HEAD_DEPT_NAME', $headquarter[3]);
        $this->Session->write('HEAD_DEPT_CODE', $headquarter[1]);
        $this->Session->write('BrmTemSelections_PERIOD_DATE', $this->request->data('targetmonth'));
        $terms = $this->BrmTerm->find('all', array('conditions' => array('flag' => '1', 'id' => $term_arr[0])));
        //print_r($terms);exit;
        if (!empty($this->request->data('date'))) {
            $targetmonth = date("Y-m-d", strtotime($this->request->data('date') . '-01'));
        } else {
            $targetmonth = date("Y-m-d", strtotime($terms[0]['BrmTerm']['budget_year'] . '-' . $terms[0]['BrmTerm']['start_month'] . '-01'));
        }
        $startdate = date("Y-m-d", strtotime($terms[0]['BrmTerm']['budget_year'] . '-' . $terms[0]['BrmTerm']['start_month'] . '-01'));
        $enddate = date("Y-m-d", strtotime($terms[0]['BrmTerm']['budget_end_year'] . '-' . $terms[0]['BrmTerm']['end_month'] . '-01' . "last day of + 1 year"));

        #put session for layer_type name
        $this->LayerType->virtualFields['layer_type_name'] = $language == 'eng' ? 'name_en' : 'name_jp';
        $layer_type = $this->LayerType->find('list',array(
            'fields' => array('type_order','layer_type_name'),
            'conditions' => array(
                'type_order' => array(SETTING::LAYER_SETTING['topLayer'],SETTING::LAYER_SETTING['middleLayer'],SETTING::LAYER_SETTING['bottomLayer']),
                'flag' => 1,
            ),
            'order' => 'type_order ASC'
        ));
        $this->Session->write('LayerTypeData',$layer_type);

        if (!empty($this->request->data('term_id'))) {
            $term_id = $term_arr[0];
        }
        if (!empty($this->request->data('head_dept_id'))) {
            if (!empty($headquarter[0])) {
                $head_dept_id = $headquarter[2];
            } else {
                $head_dept_id = null;
            }
        }
        
        $date = $this->request->data['date'];
        $ba_code = $this->request->data['ba_code'];

        /* add by HHK */
        //get ba_name from tbl
        $ba_name = $this->Layer->find(
            'all',
            array(
                'conditions' => array(
                    'Layer.id' => $ba_code,
                    'Layer.flag' => 1
                ),
                'fields'  => 'Layer.name_jp,Layer.layer_code'
            )
        );
        if (!empty($ba_name[0]['Layer']['name_jp'])) {
            $layer_code = $ba_name[0]['Layer']['layer_code'];
            $ba_name    = $ba_name[0]['Layer']['name_jp'];  
        } else {
            $layer_code = null;
            $ba_name    = "";
        }
        $this->Session->write('SESSION_LAYER_CODE',$layer_code);
        // Get deadline date by headquarter
        $headqDeadline = array_shift($this->BrmTermDeadline->find('list', array(
            'fields' => array('deadline_date'),
            'conditions' => array('brm_term_id' => $term_id, 'hlayer_code' => $headquarter[1]))));
        $response = array(
            'term_id'         => $this->Session->write('TERM_ID', $term_id),
            'head_dept_id'     => $this->Session->write(
                'HEAD_DEPT_ID',
                $head_dept_id
            ),
            'date'     => $this->Session->write(
                'TARGETMONTH',
                $date
            ),
            'ba_code'         => $this->Session->write('BUDGET_BA_CODE', $ba_code),
            'ba_name' => $this->Session->write('BUDGET_BA_NAME', $ba_name),
            'headq_deadline' => $this->Session->write('HEADQUARTER_DEADLINE', $headqDeadline)

        );
        if ($targetmonth >= $startdate && $targetmonth <= $enddate) {
            if ($this->Session->check('COMPARE_TM')) {     #(added by NU NU LWIN/20200609)
                $this->Session->delete('COMPARE_TM');
            }
            return true;
        } else {
            $this->Session->write('COMPARE_TM', 'error'); #although show err in term selection, enter the BRD page (added by Khin Hnin Myo/20200609)
            return false;
        }

        echo json_encode($response);
    }

    /* added by Hein Htet Ko */
    /* check head dept id */
    public function checkHeadDeptId($hd_id)
    {
        $permission = $this->Session->read('PERMISSION');

        $max_limit = min($permission['BudgetResultDifferenceReadLimit'], $permission['MonthlyReportReadLimit'], $permission['BudgetingSystemReadLimit']);

        if ($max_limit == 1 || $max_limit < 1) {
            # Whole Company (Show all headquarters)
            $headpts = $this->HeadDepartmentModel->find('list', array(
                'fields' => array('id'), 'conditions' => array(
                    'flag' => '1',
                    'head_dept_name !=' => '大阪支社'
                ),
                'order' => $order
            ));
        } elseif ($max_limit == 2) {
            # Show related Headquarter only
            $user_id = $this->Session->read('LOGIN_ID');

            # get user's BA
            $getBA = $this->User->find('list', array(
                'fields' => 'ba_code',
                'conditions' => array(
                    'id' => $user_id,
                    'flag' => 1
                )
            ));
            # get ba code only
            $ba_code = $getBA[$user_id];

            # get headquarter id by ba_code
            $getHq = $this->BusinessAreaModel->find('list', array(
                'fields' => 'head_dept_id',
                'conditions' => array(
                    'ba_code' => $ba_code,
                    'flag' => 1
                )
            ));

            # get headquarters by headquarter id
            $headpts = $this->HeadDepartmentModel->find('list', array(
                'fields' => array('id'), 'conditions' => array(
                    'id' => $getHq,
                    'flag' => '1',
                    'head_dept_name !=' => '大阪支社'
                ),
                'order' => $order
            ));
        }

        if (in_array($hd_id, $headpts)) {
            return true;
        } else {
            return false;
        }
    }
    public function saveBudget() {
        $Common = New CommonController();
        $term_id = 1;
        $termRange = range('2022','2025');
        $ba_code = $layer_code = '30001';
        $budgetData = $this->BrmBudgetPrime->find('all', array(
            'fields' => array(
                'target_year','BrmBudgetPrime.brm_account_id','account_code','logistic_index_no','sum(month_1_amt) As month_1_amt' ,'sum(month_2_amt) As month_2_amt' ,'sum(month_3_amt) As month_3_amt' ,'sum(month_4_amt) As month_4_amt' ,'sum(month_5_amt) As month_5_amt' ,'sum(month_6_amt) As month_6_amt' ,'sum(month_7_amt) As month_7_amt' ,'sum(month_8_amt) As month_8_amt' ,'sum(month_9_amt) As month_9_amt' ,'sum(month_10_amt) As month_10_amt' ,'sum(month_11_amt) As month_11_amt' ,'sum(month_12_amt) As month_12_amt'
            ),
            'conditions' => array(
                'BrmBudgetPrime.brm_term_id' => $term_id,
                'target_year' => $termRange,
                'layer_code' => $ba_code,
                'BrmBudgetPrime.flag' => 1
            ),
            'group' => array('target_year,layer_code,BrmBudgetPrime.brm_account_id,account_code,logistic_index_no')
        ));
        
        $subacc_names = $this->BrmAccount->find('list', array(
            'fields' => array('id','name_jp'),
            'conditions' => array(
                'flag' => 1,
                'group_code' => '01',
            )
        ));

        $today_date = date("Y-m-d H:i:s") ;
        $topLayer = Setting::LAYER_SETTING['topLayer'];
        $middleLayer = Setting::LAYER_SETTING['middleLayer'];
        $ba_data = $this->Layer->find('list', array(
            'fields' => array('layer_code', 'parent_id'),
            'conditions' => array(
                'layer_code' => $layer_code,
                'flag' => 1,
                'to_date >=' => $today_date
            )
        ));
        
        $baArr = json_decode($ba_data[$ba_code], true);
        $budgetSummary = [];
        if (!empty($budgetData)) {
            foreach ($budgetData as $data) { // for one year
                $year           = $data['BrmBudgetPrime']['target_year'];
                $sub_acc_id     = $data['BrmBudgetPrime']['brm_account_id'];
                $sub_acc_name   = $subacc_names[$sub_acc_id];
                $account_code   = $data['BrmBudgetPrime']['account_code'];
                $log_index_no   = $data['BrmBudgetPrime']['logistic_index_no'];

                $start_month    = $Common->getMonth($year, $term_id, 'start');
                
                #Loop through 12 months
                for ($i=0; $i <12 ; $i++) {
                    $tmp = array();
                    #Increase month by loop count
                    $month = date("Y-m", strtotime($start_month. "last day of + ".$i." Month"));
                    #get database field name
                    $field = 'month_'.($i+1).'_amt';

                    $amount = $data[0][$field];
                   
                    $budget_id_amount = $this->BrmBudget->find(
                        'first',
                        array(
                            'fields' => array(
                                'id','amount'),
                            'conditions' => array(
                                'brm_term_id' => $term_id,
                                'hlayer_code' => $baArr['L'.$topLayer],
                                'dlayer_code' => $baArr['L'.$middleLayer],
                                'BrmBudget.account_code' => $account_code,
                                'target_month' => $month,
                                'layer_code' => $layer_code,
                                'BrmBudget.flag' => 1,
                                'logistic_index_no' => $log_index_no
                            ))
                    );
                    if (!empty($budget_id_amount)) {
                        $tmp['id'] = $budget_id_amount['BrmBudget']['id'];
                    }
                    $tmp['target_month'] = $month;
                    $tmp['brm_term_id'] = $term_id;
                    $tmp['hlayer_code'] = $baArr['L'.$topLayer];
                    $tmp['dlayer_code'] = $baArr['L'.$middleLayer];
                    $tmp['layer_code'] = $layer_code;
                    $tmp['team'] = '';
                    $tmp['sub'] = '';
                    $tmp['brm_saccount_id'] = '';
                    $tmp['account_code'] = $account_code;
                    $tmp['amount'] = $amount;
                    $tmp['logistic_index_no'] = $log_index_no;
                    $tmp['flag'] = 1;
                    $tmp['type'] = 0;
                    $tmp['created_by'] = 16;
                    $tmp['updated_by'] = 16;
                    $tmp['created_date'] = date("Y-m-d H:i:s");
                    $tmp['updated_date'] = date("Y-m-d H:i:s");

                    $budgetSummary[] = $tmp;
                }
            }
            try {
                $this->BrmBudget->getDataSource();
                $this->BrmBudget->begin();
                if (!empty($budgetSummary)) {
                    $this->BrmBudget->saveAll($budgetSummary);
                    $this->BrmBudget->commit();
                }               
            } catch (Exception $e) {
                $this->BrmBudget->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            }
        }
    }
    public function CalculateCake() {
       
        $mainbudget = $this->BrmMainBudget->getDataSource();
        $mainresult = $this->BrmMainResult->getDataSource();

        try {
            $logid_arr = [];
            
            $cron_log = $this->BrmCronLog->find('all',array(
                'fields' => array('id','brm_term_id','hlayer_code','layer_code'),
                'conditions' => array(
                    'BrmCronLog.flag' => 1
                ),
                'group' => array('brm_term_id'),
            ));
            
            if (count($cron_log) > 0) {
                
                $mainbudget->begin();
                $mainresult->begin();
                
                foreach ($cron_log as $log_data) {
                    $conditions = [];

                    $id         = $log_data['BrmCronLog']['id'];
                    $term_id    = $log_data['BrmCronLog']['brm_term_id'];
                    $hlayer_code = $log_data['BrmCronLog']['hlayer_code'];
                    $layer_code    = $log_data['BrmCronLog']['layer_code'];

                    $conditions['hlayer_code'] = $hlayer_code;
                    if ($layer_code != 0) {
                        $conditions['layer_code'] = $layer_code;
                    } else {
                        $conditions['layer_code'] = $this->Layer->find('list',array(
                            'fields' => array('Layer.layer_code'),
                            'conditions' => array(
                                'flag' => 1,
                                'Layer.parent_id LIKE ' => '%'.$hlayer_code.'%',
                                'Layer.type_order' => Setting::LAYER_SETTING['bottomLayer']
                            )
                        ));
                    }
                    if ($term_id != 0) { #having term_id means this is budget data
                        $conditions['term_id'] = $term_id;
                        $this->Message->query("update brm_budgets set logistic_index_no='' where logistic_index_no is NULL;");
                        $this->BrmMainBudget->query("DELETE FROM `brm_main_budgets` WHERE brm_term_id=".$term_id.";");
                        $this->BrmMainBudget->query("INSERT INTO `brm_main_budgets` (brm_term_id, brm_account_id, brm_account_name_jp, target_month, hlayer_name, hlayer_code, dlayer_name, dlayer_code, layer_name_jp, layer_code, index_name, transaction_key, amount) 
                            SELECT
                            `BudgetModel`.`brm_term_id`,
                            `SubAccountModel`.`id` AS `sub_acc_id`, 
                            `SubAccountModel`.`name_jp`, 
                            `BudgetModel`.`target_month`,
                            layersHQ.name_jp,
                            layersHQ.layer_code,
                            layersDEPT.name_jp,
                            layersDEPT.layer_code,
                            `Layer`.`name_jp`,
                            `BudgetModel`.`layer_code`,
                            `LogisticModel`.`index_name`, 
                            `BudgetModel`.`logistic_index_no` as transaction_key, 
                            (SUM(amount)) AS  `amount` 
                            FROM `brm_budgets` AS `BudgetModel` 
                            left JOIN `brm_saccounts` AS `AccountModel` ON (
                                `AccountModel`.`account_code` = `BudgetModel`.`account_code` AND
                                `AccountModel`.`flag` = 1
                            ) left JOIN `brm_accounts` AS `SubAccountModel` ON (
                                `SubAccountModel`.`id` = `AccountModel`.`brm_account_id` AND
                                `SubAccountModel`.`flag` = 1
                            ) left JOIN `layers` AS `Layer` ON (
                                `Layer`.`layer_code` = `BudgetModel`.`layer_code` AND
                                `Layer`.`flag` = 1
                            ) left JOIN `layers` as `layersHQ` ON (
                                Layer.parent_id LIKE CONCAT('%', `layersHQ`.`layer_code`, '%') AND `layersHQ`.`type_order` = ".Setting::LAYER_SETTING['topLayer']." AND 
                                `layersHQ`.`flag` = 1 AND Layer.flag=1
                            )LEFT JOIN `layers` as `layersDEPT` ON (
                                Layer.parent_id LIKE CONCAT('%', `layersDEPT`.`layer_code`, '%')  AND `layersDEPT`.`type_order` = ".Setting::LAYER_SETTING['middleLayer']." AND
                                `layersDEPT`.`flag` = 1 AND Layer.flag=1
                            ) left JOIN (select distinct index_name,`brm_logistics`.`index_no`,layer_code,flag from `brm_logistics` group by layer_code)as `LogisticModel` ON (
                                (`BudgetModel`.`logistic_index_no` = `LogisticModel`.`index_no` OR `BudgetModel`.`logistic_index_no` = index_name) AND
                                `BudgetModel`.`logistic_index_no` != '' AND
                                `BudgetModel`.`layer_code` = `LogisticModel`.`layer_code` AND
                                `LogisticModel`.`flag` = 1
                            )
                            WHERE `BudgetModel`.`account_code` != 0 AND `BudgetModel`.`brm_term_id` =".$term_id."
                            GROUP BY 
                            `BudgetModel`.`brm_term_id`, 
                            `BudgetModel`.`target_month`, 
                            `BudgetModel`.`layer_code`, 
                            `BudgetModel`.`logistic_index_no`, 
                            `AccountModel`.`brm_account_id`  
                            ORDER BY 
                            `BudgetModel`.`brm_term_id` ASC, 
                            `BudgetModel`.`target_month` ASC, 
                            `layersHQ`.`layer_code` ASC, 
                            `layersDEPT`.`layer_code` ASC, 
                            `BudgetModel`.`layer_code` ASC, 
                            `AccountModel`.`brm_account_id` ASC,
                            `BudgetModel`.`logistic_index_no` ASC;"
                        );
                        // $this->out("Successfully update budget! ".date("Y/m/d h:i:s"));
                        CakeLog::write('debug', 'Successfully update budget! '.date("Y/m/d h:i:s").'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    } else {
                        $this->Message->query("update brm_actual_result_summary set transaction_key='' where transaction_key is NULL;");
                        $this->BrmMainResult->query("DELETE FROM `brm_main_results`");
                        $this->BrmMainResult->query("INSERT INTO `brm_main_results` (id, brm_account_id, brm_account_name_jp, target_month, hlayer_name, hlayer_code, dlayer_name, dlayer_code, layer_name_jp, layer_code, index_name, transaction_key, amount) 
                            SELECT
                            ROW_NUMBER() OVER(ORDER BY (SELECT 1)) AS id,
                            `SubAccountModel`.`id` AS `sub_acc_id`, 
                            `SubAccountModel`.`name_jp`, 
                            `ActualResultSummaryModel`.`target_month`,
                            layersHQ.name_jp,
                            layersHQ.layer_code,
                            layersDEPT.name_jp,
                            layersDEPT.layer_code,
                            `Layer`.`name_jp`,
                            `ActualResultSummaryModel`.`layer_code`,
                            `LogisticModel`.`index_name`, 
                            `ActualResultSummaryModel`.`transaction_key` as transaction_key,
                            (SUM(amount)) AS  `amount` 
                            FROM `brm_actual_results` AS `ActualResultSummaryModel` 
                            left JOIN `brm_saccounts` AS `AccountModel` ON (
                                `AccountModel`.`account_code` = `ActualResultSummaryModel`.`account_code` AND
                                `AccountModel`.`flag` = 1
                            ) left JOIN `brm_accounts` AS `SubAccountModel` ON (
                                `SubAccountModel`.`id` = `AccountModel`.`brm_account_id` AND
                                `SubAccountModel`.`flag` = 1
                             ) left JOIN `layers` AS `Layer` ON (
                                `Layer`.`layer_code` = `ActualResultSummaryModel`.`layer_code` AND
                                `Layer`.`flag` = 1
                            ) left JOIN `layers` as `layersHQ` ON (
                                Layer.parent_id LIKE CONCAT('%', `layersHQ`.`layer_code`, '%') AND `layersHQ`.`type_order` = ".Setting::LAYER_SETTING['topLayer']." AND 
                                                    `layersHQ`.`flag` = 1 AND Layer.flag=1
                            )LEFT JOIN `layers` as `layersDEPT` ON (
                                Layer.parent_id LIKE CONCAT('%', `layersDEPT`.`layer_code`, '%')  AND `layersDEPT`.`type_order` = ".Setting::LAYER_SETTING['middleLayer']." AND `layersDEPT`.`flag` = 1 AND Layer.flag=1
                            ) left JOIN (select distinct index_name,`brm_logistics`.`index_no`,layer_code,flag from `brm_logistics` group by layer_code)as `LogisticModel` ON (
                                (`ActualResultSummaryModel`.`transaction_key` = `LogisticModel`.`index_no` OR `ActualResultSummaryModel`.`transaction_key` = index_name) AND
                                `ActualResultSummaryModel`.`transaction_key` != '' AND
                                `ActualResultSummaryModel`.`layer_code` = `LogisticModel`.`layer_code` AND
                                `LogisticModel`.`flag` = 1
                            )
                            WHERE `ActualResultSummaryModel`.`account_code` != 0
                            GROUP BY 
                            `ActualResultSummaryModel`.`target_month`, 
                            `ActualResultSummaryModel`.`layer_code`, 
                            `ActualResultSummaryModel`.`transaction_key`, 
                            `AccountModel`.`brm_account_id`  
                            ORDER BY 
                            `ActualResultSummaryModel`.`target_month` ASC, 
                            `layersHQ`.`layer_code` ASC, 
                            `layersDEPT`.`layer_code` ASC, 
                            `ActualResultSummaryModel`.`layer_code` ASC, 
                            `AccountModel`.`brm_account_id` ASC,
                            `ActualResultSummaryModel`.`transaction_key` ASC;"
                        );
                        // $this->out("Successfully update result! ".date("Y/m/d h:i:s"));
                        CakeLog::write('debug', 'Successfully update result! '.date("Y/m/d h:i:s").'. In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    }

                    $id_arr = $this->BrmCronLog->find('list',array(
                        'field' => array('id'),
                        'conditions' => array(
                            'flag' => 1,
                            'brm_term_id' => $term_id,
                        )
                    ));
                    
                    $this->BrmCronLog->updateAll(
                        array('BrmCronLog.flag' => 0),
                        array('BrmCronLog.id' => $id_arr)
                    );
                }
                     
                $mainbudget->commit();
                $mainresult->commit();
                
                Cache::clear();
            }


        } catch (Exception $e) {
            // $this->out("Update failed!".$e->getMessage());
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $mainbudget->rollback();
            $mainresult->rollback();
        }  
     
    }
    public function saveResultSummary(){
        $sql  = "INSERT INTO `brm_actual_result_summary`
        (`hlayer_code`,`target_month`,`layer_code`,`account_code`,`transaction_key`,`submission_deadline_date`,destination_code,`amount`,`updated_date`)
        SELECT `hlayer_code`,`target_month`,`layer_code`,`account_code`,`transaction_key`,`submission_deadline_date`,destination_code, sum(amount) as amount,`updated_date` FROM brm_actual_results group by layer_code,account_code,target_month,submission_deadline_date,transaction_key,destination_code";
        $this->BrmMainResult->query($sql);
        
    }
}
