<?php

function Pato_Migrations_Seeds_1Permisos_run ($params=null) {
	$lista = array (
		array ('admin_agenda', 'Administrar agenda', 'Permite crear, modificar y eliminar las agendas de los alumnos'),
		array ('admin_calendario', 'Administrar calendarios', 'Permite al usuario crear, modificar y configurar calendarios'),
		array ('admin_carrera', 'Administrar y modificar carreras', 'Permite al usuario crear y modificar carreras'),
		array ('admin_edificios_salones', 'Administrar edificios y salones', 'Permite al usuario crear y modificar edificios y salones'),
		array ('admin_estatus', 'Administrar estatus de los alumnos', 'Permite al usuario administrar y modificar el estatus de los alumnos'),
		array ('admin_evaluacion_profs', 'Configurar evaluación de profesores', 'Permite al usuario configurar la evaluación de profesores'),
		array ('admin_materia', 'Administrar materias', 'Permite al usuario crear y modificar materias'),
		array ('admin_materia_evals', 'Modificar formas de evaluacion de materias', 'Permite al usuario cambiar la asociación que existe entre las formas de evaluación y las materias'),
		array ('admin_profesores', 'Administrar profesores', 'Permite al usuario crear y modificar profesores en el sistema'),
		array ('admin_secciones', 'Modificar secciones con alumnos', 'Permite al usuario crear, modificar y eliminar secciones. Incluso si tiene alumnos matriculados'),
		array ('admin_suficiencias', 'Configurar suficiencias', 'Permite al usuario configurar las suficiencias'),
		array ('asociar_carrera_materia', 'Asociar carreras y materias', 'Permite al usuario cambiar la asociación que existe entre materias y carreras'),
		array ('autorizar_suficiencias', 'Autorizar suficiencias', 'Permite al usuario autorizar o negar solicitudes de suficiencias'),
		array ('boleta_alumno', 'Ver calificaciones en boleta', 'Permite al usuario ver las calificaciones en boleta de los alumnos'),
		array ('cerrar_kardex', 'Cerrar a Kardex', 'Permite al usuario convertir calificaciones de boleta a Kardex'),
		array ('corregir_kardex', 'Corregir Kardex', 'Permite corregir una calificación en Kardex'),
		array ('crear_alumno', 'Crear alumnos', 'Permite al usuario crear nuevos alumnos'),
		array ('editar_alumno', 'Modificar alumnos', 'Permite al usuario modificar alumnos'),
		array ('editar_secciones_vacio', 'Crear y modificar secciones sin alumnos', 'Permite al usuario crear, modificar y eliminar secciones que no contienen alumnos matriculados'),
		array ('falsificador_fecha', 'Configuración de la fecha', 'Permite cambiar y falsificar la fecha de impresión de ciertos documentos'),
		array ('foliador', 'Configuración del foliador', 'Permite cambiar y ajustar el foliador interno del sistema'),
		array ('grant', 'Otorgar permisos', 'Otorga el privilegio para asignar y quitar permisos a otros usuarios'),
		array ('horario_alumno', 'Horarios de alumnos', 'Permite ver el horario de los alumnos'),
		array ('imprimir_acta',  'Imprimir actas de calificaciones', 'Permite imprimir actas de calificaciones'),
		array ('imprimir_boleta_alumno', 'Imprimir boleta de alumno', 'Permite imprimir boletas de califaciones de los alumnos'),
		array ('kardex_alumno', 'Ver Kardex', 'Permite ver el historial académico del alumno (Kardex)'),
		array ('levantar_kardex', 'Generar calificación en Kardex', 'Permite generar una calificación directa en Kardex a un alumno'),
		array ('matricular_alumnos', 'Matricular alumnos', 'Permite al usuario matricular y desmatricular alumnos en secciones'),
		array ('reportes_planeacion', 'Ver reportes de planeación', 'Permite ver los reportes sobre planeación académica'),
		array ('reportes_todos', 'Ver reportes (varios)', 'Permite al usuario ver cualquier reporte, excepto reportes de planeación'),
		array ('reset_password', 'Reiniciar contraseñas', 'Permite mandar usuarios de reinicio de contraseña de los usuarios'),
		array ('resultados_eval_profesores', 'Ver evaluación de profesores', 'Permite al usuario ver los resultados de la evaluación de profesores'),
		array ('subir_evaluaciones', 'Subir evaluaciones', 'Permite al usuario subir calificaciones de formas de evaluación exclusivas de control escolar'),
		array ('ver_planeacion', 'Ver planeación', 'Permite ver la planeación de otros profesores'),
	);
	
	foreach ($lista as $l) {
		$permiso = new Gatuf_Permission ();
		
		$permiso->code_name = $l[0];
		$permiso->name = $l[1];
		$permiso->description = $l[2];
		$permiso->application = 'Patricia';
		
		$permiso->create ();
	}
}
