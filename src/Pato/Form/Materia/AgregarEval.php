<?php

class Pato_Form_Materia_AgregarEval extends Gatuf_Form {
	private $materia;
	
	public function initFields($extra=array()) {
		$choices = array ();
		$this->materia = $extra['materia'];
		
		foreach ($this->materia->getNotEvals ($extra['gp']) as $eval) {
			$choices [$eval->descripcion] = $eval->id;
		}
		
		$this->fields['evaluacion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Forma de evaluacion',
				'initial' => '',
				'help_text' => 'La forma de evaluación que aplica sobre esta materia',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['porcentaje'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Porcentaje',
				'initial' => '20',
				'help_text' => 'La ponderación que recibe esta forma de evaluación',
				'min' => 1,
		));
		
		$this->fields['sel'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Subida',
				'initial' => 0,
				'help_text' => 'Cuando se deberían poder subir calificaciones',
				'widget_attrs' => array (
					'choices' => array ('Cerrado' => 0, 'Abierto, siempre' => 1, 'Abierto desde el día' => 2, 'Cerrado hasta el día' => 3, 'Por fechas' => 4)
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['apertura'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => false,
				'label' => 'Apertura',
				'initial' => '',
				'help_text' => 'Fecha de apertura',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
		
		$this->fields['cierre'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => false,
				'label' => 'Cierre',
				'initial' => '',
				'help_text' => 'Fecha de cierre',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
	}
	
	public function clean () {
		$sel = $this->cleaned_data['sel'];
		
		if ($sel == 0 || $sel == 1) return $this->cleaned_data;
		
		$inicio = $this->cleaned_data ['apertura'];
		$fin = $this->cleaned_data ['cierre'];
		
		if (($sel == 2 || $sel == 4) && $inicio == '') {
			throw new Gatuf_Form_Invalid ('Es necesario poner una fecha y hora de apertura');
		}
		
		if (($sel == 3 || $sel == 4) && $fin == '') {
			throw new Gatuf_Form_Invalid ('Es necesario poner una fecha y hora de cierre');
		}
		
		if ($sel == 4 && $inicio > $fin) {
			throw new Gatuf_Form_Invalid ('La fecha y hora de apertura son posteriores a la fecha y hora de cierre');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$sel = $this->cleaned_data['sel'];
		if ($sel == 0 || $sel == 1) {
			$this->cleaned_data['apertura'] = null;
			$this->cleaned_data['cierre'] = null;
		} else if ($sel == 2) {
			$this->cleaned_data['cierre'] = null;
		} else if ($sel == 3) {
			$this->cleaned_data['apertura'] = null;
		}
		
		$porcentaje = new Pato_Porcentaje ();
		$porcentaje->abierto = ($sel == 0) ? 0 : 1;
		$porcentaje->materia = $this->materia;
		$porcentaje->setFromFormData ($this->cleaned_data);
		
		$porcentaje->create ();
		
		return $porcentaje;
	}
}

