<?php
App::uses('AppController', 'Controller');

/**
 *	AssetEventsController
 *	@author Sandi Khaing
 *
 **/
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'Permissions');

class AssetEventsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('AssetEvent','Asset');
    public $components = array('Session','Flash','RequestHandler', 'Paginator');

    

    public function beforeFilter()
    {
        parent::CheckSession();
        $Common = New CommonController();

        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);
     
        //if((!in_array($layer_code, array_keys($permissions['index']['layers']))) || ($layer_code=="" && $permissions['index']['limit']>0)) {
        if($permissions['index']['limit']>0) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
        }
    }

    public function index()
    {
        $Common = New CommonController();
        $this->layout = 'mastermanagement'; #layout in fixassets
        $eventname_session = $this->Session->read('EVENT_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
        $language   = $this->Session->read('Config.language');
        if ($this->Session->check('SAVE_EVENT_NAME')) {
            $save_event_name = $this->Session->read('SAVE_EVENT_NAME');
            $this->Session->delete('SAVE_EVENT_NAME');
        } else {
            $save_event_name = '';
        }

        if ($this->Session->check('SAVE_EVENT_REF')) {
            $save_event_ref = $this->Session->read('SAVE_EVENT_REF');
            $this->Session->delete('SAVE_EVENT_REF');
        } else {
            $save_event_ref = '';
        }

        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');

        $permissions = $this->Session->read('PERMISSIONS');
        // $status = $this->AssetEvent->find('first',array(
        //     'conditions' => array(
        //         'AssetEvent.flag <>' => 0
        //     ),
        // ));
        $status = 1;
        $buttons = $Common->getButtonLists($status,$layer_code,$permissions);
      
        #condition change ''flag '=>'null to 0 12/9/2019
        
        if($save_event_name != '') $conditions = array('NOT'=>array('flag '=>'0', 'id' =>$this->Session->read('SAVE_EVENT_ID')));
        else $conditions = array('NOT'=>array('flag '=>'0'));
        $Event_ref_data = $this->AssetEvent->find('list', array(
                                            'fields'=>array('id','event_name'),
                                             'conditions'=>$conditions,
                                             'order' => array('AssetEvent.id DESC')
                                            ));
        $refIds = $this->AssetEvent->find('list', array(
                                        'fields'=>array('reference_event_id'),
                                        'conditions'=>array('NOT'=>array('reference_event_id' => null)),
                                        ));
        try {
            $this->paginate = array(
                                    'limit' => Paging::TABLE_PAGING,
                                    'conditions' => array(
                                        'NOT' => array(
                                            'flag' => 0
                                        )
                                    )
                                );
            $list = $this->Paginator->paginate('AssetEvent');
            //echo $count = $this->params['paging']['AssetEvent']['count'];
            $count = sizeof($list);
            //show total row msg
            if ($count == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $count));
                $this->set('errmsg', "");
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            CakeLog::write('debug', $e->getMessage());
            // $this->render('index');
            $this->redirect(array('controller'=>'AssetEvents', 'action'=>'index'));
        }

        $this->set('Event_ref_data', $Event_ref_data);
        $this->set('list', $list);
        $this->set('rowcount', $count);
        $this->set('save_event_name', $save_event_name);
        $this->set('save_event_ref', $save_event_ref);
        $this->set('buttons', $buttons);
        $this->set('language',$language);
        $this->set('hid_id',$this->Session->read('SAVE_EVENT_ID'));
        $this->set('refIds',$refIds);
        $this->render('index');
    }

    public function saveData()
    {
        $this->layout = 'mastermanagement'; #layout in fixassets
        $login_id= $this->Session->read('LOGIN_ID');//get login_id
        if ($this->request->is('post')) {
            $event_name = trim($this->request->data('event_name'));
            $event_reference = $this->request->data('event_reference');
            $hid_id = $this->request->data('hid_id');
            $param = array();
            $param["event_name"] = $event_name;
            $param["reference_event_id"] = $event_reference;
            $param["created_by"] = $login_id;
            $param["updated_by"] = $login_id;

            /* Find same event name */
            if($hid_id != ''){
                $exist_count = $this->AssetEvent->find('count', array('conditions'=>array('event_name'=>$event_name, 'id !='=>$hid_id)));
            }else{
                $exist_count = $this->AssetEvent->find('count', array('conditions'=>array('event_name'=>$event_name)));
            }
            /* Find inactive event */
            $Event_ref_data = $this->AssetEvent->find('all', array(
                                            'fields'=>array('id','event_name','reference_event_id'),
                                             'conditions'=>array('NOT'=>array('flag '=>'0'))
                                            ));
            
                    

            
            if ($exist_count > 0) {// Check Event Name already exists!

                $this->Session->write('SAVE_EVENT_NAME', $event_name);
                $this->Session->write('SAVE_EVENT_REF', $event_reference);
                $errorMsg = parent::getErrorMsg('SE002', __("イベント名"));
                $this->Flash->set($errorMsg, array("key"=>"EventError"));
                $this->redirect(array(
                    'controller' => 'AssetEvents',
                    'action' => 'index'
                    ));
            } else { //Event Name not exists!
                /* Begin  BCMM Sandi */

                //check there is no data in reference event
                if (!empty($event_reference)) {
                    # Reference data check record in tbl_m_asset
                    $count_reference_data =$this->Asset->find('count', array('conditions' => array('asset_event_id' => $event_reference)));

                    #Get Reference name
                    $ref_namshow =$this->AssetEvent->find('first', array('conditions' => array('id' => $event_reference)))['AssetEvent']['event_name'];

                    if ($count_reference_data==0) {
                        $this->Session->write('SAVE_EVENT_ID', $hid_id);
                        $this->Session->write('SAVE_EVENT_NAME', $event_name);
                        $this->Session->write('SAVE_EVENT_REF', $event_reference);
                        $errorMsg = parent::getErrorMsg('SE054', $ref_namshow);
                        $this->Flash->set($errorMsg, array("key"=>"EventError"));
                        $this->redirect(array(
                            'controller' => 'AssetEvents',
                            'action' => 'index'
                            ));
                    }
                }
                /* End  BCMM Sandi*/
                if($hid_id != ''){
                    $param = array(
                        'id'=> $hid_id,
                        'event_name'=> $event_name,
                        'reference_event_id'=> $event_reference,
                        'updated_by'=> $this->Session->read('LOGIN_ID'),
                        'updated_date'=> date("Y-m-d H:i:s")

                    );
                    $saveUserinfo = $this->AssetEvent->save($param);
                    if ($this->Session->check('SAVE_EVENT_ID')) {
                        $this->Session->delete('SAVE_EVENT_ID');
                    }
                    $successMsg = parent::getSuccessMsg('SS002');
                }else{
                    $saveUserinfo = $this->AssetEvent->save($param);

                    if ($this->Session->check('SAVE_EVENT_NAME')) {
                        $this->Session->delete('SAVE_EVENT_NAME');
                    }
                    if ($this->Session->check('SAVE_EVENT_REF')) {
                        $this->Session->delete('SAVE_EVENT_REF');
                    }
                    $successMsg = parent::getSuccessMsg('SS001');
                }
                
                $this->Flash->set($successMsg, array("key"=>"EventSuccess"));
                CakeLog::write('debug', $event_name.' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->redirect(array(
                            'controller' => 'AssetEvents',
                            'action' => 'index'
                            ));
            }
        } else {
            $this->redirect(array(
                            'controller' => 'AssetEvents',
                            'action' => 'index'
                            ));
        }
    }

    /*Purpose to make Inactive state and input validation */
    /**
     *	@author Sandi khaing
     **/
    
    public function activeStatusChange()
    {
        if ($this->request->is('post')) {
            $pageNo =  $this->request->data('hid_page_no');
            $eventId = $this->request->data('hid_id');

            $login_id= $this->Session->read('LOGIN_ID');//get login_id
            $updated_by = $login_id;

            # When eventid  use in tbl_m_asset is not change in inactive status.
            $event_result = $this->AssetEvent->find('all', array('conditions'=>array('id'=>$eventId)));
            $get_flag = $event_result[0]['AssetEvent']['flag'];
            $event_msg_name = $event_result[0]['AssetEvent']['event_name'];
            
            #change in IN Active stage
            if ($get_flag == 1) {
                $active_flag  = $this->AssetEvent->activeFlagChange($updated_by, $eventId);
                $successMsg = parent::getSuccessMsg('SS016', $event_msg_name);
                $this->Flash->set($successMsg, array("key"=>"EventSuccess"));
                $this->redirect(array('controller'=>'AssetEvents','action' => 'index/'.$pageNo));
            } else {
                $errorMsg = parent::getErrorMsg('SE062');
                $this->Flash->set($errorMsg, array("key"=>"EventError"));
                $this->redirect(array(
                    'controller' => 'AssetEvents',
                            'action' => 'index/'.$pageNo
                            ));
            }
        }
    }
    
    /**
     * Purpose to make Active state and validation in not equal in flag 0
     * @author Sandi Khaing
     **/
    public function InActiveStatusChange()
    {
        if ($this->request->is('post')) {
            $pageNo =  $this->request->data('hid_page_no');
            $eventId = $this->request->data('hid_id');
            
            $login_id= $this->Session->read('LOGIN_ID');//get login_id
            $updated_by = $login_id;

            #Find flag not equal event_id 0
            $event_result = $this->AssetEvent->find('all', array('conditions'=>array('id'=>$eventId)));
            
           $get_flag = $event_result[0]['AssetEvent']['flag'];
            #Get event name For when message show
            $event_msg_name = $event_result[0]['AssetEvent']['event_name'];
            #change in Active stage
            if ($get_flag == 2) {
                $Inactive_flag  = $this->AssetEvent->InactiveFlagChange($updated_by, $eventId);
                
                $successMsg = parent::getSuccessMsg('SS017', $event_msg_name);
                $this->Flash->set($successMsg, array("key"=>"EventSuccess"));
                $this->redirect(array('controller'=>'AssetEvents','action' => 'index/'.$pageNo));
            } else {
                $errorMsg = parent::getErrorMsg('SE063');
                $this->Flash->set($errorMsg, array("key"=>"EventError"));
                $this->redirect(array(
                            'controller' => 'AssetEvents',
                                    'action' => 'index/'.$pageNo
                                    ));
            }
        }
    }
    public function getEvent(){
        #only allow ajax request
		parent::checkAjaxRequest($this);
	
		$id = $this->request->data['id'];
        $Event_ref_data = $this->AssetEvent->find('list', array(
            'fields'=>array('id','event_name'),
             'conditions'=>array('NOT'=>array('flag '=>'0', 'id' => $id)),
             'order' => array('AssetEvent.id DESC')
            ));

		$event = $this->AssetEvent->find('all', array('conditions'=>array('id'=>$id, 'flag'=>1)));
        
        $response = array(
			'id'                => $event[0]['AssetEvent']['id'],
			'event_name'        => $event[0]['AssetEvent']['event_name'],
			'event_reference'   => $event[0]['AssetEvent']['reference_event_id'],
            'Event_ref_data'    => $Event_ref_data,
		);
        echo json_encode($response);
    }
    public function DeleteData(){
        $id = $this->request->data['hid_id'];
        $usedRefernce = $this->AssetEvent->find(
			'count',
			array(
				'conditions' => array('reference_event_id' => $id),
				'fields' => array('flag', 1)
			)
		);
        if($usedRefernce > 0){
            $err = array(__('このイベント'), __('その他のイベント'));
            $errorMsg = parent::getErrorMsg('SE130', $err);
            $this->Flash->set($errorMsg, array("key"=>"EventError"));
            $this->redirect(array(
                        'controller' => 'AssetEvents',
                                'action' => 'index/'
                                ));
        }else{
            $result = array(
				'id' => $id,
				'flag' => 0,
				'updated_date'  => date("Y-m-d H:i:s"),
				'updated_by'    => $this->Session->read('LOGIN_ID')
			);
			$this->AssetEvent->save($result);
            $successMsg = parent::getSuccessMsg('SS003');
			$this->Flash->set($successMsg, array("key" => "EventSuccess"));
            $this->redirect(array(
                'controller' => 'AssetEvents',
                        'action' => 'index/'
                        ));
        }
    }
}
