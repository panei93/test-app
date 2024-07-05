<?php
App::uses('AppController', 'Controller');
/**
 * BudgetHyokas Controller
 *
 * @property BudgetHyoka $BudgetHyoka
 * @property PaginatorComponent $Paginator
 */
class BudgetHyokasController extends AppController {

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
		$this->BudgetHyoka->recursive = 0;
		$this->set('budgetHyokas', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->BudgetHyoka->exists($id)) {
			throw new NotFoundException(__('Invalid budget hyoka'));
		}
		$options = array('conditions' => array('BudgetHyoka.' . $this->BudgetHyoka->primaryKey => $id));
		$this->set('budgetHyoka', $this->BudgetHyoka->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->BudgetHyoka->create();
			if ($this->BudgetHyoka->save($this->request->data)) {
				$this->Flash->success(__('The budget hyoka has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget hyoka could not be saved. Please, try again.'));
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
		if (!$this->BudgetHyoka->exists($id)) {
			throw new NotFoundException(__('Invalid budget hyoka'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->BudgetHyoka->save($this->request->data)) {
				$this->Flash->success(__('The budget hyoka has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget hyoka could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('BudgetHyoka.' . $this->BudgetHyoka->primaryKey => $id));
			$this->request->data = $this->BudgetHyoka->find('first', $options);
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
		if (!$this->BudgetHyoka->exists($id)) {
			throw new NotFoundException(__('Invalid budget hyoka'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->BudgetHyoka->delete($id)) {
			$this->Flash->success(__('The budget hyoka has been deleted.'));
		} else {
			$this->Flash->error(__('The budget hyoka could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
