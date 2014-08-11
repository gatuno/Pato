<?php

class Pato_Form_Seccion_Agregar extends Gatuf_Form {
	private $user;
	public function initFields($extra=array()) {
		$this->user = $extra['user'];
		
		$materia = '';
		if (isset ($extra['materia'])) $materia = $extra['materia'];
		
		$choices = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $car) {
			if (!$this->user->hasPerm ('Patricia.coordinador.'.$car->clave)) continue;
			$choices[$car->descripcion] = array ();
			foreach ($car->get_materias_list () as $m) {
				$choices[$car->descripcion][$m->clave . ' - ' . $m->descripcion] = $m->clave;
			}
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => $materia,
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['seccion'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => true,
				'label' => 'Seccion',
				'initial' => '',
				'help_text' => 'La sección, como M15VC o M14MB',
				'max_length' => 15,
				'widget_attrs' => array(
					'maxlength' => 15,
				)
		));
		
		$todoslosmaestros = Gatuf::factory ('Pato_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));
		$choices = array ();
		foreach ($todoslosmaestros as $m) {
			$choices[$m->apellido.' '.$m->nombre] = $m->codigo;
		}
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => true,
				'label' => 'Profesor',
				'initial' => '',
				'help_text' => 'El profesor de este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));

		$choices = array('Sin suplente' => 0) + $choices;
		 
		$this->fields['suplente'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => false,
				'label' => 'Suplente',
				'initial' => 0,
				'help_text' => 'El suplente para este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_seccion () {
		$seccion = mb_strtoupper($this->cleaned_data['seccion']);
		
		if (!preg_match ("/^\w\d+[MV]\w$/", $seccion)) {
			throw new Gatuf_Form_Invalid('La sección de la materia tiene que comenzar con una letra, seguida de un número, luego el turno (MV) y al final la letra del grupo');
		}
		
		return $seccion;
	}
	
	public function clean_suplente () {
		if ($this->cleaned_data['suplente'] === 0) {
			return null;
		}
		
		return $this->cleaned_data['suplente'];
	}
	
	public function clean () {
		/* Verificar que la materia y la sección no estén duplicados */
		$materia = $this->cleaned_data['materia'];
		$seccion = $this->cleaned_data['seccion'];
		
		$sql = new Gatuf_SQL ('seccion=%s AND materia=%s', array ($seccion, $materia));
		$l = Gatuf::factory ('Pato_Seccion')->getList (array ('filter'=>$sql->gen(), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid ('La materia ya tiene esta misma sección');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$seccion = new Pato_Seccion ();
		$seccion->setFromFormData ($this->cleaned_data);
		
		if ($commit) $seccion->create();
		
		return $seccion;
	}
}
