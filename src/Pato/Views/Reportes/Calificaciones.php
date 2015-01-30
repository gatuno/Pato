<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Reportes_Calificaciones {
	public $subidaTarde_precond = array ('Gatuf_Precondition::adminRequired');
	public function subidaTarde ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarEvaluacion ($request->POST);
			
			if ($form->isValid ()) {
				$eval = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Calificaciones::subidaTardeReporte', array ($eval->id));
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarEvaluacion (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/calificaciones/subida-tarde.html',
		                                         array('page_title' => 'Reporte maestros que no subieron calificaciones',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $subidaTardeReporte_precond = array ('Gatuf_Precondition::adminRequired');
	public function subidaTardeReporte ($request, $match) {
		$eval = new Pato_Evaluacion ();
		
		if (false === ($eval->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$incompletos = array ();
		
		/* Listar todas las materias que tienen esa forma de evaluación */
		$pors = $eval->get_pato_porcentaje_list ();
		$materia = new Pato_Materia ();
		
		$sql = new Gatuf_SQL ('evaluacion=%s', $eval->id);
		$where = $sql->gen ();
		
		foreach ($pors as $p) {
			$materia->get ($p->materia);
			
			/* Recuperar todas las secciones de esta materia */
			$secciones = $materia->get_pato_seccion_list ();
			
			foreach ($secciones as $sec) {
				/* Contabilizar el total de alumnos */
				$total = $sec->get_alumnos_list (array ('count' => true));
				
				/* Contabilizar cuantas boletas de esta forma de evaluación hay */
				$boletas = $sec->get_pato_boleta_list (array ('filter' => $where, 'count' => true));
				
				if ($boletas < $total) {
					$i = new stdClass ();
					$i->nrc = $sec;
					$i->total = $total;
					$i->calif = $boletas;
					$i->faltantes = $total - $boletas;
					
					$incompletos[] = $i;
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/calificaciones/subida-tarde-reporte.html',
		                                         array('page_title' => 'Reporte maestros que no subieron calificaciones',
		                                               'eval' => $eval,
		                                               'incompletos' => $incompletos),
                                                 $request);
	}
	
	public $indiceReprobacion_precond = array ('Gatuf_Precondition::adminRequired');
	public function indiceReprobacion ($request, $match) {
		$sql = new Gatuf_SQL ('calendario=%s AND aprobada=0', $request->calendario->clave);
		$kardex = new Pato_Kardex ();
		$kardex->_a['views']['simple'] = array ('group' => 'alumno');
		$kardex->_a['views']['doble'] = array ('group' => 'alumno', 'having' => 'COUNT(*) > 1');
		
		$reprobadas = $kardex->getList (array ('select' => 'alumno, COUNT(*)', 'filter' => $sql->gen (), 'view' => 'simple'));
		
		$total_reprobadores = count ($reprobadas);
		
		$dobles_reprobadores = $kardex->getList (array ('select' => 'alumno, COUNT(*)', 'filter' => $sql->gen (), 'view' => 'doble'));
		
		$total_dobles = count ($dobles_reprobadores);
		
		$sql = new Gatuf_SQL ('calendario=%s', $request->calendario->clave);
		$total_set = $kardex->getList (array ('select' => 'alumno, COUNT(*)', 'filter' => $sql->gen (), 'view' => 'simple'));
		
		$total = count ($total_set);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/calificaciones/reprobadores-reporte.html',
		                                         array('page_title' => 'Reporte de reprobadores',
		                                               'calendario' => $request->calendario,
		                                               'total' => $total,
		                                               'total_reprobadores' => $total_reprobadores,
		                                               'total_dobles' => $total_dobles),
                                                 $request);
	}
	
	public $indiceReprobacionODS_precond = array ('Gatuf_Precondition::adminRequired');
	public function indiceReprobacionODS ($request, $match) {
		$kardex = new Pato_Kardex ();
		$kardex->_a['views']['simple'] = array ('group' => 'materia', 'props' => array ('m_reprobada'));
		
		$sql = new Gatuf_SQL ('calendario=%s AND aprobada=0', $request->calendario->clave);
		$m_repro = $kardex->getList (array ('select' => 'materia, COUNT(*) AS m_reprobada', 'filter' => $sql->gen (), 'view' => 'simple'));
		
		$ods = new Gatuf_ODS ();
		$ods->addNewSheet ('Reprobados por materia');
		$ods->addStringCell ('Reprobados por materia', 1, 1, 'Clave');
		$ods->addStringCell ('Reprobados por materia', 1, 2, 'Materia');
		$ods->addStringCell ('Reprobados por materia', 1, 3, 'Cantidad de alumnos reprobados');
		
		$g = 2;
		
		foreach ($m_repro as $kardex) {
			$materia = $kardex->get_materia ();
			
			$ods->addStringCell ('Reprobados por materia', $g, 1, $materia->clave);
			$ods->addStringCell ('Reprobados por materia', $g, 2, $materia->descripcion);
			$ods->addStringCell ('Reprobados por materia', $g, 3, $kardex->m_reprobada);
			$g++;
		}
		
		$ods->construir_paquete ();
		return new Gatuf_HTTP_Response_File ($ods->nombre, 'Indice_reprobacion-'.$request->calendario->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
	
	public $promedioCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function promedioCarrera ($request, $match) {
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		
		$sumas = array ();
		$alumnos = array ();
		$num = array ();
		foreach ($carreras as $car) {
			$sumas[$car->clave] = 0;
			$alumnos[$car->clave] = array ();
			$num[$car->clave] = 0;
		}
		
		$kardexs = $request->calendario->get_pato_kardex_list ();
		
		foreach ($kardexs as $kardex) {
			$alumno = $kardex->get_alumno ();
			$ins = $alumno->get_inscripcion_for_cal ($request->calendario);
			
			if ($ins === null) {
				throw new Exception (sprintf ('Alto. Algo está mal. El alumno %s no tiene inscripción en el calendario de sesion', $alumno->codigo));
			}
			
			$alumnos[$ins->carrera][$alumno->codigo] = 1;
			
			if (!$kardex->aprobada) {
				$sumas[$ins->carrera] += 6;
			} else {
				$sumas[$ins->carrera] += $kardex->calificacion;
			}
			$num[$ins->carrera]++;
		}
		
		/* Ejecutar los promedios */
		$promedios = array ();
		foreach ($sumas as $carrera => $suma) {
			if ($num[$carrera] == 0) {
				$promedios[$carrera] = 0;
			} else {
				$promedios[$carrera] = ($suma / $num[$carrera]);
			}
		}
		
		/* Contabilizar alumnos diferentes */
		$total_al = array ();
		foreach ($alumnos as $carrera => $pack_alumnos) {
			$total_al[$carrera] = count ($pack_alumnos);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/calificaciones/promedio-por-carrera.html',
		                                         array('page_title' => 'Reporte de promedios por carrera',
		                                               'calendario' => $request->calendario,
		                                               'carreras' => $carreras,
		                                               'alumnos' => $total_al,
		                                               'total' => $sumas,
		                                               'materias' => $num,
		                                               'promedios' => $promedios),
                                                 $request);
	}
}
