<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Materia {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		/* Listar las materias aquí */
		$filtro = array();
		$materia = new Pato_Materia ();
		
		/* Aplicando filtrado por carrera y/o departamento */
		if ($request->method == 'POST') {
			$form = new Pato_Form_Materia_Filtrar ($request->POST);
			$filtrado = $form->save ();	
			if($filtrado[0] == 'c'){
				$filtrado = substr($filtrado, 2);
				$request->session->setData('filtro_materia_carrera',$filtrado);
			}
		} else {
			$form = new Pato_Form_Materia_Filtrar(null);
		}

		/* Verificar filtro de materias por carrera */
		$car = $request->session->getData('filtro_materia_carrera', null);
		if (!is_null ($car)){
			$carrera = new Pato_Carrera ();
			$carrera->get ($car);
			$filtro['c'] = 'Carrera de '.$carrera->descripcion;
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
			
			$materia->_a['views']['paginador']['join'] = ' LEFT JOIN '.$dbname.'.'.$table.' ON '
					.$carrera->_con->qn(strtolower($materia->_a['model']).'_'.$materia->primary_key).' = '.$carrera->primary_key;
			$key = $carrera->primary_key;
			$materia->_a['views']['paginador']['where'] = $carrera->_con->qn(strtolower($carrera->_a['model']).'_'.$carrera->primary_key).'='.$carrera->_con->esc ($carrera->$key);
		}
		
		$pag = new Gatuf_Paginator ($materia);
		if (!is_null ($car)) $pag->model_view = 'paginador';
		
		$pag->action = array ('Pato_Views_Materia::index');
		
		$pag->summary = 'Lista de las materias';
		
		$list_display = array (
			array ('clave', 'Gatuf_Paginator_FKLink', 'Clave'),
			array ('descripcion', 'Gatuf_Paginator_DisplayVal', 'Materia')
		);
		
		$pag->items_per_page = 40;
		$pag->no_results_text = 'No hay materias';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('clave', 'descripcion'),
			array ('clave', 'descripcion')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/index.html',
		                                         array('page_title' => 'Materias',
		                                         'filtro' => $filtro,
		                                         'form' => $form,
		                                         'paginador' => $pag),
		                                         $request);
	}
	
	public $eliminarFiltro_precond = array ('Gatuf_Precondition::loginRequired');
	public function eliminarFiltro($request, $match){
		if($match[1] == 'c') $request->session->setData('filtro_materia_carrera',null);
		
		$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Materia::index');
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $porCarrera_precond = array ('Gatuf_Precondition::loginRequired');
	public function porCarrera ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$request->session->setData('filtro_materia_carrera',$match[1]);
		
		$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Materia::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $verMateria_precond = array ('Gatuf_Precondition::loginRequired');
	public function verMateria ($request, $match) {
		$materia = new Pato_Materia ();
		
		if (false === ($materia->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$carreras_count = Gatuf::factory ('Pato_Carrera')->getList (array ('count' => true));
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/ver-materia.html',
		                                         array('page_title' => (string) $materia,
		                                               'materia' => $materia,
		                                               'carreras_count' => $carreras_count),
		                                         $request);
	}
	
	public $verHoras_precond = array ('Gatuf_Precondition::loginRequired');
	public function verHoras ($request, $match) {
		$materia = new Pato_Materia ();
		
		if (false === ($materia->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Materia::verMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Listar las secciones de esta materia */
		$seccion = new Pato_Seccion ();
		
		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		$pag->model_view = 'paginador';
		
		$pag->action = array ('Pato_Views_Materia::verMateria', $materia->clave);
		$pag->summary = 'Lista de secciones';
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_FKLink', 'NRC'),
			array ('materia', 'Gatuf_Paginator_DisplayVal', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_FKLink', 'Sección'),
			array ('maestro', 'Gatuf_Paginator_FKLink', 'Maestro'),
		);
		
		/*if ($request->user->isJefe() || $request->user->isCoord ()) {
			$list_display[] = array ('asignacion', 'Gatuf_Paginator_FKExtra', 'Asignacion');
		}*/
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'maestro'),
			array ('nrc', 'materia', 'seccion', 'maestro', 'asignacion')
		);
		
		$sql_filter = new Gatuf_SQL ('materia=%s', $materia->clave);
		$pag->forced_where = $sql_filter;
		$pag->setFromRequest ($request);
		
		/* Recuperar todos las secciones de esta materia */
		$calendario_materia = new Gatuf_Calendar ();
		$calendario_materia->events = array ();
		$calendario_materia->opts['conflicts'] = false;
		
		$secciones = $materia->get_pato_seccion_list ();
		
		$salon_model = new Pato_Salon ();
		
		foreach ($secciones as $seccion) {
			$horas = $seccion->get_pato_horario_list ();
			
			foreach ($horas as $hora) {
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
				$cadena_desc = sprintf ('%s <a href="%s">%s</a><br />', $seccion->materia, $url, $seccion->seccion);
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Edificio::verEdificio', $hora->get_salon()->edificio).'#salon_'.$hora->salon;
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
						$calendario_materia->events[] = array ('start' => date('Y-m-d ', $dia_semana).$h_i,
										             'end' => date('Y-m-d ', $dia_semana).$h_f,
										             'title' => (string) $hora->get_salon (),
										             'content' => $cadena_desc,
										             'url' => $url);
					}
					$dia_semana = $dia_semana + 86400;
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/ver-horas.html',
		                                         array('page_title' => (string) $materia,
		                                               'materia' => $materia,
		                                               'calendario' => $calendario_materia,
		                                               'paginador' => $pag),
		                                         $request);
	}
	
	public $agregarACarrera_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.asociar_carrera_materia'));
	public function agregarACarrera ($request, $match) {
		$materia = new Pato_Materia();
		
		if (($materia->get($match[1]))===false){
			throw new Gatuf_HTTP_Error404();
		}
		
		$assoc_carreras = $materia->get_carreras_list ();
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		
		if (count ($assoc_carreras) == count ($carreras)) {
			/* Ya pertenece a todas las carreras posibles */
			$request->user->setMessage (2, 'La materia pertence a todas las carreras posibles');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('materia' => $materia);
		if ($request->method == 'POST') {
				$form = new Pato_Form_Materia_AgregarCarrera ($request->POST, $extra);
				
				if ($form->isValid()) {
					$materia = $form->save ();
					
					Gatuf_Log::info (sprintf ('La materia %s ha sido agregada a la carrera %s por el usuario %s', $materia->clave, $form->cleaned_data['carrera'], $request->user->codigo));
					$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($materia->clave));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
		} else {
			$form = new Pato_Form_Materia_AgregarCarrera (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/agregar-carrera.html',
		                                         array ('page_title' => 'Agregar materia a una carrera',
		                                                'form' => $form,
		                                                'materia'=> $materia),
		                                         $request);
	}
	
	public $eliminarDeCarrera_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.asociar_carrera_materia'));
	public function eliminarDeCarrera ($request, $match) {
		$materia = new Pato_Materia ();
		
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$carrera = new Pato_Carrera ();
		
		if (false === ($carrera->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$assoc_carrera = $materia->get_carreras_list ();
		$found = false;
		foreach ($assoc_carrera as $as_c) {
			if ($as_c->clave == $carrera->clave) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			/* La confirmación, eliminar la asociación */
			$materia->delAssoc ($carrera);
			Gatuf_Log::info (sprintf ('La materia %s ha sido desasociada de la carrera %s por el usuario %s', $materia->clave, $carrera->clave, $request->user->codigo));
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Presentar la confirmación de eliminación */
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/eliminar-carrera.html',
		                                         array ('page_title' => 'Desasociar materia de una carrera',
		                                                'materia' => $materia,
		                                                'carrera' => $carrera),
		                                         $request);
	}
	
	public $agregarMateria_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_materia'));
	public function agregarMateria ($request, $match) {
		$extra = array ();
		$extra['user'] = $request->user;
		if ($request->method == 'POST') {
			$form = new Pato_Form_Materia_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$materia = $form->save ();
				
				Gatuf_Log::info (sprintf ('La materia %s ha sido creada por el usuario %s', $materia->clave, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Materia_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/agregar-materia.html',
		                                         array ('page_title' => 'Nueva materia',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $actualizarMateria_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_materia'));
	public function actualizarMateria ($request, $match) {
		$materia = new Pato_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/*if (!$request->user->hasPerm ('SIIAU.jefe.'.$materia->departamento)) {
			$request->user->setMessage (3, 'No puede actualizar esta materia, usted no es el Jefe de ese Departamento');
			$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Materia::verMateria', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}*/
		
		/* Verificar que la materia esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Materia::actualizarMateria', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('materia' => $materia, 'user' => $request->user);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Materia_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$materia = $form->save ();
				
				Gatuf_Log::info (sprintf ('La materia %s ha sido actualizada por el usuario %s', $materia->clave, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Materia_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/edit-materia.html',
		                                         array ('page_title' => (string) $materia,
		                                                'materia' => $materia,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $verEvals_precond = array ('Gatuf_Precondition::loginRequired');
	public function verEvals ($request, $match) {
		$materia = new Pato_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$evals = Gatuf::factory ('Pato_Evaluacion')->getList ();
		$eval_t = Gatuf::factory ('Pato_Evaluacion')->getSqlTable ();
		$p_model = new Pato_Porcentaje ();
		$p_model->_a['views']['join_eval'] = array ('join' => 'LEFT JOIN '.$eval_t.' ON evaluacion='.$eval_t.'.id');
		
		$porcentajes = array ();
		foreach (Gatuf::factory ('Pato_GPE')->getList () as $gpe) {
			$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array ($materia->clave, $gpe->id));
			$porcentajes[$gpe->id] = $p_model->getList (array ('view' => 'join_eval', 'filter' => $sql->gen ()));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/ver-evals.html',
		                                         array ('page_title' => (string) $materia,
		                                                'materia' => $materia,
		                                                'evals' => $evals,
		                                                'porcentajes' => $porcentajes),
		                                         $request);
	}
	
	public $agregarEval_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_materia_evals'));
	public function agregarEval ($request, $match) {
		$materia = new Pato_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Verificar que el grupo de evaluación exista */
		$grupo_eval = new Pato_GPE ();
		
		if (false === ($grupo_eval->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Verificar que la materia esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Materia::agregarEval', array ($nueva_clave, $match[2]));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$disponibles = $materia->getNotEvals ($grupo_eval->id, true);
		
		if ($disponibles == 0) {
			$request->user->setMessage (3, 'No hay formas de evaluación para agregar a esta materia en la modalidad '.$grupo_eval->descripcion);
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verEvals', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('gp' => $grupo_eval->id, 'materia' => $materia);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Materia_AgregarEval ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				Gatuf_Log::info (sprintf ('Se agrego la forma de evaluacion %s a la materia %s por el usuario %s', $form->cleaned_data['evaluacion'], $materia->clave, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verEvals', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Materia_AgregarEval (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/agregar-eval.html',
		                                         array ('page_title' => (string) $materia,
		                                                'materia' => $materia,
		                                                'gpe' => $grupo_eval,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarEval_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_materia_evals'));
	public function eliminarEval ($request, $match) {
		$materia = new Pato_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$eval = new Pato_Evaluacion ();
		if (false === ($eval->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND evaluacion=%s', array ($materia->clave, $eval->id));
		$porcentaje = Gatuf::factory ('Pato_Porcentaje')->getOne ($sql->gen ());
		
		if ($porcentaje === null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			/* Si el formulario viene de regreso, eliminar el porcentaje y todas las calificaciones en boleta */
			$boleta_t = Gatuf::factory ('Pato_Boleta')->getSqlTable ();
			$seccion_t = Gatuf::factory ('Pato_Seccion')->getSqlTable ();
			
			$query = sprintf ('DELETE B FROM %s AS B, %s AS S WHERE B.nrc = S.nrc AND S.materia="%s" AND B.evaluacion=%s', $boleta_t, $seccion_t, $porcentaje->materia, $porcentaje->evaluacion);
			
			$db = Gatuf::db ();
			$db->execute ($query);
			
			$porcentaje->delete ();
			
			Gatuf_Log::info (sprintf ('La forma de evaluacion %s ha sido eliminada de la materia %s por el usuario %s', $eval->id, $materia->clave, $request->user->codigo));
			
			$request->user->setMessage (1, 'La forma de evaluación "'.$eval->descripcion.'" ha sido eliminada de la materia');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verEvals', $materia->clave);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/eliminar-eval.html',
		                                         array ('page_title' => (string) $materia,
		                                                'materia' => $materia,
		                                                'eval' => $eval,
		                                                'porcentaje' => $porcentaje),
		                                         $request);
	}
	
	public $editarEval_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_materia_evals'));
	public function editarEval ($request, $match) {
		$materia = new Pato_Materia ();
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$eval = new Pato_Evaluacion ();
		if (false === ($eval->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND evaluacion=%s', array ($materia->clave, $eval->id));
		$porcentaje = Gatuf::factory ('Pato_Porcentaje')->getOne ($sql->gen ());
		
		if ($porcentaje === null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('porcentaje' => $porcentaje);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Materia_EditarEval ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$request->user->setMessage (1, 'Se ha ajustado la forma de evaluación');
				Gatuf_Log::info (sprintf ('Se ha ajustado la forma de evaluacion %s de la materia %s, por el usuario %s', $eval->id, $materia->clave, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verEvals', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Materia_EditarEval (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/materia/editar-eval.html',
		                                         array ('page_title' => (string) $materia,
		                                                'materia' => $materia,
		                                                'evaluacion' => $eval,
		                                                'form' => $form),
		                                         $request);
	}
}
