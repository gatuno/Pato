<?php
Gatuf::loadFunction ('CP_DB_getDB');

class CP_Asentamiento extends Gatuf_Model {
	/* Manejador de la tabla de asentamientos */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'asentamientos';
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
			       'size' => 100,
			),
		);
		
		$this->_con = CP_DB_getDB ();
	}
}
