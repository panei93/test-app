<?php

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
App::import('Controller', 'TermSelection');
App::import('Controller', 'BrmPLSummary');
App::import('Controller', 'BrmSummary');
App::import('Controller', 'ForecastBudgetDifference');
App::import('Controller', 'TradingPlan');
App::import('Controller', 'ManpowerPlan');
App::import('Controller', 'PositionMp');
App::import('Controller', 'BudgetPlan');
App::import('Controller', 'BrmBudgetResultDifference');
App::import('Controller', 'Calculation');
App::import('Controller', 'BrmMonthlyReport');
App::import('Controller', 'BrmBudgetSummary');


use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\Exception\GoogleException;

class BrmBackupFileController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('BrmTerm', 'Layer',  'BrmTermDeadline', 'BrmExpectedBudgetDiffAccount', 'BrmBudgetApprove', 'User', 'BrmBackupFile', 'BrmCloudFile', 'Permission', 'Menu', 'LayerType');
    public $components = array('Session', 'Flash', 'Paginator', 'PhpExcel.PhpExcel');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        $login_id = $this->Session->read('LOGIN_ID');
        $pagename = $this->request->params['controller'];

        $Common = new CommonController();
        $permissions = $Common->getPermissionsByRole($login_id, $role_id, $pagename);
        $this->Session->write('PERMISSIONS', $permissions);

        #verify read permission
        if ($permissions['index']['limit'] < 0) {
            $errorMsg = parent::getErrorMsg('SE065');
            $this->Flash->set($errorMsg, array('key' => 'TermError'));
            $this->redirect(array('controller' => 'BrmTermSelection', 'action' => 'index'));
        }
    }

    public function index()
    {
        #read layout to test user level
        // $this->layout = "$this->Session->read('LAYOUT')";
        $this->layout = "phase_3_menu";
        $role_id = $this->Session->read('ADMIN_LEVEL_ID');
        if ($this->Session->check('BK_SEARCH_TERMID')) {
            $search_termid = $this->Session->read('BK_SEARCH_TERMID');
            $this->Session->delete('BK_SEARCH_TERMID');
        }
       if ($this->Session->check('BK_SEARCH_HEADID')) {
            $search_headid = $this->Session->read('BK_SEARCH_HEADID');
            $this->Session->delete('BK_SEARCH_HEADID');
        }

        if ($this->Session->check('BK_SEARCH_TYPE')) {
            $search_type    = $this->Session->read('BK_SEARCH_TYPE');
            $this->Session->delete('BK_SEARCH_TYPE');
        }

        if ($this->Session->check('BK_SEARCH_SMONTH')) {
            $search_smonth = $this->Session->read('BK_SEARCH_SMONTH');
            $this->Session->delete('BK_SEARCH_SMONTH');
        }

        if ($this->Session->check('BK_SEARCH_EMONTH')) {
            $search_emonth = $this->Session->read('BK_SEARCH_EMONTH');
            $this->Session->delete('BK_SEARCH_EMONTH');
        }
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $budget_layer_code = $this->Session->read('SESSION_LAYER_CODE');
        }
        $pagearr = ['BrmPLSummary', 'BrmSummary', 'BrmBudgetingSystem', 'BrmBudgetResultDifference', 'BrmMonthlyReport'];
        $pagename = $this->Permission->find('list', array(
            'fields' => array('Menus.page_name', 'Permission.limit'),
            'joins' => array(
                array(
                    'table' => 'menus',
                    'alias' => 'Menus',
                    'type'  =>  'left',
                    'conditions' => array(
                        'Menus.id = Permission.menu_id',
                        'Menus.flag' => 1,
                    )
                )
            ),
            'conditions' => array(
                'Menus.page_name' => $pagearr,
                'Permission.role_id' => $role_id,
            )
        ));
        #term raw data
        $term = $this->BrmTerm->find('all', array(
            'conditions' => array('flag' => '1')
        ));

        #term list to display
        $term_list = [];

        #task list to display
        $task_list = [];

        #folder list to display
        $folder_list = [];

        #db backup file list
        $db_file_list = [];

        #push required term data
        foreach ($term as $value) {
            $term_list[$value['BrmTerm']['id']] = $value['BrmTerm'];
            $term_list[$value['BrmTerm']['id']]['display_term_name'] = $this->getDisplayTermName($value['BrmTerm']['id']);
            $term_list[$value['BrmTerm']['id']]['min_date'] = $value['BrmTerm']['budget_year'] . '-' . $value['BrmTerm']['start_month'];
            $term_list[$value['BrmTerm']['id']]['max_date'] = $this->getMaxDate($value['BrmTerm']['budget_year'], $value['BrmTerm']['budget_end_year'], $value['BrmTerm']['start_month'], $value['BrmTerm']['end_month']);
        }

        #type list
        $type_list = BackupFormInfo::TYPE_LIST_JPN;

        #head department list
        $head_dept_list = $this->getHeadDeptList();

        #cloud folder name
        $archive_folder = CloudStorageInfo::FOLDER_NAME;

        #read user id
        $login_id = $this->Session->read('LOGIN_ID');

        #read admin level id
        $user_level = $this->Session->read('ADMIN_LEVEL_ID');



        #task list data
        $backup_task_list = $this->BrmBackupFile->find('all', array(
            'fields' => array('BrmBackupFile.id', 'BrmBackupFile.term_id', 'BrmBackupFile.term_name', 'BrmBackupFile.hlayer_name', 'BrmBackupFile.hlayer_code', 'BrmBackupFile.file_type', 'BrmBackupFile.start_month', 'BrmBackupFile.end_month', 'User.user_name', 'BrmBackupFile.created_date'),
            'joins' => array(
                array(
                    'table' => 'users',
                    'alias' => 'User',
                    'type'  =>  'left',
                    'conditions' => array(
                        'User.id = BrmBackupFile.updated_by',
                        'User.flag' => 1,
                    )
                )
            ),
            'conditions' => array(
                'status' => 0, 'BrmBackupFile.flag' => 1
            ),
            'order' => array('term_id', 'BrmBackupFile.hlayer_code')
        ));

        #push required task list data
        foreach ($backup_task_list as $each_task) {
            $type_no = $each_task['BrmBackupFile']['file_type'];
            $task = array(
                'backup_id' => $each_task['BrmBackupFile']['id'],
                'term_id' => $each_task['BrmBackupFile']['term_id'],
                'term_name' => $each_task['BrmBackupFile']['term_name'],
                'hlayer_code' => $each_task['BrmBackupFile']['hlayer_code'],
                'head_dept_name' => $each_task['BrmBackupFile']['hlayer_name'],
                'type_no' => $type_no,
                'file_type' => $type_list[$type_no],
                'created_user' => $each_task['User']['user_name'],
                'created_date' => $each_task['BrmBackupFile']['created_date'],
                'start_month' => $each_task['BrmBackupFile']['start_month'],
                'end_month' => $each_task['BrmBackupFile']['end_month'],
            );
            $task_list[] = $task;
        }

        try {
            $folder_list = [];
            #permission for backup master
            $permission = $this->Session->read('PERMISSIONS');
            $PL_Read_Limit = $pagename['BrmPLSummary'];
            $SM_Read_Limit = $pagename['BrmSummary'];
            $PF_Read_Limit = $pagename['BrmBudgetingSystem'];
            $BRD_Read_Limit = $pagename['BrmBudgetResultDifference'];
            $MR_Read_Limit = $pagename["BrmMonthlyReport"];
            // $condition['status'] = 1;
            // $condition['flag'] = 1;
            if (!empty($search_termid)) {
                $condition['term_id'] = $search_termid;
            }

            if (!empty($search_headid)) {
                $condition['hlayer_code'] = $search_headid;
            }

            if (!empty($search_type)) {
                $condition['file_type'] = $search_type;
            }


            #backup files
            $db_file_list = $this->BrmBackupFile->find('list', array(
                'fields' => array('hlayer_code', 'hlayer_name', 'term_name'),
                'conditions' => $condition,
                'order' => 'term_id ASC'
            ));

            #collect backup files
            foreach ($db_file_list as $term_name => $each_term) {
                $pl_hqs = $this->getHqByPermission($PL_Read_Limit, $each_term, $search_headid);
                $sm_hqs = $this->getHqByPermission($SM_Read_Limit, $each_term, $search_headid);
                $pf_hqs = $this->getHqByPermission($PF_Read_Limit, $each_term, $search_headid);
                $brd_hqs = $this->getHqByPermission($BRD_Read_Limit, $each_term, $search_headid);
                $mr_hqs = $this->getHqByPermission($MR_Read_Limit, $each_term, $search_headid);

                #conditions to get urls from tbl_cloud_files
                $prefix = $archive_folder . '/' . $term_name . '/';

                #get file urls
                $file_urls = $this->BrmCloudFile->find('all', array(
                    'fields' => array('url', 'file_size', 'updated_date'),
                    'conditions' => array(
                        'flag' => 1,
                        'url LIKE' => $prefix . '%'
                    ),
                ));

                #loop through each files under ArchiveFolder/TermName directory
                foreach ($file_urls as $file_url) {
                    #get file size
                    $file_size = $file_url['BrmCloudFile']['file_size'];
                    #get full url
                    $blob = $file_url['BrmCloudFile']['url'];
                    $namearr = explode('/', $blob);
                    $file_head = $namearr[2];
                    $file_subf = $namearr[3];
                    #get file uploaded date
                    $time = new DateTime($file_url['BrmCloudFile']['updated_date']);
                    $time->setTimezone(new DateTimeZone(Setting::TIMEZONE));
                    $file_created_date = $time->format('m/d/Y g:i A');
                    #PLSummary Files
                    if ((in_array($file_head, $pl_hqs)) && (strpos($file_subf, 'PLサマリ') !== false) && (empty($search_type) || $search_type == '01')) {
                        $folder_list[$archive_folder][$term_name][$file_head][] = $file_subf . '@&@' . $file_created_date . '@&@' . $file_size;
                    }

                    #Summary Files
                    if ((in_array($file_head, $sm_hqs)) && (strpos($file_subf, '総括表') !== false) && (empty($search_type) || $search_type == '01')) {
                        $folder_list[$archive_folder][$term_name][$file_head][] = $file_subf . '@&@' . $file_created_date . '@&@' . $file_size;
                    }

                    #Plan Forms
                    if ((in_array($file_head, $pf_hqs)) && (strpos($file_subf, '計画フォーム') !== false) && (empty($search_type) || $search_type == '02')) {
                        $pf_file = $namearr[4];
                        if (!empty($pf_file)) {
                            $folder_list[$archive_folder][$term_name][$file_head][$file_subf][] = $pf_file . '@&@' . $file_created_date . '@&@' . $file_size;
                        }
                    }

                    #BRD Files
                    if ((in_array($file_head, $brd_hqs)) && (strpos($namearr[4], '予実比較') !== false) && (empty($search_type) || $search_type == '03')) {
                        $brd_file = $namearr[4];
                        if (!empty($brd_file)) {
                            if ((empty($search_smonth) && empty($search_emonth)) || ($file_subf >= $search_smonth && $file_subf <= $search_emonth)) {
                                $folder_list[$archive_folder][$term_name][$file_head][$file_subf][] = $brd_file . '@&@' . $file_created_date . '@&@' . $file_size;
                            }
                        }
                    }

                    #MR Files
                    if ((in_array($file_head, $mr_hqs)) && (strpos($namearr[4], '月次業績報告') !== false) && (empty($search_type) || $search_type == '04')) {
                        $mr_file = $namearr[4];
                        if (!empty($mr_file)) {
                            if ((empty($search_smonth) && empty($search_emonth)) || ($file_subf >= $search_smonth && $file_subf <= $search_emonth)) {
                                $folder_list[$archive_folder][$term_name][$file_head][$file_subf][] = $mr_file . '@&@' . $file_created_date . '@&@' . $file_size;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            #log for fail connection
            CakeLog::write('error', 'Db connection error....' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

            #compact required data if some errors happened
            $this->set(compact('term_list', 'folder_list', 'task_list', 'type_list', 'user_level', 'archive_folder', 'search_termid', 'search_headid', 'search_type', 'search_smonth', 'search_emonth'));
            return $this->render('index');
        }

        #compact required data
        $this->set(compact('term_list', 'folder_list', 'task_list', 'type_list', 'head_dept_list', 'user_level', 'archive_folder', 'search_termid', 'search_headid', 'search_type', 'search_smonth', 'search_emonth'));
        return $this->render('index');
    }

    #queue according to term, type, headquarters to backup
    public function backup()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('POST')) {
            $term_id = $this->request->data('term_id');
            $term_name = $this->getDisplayTermName($term_id);
            $type = $this->request->data('type');
            $start_month = $this->request->data('from_month');
            $start_month = (empty($start_month) ? '' : (new DateTime($start_month))->format('Y-m-d'));
            $end_month = $this->request->data('to_month');
            $end_month = (empty($end_month) ? '' : (new DateTime($end_month))->format('Y-m-d'));
            $hq = array_unique($this->request->data('headquarters'));
            sort($hq);

            $backup_files = [];
            $head_dept_arr = (strpos($hq[0], ',')) ? explode(',', $hq[0]) : $hq;

            if (empty($head_dept_arr)) {
                #head department list
                $head_dept_list = $this->getHeadDeptList();
                $head_dept_arr = array_keys($head_dept_list);
            }

            foreach ($head_dept_arr as $key => $head_dept) {

                $head_dept_name = $this->getHeadDepartName($head_dept)[$head_dept];
                $queued_row = $this->BrmBackupFile->find('first', array(
                    'fields' => array('id', 'status'),
                    'conditions' => array(
                        'term_id' => $term_id,
                        'file_type' => $type,
                        'hlayer_code' => $head_dept,
                        'start_month' => $start_month,
                        'end_month' => $end_month
                    ),
                    'order' => array('id')
                ));

                #read user id
                $login_id = $this->Session->read('LOGIN_ID');

                if (empty($queued_row)) {
                    $backup_files[] = array(
                        'term_id' => $term_id,
                        'term_name' => $term_name,
                        'file_type' => $type,
                        'hlayer_code' => $head_dept,
                        'hlayer_name' => $head_dept_name,
                        'start_month' => $start_month,
                        'end_month' => $end_month,
                        'status' => '0',
                        'flag' => '1',
                        'created_by' => $login_id,
                        'updated_by' => $login_id,
                    );
                } else {
                    date_default_timezone_set(Setting::TIMEZONE);
                    $backup_files[] = array(
                        'id' => $queued_row['BrmBackupFile']['id'],
                        'term_id' => $term_id,
                        'term_name' => $term_name,
                        'file_type' => $type,
                        'hlayer_code' => $head_dept,
                        'hlayer_name' => $head_dept_name,
                        'start_month' => $start_month,
                        'end_month' => $end_month,
                        'status' => '0',
                        'flag' => 1,
                        'updated_by' => $login_id,
                        'updated_date' => date('Y-m-d H:i:s', time()),
                    );
                }
            }

            $this->BrmBackupFile->saveAll($backup_files);

            $successMsg  = parent::getSuccessMsg("SS001");
            $this->Flash->set($successMsg, array("key" => "successBackup"));

            $this->loadModel('Queue.QueuedTask');
            $this->QueuedTask->createJob('BrmBackupFile', 2 * MINUTE);

            $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
        }
    }

    #search data according to term, type, headquarters from backup
    public function searchArchiveData()
    {
        $this->layout = 'mastermanagement';
        if ($this->request->is('POST')) {
            $term_id = $this->request->data('term_id');
            $type = $this->request->data('type');
            $start_month = $this->request->data('from_month');
            $start_month = (empty($start_month) ? '' : (new DateTime($start_month))->format('Y-m'));
            $end_month = $this->request->data('to_month');
            $end_month = (empty($end_month) ? '' : (new DateTime($end_month))->format('Y-m'));
            $hq = array_unique($this->request->data('headquarters'));
            sort($hq);
            $head_dept_arr = (strpos($hq[0], ',')) ? explode(',', $hq[0]) : $hq;
            if (!empty($term_id)) {
                $this->Session->write('BK_SEARCH_TERMID', $term_id);
            }
            if (!empty($head_dept_arr)) {
                $this->Session->write('BK_SEARCH_HEADID', $head_dept_arr);
            }
            if (!empty($type)) {
                $this->Session->write('BK_SEARCH_TYPE', $type);
            }
            if (!empty($start_month)) {
                $this->Session->write('BK_SEARCH_SMONTH', $start_month);
            }
            if (!empty($end_month)) {
                $this->Session->write('BK_SEARCH_EMONTH', $end_month);
            }

            $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
        } else {
            $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
        }
    }

    #get headquarters by permission
    public function getHqByPermission($limit, $head_dept_list = null, $search_hq = null)
    {
        #read user id
        $login_id = $this->Session->read('LOGIN_ID');
        switch ($limit) {
            case 0:
                #all headquarters
                $headquarters = ($head_dept_list == null) ? $this->getHeadDeptList() : $head_dept_list;
                if (empty($search_hq)) {
                    $headquarters[] = '全社';
                }

                break;
            case 1:
                #related headquarters
                $ba_code = $this->getBACodeByLoginId($login_id);
                $headquarters = $this->getBusinessAreaByBACode($ba_code);
                break;
        }
        return $headquarters;
    }

    #get max date for disable
    public function getMaxDate($budget_year, $budget_end_year, $start_month, $end_month)
    {
        if ($start_month == 1) {
            return $budget_end_year . '-' . $end_month;
        } else {
            $budget_end_year = $budget_end_year + 1;
            return $budget_end_year . '-' . $end_month;
        }
    }

    #file objects loop to display
    public function objectsCommonLoop($objects, $bucket, $folder_list, $type)
    {
        $timeZone = new DateTimeZone(Setting::TIMEZONE);
        foreach ($objects as $object) {
            $extension = pathinfo($object, PATHINFO_EXTENSION);
            if ($extension == 'xlsx') {
                $object_arr = explode('/', $object);
                $file_object = $bucket->object($object);
                if ($file_object->exists()) {
                    $time = new DateTime($file_object->info()['updated']);
                    $time->setTimezone($timeZone);
                    $object_arr[count($object_arr) - 1] = (empty($object_arr[count($object_arr) - 1])) ? '' : $object_arr[count($object_arr) - 1] . '@&@' . $time->format('m/d/Y g:i A');
                    $folder_list = $this->getFolderList($object_arr, $folder_list);
                }
            } else {
                $file_objects = $bucket->objects([
                    'prefix' => $object
                ]);

                foreach ($file_objects as $file_object) {
                    $object_arr = explode('/', $file_object->name());
                    $object_arr = array_filter($object_arr, create_function('$value', 'return $value !== "";'));
                    $time = new DateTime($file_object->info()['updated']);
                    $time->setTimezone($timeZone);
                    $object_arr[count($object_arr) - 1] = (empty($object_arr[count($object_arr) - 1])) ? '' : $object_arr[count($object_arr) - 1] . '@&@' . $time->format('m/d/Y g:i A');
                    if (pathinfo($file_object->name(), PATHINFO_EXTENSION) == 'xlsx') {
                        if (strpos($file_object->name(), $type)) {
                            $object_arr = array_filter($object_arr, create_function('$value', 'return $value !== "";'));
                            $folder_list = $this->getFolderList($object_arr, $folder_list);
                        }
                    }
                }
            }
        }
        return $folder_list;
    }

    #get file objects
    public function getFormTypeObjects($term_name, $headquarters, $type = null)
    {
        $form_type_objects = [];
        if ($type == BackupFormInfo::BRD_JPN) {
            if ($this->Session->read('PERMISSION')['BudgetResultDifferenceReadLimit'] == 1) {
                array_unshift($headquarters, BackupFormInfo::WC_JPN);
            }
        }
        foreach ($headquarters as $headquarter) {
            $form_type_objects[] = CloudStorageInfo::FOLDER_NAME . '/' . $term_name . '/' . $headquarter . '/';
        }
        return $form_type_objects;
    }

    #get ba code by user id
    public function getBACodeByLoginId($login_id)
    {
        return $this->User->find('first', array(
            'fields' => array('ba_code'),
            'conditions' => array(
                'id' => $login_id,
                'flag' => 1
            ),
        ))['User']['ba_code'];
    }

    #get business area data by ba code
    public function getBusinessAreaByBACode($ba_code)
    {
        $head_id = $this->BusinessAreaModel->find('first', array(
            'fields' => array('head_dept_id'),
            'conditions' => array(
                'ba_code' => $ba_code,
                'flag' => 1
            ),
        ))['BusinessAreaModel']['head_dept_id'];

        $hqs = $this->BrmBackupFile->find('list', array(
            'fields' => array('head_dept_name'),
            'conditions' => array(
                'status' => 1,
                'flag'  => 1,
                'head_dept_id' => $head_id
            ),
            'group' => 'head_dept_name'
        ));

        return $hqs;
    }

    #verify hq already backup
    public function hasHqBackup($term_id, $head_dept_list, $type)
    {
        $hasHq = $this->BrmBackupFile->find('list', array(
            'fields' => array('id', 'hlayer_code'),
            'conditions' => array(
                'term_id' => $term_id,
                'hlayer_code' => array_keys($head_dept_list),
                'file_type' => $type
            )
        ));
        return $hasHq;
    }

    #archive files
    public function filesToZip($form_name)
    {
        $dir = new Folder(WWW_ROOT . $form_name . '/');
        $originalPath = $dir->pwd();
        $fileNames = $dir->find('.*\.xlsx');
        $files = array();
        foreach ($fileNames as $fileName) {
            array_push($files, $originalPath . $fileName);
        }
        $zipFileName = $form_name . '.zip';
        $result = $this->createZipArchive($files, $zipFileName);
        if (!$result) {
            $msg = parent::getErrorMsg("SE114");
            $this->Flash->set($msg, array('key' => 'errorBackup'));
            $this->redirect(array('controller' => 'BackupFile', 'action' => 'index'));
        }
    }

    #upload archive files into cloud
    public function uploadZipToCloud($resource)
    {
        $zip_dir = new Folder(APP . '/tmp/');
        $zip_path = $zip_dir->pwd();
        // $zip_names = $zip_dir->find('.*\.zip');
        $zip_names = $zip_dir->find('予実比較');
        // $uploaded_fail = array();
        // foreach ($zip_names as $zip_name) {
        // if(BackupFormInfo::has(pathinfo($zip_name, PATHINFO_FILENAME))){
        $this->__upload_object_to_cloud('test', $resource, 'Archive/New');
        //}
        //}
    }

    #create archive files
    public function createZipArchive($files = array(), $destination = '', $long_file_name = '', $overwrite = false)
    {
        if (file_exists($destination)) {
            $overwrite = true;
        }

        $validFiles = array();

        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    array_push($validFiles, $file);
                }
            }
        }

        if (count($validFiles)) {
            $zip = new ZipArchive();
            $long_file_arr = explode('@#$', $long_file_name);
            #define length for folder path
            $file_len = array(40, 52, 72, 120);
            foreach ($long_file_arr as $key => $value) {
                if (strlen($value) > $file_len[$key]) {
                    $long_file_arr[$key] = mb_strcut($value, 0, $file_len[$key]) . '... ';
                }
            }
            $long_file_name = implode('_', $long_file_arr);
            if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) == true) {
                foreach ($validFiles as $file) {
                    $folder_path = end(explode('/', $file));
                    $my_path = array_filter(explode('%#%', $folder_path));
                    $base_file = array_pop($my_path);
                    if (strlen($base_file) > end($file_len)) {
                        $base_file = mb_strcut(explode('.xlsx', $base_file)[0], 0, (end($file_len) - 5)) . '....xlsx';
                    }
                    if (count($my_path) == 0) {
                        $my_path = $long_file_name . '/' . implode('/', $my_path);
                    } else {
                        $my_path = $long_file_name . '/' . implode('/', $my_path) . '/';
                    }
                    if (is_file($file)) {
                        $zip->addFile($file, $my_path . $base_file);
                    }
                }
                $zip->close();
                return file_exists($destination);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    #upload files into cloud
    public function __upload_object_to_cloud($objectName, $source, $Path)
    {
        try {
            $cloud = parent::connect_to_google_cloud_storage();

            $storage = $cloud[0];
            $bucketName = $cloud[1];

            $file = fopen($source, 'r');
            $bucket = $storage->bucket($bucketName);

            $object = $bucket->object($objectName);
            $object = $bucket->upload($file, [
                'name' => $Path . $objectName
            ]);
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'file upload error on cloud ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            return $this->redirect(array('controller' => 'BackupFile', 'action' => 'index'));
        }
    }

    #delete files from cloud
    public function __delete_object_from_cloud($url)
    {
        try {
            $cloud = parent::connect_to_google_cloud_storage();
            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
            $object = $bucket->object($url);
            if ($object->exists()) {
                $object->delete();
            } else {
                return false;
            }
        } catch (GoogleException $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            return false;
        }
        return true;
    }

    #remove files
    public function removeFiles($form_name)
    {
        // $dir = new Folder(WWW_ROOT.$form_name.'\\');
        $dir = new Folder(APP . '/tmp/' . $form_name . '/');
        $originalPath = $dir->pwd();
        $fileNames = $dir->find('.*\.xlsx');
        foreach ($fileNames as $fileName) {
            unlink($originalPath . $fileName);
        }
        rmdir(APP . '/tmp/' . $form_name);
        // unlink($form_name.'.zip');
    }

    #get ba code by headquarter id
    public function getBACode($head_dept_id)
    {
        #get permission from session
        $permission = $this->Session->read('PERMISSION');
        #get user id
        $user_id = $this->Session->read('LOGIN_ID');

        $today_date = date("Y/m/d");
        #get read_limit 1 or 2 or 3 or 4
        $readLimit   = $permission['BudgetingSystemReadLimit'];
        $data = array();

        #get head_dept_id of user
        $userHQ = $this->User->find('first', array(
            'fields' => 'ba.head_dept_id',
            'conditions' => array(
                'User.flag' => 1,
                'User.id' => $user_id
            ),
            'joins' => array(
                array(
                    'table' => 'tbl_business_area',
                    'alias' => 'ba',
                    'type'  =>  'left',
                    'conditions' => array(
                        'ba.ba_code = User.ba_code',
                        'ba.flag' => 1,
                        'ba.dept_id !=' => '',
                        'ba.head_dept_id !=' => '',
                        'ba.to_date >=' => $today_date
                    )
                )
            )
        ));

        # get user name
        $getUserName = $this->User->find('list', array(
            'fields' => array('user_name'),
            'conditions' => array(
                'id' => $user_id,
                'flag' => 1
            )
        ))[$user_id];
        # get head department list
        $head_id_list = array();
        if ($getUserName == 'マキシコン事業部長') {
            $head_id_list = $this->BusinessAreaModel->find('list', array(
                'fields' => array('head_dept_id'),
                'conditions' => array(
                    'ba_code' => '801C',
                    'flag' => 1
                ),
            ));
        }

        $userHQ = $userHQ['ba']['head_dept_id'];
        if (!in_array($head_dept_id, $head_id_list)) {
            array_push($head_id_list, $userHQ);
        }

        if ($readLimit == 1 || ($readLimit == 2 && in_array($head_dept_id, $head_id_list))) {
            // $data['errmsg'] = '';

            $conditions = array();
            $conditions["BusinessAreaModel.flag ="] = 1;
            $conditions["BusinessAreaModel.to_date >="] = $today_date;
            $conditions["BusinessAreaModel.head_dept_id"] = $head_dept_id;

            // if(!empty($dept_id))
            //  $conditions["BusinessAreaModel.dept_id"] = $dept_id;

            $data = $this->BusinessAreaModel->find('all', array(
                'fields' => array('ba_code', 'ba_name_jp', 'ba_name_en', 'YEAR(from_date) as from_date'),
                'conditions' => $conditions,
                'order'      => array('ba_code ASC')
            ));
            array_pop($head_id_list);
            if (in_array($head_dept_id, $head_id_list)) {
                $conditions["BusinessAreaModel.ba_code"] = '801C';

                reset($data);
                $data = $this->BusinessAreaModel->find('all', array(
                    'fields' => array('ba_code', 'ba_name_jp', 'ba_name_en', 'YEAR(from_date) as from_date'),
                    'conditions' => $conditions,
                    'order'      => array('ba_code ASC'),
                ));
            }
        }
        //else {
        //  $data['errmsg'] = 'You dont have permission';
        //  $data['ba_list'] = '';
        // }

        return $data;
    }

    #make directory
    public function makeDir($dir)
    {
        $dir_arr = explode('/', $dir);
        $path = APP . '/tmp/';
        for ($i = 0; $i < count($dir_arr); $i++) {
            $path = $path . $dir_arr[$i] . '/';
            $folder = mkdir($path);
        }
    }

    #remove directory
    public function removeDir($dir)
    {
        $path = APP . '/tmp/';
        rmdir($path . $dir);
    }

    #save cloud file path
    public function Save_Cloud_File_Path($url, $file_size, $date = null)
    {
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        if ($extension == 'xlsx') {
            try {
                $cloud_files = $this->BrmCloudFile->getDataSource();
                $cloud_files->begin();
                $c_file = array();
                $now = date("Y-m-d H:i:s");
                #only for shortcut
                if (!is_null($date)) {
                    $c_file['created_date'] =  $date;
                    $c_file['updated_date'] =  $date;
                }

                $cloud_files_exit = $this->BrmCloudFile->find('first', array(
                    'conditions' => array(
                        'url' => $url
                    )
                ));
                if (empty($cloud_files_exit)) {
                    $c_file['url']      = $url;
                    $c_file['file_size']    = $file_size;
                    $c_file['flag']         = 1;
                } else {
                    $c_file['id'] = $cloud_files_exit['BrmCloudFile']['id'];
                    $c_file['file_size']    = $file_size;
                    $c_file['flag'] = 1;
                    $c_file['updated_date'] = $now;
                }
                #save data into tbl_cloud_files
                $this->BrmCloudFile->saveAll($c_file);
                $cloud_files->commit();
            } catch (Exception $e) {
                CakeLog::write('error', 'Data cannot be saved...' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $cloud_files->rollback();
            }
        }
    }

    #budget hearing download
    public function DownloadType_01($term_id, $budget_term, $head_dept_id, $headquarter, $login_id = null, $start_month = null, $end_month = null)
    {
        $SM = new BrmSummaryController();
        $PL = new BrmPLSummaryController();
        $Common = new CommonController();
        $BudgetSummary = new BrmBudgetSummaryController();

        $save_into_tmp = true;
        $admin_level_id = AdminLevel::ADMIN;
        $term_name = $this->getDisplayTermName($term_id);
        $PHPExcel = $this->PhpExcel;
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $budget_layer_code = $this->Session->read('SESSION_LAYER_CODE');
        }

        $fileName_sm = BackupFormInfo::SUM_TBL_JPN;
        $fileName_pl = BackupFormInfo::PL_SUM_JPN;

        #Get folder name from setting.php to upload cloud folder
        $archive_folder = CloudStorageInfo::FOLDER_NAME;

        #Summary Table
        ###########################################################################
        $tmpFileName = APP . '/tmp/' . $headquarter . '/' . $fileName_sm . '_' . $headquarter . '.xlsx';

        #make folder under tmp directory
        $folder = $this->makeDir($headquarter);
        #Calculate and save cache data
        $SM->getSummaryData($term_id, $budget_term, $head_dept_id, $headquarter, $login_id, $admin_level_id, $save_into_tmp);
        #download excel
        $SM->DownloadExcel($term_id, $budget_term, $headquarter, $admin_level_id, $head_dept_id, $tmpFileName, $PHPExcel, $save_into_tmp);

        $Path = $archive_folder . '/' . $term_name . '/' . $headquarter . '/';
        $objectName = $fileName_sm . '_' . $headquarter . '.xlsx';


        #PL Summary
        ###########################################################################
        $tmpFileName_pl = APP . '/tmp/' . $headquarter . '/' . $fileName_pl . '_' . $headquarter . '.xlsx';

        #make folder under tmp directory
        $folder_pl = $this->makeDir($headquarter);
        #Calculate and save cache data
        $budget_year = explode('~', $budget_term);
        $start_year  = $budget_year[0];
        $years       = range($budget_year[0], $budget_year[1]);
        #deptid
        $dept_id = $this->Layer->find('all', array(
            'fields' => 'parent_id',
            'conditions' => array('Layer.flag' => 1, 'Layer.layer_code' => $budget_layer_code)
        ));
        $deptId = json_decode($dept_id[0]['Layer']['parent_id'], true);
        $BudgetSummary->getBudgetSummary($term_id, $head_dept_id, $deptId['L2'], $budget_layer_code, 'PL', $start_year, $years);
        //zeyar
        // $PL->calculateAmounts($term_id, $head_dept_id, '', '', $start_year, $years, $login_id, $save_into_tmp);
        #download excel

        $this->downloadCache($term_id, $head_dept_id, $budget_layer_code, $budget_term, $login_id);

        $PL->DownloadExcel($term_id, $budget_term, $headquarter, $head_dept_id, $tmpFileName_pl, $PHPExcel, $login_id, $save_into_tmp);

        $Path_pl = $archive_folder . '/' . $term_name . '/' . $headquarter . '/';
        $objectName_pl = $fileName_pl . '_' . $headquarter . '.xlsx';


        #Upload files to cloud
        try {
            #upload file
            $this->__upload_object_to_cloud($objectName, $tmpFileName, $Path);
            #save file path
            $file_size = number_format(filesize($tmpFileName) / 1024, 1, '.', '') . 'KB';
            $this->Save_Cloud_File_Path($Path . $objectName, $file_size);
            #upload file
            $this->__upload_object_to_cloud($objectName_pl, $tmpFileName_pl, $Path_pl);
            #save file path
            $file_size = number_format(filesize($tmpFileName_pl) / 1024, 1, '.', '') . 'KB';
            $this->Save_Cloud_File_Path($Path_pl . $objectName_pl, $file_size);
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'file upload error on cloud ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            unlink($tmpFileName);
            unlink($tmpFileName_pl);
            $this->removeDir($fileName_sm);
            $this->removeDir($fileName_pl);
            $this->removeDir($headquarter);
            return false;
        }
        unlink($tmpFileName);
        unlink($tmpFileName_pl);

        $this->removeDir($fileName_sm);
        $this->removeDir($fileName_pl);
        $this->removeDir($headquarter);
        return true;
    }

    #pl whole company download
    public function DownloadWholePL($term_id, $budget_term, $login_id = null, $start_month = null, $end_month = null)
    {
        $PL = new BrmPLSummaryController();
        $BudgetSummary = new BrmBudgetSummaryController();
        $headquarter = '全社';
        $head_dept_id = '';

        $save_into_tmp = true;
        $term_name = $this->getDisplayTermName($term_id);
        $PHPExcel = $this->PhpExcel;
        if ($this->Session->check('SESSION_LAYER_CODE')) {
            $budget_layer_code = $this->Session->read('SESSION_LAYER_CODE');
        }

        $fileName_pl = BackupFormInfo::PL_SUM_JPN;

        #Get folder name from setting.php to upload cloud folder
        $archive_folder = CloudStorageInfo::FOLDER_NAME;

        #PL Summary
        ###########################################################################
        $tmpFileName_pl = APP . '/tmp/' . $headquarter . '/' . $fileName_pl . '_' . $headquarter . '.xlsx';

        #make folder under tmp directory
        $folder_pl = $this->makeDir($headquarter);
        #deptid
        $dept_id = $this->Layer->find('all', array(
            'fields' => 'parent_id',
            'conditions' => array('Layer.flag' => 1, 'Layer.layer_code' => $budget_layer_code)
        ));
        $deptId = json_decode($dept_id[0]['Layer']['parent_id'], true);
        #Calculate and save cache data
        $budget_year = explode('~', $budget_term);
        $start_year  = $budget_year[0];
        $years       = range($budget_year[0], $budget_year[1]); //zeyar
        $BudgetSummary->getBudgetSummary($term_id, $head_dept_id, $deptId['L2'], $budget_layer_code, 'PL', $start_year, $years);
        // $PL->calculateAmounts($term_id, $head_dept_id, '', '', $start_year, $years, $login_id, $save_into_tmp);
        $this->downloadCache($term_id, $head_dept_id, $budget_layer_code, $budget_term, $login_id);
        #download excel
        $PL->DownloadExcel($term_id, $budget_term, $headquarter, $head_dept_id, $tmpFileName_pl, $PHPExcel, $login_id, $save_into_tmp);

        $Path_pl = $archive_folder . '/' . $term_name . '/' . $headquarter . '/';
        $objectName_pl = $fileName_pl . '_' . $headquarter . '.xlsx';

        #Upload files to cloud
        try {
            #upload file
            $this->__upload_object_to_cloud($objectName_pl, $tmpFileName_pl, $Path_pl);
            #save file path
            $file_size = number_format(filesize($tmpFileName_pl) / 1024, 1, '.', '') . 'KB';
            $this->Save_Cloud_File_Path($Path_pl . $objectName_pl, $file_size);
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'file upload error on cloud ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            unlink($tmpFileName_pl);
            $this->removeDir($headquarter);

            return false;
        }
        unlink($tmpFileName_pl);

        $this->removeDir($headquarter);
        return true;
    }

    #plan form download
    public function DownloadType_02($term_id, $budget_term, $hlayer_code, $hlayer_name, $login_id = null, $start_month = null, $end_month = null)
    {
        $TP = new BrmTradingPlanController();
        $MP = new BrmManpowerPlanController();
        $BP = new BrmBudgetPlanController();
        $FBD = new BrmForecastBudgetDifferenceController();
        $Common = new CommonController();

        $save_into_tmp = true;
        $today_date = date('Y/m/d');
        $budget_year = explode('~', $budget_term);
        $start_year = $budget_year[0];
        $end_year = $budget_year[1];
        $years = range($start_year, $end_year);
        $term_name = $this->getDisplayTermName($term_id);
        $PHPExcel = $this->PhpExcel;

        $fileName_plan = BackupFormInfo::PLAN_JPN;
        $fileName_tp = BackupFormInfo::TPF_JPN;
        $fileName_mp = BackupFormInfo::MPF_JPN;
        $fileName_bp = BackupFormInfo::BF_JPN;
        $fileName_fp = BackupFormInfo::FF_JPN;
        $fileName_fbd = BackupFormInfo::FBD_JPN;

        $language = $this->Session->read('Config.language');

        if ($this->Session->check('PERMISSION')) {
            $permission = $this->Session->read('PERMISSION');
        }
        $ba_list = $this->Layer->find('list', array(
            'fields' => array('layer_code', 'name_jp'),
            'conditions' => array(
                'flag' => 1,
                'to_date >=' => $today_date,
                'type_order ' => Setting::LAYER_SETTING['bottomLayer'],
                "Layer.parent_id LIKE CONCAT('%\"L', " . Setting::LAYER_SETTING['topLayer'] . ", '\":\"'," . $hlayer_code . ",'\"%')"
            ),
            'order' => array('layer_code  ASC')
        ));
        #Get folder name from setting.php to upload cloud folder
        $archive_folder = CloudStorageInfo::FOLDER_NAME;
        foreach ($ba_list as $ba_code => $ba_name) {
            $upload_file_arr = [];
            $folder_dir = $hlayer_name . '/計画フォーム_' . $ba_code . '-' . $ba_name;
            $file_name_fbd = $fileName_fbd . '_' . $ba_code . $ba_name . '.xlsx';
            $upload_file_arr[0] = $file_name_fbd;

            #make folder under tmp directory
            $this->makeDir($folder_dir);

            #Forecast & Budget Difference
            ##########################################################################
            $tmpFileName = APP . '/tmp/' . $folder_dir . '/' . $file_name_fbd;

            #Calculate and save cache data
            $FBD->getFbdData($term_id, $hlayer_code, $ba_code, $start_year, $end_year, $login_id);
            #download excel
            $FBD->DownloadExcel($term_id, $budget_term, $ba_code, $login_id, $tmpFileName, $PHPExcel, $language, $save_into_tmp);

            #set required session
            // $this->Session->write('SESSION_LAYER_CODE', $ba_code);
            // $this->Session->write('BUDGET_BA_NAME', $ba_name);
            // foreach ($years as $each_year) {
            //     $_SESSION['YEAR'] = $each_year;
            //     $Common->combineAsExcelSheets($term_id, $budget_term, $hlayer_code, $hlayer_name, $login_id, $this, $save_into_tmp);
            //     $upload_file_arr[] = $this->Session->read('UPLOAD_FILE_ARR');
            //     unset($_SESSION['YEAR']);
            // }
            #download bulk plan excel file
            // $FBD->downloadBulkExcelDownload();
            // unset($_SESSION['active_index']);
            // unset($_SESSION['total_years']);
            foreach ($years as $each_year) {
                if (!in_array($hlayer_name, Setting::TRADING_DISABLE_HQS)) {
                    $name_tp = $each_year . '_' . $fileName_tp . '_' . $ba_code . $ba_name . '.xlsx';
                }
                $name_mp = $each_year . '_' . $fileName_mp . '_' . $ba_code . $ba_name . '.xlsx';
                $name_bp = ($each_year == $start_year) ? $each_year . '_' . $fileName_fp . '_' . $ba_code . $ba_name . '.xlsx' : $each_year . '_' . $fileName_bp . '_' . $ba_code . $ba_name . '.xlsx';

                $upload_file_arr = array_merge($upload_file_arr, array($name_tp, $name_mp, $name_bp));

                $file_name_tp = APP . '/tmp/' . $folder_dir . '/' . $name_tp;
                $file_name_mp = APP . '/tmp/' . $folder_dir . '/' . $name_mp;
                $file_name_bp = APP . '/tmp/' . $folder_dir . '/' . $name_bp;

                #Trading Plan Save cache & download
                if (!in_array($hlayer_name, Setting::TRADING_DISABLE_HQS)) {
                    $tr_data = $TP->getTradingDataAndCaching($each_year, $term_id, $budget_term, $hlayer_code, $ba_code, $ba_name, $login_id);
                    $approved_BA = $tr_data['approved_BA'];
                    $approveHQ = $tr_data['approveHQ'];
                    $createlimit = $permission['BudgetingSystemCreateLimit'];

                    $createLimit = $Common->checkLimit($createlimit, $ba_code, $login_id, $permission);

                    # disabled/enabled(input field and button )
                    if ($createLimit == 'true') {
                        $page = 'Enabled';
                    } else {
                        $page = 'Disabled'; # no action and read only
                    }
                    # for excel disable
                    if (!empty($approved_BA) || !empty($approveHQ)) {
                        $approved = 'Approved';
                    }
                    #not to combine one file
                    unset($_SESSION['objworksheet']);
                    $disable = explode('_', $approved . '_' . $page);
                    $TP->DownloadExcel($term_id, $budget_term, $ba_code, $each_year, $login_id, $file_name_tp, $PHPExcel, $save_into_tmp, $disable);
                }
                #Manpower Plan Save cache & download
                $mpData = $MP->getManpowerData($term_id, $budget_term, $hlayer_code, $ba_code, $each_year, $login_id, $permission);
                if ($mpData != 'no_data') {
                    #not to combine one file
                    unset($_SESSION['objworksheet']);
                    $MP->DownloadExcel($term_id, $budget_term, $hlayer_code, $ba_code, $each_year, $login_id, $file_name_mp, $PHPExcel, $save_into_tmp);
                } else {
                    if (($key = array_search($name_mp, $upload_file_arr)) !== false) {
                        unset($upload_file_arr[$key]);
                    }
                }

                #Budget Plan Save cache & download
                $form_type = ($each_year == $start_year) ? 'forecast' : 'budget';
                $BP->getBudgetData($term_id, $budget_term, $hlayer_code, $hlayer_name, $ba_code, $ba_name, $each_year, $login_id, $save_into_tmp, $form_type);
                #not to combine one file
                unset($_SESSION['objworksheet']);
                $BP->DownloadExcel($term_id, $budget_term, $hlayer_name, $hlayer_code, $ba_code, $each_year, $form_type, $login_id, $file_name_bp, $PHPExcel, '', $save_into_tmp);
            }

            $tmp_file_path = APP . '/tmp/' . $folder_dir . '/';
            $Path = $archive_folder . '/' . $this->getDisplayTermName($term_id) . '/' . $folder_dir . '/';
            #Upload files to cloud
            $upload_file_arr = array_filter($upload_file_arr, create_function('$value', 'return $value !== "";'));

            try {
                foreach ($upload_file_arr as $each_file) {
                    $tmpFileName = $tmp_file_path . $each_file;
                    $objectName     = $each_file;
                    #upload file
                    $this->__upload_object_to_cloud($objectName, $tmpFileName, $Path);
                    #save file path
                    $file_size = number_format(filesize($tmpFileName) / 1024, 1, '.', '') . 'KB';
                    $this->Save_Cloud_File_Path($Path . $objectName, $file_size);
                    unlink($tmpFileName);
                }
            } catch (GoogleException $e) {
                CakeLog::write('debug', 'file upload error on cloud ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $this->removeDir($tmp_file_path);
                $this->removeDir($folder_dir);
                $this->removeDir($hlayer_name);
                return false;
            }
            $this->removeDir($tmp_file_path);
            $this->removeDir($folder_dir);
            $this->removeDir($hlayer_name);
        }
        return true;
    }

    #budget result difference download
    public function DownloadType_03($term_id, $budget_term, $head_dept_id, $headquarter, $login_id = null, $start_month = null, $end_month = null)
    {
        // $BRD = new BudgetResultDifferenceController();
        $target_month_ranges = $this->getTargetMonthRanges($start_month, $end_month);

        $dept_list = $this->Layer->find('list', array(
            'fields' => array('name_jp', 'layer_code'),
            'conditions' => array(
                'flag' => 1,
                'type_order' => SETTING::LAYER_SETTING['middleLayer'],
            ),
        ));
        $today_date = date('Y/m/d');
        $ba_list = $this->Layer->find('list', array(
            'fields' => array('name_jp', 'layer_code'),
            'conditions' => array(
                'flag' => 1,
                'to_date >=' => $today_date,
                "Layer.parent_id LIKE CONCAT('%\"L', " . Setting::LAYER_SETTING['topLayer'] . ", '\":\"'," . $head_dept_id . ",'\"%')"
            ),
        ));

        // $head_dept_list = $this->getHeadDeptList();#id, hq_name

        foreach ($target_month_ranges as $month) {
            $target_month = $month->format('Y-m');
            $tgmonth = date('Ym', strtotime($target_month));

            $tab = 'Headquarters';
            $prefix = '全社';
            $folderName = $prefix . '/' . $target_month;
            $fileName = $tgmonth . '_' . BackupFormInfo::BRD_JPN . '_' . $prefix;
            $this->_type03_download_n_upload($tab, $target_month, $term_id, $head_dept_id, $prefix, $budget_term, $folderName, $fileName);

            $tab = 'Department';
            $hq_name = $headquarter;
            $folderName = $hq_name . '/' . $target_month;
            $fileName = $tgmonth . '_' . BackupFormInfo::BRD_JPN;
            $this->_type03_download_n_upload($tab, $target_month, $term_id, $head_dept_id, $hq_name, $budget_term, $folderName, $fileName);


            $cacheName = 'brd_' . $term_id . '_' . $tgmonth . '_' . $tab . '_' . $head_dept_id;
            $get_calculate = Cache::read($cacheName);
            $dept_name_list = array_keys($get_calculate['data'][0]);
            foreach ($dept_name_list as $dept_name) {
                $tab = 'BA';
                $dept_id = $dept_list[$dept_name];
                $folderName = $hq_name . '/' . $target_month;
                $fileName = $tgmonth . '_' . BackupFormInfo::BRD_JPN . '_' . $dept_name;
                $this->_type03_download_n_upload($tab, $target_month, $term_id, $dept_id, $dept_name, $budget_term, $folderName, $fileName);

                $cacheName = 'brd_' . $term_id . '_' . $tgmonth . '_' . $tab . '_' . $dept_id;
                $get_calculate = Cache::read($cacheName);
                $ba_name_list = array_keys($get_calculate['data'][0]);
                foreach ($ba_name_list as $ba_name) {
                    $tab = 'Logistic';
                    $ba_code = $ba_list[$ba_name];
                    $folderName = $hq_name . '/' . $target_month;
                    $fileName = $tgmonth . '_' . BackupFormInfo::BRD_JPN . '_' . $dept_name . '_' . $ba_code . $ba_name;
                    $this->_type03_download_n_upload($tab, $target_month, $term_id, $ba_code, $ba_name, $budget_term, $folderName, $fileName);
                }
            }
        }
        $this->removeDir($folderName);
        $this->removeDir($headquarter);
        return true;
    }
    public function sessionWritingType03()
    {
        $type_data = $this->Session->read('LayerTypeData');
        $top_layer_type     = Setting::LAYER_SETTING['topLayer'];
        $middle_layer_type  = Setting::LAYER_SETTING['middleLayer'];
        $bottom_layer_type  = Setting::LAYER_SETTING['bottomLayer'];
        $this->Session->write('TOP_LAYER_NAME', $type_data[$top_layer_type]);
        $this->Session->write('MIDDLE_LAYER_NAME', $type_data[$middle_layer_type]);
        $this->Session->write('BOTTOM_LAYER_NAME', $type_data[$bottom_layer_type]);
    }
    #extra method for budget result difference download
    public function _type03_download_n_upload($tab, $target_month, $term_id, $head_dept_id, $headquarter, $budget_term, $folderName, $fileName)
    {
        $BRD = new BrmBudgetResultDifferenceController();

        $save_into_tmp = true;
        $id_array = [];
        $searched_id = 0;

        $PHPExcel = $this->PhpExcel;

        $tmpFileName = APP . '/tmp/' . $folderName . '/' . $fileName . '.xlsx';

        #make folder under tmp directory
        $folder = $this->makeDir($folderName);
        // $folder = $this->makeDir(BackupFormInfo::BRD_JPN.'/'.$headquarter);
        $top_layer_type = Setting::LAYER_SETTING['topLayer'];
        $this->sessionWritingType03();
        #Calculate and save cache data
        $brd_result = $BRD->getCacheData($tab, $target_month, $term_id, $id_array, $searched_id, $head_dept_id, $budget_term, $top_layer_type);

        #download excel
        $BRD->DownloadExcel($term_id, $budget_term, $target_month, $headquarter, $head_dept_id, $tmpFileName, $PHPExcel, $id_array, $tab, $searched_id, $save_into_tmp);

        #Get folder name from setting.php to upload cloud folder
        $archive_folder = CloudStorageInfo::FOLDER_NAME;

        $Path = $archive_folder . '/' . $this->getDisplayTermName($term_id) . '/' . $folderName . '/';
        $objectName = $fileName . '.xlsx';

        try {
            if (!empty($brd_result[0])) {
                #upload file
                $this->__upload_object_to_cloud($objectName, $tmpFileName, $Path);
                #save file path
                $file_size = number_format(filesize($tmpFileName) / 1024, 1, '.', '') . 'KB';
                $this->Save_Cloud_File_Path($Path . $objectName, $file_size);
            }
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'file upload error on cloud ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            unlink($tmpFileName);
            $this->removeDir($folderName);
            $this->removeDir($headquarter);
            return false;
        }
        unlink($tmpFileName);
        $this->removeDir($folderName);
        $this->removeDir($headquarter);
    }

    #monthly report download
    public function DownloadType_04($term_id, $budget_term, $hlayer_code, $hlayer_name, $user_id = null, $start_month = null, $end_month = null)
    {
        $save_into_tmp = true;
        $MR = new BrmMonthlyReportController();

        $target_month_ranges = $this->getTargetMonthRanges($start_month, $end_month);
        // $head_dept_list = $this->getHeadDeptList();#id, hq_name

        #Get folder name from setting.php to upload cloud folder
        $archive_folder = CloudStorageInfo::FOLDER_NAME;

        foreach ($target_month_ranges as $month) {
            $target_month = $month->format('Y-m');
            $tgmonth = date('Ym', strtotime($target_month));
            $tmpFileName = APP . '/tmp/' . $hlayer_name . '/' . BackupFormInfo::MR_JPN . '_' . $target_month . '.xlsx';

            #make folder under tmp directory
            $folder = $this->makeDir($hlayer_name);

            #Calculate and save cache data
            $MR->commonIndexCal($hlayer_code, $hlayer_name, $budget_term, $term_id, $target_month);

            $PHPExcel = $this->PhpExcel;

            #download excel
            $res = $MR->DownloadExcel($term_id, $budget_term, $target_month, $hlayer_code, $hlayer_name, $tmpFileName, $PHPExcel, $save_into_tmp);
            $Path = $archive_folder . '/' . $this->getDisplayTermName($term_id) . '/' . $hlayer_name . '/' . $target_month . '/';
            $objectName = $tgmonth . '_' . BackupFormInfo::MR_JPN . '.xlsx';
            try {
                #upload file
                $this->__upload_object_to_cloud($objectName, $tmpFileName, $Path);
                #save file path
                $file_size = number_format(filesize($tmpFileName) / 1024, 1, '.', '') . 'KB';
                $this->Save_Cloud_File_Path($Path . $objectName, $file_size);
            } catch (GoogleException $e) {
                CakeLog::write('debug', 'file upload error on cloud ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                unlink($tmpFileName);
                $this->removeDir($hlayer_name);

                return false;
            }
            unlink($tmpFileName);
        }

        $this->removeDir($hlayer_name);
        return true;
    }

    #download files as zip type or xlsx type
    public function Download_Archive()
    {
        if ($this->request->is('POST')) {
            $url = $this->request->data('url');
            $url_count = count(explode('/', $url));
            $download_file = $this->request->data('download_file');

            try {
                $cloud = parent::connect_to_google_cloud_storage();
                $storage = $cloud[0];
                $bucketName = $cloud[1];
                $bucket = $storage->bucket($bucketName);

                $extension = pathinfo($url, PATHINFO_EXTENSION);
                if ($extension == 'xlsx') {
                    $object = $bucket->object($url);
                    $stream = $object->downloadAsStream();
                    header('Content-disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(end(explode('/', $url))));
                    echo $stream->getContents();
                    exit();
                } else {
                    $download_path = explode('/', $url);
                    $assign_path = $download_path;
                    array_shift($assign_path);
                    $zip_file_path = implode('@#$', $assign_path);
                    $long_file_name = trim(implode('_', $assign_path), ' ');
                    /* zip file path name */
                    $filter_path = $assign_path;
                    $tmp_str = implode('@#$', $assign_path);
                    if (count($assign_path) == 3 && strpos($tmp_str, '計画フォーム')) {
                        unset($filter_path[1]);
                        $zip_file_path = implode('@#$', $filter_path);
                    } else {
                        $zip_file_path = implode('@#$', $filter_path);
                    }
                    $long_file_name = trim(implode('_', $filter_path), ' ');
                    /* zip file path name end */
                    $zip_code = uniqid(rand(), true);
                    
                    $archive_folder = $download_path[0]; #Assign archive folder
                    $term_name = $download_path[1]; #Assign term name

                    $data_arr = $download_file[$download_path[0]][$download_path[1]];
                    foreach ($data_arr as $head_name => $head_files) {
                        $save_url = $download_path[1] . '/' . $head_name;
                        $cloud_url = $save_url;
                        $url_arr = explode('/', $save_url);
                        $save_url = implode('/', array_diff($url_arr, $assign_path));
                        $folder_path = str_replace('/', '%#%', $save_url) . '%#%';
                        if ((empty($download_path[2])) || ($head_name == $download_path[2])) {
                            foreach ($head_files as $sub_folder => $files) {
                                $file_ext = pathinfo($save_url . '/' . $files, PATHINFO_EXTENSION);
                                if ($file_ext != 'xlsx') {
                                    $save_url = $download_path[1] . '/' . $head_name . '/' . $sub_folder;
                                    $cloud_url = $save_url;
                                    $url_arr = explode('/', $save_url);
                                    $save_url = implode('/', array_diff($url_arr, $assign_path));
                                    $folder_path = str_replace('/', '%#%', $save_url) . '%#%';
                                    if ((empty($download_path[3])) || ($sub_folder == $download_path[3])) {
                                        $this->makeDir($zip_code . '/' . $long_file_name);
                                        foreach ($files as $each_file) {
                                            $file_str = $folder_path . $each_file;
                                            if (strlen($file_str) > 250) {
                                                $file_str_name = explode('.xlsx', $file_str)[0];
                                                $file_str = mb_strcut($file_str_name, 0, 245) . '....xlsx';
                                            }
                                            $tmpFileName = APP . 'tmp/' . $zip_code . '/' . $long_file_name . '/' . $file_str;
                                            $object_url = $archive_folder . '/' . $cloud_url . '/' . $each_file;
                                            $object = $bucket->object($object_url);
                                            $stream = $object->downloadAsStream();
                                            try {
                                                file_put_contents($tmpFileName, $stream->getContents());
                                            } catch (Exception $e) {
                                                CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                            }
                                        }
                                    }
                                } elseif ($file_ext == 'xlsx' && count($download_path) <= 3) {
                                    $save_url = $download_path[1] . '/' . $head_name;
                                    $cloud_url = $save_url;
                                    $url_arr = explode('/', $save_url);
                                    $save_url = implode('/', array_diff($url_arr, $assign_path));
                                    $folder_path = str_replace('/', '%#%', $save_url) . '%#%';
                                    $this->makeDir($zip_code . '/' . $long_file_name);
                                    $file_str = $folder_path . $files;
                                    if (strlen($file_str) > 250) {
                                        $file_str_name = explode('.xlsx', $file_str)[0];
                                        $file_str = mb_strcut($file_str_name, 0, 245) . '....xlsx';
                                    }
                                    $tmpFileName = APP . 'tmp/' . $zip_code . '/' . $long_file_name . '/' . $file_str;
                                    $object_url = $archive_folder . '/' . $cloud_url . '/' . $files;
                                    $object = $bucket->object($object_url);
                                    $stream = $object->downloadAsStream();
                                    try {
                                        file_put_contents($tmpFileName, $stream->getContents());
                                    } catch (Exception $e) {
                                        CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                    }
                                }
                            }
                        }
                    }
                    $dir = new Folder(APP . 'tmp/' . $zip_code . '/' . $long_file_name . '/');
                    $originalPath = $dir->pwd();
                    $fileNames = $dir->find('.*\.xlsx');
                    $files = array();
                    foreach ($fileNames as $fileName) {
                        array_push($files, $originalPath . $fileName);
                    }
                    $zipFileName = $long_file_name . '.zip';
                    $tmp_zip = $zip_code . '/' . $long_file_name . '/';

                    $result = $this->createZipArchive($files, $zipFileName, $zip_file_path);
                    if ($result) {
                        foreach ($files as $file) {
                            unlink($file);
                        }
                        $this->removeDir($tmp_zip);
                        $this->removeDir($zip_code);
                        header("Content-Disposition: attachment; filename=\"" . $zipFileName . "\"");
                        header("Content-Length: " . filesize($zipFileName));
                        readfile($zipFileName);        
                        unlink($zipFileName);
                        // $msg = parent::getSuccessMsg("SS031");
                        // $this->Flash->set($msg, array('key' => 'successBackup'));
                        // $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
                    } else {
                        $msg = parent::getErrorMsg("SE012");
                        $this->Flash->set($msg, array('key' => 'errorBackup'));
                        $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
                    }
                }
            } catch (GoogleException $e) {
                CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE012");
                $this->Flash->set($msg, array('key' => 'errorBackup'));
                $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
            }
        }
    }

    #get cloud objects according to permission
    public function getCloudObjectsByPermission($form_objects, $bucket, $destination, $extension, $type)
    {
        foreach ($form_objects as $form_object) {
            $objects = $bucket->objects([
                'prefix' => $form_object
            ]);
            foreach ($objects as $object) {
                $zip_folder_path = str_replace('/', '%#%', $object->name());
                $this->makeDir($destination);
                $tmpFileName = APP . '/tmp/' . $destination . '/' . $zip_folder_path;
                if (strpos($object->name(), $extension)) {
                    if (strpos($object->name(), $type)) {
                        $stream = $object->downloadAsStream();
                        file_put_contents($tmpFileName, $stream->getContents());
                    }
                }
            }
        }
    }

    #delete files
    public function Delete_File()
    {
        if ($this->request->is('POST')) {
            $url = $this->request->data('url');

            $term_id = $this->request->data('search_termid');
            $hq = array_unique($this->request->data('headquarters'));
            sort($hq);
            $head_dept_arr = (strpos($hq[0], ',')) ? explode(',', $hq[0]) : $hq;
            $type = $this->request->data('search_type');
            $start_month = $this->request->data('search_smonth');
            $end_month = $this->request->data('search_emonth');

            #solve same term prefix
            if (count(explode('/', $url)) == 2) {
                $url = $url . '/';
            }

            if (!empty($term_id)) {
                $this->Session->write('BK_SEARCH_TERMID', $term_id);
            }
            if (!empty($head_dept_arr)) {
                $this->Session->write('BK_SEARCH_HEADID', $head_dept_arr);
            }
            if (!empty($type)) {
                $this->Session->write('BK_SEARCH_TYPE', $type);
            }
            if (!empty($start_month)) {
                $this->Session->write('BK_SEARCH_SMONTH', $start_month);
            }
            if (!empty($end_month)) {
                $this->Session->write('BK_SEARCH_EMONTH', $end_month);
            }
            $deleted_file = false;
            try {
                $cloud_files = $this->BrmCloudFile->getDataSource();
                $cloud_files->begin();
                $old_data = $this->BrmCloudFile->find('all');
                $this->BrmCloudFile->updateAll(
                    array('flag' => '0'),
                    array('flag' => '1', 'url LIKE' => $url . '%')
                );
                $new_data = $this->BrmCloudFile->find('all');
                if ($old_data !== $new_data) {
                    $deleted_file = true;
                }
                $cloud_files->commit();
            } catch (Exception $e) {
                CakeLog::write('error', 'Data cannot be updated...' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $cloud_files->rollback();
                $msg = parent::getErrorMsg('SE116');
                $this->Flash->set($msg, array('key' => 'errorBackup'));
                $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
            }
            if ($deleted_file) {
                $msg = parent::getSuccessMsg('SS028');
                $this->Flash->set($msg, array('key' => 'successBackup'));
                $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
            } else {
                $msg = parent::getErrorMsg('SE116');
                $this->Flash->set($msg, array('key' => 'errorBackup'));
                $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
            }
        }
    }

    #delete queued row
    public function Delete_Queued_Row()
    {
        if ($this->request->is('POST')) {
            $backup_id = $this->request->data('backup_id');

            $queued_row = $this->BrmBackupFile->find('first', array(
                'conditions' => array('id' => $backup_id),
            ));

            if (!empty($queued_row)) {
                $backup_files[] = array(
                    'id' => $queued_row['BrmBackupFile']['id'],
                    'flag' => 0,
                );
                $this->BrmBackupFile->saveAll($backup_files);
            } else {
                $msg = parent::getErrorMsg("SE116");
                $this->Flash->set($msg, array('key' => 'errorBackup'));
                $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
            }
            $successMsg  = parent::getSuccessMsg("SS003");
            $this->Flash->set($successMsg, array("key" => "successBackup"));
            $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
        }
    }

    #get headquarter list
    public function getHeadDeptList()
    {
        $head_dept_list = $this->Layer->find('list', array(
            'fields' => array('layer_code', 'name_jp'),
            'conditions' => array('flag' => 1, "layer_type_id" => SETTING::LAYER_SETTING['topLayer']),
        ));
        return $head_dept_list;
    }

    #get department name
    public function getDeptName($dept_id)
    {
        $dept = $this->Layer->find('list', array(
            'fields' => array('name_jp', 'name_en'),
            'conditions' => array(
                'flag' => 1,
                'id' => $dept_id
            )
        ));
        return $dept[$dept_id];
    }

    #get target month ranges for disable
    public function getTargetMonthRanges($start_month, $end_month)
    {
        // $term = $this->BrmTerm->find('first', array('conditions' => array('flag' => '1', 'id' => $term_id)));

        $start_token_date = explode('-', $start_month);
        $start_year = $start_token_date[0];
        $start_month = $start_token_date[1];
        $end_token_date = explode('-', $end_month);
        $end_year = $end_token_date[0];
        $end_month = $end_token_date[1] + 1;

        if ($end_month == 13) {
            $end_year++;
            $end_month = '01';
        }

        #'2020-12'
        $target_start_month = new DateTime($start_year . '-' . $start_month);
        #'2024-12'
        $target_end_month = (new DateTime($end_year . '-' . $end_month));

        $interval = new DateInterval('P1M');

        $target_month_ranges = new DatePeriod($target_start_month, $interval, $target_end_month);

        return $target_month_ranges;
    }

    #make unique id
    public function getUniqueAllId($list)
    {
        $tmp_array = array();
        foreach ($list as $key => $value) {
            foreach ($value as $val) {
                $tmp_array[$key][] = $val;
            }
            $tmp_array[$key] = array_unique($tmp_array[$key]);
        }
        return $tmp_array;
    }

    #get term name
    public function getTermName($term_id)
    {
        $term = $this->BrmTerm->find('first', array('conditions' => array('flag' => '1', 'id' => $term_id)));

        $term_name = $term['BrmTerm']['budget_year'] . '~' . $term['BrmTerm']['budget_end_year'];
        return $term_name;
    }

    #get display term name
    public function getDisplayTermName($term_id)
    {
        $term = $this->BrmTerm->find('first', array('conditions' => array('flag' => '1', 'id' => $term_id)));
        $term_name = $term['BrmTerm']['term_name'];
        $term_name =  ($term_name == '' || empty($term_name)) ? $term['BrmTerm']['budget_year'] . '~' . $term['BrmTerm']['budget_end_year'] : $term_name;
        return $term_name;
    }

    #get headquarter deadline dates
    public function getHqDeadline($term_id, $head_dept_id)
    {
        $head_dept_dates = $this->BrmTermDeadline->find('list', array(
            'fields' => array('head_department_id', 'deadline_date'),
            'conditions' => array('term_id' => $term_id, 'head_department_id' => $head_dept_id)
        ));
        return $head_dept_dates[$head_dept_id];
    }

    #get headquarter name
    public function getHeadDepartName($head_dept_id)
    {
        $head_dept = $this->Layer->find('list', array(
            'fields' => array('Layer.layer_code', 'Layer.name_jp'),
            'conditions' => array(
                'Layer.flag' => 1,
                'Layer.layer_code' => $head_dept_id
            )
        ));
        return $head_dept;
    }

    #get ba name by ba code
    public function getBAName($ba_code)
    {
        $getBAName = $this->BusinessAreaModel->find('first', array(
            'fields' => array('ba_name_jp'),
            'conditions' => array('ba_code' => $ba_code, 'flag' => 1)
        ));
        return $getBAName['BusinessAreaModel']['ba_name_jp'];
    }

    #shortcut upload files into cloud
    public function ArchiveBackup()
    {

        $type_list = BackupFormInfo::TYPE_LIST;

        // In your controller
        // $this->loadModel('Queue.QueuedTask');
        // $this->QueuedTask->createJob('LongExample', 2 * MINUTE);
        // echo "This is Test Function!";

        try {
            $backup_tasks = $this->BrmBackupFile->find('all', array(
                'fields' => array('BrmBackupFile.id', 'BrmBackupFile.term_id', 'BrmBackupFile.file_type', 'BrmBackupFile.hlayer_code', 'BrmBackupFile.hlayer_name', 'BrmBackupFile.start_month', 'BrmBackupFile.end_month', 'Layer.name_jp', 'Layer.name_en', 'BrmTerm.term_name', 'BrmTerm.budget_year', 'BrmTerm.budget_end_year', 'BrmBackupFile.created_by'),
                'conditions' => array('status' => '0', 'BrmBackupFile.flag' => 1),
                'joins' => array(
                    array(
                        'table' => 'layers',
                        'alias' => 'Layer',
                        'type' => 'LEFT',
                        'conditions' => array(
                            'Layer.layer_code = BrmBackupFile.hlayer_code',
                            'Layer.flag' => 1
                        )
                    ),
                    array(
                        'table' => 'brm_terms',
                        'alias' => 'BrmTerm',
                        'type' => 'LEFT',
                        'conditions' => array(
                            'BrmTerm.id = BrmBackupFile.term_id',
                            'BrmTerm.flag != ' => 0
                        )
                    )
                ),
            ));
            $now = date("Y-m-d H:i:s");
            $wholepl_upload = true;
            $last_term = [];

            foreach ($backup_tasks as $backup_task) {
                #Merge subarrays
                $backup_task = call_user_func_array('array_merge', $backup_task);
                $user_id = $backup_task['created_by'];
                $id = $backup_task['id'];
                $term_id = $backup_task['term_id'];
                $file_type = $backup_task['file_type'];
                $type_name = $type_list[$file_type];
                $hlayer_code = $backup_task['hlayer_code'];
                $status = $backup_task['status'];
                $hlayer_name = $backup_task['hlayer_name'];
                $term_name = $backup_task['term_name'];
                $budget_term = $backup_task['budget_year'] . '~' . $backup_task['budget_end_year'];
                $start_month = $backup_task['start_month'];
                $end_month = $backup_task['end_month'];
                $functionName = "DownloadType_" . $file_type;

                if ($file_type == '01' && !in_array($term_id, $last_term)) {
                    $wholepl_upload = $this->DownloadWholePL($term_id, $budget_term, $user_id, $start_month, $end_month);
                    $last_term[] = $term_id;
                }
                $upload = $this->$functionName($term_id, $budget_term, $hlayer_code, $hlayer_name, $user_id, $start_month, $end_month);
                if ($upload == true || $wholepl_upload == true) {
                    $update_task = array(
                        'id' => $id,
                        'status' => 1,
                        'updated_date' => $now
                    );
                    $this->BrmBackupFile->save($update_task);
                }
            }

            Cache::clear();
            $current_time = parent::getCurrentTime();
            CakeLog::write('debug', 'Cron job finished at ' . $current_time . '.');
            // $this->redirect(array('controller'=>'BrmBackupFile', 'action'=>'index'));
            return true;
        } catch (Exception $e) {
            CakeLog::write('debug', 'Cron job failed at ' . $current_time . '. ' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            return false;
        }
    }

    #shortcut adding file url into tbl_cloud_files
    public function AddUrls()
    {
        try {
            $archive_folder = CloudStorageInfo::FOLDER_NAME;
            $cloud = parent::connect_to_google_cloud_storage();
            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
            $objects = $bucket->objects([
                'prefix' => $archive_folder . '/'
            ]);
            foreach ($objects as $object) {
                $info = $object->info();
                $url = $info['name']; #get file url
                $file_size = number_format($info['size'] / 1024, 1, '.', '') . 'KB'; #get file size
                $flag = 1;
                $time = new DateTime($info['updated']); #get file uploaded date
                $time->setTimezone(new DateTimeZone(Setting::TIMEZONE));
                $file_created_date = $time->format('Y-m-d H:i:s');
                $this->Save_Cloud_File_Path($url, $file_size, $file_created_date);
            }
        } catch (GoogleException $e) {
            CakeLog::write('error', 'Connection error...' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
        }
        $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
    }

    #shortcut rename file in cloud, trancate tbl_cloud_files, adding actual files url into tbl_cloud_files
    public function RenameFiles()
    {
        $hq_list = $this->getHeadDeptList();
        try {
            $archive_folder = CloudStorageInfo::FOLDER_NAME;
            $cloud = parent::connect_to_google_cloud_storage();
            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
            $objects = $bucket->objects([
                'prefix' => $archive_folder . '/'
            ]);
            foreach ($objects as $object) {
                $copiedObject = $object->copy($bucket, [
                    'name' => 'sumisho_backup_' . $archive_folder . '/' . $object->name()
                ]);
                $path_arr = explode('/', $object->name());
                $tmp_file = array_pop($path_arr);
                $full_path = implode('/', $path_arr) . '/';
                foreach ($hq_list as $each_hq) {
                    if (strpos($tmp_file, '_' . $each_hq) !== false) {
                        if (strpos($tmp_file, 'PLサマリー') === false && strpos($tmp_file, '総括表_') === false) {
                            $search = '/' . preg_quote('_' . $each_hq, '/') . '/';
                            $tmp_file = preg_replace($search, '', $tmp_file, 1);
                        }
                    }
                }
                $full_path = $full_path . $tmp_file;
                $rename_object = $object->rename($full_path);
            }
            $this->BrmCloudFile->query('TRUNCATE TABLE tbl_cloud_files;');
            $this->AddUrls();
        } catch (GoogleException $e) {
            CakeLog::write('error', 'Connection error...' . $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
        }
        $this->redirect(array('controller' => 'BrmBackupFile', 'action' => 'index'));
    }

    public function downloadCache($term_id, $head_dept_id, $budget_layer_code, $budget_term, $login_id)
    {
        $PL = new BrmPLSummaryController();
        $Common = new CommonController();
        $BudgetSummary = new BrmBudgetSummaryController();

        $dept_id = $this->Layer->find('all', array(
            'fields' => 'parent_id',
            'conditions' => array('Layer.flag' => 1, 'Layer.layer_code' => $budget_layer_code)
        ));
        $deptId = json_decode($dept_id[0]['Layer']['parent_id'], true);

        $budget_year = explode('~', $budget_term);
        $start_year     = $budget_year[0];
        $years          = range($budget_year[0], $budget_year[1]);

        $calculateAmounts = $BudgetSummary->getBudgetSummary($term_id, $head_dept_id, $deptId['L2'], $budget_layer_code, 'PL', $start_year, $budget_year[1]);
        $result_arr = $calculateAmounts;
        $freeze_arr = $PL->getFreezeArray($term_id, $start_year);
        $Month_12 = $Common->get12Month($term_id);
        $head_name = $this->Session->read('HEAD_DEPT_NAME');
        $cache_name = 'pl_' . $term_id . '_' . $head_dept_id . '_' . $login_id;
        $cache_data = array(
            'result_arr' => $result_arr,
            'freeze_arr' => $freeze_arr,
            'month_12' => $Month_12,
            'head_name' => $head_name,
            'budget_layer_code' => $budget_layer_code,
            'years' => $years,
        );
        Cache::write($cache_name, $cache_data);
    }
}
