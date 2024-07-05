<?php
App::uses('AppController', 'Controller');
/**
 * BudgetComps Controller
 *
 * @property BudgetComp $BudgetComp
 * @property PaginatorComponent $Paginator
 */
class BudgetCompsController extends AppController {

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
		$this->BudgetComp->recursive = 0;
		$this->set('budgetComps', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->BudgetComp->exists($id)) {
			throw new NotFoundException(__('Invalid budget comp'));
		}
		$options = array('conditions' => array('BudgetComp.' . $this->BudgetComp->primaryKey => $id));
		$this->set('budgetComp', $this->BudgetComp->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->BudgetComp->create();
			if ($this->BudgetComp->save($this->request->data)) {
				$this->Flash->success(__('The budget comp has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget comp could not be saved. Please, try again.'));
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
		if (!$this->BudgetComp->exists($id)) {
			throw new NotFoundException(__('Invalid budget comp'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->BudgetComp->save($this->request->data)) {
				$this->Flash->success(__('The budget comp has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The budget comp could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('BudgetComp.' . $this->BudgetComp->primaryKey => $id));
			$this->request->data = $this->BudgetComp->find('first', $options);
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
		if (!$this->BudgetComp->exists($id)) {
			throw new NotFoundException(__('Invalid budget comp'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->BudgetComp->delete($id)) {
			$this->Flash->success(__('The budget comp has been deleted.'));
		} else {
			$this->Flash->error(__('The budget comp could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
