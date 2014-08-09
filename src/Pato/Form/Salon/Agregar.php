<?php

class Pato_Form_Salon_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		/* Preparar la lista de edificios */
		$edificios = Gatuf::factory ('Pato_Edificio')->getList (array ('order' => array ('clave ASC')));
		$edifiicio_actual = $extra['edificio'];
		$choices = array ();
		foreach ($edificios as $edificio) {
			$choices[$edificio->clave.' - '.$edificio->descripcion] = $edificio->clave;
		}
		
		$this->fields['edificio'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Edificio',
				'help_text' => 'El edificio donde se encuentra en nuevo salon',
				'initial' => $edifiicio_actual,
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput'
		));

		$this->fields['aula'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Aula',
				'help_text' => 'El nombre del aula',
				'initial' => '',
				'max_length' => 10,
				'min_length' => 1,
				'widget_attrs' => array (
					'maxlength' => 10,
					'size' => 30,
				)
		));

		$this->fields['cupo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Cupo',
				'help_text' => 'El cupo de esta aula',
				'initial' => 40,
				'min' => 0
		));
	}
	
	function clean () {
		$aula = $this->cleaned_data['aula'];
		$edificio = $this->cleaned_data['edificio'];

		$sql = new Gatuf_SQL ('aula=%s AND edificio=%s', array ($aula, $edificio));
		$count = Gatuf::factory ('Pato_Salon')->getList (array ('filter' => $sql->gen (), 'count' => true));
		if ($count > 0) {
			/* Este salon ya existe */
			throw new Gatuf_Form_Invalid ('Este salon ya existe');
		}

		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$salon = new Pato_Salon ();
		$salon->setFromFormData ($this->cleaned_data);
		
		if ($commit) $salon->create ();

		return $salon;
	}
}
