<?php

class Pato_Inscripcion extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'inscripciones';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Alumno',
			       'relate_name' => 'inscripciones',
			),
			'carrera' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Carrera',
			),
			'turno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'M',
			),
			'ingreso' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Calendario',
			       'relate_name' => 'ingreso',
			),
			'egreso' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			       'model' => 'Pato_Calendario',
			       'relate_name' => 'egreso',
			),
			'estatus' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Estatus',
			),
		);
		$this->_a['idx'] = array (
			'calificacion_idx' =>
			array (
				   'col' => 'alumno, carrera, ingreso',
				   'type' => 'unique',
			),
		);
	}
} 
