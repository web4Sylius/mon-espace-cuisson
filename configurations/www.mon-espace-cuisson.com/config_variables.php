<?php

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    
    $rootpath   = "http://localhost/mec/"; 
    $rootpath   = "/var/www/html/mec/";
    define('MAIL_DEV', 'webmaster@mon-espace-cuisson.com');
    define('MAIL_DEV1', 'webmaster@mon-espace-cuisson.com');
    define('MAIL_DEV2', 'webmaster@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE', 'pr@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE2', 'pr@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE3', 'pr@mon-espace-cuisson.com');
    define('MAIL_DIRECTION', 'pr@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'pr@mon-espace-cuisson.com');
    define('MAIL_ADV', 'pr@mon-espace-cuisson.com');
    define('MAIL_COMMERCE', 'pr@mon-espace-cuisson.com');
    define('MAIL_COMMERCE2', 'pr@mon-espace-cuisson.com');
    define('SITE_PREFIX', 'localhost');

    define('SITE_HOST', 'http://localhost/mec');
    define('SITE_URL', 'http://localhost.mon-espace-cuisson.com');
    define('BACK_URL', 'http://localhost.mon-espace-cuisson.com/admin');
    define('SITE_PATH', 'http://localhost/mec');
    define('BACK_PATH', 'http://localhost/mec//admin');

    define('BACK_HOST', 'http://localhost.mon-espace-cuisson.com/admin');
    define('ROOT_PATH', $rootpath);

    define('LOG_URL', 'http://localhost/mec//log');

    define('ESPACE', 'preprod');

} /*else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT'];
    if(substr($rootpath,-1,1)!="/") $rootpath.="/";
    define('MAIL_DEV', 'benoit@mon-espace-cuisson.com,webmaster1@mon-espace-cuisson.com');
    define('MAIL_DEV1', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DEV2', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE2', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DIRECTION', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COM1', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COM2', 'benoit@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'benoit@mon-espace-cuisson.com');
    define('MAIL_ADV', 'benoit@mon-espace-cuisson.com');

    define('SITE_PREFIX', 'test');
    define('SITE_HOST', 'http://test.mon-espace-cuisson.fr');
    define('SITE_URL', 'test.mon-espace-cuisson.fr');
    define('BACK_URL', 'http://test.mon-espace-cuisson.fr/admin');
    define('SITE_PATH', $rootpath);
    define('BACK_PATH', $rootpath.'admin');
    define('BACK_HOST', 'http://test.ma-cave-a-vin.fr/admin');
    define('ROOT_PATH', $rootpath);
     
    define('LOG_URL', '/var/www/ma-cave-a-vin.fr/configurations/www.mon-espace-cuisson.com/log');

    define('ESPACE', 'preprod');

} */
/*else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'fusion') !== false) {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/fusion.ma-cave-a-vin.fr";
    if(substr($rootpath,-1,1)!="/") $rootpath.="/";
    define('MAIL_DEV', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DEV1', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DEV2', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DEV_SENDER', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COMPTABLE2', 'benoit@mon-espace-cuisson.com');
    define('MAIL_DIRECTION', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COM1', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COM2', 'benoit@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'benoit@mon-espace-cuisson.com');
    define('MAIL_ADV', 'benoit@mon-espace-cuisson.com');

    define('SITE_PREFIX', 'fusion');
    define('SITE_HOST', 'https://fusion.ma-cave-a-vin.fr');
    define('SITE_URL', 'fusion.ma-cave-a-vin.fr');
    define('BACK_URL', 'https://fusion.ma-cave-a-vin.fr/admin');
    define('SITE_PATH', $rootpath);
    define('BACK_PATH', $rootpath.'admin');
    define('BACK_HOST', 'https://fusion.ma-cave-a-vin.fr/admin');
    define('ROOT_PATH', $rootpath);

    define('LOG_URL', '/var/www/vhosts/ma-cave-a-vin.fr/fusion.ma-cave-a-vin.fr/configurations/www.mon-espace-cuisson.com/log');

    define('ESPACE', 'preprod');

} else { // PROD !
    $rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
    define('MAIL_DEV', 'benoit@mon-espace-cuisson');
    define('MAIL_DEV1', 'benoit@mon-espace-cuisson');
    define('MAIL_DEV2', 'benoit@mon-espace-cuisson');
    define('MAIL_DEV_SENDER', 'benoit@mon-espace-cuisson');
    define('MAIL_COMPTABLE', 'j3mexp@gmail.com');
    define('MAIL_COMPTABLE2', 'jm.coubes@acilyon.com');
    define('MAIL_DIRECTION', 'benoit@mon-espace-cuisson.com');
    define('MAIL_COM1', 'stephane@mon-espace-cuisson.com');
    define('MAIL_COM2', 'stephane@mon-espace-cuisson.com');
    define('MAIL_CONTACT', 'contact@mon-espace-cuisson.com');
    define('MAIL_ADV', 'adv@mon-espace-cuisson.com');
	define('MAIL_TRANSPORT', 'adv@mon-espace-cuisson.com');

    define('SITE_PREFIX', 'www');
    define('SITE_HOST', 'www.mon-espace-cuisson.com');
    define('SITE_URL', 'https://www.mon-espace-cuisson.com');
    define('BACK_URL', 'https://www.mon-espace-cuisson.com/admin');
    define('SITE_PATH', '/var/www/vhosts/mon-espace-cuisson.com/httpdocs/');
    define('BACK_PATH', '/var/www/vhosts/mon-espace-cuisson.com/httpdocs/admin');
    define('BACK_HOST', 'https://www.mon-espace-cuisson.com/admin');
    define('ROOT_PATH', $rootpath);

    define('LOG_URL', '/var/www/vhosts/mon-espace-cuisson.com/httpdocs/log');

    define('ESPACE', 'prod');

}*/
define('HOST', 'www.mon-espace-cuisson.com');
define('CODE_TEL_ACCUEIL', '+33.4.81.91.90.40');
define('CODE_TEL_ACCUEIL2', '04 81 91 90 40');
define('CODE_TEL_ADV', '+33.4.81.91.90.41');
define('CODE_FAX', '+33.4.26.03.05.49');
define('NOM_SOCIETE', 'ACILYON DEVELOPPEMENTS');
define('ADRESSE_SOCIETE', '150 rue Paul Bert');
define('CP_SOCIETE', '69003');
define('VILLE_SOCIETE', 'Lyon');
define('PAYS_SOCIETE', 'FRANCE');
define('NOM_CONTACT', 'Mon Espace Cuisson');
define('SITE_NOM', 'Mon Espace Cuisson');
define('MAIL_ADMIN', 'benoit@mon-espace-cuisson.com');
define('NUM_FACTURE', '556500');
define('SITE_CODE', 'MEC');
define('HORAIRES', 'lundi au vendredi de 9h à 18h');