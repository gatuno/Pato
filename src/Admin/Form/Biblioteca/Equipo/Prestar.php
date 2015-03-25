<?php

class Admin_Form_Biblioteca_Equipo_Prestar extends Gatuf_Form {
	private $user;
	private $tipos;
	private $biblioteca;
	
	public function initFields ($extra=array()) {
		$this->user = $extra['user'];
		$this->biblioteca = $extra['biblioteca'];
		
		$this->tipos = array ('Cañon' => 1, 'Extensión' => 2, 'Laptop' => 3, 'Bocinas' => 4);
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Código del docente',
				'initial' => '',
				'help_text' => 'Escriba el código, nombre o apellidos del profesor.',
				'widget_attrs' => array(
					'json' => Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::buscarJSON'),
					'min_length' => 2,
				),
				'widget' => 'Gatuf_Form_Widget_AutoCompleteInput',
		));
		
		$choices_c = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $carrera) {
			$choices_c[$carrera->descripcion] = $carrera->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => true,
				'label' => 'Carrera',
				'initial' => '',
				'choices' => $choices_c,
				'help_text' => 'La supuesta disque carrera. Recuerde, hay grupos mezclados.',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$choices = array ();
		foreach (Gatuf::factory ('Pato_Edificio')->getList() as $edificio) {
			$salones = $edificio->get_pato_salon_list ();
			
			$choices[$edificio->descripcion] = array ();
			foreach ($salones as $salon) {
				$choices[$edificio->descripcion][$salon->aula] = $salon->id;
			}
		}
		
		$this->fields['salon'] = new Gatuf_Form_Field_Integer(
			array (
				'required' => true,
				'label' => 'Salon',
				'initial' => '',
				'choices' => $choices,
				'help_text' => 'El salon',
				'widget' => 'Gatuf_Form_Widget_DobleInput',
		));
		
		$pre_sel = new Admin_Biblioteca_Equipo ();
		if (isset ($extra['equipo']) && false === ($pre_sel->get ($extra['equipo']))) {
			$tipo_equipo = 0;
		} else {
			$tipo_equipo = $pre_sel->tipo;
		}
		
		$ops = array ();
		foreach ($this->tipos as $tipo) {
			$ops[$tipo] = array ('Ninguno' => 0);
		}
		
		foreach (Gatuf::factory ('Admin_Biblioteca_Equipo')->getList () as $equipo) {
			if ($equipo->prestado ()) continue;
			$ops[$equipo->tipo][$equipo->nombre." (".$equipo->id.")"] = $equipo->id;
		}
		
		foreach ($this->tipos as $desc => $tipo) {
			$this->fields['tipo_'.$tipo] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => $desc,
					'initial' => ($tipo_equipo == $tipo) ? $pre_sel->id : 0,
					'help_text' => 'El equipo a prestar',
					'widget' => 'Gatuf_Form_Widget_SelectInput',
					'choices' => $ops[$tipo],
			));
		}
		
		$this->fields['notas'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Notas',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
	}
	
	public function clean_maestro () {
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($this->cleaned_data['maestro']))) {
			throw new Gatuf_Form_Invalid ('El código escrito es incorrecto');
		}
		
		return $this->cleaned_data['maestro'];
	}
	
	public function clean () {
		$algo = false;
		foreach ($this->tipos as $tipo) {
			if ($this->cleaned_data['tipo_'.$tipo] != 0) $algo = true;
		}
		
		if ($algo === false) {
			throw new Gatuf_Form_Invalid ('Tiene que prestar al menos un equipo');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$prestamo = new Admin_Biblioteca_Prestamo ();
		$prestamo->setFromFormData ($this->cleaned_data);
		$prestamo->usuario_salida = $this->user;
		$prestamo->biblioteca = $this->biblioteca;
		
		$prestamo->salida = gmdate ('Y-m-d H:i:s');
		
		$prestamo->create ();
		
		$equipo = new Admin_Biblioteca_Equipo ();
		foreach ($this->tipos as $tipo) {
			if ($this->cleaned_data['tipo_'.$tipo] != 0) {
				$equipo->get ($this->cleaned_data['tipo_'.$tipo]);
				
				$prestamo->setAssoc ($equipo);
			}
		}
		
		return $prestamo;
	}
}
