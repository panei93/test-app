<?php
App::uses('AppController', 'Controller');
/**
 * BudgetSngs Controller
 *
 * @property BudgetSng $BudgetSng
 * @property PaginatorComponent $Paginator
 */
class BudgetSngsController extends AppController {

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
		$this->BudgetSng->recursive = 0;
		$this->set('budgetSngs', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->BudgetSng->exists($id)) {
			throw new NotFoundException(__('Invalid budget sng'));
		}
		$options = array('conditions' => array('BudgetSng.' . $this->BudgetSng->primaryKey => $id));
		$this->set('budgetSng', $this->BudgetSng->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->BudgetSng->create();
			if ($this->BudgetSng->save($this->request->data)) {
				$this->Flash->success(__('The budget sng has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget sng could not be saved. Please, try again.'));
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
		if (!$this->BudgetSng->exists($id)) {
			throw new NotFoundException(__('Invalid budget sng'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->BudgetSng->save($this->request->data)) {
				$this->Flash->success(__('The budget sng has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget sng could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('BudgetSng.' . $this->BudgetSng->primaryKey => $id));
			$this->request->data = $this->BudgetSng->find('first', $options);
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
		if (!$this->BudgetSng->exists($id)) {
			throw new NotFoundException(__('Invalid budget sng'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->BudgetSng->delete($id)) {
			$this->Flash->success(__('The budget sng has been deleted.'));
		} else {
			$this->Flash->error(__('The budget sng could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
