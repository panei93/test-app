<?php

/**
 * Author: Pan Ei Phyo
 * Common functions for all controller
 */
App::uses('AppController', 'Controller');

class PermissionsController extends AppController
{
    public $uses = array('Permissions','User','Menu');

    public function getUserPermission($user_level, $user_id)
    {
        $data = array();
        $pages = $this->Menu->find('all', array(
            'fields' => 'page_name, method, id',
            'conditions' => array('flag'=>1)
            //'group' => 'page_name',
        ));
        
        #set default return data
        foreach ($pages as $key=>$value) {
            //pr($value);
            $pageName = $value['Menu']['page_name'];
            $method = $value['Menu']['method'];
            if($method == 'index') $method = 'Read';
            $data[$pageName.ucfirst($method).'Limit'] = false;
        }
        //pr($data);exit;

        #get permissions by admin level
        $fiels = array('Menus.id', 'Menus.page_name', 'Menus.method', 'Permissions.id', 'Permissions.limit');
        $permissions = $this->Permissions->find('all', array(
            'fields' => $fiels,
            'conditions' => array(
                'role_id' => $user_level
            ),
            'joins' => array(
                array(
                    'table' => 'menus',
                    'alias' => 'Menus',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Menus.id = Permissions.menu_id AND Menus.flag = 1'
                    )
                ),
            )
        ));
        
        foreach ($permissions as $permission) {
            $pageName = $permission['Menus']['page_name'];
            $method = $permission['Menus']['method'];
            if($method == 'index') $method = 'Read';
            $limit = $permission['Permissions']['limit'];
            $data[$pageName.ucfirst($method).'Limit'] = $limit;
        }
        return $data;
    }

    public function checkPermission($user_level, $page) {
        $this->Permission->virtualFields['data'] = "CONCAT(Menu.page_name, Menu.method,Permission.limit)";
        $permit = $this->Permission->find('all', array(
            'fields' => array('Menu.method','Permission.limit'),
            'conditions' => array(
                'role_id' => $user_level,
                'Menu.page_name' => $page
            ),
            'contain' => array('Menu.page_name','Menu.method')
        ));
        foreach($permit as $permit_data) {
            $data_arr[] = $page.$permit_data['Menu']['method'].$permit_data['Permission']['limit'];
        }
        return $data_arr;
        
    }
}
