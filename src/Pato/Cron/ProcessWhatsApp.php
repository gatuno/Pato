<?php

require dirname(__FILE__).'/../conf/path.php';

# Cargar Gatuf
require 'Gatuf.php';

# Inicializar las configuraciones
Gatuf::start(dirname(__FILE__).'/../conf/pato.php');

/* Manejadores de eventos */
function onLoginFailed($mynumber, $data) {
	printf ("Ha fallado el login\n");
	/* ¿Debería enviar un correo a los administradores? */
	exit;
}

/* Cuando se recibe un mensaje */
function onGetMessage($mynumber, $from, $id, $type, $time, $name, $body) {
	printf ('Mensaje recibido de %s, Texto: %s'."\n", $from, $body);

	$texto = strtoupper (trim ($body));

	if (substr ($texto, 0, 4) === 'ALTA') {
		$subcuerpo = trim (substr ($texto, 4));
		
		$alumno = new Pato_Alumno ();
		
		$codigo = substr ($subcuerpo, 0, 8);
		printf ("Tratando de recuperar al alumno: %s\n", $codigo);
		if (false === ($alumno->get ($codigo))) {
			printf ("\tNo existe\n");
			$m = new Pato_Mensaje_WhatsApp ();
			$m->numero = substr ($from, 3, 10);;
			$m->mensaje = 'Comando incorrecto. Asegurate de escribir la palabra ALTA, seguida de un espacio, seguido de tu código a 8 dígitos (incluyendo ceros iniciales)';
			
			$m->create ();
			
			return;
		}
		
		/* Recuperar el perfil del alumno */
		$perfiles = $alumno->get_pato_perfilalumno_list();
		
		if (count ($perfiles) == 0) {
			printf ("\tNo tiene perfil\n");
			/* Aún no tiene un perfil, crearlo */
			$perfil = new Pato_PerfilAlumno ();
			
			$perfil->alumno = $alumno;
			$perfil->create ();
			
			$m = new Pato_Mensaje_WhatsApp ();
			$m->numero = substr ($from, 3, 10);;
			$m->mensaje = 'Para registrarte en el sistema de notificaciones por WhatsApp de Patricia, primero debes registrar tu número en la página de patricia.upzmg.edu.mx';
			
			$m->create ();
			
			return;
		} else {
			$perfil = $perfiles[0];
		}
		
		$num = substr ($from, 3, 10);
		printf ("Comprobando el número %s\n", $num);
		if ($perfil->whatsapp === null) {
			printf ("\tFalta la alta previa\n");
			
			$m = new Pato_Mensaje_WhatsApp ();
			$m->numero = substr ($from, 3, 10);;
			$m->mensaje = 'Para registrarte en el sistema de notificaciones por WhatsApp de Patricia, primero debes registrar tu número en la página de patricia.upzmg.edu.mx';
			
			$m->create ();
			
			return;
		}
		
		if ($perfil->whatsapp != $num) {
			printf ("\tNo coincide\n");
			$m = new Pato_Mensaje_WhatsApp ();
			$m->numero = $num;
			$m->mensaje = 'Lo sentimos, pero el código de alumno no coincide con el número de WhatsApp registrado para el mismo, así que tu alta no procede';
			
			$m->create ();
			
			return;
		}
		
		printf ("Verificado\n");
		$perfil->whatsapp_verificado = true;
		$perfil->update ();
		
		$m = new Pato_Mensaje_WhatsApp ();
		$m->numero = $num;
		$m->mensaje = 'Tu registro al sistema de notificaciones de Patricia ha sido exitoso. En cualquier momento te puedes dar de baja mandando la palabra BAJA a este mismo número.';
		
		$m->create ();
		
		return;
	} else if (substr ($texto, 0, 4) == 'BAJA') {
		/* Procesar aquí las bajas */
		$num = substr ($from, 3, 10);
		
		$sql = new Gatuf_SQL ('whatsapp=%s', $num);
		
		$perfiles = Gatuf::factory ('Pato_PerfilAlumno')->getList (array ('filter' => $sql->gen ()));
		
		foreach ($perfiles as $p) {
			$p->whatsapp = null;
			$p->whatsapp_verificado = false;
			
			$p->update ();
		}
		
		$m = new Pato_Mensaje_WhatsApp ();
		$m->numero = $num;
		$m->mensaje = 'Tu número ha sido dado de baja. No recibirás más notificaciones por parte del sistema Patricia. Si deseas volver a suscribirte, entra a patricia.upzmg.edu.mx';
		
		$m->create ();
	}
}

/* Procesar los mensajes de entrada del whatsapp y enviar los que estén en la cola */
$username = Gatuf::config ('whatsapp_login');
$pass = Gatuf::config ('whatsapp_pass');
$nick = Gatuf::config ('whatsapp_nick');

$whats = new External_WhatsApp_Protocol ($username, $nick, false);

$whats->connect ();
$whats->loginWithPassword($pass);

$events = new External_WhatsApp_events_MyEvents ($whats);
$whats->eventManager()->bind ("onLoginFailed", "onLoginFailed");
$whats->eventManager()->bind ("onGetMessage", "onGetMessage");

/* Primero procesar la cola de mensajes pendientes */
while ($whats->pollMessage ());

/* Enviar todos los mensajes en la cola, solo los primeros 100, con 5 segundos entre cada mensaje
 * todo esto para evitar ser tachado de spammer */

$mensajes = Gatuf::factory ('Pato_Mensaje_WhatsApp')->getList (array ('nb' => 100));

foreach ($mensajes as $m) {
	$whats->sendMessage ('521'.$m->numero, $m->mensaje);
	
	$m->delete ();
	// sleep (5);
}

