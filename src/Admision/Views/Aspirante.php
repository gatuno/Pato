<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Admision_Views_Aspirante {
	public function convocatoria ($request, $match) {
		/* Revisar que exista al menos un convocatoria abierta */
		$abierta = false;
		foreach (Gatuf::factory ('Admision_Convocatoria')->getList () as $convocatoria) {
			$hora = gmdate ('Y/m/d H:i');
			$unix_time = strtotime ($hora);
		
			$unix_inicio = strtotime ($convocatoria->apertura);
			$unix_fin = strtotime ($convocatoria->cierre);
		
			if ($unix_time >= $unix_inicio && $unix_time <= $unix_fin) {
				/* La convocatoria está abierta */
				$cupos = $convocatoria->get_admision_cupocarrera_list (array ('count' => true));
				if ($cupos > 0) $abierta = true;
			}
		}
		
		if (!$abierta) {
			return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/convocatoria-cerrada.html',
		                                         array('page_title' => 'Todas las convocatorias cerradas'),
                                                 $request);
		}
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_SeleccionarConvocatoria ($request->POST);
			
			if ($form->isValid ()) {
				$convocatoria = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::registro', $convocatoria->id);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Aspirante_SeleccionarConvocatoria (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/convocatoria.html',
		                                         array('page_title' => 'Seleccionar convocatoria',
		                                               'form' => $form),
                                                 $request);
	}
	
	public function registro ($request, $match) {
		$convocatoria = new Admision_Convocatoria ();
		
		if (false === ($convocatoria->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Revisar que esta convocatoria esté abierta */
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$unix_inicio = strtotime ($convocatoria->apertura);
		$unix_fin = strtotime ($convocatoria->cierre);
		
		if ($unix_time < $unix_inicio || $unix_time > $unix_fin) {
			/* La convocatoria está cerrada */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('convocatoria' => $convocatoria);
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_Registro ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$aspirante = $form->save (false);
				
				/* Enviar el correo */
				$tmpl = new Gatuf_Template('admision/aspirante/primer-registro.txt');
				$context = new Gatuf_Template_Context (
				               array ('numero' => $aspirante->id,
				                      'pass' => $aspirante->token));
				$email = new Gatuf_Mail (Gatuf::config ('from_email'), $aspirante->email, 'Bienvenido Aspirante - Continua tu trámite');
				$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
				$email->addTextMessage ($tmpl->render ($context));
				$email->sendMail ();
				
				$return_url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::postRegistro');
				
				return new Gatuf_HTTP_Response_Redirect ($return_url);
			}
		} else {
			$form = new Admision_Form_Aspirante_Registro (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/registro.html',
		                                         array('page_title' => 'Nuevo registro',
		                                               'convocatoria' => $convocatoria,
		                                               'form' => $form),
                                                 $request);
	}
	
	public function postRegistro ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/postregistro.html',
		                                         array('page_title' => 'Registro completo'),
                                                 $request);
	}
	
	public $index_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function index ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$aspirante = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::editar', $aspirante->id);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Aspirante_Seleccionar (null);
		}
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/index.html',
		                                         array ('page_title' => 'Seleccionar aspirante',
		                                                'form' => $form),
                                                 $request);
	}
	
	public $buscarJSON_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function buscarJSON ($request, $match) {
		if (!isset ($request->GET['term'])) {
			return new Gatuf_HTTP_Response_Json (array ());
		}
		
		$bus = '%'.$request->GET['term'].'%';
		
		$sql = new Gatuf_SQL ('nombre LIKE %s OR apellido LIKE %s OR id LIKE %s', array ($bus, $bus, $bus));
		$aspirantes = Gatuf::factory ('Admision_Aspirante')->getList (array ('filter' => $sql->gen ()));
		
		$response = array ();
		foreach ($aspirantes as $aspirante) {
			$o = new stdClass();
			$o->value = (string) $aspirante->id;
			$o->label = (string) $aspirante;
			
			$response[] = $o;
		}
		
		return new Gatuf_HTTP_Response_Json ($response);
	}
	
	public function continuar ($request, $match) {
		$logged = $request->session->getData ('aspirante_id', null);
		
		if ($logged !== null) {
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::dashboard');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_Login ($request->POST);
			
			if ($form->isValid ()) {
				$aspirante = $form->save ();
				
				$request->session->setData ('aspirante_id', $aspirante->id);
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::dashboard');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Aspirante_Login ($request->POST);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/login.html',
		                                         array ('page_title' => 'Continua tu trámite',
		                                                'form' => $form),
		                                         $request);
	}
	
	public function cerrar ($request, $match) {
		$request->session->setdata ('aspirante_id');
		
		$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::continuar');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function dashboard ($request, $match) {
		$logged = $request->session->getData ('aspirante_id', null);
		
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($logged))) {
			$request->session->setdata ('aspirante_id');
			
			throw new Gatuf_HTTP_Error404 ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/ver.html',
		                                         array ('page_title' => 'Aspirante',
		                                                'aspirante' => $aspirante),
		                                         $request);
	}
	
	public function dashboardFotoMiniatura ($request, $match) {
		$logged = $request->session->getData ('aspirante_id', null);
		
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($logged))) {
			$request->session->setdata ('aspirante_id');
			
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$miniaturas_dir = Gatuf::config ('admision_data_upload').'/thumbnails';
		if (!is_dir($miniaturas_dir)) {
			if (false == @mkdir($miniaturas_dir, 0777, true)) {
		        throw new Gatuf_Form_Invalid('An error occured when creating the thumbnails folder path.');
		    }
		}
		
		$thumbnail = new Gatuf_Image_Thumbnail ($miniaturas_dir, Gatuf::config ('admision_data_upload').'/'.$aspirante->foto);
		$thumbnail->size = array (128, 128);
		
		if (!$thumbnail->exists()) {
			$thumbnail_filename = $thumbnail->generate();
		} else {
			$thumbnail_filename = $thumbnail->getPath();
		}
		
		$name = $thumbnail->getName ();
		$info = Gatuf_FileUtil::getMimeType ($thumbnail_filename);
		
		return new Gatuf_HTTP_Response_File ($thumbnail_filename, $name, $info[0], false);
	}
	
	public $editar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function editar ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/editar.html',
		                                         array ('page_title' => 'Aspirante',
		                                                'aspirante' => $aspirante),
		                                         $request);
	}
	
	public $subirFoto_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function subirFoto ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('aspirante' => $aspirante);
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_SubirFoto (array_merge ($request->POST, $request->FILES), $extra);
			
			if ($form->isValid ()) {
				$aspirante = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::editar', $aspirante->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Aspirante_SubirFoto (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/subir-foto.html',
		                                         array ('page_title' => 'Subir foto',
		                                                'form' => $form,
		                                                'aspirante' => $aspirante),
		                                         $request);
	}
	
	public $verFotoMiniatura_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function verFotoMiniatura ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$miniaturas_dir = Gatuf::config ('admision_data_upload').'/thumbnails';
		if (!is_dir($miniaturas_dir)) {
			if (false == @mkdir($miniaturas_dir, 0777, true)) {
		        throw new Gatuf_Form_Invalid('An error occured when creating the thumbnails folder path.');
		    }
		}
		
		$thumbnail = new Gatuf_Image_Thumbnail ($miniaturas_dir, Gatuf::config ('admision_data_upload').'/'.$aspirante->foto);
		$thumbnail->size = array (128, 128);
		
		if (!$thumbnail->exists()) {
			$thumbnail_filename = $thumbnail->generate();
		} else {
			$thumbnail_filename = $thumbnail->getPath();
		}
		
		$name = $thumbnail->getName ();
		$info = Gatuf_FileUtil::getMimeType ($thumbnail_filename);
		
		return new Gatuf_HTTP_Response_File ($thumbnail_filename, $name, $info[0], false);
	}
	
	public $registrarPago_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function registrarPago ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($aspirante->pago !== null) {
			$request->user->setMessage (3, 'Este aspirante ya tiene su pago registrado');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::editar', $aspirante->id);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_SeleccionarFechaHora ($request->POST);
			
			if ($form->isValid ()) {
				$fecha = $form->save ();
				
				$aspirante->pago = $fecha;
				$aspirante->update ();
				
				$request->user->setMessage (1, 'Pago del aspirante registrado');
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::editar', $aspirante->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_SeleccionarFechaHora (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/registrar-pago.html',
		                                         array ('page_title' => 'Registrar la fecha de pago',
		                                                'form' => $form,
		                                                'aspirante' => $aspirante),
		                                         $request);
	}
	
	public $imprimir_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function imprimir ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Marcar la hora de impresión ahora */
		if ($aspirante->print_time === null) {
			/* Enviar el correo del ceneval */
			$tmpl = new Gatuf_Template('admision/aspirante/correo-ceneval.txt');
			$context = new Gatuf_Template_Context (
				           array ('numero' => $aspirante->id,
				                  'nombre' => $aspirante->nombre,
				                  'apellido' => $aspirante->apellido));
			$email = new Gatuf_Mail (Gatuf::config ('from_email'), $aspirante->email, 'Continua tu trámite - Registro para el examen CENEVAL');
			$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
			$email->addTextMessage ($tmpl->render ($context));
			$email->sendMail ();
		
			$aspirante->print_time = date ('Y-m-d H:i:s');
			$aspirante->update ();
		}
		
		$pdf = new Admision_PDF_Admision ('P', 'mm', 'Letter');
		
		$pdf->renderAspirante ($aspirante);
		
		$pdf->Close ();
		
		$nombre = 'admision_'.$aspirante->id.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $actualizar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admin_aspirantes'));
	public function actualizar ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('aspirante' => $aspirante);
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$request->user->setMessage (1, 'Datos actualizados');
				Gatuf_Log::info (sprintf ('El usuario %s actualizó la información del aspirante %s', $request->user->codigo, $aspirante->id));
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::editar', $aspirante->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Aspirante_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/actualizar.html',
		                                         array ('page_title' => 'Aspirante '.$aspirante->id,
		                                                'aspirante' => $aspirante,
		                                                'convocatoria' => $aspirante->get_aspiracion()->get_convocatoria(),
		                                                'form' => $form),
		                                         $request);
	}
}
