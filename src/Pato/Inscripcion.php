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
			/*'estatus' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Estatus',
			),*/
		);
		$this->_a['idx'] = array (
			'carrera_idx' =>
			array (
				   'col' => 'alumno, carrera, ingreso',
				   'type' => 'unique',
			),
		);
	}
	
	public function get_current_estatus () {
		$estatus = $this->get_pato_inscripcionestatus_list (array ('filter' => 'fin IS NULL'));
		
		if (count ($estatus) != 1) {
			throw new Exception ('Alto. No debería pasar. Este alumno no tiene su estatus correcto con respecto a su inscripcion');
		}
		
		return $estatus[0];
	}
} 
