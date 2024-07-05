<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class AssetSelectionsController extends AppController
{
    public $uses = array('AssetEvent');
    public $components = array('Session','Flash');
    public $helpers = array('Html', 'Form');

    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
        
        $show_menu_lists = $Common->getMenuByRole($role_id, $pagename);
        $this->Session->write('MENULISTS', $show_menu_lists);
        
        /*if((!in_array($layer_code, array_keys($permissions['index']['layers']))) || ($layer_code=="" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->render("index");
        }*/
    }
    /**
     *
     * index method
   * Aye Zar Ni Kyaw
     *
     */
    public function index()
    {
        $this->layout = 'fixedassets';
        $Common = New CommonController();
        
        $login_id = $this->Session->read('LOGIN_ID');
        $get_login_id = $this->Session->read('LOGINID');#login id
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $event_id = $this->Session->read('EVENT_ID');
        $event_name = $this->eventData();
        $layer_three = $this->Session->read('BALIST');
        
        $this->set(compact('event_name', 'layer_three', 'layer_code', 'event_id'));
        $this->render('index');
    }
    /**
     *
     * add method
   * Aye Zar Ni Kyaw
     *
     */
    public function add()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $Common = New CommonController();

        $language = $this->Session->read('Config.language');
        $event_id = $this->request->data['event_id'];
        $layer_code = $this->request->data['layer_code'];

        $login_id = $this->Session->read('LOGIN_ID');#auto increment id
        $admin_lvl = $this->Session->read('ADMIN_LEVEL_ID');
        $get_login_id = $this->Session->read('LOGINID');#login id
        
        $event_data = $this->eventData($event_id);
        $event_date = date("Y-m-d", strtotime(explode('/', array_keys($event_data)[0])[1]));
        
        $pagename = $this->request->params['controller'];
        $filterBaList = $Common->filterBAList($login_id, $get_login_id, $event_date, $language, $pagename);
        
        $this->Session->write('BALIST', $filterBaList);
        $layer_name = ($filterBaList[$layer_code]['code'] == $layer_code)? (($language == 'eng')? $filterBaList[$layer_code]['name_en'] : $filterBaList[$layer_code]['name_jp']) : "";
        
        $event_name = array_values($event_data)[0];
        
        $this->Session->write('EVENT_ID', $event_id);
        $this->Session->write('EVENT_NAME', $event_name);
        $this->Session->write('SESSION_LAYER_CODE', $layer_code);
        $this->Session->write('BASIC_SELECTION_BA_NAME', $layer_name);
        
        return json_encode(1);
    }

    public function getBa() {
        parent::checkAjaxRequest($this);
        $Common = New CommonController();

        $language = $this->Session->read('Config.language');
        $event_id = $this->request->data['event_id'];

        $login_id = $this->Session->read('LOGIN_ID');#ato increment id
        $admin_lvl = $this->Session->read('ADMIN_LEVEL_ID');
        $get_login_id = $this->Session->read('LOGINID');#login id
        $event_name = $this->eventData($event_id);
        $event_date = date("Y-m-d", strtotime(explode('/', array_keys($event_name)[0])[1]));
        
        $pagename = $this->request->params['controller'];
        $filterBaList = $Common->filterBAList($login_id, $get_login_id, $event_date, $language, $pagename);
        
        echo json_encode($filterBaList);
    }

    public function eventData($event_id = '') {   
        $conditions = [];
        $conditions['flag'] = 1;
        if(!empty($event_id)) $conditions['id'] = $event_id;
        $this->AssetEvent->virtualFields['data'] = "CONCAT(AssetEvent.id, '/', AssetEvent.created_date)";
        $event_name = $this->AssetEvent->find('list', array(
            'fields' => array('data','event_name'),
            'conditions' => $conditions,
            'order' => array('id DESC')
        ));
        return $event_name;
    }
}
