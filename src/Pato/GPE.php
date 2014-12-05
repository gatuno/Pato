<?php

class Pato_GPE extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'grupo_evaluaciones';
		$this->_a['model'] = __CLASS__;
		
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 30,
			),
			'secciones' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 20,
			       'default' => '',
			),
		);
	}
	
	function __toString () {
		return $this->descripcion;
	}
}
