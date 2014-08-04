<?php

class Pato_Asistencia extends Gatuf_Model {
	/* Manejador de la tabla de calificaciones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'asistencias';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Seccion',
			       'blank' => false,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Alumno',
			       'blank' => false,
			       'relate_name' => 'asistencias',
			),
			'asistencia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
		);
		
		$this->_a['idx'] = array (
			'asistencia_idx' =>
			array (
			       'col' => 'nrc, alumno',
			       'type' => 'unique',
			),
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
}
