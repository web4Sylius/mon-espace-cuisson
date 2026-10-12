<?php


require "admin_global.php";
include "./header.php";
include "./menu.php";

$banques=array();
$banques["CA"]=array("name"=>"Crédit Agricole","compte"=>512115,"411"=>"4119000300");
$banques["CP"]=array("name"=>"La poste","compte"=>514000,"411"=>"4119000300");

if(isset($_POST['banque'])){
  if(isset($_GET['modif'])) $DB_site2->query("DELETE FROM cheque WHERE remise='".$_GET['modif']."'");
  $sp=explode("/",$_POST['date']);
  $ts=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
  $c=0;
  foreach($_POST['facturid'] as $id=>$facturid){
      $result=$DB_site2->query("INSERT INTO cheque SET remise='".$_POST['remise']."', date=".$ts.", utilisateur='".$user_info['username']."', code_journal='".$_POST['banque']."', compte='".$banques[$_POST['banque']]['compte']."', code411='".$banques[$_POST['banque']]['411']."', facturid='".$facturid."', montant=".floatval($_POST['montant'][$id]));
      if($result) $c++;
  }
  if($c==count($_POST['facturid']))
    $error="<p>Tous les chèques ont été ajoutés</p>";
  else
    $error="<p>Erreur lors de l'ajout de chèque</p>";

  unset($_GET['modif']);
}

if(isset($_GET['del'])){
  $DB_site2->query("DELETE FROM cheque WHERE remise='".$_GET['del']."'");
}

print "<h1>Remise de chèque</h1>";

print "<LINK href=\"filebox.css\" type=\"text/css\" rel=\"stylesheet\">
<script language=\"javascript\" src=\"calendrier.js\"></script>
<script language=\"javascript\">
function newcheque(){
  var id=Math.floor(Math.random()*100000);
  var p=document.createElement('p');
  p.id=id;
  p.innerHTML=\"<img src='images/supprimer.gif' onclick='rmcheque(\"+id+\")'> n°commande <input type='text' name='facturid[\"+id+\"]' onkeyup='srcfacture(this,\"+id+\")'> montant TTC : <input type='text' name='montant[\"+id+\"]'>\";
  document.getElementById('chequelist').appendChild(p);
}
function rmcheque(id){
  document.getElementById('chequelist').removeChild(document.getElementById(id));
  calctotal();
}

function srcfacture(sender,id){
  if(sender.value.length<4) return;
  var resultdiv=document.getElementById('cheque_result');
  resultdiv.style.left=sender.offsetLeft+'px';
  resultdiv.style.top=(sender.offsetTop+20)+'px';
  resultdiv.style.display='block';
  resultdiv.innerHTML='<img src=\"../images/templates/wait.gif\">';

  ajaxrequest('cheque_ajax.php?txt='+sender.value+'&id='+id,cheque_cb);
}
function cheque_cb(httprequest){
  document.getElementById('cheque_result').innerHTML=httprequest.responseText;
}

function selectfacture(id,facturid,montant){
  document.getElementById('cheque_result').style.display='none';
  document.getElementsByName('facturid['+id+']')[0].value=facturid;
  document.getElementsByName('montant['+id+']')[0].value=montant;
  calctotal();
}
function calctotal(){
  var chequelist=document.getElementById('chequelist');
  var plist=chequelist.getElementsByTagName('p');
  var total=0;
  for(i=0;i<plist.length;i++){
    var id=plist[i].id;
    total+=parseFloat(document.getElementsByName('montant['+id+']')[0].value);
  }
  document.getElementById('totalttc').innerHTML=Math.round(total*100)/100+' &euro;';
}
</script>";


if(isset($_GET['add'])){
    $optbanque="<option></option>";
    foreach($banques as $code=>$infos) $optbanque.="<option value=\"".$code."\">".$infos['name']."</option>";
    print "<form method=\"post\">
    <div>
    $error
    <h2>Nouvelle remise de chèque</h2>
    <table>
    <tr><td>Banque</td><td><select name=\"banque\">".$optbanque."</select></td></tr>
    <tr><td>Date dépot à la banque</td><td><input type=\"text\" size=\"8\" id=\"date\" name=\"date\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td></tr>
    <tr><td>N° remise chèque</td><td><input type=\"text\" name=\"remise\"></td></tr>
    <tr><td>Montant total TTC bordereau</td><td id=\"totalttc\"></td></tr>
    </table>
    </div>

    <p><img src=\"images/plus.jpg\" onclick=\"newcheque()\" align=\"absmiddle\"> Chèques :</p>
    <div id=\"chequelist\"></div>
    <p><input type=\"submit\" value=\"Enregistrer\"></p>
    <div id=\"cheque_result\" style=\"position:absolute;background-color:white;border:solid 1px black;display:none;\"></div>
    </form>";
}elseif(isset($_GET['modif'])){
    
    print "<h2>Modifier la remise de chèque ".$_GET['modif']."</h2>";
    $remise=$DB_site2->query_first("SELECT *,SUM(montant) AS total FROM cheque WHERE remise='".$_GET['modif']."'");
    
    $optbanque="<option></option>";
    foreach($banques as $code=>$infos) $optbanque.="<option value=\"".$code."\"".($remise['code_journal']==$code?" selected":"").">".$infos['name']."</option>";

    print "<form method=\"post\">
    <table>
    <tr><td>Banque</td><td><select name=\"banque\">".$optbanque."</select></td></tr>
    <tr><td>Date dépot à la banque</td><td><input type=\"text\" size=\"8\" id=\"date\" name=\"date\" value=\"".date("d/m/Y",$remise['date'])."\" onclick=\"calendrier_show(this.id,this.value)\" readonly></td></tr>
    <tr><td>N° remise chèque</td><td><input type=\"text\" name=\"remise\" value=\"".$remise['remise']."\"></td></tr>
    <tr><td>Montant total TTC bordereau</td><td id=\"totalttc\">".$remise['total']." &euro;</td></tr>
    </table>";

    $requete=$DB_site2->query("SELECT * FROM cheque WHERE remise='".$_GET['modif']."'");
    while($cheque=$DB_site2->fetch_array($requete)){
	$id=mt_rand();
	$chequelist.="<p id=\"$id\"><img src='images/supprimer.gif' onclick='rmcheque($id)'> n°commande <input type='text' name='facturid[$id]' value=\"".$cheque['facturid']."\" onkeyup='srcfacture(this,$id)'> montant TTC : <input type='text' name='montant[$id]' value=\"".$cheque['montant']."\"></p>";
    }
    print "<p><img src=\"images/plus.jpg\" onclick=\"newcheque()\" align=\"absmiddle\"> Chèques :</p>
    <div id=\"chequelist\"$chequelist</div>
    <p><input type=\"submit\" value=\"Enregistrer\"> <input type=\"button\" value=\"Annuler\" onclick=\"document.location='remise_cheque.php';\"></p>
    <div id=\"cheque_result\" style=\"position:absolute;background-color:white;border:solid 1px black;display:none;\"></div>
    </form>";
   
}elseif (isset($_GET['action']) && $_GET['action'] == 'details' ) {
  $tab = "<table style=\"text-align:center;\">";
  $tab .="<tr style=\"text-align:center; font-weight:bold;\"><td width=\"100\">Commande</td><td width=\"100\">Montant</td></tr>";
  $remise = $DB_site2->query("SELECT * FROM cheque WHERE remise ='".$_GET['remise']."'");
  while($chq = $DB_site2->fetch_array($remise)){
    if($i > 1) $i = 0;
    $tab .= "<tr class=\"td_alt$i\"><td>$chq[facturid]</td><td>$chq[montant]</td></tr>";
    $i++;

  }
  $tab.="</table>";

  echo $tab;

}else{
    print "<a href=\"remise_cheque.php?add=1\">Ajouter</a>
    <table>
    <tr><td>Date remise</td><td>Utilisateur</td><td>N° remise</td><td>Nbr chèques</td><td>Montant TTC</td><td></td></tr>";
    $mardi=mktime(0,0,0);
    while(date("w",$mardi)!=2) $mardi-=86400;

    $requete=$DB_site2->query("SELECT *,SUM(montant) AS total,COUNT(*) AS nbcheque FROM cheque GROUP BY remise ORDER BY date DESC");
    while($remise=$DB_site2->fetch_array($requete)){
	print "<tr><td>".date("d/m/Y",$remise['date'])."</td><td>".$remise['utilisateur']."</td><td>".$remise['remise']."</td><td>".$remise['nbcheque']."</td><td>".$remise['total']." &euro;</td><td>".($remise['date']>=$mardi?"<img src=\"images/editer.gif\" onclick=\"document.location='remise_cheque.php?modif=".$remise['remise']."'\"> <img src=\"images/supprimer.gif\" onclick=\"if(confirm('Etes-vous sûr de vouloir supprimer cette remise de chèque ?')) document.location='remise_cheque.php?del=".$remise['remise']."'\">":"")."</td><td> <a href=\"remise_cheque.php?action=details&remise=$remise[remise]\">Détails</a></td></tr>";
    }
    print "</table>";
}

include "./footer.php";

?>