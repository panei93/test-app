<?php
App::uses('AppController', 'Controller');
/**
 * Accounts Controller
 *
 * @property Account $Account
 * @property PaginatorComponent $Paginator
 */
class BrmSaccountsController extends AppController
{

/**
 * Components
 *
 * @var array
 */
    public $uses = array('BrmSaccount','BrmAccountGroup','BrmAccount');
    public $components = array('Session','Flash','Paginator');
    public $helpers = array('Html', 'Form' ,'Paginator');

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
     * @author Lone Lay(2020/02/20)
     * @return void
     */

    public function index()
    {
        $this->layout = 'mastermanagement';

        $conditions = array();
        $conditions["BrmSaccount.flag !="] = 0;
        $conditions["BrmAccount.flag !="] = 0;

        # Get pair accounts PanEiPhyo(20201015)
        $sub_acc_data = $this->BrmAccount->find('list', array(
            'fields' => array('id','name_jp'),
            'conditions' => array(
                'BrmAccount.flag' => 1),
        ));
        $account_pairs = $this->BrmSaccount->find('list', array(
            'fields' => array('id','pair_ids'),
            'conditions' => array(
                'BrmSaccount.flag' => 1),
        ));
        $pair_accounts = array();
        foreach ($account_pairs as $id => $pair_ids) {
            $id_pair = json_decode($pair_ids, 1);
            foreach ($id_pair as $g_code => $pair_id) {
                $name_arr[$g_code] = $sub_acc_data[$pair_id];
            }
            $pair_accounts[$id] = $name_arr;
        }
        #END
        
        $account_groups = $this->BrmAccountGroup->find('list', array(
            'fields' => array('group_code','group_name'),
            'conditions' => array('BrmAccountGroup.flag' => '1')
        ));

        $group_1_data = $this->BrmAccount->find('list', array(
            'fields' => array('id','name_jp'),
            'conditions' => array(
                'BrmAccount.group_code' => '01',
                'BrmAccount.flag' => 1),
        ));

        try {
            $this->paginate = array(
                                    'limit' => Paging::TABLE_PAGING,
                                    'conditions' => $conditions,
                                    'order'  =>  'BrmSaccount.account_code ASC'
                                    );
                                            
            $all_accmaster = h($this->Paginator->paginate('BrmSaccount'));
            //pr($all_accmaster);die;
            //count
            $query_count = $this->params['paging']['BrmSaccount']['count'];
            $count = parent::getSuccessMsg('SS004', $query_count);

            //no count value
            if ($query_count == 0) {
                $no_data = parent::getErrorMsg("SE001");
                $this->set(compact('no_data'));
            }
            
            $this->set(compact('all_accmaster', 'count', 'account_groups', 'pair_accounts', 'group_1_data'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect('index');
        }
    }
    /**
     *
     * getSubAccountName method
      *@author ayezarnikyaw(20200408)
     *
    */
    public function getSubAccountGroupNames()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $data_arr = array();
        $result_arr = array();

        $sub_acc_id = $this->request->data['id'];

        $sub_acc_data = $this->BrmAccount->find('list', array(
            'fields' => array('id','name_jp'),
            'conditions' => array(
                'BrmAccount.flag' => 1),
        ));
       
        $accgroup_data = $this->BrmSaccount->find('all', array(
            'fields' => array('BrmSaccount.pair_ids','BrmSaccount.name_jp'),
            'conditions' => array(
                'BrmSaccount.brm_account_id' => $sub_acc_id,
                'BrmSaccount.flag' => 1
            ),
        )); //$this->log(print_r($accgroup_data,true),LOG_DEBUG);
        foreach ($accgroup_data as $group_data) {
            $names = array();
            $id_pair = $group_data['BrmSaccount']['pair_ids'];
            // $tmp['id_pair'] = $id_pair;
            $id_arr = json_decode($id_pair, 1);
            foreach ($id_arr as $id) {
                $names[] = $sub_acc_data[$id];
            }
            $data_arr[$id_pair] = implode('／', array_filter(array_unique($names)));
        }
        foreach ($data_arr as $id_pair => $name_pair) {
            $tmp['id_pair'] = $id_pair;
            $tmp['name_pair'] = $name_pair;
            $result_arr[] = $tmp;
        }

        echo json_encode($result_arr);
    }

    public function delete()
    {
        $id = $this->request->data['id'];
        $page_no = $this->request->data('hid_page_no');
        $id_flag = $this->BrmSaccount->find(
            'first',
            array(
        'conditions' => array('BrmSaccount.id' => $id),
        'fields' => array('BrmSaccount.flag'))
        );
          
        if ($id_flag['BrmSaccount']['flag'] == '1') {
            $result = array(
            'id' => $id,
            'flag' => '0',
            'updated_BY' => $this->Session->read('LOGIN_ID'),
            'updated_date'  => date("Y-m-d H:i:s"));
            $this->BrmSaccount->save($result);
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array("key"=>"SubAccountSuccess"));
            $this->redirect(array('controller'=>'BrmSaccounts','action'=>'index/'.$page_no));
        } else {
            //Exclusive　error
            $errorMsg = parent::getErrorMsg('SE050');
            $this->Flash->set($errorMsg, array("key"=>"SubAccountFail"));
            $this->redirect(array('controller'=>'BrmSaccounts','action'=>'index/'.$page_no));
        }
    }

    // public function PopupSave() {
    // 	if($this->request->is('post')) {
    // 		$subacc_code = $this->request->data('popup_code');
    // 		$subacc_name_en = $this->request->data('popup_name_eng');
    // 		$subacc_name_jp = $this->request->data('popup_name_jp');
    // 		$param = array();
    // 		$param["sub_acc_code"] = $subacc_code;
    // 		$param["sub_acc_name_en"] = $subacc_name_en;
    // 		$param["sub_acc_name_jp"] = $subacc_name_jp;
    // 		$param["updated_id"] = 1;
    // 		$param["created_id"] = 1;
    // 		$param["flag"]=1;

    // 		$this->SubBrmSaccount->save($param);
    // 		$id = $this->SubBrmSaccount->find('all',array('fields'=>('id'),
    // 				'conditions'=>array('NOT'=>array('flag'=>'0'))));
            
    // 		$successMsg = parent::getSuccessMsg('SS001');
    // 		$this->Flash->set($successMsg, array("key"=>"SubAccountSuccess"));
    // 	}

    // 	$this->redirect (array (
    // 			  'controller' => 'Account',
    // 			  'action' => 'index'
    // 			) );
    // }

    public function saveUserData()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('post')) {
            $actual_link = $_SERVER["HTTP_REFERER"];//pr($actual_link);die;
            $page_no = $this->request->data('hid_page_no');
            $acc_code = $this->request->data('acc_code');
            $acc_name_en =trim($this->request->data('acc_name_en'));
            $acc_name_jp = $this->request->data('acc_name_jp');
            $sub_id = $this->request->data('sub_acc_id');
            $sub_acc_groups = $this->request->data('sub_acc_groups');
           
            $param = array();
            $param["brm_account_id"] = $sub_id;
            $param["account_code"] = $acc_code;
            $param["name_jp"] = $acc_name_jp;
            $param["pair_ids"] = $sub_acc_groups;

            if (!empty($acc_name_en)) {
                $param["name_en"] = $acc_name_en;
            }

            $param["flag"]	= 1;
            $param["created_by"] = $this->Session->read('LOGIN_ID');
            $param["updated_by"] = $this->Session->read('LOGIN_ID');
            $param["created_date"]   = date("Y-m-d H:i:s");
            $param["updated_date"]  = date("Y-m-d H:i:s");

            //index funciton of query
            $conditions = array();
            $conditions["BrmSaccount.flag !="] = 0;
            $conditions["BrmAccount.flag !="] = 0;

            $sub_acc_data = $this->BrmAccount->find('all', array('conditions' => array('flag' => '1')));
                
            try {
                $this->paginate = array(
                                            'limit' => Paging::TABLE_PAGING,
                                            'conditions' => $conditions,
                                            'order'  =>  'BrmSaccount.id DESC'
                                            );
                                                    
                $all_accmaster = h($this->Paginator->paginate('BrmSaccount'));
                //count
                $query_count = $this->params['paging']['BrmSaccount']['count'];
                $count = parent::getSuccessMsg('SS004', $query_count);

                $this->set(compact('all_accmaster', 'count', 'sub_acc_data'));
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->redirect($actual_link);
            }
            //end index funciton of query

            if (!empty($this->request->data)) {
                //duplicate account code
                $results = $this->BrmSaccount->find('all', array(
                            'fields' => 'account_code,flag'));
                          
                foreach ($results as  $value) {
                    if ($value['BrmSaccount']['account_code'] == $acc_code
                         && $value['BrmSaccount']['flag'] == 1) {

                        //to send all data for view LoneLay(20210519)
                        $account_groups = $this->BrmAccountGroup->find('list', array(
                            'fields' => array('group_code','group_name'),
                            'conditions' => array('flag' => '1')
                        ));
                       
                        $conditions = array();
                        $conditions["BrmSaccount.flag !="] = 0;
                        $conditions["BrmAccount.flag !="] = 0;

                        $this->paginate = array(
                                                    'limit' => Paging::TABLE_PAGING,
                                                    'conditions' => $conditions,
                                                    'order'  =>  'BrmSaccount.account_code ASC'
                                                    );
                                                            
                        $all_accmaster = h($this->Paginator->paginate('BrmSaccount'));

                        $sub_acc_data = $this->BrmAccount->find('list', array(
                            'fields' => array('id','name_jp'),
                            'conditions' => array(
                                'BrmAccount.flag' => 1),
                        ));
                        $account_pairs = $this->BrmSaccount->find('list', array(
                            'fields' => array('id','pair_ids'),
                            'conditions' => array(
                                'BrmSaccount.flag' => 1),
                        ));
                        $pair_accounts = array();
                        foreach ($account_pairs as $id => $pair_ids) {
                            $id_pair = json_decode($pair_ids, 1);
                            foreach ($id_pair as $g_code => $pair_id) {
                                $name_arr[$g_code] = $sub_acc_data[$pair_id];
                            }
                            $pair_accounts[$id] = $name_arr;
                        }

                        $group_1_data = $this->BrmAccount->find('list', array(
                            'fields' => array('id','name_jp'),
                            'conditions' => array(
                                'BrmAccount.group_code' => '01',
                                'BrmAccount.flag' => 1),
                        ));
                      
                        $request = $this->request->data;
                        $errorMsg = parent::getErrorMsg('SE002', "勘定コード");
                        $this->Flash->set($errorMsg, array("key"=>"SubAccountFail"));
                        //$this->redirect(array('controller'=>'Account','action'=>'index/'.$page_no.'?request='.$request));

                         
                        $this->set('request', $request);
                        $this->set(compact('all_accmaster', 'count', 'account_groups', 'pair_accounts', 'group_1_data'));
                       // pr($actual_link);die;
                        //return $this->render($actual_link);
                       $this->redirect($actual_link);
                    }
                }
              
                //Account code not allow '0000000000' LoneLay(20210519)
                $numAcc_code = intval($acc_code);
               
                if ($numAcc_code == 0) {
                    $errorMsg = parent::getErrorMsg('SE004');
                    $this->Flash->set($errorMsg, array('key'=>'SubAccountFail'));
                    $this->redirect($actual_link);
                }

                //save data
                if ($this->BrmSaccount->save($param)) {
                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array("key"=>"SubAccountSuccess"));
                    $this->redirect($actual_link);
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key"=>"SubAccountFail"));
                $this->redirect($actual_link);
            }
        }
    }

    public function editAccount()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $acc_id = $this->request->data['id'];
        $all_name_pairs = array();
            
        
        $accData= $this->BrmSaccount->find('first', array('conditions'=>array('BrmSaccount.id'=> $acc_id)));
        //$this->log(print_r($accData,true),LOG_DEBUG);
        //find group code of sub_acc_name (aznk)
        $group_code = $accData['BrmAccount']['group_code'];
        $sub_acc_name = $this->BrmAccount->find('list', array(
            'fields' => array('id','name_jp'),
            'conditions' => array(
                'BrmAccount.group_code' => $group_code,
                'BrmAccount.flag' => 1
            )
        ));
        $sub_acc_data = $this->BrmAccount->find('list', array(
            'fields' => array('id','name_jp'),
            'conditions' => array(
                'BrmAccount.flag' => 1),
        ));

        $accgroup_data = $this->BrmSaccount->find('all', array(
            'fields' => array('BrmSaccount.pair_ids','BrmAccount.name_jp'),
            'conditions' => array(
                'BrmSaccount.brm_account_id' => $accData['BrmSaccount']['brm_account_id'],
                'BrmSaccount.flag' => 1
            ),
        ));
        foreach ($accgroup_data as $group_data) {
            $names = array();
            $id_pair = $group_data['BrmSaccount']['pair_ids'];
            // $tmp['id_pair'] = $id_pair;
            $id_arr = json_decode($id_pair, 1);
            foreach ($id_arr as $id) {
                $names[] = $sub_acc_data[$id];
            }
            $data_arr[$id_pair] = implode('／', array_filter(array_unique($names)));
        }
        foreach ($data_arr as $id_pair => $name_pair) {
            $tmp['id_pair'] = $id_pair;
            $tmp['name_pair'] = $name_pair;
            $all_name_pairs[] = $tmp;
        }

        $id_pairs = $accData['BrmSaccount']['pair_ids'];

        $response = array(
                'id'      => $accData['BrmSaccount']['id'],
                'acc_code'=> $accData['BrmSaccount']['account_code'],
                'acc_name_jp'=>$accData['BrmSaccount']['name_jp'],
                'acc_name_en'=>$accData['BrmSaccount']['name_en'],
                'sub_acc_id'=>$accData['BrmSaccount']['brm_account_id'],
                'id_pairs'=>$id_pairs,
                'sub_acc_groups'=>json_encode($all_name_pairs),
                'flag'=>$accData['BrmSaccount']['flag'],
                'sub_acc_name' => $accData['BrmAccount']['name_jp']
        );
       
        echo json_encode($response);
    }


    public function updateAccountData()
    {
        if ($this->request->is('post')) {
            $page_no = $this->request->data('hid_page_no');
            $id= $this->request->data('acc_id');
            $acc_code = $this->request->data('acc_code');
            $acc_name_en =trim($this->request->data('acc_name_en'));
            $acc_name_jp = $this->request->data('acc_name_jp');
            $sub_acc_id = $this->request->data('sub_acc_id');
            $sub_acc_groups = $this->request->data('sub_acc_groups');


            $id_flag = $this->BrmSaccount->find('first', array(
              'conditions' => array('BrmSaccount.id' => $id),
              'fields' => 'BrmSaccount.flag'
              ));
            
            if (!empty($this->request->data)) {
              
                if ($id_flag['BrmSaccount']['flag'] == 1) {
                    $this->BrmSaccount->updateAll(
                        array("flag"=>'1',
                                "brm_account_id" => "'".$sub_acc_id."'",
                                "account_code" => "'".$acc_code."'",
                                "name_jp" => "'".$acc_name_jp."'",
                                "name_en" => "'".$acc_name_en."'",
                                // "sub_acc_id" => "'".$sub_acc_id."'", block by yuya
                                "pair_ids" => "'".$sub_acc_groups."'",
                                "updated_date"=>"'".date("Y-m-d H:i:s")."'",
                                "updated_by"=>"'".$this->Session->read('LOGIN_ID')."'"),
                        array("BrmSaccount.id"=>$id)
                    );
                    $successMsg = parent::getSuccessMsg('SS002');
                    $this->Flash->set($successMsg, array("key"=>"SubAccountSuccess"));
                    $this->redirect(array('controller'=>'BrmSaccounts','action'=>'index/'.$page_no));
                } else {
                    //Exclusive　error
                    $errorMsg = parent::getErrorMsg('SE050');
                    $this->Flash->set($errorMsg, array("key"=>"SubAccountFail"));
                    $this->redirect(array('controller' => 'BrmSaccounts', 'action' => 'index/'.$page_no));
                }
            }
        }
    }
}
