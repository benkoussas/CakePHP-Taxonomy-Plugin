<?php
App::uses('TaxonomyAppModel', 'Taxonomy.Model');
class TermR extends TaxonomyAppModel {

	public $useTable = 'term_relationships';
	public $recursive = -1; 
	public $belongsTo = array(
		'Term' => array(
			'className' => 'Taxonomy.Term',
			'foreignKey' => 'term_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		),
		
	);
	
}