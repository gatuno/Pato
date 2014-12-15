<?php

class Pato_Form_Materia_EditarEval extends Gatuf_Form {
	private $porcentaje;
	
	public function initFields($extra=array()) {
		$choices = array ();
		$this->porcentaje = $extra['porcentaje'];
		
		$this->fields['porcentaje'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Porcentaje',
				'initial' => $this->porcentaje->porcentaje,
				'help_text' => 'La ponderación que recibe esta forma de evaluación',
				'min' => 0,
		));
		
		$initial = 0;
		if ($this->porcentaje->abierto == 1) {
			if ($this->porcentaje->apertura == null && $this->porcentaje->cierre == null) {
				$initial = 1;
			} else if ($this->porcentaje->apertura == null) {
				$initial = 3;
			} else if ($this->porcentaje->cierre == null) {
				$initial = 2;
			} else {
				$initial = 4;
			}
		}
			
		$this->fields['sel'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Subida',
				'initial' => $initial,
				'help_text' => 'Cuando se deberían poder subir calificaciones',
				'widget_attrs' => array (
					'choices' => array ('Cerrado' => 0, 'Abierto, siempre' => 1, 'Abierto desde el día' => 2, 'Abierto hasta el día' => 3, 'Por fechas' => 4)
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['apertura'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => false,
				'label' => 'Apertura',
				'initial' => $this->porcentaje->apertura,
				'help_text' => 'Fecha de apertura',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
		
		$this->fields['cierre'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => false,
				'label' => 'Cierre',
				'initial' => $this->porcentaje->cierre,
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
		
		$this->porcentaje->abierto = ($sel == 0) ? false : true;
		$this->porcentaje->setFromFormData ($this->cleaned_data);
		
		$this->porcentaje->update ();
		
		return $this->porcentaje;
	}
}

