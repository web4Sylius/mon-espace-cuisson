<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

     //mode d'appel
     $PBX_MODE = '4';    //pour lancement paiement par exécution
     //$PBX_MODE = '1';    //pour lancement paiement par URL
     
     //Identification
     if(ESPACE == 'preprod'){
          $PBX_SITE	= '1999888';
          $PBX_RANG	= '98' ;
          $PBX_IDENTIFIANT	= '3';
     } else {
          $PBX_SITE        = '1106772';
          $PBX_RANG        = '01';
          $PBX_IDENTIFIANT = '203988826';
     }
     
     //gestion de la page de connection : paramétrage "invisible"
     $PBX_WAIT        = '0';
     $PBX_TXT         = " ";
     $PBX_BOUTPI      = "nul";
     $PBX_BKGD        = "white";
     
     //informations paiement (appel)
     $PBX_TOTAL       = number_format($reglement_total, 2, '.', '') * 100;
     $PBX_DEVISE      = '978';
     $PBX_CMD         = $reglement_facturid."/".$reglement_id;
     $PBX_PORTEUR     = $reglement_mail;
     
     //informations nécessaires aux traitements (réponse)
     $PBX_RETOUR      = "auto:A\;amount:M\;reglement:R\;trans:T\;cbpays:Y\;error:E\;bin6:N\;type:C";
     $PBX_EFFECTUE    = SITE_HOST.'/payer-en-ligne.php';
     $PBX_REFUSE      = SITE_HOST.'/payer-en-ligne.php';
     $PBX_ANNULE      = SITE_HOST.'/payer-en-ligne.php';
     $PBX_REPONDRE_A	= "http://".$_SERVER['HTTP_HOST']."/cgi-bin/result_bis.php";

     //page en cas d'erreur
     $PBX_ERREUR      = "http://".$_SERVER['HTTP_HOST']."/cgi-bin/result_bis.php";
     
     $paiement_amex = '';
     
     //construction de la chaîne de paramètres
     $PBX             = "PBX_MODE=$PBX_MODE PBX_SITE=$PBX_SITE PBX_RANG=$PBX_RANG PBX_IDENTIFIANT=$PBX_IDENTIFIANT PBX_WAIT=$PBX_WAIT PBX_TXT=$PBX_TXT PBX_BOUTPI=$PBX_BOUTPI PBX_BKGD=$PBX_BKGD PBX_TOTAL=$PBX_TOTAL PBX_DEVISE=$PBX_DEVISE PBX_CMD=$PBX_CMD PBX_PORTEUR=$PBX_PORTEUR PBX_EFFECTUE=$PBX_EFFECTUE PBX_REFUSE=$PBX_REFUSE PBX_ANNULE=$PBX_ANNULE PBX_ERREUR=$PBX_ERREUR PBX_REPONDRE_A=$PBX_REPONDRE_A PBX_RETOUR=$PBX_RETOUR".$paiement_amex;

     //Lancement paiement par exécution
     echo(shell_exec( "cgi-bin/modulev2.cgi $PBX" ));
     exit;
