<?php

class Pato_Form_Calendario_Preferencias extends Gatuf_Form {
	private $cal;
	public function initFields ($extra = array ()) {
		$this->cal = $extra['cal'];
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$abierto = $gconf->getVal ('suficiencias_abierta_'.$this->cal->clave, false);
		
		$this->fields['suficiencias'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Suficiencias abiertas',
				'initial' => $abierto,
				'help_text' => 'Indica si los alumnos pueden o no solicitar un trámite de suficiencia'
		));
		
		$abierto = $gconf->getVal ('planeacion_asignatura_'.$this->cal->clave, false);
		
		$this->fields['planeacion_asig'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Planeacion de las asignaturas',
				'initial' => $abierto,
				'help_text' => 'Indica si los profesores pueden programar la planeación de la asignatura',
		));
	}
	
	public function save ($commit = true) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		if ($this->cleaned_data['suficiencias']) {
			$sufi = true;
		} else {
			$sufi = false;
		}
		$gconf->setVal ('suficiencias_abierta_'.$this->cal->clave, $sufi);
		
		if ($this->cleaned_data['planeacion_asig']) {
			$eval = true;
		} else {
			$eval = false;
		}
		
		$gconf->setVal ('planeacion_asignatura_'.$this->cal->clave, $eval);
	}
}
