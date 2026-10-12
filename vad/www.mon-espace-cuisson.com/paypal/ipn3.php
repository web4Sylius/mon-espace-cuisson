<?
require_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

function doTheCurl ()
{
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value)
	{
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	$ch = curl_init();

	// verifie si on est en mode test - sandbox
	if ($_POST["test_ipn"] == 1)
	{
		curl_setopt($ch, CURLOPT_URL, "https://www.sandbox.paypal.com/cgi-bin/webscr");
	}
	else
	{
		curl_setopt($ch, CURLOPT_URL, "https://www.paypal.com/cgi-bin/webscr");
	}


	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$fp = curl_exec ($ch);
	curl_close($ch);
	return $fp;
}

function doTheHttp ()
{
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value)
	{
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}
	// renvoi les donnees au serveur PayPal afin d'avoir une validation
	$header .= "Host: ipnpb.paypal.com:443\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

	// verifie si on est en mode sandbox.
	if ($_POST["test_ipn"] == 1)
	{
		$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
	}
	else
	{
		$fp = fsockopen ('ssl://ipnpb.paypal.com', 443, $errno, $errstr, 30);
	}

	if (!$fp) {
		return "ERROR";
	}
	else
	{
		fputs ($fp, $header . $req);
		while (!feof($fp))
		{
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0)
			{
				return "VERIFIED";
			}
			else if (strcmp ($res, "INVALID") == 0)
			{
				return "INVALID";
			}
		}
		fclose ($fp);
	}
	return "ERROR";
}

function doEmail ($title,$email)
{
	$alsomail = "$title\n\n----HTTP POST VARS----\n\n";
	foreach ($_POST as $key => $value)
	{
		$alsomail .= "$key = $value \n\n";
	}
	foreach($_GET as $kee => $val)
	{
		$gets .= "$kee = $val \n\n";
	}

	mail($email,$title,$alsomail . "\n\n get values \n\n" . $gets);
}

/*
Envoi de mail pour le troubleshoot:
*/

$fp = doTheCurl();
if (!$fp)
{
	// doEmail("cURL ERROR, SWITCHING TO HTTP",$email);
	$fp = doTheHttp();
}
$res = $fp;

/*
and after that, you can check to see if $res is
invalid or verified
*/

if (strcmp ($res, "VERIFIED") == 0)



{


 $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];


	/*
	Placer ici Vos requetes SQL pour mettre a jour votre base de donnees!

	 Vous pouvez aussi vous envoyer un email avec les donnees recus pour etre en mesure de determiner les causes des erreurs
	*/
	doEmail("PayPal Purchase Success",defined('MAIL_ADMIN') ? MAIL_ADMIN : 'webmaster1@mon-espace-cuisson.com');
}
else if (strcmp ($res, "INVALID") == 0)
{
	/*
	Je m'envoi un mail m'indiquand que j'ai recu une reponse invalid d'IPN



	*/
	doEmail("INVALID PayPal Purchase!!",defined('MAIL_ADMIN') ? MAIL_ADMIN : 'webmaster1@mon-espace-cuisson.com');
}
else
{
	/*
	En cas de fatal erreur - envoi d'un message
	*/
	doEmail("Error in IPN Code - Normally HTTP Error",defined('MAIL_ADMIN') ? MAIL_ADMIN : 'webmaster1@mon-espace-cuisson.com');
}

$f=fopen($_SERVER['DOCUMENT_ROOT']."/paypal/ppresponse.log","a");
fwrite($f,date("d/m/Y H:i")." ".$orderid." ".$paymentstatus." ".$valide." ".$TemplateCde3."\n");
fclose($f);
?>