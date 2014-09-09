<?php

class Pato_Kardex extends Gatuf_Model {
	/* Manejador de la tabla de calificaciones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'kardex';
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
			       'relate_name' => 'kardex',
			),
			'materia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Materia',
			       'blank' => false,
			),
			'nrc' => /* No como foreign porque la integridad referencial podría fallar */
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'is_null' => true,
			),
			'calendario' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Calendario',
			       'blank' => false,
			),
			'gpe' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_GPE',
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
			'aprobada' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'creada' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
			'actualizada' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
		);
		
		$this->_a['idx'] = array (
			'kardex_idx' =>
			array (
			       'col' => 'alumno, materia, calendario, gpe',
			       'type' => 'unique',
			),
		);
	}
	
	function preSave ($create = true) {
		if ($create) {
			/* Tomar el timestamp actual */
			$this->creada = gmdate ('Y-m-d H:i:s');
		}
		
		$this->actualizada = gmdate ('Y-m-d H:i:s');
	}
}
