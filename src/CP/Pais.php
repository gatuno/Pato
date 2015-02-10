<?php
Gatuf::loadFunction ('CP_DB_getDB');

class CP_Pais extends Gatuf_Model {
	/* Manejador de la tabla Paises */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'paises';
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
			'nombre_ingles' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
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
			'prefijo_tel' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 10,
			),
		);
		
		$this->_con = CP_DB_getDB ();
	}
}
