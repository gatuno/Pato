<?php
Gatuf::loadFunction ('Admin_DB_getDB');

class Admin_Biblioteca_Equipo extends Gatuf_Model {
	public $_model = __CLASS__;
	private $c_prestado = null;
	
	function init () {
		$this->_a['table'] = 'biblio_equipo';
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
			'tipo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false, /* 1 = Cañon, 2 = Extension, 3 = Laptop, 4 = Bocinas */
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 50,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'activo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			),
		);
	}
	
	public function display_tipo () {
		switch ($this->tipo) {
			case 1:
				return 'Cañon';
			case 2:
				return 'Extensión';
			case 3:
				return 'Laptop';
			case 4:
				return 'Bocinas';
			default:
				return 'Otros/Desconocido';
		}
	}
	
	public function prestado () {
		if ($this->c_prestado !== null) return $this->c_prestado;
		
		$prestamos = $this->get_prestamos_list (array ('filter' => 'regreso IS NULL', 'count' => true));
		
		$this->c_prestado = ($prestamos > 0);
		return $this->c_prestado;
	}
}
