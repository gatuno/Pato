<?php

class Pato_Form_Calendario_Seleccionar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList (array ('filter' => 'oculto=0', 'order' => 'clave DESC')) as $cal) {
			$choices[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => Pato_Calendario_getDefault (),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the form in a valid state');
		}
		
		$calendario = new Pato_Calendario ($this->cleaned_data['calendario']);
		
		return $calendario;
	}
}
