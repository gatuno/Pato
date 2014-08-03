<?php

class Pato_Boleta extends Gatuf_Model {
	/* Manejador de la tabla de calificaciones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'boleta';
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
			       'relate_name' => 'boleta',
			),
			'evaluacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Evaluacion',
			       'blank' => false,
			),
			'calificacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Float',
			       'blank' => false,
			       'is_null' => true,
			       'decimal_places' => 1,
			       'max_digits' => 5,
			),
		);
		
		$this->_a['idx'] = array (
			'boleta_idx' =>
			array (
			       'col' => 'nrc, alumno, evaluacion',
			       'type' => 'unique',
			),
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
}
