<?php

class CP_Views {
	public function buscarCPJSON ($request, $match) {
		if (!isset ($request->GET['codigo']) && !isset ($request->GET['id'])) {
			return new Gatuf_HTTP_Response_Json (array ());
		}
		
		if (isset ($request->GET['codigo'])) {
			$sql = new Gatuf_SQL ('codigo = %s', $request->GET['codigo']);
		} else if (isset ($request->GET['id'])) {
			$sql = new Gatuf_SQL ('id = %s', $request->GET['id']);
		}
		$codigos = Gatuf::factory ('CP_CP')->getList (array ('filter' => $sql->gen ()));
		
		$response = array ();
		foreach ($codigos as $cp) {
			$o = new stdClass();
			$o->id = $cp->id;
			$o->codigo = $cp->codigo;
			$o->localidad = $cp->localidad;
			
			$as = new stdClass ();
			$as_obj = $cp->get_asentamiento();
			$as->id = $as_obj->id;
			$as->nombre = $as_obj->nombre;
			$o->asentamiento = $as;
			
			$mun = new stdClass ();
			$mun_obj = $cp->get_municipio ();
			$mun->id = $mun_obj->id;
			$mun->numero = $mun_obj->numero;
			$mun->nombre = $mun_obj->nombre;
			$o->municipio = $mun;
			
			$est = new stdClass ();
			$est_obj = $mun_obj->get_estado ();
			$est->id = $est_obj->id;
			$est->nombre = $est_obj->nombre;
			$est->A2 = $est_obj->A2;
			$est->A3 = $est_obj->A3;
			$o->estado = $est;
			
			$response[] = $o;
		}
		
		return new Gatuf_HTTP_Response_Json ($response);
	}
}
