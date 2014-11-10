<?php

class Pato_Form_SeleccionarFecha extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		if (isset ($extra['fecha'])) $fecha = $extra['fecha'];
		else $fecha = '';
		$this->fields['fecha'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'initial' => $fecha,
				'label' => 'Fecha a falsificar',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data ['fecha'];
	}
}
