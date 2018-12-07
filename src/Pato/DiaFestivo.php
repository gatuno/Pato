<?php

class Pato_DiaFestivo extends Gatuf_Model {
	/* Manejador de la tabla de dias festivos */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'dias_festivos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'inicio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
			'fin' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
			'admvos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'acad' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
		);
	}
}
