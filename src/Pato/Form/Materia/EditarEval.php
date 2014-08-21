<?php

class Pato_Form_Materia_EditarEval extends Gatuf_Form {
	private $porcentaje;
	
	public function initFields($extra=array()) {
		$choices = array ();
		$this->porcentaje = $extra['porcentaje'];
		
		$this->fields['porcentaje'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Porcentaje',
				'initial' => $this->porcentaje->porcentaje,
				'help_text' => 'La ponderación que recibe esta forma de evaluación',
				'min' => 1,
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->porcentaje->setFromFormData ($this->cleaned_data);
		
		$this->porcentaje->update ();
		
		return $this->porcentaje;
	}
}

