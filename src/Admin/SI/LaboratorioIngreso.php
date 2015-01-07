<?php
Gatuf::loadFunction ('Admin_DB_getDB');

class Admin_SI_LaboratorioIngreso extends Gatuf_Model {
	/* Manejador de la tabla de areas administrativas */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'si_lab_ingreso';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_con = Admin_DB_getDB ();
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'laboratorio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Admin_SI_Laboratorio',
			       'blank' => false,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Alumno',
			       'blank' => false,
			),
			'tipo' => /* e para entrada, s para salida */
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			),
			'hora' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
		);
	}
}
