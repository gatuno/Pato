<?php

class Pato_Form_Calendario_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices_c = array ();
		for ($g = date ('Y') + 1; $g > 1990; $g--) {
			$choices_c[$g] = array ();
			foreach (array ('E' => 'Sep-Dic', 'D' => 'May-Ago', 'C' => 'Ene-Abr') as $key => $desc) {
				$choices_c[$g][$desc] = $g.$key;
			}
		}
		
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'max_length' => 6,
				'widget' => 'Gatuf_Form_Widget_DobleInput',
				'widget_attrs' => array (
					'choices' => $choices_c,
				),
		));
	}
	
	public function clean () {
		/* Verificar que no exista otro calendario con la misma clave */
		$sql = new Gatuf_SQL ('clave=%s', $this->cleaned_data['clave']);
		
		$l = Gatuf::factory ('Pato_Calendario')->getList (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid ('Ya existe otro calendario con la misma clave');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the form in a valid state');
		}
		
		$calendario = new Pato_Calendario ();
		
		$calendario->setFromFormData ($this->cleaned_data);
		$anio = substr ($this->cleaned_data['clave'], 0, 4);
		$clave = substr ($this->cleaned_data['clave'], 4);
		
		$calendario->anio = (int) $anio;
		
		if ($clave == 'C' || $clave == 'D') {
			/* Los "C" y "D" trabajan en el ciclo escolar anterior */
			$calendario->anio_for_show = ((int) $anio) - 1;
		} else if ($clave == 'E') {
			/* Los "E" van en su propio año */
			$calendario->anio_for_show = (int) $anio;
		}
		
		$calendario->letra = $clave;
		$descs = array ('E' => 'Sep-Dic', 'D' => 'May-Ago', 'C' => 'Ene-Abr');
		$calendario->descripcion = $anio.' '.$descs[$clave];
		
		if ($commit) $calendario->create ();
		
		return $calendario;
	}
}
