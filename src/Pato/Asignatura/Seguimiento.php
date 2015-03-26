<?php

class Pato_Asignatura_Seguimiento extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'asig_seguimiento';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'plan' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Asignatura_Planeacion',
			       'blank' => false,
			),
			'realizada' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
			'resultado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			),
			'estrategia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
			'evidencia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'notas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
	
	function setCalpfx ($calpfx) {
		$this->_con = Pato_Calendario_getDBForCal ($calpfx);
	}
}
