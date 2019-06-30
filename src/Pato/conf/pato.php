<?php
$cfg = array();

$cfg['debug'] = true;

$cfg['admins'] = array(
	array('Gatuno Admin', 'dinformatica@upzmg.edu.mx'),
);

# Llave de instalación,
# Debe ser única para esta instalación y lo suficientemente larga (40 caracteres)
# Puedes generar una llave con:
#	$ dd if=/dev/urandom bs=1 count=64 2>/dev/null | base64 -w 0
$cfg['secret_key'] = 'Qj2OnzPaKaJULrzLVrdyVwZANTaK8WPFGusvMSzpGqW+hruY90hyJ';

# ---------------------------------------------------------------------------- #
#                                   Rutas                                      #
# ---------------------------------------------------------------------------- #

# Carpeta temporal donde la aplicación puede crear plantillas complicadas,
# datos en caché y otros recursos temporales.
# Debe ser escribible por el servidor web.
$cfg['tmp_folder'] = '/tmp';

# Subida de archivos para admision
$cfg['admision_data_upload'] = 'admision_user_data_upload';

# Ruta a la carpeta PEAR
$cfg['pear_path'] = '/usr/share/php';

# Ruta de los templates
$cfg['template_folders'] = array(
   dirname(__FILE__).'/../templates',
   dirname(__FILE__).'/../../Admin/templates',
   dirname(__FILE__).'/../../Admision/templates',
);

# ---------------------------------------------------------------------------- #
#                                URL section                                   #
# ---------------------------------------------------------------------------- #

# Ejemplos:
# Tienes:
#   http://www.mydomain.com/myfolder/index.php
# Pon:
#   $cfg['calif_base'] = '/myfolder/index.php';
#   $cfg['url_base'] = 'http://www.mydomain.com';
#
# Tienes activado mod_rewrite:
#   http://www.mydomain.com/
# Pon:
#   $cfg['calif_base'] = '';
#   $cfg['url_base'] = 'http://www.mydomain.com';
#
$cfg['pato_base'] = '';
$cfg['url_base'] = 'http://pato.upzmg.edu.mx';
$cfg['url_media'] = 'http://pato.upzmg.edu.mx/media';


$cfg['pato_views'] = dirname(__FILE__).'/urls.php';

# ---------------------------------------------------------------------------- #
#                      Sección de internacionalización                         #
# ---------------------------------------------------------------------------- #

# La zona horaria
# La lista de zonas horarios puede ser encontrado aqui
# <http://www.php.net/manual/en/timezones.php>
$cfg['time_zone'] = 'America/Mexico_City';

# ---------------------------------------------------------------------------- #
#                             Database section                                 #
# ---------------------------------------------------------------------------- #
#
#

$cfg['db_engine'] = 'MySQL';

# El nombre de la base de datos para MySQL y PostgreSQL, y la ruta absoluta
# al archivo de la base de datos si estás usando SQLite.
$cfg['db_database'] = 'pato';

# El servidor a conectarse
$cfg['db_server'] = 'localhost';

# Información del usuario.
$cfg['db_login'] = 'sistemas';
$cfg['db_password'] = '';

# Un prefijo para todas tus tabla; esto puede ser útil si piensas correr
# multiples instalaciones en la misma base de datos.
$cfg['db_table_prefix'] = '';

# -----------------------
#        Correo
# -----------------------

$cfg['send_emails'] = true;
$cfg['mail_backend'] = 'smtp';
$cfg['mail_host'] = 'localhost';
$cfg['mail_port'] = 25;

# the sender of all the emails.
$cfg['from_email'] = 'pato@upzmg.edu.mx';

# Email address for the bounced messages.
$cfg['bounce_email'] = 'pato@upzmg.edu.mx';

# -----------------------
# Configuraciones varias
# -----------------------

$cfg['middleware_classes'] = array(
	'Pato_Middleware_Session',
	'Pato_Middleware_Calendario',
	'Pato_Middleware_Password',
	'Pato_Middleware_Aviso',
);

$cfg['gatuf_custom_user'] = 'Pato_Maestro';

/*$cfg['template_tags'] = array ('coordperm' => 'Calif_Template_CoordPerm',
	'jefeperm' => 'Calif_Template_JefePerm');*/

$cfg['installed_apps'] = array('Gatuf', 'Pato', 'CP', 'Admision');

$cfg['template_tags'] = array (
	'patomsgs' => 'Pato_Template_Tag_Messages',
	'alumnoself' => 'Pato_Template_Tag_AlumnoSelf',
	'maestroself' => 'Pato_Template_Tag_MaestroSelf',
	'anyperm' => 'Pato_Template_Tag_AnyPerm',
);

$cfg['gatuf_log_file'] = '/home/www/patricia.upzmg.edu.mx/pato.log';
Gatuf_Log::$level = Gatuf_Log::ALL;

# -------------------------
# Configuraciones de Pato
# -------------------------

$cfg['buscar-edificios'] = array ();
$cfg['blocked_passwords'] = array ('1', '12', '123', '1234', '12345', '123456', '1234567', '12345678', '123456789', '1234567890', 'qwerty', 'abc123', '123123', '1234567890', '11111', '111111', '1111', '111', '11', 'password', 'qwerty123', 'password1', 'qwertyuiop');

return $cfg;
