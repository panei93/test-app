<?php
App::uses('AppController', 'Controller');
/**
 * PositionMps Controller
 *
 * @property PositionMp $PositionMp
 * @property PaginatorComponent $Paginator
 */
class BrmPositionsController extends AppController
{

/**
 * Components
 *
 * @var array
 */
    public $uses = array('BrmPosition', 'BrmField','BrmTerm','BrmManpowerPlan');
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
        $search_year = !empty($this->request->data('year'))? $this->request->data('year') : $this->request->query('year');
        $target_year =	$search_year;

        $sYear = $this->Session->read('SEARCH_YEAR');

        $conditions = array();
        if (!empty($search_year)) {
            $this->Session->write('SEARCH_YEAR', $search_year);

            $conditions["BrmPosition.target_year"]  = $search_year;
            $conditions["BrmPosition.flag"]         = 1;
            $conditions["BrmField.flag"]            = 1;
        } else {
            $this->Session->write('SEARCH_YEAR', '');
            $conditions["BrmPosition.flag"] = 1;
            $conditions["BrmField.flag"]    = 1;
        }
        try {
            // $this->BrmPosition->virtualFields['order_string'] = 'CONCAT(BrmPosition.target_year, "-", BrmPosition.display_no, "-", BrmField.id, "-", BrmPosition.id)';
            
            $this->paginate = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'fields'=> array(
                    'BrmField.id',
                    'BrmField.field_name_jp',
                    'BrmField.field_name_en',
                    'BrmPosition.id',
                    'BrmPosition.brm_field_id',
                    'BrmPosition.target_year',
                    'BrmPosition.position_name_jp',
                    'BrmPosition.position_name_en',
                    'BrmPosition.unit_salary',
                    'BrmPosition.display_no',
                    'BrmPosition.edit_flag',
                    'BrmPosition.flag',
                    // 'BrmPosition.order_string',
                ),
                'order' => 'BrmPosition.brm_field_id'
            );
            $list = h($this->Paginator->paginate('BrmPosition', array(), array('BrmPosition.target_year','BrmPosition.display_no','BrmPosition.brm_field_id','BrmPosition.id')));
            

            #show total row msg
            $rowCount = $this->params['paging']['BrmPosition']['count'];
            if ($rowCount == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
                $this->set('errmsg', "");
            }

            #get year
            $years = $this->BrmPosition->find('all', array(
                            'conditions' => array('BrmPosition.flag' => 1,
                                                  'BrmField.flag'    => 1
                                                ),
                            'fields' => array('BrmPosition.target_year'),
                            'group'  => array('target_year')));

           
            $this->set(compact('list', 'rowCount', 'years', 'target_year'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
        }
    }

    /**
     * savePositionData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function savePositionData()
    {
        $login_id= $this->Session->read('LOGIN_ID');
        if ($this->request->is('post')) {
            $actual_link = $_SERVER["HTTP_REFERER"];
            $data = $this->request->data;
            $search_year = $this->request->query('hid_year');
            if (!empty($data)) {
                if ($data["edit_flag"] == "") {
                    $data["edit_flag"] = 0;
                } else {
                    $data["edit_flag"] = 1;
                }
                $date                   = date('Y-m-d H:i:s');
                $data["flag"]           = 1;
                $data["created_by"]     = $login_id;
                $data["updated_by"]     = $login_id;
                $data["created_date"]   = $date;
                $data["updated_date"]   = $date;
                $data["updated_date"]   = $date;
                $data["brm_field_id"]   = $data['field_id'];
                if (empty($data['unit_salary'])) {
                    $data['unit_salary'] = 0.00;
                } 
                $dup_pos_data = $this->BrmPosition->find('first', array(
                    'fields' => array('BrmPosition.id'),
                    'conditions' => array(
                        'BrmPosition.target_year'       => $data['target_year'],
                        'BrmPosition.brm_field_id'      => $data['field_id'],
                        'BrmPosition.position_name_jp'  => $data['position_name_jp'],
                        'BrmPosition.flag'              => 1,
                    )
                ));
               
                if (empty($dup_pos_data)) {
                    try {
                        $this->BrmPosition->create();//pr($data);die;
                        $status = $this->BrmPosition->save($data);
                        
                        if ($status) {
                            $msg = parent::getSuccessMsg('SS001');
                            $this->Flash->set($msg, array('key'=>'PositionmpOK'));
                            $this->redirect($actual_link);
                        } else {
                            $msg = parent::getErrorMsg('SE003');
                            $this->Flash->set($msg, array('key'=>'PositionmpFail'));
            
                            $this->redirect($actual_link);
                        }
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->set($msg, array('key'=>'PositionmpFail'));
                        CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
                        $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
                    }
                } else {
                    $msg = parent::getErrorMsg('SE002', __("ポジション"));
                    $this->Flash->set($msg, array('key'=>'PositionmpFail'));
                    $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
                }
            }
        }
    }

    /**
     * editPositionData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return response
     */
    public function editPositionData()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $edit_posid = $this->request->data('id');
        $position_data = $this->BrmPosition->find('all', array(
            'conditions' => array(
                'BrmPosition.id'    => $edit_posid,
                'BrmPosition.flag'  => 1
            )
        ));
       // $this->log(print_r($position_data,true),LOG_DEBUG);
        $field_data = $this->BrmField->find('all', array(
            'conditions' => array(
                'BrmField.id'   => $position_data[0]['BrmPosition']['brm_field_id'],
                'BrmField.flag' => 1
            )
        ));
      
        $response = array(
            'position_id'       => $position_data[0]['BrmPosition']['id'],
            'target_year'       => $position_data[0]['BrmPosition']['target_year'],
            'display_no'        => $position_data[0]['BrmPosition']['display_no'],
            'position_name_jp'  => $position_data[0]['BrmPosition']['position_name_jp'],
            'position_name_en'  => $position_data[0]['BrmPosition']['position_name_en'],
            'unit_salary'       => $position_data[0]['BrmPosition']['unit_salary'],
            'edit_flag'         => $position_data[0]['BrmPosition']['edit_flag'],
            'flag'              => $position_data[0]['BrmPosition']['flag'],
            'field_id'          => $field_data[0]['BrmField']['id'],
            'field_name_jp'     => $field_data[0]['BrmField']['field_name_jp']
        );
        echo json_encode($response);
    }

    /**
     * updatePositionData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function updatePositionData()
    {
        $this->layout = 'mastermanagement';
        $login_id= $this->Session->read('LOGIN_ID');
        
        if ($this->request->is('post')) {
            $data = $this->request->data;  
            $search_year        = $data['hid_year'];
            $page_no            = $data['hid_page_no'];
            $id                 = $data['hid_updateId'];
            $target_year        = $data['target_year'];
            $field_id           = $data['field_id'];
            $display_no         = $data['display_no'];
            $position_name_jp   = $data['position_name_jp'];
            $position_name_en   = $data['position_name_en'];
            if (empty($data['unit_salary'])) {
                $unit_salary    = 0.00;
            } else {
                $unit_salary    = $data['unit_salary'];
            }
            $edit_flag = $data['edit_flag'];
            $date = date('Y-m-d H:i:s');
            if (!empty($data)) {
                if ($edit_flag == "") {
                    $edit_flag = 0;
                } else {
                    $edit_flag = 1;
                }
              
                $dup_pos_data = $this->BrmPosition->find('first', array(
                    'fields' => array('BrmPosition.id'),
                    'conditions' => array(
                        'BrmPosition.id !='             => $id,
                        'BrmPosition.target_year'       => $target_year,
                        'BrmPosition.brm_field_id'      => $field_id,
                        'BrmPosition.position_name_jp'  => $position_name_jp,
                        'BrmPosition.flag'              => 1,
                    )
                ));
              
                if (empty($dup_pos_data)) {
                    try {
                        $this->BrmPosition->updateAll(
                            array(
                                "flag"              =>'1',
                                "target_year"       => "'".$target_year."'",
                                "brm_field_id"      => "'".$field_id."'",
                                "display_no"        => "'".$display_no."'",
                                "position_name_jp"  => "'".$position_name_jp."'",
                                "position_name_en"  => "'".$position_name_en."'",
                                "unit_salary"       => "'".$unit_salary."'",
                                "edit_flag"         => "'".$edit_flag."'",
                                "updated_date"      => "'".$date."'",
                                "updated_by"        => "'".$login_id."'"
                            ),
                            array(
                                "BrmPosition.id"=>$id
                            )
                        );
                        $update_status = $this->BrmPosition->getAffectedRows();
                        if ($update_status > 0) {
                            $msg = parent::getSuccessMsg("SS002");
                            $this->Flash->set($msg, array('key'=>'PositionmpOK'));

                            //write search_year in Url.
                            if ($search_year != "") {
                                $queryYear = 'year='.$search_year;
                                $this->redirect(array(
                                'controller'=>'BrmPositions',
                                'action'=>'index/'.$page_no.'?'.$queryYear
                            ));
                            }

                            $this->redirect(array(
                                'controller'=>'BrmPositions',
                                'action'=>'index/'.$page_no.'?year='.$search_year
                            ));
                        } else {
                            $msg = parent::getErrorMsg('SE011', __("変更"));
                            $this->Flash->set($msg, array('key'=>'PositionmpFail'));
                            $this->redirect(array(
                                'controller'=>'BrmPositions',
                                'action'=>'index/'.$page_no
                            ));
                        }
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE011', __("変更"));
                        $this->Flash->set($msg, array('key'=>'PositionmpFail'));
                        CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
                        $this->redirect(array(
                            'controller'=>'BrmPositions',
                            'action'=>'index/'.$page_no
                        ));
                    }
                } else {
                    $msg = parent::getErrorMsg('SE002', __("ポジション"));
                    $this->Flash->set($msg, array('key'=>'PositionmpFail'));
                    $this->redirect(array(
                        'controller'=>'BrmPositions',
                        'action'=>'index/'.$page_no
                    ));
                }
            }
        }
    }

    /**
     * deletePositionData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return void
     */
    public function deletePositionData()
    {
        if ($this->request->is('post')) {
            $page_no = $this->request->data('hid_page_no');
            $hid_deleteid = $this->request->data('hid_deleteId');
            $search_year = $this->request->data('hid_year');
            
            $mp_data = $this->BrmManpowerPlan->find('first', array(
                        'conditions' =>array(
                            'BrmManpowerPlan.brm_position_id' => $hid_deleteid,
                            'BrmManpowerPlan.flag'			  => 1)
                    ))['BrmManpowerPlan']['brm_position_id'];

            #if have position id of deleted value in manpower tbl.
            if (!empty($mp_data)) {
                $errmsg = array(__("役職"),__("役職"));

                $errorMsg = parent::getErrorMsg('SE096', $errmsg);
                $this->Flash->set($errorMsg, array("key"=>"PositionmpFail"));
                $this->redirect(array(
                        'controller' => 'BrmPositions',
                        'action' => 'index/'.$page_no
                    ));
            }

            $login_id= $this->Session->read('LOGIN_ID');
            $date = date('Y-m-d H:i:s');
            try {
                $this->BrmPosition->updateAll(
                    array(
                        "flag"=>'0',
                        "updated_date"=> "'".$date."'",
                        "updated_by"=> "'".$login_id."'"
                    ),
                    array(
                        "BrmPosition.id" => $hid_deleteid,
                        "BrmPosition.flag" => '1'
                    )
                );
                $delete_status = $this->BrmPosition->getAffectedRows();

                if ($delete_status > 0) {
                    $msg = parent::getSuccessMsg("SS003");
                    $this->Flash->set($msg, array('key'=>'PositionmpOK'));

                    if ($search_year != "") {
                        $queryYear = 'year='.$search_year;
                        $this->redirect(array(
                                'controller'=>'BrmPositions',
                                'action'=>'index/'.$page_no.'?'.$queryYear
                            ));
                    }
                    $this->redirect(array(
                        'controller'=>'BrmPositions',
                        'action'=>'index/'.$page_no
                    ));
                } else {
                    $errorMsg = parent::getErrorMsg('SE050');
                    $this->Flash->set($errorMsg, array("key"=>"PositionmpFail"));
                    $this->redirect(array(
                        'controller' => 'BrmPositions',
                        'action' => 'index/'.$page_no.'?year='.$search_year
                    ));
                }
            } catch (Exception $e) {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array('key'=>'PositionmpFail'));
                CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->redirect(array(
                    'controller'=>'BrmPositions',
                    'action'=>'index/'.$page_no.'?year='.$search_year
                ));
            }
        }
    }

    /**
     * getFieldData method
     *
     * @author Khin Hnin Myo (20200622)
     * @return field_select_data
     */
    public function getFieldData()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $target_year = $this->request->data('target_year');
        
        $field_select_data = h($this->BrmField->find('list', array(
            'conditions' => array(
                'BrmField.target_year' => $target_year,
                'BrmField.flag' => 1
            ),
            'fields' => array('id','field_name_jp')
        )));
        
        echo json_encode($field_select_data);
    }

    /**
     * SearchData method
     *
     * @author Aye Zar Ni Kyaw (20201008)
     * @return serachdata
     */
    public function SearchData($param=null)
    {
        $this->layout = 'mastermanagement';
        $target_year =	$this->request->data('year');
        $conditions = array();
        $conditions['BrmPosition.flag'] = 1;
        $conditions['BrmField.flag'] = 1;

        if (!empty($target_year)) {
            $this->Session->write('SEARCH_YEAR', $target_year);
            $conditions["BrmPosition.target_year"] = $year;
        } else {
            if ($this->request->data('hid_search') == 'SEARCHALL') {
                $this->Session->write('SEARCH_YEAR', '');
            }
        }
        // $this->Session->write('SEARCH_YEAR',$target_year);
        $sYear = $this->Session->read('SEARCH_YEAR');
        $target_year = $sYear;
        if (!empty($sYear)) {
            $conditions['BrmPosition.target_year'] = $sYear;
            if (strpos($_SERVER['REQUEST_URI'], 'SearchData')) {
                $year = $sYear;
                if ($param == 'positionmp') {
                    $this->Session->write('SEARCH_YEAR', '');
                    $conditions = array();
                    $year = '';
                }
            } else {
                $year = $sYear;
            }
        }

        try {
            $this->paginate = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'fields'=> array(
                    'BrmField.id',
                    'BrmField.field_name_jp',
                    'BrmField.field_name_en',
                    'BrmPosition.id',
                    'BrmPosition.brm_field_id',
                    'BrmPosition.target_year',
                    'BrmPosition.position_name_jp',
                    'BrmPosition.position_name_en',
                    'BrmPosition.unit_salary',
                    'BrmPosition.display_no',
                    'BrmPosition.edit_flag',
                    'BrmPosition.flag'
                ),
                'order' => 'BrmPosition.brm_field_id'
            );
            $list = h($this->Paginator->paginate('BrmPosition', array(), array('BrmPosition.target_year','BrmPosition.display_no','BrmPosition.brm_field_id','BrmPosition.id')));
            
            #show total row msg
            $rowCount = $this->params['paging']['BrmPosition']['count'];
            if ($rowCount == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
                $this->set('errmsg', "");
            }

            #get year
            $years = $this->BrmPosition->find('all', array(
                            'conditions' => array('BrmPosition.flag' => 1,
                                                  'BrmField.flag' => 1
                                                ),
                            'fields' => array('BrmPosition.target_year'),
                            'group'  => array('target_year')));


            #min and max year for copy
            $copy_year = $this->BrmTerm->find('all', array(
                         'conditions' => array('flag' => 1),
                         'fields'	  => array('min(budget_year) as start',
                                                'max(budget_end_year) as end')
                        ));
            $start = $copy_year[0][0]['start'];
            $end   = $copy_year[0][0]['end'];
            $copy_year_datas = range($start, $end);

            $this->set(compact('list', 'rowCount', 'years', 'target_year', 'copy_year_datas'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
        }
    }
    /**
     * SearchData method
     *
     * @author Aye Zar Ni Kyaw (20201009)
     * @return sucess
     */
    public function CopyPositionMp()
    {
        $from_year = $this->request->data['hid_form_year'];
        $copy_year = $this->request->data['to_year'];
        $login_id= $this->Session->read('LOGIN_ID');

        #field
        $field_name_from = $this->BrmField->find('list', array(
                            'fields'	 => array('field_name_jp'),
                            'conditions' => array(
                                'BrmField.target_year'  => $from_year,
                                'BrmField.flag'		    => 1)
                        ));
                       
        foreach ($field_name_from as $id => $name) {
            $field_name_copy[] = $this->BrmField->find('first', array(
                                'fields'	 => array('field_name_jp'),
                                'conditions' => array(
                                    'BrmField.field_name_jp'    => $name,
                                    'BrmField.target_year'      => $copy_year,
                                    'BrmField.flag'		        => 1)
                            ))['BrmField']['field_name_jp'];
        }
        $fields_names = array_diff($field_name_from, $field_name_copy);
        
        #positionmp
        $position_name_from = $this->BrmPosition->find('list', array(
                            'fields'	                    => array('position_name_jp'),
                            'conditions'                    => array(
                                'BrmPosition.target_year'   => $from_year,
                                'BrmPosition.flag'		    => 1)
                        ));

        foreach ($position_name_from as $id => $name) {
            $position_name_copy[] = $this->BrmPosition->find('first', array(
                                'fields'	 => array('position_name_jp'),
                                'conditions' => array(
                                    'BrmPosition.position_name_jp'  => $name,
                                    'BrmPosition.target_year'       => $copy_year,
                                    'BrmPosition.flag'		        => 1)
                            ))['BrmPosition']['position_name_jp'];
        }
        $position_names = array_diff($position_name_from, $position_name_copy);

       
        if (!empty($fields_names || !empty($position_names))) { 
            #if have from_year of diff field_name,copy to fields tbl
            if (!empty($fields_names)) {
                #fields save
                $fields = $this->BrmField->find('all', array(
                        'conditions' => array(
                            'BrmField.field_name_jp IN' => $fields_names,
                            'BrmField.target_year'      => $from_year,
                            'BrmField.flag'		        => 1)
                    ));
                foreach ($fields as $data) {
                    $save_fielddatas[] = array(
                        'target_year' 	=> $copy_year,
                        'field_name_jp' => $data['BrmField']['field_name_jp'],
                        'field_name_en' => $data['BrmField']['field_name_en'],
                        'overtime_rate' => $data['BrmField']['overtime_rate'],
                        'flag' 			=> $data['BrmField']['flag'],
                        'created_by' 	=> $login_id,
                        'updated_by' 	=> $login_id,
                        'created_date'  => date("Y-m-d H:i:s"),
                        'updated_date'  => date("Y-m-d H:i:s")

                    );
                }
                
                $this->BrmField->saveAll($save_fielddatas);
            }
            #if have from_year of diff positon_name,copy to position tbl
            if (!empty($position_names)) {
                #positonmp save
                $position = $this->BrmPosition->find('all', array(
                        'conditions' => array(
                            'BrmPosition.position_name_jp IN' => $position_names,
                            'BrmPosition.target_year' => $from_year,
                            'BrmPosition.flag'		  => 1)
                    ));

                $field_data = $this->BrmField->find('list', array(
                            'fields' => array('id', 'field_name_jp'),
                            'conditions' => array(
                                                'flag'          => 1,
                                                'target_year'   => $copy_year)
                        ));

                foreach ($position as $data) {
                    $field_data_id = array_search($data['BrmField']['field_name_jp'], $field_data);
                    $save_datas[] = array(
                          'target_year'  	=> $copy_year,
                          'brm_field_id'   		=> $field_data_id,
                          'position_name_jp'=> $data['BrmPosition']
                                                ['position_name_jp'],
                          'position_name_en'=> $data['BrmPosition']
                                                ['position_name_en'],
                          'unit_salary'   	=> $data['BrmPosition']
                                                ['unit_salary'],
                          'percentage'		=> $data['BrmPosition']
                                                ['percentage'],
                          'display_no'   	=> $data['BrmPosition']
                                                ['display_no'],
                          'edit_flag'		=> $data['BrmPosition']
                                                ['edit_flag'],
                          'flag'   			=> $data['BrmPosition']
                                                ['flag'],
                          'created_by'   	=> $login_id,
                          'updated_by'		=> $login_id,
                          'created_date'   	=> date("Y-m-d H:i:s"),
                          'updated_date'	=> date("Y-m-d H:i:s")

                    );
                }
                $this->BrmPosition->saveAll($save_datas);
                $successMsg = parent::getSuccessMsg('SS025', '役職データ');
                $this->Flash->set($successMsg, array("key"=>"PositionmpOK"));
            }
        } elseif (empty($fields_names) && empty($position_names)) {
            #from data and copy data is same stage
            $successMsg = parent::getSuccessMsg('SS025', '役職データ');
            $this->Flash->set($successMsg, array("key"=>"PositionmpOK"));
        } else {
            $errorMsg = parent::getErrorMsg('SE017', __("コピー年度"));
            $this->Flash->set($errorMsg, array("key"=>"PositionmpFail"));
        }

        $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
    }
    /**
     * SearchData method
     *
     * @author Aye Zar Ni Kyaw (20201028)
     * @return sucess
     */
    public function getToYearData()
    {
        // $this->request->onlyAllow('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $copy_year = $this->request->data('copy_year');

        $poistionmp_data = $this->BrmPosition->find('first', array(
                            'conditions' => array(
                                'BrmPosition.target_year' => $copy_year,
                                'BrmPosition.flag'		 => 1,
                                'BrmField.flag'		 	 => 1)
                            ));

        if (!empty($poistionmp_data)) {
            $response_year = true;
        } else {
            $response_year = false;
        }

        $response = array(
            'response_year' => $response_year
        );

        echo json_encode($response);
    }

    public function getFirstTwoFieldName($target_year, $ba_code)
    {
        $data = $this->BrmPosition->find('list', array(
            'fields' => array(
                'BrmField.field_name_jp'
            ),
            'conditions' => array(
                'BrmPosition.flag' => 1,
                'BrmPosition.target_year' => $target_year,
                'BrmField.target_year'=> $target_year,
                'BrmPosition.display_no' => 1,
            ),
            'joins' => array(
                array(
                    'table' => 'brm_fields',
                    'alias' => 'BrmField',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'BrmPosition.brm_field_id = BrmField.id',
                        'BrmField.flag' => 1
                    )
                )
            ),
            'group' => array('BrmPosition.brm_field_id'),
            'order' => array(
                'BrmPosition.brm_field_id ASC',
                'BrmPosition.id ASC'
            ),
            'limit'=>2
        ));

        return array_values($data);
    }
    /**
     * SearchData method
     *
     * @author Nu Nu Lwin (20211203)
     * @return sucess
     */
    public function OverwirteDataCopy()
    {
        $login_id  = $this->Session->read('LOGIN_ID');
        $from_year = $this->request->data['hid_form_year'];
        $copy_year = $this->request->data['to_year'];

        $getSaveData = $this->compareTwoArray($from_year, $copy_year);
    
        $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
    }

    /**
 * compareTwoArray method
 *
 * @author Nu Nu Lwin (20211201)
 * @return sucess
 */
    public function compareTwoArray($from_year, $copy_year)
    {
        $orginalPosition = $this->BrmPosition->find('all', array(
                            'conditions' => array(
                                'BrmPosition.target_year' => $from_year,
                                'BrmPosition.flag'		  => 1)
                        ));
       
        $createPosition = $this->BrmPosition->find('all', array(
                'conditions' => array(
                    'BrmPosition.target_year' => $copy_year,
                    'BrmPosition.flag'		  => 1
                )));
               
        $orginalField = $this->BrmField->find('all', array(
                            'conditions' => array(
                                'BrmField.target_year' => $from_year,
                                'BrmField.flag'		  => 1)
                        ));
                        
        $createField = $this->BrmField->find('all', array(
                'conditions' => array(
                    'BrmField.target_year' => $copy_year,
                    'BrmField.flag'		  => 1
                )));
            
        $orgPosArray = [];
        $crtPosArray = [];
        $orgFieldArray = [];
        $crtFieldArray = [];

        foreach ($orginalPosition as $orgKey => $orgValue) {
            $orgPos = $orgValue['BrmPosition'];
            unset($orgPos['id']);
            unset($orgPos['target_year']);
            unset($orgPos['brm_field_id']);
            unset($orgPos['created_by']);
            unset($orgPos['updated_by']);
            unset($orgPos['created_date']);
            unset($orgPos['updated_date']);
            $orgPos['field_name_jp'] = $orgValue['BrmField']['field_name_jp'];
            array_push($orgPosArray, $orgPos);
        }    
        foreach ($createPosition as $crtKey => $crtValue) {
            $crtPos = $crtValue['BrmPosition'];
            unset($crtPos['id']);
            unset($crtPos['target_year']);
            unset($crtPos['brm_field_id']);
            unset($crtPos['created_by']);
            unset($crtPos['updated_by']);
            unset($crtPos['created_date']);
            unset($crtPos['updated_date']);
            $crtPos['field_name_jp'] = $crtValue['BrmField']['field_name_jp'];
            array_push($crtPosArray, $crtPos);
        }

        foreach ($orginalField as $orgFKey => $orgFValue) {
            $orgField = $orgFValue['BrmField'];
            unset($orgField['id']);
            unset($orgField['target_year']);
            unset($orgField['flag']);
            unset($orgField['created_by']);
            unset($orgField['updated_by']);
            unset($orgField['created_date']);
            unset($orgField['updated_date']);
            array_push($orgFieldArray, $orgField);
        }
        foreach ($createField as $crtFKey => $crtFValue) {
            $crtField = $crtFValue['BrmField'];
            unset($crtField['id']);
            unset($crtField['target_year']);
            unset($crtField['flag']);
            unset($crtField['created_by']);
            unset($crtField['updated_by']);
            unset($crtField['created_date']);
            unset($crtField['updated_date']);
            array_push($crtFieldArray, $crtField);
        }
      
        $resultFieldDup 	= $this->array_merge_overwrite($crtFieldArray, $orgFieldArray, $uniques=array('field_name_jp'));
       
        $getSaveFieldData 	= $this->getSaveFieldData($createField, $resultFieldDup, $copy_year);
        
        try {
            $FieldDB = $this->BrmField->getDataSource();
            $FieldDB->begin();

            $this->BrmField->deleteAll([
                        'BrmField.target_year' => $copy_year,
                        'BrmField.flag' => 1
                    ]);
            foreach ($getSaveFieldData as $keySave => $valueSave) {
                $this->BrmField->saveAll($valueSave);
            }
            $FieldDB->commit();
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $FieldDB->rollback();
        }


        $resultPositionDup	 = $this->array_merge_overwrite($crtPosArray, $orgPosArray, $uniques = array('position_name_jp','field_name_jp'));
        $getSavePositionData = $this->getSavePositoinData($createPosition, $resultPositionDup, $copy_year);
        

        try {
            $PositionDB = $this->BrmPosition->getDataSource();
            $PositionDB->begin();

            $this->BrmPosition->deleteAll([
                        'BrmPosition.target_year' => $copy_year,
                        'BrmPosition.flag' => 1
                    ]);
            foreach ($getSavePositionData as $keySave => $valueSave) {
                $this->BrmPosition->saveAll($valueSave);
            }
            $PositionDB->commit();
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $PositionDB->rollback();
        }
        
        return true;
    }
    /**
     * array_merge_overwrite method
     *
     * @author Nu Nu Lwin (20211202)
     * @return sucess
     */
    public function array_merge_overwrite($arr1, $arr2, $uniques, $delimiter='/')
    {
        $result = array();
        $uk = array();
        foreach ($arr1 as $a1) {
            $uk = array();
            foreach ($uniques as $u) {
                $uk[] = $a1[$u];
            }
            $result[implode($delimiter, $uk)] = $a1;
        }

        foreach ($arr2 as $a2) {
            $uk = array();
            foreach ($uniques as $u) {
                $uk[] = $a2[$u];
            }
            $result[implode($delimiter, $uk)] = $a2;
        }
        
        return $result;
    }
    /**
     * getSaveData method (for field)
     *
     * @author Nu Nu Lwin (20211202)
     * @return sucess
     */
    public function getSaveFieldData($createField, $resultFieldDup, $copy_year)
    {
        $login_id  = $this->Session->read('LOGIN_ID');
        $saveFieldDatas = array();

        foreach ($resultFieldDup as $keyRes => $valueRes) {
            $new_jp_name 	= $valueRes['field_name_jp'];
            $new_eng_name 	= $valueRes['field_name_en'];
            $new_OT 		= $valueRes['overtime_rate'];
            $chk_flag = 0;

            foreach ($createField as $keyF => $valueF) {
                $FieldId 		= $valueF['BrmField']['id'];
                $jpName 		= $valueF['BrmField']['field_name_jp'];
                $createdId 		= $valueF['BrmField']['created_by'];
                $createdDate 	= $valueF['BrmField']['created_date'];

                if ($new_jp_name == $jpName) {
                    $chk_flag = 1;
                    $saveFieldDatas[] = array(
                            'id'			=> $FieldId,
                            'target_year' 	=> $copy_year,
                            'field_name_jp' => $jpName,
                            'field_name_en' => $new_eng_name,
                            'overtime_rate' => $new_OT,
                            'flag' 			=> '1',
                            'created_by' 	=> $createdId,
                            'updated_by' 	=> $login_id,
                            'created_date' 	=> $createdDate,
                            'updated_date' 	=> date("Y-m-d H:i:s")

                    );
                }
            }
            if ($chk_flag == 0) {
                if (!array_search($new_jp_name, array_column($saveFieldDatas, 'field_name_jp'))) {
                    $saveFieldDatas[] = array(
                        'target_year' 	=> $copy_year,
                        'field_name_jp' => $new_jp_name,
                        'field_name_en' => $new_eng_name,
                        'overtime_rate' => $new_OT,
                        'flag' 			=> '1',
                        'created_by' 	=> $login_id,
                        'updated_by' 	=> $login_id,
                        'created_date' 	=> date("Y-m-d H:i:s"),
                        'updated_date' 	=> date("Y-m-d H:i:s")

                    );
                }
            }
        }

        return $saveFieldDatas;
    }
    //for position
    public function getSavePositoinData($createPosition, $resultPositionDup, $copy_year)
    {
        $login_id  = $this->Session->read('LOGIN_ID');
        $savePositionDatas = array();
        
        foreach ($resultPositionDup as $keyRes => $valueRes) {
            $new_ps_name_jp 	= $valueRes['position_name_jp'];
            $new_ps_name_en 	= $valueRes['position_name_en'];
            $new_unit_salary 	= $valueRes['unit_salary'];
            $new_percentage 	= $valueRes['percentage'];
            $new_display_no 	= $valueRes['display_no'];
            $new_edit_flag	 	= $valueRes['edit_flag'];
            $new_field_name 	= $valueRes['field_name_jp'];
            $chk_flag = 0;
           
            $getNewFieldId = $this->BrmField->find('first', array(
                                        'fields' => array('id','field_name_jp'),
                                        'conditions' => array(
                                            'BrmField.target_year' => $copy_year,
                                            'BrmField.field_name_jp'=> $new_field_name,
                                            'BrmField.flag'		  => 1
                                        )));
            $i = 0;
            foreach ($createPosition as $keyP => $valueP) {
                $positionId 	= $valueP['BrmPosition']['id'];
                $fieldId 		= $valueP['BrmPosition']['brm_field_id'];
                $positionName 	= $valueP['BrmPosition']['position_name_jp'];
                $fieldName 		= $valueP['BrmField']['field_name_jp'];
                $createdId 		= $valueP['BrmPosition']['created_by'];
                $createdDate 	= $valueP['BrmPosition']['created_date'];
                $i++;
                if ($positionName == $new_ps_name_jp && $new_field_name == $fieldName) {
                    $chk_flag = 1;

                    $savePositionDatas[] = array(
                        'id'				=> $positionId,
                        'target_year' 		=> $copy_year,
                        'brm_field_id'		=> $fieldId,
                        'position_name_jp' 	=> $new_ps_name_jp,
                        'position_name_en' 	=> $new_ps_name_en,
                        'unit_salary' 		=> $new_unit_salary,
                        'percentage' 		=> $new_percentage,
                        'display_no' 	    => $new_display_no,
                        'edit_flag' 	    => $new_edit_flag,
                        'flag' 			    => '1',
                        'created_by' 	    => $createdId,
                        'updated_by' 	    => $login_id,
                        'created_date' 	    => $createdDate,
                        'updated_date' 	    => date("Y-m-d H:i:s")

                    );
                } elseif ($positionName == $new_ps_name_jp && $new_field_name != $fieldName) {
                    $chk_flag = 1;
                        
                    $bb = $this->get_in_array($getNewFieldId['BrmField']['id'], $savePositionDatas, 'field_id');
                    $cc = $this->get_in_array($fieldId, $savePositionDatas, 'brm_field_id');
                    
                    if (empty($bb) && empty($cc)) {
                        $savePositionDatas[] = array(
                                'target_year' 		=> $copy_year,
                                'brm_field_id'	    => $getNewFieldId['BrmField']['id'],
                                'position_name_jp' 	=> $new_ps_name_jp,
                                'position_name_en' 	=> $new_ps_name_en,
                                'unit_salary' 		=> $new_unit_salary,
                                'percentage' 		=> $new_percentage,
                                'display_no' 		=> $new_display_no,
                                'edit_flag' 		=> $new_edit_flag,
                                'flag' 				=> '1',
                                'created_by' 		=> $login_id,
                                'updated_by' 		=> $login_id,
                                'created_date' 		=> date("Y-m-d H:i:s"),
                                'updated_date' 		=> date("Y-m-d H:i:s")

                            );
                    }
                }
            }
            if ($chk_flag == 0) {
                if (!array_search($new_ps_name_jp, array_column($savePositionDatas, 'position_name_jp'))) {
                    if (!empty($getNewFieldId)) {
                        $savePositionDatas[] = array(
                            'target_year' 		=> $copy_year,
                            'brm_field_id'		=> $getNewFieldId['BrmField']['id'],
                            'position_name_jp' 	=> $new_ps_name_jp,
                            'position_name_en' 	=> $new_ps_name_en,
                            'unit_salary' 		=> $new_unit_salary,
                            'percentage' 		=> $new_percentage,
                            'display_no' 		=> $new_display_no,
                            'edit_flag' 		=> $new_edit_flag,
                            'flag' 				=> '1',
                            'created_by' 		=> $login_id,
                            'updated_by' 		=> $login_id,
                            'created_date' 		=> date("Y-m-d H:i:s"),
                            'updated_date' 		=> date("Y-m-d H:i:s")

                        );
                    } else {
                        CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class empty field id error' . get_class());
                        $this->redirect(array('controller'=>'BrmPositions', 'action'=>'index'));
                    }
                }
            }
        }
      
        return $savePositionDatas;
    }

    public function get_in_array(string $needle, array $haystack, string $column)
    {
        $matches = [];
        foreach ($haystack as $item) {
            if ($item[ $column ] === $needle) {
                $matches[] = $item;
            }
        }
        return $matches;
    }
}
