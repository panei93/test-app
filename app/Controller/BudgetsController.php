<?php
App::uses('AppController', 'Controller');
/**
 * Budgets Controller
 *
 * @property Budget $Budget
 * @property PaginatorComponent $Paginator
 */
class BudgetsController extends AppController {

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
		$this->Budget->recursive = 0;
		$this->set('budgets', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Budget->exists($id)) {
			throw new NotFoundException(__('Invalid budget'));
		}
		$options = array('conditions' => array('Budget.' . $this->Budget->primaryKey => $id));
		$this->set('budget', $this->Budget->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Budget->create();
			if ($this->Budget->save($this->request->data)) {
				$this->Flash->success(__('The budget has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget could not be saved. Please, try again.'));
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
		if (!$this->Budget->exists($id)) {
			throw new NotFoundException(__('Invalid budget'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Budget->save($this->request->data)) {
				$this->Flash->success(__('The budget has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Budget.' . $this->Budget->primaryKey => $id));
			$this->request->data = $this->Budget->find('first', $options);
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
		if (!$this->Budget->exists($id)) {
			throw new NotFoundException(__('Invalid budget'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Budget->delete($id)) {
			$this->Flash->success(__('The budget has been deleted.'));
		} else {
			$this->Flash->error(__('The budget could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
