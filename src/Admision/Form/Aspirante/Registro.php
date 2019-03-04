<?php

class Admision_Form_Aspirante_Registro extends Gatuf_Form {
	private $convocatoria;
	public function initFields($extra=array()) {
		$this->convocatoria = $extra['convocatoria'];
		
		$choices_asp = array ();
		foreach ($this->convocatoria->get_admision_cupocarrera_list () as $cupo) {
			$carrera = $cupo->get_carrera ();
			$choices_asp[$carrera->descripcion] = $cupo->id;
		}
		
		$this->fields['aspiracion'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Programa educativo',
				'help_text' => 'La carrera',
				'initial' => '',
				'choices' => $choices_asp,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$choices_turnos = array ('Matutino' => 'M', 'Vespertino' => 'V');
		
		$this->fields['turno'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Turno',
				'help_text' => '',
				'initial' => 'M',
				'choices' => $choices_turnos,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'help_text' => 'Tu nombre o nombres. Por favor escribelos correctamente con acentos',
				'initial' => '',
				'max_length' => 69,
				'widget_attrs' => array (
					'maxlength' => 69,
					'size' => 25,
				),
		));
		
		$this->fields['apellido'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Apellido',
				'help_text' => 'Por favor escribelos correctamente con acentos',
				'initial' => '',
				'max_length' => 139,
				'widget_attrs' => array (
					'maxlength' => 139,
					'size' => 35,
				),
		));
		
		$this->fields['sexo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Sexo',
				'help_text' => '',
				'initial' => 'F',
				'choices' => array ('Femenino' => 'F', 'Masculino' => 'M'),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['nacimiento'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Fecha de nacimiento',
				'help_text' => '',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
				'widget_attrs' => array (
					'size' => 10,
				),
		));
		
		$choices_paises = array ();
		
		foreach (Gatuf::factory ('CP_Pais')->getList () as $pais) {
			$choices_paises[$pais->nombre] = $pais->id;
		}
		
		$this->fields['pais_nacimiento'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'País de nacimiento',
				'help_text' => '',
				'initial' => 141, /* México preseleccionado */
				'choices' => $choices_paises,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$choices_estados = array ('No aplica - Fuera de México' => 0);
		$choices_municipio = array ();
		foreach (Gatuf::factory ('CP_Estado')->getList () as $estado) {
			$choices_estados[$estado->nombre] = $estado->id;
			
			$choices_municipio[$estado->nombre] = array ();
			foreach ($estado->get_cp_municipio_list (array ('order' => 'nombre ASC')) as $muni) {
				$choices_municipio[$estado->nombre][$muni->nombre] = $muni->id;
			}
		}
		
		$this->fields['estado_nacimiento'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Estado de nacimiento',
				'help_text' => '',
				'initial' => 0,
				'choices' => $choices_estados,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['lugar_nacimiento'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Lugar de nacimiento',
				'help_text' => '',
				'initial' => '',
				'max_length' => 119,
				'widget_attrs' => array (
					'maxlength' => 119,
					/* 'size' => 30, */
				),
		));
		
		$this->fields['nacionalidad'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Nacionalidad',
				'help_text' => '',
				'initial' => 141, /* México preseleccionado */
				'choices' => $choices_paises,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$choices_civil = array ('Soltero/a' => 1, 'Casado/a' => 2, 'Divorciado/a' => 3, 'Viudo/a' => 4, 'Unión libre' => 5);
		$this->fields['estado_civil'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Estado civil',
				'help_text' => '',
				'initial' => 0,
				'choices' => $choices_civil,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['curp'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'CURP',
				'help_text' => 'Tu Clave Única de Registro de Población',
				'initial' => '',
				'max_length' => 24,
				'widget_attrs' => array (
					'maxlength' => 24,
					'size' => 30,
				),
		));
		
		$this->fields['domicilio'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Domicilio',
				'help_text' => 'Ingresa tu calle y número. En caso de tener número interior, agregalo aquí',
				'initial' => '',
				'max_length' => 249,
				'widget_attrs' => array (
					'maxlength' => 249,
					'size' => 70,
				),
		));
		
		$this->fields['codigo_postal'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Codigo postal',
				'help_text' => 'Nos ayudará a identificar en que estado y municipio vives',
				'initial' => '',
				'widget_attrs' => array (
					'maxlength' => 5,
					'size' => 10,
				),
		));
		
		$this->fields['colonia'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Colonia',
				'help_text' => 'En caso de que tu colonia no esté listada, selecciona la opción más cercana',
				'initial' => '',
				'widget_attrs' => array (
					'choices' => array ('Escribe un código postal' => 0),
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['numero_local'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Teléfono local',
				'help_text' => 'Un teléfono local, si es que tienes alguno',
				'initial' => '',
				'max_length' => 20,
				'widget_attrs' => array (
					'maxlength' => 20,
					/* 'size' => 30, */
				),
		));
		
		$this->fields['numero_celular'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Teléfono celular',
				'help_text' => 'Un teléfono celular, si es que tienes alguno',
				'initial' => '',
				'max_length' => 20,
				'widget_attrs' => array (
					'maxlength' => 20,
					/* 'size' => 30, */
				),
		));
		
		$this->fields['email'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo electrónico',
				'help_text' => 'Verifca que esté correctamente escrito, las instrucciones para continuar tu proceso de admisión serán enviadas a este correo',
				'initial' => '',
				'widget_attrs' => array (
					'size' => 30,
				),
		));
		
		$this->fields['trabaja'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => '¿Trabajas?',
				'help_text' => '',
				'initial' => false,
				'widget' => 'Gatuf_Form_Widget_CheckboxInput',
		));
		
		$choices_dis = array (
			'No' => 'No',
			'Ceguera' => 'Ceguera',
			'Baja visión' => 'Baja visión',
			'Sordera' => 'Sordera',
			'Hipoacusia (Debilidad auditiva)' => 'Hipoacusia',
			'Discapacidad Motriz' => 'Discapacidad Motriz',
			'Discapacidad Intelectual' => 'Discapacidad Intelectual',
			'Discapacidad Múltiple' => 'Discapacidad Múltiple',
		);
		
		$this->fields['discapacidad'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => '¿Tienes algún tipo de capacidad diferente?',
				'help_text' => '',
				'initial' => 'No',
				'choices' => $choices_dis,
				'choices_other' => true,
				'choices_other_text' => 'Otra',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$choices_sangre = array ('O Negativo' => 'O-', 'O Positivo' => 'O+', 'A Negativo' => 'A-', 'A Positivo' => 'A+', 'B Negativo' => 'B-', 'B Positivo' => 'B+', 'AB Negativo' => 'AB-', 'AB Positivo' => 'AB+');
		$this->fields['sanguineo_rh'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grupo sanguíneo',
				'initial' => '',
				'help_text' => 'Tu tipo sanguino',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'choices' => $choices_sangre,
		));
		
		$this->fields['escuela'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Escuela de procedencia',
				'help_text' => 'Por favor no utilices abreviaturas',
				'initial' => '',
				'max_length' => 299,
				'widget_attrs' => array (
					'maxlength' => 299,
					'size' => 70,
				),
		));
		
		$this->fields['promedio'] = new Gatuf_Form_Field_Float (
			array (
				'required' => true,
				'label' => 'Promedio',
				'help_text' => '',
				'initial' => '',
				'widget_attrs' => array (
					'size' => 10,
				),
		));
		
		$choices_anio = array ();
		for ($g = date('Y'); $g > 1900; $g--) {
			$choices_anio[$g] = $g;
		}
		
		$this->fields['egreso'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Año de egreso',
				'help_text' => '',
				'initial' => date ('Y'),
				'choices' => $choices_anio,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['escuela_colonia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Colonia de la escuela',
				'help_text' => '',
				'initial' => '',
				'max_length' => 199,
				'widget_attrs' => array (
					'maxlength' => 199,
					/* 'size' => 30, */
				),
		));
		
		$this->fields['escuela_municipio'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Estado y municipio de la escuela',
				'help_text' => '',
				'initial' => '',
				'choices' => $choices_municipio,
				'widget' => 'Gatuf_Form_Widget_DobleInput',
		));
		
		$choices_te = array ('Pública' => 1, 'Privada' => 2);
		
		$this->fields['escuela_tipo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Tipo de escuela',
				'help_text' => '',
				'initial' => 0,
				'choices' => $choices_te,
				'widget' => 'Gatuf_Form_Widget_RadioInput',
		));
		
		$choices_tp = array ('General' => 1, 'Técnico' => 2, 'Sistema abierto' => 3);
		$this->fields['tipo_prepa'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Tipo de Bachillerato',
				'help_text' => '',
				'initial' => 0,
				'choices' => $choices_tp,
				'widget' => 'Gatuf_Form_Widget_RadioInput',
		));
		
		$this->fields['lic_previa'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Licenciatura previa',
				'help_text' => '',
				'initial' => 'No',
				'choices' => array ('No' => 'No'),
				'choices_other' => true,
				'choices_other_text' => 'Sí, ¿Cuál?',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['emergencia_nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre del contacto de emergencia',
				'initial' => '',
				'help_text' => '',
				'max_length' => 100,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => 40,
				),
		));
		
		$this->fields['emergencia_local'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Número local del contacto de emergencia',
				'initial' => '',
				'help_text' => '',
				'max_length' => 10,
				'widget_attrs' => array (
					'maxlength' => 10,
				),
		));
		
		$this->fields['emergencia_celular'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Número celular del contacto de emergencia',
				'initial' => '',
				'help_text' => '',
				'max_length' => 10,
				'widget_attrs' => array (
					'maxlength' => 10,
				),
		));
		
		$this->fields['medio'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => '¿Por qué medio te enteraste de la UPZMG?',
				'help_text' => '',
				'initial' => 'NULL',
				'choices' => array ('Selecciona una opción' => 'NULL', 'Radio' => 'Radio', 'Televisión' => 'Televisión', 'Prensa' => 'Prensa', 'Internet' => 'Internet'),
				'choices_other' => true,
				'choices_other_text' => 'Otros, ¿cuál?',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['informes'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => '¿Qué área de la Universidad te dió informes?',
				'help_text' => '',
				'initial' => 'NULL',
				'choices' => array ('Selecciona una opción' => 'NULL', 'Vinculación' => 'Vinculación', 'Servicios Escolares' => 'Servicios Escolares'),
				'choices_other' => true,
				'choices_other_text' => 'Otra, ¿cuál?',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['entrar'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => '¿Qué hizo que te decidieras a entrar a la UPZMG?',
				'help_text' => '',
				'initial' => 'NULL',
				'choices' => array ('Selecciona una opción' => 'NULL', 'Sus instalaciones' => 'Sus instalaciones', 'El plan de estudios' => 'El plan de estudios', 'La atención recibida' => 'La atención recibida'),
				'choices_other' => true,
				'choices_other_text' => 'Otros, ¿cuál?',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['terms'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Acepto la cláusula de veracidad de datos',
				'initial' => false,
		));
		
		$this->fields['terms2'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Acepto el aviso de confidencialidad',
				'initial' => false,
		));
	}
	
	public function clean_terms() {
		if (!$this->cleaned_data['terms']) {
			throw new Gatuf_Form_Invalid('No puedo aceptar tu solicitud si no me aseguras que tus datos son verídicos');
		}
		return $this->cleaned_data['terms'];
	}
	
	public function clean_terms2() {
		if (!$this->cleaned_data['terms2']) {
			throw new Gatuf_Form_Invalid('No puedo aceptar tu solicitud si no estás de acuerdo con el aviso de confidencialidad');
		}
		return $this->cleaned_data['terms2'];
	}
	
	public function clean_nombre () {
		return trim (mb_convert_case ($this->cleaned_data['nombre'], MB_CASE_TITLE));
	}
	
	public function clean_apellido () {
		return trim (mb_convert_case ($this->cleaned_data['apellido'], MB_CASE_TITLE));
	}
	
	public function clean_curp () {
		if (strlen (trim ($this->cleaned_data['curp'])) != 18) {
			throw new Gatuf_Form_Invalid ('Tu curp debe ser de 18 caracteres exactamente');
		}
		return mb_convert_case ($this->cleaned_data['curp'], MB_CASE_UPPER);
	}
	
	public function clean_colonia () {
		$colonia = $this->cleaned_data['colonia'];
		
		$cp = new CP_CP ();
		if (false === ($cp->get ($colonia))) {
			throw new Gatuf_Form_Invalid ('Colonia/Localidad inválida');
		}
		
		return $colonia;
	}
	
	public function clean_numero_local () {
		$tel_casa = $this->cleaned_data ['numero_local'];
		
		$limpio = str_replace (array (' ', '-'), '', $tel_casa);
		
		if (!preg_match ('/^[0-9]*$/', $limpio)) {
			throw new Gatuf_Form_Invalid ('El teléfono de casa sólo puede estar formado por dígitos');
		}
		
		return $limpio;
	}
	
	public function clean_numero_celular () {
		$tel_cel = $this->cleaned_data ['numero_celular'];
		
		$limpio = str_replace (array (' ', '-'), '', $tel_cel);
		
		if (!preg_match ('/^[0-9]*$/', $limpio)) {
			throw new Gatuf_Form_Invalid ('El teléfono celular sólo puede estar formado por dígitos');
		}
		
		return $limpio;
	}
	
	public function clean_emergencia_local () {
		$tel_casa = $this->cleaned_data ['emergencia_local'];
		
		$limpio = str_replace (array (' ', '-'), '', $tel_casa);
		
		if (!preg_match ('/^[0-9]*$/', $limpio)) {
			throw new Gatuf_Form_Invalid ('El teléfono de casa para tu contacto de emergencia sólo puede estar formado por dígitos');
		}
		
		return $limpio;
	}
	
	public function clean_emergencia_celular () {
		$tel_cel = $this->cleaned_data ['emergencia_celular'];
		
		$limpio = str_replace (array (' ', '-'), '', $tel_cel);
		
		if (!preg_match ('/^[0-9]*$/', $limpio)) {
			throw new Gatuf_Form_Invalid ('El teléfono celular para tu contacto de emergencia sólo puede estar formado por dígitos');
		}
		
		return $limpio;
	}
	
	public function clean () {
		$pais_nacido = $this->cleaned_data['pais_nacimiento'];
		
		if ($pais_nacido == 141) { /* México */
			/* Tiene que elegir un estado */
			$estado_nacido = $this->cleaned_data['estado_nacimiento'];
			if ($estado_nacido == 0) {
				throw new Gatuf_Form_Invalid ('Tienes que seleccionar tu estado de nacimiento');
			}
		} else {
			$this->cleaned_data['estado_nacimiento'] = 0;
		}
		
		$casa = $this->cleaned_data['numero_local'];
		$cel = $this->cleaned_data['numero_celular'];
		
		if ($casa == '' && $cel == '') {
			throw new Gatuf_Form_Invalid ('Debes proporcionar al menos un número local o celular para contactarte');
		}
		
		$casa = $this->cleaned_data['emergencia_local'];
		$cel = $this->cleaned_data['emergencia_celular'];
		
		if ($casa == '' && $cel == '') {
			throw new Gatuf_Form_Invalid ('Debes proporcionar al menos un número local o celular para tu contacto de emergencia');
		}
		
		/* Revisar que no haya otro correo o curp registrados */
		$curp = $this->cleaned_data['curp'];
		$correo = $this->cleaned_data['email'];
		
		$sql = new Gatuf_SQL ('curp=%s OR email=%s', array ($curp, $correo));
		
		/*$aspi = Gatuf::factory ('Admision_Aspirante')->getList (array ('filter' => $sql->gen(), 'count' => true));
		if ($aspi > 0) {
			throw new Gatuf_Form_Invalid ('Ya hay otro aspirante registrado con este mismo curp o correo');
		}*/
		$aspis = Gatuf::factory ('Admision_Aspirante')->getList (array ('filter' => $sql->gen()));
		foreach ($aspis as $aspi) {
			$conv = $aspi->get_aspiracion();
			if ($conv->convocatoria == $this->convocatoria->id) {
				throw new Gatuf_Form_Invalid ('Ya hay otro aspirante registrado con este mismo curp o correo');
			}
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$aspirante = new Admision_Aspirante ();
		$aspirante->setFromFormData ($this->cleaned_data);
		
		if ($this->cleaned_data['estado_nacimiento'] == 0) {
			$aspirante->estado_nacimiento = null;
		}
		
		$aspirante->create ();
		
		$estadistica = new Admision_Estadistica ();
		$estadistica->setFromFormData ($this->cleaned_data);
		
		$estadistica->aspirante = $aspirante;
		
		$estadistica->create ();
		
		return $aspirante;
	}
}
