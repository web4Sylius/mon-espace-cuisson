<?php
header('Content-Type: xml');
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$artid = intval($_GET['artid']);
$option_id = isset($_GET['caractvalid']) && !empty($_GET['caractvalid']) ? $_GET['caractvalid'] : '';

if(!empty($artid) && !empty($option_id)){
     $xml_return = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
     $xml_return .= "<xml>";
     $sql = "SELECT *";
     $sql .= " FROM article_carac_incompatibilite";
     $sql .= " WHERE article_carac_incomp_liste LIKE '%|".$option_id."|%'";
     $sql .= " AND artid = ".$artid;
     $tabCompatibilite = $DB_site->query($sql);
     while($ligne = $DB_site->fetch_array($tabCompatibilite)){
          $tabActivation = array();
          $tabTemp = explode('|', $ligne['article_carac_incomp_liste']);
          foreach($tabTemp as $option_temp){
               if($option_temp != '' && $option_temp != $option_id){
                    $sql = "SELECT caractid";
                    $sql .= " FROM caracteristiquevaleur";
                    $sql .= " WHERE caractvalid = ".$option_temp;
                    $caracteristique_id = $DB_site->query_first($sql)['caractid'];
                    
                    if(!isset($tabActivation[$caracteristique_id]) || (isset($tabActivation[$caracteristique_id]) && !in_array($option_temp, $tabActivation[$caracteristique_id]))){
                         $tabActivation[$caracteristique_id][] = $option_temp;
                    }
               }
          }
          //Vérification de la présence d'options à réactiver
          if(!empty($tabActivation)){
               $xml_return .= "<incompatibilite value=\"".$ligne['article_carac_incomp_id']."\">";
               //Création du xml avec les options à réactiver
               foreach($tabActivation as $caracteristique_id => $listeOptions){
                    $xml_return .= "<caracteristique value=\"".$caracteristique_id."\">";
                    foreach($listeOptions as $l_option){
                         $xml_return .= "<option>".$l_option."</option>";
                    }
                    $xml_return .= "</caracteristique>";
               }
               $xml_return .= "</incompatibilite>";
          }
     }
     $xml_return .= "</xml>";
     echo $xml_return;
}

?>