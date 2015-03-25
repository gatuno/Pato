<?php

class Admin_Form_Biblioteca_Equipo_Regresar extends Gatuf_Form {
	private $prestamo;
	
	public function initFields ($extra=array()) {
		$this->prestamo = $extra['prestamo'];
		$this->fields['notas'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Notas',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->prestamo->setFromFormData ($this->cleaned_data);
		
		if ($commit) $this->prestamo->update ();
		return $this->prestamo;
	}
}
