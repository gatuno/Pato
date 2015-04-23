<?php

Gatuf::loadFunction ('Admision_DB_getDB');

class Admision_Convocatoria extends Gatuf_Model {
	/* Manejador de la tabla de convocatorias */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'convocatorias';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'calendario' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Calendario',
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
			'apertura' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
			'cierre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
		);
		
		$this->_con = Admision_DB_getDB ();
	}
}
