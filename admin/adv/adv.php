<?php
ini_set("display_errors",1);
$rootpath = '/var/www/html/mec/';
require_once($rootpath . "/class/fournisseur.class.php");
include_once("adv.class.php");
include_once "../class/facture.class.php";


$datelivrtxt=($rs_facture['datelivraison']>0?date("d/m/Y",$rs_facture['datelivraison']):"?");

print "<style>
.tab_on a { background-color:white; border:solid thin black; text-align:center; text-decoration:none; padding:10px; }
.tab_off { padding-top:10px; }
.tab_off:hover { padding-top:0px; }
.tab_off a { background-color:#e4e4e4; border:solid thin #cccccc; text-align:center; text-decoration:none; padding:10px; }
.advgroup { background-color:#f4f4f4; margin-bottom:10px; font-size:12px; text-align:left; }
.advgroup > p { margin:6px; color:white; font-weight:bold; font-size:14px; background-color:AA0000; }
.advgroup > p.normal { background-color: #F4F4F4; color: #363e42; font-size: 11px; font-weight: normal; margin: 3px 0; padding: 0;  }
.advgroup table { font-size:12px; border-collapse:collapse; width:100%; }
.advgroup td { border-top:solid thin #808080; padding:5px; }
.advgroup select { max-width: 200px; }
.advgroup textarea { vertical-align:bottom; }
.advgroup input[type=\"radio\"], .advgroup input[type=\"checkbox\"] { vertical-align: -2px; }
.advgroup[id^='steptrspt'] { display:none; }
#stepTypeTrspt, #stepLieuEnlev { display:none; }
#steptrspt1 { display:block; }
#stepfourn2,#stepfourn3,#stepfourn4,#stepfourn5,#stepfourn6,#stepfourn7 { display:none; }
.caracts td { height:20px; border:none; }
.roinput { border:solid thin black; background:none; }
#fournbtn { height:20px; background:white; text-align:center; }
#stepsls2,#stepsls3,#stepsls4,#stepsls5,#stepsls6,#stepsls6,#stepsls7,#stepsls8,#stepsls9,#stepsls10,#stepsls11, #stepsls12 { display:none;}
#lieusls { display:none; }
.editvalue { background:#DDD; color:#1F2B63; text-align:center; cursor:pointer;}
.editvalue:hover { background:#AAA; }
#colis_conf, #colis_simple { display:none; }
#colis_list,#art_list { float:left; width:50%; min-height:50px; }
#colis_list > a { display:inline-block; margin:4px; }
#colis_list > a img { vertical-align:middle; }
#colis_list > div { margin:5px; width:90%; padding:5px; border:solid 1px #999; }
#colis_list > div img { margin-left:5px; cursor:pointer; }
#colis_list > div.selected { border-color:red; }
#colis_list > div > .del { float:right; }
#colis_list > div input[type=text] { width:40px; border:solid 1px #AAA; background:none; padding:2px; }
#colis_list > div input[type=text]:focus { background:white; border:solid 1px #999; }
#art_list p > * { margin-right:4px; }
div.fourn_alerte { position: relative; float: left; }
div.fourn_alerte img { width: 16px; cursor: pointer; vertical-align: -3px; margin-left: 4px; float: left; }
div.fourn_alerte p { position: absolute; top: 20px; width: 250px; color: red; margin: 0; padding: 5px; border: 1px solid black; background-color: white; max-width: 300px; font-size: 10px; display: none; }

.preview { background:#CCC url('images/apercu.png') no-repeat 5px center; width:100px; }
.clock { background:#CCC url('images/clock.png') no-repeat center right; width:100px; height:25px; }
.tnt { color:#FFAA00; }
.mory { color:#00AA00; }
.mazet { color:#0000FF; }

.edibtn { margin:10px; padding:4px; border:solid 1px #999; cursor: pointer; }
.edibtn.btn0 { color:red; }
.edibtn.btn1 { color:green; }

.tab_ar { width: 800; border-collapse: collapse; }
</style>";

$jsdevise="var devises=new Array();\n";
foreach($devises->listcodes() as $code)
    $jsdevise.="devises['$code']='".$devises->symbole($code)."';\n";

$offre=new offre_fournisseur($DB_site2);
$artids=$offre->artids_facture($facturid,true);
$offres=$offre->search_artids($artids,"fournisseur",$facture['heurefacture']);
    
$fournlink=array();
$fournisseur_devise=array();
$jspaht=array();
$lignefactures=$DB_site2->query("SELECT af.*,artid,lignefactureid, af.prix as paht FROM lignefacture lf INNER JOIN article_fournisseur af USING (artid) WHERE factureid = '$facturid'");
while($ligne=$DB_site2->fetch_array($lignefactures)){
    $artid=$ligne['artid'];
    $req=$DB_site2->query("SELECT *,fc.devise FROM fournisseur LEFT OUTER JOIN fournisseur_condition fc USING(societe) WHERE `type`='fournadv' AND societe='".mysqli_real_escape_string($DB_site2->link_id,$ligne['fournisseur'])."'");
    while($advf=$DB_site2->fetch_array($req)){
        if($advf['groupes']!=""){
            $groups=explode(",",$advf['groupes']);

            foreach($groups as $group){
                $fournlink[$artid][$group][]=$advf['mail'];
                $fournisseur_devise[$group]=$advf['devise'];
                $jspaht[fournkey($group)][$ligne['lignefactureid']]=$ligne['paht'];
            }
        }else{
            $fournlink[$artid][$advf['societe']][]=$advf['mail'];
            $fournisseur_devise[$advf['societe']]=$advf['devise'];

            $paht_temporaire = false;
            if(isset($offres[$fourn['fournisseur']])){
                foreach($offres[$fourn['fournisseur']] as $offre){
                    $pahtart=$offre->paht_articles($artids);

                    if(isset($pahtart[$fourn['artid']])){
                        foreach($pahtart[$fourn['artid']] as $paht=>$qte){
                            if($offre->articles[$fourn['artid']]['offre']!=""){
                                $paht_temporaire = true;
                                $jspaht[fournkey($advf['societe'])][$ligne['lignefactureid']]=$paht;
                            }
                        }
                    }
                }
            }

            if(!$paht_temporaire) $jspaht[fournkey($advf['societe'])][$ligne['lignefactureid']]=$ligne['paht'];
        }
    }

}

foreach($jspaht as $fkey=>$lignefactureids){
    $fourn_paht.="fourn_paht['$fkey']=new Array();\n";
    foreach($lignefactureids as $lignefactureid=>$paht)
	$fourn_paht.="fourn_paht['$fkey'][$lignefactureid]=".floatval($paht).";\n";
}

?>

<script language="javascript" src="../javascripts/prototype.js"></script>
<script language="javascript" src="trsptprice.js"></script>
<?php

  if($rs_facture['datelivraison'] > 0){ 
?>
     <p class="date_livraison" style="margin: 5px 0 0 5px;">Livraison <?php echo $rs_facture['changelivraison'] == "" ? "annoncée" : ''; ?> le <span style="color:#CC0000; font-weight: bold; font-size: 16px;"><?php echo date("d/m/Y",$rs_facture['datelivraison']); ?></span><?php echo $rs_facture['changelivraison'] != "" ? '(modifiée par '.$rs_facture['changelivraison'].')' : ''; ?> </p>
<?php } else { ?>
     <p class="date_livraison" style="margin: 5px 0 0 5px;">Aucune date de livraison annoncée</p>
<?php } ?>
     <p class="date_livraison" style="margin: 0 0 0 5px;">Livraison <span style="color:#CC0000; font-size: 16px;"><?php echo $modelivraison; ?></span></p>
<?php  
     if($user['vip'] == 1){
?>
     <p style="margin: 0 0 0 5px; font-weight: bold; font-size: 14px; color: #CC0000;"><img src="images/icon/agt_update_critical.png" style="vertical-align: -10px;"> Client VIP : <span style="color: black; font-weight: normal; text-decoration: underline;">Précautions particulières à prendre lors de la livraison</span></p>
<?php
     }

$fournisseurs=array("acilyon test"=>"pr@mon-espace-cuisson.com","stock acilyon"=>"ph.rodriguez@acilyon.com","eurocave - caves"=>"mbrun@eurocave.com;clefrancois@eurocave.com","eurocave accessoires"=>"FGRZUNOV@eurocave.com","climadiff"=>"ndufloux@climadiff.com;secretariat@climadiff.com;adv@vinosafe.com","vinosafe"=>"h.fehrenbach@vinosafe.com;jc.lody@vinosafe.com","la sommeliere"=>"isabelle.reze@lasommeliere.com","liebherr"=>"isabelle.andres@eberhardt.fr","dometic"=>"veronique.bocque@dometic.fr;laurence.martin@dometic.fr","Atelier du VIN"=>"commercial@atelierduvin.com;georges.richard@atelierduvin.com;support@atelierduvin.com","Systena"=>"info@systena.fr","Interfroid"=>"anne.gadeyne@interfroidservices.fr");

$dep="XX";
if(strlen($rs_facture['codepostal'])==5 && ($rs_facture['paysid']==57 || $rs_facture['paysid']==181)) $dep=substr($rs_facture['codepostal'],0,2);

//Mise en place de la liste déroulante de nos transporteurs
$sql = "SELECT f.*";
$sql .= " FROM fournisseur f";
$sql .= " INNER JOIN fournisseur_condition fc ON (fc.societe LIKE f.societe)";
$sql .= " WHERE fc.types LIKE '%transporteur%'";
$sql .= " AND f.type = 'trspt'";
$sql .= " ORDER BY societe, ville";
$requete = $DB_site2->query($sql);
$optlieutrspt = array( "" => "-- Sélectionner --");
while($lieutrspt=$DB_site2->fetch_array($requete)){
    $optlieutrspt[$lieutrspt['id']] = $lieutrspt['societe']." ".$lieutrspt['ville']." (".substr($lieutrspt['cp'],0,2).")";
}

//Mise en place de la liste déroulante de nos lieux de stockage

$optstock = array( "" => "-- Sélectionner --");
$stock=new stock($DB_site2);
foreach($stock->lieux() as $fournid=>$stockname)
    $optstock[$fournid] = $stockname;



$stock=new stock($DB_site2);
$adv=new adv($DB_site2);

if(isset($_GET['deladv'])){
    $deladv=new adv($DB_site2,$_GET['deladv']);
    $deladv->delete();
}

if(isset($_GET['deladvcmd'])){
    include_once("adv/envoi_fournisseur.php");
    $ef=new envoi_fournisseur($DB_site2,$_GET['deladvcmd']);
    $ef->delete();
    $adv->cancel_efid($_GET['deladvcmd']);
}

if(isset($_POST['nocmd'])){
    include_once("adv/envoi_fournisseur.php");
    $lignesfacid=explode(",",$_POST['lignefacid']);
    $listefl=array();
    $artcode="";
    foreach($lignesfacid as $lignefacid){
        $lignes=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lignefacid);
        $efl=new envoi_fournisseur_ligne($DB_site2);
        $efl->date=time();
        $efl->type=$_POST['type'];
        $efl->lignefactureid=$lignes['lignefactureid'];
        $efl->qte=$lignes['qte'];
        $listefl[]=$efl;
        $artcode.=$lignes['artcode']." ";
    }
    
    $ef=new envoi_fournisseur($DB_site2);
    $ef->facturid=$_GET['facturid'];
    $ef->destinataire="0";
    $ef->type=$_POST['type'];
    $ef->create();
    $ef->create_lines($listefl);
    
    $sql = "INSERT INTO suivi_commande SET";
    $sql .= " facturid    = ".$_GET['facturid'];
    $sql .= ", date       = ".time();
    $sql .= ", commercial = '".$user_info['username']."'";
    $sql .= ", suivi      = 'Pas de commande ".$_POST['type']." pour les produits ".$artcode."'";
    $DB_site2->query($sql);
}

// Affichage des infos recap fournisseur, stock, transporteur ...
include("adv/adv_recap.php");
$fourn_recap=fourn_recap($facturid);
$stock_recap=stock_recap($facturid);
$trspt_recap=trspt_recap($facturid);
$miseenservice_recap = miseenservice_recap($facturid);
$sls_recap=sls_recap($facturid);

$sfg_recap=sfg_recap($facturid);
$garantie_recap=garantie_recap($facturid);
unset($adv);

if(isset($_GET['advid'])){
    if($_GET['advid']==0){
        $adv=new adv($DB_site2);
        $adv->type=$_GET['type'];
        $adv->facturid=$_GET['facturid'];
        $adv->create();
        $_GET['advid']=$adv->id;
    }else
        $adv=new adv($DB_site2,$_GET['advid']);

    $adv->locked=isset($_GET['annule']);
    $adv->modif=isset($_GET['modif']);
}



print "<a name=\"adv\"></a><table height=\"30\" style=\"margin-top:5px;\">
<tr>
  <td class=\"tab_".($_GET['type']=="fourn"?"on":"off")."\" id=\"tablinkfourn\"><a href=\"javascript:tab('fourn');\">Fournisseur</a></td>
  <td class=\"tab_".($_GET['type']=="stock"?"on":"off")."\" id=\"tablinkstock\"><a href=\"javascript:tab('stock');\">Stock</a></td>
  <td class=\"tab_".($_GET['type']=="garantie"?"on":"off")."\" id=\"tablinkgarantie\"><a href=\"javascript:tab('garantie');\">Garantie</a></td>
  <td class=\"tab_".($_GET['type']=="trspt"?"on":"off")."\" id=\"tablinktrspt\"><a href=\"javascript:tab('trspt');\">Transporteur</a></td>
  <td class=\"tab_".($_GET['type']=="sls"?"on":"off")."\" id=\"tablinksls\"><a href=\"javascript:tab('sls');\">Installateur</a></td>
  <td class=\"tab_".(!isset($_GET['type']))."\" id=\"tablinkrecap\"><a href=\"javascript:tab('recap');\">Récapitulatif</a></td>
</tr>
</table>
<br>";

// if(!$adv->toutcmd($facturid,"fourn")) print "<a href=\"facture.php?action=editer&facturid=".$facturid."&advid=0&type=fourn#adv\"><img src=\"images/plus.jpg\" align=\"absmiddle\"> Nouvelle commande</a>";

print $fourn_recap;
if($_GET['type']=="fourn" && isset($_GET['advid'])) include("adv_fourn.php");

print $stock_recap;
if($_GET['type']=="stock" && isset($_GET['advid'])) include("adv_stock.php");

print $garantie_recap;

// if(!$adv->toutcmd($facturid,"trspt")) print "<a href=\"facture.php?action=editer&facturid=".$facturid."&advid=0&type=trspt#adv\"><img src=\"images/plus.jpg\" align=\"absmiddle\"> Nouvelle commande</a>";
print $trspt_recap;
if($_GET['type']=="trspt" && isset($_GET['advid'])) include("adv_trspt.php");


// if(!$adv->toutcmd($facturid,"sls")) print "<a href=\"facture.php?action=editer&facturid=".$facturid."&advid=0&type=sls#adv\"><img src=\"images/plus.jpg\" align=\"absmiddle\"> Nouvelle commande</a>";
print $sls_recap;

if($_GET['type']=="sls" && isset($_GET['advid'])) include("adv_sls.php");

print $miseenservice_recap;
if($_GET['type']=="miseenservice" && isset($_GET['advid'])) include("adv_miseenservice.php");

print $sfg_recap;

include("adv_js.php");

function advmarque($advmids,$lignemids){
    foreach($advmids as $mid)
	if(in_array($mid,$lignemids)) return true;

    return false;
}

function fournkey($txt){
    $search=array("é","è","ê","à","ç"," ",",",";",":","'");
    $replace=array("e","e","e","a","c");
    if(is_array($txt)){
      foreach($txt as $v)
	$return[]=strtolower(str_replace($search,$replace,$v));
      return $return;
    }else
      return strtolower(str_replace($search,$replace,$txt));
}

function formatGarantieName($duree,$cumul,$type,$fournisseur,$destinataire = ''){
    global $DB_site2;
    
    //$destinataire = 1; //Hack pour désactiver l'affichage dans l'ADV
    $txt_destinataire = $destinataire == 2 ? '"PRO" ' : '';
    if($duree<99) $txtduree = $duree." an".($duree>1?"s":"");
    if($duree == 99) $txtduree = "à vie";
    if($duree == 100) $txtduree="SFG";
    
    return $txt_destinataire.($cumul==1?"+":"").$txtduree." ".DAOType::getTypeById($DB_site2,$type)." ".$fournisseur;
}


?>
