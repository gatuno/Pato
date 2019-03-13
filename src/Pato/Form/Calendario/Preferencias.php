<?php

class Pato_Form_Calendario_Preferencias extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$gconf = new Pato_Calendario_GSettings ();
		$gconf->setApp ('Patricia');
		
		$abierto = $gconf->getVal ('solicitar_suficiencias', false);
		
		$this->fields['suficiencias'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Suficiencias abiertas',
				'initial' => $abierto,
				'help_text' => 'Indica si los alumnos pueden o no solicitar un trámite de suficiencia'
		));
	}
	
	public function save ($commit = true) {
		$gconf = new Pato_Calendario_GSettings ();
		$gconf->setApp ('Patricia');
		
		if ($this->cleaned_data['suficiencias']) {
			$sufi = true;
		} else {
			$sufi = false;
		}
		$gconf->setVal ('solicitar_suficiencias', $sufi);
	}
}
