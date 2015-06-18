<?php

class Admision_PDF_Admision extends External_FPDF {
	private function addHeader () {
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->Image (dirname(__FILE__).'/../../Pato/PDF/data/logo-1.jpg', 18, 8, 26, 30);
		
		$this->SetFont('Times', '', 12);
		$this->SetY (20);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Formato  de  Admisión', 0, 0, 'C');
	}
	
	public function renderAspirante ($aspirante) {
		$this->addHeader ();
		setlocale (LC_TIME, 'es_MX');
		
		/* Recuperar la foto del aspirante para poner */
		
		if ($aspirante->foto === '') {
			$foto = 'http://www.gravatar.com/avatar/.jpg?d=mm&s=128';
		} else {
			$foto = Gatuf::config ('admision_data_upload').'/'.$aspirante->foto;
		}
		
		$this->Image ($foto, 18, 46, 26, 26, ($aspirante->foto == '' ? 'jpg' : ''));
		
		$this->SetFont('Times', '', 12);
		$this->SetY (54);
		$this->SetX (48);
		$this->Cell (250, 8, $aspirante->apellido.' '.$aspirante->nombre, 0, 0);
		
		$this->SetY (74);
		$this->SetX (18);
		$carrera = $aspirante->get_aspiracion ()->get_carrera ();
		$this->Cell (155, 4, 'Programa educativo solicitado: '.$carrera->descripcion, 0, 0);
		
		$this->SetX (175);
		$texto = ($aspirante->turno == 'M' ? 'Matutino' : ($aspirante->turno == 'V' ? 'Vespertino' : ''));
		$this->Cell (40, 4, 'Turno: '.$texto, 0, 0);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (84);
		$this->SetX (18);
		$this->Cell (100, 4, 'Datos generales', 0, 0);
		
		$this->SetFont('Times', '', 12);
		$this->SetY (90);
		$this->SetX (18);
		$lugar = $aspirante->lugar_nacimiento.', ';
		if ($aspirante->estado_nacimiento) {
			$lugar .= $aspirante->get_estado_nacimiento ()->nombre.', ';
		}
		$lugar .= $aspirante->get_pais_nacimiento()->nombre;
		
		$this->Cell (110, 4, 'Lugar de nacimiento: '.$lugar, 0, 0);
		
		$this->SetY (90);
		$this->SetX (130);
		
		$fecha = strftime('%e de %B de %Y', strtotime(date('Y-m-d H:i:s', strtotime($aspirante->nacimiento))));
		$this->Cell (100, 4, 'Fecha de nacimiento: '.$fecha, 0, 0);
		
		$this->SetY (96);
		$this->SetX (18);
		$this->Cell (120, 4, 'País de la nacionalidad: '.$aspirante->get_nacionalidad()->nombre, 0, 0);
		
		$this->SetY (102);
		$this->SetX (18);
		if ($aspirante->sexo == 'F') {
			$this->Cell (100, 4, 'Sexo: Femenino', 0, 0);
		} else {
			$this->Cell (100, 4, 'Sexo: Masculino', 0, 0);
		}
		
		$this->SetX (60);
		$this->Cell (100, 4, 'Estado civil: '.$aspirante->display_estado_civil (), 0, 0);
		
		$this->SetX (130);
		$this->Cell (100, 4, 'Curp: '.$aspirante->curp, 0, 0);
		
		$this->SetY (108);
		$this->SetX (18);
		$this->Cell (200, 4, 'Domicilio: '.$aspirante->domicilio, 0, 0);
		$this->SetY (114);
		$this->SetX (37.5);
		$this->Cell (200, 4, $aspirante->get_colonia()->display_full(), 0, 0);
		
		$this->SetY (120);
		$this->SetX (18);
		$tels = array ();
		if ($aspirante->numero_local != '') {
			$tels[] = 'Local '.$aspirante->numero_local;
		}
		
		if ($aspirante->numero_celular != '') {
			$tels[] = 'Celular '.$aspirante->numero_celular;
		}
		
		$this->Cell (100, 4, 'Teléfono (s): '.implode (', ', $tels), 0, 0);
		
		$this->SetY (126);
		$this->SetX (18);
		$this->Cell (100, 4, 'Correo electrónico: '.$aspirante->email, 0, 0);
		
		$this->SetY (132);
		$this->SetX (18);
		$this->Cell (110, 4, 'Discapacidad: '.$aspirante->discapacidad, 0, 0);
		
		$this->SetX (130);
		$texto = ($aspirante->trabaja ? 'Sí' : 'No');
		$this->Cell (100, 4, 'Trabaja: '.$texto, 0, 0);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (140);
		$this->SetX (18);
		$this->Cell (100, 4, 'Estudios Previos', 0, 0);
		
		$this->SetFont('Times', '', 12);
		$this->SetY (146);
		$this->SetX (18);
		$this->Cell (180, 4, 'Escuela: '.$aspirante->escuela, 0, 0);
		
		$this->SetY (152);
		$this->SetX (18);
		$n = 'Tipo de escuela y bachillerato: ';
		if ($aspirante->escuela_tipo == 1) {
			$n .= 'Pública, ';
		} else {
			$n .= 'Particular, ';
		}
		
		if ($aspirante->tipo_prepa == 1) {
			$n .= 'Bachillerato general';
		} else if ($aspirante->tipo_prepa == 2) {
			$n .= 'Bachillerato técnico';
		} else {
			$n .= 'Sistema abierto';
		}
		$this->Cell (100, 4, $n, 0, 0);
		
		$this->SetY (158);
		$this->SetX (18);
		$this->Cell (100, 4, 'Promedio: '.number_format ($aspirante->promedio, 2), 0, 0);
		
		$this->SetX (78);
		$this->Cell (100, 4, 'Año de egreso: '.$aspirante->egreso, 0, 0);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (166);
		$this->SetX (18);
		$this->Cell (100, 4, 'Contacto de emergencia', 0, 0);
		
		$this->SetFont('Times', '', 12);
		$this->SetY (172);
		$this->SetX (18);
		$this->Cell (200, 4, 'Nombre: '.$aspirante->emergencia_nombre, 0, 0);
		
		$this->SetY (178);
		$this->SetX (18);
		$tels = array ();
		if ($aspirante->emergencia_local != '') {
			$tels[] = 'Local '.$aspirante->emergencia_local;
		}
		
		if ($aspirante->emergencia_celular != '') {
			$tels[] = 'Celular '.$aspirante->emergencia_celular;
		}
		
		$this->Cell (100, 4, 'Teléfono (s): '.implode (', ', $tels), 0, 0);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (185);
		$this->SetX (18);
		$this->Cell (100, 4, 'Cláusula de veracidad de datos', 0, 0);
		
		$this->SetFont('Times', '', 9);
		$this->SetY (190);
		$this->SetX (18);
		$this->MultiCell (180, 4, 'Manifiesto bajo protesta, que los datos asentados en la presente solicitud de admisión son verídicos. Y que en  caso de haberme conducido con falsedad en los datos asentados en mi solicitud, acepto hacerme acreedor a cualquiera de las sanciones administrativas correspondientes, incluyendo la negativa de ingreso a la Universidad.');
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (205);
		$this->SetX (18);
		$this->Cell (100, 4, 'Aviso de confidencialidad', 0, 0);
		
		$this->SetFont('Times', '', 9);
		$this->SetY (210);
		$this->SetX (18);
		$this->MultiCell (180, 4, 'He leído y conozco el Aviso de Confidencialidad, de la Universidad Politécnica de la Zona Metropolitana de Guadalajara. Por lo que manifiesto mi consentimiento para que la Universidad realice el tratamiento y transferencia de mis datos personales que será necesario para que se cumplan con las finalidades y atribuciones de la misma.');
		
		$this->SetFont('Times', '', 9);
		$this->SetY (224);
		$this->SetX (18);
		$this->MultiCell (180, 4, 'Autorizo que la documentación entregada a la Universidad Politécnica de la Zona Metropolitana de Guadalajara para el proceso de Admisión. Sea destruida en el caso de no ser admitido en el periodo en trámite.');
		
		$this->SetFont('Times', '', 12);
		$this->SetY (246);
		$this->SetX (18);
		$this->Cell (180, 4, '______________________', 0, 0, 'C');
		
		$this->SetY (252);
		$this->SetX (18);
		$this->Cell (180, 4, $aspirante->nombre.' '.$aspirante->apellido, 0, 0, 'C');
		
		$fecha = strftime ('Tlajomulco de Zúñiga, Jalisco a %e de %B de %Y', strtotime(date('Y-m-d H:i:s', strtotime($aspirante->print_time))));
		$this->SetY (256);
		$this->SetX (18);
		$this->Cell (180, 4, $fecha, 0, 0, 'C');
		
		$this->SetFont('Times', '', 8);
		
		$this->SetY (264);
		$this->SetX (14);
		$this->Cell (60, 6, 'F-SE-01-01', 0, 0, 'L');
		
		$this->SetX (95);
		$this->Cell (60, 6, 'Revisión: 4');
		
		$this->SetX (146);
		$this->Cell (50, 6, 'Fecha: 1 de mayo de 2015', 0, 0, 'R');
	}
}
