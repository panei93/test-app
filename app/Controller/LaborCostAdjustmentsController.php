<?php
App::uses('AppController', 'Controller');
/**
 * LaborCostAdjustments Controller
 *
 * @property LaborCostAdjustment $LaborCostAdjustment
 * @property PaginatorComponent $Paginator
 */
class LaborCostAdjustmentsController extends AppController {

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
		$this->LaborCostAdjustment->recursive = 0;
		$this->set('laborCostAdjustments', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->LaborCostAdjustment->exists($id)) {
			throw new NotFoundException(__('Invalid labor cost adjustment'));
		}
		$options = array('conditions' => array('LaborCostAdjustment.' . $this->LaborCostAdjustment->primaryKey => $id));
		$this->set('laborCostAdjustment', $this->LaborCostAdjustment->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->LaborCostAdjustment->create();
			if ($this->LaborCostAdjustment->save($this->request->data)) {
				$this->Flash->success(__('The labor cost adjustment has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The labor cost adjustment could not be saved. Please, try again.'));
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
		if (!$this->LaborCostAdjustment->exists($id)) {
			throw new NotFoundException(__('Invalid labor cost adjustment'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->LaborCostAdjustment->save($this->request->data)) {
				$this->Flash->success(__('The labor cost adjustment has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The labor cost adjustment could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('LaborCostAdjustment.' . $this->LaborCostAdjustment->primaryKey => $id));
			$this->request->data = $this->LaborCostAdjustment->find('first', $options);
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
		if (!$this->LaborCostAdjustment->exists($id)) {
			throw new NotFoundException(__('Invalid labor cost adjustment'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->LaborCostAdjustment->delete($id)) {
			$this->Flash->success(__('The labor cost adjustment has been deleted.'));
		} else {
			$this->Flash->error(__('The labor cost adjustment could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
