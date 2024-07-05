<?php

/**
 * Accountset up Controller
 *
 * @property
 * @property PaginatorComponent $Paginator
 */
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Vendor', 'php-excel-reader/PHPExcel'); # excel
App::import('Controller', 'Common'); # mail
define('UPLOAD_FILEPATH', ROOT); # server
define('UPLOAD_PATH', 'app' . DS . 'temp'); # path

class BrmAccountSetupController extends AppController
{

    /**
     * Components
     *
     * @var array
     */

    public $components = array('Paginator', 'PhpExcel.PhpExcel', 'Flash');
    public $uses = array('LayerType', 'Layer', 'BrmTerm', 'BrmSaccount', 'BrmAccountSetup', 'BrmAccount');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();

        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Login', 'action' => 'logout'));
        // }
        if (!empty($this->request->query('param'))) {
            $this->Session->delete('SearchHQ');
            $this->Session->delete('SearchYear');
        }
    }


    /**
     * Show data in view from tbl_actual_set up
     *
     * @author Sandi khaing (2020022)
     * @return data
     */
    public function index()
    {
        $this->layout = 'mastermanagement';

        # msg with confirmation box by Khin Hnin Myo (Start)

        if ($this->Session->read('Config.language') == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }
        $topLayer = Setting::LAYER_SETTING['topLayer'];

        $type_order = $this->LayerType->find('first', array(
            'conditions' => array('LayerType.flag' => '1', 'type_order' => $topLayer),
            'fields' => array('LayerType.id', 'LayerType.name_jp', 'LayerType.name_en', 'type_order'),
        ));
        $topLayerNames = $this->Layer->find('all', array(
            'fields' => 'Layer.*',
            'conditions' => array('Layer.flag' => '1', 'Layer.name_jp !=' => '大阪支社', 'layer_type_id' => $type_order['LayerType']['type_order'])
        ));

        $terms = $this->BrmTerm->find('all', array('conditions' => array('flag' => '1')));

        # for sub account loop
        $sub_accs_name = $this->BrmAccount->find('list', array(
            'fields' => array('BrmAccount.name_jp'),
            'conditions' => array(
                'group_code' => '01',
                'BrmAccount.flag' => 1
            )
        ));

        # for trading sub account loop
        $trade_accs = $this->BrmAccount->find('list', array(
            'fields' =>  array('BrmAccount.name_jp'),
            'conditions' => array(
                'group_code' => '01',
                'name_jp' => array('売上高', '売上原価'),
                'BrmAccount.flag' => 1
            )
        ));

        try {
            // year to show in dropdown to search
            $yearsArr = array();
            $yearsArr = $this->BrmAccountSetup->find('list', array(
                'fields' => array('BrmAccountSetup.target_year'),
                'conditions' => array(
                    'BrmAccountSetup.flag' => 1
                ),
                'group' => array('BrmAccountSetup.target_year')
            ));
            // head departments to show in tab to search
            $headDepartments = $this->BrmAccountSetup->find('list', array(
                'fields' => array('BrmAccountSetup.hlayer_code', 'layers.name_jp'),
                'conditions' => array(
                    'BrmAccountSetup.flag' => 1
                ),
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'layers',
                        'type' => 'INNER',
                        'conditions' => array(
                            'layers.layer_code = BrmAccountSetup.hlayer_code',
                            'layers.flag' => 1
                        )
                    )
                ),
                'order' => 'BrmAccountSetup.hlayer_code'
            ));
            // year to show in dropdown in popup(this year is form tbl_term)
            $minYear = $this->BrmTerm->find(
                'all',
                array(
                    'fields' => array('budget_year'),
                    'conditions' => array('flag' => '1'),
                    'order' => 'budget_year',
                    'limit' => 1,
                )
            );
            $maxYear = $this->BrmTerm->find(
                'all',
                array(
                    'fields' => array('budget_end_year'),
                    'conditions' => array('flag' => '1'),
                    'order' => 'budget_end_year DESC',
                    'limit' => 1,
                )
            );
            $termYears = range($minYear[0]['BrmTerm']['budget_year'], $maxYear[0]['BrmTerm']['budget_end_year']);

            $hq = '';
            $year = '';

            if ($this->request->is('post')) {
                $hq = $this->request->data['hq'];
                $year = $this->request->data['year'];
                $this->Session->write('SearchHQ', $hq);
                $this->Session->write('SearchYear', $year);
            } elseif ($this->Session->check('SearchHQ') && $this->Session->check('SearchYear')) {
                $hq = $this->Session->read('SearchHQ', $hq);
                $year = $this->Session->read('SearchYear', $year);
            }

            $data_arr = array();
            $i = 0;
          
            foreach ($headDepartments as $id => $name) {
                if ($i == 0 && $hq == '') {
                    $hq = $id;
                }
                $conditions['BrmAccountSetup.flag']             = 1;
                $conditions['BrmAccountSetup.hlayer_code']      = $id;
                if ($year != '') $conditions['BrmAccountSetup.target_year']      = $year;

                $acc_data = $this->BrmAccountSetup->find('all', array(
                    'fields' => array('*', 'Layer.name_jp'),
                    'conditions' => $conditions,
                    'joins' => array(
                        array(
                            'table' => 'layers',
                            'alias' => 'Layer',
                            'type' => 'LEFT',
                            'conditions' => array(
                                'BrmAccountSetup.hlayer_code = Layer.layer_code'
                            )
                        )
                    ),
                    'order' => array('BrmAccountSetup.target_year', 'BrmAccountSetup.order ASC', 'BrmAccountSetup.sub_order ASC')
                ));

                $data_arr[$id] = $acc_data;
                $i++;
            }
            //Show message from session
            $flash_errmsg = $this->Session->read('errorMsg');
            if (!empty($flash_errmsg) || $flash_errmsg != '') {
                $this->Flash->set($flash_errmsg, array("key" => "AccountsetupError"));
                $this->Session->write('errorMsg', '');
            }


            //Show message from session
            $flash_sucmsg = $this->Session->read('successMsg');
            if (!empty($flash_sucmsg) || $flash_sucmsg != '') {
                $this->Flash->set($flash_sucmsg, array("key" => "AccountSetupSuccess"));
                $this->Session->write('successMsg', '');
            }
            //show total row msg
            if (count($data_arr) == 0) {
                $no_data_group1 = parent::getErrorMsg('SE001');
            }
            $this->set(compact('lang_name', 'type_order', 'topLayerNames', 'terms', 'sub_accs_name', 'trade_accs', 'headDepartments', 'yearsArr', 'year', 'data_arr', 'hq', 'termYears', 'no_data_group1'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmAccountSetup', 'action' => 'index'));
        }
    }

    /**
     * save data to  tbl_account_setup
     *
     * @author PanEiPhyo (20200722)
     * @return boolen
     */
    public function saveAccountSetup()
    {
        $login_id = $this->Session->read('LOGIN_ID');

        $date =    date("Y-m-d H:i:s");

        #only allow ajax request
        parent::checkAjaxRequest($this);

        $data = $this->request->data;
        $skip_subacc = [];

        if (!empty($data)) {
            $first_data = json_decode($data[0], true);
            // $this->log(print_r($first_data,true),LOG_DEBUG);
            // $head_id = $first_data['head_id'];
            $head_id = explode(",", $first_data['head_id']);  //$this->log(print_r($pieces,true),LOG_DEBUG);die;
            $year = $first_data['year'];
            $sub_acc_id = $first_data['sub_acc_id'];

            $acc_exist = ($first_data['acc_id'] != 0) ? true : false;
           // $this->log(print_r($head_id[1],true),LOG_DEBUG);
            #get last order of same head deptment, term and year
            $last_order = $this->BrmAccountSetup->find('first', array(
                'fields' => array('order'),
                'conditions' => array(
                    'BrmAccountSetup.hlayer_code' => $head_id[1],
                    'BrmAccountSetup.target_year' => $year,
                    'BrmAccountSetup.flag' => 1
                ),
                'order' => array(
                    'BrmAccountSetup.order DESC',
                )
            ));

            #If there is no data, last order will be 1 for initialize order
            $last_order = (!empty($last_order)) ? $last_order['BrmAccountSetup']['order'] + 1 : 1;

            if ($acc_exist) { #Save sub accounts and accounts
                #get last saved order of same sub account
                $match_order = $this->BrmAccountSetup->find('first', array(
                    'fields' => array('order'),
                    'conditions' => array(
                        'BrmAccountSetup.hlayer_code' => $head_id[1],
                        'BrmAccountSetup.target_year' => $year,
                        'BrmAccountSetup.brm_saccount_id' => $sub_acc_id,
                        'BrmAccountSetup.flag' => 1
                    )
                ));
                $subacc_order = (!empty($match_order)) ? $match_order['BrmAccountSetup']['order'] : $last_order;

                #get last saved sub_order for accounts of same sub account
                $acc_order = $this->BrmAccountSetup->find('first', array(
                    'fields' => array('sub_order'),
                    'conditions' => array(
                        'BrmAccountSetup.hlayer_code' => $head_id[1],
                        'BrmAccountSetup.target_year' => $year,
                        'BrmAccountSetup.brm_saccount_id' => $sub_acc_id,
                        'BrmAccountSetup.flag' => 1
                    ),
                    'order' => array(
                        'BrmAccountSetup.sub_order DESC'
                    )
                ));
                $acc_order = (!empty($acc_order)) ? $acc_order['BrmAccountSetup']['sub_order'] + 1 : 1;

                $param = array();
                foreach ($data as $record) {
                    $save_data     = json_decode($record, true);
                    $temp["target_year"]         = $year;
                    $temp["hlayer_code"]         = $head_id[1];
                    $temp["brm_account_id"]      = $sub_acc_id;
                    $temp["brm_saccount_id"]     = $save_data['acc_id'];
                    $temp["order"]               = $subacc_order;
                    $temp["sub_order"]           = $acc_order;
                    $temp["flag"]                = 1;
                    $temp["created_by"]          = $login_id;
                    $temp["updated_by"]          = $login_id;
                    $temp["created_date"]        = $date;
                    $temp["updated_date"]        = $date;

                    $param[] = $temp;

                    $acc_order++;
                }
            } else { #Save sub accounts

                $param = array();
                foreach ($data as $record) {
                    $save_data     = json_decode($record, true);

                    #get last saved order for sub account
                    $match_count = $this->BrmAccountSetup->find('count', array(
                        'conditions' => array(
                            'BrmAccountSetup.hlayer_code' => $head_id[1],
                            'BrmAccountSetup.target_year' => $year,
                            'BrmAccountSetup.brm_account_id' => $save_data['sub_acc_id'],
                            'BrmAccountSetup.brm_saccount_id' => 0,
                            'BrmAccountSetup.flag' => 1
                        ),
                    ));
                    if ($match_count > 0) {
                        $skip_subacc[] = $save_data['sub_acc_id'];
                    } else {
                        #get last saved order for sub account
                        $subacc_order = $this->BrmAccountSetup->find('first', array(
                            'fields' => array('order'),
                            'conditions' => array(
                                'BrmAccountSetup.hlayer_code' => $head_id[1],
                                'BrmAccountSetup.target_year' => $year,
                                'BrmAccountSetup.brm_account_id' => $save_data['sub_acc_id'],
                                'BrmAccountSetup.flag' => 1
                            ),
                        ));

                        $order = (!empty($subacc_order)) ? $subacc_order['BrmAccountSetup']['order'] : $last_order;

                        $temp["target_year"]         = $year;
                        $temp["hlayer_code"]         = $head_id[1];
                        $temp["brm_saccount_id"]     = 0;
                        $temp["brm_account_id"]      = $save_data['sub_acc_id'];
                        $temp["order"]               = $order;
                        $temp["sub_order"]           = 0;
                        $temp["flag"]                = 1;
                        $temp["created_by"]          = $login_id;
                        $temp["updated_by"]          = $login_id;
                        $temp["created_date"]        = $date;
                        $temp["updated_date"]        = $date;

                        $param[] = $temp;

                        if (empty($subacc_order)) {
                            $last_order++;
                        }
                    }
                }
            }

            $this->BrmAccountSetup->saveAll($param);
            $successMsg  = parent::getSuccessMsg("SS001");
            $this->Flash->set($successMsg, array("key" => "AccountSetupSuccess"));

            return json_encode($successMsg);
        } else {
            $errorMsg = parent::getErrorMsg("SS002");
            $this->Flash->set($errorMsg, array("key" => "AccountSetupFail"));
            return json_encode($errorMsg);
        }

        #return true if save success, otherwise return false;
        #Show error or success message in view.
    }

    /**
     * save data to  tbl_account_setup
     *
     * @author ayezarnikyaw (20200804)
     * @return boolen
     */
    public function EditAccountSetup()
    {
        $login_id = $this->Session->read('LOGIN_ID');
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        // $accsetup_id = $this->request->data['id'];
        $data = $this->request->data['data_arr'];
        if (!empty($data)) {
            $date =    date("Y-m-d H:i:s");

            $first_data = json_decode($data[0], true);
            //  $this->log(print_r($first_data,true),LOG_DEBUG);
            $head_id    = $first_data['hlayer_code'];
            $year       = $first_data['year'];
            $sub_acc_id = $first_data['brm_account_id'];

            $acc_exist  = ($first_data['brm_saccount_id'] != 0) ? true : false;
            $update     = [];

            if ($acc_exist) {
                foreach ($data as $key => $each_data) {
                    $save_data = json_decode($each_data, true);
                    $update[] = array(
                        'id'             => $save_data['id'],
                        'order'          => $save_data['order'],
                        'sub_order'      => $key + 1,
                        'updated_by'     => $login_id,
                        'updated_date'   => $date
                    );
                }
            } else {
                foreach ($data as $key => $each_data) {
                    $save_data = json_decode($each_data, true);

                    $searchID = $this->BrmAccountSetup->find('list', array(
                        'fields' => array('id', 'sub_order'),
                        'conditions' => array(
                            'target_year'     => $save_data['year'],
                            'hlayer_code'     => $save_data['hlayer_code'],
                            'brm_account_id'  => $save_data['brm_account_id'],
                        )
                    ));

                    foreach ($searchID as $id => $sub_order) {
                        $update[] = array(
                            'id'              => $id,
                            'order'           => $key + 1,
                            'sub_order'       => $sub_order,
                            'updated_by'      => $login_id,
                            'updated_date'    => $date
                        );
                    }
                }
            }
            $this->BrmAccountSetup->saveMany($update);
            $successMsg  = parent::getSuccessMsg("SS001");
            $this->Session->write('successMsg', $successMsg);

            //$this->Flash->set($successMsg, array("key"=>"AccounSetupFail"));

            return json_encode($successMsg);
        } else {
            $errorMsg = parent::getErrorMsg("SS002");
            $this->Session->write('errorMsg', $errorMsg);
            return json_encode($errorMsg);
        }
    }

    /**
     * save data to  tbl_account_setup
     *
     * @author PanEiPhyo (20200722)
     * @return boolen
     */
    public function getRelatedRow()
    {
        $admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');

        $date =    date("Y-m-d H:i:s");

        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $id = $this->request->data["id"];

        $related_data = array();

        #Get current data from database
        $current_data = $this->BrmAccountSetup->find('first', array(
            'conditions' => array(
                'BrmAccountSetup.id' => $id
            )
        ));
        $current_setup = $current_data['BrmAccountSetup'];
       
        if ($current_setup['brm_saccount_id'] == null || $current_setup['brm_saccount_id'] == 0) {
            //Order sub acc
            $related_data = $this->BrmAccountSetup->find('all', array(
                'fields' => array(
                    'BrmAccountSetup.id',
                    'BrmAccountSetup.brm_account_id',
                    'BrmAccountSetup.target_year',
                    'BrmAccountSetup.hlayer_code',
                    'BrmAccountSetup.brm_saccount_id',
                    'BrmAccount.name_jp',
                    'Layer.name_jp',
                    'BrmAccountSetup.order',
                    'BrmSaccount.name_jp'
                ),
                'conditions' => array(
                    'BrmAccountSetup.target_year' => $current_setup['target_year'],
                    'BrmAccountSetup.hlayer_code' => $current_setup['hlayer_code'],
                    'BrmAccountSetup.sub_order'   => 0,
                    'BrmAccountSetup.flag'        => 1
                ),
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'Layer',
                        'type'  => 'LEFT',
                        'conditions' => array(
                            'BrmAccountSetup.hlayer_code = Layer.layer_code'
                        )
                    )
                ),
                'order' => array(
                    'order ASC',
                )
            ));
        } else {
            //order account
            $related_data = $this->BrmAccountSetup->find('all', array(
                'fields' => array(
                    'BrmAccountSetup.id',
                    'BrmAccountSetup.brm_account_id',
                    'BrmAccountSetup.target_year',
                    'BrmAccountSetup.hlayer_code',
                    'BrmAccountSetup.brm_saccount_id',
                    'BrmAccount.name_jp',
                    'Layer.name_jp',
                    'BrmAccountSetup.order',
                    'BrmSaccount.name_jp'
                ),
                'joins' => array(
                    array(
                        'table'             => 'layers',
                        'alias'             => 'Layer',
                        'type'              => 'LEFT',
                        'conditions' => array(
                            'BrmAccountSetup.hlayer_code = Layer.layer_code'
                        )
                    )
                ),
                'conditions' => array(
                    'BrmAccountSetup.target_year'       => $current_setup['target_year'],
                    'BrmAccountSetup.hlayer_code'       => $current_setup['hlayer_code'],
                    'BrmAccountSetup.brm_account_id'    => $current_setup['brm_account_id'],

                    'NOT' => array(
                        'BrmAccountSetup.sub_order' => 0,
                    ),
                    'BrmAccountSetup.flag' => 1
                ),
                'order' => array(
                    'sub_order ASC'
                )
            ));
        } 
        //$this->log(print_r($related_data,true),LOG_DEBUG);
        echo json_encode($related_data);
        /*$data = $this->request->data;

        foreach ($data as $each_data) {
            $save_data = json_decode($each_data, true);

            $param = array();
            $param["order"]			=$save_data['AccountSetupModel']['order'];

            $param["created_id"]    = $admin_level_id;
            $param["updated_id"]    = $admin_level_id;
            $param["created_date"]  = $date;
            $param["updated_date"]  = $date;
            //Save data here
            //$this->AccountSetupModel->update($param);
        }
         $data = parent::getSuccessMsg('SS001');
         $this->Flash->set($successMsg, array("key"=>"AccountSetupSuccess"));*/
        //return true;
        #return true if save success, otherwise return false;
        #Show error or success message in view.
    }

    #get account id multi select
    public function getAccountName()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $get_account = $this->request->data['sub_account'];
        $year = $this->request->data['year'];
        // $head = $this->request->data['head'];
        $head = explode(",", $this->request->data['head']);
      
        //Get existing accounts from tbl_account_setup
        $get_existing_account = $this->BrmAccountSetup->find('list', array(
            'fields' => 'brm_saccount_id',
            'conditions' => array(
                'target_year'       => $year,
                'hlayer_code'       => $head[2],
                'brm_account_id'    => $get_account,
                'flag'              => 1
            )
        ));

        //Get Accounts by sub_acc_id except existing account
        $get_acc_data = $this->BrmSaccount->find('all', array(
            'fields' => array('id', 'name_jp'),
            'conditions' => array(
                'BrmSaccount.brm_account_id' => $get_account,
                'BrmSaccount.flag' => 1,
                'NOT' => array(
                    'BrmSaccount.id' => $get_existing_account
                )
            ),
            'order'  =>  'BrmSaccount.id ASC'
        ));
       
        //Return data
        echo json_encode($get_acc_data);
    }


    #Delete data from tbl_account-setup flag!=0
    public function DeleteAccountSetup()
    {
        $delete_id = $this->request->data['delete_id'];
        $page_no   = $this->request->data('hid_page_no');
        $id_flag   = $this->BrmAccountSetup->find(
            'first',
            array(
                'conditions' => array('BrmAccountSetup.id' => $delete_id),
                'fields' => array('BrmAccountSetup.flag')
            )
        );

        if ($id_flag['BrmAccountSetup']['flag'] == '1') {
            $pageNo =  $this->request->data['hid_page_no'];


            $result = array(
                'id' => $delete_id,
                'flag' => '0',
                'updated_by' => $this->Session->read('LOGIN_ID'),
                'updated_date'  => date("Y-m-d H:i:s")
            );
            $this->BrmAccountSetup->save($result);
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array("key" => "AccountSetupSuccess"));
            //$this->redirect(array('controller'=>'AccountSetup','action'=>'index/'.$pageNo));
            $this->redirect(array('controller' => 'BrmAccountSetup', 'action' => 'index'));
        } else {
            $pageNo =  $this->request->data['hid_page_no'];
            $errorMsg = parent::getErrorMsg('SE050');
            $this->Flash->set($errorMsg, array("key" => "AccounSetupFail"));
            //$this->redirect(array('controller'=>'AccountSetup','action'=>'index/'.$pageNo));
            $this->redirect(array('controller' => 'BrmAccountSetup', 'action' => 'index'));
        }
    }
    /**
     * coping account
     *
     * @author EiThandarKyaw (20201014)
     * @return json
     */
    public function AccountCopy()
    {
        if ($this->request->is('post')) {
            $requestData = $this->request->data;
            $fromYear = $this->request->data['hidFromYear'];
            $fromhq = $this->request->data['hidFromHq'];
            $toYear = $this->request->data['copy_to_year'];
            $toHq = $this->request->data['copy_to_hq'];

            $existedAccount = $this->BrmAccountSetup->find(
                'all',
                array(
                    'fields' => array('BrmAccountSetup.*'),
                    'conditions' => array('BrmAccountSetup.flag' => '1', 'BrmAccountSetup.target_year' => $toYear, 'BrmAccountSetup.hlayer_code' => $toHq)
                )
            );
            //$this->log(print_r($existedAccount, true), LOG_DEBUG);
            $copyAccounts = $this->BrmAccountSetup->find(
                'all',
                array(
                    'fields' => array('BrmAccountSetup.*'),
                    'conditions' => array('BrmAccountSetup.flag' => '1', 'BrmAccountSetup.target_year' => $fromYear, 'BrmAccountSetup.hlayer_code' => $fromhq)
                )
            );

            // get difference account to copy
            if (sizeof($existedAccount) > 0) {
                $result = $this->multi_diff($copyAccounts, $existedAccount);
            } else {
                $result = $copyAccounts;
            }

            $save_datas = array();

            foreach ($result as $data) {
                $save_datas[] = array(
                    'target_year' => $toYear,
                    'hlayer_code' => $toHq,
                    'brm_account_id' => $data['BrmAccountSetup']['brm_account_id'],
                    'brm_saccount_id' => $data['BrmAccountSetup']['brm_saccount_id'],
                    'order' => $data['BrmAccountSetup']['order'],
                    'sub_order' => $data['BrmAccountSetup']['sub_order'],
                    'flag' => $data['BrmAccountSetup']['flag'],
                    'created_by' => $this->Session->read('LOGIN_ID'),
                    'updated_by' => $this->Session->read('LOGIN_ID'),
                    'created_date' => date("Y-m-d H:i:s"),
                    'updated_date' => date("Y-m-d H:i:s"),
                );
            }
            try {

                if (sizeof($save_datas) > 0) {
                    $this->BrmAccountSetup->begin();
                    $this->BrmAccountSetup->saveAll($save_datas);
                    $this->BrmAccountSetup->commit();
                }
                $successMsg  = parent::getSuccessMsg("SS025");
                $this->Flash->set($successMsg, array("key" => "AccountSetupSuccess"));
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array("key" => "AccounSetupFail"));
            }
            $this->redirect(array('controller' => 'BrmAccountSetup', 'action' => 'index'));
        }
    }
    // compare two arrays and return difference array
    public function multi_diff($arr1, $arr2)
    {
        $result = array();
        $exitedAccountId = array();
        // find same account id
        foreach ($arr1 as $k => $v) {
            foreach ($arr2 as $k2 => $v2) {
                if (
                    $v['BrmAccountSetup']['account_id'] == $v2['BrmAccountSetup']['account_id'] &&
                    $v['BrmAccountSetup']['sub_acc_id'] == $v2['BrmAccountSetup']['sub_acc_id']
                ) {
                    $exitedAccountId[] = $v['BrmAccountSetup']['sub_acc_id'];
                } else {
                    $result[] = $v;
                }
            }
            $result = array_unique($result);
        }

        return $result;
    }
    /**
     * check account to overwrite or copy
     *
     * @author EiThandarKyaw (20201028)
     * @return json
     */
    public function checkData()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $result = array();
        if ($this->request->is('post')) {
            $requestData = $this->request->data;
            $copyToHq = $requestData['copyToHq'];
            $copyToYear = $requestData['copyToYear'];
            $accounts = $this->BrmAccountSetup->find(
                'list',
                array(
                    'fields' => array('BrmAccountSetup.id'),
                    'conditions' => array('flag' => '1', 'target_year' => $copyToYear, 'hlayer_code' => $copyToHq),
                    'limit' => 1,
                )
            );
            if (sizeOf($accounts) > 0) {
                $result['DataExist'] = true;
            } else {
                $result['DataExist'] = false;
            }

            return json_encode($result);
        }
    }
    /**
     * account overwrite
     *
     * @author EiThandarKyaw (20201028)
     * @return json
     */
    public function AccountOverwrite()
    {
        if ($this->request->is('post')) {
            $requestData = $this->request->data;
            //echo '<pre>';print_r($requestData);echo '</pre>';exit;
            $fromYear = $this->request->data['hidFromYear'];
            $fromhq = $this->request->data['hidFromHq'];
            $toYear = $this->request->data['copy_to_year'];
            $toHq = $this->request->data['copy_to_hq'];
            // get all data to copy
            $copyAccounts = $this->BrmAccountSetup->find(
                'all',
                array(
                    'fields' => array('BrmAccountSetup.*'),
                    'conditions' => array('BrmAccountSetup.flag' => '1', 'BrmAccountSetup.target_year' => $fromYear, 'BrmAccountSetup.hlayer_code' => $fromhq)
                )
            );
            // prepare to copy
            foreach ($copyAccounts as $data) {
                $save_datas[] = array(
                    'target_year' => $toYear,
                    'hlayer_code' => $toHq,
                    'brm_account_id' => $data['BrmAccountSetup']['brm_account_id'],
                    'brm_saccount_id' => $data['BrmAccountSetup']['brm_saccount_id'],
                    'order' => $data['BrmAccountSetup']['order'],
                    'sub_order' => $data['BrmAccountSetup']['sub_order'],
                    'flag' => $data['BrmAccountSetup']['flag'],
                    'created_by' => $this->Session->read('LOGIN_ID'),
                    'updated_by' => $this->Session->read('LOGIN_ID'),
                    'created_date' => date("Y-m-d H:i:s"),
                    'updated_date' => date("Y-m-d H:i:s"),
                );
            }
            try {
                $this->BrmAccountSetup->begin();
                // delete all previous data
                $this->BrmAccountSetup->deleteAll(array('target_year' => $toYear, 'hlayer_code' => $toHq));
                // save new data
                $this->BrmAccountSetup->saveAll($save_datas);
                $this->BrmAccountSetup->commit();
                $successMsg  = parent::getSuccessMsg("SS025");
                $this->Flash->set($successMsg, array("key" => "AccountSetupSuccess"));
            } catch (Exception $e) {
                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array("key" => "AccounSetupFail"));

                CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            }
            $this->redirect(array('controller' => 'BrmAccountSetup', 'action' => 'index'));
        }
    }
}
