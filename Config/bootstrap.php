<?php
$default = array(
	'field' => 'title',
);
Configure::write('Taxonomy', array_merge($default, Configure::read('Taxonomy')?Configure::read('Taxonomy'):array()));
