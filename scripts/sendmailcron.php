#!/usr/bin/php
<?php

require dirname(__FILE__).'/../src/Pato/conf/path.php';
require 'Gatuf.php';
Gatuf::start(dirname(__FILE__).'/../src/Pato/conf/pato.php');
Gatuf_Despachador::loadControllers(Gatuf::config('pato_views'));

#;*/ ::
$lock_file = Gatuf::config('pato_mailqueuecron_lock', 
                     Gatuf::config('tmp_folder', '/tmp').'/mailqueuecron.lock');
if (file_exists($lock_file)) {
    Gatuf_Log::event(array('sendmailcron.php', 'skip'));
    return;
}
file_put_contents($lock_file, time(), LOCK_EX);

/* Recoger un elemento de los correos pendientes y tratar de bloquearlo */
$max_to_send = 10; /* TODO: Sacar esto de una configuración */
$real_sent = 0;

while ($real_sent < $max_to_send) {
	$sql = new Gatuf_SQL ('estado = 1');
	$correos = Gatuf::factory ('Pato_CorreoPendiente')->getList (array ('filter' => $sql->gen (), 'nb' => 1));
	
	if (count ($correos) == 0) {
		break;
	}
	
	$correo = $correos[0];
	
	$blocked = $correo->block_for_sending ();
	
	if ($blocked == false) continue;
	
	$email = new Gatuf_Mail (Gatuf::config ('from_email'), $correo->destinatario, $correo->asunto);
	$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
	if ($correo->cuerpo_txt != '') $email->addTextMessage ($correo->cuerpo_txt);
	if ($correo->cuerpo_html != '') $email->addHtmlMessage ($correo->cuerpo_html);
	$email->sendMail ();
	
	$correo->delete ();
	$real_sent++;
}

unlink($lock_file);
