<?php
App::uses('AppController', 'Controller');
App::import('Controller', 'Common');
/**
 * Menus Controller
 *
 * @property Menu $Menu
 * @property PaginatorComponent $Paginator
 */
class MenusController extends AppController {
    // public $uses = array('User','PasswordHistory',);
    // public $component = array('Session','Flash');
    // public $helpers = array('Html', 'Form');
    
    /**
     * Check Session before render page
     *
     */
    public function beforeFilter()
    {
        parent::CheckSession();
        parent::checkUserStatus();
        parent::checkExpiredUser();
    }

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->layout = "plain";
		$role_id = $_SESSION['ADMIN_LEVEL_ID'];
		#get language extension
		$ext = ($this->Session->read('Config.language') == 'eng') ? '_en' : '_jp';
		$menu_name = 'menu_name'.$ext;
		$menus = $this->Menu->find('list',array(
			'fields' => array($menu_name,'page_name'),
			'conditions' => array(
				'flag' => 1,
			),
			'group' => array($menu_name),
			'order' => 'id'
		));
		$Common = New CommonController();
		$permissions = $Common->checkRolePermission($menus, $menu_name);
    	
		$this->set('menus', $permissions);
	}
	
/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Menu->exists($id)) {
			throw new NotFoundException(__('Invalid menu'));
		}
		$options = array('conditions' => array('Menu.' . $this->Menu->primaryKey => $id));
		$this->set('menu', $this->Menu->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Menu->create();
			if ($this->Menu->save($this->request->data)) {
				$this->Flash->success(__('The menu has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The menu could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Menu->exists($id)) {
			throw new NotFoundException(__('Invalid menu'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Menu->save($this->request->data)) {
				$this->Flash->success(__('The menu has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The menu could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Menu.' . $this->Menu->primaryKey => $id));
			$this->request->data = $this->Menu->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->Menu->exists($id)) {
			throw new NotFoundException(__('Invalid menu'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Menu->delete($id)) {
			$this->Flash->success(__('The menu has been deleted.'));
		} else {
			$this->Flash->error(__('The menu could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}

