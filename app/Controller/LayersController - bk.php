<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * LayerGroup Controller
 *
 * @property LayerGroup $LayerGroup
 * @property PaginatorComponent Paginator
 */
class LayersController extends AppController
{
	/**
	 * Components
	 *
	 * @var array
	 */
	public $uses = array('Layer', 'LayerType', 'User','Sample','Asset','Sap','BrmActualResult','BrmBudgetPrime');
	public $components = array('Session','Flash','Paginator', 'PhpExcel.PhpExcel');
	public $helpers = array('Html', 'Form', 'Session');

	public function beforeFilter()
	{
		parent::CheckSession();
		parent::checkUserStatus();
	}

	/**
	 * index method
	 *
	 * @author Hein Htet Ko
	 * @return void
	 */

	public function index() {
		$this->layout = 'mastermanagement';
		#get layer list
		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
		
		#get all layers
		$layers = $this->LayerType->find('list', array(
			'fields' => array(
				'LayerType.type_order', 'LayerType.name_'.$lang_name
			),
			'conditions' => array(
				'LayerType.flag' => 1,
			),
			'order' => 'LayerType.type_order'
		));
		$layer_no = array_keys($layers)[0];
		if ($this->params['url']['layer'] != '') {
			$layer_no = $this->params['url']['layer'];
		}

		$layer_type_id = $this->LayerType->find('all', array(
			'fields' => array(
				'LayerType.id'
			),
			'conditions' => array(
				'LayerType.flag' => 1,
				'LayerType.type_order' => $layer_no,
			),
			'order' => 'LayerType.type_order'
		))[0]['LayerType']['id'];
	
		#get show detail
		$show_detail = $this->getShowDetail($layer_no);

		#get manager id
		$managerss = $this->User->find('list', array(
			'fields'=>array('User.id', 'User.login_code'),
			'conditions'=>array(
				'NOT'=>array(
					'User.flag'=>'0'
				),
				'OR' =>array(
					array('User.role_id' => 1),
					array('User.role_id' => 2),
					array('User.role_id' => 5),
					array('User.role_id' => 8),
				)
			)
		));
		$date = date('Y-m-d');

		#get parent layers
		$parents = $this->getParentLayers($layer_no,$date,$lang_name);

		#set limit
		$limit = Paging::TABLE_PAGING;

		#set table fields in manual
		$field_pair = array(
			'id' => '＃',
			'layer_code' => 'コード',
			'name_jp' => $layers[$layer_no].' '.__('名').'（JP）',
			'name_en' => $layers[$layer_no].' '.__('名').'（ENG）',
			'parent_id' => 'PARENT ID',
			'from_date' => '開始日',
			'to_date' => '終了日',
			'managers' => '部長ID',
			'item_1' => '内訳①',
			'item_2' => '内訳②',
			'form' => '形態',
		);

		
		
		#hide detail data if not showing detail
		if (!$show_detail) {
			unset($field_pair['object']);
			unset($field_pair['destination']);
			unset($field_pair['managers']);
			unset($field_pair['code']);
		}

		if ($layer_no == array_keys($layers)[0]) {
			unset($field_pair['parent_id']);
		}

		#get table title and db fields
		$title_fields = array_values($field_pair);
		$db_fields = array_keys($field_pair);
		array_push($db_fields,'id');

		#get all layer data
		$list = $this->getPaginateData($limit,$layer_type_id,$show_detail,$db_fields);
		
		foreach ($list as $key => $value) {
			
			$exist_child = $this->Layer->find('count', array(
				'fields' => array('Layer.id','Layer.layer_code'),
				'conditions' => array(
					'Layer.flag' => 1,
					//'Layer.type_order' => 2,
					'Layer.parent_id like ' => "%".$value['layer_data']['layer_code']."%",
				)
			));//pr($exist_child);
			$list[$key]['exist_child'] = $exist_child;
			
		}
		
		$rowCount = $this->params['paging']['Layer']['count'];
		//echo '<pre>';print_r($sepLayers);echo '</pre>';
		if ($rowCount == 0) {
			$this->set('errmsg', parent::getErrorMsg('SE001'));
			$this->set('succmsg', '');
		} else {
			$this->set('succmsg', parent::getSuccessMsg('SS004', $rowCount));
			$this->set('errmsg', '');
		}
		

		$page = $this->params['paging']['Layer']['page'];
		$limit = $this->params['paging']['Layer']['limit'];

		$parents_json = json_encode($parents);

		#get default code
		$default_code = $this->getDefaultCode($layer_no);

		$this->set(compact( 'parents', 'parents_json', 'title_fields', 'layers', 'show_detail', 'managerss', 'list', 'rowCount', 'page', 'limit', 'layer_no', 'default_code','layer_type_id'));
		return $this->render('index');
	}


	/**
	 * get paginated data from layer group
	 *
	 * @author PanEiPhyo (20220610)
	 * @return array
	 */
	public function getPaginateData($limit,$layer_no,$show_detail,$fields) {
		$today = date('Y-m-d');
		$layer_list = [];
		$conditions['Layer.layer_type_id'] = $layer_no;
		$conditions['Layer.layer_code <>'] = null;
		$conditions['Layer.flag'] = 1;
		//$conditions['Layer.to_date >='] = $today; #filter exprie data
		$conditions[] = 'Layer.id IN (select max(layers.id) from layers where layers.flag=1 group by layers.layer_code)';
		
		#for pagination
		$this->paginate = array(
			'maxLimit' => $limit,
			'limit' => $limit,
			'conditions' => $conditions,
			'fields' => $fields,
			'order' => [
				'Layer.id' => 'asc'
			]			
		);
	//pr($this->paginate());die;
		$lists = $this->Paginator->paginate('Layer');
	
		foreach ($lists as $layer_data) {
			$layer_data = $layer_data['Layer'];
			
			$history_count = $this->Layer->find('count', array(
				'conditions' => array(
					'Layer.flag' => 1,
					'Layer.layer_code' => $layer_data['layer_code'],
					'Layer.id <>' => $layer_data['id'],
				),
				'Layer.order' => array('id')
			));
			
			$data['history_count'] = $history_count;
			$data['layer_data'] = $layer_data;

			$layer_list[] = $data;
		}

		return $layer_list;
	}

	/**
	 * get parents data from layer group
	 *
	 * @author PanEiPhyo (20220613)
	 * @return array
	 */
	public function getParentLayers($layer_no,$today,$lang_name)
	{
		
		$parents = $this->LayerType->find('all',array(
			'fields' => array('name_'.$lang_name.' As layerTypeName','type_order'),
			'conditions' => array(
				'LayerType.flag' => 1,
				// 'from_date <=' => $today,
				// 'to_date >=' => $today,
				'type_order <' => $layer_no
			),
			'order' => array(
				'id',
			)
		));
	
		$parent_data = $this->Layer->find('list',array(
			'fields' => array('layer_code','name_'.$lang_name,'layer_type_id'),
			'conditions' => array(
				'Layer.flag' => 1,
				//'from_date <=' => $today,
				//'to_date >=' => $today,
				'Layer.type_order <' => $layer_no
			),
			'order' => array(
				'id',
			)
		));
		// pr($layer_no);
		//pr($parent_data);die;
		
	foreach($parent_data as $key=>$value){
		for ($i=0;$i<count($parents);$i++){
			
			if($parents[$i]['LayerType']['type_order'] == $key){
				$parents[$i]['LayerType']['layers'] = $value;
			}else if($parents[$i]['LayerType']['id'] == $key){
				$parents[$i]['LayerType']['layers'] = $value;
			}
		}
	}
	
		return $parents;
	}

	public function getParentLayerData($layer_no,$today,$lang_name)
	{
	
		$parent_data = $this->Layer->find('list',array(
			'fields' => array('layer_code','name_'.$lang_name,'layer_type_id'),
			'conditions' => array(
				'Layer.flag' => 1,
				'from_date <=' => $today,
				'to_date >=' => $today,
				'Layer.layer_type_id <' => $layer_no
			),
			'order' => array(
				'id',
			)
		));
		
	//pr($parents);die;
		return $parent_data;
	}

	/**
	 * save layer group
	 *
	 * @author Hein Htet Ko
	 * @return void
	 */
	#Edited by PanEiPhyo(20220615)
	public function saveLayer()
	{
		if ($this->request->is('POST')) {
			$data = [];
			$edit_date = [];
			$update_parents = [];
			$date = date('Y-m-d H:i:s');

			$req_data 	= $this->request->data;

			$login_id		= $this->Session->read('LOGIN_ID');
			$type_order 	= $this->request->data['type_order'];
			$layer_type_id 	= $this->request->data['layer_type_id'];
			$page_no 		= $this->request->data('hid_page_no');
			$show_detail 	= 0;
			$from_date 		= trim((!empty($req_data['from_date']))?$req_data['from_date']:$date);
			$to_date 		= trim((!empty($req_data['to_date']))?$req_data['to_date']:'9999-12-31');
			$name_jp 		= trim($req_data['name_jp']);
			$name_en 		= trim($req_data['name_en']);
			$code 			= trim($req_data['code']);
			$code 			= (strlen($code) < 4)? str_pad($code, 4, '0', STR_PAD_LEFT) : $code;
			$old_fromdate 	= trim((!empty($req_data['hid_fromdate']))?$req_data['hid_fromdate']:$date);
			$old_todate 	= trim((!empty($req_data['hid_todate']))?$req_data['hid_todate']:$date);
			$old_parents 	= trim($req_data['hid_parents']);
			$id 			= $this->request->data('hid_update_id');
			$layer_code 	= $this->request->data['org_id'];
			$parent_id 		= (!empty($id)) ? json_encode($req_data["parent_id"]) : json_encode($req_data["save_parent_id"]);
			$item_1 		= trim($req_data['item_1']);
			$item_2 		= trim($req_data['item_2']);
			$form 			= trim($req_data['form']);
	
			if ($from_date > $to_date) {
				$msg = parent::getErrorMsg('SE128', array(__('終了日'),__('開始日')));
				$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
				$this->redirect( Router::url( $this->referer(), true ));
			}
			
			try {
				#get all layers
				$msg = "";
				
				// if($id){
				// 	$check_nameJP_exist = $this->Layer->find('all', array(
				// 		'fields' => array(
				// 			'Layer.id,Layer.layer_code,Layer.name_jp'
				// 		),
				// 		'conditions' 	=> array(
				// 			'Layer.name_jp'  => $name_jp,
				// 			'Layer.flag' => 1,
				// 			'Layer.id <>' => $id
				// 		),
				// 	));
				// //	pr($check_nameJP_exist);die;
				// 	$check_nameEN_exist = $this->Layer->find('all', array(
				// 		'fields' => array(
				// 			'Layer.id,Layer.layer_code,Layer.name_en'
				// 		),
				// 		'conditions' 	=> array(
				// 			'Layer.name_jp'  => $name_en,
				// 			'Layer.flag' => 1,
				// 			'Layer.id <>' => $id
				// 		),
				// 	));
				// }else{
				// 	$check_nameJP_exist = $this->Layer->find('all', array(
				// 		'fields' => array(
				// 			'Layer.id,Layer.layer_code,Layer.name_jp'
				// 		),
				// 		'conditions' 	=> array(
				// 			'Layer.name_jp'  => $name_jp,
				// 			'Layer.flag' => 1
				// 		),
				// 	));

				// 	$check_nameEN_exist = $this->Layer->find('all', array(
				// 		'fields' => array(
				// 			'Layer.id,Layer.layer_code,Layer.name_en'
				// 		),
				// 		'conditions' 	=> array(
				// 			'Layer.name_jp'  => $name_en,
				// 			'Layer.flag' => 1
				// 		),
				// 	));
				// }
				
				// if(!empty($check_nameJP_exist)){
				// 	$msg = parent::getErrorMsg('SE002', array(__('Name JP')));
				// 	$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
				// 	$this->redirect( Router::url( $this->referer(), true ));
				// }	

				// if(!empty($check_nameEN_exist)){
				// 	$msg = parent::getErrorMsg('SE002', array(__('Name EN')));
				// 	$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
				// 	$this->redirect( Router::url( $this->referer(), true ));
				// }

				$exit_value = $this->Layer->find('list', array(
					'conditions' => array(
						'Layer.layer_code' => $code,
						'flag' => 1
					)
				));

			
				if($id == "" && $exit_value){
					$msg = parent::getErrorMsg('SE002', array(__('コード')));
					$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
					$this->redirect( Router::url( $this->referer(), true ));
				}		
				
				#check already inserted
				$duplicate_data = $this->Layer->find('list', array(
					'fields' => array(
						'from_date','to_date','id'
					),
					'conditions' => array(
						'Layer.name_jp' => $name_jp,
						'Layer.name_en' => $name_en,
						'Layer.layer_type_id' => $layer_type_id,
						'Layer.flag' => 1,
					)
				));

				//pr($parent_id);die;
				
				$dup_from = (!empty($duplicate_data[$id])) ? array_keys($duplicate_data[$id])[0] : "";
				$dup_to = (!empty($duplicate_data[$id])) ? array_values($duplicate_data[$id])[0] : "";


				if ($parent_id!=$old_parents || (empty($duplicate_data) || ($dup_from!=$from_date) || ($dup_to!=$to_date) || $id != "")) {
					
					$data['layer_type_id'] 	= $layer_type_id;
					$data['type_order'] 	= trim($type_order);
					$data['layer_code'] 	= $code;
					$data['name_jp'] 		= $name_jp;
					$data['name_en'] 		= $name_en;
					$data['parent_id'] 		= $parent_id;
					$data['from_date'] 		= $from_date;
					$data['to_date'] 		= $to_date;
					$data['managers'] 	    = trim((!empty($req_data['manager_code']))?$req_data['manager_code']:null);
					$data['item_1'] 		= $item_1;
					$data['item_2'] 		= $item_2;
					$data['form'] 			= $form;
					$data['object'] 		= trim($req_data['object']);
					$data['flag'] 			= 1;
					$data['created_by'] 	= trim($login_id);
					$data['updated_by'] 	= trim($login_id);
					$data['created_date'] 	= trim($date);
					$data['updated_date'] 	= trim($date);
					

				} else if(!empty($duplicate_data) && $id == "") {
					$msg = parent::getErrorMsg('SE002', __('データ'));
					$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
					$this->redirect( Router::url( $this->referer(), true ));
				}
				
				if ($id != "") {
					$current_name = $this->Layer->find('first',array(
						'fields' => array('name_jp','name_en'),
						'conditions' => array(
							'Layer.flag' => 1,
							'Layer.id' => $id,
						)
					))['Layer'];
						
					if ((($current_name['name_jp']==$name_jp) && 
					($current_name['name_en']==$name_en) 
					|| ($from_date==$old_fromdate && $to_date==$old_todate)) 
					&& $parent_id==$old_parents
					 ) { 
						$data['id'] = $id;
						unset($data['created_by']);
						unset($data['created_date']);
					} else {
			
						if ($from_date >= $old_fromdate) {
							$edit_date['id'] = $id;
							$edit_date['to_date'] = date('Y-m-d',(strtotime('-1 day' , strtotime ($from_date))));
							$edit_date['updated_by'] = trim($login_id);
							$edit_date['updated_date'] = trim($date);

							if ($parent_id!=$old_parents) {
								$same_parents = $this->getUpdateParentIDData($type_order,$parent_id,$old_parents,$login_id,$date,$from_date,$to_date, $code);
							
							}else $same_parents = [];
						} else {
							$msg = parent::getErrorMsg('SE128', array(__('開始日'),__('現在の開始日')));
							$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
							$this->redirect( Router::url( $this->referer(), true ));
						}
					}
				} else {
					$last_id = $this->Layer->find('first',array(
						'fields' => 'id',
						'order' => array('id DESC'),
					))['Layer']['id'];
					$layer_id = (!empty($last_id)) ? $last_id+1 : 1;
				}
				
				$data['Layer.id'] = $layer_id;

				$lgDB = $this->Layer->getDataSource();

                $lgDB->begin();
				$this->Layer->create();
				//$same_parents = '';
			
				$status1 = $this->Layer->save($data);
				$status2 = (!empty($edit_date)) ? $this->Layer->save($edit_date) : true;
				$status3 = (!empty($same_parents)) ? $this->Layer->saveAll($same_parents) : true;
				
				if ($status1 && $status2 && $status3) {
					$lgDB->commit();
					#check pagination
					$msgcode = ($id != "") ? 'SS002' : 'SS001';
					$msg = parent::getSuccessMsg($msgcode);
					$this->Flash->set($msg, array('key'=>'LayerGroupSuccess'));
					
					$this->redirect( Router::url( $this->referer(), true ));
				} else {
					$lgDB->rollback();
					$msg = parent::getErrorMsg('SE003');
					$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
					
					$this->redirect( Router::url( $this->referer(), true ));
				}

			} catch (Exception $e) {
				CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
				$msg = parent::getErrorMsg('SE003');
				$this->Flash->set($msg, array('key'=>'LayerGroupFail'));
				
				$this->redirect( Router::url( $this->referer(), true ));
			}
		} else {
			$this->redirect('index');
		}
	}

	// /**
	//  * get show detail method
	//  *
	//  * @author Hein Htet Ko
	//  * @return value
	//  */
	public function getShowDetail($type_order=null)
	{
		$conditions = array();
		if ($type_order != null) {
			$conditions['LayerType.type_order'] = $type_order;
		}
		$conditions['LayerType.flag'] = 1;
		$show_detail = $this->LayerType->find('first', array(
			'fields' => array(
				'LayerType.type_order','LayerType.show_detail'
			),
			'conditions' => $conditions,
		))['LayerType']['show_detail'];
		return $show_detail;
	}

	public function checkUsedCode($code){
		$used_code[] = $this->doRecursiveJob('samples', 'Sample', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('assets', 'Asset', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('saps', 'Sap', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('brm_actual_results', 'BrmActualResult', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('brm_budget_primes', 'BrmBudgetPrime', $code, 'layer_code', 'layer_code');

		return (in_array('exit', $used_code))?true : false;

	}

	public function doRecursiveJob($table, $model, $code, $field1, $field2){
		$tmp_flag = empty($this->Layer->find('all', array(
			'fields' => array('DISTINCT Layer.layer_code'),
			'joins' => array(
				array(
					'table' => $table,
					'alias' => $model,
					'type' => 'INNER',
					'conditions' => array(
						$model.'.'.$field1.' = Layer.'.$field2,
					)
				),
			),
			'conditions' => array(
				'Layer.layer_code' => $code,
				'Layer.flag' => 1
			)
		)))? 'not_exit' : 'exit';

		return $tmp_flag;
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
		$haveChild = $this->request->data('haveChild');

		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
		
		#get data by layer id
		$layer_data = $this->Layer->find('first', array(
			'fields' => array('id','layer_type_id','name_en', 'name_jp', 'parent_id','layer_code','from_date','to_date','managers','item_1','item_2','form','object'),
			'conditions' => array(
				'Layer.id' => $edit_field_id,
				'Layer.flag' => 1
			)
		))['Layer'];
		$remove_text = ["{", "}"];
		$add_text   = ["", ""];

		$parent_id_arr = str_replace($remove_text,$add_text,explode(",",$layer_data['parent_id']));
		
		$today = date('Y-m-d');
		$layers = [];
		//$this->log(print_r($parent_id_arr,true),LOG_DEBUG);
		for($i=0;$i<count($parent_id_arr);$i++){
			//$order = str_replace('"',,explode(":",$parent_id_arr[$i])[0]);
			preg_match_all('!\d+!', explode(":",$parent_id_arr[$i])[0], $typeOrder);
			
			$layer_type_id = $this->Layer->find('list',array(
				'fields' => array('layer_code','layer_type_id'),
				'conditions' => array(
					'Layer.flag' => 1,
					// 'Layer.from_date <=' => $today,
					'Layer.to_date >=' => $today,
					'Layer.type_order' => $typeOrder[0][0],
					//"Layer.parent_id like" => '%'.$parent_id_arr[$i-1].'%',
				),'group' => array(
					'Layer.layer_type_id',
				)
			));
			$conditions['Layer.flag'] = 1;
			// $conditions['Layer.from_date <='] = $today;
			$conditions['Layer.to_date >='] = $today;
			$conditions['Layer.layer_type_id'] = $layer_type_id;
			if($i>0){
				$conditions['Layer.parent_id like'] = '%'.$parent_id_arr[$i-1].'%';
			}
			$data = $this->Layer->find('list',array(
				'fields' => array('layer_code','name_'.$lang_name),
				'conditions' => $conditions,
				'order' => array(
					'Layer.id',
				)
			));
			//$this->log(print_r($data,true),LOG_DEBUG);
			//$this->log(print_r($order,true),LOG_DEBUG);
			$layers['layer_data'][$i] = $data;
		}
		//$this->log(print_r($layers,true),LOG_DEBUG);
		// $exist_code = [];
		// if(!empty($exist_layer_code_arr)){
		// 	foreach($exist_layer_code_arr as $val){
		// 		array_push($exist_code,$val['Layer']['id']);
		// 	}
		// 	$exist_code = implode(",",$exist_code);
		// 	$response['exist_code'] = $exist_code;
		// }  
		
		if ($layer_data['to_date'] != '' && $layer_data['to_date'] < date('Y-m-d')) {
			$response['expired'] = true;
		} else {
			$response['expired'] = false;
		}
		if (!empty($layer_data)) {
			$response['data_list'] = $layer_data;
		}
		if (!empty($layer_data)) {
			$response['layer'] = $layers;
		}
		

		#check code already use or not
		$used_code = $this->checkUsedCode($layer_data['layer_code']);
		$response['code_readonly'] = $used_code;
		if($haveChild == "1") $response['code_readonly'] = true;
		
		echo json_encode($response);
	}

	/**
	* remove layer group
	*
	* @author Hein Htet Ko
	* @return void
	*/
	public function removeLayer()
	{
		if ($this->request->is('POST')) {
			$login_id= $this->Session->read('LOGIN_ID');
			$page_no = (in_array($this->request->data('hid_page_no'), array('Layers', 'index')))? '' : $this->request->data('hid_page_no');
			$hid_delete_id = $this->request->data('hid_delete_id');
			$has_layer = $this->Layer->find('first', array(
				'conditions' =>array(
					//'OR' => array(
						'Layer.id' => $hid_delete_id,
						//'layer_code' => $hid_delete_id,
					//),
					'Layer.flag' => 1)
				))['Layer']['layer_code'];

			$type_order = $has_layer['LayerType']['type_order'];
			
			$users_data = $this->User->find('all',array(
				'fields' => array('layer_code'),
				'conditions' => array(
					'flag' =>1,
				)
			));
			
			foreach($users_data as $value){
				$layer_arr = explode(',',$value['User']['layer_code']);
				if(in_array($has_layer,$layer_arr)){
					$errorMsg = parent::getErrorMsg('SE144');
					$this->Flash->set($errorMsg, array('key'=> 'LayerGroupFail'));
					$this->redirect( Router::url( $this->referer(), true ));
				}
			}
			if (empty($has_layer)) {
				$errorMsg = parent::getErrorMsg('SE050');
				$this->Flash->set($errorMsg, array('key'=> 'LayerGroupFail'));
				$this->redirect( Router::url( $this->referer(), true ));
			}
			try {
				// $checkedChild = $this->Layer->find('all', array(
				// 	'fields' => array(
				// 		'Layer.id'
				// 	),
				// 	'conditions' => array(
				// 		'OR' =>array(
				// 			array('Layer.parent_id LIKE' => $hid_delete_id),
				// 			array('Layer.parent_id LIKE' => '%'.$hid_delete_id.'%'),
				// 			array('Layer.parent_id LIKE' => '%.'.$hid_delete_id.'%'),
				// 			array('Layer.parent_id LIKE' => '%'.$hid_delete_id.'.%'),
	
				// 		),
				// 		'Layer.flag' => 1,
				// 	)
				// ));
				
				// if (sizeof($checkedChild) > 0) {
				// 	$errorMsg = parent::getErrorMsg('SE126');
				// 	$this->Flash->set($errorMsg, array('key'=> 'LayerGroupFail'));
				// 	$this->redirect( Router::url( $this->referer(), true ));
				// } else {
					$attachDB = $this->Layer->getDataSource();
					$attachDB->begin();
					$rows = $this->Layer->find('all', array(
					'conditions' =>array(
						//'OR' => array(
							'Layer.id' => $hid_delete_id,
						// 	'LayerType.layer_code' => $hid_delete_id,
						// )
					)));

					
					foreach ($rows as $row) {
						$row['Layer']['id'] = $row['Layer']['id'];
						$row['Layer']['flag'] = 0;
						$row['Layer']['updated_by'] = $login_id;
						$row['Layer']['updated_date'] = date('Y-m-d H:i:s');
						$this->Layer->saveAll($row['Layer']);
					}
					$attachDB->commit();
					#check it has affected row in table
					$delete_status = $this->Layer->getAffectedRows();
					
		
					if ($delete_status) {
						$msg = parent::getSuccessMsg('SS003');
						$this->Flash->set($msg, array('key'=>'LayerGroupSuccess'));
						$this->redirect( Router::url( $this->referer(), true ));
					} else {
						$errorMsg = parent::getErrorMsg('SE050');
						$this->Flash->set($errorMsg, array('key'=> 'LayerGroupFail'));
						$this->redirect( Router::url( $this->referer(), true ));
					}
				//}
			} catch (Exception $e) {
				CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
				$attachDB->rollback();
				$msg = parent::getErrorMsg('SE050');
				$this->Flash->set($msg, array('key'=> 'LayerGroupFail'));
				$this->redirect( Router::url( $this->referer(), true ));
			}
		}
	}
	
	/**
	* get history data
	*
	* @author Ei Thandar Kyaw
	* @return void
	*/
	public function getHistoryData()
	{
		#only allow ajax request
		$this->request->allowMethod('ajax');
		$this->autoRender = false;
		$id = $this->request->data('id');
		
		$lyr_id = $this->Layer->find('first',array(
			"fields" => 'layer_code',
			"conditions" => array(
				'Layer.flag' => 1,
				'Layer.id' => $id,
			),
		))['Layer']['layer_code'];

		#get data by layer id
		$data = $this->Layer->find('all', array(
			'fields' => array('name_en','name_jp','from_date','to_date', 'parent_id'),
			"conditions" => array(
				'Layer.layer_code' => $lyr_id,
				'Layer.flag' => 1
			),
			"order" => array('from_date','to_date'),
		));

		$today = date('Y-m-d');
		$name = ($_SESSION['Config']['language'] == 'eng') ? 'name_en' : 'name_jp';
		$parent_code = $this->Layer->find('list', array(
			'fields' => array('layer_code', $name),
			"conditions" => array(
				'Layer.flag' => 1,
				'Layer.from_date <=' => $today,
                'Layer.to_date >=' => $today,
			),
		));
		
		foreach ($data as $key => $value) {
			$layer = array_values(json_decode($value['Layer']['parent_id'], TRUE));
			$data[$key]['lan_name'] = $name;
			foreach ($layer as $code) {
				$data[$key]['parent_data'][] = $parent_code[$code];
			}
		}
		echo json_encode($data);
	}

	/**
	* get child's parent id data
	*
	* @author PanEiPhyo(20200617)
	* @return json
	*/
	public function getUpdateParentIDData($layer_no,$new_id,$old_id,$login_id,$today,$from_date,$to_date,$code='')
	{	
		$cur_id = ',"L'.$layer_no.'":"'.$code.'"';
		$new_id = rtrim($new_id, "}").$cur_id;
		$old_id = rtrim($old_id, "}").$cur_id;
		$update_data = [];
		$upd_to_date = date('Y-m-d',(strtotime('-1 day' , strtotime ($from_date))));
		
		$lg_lists = $this->Layer->find('all',array(
			'conditions' => array(
				'Layer.flag' => 1,
				'Layer.parent_id LIKE ' => $old_id."%",
				'Layer.layer_type_id >' => $layer_no,
			),
		));

		foreach ($lg_lists as $lg_list) {
			$data = $lg_list['Layer'];

			// $update_data[] = array(
			// 	'Layer.id' => $data['id'],
			// 	'Layer.to_date' => $upd_to_date,
			// 	'Layer.updated_by' => $login_id,
			// 	'Layer.updated_date' => $today,
			// );

			//unset($data['id']);
			$data['created_date'] = $today;
			$data['updated_date'] = $today;
			$data['created_by'] = $login_id;
			$data['updated_by'] = $login_id;
			$data['from_date'] = $from_date;
			$data['to_date'] = $to_date;
			$data['parent_id'] = str_replace($old_id, $new_id, $data['parent_id']);
			$update_data[] = $data;
		}
		//pr($update_data);die;
		return $update_data;
	}

	/**
	* get child data
	*
	* @author PanEiPhyo (20220705)
	* @return json array
	*/
	public function getChildData()
	{
		#only allow ajax request
		$this->request->allowMethod('ajax');
		$this->autoRender = false;
		$l_name = $this->request->data('layer_name');
		$l_id = $this->request->data('layer_code');
		$l_no = $this->request->data('next_layer');
		$parent_id = '%'.$l_name.'":"'.$l_id.'%';
		$today = date('Y-m-d');

		#get language extension
		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
		
		$lTypeData = $this->LayerType->find('all',array(
			'fields' => array('flag'),
			'conditions' => array(
				'LayerType.flag' => 1,
				'LayerType.type_order' => $l_no,
				
			)
		))[0]['LayerType']['flag'];
		
		$realNextLayer = "";
		if($lTypeData == 0){	
			$realNextLayer = $this->LayerType->find('first',array(
				'fields' => array('type_order','flag'),
				'conditions' => array(
					'LayerType.flag' => 1,
					'LayerType.type_order >' => $l_no,
					
				)
			))['LayerType']['type_order'];

		}
		$layer_type_id = !empty($realNextLayer)?$realNextLayer : $l_no;
		$this->log(print_r($layer_type_id,true),LOG_DEBUG);
		#get data by layer id
		$data = $this->Layer->find('list',array(
			'fields' => array('layer_code','name_'.$lang_name),
			'conditions' => array(
				'Layer.flag' => 1,
				// 'Layer.from_date <=' => $today,
				'Layer.to_date >=' => $today,
				'Layer.type_order' => $layer_type_id,
				"Layer.parent_id like" => $parent_id,
			),
			'order' => array(
				'Layer.id',
			)
		));
	
		echo json_encode($data);
	}


	/**
	* get default code
	*
	* @author Hein Htet Ko (20220727)
	* @return string 
	*/

	public function getDefaultCode($layer_no){
		$n1=3;
		$n2=1;

		$character_1 = '0123456789';
		$character_2 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$randomString_1 = '';
    	$randomString_2 = '';
  
    for ($i = 0; $i < $n1; $i++) {
        $index_1 = rand(0, strlen($character_1) - 1);
        $randomString_1 .= $character_1[$index_1];
    }

	for ($i = 0; $i < $n2; $i++) {
        $index_2 = rand(0, strlen($character_2) - 1);
        $randomString_2 .= $character_2[$index_2];
    }

    return $layer_no.$randomString_1.$randomString_2;

	}


	// /**
	// * check field value exit or not
	// *
	// * @author Hein Htet Ko (20220728)
	// * @return boolean 
	// */
	// public function existFieldValue($field, $value){
		
		
	// 	$exit_value = $this->Layer->find('all', array(
	// 		'conditions' => array(
	// 			$field => $value,
	// 			'flag' => 1
	// 		)
	// 	));
	// 	return (empty($exit_value))?false : true;

	// }

	/**
	* check code already used or not
	*
	* @author Hein Htet Ko (20220728)
	* @return boolean 
	*/
	// public function checkUsedCode($code){
			
	// 	$used_code[] = $this->doRecursiveJob('samples', 'Sample', $code, 'layer_code', 'layer_code');
	// 	$used_code[] = $this->doRecursiveJob('tbl_m_asset', 'Asset', $code, 'layer_code', 'layer_code');
	// 	$used_code[] = $this->doRecursiveJob('tbl_m_sap', 'SapImportsModel', $code, 'layer_code', 'layer_code');
	// 	$used_code[] = $this->doRecursiveJob('tbl_actual_result', 'ActualResultModel', $code, 'layer_code', 'layer_code');
	// 	$used_code[] = $this->doRecursiveJob('tbl_budget_prime', 'BudgetPlanModel', $code, 'layer_code', 'layer_code');

	// 	return (in_array('exit', $used_code))?true : false;

	// }

	/**
	* do recursive job
	*
	* @author Hein Htet Ko (20220729)
	* @return boolean 
	*/
	// public function doRecursiveJob($table, $model, $code, $field1, $field2){

	// 	$tmp_flag = empty($this->Layer->find('all', array(
	// 		'fields' => array('DISTINCT Layer.layer_code'),
	// 		'joins' => array(
	// 			array(
	// 				'table' => $table,
	// 				'alias' => $model,
	// 				'type' => 'INNER',
	// 				'conditions' => array(
	// 					$model.'.'.$field1.' = Layer.'.$field2,
	// 				)
	// 			),
	// 		),
	// 		'conditions' => array(
	// 			'Layer.layer_code' => $code,
	// 			'Layer.flag' => 1
	// 		)
	// 	)))? 'not_exit' : 'exit';
		
	// 	return $tmp_flag;
	// }
}
