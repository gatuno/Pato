<?php

class Pato_Alumno extends Gatuf_Model {
	/* Manejador de la tabla Alumnos */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'alumnos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 8,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 70,
			),
			'apellido' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 140,
			),
			'sexo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 'M'
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	function getUser () {
		$sql = new Gatuf_SQL ('login=%s', $this->codigo);
		$this->user = Gatuf::factory ('Pato_User')->getOne (array ('filter' => $sql->gen ()));
	}
	
	public function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
}
