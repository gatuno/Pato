<?php

class Pato_Form_Alumno_CambiarAgenda extends Gatuf_Form {
	private $agenda;
	
	public function initFields ($extra = array ()) {
		$this->agenda = $extra['agenda'];
		$this->fields['inicio'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => true,
				'label' => 'Apertura',
				'initial' => $this->agenda->inicio,
				'help_text' => 'Fecha y hora de la apertura',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => true,
				'label' => 'Cierre',
				'initial' => $this->agenda->fin,
				'help_text' => 'Fecha y hora del cierre',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
	}
	
	public function save ($commit = true) {
		$this->agenda->setFromFormData ($this->cleaned_data);
		
		if ($commit) {
			$this->agenda->update ();
		}
		
		return $this->agenda;
	}
}
