<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Terms Controller
 *
 * @property Term $Term
 * @property PaginatorComponent $Paginator
 */
class BrmTermsController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array(
        'BrmTerm', 'Layer', 'BrmBudgetApprove', 'BrmTermDeadline', 'BrmBudget', 'BrmSummary',
        'BrmBudgetPrime', 'BrmCashFlow', 'BrmCeoComment', 'BrmInvestment', 'BrmMainBudget',
        'BrmManpowerPlan', 'BrmExpectedBudgetDiffJob', 'BrmExpected', 'BrmExpectedBudgetDiffAccount',
        'BrmManpowerPlanOt', 'BrmSmExplain', 'BrmCopyTermLog', 'BrmForecastSummary', 'BrmBudgetSummary'
    );

    public $components = array('Session', 'Flash', 'Paginator');
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();

        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Login', 'action' => 'logout'));
        // }
    }

    /**
     * index method
     *
     * @author Kaung Zaw Thant (20200220)
     * @return void
     */
    public function index()
    {
        $this->layout = 'mastermanagement';
        $successMsg = "";

        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');

        $hdMode = 'Save';

        $conditions = array();
        $conditions["BrmTerm.flag !="] = 0;
        //$conditions["BrmTermDeadline.flag !="] = 0;
        try {
            $this->paginate = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'order' => array('BrmTerm.id' => 'DESC'),
            );

            $all_term = h($this->Paginator->paginate('BrmTerm'));

            $total_pages = $this->params['paging']['BrmTerm']['pageCount'];
            $count = $this->params['paging']['BrmTerm']['count'];
            $limit = $this->params['paging']['BrmTerm']['limit'];
            $page = $this->params['paging']['BrmTerm']['page'];

            $no = ($page - 1) * $limit + 1;

            //show total row msg
            if ($count == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $count));
                $this->set('errmsg', "");
            }

            $head_department = array();
            $head_dept_data = array();
            $deadline_dates = array();
            $language = ($this->Session->read('Config.language') == 'eng') ? 'en' : 'jp';

            //head_department for popupwindow
            $head_department = $this->Layer->find('list', array(
                'conditions' => array(
                    'flag'   => 1,
                    'Layer.type_order =' => Setting::LAYER_SETTING['topLayer'],
                    'Layer.to_date >=' => date("Y-m-d"),
                ),
                'fields'     => array('id', 'name_' . $language)
            ));
            // pr($head_department);die;
            $head_dept_id = $this->BrmBudgetApprove->find('list', array(
                'conditions' => array('flag' => 2),
                'fields'     => array('hlayer_code')
            ));

            $head_dept_data = $this->Layer->find('list', array(
                'conditions' => array(
                    'id' => $head_dept_id,
                    // 'Layer.type_order =' =>Setting::LAYER_SETTING['topLayer'],
                    'flag' => 1
                ),
                'fields'     => array('id', 'name_' . $language)
            ));
            
            
            $this->Session->write('HQ_LIST', $head_department);
            $this->set('head_department', $head_department);
            $this->set('head_dept_data', $head_dept_data);
            $this->set('all_term', $all_term);
            $this->set('count', $count);
            $this->set('number', $no);
            $this->set('hdMode', $hdMode);
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect('index');
        }
    }

    /**
     * add method
     * @author Kaung Zaw Thant (20200220)
     * @return void
     */
    public function saveTerm()
    {
        if (isset($this->request->data) && !empty($this->request->data)) {
            $page_no        = $this->request->data('hid_page_no');
            $term_id        = $this->request->data('term_id');
            $budget_year    = $this->request->data('period_date');
            $term           = $this->request->data('term');
            $term_name      = $this->request->data('term_name');
            $budget_start_month = $this->request->data('start_month');
            $deadline_date      = $this->request->data('deadline_date');
          
            $forecast_period    = $this->request->data('forecast_period');
            $budget_end_year    = $budget_year + $term;

            if ($budget_start_month == 1) {
                $budget_end_month = 12;
            } else {
                $budget_end_month = $budget_start_month - 1;
            }

            $hd_mode = 'Save';

            //duplicate term_name
            #reuse:checking term_name duplicate and show error msg by khinhninmyo(2021.05.07)
            $term_datas = $this->BrmTerm->find('all', array('fields' => 'term_name,flag'));
            if (!empty($term_datas)) {
                foreach ($term_datas as $value) {
                    if ($value['BrmTerm']['term_name'] == trim($term_name) && $value['BrmTerm']['flag'] != 0) {
                        $errorMsg = parent::getErrorMsg('SE002', __("期間名"));
                        $this->Flash->set($errorMsg, array("key" => "UserError"));

                        return $this->redirect(array('controller' => 'Term', 'action' => 'index/' . $page_no));
                    }
                }
            }

            try {
                $term_list = array(
                    'term_name'             => $term_name,
                    'budget_year'           => $budget_year,
                    'budget_end_year'       => $budget_end_year,
                    'term'                  => $term,
                    'start_month'           => $budget_start_month,
                    'end_month'             => $budget_end_month,
                    'forecast_period'       => $forecast_period,
                    'flag'                  => '1',
                    'created_by'            => $this->Session->read('LOGIN_ID'),
                    'updated_by'            => $this->Session->read('LOGIN_ID'),
                    'created_date'          => date("Y-m-d H:i:s"),
                    'updated_date'          => date("Y-m-d H:i:s")
                );
                $this->BrmTerm->save($term_list);

                #added by HHK
                #get latest term id
                $term_id = $this->BrmTerm->find(
                    'first',
                    array(
                        'fields'        => array('id'),
                        'conditions'    => array('flag' => 1),
                        'order'         => 'id DESC'
                    )
                )['BrmTerm']['id'];

                $deadline_list = array();
                foreach ($deadline_date as $head_id => $deadline) {
                    $hlayer_code = $this->Layer->find(
                        'list',
                        array(
                            'fields'        => array('Layer.id','layer_code'),
                            'conditions'    => array(
                                                    'Layer.flag' => 1,
                                                    'Layer.id' => $head_id,
                                                ),
                        )
                    ); 
                    $deadline_list[] = array(
                        'brm_term_id'       => $term_id,
                        'hlayer_code'       => $hlayer_code[$head_id],
                        'deadline_date'     => date("Y-m-d H:i:s", strtotime($deadline)),
                        'created_by'        => $this->Session->read('LOGIN_ID'),
                        'updated_by'        => $this->Session->read('LOGIN_ID'),
                        'created_date'      => date("Y-m-d H:i:s"),
                        'updated_date'      => date("Y-m-d H:i:s")
                    );
                } 
               
                $this->BrmTermDeadline->saveAll($deadline_list);

                $this->copyBudgetData($budget_year, $budget_start_month, $term_id);
                $successMsg = parent::getSuccessMsg('SS001');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                return $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index'));
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->redirect('index');
            }
        }
    }

    /**
     * edit method
     *
     * @author Kaung Zaw Thant (20200220)
     * @return void
     */
    public function editTerm()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $id = $this->request->data['id'];
        $term = $this->BrmTerm->find('first', array(
            'conditions' => array('id' => $id)
        ));

        $deadline_date_arr = array();
        $hq_deadlinedata = $this->BrmTermDeadline->find('list', array(
            'conditions' => array('brm_term_id' => $id),
            'fields'     => array('id', 'deadline_date'),
            'group' => 'brm_term_id,hlayer_code',
            'order' => 'brm_term_id,hlayer_code'
        ));

       // $this->log(print_r($hq_deadlinedata,true),LOG_DEBUG);
        foreach ($hq_deadlinedata as $hq_id => $deadline) {
            if ($deadline != '0000-00-00 00:00:00') {
                $deadline_date_arr[$hq_id] = date("Y/m/d", strtotime($deadline));
            }
        }
        
        $response = array(
            'id'                => $id,
            'budget_year'       => $term['BrmTerm']['budget_year'],
            'term'              => $term['BrmTerm']['term'],
            'term_name'         => $term['BrmTerm']['term_name'],
            'start_month'       => $term['BrmTerm']['start_month'],
            'end_month'         => $term['BrmTerm']['end_month'],
            'forecast_period'   => $term['BrmTerm']['forecast_period'],
            'hq_deadlinedata'   => $deadline_date_arr,
        );

        echo json_encode($response);
    }

    /**
     * update method
     *
     * @author Kaung Zaw Thant (20200220)
     * @return void
     */

    public function updateTerm()
    {
        if ($this->request->is('post')) {
            $page_no = $this->request->data('hid_page_no');
            $term_id = $this->request->data('term_id');
            $budget_year = $this->request->data('period_date');
            $term = $this->request->data('term');
            $coki_term_name =  $_COOKIE['TERMNAME'];
            $term_name = $this->request->data('term_name');
            $budget_start_month = $this->request->data('start_month');
            $deadline_date = $this->request->data('deadline_date');
            $forecast_period = $this->request->data('forecast_period');
            $budget_end_year = $budget_year + $term;

            #add ayezarnikyaw (06_08_2020)
            if ($term_name != $coki_term_name) {
                #duplicate term_name
                $term_datas = $this->BrmTerm->find('all', array('fields' => 'id,term_name,flag'));

                foreach ($term_datas as $value) {
                    if (
                        $value['BrmTerm']['term_name'] == $term_name
                        &&
                        $value['BrmTerm']['flag'] != 0 && $value['BrmTerm']['id'] != $term_id
                    ) {
                        $errorMsg = parent::getErrorMsg('SE002', __("期間名"));
                        $this->Flash->set($errorMsg, array("key" => "UserError"));

                        return $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index'));
                    }
                }
            }

            if ($budget_start_month == 1) {
                $budget_end_month = 12;
            } else {
                $budget_end_month = $budget_start_month - 1;
            }

            $id_flag = $this->BrmTerm->find(
                'first',
                array(
                    'conditions' => array('id' => $term_id),
                    'fields' => 'flag'
                )
            );

            //update
            if ($id_flag['BrmTerm']['flag'] != 0) {
                $term_list = array(
                    'id'                    => $term_id,
                    'term_name'             => $term_name,
                    'term'                  => $term,
                    'start_month'           => $budget_start_month,
                    'end_month'             => $budget_end_month,
                    'budget_end_year'       => $budget_end_year,
                    'forecast_period'       => $forecast_period,
                    'updated_by'            => $this->Session->read('LOGIN_ID'),
                    'updated_date'          => date("Y-m-d H:i:s")
                );
                $hisArr['id']               = $term_id;
                $hisArr['org_id']           = $term_id;
                $hisArr['page_name']        = 'Term';
                $hisArr['table_name']       = 'brm_terms';
                $hisArr['term_name']        = $term_name;
                $hisArr['term']             = $term;
                $hisArr['start_month']      = $budget_start_month;
                $hisArr['end_month']        = $budget_end_month;
                $hisArr['budget_end_year']  = $budget_end_year;
                $hisArr['forecast_period']  = $forecast_period;
                $hisArr['created_by']       = $this->Session->read('LOGIN_ID');
                $hisArr['created_date']     = date('Y-m-d H:i:s');
                // $Common                     = new CommonController(); #To import CommonController
                // $Common->saveHistory($hisArr, "BrmTerm");

                $this->BrmTerm->save($term_list);

                $deadline_list = array();
                foreach ($deadline_date as $head_id => $deadline) {

                    $hlayer_code = $this->Layer->find(
                        'list',
                        array(
                            'fields'        => array('Layer.id','layer_code'),
                            'conditions'    => array(
                                                    'Layer.flag' => 1,
                                                    'Layer.id' => $head_id,
                                                ),
                        )
                    ); 

                    $tmp = array(
                        'brm_term_id'           => $term_id,
                        'hlayer_code'           => $hlayer_code[$head_id],
                        'deadline_date'         => date("Y-m-d H:i:s", strtotime($deadline)),
                        'updated_by'            => $this->Session->read('LOGIN_ID')
                    );
                    $his_tmp = array(
                        'id'                    => $term_id,
                        'org_id'                => $term_id,
                        'page_name'             => 'Term',
                        'table_name'            => 'brm_term_deadlines',
                        'hlayer_code'           => $hlayer_code[$head_id],
                        'deadline_date'         => date("Y-m-d H:i:s", strtotime($deadline)),
                        'created_by'            => $this->Session->read('LOGIN_ID'),
                        'created_date'          => date('Y-m-d H:i:s')
                    );
                    $deadline_exist_id = $this->BrmTermDeadline->find('first', array(
                        'fields' => 'id',
                        'conditions' => array(
                            'brm_term_id' => $term_id,
                            'hlayer_code' => $hlayer_code[$head_id],
                        )
                    ))['BrmTermDeadline']['id'];

                    if (!empty($deadline_exist_id)) {
                        $tmp['id'] = $deadline_exist_id;
                    } else {
                        $tmp['created_by'] = $this->Session->read('LOGIN_ID');
                    }
                    $deadline_list[] = $tmp;
                    $his_deadline_list[] = $his_tmp;
                }
                // $Common->saveHistory($his_deadline_list, "BrmTermDeadline");
                //pr($deadline_list);die;
                $this->BrmTermDeadline->saveAll($deadline_list);

                $successMsg = parent::getSuccessMsg('SS002');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" => "UserError"));
            }
            #end add ayezarnikyaw (06_08_2020)

            return $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index/' . $page_no));
        }
    }

    /**
     * delete method
     *
     * @author Kaung Zaw Thant (20200220)
     * @return void
     */
    public function deleteTerm()
    {
        if ($this->request->is('post')) {
            $page_no = $this->request->data('hid_page_no');
            $term_id = $this->request->data('term_id');
            $login_id = $this->Session->read('LOGIN_ID');

            /*$con = mysqli_connect("localhost","root","","sumisho_prod_20201222");
            $listdbtables = array_column(mysqli_fetch_all($con->query('SHOW TABLES FROM sumisho_prod_20201222')),0);
            foreach ($listdbtables as $key => $value) {
                $sqlqry = "select * from ".$value." where term_id=".$term_id;
                $nameResult = mysqli_query($con,$sqlqry);
                if(!empty($nameResult)) {
                    pr($sqlqry.";");
                }
            }die();
            */
            $id_flag = $this->BrmTerm->find(
                'first',
                array(
                    'conditions' => array('id' => $term_id),
                    'fields' => 'flag'
                )
            );
            $delTables = array(
                'BrmBudget',
                'BrmBudgetApprove',
                'BrmBudgetPrime',
                'BrmCashFlow',
                'BrmCeoComment',
                'BrmExpected',
                'BrmExpectedBudgetDiffAccount',
                'BrmExpectedBudgetDiffJob',
                'BrmTermDeadline',
                'BrmInvestment',
                'BrmMainBudget',
                'BrmManpowerPlan',
                'BrmManpowerPlanOt',
                'BrmSmExplain', // PlExplainModel
                'BrmSummary', //'SummaryModel', 
                'BrmForecastSummary',
                'BrmBudgetSummary'
            );

            if ($id_flag['BrmTerm']['flag'] != 0) {
                $delete_term = array('id' => $term_id, 'flag' => 0, 'updated_by' => $login_id, 'updated_date' => date("Y-m-d H:i:s"));

                $this->BrmTerm->save($delete_term);
                # Delete saved data
                //  pr($term_id);die;
                foreach ($delTables as $tableName) {
                    $this->$tableName->deleteAll(array(
                        'brm_term_id' => $term_id,
                    ), false);
                }
                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" => "UserError"));
            }

            $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index/' . $page_no));
        }
    }
    /**
     * GetPopupValue method
     *
     * @author Aye Zar Ni Kyaw(20200715)
     * @return response datas
     */
    public function GetPopupValue()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $id = $this->request->data['id'];

        $today_date = date("Y/m/d");
        $hq_list = array();

        $term = $this->BrmTerm->find('first', array(
            'conditions' => array('id' => $id)
        ));

        $language = ($this->Session->read('Config.language') == 'eng') ? 'en' : 'jp';
        $hqs = $this->Layer->find('list', array(
            'fields'     => array('id', 'name_' . $language),
            'conditions' => array(
                'flag'   => 1,
            ),
        ));

        $ba_by_hq = $this->Layer->find('list', array(
            'fields' => array('Layer.id', 'layer_code'),
            'conditions' => array(
                'Layer.flag' => 1,
                // 'dept_id !=' => '',
                // 'head_dept_id !=' => '',
                'type_order' => Setting::LAYER_SETTING['topLayer'],
                'to_date >=' => $today_date
            ),
            'order' => 'Layer.id ASC'
        ));

        foreach ($ba_by_hq as $head_id => $ba_data) {
            // $this->log(print_r($ba_data,true),LOG_DEBUG);die();
            $approved_count = $this->BrmBudgetApprove->find('count', array(
                'conditions' => array(
                    'brm_term_id'               => $id,
                    'layer_code IN'             => array($ba_data),
                    'BrmBudgetApprove.flag'     => 2,
                    'hlayer_code'               => $head_id
                )
            ));
            if ($approved_count == count($ba_data)) {
                $tmp = array();
                $tmp['hq_id']   = $head_id;
                $tmp['hq_name'] = $hqs[$head_id];
                $hq_list[]      = $tmp;
            }
        }

        $response = array(
            'term_id'           => $term['BrmTerm']['id'],
            'term_name'         => $term['BrmTerm']['term_name'],
            'approved_hq'       => $hq_list
        );

        echo json_encode($response);
    }
    /**
     * Copy&Clone Datas method
     *
     * @author Aye Zar Ni Kyaw(20200707)
     * @return response datas
     */
    public function CopyAndClone()
    {
        $login_id           = $this->Session->read('LOGIN_ID');
        $term_name          = $this->request->data('period_name');
        $term_id            = $this->request->data('term_id');
        $multi_head_dept    = $this->request->data('multi_head_dept');
        $head_dept_arr      = array_unique($multi_head_dept);
        $hqlist             = $this->Session->read('HQ_LIST');
        $clone_data         = array();
        $table_arr          = array();

        //duplicate term_name
        $term_datas = $this->BrmTerm->find('all', array(
            'fields' => 'id,term_name,flag',
            'order'  => 'id DESC'
        ));

        $last_term_id = $term_datas[0]['BrmTerm']['id'];
        $copy_term_id = $last_term_id + 1;


        foreach ($term_datas as $value) {
            if (
                $value['BrmTerm']['term_name'] == $term_name &&
                $value['BrmTerm']['flag'] != 0
            ) {
                $errorMsg = parent::getErrorMsg('SE002', __("期間名"));
                $this->Flash->set($errorMsg, array("key" => "UserError"));

                return $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index'));
            }
        }

        //copy and save all term
        try {
            if (!empty($term_id)) {
                $termdata = $this->BrmTerm->find('first', array(
                    'fields' => array(
                        'budget_year', 'budget_end_year', 'term', 'start_month', 'end_month', 'forecast_period', 'flag',
                    ),
                    'conditions' => array(
                        'id' => $term_id
                    )
                ));

                if (!empty($termdata)) {
                    $tmp = array();
                    $tmp = $termdata['BrmTerm'];
                    $tmp['id'] = $copy_term_id;
                    $tmp['brm_term_id'] = $term_name;
                    $tmp['term_name'] = $term_name;
                    $tmp['created_by'] = $login_id;
                    $tmp['updated_by'] = $login_id;

                    $clone_data = $tmp;
                }

                $clone_tables = array(
                    'BrmBudget',
                    'BrmBudgetApprove',
                    'BrmBudgetPrime',
                    'BrmCashFlow',
                    'BrmCeoComment',
                    'BrmExpected',
                    'BrmExpectedBudgetDiffAccount',
                    'BrmExpectedBudgetDiffJob',
                    'BrmTermDeadline',
                    'BrmInvestment',
                    'BrmMainBudget',
                    'BrmManpowerPlan',
                    'BrmManpowerPlanOt',
                    'BrmSmExplain',
                    'BrmForecastSummary',
                    'BrmBudgetSummary'
                );

                foreach ($clone_tables as $tableName) {
                    $datas = array();
                    if (($tableName == 'BrmBudgetApprove') && (count($head_dept_arr) > 0)) {
                        $datas = $this->$tableName->find('all', array(
                            'conditions' => array(
                                $tableName . '.brm_term_id' => $term_id,
                                $tableName . '.hlayer_code' => $head_dept_arr
                            )
                        ));
                    } elseif ($tableName != 'BrmBudgetApprove') {
                        if ($tableName == 'BrmManpowerPlan') {
                            $datas = $this->$tableName->find('all', array(
                                'fields' => array(
                                    'id',
                                    'brm_term_id',
                                    'target_year',
                                    'layer_code',
                                    'type',
                                    'display_no',
                                    'brm_position_id',
                                    'unit_salary',
                                    'month_1_amt',
                                    'month_2_amt',
                                    'month_3_amt',
                                    'month_4_amt',
                                    'month_5_amt',
                                    'month_6_amt',
                                    'month_7_amt',
                                    'month_8_amt',
                                    'month_9_amt',
                                    'month_10_amt',
                                    'month_11_amt',
                                    'month_12_amt',
                                    'filling_date',
                                    'deadline_date',
                                    'flag',
                                    'created_by',
                                    'updated_by',
                                    'created_date',
                                    'updated_date'
                                ),
                                'conditions' => array(
                                    $tableName . '.brm_term_id' => $term_id
                                ),
                                'order' => array($tableName . '.id'),
                            ));
                        } else {
                            if($tableName == 'MainBudgetModel') {
                                $datas = $this->$tableName->find('all', array(
                                    'conditions' => array(
                                        $tableName . '.brm_term_id' => $term_id
                                    ),
                                    'order' => array($tableName . '.id'),
                                    'group' => array($tableName . '.id'),#public $virtualFields = array('total_amount' => 'SUM(amount)') in MainBudgetModel;
                                ));
                            }else{
                                $datas = $this->$tableName->find('all', array(
                                    'conditions' => array(
                                        $tableName . '.brm_term_id' => $term_id
                                    ),
                                    'order' => array($tableName . '.id'),
                                ));
                            }  
                        }
                    }
                  
                    if (($tableName != 'BrmMainBudget' && !empty($datas)) ||
                    ($tableName == 'BrmMainBudget' && !empty($datas[0]['BrmMainBudget']['brm_term_id']))
                    ) {
                        
                        $save_data = [];
                        foreach ($datas as $each_data) {
                            $tmp = array();
                            unset($each_data[$tableName]['id']);
                            unset($each_data[$tableName]['created_date']);
                            unset($each_data[$tableName]['updated_date']);
                            $tmp = $each_data[$tableName];
                            foreach ($each_data[$tableName] as $field => $value) {
                                if (!empty($value) || $value != '') {
                                    $tmp[$field] = $value;
                                }
                            }
                            $tmp['brm_term_id'] = $copy_term_id;
                            $tmp['created_by'] = ($tableName == 'BrmBudgetApprove') ? $each_data[$tableName]['created_by'] : $login_id;
                            $tmp['updated_by'] = ($tableName == 'BrmBudgetApprove') ? $each_data[$tableName]['updated_by'] : $login_id;
                         
                            $save_data[] = $tmp;
                        }
                   
                        $attachDB = $this->$tableName->getDataSource();
                        #Save new data
                       
                            $save = $this->$tableName->saveAll($save_data);

                        $table_arr[] = $attachDB;

                        if ($save != true) {
                            throw new Exception("can't copy data for " . $tableName, 1);
                        } else {
                            $attachDB->commit();
                        }
                    }
                
                }
                $TermDB = $this->BrmTerm->getDataSource();
                // $table_arr[] = $TermDB;
                #Save new data
             
                $this->BrmTerm->saveAll($clone_data);
                $TermDB->commit();
                #Save log into tbl_copy_term_log
                $check_term_copy_log = $this->BrmCopyTermLog->find(
                    'list',
                    array(
                        'conditions' => array(
                            'brm_term_id' => $term_id,
                            'copy_brm_term_id' => $copy_term_id
                        )
                    )
                );
                if (empty($check_term_copy_log)) {
                    $copy_log_data = array(
                        'brm_term_id' => $term_id,
                        'copy_brm_term_id' => $copy_term_id,
                        'created_by' => $login_id
                    );

                    $copy_term_log_db = $this->BrmCopyTermLog->getDataSource();
                    $copy_term_log_db->begin();

                    $this->BrmCopyTermLog->save($copy_log_data);
                    $copy_term_log_db->commit();
                }

                $successMsg = parent::getSuccessMsg('SS025', __('期間データ'));
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index'));
            }
        } catch (Exception $e) {
            foreach ($clone_tables as $tableName) {
                # Delete saved data
                $this->$tableName->deleteAll(array(
                    'brm_term_id' => $copy_term_id,
                ), false);
            }

            $errorMsg = parent::getErrorMsg('SE092');
            $this->Flash->set($errorMsg, array("key" => "UserError"));

            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index'));
        }
    }

    /**
     * Copy Data into Budget and Job
     *
     * @author Hein Htet Ko (20201022)
     * @return void
     */
    public function copyBudgetData($budget_year, $budget_start_month, $term_id)
    {
        #prepare target months to copy
        $target_months = array();
        $total_months = 12;
        $start_month = $budget_start_month;
        $b_year = $budget_year;
        for ($i = 0; $i < $total_months; $i++) {
            if (strlen($start_month) == 1) {
                array_push($target_months, $b_year . '-0' . $start_month);
            } else {
                array_push($target_months, $b_year . '-' . $start_month);
            }
            if ($start_month == $total_months) {
                $start_month = 0;
                $b_year++;
            }
            $start_month++;
        }
        $index = 0;
        $get_term_id = $this->BrmForecastSummary->find('first', array(
            'conditions' => array( 
                'target_year' => $budget_year
            ),
            'fields' => 'brm_term_id',
            'group' => 'brm_term_id',
            'order' => 'brm_term_id DESC'
        ))['BrmForecastSummary']['brm_term_id'];
        if(!empty($get_term_id)) {
            $budget_data = $this->BrmBudget->find('all', array(
                'conditions' => array( 
                    'BrmBudget.flag' => 1,
                    'target_month' => $target_months,
                    'brm_term_id' => $get_term_id
                )
            ));
            $forecast_summary_data = $this->BrmForecastSummary->find('all', array(
                'conditions' => array( 
                    'target_year' => $budget_year,
                    'brm_term_id' => $get_term_id
                )
            ));

            $budget_summary_data = $this->BrmBudgetSummary->find('all', array(
                'conditions' => array( 
                    // 'target_year' => $budget_year,
                    'brm_term_id' => $get_term_id
                )
            ));
            $expected_budget_diff_acc_data = $this->BrmExpectedBudgetDiffAccount->find('all', array(
                'conditions' => array( 
                    'target_year' => $budget_year,
                    'brm_term_id' => $get_term_id
                )
            ));
            #prepare data collections for budget
            foreach ($budget_data as $value) {
                $save_data[] = array(
                    'target_month'      => $target_months[$index],
                    'brm_term_id'       => $term_id,
                    'hlayer_code'       => $value['BrmBudget']['hlayer_code'],
                    'dlayer_code'       => $value['BrmBudget']['dlayer_code'],
                    'layer_code'        => $value['BrmBudget']['layer_code'],
                    'team'              => $value['BrmBudget']['team'],
                    'sub'               => $value['BrmBudget']['sub'],
                    'account_id'        => $value['BrmBudget']['account_id'],
                    'account_code'      => $value['BrmBudget']['account_code'],
                    'amount'            => $value['BrmBudget']['amount'],
                    'logistic_index_no' => $value['BrmBudget']['logistic_index_no'],
                    'flag'              => $value['BrmBudget']['flag'],
                    'type'              => $value['BrmBudget']['type'],
                    'created_by'        => $value['BrmBudget']['created_by'],
                    'updated_by'        => $value['BrmBudget']['updated_by'],
                    'created_date'      => date('Y-m-d H:i:s'),
                    'updated_date'      => date('Y-m-d H:i:s')
                );
                $index++;
                if ($index == $total_months) {
                    $index = 0;
                }
            }
            #prepare data collections for forecast summary
            foreach ($forecast_summary_data as $key => $value) {
                $forecast_summary_data[$key]['BrmForecastSummary']['id'] = '';
                $forecast_summary_data[$key]['BrmForecastSummary']['brm_term_id'] = $term_id;
                $forecast_summary_data[$key]['BrmForecastSummary']['created_date'] = date('Y-m-d H:i:s');
            }

            #prepare data collections for budget summary
            foreach ($budget_summary_data as $key => $value) {
                $budget_summary_data[$key]['BrmBudgetSummary']['id'] = '';
                $budget_summary_data[$key]['BrmBudgetSummary']['brm_term_id'] = $term_id;
                $budget_summary_data[$key]['BrmBudgetSummary']['created_date'] = date('Y-m-d H:i:s');
            }
            #prepare data collections for expected budget diff acc 
            foreach ($expected_budget_diff_acc_data as $key => $value) {
                $expected_budget_diff_acc_data[$key]['BrmExpectedBudgetDiffAccount']['id'] = '';
                $expected_budget_diff_acc_data[$key]['BrmExpectedBudgetDiffAccount']['brm_term_id'] = $term_id;
                $expected_budget_diff_acc_data[$key]['BrmExpectedBudgetDiffAccount']['created_date'] = date('Y-m-d H:i:s');
                $expected_budget_diff_acc_data[$key]['BrmExpectedBudgetDiffAccount']['updated_date'] = date('Y-m-d H:i:s');
            }
            if (!empty($save_data)) {
                #save data into budget table
                $this->BrmBudget->saveAll($save_data);
            }

            if (!empty($term_id)) {
                #save data into budget summary table
                $this->BrmForecastSummary->saveAll($forecast_summary_data);
                $this->BrmBudgetSummary->saveAll($budget_summary_data);
                $this->BrmExpectedBudgetDiffAccount->saveAll($expected_budget_diff_acc_data);
            }
        }
       

        #get latest term in tbl_expected_budget_diff_job  
        $selected_term_job = $this->BrmExpectedBudgetDiffJob->find(
            'first',
            array(
                'fields'        => array('DISTINCT brm_term_id'),
                'conditions'    => array('target_year' => $budget_year,  'type' => 'budget'),
                'order'         => 'brm_term_id DESC'
            )
        )['BrmExpectedBudgetDiffJob']['brm_term_id'];
        if(!empty($selected_term_job)) {
            #get job data to copy
            $job_data = $this->BrmExpectedBudgetDiffJob->find(
                'all',
                array(
                    'conditions' => array('brm_term_id' => $selected_term_job, 'target_year' => $budget_year, 'type' => 'budget'),
                )
            );
            #prepare data collections for job
            foreach ($job_data as $value) {
                $save_data_job[] = array(
                    'brm_term_id'   => $term_id,
                    'target_year'   => $budget_year,
                    'layer_code'    => $value['BrmExpectedBudgetDiffJob']['layer_code'],
                    'name_jp'       => $value['BrmExpectedBudgetDiffJob']['name_jp'],
                    'amount'        => $value['BrmExpectedBudgetDiffJob']['amount'],
                    'factor'        => $value['BrmExpectedBudgetDiffJob']['factor'],
                    'type'          => $value['BrmExpectedBudgetDiffJob']['type'],
                    'created_by'    => $value['BrmExpectedBudgetDiffJob']['created_by'],
                    'updated_by'    => $value['BrmExpectedBudgetDiffJob']['updated_by'],
                    'created_date'  => date('Y-m-d H:i:s'),
                    'updated_date'  => date('Y-m-d H:i:s')
                );
            }
            if (!empty($save_data_job)) {
                #save data into job table
                $this->BrmExpectedBudgetDiffJob->saveAll($save_data_job);
            }
        }
    }

    public function activeStatus()
    {
        if ($this->request->is('post')) {
            $termId = $this->request->data('term_id');
            $pageNo =  $this->request->data('hid_page_no');

            $this->BrmTerm->id = $termId;
            $inactive = array(
                'flag'              => '1',
                'updated_by'        => $this->Session->read('LOGIN_ID'),
                'updated_date'      => date("Y-m-d H:i:s")
            );
            $this->BrmTerm->save($inactive);
            $successMsg = parent::getSuccessMsg('SS017', __('期間'));
            $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index/' . $pageNo));
        }
    }

    public function inactiveStatus()
    {
        if ($this->request->is('post')) {
            $termId = $this->request->data('term_id');
            $pageNo =  $this->request->data('hid_page_no');

            $this->BrmTerm->id = $termId;
            $active = array(
                'flag'              => '3',
                'updated_by'        => $this->Session->read('LOGIN_ID'),
                'updated_date'      => date("Y-m-d H:i:s")
            );
            $this->BrmTerm->save($active);
            $successMsg = parent::getSuccessMsg('SS016', __('期間'));
            $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            $this->redirect(array('controller' => 'BrmTerms', 'action' => 'index/' . $pageNo));
        }
    }
}
