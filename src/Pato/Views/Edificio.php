<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Edificio {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		$edificio = new Pato_Edificio ();
		
		$pag = new Gatuf_Paginator ($edificio);
		$sql = new Gatuf_SQL ('oculto=0');
		$pag->forced_where = $sql;
		$pag->action = array ('Pato_Views_Edificio::index');
		$pag->summary = 'Lista de los edificios';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Descripción')
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No hay edificios';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion'),
			array ('clave', 'descripcion')
		);
		
		$pag->setFromRequest ($request);
		
		$cant_edificios = Gatuf::factory ('Pato_Edificio')->getList (array ('count' => true));
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/edificio/index.html',
		                                         array('page_title' => 'Edificios',
                                                       'paginador' => $pag,
                                                       'cant_edificios' => $cant_edificios),
                                                 $request);
	}
	
	public $verEdificio_precond = array ('Gatuf_Precondition::loginRequired');
	public function verEdificio ($request, $match) {
		$edificio = new Pato_Edificio ();
		
		if (false === $edificio->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que el edificio esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Edificio::verEdificio', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$salones = $edificio->get_pato_salon_list (array ('filter' => 'oculto=0'));
		
		$salones_ocultos = $edificio->get_pato_salon_list (array ('filter' => 'oculto=1', 'count' => true));
		
		$hay_ocultos = false;
		if ($salones_ocultos > 0) {
			$hay_ocultos = true;
		}
		
		$super_calendarios = array ();
		foreach ($salones as $salon) {
			$sql = new Gatuf_SQL ('salon=%s', $salon->id);
			$horas_salon = $salon->get_pato_horario_list ();
			
			if (count ($horas_salon) == 0) {
				$super_calendarios[$salon->id] = null;
				continue;
			}
			$calendar = new Gatuf_Calendar ();
			$calendar->events = array ();
			$calendar->opts['conflicts'] = true;
			$calendar->opts['conflict-color'] = '#FF2828';
			
			$nrc = new Pato_Seccion ();
			foreach ($horas_salon as $horario) {
				$nrc->get ($horario->nrc);
				$cadena_desc = $nrc->materia . ' ' . $nrc->seccion.'<br />';
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $nrc->nrc);
				$dia_semana = strtotime ('next Monday');
				$calendar->opts['start-day'] = date('Y-m-d', $dia_semana);
				foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
					if ($horario->$dia) {
						if ($horario->inicio instanceof DateTime) {
							$h_i = $horario->inicio->format ('H:i');
						} else {
							$h_i = $horario->inicio;
						}
						
						if ($horario->fin instanceof DateTime) {
							$h_f = $horario->fin->format ('H:i');
						} else {
							$h_f = $horario->fin;
						}
						$calendar->events[] = array ('start' => date('Y-m-d ', $dia_semana).$h_i,
								                     'end' => date('Y-m-d ', $dia_semana).$h_f,
								                     'title' => $horario->nrc,
								                     'content' => $cadena_desc,
								                     'url' => $url, 'color' => '');
					}
					$dia_semana = $dia_semana + 86400;
				}
				$calendar->opts['end-day'] = date('Y-m-d', $dia_semana);
			}
			
			$super_calendarios[$salon->id] = $calendar;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/edificio/ver-edificio.html',
		                                         array('page_title' => 'Edificio '.$edificio->clave,
		                                               'edificio' => $edificio,
		                                               'salones' => $salones,
                                                       'calendarios' => $super_calendarios,
                                                       'hay_ocultos' => $hay_ocultos),
                                                 $request);
	}

	public $agregarEdificio_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_edificios_salones'));
	public function agregarEdificio ($request, $match) {
		$extra = array ();
		if ($request->method == 'POST') {
			$form = new Pato_Form_Edificio_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$edificio = $form->save ();
				
				Gatuf_Log::info (sprintf ('El edificio %s ha sido creado por el usuario %s', $edificio->clave, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Edificio::verEdificio', array ($edificio->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Edificio_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/edificio/agregar-edificio.html',
		                                         array ('page_title' => 'Nuevo Edificio',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $salonesOcultos_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_edificios_salones'));
	public function salonesOcultos ($request, $match) {
		$edificio = new Pato_Edificio ();
		
		if (false === $edificio->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que el edificio esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Edificio::verEdificio', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$salones = $edificio->get_pato_salon_list (array ('filter' => 'oculto=1'));
		
		if (count ($salones) == 0) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Edificio::verEdificio', array ($edificio->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/edificio/salones-ocultos.html',
		                                         array ('page_title' => 'Edificio '.$edificio->clave,
		                                               'edificio' => $edificio,
		                                               'ocultos' => $salones),
		                                         $request);
	}
	
	public $ocultarEdificio_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_edificios_salones'));
	public function ocultarEdificio ($request, $match) {
		$edificio = new Pato_Edificio ();
		
		if (false === $edificio->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que el edificio esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Edificio::verEdificio', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($edificio->oculto == 1) {
			/* ¿Ya está oculto? */
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Edificio::verEdificio', $edificio->clave);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$edificio->oculto = 1;
			
			$edificio->update ();
			
			foreach ($edificio->get_pato_salon_list () as $s) {
				$s->oculto = 1;
				
				$s->update ();
			}
			
			Gatuf_Log::info (sprintf ('El edificio %s ha sido ocultado por el usuario %s', $edificio->clave, $request->user->codigo));
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Edificio::verEdificio', $edificio->clave);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}

		return Gatuf_Shortcuts_RenderToResponse ('pato/edificio/ocultar-edificio.html',
		                                         array('page_title' => 'Edificio '.$edificio->clave,
		                                               'edificio' => $edificio),
                                                 $request);
	}
}
