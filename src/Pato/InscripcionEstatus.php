<?php

class Pato_InscripcionEstatus extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'inscripcion_estatus';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'inscripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Inscripcion',
			),
			'estatus' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Estatus',
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
			       'is_null' => true,
			       'default' => null,
			)
		);
	}
	
	function isActivo () {
		return $this->get_estatus ()->activo;
	}
	
	function __toString () {
		//return $this->descripcion.' ('.$this->clave.')';
		return ((string) $this->get_estatus ());
	}
}
