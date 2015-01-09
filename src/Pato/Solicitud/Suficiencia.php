<?php

class Pato_Solicitud_Suficiencia extends Gatuf_Model {
	/* Manejador de la tabla de suficiencias */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'suficiencias';
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
			'materia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Materia',
			       'blank' => false,
			),
			'maestro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Maestro',
			       'blank' => false,
			       'model' => 'Pato_Maestro',
			       'is_null' => true,
			       'default' => null,
			),
			'estatus' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'defaul' => 0,
			),
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
	
	function setCalpfx ($calpfx) {
		$this->_con = Pato_Calendario_getDBForCal ($calpfx);
	}
}
