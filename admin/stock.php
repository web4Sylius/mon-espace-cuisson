<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include "../class/fonctions.php";
include "../class/stock.class.php";

$DB_site=$DB_site2;

$stock=new stock($DB_site2);
$arts=$stock->artids();

$msg="";
if(isset($_POST['fournisseur']) && isset($_POST['date_souhaitee'])){
    $items=array();
    foreach($_POST as $name=>$value){
    if(substr($name,0,4)=="qte_"){
        $item=new stock_item($DB_site2);
        $item->artid=substr($name,4);
        $item->paht=$_POST['paht_'.$item->artid];
        $item->ecopartht=$_POST['ecopartht_'.$item->artid];
        $item->origin="cmd";
        $item->lieu=$_POST['lieu'];
        $item->state=-1;
        for($i=0;$i<$value;$i++) $items[]=$item;
    }
    }
    $stock_cmd=new stock_cmd($DB_site2);
    $stock_cmd->destinataire=$_POST['fournisseur'];
    $stock_cmd->utilisateur=$user_info['username'];
    $stock_cmd->date=time();
    $d=explode("/",$_POST['date_souhaitee']);
    $stock_cmd->date_livraison=mktime(0,0,0,$d[1],$d[0],$d[2]);
    $stock_cmd->enlevement=$_POST['enlevement'];
    $stock_cmd->livraison=$_POST['lieu'];
    $stock_cmd->items=$items;
    $stock_cmd->create();

    print "<script language=\"javascript\">
    document.location='stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."';
    </script>";
    exit;
}

if(isset($_POST['cmdid']) && isset($_POST['transporteur'])){
    $stock_cmd=new stock_cmd($DB_site2,$_POST['cmdid']);
    $stock_cmd->transporteur=$_POST['transporteur'];
    $stock_cmd->tariftrspt=$_POST['tarif'];
    $stock_cmd->update();
}
if(isset($_POST['qte_produit_supprime']) && isset($_POST['id_produit_supprime'])){

    $qte_produit_a_supprime = $_POST['qte_produit_supprime'] ;
    $id_produit_a_supprime = $_POST['id_produit_supprime'] ;
    $qte_produit_supprime = intval($qte_produit_supprime);

    $stock_cmd=new stock_cmd($DB_site2,$_GET['stock_cmd_id']);
    $items=$stock_cmd->getitems($id_produit_a_supprime);
    $suivitxt = 'Supression de ';
    $i=0;
    foreach($items as $item){
        if($item->state == 0 || $item->state == -1 ){
            $item->state = -2 ;
            $item->update();
            $i++;
            if($i === $qte_produit_supprime) break ;
        }
    }
    $item = reset($items);
    $artcode = $item->artcode ;
    $suivitxt.=$i.' articles <br> Référence : '.$artcode;

    $stock_suivi=new stock_suivi($DB_site2);
    $stock_suivi->stock_cmd_id=$_GET['stock_cmd_id'];
    $stock_suivi->commercial=$user_info['username'];
    $stock_suivi->suivi=$suivitxt;
    $stock_suivi->create();
}
if(isset($_POST['qte_produit_ajout']) && isset($_POST['id_produit_ajout'])){
    $qte_produit_a_ajout = $_POST['qte_produit_ajout'] ;
    $id_produit_a_ajout = $_POST['id_produit_ajout'] ;
    $qte_produit_ajout = intval($qte_produit_ajout);

    $stock_cmd=new stock_cmd($DB_site2,$_GET['stock_cmd_id']);
    $items=$stock_cmd->getitems($id_produit_a_ajout);
    $suivitxt = 'Ajout de ';

    $new_item=new stock_item($DB_site2);
    foreach($items as $item){
        $new_item->stock_cmd_id=$item->stock_cmd_id;
        $new_item->artid=$item->artid;
        $new_item->state=$item->state;
        $new_item->paht=$item->paht;
        $new_item->ecopartht=$item->ecopartht;
        $new_item->lieu=$item->lieu;
        $new_item->rayon=$item->rayon;
        $new_item->trspt=$item->trspt;
        $new_item->prix_trspt=$item->prix_trspt;
        $new_item->origin=$item->origin;
        $new_item->dest=$item->dest;
        $new_item->utilisateur=$item->utilisateur;
        $new_item->date=$item->date;
        $new_item->date_livraison=$item->date_livraison;
        $new_item->date_enlevement=$item->date_enlevement;
        $new_item->date_enlevement_origin=$item->date_enlevement_origin;
        $new_item->date_livraison_origin=$item->date_livraison_origin;
        $new_item->date_sortie=$item->date_sortie;
        $new_item->utilisateur_sortie=$item->utilisateur_sortie;
        $new_item->state=$item->state;
        $new_item->facture=$item->facture;
        $new_item->facture_trspt=$item->facture_trspt;
    }
    $i=0;
    while($i < $qte_produit_a_ajout){
        $result=$new_item->insert();
        $i++;
    }

    $item = reset($items);
    $artcode = $item->artcode ;
    $suivitxt.=$qte_produit_a_ajout.' articles <br> Référence : '.$artcode;

    $stock_suivi=new stock_suivi($DB_site2);
    $stock_suivi->stock_cmd_id=$_GET['stock_cmd_id'];
    $stock_suivi->commercial=$user_info['username'];
    $stock_suivi->suivi=$suivitxt;
    $stock_suivi->create();
}

if(isset($_POST['attente'])){
    $stock_cmd=new stock_cmd($DB_site2,$_POST['attente']);
    $suivitxt="Articles entrés dans le stock :";
    foreach($_POST as $name=>$value){
        preg_match("/(chk)_([0-9]+)_([0-9]+)/",$name,$m);
        if($m[1]=="chk"){
            $items=$stock_cmd->getitems($m[3],0,$m[2]);
            $i=0;
            foreach($items as $item){
                $i++;
                if($i>intval($_POST['qte_'.$m[2]."_".$m[3]])) break;
                $item->utilisateur=$user_info['username'];
                $item->date=time();
                $item->state=1;
                $item->update();
            }
            $item0=new stock_item($DB_site2);
            $item0->loadartid($m[3]);
            $suivitxt.="<br>".$_POST['qte_'.$m[2]."_".$m[3]]." ".$item0->artcode." (".$item0->ref.")";
        }
    }
    $stock_suivi=new stock_suivi($DB_site2);
    $stock_suivi->stock_cmd_id=$_POST['attente'];
    $stock_suivi->commercial=$user_info['username'];
    $stock_suivi->suivi=$suivitxt;
    $stock_suivi->create();
}

if(isset($_POST['cmdid']) && isset($_POST['suivi'])){
    $stock_suivi=new stock_suivi($DB_site2);
    $stock_suivi->stock_cmd_id=$_POST['cmdid'];
    $stock_suivi->commercial=$user_info['username'];
    $stock_suivi->suivi=$_POST['suivi'];
    $stock_suivi->moyen=$_POST['moyen'];
    $stock_suivi->to=$_POST['destinataire'];
    $stock_suivi->rappel=$_POST['rappel'];
    $stock_suivi->pour=$_POST['pour'];
    $stock_suivi->create();
}

if(isset($_POST['modifcmdid']) && $_POST['enlevdate']==null && $_POST['valid_enlev']==null){
    if($_POST['updateall']=='1'){
        $items_liste=explode(",",$_POST['artid']);
        $dates=explode(",",$_POST['olddate']);
        $i=0;
        foreach($items_liste as $items){
            $item = new stock_item($DB_site2,$items);
            $livrdate=mktime(0,0,0,date("n",$_POST['livrdate']),date("j",$_POST['livrdate']),date("Y",$_POST['livrdate']));
            if($item->date_livraison==0){
                $item->date_livraison_origin=$livrdate;
            }
            $item->date_livraison=$livrdate;
            $item->update();
            $suivi=new stock_suivi($DB_site2);
            $suivi->stock_cmd_id=$_POST['modifcmdid'];
            $suivi->commercial=$user_info['username'];
            $suivi->suivi="Modification date enlèvement " .$item2->artcode." (".$item->ref.") ".date("d/m/Y",$dates[$i])." => ".date("d/m/Y",$_POST['livrdate']);
            $suivi->create();
            $i++;
        }
    }
    else {
        $cmd=new stock_cmd($DB_site2,$_POST['modifcmdid']);
        $items=$cmd->getitems($_POST['artid'],0,$_POST['olddate']);
        $livrdate=mktime(0,0,0,date("n",$_POST['livrdate']),date("j",$_POST['livrdate']),date("Y",$_POST['livrdate']));
        $i=0;
        foreach($items as $item){
            if($i==$_POST['qte']) break;
            if($item->date_livraison==0){
                $item->date_livraison_origin=$livrdate;
            }
            $item->date_livraison=$livrdate;
            $item->update();
            $i++;
        }
        $suivi=new stock_suivi($DB_site2);
        $suivi->stock_cmd_id=$_POST['modifcmdid'];
        $suivi->commercial=$user_info['username'];
        $suivi->suivi="Modification date livraison ".$_POST['qte']." ".$item2->artcode." (".$item->ref.") ".date("d/m/Y",$_POST['olddate'])." => ".date("d/m/Y",$_POST['livrdate']);
        $suivi->create();
    }
}

if(isset($_POST['modifcmdid']) && $_POST['livrdate'] == null && $_POST['valid_enlev']==null){
    if($_POST['updateall']=='1'){
        $items_liste=explode(",",$_POST['artid']);
        $dates=explode(",",$_POST['olddate']);
        $i=0;
        foreach($items_liste as $items){
            $item = new stock_item($DB_site2,$items);
            $enlevdate=mktime(0,0,0,date("n",$_POST['enlevdate']),date("j",$_POST['enlevdate']),date("Y",$_POST['enlevdate']));
            if($item->date_enlevement==0){
                $item->date_enlevement_origin=$enlevdate;
            }
            $item->date_enlevement=$enlevdate;
            $item->update();
            $suivi=new stock_suivi($DB_site2);
            $suivi->stock_cmd_id=$_POST['modifcmdid'];
            $suivi->commercial=$user_info['username'];
            $suivi->suivi="Modification date enlèvement " .$item2->artcode." (".$item->ref.") ".date("d/m/Y",$dates[$i])." => ".date("d/m/Y",$_POST['enlevdate']);
            $suivi->create();
            $i++;
        }
        $stock_cmd=new stock_cmd($DB_site2,$_POST['modifcmdid']);
    }
    else {
        $cmd=new stock_cmd($DB_site2,$_POST['modifcmdid']);
        $items=$cmd->getitems($_POST['artid'],0,$_POST['olddate'],"date_enlevement");
        $enlevdate=mktime(0,0,0,date("n",$_POST['enlevdate']),date("j",$_POST['enlevdate']),date("Y",$_POST['enlevdate']));
        $i=0;
        foreach($items as $item){
            if($i==$_POST['qte']) break;
            if($item->date_enlevement==0){
                $item->date_enlevement_origin=$enlevdate;
            }
            $item->date_enlevement=$enlevdate;
            $item->update();
            $i++;
        }
        $suivi=new stock_suivi($DB_site2);
        $suivi->stock_cmd_id=$_POST['modifcmdid'];
        $suivi->commercial=$user_info['username'];
        $suivi->suivi="Modification date enlèvement ".$_POST['qte']." ".$item2->artcode." (".$item->ref.") ".date("d/m/Y",$_POST['olddate'])." => ".date("d/m/Y",$_POST['enlevdate']);
        $suivi->create();
    }
}

if(isset($_POST['entree'])){
    $result=false;
    $msg="Erreur lors de l'ajout";
    $article=$DB_site2->query_first("SELECT paht,ecopartht FROM article WHERE artid=".intval($_POST['artid']));
    for($i=0;$i<intval($_POST['qte']);$i++){
        $item=new stock_item($DB_site2);
        $item->artid=$_POST['artid'];
        $item->lieu=$_POST['lieu'];
        $item->rayon=$_POST['rayon'];
        $item->paht=$_POST['paht'];
        $item->ecopartht=$article['ecopartht'];
        $item->origin="manu";
        $item->infos=$_POST['infos'];
        $item->date=time();
        $item->utilisateur=$user_info['username'];
        $item->state=1;
        $result=$item->insert();
    }
    if($result) $msg=$_POST['qte']." produits ajoutés";
}

if(isset($_POST['sortie'])){
    $result=false;
    $items=$stock->search($_POST['artid'],$_POST['lieu']);
    if(count($items)<intval($_POST['qte']))
      $msg="Il n'y a pas assez de quantité en stock";
    else{
      $i=0;
      foreach($items as $id=>$item){
    $i++;
    $item->dest=$_POST['dest'].$_POST['dest2'];
    $item->state=3;
    $item->date_sortie=time();
    $item->utilisateur_sortie=$user_info['username'];
    $item->infos=$_POST['infos'];
    $result=$item->update();
    if($i==intval($_POST['qte'])) break;
      }
    }
    if($result) $msg=$_POST['qte']." produits sortis du stock";
}

if(isset($_POST['liste'])){
    if($_POST['artid']!=0){
    $msg="Erreur";
    if(substr($_POST['artids'],0,1)==",") $_POST['artids']=substr($_POST['artids'],1);
    $result=$stock->setmini($_POST['artid'],$_POST['qte'],$_POST['qtemini'],$_POST['artids'],$_POST['lieu'],$_POST['delai']);
    if($result) $msg="Article modifié";
    }
    if(isset($_POST['delartid'])){
    $result=$stock->delmini($_POST['delartid'],$_POST['dellieu']);
    if(!$result) $msg="Erreur";
    }
}

if(isset($_GET['updatecache'])) $stock->updatecache();

if(isset($_POST['comment']) && isset($_POST['artid'])){
    $stock_cmd=new stock_cmd($DB_site2,$_GET['stock_cmd_id']);
    $items=$stock_cmd->getitems($_POST['artid'],array(0,1,2,3));
    $comment="comment";
    if(isset($_POST['commenttype']) && $_POST['commenttype']!="") $comment="comment_".$_POST['commenttype'];
    foreach($items as $si){
        $si->$comment=$_POST['comment'];
        $si->update();
    }
}

if(isset($_POST['advcomment'])){
    $suivi=new stock_suivi($DB_site2);
    $suivi->stock_cmd_id=$_GET['stock_cmd_id'];
    $suivi->commercial=$user_info['username'];
    $suivi->moyen="adv".$_POST['commenttype'];
    $suivi->suivi=$_POST['advcomment'];
    $suivi->create();
}

// TSI Gestion de demande transfert entre stock
if(isset($_POST['transfert'])){
    $stock=new stock($DB_site2);
    $i=0;
    foreach($stock->search($_POST['tArtid'],$_POST['tDep']) as $item){
            $item->transfer($user_info['username'],$_POST['tArr']);
            $i++;
            if($i==$_POST['tQte']) break;
    }
    $stock = new stock($DB_site2);
    $lieux = $stock->lieux();
    $t_txt= $_POST['tQte']."x produits du stock ".$lieux[$_POST['tDep']]." ont été transféré vers ".$lieux[$_POST['tArr']];
    $DB_site2->query("INSERT INTO suivi_article (`artid`, `commercial`, `date`, `suivi`) VALUES(".$_POST['tArtid'].", '".$user_info['username']."', ".time().", '".$t_txt."')");
    
    //TSI pour retourner dans la page de transfert apres l'action et la mise a jours du suivi
    $_GET['action']="transfert";
}

//Mise a jour des qte du suivi
if(isset($_POST['majsuivi'])){
    $majqte = $_POST['majsuivi'];
    $majop = "";
    
    if($_POST['majop'] == 1 ){
        $majop = "-";
        $txt = "enlevé(s)";
    }else
        $txt = "ajouté(s)";


    $commentaire = $majqte." produit(s) ".$txt;
    $DB_site2->query("INSERT INTO stock_maj SET artid='".$_POST['artid']."', date ='".time()."', lieu='".$_POST['lieu']."', user='".$admin_username."', qte='".$majop.$majqte."', commentaire='".$commentaire."' ");

}

print "<style>
.main{width:1400px;}
#stock_cmd { position:absolute; background:white; width:600px; border:solid thin black; padding:5px; display:none; box-shadow: 10px 10px 5px #888; }
.cmd_btn { text-align:center; margin-top:20px; }
.simbtn { border:solid thin #CCCCCC; padding:2px; margin-left:5px; margin-top:5px; }
.simbtn a { color:red; font-weight:bold; text-decoration:none; }
#similaires { display:none; border:solid thin #cccccc; padding:5px; }
#stock_table { border-collapse:collapse; font-size:11px; }
#stock_table td { border:solid thin #cccccc; }
#stock_table td.libel { max-width: 200px !important; white-space: normal;}
#stock_table .center { text-align:center; }
#stock_table .bottom { height:30px; border-style:solid none none none; border-top:solid thin #888888; }
#suividiv { border:solid thin black; background:white; position:absolute; display:none; }
.cmdtable { border-collapse:collapse; }
.cmdhead { height:30px; color:black; background-color:#DCDCDC; border-style:solid solid none solid; border-width:1px; border-color:black; }
.cmdtdl { border-left:solid thin black; }
.cmdtdr { border-right:solid thin black; }
.cmdsep { height:30px; border-top:solid thin black; }
.totaltable { float:left; margin:0 20px 0 0; border-collapse:collapse; font-size:11px; }
.totaltable thead { font-weight:bold; text-align:center; }
.totaltable td { border:solid thin #cccccc; padding:2px; }
.totaltable tbody td:nth-child(2), .totaltable tbody td:nth-child(3) { text-align:right; }
</style>
<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css\">
<script language=\"javascript\">
var servername=\"".$_SERVER['SERVER_NAME']."\";
var lieufourn=new Array();\n";
$requete=$DB_site2->query("SELECT id,societe,ville FROM fournisseur WHERE type='fourn' ORDER BY societe");
while($f=$DB_site2->fetch_array($requete)){
    print "lieufourn[\"".$f['societe']."\"]={id:\"$f[id]\",ville:\"$f[ville]\",societe:\"$f[societe]\"};\n";
    $optenlev.="<option value=\"".$f['id']."\">".$f['societe']." ".$f['ville']."</option>";
}

print "</script>
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\" src=\"stock.js\"></script>
<script language=\"javascript\" src=\"../javascripts/prototype.js\"></script>
<script language=\"javascript\" src=\"filebox.js\"></script>
<LINK href=\"filebox.css\" type=\"text/css\" rel=\"stylesheet\">";

print "<script language=\"javascript\">
    function showStock(value){
        switch(value){
            case 'add':
                document.getElementById('addStock').style.display = \"block\";
                hideStock('cancelStock');
            break;

            case 'cancel':
                document.getElementById('cancelStock').style.display = \"block\";
                hideStock('addStock');
            break;

            case 'cancel2':
                var action = 'cancel';
                var cmd = document.getElementsByName(\"cmdStockC\")[0].value;
                var data = 'action='+action+'&cmd='+cmd+'&mail=1';
                if(confirm(\"Valider l'annulation de la commande ?\")){
                    ajaxrequest('ajax/commande_stock.php?'+data, succesStock);
                }
            break;
        }
    }
    
    function hideStock(value){
        var myDiv = document.getElementById(value);
        myDiv.style.display = \"none\";
        
        document.getElementsByName(\"date_souhaitee\")[0].value = '';
        document.getElementsByName(\"artStockA\")[0].value      = '';
        document.getElementsByName(\"qteStockA\")[0].value      = '';
    }
    
    function cal_delete(value){
        var myValue = document.getElementsByName(value);
        myValue[0].value = '';
    }
    
    function videStock(){
        document.getElementsByName(\"date_souhaitee\")[0].value = '';
        document.getElementsByName(\"qteStockA\")[0].value      = '';
    }
    
    function submitStock(form){
        if(form == 'add'){
            var action  = form;
            var cmd     = document.getElementsByName(\"cmdStockA\")[0].value;
            var lieu    = document.getElementsByName(\"lieuStockA\")[0].value;
            var date    = document.getElementsByName(\"date_souhaitee\")[0].value;
            var art     = document.getElementsByName(\"artStockA\")[0].value;
            var split   = art.split(\":\");
            var artid   = split[0];
            var qte     = document.getElementsByName(\"qteStockA\")[0].value;
            var prix    = split[1];
            var data = 'action='+action+'&cmd='+cmd+'&date='+date+'&artid='+artid+'&qte='+qte+'&prix='+prix+'&lieu='+lieu;
            if(artid == '' || qte == ''){
                alert(\"Veuillez remplir tous les champs\");
            }else{
                if(confirm(\"Valider l'ajout ?\")){
                    ajaxrequest('ajax/commande_stock.php?'+data, succesStock);
                }
            }
        }else if(form == 'cancel'){
            var action  = form;
            var cmd     = document.getElementsByName(\"cmdStockC\")[0].value;
            var data = 'action='+action+'&cmd='+cmd;
            if(confirm(\"Valider l'annulation de la commande ?\")){
                ajaxrequest('ajax/commande_stock.php?'+data, succesStock);
            }
        }else
            return false;
    }
    
    function succesStock(httpRequest){
        var split = httpRequest.responseText.split(':');
        var success = split[0];
        var action = split[1];
        var cmd = split[2];
        if(success === 'OK'){
            switch(action){
                case 'add':
                    alert('Article ajouté');
                    window.location.href = \"stock.php?action=cmd&stock_cmd_id=\"+cmd;
                    break;
                
                case 'edit':
                    alert('Article modifié');
                    window.location.href = \"stock.php?action=cmd&stock_cmd_id=\"+cmd;
                    break;
                
                case 'delete':
                    alert('Article supprimé');
                    window.location.href = \"stock.php?action=cmd&stock_cmd_id=\"+cmd;
                    break;
                
                case 'cancel':
                    alert('Commande annulée');
                    window.location.href = \"stock.php?action=listcmd\";
                    break;
            }
        }else{
            alert(\"Erreur lors de la validation du formulaire\");
        }
    }
    
    function ligneStockClick(value, i){
        switch(value){
            case 'edit':
                document.getElementById('modifLigneStock'+i).style.display      = \"none\";
                document.getElementById('suppLigneStock'+i).style.display       = \"none\";
                document.getElementById('livraisonLigneStock'+i).style.display  = \"none\";
                document.getElementById('validLigneStock'+i).style.display      = \"\";
                document.getElementById('cancelLigneStock'+i).style.display     = \"\";
                document.getElementById('dateLigneStock'+i).style.display       = \"\";
                document.getElementById('xLigneStock'+i).style.display          = \"\";
                document.getElementById('qteLigne'+i).disabled                  = false;
                document.getElementById('prixLigne'+i).disabled                 = false;
                var ligne = document.getElementsByName('idLigne'+i)[0].value;
                var split = ligne.split(\":\");
                document.getElementsByName('prixLigne'+i)[0].value              = split[3];
                break;
                
            case 'valid':
                var ligne   = document.getElementsByName('idLigne'+i)[0].value;
                var split   = ligne.split(\":\");
                var cmd     = split[0];
                var art     = split[1];
                var old     = split[2];
                var qte     = document.getElementsByName('qteLigne'+i)[0].value;
                var prix    = document.getElementsByName('prixLigne'+i)[0].value;
                prix        = prix.replace(\",\", \".\");
                if(document.getElementsByName('dateLigneStock'+i)[0].value != '' || document.getElementsByName('dateLigneStock'+i)[0].value != 0){
                    var dateA   = document.getElementsByName('dateLigneStock'+i)[0].value;
                    var dateS   = dateA.split(\"/\");
                    date        = mktime(0, 0, 0, dateS[1], dateS[0], dateS[2]);
                }else{
                    date = old;
                }
                var data = 'action=edit'+'&cmd='+cmd+'&date='+date+'&artid='+art+'&qte='+qte+'&prix='+prix+'&where='+old+'&paht='+split[3];
                if(confirm(\"Valider cette modification ?\")){
                    ajaxrequest('ajax/commande_stock.php?'+data, succesStock);
                }
                break;
                
            case 'cancel':
                document.getElementById('modifLigneStock'+i).style.display      = \"\";
                document.getElementById('suppLigneStock'+i).style.display       = \"\";
                document.getElementById('livraisonLigneStock'+i).style.display  = \"\";
                document.getElementById('qteLigne'+i).disabled                  = true;
                document.getElementById('prixLigne'+i).disabled                 = true;
                document.getElementById('validLigneStock'+i).style.display      = \"none\";
                document.getElementById('cancelLigneStock'+i).style.display     = \"none\";
                document.getElementById('dateLigneStock'+i).style.display       = \"none\";
                document.getElementById('xLigneStock'+i).style.display          = \"none\";
                var ligne = document.getElementsByName('idLigne'+i)[0].value;
                var split = ligne.split(\":\");
                document.getElementsByName('prixLigne'+i)[0].value              = split[3]*split[4];
                document.getElementsByName('qteLigne'+i)[0].value               = split[4];
                break;
                
            case 'delete':
                var ligne   = document.getElementsByName('idLigne'+i)[0].value;
                var split   = ligne.split(\":\");
                var cmd     = split[0];
                var art     = split[1];
                var old     = split[2];
                var data    = 'action=delete'+'&cmd='+cmd+'&date='+old+'&artid='+art+'&paht='+split[3];
                if(confirm(\"Valider la suppression ?\")){
                    ajaxrequest('ajax/commande_stock.php?'+data, succesStock);
                }
                break;
        }
    }
    
    function mktime(){
        var d = new Date(),
        r = arguments,
        i = 0,
        e = ['Hours', 'Minutes', 'Seconds', 'Month', 'Date', 'FullYear']
        for (i = 0; i < e.length; i++) {
            if (typeof r[i] === 'undefined') {
                r[i] = d['get' + e[i]]()
                r[i] += (i === 3)
            } else {
                r[i] = parseInt(r[i], 10)
                if (isNaN(r[i])) {
                    return false
                }
            }
        }
        r[5] += (r[5] >= 0 ? (r[5] <= 69 ? 2e3 : (r[5] <= 100 ? 1900 : 0)) : 0)
        d.setFullYear(r[5], r[3] - 1, r[4])
        d.setHours(r[0], r[1], r[2])
        return (d.getTime() / 1e3 >> 0) - (d.getTime() < 0)
    }

    function updcomptacomment(text, artid, type){
        var form=document.createElement('form');
        form.method='post';
        var input=document.createElement('input');
        input.name='comment';
        input.value=text;
        form.appendChild(input);
        var input=document.createElement('input');
        input.name='artid';
        input.value=artid;
        form.appendChild(input);
        if(type!=undefined){
            var input=document.createElement('input');
            input.name='commenttype';
            input.value=type;
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }
    
    function updadvcomment(type){
        var form=document.createElement('form');
        form.method='post';
        var input=document.createElement('input');
        input.name='advcomment';
        input.value=document.getElementById(type+'advcomment').value;
        form.appendChild(input);
        var input=document.createElement('input');
        input.name='commenttype';
        input.value=type;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    
    function doTransfert(artid){
        document.getElementById('formTransfert').innerHTML = '';
        ajaxrequest(\"includes/stock/form.php?artid=\"+artid, ajaxDoTransfert_cb);
    }

    function ajaxDoTransfert_cb(httprequest){
        document.getElementById('formTransfert').innerHTML = httprequest.responseText;
    }

    function doImg(artid){
        document.getElementById('imgart').src = '/configurations/www.mon-espace-cuisson.com/images/produits/br/'+artid+'.jpg';
        document.getElementById('imgart').style.display = 'block';

        document.getElementById('formTransfert').innerHTML = '';
        document.getElementById('formTransfert2').innerHTML = '';
    }

    function doLieu(lieu){
        var artid = document.getElementsByName('tArtid')[0].value;
        document.getElementById('formTransfert2').innerHTML = '';
        if(lieu!='')
                    ajaxrequest(\"includes/stock/form.php?tartid=\"+artid+\"&tlieu=\"+lieu, ajaxDoLieu_cb);
    }

    function ajaxDoLieu_cb(httprequest){
        document.getElementById('formTransfert2').innerHTML = httprequest.responseText;
    }
    function maj(op){
        jQuery('#majop').val(op);
        jQuery('#majvaleur').show();
        if(op == 1){ 
            jQuery('#majplus').css('opacity','0.5');
            jQuery('#majmoins').css('opacity','1');
        }
        else{
            jQuery('#majmoins').css('opacity','0.5');
            jQuery('#majplus').css('opacity','1');  
        }
    }

    function affiche_type_suivi(type=null) {
        if(type===null) {
            jQuery('.suiviint').show();
            jQuery('.suivipro').show();
        }
        else {
            jQuery('.suiviint').hide();
            jQuery('.suivipro').hide();
            jQuery('.suivi'+type).show();
        }
    }
</script>";

print "<h1>Stock</h1>";

$commandable = array( 0 => "Non", 1 => "Oui", 2 => "Prix sur demande");
$articleActif = array( 0 => "Actif invisible", 1 => "Oui", 2 => "Supprimé", 3 => "Archivé définitivement", 4 => "Nouveau, a contrôler", 5 => "Archivé temporairement", 6 => "En cours de création");
 
$superuser=array("philippe","benoit","cc", "ariel");
$menu="<p><a href=\"stock.php\">Etat du stock</a> &nbsp; <a href=\"stock.php?action=listcmd\">Liste des commandes</a> &nbsp; ".(in_array($user_info['username'],$superuser)?"<a href=\"stock.php?action=entree\">Entrée manuelle</a> &nbsp; <a href=\"stock.php?action=sortie\">Sortie manuelle</a> &nbsp;":"")." <a href=\"stock.php?action=liste\">Ajouter un article</a> &nbsp; <a href=\"stock.php?action=suivi\">Suivi</a> &nbsp; <a href=\"stock.php?action=transfert\">Transfert de stock</a>";
$menu.=" &nbsp; <a href=\"stock.php?action=qteinsttype\">Qté instantannée par type</a>";


        $newcmdid=1+$stock->cmd_nb();
switch($_GET['action']){
    case "":
        $lieux=$stock->lieux();
        $liste_marque_lieu=$stock->marques_liste();
        $requete=$DB_site2->query("SELECT id,societe,mail FROM fournisseur WHERE type='fournadv' ORDER BY societe");
        while($fourn=$DB_site2->fetch_array($requete)) $group[$fourn['societe']][]=$fourn['mail'];

        foreach($group as $societe=>$mails) $optfourn.="<option value=\"".implode(";",$mails)."\" onclick=\"document.getElementsByName('fournisseur')[0].value='".addslashes($societe)."';\">".$societe."</option>";
     
        $lieuopt="<option value=\"\"></option>";
        $lieuopt2="<option value=\"\">Tout</option>";
        $marqueopt="<option value=\"\"></option>";
        $marqueopt2="<option value=\"\">Tout</option>";
        
        foreach($lieux as $id=>$lieu){
          if(is_null($_GET['lieu']) || $_GET['lieu']==$id) $lieuopt.="<option value=\"$id\">$lieu</option>";
          $lieuopt2.="<option value=\"$id\"".($_GET['lieu']==$id?" selected":"").">$lieu</option>";
        }

        foreach($liste_marque_lieu as $marque){
          if(is_null($_GET['marque']) || $_GET['marque']==$marque) $marqueopt.="<option value=\"".$marque."\">".$marque."</option>";
          $marqueopt2.="<option value=\"".$marque."\"".($_GET['marque']==$marque?" selected":"").">".$marque."</option>";
        }
         print "<div id=\"stock_cmd\">
            <form action=\"stock.php\" name=\"stock_cmd_form\" method=\"post\">
            <input type=\"hidden\" name=\"fournisseur\">
            <!--<p>Fournisseur : <select name=\"mails\">$optfourn</select></p>-->
            <!--<p>Lieu d'enlÃ¨vement : <span id=\"enlevement\"></span><select name=\"enlevement\">$optenlev</select></p>-->
            <p>Lieu de livraison : <select name=\"lieu\" onchange=\"fourncoord(this.value);\">$lieuopt</select><input type=\"hidden\" name=\"livraison\"></p>
            <p>Date livraison souhaitée : <input type=\"text\" id=\"date_souhaitee\" name=\"date_souhaitee\" onclick=\"calendrier_show(this.id,this.value);\" size=\"8\" readonly></p>
            <p>Détail de votre commande :</p>
            <div id=\"stock_cmd_details\"></div>
            <div class=\"cmd_btn\"><input type=\"button\" value=\"OK\" onclick=\"document.forms.stock_cmd_form.submit()\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('stock_cmd').style.display='none';\"></div>
            </form>
            </div>";
        print "<h3>Etat du stock</h3>
            $menu
            <form method=\"get\">
            <input type=\"hidden\" name=\"action\" value=\"recherche\">
            <p>Lieu : <select name=\"lieu\" value=\"".$_GET['lieu']."\">$lieuopt2</select> Marque : <select name=\"marque\" value=\"".$_GET['marque']."\">$marqueopt2</select> <input type=\"submit\" value=\"valider\" onclick=\"wait_chargement('".$host."')\"></p>
            <div id=\"div_wait\" style=\"text-align:center;\"></div>";
        break;

        case "recherche":
        $lieux=$stock->lieux();
        $liste_marque_lieu=$stock->marques_liste();
        $requete=$DB_site2->query("SELECT id,societe,mail FROM fournisseur WHERE type='fournadv' ORDER BY societe");
        while($fourn=$DB_site2->fetch_array($requete)) $group[$fourn['societe']][]=$fourn['mail'];

        foreach($group as $societe=>$mails) $optfourn.="<option value=\"".implode(";",$mails)."\" onclick=\"document.getElementsByName('fournisseur')[0].value='".addslashes($societe)."';\">".$societe."</option>";
     
        $lieuopt="<option value=\"\"></option>";
        $lieuopt2="<option value=\"\">Tout</option>";
        $marqueopt="<option value=\"\"></option>";
        $marqueopt2="<option value=\"\">Tout</option>";
        
        foreach($lieux as $id=>$lieu){
          if(is_null($_GET['lieu']) || $_GET['lieu']==$id) $lieuopt.="<option value=\"$id\">$lieu</option>";
          $lieuopt2.="<option value=\"$id\"".($_GET['lieu']==$id?" selected":"").">$lieu</option>";
        }

        foreach($liste_marque_lieu as $marque){
          if(is_null($_GET['marque']) || $_GET['marque']==$marque) $marqueopt.="<option value=\"".$marque."\">".$marque."</option>";
          $marqueopt2.="<option value=\"".$marque."\"".($_GET['marque']==$marque?" selected":"").">".$marque."</option>";
        }
         print "<div id=\"stock_cmd\">
            <form action=\"stock.php\" name=\"stock_cmd_form\" method=\"post\">
            <input type=\"hidden\" name=\"fournisseur\">
            <!--<p>Fournisseur : <select name=\"mails\">$optfourn</select></p>-->
            <!--<p>Lieu d'enlevement : <span id=\"enlevement\"></span><select name=\"enlevement\">$optenlev</select></p>-->
            <p>Lieu de livraison : <select name=\"lieu\" onchange=\"fourncoord(this.value);\">$lieuopt</select><input type=\"hidden\" name=\"livraison\"></p>
            <p>Date livraison souhaitée : <input type=\"text\" id=\"date_souhaitee\" name=\"date_souhaitee\" onclick=\"calendrier_show(this.id,this.value);\" size=\"8\" readonly></p>
            <p>Détail de votre commande :</p>
            <div id=\"stock_cmd_details\"></div>
            <div class=\"cmd_btn\"><input type=\"button\" value=\"OK\" onclick=\"document.forms.stock_cmd_form.submit()\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('stock_cmd').style.display='none';\"></div>
            </form>
            </div>";
        print "<h3>Etat du stock</h3>
            $menu
            <form method=\"get\">
            <input type=\"hidden\" name=\"action\" value=\"recherche\">
            <p>Lieu : <select name=\"lieu\" value=\"".$_GET['lieu']."\">$lieuopt2</select> Marque : <select name=\"marque\" value=\"".$_GET['marque']."\">$marqueopt2</select> <input type=\"submit\" value=\"valider\" onclick=\"wait_chargement('".$host."')\"> <input type=\"checkbox\" onclick=\"show_hide_column(this.checked)\" style=\"margin-left:5px;\"> Afficher le détail </p>
            <div id=\"div_wait\" style=\"text-align:center;\"></div>";

        if($_GET['lieu']=="") $_GET['lieu']=null;
        $r1=$stock->stockbyqte(array(0,1,2,3),$_GET['lieu']);
        $r=array_merge($r1,$stock->stock0(array(0,1,2),$_GET['lieu'],true));
        ksort($r);

        $r2=$stock->stockbyqte(2,$_GET['lieu']);
        $r0=$stock->stockbyqte(0,$_GET['lieu']);
        $r3=$stock->stockbyqte(3,$_GET['lieu']);

        $lieux=$stock->lieux();
        
        $totalmarque=$qtemarque=array();
        $totaltype=$qtetype=array();
        $totalligne=0;
        $total=0;
        $delai12m=time()-365*86400;
        $delai6m=time()-182*86400;
        $delai3m=time()-91*86400;
        $delai1m=time()-31*86400;
        $tr=array();
        $liste_marque_lieu=array();
   
        foreach($r as $item){
          

            if((isset($_GET['marque']) && $_GET['marque']==$item->marque) || empty($_GET['marque'])){

               //n'affiche pas les items plus en stock, sauf s'ils ont des articles similaires en stock, et sauf s'ils peuvent être commandés
                if(!empty($item->similaire)){
                    $artids_list = array();
                    foreach($item->similaire as $key_sim => $val_sim){
                        $artids_list[] = $key_sim;
                    }
                    $artid_artids = "(sm.artid='".$item->artid."' OR sm.artid IN (".implode(",",$artids_list)."))";
                }
                else{
                     $artid_artids = "sm.artid='".$item->artid."'";
                }
                $req_state3=$DB_site2->query("SELECT sm.artid FROM stock_mini sm LEFT JOIN stock_item si ON sm.artid = si.artid WHERE ".$artid_artids." AND (state IN ('1','2','3','0') OR state IS NULL) AND (sm.mini+sm.alert)>0".(!is_null($_GET['lieu'])?" AND sm.lieu =".$_GET['lieu']:""));
                if($stock->is_similaire($item->artid)==false && $DB_site2->num_rows($req_state3) == 0){
                    //si aucun article correspondant dans stock_mini, on regarde s'il n'y a pas d'articles actifs dans stock_item
                    if(!empty($item->similaire)){
                        $artids_list = array();
                        foreach($item->similaire as $key_sim => $val_sim){
                            $artids_list[] = $key_sim;
                        }
                        $artid_artids = "(si.artid='".$item->artid."' OR si.artid IN (".implode(",",$artids_list)."))";
                    }
                    else{
                       $artid_artids = "si.artid='".$item->artid."'";
                   }
                    $req_autrelieu=$DB_site2->query("SELECT si.artid FROM stock_item si WHERE ".$artid_artids.(!is_null($_GET['lieu'])?" AND si.lieu =".$_GET['lieu']:"")." AND state IN ('1','2')");
                    //si pas d'articles, on continue la boucle sans afficher la ligne
                    if($DB_site2->num_rows($req_autrelieu) == 0){
                         continue;
                    }    
                }


                //si l'item est un produit similaire on ne l'affiche pas, sinon ça fera doublon avec l'art principal :
                if(($stock->is_similaire($item->artid)==false && empty($_GET['marque'])) || $_GET['marque']!==''){

                //on additionne les qte et les paht des articles similaires
                    $paht_r0_sim=$paht_r1_sim=$qte_r0_sim=$qte_r2_sim=0;
                    $sim_artids=array();
                    if(!empty($item->similaire)){
                        foreach($item->similaire as $key_sim => $val_sim){
                            $arts_simi=$stock->stockbyartid_sim($key_sim,$_GET['lieu'],array(1,2));
                            $item->qte+=$arts_simi['qte'];
                            if(!empty($arts_simi)){
                               $paht_r0_sim+=$r0[$key_sim]->paht;
                               $paht_r1_sim+=$r1[$key_sim]->paht-$r3[$key_sim]->paht;
                               $qte_r0_sim+=$r0[$key_sim]->qte;
                               $qte_r2_sim+=$r2[$key_sim]->qte;

                            }
                            $sim_artids[]=$key_sim;
                        }
                    }
                    $sim_artcodes="";
                    $simi_principal=null;
                    if($stock->is_similaire($item->artid)!==false){
                        $item_similaire = $stock->is_similaire($item->artid);
                        $sql_sim="SELECT si.*,sm.mini,sm.alert,sm.delai,a.artcode FROM stock_item si LEFT JOIN stock_mini sm ON (sm.artids LIKE '%,si.artid%' OR sm.artids LIKE '%si.artid,%' OR sm.artids = 'si.artid') LEFT JOIN stock_mini sm2 ON sm2.lieu = 'si.lieu' INNER JOIN article a ON a.artid = si.artid WHERE si.artid='".$stock->is_similaire($item->artid)."' AND si.state IN ('1','2')";
                        if(!is_null($_GET['lieu'])) $sql_sim.=" AND si.lieu=".$_GET['lieu'];
                        $r=$DB_site2->query($sql_sim);
                        if($DB_site2->num_rows($r) > 0){
                            while($data=$DB_site2->fetch_array($r)){
                                $item->qte+=1;
                                $paht_r1_sim=$r1[$data['artid']]->paht-$r3[$data['artid']]->paht;
                                $paht_r0_sim=$r0[$data['artid']]->paht;
                                $item->artcode=$data['artcode'];
                            }
                        }
               
                        $sql_artcode_sim="SELECT artids,mini,alert,delai FROM stock_mini sm WHERE artid=".$stock->is_similaire($item->artid)." AND (artids LIKE '%,".$item->artid."%' OR artids LIKE '%".$item->artid.",%' OR artids = '".$item->artid."')".(!is_null($_GET['lieu'])?' AND lieu ='.$_GET['lieu']:'');
                        $r=$DB_site2->query($sql_artcode_sim);
                        if($DB_site2->num_rows($r)>0){
                            while($a=$DB_site2->fetch_array($r)){
                                if($a['artids']!==''){
                                    $spartid=explode(",",$a['artids']);
                                }
                                $item->mini=$a['mini'];
                                $item->delai=$a['delai'];
                                $item->alert=$a['alert'];
                            }
                            $r=$DB_site2->query("SELECT artid,artcode FROM article WHERE artid IN ('".implode("','",$spartid)."')");
                                while($a=$DB_site2->fetch_array($r)) $sim_artcodes.='<br>'.$a['artcode']; 
                                $simi_principal = $stock->is_similaire($item->artid);
                            }
                    }



                    $totalligne=($r1[$item->artid]->paht+$paht_r1_sim)-($r0[$item->artid]->paht+$paht_r0_sim)-$r3[$item->artid]->paht;
                    ($totalligne<0?$totalligne=0:'');
                    $totalqte+=($item->qte-$r3[$item->artid]->qte<0?0:$item->qte-$r3[$item->artid]->qte);
                    $total+=$totalligne;
                    $totalmarque[$item->marque]+=$totalligne;
                    $qtemarque[$item->marque]+=($item->qte-($r0[$item->artid]->qte-$qte_r0_sim)-$r3[$item->artid]->qte<0?0:$item->qte-($r0[$item->artid]->qte-$qte_r0_sim)-$r3[$item->artid]->qte);
                    $totaltype[$item->typearticleid]+=$totalligne;
                    $qtetype[$item->typearticleid]+=($item->qte-($r0[$item->artid]->qte-$qte_r0_sim)-$r3[$item->artid]->qte<0?0:$item->qte-($r0[$item->artid]->qte-$qte_r0_sim)-$r3[$item->artid]->qte);
                    $nclist=$nclist0="";

                    $noncommande=$item->cmd_non_reserve(array(1,7),null,$noncommandenb,$simi_principal);
                    foreach($noncommande as $noncmdid)
                        $nclist.="<br><a href=\"facture.php?action=editer&facturid=".$noncmdid."\" target=\"_blank\">".$noncmdid."</a>";

                    $noncommande0nb=0;
                    $noncommande0=$item->cmd_non_reserve(array(0),array(2,4,5,14,17,18,99),$noncommande0nb,$simi_principal);
                    foreach($noncommande0 as $noncmdid){
                        $nclist0.="<br><a href=\"facture.php?action=editer&facturid=".$noncmdid."\" target=\"_blank\">".$noncmdid."</a>";
                    }

                    $qte12m=$item->ventes($delai12m,$simi_principal);
                    $qte6m=$item->ventes($delai6m,$simi_principal);
                    $qte3m=$item->ventes($delai3m,$simi_principal);
                    $qte1m=$item->ventes($delai1m,$simi_principal);
                    $tdqtecde=$tddatecde="";
                    $cmds=$item->commandes($_GET['lieu']);
                    $plus_en_reappro=0;

          ///pour les commandes sur affaire, mettre le n° de facture
                    if(!$cmds){
                        $nb_dest_enlev=0; //-> si cmd sur affaire, on va compter le nb de cmd qui ont un dest. Car sinon, ça va compter des cmd en réappro alors que ni date ni dest associés
                        $dest_facs=$DB_site2->query("SELECT dest, origin, count(*) as qte FROM stock_item si WHERE artid =".$item->artid." AND stock_cmd_id=0 AND state != 3 AND state != 2 AND dest != '' GROUP BY dest");
                        //on récupère les cmd associées :
                        while($dest_fac=$DB_site2->fetch_array($dest_facs)){
                            if(substr($dest_fac['dest'],0,7)=="facture") $facid=substr($dest_fac['dest'],8);
                            if(!is_null($facid)){
                                $envoi_lignes=$DB_site2->query("SELECT outdate, efl.qte, enlevement,livraison,ef.type FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid INNER JOIN envoi_fournisseur ef ON ef.id = efl.efid WHERE lf.factureid = ".$facid." AND lf.artid =".$item->artid." AND (ef.type='fourn' OR ef.type='stock') GROUP BY efl.qte ORDER BY enlevement DESC");
                                $nb_enlev = 0;
                                while($envoi_ligne=$DB_site2->fetch_array($envoi_lignes)){
                               //si la commande a été livrée, elle ne doit plus apparaitre en réappro. Sinon, on l'affiche bien :
                                    if($envoi_ligne['type']=='fourn'){
                                        if($envoi_ligne['livraison']==0){
                                            $nb_enlev++;
                                            $nb_dest_enlev++;
                                            $tdqtecde.="<br>".$envoi_ligne['qte'];
                                            $tddatecde.="<p><a href=\"facture.php?action=editer&facturid=".$facid."\" target=\"_blank\">".$facid."</a><br>".($envoi_ligne['outdate']>0?date("d/m/Y",$envoi_ligne['outdate']):"?")."</p>";
                                        }
                                    }
                                    else{
                                        if($envoi_ligne['enlevement']==0){
                                            $nb_enlev++;
                                            $nb_dest_enlev++;
                                            $tdqtecde.="<br>".$envoi_ligne['qte'];
                                            $tddatecde.="<p><a href=\"facture.php?action=editer&facturid=".$facid."\" target=\"_blank\">".$facid."</a><br>".($envoi_ligne['indate']>0?date("d/m/Y",$envoi_ligne['indate']):"?")."</p>";
                                        }
                                    }
                                }
                            }
                            $plus_en_reappro+=$dest_fac['qte']-$nb_enlev;
                        }
                    }

                    foreach($cmds as $cmd){
                      $tdqtecde.="<br>".$cmd->qte;
                      $tddatecde.="<p><a href=\"stock.php?action=cmd&stock_cmd_id=".$cmd->id."\" target=\"_blank\">".$cmd->id."</a><br>".($cmd->date_livraison>0?date("d/m/Y",$cmd->date_livraison):"?")."</p>";
                  }
                  $flist="";
                  if(intval($r2[$item->artid]->qte+$qte_r2_sim)>0){
                      $cmdlist=$stock->getdest($item->artid,$_GET['lieu'],2,($sim_artids?implode(',',$sim_artids):null));  
                      foreach($cmdlist as $facturid) $flist.="<br><a href=\"facture.php?action=editer&facturid=".$facturid."\" target=\"_blank\">".$facturid."</a>";
                  }

                  $qtevirtuel=$item->qte-$noncommandenb-$noncommande0nb-($r2[$item->artid]->qte+$qte_r2_sim)-$r3[$item->artid]->qte-$plus_en_reappro;

                  $stocklieu=array();
                  $stocklieutxt="";
                  if(is_null($_GET['lieu'])){
                      $search=$stock->search($item->artid,null,null,array(1,2));
                      foreach($search as $id=>$si){
                        if($si->stock_cmd_id!=0&&$si->lieu==''){
                          $lieu_stock=$DB_site2->query_first("SELECT livraison FROM stock_cmd WHERE id='".$si->stock_cmd_id."'");
                          $stocklieu[$lieu_stock['livraison']]+=1;
                        }
                        else{
                            $stocklieu[$si->lieu]+=1;
                        }
                      }

                      foreach($stocklieu as $lieu=>$nb){
                        if(!empty($item->similaire)){
                            foreach($item->similaire as $key_sim => $val_sim){
                                $arts_simi=$stock->stockbyartid_sim($key_sim,$lieu,array(1,2));
                                $nb+=$arts_simi['qte'];
                            }
                        }
                        // $stocklieutxt.="<br>".strstr($lieux[$lieu]," ",true).":$nb";
						$stocklieutxt.="<br>".$lieux[$lieu].":$nb";
                    }
                }

                $delailivraison=delailivraison($item->artid);
                $jourlivraison = mktime(0,0,0)+(86400*$delailivraison['delailivraison']);

                $trcolor=is_null($item->alert)?"#FFFABF":"#FFAAAA";

                $liste_marque_lieu[]=$item->marque;


                if($qtevirtuel==0&&($r2[$item->artid]->qte+$qte_r2_sim)==0&&is_null($item->alert)&&(($item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte)<0?0:($item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte))==0){
                    //si stock sur affaire et aucune commande en cours, on n'affiche pas la ligne
                        continue;
                }
                else{


                $tr[$item->marque][$item->artcode]="<tr".($qtevirtuel<=$item->alert?" style=\"background-color:".$trcolor.";\"":"").">
                <td><img src=\"".$item->img."\" width=\"50\"></td>
                <td><input type=\"hidden\" id=\"lib_".$item->artid."\" value=\"".$item->ref." ".$item->marque." ".$item->libelle."\"><input type=\"hidden\" id=\"paht_".$item->artid."\" value=\"".$item->artpaht."\"><input type=\"hidden\" id=\"ecopartht_".$item->artid."\" value=\"".$item->artecopartht."\"><a href=\"/admin/ajout_produit.php?artid=".$item->artid."&prec=modif\" target=\"_blank\">".$item->artcode."</a><br><i>".($sim_artcodes?$sim_artcodes:implode("<br>",$item->similaire))."</i><br>".$item->ref."</td>
                <td> <p style=\"".(intval($item->active)==3&&($item->qte-($r0[$item->artid]->qte+$qte_r0_sim))>0?'color:red;':'')."\">Actif :<br>".$articleActif[intval($item->active)]."</p><p style=\"white-space: nowrap;\"> Commandable :<br>".$commandable[intval($item->commandable)]."</p><p>Délai: ".$item->delailivraison."j</p></td>
                <td class='libel'>".$item->libelle."<br>".$item->marque."</td>
                <td class=\"center\"><span style=\"font-weight:bold;display:block;margin-bottom:-5px;\">".(($item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte)<0?0:($item->qte-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte))."</span>".$stocklieutxt."</td>
                <td class=\"center\">".$noncommandenb.$nclist."</td>
                <td class=\"center\">".intval($r2[$item->artid]->qte+$qte_r2_sim).$flist."</td>
                <td class=\"center\">".(($item->qte-$noncommandenb-($r2[$item->artid]->qte+$qte_r2_sim)-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte)<0?0:($item->qte-$noncommandenb-($r2[$item->artid]->qte+$qte_r2_sim)-($r0[$item->artid]->qte+$qte_r0_sim)-$r3[$item->artid]->qte))."</td>
                <td class=\"center\">".$noncommande0nb.$nclist0."</td>
                <td class=\"center\"><b>".(!$cmds?$nb_dest_enlev:intval($r0[$item->artid]->qte+$qte_r0_sim-$plus_en_reappro))."</b>".$tdqtecde."</td>
                <td class=\"center\">&nbsp;$tddatecde</td>
                <td class=\"center\">".$qtevirtuel."</td>
                <td class=\"center\">".number_format($totalligne,2,',','')."</td> 
                <td><input type=\"checkbox\" name=\"cmdartid\" value=\"".$item->artid."\"></td>
                <td class=\"center\"><input type=\"hidden\" name=\"mini_".$item->artid."\" value=\"".$item->mini."\">".$item->alert."</td>
                <td class=\"center\">".$item->mini."</td>
                <td class=\"center\">".$item->delai."</td>
                <td class=\"center\">".$qte12m." / ".($qte12m>0?round(365/$qte12m,1):"N/C")."</td>
                <td class=\"center\">".$qte6m." / ".($qte6m>0?round(182/$qte6m,1):"N/C")."</td> 
                <td class=\"center\">".$qte3m." / ".($qte3m>0?round(91/$qte3m,1):"N/C")."</td>
                <td class=\"center\">".$qte1m." / ".($qte1m>0?round(31/$qte1m,1):"N/C")."</td>
                </tr>";
            }
        }
      }
  }
        ksort($tr);
        

        $requete=$DB_site2->query("SELECT id,societe,mail FROM fournisseur WHERE type='fournadv' ORDER BY societe");
        while($fourn=$DB_site2->fetch_array($requete)) $group[$fourn['societe']][]=$fourn['mail'];

        foreach($group as $societe=>$mails) $optfourn.="<option value=\"".implode(";",$mails)."\" onclick=\"document.getElementsByName('fournisseur')[0].value='".addslashes($societe)."';\">".$societe."</option>";
     
        $lieuopt="<option value=\"\"></option>";
        $lieuopt2="<option value=\"\">Tout</option>";
        $marqueopt="<option value=\"\"></option>";
        $marqueopt2="<option value=\"\">Tout</option>";
        
        foreach($lieux as $id=>$lieu){
          if(is_null($_GET['lieu']) || $_GET['lieu']==$id) $lieuopt.="<option value=\"$id\">$lieu</option>";
          $lieuopt2.="<option value=\"$id\"".($_GET['lieu']==$id?" selected":"").">$lieu</option>";
        }

        $liste_marque_lieu=array_unique($liste_marque_lieu);
        asort($liste_marque_lieu);
        foreach($liste_marque_lieu as $marque){
          if(is_null($_GET['marque']) || $_GET['marque']==$marque) $marqueopt.="<option value=\"".$marque."\">".$marque."</option>";
          $marqueopt2.="<option value=\"".$marque."\"".($_GET['marque']==$marque?" selected":"").">".$marque."</option>";
        }
            print "$msg
            <table id=\"stock_table\">";
        foreach($tr as $marque=>$artcodes){
          ksort($artcodes);
          if((isset($_GET['marque']) && $_GET['marque']==$marque) || empty($_GET['marque'])){
          print "<tr>
            <td name=\"#tr$marque\" colspan=\"21\" style=\"text-align:center;background-color:#CCCCCC;\">".$marque."</td></tr>
            <tr style=\"font-weight:bold;text-align:center;\">
            <td></td>
            <td>Ref acilyon<br>constructeur</td>
            <td>Etat</td>
            <td class='libel'>Libellé</td>
            <td>Qté stock physique</td>
            <td>Qté non réservé</td>
            <td>Qté réservé</td>
            <td>Qté réel instantané</td>
            <td>Qté non validé</td>
            <td>Qté en réappro</td>
            <td>Cmd<br>/Date réappro</td>
            <td>Qté stock virtuel</td>
            <td>Valeur HT Stock</td>
            <td></td>
            <td>Qté mini</td>
            <td>Qté réappro</td>
            <td>Nb jours réappro</td>
            <td>Sur 1 an<br>qte/moy. jour</td>
            <td>Sur 6 mois<br>qte/moy. jour</td>
            <td>Sur 3 mois<br>qte/moy. jour</td>
            <td>Sur 1 mois<br>qte/moy. jour</td>
              </tr>".implode("",$artcodes)."
              <tr>
            <td colspan=\"4\" align=\"right\">Total :</td>
            <td class=\"center\">".$qtemarque[$marque]."</td>
            <td colspan=\"7\"></td>
            <td class=\"center\">".number_format($totalmarque[$marque],2,',','')."</td>
            <td></td>
              </tr>
              <tr>
            <td colspan=\"14\" align=\"right\"><input type=\"button\" value=\"Commander\" onclick=\"stock_cmd_open()\"></td>
              </tr>
              <tr><td colspan=\"12\" class=\"bottom\"></td></tr>";
        }
    }
        print "</table>";
        
        
        print "<a href=\"/admin/fichier_stock_plateforme.php?".($_GET['lieu']!=''?"lieu=".$_GET['lieu']:'').($_GET['marque']!=''?"&marque=".$_GET['marque']:'')."\">Télécharger fichier stock excel</a>";
        
        

        ksort($totalmarque);
        print "<table class=\"totaltable\">
        <thead>
        <tr><td>Marque</td><td>Qte</td><td>PA HT</td></tr>
        </thead>";
        foreach($totalmarque as $marque=>$total)
            print "<tr>
              <td>".$marque."</td>
              <td>".$qtemarque[$marque]."</td>
              <td>".number_format($total,2,',',' ')."</td>
            </tr>";
        print "<tr>
          <td><b>Total</b></td>
          <td>".array_sum($qtemarque)."</td>
          <td>".number_format(array_sum($totalmarque),2,',','')."</td>
        </tr>
        </table>";
        
        $r=$DB_site2->query("SELECT typearticleid, libelle FROM typearticle");
        while($typearticle=$DB_site2->fetch_array($r)) $types[$typearticle['typearticleid']]=$typearticle['libelle'];
        
        ksort($totaltype);
        print "<table class=\"totaltable\">
        <thead>
        <tr><td>Type</td><td>Qte</td><td>PA HT</td></tr>
        </thead>";
        foreach($totaltype as $typearticleid=>$total)
            print "<tr>
              <td>".$types[$typearticleid]."</td>
              <td>".$qtetype[$typearticleid]."</td>
              <td>".number_format($total,2,',',' ')."</td>
            </tr>";
        print "<tr>
          <td><b>Total</b></td>
          <td>".array_sum($qtetype)."</td>
          <td>".number_format(array_sum($totaltype),2,',',' ')."</td>
        </tr>
        </table>";
    break;

    case "listcmd":
    $state=0;
    $annee=date('Y');
    $from=mktime(0,0,0,01,01,date('Y'));
    $to=mktime(22,59,59,12,31,date('Y'));
    if(isset($_GET['state'])) $state=$_GET['state'];
    if(isset($_GET['annee'])){
            $from=mktime(0,0,0,01,01,$_GET['annee']);
            $to=mktime(22,59,59,12,31,$_GET['annee']);
            $annee=$_GET['annee'];
    } 
    $showsav=isset($_GET['sav']);
    
    if($_GET['orderby'] == "n_cde"){
        $sql_tri = "cmd.id";
        $sql_sens = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
        $fleche_n = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? '▼' : '▲';
    }
    else if($_GET['orderby'] == "date_cde"){
        $sql_tri = "cmd.date";
        $sql_sens = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
        $fleche_date = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? '▼' : '▲';

    }
    else if($_GET['orderby'] == "date_livr_prev"){

        $sql_tri = "item.date_livraison";
        $sql_sens = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
        $fleche_datelivr_prev = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? '▼' : '▲';
    }
    else if($_GET['orderby'] == "date_livr_souhait"){

        $sql_tri = "cmd.date_livraison";
        $sql_sens = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? 'DESC' : 'ASC';
        $fleche_datelivr = isset($_GET['sensorder']) && $_GET['sensorder'] == 'ASC' ? '▼' : '▲';
    }  
    else {
        $sql_tri = "cmd.id";
        $sql_sens = 'DESC';
        $fleche_n = $fleche_datelivr = $fleche_date = $fleche_datelivr_prev = "";
    }
             

    $stock_cmd=new stock_cmd($DB_site2);

    $r=$stock_cmd->getcmd($state,null,$from,$to,$sql_tri,$sql_sens,'listcmd');

              $params_exclusion = array('sensorder', 'orderby');
              $debut_url = '';
          foreach($_GET as $param => $value){
               if(!in_array($param, $params_exclusion)){
                    $debut_url .= empty($debut_url) ? '?' : '&';
                    $debut_url .= $param.'='.$value;
               }
          }

 
    
    include("sav.class.php");
    $sav=new sav($DB_site2);
    
   
    print $menu;
    print "<div class=\"commande_detail\">
    <div style=\"padding:10px;\">
        <p>Numéro de commande : <input type=\"text\" id=\"srccdmdid\" onkeyup=\"if(event.keyCode==13)search_cmdid(this.value)\"> <img src=\"images/rechercher.gif\" style=\"cursor:pointer; vertical-align:middle;\" onclick=\"search_cmdid(document.getElementById('srccdmdid').value)\"></p>
        <p>
            Trier les articles : <select onchange=\"document.location='stock.php?action=listcmd&state='+this.value+'&annee=$annee'\">
                    <option value=\"-1\"".($state=="-1"?" selected":"").">Non commandé</option>
                    <option value=\"0\"".($state=="0"?" selected":"").">En attente de livraison</option>
                    <option value=\"1,2,3\"".($state=="1,2,3"?" selected":"").">Livrés</option>
                    <option value=\"-2,-1,0,1,2,3\"".($state=="-1,0,1,2,3"?" selected":"").">Tous</option>
                    <option value=\"-2\"".($state=="-2"?" selected":"").">Annulé</option>
            </select>
            <input type=\"checkbox\" onclick=\"document.location='stock.php?action=listcmd&state=-1,0,1,2,3'+(this.checked?'&sav=1':'')\"".(isset($_GET['sav'])?" checked":"")."> SAV
        </p>";
        
        $filters=array();
        
        foreach($r as $scmd) $filters[$scmd['destinataire']]++;
        ksort($filters);
        print "<p>Fournisseur : <select onchange=\"document.location='stock.php?action=listcmd&state=$state&annee=$annee&filter='+this.value\"><option value=\"\">Tous</option>";
        foreach($filters as $filter=>$qte) print "<option value=\"".$filter."\"".($_GET['filter']==$filter?" selected":"").">".$filter." (".$qte.")</option>";
        print "</select> &nbsp; Période : <select onchange=\"document.location='stock.php?action=listcmd&state=$state&annee='+this.value\">";
        for($i=date('Y');$i>=2010;$i--) print "<option value=\"".$i."\"".($_GET['annee']==$i?" selected":"").">".$i."</option>";
        print "</select></p>
    </div>
    </div>";

    print "<table class=\"table_factures\">
    <tr>
    <td class=\"td_head_factures\"><a href=\"".$debut_url."&orderby=n_cde&sensorder=".$sql_sens."\">Cde ".$fleche_n."</a></td>
    <td class=\"td_head_factures\"><a href=\"".$debut_url."&orderby=date_cde&sensorder=".$sql_sens."\">Date de cmd ".$fleche_date."</a></td>
    <td class=\"td_head_factures\"><a href=\"".$debut_url."&orderby=date_livr_souhait&sensorder=".$sql_sens."\">Date de livraison souhaitée".$fleche_datelivr."</a></td>
    <td class=\"td_head_factures\"><a href=\"".$debut_url."&orderby=date_livr_prev&sensorder=".$sql_sens."\">Date de livraison prévue".$fleche_datelivr_prev."</a></td>
    <td colspan=\"2\" class=\"td_head_factures\">Articles</td>
    <td class=\"td_head_factures\">Transport</td>
    <td class=\"td_head_factures\">Etat</td>
    <td></td>
    </tr>";
    
$alt=1;
foreach($r as $scmd){

    $articles=$articles2=$date_livr="";
    if(!isset($_GET['filter']) || $_GET['filter']==$scmd['destinataire'] || $_GET['filter']==""){
        $trspt=array();
        
        $item_liste = new stock_cmd($DB_site2,$scmd['id']);
        $item_liste->items();
        $item_liste->groupitems2();
        foreach($item_liste->items as $item){
            if($item->date_livraison == $scmd['itemdate']) {
                $articles.=$item->qte." ".$item->artcode."<br>";
                $articles2.=$item->qte." ".$item->ref."<br>";
            }
            $transporteur=$item->trspt;
            if($item->trspt=="" && $item->date_livraison>0) $transporteur="Fournisseur";
            $trspt[$transporteur]=$transporteur;
            $date_livr = $item->date_livraison;
        }

        $savicon="";
        $savlist=$sav->get_from_facturid($scmd['id']);
        if($savlist!=false){
            foreach($savlist as $savcmd)
                if($savcmd->date_fin==0) $savicon.=" <a href=\"stock.php?action=cmd&stock_cmd_id=".$scmd['id']."&savid=".$savcmd->id."#sav\"><img src=\"images/sav.png\" align=\"absmiddle\"></a>";
        }elseif($showsav)
        continue;

        //$transporteur=$scmd['trspt'];
        //if($scmd['trspt']=="" && $scmd['itemdate']>0) $transporteur="Fournisseur";
        $state = new stock_cmd($DB_site2,$scmd['id']);
        
        print "<tr>
        <td class=\"td_factures_alt$alt\">".$scmd['id']."<br>".$scmd['destinataire']."</td>
        <td class=\"td_factures_alt$alt\">".date("d/m/Y H:i",$scmd['date'])."<br>".$scmd['utilisateur']."</td>
        <td class=\"td_factures_alt$alt\">".date("d/m/Y",$scmd['date_livraison'])."</td>
        <td class=\"td_factures_alt$alt\">".($scmd['itemdate'] ? date("d/m/Y",$scmd['itemdate']) : '')."</td>
        <td class=\"td_factures_alt$alt\">".($articles == '' ? 'annulé' : $articles)."</td>
        <td class=\"td_factures_alt$alt\">".($articles2 == '' ? 'annulé' : $articles2)."</td>
        <td class=\"td_factures_alt$alt\">".implode("<br>",$trspt)."</td>
        <td class=\"td_factures_alt$alt\">".$state->statetxt[$state->state()].$savicon."</td>
        <td class=\"td_factures_alt$alt\"><a href=\"stock.php?action=cmd&stock_cmd_id=".$scmd['id']."\">Editer</a></td>
        </tr>";

    }
    
     $alt=($alt==1?2:1);
    }
    print "</table>";
    break;

    case "cmd":
    $stock_cmd=new stock_cmd($DB_site2,$_GET['stock_cmd_id']);

    if(is_null($stock_cmd->id) || $stock_cmd->id==0){
        print "<p>Cette commande n'existe pas<br><a href=\"stock.php?action=listcmd\">Retour</a></p>";
        break;
    }

    if(isset($_GET['datelivr'])){
        $suivi=new stock_suivi($DB_site2);
        $suivi->stock_cmd_id=$stock_cmd->id;
        $suivi->commercial=$user_info['username'];
        $suivi->suivi="Modification date livraison souhaitée : ".date("d/m/Y",$stock_cmd->date_livraison)." &gt; ".date("d/m/Y",$_GET['datelivr']);
        $suivi->create();
        $stock_cmd->date_livraison=$_GET['datelivr'];
        $stock_cmd->update();
    }
    print $menu;

    $fourn=$fournname=array();
    $list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='trsptadv' ORDER BY societe");
    while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
      $groups=explode(";",$advf['groupes']);
      foreach($groups as $group){
        $fourn[$group][]=$advf['mail'];
        $fournname[$group]=$advf['societe'];
     }
    }else{
      $fourn[$advf['societe']][]=$advf['mail'];
      $fournname[$advf['societe']]=$advf['societe'];
    }
    }

    $opttrspt="<option></option>";
    foreach($fourn as $group=>$mails){
        $mails=array_unique($mails);
        $mailcsv=implode(";",$mails);
        $opttrspt.="<option value=\"$mailcsv\" onclick=\"document.getElementsByName('transporteur')[0].value='".addslashes($fournname[$group])."';\">".ucfirst($group)."</option>";
    }

    print "<div id=\"stock_cmd\">
      <form name=\"trsptcmd\" method=\"post\">
      <input type=\"hidden\" name=\"cmdid\">
      <input type=\"hidden\" name=\"transporteur\">
      <div style=\"width:50%;float:left;\">
    Enlèvement :
    <p id=\"enlevmenttxt\"></p>
      </div>
      <div style=\"width:50%;float:left;\">
    Livraison :
    <p id=\"livraisontxt\"></p>
      </div>
      <div style=\"clear:both;\"></div>
      <p>Transporteur : <select name=\"mails\">$opttrspt</select></p>
      <p>Date d'enlevement : <input type=\"text\" id=\"indate\" name=\"indate\" onclick=\"calendrier_show(this.id,this.value)\" size=\"10\" readonly></p>
      <p>Date de livraison : <input type=\"text\" id=\"outdate\" name=\"outdate\" onclick=\"calendrier_show(this.id,this.value)\" size=\"10\" readonly></p>
      <p>Tarif : <input type=\"text\" name=\"tarif\" size=\"5\"></p>
      <div class=\"cmd_btn\"><input type=\"button\" value=\"OK\" onclick=\"createtrsptmail()\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('stock_cmd').style.display='none';\"></div>
      </form>
    </div>";

    print "<table class=\"table_modules_boutiques\">
    <tr style=\"font-weight:bold;text-align:center;\">
      <td>Commande #CDE".$stock_cmd->id."#</td>
      <td>Etat</td>
      <td>SAV</td>
    </tr>
    <tr>
      <td style=\"border-right:solid thin #CCCCCC;\">Du ".date("d/m/Y à H:i",$stock_cmd->date)."<br>par ".$stock_cmd->utilisateur."<br><b>Date livraison souhaitée ".date("d/m/Y",$stock_cmd->date_livraison)."</b> <img align=\"absmiddle\" onclick=\"calendrier_show(undefined,'".date("d/m/Y",$stock_cmd->date_livraison)."',false,changedatelivr);\" title=\"Modifier\" src=\"images/editer.gif\"></td>
      <td style=\"border-right:solid thin #CCCCCC;\">".$stock_cmd->statetxt[$stock_cmd->state()]."</td>
      <td style=\"border-right:solid thin #CCCCCC;\">
    <form name=\"newsav\" method=\"get\" style=\"position:absolute;background:white;border:solid thin black;display:none;\">
    <input type=\"hidden\" name=\"action\" value=\"cmd\">
    <input type=\"hidden\" name=\"facturid\" value=\"".$stock_cmd->id."\">
    <input type=\"hidden\" name=\"stock_cmd_id\" value=\"".$stock_cmd->id."\">
    <input type=\"hidden\" name=\"savid\" value=\"0\">
    Type de SAV : <select name=\"typelitige\">
      <option value=\"0\">Une avarie de transport</option>
      <option value=\"2\">Une erreur de livraison</option>
      <option value=\"4\">Un manque de produit</option>
    </select>
    <input type=\"submit\" value=\"OK\">
    </form>
    <input type=\"button\" onclick=\"document.forms.newsav.style.display='block';\" value=\"Nouveau SAV\">";
    
    $listesav=$DB_site2->query("SELECT * FROM sav WHERE facturid=".$stock_cmd->id);
    while($sav=$DB_site2->fetch_array($listesav)){
      $savtype=$DB_site2->query_first("SELECT * FROM sav_fields WHERE name=\"sav_typelitige\" AND savid=".$sav['id']);
      switch($savtype['value']){
      case 0:
      $ltype="transport/installation";
      break;
      case 2:
      $ltype="erreur produit";
      break;
      case 4:
      $ltype="manque produit";
      break;
      }
      print "<br><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&savid=$sav[id]#sav\" ".($sav['fin']>0?"style=\"color:green;\"":"").">SAV N° SAV$stock_cmd->id-$sav[id] du ".date("d/m/Y",$sav['date'])."($ltype) ".($sav['fin']>0?"cloturé":"")."</a>";
    }
      print "</td>
    </tr>
    </table>
    <br>";

    $livraison=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
    $enlevement=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->enlevement);

    print "<table class=\"table_modules_boutiques\">
    <tr><td colspan=\"2\" style=\"text-align:center;font-weight:bold;\">Coordonnées de livraison</td><td colspan=\"2\" style=\"text-align:center;font-weight:bold;\">Coordonnées fournisseur</td></tr>
    <tr><td>Mail :</td><td>".$livraison['mail']."</td><td>Mail :</td><td>".$enlevement['mail']."</td></tr>
    <tr><td>Raison sociale :</td><td>".$livraison['societe']."</td><td>Raison sociale :</td><td>".$enlevement['societe']."</td></tr>
    <tr><td>Nom :</td><td>".$livraison['nom']."</td><td>Nom :</td><td>".$enlevement['nom']."</td></tr>
    <tr><td>Prénom :</td><td>".$livraison['prenom']."</td><td>Prénom :</td><td>".$enlevement['prenom']."</td></tr>
    <tr><td>Adresse :</td><td>".$livraison['adresse1']."<br>".$livraison['adresse2']."</td><td>Adresse :</td><td>".$enlevement['adresse1']."<br>".$enlevement['adresse2']."</td></tr>
    <tr><td>CP :</td><td>".$livraison['cp']." ".$livraison['ville']."</td><td>CP :</td><td>".$enlevement['cp']." ".$enlevement['ville']."</td></tr>
    <tr><td>Pays : </td><td>".$livraison['libelle']."</td><td>Pays : </td><td>".$enlevement['libelle']."</td></tr>
    <tr><td>Téléphone :</td><td>".epigy($livraison['telephone'])." ".epigy($livraison['portable'])."</td><td>Téléphone :</td><td>".epigy($enlevement['telephone'])." ".epigy($enlevement['portable'])."</td></tr>
    <tr><td></td><td></td><td><span class=\"fileboxbutton\" onclick=\"fb.show('fournisseur_files.php?fournisseur=".trim(addslashes($stock_cmd->destinataire))."&cmdid=".$stock_cmd->id."&action=stock&parent=0');\"><img src=\"images/icon/pdf.png\"> Voir les fichiers joints</span></td></tr>
    </table>
    <input type=\"hidden\" id=\"enlevement_".$stock_cmd->id."\" value=\"".htmlentities($enlevement['societe']."<br>".$enlevement['nom']." ".$enlevement['prenom']."<br>".$enlevement['adresse1'].($enlevement['adresse2']!=""?"<br>".$enlevement['adresse2']:"")."<br>".$enlevement['cp']." ".$enlevement['ville']."<br>".$enlevement['pays'],ENT_COMPAT,"ISO-8859-15")."<br>Tel :".$enlevement['telephone']."<br>".$enlevement['horaires']."\">
    <input type=\"hidden\" id=\"livraison_".$stock_cmd->id."\" value=\"".htmlentities($livraison['societe']."<br>".$livraison['nom']." ".$livraison['prenom']."<br>".$livraison['adresse1'].($livraison['adresse2']!=""?"<br>".$livraison['adresse2']:"")."<br>".$livraison['cp']." ".$livraison['ville']."<br>".$livraison['pays'],ENT_COMPAT,"ISO-8859-15")."<br>Tel :".$livraison['telephone']."<br>".$livraison['horaires']."\">
    <br>";

    print "<table class=\"table_modules_boutiques\">
    <tr><td colspan=\"9\" style=\"font-weight:bold;text-align:center;background-color:#FF7676;\">Détail des produits commandés</td></tr>
        <tr style=\"text-decoration:underline;text-align:center;background-color:#FFACAC;\"><td>Référence</td><td>Libellé</td><td>Quantité</td><td>Prix</td><td>Date souhaitée</td><td colspan=\"4\"></td></tr>";
    
    $stock_cmd->items();
    //$stock_cmd->groupitems();
    $stock_cmd->groupitems2(); //groupitems() modifié
    $i = 1;
    $annul = 0;
    $cancel = 0;
    
    //Détails des produits commandés
    foreach($stock_cmd->items as $item){
    $validform="<input type=\"checkbox\" name=\"chk_".$item->artid."\" value=\"1\" onclick=\"document.getElementById('detail_".$item->artid."').style.display=(this.checked?'inline':'none');\"> <span id=\"detail_".$item->artid."\" style=\"display:none\">qté : <input type=\"text\" name=\"qte_".$item->artid."\" value=\"".$item->qte."\" size=\"1\"><!--<br>rayon : <input type=\"text\" name=\"rayon_".$item->artid."\">--></span>";
    $modifid="modiflivr".$item->stock_cmd_id.$item->artid;
    if($item->state[0]==0){
        $validform=($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"");
        $tdlivraison="<td align=\"center\"><span id=\"livraisonLigneStock".$i."\">".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."</span><input id=\"dateLigneStock".$i."\" name=\"dateLigneStock".$i."\" onclick=\"calendrier_show(this.id,this.value);\" size=\"8\" type=\"text\" placeholder=\"".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."\" style=\"display:none;\"><a href=\"javascript:cal_delete('dateLigneStock".$i."');\" class=\"cal_delete\" id=\"xLigneStock".$i."\" style=\"display:none;\"> X&nbsp;</a></td>";
    }else
        $tdlivraison="<td align=\"center\"><span id=\"livraisonLigneStock".$i."\">".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."</span><input id=\"dateLigneStock".$i."\" name=\"dateLigneStock".$i."\" onclick=\"calendrier_show(this.id,this.value);\" size=\"8\" type=\"text\" placeholder=\"".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."\" style=\"display:none;\"><a href=\"javascript:cal_delete('dateLigneStock".$i."');\" class=\"cal_delete\" id=\"xLigneStock".$i."\" style=\"display:none;\"> X&nbsp;</a></td>";
        

    print "<tr>
                <td style=\"visibility:hidden;display:none;\">
                    <input type=\"text\" value=\"".$stock_cmd->id.":".$item->artid.":".$item->date_livraison.":".$item->pu.":".$item->qte."\" name=\"idLigne".$i."\" style=\"display:none\" readonly>
                </td>
                <td style=\"text-align:center;font-weight:bold;\">".$item->artcode."<br>".$item->ref."</td>
                <td align=\"center\">".$item->libelle."</td>
                <td align=\"center\"><input type=\"number\" value=\"".$item->qte."\" name=\"qteLigne".$i."\" min=\"0\" style=\"width:30%;\" id=\"qteLigne".$i."\" disabled></td>
                <td align=\"right\"><input type=\"text\" value=\"".number_format($item->paht,2,',','')."\" name=\"prixLigne".$i."\" size=\"6\" id=\"prixLigne".$i."\" disabled>&euro;<span style=\"font-size:10px;\">".($item->ecopartht>0?"<br>".number_format($item->ecopartht,2,',','')."&euro;":"")."</span></td>
                ".$tdlivraison."
                <td colspan=\"2\">
                    <a href=\"javascript:void(0)\" id=\"modifLigneStock".$i."\"  onclick=\"ligneStockClick('edit', ".$i.")\" style=\"display:block; ".($item->state[-1] == null ? "pointer-events:none;cursor: default;" : "color:#007df6;")."\"><i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i></a> 
                    <a href=\"javascript:void(0)\" id=\"validLigneStock".$i."\"  onclick=\"ligneStockClick('valid', ".$i.")\" style=\"display:none;color:#00b300;\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>
                </td>
                <td colspan=\"2\">
                    <a href=\"javascript:void(0)\" id=\"cancelLigneStock".$i."\" onclick=\"ligneStockClick('cancel', ".$i.")\" style=\"display:none;color:#b30000;\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a> 
                    <a href=\"javascript:void(0)\" id=\"suppLigneStock".$i."\"   onclick=\"ligneStockClick('delete', ".$i.")\" style=\"display:block; ".($item->state[-1] == NULL ? "pointer-events:none;cursor: default;" : "color:#007df6;")."\"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a>
                </td>
            </tr>";
            $i++;
            if($item->state[-2] || $item->state[0] || $item->state[1] || $item->state[2] || $item->state[3]) $annul = 1;
            //if($item->state[-2]) $cancel = 1;
        }
        
        if($stock_cmd->tariftrspt>0) print "<tr><td colspan=\"4\" align=\"right\">Frais de port :".number_format($stock_cmd->tariftrspt,2,',','')."&euro;</td></tr>";

        //Bouton commande
        if($annul != 1){            
        echo "<tr>
                <td colspan=\"6\" align=\"center\">
                    <br>
                    <input type=\"submit\" value=\"Ajouter un article\" onclick=\"showStock('add')\" > 
                    <input type=\"button\" value=\"Annuler la commande\" onclick=\"showStock('cancel')\" >
                </td>
            </tr>";
        }
        print "</table>"; 
        
        /***********************************************
            Boutons Ajouter/Annuler
        ***********************************************/
        //Liste déroulante : Article en stock
        $optart.="<option></option>";
        $sql = "SELECT m.libelle, a.artcode, a.artcode_eurocave, a.artid, si.paht";
        $sql .= " FROM stock_mini sm";
        $sql .= " LEFT JOIN stock_item si USING (artid)";
        $sql .= " INNER JOIN article a USING(artid)";
        $sql .= " INNER JOIN article_marque USING(artid)";
        $sql .= " INNER JOIN marque m USING(marqueid)";
        $sql .= " GROUP BY sm.artid";
        $sql .= " ORDER BY a.artcode";
        $r = $DB_site2->query($sql);
        while($art = $DB_site2->fetch_array($r)){
            $paht = $DB_site2->query_first("SELECT prix FROM article_fournisseur WHERE artid=".$art['artid']);
            if($art['marqueid'] == 68){
                $optart.="<option value=\"".$art['artid'].":".$paht[0]."\">".$art['libelle']." - ".$art['artcode']."</option>";
            }else
                $optart.="<option value=\"".$art['artid'].":".$paht[0]."\">".$art['libelle']." - ".$art['artcode']." ".$art['artcode_eurocave']."</option>";
        }
        
        // -> Ajouter un article
        echo"
        <div id=\"addStock\" style=\"display:none;\">
            <form method=\"post\" id=\"form_stock\" action=\"\" onsubmit=\"submitStock('add'); return false;\">
                <input type=\"hidden\" name=\"cmdStockA\" value=\"$stock_cmd->id\">
                <input type=\"hidden\" name=\"lieuStockA\" value=\"".$livraison['id']."\">
                <table id=\"tableStock\">
                    <tr>
                        <td colspan=\"2\" align=\"center\"><h3>Ajouter un article</h3><hr id=\"hrStock\"></td>
                    </tr>
                    <tr>
                        <td><label for=\"article\">Article à ajouter : </label></td>
                        <td><select name=\"artStockA\" id=\"article\" onchange=\"videStock();\">$optart</select></td>
                    </tr>
                    <tr>
                        <td><label for=\"date_souhaitee\">Date livraison souhaitée : </label></td>
                        <td><input id=\"date_souhaitee\" name=\"date_souhaitee\" onclick=\"calendrier_show(this.id,this.value);\" size=\"8\" readonly=\"\" type=\"text\"><a href=\"javascript:cal_delete('date_souhaitee');\" class=\"cal_delete\"> X&nbsp;</a></td>
                    </tr>
                    <tr>
                        <td><label for=\"quantite\">Quantité : </label></td>
                        <td><input type=\"number\" name=\"qteStockA\" id=\"quantite\" min=\"0\" style=\"width:11%;\"></td>
                    </tr>
                    <tr>
                        <td colspan=2 style=\"text-align:center;\"><input type=\"submit\" value=\"Ajouter\">
                        <input type=\"button\" value=\"Fermer\" onclick=\"hideStock('addStock');\"></td>
                    </tr>
                </table>
            </form>
        </div>";
        
        // -> Annuler la commande
        echo"
        <div id=\"cancelStock\" style=\"display:none;\">
            <form method=\"post\" id=\"form_stock\" action=\"\"  onsubmit=\"submitStock('cancel'); return false;\">
                <input type=\"hidden\" name=\"cmdStockC\" value=\"$stock_cmd->id\">
                <table id=\"tableStock\">
                    <tr>
                        <td colspan=\"2\" align=\"center\"><h3>Annuler la commande</h3><hr id=\"hrStock\"></td>
                    </tr>
                    <tr>
                        <td colspan=\"2\" align=\"center\">Vous êtes sur le point d'annuler la totalité<br> de la <strong>commande $stock_cmd->id</strong></td>
                    </tr>
                    <tr>
                        <td colspan=2 style=\"text-align:center;\"><input type=\"submit\" value=\"Valider\">
                        <input type=\"button\" value=\"Fermer\" onclick=\"hideStock('cancelStock');\"></td>
                    </tr>
                </table>
            </form>
        </div>";

    echo "<br>";
    
    include("stock_adv.php");
    
    $lieux=$stock->lieux();
    
    print "<br><div class=\"table_factures\">
        <p><b>Utilisation des produits de cette commande de stock :</b></p>
        <table border=\"1\" cellspacing=\"0\">
        <tr>
            <td align=\"center\"><b>Ref.</b></td>
            <td align=\"center\"><b>PA HT</b></td>
            <td align=\"center\"><b>Ecopart HT</b></td>
            <td align=\"center\"><b>utilisation</b></td>
            <td align=\"center\"><b>lieu</b></td>
            <td align=\"center\"><b>Qté</b></td>
            <td align=\"center\"><b>Etat</b></td>
        </tr>";
    $sql="SELECT stock_item.*, article.artcode FROM stock_item INNER JOIN article ON (stock_item.artid=article.artid) WHERE stock_cmd_id='".$stock_cmd->id."' ";
    
    
    $utilisations=$DB_site2->query($sql);
    while($utilisation=$DB_site2->fetch_array($utilisations)){
        
        if($utilisation[state]==1){
            $etat="en stock";
        } elseif($utilisation[state]==2){
            $etat="réservé";
        } elseif($utilisation[state]==3){
            $etat="expédié";
        }
        $le_lieu=$lieux[$utilisation[lieu]];
        print "<tr>
                <td>".$utilisation[artcode]."</td>
                <td align=\"right\">".$utilisation[paht]." €</td>
                <td align=\"right\">".$utilisation[ecopartht]." €</td>
                <td>".$utilisation[dest]."</td>
                <td>".$le_lieu."</td>
                <td align=\"right\">1</td>
                <td>".$etat."</td>
            </tr>";
        
    }
    
    print "</table>";
    

    print "<br><div class=\"table_factures\">
    <p><b>Stock</b></p>
    <table border=\"1\" cellspacing=\"0\">
    <tr><td colspan=\"2\" align=\"center\">Ref.</td><td>Qté en stock</td><td>Qté non réservé</td><td>Qté réservé</td><td>Qté non<br>validé</td><td>Qté dispo</td><td>Lieux de stockage</td><td>Livraisons prévues - cmd</td><td>Qte dispo<br>prévi.</td></tr>";


    $stock1=false;
    $stock_cmd->items();
    $stock_cmd->groupitems();
    foreach($stock_cmd->items as $item){
        $stocklieu=array();
        $livrlieu=array();
        $stockcmdids=array();
        $txtdetail=$txtlivr=$txtnoncmd="";
        $sumnoncmd=0;
        $stockitems=$stock->search($item->artid);
        foreach($stockitems as $stockitem)
          $stocklieu[$stockitem->lieu]++;

        $livritems=$stock->search($item->artid,null,null,0);
        foreach($livritems as $livritem){
          $livrlieu[$livritem->lieu][$livritem->date_livraison]++;
          $stockcmdids[$livritem->date_livraison][$livritem->stock_cmd_id]++;
        }
    
      $si=new stock_item($DB_site2);
      $si->loadartid($item->artid);
      $noncmd=$si->cmd_non_reserve();
      if($noncmd!=false){
        foreach($noncmd as $noncmdid){
        $noncmdqte=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$noncmdid." AND (artid=".$si->artid.($si->artids!=""?" OR artid IN (".$si->artids.")":"").")");
          $txtnoncmd.="<a href=\"facture.php?action=editer&facturid=".$noncmdid."\" target=\"_blank\">".$noncmdid."</a> : ".$noncmdqte[0]."<br>";
          $sumnoncmd+=$noncmdqte[0];
        }
      }
      foreach($stocklieu as $lieu=>$qte){
         $txtdetail.="<p style=\"".($qte>=$item->qte?"":"color:red;")."\">".$lieux[$lieu]." : ".$qte."</p>";
      }

      $qte_tt_prev=0;
      foreach($livrlieu as $lieu=>$livraisons){
        $txtlivr.="<p>".$lieux[$lieu];
        foreach($livraisons as $date=>$qte){
            foreach($stockcmdids[$date] as $stockcmdid=>$qtes){
                if($_GET['stock_cmd_id'] == $stockcmdid){
                    $txtlivr.="<br>".date("d/m/Y",$date)." : ".$qtes." - <span style=\"color:red;\">n°".$stockcmdid."</span>";
                }
                else {
                    $txtlivr.="<br>".date("d/m/Y",$date)." : ".$qtes." - <a href=\"stock.php?action=cmd&stock_cmd_id=".$stockcmdid."\" target=\"_blank\">n°".$stockcmdid."</a>";
                }
            }
            $qte_tt_prev+=$qte;
        }
        $txtlivr.="</p>";
      }

      /// pour les commandes non payées
      $simi="";
      $sql_simi=$DB_site2->query("SELECT artid,artids FROM stock_mini WHERE artids LIKE '%".$item->artid."%' OR artid=".$item->artid);
      foreach($sql_simi as $artids){
        $simi[]=$artids['artids'];
        $simi[]=$artids['artid'];
      }
      $simi=array_filter(array_unique($simi));
      (empty($simi)||!is_array($simi)?$art_in="('".$item->artid."')":$art_in="('".implode("','",$simi)."')");
      $cmdnnpay="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and supprimee='0' and artid IN ".$art_in." AND valide = 0 AND moyenid in (2,4,5,14,17,18,99)";
      $reqcmd=$DB_site2->query($cmdnnpay);
      $qte_nn_paye=0;
      $list_nn_paye="";
      while($facture=$DB_site2->fetch_array($reqcmd)){
        $non_paye=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid=".$item->artid." AND dest='facture ".$facture['factureid']."' AND state=2");
        if($non_paye[0]<$facture['qte']){
            $qte_nn_paye+=$facture['qte'];
            $list_nn_paye.="<br><a href=\"facture.php?action=editer&facturid=".$facture[0]."\" target=\"_blank\">".$facture[0]."</a>";
        }
    }

    /// pour les commandes non réservées:
    $cmdqte="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and valide in ('1','7') and supprimee='0' and artid=".$item->artid;
    $reqcmd=$DB_site2->query($cmdqte);
    $qte_nn_reserve=0;
    $list_nn_reserve="";
    while($facture=$DB_site2->fetch_array($reqcmd)){
        $reserve=$DB_site2->query_first("SELECT COUNT(*) FROM stock_item WHERE artid=".$item->artid." AND dest='facture ".$facture['factureid']."' AND state=2");
        if($reserve[0]<$facture['qte']){
            $qte_nn_reserve+=$facture['qte'];
            $list_nn_reserve.="<br><a href=\"facture.php?action=editer&facturid=".$facture[0]."\" target=\"_blank\">".$facture[0]."</a>";
        }
    }

      /// pour les commandes réservées:
      $list_reserve="";
      $qte_reserve=0;
      $cmdres=$DB_site2->query("SELECT si.*,mini,alert,artids,delai FROM stock_item si LEFT JOIN stock_mini USING(artid) WHERE state IN ('2') AND artid=".$item->artid." GROUP BY si.id");
      if($DB_site2->num_rows($cmdres)>0){
        $qte_reserve=$DB_site2->num_rows($cmdres);
    
        $cmdlist=$stock->getdest($item->artid,null,2);  
        foreach($cmdlist as $cmdid) $list_reserve.="<br><a href=\"facture.php?action=editer&facturid=".$cmdid."\" target=\"_blank\">".$cmdid."</a>";
      }
      $stock1=true;
      /// qte physique
      $r1=$stock->stockbyqte(array(0,1,2),null);
      $r=array_merge($r1,$stock->stock0(array(0,1,2),null,true));
      ksort($r);
      $r0=$stock->stockbyqte(0,null);
      
      foreach($r as $itemr){
        if($itemr->artid==$item->artid){
            $nb_stock=$itemr->qte;
        }
      }
      $nb_stock=$nb_stock-$r0[$item->artid]->qte;
      print "<tr><td><img src=\"../configurations/www.mon-espace-cuisson.com/images/produits/".$item->artid.".jpg\" width=\"30\"></td><td>".$item->qte." ".$item->artcode."<br>".$item->ref."</td><td style=\"text-align:center;\"><b>".$nb_stock."</b></td><td style=\"text-align:center;\">".$qte_nn_reserve.$list_nn_reserve."</td><td style=\"text-align:center;\">".$qte_reserve.$list_reserve."</td><td style=\"text-align:center;\">".$qte_nn_paye.$list_nn_paye."</td><td style=\"text-align:center;background-color:#c4c3c3;\"><b>".($nb_stock-$qte_nn_reserve-$qte_reserve-$qte_nn_paye)."</b></td><td>".$txtdetail."</td><td>".$txtlivr."</td><td style=\"text-align:center;background-color:#c4c3c3;\"><b>".($nb_stock-$qte_nn_reserve-$qte_reserve-$qte_nn_paye+$qte_tt_prev)."</b></td></tr>";

    }
    if(!$stock1) print "<tr><td colspan=\"6\" style=\"text-align:center;\">Aucun article en stock</td></tr>";
    print "</table>
    </div>";

    $suiviicon=array("tel_out"=>"<img src=\"images/tel_out.png\"><br>","tel_in"=>"<img src=\"images/tel_in.png\"><br>","mail_in"=>"<img src=\"images/get_mail.png\"><br>","mail_out"=>"<img src=\"images/send_mail.png\"><br>");
    $suivis=$stock_cmd->suivis();
    print "<br><table class=\"table_modules_boutiques\">
    <tr><td colspan=\"6\" style=\"font-weight:bold;text-align:center\">Suivis</td></tr>
    <tr><td colspan=\"6\">
    <table width=\"100%\">
    <tr><td class=\"suivipro\"><b>Suivi fournisseur</b></td><td class=\"suiviint\"><b>Suivi interne</b></td></tr>
    <tr><td class=\"suivipro\"><input type=\"text\" onkeyup=\"srcfournisseur(this.value,".$stock_cmd->id.");\"><div id=\"srcfournisseur".$stock_cmd->id."\"></div></td><td class=\"suiviint\"><a href=\"javascript:suiviauto(".$stock_cmd->id.",'',0,'Message interne :');\">Message interne</a></td></tr>
    <tr id=\"trsuivi".$stock_cmd->id."\" style=\"display:none;\">
      <td colspan=\"2\">
    <div id=\"suivititle".$stock_cmd->id."\"></div>
    <textarea id=\"txt".$stock_cmd->id."\" cols=\"60\" rows=\"3\"></textarea>
    <br>Date de rappel : <input type=\"text\" id=\"rappel".$stock_cmd->id."\" onclick=\"calendrier_show(this.id,this.value,'planning',planningcb);\" readonly> <input type=\"checkbox\" name=\"box\" value=\"1\" checked> Afficher le rappel
    <br><input type=\"button\" value=\"OK\" onclick=\"submitsuivi(".$stock_cmd->id.")\"><input type=\"button\" value=\"Annuler\" onclick=\"suivi_hide(".$stock_cmd->id.")\">
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    </table>
    </td></tr>";

    print "<tr><td colspan=\"6\"><div id=\"suivi_tous\" onclick=\"affiche_type_suivi()\">Tous</div>
            <div id=\"suivi_int\" onclick=\"affiche_type_suivi('int')\">Interne</div>
            <div id=\"suivi_cde\" onclick=\"affiche_type_suivi('pro')\">Pro</div></td></tr>";

    foreach($suivis as $suivi){

        //// récupère pj du mail
        $pjlist = ""; 
        $sql = "SELECT *";
        $sql .= " FROM mail_attachment";
        $sql .= " WHERE mailid = '".$suivi->id."'";
        $reqpj = $DB_site2->query($sql);
        $i = 0;
        if($DB_site2->num_rows($reqpj) > 0){
            while($pj = $DB_site2->fetch_array($reqpj)){
                $year = date("Y", $suivi->date);
                $valeur = ROOT_PATH."configurations/".$host."/mails/attachments/$year/".$pj['id'].".".$pj['ext'];
                if (file_exists($valeur)){

                    $pjlist .= "<form method=\"post\" style=\"margin: 0px;\" align=\"center\" type=\"button\" action=\"/admin/includes/download.php\">
                    <input type=\"submit\" value=\" \" onmouseover=\"mouseOver('special".$suivi->id.$i."')\" onmouseout=\"mouseOut('special".$suivi->id.$i."')\" class=\"dlbutton\"/>
                    <input type=\"hidden\" name=\"page\" value=\"".$valeur."\"/>
                    </form> <div id=\"special".$suivi->id.$i."\" class=\"spehover\">".$pj['name']."</div>";
                }
                $i++;
            }
        }

        $class="suiviint";
        $totxt="";
        if($suivi->to!="0"){
          $class="suivipro";
          $to=$suivi->to;
          if(is_array($suivi->to)) $to=implode("','",$suivi->to);
          $req=$DB_site2->query("SELECT id,societe,nom,telephone,mail FROM fournisseur WHERE id IN ('".$to."') GROUP BY nom");
          while($fourn=$DB_site2->fetch_array($req))
            $totxt.="<div class=\"suividest\">".$fourn['societe']." ".$fourn['nom']."<br><a href=\"javascript:suiviauto(".$stock_cmd->id.",'tel_out',".$fourn['id'].",'Appel téléphone ".addslashes($fourn['societe'])." (".addslashes($fourn['nom']).") ".$fourn['telephone']."')\">".$fourn['telephone']."<br><a href=\"javascript:mail_auto(undefined,'".$fourn['mail']."');mail_suivi('suivi_stock',".$stock_cmd->id.")\">".$fourn['mail']."</a></div>";
        }
        $rappel="";
        if($suivi->rappel>0) $rappel="Rappel le ".date("d/m/Y à H:i",$suivi->rappel)." pour ".$suivi->pour;
        print "<tr class=\"$class\"><td width=\"50\">".$suiviicon[$suivi->moyen].date("d/m/Y H:i",$suivi->date)."</td><td>".$suivi->commercial."</td><td width=\"120\">".$totxt."</td><td>".formatsuivi($suivi->suivi)."</td><td>$rappel</td><td>".$pjlist."
        </td></tr>";
    }
    print "</table>
    <br>";


    print "<table class=\"table_modules_boutiques\">
    <tr><td colspan=\"8\" style=\"font-weight:bold;text-align:center\">Factures</td></tr>
    <tr><td>Type</td><td>Réf. produits</td><td>Destinataire</td><td>N°</td><td>Date commande</td><td>Date facture</td><td>Entrée par</td><td>Achat H.T.</td></tr>";
    $total=0;
    $code_comptable=array();
    $req=$DB_site2->query("SELECT * FROM code_comptable");
    while($cc=$DB_site2->fetch_array($req)){
    $code_comptable[$cc['type']]=ucfirst($cc['info']);
    }

    $factures=$stock_cmd->factures();
    foreach($factures as $type=>$facturestype){
        $varpaht="paht";
        if($type=="trspt") $varpaht="prix_trspt";
        if($type=="avoir") continue;
        foreach($facturestype as $factureid=>$items){
            $facture=$DB_site2->query_first("SELECT * FROM facture_fournisseur WHERE numero='".$factureid."'");
            print "<tr style=\"background-color:#dddddd;\">
              <td>".$code_comptable[$type]."</td>
              <td>";
            $totalligne=0;
            foreach($items as $item){
                print $item->qte." ".$item->artcode."<br>";
                $totalligne+=$item->$varpaht;
                if($type=="fourn") $totalligne+=($item->ecopartht*$item->qte);
            }
            $total+=$totalligne;
            print "</td>
            <td>".$facture['fournisseur']."</td>
            <td><a href=\"fournisseur.php?action=editer&facturid=".$factureid."\" target=\"_blank\">".$factureid."</a></td>
            <td>".($item->date>0?date("d/m/Y",$item->date):"")."</td>
            <td>".date("d/m/Y",$facture['date_facture'])."</td>
            <td>".$facture['utilisateur']."</td>
            <td style=\"text-align:right; ".$color_td."\">".number_format($totalligne,2,',','')."&euro;</td>
           </tr>";
        }
    }

    $totalprevision=$total;
    $nonfacture=$stock_cmd->nonfacture();
    foreach($nonfacture as $item){
    $totalligne=($item->paht+$item->ecopartht);
    $totalprevision+=$totalligne;
    print "<tr style=\"background-color:#dddddd;\">
            <td>Achat produit</td>
            <td>".$item->qte." ".$item->artcode."</td>
            <td></td>
            <td></td>
            <td>".($item->date>0?date("d/m/Y",$item->date):"")."</td>
            <td></td>
            <td></td>
            <td style=\"color:red;text-align:right;\">".number_format($totalligne,2,',','')."&euro;</td>
        </tr>";
    }
    
    $nonfacture=$stock_cmd->nonfacture("trspt");
    foreach($nonfacture as $item){
    $totalprevision+=$item->paht;
    print "<tr style=\"background-color:#dddddd;\">
            <td>Achat transport</td>
            <td>".$item->qte." ".$item->artcode."</td>
            <td></td>
            <td></td>
            <td>".($item->date>0?date("d/m/Y",$item->date):"")."</td>
            <td></td>
            <td></td>
            <td style=\"color:red;text-align:right;\">".number_format($item->paht,2,',','')."&euro;</td>
        </tr>";
    }
    
    $req=$DB_site2->query("SELECT description, destinataire, ffid, date, utilisateur, paht, ecopart, qte FROM avoir_ligne WHERE stock_cmd_id=".$stock_cmd->id." AND date_recu=0");
    while($avoir=$DB_site2->fetch_array($req)){
        $prixavoir=$avoir['qte']*($avoir['paht']+$avoir['ecopart']);
        $totalprevision-=$prixavoir;
        print "<tr style=\"background-color:#dddddd;\">
            <td>Demande d'avoir en attente</td>
            <td>".$avoir['description']."</td>
            <td>".$avoir['destinataire']."</td>
            <td>".$avoir['ffid']."</td>
            <td>".date("d/m/Y",$avoir['date'])."</td>
            <td></td>
            <td>".$avoir['utilisateur']."</td>
            <td style=\"color:red;text-align:right;\">".number_format(-$prixavoir,2,',','')."&euro;</td>
        </tr>";
    }
    
    print "<tr><td colspan=\"2\" style=\"font-weight:bold;\">Total prévisionnel</td><td colspan=\"6\" style=\"text-align:right\">".number_format($totalprevision,2,',','')."&euro;</td></tr>
    <tr><td colspan=\"2\" style=\"font-weight:bold;\">Total réel</td><td colspan=\"6\" style=\"text-align:right;\">".number_format($total,2,',','')."&euro;</td></tr>";
    
    print "</table>";

    print "<form name=\"suiviform\" method=\"post\">
    <input type=\"hidden\" name=\"cmdid\">
    <input type=\"hidden\" name=\"destinataire\">
    <input type=\"hidden\" name=\"moyen\">
    <input type=\"hidden\" name=\"suivi\">
    <input type=\"hidden\" name=\"rappel\">
    <input type=\"hidden\" name=\"pour\">
    </form>";
    print "<script language=\"javascript\">
    function modiflivrdate(cmdid,artid){
    document.getElementsByName('modifcmdid')[0].value=cmdid;
    document.getElementsByName('artid')[0].value=artid;
    calendrier_show(undefined,'',false,updlivrdate);
    }
    function updlivrdate(){
    document.getElementsByName('livrdate')[0].value=cal_timestamp;
    document.forms.livrdateform.submit();
    }
    fb=new filebox('fb');
    fb.dataurl='fournisseur_files.php?fournisseur=".trim(addslashes($stock_cmd->destinataire))."&cmdid=".$stock_cmd->id."&action=stock&parent=0';
    </script>";
    
    if(isset($_GET['savid'])) include("stock_sav.php");

    break;

    case "attente":
    case "historique":
    $filter=array();
    $stock_cmd=new stock_cmd($DB_site2);
    $state=0;
    $showstate=0;
    if($_GET['action']=="historique"){ $state=array(1,2,3); $showstate=null; }
    $r=$stock_cmd->getcmd($state);

    $requete=$DB_site2->query("SELECT id,societe,ville FROM fournisseur WHERE type='fourn'");
    while($f=$DB_site2->fetch_array($requete)) $lieux[$f['id']]=$f['societe']." ".$f['ville'];

    $totalligne=0;;
    $tr=array();
    $suiviicon=array("tel_out"=>"<img src=\"images/tel_out.png\"><br>","tel_in"=>"<img src=\"images/tel_in.png\"><br>","mail_in"=>"<img src=\"images/get_mail.png\"><br>","mail_out"=>"<img src=\"images/send_mail.png\"><br>");
    foreach($r as $scmd){
      $filter[$scmd->destinataire]++;
      
      if($scmd->destinataire==$_GET['filter'] || !isset($_GET['filter']) || $_GET['filter']==""){
      $trspt="<a href=\"javascript:trsptcmd(".$scmd->id.");\">Passer commande de transport</a> <a href=\"javascript:notrsptcmd(".$scmd->id.");\">Pas de transport</a>";
      if($scmd->transporteur!="") $trspt="transport ".$scmd->transporteur;
      if($scmd->transporteur=='0') $trspt="Pas de transport";
      $enlev=$DB_site2->query_first("SELECT societe,nom,prenom,adresse1,adresse2,cp,ville,telephone,horaires,libelle AS pays FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$scmd->enlevement);
      $livr=$DB_site2->query_first("SELECT societe,nom,prenom,adresse1,adresse2,cp,ville,telephone,horaires,libelle AS pays FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$scmd->livraison);
      $tr[$scmd->id]="<form method=\"post\">
      <input type=\"hidden\" name=\"attente\" value=\"".$scmd->id."\">
      <input type=\"hidden\" id=\"enlevement_".$scmd->id."\" value=\"".htmlentities($enlev['societe']."<br>".$enlev['nom']." ".$enlev['prenom']."<br>".$enlev['adresse1'].($enlev['adresse2']!=""?"<br>".$enlev['adresse2']:"")."<br>".$enlev['cp']." ".$enlev['ville']."<br>".$enlev['pays'],ENT_COMPAT,"ISO-8859-15")."<br>Tel :".$enlev['telephone']."<br>".$enlev['horaires']."\">
      <input type=\"hidden\" id=\"livraison_".$scmd->id."\" value=\"".htmlentities($livr['societe']."<br>".$livr['nom']." ".$livr['prenom']."<br>".$livr['adresse1'].($livr['adresse2']!=""?"<br>".$livr['adresse2']:"")."<br>".$livr['cp']." ".$livr['ville']."<br>".$livr['pays'],ENT_COMPAT,"ISO-8859-15")."<br>Tel :".$livr['telephone']."<br>".$livr['horaires']."\">
      <tr><td colspan=\"8\" class=\"cmdhead\">".date("d/m/Y H:i",$scmd->date)." ".$scmd->utilisateur." ".$scmd->destinataire." #CDE".$scmd->id."# ".$trspt."</td></tr>
      <tr><td class=\"cmdtdl\">Ref acilyon<br>constructeur</td><td>Marque</td><td>Qté cde</td><td>Délai souhaité</td><td>Suivi cde</td><td>Relance</td><td>Lieu stockage</td><td class=\"cmdtdr\">Validation rentrée</td></tr>";
      $items=$scmd->items($showstate);
      $scmd->groupitems();
      foreach($scmd->items as $artid=>$item){
        $totalligne=($item->qte+$r2[$item->artid]->qte)*$item->paht;
        $totalqte+=$item->qte;
        $total+=$totalligne;
        $modifid="modiflivr".$item->stock_cmd_id.$item->artid;
        $validform="<input type=\"checkbox\" name=\"chk_".$item->artid."\" value=\"1\" onclick=\"document.getElementById('detail_".$scmd->id.$item->artid."').style.display=(this.checked?'inline':'none');\"> <span id=\"detail_".$scmd->id.$item->artid."\" style=\"display:none\">qté : <input type=\"text\" name=\"qte_".$item->artid."\" value=\"".$item->qte."\" size=\"1\"><!--<br>rayon : <input type=\"text\" name=\"rayon_".$item->artid."\">--></span>";
        if($item->state[0]==0) $validform=($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"");
        $tr[$item->stock_cmd_id].="<tr>
          <td class=\"cmdtdl\">".$item->artcode." (".$item->ref.")</td>
          <td>".$item->marque."</td>
          <td>".$item->qte."</td>
          <td onmouseover=\"document.getElementById('$modifid').style.display='block';\" onmouseout=\"document.getElementById('$modifid').style.display='none';\"><img src=\"images/editer.gif\" id=\"$modifid\" style=\"position:absolute;display:none;cursor:pointer;\" onclick=\"modiflivrdate(".$scmd->id.",".$item->artid.")\">".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"-")."</td>
          <td></td>
          <td></td>
          <td>".$lieux[$item->lieu]."</td>
          <td class=\"cmdtdr\">$validform</td>
          </tr>";
      }

    
      $suivis=$scmd->suivis();
      $lsuivi="<table width=\"100%\">
      <tr><td class=\"suivipro\"><b>Suivi fournisseur</b></td><td class=\"suiviint\"><b>Suivi interne</b></td></tr>
      <tr><td class=\"suivipro\"><input type=\"text\" onkeyup=\"srcfournisseur(this.value,".$scmd->id.");\"><div id=\"srcfournisseur".$scmd->id."\"></div></td><td class=\"suiviint\"><a href=\"javascript:suiviauto(".$scmd->id.",'',0,'Message interne :');\">Message interne</a></td></tr>
      <tr id=\"trsuivi".$scmd->id."\" style=\"display:none;\">
        <td colspan=\"2\">
          <div id=\"suivititle".$scmd->id."\"></div>
          <textarea id=\"txt".$scmd->id."\" cols=\"60\" rows=\"3\"></textarea>
          <br>Date de rappel : <input type=\"text\" id=\"rappel".$scmd->id."\" onclick=\"calendrier_show(this.id,this.value,'planning',planningcb);\" readonly> <input type=\"checkbox\" name=\"box\" value=\"1\" checked> Afficher le rappel
          <br><input type=\"button\" value=\"OK\" onclick=\"submitsuivi(".$scmd->id.")\"><input type=\"button\" value=\"Annuler\" onclick=\"suivi_hide(".$scmd->id.")\">
        </td>
      </tr>
      </table>";
      $totxt="";
      if(count($lsuivi)>0){
          $lsuivi.="<table width=\"100%\">";
          foreach($suivis as $suivi){
        $class="suiviint";
        $totxt="";
        if($suivi->to!="0"){
          $class="suivipro";
          $to=$suivi->to;
          if(is_array($suivi->to)) $to=implode("','",$suivi->to);
          $req=$DB_site2->query("SELECT id,societe,nom,telephone,mail FROM fournisseur WHERE id IN ('".$to."') GROUP BY nom");
          while($fourn=$DB_site2->fetch_array($req))
            $totxt.="<div class=\"suividest\">".$fourn['societe']." ".$fourn['nom']."<br><a href=\"javascript:suiviauto(".$scmd->id.",'tel_out',".$fourn['id'].",'Appel téléphone ".addslashes($fourn['societe'])." (".addslashes($fourn['nom']).") ".$fourn['telephone']."')\">".$fourn['telephone']."<br><a href=\"javascript:mail_auto(undefined,'".$fourn['mail']."');mail_suivi('suivi_stock',".$fourn['id'].")\">".$fourn['mail']."</a></div>";
        }
        $rappel="";
        if($suivi->rappel>0) $rappel="Rappel le ".date("d/m/Y à H:i",$suivi->rappel)." pour ".$suivi->pour;
        $lsuivi.="<tr class=\"$class\"><td width=\"50\">".$suiviicon[$suivi->moyen].date("d/m/Y H:i",$suivi->date)."</td><td>".$suivi->commercial."</td><td width=\"120\">".$totxt."</td><td>".formatsuivi($suivi->suivi)."</td><td>$rappel</td></tr>";
          }
      }
      $lsuivi.="</table>";

      $tr[$scmd->id].="<tr><td colspan=\"8\" align=\"right\" class=\"cmdtdl cmdtdr\"><input type=\"submit\" value=\"Valider\"></td></tr>
      </form>
      <tr><td colspan=\"8\" class=\"cmdtdl cmdtdr\">$lsuivi</td></tr>
      <tr><td colspan=\"8\" class=\"cmdsep\">&nbsp;</td></tr>";
      }
    }
    if($_GET['action']=="historique")
      krsort($tr);
    else
      ksort($tr);

    $fourn=$fournname=array();
    $list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='trsptadv' ORDER BY societe");
    while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
      $groups=explode(";",$advf['groupes']);
      foreach($groups as $group){
        $fourn[$group][]=$advf['mail'];
        $fournname[$group]=$advf['societe'];
     }
    }else{
      $fourn[$advf['societe']][]=$advf['mail'];
      $fournname[$advf['societe']]=$advf['societe'];
    }
    }

    $opttrspt="<option></option>";
    foreach($fourn as $group=>$mails){
        $mails=array_unique($mails);
        $mailcsv=implode(";",$mails);
        $opttrspt.="<option value=\"$mailcsv\" onclick=\"document.getElementsByName('transporteur')[0].value='".addslashes($fournname[$group])."';\">".ucfirst($group)."</option>";
    }

    if($_GET['action']=="attente") print "<h3>Stock en attente de livraison</h3>";
    if($_GET['action']=="historique") print "<h3>Commandes livrées</h3>";

    print "$menu
    $msg
    <div id=\"stock_cmd\">
      <form name=\"trsptcmd\" method=\"post\">
      <input type=\"hidden\" name=\"cmdid\">
      <input type=\"hidden\" name=\"transporteur\">
      <div style=\"width:50%;float:left;\">
    Enlèvement :
    <p id=\"enlevmenttxt\"></p>
      </div>
      <div style=\"width:50%;float:left;\">
    Livraison :
    <p id=\"livraisontxt\"></p>
      </div>
      <div style=\"clear:both;\"></div>
      <p>Transporteur : <select name=\"mails\">$opttrspt</select></p>
      <p>Date d'enlevement : <input type=\"text\" id=\"indate\" name=\"indate\" onclick=\"calendrier_show(this.id,this.value)\" size=\"10\" readonly></p>
      <p>Date de livraison : <input type=\"text\" id=\"outdate\" name=\"outdate\" onclick=\"calendrier_show(this.id,this.value)\" size=\"10\" readonly></p>
      <p>Tarif : <input type=\"text\" name=\"tarif\" size=\"5\"></p>
      <div class=\"cmd_btn\"><input type=\"button\" value=\"OK\" onclick=\"createtrsptmail()\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('stock_cmd').style.display='none';\"></div>
      </form>
    </div>
    <p>Afficher par fournisseur : <select onchange=\"document.location='stock.php?action=".$_GET['action']."&filter='+this.value;\">
    <option value=\"\">Tout</option>";
    foreach($filter as $d=>$qte) print "<option value=\"$d\"".($_GET['filter']==$d?" selected":"").">$d ($qte)</option>";
    print "</select></p>
    <table class=\"cmdtable\">".implode("",$tr)."</table>";
    print "<script language=\"javascript\">
    function modiflivrdate(cmdid,artid){
    document.getElementsByName('modifcmdid')[0].value=cmdid;
    document.getElementsByName('artid')[0].value=artid;
    calendrier_show(undefined,'',false,updlivrdate);
    }
    function updlivrdate(){
    document.getElementsByName('livrdate')[0].value=cal_timestamp;
    document.forms.livrdateform.submit();
    }
    </script>
    <form name=\"livrdateform\" method=\"post\">
    <input type=\"hidden\" name=\"modifcmdid\">
    <input type=\"hidden\" name=\"artid\">
    <input type=\"hidden\" name=\"livrdate\">
    </form>";
    print "<form name=\"suiviform\" method=\"post\">
      <input type=\"hidden\" name=\"cmdid\">
      <input type=\"hidden\" name=\"destinataire\">
      <input type=\"hidden\" name=\"moyen\">
      <input type=\"hidden\" name=\"suivi\">
      <input type=\"hidden\" name=\"rappel\">
      <input type=\"hidden\" name=\"pour\">
      </form>";
    break;


    case "entree":
    $lieuopt="";
    $lieux=$stock->lieux();
    foreach($lieux as $id=>$lieu) $lieuopt.="<option value=\"$id\">$lieu</option>";

    $optart="<option></option>";
    $items=$stock->artids("artcode");
    foreach($items as $lieuid=>$lieuart)
      foreach($lieuart as $artid=>$art) $gitems[$art->marque.$art->artcode]=$art;

    ksort($gitems);
    foreach($gitems as $artid=>$art) $optart.="<option value=\"".$art->artid."\" onclick=\"document.getElementById('totalqte').innerHTML=".$art->qte.";document.getElementsByName('paht')[0].value=".floatval($art->artpaht).";\">".$art->marque." ".$art->artcode." ".$art->ref."</option>";

    print "<h3>Entrée manuelle</h3>
    $menu
    $msg
    <form method=\"post\">
    <input type=\"hidden\" name=\"entree\" value=\"1\">
    <div style=\"float:left;\">
      <p>Article : <select name=\"artid\" onchange=\"document.getElementById('imgart').src='/configurations/www.mon-espace-cuisson.com/images/produits/br/'+this.value+'.jpg';document.getElementById('imgart').style.display='block';document.getElementById('submit').disabled=false;\">$optart</select></p>
      <p>Quantité : <input type=\"text\" name=\"qte\" value=\"1\" size=\"1\"> total : <span id=\"totalqte\"></span></p>
      <p>Prix d'achat unitaire : <input type\"text\" name=\"paht\" value=\"\" size=\"4\"></p>
      <p>Lieu : <select name=\"lieu\">$lieuopt</select></p>
      <!--<p>Rayon : <input type=\"text\" name=\"rayon\"></p>-->
      <p>Infos : <textarea name=\"infos\" cols=\"40\"></textarea></p>
      <p align=\"center\"><input type=\"submit\" id=\"submit\" value=\"Ajouter\" disabled></p>
    </div>
    <div style=\"float:left;margin-left:40px;\">
      <img src=\"\" id=\"imgart\" style=\"width:80px;display:none;\">
    </div>
    </form>";
    break;

    case "sortie":

    $optsortie="";
    $sorties=array(""=>"","perdu"=>"perte","cadeau"=>"cadeau","maj"=>"remise à jour stock","autre"=>"autre");
    foreach($sorties as $sortie=>$txt) $optsortie.="<option value=\"".$sortie."\">".ucfirst($txt)."</option>";
    $optart="<option></option>";
    $items=$stock->artids("artcode");
    foreach($items as $lieuid=>$lieuart)
      foreach($lieuart as $artid=>$art) $gitems[$art->marque.$art->artcode]=$art;

    ksort($gitems);
    foreach($gitems as $artid=>$art) $optart.="<option value=\"".$art->artid."\"".($_GET['artid']==$art->artid?" selected":"").">".$art->marque." ".$art->artcode." ".$art->ref."</option>";


    print "<h3>Sortie manuelle</h3>
    $menu
    $msg
    <form method=\"post\">
    <input type=\"hidden\" name=\"sortie\" value=\"1\">
    <div style=\"float:left;\">
      <p>Article : <select name=\"artid\" onchange=\"document.location='stock.php?action=sortie&artid='+this.value\">$optart</select></p>";
    if(isset($_GET['artid'])){
    $items=$stock->stockbyartid($_GET['artid']);
    $itemlieu=array();
    foreach($items as $item) if($item->state==1) $itemlieu[$item->lieu]+=1;
    $optlieu=$qte1="";
    if(count($itemlieu)==0)
        print "<p style=\"color:red;\">Cet article n'est pas en stock</p>";
    else{
        foreach($itemlieu as $lieu=>$qte){
        $fourn=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".intval($lieu));
        $optlieu.="<option value=\"$lieu\" onclick=\"setqte($qte);\">$fourn[societe] $fourn[ville]</option>";
        if($qte1=="") $qte1=$qte;
        }
        print "<p>Lieu de stockage : <select name=\"lieu\">$optlieu</select></p>
        <p>Quantité : <select name=\"qte\">";
        for($i=1;$i<=$qte1;$i++) print "<option value=\"$i\">$i</option>";
        print "</select></p>
        <p>Destination : <select name=\"dest\" onchange=\"document.getElementById('submit').disabled=(this.value=='')\">".$optsortie."</select> <input type=\"text\" name=\"dest2\" size=\"5\" style=\"display:none;\"></p>
        <p>Infos : <textarea name=\"infos\" cols=\"40\"></textarea></p>
        <p align=\"center\"><input type=\"submit\" id=\"submit\" value=\"Terminer\" disabled></p>";
    }
     }
     print "</div>
      <div style=\"float:left;margin-left:40px;\">";
    if(isset($_GET['artid'])) print "<img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$_GET['artid'].".jpg\" id=\"imgart\" style=\"width:80px;\">";
     print "</div>
    </form>";
    break;

    case "liste":
    $requete=$DB_site2->query("SELECT id,societe,ville FROM fournisseur WHERE type='fourn' ORDER BY societe");
    while($lieu=$DB_site2->fetch_array($requete)) $optlieu.="<option value=\"".$lieu['id']."\">".$lieu['societe']." ".$lieu['ville']."</option>";
    $liste=$stock->artids("artcode");

    print "<h3>Liste des articles en stock</h3>
    $menu
    $msg
    <form name=\"formdelmini\" method=\"post\">
     <input type=\"hidden\" name=\"liste\" value=\"1\">
     <input type=\"hidden\" id=\"delartid\" name=\"delartid\" value=\"0\">
     <input type=\"hidden\" id=\"dellieu\" name=\"dellieu\">
    </form>
    <form name=\"liste_form\" method=\"post\">
    <input type=\"hidden\" name=\"liste\" value=\"1\">
    <input type=\"hidden\" name=\"artid\" value=\"0\">
    <p>Ajouter une référence : <input type=\"text\" id=\"src\" onkeyup=\"srcartid(this.value)\"></p>
    <div id=\"results\"></div>
    <hr>
    <div id=\"add\" style=\"display:none;position:absolute;border:solid thin black;background:white;\">
      <p id=\"modiftitle\" style=\"font-weight:bold;\"></p>
      <p>Lieu de stockage : <select name=\"lieu\">$optlieu</select></p>
      <p>Quantité appro : <input type=\"text\" name=\"qte\" value=\"1\" size=\"1\"></p>
      <p>Quantité mini : <input type=\"text\" name=\"qtemini\" value=\"1\" size=\"1\"></p>
      <p>Délai réappro (jours) : <input type=\"text\" name=\"delai\" value=\"0\" size=\"1\"></p>
      <p>Références similaires : <input type=\"hidden\" name=\"artids\"><span id=\"simbtn\"></span></p>
      Rechercher : <input type=\"text\" onkeyup=\"srcsimilaire(this.value);\"><div id=\"similaires\"></div>
      <p align=\"center\"><input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('add').style.display='none';\"> <input type=\"submit\" id=\"submitartid\" value=\"Ajouter\"></p>
    </div>";
    print "<div id=\"liste\">";
    ksort($liste);
    foreach($liste as $lieu=>$articles){
      $fourn=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".$lieu);
      $icon="plus";
      $style="none";
      if($_POST['lieu']==$lieu || $_POST['dellieu']==$lieu){
      $icon="moins";
      $style="block";
      }
      print "<p style=\"font-size:14px;\"><img src=\"images/$icon.jpg\" id=\"icon_$lieu\" align=\"absmiddle\" onclick=\"showlist($lieu);\"> ".$fourn['societe']." ".$fourn['ville']."</p>
      <table id=\"liste_".$lieu."\" style=\"display:$style;\">
      <thead style=\"font-weight:bold;\">
      <tr><td></td><td>Référence</td><td>Marque</td><td>Libellé</td><td>Qté stock physique</td><td>Qté stock virtuel</td><td>Qté appro</td><td>Qté mini</td><td>Délai réappro</td></tr>
      </thead>";
      ksort($articles);
      $lastmarque="";
      foreach($articles as $artcode=>$item){
      $si=$stock->search($item->artid,$item->lieu,null,array(1,2));
      $si0=$stock->search($item->artid,$item->lieu,null,array(0));
      $stockphysique=count($si);
      $stockattente=count($si0);
      $simbtn="";
      foreach($item->similaire as $sartid=>$sartcode) $simbtn.="<span id=\"sim_".$sartid."\" class=\"simbtn\">".$sartcode." <a href=\"javascript:rmsimilaire(".$sartid.")\">X</a></span>";
      $suppr="";
      if(in_array($user_info['username'],array("ariel","philippe","alexis","benoit")) && $stockphysique+$stockattente==0 && $item->alert==0 && $item->mini==0) $suppr="<br><a href=\"javascript:delmini(".$item->artid.",".$item->lieu.")\" style=\"color:red;\">Supprimer</a>";  
      
      if($item->marque!=$lastmarque) print "<tr style=\"text-align:center;background-color:#CCCCCC;\">
            <td colspan=\"10\">".$item->marque."</td>
            </tr>";
      
      print "<tr".($item->mini+$item->alert==0?" style=\"background-color:#FFAAAA;\"":"").">
            <td><img src=\"".$item->img."\" width=\"50\"></td>
            <td id=\"artname_".$item->artid."_".$lieu."\">".$item->artcode."<br><i>".implode("<br>",$item->similaire)."</i><br>".$item->ref."</td>
            <td>".$item->marque."</td>
            <td>".$item->libelle."</td>
            <td align=\"center\">".$stockphysique."</td>
            <td align=\"center\">".($stockphysique+$stockattente)."</td>
            <td><input type=\"text\" id=\"qte_".$item->artid."_".$lieu."\" value=\"".$item->mini."\" size=\"1\" readonly></td>
            <td><input type=\"text\" id=\"alert_".$item->artid."_".$lieu."\" value=\"".$item->alert."\" size=\"1\" readonly><input type=\"hidden\" id=\"artids_".$item->artid."_".$lieu."\" value=\"".$item->artids."\"><span id=\"simbtn_".$item->artid."_".$lieu."\" style=\"display:none;\">$simbtn</span></td>
            <td><input type=\"delai\" id=\"delai_".$item->artid."_".$lieu."\" size=\"2\" value=\"".$item->delai."\"></td>
            <td><a href=\"javascript:updatemini(".$item->artid.",".$lieu.");\">Modifier</a>$suppr</td>
            </tr>";
      $lastmarque=$item->marque;
      }
      print "</table>";
    }
    print "</div>";
    
    print "</form>";
    break;

    case "detail":
        $items=$stock->stockbyartid($_GET['artid']);
        $keys=array_keys($items);
        $item1=$items[$keys[0]];
        print "<p><img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$item1->artid.".jpg\" align=\"left\" width=\"80\">".$item1->artcode." ".$item1->ref."<br>".$item1->marque."</p>";
        print "<table>
        <thead style=\"font-weight:bold;\">
        <tr><td>Lieu stockage</td><td>Date entrée</td><td>Par</td><td>Origine</td><td>Status</td></tr>
        </thead>";
        foreach($items as $item){
            $fourn=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".intval($item->lieu));
            print "<tr><td>".$fourn['societe']." ".$fourn['ville']."</td><td>".($item->date>0?date("d/m/Y",$item->date):"")."</td><td>".$item->utilisateur."</td><td>".$item->origine()."</td><td>".$item->statetxt[$item->state]."</td></tr>";
        }
        print "</table>";
    break;

    // Gestion suivi des transferts
    case "suivi":
        print "<h3>Suivi</h3>"
        . $menu
        . $msg;
        
        $qte = array();
        $tr  = array();
        $lieux   = $stock->lieux();
        
        
        $artids = $stock->artids();
        
        foreach($artids as $lieuid => $items){
            foreach($items as $item)
                    $articles[$item->artcode] = $item->artid;
        }
        ksort($articles);

        $optart = "<option value=\"\">Tout</option>";
        foreach($articles as $artcode => $artid)
            $optart .= "<option value=\"".$artid."\"".($artid == $_GET['filter'] ? " selected" : "").">".$artcode."</option>";


        $optlieu = "<option value=\"\">Tout</option>";
        foreach($lieux as $lieuid => $lieu)
            $optlieu .= "<option value=\"".$lieuid."\"".($lieuid==$_GET['lieu']?" selected":"").">".$lieu."</option>";

        print ""
        . "<p>"
            . "Filtrer par article : "
            . "<select id=\"artid\" onchange=\"document.location='stock.php?action=suivi&filter='+this.value+'&lieu='+document.getElementById('lieu').value\">".$optart."</select> "
            . "Filter par lieu : "
            . "<select id=\"lieu\"onchange=\"document.location='stock.php?action=suivi&filter='+document.getElementById('artid').value+'&lieu='+this.value\">".$optlieu."</select>"
        . "</p>";

        
        if($_GET['filter'] == 0) unset($_GET['filter']);
        if($_GET['lieu']=="") unset($_GET['lieu']);
        if(isset($_GET['filter']) && isset($_GET['lieu'])){
            $_GET['filter'] = intval($_GET['filter']);
        
            $stockajust=new stock_ajust($DB_site2);
            $firstajust=$stockajust->searchfirst($_GET['filter']);
            foreach($firstajust as $lieuid=>$ajust){
                if($lieuid==$_GET['lieu']){
                    
                    $qte[$ajust->artid][$ajust->date]=$ajust->qte;
                    $tr[date("Y",$ajust->date)][$ajust->date]="<tr>
                        <td></td>
                        <td>".date("d/m/Y H:i",$ajust->date)."</td>
                        <td>".$ajust->utilisateur."</td>
                        <td>Etat du stock</td>
                        <td></td>
                        <td>".$lieux[$lieuid]."</td>
                        <td>".$ajust->qte."</td>
                    </tr>";
                }
            }
        
            $to    = time();
            $from  = $to-(isset($_GET['filter'])?5:1)*365*86400;
            $from=$firstajust[$_GET['lieu']]->date;
        
            $stateAnnu = -2;
            $suivi = $stock->suivi($_GET['filter'],$_GET['lieu'],$from,$to);
            $annule = $stock->suivi($_GET['filter'],$_GET['lieu'],$from,$to,$stateAnnu);

            ksort($suivi);
        
            $article = $DB_site2->query_first("SELECT artid,artcode,artcode_eurocave,libelleFR AS libelle FROM article WHERE artid=".$_GET['filter']);
            print "<p><img src=\"/configurations/www.mon-espace-cuisson.com/images/produits/br/".$article['artid'].".jpg\" width=\"80\"> ".$article['libelle']." ".$article['artcode_eurocave']."</p>";
            
                    
            foreach($annule['cmd'] as $a){
                $dateAnnu=array($a->date);
                $lieulivraison=$stock->lieudetail($a->livraison);
                $jsCmd = $a->id."A";
                $td = ""
                . "<tr style=\"background-color:#ff6060;\">"
                    . "<td><img src=\"images/plus.gif\" id=\"icon".$a->id."A\" onclick=\"details('".$jsCmd."')\"></td>"
                    . "<td>".date("d/m/Y H:i",$dateAnnu[0])."</td>"
                    . "<td>".$dateAnnu[1]."</td>"
                    . "<td>Commande #CDE".$a->id."# <b>ANNULE</b></td>"
                    . "<td>".$a->destinataire."</td>"
                    . "<td>".$lieulivraison."</td>"
                    . "<td>&nbsp;</td>"
                . "</tr>";

                $a->items();
                $a->groupitems();

                $total = 0;

                $td .= ""
                . "<tr id=\"cmd".$a->id."A\" style=\"display:none;background-color:#ff6060;\">"
                    . "<td></td>"
                    . "<td colspan=\"6\">"
                        . "<table style=\"background-color:#dddddd;\">";

                foreach($a->items as $item){

                    $td .= ""
                    . "<tr".($item->artid==$_GET['filter']?" style=\"background-color:#FFAAAA;\"":"").">"
                        . "<td>".$item->qte."</td>"
                        . "<td>".$item->artcode." (".$item->ref.")</td>"
                        . "<td align=\"right\">".number_format($item->paht+$item->ecopartht,2,',','')."</td>"
                    . "</tr>";

                    $total += $item->paht + $item->ecopartht;
                }
                
                $td .= ""
                            . "<tr>"
                                . "<td colspan=\"2\">Total HT</td>"
                                . "<td style=\"text-align:right;font-weight:bold;\">".number_format($total,2,',','')."</td>"
                            . "</tr>"
                        . "</table>"
                    . "</td>"
                . "</tr>";
                
                $tr[date("Y",$dateAnnu[0])][$dateAnnu[0]] .= $td;
            }

            foreach($suivi['cmd'] as $s){
                $lieulivraison=$stock->lieudetail($s->livraison);
                $l = ""
                . "<tr style=\"background-color:#AACCFF;\">"
                    . "<td><img src=\"images/plus.gif\" id=\"icon".$s->id."\" onclick=\"details(".$s->id.")\"></td>"
                    . "<td>".date("d/m/Y H:i",$s->date)."</td>"
                    . "<td>".$s->utilisateur."</td>"
                    . "<td>Commande #CDE".$s->id."#</td>"
                    . "<td>".$s->destinataire."</td>"
                    . "<td>".$lieulivraison."</td>"
                    . "<td>&nbsp;</td>"
                . "</tr>";

                $s->items();
                $s->groupitems();

                $total = 0;

                $l .= ""
                . "<tr id=\"cmd".$s->id."\" style=\"display:none;background-color:#AACCFF;\">"
                    . "<td></td>"
                    . "<td colspan=\"6\">"
                        . "<table style=\"background-color:#dddddd;\">";

                foreach($s->items as $item){

                    $l .= ""
                    . "<tr".($item->artid==$_GET['filter']?" style=\"background-color:#FFAAAA;\"":"").">"
                        . "<td>".$item->qte."</td>"
                        . "<td>".$item->artcode." (".$item->ref.")</td>"
                        . "<td align=\"right\">".number_format($item->paht+$item->ecopartht,2,',','')."</td>"
                    . "</tr>";

                    $total += $item->paht + $item->ecopartht;
                }
                
                $l .= ""
                            . "<tr>"
                                . "<td colspan=\"2\">Total HT</td>"
                                . "<td style=\"text-align:right;font-weight:bold;\">".number_format($total,2,',','')."</td>"
                            . "</tr>"
                        . "</table>"
                    . "</td>"
                . "</tr>";
                
                $tr[date("Y",$s->date)][$s->date] .= $l;
            }

            foreach($suivi['in'] as $s){
                    $qte[$s->artid][$s->date] += $s->qte;
                }
                            
            foreach($suivi['facture'] as $s){
                    $qte[$s->artid][$s->date_expedition] -= $s->qte;
                }
                    
            foreach($suivi['perdu'] as $s){
                    $qte[$s->artid][$s->date_sortie] -= $s->qte;
                }
                    
            foreach($suivi['suivi'] as $date=>$s){
                    $qte[$s->artid][$date] -= $s->qte;
                }

            foreach ($suivi['maj'] as $date=>$s) {
                    $qte[$s[artid]][$date] = (signe($s[qte]) * $qte[$s[artid]][$date]) +  $s[qte];
                }    
                
            
            foreach($suivi['in'] as $ts => $s){ // ENTREE EN STOCK
                    $trqte = 0;
                    ksort($qte[$s->artid]);
                    foreach($qte[$s->artid] as $qtets => $qteartid){
                        if($qtets<=$s->date)
                            $trqte+=$qteartid;
                    }
                    
                    $lieulivraison=$stock->lieudetail($s->lieu);
                    $tr[date("Y",$s->date)][$s->date] .= ""
                    . "<tr style=\"background-color:#FFCCEE;\">"
                        . "<td></td>"
                        . "<td>".date("d/m/Y H:i",$s->date)."</td>"
                        . "<td>".$s->utilisateur."</td>"
                        . "<td>".$s->origine()."<br><small>".$s->infos."</small></td>"
                        . "<td>Entrée ".$s->qte." ".$s->artcode." (".$s->ref.")</td>"
                        . "<td>".$lieulivraison."</td>"
                        . "<td>".$trqte."</td>"
                    . "</tr>";
            }

            foreach($suivi['facture'] as $ts=>$s){ // EXPEDITION POUR COMMANDE CLIENT
                    $trqte = 0;
                    foreach($qte[$s->artid] as $qtets => $qteartid){
                        if($qtets <= $s->date_expedition+86000) $trqte += $qteartid;
                    }
                    $lieulivraison=$stock->lieudetail($s->lieu);
                    $tr[date("Y",$s->date_expedition)][$s->date_expedition.".".$s->facturid] .= ""
                    . "<tr style=\"background-color:#CCFFAA;\">"
                        . "<td></td>"
                        . "<td>".date("d/m/Y H:i",$s->date_expedition)."</td>"
                        . "<td>".$s->utilisateur_adv."</td>"
                        . "<td>Commande ".$s->facturid."</td>"
                        . "<td>Expédition ".$s->qte." ".$s->artcode." (".$s->ref.")</td>"
                        . "<td>".$lieulivraison."</td>"
                        . "<td>".$trqte."</td>"
                    . "</tr>";
            }
            foreach($suivi['maj'] as $ts=>$s){ // suivi lié aux mise a jour du suivi du produit
                    $trqte = 0;
                    foreach($qte[$s[artid]] as $qtets => $qteartid){
                        if($qtets <= $s[date]) {
                            $trqte += $qteartid;
                        }
                    }
                    $lieulivraison=$stock->lieudetail($s[lieu]);
                    $tr[date("Y",$s[date])][$s[date].".".$s[id]] .= ""
                    . "<tr style=\"background-color:#CCFFAA;\">"
                        . "<td></td>"
                        . "<td>".date("d/m/Y H:i",$s[date])."</td>"
                        . "<td>".$s[user]."</td>"
                        . "<td>MAJ quantité du suivi</td>"
                        . "<td>".$s[commentaire]."</td>"
                        . "<td>".$lieulivraison."</td>"
                        . "<td>".$trqte."</td>"
                    . "</tr>";
            }

            foreach($suivi['perdu'] as $ts=>$s){
                    $trqte = 0;
                    foreach($qte[$s->artid] as $qtets => $qteartid){
                        if($qtets <= $s->date_sortie) $trqte += $qteartid;
                    }
                    
                    $lieulivraison=$stock->lieudetail($s->lieu);
                    $tr[date("Y",$s->date_sortie)][$s->date_sortie] .= ""
                    . "<tr style=\"background-color:#FF9999;\">"
                        . "<td></td>"
                        . "<td>".date("d/m/Y H:i",$s->date_sortie)."</td>"
                        . "<td>".$s->utilisateur_sortie."</td>"
                        . "<td>".$s->infos."</td>"
                        . "<td>".$s->qte." ".$s->artcode." (".$s->ref.")</td>"
                        . "<td>".$lieulivraison."</td>"
                        . "<td>".$trqte."</td>"
                    . "</tr>";
            }
            
            foreach($suivi['suivi'] as $ts=>$s){
                    $trqte = 0;
                    foreach($qte[$s->artid] as $qtets => $qteartid){
                        if($qtets <= $ts) $trqte += $qteartid;
                    }
            
                    $lieulivraison=$stock->lieudetail($s->lieu);
                    $transfert=$s->last_transfer($ts);
                    $lieuorigine=$stock->lieudetail($transfer['from']);
                    $commentaire="Transfert ".$s->qte." ".$s->artcode." vers ".$lieulivraison;
                    
                    $tr[date("Y",$ts)][$ts] .= ""
                            . "<tr style=\"background-color:#FF9999;\">"
                                    . "<td></td>"
                                    . "<td>".date("d/m/Y H:i",$ts)."</td>"
                                    . "<td>".$transfert['user']."</td>"
                                    . "<td>Transfert produit</td>"
                                    . "<td>".$commentaire."</td>"
                                    . "<td>".$lieuorigine."</td>"
                                    . "<td>".$trqte."</td>"
                            . "</tr>";
            }
        
            krsort($tr);

            echo "<a style=\"cursor:pointer\" onclick=\"jQuery('#majsuivi').show();\">Remettre à jour le suivi</a>";
            echo "<form name=\"majsuivi\" method=\"post\" action =\"stock.php?action=suivi&filter=".$_GET['filter']."&lieu=".$_GET['lieu']."\">";
            echo "<div id=\"majsuivi\" style=\"display:none;\">";
            echo "<p>Cliquez sur <strong>+</strong> ou <strong>-</strong>  pour ajouter ou enlever une quantité, puis indiquez la quantité et validez</p>";
            echo "<img id=\"majplus\" src=\"images/plus.jpg\" onclick=\"maj(0);\"> <img id=\"majmoins\" src=\"images/moins.jpg\" onclick=\"maj(1);\"><br><br>";
            echo "<div id=\"majvaleur\" style=\"display:none;\">";
            echo "<input type=\"text\" id=\"majsuivi\" name=\"majsuivi\" value=\"\" >";
            echo "<input type=\"hidden\" id=\"majop\" name=\"majop\" value=\"\" >";
            echo "<input type=\"hidden\" name=\"artid\" value=\"".$_GET['filter']."\" >";
            echo "<input type=\"hidden\" name=\"lieu\" value=\"".$_GET['lieu']."\" >";
            echo "<input type=\"submit\" value=\"Valider\">";
            echo "</div></div></form>";
           
            foreach($tr as $y=>$try){
                    krsort($try);
                    
                    $btnimg="plus.jpg";
                    $visible="none";
                    if($y==date("Y")){
                            $btnimg="moins.jpg";
                            $visible="table";
                    }
                    
                    echo "<h3 onclick=\"suivi_annee(".$y.")\"><img src=\"images/".$btnimg."\" id=\"btn".$y."\" style=\"vertical-align:middle;\"> ".$y."</h3>
                    <table id=\"suivi".$y."\" style=\"display:".$visible."\">
                            <tr>
                                    <td width=\"11\">#</td>
                                    <td width=\"100\">date</td>
                                    <td width=\"55\">utilisateur</td>
                                    <td width=\"200\">description</td>
                                    <td width=\"500\">destinataire</td>
                                    <td width=\"100\">lieu</td>
                                    <td width=\"11\">qté</td>
                            </tr>";
                    foreach($try as $trl)
                            echo $trl;
                    echo "</table>";
            }
        }
        
    break;
    // fin affichage suivi stock
        
    case "diag":
        $article=$DB_site2->query_first("SELECT artcode_eurocave FROM article WHERE artid=".$_GET['artid']);
        print "<table border=\"1\">
        <tr><td colspan=\"3\" style=\"font-weight:bold;\">Sorties</td></tr>";
        $sql="SELECT *,count(*) AS qte FROM `stock_item` WHERE `artid` = ".$_GET['artid']." AND `lieu` LIKE '".$_GET['lieu']."' group by dest";
        $req=$DB_site2->query($sql);
        while($s=$DB_site2->fetch_array($req)){
        if($s['dest']!=""){
          $dest=substr($s['dest'],8);
          $ligne=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=$dest AND artid=".$_GET['artid']);
          print "<tr><td>".$dest."</td><td>".$s['state']."</td><td style=\"color:".($ligne[0]!=$s['qte']?"red":"green")."\">".$ligne[0]."</td></tr>";
        }else
          print "<tr><td>?</td><td></td><td>".$s['qte']."</td></tr>";
        }
        print "</table>";

        print "<br><table border=\"1\">
        <tr><td colspan=\"3\" style=\"font-weight:bold;\">Entrées</td></tr>";
        $req=$DB_site2->query("SELECT *,count(*) as qte FROM `stock_item` where artid=".$_GET['artid']." and lieu=".$_GET['lieu']." AND state>0 group by stock_cmd_id");
        while($s=$DB_site2->fetch_array($req)){
        $suivi=$DB_site2->query_first("SELECT * FROM mail_outbox WHERE stock_cmd_id=".$s['stock_cmd_id']." ORDER BY date LIMIT 1");
        print "<tr><td>".$s['stock_cmd_id']."<br>".date("d/m/Y",$s['date_livraison'])."</td><td>".$s['qte']."</td><td>".preg_replace("/".$article['artcode_eurocave']."/si","<b style=\"color:red;\">".$article['artcode_eurocave']."</b>",$suivi['body'])."</td></tr>";
        }
        print "</table>";
        break;
        
        // Affichage formulaire pour demande de transfert
        case "transfert":
        $optart = "<option></option>";
        $items = $stock->artids("artcode");
        foreach($items as $lieuid=>$lieuart)
            foreach($lieuart as $artid=>$art) 
                $gitems[$art->marque.$art->artcode]=$art;

        ksort($gitems);
        
        foreach($gitems as $artid=>$art) 
            $optart .= "<option value=\"".$art->artid."\">".$art->marque." | ".$art->artcode." ".$art->ref."</option>";

        
        print "<h3>Transfert de Stock</h3>
        $menu
        $msg
        <form method=\"post\">
            <input type=\"hidden\" name=\"transfert\" value=\"1\">
            <div style=\"float:left;\">
                <p>Article : <select name=\"tArtid\" id=\"tArtid\" onchange=\"doTransfert(this.value);doImg(this.value);\">$optart</select></p>
                <div id=\"formTransfert\"></div>
                <div id=\"formTransfert2\"></div>
                <p align=\"center\"><input type=\"submit\" id=\"submit\" style=\"display:none;\" value=\"Transférer\"></p>
            </div>
            <div style=\"float:left; margin-left:40px;\">
                <img src=\"\" id=\"imgart\" style=\"display:none;\" width=\"50\">
            </div>
        </form>";
        
        //TSI affichage resultat du transfert
        print "<div style=\"float:left;margin-left:100px;color:green;text-align:center\"  >".$t_txt."</div>";
    break;
    
    
    case "qteinsttype":
        print "<h3>quantité instantanné par type de produit</h3> $menu ";
        $lieux=$stock->lieux();
        foreach($lieux as $id=>$lieu){
            tableau_stock_marque_type($id, "pour le lieu ".$lieu);
        }
        tableau_stock_marque_type(null, " TOTAL GLOBAL ");
        
    break;
    
}



include "footer.php";

?>
