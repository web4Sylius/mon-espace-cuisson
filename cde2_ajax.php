<?
header('Content-type: text/html; charset=iso-8859-1'); 
require ("global.php");

/*$prix_encours = 0 ;
$article = $DB_site->query_first("SELECT* FROM article WHERE artid = '$artid'");

$datejour=date("Y") ."-". date("m") . "-" . date("d") ;
$articlepromo=$DB_site->query_first(" SELECT * FROM promotion WHERE artid='$article[artid]' and datedebut<='$datejour' and datefin>'$datejour' ");
if (isset($remise_sur_tout_catalogue) and $remise_sur_tout_catalogue > 0)
    {
    if ($articlepromo[promotionid]!="")
        {
        $prix_encours = $articlepromo[prix] ;
        }
    else
        {
        $prix_encours = $articlepromo[prix] ;
        }
    }
else // pas de remise sur tout le catalogue
    {
    if ($articlepromo[promotionid]!="")
        {
        $prix_encours = $articlepromo[prix] ;
        }
    else
        {
        $prix_encours = $article[prix] ;
        }
    }




$valeur = explode(",",$liste_caractvalid);
for ($i=0;$i<count($valeur);$i++)
    {
    if ($valeur[$i] != "0")
        {
        $modifprix = $DB_site->query_first("SELECT differenceprix FROM article_caractval WHERE artid = '$article[artid]' AND caractvalid = '$valeur[$i]'");
        $prix_encours += $modifprix[0];
        }
    }
    

// affichage du prix HT
if ($_COOKIE[userid] != "")
    {
    $pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '$_COOKIE[userid]'");
    $paysid = $pays_user[paysid] ;
    }
elseif ($user_info[userid] != "")
    {
    $pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '$user_info[userid]'");
    $paysid = $pays_user[paysid] ;
    }
else
    {
    $paysid = "57" ;
    }
switch ($article[tauxchoisi])
    {
    case '0' :
        $prix_encoursht = $prix_encours ;
        break ;
    case '1' : // si on est connecté on regarde tout de suite le pays !!!
        $montauxtva=$DB_site->query_first("select TVAtauxnormal from pays where paysid = '$paysid'");
        $prix_encoursht = $prix_encours / (1 + ($montauxtva[TVAtauxnormal]/100)) ;
        break ;
    case '2' : // si on est connecté on regarde tout de suite le pays !!!
        $montauxtva=$DB_site->query_first("select TVAtauxreduit from pays where paysid = '$paysid'");
        $prix_encoursht = $prix_encours / (1 + ($montauxtva[TVAtauxreduit]/100)) ;
        break ;
    }
$prix_encoursht = number_format($prix_encoursht, 2, ',', '') ;
$prix_encours= number_format($prix_encours, 2, ',', '');*/


$facture=$DB_site->query_first("SELECT * FROM facture WHERE facturid='$factureid' ");
$lignepaniertmps=$DB_site->query("SELECT l.artid,l.qte, a.poid, l.prix, l.tva,a.emballage_poids,a.paht,a.typearticleid FROM lignefacture l LEFT JOIN article a on (a.artid=l.artid) WHERE factureid = '$facture[facturid]' ");
$poidstotal=0;
$prixtotal=0;
$totalpaht=0;
$donttva=0;
$totalttc=array();
$artids=array();
$poidscaves=array();
$prixtypeinstall="";
while ($lignepaniertmp=$DB_site->fetch_array($lignepaniertmps)){
    $artids[$lignepaniertmp['artid']]+=$lignepaniertmp['qte'];
    $poidstotal+=1000*floatval(str_replace(",",".",$lignepaniertmp['emballage_poids']))* $lignepaniertmp['qte'];
    $prixtotal+=$lignepaniertmp[qte] * $lignepaniertmp[prix];
    $totalpaht+=$lignepaniertmp['qte'] * $lignepaniertmp['paht'];
    $donttva+=($lignepaniertmp[qte] * $lignepaniertmp[prix]) - (($lignepaniertmp[qte] * $lignepaniertmp[prix]) / (1 + $lignepaniertmp[tva]/100));
    $totalttc[$lignepaniertmp[tva]]+=$lignepaniertmp[qte] * $lignepaniertmp[prix];
    install_type($lignepaniertmp['artid']);
    if($lignepaniertmp['typearticleid']==1) $poidscaves[round($lignepaniertmp[poid])]+=$lignepaniertmp[qte];

}

/*
$fraisport=$DB_site->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidstotal' AND poidfin >= '$poidstotal' 
                                                and paysid = '$facture[paysid]'");

$gratuitfrais=$DB_site->query_first("SELECT distinct gratuit FROM fraisport WHERE paysid = '$facture[paysid]'");
if (($gratuitfrais[0] != 0) and ($prixtotal >= $gratuitfrais[0])) $fraisport[prix]=0;
*/
$pro = false;                              
if($user_info['raisonsociale'] != "") $pro = true;
$frais_port=new frais_port($DB_site,$artids,$facture[paysid],$prixtotal,$totalpaht,$template->site_code(),$pro,$user_info['userid']);
$tvacommande=(1+$facture['tvaport']/100);

if ($type==0){
    $tarif=ceil($frais_port->prix*$tvacommande);
}
if ($type==1){
    $install=pays_install($facture[paysid]);
    if($install=="sls") $tarif=prixsls($facture[codepostal],$poidstotal,$facture[paysid],$prixtypeinstall);
    if($install=="enigma") $tarifsls=prix_enigma($poidscaves,$template->site_code());
    $tarif+=ceil($frais_port->prix*$tvacommande);
}
if($type==2){
    $tarif=ceil($frais_port->prix*$tvacommande);
}
if($type==5){
    $tarif=0;
}

$portcadeau=0;
if ($facture[cadeauid]!=""){
    $cadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid='$facture[cadeauid]' ");
    if($cadeau['code']!=""){
      $montantkdos=$facture['montantcadeau'];
      if(($cadeau['port']==1 || $cadeau['port']==3) && $type==0) $tarif=0;
      if(($cadeau['port']==2 || $cadeau['port']==3) && $type==1) $tarif=0;
    }

    $code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
    if($code_caviste['code']!=""){
	  if($code_caviste['type']==2) $montantkdos=$facture['montantcadeau'];
	  if($code_caviste['type']==1 && $facture['montantcadeau']<$code_caviste['mini']) $montantkdos=$facture['montantcadeau'];
    }
    
    $prixtotal-=$montantkdos;
}elseif($type==1 && $tarif==0) $type=0;

//$DB_site->query("UPDATE facture SET prixport = '$tarif', modelivraison='$type' WHERE facturid = '$factureid'");

setcookie("trspt",$type);

$donttva+= $tarif - ($tarif/(1+$facture[tvaport]/100));
$prixtotal+=$tarif;

foreach($totalttc as $tx=>$value){
    $partkdosttc=$montantkdos*$value/array_sum($totalttc);
    $donttva-=$partkdosttc-$partkdosttc/(1+$tx/100);
}

$tarif=number_format($tarif, 2, ',', '');
$prixtotal=number_format($prixtotal, 2, ',', '');
$donttva=number_format($donttva, 2, ',', '');

echo "$type|||$tarif|||$prixtotal|||$donttva";


//echo "$artid|||$prix_encours|||$prix_encoursht" ;

?>
