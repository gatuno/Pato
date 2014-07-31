<?php

class Pato_Form_Maestro_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		/* Preparar catalogos */
		$choices_grados = array ('Lic.' => 'L', 'Ing.' => 'I', 'Mtro./Mtra' => 'M', 'Dr./Dra.' => 'D');
		$choices_sex = array ('Masculino' => 'M', 'Femenino' => 'F');
		$choices_tiempo = array ('Profesor de asignatura' => 'a', 'Tiempo completo' => 't', 'Medio tiempo' => 'm');
		
		$this->fields['codigo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Código',
				'initial' => '',
				'help_text' => 'El código del profesor de 6 dígitos',
				'min' => 100000,
				'max' => 999999,
				'widget_attrs' => array (
					'maxlength' => 6,
					'size' => 12,
				),
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
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
				'initial' => '',
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
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_sex
				),
		));
		
		$this->fields['grado'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grado de estudios',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_grados
				),
		));
		
		$this->fields['email'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => '',
				'help_text' => 'Un correo',
		));
		
		$this->fields['tiempo'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Tiempo completo',
				'initial' => 'a',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array(
					'choices' => $choices_tiempo,
				),
		));
	}
	
	public function clean_codigo () {
		$codigo = $this->cleaned_data['codigo'];
		$sql = new Gatuf_SQL ('codigo=%s', array ($codigo));
		$l = Gatuf::factory('Pato_Maestro')->getList(array ('filter' => $sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid (sprintf ('El código "%s" ya está en uso por otro profesor', $codigo));
		}
		
		return $codigo;
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
		
		$maestro = new Pato_Maestro ();
		$maestro->setFromFormData ($this->cleaned_data);
		
		if ($commit) {
			$maestro->create();
		}
		
		return $maestro;
	}
}
