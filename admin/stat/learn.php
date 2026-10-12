<?php

include("../../config.php");

if(isset($_POST['data'])){
     switch($_POST['type']){
          case "fai":
          mysql_query("INSERT INTO fai SET fai='".$_POST['kw']."', name='".$_POST['name']."', site='".$_POST['site']."'");
          break;

          case "mr":
          mysql_query("INSERT INTO mr SET keyword='".$_POST['kw']."', name='".$_POST['name']."'");
          break;

          case "os":
          mysql_query("INSERT INTO os SET kw='".$_POST['kw']."', name='".$_POST['name']."'");
          break;

          case "browser":
          mysql_query("INSERT INTO browser SET kw='".$_POST['kw']."', name='".$_POST['name']."'");
          break;
     }
     mysql_query("DELETE FROM stat_unknown WHERE data='".$_POST['data']."'");
}

if(!isset($_GET['add'])){

     $requete=mysql_query("SELECT * FROM stat_unknown");
     print "<table>
     <tr><td>".mysql_num_rows($requete)."</td></tr>";
     while($row=mysql_fetch_object($requete)){
          print "<tr><td>$row->data</td><td>$row->type</td><td><a href=\"learn.php?add=$row->type&data=$row->data\">Ajouter</a></td></tr>";
     }
     print "</table>";
}else{
    $types=array("fai","mr","os","browser");
    print "<p>Ajouter ".$_GET['data']." en ".$_GET['type']."</p>
    <form action=\"learn.php\" method=\"post\">
    <input type=\"hidden\" name=\"data\" value=\"".$_GET['data']."\">
    <p><select name=\"type\">";
    foreach($types as $t){
            print "<option value=\"$t\"";
            if($_GET['type']==$t) print " selected";
            print "\">$t</option>";
    }
    print "</select></p>
    <p>Nom : <input type=\"text\" name=\"name\"></p>
    <p>kw : <input type=\"text\" name=\"kw\"></p>
    <p>Site : <input type=\"text\" name=\"site\"></p>
    <p><input type=\"button\" value=\"Annuler\" onclick=\"document.location='learn.php';\"> <input type=\"submit\"></p>
    </form>";
}


?>