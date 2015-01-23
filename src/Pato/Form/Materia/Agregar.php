<?php

class Pato_Form_Materia_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'La clave de la materia',
				'max_length' => 64,
				'widget_attrs' => array(
					'maxlength' => 64,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => '',
				'help_text' => 'El nombre completo de la materia',
				'max_length' => 200,
				'widget_attrs' => array(
					'maxlength' => 200,
					'size' => 30,
				),
		));
		
		$this->fields['creditos'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Créditos',
				'initial' => 0,
				'help_text' => 'La cantidad de créditos de la materia',
				'min' => 0,
		));
		
		$this->fields['curso'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Curso',
				'initial' => false,
				'help_text' => '¿La materia es un curso?'
		));
		
		$this->fields['taller'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Taller',
				'initial' => false,
				'help_text' => '¿La materia es un taller?'
		));
		
		$this->fields['laboratorio'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Laboratorio',
				'initial' => false,
				'help_text' => '¿La materia es un laboratorio?'
		));
		
		$this->fields['seminario'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Seminario',
				'initial' => false,
				'help_text' => '¿La materia es un seminario?'
		));
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^[-\w]+$/", $clave)) {
			throw new Gatuf_Form_Invalid('La clave de la materia deben ser de 1 a 4 letras seguidas de números.');
		}
		
		$sql = new Gatuf_SQL('clave=%s', array($clave));
        $l = Gatuf::factory('Pato_Materia')->getList(array('filter'=>$sql->gen(),'count' => true));
        if ($l > 0) {
            throw new Gatuf_Form_Invalid('Esta materia ya existe');
        }
        
        return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$materia = new Pato_Materia ();
		
		$materia->setFromFormData ($this->cleaned_data);
		if ($commit) $materia->create();
		
		return $materia;
	}
}
