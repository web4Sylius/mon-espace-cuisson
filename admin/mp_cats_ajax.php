<?php
include("admin_global.php");
include("mpfields.php");

header('Content-Type: text/html; charset=utf-8');

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

if(!isset($_GET['tags'])) $_GET['tags']="";

if(isset($_POST['catid'])){
    //Récupération d'un article aléatoire pour générer le texte de test
    $sql = "SELECT artid, artcode";
    $sql .= " FROM article";
    $sql .= " WHERE active = '1'";
    $sql .= " AND catid = ".$_POST['catid'];
    $sql .= " ORDER BY RAND()";
    $sql .= " LIMIT 1";
}

if(isset($_POST['artid'])){
    $sql = "SELECT artid, artcode, catid";
    $sql .= " FROM article";
    $sql .= " WHERE active = '1'";
    $sql .= " AND artid = ".$_POST['artid'];
}

$artid = $DB_site2->query_first($sql);


echo ""
. "<table>"
     . "<tr>"
          . "<td colspan=\"2\">Test ".$artid['artcode']."</td>"
     . "</tr>";

if(isset($_POST['mp'])){
    $sql = "SELECT *";
    $sql .= " FROM market_place_catid";
    $sql .= " WHERE 1";
    $sql .= " AND catid = ".$artid['catid'];
    $sql .= " AND market_place LIKE '".$_POST['mp']."'";
    $req = $DB_site2->query($sql);
    while($field=$DB_site2->fetch_array($req)){
        echo ""
            . "<tr>"
                . "<td>".$field['champ']."</td>"
                . "<td>".mpfields($artid['artid'],$field['valeur'],$_POST['tags_'.$name])."</td>"
            . "</tr>";
    }
}else{
    foreach($_POST as $name => $value){
        if(substr($name,0,6) == "field_"){
            $name = substr($name,6);
            echo ""
            . "<tr>"
                . "<td>".$name."</td>"
                . "<td>".mpfields($artid['artid'],$value,$_POST['tags_'.$name])."</td>"
            . "</tr>";
        }
    }
}
echo "</table>";

foreach($_POST as $name => $value){
     if(substr($name,0,6) == "limit_" && $value!=0){
          $name = substr($name,6);
          
          $sql = "SELECT artid, artcode";
          $sql .= " FROM article";
          $sql .= " WHERE active = '1'";
          $sql .= " AND catid = ".$_POST['catid'];
          $arts = $DB_site2->query($sql);
          
          while($art = $DB_site2->fetch_array($arts)){
               $fieldvalue = mpfields($art[0],$_POST['field_'.$name]);
               $fieldlen = strlen($fieldvalue);
               if($fieldlen > $value){
                    print "<p><span style=\"color:red;\">Erreur longueur ".$name." ".$art[1]." ".$value." (+".($fieldlen-$value).")</span> ".$fieldvalue."</p>";
                    break;
               }
	}
    }
}

?>
