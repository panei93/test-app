<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * MailFlowSettings Controller
 *
 * @property PaginatorComponent Paginator
 */
class MailFlowSettingsController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $uses = array('Role','LayerType','Permission','Mail','MailReceiver','Menu');
    public $components = array('Session', 'Flash','Paginator','PhpExcel.PhpExcel');
    public $helpers = array('Html', 'Form', 'Session');
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkSettingSession($this->name);
    }

    /**
     * index method
     *
     * @author WaiWaiMoe
     * @created_date 2022/05/16
     * @return void
     */

    public function index()
    {
        $this->layout = 'mastermanagement';
        $language     = $this->Session->read('Config.language');
        $mail_vars = [];
        $search_bar = false;
        #get page name and phase name in search box
        $phase_name = !empty($this->request->data('s_phase'))? $this->request->data('s_phase') : $this->request->query('s_phase');
        $page_name  = !empty($this->request->data('s_page'))? $this->request->data('s_page') : $this->request->query('s_page');
        $conditions = array();
        $layer_type_limit = Setting::LAYER_TYPE_LIMIT;
        if ($phase_name != null) {
            if($language == 'eng') $conditions["Menu.menu_name_en"] = $phase_name;
            else $conditions["Menu.menu_name_jp"] = $phase_name;
            #set session to get search value when pagination
            $this->Session->write('SEARCH_PHASE', $phase_name);
        } 
        if ($page_name != null) {
            if($language == 'eng') $conditions["Menu.page_name"]  = $page_name;
            else $conditions["Menu.page_name_jp"]  = $page_name;
            #set session to get search value when pagination
            $this->Session->write('SEARCH_PAGE', $page_name);
        } 
        if ($phase_name == null && $page_name == null && $this->request->data('hidSearch') == 'SEARCHALL') {
            $this->Session->write('SEARCH_PHASE', '');
            $this->Session->write('SEARCH_PAGE', '');
        } else if ($phase_name != null && $page_name == null) {
            $this->Session->write('SEARCH_PAGE', '');
        }
        #get search value for pagination
        if ($this->Session->read('SEARCH_PHASE') != null) {
            if ($language == 'eng') $conditions["Menu.menu_name_en"] = $this->Session->read('SEARCH_PHASE');
            else $conditions["Menu.menu_name_jp"] = $this->Session->read('SEARCH_PHASE');
            $phase_name = $this->Session->read('SEARCH_PHASE');
            $this->Session->write('SEARCH_PHASE', '');
        } 
        if ($this->Session->read('SEARCH_PAGE') != null) {
            if ($language == 'eng') $conditions["Menu.page_name"]  = $this->Session->read('SEARCH_PAGE');
            else $conditions["Menu.page_name_jp"]  = $this->Session->read('SEARCH_PAGE');
            
            $page_name = $this->Session->read('SEARCH_PAGE');
            $this->Session->write('SEARCH_PAGE', '');
        }

        #get menu name
        $phase = $this->Menu->query("Select id,menu_name_en,menu_name_jp from menus where flag = 1 and (menu_name_en != 'Setting Management' or menu_name_jp != '設定管理') group by menu_name_en");
        #add phase value for search box
        $search_phase = $this->Mail->query("Select distinct menu.id as id,menu_name_en,menu_name_jp from mails left join menus as menu on mails.mail_code = menu.mail_code where menu.flag = 1 group by menu_name_en");
        if($language == 'jpn'){
            $button_type  = Setting::BUTTONS_JP;
            $menu_name = Setting::MENU_NAME_JP;
            if(in_array("拒否", $button_type)) {
                $index = array_search("拒否", $button_type);
                $button_type[$index] = "差し戻し";
            }
            if(in_array("レビュー", $button_type)) {
                $index = array_search("レビュー", $button_type);
                $button_type[$index] = "確認";
            }
            // $period_dealine = Setting::PERIOD_DEADLINE;
        } else {
            $button_type  = Setting::BUTTONS;
            $menu_name = Setting::MENU_NAME_EN;
            // $period_dealine = Setting::PERIOD_DEADLINE;
            # change from reject to revert (20230609-FRI-KHM)
            if(in_array("Reject", $button_type)) {
                $index = array_search("Reject", $button_type);
                $button_type[$index] = "Revert";
            }
            if(in_array("Review", $button_type)) {
                $index = array_search("Review", $button_type);
                $button_type[$index] = "Check";
            }
        }
        $admin_levels = $this->Role->find("list",array(
            'fields' => array('id','read_limit','role_name'),
            'conditions' => array('flag' => 1)
        ));
        $read_limit = array_values(
			$this->Role->find('list', array(
				'fields' => 'read_limit',
				'conditions' => array('Role.flag' => 1)
			))
		);
        #get mail code from tbl_mail_setting table
        $mail_code = $this->Mail->find("list",array(
            'fields'     => array('mail_code'),
            'conditions' => array('flag' => 1),
            'group'      => array('mail_code')
        ));
        
        #get layers data from tbl_layer table
        $layers = $this->LayerType->find('list',array(
            'fields' => array('name_jp','name_en','type_order'),
            'conditions' => array(
                'type_order BETWEEN ? AND ?' => array($read_limit, $layer_type_limit),
                'flag' => 1
            ),
            'order' => 'type_order',
        ));
        $layer_data[0] = ($language== 'eng') ? 'Whole Company' : '全社';
        #prepare data according to language
        foreach ($layers as $layer_no => $layer_name) {
            $name_jp = array_keys($layer_name)[0];
            $name_en = array_values($layer_name)[0];
            $layer_data[$layer_no] = ($language== 'eng') ? 'Same '.$name_en : '該当'.$name_jp;
            
            $mail_value = ($language== 'eng') ? $name_en : $name_jp;
            $mail_param = strtolower(str_replace(' ', '_', $name_en));
            $mail_vars[$mail_param] = $mail_value;
        }
        $conditions["Menu.flag"]  = 1;
        $this->Menu->virtualFields['method'] = 'IF(Menu.method = "reject", "revert", IF(Menu.method = "review", "check", Menu.method))';
        $this->Menu->virtualFields['method_jp'] = 'IF(Menu.method_jp = "拒否", "差し戻し", IF(Menu.method_jp = "レビュー", "確認", Menu.method_jp))';
        #get permission data
        $this->paginate = array(
            'limit'      => Paging::TABLE_PAGING,
            'conditions' => $conditions,
            'fields' => 'Menu.id,Menu.page_name,Menu.page_name_jp,Menu.method,Menu.method_jp,
            Menu.menu_name_en,Menu.menu_name_jp,Menu.mail_code',
            'joins' => array(
                array(
                    'table' => 'mails',
                    'alias' => 'Mails',
                    'conditions' => array(
                        'Menu.mail_code = Mails.mail_code',
                        'Mails.flag' => 1
                    )
                ),
            ),
            'group' => array('Menu.id'),
            'order' => array('Menu.id' => 'ASC')
            );
        $data_list = h($this->Paginator->paginate('Menu'));
        $rowCount = $this->params['paging']['Menu']['count'];
        $show_searchbtn = ($rowCount == 0) ? false : true;
        $this->Menu->virtualFields['method'] = 'Menu.method';
        $this->Menu->virtualFields['method_jp'] = 'Menu.method_jp';
        if(!empty($phase_name) && $rowCount == 0) {
            return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'));
        }
        if ($rowCount == 0) {
            $this->set('errmsg', parent::getErrorMsg('SE001'));
            $this->set('succmsg', '');
        } else {
            $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
            $this->set('errmsg', '');
        }
        $page = $this->params['paging']['Menu']['page'];
        $limit = $this->params['paging']['Menu']['limit'];
        $this->set(compact('phase','search_phase','phase_name','page_name','mail_vars', 'button_type','menu_name','deadline','layer_data','language','admin_levels','mail_code','data_list','rowCount','page','limit', 'show_searchbtn'));
        $this->render('index');
    }

    /**
     * ajax method to get pagename according to phase
     *
     * @author WaiWaiMoe
     * @created_date 2022/05/23
     * @return data
     */
    public function getPageName() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $language       = $this->Session->read('Config.language');
        $phase_id       = $this->request->data['phase_id'];
        $name           = $this->request->data['name'];
        $mail_vars      = [];
        if($language == 'jpn'){
            $menu_name = Setting::MENU_NAME_JP;
        } else {
            $menu_name = Setting::MENU_NAME_EN;
        }
        
        if ($name != "Search") {
            #get page name from tbl_page table
            if($language == 'jpn'){
                $menu_array = $this->Menu->find("list",array(
                    'fields' => array('page_name_jp','layer_no'),
                    'conditions' => array('flag' => 1, 'method !=' => 'index', 'menu_name_jp = "'.$phase_id.'"')
                ));
            } else {
                $menu_array = $this->Menu->find("list",array(
                    'fields' => array('page_name','layer_no'),
                    'conditions' => array('flag' => 1, 'method !=' => 'index','menu_name_en = "'.$phase_id.'"')
                ));
            }
            
            $page_name     = array_keys($menu_array);
            $layer_setting = $menu_array[$page_name[0]];
            
            #get layers data from tbl_layer table
            $layers = $this->LayerType->find('list',array(
                'fields' => array('name_jp','name_en','type_order'),
                'conditions' => array('flag' => 1,'type_order <=' => $layer_setting[$phase_id]),
                'order' => 'type_order',
            ));

            if(!empty($phase_id)) {
                $layer_data[0] = ($language== 'eng') ? 'Whole Company' : '全社';
            } else {
                $layer_data = [];
            }
            #prepare data according to language
            foreach ($layers as $layer_no => $layer_name) {
                $name_jp = array_keys($layer_name)[0];
                $name_en = array_values($layer_name)[0];
                $layer_data[$layer_no] = ($language== 'eng') ? 'Same '.$name_en : '該当'.$name_jp;
                
                $mail_value = ($language== 'eng') ? $name_en : $name_jp;
                $mail_param = strtolower(str_replace(' ', '_', $name_en));
                $mail_vars[$mail_param] = $mail_value;
            }
        } else {
            #get page name from permissions table for search
            /*if($language == 'eng'){
                $page_name = $this->Menu->find("list",array(
                    'fields' => array('Menu.page_name'),
                    'conditions' => array('Menu.mail_flag'=>'ON','Menu.menu_name_en = "'.$phase_id.'"'),
                    'group' => array('Menu.page_name')
                ));
            } else {
                $page_name = $this->Menu->find("list",array(
                    'fields' => array('Menu.page_name_jp'),
                    'conditions' => array('Menu.mail_flag'=>'ON','Menu.menu_name_jp = "'.$phase_id.'"'),
                    'group' => array('Menu.page_name_jp')
                ));
            }*/
            $menu_condi = [];
            $menu_condi['flag'] = 1;
            if($language == 'eng') {
                $menu_condi['Menu.menu_name_en'] = $phase_id;
                $group_order = array('Menu.page_name');
            }else {
                $menu_condi['Menu.menu_name_jp'] = $phase_id;
                $group_order = array('Menu.page_name_jp');
            }

            $page_name = $this->Menu->find('list',array(
                'fields' => $group_order,
                'conditions' => $menu_condi,
                'group' => $group_order,
            ));
            
            $page_name = array_unique($page_name);
        } 
        echo json_encode(array($page_name,$layer_data,$mail_vars,$menu_name));
    }

    /**
     * ajax method to get button type according to page name
     *
     * @author WaiWaiMoe
     * @created_date 2022/06/22
     * @return data
     */
    public function getButtonType() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $result    = [];
        $language       = $this->Session->read('Config.language');
        $page_name = $this->request->data['page_name'];
        $this->Menu->virtualFields['method'] = 'IF(Menu.method = "reject", "revert", IF(Menu.method = "review", "check", Menu.method))';
        $this->Menu->virtualFields['method_jp'] = 'IF(Menu.method_jp = "拒否", "差し戻し", IF(Menu.method_jp = "レビュー", "確認", Menu.method_jp))';
        if($language == 'eng'){
            $btn_data  = $this->Menu->find("list",array(
                'fields'     => array('id','method'),
                'conditions' => array('flag' => 1,'page_name' => $page_name,'method !=' => 'index'),
            ));
        } else {
            $btn_data  = $this->Menu->find("list",array(
                'fields'     => array('id','method_jp'),
                'conditions' => array('flag' => 1,'page_name_jp' => $page_name,'method_jp !=' => '画面表示'),
            ));
        }
        $this->Menu->virtualFields['method'] = 'Menu.method';
        $this->Menu->virtualFields['method_jp'] = 'Menu.method_jp';
        if(empty($btn_data)) $btn_data[] = '';
        echo(json_encode($btn_data));
    }

    /**
     * ajax method to get mail info according to mail_id
     *
     * @author WaiWaiMoe
     * @created_date 2022/06/08
     * @return json data
     */
    public function getMailInfo() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $mail_code = $this->request->data['mail_code'];
        $result  = [];
        #get mail info based on mail_id
        $mail_info = $this->Mail->find("all",array(
            'fields'     => array('Mail.mail_type','Mail.mail_subject','Mail.mail_body','MailReceiver.role_id','MailReceiver.mail_send_type','MailReceiver.mail_limit'),
            'joins' => array(
                array(
                    'table' => 'mail_receivers',
                    'alias' => 'MailReceiver',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'MailReceiver.mail_id = Mail.id',
                        'MailReceiver.flag' => 1
                    )
                ),
            ),
            'conditions' => array('Mail.flag' => 1,'Mail.mail_code' => $mail_code),
        ));
        if(!empty($mail_info)) {
            $level_to = $level_cc = $level_bcc = [];
            foreach ($mail_info as $mail) {
                $result['mail_type']        = $mail['Mail']['mail_type'];
                $result['mail_subject']     = $mail['Mail']['mail_subject'];
                $result['mail_body']        = str_replace("(_*_)", "\r\n", $mail['Mail']['mail_body']);
                #check mail send type To/Cc/Bcc 
                if ($mail['MailReceiver']['mail_send_type'] == 'to') {
                    $level_to[$mail['MailReceiver']['role_id']] = $mail['MailReceiver']['mail_limit'];
                } 
                if ($mail['MailReceiver']['mail_send_type'] == 'cc') {
                    $level_cc[$mail['MailReceiver']['role_id']] = $mail['MailReceiver']['mail_limit'];
                } 
                if ($mail['MailReceiver']['mail_send_type'] == 'bcc') {
                    $level_bcc[$mail['MailReceiver']['role_id']] = $mail['MailReceiver']['mail_limit'];
                }
            }
            $result['to_level_info']  = $level_to;
            $result['cc_level_info']  = $level_cc;
            $result['bcc_level_info'] = $level_bcc;
        }
        echo json_encode($result);
    }

    /*
     * save data into permissions and tbl_mail_setting tables
     *
     * @author WaiWaiMoe
     * @created_date 2022/05/24
     * @return boolean
     */
    public function saveMail()
    {
        if ($this->request->is('POST')) {
            $link          = "";
            $page_no       = $this->request->data('hid_page_no');
            $login_id      = $this->Session->read('LOGIN_ID');
            $phase         = $this->request->data['phase'];
            $page_name     = $this->request->data['page_name'];
            $button_type   = array_unique($this->request->data['button_type']);
            $mail_code     = $this->request->data['m_code'];
            $mail_subject  = $this->request->data['m_subject'];
            $mail_body     = str_replace("\r\n", "(_*_)", $this->request->data['email_body']);
            $mail_receiver = ($this->request->data['mail_receiver']) ? $this->request->data['mail_receiver'] : $this->request->data['hid_receiver'];
            $mail_to       = $this->request->data['receive_to'];
            $mail_cc       = $this->request->data['receive_cc'];
            $mail_bcc      = $this->request->data['receive_bcc'];
            #get page name and phase name in search box
            $phase_name = !empty($this->request->data('s_phase'))? $this->request->data('s_phase') : null;
            $pg_name    = !empty($this->request->data('s_page'))? $this->request->data('s_page') : null;
            if ($phase_name != null) {
                $link   = $link."?s_phase=".urlencode($phase_name);
            }
            if ($pg_name != null) {
                $link   = $link."&s_page=".$pg_name;
            } #concat page number with link

            #check duplicate data exist or not
            $language       = $this->Session->read('Config.language');
            $this->Menu->virtualFields['method'] = 'IF(Menu.method = "reject", "revert", IF(Menu.method = "review", "check", Menu.method))';
            $this->Menu->virtualFields['method_jp'] = 'IF(Menu.method_jp = "拒否", "差し戻し", IF(Menu.method_jp = "レビュー", "確認", Menu.method_jp))';
                foreach ($button_type as $menuid) {
                if ($language == 'jpn') {
                    $duplicated_data = $this->Menu->find('first', array(
                        'fields' => array('Menu.mail_code','Menu.id', 'Menu.method_jp'),
                        'join' => array(
                            array(
                                'table' => 'mails',
                                'alias' => 'Mail',
                                'type' => 'LEFT',
                                'conditions' => array(
                                    'Mail.mail_code = Menu.mail_code',
                                    'Mail.flag' => 1
                                )
                            ),
                        ),
                        'conditions' => array(
                            'Menu.menu_name_jp' => $phase,
                            'Menu.page_name_jp' => $page_name,
                            'Menu.id IN ('.$menuid.')',
                            'Menu.flag' => 1,
                            array('OR' =>  array('Menu.mail_code <>' => null, 'Menu.mail_code <>' => ''))
                        )
                    ));
                    if(!empty($duplicated_data)) {
                        if($menuid === $duplicated_data['Menu']['id']){
                            $msg = parent::getErrorMsg('SE136', __($duplicated_data['Menu']['method_jp']));
                            $this->Flash->set($msg, array('key'=>'MailFail'));
                            return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                        }
                        $msg = parent::getErrorMsg('SE135', __("Mail setting"));
                        $this->Flash->set($msg, array('key'=>'MailFail'));
                        return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                    }       
                } else {
                    $duplicated_data = $this->Menu->find('first', array(
                        // 'fields' => array('Menu.mail_code','Menu.id','Menu.method'),
                        'join' => array(
                            array(
                                'table' => 'mails',
                                'alias' => 'Mail',
                                'type' => 'LEFT',
                                'conditions' => array(
                                    'Mail.mail_code = Menu.mail_code',
                                    'Mail.flag' => 1
                                )
                            ),
                        ),
                        'conditions' => array(
                            'Menu.menu_name_en' => $phase,
                            'Menu.page_name' => $page_name,
                            'Menu.id IN ('.$menuid.')',
                            'Menu.flag' => 1,
                            array('OR' =>  array('Menu.mail_code <>' => null, 'Menu.mail_code <>' => ''))
                        )
                    ));
                    if(!empty($duplicated_data)) {
                        if($menuid === $duplicated_data['Menu']['id']){
                            $msg = parent::getErrorMsg('SE136', __($duplicated_data['Menu']['method']));
                            $this->Flash->set($msg, array('key'=>'MailFail'));
                            return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                        }
                        $msg = parent::getErrorMsg('SE135', __("Mail setting"));
                        $this->Flash->set($msg, array('key'=>'MailFail'));
                        return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                    }
                }
            }
            $this->Menu->virtualFields['method'] = 'Menu.method';
            $this->Menu->virtualFields['method_jp'] = 'Menu.method_jp';
            $link =($page_no != null ? "/".$page_no.$link : $link);
            $check_result = array();
            $save_menu_data = [];
            $save_id = '';
            foreach($button_type as $btype) {
                $save_id = $btype;
                // if(!empty($res)) array_push($check_result, $res);

                $save_data = [];
                $menu_data = $this->Menu->find('first', array(
                    'conditions' => array(
                        'Menu.id' => $btype
                    )
                )
                );
                if (!empty($menu_data))
                    array_push($save_data, $menu_data);
                $save_menu_data = array_column($save_data, "Menu");
            }

            $MailDB = $this->Mail->getDataSource();
            $MailReceiverDB = $this->MailReceiver->getDataSource();
            $MenuDB = $this->Menu->getDataSource();
            try {
                $MailDB->begin();
                $MailReceiverDB->begin();
                $MenuDB->begin();


                // if (empty($check_result)) {
                    // $mail_id = null;
                    foreach($button_type as $btype) {
                        #save data into mail table
                        $existing_mail_code = array_values($this->Mail->find('list', array(
                            'fields' => array('mail_code'),
                            'conditions' => array('mail_code'=> $mail_code)
                        )))[0];
                            
                            if (empty($existing_mail_code)) {
                                $mail_data = [];
                                $mail_data['mail_type']        = $mail_receiver;
                                $mail_data['mail_code']        = $mail_code;
                                $mail_data['mail_subject']     = $mail_subject;
                                $mail_data['mail_body']        = $mail_body;
                                $mail_data['flag']             = 1;
                                $mail_data['created_by']       = $login_id;
                                $mail_data['updated_by']       = $login_id;
                                $mail_data['created_date']     = date('Y-m-d H:i:s');
                                $mail_data['updated_date']     = date('Y-m-d H:i:s');
                                #save data in tbl_mail_setting table

                                $status = $this->Mail->saveAll($mail_data); 
                                $mail_id = $this->Mail->getLastInsertId(); 
                                if (!$status) {
                                    $msg = parent::getErrorMsg('SE011', __("変更"));
                                    $this->Flash->set($msg, array('key'=>'MailFail'));
                                    $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                                } 

                                #prepare data for mail_receivers table
                                $mail_id = current($this->Mail->find('list',array(
                                    'fields' => array('id'),
                                    'conditions' => array(
                                        'Mail.mail_type'=> $mail_receiver,
                                        'Mail.mail_code'=> $mail_code,
                                        'Mail.flag'     => 1
                                    ),
                                )));
                                #prepare data to in mail_receivers table
                                $mail_receiver_list = [];
                                #loop if not empty email_to list
                                if(!empty($mail_to)) {
                                    foreach($mail_to as $level => $mail_limit) {
                                        $temp = [];
                                        $temp['mail_id']        = $mail_id;
                                        $temp['role_id']        = $level;
                                        $temp['mail_send_type'] = "to";
                                        $temp['mail_limit']     = $mail_limit;
                                        $temp['flag']           = 1;
                                        $temp['created_by']     = $login_id;
                                        $temp['updated_by']     = $login_id;
                                        $temp['created_date']   = date('Y-m-d H:i:s');
                                        $temp['updated_date']   = date('Y-m-d H:i:s');
                                        array_push($mail_receiver_list,$temp);
                                    }
                                }
                                #loop if not empty email_cc list
                                if(!empty($mail_cc)) {
                                    foreach($mail_cc as $level => $mail_limit) {
                                        $temp = [];
                                        $temp['mail_id']        = $mail_id;
                                        $temp['role_id']        = $level;
                                        $temp['mail_send_type'] = "cc";
                                        $temp['mail_limit']     = $mail_limit;
                                        $temp['flag']           = 1;
                                        $temp['created_by']     = $login_id;
                                        $temp['updated_by']     = $login_id;
                                        $temp['created_date']   = date('Y-m-d H:i:s');
                                        $temp['updated_date']   = date('Y-m-d H:i:s');
                                        array_push($mail_receiver_list,$temp);
                                    }
                                }
                                #loop if not empty email_bcc list
                                if(!empty($mail_bcc)) {
                                    foreach($mail_bcc as $level => $mail_limit) {
                                        $temp = [];
                                        $temp['mail_id']        = $mail_id;
                                        $temp['role_id']        = $level;
                                        $temp['mail_send_type'] = "bcc";
                                        $temp['mail_limit']     = $mail_limit;
                                        $temp['flag']           = 1;
                                        $temp['created_by']     = $login_id;
                                        $temp['updated_by']     = $login_id;
                                        $temp['created_date']   = date('Y-m-d H:i:s');
                                        $temp['updated_date']   = date('Y-m-d H:i:s');
                                        array_push($mail_receiver_list,$temp);
                                    }
                                }
                                #save data in mail_receivers table
                                $this->MailReceiver->create();
                                $status = $this->MailReceiver->saveAll($mail_receiver_list);
                                if (!$status) {
                                    $msg = parent::getErrorMsg('SE003');
                                    $this->Flash->set($msg, array('key'=>'MailFail'));
                                    $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                                }
                            } 

                        if(!empty($save_menu_data)) {
                            #save mail_code into Menu table
                            $save_menu = [];
                            $save_menu['id'] = $btype;
                            $save_menu['menu_name_en'] = $save_menu_data[0]['menu_name_en'];
                            $save_menu['menu_name_jp'] = $save_menu_data[0]['menu_name_jp'];
                            $save_menu['page_name'] = $save_menu_data[0]['page_name'];
                            $save_menu['page_name'] = $save_menu_data[0]['page_name'];
                            $save_menu['mail_code'] = $mail_code;
                            $save_menu['mail_flag'] = "ON";
                            $save_menu['flag'] = $save_menu_data[0]['flag'];
                            $save_menu['created_by'] = $save_menu_data[0]['created_by'];
                            $save_menu['updated_date'] = date('Y-m-d H:i:s');
                            $save_menu['created_date'] = $save_menu_data[0]['created_date'];
                            $save_menu['updated_date'] = date('Y-m-d H:i:s');
                            #save into menu table
                            $status = $this->Menu->save($save_menu); 
                            if (!$status) {
                                $msg = parent::getErrorMsg('SE003');
                                $this->Flash->set($msg, array('key'=>'MailFail'));
                                $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                            } 
                        }

                    }

                    $MailDB->commit();
                    $MailReceiverDB->commit();
                    $MenuDB->commit();
    
                    $msg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($msg, array('key'=>'MailSuccess'));
                    $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));  
                // } else {
                    // $msg = parent::getErrorMsg('SE002', __('データ'));
                    // $this->Flash->set($msg, array('key'=>'MailFail'));
                    // $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));  
                // }

            } catch (Exception $e) {
                $MailDB->rollback();
                $MailReceiverDB->rollback();
                $MenuDB->rollback();

                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg('SE003');
                $this->Flash->set($msg, array('key'=>'MailFail'));
                $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
            }
        }
    }

    /**
     * get mail data
     *
     * @author WaiWaiMoe
     * @created_date 2022/05/31
     * @return json array
     */
    public function getMailDetailData() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $language = $this->Session->read('Config.language');
        $mail_code  = $this->request->data['mail_code'];

        $check    = ($language == 'eng' ? 1 : 0);
        $result   = [];
       
        #get mail detail data 
        $mail_list = $this->Mail->find("all",array(
            'fields' => array('Mail.mail_subject','Mail.mail_body','MailReceiver.mail_send_type',
            'case when 1='.$check.' Then case when (MailReceiver.mail_limit = 0) Then "Whole Company" Else concat("Same ",Layer.name_en) END
            Else Case when (MailReceiver.mail_limit = 0) Then "全社" Else concat("該当 ",Layer.name_jp)END END as layer_name',
            'Role.role_name'
            ),
            'joins' => array(
                array(
                    'table' => 'mail_receivers',
                    'alias' => 'MailReceiver',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'MailReceiver.mail_id = Mail.id',
                        'MailReceiver.flag' => 1
                    )
                ),
                array(
                    'table' => 'layer_types',
                    'alias' => 'Layer',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Layer.type_order = MailReceiver.mail_limit',
                        'Layer.flag' => 1
                    )
                ),
                array(
                    'table' => 'roles',
                    'alias' => 'Role',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Role.id = MailReceiver.role_id',
                        'Role.flag' => 1
                    )
                ),
            ),
            'conditions' => array('Mail.flag' => 1,'Mail.mail_code' => $mail_code)
        ));

        if(!empty($mail_list)) {
            $to = $cc = $bcc = [];
            foreach($mail_list as $mail) {
                $result['mail_subject'] = $mail['Mail']['mail_subject'];
                $result['mail_body']    = str_replace("(_*_)", "\r\n", $mail['Mail']['mail_body']);
                if($mail['MailReceiver']['mail_send_type'] == 'to') {
                    array_push($to,$mail[0]['layer_name']."の".$mail['Role']['role_name']);
                }
                if($mail['MailReceiver']['mail_send_type'] == 'cc') {
                    array_push($cc,$mail[0]['layer_name']."の".$mail['Role']['role_name']);
                }
                if($mail['MailReceiver']['mail_send_type'] == 'bcc') {
                    array_push($bcc,$mail[0]['layer_name']."の".$mail['Role']['role_name']);
                }
            }
            $result['to'] = implode(",",$to);
            $result['cc'] = implode(",",$cc);
            $result['bcc']= implode(",",$bcc);
        }
        echo json_encode($result);
    }

    /**
     * get edit data list
     *
     * @author WaiWaiMoe
     * @created_date 2022/05/31
     * @return json array
     */
    public function getEditData() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $language       = $this->Session->read('Config.language');
        $id             = $this->request->data['id'];
        $mail_code      = $this->request->data['mail_code'];
        
        $result         = [];
        $this->Mail->virtualFields['method'] = 'IF(Menus.method = "reject", "revert", IF(Menus.method = "review", "check", Menus.method))';
        $this->Mail->virtualFields['method_jp'] = 'IF(Menus.method_jp = "拒否", "差し戻し", IF(Menus.method_jp = "レビュー", "確認", Menus.method_jp))';
        $edit_data      = $this->Mail->find("all",array(
            'fields' => array('Mail.id','Mail.mail_code','Menus.id','Menus.menu_name_en','Menus.menu_name_jp','Menus.page_name','Menus.page_name_jp','Menus.method','Menus.method_jp','Mail.mail_type','Mail.mail_subject','Mail.mail_body', 'Mail.method', 'Mail.method_jp'
            ),
            'joins' => array(
                array(
                    'table' => 'mail_receivers',
                    'alias' => 'MailReceiver',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'MailReceiver.mail_id = Mail.id',
                        'MailReceiver.flag' => 1
                    )
                ),
                array(
                    'table' => 'menus',
                    'alias' => 'Menus',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Menus.mail_code = Mail.mail_code AND Menus.flag = 1',
                        'Menus.id ' => $id
                    )
                ),
            ),
            'conditions' => array('Mail.flag'=> 1, 'Mail.mail_code' => $mail_code),
            'group'      => array('Mail.id','Mail.mail_code'),
        ));
        if(!empty($edit_data)) {
            $level_to = $level_cc = $level_bcc = [];
            foreach ($edit_data as $edit) {
                $result['id'] = $edit['Menus']['id'];
                $result['mail_id']          = $edit['Mail']['id'];
                $result['menu_name']        = ($language == 'eng') ? $edit['Menus']['menu_name_en'] : $edit['Menus']['menu_name_jp'];
                $result['page']             = ($language == 'eng') ? $edit['Menus']['page_name'] : $edit['Menus']['page_name_jp'];
                $result['function']         = ($language == 'eng') ? $edit['Mail']['method'] : $edit['Mail']['method_jp'] ;
                $result['mail_type']        = $edit['Mail']['mail_type'];
                $result['mail_code']        = $edit['Mail']['mail_code'];
                $result['mail_subject']     = $edit['Mail']['mail_subject'];
                $result['mail_body']        = str_replace("(_*_)", "\r\n", $edit['Mail']['mail_body']);
                foreach ($edit['MailReceiver'] as $mailReceiver) {
                    #check mail send type To/Cc/Bcc 
                    if ($mailReceiver['mail_send_type'] == 'to') {
                        $level_to[$mailReceiver['role_id']] = $mailReceiver['mail_limit'];
                    } 
                    if ($mailReceiver['mail_send_type'] == 'cc') {
                        $level_cc[$mailReceiver['role_id']] = $mailReceiver['mail_limit'];
                    } 
                    if ($mailReceiver['mail_send_type'] == 'bcc') {
                        $level_bcc[$mailReceiver['role_id']] = $mailReceiver['mail_limit'];
                    }
                }
            }
            $result['to_level_info']  = $level_to;
            $result['cc_level_info']  = $level_cc;
            $result['bcc_level_info'] = $level_bcc;
        }
        echo json_encode($result);
    }

     /**
     * update data into permissions and tbl_mail_setting tables
     *
     * @author WaiWaiMoe
     * @created_date 2022/06/01
     * @return boolean
     */
    public function updateMail() {
        if ($this->request->is('POST')) {
            $link          = "";
            $page_no       = $this->request->data('hid_page_no');
            $login_id      = $this->Session->read('LOGIN_ID');
            $updated_id    = explode(",",$this->request->data['hid_update_id']);
            $phase         = $this->request->data['phase'];
            $page_name     = $this->request->data['page_name'];
            $button_type = $this->request->data['edit_button_type'];
            $mail_code     = $this->request->data['m_code'];
            $mail_subject  = $this->request->data['m_subject'];
            $mail_body     = str_replace("\r\n", "(_*_)", $this->request->data['email_body']);
            //echo $mail_body;exit;
            $mail_receiver = ($this->request->data['mail_receiver']) ? $this->request->data['mail_receiver'] : $this->request->data['hid_receiver'];
            $mail_to       = $this->request->data['receive_to'];
            $mail_cc       = $this->request->data['receive_cc'];
            $mail_bcc      = $this->request->data['receive_bcc'];
            #get page name and phase name in search box
            $phase_name = !empty($this->request->data('s_phase'))? $this->request->data('s_phase') : null;
            $pg_name    = !empty($this->request->data('s_page'))? $this->request->data('s_page') : null;
            if ($phase_name != null) {
                $link   = $link."?s_phase=".urlencode($phase_name);
            }
            if ($pg_name != null) {
                $link   = $link."&s_page=".$pg_name;
            }#concat page number with link
            $link =($page_no != null ? "/".$page_no.$link : $link);
            
            $action_name = $this->Menu->find('first',array(
                'fields' => 'method',
                'conditions' => array(
                    'Menu.id' => $button_type,
                )
            ));
            $this->Menu->virtualFields['method'] = 'IF(Menu.method = "reject", "revert", IF(Menu.method = "review", "check", Menu.method))';
            $this->Menu->virtualFields['method_jp'] = 'IF(Menu.method_jp = "拒否", "差し戻し", IF(Menu.method_jp = "レビュー", "確認", Menu.method_jp))';
            #check duplicate data exist or not
            $language = $this->Session->read('Config.language');
            if ($language == 'jpn') {
                $duplicated_data = $this->Menu->find('first', array(
                    'fields' => array('Menu.id'),
                    'join' => array(
                        array(
                            'table' => 'mails',
                            'alias' => 'Mail',
                            'type' => 'LEFT',
                            'conditions' => array(
                                'Mail.mail_code = Menu.mail_code',
                                'Mail.flag' => 1
                            )
                        ),
                    ),
                    'conditions' => array(
                        'Menu.menu_name_jp' => $phase,
                        'Menu.page_name_jp' => $page_name,
                        'Menu.method' => $action_name['Menu']['method_jp'],
                        'Menu.id !=' => $updated_id,
                        'Menu.flag' => 1
                    )
                ));
                if(!empty($duplicated_data)) {
                    $msg = parent::getErrorMsg('SE135', __("Mail setting"));
                    $this->Flash->set($msg, array('key'=>'MailFail'));
                    return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                }       
            } else {
                $duplicated_data = $this->Menu->find('count', array(
                    // 'fields' => array('Menu.id'),
                    'join' => array(
                        array(
                            'table' => 'mails',
                            'alias' => 'Mail',
                            'type' => 'LEFT',
                            'conditions' => array(
                                'Mail.mail_code = Menu.mail_code',
                                'Mail.flag' => 1
                            )
                        ),
                    ),
                    'conditions' => array(
                        'Menu.menu_name_en' => $phase,
                        'Menu.page_name' => $page_name,
                        'Menu.method' => $action_name['Menu']['method'],
                        'Menu.id !=' => $updated_id,
                        'Menu.flag' => 1,
                        'Menu.mail_code !=' => '',
                    )
                ));
                // print_r($duplicated_data);die();
                if($duplicated_data > 0) {
                    $msg = parent::getErrorMsg('SE135', __("Mail setting"));
                    $this->Flash->set($msg, array('key'=>'MailFail'));
                    return $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                }
            }
            $this->Menu->virtualFields['method'] = 'Menu.method';
            $this->Menu->virtualFields['method_jp'] = 'Menu.method_jp';
            $db_menu_data = ($this->Menu->find('first', array(
                'conditions' => array(
                    'Menu.id' => $button_type,
                )
            )))['Menu'];
            $existing_mail_code = $this->Mail->find('list',
                array(
                    'fields'     => array('mail_code'),
                    'conditions' => array('Mail.mail_code' => $mail_code),
                    )
            );

            $MailDB = $this->Mail->getDataSource();
            $MailReceiverDB = $this->MailReceiver->getDataSource();
            $MenuDB = $this->Menu->getDataSource();
            try {
                $MailDB->begin();
                $MailReceiverDB->begin();
                $MenuDB->begin();
                
                if (empty($existing_mail_code)) {
                    $mail_data = [];
                    // $mail_data['id']               = current($existing_data);
                    $mail_data['mail_type']        = $mail_receiver;
                    $mail_data['mail_code']        = $mail_code;
                    $mail_data['mail_subject']     = $mail_subject;
                    $mail_data['mail_body']        = $mail_body;
                    $mail_data['flag']             = 1;
                    $mail_data['created_by']       = $login_id;
                    $mail_data['updated_by']       = $login_id;
                    $mail_data['created_date']     = date('Y-m-d H:i:s');
                    $mail_data['updated_date']     = date('Y-m-d H:i:s');
                    #save data in tbl_mail_setting table
                    
                    $status = $this->Mail->saveAll($mail_data);  

                    $existing_data = $this->Mail->find('list',
                    array(
                        'fields'     => array('id'),
                        'conditions' => array('Mail.mail_code' => $mail_code),
                        )
                    );
                    if(!empty($existing_data)) {
                        #save data in tbl_mail_receiver table
                        $mail_receiver_list = [];
                        #loop if not empty email_to list
                        if(!empty($mail_to)) {
                            foreach($mail_to as $level => $mail_limit) {
                                $temp = [];
                                $temp['mail_id']        = current($existing_data);
                                $temp['role_id']        = $level;
                                $temp['mail_send_type'] = "to";
                                $temp['mail_limit']     = $mail_limit;
                                $temp['flag']           = 1;
                                $temp['created_by']     = $login_id;
                                $temp['updated_by']     = $login_id;
                                $temp['created_date']   = date('Y-m-d H:i:s');
                                $temp['updated_date']   = date('Y-m-d H:i:s');
                                array_push($mail_receiver_list,$temp);
                            }
                        }
                        #loop if not empty email_cc list
                        if(!empty($mail_cc)) {
                            foreach($mail_cc as $level => $mail_limit) {
                                $temp = [];
                                $temp['mail_id']        = current($existing_data);
                                $temp['role_id']        = $level;
                                $temp['mail_send_type'] = "cc";
                                $temp['mail_limit']     = $mail_limit;
                                $temp['flag']           = 1;
                                $temp['created_by']     = $login_id;
                                $temp['updated_by']     = $login_id;
                                $temp['created_date']   = date('Y-m-d H:i:s');
                                $temp['updated_date']   = date('Y-m-d H:i:s');
                                array_push($mail_receiver_list,$temp);
                            }
                        }
                        #loop if not empty email_bcc list
                        if(!empty($mail_bcc)) {
                            foreach($mail_bcc as $level => $mail_limit) {
                                $temp = [];
                                $temp['mail_id']        = current($existing_data);
                                $temp['role_id']        = $level;
                                $temp['mail_send_type'] = "bcc";
                                $temp['mail_limit']     = $mail_limit;
                                $temp['flag']           = 1;
                                $temp['created_by']     = $login_id;
                                $temp['updated_by']     = $login_id;
                                $temp['created_date']   = date('Y-m-d H:i:s');
                                $temp['updated_date']   = date('Y-m-d H:i:s');
                                array_push($mail_receiver_list,$temp);
                            }
                        }
                        $this->MailReceiver->updateAll(
                            array(
                                "flag" => '0',
                                "updated_date" => "'" . date("Y-m-d H:i:s") . "'",
                                "updated_by" => "'" . $this->Session->read('LOGIN_ID') . "'"
                            ),
                            array("mail_id" => current($existing_data))
                        );

                        #save data in mail_receivers table
                        $status = $this->MailReceiver->saveAll($mail_receiver_list);
                    }
                    
                }
                #save mail_code into menu table
                if(!empty($db_menu_data)) {
                    $save_menu_data = [];
                    $save_menu_data['id'] = $db_menu_data['id'];
                    $save_menu_data['menu_name_en'] = $db_menu_data['menu_name_en'];
                    $save_menu_data['menu_name_jp'] = $db_menu_data['menu_name_jp'];
                    $save_menu_data['page_name'] = $db_menu_data['page_name'];
                    $save_menu_data['method'] = $db_menu_data['method'];
                    $save_menu_data['layer_no'] = $db_menu_data['layer_no'];
                    $save_menu_data['mail_code'] = $mail_code;
                    $save_menu_data['mail_flag'] = 'ON';
                    $save_menu_data['flag'] = $db_menu_data['flag'];
                    $save_menu_data['created_by'] = $db_menu_data['created_by'];
                    $save_menu_data['updated_by'] = $login_id;
                    $save_menu_data['created_date'] = $db_menu_data['created_date'];
                    $save_menu_data['updated_date'] = date('Y-m-d H:i:s');
                    if($button_type != current($updated_id)) {
                        $old_menu_data = [];
                        $old_menu_data['id'] = current($updated_id);
                        $old_menu_data['mail_code'] = null;
                        $old_menu_data['mail_flag'] = 'OFF';
                        $old_menu_data['updated_by'] = $login_id;
                        $old_menu_data['updated_date'] = date('Y-m-d H:i:s');

                        $this->Menu->saveAll($old_menu_data);
                    }
                    $this->Menu->saveAll($save_menu_data);
                }   

                /*}
                } else {
                    $errorMsg = parent::getErrorMsg('SE050');
                    $this->Flash->set($errorMsg, array("key" =>"MailFail"));
                    $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                }*/

                $MailDB->commit();
                $MailReceiverDB->commit();
                $MenuDB->commit();

                $msg = parent::getSuccessMsg('SS002');
                $this->Flash->set($msg, array('key'=>'MailSuccess'));
                $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));

            } catch (Exception $e) {
                $MailDB->rollback();
                $MailReceiverDB->rollback();
                $MenuDB->rollback();

                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg('SE011', __("変更"));
                $this->Flash->set($msg, array('key'=>'MailFail'));
                $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
            }
        }
    }

    /**
     * delete data from permissions table
     *
     * @author WaiWaiMoe
     * @created_date 2022/06/01
     * @return boolean
     */
    public function deleteMail() {
        if ($this->request->is('POST')) {
            $login_id      = $this->Session->read('LOGIN_ID');
            $link          = "";
            $deleted_id    = $this->request->data['hid_delete_id'];
            $row_count     = $this->request->data('hid_row_count');
            $page_no       = $this->request->data('hid_page_no');
            $page_val      = substr($page_no, 5);
            $limit         = $this->request->data('hid_limit');
            $no_of_page 	= ceil($row_count/$limit);
            $row_per_page  = $row_count % $limit;
            if($row_per_page == 1 && $no_of_page == $page_val) {
                $page_no = 'page:'.($page_val - 1);
            }
            #get page name and phase name in search box
            $phase_name = !empty($this->request->data('s_phase'))? $this->request->data('s_phase') : null;
            $pg_name    = !empty($this->request->data('s_page'))? $this->request->data('s_page') : null;
            if ($phase_name != null) {
                $link   = $link."?s_phase=".urlencode($phase_name);
            }
            if ($pg_name != null) {
                $link   = $link."&s_page=".$pg_name;
            }#concat page number with link
            $link =($page_no != null ? "/".$page_no.$link : $link);
            #delete existing data
            $deleted_data = $this->Menu->find('first',
            array(
                'fields' => array('id'),
                'conditions' => array('Menu.id' => $deleted_id),
                )
            );
            if (!empty($deleted_data)) {
                try{
                    $menu_data = [];
                    $mail_data = [];
                    $menu_data['id'] = $deleted_data['Menu']['id'];
                    $menu_data['mail_code'] = null;
                    $menu_data['mail_flag'] = 'OFF';
                    $menu_data['updated_by'] = $login_id;
                    $menu_data['updated_date'] = date('Y-m-d H:i:s');

                    $mail_data['flag'] = 0;

                    #delete data from permissions table
                    $this->Menu->saveAll(array($menu_data));
                    // $this->MailReceiver->deleteAll(array('MailReceiver.mail_id' => $deleted_data));
                    $msg = parent::getSuccessMsg('SS003');
                    $this->Flash->set($msg, array('key'=>'MailSuccess'));
                    $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg('SE007');
                    $this->Flash->set($msg, array('key'=>'MailFail'));
                    $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
                }
                
            } else {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array("key" =>"MailFail"));
                $this->redirect(array('controller' => 'MailFlowSettings', 'action' => 'index'.$link));
            }
        }
    }
}