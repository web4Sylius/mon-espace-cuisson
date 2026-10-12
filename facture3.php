<?php

ini_set("display_errors","on");

if (!isset($langue)) $langue = "FR" ;

if(isset($_GET['annule'])){
    $req=$DB_site->query("SELECT * FROM facture WHERE numerofacture='".$_GET['annule']."'");
    if($req!=false && $DB_site->num_rows($req)>0) unset($_GET['annule']);
}
$proforma=isset($_GET['proforma']);

require_once ("global.php");

if($DB_site2!=null) $DB_site=$DB_site2;

function CleanFiles($dir){
    $t=time();
    $h=opendir($dir);
    while($file=readdir($h))
        {
        if(substr($file,0,3)=='tmp' and substr($file,-4)=='.pdf')
            {
            $path=$dir.'/'.$file;
            if($t-filemtime($path)>30)
                {
                @unlink($path);
                }
            }
        }
    closedir($h);
} 

function french_date($joindate ) { 
    $monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'); 
    $days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'); 
    return $days[date("w",$joindate)].' '.date("d",$joindate).' '.$monthes[date("n",$joindate) ].' '.date("Y",$joindate); 
}

$marketplaces=array(""=>"Autre","01"=>"Amazon","04"=>"Price Minister","05"=>"Pixmania","06"=>"Windill","07"=>"Rue du Commerce","08"=>"Edengo","09"=>"Ebuyclub","10"=>"Ebay","11"=>"Le guide de la maison");
$codepaysfabrication=array(0=>"France",1=>"Europe",2=>"Asie",3=>"USA");

$optionsoffert=array(4,6,7,8);


$facture=$DB_site->query_first("SELECT * FROM facture f, pays p WHERE facturid = '$facturid' and f.paysid = p.paysid");
$user=$DB_site->query_first("SELECT *,u.tvaintracom AS tvaintracom FROM utilisateur u, pays p WHERE userid = '$facture[userid]' and u.paysid = p.paysid");
$numerofacture = $facture[codenumerofacture].$facture[numerofacture];
$moyenpaiement=$DB_site->query_first("SELECT * FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
$coord_commercant=$DB_site->query_first("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux");
$pays=$DB_site->query_first("SELECT * FROM pays WHERE paysid=".intval($user['paysid']));

$aInstallation = array();
$sql = "SELECT * FROM lignefactureinstallation WHERE factureid = ".$facture['facturid'];
$req = $DB_site->query($sql);
if($DB_site->num_rows($req) > 0){
     while($row = $DB_site->fetch_array($req)){
          $aInstallation[] = $row;
     }
}

if($facture['site_code']=="") $facture['site_code']="base";
$template->changesite($facture['site_code'],$user['langue']);
$libelle = "libelle".$user['langue'];
$site_code=$template->site_code();
$template->pro=$template->site_code()=="base" && $user['raisonsociale']!="";
$autocolor=$template->pro?"bleu":"dore";

$avoir=false;
$pretotal=$DB_site->query_first("SELECT SUM(qte*prix) FROM lignefacture WHERE factureid=".$facturid);
if($pretotal[0]<0) $avoir=true;

$ht=false;
$ht=($user['raisonsociale']!="");

$nomboutique = ${$title} ;

if ($passe==$facture[passefacture]) 
    {
    if(file_exists("admin/factures/$facturid.pdf") && $cde!=1 && !$proforma){
      header('Content-type: application/pdf');
      header("Content-Disposition: attachment; filename=\"facture $facturid.pdf\"");
      readfile("admin/factures/$facturid.pdf");
      exit;
    }


    $pdf=new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetTitle("Facture");
    $pdf->SetAuthor("Arobases");
    $pdf->SetAutoPageBreak(true,10);
    //$pdf->SetFillColor(230,230,230);
    $pdf->SetFont('Arial','BU',14);
    //$pdf->Cell(100,5,"$multilangue[expediteur] :");

     if ($cde == "1") {
       $adresse=$facture;
       if(isset($_GET['livr'])){
	  $newaddress=$DB_site->query_first("SELECT f.societe AS raisonsociale,f.nom,f.prenom,f.adresse1 AS adresse,f.cp AS codepostal,f.ville,f.telephone,p.libelle,f.portable FROM fournisseur f NATURAL JOIN pays p WHERE f.id=".intval($_GET['livr']));
	  if($newaddress!=false){
	    $adresse=$newaddress;
	    $facture[infotranspo]=$template->translate("/facture/a_livrer_par")." $facture[raisonsociale] $facture[nom] $facture[prenom], $facture[adresse], $facture[codepostal] $facture[ville], $facture[telephone]";
	  }
	}
      // $pdf->Cell(60,5,"Adresse de livraison :");
    }else{
        $adresse=$user;
      //  $pdf->Cell(60,5,"Adresse de facturation :");
    }

    $pdf->setXY(10,41);
    $pdf->SetFont('Arial','B',10);
    $pdf->textColor("black");
    $pdf->Cell(70,5,$template->translate("/devis/expediteur"));

    $vendeur=$template->translate("/devis/commercant/[site_code]/raison_sociale")."\n"
    .$template->translate("/devis/commercant/[site_code]/adresse")."\n"
    .$template->translate("/devis/commercant/[site_code]/ville");
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->multicell(100,5,$vendeur);
    

    if($cde!=1 && $adresse[tvaintracom]!=""){
      $client.="\n".$template->translate("/facture/n_tva","U")." : ".$adresse[tvaintracom];
      if($adresse['eori'] != "") {
            $client.="\nN° EORI ".$adresse['eori'];
        }
    }
    
    if($cde==1){
	$pdf->fillColor($autocolor);
	$pdf->rect(112,41,94,7.5,"F");
	$pdf->fillColor("light gray");
	$pdf->rect(112,48.5,94,33,"F");
	
	$pdf->setXY(115,43);
	$pdf->SetFont('Arial','B',8);
	$pdf->textColor("white");
	$pdf->Cell(70,5,$template->translate("/devis/adresse_livraison","U"));
    }
    
    $pdf->setXY(115,50);
    $pdf->textColor("black");
    
    if($cde==1){
	$client=trim($adresse['nom'])." ".trim($adresse['prenom']);
	if($adresse['raisonsociale']!=""){
	    $client=trim($adresse['raisonsociale'])."\n".$client;
	}
    }else{
	if($adresse['raisonsociale']=="")
	    $client=trim($adresse['nom'])." ".trim($adresse['prenom']);
	else
	    $client=trim($adresse['raisonsociale']);
    }
    $pdf->setfont('arial','B',8);
    $pdf->multiCell(100,5,$client);
    
    $client=trim($adresse[adresse])."\n"
    .$adresse[codepostal]." ".trim($adresse[ville])."\n"
    .$adresse[libelle]."\n"
    .$template->translate("/facture/tel")." : ".trim($adresse[telephone])." / Gsm : ".trim($adresse[portable]);
    
    if($adresse['tvaintracom']!="" && $cde<>1) $client.="\n".$template->translate("/facture/n_tva")." ".$adresse['tvaintracom'];
    
    $pdf->setX(115);
    $pdf->SetFont('Arial','',8);
    $pdf->multiCell(100,5,$client);
    
    
    $ly=65;
    $pdf->Cell(100,5,"");
    if($cde==1){
        $pdf->setXY(10,$ly);
	$pdf->setfont('arial','B',8);
	$height=$pdf->nblines(90,$template->translate("/facture/informations","F")." : $facture[infotranspo]")*4;
        $pdf->multiCell(90,4,$template->translate("/facture/informations","F")." : ".stripslashes(utf8_decode($facture[infotranspo])));
        $pdf->rect(10,$ly,92,16);
	$pdf->setfont('arial','',8);
	$wpic=32;
    }
    $pdf->setY($ly+$height);
    $pdf->Ln(5);


    $wcol=array(7,112,20,11,11,20,20);
    if($cde==1) $wcol=array(7,162,0,0,0,0,0);

    toptable($pdf);

    if(isset($produits) && $cde==1){
	     $lfids=$qteprod=array();
	     foreach($produits as $produit){
	       if(preg_match("/([0-9]+)-([0-9]+)/",$produit,$m)){
		        $lfids[]=$m[2];
		        $qteprod[$m[2]]=$m[1];
	       }
	     }
	     if($lfids==array()) $lfids=$produits;
       $sql="SELECT * FROM lignefacture WHERE";
	     $sql.=" lignefactureid IN (".implode(",",$lfids).")";
       $sql.=" ORDER BY artcode";
       $lignefactures=$DB_site->query($sql);    
    }else{
        $lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$facturid'");
    }
    
    $tva = array() ;
    $i=0;
    $j=1;
    $poidstotal=0;
    $poidscaves=array();
    $prixtypeinstall="";
    $fichesj=array();
    $typesfiche=array(1,2,3,4,5,7,8,10,12,13,14,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,74);
    $codecaviste=0;
    $lignekdo=0;
    $totalcadeau=0;
    $totalttc=array();
    

    //Gestion de la dissociation
    $ligne = array();
    while($lignefacture=$DB_site->fetch_array($lignefactures)){
      if($lignefacture['lignefacturedissid'] != 0){
          $sql = "SELECT * FROM lignefacturediss WHERE lignefactureid = '".$lignefacture['lignefacturedissid']."'";
          $diss = $DB_site->query_first($sql);
          $ligne[$lignefacture['lignefactureid']] = $diss['lignefactureid'];
        }else{
          $ligne[$lignefacture['lignefactureid']] = $lignefacture['lignefactureid'];
        }
    }
    //on trie les résultat.
    $lesLignes = array_unique($ligne);
    $sql="SELECT * FROM lignefacture WHERE";
    $sql.=" lignefactureid IN (".implode(",",$lesLignes).")";
    $sql.=" UNION";
    $sql.=" SELECT * FROM lignefacturediss WHERE";
    $sql.=" lignefactureid IN (".implode(",",$lesLignes).")";
    $sql.=" ORDER BY artcode";
    $lignefactures=$DB_site->query($sql);

    while ($lignefacture=$DB_site->fetch_array($lignefactures)){
      $diffprix=0;
      $des_caracts = array() ;
      $des_garanties=array();
      $calcdesign="";
      $pdf->drawColor("white");
      if($lignefacture['artid'] > 0){
          $libmarque=$DB_site->query_first("SELECT * from article_marque am, marque m where am.marqueid = m.marqueid and am.artid = '$lignefacture[artid]'"); 
      } else {
          $libmarque=array('libelle' => '');
      }
      
      $libmodele=array();
      if($lignefacture['artcode']!="avoir") $libmodele = $DB_site->query_first("SELECT * FROM article WHERE artid = '$lignefacture[artid]'");if($lignefacture['artcode']!="avoir") {

        $sql = "SELECT bdd_table FROM typearticle";
        $sql .= " INNER JOIN article a USING(typearticleid)";
        $sql .=" WHERE a.artid= '$lignefacture[artid]'";
        $table_type = $DB_site->query_first($sql);
        if(!is_null($table_type)){
            $libmodele = $DB_site->query_first("SELECT * FROM article as a INNER JOIN $table_type[0] as spec USING(artid) WHERE a.artid= '$lignefacture[artid]'");
            $libmodele['emballage_hauteur']=$libmodele['emballage_hauteur1']+$libmodele['emballage_hauteur2']+$libmodele['emballage_hauteur3'];
            $libmodele['emballage_largeur']= $libmodele['emballage_largeur1']+ $libmodele['emballage_largeur2']+$libmodele['emballage_largeur3'];
            $libmodele['emballage_profondeur']=$libmodele['emballage_profondeur1']+$libmodele['emballage_profondeur2']+$libmodele['emballage_profondeur3'];
            $libmodele['emballage_poids']=$libmodele['emballage_poids1']+$libmodele['emballage_poids2']+$libmodele['emballage_poids3'];
        }

      }
      
      $sql = "SELECT lfg.*, g.garantie_client_id, g.utilisation_garantie,g.cumul";
      $sql .= " FROM lignefacturegarantie lfg";
      $sql .= " INNER JOIN garantie g ON (g.garantieid = lfg.garantieid)";
      $sql .= " WHERE lfg.lignefactureid = '".$lignefacture['lignefactureid']."'";
      $sql .= " GROUP BY lfg.garantieid";
      $sql .= " ORDER BY g.utilisation_garantie ASC, lfg.type ASC, lfg.duree ASC";
      $garanties = $DB_site->query($sql);
	
      $caracts=$DB_site->query("SELECT * FROM lignefacturecaracteristique lc, caracteristiquevaleur cv WHERE lc.caractvalid=cv.caractvalid AND lc.lignefactureid = '$lignefacture[lignefactureid]' ORDER BY libelle_caract");
      if($lignefacture['artcode']=="Code caviste"){
            $codecaviste=$facture['cadeauid'];
            $lignekdo=$lignefacture;
      }

      while ($caract=$DB_site->fetch_array($caracts)){
          $des_caracts["$caract[libelle_caract] : $caract[libelle_caractval]"] = $caract[differenceprix] ;
          $des_caracts_type["$caract[libelle_caract] : $caract[libelle_caractval]"] = $caract[caractid] ;
          $calcdesign.="\n$caract[libelle_caract] : $caract[libelle_caractval]";
          $diffprix+=($caract[differenceprix]);
      }
      ksort($des_caracts);
	    while ($garantie=$DB_site->fetch_array($garanties)){
	      if($cde==1) break;
        $date="";
        if($garantie['type'] == '6'){ // gestion affichage tarif platinehome
          $test = $DB_site->query_first("SELECT * FROM lignefacture where lignefactureid='".$lignefacture[lignefactureid]."'");
          $count = 0 ;
          if($test){
              $tarifph = $DB_site->query_first("SELECT offre_prix, count FROM platinehome WHERE lignefactureid ='".$lignefacture[lignefactureid]."'");
              $leTarif = $tarifph['offre_prix'];
              $count = $test['qte'] * $tarifph['count'];
              
           }else{
              $test = $DB_site->query("SELECT * FROM lignefacture where lignefacturedissid='".$lignefacture[lignefactureid]."'");
              while($row = $DB_site->fetch_array($test)){
                  $tarifph = $DB_site->query_first("SELECT offre_prix, count FROM platinehome WHERE lignefactureid ='".$row['lignefactureid']."'");
                  if($tarifph){ 
                      $leTarif = $tarifph['offre_prix'];
                      $count += $row['qte'] * $tarifph['count'];
                  } 
              }
          }
          if($count > 1 )
              $tarifPH = $count . " x ". $template->price_format($leTarif, 0, false);
          else
              $tarifPH = $template->price_format($leTarif,0,false);

            $anneeGarantie = utf8_decode($template->translate("/article/garantie_type/abonnement","F")) . " (".$tarifPH." / ".$template->translate("/garantie/platine_home/mois").")";

        }else{
            if ($garantie['dureeReelle']!=99 && $garantie['dureeReelle'] != 100) {
                $dateEffetGarantie=date_create($facture['dateexpedition']);
                date_add($dateEffetGarantie,date_interval_create_from_date_string($garantie["duree"]." years"));
                date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
                $dateEffetGarantie = date_format($dateEffetGarantie,"d/m/Y");
                $date = " - ".$template->translate("/article/garantie_type/valable_jusqu_au",'F')." ".$dateEffetGarantie;
                $an=$template->translate("/article/garantie_type/an");
                if ($garantie['dureeReelle']>1) {
                    $an=$template->translate("/article/garantie_type/ans");
                }
                $anneeGarantie=$garantie['dureeReelle']." ".$an;
            } else {
                $anneeGarantie=$template->translate("/article/garantie_type/a_vie");
            }
        }
	    
	      $intitule_destinataire = '';
	      if($user['raisonsociale'] != ''){
		      switch($garantie['garantie_client_id']){
  		        case 1:
  			        $intitule_destinataire = '"'.$template->translate("/garanties/usage_prive").'"';
  			        break;
  		        case 2:
  			        $intitule_destinataire = '"'.$template->translate("/garanties/usage_pro").'"';
  			        break;
		      }
	      }
	      if($garantie['type'] == 6 ){
          $libGarantie =$garantie['libelle']." ".$anneeGarantie;
        }else{
          $libGarantie = $garantie['libelle']." ".$intitule_destinataire." ".$anneeGarantie." ".html_entity_decode($template->translate("/article/garantie_type/".$garantie['type']),ENT_COMPAT,"ISO-8859-15")." ".DAOpays::getPaysById($DB_site,$garantie['pays'])." (".$garantie['codeFournisseur'].")".$date;  
        } 
        $des_garanties[$libGarantie] = $garantie[differenceprix] ;
  	    $calcdesign.="\n$libGarantie";
  	    $diffprix+=$garantie[differenceprix];
        $garantieDuree = $garantie['dureeReelle'];
      }
      $diffprixht=round($diffprix/(1+$lignefacture[tva]/100),2);

      // début de la désignation
      $designation = html_entity_decode($lignefacture['libelle'],ENT_COMPAT,"UTF-8")." ".$libmarque['libelle'].($libmodele['gamme'.$template->langue] != "" ? " ".$libmodele['gamme'.$template->langue] : "");
      if($facture['moyenid']==20){
        if($libmodele[artcode]!="") $designation.=" Ref. $libmodele[artcode] ($libmodele[artcode_eurocave])";
      }else{
        if($libmodele[artcode]!="") $designation.=" Ref. $libmodele[artcode]";
      }
      if($libmodele['gaz']!="")
            $designation.="\n - ".$template->translate("/facture/type_gaz","F")." : ".$template->translate($libmodele['gaz']);
      $designation.="\n";

      $heightlibele=$pdf->nblines($wcol[1],$designation)*4;
      $calcdesign=$designation."\n".$calcdesign;

      $heighteco=0;
      $ecopart=0;
      $ecopartht=0;
      $ecoparthttotal=0;
      if($site_code=="base" && $lignefacture[ecopart]>0 && $lignefacture[prix]>0){
        $ecopart=$lignefacture['ecopart'];
        $ecopartht=round($ecopart/(1+$lignefacture['tva']/100),2);
        $ecoparthttotal=$ecopartht*$lignefacture[qte];
        $calcdesign.="\n";
      }
      $paysfabrication=$DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".intval($libmodele['paysid']));
      $designation2="";
      if($cde==1){
	      $req=$DB_site->query("SELECT qte,libelle FROM article_accessoire WHERE artid=".$lignefacture['artid']);
	      if($DB_site->num_rows($req)>0){
		      $designation2.="\n".$template->translate("/facture/accessoires_inclus")." :\n";
		      while($acc=$DB_site->fetch_array($req)){
		        $designation2.="- ".$acc['qte']." ".$template->translate($acc['libelle'],"L")."\n";
		      }
	      }
	    }

      if($libmodele[emballage_hauteur]!="" || $libmodele[emballage_largeur]!="" || $libmodele[emballage_profondeur]!="" || $libmodele[emballage_poids]!="" || $libmodele[hauteur]!="" || $libmodele[largeur]!="" || $libmodele[profondeur]!="" || $libmodele[poids]!=0)
               $designation2.="\n - ".$template->translate("/facture/dim_poids_brut")." : ".$libmodele[emballage_hauteur]."*".$libmodele[emballage_largeur]."*".$libmodele[emballage_profondeur]." / ".$libmodele[emballage_poids]."\n - ".$template->translate("/facture/dim_poids_net")." : ".$libmodele[hauteur]."*".$libmodele[largeur]."*".$libmodele[profondeur]." / ".round($libmodele[poids]/1000,1) ;
      if($cde!=1 && $libmodele['artcode']!="" && (!in_array($facture['paysid'],array(57,181,111)) || !in_array($user['paysid'],array(57,181,111)))){
  	    $designation2.="\n - ".$template->translate("/facture/pays_fabrication")." : ".$paysfabrication['libelle'];
  	    $designation2.="\n - ".$template->translate("/facture/code_douanier")." : ".$libmodele['douanier'];
	    }
	    $calcdesign.="\n".$designation2;

      $article_prices=article_prices($lignefacture['artid'],$template->tva_ref(),$template->site_code(),true,false);
      $puht = floatval($article_prices['prixconseille'])-$ecopartht ;
      $qte = isset($qteprod[$lignefacture['lignefactureid']]) ? $qteprod[$lignefacture['lignefactureid']] : $lignefacture[qte] ;
      $pvente=$lignefacture[prix] / (1 + $lignefacture[tva] / 100)-$ecopartht-$diffprixht;
	    if($pvente<0) $totalcadeau-=($qte*$lignefacture[prix]);
	    $pventettc=$template->price_format($lignefacture[prix]-$ecopart-$diffprix,2,false);
      $remise=0;
      if($puht>0) $remise=-round(($puht-$pvente)/$puht*100);
      $montant_ht=$template->price_format(($pvente*$qte),2,false);
      $pvente=$template->price_format($pvente,2,false);
      $puttc=$template->price_format(floatval($article_prices['prixconseille'])*(1 + $lignefacture[tva] / 100)-$ecopart,2,false);
      $puht = $template->price_format($puht, 2, false) ;
      $txtva = $lignefacture[tva] ;
      $tva[$lignefacture[tva]] += ($lignefacture[prix] * $qte) - (($lignefacture[prix] * $qte) / (1 + $lignefacture[tva] / 100)) ;
      $montant_ttc = ($lignefacture[prix]-$diffprix-$ecopart) * $qte ; 
	  $totalttc[$txtva]+=($qte*$lignefacture[prix]) ;  
      $montant_ttc = $template->price_format($montant_ttc, 2, false) ;
      $poidstotal+=(floatval(str_replace(",",".",$libmodele[emballage_poids]))*$qte);
      if($libmodele['typearticleid']==1) $poidscaves[$libmodele[emballage_poids]]+=$qte;
      install_type($libmodele[emballage_poids]);
    
	    $i++ ;
      
    	$height = 5 ;
    	$nbre = ceil(strlen($designation)/80) ;
    	if ($nbre > 1)
    	    $height = $nbre * 5 ;
    	$pdf->SetFont('Arial','',8);

    	$ly=$pdf->getY();
	    $lx=$wpic+5+$wcol[0];
	    $height=$pdf->nblines($wcol[1],$calcdesign)*4;
	    // calcul si faut ajouter une page
    	if($height+$ly>=270){
    	    footer($pdf);
    	    $pdf->addpage();
    	    $pdf->Header($pdf);
    	    toptable($pdf);
    	    $ly=$pdf->getY();
    	    $lx=5+$wcol[0];
    	}
    	$pdf->setXY($lx,$ly);
    	$pdf->multiCell($wcol[1],4,"$designation" ,0,'',0);
    	$height=$pdf->nblines($wcol[1],$designation)*4;
    	$posY=$height+$ly;
	
    	// affichage des options
    	foreach($des_caracts as $txt=>$prix){
    		$pdf->setXY($wpic+$wcol[0]+5,$posY);
		
		    $pdf->multicell($wcol[1],4," - ".stripslashes($txt),0,'',0);
		    if($cde!=1){
	        if($prix!=0){
	          if($ht) $prix=$prix/(1+$txtva/100);
  		      $posX=$wcol[0]+$wcol[1]+5;
  		      $pdf->setXY($posX,$posY);
  		      $pdf->multiCell($wcol[2],4,$template->price_format($prix,2,false) ,0,'R',0);
  		      $posX+=$wcol[2];
  		      $pdf->setXY($posX,$posY);
  		      $pdf->multiCell($wcol[3],4,number_format($txtva,1,",","")."%" ,0,'R',0);
  		      $posX+=$wcol[3];
  		      $pdf->setXY($posX,$posY);
  		      $pdf->multicell($wcol[4],4,"",0,'C',0);
  		      $posX+=$wcol[4];
  		      $pdf->setXY($posX,$posY);
  		      $pdf->multiCell($wcol[5],4,$template->price_format($prix,2,false) ,0,'R',0);
  		      $posX+=$wcol[5];
  		      $pdf->setXY($posX,$posY);
  		      $pdf->multiCell($wcol[6],4,$template->price_format(($prix*$qte),2,false),0,'R',0);
	        }else{
	          // affichage offert si prix à 0 pour certaines options
	          if(in_array($des_caracts_type[$txt],$optionsoffert)){
      			  $posX=$wcol[0]+$wcol[1]+5;
      			  $pdf->setXY($posX,$posY);
      			  $pdf->multiCell($wcol[2],4,$template->translate("/facture/offert","F"),0,'R',0);
      			  $posX+=$wcol[2];
      			  $pdf->setXY($posX,$posY);
      			  $pdf->multiCell($wcol[3],4,"" ,0,'R',0);
      			  $posX+=$wcol[3]+$wcol[4];
      			  $pdf->setXY($posX,$posY);
      			  $pdf->multiCell($wcol[5],4,$template->translate("/facture/offert","F"),0,'R',0);
      			  $posX+=$wcol[5];
      			  $pdf->setXY($posX,$posY);
      			  $pdf->multiCell($wcol[6],4,$template->translate("/facture/offert","F"),0,'R',0);
		        }
	        }
		    }
            //  $designation.=" - ".$txt."";
		    $height+=$pdf->nblines($wcol[1]," - ".$txt)*4;
		    $posY=$height+$ly;
	    }

    	// affichage de l'eco participation
    	if($ecopart!=0 && $cde!=1){
	      $prix=$ecopart;
	      if($ht) $prix=$ecopartht;

	      $lx=$wpic+5;
	      $pdf->setXY($lx,$posY);
	      $pdf->multiCell($wcol[0],4,$qte ,0,'C',0);
	      $lx+=$wcol[0];
	      $pdf->setXY($lx,$posY);
	      $libEcoPart=" - ".$template->translate("/devis/eco_participation","F");
	      $pdf->multicell($wcol[1],4,$libEcoPart,0,'',0);
	      $lx+=$wcol[1];
	      
	      $pdf->setXY($lx,$posY);
	      $pdf->multiCell($wcol[2],4,$template->price_format($prix,2,false) ,0,'R',0);
	      $lx+=$wcol[2];
	      $pdf->setXY($lx,$posY);
	      $pdf->multiCell($wcol[3],4,number_format($txtva,1,",","")."%",0,'R',0);
	      $lx+=$wcol[3]+$wcol[4];
	      $pdf->setXY($lx,$posY);
	      ($prix==0) ? $pdf->multiCell($wcol[5],4,$template->translate("/devis/offert","F"),0,'R',0) : $pdf->multiCell($wcol[5],4,$template->price_format($prix,2,false),0,'R',0);
	      $lx+=$wcol[5];
	      $pdf->setXY($lx,$posY);
	      ($prix==0) ? $pdf->multiCell($wcol[6],4,$template->translate("/devis/offert","F"),0,'R',0) : $pdf->multiCell($wcol[6],4,$template->price_format($prix*$qte,2,false),0,'R',0);

          // 		  $designation.=$libEcoPart."\n";
	      $height+=$pdf->nblines($wcol[1],$libEcoPart)*4;
	      $posY=$height+$ly;  
	    }

	  // affichage des garanties
	foreach($des_garanties as $txt=>$prix){
	      $lx=5+$wpic;
	      $pdf->setXY($lx,$posY);
	      $pdf->multiCell($wcol[0],4,$qte ,0,'C',0);

	      $pdf->setXY($lx+$wcol[0],$posY);
    
	      $pdf->multicell($wcol[1],4," - ".stripslashes($txt),0,'',0);
	      if($cde!=1){
		if($prix!=0){
		      if($ht) $prix=$prix/(1+$txtva/100);

		      $posX=$wcol[0]+$wcol[1]+5;
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[2],4,$template->price_format($prix,2,false) ,0,'R',0);
		      $posX+=$wcol[2];
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[3],4,number_format($txtva,1,",","")."%" ,0,'R',0);
		      $posX+=$wcol[3];
		      $pdf->setXY($posX,$posY);
		      $pdf->multicell($wcol[4],4,"",0,'C',0);
		      $posX+=$wcol[4];
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[5],4,$template->price_format($prix,2,false) ,0,'R',0);
		      $posX+=$wcol[5];
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[6],4,$template->price_format(($prix*$qte),2,false),0,'R',0);
		}else{
		      // affichage offert si prix à 0 pour certaines options

		      $posX=$wcol[0]+$wcol[1]+5;
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[2],4,($garantieDuree == 100 ? "" : $template->translate("/devis/offert", "F")),0,'R',0);
		      $posX+=$wcol[2];
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[3],4,"" ,0,'R',0);
		      $posX+=$wcol[3]+$wcol[4];
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[5],4,($garantieDuree == 100 ? "" : $template->translate("/devis/offert", "F")),0,'R',0);
		      $posX+=$wcol[5];
		      $pdf->setXY($posX,$posY);
		      $pdf->multiCell($wcol[6],4,($garantieDuree == 100 ? "" : $template->translate("/devis/offert", "F")),0,'R',0);

		}
	      }

	      $height+=$pdf->nblines($wcol[1]," - ".$txt)*4;
	      $posY=$height+$ly;
      }

      // affichage de la fin de la désignation
      $lx=$wpic+5+$wcol[0];
      $pdf->setXY($lx,$posY);
      $pdf->multicell($wcol[1],4,$designation2,0,'',0);
      $height+=$pdf->nblines($wcol[1],"\n".$designation2)*4;
      $pdf->link($lx,$ly,$wcol[1],$height,$template->translate("/site/[site_code]/url").$template->mkurl("article",$libmodele['artid'],$libmodele));
	
// affichage des prix de la cave
      $lx=5;
      $pdf->setXY($lx,$ly);
      
      if($cde==1){
	  $imgpath="";
	  if($libmodele['artid']>0){
	      $autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".intval($libmodele['artid'])." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	      if($autreimg!="")
		  $imgfile=$libmodele['artid']."_".$autreimg['articlephotoid'];
	      else
		  $imgfile=$libmodele['artid'];
	      $imgpath="configurations/".$host."/images/produits/br/".$imgfile.".jpg";
	  }
	  
	  if($imgpath!="" && file_exists($imgpath)){
	      $size=getimagesize($imgpath);
	      $img_w=$wpic-2;
	      $img_h=$size[1]*$img_w/$size[0];//var_dump($img_h);var_dump($img_h*72/25.4);
	      if($img_h>$height){
		  $img_h=$height-2;
		  $img_w=$size[0]*$img_h/$size[1];
	      }
	      $pdf->image($imgpath,5+($wpic-$img_w)/2,$ly+($height-$img_h)/2,$img_w,$img_h,"JPG");
	  }
	  $pdf->cell($wpic,$height,"",1,0,'L',0);
	  $lx+=$wpic;
      }
      
      $pdf->setXY($lx,$ly);
      $pdf->multiCell($wcol[0],$heightlibele,"$qte" ,0,'C',0);
      
      if($cde!=1){
	    $prix=$puttc;
	    $prixvente=$pventettc;
	    $prixtotal=$montant_ttc;
	    if($ht){
	      $prix=$puht;
	      $prixvente=$pvente;
	      $prixtotal=$montant_ht;
	    }

	    
	    $lx+=$wcol[0]+$wcol[1];
	    $pdf->setXY($lx,$ly);
	    $pdf->multiCell($wcol[2],$heightlibele,$prix ,0,'R',0);
	    $pdf->setdrawcolor(160,5,5);
	    if($remise!=0) $pdf->line($lx+7,$ly-2+$heightlibele/2,$lx+$wcol[2]-3,$ly+2+$heightlibele/2);
	    $pdf->drawColor("white");
	    $lx+=$wcol[2];
	    $pdf->setXY($lx,$ly);
	    $pdf->multiCell($wcol[3],$heightlibele,number_format($txtva,1,",","")."%",0,'R',0);
	    $lx+=$wcol[3];
	    $pdf->setXY($lx,$ly);
	    if($remise!=0) $pdf->multiCell($wcol[4],$heightlibele,"$remise %" ,0,'C',0);
	    $lx+=$wcol[4];
	    $pdf->setXY($lx,$ly);
	    $pdf->multiCell($wcol[5],$heightlibele,"$prixvente" ,0,'R',0);
	    $lx+=$wcol[5];
	    $pdf->setXY($lx,$ly);
	    $pdf->multiCell($wcol[6],$heightlibele,"$prixtotal",0,'R',0);
      }
      
      $lx=$wpic+5;
      $pdf->setXY($lx,$ly);
      $pdf->multicell($wcol[0],$height,"",1,'',0);
      $lx=$wpic+$wcol[0]+5;
      $pdf->setXY($lx,$ly);
      $pdf->multiCell($wcol[1],$height,"" ,1,'R',0);
      $lx+=$wcol[1];
      $pdf->drawColor("black");
      $pdf->line(5,$ly+$height-0.5,205,$ly+$height-0.5);

  }

        
    $i++ ;

    if($facture['provenance']=="sav"){
	$sav=$DB_site->query_first("SELECT * FROM sav WHERE facturid=".$facture['provenanceid']);
	if($sav['id']!="") {
	  $sav_ndossier=$DB_site->query_first("SELECT * FROM sav_fields WHERE name='sav_ndossier' AND savid=".$sav['id']);
	  $sav_nbon=$DB_site->query_first("SELECT * FROM sav_fields WHERE savid=$sav[id] AND name='sav_nbon'");
	  $txt=$template->translate("/facture/numero_reclamation")." : $sav_ndossier[value]\n".$template->translate("/facture/numero_colis","F")." : $sav_nbon[value]";
	  $oldfacture=$DB_site->query_first("SELECT u.*,p.libelle FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) INNER JOIN pays p ON (p.paysid=u.paysid) WHERE f.facturid=".$facture['provenanceid']);
	  $txt.="\n".$oldfacture['nom']." ".$oldfacture['prenom']."\n".$oldfacture['codepostal']." ".$oldfacture['ville']." ".$oldfacture['libelle'];
	  $pdf->setX(5);
	  $hautsav=$pdf->getY();
	  $pdf->Cell($wcol[0],16,"" ,0,0,'C',0);
	  $pdf->setX($wcol[0]+$wcol[1]+5);
	  $pdf->Cell($wcol[2],16,"" ,0,0,'R',0);
	  $pdf->Cell($wcol[3],16,"",0,0,'R',0);
	  $pdf->Cell($wcol[4],16,"" ,0,0,'R',0);
	  $pdf->Cell($wcol[5],16,"",0,0,'R',0);
	  $pdf->Cell($wcol[6],16,"",0,0,'R',0);
	  $pdf->setXY(10+$wcol[0],$hautsav);
	  $pdf->multiCell($wcol[1],4, $txt ,0,'',0);
	  $pdf->line(5,$hautsav+16,205,$hautsav+16);
	  $pdf->Ln(1);
	}
    }
   

    $tvaremise = array();
    // Bons de réduction 
    if (in_array("13", $modules) && $cde<>1)
        {
	if ($facture[cadeauid] != "")
            {
	    $libcadeau="";
	    $rayons=array();
            $infoscadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
            if($infoscadeau['code']!=""){
		$article=$DB_site->query_first("SELECT artcode from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '$facture[cadeauid]'");
		$fin=$template->translate("/devis/conditions_application")." : ";
		if ($article[0] != "")
		  {
		  $fin .= $template->translate("/devis/sur_article")." ".$article[0];
		  }
		else{
		  $rayon=$DB_site->query_first("SELECT libelleFR,c.catid from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '$facture[cadeauid]'");
		  if($rayon['catid']!=""){
		    $rayons[]=$rayon['catid'];
		    $ssrayons=$DB_site->query("SELECT catid FROM categorie WHERE parentid=".$rayon['catid']);
		    while($ssrayon=$DB_site->fetch_array($ssrayons)){
		      $rayons[]=$ssrayon['catid'];
		      $ss2rayons=$DB_site->query("SELECT catid FROM categorie WHERE parentid=".$ssrayon['catid']);
		      while($ss2rayon=$DB_site->fetch_array($ss2rayons)) $rayons[]=$ss2rayon['catid'];
		    }
		    $fin .= $template->translate("/devis/sur_rayon")." ".$rayon[0];
		  }else
		      $fin .= $template->translate("/devis/sur_tout_site","F");
		      
		}

		$totalreduc=array();
		$lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$facturid'");
		while($ligne=$DB_site->fetch_array($lignefactures)){
		    $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']);
		    if($ligne['artcode']==$article[0] || in_array($ligne['catid'],$rayons) || ($article[0]=="" && $rayons==array()))
			$totalreduc[$ligne['tva']]+=$ligne['qte']*($ligne['prix']-$caract[0]);
		}
		$lignefactures=$DB_site->query("SELECT * FROM lignefacturediss WHERE artcode<>'' AND factureid = '$facturid'");
		while($ligne=$DB_site->fetch_array($lignefactures)){
		    $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']);
		    if($ligne['artcode']==$article[0] || in_array($ligne['catid'],$rayons) || ($article[0]=="" && $rayons==array()))
			$totalreduc[$ligne['tva']]+=$ligne['qte']*($ligne['prix']-$caract[0]);
		}

		$libcadeau=$template->translate("/facture/bon_reduction","F")." $infoscadeau[code]";
		if($infoscadeau[valeurcadeau]>0){
		switch ($infoscadeau[typecadeauid]){
		    case '0': // remise sur le poids
		      $libcadeau .= "\n".$template->translate("/facture/remise_de","F")." ".$template->price_format($infoscadeau[valeurcadeau],2,false) ;
		      break;
		    case '1': // remise sur le prix
		      $libcadeau .= "\n".$template->translate("/facture/remise_de","F")." $infoscadeau[valeurcadeau] %" ;
		      break;
		    }
		}
		$i++ ;

	      
		if($infoscadeau[datefin]!=0) $libcadeau.="\n".$template->translate("/facture/date_validite","F")." : ".date("d/m/Y",$infoscadeau[datefin]);
		$libcadeau.="\n$fin";
		$totalcadeau+=$facture['montantcadeau'];

	    }

	    $code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
	    if($code_caviste['code']!=""){
		if($code_caviste['type']==2){
		    $libcadeau=sprintf($template->translate("/facture/code_remise_immediate","F"),$code_caviste['code'],$template->price_format($facture['montantcadeau'],2,false));
		    $totalcadeau+=$facture['montantcadeau'];
		}
		if($code_caviste['type']==1 && $facture['montantcadeau']<$code_caviste['mini']){
		    $libcadeau=sprintf($template->translate("/facture/code_remise_immediate","F"),$code_caviste['code'],$template->price_format($facture['montantcadeau'],2,false));
		    $totalcadeau+=$facture['montantcadeau'];
		}
		$totalreduc=array();
		$lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$facturid'");
		while($ligne=$DB_site->fetch_array($lignefactures)){
		    $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']);
			$totalreduc[$ligne['tva']]+=$ligne['qte']*($ligne['prix']-$caract[0]);
		}
	    }

	    if($libcadeau!=""){
	      $partcadeau=array();
	      foreach($totalreduc as $tx=>$totaltx){
	    
		  $partcadeau[$tx]=$facture[montantcadeau]*$totaltx/array_sum($totalreduc);
	      
		  $tvaremise[$tx] = $partcadeau[$tx]-$partcadeau[$tx]/(1+$tx/100);
	      
		  $nbre = $pdf->nblines($wcol[1],$libcadeau)*4 ;
		  if($partcadeau[$tx]>0){
		    $cadeauttc="-".$template->price_format($partcadeau[$tx],2,false);
		    $cadeauht="-".$template->price_format($partcadeau[$tx]/(1+$tx/100),2,false);
		  }else
		    $cadeauttc=$cadeauht="";

		  $prix=$cadeauttc;
		  if($ht) $prix=$cadeauht;

		  $pdf->drawColor("white");
		  $pdf->SetFont('Arial','',8);
		  $hautcadeau=$pdf->getY();
		  $pdf->setX(5);
		  $pdf->Cell($wcol[0],$nbre,"1" ,1,0,'C',0);
		  $pdf->setX($wcol[0]+$wcol[1]+5);
		  $pdf->Cell($wcol[2],$nbre,$prix ,1,0,'R',0);
		  $pdf->Cell($wcol[3],$nbre,number_format($tx,1,",","")."%" ,1,0,'R',0);
		  $pdf->Cell($wcol[4],$nbre,"" ,1,0,'R',0);
		  $pdf->Cell($wcol[5],$nbre,$prix,1,0,'R',0);
		  $pdf->Cell($wcol[6],$nbre,$prix,1,0,'R',0);
		  $pdf->setXY(5+$wcol[0],$hautcadeau);
		  $pdf->multiCell($wcol[1],4, "$libcadeau" ,1,'',0);
	      }
	      foreach($partcadeau as $tx=>$value) $totalttc[$tx]-=$value;
	      $pdf->drawColor("black");
	      $pdf->line(5,$hautcadeau+$nbre,array_sum($wcol)+5,$hautcadeau+$nbre);
          }
	}
      }

    // frais de port
    if($cde<>1){
        $totalttc[$facture[tvaport]]+=$facture[prixport];
        $tva[$facture[tvaport]] += $facture[prixport] - ($facture[prixport] / (1 + $facture[tvaport]/100)) ;
        $prixport_ht=($facture[prixport] / (1 + $facture[tvaport]/100)) ;
        $prixport_origin=$facture[prixport];

        $pdf->SetFont('Arial','',8);
        $pdf->drawColor("white");
                    
        $institu0=$DB_site->query_first("SELECT libelle".$template->langue." FROM institutionnel WHERE institutionnelid=12");
        $livrlink[0]=$template->translate("/site/[site_code]/url")."/int,12,".url_rewrite($institu0[0]).".htm";
        $institu1=$DB_site->query_first("SELECT libelle".$template->langue." FROM institutionnel WHERE institutionnelid=39");
        $livrlink[1]=$template->translate("/site/[site_code]/url")."/int,39,".url_rewrite($institu1[0]).".htm";
        
        if($facture['incoterms']==""){
            $frais_port=new frais_port($DB_site,$facture['facturid']);
            if($facture['modelivraison'] == 5){
              $livr = $template->translate("/facture/mode_livraison_".$facture[modelivraison])." - ".$facture['txtlivr'];
              $prixport_origin=$facture[prixport];
            }else{
              $livr=sprintf($template->translate("/facture/mode_livraison_".$facture[modelivraison]),$poidstotal);
              $prixport_origin=calcport($poidstotal,0,$facture[modelivraison],$prixtypeinstall,$poidscaves); //total à 0 pour avoir pas avoir les fdp offerts
            }
            
        }else{
            $livr="Incoterms ".$facture['incoterms'];
            $prixport_origin=$facture[prixport];
        }
            
        $prixport_origin_ht=$prixport_origin/(1 + $facture[tvaport]/100);
        $prixorigin=$prixport_origin;
        $prix=$facture[prixport];
        if($ht){
            $prixorigin=$prixport_origin_ht;
            $prix=$prixport_ht;
        }

        if($facture[prixport]==0){
            $livr=$template->translate("/devis/livraison_offerte","F")." : ".$livr;
            $totalcadeau+=$prixport_origin;
        }
        $nbre = $pdf->nblines($wcol[1],$livr)*4 ;

        $lx=5;
        $ly=$pdf->getY();
        if($ly>=260){
            footer($pdf);
            $pdf->addpage();
            $pdf->Header($pdf);
            toptable($pdf);
            $ly=$pdf->getY();
        }
        
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[0],$nbre,"1" ,0,'C',0);
        $lx+=$wcol[0];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[1],4,"$livr" ,0,'',0);
        $pdf->link($lx,$ly,$wcol[1],$nbre,$livrlink[$facture[modelivraison]]);
        $lx+=$wcol[1];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[2],$nbre, $template->price_format($prixorigin,2,false),0,'R',0);
        $pdf->setdrawcolor(160,5,5);
        if($prixorigin!=$prix) $pdf->line($lx+7,$ly-2+$nbre/2,$lx+$wcol[2]-3,$ly+2+$nbre/2);
        $pdf->drawColor("white");
        $lx+=$wcol[2];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[3],$nbre,number_format($facture['tvaport'], 1, ',', ' ')."%" ,0,'R',0);
        $lx+=$wcol[3];
        $pdf->setXY($lx,$ly);
        if($facture['incoterms']=="" && $prixport_ht!=$prixport_origin_ht && $prixport_origin_ht>0){
            $pdf->setfont('arial','',7);
            $pdf->multicell($wcol[4],$nbre,round(-100+(100*$prixport_ht/$prixport_origin_ht))."%",0,'C',0);
            $pdf->setfont('arial','',8);
        }else
            $pdf->multicell($wcol[4],$nbre,"",0,'C',0);
        
        $lx+=$wcol[4];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[5],$nbre, $template->price_format($prix,2,false),0,'R',0);
        $lx+=$wcol[5];
        $pdf->setXY($lx,$ly);
        
        $pdf->multiCell($wcol[6],$nbre, $template->price_format($prix,2,false),0,'R',0);
        $pdf->setY($ly+$nbre);
        
        $pdf->drawColor("black");
        $pdf->line(5,$ly+$nbre,array_sum($wcol)+5,$ly+$nbre);
    }
    $pdf->Ln(1);
    /*******************\
      * INSTALLATION(S) *
     \*******************/
     /*if(!empty($aInstallation)){
          //nice($aInstallation);
          $txt_installation = '';
          $tabRemind = array();
          $txt_libelle = '';
          $montant_total_ht = 0;
          $montant_total_ttt = 0;
          $tva_mes=0;
          foreach($aInstallation as $l_installation){

               //Récupération des différents types d'articles
               $tabTemp = explode('|', $l_installation['lignefactureinstallation_typearticles']);
               foreach($tabTemp as $type_temporaire){

                    if(!in_array($type_temporaire, $tabRemind)){

                         array_push($tabRemind, $type_temporaire);

                         $type_temporaire = intval($type_temporaire);
                         if($type_temporaire > 0){

                              $sql = "SELECT libelle";
                              $sql .= " FROM typearticle";
                              $sql .= " WHERE typearticleid = ".$type_temporaire;
                              $tabLibelle = $DB_site->query_first($sql);

                              if(!empty($txt_libelle)) $txt_libelle .= ' / ';
                              $txt_libelle .= $tabLibelle['libelle'];
                         }
                    }
               }

               $montant_total_ht += $l_installation['lignefactureinstallation_differenceprix'] / (1 + $l_installation['lignefactureinstallation_tauxtva'] / 100);
               $montant_total_ttc += $l_installation['lignefactureinstallation_differenceprix'];
               $tva_mes = $l_installation['lignefactureinstallation_tauxtva'];
          }

          $totalttc[$l_installation['lignefactureinstallation_tauxtva']] += $montant_total_ttc;
          $tva_installation = $montant_total_ttc-$montant_total_ht;

          $txt_installation = "Installation et mise en service de votre << ".$txt_libelle." >>";

          $lx = 5;
          $ly = $pdf->getY();
          $nbre = 6;

          if($nbre + $ly >= 270){

               footer($pdf);
               $pdf->addpage();

               Head($pdf);
               toptable($pdf);

               $ly = $pdf->getY();
               $lx = 5;
          }

          $pdf->setXY($lx,$ly);
          $pdf->multiCell($wcol[0],$nbre,"1" ,0,'C',0);

          $lx += $wcol[0];

          $pdf->setXY($lx,$ly);
          $pdf->multiCell($wcol[1],$nbre,$txt_installation,0,'',0);

          $lx += $wcol[1];

          $pdf->setXY($lx,$ly);
          $pdf->multiCell($wcol[2],$nbre,$template->price_format($montant_total_ttc,2,false),0,'R',0);

          $lx += $wcol[2];

          $pdf->setXY($lx,$ly);
          $pdf->multicell($wcol[3],$nbre,number_format($tva_mes, 1, ',', ' ')."%",0,'C',0);

          $lx += $wcol[3];

          $pdf->setXY($lx,$ly);
          $pdf->multiCell($wcol[4],$nbre,"",0,'R',0);

          $lx += $wcol[4];

          $pdf->setXY($lx,$ly);
          $pdf->multicell($wcol[5],$nbre,$template->price_format($montant_total_ttc,2,false),0,'R',0);

          $lx += $wcol[5];

          $pdf->setXY($lx,$ly);
          $pdf->multiCell($wcol[6],$nbre,$template->price_format($montant_total_ttc,2,false),0,'R',0);

          $lx += $wcol[6];

          $pdf->setY($ly + $nbre);

          $pdf->drawColor("black");
          $pdf->line(5,$ly+$nbre,array_sum($wcol)+5,$ly+$nbre);
     }*/
     $pdf->Ln(1);
if($cde==1){
    $pdf->setX(5);
    $pdf->cell($wcol[0]+$wpic,5,"",0);
    $pdf->cell($wcol[1],5,sprintf($template->translate("/facture/poids_total_brut"),$poidstotal),0);
    if(isset($_GET['ctr_remboursement'])){
        $pdf->Ln(10);
        $pdf->textColor("red");
        $pdf->SetFont('arial','B',10);
        $pdf->setX(5);

        $pdf->multiCell(array_sum($wcol)+32,5,sprintf($template->translate("/facture/ctr_remboursement"),$_GET['ctr_remboursement']),0,'C',0);
        $pdf->textColor("black");
        $pdf->setfont('arial','',8);
    }
}

    if($cde!=1){
	$lx=5+$wcol[0]+$wcol[1]+$wcol[2];
	$ly=$pdf->getY();
	if($ly>=260){
            footer($pdf);
            $pdf->addpage();
            $pdf->Header($pdf);
            $ly=$pdf->getY()+20;
        }
	
	if($facture['provenance']=="mp") $moyenpaiement[$libelle]=$marketplaces[$facture['provenanceid']];
	$pdf->setXY(5,$ly);
	$dateexp=explode("-",$facture[dateexpedition]);
	$txtpaiement=$moyenpaiement[$libelle];
	$nbmoyens=1;
	if($facture[moyenid]==99){
	    $sql = "SELECT r.reglement_id, r.reglement_date, rd.reglement_delais_libelle, r.reglement_montant, m.libelle".$template->langue." AS libelle, r.reglement_moyen";
	    $sql .= " FROM reglement r";
	    $sql .= " INNER JOIN moyenpaiement m ON (m.moyenid = r.reglement_moyen)";
	    $sql .= " INNER JOIN reglement_delais rd ON (rd.reglement_delais_id = r.reglement_delais_id)";
	    $sql .= " WHERE r.facturid = ".$facture['facturid'];
	    $sql .= " ORDER BY r.reglement_id";
	    $req = $DB_site->query($sql);
	    
	    $nbmoyens=$DB_site->num_rows($req);
	    $txtpaiement="";
	    while($reglement = $DB_site->fetch_array($req)){
		$txtpaiement.=sprintf($template->translate("/facture/paiement_par"),$template->price_format($reglement['reglement_montant'],2,false),utf8_decode($reglement['libelle']),date_create($reglement['reglement_date'])->format("d/m/Y")."\n");
	    }
	}

	$regllabel=$template->translate("/facture/moyen_paiement")." : ";
	$pdf->multicell(30,5,$regllabel);
	$pdf->setXY(6+$pdf->GetStringWidth($regllabel),$ly);
	$pdf->multiCell(55,5,$txtpaiement);

  //numero EORI
  $tabPays = array(57,181,111); //FRANCE, CORSE, MONACO
  if(!in_array($user[paysid], $tabPays)){
    $pdf->setXY(61+$pdf->GetStringWidth($regllabel),$ly);
    $pdf->Cell(60,5,"EORI FR : ".EORI_FR);
    $pdf->setXY(61+$pdf->GetStringWidth($regllabel),$ly+5);
    $pdf->Cell(60,5,"EORI UK : ".EORI_GB);
  }
  
	
	$txt="";
	if(!$proforma){
	    $txt=$template->translate("/facture/date_expedition","F")." : $dateexp[2]/$dateexp[1]/$dateexp[0]\n"
	    .$template->translate("/facture/compte_analytique","F")." : ".compte_analytique($user['paysid'],$facture['provenance'],$facture['provenanceid']);
	
	    $tsexpedition=strtotime($facture['dateexpedition']);
	    if($tsexpedition!=false && $user['code411']!="") $txt.="\n".$template->translate("/facture/echeance","F")." : ".date("d/m/Y",$tsexpedition+30*86400);
	}
	
	if($pays['cee']==0) {
	    if($user['paysid']==158)
	      $txt.="\n".$template->translate("/facture/loi_suisse");
	    else
	      $txt.="\n".$template->translate("/facture/loi_hors_ue");
	}elseif($pays['tvaintracom']==1 && $user['tvaintracom']!="") {
	    $txt.="\n".$template->translate("/facture/loi_intracom");
	}elseif(in_array($user['paysid'],array(59,68,135,107))) {
	    $txt.="\n".$template->translate("/facture/loi_dom");
	}
	
	$pdf->setXY(5,$ly+($nbmoyens*5));
	$pdf->multiCell($wcol[0]+$wcol[1]+$wcol[2],4,$txt,0,'L',0);
	
	
	$totalwidth=0;
        for($i=3;$i<=5;$i++) $totalwidth+=$wcol[$i];

	$pdf->setfillcolor(198,199,200);
	foreach($totalttc as $tx=>$ttc){
	    $tht=$ttc/(1+$tx/100);
	    $pdf->setXY($lx,$ly);
	    $pdf->Cell($totalwidth,5,$template->translate("/facture/total_ht")." (".$template->translate("/facture/tva","U")." ".number_format($tx,1,",","")."%)",1,0,'L',1);
	    $pdf->setfont('arial','B',10);
	    $pdf->Cell($wcol[6],5,$template->price_format($tht, 2, false),1,0,'R',1);
	    $ly+=6;
	}
	$pdf->fillColor("white");
 
	foreach($tva as $tx=>$totaltva){
	    $pdf->setXY($lx,$ly);
	    $pdf->Cell($totalwidth,5,$template->translate("/facture/tva","U")." ".$tx."%",1,0,'L',1);
	    $pdf->setfont('arial','B',10);
	    $pdf->Cell($wcol[6],5,$template->price_format($totaltva-$tvaremise[$tx], 2, false),1,0,'R',1);
	    $ly+=6;
	}

	$pdf->setXY($lx,$ly);
	$pdf->fillColor("black");
	$pdf->textColor("white");
	$pdf->drawColor("white");
        $pdf->Cell($totalwidth,5,$template->translate("/facture/montant_total_ttc"),1,0,'L',1);
	$total=array_sum($totalttc);
        $pdf->Cell($wcol[6],5,$template->price_format($total, 2, false),1,0,'R',1);

        $pdf->setfont('arial','',8);
	$pdf->textColor("black");

        $pdf->Ln(6);

    }

    $pdf->SetFont('Arial','',8);

    $pdf->Ln(5);

    footer($pdf);

    if($cde!=1 && !$proforma && $_GET['nopub']!=1 && $template->site_code()=="base"){
      $pdf->addpage();
      $pdf->image($rootpath."images/templates/".$template->site_code()."/".$template->langue."/pub_devis2016".($template->pro?"_pro":"").".jpg",0,0,210);

    }

    // fin
   // $file=basename(tempnam("$rootpath/configurations/$host/factures" , "tmp"));
   // $pdffile="$outputrootpath/configurations/$host/factures/" . $file . ".pdf";
   // rename("$rootpath/configurations/$host/factures/$file" , "$rootpath/configurations/$host/factures/$file.pdf");
    //$pdf->Output($pdffile);
    if(isset($_GET['join']))
        return $pdf->output('','S');
    else
        $pdf->output();

    } 
else 
    {
    echo "Error !!!";
    }


function toptable($pdf){
	global $template;
	global $cde;
	global $wcol;
	global $facture;
	global $ht;
	global $multilangue;
	global $avoir;
	global $autocolor;

	if(isset($_GET['annule'])){
	    $facture[numerofacture]=$_GET['annule'];
	    $facture[codenumerofacture]="CaVin";
	}

	if($ht)
	    $httxt=$facture['devise']." ".$template->translate("/facture/en_ht");
	else
	    $httxt=$facture['devise']." ".$template->translate("/facture/en_ttc");

	$pdf->setfont('arial','B',8);
	$pdf->fillColor("light gray");
	$pdf->textColor("black");
	
	$hauttableau=$pdf->PageNo()==1?87:40;
	$pdf->setXY(5,$hauttableau);
	$hline=8;
	
	$num=$template->translate("/facture/commande_n","F").$facture[facturid];
	if($facture[mp_facturid]!=""){
	    $num.=" / ".$facture[mp_facturid];
	}
	
	if($facture['provenance']=="sav" || $facture['provenance']=="avoir"){
	    $type=$template->translate("/facture/sav","U");
	    if($avoir) $type=$template->translate("/facture/avoir");
	    $num.="\n (".$type." ".$template->translate("/facture/commande_n").$facture['provenanceid']." )";
      $hline=4;
	}
	
	$cdeclient="";
	
	if($facture['clientcde']!=""){
	    $cdeclient="\n".$template->translate("/facture/cde_client","F")." ".$facture['clientcde'];
	    $hline=4;
	}
	
	if ($cde == "1") 
	{
	
	$pdf->cell(55,8,$template->translate("/facture/date_bon_livraison","F")." : ".date("d/m/Y"),0,0,'C',1);
	$dateafftmp=explode("-",$facture[datefacture]);
	$dateaff="$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
	$pdf->Cell(57,8,$template->translate("/facture/date_commande")." : $dateaff",0,0,'C',1);
	$hline=8/$pdf->nblines(65,$num);
	$pdf->multiCell(65,$hline,$num.$cdeclient,0,'C',1);
	$pdf->setXY(182,$hauttableau);
	$pdf->cell(24,8,$template->translate("/facture/page","F")." ".$pdf->page,0,0,'C',1);

	$pdf->setdrawcolor(0);
	$pdf->line(60,$hauttableau+0.5,60,$hauttableau+7.5);
	$pdf->line(114,$hauttableau+0.5,114,$hauttableau+7.5);
	$pdf->line(182,$hauttableau+0.5,182,$hauttableau+7.5);
	} 
	else 
	{

	if($facture[dateexpedition]=="0000-00-00") $facture[dateexpedition]=$facture[datefacture];
	$dateafftmp=explode("-",$facture[dateexpedition]);
	$dateaff="$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
	$pdf->cell(55,8,$template->translate("/facture/date_facture","F")." : ".$dateaff,0,0,'C',1);
	$dateafftmp=explode("-",$facture[datefacture]);
	$dateaff="$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
	$pdf->Cell(57,8,$template->translate("/facture/date_commande","F")." : $dateaff",0,0,'C',1);
	$pdf->multiCell(65,$hline,$num.$cdeclient,0,'C',1);
	$pdf->setXY(182,$hauttableau);
	$pdf->cell(24,8,$template->translate("/facture/page","F")." ".$pdf->PageNo(),0,0,'C',1);

	$pdf->setdrawcolor(0);
	$pdf->line(60,$hauttableau+0.5,60,$hauttableau+7.5);
	$pdf->line(114,$hauttableau+0.5,114,$hauttableau+7.5);
	$pdf->line(182,$hauttableau+0.5,182,$hauttableau+7.5);
	
	}

	$pdf->Ln(10);

	$pdf->fillColor($autocolor);
	$pdf->drawColor("white");
	$pdf->textColor("white");
	$pdf->setfont('arial','B',8);

	$lx=5;
	$ly=$pdf->getY();
	$pdf->setX($lx);
	
	if($cde==1){
	    $pdf->cell(32,8,"",1,0,'C',1);
	    $lx+=32;
	}
	
	$pdf->cell($wcol[0],8,$template->translate("/facture/qte","F"),1,0,"C",1);
	$lx+=$wcol[0];
	$pdf->cell($wcol[1],8,$template->translate("/facture/designation","F"),1,0,'C',1);
	$lx+=$wcol[1];
	    
	if($cde<>1){
	    $pdf->multicell($wcol[2],4,$template->translate("/facture/prix_tarif","F")."\n ",1,"C",1);
	    $lx+=$wcol[2];
	    $pdf->setXY($lx,$ly);
	    $pdf->cell($wcol[3],8,$template->translate("/facture/tva","U"),1,0,"C",1);
	    $lx+=$wcol[3];
	    $pdf->cell($wcol[4],8,"",1,0,"C",1);
	    $lx+=$wcol[4];
	    $pdf->multicell($wcol[5],4,$template->translate("/facture/prix_unit","F")."\n ",1,"C",1);
	    $lx+=$wcol[5];
	    $pdf->setXY($lx,$ly);
	    $pdf->multicell($wcol[6],4,$template->translate("/facture/prix_total","F")."\n ",1,"C",1);
	
	    $pdf->setfont('arial','',7);
	    $pdf->setXY(5+$wcol[0]+$wcol[1],$ly+4);
	    $pdf->Cell($wcol[2],4,$httxt,0,0,'C');
	    $pdf->setXY(5+$wcol[0]+$wcol[1]+$wcol[2]+$wcol[3]+$wcol[4],$ly+4);
	    $pdf->Cell($wcol[5],4,$httxt,0,0,'C');
	    $pdf->setXY(5+$wcol[0]+$wcol[1]+$wcol[2]+$wcol[3]+$wcol[4]+$wcol[5],$ly+4);
	    $pdf->Cell($wcol[6],4,$httxt,0,0,'C');
	
	}
	
	$pdf->textColor("black");
	$pdf->setfont('arial','',8);
	$pdf->setY($hauttableau+18);

}

function footer($pdf){
	global $template;
	if($pdf->getY()>270) return;
	$pdf->setY(275);
	$pdf->SetFont('Arial','',6);
	$pdf->Cell(200,4,sprintf($template->translate("/facture/copyright/[site_code]"),date("Y")),0,0,'C');
	$pdf->Ln(3);
	$pdf->Cell(200,4,$template->translate("/facture/reserves_propriete"),0,0,'C');
	$pdf->Ln(3);
	$pdf->multicell(200,3,$template->translate("/facture/loi"),0,'C',0);
	$pdf->SetFont('Arial','',8);
}

function compte_analytique($paysid,$prov,$provid){
	  global $DB_site;

	  if($prov=="mp")
	     $sql="SELECT * FROM compte_analytique WHERE paysid=".intval($paysid)." AND site='".$provid."'";
	  else
	    $sql="SELECT * FROM compte_analytique WHERE paysid=".intval($paysid)." AND site='macaveavin'";
         
	  $ca=$DB_site->query_first($sql);
	  return $ca['code'];
}


function calcport($poidstotal,$prixtotal,$livrmode,$prixtypeinstall,$poidscaves=array()){
	global $DB_site;
	global $facture;
	global $frais_port;

	if($prixtotal==0 && $frais_port->prixbarreht>0)
	    $return=$frais_port->prixbarrettc($facture['tvaport']);
	else
	    $return=$frais_port->prixttc($facture['tvaport']);
	
	$install=pays_install($facture[paysid]);
	if(($livrmode=='1' || $livrmode=='4') && $install!=false){
		if($facture['codepostal']=="") return false;
		
		if($install=="sls") $tarif=prixsls($facture[codepostal],$poidstotal*1000,$facture[paysid],$prixtypeinstall);
		if($install=="enigma" && $poidscaves!=array()) $tarif=prix_enigma($poidscaves,$facture['site_code']);
		if($tarif==0) return false;
		$return+=$tarif;
		
		if($livrmode==4) $return+=prix_montage($facture['facturid']);
	}
    if($livrmode=='7'){
        if($facture['codepostal']=="") return false;
        $tarif = tarifMES($facture[facturid]);
        $tarif += prixsls($facture['codepostal'], $poidstotal * 1000, $facture['paysid'], $prixtypeinstall);
        $return = $tarif;
    }
	return $return;
}

function garantie_fourn($DB,$lf, $date_exp_fourn){
  $sql  = "SELECT lfg.dureeReelle FROM lignefacturegarantie lfg";
  $sql .= " INNER JOIN garantie g ON lfg.garantieid=g.garantieid";
  $sql .= " WHERE g.utilisation_garantie = 0";
  $sql .= " AND lfg.lignefactureid ='".$lf."'";
  $duree = $DB->query_first($sql);

  $dateEffetGarantie=date_create($date_exp_fourn);
  date_add($dateEffetGarantie,date_interval_create_from_date_string($duree[0]." years"));
  date_add($dateEffetGarantie,date_interval_create_from_date_string("-1 day"));
  $dateEffetGarantie = date_format($dateEffetGarantie,"Y-m-d");

  return $dateEffetGarantie;

}

?>
