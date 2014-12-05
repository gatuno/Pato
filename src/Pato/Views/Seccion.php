<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Seccion {
	public function index ($request, $match) {
		$seccion = new Pato_Seccion ();
		$filtro = array();

		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		$pag->model_view = 'paginador';
		$pag->action = array ('Pato_Views_Seccion::index');
		$sql = new Gatuf_SQL ();
		
		/* Aplicando filtrado por carrera y/o departamento
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Filtrar ($request->POST, array ('logged' => !$request->user->isAnonymous ()));
			if ($form->isValid ()) {
				$filtrado = $form->save ();	
				$data = substr($filtrado, 2);
				if ($filtrado[0] == 'c') {
					$this->porCarrera ($request, array (0 => '', 1 => $data));
				} else if ($filtrado[0] == 'a') {
					$this->porAsignadas ($request, array ());
				} else if ($filtrado[0] == 'n') {
					$this->porNoAsignadas ($request, array ());
				} else if ($filtrado[0] == 's') {
					$this->porSuplente ($request, array ());
				}
			}
		} else {
			$form = new Calif_Form_Seccion_Filtrar (null, array ('logged' => !$request->user->isAnonymous ()));
		}*/
		
		/* Verificar filtro por Carrera
		$car = $request->session->getData('filtro_seccion_asignada_carrera', null);
		$noasig = $request->session->getData('filtro_seccion_asignada_no', false);
		$asig = $request->session->getData('filtro_seccion_asignada', false);
		$suplente = $request->session->getData('filtro_seccion_suplente', false);
		if ($asig === true) {
			$filtro['a'] = 'Secciones asignadas';
			
			$sql->Q ('asignacion IS NOT NULL');
		} else if ($noasig === true) {
			$filtro['n'] = 'Secciones no solicitadas';
			
			$sql->Q ('asignacion IS NULL');
		} else if (!is_null ($car)) {
			$carrera = new Calif_Carrera ($car);
			$filtro['c'] = 'Secciones asignadas a la carrera "'.$carrera->descripcion.'"';
			
			$sql->Q ('asignacion=%s', $car);
		}
		
		if ($suplente === true) {
			$filtro['s'] = 'Con suplente asignado';
			$sql->Q ('suplente IS NOT NULL');
		} */
		
		//$pag->forced_where = $sql;
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
		                                                 /*'filtro'=>$filtro,
		                                                 'form' => $form,*/
		                                                 'page_title' => 'Secciones'),
		                                          $request);
	}

	public function porCarrera ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$request->session->setData('filtro_seccion_asignada_carrera',$match[1]);
		$request->session->setData('filtro_seccion_asignada_no', false);
		$request->session->setData('filtro_seccion_asignada', false);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}

	public function porNoAsignadas ($request, $match) {
		$request->session->setData('filtro_seccion_asignada_carrera',null);
		$request->session->setData('filtro_seccion_asignada_no', true);
		$request->session->setData('filtro_seccion_asignada', false);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function porAsignadas ($request, $match) {
		$request->session->setData('filtro_seccion_asignada_carrera',null);
		$request->session->setData('filtro_seccion_asignada_no', false);
		$request->session->setData('filtro_seccion_asignada', true);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function porSuplente ($request, $match) {
		$request->session->setData ('filtro_seccion_suplente', true);
		
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function eliminarFiltro($request, $match){
		if ($match[1] == 'a') {
			$request->session->setData('filtro_seccion_asignada', false);
		} else if($match[1] == 'c'){
			$request->session->setData('filtro_seccion_asignada_carrera',null);
		} else if ($match[1] == 'n') {
			$request->session->setData('filtro_seccion_asignada_no', false);
		} else if ($match[1] == 's') {
			$request->session->setData ('filtro_seccion_suplente', false);
		}
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}

	public function verNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = $seccion->get_materia ();
		$maestro = $seccion->get_maestro ();
		if ($seccion->suplente != null) {
			$suplente = $seccion->get_suplente ();
		} else {
			$suplente = null;
		}
		
		$horarios = $seccion->get_pato_horario_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/ver-seccion.html',
		                                          array ('page_title' => 'NRC '.$seccion->nrc,
		                                                 'seccion' => $seccion,
		                                                 'materia' => $materia,
		                                                 'maestro' => $maestro,
		                                                 'suplente' => $suplente,
		                                                 'horarios' => $horarios,
		                                                 ),
		                                          $request);
	}
	
	public $agregarNrc_precond = array ('Pato_Precondition::coordinadorRequired');
	public function agregarNrc ($request, $match) {
		$extra = array ('user' => $request->user);
		
		if (!$request->user->administrator) {
			/* Si es un coordinador, sólo puede crear secciones en el calendario siguiente */
			$gconf = new Gatuf_GSetting ();
			$gconf->setApp ('Patricia');
			$sig = $gconf->getVal ('calendario_siguiente', null);
			
			if ($request->calendario->clave != $sig) {
				$request->user->setMessage (3, 'No se pueden crear secciones en calendarios pasados');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::index');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
			
			/* TODO: Revisar la otra configuración que cierra la creación de NRCS */
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			if (isset ($request->REQUEST['materia'])) {
				$materia = new Pato_Materia ();
				if (false === ($materia->get($request->REQUEST['materia']))) {
					$extra['materia'] = '';
				} else {
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
	
	public $actualizarNrc_precond = array ('Pato_Precondition::coordinadorRequired');
	public function actualizarNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (!$request->user->administrator) {
			/* Si es un coordinador, sólo puede crear secciones en el calendario siguiente */
			$gconf = new Gatuf_GSetting ();
			$gconf->setApp ('Patricia');
			$sig = $gconf->getVal ('calendario_siguiente', null);
			
			if ($request->calendario->clave != $sig) {
				$request->user->setMessage (3, 'No se pueden crear secciones en calendarios pasados');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::index');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
			
			/* TODO: Revisar la otra configuración que cierra la creación de NRCS */
		}
		
		/* Revisar que tenga permisos de edición sobre la materia de esta sección */
		$carreras = $seccion->get_materia ()->get_carreras_list ();
		
		$found = $request->user->administrator;
		foreach ($carreras as $carrera) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			$request->user->setMessage (3, 'Usted no puede editar esta sección por falta de permisos');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('seccion' => $seccion);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Seccion_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/edit-seccion.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST') {
			/* Eliminar todos los alumnos de este grupo */
			$related = $seccion->get_alumnos_list();
			foreach ($related as $rel) {
				$seccion->delAssoc($rel);
			}
			
			/* Eliminar todas los horarios dde esta sección */
			$horas = $seccion->get_pato_horario_list ();
			
			foreach ($horas as $hora) {
				$hora->delete ();
			}
			
			$seccion->delete ();
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::index');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/eliminar-seccion.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $verAlumnos_precond = array ('Pato_Precondition::maestroRequired');
	public function verAlumnos ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator && !$request->user->isCoord () && $request->user->login != $seccion->maestro) {
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
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/ver-alumnos.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'alumnos' => $alumnos,
		                                                'seccion' => $seccion,
		                                                'boleta' => $boleta,
		                                                'especial' => $especiales,
		                                                'evals' => $evaluaciones,
		                                                'asistencias' => $asistencias),
		                                         $request);
	}
	
	public function verFormatos ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = Gatuf::factory ('Pato_GPE')->getList ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/formatos.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'gpe' => $gpe,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $cerrarAKardex_precond = array ('Gatuf_Precondition::adminRequired');
	public function cerrarAKardex ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = new Pato_GPE ();
		
		if (false === ($gpe->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verFormatos', $seccion->nrc);
		
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
		
		$request->user->setMessage (1, 'Fueron creadas '.$totales['generados'].' calificaciones en kardex. Se omitieron '.$totales['enkardex'].' registros por que ya existían.');
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $actaCalificaciones_precond = array ('Gatuf_Precondition::adminRequired');
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
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $listaAsistencia_precond = array ('Pato_Precondition::maestroRequired');
	public function listaAsistencia ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator && $request->user->login != $seccion->maestro) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Seccion_Asistencia ('L', 'mm', 'Letter');
		
		$pdf->renderGrupo ($seccion);
		
		$pdf->Close ();
		
		$nombre = 'Lista_asistencias_'.$seccion->nrc.'_'.$seccion->materia.'_'.$seccion->seccion.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $evaluar_precond = array ('Gatuf_Precondition::loginRequired');
	public function evaluar ($request, $match) {
		if ($request->user->type != 'm') {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$eval = new Pato_Evaluacion ();
		
		if (false === ($eval->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->user->login != $seccion->maestro && !$request->user->administrator) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Si hay suplente, el suplente puede subir calificaciones */
		if ($seccion->suplente && $request->user->login != $seccion->suplente) {
			$request->user->setMessage (3, 'Usted no puede subir calificaciones. Hay un suplente asignado a esta sección');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Revisar que el porcentaje exista para esta materia */
		$sql = new Gatuf_SQL ('materia=%s AND evaluacion=%s', array ($seccion->materia, $eval->id));
		
		$ps = Gatuf::factory ('Pato_Porcentaje')->getOne ($sql->gen ());
		
		if ($ps === null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator && $ps->abierto == 0) {
			$request->user->setMessage (3, 'La subida de calificaciones para '.$eval->descripcion.' está cerrada.');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* TODO: Revisar las horas aquí */
		
		$extra = array ('porcentaje' => $ps, 'seccion' => $seccion);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Evaluar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Seccion_Evaluar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/evaluar.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion,
		                                                'porcentaje' => $ps,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $evaluarAsistencias_precond = array ('Gatuf_Precondition::loginRequired');
	public function evaluarAsistencias ($request, $match) {
		if ($request->user->type != 'm') {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* TODO: Eliminar el permiso de que si es administrador */
		if ($request->user->login != $seccion->maestro && !$request->user->administrator) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Si hay suplente, el suplente puede subir asistencias */
		if ($seccion->suplente && $request->user->login != $seccion->suplente) {
			$request->user->setMessage (3, 'Usted no puede subir asistencias. Hay un suplente asignado a esta sección');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
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
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/asistencias.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	/*public $reclamarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function reclamarNrc ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
		
		$carrera_a_reclamar = new Calif_Carrera ();
		
		if (false === ($carrera_a_reclamar->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}*/
		
		/* Verificar que la materia pertenezca a la carrera
		$materia = new Calif_Materia ($seccion->materia);
		
		$carreras = $materia->get_carreras_list ();
		
		$permiso = false;
		
		foreach ($carreras as $c) {
			if ($carrera_a_reclamar->clave == $c->clave) $permiso = true;
		}
		
		if ($permiso == false) {
			$request->user->setMessage (2, sprintf ('No puede reclamar esta sección. La materia "%s" no pertenece a la carrera %s', $materia->descripcion, $carrera_a_reclamar->descripcion));
			return new Gatuf_HTTP_Response_Redirect ($url);
		} */
		
		/* Verificar que el maestro sea coordinador de la carrera que quiere reclamar
		if (!$request->user->hasPerm ('SIIAU.coordinador.'.$carrera_a_reclamar->clave)) {
			$request->user->setMessage (2, 'No puede reclamar secciones para '.$carrera_a_reclamar->clave.'. Usted no es coordinador de esta carrera');
			return new Gatuf_HTTP_Response_Redirect ($url);
		} */
		
		/* Si ya está asignado, marcar error
		if (!is_null ($seccion->asignacion)) {
			$request->user->setMessage (2, 'La sección ya ha sido reclamada por '.$seccion->asignacion);
			return new Gatuf_HTTP_Response_Redirect ($url);
		} */
		
		/* Ahora, intentar asignar el nrc
		$seccion->asignacion = $carrera_a_reclamar;
		
		if ($seccion->updateAsignacion () === true) {
			$request->user->setMessage (1, 'La sección '.$seccion->nrc.' ha sido marcada para la carrera '.$carrera_a_reclamar->clave);
		} else {
			$request->user->setMessage (2, 'La sección '.$seccion->nrc.' no pudo ser reclamada. Por favor intentelo otra vez');
		}
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}*/
	
	/*public $liberarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function liberarNrc ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
		
		if (is_null ($seccion->asignacion)) {
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if (!$request->user->hasPerm ('SIIAU.coordinador.'.$seccion->asignacion)) {
			$request->user->setMessage (2, 'No puede liberar la sección '.$seccion->nrc.', usted no es el coordinador que la solicitó.');
		} else {
			$seccion->liberarAsignacion ();
			$request->user->setMessage (1, 'La sección '.$seccion->nrc.' ha sido liberada.');
		}
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}*/
	
	public $matricular_precond = array ('Gatuf_Precondition::adminRequired');
	public function matricular ($request, $match){
		$seccion =  new Pato_Seccion ();
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				/* TODO: Realizar validaciones antes de matricular al Alumno */
				$ins = $alumno->get_current_inscripcion();
				$est = $ins->get_estatus ();
				
				if (!$est->activo) {
					$request->user->setMessage (3, 'No se puede matricular al alumno '.((string) $alumno).' porque está inactivo');
					
					$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
				
				if ($est->clave == 'LI') {
					$request->user->setMessage (3, 'No se puede matricular al alumno '.((string) $alumno).' porque está de Licencia');
					
					$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
				
				/* Buscar que no esté matriculado en otra seccion de esta misma materia */
				$grupos = $alumno->get_grupos_list ();
				foreach ($grupos as $g) {
					if ($seccion->materia == $g->materia) {
						$request->user->setMessage (3, 'El alumno '.((string) $alumno).' ya está matriculado en otra sección ('.$g->seccion.') de esta misma materia');
					
						$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
						return new Gatuf_HTTP_Response_Redirect ($url);
					}
				}
				
				$alumno->setAssoc ($seccion);
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/matricular.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $desmatricular_precond = array ('Gatuf_Precondition::adminRequired');
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
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verAlumnos', array ($seccion->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/desmatricular.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion,
		                                                'alumno' => $alumno),
		                                         $request);
	}
}
