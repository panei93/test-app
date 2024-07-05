<?php

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * ChemicalAccountController
 * @author 
 */

class AccountsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('Account', 'AccountType');
    public $components = array('Session', 'Flash', 'Paginator');
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkSettingSession($this->name);

        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
        // }
    }
    public function index()
    {
        $this->layout = 'mastermanagement';

        try {
            $account_types = $this->AccountType->find('all', array(
                'fields' => array('AccountType.id', 'AccountType.type_name'),
                'conditions' => array(
                    'AccountType.flag' => 1
                )
            ));
            $current_data = $this->Account->find('all', array(
                'fields' => array('Account.id', 'Account.account_name'),
                'conditions' => array(
                    'Account.flag' => 1
                )
            ));

            $data = $this->getPaginateAccountData(Paging::TABLE_PAGING);

            $query_count = $this->params['paging']['Account']['count'];
            $count = parent::getSuccessMsg('SS004', $query_count);

            if ($query_count == 0) {
                $no_data = parent::getErrorMsg("SE001");
                $this->set(compact('no_data'));
            }

            $limit = $this->params['paging']['Account']['limit'];
            $pageno = $this->params['paging']['Account']['page'];
            $this->Session->write('Page.pageCount', $pageno);
            $this->set(compact('account_types', 'current_data', 'data', 'count', 'limit', 'query_count' ));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'Accounts', 'action' => 'index'));
        }
    }

    public function getPaginateAccountData($limit)
    {
        $this->paginate  = array(
            'maxLimit' => $limit,
            'limit' => $limit,
            'conditions' => array('Account.flag' => 1),
            'joins' => array(
                array(
                    'table' => 'account_types',
                    // 'alias' => 'account_types',
                    'type'  =>  'left',
                    'conditions' => array(
                        'account_types.id = Account.account_type_id'
                    )
                ),
            ),
            'fields' => array(
                'Account.*',
                'account_types.type_name',
            ),
            'order' => 'Account.display_number,Account.account_type_id ASC'

        );
       
        $data = $this->Paginator->paginate('Account');
       
        return $data;
    }

    public function saveData()
    {
        $this->layout = 'mastermanagement';
       
        if ($this->request->is('post')) {

            if (!empty($this->request->data)) {

                $acc_type       = $this->request->data['acc_type'];
                $acc_name       = $this->request->data['acc_name'];
                $acc_code       = $this->request->data['acc_code'];
                $acc_category   = $this->request->data['acc_category'];
                $postfix        = $this->request->data['postfix'];
                $memo           = $this->request->data['memo'];
                $formula        = $this->request->data['formula'];
                $b_param        = $this->request->data['hd_base_param'];


                $max_display_number = $this->Account->find(
                    'first',
                    array(
                        //'conditions' => array('Account.flag' => 1),
                        'fields' => array('MAX(Account.id) as display_number')
                    )
                );
           
                $display_number = !empty($max_display_number[0]['display_number']) ? ++$max_display_number[0]['display_number'] : 1;

                $param = array();

                $match_count = $this->Account->find('count', array(
                    'conditions' => array(
                        //'account_type_id' => $acc_type,
                        'Account.account_code' => $acc_code,
                        'Account.flag' => 1
                    )
                ));

                if ($match_count <= 0) {
                    $param["account_type_id"] = $acc_type;
                    $param["account_code"] = $acc_code;
                    $param["account_name"] = $acc_name;
                    $param["account_type"] = $acc_category;
                    $param["display_number"] = $display_number;
                    $param["postfix"] = $postfix;
                    $param["base_param"] = !empty($b_param)?$b_param:'';
                    $param["calculation_formula"] = !empty($formula)?$formula:'';
                    $param["memo"] = $memo;
                    $param["flag"]    = 1;
                    $param["created_by"] = $this->Session->read('LOGIN_ID');
                    $param["updated_by"] = $this->Session->read('LOGIN_ID');
                    $param["created_date"]   = date("Y-m-d H:i:s");
                    $param["updated_date"]  = date("Y-m-d H:i:s");
                   /// pr($param);die;
            
                    try {
                        $this->Account->save($param);
                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                    } catch (Exception $e) {
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE002', __("Account Code"));
                    $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                }
                $this->redirect(array('controller' => 'Accounts', 'action' => 'index'));
            }
        }
    }

    public function editData()
    {
        parent::checkAjaxRequest($this);
        $id = $this->request->data['id'];
        $result = $this->Account->find(
            'all',
            array(
                'conditions' => array('Account.id' => $id),
                'joins' => array(
                    array(
                        'table' => 'account_types',
                        'alias' => 'account_types',
                        'type'  =>  'left',
                        'conditions' => array(
                            'account_types.id = Account.account_type_id'
                        )
                    ),
                ),
                'fields' => array(
                    'Account.*', 'account_types.type_name', 'account_types.id'
                ),
            )
        );

        return json_encode($result);
    }

    public function updateData()
    {
        $this->layout = 'mastermanagement';

        if ($this->request->is('post')) {
            if (!empty($this->request->data)) {

                $id             = $this->request->data['hd_acc_id'];
                $acc_type       = $this->request->data['acc_type'];
                $acc_name       = $this->request->data['acc_name'];
                $acc_code       = $this->request->data['acc_code'];
                $acc_category   = $this->request->data['acc_category'];
                $postfix        = $this->request->data['postfix'];
                $memo           = $this->request->data['memo'];
                $formula        = $this->request->data['formula'];
                $base_param     = $this->request->data['hd_base_param'];
               

                $match_count = $this->Account->find('count', array(
                    'conditions' => array(
                        'Account.account_code' => $acc_code,
                        'Account.id !=' => $id,
                        'Account.flag' => 1
                    )
                ));

                //update data
                if ($match_count <= 0) {
                    try {
                        $this->Account->updateAll(
                            array(
                                "Account.flag" => '1',
                                "Account.account_type_id"   => "'" . $acc_type . "'",
                                "Account.account_code"      => "'" . $acc_code . "'",
                                "Account.account_name"      => "'" . $acc_name . "'",
                                "Account.account_type"      => "'" . $acc_category . "'",
                                //"operator"          => "'" . $operator . "'",
                                "Account.postfix"           => "'" . $postfix . "'",
                                // "Account.calculation_formula"           => "'" . $formula . "'",
                                "Account.memo"              => "'" . $memo . "'",
                                // "Account.base_param"        => "'" . $base_param . "'",
                                "Account.updated_date"      => "'" . date("Y-m-d H:i:s") . "'",
                                "Account.updated_by"        => "'" . $this->Session->read('LOGIN_ID') . "'"
                            ),
                            array("Account.id"    =>  $id)
                        );
                        $successMsg = parent::getSuccessMsg("SS002");
                        $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                    } catch (Exception $e) {
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE002', __("Account Code"));
                    $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                }
                $pageno = $this->Session->read('Page.pageCount');
                if($pageno > 1) {
                    $this->redirect(array('controller'=>'Accounts', 'action'=>'index', 'page' => $pageno));
                }else {
                    $this->redirect(array('controller' => 'Accounts', 'action' => 'index'));
                }
            }
        }
    }

    public function deleteData()
    {
        $id = $this->request->data['hd_acc_id'];

        $id_flag = $this->Account->find(
            'first',
            array(
                'conditions' => array('Account.id' => $id),
                'fields' => array('Account.flag')
            )
        );

        $b_param = $this->Account->find(
            'all',
            array(
                'conditions' => array('Account.flag' => 1),
                'fields' => array('Account.base_param')
            )
        );

        $using_param = 0;
        foreach ($b_param as $val) {
            $b = explode(",", str_replace('"', "", $val['Account']['base_param']));
            $using_param += in_array($id . "", $b);
        }

        if ($using_param != 0) {
            $errorMsg = parent::getErrorMsg('SE002', "The other based param used selected param");
            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
            $this->redirect(array('controller' => 'Accounts', 'action' => 'index'));
        } else {
            if ($id_flag['Account']['flag'] == '1') {

                $this->Account->updateAll(
                    array(
                        "flag" => '0',
                        "updated_date" => "'" . date("Y-m-d H:i:s") . "'",
                        "updated_by" => "'" . $this->Session->read('LOGIN_ID') . "'"
                    ),
                    array("Account.id" => $id)
                );

                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                $this->redirect(array('controller' => 'Accounts', 'action' => 'index'));
            } else {
                //Exclusive　error
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                $this->redirect(array('controller' => 'Accounts', 'action' => 'index'));
            }
        }
    }

}
