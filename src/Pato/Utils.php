<?php
function Pato_Utils_buscarSalonVacio ($semana, $bus_inicio, $bus_fin, $edificios = array ()) {
	/* FIXME: Optimizar este código */
	if (count ($edificios) != 0) {
		$sql = new Gatuf_SQL ('edificio IN ('.implode (',', array_fill (0, count ($edificios), '%s')).')', $edificios);
		$where = $sql->gen ();
	} else {
		$where = null;
	}
	$salones = Gatuf::factory('Pato_Salon')->getList (array ('order' => array ('edificio ASC', 'aula ASC'), 'filter' => $where));
	
	if (count ($salones) == 0)  return array ();
	
	$libres = array ();
	foreach ($salones as $salon) {
		$libres[$salon->id] = $salon;
	}
	
	$horario_model = new Pato_Horario ();
	$horario_model->inicio = $bus_inicio;
	$horario_model->fin = $bus_fin;
	
	foreach ($semana as $dia) {
		$horario_model->$dia = true;
		$horarios_en_dia = Gatuf::factory ('Pato_Horario')->getList (array ('filter' => sprintf ('%s=1', $dia)));
	
		foreach ($horarios_en_dia as $hora) {
			if (Pato_Horario::chocan ($horario_model, $hora)) {
				/* Choque, este salon está ocupado a la hora solicitada */
				if (isset ($libres[$hora->salon])) unset ($libres[$hora->salon]);
			}
		}
		$horario_model->$dia = false;
	}
	
	return $libres;
}

function Pato_Utils_numeroLetra($numero, $decimales = 1) {
	$flotante = number_format($numero,$decimales);
	
	$decenas_letra = array (3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA', 6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA', 10 => 'CIEN');
	$unidades_letra = array(0 => '', 1 => 'Y UNO', 2 => 'Y DOS', 3 => 'Y TRES', 4 => 'Y CUATRO', 5 => 'Y CINCO', 6 => 'Y SEIS', 7 => 'Y SIETE', 8 => 'Y OCHO', 9 => 'Y NUEVE');
	$especiales_letra = array(0 => 'CERO', 1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO', 5 => 'CINCO', 6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE', 10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 16 => 'DIECISEIS', 17 =>'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE', 20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDOS', 23 => 'VEINTITRES', 24 => 'VEINTICUATRO', 25 => 'VEINTICINCO', 26 => 'VEINTISEIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE');
	
	$explote = explode (".", $flotante);
	$parte_entera = (int) $explote[0];
	$parte_flotante = (int) $explote[1];
	
	if ($parte_entera < 30) {
		$cadena_entero = $especiales_letra[$parte_entera];
	} else {
		$unidad = $parte_entera % 10;
		$decena = ($parte_entera - $unidad) / 10;
		$cadena_entero = trim ($decenas_letra[$decena].' '.$unidades_letra[$unidad]);
	}
	
	$cadena_flotante = '';
	if ($parte_flotante != 0) {
		if ($parte_flotante < 30) {
			$cadena_flotante = 'PUNTO '.$especiales_letra[$parte_flotante];
		} else {
			$unidad = $parte_flotante % 10;
			$decena = ($parte_flotante - $unidad) / 10;
			$cadena_flotante = trim ('PUNTO '.$decenas_letra[$decena].' '.$unidades_letra[$unidad]);
		}
	}
	
	return trim ($cadena_entero.' '.$cadena_flotante);
}
