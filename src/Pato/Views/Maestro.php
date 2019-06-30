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
	
	public $verMaestro_precond = array ('Gatuf_Precondition::loginRequired');
	public function verMaestro ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/maestro/ver-maestro.html',
		                                         array('page_title' => $title,
		                                               'maestro' => $maestro,),
		                                         $request);
	}
	
	public $verHorario_precond = array ('Gatuf_Precondition::loginRequired');
	public function verHorario ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$sec = new Pato_Seccion ();
		$dbpfx = $sec->_con->pfx;
		$sql = new Gatuf_SQL ($dbpfx.'secciones_view.maestro = %s', array ($maestro->codigo));
		$grupos = $sec->getList (array ('view' => 'paginador', 'filter' => $sql->gen ()));
		
		$sql = new Gatuf_SQL ($dbpfx.'secciones_view.suplente = %s', array ($maestro->codigo));
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
							if ($hora->inicio instanceof DateTime) {
								$h_i = $hora->inicio->format ('H:i');
							} else {
								$h_i = $hora->inicio;
							}
						
							if ($hora->fin instanceof DateTime) {
								$h_f = $hora->fin->format ('H:i');
							} else {
								$h_f = $hora->fin;
							}
							$horario_maestro->events[] = array ('start' => date('Y-m-d ', $dia_semana).$h_i,
											             'end' => date('Y-m-d ', $dia_semana).$h_f,
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
	
	public $agregarMaestro_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_profesores'));
	public function agregarMaestro ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Maestro_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				Gatuf_Log::info (sprintf ('El maestro %s ha sido creado por el usuario %s', $maestro->codigo, $request->user->codigo));
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
	
	public $actualizarMaestro_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_profesores'));
	public function actualizarMaestro ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('maestro' => $maestro);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Maestro_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$maestro = $form->save ();
				
				Gatuf_Log::info (sprintf ('El maestro %s ha sido actualizado por el usuario %s', $maestro->codigo, $request->user->codigo));
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
	
	public $passwordReset_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_profesores'));
	public function passwordReset ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$url_af = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($maestro->codigo));
		
		if (!$maestro->active) {
			$request->user->setMessage (3, 'No se puede reestablecer la contraseña del profesor porque se encuentra inactivo');
			return new Gatuf_HTTP_Response_Redirect ($url_af);
		}
		
		Pato_Form_Login_PasswordRecovery::send_code ($maestro);
		
		$request->user->setMessage (1, sprintf ('Se ha enviado un correo a "%s" para resetear la contraseña. Expira en 12 horas', $maestro->email));
		return new Gatuf_HTTP_Response_Redirect ($url_af);
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
	
	public $verHorarioPDF_precond = array ('Gatuf_Precondition::loginRequired');
	public function verHorarioPDF ($request, $match, $params = array ()) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		throw new Exception ('No implementado. Revisar formato de horario');
	}
	
	public $permisos_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.grant'));
	public function permisos ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('usuario' => $maestro);
		
		$title = (($maestro->sexo == 'M') ? 'Profesor ':'Profesora ').$maestro->nombre.' '.$maestro->apellido;
		
		$permisos_usuario = $maestro->getAllPermissions();
		if ($maestro->administrator || count ($permisos_usuario) == Gatuf::factory ('Gatuf_Permission')->getCount ()) {
			/* Tiene todos los permisos, no hay nada que agregar */
			$form = null;
		} else {
			$form = new Pato_Form_Usuario_Permisos (null, $extra);
		}
		
		$permisos_usuario = $maestro->get_permissions_list ();
		$grupos = $maestro->get_groups_list ();
		
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
	
	public $agregarPermiso_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.grant'));
	public function agregarPermiso ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('usuario' => $maestro);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Usuario_Permisos ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$permiso = $form->save ();
				
				Gatuf_Log::info (sprintf ('Se asignó el permiso %s al profesor %s. Asignación hecha por el usuario %s', $permiso->code_name, $maestro->codigo, $request->user->codigo));
			}
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $maestro->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}

	public $eliminarPermiso_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.grant'));
	public function eliminarPermiso($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST' && isset ($request->POST['permiso'])) {
			$permiso = new Gatuf_Permission ();
			
			if (false !== $permiso->get ($request->POST['permiso'])) {
				$maestro->delAssoc($permiso);
				
				Gatuf_Log::info (sprintf ('Se quitó el permiso %s del profesor %s. Eliminación por el usuario %s', $permiso->code_name, $maestro->codigo, $request->user->codigo));
			}
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $maestro->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $agregarGrupo_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.grant'));
	public function agregarGrupo($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('usuario' => $maestro);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Usuario_Grupos ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$grupo = $form->save ();
				
				Gatuf_Log::info (sprintf ('Se asignó el grupo %s al profesor %s. Asignación hecha por el usuario %s', $grupo->name, $maestro->codigo, $request->user->codigo));
			}
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $maestro->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $eliminarGrupo_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.grant'));
	public function eliminarGrupo($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === $maestro->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST' && isset ($request->POST['grupo'])) {
			$grupo = new Gatuf_Group ();
			
			if (false !== $grupo->get ($request->POST['grupo'])) {
				$maestro->delAssoc($grupo);
				
				Gatuf_Log::info (sprintf ('Se quitó el grupo %s al profesor %s. Eliminación por el usuario %s', $grupo->name, $maestro->codigo, $request->user->codigo));
			}
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $maestro->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
}
