<?php

use Zend\Validator\InArray;

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Roles Controller
 *
 * @property Role $Role
 * @property PaginatorComponent $Paginator
 */
class RolesController extends AppController
{

     /**
      * Components
      *
      * @var array
      */
     public $helpers = array('Html', 'Form', 'Session');
     public $uses = array('User', 'Role', 'Menu', 'LayerType', 'Permission', 'MailReceiver');
     public $components = array('Session', 'Flash', 'Paginator');
     public function beforeFilter()
     {
          parent::CheckSession();
          parent::checkUserStatus(); 
          parent::checkSettingSession($this->name);

          // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
          // 	$this->redirect(array('controller' => 'Login', 'action' => 'logout'));
          // }
     }


     /**
      * index method
      *
      * @return void
      */
     public function index()
     {
          $this->Role->recursive = 0;
          // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
          // 	$this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
          // }
          $this->Session->write('LAYOUT', 'mastermanagement');
          $this->layout = 'mastermanagement';
          parent::CheckSession();
          parent::checkUserStatus();
          parent::checkExpiredUser();
          $language   = $this->Session->read('Config.language');
          $role       = !empty($this->request->query('s_role')) ? $this->request->query('s_role') : '';
          $menu       = !empty($this->request->query('s_menu')) ? $this->request->query('s_menu') : '';
          $pg_name    = !empty($this->request->query('s_page')) ? $this->request->query('s_page') : '';
          $menu_name      = ($language == 'eng') ? ['menu_name_en'] : ['menu_name_jp'];
          $page_name      = ($language == 'eng') ? ['page_name'] : ['page_name_jp'];
          $condition_lng          = ($language == 'eng') ? ['Menu.flag' => "1", "Menu.menu_name_en" => $menu] : ['Menu.flag' => "1", "Menu.menu_name_jp" => $menu];
          $conditions = array();
          $page_conditions = array();
          try {
               if ($role != null) {
                    $conditions["Role.id"] = $role;
               }
               if ($menu != null) {
                    if ($language == 'eng') {
                         $conditions["Menu.menu_name_en"] = $menu;
                    } else {
                         $conditions["Menu.menu_name_jp"] = $menu;
                    }
               }
               if ($pg_name != null) {
                    if ($language == 'eng') $conditions["Menu.page_name"] = $pg_name;
                    else $conditions["Menu.page_name_jp"] = $pg_name;
               }

               #get menu name
               $this->Menu->virtualFields['menu_name'] = ($language == 'eng') ? 'menu_name_en' : 'menu_name_jp';
               $phase      = $this->Menu->find("list", array(
                    'fields' => array('id', 'menu_name'),
                    'conditions' => array('flag' => 1),
                    'group'  => array('menu_name'),
                    'order'      => 'id'
               ));
               if ($language != 'eng') {
                    $button_type  = Setting::BUTTONS_JP;
               } else {
                    $button_type  = Setting::BUTTONS;
               }
               array_shift($button_type);

               $admin_levels = $this->Role->find("list", array(
                    'fields' => array('id', 'role_name'),
                    'conditions' => array('flag' => 1)
               ));
               $role_list = $this->Role->find('list', array(
                    'fields'     => array('id', 'role_name'),
                    'joins' => array(
                         array(
                              'table' => 'permissions',
                              'alias' => 'Permission',
                              'type'  => 'LEFT',
                              'conditions' => array(
                                   'Role.id = Permission.role_id',
                              )
                         ),
                         array(
                              'table' => 'menus',
                              'alias' => 'Menu',
                              'type'  => 'LEFT',
                              'conditions' => array(
                                   'Menu.id = Permission.menu_id',
                                   'Menu.flag' => 1,
                              )
                         ),
                    ),
                    'conditions' => array('Role.flag' => 1, 'Menu.flag' => 1),
                    'order'      => array('Role.id ASC')
               ));
               $menu_list      = array_unique(
                    $this->Menu->find("list", array(
                         'fields' => $menu_name,
                         'joins' => array(
                              array(
                                   'table' => 'permissions',
                                   'alias' => 'Permissions',
                                   'conditions' => array(
                                        'Menu.id = Permissions.menu_id',
                                        'Permissions.role_id' => $role,
                                   )
                              ),
                         ),
                         'conditions' => array(
                              'Menu.flag' => 1,
                         ),
                         'order' => array('Menu.id')
                    ))
               );

               #get page name from permissions table for search
               $page_list      = $this->Menu->find("list", array(
                    'joins' => array(
                         array(
                              'table' => 'permissions',
                              'alias' => 'Permissions',
                              'conditions' => array(
                                   'Menu.id = Permissions.menu_id',
                                   'Permissions.role_id' => $role,
                              )
                         ),
                    ),
                    'fields' => $page_name,
                    'conditions' => $condition_lng,
                    'order'      => array('Menu.id')
               ));
               $page_list = array_unique($page_list);

               $menu_array = $this->Menu->find("first", array(
                    'fields' => array('layer_no'),
                    'conditions' => array('flag' => 1),
                    'group'  => array('layer_no'),
                    'order' => 'layer_no DESC'
               ));
               $layerNo = $menu_array['Menu']['layer_no'];
               #get layers data from tbl_layer table
               $layers = $this->LayerType->find('list', array(
                    'fields' => array('name_jp', 'name_en', 'type_order'),
                    'conditions' => array('flag' => 1, 'type_order <=' => $layerNo),
                    'order' => 'type_order',
               ));

               $layer_data[0] = ($language == 'eng') ? 'Whole Company' : '全社';
               #prepare data according to language
               foreach ($layers as $layer_no => $layer_name) {
                    $name_jp = array_keys($layer_name)[0];
                    $name_en = array_values($layer_name)[0];
                    $layer_data[$layer_no] = ($language == 'eng') ? 'Same ' . $name_en : '該当' . $name_jp;
               }
               $datas = $this->getPaginateUserDatas(Paging::TABLE_PAGING, $conditions);
               //$datas = $this->Paginator->paginate('Role');
               foreach ($datas as $key => $value) {
                    if ($value['Role']['id'] == $datas[$key + 1]['Role']['id'] && $value['Menu']['page_name'] == $datas[$key + 1]['Menu']['page_name']) {
                         $datas[$key][0]['name_en'] .= ', ' . $datas[$key + 1][0]['name_en'];
                         $datas[$key][0]['name_jp'] .= ', ' . $datas[$key + 1][0]['name_jp'];
                         unset($datas[$key + 1]);
                    }
               }
               $resultData = array();
               foreach ($datas as $key => $value) {
                    if ($language == 'eng') {
                         if ($value['Menu']['menu_name_en'] != '') $resultData[$value['Role']['role_name']][$value['Menu']['menu_name_en']][$value['Menu']['page_name']] = $datas[$key];
                    } else {
                         if ($value['Menu']['menu_name_jp'] != '') $resultData[$value['Role']['role_name']][$value['Menu']['menu_name_jp']][$value['Menu']['page_name_jp']] = $datas[$key];
                    }
               }
               $query_count = $this->params['paging']['Role']['count'];
               // $query_count = count($page_list);
               $count = parent::getSuccessMsg('SS004', $query_count);
               $page  = $this->params['paging']['Role']['page'];
               $limit = $this->params['paging']['Role']['limit'];
               if ($query_count == 0) {
                    $this->set('errmsg', parent::getErrorMsg('SE001'));
                    $this->set('succmsg', '');
               } else {
                    $this->set('succmsg', parent::getSuccessMsg('SS004', $query_count));
                    $this->set('errmsg', '');
               }
               $this->set(compact('count', 'datas', 'role_list', 'menu_list', 'page_list', 'query_count', 'phase', 'button_type', 'admin_levels', 'layer_data', 'language', 'role', 'menu', 'pg_name', 'page', 'limit', 'resultData'));
               return $this->render('index');
          } catch (Exception $e) {
               CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
          }
          return $this->render('index');
     }

     /**
      * ajax method to get menu according to role_id
      *
      * @author WaiWaiMoe
      * @created_date 2022/12/05
      * @return data
      */
     public function getMenuName()
     {
          #only allow ajax request
          parent::checkAjaxRequest($this);
          $language       = $this->Session->read('Config.language');
          $check          = ($language == 'eng') ? 1 : 0;
          $role_id        = $this->request->data['role_id'];
          $this->Menu->virtualFields['menu_name'] = ($check == 1) ? 'menu_name_en' : 'menu_name_jp';
          #get page name from permissions table for search
          $menu_list      = $this->Menu->find("list", array(
               'fields' => array('menu_name'),
               'joins' => array(
                    array(
                         'table' => 'permissions',
                         'alias' => 'Permissions',
                         'conditions' => array(
                              'Menu.id = Permissions.menu_id',
                              'Permissions.role_id' => $role_id,
                         )
                    ),
               ),
               'conditions' => array(
                    'Menu.flag' => 1,
               ),
               'order'      => array('Menu.id')
          ));
          $menu_list = array_unique($menu_list);
          echo json_encode($menu_list);
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
          if (!$this->Role->exists($id)) {
               throw new NotFoundException(__('Invalid role'));
          }
          $options = array('conditions' => array('Role.' . $this->Role->primaryKey => $id));
          $this->set('role', $this->Role->find('first', $options));
     }

     /**
      * add method
      *
      * @return void
      */
     public function add()
     {

          if ($this->request->is('post')) {

               $role_name         = $this->request->data['role_name'];
               $permission_list   = explode(',', $this->request->data['permission_list']);
               $permission_choose = $this->request->data['permission_choose'];
               $menu = $this->request->data['hd_phase'];
               $page = $this->request->data['hd_page_name'];
               $button_type = $this->request->data['button_type'];
               $lower_role_name   = strtolower($role_name);
               $datas             = $this->getPaginateUserDatas(Paging::TABLE_PAGING);
               $actual_link       = $_SERVER["HTTP_REFERER"];
               #get role id and menu name in search box
               $link          = "";
               $pageNo  =  $this->request->data['hid_page_no'];
               $role_id = !empty($this->request->data('hid_s_role')) ? $this->request->data('hid_s_role') : null;
               $menu_name = !empty($this->request->data('hid_s_menu')) ? $this->request->data('hid_s_menu') : null;
               $page_name = !empty($this->request->data('hid_s_page')) ? $this->request->data('hid_s_page') : null;
               if ($role_id != null) {
                    $link   = $link . "?s_role=" . $role_id;
               }
               if ($menu_name != null) {
                    $link   = $link . "&s_menu=" . urlencode($menu_name);
               }
               if ($page_name != null) {
                    $link   = $link . "&s_page=" . urlencode($page_name);
               }
               #concat page number with link
               $link = ($pageNo != null ? "/" . $pageNo . $link : $link);
               if (!empty($this->request->data['primary_id'])) { //update mode
                    //$menu_id_array = explode(",",$this->request->data['menu_id']);
                    $menu_id_array = array();
                    //data prepare
                    $id_flag = $this->Role->find(
                         'first',
                         array(
                              'conditions' => array('Role.id' => $this->request->data['primary_id']),
                              'fields' => array('Role.read_limit', 'Role.flag')
                         )
                    );


                    //check role name already exist or not
                    foreach ($datas as $data) {
                         $db_role_name = strtolower($data['Role']['role_name']);
                         $db_id        = $data['Role']['id'];
                         if ($lower_role_name == $db_role_name && $db_id != $this->request->data['primary_id']) {
                              $errorMsg = parent::getErrorMsg('SE002', __('ロール名'));
                              $this->Flash->set($errorMsg, array("key" => "RoleError"));
                              $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                         }
                    }

                    if (!empty($id_flag)) {
                         // update
                         $read_limit = ($this->request->data('read_limit') < $id_flag['Role']['read_limit']) ? $this->request->data('read_limit') : $id_flag['Role']['read_limit'];
                         $result = array(
                              'id'          => $this->request->data['primary_id'],
                              'read_limit'  => $read_limit,
                              'role_name'   => $role_name,
                              'updated_by'  => $this->Session->read('LOGIN_ID')
                         );

                         $this->Role->save($result);
                         #read action limit need to use
                         $limit = substr($this->request->data['permission_list'], -1);
                         if ($limit == '' || $limit == null) {
                              $limit = 0;
                         }
                         $read_limit = ($this->request->data('read_limit') === null) ? 0 : $this->request->data('read_limit');
                         // $limit = substr($this->request->data['permission_list'], -1);

                         $language = $this->Session->read('Config.language');
                         $method    = ($language == 'eng') ? 'method' : 'method_jp';
                         if ($menu == 'all' && $page == 'all') {
                              $permission_list = [];
                              $button_type = ($button_type == null) ? [] : $button_type;
                              if ($language == 'eng') array_push($button_type, "index");
                              else array_push($button_type, "画面表示");
                              $menu_id = $this->Menu->find(
                                   'list',
                                   array(
                                        'conditions' => array(
                                             'Menu.flag' => 1,
                                             $method . ' IN' => $button_type
                                        ),
                                        'fields' => array('Menu.id', 'Menu.method')
                                   )
                              );
                              foreach ($menu_id as $value => $method) {
                                   if ($method == 'index') {
                                        $permission_list[] = $value . '_' . $read_limit;
                                   } else {
                                        $permission_list[] = $value . '_' . $limit;
                                   }
                                   $menu_id_array[] = $value;
                              }
                              // foreach($menu_id as $value){
                              // 	$permission_list[] = $value.'_'.$limit;
                              // 	$menu_id_array[] = $value;
                              // }

                         } else if ($menu != 'all' && $page == 'all') {
                              $permission_list = [];
                              $buttonType = [];
                              $button_type = ($button_type == null) ? [] : $button_type;
                              if ($language == 'eng') array_push($button_type, $page . "_index");
                              else array_push($button_type, $page . "_画面表示");
                              foreach ($button_type as $value) {
                                   $arrayExp = explode("_", $value);
                                   if (count($arrayExp) == 3) $buttonType[] = $arrayExp[1] . '_' . $arrayExp[2];
                                   else $buttonType[] = $arrayExp[1];
                              }

                              $menu_name    = ($language == 'eng') ? 'menu_name_en' : 'menu_name_jp';
                              $menu_id = $this->Menu->find(
                                   'list',
                                   array(
                                        'conditions' => array(
                                             'Menu.flag' => 1,
                                             $method . ' IN' => $buttonType,
                                             $menu_name => $menu
                                        ),
                                        'fields' => array('Menu.id', 'Menu.method')
                                   )
                              );
                              foreach ($menu_id as $value => $method) {
                                   if ($method == 'index') {
                                        $permission_list[] = $value . '_' . $read_limit;
                                   } else {
                                        $permission_list[] = $value . '_' . $limit;
                                   }
                                   $menu_id_array[] = $value;
                              }
                              // foreach($menu_id as $value){
                              // 	$permission_list[] = $value.'_'.$limit;
                              // 	$menu_id_array[] = $value;
                              // }

                         } else {
                              //
                              $menu_id = $this->request->data['menu_id'];
                              $menu_id_array = explode(",", $menu_id);
                              $menu_name    = ($language == 'eng') ? 'menu_name_en' : 'menu_name_jp';
                              $list = [];
                              $button_type = [];

                              foreach ($permission_list as $value) {

                                   $arrayExp = explode("_", $value);
                                   //$menu_id_array[] = $arrayExp[0];
                                   if (count($arrayExp) == 3) {
                                        $menu = $arrayExp[0];
                                        $button_type[] = $arrayExp[1];
                                   } else {

                                        $list[] = $value;
                                   }
                              }
                              $permission_list = [];
                              $permission_list = $list;

                              if (count($button_type) > 0) {
                                   $menu_id = $this->Menu->find(
                                        'list',
                                        array(
                                             'conditions' => array(
                                                  'Menu.flag' => 1,
                                                  $method . ' IN' => $button_type,
                                                  $menu_name => $menu
                                             ),
                                             'fields' => array('Menu.id', 'Menu.method')
                                        )
                                   );

                                   foreach ($menu_id as $value => $method) {
                                        if ($method == 'index') {
                                             $permission_list[] = $value . '_' . $read_limit;
                                        } else {
                                             $permission_list[] = $value . '_' . $limit;
                                        }
                                   }
                                   // foreach($menu_id as $value){
                                   // 	$permission_list[] = $value.'_'.$limit;
                                   // }
                              }
                         }
                         $permission_array = [];
                         foreach ($permission_list as $permit) {
                              $temp    = explode("_", $permit);
                              $menu_id = $temp[0];
                              $menu_id_array[] = $menu_id;
                              $limit   = $temp[1];
                              $res = array(
                                   'menu_id'    => $menu_id,
                                   'role_id'    => $this->request->data['primary_id'],
                                   'limit'      => $limit,
                                   'created_by' => $this->Session->read('LOGIN_ID'),
                                   'updated_by' => $this->Session->read('LOGIN_ID'),
                                   'created_date' => date("Y-m-d H:i:s"),
                                   'updated_date' => date("Y-m-d H:i:s"),
                              );
                              array_push($permission_array, $res);
                         }
                         $this->Permission->deleteAll(
                              array(
                                   "role_id" => $this->request->data['primary_id'],
                                   "menu_id" => $menu_id_array
                              )
                         );
                         $this->Permission->saveAll($permission_array);
                         $successMsg = parent::getSuccessMsg('SS002');
                         $this->Flash->set($successMsg, array("key" => "RoleSuccess"));
                         return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                    } else {
                         $errorMsg = parent::getErrorMsg('SE037');
                         $this->Flash->set($errorMsg, array("key" => "RoleError"));
                         return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                    }
               } else {
                    try {
                         //save 
                         $role_id = null;
                         $role_exist = $this->Role->find(
                              'first',
                              array(
                                   'conditions' => array('LOWER(Role.role_name)' => $lower_role_name),
                                   'fields' => array('Role.role_name', 'Role.read_limit')
                              )
                         );
                         if (empty($role_exist)) {
                              $result = array(
                                   'role_name'  => $role_name,
                                   'flag'       => 1,
                                   //'read_limit' => ($this->request->data('read_limit') === null)?$this->request->data('read_limit'):0, 
                                   'read_limit' => ($this->request->data('read_limit') != '') ? $this->request->data('read_limit') : 0,
                                   'created_by' => $this->Session->read('LOGIN_ID'),
                                   'updated_by' => $this->Session->read('LOGIN_ID'),
                                   'created_date' => date("Y-m-d H:i:s"),
                                   'updated_date' => date("Y-m-d H:i:s"),
                              );
                              $this->Role->save($result);
                              $role_id = $this->Role->getLastInsertId();
                         } else {
                              $role_id = $role_exist['Role']['id'];
                              $read_limit = $role_exist['Role']['read_limit'];
                         }
                         #check all permission or not
                         if ($permission_choose == 1) {
                              $permission_array = [];
                              $menu_id = $this->Menu->find(
                                   'list',
                                   array(
                                        'conditions' => array(
                                             'Menu.flag' => 1,
                                        ),
                                        'fields' => 'Menu.id'
                                   )
                              );
                              foreach ($menu_id as $menu) {
                                   $permission_id = null;
                                   $permission_id = $this->Permission->find(
                                        'list',
                                        array(
                                             'conditions' => array(
                                                  'Permission.menu_id' => $menu,
                                                  'Permission.role_id' => $role_id,
                                             ),
                                             'fields' => 'Permission.id'
                                        )
                                   );
                                   $res = array(
                                        'id'         => current($permission_id),
                                        'menu_id'    => $menu,
                                        'role_id'    => $role_id,
                                        'limit'      => 0,
                                        'created_by' => $this->Session->read('LOGIN_ID'),
                                        'updated_by' => $this->Session->read('LOGIN_ID'),
                                        'created_date' => date("Y-m-d H:i:s"),
                                        'updated_date' => date("Y-m-d H:i:s"),
                                   );
                                   array_push($permission_array, $res);
                              }
                         } else {
                              #read action limit need to use
                              $limit = substr($this->request->data['permission_list'], -1);
                              if ($limit == '' || $limit == null) {
                                   $limit = 0;
                              }
                              $read_limit = ($this->request->data('read_limit') === null) ? 0 : $this->request->data('read_limit');

                              $language = $this->Session->read('Config.language');
                              $method    = ($language == 'eng') ? 'method' : 'method_jp';
                              if ($menu == 'all' && $page == 'all') {
                                   $permission_list = [];
                                   $button_type = ($button_type == null) ? [] : $button_type;
                                   if ($language == 'eng') array_push($button_type, "index");
                                   else array_push($button_type, "画面表示");
                                   $menu_id = $this->Menu->find(
                                        'list',
                                        array(
                                             'conditions' => array(
                                                  'Menu.flag' => 1,
                                                  $method . ' IN' => $button_type
                                             ),
                                             'fields' => array('Menu.id', 'Menu.method')
                                        )
                                   );

                                   foreach ($menu_id as $value => $method) {
                                        if ($method == 'index') {
                                             $permission_list[] = $value . '_' . $read_limit;
                                        } else {
                                             $permission_list[] = $value . '_' . $limit;
                                        }
                                   }
                              } else if ($menu != 'all' && $page == 'all') {
                                   $permission_list = [];
                                   $buttonType = [];
                                   $button_type = ($button_type == null) ? [] : $button_type;
                                   if ($language == 'eng') array_push($button_type, $page . "_index");
                                   else array_push($button_type, $page . "_画面表示");
                                   foreach ($button_type as $value) {
                                        $arrayExp = explode("_", $value);
                                        if (count($arrayExp) == 3) $buttonType[] = $arrayExp[1] . '_' . $arrayExp[2];
                                        else $buttonType[] = $arrayExp[1];
                                   }
                                   $menu_name    = ($language == 'eng') ? 'menu_name_en' : 'menu_name_jp';
                                   $menu_id = $this->Menu->find(
                                        'list',
                                        array(
                                             'conditions' => array(
                                                  'Menu.flag' => 1,
                                                  $method . ' IN' => $buttonType,
                                                  $menu_name => $menu
                                             ),
                                             'fields' => array('Menu.id', 'Menu.method')
                                        )
                                   );
                                   foreach ($menu_id as $value => $method) {
                                        if ($method == 'index') {
                                             $permission_list[] = $value . '_' . $read_limit;
                                        } else {
                                             $permission_list[] = $value . '_' . $limit;
                                        }
                                   }
                              } else {
                                   $menu_name    = ($language == 'eng') ? 'menu_name_en' : 'menu_name_jp';
                                   $list = [];
                                   $button_type = [];

                                   foreach ($permission_list as $value) {
                                        $arrayExp = explode("_", $value);
                                        if (count($arrayExp) == 3) {
                                             $menu = $arrayExp[0];
                                             $button_type[] = $arrayExp[1];
                                        } else {

                                             $list[] = $value;
                                        }
                                   }

                                   $permission_list = [];
                                   $permission_list = $list;
                                   if (count($button_type) > 0) {
                                        $menu_id = $this->Menu->find(
                                             'list',
                                             array(
                                                  'conditions' => array(
                                                       'Menu.flag' => 1,
                                                       $method . ' IN' => $button_type,
                                                       $menu_name => $menu
                                                  ),
                                                  'fields' => array('Menu.id', 'Menu.method')
                                             )
                                        );

                                        foreach ($menu_id as $value => $method) {
                                             if ($method == 'index') {
                                                  $permission_list[] = $value . '_' . $read_limit;
                                             } else {
                                                  $permission_list[] = $value . '_' . $limit;
                                             }
                                        }
                                   }
                              }
                              $permission_array = [];
                              foreach ($permission_list as $permit) {
                                   $temp    = explode("_", $permit);
                                   $menu_id = $temp[0];
                                   $limit   = $temp[1];
                                   $permission_exist = $this->Permission->find(
                                        'list',
                                        array(
                                             'conditions' => array(
                                                  'role_id' => $role_id,
                                                  'menu_id' => $menu_id
                                             ),
                                             'fields' => 'Permission.id'
                                        )
                                   );
                                   if (!empty($permission_exist)) {
                                        $errorMsg = parent::getErrorMsg('SE002', __('データ'));
                                        $this->Flash->set($errorMsg, array("key" => "RoleError"));
                                        return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                                   } else {
                                        $res = array(
                                             'menu_id'    => $menu_id,
                                             'role_id'    => $role_id,
                                             'limit'      => $limit,
                                             'created_by' => $this->Session->read('LOGIN_ID'),
                                             'updated_by' => $this->Session->read('LOGIN_ID'),
                                             'created_date' => date("Y-m-d H:i:s"),
                                             'updated_date' => date("Y-m-d H:i:s"),
                                        );
                                        array_push($permission_array, $res);
                                   }
                              }
                         }

                         $result = array(
                              'id'          => $role_id,
                              'read_limit'  => ($read_limit > $this->request->data('read_limit')) ? $this->request->data('read_limit') : $read_limit,
                              'flag'          => 1,
                              'created_by' => $this->Session->read('LOGIN_ID'),
                              'updated_by'  => $this->Session->read('LOGIN_ID')
                         );
                         $this->Role->save($result);
                         $this->Permission->saveAll($permission_array);
                         $successMsg = parent::getSuccessMsg('SS001');
                         $this->Flash->set($successMsg, array("key" => "RoleSuccess"));
                         return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                    } catch (Exception $e) {
                         CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                         $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                    }
               }
          }
     }

     /**
      * edit method
      *
      * @throws NotFoundException
      * @param string $id
      * @return void
      */
     public function edit()
     {
          #only allow ajax request
          parent::checkAjaxRequest($this);
          $language = $this->Session->read('Config.language');
          $check    = ($language == 'eng') ? 1 : 0;
          $roles = $this->Role->find(
               'all',
               array(
                    'fields' => array('Role.read_limit', 'Role.id', 'role_name', 'Menu.page_name', 'Menu.page_name_jp', 'GROUP_CONCAT(Menu.method) as method_name', 'GROUP_CONCAT(Permission.limit) as limitation', 'GROUP_CONCAT(Menu.id) as menu_id', 'group_concat(Menu.method) as method', 'group_concat(Menu.method_jp) as method_jp', 'IF(' . $check . ', Menu.menu_name_en,Menu.menu_name_jp)as menu_name', 'IF(' . $check . ', Menu.page_name,Menu.page_name_jp)as page_name', 'IF(' . $check . ',GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method))', 'GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)))as action_name'),
                    // 'fields' => array('Role.read_limit','Role.id', 'role_name','Menu.page_name', 'Menu.page_name_jp','GROUP_CONCAT(Menu.method) as method_name','GROUP_CONCAT(Permission.limit) as limitation', 'GROUP_CONCAT(Menu.id) as menu_id',
                    // 'IF('.$check.', Menu.menu_name_en,Menu.menu_name_jp)as menu_name', 'IF('.$check.', Menu.page_name,Menu.page_name_jp)as page_name','IF('.$check.',CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("Whole Company の ",Menu.method)) Else GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method)) END','CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("全社 の ",Menu.method_jp)) ELSE GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)) END )as action_name')
                    'joins'  => array(
                         array(
                              'table' => 'permissions',
                              'alias' => 'Permission',
                              'type'  => 'LEFT',
                              'conditions' => array(
                                   'Role.id = Permission.role_id',
                                   'Permission.id' => explode(",", $this->request->data['id_array']),
                                   // 'Permission.id'=>explode(",","2668"),
                              )
                         ),
                         array(
                              'table' => 'menus',
                              'alias' => 'Menu',
                              'type'  => 'LEFT',
                              'conditions' => array(
                                   'Menu.id = Permission.menu_id',
                                   'Menu.flag' => 1,
                              )
                         ),
                         array(
                              'table' => 'layer_types',
                              'alias' => 'Layer',
                              'type' => 'LEFT',
                              'conditions' => array(
                                   'Layer.type_order = Permission.limit',
                                   'Layer.flag' => 1
                              )
                         ),
                    ),
                    'conditions' => array('Role.id' => $this->request->data['id'], 'Role.flag' => 1),
                    'group' => array('Menu.menu_name_en', 'Menu.page_name'),
                    'order' => array('Role.id'),
               )
          );
          // $tmp_datas = $roles[0];
          // $tmp_datas[0]['method_name'] = implode(',', array_column(array_column($roles,0),'method_name'));
          // $tmp_datas[0]['limitation'] = implode(',', array_column(array_column($roles,0),'limitation'));
          // $tmp_datas[0]['menu_id'] = implode(',', array_column(array_column($roles,0),'menu_id'));
          // $tmp_datas[0]['action_name'] = implode(',', array_column(array_column($roles,0),'action_name'));
          // $tmp_datas['Menu']['page_name'] = implode(',', array_column(array_column($roles,'Menu'),'page_name'));
          // $tmp_datas['Menu']['page_name_jp'] = implode(',', array_column(array_column($roles,'Menu'),'page_name_jp'));

          // $rt_data[0] = $tmp_datas;
          // echo json_encode($rt_data[0]);
          // pr($roles);
          // die();
          foreach ($roles as $col_index => $role) {
               // $last_value = array_pop(explode(',',$role[0]['method']));
               // $last_value_jp = array_pop(explode(',',$role[0]['method_jp']));

               $limit_method = array_combine(explode(',', $role[0]['method']), explode(',', $role[0]['limitation']));
               $limit_method_jp = array_combine(explode(',', $role[0]['method_jp']), explode(',', $role[0]['limitation']));

               $comma = ($roles[$col_index][0]['action_name'] == '') ? '' : ',';


               if ($language == 'eng') {
                    $tmp = array();
                    foreach ($limit_method as $method => $limit) {
                         if ($limit == 0) {
                              $tmp[] = 'Whole Company の ' . $method;
                         }
                    }
                    $roles[$col_index][0]['action_name'] = trim($roles[$col_index][0]['action_name'] . $comma . implode(',', $tmp), ',');
               } else {
                    $tmp_jp = array();
                    foreach ($limit_method_jp as $method_jp => $limit) {
                         if ($limit == 0) {
                              $tmp_jp[] = '全社 の ' . $method_jp;
                         }
                    }
                    $roles[$col_index][0]['action_name'] = trim($roles[$col_index][0]['action_name'] . $comma . implode(',', $tmp_jp), ',');
               }


               // if($last_value == 'index' || $last_value == '画面表示'){
               // if($language=='eng'){
               // 	$roles[$col_index][0]['action_name'] .= $comma.'Whole Company の '.$last_value;
               // }else{
               // 	$roles[$col_index][0]['action_name'] .= $comma.'全社 の '.$last_value_jp;
               // }
               // }
          }
          // $roles[0]['count'] = (count(array_unique(explode(',',$roles[0][0]['method_name'])))>1)? 2 : 1;
          // pr($roles);
          // die();
          echo json_encode($roles[0]);
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
          $language = $this->Session->read('Config.language');
          #get role id and menu name in search box
          $link               = "";
          $pageNo            =  $this->request->data['hid_search_page_no'];
          $limit              = $this->request->data('hid_search_limit');
          $row_count          = $this->request->data('hid_search_row_count');
          $id                = $this->request->data('id');
          $id_array        = explode(",", $this->request->data['id_array']);
          $role_id           = !empty($this->request->data('s_role')) ? $this->request->data('s_role') : null;
          $menu_name           = !empty($this->request->data('s_menu')) ? $this->request->data('s_menu') : null;
          $page_name           = !empty($this->request->data('s_page')) ? $this->request->data('s_page') : null;
          $no_of_page      = ceil($row_count / $limit);
          $page_val           = substr($pageNo, 5);
          $row_per_page       = $row_count % $limit;
          if ($row_per_page == 1 && $no_of_page == $page_val) {
               $pageNo = 'page:' . ($page_val - 1);
          }
          if ($role_id != null) {
               $link   = $link . "?s_role=" . $role_id;
          }
          if ($menu_name != null) {
               $link   = $link . "&s_menu=" . urlencode($menu_name);
          }
          if ($page_name != null) {
               $link   = $link . "&s_page=" . urlencode($page_name);
          }
          #concat page number with link
          $link = ($pageNo != null ? "/" . $pageNo . $link : $link);
          $id_flag = $this->Role->find(
               'first',
               array(
                    'conditions' => array('Role.id' => $id),
                    'fields' => array('Role.flag, Role.id')
               )
          );
          $role_id    = $id_flag['Role']['id'];
          $chk_user = $this->User->find(
               'list',
               array(
                    'conditions' => array('User.flag' => 1),
                    'fields' => array('User.role_id')
               )
          );

          $permission = $this->Permission->find('all', array(
               'conditions' => array(
                    'Permission.id' => $id_array,
                    'Permission.role_id' => $id,
                    'Menu.mail_flag' => "ON"
               )
          ));
          $count_per = $this->Permission->find(
               'count',
               array(
                    'conditions' => array('Permission.role_id' => $role_id),
               )
          );
          if ($count_per > 0) {
               // check role name is used in user or not 
               foreach ($chk_user as $users) {
                    if ($users == $role_id) {
                         $err = array(__('ロール名'), __('ユーザー管理'));
                         $errorMsg = parent::getErrorMsg('SE130', $err);
                         $this->Flash->set($errorMsg, array("key" => "RoleError"));
                         return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
                    }
               }
               if (!empty($permission)) {
                    $err = array(__('ロール名'), __('メールフロー設定'));
                    $errorMsg = parent::getErrorMsg('SE130', $err);
                    $this->Flash->set($errorMsg, array("key" => "RoleError"));
                    return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
               }
          }
          // check role name is used in mail setting or not 
          if ($id_flag['Role']['flag'] == 1) {
               $check_data = $this->Permission->find(
                    'list',
                    array(
                         'conditions' => array(
                              "NOT" => array("Permission.id" => $id_array),
                              'Permission.role_id' => $id
                         ),
                         'fields' => array('Permission.id', 'Permission.role_id')
                    )
               );
               if (empty($check_data)) {
                    $result = array(
                         'id' => $id,
                         'flag' => 0,
                    );
                    $this->Role->save($result);
               }

               #delete existing data
               $delete_data = $this->Permission->find(
                    'all',
                    array(
                         'conditions' => array('Permission.id' => $id_array),
                    )
               );
               if (!empty($delete_data)) {
                    $this->Permission->delete($id_array);
               }
               $successMsg = parent::getSuccessMsg('SS003');
               $this->Flash->set($successMsg, array("key" => "RoleSuccess"));
               if ($row_count == 1) {
                    return $this->redirect(array('controller' => 'Roles', 'action' => 'index'));
               }
               return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
          } else {
               $errorMsg = parent::getErrorMsg('SE037');
               $this->Flash->set($errorMsg, array("key" => "RoleError"));
               return $this->redirect(array('controller' => 'Roles', 'action' => 'index' . $link));
          }
     }

     public function getPaginateUserDatas($limit, $conditions = [])
     {
          $language       = $this->Session->read('Config.language');
          if ($language == 'eng') {
               // $fields = array('group_concat(Permission.menu_id) as menu_array','group_concat(Permission.limit) as plimit','SUBSTRING_INDEX(group_concat(Permission.limit),",",1) as test','IF(SUBSTRING_INDEX(group_concat(Permission.limit),",",-1) = 0,GROUP_CONCAT(concat("Whole Company の ",Menu.method)),GROUP_CONCAT(concat("Same の ",Menu.method))) as name_en',
               // );
               $fields = array(
                    'Role.role_name', 'group_concat(Permission.id) as id_array', 'group_concat(Permission.menu_id) as menu_array', 'Menu.page_name', 'Menu.page_name_jp', 'group_concat(Menu.method) as method', 'group_concat(Menu.method_jp) as method_jp', 'group_concat(Permission.limit) as plimit', 'Menu.menu_name_en', 'Menu.menu_name_jp', 'GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method)) as name_en', 'GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)) as name_jp'
               );
               // $fields = array('Role.role_name','group_concat(Permission.id) as id_array','group_concat(Permission.menu_id) as menu_array','Menu.page_name', 'Menu.page_name_jp','group_concat(Menu.method) as method','group_concat(Menu.method_jp) as method_jp','group_concat(Permission.limit) as plimit','Menu.menu_name_en','Menu.menu_name_jp','CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("Whole Company の ",Menu.method)) Else GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method)) END as name_en','CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("全社 の ",Menu.method_jp)) ELSE GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)) END as name_jp'
               // );
               // $fields = array(
               // 'Permission.limit','group_concat(Layer.name_en)'
               // 'CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("Whole Company の ",Menu.method)) Else GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method)) END as name_en','CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("全社 の ",Menu.method_jp)) ELSE GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)) END as name_jp'
               // );
          } else {
               $fields = array(
                    'Layer.*', 'Role.role_name', 'group_concat(Permission.id) as id_array', 'group_concat(Permission.menu_id) as menu_array', 'Menu.page_name', 'Menu.page_name_jp', 'group_concat(Menu.method) as method', 'group_concat(Menu.method_jp) as method_jp', 'group_concat(Permission.limit) as plimit', 'Menu.menu_name_en', 'Menu.menu_name_jp', 'GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method)) as name_en', 'GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)) as name_jp'
               );
               // $fields = array('Layer.*','Role.role_name','group_concat(Permission.id) as id_array','group_concat(Permission.menu_id) as menu_array','Menu.page_name', 'Menu.page_name_jp','Menu.method','Menu.method_jp','group_concat(Permission.limit) as plimit','Menu.menu_name_en','Menu.menu_name_jp','CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("Whole Company の ",Menu.method)) Else GROUP_CONCAT(concat("Same ",Layer.name_en," の ",Menu.method)) END as name_en','CASE WHEN (Permission.limit = 0) THEN GROUP_CONCAT(concat("全社 の ",Menu.method_jp)) ELSE GROUP_CONCAT(concat("該当 ",Layer.name_jp," の ",Menu.method_jp)) END as name_jp'
               // );
          }
          $conditions["Role.flag"] = 1;
          $this->paginate  = array(
               'maxLimit' => $limit,
               'limit' => $limit,
               'fields' => $fields,
               'joins'      => array(
                    array(
                         'table' => 'permissions',
                         'alias' => 'Permission',
                         'type'  => 'LEFT',
                         'conditions' => array(
                              'Role.id = Permission.role_id',
                         ),
                    ),
                    array(
                         'table' => 'menus',
                         'alias' => 'Menu',
                         'type'  => 'LEFT',
                         'conditions' => array(
                              'Menu.id = Permission.menu_id',
                              'Menu.flag' => 1,
                         )
                    ),
                    array(
                         'table' => 'layer_types',
                         'alias' => 'Layer',
                         'type' => 'LEFT',
                         'conditions' => array(
                              'Layer.type_order = Permission.limit',
                              'Layer.flag' => 1
                         )
                    ),
               ),
               'conditions' => $conditions,
               // 'group' => array('Permission.role_id','Permission.limit','Menu.menu_name_en','Menu.page_name'),
               // 'group' => array('Permission.role_id','Menu.menu_name_en','Menu.page_name'),
               'group' => array('Permission.role_id', 'Menu.menu_name_en', 'Menu.page_name'),
               'order' => 'Role.id, Menu.id ASC'
          );
          $datas = $this->Paginator->paginate('Role');
          // pr($datas);
          // $tmp_datas = $datas[0];
          // $tmp_datas[0]['id_array'] = implode(',', array_column(array_column($datas,0),'id_array'));
          // $tmp_datas[0]['menu_array'] = implode(',', array_column(array_column($datas,0),'menu_array'));
          // $tmp_datas[0]['method'] = implode(',', array_column(array_column($datas,0),'method'));
          // $tmp_datas[0]['method_jp'] = implode(',', array_column(array_column($datas,0),'method_jp'));
          // $tmp_datas[0]['plimit'] = implode(',', array_column(array_column($datas,0),'plimit'));
          // $tmp_datas[0]['name_en'] = implode(',', array_column(array_column($datas,0),'name_en'));
          // // $tmp_datas[0]['name_jp'] = implode(',', array_column(array_column($datas,0),'name_jp'));
          // // $tmp_datas['Menu']['page_name'] = implode(',', array_column(array_column($datas,'Menu'),'page_name'));
          // // $tmp_datas['Menu']['page_name_jp'] = implode(',', array_column(array_column($datas,'Menu'),'page_name_jp'));

          // $rt_data[0] = $tmp_datas;
          // return $rt_data;
          foreach ($datas as $col_index => $data) {
               $limit_method = array_combine(explode(',', $data[0]['method']), explode(',', $data[0]['plimit']));
               $limit_method_jp = array_combine(explode(',', $data[0]['method_jp']), explode(',', $data[0]['plimit']));
               // pr($limit_method);

               // $last_value = array_pop(explode(',',$data[0]['method']));
               // $last_value_jp = array_pop(explode(',',$data[0]['method_jp']));

               $comma = ($datas[$col_index][0]['name_en'] == '') ? '' : ',';
               $comma_jp = ($datas[$col_index][0]['name_jp'] == '') ? '' : ',';

               $tmp = array();
               foreach ($limit_method as $method => $limit) {
                    if ($limit == 0) {
                         $tmp[] = 'Whole Company の ' . $method;
                    }
               }
               $datas[$col_index][0]['name_en'] = trim($datas[$col_index][0]['name_en'] . $comma . implode(',', $tmp), ',');

               $tmp_jp = array();
               foreach ($limit_method_jp as $method_jp => $limit) {
                    if ($limit == 0) {
                         $tmp_jp[] = '全社 の ' . $method_jp;
                    }
               }
               $datas[$col_index][0]['name_jp'] = trim($datas[$col_index][0]['name_jp'] . $comma_jp . implode(',', $tmp_jp), ',');


               // if($limit_method[$last_value] == 0 || $limit_method_jp[$last_value_jp] == 0){
               // $comma = ($datas[$col_index][0]['name_en'] == '')? '':',';
               // $comma_jp = ($datas[$col_index][0]['name_jp'] == '')? '':',';
               // 	if($last_value == 'index' || $last_value == '画面表示'){
               // 		$datas[$col_index][0]['name_en'] .= $comma.'Whole Company の '.$last_value;
               // 		$datas[$col_index][0]['name_jp'] .= $comma_jp.'全社 の '.$last_value_jp;
               // 	}

               // }


          }
          // die();
          // pr($datas);die();
          return $datas;
     }

     /**
      * ajax method to get pagename according to phase
      * @author WaiWaiMoe
      * @created_date 2022/05/23
      * @return data
      */
     public function getPageName()
     {
          #only allow ajax request
          parent::checkAjaxRequest($this);
          $language       = $this->Session->read('Config.language');
          $phase_id       = $this->request->data['phase_id'];
          $role_id          = $this->request->data['role_id'];
          $menu_name          = $this->request->data['menu_name'];
          $pg_name           = ($language == 'eng') ? ['page_name'] : ['page_name_jp'];
          $condition          = ($language == 'eng') ? ['Menu.flag' => "1", "Menu.menu_name_en" => $menu_name] : ['Menu.flag' => "1", "Menu.menu_name_jp" => $menu_name];

          $name           = $this->request->data['name'];
          $mail_vars      = [];

          if ($name != "Search") {
               #get page name from tbl_page table
               $menu_array = $this->Menu->find("list", array(
                    'fields' => array('page_name', 'layer_no'),
                    'conditions' => array('flag' => 1, 'menu_name_en = "' . $phase_id . '" OR menu_name_jp = "' . $phase_id . '"'),
                    'order' => 'id',
               ));
               if ($language != 'eng') {
                    $menu_array = $this->Menu->find("list", array(
                         'fields' => array('page_name_jp', 'layer_no'),
                         'conditions' => array('flag' => 1, 'menu_name_en = "' . $phase_id . '" OR menu_name_jp = "' . $phase_id . '"'),
                         'order' => 'id',
                    ));
               }
               $page_name     = array_keys($menu_array);
               $layer_setting = $menu_array[$page_name[0]];

               #get layers data from tbl_layer table
               $layers = $this->LayerType->find('list', array(
                    'fields' => array('name_jp', 'name_en', 'type_order'),
                    'conditions' => array('flag' => 1, 'type_order <=' => $layer_setting[$phase_id]),
                    'order' => 'type_order',
               ));

               $layer_data[0] = ($language == 'eng') ? 'Whole Company' : '全社';
               #prepare data according to language
               foreach ($layers as $layer_no => $layer_name) {
                    $name_jp = array_keys($layer_name)[0];
                    $name_en = array_values($layer_name)[0];
                    $layer_data[$layer_no] = ($language == 'eng') ? 'Same ' . $name_en : '該当' . $name_jp;
               }
               echo json_encode(array($page_name, $layer_data));
          } else {
               #get page name from permissions table for search
               // $page_name = $this->Permission->find("list",array(
               //     'fields' => array('Menus.page_name'),
               //     'joins'      => array(
               //         array(
               //             'table' => 'menus',
               //             'alias' => 'Menus',
               //             'type' => 'LEFT',
               //             'conditions' => array(
               //                 'Menus.id = Permission.menu_id',
               //                 'Menus.flag' => 1
               //             )
               //         ),
               //     ),
               //     'conditions' => array('flag' => 1,'menu_name_en = "'.$phase_id.'" OR menu_name_jp = "'.$phase_id.'"'),
               // 	'order' => 'Menus.id',
               // ));
               $page_name      = $this->Menu->find("list", array(
                    'joins' => array(
                         array(
                              'table' => 'permissions',
                              'alias' => 'Permissions',
                              'conditions' => array(
                                   'Menu.id = Permissions.menu_id',
                                   'Permissions.role_id' => $role_id,
                              )
                         ),
                    ),
                    'fields' => $pg_name,
                    'conditions' => $condition,
                    'order'      => array('Menu.id')
               ));
               $page_name = array_unique($page_name);
               echo json_encode($page_name);
          }
     }
     public function getLimitaion()
     {
          parent::checkAjaxRequest($this);
          $language       = $this->Session->read('Config.language');
          $phase_id = $this->request->data['phase_id'];
          $menu_array = $this->Menu->find("first", array(
               'fields' => array('id', 'layer_no'),
               'conditions' => array('flag' => 1),
               'group'  => array('layer_no'),
               'order' => 'layer_no DESC'
          ));
          $layerNo = $menu_array['Menu']['layer_no'];
          #get layers data from tbl_layer table
          $layers = $this->LayerType->find('list', array(
               'fields' => array('name_jp', 'name_en', 'type_order'),
               'conditions' => array('flag' => 1, 'type_order <=' => $layerNo),
               'order' => 'type_order',
          ));
          $layer_data[0] = ($language == 'eng') ? 'Whole Company' : '全社';
          #prepare data according to language
          foreach ($layers as $layer_no => $layer_name) {
               $name_jp = array_keys($layer_name)[0];
               $name_en = array_values($layer_name)[0];
               $layer_data[$layer_no] = ($language == 'eng') ? 'Same ' . $name_en : '該当' . $name_jp;
          }

          echo json_encode($layer_data);
     }
     /**
      * ajax method to get button type according to page name
      *
      * @author WaiWaiMoe
      * @created_date 2022/06/22
      * @return data
      */
     public function getButtonType()
     {
          #only allow ajax request
          parent::checkAjaxRequest($this);
          $result    = [];
          $language       = $this->Session->read('Config.language');
          $page_name = $this->request->data['page_name'];
          if ($language == 'eng') {
               $btn_data  = $this->Menu->find("list", array(
                    'fields'     => array('id', 'method'),
                    'conditions' => array('flag' => 1, 'page_name' => $page_name, 'method !=' => 'index'),
               ));
          } else {
               $btn_data  = $this->Menu->find("list", array(
                    'fields'     => array('id', 'method_jp'),
                    'conditions' => array('flag' => 1, 'page_name_jp' => $page_name, 'method !=' => 'index'),
               ));
          }

          echo (json_encode($btn_data));
     }

     /**
      * ajax method to get read menu id
      *
      * @author Hein Htet Ko
      * @created_date 2023/06/29
      * @return id
      */
     public function getReadMenuId()
     {
          parent::checkAjaxRequest($this);
          $phase = $this->request->data('phase');
          $page = $this->request->data('page_name');
          $language = $this->Session->read('Config.language');
          $menu_name = ($language == 'eng') ? 'menu_name_en' : 'menu_name_jp';
          $page_name = ($language == 'eng') ? 'page_name' : 'page_name_jp';
          $menu_id = $this->Menu->find("first", array(
               'fields' => array('id'),
               'conditions' => array(
                    $menu_name => $phase,
                    $page_name => $page,
                    'method' => 'index',
                    'flag' => 1
               ),
          ));

          echo json_encode($menu_id);
     }
     /**
      * ajax method to get read limit from permission table
      *
      * @author Hein Htet Ko
      * @created_date 2023/07/03
      * @return read_limit
      */
     public function getPermissionReadLimit()
     {
          parent::checkAjaxRequest($this);
          $role_id = $this->request->data('role_id');
          $menu_id = $this->request->data('menu_id');
          $page = $this->request->data('page_name');
          $language = $this->Session->read('Config.language');
          $menu_name = ($language == eng) ? 'menu_name_en' : 'menu_name_jp';
          $page_name = ($language == eng) ? 'page_name' : 'page_name_jp';
          $conditions = array();
          $menu_conditions = array();
          if ($role_id != '') {
               $conditions['Permission.role_id'] = $role_id;
          }
          $menu_conditions[] = 'M.id = Permission.menu_id';
          $menu_conditions[] = 'M.flag = 1';

          $read_limit = $this->Permission->find('list', array(
               'fields' => array('M.' . $page_name, 'Permission.limit'),
               'conditions' => $conditions,
               'joins' => array(
                    array(
                         'table' => 'menus',
                         'alias' => 'M',
                         'type'  => 'INNER',
                         'conditions' => $menu_conditions,
                    ),
               ),
               'order' => 'Permission.limit DESC',
               // 'group' => array('M.menu_name_en')
          ));

          $count = $this->Permission->find(
               'count',
               array(
                    'conditions' => array(
                         'Permission.role_id' => $role_id,
                    ),
                    'joins' => array(
                         array(
                              'table' => 'menus',
                              'alias' => 'M1',
                              'type' => 'INNER',
                              'conditions' => array(
                                   'M1.id = Permission.menu_id',
                                   'M1.' . $page_name . ' = "' . $page . '"',
                                   'M1.flag = 1'
                              )
                         )
                    ),
               )
          );

          // pr($count);die();
          $tmp = array();
          if ($menu_id != 'all' && $page != 'all') {
               // $conditions['Permission.menu_id'] = $menu_id;
               // $menu_conditions[] = 'M.'.$page_name.' = "'.$page.'"';
               $tmp[$page] = $read_limit[$page];
               $tmp['count'] = ($count > 1) ? 2 : 1;
          } else {
               $tmp = $read_limit;
               // $tmp['count'] = 2;
          }
          // pr($tmp);die();

          echo json_encode($tmp);
     }
}
