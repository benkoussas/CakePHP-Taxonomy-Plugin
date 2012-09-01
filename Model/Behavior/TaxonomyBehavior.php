<?php
/**
 * Taxonomy Model Behavior
 *
 * PHP version 5.3
 * CakePHP 2.2+
 *
 * @package  Taxonomy.Taxonomy.Model.Behavior
 * @version  1.0
 * @author   T3RA77 - (based on Grafikart beta plugin https://github.com/Grafikart/CakePHP-Taxonomy)
 * @date 	 August 2012
 *
 */
App::uses('Taxonomy.TaxonomyFinder', 'Model/Behaviors');
class TaxonomyBehavior extends ModelBehavior {

	public function setup($Model, $options = array() ){
		
		/**
		*
		* hasMany through association.
		* possibility to add a position field in term_relationships table.
		*
		*/
		
		$Model->hasMany['TermR'] = array(
		'className'  => 'Taxonomy.TermR',
		'foreignKey' => 'ref_id',
		'conditions' => 'TermR.ref = "'.$Model->alias.'"'
		);
		
		$Model->TermR->belongsTo[$Model->alias] = array(
		'className'  => $Model->alias,
		'foreignKey' => 'ref_id',
		'conditions' => 'TermR.ref = "'.$Model->alias.'"'
		);
	
	}
	
	public function afterFind(Model $Model, $results){
	
			foreach($results as $key => $result){
			
					if( empty($result[$Model->alias][$Model->primaryKey]) ){	return false; }
			
					if( empty($result['TermR']) ) { return false; }
					
					$query = array();
					
					foreach($result['TermR'] as $termRKey => $termR){
					$query = $Model->TermR->Term->find('first', array(
					'fields' => array('Term.id', 'Term.type', 'Term.title'),
					'conditions' => array('Term.id =' => $termR['term_id']) ) );
					$query = Hash::insert($query, 'TermR', $termR);
					$row[$termRKey] = $query;
					}
					
					if(empty($query)){ return false; }
					$results[$key]['Taxonomy'] = Hash::combine($row, '{n}.Term.id', '{n}', '{n}.Term.type');
			}
		
		//debug($results);
		return $results;
	}
		

	public function addTerm(Model $Model, $name, $type){
		$d = array(
			Configure::read('Taxonomy.field') => $name,
			'type' => $type
		);
		$term = $Model->TermR->Term->find('first',array('conditions' => $d,'fields' => array('id')));
		if(empty($term)){
			$Model->Term->create(); 
			$Model->Term->save($d); 
			$term_id = $Model->Term->id; 
		}else{
			$term_id = $term['Term']['id'];
		}
		$d = array(
			'ref'     => $Model->alias,
			'ref_id'  => $Model->id,
			'term_id' => $term_id
		);
		$count = $Model->TermR->find('count',array('conditions'=>$d));
		if($count == 0){
			$Model->Term->TermR->create(); 
			$Model->Term->TermR->save($d);
		}
		return true;
	}
	


}