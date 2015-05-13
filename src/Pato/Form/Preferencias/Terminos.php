<?php

class Pato_Form_Preferencias_Terminos extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Descripción',
				'initial' => $extra['terms'],
				'help_text' => 'Texto enriquecido',
				'widget' => 'Gatuf_Form_Widget_HtmlareaInput',
		));
	}
	
	function clean_descripcion () {
		$data = $this->cleaned_data['descripcion'];
		
		$filter = new Gatuf_Text_HTML_Filter ();
		$filter->allowed = array('ol' => array (), 'li' => array (), 'ul' => array (), 'i' => array (), 'u' => array (), 'blockquote' => array (), 'br' => array (), 'hr' => array (), 'a' => array('href', 'target'), 'b' => array(), 'img' => array('src', 'width', 'height', 'alt'));
		$data = trim ($filter->go ($data));
		if (substr ($data, -6) == '<br />') {
			$data = substr ($data, 0, -6);
		}
		
		if ($data === '') {
			throw new Gatuf_Form_Invalid ('Este campo es obligatorio');
		}
		
		return $data;
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data ['descripcion'];
	}
}
