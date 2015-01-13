<?php

class Pato_PerfilAlumno extends Gatuf_Model {
	/* Manejador de la tabla Alumnos */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'perfil_alumnos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
		    'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Alumno',
			       'blank' => false,
			),
			/* Parte del perfil del alumno */
			'nacimiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			),
			'seguro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
			'numero_seguro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 30,
			       'default' => '',
			       'blank' => false,
			),
			'sanguineo_rh' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'size' => 2,
			       'default' => null,
			       'is_null' => true,
			       'blank' => false,
			),
			'alergias' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 300,
			       'default' => '',
			       'blank' => false,
			),
			'medicacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 300,
			       'default' => '',
			       'blank' => false,
			),
			'patologias' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 300,
			       'default' => '',
			       'blank' => false,
			),
			'emergencia_nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 100,
			       'default' => '',
			       'blank' => false,
			),
			'emergencia_local' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 10,
			       'blank' => false,
			),
			'emergencia_celular' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 10,
			       'blank' => false,
			),
			'gestacion_partos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'default' => 0,
			       'blank' => false,
			),
			'gestacion_cesareas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'default' => 0,
			       'blank' => false,
			),
			'gestacion_abortos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'default' => 0,
			       'blank' => false,
			),
			'anticonceptivos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'default' => 0,
			       'blank' => false,
			),
			'last_update' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'editable' => false,
			       'is_null' => true,
			       'default' => null,
			),
		);
	}
	
	public function preSave ($create = true) {
		$this->last_update = date ('Y-m-d H:i:s');
	}
}
