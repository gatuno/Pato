<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Alumno {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		$alumno =  new Pato_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->action = array ('Pato_Views_Alumno::index');
		$pag->summary = 'Lista de los alumnos';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron alumnos';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/index.html',
		                                         array('page_title' => 'Alumnos',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public $agregarAlumno_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.crear_alumno'));
	public function agregarAlumno ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				Gatuf_Log::info (sprintf ('El alumno %s fue creado a petición del usuario %s', $alumno->codigo, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agregar-alumno.html',
		                                         array ('page_title' => 'Nuevo alumno',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $passwordReset_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.reset_password'));
	public function passwordReset ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$url_af = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($alumno->codigo));
		
		if (!$alumno->active) {
			$request->user->setMessage (3, 'No se puede reestablecer la contraseña del alumno porque se encuentra inactivo');
			return new Gatuf_HTTP_Response_Redirect ($url_af);
		}
		
		Pato_Form_Login_PasswordRecovery::send_code ($alumno);
		
		$request->user->setMessage (1, sprintf ('Se ha enviado un correo a "%s" para resetear la contraseña. Expira en 12 horas', $alumno->email));
		return new Gatuf_HTTP_Response_Redirect ($url_af);
	}
	
	public $verCalificaciones_precond = array ('Gatuf_Precondition::loginRequired');
	public function verCalificaciones ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$res = Pato_Precondition::selfAlumnoOrHasPerm ($request, $alumno, 'Patricia.boleta_alumno');
		if (true !== $res) {
			return $res;
		}
		
		$secciones = $alumno->get_grupos_list(array ('view' => 'paginador'));
		
		$especiales = array (3 => 'IN', 2 => 'SD');
		
		/* Conseguir todas las formas de evaluación para todas las secciones */
		$porc_t = Gatuf::factory ('Pato_Porcentaje')->getSqlTable ();
		$eval = new Pato_Evaluacion ();
		$eval_t = $eval->getSqlTable ();
		$eval->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$porc_t.' ON '.$eval_t.'.id=evaluacion');
		
		$evaluaciones = array ();
		$asistencias = array ();
		$boleta = array ();
		$sql_al = new Gatuf_SQL ('alumno=%s', $alumno->codigo);
		foreach ($secciones as $seccion) {
			$sql = new Gatuf_SQL ('materia=%s', $seccion->materia);
			$evaluaciones[$seccion->nrc] = $eval->getList (array ('view' => 'join_materia', 'filter' => $sql->gen ()));
			$t_as = $seccion->get_pato_asistencia_list (array ('filter' => $sql_al->gen ()));
			
			if (count ($t_as) == 0) {
				$asistencias[$seccion->nrc] = null;
			} else {
				$asistencias[$seccion->nrc] = $t_as[0];
			}
			$boleta[$seccion->nrc] = array ();
			foreach ($seccion->get_pato_boleta_list (array ('filter' => $sql_al->gen ())) as $b) {
				$boleta[$seccion->nrc][$b->evaluacion] = $b->calificacion;
			}
		}
		
		/* Recoger las asistencias */
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-calificaciones.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'secciones' => $secciones,
		                                               'evals' => $evaluaciones,
		                                               'boleta' => $boleta,
		                                               'especial' => $especiales,
		                                               'asistencias' => $asistencias),
                                                 $request);
	}
	
	public $verHorario_precond = array ('Gatuf_Precondition::loginRequired');
	public function verHorario ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$res = Pato_Precondition::selfAlumnoOrHasPerm ($request, $alumno, 'Patricia.horario_alumno');
		if (true !== $res) {
			return $res;
		}
		
		$secciones = $alumno->get_grupos_list(array ('view' => 'paginador'));
		
		$calendario = new Gatuf_Calendar ();
		$calendario->events = array ();
		$calendario->opts['conflicts'] = false;
		
		foreach ($secciones as $seccion) {
			$horas = $seccion->get_pato_horario_list ();
			
			foreach ($horas as $hora) {
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
				$cadena_desc = sprintf ('%s <a href="%s">%s</a><br />', $seccion->materia, $url, $seccion->seccion);
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Edificio::verEdificio', $hora->get_salon()->edificio).'#salon_'.$hora->salon;
				$dia_semana = strtotime ('next Monday');
				
				foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
					if ($hora->$dia) {
						$calendario->events[] = array ('start' => date('Y-m-d ', $dia_semana).$hora->inicio,
										             'end' => date('Y-m-d ', $dia_semana).$hora->fin,
										             'title' => (string) $hora->get_salon (),
										             'content' => $cadena_desc,
										             'url' => $url);
					}
					$dia_semana = $dia_semana + 86400;
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-horario.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'secciones' => $secciones,
		                                               'calendario' => $request->calendario,
		                                               'horario' => $calendario),
                                                 $request);
	}
	
	public $actualizarAlumno_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.editar_alumno'));
	public function actualizarAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === $alumno->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				Gatuf_Log::info (sprintf ('Los datos del alumno %s fueron actualizados por el usuario %s', $alumno->codigo, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/edit-alumno.html',
		                                         array ('page_title' => 'Actualizar alumno',
		                                                'alumno' => $alumno,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $buscarJSON_precond = array ('Gatuf_Precondition::adminRequired');
	public function buscarJSON ($request, $match) {
		if (!isset ($request->GET['term'])) {
			return new Gatuf_HTTP_Response_Json (array ());
		}
		
		$bus = '%'.$request->GET['term'].'%';
		
		$sql = new Gatuf_SQL ('nombre LIKE %s OR apellido LIKE %s or codigo LIKE %s', array ($bus, $bus, $bus));
		$alumnos = Gatuf::factory ('Pato_Alumno')->getList (array ('filter' => $sql->gen ()));
		
		$response = array ();
		foreach ($alumnos as $alumno) {
			$o = new stdClass();
			$o->value = (string) $alumno->codigo;
			$o->label = (string) $alumno;
			
			$response[] = $o;
		}
		
		return new Gatuf_HTTP_Response_Json ($response);
	}
	
	public $verFormatos_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.imprimir_boleta_alumno')));
	public function verFormatos ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/formatos.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno),
		                                         $request);
	}
	
	public $boleta_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.imprimir_boleta_alumno'));
	public function boleta ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gpe = new Pato_GPE ();
		if (false === ($gpe->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Alumno_Boleta ('P', 'mm', 'Letter');
		
		$pdf->renderBoleta ($alumno, $request->calendario, $gpe);
		
		$pdf->Close ();
		
		$nombre = 'boleta_'.$alumno->codigo.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $kardex_precond = array ('Gatuf_Precondition::loginRequired');
	public function kardex ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$res = Pato_Precondition::selfAlumnoOrHasPerm ($request, $alumno, 'Patricia.kardex_alumno');
		if (true !== $res) {
			return $res;
		}
		
		/* Presentar el kardex organizado por carreras */
		$inscripciones = $alumno->get_inscripciones_list (array ('order' => 'egreso ASC'));
		
		if (count ($inscripciones) == 1) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::kardexCarrera', array ($alumno->codigo, $inscripciones[0]->id));
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/kardex-lista.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'inscripciones' => $inscripciones),
		                                         $request);
	}
	
	public $kardexCarrera_precond = array ('Gatuf_Precondition::loginRequired');
	public function kardexCarrera ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$res = Pato_Precondition::selfAlumnoOrHasPerm ($request, $alumno, 'Patricia.kardex_alumno');
		if (true !== $res) {
			return $res;
		}
		
		$inscripcion = new Pato_Inscripcion ();
		
		if (false === ($inscripcion->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($inscripcion->alumno != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$estatus = $inscripcion->get_current_estatus ();
		
		/* Recoger las materias en kardex pertenecientes a esta carrera */
		$carrera = $inscripcion->get_carrera ();
		$materia = new Pato_Materia ();
		$hay = array(strtolower($carrera->_a['model']), 
						 strtolower($materia->_a['model']));
		// Calcular la base de datos que contiene la relación M-N
		if (isset ($GLOBALS['_GATUF_models_related'][$hay[0]][$hay[1]])) {
			// La relación la tiene el $hay[1]
			$dbname = $materia->_con->dbname;
			$dbpfx = $materia->_con->pfx;
		} else {
			$dbname = $carrera->_con->dbname;
			$dbpfx = $carrera->_con->pfx;
		}
		sort($hay);
		$table = $dbpfx.$hay[0].'_'.$hay[1].'_assoc';
		
		$kardex = new Pato_Kardex ();
		$kardex->_a['views'] = array ('join_carrera' => array ());
		$kardex->_a['views']['join_carrera']['join'] = ' LEFT JOIN '.$dbname.'.'.$table.' ON '
				.$kardex->_con->qn(strtolower($materia->_a['model']).'_'.$materia->primary_key).' = materia';
		
		$sql = new Gatuf_SQL($kardex->_con->qn(strtolower($carrera->_a['model']).'_'.$carrera->primary_key).'=%s AND alumno=%s', array ($carrera->clave, $alumno->codigo));
		
		$kardexs = $kardex->getList (array ('filter' => $sql->gen (), 'view' => 'join_carrera', 'order' => 'calendario ASC'));
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/kardex.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'inscripcion' => $inscripcion,
		                                                'kardexs' => $kardexs,
		                                                'estatus' => $estatus),
		                                         $request);
	}
	
	public $verPerfil_precond = array ('Gatuf_Precondition::loginRequired');
	public function verPerfil ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		setlocale (LC_TIME, 'es_MX');
		/* Recuperar el perfil del alumno */
		$perfiles = $alumno->get_pato_perfilalumno_list();
		
		if (count ($perfiles) == 0) {
			/* Aún no tiene un perfil, crearlo */
			$perfil = new Pato_PerfilAlumno ();
			
			$perfil->alumno = $alumno;
			$perfil->create ();
		} else {
			$perfil = $perfiles[0];
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/perfil.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'perfil' => $perfil),
		                                         $request);
	}
	
	public $editarPerfil_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.editar_alumno'));
	public function editarPerfil ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Recuperar el perfil del alumno */
		$perfiles = $alumno->get_pato_perfilalumno_list();
		
		if (count ($perfiles) == 0) {
			/* Aún no tiene un perfil, crearlo */
			$perfil = new Pato_PerfilAlumno ();
			
			$perfil->alumno = $alumno;
			$perfil->create ();
		} else {
			$perfil = $perfiles[0];
		}
		
		$extra = array ('perfil' => $perfil);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_ActualizarPerfil ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				Gatuf_Log::info (sprintf ('El perfil del alumno %s fué actualizado por el usuario %s', $alumno->codigo, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_ActualizarPerfil (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/editar-perfil.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'form' => $form),
		                                         $request);
	}
	
}
