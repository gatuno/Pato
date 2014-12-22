<?php
Gatuf::loadFunction ('Admin_DB_getDB');

class Admin_SI_Laboratorio extends Gatuf_Model {
	/* Manejador de la tabla de areas administrativas */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'si_laboratorios';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_con = Admin_DB_getDB ();
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 50,
			),
		);
	}
}
