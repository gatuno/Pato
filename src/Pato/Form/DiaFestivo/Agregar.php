<?php

class Pato_Form_DiaFestivo_Agregar extends Gatuf_Form {
	private $calendario;
	
	public function initFields ($extra = array ()) {
		$this->calendario = $extra['cal'];
		
		$this->fields['inicio'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Día',
				'initial' => '',
				'help_text' => 'Fecha del día festivo',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['check'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Múltiples días',
				'initial' => false,
				'help_text' => 'Marque la casilla si la festividad abarca múltiples días',
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Date (
			array (
				'required' => false,
				'label' => 'Día final',
				'initial' => '',
				'help_text' => 'El último día festivo',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['admvos'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Aplica para personal administrativo',
				'initial' => false,
				'help_text' => 'Indique si la festividad aplica para el personal administrativo',
		));
		
		$this->fields['acad'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Aplica para personal académico',
				'initial' => false,
				'help_text' => 'Indique si la festividad aplica para el personal académico',
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Descripción',
				'initial' => '',
				'help_text' => 'Un texto corto que describa el día. Algo como "Navidad", o "Día del maestro"',
		));
	}
	
	public function clean () {
		$inicio = $this->cleaned_data ['inicio'];
		$inicio = date_create_from_format ('d/m/Y', $inicio);
		
		/* Validar que día esté dentro del rango del calendario */
		$cal_inicio = $this->calendario->inicio;
		$cal_inicio = date_create_from_format ('Y-m-d', $cal_inicio);
		
		$cal_fin = $this->calendario->fin;
		$cal_fin = date_create_from_format ('Y-m-d', $cal_fin);
		
		if ($cal_inicio > $inicio || $cal_fin < $inicio) {
			throw new Gatuf_Form_Invalid ('El día festivo debe estar dentro del inicio-fin del calendario');
		}
		
		if (isset ($this->cleaned_data['fin']) && $this->cleaned_data['fin'] != '') {
			$fin = $this->cleaned_data ['fin'];
			$fin = date_create_from_format ('d/m/Y', $fin);
			
			if ($inicio > $fin) {
				throw new Gatuf_Form_Invalid ('La fecha de inicio del día festivo es posterior a la fecha de fin');
			}
			
			if ($cal_inicio > $fin || $cal_fin < $fin) {
				throw new Gatuf_Form_Invalid ('El día festivo debe estar dentro del inicio-fin del calendario');
			}
		}
		
		$adm = $this->cleaned_data['admvos'];
		$acad = $this->cleaned_data['acad'];
		
		$res = $adm | $acad;
		
		if (!$res) {
			throw new Gatuf_Form_Invalid ('La festividad debe aplicar al menos para los administrativos o académicos');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$dia = new Pato_DiaFestivo ();
		
		$dia->inicio = $this->cleaned_data['inicio'];
		
		if (isset ($this->cleaned_data['fin']) && $this->cleaned_data['fin'] != '') {
			$dia->fin = $this->cleaned_data['fin'];
		} else {
			$dia->fin = $dia->inicio;
		}
		
		if ($this->cleaned_data['admvos']) {
			$dia->admvos = true;
		}
		
		if ($this->cleaned_data['acad']) {
			$dia->acad = true;
		}
		
		$dia->descripcion = $this->cleaned_data['descripcion'];
		
		if ($commit) {
			$dia->create ();
		}
		
		return $dia;
	}
}
