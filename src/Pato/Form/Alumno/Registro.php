<?php

class Pato_Form_Alumno_Registro extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		for ($g = 1; $g <= 10; $g++) {
			$this->fields['nrc'.$g] = new Gatuf_Form_Field_Integer (
				array (
					'required' => false,
					'label' => 'NRC',
					'initial' => '',
					'max' => 99999,
					'min' => 0,
					'widget_attrs' => array(
						'maxlength' => 5,
						'size' => 6,
					),
			));
		}
	}
	
	public function save () {
		$nrcs = array ();
		
		for ($g = 1; $g <= 10; $g++) {
			if ($this->cleaned_data['nrc'.$g] != 0) {
				$nrcs[] = $this->cleaned_data['nrc'.$g];
			}
		}
		
		return $nrcs;
	}
}
