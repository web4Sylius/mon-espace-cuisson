<?php

include("admin_global.php");

if(isset($_POST['contenu'])){
    $ret=$DB_site2->query("UPDATE template SET $_POST[langue]='$_POST[contenu]' WHERE templateid=".$_POST['templateid']);
    if($ret==false) 
      print mysqli_error($DB_site2->link_id);
    else
      print "Mise à jour effectuée";
}

$data="";
$libelle="";
if(isset($_POST['libelle'])){
  $requete=$DB_site2->query("SELECT * FROM template WHERE libelle ='".$_POST['libelle']."'");
  if($DB_site2->num_rows($requete)==1){
    $t=$DB_site2->fetch_array($requete);
    $data="
      <form method=\"post\">
      <input type=\"hidden\" name=\"langue\" value=\"$_POST[langue]\">
      <input type=\"hidden\" name=\"templateid\" value=\"$t[templateid]\">
      <input type=\"hidden\" name=\"libelle\" value=\"$t[libelle]\">
      <input type=\"submit\" style=\"color:red;\" value=\"Enregistrer\">
      <br><textarea name=\"contenu\" style=\"width:100%; height:600px;\">".$t[$_POST['langue']]."</textarea>
      </form>";
    $libelle=$t['libelle'];
  }else{
    $requete=$DB_site2->query("SELECT * FROM template WHERE libelle LIKE '%".$_POST['libelle']."%'");
    if($DB_site2->num_rows($requete)>1){
      $data="<p>Résultats</p>";
      while($t=$DB_site2->fetch_array($requete)){
	  $data.="<li><a href=\"javascript:submitform('$t[libelle]');\">$t[libelle]</a>";
      }
      $libelle=$_POST['libelle'];
    }else
      $data="<p>Aucun résultat</p>";
  }
}

print "<script language=\"javascript\">
function submitform(libelle){
    document.getElementsByName('libelle')[0].value=libelle;
    document.forms.src.submit();
}
</script>";

print "<form name=\"src\" method=\"post\">
<table>
<tr><td>Libelle</td><td><input type=\"text\" name=\"libelle\" value=\"$libelle\"></td></tr>
<tr><td>Langue</td><td><select name=\"langue\"><option value=\"contenuFR\">FR</option></select></td></tr>
<tr><td><input type=\"submit\" value=\"OK\"></td></tr>
</table>
</form>";

print "<p>$_POST[langue]</p>";

print $data;

?>