<?php

class Pato_Form_Evaluacion_Profesor extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$textos = array (
			'Domina el profesor los contenidos de la materia que imparte',
			'Es evidente que el profesor prepara las clases o sesiones',
			'El profesor es ordenado y claro en la exposición de los temas',
			'Procura relacionar los nuevos conocimientos con lo visto anteriormente',
			'El profesor elabora síntesis, resúmenes o mapas conceptuales de lo revisado y de lo que va a explicar',
			'El profesor verifica al término de las sesiones si los alumnos han comprendido lo estudiado',
			'El profesor usa medios variados de apoyo al aprendizaje (blog, foro, otros)',
			'El profesor motiva a los alumnos para asistir a asesorias para resolver dudas',
			'Demuestra respeto el profesor a los juicios y opiniones de los alumno',
			'El profesor se expresa respetuosamente hacia los alumnos',
			'El profesor brinda una atención individual cuando se le solicita',
			'El profesor realizó al inicio de cada unidad de aprendizaje una evaluación diagnóstica para conocer el nivel de competencia de cada alumno',
			'Al inicio del curso, el profesor le indicó que tenía que desarrollar un portafolio de evidencias de la asignatura',
			'El profesor realizó una evaluación formativa durante el curso (evaluó los avances que tenía en prácticas, ejercicios, trabajos, tareas, etc.)',
			'Considera usted que el profesor utilizó todos los criterios de evaluación durante el curso (conocimientos, capacidades, habilidades, destrezas, actitudes, aptitudes y valores)',
			'Considera usted que en todas las unidades de aprendizaje, el profesor alcanzó los objetivos del proceso enseñanza-aprendizaje',
			'Motiva el profesor a los alumnos para preguntar y participar en clase',
			'Impulsa el trabajo en equipo',
			'Da a conocer los criterios de evaluación, contenido y objetivo del curso a los alumnos',
			'Es justo en las evaluaciones',
			'Usa diferentes mecanismos de evaluación, según los objetivos a evaluar (proyectos, prácticas, mapas conceptuales, rúbricas)',
			'Entrega con oportunidad los resultados de las evaluaciones realizadas',
			'Informa el profesor a los alumnos sobre los problemas detectados en las evaluaciones',
			'El profesor inicia y termina con puntualidad las sesiones programadas',
			'El profesor relaciona los contenidos de la materia con la práctica profesional de tu carrera',
			'¿Cómo evaluaría globalmente el desempeño de su profesor?',
			'La cordialidad y capacidad del profesor logra crear un clima de confianza para que el alumno pueda exponer sus problemas',
		);
		
		$opciones = array ('Seleccione una opción' => -1, 'Totalmente de acuerdo' => 5, 'De acuerdo' => 4, 'Más o menos de acuerdo' => 3, 'En desacuerdo' => 2, 'Totalmente en desacuerdo' => 1);
		
		$g = 1;
		foreach ($textos as $t) {
			$this->fields['p_'.$g] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => $t,
					'initial' => -1,
					'help_text' => '',
					'widget' => 'Gatuf_Form_Widget_SelectInput',
					'widget_attrs' => array (
						'choices' => $opciones,
						'invalid' => array (-1),
					)
			));
			
			$g++;
		}
		
		$this->fields['comentario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Comentarios',
				'initial' => '',
				'help_text' => 'Sus comentarios ayudarán para la evaluación continua de los profesores',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				)
		));
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data;
	}
}
