<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * RtaxFees Controller
 *
 * @property RTaxFee $RTaxFee
 * @property PaginatorComponent $Paginator
 */
class RtaxFeesController extends AppController
{
    public $uses = array('RTaxFee');

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkSettingSession($this->name);
    }

    /**
     * index method
     *
     * @return void
     */
    public function index()
    {
        $this->RTaxFee->recursive = 0;
        $this->layout = 'mastermanagement';
        $this->Session->write('LAYOUT', 'mastermanagement');
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkExpiredUser();

        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
        // }

        $this->layout = 'mastermanagement';


        if ($this->Session->read('Config.language') == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }

        try {
            $datas = $this->getPaginateUserDatas(Paging::TABLE_PAGING);
            $datas = $this->Paginator->paginate('RTaxFee');
            $query_count = $this->params['paging']['RTaxFee']['count'];
            $count = parent::getSuccessMsg('SS004', $query_count);
            $this->set('noDataMsg', parent::getErrorMsg('SE001'));
            $this->set(compact('count', 'datas', 'query_count'));
            return $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            //return $this->redirect(array('controller' => 'Users', 'action' => 'ResetPassword', 'param'=>'expire'));
        }
        return $this->render('index');
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null)
    {
        if (!$this->RTaxFee->exists($id)) {
            throw new NotFoundException(__('Invalid rtax fee'));
        }
        $options = array('conditions' => array('RTaxFee.' . $this->RTaxFee->primaryKey => $id));
        $this->set('RTaxFee', $this->RTaxFee->find('first', $options));
    }

    /**
     *
     * add method
     * Zeyar Min
     * 
     */
    public function add()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('post')) {
            $target_year = $this->request->data['target_year'];
            $int_percentage = $this->request->data['percentage'];
            // $int_percentage = substr($percentage, 0, -1);
            $actual_link = $_SERVER["HTTP_REFERER"];

            if (!empty($this->request->data['primary_id'])) { //update mode
                //data prepare
                $id_flag = $this->RTaxFee->find(
                    'first',
                    array(
                        'conditions' => array('id' => $this->request->data['primary_id']),
                        'fields' => 'flag'
                    )
                );
                if ($id_flag['RTaxFee']['flag'] == 1) {
                    // update
                    $result = array(
                        'id'            => $this->request->data['primary_id'],
                        'target_year'    => $target_year,
                        'rate'     => $int_percentage,
                    );

                    #change $role_id into $post_name by HHK
                    $pageNo =  $this->request->data['hid_page_no'];
                    $this->RTaxFee->save($result);
                    $successMsg = parent::getSuccessMsg('SS002');
                    $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                    return $this->redirect(array('controller' => 'RtaxFees', 'action' => 'index'));
                } else {
                    $pageNo =  $this->request->data['hid_page_no'];
                    $errorMsg = parent::getErrorMsg('SE037');
                    $this->Flash->set($errorMsg, array("key" => "Error"));
                    return $this->redirect(array('controller' => 'RtaxFees', 'action' => 'index'));
                }
            } else {
                try {
                    $checked_data = $this->RTaxFee->find('first', array(
                        'conditions' => array(
                            'target_year' => $target_year,
                            'flag' => 1
                        )
                    ));
                    if (!empty($checked_data)) {
                        $errorMsg = parent::getErrorMsg('SE148');
                        $this->Flash->set($errorMsg, array("key" => "Error"));
                    } else {
                        $result = array(
                            'target_year' => $target_year,
                            'rate' => $int_percentage,
                            'flag' => 1,
                        );
                        $this->RTaxFee->save($result);
                        $successMsg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                    }
                    #testing
                    return $this->redirect(array('controller' => 'RtaxFees', 'action' => 'index'));
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                    $this->redirect('index');
                }
            }
        }
    }

    /**
     *
     * get edit data method
     * Zeyar Min
     *
     */

    public function editData()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $taxfees = $this->RTaxFee->find(
            'first',
            array(
                'conditions' => array('id' => $this->request->data['id'], 'flag' => 1),
                'fields' => array('id', 'target_year', 'rate')
            )
        );

        $response = array(
            'id'                => $taxfees['RTaxFee']['id'],
            'target_year'          => $taxfees['RTaxFee']['target_year'],
            'rate'         => $taxfees['RTaxFee']['rate'],
        );
        echo json_encode($response);
    }


    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null)
    {
        if (!$this->RTaxFee->exists($id)) {
            throw new NotFoundException(__('Invalid rtax fee'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->RTaxFee->save($this->request->data)) {
                $this->Flash->success(__('The rtax fee has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->error(__('The rtax fee could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('RTaxFee.' . $this->RTaxFee->primaryKey => $id));
            $this->request->data = $this->RTaxFee->find('first', $options);
        }
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null)
    {
        $id_flag = $this->RTaxFee->find(
            'first',
            array(
                'conditions' => array('id' => $this->request->data['id']),
                'fields' => array('flag')
            )
        );

        $pageNo =  $this->request->data['hid_page_no'];

        if ($id_flag['RTaxFee']['flag'] == 1) {

            $result = array(
                'id' => $this->request->data['id'],
                'flag' => 0,
            );
            $this->RTaxFee->save($result);
            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array("key" => "UserSuccess"));
            return $this->redirect(array('controller' => 'RtaxFees', 'action' => 'index/' . $pageNo));
        } else {
            $errorMsg = parent::getErrorMsg('SE037');
            $this->Flash->set($errorMsg, array("key" => "Error"));
            return $this->redirect(array('controller' => 'RtaxFees', 'action' => 'index/' . $pageNo));
        }
    }

    public function getPaginateUserDatas($limit)
    {
        $this->paginate  = array(
            'maxLimit' => $limit,
            'limit' => $limit,
            'conditions' => array('RTaxFee.flag' => 1),
            'fields' => array(
                'RTaxFee.*'
            ),
            'order' => 'RTaxFee.id ASC'

        );

        $datas = $this->Paginator->paginate('RTaxFee');
        return $datas;
    }
}
