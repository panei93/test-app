<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

/**
 * Rexchanges Controller
 *
 * @property Rexchange $Rexchange
 * @property PaginatorComponent $Paginator
 */
class RexchangesController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('Currency', 'Rexchange');
    public $components = array('Session', 'Flash', 'Paginator', 'PhpExcel.PhpExcel');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkSettingSession($this->name);
    }

    /**
     * index method
     * Thu Ta 
     * @return void
     */
    public function index()
    {

        $this->layout = 'mastermanagement';

        $currency = $this->Currency->find('all', array(
            'fields' => array('Currency.id', 'Currency.country', 'Currency.currency_code')
        ));


        if ($this->Session->read('Config.language') == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }

        try {

            $datas = $this->getPaginateUserDatas(Paging::TABLE_PAGING);
            $datas = $this->Paginator->paginate('Rexchange');
            $query_count = $this->params['paging']['Rexchange']['count'];
            $count = parent::getSuccessMsg('SS004', $query_count);
            $this->set('noDataMsg', parent::getErrorMsg('SE001'));
            $this->set(compact('currency', 'count', 'datas', 'query_count'));
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            //return $this->redirect(array('controller' => 'Users', 'action' => 'ResetPassword', 'param'=>'expire'));
        }
        return $this->render('index');
    }

    /**
     * add method
     * Thu Ta
     * @return void
     */
    public function add()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('post')) {
            $target_year = $this->request->data['target_year'];
            $rate = $this->request->data['exchange_amount'];
            $excode_id = $this->request->data['exchange_type'];
            $maincode_id =  $this->request->data['main_type'];
            $page_no = $this->request->data('hid_page_no');

            $excode = $this->Currency->find('all', array(
                'conditions' => array('Currency.id' => $excode_id),
                'fields'  => array('Currency.currency_code')
            ));

            $maincode = $this->Currency->find('all', array(
                'conditions' => array('Currency.id' => $maincode_id),
                'fields'  => array('Currency.currency_code')
            ));
            //duplicate target_year
            $dup_target_year = $this->Rexchange->find('all', array('fields' => array('Rexchange.target_year', 'Rexchange.flag')));

            if (!empty($dup_target_year)) {
                foreach ($dup_target_year as $value) {
                    if ($value['Rexchange']['target_year'] == $target_year && $value['Rexchange']['flag'] != 0) {
                        $errorMsg = parent::getErrorMsg('SE148');
                        $this->Flash->set($errorMsg, array("key" => "UserError"));

                        return $this->redirect(array('controller' => 'Rexchanges', 'action' => 'index/' . $page_no));
                    }
                }
            }

            try {
                $result = array(
                    'target_year' => $target_year,
                    'rate' => $rate,
                    'main_currency_code' => $maincode[0]['Currency']['currency_code'],
                    'ex_currency_code' => $excode[0]['Currency']['currency_code'],
                    'flag' => 1,
                );

                $this->Rexchange->save($result);
                $successMsg = parent::getSuccessMsg('SS001');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                return $this->redirect(array('controller' => 'Rexchanges', 'action' => 'index'));
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $errorMsg = parent::getErrorMsg('SE002', __("Account Code"));
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                $this->redirect('index');
            }
        }
    }

    /**
     * update method
     * Thu Ta
     * @return void
     */
    public function update()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('post')) {

            $id = $this->request->data['hd_id'];
            $rate = $this->request->data['exchange_amount'];
            $main_code = $this->request->data['main_type'];
            $exchange_code = $this->request->data['exchange_type'];
            $page_no = $this->request->data('hid_page_no');

            try {
                $excode = $this->Currency->find('all', array(
                    'conditions' => array('Currency.id' => $exchange_code),
                    'fields'  => array('Currency.currency_code')
                ));

                $maincode = $this->Currency->find('all', array(
                    'conditions' => array('Currency.id' => $main_code),
                    'fields'  => array('Currency.currency_code')
                ));
                $result = array(
                    'id' => $id,
                    'rate' => $rate,
                    'main_currency_code' => $maincode[0]['Currency']['currency_code'],
                    'ex_currency_code' => $excode[0]['Currency']['currency_code']
                );

                $this->Rexchange->save($result);
                $successMsg = parent::getSuccessMsg('SS002');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                return $this->redirect(array('controller' => 'Rexchanges', 'action' => 'index'));
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $errorMsg = parent::getErrorMsg('SE002', __("Account Code"));
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                $this->redirect('index');
            }
        }
    }

    /**
     * edit method
     * Thu Ta
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit()
    {
        parent::checkAjaxRequest($this);
        $id = $this->request->data['id'];
        if ($this->request->is(array('post', 'put'))) {
            $data = $this->Rexchange->find('all', array(
                "conditions" => array('Rexchange.id' => $id, 'Rexchange.flag' => 1),
                "fields" => array('Rexchange.*')
            ));
            $main_code = $this->Currency->find('all', array(
                "conditions" => array('Currency.currency_code' => $data[0]['Rexchange']['main_currency_code']),
                "fields" => 'Currency.id'
            ));
            $exchange_code = $this->Currency->find('all', array(
                "conditions" => array('Currency.currency_code' => $data[0]['Rexchange']['ex_currency_code']),
                "fields" => 'Currency.id'
            ));
            $response = array(
                'hd_id' => $data[0]['Rexchange']['id'],
                'exchange_currency_code' => $data[0]['Rexchange']['ex_currency_code'],
                'main_currency_code' => $data[0]['Rexchange']['main_currency_code'],
                'target_year' => $data[0]['Rexchange']['target_year'],
                'rate' => $data[0]['Rexchange']['rate'],
                'main_country' => $main_code[0]['Currency']['id'],
                'exchange_country'  =>  $exchange_code[0]['Currency']['id']
            );
        }

        echo json_encode($response);
    }

    /**
     * delete method
     * Thu Ta
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete()
    {
        $id = $this->request->data['hd_id'];

        // $page_no = $this->request->data['hid_page_no'];
        if ($this->request->is('post')) {
            $id_flag = $this->Rexchange->find('first', array(
                'conditions' => array('Rexchange.id' => $id),
                'fields' => array('Rexchange.flag')
            ));
            if ($id_flag['Rexchange']['flag'] != 0) {
                $result = array(
                    'id' => $id,
                    'flag' => 0
                );

                $this->Rexchange->save($result);

                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            } else {
                $errorMsg = parent::getErrorMsg('SE007');
                $this->Flash->set($errorMsg, array("key" => "UserError"));
            }

            $this->redirect(array('controller' => 'Rexchanges', 'action' => 'index/'));
        }
    }

    public function getPaginateUserDatas($limit)
    {
        $this->paginate  = array(
            'maxLimit' => $limit,
            'limit' => $limit,
            'conditions' => array('Rexchange.flag' => 1),
            'fields' => array(
                'Rexchange.*'
            ),
            'order' => 'Rexchange.id ASC'

        );

        $datas = $this->Paginator->paginate('Rexchange');
        return $datas;
    }

    public function getMainCurrencyCode()
    {

        parent::checkAjaxRequest($this);
        $id = $this->request->data['id'];

        $code = $this->Currency->find('all', array(
            'conditions' => array('Currency.id' => $id),
            'fields' => array('Currency.currency_code')
        ));
        return json_encode($code);
    }

    public function getExchangeCurrencyCode()
    {

        parent::checkAjaxRequest($this);
        $id = $this->request->data['id'];

        $code = $this->Currency->find('all', array(
            'conditions' => array('Currency.id' => $id),
            'fields' => array('Currency.currency_code')
        ));
        return json_encode($code);
    }
}
