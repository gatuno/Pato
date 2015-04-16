<?php

class Pato_Aviso extends Gatuf_Model {
	/* Manejador de la tabla de avisos */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'avisos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'maestro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'titulo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'texto' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
			'usuarios' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'model' => 'Pato_User',
			       'relate_name' => 'avisos',
			),
		);
	}
}
