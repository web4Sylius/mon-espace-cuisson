<?
/**************************************************************************/
/****************PAIEMENT SECURISE VIA CREDIT AGRICOLE*********************/
/**************************************************************************/
/**************************************************************************/
/*****$totalmontant = montant en euros de la transaction*******************/
/*****$facturid = num de transaction unique********************************/
/**************************************************************************/
/**************************************************************************/
/**************************************************************************/

$totalmontant = number_format($totalmontant, 2, '.', '')*100;
$pagepaiementcb3fois="";
$montant=$totalmontant;
$lg = strtolower($langue) ;
if ($lg == "de")
	{
	$lg = "ge" ;
	}

$premiermontant=round($montant/3);

$parm="merchant_id=045294738500039";
$parm="$parm amount=$montant";
$parm="$parm automatic_response_url=https://www.ma-cave-a-vin.fr/vad/macavavin/call_autoresponse.php";
$parm="$parm cancel_return_url=https://www.ma-cave-a-vin.fr/index.php";
$parm="$parm currency_code=978";
$parm="$parm language=$lg";
$parm="$parm normal_return_url=https://www.ma-cave-a-vin.fr/index.php?action=delpanier_complet";
$parm="$parm payment_means=CB,1,VISA,1,MASTERCARD,1";
$parm="$parm transaction_id=$facturid";
$parm="$parm merchant_country=fr";
$parm="$parm pathfile=/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/VAD/macavavin/param/pathfile";
$parm="$parm capture_mode=PAYMENT_N";
$parm="$parm capture_day=0";
$parm="$parm data=\"NB_PAYMENT=3;PERIOD=30;INITIAL_AMOUNT=$premiermontant\"";
$parm="$parm logo_id=commercant.gif";


$path_bin = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/VAD/macavavin/bin/request";
$result=exec("$path_bin $parm");
$tableau = explode ("!", "$result");
$code = $tableau[1];
$error = $tableau[2];
$message = $tableau[3];
if (( $code == "" ) && ( $error == "" ) ){
	$pagepaiementcb3fois.= "<CENTER>erreur appel request</CENTER><BR>";
	$pagepaiementcb3fois.= "executable request non trouve $path_bin<br>";
	$pagepaiementcb3fois.= "Result : $result";
}
else if ($code != 0){
	$pagepaiementcb3fois.= "<center><b><h2>Erreur appel API de paiement.</h2></center></b>";
	$pagepaiementcb3fois.= "<br><br><br>";
	$pagepaiementcb3fois.= " message erreur : $error (code : $code)";
}
else {
	$pagepaiementcb3fois.= $message;
}
/**************************************************************************/
/****************PAIEMENT SECURISE VIA CREDIT AGRICOLE*********************/
/**************************************************************************/
?>