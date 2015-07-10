<?php

class Pato_Agenda extends Gatuf_Model {
	/* Manejador de la tabla de las agendas de los alumnos */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'agenda';
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
			       'relate_name' => 'agenda',
			),
			'inicio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
			'fin' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
		);
		
		$this->_a['idx'] = array (
			'alumno_idx' =>
			array (
			       'col' => 'alumno',
			       'type' => 'unique',
			),
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
	
	function setCal ($cal) {
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
}
