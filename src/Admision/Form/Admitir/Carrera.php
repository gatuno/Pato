<?php

class Admision_Form_Admitir_Carrera extends Gatuf_Form {
	private $alumnos;
	private $cupo_carrera;
	public function initFields($extra=array()) {
		$this->alumnos = $extra['alumnos'];
		$this->cupo_carrera = $extra['cupo_carrera'];
		
		$choices_turno = array ('Pendiente' => 'U', 'Matutino' => 'M', 'Vespertino' => 'V');
		$choices_admision = array ('Pendiente' => 0, 'Admitido' => 1, 'No admitido' => 2);
		
		foreach ($this->alumnos as $aspirante) {
			$this->fields['turno_final_'.$aspirante->id] = new Gatuf_Form_Field_Varchar (
				array (
					'required' => true,
					'label' => 'Turno final',
					'help_text' => 'El turno en el que al final queda el alumno',
					'initial' => (is_null ($aspirante->turno_final) ? 'U' : $aspirante->turno_final),
					'choices' => $choices_turno,
					'widget' => 'Gatuf_Form_Widget_SelectInput',
			));
			
			$this->fields['admision_'.$aspirante->id] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => 'Estado',
					'help_text' => 'Su estatus de admisión',
					'initial' => $aspirante->admision,
					'choices' => $choices_admision,
					'widget' => 'Gatuf_Form_Widget_SelectInput',
					'widget_attrs' => array (
						'onchange' => "update_cupos ();",
					),
			));
		}
	}
	
	public function clean () {
		foreach ($this->alumnos as $aspi) {
			$admitido = $this->cleaned_data['admision_'.$aspi->id];
			$turno = $this->cleaned_data['turno_final_'.$aspi->id];
			if ($admitido == 1 && $turno == 'U') {
				throw new Gatuf_Form_Invalid ('El aspirante '.$aspi->id.' no tiene turno final seleccionado');
			}
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		foreach ($this->alumnos as $aspi) {
			$admitido = $this->cleaned_data['admision_'.$aspi->id];
			$turno = $this->cleaned_data['turno_final_'.$aspi->id];
			
			if ($turno == 'U' || $admitido != 1) $turno = null;
			
			$aspi->turno_final = $turno;
			$aspi->admision = $admitido;
			
			$aspi->update ();
		}
	}
}
