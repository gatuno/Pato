<?php

Gatuf::loadFunction('Gatuf_Template_dateSimpleFormat');

class Pato_Form_Planeacion_AgregarSeguimiento extends Gatuf_Form {
	private $tema;
	private $seccion;
	
	public function initFields ($extra = array ()) {
		$this->tema = $extra['tema'];
		$this->seccion = $extra['seccion'];
		
		$choices = array ('Sí' => 1, 'No' => 2);
		$this->fields['realizacion'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'initial' => 0,
				'label' => 'Cumplimiento',
				'choices' => $choices,
				'help_text' => 'Indique si cumplió con el resultado del aprendizaje',
				'widget' => 'Pato_Form_Widget_RadioWithLabel',
				'widget_attrs' => array (
					'choices' => $choices,
					'class' => 'realizacion',
				),
		));
		
		$this->fields['inicio'] = new Gatuf_Form_Field_Date (
			array (
				'required' => false,
				'label' => 'Fecha de inicio',
				'initial' => Gatuf_Template_dateSimpleFormat ($this->tema->inicio, '%d/%m/%Y'),
				'help_text' => 'Escriba la fecha en la que inició el tema',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Date (
			array (
				'required' => false,
				'label' => 'Fecha fin',
				'initial' => Gatuf_Template_dateSimpleFormat ($this->tema->fin, '%d/%m/%Y'),
				'help_text' => 'Escriba la fecha que terminó de ver el tema',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['estrategia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Estrategia de enseñanza-aprendizaje',
				'initial' => $this->tema->estrategia,
				'help_text' => 'Escriba la estrategia enseñanza-aprendizaje (centrada en el alumno)',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
		
		$this->fields['evidencia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Evidencia',
				'initial' => $this->tema->evidencia,
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
		
		$this->fields['observaciones'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Observaciones',
				'initial' => '',
				'help_text' => 'Escriba aquí cualquier observación que quiera agregar. Las observaciones son obligatorias si no cumplió con la planeación.',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
	}
	
	public function clean () {
		/* Si elige "No", debe escribir una observación y las cajas deben estar llenas */
		if ($this->cleaned_data['realizacion'] == 2) {
			$campos = array ('inicio', 'fin');
			
			foreach ($campos as $campo) {
				if (trim ($this->cleaned_data[$campo]) == '') {
					$cad = sprintf ('La fecha de "%s" es requerida si no cumplió con la planeación', $campo);
					throw new Gatuf_Form_Invalid ($cad);
				}
			}
			
			$campos = array ('estrategia', 'evidencia', 'observaciones');
			
			foreach ($campos as $campo) {
				if (trim ($this->cleaned_data[$campo]) == '') {
					$cad = sprintf ('El campo "%s" es requerido si no cumplió con la planeación', $campo);
					throw new Gatuf_Form_Invalid ($cad);
				}
			}
		}
		
		/* TODO: Revisar si en el seguimiento también voy a realizar la validación de fechas
		$unidad_tabla = Gatuf::factory ('Pato_Planeacion_Unidad')->getSqlTable ();
		
		$tema_model = new Pato_Planeacion_Tema ();
		
		$tema_model->_a['views']['all'] = array ('join' => 'LEFT JOIN '.$unidad_tabla.' ON unidad='.$unidad_tabla.'.id');
		
		$sql = new Gatuf_SQL ($unidad_tabla.'.materia=%s', $this->unidad->materia);
		$temas = $tema_model->getList (array ('filter' => $sql->gen (), 'view' => 'all', 'order' => 'fin DESC', 'nb' => 1));*/
		
		$inicio = $this->cleaned_data ['inicio'];
		$inicio = date_create_from_format ('d/m/Y', $inicio);
		
		$fin = $this->cleaned_data ['fin'];
		$fin = date_create_from_format ('d/m/Y', $fin);
		
		if ($inicio > $fin) {
			throw new Gatuf_Form_Invalid ('La fecha de inicio del tema es posterior a la fecha de fin');
		}
		
		//if (count ($temas) > 0) {
			/* Revisar que la fecha de inicio de este tema sea mayor que la última fecha planeada */
			/*$last = $temas[0]->fin;
			$last = date_create_from_format ('Y-m-d', $last);
			
			if ($last >= $inicio) {
				$desc = sprintf ('La fecha de inicio debe ser mayor que la última planeación de esta materia. La fecha de fin del tema "%s" es %s', $temas[0]->tema, Gatuf_Template_dateSimpleFormat ($temas[0]->fin, '%e/%b/%Y'));
				throw new Gatuf_Form_Invalid ($desc);
			}
		}*/
		
		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$seguimiento = new Pato_Planeacion_Seguimiento ();
		
		$seguimiento->tema = $this->tema;
		$seguimiento->nrc = $this->seccion;
		
		if ($this->cleaned_data['realizacion'] == 1) {
			/* Si la opción es Sí, copiar los datos desde el tema */
			$seguimiento->cumplimiento = true;
			
			$seguimiento->inicio = $this->tema->inicio;
			$seguimiento->fin = $this->tema->fin;
			
			$seguimiento->estrategia = $this->tema->estrategia;
			$seguimiento->evidencia = $this->tema->evidencia;
		} else {
			$seguimiento->cumplimiento = false;
			
			$seguimiento->inicio = $this->cleaned_data['inicio'];
			$seguimiento->fin = $this->cleaned_data['fin'];
			
			$seguimiento->estrategia = $this->cleaned_data['estrategia'];
			$seguimiento->evidencia = $this->cleaned_data['evidencia'];
		}
		
		$seguimiento->observaciones = $this->cleaned_data['observaciones'];
		
		if ($commit) $seguimiento->create ();

		return $seguimiento;
	}
}
