<?php

$contenu_onglet.="<h3>Suivi - Historique</h3>
<style>
#suiviarticle { width:100%; margin-top:10px; }
#suiviarticle .suivi { background-color:#ddd;}
#suiviarticle .modif { background-color:#ccccff; }
.tddate { width:50px; }
.highlight { cursor:help; }
.highlight:hover { background-color:white; }
</style>
<script language=\"javascript\">
function filter(){
    var displaysuivi=document.getElementById('chksuivi').checked;
    var displaymodif=document.getElementById('chkmodif').checked;
    var tbl=document.getElementById('suiviarticle');
    var trs=tbl.getElementsByTagName('tr');
    for(i=0;i<trs.length;i++){
	switch(trs[i].className){
	    case 'modif':
	    trs[i].style.display=(displaymodif?'table-row':'none');
	    break;
	    case 'suivi':
	    trs[i].style.display=(displaysuivi?'table-row':'none');
	    break;
	}
    }
}

function highlight(field){
    field=field+' ';
    var tbl=document.getElementById('suiviarticle');
    var spans=tbl.getElementsByTagName('span');
    for(i=0;i<spans.length;i++){console.debug(spans[i].className);
	if(spans[i].className=='highlight' && spans[i].innerHTML.indexOf(field)==0)
	    spans[i].style.backgroundColor='yellow';
	else
	    spans[i].style.backgroundColor='';
    }
}
</script>
<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">
<input type=\"hidden\" name=\"old\" value=\"\">
<input type=\"hidden\" name=\"quitter\" value=\"\">
<input type=\"hidden\" name=\"mq2\" value=\"\">
<input type=\"hidden\" name=\"modif\" value=\"0\">
<input type=\"hidden\" name=\"action2\" value=\"\">
<input type=\"hidden\" name=\"artid\" value=\"$artid\">
<input type=\"hidden\" name=\"catid\" value=\"$catid\">";

$tr=array();
$req=$DB_site2->query("SELECT * FROM suivi_article WHERE artid=$artid ORDER BY date DESC");
while($suivi=$DB_site2->fetch_array($req)){
    $tr[$suivi['date']].="<tr class=\"suivi\"><td class=\"tddate\">".date("d/m/Y H:i",$suivi['date'])."</td><td>".$suivi['commercial']."</td><td>".formatsuivi($suivi['suivi'])."</td></tr>";
}

$contenu_onglet.="<b>Ajouter un suivi :</b><br>
<textarea name=\"suivi\" style=\"width:600px;height:100px;\"></textarea>
<br><input type=\"button\" value=\"Ajouter le suivi\" onclick=\"clique('suivi','suivi')\">";

$requete=$DB_site2->query("SELECT * FROM article_history WHERE artid=$artid ORDER BY date");

$m=array();
$last=array();
$paht=array();
$pv=array();

while($modif=$DB_site2->fetch_array($requete)){
      $m[$modif['date']][$modif['key']]=$modif['value'];
      $mu[$modif['date']]=$modif['utilisateur'];
}
ksort($m);


foreach($m as $date=>$fields){

      $showmodif=array();
      foreach($fields as $field=>$value){
	  if($last[$field]!=$value) $showmodif[]="<span class=\"highlight\" onclick=\"highlight('".$field."')\">".$field." : ".$template->translate($last[$field])." =&gt; ".$template->translate($value)."</span>";
	  $last[$field]=$value;
      }

      $tr[$date].="<tr class=\"modif\"><td class=\"tddate\" valign=\"top\">".date("d/m/Y H:i",$date)."</td><td valign=\"top\">".$mu[$date]."</td><td>".implode("<br>",$showmodif)."</td></tr>";
//       $last=$fields;
}

krsort($tr);
$contenu_onglet.="<p><input type=\"checkbox\" id=\"chksuivi\" onclick=\"filter()\" checked> <span style=\"background-color:#ddd;\">Suivis</span> &nbsp; <input type=\"checkbox\" id=\"chkmodif\" onclick=\"filter()\" checked> <span style=\"background-color:#ddddff;\">Modifications</span></p>";
$contenu_onglet.="<table id=\"suiviarticle\">".implode("",$tr)."</table>";

$contenu_onglet.="</form>";


//TODO: create a TaskManager/TaskTemplate method
$contenu_onglet.= "<h3>Suivi interne</h3>";
$si_query = $DB_site2->query('
  SELECT `type`, `id`, `name`, `short_desc`, `excepted_date`
  FROM `suivi_interne`
  WHERE `link_type` LIKE "article"
  AND `link` LIKE "'.mysqli_real_escape_string($DB_site2->link_id,$artid).'"
  ');
$contenu_onglet.= '
  <table style="width:100%;">
    <thead style="font-size:12px;">
      <tr>
        <th>ID</th><th>Nom</th><th>'.utf8_decode('Résumé').'</th><th>Date de fin</th>
      </tr>
    </thead>
  <tbody>';
while ($si = $DB_site2->fetch_array($si_query))
  $contenu_onglet.= '
    <tr class="suivi">
      <td>'.($si['type']=='suivi'?'SI':'SB').'#'.$si['id'].'</td>
      <td>'.$si['name'].'</td>
      <td>
      <a href="task.php?a=showTask&amp;task='
      .$si['id'].'&amp;'.$si['type'].'">'
        .$si['short_desc'].'</a>
      </td>
      <td>'.$si['excepted_date'].'</td>
    </tr>';
$contenu_onglet.= '
    </tbody>
  </table>';
?>