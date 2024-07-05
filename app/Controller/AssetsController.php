<?php
/**
 *	AssetsController
 *	@author Thura Moe
 **/
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'Permissions');
# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

class AssetsController extends AppController
{
    public $uses = array('User', 'AssetEvent', 'Asset', 'AssetBusiIncComment', 'AssetBusiMgrApprove','AssetRemove','AssetSold', 'Layer');
    public $helper = array('form');
    public $components = array('Session', 'Flash', 'Paginator', 'PhpExcel.PhpExcel');

    public function beforeFilter()
    {
        $Common = New CommonController();
        $login_id = $this->Session->read('LOGIN_ID');
        $get_login_id = $this->Session->read('LOGINID');#login id
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $pagename = $this->request->params['controller'];

        $layer_code = $this->request->query('ba');
        $event_id = $this->request->query('param');
        // parent::checkUserStatus();
        // parent::CheckSession();
        #request from url (edited by khin hnin myo)  
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);

        $lan = $this->Session->read('Config.language');
        if (!empty($this->Session->read('EVENT_ID')) && !empty($this->Session->read('SESSION_LAYER_CODE')) && !empty($event_id) && !empty($layer_code)) { #from session

            if ($event_id != $this->Session->read('EVENT_ID') || $layer_code != $this->Session->read('SESSION_LAYER_CODE')) {#datas of url and session are not match
                $param = array();
                $param['event_name']  = __("イベント : ").$event_name;
                $param['layer_code'] = __("と BA : ").$layer_code;
                 
                $errorMsg = parent::getErrorMsg('SE060', $param);
                $this->Flash->set($errorMsg, array("key"=>"Error"));
                $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
            }
        } else {
            if (!empty($event_id) && !empty($layer_code)) { #from url
               
                $ba_data = $this->User->find('first', array(
                    'conditions'=>array(
                        'layer_code LIKE' => '%'.$layer_code.'%',
                        'id' => $get_login_id,
                        'flag' => '1'
                    )
                ));
                $date = $this->importedDate($event_id);
                $ba = $Common->getLayerThreeName($layer_code, date("Y-m-d", strtotime($date)));
                $layer_name = ($lan == 'eng') ? $ba['name_en'] : $ba['name_jp'];
                $event_name = $this->getEventName($event_id);

                if (!empty($ba_data) || $role_id==1) {
                    $this->Session->write('SESSION_LAYER_CODE', $layer_code);
                    $this->Session->write('BASIC_SELECTION_BA_NAME', $layer_name);
                    $this->Session->write('EVENT_ID', $event_id);
                    $this->Session->write('EVENT_NAME', $event_name);
                } else {
                    $errorMsg = parent::getErrorMsg('SE065');
                    $this->Flash->set($errorMsg, array("key"=>"Error"));
                    $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
                }
            } elseif (empty($this->Session->read('EVENT_ID')) && empty($this->Session->read('SESSION_LAYER_CODE'))) { #from session with login
                $errorMsg = parent::getErrorMsg('SE072', __('イベントと部署'));
                $this->Flash->set($errorMsg, array("key"=>"Error"));
                $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
            }
        }
    }

    /**
     *	Show Fixed Assets Data List
     *	sales users(level 7,6,5) can edit/approve/reject/approve cancel
     *  account users(level 4,3,2) only view
     * 	Admin(level 1) can delete
     **/
    public function index()
    {
        $this->layout = 'fixedassets';
        $no_data = '';
        $event_id = $this->Session->read('EVENT_ID');
        $event_name = $this->Session->read('EVENT_NAME');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $checkState = $this->checkState($user_level, $event_id,  $layer_code);
        //pr($checkState);die;
        $permissions = $this->Session->read('PERMISSIONS');
        $index_permt = $permissions['index'];
        
        if((!empty($layer_code) && !array_key_exists($layer_code,$index_permt['layers'])) || ($layer_code=="" && $index_permt['limit']>0)) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array("key"=>"Error"));
            $this->redirect(array('controller'=>'AssetSelections', 'action'=>'index'));
        }

        $language = $this->Session->read('Config.language');
        # delete search session of pdf
        $this->Session->delete('SRH_DATA_LIST');
        $searchConditions = $this->__searchCondition($event_id, $layer_code);
       
        try {
            $limit = 20;
            $rsl = $this->__preparePaginate($searchConditions, $limit);
            
            $cnt_rsl = count($rsl);
            if ($cnt_rsl > 0) {
                $data = $this->__getDataList($rsl);
            } else {
                $data = $rsl;
            }
        
            $count = count($data);
            $total_pages = $this->params['paging']['Asset']['pageCount'];
            $total_rows = $this->params['paging']['Asset']['count'];
            $row_count = parent::getSuccessMsg('SS004', $total_rows);
            # get user current page from pagination
            $page_no = $this->params['paging']['Asset']['page'];
            $this->Session->write('Page.pageCount', $page_no);
            if ($count == 0) {
                $no_data = parent::getErrorMsg("SE001");
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            // $this->redirect(array('controller'=>'Assets','action'=>'index', 'sort'=>'Asset.id','direction'=>'asc', '?'=>$this->Session->read('SRH_DATA_LIST')));
        }

        $checkFARowcnt = $total_rows - ($this->checkFARowcnt($layer_code, $event_id, 2));

        $this->set(compact('data', 'total_pages', 'count', 'row_count', 'layer_code', 'layer_name', 'no_data', 'user_level', 'toggleApprovedCancel', 'toggleRejectBtn', 'toggleApproveBtn', 'total_rows', 'mails', 'checkState', 'language', 'checkFARowcnt'));
        $this->render('index');
    }

    /**
     * Purpose to make search condition and return condition array
     * $this->request->query used for index page
     * SRH_DATA_LIST session used for download pdf
     * @param $event_id, $layer_code
     *
     **/
    public function __searchCondition($event_id, $layer_code)
    {
        # Data search condition
        $statusChk = [];
        if (isset($this->request->query) && !empty($this->request->query)) {
            $sec_key_name = $this->request->query('sec_key_name');
            $intsall_location = $this->request->query('intsall_location');
            $physical_check = $this->request->query('physical_check');
            $label_check = $this->request->query('label_check');
            $label_number = $this->request->query('label_number');
            $picture_check = $this->request->query('picture_check');
            
            $hdStatus = $this->request->query('hdStatus');
            
            # Add search query to session to print pdf
            $this->Session->write('SRH_DATA_LIST', $this->request->query);
        } else {
            # if search data exists when download pdf, only show that search data
            $srh_data_list = $this->Session->read('SRH_DATA_LIST');
            $sec_key_name = $srh_data_list['sec_key_name'];
            $intsall_location = $srh_data_list['intsall_location'];
            $physical_check = $srh_data_list['physical_check'];
            $label_check = $srh_data_list['label_check'];
            $label_number = $srh_data_list['label_number'];
            $picture_check = $srh_data_list['picture_check'];
            $hdStatus = $srh_data_list['hdStatus'];
        }

        $intsall_location = str_replace(array('\\', '_', '%'), array('\\\\', '\_', '\%'), $intsall_location);
        $sec_key_name = str_replace(array('\\', '_', '%'), array('\\\\', '\_', '\%'), $sec_key_name);
        $label_number = str_replace(array('\\', '_', '%'), array('\\\\', '\_', '\%'), $label_number);
        
        if ($sec_key_name != '') {
            $conditions['Asset.2nd_key_name LIKE '] = "%".$sec_key_name."%";
        }
        if ($intsall_location != '') {
            $conditions['Asset.place_name LIKE '] = "%".$intsall_location."%";
        }
        $condi = [];
        $condi ['Asset.asset_event_id'] = $event_id;
        $condi ['Asset.flag'] = 1;
        if(!empty($layer_code)) $condi['Asset.layer_code'] = $layer_code;
        $not_save_state = $this->Asset->find('first', array(
            'conditions' => $condi
        ));

        if ($physical_check != '') {
            if(!empty($not_save_state)) {
                $conditions['AND']['OR']['0']['Asset.physical_chk'] = $physical_check;
                $conditions['AND']['OR']['1']['Asset.physical_chk'] = $physical_check;
            }else $conditions['Asset.physical_chk'] = $physical_check;
        }
        if ($label_check != '') {
            if(!empty($not_save_state)) { 
                $conditions['AND']['OR']['0']['Asset.label_chk'] = $label_check;
                $conditions['AND']['OR']['1']['Asset.label_chk'] = $label_check;
            }else  $conditions['Asset.label_chk'] = $label_check;
        }
        if(!empty($conditions['AND']['OR']['1'])) {
            $conditions['AND']['OR']['1']['Asset.asset_status'] = 1;
            $conditions['AND']['OR']['1']['OR']['0']['Asset.flag'] = 1;
            $conditions['AND']['OR']['1']['OR']['0']['Asset.status IN '] = array(1,3,4,5);
            $conditions['AND']['OR']['1']['OR']['1']['Asset.flag >'] = 1;
        }
        if ($label_number != '') {
            $conditions['Asset.label_no LIKE '] = "%". $label_number ."%";
        }
        if (!empty($layer_code)) {
            $conditions['Asset.layer_code'] = $layer_code;
        }

        if (!empty($hdStatus)) {
            $statusChk = explode(",", $hdStatus);
            if (!empty($statusChk)) {
                $conditions['OR'] =array();
                
                if (in_array("1", $statusChk)) {
                    $conditions['OR'] []=array('Asset.status'=>1);
                }
                if (in_array("2", $statusChk)) {
                    $conditions['OR'][] =array('Asset.status'=>2);
                }
                if (in_array("3", $statusChk)) {
                    $conditions['OR'] []=array('Asset.status'=>3);
                }
                if (in_array("4", $statusChk)) {
                    $conditions['OR'] []=array('Asset.status'=>4);
                }
                if (in_array("5", $statusChk)) {
                    $conditions['OR'] []=array('Asset.status'=>5);
                }
            }
        }
            
        $conditions['Asset.asset_event_id'] = $event_id;
        $conditions['CAST(Asset.flag AS UNSIGNED) > '] = 0;
        $conditions['Layer.flag'] = 1;

        return $conditions;
    }

    /**
     * Used for two purpose, select data for paginate and download pdf
     * IF $limit='All', it means no limit and select data to download pdf
     * ELSE select data for pagination of index page
     * @param $condition_array, $limit
     *
     **/
    public function __preparePaginate($conditions, $limit)
    {
        $picture_check1 = $this->request->query('picture_check');
        $picture_check2 = $this->request->data('hddImage');
        $picture_check3 = $this->request->data('hddImage1');
        #filter ba list as basic selection
      
        if (!empty($picture_check1)) {
            $picture_check=$picture_check1;
        } elseif (!empty($picture_check2)) {
            $picture_check=$picture_check2;
        } else {
            $picture_check=$picture_check3;
        }
        
        $joins = array(
            array(
                'table' => 'layers',
                'alias' => 'Layer',
                'type' => 'left',
                'conditions' => 'Asset.layer_code = Layer.layer_code AND  Layer.flag=1 AND date_format(Asset.created_date, "%Y-%m-%d") BETWEEN Layer.from_date AND Layer.to_date '
            ),
            array(
                'table' => 'asset_events',
                'alias' => 'event',
                'type' => 'left',
                'conditions' => 'Asset.asset_event_id = event.id AND event.flag=1'
            ),
            array(
                'table' => 'asset_busi_inc_comments',
                'alias' => 'cmt_not_ref',
                'type' => 'left',
                'conditions' => 'cmt_not_ref.asset_id = Asset.id AND cmt_not_ref.flag=1'
            ),
            // array(
            //     'table' => 'assets',
            //     'alias' => 'assets',
            //     'type' => 'left',
            //     'conditions' => 'assets.asset_event_id = event.reference_event_id  AND assets.asset_no=Asset.asset_no AND Asset.flag=1 AND Asset.layer_code = tbl_m_asset.layer_code'
            // )
        );
        $pic_joins = array(
            'table' => 'pictures',
            'alias' => 'pic',
            'conditions' => 'Asset.asset_no = pic.picture_name AND pic.flag=1'
        );
        $fields = array(
            'event.id',
            'event.event_name',
            'event.reference_event_id',
            'Asset.id',
            'Asset.asset_event_id',
            'Asset.layer_code',
            'Asset.layer_name',
            'Asset.2nd_key_code',
            'Asset.2nd_key_name',
            'Asset.asset_no',
            'Asset.asset_name',
            'Asset.quantity',
            'Asset.acq_date',
            'Asset.place_code',
            'Asset.place_name',
            'Asset.label_no',
            'Asset.amount',
            'Asset.status',
            'Asset.flag',
            'Asset.created_date',
            'Asset.physical_chk as not_ref_physical_chk',
            'Asset.label_chk as not_ref_label_chk',
            'cmt_not_ref.asset_id as cmt_not_ref_asset_id',
            'cmt_not_ref.comment as cmt_not_ref_comment',
            'cmt_not_ref.remark as cmt_not_ref_remark',
            'Asset.asset_status',
           // 'tbl_m_asset.asset_event_id',
            'Asset.physical_chk as ref_physical_chk',
            'Asset.label_chk as ref_label_chk',
        );
        
        if($picture_check == '2') {#not exist
            $conditions[] = 'Asset.asset_no NOT IN (select picture_name from pictures)';
        }else {#exist
            if ($picture_check == '') {
                $pic_joins['type'] = 'left';
            }
            array_push($joins, $pic_joins);
            array_push($fields, 'pic.file_path');
        }

        $tmp = array(
            'conditions' => $conditions,
            'joins' => $joins,
            'fields' => $fields,
            'order' => array(
                'Asset.id ASC'
            )
        );
       
       
        if ($limit != 'All') {
            # for index pagination
            $tmp['limit'] = $limit;
            $this->paginate = $tmp;
          
            $rsl = $this->Paginator->paginate('Asset');
         //   pr($this->Paginator->paginate('Asset'));die;
        } else {
            # PDF Download
            $rsl = $this->Asset->find('all', $tmp);
        }
       
        return $rsl;
    }

    /**
     * To decide reference data or no refrence data after paginate and
     * get image from google cloud storage for each asset_no
     * @param $rsl
     **/
    public function __getDataList($rsl)
    {
        $getRefData = '';
        $getLatestData = '';
       
        $count = count($rsl);
        # check selected event has reference_event_id is exists or not
        $choose_event_id = $this->Session->read('EVENT_ID');
        $isReferenced = $this->AssetEvent->find('first', array(
            'conditions' => array(
                'id' => $choose_event_id,
                'flag' => 1,
                'NOT' => array('reference_event_id'=>0)
            ),
            'fields' => array(
                'reference_event_id'
            )
        ));
        if (!empty($isReferenced)) {
            # reference_event_id is exists
            # find same remark, comment for reference_event_id of same asset_no
            $getRefData = $this->Asset->getReferenceData($rsl);
        } else {
            # if reference_event_id is not exists,
            # get remark, comment for same asset_no of selected event_id with latest created_date

            # get max asset_id except selected event id
            $getMaxIdExceptChooseEvent = $this->Asset->getLatestIDOfSameAssetNo($rsl, $choose_event_id);

            if (!empty($getMaxIdExceptChooseEvent)) {
                $tmp = array_column($getMaxIdExceptChooseEvent, '0');
                $latest_id_arr = array_column($tmp, 'latest_asset_id');
                #find comment and remark
                $getLatestData = $this->Asset->find('all', array(
                    'conditions' => array(
                        'Asset.id' => $latest_id_arr,
                        'NOT' => array(
                            'Asset.flag' => 0,
                            'ref_cmt.flag' => 0
                        )
                    ),
                    'joins' => array(
                        array(
                            'table' => 'asset_busi_inc_comments',
                            'alias' => 'ref_cmt',
                            'type' => 'LEFT',
                            'conditions' => 'Asset.id = ref_cmt.asset_id'
                        )
                    ),
                    'fields' => array(
                        'Asset.id',
                        'Asset.asset_no',
                        'ref_cmt.comment',
                        'ref_cmt.remark'
                    )
                ));
            }
        }
        
        for ($i=0; $i<$count; $i++) {
            $rsl_asset_id = $rsl[$i]['Asset']['asset_id'];
            $rsl_layer_code = $rsl[$i]['Asset']['layer_code'];
            $rsl_ref_event_id = $rsl[$i]['event']['reference_event_id'];
            $rsl_asset_no = $rsl[$i]['Asset']['asset_no'];

            # create url to access image from google cloud storage
            if (array_key_exists('pic', $rsl[$i])) {
                $file_path = $rsl[$i]['pic']['file_path'];
                if (!empty($file_path)) {
                    $rsl[$i]['pic']['real_path'] = $this->__get_object_v4_signed_url($file_path);
                } else {
                    $rsl[$i]['pic']['real_path'] = '';
                }
            } else {
                $rsl[$i]['pic']['real_path'] = '';
            }            
            # if reference data exists, add this data to $rsl array
            if (!empty($getRefData)) {
                $cnt = count($getRefData);
                for ($j=0; $j<$cnt; $j++) {
                    $ref_event_id = $getRefData[$j]['s']['event_id'];
                    $ref_asset_no = $getRefData[$j]['s']['asset_no'];
                    $ref_layer_code=$getRefData[$j]['s']['layer_code'];
                    $ref_physical = $getRefData[$j]['s']['physical_chk'];
                    $ref_label = $getRefData[$j]['s']['label_chk'];
                    $ref_remark = $getRefData[$j]['ref_cmt']['remark'];
                    $ref_comment = $getRefData[$j]['ref_cmt']['comment'];
                    if ($rsl_asset_no == $ref_asset_no && $rsl_ref_event_id == $ref_event_id && $ref_layer_code==$rsl_layer_code) {
                        $rsl[$i]['ref_event_data'] = array(
                            'physical_chk_ref' => $ref_physical,
                            'label_chk_ref' => $ref_label,
                            'cmt_ref_comment' => $ref_comment,
                            'cmt_ref_remark' => $ref_remark
                        );
                    }
                }
            }
            # if latest data exists
            if (!empty($getLatestData)) {
                $cnt = count($getLatestData);
                for ($j=0; $j<$cnt; $j++) {
                    $lst_asset_id = $getLatestData[$j]['Asset']['asset_id'];
                    $lst_asset_no = $getLatestData[$j]['Asset']['asset_no'];
                    $lst_remark = $getLatestData[$j]['ref_cmt']['remark'];
                    $lst_comment = $getLatestData[$j]['ref_cmt']['comment'];
                    if ($rsl_asset_no == $lst_asset_no) {
                        $rsl[$i]['ref_event_data'] = array(
                            'physical_chk_ref' => 2,//set default uncheck
                            'label_chk_ref' => 2,//set default uncheck
                            'cmt_ref_comment' => $lst_comment,
                            'cmt_ref_remark' => $lst_remark
                        );
                    }
                }
            }
            if (!array_key_exists('ref_event_data', $rsl[$i])) {
                $rsl[$i]['ref_event_data'] = array(
                    'physical_chk_ref' => '',
                    'label_chk_ref' => '',
                    'cmt_ref_comment' => '',
                    'cmt_ref_remark' => ''
                );
            }
        }
        return $rsl;
    }

    /**
     * get page number to redirect
     * @param total number of rows
     **/
    protected function __getPageNoToRedirect($total_record)
    {
        $session_page = $this->Session->read('Page.pageCount');
        if ($total_record > 0) {
            $limit = Paging::PICTURE_PAGING; // becz 20 rows per page
            $recalculate_page = ceil(($total_record)/$limit);
        } elseif ($total_record != 0) {
            $recalculate_page = $total_record;
        } else {
            $recalculate_page = 1;
        }
        if ($session_page < $recalculate_page) {
            $page = $session_page;
        } else {
            $page = $recalculate_page;
        }
        return $page;
    }

    /**
     * Regenerate URL to redirect after delete, Approve, Reject, Approve Cancel
     * @param total number of rows
     **/
    protected function __regenerateURL()
    {
        $limit = 20;
        $event_id = $this->Session->read('EVENT_ID');
        $layer_code = $this->Session->read('SESSION_LAYER_CODE');
        $searchConditions = $this->__searchCondition($event_id, $layer_code);
        $rsl = $this->__preparePaginate($searchConditions, $limit);
        $total_rows = $this->params['paging']['Asset']['count'];
        $page = $this->__getPageNoToRedirect($total_rows);

        if ($this->Session->check('SRH_DATA_LIST')) {
            $srh_data_list = $this->Session->read('SRH_DATA_LIST');
            $sec_key_name = $srh_data_list['sec_key_name'];
            $intsall_location = $srh_data_list['intsall_location'];
            $physical_check = $srh_data_list['physical_check'];
            $label_check = $srh_data_list['label_check'];
            $label_number = $srh_data_list['label_number'];
            $picture_check = $srh_data_list['picture_check'];
            $hdStatus = $srh_data_list['hdStatus'];
            $queryString = 'sec_key_name='.$sec_key_name.'&intsall_location='.$intsall_location.'&physical_check='.$physical_check.'&label_check='.$label_check.'&label_number='.$label_number.'&picture_check='.$picture_check.'&hdStatus='.$hdStatus;
            $url = $this->webroot."Assets/index/page:{$page}/sort:Asset.id/direction:ASC?".$queryString;
        } else {
            $url = $this->webroot."Assets/index/page:{$page}/sort:Asset.id/direction:ASC";
        }
        return $url;
    }

    /**
     *	Find all data is approved or not
     *	@param $event_id, $layer_code
     *	@return `true`, if all data is approved,
     *	@return	`false`, if data is not yet approved
     **/
    public function __checkAllDataApproved($event_id, $layer_code=null)
    {
        $conditions = [];
        $conditions['asset_event_id'] = $event_id;
        $conditions['Asset.flag'] = 4;
        $conditions['layer_code'] = $layer_code;

        $rsl = $this->Asset->find('all', array(
            'conditions' => $conditions
        ));
        if (!empty($rsl)) {
            # if data(flag=4) is found, it means all data is approved
            return true;
        } else {
            # if data(flag=4) is not found, it means all data is not yet approved
            return false;
        }
    }

    /**
     *	Find requested data (flag=2)
     *	@param $event_id, $layer_code
     *	@return `true`, if requested data found
     *	@return	`false`, if not found
     **/
    public function __findRequestedData($event_id, $layer_code)
    {
        $find = $this->Asset->find('all', array(
            'conditions' => array(
                'asset_event_id' => $event_id,
                'layer_code' => $layer_code,
                'Asset.flag' => 3
            )
        ));
        if (!empty($find)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Generate a v4 signed URL for downloading an object.
    *
    * @param string $bucketName the name of your Google Cloud bucket.
    * @param string $objectName the name of your Google Cloud object.
    *
    * @return void
    */
    public function __get_object_v4_signed_url($objectName)
    {
        $cloud = parent::connect_to_google_cloud_storage();
        $storage = $cloud[0];
        $bucketName = $cloud[1];
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->object($objectName);
        if ($object->exists()) {
            $url = $object->signedUrl(
            # This URL is valid for 100 minutes
            new \DateTime('100 min'),
                [
                    'version' => 'v4'
                ]
            );
        } else {
            $url = '';
        }
        return $url;
    }

    /**
     *	Delete data in tbl_m_asset, tbl_busi_incharge_comment table
     *  Only `Admin` can delete
     *  Data can delete before Approve(flag=4)
     *	@param $asset_id
     **/
    public function deleteAsset()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');
        
        if ($this->request->is('post')) {
            $id = $this->request->data['asset_id'];
          
            $del_asset_no = $this->request->data['hddAsset_number'];
            $del_layer_code = $this->request->data['hddBA'];
            $event_id = $this->Session->read('EVENT_ID');
            
            
            $admin_id = $this->Session->read('LOGIN_ID');
      
            if ($user_level != 1) {
                $msg = parent::getErrorMsg("SE016", [__("削除")]);
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                $url = $this->__regenerateURL();
                return $url;
            }

            # check data is already approve or deleted
            $checkFlag = $this->Asset->find('first', array(
                'conditions' => array(
                    'Asset.id' => $id,
                    'OR' => array(
                        array('Asset.flag'=>4),
                        array('Asset.flag'=>0)
                    )
                ),
                'fields' => array(
                    'Asset.flag'
                )
            ));
         
            if (!empty($checkFlag)) {
                $flag = $checkFlag['Asset']['flag'];
               
                if ($flag == 4) {
                    # already approved
                    $msg = parent::getErrorMsg('SE007');
                    $msg .= ' '.parent::getErrorMsg('SE033');
                } elseif ($flag == 0) {
                    # already deleted
                    $msg = parent::getErrorMsg('SE050');
                }
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                $url = $this->__regenerateURL();
                return $url;
            }
            
            # prepare to update flag in tbl_m_asset
            $assetDB = $this->Asset->getDataSource();
            $assetUpdate['Asset.flag'] = $assetDB->value(0, 'string');//field to update
            $assetUpdate['updated_by'] = $assetDB->value($admin_id, 'string');
            $date = date("Y-m-d H:i:s");
            $assetUpdate['updated_date'] = $assetDB->value($date, 'string');
            $assetCondition['Asset.id'] = $id;//condition to update
           
            # find comment is exists or not for asset_id
            $findCmt = $this->AssetBusiIncComment->find('all', array('conditions'=>array('Asset.id'=>$id,'Asset.flag'=>1)));

            # find remove asset data exists or not, for asset_number
            $findRemove = $this->AssetRemove->find('all', array('conditions'=>array('asset_no'=>$del_asset_no,'layer_code'=>$del_layer_code,'asset_event_id'=>$event_id)));

            # find sold asset data exists or not, for asset_number
            $findSold = $this->AssetSold->find('all', array('conditions'=>array('asset_no'=>$del_asset_no,'layer_code'=>$del_layer_code,'asset_event_id'=>$event_id)));
            
            # delete if remove asset data exists or not, for asset_number
           
            if (!empty($findRemove)) {
                try {
                    $result = $this->AssetRemove->DeletedRemoveData($del_asset_no, $del_layer_code, $event_id);
                    
                    if ($result == false) {
                        throw new Exception("Asset NO:{$del_asset_no} is not deleted", 1);
                    }
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE007");
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    $url = $this->__regenerateURL();
                    return $url;
                }
            }
            # delete if sold asset data exists or not, for asset_number
            if (!empty($findSold)) {
                try {
                    $result = $this->AssetSold->DeletedSoldData($del_asset_no, $del_layer_code, $event_id);
                    
                    if ($result == false) {
                        throw new Exception("Asset NO:{$del_asset_no} is not deleted", 1);
                    }
                } catch (Exception $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE007");
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    $url = $this->__regenerateURL();
                    return $url;
                }
            }

            $cmtDB = $this->AssetBusiIncComment->getDataSource();

            if (!empty($findCmt)) {
                $cmtUpdate['flag'] = $cmtDB->value(0, 'string');//field to update
                $cmtUpdate['updated_by'] = $cmtDB->value($admin_id, 'string');
                $cmtUpdate['updated_date'] = $cmtDB->value(date("Y-m-d H:i:s"), 'string');
                $cmtCondition = array(
                    'asset_id' => $id,
                    'NOT' => array('flag'=>0)
                );
            }
            
            $assetDB->begin();
            $cmtDB->begin();
            try {
                # update tbl_m_assets
                $this->Asset->updateAll(
                    $assetUpdate,
                    $assetCondition
                );
                $assetRow = $this->Asset->getAffectedRows();
                $cmtRow = 1;//initialze value to commit when comment is not found
                if (!empty($findCmt)) {
                    # update asset_busi_inc_comments
                    $this->AssetBusiIncComment->updateAll(
                        $cmtUpdate,
                        $cmtCondition
                    );
                    $cmtRow = $this->AssetBusiIncComment->getAffectedRows();
                }
                
                if ($assetRow > 0 && $cmtRow > 0) {
                    $assetDB->commit();
                    $cmtDB->commit();
                    $msg = parent::getSuccessMsg("SS003");
                    $this->Flash->set($msg, array('key'=>'assetsOK'));
                    $url = $this->__regenerateURL();
                    return $url;
                } else {
                    throw new Exception("Fail to delete asset_id:$id in assets and asset_busi_inc_comments table", 1);
                }
            } catch (Exception $e) {
                $assetDB->rollback();
                $cmtDB->rollback();
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE007");
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                $url = $this->__regenerateURL();
                return $url;
            }
        } else {
            $url = $this->__regenerateURL();
            return $url;
        }
    }

    /**
     *	save data into tbl_m_asset and tbl_busi_asset_comment
     *	change flag to 2 in tbl_m_asset
     *	save remark, comment into tbl_busi_asset_comment
     *	allow user level -> 7,6
     **/
    public function saveAsset()
    {
        $Common = new CommonController();
        $Permission = new PermissionsController();
        
        #only allow ajax request
        if ($this->request->is('post')) {
            $updateCmt = [];
            $updCmtAssetId = [];
            $saveCmt = [];
            $arr = json_decode($this->request->data['dataArr'], true);
            $get_id_arr = array_column($arr, 'asset_id');#get asset_id
            $event_id = $this->Session->read('EVENT_ID');
            $event_name = $this->Session->read('EVENT_NAME');
            $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $admin_id = $this->Session->read('LOGIN_ID');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            #request from mail url
            $sec_key_name = $this->request->query('sec_key_name');
            $intsall_location = $this->request->query('intsall_location');
            $physical_check = $this->request->query('physical_check');
            $label_check = $this->request->query('label_check');
            $label_number = $this->request->query('label_number');
            $picture_check = $this->request->query('picture_check');
            $hdStatus = $this->request->query('hdStatus');
            $status = true;
            $limit = 20;
            $searchConditions = $this->__searchCondition($event_id, $layer_code);
            $rsl = $this->__preparePaginate($searchConditions, $limit);
           
            $total_record = $this->params['paging']['Asset']['count'];
            $page = $this->__getPageNoToRedirect($total_record);
           // pr($get_id_arr);die;
            # before saved, check data is already requested or not
            $isRequested = $this->Asset->find('all', array(
                'conditions' => array(
                    'Asset.id' => $get_id_arr,
                    'CAST(Asset.flag AS UNSIGNED) >=' => 3
                )
            ));
           
            if (!empty($isRequested)) {
                $tmp_req = array_column($isRequested, 'Asset');
                $requested_id_arr = array_column($tmp_req, 'asset_id');
                $cnt_requested = count($requested_id_arr);
                $cnt_arr = count($arr);
                if ($cnt_requested == $cnt_arr) {
                    # if all data is requested, then don't save
                    $msg = parent::getErrorMsg("SE019");
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
                } else {
                    # if some of data is already requested, then remove that data from $arr
                    for ($i=0; $i<$cnt_arr; $i++) {
                        $arr_asset_id = $arr[$i]['asset_id'];
                        if (in_array($arr_asset_id, $requested_id_arr)) {
                            unset($arr[$i]);
                        }
                    }
                    $arr = array_values($arr);//re-order array index
                }
            }

            # check data is already deleted or not
            $findDeletedData = $this->Asset->find('all', array(
                'conditions' => array(
                    'Asset.id' => $get_id_arr,
                    'Asset.flag' => 0
                ),
                'fields' => array('id')
            )); 
        
            if (!empty($findDeletedData)) {
                $tmp_asset = array_column($findDeletedData, 'Asset');
                $del_asset_arr = array_column($tmp_asset, 'id');
                $cnt_del_asset_arr = count($del_asset_arr);
                $cnt_arr = count($arr);
                
                if ($cnt_del_asset_arr == $cnt_arr) {
                    # if all data is deleted, no need to save
                    $msg = parent::getErrorMsg("SE003");
                    $msg .= " ".parent::getErrorMsg("SE046");
                    $this->Flash->set($msg, array('key' => 'assetsFail'));
                    $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
                } else {
                    # if some of data is already deleted, then remove that data from $arr
                    for ($i=0; $i<$cnt_arr; $i++) {
                        $arr_asset_id = $arr[$i]['asset_id'];
                        if (in_array($arr_asset_id, $del_asset_arr)) {
                            unset($arr[$i]);
                        }
                    }
                    $arr = array_values($arr);//re-order array index
                }
            }
        
            if (!empty($arr)) {
                $asset_id_arr = array_column($arr, 'asset_id');//get asset_id
                # find comment
                $cmtExist = $this->AssetBusiIncComment->find('all', array(
                    'conditions' => array(
                        'AssetBusiIncComment.asset_id' => $asset_id_arr,
                        'AssetBusiIncComment.flag' => 1
                    ),
                    'fields' => array('asset_id')
                ));
          
                if (!empty($cmtExist)) {
                    # get asset_id to update remark, comment
                    $tmp = array_column($cmtExist, 'AssetBusiIncComment');
                    $upd_asset_id = array_column($tmp, 'asset_id');
                
                } else {
                    $upd_asset_id = [];
                }
                
                $count = count($arr);
                $c_u_date = date('Y-m-d H:i:s');
                for ($i=0; $i<$count; $i++) {
                    $asset_id = trim($arr[$i]['asset_id']);
                    $remark = trim($arr[$i]['remark']);
                    $comment = trim($arr[$i]['comment']);
                 
                    $tmp = array(
                        'asset_id' => $asset_id,
                        'remark' => $remark,
                        'comment' => $comment,
                        'flag' => 1,
                        'created_by' => $admin_id,
                        'updated_by' => $admin_id,
                        'created_date' => $c_u_date,
                        'updated_date' => $c_u_date
                    );
                    if (in_array($asset_id, $upd_asset_id)) {
                        # comment to update
                        $updateCmt[] = $tmp;
                        $updCmtAssetId[] = $asset_id;
                      
                    } else {
                        # comment to insert
                        $saveCmt[] = $tmp;
                    }
                }

                $assetDB = $this->Asset->getDataSource();
                $cmtDB = $this->AssetBusiIncComment->getDataSource();
                $assetDB->begin();
                $cmtDB->begin();

                try {
                  
                    $isAssetUpdate = $this->Asset->updateAsset($arr, $asset_id_arr, $admin_id);
                 
                    if (!empty($updateCmt)) {
                        $isCmtUpdate = $this->AssetBusiIncComment->updateComment($updateCmt, $updCmtAssetId, $admin_id);
                       
                        if (!$isCmtUpdate) {
                            throw new Exception("Fail to update comment in asset_busi_inc_comments table", 1);
                        }
                    } else {
                        # if no need to update, set true
                        $isCmtUpdate = true;
                    }
                  
                    if (!empty($saveCmt)) { 
                        $this->AssetBusiIncComment->create();
                     
                        $this->AssetBusiIncComment->saveAll($saveCmt);
                    }
                   
                    $matchCnt = $this->checkFARowcnt($layer_code, $event_id);
                  
                    $physORLblCheck = $this->AssetBusiIncComment->checkPhyLblState($layer_code, $event_id);
              
                    $phyStatus = 0;
                    $lblStatus = 0;
                    
                    foreach ($physORLblCheck as $value) {
                        $cmtForCheck = $value['asset_busi_inc_comments']['comment'];
                        $cmtFlgForCheck = $value['tblTemp']['physical_chk'];
                        
                        $reasonForCheck = $value['asset_busi_inc_comments']['remark'];
                        $reasonFlgForCheck = $value['tblTemp']['label_chk'];

                        if ($cmtForCheck == "" && $cmtFlgForCheck == 2) {
                            $phyStatus = 1;
                        }
                        if ($reasonForCheck == "" && $reasonFlgForCheck == 2) {
                            $lblStatus = 1;
                        }
                    }
                   
                    if($matchCnt) {
                        if ($phyStatus == 0 && $lblStatus == 0) {
                            #change flag = 3
                            $updateForRequest=$this->Asset->updateFADataToRequest($layer_code, $event_id);
                            
                            if($_POST['mailSend']) {
                                $url = '/Assets?param='.$event_id.'&ba='.$layer_code.'&sec_key_name='.$sec_key_name.'&intsall_location='.$intsall_location.'&physical_check='.$physical_check.'&label_check='.$label_check.'&label_number='.$label_number.'&picture_check='.$picture_check.'&hdStatus='.$hdStatus;
                            
                                $period = '';
                                $to_email = $_POST['toEmail'];
                                $cc_email = $_POST['ccEmail'];
                                $bcc_email = $_POST['bccEmail'];
                                $toEmail = parent::formatMailInput($to_email);
                                $ccEmail = parent::formatMailInput($cc_email);
                                $bccEmail = parent::formatMailInput($bcc_email); 
                                #Mail contents
                                $mail_template = 'common';
                                $mail['subject'] = $_POST['mailSubj'];
                                $mail['template_body'] = $_POST['mailBody'];
                            
                                if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                                
                                    $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                    if ($sentMail["error"]) {
                                        $msg = $sentMail["errormsg"];
                                        $this->Flash->set($msg, array('key'=>'assetsFail'));
                                        $invalid_email = parent::getErrorMsg('SE042');
                                        $this->Flash->set($invalid_email, array('key'=>'assetsFail'));
                                    } else {
                                        $assetDB->commit();
                                        $cmtDB->commit();
                                        $msg = parent::getSuccessMsg("SS001", [__("正常")]);
                                        $this->Flash->set($msg, array('key'=>'assetsOK'));
                                        $msg = parent::getSuccessMsg("SS018");
                                        $this->Flash->set($msg, array('key'=>'assetsOK'));
                                    }
                                } else {
                                    $msg = parent::getErrorMsg("SE129");
                                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                                    CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Save` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                }
                            }else {
                                $assetDB->commit();
                                $cmtDB->commit();
                                $msg = parent::getSuccessMsg("SS001", [__("正常")]);
                                $this->Flash->set($msg, array('key'=>'assetsOK'));
                            }
                        } else {
                            $assetDB->commit();
                            $cmtDB->commit();
                            $msg = parent::getSuccessMsg("SS001", [__("正常")]);
                            $this->Flash->set($msg, array('key'=>'assetsOK'));
                        }
                    } else {
                        $assetDB->commit();
                        $cmtDB->commit();
                        $msg = parent::getSuccessMsg("SS001", [__("正常")]);
                        $this->Flash->set($msg, array('key'=>'assetsOK'));
                    }
                } catch (Exception $e) {
                    $assetDB->rollback();
                    $cmtDB->rollback();
                    $msg = parent::getErrorMsg("SE003");
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
            }
            # redirect to original page
            $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
        }
    }

    
    public function checkFARowcnt($layer_code, $event_id, $flg = '') {
        $flag = false;
        if(!empty($layer_code)) $condi['Asset.layer_code'] = $layer_code;
        $condi['Asset.asset_event_id'] = $event_id;
        $condi['Asset.flag !='] = '0';
        $condi[0] = 'date_format(Asset.created_date, "%Y-%m-%d") BETWEEN Layer.from_date AND Layer.to_date ';

        $totalcnt = $this->Asset->find('count', array(
            'conditions' => $condi,
            'joins' => array(array(
                'table' => 'layers',
                'alias' => 'Layer',
                'type' => 'left',
                'conditions' => 'Asset.layer_code = Layer.layer_code AND  Layer.flag=1 '
            ))
        ));
        $joins = '';
        if(!empty($flg)) {
            $condi['Asset.flag'] = $flg;
            array_push($condi,'(Asset.physical_chk = 1 OR cmt_not_ref.comment != "") AND (Asset.label_chk = 1 OR cmt_not_ref.remark != "")');
            $joins = array(
                'table' => 'asset_busi_inc_comments',
                'alias' => 'cmt_not_ref',
                'type' => 'left',
                'conditions' => 'cmt_not_ref.asset_id = Asset.id AND cmt_not_ref.flag=1 AND (Asset.physical_chk = 1 OR cmt_not_ref.comment != "") AND (Asset.label_chk = 1 OR cmt_not_ref.remark != "")'
            );
        }else {
            $condi['Asset.flag'] = array(2, 3);
        }
        
        $savecnt = $this->Asset->find('count', array(
            'conditions' => $condi,
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'Layer',
                    'type' => 'left',
                    'conditions' => 'Asset.layer_code = Layer.layer_code AND  Layer.flag=1 '
                    ),
                $joins
            )
        ));

       
        if($flg != '') {
            $flag = $savecnt;
        }
        elseif($totalcnt == $savecnt) $flag = true;

        return $flag;

    }
    /**
     *	Approve process
     *	Change flag 3 to flag 4 in tbl_m_asset
     *	Insert approve date for all approved(flag=4) asset id to
     *  tbl_busi_mgr_assets_approved
     **/
    public function approveAsset()
    {
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $approve_date = [];
            $event_id = $this->Session->read('EVENT_ID');
            $event_name = $this->Session->read('EVENT_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
            $admin_id = $this->Session->read('LOGIN_ID');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            // request for entering from mail url (Start / edited by khin hnin myo)
            $sec_key_name = $this->request->query('sec_key_name');
            $intsall_location = $this->request->query('intsall_location');
            $physical_check = $this->request->query('physical_check');
            $label_check = $this->request->query('label_check');
            $label_number = $this->request->query('label_number');
            $picture_check = $this->request->query('picture_check');
            $hdStatus = $this->request->query('hdStatus');
            $to_email = $_POST['toEmail'];
            $cc_email = $_POST['ccEmail'];   
            $bcc_email = $_POST['bccEmail'];           
            # recalculate page no to redirect
            $limit = 20;
            $searchConditions = $this->__searchCondition($event_id, $layer_code);
            $rsl = $this->__preparePaginate($searchConditions, $limit);
            $total_record = $this->params['paging']['Asset']['count'];
            $page = $this->__getPageNoToRedirect($total_record);
            if ($user_level == 1 || $user_level == 8 || $user_level == 5 || ($user_level == 2 && $layer_code!="")) {
               
                # check data is already approved or not
                $isApproved = $this->Asset->find('first', array(
                    'conditions' => array(
                        'asset_event_id' => $event_id,
                        'Asset.layer_code' => $layer_code,
                        'Asset.flag' => 4
                    )
                ));
                if (!empty($isApproved)) {
                    # if already approved, then redirect
                    $msg = parent::getErrorMsg('SE033');
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    # redirect to original page
                    $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
                }

                # check flag 2(not yet request) data is exists or not
                $isReady = $this->Asset->find('first', array(
                    'conditions' => array(
                        'asset_event_id' => $event_id,
                        'Asset.layer_code' => $layer_code,
                        'Asset.flag' => 2
                    )
                )); 
                if (!empty($isReady)) {
                    # if flag 2 data exists, then redirect
                    $msg = parent::getErrorMsg('SE040')." ";
                    $msg .=  parent::getErrorMsg('SE041');
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    # redirect to original page
                    $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
                }

                # get total row count of selected event_id, layer_code
                $getAllRows = $this->Asset->find('all', array(
                    'conditions' => array(
                        'asset_event_id' => $event_id,
                        'Asset.layer_code' => $layer_code,
                        'NOT' => array('Asset.flag' => 0)
                    )
                ));
                $countAllRows = count($getAllRows);
                
                # get requested row count of selected event_id, layer_code
                $reqRows = $this->Asset->find('all', array(
                    'conditions' => array(
                        'asset_event_id' => $event_id,
                        'Asset.layer_code' => $layer_code,
                        'Asset.flag' => 3
                    )
                ));
              
                $countReqRows = count($reqRows);
                if ($countReqRows > 0) {
                    #prepare data to save in tbl_busi_mgr_assets_approve table
                    for ($i=0; $i<$countReqRows; $i++) {
                        $asset_id = $reqRows[$i]['Asset']['id'];
                        $app_date = date('Y-m-d');
                        $upd_date = date('Y-m-d H:i:s');
                        $approve_date[] = array(
                            'asset_id' => $asset_id,
                            'approve_date' => $app_date,
                            'flag' => 1,
                            'created_by' => $admin_id,
                            'updated_by' => $admin_id,
                            'created_date' => $upd_date,
                            'updated_date' => $upd_date
                        );
                    }
                }
                
                # check total row and requested row count is same or not
                if ($countAllRows == $countReqRows && $countAllRows!=0 && $countReqRows!=0) {
                    # if same, can approve
                    $assetDB =  $this->Asset->getDataSource();
                    $approveDB =  $this->AssetBusiMgrApprove->getDataSource();
                    $assetDB->begin();
                    $approveDB->begin();
                    try {
                        # update flag 3 to 4 in tbl_m_asset
                        $change['Asset.flag'] = $assetDB->value(4, 'string');
                        $change['Asset.updated_by'] = $assetDB->value($admin_id, 'string');
                        $date = date('Y-m-d H:i:s');
                        $change['Asset.updated_date'] = $assetDB->value($date, 'string');
                        $condition['Asset.flag'] = 3;
                        $condition['Asset.asset_event_id'] = $event_id;
                        $condition['Asset.layer_code'] = $layer_code;
                        $this->Asset->updateAll(
                            $change,
                            $condition
                        ); 
                        $effectRows = $this->Asset->getAffectedRows();

                      //  pr($approve_date);die;
                        # save approve date in tbl_busi_mgr_asset
                        $this->AssetBusiMgrApprove->create();
                        $this->AssetBusiMgrApprove->saveAll($approve_date);
                        
                        if ($effectRows < 1) {
                            throw new Exception('Approve Fail! flag not change from 3 to 4 in tbl_m_assets table');
                        }
                    
                        if($_POST['mailSend']) {
                            #Mail contents
                            $mail_template = 'common';
                            $mail['subject']        = $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body']  = $_POST['mailBody'];
                            $url ='/Assets?param='.$event_id.'&ba='.$layer_code.'&sec_key_name='.$sec_key_name.'&intsall_location='.$intsall_location.'&physical_check='.$physical_check.'&label_check='.$label_check.'&label_number='.$label_number.'&picture_check='.$picture_check.'&hdStatus='.$hdStatus;
                            $imported_date = $this->importedDate($event_id);
                            $searchToEmail = parent::formatMailInput($to_email);
                            $searchCCEmail = parent::formatMailInput($cc_email);
                            $searchBccEmail = parent::formatMailInput($bcc_email);
                            
                            if (!empty($searchToEmail) || !empty($searchCCEmail) || !empty($searchBccEmail)) {
                                $period = '';
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $searchToEmail, $searchCCEmail, $searchBccEmail, $mail_template, $mail, $url);
                                if ($sentMail["error"]) {   
                                $msg = $sentMail["errormsg"];
                                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                                    $msg = parent::getErrorMsg('SE042');
                                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                                } else {
                                    $assetDB->commit();
                                    $approveDB->commit();
                                    $msg = parent::getSuccessMsg('SS005');
                                    $this->Flash->set($msg, array('key'=>'assetsOK'));
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'assetsOK'));
                                }
                            } else {
                                $msg = parent::getErrorMsg("SE129");
                                $this->Flash->set($msg, array('key'=>'assetsFail'));
                                CakeLog::write('debug', 'Manager email not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Save` button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            }
                        }else {
                            $assetDB->commit();
                            $approveDB->commit();
                            $msg = parent::getSuccessMsg('SS005');
                            $this->Flash->set($msg, array('key'=>'assetsOK'));
                        }
                    } catch (Exception $e) {
                        $assetDB->rollback();
                        $approveDB->rollback();
                        $msg = parent::getErrorMsg('SE011', [__("承認")]);
                       // $msg .= ' '.$invalid_email;
                        $this->Flash->set($msg, array('key'=>'assetsFail'));
                        CakeLog::write('debug', $e->getMessage(). ' in file '. __FILE__ .' on line '. __LINE__ .' withing the class '. get_class());
                    }
                } else {
                    # can't approve
                    $msg = parent::getErrorMsg('SE040')." ";
                    $msg .=  parent::getErrorMsg('SE046');
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                }
                # redirect to original page
                $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
            } else {
                # not allow to make 'approve'
                $msg = parent::getErrorMsg("SE016", [__("承認")]);
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                # redirect to original page
                $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
            }
        } else {
            # not allow method
            $this->redirect(array('controller'=>'Assets', 'action'=>'index'));
        }
    }

    /**
     *	Reject process
     *	Change flag 3 to flag 2 in tbl_m_asset
     **/
    public function rejectAsset()
    {
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $event_id = $this->Session->read('EVENT_ID');
            $event_name = $this->Session->read('EVENT_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
            $admin_id = $this->Session->read('LOGIN_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $sec_key_name = $this->request->query('sec_key_name');
            $intsall_location = $this->request->query('intsall_location');
            $physical_check = $this->request->query('physical_check');
            $label_check = $this->request->query('label_check');
            $label_number = $this->request->query('label_number');
            $picture_check = $this->request->query('picture_check');
            $hdStatus = $this->request->query('hdStatus');
            $to_email = $_POST['toEmail'];
            $cc_email = $_POST['ccEmail'];
            $bcc_email = $_POST['bccEmail'];
            
            # recalculate page no to redirect
            $limit = 20;
            $searchConditions = $this->__searchCondition($event_id, $layer_code);
            $rsl = $this->__preparePaginate($searchConditions, $limit);
            $total_record = $this->params['paging']['Asset']['count'];
            $page = $this->__getPageNoToRedirect($total_record);

            if ($user_level == 1 || $user_level == 5 || $user_level == 8 || ($user_level == 2 && $layer_code!="")) {
                # check data can reject or not
                $checked = $this->Asset->find(
                    'all',
                    array(
                    'conditions' => array(
                        'asset_event_id' => $event_id,
                        'Asset.layer_code' => $layer_code,
                        'Asset.flag' => 3
                    )
                )
                );
                
                if (empty($checked)) {
                    # if can't reject, then redirect
                    $msg = parent::getErrorMsg('SE017', [__("差し戻し")]);
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    # redirect to original page
                    $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
                }

                $assetDB =  $this->Asset->getDataSource();
                $assetDB->begin();
                try {
                    # update flag 3 to 2 in tbl_m_asset
                    $change['Asset.flag'] = $assetDB->value(2, 'string');
                    $change['updated_by'] = $assetDB->value($admin_id, 'string');
                    $upd_date = date('Y-m-d H:i:s');
                    $change['updated_date'] = $assetDB->value($upd_date, 'string');
                    $condition['Asset.flag'] = 3;
                    $condition['asset_event_id'] = $event_id;
                    $condition['Asset.layer_code'] = $layer_code;
                    $this->Asset->updateAll(
                        $change,
                        $condition
                    );
                    $effectRows = $this->Asset->getAffectedRows();
                    
                    if ($effectRows < 1) {
                        throw new Exception('Reject Fail! flag not change from 3 to 2 in tbl_m_assets table');
                    }
                    //pr($_POST['mailSend']);die;
                    if($_POST['mailSend']) {
                        if ($effectRows > 0) {
                            $toEmail = parent::formatMailInput($to_email);
                            $ccEmail = parent::formatMailInput($cc_email);
                            $bccEmail = parent::formatMailInput($bcc_email);
                            
                            $period = "";

                            $mail_template 			= 'common';
                            #Mail contents
                            $mail['subject'] 		= $_POST['mailSubj'];
                            $mail['template_title'] = $_POST['mailTitle'];
                            $mail['template_body'] 	= $_POST['mailBody'];
                            
                            $url = '/Assets?param='.$event_id.'&ba='.$layer_code.'&sec_key_name='.$sec_key_name.'&intsall_location='.$intsall_location.'&physical_check='.$physical_check.'&label_check='.$label_check.'&label_number='.$label_number.'&picture_check='.$picture_check.'&hdStatus='.$hdStatus;
                            
                            if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                                $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                                if ($sentMail["error"]) {
                                    $msg = $sentMail["errormsg"];
                                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                                    $msg = parent::getErrorMsg('SE042');
                                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                                } else {
                                    $assetDB->commit();
                                    $msg = parent::getSuccessMsg('SS014');
                                    $this->Flash->set($msg, array('key'=>'assetsOK'));
                                    $msg = parent::getSuccessMsg("SS018");
                                    $this->Flash->set($msg, array('key'=>'assetsOK'));
                                }
                            } else {
                                CakeLog::write('debug', 'User level 7 and 6 emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                $msg = parent::getErrorMsg("SE058", [__("依頼")]);
                                $this->Flash->set($msg, array('key'=>'assetsFail'));
                            }
                        } else {
                            CakeLog::write('debug', 'User level(6,7) informations are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click `Reject` button for selected event:' .$event_name. ' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                        }
                    }else {
                        $assetDB->commit();
                        $msg = parent::getSuccessMsg('SS014');
                        $this->Flash->set($msg, array('key'=>'assetsOK'));
                    }
                } catch (Exception $e) {
                    $assetDB->rollback();
                    $msg = parent::getErrorMsg('SE011', [__("差し戻し")]);
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    CakeLog::write('debug', $e->getMessage(). ' in file '. __FILE__ .' on line '. __LINE__ .' withing the class '. get_class());
                }
                # redirect to original page
                $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
            } else {
                # not allow to make 'reject'
                $msg = parent::getErrorMsg("SE016", [__("差し戻し")]);
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                # redirect to original page
                $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
            }
        } else {
            # not allow method
            $this->redirect(array('controller'=>'Assets', 'action'=>'index'));
        }
    }

    

    public function approveCancelAsset()
    {
        $Common = new CommonController();
        if ($this->request->is('post')) {
            $sec_key_name = $this->request->query('sec_key_name');
            $intsall_location = $this->request->query('intsall_location');
            $physical_check = $this->request->query('physical_check');
            $label_check = $this->request->query('label_check');
            $label_number = $this->request->query('label_number');
            $picture_check = $this->request->query('picture_check');
            $hdStatus = $this->request->query('hdStatus');
            $approve_asset_id = [];
            $event_id = $this->Session->read('EVENT_ID');
            $event_name = $this->Session->read('EVENT_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
            $admin_id = $this->Session->read('LOGIN_ID');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $login_user_name = $this->Session->read('LOGIN_USER');
            $to_email = $_POST['toEmail'];
            $cc_email = $_POST['ccEmail'];
            $bcc_email = $_POST['bccEmail'];

            # recalculate page no to redirect
            $limit = 20;
            $searchConditions = $this->__searchCondition($event_id, $layer_code);
            $rsl = $this->__preparePaginate($searchConditions, $limit);
            $total_record = $this->params['paging']['Asset']['count'];
            $page = $this->__getPageNoToRedirect($total_record);

            # find approved data exists or not
            $approved = $this->Asset->find('all',array(
                'conditions' => array(
                    'asset_event_id' => $event_id,
                    'Asset.layer_code' => $layer_code,
                    'Asset.flag'=> 4
                ))
            );
         
            if (empty($approved)) {
                # if approved data not found, can't cancel
                $msg = parent::getErrorMsg('SE017', [__("承認キャンセル")]);
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                # redirect to original page
                $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
            } else {
                $tmp = array_column($approved, 'Asset');
                $approve_asset_id = array_column($tmp, 'id');
            }
            
            $assetDB =  $this->Asset->getDataSource();
            $approveDB =  $this->AssetBusiMgrApprove->getDataSource();
            $assetDB->begin();
            $approveDB->begin();
            try {
                # update flag 4 to 2 in tbl_m_asset
                $change['Asset.flag'] = $assetDB->value(2, 'string');
                $change['updated_by'] = $assetDB->value($admin_id, 'string');
                $upd_date = date('Y-m-d H:i:s');
                $change['updated_date'] = $assetDB->value($upd_date, 'string');
                $condition['Asset.flag'] = 4;
                $condition['asset_event_id'] = $event_id;
                $condition['layer_code'] = $layer_code;
                $this->Asset->updateAll(
                    $change,
                    $condition
                );
              
                $effectRows = $this->Asset->getAffectedRows();
              
                if ($effectRows < 1) {
                    throw new Exception('Approve Cancel Fail! flag not change from 4 to 2 in assets table');
                }
                
                # update flag 1 to 0 in tbl_busi_mgr_assets_approve
                $update['AssetBusiMgrApprove.flag'] = $approveDB->value(0, 'string');
                $update['updated_by'] = $approveDB->value($admin_id, 'string');
                $update['updated_date'] = $approveDB->value($upd_date, 'string');
              
                $this->AssetBusiMgrApprove->updateAll(
                    $update,
                    array('asset_id'=>$approve_asset_id)
                );
               
                $appEffRows = $this->AssetBusiMgrApprove->getAffectedRows();
            
                if ($appEffRows < 1) {
                    throw new Exception('Approve Cancel Fail! flag not change from 1 to 0 in assets table');
                }
           
                if ($effectRows > 0 && $appEffRows > 0) {
                    # send email to tantoesha(level 7) and Kacho (level 6)
                    if($_POST['mailSend']) {
                        $imported_date = $this->importedDate($event_id);
                        $toEmail = parent::formatMailInput($to_email);
                        $ccEmail = parent::formatMailInput($cc_email);
                        $bccEmail = parent::formatMailInput($bcc_email);
                        
                        $period = "";

                        $mail_template 			= 'common';
                        #Mail contents
                        $mail['subject'] 		= $_POST['mailSubj'];
                        $mail['template_title'] = $_POST['mailTitle'];
                        $mail['template_body'] 	= $_POST['mailBody'];
                        $url = '/Assets?param='.$event_id.'&ba='.$layer_code.'&sec_key_name='.$sec_key_name.'&intsall_location='.$intsall_location.'&physical_check='.$physical_check.'&label_check='.$label_check.'&label_number='.$label_number.'&picture_check='.$picture_check.'&hdStatus='.$hdStatus;
                        if (!empty($toEmail) || !empty($ccEmail) || !empty($bccEmail)) {
                            $sentMail = parent::sendEmailP3($period, $login_user_name, $toEmail, $ccEmail, $bccEmail, $mail_template, $mail, $url);
                            if ($sentMail["error"]) {
                                $msg = $sentMail["errormsg"];
                                $this->Flash->set($msg, array('key'=>'assetsFail'));
                                $msg = parent::getErrorMsg('SE042');
                                $this->Flash->set($msg, array('key'=>'assetsFail'));
                            } else {
                                $assetDB->commit();
                                $approveDB->commit();
                                $msg = parent::getSuccessMsg("SS006");
                                $this->Flash->set($msg, array('key'=>'assetsOK'));
                                $msg = parent::getSuccessMsg("SS018");
                                $this->Flash->set($msg, array('key'=>'assetsOK'));
                            }
                        } else {
                            CakeLog::write('debug', 'Incharge(level 4) and Sub-Manager(level 3) emails are not found for BA Code:' .$layer_code. ', when login user id:' .$admin_id. '('.$login_user_name.') click Approve button for selected period:'.$period .' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $msg = parent::getErrorMsg("SE058", [__("依頼")]);
                            $this->Flash->set($msg, array('key'=>'assetsFail'));
                        }
                    }else {
                        $assetDB->commit();
                        $approveDB->commit();
                        $msg = parent::getSuccessMsg("SS006");
                        $this->Flash->set($msg, array('key'=>'assetsOK'));
                    }
                    /* mail ending for approve cancle*/
                }
            } catch (Exception $e) {
                $assetDB->rollback();
                $approveDB->rollback();
                $msg = parent::getErrorMsg('SE011', [__("承認キャンセル")]);
                // $msg .= ' '.$invalid_email;
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                CakeLog::write('debug', $e->getMessage(). ' in file '. __FILE__ .' on line '. __LINE__ .' withing the class '. get_class());
            }
            # redirect to original page
            $this->redirect(array('controller'=>'Assets','action'=>'index', 'page'=>$page, '?'=>$this->Session->read('SRH_DATA_LIST')));
        } else {
            # not allow method
            $this->redirect(array('controller'=>'Assets', 'action'=>'index'));
        }
    }
    /**
     *	Download PDF
     *	All user level can download
     **/
    public function dataListPdf()
    {
        $this->layout = false;
        if ($this->request->is('post')) {
            $event_id = $this->Session->read('EVENT_ID');
            $event_name = $this->Session->read('EVENT_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            $searchConditions = $this->__searchCondition($event_id, $layer_code);
            #testing
            $pdfFrom = $this->request->data('pdfFrom');
            $pdfTo = $this->request->data('pdfTo');
            if (!empty($this->request->data('hddTotalRow'))) {
                $hddTotalRow=$this->request->data('hddTotalRow');
            } else {
                $hddTotalRow=$this->request->data('hddTotalRow1');
            }
            
            if ($hddTotalRow>=1 && $hddTotalRow<=20) {
                $pdfFromCalc = 0;
                $pdfToCalc   = $hddTotalRow -1 ;
            } elseif ($hddTotalRow>20) {
                # Choose  pdf Limit number.
                $pdfFromCalc = ($pdfFrom - 1) * 20;
                $pdfToCalc   = ($pdfTo * 20) -1;
            } else {
                $pdfFromCalc = 0;
                $pdfToCalc   = 0;
            }
            
            try {
                $limit = 'All';

                $rsl = $this->__preparePaginate($searchConditions, $limit);
                $count=count($rsl);

                #output PDF row
                for ($i=0 ; $i<$count; $i++) {
                    if ($i>=$pdfFromCalc && $i<=$pdfToCalc) {
                    } else {
                        unset($rsl[$i]);
                    }
                }
                $count=count($rsl);
                $rsl = array_values($rsl);//

                if ($count > 160) {
                    # If total rows over 160 rows, not allow to download pdf
                    $msg = parent::getErrorMsg('SE049');
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    # recalculate page to redirect
                    $page = $this->__getPageNoToRedirect($count);
                    $this->redirect(array('controller'=>'Assets','action'=>'index', 'page' => $page, '?' => $this->Session->read('SRH_DATA_LIST')));
                } elseif ($count < 1) {
                    $msg = parent::getErrorMsg('SE017', [__("PDFダウンロード")]);
                    $this->Flash->set($msg, array('key'=>'assetsFail'));
                    $this->redirect(array('controller'=>'Assets','action'=>'index', '?' => $this->Session->read('SRH_DATA_LIST')));
                } else {
                    $data = $this->__getDataList($rsl);
                }
            } catch (Exception $e) {
                $msg = parent::getErrorMsg('SE012');
                $this->Flash->set($msg, array('key'=>'assetsFail'));
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->redirect(array('controller'=>'Assets','action'=>'index'));
            }
            # set cookie when download complete to hide loading animation at index.ctp
            setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);

            $total_records = parent::getSuccessMsg('SS004', $count);
            
            $this->set(compact('data', 'count', 'total_records'));
        } else {
            # not allow method
            $this->redirect(array('controller'=>'Assets', 'action'=>'index'));
        }
    }

    /**
     *	Download Excel for unchecked data
     *	All user level can download
     **/
    public function uncheckDataExcelDownload()
    {
        // $this->autoRender = false;
        // $this->request->allowMethod('ajax');
        #only allow ajax request
        parent::checkAjaxRequest($this);
        if ($this->request->is('post')) {
            //$date = date('Y/m/d'); // edited by khin hnin myo
            $busi_area_name = 'All'; //if layer_code not choose, show as 'All'
            $event_id = $this->Session->read('EVENT_ID');
            $event_name = $this->Session->read('EVENT_NAME');
            $layer_code = $this->Session->read('SESSION_LAYER_CODE');
            $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
            $user_level = $this->Session->read('ADMIN_LEVEL_ID');
            
            // if chosen layer_code, show date that is approve date in excel download
            // if chosen all layer_code, no show date
            // edited by khin hnin myo (Start)
            $approve_date_one = "";
            $asset_id_one = "";
           
            if (!empty($layer_code)) {
                $get_ass_id = $this->Asset->find('all', array('fields'=>('id'),
                                                'conditions'=>array('NOT'=>array('Asset.flag'=>'0'),
                                                    array('Asset.layer_code'=>$layer_code,'asset_event_id'=>$event_id))));
                        
                foreach ($get_ass_id as $value) {
                    $asset_id_one = $value['Asset']['id'];
                }
                
                $get_app_date = $this->AssetBusiMgrApprove->find('all', array('fields'=>('approve_date'),'conditions'=>array('AssetBusiMgrApprove.flag'=>'1','asset_id'=>$asset_id_one)));
               
                
                foreach ($get_app_date as $value) {
                    $approve_date_one = $value['AssetBusiMgrApprove']['approve_date'];
                }
            }
            // end
            $searchConditions = $this->__searchCondition($event_id, $layer_code);
            try {
                # If uncheck data is not exists, recalculate paging to redirect
                //$limit = 20;
                //$rsl = $this->__preparePaginate($searchConditions, $limit);
                //$total_records = $this->params['paging']['Asset']['count'];

                # Find uncheck label data with comment
                if (!empty($layer_code)) {
                    $conditions['Asset.layer_code'] = $layer_code;
                }
                $conditions['Asset.asset_event_id'] = $event_id;
                //$conditions['Asset.label_chk'] = 2;
                $conditions['NOT'] = array(
                    'Asset.flag'=>0,
                );
                //$conditions['bac.flag'] = 1;
                $filter_data = $this->Asset->find('all', array(
                    'conditions' => $conditions,
                    'joins' => array(
                        array(
                            'table' => 'asset_busi_inc_comments',
                            'alias' => 'bac',
                            'type' => 'LEFT',
                            'conditions' => 'Asset.id=bac.asset_id'
                        )
                    ),
                    'fields' => array(
                        'Asset.asset_no',
                        'Asset.asset_name',
                        'Asset.layer_name',
                        'Asset.layer_code',
                        'Asset.flag',
                        'Asset.label_chk',
                        'Asset.id',
                        'bac.asset_id',
                        'bac.remark'
                    ),
                    'order' => 'Asset.id',
                ));
                $asset_no_arr = array_column(array_column($filter_data, 'Asset'), 'asset_no');
                if (!empty($layer_code)) {
                    $ref_data = $this->Asset->getReferenceEventData($event_id, $asset_no_arr, $layer_code);
                } else {
                    $ref_data = $this->Asset->getReferenceEventData($event_id, $asset_no_arr);
                }
                $count = count($filter_data);
                $data = [];
                for ($r = 0; $r < $count; $r++) {
                    if (!empty($ref_data[$filter_data[$r]['Asset']['asset_no']])) {
                        if ($filter_data[$r]['Asset']['flag'] == 1 && $filter_data[$r]['Asset']['layer_code'] == $ref_data[$filter_data[$r]['Asset']['asset_no']]['Asset']['layer_code']) {
                            $filter_data[$r]['bac']['remark'] = $ref_data[$filter_data[$r]['Asset']['asset_no']]['bac']['remark'];
                            if (!empty($filter_data[$r]['bac']['remark'])) {
                                $filter_data[$r]['Asset']['label_chk'] = $ref_data[$filter_data[$r]['Asset']['asset_no']]['Asset']['label_chk'];
                            }
                        }
                    }
                    if ($filter_data[$r]['Asset']['label_chk']==2) {
                        $data[] = $filter_data[$r];
                    }
                }
               
                $count = count($data);
                if (!empty($data)) {
                    # get business area name if ba chosen
                    if (!empty($layer_code)) {
                        $busi_area_name = $data[0]['Asset']['layer_name'];
                    }
                    
                    # write data into excel
                    $objPHPExcel = $this->PhpExcel->createWorksheet()->setDefaultFont('Cambria', 11);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $objPHPExcel->setActiveSheetIndex(0);
                
                    $objPHPExcel->getActiveSheet()->setTitle('資産ラベル貼付不可管理一覧表');
                
                    $titleStyle = array(
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 14
                        )
                    );
                    $headerStyle = array(
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 11
                        )
                    );
                    $border = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $aligncenter = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    );
                    $centerBorder = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $alignleft = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_JUSTIFY
                        )
                    );
                    $leftJustifyBorder = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $rightJustify = array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                    );
                    $bold = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('A:U')->getAlignment()->setWrapText(true);
                    
                    //set column width
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(13.86);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16.11);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(16.11);

                    //set row height for header part
                    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);//new added for date (edited by khin hnin myo)
                    $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(7.5);
                    $objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(20);
                    $objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(8);
                    
                    //set date
                    $objPHPExcel->getActiveSheet()->setCellValue('H1', __("日付 :"));
                    $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($aligncenter);
                    $objPHPExcel->getActiveSheet()->setCellValue('I1', $approve_date_one);
                    $objPHPExcel->getActiveSheet()->mergeCells('I1:J1');
                    $objPHPExcel->getActiveSheet()->getStyle('I1:J1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    //department name
                    $objPHPExcel->getActiveSheet()->setCellValue('B2', __("部署名"));
                    $objPHPExcel->getActiveSheet()->mergeCells('B2:B3');
                    $objPHPExcel->getActiveSheet()->getStyle('B2:B3')->applyFromArray($aligncenter);
                    $objPHPExcel->getActiveSheet()->getStyle('B2:B3')->applyFromArray($border);
                    $objPHPExcel->getActiveSheet()->getStyle('B2:B3')->applyFromArray($bold);
                    $objPHPExcel->getActiveSheet()->setCellValue('C2', $busi_area_name);
                    $objPHPExcel->getActiveSheet()->mergeCells('C2:E3');
                    $objPHPExcel->getActiveSheet()->getStyle('C2:E3')->applyFromArray($centerBorder);
                    $depNumRows = $this->getRowcount($busi_area_name, 18.76);
                    $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight($depNumRows * 15 + 2.25);//set line height

                    $objPHPExcel->getActiveSheet()->setCellValue('D5', __("資産ラベル貼付不可管理一覧表"));
                    $objPHPExcel->getActiveSheet()->mergeCells('D5:H5');
                    $objPHPExcel->getActiveSheet()->getStyle('D5:H5')->applyFromArray($aligncenter);
                    $objPHPExcel->getActiveSheet()->getStyle('D5:H5')->applyFromArray($titleStyle);
                    $objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(35);

                    //set row height between title and table
                    $objPHPExcel->getActiveSheet()->getRowDimension(6)->setRowHeight(8);

                    //table header
                    $objPHPExcel->getActiveSheet()->getStyle('B7:F7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('daeef3');//header color change
                    $objPHPExcel->getActiveSheet()->setCellValue('B7', __("資産番号"));
                    $objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($centerBorder);
                    $objPHPExcel->getActiveSheet()->setCellValue('C7', __("資産名称"));
                    $objPHPExcel->getActiveSheet()->mergeCells('C7:E7');
                    $objPHPExcel->getActiveSheet()->getStyle('C7:E7')->applyFromArray($centerBorder);
                    $objPHPExcel->getActiveSheet()->setCellValue('F7', __("資産ラベル貼付不可理由"));
                    $objPHPExcel->getActiveSheet()->mergeCells('F7:J7');
                    $objPHPExcel->getActiveSheet()->getStyle('F7:J7')->applyFromArray($centerBorder);
                    //set header style
                    $objPHPExcel->getActiveSheet()->getStyle('B7:J7')->applyFromArray($headerStyle);
                    //set header row height
                    $objPHPExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(33);

                    //table body
                    $line = 8; //start to write dynamic data
                    for ($i=0; $i<$count; $i++) {
                        $no = $i+1;
                        $asset_no = $data[$i]['Asset']['asset_no'];
                        $asset_name = $data[$i]['Asset']['asset_name'];
                        $remark = $data[$i]['bac']['remark'];

                        //get total rows for each string
                        $assetRows = $this->getRowcount($asset_name, 28.14);
                        $remarkRows = $this->getRowcount($remark, 59.14);
                        if ($remarkRows > $assetRows) {
                            $numrows = $remarkRows;
                        } else {
                            $numrows = $assetRows;
                        }

                        //write line no
                        $objPHPExcel->getActiveSheet()->setCellValue('A'.$line, $no);
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$line)->applyFromArray($rightJustify);
                        //write asset no
                        $objPHPExcel->getActiveSheet()->setCellValue('B'.$line, " ".$asset_no);
                        $objPHPExcel->getActiveSheet()->getStyle('B'.$line)->applyFromArray($leftJustifyBorder);
                        
                        //write asset name
                        $objPHPExcel->getActiveSheet()->setCellValue('C'.$line, $asset_name);
                        $objPHPExcel->getActiveSheet()->mergeCells('C'.$line.':'.'E'.$line);
                        $objPHPExcel->getActiveSheet()->getStyle('C'.$line.':'.'E'.$line)->applyFromArray($leftJustifyBorder);
                        $objPHPExcel->getActiveSheet()->getStyle('C'.$line.':'.'E'.$line)->getAlignment()->setWrapText(true);

                        //write remark
                        $objPHPExcel->getActiveSheet()->setCellValue('F'.$line, $remark);
                        $objPHPExcel->getActiveSheet()->mergeCells('F'.$line.':'.'J'.$line);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$line.':'.'J'.$line)->applyFromArray($leftJustifyBorder);
                        $objPHPExcel->getActiveSheet()->getStyle('F'.$line.':'.'J'.$line)->getAlignment()->setWrapText(true);

                        //set line height
                        $objPHPExcel->getActiveSheet()->getRowDimension($line)->setRowHeight($numrows * 15 + 2.25);
                        
                        $line++;
                    }
                    
                    $this->PhpExcel->getWriter('Excel2007');
                    ob_start();
                    $this->PhpExcel->save("php://output");
                    $xlsData = ob_get_contents();
                    ob_end_clean();
                    $response =  array(
                            'status' => true,
                            'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData),
                            'msg' => ''
                        );
                    die(json_encode($response));
                } else {
                    $msg = parent::getErrorMsg('SE017', [__("貼付不可一覧表ダウンロード")]);
                    $response =  array(
                        'status' => false,
                        'file' => '',
                        'msg' => $msg
                    );
                    die(json_encode($response));
                }
            } catch (Exception $e) {
                $msg = parent::getErrorMsg('SE012');
                CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $response =  array(
                    'status' => false,
                    'file' => '',
                    'msg' => $msg
                );
                die(json_encode($response));
            }
        } else {
            # not allow method
            $msg = parent::getErrorMsg('SE012');
            $response =  array(
                'status' => false,
                'file' => '',
                'msg' => $msg
            );
            die(json_encode($response));
        }
    }


    /**
     *	Calculate row counts for text string
     *  rc = ((each line of string)/(total column width of excel merge cells))+1
     *  @return rowcount as $rc
     **/
    protected function getRowcount($text, $width)
    {
        $rc = 0;
        $line = explode("\n", $text);
        foreach ($line as $source) {
            $rc += intval((strlen($source) / $width)+1);
        }
        return $rc;
    }
    /*Begin edit for status BCMM Sandi */
    /**
     *	@author Kaung Zaw Thant
     **/
    public function dataList()
    {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout=false;
        $event_name=$this->Session->read('EVENT_NAME');   
        $asset_no=$this->request->data('asset_no');
        $dbasset=$this->Asset->popUpData($event_name, $asset_no);
        //$this->log(print_r($dbasset,true),LOG_DEBUG);die;
        $dbasset1=$this->Asset->popUpDataRef($event_name, $asset_no);
        $response = array();
    
        for ($i=0;$i<sizeof($dbasset);$i++) {
            $response[$i] = array(
                'asset_no'=> $dbasset[$i]['assets']['asset_no'],
                'asset_name'=> $dbasset[$i]['assets']['asset_name'],
                'layer_code'=> $dbasset[$i]['assets']['layer_code'],
                'layer_name'=> $dbasset[$i]['assets']['layer_name'],
                /* add in quality*/
                'quantity'=> $dbasset[$i]['assets']['quantity'],
                'diff_qty'=> $dbasset[$i]['assets']['diff_qty'],
                'event_name'=> $dbasset[$i]['e']['event_name'],
                'assetstatus'=> $dbasset[$i]['assets']['asset_status']
            );
        }
        if (!empty($dbasset1)) {
            $response1 = array();
            for ($i=0;$i<sizeof($dbasset1);$i++) {
                $response1[$i] = array(
                    'asset_no'=> $dbasset1[$i]['assets']['asset_no'],
                    'asset_name'=> $dbasset1[$i]['assets']['asset_name'],
                    'layer_code'=> $dbasset1[$i]['assets']['layer_code'],
                    'layer_name'=> $dbasset1[$i]['assets']['layer_name'],
                    /* add in quality*/
                    'quantity'=> $dbasset1[$i]['assets']['quantity'],
                    'diff_qty'=> $dbasset1[$i]['assets']['diff_qty'],
                    'event_name'=> $dbasset1[$i]['e']['event_name'],
                    'assetstatus'=> $dbasset1[$i]['assets']['asset_status']
                );
            }
        } else {
            $response1 = array();
        }
        $data = array(
            'content' => $response,
            'content1'=> $response1
        );
        
        return json_encode($data);
    }
    /* End edit for status BCMM Sandi */

    public function getEventName($event_id)
    {
        $getEventName = $this->AssetEvent->find('first', array(
            'fields' => array(
                'AssetEvent.event_name'
            ),
            'conditions' => array(
                'AssetEvent.flag' => 1,
                'AssetEvent.event_id' => $event_id
            )
        ));
        return $getEventName['AssetEvent']['event_name'];
    }

    #data pass from view ajax to controller to get mail data
    public function getMailLists() {
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $Common = New CommonController();
        $event_id = $this->Session->read('EVENT_ID');
        $layer_name = $this->Session->read('BASIC_SELECTION_BA_NAME');
        $layer_code = $_POST['layer_code'];
        $page = $_POST['page'];
        $function = $_POST['function'];
        $language = $_POST['language'];
      
        $imported_date = $this->importedDate($event_id);
       
        $phase = Setting::PHASE_SELECTION['AssetSelections'];
        $setting_layer = Setting::LAYER_SETTING[$phase];
        $mails = $Common->getMailList($layer_code, $page, $function, $language, $layer_name, $imported_date, $setting_layer);
       
        return json_encode($mails);
    }
    #check state that are read or save or app or rej or cancel
    public function checkState($user_level, $event_id,  $layer_code = null) {
        $Permission = New PermissionsController();
        $permission = $this->Session->read('PERMISSION');
       
        $current_controller = $this->request->params['controller'];
        $permits = $Permission->checkPermission($user_level, $current_controller);
      
        $saveflg = array(1, 2);
        $rejflg = 3;
        $appflg = 3;
        $cancelflg = 4;
        $setting_layer = Setting::LAYER_SETTING[3];
        $ret = [];
    
        $ret['canRead'] = $ret['canSave'] = $ret['canRej'] = $ret['canApp'] = $ret['canCancel'] = false;

        
        foreach($permits as $canCheck) { 
            $canCheck = str_replace(" ", "", $canCheck);
         
            if(strpos($canCheck, "index") && $permission[$canCheck] == 0) $ret['canRead'] = true;
          
            if((strpos($canCheck, "save")) && $this->checkAllFlag($event_id, $layer_code, $saveflg) && ($permission[$canCheck] == $setting_layer || $permission[$canCheck] == 0) && !empty($layer_code) ) $ret['canSave'] = true;

            if(strpos($canCheck, "approve") && $this->checkAllFlag($event_id, $layer_code, $appflg) && ($permission[$canCheck] == $setting_layer || $permission[$canCheck] == 0) && !empty($layer_code)) $ret['canApp'] = true;

            if(strpos($canCheck, "reject") && $this->checkAllFlag($event_id, $layer_code, $rejflg) && ($permission[$canCheck] == $setting_layer || $permission[$canCheck] == 0) && !empty($layer_code)) $ret['canRej'] = true;
            
            if(strpos($canCheck, "cancel") && $this->checkAllFlag($event_id, $layer_code, $cancelflg) && ($permission[$canCheck] == $setting_layer || $permission[$canCheck] == 0) && !empty($layer_code)) $ret['canCancel'] = true;

        }
      
        return $ret;
    }
    #check flag that determined show or not button
    public function checkAllFlag($event_id, $layer_code = null, $flag) {
        
        $conditions = [];
        $conditions['asset_event_id'] = $event_id;
        $conditions['Asset.flag'] = $flag;
        $conditions['Asset.layer_code'] = $layer_code;
        #all BA condition for level 3 and 4
        if(count($flag) > 1 && empty($layer_code)) unset($conditions['layer_code']);

        $check = $this->Asset->find('all', array(
            'conditions' => $conditions
        ));

        if(!empty($check)) return true;
        else return false;
    }
    #check imported date
    public function importedDate($event_id){
        $conditions = [];
        $conditions['AssetEvent.flag'] = 1;
        $conditions['OR']['reference_event_id'] = $event_id;
        $conditions['OR']['id'] = $event_id;
        
        $date = $this->AssetEvent->find('first', array(
            'fields' => array('AssetEvent.created_date'),
            'conditions' => $conditions,
            'order' => 'id DESC'
        ))['AssetEvent']['created_date'];
       
        return $date;
    }
    #check have or not permission
    public function AllowBA($layer_code) {
        $inc_id = $this->Session->read('LOGIN_ID');
        $event_id = $this->Session->read('EVENT_ID');
        $check_limit = $this->Session->read('CHECKLIMIT');
        /*$date = date("Y-m-d", strtotime($this->importedDate($event_id)));
        $conditions = [];
        $conditions['flag'] = 1;
        $conditions['id'] = $inc_id;
        $phase = 3;
        $type_order = Setting::LAYER_SETTING[$phase];
        $conditions = array();
        $conditions['Layer.flag'] = 1;
        $conditions['Layer.from_date <='] = $date;
        $conditions['Layer.to_date >='] = $date;
        $conditions['Layer.type_order >='] = $type_order;
        $conditions['Layer.layer_code'] = $layer_code;
        */
        $phase = 3;
        $type_order = Setting::LAYER_SETTING[$phase];
        $return = [];
        $return['registerBA'] = false;
        $Common = New CommonController();
        $last_layer = $Common->getLastLayer();
        
        $fields = 'Layer.*';
        $arr_col = 'Layer';
        $joins = '';
        if($type_order < $last_layer && $check_limit != 0) {
            $fields = 'layers.*';
            $arr_col = 'layers';
            $joins = array(
                'table' => 'layers',
                'alias' => 'layers',
                'conditions' => array(
                    "Layer.parent_id LIKE CONCAT('%\"L', ".$type_order.", '\":\"',layers.layer_code,'\"%') AND layers.flag = 1"
                )
            );
        }
        $data = $this->User->find('all', array(
            'fields' => $fields,
            'conditions' => array(
                'User.id' => $inc_id,
            ),
            'joins' => array(
                array(
                    'table' => 'layers',
                    'alias' => 'Layer',
                    'type' => 'left',
                    'conditions' => array(
                        '(User.layer_code LIKE CONCAT("%", Layer.layer_code, "%") OR 1=1) AND Layer.flag=1'
                    )
                ),
                $joins
            )
        ));
        $datas = array_column($data, $arr_col);
        foreach ($datas as $value) {
            $return['layer_code'][] = $value['code'];
        }
        
        if(in_array($layer_code, $return['layer_code']) && !empty($layer_code) && !empty($return['layer_code'])) {
            $return['registerBA'] = true;
            $return['layer_code'] = $layer_code;
        }elseif(empty($layer_code)){
            $return['registerBA'] = true;
        }
        
        return $return;
    }
    
}
