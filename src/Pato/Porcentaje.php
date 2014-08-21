<?php

class Pato_Porcentaje extends Gatuf_Model {
	/* Manejador de la tabla de porcentajes */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'porcentajes';
		$this->_a['model'] = __CLASS__;
		$this->_primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'materia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Materia',
			       'relate_name' => 'porcentajes',
			),
			'evaluacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Evaluacion',
			),
			'porcentaje' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 1
			),
			'abierto' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => true,
			       'default' => 1,
			),
			'apertura' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'is_null' => true,
			       'default' => null,
			),
			'cierre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'is_null' => true,
			       'default' => null,
			),
		);
		
		$this->_a['idx'] = array (
			'porcentaje_idx' =>
			array (
			       'col' => 'materia, evaluacion',
			       'type' => 'unique',
			),
		);
		
		$this->default_order = 'evaluacion ASC';
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
}
