<?php

App::uses('AppController', 'Controller');
/**
 * SubAccountController
 * @author Hein Htet Ko
 */

class BrmAccountsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('BrmSaccount','BrmAccountGroup','BrmAccount');
    public $components = array('Session','Flash','Paginator');
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        
        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Login', 'action' => 'logout'));
        // }
    }
    public function index()
    {
        $this->layout = 'mastermanagement';
        $data_arr = array();

        try {
            $account_groups = $this->BrmAccountGroup->find('list', array(
                'fields' => array('group_code','group_name'),
                'conditions' => array(
                    'BrmAccountGroup.flag' => 1
                )
            ));
          
            foreach ($account_groups as $code => $name) {
                $data = $this->BrmAccount->find('all', array(
                    'conditions' => array(
                        'BrmAccount.flag' => 1,
                        'BrmAccount.group_code' => $code
                    ),
                    'order' => array('BrmAccount.id ASC')
                ));
                $data_arr[$code] = $data;
            }
           
            $this->set(compact('data_arr', 'account_groups'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmAccounts', 'action' => 'index'));
        }
    }

    public function saveData()
    {
        $this->layout = 'mastermanagement';
     //   $page_name = $this->request->data['page_name'];
        // pr($page_name);die;
        $page_name = $this->request->params['controller'];
       // pr($page_name);die;
        if ($this->request->is('post')) {
            if (!empty($this->request->data)) {
                $group_code =$this->request->data['g_code'];

                $acc_name_jp = !empty($this->request->data['name_jp']) ? $this->request->data['name_jp'] : $this->request->data['sub_acc_name_jp'];
                $name_jp = rtrim($acc_name_jp, " ");
                $name_en = !empty($this->request->data['name_jp']) ? $this->request->data['name_en'] : $this->request->data['sub_acc_name_en'];
                $page_no = $this->request->data['hid_page_no'];
                
                $language = ($this->Session->read('Config.language') == 'eng') ? 'en' : 'jp';
                $param = array();
                
                $match_count = $this->BrmAccount->find('count', array(
                    'conditions' => array(
                        'group_code' => $group_code,
                        'name_jp' => $name_jp,
                        'BrmAccount.flag' => 1
                    )
                ));

                if ($match_count <= 0) {
                    $param["group_code"] = $group_code;
                    $param["name_jp"] = $name_jp;
                    $param["name_en"] = $name_en;
                    $param["type"] = "";
                    $param["flag"]	= 1;
                    $param["created_by"] = $this->Session->read('LOGIN_ID');
                    $param["updated_by"] = $this->Session->read('LOGIN_ID');
                    $param["created_date"]   = date("Y-m-d H:i:s");
                    $param["updated_date"]  = date("Y-m-d H:i:s");
                 
                    //save data
                    $this->BrmAccount->save($param);
                    $successMsg = parent::getSuccessMsg('SS001');
                    
                    if ($page_name == "BrmAccounts") {
                        $this->Flash->set($successMsg, array("key"=>"AccountSuccess"));
                        $this->redirect(array('controller'=>'BrmAccounts','action'=>'index/'.$page_no));
                    } else {
                        $this->Flash->set($successMsg, array("key"=>"SubAccountSuccess"));
                        $this->redirect(array('controller'=>'BrmSaccounts','action'=>'index/'.$page_no));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE002', "グループコード と　小勘定科目名");
                    if ($page_name == "BrmAccounts") {
                        $this->Flash->set($errorMsg, array("key"=>"AccountError"));
                        $this->redirect(array('controller'=>'BrmAccounts','action'=>'index/'.$page_no));
                    } else {
                        $this->Flash->set($errorMsg, array("key"=>"SubAccountFail"));
                        $request = $this->request->data;
                        $this->set('request', $request);
                    }
                    $this->redirect(array('controller'=>'BrmSaccounts','action'=>'index'));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                if ($page_name == "BrmAccounts") {
                    $this->Flash->set($errorMsg, array("key"=>"AccountError"));
                    $this->redirect(array('controller' => 'BrmAccounts', 'action' => 'index/'.$page_no));
                } else {
                    $this->Flash->set($errorMsg, array("key"=>"AccountFail"));
                    $this->redirect(array('controller' => 'BrmSaccounts', 'action' => 'index/'.$page_no));
                }
            }
        } else {
            $this->redirect(array('controller'=>'SubAccount','action'=>'index'));
        }
    }
    /**
     * 	@author aznk
     *  updateData
     */
    public function updateData()
    {
        if ($this->request->is('post')) {
            $page_no = $this->request->data('hid_page_no');
            $id = $this->request->data('id_vl');
            $g_code = $this->request->data('hid_g_code');
            $acc_name_jp = $this->request->data('name_jp');
            $name_jp = rtrim($acc_name_jp, " ");
            $name_en = $this->request->data('name_en');
            $acc_name_en =trim($this->request->data('acc_name_en'));

            $id_flag = $this->BrmAccount->find('first', array(
              'conditions' => array('BrmAccount.id' => $id),
              'fields' => 'BrmAccount.flag'
              ));

            $match_count = $this->BrmAccount->find('count', array(
                'conditions' => array(
                    'BrmAccount.id !=' => $id,
                    'group_code' => $g_code,
                    'name_jp' => $name_jp,
                    'BrmAccount.flag' => 1
                )
            ));

            if ($match_count <=0) {
                $param = array();
                if (!empty($this->request->data)) {
                    $param["id"] = $id;
                    $param["group_code"] = $g_code;
                    $param["name_jp"] = $name_jp;
                    $param["name_en"] = $name_en;
                    $param["flag"]	= 1;
                    $param["type"] = "";
                    $param["updated_by"] = $this->Session->read('LOGIN_ID');
                    $param["updated_date"]  = date("Y-m-d H:i:s");
//pr($param);die;
                    if ($id_flag['BrmAccount']['flag'] == 1) {
                        $this->BrmAccount->save($param);
                        $successMsg = parent::getSuccessMsg('SS002');
                        $this->Flash->set($successMsg, array("key"=>"AccountSuccess"));
                        $this->redirect(array('controller'=>'BrmAccounts','action'=>'index/'.$page_no));
                    } else {
                        //Exclusive　error
                        $errorMsg = parent::getErrorMsg('SE050');
                        $this->Flash->set($errorMsg, array("key"=>"AccountFail"));
                        $this->redirect(array('controller' => 'BrmAccounts', 'action' => 'index/'.$page_no));
                    }
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE002', "グループコード と　小勘定科目名");
                $this->Flash->set($errorMsg, array("key"=>"AccountFail"));
                $request = $this->request->data;
                $this->set('request', $request);
                $this->redirect(array('controller'=>'BrmAccounts','action'=>'index'));
            }
        } else {
            $this->redirect(array('controller'=>'BrmAccounts','action'=>'index'));
        }
    }
    public function editData()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $subId = $this->request->data['id'];
        $subAccResult = $this->BrmAccount->find('all', array('conditions'=>array('id'=>$subId)));
        $response = array(
                        'id_vl'=> $subAccResult[0]['BrmAccount']['id'],
                        'group_code'=> $subAccResult[0]['BrmAccount']['group_code'],
                        'name_jp'=>$subAccResult[0]['BrmAccount']['name_jp'],
                        'name_en'=>$subAccResult[0]['BrmAccount']['name_en'],
                        'flag'=>$subAccResult[0]['BrmAccount']['flag']
        );

        return json_encode($response);
    }

    public function deleteData()
    {
        $id = $this->request->data['deleteItem'];

        $id_flag = $this->BrmAccount->find(
            'first',
            array(
        'conditions' => array('BrmAccount.id' => $id),
        'fields' => array('BrmAccount.flag'))
        );

          
        if ($id_flag['BrmAccount']['flag'] == '1') {
            $result = array(
            'id' => $id,
            'flag' => '0',
            'updated_by' => $this->Session->read('LOGIN_ID'),
            'updated_date'  => date("Y-m-d H:i:s"));

            $this->BrmAccount->save($result);
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array("key"=>"AccountSuccess"));
            $this->redirect(array('controller'=>'BrmAccounts','action'=>'index'));
        } else {
            //Exclusive　error
            $errorMsg = parent::getErrorMsg('SE050');
            $this->Flash->set($errorMsg, array("key"=>"AccountFail"));
            $this->redirect(array('controller'=>'BrmAccounts','action'=>'index'));
        }
    }
    
    public function getPairedAccounts($group_code)
    {
        // $group_code = '03';
        $account_pair = [];

        $accounts = $this->BrmAccount->find('list', array(
            'fields' => array('name_jp','calculation_method','id'),
            'conditions' => array(
                'group_code' => $group_code
            ),
        ));
        foreach ($accounts as $acc_id => $acc_data) {
            $acc_name = array_keys($acc_data)[0];
            $cal_fields = array_values($acc_data)[0];
            //SST 11.2.2022
            $paired_acc_id = $this->BrmSaccount->find('list', array(
                'fields' => array(
                    'BrmSaccount.brm_account_id',
                ),
                'conditions' => array(
                    'pair_ids LIKE' => '%"'.$group_code.'":'.$acc_id.'%',
                ),
                'joins' => array(
                    array('table' => 'brm_accounts',
                          'alias' => 'BrmAccount',
                          'type' => 'LEFT',
                          'conditions' => array('BrmSaccount.brm_account_id = BrmAccount.id AND BrmAccount.flag=1'))
                ),
                'group' => array('BrmAccount.id')
            ));
            $account_pair[$acc_name] = $paired_acc_id;
            if (!empty($cal_fields)) {
                $sum_accs = json_decode($cal_fields, true)['field'];
                foreach ($sum_accs as $each_acc) {
                    $account_pair[$acc_name] = array_merge($account_pair[$acc_name], $account_pair[$each_acc]);
                }
            }
        }
        return $account_pair;
    }
}
