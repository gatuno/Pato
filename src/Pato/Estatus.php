<?php

class Pato_Estatus extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'estatus';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 5,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'activo' => /* Si este estatus se considera activo */
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => true,
			),
			'oculto' => /* Si este estatus se considera oculto */
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'baja' => /* Si este estatus se considera baja */
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => true,
			),
		);
	}
	
	function __toString () {
		return $this->descripcion.' ('.$this->clave.')';
	}
}
