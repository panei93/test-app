<?php
App::uses('CakeText', 'Utility');
App::uses('AppController', 'Controller');

App::import('Controller', 'Common');


class BrmMonthlyProgressReportController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('BrmMrApprove','Layer','LayerType');
    public $components = array('Session','Flash','Paginator');


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
        $layers = array_keys($permissions['index']['layers']);
        
        if((!in_array($layer_code, $layers)) || ($layer_code=="" && $permissions['index']['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller'=>'BrmTermSelection', 'action'=>'index'));
        }
        $term_id = $this->Session->read('TERM_ID');
        $target_month = $this->Session->read('TARGETMONTH');

        #check target_month,term_id and show error message
        if (!$term_id && !$target_month) {
            $errorMsg = parent::getErrorMsg('SE080');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }

        #check target month and show error message
        if (!$target_month) {
            $errorMsg = parent::getErrorMsg('SE085');
            $this->Flash->set($errorMsg, array("key"=>"TermError"));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
    }
    /**
     *
     * index method
     * Aye Zar Ni Kyaw
     * 29_06_2020
     *
     */
    public function index()
    {
        #flag to show hide approve button in view
        $Common = new CommonController;
        // $readLimit = $Common->checkLimit($read_limit, $ba_code);
        // die("here");

        $this->layout = 'phase_3_menu';

        $budget_term 	= $this->Session->read('TERM_NAME');
        $term_id 		= $this->Session->read('TERM_ID');
        $target_month 	= $this->Session->read('TARGETMONTH');
        $user_id 		= $this->Session->read('LOGIN_ID'); #get user id
        $language       = $this->Session->read('Config.language');
        
        $today_date = date("Y/m/d") ;

        #get permission when login
        if ($this->Session->check('PERMISSIONS')) {
            $permission = $this->Session->read('PERMISSIONS');
        }
        #get read_limit 1 or 2 or 3 or 4
        $readLimit   = $permission['index']['limit'];
        $conditions = array();

        if ($readLimit == 0) {
            $conditions = array(
                'Layer.flag' => 1,
                'Layer.to_date >=' => $today_date,
                'Layer.type_order' => Setting::LAYER_SETTING['topLayer']
            );
            $layer_list = $this->Layer->find('all', array(
                'fields' => array(
                    'Layer.name_jp as hlayer','second_layer.name_jp as dlayer','Layer.layer_code as hlayer_code','second_layer.layer_code as dlayer_code'
                ),
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'second_layer',
                        // 'type'  => 'LEFT',
                        'conditions' => array(
                            'second_layer.flag = 1',
                            'second_layer.to_date >= '=> $today_date,
                            'second_layer.type_order' => Setting::LAYER_SETTING['middleLayer'],
                            // "second_layer.parent_id LIKE CONCAT('%\"L', ".Setting::LAYER_SETTING['topLayer'].", '\":\"',Layer.layer_code,'\"%')"
                            "second_layer.parent_id LIKE CONCAT('%', Layer.layer_code,'%')"
                        )
                    ),
                ),
                'conditions' => $conditions,
                'group' => array('Layer.layer_code,second_layer.layer_code'),
                'order' => array('Layer.id ASC')
            ));
        } elseif ($readLimit == 1) {
            $layer_list = [];
            foreach ($permission['index']['parent_data'] as $p_data) {
                $parent_list = implode(",",$p_data);
                $layer_data = $this->Layer->find('all', array(
                    'fields' => array(
                        'Layer.name_jp as hlayer','second_layer.name_jp as dlayer','Layer.layer_code as hlayer_code','second_layer.layer_code as dlayer_code'
                    ),
                    'joins' => array(
                        array(
                            'table' => 'layers',
                            'alias' => 'second_layer',
                            // 'type'  => 'LEFT',
                            'conditions' => array(
                                'second_layer.flag = 1',
                                'second_layer.to_date >= '=> $today_date,
                                'second_layer.type_order' => Setting::LAYER_SETTING['middleLayer'],
                                'second_layer.layer_code IN ('.$parent_list.')'
                            )
                        ),
                    ),
                    'conditions' => array(
                        'Layer.flag' => 1,
                        'Layer.to_date >=' => $today_date,
                        'Layer.layer_code' => $p_data['L1']
                     ),
                    'group' => array('Layer.layer_code,second_layer.layer_code'),
                    'order' => array('Layer.id ASC')
                ));
                array_push($layer_list,$layer_data);
            }
            $layer_list = array_reduce($layer_list, 'array_merge', array());
        }

        $query_count = sizeof($layer_list);
        $count = parent::getSuccessMsg('SS004', $query_count);
        
        //no count value
        if ($query_count == 0) {
            $no_data = parent::getErrorMsg("SE001");
            $this->set(compact('no_data'));
        }

        $dept_approved = $this->BrmMrApprove->find('list', array(
            'fields' => array('dlayer_code','updated_date','hlayer_code'),
            'conditions' => array(
                'target_month' => $target_month ,
                'dlayer_code !=' => 0,
                'flag' => 2,
            )
        ));
        
        $head_approved = $this->BrmMrApprove->find('list', array(
            'fields' => array('hlayer_code','updated_date'),
            'conditions' => array(
                'target_month' => $target_month ,
                'dlayer_code' => 0,
                'flag' => 2,
            )
        ));
        $this->LayerType->virtualFields['layer_type_name'] = $language == 'eng' ? 'name_en' : 'name_jp';
        $layer_type = $this->LayerType->find('list',array(
            'fields' => array('type_order','layer_type_name'),
            'conditions' => array(
                'type_order' => array(SETTING::LAYER_SETTING['topLayer'],SETTING::LAYER_SETTING['middleLayer']),
                'flag' => 1,
            ),
            'order' => 'type_order ASC'
        ));

        $this->set(compact('budget_term', 'target_month', 'layer_list', 'dept_approved', 'head_approved', 'count','layer_type'));
        return $this->render('index');
    }

    #to get head dept and dept
    public function getBACollection()
    {
        $user_id 	= $this->Session->read('LOGIN_ID'); #get user id
        $login_id 	= $this->Session->read('LOGINID'); #get login id

        # get user's BA
        $get_ba = $this->UserModel->find('list', array(
            'fields' => 'ba_code',
            'conditions' => array(
                'id' => $user_id,
                'flag' => 1
            )
        ));

        # get ba code by user id
        $ba_collection['ba_code'] = $get_ba[$user_id];

        # get headquarter id and dept id by ba_code
        $get_head_dept_id = $this->Layer->find('first', array(
            'fields' => array('head_dept_id','dept_id'),
            'conditions' => array(
                'ba_code' => $ba_collection['ba_code'],
                'flag' => 1
            )
        ));

        $head_dept_id = $get_head_dept_id['Layer']['head_dept_id'];
        $dept_id = $get_head_dept_id['Layer']['dept_id'];

        #get ba code by head dept id
        $ba_collection['head_dept'] = $this->Layer->find('list', array(
            'fields' => array('ba_code'),
            'conditions' => array(
                'head_dept_id' => $head_dept_id,
                'flag' => 1
            )
        ));

        #get ba code by dept id
        $ba_collection['dept'] = $this->Layer->find('list', array(
            'fields' => array('ba_code'),
            'conditions' => array(
                'dept_id' => $dept_id,
                'flag' => 1
            )
        ));

        return $ba_collection;
    }
}
