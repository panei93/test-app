<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Positionss Controller
 *
 * @property Positions $Positions
 * @property PaginatorComponent $Paginator
 */
class PositionsController extends AppController
{

    /**
     * Components
     *
     * @var array
     */
    public $uses = array('Position');
    public $components = array('Session', 'Flash', 'Paginator');

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
        parent::checkSettingSession($this->name);

        // if ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) {
        //     $this->redirect(array('controller' => 'Logins', 'action' => 'logout'));
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

        $conditions = [];
        $conditions["Position.flag"] = 1;

        $search_year = $this->request->query('year');
        if ($search_year) {
            $this->Session->write('SEARCH_YEAR', $search_year);
            $conditions["Position.target_year"] = $search_year;
        } else {
            $this->Session->write('SEARCH_YEAR', '');
        }

        try {

            $this->paginate = array(
                'limit' => Paging::TABLE_PAGING,
                'conditions' => $conditions,
                'fields' => array(
                    'Position.id',
                    'Position.position_type',
                    'Position.target_year',
                    'Position.position_name',
                    'Position.personnel_cost',
                    'Position.corporate_cost',
                    'Position.flag',
                ),
                'order' => 'Position.target_year'
            );
            $list = $this->Paginator->paginate(
                'Position',
                array(),
                array(
                    'Position.target_year',
                    'Position.position_type',
                    'Position.id'
                )
            );
            #show total row msg
            $rowCount = $this->params['paging']['Position']['count'];
            if ($rowCount == 0) {
                $this->set('errmsg', parent::getErrorMsg('SE001'));
                $this->set('succmsg', "");
            } else {
                $this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
                $this->set('errmsg', "");
            }

            #get year
            $years = $this->Position->find('all', array(
                'conditions' => array(
                    'Position.flag' => 1,
                ),
                'fields' => array('Position.target_year'),
                'group'  => array('target_year')
            ));


            $this->set(compact('list', 'rowCount', 'years', 'search_year'));
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'Positions', 'action' => 'index'));
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
        $this->Position->recursive = 0; // not to select other related table data
        $login_id = $this->Session->read('LOGIN_ID');
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $actual_link = $_SERVER['HTTP_REFERER'];
            if (!empty($data)) {

                $date = date('Y-m-d H:i:s');
                $data["flag"] = 1;
                $data["created_by"] = $login_id;
                $data["updated_by"] = $login_id;
                $data["created_date"] = $date;
                $data["updated_date"] = $date;
                $data['position_name'] = $data['position_name'] ? trim($data['position_name']) : '';
                $data['personnel_cost'] = $data['personnel_cost'] ? trim($data['personnel_cost']) : 0.00;
                $data['corporate_cost'] = $data['corporate_cost'] ? trim($data['corporate_cost']) : 0.00;

                $dup_pos_data = $this->Position->find('all', array(
                    'conditions' => array(
                        'Position.target_year' => $data['target_year'],
                        'Position.position_type' => $data['position_type'],
                        'Position.position_name' => $data['position_name'],
                    )
                ));

                if (empty($dup_pos_data)) {
                    // check existing position_code if there is no duplicated position
                    $existing_pos_code = $this->Position->find('first', [
                        'fields' => ['Position.position_code'],
                        'conditions' => [
                            'Position.position_type' => $data['position_type'],
                            'Position.position_name' => $data['position_name'],
                            'Position.flag' => 1,
                        ]
                    ]);
                    if(!empty($existing_pos_code)){
                        // save with existing position_code
                        $data['position_code'] = $existing_pos_code['Position']['position_code'];
                    } else {
                        // save with new incremented position_code if there is no existing position_code
                        $position_code = $this->Position->find('first', [
                            'fields' => ['MAX(position_code) AS max_position_code'],
                            'order' => ['position_code' => 'DESC'],
                        ]);
                        if(!empty($position_code)){
                            $position_code = $position_code[0]['max_position_code'];
                            $position_code += 1;
                            $data['position_code'] = $position_code;    
                        }
                    }
                    try {
                        $this->Position->create();
                        $status = $this->Position->save($data);
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE003');
                        $this->Flash->set($msg, array('key' => 'PositionsFail'));
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                        $this->redirect($actual_link);
                    }
                } else {
                    if($dup_pos_data[0]['Position']['flag'] == 0){
                        // to change flag 1 if the position is existing deleted position(flag 0)
                        $data['id'] = $dup_pos_data[0]['Position']['id']; 
                        try{
                            $this->Position->create();
                            $status = $this->Position->save($data);
                        }catch (Exception $e) {
                            $msg = parent::getErrorMsg('SE003');
                            $this->Flash->set($msg, array('key' => 'PositionsFail'));
                            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                            $this->redirect($actual_link);
                        }
                    }else{
                        $msg = parent::getErrorMsg('SE002', __("ポジション"));
                        $this->Flash->set($msg, array('key' => 'PositionsFail'));
                        $this->redirect($actual_link);
                    }
                }
                if ($status) {
                    $msg = parent::getSuccessMsg('SS001');
                    $this->Flash->set($msg, array('key' => 'PositionsOK'));
                    $this->redirect($actual_link);
                } else {
                    $msg = parent::getErrorMsg('SE003');
                    $this->Flash->set($msg, array('key' => 'PositionsFail'));
                    $this->redirect($actual_link);
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
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $edit_posid = $this->request->data('id');
        $position_data = $this->Position->find('first', array(
            'conditions' => array(
                'Position.id' => $edit_posid,
                'Position.flag' => 1
            )
        ));

        $response = $position_data['Position'];
        $response['position_code'] = $response['position_code'];
        $response['field_name_jp'] = PositionType::Types[$response['position_type']];
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
        $this->Position->recursive = 0; // not to select other related table data
        $this->layout = 'mastermanagement';
        $login_id = $this->Session->read('LOGIN_ID');
        $actual_link = $_SERVER['HTTP_REFERER'];
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $search_year = $this->Session->read('SEARCH_YEAR');
            $page_no = $data['hid_page_no'];
            $id = $data['hid_updateId'];
            $position_code = $data['hid_position_code'];
            $target_year = $data['target_year'];
            $position_type = $data['position_type'];
            $position_name = trim($data['position_name']);
            $personnel_cost = $data['personnel_cost'] ? trim($data['personnel_cost']) : 0.00;
            $corporate_cost = $data['corporate_cost'] ? trim($data['corporate_cost']) : 0.00;
            $date = date('Y-m-d H:i:s');
            $position = [];

            if (!empty($data)) {
                $dup_pos_data = $this->Position->find('first', array(
                    'fields' => array('Position.id'),
                    'conditions' => array(
                        'Position.id !=' => $id,
                        'Position.target_year' => $target_year,
                        'Position.position_type' => $position_type,
                        'Position.position_name' => $position_name,
                        'Position.flag' => 1,
                    )
                ));
               if(empty($dup_pos_data)) {
                    // no duplicated position
                    try {
                        # retrieve position for update
                        $check_position = $this->Position->find('first', [
                            'conditions' => array(
                                'Position.position_type' => $position_type,
                                'Position.position_name' => $position_name,
                                'Position.flag' => 1,
                            )
                        ]);
                        if(empty($check_position)){
                            // for new incremented position code
                            $max_position_code = $this->Position->find('first', [
                                'fields' => ['MAX(position_code) AS max_position_code'],
                                'order' => ['position_code' => 'DESC'],
                            ]);
                            if(!empty($max_position_code)){
                                $new_position_code = $max_position_code[0]['max_position_code'] + 1;
                            }
                            $position['Position']['position_code'] = $new_position_code;
                        } else {
                            // for existing position code
                            $position['Position']['position_code'] = $check_position['Position']['position_code'];
                        }

                        $position['Position']['id'] = $id;
                        $position['Position']['target_year'] = $target_year;
                        $position['Position']['position_type'] = $position_type;
                        $position['Position']['position_name'] = $position_name;
                        $position['Position']['personnel_cost'] = $personnel_cost;
                        $position['Position']['corporate_cost'] = $corporate_cost;
                        $position['Position']['flag'] = 1;
                        $position['Position']['updated_date'] = $date;
                        $position['Position']['updated_by'] = $login_id;
                        if ($this->Position->save($position)) {
                            $msg = parent::getSuccessMsg("SS002");
                            $this->Flash->set($msg, array('key' => 'PositionsOK'));
                            $this->redirect($actual_link);
                        } else {
                            // Error saving the data
                            $msg = parent::getErrorMsg('SE011', __("変更"));
                            $this->Flash->set($msg, array('key' => 'PositionsFail'));
                            $this->redirect($actual_link);
                        }
                    } catch (Exception $e) {
                        $msg = parent::getErrorMsg('SE011', __("変更"));
                        $this->Flash->set($msg, array('key' => 'PositionsFail'));
                        CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                        $this->redirect($actual_link);
                    }
                } else {
                    $msg = parent::getErrorMsg('SE002', __("ポジション"));
                    $this->Flash->set($msg, array('key' => 'PositionsFail'));
                    $this->redirect($actual_link);
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
            $search_year = $this->Session->read('SEARCH_YEAR');

            $login_id = $this->Session->read('LOGIN_ID');
            $date = date('Y-m-d H:i:s');
            try {
                $this->Position->updateAll(
                    array(
                        "flag" => '0',
                        "updated_date" => "'" . $date . "'",
                        "updated_by" => "'" . $login_id . "'"
                    ),
                    array(
                        "Position.id" => $hid_deleteid,
                        "Position.flag" => '1'
                    )
                );
                $delete_status = $this->Position->getAffectedRows();

                if ($delete_status > 0) {
                    $msg = parent::getSuccessMsg("SS003");
                    $this->Flash->set($msg, array('key' => 'PositionsOK'));

                    if ($search_year != "") {
                        $queryYear = 'year=' . $search_year;
                        $this->redirect(array(
                            'controller' => 'Positions',
                            'action' => 'index/' . $page_no . '?' . $queryYear
                        ));
                    }
                    $this->redirect(array(
                        'controller' => 'Positions',
                        'action' => 'index/' . $page_no
                    ));
                } else {
                    $errorMsg = parent::getErrorMsg('SE050');
                    $this->Flash->set($errorMsg, array("key" => "PositionsFail"));
                    $this->redirect(array(
                        'controller' => 'Positions',
                        'action' => 'index/' . $page_no . '?year=' . $search_year
                    ));
                }
            } catch (Exception $e) {
                $errorMsg = parent::getErrorMsg('SE050');
                $this->Flash->set($errorMsg, array('key' => 'PositionsFail'));
                CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
                $this->redirect(array(
                    'controller' => 'Positions',
                    'action' => 'index/' . $page_no . '?year=' . $search_year
                ));
            }
        }
    }

    /**
     * SearchData method
     *
     * @author Aye Zar Ni Kyaw (20201009)
     * @return sucess
     */
    public function CopyPositions()
    {
        $from_year = $this->request->data['hid_form_year'];
        $copy_year = $this->request->data['to_year'];
        $login_id = $this->Session->read('LOGIN_ID');

        $field_name = PositionType::Types;

        #Positions
        $position_name_from = $this->Position->find('list', array(
            'fields'     => array('position_name'),
            'conditions' => array(
                'Position.target_year' => $from_year,
                'Position.flag'          => 1
            )
        ));

        foreach ($position_name_from as $id => $name) {
            $position_name_copy[] = $this->Position->find('first', array(
                'fields'     => array('position_name'),
                'conditions' => array(
                    'Position.position_name' => $name,
                    'Position.target_year'  => $copy_year,
                    'Position.flag'          => 1
                )
            ))['Position']['position_name'];
        }

        $position_names = array_diff($position_name_from, $position_name_copy);

        if (!empty($position_names)) {
            #if have from_year of diff positon_name,copy to position tbl
            #positonmp save
            $position = $this->Position->find('all', array(
                'conditions' => array(
                    'Position.position_name IN' => $position_names,
                    'Position.target_year' => $from_year,
                    'Position.flag'          => 1
                )
            ));


            foreach ($position as $data) {

                $save_datas[] = array(
                    'target_year' => $copy_year,
                    'position_type'           => $data['Position']['position_type'],
                    'position_code' => $data['Position']['position_code'],
                    'position_name' => $data['Position']['position_name'],
                    'personnel_cost' => $data['Position']['personnel_cost'],
                    'corporate_cost' => $data['Position']['corporate_cost'],
                    'percentage' => $data['Position']['percentage'],

                    'flag' => $data['Position']['flag'],
                    'created_by' => $login_id,
                    'updated_by' => $login_id,
                    'created_date' => date("Y-m-d H:i:s"),
                    'updated_date' => date("Y-m-d H:i:s")

                );
            }
            $this->Position->saveAll($save_datas);
            $successMsg = parent::getSuccessMsg('SS025', __('データ'));
            $this->Flash->set($successMsg, array("key" => "PositionsOK"));
        } elseif (empty($position_names)) {
            #from data and copy data is same stage
            $successMsg = parent::getSuccessMsg('SS025', __('データ'));
            $this->Flash->set($successMsg, array("key" => "PositionsOK"));
        } else {
            $errorMsg = parent::getErrorMsg('SE017', __("コピー年度"));
            $this->Flash->set($errorMsg, array("key" => "PositionsFail"));
        }

        $this->redirect(array('controller' => 'Positions', 'action' => 'index'));
    }
    /**
     * SearchData method
     *
     * @author Aye Zar Ni Kyaw (20201028)
     * @return sucess
     */
    public function getToYearData()
    {
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $copy_year = $this->request->data('copy_year');

        $poistionmp_data = $this->Position->find('first', array(
            'conditions' => array(
                'Position.target_year' => $copy_year,
                'Position.flag'         => 1,
            )
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

    public function getFirstTwoFieldName($target_year, $layer_code)
    {
        $data = $this->Position->find('list', array(
            'fields' => array(
                'FieldModel.field_name_jp'
            ),
            'conditions' => array(
                'Position.flag' => 1,
                'Position.target_year' => $target_year,
                'FieldModel.target_year' => $target_year,
                'Position.display_no' => 1,
            ),
            'joins' => array(
                array(
                    'table' => 'tbl_field',
                    'alias' => 'FieldModel',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Position.position_type = FieldModel.id',
                        'FieldModel.flag' => 1
                    )
                )
            ),
            'group' => array('Position.position_type'),
            'order' => array(
                'Position.position_type ASC',
                'Position.id ASC'
            ),
            'limit' => 2
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
        if($getSaveData) {
            $successMsg = parent::getSuccessMsg('SS025', __('データ'));
            $this->Flash->set($successMsg, array("key" => "PositionsOK"));
        }else {
            $errorMsg = parent::getErrorMsg('SE017', __("コピー年度"));
            $this->Flash->set($errorMsg, array("key" => "PositionsFail"));
        }
        $this->redirect(array('controller' => 'Positions', 'action' => 'index'));
    }

    /**
     * compareTwoArray method
     *
     * @author Nu Nu Lwin (20211201)
     * @return sucess
     */
    public function compareTwoArray($from_year, $copy_year)
    {
        $orginalPosition = $this->Position->find('all', array(
            'conditions' => array(
                'Position.target_year' => $from_year,
                'Position.flag'          => 1
            )
        ));

        $createPosition = $this->Position->find('all', array(
            'conditions' => array(
                'Position.target_year' => $copy_year,
                'Position.flag'          => 1
            )
        ));

        $orgPosArray = [];
        $crtPosArray = [];

        foreach ($orginalPosition as $orgKey => $orgValue) {
            $orgPos = $orgValue['Position'];
            unset($orgPos['id']);
            unset($orgPos['target_year']);
            // unset($orgPos['position_type']);
            unset($orgPos['created_by']);
            unset($orgPos['updated_by']);
            unset($orgPos['created_date']);
            unset($orgPos['updated_date']);
            // $orgPos['field_name_jp'] = $orgValue['FieldModel']['field_name_jp'];
            array_push($orgPosArray, $orgPos);
        }

        foreach ($createPosition as $crtKey => $crtValue) {
            $crtPos = $crtValue['Position'];
            unset($crtPos['id']);
            unset($crtPos['target_year']);
            // unset($crtPos['position_type']);
            unset($crtPos['created_by']);
            unset($crtPos['updated_by']);
            unset($crtPos['created_date']);
            unset($crtPos['updated_date']);
            // $crtPos['field_name_jp'] = $crtValue['FieldModel']['field_name_jp'];
            array_push($crtPosArray, $crtPos);
        }

        $resultPositionDup     = $this->array_merge_overwrite($crtPosArray, $orgPosArray, $uniques = array('position_name'));
        $getSavePositionData = $this->getSavePositoinData($createPosition, $resultPositionDup, $copy_year);

        try {
            $PositionDB = $this->Position->getDataSource();
            $PositionDB->begin();

            $this->Position->deleteAll([
                'Position.target_year' => $copy_year,
                'Position.flag' => 1
            ]);
            // foreach ($getSavePositionData as $keySave => $valueSave) {
            $this->Position->saveAll($getSavePositionData);
            // }
            $PositionDB->commit();
            $return = true;
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            $PositionDB->rollback();
            $return = false;
        }

        return $return;
    }
    /**
     * array_merge_overwrite method
     *
     * @author Nu Nu Lwin (20211202)
     * @return sucess
     */
    public function array_merge_overwrite($arr1, $arr2, $uniques, $delimiter = '/')
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

    //for position
    public function getSavePositoinData($createPosition, $resultPositionDup, $copy_year)
    {
        $login_id  = $this->Session->read('LOGIN_ID');
        $savePositionDatas = array();

        foreach ($resultPositionDup as $keyRes => $valueRes) {
            $new_ps_name     = $valueRes['position_name'];
            $new_personnel_cost     = $valueRes['personnel_cost'];
            $new_corporate_cost = $valueRes['corporate_cost'];
            $new_position_type     = $valueRes['position_type'];
            $new_position_code     = $valueRes['position_code'];
            $chk_flag = 0;
            $i = 0;
            foreach ($createPosition as $keyP => $valueP) {
                $positionId     = $valueP['Position']['id'];
                // $fieldId 		= $valueP['Position']['position_type'];
                $positionName     = $valueP['Position']['position_name'];
                $createdId         = $valueP['Position']['created_by'];
                $createdDate     = $valueP['Position']['created_date'];
                $i++;
                //if position already exist;
                if ($positionName == $new_ps_name) {
                    $chk_flag = 1;
                    $savePositionDatas[] = array(
                        'id'                => $positionId,
                        'target_year'         => $copy_year,
                        'position_code'            => $new_position_code,
                        'position_type'            => $new_position_type,
                        'position_name'     => $new_ps_name,
                        'personnel_cost'         => $new_personnel_cost,
                        'corporate_cost' => $new_corporate_cost,
                        'flag'             => '1',
                        'created_by'     => $createdId,
                        'updated_by'     => $login_id,
                        'created_date'     => $createdDate,
                        'updated_date'     => date("Y-m-d H:i:s")
                    );
                }
            }

            if ($chk_flag == 0) {
                $savePositionDatas[] = array(
                    'target_year'             => $copy_year,
                    'position_code'                => $new_position_code,
                    'position_type'                => $new_position_type,
                    'position_name'         => $new_ps_name,
                    'personnel_cost'     => $new_personnel_cost,
                    'corporate_cost' => $new_corporate_cost,
                    'flag'                     => '1',
                    'created_by'             => $login_id,
                    'updated_by'             => $login_id,
                    'created_date'             => date("Y-m-d H:i:s"),
                    'updated_date'             => date("Y-m-d H:i:s")
                );
            }
        }
        return $savePositionDatas;
    }

    function get_in_array(string $needle, array $haystack, string $column)
    {
        $matches = [];
        foreach ($haystack as $item)  if ($item[$column] === $needle)  $matches[] = $item;
        return $matches;
    }
}