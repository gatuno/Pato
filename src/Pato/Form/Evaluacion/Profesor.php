<?php

class Pato_Form_Evaluacion_Profesor extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$textos = array (
			'Asiste normalmente a clase.',
			'Cumple adecuadamente con el inicio y término del horario de clase.',
			'Al inicio del curso da a conocer el programa (competencias, contenidos, metodología, criterio de evaluación).',
			'El profesor cumple con los resultados de aprendizaje, contenido, metodología y criterio de evaluación que se dio a conocer al principio del curso.',
			'He desarrollado las competencias planteadas en el programa al concluir el cuatrimestre.',
			'Se han visto en clase los temas contenidos en el programa de la materia o curso.',
			'Aclara mis dudas.',
			'Cuando solicita actividades de aprendizaje los devuelve con comentarios u observaciones.',
			'Utiliza con frecuencia ejemplos, esquemas, presentaciones, modelos o gráficos, para apoyar sus explicaciones.',
			'Expone claramente los temas de la materia.',
			'Las actividades que se realizaron en las sesiones de clase, dan evidencia que el profesor se ocupa de la planeación.',
			'Hace reflexionar en las implicaciones o aplicaciones prácticas de lo tratado en clase.',
			'Promueve el uso de diversas herramientas, particularmente las digitales, para gestionar (recabar, procesar, evaluar y usar) información.',
			'Promueve actividades participativas que me permiten colaborar con mis compañeros con una actitud positiva.',
			'La comunicación profesor(a)-estudiante crea un clima de confianza.',
			'Promueve valores como el respeto, tolerancia, equidad, responsabilidad, honestidad, entre otros.',
			'La clase se desarrolla en un ambiente de respeto, tolerancia y equidad.',
			'Los evidencias que solicita el profesor evalúan el contenido temático del programa de la materia.',
			'Los criterios y procedimientos de evaluación se aplicaron tal como fueron presentados al inicio del curso.',
			'Se informa detalladamente la calificación obtenida en el curso.',
			'Es posible revisar con el profesor la calificación, si se considera que puede haber error.',
			'Además de los exámenes, las evaluaciones del profesor se basan en los temas vistos en clase.',
			'Da a conocer las calificaciones en el plazo establecido (actividades de aprendizaje, evidencias, etc.).',
			'La calificación final toma en cuenta el trabajo de todo el curso.',
			'Estoy satisfecho(a) con la labor docente de este(a) profesor(a).',
			'Considero que el profesor(a) me ha brindado los elementos necesarios para que desarrolle las habilidades planteadas en la materia.',
		);
		
		$opciones = array ('Siempre' => 5, 'Casi siempre' => 4, 'En ocasiones' => 3, 'Al menos una vez' => 2, 'Nunca' => 1);
		
		$g = 1;
		foreach ($textos as $t) {
			$this->fields['p_'.$g] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => $t,
					'initial' => -1,
					'help_text' => '',
					'choices' => $opciones,
					'widget' => 'Gatuf_Form_Widget_SelectInput',
					'widget_attrs' => array (
						'choices' => array ('Seleccione una opción' => -1),
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
