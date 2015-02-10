<?php
Gatuf::loadFunction ('Admision_DB_getDB');

class Admision_Aspirante extends Gatuf_Model {
	/* Manejador de la tabla Aspirantes */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'aspirantes';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'aspiracion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Admision_CupoCarrera',
			       'blank' => false,
			),
			'token' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 10,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 70,
			),
			'apellido' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 140,
			),
			'sexo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => 'M'
			),
			'nacimiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
			'pais_nacimiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Pais',
			),
			'estado_nacimiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Estado',
			       'is_null' => true,
			       'default' => null,
			),
			'lugar_nacimiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 120,
			),
			'nacionalidad' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Pais',
			),
			'estado_civil' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0, /* 1 = Soltero, 2 Casado, 3 Divorciado, 4 Viudo, 5 Unión libre */
			),
			'curp' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 25,
			),
			'domicilio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 250,
			),
			'codigo_postal' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_CP',
			),
			'colonia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'numero_local' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 10,
			       'blank' => false,
			),
			'numero_celular' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 10,
			       'blank' => false,
			),
			'email' =>
			array (
			       'type' => 'Gatuf_DB_Field_Email',
			       'blank' => false,
			),
			'trabaja' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			),
			'discapacidad' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 100,
			),
			'sanguineo_rh' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'size' => 2,
			       'blank' => false,
			),
			'escuela' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'promedio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Float',
			       'blank' => false,
			       'decimal_places' => 1,
			),
			'egreso' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'escuela_colonia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'escuela_municipio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_municipio',
			),
			'escuela_tipo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0, /* 1 = Pública, 2 = Privada */
			),
			'tipo_prepa' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0, /* 1 = General, 2 = Técnico, 3 = Sistema abierto */
			),
			'lic_previa' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'emergencia_nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 100,
			       'default' => '',
			       'blank' => false,
			),
			'emergencia_local' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 10,
			       'blank' => false,
			),
			'emergencia_celular' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 10,
			       'blank' => false,
			),
			'create_time' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
			'print_time' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			       'is_null' => true,
			       'default' => null,
			),
			'foto' =>
			array (
			       'type' => 'Gatuf_DB_Field_File',
			       'blank' => false,
			),
		);
		
		$this->_con = Admision_DB_getDB ();
	}
	
	function preSave ($create=true) {
		if ($create) {
			$this->create_time = date ('Y-m-d H:i:s');
			
			/* Generar una contraseña aleatoria de 6 letras */
			$this->token = Gatuf_Utils::getPassword (6);
		}
	}
}
