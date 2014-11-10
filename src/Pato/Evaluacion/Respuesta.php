<?php

class Pato_Evaluacion_Respuesta extends Gatuf_Model {
	/* Manejador de la tabla respuestas de las evaluaciones */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'eval_alum_prof';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'revision' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Alumno',
			),
			'seccion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Seccion',
			),
			'respuestas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Serialized',
			       'blank' => false,
			),
		);
		
		$this->_a['idx'] = array (
			'al_sec_idx' =>
			array (
			       'col' => 'alumno, seccion',
			       'type' => 'unique',
			),
		);
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	}
	
	function setCalpfx ($calpfx) {
		$this->_con = Pato_Calendario_getDBForCal ($calpfx);
		
		$this->_a['views'] = array (
			'paginador' => array (
				'select' => $this->_con->pfx.'secciones_view.*',
				'from' => $this->_con->dbname.'.'.$this->_con->pfx.'secciones_view',
				'props' => array ('materia_desc', 'maestro_nombre', 'maestro_apellido'),
			),
		);
	}
}
