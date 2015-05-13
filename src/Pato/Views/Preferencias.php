<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Preferencias {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/index.html',
		                                         array ('page_title' => 'Preferencias'),
		                                         $request);
	}
	
	public $cambiaFolio_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarFolio ($request, $match) {
		$folio = $request->session->getData ('numero_folio', 1);
		
		$usados = $request->session->getData ('folios_usados', array ());
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_EstablecerFolio ($request->POST, array ('numero' => $folio));
			
			if ($form->isValid ()) {
				$folio = $form->save ();
				
				$request->session->setData ('numero_folio', $folio);
				$request->user->setMessage (1, 'Número de folio cambiado a: '.$folio);
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Preferencias::cambiarFolio');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_EstablecerFolio (null, array ('numero' => $folio));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/folio.html',
		                                         array ('page_title' => 'Cambiar número de folio',
		                                                'form' => $form,
		                                                'folio' => $folio,
		                                                'usados' => $usados),
		                                         $request);
	}
	
	public $eliminarFolio_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarFolio ($request, $match) {
		$usados = $request->session->getData ('folios_usados', array ());
		
		$eliminar = (int) $match[1];
		
		if (isset ($usados[$eliminar])) {
			unset ($usados[$eliminar]);
			
			$request->session->setData ('folios_usados', $usados);
			$request->user->setMessage (1, 'El folio '.$eliminar.' fué liberado');
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Preferencias::cambiarFolio');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $subirFolios_precond = array ('Gatuf_Precondition::adminRequired');
	public function subirFolios ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SubirFolios (array_merge ($request->POST, $request->FILES));
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Preferencias::cambiarFolio');
				
				/* Intentar abrir el archivo */
				if (($archivo = fopen ($data['archivo'], "r")) === false) {
					$request->user->setMessage (3, 'Hubo un error con la subida de archivos');
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
				
				/* Leer la cabecera e intentar "adivinar" las columnas */
				$linea = fgetcsv ($archivo, 600, ',', '"');

				if ($linea === false || is_null ($linea)) {
					$request->user->setMessage (3, 'No hay cabecera, o es una linea vacia');
					return new Gatuf_HTTP_Response_Redirect ($url);
				}

				$indexes = array ();

				foreach ($linea as $index => $columna) {
					$col = strtolower ($columna);

					if (!isset ($indexes[$col])) {
						$indexes[$col] = $index;
					}
				}
				
				if (!isset ($indexes ['folio']) || !isset ($indexes['nrc'])) {
					$request->user->setMessage (3, 'El archivo importado debe contener una columna llamada "NRC" y otra llamada "FOLIO". No importan las mayúsculas o minúsculas.');
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
				$usados = $request->session->getData ('folios_usados', array ());

				$calendario = new Pato_Calendario ($data['calendario']);
				$total = 0;
				/* Recorrer el archivo y hacer la asociación */
				while (($linea = fgetcsv ($archivo, 600, ",", "\"")) !== FALSE) {
					if (is_null ($linea[0])) continue;
					$folio = (int) $linea[$indexes['folio']];
					$nrc = (int) $linea[$indexes['nrc']];
					
					$usados[$folio] = 'NRC '.$nrc.' '.$calendario->clave;
					$total++;
				}
				
				$request->session->setData ('folios_usados', $usados);
				
				fclose ($archivo);
				
				$request->user->setMessage (1, $total.' folios fueron importados');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SubirFolios (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/subir-folios.html',
		                                         array ('page_title' => 'Subir folios',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $cambiarFecha_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarFecha ($request, $match) {
		$fecha = $request->session->getData ('fecha', date ('d/m/Y'));
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarFecha ($request->POST, array ('fecha' => $fecha));
			
			if ($form->isValid ()) {
				$fecha = $form->save ();
				
				$request->session->setData ('fecha', $fecha);
				$request->user->setMessage (1, 'Fecha falsificada a: '.$fecha);
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Preferencias::cambiarFecha');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarFecha (null, array ('fecha' => $fecha));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/fecha.html',
		                                         array ('page_title' => 'Falsificar un fecha',
		                                                'form' => $form,
		                                                'fecha' => $fecha,),
		                                         $request);
	}
	
	public $terminosSuficiencias_precond = array ('Gatuf_Precondition::adminRequired');
	public function terminosSuficiencias ($request, $match) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		$terms = $gconf->getVal ('terminos_suficiencias', '<p>Texto pendiente</p>');
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Preferencias_Terminos ($request->POST, array ('terms' => $terms));
			
			if ($form->isValid ()) {
				$terms = $form->save ();
				
				$gconf->setVal ('terminos_suficiencias', $terms);
				
				$request->user->setMessage (1, 'Texto cambiado');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Preferencias::terminosSuficiencias');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Preferencias_Terminos (null, array ('terms' => $terms));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/terminos_suficiencias.html',
		                                         array ('page_title' => 'Texto para los términos de las suficiencias',
		                                                'form' => $form),
		                                         $request);
	}
}
