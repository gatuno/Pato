<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Maestro {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		$pag = new Gatuf_Paginator ($maestro);
		$pag->action = array ('Pato_Views_Maestro::index');
		$pag->summary = 'Lista de maestros';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
			array ('grado', 'Gatuf_Paginator_FKExtra', 'Grado'),
		);

		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron profesores';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/index.html',
		                                         array('page_title' => 'Profesores',
		                                               'paginador' => $pag),
		                                         $request);
	}
	
	public function verMaestro ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/ver-maestro.html',
		                                         array('page_title' => $title,
		                                               'maestro' => $maestro,),
		                                         $request);
	}
	
	public function verHorario ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$sec = new Pato_Seccion ();
		$sql = new Gatuf_SQL ('secciones_view.maestro = %s', array ($maestro->codigo));
		$grupos = $sec->getList (array ('view' => 'paginador', 'filter' => $sql->gen ()));
		
		$sql = new Gatuf_SQL ('secciones_view.suplente = %s', array ($maestro->codigo));
		$grupos_suplente = $sec->getList (array ('view' => 'paginador', 'filter' => $sql->gen ()));
		if (count ($grupos_suplente) > 0){
			foreach ($grupos_suplente as $suple) {
			$grupos[] = $suple;
			}
		}
		
		if (count ($grupos) == 0) {
			$horario_maestro = null;
			$grupos = array ();
		} else {
			$horario_maestro = new Gatuf_Calendar ();
			$horario_maestro->events = array ();
			$horario_maestro->opts['conflicts'] = true;
			$horario_maestro->opts['conflict-color'] = '#FFE428';
			
			foreach ($grupos as $grupo) {
				if ($grupo->suplente && $grupo->suplente != $maestro->codigo) continue;
				$horas = $grupo->get_pato_horario_list ();
				
				foreach ($horas as $hora) {
					$cadena_desc = $grupo->materia.' '.$grupo->seccion;
					$dia_semana = strtotime ('next Monday');
					
					foreach (array ('l', 'm', 'i', 'j', 'v', 's') as $dia) {
						if ($hora->$dia) {
							$horario_maestro->events[] = array ('start' => date('Y-m-d ', $dia_semana).$hora->inicio,
											             'end' => date('Y-m-d ', $dia_semana).$hora->fin,
											             'content' => ((string) $hora->get_salon ()).'<br />'.$cadena_desc,
											             'title' => '',
											             'url' => '.');
						}
						$dia_semana = $dia_semana + 86400;
					}
				}
			}
		}
		
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/ver-horario.html',
		                                         array('page_title' => $title,
		                                               'maestro' => $maestro,
		                                               'calendario' => $horario_maestro,
                                                       'grupos' => $grupos),
                                                 $request);
	}
	
	public $agregarMaestro_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarMaestro ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Maestro_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				Gatuf_Log::info (sprintf ('El maestro %s ha sido creado por el usuario %s (%s)', $maestro->codigo, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($maestro->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Maestro_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/agregar-maestro.html',
		                                         array ('page_title' => 'Nuevo profesor',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarMaestro_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarMaestro ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		$extra = array ('maestro' => $maestro);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Maestro_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				Gatuf_Log::info (sprintf ('El maestro %s ha sido actualizado por el usuario %s (%s)', $maestro->codigo, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($maestro->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Maestro_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/edit-maestro.html',
		                                         array ('page_title' => 'Actualizar profesor',
		                                                'maestro' => $maestro,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $buscarJSON_precond = array ('Gatuf_Precondition::loginRequired');
	public function buscarJSON ($request, $match) {
		if (!isset ($request->GET['term'])) {
			return new Gatuf_HTTP_Response_Json (array ());
		}
		
		$bus = '%'.$request->GET['term'].'%';
		
		$sql = new Gatuf_SQL ('nombre LIKE %s OR apellido LIKE %s or codigo LIKE %s', array ($bus, $bus, $bus));
		$maestros = Gatuf::factory ('Pato_Maestro')->getList (array ('filter' => $sql->gen ()));
		
		$response = array ();
		foreach ($maestros as $maestro) {
			$o = new stdClass();
			$o->value = (string) $maestro->codigo;
			$o->label = (string) $maestro;
			
			$response[] = $o;
		}
		
		return new Gatuf_HTTP_Response_Json ($response);
	}
	
	public function verHorarioPDF ($request, $match, $params = array ()) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		throw new Exception ('No implementado. Revisar formato de horario');
	}
	
	public $permisos_precond = array ('Gatuf_Precondition::adminRequired');
	public function permisos ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getUser ();
		$extra = array ('user' => $maestro->user);
		
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		$permisos_usuario = $maestro->user->getAllPermissions();
		if ($maestro->user->administrator || count ($permisos_usuario) == Gatuf::factory ('Gatuf_Permission')->getCount ()) {
			/* Tiene todos los permisos, no hay nada que agregar */
			$form = null;
		} else {
			$form = new Pato_Form_Usuario_Permisos (null, $extra);
		}
		
		$permisos_usuario = $maestro->user->get_permissions_list ();
		$grupos = $maestro->user->get_groups_list ();
		
		if (count ($grupos) == Gatuf::factory ('Gatuf_Group')->getCount ()) {
			$form2 = null;
		} else {
			$form2 = new Pato_Form_Usuario_Grupos (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/permisos.html',
		                                         array( 'page_title' => $title,
		                                                'maestro' => $maestro,
		                                                'permisos' => $permisos_usuario,
		                                                'grupos' => $grupos,
		                                                'form' => $form,
		                                                'form2' => $form2),
		                                         $request);
	}
}
