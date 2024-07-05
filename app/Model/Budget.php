<?php
App::uses('AppModel', 'Model');
/**
 * Budget Model
 *
 * @property BrmTerm $BrmTerm
 * @property BrmAccount $BrmAccount
 */
class Budget extends AppModel {
public $useTable = 'budgets';
}