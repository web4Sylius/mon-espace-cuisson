<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";
?>

<Script>
function downloadCSV(filename, datacsv) {
	//alert (datacsv);
	if(confirm("Export csv "+filename+" ?")){
		var data, link;
		var csv = datacsv;
		if (csv == null) return;
		if (!csv.match(/^data:text\/csv/i)) {
			csv = 'data:text/csv;charset=utf-8,' + csv;
		}
		var encodedUri = encodeURI(csv);
		var link = document.createElement("a");
		link.setAttribute("href", encodedUri);
		link.setAttribute("download", filename);
		document.body.appendChild(link); // Required for FF
		link.click();
	}
}
</Script>

<?php
//************************************************************
// Affichage de l'unique "onglet" et titre de la page de stat  
echo "
<td valign=\"top\">
  <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
    <tr>
      <td>
        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
          <tr>
            <td>
              <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                <tr>
                  <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Statistiques de chiffre d'affaires</font>
                  </td>
                </tr>
                <tr>
                  <td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
                  </td>
                </tr>
              </table>
            </td>
            <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
            <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\"></td>
            <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
          <tr>
            <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
            <td align=\"justify\" valign=\"middle\"><br><font class=\"txt_2\">
              <table align=\"center\" width=\"100%\">
                <tr>
                  <td align=\"center\" valign=\"middle\" width=\"100%\">";

print "<style>
.stat { width:500px; margin-bottom:10px; }
.stat thead { background-color:#cccccc;font-weight:bold;text-align:center; }
.stat td { padding:2px; }
</style>";

if(isset($_GET['date_debut'])){
	$date_debut=$_GET['date_debut'];
} else {
	$date_debut=date("d/m/Y", mktime(0,0,0,date("m")-1)); // il y a 1 mois
}
if(isset($_GET['date_fin'])){
	$date_fin=$_GET['date_fin'];
} else {
	$date_fin=date("d/m/Y"); // il y a 1 mois
}
?>
<!-- ************************************* -->
<!-- Affichage du formulaire de recherche -->
<form method="get">
    Du <input type="text" name="date_debut" id="date_debut" value="<?= $date_debut ?>" onclick="calendrier_show('date_debut');" style="width: 100px;">(inclus)
	Au <input type="text" name="date_fin" id="date_fin" value="<?= $date_fin ?>" onclick="calendrier_show('date_fin');" style="width: 100px;">(inclus)
    <input type="submit" value="Valider">
</form>
<?php

//************************************************************
// Dans le cas ou il y a une recherche  
if(isset($_GET['date_debut']) && isset($_GET['date_fin'])){
	$prov=array(
		"commercial"	=> "Cde du service commercial",
		"non_attribue"	=> "Cde non attribuées",
		"direct"		=> "Cde directe site",
		"marketplace"	=> "Cde Market place",
		"sav"			=> "Cde SAV"
	);
    $lw=mktime(0,0,0)-864000;
    $lm=$lw;
    while(date("w",$lm)!=1)
      $lm-=86400;

    $aDateDebut = explode('/',$_GET['date_debut']);
    $date_debut = mktime(0,0,0,$aDateDebut[1],$aDateDebut[0],$aDateDebut[2]);
    $aDateFin   = explode('/',$_GET['date_fin']);
    $date_fin   = mktime(0,0,0,$aDateFin[1],$aDateFin[0]+1,$aDateFin[2]); // ajout d'une journée, pour inclusion de la date de fin
    $date_finaff= mktime(0,0,0,$aDateFin[1],$aDateFin[0],$aDateFin[2]); // garde l'affichage avec la date choisie dans les critères

    $dat=getdata($date_debut,$date_fin);
    $totalca=array_sum($dat['ca']);
    $totalqte=array_sum($dat['marque']['qte']);
    
	
    print "<a name=\"semaine\"></a><h2>Commandes passées entre le ".date("d/m/Y",$date_debut)." et le ".date("d/m/Y",$date_finaff)."</h2>";
    print "<p align=\"left\">Nombre total de commandes : ".number_format($dat['provenance']['qte'],0,'',' ')."
		<br>CA HT : ".number_format($totalca,2,',',' ')."&euro;";
    print "<br>Nombre total de produits : ".number_format($totalqte,0,'',' ')."</p>\n";
    
    display($dat,$date_debut,$date_fin);
}

//************************************************************
// Affichage des données de la recherche
function display($dat, $datedeb, $datefin){
  global $DB_site2;
  global $prov;
  
  $du = date("d/m/Y",$datedeb);
  $au = date("d/m/Y",$datefin);

  $totalca=array_sum($dat['ca']);
  $totalqte=array_sum($dat['marque']['qte']);
  
  arsort($dat['ca']);
  
  
  //******************************************************
  // entete tableau details type Cde
  //******************************************************
  print "<table class=\"stat\">
    <thead>
      <tr>
        <td width=\"40%\">Détails type Cde<br>(CA enregistré validé)</td>
        <td width=\"5%\">Nb cdes</td>
        <td width=\"15%\">CA HT</td>
        <td width=\"20%\">% CA</td>
    	<td width=\"20%\">% Qté vendue</td>
      </tr>
    </thead>";
  
  // Préparation de l'export CSV
  $nomexport = "Details type Cde"; // nom du fichier
  $arraydata =$nomexport.";".$du.";".$au."\n\n"; // info peridoe
  $arraydata .="Details type cde;Qte;CA HT;% CA;% Qte vendue\n"; // entete colonnes

  // lignes tableau details type Cde
  foreach($dat['ca'] as $p=>$ca){
    $pc=round(100*$ca/$totalca,1);
    $pcv= round($dat['provenance'][$p]/$dat['provenance']['qte']*100,1);
    
    print "<tr>
        <td>".$prov[$p]."</td>
        <td align=\"right\">".$dat['provenance'][$p]."</td>
        <td align=\"right\">".number_format($ca,2,',',' ')."&euro;</td>
        <td><div style=\"color:black;background-color:lightgreen;width:".intval($pc)."px;\">".$pc."%</div></td>
      	<td><div style=\"color:black;background-color:yellow;width:".intval($pcv)."px;\">".$pcv."%</div></td>
      </tr>";
    
    // stockage des donnees du csv, on enleve les quotes, les accents et les entities html
    // car les donnees dans la base n'ont pas le mm type (utf-8 / ASCII / HTML Encoded...) et cela fout le bordel !!!!
    $name = str_replace('\'',' ',$prov[$p]);
    $name = html_entity_decode($name);
    $name = wd_remove_accents($name,'utf-8');
    $arraydata .= $name.";".$dat['provenance'][$p].";".number_format($ca,2,',',' ').";".$pc.";".$pcv."\n";
  }
  /*
  print "<tr>
      <td>Annulation et avoirs</td>
      <td align=\"right\">".$dat['avoir']['qte']."</td>
      <td align=\"right\">".number_format($dat['avoir']['ca'],2,',',' ')."&euro;</td>
    </tr>
  </table>";
  // stockage des donnees annulation du csv
  $arraydata .= "Annulation et avoirs".";".$dat['avoir']['qte'].";".number_format($dat['avoir']['ca'],2,',',' ').";".""."\n";
  */
  // fin tableau details type Cde
  
  // appel de la fonction javascript export en csv
  // echo json_encode($day) permet de transformer la varible php en variable js
  $filename = $nomexport.".csv";
  print "<a href='#' onclick='downloadCSV(";
  print json_encode($filename).",";
  print json_encode($arraydata);
  print ");'>Download CSV</a><br><br>";

  // recuperation des libelle marque pour le tableau suivant
  $req=$DB_site2->query("SELECT marqueid,libelle FROM marque");
  while($m=$DB_site2->fetch_array($req)) 
    $marques[$m[0]]=$m[1];

    
  //******************************************************
  // entete tableau Cde par marques
    //******************************************************
  print "<table class=\"stat\">
    <thead>
      <tr>
        <td width=\"40%\">Commandes par marques<br>(hors frais de port et bons de réduction)</td>
        <td width=\"5%\">Nb produits</td>
        <td width=\"15%\">CA HT</td>
        <td width=\"20%\">% CA</td>
      	<td width=\"20%\">% Qté Vendue</td>
      </tr>
    </thead>";
  
  // Préparation de l'export CSV
  $nomexport = "Cde par Marques"; // nom du fichier
  $arraydata =$nomexport.";".$du.";".$au."\n\n"; // info periode
  $arraydata .="Cde par marques;Qte;CA HT;% CA;% Qte vendue\n";  // entete des colonnes

  // lignes tableau Cde par marques
  arsort($dat['marque']['ca']);
  foreach($dat['marque']['ca'] as $marqueid=>$ca){
    $pc=round(100*$ca/$totalca,1);
    $pcv= round($dat['marque']['qte'][$marqueid]/$totalqte*100,1);
    print "<tr>
        <td>".$marques[$marqueid]."</td>
        <td align=\"right\">".$dat['marque']['qte'][$marqueid]."</td>
        <td align=\"right\">".number_format($ca,2,',',' ')."&euro;</td>
        <td><div style=\"color:black;background-color:lightgreen;width:".intval($pc)."px;\">".$pc."%</div></td>
    	<td><div style=\"color:black;background-color:yellow;width:".intval($pcv)."px;\">".$pcv."%</div></td>
      </tr>";
    
    // stockage des donnees du csv, on enleve les quotes, les accents et les entities html
    // car les donnees dans la base n'ont pas le mm type (utf-8 / ASCII / HTML Encoded...) et cela fout le bordel !!!!
    $name = str_replace('\'',' ',$marques[$marqueid]);
    $name = html_entity_decode($name);
    $name = wd_remove_accents($name,'utf-8');
    $arraydata .= $name.";".$dat['marque']['qte'][$marqueid].";".number_format($ca,2,',',' ').";".$pc.";".$pcv."\n";
  }

  print "</table>";
  // fin tableau Cde par marques
  
  // appel de la fonction javascript export en csv
  // echo json_encode($day) permet de transformer la varible php en variable js
  $filename = $nomexport.".csv";
  print "<a href='#' onclick='downloadCSV(";
  print json_encode($filename).",";
  print json_encode($arraydata);
  print ");'>Download CSV</a><br><br>";
  
  
  //******************************************************
  // entete tableau Cde par catégories
  //******************************************************
  print "<table class=\"stat\">
    <thead>
      <tr>
        <td width=\"40%\">Commandes par catégorie<br>(hors frais de port et bons de réduction)</td>
        <td width=\"5%\">Nb produits</td>
        <td width=\"15%\">CA HT</td>
        <td width=\"20%\">% CA</td>
		<td width=\"20%\">% Qté Vendue</td>        		
      </tr>
    </thead>";
  
  // Préparation de l'export CSV
  $nomexport = "Cde par Categories"; // nom du fichier
  $arraydata =$nomexport.";".$du.";".$au."\n\n"; // info periode
  $arraydata .="Cde par categories;Qte;CA HT;% CA;% Qte vendue\n"; // entete des colonnes

  // lignes tableau Cde par catégories
  arsort($dat['cat']['ca']);
  foreach($dat['cat']['ca'] as $catid=>$ca)
  {
  	  $profondeur = 0;
  	  $catParent = $catid;
	  while (($catParent > 0) && ($profondeur < 6))
	  	{
	  		$cat=$DB_site2->query_first("SELECT cp.libelleFR, cp.parentid FROM categorie c INNER JOIN categorie cp on (cp.catid=c.parentid or c.parentid=0) WHERE c.catid=".$catid);
	  		//print_r ($cat);
	  		$catParent=$cat[1];
	  		$profondeur++;
	  	}
    
	  $mastercat['qte'][$cat[0]]+=$dat['cat']['qte'][$catid];
	  $mastercat['ca'][$cat[0]]+=$dat['cat']['ca'][$catid];
  }

  arsort($mastercat['ca']);
  
    
  foreach($mastercat['ca'] as $catname=>$ca){
    $pc=round(100*$ca/$totalca,1);
    $pcv= round($mastercat['qte'][$catname]/$totalqte*100,1);
    print "<tr>
        <td>".$catname."</td>
        <td align=\"right\">".$mastercat['qte'][$catname]."</td>
        <td align=\"right\">".number_format($ca,2,',',' ')."&euro;</td>
        <td><div style=\"color:black;background-color:lightgreen;width:".intval($pc)."px;\">".$pc."%</div></td>
  		<td><div style=\"color:black;background-color:yellow;width:".intval($pcv)."px;\">".$pcv."%</div></td>
      </tr>";
    
    // stockage des donnees du csv, on enleve les quotes, les accents et les entities html 
    // car les donnees dans la base n'ont pas le mm type (utf-8 / ASCII / HTML Encoded...) et cela fout le bordel !!!!  
    $name = str_replace('\'',' ',$catname);
    $name = html_entity_decode($name);
    $name = wd_remove_accents($name,'utf-8');
    $arraydata .= $name.";".$mastercat['qte'][$catname].";".number_format($ca,2,',',' ').";".$pc.";".$pcv."\n";
  }

  print "</table>";
  // fin tableau Cde par catégorie
   
  // appel de la fonction javascript export en csv
  // echo json_encode($day) permet de transformer la varible php en variable js
  $filename = $nomexport.".csv";
  print "<a href='#' onclick='downloadCSV(";
  print json_encode($filename).",";
  print json_encode($arraydata);
  print ");'>Download CSV</a><br><br>";
  
  
  //******************************************************
  // entete tableau Cde par catégories et sous categories
  //******************************************************
  print "<table class=\"stat\">
    <thead>
      <tr>
        <td width=\"40%\">Commandes par catég/sous catég<br>(hors frais de port et bons de réduction)</td>
        <td width=\"5%\">Nb produits</td>
        <td width=\"15%\">CA HT</td>
        <td width=\"20%\">% CA</td>
		<td width=\"20%\">% Qté Vendue</td>
      </tr>
    </thead>";
  
  // Préparation de l'export CSV
  $nomexport = "Cde par Categ et SousCateg "; // nom du fichier
  $arraydata =$nomexport.";".$du.";".$au."\n\n"; // info periode
  $arraydata .="Cde par categ/sous categ;Qte;CA HT;% CA;% Qte vendue\n"; // entete des colonnes
  
  // lignes tableau Cde par catégories et sous categories
  //arsort($dat['cat']['ca']);
  foreach($dat['cat']['ca'] as $catid=>$ca){
  	$cat=$DB_site2->query_first("SELECT libelleFR FROM categorie c WHERE c.catid=".$catid);
  	$parentcat['qte'][$cat[0]]+=$dat['cat']['qte'][$catid];
  	$parentcat['ca'][$cat[0]]+=$dat['cat']['ca'][$catid];
  }
  
  arsort($parentcat['ca']);
  
  foreach($parentcat['ca'] as $catname=>$ca){
  	$pc=round(100*$ca/$totalca,1);
  	$pcv= round($parentcat['qte'][$catname]/$totalqte*100,1);
  	print "<tr>
        <td>".$catname."</td>
        <td align=\"right\">".$parentcat['qte'][$catname]."</td>
        <td align=\"right\">".number_format($ca,2,',',' ')."&euro;</td>
        <td><div style=\"color:black;background-color:lightgreen;width:".intval($pc)."px;\">".$pc."%</div></td>
  		<td><div style=\"color:black;background-color:yellow;width:".intval($pcv)."px;\">".$pcv."%</div></td>
      </tr>";
  
  	// stockage des donnees du csv, on enleve les quotes, les accents et les entities html
  	// car les donnees dans la base n'ont pas le mm type (utf-8 / ASCII / HTML Encoded...) et cela fout le bordel !!!!
  	$name = str_replace('\'',' ',$catname);
  	$name = html_entity_decode($name);
  	$name = wd_remove_accents($name,'utf-8');
  	$arraydata .= $name.";".$parentcat['qte'][$catname].";".number_format($ca,2,',',' ').";".$pc.";".$pcv."\n";
  }
  
  print "</table>";
  // fin tableau Cde par catégorie et sous catégories
   
  // appel de la fonction javascript export en csv
  // echo json_encode($day) permet de transformer la varible php en variable js
  $filename = $nomexport.".csv";
  print "<a href='#' onclick='downloadCSV(";
  print json_encode($filename).",";
  print json_encode($arraydata);
  print ");'>Download CSV</a>";
  				
}


//************************************************************
// On enleve les caractères accentues pour l'export HTML
function wd_remove_accents($str, $charset='utf-8'){
	$str = htmlentities($str, ENT_NOQUOTES, $charset);
	$str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
	return $str;
}


//************************************************************
// Recuperation des données pour la période
function getdata($from,$to){
    global $DB_site2;
    $provenanceqte = 0;
    $ret = array();
    //Récupération des commandes validées et expédiées dans le laps de temps demandé
    $sql = "SELECT *";
    $sql .= " FROM facture";
    $sql .= " WHERE valide IN (1,5,7,8)";
    $sql .= " AND heurefacture BETWEEN " . $from . " AND " . $to;
	$sql .= " ORDER BY facturid ASC ";
	// echo $sql;
    $factures = $DB_site2->query($sql);

    while($facture = $DB_site2->fetch_array($factures)){

        $totalht  = 0;
        $totalqte = 0;
       // $totalttc = array();

        //Récupération des articles de la commande (on enleve les bons de reduction et autres ...)
        $sql = "SELECT lf.artid, lf.qte, lf.prix, lf.tva, a.catid FROM lignefacture lf
				LEFT JOIN article AS a ON(lf.artid=a.artid) ";
        $sql .= " WHERE  lf.factureid = " . $facture['facturid'];
        $lignes = $DB_site2->query($sql);

        while($ligne = $DB_site2->fetch_array($lignes)){
          //  $totalttc[$ligne['tva']] += $ligne['prix'] * $ligne['qte'];
		    $ligneht=($ligne['prix'] * $ligne['qte']) / (1 + ($ligne['tva'] / 100) );
            $totalht                 += $ligneht;
            if($ligne['artid'] != 0){
                //Détail sur marque de l'article
                $sql = "SELECT marqueid";
                $sql .= " FROM article_marque";
                $sql .= " WHERE artid = " . $ligne['artid'];
                $marque = $DB_site2->query_first($sql);

                $ret['marque']['qte'][$marque[0]]   += $ligne['qte'];
                $ret['marque']['ca'][$marque[0]]    += $ligne['qte'] * $ligne['prix'] / (1 + $ligne['tva'] / 100);
                $ret['cat']['qte'][$ligne['catid']] += $ligne['qte'];
                $ret['cat']['ca'][$ligne['catid']]  += $ligne['qte'] * $ligne['prix'] / (1 + $ligne['tva'] / 100);
            }
        }
		$totalligne=$totalht;
        $portHT = $facture['prixport'] / ( 1 + $facture['tvaport'] / 100) ;
        $totalht +=  $portHT;
		if($facture['cadeauid'] != ""){
            $sql = "SELECT *";
            $sql .= " FROM code_caviste";
            $sql .= " WHERE code = '" . $facture['cadeauid'] . "'";
            $cadeau = $DB_site2->query_first($sql);

            if($cadeau['code'] == ""){
				$totalcadeau=$facture['montantcadeau'] / ( 1 + $facture['tvaport'] / 100);
				if($totalcadeau>0){
					$totalht -= $totalcadeau;
				}
			}
        }
		if($facture['commercial_attribution']!="direct" && $facture['commercial_attribution']!=""){
			$ret['provenance']['commercial']++;
			$ret['ca']['commercial'] += $totalht;
			$provenanceqte++;
		}
		if($facture['commercial_attribution']=="direct"){
			$ret['provenance']['direct']++;
			$ret['ca']['direct'] += $totalht;
			$provenanceqte++;
		}
		if($facture['commercial_attribution']=="" && !($facture['provenance']=="mp" || $facture['provenance']=="sav" || $facture['provenance']=="avoir") ){
			$ret['provenance']['non_attribue']++;
			$ret['ca']['non_attribue'] += $totalht;
			$provenanceqte++;
		}
		if($facture['provenance'] == "sav" || $facture['provenance'] == "avoir"){
			$ret['provenance']['sav']++;
			$ret['ca']['sav'] += $totalht;
			$provenanceqte++;
		}
		if($facture['provenance'] == "mp"){
			$ret['provenance']['marketplace']++;
			$ret['ca']['marketplace'] += $totalht;
			$provenanceqte++;
		}
    }
    $ret['provenance']['qte']=$provenanceqte; //+$ret['avoir']['qte'];
    
    return $ret;
}

//************************************************************
// Calcul de l'objectif
function calcobj($from,$to){
  global $objectif,$objectif_mp;

  $obj=0;
  for($i=$from;$i<$to;$i+=86400){
    $y=date("Y",$i);
    $m=date("n",$i);
    $obj+=($objectif[$y][$m]+$objectif_mp[$y][$m])/date("t",$i);
  }

  return $obj;
}




//************************************************************
// Fin d'affichage du tableau de resultat

echo "</td>
      </tr>   
    </table>    
  </font>
</td>
<td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
</tr>
</table>
</td>
</tr>
<tr>
  <td>
    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
      <tr>
        <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
        <td align=\"center\" height=\"23\" width=\"100%\"></td>
        <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
      </tr>
      <tr>
        <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
      </tr>
    </table>
</td></tr></table>
</td>
</tr>
<tr></tr>
</table>
</td>
</tr>
</table>
</td>
</tr>";

include "./footer.php";

?>
