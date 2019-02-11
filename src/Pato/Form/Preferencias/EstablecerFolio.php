<?php

class Pato_Form_Preferencias_EstablecerFolio extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		if (isset ($extra['numero'])) $inicio = $extra['numero'];
		else $inicio = 1;
		$this->fields['numero'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'initial' => $inicio,
				'min' => 1,
				'label' => 'Foliador',
				'help_text' => 'Cambia el número del foliador inicial',
				'widget_attrs' => array (
					'size' => 5,
				),
		));
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data ['numero'];
	}
}
