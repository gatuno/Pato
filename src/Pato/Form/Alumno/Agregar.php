<?php

class Pato_Form_Alumno_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['codigo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Código',
				'initial' => '',
				'help_text' => 'El código de alumno de 8 dígitos',
				'max_length' => 8,
				'min_length' => 8,
				'widget_attrs' => array (
					'maxlength' => 8,
					'size' => 12,
				),
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
				'help_text' => 'El nombre o nombres del alumno',
				'max_length' => 70,
				'widget_attrs' => array (
					'maxlength' => 70,
					'size' => 30,
				),
		));
		
		$this->fields['apellido'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Apellido',
				'initial' => '',
				'help_text' => 'Los apellidos del alumno',
				'max_length' => 140,
				'widget_attrs' => array (
					'maxlength' => 140,
					'size' => 30,
				),
		));
		
		$this->fields['sexo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Sexo',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => array ('Masculino' => 'M', 'Femenino' => 'F'),
				),
		));
		
		$this->fields['email'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => '',
				'help_text' => 'Un correo',
		));
		
		$carreras = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $c) {
			$carreras[$c->descripcion] = $c->clave;
		}
		
		$this->fields['inscrip_carrera'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Carrera',
				'initial' => '',
				'help_text' => 'Carrera la que está inscrito',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $carreras,
				),
		));
		
		$calendarios = array ();
		foreach (Gatuf::factory ('Pato_Calendario')->getList () as $c) {
			$calendarios[$c->descripcion] = $c->clave;
		}
		
		$this->fields['inscrip_cal'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario de ingreso',
				'initial' => '',
				'help_text' => 'Calendario de ingreso',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $calendarios,
				)
		));
		
		$estatus = array ();
		foreach (Gatuf::factory ('Pato_Estatus')->getList () as $e) {
			$estatus[$e->descripcion] = $e->clave;
		}
		
		$this->fields['inscrip_estatus'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Estatus del alumno',
				'initial' => '',
				'help_text' => 'Indica el estatus de este alumno en esta carrera',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $estatus,
				)
		));
	}
	
	public function clean_codigo () {
		$codigo = mb_strtoupper($this->cleaned_data['codigo']);
		
		if (!preg_match ('/^\d{8}$/', $codigo)) {
			throw new Gatuf_Form_Invalid ('El código del alumno es incorrecto');
		}
		
		$sql = new Gatuf_SQL ('codigo=%s', array ($codigo));
		$l = Gatuf::factory('Pato_Alumno')->getList(array ('filter' => $sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid (sprintf ('El código %s de alumno especificado ya existe', $codigo));
		}
		
		return $codigo;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$carrera = $this->cleaned_data ['inscrip_carrera'];
		$cal = $this->cleaned_data ['inscrip_cal'];
		$est = $this->cleaned_data ['inscrip_estatus'];
		
		$alumno = new Pato_Alumno ();
		$alumno->setFromFormData ($this->cleaned_data);
		
		$alumno->create ();
		
		$inscripcion = new Pato_Inscripcion ();
		$inscripcion->alumno = $alumno;
		$inscripcion->carrera = new Pato_Carrera ($carrera);
		$inscripcion->ingreso = new Pato_Calendario ($cal);
		$inscripcion->egreso = null;
		$inscripcion->estatus = new Pato_Estatus ($est);
		
		$inscripcion->create ();
		return $alumno;
	}
}
