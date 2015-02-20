<?php

class Pato_Form_Alumno_ActualizarPerfil extends Gatuf_Form {
	private $perfil;

	public function initFields ($extra = array ()) {
		$this->perfil = $extra['perfil'];
		
		$this->fields['nacimiento'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Fecha de nacimiento',
				'initial' => implode('/', array_reverse(explode('-', $this->perfil->nacimiento))),
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$choices_seguro = array ('Ninguno' => 0, 'IMSS' => 1, 'ISSSTE' => 2, 'Seguro Popular' => 3, 'Pemex' => 4, 'Gastos médicos mayores' => 5);
		$this->fields['seguro'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Seguro',
				'initial' => $this->perfil->seguro,
				'help_text' => 'Tipo de seguro social que tienes',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices_seguro,
				),
		));
		
		$this->fields['numero_seguro'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Número de seguro social',
				'initial' => $this->perfil->numero_seguro,
				'help_text' => 'Tu número de seguro social, si lo conoces. En caso contrario dejar en blanco',
				'max_length' => 30,
				'widget_attrs' => array (
					'maxlength' => 30,
				),
		));
		
		$choices_sangre = array ('Desconocido' => 'NULL', 'O Negativa' => 'O-', 'O Positiva' => 'O+', 'A Negativa' => 'A-', 'A Positiva' => 'A+', 'B Negativa' => 'B-', 'B Positiva' => 'B+', 'AB Negativa' => 'AB-', 'AB Positiva' => 'AB+');
		$this->fields['sanguineo_rh'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Grupo sanguíneo',
				'initial' => is_null ($this->perfil->sanguineo_rh) ? 'NULL' : $this->perfil->sanguineo_rh,
				'help_text' => 'Tu tipo sanguino. Si desconoces este dato selecciona "Desconocido"',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices_sangre,
				),
		));
		
		$this->fields['alergias'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Alergias',
				'initial' => $this->perfil->alergias,
				'help_text' => '',
				'max_length' => 300,
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'maxlength' => 300,
					'cols' => 80,
					'rows' => 8,
				),
		));
		
		$this->fields['medicacion'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Medicación',
				'initial' => $this->perfil->medicacion,
				'help_text' => '',
				'max_length' => 300,
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'maxlength' => 300,
					'cols' => 80,
					'rows' => 8,
				),
		));
		
		$this->fields['patologias'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Patologias',
				'initial' => $this->perfil->patologias,
				'help_text' => '',
				'max_length' => 300,
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'maxlength' => 300,
					'cols' => 80,
					'rows' => 8,
				),
		));
		
		$this->fields['emergencia_nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre del contacto de emergencia',
				'initial' => $this->perfil->emergencia_nombre,
				'help_text' => '',
				'max_length' => 100,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => 40,
				),
		));
		
		$this->fields['emergencia_local'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Número local del contacto de emergencia',
				'initial' => $this->perfil->emergencia_local,
				'help_text' => '',
				'max_length' => 10,
				'widget_attrs' => array (
					'maxlength' => 10,
				),
		));
		
		$this->fields['emergencia_celular'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Número celular del contacto de emergencia',
				'initial' => $this->perfil->emergencia_celular,
				'help_text' => '',
				'max_length' => 10,
				'widget_attrs' => array (
					'maxlength' => 10,
				),
		));
		
		$alumno = $this->perfil->get_alumno ();
		if ($alumno->sexo == 'F') {
			$this->fields['gestacion_partos'] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => 'Número de Partos. FIXME Corregir texto',
					'initial' => $this->perfil->gestacion_partos,
					'help_text' => 'FIXME',
			));
			
			$this->fields['gestacion_cesareas'] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => 'Número de cesareas. FIXME Corregir texto',
					'initial' => $this->perfil->gestacion_cesareas,
					'help_text' => 'FIXME',
			));
			
			$this->fields['gestacion_abortos'] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => 'Número de abortos. FIXME Corregir texto',
					'initial' => $this->perfil->gestacion_partos,
					'help_text' => 'FIXME',
			));
		}
		
		$choices_antico = array ('Orales' => 0x01, 'Parches' => 0x02, 'Preservativos' => 0x04, 'Inyecciones' => 0x08, 'Otros' => 0x100);
		$antico = array ();
		foreach (array (1,2,4,8,256) as $bit) {
			if ($this->perfil->anticonceptivos & $bit) $antico[] = $bit;
		}
		$this->fields['antico'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => false,
				'multiple' => true,
				'label' => 'Método anticonceptivo que utilizas',
				'initial' => $antico,
				'widget' => 'Gatuf_Form_Widget_SelectMultipleInput_Checkbox',
				'widget_attrs' => array (
					'choices' => $choices_antico,
				),
		));
	}
	
	public function clean_emergencia_local () {
			$tel_cel = $this->cleaned_data ['emergencia_local'];
			$limpio = str_replace (array (' ', '-'), '', $tel_cel);
			if (!preg_match ('/^[0-9]*$/', $limpio)) {
				throw new Gatuf_Form_Invalid ('El teléfono local sólo puede contener dígitos');
			}
			return $limpio;
	}
	
	public function clean_emergencia_celular () {
			$tel_cel = $this->cleaned_data ['emergencia_celular'];
			$limpio = str_replace (array (' ', '-'), '', $tel_cel);
			if (!preg_match ('/^[0-9]*$/', $limpio)) {
				throw new Gatuf_Form_Invalid ('El teléfono celular sólo puede contener dígitos');
			}
			return $limpio;
	}
	
	public function clean_sanguineo_rh () {
		if ($this->cleaned_data['sanguineo_rh'] == 'NULL') return null;
		return $this->cleaned_data['sanguineo_rh'];
	}
	
	public function clean () {
		if ($this->cleaned_data['emergencia_local'] == '' && $this->cleaned_data['emergencia_celular'] == '') {
			throw new Gatuf_Form_Invalid ('Debes proveer al menos un número telefónico para tu contacto de emergencia');
		}
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$this->perfil->setFromFormData ($this->cleaned_data);
		
		/* ORear las banderas de los anticonceptivos */
		$antico = 0;
		foreach ($this->cleaned_data['antico'] as $an) {
			$antico = $antico | $an;
		}
		
		$this->perfil->anticonceptivos = $antico;
		
		$this->perfil->update ();
	}
}
