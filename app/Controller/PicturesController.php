<?php

ob_get_contents();// to clear POST Content length error when file upload
ob_end_clean();// to clear POST Content length error when file upload
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * @author 			Nu Nu Lwin
 * @start_date 	16.Jul.2019
 * @finished_date 24.Jul.2019
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
    
class PicturesController extends AppController
{
    public $uses = array('Picture','Asset');
    public $helpers = array('Html', 'Form', 'Session');
    public $components = array('Session', 'Flash', 'Paginator');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkAccessType();
        /** can access admin level 1,2,3,4 **/
        if (($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) &&
                ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ACCOUNT_MANAGER) &&
                ($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ACCOUNT_SECTION_MANAGER)&&($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ACCOUNT_INCHARGE)) {
            $this->redirect(array('controller' => 'Login', 'action' => 'logout'));
        }
    }

    /**
     *
     * index method
     * @author Aye Zar Ni Kyaw
     *
     */
    public function index()
    {
        $this->layout = 'fixedassets';
        
        if ($this->Session->check('STATUS')) {
            $this->Session->delete('STATUS');
        }
        if ($this->Session->check('ASSETS_NO')) {
            $this->Session->delete('ASSETS_NO');
        }
        if ($this->Session->check('CHECK_IMG')) {
            $this->Session->delete('CHECK_IMG');
        }
        //picture table of data
        try {
            $event_id = $this->Session->read('EVENT_ID');
            //fixedAssets table of data
            $asset_no = $this->Asset->find('all', array(
                                            'conditions' => array('Asset.flag >=' => 1),
                                            'fields' => 'asset_no'));

            //images data get from cloud strogae path
            $cloud = parent::connect_to_google_cloud_storage();

            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
        
            $asset_no_form  = $this->Session->read('ASSETS_NO');
            $status = $this->Session->read('STATUS');
            
            $search_data = array(
                    'asset_no_form' => $asset_no_form,
                    'status' => $status
            );

            $this->__preparePaginate('index', '', '', '');

            $picturelist = $this->Paginator->paginate('Picture');

            if (!empty($picturelist)) {
                $count = count($picturelist);
                $page = $this->params['paging']['Picture']['page'];

                $this->Session->write('Page.pic_list_page', $page);
                $limit = $this->params['paging']['Picture']['limit'];
                $no = ($page-1) * $limit + 1;
                
                for ($i=0; $i<$count; $i++) {
                    $picturelist[$i]['pictures']['url'] = $this->webroot."img/no_image.png";
                    if ($picturelist[$i]['pictures']['file_path'])
                    {
                        $object = $bucket->object($picturelist[$i]['pictures']['file_path']);
                        $objectName = $object->name();
                        $url = self::get_object_v4_signed_url($objectName);
                    
                        //url in image exit or not exit
                        if ($object->exists()) {
                            $picturelist[$i]['pictures']['url'] = self::get_object_v4_signed_url($objectName);
                        }
                    
                    }

                    //number of value
                    $picturelist[$i]['pictures']['number'] = $no;
                    $no++;
                }
                // debug($picturelist);exit;
                //count
                $query_count = $this->params['paging']['Picture']['count'];
                $count = parent::getSuccessMsg('SS004', $query_count);
                $this->set(compact('picturelist', 'asset_no', 'count', 'search_data', 'query_count'));
            } else {
                $no_data = parent::getErrorMsg("SE001");
                $this->set('no_data', $no_data);
                $this->set('search_data', $search_data);
                $this->render('index');
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            $this->render('index');
        }
        $this->render('index');
    }

    public function __preparePaginate($from, $asset_no_form, $status, $check_img)
    {
        $condition = [];
        $string_condition = '';

        if ($from == 'search') {
            
            if ($status == 2) { //not exist
                $condition[] = 'asset_no IS NULL';
            } elseif ($status ==1) { //exist
                $condition[] = 'asset_no IS NOT NULL';
            }

            if ($check_img == 2) { //not exist
                $condition[] = 'picture_name IS NULL';
            } elseif ($check_img == 1) { //exist
                $condition[] = 'picture_name IS NOT NULL';
            }

            if ($asset_no_form != null || $asset_no_form !='') {
                $condition[] =  "asset_no LIKE '%" . $asset_no_form . "%'";
            }

            $string_condition = implode(' AND ', $condition);
        }
        

        $this->Picture->recursive = 0;//need for custom pagination
        $this->paginate = array(
            'Picture' => array(
                'limit' => 20,
                'conditions' => $string_condition,
            )
        );
        
    }

    /**
    * Generate a v4 signed URL for downloading an object.
    *
    * @param string $bucketName the name of your Google Cloud bucket.
    * @param string $objectName the name of your Google Cloud object.
    * @author Aye Zar Ni Kyaw
    *
    * @return void
    */
    public function get_object_v4_signed_url($objectName)
    {
        $cloud = parent::connect_to_google_cloud_storage();
        $storage = $cloud[0];
        $bucketName = $cloud[1];
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->object($objectName);
        $url = $object->signedUrl(
            # This URL is valid for 15 minutes
            new \DateTime('1 min'),
            [
                'version' => 'v4'
            ]
        );
        return $url;
    }
    
    /**
     *
     * search function for Picture list by asset_no,status(exist or not exist)
     * @param - status /asset_no
     * @author Aye Thandar Lwin
     */
    public function search_pictureList()
    {
        $this->layout = 'fixedassets';
        try {
            if ($this->request->is('post')) {
                $asset_no_form = h($this->request->data('asset_no'));
                $asset_no_form = str_replace(array('\\', '_', '%'), array('\\\\', '\_', '\%'), $asset_no_form);
                $status = $this->request->data('status');
                $check_img = $this->request->data("check_img");

                $this->Session->write('ASSETS_NO', $asset_no_form);
                $this->Session->write('STATUS', $status);
                $this->Session->write('CHECK_IMG', $check_img);
            }
            if ($this->request->is('get')) {
                $asset_no_form = $this->Session->read('ASSETS_NO');
                $status= $this->Session->read('STATUS');
                $check_img = $this->Session->read('CHECK_IMG');
            }
        
            $search_data = array(
                'asset_no_form' => $asset_no_form,
                'status' => $status,
                'check_img' => $check_img
            );

            $asset_no = $this->Asset->find(
                'all',
                array(
                    'conditions' => array('Asset.flag >=' => 1),
                    'fields' => 'asset_no'
                )
            );

            $cloud = parent::connect_to_google_cloud_storage();
            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
           
            $this->__preparePaginate('search', $asset_no_form, $status, $check_img);

            $picturelist = $this->Paginator->paginate('Picture');

            if (!empty($picturelist)) {
                $count = count($picturelist);
                $page = $this->params['paging']['Picture']['page'];
                
                $this->Session->write('Page.pic_list_page', $page);
                $limit = $this->params['paging']['Picture']['limit'];
                
                $no = ($page-1) * $limit + 1;

                for ($i=0; $i<$count; $i++) {
                    $picturelist[$i]['pictures']['url'] = $this->webroot."img/no_image.png";
                    if ($picturelist[$i]['pictures']['file_path'])
                    {
                        $object = $bucket->object($picturelist[$i]['pictures']['file_path']);
                        $objectName = $object->name();
                        $url = self::get_object_v4_signed_url($objectName);
                    
                        //url in image exit or not exit
                        if ($object->exists()) {
                            $picturelist[$i]['pictures']['url'] = self::get_object_v4_signed_url($objectName);
                        }
                    
                    }

                    //number of value
                    $picturelist[$i]['pictures']['number'] = $no;
                    $no++;
                }
                
                //count
                $query_count = $this->params['paging']['Picture']['count'];
               
                $count = parent::getSuccessMsg('SS004', $query_count);
                $this->set(compact('picturelist', 'asset_no', 'count'));
                $this->set('search_data', $search_data);
                $this->set('query_count', $query_count);
                $this->render('index');
            } else {
                $no_data = parent::getErrorMsg("SE001");
                $this->set('no_data', $no_data);
                $this->set('search_data', $search_data);
                $this->render('index');
            }
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            // $this->redirect(array('controller' => 'Pictures', 'action' => 'search_pictureList'));
        }
    }
    
    /**
     *
     * delete method
     * @author Aye Zar Ni Kyaw
     * Modify By Thura Moe (03/10/2019)
     *
    */
    public function delete()
    {
        if ($this->request->is('post')) {
            // debug($this->request->data);exit;
            $picture_id = $this->request->data['picture_id'];
            $updated_by = $this->Session->read('LOGIN_ID');
            $updated_date = date("Y-m-d H:i:s");
            
            $pageNo =  $this->request->data['hid_page_no'];
            
            $chk_picture_exist = $this->Picture->find(
                'all',
                array(
                    'conditions' => array(
                        'Picture.id' => $picture_id,
                        'Picture.flag' => 1
                    ),
                    'fields' => array('file_path')
                )
            );
           
            $cnt_exist = count($chk_picture_exist);
            if ($cnt_exist > 0) {
                $db = $this->Picture->getDataSource();
                try {
                    $db->begin();
                    $url = $chk_picture_exist[0]['Picture']['file_path'];
                    //$result = $this->Picture->DeletedDataUpdate($picture_id);
                    $result = $this->Picture->delete($picture_id);
                    if ($result == false) {
                        throw new GoogleException("Picture id:{$picture_id} is not deleted", 1);
                    } else {
                        //delete cloud of image
                        $cloud = parent::connect_to_google_cloud_storage();
                        $storage = $cloud[0];
                        $bucketName = $cloud[1];
                        $bucket = $storage->bucket($bucketName);
                        $object = $bucket->object($url);
                        if ($object->exists()) {
                            $object->delete();
                        }
                    }
                    $successMsg = parent::getSuccessMsg('SS003');
                    $this->Flash->set($successMsg, array("key"=>"PicturesSuccess"));
                    $db->commit();
                } catch (GoogleException $e) {
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $db->rollback();
                    $msg = parent::getErrorMsg("SE007");
                    $this->Flash->set($msg, array("key"=>"PicturesDeleteFail"));
                }
            } else {
                $msg = parent::getErrorMsg("SE047");
                $this->Flash->set($msg, array("key"=>"PicturesDeleteFail"));
            }
        }
        $chk_page = $this->Session->check('Page.pic_list_page');
        $chk_asset = $this->Session->check('ASSETS_NO');
        $chk_status = $this->Session->check('STATUS');
        if ($chk_page) {
            $session_page = $this->Session->read('Page.pic_list_page');
        } else {
            $session_page = 1;
        }

        # recalculating page no
        if ($chk_asset || $chk_status) {
            $action = 'search_pictureList';
            $asset_no_form = $this->Session->read('ASSETS_NO');
            $status = $this->Session->read('STATUS');
            $check_img = $this->Session->read('CHECK_IMG');
            $this->__preparePaginate('search', $asset_no_form, $status, $check_img);
        } else {
            $action = 'index';
            $this->__preparePaginate('index', '', '', '');
        }
        $this->Paginator->paginate('Picture');
        $total_record = $this->params['paging']['Picture']['count'];
        if ($total_record > 0) {
            $limit = 20; // becz 20 rows per page
            $recalculate_page = ceil(($total_record)/$limit);
        } else {
            $recalculate_page = $total_record;
        }
        if ($session_page < $recalculate_page) {
            $page = $session_page;
        } else {
            $page = $recalculate_page;
        }
        return $this->redirect(array('controller' => 'Pictures', 'action' => $action, 'page' => $page));
    }

    public function add($errmessage = null)
    {
        $errorMsg   = "";
        $successMsg = "";
        $this->layout = 'fixedassets';

        if ($this->Session->check('PictureSize0')) {
            $reviseInfo = $this->Session->read('PictureSize0');
            $this->Session->delete('PictureSize0');
            $this->set('PictureSize0', 'PictureSize0');
        }

        $this->set('successMsg', $successMsg);
        $this->set('errorMsg', $errorMsg);
        $this->render('add');
    }

    public function SavePicture()
    {
        if ($this->request->is('post')) {
            $errorMsg   = "";
            $successMsg = "";
            $login_id = $this->Session->read('LOGIN_ID');
            $role_id = $this->Session->read('ADMIN_LEVEL_ID');
            $file = $this->request->params['form']['multiPicUpload'];
            $removeName = $this->request->data["remove-id-hide"];
            
            $saveCount = "0"; // to know how many save in db.
            
            $PicNameArr = array();

            if (empty($file) && empty($_POST) &&
                isset($_SERVER['REQUEST_METHOD']) &&
                strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
                $errorMsg = parent::getErrorMsg('SE015');
                $this->Flash->set($errorMsg, array('key'=>'picError'));
                $this->redirect(array('controller'=>'Pictures','action'=>'add'));
            } else {
                $allowedTypes = array('gif','png' ,'jpg','jpeg','JPEG','JPG','PNG','GIF');
                $pic_data = array();
                $totalFileSize = 0;
                $sizeLessPic = array();
                $date = date('Y-m-d H:i:s');

                //change file name with zero eg: "123" => "0000000123"
                $K=0;
                foreach ($file['name'] as $value) {
                    $pic_name = pathinfo($value, PATHINFO_FILENAME);
                    $ext = pathinfo($value, PATHINFO_EXTENSION);
                    $new_pic_name = str_pad($pic_name, 10, "0", STR_PAD_LEFT).".".$ext;
                    
                    $file['name'][$K]=$new_pic_name;
                    $K++;
                }
                
                /***
                 *** if remove link click, store hidden field in remove Name.
                 *** And then remove form file's value when same remove's Name and file's Name.
                ***/

                if (!empty($removeName)) {
                    $arrayRmName = explode(',', $removeName);	//The explode() function breaks a string into an array.
                    
                    $rmvlength = count($arrayRmName);
                    
                    for ($x = 0; $x < $rmvlength; $x++) {

                        //$rmv = ltrim($arrayRmName[$x],'0'); //get remove name;
                        $rmv = $arrayRmName[$x];

                        foreach ($file as $key => $value) {
                            if (in_array($rmv, $file['name'])) {
                                $arrKey = array_keys($file['name'], $rmv);
                                // array_keys => If the optional search_key_value is specified, then only the keys for that value are returned. Otherwise, all the keys from the array are returned.
                            }

                            foreach ($value as $val => $v) {
                                if (in_array($val, $arrKey)) {
                                    unset($value[$val]); //unset — Unset a given variable
                                }
                                $value = array_values($value); //rearranged in file of value
                                $file[$key] = $value;
                            }
                        }
                    }
                }
                
                foreach ($file['name'] as $key => $name) {
                    //Check type, allow 'gif','png' ,'jpg'
                    $ext = pathinfo($name, PATHINFO_EXTENSION);

                    if (!in_array($ext, $allowedTypes)) {
                        $errorMsg = parent::getErrorMsg("SE013", $ext);
                        $this->Flash->set($errorMsg, array('key'=>'picError'));
                            
                        $this->redirect(array('controller'=>'Pictures','action'=>'add'));
                    }
                }
         
                foreach ($file['size'] as $key => $size) {
                    //Check Size 10M,
                        
                    if ($size <= '1024') { //check size on dish '0'

                        $sizeLost = $file['name'][$key];
                        array_push($sizeLessPic, $sizeLost);
                    } else {
                        $totalFileSize += $size;
                    }
                }
            

                if ($totalFileSize >= '10485760') {
                    $errorMsg = parent::getErrorMsg('SE014');
                    $this->Flash->set($errorMsg, array('key'=>'picError'));
                    $this->redirect(array('controller'=>'Pictures','action'=>'add'));
                } else {
                    $countFile = '0'; // overwrite name count
                    $toUploadCloud = array();
                    $picDB = $this->Picture->getDataSource(); //for try catch
                    
                    foreach ($file['name'] as $key => $name) {
                        if (!in_array($name, $sizeLessPic)) {
                            $pic_ext  = pathinfo($name, PATHINFO_EXTENSION);
                            $pic_name = pathinfo($name, PATHINFO_FILENAME);
                            //$pic_name = str_pad($pic_name, 10, '0', STR_PAD_LEFT);
                            
                            $countFile++;
                            //pic is already exist or not check because if exist, remove on cloud and update in db
                            $getPicName = $this->Picture->find('all', array(
                                        'conditions' => array('Picture.picture_name' => $pic_name,
                                        'Picture.flag !='=> '0'),
                                        'limit'=>'1'
                                    ));
                            
                            if (!empty($getPicName)) {
                                try {
                                    $picDB->begin();

                                    foreach ($getPicName as $value) {
                                        $duplicateId = $value['Picture']['picture_id'];
                                        $url = $value['Picture']['file_path'];
                                        
                                        // $pic_name     = $picDB->value("Pictures/".$pic_name.".".$pic_ext,'string');
                                        $pic_name     = $picDB->value("PicturesTest/".$pic_name.".".$pic_ext, 'string');
                                        //Pictures
                                        
                                        $pic_ext      = $picDB->value($pic_ext, 'string');
                                        $updated_by   = $picDB->value($login_id, 'string');
                                        $updated_date = $picDB->value($date, 'string');

                                        $saveCount++;
                                        $updatePicture = $this->Picture->updateAll(
                                            array("Picture.picture_type"=> $pic_ext,
                                                       "Picture.file_path" => $pic_name,
                                                         "Picture.updated_date"=> $updated_date,
                                                        "Picture.updated_by"=> $updated_by),
                                            array("Picture.picture_id"=>$duplicateId)
                                        );
                                        if ($updatePicture) {
                                            $deleteOnCloud = $this->__delete_object_to_cloud($url);
                                        }
                                    }

                                    $picDB->commit();
                                } catch (Exception $e) {
                                    $picDB->rollback();

                                    $errorMsg = parent::getErrorMsg('SE051');
                                    $this->Flash->set($errorMsg, array('key'=>'picError'));
                                    
                                    CakeLog::write('debug', 'Pic already save another user and login_id or Already exist picture name and type in db. Error occur (update in db or delete pic on cloud). login_id ' .$login_id. " , " .$e. 'In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                                    $this->redirect(array('controller'=>'Pictures','action'=>'add'));
                                }
                            } else {
                                $saveCount++;
                                //prepare to save
                                $pic_data[] = array("picture_name" => "$pic_name",
                                                    "picture_type" => "$pic_ext",
                                                    "file_path" => "PicturesTest/"."$name",
                                                    // "file_path" => "Pictures/"."$name",
                                                    "Picture.flag" => "1",
                                                    "created_by" => "$login_id",
                                                    "updated_by" => "$login_id",
                                                    "created_date" => "$date",
                                                    "updated_date" => "$date");
                            }
                        }
                    }
                     
                    $saveAllData = "0";
                    if (!empty($pic_data)) {
                        try {
                            $picDB->begin();
                            
                            $saveAllData = $this->Picture->saveAll($pic_data);
                                
                            $picDB->commit();
                        } catch (Exception $e) {
                            $picDB->rollback();
                            $errorMsg = parent::getErrorMsg('SE051');
                            $this->Flash->set($errorMsg, array('key'=>'picError'));

                            CakeLog::write('debug', 'Pic already save another user and login_id ' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                            $this->redirect(array('controller' => 'Pictures', 'action' => 'add'));
                        }
                    }

                    if ($countFile != "0" || $saveAllData != "0") {
                        for ($i=0; $i < $countFile; $i++) {
                            $fileName = $file['name'][$i];
                            //$fileName = str_pad($fileName, 10, '0', STR_PAD_LEFT);
                            
                            $filePath = $file['tmp_name'][$i];
                            $fileType = $file['type'][$i];
                            $uploadFolderPath = "PicturesTest".'/';
                            // $uploadFolderPath = "Pictures".'/';


                            if (!in_array($fileName, $sizeLessPic)) {
                                $resizePic = $this->resizeImage($filePath, $fileType);
                                
                                $isUpload = $this->__upload_object_to_cloud($fileName, $filePath, $uploadFolderPath);
                            }
                        }
                    }
                }

                if (!empty($sizeLessPic)) {
                    //if have size on dish 0
                    $this->Session->write('PictureSize0', 'PictureSize0');
                    $this->Session->write('SIZEZEROERROR', $sizeLessPic);
                }
                if ($saveCount != '0') {
                    $successMsg = parent::getSuccessMsg('SS015', $saveCount);
                    $this->Flash->set($successMsg, array('key'=>'picSuccess'));

                    CakeLog::write('debug', 'save '.$saveCount. ' pic and login_id ' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                }
                
                $this->redirect(array('controller' => 'Pictures', 'action' => 'add'));
            }
        } else {
            $this->redirect(array('controller' => 'Pictures', 'action' => 'add'));
        }
    }

    public function __upload_object_to_cloud($objectName, $source, $folderStructure)
    {
        $cloud = parent::connect_to_google_cloud_storage();
        
        $storage = $cloud[0];
        
        $bucketName = $cloud[1];
        
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        
        try {
            $object = $bucket->upload($file, [
                'name' => $folderStructure.$objectName
            ]);
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'picture upload error on cloud '.$e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function __delete_object_to_cloud($url)
    {
        $cloud = parent::connect_to_google_cloud_storage();
        $storage = $cloud[0];
        $bucketName = $cloud[1];
        $bucket = $storage->bucket($bucketName);

        try {
            $object = $bucket->object($url);
            if ($object->exists()) {
                $object->delete();
            }
        } catch (GoogleException $e) {
            CakeLog::write('debug', 'picture delete error on cloud =>'.$e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function resizeImage($path, $type)
    {
        try {
            list($width, $height) = getimagesize($path);

            $new_width = $new_height = 100;

            switch ($type) {
                //@ is to avoid warning messages (JPEG library reports unrecoverable error))
                case 'image/jpeg':
                    $original = @imagecreatefromjpeg($path);
                    $thumb    = @imagecreatetruecolor($new_width, $new_height);
                    @imagecopyresampled($thumb, $original, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    @imagejpeg($thumb, $path, 70);
                    break;

                case 'image/gif':
                    $original = @imagecreatefromgif($path);
                    $thumb    = @imagecreatetruecolor($new_width, $new_height);
                    @imagecopyresampled($thumb, $original, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    @imagegif($thumb, $path, 70);
                    break;

                case 'image/png':
                    $original = @imagecreatefrompng($path);
                    $thumb    = @imagecreatetruecolor($new_width, $new_height);
                    @imagecopyresampled($thumb, $original, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    @imagepng($thumb, $path);
                    break;
            }

            @imagedestroy($thumb);
            @imagedestroy($original);
        } catch (Exception $e) {
            CakeLog::write('debug', 'resizeImage error =>'.$e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function OverwritePictureConfirm()
    {
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $pName = json_decode($this->request->data['myJSONString'], true);

        $PicNameArr = array();
        $i = '-1';
        $iArray = array();
        
        foreach ($pName as $value) {
            $i++;
            $pic_name = pathinfo($value, PATHINFO_FILENAME);
            $pic_ext  = pathinfo($value, PATHINFO_EXTENSION);
            
            $getPicName = $this->Picture->find('all', array(
                        'conditions' => array('Picture.picture_name' => $pic_name,
                        'Picture.flag'=> '1'),
                        'limit'=>'1'
                    ));
            
            if (!empty($getPicName)) {
                foreach ($getPicName as $value) {
                    $duplicateName = $value['Picture']['picture_name'];
                    //$duplicateType = $value['Picture']['picture_type'];
                    $duplicate = $duplicateName.".".$pic_ext;
                    array_push($PicNameArr, $duplicate);
                    array_push($iArray, $i);
                }
            }
        }
        
        if (!empty($PicNameArr)) {
            $data = array(
                        'content' => $PicNameArr,
                        'content1'=> $iArray,
                        'invalid' => "",
                        'error'   => ""
                    );
            return json_encode($data);
        //echo json_encode($PicNameArr);
        } else {
            $data = array(
                        'content' => "empty",
                        'invalid' => "",
                        'error'   => ""
                    );
            return json_encode($data);
        }
    }

    public function SizeZeroError()
    {
        // $this->request->allowMethod('ajax');
        // $this->autoRender = false;
        #only allow ajax request
        parent::checkAjaxRequest($this);
        $this->layout = false;

        $SizeError = $this->Session->read('SIZEZEROERROR');
        $this->Session->delete('SIZEZEROERROR');
        echo json_encode($SizeError);
    }
}
