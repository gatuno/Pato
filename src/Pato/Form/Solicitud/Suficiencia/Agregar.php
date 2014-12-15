<?php

class Pato_Form_Solicitud_Suficiencia_Agregar extends Gatuf_Form {
	private $alumno;
	public function initFields ($extra = array ()) {
		$this->alumno = $extra['alumno'];
		
		/* Recoger las solicitudes de suficiencias para luego eliminarlas del catálogo */
		$solicitudes = $this->alumno->get_pato_solicitud_suficiencia_list ();
		
		$no = array ();
		foreach ($solicitudes as $s) {
			$no[] = $s->materia;
		}
		
		$choices = array ();
		$ins = $this->alumno->get_current_inscripcion ();
		$carrera = $ins->get_carrera ();
		foreach ($carrera->get_materias_list () as $m) {
			if (in_array ($m->clave, $no)) continue;
			$choices[$m->descripcion] = $m->clave;
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Materia',
				'initial' => '',
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['terms'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Acepto los términos y condiciones de las suficiencias.',
				'initial' => '',
		));
	}
	
	public function clean_terms() {
		if (!$this->cleaned_data['terms']) {
			throw new Gatuf_Form_Invalid('Sabemos que es aburrido, pero tienes que aceptar los términos y condiciones de las suficiencias');
		}
		return $this->cleaned_data['terms'];
	}
	
	public function clean () {
		$sql = new Gatuf_SQL ('materia=%s', $this->cleaned_data['materia']);
		
		$solicitudes = $this->alumno->get_pato_solicitud_suficiencia_list (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($solicitudes > 0) {
			throw new Gatuf_Form_Invalid ('Ya tienes una solicitud de suficiencias para esta materia');
		}
		
		$sql = new Gatuf_SQL ('(materia=%s AND aprobada=1)', $this->cleaned_data['materia']);
		
		$kardexs = $this->alumno->get_kardex_list (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($kardexs > 0) {
			throw new Gatuf_Form_Invalid ('No puedes solicitar una suficiencia para esta materia, ¡Ya está aprobada!');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$suficiencia = new Pato_Solicitud_Suficiencia ();
		
		$suficiencia->alumno = $this->alumno;
		$suficiencia->setFromFormData ($this->cleaned_data);
		
		$suficiencia->estatus = 0;
		
		if ($commit) {
			$suficiencia->create ();
		}
		
		return $suficiencia;
	}
}
