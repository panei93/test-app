<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * InterestCost Controller
 *
 * @property InterestCost $InterestCost
 * @property PaginatorComponent $Paginator
 */
class InterestCostController extends AppController
{
    public $uses = array('InterestCost');

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator');

    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkSettingSession($this->name);
    }

    public function index() {
        $this->layout = 'mastermanagement';

        $interest_costs = Setting::INTEREST_COST;

        $this->paginate = array(
            'limit' => Paging::TABLE_PAGING,
            'conditions' => array(
                'InterestCost.flag' => 1
            ),
            'group' => array(
                'InterestCost.target_year',
                'InterestCost.account_code',
            ),
            'order' => array( 
                'InterestCost.id',
                'InterestCost.target_year',
                'InterestCost.account_code'
            )
        );
        
        $datas = $this->Paginator->paginate('InterestCost');

        if(count($datas) < 1) {
            $no_data = parent::getErrorMsg("SE001");
        }else {
            $count = $this->params['paging']['InterestCost']['count'];
            $pageno = $this->params['paging']['InterestCost']['page'];
            $total_pages = $this->params['paging']['InterestCost']['pageCount'];
            $row_count = parent::getSuccessMsg('SS004', $count);
        }
        $no_data = (count($datas) < 1) ? parent::getErrorMsg("SE001") : '';
        
        $this->set(compact('datas', 'interest_costs', 'no_data', 'count', 'pageno', 'total_pages', 'row_count'));
        $this->render('index');
    }

    public function interestSaveUpdate() {
        if ($this->request->is('post')) {
            $login_id = $this->Session->read('LOGIN_ID');
            $edit_id = $this->request->data('edit_id');
            $target_year = $this->request->data('target_year');
            $account_code = $this->request->data('account_code');
            $rate = $this->request->data('rate');
            $mode = $this->request->data('mode');
            $date = date('Y-m-d H:i:s');
            
            $save_data = [];
            $save_data['target_year'] = $target_year;
            $save_data['account_code'] = $account_code;
            $save_data['flag'] = 1;
            
            $chk_exsit = $this->getInterestDatas('first', $save_data);

            $skip = true;
            if(($mode == 'save' && count($chk_exsit) > 0) || ($mode == 'update' && count($chk_exsit) > 0 && $chk_exsit['InterestCost']['id'] != $edit_id)) {
                $skip = false;
            }
            
            if(!$skip) {
                CakeLog::write('debug', 'The data already exists!  in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE002", [__("データ")]);
                $this->Flash->set($msg, array('key'=>'interestFail'));
                $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
            }else {
                
                $save_data['rate'] = $rate;
                $save_data['updated_by'] = $login_id;
                $save_data['updated_date'] = $date;
                if($mode == 'save') {
                    $save_data['created_by'] = $login_id;
                    $save_data['created_date'] = $date;
                }else {
                    $save_data['id'] = $edit_id;
                }

                $attachDB = $this->InterestCost->getDataSource();
                try{
                    $attachDB->begin();
                    $this->InterestCost->saveAll($save_data);
                    $attachDB->commit();

                    if($mode == 'save') $msg = parent::getSuccessMsg("SS001");
                    else $msg = parent::getSuccessMsg("SS002");
                    
                    $this->Flash->set($msg, array('key'=>'interestOK'));
                    $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
                }catch(Exception $e) {
                    $attachDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());

                    if($mode == 'save') $msg = parent::getSuccessMsg("SS001");
                    else $msg = parent::getSuccessMsg("SS002");
                    
                    $msg = parent::getErrorMsg("SE003");
                    $this->Flash->set($msg, array('key'=>'interestFail'));
                    $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
                }
            }
        }else {
            $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
        }
    }

    public function interestDelete() {
        if ($this->request->is('post')) {
            $login_id = $this->Session->read('LOGIN_ID');
            $edit_id = $this->request->data['edit_id'];
            $date = date('Y-m-d H:i:s');
            
            $delete_data = [];
            $delete_data['id'] = $edit_id;
            $delete_data['flag'] = 1;
            $chk_exsit = $this->getInterestDatas('count', $delete_data);
            if($chk_exsit < 1) {
                CakeLog::write('debug', 'The data already deleted!  in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                $msg = parent::getErrorMsg("SE050");
                $this->Flash->set($msg, array('key'=>'interestFail'));
                $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
            }else {
                $delete_data['flag'] = 0;
                $delete_data['updated_by'] = $login_id;
                $delete_data['updated_date'] = $date;
                $attachDB = $this->InterestCost->getDataSource();
                try{
                    $attachDB->begin();
                    $this->InterestCost->saveAll($delete_data);
                    $attachDB->commit();
                    $msg = parent::getSuccessMsg("SS003");
                    $this->Flash->set($msg, array('key'=>'interestOK'));
                    $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
                }catch(Exception $e) {
                    $attachDB->rollback();
                    CakeLog::write('debug', $e->getMessage().' in file '. __FILE__ . ' on line ' . __LINE__ . ' within the class ' . get_class());
                    $msg = parent::getErrorMsg("SE003");
                    $this->Flash->set($msg, array('key'=>'interestFail'));
                    $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
                }
            }
        }else {
            $this->redirect(array('controller'=>'InterestCost', 'action'=>'index'));
        }
    }

    public function editInterest() {
        parent::checkAjaxRequest($this);
        if ($this->request->is('post')) {
            $edit_id = $this->request->data('id');
            $conditions = [];
            $conditions['flag'] = 1;
            $conditions['id'] = $edit_id;
            $datas = $this->getInterestDatas('first', $conditions);
            echo json_encode($datas);
        }
    }

    public function getInterestDatas($type, $conditions) {

        $datas = $this->InterestCost->find($type, array(
            'conditions' => $conditions
        ));

        return $datas;
    }
}
