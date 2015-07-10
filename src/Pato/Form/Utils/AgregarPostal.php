<?php

class Pato_Form_Utils_AgregarPostal extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices_a = array ();
		
		foreach (Gatuf::factory ('CP_Asentamiento')->getList (array ('order' => 'nombre ASC')) as $as) {
			$choices_a[$as->nombre] = $as->id;
		}
		
		$choices_z = array ();
		
		foreach (Gatuf::factory ('CP_Zona')->getList (array ('order' => 'nombre ASC')) as $z) {
			$choices_z[$z->nombre] = $z->id;
		}
		
		$choices_m = array ();
		foreach (Gatuf::factory ('CP_Estado')->getList (array ('order' => 'nombre ASC')) as $est) {
			$choices_m[$est->nombre] = array ();
			foreach ($est->get_cp_municipio_list (array ('order' => 'nombre ASC')) as $mun) {
				$choices_m[$est->nombre][$mun->nombre] = $mun->id;
			}
		}
		
		$this->fields['codigo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'CP',
				'initial' => 0,
				'min' => 1,
				'max' => 99999,
		));
		
		$this->fields['localidad'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Localidad',
				'initial' => '',
				'max_length' => 200,
				'min_length' => 5,
				'widget_attrs' => array (
					'size' => 40,
				)
		));
		
		$this->fields['asentamiento'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Asentamiento',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices_a,
				)
		));
		
		$this->fields['municipio'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Municipio',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_DobleInput',
				'widget_attrs' => array (
					'choices' => $choices_m,
				)
		));
		
		$this->fields['zona'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Zona',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices_z,
				)
		));
	}
	
	public function save ($commit = true) {
		$cp = new CP_CP ();
		
		$cp->setFromFormData ($this->cleaned_data);
		
		if ($commit) $cp->create ();
		
		return $cp;
	}
}
