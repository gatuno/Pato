<?php

class Pato_Form_Alumno_CrearAgenda extends Gatuf_Form {
	private $alumno;
	
	public function initFields ($extra = array ()) {
		$this->alumno = $extra['alumno'];
		
		$this->fields['inicio'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => true,
				'label' => 'Apertura',
				'initial' => '',
				'help_text' => 'Fecha y hora de la apertura',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => true,
				'label' => 'Cierre',
				'initial' => '',
				'help_text' => 'Fecha y hora del cierre',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
	}
	
	public function save ($commit = true) {
		$agenda = new Pato_Agenda ();
		
		$agenda->alumno = $this->alumno;
		$agenda->setFromFormData ($this->cleaned_data);
		
		if ($commit) {
			$agenda->create ();
		}
		
		return $agenda;
	}
}
