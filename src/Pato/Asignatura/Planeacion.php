<?php

class Pato_Asignatura_Planeacion extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'asig_planeacion';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Seccion',
			       'blank' => false,
			),
			'programada' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
			'unidad' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'resultado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
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
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
	
	function setCalpfx ($calpfx) {
		$this->_con = Pato_Calendario_getDBForCal ($calpfx);
	}
	
	function getSeguimiento () {
		$segs = $this->get_pato_asignatura_seguimiento_list ();
		
		if (count ($segs) == 0) {
			return null;
		} else {
			return $segs[0];
		}
	}
}
