<?php

class Pato_Form_WhatsApp_Registrar extends Gatuf_Form {
	private $perfil;
	public function initFields ($extra = array ()) {
		$this->perfil = $extra['perfil'];
		$this->fields['numero'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Número celular de WhatsApp',
				'initial' => ($this->perfil->whatsapp !== null ? $this->perfil->whatsapp : ''),
				'help_text' => 'El número celular que quieres registrar, a 10 dígitos sin el 044.',
				'max_length' => 10,
				'widget_attrs' => array (
					'maxlength' => 20,
				),
		));
		
		$this->fields['terms'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Acepto los términos y condiciones del uso del WhatsApp.',
				'initial' => '',
		));
	}
		
	public function clean_numero () {
		$tel_cel = $this->cleaned_data ['numero'];
		$limpio = str_replace (array (' ', '-'), '', $tel_cel);
		if (!preg_match ('/^[0-9]*$/', $limpio)) {
			throw new Gatuf_Form_Invalid ('El teléfono celular sólo puede contener dígitos');
		}
		
		if (substr ($limpio, 0, 3) == '044') {
			throw new Gatuf_Form_Invalid ('Por favor omite el 044');
		}
		
		if (strlen ($limpio) != 10) {
			throw new Gatuf_Form_Invalid ('El teléfono celular debe ser de 10 dígitos exactamente, omite el 044 y agrega tu clave lada');
		}
		
		return $limpio;
	}
	
	public function clean_terms() {
		if (!$this->cleaned_data['terms']) {
			throw new Gatuf_Form_Invalid('Sabemos que es aburrido, pero tienes que aceptar los términos y condiciones del servicio');
		}
		return $this->cleaned_data['terms'];
	}
	
	public function clean () {
		$num = $this->cleaned_data['numero'];
		
		/* Validar que el número de cel no esté registrado a otro alumno */
		$sql = new Gatuf_SQL ('whatsapp=%s AND id!=%s', array ($num, $this->perfil->id));
		
		$perfiles = Gatuf::factory ('Pato_PerfilAlumno')->getList (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($perfiles > 0) {
			throw new Gatuf_Form_Invalid ('Este número telefónico ya está en uso por otro alumno');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data['numero'];
	}
}
