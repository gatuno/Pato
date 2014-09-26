<?php

class Pato_Form_Utils_AltasBajas extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['horarios'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Revisar horarios',
				'initial' => true,
				'help_text' => 'Si se debería revisar por colisiones de horarios al momento de hacer altas',
		));
		
		for ($g = 0; $g < 10; $g++) {
			$this->fields['al_'.$g] = new Gatuf_Form_Field_Varchar (
				array(
					'required' => false,
					'label' => 'Alumno',
					'initial' => '',
					'help_text' => 'El codigo, nombre o apellidos del alumno a matricular',
					'widget_attrs' => array(
						'json' => Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::buscarJSON'),
						'min_length' => 2,
					),
					'widget' => 'Gatuf_Form_Widget_AutoCompleteInput',
			));
			
			$this->fields['nrc_'.$g] = new Gatuf_Form_Field_Integer (
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
			
			$this->fields['tipo_'.$g] = new Gatuf_Form_Field_Integer (
				array (
					'required' => false,
					'label' => 'Tipo',
					'initial' => 1,
					'widget_attrs' => array (
						'choices' => array ('Alta' => 1, 'Baja' => 2),
					),
					'widget' => 'Gatuf_Form_Widget_SelectInput',
			));
		}
	}
	
	public function save ($commit = true) {
		$cambios = array ('opc' => array (), 'altas' => array (), 'bajas' => array ());
		
		$cambios['opc']['horario'] = $this->cleaned_data['horarios'];
		
		for ($g = 0; $g < 10; $g++) {
			if ($this->cleaned_data['al_'.$g] != '' && $this->cleaned_data['nrc_'.$g] != '') {
				if ($this->cleaned_data['tipo_'.$g] == 1) {
					/* Es alta */
					$cambios['altas'][] = array (0 => $this->cleaned_data['nrc_'.$g], 1 => $this->cleaned_data['al_'.$g]);
				} else {
					$cambios['bajas'][] = array (0 => $this->cleaned_data['nrc_'.$g], 1 => $this->cleaned_data['al_'.$g]);
				}
			}
		}
		
		return $cambios;
	}
}
