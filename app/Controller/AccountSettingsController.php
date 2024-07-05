<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * AccountSettings Controller
 *
 * @property AccountSetting $AccountSetting
 * @property PaginatorComponent $Paginator
 */
class AccountSettingsController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator');
    public $uses = array('AccountSetting', 'Menu', 'Account', 'Layer', 'LayerType');

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

        $this->layout = 'mastermanagement';
        $layer_type = Setting::LAYER_SETTING['bottomLayer'];
        $lan = $this->Session->read('Config.language');

        $page_name = Setting::PAGE_NAME;
        $start_num = 1;
        $search_fields = ($lan == 'eng') ? array('Menu.id', 'Menu.page_name') : array('Menu.id', 'Menu.page_name_jp');
        $searchData = !empty($this->request->data('search_page_name')) ? $this->request->data('search_page_name') : $this->request->query('page_name');
        if ($searchData) {
            $this->Session->write('SEARCH_PAGE', $searchData);
            $spage_conditions = ($lan == 'eng') ? array('Menu.page_name' => $searchData, 'Menu.flag' => 1) : array('Menu.page_name_jp' => $searchData, 'Menu.flag' => 1);

            $spage_eng = $this->Menu->find('first', array(
                'fields' => array('Menu.page_name'),
                'conditions' => $spage_conditions
            ))['Menu']['page_name'];
        } else {
            $this->Session->write('SEARCH_PAGE', '');
        }

        $accountSettings = $this->getAccountSettingData($searchData);

        $search_page_name = $this->AccountSetting->find('list', array(
            'fields' => $search_fields,
            'conditions' => array(
                'Menu.page_name IN' => $page_name,
                'AccountSetting.flag' => 1,
                'Menu.flag' => 1
            ),
            'group' => array('Menu.page_name'),
            'joins' => array(array(
                'table' => 'menus',
                'alias' => 'Menu',
                'type' => 'left',
                'conditions' => array(
                    'AccountSetting.menu_id = Menu.id ',
                )
            ),),
            'order' => array('Menu.menu_name_en desc')
        ));

        $menuFields[] = ($lan == 'eng') ? "Menu.page_name" : "Menu.page_name_jp";

        $account_pages = $this->Menu->find('list', array(
            'fields' => $menuFields,
            'conditions' => array(
                'Menu.method' => array('index'),
                'Menu.flag' => '1',
                'Menu.page_name IN' => $page_name
            ),

            'group' => array('Menu.page_name'),
            'order' => array('Menu.menu_name_en desc')
        ));

        $query_count = $this->params['paging']['AccountSetting']['count'];
        $rowCount = parent::getSuccessMsg('SS004', $query_count);
        $limit = $this->params['paging']['AccountSetting']['limit'];
        $page = $this->params['paging']['AccountSetting']['page'];
        $this->Session->write('Page.pageCount', $page);
        $this->set(compact('accountSettings', 'start_num', 'account_pages', 'rowCount', 'layer_name', 'search_page_name', 'limit', 'query_count', 'searchData', 'spage_eng'));
    }

    public function getAccountSettingData($search_data)
    {
        $limit = Paging::TABLE_PAGING;
        $menus = Setting::PAGE_NAME;
        $lan = $this->Session->read('Config.language');

        if (!empty($search_data)) {
            if ($lan == 'eng') {
                $conditions = array('Menus.page_name like ' => $search_data, 'AccountSetting.flag' => '1');
            } else {
                $conditions = array('Menus.page_name_jp like ' => $search_data, 'AccountSetting.flag' => '1');
            }
        } else {
            $conditions = array('Menus.page_name in ' => $menus, 'AccountSetting.flag' => '1');
        }
        $this->paginate  = array(
            'maxLimit' => $limit,
            'limit' => $limit,
            'joins' => array(
                array(
                    'table' => 'accounts',
                    'alias' => 'Accounts',
                    'type' => 'left',
                    'conditions' => array(
                        'Accounts.id = AccountSetting.account_id AND Accounts.flag = 1'
                    )
                ),
                array(
                    'table' => 'menus',
                    'alias' => 'Menus',
                    'type' => 'left',
                    'conditions' => array(
                        'Menus.id = AccountSetting.menu_id AND Menus.flag = 1'
                    )
                ),
                array(
                    'table' => 'layers',
                    'alias' => 'Layers',
                    'type' => 'left',
                    'conditions' => array(
                        'Layers.layer_code = AccountSetting.layer_code AND Layers.flag = 1'
                    )
                ),
            ),
            'fields' => array('AccountSetting.*', 'Accounts.*', 'Menus.*', 'Layers.*'),
            'conditions' => $conditions,
            'order' => array('AccountSetting.target_year')
        );

        $data = $this->Paginator->paginate('AccountSetting');
        return $data;
    }


    /**
     * add method
     *
     * @return void
     */
    public function saveData()
    {
        $data = $this->request->data;
        $hd_id = $data['hd_id'];
        $search_page = $data['hid_page'];
        $page_no = $data['hid_page_no'];
        $menu_id = $data['page_name'];
        $display_order = $data['display_order'];
        $label_name = $data['label_name'];
        $acc_type = $data['acc_type'];
        $target_year = $data['target_year'];
        $layer_code = $data['layer_type'];
        $login_id = $this->Session->read('LOGIN_ID');
        $date = date('Y-m-d H:i:s');
        $page = $this->Session->read('Page.pageCount');

        if ($this->request->is('post')) {
            $checkDuplicate = $this->checkDuplicate($data);

            $acc_type_id = $this->Account->find('first', array(
                'fields' => array('account_type_id'),
                'conditions' => array(
                    'Account.flag' => 1,
                    'Account.id' => $acc_type
                )
            ))['Account']['account_type_id'];
            if ($checkDuplicate) {
                if (!empty($hd_id)) {

                    $save_element = [
                        "id" => $hd_id,
                        "target_year" => $target_year,
                        "layer_code" => $layer_code,
                        "account_id" => $acc_type,
                        "account_type_id" => $acc_type_id,
                        "menu_id" => $menu_id,
                        "label_name" => $label_name,
                        "display_order" => $display_order,
                        "flag" => 1,
                        "created_by" => $login_id,
                        "updated_by" => $login_id,
                        "created_date" => $date,
                        "updated_date" => $date
                    ];

                    if ($this->AccountSetting->save($save_element)) {

                        if (!empty($search_page)) {
                            $successMsg = parent::getSuccessMsg('SS002');
                            $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                        } else {
                            $successMsg = parent::getSuccessMsg('SS002');
                            $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index', 'page' => $page));
                        }
                    } else {
                        if (!empty($search_page)) {
                            $errorMsg = parent::getErrorMsg('SE134');
                            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                        } else {
                            $errorMsg = parent::getErrorMsg('SE134');
                            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index', 'page' => $page));
                        }
                    }
                } else {

                    $save_element = [
                        "target_year" => $target_year,
                        "layer_code" => $layer_code,
                        "account_id" => $acc_type,
                        "account_type_id" => $acc_type_id,
                        "menu_id" => $menu_id,
                        "label_name" => $label_name,
                        "display_order" => $display_order,
                        "flag" => 1,
                        "created_by" => $login_id,
                        "updated_by" => $login_id,
                        "created_date" => $date,
                        "updated_date" => $date
                    ];

                    if ($this->AccountSetting->save($save_element)) {

                        if (!empty($search_page)) {
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                        } else {
                            $successMsg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index'));
                        }
                    } else {
                        if (!empty($search_page)) {
                            $errorMsg = parent::getErrorMsg('SE003');
                            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                        } else {
                            $errorMsg = parent::getErrorMsg('SE003');
                            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index', 'page' => $page));
                        }
                    }
                }
            } else {
                if (!empty($search_page)) {
                    $errorMsg = parent::getErrorMsg('SE002', __('Data'));
                    $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                    return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                } else {
                    $errorMsg = parent::getErrorMsg('SE002', __('Data'));
                    $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                    return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index', 'page' => $page));
                }
            }
        } else {
            if (!empty($search_page)) {
                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
            } else {
                $errorMsg = parent::getErrorMsg('SE003');
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index', 'page' => $page));
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
        parent::checkAjaxRequest($this);
        $id = $this->request->data('id');

        $result = $this->AccountSetting->find('first', array(
            'fields' => array(
                'AccountSetting.*', 'Accounts.*'
            ),
            'conditions' => array('AccountSetting.id' => $id, 'AccountSetting.flag = 1'),

            'joins' => array(
                array(
                    'table' => 'accounts',
                    'alias' => 'Accounts',
                    // 'type'  => 'LEFT',
                    'conditions' => array(
                        'Accounts.flag = 1',
                        'Accounts.id = AccountSetting.account_id'
                    )
                ),
            )

        ));
        return json_encode($result);
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function deleteData()
    {
        $id = $this->request->data('hd_id');
        $search_page = $this->request->data('hid_page');
        $page_no = $this->request->data('hid_page_no');
        $date = date('Y-m-d H:i:s');

        if (!$this->AccountSetting->exists($id)) {

            throw new NotFoundException(__('Invalid account setting'));
        }

        if ($this->request->allowMethod('post', 'delete')) {
            $this->AccountSetting->save(
                array(
                    "id" => $id,
                    "flag" => '0',
                    "updated_date" => $date,
                    "updated_by" => $this->Session->read('LOGIN_ID')
                )
            )['AccountSetting']['menu_id'];

            $successMsg = parent::getSuccessMsg('SS003');
            $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
        } else {
            $errorMsg = parent::getErrorMsg('SE007');
            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
        }

        if (!empty($search_page)) {
            $menu_id = $this->AccountSetting->find('first', array(
                'fields' => array('menu_id'),
                'conditions' => array('id' => $id)
            ))['AccountSetting']['menu_id'];

            $rowCount = $this->AccountSetting->find('count', array(
                'conditions' => array(
                    'menu_id' => $menu_id,
                    'flag' => 1
                )
            ));
            if ($rowCount == 0) {
                $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index'));
            } else {
                return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
            }
        } else {
            $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index'));
        }
    }

    public function getPageData()
    {
        parent::checkAjaxRequest($this);

        $copy_page = $this->request->data('copy_page');

        $page_data = $this->Menu->find('first', array(
            'conditions' => array(
                'page_name' => $copy_page,
                'flag'        => 1
            )
        ));

        if (!empty($page_data)) {
            $response_page = true;
        } else {
            $response_page = false;
        }

        $response = array(
            'response_page' => $response_page
        );

        echo json_encode($response);
    }

    public function CopyAccountSetting()
    {

        $from_page = $this->request->data('hid_page');
        $from_year = $this->request->data('from_year');
        $to_year = $this->request->data('to_year');
        $from_layer = $this->request->data('from_layer_code');
        $to_layer = $this->request->data('to_layer_code');
        $lan = $this->Session->read('Config.language');
        $date = date('Y-m-d H:i:s');
        $login_id = $this->Session->read('LOGIN_ID');
        $search_page = $this->request->data('hid_page');
        $page_no = $this->request->data('hid_page_no');



        try {
            $menuCondition = ($lan == 'eng') ? array('Menu.page_name' => $from_page, 'Menu.flag' => 1) : array('Menu.page_name_jp' => $from_page, 'Menu.flag' => 1);
            $orgMenuId = $this->Menu->find(
                'first',
                array(
                    'fields' => array('Menu.id'),
                    'conditions' => $menuCondition
                )
            )['Menu']['id'];

            $orgAccSetting = $this->AccountSetting->find('all', array(
                'conditions' => array(
                    'AccountSetting.menu_id' => $orgMenuId,
                    'AccountSetting.target_year' => $from_year,
                    'AccountSetting.layer_code' => $from_layer,
                    'AccountSetting.flag' => 1
                )
            ));

            $cpyAccSetting = $this->AccountSetting->find('all', array(
                'conditions' => array(
                    'AccountSetting.menu_id' => $orgMenuId,
                    'AccountSetting.target_year' => $to_year,
                    'AccountSetting.layer_code' => $to_layer,
                    'AccountSetting.flag' => 1
                )
            ));

            if (!empty($orgAccSetting)) {
                $orgAccSet = [];
                $cpyAccSet = [];

                foreach ($orgAccSetting as $value) {
                    array_push($orgAccSet, $value['AccountSetting']['account_id']);
                }
                foreach ($cpyAccSetting as $value) {
                    array_push($cpyAccSet, $value['AccountSetting']['account_id']);
                }
                $dif_data = array_diff($orgAccSet, $cpyAccSet);

                if (!empty($dif_data)) {
                    $resultAccSet = $this->AccountSetting->find(
                        'all',
                        array(
                            'conditions' => array(
                                'AccountSetting.account_id IN' => $dif_data,
                                'AccountSetting.menu_id' => $orgMenuId,
                                'AccountSetting.target_year' => $from_year,
                                'AccountSetting.flag' => 1
                            )
                        )
                    );

                    try {
                        foreach ($resultAccSet as $accSet) {

                            $resAccSet[] = array(
                                "target_year" => $to_year,
                                "layer_code" => $to_layer,
                                "account_id" => $accSet['AccountSetting']['account_id'],
                                "account_type_id" => $accSet['AccountSetting']['account_type_id'],
                                "menu_id" => $orgMenuId,
                                "label_name" => $accSet['AccountSetting']['label_name'],
                                "display_order" => $accSet['AccountSetting']['display_order'],
                                "flag" => $accSet['AccountSetting']['flag'],
                                "created_by" => $login_id,
                                "updated_by" => $login_id,
                                "created_date" => $date,
                                "updated_date"    => $date,
                            );
                        }
                        $this->AccountSetting->saveAll($resAccSet);
                        $successMsg = parent::getSuccessMsg('SS025', [__("データ")]);
                        $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                        return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                    } catch (Exception $e) {
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                        $errorMsg = parent::getErrorMsg('SE137');
                        $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                        return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                    }
                } else {
                    $errorMsg = parent::getErrorMsg('SE138');
                    $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                    return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE139');
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $errorMsg = parent::getErrorMsg('SE140');
            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
        }
    }

    public function OverwirteDataCopy()
    {

        $from_page = $this->request->data('hid_page');
        $from_year = $this->request->data('from_year');
        $to_year = $this->request->data('to_year');
        $from_layer = $this->request->data('from_layer_code');
        $to_layer = $this->request->data('to_layer_code');
        $lan = $this->Session->read('Config.language');
        $date = date('Y-m-d H:i:s');
        $login_id = $this->Session->read('LOGIN_ID');
        $search_page = $this->request->data('hid_page');
        $page_no = $this->request->data('hid_page_no');

        try {
            $menuCondition = ($lan == 'eng') ? array('Menu.page_name' => $from_page, 'Menu.flag' => 1) : array('Menu.page_name_jp' => $from_page, 'Menu.flag' => 1);
            $orgMenuId = $this->Menu->find(
                'first',
                array(
                    'fields' => array('Menu.id'),
                    'conditions' => $menuCondition
                )
            )['Menu']['id'];

            $orgAccSetting = $this->AccountSetting->find('all', array(
                'conditions' => array(
                    'AccountSetting.menu_id' => $orgMenuId,
                    'AccountSetting.target_year' => $from_year,
                    'AccountSetting.layer_code' => $from_layer,
                    'AccountSetting.flag' => 1
                )
            ));

            $cpyAccSetting = $this->AccountSetting->find('first', array(
                'conditions' => array(
                    'AccountSetting.menu_id' => $orgMenuId,
                    'AccountSetting.target_year' => $to_year,
                    'AccountSetting.layer_code' => $to_layer,
                    'AccountSetting.flag' => 1
                )
            ));
            if (!empty($orgAccSetting)) {
                if (!empty($cpyAccSetting)) {
                    try {
                        $AccSetDB = $this->AccountSetting->getDataSource();
                        $AccSetDB->begin();

                        $this->AccountSetting->deleteAll(array(
                            'AccountSetting.menu_id' => $orgMenuId,
                            'AccountSetting.target_year' => $to_year,
                            'AccountSetting.layer_code' => $to_layer,
                            'AccountSetting.flag' => 1
                        ));

                        foreach ($orgAccSetting as $accSet) {

                            $resAccSet[] = array(
                                "target_year" => $to_year,
                                "layer_code" => $to_layer,
                                "account_id" => $accSet['AccountSetting']['account_id'],
                                "account_type_id" => $accSet['AccountSetting']['account_type_id'],
                                "menu_id" => $orgMenuId,
                                "label_name" => $accSet['AccountSetting']['label_name'],
                                "display_order" => $accSet['AccountSetting']['display_order'],
                                "flag" => $accSet['AccountSetting']['flag'],
                                "created_by" => $login_id,
                                "updated_by" => $login_id,
                                "created_date" => $date,
                                "updated_date"    => $date,
                            );
                        }
                        $this->AccountSetting->saveAll($resAccSet);
                        $AccSetDB->commit();

                        $successMsg = parent::getSuccessMsg('SS032');
                        $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                    } catch (Exception $e) {
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        $AccSetDB->rollback();
                        $errorMsg = parent::getErrorMsg('SE141');
                        $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                    }
                } else {
                    try {
                        $AccSetDB = $this->AccountSetting->getDataSource();
                        $AccSetDB->begin();

                        foreach ($orgAccSetting as $accSet) {

                            $resAccSet[] = array(
                                "target_year" => $to_year,
                                "layer_code" => $to_layer,
                                "account_id" => $accSet['AccountSetting']['account_id'],
                                "account_type_id" => $accSet['AccountSetting']['account_type_id'],
                                "menu_id" => $orgMenuId,
                                "label_name" => $accSet['AccountSetting']['label_name'],
                                "display_order" => $accSet['AccountSetting']['display_order'],
                                "flag" => 1,
                                "created_by" => $login_id,
                                "updated_by" => $login_id,
                                "created_date" => $date,
                                "updated_date"    => $date,
                            );
                        }
                        $this->AccountSetting->saveAll($resAccSet);
                        $AccSetDB->commit();

                        $successMsg = parent::getSuccessMsg('SS032');
                        $this->Flash->set($successMsg, array("key" => "AccountsSuccess"));
                    } catch (Exception $e) {
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        $AccSetDB->rollback();
                        $errorMsg = parent::getErrorMsg('SE141');
                        $this->Flash->set($errorMsg, array("key" => "AccountsError"));
                    }
                }
            } else {
                $errorMsg = parent::getErrorMsg('SE143');
                $this->Flash->set($errorMsg, array("key" => "AccountsError"));
            }

            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $errorMsg = parent::getErrorMsg('SE142');
            $this->Flash->set($errorMsg, array("key" => "AccountsError"));
            return $this->redirect(array('controller' => 'AccountSettings', 'action' => 'index/' . $page_no . '?page_name=' . $search_page));
        }
    }

    public function choiceAccount()
    {
        parent::checkAjaxRequest($this);
        $total = $this->request->data('total');
        $normal = $this->request->data('normal');
        $id = $this->request->data('check');
        $response = [];
        $response['check'] = $id;
        $response['total'] = $total;
        $response['normal'] = $normal;
        $datas = $this->AccountSetting->choiceAccount($total, $normal);
        $response['result'] = $datas;

        echo json_encode($response);
    }

    public function getlayers()
    {
        parent::checkAjaxRequest($this);
        $lan = $this->Session->read('Config.language');
        $layer_type = Setting::LAYER_SETTING['bottomLayer'];
        $menu_id = $this->request->data('menu_id');
        $layer_code = $this->request->data('layer_code');
        $page_name =  array_slice(Setting::PAGE_NAME, -2, 2);

        $fields = ($lan == 'eng') ? array("Layer.layer_code", "Layer.name_en") : array("Layer.layer_code", "Layer.name_jp");

        $menu_exist = $this->Menu->find('first', array(
            'conditions' => array(
                'id' => $menu_id,
                'page_name in' => $page_name

            )
        ));
        if ($menu_exist) {
            $response = [];
            $response['layer_code'] = $layer_code;
            $layer_type_name = $this->Layer->find('list', array(
                'fields' => $fields,
                'conditions' => array(
                    'flag' => 1,
                    'type_order' => $layer_type
                )
            ));
            $response['layer_type'] = $layer_type_name;
            if (!empty($layer_code)) {
                echo json_encode($response);
            } else {
                echo json_encode($response);
            }
        } else {
            echo json_encode(null);
        }
    }

    public function checkDuplicate($data)
    {
        $menu_id = $data['page_name'];
        $display_order = $data['display_order'];
        $label_name = $data['label_name'];
        $acc_type = $data['acc_type'];
        $target_year = $data['target_year'];
        $layer_code = $data['layer_type'];
        $id = $data['hd_id'];
        $login_id = $this->Session->read('LOGIN_ID');
        $date = date('Y-m-d H:i:s');

        $checkMenu = $this->Menu->find('first', array(
            'fields' => array('menu_name_en'),
            'conditions' => array('id' => $menu_id)
        ))['Menu']['menu_name_en'];

        if ($checkMenu == 'Budget & Result Management') {
            $dataExConditions['menu_id'] = $menu_id;
            $dataExConditions['account_id'] = $acc_type;
            $dataExConditions['layer_code'] = $layer_code;
            $dataExConditions['target_year'] = $target_year;
            $dataExConditions['flag'] = 1;
        } else {
            $dataExConditions['menu_id'] = $menu_id;
            $dataExConditions['account_id'] = $acc_type;
            $dataExConditions['flag'] = 1;
        }
        if (!empty($id)) {
            $dataExConditions['id <>'] = $id;
        }
        $dataExist = $this->AccountSetting->find('first', array(
            'conditions'  => $dataExConditions
        ));
        if (empty($dataExist)) {
            return true;
        } else {
            return false;
        }
    }

    public function getYearLayer()
    {
        parent::checkAjaxRequest($this);

        $page_name = $this->request->data('page_name');
        $from_year = $this->request->data('from_year');
        $from_layer_code = $this->request->data('from_layer_code');
        $to_year = $this->request->data('to_year');
        $layer_type = Setting::LAYER_SETTING['bottomLayer'];
        $current_year = date('Y');
        $lan = $this->Session->read('Config.language');

        if ($lan == 'eng') {
            $layer_field = array("Layer.layer_code", "Layer.name_en");
            $menuCondition = array('Menu.page_name' => $page_name, 'Menu.flag' => 1, 'Menu.method' => 'index');
        } else {
            $layer_field = array("Layer.layer_code", "Layer.name_jp");
            $menuCondition = array('Menu.page_name_jp' => $page_name, 'Menu.flag' => 1, 'Menu.method' => 'index');
        }
        $orgMenuId = $this->Menu->find(
            'first',
            array(
                'fields' => array('Menu.id'),
                'conditions' => $menuCondition
            )
        )['Menu']['id'];

        $from_range_year = $this->AccountSetting->find('all', array(
            'fields' => array('AccountSetting.target_year as year'),
            'conditions' => array('AccountSetting.menu_id' => $orgMenuId, 'AccountSetting.flag' => 1, 'AccountSetting.target_year !=' => null),
            'group' => array('target_year'),
            'order' => array('target_year DESC')
        ));


        $min_year = $this->AccountSetting->find('all', array(
            'fields' => array('MIN(AccountSetting.target_year) AS min_year'),
            'conditions' => array('AccountSetting.menu_id' => $orgMenuId, 'AccountSetting.flag' => 1),
        ))[0][0]['min_year'];

        $max_year = $this->AccountSetting->find('all', array(
            'fields' => array('MAX(AccountSetting.target_year) AS max_year'),
            'conditions' => array('AccountSetting.menu_id' => $orgMenuId, 'AccountSetting.flag' => 1),
        ))[0][0]['max_year'];

        if ($max_year > $current_year) {
            $to_range_year = range($min_year, $max_year + 5);
        } else {
            $to_range_year = range($min_year, $current_year + 5);
        }

        $layer_type_name = $this->Layer->find('list', array(
            'fields' => $layer_field,
            'conditions' => array('Layer.flag' => 1, 'Layer.type_order' => $layer_type, 'AccountSetting.menu_id' => $orgMenuId),
            'joins' => array(
                array(
                    'table' => 'account_settings',
                    'alias' => 'AccountSetting',
                    'type'  => 'LEFT',
                    'conditions' => array(
                        'AccountSetting.flag = 1',
                        'Layer.layer_code = AccountSetting.layer_code'
                    )
                ),
            ),
            'group' => array('Layer.layer_code'),
            'order' => array('Layer.id DESC')
        ));
        if (!empty($to_year)) {
            if ($from_year == $to_year) {
                $to_lc_conditions =  array('Layer.flag' => 1, 'Layer.type_order' => $layer_type, 'Layer.layer_code !=' => $from_layer_code);
            } else {
                $to_lc_conditions =  array('Layer.flag' => 1, 'Layer.type_order' => $layer_type);
            }
            $to_layer_code = $this->Layer->find('list', array(
                'fields' => $layer_field,
                'conditions' => $to_lc_conditions
            ));
        }

        if (empty($from_year) && empty($from_layer_code)) {
            $response['from_range_year'] = $from_range_year;
            $response['from_layer_type'] = $layer_type_name;
            $response['to_range_year'] = $to_range_year;
        } else if (!empty($to_year) && !empty($from_layer_code)) {
            $response['to_layer_type'] = $to_layer_code;
        } else {
            $response['to_layer_type'] = '';
        }

        echo json_encode($response);
    }

    public function OverwriteCheck()
    {
        parent::checkAjaxRequest($this);

        $to_year = $this->request->data('to_year');
        $to_layer_code = $this->request->data('to_layer');

        if (!empty($to_layer_code)) {
            $conditions = array('flag' => 1, 'target_year' => $to_year, 'layer_code' => $to_layer_code);
        } else {
            $conditions = array('flag' => 1, 'target_year' => $to_year);
        }

        $data = $this->AccountSetting->find('all', array(
            'conditions' => $conditions
        ));

        if (!empty($data) && !empty($to_layer_code)) {
            $response['check'] = true;
        } else {
            $response['check'] = false;
        }
        echo json_encode($response);
    }
}
