<?php
/**
 * TaxonomyFinder Model Behavior
 *
 * PHP version 5.3
 * CakePHP 2.2+
 *
 * @package  Taxonomy.TaxonomyFinder.Model.Behavior
 * @version  1.0
 * @author   T3RA77 - (based on Grafikart beta plugin https://github.com/Grafikart/CakePHP-Taxonomy)
 * @date 	 August 2012
 */
 
class TaxonomyFinderBehavior extends ModelBehavior {
		
		protected $_defaultSettings = array(
		);
		
		public function setup(Model $Model, $settings) {
		    if (!isset($this->settings[$Model->alias])) {
		        $this->settings[$Model->alias] = $this->_defaultSettings;
		    }
		    $this->settings[$Model->alias] = array_merge(
		    $this->settings[$Model->alias], (array)$settings);
		}
		
		/**
		* 
		* List terms
		*
		**/
		public function listTerms(Model $Model){
		
		$args = func_get_args();
		unset($args[0]);
			
		$row = $Model->TermR->Term->find('list',array(
			'fields' => array('id', Configure::read('Taxonomy.field'), 'type'),
			'conditions' => array('type' => $args)
		));
		
		return $row;
		}
		
		
		/**
		* ListByTerm - Joining tables
		* 	params :
		*		type : type of the Term (ex: category) - string - array()
	 	*		query : type of the Term (ex: cakes) - string - array()
	 	*	
	 	*		CakePHP params : fields, conditions, limit, order, recursive.
	 	*
	 	*		
	 	*		Find example (find first node by term - in controller) :
	 	*		$options = $this->Node->listByTerm(array( 'type' => $type, 'query' => $query, 'limit' => $limit, 'recursive' => -1 ));
	 	*		$row = $this->Node->find('first', $options);
	 	*
	 	*		
	 	*		Paginate example (paginate nodes by term - in controller) :
	 	*		$this->paginate = $this->Node->listByTerm(array( 'type' => $type, 'query' => $query, 'limit' => 10, 'order' => 'Node.lft ASC', 'recursive' => -1 ));
	 	*		$this->set('nodes', $this->paginate( $this->Node ));
	 	*
	 	*
	 	**/
	 	
		public function listByTerm(Model $Model, $params = array() ){
		
		$extracted = extract($params);
		
		$options['joins'] = array(
		array('table' => 'term_relationships',
			'alias' => 'TermR',
			'type' => 'inner',
			'conditions' => array(
			$Model->alias.'.'.$Model->primaryKey.' = TermR.ref_id'
			)
		),
		array('table' => 'terms',
			'alias' => 'Term',
			'type' => 'inner',
			'conditions' => array(
			'TermR.term_id = Term.id',
			)
		)
		);
		
		if(isset($fields) && !empty($fields)){
		$options['fields'] = $fields;
		}
		
		$options['conditions'] = array();
		
		if(isset($type) && !empty($type)){
			if(is_array($type)){
				$types = array();
				foreach($type as $key => $value) {
					array_push($types, array('Term.type =' => $value ));
				}
				$typesConditions = array('OR' => $types);
				array_push($options['conditions'], $typesConditions);
				
			} else {
				array_push($options['conditions'], array('Term.type =' => $type));
			}
		}
		
		if(isset($query) && !empty($query)){
			if(is_array($query)){
				$queries = array();
				foreach($query as $key => $value) {
					array_push($queries, array('Term.'.Configure::read('Taxonomy.field').' =' => $value ));
				}
				$queriesConditions = array('OR' => $queries);
				array_push($options['conditions'], $queriesConditions);
				
			} else {
				array_push($options['conditions'], array('Term.'.Configure::read('Taxonomy.field').' =' => $query));
			}
		}
		
		if(isset($conditions) && !empty($conditions)){
		array_push($options['conditions'], $conditions);
		}
		
		if(isset($limit) && !empty($limit)){
		$options['limit'] = $limit;
		}
		
		if(isset($order) && !empty($order)){
		$options['order'] = $order;
		}
		
		if(isset($recursive) && !empty($recursive)){
		$options['recursive'] = $recursive;
		}
		
		return $options;
		}
		
}
?>