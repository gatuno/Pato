<?php

class Pato_Form_SeleccionarGPE extends Gatuf_Form {
	public function initFields($extra=array()) {
		$gpe_ops = array ();
		
		foreach (Gatuf::factory ('Pato_GPE')->getList () as $gpe) {
			$gpe_ops[$gpe->descripcion] = $gpe->id;
		}
		
		$this->fields['modalidad'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Modalidad',
				'initial' => '',
				'help_text' => 'La forma de evaluación a procesar',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $gpe_ops,
				),
		));
	}
	
	public function save ($commit = true) {
		return new Pato_GPE ($this->cleaned_data['modalidad']);
	}
}
