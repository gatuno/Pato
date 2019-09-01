<?php

class Pato_PDF_Estatus_Documentos extends External_FPDF {
	public function render ($alumno, $user) {
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 18, 8, 26, 30);
		
		$this->SetFont('Times', '', 10);
		$this->SetY (13);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Comprobante  de  recibo  de  documentos', 0, 0, 'C');
		
		$this->SetY (17);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Departamento  de  Servicios  Escolares', 0, 0, 'C');
		
		setlocale (LC_TIME, 'es_MX');
		$fecha = strftime ('%e de %B de %Y', time ());
		$this->SetY (30);
		$this->SetX (13);
		$this->Cell (191, 6, 'Fecha: '.$fecha, 0, 0, 'R');
		
		$this->SetY (40);
		$this->SetX (13);
		
		$this->MultiCell (191, 6, 'Por el siguiente comprobante, la Universidad Politécnica de la Zona Metropolitana de Guadalajara recibe los siguientes documentos en resguardo del alumno '.$alumno->nombre.' '.$alumno->apellido.':');
		
		$falsos = array ('Original y copia del acta de nacimiento',
		                 '6 fotografías tamaño infantil',
		                 '2 copias del CURP',
		                 '2 copias de una identificación oficial',
		                 '2 copias del comprobante de domicilio',
		                 'Original y copia del certificado médico de una institución oficial');
		
		$y = 56;
		foreach ($falsos as $falso) {
			$this->SetY ($y);
			$this->SetX (13);
			
			$this->Cell (191, 4, '*  '.$falso, 0, 0, 'L');
			
			$y += 4;
		}
		
		$documentos = $alumno->get_documentos_list ();
		
		foreach ($documentos as $doc) {
			$this->SetY ($y);
			$this->SetX (13);
			
			$this->Cell (191, 4, '*  '.$doc->descripcion, 0, 0, 'L');
			
			$y += 4;
		}
		
		$this->SetY (90);
		$this->SetX (13);
		$this->Cell (84, 4, '_______________________________', 0, 0, 'C');
		$this->SetX (109);
		$this->Cell (84, 4, '_______________________________', 0, 0, 'C');
		
		$this->SetY (95);
		$this->SetX (13);
		$this->Cell (84, 4, $user->extra->nombre.' '.$user->extra->apellido, 0, 0, 'C');
		
		$this->SetX (99);
		$this->Cell (84, 4, $alumno->nombre.' '.$alumno->apellido, 0, 0, 'C');
		
		$this->SetY (100);
		$this->SetX (13);
		$this->Cell (84, 4, 'Servicios Escolares', 0, 0, 'C');
		
		$this->SetX (109);
		$this->Cell (84, 4, 'Alumno', 0, 0, 'C');
		
		
		/* Ahora la parte de inscripción */
		$this->Line (0, 110, 216, 110);
		$this->SetAutoPageBreak (false);
		
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 18, 114, 26, 30);
		
		$this->SetFont('Times', '', 10);
		$this->SetY (122);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Formato  de  Inscripción', 0, 0, 'C');
		
		$this->SetY (130);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Departamento  de  Servicios  Escolares', 0, 0, 'C');
		
		setlocale (LC_TIME, 'es_MX');
		$fecha = strftime ('%e de %B de %Y', time ());
		$this->SetY (146);
		$this->SetX (13);
		$this->Cell (191, 6, 'Fecha: '.$fecha, 0, 0, 'R');
		
		/* Recuperar la foto del aspirante para poner */
		$sql = new Gatuf_SQL ('matricula = %s', $alumno->codigo);
		
		$aspis = Gatuf::factory ('Admision_Aspirante')->getList (array ('filter' => $sql->gen ()));
		if (count ($aspis) == 0) {
			$aspi = new Admision_Aspirante ();
		} else {
			$aspi = $aspis[0];
		}
		
		if ($aspi->foto === '') {
			$foto = 'http://www.gravatar.com/avatar/.jpg?d=mm&s=128';
		} else {
			$foto = Gatuf::config ('admision_data_upload').'/'.$aspi->foto;
		}
		
		$this->Image ($foto, 18, 152, 26, 26, ($aspi->foto == '' ? 'jpg' : ''));
		
		$this->SetY (152);
		$this->SetX (48);
		$this->Cell (191, 6, 'Alumno: '.$alumno->nombre.' '.$alumno->apellido);
		
		$ins = $alumno->get_current_inscripcion();
		$carrera = $ins->get_carrera ();
		
		$this->SetY (158);
		$this->SetX (48);
		$this->Cell (155, 6, 'Programa educativo: '.$carrera->descripcion, 0, 0);
		
		$this->SetY (164);
		$this->SetX (48);
		if ($ins->turno == 'M') {
			$this->Cell (155, 6, 'Turno: Matutino');
		} else if ($ins->turno == 'V') {
			$this->Cell (155, 6, 'Turno: Vespertino');
		}
		
		$this->SetY (170);
		$this->SetX (48);
		
		$this->Cell (155, 6, 'Código: '.$alumno->codigo, 0, 0);
		
		$this->SetX (110);
		$this->Cell (155, 6, 'Calendario: '.((string) $ins->get_ingreso ()), 0, 0);
		
		$this->SetY (180);
		$this->SetX (18);
		$this->Cell (200, 4, 'Domicilio: '.$aspi->domicilio, 0, 0);
		
		$this->SetY (186);
		$this->SetX (37.5);
		$this->Cell (200, 4, $aspi->get_colonia()->display_full(), 0, 0);
		
		$this->SetY (192);
		$this->SetX (18);
		$tels = array ();
		if ($aspi->numero_local != '') {
			$tels[] = 'Local '.$aspi->numero_local;
		}
		
		if ($aspi->numero_celular != '') {
			$tels[] = 'Celular '.$aspi->numero_celular;
		}
		
		$this->Cell (100, 4, 'Teléfono (s): '.implode (', ', $tels), 0, 0);
		/*
		$this->SetY (228);
		$this->SetX (18);
		$this->Cell (100, 4, 'Correo electrónico: '.$aspi->email, 0, 0);*/
		
		$this->SetY (198);
		$this->SetX (18);
		$this->Cell (100, 4, 'Tipo de sangre: '.$aspi->sanguineo_rh, 0, 0);
		
		$this->SetX (110);
		$this->Cell (100, 4, 'Curp: '.$aspi->curp, 0, 0);
		
		$this->SetY (204);
		$this->SetX (18);
		$tels = array ();
		if ($aspi->emergencia_local != '') {
			$tels[] = 'Local '.$aspi->emergencia_local;
		}
		
		if ($aspi->emergencia_celular != '') {
			$tels[] = 'Celular '.$aspi->emergencia_celular;
		}
		
		$this->Cell (150, 4, 'Teléfonos en caso de emergencia: '.implode (', ', $tels), 0, 0);
		
		$this->SetFont('Times', '', 10);
		$this->SetY (211);
		$this->SetX (13);
		$this->MultiCell (180, 4, 'Declaro bajo protesta de decir verdad que la documentación entregada a la UPZMG, para efecto de trámite de inscripción es auténtica y fidedigna, otorgando mi consentimiento expreso para que en caso de considerarlo necesario la UPZMG constate su autenticidad. Así mismo, manifiesto mi conocimiento de las implicaciones legales y sanciones administrativas en los casos en que se presente documentación falsa o alterada, sin que ello implique responsabilidad alguna para la UPZMG. A continuación se mencionan los documentos entregados: Original y copia de acta de nacimiento, 6 fotografías tamaño infantil blanco y negro, dos  copias de CURP, dos copias de identificación oficial, dos copias de comprobante de domicilio, original y copia de certificado médico de una institución oficial, original y copia del certificado de prepa.');
		
		$this->SetFont('Times', '', 10);
		$this->SetY (246);
		$this->SetX (21);
		$this->Cell (81, 4, '_______________________________', 0, 0, 'C');
		$this->SetX (112);
		$this->Cell (81, 4, '_______________________________', 0, 0, 'C');
		
		$this->SetY (256);
		$this->SetX (18);
		$this->Cell (81, 4, 'Servicios Escolares', 0, 0, 'C');
		
		$this->SetY (251);
		$this->SetX (13);
		$this->Cell (81, 4, $alumno->nombre.' '.$alumno->apellido, 0, 0, 'C');
		$this->SetY (251);
		$this->SetX (112);
		$this->Cell (81, 4, $alumno->nombre.' '.$alumno->apellido, 0, 0, 'C');
		
		$this->SetY (256);
		$this->SetX (112);
		$this->Cell (81, 4, 'Alumno', 0, 0, 'C');
		
		$this->SetY (265);
		$this->SetX (14);
		$this->Cell (60, 6, 'F-SE-01-04', 0, 0, 'L');

		$this->SetX (95);
		$this->Cell (60, 6, 'Revisión: 3');

		$this->SetX (146);
		$this->Cell (50, 6, 'Fecha: 1 de mayo 2016', 0, 0, 'R');
	}
}
