<?php

class Admision_Form_SeleccionarFechaHora extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['fechahora'] = new Gatuf_Form_Field_Datetime (
			array (
				'required' => true,
				'initial' => '',
				'label' => 'Fecha y hora',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data ['fechahora'];
	}
}
