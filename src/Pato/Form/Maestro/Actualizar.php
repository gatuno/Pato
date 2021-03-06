<?php

class Pato_Form_Maestro_Actualizar extends Gatuf_Form {
	private $maestro;
	
	public function initFields ($extra = array ()) {
		$this->maestro = $extra['maestro'];
		
		/* Preparar catalogos */
		$choices_grados = array ('Lic.' => 'L', 'Ing.' => 'I', 'Mtro./Mtra' => 'M', 'Dr./Dra.' => 'D');
		$choices_sex = array ('Masculino' => 'M', 'Femenino' => 'F');
		$choices_tiempo = array ('Profesor de asignatura' => 'a', 'Tiempo completo' => 't', 'Medio tiempo' => 'm');
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => $this->maestro->nombre,
				'help_text' => 'El nombre o nombres del profesor',
				'max_length' => 50,
				'widget_attrs' => array (
					'maxlength' => 50,
					'size' => 30,
				),
		));
		
		$this->fields['apellido'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Apellido',
				'initial' => $this->maestro->apellido,
				'help_text' => 'Los apellidos del profesor',
				'max_length' => 100,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => 30,
				),
		));
		
		$this->fields['sexo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Sexo',
				'initial' => $this->maestro->sexo,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_sex,
				),
		));
		
		$this->fields['grado'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grado de estudios',
				'initial' => $this->maestro->grado,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_grados,
				),
		));
		
		$this->fields['email'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => $this->maestro->email,
				'help_text' => 'Un correo',
		));
		
		$this->fields['tiempo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Tiempo completo',
				'initial' => ($this->maestro->tiempo === null) ? 'a' : $this->maestro->tiempo,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_tiempo,
				),
		));
	}
	
	public function clean_tiempo () {
		if ($this->cleaned_data['tiempo'] == 'a') {
			return null;
		}
		
		return $this->cleaned_data['tiempo'];
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the model from and invalid form.');
		}
		
		$this->maestro->setFromFormData ($this->cleaned_data);
		
		if ($commit) {
			$this->maestro->update();
		}
		
		return $this->maestro;
	}
}
