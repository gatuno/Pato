<?php

class Pato_Form_Materia_AgregarCarrera extends Gatuf_Form {
	private $materia;
	public function initFields($extra=array()) {
		$this->materia = $extra['materia'];
		
		$assoc_carreras = $this->materia->get_carreras_list ();
		$ya = array ();
		foreach ($assoc_carreras as $as_c) {
			$ya[$as_c->clave] = true;
		}
		$carreras = Gatuf::factory('Pato_Carrera')->getList ();
		$choices = array ();
		foreach ($carreras as $carrera) {
			if (!isset ($ya[$carrera->clave])) $choices[$carrera->descripcion] = $carrera->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Carrera',
				'initial' => '',
				'help_text' => 'Elija la carrera a asociar',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$carrera = new Pato_Carrera ($this->cleaned_data['carrera']);
		
		if ($commit) 
		$this->materia->setAssoc($carrera);
		
		return $this->materia;
	}
}
