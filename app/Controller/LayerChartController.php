<?php

App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * ChemicalAccountController
 * @author 
 */

class LayerChartController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('LayerType', 'AccountType');
    public $components = array('Session', 'Flash', 'Paginator');
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkSettingSession($this->name);

    }
    public function index()
    {
        $this->layout = 'mastermanagement';
        try {
            $lang_name = ($_SESSION['Config']['language'] == 'jpn') ? 'jp' : 'en';
            $today = date('Y-m-d');
            $maxi_type = $this->LayerType->find('first',array(
                'fields' => 'MAX(type_order) as maxi_type',
                'conditions' => array(
                    'LayerType.flag' => 1
                )
            ))[0]['maxi_type'];
            $this->Layer->virtualFields['layer_data'] = 'CONCAT(Layer.layer_code, "/", Layer.name_'.$lang_name.')';
            $type1 = $this->Layer->find('list',array(
                'fields' => array('Layer.id', 'layer_data'),
                'conditions' => array(
                    'Layer.flag' => 1,
                    'Layer.from_date <=' => $today,
                    'Layer.to_date >=' => $today,
                    'Layer.type_order' => 1
                ),
                'order' => array('Layer.id')
            ));
            $lists = [];
            $this->Layer->virtualFields['layer_datas'] = 'CONCAT(Layer.layer_code, "/", Layer.name_'.$lang_name.')';
            
            for ($i = 2; $i <= $maxi_type; $i++) {
                if($i-1 == 1) {
                    $lists[] = ${'type'.($i-1)};
                    foreach (${'type'.($i-1)} as $key => $value) {
                        ${'type'.$i}[$value] = $this->Layer->find('list',array(
                            'fields' => array('Layer.id', 'layer_data'),
                            'conditions' => array(
                                'Layer.flag' => 1,
                                'Layer.from_date <=' => $today,
                                'Layer.to_date >=' => $today,
                                'Layer.type_order' => $i,
                                'Layer.parent_id LIKE' => '%'.explode('/', $value)[0].'%'
                            ),
                            'order' => array('Layer.id')
                        ));
                    }
                }else {
                    foreach (${'type'.($i-1)} as $key => $value) {
                        foreach ($value as $key22 => $value22) {
                            ${'type'.$i}[$value22] = $this->Layer->find('list',array(
                                'fields' => array('Layer.id', 'layer_data'),
                                'conditions' => array(
                                    'Layer.flag' => 1,
                                    'Layer.from_date <=' => $today,
                                    'Layer.to_date >=' => $today,
                                    'Layer.type_order' => $i,
                                    'Layer.parent_id LIKE' => '%'.explode('/', $value22)[0].'%'
                                ),
                                'order' => array('Layer.id')
                            ));
                        }
                    }
                }
                $lists[] = ${'type'.($i)};
            }
            /*$type6 = $this->Layer->find('list',array(
                'fields' => array('Layer.id', 'layer_data', 'Layer.parent_id'),
                'conditions' => array(
                    'Layer.flag' => 1,
                    'Layer.from_date <=' => $today,
                    'Layer.to_date >=' => $today,
                    'Layer.type_order' => 6
                ),
                'order' => array('Layer.id')
            ));
            $layer_list = $this->Layer->find('list',array(
                'fields' => array('Layer.layer_code', 'layer_data'),
                'conditions' => array(
                    'Layer.flag' => 1,
                    'Layer.from_date <=' => $today,
                    'Layer.to_date >=' => $today,
                    // 'Layer.type_order' => 6
                ),
                'order' => array('Layer.id')
            ));
            
            foreach ($type6 as $key => $value) {
                $parent = json_decode($key, TRUE);
                $one = '';
                $newArray[$layer_list[$parent['L1']]][$layer_list[$parent['L2']]][$layer_list[$parent['L3']]][$layer_list[$parent['L4']]][$layer_list[$parent['L5']]] = $value;
            }*/
            
            if(empty($lists)) {
                $no_data = parent::getErrorMsg('SE001');
            }else {
                $no_data = "";
                $_SESSION['LCHARTLISTS'] = $lists;
            }
            
            $this->set(compact('lists', 'no_data', 'maxi_type', 'newArray'));
            $this->render('index');
        } catch (Exception $e) {
            CakeLog::write('debug', $e->getMessage() . ' in file ' . FILE . ' on line ' . LINE . ' within the class ' . get_class());
            $this->redirect(array('controller' => 'LayerChart', 'action' => 'index'));
        }
    }

    public function layerChart_pdf() {
        $lists = $_SESSION['LCHARTLISTS'];
        $this->set('lists', $lists);
        // $this->redirect(array('controller'=>'LayerChart', 'action'=>'index'));
        
    }
}
