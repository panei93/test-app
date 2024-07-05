<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Fields Controller
 *
 * @property Field $Field
 * @property PaginatorComponent $Paginator
 */
class BrmFieldsController extends AppController
{

/**
 * Components
 *
 * @var array
 */
    public $uses = array('BrmField', 'BrmPosition');
    public $components = array('Session', 'Flash','Paginator');

    /**
     * beforeFilter
     *
     * @author Khin Hnin Myo (20200821)
     * @return void
     */
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        
        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Login', 'action' => 'logout'));
        // }
    }
    /**
     * index method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function index()
    {
        $this->layout = 'mastermanagement';
        #show term_name in the term name select box
        try {
            $this->paginate = array(
                                'limit' => Paging::TABLE_PAGING,
                                'conditions' => array(
                                    'BrmField.flag' => 1,
                                ),
                                'fields'=> array(
                                    'BrmField.id',
                                    'BrmField.target_year',
                                    'BrmField.field_name_jp',
                                    'BrmField.field_name_en',
                                    'BrmField.overtime_rate',
                                    'BrmField.flag'
                                )
                            );
            $list = h($this->Paginator->paginate('BrmField'));
            #show total row msg
            $rowCount = $this->params['paging']['BrmField']['count'];
            if ($rowCount == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
                $this->set('errmsg', "");
            }
                    
            $this->set(compact('list', 'rowCount'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
            $this->redirect(array('controller'=>'BrmFields', 'action'=>'index'));
        }
    }
    /**
     * saveField method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function saveFieldData()
    {
        $this->layout = 'mastermanagement';
        $login_id= $this->Session->read('LOGIN_ID');
        if ($this->request->is('post')) {
            $data = $this->request->data;

            $dup_field_data = $this->BrmField->find('first', array(
                'fields' => array(
                    'BrmField.id'
                ),
                'conditions' => array(
                    'BrmField.target_year' => $data['target_year'],
                    'BrmField.field_name_jp' => $data['field_name_jp'],
                    'BrmField.flag' => 1
                )
            ));

            if (empty($dup_field_data)) {
                $date = date('Y-m-d H:i:s');
                $data["flag"] = 1;
                $data["created_by"] = $login_id;
                $data["updated_by"] = $login_id;
                $data["created_date"] = $date;
                $data["updated_date"] = $date;
                if(empty($data['overtime_rate'])) {
                    $data['overtime_rate'] = 0.00;
                }
                try {
                    $this->BrmField->create();
                    $status = $this->BrmField->save($data);
                    if ($status) {
                        $msg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($msg, array('key'=>'FieldOK'));
                        $this->redirect(array('controller'=>'BrmFields', 'action'=>'index'));
                    } else {
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->set($msg, array('key'=>'FieldFail'));
                        $this->redirect(array('controller'=>'BrmFields', 'action'=>'index'));
                    }
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg('SE003');
                    $this->Flash->set($msg, array('key'=>'FieldFail'));
                    $this->redirect(array('controller'=>'BrmFields', 'action'=>'index'));
                }
            } else {
                $msg = parent::getErrorMsg('SE002', __("職務"));
                $this->Flash->set($msg, array('key'=>'FieldFail'));
                $this->redirect(array('controller'=>'BrmFields', 'action'=>'index'));
            }
        }
    }

    /**
     * editFieldData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function editFieldData()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $edit_fieldid = $this->request->data('id');

        $field_data = $this->BrmField->find('all', array(
            'conditions' => array(
                'BrmField.id' => $edit_fieldid,
                'BrmField.flag' => 1
            )
        ));
        $response = array(
            'field_id' => $field_data[0]['BrmField']['id'],
            'target_year' => $field_data[0]['BrmField']['target_year'],
            'field_name_jp' => $field_data[0]['BrmField']['field_name_jp'],
            'field_name_en' => $field_data[0]['BrmField']['field_name_en'],
            'overtime_rate' => $field_data[0]['BrmField']['overtime_rate'],
            'flag' => $field_data[0]['BrmField']['flag']
        );
        echo json_encode($response);
    }

    /**
     * updateFieldData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function updateFieldData()
    {
        $this->layout = 'mastermanagement';
        $login_id= $this->Session->read('LOGIN_ID');
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $page_no = $this->request->data('hid_page_no');
            
            $dup_field_data = $this->BrmField->find('all', array(
                'fields' => array(
                    'BrmField.id'
                ),
                'conditions' => array(
                    'BrmField.id !=' => $data['hid_updateId'],
                    'BrmField.target_year' => $data['target_year'],
                    'BrmField.field_name_jp' => $data['field_name_jp'],
                    'BrmField.flag' => 1
                )
            ));
            
            if (empty($dup_field_data)) {
                $date = date('Y-m-d H:i:s');
                if(empty($data['overtime_rate'])) {
                    $data['overtime_rate'] = 0.00;
                }
                $attachDB = $this->BrmField->getDataSource();
                try {
                    $attachDB->begin();
                    #set fields
                    $arr['target_year'] = $attachDB->value($data['target_year']);
                    $arr['field_name_jp'] = $attachDB->value($data['field_name_jp']);
                    $arr['field_name_en'] = $attachDB->value($data['field_name_en']);
                    $arr['overtime_rate'] = $attachDB->value($data['overtime_rate']);
                    $arr['flag'] = $attachDB->value(1, 'string');
                    $arr['updated_by'] = $attachDB->value($login_id, 'string');
                    $arr['updated_date'] = $attachDB->value(date('Y-m-d H:i:s'), 'string');
                    #condition
                    $con['id'] = $data['hid_updateId'];
                    $con['flag'] = 1;
                    // save history
                    $hisArr['id'] = $data['hid_updateId'];
                    $hisArr['org_id'] = $data['hid_updateId'];
                    $hisArr['page_name'] = 'Field';
                    $hisArr['table_name'] = 'tbl_field';
                    $hisArr['target_year'] = $data['target_year'];
                    $hisArr['field_name_jp'] = $data['field_name_jp'];
                    $hisArr['field_name_en'] = $data['field_name_en'];
                    $hisArr['overtime_rate'] = $data['overtime_rate'];
                    $hisArr['created_by'] = $login_id;
                    $hisArr['created_date'] = date('Y-m-d H:i:s');
                    // $Common = new CommonController(); #To import CommonController
                    // $Common->saveHistory($hisArr, "BrmField");
                    // edit data
                    $this->BrmField->updateAll(
                        $arr,
                        $con
                    );
                    $update_status = $this->BrmField->getAffectedRows();
                    if ($update_status > 0) {
                        $attachDB->commit();
                        $msg = parent::getSuccessMsg("SS002");
                        $this->Flash->set($msg, array('key'=>'FieldOK'));
                        $this->redirect(array('controller'=>'BrmFields', 'action'=>'index/'.$page_no));
                    } else {
                        $attachDB->rollback();
                        $msg = parent::getErrorMsg('SE011', __("変更"));
                        $this->Flash->set($msg, array('key'=>'FieldFail'));
                        $this->redirect(array(
                            'controller'=>'BrmFields',
                            'action'=>'index/'.$page_no
                        ));
                    }
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                    $attachDB->rollback();
                    $msg = parent::getErrorMsg('SE011', __("変更"));
                    $this->Flash->set($msg, array('key'=>'FieldFail'));
                    $this->redirect(array(
                        'controller'=>'BrmFields',
                        'action'=>'index/'.$page_no
                    ));
                }
            } else {
                $msg = parent::getErrorMsg('SE002', __("職務"));
                $this->Flash->set($msg, array('key'=>'FieldFail'));
                $this->redirect(array('controller'=>'BrmFields', 'action'=>'index'));
            }
        }
    }

    /**
     * deleteFieldData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function deleteFieldData()
    {
        if ($this->request->is('post')) {
            $login_id= $this->Session->read('LOGIN_ID');
            $page_no = $this->request->data('hid_page_no');
            $hid_deleteId = $this->request->data('hid_deleteId');

            $position_data = $this->BrmPosition->find('first', array(
                        'conditions' =>array(
                            'BrmPosition.brm_field_id' => $hid_deleteId,
                             'BrmPosition.flag'			 => 1)
                    ))['BrmPosition']['brm_field_id'];

            #if have field id of deleted value in position tbl.
            if (!empty($position_data)) {
                $errmsg = array(__("職務"),__("職務"));
                $errorMsg = parent::getErrorMsg('SE096', $errmsg);
                $this->Flash->set($errorMsg, array("key"=>"FieldFail"));
                $this->redirect(array(
                        'controller' => 'BrmFields',
                        'action' => 'index/'.$page_no
                    ));
            }
            try {
                $attachDB = $this->BrmField->getDataSource();
                $attachDB->begin();
                #set fields
                $arr['BrmField.flag'] = $attachDB->value(0, 'string');
                $arr['updated_by'] = $attachDB->value($login_id, 'string');
                $arr['updated_date'] = $attachDB->value(date('Y-m-d H:i:s'), 'string');
                #condition
                $con['BrmField.id'] = $hid_deleteId;
                $con['BrmField.flag'] = 1;
                
                $this->BrmField->updateAll(
                    $arr,
                    $con
                );
                $delete_status = $this->BrmField->getAffectedRows();
                if ($delete_status > 0) {
                    $attachDB->commit();
                    $msg = parent::getSuccessMsg("SS003");
                    $this->Flash->set($msg, array('key'=>'FieldOK'));
                    $this->redirect(array(
                        'controller'=>'BrmFields',
                        'action'=>'index/'.$page_no
                    ));
                } else {
                    $errorMsg = parent::getErrorMsg('SE050');
                    $this->Flash->set($errorMsg, array("key"=>"FieldFail"));
                    $this->redirect(array(
                        'controller' => 'BrmFields',
                        'action' => 'index/'.$page_no
                    ));
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. _FILE_ . ' on line ' . _LINE_ . ' within the class ' . get_class());
                $attachDB->rollback();
                $msg = parent::getErrorMsg('SE050');
                $this->Flash->set($msg, array('key'=>'FieldFail'));
                $this->redirect(array(
                    'controller'=>'BrmFields',
                    'action'=>'index/'.$page_no
                ));
            }
        }
    }
}
