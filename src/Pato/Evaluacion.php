<?php

class Pato_Evaluacion extends Gatuf_Model {
	/* Manejador de la tabla de evaluaciones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'evaluaciones';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'grupo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_GPE',
			       'blank' => false,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
			'maestro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => true,
			),
		);
		
		$this->default_order = 'grupo ASC, descripcion ASC';
	}
	
	function __toString () {
		return $this->descripcion;
	}
}
