<?php
function Pato_Procedimiento_matricular ($seccion, $alumno, $checar_horario = true, $checar_cupo = true) {
	if ($checar_cupo) {
		/* Revisar cupos aquí */
		$count = $seccion->get_alumnos_list (array ('count' => true));
		if ($count >= $seccion->cupo) {
			return 'NRC con cupo lleno';
		}
	}
	
	$materia = $seccion->get_materia();
	
	/* Si el alumno tiene pasada la materia, no la puede recursar */
	$sql_k = new Gatuf_SQL ('(materia=%s AND aprobada=1)', $seccion->materia);
	$kardexs = $alumno->get_kardex_list (array ('filter' => $sql_k->gen (), 'count' => true));
	
	if ($kardexs > 0) {
		return 'Materia ya acreditada';
	}
	
	/* Revisar que la materia pertenezca a su carrera actual */
	$ins = $alumno->get_current_inscripcion ();
	
	if ($ins === null) {
		return 'El alumno no tiene carrera activa';
	}
	
	$carrera_actual = $ins->get_carrera ();
	$cars = $materia->get_carreras_list ();
	$pertenece = false;
	foreach ($cars as $car) {
		if ($carrera_actual->clave == $car->clave) {
			$pertenece = true;
			break;
		}
	}
	
	if (!$pertenece) {
		return 'La materia no pertence a la carrera del alumno';
	}
	
	$secciones_al = $alumno->get_grupos_list ();
	$horas = array ();
	/* Revisar que no haya matriculado otro curso de la misma materia */
	foreach ($secciones_al as $sec_al) {
		if ($sec_al->nrc == $seccion->nrc) {
			return 'NRC ya registrado';
		}
		
		if ($sec_al->materia == $seccion->materia) {
			return 'Otro NRC de la misma materia ya está registrado';
		}
		
		if ($checar_horario) {
			foreach ($sec_al->get_pato_horario_list () as $h_al) {
				$horas[] = $h_al;
			}
		}
	}
	
	if ($checar_horario) {
		$choque = false;
		foreach ($horas as $h_al) {
			foreach ($seccion->get_pato_horario_list () as $h_sec) {
				if (Pato_Horario::chocan ($h_al, $h_sec)) $choque = true;
			}
		}
		
		if ($choque) {
			return 'Conflicto de horario';
		}
	}
	
	$alumno->setAssoc ($seccion);
	
	return true;
}
