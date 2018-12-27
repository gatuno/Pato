<?php

Gatuf::loadFunction('Gatuf_Template_dateSimpleFormat');

class Pato_Form_Planeacion_AgregarTema extends Gatuf_Form {
	private $unidad;
	public function initFields ($extra = array ()) {
		$this->unidad = $extra['unidad'];
		
		$this->fields['inicio'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Fecha de inicio',
				'initial' => '',
				'help_text' => 'Escriba la fecha que iniciará a ver el tema; o el primer dia de la semana, en caso de hacer planeación semanal',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Fecha fin',
				'initial' => '',
				'help_text' => 'Escriba la fecha que terminará de ver el tema; o el último dia de la semana, en caso de hacer planeación semanal',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['tema'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Tema',
				'initial' => '',
				'help_text' => '',
				'max_length' => 300,
				'widget_attrs' => array (
					'size' => 30,
				),
		));
		
		$this->fields['estrategia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Estrategia de enseñanza-aprendizaje',
				'initial' => '',
				'help_text' => 'Escriba la estrategia enseñanza-aprendizaje (centrada en el alumno)',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
		
		$this->fields['evidencia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Evidencia',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
	}
	
	public function clean () {
		$unidad_tabla = Gatuf::factory ('Pato_Planeacion_Unidad')->getSqlTable ();
		
		$tema_model = new Pato_Planeacion_Tema ();
		
		$tema_model->_a['views']['all'] = array ('join' => 'LEFT JOIN '.$unidad_tabla.' ON unidad='.$unidad_tabla.'.id');
		
		$sql = new Gatuf_SQL ($unidad_tabla.'.materia=%s', $this->unidad->materia);
		$temas = $tema_model->getList (array ('filter' => $sql->gen (), 'view' => 'all', 'order' => 'fin DESC', 'nb' => 1));
		
		$inicio = $this->cleaned_data ['inicio'];
		$inicio = date_create_from_format ('d/m/Y', $inicio);
		
		$fin = $this->cleaned_data ['fin'];
		$fin = date_create_from_format ('d/m/Y', $fin);
		
		if ($inicio > $fin) {
			throw new Gatuf_Form_Invalid ('La fecha de inicio del tema es posterior a la fecha de fin');
		}
		
		if (count ($temas) > 0) {
			/* Revisar que la fecha de inicio de este tema sea mayor que la última fecha planeada */
			$last = $temas[0]->fin;
			$last = date_create_from_format ('Y-m-d', $last);
			
			if ($last >= $inicio) {
				$desc = sprintf ('La fecha de inicio debe ser mayor que la última planeación de esta materia. La fecha de fin del tema "%s" es %s', $temas[0]->tema, Gatuf_Template_dateSimpleFormat ($temas[0]->fin, '%e/%b/%Y'));
				throw new Gatuf_Form_Invalid ($desc);
			}
		}
		
		return $this->cleaned_data;
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$tema = new Pato_Planeacion_Tema ();
		$tema->setFromFormData ($this->cleaned_data);
		$tema->unidad = $this->unidad;
		
		if ($commit) $tema->create ();

		return $tema;
	}
}
