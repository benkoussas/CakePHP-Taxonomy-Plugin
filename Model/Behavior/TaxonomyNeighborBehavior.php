<?php
/**
 * TaxonomyNeighbor Model Behavior
 *
 * PHP version 5.3
 * CakePHP 2.2+
 *
 * @package  Taxonomy.TaxonomyNeighbor.Model.Behavior
 * @version  1.0
 * @author   T3RA77 - (based on Grafikart beta plugin https://github.com/Grafikart/CakePHP-Taxonomy)
 * @date 	 August 2012
 * @goal 	 Find Neighbors and Related Terms By Type.
 */
 
class TaxonomyNeighborBehavior extends ModelBehavior {
	
	protected $_defaultSettings = array(
	'neighbors' => true,
	'related' => true,
	'relatedType' => array('Tag', 'Catégorie')
	);
	
		  
    public function setup(Model $Model, $settings) {
        if (!isset($this->settings[$Model->alias])) {
            $this->settings[$Model->alias] = $this->_defaultSettings;
        }
        $this->settings[$Model->alias] = array_merge(
        $this->settings[$Model->alias], (array)$settings);
    }
    
    
    /**
    * Find Neighbors and related terms
    * 	
    *	unload neighbors example (in controller) :	
	*	$this->Node->Behaviors->load('Taxonomy.TaxonomyNeighbors', array('neighbors' => false) );
	*
	*	Change value of relatedType in settings to retrieve your term's type. - 'relatedType' => array('Tag', 'Category');
	*	Don't forget you can change these settings on the fly in your controller.
	*
	*	Data row : 'TaxonomyNeighbors' and 'TaxonomyRelated'.
    *
    **/
    
    
    public function afterFind(Model $Model, $results, $primaryKey = false) {
        $neighbors = $this->settings[$Model->alias]['neighbors'];
        $related = $this->settings[$Model->alias]['related'];
        $relatedType = $this->settings[$Model->alias]['relatedType'];		
    
    	foreach ($results as $key => $row) {
     			
     			if(empty($row[$Model->alias][$Model->primaryKey]) || empty($row[$Model->alias]['lft'])){
     			return false;
     			}
     			
     			$Model->Behaviors->disable('TaxonomyNeighbors');
     			
     			if(!isset($row['Taxonomy']) ){
     			return false;
     			}
     			
     			/** 
     			*
     			* Direct Neighbors with the first same Type
     			* 
     			*/
     			
     			if(isset($neighbors) && $neighbors === true){
     			
     			$term = array_values( $row['Taxonomy'][key($row['Taxonomy'])] );
     			
     			$options = $Model->listByTerm(array(
     			'type' => key($row['Taxonomy']), 
     			'query' => $term[0]['Term'][Configure::read('Taxonomy.field')],
     			'conditions' => array( 
     			$Model->alias.'.lft >' =>  $row[$Model->alias]['lft'],
     			$Model->alias.'.disabled =' => false,
     			),
     			'order' => $Model->alias.'.lft ASC', 
     			'recursive' => -1));
     			
     			$next = $Model->find('first', $options);
     			//debug($next);
     			
     			$options = $Model->listByTerm(array(
     			'type' => key($row['Taxonomy']), 
     			'query' => $term[0]['Term'][Configure::read('Taxonomy.field')],
     			'conditions' => array( 
     			$Model->alias.'.lft <' =>  $row[$Model->alias]['lft'],
     			$Model->alias.'.disabled =' => false
     			),
     			'order' => $Model->alias.'.lft DESC', 
     			'recursive' => -1));
     			$prev = $Model->find('first', $options);
     			
     			$row[$Model->alias]['TaxonomyNeighbors']['next'] = $next;		
     			$results[$key] = $row;
     			
     			$row[$Model->alias]['TaxonomyNeighbors']['prev'] = $prev;		
     			$results[$key] = $row;
     			
     			}
     			
     			/** 
     			*
     			* Related Neighbors by Type AND by 'Name' field
     			* 
     			*/
     			
     			if(isset($related) && $related === true){
     			$query = array();
		     			
		     			
		     			if(isset($row['Taxonomy']) && !empty($row['Taxonomy'])) {
		     				foreach($row['Taxonomy'] as $relatedTypeKey => $relatedTypeValue){
		     					$relatedTypeValue = array_values($relatedTypeValue);
		     					$type[$relatedTypeKey] = $relatedTypeValue[0]['Term']['type'];
		     					$query[$relatedTypeKey] = $relatedTypeValue[0]['Term'][Configure::read('Taxonomy.field')];
		     			}
		     			
		     			$options = $Model->listByTerm(array(
		     			'type' => $relatedType, 
		     			'query' => $query,
		     			'fields' => array('DISTINCT '.$Model->alias.'.'.$Model->primaryKey),
		     			'conditions' => array( 
		     			$Model->alias.'.'.$Model->primaryKey.' !=' =>  $row[$Model->alias][$Model->primaryKey],
		     			$Model->alias.'.disabled =' => false,
		     			),
		     			'limit' => 8,
		     			'order' => $Model->alias.'.lft ASC', 
		     			'recursive' => -1));
		
		     			$relatedResult = $Model->find('all', $options);
		     			
		     			$row[$Model->alias]['TaxonomyRelated'] = $relatedResult;		
		     			$results[$key] = $row;
		     			}
     			
     			}
     			
     			}
     			return $results;
     }
}

?>