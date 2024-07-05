<?php
/**
 *	AssetProgressController
 *	@author sandikhaing
 *
 **/

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
class AssetProgressController extends AppController
{
    public $uses = array('Asset','LayerType');
    public $components = array('Session','Flash');

    public function beforeFilter()
    {
        // parent::checkUserStatus();
        // parent::CheckSession();
        // parent::checkAccessType();
        // parent::CheckFixedAssetSelection();
        
        $Common = New CommonController();
        $login_id = $this->Session->read('LOGIN_ID');
        $get_login_id = $this->Session->read('LOGINID');#login id
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename = $this->request->params['controller'];

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $event_id = $this->Session->read('EVENT_ID');
        // parent::checkUserStatus();
        // parent::CheckSession();
        #request from url (edited by khin hnin myo)  
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
    
        $this->Session->write('PERMISSIONS', $permissions);
      
        if(empty($event_id))
        {
            $errorMsg = parent::getErrorMsg('SE072', __('イベント名'));
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
        }
        // else{
        //     pr(array_keys($permissions['index']['layers']));die;
        //     if((!array_key_exists($layer_code, array_keys($permissions['index']['layers']))) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        //         $errorMsg = parent::getErrorMsg('SE065');
        //         $this->Flash->set($errorMsg, array("key"=>"Error"));
        //         $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
        //     }
        // }
    }
    
    public function index()
    {
        $this->layout = 'fixedassets';
        $Common = New CommonController();
        $period = '';
        //$Eventname = $this->Session->read('SapSelections_PERIOD_DATE');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        # check user has permission to access this method
        $eventname_session = $this->Session->read('EVENT_NAME');

        $event_id = $this->Session->read('EVENT_ID');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        
        $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
        $lan = $this->Session->read('Config.language');
        $type_order = Setting::LAYER_SETTING[3];
       
        //$header = array_column(array_column($Common->getLayerNameOnCode($layer_code, $lan, $type_order), 'LayerType'), 'layer_name');
        $header = $Common->getLayerNameOnCode($layer_code, $lan, $type_order);
        $language = ($lan == 'eng') ? 'en' : 'jp';
        $progress_data = $this->Asset->getProgressChart($event_id, $language);
        $layers = $this->LayerType->find('list', array(
            'fields' => array('type_order', 'name_'.$language),
            'conditions' => array(
                'LayerType.flag' => 1
            )
        ));

        /* paginator*/
        $pg_data = count($progress_data);
        if ($pg_data == 0) {
            $this->set('errorMsg', parent::getErrorMsg('SE001'));
            $this->set('EventSuccess', "");
        }

        $total_rows = parent::getSuccessMsg('SS004', $pg_data);

        $this->set('progress_data', $progress_data);
        $this->set('total_rows', $total_rows);
        $this->set('eventname_session', $eventname_session);
        $this->set('pg_data', $pg_data);
        $this->set('header', $header);
        $this->set('layers', $layers);
        $this->render('index');
    }

    public function progressChart_pdf()
    {
        $eventname_session = $this->Session->read('EVENT_NAME');
        $event_id = $this->Session->read('EVENT_ID');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $type_order = Setting::LAYER_SETTING[3];
        $Common = New CommonController();
        if ($this->request->is('post')) {
            $Eventname = $this->Session->read('SapSelections_PERIOD_DATE');
            $lan = $this->Session->read('Config.language');
            $header = array_column(array_column($Common->getLayerNameOnCode($layer_code, $lan, $type_order), 'LayerType'), 'layer_name');
            //pr($Common->getLayerNameOnCode($layer_code, $lan, $type_order));die;
            $header = $Common->getLayerNameOnCode($layer_code, $lan, $type_order);
            $language = ($lan == 'eng') ? 'en' : 'jp';
            $progress_data = $this->Asset->getProgressChart($event_id, $language);
            
            $layers = $this->LayerType->find('list', array(
                'fields' => array('type_order', 'name_'.$language),
                'conditions' => array(
                    'LayerType.flag' => 1
                )
            ));    
            $pg_data = count($progress_data);
            if ($pg_data == 0) {
                $this->set('errorMsg', parent::getErrorMsg('SE001'));
                $this->set('EventSuccess', "");
                $this->redirect(array('controller'=>'AssetProgress', 'action'=>'index'));
            }
            $total_rows = parent::getSuccessMsg('SS004', $pg_data);
            
            $this->set('progress_data', $progress_data);
            $this->set('total_rows', $total_rows);
            $this->set('eventname_session', $eventname_session);
            $this->set('header', $header);
            $this->set('layers', $layers);

        } else {
            $this->redirect(array('controller'=>'AssetProgress', 'action'=>'index'));
        }
    }
}
