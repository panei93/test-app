<?php
App::uses('AppController', 'Controller');
/**
 * Logistic Controller
 *
 * @property PaginatorComponent $Paginator
 */
class BrmLogisticsController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Session', 'Flash', 'Paginator');
    public $helper = array('form');
    public $uses = array('BrmLogistic', 'Layer', 'BrmTerm', 'ExpectedModel', 'BudgetPrimeModel', 'BrmBudgetApprove');

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
     * @author Name (YYYYMMDD)
     * @return void
     */
    public function index($param = null)
    {
        $this->layout = 'mastermanagement';
        $language = $this->Session->read('Config.language');
        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');

        $conditions = array();
        $year = "";
        $layer_code = "";
        $get_ba = "";
        $btn_copy_mode = false;
       
        $year = !empty($this->request->data('year')) ? $this->request->data('year') : $this->request->query('year');
       
        $baName = !empty($this->request->data('layer_code')) ? $this->request->data('layer_code') : $this->request->query('layer_code');
    //pr($this->request->data('ba_code'));die;
        $layer_code = explode("/", $baName)[0];
     
        if (!empty($year) && !empty($layer_code)) {
            //set session to get search value when pagination
            $this->Session->write('SEARCH_YEAR', $year);
            $this->Session->write('SEARCH_BACODE', $layer_code);
            $btn_copy_mode = true;
            
            $get_ba = $this->BrmLogistic->find('list', array(
                'conditions' => array('target_year' => $year, 'BrmLogistic.flag' => 1),
                'fields' => array('layer_code'),
                'group' => array('layer_code')
            ));
            
            $get_bas = $this->Layer->find('all', array(
                'fields' => array('name_jp', 'name_en', 'layer_code'),
                'conditions' => array(
                    'layer_code' => $get_ba,
                    'Layer.flag' => 1
                ),
                'group' => array('layer_code')
            ));
            
            foreach ($get_bas as $key => $value) {
                if ($language == 'eng') {
                    if (!empty($value['Layer']['name_en'])) {
                        $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'] . '/' . $value['Layer']['name_en'];
                    } else {
                        $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'];
                    }
                } else {
                    $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'] . '/' . $value['Layer']['name_jp'];
                }
            }

            $conditions["BrmLogistic.target_year"] = $year;
            $conditions["BrmLogistic.layer_code"] = $layer_code;
        } else {
            if ($this->request->data('hidSearch') == 'SEARCHALL') {
                $this->Session->write('SEARCH_YEAR', '');
                $this->Session->write('SEARCH_BACODE', '');
            }
        }
        // get search value for pagination
        $sYear = $this->Session->read('SEARCH_YEAR');
        $sBacode = $this->Session->read('SEARCH_BACODE');
        if (!empty($sYear) && !empty($sBacode)) {
            $conditions["BrmLogistic.target_year"] = $sYear;
            $conditions["BrmLogistic.layer_code"] =  $sBacode;
            // when go to other page, will select value for year and ba code
            if (strpos($_SERVER['REQUEST_URI'], 'index')) {
                $year = $sYear;
                $layer_code = $sBacode;
                if ($param == 'trading') {
                    $this->Session->write('SEARCH_YEAR', '');
                    $this->Session->write('SEARCH_BACODE', '');
                    $conditions = array();
                    $year = '';
                    $layer_code = '';
                }
            } else {
                $year = $sYear;
                $layer_code = $sBacode;
            }
            $get_ba = $this->BrmLogistic->find('list', array(
                'conditions' => array('target_year' => $year, 'BrmLogistic.flag' => 1),
                'fields' => array('layer_code'),
                'group' => array('layer_code')
            ));

            $get_bas = $this->Layer->find('all', array(
                'fields' => array('name_jp', 'name_en', 'layer_code'),
                'conditions' => array(
                    'layer_code' => $get_ba,
                    'Layer.flag' => 1
                ),
                'group' => array('layer_code')
            ));
         
            foreach ($get_bas as $key => $value) {
                if ($language == 'eng') {
                    if (!empty($value['Layer']['name_en'])) {
                        $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'] . '/' . $value['Layer']['name_en'];
                    } else {
                        $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'];
                    }
                } else {
                    $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'] . '/' . $value['Layer']['name_jp'];
                }
            }
        }


        $conditions["BrmLogistic.flag !="] = 0;

        try {
            $this->paginate = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'busi',
                        'type' => 'left',
                        'conditions' => 'BrmLogistic.layer_code = busi.layer_code AND busi.flag=1'
                    )
                ),
                'fields' => array('BrmLogistic.*', 'busi.layer_code', 'busi.name_jp', 'busi.name_en'),
                'order' => array('BrmLogistic.target_year' => 'ASC', 'BrmLogistic.layer_code' => 'ASC', 'BrmLogistic.logistic_order' => 'ASC', 'BrmLogistic.index_name' => 'ASC', 'BrmLogistic.index_no' => 'ASC')
            );
            //echo '<pre>';print_r( $this->params);echo '</pre>';
            $all_logistic = h($this->Paginator->paginate('BrmLogistic'));

            $count = $this->params['paging']['BrmLogistic']['count'];
            $page = $this->params['paging']['BrmLogistic']['page'];

            foreach ($all_logistic as $key => $logistic) {
                $logi_order = $logistic['BrmLogistic']['logistic_order'];
                $log_data[$key]['id']             = $logistic['BrmLogistic']['id'];
                $log_data[$key]['year']           = $logistic['BrmLogistic']['target_year'];
                $log_data[$key]['index_no']       = $logistic['BrmLogistic']['index_no'];
                $log_data[$key]['index_name']     = $logistic['BrmLogistic']['index_name'];
                $log_data[$key]['layer_code']     = $logistic['BrmLogistic']['layer_code'];
                $log_data[$key]['logistic_order'] = ($logi_order != 1000) ? $logi_order : '';
                if ($language == 'eng') {
                    if (!empty($logistic['busi']['name_en'])) {
                        $log_data[$key]['ba_name'] = $logistic['busi']['name_en'];
                    } else {
                        $log_data[$key]['ba_name'] = "";
                    }
                } else {
                    $log_data[$key]['ba_name'] = $logistic['busi']['name_jp'];
                }
            }
            //show total row msg
            if ($count == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $count));
                $this->set('errmsg', "");
            }

            $today = date("Y-m-d");
            $active_BA = $this->Layer->find('all', array(
                'fields' => array('id', 'layer_code', 'name_en', 'name_jp'),
                'conditions' => array(
                    'Layer.flag' => 1,
                    // 'dept_id !=' => '',
                    // 'OR' => array('head_dept_id !=' => '', 'head_dept_id !=' => '1', 'head_dept_id !=' => '2'),
                    'Layer.type_order' => Setting::LAYER_SETTING['bottomLayer'],
                    'from_date <=' => $today,
                    'to_date >=' => $today
                ),
                'order' => array('layer_code' => 'ASC')
            ));

            foreach ($active_BA  as $ba) {
                if ($language == 'eng') {
                    if (!empty($ba['Layer']['name_en'])) {
                        $select_BA[$ba['Layer']['layer_code']] = $ba['Layer']['layer_code'] . '/' . $ba['Layer']['name_en'];
                    } else {
                        $select_BA[$ba['Layer']['layer_code']] = $ba['Layer']['layer_code'];
                    }
                } else {
                    $select_BA[$ba['Layer']['layer_code']] = $ba['Layer']['layer_code'] . '/' . $ba['Layer']['name_jp'];
                }
            }

            $get_year = $this->BrmLogistic->find('list', array(
                'conditions' => array('BrmLogistic.flag' => 1),
                'fields' => array('target_year'),
                'group' => array('target_year')
            ));

            #min and max year for copy
            $copy_year = $this->BrmTerm->find('all', array(
                'conditions' => array('flag' => 1),
                'fields'      => array(
                    'min(budget_year) as start',
                    'max(budget_end_year) as end'
                )
            ));

            $start = $copy_year[0][0]['start'];
            $end   = $copy_year[0][0]['end'];
            $copy_year_datas = range($start, $end);
            // $copy_layer_code = $this->Layer->find('list',array(
            // 	'fields' => array('id','layer_code'),
            // 	'conditions' => array(
            // 		'flag' => 1,
            // 		'dept_id !=' => '',
            // 		'head_dept_id !=' => '',
            // 		'from_date <=' => $today,
            // 		'to_date >=' => $today
            // 	)
            // ));

            $logistic_data = $this->BrmLogistic->find('all', array('conditions' => array('flag' => 1)));
            
            $this->set(compact('log_data', 'select_BA', 'count', 'get_year', 'year', 'getba', 'layer_code', 'copy_year_datas', 'btn_copy_mode', 'logistic_data'));
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect('index');
        }
    }

    /**
     * save method
     *
     * @author Kaung Zaw Thant (20201015)
     * @throws NotFoundException
     * @param null
     * @return void
     */

    public function saveAndEditLogistic()
    {
        if ($this->request->is('post')) {
            $actual_link = $_SERVER['HTTP_REFERER'];
            $page_no = $this->request->data('hid_page_no');
            $logistic_index_no = $this->request->data('index_no');
            $logistic_index_name = trim($this->request->data('index_name'));
            $order = $this->request->data('order');
            $order = ($order != '') ? $order : 1000;
            $btn_mode = $this->request->data('hid_btn');
            $id = $this->request->data('logistic_id');
            $year = $this->request->data('target_year');
            $layer_code = $this->request->data('ba_name');
            $layer_code = (empty($layer_code)) ? $this->request->data('hidden_ba') : $this->request->data('ba_name');
            $srh_year = $this->request->data('year');
            $srh_ba = $this->request->data('layer_code');
            $order_data = $this->BrmLogistic->find('first', array('conditions' => array('id !=' => $id, 'logistic_order' => $order, 'target_year' => $year, 'layer_code' => $layer_code, 'flag' => 1), 'fields' => array('id', 'index_name')));
            // if order is same in same year and same ba code , will not save
            if ($order != '' && $order != 1000 && count($order_data) > 0 && $logistic_index_name != $order_data['BrmLogistic']['index_name'] && $order != 0) {
                $errorMsg = parent::getErrorMsg('SE002', __('表示順'));
                $this->Flash->set($errorMsg, array("key" => "UserError"));
                if ($srh_year && $srh_ba) {
                    return $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/' . $page_no . '?year=' . $srh_year . '&layer_code=' . $srh_ba));
                } else {
                    return $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/'));
                }
            }

            if ($btn_mode == 'Update' || $btn_mode == '変更') {
                $id = $this->request->data('logistic_id');
                $srh_year = $this->request->data('year');
                $srh_ba = $this->request->data('layer_code');
                $type = 1;

                $upd_data = $this->BrmLogistic->find('first', array('conditions' => array('id' => $id), 'fields' => 'target_year,layer_code,index_name'));

                $year = (empty($this->request->data('target_year'))) ? $upd_data['BrmLogistic']['target_year'] : $this->request->data('target_year');

                $layer_code = (empty($this->request->data('ba_name'))) ? $upd_data['BrmLogistic']['layer_code'] : $this->request->data('ba_name');

                $srh_ba = str_replace("&", "@", $srh_ba);
                $logistic_list = array(
                    'id'        => $id,
                    'target_year' => $year,
                    'layer_code' => $layer_code,
                    'index_no'        => $logistic_index_no,
                    'index_name' => $logistic_index_name,
                    'logistic_order' => $order,
                    'updated_by'    => $this->Session->read('LOGIN_ID')
                );

                #get all data by logistic_index_name,target_year,layer_code
                $get_data = $this->BrmLogistic->find('all', array('conditions' => array('index_name' => $logistic_index_name, 'target_year' => $year, 'layer_code' => $layer_code, 'flag' => 1), 'fields' => 'id,logistic_order'));
                #create array to update order
                $update_list = array();
                foreach ($get_data as $row) {
                    $update_list[] = array(
                        'id' => $row['BrmLogistic']['id'],
                        'logistic_order' => $order,
                        'updated_by' => $this->Session->read('LOGIN_ID')
                    );
                }
                #save order
                $this->BrmLogistic->saveAll($update_list);
            } else {
                $year = $this->request->data('target_year');
                $layer_code = $this->request->data('ba_name');
                $type = 0;
                $srh_year = "";
                $srh_ba = "";
                $srh_year = $this->request->data('year');
                $srh_ba = $this->request->data('layer_code');
                $srh_ba = str_replace("&", "@", $srh_ba);

                #get order by logistic_index_name,target_year,layer_code
                $get_default_order = $this->BrmLogistic->find('first', array('conditions' => array('index_name' => $logistic_index_name, 'target_year' => $year, 'layer_code' => $layer_code, 'flag' => 1), 'fields' => 'logistic_order'))['BrmLogistic']['logistic_order'];
                if (empty($get_default_order)) {
                    $get_default_order = 1000;
                }
                if ($order == '' || $order == 1000) {
                    $logistic_list = array(
                        'target_year'    => $year,
                        'index_no'        => $logistic_index_no,
                        'index_name'     => $logistic_index_name,
                        'layer_code'         => $layer_code,
                        'logistic_order' => $get_default_order,
                        'flag'             => '1',
                        'created_by'    => $this->Session->read('LOGIN_ID'),
                        'updated_by'    => $this->Session->read('LOGIN_ID')
                    );
                } else {
                    $data_order = $this->BrmLogistic->find('all', array('conditions' => array('index_name' => $logistic_index_name, 'target_year' => $year, 'layer_code' => $layer_code, 'flag' => 1), 'fields' => array('id', 'logistic_order')));
                    $save_list = array();
                    if (!empty($data_order)) {
                        foreach ($data_order as $each) {
                            $save_list[] = array(
                                'id' => $each['BrmLogistic']['id'],
                                'logistic_order' => $order,
                                'updated_by' => $this->Session->read('LOGIN_ID')
                            );
                        }
                        $flag_save = true;
                    }
                    $logistic_list = array(
                        'target_year'       => $year,
                        'index_no'          => $logistic_index_no,
                        'index_name'        => $logistic_index_name,
                        'layer_code'        => $layer_code,
                        'logistic_order'    => $order,
                        'flag'              => '1',
                        'created_by'        => $this->Session->read('LOGIN_ID'),
                        'updated_by'        => $this->Session->read('LOGIN_ID')
                    );
                }
            }

            $logistic_datas = $this->BrmLogistic->find('all', array('conditions' => array('flag' => 1), 'fields' => 'id,target_year,layer_code,index_no,index_name,flag'));

            //duplicate logistic_name
            foreach ($logistic_datas as $value) {
                if ($value['BrmLogistic']['flag'] == 1) {
                    if ($value['BrmLogistic']['target_year'] == $year && $value['BrmLogistic']['layer_code'] == $layer_code) {
                        if (!empty($value['BrmLogistic']['index_no']) && $value['BrmLogistic']['index_name'] == $logistic_index_name) {
                            if ($type == 1) {
                                if ($value['BrmLogistic']['index_no'] == $logistic_index_no && $value['BrmLogistic']['id'] != $id) {
                                    $errorMsg = parent::getErrorMsg('SE002', __('取引コード'));
                                    $this->Flash->set($errorMsg, array("key" => "UserError"));

                                    return $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/' . $page_no . '?year=' . $srh_year . '&layer_code=' . $srh_ba));
                                }
                            } else {
                                if ($value['BrmLogistic']['index_no'] == $logistic_index_no) {
                                    $errorMsg = parent::getErrorMsg('SE002', __('取引コード'));
                                    $this->Flash->set($errorMsg, array("key" => "UserError"));

                                    return $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/' . $page_no . '?year=' . $srh_year . '&layer_code=' . $srh_ba));
                                }
                            }
                        }
                    }
                }
            }

            $this->BrmLogistic->save($logistic_list);
            if ($flag_save) {
                $this->BrmLogistic->saveAll($save_list);
            }
            $successMsg = parent::getSuccessMsg('SS001');
            $this->Flash->set($successMsg, array("key" => "UserSuccess"));

            if (!empty($srh_year) && !empty($srh_ba)) {
                $srh_ba = explode('/', $srh_ba)[0];
                $queryString = 'year=' . $srh_year . '&layer_code=' . $srh_ba;
                return $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/' . $page_no . '?' . $queryString));
            }

            return $this->redirect($actual_link);
        }
    }

    /**
     * edit method
     *
     * @author Kaung Zaw Thant (20201015)
     * @throws NotFoundException
     * @param null
     * @return void
     */

    public function editDeletLogistic()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $id = $this->request->data['id'];
        $btn_mode = $this->request->data['btn_mode'];

        $logistic = $this->BrmLogistic->find('first', array(
            'conditions' => array('BrmLogistic.id' => $id),
            'joins' => array(
                array(
                    'table' => 'brm_expected',
                    'alias' => 'expected',
                    'type'  => 'left',
                    'conditions' => 'BrmLogistic.index_name = expected.logistic_index_no AND BrmLogistic.layer_code = expected.layer_code AND BrmLogistic.target_year = expected.target_year AND expected.flag=1'
                ),
                array(
                    'table' => 'brm_budget_primes',
                    'alias' => 'budget',
                    'type'  => 'left',
                    'conditions' => 'BrmLogistic.index_name = budget.logistic_index_no AND BrmLogistic.layer_code = budget.layer_code AND BrmLogistic.target_year = budget.target_year AND budget.flag=1'
                )
            ),
            'fields' => array('BrmLogistic.*', 'expected.logistic_index_no', 'budget.brm_term_id', 'budget.layer_code', 'budget.logistic_index_no')
        ));

        $term_id    = $logistic['budget']['brm_term_id'];
        $layer_code = $logistic['budget']['layer_code'];

        $approved_logistic = $this->BrmBudgetApprove->find('first', array(
            'conditions' => array(
                'BrmBudgetApprove.brm_term_id'  => $term_id,
                'BrmBudgetApprove.layer_code'   => $layer_code,
                'BrmBudgetApprove.flag'         => '2'
            )
        ));

        $response = array(
            'id'                => $logistic['BrmLogistic']['id'],
            'target_year'       => $logistic['BrmLogistic']['target_year'],
            'index_no'          => $logistic['BrmLogistic']['index_no'],
            'index_name'        => $logistic['BrmLogistic']['index_name'],
            'ba_name'           => $logistic['BrmLogistic']['layer_code'],
            'logistic_order'    => $logistic['BrmLogistic']['logistic_order']
        );

        if (empty($logistic['expected']['logistic_index_no']) && empty($logistic['budget']['logistic_index_no']) && empty($approved_logistic)) {
            $response['log_check'] = 1; #can delete state
        } else {
            $response['log_check'] = 0; #can't delete state
            #can delete index_no==null from index_name have many index_no that are saved in Trading Plan
            if ($btn_mode == 'Delete' && $logistic['BrmLogistic']['index_no'] == '') {
                $logi_cnt = $this->BrmLogistic->find('count', array(
                    'conditions' => array(
                        'flag' => 1,
                        'target_year' => $logistic['BrmLogistic']['target_year'],
                        'layer_code' => $logistic['BrmLogistic']['layer_code'],
                        'index_name' => $logistic['BrmLogistic']['index_name']
                    )
                ));
                if ($logi_cnt > 1) $response['log_check'] = 1; #can delete state
            }
        }

        echo json_encode($response);
    }


    /**
     * delete method
     *
     * @author Kaung Zaw Thant (20201015)
     * @throws NotFoundException
     * @param null
     * @return void
     */
    public function deleteLogistic()
    {
        if ($this->request->is('post')) {
            $page_no        = $this->request->data('hid_page_no');
            $logistic_id    = $this->request->data('logistic_id');
            $login_id       = $this->Session->read('LOGIN_ID');

            $srh_year       = $this->request->data('year');
            $srh_ba         = $this->request->data('layer_code');

            $chk_empty      = $this->BrmLogistic->find('all', array(
                'conditions' => array(
                    'target_year'   => $srh_year,
                    'layer_code'    => $srh_ba,
                    'flag'          => 1
                ),
                'fields'            => 'id'
            ));

            if (count($chk_empty) == 1) {
                $chk_srh_year = "";
                $chk_srh_ba = "";
            } else {
                $chk_srh_year = $srh_year;
                $chk_srh_ba = $srh_ba;
            }

            $id_flag = $this->BrmLogistic->find('first', array(
                'conditions' => array('id' => $logistic_id),
                'fields' => 'flag'
            ));

            if ($id_flag['BrmLogistic']['flag'] == 1) {
                $delete_logistic = array('id' => $logistic_id, 'flag' => 0, 'updated_by' => $login_id, 'updated_date' => date("Y-m-d H:i:s"));
                $this->BrmLogistic->save($delete_logistic);

                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" => "UserError"));
            }

            if ($chk_srh_year != "" && $chk_srh_ba != "") {
                $queryString = 'year=' . $chk_srh_year . '&layer_code=' . $chk_srh_ba;
                $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/' . $page_no . '?' . $queryString));
            }else $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/trading' . $page_no));
            //$this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index/' . $page_no));
        }
    }

    public function getBAcode()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $target_year    = $this->request->data('target_year');
        $language       = $this->Session->read('Config.language');
        $get_ba         = h($this->BrmLogistic->find('list', array(
            'conditions' => array(
                'target_year' => $target_year,
                'BrmLogistic.flag' => 1
            ),
            'fields' => array('layer_code'),
            'group' => array('layer_code')
        )));
       
        $get_bas = $this->Layer->find('all', array(
            'fields' => array('name_jp', 'name_en', 'layer_code'),
            'conditions' => array(
                'Layer.layer_code'  => $get_ba,
                'Layer.flag'        => 1
            ),
            'group' => array('Layer.layer_code')
        ));
        //$this->log(print_r($get_bas,true),LOG_DEBUG);
        foreach ($get_bas as $key => $value) {
            if ($language == 'eng') {
                if (!empty($value['Layer']['name_en'])) {
                    $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'] . '/' . $value['Layer']['name_en'];
                } else {
                    $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'];
                }
            } else {
                $getba[$value['Layer']['layer_code']] = $value['Layer']['layer_code'] . '/' . $value['Layer']['name_jp'];
            }
        }

        echo json_encode($getba);
    }

    public function CopyLogistic()
    {
        $exit_year  = $this->request->data['hid_from_year'];
        $exit_ba    = $this->request->data['hid_from_code'];
        $copy_year  = $this->request->data['to_year'];
        $copy_ba    = $this->request->data['to_ba'];

        $copy_data = $this->BrmLogistic->find('all', array(
            'conditions' => array('flag' => 1, 'target_year' => $copy_year, 'layer_code' => $copy_ba),
            'fields'     => array('index_no', 'index_name')
        ));

        #get requiered data from Logistic table to copy
        $required_data = $this->BrmLogistic->find('all', array(
            'conditions' => array('flag' => 1, 'target_year' => $exit_year, 'layer_code' => $exit_ba),
            'fields' => array('index_no', 'index_name', 'logistic_order')
        ));

        if (!empty($copy_data)) {
            $new_log_data = array();
            $arr_copy_no = array();
            $arr_copy_name = array();
            foreach ($copy_data as $key => $copy) {
                array_push($arr_copy_no, $copy['BrmLogistic']['index_no']);
                array_push($arr_copy_name, $copy['BrmLogistic']['index_name']);
            }
  
            if (!empty(($required_data))) {
                foreach ($required_data as $key => $required) {
                    // if index_no is empty, check index_name, if index_no is empty, check index_no
                    if (($required['BrmLogistic']['index_no'] == '' && !in_array($required['BrmLogistic']['index_name'], $arr_copy_name)) || ($required['BrmLogistic']['index_no'] != '' && !(in_array($required['BrmLogistic']['index_no'], $arr_copy_no)))) {
                        #create new array to copy into Logistic table
                        $new_log_data[] = array(
                            'target_year'       => $copy_year,
                            'index_no'          => $required['BrmLogistic']['index_no'],
                            'index_name'        => $required['BrmLogistic']['index_name'],
                            'layer_code'        => $copy_ba,
                            'flag'              => 1,
                            'logistic_order'    => $required['BrmLogistic']['logistic_order'],
                            'created_by'        => $this->Session->read('LOGIN_ID'),
                            'updated_by'        => $this->Session->read('LOGIN_ID'),
                            'created_date'      => date("Y-m-d H:i:s"),
                            'updated_date'      => date("Y-m-d H:i:s")

                        );
                    }
                }
            } else {
                $msg = parent::getErrorMsg('SE017', __("コピー年度"));
                $this->Flash->set($msg, array('key' => 'UserError'));
            }
        } else {
            
            foreach ($required_data as $key => $value) {

                #create new array to copy into Logistic table
                $new_log_data[] = array(
                    'target_year'    => $copy_year,
                    'index_no'       => $value['BrmLogistic']['index_no'],
                    'index_name'     => $value['BrmLogistic']['index_name'],
                    'layer_code'     => $copy_ba,
                    'flag'           => 1,
                    'logistic_order' => $value['BrmLogistic']['logistic_order'],
                    'created_by'     => $this->Session->read('LOGIN_ID'),
                    'updated_by'     => $this->Session->read('LOGIN_ID'),
                    'created_date'   => date("Y-m-d H:i:s"),
                    'updated_date'   => date("Y-m-d H:i:s")

                );
            }
        }

        if (!empty($new_log_data)) {

            #save all new data into Logistic table
            $this->BrmLogistic->saveAll($new_log_data);
            $successMsg = parent::getSuccessMsg('SS025', '取引のデータ');
            $this->Flash->set($successMsg, array("key" => "UserSuccess"));
        } else {
            $msg = parent::getErrorMsg('SE002', __("取引コード"));
            $this->Flash->set($msg, array('key' => 'UserError'));
        }

        $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index'));
    }

    public function getOverwrite()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $year = $this->request->data('to_year');
        $layer_code = $this->request->data('to_ba');

        $get_data = h($this->BrmLogistic->find('all', array(
            'conditions' => array(
                'target_year'   => $year,
                'layer_code'    => $layer_code,
                'flag'          => 1
            )
        )));

        $exit_trading = false;

        if (!empty($get_data)) {
            $exit_trading = true;
        }

        echo json_encode($exit_trading);
    }

    public function existedCode()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $year = $this->request->data('to_year');
        $layer_code = $this->request->data('to_ba');

        $chk_code = $this->BrmLogistic->find('all', array(
            'conditions' => array('BrmLogistic.flag' => 1, 'BrmLogistic.target_year' => $year, 'BrmLogistic.layer_code' => $layer_code),
            'joins' => array(
                array(
                    'table' => 'brm_expected',
                    'alias' => 'expected',
                    'type'  => 'left',
                    'conditions' => 'BrmLogistic.index_name = expected.logistic_index_no AND BrmLogistic.layer_code = expected.layer_code AND BrmLogistic.target_year = expected.target_year AND expected.flag=1'
                ),
                array(
                    'table' => 'brm_budget_primes',
                    'alias' => 'budget',
                    'type'  => 'left',
                    'conditions' => 'BrmLogistic.index_name = budget.logistic_index_no AND BrmLogistic.layer_code = budget.layer_code AND BrmLogistic.target_year = budget.target_year AND budget.flag=1'
                )
            ),
            'fields' => array('expected.logistic_index_no', 'budget.logistic_index_no'),
            'group' => array('expected.logistic_index_no', 'budget.logistic_index_no')
        ));

        $chk_log = false;

        if (!empty($chk_code)) {
            foreach ($chk_code as $key => $value) {
                if (!empty($value['expected']['logistic_index_no'])) {
                    $exit_log_code[] = $value['expected']['logistic_index_no'];
                    $chk_log = true;
                } elseif (!empty($value['budget']['logistic_index_no'])) {
                    $exit_log_code[] = $value['budget']['logistic_index_no'];
                    $chk_log = true;
                }
            }
        }

        $response = array(

            'arr_code' => $exit_log_code,
            'chk_log' => $chk_log
        );

        echo json_encode($response);
    }

    public function OverwriteLogistic()
    {
        $exit_year = $this->request->data['hid_from_year'];
        $exit_ba = $this->request->data['hid_from_code'];
        $copy_year = $this->request->data['to_year'];
        $copy_ba = $this->request->data['to_ba'];

        $overwrite_data = $this->BrmLogistic->find('all', array(
            'conditions' => array('flag' => 1, 'target_year' => $copy_year, 'layer_code' => $copy_ba),
            'fields'     => array('id', 'target_year')
        ));


        if (!empty($overwrite_data)) {
            foreach ($overwrite_data as $value) {
                $id = $value['BrmLogistic']['id'];
                $this->BrmLogistic->id = $id;
                $this->BrmLogistic->save(array('flag' => 0));
            }

            #get requiered data from Logistic table to copy
            $required_data = $this->BrmLogistic->find('all', array('conditions' => array('flag' => 1, 'target_year' => $exit_year, 'layer_code' => $exit_ba)));
            if (!empty($required_data)) {
                #create new array to copy into Logistic table
                $new_log_data = array();
                foreach ($required_data as $value) {
                    $new_log_data[] = array(
                        'target_year'       => $copy_year,
                        'index_no'          => $value['BrmLogistic']['index_no'],
                        'index_name'        => $value['BrmLogistic']['index_name'],
                        'layer_code'        => $copy_ba,
                        'flag'              => $value['BrmLogistic']['flag'],
                        'logistic_order'    => $value['BrmLogistic']['logistic_order'],
                        'created_by'        => $value['BrmLogistic']['created_by'],
                        'updated_by'        => $value['BrmLogistic']['updated_by'],
                        'created_date'      => date("Y-m-d H:i:s"),
                        'updated_date'      => date("Y-m-d H:i:s")

                    );
                }
                #save all new data into Logistic table
                $this->BrmLogistic->saveAll($new_log_data);
                $successMsg = parent::getSuccessMsg('SS025', '取引のデータ');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            } else {
                $msg = parent::getErrorMsg('SE017', __("コピー年度"));
                $this->Flash->set($msg, array('key' => 'UserError'));
            }
        } else {
            $msg = parent::getErrorMsg('SE017', __(""));
            $this->Flash->set($msg, array('key' => 'UserError'));
        }

        $this->redirect(array('controller' => 'BrmLogistics', 'action' => 'index'));
    }

    /**
     * temporary function to analyze data
     *
     * @author Pan Ei Phyo (20210325)
     * @return void
     */
    public function TempUpdateIndexNo()
    {
        $update_data = array();
        $logistics = $this->BrmLogistic->find('all', array(
            'fields' => array('target_year', 'layer_code', 'index_no', 'count(index_no) as index_count'),
            'conditions' => array(
                'flag' => 1,
                'NOT' => array('index_no' => ''),
            ),
            'group' => array('target_year', 'layer_code', 'index_no'),
            'order' => array(
                'index_count' => 'DESC',
                'target_year',
                'layer_code',
                'index_no'
            ),
        ));

        foreach ($logistics as $logistic) {
            if ($logistic[0]['index_count'] > 1) {
                $logistic_data = $logistic['BrmLogistic'];
                $matches = $this->BrmLogistic->find('all', array(
                    'conditions' => array(
                        'target_year' => $logistic_data['target_year'],
                        'layer_code' => $logistic_data['layer_code'],
                        'index_no' => $logistic_data['index_no'],
                        'flag' => 1,
                    )
                ));

                $loopcnt = 1;
                foreach ($matches as $match) {
                    $update_data[] = array(
                        'id' => $match['BrmLogistic']['id'],
                        'index_no' => $match['BrmLogistic']['index_no'] . $loopcnt,
                        'updated_by' => 1,
                        'updated_date' => date('Y-m-d H:i:s'),

                    );

                    $loopcnt++;
                }
            }
        }
        if (!empty($update_data)) {

            #save all new data into Logistic table
            $this->BrmLogistic->saveAll($update_data);
            print_r("Updated successfully " . count($update_data) . " rows");
        } else {
            print_r("There is no data to update");
        }
    }
}
