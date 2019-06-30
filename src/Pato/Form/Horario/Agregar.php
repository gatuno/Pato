<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Form_Horario_Agregar extends Gatuf_Form {
	private $nrc;
	public function initFields($extra=array()) {
		$this->nrc = $extra['seccion'];
		
		/* Preparar la lista de salones a elegir */
		$edificios = Gatuf::factory('Pato_Edificio')->getList (array ('filter' => 'oculto=0'));
		
		$choices = array ();
		foreach ($edificios as $edificio) {
			$salones = $edificio->get_pato_salon_list (array ('filter' => 'oculto=0'));
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
		
		$this->fields['inicio'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora inicio',
				'initial' => '',
				'help_text' => 'La hora de inicio. Puede ser del tipo 17:00',
				'widget_attrs' => array (
					'size' => 5,
				)
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Time(
			array (
				'required' => true,
				'label' => 'Hora fin',
				'initial' => '',
				'help_text' => 'La hora de fin. Puede ser del tipo 17:00',
				'widget_attrs' => array (
					'size' => 5,
				)
		));
		
		foreach (array ('l' => 'Lunes', 'm' => 'Martes', 'i' => 'Miércoles', 'j' => 'Jueves', 'v' => 'Viernes', 's' => 'Sábado') as $key => $dia) {
			$this->fields[$key] = new Gatuf_Form_Field_Boolean (
				array (
					'required' => true,
					'label' => $dia,
					'initial' => '',
					'help_text' => 'Active la casilla para una clase en '.mb_strtolower ($dia),
					'widget' => 'Gatuf_Form_Widget_CheckboxInput',
			));
		}
	}
	
	public function clean () {
		/* Verificar que por lo menos tenga un día activo */
		$activo = false;
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
			if ($this->cleaned_data[$dia]) $activo = true;
		}
		
		if ($activo == false) {
			throw new Gatuf_Form_Invalid ('La hora tiene que tener programada al menos un día.');
		}
		
		/* Verificar que la hora de entrada sea siempre menor */
		if ($this->cleaned_data['inicio'] >= $this->cleaned_data['fin']) {
			throw new Gatuf_Form_Invalid ('Las horas de inicio y fin son inválidas');
		}
		
		/* Antes de guardar, verificar si la hora recién agregada colisiona con 
		 * otro salon */
		$sql = new Gatuf_SQL ('salon=%s', $this->cleaned_data['salon']);
		$ors = array ();
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
			if ($this->cleaned_data[$dia]) {
				$ors[] = $dia.'=1';
			}
		}
		$sql_dias = new Gatuf_SQL (implode (' OR ', $ors));
		$sql->SAnd ($sql_dias);
		$horas_salon = Gatuf::factory ('Pato_Horario')->getList (array ('filter' => $sql->gen ()));
		
		$horario = new Pato_Horario ();
		$horario->setFromFormData ($this->cleaned_data);
		
		/* Recorrer estas horas */
		foreach ($horas_salon as $hora_en_salon) {
			if (Pato_Horario::chocan ($horario, $hora_en_salon)) {
				throw new Gatuf_Form_Invalid ('La hora agregada colisiona en el salon');
			}
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$horario = new Pato_Horario ();
		
		$horario->nrc = $this->nrc;
		$horario->setFromFormData ($this->cleaned_data);
		
		foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
			$horario->$dia = $this->cleaned_data[$dia];
		}
		
		if ($commit) $horario->create ();
		
		return $horario;
	}
}
