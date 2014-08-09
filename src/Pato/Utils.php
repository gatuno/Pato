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
