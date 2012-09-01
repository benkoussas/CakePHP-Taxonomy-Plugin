<?php
App::uses('TaxonomyAppController', 'Taxonomy.Controller');
class TermsController extends TaxonomyAppController {
	
	public function admin_search(){
		
		if($this->request->is('ajax')) {
		Configure::write('debug', 0);
		$this->layout = false;
		$this->autoRender = false;
		
		$terms = $this->Term->find('list',array(
			'conditions' => array(
				Configure::read('Taxonomy.field').' LIKE'=>'%'.$_GET['term'].'%',
				'type' => $_GET['type']
			),
			'fields' => array('id',Configure::read('Taxonomy.field'))
		));
		$json = array(); 
		foreach($terms as $id=>$name){
			$json[] = array('id'=>$id,'label'=>$name);
		}
		
		return json_encode($json);
		} 
	}

	public function admin_delete($id = null){
		
		Configure::write('debug', 0);
		$this->layout = false;
		$this->autoRender = false;
		
		$this->Term->TermR->id = $id;
		$term_id = $this->Term->TermR->field('term_id');
		$this->Term->TermR->delete($id); 

		//Delete term if not used anymore
		$count = $this->Term->TermR->find('count',array(
			'conditions' => array('term_id'=>$term_id)
		));
		if($count == 0){
			$this->Term->delete($term_id); 
		}
		die('ok');
	}

	public function admin_add($ref, $ref_id){
		
		Configure::write('debug', 0);
		$this->layout = false;
		$this->autoRender = false;
	
		$type = $_GET['type']; 
		if( isset($_GET['id']) ){
			$term_id = $_GET['id']; 
		}else{
			$d = array(
				Configure::read('Taxonomy.field') => $_GET['name'],
				'type' => $type
			);
			$term = $this->Term->find('first',array(
				'conditions' => $d,
				'fields' => 'id'
			));
			if(empty($term)){
				$this->Term->save($d); 
				$term_id = $this->Term->id; 
			}else{
				$term_id = $term['Term']['id'];
			}
		}
		$d = array(
			'ref' => $ref,
			'ref_id' => $ref_id,
			'term_id' => $term_id
		);
		$count = $this->Term->TermR->find('count',array('conditions'=>$d));
		
		if($count == 0){
			$this->Term->TermR->save($d);
			$url = Router::url(array('action'=>'delete',$this->Term->TermR->id));
			die('<span class="tag">'.$_GET['name'].' <a href="'.$url.'" class="del-taxonomy">x</a></span>');
		}else{
			die(); 
		}
	}

}