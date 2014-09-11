<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Alumno {
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
	
	public $agregarAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarAlumno ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($alumno->codigo));
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
	
	
	public function verAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-alumno.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno),
                                                 $request);
	}
	
	public function verInscripciones ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$inscripciones = $alumno->get_inscripciones_list (array ('order' => 'ingreso DESC'));
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-inscripciones.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'inscripciones' => $inscripciones),
                                                 $request);
	}
	
	public function verGrupos ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
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
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-grupos.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'secciones' => $secciones,
		                                               'evals' => $evaluaciones,
		                                               'boleta' => $boleta,
		                                               'especial' => $especiales,
		                                               'asistencias' => $asistencias),
                                                 $request);
	}
	
	public function verHorario ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
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
	
	public $actualizarAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === $alumno->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($alumno->codigo));
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
	
	public $verFormatos_precond = array ('Gatuf_Precondition::adminRequired');
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
	
	public $boleta_precond = array ('Gatuf_Precondition::adminRequired');
	public function boleta ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Alumno_Boleta ('P', 'mm', 'Letter');
		
		$pdf->renderBoleta ($alumno);
		
		$pdf->Close ();
		
		$nombre = 'boleta_'.$alumno->codigo.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $agenda_precond = array ('Gatuf_Precondition::loginRequired');
	public function agenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator) {
			if ($request->user->login != $alumno->codigo) {
				throw new Gatuf_HTTP_Error404 ();
			}
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$abierta = false;
		if (count ($list) == 0) {
			$agenda = null;
		} else {
			$agenda = $list[0];
			$unix_inicio = strtotime ($agenda->inicio);
			$unix_fin = strtotime ($agenda->fin);
			if ($unix_time > $unix_inicio && $unix_time < $unix_fin) {
				$abierta = true;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'abierta' => $abierta,
		                                                'hora' => $hora,
		                                                'agenda' => $agenda),
		                                         $request);
	}
	
	public $crearAgenda_precond = array ('Gatuf_Precondition::adminRequired');
	public function crearAgenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) != 0) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_CrearAgenda ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$agenda = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::agenda', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_CrearAgenda (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/crear-agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $request->calendario,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $cambiarAgenda_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarAgenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$extra = array ('agenda' => $list[0]);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_CambiarAgenda ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$agenda = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::agenda', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_CambiarAgenda (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/cambiar-agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $request->calendario,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarAgenda_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarAgenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$list[0]->delete ();
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::agenda', $alumno->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $registro_precond = array ('Gatuf_Precondition::loginRequired');
	public function registro ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->user->type != 'a' || $request->user->login != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::agenda', $alumno->codigo);
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else {
			$agenda = $list[0];
		}
		
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$unix_inicio = strtotime ($agenda->inicio);
		$unix_fin = strtotime ($agenda->fin);
		
		if ($unix_time < $unix_inicio || $unix_time > $unix_fin) {
			$request->user->setMessage (3, 'Tu agenda aún no permite el registro');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::agenda', $alumno->codigo);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Registro ($request->POST);
			
			if ($form->isValid ()) {
				$nrcs = $form->save ();
				
				/* Sacar todos los horarios del alumno */
				$horarios = array ();
				$secciones_al = $alumno->get_grupos_list ();
				
				foreach ($secciones_al as $seccion) {
					foreach ($seccion->get_pato_horario_list () as $h_sec) {
						$horarios[] = $h_sec;
					}
				}
		
				$seccion = new Pato_Seccion ();
		
				foreach ($nrcs as $nrc_nuevo) {
					/* Intentar matricular el alumno en los nrc */
					if (false === ($seccion->get ($nrc_nuevo))) continue;
					
					/* Revisar cupos aquí */
					$count = $seccion->get_alumnos_list (array ('count' => true));
					if ($count >= $seccion->cupo) {
						$request->user->setMessage (2, 'El NRC '.$seccion->nrc.' tiene cupo lleno');
						continue;
					}
					
					$secciones_al = $alumno->get_grupos_list ();
					/* Revisar que no haya matriculado otro curso de la misma materia */
					$choque = false;
					foreach ($secciones_al as $sec_al) {
						if ($sec_al->nrc == $seccion->nrc) {
							$request->user->setMessage (2, 'El NRC '.$seccion->nrc.' ya está matriculado');
							$choque = true;
							continue;
						}
						
						if ($sec_al->materia == $seccion->materia) {
							$request->user->setMessage (2, 'El NRC '.$seccion->nrc.' no se puede agregar porque ya tienes registrado otro NRC de esa misma materia');
							$choque = true;
							continue;
						}
					}
					
					if ($choque) continue;
					
					$horas = $seccion->get_pato_horario_list ();
					
					/* Chocar todos los horarios contra los horarios del alumno */
					$choque = false;
					foreach ($horarios as $h_al) {
						foreach ($horas as $h_sec) {
							if (Pato_Horario::chocan ($h_al, $h_sec)) $choque = true;
						}
					}
					
					if ($choque) {
						$request->user->setMessage (2, 'El NRC '.$seccion->nrc.' tiene conflictos de horario');
						continue;
					}
					
					$alumno->setAssoc ($seccion);
					/* Agregar las horas al alumno */
					foreach ($horas as $h_sec) {
						$horarios[] = $h_sec;
					}
				}
			}
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::registro', $alumno->codigo);
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else {
			$form = new Pato_Form_Alumno_Registro (null);
		}
		
		$secciones_al = $alumno->get_grupos_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/registro.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'secciones' => $secciones_al,
		                                                'form' => $form,
		                                                'agenda' => $agenda),
		                                         $request);
	}
	
	public $registroEliminar_precond = array ('Gatuf_Precondition::loginRequired');
	public function registroEliminar ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->user->type != 'a' || $request->user->login != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$seccion = new Pato_Seccion ();
		if (false === ($seccion->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::agenda', $alumno->codigo);
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else {
			$agenda = $list[0];
		}
		
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$unix_inicio = strtotime ($agenda->inicio);
		$unix_fin = strtotime ($agenda->fin);
		
		if ($unix_time < $unix_inicio || $unix_time > $unix_fin) {
			$request->user->setMessage (3, 'Tu agenda aún no permite el registro');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$alumno->delAssoc ($seccion);
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::registro', $alumno->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
}
