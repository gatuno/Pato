<?php
Gatuf::loadFunction ('CP_DB_getDB');

class CP_Estado extends Gatuf_Model {
	/* Manejador de la tabla Estados */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'estados';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 70,
			),
			'A2' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 3,
			),
			'A3' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 4,
			),
		);
		
		$this->_con = CP_DB_getDB ();
	}
}
