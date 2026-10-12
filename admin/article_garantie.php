<?php
$sql = "SELECT paysid,libelle,typepays";
$sql .= " FROM pays";
$sql .= " WHERE 1";
$sql .= " ORDER BY libelle";
$req = $DB_site2->query($sql);

$optpays = "<option value=\"\">Tout</option>";
while($pays = $DB_site2->fetch_array($req)){
    
     $optpays.="<option value=\"".($pays['typepays']=="EE"?$pays['paysid']:$pays['typepays'])."\">".$pays['libelle']."</option>";
}

$contenu_onglet .= ""
. "<font class=\"cost\">".$erreur."</font>"
. "<h3>Garanties</h3>"
. "Filtrer un pays : <select onchange=\"filter_pays(this.value)\">".$optpays."</select>"
. "<form name=\"formu\" action=\"ajout_produit.php\" method=\"post\">"
     . "<input type=\"hidden\" name=\"old\" value=\"\">"
     . "<input type=\"hidden\" name=\"quitter\" value=\"\">"
     . "<input type=\"hidden\" name=\"mq2\" value=\"\">"
     . "<input type=\"hidden\" name=\"action2\" value=\"\">"
     . "<input type=\"hidden\" name=\"modif\" value=\"0\">"
     . "<input type=\"hidden\" name=\"artid\" value=\"".$artid."\">"
     . "<input type=\"hidden\" name=\"catid\" value=\"".$catid."\">";

function in_record_array($needle, $haystack, $column) {
        
     $ok = false;
     
     for($i=0; $i < count($haystack); $i++) {
          
          if ($needle == $haystack[$i][$column]) {
                  
               $ok=$i;
          }
     }
     
     return $ok;
}

function in_object_array($needle, $haystack, $field) {
     
     $ok = false;
     
     for ($i=0; $i < count($haystack); $i++) {
     
          if ($needle==$haystack[$i]->$field) {
             
               $ok[]=$i;
          }
     }
     
     return $ok;
}

function triTableau($tabGarantie,$DB,$artid,&$nbFourPrin,&$nbFourGar,&$nbFourSec) {
     $tabTri     = array();
     $etp        = 1;
     $nbFourPrin = 0;
     $nbFourGar  = 0;
     $nbFourSec  = 0;
     
     while ($etp<=3) {
          
          switch ($etp) {
               
               case 1:
                    
                    $sql = "SELECT DISTINCT(fournisseur)";
                    $sql .= " FROM article_fournisseur";
                    $sql .= " WHERE (principal = 'base' || principal = 'uk' || principal = 'base,uk')";
                    $sql .= " AND artid = ".$artid;
                    $rNbFournisseur = $DB->query($sql);
                    
                    $nbFour = 'nbFourPrin';
                    
                    break;
               
               case 2:
                    
                    $sql = "SELECT DISTINCT(fournisseur)";
                    $sql .= " FROM garantie";
                    $sql .= " WHERE fournisseur IN (";
                    $sql .= "                SELECT DISTINCT(societe)";
                    $sql .= "                FROM fournisseur";
                    $sql .= "                WHERE type = \"garadv\"";
                    $sql .= ")";
                    $rNbFournisseur = $DB->query($sql);
                    
                    $nbFour = 'nbFourGar';
                    
                    break;
               
               case 3:
                    
                    $sql = "SELECT DISTINCT(fournisseur)";
                    $sql .= " FROM article_fournisseur";
                    $sql .= " WHERE principal = ''";
                    $sql .= " AND artid = ".$artid.";";
                    $rNbFournisseur = $DB->query($sql);
                    
                    $nbFour = 'nbFourSec';
                    
                    break;
          }
          
          $nbFournisseur = $DB->num_rows($rNbFournisseur);
          
          if($nbFournisseur>0){
               
               while($fournisseur = $DB->fetch_array($rNbFournisseur)){
                    
                    $pointer = in_object_array($fournisseur[0],$tabGarantie,"fournisseur");

                    if (is_array($pointer)){
                         
                         //variable dynamique qui reçoit le nom de la bonne variable à changer
                         ${$nbFour} += count($pointer);
                         
                         for($i=0; $i < count($pointer); $i++){
                              
                              $tabTri[] = $tabGarantie[$pointer[$i]];
                         }
                    }
               }
          }
          
          $etp++;
     }
     
     return $tabTri;
}

$sql = "SELECT COUNT(lotid)";
$sql .= " FROM article_lot";
$sql .= " WHERE lotid = ".$artid;
$lot = $DB_site2->query_first($sql);

if($lot[0] == 0){ //Article classique (non "lot")

     $typearticle       = DAOarticle::typeArticleByIDart($DB_site2,'FR',$artid);
     $marque            = DAOarticle::marqueArticleByIDart($DB_site2,'FR',$artid);
     $garantiesAvantTri = DAOgarantie::loadGarantiesByTypeMarque($DB_site2,null,'FR',$typearticle["typearticleid"],$marque["marqueid"]);

     $garanties = triTableau($garantiesAvantTri,$DB_site2,$artid,$nbFourPrin,$nbFourGar,$nbFourSec);
     $garantiesInArt = DAOgarantie::checkGarantiesArt($DB_site2,'FR',$artid);
     
     $contenu_onglet .= "<input type=\"checkbox\" onClick=\"checkAll(this,'checkFournisseur[]')\" style=\"margin-left:450px;\"/>Tout cocher<br/>";

     for($i=0; $i < count($garanties); $i++) {
          
          $checked = "";
          
          $paht = $garanties[$i]->paht;
          $prix = $garanties[$i]->prixsites;
          $ppi  = $garanties[$i]->ppisites;
          if($garantiesInArt != ""){
               
               $pointer = in_record_array($garanties[$i]->garantieid,$garantiesInArt,2);
               if($pointer !== false) {
                    
                    $checked = "checked";
               }
          }

          switch ($i) {
               case 0:
                    $contenu_onglet .="<h4>Fournisseur principal</h4>";
                    break;
               case $nbFourPrin:
                    $contenu_onglet .="<h4>Fournisseur(s) de garanties</h4>";
                    break;
               case $nbFourPrin+$nbFourGar:
                    $contenu_onglet .="<h4>Fournisseur(s) secondaire(s)</h4>";
                    break;
          }

          $cumul="";
          if ($garanties[$i]->cumul==1) {
               $cumul="Extension +";
          }
          $disabled = " disabled";

          $contenu_onglet .= ""
          . "<table id=\"garantie".$garanties[$i]->garantieid."\" class=\"table_garantie ".($checked!=""?"table_garantie_selected":"")."\">"
               . "<tr>"
                    . "<td class=\"garantieid\"><a href=\"garanties.php?action=modifier&garantieid=".$garanties[$i]->garantieid."\" style=\"text-decoration: none;\">Garantie n°<b>".$garanties[$i]->garantieid."</b></a></td>"
                    . "<td class=\"title\"><a href=\"garanties.php?action=modifier&garantieid=".$garanties[$i]->garantieid."\" style=\"text-decoration: none; color: #0000aa;\">".$cumul.$garanties[$i]->duree."  an".($garanties[$i]->duree>1?"s":"")." ".$garanties[$i]->getTypeGarantie()." ".$garanties[$i]->fournisseur."</a></td>"
                    . "<td rowspan=\"2\" style=\"vertical-align: top;\"><img src=\"images/icon/".($garanties[$i]->destinataire_garantie == 1 ? 'particulier.png' : 'pro.png')."\" style=\"float: right;\"><div class=\"clear\"></div><input type=\"checkbox\" name=\"checkFournisseur[]\" value=\"".$garanties[$i]->garantieid."\" ".$checked.$disabled."/>"
               . "</tr>"
               . "<tr>"
                    . "<td>".$garanties[$i]->comm."</td>"
                    . "<td class=\"pays\">".$garanties[$i]->getPays()."</td>"
               . "</tr>";
          $contenu_onglet .= "</table>";

          $jstri .= "tripays[".$garanties[$i]->garantieid."]=new Array('".implode("','",$garanties[$i]->pays)."');\n";
     }
     
} else { //Article de type "Lot"
	
    $contenu_onglet .= "<p class=\"recap_alert\">Cet article est un lot, les garanties sont configurées automatiquement</p>";
    
    $qte = DAOgarantie::lotGaranties($DB_site2,$artid);
    $garantiesAvantTri = DAOgarantie::checkGarantiesArt($DB_site2,"FR",$artid);
     
    if(is_array($garantiesAvantTri) && count($garantiesAvantTri)>0){
        $garanties = array();
        
        for ($i=0; $i < count($garantiesAvantTri); $i++) {
            
            $garanties[$i] = new garantie($DB_site2,null,"FR",$garantiesAvantTri[$i]['garantieid'],$artid);
        }

        $garanties = triTableau($garanties,$DB_site2,$artid,$nbFourPrin,$nbFourGar,$nbFourSec);
                
        for($i=0; $i<count($garanties); $i++){
            
            $checked = "";
            
            $paht = $garanties[$i]->paht;
            $prix = $garanties[$i]->prixsites;
            $ppi  = $garanties[$i]->ppisites;

            switch($i){
                case 0:
                        $contenu_onglet .="<h4>Fournisseur principal</h4>";
                        break;
                case $nbFourPrin:
                        $contenu_onglet .="<h4>Fournisseur(s) de garanties</h4>";
                        break;
                case $nbFourPrin+$nbFourGar:
                        $contenu_onglet .="<h4>Fournisseur(s) secondaire(s)</h4>";
                        break;
            }

            $cumul = "";
            if($garanties[$i]->cumul==1) {
                
                $cumul = "Extension +";
            }
                
            $contenu_onglet .= ""
            . "<table id=\"garantie".$garanties[$i]->garantieid."\" class=\"table_garantie table_garantie_selected\">"
                . "<tr>"
                        . "<td class=\"garantieid\"><span class=\"multi\">".$qte." &#10005;</span> <a href=\"garanties.php?action=modifier&garantieid=".$garanties[$i]->garantieid."\" style=\"text-decoration: none;\">Garantie n°<b>".$garanties[$i]->garantieid."</b></a></td>"
                        . "<td class=\"title\">".$cumul.$garanties[$i]->duree."  an".($garanties[$i]->duree>1?"s":"")." ".$garanties[$i]->getTypeGarantie()." ".$garanties[$i]->fournisseur."</td>"
                        . "<td rowspan=\"2\" style=\"vertical-align: top;\"><img src=\"images/icon/".($garanties[$i]->destinataire_garantie == 1 ? 'particulier.png' : 'pro.png')."\" style=\"float: right;\"></td>"
                . "</tr>"
                . "<tr>"
                        . "<td>".$garanties[$i]->comm."</td>"
                        . "<td class=\"pays\">".$garanties[$i]->getPays()."</td>"
                . "</tr>"
            . "</table>";

            $jstri .= "tripays[".$garanties[$i]->garantieid."]=new Array('".implode("','",$garanties[$i]->pays)."');\n";
        }
    }
}

	$contenu_onglet .= "</form>";
	
	$contenu_onglet.=<<<HTML
	<script language="javascript">
	var tripays=new Array();
	$jstri
	
	function filter_pays(paysid){
	    if(paysid=="") paysid=undefined;
	    var tables=document.getElementsByTagName("table");
	    for(i=0;i<tables.length;i++){
		if(tables[i].id.substr(0,8)=="garantie"){
		    var garantieid=tables[i].id.substr(8);
		    tables[i].style.display=(paysid==undefined || in_array(paysid,tripays[garantieid])?"table":"none");
		}
	    }
	}
	
	function in_array(value,array){
	    for(v in array)
	      if(array[v]==value) return true;
	      
	    return false;
	}
	</script>
HTML;

?>
