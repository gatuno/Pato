<?php

class Pato_Form_Estatus_Documentos extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$documentos = Gatuf::factory ('Admision_Documento')->getList ();
		
		foreach ($documentos as $documento) {
			$this->fields['doc_'.$documento->id] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $documento->descripcion,
					'initial' => false,
					'help_text' => 'El alumno tiene este documento'
			));
		}
	}
	
	public function save ($commit = true) {
		$docs = array ();
		
		foreach ($this->fields as $field => $value) {
			if ($this->cleaned_data[$field]) {
				$id = substr ($field, 4);
				$docs[] = (int) $id;
			}
		}
		
		return $docs;
	}
}
