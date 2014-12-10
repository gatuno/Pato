<?php

class Pato_Form_Materia_Seleccionar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Materia')->getList (array ('order' => 'descripcion ASC')) as $m) {
			$choices[$m->descripcion.' ('.$m->clave.')'] = $m->clave;
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Materia',
				'initial' => '',
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
		
		$materia = new Pato_Materia ($this->cleaned_data['materia']);
		
		return $materia;
	}
}
