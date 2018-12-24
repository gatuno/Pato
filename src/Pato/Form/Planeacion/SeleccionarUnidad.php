<?php

class Pato_Form_Planeacion_SeleccionarUnidad extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->materia = $extra['materia'];
		$this->maestro = $extra['maestro'];
		
		$sql = new Gatuf_SQL ('maestro=%s AND materia=%s', array ($this->maestro->codigo, $this->materia->clave));
		$unidades = Gatuf::factory ('Pato_Planeacion_Unidad')->getList (array ('filter' => $sql->gen ()));
		
		$choices = array ();
		
		foreach ($unidades as $unidad) {
			$choices[$unidad->nombre] = $unidad->id;
		}
		
		$this->fields['unidad'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Unidad de aprendizaje',
				'help_text' => 'Seleccione una unidad de aprendizaje ya existente',
				'initial' => 0,
				'choices' => $choices,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$unidad = new Pato_Planeacion_Unidad ($this->cleaned_data['unidad']);

		return $unidad;
	}
}
