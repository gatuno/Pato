<?php

class Admision_Form_Convocatoria_AgregarCupo extends Gatuf_Form {
	private $convocatoria;
	public function initFields($extra=array()) {
		$this->convocatoria = $extra['convocatoria'];
		
		$usadas = array ();
		$cupos = $this->convocatoria->get_admision_cupocarrera_list ();
		foreach ($cupos as $cupo) {
			$usadas[] = $cupo->carrera;
		}
		
		$choices = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $carrera) {
			if (!in_array ($carrera->clave, $usadas)) {
				$choices[$carrera->descripcion] = $carrera->clave;
			}
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Carrera',
				'help_text' => '',
				'initial' => '',
				'choices' => $choices,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['cupo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Cupo',
				'help_text' => '¿Cuántos espacios de esta carrera deberían ser abiertos?',
				'initial' => 10,
				'min' => 1,
		));
	}
	
	public function save ($commit=true) {
		$cupo = new Admision_CupoCarrera ();
		
		$cupo->setFromFormData ($this->cleaned_data);
		$cupo->convocatoria = $this->convocatoria;
		
		$cupo->create ();
		
		return $cupo;
	}
}
