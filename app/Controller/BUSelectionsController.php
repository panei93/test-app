<?php

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
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Microsoft\Graph\Model\Call;

App::uses('Controller', 'Controller');
App::import('Controller', 'Common');

/**
 * SampleSelections Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 * @Design By Nu Nu Lwin
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class BUSelectionsController extends AppController
{
    public $uses = array('LayerType', 'Layer', 'BuTerm', 'User', 'Menu', 'Permission');
    public $components = array('Session', 'RequestHandler');
    public $helpers = array('Html', 'Form');

    public $language,
        $maxPermission,
        $menus,
        $permissions,
        $users,
        $layers,
        $layerTypes,
        $layerSetting,
        $buLayers,
        $groupLayers,
        $layerOne,
        $layerTwo,
        $childTypeOrder,
        $LOGIN_ID,
        $ROLE_ID;

    public function beforeFilter()
    {
        parent::checkUserStatus();

        if (!$this->request->is('Ajax')) {
            $this->dataLoad();
        }
    }

    /**
     * get all the data available for form 
     * 
     * @date    09-28-2023
     * @author  Thura Win
     * @param   void
     * @return  void
     */
    public function dataLoad()
    {
        $this->setGlobalVariables();

        /**
         * org layercodes logic
         */
        // $conditions = array();

        // if ($this->maxPermission > 0) { // if the user don't have whole company permission.
        //     $layerCodes = $this->getLayerCodes($this->users[0]['User']['layer_code']); // get layers as an array

        //     foreach ($layerCodes as $code) {
        //         $layerKey = array_key_exists('layerOne', $code) ? 'layerOne' : 'layerTwo';

        //         $conditions['OR'][] = array('AND' => array(
        //             array('layer_code LIKE' => '%' . $code[$layerKey] . '%'),
        //             array('type_order' => $this->{$layerKey})
        //         ));
        //     }
        // } else {
        //     $conditions['OR'] = array(
        //         array('type_order' => $this->layerOne),
        //         array('type_order' => $this->layerTwo)
        //     );
        // }

        $conditions = $this->getLayerCodes($this->users[0]['User']['layer_code']);

        if ($this->maxPermission == 0) {
            $this->layers = $this->Layer->find('all', array(
                'conditions' => array(
                    'Layer.bu_status' => 1,
                    'OR' => array(
                        array('type_order' => $this->layerOne),
                        array('type_order' => $this->layerTwo)
                    ),
                    'NOT' => array(
                        'Layer.flag' => 0,
                    ),
                ),
                'order' => array(
                    'layer_order' => 'ASC',
                    'layer_code' => 'ASC'
                )
            ));
        } else {
            $this->layers = $this->Layer->find('all', array(
                'conditions' => array(
                    'Layer.bu_status' => 1,
                    $conditions,
                    'NOT' => array(
                        'Layer.flag' => 0,
                    ),
                ),
                'order' => array(
                    'layer_order' => 'ASC',
                    'layer_code' => 'ASC'
                )
            ));
        }
    }


    /**
     * set all the values for the global variables
     * 
     * @date    09-28-2023
     * @author  Thura Win
     * @param   void
     * @return  void 
     */
    public function setGlobalVariables()
    {
        $this->layerOne = Setting::LAYER_SETTING['BuSelections'];
        $this->layerTwo = ($this->layerOne + 1);

        $this->LOGIN_ID = $this->Session->read('LOGIN_ID'); // get the user id
        $this->ROLE_ID = $this->Session->read('ADMIN_LEVEL_ID'); // get the user's role id
        $this->users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $this->LOGIN_ID,
                'NOT' => array(
                    'User.flag' => 0,
                ),
            ),
        )); // get the user's layer codes

        $this->language = $this->Session->read('Config.language') ? $this->Session->read('Config.language') : 'jp';

        $this->layerTypes = $this->LayerType->find('all', array(
            "fields" => array(
                'name_en',
                'name_jp',
                'type_order',
            ),
            'conditions' => array(
                'type_order' => array(
                    $this->layerOne,
                    $this->layerTwo,
                ),
                'NOT' => array(
                    'LayerType.flag' => 0,
                ),
            ),
        ));

        $this->menus = $this->Menu->find('all', array(
            'conditions' => array(
                'menu_name_en' => 'BU Analysis',
                'method' => 'index',
                'NOT' => array(
                    'Menu.flag' => 0,
                ),
            ),
        ));

        $allMenuId = array();

        foreach ($this->menus as $menu) {
            $allMenuId[] = $menu['Menu']['id'];
        }

        $this->permissions = $this->Permission->find('first', array(
            'conditions' => array(
                'menu_id' => $allMenuId,
                'role_id' => $this->ROLE_ID,
            ), 'order' => array(
                'limit' => 'ASC',
            ),
        ));

        $this->maxPermission = $this->permissions['Permission']['limit'];
    }

    public function index()
    {
        $this->layout = 'buanalysis';
        $BU_LABEL = $this->language == 'eng' ? $this->layerTypes[0]['LayerType']['name_en'] : $this->layerTypes[0]['LayerType']['name_jp'];
        $GROUP_LABEL = $this->language == 'eng' ? $this->layerTypes[1]['LayerType']['name_en'] : $this->layerTypes[1]['LayerType']['name_jp'];
        $CHOOSE_TERM = parent::getErrorMsg('SE072', __("Term Name"));
        $CHOOSE_BU = parent::getErrorMsg('SE072', $BU_LABEL);
        $CHOOSE_GROUP = parent::getErrorMsg('SE072', $GROUP_LABEL);

        $TERMS = $this->getBuTerms();
        $LAYERS = $this->getLayers();

        $this->Session->write('buTerms', $TERMS);

        $this->set([
            'LANG' => $this->language,
            'TERMS' => $TERMS,
            'LAYERS' => $LAYERS,
            'BU_LABEL' => $BU_LABEL,
            'GROUP_LABEL' => $GROUP_LABEL,
            'layerSetting' => $this->layerOne,
            'CHOOSE_TERM' => $CHOOSE_TERM,
            'CHOOSE_BU' => $CHOOSE_BU,
            'CHOOSE_GROUP' => $CHOOSE_GROUP,
            'LOGIN_ID' => $this->LOGIN_ID,
        ]);
        $this->render('index');
    }

    /**
     * 
     * 
     * @date    09-22-2023
     * @author  Thura Win
     * @param   null
     * @return  void
     */
    public function add()
    {
        parent::checkAjaxRequest($this);
        if($this->Session->check('SEARCH_LABOR_COST.target_year')) {
            $this->Session->delete('SEARCH_LABOR_COST.target_year');
        }
        $formValues = null;
        $formValues = $this->request->data['formValues'];

        $buTerms = $this->Session->read('buTerms');
        $term_id_lists = array_column($buTerms, 'id');
        $terms = $buTerms[array_search($formValues['term'], $term_id_lists)];
        
        $allLayerYears = $this->getYears($terms['id'], $terms['budget_year']);

        $this->Session->write('TERM_ID', $terms['id']);
        $this->Session->write('BU_TERM_ID', $terms['id']);
        $this->Session->write('TERM_NAME', $terms['term_name']);
        $this->Session->write('yearListOnTerm', $allLayerYears);
        $this->Session->write('SELECTED_BU', $formValues['bu']);
        $this->Session->write('SELECTED_GROUP', $formValues['group']);
        $this->Session->write('BudgetTargetYear', $terms['budget_year']);
        $this->Session->write('SEARCH_LABOR_COST.layer_code', $formValues['group']);
        $this->Session->write('SELECTION', 'SET');
        
        $message = __('Data selection Successfully!');
        $response = array('status' => 'success', 'message' => $message);
        echo json_encode($response);
    }


    /**
     *  get all the BuTerms 
     * 
     * @date    09-15-2023
     * @author  Thura Win
     * @param   null
     * @return  json || array
     */
    public function getBuTerms()
    {
        if ($this->request->is('ajax')) {
            $this->autoRender = false; // Disable rendering of views
        }

        $terms = $this->BuTerm->find('all', array(
            'conditions' => array(
                'NOT' => array(
                    'BuTerm.flag' => 0,
                ),
            ),
        ));

        $buTerms = Hash::extract($terms, "{n}.BuTerm"); // Removing the BuTerm Layer From Every index of the array.

        if (!$this->request->is('ajax')) {
            return $buTerms;
        }

        echo json_encode($buTerms);
    }

    /**
     *  get all the Layers 
     * 
     * @date    09-15-2023
     * @author  Thura Win
     * @param   null
     * @return  json || array
     */
    public function getLayers()
    {
        if ($this->request->is('Ajax')) {
            $this->autoRender = false; // Disable rendering of views
        }

        $newLayers = Hash::extract($this->layers, '{n}.Layer');

        if (!$this->request->is('Ajax')) {
            return $newLayers;
        }

        echo json_encode($newLayers);
    }

    /**
     * get the layer codes for layerOne and layerTwo
     * 
     * @date    09-27-2023
     * @author  Thura Win
     * @param   string $orgLayerCode
     * @return  array $layerCodes
     */
    public function getLayerCodes($layerCodes)
    {
        $maxUserPermission  = $this->maxPermission;
        $orgLayerCodes = explode('/', $layerCodes);

        $conditions = [];
        foreach ($orgLayerCodes as $orgLayerCode) {

            $childLayerCode = $this->Layer->find('all', array(
                'conditions' => array(
                    'Layer.layer_code' => $orgLayerCode,
                ),
            ));

            $childParentCodes = json_decode($childLayerCode[0]['Layer']['parent_id'], true); // true for associative array
            $this->childTypeOrder = $childLayerCode[0]['Layer']['type_order'];
            if ($maxUserPermission == 1) { // if the user has the same Department Permission
                if ($maxUserPermission == $this->childTypeOrder) { // means the layer_code in user table is the department
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('parent_id LIKE' => '%' . $orgLayerCode . '%'),
                            array('type_order' => $this->layerOne)
                        )
                    );
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('parent_id LIKE' => '%' . $orgLayerCode . '%'),
                            array('type_order' => $this->layerTwo)
                        )
                    );
                } else {
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('parent_id LIKE' => '%' . $childParentCodes['L1'] . '%'),
                            array('type_order' => $this->layerOne)
                        )
                    );
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('parent_id LIKE' => '%' . $childParentCodes['L1'] . '%'),
                            array('type_order' => $this->layerTwo)
                        )
                    );
                }
            }

            if ($maxUserPermission == 2) { // if the user has the same BU permission
                if ($maxUserPermission == $this->childTypeOrder) { // means the layer_code in user table is the BU
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('layer_code LIKE' => '%' . $orgLayerCode . '%'),
                            array('type_order' => $this->layerOne)
                        )
                    );
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('parent_id LIKE' => '%' . $orgLayerCode . '%'),
                            array('type_order' => $this->layerTwo)
                        )
                    );
                } else {
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('layer_code LIKE' => '%' . $childParentCodes['L2'] . '%'),
                            array('type_order' => $this->layerOne)
                        )
                    );
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('parent_id LIKE' => '%' . $childParentCodes['L2'] . '%'),
                            array('type_order' => $this->layerTwo)
                        )
                    );
                }
            }

            if ($maxUserPermission == 3) { // if the user has the same Group permission
                if ($maxUserPermission == $this->childTypeOrder) { // means the layer_code in user table is the group 
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('layer_code LIKE' => '%' . $childParentCodes['L2'] . '%'),
                            array('type_order' => $this->layerOne)
                        )
                    );
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('layer_code LIKE' => '%' . $orgLayerCode . '%'),
                            array('type_order' => $this->layerTwo)
                        )
                    );
                } else {
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('layer_code LIKE' => '%' . $childParentCodes['L2'] . '%'),
                            array('type_order' => $this->layerOne)
                        )
                    );
                    $conditions['OR'][] = array(
                        'AND' => array(
                            array('layer_code LIKE' => '%' . $childParentCodes['L3'] . '%'),
                            array('type_order' => $this->layerTwo)
                        )
                    );
                }
            }
        }

        if ($this->maxPermission == 0) {
            $conditions = [];
        }

        return $conditions;

        /**
         * Org Logic for generating the LayerCodes
         * 
         */
        // $orgLayerCodes = explode('/', $orgLayerCode);
        // $layerCodes = [];

        // switch ($this->maxPermission) { // Org Logic
        //     case 1: // if the user has the department permission
        //         foreach ($orgLayerCodes as $layerCode) {
        //             $layerCodes[]['layerOne'] = substr($layerCode, 0, 3);
        //             $layerCodes[]['layerTwo'] = substr($layerCode, 0, 3);
        //         }
        //         break;

        //     case 2: // if the user has the bu permission
        //         foreach ($orgLayerCodes as $layerCode) {
        //             $layerCodes[]['layerOne'] = substr($layerCode, 0, 4);
        //             $layerCodes[]['layerTwo'] = substr($layerCode, 0, 4);
        //         }
        //         break;

        //     case 3: // if the user has the group permission
        //         foreach ($orgLayerCodes as $layerCode) {
        //             $layerCodes[]['layerOne'] = substr($layerCode, 0, 4);
        //             $layerCodes[]['layerTwo'] = substr($layerCode, 0, 6);
        //         }
        //         break;

        //     default: // if the user has the whole company permission
        //         foreach ($orgLayerCodes as $layerCode) {
        //             $layerCodes[]['layerOne'] = substr($layerCode, 0, 2);
        //             $layerCodes[]['layerTwo'] = substr($layerCode, 0, 2);
        //         }
        //         break;
        // }
        // return $layerCodes;

    }

    public function getYears($term, $budgetYear)
    {
        $Common = new CommonController();

        $year1 = $budgetYear - 3;
        $year2 = $budgetYear - 2;
        $year3 = $budgetYear - 1;
        $year4 = $budgetYear;
        $year5 = $budgetYear + 1;
        $year6 = $budgetYear + 2;

        $years =  array($year1, $year2, $year3, $year4, $year5, $year6);

        $yearList = [];
        foreach ($years as $year) {
            $start_month = $Common->getYearMonth($year, $term, 'start');
            $end_month = $Common->getYearMonth($year, $term, 'end');
            $yearList[$year] = array($start_month, $end_month);
        }
        return $yearList;
    }
}
