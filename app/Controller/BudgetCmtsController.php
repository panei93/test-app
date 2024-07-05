<?php
App::uses('AppController', 'Controller');
/**
 * BudgetCmts Controller
 *
 * @property BudgetCmt $BudgetCmt
 * @property PaginatorComponent $Paginator
 */
class BudgetCmtsController extends AppController {

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
		$this->BudgetCmt->recursive = 0;
		$this->set('budgetCmts', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->BudgetCmt->exists($id)) {
			throw new NotFoundException(__('Invalid budget cmt'));
		}
		$options = array('conditions' => array('BudgetCmt.' . $this->BudgetCmt->primaryKey => $id));
		$this->set('budgetCmt', $this->BudgetCmt->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->BudgetCmt->create();
			if ($this->BudgetCmt->save($this->request->data)) {
				$this->Flash->success(__('The budget cmt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget cmt could not be saved. Please, try again.'));
			}
		}
		$budgets = $this->BudgetCmt->Budget->find('list');
		$this->set(compact('budgets'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->BudgetCmt->exists($id)) {
			throw new NotFoundException(__('Invalid budget cmt'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->BudgetCmt->save($this->request->data)) {
				$this->Flash->success(__('The budget cmt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget cmt could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('BudgetCmt.' . $this->BudgetCmt->primaryKey => $id));
			$this->request->data = $this->BudgetCmt->find('first', $options);
		}
		$budgets = $this->BudgetCmt->Budget->find('list');
		$this->set(compact('budgets'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->BudgetCmt->exists($id)) {
			throw new NotFoundException(__('Invalid budget cmt'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->BudgetCmt->delete($id)) {
			$this->Flash->success(__('The budget cmt has been deleted.'));
		} else {
			$this->Flash->error(__('The budget cmt could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
