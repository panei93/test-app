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
	public $uses = array('Layer', 'LayerType', 'User', 'Sample', 'Asset', 'Sap', 'BrmActualResult', 'BrmBudgetPrime');
	public $components = array('Session', 'Flash', 'Paginator', 'PhpExcel.PhpExcel');
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
		#get layer list
		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
		$user_datas = $this->checkRoleUser();
		
		#get all layers
		$layers = $this->LayerType->find('list', array(
			'fields' => array(
				'LayerType.type_order', 'LayerType.name_' . $lang_name
			),
			'conditions' => array(
				'LayerType.flag' => 1,
			),
			'order' => 'LayerType.type_order'
		));
		if(!empty($user_datas)) {
			$layer_tab = $this->TabHideShow($lang_name);
			$layer_code = explode('/', array_column($user_datas, 'layer_code')[0]);
			if(!empty($this->params->query['layer']) && $this->params->query['layer'] < array_keys(Setting::LAYER_PERMIT_ROLES)[0]) {
				$this->redirect(Router::url($this->referer(), true)."Layers");
			}
		}else $layer_tab = $layers;

		$layer_no = array_keys($layer_tab)[0];
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
			'fields' => array('User.id', 'User.login_code'),
			'conditions' => array(
				'NOT' => array(
					'User.flag' => '0'
				),
				'OR' => array(
					array('User.role_id' => 1),
					array('User.role_id' => 2),
					array('User.role_id' => 5),
					array('User.role_id' => 8),
				)
			)
		));
		$date = date('Y-m-d');

		#get parent layers
		$parents = $this->getParentLayers($layer_no, $date, $lang_name, $layer_code);

		#set limit
		$limit = Paging::TABLE_PAGING;

		#set table fields in manual
		$field_pair = array(
			'id' => '＃',
			'layer_code' => 'コード',
			'name_jp' => $layers[$layer_no] . ' ' . __('名') . '（JP）',
			'name_en' => $layers[$layer_no] . ' ' . __('名') . '（ENG）',
			'parent_id' => 'PARENT ID',
			'from_date' => '開始日',
			'to_date' => '終了日',
			'managers' => '部長ID',
			'item_1' => '内訳①',
			'item_2' => '内訳②',
			'form' => '形態',
			'layer_order' => '部署順番',
			'bu_status' => 'BU概要レイヤー状態'
		);

		$sub_busi_show_hide = true;
		#check sub_business to show table header
		/*$show_bu_status = $layer_no;
		$bu_show_list = Setting::BU_SHOW_HIDE;
		$sub_busi_show_hide = (in_array($show_bu_status, $bu_show_list)) ? true : false;

		if(!$sub_busi_show_hide) {
			unset($field_pair['bu_status']);
		}*/
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
		array_push($db_fields, 'id');

		#get all layer data
		$list = $this->getPaginateData($limit, $layer_type_id, $show_detail, $db_fields, $layer_code);

		foreach ($list as $key => $value) {

			$exist_child = $this->Layer->find('count', array(
				'fields' => array('Layer.id', 'Layer.layer_code'),
				'conditions' => array(
					'Layer.flag' => 1,
					'Layer.parent_id like ' => "%" . $value['layer_data']['layer_code'] . "%",
				)
			));
			$list[$key]['exist_child'] = $exist_child;
		}

		$rowCount = $this->params['paging']['Layer']['count'];

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
		if (count($parents) > 0) {
			$cur_layer_prev[count($parents) - 1] = $parents[count($parents) - 1];
			unset($parents[count($parents) - 1]);
		}

		$this->set(compact('parents', 'parents_json', 'title_fields', 'layers', 'show_detail', 'managerss', 'list', 'rowCount', 'page', 'limit', 'layer_no', 'default_code', 'layer_type_id', 'cur_layer_prev', 'sub_busi_show_hide', 'layer_tab'));
		return $this->render('index');
	}


	/**
	 * get paginated data from layer group
	 *
	 * @author PanEiPhyo (20220610)
	 * @return array
	 */
	public function getPaginateData($limit, $layer_no, $show_detail, $fields, $layer_code = '')
	{
		$today = date('Y-m-d');
		$layer_list = [];
		$conditions['Layer.layer_type_id'] = $layer_no;
		$conditions['Layer.layer_code <>'] = null;
		$conditions['Layer.flag'] = 1;
		//$conditions['Layer.to_date >='] = $today; #filter exprie data
		$conditions[] = 'Layer.id IN (select max(layers.id) from layers where layers.flag=1 group by layers.layer_code)';
		if(!empty($layer_code)) {
			foreach ($layer_code as $code) {
				$sql1[] = 'Layer.parent_id LIKE "%'.$code.'%"';
				$sql2[] = 'Layer.layer_code = "'.$code.'"';
			}
			$conditions['OR'][] = "(".implode(" OR ", $sql1).")";
			$conditions['OR'][] = "(".implode(" OR ", $sql2).")";
		}

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
	public function getParentLayers($layer_no, $today, $lang_name, $layer_code = '')
	{

		$parents = $this->LayerType->find('all', array(
			'fields' => array('name_' . $lang_name . ' As layerTypeName', 'type_order'),
			'conditions' => array(
				'LayerType.flag' => 1,
				'type_order <' => $layer_no
			),
			'order' => array(
				'id',
			)
		));
		if(!empty($layer_code)) {
			$layer_type_order = $this->checkRoleUser()[0]['layer_type_order'];
			$condi = [];$fields = [];$order = [];$joins = [];
			$condi['Layer.flag'] = 1;
			
			if($layer_type_order <= Setting::LAYER_TYPE_LIMIT) {
				$fields = array('LayerParent.layer_code', 'LayerParent.name_' . $lang_name, 'LayerParent.layer_type_id');
				$condi['Layer.type_order <'] = $layer_no;
				foreach ($layer_code as $code) {
					$sql[] = 'Layer.parent_id LIKE "%'.$code.'%"';
					$sql[] = 'Layer.layer_code = "'.$code.'"';
				}
				array_push($condi, "(".implode(" OR ", $sql).")");
				$joins = array(
					array(
						'table' => 'layers',
						'alias' => 'LayerParent',
						'type' => 'right',
						'conditions' => array(
							'Layer.parent_id LIKE CONCAT("%", LayerParent.layer_code, "%") AND LayerParent.flag = 1'
						)
					)
				);
				$order = array('LayerParent.layer_code');
			}
			# from layer_no = 1 to selected user's layer' s parent layer
			$parent_one = $this->Layer->find('list', array(
				'fields' => $fields,
				'conditions' => $condi,
				'joins' => $joins,
				'order' => $order
			));
			# from [layer_no - 1] to user selected layer
			$parent_two = $this->Layer->find('list', array(
				'fields' => array('Layer.layer_code', 'Layer.name_' . $lang_name, 'Layer.layer_type_id'),
				'conditions' => $condi,
				'order' => array('Layer.layer_code')
			));
			$parent_data = $parent_two + $parent_one;
		}else {
			$parent_data = $this->Layer->find('list', array(
				'fields' => array('layer_code', 'name_' . $lang_name, 'layer_type_id'),
				'conditions' => array(
					'Layer.flag' => 1,
					'Layer.type_order <' => $layer_no,
				),
				'order' => array(
					'layer_code',
				)
			));
		}
	
		foreach ($parent_data as $key => $value) {
			for ($i = 0; $i < count($parents); $i++) {
				if ($parents[$i]['LayerType']['type_order'] == $key) {
					$parents[$i]['LayerType']['layers'] = $value;
				} else if ($parents[$i]['LayerType']['id'] == $key) {
					$parents[$i]['LayerType']['layers'] = $value;
				}
			}
		}
		
		return $parents;
	}

	public function getParentLayerData($layer_no, $today, $lang_name)
	{

		$parent_data = $this->Layer->find('list', array(
			'fields' => array('layer_code', 'name_' . $lang_name, 'layer_type_id'),
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
			$type_order 	= $req_data['type_order'];
			$layer_type_id 	= $req_data['layer_type_id'];
			$page_no 		= $req_data['hid_page_no'];
			$show_detail 	= 0;
			$from_date 		= trim((!empty($req_data['from_date'])) ? $req_data['from_date'] : $date);
			$to_date 		= trim((!empty($req_data['to_date'])) ? $req_data['to_date'] : '9999-12-31');
			$name_jp 		= trim($req_data['name_jp']);
			$name_en 		= (trim($req_data['name_en']) != "") ? trim($req_data['name_en']) : trim($req_data['name_jp']);
			$code 			= trim($req_data['code']);
			//$code 			= (strlen($code) < 4)? str_pad($code, 4, '0', STR_PAD_LEFT) : $code;
			$old_fromdate 	= trim((!empty($req_data['hid_fromdate'])) ? $req_data['hid_fromdate'] : $date);
			$old_todate 	= trim((!empty($req_data['hid_todate'])) ? $req_data['hid_todate'] : $date);
			$old_parents 	= trim($req_data['hid_parents']);
			$id 			= $req_data['hid_update_id'];
			$layer_code 	= $req_data['org_id'];
			$layer_order 	= $req_data['layer_order'] == '' ? 1000 : $req_data['layer_order'];
			$bu_status 		= $req_data['bu_status'];

			// $parent_id 		= (!empty($id)) ? json_encode($req_data["parent_id"]) : json_encode($req_data["save_parent_id"]);
			$item_1 		= trim($req_data['item_1']);
			$item_2 		= trim($req_data['item_2']);
			$form 			= trim($req_data['form']);
			$parent = (!empty($id)) ? 'parent_id' : 'save_parent_id';
			for ($i = 1; $i < $type_order; $i++) {
				$parent_ids['L' . $i] = $req_data[$parent]['L' . $i];
			}
			$parent_id = json_encode($parent_ids);

			if ($from_date > $to_date) {
				$msg = parent::getErrorMsg('SE128', array(__('終了日'), __('開始日')));
				$this->Flash->set($msg, array('key' => 'LayerGroupFail'));
				$this->redirect(Router::url($this->referer(), true));
			}
			$object =  $this->Layer->find('all', array(
				'fields' 	=> array('object'),
				'conditions'	=> array(
					'Layer.id' => $id
				)
			));
			try {
				#get all layers
				$msg = "";
				#code duplicated and name, p_name duplicated(start)
				$checkCodeDuplicate = true; #code duplicated, can't save, show err msg
				$checkDataDuplicate = true; #data duplicated, can't save, show err msg
				if ($id == "") { #save state
					$checkCodeDuplicate = $this->checkCodeDuplicate($checkCodeDuplicate, $code);
					$oldcode = '';
				} else { #update state
					$checkCodeDuplicate = false;
					$oldcode = $layer_code;
				}

				if (!$checkCodeDuplicate) {
					$checkDataDuplicate = $this->checkDataDuplicate($checkDataDuplicate, $from_date, $to_date, $name_jp, $name_en, $parent_id, $oldcode);
					if ($checkDataDuplicate) { #data duplicated
						$msg = parent::getErrorMsg('SE147', array($code));;
						$this->Flash->set($msg, array('key' => 'LayerGroupFail'));
						$this->redirect(Router::url($this->referer(), true));
					}
				} else {
					$msg = parent::getErrorMsg('SE002', array(__('コード')));
					$this->Flash->set($msg, array('key' => 'LayerGroupFail'));
					$this->redirect(Router::url($this->referer(), true));
				}
				#code duplicated and name, p_name duplicated(end)

				#check already inserted
				$duplicate_data = $this->Layer->find('list', array(
					'fields' => array(
						'from_date', 'to_date', 'id'
					),
					'conditions' => array(
						'Layer.name_jp' => $name_jp,
						'Layer.name_en' => $name_en,
						'Layer.layer_type_id' => $layer_type_id,
						'Layer.flag' => 1,
					)
				));

				$dup_from = (!empty($duplicate_data[$id])) ? array_keys($duplicate_data[$id])[0] : "";
				$dup_to = (!empty($duplicate_data[$id])) ? array_values($duplicate_data[$id])[0] : "";

				if ($parent_id != $old_parents || (empty($duplicate_data) || ($dup_from != $from_date) || ($dup_to != $to_date) || $id != "")) {

					$data['layer_type_id'] 	= $layer_type_id;
					$data['type_order'] 	= trim($type_order);
					$data['layer_code'] 	= $code;
					$data['name_jp'] 		= $name_jp;
					$data['name_en'] 		= $name_en;
					$data['parent_id'] 		= $parent_id;
					$data['from_date'] 		= $from_date;
					$data['to_date'] 		= $to_date;
					$data['managers'] 	    = trim((!empty($req_data['manager_code'])) ? $req_data['manager_code'] : null);
					$data['item_1'] 		= $item_1;
					$data['item_2'] 		= $item_2;
					$data['form'] 			= $form;
					$data['layer_order']	= trim($layer_order);
					$data['object'] 		= trim($object[0]['Layer']['object']);
					$data['bu_status'] 		= $bu_status;
					$data['flag'] 			= 1;
					$data['created_by'] 	= trim($login_id);
					$data['updated_by'] 	= trim($login_id);
					$data['created_date'] 	= trim($date);
					$data['updated_date'] 	= trim($date);
				} else if (!empty($duplicate_data) && $id == "") {
					$msg = parent::getErrorMsg('SE002', __('データ'));
					$this->Flash->set($msg, array('key' => 'LayerGroupFail'));
					$this->redirect(Router::url($this->referer(), true));
				}

				if ($id != "") {
					$current_name = $this->Layer->find('first', array(
						'fields' => array('name_jp', 'name_en'),
						'conditions' => array(
							'Layer.flag' => 1,
							'Layer.id' => $id,
						)
					))['Layer'];

					if ($current_name['name_jp'] == $name_jp && $current_name['name_en'] == $name_en && $from_date == $old_fromdate && $to_date == $old_todate && $parent_id == $old_parents) {
						$data['id'] = $id;
						unset($data['created_by']);
						unset($data['created_date']);
					} else {
						if ($from_date >= $old_fromdate) {
							if ($from_date == $old_fromdate) {
								$data['id'] = $id;
								unset($data['created_by']);
								unset($data['created_date']);
							} else {
								$edit_date['id'] = $id;
								$edit_date['to_date'] = date('Y-m-d', (strtotime('-1 day', strtotime($from_date))));
								$edit_date['updated_by'] = trim($login_id);
								$edit_date['updated_date'] = trim($date);
							}
							if ($parent_id != $old_parents) {
								$same_parents = $this->getUpdateParentIDData($type_order, $parent_id, $old_parents, $login_id, $date, $from_date, $to_date, $code);
							} else $same_parents = [];
						} else {
							$msg = parent::getErrorMsg('SE128', array(__('開始日'), __('現在の開始日')));
							$this->Flash->set($msg, array('key' => 'LayerGroupFail'));
							$this->redirect(Router::url($this->referer(), true));
						}
					}
				} else {
					$last_id = $this->Layer->find('first', array(
						'fields' => 'id',
						'order' => array('id DESC'),
					))['Layer']['id'];
					$layer_id = (!empty($last_id)) ? $last_id + 1 : 1;
				}

				$data['Layer.id'] = $layer_id;

				$lgDB = $this->Layer->getDataSource();

				$lgDB->begin();
				$this->Layer->create();

				$status1 = $this->Layer->save($data);
				$status2 = (!empty($edit_date)) ? $this->Layer->save($edit_date) : true;
				$status3 = (!empty($same_parents)) ? $this->Layer->saveAll($same_parents) : true;

				if (!$bu_status) {
					$bu_status = 0;
				} else {
					$bu_status = 1;
				}

				# updated by KHS (me) 10/11/2023
				# update current layer_code records' histories

				# start
				$this->Layer->updateAll(array(
					# 'Layer.bu_status' => $bu_status,
					'Layer.layer_order' => $data['layer_order']
				), array(
					'OR' => array(
						'Layer.layer_code ' => $layer_code, # current
					),  # conditions
					'Layer.flag' => 1
				));
				# end

				$this->Layer->updateAll(array(
					'Layer.bu_status' => $bu_status, # update
				), array(
					'OR' => array(
						'Layer.parent_id LIKE' => '%' . $layer_code . '%', # parent
						'Layer.layer_code LIKE' => '%' . $layer_code . '%', # current
					),  # conditions
					'Layer.flag' => 1
				));

				if ($status1 && $status2 && $status3) {
					$lgDB->commit();
					#check pagination
					$msgcode = ($id != "") ? 'SS002' : 'SS001';
					$msg = parent::getSuccessMsg($msgcode);
					$this->Flash->set($msg, array('key' => 'LayerGroupSuccess'));

					$this->redirect(Router::url($this->referer(), true));
				} else {
					$lgDB->rollback();
					$msg = parent::getErrorMsg('SE003');
					$this->Flash->set($msg, array('key' => 'LayerGroupFail'));

					$this->redirect(Router::url($this->referer(), true));
				}
			} catch (Exception $e) {
				CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
				$msg = parent::getErrorMsg('SE003');
				$this->Flash->set($msg, array('key' => 'LayerGroupFail'));

				$this->redirect(Router::url($this->referer(), true));
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
	public function getShowDetail($type_order = null)
	{
		$conditions = array();
		if ($type_order != null) {
			$conditions['LayerType.type_order'] = $type_order;
		}
		$conditions['LayerType.flag'] = 1;
		$show_detail = $this->LayerType->find('first', array(
			'fields' => array(
				'LayerType.type_order', 'LayerType.show_detail'
			),
			'conditions' => $conditions,
		))['LayerType']['show_detail'];
		return $show_detail;
	}

	public function checkUsedCode($code)
	{
		$used_code[] = $this->doRecursiveJob('samples', 'Sample', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('assets', 'Asset', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('saps', 'Sap', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('brm_actual_results', 'BrmActualResult', $code, 'layer_code', 'layer_code');
		$used_code[] = $this->doRecursiveJob('brm_budget_primes', 'BrmBudgetPrime', $code, 'layer_code', 'layer_code');

		return (in_array('exit', $used_code)) ? true : false;
	}

	public function doRecursiveJob($table, $model, $code, $field1, $field2)
	{
		$tmp_flag = empty($this->Layer->find('all', array(
			'fields' => array('DISTINCT Layer.layer_code'),
			'joins' => array(
				array(
					'table' => $table,
					'alias' => $model,
					'type' => 'INNER',
					'conditions' => array(
						$model . '.' . $field1 . ' = Layer.' . $field2,
					)
				),
			),
			'conditions' => array(
				'Layer.layer_code' => $code,
				'Layer.flag' => 1
			)
		))) ? 'not_exit' : 'exit';

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
			'fields' => array('id', 'layer_type_id', 'name_en', 'name_jp', 'parent_id', 'layer_code', 'from_date', 'to_date', 'managers', 'item_1', 'item_2', 'form', 'object', 'layer_order', 'bu_status', 'type_order'),
			'conditions' => array(
				'Layer.id' => $edit_field_id,
				'Layer.flag' => 1
			)
		))['Layer'];
		#check have children
		// $check_has_child = (count($this->Layer->find('first', array(
		// 	'conditions' => array(
		// 		'Layer.parent_id LIKE '=> '%"'.$layer_data['layer_code'].'"%',
		// 		'Layer.flag' => 1
		// 	)
		// )))>0)? true : false;
		$remove_text = ["{", "}"];
		$add_text   = ["", ""];

		$parent_id_arr = str_replace($remove_text, $add_text, explode(",", $layer_data['parent_id']));

		#get parent detail by selected layer code
		$parent_detail = $this->Layer->find('all', array(
			'fields' => array('from_date', 'to_date', 'layer_code', 'parent_id'),
			"conditions" => array(
				'Layer.flag' => 1,
				'Layer.layer_code' => str_replace('"', '', explode(':', $parent_id_arr[count($parent_id_arr) - 1])[1])
			),
			'order' => array('Layer.id desc')
		));
		$today = date('Y-m-d');
		$layers = [];
		$type_name = [];
		$user_datas = $this->checkRoleUser();
		$layer_code = explode('/', array_column($user_datas, 'layer_code')[0]);
		$sql = [];
		foreach ($layer_code as $code) {
			$sql[] = 'Layer.parent_id LIKE "%'.$code.'%"';
			$sql[] = 'Layer.layer_code = "'.$code.'"';
		}
		if(!empty($layer_code)) {
			$layer_type_order = $user_datas[0]['layer_type_order'];
			$condi = [];$fields = [];$order = [];$joins = [];
			$condi['Layer.flag'] = 1;
			
			if($layer_type_order <= Setting::LAYER_TYPE_LIMIT) {
				$fields = array('LayerParent.layer_code', 'LayerParent.name_' . $lang_name, 'LayerParent.layer_type_id');
				$condi['Layer.type_order <'] = $layer_data['type_order'];

				array_push($condi, "(".implode(" OR ", $sql).")");
				$joins = array(
					array(
						'table' => 'layers',
						'alias' => 'LayerParent',
						'type' => 'right',
						'conditions' => array(
							'Layer.parent_id LIKE CONCAT("%", LayerParent.layer_code, "%") AND LayerParent.flag = 1'
						)
					)
				);
				$order = array('LayerParent.layer_code');
			}

			$parent_one = $this->Layer->find('list', array(
				'fields' => $fields,
				'conditions' => $condi,
				'joins' => $joins,
				'order' => $order
			));
			$parent_two = $this->Layer->find('list', array(
				'fields' => array('Layer.layer_code', 'Layer.name_' . $lang_name, 'Layer.layer_type_id'),
				'conditions' => $condi,
				'order' => array('Layer.layer_code')
			));
			$parent_data = $parent_one + $parent_two;
			
			$layer_type_name = $this->LayerType->find('list', array(
				'fields' => array('type_order', 'name_' . $lang_name),
				'conditions' => array('LayerType.flag = 1'),
				'group' => array('LayerType.type_order'),
				'order' => array('LayerType.type_order')
			));
			
			for ($i = 0; $i < count($parent_data); $i++) {
				preg_match_all('!\d+!', explode(":", $parent_id_arr[$i])[0], $typeOrder);
				$layer_type_name = $this->LayerType->find('list', array(
					'fields' => array('type_order', 'name_' . $lang_name),
					'conditions' => array('LayerType.flag' => 1, 'LayerType.type_order' => $typeOrder[0][0]),
				));
				$layers['layer_data'][$i] = array_values($parent_data)[$i];
				$type_name[$i] = $layer_type_name;
			}
		}else {
			for ($i = 0; $i < count($parent_id_arr); $i++) {

				preg_match_all('!\d+!', explode(":", $parent_id_arr[$i])[0], $typeOrder);

				$layer_type_id = $this->Layer->find('list', array(
					'fields' => array('layer_code', 'layer_type_id'),
					'conditions' => array(
						'Layer.flag' => 1,
						'Layer.to_date >=' => $today,
						'Layer.type_order' => $typeOrder[0][0],
					), 'group' => array(
						'Layer.layer_type_id',
					)
				));

				$conditions['Layer.flag'] = 1;
				$conditions['Layer.to_date >='] = $today;
				$conditions['Layer.layer_type_id'] = $layer_type_id;

				$data = $this->Layer->find('list', array(
					'fields' => array('layer_code', 'name_' . $lang_name),
					'conditions' => $conditions,
					'order' => array(
						'Layer.layer_code',
					)
				));

				$layer_type_name = $this->LayerType->find('list', array(
					'fields' => array('type_order', 'name_' . $lang_name),
					'conditions' => array('LayerType.flag = 1', 'LayerType.type_order' => $typeOrder[0][0]),
				));

				$layers['layer_data'][$i] = $data;
				$type_name[$i] = $layer_type_name;
			}
		}
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
		if (!empty($layer_type_name)) {
			$response['layer_type_name'] = $type_name;
		}

		$response['from_date'] = $parent_detail[0]['Layer']['from_date'];
		$response['to_date'] = $parent_detail[0]['Layer']['to_date'];
		$response['parent_id'] = $layer_data['parent_id'];
		// $response['has_child'] = $check_has_child;

		#check code already use or not
		$used_code = $this->checkUsedCode($layer_data['layer_code']);
		$response['code_readonly'] = $used_code;
		if ($haveChild == "1") $response['code_readonly'] = true;

		$check_bu_status = $this->Layer->find('count', array(
			'fields' => array('layer_code', 'layer_type_id'),
			'conditions' => array(
				'Layer.flag' => 1,
				'Layer.layer_code' => $parent_detail[0]['Layer']['layer_code'],
				'Layer.bu_status' => 0
			)
		));
		$response['check_bu_status'] = false;
		if ($check_bu_status > 0) $response['check_bu_status'] = true;
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
			$login_id = $this->Session->read('LOGIN_ID');
			$page_no = (in_array($this->request->data('hid_page_no'), array('Layers', 'index'))) ? '' : $this->request->data('hid_page_no');
			$hid_delete_id = $this->request->data('hid_delete_id');
			$has_layer = $this->Layer->find('first', array(
				'conditions' => array(
					//'OR' => array(
					'Layer.id' => $hid_delete_id,
					//'layer_code' => $hid_delete_id,
					//),
					'Layer.flag' => 1
				)
			))['Layer']['layer_code'];

			$type_order = $has_layer['LayerType']['type_order'];

			$users_data = $this->User->find('all', array(
				'fields' => array('layer_code'),
				'conditions' => array(
					'flag' => 1,
				)
			));

			foreach ($users_data as $value) {
				$layer_arr = explode('/', $value['User']['layer_code']);
				if (in_array($has_layer, $layer_arr)) {
					$errorMsg = parent::getErrorMsg('SE144');
					$this->Flash->set($errorMsg, array('key' => 'LayerGroupFail'));
					$this->redirect(Router::url($this->referer(), true));
				}
			}
			if (empty($has_layer)) {
				$errorMsg = parent::getErrorMsg('SE050');
				$this->Flash->set($errorMsg, array('key' => 'LayerGroupFail'));
				$this->redirect(Router::url($this->referer(), true));
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
					'conditions' => array(
						//'OR' => array(
						'Layer.id' => $hid_delete_id,
						// 	'LayerType.layer_code' => $hid_delete_id,
						// )
					)
				));


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
					$this->Flash->set($msg, array('key' => 'LayerGroupSuccess'));
					$this->redirect(Router::url($this->referer(), true));
				} else {
					$errorMsg = parent::getErrorMsg('SE050');
					$this->Flash->set($errorMsg, array('key' => 'LayerGroupFail'));
					$this->redirect(Router::url($this->referer(), true));
				}
				//}
			} catch (Exception $e) {
				CakeLog::write('debug', $e->getMessage() . ' in file ' . __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
				$attachDB->rollback();
				$msg = parent::getErrorMsg('SE050');
				$this->Flash->set($msg, array('key' => 'LayerGroupFail'));
				$this->redirect(Router::url($this->referer(), true));
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

		$lyr_id = $this->Layer->find('first', array(
			"fields" => 'layer_code',
			"conditions" => array(
				'Layer.flag' => 1,
				'Layer.id' => $id,
			),
		))['Layer']['layer_code'];

		#get data by layer id
		$data = $this->Layer->find('all', array(
			'fields' => array('name_en', 'name_jp', 'from_date', 'to_date', 'parent_id'),
			"conditions" => array(
				'Layer.layer_code' => $lyr_id,
				'Layer.flag' => 1
			),
			"order" => array('from_date', 'to_date'),
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
	public function getUpdateParentIDData($layer_no, $new_id, $old_id, $login_id, $today, $from_date, $to_date, $code = '')
	{
		$cur_id = ',"L' . $layer_no . '":"' . $code . '"';
		$new_id = rtrim($new_id, "}") . $cur_id;
		$old_id = rtrim($old_id, "}") . $cur_id;
		$update_data = [];
		$upd_to_date = date('Y-m-d', (strtotime('-1 day', strtotime($from_date))));

		$lg_lists = $this->Layer->find('all', array(
			'conditions' => array(
				'Layer.flag' => 1,
				'Layer.parent_id LIKE ' => $old_id . "%",
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
		$parent_id = '%' . $l_name . '":"' . $l_id . '%';
		$today = date('Y-m-d');

		#get language extension
		if ($this->Session->read('Config.language') == 'eng') {
			$lang_name = 'en';
		} else {
			$lang_name = 'jp';
		}
		if (!empty($l_id)) {
			#get parent detail by selected layer code
			$parent_detail = $this->Layer->find('all', array(
				'fields' => array('from_date', 'to_date', 'layer_code'),
				"conditions" => array(
					'Layer.flag' => 1,
					'Layer.layer_code' => $l_id
				)
			));

			$parent_code = $this->Layer->find('list', array(
				'fields' => array('layer_code', 'name_' . $lang_name),
				"conditions" => array(
					'Layer.flag' => 1,
					'Layer.from_date <=' => $today,
					'Layer.to_date >=' => $today,
				)
			));

			$data['value'] = json_decode($this->Layer->find('list', array(
				'fields' => array('layer_code', 'parent_id'),
				'conditions' => array(
					'Layer.flag' => 1,
					'Layer.from_date <=' => $today,
					'Layer.to_date >=' => $today,
					'Layer.layer_code' => $l_id,
				),
				'order' => array(
					'Layer.id',
				)
			))[$l_id], TRUE);

			foreach ($data['value'] as $key => $code) {
				$data['text'][$code] = $parent_code[$code];
			}
		} else {
			for ($i = 1; $i < $l_no; $i++) {
				$data['value']['L' . $i] = '';
			}
		}
		$data['from_date'] = $parent_detail[0]['Layer']['from_date'];
		$data['to_date'] = $parent_detail[0]['Layer']['to_date'];
		echo json_encode($data);
	}


	/**
	 * get default code
	 *
	 * @author Hein Htet Ko (20220727)
	 * @return string 
	 */

	public function getDefaultCode($layer_no)
	{
		$n1 = 3;
		$n2 = 1;

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

		return $layer_no . $randomString_1 . $randomString_2;
	}

	/**
	 * checkCodeDuplicate
	 *
	 * @author Khin Hnin Myo(28/03/2023)
	 * @param $dup_status, $code
	 * @return boolean
	 */
	public function checkCodeDuplicate($dup_status, $code)
	{
		$check_code = $this->Layer->find('count', array(
			'conditions' => array(
				'flag' => 1,
				'layer_code' => $code
			)
		));
		if ($check_code < 1) $dup_status = false; #code is not duplicated, can save
		return $dup_status;
	}
	/**
	 * checkDataDuplicate
	 *
	 * @author Khin Hnin Myo(28/03/2023)
	 * @param $dup_status, $from_date, $to_date, $name_jp, $name_en, $parent_id, $code
	 * @return boolean
	 */
	public function checkDataDuplicate($dup_status, $from_date, $to_date, $name_jp, $name_en, $parent_id, $code)
	{
		$dup_cond = [];
		$dup_cond['Layer.flag'] = 1;
		$dup_cond['Layer.name_jp'] = $name_jp;
		$dup_cond['Layer.name_en'] = $name_en;
		$dup_cond['Layer.parent_id'] = $parent_id;
		if ($code != '') $dup_cond['Layer.layer_code <>'] = $code;
		$data = $this->Layer->find('all', array(
			'fields' => array('MIN(from_date) AS min_from_date', 'MAX(to_date) AS max_to_date'),
			'conditions' => $dup_cond
		))[0][0];
		if ($data['min_from_date'] > $to_date || $data['max_to_date'] < $from_date) {
			$dup_status = false; #datas are not duplicated, can save
		}
		return $dup_status;
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
	#khin
	public function checkRoleUser() {
		$role_id = $_SESSION['ADMIN_LEVEL_ID'];
		$login_id = $_SESSION['LOGIN_ID'];
		
		$role_users = $this->User->find('all', array(
			'conditions' => array(
				'User.flag' => 1,
				'User.id' => $login_id,
				'Role.id' => $role_id,
				'Role.role_name' => Setting::LAYER_PERMIT_ROLES
			),
			'joins' => array(
				array(
					'table' => 'roles',
					'alias' => 'Role',
					'conditions' => array(
						'User.role_id = Role.id AND User.flag = 1'
					)
				)
			),
			'fields' => array(
				'User.*'
			)
		));
		if(!empty($role_users)) {
			$user_datas = array_column($role_users, 'User');
		}
		return $user_datas;
	}

	public function TabHideShow($lang_name) {
		$conditions['LayerType.flag'] = 1;
		$conditions['LayerType.type_order'] = array_keys(Setting::LAYER_PERMIT_ROLES);
		$layers = $this->LayerType->find('list', array(
			'fields' => array(
				'LayerType.type_order', 'LayerType.name_' . $lang_name
			),
			'conditions' => $conditions,
			'order' => 'LayerType.type_order'
		));
		return $layers;
	}
}
