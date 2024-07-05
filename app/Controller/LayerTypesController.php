<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Layers Controller
 *
 * @property Layers $Layers
 * @property PaginatorComponent Paginator
 */
class LayerTypesController extends AppController
{
    /**
     * Components
     *
     * @var array
     */
    public $uses = array('LayerType', 'Layer');
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
     * @author Hein Htet Ko
     * @return void
     */

    public function index()
    {
        $this->layout = 'mastermanagement';
       
        #get order list that already exist
        $order_list = array_column($this->LayerType->find('all', array(
            'fields' => ', LayerType.name_jp, LayerType.type_order',
            'conditions' => array(
                'LayerType.flag' => 1,
            )
        )), 'LayerType');
       
        #get all layers
        $this->paginate = array(
            'limit' => Paging::TABLE_PAGING,
            'conditions' => array(
                'LayerType.flag' => 1,
            ),
            'order' => 'type_order ASC'
        );
       
        #disable layer adjust list
        $disable_adjust_list = array_column(array_column($this->Layer->find('all', array(
            'fields' => 'DISTINCT Layer.layer_type_id',
            'conditions' => array(
                'Layer.flag' => 1,
            )
            )), 'Layer'), 'layer_type_id');
           
            $this->paginate = array(
                'maxLimit' => Paging::TABLE_PAGING,
                'limit' => Paging::TABLE_PAGING,
                'conditions' => array(
                    'LayerType.flag' => 1
                )	
            );
        $list = h($this->Paginator->paginate('LayerType'));

        $rowCount = $this->params['paging']['LayerType']['count'];
        if ($rowCount == 0) {
            $this->set('errmsg', parent::getErrorMsg('SE001'));
            $this->set('succmsg', '');
        } else {
            $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
            $this->set('errmsg', '');
        }

        $page = $this->params['paging']['LayerType']['page'];
        $limit = $this->params['paging']['LayerType']['limit'];

        #set layer order limit
        $type_order_limit = Setting::LAYER_ORDER_LIMIT;
        
        $this->set(compact('order_list', 'list', 'rowCount', 'page', 'limit', 'disable_adjust_list', 'type_order_limit'));
        $this->render('index');
    }

    /**
     * save layer
     *
     * @author Hein Htet Ko
     * @return void
     */
    public function saveLayerType()
    {
        if ($this->request->is('POST')) {
            $data = $this->request->data;            
            $login_id= $this->Session->read('LOGIN_ID');
            $name_jp = trim($data['name_jp']);
            $name_en = (trim($data['name_en']) != "") ? trim($data['name_en']) : trim($data['name_jp']);
            $layer_name = __('部署名');
            $dup_field_data = $this->LayerType->find('first', array(
                'fields' => array(
                    'LayerType.id','LayerType.name_jp','LayerType.name_en'
                ),
                'conditions' => array(
                    'OR' => array(
                        'LayerType.name_jp' => $name_jp,
                        'LayerType.name_en' => $name_en
                    ),
                    // 'LayerType.type_order' => $data['type_order'],
                    'LayerType.flag' => 1,
                )
            ));

            if (empty($dup_field_data)) {
                $maxOrder = $this->LayerType->find('first', array(
                    'fields' => array('MAX(LayerType.type_order) AS maxOrder'),
                    'conditions' => array(
                        'LayerType.flag' => 1,
                    ),
                    ))[0]['maxOrder'];
                   // pr($maxOrder);die;
                $date = date('Y-m-d H:i:s');
                $data['name_jp'] = trim($data['name_jp']);
                $data['name_en'] = (trim($data['name_en']) != "") ? trim($data['name_en']) : trim($data['name_jp']);
                
                if ($data['show_detail_radio'] == 1) {
                    $data['show_detail'] = 1;
                } else {
                    $data['show_detail'] = 0;
                }
                $data['type_order'] = $maxOrder ? $maxOrder+ 1  : 1;
                $data['created_by'] = $login_id;
                $data['updated_by'] = $login_id;
                $data['created_date'] = $date;
                $data['updated_date'] = $date;
                $data['flag'] = 1;
                try {                    
                    $status = $this->LayerType->save($data);
                    if ($status) {
                        $msg = parent::getSuccessMsg('SS001');
                        $this->Flash->set($msg, array('key'=>'LayerTypesSuccess'));
                        $this->redirect(array('controller'=>'LayerTypes', 'action'=>'index'));
                    } else {
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->set($msg, array('key'=>'LayerTypesFail'));
                        $this->redirect(array('controller'=>'LayerTypes', 'action'=>'index'));
                    }
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg('SE003');
                    $this->Flash->set($msg, array('key'=>'LayerTypesFail'));
                    $this->redirect(array('controller'=>'LayerTypes', 'action'=>'index'));
                }
            } else {
                if(!empty($dup_field_data)) $msg = parent::getErrorMsg('SE002', $layer_name);
                else if(!empty($dup_parent_data)) $msg = parent::getErrorMsg('SE002',  __('部署順番'));
                
                $this->Flash->set($msg, array('key'=>'LayerTypesFail'));
                $this->redirect(array('controller'=>'LayerTypes', 'action'=>'index'));
            }
        }
    }

    /**
     * get edit data
     *
     * @author Hein Htet Ko
     * @return void
     */
    public function getEditData()
    {
        #only allow ajax request
        $this->request->allowMethod('ajax');
        $this->autoRender = false;
        $edit_field_id = $this->request->data('id');

        #get data by layer id
        $layer_data = $this->LayerType->find('first', array(
            'conditions' => array(
                'OR' => array(
                    'LayerType.id' => $edit_field_id,
                ),
                'LayerType.flag' => 1
            )
        ));
 
        #response data for clicked layer
        $response = array(
            'id' => $edit_field_id,
            'name_jp' => $layer_data['LayerType']['name_jp'],
            'name_en' => $layer_data['LayerType']['name_en'],
            'type_order' => $layer_data['LayerType']['type_order'],
            'show_detail' => $layer_data['LayerType']['show_detail'],
            'type_order_list' => $layer_data['LayerType']['type_order']
        );
        echo json_encode($response);
    }

    /**
    * update layer
    *
    * @author Hein Htet Ko
    * @return void
    */
    public function updateLayerType()
    {
        if ($this->request->is('post')) {
            $login_id= $this->Session->read('LOGIN_ID');
            $data = $this->request->data;
            $page_no = $this->request->data('hid_page_no');
            $name_jp = trim($data['name_jp']);
            $name_en = (trim($data['name_en']) != "") ? trim($data['name_en']) : trim($data['name_jp']);
            $layer_name = __('部署名');
            $dup_field_data = $this->LayerType->find('all', array(
                'fields' => array(
                    'LayerType.id','LayerType.name_jp','LayerType.name_en'
                ),
                'conditions' => array(
                    // 'OR' => array(
                    //     'LayerType.id !=' => $data['hid_update_id'],
                    // ),
                    'LayerType.id !=' => $data['hid_update_id'],
                    'OR' => array(
                        'LayerType.name_jp' => $name_jp,
                        'LayerType.name_en' => $name_en
                    ),
                    'LayerType.flag' => 1
                )
            ));
         
            $dup_parent_data = $this->LayerType->find('first', array(
                'fields' => array(
                    'LayerType.id'
                ),
                'conditions' => array(
                    'LayerType.id !=' => $data['hid_update_id'],
                    'LayerType.flag' => 1,
                    'LayerType.type_order' => $data['type_order'],
                )
            ));   
            if (in_array($name_jp, array_column(array_column($dup_field_data, 'LayerType'), 'name_jp'))) {
                $layer_name = __('部署名（JP）');
            }
            if (in_array($name_en, array_column(array_column($dup_field_data, 'LayerType'), 'name_en'))) {
                $layer_name = __('部署名（ENG）');
            }
            if (in_array($name_jp, array_column(array_column($dup_field_data, 'LayerType'), 'name_jp')) && in_array($name_en, array_column(array_column($dup_field_data, 'LayerType'), 'name_en'))) {
                $layer_name = __('部署名');
            }
            if (empty($dup_field_data) && empty($dup_parent_data)) {
                $date = date('Y-m-d H:i:s');
                $attachDB = $this->LayerType->getDataSource();
                try {
                    $attachDB->begin();
                    #set fields
                    $arr['name_jp'] = $attachDB->value($name_jp);
                    $arr['name_en'] = $attachDB->value($name_en);
                    $arr['type_order'] = $attachDB->value($data['type_order']);
                    if ($data['show_detail_radio'] == 1) {
                        $arr['show_detail'] = $attachDB->value(1, 'string');
                    } else {
                        $arr['show_detail'] = $attachDB->value(0, 'string');
                    }
                    $arr['updated_by'] = $attachDB->value($login_id, 'string');
                    $arr['updated_date'] = $attachDB->value(date('Y-m-d H:i:s'), 'string');
                    #condition
                    $con['id'] = $data['hid_update_id'];
                    $con['flag'] = 1;
                    // save history
                    $hisArr['id'] = $data['hid_update_id'];
                    $hisArr['org_id'] = $data['hid_update_id'];
                    $hisArr['page_name'] = 'Layer';
                    $hisArr['table_name'] = 'layer_types';
                    $hisArr['name_jp'] = trim($data['name_jp']);
                    $hisArr['name_en'] = (trim($data['name_en']) != "") ? trim($data['name_en']) : trim($data['name_jp']);
                    $hisArr['type_order'] = $data['hid_type_order'];
                    if ($data['show_detail_radio'] == 1) {
                        $hisArr['show_detail'] = 1;
                    } else {
                        $hisArr['show_detail'] = 0;
                    }
                    $hisArr['created_by'] = $login_id;
                    $hisArr['created_date'] = date('Y-m-d H:i:s');
                    // $Common = new CommonController(); #To import CommonController
                    // $status = $Common->saveHistory($hisArr, 'LayerType');
                    $this->LayerType->updateAll(
                        $arr,
                        $con
                    );
                    $update_status = $this->LayerType->getAffectedRows();
                   
                    if ($update_status > 0) {
                        $attachDB->commit();

                        #check pagination
                        $paingData = $this->getPagingData();
                        $rowCount = $paingData['count'];
                        $limit = $paingData['limit'];
                        $current_page = explode(':', $page_no)[1];
                        $start = ($current_page-1)*$limit + 1;
                    
                        if ($rowCount < $start) {
                            $page_no = (($current_page-1) != 0)? 'page:'.($current_page-1) : '';
                            if ($page_no == 'page:1') {
                                $page_no = '';
                            }
                        }
                        $msg = parent::getSuccessMsg('SS002');
                        $this->Flash->set($msg, array('key'=>'LayerTypesSuccess'));
                        $this->redirect(array('controller'=>'LayerTypes', 'action'=>'index/'.$page_no));
                    } else {
                        $attachDB->rollback();
                        $msg = parent::getErrorMsg('SE011', __("変更"));
                        $this->Flash->set($msg, array('key'=>'LayerTypeFail'));
                        $this->redirect(array(
                            'controller'=>'LayerTypes',
                            'action'=>'index/'.$page_no
                        ));
                    }
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $attachDB->rollback();
                    $msg = parent::getErrorMsg('SE011', __("変更"));
                    $this->Flash->set($msg, array('key'=>'LayerTypeFail'));
                    $this->redirect(array(
                        'controller'=>'LayerTypes',
                        'action'=>'index/'.$page_no
                    ));
                }
            } else {
                if(!empty($dup_field_data)) $msg = parent::getErrorMsg('SE002', $layer_name);
                else if(!empty($dup_parent_data)) $msg = parent::getErrorMsg('SE002',  __('部署順番'));
                $this->Flash->set($msg, array('key'=>'LayerTypesFail'));
                $this->redirect(array('controller'=>'LayerTypes', 'action'=>'index'));
            }
        }
    }



    /**
     * remove layer
     *
     * @author Hein Htet Ko
     * @return void
     */
    public function removeLayerType()
    {
        if ($this->request->is('POST')) {
            $login_id= $this->Session->read('LOGIN_ID');
            $page_no = (in_array($this->request->data('hid_page_no'), array('LayerTypess', 'index')))? '' : $this->request->data('hid_page_no');
            $hid_delete_id = $this->request->data('hid_delete_id');

            $has_layer = $this->LayerType->find('all', array(
                        'conditions' =>array(
                            'OR' => array(
                                'id' => $hid_delete_id,
                            ),
                            'flag' => 1)
                    ));
                  
            if (empty($has_layer)) {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array('key'=>'LayerTypesFail'));
                $this->redirect(array(
                    'controller' => 'LayerTypes',
                    'action' => 'index/'.$page_no
                ));
            }
            try {
                $used_layerOrder = $this->Layer->find('first', array(
                    'fields' => array(
                        'Layer.id'
                    ),
                    'conditions' => array(
                        'Layer.layer_type_id' => $has_layer[0]['LayerType']['id'],
                        'Layer.flag' => 1,
                    )
                )); 
                $child_layer = $this->LayerType->find('first', array(
                    'fields' => array(
                        'LayerType.id'
                    ),
                    'conditions' => array(
                        'LayerType.type_order' => $has_layer[0]['LayerType']['type_order']+1,
                        'LayerType.flag' => 1,
                    )
                ));
                if (sizeof($used_layerOrder) > 0 ) { //|| sizeof($child_layer) > 0
                    $errorMsg = parent::getErrorMsg('SE125');
                    $this->Flash->set($errorMsg, array('key'=>'LayerTypesFail'));
                    $this->redirect(array(
                        'controller' => 'LayerTypes',
                        'action' => 'index/'.$page_no
                    ));
                } else {
                    $nextRecords = $this->LayerType->find('list', array(
                        'fields' => array(
                            'LayerType.id', 'LayerType.type_order'
                        ),
                        'conditions' => array(
                            'LayerType.id > ' => $hid_delete_id,
                            'LayerType.flag' => 1,
                        )
                    ));
                    $updated_record = array();
                    $i = 0;
                    foreach($nextRecords as $key=>$value){
                        $updated_record[$i]['id'] = $key;
                        $updated_record[$i]['type_order'] = $value - 1;
                        $i++;
                    }
                    
                    $result = array(
                        'id' => $hid_delete_id,
                        'flag' => 0,
                        'updated_date'  => date("Y-m-d H:i:s"),
                        'updated_by'    => $this->Session->read('LOGIN_ID')
                        );
                
                    $this->LayerType->save($result);
                    if(count($updated_record) > 0) $this->LayerType->saveMany($updated_record);
                    $delete_status = $this->LayerType->getAffectedRows();
                    if ($delete_status) {
                        $paingData = $this->getPagingData();
                        $rowCount = $paingData['count'];
                        $limit = $paingData['limit'];
                        $current_page = explode(':', $page_no)[1];
                        $start = ($current_page-1)*$limit + 1;
                        
                        if ($rowCount < $start) {
                            $page_no = (($current_page-1) != 0)? 'page:'.($current_page-1) : '';
                            if ($page_no == 'page:1') {
                                $page_no = '';
                            }
                        }
                        $msg = parent::getSuccessMsg('SS003');
                        $this->Flash->set($msg, array('key'=>'LayerTypesSuccess'));
                        $this->redirect(array(
                            'controller'=>'LayerTypes',
                            'action'=> 'index/'.$page_no
                        ));
                    } else {
                        $errorMsg = parent::getErrorMsg('SE050');
                        $this->Flash->set($errorMsg, array('key'=>'LayerTypesFail'));
                        $this->redirect(array(
                            'controller' => 'LayerTypes',
                            'action' => 'index/'.$page_no
                        ));
                    }
                }
            } catch (Exception $e) {
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $attachDB->rollback();
                $msg = parent::getErrorMsg('SE050');
                $this->Flash->set($msg, array('key'=>'LayerTypesFail'));
                $this->redirect(array(
                    'controller'=>'Layers',
                    'action'=> 'index/'.$page_no
                ));
            }
        }
    }

    /**
    * get paging data
    * @author Hein Htet Ko(20220503)
    * @return array
    *
    */
    public function getPagingData()
    {
        #get all layers
        $this->paginate = array(
            'limit' => Paging::TABLE_PAGING,
            'conditions' => array(
                'LayerType.flag' => 1,
            ),
            'order' => 'LayerType.type_order ASC'
        );
        
        $list = h($this->Paginator->paginate('LayerType'));
        return $this->params['paging']['LayerType'];
    }

    // /**
    // * save data to  layer_types
    // *
    // * @author Hein Htet Ko
    // * @return boolen
    // */
    // public function getRelatedRow()
    // {
    //     // $this->request->allowMethod('ajax');
    //     // $this->autoRender = false;
    //     #only allow ajax request
    //     parent::checkAjaxRequest($this);

    //     $id = $this->request->data('id');

    //     $related_data = array();

    //     #Get current data from database
    //     $current_data = $this->LayerType->find('list', array(
    //         'fields' => array(
    //             'LayerType.id'
    //         ),
    //         'conditions' => array(
    //             'LayerType.flag'=>'1'
    //         )
    //     ));
    //     #Search used layer order in layer group
    //     $search_order = $this->Layer->find('list', array(
    //         'fields' => array(
    //             'Layer.type_order'
    //         ),
    //         'conditions' => array(
    //             'Layer.flag' => 1,
    //             'Layer.type_order' => $current_data
    //         ),
    //         'group' => array(
    //             'Layer.type_order'
    //             )
    //         ));
            
    //     $search_order = implode(',', $search_order);
    //     $conditions['LayerType.flag'] = '1';
    //     if (!empty($search_order) || $search_order != '') {
    //         $conditions[] = 'LayerType.type_order NOT IN ('. $search_order.')';
    //     }

    //     #Filter layer order that can be order
    //     $related_data = $this->LayerType->find('all', array(
    //             'fields' => array('LayerType.id', 'LayerType.name_jp', 'LayerType.name_en', 'LayerType.type_order', 'LayerType.show_detail'),
    //             'conditions' => $conditions,
    //             'order' => array('LayerType.type_order')
    //             ));
    //     echo json_encode($related_data);
    // }


    /**
     * save data to  layer_types
     *
     * @author Hein Htet Ko
     * @return boolen
     */
    public function EditLayerSetup()
    {
        $login_id = $this->Session->read('LOGIN_ID');
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $data = $this->request->data('data_arr');
       
        if (!empty($data)) {            
            $index = 0;
            $dataArrs = json_decode($data[0], true);
            foreach ($dataArrs as $value) {
                // $update[$index] = $value;                
                $value['updated_by'] =  $login_id;
                $value['updated_date'] = date('Y-m-d H:i:s');

                // save history
                $hisArr[$index]['id'] = $value['id'];
                $hisArr[$index]['org_id'] = $value['id'];
                $hisArr[$index]['page_name'] = 'Layer';
                $hisArr[$index]['table_name'] = 'layer_types';
                $hisArr[$index]['type_order'] = $value['type_order'];
                $hisArr[$index]['created_by'] = $login_id;
                $hisArr[$index]['created_date'] = date('Y-m-d H:i:s');
                $index++;
            }

            $Common = new CommonController(); #To import CommonController
            $Common->saveHistory($hisArr, "LayerType");
            $this->LayerType->saveMany($dataArrs);
            $msg = parent::getSuccessMsg('SS001');
            $this->Flash->set($msg, array('key'=>'LayerTypesSuccess'));
            return json_encode($successMsg);
        } else {
            $msg = parent::errorMsg('SS002');
            $this->Flash->set($msg, array('key'=>'LayerTypesFail'));
            return json_encode($errorMsg);
        }
    }
}
