<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');

class StockSelectionsController extends AppController
{
    public $uses = array('Message','LayerType');
    public $components = array('Session','Flash');
    public $helpers = array('Html', 'Form');
   
    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        parent::checkUserStatus();
        // parent::CheckSession();

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
        
        // if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        //     $errorMsg = parent::getErrorMsg('SE065');
        //     $this->Flash->set($errorMsg, array("key"=>"Error"));
        //     $this->render('index');
        // }
    }
    /**
     *
     * index method
     * Aye Zar Ni Kyaw
     *
     */
    public function index()
    {
        $this->layout = 'stocks';
        $permissions = $this->Session->read('PERMISSIONS');
        $index_permt = $permissions['index'];
        
        
        $id             = $this->Session->read('LOGIN_ID');//inc_id
        $login_id       = $this->Session->read('LOGINID');//login_id
        $role_id        = $this->Session->read('ADMIN_LEVEL_ID');
        $list           = $index_permt['layers'];        

      
        //added by Khin Hnin Myo (retrieve Message from Message table)
        $noti_message = $this->Message->find('all', array(
                                            'fields'=>array('message'),
                                            'order' => array('Message.id DESC'),
                                            'limit' =>1));
        $show_msg = "";
        if (!empty($noti_message)) {
            $show_msg = $noti_message[0]['Message']['message'];
        }
        $list = [];
        $this->set('show_msg', $show_msg);
        $this->set('layer_name', $list);
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

        $period = $this->request->data['period'];
        $layer_code = $this->request->data['layer_code'];
        $login_id = $this->Session->read('LOGIN_ID');#auto increment id
        $get_login_id = $this->Session->read('LOGINID');#login id
        $lan['language'] = $this->Session->read('Config.language');

        $Common = New CommonController();
        $filterBaList = $Common->filterBAList($login_id, $get_login_id, date("Y-m-d", strtotime($period)), $lan['language'],$this->request->params['controller']);
       
        $layer_name = (array_key_exists($layer_code,$filterBaList))? (( $lan['language'] == 'eng')? $filterBaList[$layer_code]: $filterBaList[$layer_code]) : "";


        $name = ($lan['language'] == 'jpn') ? 'name_jp' : 'name_en';
        $this->Session->write('BALIST', $filterBaList);
        $la_code  = Setting::LAYER_SETTING[$this->request->params['controller']];
        $la_name  = $this->LayerType->find('list',array(
            'fields' => $name,
            'conditions' => array('type_order' => $la_code)
        ));
        $l_code = $la_name[$la_code];
        $l_name = $la_name[$la_code].' '.__('名');    
        $response = array(
            'period'          => $this->Session->write('StockSelections_PERIOD_DATE', $period),
            'layer_code' 	  => $this->Session->write('SESSION_LAYER_CODE', $layer_code),
            'layer_name' 	  => $this->Session->write('StockSelections_BA_NAME', $layer_name),
            'l_name'         => $this->Session->write('StockSelections_name',$l_name),
            'l_code'         => $this->Session->write('StockSelections_code',$l_code)
        );
        echo json_encode($response);
    }


    public function getBa() {

        parent::checkAjaxRequest($this);
        $Common = New CommonController();

        $period = $this->request->data['period'];
        $layer_code = $this->request->data['layer_code'];
        $login_id = $this->Session->read('LOGIN_ID');#ato increment id
        $admin_lvl = $this->Session->read('ADMIN_LEVEL_ID');
        $get_login_id = $this->Session->read('LOGINID');#login id
        $period = date("Y-m-d", strtotime($period));
        $lan['language'] = $this->Session->read('Config.language');
        $filterBaList = $Common->filterBAList($login_id, $get_login_id, $period, $lan['language'],$this->name);
        $list = (!empty($filterBaList)) ? $filterBaList + $lan : false;
        
        echo json_encode($list);exit();
    }
}
