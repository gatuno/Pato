<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Utils {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/index.html',
		                                         array('page_title' => 'Utilerias varias'),
                                                 $request);
	}
	
	public $loteBoletas_precond = array ('Gatuf_Precondition::adminRequired');
	public function loteBoletas ($request, $match) {
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/lote-boletas.html',
		                                         array('page_title' => 'Imprimir boletas en lote',
                                                       'carreras' => $carreras),
                                                 $request);
	}
	
	public $loteBoletaCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function loteBoletaCarrera ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if (false === $carrera->get ($match[1])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Alumno_Boleta ('P', 'mm', 'Letter');
		
		$inscripciones = $carrera->get_pato_inscripcion_list (array ('filter' => 'egreso IS NULL'));
		
		foreach ($inscripciones as $ins) {
			$estatus = $ins->get_estatus ();
			
			if (!$estatus->activo) continue;
			
			$alumno = $ins->get_alumno ();
			
			$pdf->renderBoleta ($alumno);
		}
		
		$nombre = 'boletas_'.$carrera->clave.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
}
