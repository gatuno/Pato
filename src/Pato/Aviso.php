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
			'klass' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'data' =>
			array (
			       'type' => 'Gatuf_DB_Field_Serialized',
			       'blank' => false,
			),
			'alumnos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => false,
			       'model' => 'Pato_Alumno',
			       'relate_name' => 'avisos',
			),
			'maestros' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => false,
			       'model' => 'Pato_Maestro',
			       'relate_name' => 'avisos',
			),
		);
	}
}
