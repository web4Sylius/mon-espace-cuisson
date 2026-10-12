<?php

ini_set("display_errors", "on");
// error_reporting(E_ERROR | E_WARNING | E_PARSE);

ob_start();
require_once("global.php");
require_once("ficheproduit.php");
require_once("fichevin.php");
require_once("pubdevis.php");
ob_end_clean();

// id des options où on peut marquer offert quand le prix est à 0
$optionsoffert = array(4,6,7,8);

function french_date($joindate)
    { 
    $monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'); 
    $days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'); 
    return $days[date("w",$joindate)].' '.date("d",$joindate).' '.$monthes[date("n",$joindate) ].' '.date("Y",$joindate); 
    }

if ($_GET['p'] != strrev(md5($devisid . "devis"))) {
    print "<img src=\"/images/templates/base/FR/logo.jpg\" width=\"400\">
  <p>Bonjour,</p>
  <p>Désolé, ce lien a expiré et n'est plus actif.</p>
  <p>Merci de contacter nos services commerciaux par téléphone au +33.(0)4.81.91.90.40 ou par mail à <a href=\"mailto:contact@mon-espace-cuisson.com\">contact@mon-espace-cuisson.com</a> ou par internet <a href=\"" . $template->translate("/site/[site_code]/url") . "\">" . $template->translate("/site/[site_code]/url") . "</a> afin de vous expédier immédiatement un nouveau devis.</p>
  <p>Nous vous remercions de votre confiance et de l'intérêt que vous portez à nos produits. Nous restons à votre disposition pour toute information complémentaire.</p>
  <p>Cordialement,
  <br>L'Equipe de Mon Espace Cuisson
  <br>Pour passer commande, rendez vous sur <a href=\"" . $template->translate("/site/[site_code]/url") . "\">" . $template->translate("/site/[site_code]/url") . "</a> ou téléphonez au +33 (0)4.81.91.90.40.</p>";
    
    exit;
}
//affichage du prix des options
$afficheprixopt = false;
if(isset($_GET['opt']) && $_GET['opt'] == 1){
    $afficheprixopt = true;
}

$libelle = "libelle" . $template->langue;

$facture       = $DB_site->query_first("SELECT * FROM facture f, pays p WHERE facturid = '$devisid' and f.paysid = p.paysid");
$user          = $DB_site->query_first("SELECT *,u.tvaintracom AS tvaintracom FROM utilisateur u, pays p WHERE userid = '$facture[userid]' and u.paysid = p.paysid");
$numerofacture = $facture['codenumerofacture'] . $facture['numerofacture'];
$moyenpaiement = $DB_site->query_first("SELECT * FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
$pays          = $DB_site->query_first("SELECT * FROM pays WHERE paysid=" . $user['paysid']);

if ($facture['site_code'] == "")
    $facture['site_code'] = "base";
$template->changesite($facture['site_code'], $user['langue']);
//$template->pro = $template->site_code() == "base" && $user['raisonsociale'] != "";
$template->pro = false;
$autocolor     = $template->pro ? "bleu" : "dore";
$site_code     = $template->site_code();

$nomboutique = ${$title};



$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle("Devis");
$pdf->SetAuthor("Acilyon");
$pdf->SetAutoPageBreak(true, 5);
//$pdf->SetFillColor(230,230,230);

$ht = false;
$ht = ($user['raisonsociale'] != "");

head($pdf);

$pdf->setXY(10, 29);
$pdf->SetFont('Arial', 'B', 10);
$pdf->textColor("black");
$pdf->Cell(70, 5, $template->translate("/devis/expediteur"));
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(70, 4, $template->translate("/devis/commercant/base/raison_sociale"));
$pdf->Ln(4);
$pdf->Cell(70, 4, $template->translate("/devis/commercant/[site_code]/adresse"));
$pdf->Ln(4);
$pdf->Cell(70, 4, $template->translate("/devis/commercant/[site_code]/ville"));

$pdf->fillColor($autocolor);
$pdf->rect(54, 27, 72, 7, "F");
$pdf->fillColor("light gray");
$pdf->rect(54, 34, 72, 40, "F");

$pdf->setXY(57, 28.5);
$pdf->SetFont('Arial', 'B', 8);
$pdf->textColor("white");
$pdf->Cell(70, 5, $template->translate("/devis/adresse_livraison", "U"));
$pdf->setXY(57, 36);
$pdf->textColor("black");
$pdf->Cell(70, 4, "$facture[raisonsociale]");
$pdf->setXY(57, 40);
$pdf->Cell(70, 4, "$facture[nom] $facture[prenom]");
$pdf->SetFont('Arial', '', 8);
$pdf->setXY(57, 44);
$pdf->Cell(70, 4, "$facture[adresse]");
$pdf->setXY(57, 48);
$pdf->Cell(70, 4, "$facture[codepostal] $facture[ville]");
$pdf->setXY(57, 52);
$pdf->Cell(70, 4, "$facture[libelle]");
$pdf->setXY(57, 56);
$pdf->Cell(70, 4, $template->translate("/devis/tel", "F") . " : $facture[telephone]");
$pdf->setXY(57, 60);
$pdf->multiCell(70, 4, $template->translate("/devis/info_transporteur", "F") . " : " . stripslashes($facture['infotranspo']));


$pdf->fillColor($autocolor);
$pdf->rect(133.5, 27, 72, 7, "F");
$pdf->fillColor("light gray");
$pdf->rect(133.5, 34, 72, 40, "F");

$pdf->setXY(137, 28.5);
$pdf->SetFont('Arial', 'B', 8);
$pdf->textColor("white");
$pdf->cell(70, 5, $template->translate("/devis/adresse_facturation", "U"));

$pdf->setXY(137, 36);
$pdf->textColor("black");
$pdf->cell(70, 4, $user['raisonsociale']);
$ajust=4;
if(!$template->pro){
   $pdf->setXY(137, 40);
   $pdf->Cell(70, 4, "$user[nom] $user[prenom]");
   $ajust = 0;
}
$pdf->SetFont('Arial', '', 8);
$pdf->setXY(137, 44-$ajust);
$pdf->Cell(70, 4, "$user[adresse]");
$pdf->setXY(137, 48-$ajust);
$pdf->Cell(70, 4, "$user[codepostal] $user[ville]");
$pdf->setXY(137, 52-$ajust);
$pdf->Cell(70, 4, "$user[libelle]");
$pdf->setXY(137, 56-$ajust);
$pdf->multiCell(70, 4, $template->translate("/devis/tel", "F") . " : $user[telephone] \n" . $template->translate("/devis/gsm") . " : $user[portable]");

if ($user['tvaintracom'] != "") {
    $pdf->setXY(137, 64-$ajust);
    $pdf->multiCell(70, 4, $template->translate("/devis/n_tva", "F") . " : $user[tvaintracom]");
}

$devisid_img = $devisid;
while (strlen($devisid) < 8) {
    $devisid = "0" . $devisid;
}

$wcol = array(31,7,108,21,12,22);

toptable($pdf);


$lignefactures = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$devisid'");

//DEBUT GESTION DE LA DISSOCIATION   ********
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
$sql.=" ORDER BY artcode DESC";
$lignefactures=$DB_site->query($sql);

$leslignesfactures = $lignefactures;

// FIN DE GESTION DE LA DISSOCIATION   *******


$tva         = array();
$i           = 0;
$j           = 1;
$poidstotal  = 0;
$poidscaves  = array();
$prixtypeinstall="";
$fichesj     = array();
$typesfiche=array(1,2,3,4,5,7,8,10,12,13,14,42,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,71,72,73,74,75,76,77,78,80,95,109,110,111,112,113,114,115,116,117,120,121,122,123,126,132,134,135,140,145,158,159,160,161,162,163,164,165,166,168,169,170,171,172,173,174,175,176,177,178,187,190,212,229,232,233,244,245,246,250,251,252,253,254,256,257,258,263,265,266,270,274,275,277,278,279,280,281,282,283,292,296,297,298,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,320,323,324,325,326,332,336,337,338,339,340,341);

$codecaviste = 0;
$lignekdo    = 0;
$totalcadeau = 0;
$totalttc    = array();

$pdf->textColor("black");


while ($lignefacture = $DB_site->fetch_array($lignefactures)) {
    $pdf->drawColor("white");
    $diffprix      = 0;
    $des_caracts   = array();
    $des_garanties = array();
    $calcdesign    = "";
    if ($lignefacture['artid'] > 0)
        $libmarque = $DB_site->query_first("SELECT * from article_marque am, marque m where am.marqueid = m.marqueid and am.artid = '$lignefacture[artid]'");
    else
        $libmarque['libelle'] = "";
    
    $sql = "SELECT bdd_table FROM typearticle";
    $sql .= " INNER JOIN article a USING(typearticleid)";
    $sql .=" WHERE a.artid= '$lignefacture[artid]'";
    $table_type = $DB_site->query_first($sql);
    if($table_type[0] == '') $table_type[0] = 'article_piano';
    $libmodele = $DB_site->query_first("SELECT * FROM article as a INNER JOIN $table_type[0] as spec USING(artid) WHERE a.artid = '$lignefacture[artid]'");
    $caracts   = $DB_site->query("SELECT * FROM lignefacturecaracteristique lc, caracteristiquevaleur cv WHERE lc.caractvalid=cv.caractvalid AND lc.lignefactureid = '$lignefacture[lignefactureid]' ORDER BY libelle_caract");

    $garanties = $DB_site->query("SELECT lfg.*, g.cumul, g.garantie_client_id, SUM(lfg.differenceprix) AS differenceprixTotale FROM `lignefacturegarantie` lfg
                INNER JOIN garantie g ON (g.garantieid = lfg.garantieid)
                WHERE lfg.`lignefactureid`='$lignefacture[lignefactureid]'
                GROUP BY lfg.garantieid ORDER BY lfg.type DESC, lfg.duree DESC LIMIT 1;");
    if ($lignefacture['artcode'] == "Code caviste") {
        $codecaviste = $facture['cadeauid'];
        $lignekdo    = $lignefacture;
    }
    $libmodele['marque'] = $libmarque['libelle'];
    $libmodele['emballage_hauteur']=$libmodele['emballage_hauteur1']+$libmodele['emballage_hauteur2']+$libmodele['emballage_hauteur3'];
    $libmodele['emballage_largeur']= $libmodele['emballage_largeur1']+ $libmodele['emballage_largeur2']+$libmodele['emballage_largeur3'];
    $libmodele['emballage_profondeur']=$libmodele['emballage_profondeur1']+$libmodele['emballage_profondeur2']+$libmodele['emballage_profondeur3'];
    $libmodele['emballage_poids']=$libmodele['emballage_poids1']+$libmodele['emballage_poids2']+$libmodele['emballage_poids3'];
    
    // options de l'article
    while ($caract = $DB_site->fetch_array($caracts)) {
        $des_caracts["$caract[libelle_caract] : $caract[libelle_caractval]"]      = $caract['differenceprix'];
        $des_caracts_type["$caract[libelle_caract] : $caract[libelle_caractval]"] = $caract['caractid'];
        $calcdesign .= "\n$caract[libelle_caract] : $caract[libelle_caractval]";
        $diffprix += ($caract['differenceprix']);
    }
    $total_caracts = $diffprix;
    ksort($des_caracts);

    //garanties de l'article
    while ($garantie = $DB_site->fetch_array($garanties)) {
        if ($garantie['duree'] != 99 && $garantie['type'] != 6) {
            $an = $template->translate("/article/garantie_type/an");
            if ($garantie['duree'] > 1) {
                $an = $template->translate("/article/garantie_type/ans");
            }
            $anneeGarantie = ($garantie['duree'] == 3 && $garantie['cumul'] == 1 ? 5 : $garantie['duree']) . " " . $an;
        } elseif($garantie['type'] == 6) {
            $anneeGarantie = $template->translate("/article/garantie_type/abonnement");
        }else{
            $anneeGarantie = $template->translate("/article/garantie_type/a_vie");            
        }
        if ($garantie['garantie_client_id'] == 1) {
           $usage=$template->translate("/article/garantie_type/usageparticulier");
        } else {
            $usage=$template->translate("/article/garantie_type/usagepro");
        }

        if($garantie['type'] == 6){ // gestion de l'affichage du tarif Platinehome pour le devis
            $test = $DB_site->query_first("SELECT * FROM lignefacture where lignefactureid='".$lignefacture['lignefactureid']."'");
            $count = 0 ;

            if($test){
                $tarifph = $DB_site->query_first("SELECT offre_prix, count FROM platinehome WHERE lignefactureid ='".$lignefacture['lignefactureid']."'");
                $leTarif = $tarifph['offre_prix'];
                $count = $test['qte'] * $tarifph['count'];
                 
            }else{
                $test = $DB_site->query("SELECT * FROM lignefacture where lignefacturedissid='".$lignefacture['lignefactureid']."'");
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

            $libGarantie = $template->translate("/article/garantie_type/garantie", 'F') . " " . utf8_decode($anneeGarantie) . " ( ".$tarifPH." / ".$template->translate("/garantie/platine_home/mois").")";
        }else{
            $libGarantie = $template->translate("/article/garantie_type/garantie", 'F') . " " . $anneeGarantie . " " . html_entity_decode($template->translate("/article/garantie_type/" . $garantie['type'])) . " " . DAOpays::getPaysById($DB_site, $garantie['pays'])." ".$usage;
        }
        $garantiePH = $garantie['duree'];
        
        $des_garanties[$libGarantie] = $garantie['differenceprixTotale'];
        $calcdesign .= "\n$libGarantie";
        $diffprix += $garantie['differenceprixTotale'];
    }

    $diffprixht = round($diffprix / (1 + $lignefacture['tva'] / 100), 2);

    
    // début de la désignation
    $lignefacture['libelle'] = str_replace("&euro;", chr(128), $lignefacture['libelle']);
    $designation           = html_entity_decode($lignefacture['libelle'] . " " . $libmarque['libelle'] . ($libmodele['gamme' . $template->langue] != "" ? " " . $libmodele['gamme' . $template->langue] : ""), ENT_COMPAT, 'ISO-8859-15');
    if ($libmodele['artcode'] != "")
        $designation .= " Ref. $libmodele[artcode]";
    $designation .= "\n";
    if($libmodele['gaz']!="")
        $designation.="\n - ".$template->translate("/devis/type_gaz","F")." : ".$template->translate($libmodele['gaz']);
    $heightlibele = $pdf->nblines($wcol[2], $designation) * 4;
    $calcdesign   = $designation . "\n" . $calcdesign;
    
    //if(in_array($libmodele[catid],$typesfiche)) $fichesj[]=$libmodele;
    
    $heighteco      = 0;
    $ecopart        = 0;
    $ecopartht      = 0;
    $ecoparthttotal = 0;
    if ($site_code == "base" && $lignefacture['ecopart'] > 0 && $lignefacture['prix'] > 0) {
        $ecopart=$lignefacture['ecopart'];
        $ecopartht=round($ecopart/(1+$lignefacture['tva']/100),2);
        $ecoparthttotal = $ecopartht * $lignefacture['qte'];
        $calcdesign .= "\n";
    }
    
    $designation2 = "";

    //if($libmodele[rangements_txt]!="") $designation2.="\n - ".$template->translate("/devis/accessoires_inclus","F")." : ".str_replace("<br>","\n",$libmodele[rangements_txt]);
    if ($libmodele['emballage_hauteur'] != "" || $libmodele['emballage_largeur'] != "" || $libmodele['emballage_profondeur'] != "" || $libmodele['emballage_poids'] != "" || $libmodele['hauteur'] != "" || $libmodele['largeur'] != "" || $libmodele['profondeur'] != "" || $libmodele['poids'] != 0)
        $designation2 .= "\n - " . $template->translate("/devis/dim_poids_net") . " " . $libmodele['hauteur'] . "*" . $libmodele['largeur'] . "*" . $libmodele['profondeur'] . " / " . round($libmodele['poids'] / 1000, 1);
    $delai = delailivraison($libmodele, $lignefacture['qte']);
    if ($delai != false)
        $designation2 .= "\n - " . $template->translate("/devis/delai_livraison", "F") . " : " . $delai['base_delai'] . " " . $template->translate("/devis/jours", "L");
    if ($delai['date_appro'] != false)
        $designation2 .= "\n - " . $template->translate("/devis/date_reappro", "F") . " : " . date($template->translate("/site/date_format"), $delai['date_appro']);
    $calcdesign .= "\n" . $designation2;
    
    
    $article_prices = article_prices($lignefacture['artid'], $template->tva_ref(), $template->site_code(), true);
    $puht           = floatval($article_prices['prixconseille']) - $ecopartht;
    $qte            = $lignefacture['qte'];
    $pvente         = ($lignefacture['prix'] / (1 + $lignefacture['tva'] / 100)) - $ecopartht - $diffprixht;

    if ($pvente < 0)
        $totalcadeau -= ($qte * $lignefacture['prix']);
    $pventettc = $template->price_format($lignefacture['prix'] - $ecopart - $diffprix, 2, false);
    $remise    = 0;
    if ($puht > 0)
        $remise = -round(($puht - $pvente) / $puht * 100);
    //$montant_ht = $template->price_format(($pvente * $qte), 2, false);
    //$pvente     = $template->price_format($pvente, 2, false);

    if($article_prices!==false){
        $puttc      = $template->price_format(floatval($article_prices['prixconseille']) * (1 + $lignefacture['tva'] / 100) - $ecopart, 2, false);
        //$puht       = $template->price_format($puht, 2, false);
    }else{
        $puttc=$pventettc;
        $puht=$pvente;
    }

    $txtva      = $lignefacture['tva'];
    $tva[$lignefacture['tva']] += ($lignefacture['prix'] * $qte) - (($lignefacture['prix'] * $qte) / (1 + $lignefacture['tva'] / 100));
    $montant_ttc = ($lignefacture['prix'] - $diffprix - $ecopart) * $qte;
    $montant_ttc_total = $lignefacture['prix'] * $qte;
    
    $totalttc[$txtva] += ($qte * $lignefacture['prix']);
    
    $montant_ttc = floatval($montant_ttc);
    $poidstotal += (floatval(str_replace(",", ".", $libmodele['emballage_poids'])) * $qte);
    if ($libmodele['typearticleid'] == 1)
        $poidscaves[$libmodele['emballage_poids']] += $qte;
    install_type($libmodele['artid']);
    
    $i++;
    
    $height = 5;
    $nbre   = ceil(strlen($designation) / 80);
    if ($nbre > 1)
        $height = $nbre * 5;
    
    $pdf->SetFont('Arial', '', 8);
    //$pdf->Cell(30,$height,stripslashes("$lignefacture[artcode]"),1,0,'',1);
    
    $ly     = $pdf->getY();
    $lx     = 5 + $wcol[0] + $wcol[1];
    $height = $pdf->nblines($wcol[2], $calcdesign) * 4;
    // calcul si faut ajouter une page
    if ($height + $ly >= 270) {
        footer($pdf);
        $pdf->addpage();
        Head($pdf);
        toptable($pdf);
        $ly = $pdf->getY();
        $lx = 5 + $wcol[0] + $wcol[1];
    }
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[2], 4, "$designation", 0, '', 0);
    $height = $pdf->nblines($wcol[2], $designation) * 4;
    $posY   = $height + $ly;
    
    // affichage des options
    $prix_opt_ok = false;
    $prix_opt_tt=$prix_opt_t=0;
    foreach ($des_caracts as $txt => $prix) {
        $pdf->setXY($wcol[0] + $wcol[1] + 5, $posY);
        $pdf->SetFont('Arial','',8);
        $pdf->multicell($wcol[2], 4, " - " . stripslashes($txt), 0, '', 0);

        $pdf->SetFont('Arial','I',8);
        if ($prix != 0) {

            //verif s'il y a au moins un prix > 0 pour les options
            $prix_opt_ok = true;

            if ($ht)
                $prix = $prix / (1 + $txtva / 100);
            
            $posX = $wcol[0] + $wcol[1] + $wcol[2] + 5;
            $pdf->setXY($posX, $posY);
            //affichage du prix des options, colonne prix tarif
            if($afficheprixopt==true){
                $pdf->multiCell($wcol[3], 4, $template->price_format($prix, 2, false), 0, 'R', 0);
            }else{
                $pdf->multiCell($wcol[3], 4, "", 0, 'R', 0);
            }
            
            $posX += $wcol[3];

            $pdf->setXY($posX, $posY);
            $pdf->multicell($wcol[4], 4, "", 0, 'C', 0);
            $posX += $wcol[4];


            $pdf->setXY($posX, $posY);
            //affichage du prix des options, colonne prix total
            if($afficheprixopt==true){
                $pdf->multiCell($wcol[5], 4, $template->price_format(($prix * $qte), 2, false), 0, 'R', 0);
            }else{
                $pdf->multiCell($wcol[5], 4, "", 0, 'R', 0);
            }
        } else {
            // affichage offert si prix à 0 pour certaines options
            if (in_array($des_caracts_type[$txt], $optionsoffert)) {
                $posX = $wcol[0] + $wcol[1] + $wcol[2] + 5;
                $pdf->setXY($posX, $posY);
                if($afficheprixopt==true){
                    $pdf->multiCell($wcol[3], 4, $template->translate("/devis/offert", "F"), 0, 'R', 0);
                }else{
                    $pdf->multiCell($wcol[3], 4, "", 0, 'R', 0);
                }
                
                $posX += $wcol[3];
                $posX += $wcol[4];

                $pdf->setXY($posX, $posY);
                if($afficheprixopt==true){
                    $pdf->multiCell($wcol[5], 4, $template->translate("/devis/offert", "F"), 0, 'R', 0);
                }else{
                    $pdf->multiCell($wcol[5], 4, "", 0, 'R', 0);
                }
                
            }
        }

        $height += $pdf->nblines($wcol[2], " - " . $txt) * 4;
        $posY = $height + $ly;

        //ajoute le prix des options dans le prix de l'article de base, si on n'affiche pas le prix des options
        if($afficheprixopt==false){
            $prix_opt_tt+=$prix * $qte;
            $prix_opt_t+=$prix;
        }
    }
    
    $pdf->SetFont('Arial', '', 8);
    
    // ************** affichage de l'eco participation******************//
    if ($ecopart != 0) {
        $prix = $ecopart;
        if ($ht)
            $prix = $ecopartht;
        
        $lx = 5 + $wcol[0];
        $pdf->setXY($lx, $posY);
        $pdf->multiCell($wcol[1], 4, $qte, 0, 'C', 0);
        $lx += $wcol[1];
        $pdf->setXY($lx, $posY);
        $libEcoPart = " - " . $template->translate("/devis/eco_participation", "F");
        $pdf->multicell($wcol[2], 4, $libEcoPart, 0, '', 0);
        $lx += $wcol[2];
        // Affichage de l'ecopart colonne prix unitaire
        $pdf->setXY($lx, $posY);
        $pdf->SetFont('Arial', 'I', 8);
        if($afficheprixopt==true){
            $pdf->multiCell($wcol[3], 4, $template->price_format($prix, 2, false), 0, 'R', 0);
        }
        else{
            $pdf->multiCell($wcol[3], 4, "", 0, 'R', 0);
        }

        $lx += $wcol[3];
        $lx += $wcol[4];
        // ******* affichage ecopart colonne prix total *** //
        $pdf->setXY($lx, $posY);
        if($afficheprixopt==true){
            ($montant_ttc == 0) ? $pdf->multiCell($wcol[5], 4, $template->translate("/devis/offert", "F"), 0, 'R', 0) : $pdf->multiCell($wcol[5], 4, $template->price_format($prix * $qte, 2, false), 0, 'R', 0);
        }
        else{
            $pdf->multiCell($wcol[5], 4, "", 0, 'R', 0);
        }
        
        
        //  $designation.=$libEcoPart."\n\n";
        $height += $pdf->nblines($wcol[2], $libEcoPart) * 4;
        $posY = $height + $ly;
    }
    $pdf->SetFont('Arial', '', 8);

    // affichage des garanties
    $prix_gar_ok=false;
    foreach ($des_garanties as $txt => $prix) {
        $lx = 5 + $wcol[0];
        $pdf->setXY($lx, $posY);
        $pdf->multiCell($wcol[1], 4, $qte, 0, 'C', 0);
        
        $pdf->setXY($wcol[0] + $wcol[1] + 5, $posY);
        
        $pdf->multicell($wcol[2], 4, " - " . stripslashes($txt), 0, '', 0);
        
        $pdf->SetFont('Arial', 'I', 8);
        if ($prix != 0) {

            //verif s'il y a au moins un prix > 0 pour les garanties
            $prix_gar_ok = true;

            if ($ht)
                $prix = $prix / (1 + $txtva / 100);
            
            $posX = $wcol[0] + $wcol[1] + $wcol[2] + 5;

            // affichage tarif garantie colonne prix unitaire 
            $pdf->setXY($posX, $posY);
            
            if($afficheprixopt==true){
                $pdf->multiCell($wcol[3], 4, $template->price_format($prix, 2, false), 0, 'R', 0);
            }
            else{
                $pdf->multiCell($wcol[3], 4, "", 0, 'R', 0);
            }
            
            $posX += $wcol[3];

            $pdf->setXY($posX, $posY);
            $pdf->multicell($wcol[4], 4, "", 0, 'C', 0);
            $posX += $wcol[4];
            // affichage tarif garantie colonne prix total
            $pdf->setXY($posX, $posY);
            if($afficheprixopt==true){
                $pdf->multiCell($wcol[5], 4, $template->price_format(($prix * $qte), 2, false), 0, 'R', 0);
            }
            else{
                $pdf->multiCell($wcol[5], 4, "", 0, 'R', 0);
            }
            
			
        } else {
            // affichage offert si prix à 0 pour certaines options

            $posX = $wcol[0] + $wcol[1] + $wcol[2] + 5;
            // affichage tarif garantie colonne prix unitaire
            $pdf->setXY($posX, $posY);
            if($afficheprixopt==true){
                $pdf->multiCell($wcol[3], 4, ($garantiePH == 100 ? "" : $template->translate("/devis/inclus", "F")), 0, 'R', 0);
            }
            else{
                $pdf->multiCell($wcol[3], 4, "", 0, 'R', 0);
            }
			
            $posX += $wcol[3];
            $posX += $wcol[4];
            // affichage tarif garantie colonne prix total
            $pdf->setXY($posX, $posY);
            if($afficheprixopt==true){
                $pdf->multiCell($wcol[5], 4, ($garantiePH == 100 ? "" : $template->translate("/devis/inclus", "F")), 0, 'R', 0);
            }
            else{
                $pdf->multiCell($wcol[5], 4, "", 0, 'R', 0);
            }

        }
        // $designation.="- ".$txt."\n";
        $height += $pdf->nblines($wcol[2], " - " . $txt) * 4;
        $posY = $height + $ly;
    }
    
    $pdf->SetFont('Arial','',8);
    // affichage de la fin de la désignation
    if($lignefacture['artid']!=0)
      $arturl = $template->translate("/site/[site_code]/url") . $template->mkurl("article", $libmodele['artid'], $libmodele);
    else
      $arturl = '';

    $lx     = 5 + $wcol[0] + $wcol[1];
    $pdf->setXY($lx, $posY);
    $pdf->multicell($wcol[2], 4, $designation2, 0, '', 0);
    $height += $pdf->nblines($wcol[2], "\n" . $designation2) * 4;
    if($lignefacture['artid']!=0)
      $pdf->link($lx, $ly, $wcol[2], $height, $arturl);
    $typeimg = "JPG";
    $imgpath = "";
    $cookieSession="";
    if(isset($_GET['session'])) $cookieSession = $_GET['session'];
    if ($libmodele['artid'] > 0) {
        $autreimg = $DB_site->query_first("SELECT * FROM articlephoto WHERE artid=" . intval($libmodele['artid']) . " AND visible=1 AND (langue='' OR langue='" . $template->langue . "')");
        if ($autreimg != "")
            $imgfile = $libmodele['artid'] . "_" . $autreimg['articlephotoid'];
        else
            $imgfile = $libmodele['artid'];
        if($libmodele['simu'] == 'oui' && $cookieSession !=""){
            if(!file_exists("simulateur/devis/" . $devisid_img ."_" .$libmodele['artid'].".png")){
                $imgpath = "simulateur/devis/" . $imgfile ."_" .$cookieSession.".png";
                $source = imagecreatefrompng($imgpath);
                //imagecopyresampled($dest, $source, 0, 0, 0, 0, 280, 280, imagesx($source), imagesy($source));
                imagepng($source,$rootpath."simulateur/devis/".$devisid_img."_".$libmodele['artid'].".png");
            }
            $imgpath = "simulateur/devis/" . $devisid_img ."_" .$libmodele['artid'].".png";
            $typeimg = "PNG";
        }
        else{
            $imgpath = "configurations/".$host."/images/produits/br/" . $imgfile . ".jpg";
        }
    } elseif ($lignefacture['prix'] == 0)
        $imgpath = "configurations/".$host."/images/templates/devis_kdo_new.jpg";
    
    if ($imgpath != "" && file_exists($imgpath)) {
        $size  = getimagesize($imgpath);
        $img_w = $wcol[0] - 4;
        $img_h = $size[1] * $img_w / $size[0]; 
        if ($img_h > $height) {
            $img_h = $height - 4;
            $img_w = $size[0] * $img_h / $size[1];
        }
        $pdf->image($imgpath, 5 + ($wcol[0] - $img_w) / 2, $ly + ($height - $img_h) / 2, $img_w, $img_h, $typeimg, $arturl);
    }
    
    // affichage des prix de la cave
    $prix      = $puttc;
    $prixvente = $pventettc;
    $prixtotal = $montant_ttc;
    $prixunit_art = $prix + $diffprix + $ecopart;//colonne prix total article + caract + gar + ecopart * qte
    $prixtotal_art= $montant_ttc_total;
    
    if ($ht) {
        $prix      = $template->price_format(floatval($puht),2,false);
        $prixunit_art = floatval($puht) + $diffprixht + $ecopartht;
        $prixvente = $pvente;
        $prixtotal = $pvente * $qte; 

        $prixtotal_art= (($diffprixht + $ecopartht) * $qte) + $prixtotal;//colonne prix total article + caract + gar + ecopart * qte
    }

    // Calcul de la remise sur le montant total 
    $prixremise = $prixunit_art - ($prixtotal_art/intval($qte));
    $remise_total=0;

    if($prixremise !== 0){
        if(intval($prixunit_art) != 0 )
            $remise_total = 100-round(($prixtotal_art/intval($qte)*100) / $prixunit_art);
    }
    //var_dump($prix,$prixunit_art,$prixtotal_art,$prixtotal);
    $prixunit_art = $template->price_format(floatval($prixunit_art), 2, false);
    $prixtotal_art= $template->price_format(floatval($prixtotal_art), 2, false);

    $prixtotal = $template->price_format($prixtotal+$prix_opt_tt,2,false);
    $prixvente = $template->price_format($prixvente+$prix_opt_t,2,false);
    
    $lx = 5 + $wcol[0];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[1], 4, "$qte", 0, 'C', 0);
    $lx += $wcol[1] + $wcol[2];
    
    $pdf->setXY($lx, $ly);
    // ********** Affichage prix colonne prix unitaire total article + options + garantie + ecopart***********/
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->multiCell($wcol[3], 4, "$prixunit_art$_COOKIE[calque1]", 0, 'R', 0);
    $pdf->setdrawcolor(160, 5, 5);

    // **********FIN Affichage prix colonne prix total article + options + garantie + ecopart***********/
    $lx+=$wcol[3];
    $pdf->setXY($lx,$ly);
    $pdf->SetFont('Arial', 'B', 8);
    if ($remise_total>0){
        $pdf->multiCell($wcol[4], 4, "-$remise_total %", 0, 'C', 0);
    }
    $lx-=$wcol[3];

    //affiche la ligne en dessous en italique que si le prix est différent du prix du dessus (donc, si pas d'options ni de garantie ni d'ecopart) :
    // if($afficheprixopt==true && $prix_opt_ok==true){
	if($afficheprixopt==true || $prix_opt_ok==true){
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->setXY($lx, $ly);
    // ********** Affichage prix colonne prix unitaire ***********/
        $pdf->multiCell($wcol[3], 12, $prix."$_COOKIE[calque1]", 0, 'R', 0);
		/*
        $pdf->setdrawcolor(160, 5, 5);

        if ($remise != 0) {
            $pdf->line($lx + 7, $ly + 6, $lx + $wcol[3] - 3, $ly +6 );
        }
		*/
    }
    $pdf->drawColor("white");

    $lx += $wcol[3];
	/*
    //affiche la ligne en dessous en italique que si le prix est différent du prix du dessus (donc, si pas d'options ni de garantie ni d'ecopart) :
    if($ecopart>0 || ($afficheprixopt==true && $prix_opt_ok==true) || $prix_opt_ok==true){
        $pdf->setXY($lx, $ly);
        if ($remise != 0){
            $pdf->multiCell($wcol[4], 12, "$remise %", 0, 'C', 0);
        }
    }
	*/
    $lx += $wcol[4];

     // ********** Affichage prix colonne prix total article + caract + gar + ecopart * qte ***********/
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[5],4, "$prixtotal_art", 0, 'R', 0);

    // ********** Affichage prix colonne prix total ***********/
    //affiche la ligne en dessous en italique que si le prix est différent du prix du dessus (donc, si pas d'options ni de garantie ni d'ecopart) :
    // if($afficheprixopt==true && $prix_opt_ok==true){
	if($afficheprixopt==true || $prix_opt_ok==true){
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->setXY($lx, $ly);
        $pdf->multiCell($wcol[5], 12, "$prixtotal", 0, 'R', 0);
    }

      
    $pdf->drawColor("black");
    $pdf->line(5, $ly + $height - 0.7, $lx + $wcol[5], $ly + $height - 0.7);
    $pdf->SetFont('Arial', '', 8);

    $lx = 5;
    $pdf->setXY($lx, $ly);
    $pdf->multicell($wcol[0], $height, "", 0, '', 0);
    $lx = $wcol[0] + 5;
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[1], $height, "", 0, 'R', 0);
    $lx += $wcol[1];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[2], $height, "", 0, 'R', 0);
    $lx += $wcol[2];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[3], $height, "", 0, '', 0);
    $lx += $wcol[3];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[4], $height, "", 0, 'R', 0);
    $lx += $wcol[4];
    
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[5], $height, "", 0, 'R', 0);
    
}
$i++;
$tvaremise = array();
// Bons de réduction 
if (in_array("13", $modules)) {
    if ($facture['cadeauid'] != "") {
        $libcadeau   = "";
        $rayons      = array();
        $cmpt_lib_kdo = 0;
        $infoscadeau = $DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
        if ($infoscadeau['code'] != "") {
            $article = $DB_site->query_first("SELECT artcode from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '$facture[cadeauid]'");
            $fin     = $template->translate("/devis/conditions_application") . " : ";
            if ($article[0] != "")
                $fin .= $template->translate("/devis/sur_article") . " " . $article[0];
            else {
                $rayon = $DB_site->query_first("SELECT libelleFR,c.catid from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '$facture[cadeauid]'");
                if ($rayon['catid'] != "") {
                    $rayons[] = $rayon['catid'];
                    $ssrayons = $DB_site->query("SELECT catid FROM categorie WHERE parentid=" . $rayon['catid']);
                    while ($ssrayon = $DB_site->fetch_array($ssrayons)) {
                        $rayons[]  = $ssrayon['catid'];
                        $ss2rayons = $DB_site->query("SELECT catid FROM categorie WHERE parentid=" . $ssrayon['catid']);
                        while ($ss2rayon = $DB_site->fetch_array($ss2rayons))
                            $rayons[] = $ss2rayon['catid'];
                    }
                    $fin .= $template->translate("/devis/sur_rayon") . " " . $rayon[0];
                } else {
                    if($infoscadeau['marqueid'] == 0)
                        $fin .= $template->translate("/devis/sur_tout_site", "F");
                    else{
                        $marquelib = $DB_site->query_first("SELECT libelle FROM marque WHERE marqueid='".$infoscadeau['marqueid']."' ");
                        $fin .= $template->translate("/devis/sur_marque", "F")." ".$marquelib[0];
                    }
                }
            }
            
            $totalreduc    = array();
            $lignesfactures = $DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$devisid'");
            while ($ligne = $DB_site->fetch_array($lignesfactures)) {
                $caract = $DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=" . $ligne['lignefactureid']);
                if ($ligne['artcode'] == $article[0] || in_array($ligne['catid'], $rayons) || ($article[0] == "" && $rayons == array()))
                    $totalreduc[$ligne['tva']] += $ligne['qte'] * ($ligne['prix'] - $caract[0]);
            }
            
            $lignesfactures = $DB_site->query("SELECT * FROM lignefacturediss WHERE artcode<>'' AND factureid = '$devisid'");
            while ($ligne = $DB_site->fetch_array($lignesfactures)) {
                $caract = $DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=" . $ligne['lignefactureid']);
                if ($ligne['artcode'] == $article[0] || in_array($ligne['catid'], $rayons) || ($article[0] == "" && $rayons == array()))
                    $totalreduc[$ligne['tva']] += $ligne['qte'] * ($ligne['prix'] - $caract[0]);
            }
            
            $libcadeau = $template->translate("/devis/bon_reduction", "F") . " " . $infoscadeau['code'];
            if ($infoscadeau['valeurcadeau'] > 0) {
                switch ($infoscadeau['typecadeauid']) {
                    case '0': // remise sur le poids
                    $valeurcadeau = $infoscadeau['valeurcadeau'];
                    if($template->pro) $valeurcadeau = round($valeurcadeau/1.2, 2);
                        $libcadeau .= "\n" . $template->translate("/devis/remise_de", "F") . " $valeurcadeau  ".($template->pro ? "HT":"TTC");
                        $cmpt_lib_kdo+=2;
                        break;
                    case '1': // remise sur le prix
                        $libcadeau .= "\n" . $template->translate("/devis/remise_de", "F") . " $infoscadeau[valeurcadeau] % ";
                        if($infoscadeau['appliquer'] == 'PPC'){
                            $libcadeau .= $template->translate("/devis/remise_ppc", "L");
                        }
                        else{
                            $libcadeau .= $template->translate("/devis/remise_pv", "L");
                        }
                        $cmpt_lib_kdo+=2;
                        break;
                }
            }

            $i++;
            
            if ($infoscadeau['datefin'] != 0)
                $libcadeau .= "\n" . $template->translate("/devis/date_validite", "F") . " : " . date($template->translate("/site/date_format"), $infoscadeau['datefin']);
            $libcadeau .= "\n$fin";
            $cmpt_lib_kdo+=2;
            $totalcadeau += $facture['montantcadeau'];
        }
        
        $code_caviste = $DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
        if ($code_caviste['code'] != "") {
            if ($code_caviste['type'] == 2) {
                $totalcadeau += $facture['montantcadeau'];
                $libcadeau = utf8_decode(sprintf($template->translate("/devis/code_remise_immediate", "F"), $code_caviste['code'], $template->price_format($facture['montantcadeau'], 2, false)));
            }
            
            if ($code_caviste['type'] == 1 && $facture['montantcadeau'] < $code_caviste['mini']) {
                $totalcadeau += $facture['montantcadeau'];
                $libcadeau = utf8_decode(sprintf($template->translate("/devis/code_remise_immediate", "F"), $code_caviste['code'], $template->price_format($facture['montantcadeau'], 2, false)));
            }
            
            $totalreduc    = array();
            $lignefactures = $DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$devisid'");
            while ($ligne = $DB_site->fetch_array($lignefactures)) {
                $caract = $DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=" . $ligne['lignefactureid']);
                $totalreduc[$ligne['tva']] += $ligne['qte'] * ($ligne['prix'] - $caract[0]);
            }
        }
        
        if ($libcadeau != "") {
            foreach ($totalreduc as $tx => $totaltx) {
                $partcadeau[$tx] = $facture['montantcadeau'] * $totaltx / array_sum($totalreduc);
                $tvaremise[$tx]  = $partcadeau[$tx] - $partcadeau[$tx] / (1 + $tx / 100);
                $nbre            = $pdf->nblines($wcol[2], $libcadeau) * 4;

                if ($partcadeau[$tx] > 0) {
                    $cadeauttc = "-" . $template->price_format($partcadeau[$tx], 2, false);
                    $cadeauht  = "-" . $template->price_format($partcadeau[$tx] / (1 + $tx / 100), 2, false);
                } else
                    $cadeauttc = $cadeauht = "";
                
                $prix = $cadeauttc;
                if ($ht)
                    $prix = $cadeauht;
                
                $pdf->SetFont('Arial', '', 8);
                $hautcadeau = $pdf->getY();
                $pdf->setX(5);
                $pdf->Cell($wcol[0], $nbre, "", 0, 0, 'C', 0);
                $img_w = $wcol[0];
                $size  = getimagesize("configurations/".$host."/images/templates/devis_kdo_new.jpg");
                if ($size[1] * $img_w / $size[0] > $nbre)
                    $img_w = $size[0] * $nbre / $size[1] - 1;

                $pdf->image("configurations/".$host."/images/templates/devis_kdo_new.jpg", 3 + ($wcol[0] - 6) / 2, $hautcadeau+$cmpt_lib_kdo, 10);
                $pdf->setX(5 + $wcol[0]);
                $pdf->Cell($wcol[1], $nbre+1, "1", 0, 0, 'C', 0);
                $pdf->setX($wcol[0] + $wcol[1] + $wcol[2] + 5);
                $pdf->Cell($wcol[3], $nbre+1,  $prix, 0, 0, 'R', 0);
                //$pdf->Cell($wcol[3],$nbre,number_format($tx,1,",","")."%" ,1,0,'R',1);
                $pdf->Cell($wcol[4], $nbre+1, "", 0, 0, 'R', 0);
                //$pdf->Cell($wcol[5],$nbre,$prix,1,0,'R',1);
                $pdf->Cell($wcol[5], $nbre+1, $prix, 0, 0, 'R', 0);
                $pdf->setXY(5 + $wcol[0] + $wcol[1], $hautcadeau);
                $pdf->multiCell($wcol[2], 5, "$libcadeau", 0, '', 0);
                
            }
            foreach ($partcadeau as $tx => $value)
                $totalttc[$tx] -= $value;
            
            $ly = $pdf->getY();
            $pdf->drawColor("black");
            $pdf->line(5, $ly - 0.5 + 1, array_sum($wcol) + 5, $ly - 0.5 + 1);
            
        }
    }
}

// Frais de port
$totalttc[$facture['tvaport']] += $facture['prixport'];
$tva[$facture['tvaport']] += $facture['prixport'] - ($facture['prixport'] / (1 + $facture['tvaport'] / 100));
$prixport_ht     = ($facture['prixport'] / (1 + $facture['tvaport'] / 100));
$prixport_origin = $facture['prixport'];

$pdf->drawColor("white");
$pdf->SetFont('Arial', '', 8);

$institu0         = $DB_site->query_first("SELECT libelle" . $template->langue . " FROM institutionnel WHERE institutionnelid=12");
$livrlink[0]      = $template->translate("/site/[site_code]/url") . "/int,12," . url_rewrite($institu0[0]) . ".htm";
$institu1         = $DB_site->query_first("SELECT libelle" . $template->langue . " FROM institutionnel WHERE institutionnelid=39");
$pagepaiement     = $DB_site->query_first("SELECT libelle" . $template->langue . " FROM institutionnel WHERE institutionnelid=10");
$pagecgv          = $DB_site->query_first("SELECT libelle" . $template->langue . " FROM institutionnel WHERE institutionnelid=1");
$pagecgvlink      = $template->translate("/site/[site_code]/url") . "/int,20," . url_rewrite($pagecgv[0]) . ".htm";  
$pagepaiementlink = $template->translate("/site/[site_code]/url") . "/int,10," . url_rewrite($pagepaiement[0]) . ".htm";
$livrlink[1]      = $livrlink[2] = $template->translate("/site/[site_code]/url") . "/int,39," . url_rewrite($institu1[0]) . ".htm";

if($facture['incoterms']==""){
    if(intval($facture['modelivraison']) == 5)
        $livr = $template->translate("/devis/mode_livraison_" . $facture['modelivraison']);
    else
        $livr = sprintf($template->translate("/devis/mode_livraison_".$facture['modelivraison']."_1"), $poidstotal);
}
else
    $livr= "Incoterm ".html_entity_decode($facture['incoterms']). 
        (!empty($facture['incoterms_texteperso']) ? " - ".html_entity_decode($facture['incoterms_texteperso'])." - " : "").html_entity_decode($facture['incoterms_textebase']);

$frais_port      = new frais_port($DB_site, $facture['facturid']);
$prixport_origin = calcport($poidstotal, 0, $facture['modelivraison'], $prixtypeinstall, $poidscaves); //total à 0 pour avoir pas avoir les fdp offerts

$prixport_origin_ht = $prixport_origin / (1 + $facture['tvaport'] / 100);

if ($facture['prixport'] == 0) {
    $livr = $template->translate("/devis/livraison_offerte", "F") . " : " . $livr;
    $totalcadeau += $prixport_origin;
}
    if($pdf->nblines($wcol[2], $livr) > 1){
        $nbre = $pdf->nblines($wcol[2], $livr) * 5;
    }
    else{
        $nbre = $pdf->nblines($wcol[2], $livr) * 10;
    }
    
    $sautligne = $nbre;


$prixorigin = $prixport_origin;
$prix       = $facture['prixport'];
if ($ht) {
    $prixorigin = $prixport_origin_ht;
    $prix       = $prixport_ht;
}

if($facture['modelivraison'] == 5){
    $prixorigin = $prix;
}

$lx = 5;
$ly = $pdf->getY();
if ($nbre + $ly >= 260) {
    footer($pdf);
    $pdf->addpage();
    Head($pdf);
    toptable($pdf);
    $ly = $pdf->getY();
    $lx = 5;
}

$pdf->setXY($lx, $ly);

$trsptimgpath = "configurations/".$host."/images/templates/";
$trsptimg     = array(
        0 => $trsptimgpath . "/eco.jpg",
        1 => $trsptimgpath . "/confort.jpg",
        7 => $trsptimgpath . "/confortmes.jpg",
        4 => $trsptimgpath . "/devis_montage.jpg",
        5 => $trsptimgpath . "/devis_specialise.jpg"
    );

if($facture['incoterms']=="" && $facture['txtlivr'] == ""){
    $size         = getimagesize($trsptimg[$facture['modelivraison']]);
    //$livr_w       = min($size[0] * ($nbre - 2) / $size[1], 16);
    $livr_w = 8;
    $pdf->image($trsptimg[$facture['modelivraison']], $lx + ($wcol[0] - $livr_w) / 2, $ly + ($nbre - $livr_w) / 2 + 0.3, $livr_w);
}else{
    $size         = getimagesize($trsptimg[$facture['modelivraison']]);
    $livr_w = 10;
    $pdf->image($trsptimg[$facture['modelivraison']], $lx + ($wcol[0] - $livr_w) / 2, $ly + ($sautligne - $livr_w) / 2 + 0.3, $livr_w);
}

$pdf->multiCell($wcol[0], $sautligne, "", 1, 'C', 0);
$lx += $wcol[0];
$pdf->setXY($lx, $ly);
$pdf->multiCell($wcol[1], $sautligne, "1", 1, 'C', 0);
$lx += $wcol[1];
$pdf->setXY($lx, $ly+1);
$pdf->MultiCell($wcol[2], 4, $livr, 1, 'L');
$pdf->link($lx, $ly, $wcol[2], $sautligne, $livrlink[$facture['modelivraison']]);
$lx += $wcol[2];
$pdf->setXY($lx, $ly);

if ($facture['incoterms']=="") {
	if($prixorigin<$prix){
		$prixorigin=$prix;
	}
    $pdf->multiCell($wcol[3], $sautligne, $template->price_format($prixorigin, 2, false), 1, 'R', 0);
    $pdf->setdrawcolor(160, 5, 5);
    if ($prixorigin != floatval($prix))
        $pdf->line($lx + 7, $ly - 2 + $sautligne / 2, $lx + $wcol[3] - 3, $ly + 2 + $sautligne / 2);
} else {
    $pdf->multiCell($wcol[3], $sautligne, $template->price_format($prix, 2, false), 1, 'R', 0);
}
$pdf->drawColor("white");

$lx += $wcol[3];
$pdf->setXY($lx, $ly);
if ($facture['incoterms']=="" && $prixport_ht != $prixport_origin_ht && $prixport_origin_ht > 0 ) {
    $pdf->setfont('arial', '', 7);
    $pourcent = round(-100 + (100 * $prixport_ht / $prixport_origin_ht))."%";
    if($pourcent >= 0) $pourcent = "";
    $pdf->multicell($wcol[4], $sautligne, $pourcent, 1, 'C', 0);
    $pdf->setfont('arial', '', 8);
} else
    $pdf->multicell($wcol[4], $sautligne, "", 1, 'C', 0);

$lx += $wcol[4];
$pdf->setXY($lx, $ly);
$pdf->multiCell($wcol[5], $sautligne, $template->price_format($prix, 2, false), 1, 'R', 0); // prix livraison
$pdf->setY($ly + $sautligne);
$pdf->drawColor("black");
$pdf->line(5, $ly + $sautligne, $lx + $wcol[5], $ly + $sautligne);
$pdf->Ln(1);

$pdf->SetFillColor(255, 255, 255);

$lx = 5;
$ly = $pdf->getY();

//INSTALLATION MISE EN SERVICE
/*$txt_installation = $txt_libelle = '';
$tabRemind = array();
$montant_total_ht = 0;
$montant_total_ttt = 0;
$prix_installe =0;
$installe = $DB_site->query_first("SELECT * FROM lignefactureinstallation WHERE factureid=".$facture['facturid']);
if($installe[factureid] > 0){

   $tabTemp = explode('|', $installe['lignefactureinstallation_typearticles']);

   foreach($tabTemp as $type_temporaire){

        if(!in_array($type_temporaire, $tabRemind)){

             array_push($tabRemind, $type_temporaire);

             $type_temporaire = intval($type_temporaire);
             if($type_temporaire > 0){

                  $sql = "SELECT libelle FROM typearticle WHERE typearticleid = ".$type_temporaire;
                  $tabLibelle = $DB_site->query_first($sql);

                  if(!empty($txt_libelle)) $txt_libelle .= ' / ';
                  $txt_libelle .= $tabLibelle['libelle'];

                  $prix_installe += $installe['lignefactureinstallation_differenceprix'];
             }
        }
   }

    $txt_installation = "Installation et mise en service de votre << ".$txt_libelle." >>";
    $livr_w = 8;
    $pdf->image($trsptimg[$facture['modelivraison']], $lx + ($wcol[0] - $livr_w) / 2, $ly + ($nbre - $livr_w) / 2, $livr_w);
    $nbre = $pdf->nblines($wcol[2], $txt_installation) * 10;
    $sautligne = $nbre;
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[0], $sautligne, "", 0, 'C', 0);
    $lx += $wcol[0];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[1], $sautligne, "1", 0, 'C', 0);
    $lx += $wcol[1];
    $pdf->setXY($lx, $ly);
    $pdf->MultiCell($wcol[2], $nbre, "$txt_installation", 0, 'L');
    $lx += $wcol[2];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[3], $sautligne, $template->price_format($prix_installe, 2, false), 0, 'R', 0);
    $lx += $wcol[3];
    $pdf->setXY($lx, $ly);

    $pdf->multicell($wcol[4], $sautligne, "", 0, 'C', 0);
    $lx += $wcol[4];
    $pdf->setXY($lx, $ly);
    $pdf->multiCell($wcol[5], $sautligne, $template->price_format($prix_installe, 2, false), 0, 'R', 0); //prix livraison

    $pdf->setY($ly + $sautligne);
    $pdf->drawColor("black");
    $pdf->line(5, $ly + $sautligne, $lx + $wcol[5], $ly + $sautligne);
    $pdf->Ln(1);
}*/



$ly = $pdf->getY();
$top = $pdf->getY();
// calcul si faut ajouter une page
if ($top >= 270) {
    footer($pdf);
    $pdf->addpage();
    Head($pdf);
    $pdf->ln(10);
    $top = $pdf->getY();
    $ly = $pdf->getY();
    $pdf->setfont('arial', '', 8);
}
$pdf->setX(5);
//$pdf->multicell($wcol[0] + $wcol[1] + $wcol[2], 4, $template->translate("/devis/pour_info"));
$pdf->setX(5);
$bottominfo = 0;
if ($facture['heurefacture'] > 0)
    $pdf->multicell($wcol[0] + $wcol[1] + $wcol[2], 3, $template->translate("/devis/validite") . " : " . date($template->translate("/site/date_format"), $facture['heurefacture'] + 10 * 86400)); //. " " . $template->translate("/devis/sous_reserve"));

$ly = $pdf->getY();

/* Afficher moyen de paiement*/
$txtpaiement = "";
if (isset($_GET['proforma'])) {
    if ($facture['provenance'] == "mp")
        $moyenpaiement[$libelle] = $marketplaces[$facture['provenanceid']];
    

}
if($moyenpaiement['moyenid']>0){
    $txtpaiement = $moyenpaiement[$libelle];
    $nbmoyens    = 1;
    if ($facture['moyenid'] == 99) {
        $req         = $DB_site->query("SELECT r.reglement_id, r.reglement_date, rd.reglement_delais_libelle, r.reglement_montant, m.libelle" . $template->langue . " AS libelle, r.reglement_moyen FROM reglement r INNER JOIN moyenpaiement m ON (m.moyenid = r.reglement_moyen) INNER JOIN reglement_delais rd ON (rd.reglement_delais_id = r.reglement_delais_id) WHERE r.facturid = " . $facture['facturid'] . " ORDER BY r.reglement_id");
        $nbmoyens    = $DB_site->num_rows($req);
        $txtpaiement = "";
        while ($reglement = $DB_site->fetch_array($req)) {
            $txtpaiement .= sprintf($template->translate("/devis/paiement_par"), $template->price_format($reglement['reglement_montant'], 2, false), utf8_decode($reglement['libelle']), date_create($reglement['reglement_date'])->format("d/m/Y") . "\n");
        }
    }
}

if (isset($_GET['paiements'])) {
    $paiements = json_decode($_GET['paiements']);
    foreach ($paiements as $paiement) {
        $moyen = $DB_site->query_first("SELECT * FROM moyenpaiement WHERE moyenid=" . $paiement->moyen);
        $txtpaiement .= sprintf($template->translate("/devis/paiement_par"), $template->price_format($paiement->value, 2, false), utf8_decode($moyen['libelle' . $template->langue]), $paiement->date . "\n");
    }
}

if ($txtpaiement != "") {
    $ly+=2;
    $pdf->setXY(5,$ly);
    $pdf->multicell($wcol[0] + $wcol[1] + $wcol[2], 4, $template->translate("/devis/moyen_paiement") . " : ");
    $pdf->setXY(6 + $pdf->GetStringWidth($template->translate("/devis/moyen_paiement") . " : "), $ly);
    $pdf->multiCell($wcol[0] + $wcol[1] + $wcol[2], 4, $txtpaiement);
    $ly=$pdf->getY();
}
/* Fin affichage */

$pdf->setY($top + 10);
$totalx = 5 + $wcol[0] + $wcol[1] + $wcol[2];

$pdf->setfillcolor(198, 199, 200);
foreach ($totalttc as $tx => $ttc) {
    
    $tht = $ttc / (1 + $tx / 100);
    $pdf->setX($totalx);
    $pdf->Cell($wcol[3] + $wcol[4], 5, $template->translate("/devis/total_ht"), 0, 0, 'L', 1);
    $pdf->setfont('arial', 'B', 10);
    $pdf->Cell($wcol[5], 5, $template->price_format($tht, 2, false), 0, 0, 'R', 1);
    $pdf->Ln(5);
    $pdf->setfont('arial', '', 8);
}
$pdf->setfillcolor(255);

foreach ($tva as $tx => $totaltva) {
    $pdf->setX($totalx);
    $pdf->Cell($wcol[3] + $wcol[4], 5, $template->translate("/devis/tva") . " " . $tx . "%", 0, 0, 'L', 1);
    $pdf->Cell($wcol[5], 5, $template->price_format($totaltva - $tvaremise[$tx], 2, false), 0, 0, 'R', 1);
    $pdf->Ln(5);
}

$pdf->setX($totalx);
$pdf->fillColor("black");
$pdf->textColor("white");
$pdf->drawColor("white");
$pdf->Cell($wcol[3] + $wcol[4], 5, $template->translate("/devis/montant_total_ttc"), 0, 0, 'L', 1);

$pdf->setfont('arial', 'B', 10);

$total = array_sum($totalttc);
$pdf->Cell($wcol[5], 5, $template->price_format($total, 2, false), 0, 0, 'R', 1);

$pdf->fillColor("white");
$pdf->textColor("black");
$pdf->ln(10);

if($ly>$pdf->getY()) $pdf->setY($ly);

$height          = 0;
$autremode_title = true;

$ly = $pdf->getY();
$top = $pdf->getY();
// calcul si faut ajouter une page
if ($top >= 250) {
    footer($pdf);
    $pdf->addpage();
    Head($pdf);
    $pdf->ln(10);
    $top = $pdf->getY();
    $ly = $pdf->getY();
    $pdf->setfont('arial', '', 8);
}

//********* Paiement immediat  ************** //
$lx = 5;
$ly = $pdf->getY();
$pdf->setXY($lx, $ly);
$total_wcol = array_sum($wcol);

//colonne / VOUS SOUHAITEZ PASSER COMMANDE 
$pdf->setfont('arial','B',12);
$pdf->fillColor($autocolor);
$pdf->textColor("white");
$pdf->Cell($total_wcol,6,strtoupper($template->translate("/devis/valider_devis","U")),0,0,"C",1);

//ligne du dessous, confirmation
$lx = 5;
$ly = $pdf->getY() + 6;
$pdf->setXY($lx, $ly);
$pdf->setfont('arial','',9);
$pdf->fillColor("white");
$pdf->textColor("black");
$pdf->Cell($total_wcol,5,$template->translate("/devis/paiement/confirm_paiement","F"),0,0,"C",1);

// ligne des trois bloc suivant on dessine 3 rectangle de 55,74,55 px
$lx = 9;
$ly = $pdf->getY() + 7;
$pdf->SetXY($lx,$ly);
$pdf->drawColor($autocolor); //couleur border
$pdf->fillColor($autocolor);
$pdf->Rect($lx,$ly,55,20,'D');//rect 1 : encadré
$pdf->SetFillColor(230,226,224);
$pdf->RoundedRect($lx+5,$ly+3,45,7,1,'1234','F'); //rect 1 : cadre gris
$pdf->textColor("black");
$pdf->SetXY($lx+15,$ly+4);
$pdf->SetFont('arial','B',9);
$pdf->Cell(30,5,strtoupper($template->translate("/devis/paiement/online")),0,0,"C",1);//rect 1 : texte en ligne
$pdf->Image($rootpath."configurations/".$host."/images/templates/mcav_iconOnline_2020.png",$lx+15,$ly+4,5,5,'PNG');//image online
$pdf->fillColor($autocolor);
$pdf->RoundedRect($lx+9,$ly+12,38,7,1,'1234','F'); //rect 1 : btn panier
$url = rewrite_url("",$template->langue,"/back_devis.php?back_payment=".$facture['facturid'],$template,$_SERVER['HTTP_HOST']);
$pdf->link($lx+9,$ly+12,35,7,$url);

$pdf->fillColor($autocolor);
$pdf->textColor("white");
$pdf->SetFont('arial','B',8);
$pdf->SetXY($lx+13,$ly+14);
$pdf->Cell(30,4,strtoupper($template->translate("/devis/paiement/panier","U")),0,0,"C",1); //rect 1 : text btn panier

$lx += 55;
$pdf->SetXY($lx,$ly);
$pdf->drawColor($autocolor);
$pdf->Rect($lx+5,$pdf->GetY(),74,20,'D'); //rect 2 : encadré
$pdf->SetFillColor(230,226,224);
$pdf->RoundedRect($lx+17,$ly+3,50,7,1,'1234','F'); //rect 2 : cadre gris

$pdf->textColor("black");
$pdf->SetXY($lx+30,$ly+4);
$pdf->SetFontSize(9);
$pdf->Cell(30,5,strtoupper($template->translate("/devis/paiement/byphone")),0,0,"C",1);//rect 2 : texte par téléphone
$pdf->Image($rootpath."configurations/".$host."/images/templates/mcav_iconPhone_2020.png",$lx+24,$ly+4,5,5,'PNG');//image byphone

$pdf->textColor("white");
$pdf->fillColor($autocolor);
$pdf->RoundedRect($lx+10,$ly+12,30,7,1,'1234','F'); //rect2 : btn tel 
$pdf->RoundedRect($lx+44,$ly+12,30,7,1,'1234','F'); //rect2 : btn rappel
$url = rewrite_url("",$template->langue,"/back_devis.php?back_payment=".$facture['facturid']."&back_call=1",$template,$_SERVER['HTTP_HOST']);
$pdf->link($lx+44,$ly+12,30,7,$url);

$pdf->SetXY($lx+11,$ly+14);
$pdf->SetFontSize(8);
$pdf->Cell(28,4,strtoupper($template->translate("/devis/paiement/numero","U")),0,0,"C",1); //rect 2 : text btn tel
$pdf->SetX($lx+45);
$pdf->Cell(28,4,strtoupper($template->translate("/devis/paiement/rappel","U")),0,0,"C",1); //rect 2 : text btn tel

$lx += 74 + 5;
$pdf->SetXY($lx,$ly);
$pdf->drawColor($autocolor);
$pdf->Rect($lx+5,$pdf->GetY(),55,20,'D'); //rect 3 : encadré
$pdf->SetFillColor(230,226,224);
$pdf->RoundedRect($lx+10,$ly+2,45,8,1,'1234','F'); //rect 1 : cadre gris

$pdf->textColor("black");
$pdf->SetFontSize(9);
$pdf->SetXY($lx+20,$ly+4);
$pdf->Cell(30,5,strtoupper($template->translate("/devis/paiement/bymail")),0,0,"C",1);//rect 3 : texte par email
$pdf->Image($rootpath."configurations/".$host."/images/templates/mcav_iconMail_2020.png",$lx+18,$ly+4,6,4,'PNG');//image bymail

$pdf->fillColor($autocolor);
$pdf->RoundedRect($lx+15,$ly+12,35,7,1,'1234','F'); //rect 3 : btn mail
$pdf->SetXY($lx+16,$ly+14);
$pdf->textColor("white");
$pdf->SetFontSize(8);
$pdf->Cell(33,4,strtoupper($template->translate("/devis/paiement/mail","U")),0,0,"C",1); //rect 3 : text btn mail
$url = rewrite_url("",$template->langue,"/back_devis.php?back_payment=".$facture['facturid']."&back_mail=1",$template,$_SERVER['HTTP_HOST']);
$pdf->link($lx+16,$ly+14,33,4,$url);

footer($pdf);

// ************************* PAGE DEVIS LIVRAISON **************************************//
//nouvelle page 
$pdf->addpage();
Head($pdf);

include_once('devis_livraison.php');
//*************************FIN PAGE DEVIS LIVRAISON ************************************//


// ************************* PAGE DEVIS GARANTIE **************************************//
$devis_garantie_file = "devis_garantie.php";
if($template->pro) $devis_garantie_file = "devis_garantie_pro.php";
include_once($devis_garantie_file);
//*************************FIN PAGE DEVIS LIVRAISON ************************************//

//Image moyen de paiement
if(!$template->pro){
  //ligne bandeau "moyens de paiements" 
  $pdf->setfont('arial','B',12);
  $pdf->fillColor($autocolor);
  $pdf->textColor("white");

  $ly+=5;
  $pdf->setXY(5,$ly);
  $pdf->Cell($total_wcol,6,$template->translate("/admin/menu/moyens_paiement","F"),0,0,"C",1);
  $ly+=8;
  $lx = 15;
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/1.jpg",$lx,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/2.jpg",$lx+20,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/3.jpg",$lx+40,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/4.jpg",$lx+60,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/5.jpg",$lx+80,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/6.jpg",$lx+100,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/8.jpg",$lx+120,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/9.jpg",$lx+140,$ly,18,9,'JPG');
  $pdf->Image($rootpath."configurations/".$host."/images/templates/base/".$template->langue."/mp/10.jpg",$lx+160,$ly,18,9,'JPG');
  $ly+=5 ;
}

//options defaut select
$pdf->SetAutoPageBreak(false);
$img = $rootpath."configurations/".$host."/images/templates/validation.png";
$pdf->Image($img,6,290,4,4,'PNG');
$pdf->SetXY(10,290);
$pdf->setfont('arial','',8);;
$pdf->textColor("black");
$pdf->fillColor("white");
$pdf->Cell(50,4,$template->translate("/devis/options_defaut"),0,0,'L',1);

footer($pdf);

$pdf->SetAutoPageBreak(true,5);

//*********************** Bloc Bon pour accord ******************************//
if($user['raisonsociale'] != ''){

    $py += 32;
    $top = $pdf->getY();

    // on ajoute une page
    //footer($pdf);
    $pdf->addpage();
    Head($pdf);

    $pdf->Ln(15);
    $ly = $pdf->getY();
    $lx = 5 + $wcol[0] + $wcol[1];
    $py = $ly ;
    $size = 0;

    $pdf->setXY(0, 20);
    $pdf->setfont('arial', '', 8);
    $pdf->textColor("black");
    $pdf->Cell(0, 5, $template->translate("/devis/page", "F") . " " . $pdf->PageNo(), 0, 0, 'R');
    foreach($wcol as $col) $size += $col;
    $py-=7;
    //barre titre bon pour accord
    $pdf->fillColor($autocolor);
    $pdf->drawColor("black");
    $pdf->textColor("white");
    $pdf->setfont('arial', 'B', 10);
    $pdf->setXY(5, $py); 
    $pdf->cell($size, 8, $template->translate("/devis/bon_accord").$facture['facturid'], 1, 0, 'C', 1);
    $py+=12;

    // text bon pour accord
    $pdf->setfont('Arial', '', 11);
    $pdf->textColor("black");
    $pdf->setXY(5, $py); 
    $retour="\n";
    if($template->langue=='EN') $retour = "";
    $ly = $pdf->nblines($size,$template->translate("/devis/bon_accord_txt_1")."\n".$template->translate("/devis/bon_accord_txt_2"));
    $pdf->MultiCell($size, 4,  $template->translate("/devis/bon_accord_txt_1")."\n".$retour.$template->translate("/devis/bon_accord_txt_2"),0, 'J', false);
    $py += $ly*4+10;
    if($template->langue=='EN') $py -=8;
    
    $pdf->setfont('Arial', 'B', 12);
    $pdf->textColor("black");
    $pdf->setXY(5,$py);
    //$pdf->drawColor("bleu");
    $pdf->cell(200,6,sprintf($template->translate("/devis/montant_total"),$template->price_format($tht, 2, false),$template->price_format($total, 2, false)),1,0,'C');
    $py+=14;

    //choix bon pour accord
    $pdf->setfont('Arial', 'B', 11);
    $pdf->setXY(4, $py);
    $pdf->Cell(6, 4, "A. ",0, 'J', false);
    $pdf->textColor("black");
    $pdf->setX(12);
    $pdf->Cell(80, 4, $template->translate("/devis/bon_accord_chx1").":",0, 'J', false);
    $pdf->setXY(101,$py-3);
    $pdf->fillColor("light gray");
    $pdf->Cell(0,10,'',0,0,'L',true);

    $py+=12;

    $pdf->setfont('Arial', 'B', 11);
    $pdf->setXY(4, $py);
    $pdf->Cell(6, 4, "B.",0, 'J', false);
    $pdf->textColor("black");
    $pdf->setX(12);
    $pdf->Cell(80, 4, $template->translate("/devis/bon_accord_chx2").":",0, 'J', false);
    $py+=10;
    $pdf->setXY(10,$py);
    $pdf->fillColor('white');
    $pdf->setdrawcolor(0);
    $pdf->setfont('Arial', '', 11);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher mode livraison
    $pdf->setXY($pdf->getX()+2,$py);
    $pdf->Cell(8,4,$template->translate("/devis/bon_accord_chx2_1"), 0,'J', false); //ECO
    $pdf->setXY($pdf->getX()+7,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher mode livraison
    $pdf->setXY($pdf->getX() + 1,$py);
    $pdf->Cell(20,4,$template->translate("/devis/bon_accord_chx2_2"), 0,'J', false); //CONFORT
    $pdf->setXY($pdf->getX()+7 ,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher mode livraison
    $pdf->setXY($pdf->getX() + 1,$py + 0.5);
    $pdf->Cell(20,4,$template->translate("/devis/bon_accord_chx2_3"), 0,'J', false); //CONFORT MEL
    $pdf->setXY($pdf->getX() + 45 ,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher mode livraison
    $pdf->setXY($pdf->getX() + 1,$py + 0.5);
    $pdf->Cell(20,4,$template->translate("/devis/bon_accord_chx2_4"), 0,'J', false); //SPECIALISE

    $py += 6;
    $pdf->setXY(8,$py+2);
    $pdf->setfont('Arial', '', 10);
    $pdf->Cell(52,7,$template->translate("/devis/txt_info_livr_1"), 0,0,'L',false); //TXT info livraison
    $pdf->Ln(5);
    $pdf->setX(8);
    $pdf->Cell(52,7,$template->translate("/devis/txt_info_livr_2"), 0,0,'L',false); //TXT info livraison
    $pdf->Ln(5);
    $pdf->setX(8);
    $pdf->setfont('Arial','',9);
    $pdf->Cell(52,7,$template->translate("/devis/txt_info_livr_sub"), 0,0,'L',false); //TXT info livraison sub
    $pdf->setXY(62,$py);
    $pdf->fillColor("light gray");
    $pdf->Cell(0,20,'',0,0,'L',true);


    $py+=30;

    $pdf->setfont('Arial', 'B', 11);
    $pdf->setXY(4, $py);
    $pdf->Cell(0, 4, "C.",0, 'J', false);
    $pdf->textColor("black");
    $pdf->setX(12);
    $pdf->Cell(80, 4, $template->translate("/devis/bon_accord_chx3").":",0, 'J', false);
    $py+=8;
    $pdf->setXY(10,$py);
    $pdf->fillColor('white');
    $pdf->setdrawcolor(0);
    $pdf->setfont('Arial', '', 11);


    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher mode paiement
    $pdf->setXY($pdf->getX()+1,$py + 0.5);
    $pdf->Cell(30,4,$template->translate("/devis/bon_accord_chx3_1"), 0,'J', false); //CB
    
    $pdf->setXY($pdf->getX()+6,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher mode paiement
    $pdf->setXY($pdf->getX()+1,$py + 0.5);
    $pdf->Cell(30,4,$template->translate("/devis/bon_accord_chx3_6"), 0,'J', false); //AMEX
    
    $pdf->setXY($pdf->getX()+12,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher virement
    $pdf->setXY($pdf->getX()+1,$py + 0.5);
    $pdf->Cell(15,4,$template->translate("/devis/bon_accord_chx3_2"), 0,'J', false); //virement
    
    $pdf->setXY($pdf->getX()+11,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher cheque
    $pdf->setXY($pdf->getX()+1,$py + 0.5);
    $pdf->Cell(20,4,$template->translate("/devis/bon_accord_chx3_4"), 0,'J', false); //paypal
    
    
    //$pdf->setXY($pdf->getX()+9,$py);
    //$pdf->Cell(4,4,'',1,0,'L',true); //case a cocher paypal
    //$pdf->setXY($pdf->getX()+1,$py + 0.5);
    //$pdf->Cell(15,4,$template->translate("/devis/bon_accord_chx3_3"), 0,'J', false); //cheque
    
    /*
    $py+=10;
    $pdf->setXY(10,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher credit documentaire
    $pdf->setXY($pdf->getX()+1,$py + 0.5);
    $pdf->Cell(30,4,$template->translate("/devis/bon_accord_chx3_7"), 0,'J', false); 

    $pdf->setXY($pdf->getX()+12,$py);
    $pdf->Cell(4,4,'',1,0,'L',true); //case a cocher location
    $pdf->setXY($pdf->getX()+1,$py + 0.5);
    $pdf->Cell(30,4,$template->translate("/devis/bon_accord_chx3_5"), 0,'J', false); //location

    $pdf->setXY($pdf->getX()+15,$py+1);
    $pdf->Cell(30,4,$template->translate("/devis/location_nbmois"), 0,'L', false); //location nb mois
    $pdf->fillColor("light gray");
    $pdf->setXY($pdf->getX()+15,$py-2);
    $pdf->Cell(12,7,'',0,0,'L',true);
    */
               
    /*
    if($user['paysid'] == 57 || $user['paysid'] == 181){
        //locam tableau
        $py+=7;
        $loc = location($tht);
        if ($template->pro && $loc != false) {
            $pdf->setXY(8,$py);
            $pdf->setfont('Arial','',10);
            $pdf->Cell(30,4,$template->translate("/devis/locam_1"), 0,'J', false); //texte locam
            $pdf->setfont('Arial','',9);
            $marge=0;
            if($template->langue=='EN') $marge = 8;
            $pdf->setXY(11-$marge,$py+7);
            $pdf->Cell(30,4,"- ".$template->translate("/devis/locam_2"), 0,'J', false); //texte locam
            $pdf->setXY(11-$marge,$py+11);
            $pdf->Cell(30,4,"- ".$template->translate("/devis/locam_3"), 0,'J', false); //texte locam
            $pdf->setXY(11-$marge,$py+15);
            $pdf->Cell(30,4,"- ".$template->translate("/devis/locam_4"), 0,'J', false); //texte locam
            $pdf->setXY(11-$marge,$py+19);
            $pdf->Cell(30,4,"- ".$template->translate("/devis/locam_5"), 0,'J', false); //texte locam            
            
            $pdf->setfont('arial', 'B', 8);
            $pdf->setXY(5, $py);
            
            $pdf->textColor("black");
            $pdf->drawColor("black");
            $pdf->fillColor("light gray");
            $py += 0;
            $marge=0;
            if($template->langue=='EN') $marge = 5;
            $pdf->setfont('arial', '', 8);
            $pdf->setXY(92+$marge, $py + 5);
            $pdf->multiCell(28, 5, $template->translate("/devis/total_ht"), 1, 'L', 1);
            $pdf->setXY(120+$marge, $py + 5);
            $pdf->multicell(80, 5, $template->translate("/devis/nombre_mois"), 1, 'C');
            $pdf->setXY(92+$marge, $py + 10);
            $pdf->multiCell(28, 5, $template->price_format($tht, 2, false), 1, 'R');
            $pdf->setXY(92+$marge, $py + 15);
            $pdf->multiCell(28, 5, $template->translate("/devis/loyer_mensuel"), 1, 'L');
            $pdf->setXY(92+$marge, $py + 20);
            $pdf->multiCell(28, 5, $template->translate("/devis/premier_loyer"), 1, 'L');
            $x = 120+$marge;

            $tabReport1loyer = array(0 => $template->translate("/devis/premier_loyer_0j"), 1 => $template->translate("/devis/premier_loyer_0j"), 2 => $template->translate("/devis/premier_loyer_30j"), 3 => $template->translate("/devis/premier_loyer_30j"), 4 => $template->translate("/devis/premier_loyer_30j"));
            $cptReport = 0;

            foreach ($loc as $mens => $prix) {
                $pdf->setXY($x, $py + 10);
                $pdf->multicell(16, 5, $mens, 1, 'C');
                $pdf->setXY($x, $py + 15);
                $pdf->multicell(16, 5, $template->price_format($prix, 2, false), 1, 'R', 1);
                $pdf->setXY($x, $py + 20);
                //$pdf->multicell(20, 4, $template->price_format($prix * $mens, 2, false), 1, 'R');
                $pdf->multicell(16, 5, $tabReport1loyer[$cptReport], 1, 'R');
                $x += 16;
                $cptReport ++;
            }
            
        }
        $py += 19;
    }
    */
    $py+= 20;
    //D TVA
    $pdf->setfont('Arial','B',11);
    $pdf->setXY(4, $py);
    $pdf->Cell(0, 4, "D.",0, 'J', false);
    $pdf->textColor("black");
    $pdf->setX(12);
    $pdf->Cell(30,4,$template->translate("/devis/num_tva"), 0,'J', false); //texte TVA
    $pdf->fillColor("light gray");
    $pdf->setXY(112,$py-3);
    $pdf->Cell(89,10,'',0,0,'L',true);

    $py += 20;

    //E bon pour accord
    $pdf->setfont('Arial','B',11);
    $pdf->setXY(4, $py);
    $pdf->Cell(0, 4, "E.",0, 'J', false);
    $pdf->textColor("black");
    $pdf->setX(12);
    $pdf->Cell(30,4,$template->translate("/devis/bon_accord_E"), 0,'J', false); //texte E    

    //$py = $pdf->getY()+5;
    $py-=5;
    $pdf->setXY(10, $py+10);
    $pdf->fillColor($autocolor);
    $pdf->drawColor("white");
    $pdf->textColor("white");
    $pdf->setfont('arial', 'B', 11);
    $pdf->setdrawcolor(0);
    $pdf->Cell(92,8,$template->translate("/devis/bon_accord_cadre1_1"),1,0,'C',true); //cadre 1_1 nom, prenom et fonction
    $pdf->setX($pdf->getX()+5);
    $pdf->Cell(92,8,$template->translate("/devis/bon_accord_cadre1_2"),1,0,'C',true); //cadre 1_2 signature
    $pdf->setdrawcolor(0);
    $pdf->fillColor("light gray");
    $pdf->textColor("black");
    $pdf->setXY(10, $py +18 );
    $pdf->Cell(92,35,'',1,0,'L',true);
    $pdf->setfont('arial', '', 11);
    $pdf->setXY(12, $py +20);
    $pdf->Cell(6,5,$template->translate("/devis/bon_accord_date"), 0,0,'L', false); //date
    $pdf->setXY(12, $py +30);
    $pdf->Cell(10,5,$template->translate("/devis/bon_accord_nom"), 0,0,'L', false); //nom prenom
    $pdf->setXY(12, $py +40);
    $pdf->Cell(6,5,$template->translate("/devis/bon_accord_fonction"), 0,0,'L', false); //fonction

    $py += 23;
    $pdf->setXY(107,$py-5);
    $pdf->Cell(92,68,'',1,0,'L',true);
    $pdf->setXY(10, $py+30);
    $pdf->fillColor($autocolor);
    $pdf->drawColor("white");
    $pdf->textColor("white");
    $pdf->setdrawcolor(0);
    $pdf->Cell(92,8,$template->translate("/devis/bon_accord_cadre1_3"),1,0,'C',true); //cadre 1_3
    $pdf->setdrawcolor(0);
    $pdf->fillColor("light gray");
    $pdf->textColor("black");
    $pdf->setXY(10, $py+38);
    $pdf->Cell(92,25,'',1,0,'L',true);

    $py = $pdf->getY();
    $pdf->SetAutoPageBreak(false);
    $py+=25;
    $pdf->setXY(10, $py); //phrase confirmation
    $pdf->setfont('Arial','',9);
    $pdf->Cell(92,8,$template->translate("/devis/bon_accord_note1"),0,0,'L',false);
    $pdf->setXY(10, $py+4);
    //$pdf->Cell(92,8,$template->translate("/devis/bon_accord_note2"),0,0,'L',false);
    $pdf->setXY(10, $py+9);
    $pdf->Link(100,$py+10,32,5,"https://www.mon-espace-cuisson.com");
    $pdf->Cell(92,8,$template->translate("/devis/acceptation_pro1"),0,0,'L',false);
    $pdf->setXY(10, $py+13);
    $pdf->Cell(92,8,$template->translate("/devis/acceptation_pro2"),0,0,'L',false);
    $pdf->setXY(10, $py+17);
    $pdf->Cell(92,8,$template->translate("/devis/acceptation_pro3"),0,0,'L',false);

    $pdf->SetAutoPageBreak(true);
    

   //FIN BON POUR ACCORD
}


$prixmax       = 0;
$fichevin      = false;

$lignefactures = $DB_site->query("SELECT * FROM lignefacture INNER JOIN article a USING(artid) WHERE factureid = '$devisid'");

//DEBUT GESTION DE LA DISSOCIATION pour les options  ********
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

$sql="SELECT * FROM lignefacture as lf INNER JOIN article a USING(artid) WHERE";
$sql.=" lignefactureid IN (".implode(",",$lesLignes).")";
$sql.=" UNION";
$sql.=" SELECT * FROM lignefacturediss INNER JOIN article a USING(artid) WHERE";
$sql.=" lignefactureid IN (".implode(",",$lesLignes).")";
//$sql.=" ORDER BY a.artcode";
$lignefactures=$DB_site->query($sql);

// FIN DE GESTION DE LA DISSOCIATION pour les options  *******


//$lignefactures = $DB_site->query("SELECT a.* FROM lignefacture INNER JOIN article a USING(artid) WHERE factureid = '$devisid'");
while ($fiche = $DB_site->fetch_array($lignefactures)) {
    $ligne = new lignefacture($DB_site,intval($fiche['lignefactureid']));
    ficheproduit($pdf, $fiche,$fiche['lignefactureid']);
    $finition = $DB_site->query_first("SELECT finitionid FROM articlerecherche_finition WHERE artid=" . $fiche['artid']);

    if ($fiche['prix'] > $prixmax && $finition[0] != null) {
        $prixmax  = $fiche['prix'];
        $fichevin = $fiche;
    }
}


// image fin
if ($user[raisonsociale] == "") {
    
    $pdf->addpage();
    $pdf->image($rootpath . "configurations/".$template->host_conf->path."/images/templates/base/" . $template->langue . "/pub_devis" . ($template->pro ? "_pro" : "") . ".jpg", 0, 0, 210);
    footer($pdf);
}


if (isset($_GET['dl'])) {
    //header("Content-Type: application/force-download");
    header('Content-type: application/pdf');
    header("Content-Disposition: attachment; filename=\"" . $template->translate("/devis/devis", "F") . " " . $template->translate("/site/name") . " $devisid.pdf\"");
    header("Content-Transfer-Encoding: text/plain\n");
    header("Pragma: no-cache");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
    print $pdf->output('', 'S');
} else
    $pdf->output();


function Head($pdf)
{
    global $template, $host, $rootpath, $langue, $cde, $nomboutique, $facture, $autocolor;
    include_once $rootpath . "configurations/".$host."/config.php";

    $pdf->SetFont('Arial', 'B', 25);
    $pdf->Cell(100);
    
    $pdf->image($rootpath . "configurations/".$host."/images/templates/" . $template->site_code() . "/" . $template->langue . "/contact_devis2016" . ($template->pro ? "_pro" : "") . ".jpg", 161, 4, 46, 0, "jpg", $template->translate("/site/[site_code]/url"));
    
    
    $pdf->Cell(100);
    $pdf->image($rootpath . "configurations/".$host."/images/templates/" . $template->site_code() . "/" . $template->langue . "/logo" . ($template->pro ? "_pro" : "") . ".jpg", 5, 5, 84, 0, "JPG", $template->translate("/site/[site_code]/url"));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->setTextColor(255, 0, 0);
    $pdf->setXY(158, 16);
    //      $pdf->cell(10,5,">>");
    $pdf->setTextColor(128, 128, 128);
    $pdf->setXY(162, 16);
    //      $pdf->cell(60,5,"1er site expert en caves à vin");
    $pdf->Ln(20);
    $pdf->settextcolor(0, 0, 0);
    
    $pdf->setXY(80, 7);
    $pdf->setfont('arial', 'B', 18);
    $pdf->textColor($autocolor);
    $title = $template->translate("/devis/devis_n", "F");
    if (isset($_GET['proforma']))
        $title = $template->translate("/devis/facture_proforma_n", "F");
    $pdf->multicell(80, 8, $title . $facture['facturid'], 0, 'C');
    $pdf->ln(2);
}

function toptable($pdf)
{
    global $template;
    global $cde;
    global $wcol;
    global $facture;
    global $multilangue;
    global $ht;
    global $autocolor;
    
    if ($ht)
        $httxt = $facture['devise'] . " " . $template->translate("/devis/en_ht");
    else
        $httxt = $facture['devise'] . " " . $template->translate("/devis/en_ttc");
    
    $pdf->ln(10);
    $pdf->setfont('arial', '', 8);
    $pdf->textColor("black");
    $dateafftmp = explode("-", $facture['datefacture']);
    $dateaff    = "$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
    $pdf->Cell(172, 5, $template->translate("/devis/date", "F") . " : " . $dateaff, 0, 0, 'L');
    
    $pdf->Cell(20, 5, $template->translate("/devis/page", "F") . " " . $pdf->PageNo(), 0, 0, 'R');
    
    $pdf->Ln(5);
    
    $hauttableau = $pdf->getY();
    $pdf->fillColor($autocolor);
    $pdf->drawColor("white");
    $pdf->textColor("white");
    $pdf->setfont('arial', 'B', 8);
    
    $lx = 5;
    $ly = $pdf->getY();
    $pdf->setXY($lx, $ly);
    $pdf->cell($wcol[0], 8, "", 1, 0, "C", 1);
    $lx += $wcol[0];
    $pdf->setXY($lx, $ly);
    $pdf->cell($wcol[1], 8, $template->translate("/devis/qte", "F"), 1, 0, "C", 1);
    $lx += $wcol[1];
    $pdf->cell($wcol[2], 8, $template->translate("/devis/designation", "F"), 1, 0, 'C', 1);
    $lx += $wcol[2];
    $pdf->multicell($wcol[3], 4, $template->translate("/devis/prix_tarif", "F") . "\n ", 1, "C", 1);
    $lx += $wcol[3];
    
    $pdf->setXY($lx, $ly);
    $pdf->cell($wcol[4], 8, "", 1, 0, "C", 1);
    $lx += $wcol[4];
    
    $pdf->setXY($lx, $ly);
    $pdf->multicell($wcol[5], 4, $template->translate("/devis/prix_total", "F") . "\n ", 1, "C", 1);
    
    
    $pdf->setfont('arial', '', 7);
    $pdf->setXY(5 + $wcol[0] + $wcol[1] + $wcol[2], $ly + 4);
    $pdf->Cell($wcol[3], 4, $httxt, 0, 0, 'C');
    $pdf->setXY(5 + $wcol[0] + $wcol[1] + $wcol[2] + $wcol[3] + $wcol[4], $ly + 4);
    $pdf->Cell($wcol[5], 4, $httxt, 0, 0, 'C');
    
    $pdf->setY($hauttableau + 8);
    $pdf->textColor("black");
}

function footer($pdf)
{
    global $template;
    if ($pdf->getY() > 280)
        return;
    $pdf->setY(280);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(200, 4, sprintf($template->translate("/devis/copyright/[site_code]"), date("Y")), 0, 0, 'C');
    $pdf->Ln(3);
    $pdf->Cell(200, 4, $template->translate("/devis/reserves_propriete", "F"), 0, 0, 'C');
    $pdf->Ln(3);
    $pdf->Cell(200, 4, $template->translate("/devis/loi", "F"), 0, 0, 'C');
    $pdf->SetFont('Arial', '', 8);
}

function calcport($poidstotal, $prixtotal, $livrmode, $prixtypeinstall, $poidscaves = array())
{
    global $DB_site, $facture, $frais_port;
    
    if ($prixtotal == 0 && $frais_port->prixbarreht > 0)
        $return = $frais_port->prixbarrettc($facture['tvaport']);
    else
        $return = $frais_port->prixttc($facture['tvaport']);
    
    $paysFR  = array(
        57,
        181
    );
    $install = pays_install($facture['paysid']);
    if (($livrmode == '1' || $livrmode == '4') && $install != false) {
        if ($facture['codepostal'] == "" && in_array($facture['paysid'], $paysFR))
            return false;
        
        if ($install == "sls")
            $tarif = prixsls($facture['codepostal'], $poidstotal * 1000, $facture['paysid'], $prixtypeinstall);
        if ($install == "enigma" && $poidscaves != array())
            $tarif = prix_enigma($poidscaves, $facture['site_code']);
        if ($tarif == 0)
            return false;
        $return += $tarif;
        
        if ($livrmode == 4)
            $return += prix_montage($facture['facturid']);
    }elseif($livrmode == '7'){
        $tarif = tarifMES($facture[facturid]);
        $tarif += prixsls($facture['codepostal'], $poidstotal * 1000, $facture['paysid'], $prixtypeinstall);
        $return = $tarif;
    }
    return $return;
}

function autremode($pdf, $mode, $poidstotal, $prix, $prixbarre, $title)
{
    global $template;
    global $trsptimg;
    global $livrlink;
    global $wcol;
    global $height;
    global $autocolor;
    
    $pdf->SetFont('Arial', 'B', 8);
    $livr   = sprintf($template->translate("/devis/mode_livraison_" . $mode), $poidstotal);
    $height = $pdf->nblines($wcol[1] + $wcol[2] + $wcol[3] + $wcol[4] + $wcol[5], $livr) * 4;
    if ($height + $pdf->getY() >= 250) {
        footer($pdf);
        $pdf->addpage();
        Head($pdf);
        $pdf->setY(30);
    }
    
    $pdf->SetFont('Arial', 'B', 8);
    $ly = $pdf->getY();
    $pdf->setXY(5, $ly);
    $pdf->fillColor($autocolor);
    $pdf->textColor("white");
    if ($title) {
        $pdf->cell(201, 4, $template->translate("/devis/option_autre_mode") . " :", 0, 0, 'C', 1);
        $pdf->ln(5);
    }
    $pdf->textColor("black");
    
    $ly  = $pdf->getY();
    $top = $ly;
    
    $pdf->setX(5 + $wcol[0]);
    $pdf->setfont('arial', '', 8);
    $pdf->multicell($wcol[1] + $wcol[2] + $wcol[3] + $wcol[4] + $wcol[5], 4, $livr, 0, 'L', 0);
    
    $pdf->setXY(5 + $wcol[0] + $wcol[1] + $wcol[2], $ly + $height);
    $pdf->setfillcolor(0);
    $pdf->settextcolor(255);
    $pdf->cell($wcol[3] + $wcol[4], 5, $template->translate("/devis/montant_option_ttc"), 1, 0, 'L', 1);
    $pdf->setXY(5 + $wcol[0] + $wcol[1] + $wcol[2] + $wcol[3] + $wcol[4], $ly + $height);
    $pdf->setfillcolor(255);
    $pdf->settextcolor(0);
    $pdf->setdrawcolor(201, 201, 201);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->cell($wcol[5], 5, $template->price_format($prix, 2, false), 1, 1, 'C', 1);
    $pdf->SetFont('Arial', '', 8);
    $size  = getimagesize($trsptimg[$mode]);
    $img_w = 10;
    $pdf->image($trsptimg[$mode], 5 + ($wcol[0] - $img_w) / 2, $ly + ($height + 5 - $img_w) / 2, $img_w);
    $pdf->link(10, $pdf->getY()-12, 170, 10, $livrlink[$mode]);
    $pc = 100 * (1 - ($prix / $prixbarre));
    if ($prixbarre > 0 && $pc != 0 && $mode == 0) {
        $pdf->setXY(5 + $wcol[0] + $wcol[1] + $wcol[2] - 12, $ly + $height);
        $pdf->cell(12, 5, "-" . round($pc) . " %", 1, 0, 'C', 0);
        $pdf->setXY(5 + $wcol[0] + $wcol[1] + $wcol[2] - 30, $ly + $height);
        $pdf->cell(18, 5, $template->price_format($prixbarre, 2, false), 1, 0, 'C', 0);
        $pdf->setdrawcolor(255, 0, 0);
        $pdf->line(5 + $wcol[0] + $wcol[1] + $wcol[2] - 28, $ly + $height + 1, 5 + $wcol[0] + $wcol[1] + $wcol[2] - 14, $ly + $height + 4);
    }
    $pdf->setXY(5, $ly);
    $pdf->drawColor("black");
    $pdf->cell(array_sum($wcol), $height + 6, "", 1, 0, 'L', 0);
    $pdf->setXY(5, $top + $height + 8);
    
}

function imgArtLeft($article,$template,$DB,$marque,$pdf){
    global $w;
    global $py;
    global $host;

    $logoimg=$rootpath."configurations/".$host."/images/marques/".$marque['marqueid'].".".$marque['image'];
    if(file_exists($logoimg)){
        $size=getimagesize($logoimg);
        $logo_w=40;
        $ly = $ly+25;
        if($size[1]*$logo_w/$size[0]>12) $logo_w=round($size[0]*12/$size[1]);
        $pdf->image($logoimg,5+(56-$logo_w)/2,$ly,$logo_w);
    }
    $pdf->settextcolor(48,40,37);
    $pdf->setfont('arial','',8);
    $pdf->setXY(5,$ly+14);
    $pdf->multicell(56,3,html_entity_decode($article["libelle".$template->langue],ENT_COMPAT,"ISO-8859-15"),0,'L',0);
    $pdf->ln(9);

    $autreimg=$DB->query_first("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
    $autreid="";
    if($autreimg!="") $autreid="_".$autreimg['articlephotoid'];
    $image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid'].$autreid.".".$article['image'];
    $w=46;
    $py=48;

    if(file_exists($image)){
        $size=getimagesize($image);
        if($size[1]>392) $w=intval($size[0]*(392/$size[1])*0.25);
        if($w>46) $w=46;
        $pdf->image($image,10,$py,$w);
    }
    $photos=$DB->query("SELECT * FROM articlephoto WHERE artid=".$article['artid']." AND langue IN ('','".$template->langue."') AND visible=0 AND pro IN ('".($template->pro?2:1)."','0') ORDER BY position LIMIT 3");
    while($photo=$DB->fetch_array($photos)){
        $py+=($w*$size[1]/$size[0])+5;
        $image=$rootpath."configurations/".$host."/images/produits/br/".$article['artid']."_".$photo['articlephotoid'].".".$photo['image'];
        if(file_exists($image)){
            $size=getimagesize($image);
            $imgbottom=$py+($w*$size[1]/$size[0]);
            if($imgbottom<280) $pdf->image($image,10,$py,$w);
        }
    }
 }

?>
