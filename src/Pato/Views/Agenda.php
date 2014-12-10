<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Agenda {
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
		
		$ins = $alumno->get_current_inscripcion ();
		$abierta = false;
		$agenda = null;
		if ($ins != null) {
			if (count ($list) != 0) {
				$agenda = $list[0];
				$unix_inicio = strtotime ($agenda->inicio);
				$unix_fin = strtotime ($agenda->fin);
				if ($unix_time > $unix_inicio && $unix_time < $unix_fin) {
					$abierta = true;
				}
			}
		}
		
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'abierta' => $abierta,
		                                                'hora' => $hora,
		                                                'agenda' => $agenda,
		                                                'inscripcion' => $ins),
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
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_CrearAgenda ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$agenda = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_CrearAgenda (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/crear-agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
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
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('agenda' => $list[0]);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_CambiarAgenda ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$agenda = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
				
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
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
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
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		$request->session->setData ('CAL_ACTIVO', $calendario->clave);
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
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
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
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
					if (false === ($seccion->get ($nrc_nuevo))) {
						$request->user->setMessage (3, 'El NRC '.$nrc_nuevo.' no existe');
						continue;
					}
					
					/* Revisar cupos aquí */
					$count = $seccion->get_alumnos_list (array ('count' => true));
					if ($count >= $seccion->cupo) {
						$request->user->setMessage (2, 'El NRC '.$seccion->nrc.' tiene cupo lleno');
						continue;
					}
					
					$materia = $seccion->get_materia();
					
					/* Si el alumno tiene pasada la materia, no la puede recursar */
					$sql_k = new Gatuf_SQL ('(materia=%s AND aprobada=1)', $seccion->materia);
					$kardexs = $alumno->get_kardex_list (array ('filter' => $sql_k->gen (), 'count' => true));
					
					if ($kardexs > 0) {
						$request->user->setMessage (2, 'Ya acreditaste la materia '.$materia->descripcion.'. El NRC '.$seccion->nrc.' se ignora');
						continue;
					}
					
					/* Revisar que la materia pertenezca a su carrera actual */
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
						$request->user->setMessage (3, 'No puedes matricular el NRC '.$seccion->nrc.' porque la materia '.$materia->descripcion.' no pertenece a tu carrera actual');
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
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::registro', $alumno->codigo);
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
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
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
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::registro', $alumno->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
}
