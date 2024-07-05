<?php
App::uses('AppModel', 'Model');
/**
 * Picture Model
 *
 */
class Picture extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'picture_type' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'file_path' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'flag' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'created_by' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'updated_by' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'created_date' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'updated_date' => array(
			'datetime' => array(
				'rule' => array('datetime'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	/**
	 * Overridden paginate method
	 * (assets join picutres) UNION (picutres join assets)
	 * SELECT field name must be same
	 */
	public function paginate(
		$conditions, 
		$fields, 
		$order, 
		$limit, 
		$page = 1, 
		$recursive = null, 
		$extra = array()
	) {
		$recursive = -1;
		// Mandatory to have
		$this->useTable = false;

		$conditions = $conditions ? " WHERE " . $conditions : '';

		$sql = "SELECT * FROM (
			SELECT a.asset_no, a.asset_name, p.file_path,  p.picture_name, p.picture_type, p.id FROM assets as a
			LEFT JOIN pictures as p ON p.picture_name = a.asset_no
			GROUP BY a.asset_no

			UNION

			SELECT a.asset_no, a.asset_name, p.file_path,  p.picture_name, p.picture_type, p.id FROM pictures as p
			LEFT JOIN assets as a ON a.asset_no = p.picture_name WHERE p.flag = 1
		) AS pictures" . $conditions . " LIMIT " . (($page - 1) * $limit) . ", " . $limit;
		$results = $this->query($sql);
		return $results;
	}

	/**
	* Overridden paginateCount method
	*/
	public function paginateCount($conditions = null, 
		$recursive = 0, 
		$extra = array()
	) {
		$conditions = $conditions ? " WHERE " . $conditions : '';
		$sql = "SELECT * FROM (
			SELECT a.asset_no, a.asset_name, p.file_path,  p.picture_name, p.picture_type, p.id FROM assets as a
			LEFT JOIN pictures as p ON p.picture_name = a.asset_no
			GROUP BY a.asset_no

			UNION

			SELECT a.asset_no, a.asset_name, p.file_path,  p.picture_name, p.picture_type, p.id FROM pictures as p
			LEFT JOIN assets as a ON a.asset_no = p.picture_name WHERE p.flag = 1
		) AS picutres" . $conditions;

		$this->recursive = $recursive;
		$results = $this->query($sql);

		return count($results);
	}
}
