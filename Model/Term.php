<?php
App::uses('TaxonomyAppModel', 'Taxonomy.Model');
class Term extends TaxonomyAppModel {

	public $recursive = -1; 
	public $hasMany = array('TermR' => array(
		'className' => 'Taxonomy.TermR',
		'foreignKey' => 'term_id',
		'dependent' => true
	)); 

}