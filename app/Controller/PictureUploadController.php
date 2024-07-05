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
	
class PictureUploadController extends AppController {
	
	public $uses = array('Picture');

	public function beforeFilter() {
		
		parent::CheckSession();
		parent::checkUserStatus();
		parent::checkAccessType();
		/** can access admin level 1,2,3,4 **/
		if(($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ADMIN) &&
				($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ACCOUNT_MANAGER) &&
				($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ACCOUNT_SECTION_MANAGER)&&($this->Session->read('ADMIN_LEVEL_ID') != AdminLevel::ACCOUNT_INCHARGE)){
	
			$this->redirect(array('controller' => 'Login', 'action' => 'logout'));
	
		}
	}

	public function index($errmessage = null){	

		$errorMsg   = "";
		$successMsg = "";
		$this->layout = 'fixedassets';

		if ($this->Session->check('PictureSize0')){
			$reviseInfo = $this->Session->read('PictureSize0');	
			$this->Session->delete('PictureSize0');
			$this->set('PictureSize0', 'PictureSize0');	
		}	

		$this->set('successMsg', $successMsg);
		$this->set('errorMsg', $errorMsg);
		$this->render('index');
	}

	//multiple upload img
	public function SavePicture(){

		if($this->request->is('post')) {
			
			$errorMsg   = "";
			$successMsg = "";
			$login_id = $this->Session->read('LOGIN_ID');
			$admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
			$file = $this->request->params['form']['multiPicUpload'];
			$removeName = $this->request->data["remove-id-hide"];
			$page_name = $this->request->params['controller'];
			$saveCount = "0"; // to know how many save in db.

			$PicNameArr = array();

			if (empty($file) && empty($_POST) &&
        		isset($_SERVER['REQUEST_METHOD']) &&
        		strtolower($_SERVER['REQUEST_METHOD']) == 'post') {

				$errorMsg = parent::getErrorMsg('SE015');
				$this->Flash->set($errorMsg,array('key'=>'picError'));
				$this->redirect(array('controller'=>'Pictures','action'=>'index'));

			}else{
				
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
					$new_pic_name = str_pad($pic_name,10,"0",STR_PAD_LEFT).".".$ext;
					
					$file['name'][$K]=$new_pic_name;
					$K++;
				}
				
				/*** 
				 *** if remove link click, store hidden field in remove Name.
				 *** And then remove form file's value when same remove's Name and file's Name. 
				***/

				if(!empty($removeName)){
					
					$arrayRmName = explode(',', $removeName);	//The explode() function breaks a string into an array.
					
					$rmvlength = count($arrayRmName);
					
					for($x = 0; $x < $rmvlength; $x++) {

						//$rmv = ltrim($arrayRmName[$x],'0'); //get remove name;
						$rmv = $arrayRmName[$x];

						foreach ($file as $key => $value) {
								
							if(in_array($rmv, $file['name'])){
								
								$arrKey = array_keys($file['name'],$rmv);
								// array_keys => If the optional search_key_value is specified, then only the keys for that value are returned. Otherwise, all the keys from the array are returned.
								
							}

							foreach ($value as $val => $v) {
								
								if(in_array($val, $arrKey)){
								
									unset($value[$val]); //unset — Unset a given variable
								}
								$value = array_values($value); //rearranged in file of value
								$file[$key] = $value;
							}
						}		
						
					}

				}
				
			   	foreach($file['name'] as $key => $name ){
			 		//Check type, allow 'gif','png' ,'jpg'
			 		$ext = pathinfo($name, PATHINFO_EXTENSION);

			   		if(!in_array($ext, $allowedTypes)){  
							
							$errorMsg = parent::getErrorMsg("SE013",$ext);
							$this->Flash->set($errorMsg,array('key'=>'picError'));
							
							$this->redirect(array('controller'=>'Pictures','action'=>'index'));

				 	 	}

			   	}
		 
			   	foreach ($file['size'] as $key => $size) {
			  		//Check Size 10M, 
			  			
		  			if($size <= '1024'){ //check size on dish '0'

		  				$sizeLost = $file['name'][$key];
		  				array_push($sizeLessPic,$sizeLost);
		  			}else{
		  				$totalFileSize += $size;
		  			}
			  						  	
			   	}
		   	

			   	if($totalFileSize >= '10485760'){
			  			
						$errorMsg = parent::getErrorMsg('SE014');
						$this->Flash->set($errorMsg,array('key'=>'picError'));
						$this->redirect(array('controller'=>'Pictures','action'=>'index'));
			  
			   	}else{

				  	$countFile = '0'; // overwrite name count
				  	$toUploadCloud = array();
				  	$picDB = $this->Picture->getDataSource(); //for try catch
				  	
				  	foreach ($file['name'] as $key => $name) {
				  		
				  		if(!in_array($name, $sizeLessPic)){

				  			$pic_ext  = pathinfo($name, PATHINFO_EXTENSION);
						  	$pic_name = pathinfo($name, PATHINFO_FILENAME); 
						  	//$pic_name = str_pad($pic_name, 10, '0', STR_PAD_LEFT);
						  	
						  	$countFile++;
						  	//pic is already exist or not check because if exist, remove on cloud and update in db
					  		$getPicName = $this->Picture->find('all',array(
										'conditions' => array('Picture.picture_name' => $pic_name,
										'Picture.flag !='=> '0'),
										'limit'=>'1'
									));
					  		
					  		if(!empty($getPicName)){

					  			try{
									$picDB->begin();

						  			foreach ($getPicName as $value) {

						  				$duplicateId = $value['Picture']['picture_id'];
						  				$url = $value['Picture']['file_path'];
						  				
						  				// $pic_name     = $picDB->value("PictureUpload/".$pic_name.".".$pic_ext,'string');
						  				$pic_name     = $picDB->value("PictureUploadTest/".$pic_name.".".$pic_ext,'string');
						  				//PictureUpload
						  				
						  				$pic_ext      = $picDB->value($pic_ext,'string');
						  				$updated_by   = $picDB->value($login_id,'string');
										$updated_date = $picDB->value($date,'string');

										$saveCount++;
								 		$updatePicture = $this->Picture->updateAll(
												array("Picture.picture_type"=> $pic_ext,
													   "Picture.file_path" => $pic_name,
														 "Picture.updated_date"=> $updated_date,
													    "Picture.updated_by"=> $updated_by),
												array("Picture.picture_id"=>$duplicateId)
										);
								 		if($updatePicture){

								 			$deleteOnCloud = $this->__delete_object_to_cloud($url);
								 		}

						  			}

						  			$picDB->commit();

					  			}catch(Exception $e){
					  				$picDB->rollback();

					  				$errorMsg = parent::getErrorMsg('SE051');
									$this->Flash->set($errorMsg,array('key'=>'picError'));
									
									CakeLog::write('debug', 'Pic already save another user and login_id or Already exist picture name and type in db. Error occur (update in db or delete pic on cloud). login_id ' .$login_id. " , " .$e. 'In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
									$this->redirect(array('controller'=>'Pictures','action'=>'index'));

					  			}
					  			
					  		}else{
					  			$saveCount++;
					  			//prepare to save
					  			$pic_data[] = array("picture_name" 	=> "$pic_name",
			  										"picture_type" 	=> "$pic_ext",
			  										"file_path" 	=> CloudStorageInfo::FOLDER_NAME.'/'.$page_name.'/'."$name",
			  										// "file_path" => "PictureUpload/"."$name",
			  									    "flag" 			=> "1",
			  										"created_by" 	=> "$login_id",
			  										"updated_by" 	=> "$login_id",
			  									 	"created_date" 	=> "$date",
			  									    "updated_date" 	=> "$date");

					  		}
				  		}

					}
					 
					$saveAllData = "0";
				 	if(!empty($pic_data)){
				 		
				 		try{

							$picDB->begin();
							
							$saveAllData = $this->Picture->saveAll($pic_data);				
								
							$picDB->commit();

						}catch (Exception $e){
							$picDB->rollback();
							$errorMsg = parent::getErrorMsg('SE051');
							$this->Flash->set($errorMsg,array('key'=>'picError'));

							CakeLog::write('debug', 'Pic already save another user and login_id ' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
							$this->redirect(array('controller' => 'Pictures', 'action' => 'index'));
						}

				 	}

				 	if($countFile != "0" || $saveAllData != "0"){ 
		  		
					  	for ($i=0; $i < $countFile; $i++) { 

					  		$fileName = $file['name'][$i];
					  		//$fileName = str_pad($fileName, 10, '0', STR_PAD_LEFT);
					  		
					  		$filePath = $file['tmp_name'][$i];
					  		$fileType = $file['type'][$i];
							

							$uploadFolderPath = CloudStorageInfo::FOLDER_NAME.'/'.$page_name.'/';
							  
					  		//$uploadFolderPath = "PictureUploadTest".'/';
					  		// $uploadFolderPath = "PictureUpload".'/';


					  		if(!in_array($fileName, $sizeLessPic)){
				
						  		$resizePic = $this->resizeImage($filePath, $fileType); 
						  		
						  		$isUpload = $this->__upload_object_to_cloud($fileName, $filePath, $uploadFolderPath);
							}
					  	}

				  	}	

				}

				if(!empty($sizeLessPic)){
					//if have size on dish 0 
					$this->Session->write('PictureSize0','PictureSize0');
					$this->Session->write('SIZEZEROERROR',$sizeLessPic);

				}
				if($saveCount != '0'){

					$successMsg = parent::getSuccessMsg('SS015',$saveCount);
					$this->Flash->set($successMsg,array('key'=>'picSuccess'));

					CakeLog::write('debug', 'save '.$saveCount. ' pic and login_id ' .$login_id. ' In file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

				}
				
				$this->redirect(array('controller' => 'Pictures', 'action' => 'index'));

			}
			
			
		}else{
			$this->redirect(array('controller' => 'Pictures', 'action' => 'index'));
		}
		
	}

	//single upload img
	public function upload_img(){
		$errorMsg   = "";
		$successMsg = "";

		if($this->request->is('post')){
			$login_id = $this->Session->read('LOGIN_ID');
			$admin_level_id = $this->Session->read('ADMIN_LEVEL_ID');
			$file = $this->request->params['form']['img'];
			$asset_no = $this->request->data['hid_asset_no'];
			$page_name = $this->request->params['controller'];

			//upload img to cloud
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
			$file_name = $asset_no . '.' . $ext;//change image name to asset number name
			$file_path = $file['tmp_name'];
			$file_type = $file['type'];

			$uploadFolderPath = CloudStorageInfo::FOLDER_NAME.'/'.$page_name.'/';
			
			$resizePic = $this->resizeImage($file_path, $file_type); 
			//nothing return from upload object to cloud function
			$isUpload = $this->__upload_object_to_cloud($file_name, $file_path, $uploadFolderPath);
			
			$PICTURE = [
				'picture_name' => $asset_no,
				'picture_type' => $ext,
				'file_path' => CloudStorageInfo::FOLDER_NAME.'/'.$page_name.'/'."$file_name",
				'flag' => 1,
				'created_by' => $login_id,
				'updated_by' => $login_id,
				'created_date' => date('Y-m-d H:i:s'),
				'updated_date' => date('Y-m-d H:i:s')
			];
			
			if($this->Picture->save($PICTURE)){
				$successMsg = parent::getSuccessMsg('SS001');
				$this->Flash->set($successMsg,array('key'=>'picSuccess'));
				$this->redirect(array('controller' => 'Pictures', 'action' => 'index'));
			}
		}
	}

	public function __upload_object_to_cloud($objectName, $source, $folderStructure) {
		
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

	public function __delete_object_to_cloud($url){
		
		$cloud = parent::connect_to_google_cloud_storage();
		$storage = $cloud[0];
		$bucketName = $cloud[1];
		$bucket = $storage->bucket($bucketName);

		try {

			$object = $bucket->object($url);
			if($object->exists()) {
				$object->delete();
			}

		} catch (GoogleException $e) {
			CakeLog::write('debug', 'picture delete error on cloud =>'.$e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
			throw new Exception($e->getMessage(), 1);
		}
	}

	public function resizeImage($path, $type) {

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

	public function OverwritePictureConfirm(){

		$this->request->allowMethod('ajax');
		$this->autoRender = false;
		$this->layout = false;

		$pName = json_decode($this->request->data['myJSONString'], TRUE);

		$PicNameArr = array();
		$i = '-1';
		$iArray = array();
		
		foreach ($pName as $value) {
			
			$i++;
		  	$pic_name = pathinfo($value, PATHINFO_FILENAME); 
		  	$pic_ext  = pathinfo($value, PATHINFO_EXTENSION);
		  	
	  		$getPicName = $this->Picture->find('all',array(
						'conditions' => array('Picture.picture_name' => $pic_name,
						'Picture.flag'=> '1'),
						'limit'=>'1'
					));
	  		
	  		if(!empty($getPicName)){

	  			foreach ($getPicName as $value) {

	  				$duplicateName = $value['Picture']['picture_name'];
	  				//$duplicateType = $value['PictureModel']['picture_type'];
	  				$duplicate = $duplicateName.".".$pic_ext;
	  				array_push($PicNameArr, $duplicate);
	  				array_push($iArray, $i);
	  			}
	  			
	  		}
  		
		}
		
		if(!empty($PicNameArr)){

			$data = array(
						'content' => $PicNameArr,
						'content1'=> $iArray,
						'invalid' => "",
						'error'   => ""
					);	
			return json_encode($data);
			//echo json_encode($PicNameArr);
		}else{
			$data = array(
						'content' => "empty",
						'invalid' => "",
						'error'   => ""
					);	
			return json_encode($data);
		}		
			
	}

	public function SizeZeroError(){
		
		$this->request->allowMethod('ajax');
		$this->autoRender = false;
		$this->layout = false;

		$SizeError = $this->Session->read('SIZEZEROERROR');
		$this->Session->delete('SIZEZEROERROR');
		echo json_encode($SizeError);

	}
	
}
