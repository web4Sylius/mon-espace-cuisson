<?php

// MULTISITE

require "admin_global.php";

include "./header.php";
include "./menu.php";

echo "<h1>Frais de port</h1>" ;
echo "<h3>Configuration des frais de port</h3>" ;

if (isset($action) and $action=="supprimer") {
    echo "<form name=\"formu\" method=\"post\" action=\"fraisport.php\">";
       
        echo "<input type=\"hidden\" name=\"action\" value=\"20fraisport\">";
        $requete="delete from fraisport where fraisportid=$_POST[supprimer_fraisportid]";
        $paysid=$DB_site2->query($requete);
        echo"<script language=\"javascript\">document.formu.submit();</script>";
    echo "</form>";

}
if (isset($action) and $action=="ajouter") {
    echo "<form name=\"formu\" method=\"post\" action=\"fraisport.php\">";
       
        echo "<input type=\"hidden\" name=\"action\" value=\"20fraisport\">";
        
        $paysid=$_POST[ajouter_id];
        $requete="
            select count(*) from fraisport 
                where paysid = $paysid AND sites='$_POST[site_code]' AND(
                    (poiddebut>=$_POST[ajouter_poiddebut] and poidfin<=$_POST[ajouter_poidfin])
                    OR (poiddebut<=$_POST[ajouter_poiddebut] and poidfin>=$_POST[ajouter_poidfin])
                    OR (poiddebut<=$_POST[ajouter_poiddebut] and poidfin between $_POST[ajouter_poiddebut] and $_POST[ajouter_poidfin])
                    OR (poidfin>=$_POST[ajouter_poidfin] and poiddebut between $_POST[ajouter_poiddebut] and $_POST[ajouter_poidfin])
                )
        ";
        $combien=$DB_site2->query_first($requete);
        $combien=$combien[0];
        if ($combien>0)
            echo "<input type=\"hidden\" name=\"erreur\" value=\"conflit_ajout\">";
        else{
            echo "<input type=\"hidden\" name=\"erreur\" value=\"franco\">";
            $requete="insert into fraisport (poiddebut,poidfin,prix,paysid,sites) values ($_POST[ajouter_poiddebut],$_POST[ajouter_poidfin],$_POST[ajouter_prix],$paysid,'$_POST[site_code]')";
            $DB_site2->query($requete);
        }
        echo"<script language=\"javascript\">document.formu.submit();</script>";
    echo "</form>";

}

if(isset($action) && $action=="modifier"){
  $action="20fraisport";
  foreach($_POST as $c=>$valeur){
      if(substr($c,0,5)=="modif"){
	  $c=substr($c,6);

	  $DB_site2->query("UPDATE fraisport SET prix=".floatval($valeur).", achat=".floatval($_POST['achat_'.$c])." WHERE fraisportid=".intval($c));

      }
  }
        /*echo "<form name=\"formu\" method=\"post\" action=\"fraisport.php\">";
        foreach($_POST as $cle=>$valeur){ 
            if (substr($cle,0,5)=="pays_")
                echo "<input type=\"hidden\" name=\"$cle\" value=\"$valeur\">";
        }
        echo "<input type=\"hidden\" name=\"action\" value=\"20fraisport\">";
        echo "<input type=\"hidden\" name=\"erreur\" value=\"\">";
        $requete="update fraisport set prix=$_POST[ajouter_prix] WHERE fraisportid=$_POST[supprimer_fraisportid]";
        $DB_site2->query($requete);
        echo"<script language=\"javascript\">document.formu.submit();</script>";
        echo "</form>";*/
}

if (isset($action) and ($action=="20fraisport" || $action=="21fraisport")) 
    {
    echo "<h2>Etape 2 : Montants</h2>";
    $nb_pays=0;
    if ($erreur == "conflit_ajout") 
        {
        echo "<font face=\"verdana\" size=\"2\" color=\"#FF0000\">
                <b>L'intervalle de poid entre en conflit avec une règle déja définie pour ce pays, l'ajout a donc échoué.</b>
            </font><br>";
        }
    if ($erreur == "franco") 
        {
        echo "<font face=\"verdana\" size=\"2\" color=\"#FF0000\">
                <b>Pour valider votre nouvelle section de poids, merci de valider imperativement la valeur du <a href=\"fraisport2.php\">franco de port</a> du pays</b>
            </font><br>";
        }
    $listediminutifs = "" ;
    
    $fpsite=array();
    if($action=="21fraisport"){
	$sql="SELECT * FROM pays WHERE paysid IN (0";
	foreach($_POST['pays'] as $site_code=>$listepays){
	    foreach($listepays as $paysid=>$valeur){
		if($valeur==-1)
		    $DB_site2->query("DELETE FROM fraisport WHERE paysid=".$paysid." AND sites='".$site_code."'");
		elseif($valeur==0){
		    $sql.=",".$paysid;
		    $DB_site2->query("UPDATE fraisport SET zone_tnt=0 WHERE paysid=".$paysid." AND sites='".$site_code."'");
		    $fpsite[$paysid][]=$site_code;
		}else{
		    $DB_site2->query("DELETE FROM fraisport WHERE paysid=".$paysid." AND sites='".$site_code."'");
		    $DB_site2->query("INSERT INTO fraisport SET poiddebut=0, poidfin=1000000,paysid=".$paysid.",zone_tnt=".$valeur.", sites='".$site_code."'");
		}
	    }
	 }
	$sql.=")";
    }else
	$sql="SELECT * FROM fraisport WHERE zone_tnt=0 GROUP BY paysid";
	
    $requete=$DB_site2->query($sql);

    while($fp=$DB_site2->fetch_array($requete)){
	  $fpsites=$DB_site2->query("SELECT sites FROM fraisport WHERE paysid=".$fp['paysid']." AND zone_tnt=0 GROUP BY sites");
	  while($site=$DB_site2->fetch_array($fpsites)){
	      $fpsite[$fp['paysid']][]=$site[0];
	  }
	  
	  $fpsite[$fp['paysid']]=array_unique($fpsite[$fp['paysid']]);
	  
	  foreach($fpsite[$fp['paysid']] as $site_code){
		$form_name="form_".$site_code."_".$fp[paysid];
		echo "<form name=\"".$form_name."\" method=\"post\">
		<input type=\"hidden\" name=\"site_code\" value=\"$site_code\">
		<input type=\"hidden\" name=\"action\" value=\"on\">";
	      
		$nb_pays++;
		$id=$fp['paysid'];
		$listediminutifs .= "'$id'," ;
		$libelle=$DB_site2->query_first("select diminutif,libelle from pays where paysid='$id'");
		$diminutif=$libelle[0];
		$pays=$libelle[1];
		$devise_vente=$sites[$site_code]['devise'];
	    
		echo "<table class=\"table_modules_boutiques\">";
			
			echo "<tr>
			  <td colspan=\"3\" class=\"td_modules_boutiques\" style=\"font-size:16px;\"><img src=\"./images/drapeaux/br/".$diminutif.".gif\" border=\"0\" align=\"absmiddle\"> ".$pays." site <b>".$site_code."</b></td>
			</tr>
			<tr>
			  <td class=\"td_head_comparateurs\">Poids</td>
			  <td class=\"td_head_comparateurs\">Prix de vente TTC</td>
			  <td class=\"td_head_comparateurs\">Prix achat moyen HT<br><small style=\"font-weight:normal;\">laisser 0 pour utiliser tarif FEDEX Express</small></td>
			  <td class=\"td_head_comparateurs\">action</td>
			</tr>";
			

		$pays_port=$DB_site2->query("select f.* from fraisport as f where f.paysid='$id' AND zone_tnt=0 AND sites='".$site_code."' order by f.poiddebut");
		$i=0;
		while ($pay_port=$DB_site2->fetch_array($pays_port)){
		  
		  $i++;
		    echo "<tr>
			    <td class=\"td_promos_alt1\">de $pay_port[poiddebut] à $pay_port[poidfin] Gr.</td>
			    <td class=\"td_promos_alt1\" align=\"center\"><input type=\"text\" name=\"modif_$pay_port[fraisportid]\" id=\"modif_$pay_port[fraisportid]\" value=\"$pay_port[prix]\" size=\"4\">".$devise_vente."</td>
			    <td class=\"td_promos_alt1\" align=\"center\"><input type=\"text\" name=\"achat_$pay_port[fraisportid]\" value=\"$pay_port[achat]\" size=\"4\">&euro;</td>
			    <td class=\"td_promos_alt1\" align=\"center\"><input type=\"button\" class=\"bouton1\" onclick=\"supprimer($pay_port[fraisportid])\" value=\"Supprimer\"></td>
			</tr>";
		}
			
		if ($i==0)
		    {
		    echo "<tr>
			    <td colspan=\"3\" class=\"td_promos_alt2\><font face=\"verdana\" size=\"2\"><b>Aucun frais de port n'a été défini pour ce pays</b></font></td>
			</tr>";
		    }
		echo "<tr>
			<td colspan=\"2\" class=\"td_promos_alt2\" align=\"center\"> 
			    De <input type=\"text\" ID=\"poiddebut_".$form_name."\" size=5>
			      grammes à <input type=\"text\" ID=\"poidfin_".$form_name."\" size=5>
			      grammes : <input type=\"text\" ID=\"prix_".$form_name."\" size=3> $devise_vente
			</td>
			<td class=\"td_promos_alt2\" align=\"center\"><input type=\"button\" class=\"bouton1\" onclick=\"ajouter('".$form_name."')\" value=\"Ajouter\"> <input type=\"button\" value=\"Mettre à jour les prix\" onclick=\"document.forms.".$form_name.".action.value='modifier';document.forms.".$form_name.".submit();\"></td>
		    </tr>";
		echo "</table><br>
		</form>";
          }
     }
     }
     echo "<p></p>";

    //$listediminutifs = substr($listediminutifs, 0, -1);
    //$DB_site2->query("delete from fraisport where paysid IN (select paysid from pays where paysid NOT IN ($listediminutifs))");
    echo "<form name=\"formu\" method=\"post\" action=\"fraisport.php\">";
    echo "<input type=\"hidden\" name=\"action\">
	    <input type=\"hidden\" name=\"site_code\">
	    <input type=\"hidden\" name=\"ajouter_id\"> 
            <input type=\"hidden\" name=\"ajouter_poiddebut\"> 
            <input type=\"hidden\" name=\"ajouter_poidfin\"> 
            <input type=\"hidden\" name=\"ajouter_prix\">  ";
    echo "<input type=\"hidden\" name=\"supprimer_fraisportid\">
    </form>";
    if ($nb_pays==0)
        echo "<br><br>Erreur : Vous n'avez selectionné aucun pays.<br><br>";


    ?>
    <script language="javascript">
    function modifier(id){
          document.formu.supprimer_fraisportid.value=id;
          document.formu.ajouter_prix.value=parseFloat(document.getElementById('modif_'+id).value);
          document.formu.action.value="modifier";
          document.formu.submit();
    }

    function ajouter(form_name)
        {
        var id=form_name.split('_')[2];
        document.formu.ajouter_id.value=id;
        document.formu.site_code.value=form_name.split('_')[1];
        document.formu.ajouter_poiddebut.value=document.getElementById("poiddebut_"+form_name).value;
        document.formu.ajouter_poidfin.value=document.getElementById('poidfin_'+form_name).value;
        document.formu.ajouter_prix.value=document.getElementById('prix_'+form_name).value;
        if (document.formu.ajouter_poiddebut.value=="" || document.formu.ajouter_poidfin.value=="" || document.formu.ajouter_prix.value=="")
            {
            alert ("Vous devez choisir un poid minimum, maximum et un prix avant de cliquer sur ajouter");
            }
        else
            {
            if (isNaN(document.formu.ajouter_poiddebut.value) || isNaN(document.formu.ajouter_poidfin.value) || isNaN(document.formu.ajouter_prix.value))
                {
                alert ("Vous devez choisir un poid minimum, maximvar_dump($valeur);um et un prix NUMERIQUES avant de cliquer sur ajouter");
                }
            else
                {
                if (document.formu.ajouter_poiddebut.value<0 || document.formu.ajouter_poidfin.value<0 || document.formu.ajouter_prix.value<0)
                    {
                    alert ("Vous devez choisir un poid minimum, maximum et un prix numériques et POSITIFS de cliquer sur ajouter");
                    }
                else
                    {
                    if (document.formu.ajouter_poidfin.value-document.formu.ajouter_poiddebut.value<0)
                        {
                        alert ("Le poid de debut doit être inférieur au poid de fin");
                        }
                    else
                        {
                        document.formu.action.value="ajouter";
                        document.formu.submit();
                        }
                    }
                }
            }
        }
    
    function supprimer(fraisportid)
        {
        document.formu.action.value="supprimer";
        document.formu.supprimer_fraisportid.value=fraisportid;
        document.formu.submit();
        }
    </script>
    
<?php
    

if (!isset($action) or $action=="") 
{

if (isset($min) and $min != '')
    {
    if (!is_numeric ($min))
        {
        echo "<font color=\"#FF0000\"><b>ATTENTION, vous devez saisir une valeur numérique !</b></font>" ;
        }
    else
        {
        $recup=$DB_site2->query_first("select pourcentage_default_affilie from parametresgeneraux");
        $recup_info = $recup[pourcentage_default_affilie] ;
        // update pour valeur
        /*$update_valeur=$DB_site2->query("UPDATE parametresgeneraux 
                                  SET minimum_frais_port_gratuit = '$min'
                                  where pourcentage_default_affilie = '$recup_info'");*/
        echo "<font color=\"#FF0000\">Le montant minimum pour bénéficier d'une gratuité des frais de port a bien été <strong>ajouté</strong>.<br>Vous pouvez le modifier ou le supprimer à tout moment sur cette page.<br></font>" ;
        }
    }
if (isset($del))
    {
    $recup=$DB_site2->query_first("select pourcentage_default_affilie from parametresgeneraux");
    $recup_info = $recup[pourcentage_default_affilie] ;
    // update pour -1
    /*$update_null=$DB_site2->query("UPDATE parametresgeneraux 
                                   SET minimum_frais_port_gratuit = '-1'
                                   where pourcentage_default_affilie = '$recup_info'");*/
    echo "<font color=\"#FF0000\">Le montant minimum pour bénéficier d'une gratuité des frais de port a bien été <strong>supprimé</strong>.<br>Vous pouvez le saisir à nouveau à tout moment sur cette page.<br></font>" ;
    }



    echo "<form name=\"formu\" method=\"post\" action=\"fraisport.php\">";
    echo "<h2>Etape 1 : Pays de livraison</h2>Choisissez parmi les pays ci-dessous ceux dans lesquels vous désirez livrer vos produits.<br><br>";
    echo "<table>
    <tr><td></td><td></td>";
    
    foreach($sites as $site_code=>$site){
	echo "<td style=\"font-size:16px; text-align:center;\">$site_code</td>";
    }
    echo "</tr>";
    
    $tnt=$DB_site2->query_first("SELECT MAX(zone) FROM tnt_grille");
    $zonetntnb=$tnt[0];
    
    $pays_port=$DB_site2->query("SELECT distinct p.diminutif as dim, p.paysid, zone_tnt, sites from fraisport as f inner join pays as p on (f.paysid=p.paysid) order by p.libelle");
    $payspresents=array();
    while ($pay_port=$DB_site2->fetch_array($pays_port)) 
        {
        array_push($payspresents,$pay_port['paysid']);
        $zonepays[$pay_port['sites']][$pay_port['paysid']]=$pay_port['zone_tnt'];
        }
        
    $pays=$DB_site2->query("SELECT * from pays order by libelle");
    $nb_pays_tot=$DB_site2->num_rows($pays) ;
    $nb_pays = 0 ;
    $compteur = 0;
    while ($pay=$DB_site2->fetch_array($pays)) 
        {
	    
            echo "<tr>
		    <td><img border=\"0\" src=\"./images/drapeaux/br/$pay[diminutif].gif\"></td>
                    <td>$pay[libelle]</td>";
                    
                    foreach($sites as $site_code=>$site){
			$opts="<option value=\"0\"".($zonepays[$site_code][$pay['paysid']]===0?" selected":"").">National</option>";
			$opts.="<option value=\"-1\"".(!isset($zonepays[$site_code][$pay['paysid']])?" selected":"")." style=\"color:red\">Désactivé</option>";
			for($z=1;$z<=$zonetntnb;$z++)
			    $opts.="<option value=\"$z\"".($zonepays[$site_code][$pay['paysid']]==$z?" selected":"").">Zone FEDEX Express $z</option>";
			    
			print "<td><select name=\"pays[".$site_code."][".$pay['paysid']."]\">$opts</select></td>";
		    }
                    
	    echo "</tr>" ;
        }
    echo "</table>";
    echo "<input type=\"hidden\" name=\"action\" value=\"21fraisport\">";
    echo "<input type=\"submit\" value=\"suivant\">";
    echo "</form>";
    }


include "./footer.php";
?>