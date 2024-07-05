<?php
App::uses('CakeText', 'Utility');
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
define('UPLOAD_FILEPATH', ROOT); # server
define('UPLOAD_PATH', 'app' . DS . 'temp'); # path

class UsersController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('User', 'PasswordHistory', 'LayerGroup', 'Role', 'Layer', 'LayerType', 'Position');
    public $components = array('Session', 'Flash', 'Paginator', 'PhpExcel.PhpExcel');

    /**
     *
     * index method
     * Aye Zar Ni Kyaw
     *
     */
    public function index()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkExpiredUser();
        parent::checkSettingSession($this->name);
        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
        // }
        $this->Session->write('LAYOUT', 'mastermanagement');
        $this->layout = 'mastermanagement';

        $session_mail_error = $this->Session->read('MAIL_ERROR') != "" ? $this->Session->read('MAIL_ERROR') : "''";

        $empty_password = count($this->User->find('list', array(
            'fields' => 'user_name',
            'conditions' => [
                'User.password' => null,
                'flag' => 1
            ]
        )));

        $role_name = $this->Role->find('all', array(
            'conditions' => array('Role.flag' => 1),
            'fields' => array('Role.id', 'Role.role_name')
        ));

        $layer_type = $this->LayerType->find('all', array(
            'fields' => array('Max(LayerType.type_order) as max_id'),
            'conditions' => array('LayerType.flag' => 1)
        ));
        $max_layertype = Setting::LAYER_TYPE_LIMIT;

        $layer_name = $this->Layer->find('all', array(
            'fields' => array('Layer.layer_code', 'Layer.name_en', 'Layer.name_jp'),
            'conditions' => array('Layer.flag' => 1, 'Layer.type_order' => $max_layertype, 'to_date >=' => date('Y-m-d'))
        ));
        $position = $this->Position->find('list', array(
            'fields' => array('position_code', 'position_name'),
            'conditions' => array(
                'Position.flag' => 1
            ),
            'group' => 'Position.position_name',
        ));
        if ($this->Session->read('Config.language') == 'eng') {

            $fields = array('layers.layer_code', 'layers.name_en', 'LayerType.type_order');
            $lTypeFields = array('LayerType.type_order', 'LayerType.name_en');
        } else {
            $fields = array('layers.layer_code', 'layers.name_jp', 'LayerType.type_order');
            $lTypeFields = array('LayerType.type_order', 'LayerType.name_jp');
        }
        $user_layer_type = $this->LayerType->find('list', array(
            'fields' => $lTypeFields,
            'conditions' => array(
                'LayerType.flag' => 1,
                'type_order <' => 5
            ),
        ));
        $date = date('Y-m-d');
        $user_layer_code = $this->LayerType->find('list', array(
            'fields' => $fields,
            'conditions' => array('LayerType.flag' => 1),
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'layers',
                    'type' => 'left',
                    'conditions' => array(
                        'LayerType.type_order = layers.type_order',
                        'layers.flag' => 1,
                        'layers.from_date <=' => $date,
                        'layers.to_date >=' => $date,
                    )
                )
            )
        ));

        $alertMsg = '';


        // YZLA - Get BA Flat Array Depend on Language
        if ($this->Session->read('Config.language') == 'eng') {
            $lang_name = 'en';
        } else {
            $lang_name = 'jp';
        }

        // $paginate
        try {
            $search_data = [];
            if(!empty($this->request->query)){
                $search_data['search_login_id'] = trim($this->request->query['login_id']);
                $search_data['search_user_name'] = trim($this->request->query['user_name']);
                $search_data['search_role'] = $this->request->query['role'];
                $search_data['search_email'] = trim($this->request->query['email']);
                if($search_data['search_role'] != ''){
                    $search_data['search_type_order'] = $this->request->query['type_order'];
                }
                if($search_data['search_type_order'] != ''){
                    $search_data['search_layer_code'] = $this->request->query['layer_code'];
                }
                if($search_data['search_layer_code'] != ''){
                    $search_data['search_layer_code'] = !empty($search_data['search_layer_code']) ? implode('/', array_unique($search_data['search_layer_code'])) : '';
                }
                $search_data['search_position'] = $this->request->query['position'];
                $search_data['search_joined_date'] = $this->request->query['joined_date'];
                $search_data['search_resigned_date'] = $this->request->query['resigned_date'];
            }
            $datas = $this->getPaginateUserDatas(Paging::TABLE_PAGING, $search_data);

            $query_count = $this->params['paging']['User']['count'];
            if (empty($layer_name)) {
                $check_array = "empty";
            } else {
                $check_array = "notempty";
            }
            $count = parent::getSuccessMsg('SS004', $query_count);
            $this->set('noDataMsg', parent::getErrorMsg('SE001'));
            $this->set(compact('search_data', 'role_name', 'layer_name', 'position', 'count', 'datas', 'query_count', 'check_array', 'alertMsg', 'user_layer_type', 'user_layer_code', 'session_mail_error', 'empty_password'));
            return $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            if(empty($this->request->query)){
                return $this->redirect(array('controller' => 'Users', 'action' => 'ResetPassword', 'param' => 'expire'));
            } else {
                $actual_link = $_SERVER["HTTP_REFERER"];
                $actual_link = explode('?', $actual_link);
                $first_part_link = explode('/', $actual_link[0]);
                $page_index = end(explode('/', $actual_link[0]));
                if($page_index != 'index'){
                    $get_page_no = explode(':', end(explode('/', $actual_link[0])));
                    $get_page_no[1] -= 1;
                    $get_page_no = implode(':', $get_page_no);
                    $max_arr_key = max(array_keys(explode('/', $actual_link[0])));
                    $first_part_link[$max_arr_key] = $get_page_no;
                    $actual_link[0] = implode('/', $first_part_link);
                    $actual_link = implode('?', $actual_link);

                    return $this->redirect($actual_link);
                } else {
                    return $this->redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
        return $this->render('index');
    }

    public function getPaginateUserDatas($limit, $search_data = null)
    {
        $condition['User.flag'] = 1;
        if ($search_data['search_login_id'] != "") {
            $condition['User.login_code  LIKE'] = "%{$search_data['search_login_id']}%";
        }
        if ($search_data['search_user_name'] != "") {
            $condition['User.user_name LIKE'] = "%{$search_data['search_user_name']}%";
        }
        if ($search_data['search_role'] != "") {
            $condition['User.role_id'] = $search_data['search_role'];
        }
        if ($search_data['search_email'] != "") {
            $condition['User.email LIKE'] = "%{$search_data['search_email']}%";
        }
        if ($search_data['search_type_order'] != "") {
            $condition['User.layer_type_order'] = $search_data['search_type_order'];
        }
        if ($search_data['search_layer_code'] != "") {
            $search_data['search_layer_code'] = explode('/', $search_data['search_layer_code']);
            foreach($search_data['search_layer_code'] as $key => $code){
                // search layer condition
                if(max(array_keys($search_data['search_layer_code'])) == 0) $condition['User.layer_code LIKE'] = '%' . $code . '%';
                else $condition['AND'][] = ['User.layer_code LIKE' => '%' . $code . '%'];
            }
        }
        if ($search_data['search_position'] != "") {
            $condition['User.position_code'] = $search_data['search_position'];
        }
        if ($search_data['search_joined_date'] != "") {
            $condition['User.joined_date'] = $search_data['search_joined_date'];
        }
        if ($search_data['search_resigned_date'] != "") {
            $condition['User.resigned_date'] = $search_data['search_resigned_date'];
        }

        $today = date('Y-m-d');
        $this->paginate  = array(
            'maxLimit' => $limit,
            'limit' => $limit,
            'conditions' => $condition,
            'joins' => array(
                array(
                    'table' => sprintf("(SELECT max(id) as max_id,flag from users where flag = 1 group by login_code) "),
                    'alias' => 'UserA',
                    'type'  =>  'inner',
                    'conditions' => array(
                        'UserA.max_id = User.id and UserA.flag = 1'
                    )
                ),
                array(
                    'table' => 'roles',
                    'alias' => 'role',
                    'type'  =>  'left',
                    'conditions' => array(
                        'role.id = User.role_id',
                    )
                ),
                array(
                    'table' => 'layers',
                    'alias' => 'layer',
                    'type'  =>  'left',
                    'conditions' => array(
                        'FIND_IN_SET(layer.layer_code,REPLACE(User.layer_code, "/", ","))',  /// Get Last BA Array Value -> To show If only one is choose
                        'layer.flag = 1',
                        'layer.from_date <= ' => $today,
                        'layer.to_date >= ' => $today,
                    ),
                ),
                array(
                    'table' => 'layer_types',
                    'alias' => 'layer_types',
                    'type'  => 'left',
                    'conditions' => array(
                        'layer_types.id = layer.layer_type_id',
                        'layer_types.flag = 1',
                    )
                ),
                array(
                    'table' => 'password_histories',
                    'alias' => 'pw',
                    'type'  => 'left',
                    'conditions' => array(
                        'pw.login_code = User.login_code',
                        'pw.status = 1',
                    )
                ),
                array(
                    'table' => sprintf("(SELECT login_code,count(login_code) as history_count from users where flag = 1 group by login_code) "),
                    'alias' => 'UserB',
                    'type'  =>  'left',
                    'conditions' => array(
                        'UserB.login_code = User.login_code'
                    )
                ),
                array(
                    'table' => 'positions',
                    'alias' => 'positions',
                    'type'     => 'left',
                    'conditions' => array(
                        'positions.position_code = User.position_code',
                        'positions.flag' => 1,
                    )
                ),
            ),
            'fields' => array(
                'User.*', 'role.role_name', 'layer.id', 'layer.layer_type_id', 'GROUP_CONCAT(layer.name_jp) as name_jp', 'GROUP_CONCAT(layer.name_en) as name_en', 'GROUP_CONCAT(layer.layer_code) as layer_code', 'positions.position_name', 'pw.created_date', 'UserB.*', 'layer_types.name_jp', 'layer_types.name_en'
            ),
            'group' => 'User.login_code',
            'order' => 'User.id desc'
        );
        $datas = $this->Paginator->paginate('User');
        return $datas;
    }
    /**
     *
     * getUser method
     * Aye Zar Ni Kyaw
     *
     */

    public function getUser()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);

        $id = $this->request->data['id'];

        $user = $this->User->UserEditData($id);

        $response = array(
            'id'                => $user[0]['users']['id'],
            'login_id'          => $user[0]['users']['login_code'],
            'user_name'         => $user[0]['users']['user_name'],
            'email'             => $user[0]['users']['email'],
            'azure_object_id'   => $user[0]['users']['azure_object_id'],
            'role_id'            => $user[0]['users']['role_id'],
            'role_name'            => $user[0]['roles']['role_name'],
            'layer_type_order'  => $user[0]['users']['layer_type_order'],
            'layer_code'        => $user[0]['users']['layer_code'],
            'position_code'     => $user[0]['users']['position_code'],
            'joined_date'       => (!empty($user[0]['users']['joined_date']) ? h(date("Y-m-d", strtotime($user[0]['users']['joined_date']))) : date('Y-m-d')),
            'resigned_date'     => (!empty($user[0]['users']['resigned_date'] && strpos($result['User']['resigned_date'], '9999') == false) ? h(date("Y-m-d", strtotime($user[0]['users']['resigned_date']))) : ''),
        );
        echo json_encode($response);
    }


    /**
     *
     * add method
     * Aye Zar Ni Kyaw
     * Edit by Nu Nu Lwin (2022/04/18)
     */
    public function add()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('post')) {
            // $dept_name = $this->request->data['department'];
            $role_id = $this->request->data['role'];
            // $role_id = $this->request->data['admin_level']; #Added by PanEiPhyo (20200313)
            $pageNo =  ($this->request->data['hid_page_no'] != 'add');
            $actual_link = $_SERVER["HTTP_REFERER"];


            $email = str_replace(' ', '', $this->request->data['email']);
            if (!empty($this->request->data['primary_id'])) {
                $condition = array(
                    'id !=' => $this->request->data['primary_id'],
                    'email != ' => $email,
                    'azure_object_id' => $this->request->data['azure_object_id'],
                    'flag' => 1
                );
            } else {
                #check azure obj id exit or not
                $condition = array(
                    'email !=' => $email,
                    'azure_object_id' => $this->request->data['azure_object_id'],
                    //'role_id' => $post_name,
                    'flag' => 1
                );
            }

            $checkDup = $this->User->find('list', array(
                'fields' => array('id'),
                'conditions' => $condition
            ));

            if (sizeof($checkDup) > 0 && $this->request->data['azure_object_id'] != '') {
                $pageNo =  $this->request->data['hid_page_no'];
                $errorMsg = parent::getErrorMsg('SE002', __("AzureオブジェクトID"));
                $this->Flash->set($errorMsg, array("key" => "Error"));
                return $this->redirect(array('controller' => 'Users', 'action' => 'index/' . $pageNo));
            }

            if (!empty($this->request->data['primary_id'])) { //update mode
                //data prepare
                $old_data = $this->User->find(
                    'all',
                    array(
                        'conditions' => array('OR' => array('id' => $this->request->data['primary_id'], 'login_code' => $this->request->data['login_id'],), 'flag' => 1),
                        'fields' => array('id', 'login_code', 'user_name', 'password', 'email', 'azure_object_id', 'role_id', 'layer_code', 'position_code', 'joined_date', 'resigned_date', 'flag'),
                        'order' => 'id DESC'
                    )
                );
                $id_flag = !empty($old_data[0]['User']);
                if ($id_flag == 1) {
                    // update user data
                    if (!empty($old_data[1]['User'])) { #before latest row
                        # change condition into '>' from '>='
                        if (strtotime($old_data[1]['User']['resigned_date']) > strtotime($this->request->data['joined_date'])) {
                            $errorMsg = parent::getErrorMsg('SE150');
                            $this->Flash->set($errorMsg, array("key" => "Error"));
                            return $this->redirect($actual_link);
                        }
                    }
                    //echo $layercode = implode('/', array_unique($this->request->data['update_layer_code']));
                    $layercode = str_replace(',', '/', $this->request->data['update_layer_code']);

                    #new row
                    $result = array(
                        'id'            => $this->request->data['primary_id'],
                        'login_code'    => trim($this->request->data['login_id'], " "),
                        'user_name'     => trim($this->request->data['user_name'], " "),
                        'email'         =>  trim($email, " "),
                        'azure_object_id'  => trim($this->request->data['azure_object_id'], " "),
                        'role_id'       => $role_id,
                        'layer_type_order'  => $this->request->data['type_order'],
                        'layer_code'    => $layercode,
                        'position_code'        => $this->request->data['position'],
                        'joined_date'   => date("Y-m-d H:i:s", strtotime($this->request->data['joined_date'])),
                        'resigned_date' => (date("Y-m-d", strtotime($this->request->data['resigned_date'])) == '1970-01-01') ? '9999-12-31 00:00:00' : date("Y-m-d H:i:s", strtotime($this->request->data['resigned_date'])),
                        'flag' => 1,
                        'updated_by'    => $this->Session->read('LOGIN_ID'),
                        'updated_date'  => date("Y-m-d H:i:s")
                        // 'access_type'   => $permission,

                    );


                    $Common = new CommonController(); #To import CommonController
                    $result = $Common->saveUserHistory($old_data, $result);

                    #change $role_id into $post_name by HHK
                    $pageNo =  $this->request->data['hid_page_no'];
                    $this->User->saveAll($result);
                    $successMsg = parent::getSuccessMsg('SS002');
                    $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                    return $this->redirect($actual_link);
                } else {
                    $pageNo =  $this->request->data['hid_page_no'];
                    $errorMsg = parent::getErrorMsg('SE037');
                    $this->Flash->set($errorMsg, array("key" => "Error"));
                    return $this->redirect($actual_link);
                }
            } else {
                // add (Save)
                $user_query = $this->User->find('all', array('fields' => 'login_code,flag'));

                //index function of query
                $position_name = $this->Role->find('list', array(
                    'conditions' => array('flag' => 1),
                    'fields' => 'id,role_name'
                ));
                // $position_name = $this->Position->find('all', array('fields' => 'position_name,position_id'));
                // $department_name = $this->DepartmentModel->find('all', array('fields' => ' department_name,department_id'));
                $this->paginate  = array(
                    'limit' => Paging::TABLE_PAGING,
                    'conditions' => array('User.flag' => 1),
                    'joins' => array(
                        array(
                            'table' => 'roles',
                            'alias' => 'role',
                            'type'  =>  'left',
                            'conditions' => array(
                                'role.id = User.role_id'
                            )
                        ),
                        array(
                            'table' => 'layers',
                            'alias' => 'ba',
                            'type'  =>  'left',
                            'conditions' => array(
                                'ba.id = SUBSTRING_INDEX(User.layer_code, ",", -1)', /// Get Last BA Array Value -> To show If only one is choose
                                // 'ba.to_date >= date("Y-m-d")',
                                'ba.flag = 1'
                            )
                        ),
                    ),
                    'fields' => array(
                        'User.*', 'role.role_name', 'ba.name_jp', 'ba.name_en', 'ba.id', 'ba.id', 'ba.layer_code'
                    ),
                    'order' => 'User.login_code ASC',
                    'group' => 'login_code'
                );

                $datas = $this->Paginator->paginate('User');

                $query_count = $this->params['paging']['User']['count'];
                $count = parent::getSuccessMsg('SS004', $query_count);
                //end index function of query

                $loginIdCapital = ucfirst($this->request->data['login_id']);
                $loginIdSmall = lcfirst($this->request->data['login_id']);


                foreach ($user_query as $value) {
                    if (($value['User']['login_code'] == $loginIdCapital && $value['User']['flag'] == 1) || ($value['User']['login_code'] == $loginIdSmall && $value['User']['flag'] == 1)) {

                        $errorMsg = parent::getErrorMsg('SE002', __("ユーザID"));
                        $this->Flash->set($errorMsg, array("key" => "Error"));
                        // request data
                        $request = $this->request->data;

                        $this->set(compact('request', 'position_name', 'user', 'count', 'datas'));;
                        return $this->redirect($actual_link);
                    }
                }

                $login_id = trim($this->request->data['login_id'], " ");
                // $joined_date = $this->request->data['joined_date'];
                // $resigned_date = $this->request->data['resigned_date'];

                $joined = date("Y-m-d", strtotime($this->request->data['joined_date']));
                $resigned =  date("Y-m-d", strtotime($this->request->data['resigned_date']));
                #change default value
                if ($resigned == '1970-01-01') {
                    $resigned = '9999-12-31';
                }
                $layercode = implode('/', array_unique($this->request->data['layer_code']));
                $result = array(
                    'login_code'        => $login_id,
                    'user_name'         => $this->request->data['user_name'],
                    'password'          => '',
                    'email'             => $this->request->data['email'],
                    'azure_object_id'   => $this->request->data['azure_object_id'],
                    'role_id'           => $role_id,
                    'layer_type_order'  => $this->request->data['type_order'],
                    'layer_code'        => $layercode,
                    'position_code'        => $this->request->data['position'],
                    'joined_date'       => $joined,
                    'resigned_date'     => $resigned,
                    'flag'              => 1,
                    'created_date'      => date("Y-m-d H:i:s"),
                    'updated_date'      => date("Y-m-d H:i:s"),
                    'created_by'    => $this->Session->read('LOGIN_ID'),
                    'updated_by'    => $this->Session->read('LOGIN_ID')
                );
                if ($this->User->save($result)) {

                    $successMsg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($successMsg, array("key" => "UserSuccess"));

                    return $this->redirect($actual_link);
                }
            }
        }
    }
    /**
     * Nu Nu Lwin (18/04/2022)
     **/
    public function generatePassword($length = 12)
    {
        // $chars = '23456789bcdfhkmnprstvzBCDFHJKLMNPRSTVZ';
        $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-!$%^&*()_+|~=`{}\[\]:\/;<>?,.@#';
        $count = 0;
        while ($count < $length) {
            $shuffled = str_shuffle($chars);
            $count = strlen($shuffled);
        }
        $result = mb_substr($shuffled, 0, $length);
        if (!preg_match('/[A-Z]/', $result)) {
            $result = $result . 'H';
        }
        if (!preg_match('/[a-z]/', $result)) {
            $result = $result . 'k';
        }
        if (!preg_match('/[0-9]/', $result)) {
            $result = $result . '2';
        }
        if (!preg_match('/[-!$%^&*()_+|~=`{}\[\]:\/;<>?,.@#]/', $result)) {
            $result = $result . '#';
        }
        return $result;
    }
    /**
     *
     * delete method
     * Aye Zar Ni Kyaw
     */
    public function delete()
    {
        $id_flag = $this->User->find(
            'first',
            array(
                'conditions' => array('id' => $this->request->data['id']),
                'fields' => array('flag', 'login_code')
            )
        );

        $rows_per_page =  $this->request->data['rows_per_page'];
        $search_user = $this->request->data['search_user'];
        $actual_link = $_SERVER["HTTP_REFERER"];

        if ($id_flag['User']['flag'] == 1) {
            try{
                #update all rows to flag 0
                $this->User->updateAll(
                    array(
                        'flag' => 0,
                        'updated_date'  => '"' . date("Y-m-d H:i:s") . '"',
                        'updated_by'    => $this->Session->read('LOGIN_ID')
                    ),
                    array("login_code"    =>  $id_flag['User']['login_code'])
                );
                $this->PasswordHistory->deleteAll(['login_code' => $id_flag['User']['login_code']]);

                $successMsg = parent::getSuccessMsg('SS003');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));

                if($rows_per_page == 1){
                    // delete the last 1 row in last page in pagination
                    if($search_user == ''){
                        // no search
                        $exploded_actual_link = explode('/', $actual_link);
                        $max_arr_key = max(array_keys($exploded_actual_link));
                        $actual_page = explode(':', end($exploded_actual_link));
                        if($actual_page[0] == 'page'){
                            $prev_page_no = explode(':', end($exploded_actual_link))[1] - 1;
                            $actual_page[1] = $prev_page_no;
                            $actual_page = implode(':', $actual_page);
                            $exploded_actual_link[$max_arr_key] = $actual_page;    
                            $actual_link = implode('/', $exploded_actual_link);
                        }
                    } else {
                        // it is in search condition
                        $exploded_actual_link = explode('/', $actual_link);
                        $max_arr_key = max(array_keys($exploded_actual_link));
                        $current_page = explode('?', $exploded_actual_link[$max_arr_key]);
                        if($current_page[0] != 'index'){
                            # delete the search result is not one row only
                            $current_page_no = explode(':', $current_page[0]);
                            $prev_page = explode(':', $current_page_no[1] - 1);
                            $current_page_no[1] = $prev_page[0];
                            $current_page_no = implode(':', $current_page_no);
                            $current_page[0] = $current_page_no;
                            $actual_page = implode('?', $current_page);
                            $exploded_actual_link[$max_arr_key] = $actual_page;
                            $actual_link = implode('/', $exploded_actual_link);
                        }
                    }
                }
                return $this->redirect($actual_link);
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $errorMsg = parent::getErrorMsg('SE007');
                $this->Flash->set($errorMsg, array("key" => "Error"));
                return $this->redirect($actual_link);
            }
        } else {
            $errorMsg = parent::getErrorMsg('SE037');
            $this->Flash->set($errorMsg, array("key" => "Error"));
            return $this->redirect($actual_link);
        }
    }

    /**
     *
     * ResetPassword method
     * Aye Zar Ni Kyaw
     * Edited by NU NU LWIN (29/03/2022)
     */

    public function ResetPassword()
    {
        $layout = 'master';
        $this->layout = $layout;
        $reset_condition = '';

        $login_id = $this->Session->read('LOGINID'); #get LOGINID while login fail 5times (just login_id eg: AC000)

        if (!empty($this->request->data['id'])) { #user master table's rows

            $id = $this->request->data['id'];
            $reset_condition = 'user_master_rows';
        } else if ($this->Session->read('LOGIN_ID')) { #Password expired or reset from menu page.

            $id = $this->Session->read('LOGIN_ID');
            $reset_condition = 'expired_or_menulink';
        } else { #Login fail 5times condition

            $reset_condition = '5times';
        }


        $url_text = $this->request->here();
        $url_explode = explode('?', $url_text);
        $expire_url = (isset($url_explode['1'])) ? $url_explode['1'] : '';

        $data = $this->User->find('first', array(
            'joins' => array(
                array(
                    'table' => 'password_histories',
                    'alias' => 'Pw_His',
                    'type' => 'INNER',
                    'conditions' => array(
                        'User.login_code = Pw_His.login_code'
                    )
                )
            ),
            'conditions' => array(
                'User.login_code' => $login_id,
                'User.flag !=' => '0',
                'Pw_His.status' => '1',
                'Pw_His.expire_date <=' => date("Y-m-d")
            ),
            'fields' => array('Pw_His.*', 'User.*')
        ));
        $expire = (!empty($expire_url)) ? ("パスワードの有効期限が切れました。新しいパスワードを設定して下さい。") : ((!empty($data)) ? ("ログインに5回パスワード間違いが発生したためパスワードをリセットする必要があります。") : '');
        if ($reset_condition == '5times') {
            $id =  $data['User']['id'];
        }
        if (empty($id)) {
            return $this->redirect(array('action' => 'index'));
        } else {
            $this->set(compact('expire', 'id', 'reset_condition'));
            $this->render('passwordreset');
        }
    }


    /**
     *
     * ForgotComfirm method
     * Login forgot password link
     * Write by NU NU LWIN (22/04/2022)
     */

    public function ForgotConfirm()
    {
        $this->layout = 'master';
        $this->render('forgotconfirm');
    }

    /**
     *
     * ResetPasswordUpdate method
     * Aye Zar Ni Kyaw
     * Edit By NU Nu Lwin (2022/04/05)
     */
    public function ResetPasswordUpdate()
    {
        $this->layout = 'master';
        $new_password = $this->request->data['password'];
        $new_pw       = md5($new_password);

        $conditions = array();

        #to active cake session
        $this->Session->check('Cofig.language');

        if (!empty($this->request->data['id']) && $this->request->data['id'] != 'undefined') {
            $id = $this->request->data['id'];
            $conditions["User.id"] = $id;
        } else {
            return $this->redirect(array('controller' => 'Users', 'action' => 'index'));
        }

        $conditions["User.flag !="] = 0;

        $user = $this->User->find(
            'first',
            array(
                'conditions' => $conditions,
                'fields'  => 'id,user_name,password,login_code,email'
            )
        );

        if (empty($user)) {
            $errorMsg = parent::getErrorMsg('SE121');
            $this->Flash->set($errorMsg, array("key" => "PasswordError"));
        } else  if ($this->request->is('post')) { //password valiation

            if ($new_pw  == $user['User']['password']) {
                $errorMsg = parent::getErrorMsg('SE002', __('パスワード'));
                $this->Flash->set($errorMsg, array("key" => "PasswordError"));
            } else {
                $UserDB  = $this->User->getDataSource();
                $PwHisDB = $this->PasswordHistory->getDataSource();

                try {
                    $UserDB->begin();
                    $PwHisDB->begin();
                    $user_login = $user['User']['login_code'];
                    $expire_date = Date('Y-m-d', strtotime('+89 days'));

                    $password_history = array(
                        'login_code'                  =>  $user_login,
                        'user_name'                 =>  $user['User']['user_name'],
                        'old_password'              =>  $user['User']['password'],
                        'expire_date'               =>  $expire_date,
                        'status'                    => '1'
                    );
                    $this->PasswordHistory->updateAll(
                        array('status' => '0'),
                        array('login_code' => $user_login)
                    );

                    $this->PasswordHistory->create();
                    $this->PasswordHistory->save($password_history);

                    $result = array(
                        'id' => $id,
                        'password' => $new_pw,
                        'updated_date' => date("Y-m-d H:i:s")
                    );

                    if ($this->User->save($result)) {

                        $successMsg = parent::getSuccessMsg('SS002');
                        $this->Flash->set($successMsg, array("key" => "PasswordSuccess"));
                    }

                    $UserDB->commit();
                    $PwHisDB->commit();
                } catch (Exception $e) {
                    $UserDB->rollback();
                    $PwHisDB->rollback();

                    $errorMsg = parent::getErrorMsg('SE003');
                    $this->Flash->set($errorMsg, array("key" => "PasswordError"));
                    CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    return $this->redirect(array('controller' => 'Users', 'action' => 'index'));
                }
            }
            $reset_condition = $this->request->data['reset_condition'];
            $this->set(compact('reset_condition', 'id'));
            $this->render('passwordreset');
        }
    }

    /**
     *
     * forgot password confirm method
     * Edit By NU Nu Lwin (2022/04/24)
     */
    public function forgotConfirmPW()
    {
        $this->layout = 'master';
        $login_id = $this->request->data['login'];
        $email = $this->request->data['email'];
        $conditions = array();
        $conditions["User.login_code"] = $login_id;
        $conditions["User.flag !="] = 0;


        $user = $this->User->find(
            'first',
            array(
                'conditions' => $conditions,
                'fields'  => 'id,email'
            )
        );

        if (empty($user)) {
            $errorMsg = parent::getErrorMsg('SE121');
            $this->Flash->set($errorMsg, array("key" => "forgotConfirmError"));
            return $this->ForgotConfirm();
        }
        $id = $user['User']['id'];
        $to_email = $user['User']['email'];


        if (!empty($to_email)) {
            if ($to_email != $email) {
                $errorMsg = parent::getErrorMsg('SE122');
                $this->Flash->set($errorMsg, array("key" => "forgotConfirmError"));
                return $this->ForgotConfirm();
            }
        } else {
            $to_email = $email;
        }
        $id_email['id'] = $id;
        $id_email['to_email'] = $to_email;
        $this->set('id', $id);
        $this->render('passwordreset');
    }
    /**
     * Reset Mail Sent
     *
     * @author Nu Nu Lwin (20220315)
     * @throws NotFoundException
     * @return void
     */
    public function createMailSend($password, $loginId, $to_email, $user_name, $layer_code = null, $dept_name = null, $role_name = null)
    {
        $get_admin_email = $this->User->find(
            'list',
            array(
                'conditions' => array(
                    'role_id' => '1',
                    'id !=' => '0',
                    'email !=' => '',
                    'flag' => '1'
                ),
                'fields'  => 'email',
                'group'   => 'email'
            )
        );
        // $url = $this->getAddress();
        $base_url = Router::url('/', true);

        //$user_info = (!empty($layer_code))? $layer_code.'の'.$dept_name.''.$post_name : $dept_name.''.$post_name;

        $subject = "【 $loginId 】【経理クラウドシステム】ユーザー登録完了のお知らせ";

        $sub_body = "経理クラウドシステムへのユーザー登録が完了致しましたので、連絡致します。<br/><br/>
            下記にログインIDとパスワードをお知らせ致しますので、ログイン後、正常にログインが可能かご確認ください。<br/>
            また、下記パスワードは初期設定パスワードになる為、お手数ですが、メインメニューの「パスワードリセット」から、パスワードの変更をお願い致します。<br/><br/>

            ログイン画面URL： <a href = '$base_url'>" . $base_url . "</a><br/>
            ログインID： " . $loginId . "<br/>
            パスワード： " . h($password);


        $mail_template          = 'common';
        #Mail contents
        $mail['subject']        = $subject;
        $mail['template_title'] = $user_name . '様';
        $mail['template_body']  = $sub_body;

        $emailAddres['toEmail'] = $to_email;
        $emailAddres['ccEmail'] = $get_admin_email;
        // debug($emailAddres);exit;
        $sentMail = parent::sendEmailFileAttach($mail_template, $mail, $emailAddres, $url = '');

        if (!$sentMail['error']) {
            CakeLog::write('email', 'Mail Successed   ログインID：  ' . $loginId . '   パスワード：  ' . $password);
        } else {
            CakeLog::write('email', 'Mail Failed   ログインID：  ' . $loginId . '   パスワード：  ' . $password);
        }
        return $sentMail;
    }

    public function getAddress()
    {
        $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Temporary Function for Upload New Password excel file 
     * Upload New Password excel file
     *
     * @author Nu Nu Lwin (20220315)
     * @throws NotFoundException
     * @return void
     */
    public function UserPWImport()
    {
        App::import('Vendor', 'php-excel-reader/PHPExcel'); # excel

        if ($this->request->is('post')) {
            $login_id = $this->Session->read('LOGIN_ID'); # get login id
            $file = $this->request->params['form']['uploadfile']; # get name, type, tmp_name, error, size of file
            $uploadPath = APP . 'tmp'; # file path
            $date = date('Y-m-d H:i:s'); # for updated id and created id

            if (!empty($file)) {
                $file_type  =   $file['type'];
                $file_name  =   $file['name'];
                $file_loc   =   $file['tmp_name'];
                $file_error =   $file['error'];
                $file_size  =   $file['size'];

                if ($file_error == 0) {
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    if ($ext == "xlsx" || $ext == "xls" || $ext == "XLSX" || $ext == "XLS") {
                        # access file size is 1 Megabytes (MB)
                        if ($file['size'] <= 10485760) {
                            #for excel
                            $fileName = "temporary_user_pw." . $ext;
                            $tempPath = $uploadPath . DS . $fileName;
                            if (move_uploaded_file($file_loc, $tempPath)) {
                                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                                $objReader->setReadDataOnly(true);
                                if ($objReader->canRead($tempPath)) {
                                    $objPHPExcel   = $objReader->load($tempPath);
                                    $objWorksheet  = $objPHPExcel->getActiveSheet();
                                    $highestRow    = $objWorksheet->getHighestRow();

                                    if ($highestRow == 1) {
                                        $highestRow = $highestRow + 1;
                                    }

                                    if (
                                        ltrim($objWorksheet->getCell('A1')->getValue()) == ("ログインID")
                                        && ltrim($objWorksheet->getCell('B1')->getValue()) == ("ユーザー名")
                                        && ltrim($objWorksheet->getCell('C1')->getValue()) == ("role_id")
                                        && ltrim($objWorksheet->getCell('D1')->getValue()) == ("メール")
                                        && ltrim($objWorksheet->getCell('E1')->getValue()) == ("BAコード")
                                        // && ltrim($objWorksheet->getCell('F1')->getValue()) == ("access_type")

                                    ) {
                                        for ($row = 2; $row <= $highestRow; $row++) {
                                            $rowData = $objWorksheet->rangeToArray('A' . $row . ':' . 'F' . $row, null, true, false);
                                            $worksheets[] = $rowData[0];
                                        }

                                        $save_user_data = [];

                                        foreach ($worksheets as $key => $value) {
                                            $org_password = $this->generatePassword();
                                            $password = md5($org_password);
                                            $save_user_data[] = array(
                                                'login_code' => $value['0'],
                                                'user_name' => $value['1'],
                                                'password' => $password,
                                                'org_password' => $org_password,
                                                'role_id' => $value['2'],
                                                'email' => $value['3'],
                                                'layer_code' => $value['4'],
                                                // 'access_type' => $value['5'],
                                                'flag' => '1',
                                                'created_by' => $login_id,
                                                'updated_by' => $login_id,
                                                'created_date' => $date
                                            );
                                        }

                                        $UserDB = $this->User->getDataSource();
                                        try {
                                            $UserDB->begin();

                                            $this->User->saveAll($save_user_data);

                                            $UserDB->commit();

                                            $successMsg = parent::getSuccessMsg('SS002');
                                            $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                                        } catch (Exception $e) {
                                            $UserDB->rollback();
                                            $PwHisDB->rollback();

                                            $errorMsg = parent::getErrorMsg('SE003');
                                            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                        }
                                    } else {
                                        $errorMsg = parent::getErrorMsg('SE022');
                                        $this->Flash->set($errorMsg, array("key" => "Error"));
                                    }
                                }
                            }
                        } else {
                            $errorMsg = parent::getErrorMsg('SE014');
                            $this->Flash->set($errorMsg, array("key" => "Error"));
                        }
                    } else {
                        $errorMsg = parent::getErrorMsg('SE013', $ext);
                        $this->Flash->set($errorMsg, array("key" => "Error"));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE015');
                    $this->Flash->set($errorMsg, array("key" => "Error"));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array("key" => "Error"));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Add auto granate password and send mail
     * From index.ctp , reset icon link
     * @author Nu Nu Lwin (20220502)
     * @throws NotFoundException
     * @return void
     */
    public function CreatePW_MailSend()
    {
        if (!empty($this->request->data['id'])) {
            $id     = $this->request->data['id'];
            $pageNo =  $this->request->data['hid_page_no'];
        } else {
            return $this->redirect(array('controller' => 'Users', 'action' => 'index'));
        }
        $UserDB  = $this->User->getDataSource();
        $PwHisDB = $this->PasswordHistory->getDataSource();
        try {
            $UserDB->begin();
            $PwHisDB->begin();

            $random_pass = $this->generatePassword();
            $password = md5($random_pass);
            $dept_name = null;
            $user_data = $this->User->find(
                'first',
                array(
                    'conditions' => array('id' => $id)
                )
            );
            $login_code       = $user_data['User']['login_code'];
            $user_name      = $user_data['User']['user_name'];
            $role_id = $user_data['User']['role_id'];
            $role_name = $user_data['User']['role_name'];
            $email          = $user_data['User']['email'];
            $layer_code        = $user_data['User']['layer_code'];
            $old_pw         = $user_data['User']['password'];

            // $get_pos_name = $this->AdminLevelModel->find('first',
            //                     array('conditions' => array('AdminLevelModel.flag' => 1,
            //                                         'AdminLevelModel.role_id'=> $role_id),
            //                         'fields' => array('position_name','department_name'))
            //                     );
            $get_role_name = $this->Role->find(
                'first',
                array(
                    'conditions' => array(
                        'Role.flag' => 1,
                        'Role.id' => $role_id
                    ),
                    'fields' => array('role_name')
                )
            );

            $role_name  = $get_role_name['Role']['role_name'];
            // $dept_name = $get_pos_name['AdminLevelModel']['department_name'];
            // update
            $save_pw = array(
                'id'            => $id,
                'password'      => $password,
                'updated_by'    => $this->Session->read('LOGIN_ID'),
                'updated_date'  => date("Y-m-d H:i:s")

            );
            $this->User->save($save_pw);
            $expire_date = Date('Y-m-d', strtotime('+89 days'));

            $password_history = array(
                'login_code'                =>  $login_code,
                'user_name'                 =>  $user_name,
                'old_password'              =>  $old_pw,
                'expire_date'               =>  $expire_date,
                'status'                    =>  '1'
            );
            $this->PasswordHistory->updateAll(
                array('status' => '0'),
                array('login_code' => $login_code)
            );
            $this->PasswordHistory->create();
            $this->PasswordHistory->save($password_history);

            $sentMail = $this->createMailSend($random_pass, $login_code, $email, $user_name, $layer_code, $dept_name, $role_name);

            if ($sentMail["error"]) {
                $msg = $sentMail["errormsg"];
                $invalid_email = parent::getErrorMsg('SE042');
                $this->Flash->set($invalid_email, array('key' => 'Error'));
                return $this->redirect(array('controller' => 'Users', 'action' => 'index/' . $pageNo));
            } else {
                $UserDB->commit();
                $PwHisDB->commit();
                $successMsg = parent::getSuccessMsg('SS029');
                $this->Flash->set($successMsg, array("key" => "UserSuccess"));
                return $this->redirect(array('controller' => 'Users', 'action' => 'index/' . $pageNo));
            }
        } catch (Exception $e) {
            $UserDB->rollback();
            $PwHisDB->rollback();

            $errorMsg = parent::getErrorMsg('SE003');
            $this->Flash->set($errorMsg, array("key" => "Error"));
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            return $this->redirect(array('controller' => 'Users', 'action' => 'index/' . $pageNo));
        }
    }

    /**
     * Temporary Function auto granate password and send mail
     *
     * @author Pan Ei Phyo (20220502)
     * @throws NotFoundException
     * @return void
     */
    public function BulkCreatePW()
    {
        $UserDB  = $this->User->getDataSource();
        $PasswordHistoryDB  = $this->PasswordHistory->getDataSource();
        $UserDB->begin();
        $PasswordHistoryDB->begin();
        $mail_error = [];

        $users = $this->getPaginateUserDatas('');
        if (!empty($users)) {
            $count_mail = 0;
            foreach ($users as $user_data) {
                $id   = $user_data['User']['id'];
                $login_id   = $user_data['User']['login_code'];
                $user_name  = $user_data['User']['user_name'];
                $role_id = $user_data['User']['role_id'];
                $email      = $user_data['User']['email'];
                $layer_code    = $user_data['User']['layer_code'];
                $old_password         = $user_data['User']['password'];
                if ($login_id != "ADMIN" && empty($old_password)) {
                    try {
                        $random_pass = $this->generatePassword();
                        $password = md5($random_pass);

                        // update
                        $save_pw = array(
                            'id'            => $id,
                            'password'      => $password,
                            'updated_by'    => $this->Session->read('LOGIN_ID'),
                            'updated_date'  => date("Y-m-d H:i:s")
                        );

                        $this->User->save($save_pw);
                        $expire_date = Date('Y-m-d', strtotime('+89 days'));

                        $password_history = array(
                            'login_code'                =>  $login_id,
                            'user_name'                 =>  $user_name,
                            'old_password'              =>  $old_password,
                            'expire_date'               =>  $expire_date,
                            'status'                    =>  '1'
                        );

                        $this->PasswordHistory->updateAll(
                            array('status' => '0'),
                            array('login_code' => $login_id)
                        );

                        $this->PasswordHistory->create();
                        $this->PasswordHistory->save($password_history);
                        $sentMail = $this->createMailSend($random_pass, $login_id, $email, $user_name, $layer_code, $dept_name, $role_id);
                        $count_mail++;
                        if ($count_mail == Setting::MAIL_SEND_LIMIT) {
                            $count_mail = 0;
                            sleep(Setting::SLEEP_TIME);
                        }
                        if ($sentMail["error"]) {
                            $mail_error_msg = parent::getErrorMsg('SE152', __($login_id));
                            array_push($mail_error, $mail_error_msg);
                            $UserDB->rollback();
                            $PasswordHistoryDB->rollback();
                        } else {
                            $UserDB->commit();
                            $PasswordHistoryDB->commit();
                        }
                    } catch (Exception $e) {
                        $UserDB->rollback();
                        $PasswordHistoryDB->rollback();

                        $errorMsg = parent::getErrorMsg('SE003');
                        $this->Flash->set($errorMsg, array("key" => "Error"));
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                        return $this->redirect(array('controller' => 'Users', 'action' => 'index/' . $pageNo));
                    }
                }
            }
        } else {
            $errorMsg = 'There is no user to save password!';
            $this->Flash->set($errorMsg, array("key" => "Error"));
        }
        if (!empty($mail_error)) {
            $this->Session->write('MAIL_ERROR', $mail_error);
            // pr($this->Session->read("MAIL_ERROR"));exit;
        } else {
            $this->Session->write('MAIL_ERROR', '');
        }
        $successMsg = parent::getSuccessMsg('SS029');
        $this->Flash->set($successMsg, array("key" => "UserSuccess"));
        return $this->redirect(array('controller' => 'Users', 'action' => 'index'));
    }

    /* mail sent error */
    public function MailSentError()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $session_mail_error = $this->Session->read('MAIL_ERROR');
        $this->Session->write("MAIL_ERROR", "");

        echo json_encode($session_mail_error);
    }

    /**
     * To get User History Data function
     *
     * @author Hein Htet Ko (20230616)
     * @param id
     * @return json data
     */
    public function getUserHistoryData()
    {
        #only allow ajax request
        $this->request->allowMethod('ajax');
        $this->autoRender = false;
        #user id
        $user_id = $this->request->data('id');

        #get language session
        $language = ($this->Session->read('Config.language')) ?? 'jpn';
        $layer = ($language == 'jpn') ? 'name_jp' : 'name_en';

        #today
        $today = date('Y-m-d');
        #get login code by id 
        $his_rows = $this->User->find('all', array(
            'fields' => array('UserClone.*', 'LayerTypeN.' . $layer . ' as layer_type_name', 'GROUP_CONCAT(distinct LayerN.' . $layer . ' SEPARATOR ", ") as layer_name'),
            'conditions' => array(
                'User.flag' => 1,
                'User.id' => $user_id,
            ),
            'joins' => array(
                array(
                    'table' => 'users',
                    'alias' => 'UserClone',
                    'type'  => 'LEFT',
                    'conditions' => array(
                        'User.login_code = UserClone.login_code',
                    ),
                ),
                array(
                    'table' => 'layers',
                    'alias' => 'LayerN',
                    'type'  => 'left',
                    'conditions' => array(
                        'FIND_IN_SET(LayerN.layer_code,REPLACE(UserClone.layer_code, "/", ","))',
                        'LayerN.flag = 1',
                        'LayerN.from_date <= ' => $today,
                        'LayerN.to_date >= ' => $today,
                    ),
                ),
                array(
                    'table' => 'layer_types',
                    'alias' => 'LayerTypeN',
                    'type'  => 'left',
                    'conditions' => array(
                        'LayerTypeN.id = LayerN.layer_type_id',
                        'LayerTypeN.flag = 1'
                    ),
                ),

            ),
            'group' => 'UserClone.id'
        ));

        echo json_encode($his_rows);
    }
    public function getLayerTypes()
    {
        #only allow ajax request
        $this->request->allowMethod('ajax');
        $this->autoRender = false;
        #user id
        $role_id = $this->request->data['id'];
        $layer_type_limit = Setting::LAYER_TYPE_LIMIT;
        // #get language session
        $language = ($this->Session->read('Config.language')) ?? 'jpn';
        $layer = ($language == 'jpn') ? 'name_jp' : 'name_en';

        $read_limit = array_values(
            $this->Role->find('list', array(
                'fields' => 'read_limit',
                'conditions' => array('Role.id' => $role_id, 'Role.flag' => 1)
            ))
        );
        $layer_type_list = $this->LayerType->find('list', array(
            'limit' => $layer_type_limit,
            'fields' => $layer,
            'conditions' => array(
                'type_order BETWEEN ? AND ?' => array($read_limit, $layer_type_limit),
                'flag' => 1
            ),
        ));

        echo json_encode($layer_type_list);
    }
}
