<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Seccion {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		$seccion = new Pato_Seccion ();

		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		$pag->model_view = 'paginador';
		$pag->action = array ('Pato_Views_Seccion::index');
		
		$pag->summary = 'Lista de secciones';
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_FKLink', 'NRC'),
			array ('materia', 'Gatuf_Paginator_FKLink', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_FKLink', 'Sección'),
			array ('maestro_apellido', 'Gatuf_Paginator_FKLink', 'Maestro'),
		);
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'materia_desc', 'maestro_nombre', 'maestro_apellido', 'suplente'),
			array ('nrc', 'materia', 'seccion', 'maestro_apellido','suplente')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/index.html',
		                                          array ('paginador' => $pag,
		                                                 'page_title' => 'Secciones'),
		                                          $request);
	}
	
	public $verNrc_precond = array ('Gatuf_Precondition::loginRequired');
	public function verNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = $seccion->get_materia ();
		if ($seccion->suplente != null) {
			$suplente = $seccion->get_suplente ();
		} else {
			$suplente = null;
		}
		
		$horarios = $seccion->get_pato_horario_list ();
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $materia->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/ver-seccion.html',
		                                          array ('page_title' => $titulo,
		                                                 'seccion' => $seccion,
		                                                 'materia' => $materia,
		                                                 'suplente' => $suplente,
		                                                 'horarios' => $horarios,
		                                                 ),
		                                          $request);
	}
	
	public $agregarNrc_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.editar_secciones_vacio', 'Patricia.admin_secciones')));
	public function agregarNrc ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				Gatuf_Log::info (sprintf ('El NRC %s (%s, %s, %s) ha sido creado por el usuario %s', $seccion->nrc, $seccion->materia, $seccion->seccion, $seccion->maestro, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$extra = array ();
			if (isset ($request->REQUEST['materia'])) {
				$materia = new Pato_Materia ();
				if (false !== ($materia->get($request->REQUEST['materia']))) {
					$extra['materia'] = $materia->clave;
				}
			}
			$form = new Pato_Form_Seccion_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/agregar-seccion.html',
		                                         array ('page_title' => 'Crear sección',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarNrc_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.editar_secciones_vacio', 'Patricia.admin_secciones')));
	public function actualizarNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('seccion' => $seccion);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				Gatuf_Log::info (sprintf ('El NRC %s (%s, %s, %s) ha sido modificado por el usuario %s', $seccion->nrc, $seccion->materia, $seccion->seccion, $seccion->maestro, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Seccion_Actualizar (null, $extra);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/edit-seccion.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarNrc_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.editar_secciones_vacio', 'Patricia.admin_secciones')));
	public function eliminarNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$has_alumnos = false;
		$cant_alumnos = $seccion->get_alumnos_list (array ('count' => true));
		if ($cant_alumnos > 0) {
			$has_alumnos = true;
		}
		
		/* Si la sección tiene alumnos, no puede eliminar esta sección si no tiene el permiso adecuado */
		if ($has_alumnos && !$request->user->hasPerm ('Patricia.admin_secciones')) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		if ($request->method == 'POST') {
			/* Las calificaciones en boleta, asistencias, alumnos y horarios se eliminan solas por integridad referencial */
			$seccion->delete ();
			
			Gatuf_Log::info (sprintf ('El NRC %s ha sido borrado por el usuario %s', $seccion->nrc, $request->user->codigo));
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::index');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/eliminar-seccion.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $verAlumnos_precond = array ('Gatuf_Precondition::loginRequired');
	public function verAlumnos ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$es_el_dueno = (get_class ($request->user) == 'Pato_Maestro' && ($seccion->maestro == $request->user->codigo || $seccion->suplente == $request->user->codigo));
		
		if (!($request->user->hasPerm ('Patricia.horario_alumno') || $request->user->hasPerm ('Patricia.matricular_alumnos')) && !$es_el_dueno) {
			return new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		$alumnos = $seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		
		$especiales = array (3 => 'IN', 2 => 'SD');
		$porc_t = Gatuf::factory ('Pato_Porcentaje')->getSqlTable ();
		$eval = new Pato_Evaluacion ();
		$eval_t = $eval->getSqlTable ();
		$eval->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$porc_t.' ON '.$eval_t.'.id=evaluacion');
		
		$sql = new Gatuf_SQL ('materia=%s', $seccion->materia);
		$evaluaciones = $eval->getList (array ('view' => 'join_materia', 'filter' => $sql->gen ()));
		
		$boleta = array ();
		$asistencias = array ();
		$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
		$asis = new Pato_Asistencia ();
		foreach ($alumnos as $al) {
			$boleta[$al->codigo] = array ();
			foreach ($al->get_boleta_list (array ('filter' => $sql->gen ())) as $b) {
				$boleta[$al->codigo][$b->evaluacion] = $b->calificacion;
			}
			$t_as = $al->get_asistencias_list (array ('filter' => $sql->gen ()));
			if (count ($t_as) == 0) {
				$asistencias[$al->codigo] = null;
			} else {
				$asistencias[$al->codigo] = $t_as[0];
			}
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/ver-alumnos.html',
		                                         array ('page_title' => $titulo,
		                                                'alumnos' => $alumnos,
		                                                'seccion' => $seccion,
		                                                'es_el_dueno' => $es_el_dueno,
		                                                'boleta' => $boleta,
		                                                'especial' => $especiales,
		                                                'evals' => $evaluaciones,
		                                                'asistencias' => $asistencias),
		                                         $request);
	}
	
	public $verFormatos_precond = array ('Gatuf_Precondition::loginRequired');
	public function verFormatos ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = Gatuf::factory ('Pato_GPE')->getList ();
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/formatos.html',
		                                         array ('page_title' => $titulo,
		                                                'gpe' => $gpe,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $verOperaciones_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.cerrar_kardex'));
	public function verOperaciones ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = Gatuf::factory ('Pato_GPE')->getList ();
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/operaciones.html',
		                                         array ('page_title' => $titulo,
		                                                'gpe' => $gpe,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $cerrarAKardex_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.cerrar_kardex'));
	public function cerrarAKardex ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = new Pato_GPE ();
		
		if (false === ($gpe->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verOperaciones', $seccion->nrc);
		
		$found = false;
		
		$secs = str_split ($gpe->secciones);
		foreach ($secs as $s) {
			if (substr ($seccion->seccion, 0, 1) == $s) {
				$found = true;
			}
		}
		
		if (!$found) {
			$request->user->setMessage (3, 'La sección no corresponde al grupo de evaluación seleccionado. No se crearán calificaciones en Kardex');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Preparar el join SQL */
		$eval_t = Gatuf::factory ('Pato_Evaluacion')->getSqlTable ();
		$porc_model = new Pato_Porcentaje ();
		$porc_t = $porc_model->getSqlTable ();
		$porc_model->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$eval_t.' ON '.$eval_t.'.id=evaluacion');
		
		/* Conseguir todos los porcentajes de esta sección */
		$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array ($seccion->materia, $gpe->id));
	
		$porcentajes = $porc_model->getList (array ('view' => 'join_materia', 'filter' => $sql->gen ()));
	
		if (count ($porcentajes) == 0) {
			/* Si no tiene porcentajes, no podemos generar una calificación */
			$request->user->setMessage (2, 'La materia no tiene registradas formas de evaluación en la modalidad '.$gpe->descripcion.', no hay calificaciones que crear');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$totales = array ('generados' => 0, 'enkardex' => 0);
		
		/* Recorrer cada alumno */
		foreach ($seccion->get_alumnos_list () as $alumno) {
			/* Si el alumno ya tiene una calificación en kardex de este calendario y materia, omitir */
			$sql = new Gatuf_SQL ('materia=%s AND calendario=%s AND gpe=%s', array ($seccion->materia, $request->calendario->clave, $gpe->id));
		
			$kardxs = $alumno->get_kardex_list (array ('count' => true, 'filter' => $sql->gen ()));
			if ($kardxs != 0) {
				/* Este alumno YA tiene registrada una calificación en kardex, omitir */
				$totales['enkardex']++;
				continue;
			}
		
			/* Si el alumno ya tiene una calificación en kardex aprobatoria, omitir */
			$sql = new Gatuf_SQL ('materia=%s AND aprobada=1', array ($seccion->materia));
		
			$kardxs = $alumno->get_kardex_list (array ('count' => true, 'filter' => $sql->gen ()));
			if ($kardxs != 0) {
				/* Este alumno YA tiene registrada una calificación en kardex, omitir */
				$totales['enkardex']++;
				continue;
			}
		
			$suma = 0;
			foreach ($porcentajes as $porcentaje) {
				/* Tratar de conseguir la boleta */
				$sql = new Gatuf_SQL ('nrc=%s AND evaluacion=%s', array ($seccion->nrc, $porcentaje->evaluacion));
			
				$boleta = $alumno->get_boleta_list (array ('filter' => $sql->gen ()));
			
				if (count ($boleta) != 0) {
					$p = ($boleta[0]->calificacion * $porcentaje->porcentaje) / 100;
					$suma += $p;
				}
			}
		
			/* Tengo el total de este alumno */
			$kardex = new Pato_Kardex ();
			$kardex->alumno = $alumno;
			$kardex->materia = $seccion->get_materia();
			$kardex->nrc = $seccion->nrc;
			$kardex->calendario = $request->calendario;
			$kardex->gpe = $gpe;
			$kardex->calificacion = $suma;
			$kardex->aprobada = ($suma >= 7);
		
			$kardex->create ();
			$totales['generados']++;
		}
		
		Gatuf_Log::info (sprintf ('El usuario %s cerró a Kardex el NRC %s, en el grupo de evaluacion %s', $request->user->codigo, $seccion->nrc, $gpe->id));
		$request->user->setMessage (1, 'Fueron creadas '.$totales['generados'].' calificaciones en kardex. Se omitieron '.$totales['enkardex'].' registros por que ya existían.');
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $actaCalificaciones_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.imprimir_acta'));
	public function actaCalificaciones ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = new Pato_GPE ();
		
		if (false === ($gpe->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$fecha = $request->session->getData ('fecha', date ('d/m/Y'));
		$fecha = date_parse_from_format ('d/m/Y', $fecha);
		$timestamp = mktime (0, 0, 0, $fecha['month'], $fecha['day'], $fecha['year']);
		$pdf = new Pato_PDF_Seccion_Acta ('P', 'mm', 'Letter');
		
		//$pdf->renderPreacta ($seccion, $gpe, $timestamp);
		
		/* Revisar si para esta acta ya hay un folio usado previamente */
		$usados = $request->session->getData ('folios_usados', array ());
		
		$buscar = 'NRC '.$seccion->nrc.' '.$request->calendario->clave;
		$found = array_search ($buscar, $usados);
		if ($found !== false) {
			/* No tomar un nuevo folio, porque ya se imprimió antes el acta */
			$pdf->renderActa ($seccion, $gpe, $found, $timestamp);
		} else {
			/* Usar e incrementar el folio */
			$folio = $request->session->getData ('numero_folio', 1);
			$pdf->renderActa ($seccion, $gpe, $folio, $timestamp);
			
			/* Guardar el folio para una futura segunda impresión */
			$usados[$folio] = 'NRC '.$seccion->nrc.' '.$request->calendario->clave;
			$request->session->setData ('folios_usados', $usados);
			
			$folio++;
			$request->session->setData ('numero_folio', $folio);
		}
		
		$pdf->Close ();
		
		$nombre = 'acta_preacta_'.$seccion->nrc.'.pdf';
		$fln = $nombre.Gatuf_Utils::getPassword (6);
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$fln, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$fln, $nombre, 'application/pdf', true);
	}
	
	public $listaAsistencia_precond = array ('Gatuf_Precondition::loginRequired');
	public function listaAsistencia ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$es_el_dueno = (get_class ($request->user) == 'Pato_Maestro' && ($seccion->maestro == $request->user->codigo || $seccion->suplente == $request->user->codigo));
		
		if (!($request->user->hasPerm ('Patricia.matricular_alumnos') || $request->user->hasPerm ('Patricia.horario_alumno')) && !$es_el_dueno) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		$pdf = new Pato_PDF_Seccion_Asistencia ('L', 'mm', 'Letter');
		
		$pdf->renderGrupo ($seccion);
		
		$pdf->Close ();
		
		$nombre = 'Lista_asistencias_'.$seccion->nrc.'_'.$seccion->materia.'_'.$seccion->seccion.'.pdf';
		$fln = $nombre.Gatuf_Utils::getPassword (6);
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$fln, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$fln, $nombre, 'application/pdf', true);
	}
	
	public $evaluar_precond = array ('Pato_Precondition::maestroRequired');
	public function evaluar ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$eval = new Pato_Evaluacion ();
		
		if (false === ($eval->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$es_el_dueno = ($seccion->maestro == $request->user->codigo || $seccion->suplente == $request->user->codigo);
		
		/* Si la forma de evaluación es subida por el profesor, sólo permitirle a él (o a su suplente) subir calificaciones */
		if ($eval->maestro) {
			if (!$es_el_dueno) {
				throw new Gatuf_HTTP_Error404 ();
			}
		} else if (!$request->user->hasPerm ('Patricia.subir_evaluaciones')) {
			/* En caso contrario, sólo un administrador alguien con el permiso de subir calificaciones */
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Revisar que el porcentaje exista para esta materia */
		$sql = new Gatuf_SQL ('materia=%s AND evaluacion=%s', array ($seccion->materia, $eval->id));
		
		$ps = Gatuf::factory ('Pato_Porcentaje')->getOne ($sql->gen ());
		
		if ($ps === null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$ps->abierto) {
			$request->user->setMessage (3, 'La subida de calificaciones para '.$eval->descripcion.' está cerrada.');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		if ($ps->apertura !== null) {
			/* Revisar sólo que la fecha actual pase de la fecha de apertura */
			$unix_inicio = strtotime ($ps->apertura);
			
			if ($unix_time < $unix_inicio) {
				$request->user->setMessage (2, 'La subida de calificaciones para '.$eval->descripcion.' aún no está abierta.');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		}
		
		if ($ps->cierre !== null) {
			$unix_fin = strtotime ($ps->cierre);
			
			if ($unix_time > $unix_fin) {
				$request->user->setMessage (2, 'La subida de calificaciones para '.$eval->descripcion.' ya cerró.');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		}
		
		/* Antes, contar si los alumnos ya tienen una calificación en kardex de esta sección,
		 * para evitar que suban si ya cerraron el acta */
		$sql = new Gatuf_SQL ('gpe=%s AND nrc=%s AND materia=%s AND calendario=%s', array ($eval->grupo, $seccion->nrc, $seccion->materia, $request->calendario->clave));
		
		$kardex = Gatuf::factory ('Pato_Kardex')->getList (array ('filter' => $sql->gen(), 'count' => true));
		$alumnos = $seccion->get_alumnos_list (array ('count' => true));
		
		if ($kardex == $alumnos) {
			/* Todos los alumnos ya tienen calificación en Kardex */
			$request->user->setMessage (2, 'Este grupo ya tiene todas las calificaciones roladas en kardex, por lo que no puede modificar las boletas. Solicite un cambio de calificación de Kardex');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('porcentaje' => $ps, 'seccion' => $seccion, 'calendario' => $request->calendario);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Evaluar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				Gatuf_Log::info (sprintf ('El usuario %s ha subido calificaciones en boleta del NRC %s en la forma de evaluacion %s', $request->user->codigo, $seccion->nrc, $eval->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Seccion_Evaluar (null, $extra);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/evaluar.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'porcentaje' => $ps,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $evaluarAsistencias_precond = array ('Pato_Precondition::maestroRequired');
	public function evaluarAsistencias ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$es_el_dueno = ($seccion->maestro == $request->user->codigo || $seccion->suplente == $request->user->codigo);
		
		if (!$es_el_dueno) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('seccion' => $seccion);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Asistencia ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Seccion_Asistencia (null, $extra);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/asistencias.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $matricular_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.matricular_alumnos'));
	public function matricular ($request, $match){
		$seccion =  new Pato_Seccion ();
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
				
				Gatuf::loadFunction ('Pato_Procedimiento_matricular');
				
				$resp = Pato_Procedimiento_matricular ($seccion, $alumno, false, false);
				
				if ($resp !== true) {
					$request->user->setMessage (2, 'El alumno ('.$alumno->codigo.') no se pudo matricular por la siguiente razón: '.$resp);
				} else {
					Gatuf_Log::info (sprintf ('El usuario %s ha matriculado al alumno %s en el NRC %s', $request->user->codigo, $alumno->codigo, $seccion->nrc));
				}
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/matricular.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $desmatricular_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.matricular_alumnos'));
	public function desmatricular ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Revisar que el alumno esté matriculado */
		$als = $seccion->get_alumnos_list ();
		$found = false;
		foreach ($als as $al) {
			if ($al->codigo == $alumno->codigo) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			/* Borrar las calificaciones y asistencias
			 * TODO: Convertir esto en un TRIGGER */
			$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
			
			$asistencias = Gatuf::factory ('Pato_Asistencia')->getList (array ('filter' => $sql->gen ()));
			foreach ($asistencias as $asis) {
				$asis->delete ();
			}
			
			$boletas = Gatuf::factory ('Pato_Boleta')->getList (array ('filter' => $sql->gen ()));
			foreach ($boletas as $b) {
				$b->delete ();
			}
			
			$seccion->delAssoc ($alumno);
			
			Gatuf_Log::info (sprintf ('El usuario %s ha desmatriculado al alumno %s del NRC %s', $request->user->codigo, $alumno->codigo, $seccion->nrc));
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/desmatricular.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'alumno' => $alumno),
		                                         $request);
	}
}
