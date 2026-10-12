<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');

require_once(SITE_PATH."/class/facture.class.php");
require_once(BACK_PATH.'/sav.class.php');
require_once(BACK_PATH."/adv/adv.class.php");

$DB_site = clone $DB_site2;

//if(empty($_POST)) $_POST['sav_statut'] = 0;

if(!function_exists('get_responsable')){
        function get_responsable($sav_id){
                global $DB_site;

                $aSAV = array();

                $sql = "SELECT name, value";
                $sql .= " FROM sav_fields";
                $sql .= " WHERE savid = ".$sav_id;
                $req = $DB_site->query($sql);
                while($row = $DB_site->fetch_array($req)){
                        $aSAV[$row['name']] = $row['value'];
                }

                switch($aSAV['sav_typelitige']){
                        case 0:
                                return $aSAV['sav_responsable'];
                                break;
                        case 1:
                                if($aSAV['optsav_prischarge'] == 0){
                                        return $aSAV['sav_prischargesav'];
                                } else {
                                        return "SFG";
                                }
                                break;
                        case 2:
                        case 4:
                                switch($aSAV['sav_responsable']){
                                        case 0:
                                                return "Aucun";
                                        case 1:
                                                return "Fournisseur";
                                        case 2:
                                                return "Transporteur";
                                        case 3:
                                                return "Installateur";
                                        case 4:
                                                return "Acilyon";
                                        case 5:
                                                return "Client";
                                }
                                break;
                }
        }
}

if(!function_exists('get_factures_sav')){
        function get_factures_sav($aFactures){
                global $DB_site;

                $aReturn = array();
				if (count($aFactures) == 0) return $aReturn;

                $sql = "SELECT facturid as commande_id";
                $sql .= " FROM facture";
                $sql .= " WHERE provenance = 'sav'";
                $sql .= " AND provenanceid IN (".implode(',',$aFactures).")";
                $sql .= " UNION ";
                $sql .= "SELECT provenanceid as commande_id";
                $sql .= " FROM facture";
                $sql .= " WHERE provenance = 'sav'";
                $sql .= " AND facturid IN (".implode(',',$aFactures).")";
                $req = $DB_site->query($sql);
                while($row = $DB_site->fetch_array($req)){
                        $aReturn[] = $row['commande_id'];
                }

                return $aReturn;
        }
}

if(!function_exists('get_suivi')){
        function get_suivi($facturid, $type){
                global $DB_site;
                global $user_info;

                //Récupération de tous les ids commande du dossier SAV
                //  $aFactureIds = $aFactureClean = array();
                //  if (!is_null($facture_id)) 
                $aFactureIds = $aFactureClean = array($facturid);
                
                if($type != 'stock')
                {
                    while(1==1){
                        $loop = false;

                        $aFactureIds = get_factures_sav($aFactureIds);

                        foreach($aFactureIds as $facture_id){
                            if(!in_array($facture_id, $aFactureClean)){
                                $loop = true;
                                $aFactureClean[] = $facture_id;
                            }
                        }

                        $aFactureIds = $aFactureClean;

                        if(!$loop) break;
                    }
                    
                    $sql="SELECT pour,DATE_FORMAT(FROM_UNIXTIME(rappel), '%d/%m/%Y %H:%i') as dateRappel, rappel, `date`
                    FROM suivi_commande
                    WHERE 1";
                    if(count($aFactureClean)>0) $sql .= " AND facturid IN (".implode(',',$aFactureClean).")";
                    $sql.=" AND pour='".mysqli_real_escape_string($DB_site->link_id,$user_info['username'])."' AND rappel>0
                    ORDER BY date DESC LIMIT 1";
                    $tabRappel = $DB_site->query_first($sql);
                    
                }
                else
                {
                    
                    $sql="SELECT pour, DATE_FORMAT(FROM_UNIXTIME(rappel), '%d/%m/%Y %H:%i') as dateRappel, rappel, `date`";
                    $sql.=" FROM suivi_stock";
                    $sql.=" WHERE 1";
                    if(count($aFactureClean)>0 && !is_null($aFactureClean[0])) $sql .= " AND stock_cmd_id IN (".implode(',',$aFactureClean).")";
                    $sql.=" AND pour='".mysqli_real_escape_string($DB_site->link_id,$user_info['username'])."' AND rappel>0
                    ORDER BY date DESC LIMIT 1";
                    $tabRappel = $DB_site->query_first($sql);
                }

                $bg_color = 'green';
                
                if($tabReturn['rappel'] < time()) $bg_color = '#C5170E'; //Rouge à J*/
            
                if($tabRappel['rappel']<time()) $bg_color="red";
                $tabReturn=$tabRappel;

                $tabReturn['bgcolor'] = $bg_color;                

                return $tabReturn;
        }
}

if(!function_exists('get_montant_ttc')){
        function get_montant_ttc($facturid){
                global $DB_site;

                //Remise HT = TOT HT hors remise x (Remise TTC / TOT TTC hors remise)
                $sql = "SELECT SUM(lf.prix * lf.qte) as 'prixHT', f.prixport as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM(lf.prix * lf.qte) + f.prixport) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
                $sql .= " FROM facture f";
                $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
                $sql .= " WHERE f.facturid = ".intval($facturid);
                $sql .= " GROUP BY f.facturid";
                $ca_cmd = $DB_site->query($sql);

                $ca_total = 0;
                while($ligne = $DB_site->fetch_array($ca_cmd)){
                        $ca_total += $ligne['prixHT'] + $ligne['portHT'] - $ligne['remiseHT'];
                }

                return number_format($ca_total,2,',',' ');
        }
}

if(!function_exists('nice')){
        function nice($var, $color = 'black'){
                if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] == 'sylvain'){
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
        }
}

if(!function_exists('nicex')){
        function nicex($var, $color = 'black'){
                nice($var, $color);
                  
                if(isset($_COOKIE['admin_username']) && $_COOKIE['admin_username'] == 'sylvain')  exit();
        }
}


function couleur_marge($pct) {
	$pct_txt=@number_format($pct, 1, ',', ' ');
	if($pct<=0){
		$return="<font color=\"#ff00ff\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>0 && $pct<=20){
		$return="<font color=\"#ff0000\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>20 && $pct<=25){
		$return="<font color=\"#ffaa00\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>25 && $pct<=30){
		$return="<font color=\"#22CC22\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>30 && $pct<=40){
		$return="<font color=\"#339933\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>40){
		$return="<font color=\"#3333ff\"><b>".$pct_txt." %</b></font>";
	}
	return $return;
}



include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");

//Par défaut, tous les chiffres du recap à 0
for($i=0;$i<=7;$i++){
        $aRecapSAV[$i]['actif']   = 0;
        $aRecapSAV[$i]['cloture'] = 0;
        $aRecapSAV[$i]['cloture_analyse'] = 0;
}
if(!empty($_POST)){
        //NB SAV en cours
        $sql = "SELECT IFNULL(f.facturid,sc.id) as facturid, sf.value as sav_type, s.demande_cloture, s.sav_analyse";
        $sql .= " FROM sav s";
        $sql .= " INNER JOIN sav_fields sf ON (sf.savid = s.id)";
        $sql .= " LEFT JOIN stock_cmd sc ON (sc.id = s.facturid)";
        $sql .= " LEFT JOIN facture f ON (f.facturid = s.facturid)";
        $sql .= " WHERE 1";
        $sql .= " AND sf.name  = 'sav_typelitige'";
        $sql .= " AND sf.value != '5'";
        $sql .= " AND s.fin    = 0";
        $req = $DB_site->query($sql);
        while($le_sav = $DB_site->fetch_array($req)){

                if($le_sav['demande_cloture'] == 0){
                    //if($le_sav['sav_type'] == 0) echo $le_sav['facturid'].'<br>';
                    $aRecapSAV[$le_sav['sav_type']]['actif']++;
                } else {
                    if($le_sav['sav_analyse'] == 1){
                        $aRecapSAV[$le_sav['sav_type']]['cloture_analyse']++;
                    } else {
                        $aRecapSAV[$le_sav['sav_type']]['cloture']++;
                    }            
                }
        }

        //Filtres par défaut (quand aucun filtre sélectionné)
        $sqlStatut = " AND s.fin = 0 AND s.demande_cloture = 0";
        $sqlType   = " AND sf.value != '5'"; //On n'affiche pas les type "fraude"
        $sqlDate   = "";
        $filtertype = "green";
}
//Gestion des filtres
if(!empty($_POST)){

        //Filtre "Statut SAV"
        if($_POST['sav_type'] != ''){
                $sqlType = " AND sf.value = ".$_POST['sav_type'];
        }

        //Filtre "Statut SAV"
        switch($_POST['sav_statut']){
                case '0':
                        $sqlStatut = " AND s.fin = 0 AND s.demande_cloture = 0";
                        break;
                case '1':
                        $sqlStatut = " AND s.demande_cloture != 0 AND sav_analyse = 1 AND s.fin = 0";
                        break;
                case '2':
                        $sqlStatut = " AND s.demande_cloture != 0 AND sav_analyse = 2 AND s.fin = 0";
                        break;
                case '3':
                        $sqlStatut = " AND s.fin != 0";
                        break;
                default:
                        $sqlStatut = "";
                        break;
        }

        //Filtre "date"
        if($_POST['sav_debut'] != '' && $_POST['sav_fin'] != ''){

                $erreur_date = false;

                //Vérification de la saisie
                $aTemp = explode('/', $_POST['sav_debut']);
                $aTemp2 = explode('/', $_POST['sav_fin']);
                if(count($aTemp) != 3 || strlen($aTemp[0]) != 2 || strlen($aTemp[1]) != 2 || strlen($aTemp[2]) != 4) $erreur_date = true;
                if(count($aTemp2) != 3 || strlen($aTemp2[0]) != 2 || strlen($aTemp2[1]) != 2 || strlen($aTemp2[2]) != 4) $erreur_date = true;
                $date_debut = $aTemp[2].'-'.$aTemp[1].'-'.$aTemp[0];
                $date_fin = $aTemp2[2].'-'.$aTemp2[1].'-'.$aTemp2[0];

                if(!$erreur_date) $sqlDate = " AND FROM_UNIXTIME(s.date) BETWEEN '".$date_debut."' AND '".$date_fin."'";
        }

        //Filtre "SAV prioritaire"
        switch($_POST['sav_priorite']){
            case '1':
                $sqlPrio = " AND s.sav_priorite = 1";
                break;
            case '2':
                $sqlPrio = " AND s.sav_priorite = 2";
                break;
            default:
                $sqlPrio = "";
                break;
        }

        if($_POST['filtertype'] != "green"){
            $filtertype = "";
        }

        //Récupération des SAV en cours
        $sql = "SELECT "
                . "IFNULL(f.facturid,sc.id) as facturid"
                . ", s.id as sav_id"
                . ", f.datefacture as commande_date"
                . ", DATE_FORMAT(f.datefacture,'%d/%m/%Y') as commande_date_formate"
                . ", u.raisonsociale"
                . ", u.prenom"
                . ", u.nom"
                . ", u.vip"
                . ", IF(p.paysid = 57,'France',IF(p.paysid = 181, 'France (Corse)', p.libelle)) as pays_libelle"
                . ", u.ville"
                . ", u.site_code"
                . ", u.langue"
                . ", DATE_FORMAT(FROM_UNIXTIME(s.date), '%d/%m/%Y %Hh%i') as sav_date_formate"
                . ", s.lignes as liste_produits"
                . ", DATE_FORMAT(FROM_UNIXTIME(s.date),'%Y-%m-%d') as sav_date"
                . ", sf.value as type_litige"
                . ", s.sav_priorite"
                . ", IF(ISNULL(f.facturid),'stock','commande') as type_commande";
        $sql .= " FROM sav s";
        $sql .= " INNER JOIN sav_fields sf ON (sf.savid = s.id)";
        $sql .= " LEFT JOIN stock_cmd sc ON (sc.id = s.facturid)";
        $sql .= " LEFT JOIN facture f ON (f.facturid = s.facturid)";
        $sql .= " LEFT JOIN utilisateur u ON (u.userid = f.userid)";
        $sql .= " LEFT JOIN pays p ON (p.paysid = u.paysid)";
        $sql .= " WHERE 1";
        $sql .= " AND sf.name = 'sav_typelitige'";
        $sql .= $sqlType;
        $sql .= $sqlStatut;
        $sql .= $sqlDate;
        $sql .= $sqlPrio;
        $sql .= " GROUP BY s.id";
        $sql .= " ORDER BY sf.value";
        $req = $DB_site->query($sql);
        $nbEnr = $DB_site->num_rows($req);

 }
        
$sql = $DB_site2->query("SELECT marqueid, libelle FROM marque");
while($marque = $DB_site->fetch_array($sql)){
        $aMarques[$marque['marqueid']] = $marque['libelle'];
}
$aResponsables = array();

if(isset($_POST['sav_type']) && $_POST['sav_type'] == "" && isset($_POST['sav_statut']) && $_POST['sav_statut'] == ""){
    $message = "Veuillez sélectionner un Type de SAV";
}elseif($nbEnr <= 400 && !empty($_POST)){
        $aListeSAV     = array();
        $cpt           = 0;
        while($le_sav = $DB_site->fetch_array($req)){

                //Recherche sur les produits concernés
                $libelle_produit     = '';
                $libelle_marque      = '';
				if($_POST['sav_marque'] == 0){
					$critere_marque  = true;
				} else {
					$critere_marque  = false;
				}
                $critere_responsable = false;
				
                if(!empty($le_sav['liste_produits'])){

                        $critere_marque      = false;
                        $aRemMarque          = array();
                        $aExplode = explode(',', $le_sav['liste_produits']);
						$aProduit			 = array();
                        foreach($aExplode as $le_pdt){
							$aPdt = explode('-',$le_pdt);
							switch($le_sav['type_commande']){
								case 'commande':
									$sql = "SELECT lf.artcode, a.artcode_eurocave, m.libelle as marque_libelle, m.marqueid";
									$sql .= " FROM lignefacture lf";
									$sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
									$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
									$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
									$sql .= " WHERE lf.lignefactureid = ".intval($aPdt[1]);
									break;

								case 'stock':
									$sql = "SELECT a.artcode, a.artcode_eurocave, m.libelle as marque_libelle, m.marqueid";
									$sql .= " FROM article a";
									$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
									$sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
									$sql .= " WHERE a.artid = ".intval($aPdt[1]);
									break;
							}
							$aProduit = $DB_site->query_first($sql);

							$libelle_produit .= ($libelle_produit != '' ? '<br>' : '').$aProduit['artcode'].(strlen($aProduit['artcode_eurocave']) <= 20 ? '<br>('.$aProduit['artcode_eurocave'].')' : '');
							if(!in_array($aProduit['marque_libelle'], $aRemMarque)){
									$aRemMarque[] = $aProduit['marque_libelle'];
									$libelle_marque  .= ($libelle_marque != '' ? '<br>' : '').$aProduit['marque_libelle'];
							}

							//Remplissage tableau select "Marque"
							if(!array_key_exists($aProduit['marqueid'], $aMarques)){
									$aMarques[$aProduit['marqueid']] = $aProduit['marque_libelle'];
							}

							//Filtration par "Marque"
							if(empty($_POST) || $_POST['sav_marque'] == 0 || $_POST['sav_marque'] == $aProduit['marqueid']) $critere_marque = true;
                        }
                }
                
                $responsable = get_responsable($le_sav['sav_id']);

                //Remplissage tableau select "Responsable"
                if(!in_array($responsable, $aResponsables) && $responsable != '') $aResponsables[] = $responsable;

                //Filtration par "Responsable"
                if(empty($_POST) || $_POST['sav_responsable'] == '' || ($_POST['sav_responsable'] == $responsable && $responsable != '')) $critere_responsable = true;

                if($critere_marque && $critere_responsable){

                    //Recherche sur site de provenance
                    switch($le_sav['site_code']){
                        case '':
                        case 'base':
                            switch($le_sav['langue']){
                                case 'FR':
                                    $provenance = 'MCAVFR';
                                    break;
                                case 'EN':
                                    $provenance = 'MWCCOM';
                                    break;
                                case 'ES':
                                    $provenance = 'MVES';
                                    break;
                            }
                            break;
                        case 'uk':
                            $provenance = 'MWCUK';
                            break;
                    }

                    $nj_jours_creation = round((strtotime(date('Y-m-d')) - strtotime($le_sav['sav_date'])) / 86400);
                    $nj_jours_tri      = round((strtotime(date('Y-m-d')) - strtotime($le_sav['sav_date'])) / 86400);

                    $tabSuivi = get_suivi($le_sav['facturid'], $le_sav['type_commande']);
                        
                    if($tabSuivi['bgcolor'] != 'green' || ($tabSuivi['bgcolor'] == 'green' && isset($_POST['rappels_futur']))){
                        $dernier_rappel = '';
                        if(!empty($tabSuivi)) $dernier_rappel = $tabSuivi['pour']."<br>".$tabSuivi['dateRappel'];

                        //Utilisation de la date de commande pour le tri du tableau, TSI ajout de la categorie 6 avarie occulte
                        if(!isset($remNBJours)) $remNBJours = array(0 => array(), 1 => array(), 2 => array(), 3 => array(), 4 => array(), 6 => array(), 7 => array());

                        //Si aucun rappel, on insère la valeur 0 en début d'index pour qu'ils apparaissent en 1er dans le tableau
                        if($tabSuivi['bgcolor'] == 'green'){
                                $nj_jours_tri = intval('1'.$nj_jours_tri.'00');
                        } else {
                                $nj_jours_tri = intval('1000'.$nj_jours_tri.'00');
                        }

                        //Si une date a déjà été utilisée comme index, on ajoute la valeur du compteur pour les différencier
                        if(!in_array($nj_jours_tri, $remNBJours[$le_sav['type_litige']])){
                                $remNBJours[$le_sav['type_litige']][] = $nj_jours_tri;
                        } else {
                                while(in_array($nj_jours_tri, $remNBJours[$le_sav['type_litige']])){
                                    $nj_jours_tri++;
                                }
                                $remNBJours[$le_sav['type_litige']][] = $nj_jours_tri;
                        }

						$parent     = getparentsav($le_sav['facturid']);
						$links      = savlink($parent);
						$savlinks   = savlinkids($parent,$links);
						
						
						$ligne=get_totaux_facture($le_sav['facturid'], $DB_site);
						$marge     		= $ligne['total_marge_previ'];
						$margereel 		= $ligne['total_marge_reel'];
						$marge_prc      = couleur_marge($ligne['total_marge_previ_pct']);
						$margereel_prc  = couleur_marge($ligne['total_marge_reel_pct']);
						
						// Quand on veut faire du ménage ....
						// à ne surtout pas décommenter ......
						/*
						if($marge>20){
							$sql="UPDATE `sav` SET fin = '1549377738' WHERE id='".$le_sav['sav_id']."' ";
							// $DB_site->query($sql);
						}
						*/
						//
						
						$img_prio = false;
						if($le_sav['sav_priorite'] == 1) $img_prio = true;
						
						//Le lien dépend du type de SAV (stock/commande)
						switch($le_sav['type_commande'])
						{
							case 'stock':
									$lien_commande = "/stock.php?action=cmd&stock_cmd_id=" . $le_sav['facturid'];
								break;
							case 'commande':
									$lien_commande = "/facture.php?action=editer&facturid=" . $le_sav['facturid'] . "&lst_trietat=";
								break;
						}

						$aListeSAV[$le_sav['type_litige']][$nj_jours_tri] = ""
						. "<tr ".($tabSuivi['bgcolor'] == 'green' ? 'class="futur"' : '').">"
								. "<td class=\"%td_alt%\"><a href=\"" . BACK_URL . $lien_commande . "\" target=\"_blank\">".$le_sav['facturid']."</a><br>".$le_sav['commande_date_formate']."".($img_prio ? "<br><img src=\"images/icon/agt_update_critical.png\" alt=\"Prioritaire\"><br><span style=\"font-weight: bold; color: red;\">Prioritaire</strong>" : "")."</td>"
								. "<td class=\"%td_alt%\">".($le_sav['raisonsociale'] != '' ? $le_sav['raisonsociale'].'<br>' : '').ucfirst(mb_strtolower($le_sav['prenom'], 'UTF-8'))." ".mb_strtoupper($le_sav['nom'], 'UTF-8')."<br>".usericon($le_sav['vip'], 20)."</td>"
								. "<td class=\"%td_alt%\">".ucfirst(mb_strtolower($le_sav['ville'], 'UTF-8')).'<br>'.mb_strtoupper($le_sav['pays_libelle'], 'UTF-8')."</td>"
								. "<td class=\"%td_alt%\">".$provenance."</td>"
								. "<td class=\"%td_alt%\">".$libelle_produit."</td>"
								. "<td class=\"%td_alt%\">".$libelle_marque."</td>"
								. "<td class=\"%td_alt%\">".$le_sav['sav_date_formate']."</td>"
								. "<td class=\"%td_alt%\"><span style=\"color:".($nj_jours_creation > 30 ? 'red' : 'green')."\">".$nj_jours_creation."</span></td>"
								. "<td class=\"%td_alt%\">".$responsable."</td>"
								. "<td class=\"%td_alt%\" style=\"background-color: ".($tabSuivi['bgcolor'] != '' ? $tabSuivi['bgcolor'] : '#C5170E')."; color: white;\">".$dernier_rappel."</td>"
								. "<td class=\"%td_alt%\">".get_montant_ttc($le_sav['facturid'])." &euro;</td>";
						$aListeSAV[$le_sav['type_litige']][$nj_jours_tri] .= ""
								. "<td class=\"%td_alt%\">".number_format($marge,2,',','')." &euro;<br> (<b>".$marge_prc."</b>)<hr>".number_format($margereel,2,',','')." &euro;<br> (<b>".$margereel_prc."</b>)</td>";
						$aListeSAV[$le_sav['type_litige']][$nj_jours_tri] .= "</tr>";
                            $cpt++;
                    }         
                } else {
                        $nbEnr--;
                }
        }

        asort($aMarques);
        asort($aResponsables);
} else {
       if(!empty($_POST)) $message = 'En raison d\'un trop grand nombre de résultats (+400), la recherche ne peut être affichée, à moins d\'être affinée';
} 

?>
<h1>Vue d'ensemble des SAV</h1>

<div style="float: left; width: 400px;">
        <table border="0" cellpadding="0" cellspacing="0" class="sav_recap" style="margin: 0 auto;">
                <tr>
                        <td class="entete_td_refonte"></td>
                        <td class="entete_td_refonte">En cours</td>
                        <td class="entete_td_refonte">SAV fini<br>(pb renta)</td>
                        <td class="entete_td_refonte">A clôturer</td>
                </tr>
                <tr>
                        <td class="td_gris1"><strong>Avarie de transport</strong></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[0]['actif']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[0]['cloture_analyse']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[0]['cloture']; ?></td>
                </tr>
                 <tr>
                        <td class="td_gris2"><strong>Avarie occulte</strong></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[6]['actif']; ?></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[6]['cloture_analyse']; ?></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[6]['cloture']; ?></td>
                </tr>
                <tr>
                        <td class="td_gris1"><strong>Panne</strong></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[1]['actif']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[1]['cloture_analyse']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[1]['cloture']; ?></td>
                </tr>
                <tr>
                        <td class="td_gris2"><strong>Erreur de livraison</strong></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[2]['actif']; ?></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[2]['cloture_analyse']; ?></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[2]['cloture']; ?></td>
                </tr>
                <tr>
                        <td class="td_gris1"><strong>Rétractation</strong></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[3]['actif']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[3]['cloture_analyse']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[3]['cloture']; ?></td>
                </tr>
                <tr>
                        <td class="td_gris2"><strong>Produit manquant</strong></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[4]['actif']; ?></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[4]['cloture_analyse']; ?></td>
                        <td class="td_gris2"><?php echo $aRecapSAV[4]['cloture']; ?></td>
                </tr>
				<tr>
                        <td class="td_gris1"><strong>Colis perdu</strong></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[7]['actif']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[7]['cloture_analyse']; ?></td>
                        <td class="td_gris1"><?php echo $aRecapSAV[7]['cloture']; ?></td>
                </tr>
                <tr>
                        <td class="td_gris1" style="background-color: #A71145; color: white;"><strong>Total</strong></td>
                        <td class="td_gris1"><strong><?php echo $aRecapSAV[0]['actif'] + $aRecapSAV[1]['actif'] + $aRecapSAV[2]['actif'] + $aRecapSAV[3]['actif'] + $aRecapSAV[4]['actif'] + $aRecapSAV[6]['actif'] + $aRecapSAV[7]['actif']; ?></strong></td>
                        <td class="td_gris1"><strong><?php echo $aRecapSAV[0]['cloture_analyse'] + $aRecapSAV[1]['cloture_analyse'] + $aRecapSAV[2]['cloture_analyse'] + $aRecapSAV[3]['cloture_analyse'] + $aRecapSAV[4]['cloture_analyse'] + $aRecapSAV[6]['cloture_analyse'] + $aRecapSAV[7]['cloture_analyse']; ?></strong></td>
                        <td class="td_gris1"><strong><?php echo $aRecapSAV[0]['cloture'] + $aRecapSAV[1]['cloture'] + $aRecapSAV[2]['cloture'] + $aRecapSAV[3]['cloture'] + $aRecapSAV[4]['cloture'] + $aRecapSAV[6]['cloture'] + $aRecapSAV[7]['cloture']; ?></strong></td>
                </tr>
        </table>
</div>

<div class="commande_detail" style="width: 500px !important; margin-top: 0;">
        <div style="padding: 10px;">
                <form method="post" action="" style="margin: 0;">
                <table cellpadding="0" cellspacing="0" class="sav_filtres">
                                <tr>
                                        <td style="text-align: right;">
                                                <strong>Type de SAV :</strong> 
                                        </td>
                                        <td>
                                                <select name="sav_type" style="max-width: 125px; margin-left: 5px;">
                                                        <option value="">Tous</option>
                                                        <option value="0" <?php echo !empty($_POST) && $_POST['sav_type'] == '0' ? 'selected' : ''; ?>>Avarie de transport</option>
                                                        <option value="6" <?php echo !empty($_POST) && $_POST['sav_type'] == '0' ? 'selected' : ''; ?>>Avarie occulte</option>
                                                        <option value="1" <?php echo !empty($_POST) && $_POST['sav_type'] == '1' ? 'selected' : ''; ?>>Panne</option>
                                                        <option value="2" <?php echo !empty($_POST) && $_POST['sav_type'] == '2' ? 'selected' : ''; ?>>Erreur de livraison</option>
                                                        <option value="3" <?php echo !empty($_POST) && $_POST['sav_type'] == '3' ? 'selected' : ''; ?>>Rétractation</option>
                                                        <option value="4" <?php echo !empty($_POST) && $_POST['sav_type'] == '4' ? 'selected' : ''; ?>>Produit manquant</option>
														<option value="7" <?php echo !empty($_POST) && $_POST['sav_type'] == '7' ? 'selected' : ''; ?>>Colis perdu</option>
                                                </select>
                                        </td>
                                        <td style="text-align: right;">
                                                <strong>Statut :</strong>
                                        </td>
                                        <td>
                                                <select name="sav_statut" style="max-width: 125px; margin-left: 5px;">
                                                        <option value="">Tous</option>
                                                        <option value="0" <?php echo empty($_POST) || $_POST['sav_statut'] == '0' ? 'selected' : ''; ?>>En cours</option>
                                                        <option value="1" <?php echo !empty($_POST) && $_POST['sav_statut'] == '1' ? 'selected' : ''; ?>>SAV fini (problème rentabilité)</option>
                                                        <option value="2" <?php echo !empty($_POST) && $_POST['sav_statut'] == '2' ? 'selected' : ''; ?>>A clôturer</option>
                                                        <option value="3" <?php echo !empty($_POST) && $_POST['sav_statut'] == '3' ? 'selected' : ''; ?>>Clôturé</option>
                                                </select>
                                        </td>
                                </tr>
                                <tr>
                                        <td style="text-align: right;">
                                                <strong>Marque :</strong>
                                        </td>
                                        <td>
                                                <select name="sav_marque" style="max-width: 125px; margin-left: 5px;">
                                                        <option value="0">Toutes</option>
                                                        <?php foreach($aMarques as $marque_id => $marque_libelle){ ?>
                                                        <option value="<?php echo $marque_id; ?>" <?php echo !empty($_POST) && $_POST['sav_marque'] == $marque_id ? 'selected' : ''; ?>><?php echo mb_strtoupper($marque_libelle, 'UTF-8'); ?></option>
                                                        <?php } ?>
                                                </select>
                                        </td>
                                        <td style="text-align: right;">
                                                <strong>Responsable :</strong>
                                        </td>
                                        <td>
                                                <select name="sav_responsable" style="max-width: 125px; margin-left: 5px;">
                                                        <option value="">Tous</option>
                                                        <?php foreach($aResponsables as $le_responsable){ ?>
                                                        <option value="<?php echo $le_responsable; ?>" <?php echo !empty($_POST) && $_POST['sav_responsable'] == $le_responsable ? 'selected' : ''; ?>><?php echo mb_strtoupper($le_responsable, 'UTF-8'); ?></option>
                                                        <?php } ?>
                                                </select>
                                        </td>
                                </tr>
                                <tr>
									
                                    <td style="text-align: right;"><strong>Priorité :</strong></td>
                                    <td>
                                        <select name="sav_priorite" style="max-width: 125px; margin-left: 5px;">
                                            <option value="0" selected>Tous</option>
                                            <option value="1" <?php echo isset($_POST['sav_priorite']) && $_POST['sav_priorite'] == 1 ? 'selected' : ''; ?>>Oui</option>
                                            <option value="2" <?php echo isset($_POST['sav_priorite']) && $_POST['sav_priorite'] == 2 ? 'selected' : ''; ?>>Non</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right;">
                                           <!--<input type="checkbox" name="rappels_futur" onclick="toggle_futur();">-->
                                           <input type="checkbox" name="rappels_futur" <?php if(isset($_POST['rappels_futur'])){ ?>checked="checked"<?php } ?>>
                                    </td>
                                    <td><strong>Afficher les rappels futurs</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center">
                                        <strong>SAV créé entre le <input type="text" name="sav_debut" style="width: 80px;" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['sav_debut']) ? $_POST['sav_debut'] : ''; ?>"> et le <input type="text" style="width: 80px;" name="sav_fin" placeholder="xx/mm/YYYY" value="<?php echo !empty($_POST['sav_fin']) ? $_POST['sav_fin'] : ''; ?>"></strong>
                                        <?php if($erreur_date){ ?>
                                        <br>
                                        <span style="color: red; padding-top: 5px;">Erreur de saisie de date. Le filtre n'a pas été pris en compte</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                        <td colspan="4" align="center" valign="bottom"><input type="submit" value="Rechercher"></td>
                                </tr>
                        </table>
                </form>
    </div>
</div>
<div style="clear: both;"></div>

<?php if(isset($message)){ ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $message; ?></p>
<?php } elseif(!empty($_POST)) { ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $cpt; ?> résultat(s)</p>
<?php } ?>
<p>
	<div><b>Légende :</b></div>
	<div><b><font color="ff00ff">marge négative</font></b></div>
	<div><b><font color="ff0000">0% &lt; marge &lt; 20%</font></b></div>
	<div><b><font color="ffaa00">20% &lt; marge &lt; 25%</font></b></div>
	<div><b><font color="22CC22">25% &lt; marge &lt; 30%</font></b></div>
	<div><b><font color="339933">30% &lt; marge &lt; 40%</font></b></div>
	<div><b><font color="3333ff">marge &gt; 40%</font></b></div>
</p>
<?php 

if(!empty($aListeSAV)){
    foreach($aListeSAV as $sav_type => $le_sav){

        //Type de litige
        switch($sav_type){
                case 0:
                        $type_litige = 'Avarie de transport';
                        break;
                case 1:
                        $type_litige = 'Panne';
                        break;
                case 2:
                        $type_litige = 'Erreur de livraison';
                        break;
                case 3:
                        $type_litige = 'Rétractation';
                        break;
                case 4:
                        $type_litige = 'Produit manquant';
                        break;
                case 6:
                        $type_litige = 'Avarie occulte';   // TSI Ajout categorie avarie occulte
                        break;
				case 7:
                        $type_litige = 'Colis perdu';
                        break;
        }

        echo ""
        . "<table border=\"0\" class=\"sav_listing\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin: 10px 0 0;\">"
        . "<tr>"
                . "<td colspan=\"2\" style=\"text-align: left; background-color: #A71145; color: white; font-weight: bold; font-size: 15px;\">".$type_litige."</td>"
        . "</tr>"
        . "<tr>"
                . "<td class=\"entete_td_refonte\">CMD</td>"
                . "<td class=\"entete_td_refonte\">Société<br>Client</td>"
                . "<td class=\"entete_td_refonte\">Dom.</td>"
                . "<td class=\"entete_td_refonte\">Prov.</td>"
                . "<td class=\"entete_td_refonte\">Pdt(s)</td>"
                . "<td class=\"entete_td_refonte\">Marque(s)</td>"
                . "<td class=\"entete_td_refonte\">Date SAV</td>"
                . "<td class=\"entete_td_refonte\">NB jours</td>"
                . "<td class=\"entete_td_refonte\">Resp.</td>"
                . "<td class=\"entete_td_refonte\">Date prochain rappel</td>"
                . "<td class=\"entete_td_refonte\" style=\"width: 90px;\">Total TTC</td>"
                . "<td class=\"entete_td_refonte\">Marge Prév./Réel</td>"
        . "</tr>";

        krsort($le_sav);

        $cpt = 0;
        foreach($le_sav as $key => $ligne_sav){
                echo str_replace('%td_alt%', 'td_gris'.($cpt%2 == 0 ? '1' : '2'), $ligne_sav);
                $cpt++;
        }

        echo "</table>";
    }
}

include_once(BACK_PATH."/footer.php");

function getparentsav($linkid){
    global $DB_site2;

    $savfrom=$DB_site2->query_first("SELECT provenanceid FROM facture WHERE provenance IN ('sav','avoir') AND facturid=".intval($linkid));

    if($savfrom[0]!="")
        return getparentsav($savfrom[0]);
    else
        return $linkid;
}

function savlink($linkid){
    global $DB_site2;

    $return=array();

    if($sens=="to" || is_null($sens)){
        $r=$DB_site2->query("SELECT facturid FROM facture WHERE provenance IN ('sav','avoir') AND provenanceid=".intval($linkid));

        while($savto=$DB_site2->fetch_array($r)){
            $return[$savto[0]]=savlink($savto[0]);
        }

        if($DB_site2->num_rows($r)==0) $return=false;
    }

    return $return;
}

function savlinkids($parent,$links){
    $return=array($parent);
    IF($links==false) return $return;
    foreach($links as $link=>$a){
        $return=array_merge($return,savlinkids($link,$a));
    }

    return $return;
}

function marge($fact){
    global $DB_site;

    $sql = "SELECT efl.paht, efl.ecopart, efl.qte, efl.facture";
    $sql .= " FROM envoi_fournisseur ef";
    $sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
    $sql .= " WHERE ef.destinataire <> '0'";
    $sql .= " AND ef.facturid = ".intval($fact);
    $requete = $DB_site->query($sql);
    while($ligne = $DB_site->fetch_array($requete)){
        if($ligne['facture'] != "" || $ligne['facture'] == "STOCK"){
            $totalachatreel += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
        }
        $totalachat += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
    }

    $sql = "SELECT facturid, prix";
    $sql .= " FROM facture_fournisseur_autre";
    $sql .= " WHERE facturid = ".intval($fact);
    $requete = $DB_site->query($sql);
    while($ligne = $DB_site->fetch_array($requete)){
        $totalachat += $ligne['prix'];
        $totalachatreel += $ligne['prix'];
    }

    $sql = "SELECT paht, ecopart, date_recu";
    $sql .= " FROM avoir_ligne";
    $sql .= " WHERE facturid = ".intval($fact);
    $requete = $DB_site->query($sql);
    while($ligne=$DB_site->fetch_array($requete)){
        if($ligne['date_recu'] == 0){
            $totalachat -= ($ligne['paht']+$ligne['ecopart']);
        }
    }

    $sql = "SELECT s.artid";
    $sql.= " FROM stock_item s";
    $sql.= " INNER JOIN article a USING(artid)";
    $sql.= " WHERE origin ='retour:".intval($fact)."'";
    $sql.= " GROUP BY date";
    $req = $DB_site->query($sql);
    if($DB_site->num_rows($req) > 0){
        while($retour = $DB_site->fetch_array($req)){ 
            $sqlac = $DB_site->query("SELECT artid, artcode FROM lignefacture WHERE factureid=".intval($fact));
            while($ac = $DB_site->fetch_array($sqlac)){
                $si = $DB_site->query("SELECT artid, artids FROM stock_mini WHERE artids=".$retour['artid']);
                if($DB_site->num_rows($si) != 0){
                    $a = $DB_site->fetch_array($si);
                    $artid = $a['artids'];
                }else{
                    $artid = $ac['artid'];
                }
            }

            $sql_lf = "SELECT lignefactureid";
            $sql_lf.= " FROM lignefacture";
            $sql_lf.= " WHERE factureid =".intval($fact);
            $sql_lf.= " AND artid=".$artid;
            $lignefacture = $DB_site->query_first($sql_lf);
            
            $sql = "SELECT ROUND((efl.paht + efl.ecopart),2) AS montantht";
            $sql.= " FROM envoi_fournisseur_ligne efl";
            $sql.= " INNER JOIN envoi_fournisseur ef ON (ef.id = efl.efid)";
            $sql.= " WHERE efl.ligne_facture_id = ".intval($lignefacture['lignefactureid']);
            $sql.= " AND (efl.type = 'stock' OR efl.type = 'fourn')";
            $sql.= " AND ef.destinataire <> '0'";
            $sql.= " AND ef.facturid = ".intval($fact);
            $ht = $DB_site->query_first($sql);
        }
    }

    $totalachat = $totalachat - ($retour['qte']*$ht['montantht']);
    $totalachatreel = $totalachatreel - ($retour['qte']*$ht['montantht']);

    return $totalachat."-".$totalachatreel;
}