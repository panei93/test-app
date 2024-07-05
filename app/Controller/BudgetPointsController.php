<?php
App::uses('AppController', 'Controller');
/**
 * BudgetPoints Controller
 *
 * @property BudgetPoint $BudgetPoint
 * @property PaginatorComponent $Paginator
 */
class BudgetPointsController extends AppController {

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
		$this->BudgetPoint->recursive = 0;
		$this->set('budgetPoints', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->BudgetPoint->exists($id)) {
			throw new NotFoundException(__('Invalid budget point'));
		}
		$options = array('conditions' => array('BudgetPoint.' . $this->BudgetPoint->primaryKey => $id));
		$this->set('budgetPoint', $this->BudgetPoint->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->BudgetPoint->create();
			if ($this->BudgetPoint->save($this->request->data)) {
				$this->Flash->success(__('The budget point has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget point could not be saved. Please, try again.'));
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
		if (!$this->BudgetPoint->exists($id)) {
			throw new NotFoundException(__('Invalid budget point'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->BudgetPoint->save($this->request->data)) {
				$this->Flash->success(__('The budget point has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget point could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('BudgetPoint.' . $this->BudgetPoint->primaryKey => $id));
			$this->request->data = $this->BudgetPoint->find('first', $options);
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
		if (!$this->BudgetPoint->exists($id)) {
			throw new NotFoundException(__('Invalid budget point'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->BudgetPoint->delete($id)) {
			$this->Flash->success(__('The budget point has been deleted.'));
		} else {
			$this->Flash->error(__('The budget point could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
