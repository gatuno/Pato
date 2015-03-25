<?php
Gatuf::loadFunction ('Admin_DB_getDB');

class Admin_Biblioteca_Prestamo extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'biblio_prestamo';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_con = Admin_DB_getDB ();
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => false,
			),
			'biblioteca' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Admin_Biblioteca',
			       'blank' => false,
			),
			'usuario_salida' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_User',
			),
			'usuario_regreso' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_User',
			       'is_null' => true,
			       'default' => null,
			),
			'maestro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Maestro',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			),
			'carrera' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Carrera',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			),
			'alumno' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Alumno',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			),
			'salon' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Salon',
			       'blank' => false,
			),
			'equipos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'model' => 'Admin_Biblioteca_Equipo',
			       'blank' => false,
			       'relate_name' => 'prestamos',
			),
			'salida' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
			'regreso' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			),
			'notas' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
		);
	}
}
