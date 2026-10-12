<?php

if (!function_exists("url_rewrite")) {
    function url_rewrite($string) {
    	$return = "";
        $string=html_entity_decode($string );
        $caracteres = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
        "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê"=> "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
        "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
        "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
        "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å"=> "a", "æ" => "a", "ç"=> "c",
        "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï"=> "i",
        "ð"=> "o", "ñ"=> "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
        "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y", "č" => "e");
        $string = strtr($string , $caracteres);
        $string=strtolower($string);
        $len = strlen($string);
        for($a=0; $a<$len; $a++){
            $p = ord($string[$a]);
            if ( ($p > 96 && $p < 123) or ($p > 47 && $p < 58) ) {
                $return .= $string[$a];
            } else {
                $return .= "-";
            }
        }
        while(strpos($return,"--")!==FALSE) {
            $return=str_replace("--","-",$return);
        }
        $return=trim($return,"-");
        return $return;
    }
}

function resizedimg($url,$maxw,$maxh){
  $s=getimagesize($url);
  $w=$s[0];
  $h=$s[1];
  if($s[0]>$s[1]){
    if($s[0]>$maxw){
      $h=intval($maxw*($s[1]/$s[0]));
      $w=$maxw;
    }
    if($h>$maxh){
      $w=intval($maxh*($s[0]/$s[1]));
      $h=$maxh;
    }
  }else{
    if($s[1]>$maxh){
      $w=intval($maxh*($s[0]/$s[1]));
      $h=$maxh;
    }
    if($w>$maxw){
      $h=intval($maxh*($s[1]/$s[0]));
      $w=$maxw;
    }
  }
  return array($w,$h);
}



function recur_categorie( $tab , $pere, $nb_elements )
    {
    global $tab_catid;
    for( $i=0; $i<$nb_elements; $i++ )
        {
        if ( $tab[$i][1] == $pere )
            {
            $tab_catid .= ",".$tab[$i][0];
            $tab_catid .= recur_categorie( $tab, $tab[$i][0], $nb_elements );
            }
        }
    }

   
function recur_categories($parentid,$niveau){
    global $DB_site;
    global $libelle;
    global $categories;
    $cats=$DB_site->query("select * from categorie where parentid = '$parentid' order by $libelle");
    while ($cat=$DB_site->fetch_array($cats))
        {
        if (compter_produits($cat[catid]) > 0)
            {
            $url_rewriting_catname=url_rewrite($cat[$libelle]);
            $categories.="<div style=\"margin-left:".($niveau*10)."px;\"><a class=\"liensinternautes\" href=\"cat,$cat[catid],$url_rewriting_catname.htm\">$cat[$libelle]</a></div>";
            recur_categories($cat[catid],$niveau+1);
            }
        }
    return($categories);
}

function categories_filles($catid)
    {
    global $DB_site, $cats_cde, $cat_fille ;
    $filles=$DB_site->query("select catid from categorie where parentid = '$catid'");
    while ($fille=$DB_site->fetch_array($filles))
        {
        $cat_fille = $fille[catid] ;
        $cats_cde[$cat_fille] = $cat_fille ;
        categories_filles($cat_fille);
        }
    return ($cats_cde) ;
    }


function ip2country($ip){
         global $DB_site;
         $dotted = preg_split( "/[.]+/", $ip);
         $ip2 = (double) ($dotted[0]*16777216)+($dotted[1]*65536)+($dotted[2]*256)+($dotted[3]);

         $row = $DB_site->query_first("SELECT `COUNTRY_NAME` FROM ip2country WHERE $ip2 BETWEEN `IP_FROM` AND `IP_TO`");

         return $row['COUNTRY_NAME'];

}

function payssls(){
	global $DB_site;
	$ret=array();
	$req=$DB_site->query("SELECT paysid FROM slszones GROUP BY zone");
	while($z=$DB_site->fetch_array($req)){
	    $p=explode(",",$z['paysid']);
	    $ret=array_merge($ret,$p);
	}
	return $ret;
}

function tauxParPays($DB, $paysid){
	$sql = "SELECT port, gestion, TVAtauxnormal, cee, tvaintracom";
    $sql .= " FROM pays";
    $sql .= " WHERE paysid = '".mysqli_real_escape_string($DB->link_id,$paysid)."'";

    return $taux = $DB->query_first($sql);
}

function prixsls($codepostal,$poids,$paysid,$prixtype=""){
	global $DB_site;
	$poids=floatval($poids);
	$sql="SELECT sls.* FROM slszones INNER JOIN sls ON (sls.zone=slszones.zone) WHERE paysid REGEXP '(^$paysid$|^$paysid,|,$paysid,|,$paysid$)'";
	if(in_array($paysid,array(57,181,111))) $sql.=" AND slszones.dep=".intval(substr($codepostal,0,2));
	$sql.=" AND ".$poids.">sls.from AND ".$poids."<=sls.to";
	$tarif=$DB_site->query_first($sql);
	return isset($tarif['prix'.$prixtype]) ? $tarif['prix'.$prixtype] : $tarif['prix'];
}

function install_type($artid){
    global $prixtypeinstall;
    if(maxweight($artid)>110) $prixtypeinstall="lv4";
    return $prixtypeinstall;
}

function prix_montage($facturid){
	global $DB_site;
	$total=0;
	
	$pays=$DB_site->query_first("SELECT paysid,site_code FROM facture WHERE facturid=".intval($facturid));
	if($pays['site_code']=="") $pays['site_code']="base";

	$montage=$DB_site->query_first("SELECT prix FROM montage WHERE paysid=".intval($pays['paysid']));

	$pumontage=unserialize($montage['prix']);

	$pumontagesite=$pumontage[$pays['site_code']];

	$req=$DB_site->query("SELECT montage_heures,qte FROM lignefacture INNER JOIN article USING(artid) WHERE montage=1 AND factureid=".intval($facturid)." GROUP BY artid");

	while($art=$DB_site->fetch_array($req)) {
	     $total+=$art['montage_heures']*$art['qte']*intval($pumontagesite);
	}

	return $total;
}
function prix_montage_devis($lignefacture){
	global $DB_site;
	$total=0;
	
	$pays=$DB_site->query_first("SELECT paysid,site_code FROM facture WHERE facturid=".intval($lignefacture->facturid));
	if($pays['site_code']=="") $pays['site_code']="base";

	$montage=$DB_site->query_first("SELECT prix FROM montage WHERE paysid=".intval($pays['paysid']));

	$pumontage=unserialize($montage['prix']);

	$pumontagesite=$pumontage[$pays['site_code']];

	$total+=$lignefacture->montage_heures*$lignefacture->qte*intval($pumontagesite);
	

	return $total;
}
function tarifMES($facturid,$txt=false){
     global $DB_site;
     $prix_ttc = 0;
     $typearticle_texte="";
     $return = array();
     //tarif mise en service
     //Récupération des articles de la commande
     $aTypeArticle = array();
     $req = $DB_site->query("SELECT DISTINCT(a.typearticleid) FROM lignefacture lf INNER JOIN article a ON (a.artid = lf.artid) WHERE lf.factureid = ".$facturid);
     while($row = $DB_site->fetch_array($req)){
        $aTypeArticle[] = $row['typearticleid'];
     }
     $aInstallation = array();
     //Récupérations des installations correspondantes
     foreach($aTypeArticle as $typearticleid){
          $req = $DB_site->query("SELECT * FROM installation i WHERE i.installation_type_id = 2 AND installation_typearticle = '|".$typearticleid."|'");
          while($row = $DB_site->fetch_array($req)){
            $aInstallation[] = $row;
          }
     }
     if(count($aInstallation) > 0){
      	foreach($aInstallation as $l_installation){
           //Concaténation des types d'articles pour affichage dans le titre
           $aTemp = explode('|', $l_installation['installation_typearticle']);
           $aTypeTemp = array();
           foreach($aTemp as $ligne){
              if(is_numeric($ligne)) $typearticleid = $ligne;
           }
           if($txt){
           		$sql = "SELECT libelle, text_livraison";
                $sql .= " FROM typearticle";
                $sql .= " WHERE typearticleid = ".$typearticleid;
                $libelle_type = $DB_site->query_first($sql);
                $typearticle_texte.= "La mise en service de votre $libelle_type[libelle] comprend : " .$libelle_type['text_livraison']."\n";
           }                      
           //Calcul du prix de vente TTC
           $prix_ttc += $l_installation['installation_pv_ht'] * (1 + ($l_installation['installation_tva_taux'] / 100));
       	}
     }
     if($txt){
     	$return[0] = $prix_ttc+30;
     	$return[1] = $typearticle_texte;
     	return $return;
     }else{
     	return $prix_ttc+30;
     }
     
}

function txt_mes($facturid){
	global $DB_site;
    $prix_ttc = 0;
    $aTypeArticle = array();
	$req = $DB_site->query("SELECT DISTINCT(a.typearticleid) FROM lignefacture lf INNER JOIN article a ON (a.artid = lf.artid) WHERE lf.factureid = ".$facturid);
	while($row = $DB_site->fetch_array($req)){
	   $aTypeArticle[] = $row['typearticleid'];
	}

}

function prix_enigma($poidscaves,$site_code){
	global $DB_site;
	
	$total=0;
	foreach($poidscaves as $poids=>$qte){
	    $prix=$DB_site->query_first("SELECT prix FROM enigma WHERE `from`<=".floatval($poids)." AND ".floatval($poids)."<`to` AND site_code='".$site_code."'");
	    $total+=$prix[0]*$qte;
	}
	return $total;
}

function pays_enigma(){
	return array(172);
}

function pays_install($paysid){
	if(in_array($paysid,payssls())) return "sls";
	if(in_array($paysid,pays_enigma())) return "enigma";
	return false;
}

if (!function_exists("checkvatnumber")) {
function checkvatnumber($vatNumber){
	$pays=array("DE","ATU","BE","BG","CY","DK","ES","EE","FI","FR","EL","HU","IE","IT","LV","LT","LU","MT","NL","PL","PT","SK","CZ","RO","GB","SI","SE");
	$vatNumber 	= str_replace(array(' ', '.', '-', ',', ', '), '', $vatNumber);
	$countryCode=substr($vatNumber,0,2);
	$vatNumber=substr($vatNumber,2);
	if(strlen($countryCode)!=2 || is_numeric(substr($countryCode, 0, 1)) || !in_array($countryCode,$pays)) return array("result"=>false,"error"=>"invalid_country");

	try {
	  $client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
	  $params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber);
	  $result = $client->checkVat($params);
	} catch (Exception $e) {
	    $f=fopen("vies.log","a");
	    fwrite($f,date("d/m/Y H:i")." ".$countryCode.$vatNumber." Erreur serveur\n");
	    fclose($f);
	    return false;
	}

	$f=fopen("vies.log","a");
	fwrite($f,$result->requestDate." ".$result->countryCode.$result->vatNumber." ".($result->valid?true:false)." ".$result->name." ".$result->address."\n");
	fclose($f);

	return $result;
}
}

function ajoute_jours_non_livrable($delai, $from=null,$add=null){ // sert à convertir un délai de livraison en jour vers une date de livraison prenant en compte les samedi, dimanches, jours fériés, etc...
	$datelivr=mktime(0,0,0); // aujourd'hui.
	$ferie=array(1=>array(1),2=>array(),3=>array(),4=>array(),5=>array(1,8),6=>array(),7=>array(14),8=>array(15),9=>array(),10=>array(),11=>array(1,11),12=>array(25));
	$paques=easter_date(date("Y"))+86400;
	if($paques<time())
		$paques=easter_date(date("Y")+1)+86400;
	$ferie[date("n",$paques)][]=date("j",$paques);

	$ascension=easter_date(date("Y"))+(39*86400);
	if($ascension<time())
		$ascension=easter_date(date("Y")+1)+(39*86400);
	$ferie[date("n",$ascension)][]=date("j",$ascension);

	$pentecote=easter_date(date("Y"))+(50*86400);
	if($pentecote<time())
		$pentecote=easter_date(date("Y")+1)+(50*86400);
	$ferie[date("n",$pentecote)][]=date("j",$pentecote);
	$for_length = $delai;
	if(!is_null($add)) {
		$delai += round($add/86400);
		$for_length =round($add/86400);
	}
	if($delai<6){
		// on calcule en jours ouvrés.
		if(!is_null($from) && $datelivr<=$from){
			$datelivr=$from;
		}
		$datelivrtmp=$datelivr;
		if(date("H")>=14){ // On ajoute 1 jour si on est après 14h00
			$datelivrtmp+=86400;
		}
		for ($i = 1; $i <= $for_length; $i++) {
			$datelivrtmp+=86400;
			$jourferie=in_array(date("j",$datelivrtmp),$ferie[date("n",$datelivrtmp)]);
			if($jourferie){
				$datelivrtmp+=86400;
			}
			$wday=date("N",$datelivrtmp);
			if($wday==6){
				$datelivrtmp+=(2*86400);
			}
			if($wday==7){
				$datelivrtmp+=86400;
			}
		}
		$datelivr=$datelivrtmp;
	} else {
		// on calcule en jours francs et on fait en sorte de pas livrer le WE ou un jour férié.
		if(!is_null($from) && $datelivr<=$from){
			$datelivrtmp=$from+$add;
		}else{
			$datelivrtmp = mktime(0,0,0);

			$datelivrtmp += $delai * 86400;
		}

		$jourferie=in_array(date("j",$datelivrtmp),$ferie[date("n",$datelivrtmp)]);
		if($jourferie){
			$datelivrtmp+=86400;
		}
		$wday=date("N",$datelivrtmp);
		if($wday==6){ //on saute les WE
			$datelivrtmp+=(2*86400);
		}
		if($wday==7){// le dimanche
			$datelivrtmp+=86400;
		}

		$datelivr=$datelivrtmp;

	}
	return $datelivr;
}


function datelivraison($facturid,$from=null,$add=null){
	global $DB_site;
	$maxdelai=0;
	$requete=$DB_site->query("SELECT a.*,qte FROM lignefacture INNER JOIN article a USING(artid) WHERE factureid=".$facturid);
	while($art=$DB_site->fetch_array($requete)){
	    $d=delailivraison($art,$art['qte']);
	    if($d['delailivraison']>$maxdelai)
			$maxdelai=$d['delailivraison'];
	}
	$datelivr=ajoute_jours_non_livrable($maxdelai, $from,$add);
	return $datelivr;
	
}

function delailivraison($article,$qte=1){
	global $DB_site;
	if(!is_array($article)) $article=$DB_site->query_first("SELECT * FROM article WHERE artid=".intval($article));
	if(intval($article['artid'])==0) return false;

	$req=$DB_site->query("SELECT delailivraison,artid,qte,date_appro FROM `article_lot` inner join article using(artid) WHERE `lotid`=".intval($article['artid']));
	if($DB_site->num_rows($req)>0){
	    $delailivraison=$delaibase=0;
	    $stockitem=false;
	    $date_appro=false;
	    while($lot=$DB_site->fetch_array($req)){
			$si=delailivraison($lot,$qte*$lot['qte']);
			if($delailivraison<$si['delailivraison']){
			    $delailivraison=$si['delailivraison'];
			    $delaibase=$si['base_delai'];
			    $date_appro=$si['date_appro'];
			}
			if($si['stock']!=false) $stockitem=$si;
	    }
	    return array("delailivraison"=>$delailivraison,"stock"=>$stockitem,"date_appro"=>$date_appro,"base_delai"=>$delaibase);
	}

	$stock=new stock($DB_site);
	$si=$stock->in_stock($article['artid'], false);
	$delailivraison=$article["delailivraison"];
	$delaibase=$article["delailivraison"];
	$minlivraison=false;

	if($article['date_appro']>0 && $article['date_appro']>time()){
	    $delailivraison+=round(($article['date_appro']-mktime(0,0,0))/86400);
	    $minlivraison=$article['date_appro'];
	}

	if($si!=false){

	    $noncommande=$si->cmd_non_reserve();
	    $noncommandenb=count($noncommande);
	    
	    if($si->qte-$noncommandenb>=$qte){
            $delailivraison=$article["delailivraison"];
            $minlivraison=false;
	    }else{
            if($si->mini>0){
                $commandes=$si->commandes();
                $minlivraison=false;
                $cmdqte=0;
                foreach($commandes as $cmd){
                    $cmdqte+=$cmd->qte;
                    if((!$minlivraison || $minlivraison>$cmd->date_livraison)) {
                    	$minlivraison=$cmd->date_livraison;
                    	/// tâche 5269 : si les articles ne sont pas rentrés en stock par l'adv le jour prévu de la réappro, décaler le jour de réappro au lendemain sur le site, et ainsi de suite.
                    	if($cmd->date_livraison<time()) {
                    		$minlivraison=strtotime('+1 day', time());
                    	}
                    }
                }
                if(count($commandes)>0 && $si->qte-$noncommandenb+$cmdqte>=$qte && $minlivraison>0){
                    $delailivraison=($minlivraison-time())/86400+$article['delailivraison'];
                }else{
                    $minlivraison=false;
//                     $delaibase=$delailivraison=$si->delai+$article['delailivraison'];
                    $delailivraison+=$si->delai;
                    $delaibase=$delailivraison;
                }
	      	}
	    }
	}
    
	return array("delailivraison"=>round($delailivraison),"stock"=>$si,"date_appro"=>$minlivraison,"base_delai"=>$delaibase);
}

function article_price($artid,$vf=true,$site_code='base',$tp=true){
	 global $DB_site;
	 
	 $article=$DB_site->query_first("SELECT a.artid,a.artcode,a_s.active,a.commandable,a_s.prix,a_s.prixconseilleht FROM article a INNER JOIN article_site a_s USING(artid) WHERE site_code='".$site_code."' AND a.artid=".intval($artid));
	 if($article[0]=="" || $article['active']!=1) return 0;
	 $prixttc=$article['prix'];
	 
	 if($tp){
	 	$temp_price=$DB_site->query_first("SELECT * FROM temp_price WHERE artid=$artid AND site_code='".$site_code."' AND ".mktime(0,0,0)." BETWEEN du AND au");
		 if($temp_price['id']!=""){
		    $article['prix']=$temp_price['prix'];
		    $article['prixconseilleht']=$temp_price['prixconseilleht'];
		    $prixttc=$temp_price['prix'];
		 }	
	 }
	 
	 if($vf){
	    $vfarticle=$DB_site->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND site_code='".$site_code."' AND vfa.artcode='$article[artcode]'");
	    if($vfarticle['id']!="") {
		$prixttc=$vfarticle['prix'];
	    }
	 }
	 
	 return $prixttc;
}

function article_prices($artid,$taux,$site_code='base',$ht=false,$vf=true,$tp=true,$vp=true){
	 global $DB_site;
	 
	 $article=$DB_site->query_first("SELECT a.artid,a.artcode,a_s.active,a.commandable,a.ecopartht,a_s.prix,a_s.prixconseilleht,a_s.tauxchoisi FROM article a INNER JOIN article_site a_s USING(artid) WHERE site_code='".$site_code."' AND a.artid=".intval($artid));
	 if($article[0]=="") return false;
	 //if($article['commandable'] != '1' && !isset($_COOKIE['admin_username'])) return false;
	 $prixttc=$article['prix'];
	 $prixconseilleht=$article['prixconseilleht'];
	 $txtva=1+$taux["taux".$article['tauxchoisi']]/100;
	 
	if($tp){
		$temp_price=$DB_site->query_first("SELECT * FROM temp_price WHERE artid=$artid AND site_code='".$site_code."' AND ".mktime(0,0,0)." BETWEEN du AND au");
		if($temp_price['id']!=""){
			$prixconseilleht=$temp_price['prixconseilleht'];
			$prixttc=$temp_price['prix'];
		}
	}
	 
	 $vfprice=false;
	 if($vf){
	    $vfarticle=$DB_site->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND site_code='".$site_code."' AND vfa.artcode='$article[artcode]'");
	    if($vfarticle['id']!="") {
		  $prixttc=$vfarticle['prix'];
		  $vfprice=true;
	    }
	 }
	 $vpprice=false;
	 if($vp){
	    $vparticle=$DB_site->query_first("SELECT * FROM vente_privee_article vfa INNER JOIN vente_privee vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND site_code='".$site_code."' AND vfa.artcode='$article[artcode]'");
	    if($vparticle['id']!="") {
		  $prixttc=$vparticle['prix'];
		  $vpprice=true;
	    }
	 }

	 $prix=($ht?$prixttc/$txtva:$prixttc);
	 $prixconseille=($ht?$prixconseilleht:$prixconseilleht*$txtva);
	 if($prixconseille==0) $prixconseille=$prix;
	 if($prixconseille==0)
	    $remise=0;
	 else
	    $remise=100-(($prix*100)/$prixconseille);
        $ecopart=($ht?$article['ecopartht']:round($article['ecopartht']*$txtva,2));
	 
	 return array("prix"=>$prix,"prixconseille"=>$prixconseille,"remise"=>$remise,"vf"=>$vfprice,"vp"=>$vpprice,"tauxtva"=>$taux["taux".$article['tauxchoisi']],"ecopart"=>$ecopart);
}

function caract_prices($caractdata,$taux,$ht=false){
	if($ht)
	    return floatval($caractdata/(1+$taux['taux1']/100));
	else
	    return floatval($caractdata);
}

function article_image($artid,$width,$height,$replace=null){
	global $DB_site;
	global $template;
	
	$artid=intval($artid);
	$width=intval($width);
	$height=intval($height);
	
	$srcartid=(is_null($replace)||$replace==0?$artid:$replace);
	$autreimg=$DB_site->query_first("SELECT * FROM articlephoto WHERE artid=".$srcartid." AND visible=1 AND (langue='' OR langue='".$template->langue."')");
	$autre="";
	if($autreimg!="")
	    $autre="_".$autreimg['articlephotoid'];
	elseif(!is_null($replace))
	    $srcartid=$artid;
	return "/images/".$srcartid.$autre."_".$width."_".$height.".jpg" ;
}

if(!function_exists('decimale')){
	function decimale($nb){
	         if(strlen($nb)>2) $nb=substr($nb,0,2);
	         if($nb==0) return "";
	         if(substr($nb,0,1)=="0"){
	             return " ".$nb." ct".($nb>1?"s":"");
	         }else
	             if($nb<10) $nb*=10;

	         return " ".$nb." ct".($nb>1?"s":"");
	}
}

if(!function_exists('unite')){
	function unite($nb,$zero=true){
	         if($nb<17){
	             switch ($nb){
	                    case 0: return ($zero ? "zero" : "");
	                    case 1: return 'un';
	                    case 2: return 'deux';
	                    case 3: return 'trois';
	                    case 4: return 'quatre';
	                    case 5: return 'cinq';
	                    case 6: return 'six';
	                    case 7: return 'sept';
	                    case 8: return 'huit';
	                    case 9: return 'neuf';
	                    case 10: return 'dix';
	                    case 11: return 'onze';
	                    case 12: return 'douze';
	                    case 13: return 'treize';
	                    case 14: return 'quatorze';
	                    case 15: return 'quinze';
	                    case 16: return 'seize';

	             }
	          }else if ($nb<20){
	                return 'dix-'.unite($nb-10);
	          }else{
	               if ($nb%10==0){
	                   switch ($nb){
	                          case 20: return 'vingt';
	                          case 30: return 'trente';
	                          case 40: return 'quarante';
	                          case 50: return 'cinquante';
	                          case 60: return 'soixante';
	                          case 70: return 'soixante-dix';
	                          case 80: return 'quatrevingt';
	                          case 90: return 'quatrevingt-dix';
	                   }
	                }else if ($nb<70){
	                      return unite($nb-$nb%10).' '.unite($nb%10);
	                }else if ($nb<80){
	                      return "soixante ".unite($nb%20);
	                }else{
	                    return "quatrevingt ".unite($nb%20);
	                }
	          }
	}
}

if(!function_exists('centaine')){
	function centaine($nb){
	         if($nb==0) return "";
	         if($nb==1) return "cent";
	         return unite($nb,false)." cent";
	}
}

if(!function_exists('millier')){
	function millier($nb){
	        if($nb==0) return "";
	        if($nb==1) return "mille";
	        if($nb<100) return unite($nb)." mille";
	        return centaine(substr($nb,0,1))." ".unite(substr($nb,1,2),false)." mille";
	        return $nb;
	}
}

if(!function_exists('million')){
	function million($nb){
	        if($nb==0) return "";
	        if($nb==1) return "un million";
	        if($nb<100) return unite($nb)." millions";
	        return centaine(substr($nb,0,1))." ".unite(substr($nb,1,2),false)." millions";
	        return $nb;
	}
}

if(!function_exists('val2str')){
	function val2str($nb){
	         $moins="";
	         if($nb<0) $moins="moins ";
	         $nb=str_replace(",",".",$nb);
	         $nb=abs($nb);
	         $int=(int) abs($nb);
	         $dec=0;
	         if(strpos($nb,".")>-1) $dec=substr($nb,strpos($nb,".")+1);

	         if($nb<100) return $moins.unite($int)." euro".($nb>1?"s":"").decimale($dec);
	         $nblen=strlen($int);
	         if($nb<1000) return $moins.centaine(substr($int,$nblen-3,1))." ".unite(substr($int,$nblen-2,2),false)." euro".($nb>1?"s":"").decimale($dec);
	         if($nb<1000000) return $moins.millier(substr($int,0,$nblen-3))." ".centaine(substr($int,$nblen-3,1))." ".unite(substr($int,$nblen-2,2),false)." euro".($nb>1?"s":"").decimale($dec);
	         return $moins.million(substr($int,0,$nblen-6))." ".millier(substr($int,$nblen-6,$nblen-3))." ".centaine(substr($int,$nblen-3,1))." ".unite(substr($int,$nblen-2,2),false)." euro".($nb>1?"s":"").decimale($dec);
	}
}

function convert_number_to_words($number) {
   
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
   
    if (!is_numeric($number)) {
        return false;
    }
   
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }
   
    $string = $fraction = null;
   
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
   
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }
   
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
   
    return $string;
}

function location($totalht){
	
	switch($totalht){
		
		case $totalht < 300:
			return false;

		case $totalht >= 300 && $totalht <= 1500:
			$pcl = array(
				13 => 0.08358,
				24 => 0.04789,
				36 => 0.03178,
				48 => 0.02493,
				60 => 0.02084
			);
			break;

		case $totalht > 1500 && $totalht <= 3000:
			$pcl = array(
				13 => 0.08279,
				24 => 0.04703,
				36 => 0.03178,
				48 => 0.02493,
				60 => 0.02084
			);
			break;

		case $totalht > 3000 && $totalht <= 7000:
			$pcl = array(
				13 => 0.08199,
				24 => 0.04661,
				36 => 0.03178,
				48 => 0.02493,
				60 => 0.02084
			);
			break;

		case $totalht > 7000 && $totalht <= 15000:
			$pcl = array(
				13 => 0.08160,
				24 => 0.04597,
				36 => 0.03178,
				48 => 0.02493,
				60 => 0.02084
			);
			break;

		case $totalht > 15000 && $totalht <= 100000:
			$pcl = array(
				13 => 0.08120,
				24 => 0.04576,
				36 => 0.03178,
				48 => 0.02493,
				60 => 0.02084
			);
			break;

		case $totalht > 50000:
			return false;
	}

	foreach($pcl as $mens=>$pc) $ret[$mens]=($totalht*$pc);
	
	return $ret;
}

function url2page($url, $pro = false){
	global $DB_site;

	$r=preg_match("/\/art,([0-9]+),([^<]+)\.htm/",$url,$matches);
	if($r==1){
	    $article=$DB_site->query_first("SELECT artid,artcode,artcode_eurocave,libelleFR FROM article WHERE artid=".intval($matches[1]));
	    if($article['artcode']!="") return $article['libelleFR']." ".$article['artcode']." (".$article['artcode_eurocave'].")";
	}

	$r=preg_match("/\/cat,([0-9]+),([^<]+)\.htm/",$url,$matches);
	if($r==1){
	    $cat=$DB_site->query_first("SELECT catid,libelleFR FROM categorie WHERE catid=".intval($matches[1]));
	    if($cat['catid']!="") return $cat['libelleFR'];
	}

	$r=preg_match("/\/([^<]+)\-([0-9]+)\.htm/",$url,$matches);
	if($r==1){
	    $mar=$DB_site->query_first("SELECT marqueid,libelle FROM marque WHERE marqueid=".intval($matches[2]));
	    if($mar['marqueid']!="") return "marque ".$mar['libelle'];
	}

	$r=preg_match("/\/int,([0-9]+),([^<]+)\.[htm|html]/",$url,$matches);
	if($r==1){
	    $int=$DB_site->query_first("SELECT institutionnelid,libelleFR FROM institutionnel WHERE institutionnelid=".intval($matches[1]));
	    if($int['institutionnelid']!="") return $int['libelleFR'];
	}

	$r=preg_match("/\/conseil,([0-9]+),([^<]+)\.[htm|html]/",$url,$matches);
	if($r==1){
		if($pro)
			$tableconseil = "conseil_pro";
		else
			$tableconseil = "conseil_part";

	    $int=$DB_site->query_first("SELECT conseilid,libelleFR FROM $tableconseil WHERE conseilid=".intval($matches[1]));
	    if($int['conseilid']!="") return "conseil ".$int['libelleFR'];
	}

	$r=preg_match("/\/cave\/([^<]+)\.html/",$url,$matches);
	if($r==1){
	    $landing=$DB_site->query_first("SELECT id,libelleFR,type FROM landing WHERE '".$matches[1]."' IN (urlFR,urlEN,urlSP)");
	    if($landing['id']!="") return "landing page ".$landing['type']." ".$landing['libelleFR'];
	}

	$info=parse_url($url);
	switch($info['path']){
	case "/categorie.php":
	    $categorie=new categorie($DB_site);
	    $param=parse_str($info['query']);
	    $ret="";
	    if(isset($catid) && $catid!=""){
		if(isset($subcatids) && $subcatids!="") $catid=$subcatids;
		$req=$DB_site->query("SELECT libelleFR FROM categorie WHERE catid IN (".$catid.")");
		while($cat=$DB_site->fetch_array($req)) $ret.=$cat['libelleFR']." ";
	    }

	    if(isset($marqueids)){
		$req=$DB_site->query("SELECT libelle FROM marque WHERE marqueid IN (".$marqueids.")");
		while($mar=$DB_site->fetch_array($req)) $ret.=$mar['libelle']." ";
	    }
	    if(isset($capaciteids)){
		$ret.="capacité ";
		$lscapacite=explode(",",$capaciteids);
		foreach($lscapacite as $id) $ret.=$categorie->capacites[$id]."-".$categorie->capacites[$id+1]." ";
	    }
	    if(isset($prixids)){
		$ret.="prix ";
		$lsprix=explode(",",$prixids);
		foreach($lsprix as $id) $ret.=$categorie->listeprix[$id]."-".$categorie->listeprix[$id+1]." ";
	    }
	    return $ret;
	break;

	case "/marques-cave-a-vin.html":
	     return "marques";
	break;

	case "/discount-cave-a-vin.html":
	     return "promos";
	break;

	case "/idees_cadeaux.html":
	    return "idées cadeaux";
	break;

	case "/client.htm":
	    return "client";
	break;

	case "/contacter.html":
	    return "contact";
	break;

	case "/cde1.php":
	    return "panier";
	break;

	case "/recapitulatif.html":
	    return "livraison commande";
	break;

	case "/cde25.php":
	     return "récapitulatif commande";
	break;

	case "/cde3.php":
	     return "confirmation commande";
	break;
	}

	return false;
}


function img_in_html($html){
    $match=preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i',$html,$results);
    if($match>0)
	return $results[1];
    
    return false;
}

function presse_txt($html,$len=100) {
    if($html=="") return "";
    $html=preg_replace("/<a.*>(.+)<\/a>/U", "", $html);
    $html=strip_tags($html);
    $html=str_replace("&nbsp;"," ",$html);
    $html=trim($html);
    $html = preg_replace('/\s+?(\S+)?$/', '', substr($html, 0, $len));
    
    return $html."...";
}

function resize_img($source,$destination,$newwidth,$newheight=null) {
    if(!file_exists($source)) return false;
    list($width, $height) = getimagesize($source);
    if(is_null($newheight))
	$newheight = $height * ($newwidth/$width);
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $source = imagecreatefromjpeg($source);
    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    return imagejpeg($thumb,$destination);
}

function maxweight($artid){
    global $DB_site;
    if($artid=="" || $artid==0) return 0;
    $lot=$DB_site->query_first("SELECT MAX(emballage_poids1+emballage_poids2+emballage_poids3+0) as emballage_poids FROM article a INNER JOIN article_lot al USING(artid) WHERE al.lotid=".intval($artid));
    
    if(is_null($lot[0])){
	$article=$DB_site->query_first("SELECT MAX(emballage_poids1+emballage_poids2+emballage_poids3+0) as emballage_poids FROM article WHERE artid=".intval($artid));
	
	return max($article);
    }else
	return max($lot);
	
}


function google_remarketing($prodids,$pagetitle,$totalvalue,$pcat=null,$pname=null,$pvalue=null){
    
    if(is_array($prodids)){
	if(count($prodids)==1)
	  $prod="'".array_shift($prodids)."'";
	else
	  $prod=array_json_encode($prodids);
    }else
	$prod="'".$prodids."'";

    if(is_numeric($totalvalue))
	$totalvaluejs=",\necomm_totalvalue:'".number_format($totalvalue,2,'.','')."'";
	
    
    $pcatjs=$pnamejs=$pvaluejs="";
    if(!is_null($pcat))
	$pcatjs=",\necomm_pcat:".array_json_encode($pcat);

    if(!is_null($pname))
	$pnamejs=",\necomm_pname:".array_json_encode($pname);
	
    if(!is_null($pvalue))
	$pvaluejs=",\necomm_pvalue:".array_json_encode($pvalue);
    
    return "<!-- data layer code -->
    <script type=\"text/javascript\">
    var google_tag_params = {
    ecomm_pagetype: '$pagetitle',
    ecomm_prodid: $prod $pcatjs $pnamejs $pvaluejs $totalvaluejs
    };
    </script>
    <script type=\"text/javascript\">
    dataLayer = [{
    'google_tag_params': window.google_tag_params
    }];
    </script>
    <!-- end data layer code -->";

}

if(!function_exists('array_json_encode')){
	function array_json_encode($array){
	    if(!is_array($array)) $array=array($array);
	    $array=array_slice($array,0,10);
	    $array2=array();
	    foreach($array as $value)
		$array2[]=addslashes(addslashes(stripslashes($value)));

	    return "['".implode("','",$array2)."']";
	}
}
if(!function_exists('in_object_array')){
	function in_object_array($needle, $haystack, $field) {
		$ok=false;
		for ($i=0; $i < count($haystack); $i++) {
	    	if ($needle==$haystack[$i]->$field) {
	    		$ok[]=$i;
	    	}
	    }
	    return $ok;
	}
}

function opengraph($title,$type,$url=null,$image=null) {
    if(is_null($url)) $url="https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $title=html_entity_decode(str_replace("\"","",$title),ENT_COMPAT,"ISO-8859-15");
    $meta=<<<HTML
<meta property="og:title" content="$title">
<meta property="og:type" content="$type">
<meta property="og:url" content="$url">\n
HTML;
    if(!is_null($image)){
	if(substr($image,0,8)!="https://") $image="https://".$_SERVER['HTTP_HOST'].$image;
		$meta.="<meta property=\"og:image\" content=\"$image\">";
    }
    return $meta;
}

if(!function_exists('nice')){
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
}

if(!function_exists('nicex')){
     function nicex($var, $color = 'black'){
          nice($var, $color);
          exit();
     }
}



function Truncate($string, $length, $stopanywhere=false) {
    //truncates a string to a certain char length, stopping on a word if not specified otherwise.
    $string=html_entity_decode($string);
    if (strlen($string) > $length) {
        //limit hit!
        $string = substr($string,0,($length -3));
        if ($stopanywhere) {
            //stop anywhere
            $string .= '...';
        } else{
            //stop on a word.
            $string = substr($string,0,strrpos($string,' ')).'...';
        }
    }
    return $string;
}

/**
 * Liste le nombre de jour fériés d'une année
 *
 * @param  integer $year Année au format YYYY
 *
 * @return array Tableau de timestamps de jours fériés
 */
function getHolidays($year = null){
    
    if ($year === null)
    {
            $year = intval(strftime('%Y'));
    }

    $easterDate = easter_date($year);
    $easterDay = date('j', $easterDate);
    $easterMonth = date('n', $easterDate);
    $easterYear = date('Y', $easterDate);

    $holidays = array(
            // Jours feries fixes
            mktime(0, 0, 0, 1, 1, $year),// 1er janvier
            mktime(0, 0, 0, 5, 1, $year),// Fete du travail
            mktime(0, 0, 0, 5, 8, $year),// Victoire des allies
            mktime(0, 0, 0, 7, 14, $year),// Fete nationale
            mktime(0, 0, 0, 8, 15, $year),// Assomption
            mktime(0, 0, 0, 11, 1, $year),// Toussaint
            mktime(0, 0, 0, 11, 11, $year),// Armistice
            mktime(0, 0, 0, 12, 25, $year),// Noel

            // Jour feries qui dependent de paques
            mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear),// Lundi de paques
            mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),// Ascension
            mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear), // Pentecote
    );

    sort($holidays);

    return $holidays;
}

/**
 * Nombre de jours calendaires entre 2 dates
 *
 * @param  date $firstDate  Date de début d'interval, au format YYYY-mm-jj
 * @param  date $secondDate Date de fin d'interval, au format YYYY-mm-jj
 *
 * @return int Nombre de secondes
 */
function diffCalendar($firstDate, $secondDate){

	$oFirstDate  = new DateTime($firstDate);
	$oSecondDate = new DateTime($secondDate);

	$nb_secondes = $oSecondDate->getTimestamp() - $oFirstDate->getTimestamp();

    return $nb_secondes;
}

/**
 * Nombre de jours ouvrés entre deux dates
 *
 * @param  date $firstDate  Date de début d'interval, au format YYYY-mm-jj
 * @param  date $secondDate Date de fin d'interval, au format YYYY-mm-jj
 *
 * @return int Nombre de jours
 */
function diffWorkDay($firstDate, $secondDate){

    //Pour calculer le nombre de jours ouvres, on calcule le nombre total de jours et on soustrait les jours fériés et les week end.
    $iDiffCalendar = Vars::diffCalendar($firstDate, $secondDate);

	$oFirstDate  = new DateTime($firstDate);
	$oSecondDate = new DateTime($secondDate);

	$iFirstYear  = $oFirstDate->format('Y');
	$iSecondYear = $oSecondDate->format('Y');

    $aHolidays = array();

    /*
     * Si l'interval demande chevauche plusieurs annees
     * on doit avoir les jours feries de toutes ces annees
     */
    for ($iYear = $iFirstYear; $iYear <= $iSecondYear; $iYear++)
    {
            $aHolidays = array_merge(getHolidays($iYear), $aHolidays);
    }

    /*
     * On est oblige de convertir les timestamps en string a cause des decalages horaires.
     */
    $aHolidaysString = array_map(
    	function ($value){
            return strftime('%Y-%m-%d', $value);
    	}
    , $aHolidays);

    for ($i = $oFirstDate->getTimestamp(); $i < $oSecondDate->getTimestamp(); $i += 86400)
    {
            /* Numero du jour de la semaine, de 1 pour lundi a 7 pour dimanche */
            $iDayNum = strftime('%u', $i);

            if (in_array(strftime('%Y-%m-%d', $i), $aHolidaysString) OR $iDayNum == 6 OR $iDayNum == 7)
            {
                    /* Si c'est ferie ou samedi ou dimanche, on soustrait le nombre de secondes dans une journee. */
                    $iDiffCalendar -= 86400;
            }
    }

    return $iDiffCalendar;
}

/**
 * Retourne un nombre de jours en tenant compte des jours ouvrés
 *
 * @param  date $firstDate Date de début du comptage
 * @param  int $nbJours    Nombre de jours calendaires (sans prise en compte des jours fériés & week end)
 *
 * @return int             Nombre de jours ouvrés (avec prise en compte des jours fériés & week end)
 */
function getDelaisOuvres($firstDate, $nbJours){

	$oFirstDate  = new DateTime($firstDate);

	$i              = $nbJours;
	$jour_timestamp = $oFirstDate->getTimestamp();
	$nb             = 0;
	$anneCourante   = '';
	$aHolidays      = array();
    while($i > 0){

		//Récupération de l'année du jour courant
    	$anneeTest = strftime('%Y', $jour_timestamp);

    	//Si année courante différente de la précédente, on récupère les WE et jours fériés de l'année
    	if($anneCourante == '' || $anneCourante != $anneeTest){
			
			$anneCourante = $anneeTest;
			$aHolidays    = array_merge(getHolidays($anneCourante), $aHolidays);

			//On est oblige de convertir les timestamps en string a cause des decalages horaires.
		    $aHolidaysString = array_map(
		    	function ($value){
		            return strftime('%Y-%m-%d', $value);
		    	}
		    , $aHolidays);
    	}

    	//Numéro du jour à tester
    	$iDayNum = strftime('%u', $jour_timestamp);

		if (in_array(strftime('%Y-%m-%d', $jour_timestamp), $aHolidaysString) OR $iDayNum == 6 OR $iDayNum == 7){
            /* Si c'est ferie ou samedi ou dimanche, on ajoute 1 jour supplémentaire. */
            $nbJours++;
        } else {
        	$i--;
        }
		
		$jour_timestamp += 86400;
    }

    return $nbJours;
}

function getFilAriane($rubrique, $params = array(), $complete = true){

	global $DB_site;
	global $template;
	global $aMenu;

	switch($rubrique){

		case 'categorie':

			$oCatMere = new categorie($DB_site, $params['catid'], $template->langue); 
			$aMenu = $oCatMere->getMenus();

			$aNiveau = array(
				array(
					'lien'    => '/',
					'libelle' => 'Accueil'
				),
				array(
					'lien'	  => '/',
					'libelle' => $aMenu[$oCatMere->menu_appartenance]['libelle']
				),
				array(
					'lien'	  => $template->mkurl("categorie", $oCatMere->catid, $oCatMere),
					'libelle' => $oCatMere->libelle
				)
			);
			
			/*if(isset($params['subcatids'])){

				if(!is_numeric($params['subcatids']) && !is_array($params['subcatids'])){
					$params['subcatids'] = explode(',',$params['subcatids']);
				}

				$oCatFille = new categorie($DB_site, $params['subcatids'], $template->langue);

				if($oCatFille->catid != ''){
					$aNiveau[] = array(
						'lien'	  => $template->mkurl("categorie", $oCatFille->catid, $oCatFille),
						'libelle' => $oCatFille->libelle
					);
				}
			} */

			$fil_ariane = '';
			$i = 0;
			foreach($aNiveau as $le_niveau){

				if(!empty($fil_ariane)) $fil_ariane .= ' > ';
				$fil_ariane .= "<span ".($i == 0 ? 'style="margin-left: 0;"' : '')." typeof=\"v:Breadcrumb\"><a href=\"".$le_niveau['lien']."\" rel=\"v:url\" property=\"v:title\">".$le_niveau['libelle']."</a></span>";
				$i++;
			}

			if($complete){
				return '<div class="fil_ariane" xmlns:v="http://rdf.data-vocabulary.org/#">'.$fil_ariane.'</div>';
			} else {
				return $fil_ariane;
			}

			break;

		case 'article':
			
			$fil_ariane = getFilAriane('categorie', $params, false);

			$fil_ariane = $fil_ariane." > <span typeof=\"v:Breadcrumb\">".$params['marque']." ".$params['gamme']." ".$params['ref']."</span>";

			return '<div class="fil_ariane" xmlns:v="http://rdf.data-vocabulary.org/#">'.$fil_ariane.'</div>';

			break;

		case 'contact':

			$fil_ariane = "<span style=\"margin-left: 0;\" typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">Accueil</a></span> > <span typeof=\"v:Breadcrumb\">Contact</span>";

			return '<div class="fil_ariane" xmlns:v="http://rdf.data-vocabulary.org/#">'.$fil_ariane.'</div>';

			break;
	}
}

function encarts($site_code,$langue,$type,$typeid){
	global $DB_site;
	global $template;
	
	$encarts=array();
	$req=$DB_site->query("SELECT encart,position FROM encarts WHERE site_code='".mysqli_real_escape_string($DB_site->link_id,$site_code)."' AND langue='".mysqli_real_escape_string($DB_site->link_id,$langue)."' AND type='".mysqli_real_escape_string($DB_site->link_id,$type)."' AND typeid=".intval($typeid));
	while($enc=$DB_site->fetch_array($req)){
	    $encarts[$enc['position']]=$enc['encart'];
	}
	ksort($encarts);
	return $encarts;
}

if(!function_exists('str_to_noaccent')){
		function str_to_noaccent($str)
		{
			$url = $str;
			$url = preg_replace('#Ç#', 'C', $url);
			$url = preg_replace('#ç#', 'c', $url);
			$url = preg_replace('#è|é|ê|ë#', 'e', $url);
			$url = preg_replace('#È|É|Ê|Ë#', 'E', $url);
			$url = preg_replace('#à|á|â|ã|ä|å#', 'a', $url);
			$url = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url);
			$url = preg_replace('#ì|í|î|ï#', 'i', $url);
			$url = preg_replace('#Ì|Í|Î|Ï#', 'I', $url);
			$url = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url);
			$url = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url);
			$url = preg_replace('#ù|ú|û|ü#', 'u', $url);
			$url = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url);
			$url = preg_replace('#ý|ÿ#', 'y', $url);
			$url = preg_replace('#Ý#', 'Y', $url);
			 
			return ($url);
		}
}

/**
 * Permet de redimensionner un visuel dans un format souhaité, en remplissant le fond d'une couleur spécifié
 * Utilisée en 1ère instance par l'export Rue du Commerce qui impose des visuels en 600x600 mini
 *
 * @param  string $visuel       Chemin absolu vers le visuel à redimensionner
 * @param  string $destinataire Chemin absolu où doit être stocké le visuel
 * @param  array  $aRGBColors   Tableau de codes couleurs avec les indexs 'R','G' et 'B' à renseigner obligatoirement
 * @param  array  $aDimensions  Tableau des dimensions de l'image finale, avec les indexs 'largeur' et 'hauteur' obligatoires
 */
function redim_color_bg($visuel, $destinataire, $aRGBColors, $aDimensions)
{
	$conteneur_largeur = $aDimensions['largeur'];
	$conteneur_hauteur = $aDimensions['hauteur'];

	if(file_exists($visuel))
	{
		$extension = pathinfo($visuel, PATHINFO_EXTENSION);

		//Récupération du visuel à incorporer
		switch($extension)
		{
			case 'jpg':
			case 'jpeg':
				$source = imagecreatefromjpeg($visuel);
				break;
			case 'png':
				$source = imagecreatefrompng($visuel);
				break;
			case 'gif':
				$source = imagecreatefromgif($visuel);
				break;
		}

		//Il faut vérifier que le visuel à incorporer ne dépasse pas les dimensions spécifiées pour l'image de base
		$visuel_largeur = imagesx($source);
		$visuel_hauteur = imagesy($source);
		if($visuel_largeur > $aDimensions['largeur'] || $visuel_hauteur > $aDimensions['hauteur'])
		{
			//On détermine l'orientation du visuel (portrait ou paysage)
			if($visuel_largeur >= $visuel_hauteur) //Paysage ou carré
			{
				$coef_reduc = $visuel_largeur / $visuel_hauteur;
				$largeur = $aDimensions['largeur'];
				$hauteur = round($largeur / $coef_reduc);
			}
			else //Portrait
			{
				$coef_reduc = $visuel_hauteur / $visuel_largeur;
				$hauteur = $aDimensions['hauteur'];
				$largeur = round($hauteur / $coef_reduc);
			}

			//Régénération du visuel source avec les dimensions adaptées
			//Ecrasement des valeurs précédemment établies afin de prendre en compte les nouvelles dimensions
			$source_image = imagecreatefromstring(file_get_contents($visuel));
			$source = imagecreatetruecolor($largeur, $hauteur);
			imagecopyresampled($source, $source_image, 0, 0, 0, 0, $largeur, $hauteur, $visuel_largeur, $visuel_hauteur);
			$visuel_largeur = imagesx($source);
			$visuel_hauteur = imagesy($source);

			imagedestroy($source_image);
		}

		//Calcul des marges a appliquer en haut et en bas (pour qu'elle soit centrée)
		$visuel_marge_largeur = ($conteneur_largeur - $visuel_largeur) / 2;
		$visuel_marge_hauteur = ($conteneur_hauteur - $visuel_hauteur) / 2;

		//Création de l'image de base, avec les dimensions et la couleur de fond souhaitée
		$conteneur  = imagecreatetruecolor($conteneur_largeur, $conteneur_hauteur);
		$couleur_fond = imagecolorallocate($conteneur, $aRGBColors['R'], $aRGBColors['G'], $aRGBColors['B']);
		imagefill($conteneur, 0, 0, $couleur_fond);

		//Insertion du visuel source dans l'image de base
		imagecopymerge($conteneur, $source, $visuel_marge_largeur, $visuel_marge_hauteur, 0, 0, $visuel_largeur, $visuel_hauteur, 100);

		//Enregistrement du visuel final dans le dossier souhaité.
		switch($extension)
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($conteneur, $destinataire);
				break;
			case 'png':
				imagepng($conteneur, $destinataire);
				break;
			case 'gif':
				imagegif($conteneur, $destinataire);
				break;
		}

		imagedestroy($source);
	  	imagedestroy($conteneur);
	}
}

//Permet de vérifier si il y a moins que 4 slider visible sur la page d'accueil du site FR et EN, pro et part
function check_nb_slider_accueil(){
    global $DB_site;
    $date = time();
    $return = '';
    $langues = array('FR');
    //Pour la version part ,pro = 1,  pro = 3 (3 c pro et part) 
    foreach ($langues as $langue) {
        $slider = $DB_site->query_first("SELECT COUNT(id) FROM slider WHERE visible =1 AND ((date_dbt <='".$date."' AND date_fin >='".$date."') OR (date_dbt='0' AND date_fin='0')) AND langue='".$langue."' AND (pro=1 OR pro=3) AND site_code='base'");
        if($slider[0] <4 ) {
            $return .= "<p style=\"color:red;font-size:15px;\">Attention : il y a seulement ".$slider[0]." sliders sur le site ".$langue." particulier !!</p>";
        }
    }
    return $return;
}

//savegarde le mot cle d'une recherche dans la recherche rapide
function saveMotCle($motcle){
	global $DB_site;
	$date = date('Y-m-d');
	$motcle = trim($motcle);
	$encoding = mb_detect_encoding($motcle,'UTF-8',true);
	if(!$encoding){
		$motcle = utf8_encode($motcle);
	}
	$motcle= mysqli_real_escape_string($DB_site->link_id,$motcle);
	$sql="SELECT stat_rid, nb_recherches FROM stat_recherches WHERE mot_cle=\"".$motcle."\" AND date_recherche='".$date."'";
	$result = $DB_site->query_first($sql);
	if($result > 0){
		$DB_site->query("UPDATE stat_recherches SET nb_recherches='".($result['nb_recherches']+1)."' WHERE stat_rid='".$result['stat_rid']."'");
	}else{
		$DB_site->query("INSERT INTO stat_recherches (mot_cle,date_recherche,nb_recherches) VALUES ('".$motcle."', '".$date."', 1)");
	}

}

function tableau_stock_marque_type($id, $lieu){
	global $DB_site;
	$stock=new stock($DB_site);
	$type_array=array();
	$typess=$DB_site->query("SELECT typearticleid, libelle FROM typearticle ORDER BY typearticleid ASC ");
	while($types=$DB_site->fetch_array($typess)){
		$type_array[]=$types[libelle];
	}
	$r=$stock->stockbyqte(array(1,2),$id);
	$tr=array();
	foreach($r as $item){
		$type_article=$DB_site->query_first("SELECT article.typearticleid, typearticle.libelle FROM article INNER JOIN typearticle ON(article.typearticleid=typearticle.typearticleid) WHERE artid='".$item->artid."' ");
		$libelle=$type_article[libelle];
		
		$tr[$item->marque][$libelle][$item->artid]=$item->qte;
	}
	ksort($tr);
	echo "<br><br><h3>".$lieu." :</h3><br>";
	echo "<table id=\"stock_table\">
			<tr>
				<td>&nbsp;</td>";
	foreach ($type_array as $libelle) {
		echo "<td align=\"center\" style=\"padding:3px;\"><strong>".$libelle."</strong></td>";
	}
	echo "	<td><strong>TOTAL</strong></td></tr>";
	$alt=1;
	$totalcol=array();
	$totallieu=0;
	foreach ($tr as $marque => $val) {
		$totalligne=0;
		if($alt==1){$alt=2;}else{$alt=1;}
		echo "<tr>
				<td class=\"td_factures_alt".$alt."\"><strong>".$marque."</strong></td>";
		foreach ($type_array as $libelle) {
			if(is_array($val[$libelle])){
				$val2=$val[$libelle];
				$qte=array_sum($val2); 
			} else {
				$qte=0;
			}
			$totalligne+=$qte;
			$totallieu+=$qte;
			$totalcol[$libelle]+=$qte;
			echo "<td class=\"td_factures_alt".$alt."\" style=\"text-align:right;\">".$qte."</td>";
		}
		echo "<td class=\"td_factures_alt".$alt."\" style=\"text-align:right;\"><strong>".$totalligne."</strong></td>
			</tr>";
	}
	if($alt==1){$alt=2;}else{$alt=1;}
	echo "<tr>
				<td class=\"td_factures_alt".$alt."\" style=\"text-align:right;\"><strong>TOTAL :</strong></td>";
	foreach ($totalcol as $lib => $val) {
		echo "<td class=\"td_factures_alt".$alt."\" style=\"text-align:right;\"><strong>".$val."</strong></td>";
	}
	echo "<td class=\"td_factures_alt".$alt."\" style=\"text-align:right;\"><strong>".$totallieu."</strong></td>
		</tr>";
	echo "</table>";
}

function gac($facture){
	global $DB_site;

	$code_pays = $DB_site->query_first("SELECT diminutif FROM pays WHERE paysid ='".$facture->paysid."' ");
    $code_pays = $code_pays[0];
    $date_liv = date("Y-m-d", $facture->datelivraison);

	if($facture->langue == 'FR')
		$lang = "fr";
	else
		$lang = "en_GB";
	
	$gac = "<script>
            window.renderOptIn = function() {
              window.gapi.load('surveyoptin', function() {
                window.gapi.surveyoptin.render(
                {
                // REQUIRED FIELDS
                \"merchant_id\": 6717956,
                \"order_id\": \"".$facture->facturid."\",
                \"email\": \"".$facture->utilisateur->mail."\",
                \"delivery_country\": \"".$code_pays."\",
                \"estimated_delivery_date\": \"".$date_liv."\",
                });
              });
            }
            </script>"; 
	$gac .=  "<!-- DÉBUT du code de langue Google Avis clients -->
			<script>
  				window.___gcfg = {
    				lang: '".$lang."'
  				};
			</script>
			<!-- FIN du code de langue Google Avis clients -->";
	return $gac;		

}

function parseNumFr($telephone,$DB){
	$telephone = mysqli_real_escape_string($DB->link_id,$telephone);
	$telephone = str_replace(array(" ","-","."),"",$telephone);
    $telephone = substr($telephone, 1);
    $telephone = '+33'.$telephone;
    return $telephone;
}

function redirectionPro($scriptName,$host, $template,$userid=null,$s=null,$prenom=null,$password=null){
	$sites = $template->sites();
    foreach ($sites['base']['hosts'] as $domaine => $version) {
        if($version == $template->langue) {
            $environnement = explode('.', $host);
            if($environnement[0] == 'localhost'){ //LOCAL
            	if($environnement[1] == 'pro'){
            		$redirection = 'http://localhost.'.$domaine;
            	}
            	else{
                	$redirection = 'http://localhost.pro.'.$domaine;
            	}
            }elseif($environnement[0] == 'test'){ // TEST
                $redirection = 'http://testpro.'.$domaine;
            }elseif($environnement[0] == 'testpro'){ // TEST
                $redirection = 'http://test.'.$domaine;
            }elseif($environnement[0] == "dedie"){
                $redirection = 'http://dediepro.'.$domaine;
            }
            elseif($environnement[0] == "dediepro"){
                $redirection = 'http://dedie.'.$domaine;
            }elseif($environnement[0] == "pro"){ // PROD
                $redirection = 'https://'.$domaine;
            }
            else{
            	$redirection = 'https://pro.'.$domaine;
            }
        }
    }

   	$template->redirect($redirection.$scriptName,301,$userid,$s,$prenom,$password);
}

function rewrite_url($raisonsociale,$langue,$scriptName,$template,$host,$dedie=false){
	$sites = $template->sites();
	
	if($raisonsociale != "")
		$pro = true;
	else 
		$pro = false;

    foreach ($sites['base']['hosts'] as $domaine => $version) {
        if($version == $langue) {

            $environnement = explode('.', $host);

            if($environnement[0] == 'localhost'){ //LOCAL
            	if($pro)
                	$url = 'http://localhost.pro.'.$domaine;
                else
                	$url = 'http://localhost.'.$domaine;
            }elseif($environnement[0] == 'test'){ // TEST
            	if($pro)
                	$url = 'http://testpro.'.$domaine;
                else
                	$url = 'http://test.'.$domaine;
            }elseif($environnement[0] == "dedie" && $dedie){//PROD
            	if($pro)
            	    $url = 'http://dediepro.'.$domaine;
            	else
            		$url = 'https://dedie.'.$domaine;
            }elseif($environnement[0] == "fusionmcav"){
            	$url = 'http://fusionmcav.'.$domaine;
            }else{ // PROD
            	if($pro)
                	$url = 'https://pro.'.$domaine;
                else
                	$url = 'https://www.'.$domaine;
            }

        }
    }
    return $url.$scriptName;
}

function signe($number){
	return ( $number > 0 ) ? 1 : ( ( $number < 0 ) ? -1 : 0 ); 
}

function txt_tracking($type,$id,$userid){
	global $DB_site;
	global $template;

	$code = "";
	switch ($type) {

		case 'CLI':
		$code = "#CLI".$id."#" ;
		break;

		case 'CPT':
		$code = "#CPT".$id."-".$userid."#" ;
		break;

		case 'SAV':
		$facturid = $DB_site->query_first("SELECT facturid FROM sav WHERE id='".$id."'");
		$code = "#SAV".$facturid[0]."-".$id."#" ;
		break;

		case 'PJT':
			if($id == 0){
				$code ="";
			}else{
				$code = "#PJT".$id."#" ;
			}
		
		break;

		case 'CDE':
		$code = "#CDE".$id."#" ;
		break;      
	}

// 	$code = $template->translate("/site/nom")." : ".$code;
	return $code;
}

function check_projets_exist($DB,$userid) {

	$projets=$DB->query_first("SELECT projetid FROM projet WHERE userid = ".$userid." AND etatid IN (2, 3, 4, 5, 6, 7, 8)");
	if(!is_null($projets)) {
		return true;
	}else {
		return false;
	}
}

function check_commande_valide($DB, $facture) {

	$valide=$DB->query_first("SELECT valide FROM facture WHERE facturid = ".$facture." AND (valide IN ('1','5','6','7','8') OR (valide = '0' AND moyenid IN (2,4,5,14,17,18,99)))");
	if(!is_null($valide)) {
		return true;
	}
	else {
		return false;
	}
}


/*
 *SI#6289 : Code douanier + Pays e fabricant
 *https://www.mon-espace-cuisson.com/admin/task.php?a=editTask&task=6289
*/  
function verif_export($facture,$template){
    $txtligne="";
    $pays = array(57,181,111);
    if(!in_array($facture->paysid, $pays) || !in_array($facture->utilisateur->paysid, $pays) ){
        foreach ($facture->lignes as $ligne) {
            $txt=$plus="";
            if($ligne->code_douanier == "" || intval($ligne->origine) == 0){
	            if($ligne->code_douanier == "" ){
	                $douane = true;
	                $txt .= "Code douanier";
	            }
	            if(intval($ligne->origine) == 0){
	                if($douane) $plus=" + ";
	                $txt.=$plus."Pays de fabrication";
	                $origine=true;
	            }
	            $txtligne .="\n - ".$ligne->artcode." (".$ligne->ref.") = ".$txt;
	        }
        }
        if($txtligne!=""){
        	$body = "Le client ".$facture->prenom." ".$facture->nom.", vient de passer la commande #CDE".$facture->facturid."# avec une livraison en ".$facture->pays." et une facturation en ".$facture->utilisateur->pays." \net il manque les informations sur les articles suivants :\n".$txtligne;
	        //Envoi du mail
	        $mail =new email();
	        $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
	        $mail->expname      = $template->translate("/site/nom");
	        $mail->destinataire = MAIL_CONTACT;
	        $mail->to           = MAIL_ADV.",webmaster3@mon-espace-cuisson.com";
	        $mail->subject      = "Attention : Commande avec Facturation et/ou livraison Export - Manque Code Douanier et/ou origine de fabrication";
	        $mail->body         = nl2br($body);
	        $mail->sendmail();
        }
        
    }
}

function verif_tva($DB,$user_info,$template,$factureid,$userid=null){
	$tvaResult = false;
	if(is_null($user_info) && !is_null($userid)){
		$user_info=$DB->query_first("SELECT * FROM  utilisateur WHERE userid =".$userid);
	}

	//Vérification de la tva intra si nom de société renseigné
	if($user_info['raisonsociale'] != ""){
	  $sql = "SELECT *";
	  $sql .= " FROM pays";
	  $sql .= " WHERE paysid = ".$user_info['paysid'];
	  $pays = $DB->query_first($sql);

	  if($pays['cee'] == 1){
	    if($user_info['tvaintracom'] != ""){
	      $user_info['tvaintracom'] = str_replace(array(' ', '.', '-', ',', ', '), '', $user_info['tvaintracom']);
	      $result      = checkvatnumber($user_info['tvaintracom']);
	      if($result->valid == false) {
	        //Ne bloque plus si TVA invalide -> envoi mail à Cécile
	        $tvaResult = true;
	        $subject = "Inscription : TVA intra fausse";
	        $body = "Attention lors de son inscription, le client n°<a href='https://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=".$user_info['userid']."'>".$user_info['userid']."</a> a indiqué un numéro de TVA invalide pour la commande n°<a href='https://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$factureid."'>".$factureid."</a>.";
	      }
	    }else{
	      $tvaResult = true;
	      $subject = "Inscription : TVA intra non renseignée #CDE$factureid#";
	      $body = "Attention lors de son inscription, le client n°<a href='https://www.mon-espace-cuisson.com/admin/utilisateur.php?action=editer&userid=".$user_info['userid']."'>".$user_info['userid']."</a> n'a pas indiqué de numéro de TVA. Commande n°<a href='https://www.mon-espace-cuisson.com/admin/facture.php?action=editer&facturid=".$factureid."'>".$factureid."</a>.";
	    }
	  } 
	} 

	if($tvaResult == true){
	  $mail=new email();
	  $mail->destinataire="cecile@mon-espace-cuisson.com";
	  //$mail->destinataire="webmaster3@mon-espace-cuisson.com";
	  $mail->bcc=array("webmaster3@mon-espace-cuisson.com","webmaster4@mon-espace-cuisson.com");
	  $mail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
	  $mail->expname=$template->translate("/site/nom");
	  $mail->subject=$subject;
	  $mail->body=$body;
	  $mail->sendmail();
	}
}

function cookie_rep($cookie){
	$content = explode("&", $cookie);
    $rep = explode("=", $content[2]);
    $rep = $rep[1];
    return $rep;
}

function cookie_repgroup($value,$key){

	$cookie = explode("&", $value);

	switch ($key) {
		case 'group1':
			$group1 = explode("=", $cookie[3]);
			return $group1[1];
			break;
		
		case 'group2':
			$group2 = explode("=", $cookie[4]);
			return $group2[1];
			break;
	}
}
function cookieconsent($cookie){
	$rep = cookie_rep($cookie);
    $group1 = cookie_repgroup($cookie,'group1');
    $group2 = cookie_repgroup($cookie,'group2');
    $cookieconsent = false;

    switch ($rep) {
        case 'all':
            $cookieconsent = true;
            break;
        
        case 'none':
            $cookieconsent = false;
            //suppression des cookies
            if(isset($_COOKIE['_fbp'])) setcookie("_fbp","",time()-84600, "/");
            if(isset($_COOKIE['_ga'])) setcookie("_ga","",time()-84600, "/");
            if(isset($_COOKIE['_gid'])) setcookie("_gid","",time()-84600, "/");
            if(isset($_COOKIE['_uetvid'])) setcookie("_uetvid","",time()-84600, "/");
            if(isset($_COOKIE['_uetcid'])) setcookie("_uetcid","",time()-84600, "/");
            if(isset($_COOKIE['_gcl_au'])) setcookie("_gcl_au","",time()-84600, "/");
            if(isset($_COOKIE['__utma'])) setcookie("__utma","",time()-84600, "/");
            if(isset($_COOKIE['__utmb'])) setcookie("__utmb","",time()-84600, "/");
            if(isset($_COOKIE['__utmc'])) setcookie("__utmc","",time()-84600, "/");
            if(isset($_COOKIE['__utmz'])) setcookie("__utmz","",time()-84600, "/");
            if(isset($_COOKIE['__cfduid'])) setcookie("__cfduid","",time()-84600, "/");
            break;

        case 'param':
            
            if(intval($group1) == 0){
                if(isset($_COOKIE['_ga'])) setcookie("_ga","",time()-84600, "/");
                if(isset($_COOKIE['_gid'])) setcookie("_gid","",time()-84600, "/");
                if(isset($_COOKIE['_uetvid'])) setcookie("_uetvid","",time()-84600, "/");
                if(isset($_COOKIE['_uetcid'])) setcookie("_uetcid","",time()-84600, "/");
                if(isset($_COOKIE['_gcl_au'])) setcookie("_gcl_au","",time()-84600, "/");
                if(isset($_COOKIE['__utma'])) setcookie("__utma","",time()-84600, "/");
                if(isset($_COOKIE['__utmb'])) setcookie("__utmb","",time()-84600, "/");
                if(isset($_COOKIE['__utmc'])) setcookie("__utmc","",time()-84600, "/");
                if(isset($_COOKIE['__utmz'])) setcookie("__utmz","",time()-84600, "/");
                if(isset($_COOKIE['__cfduid'])) setcookie("__cfduid","",time()-84600, "/");
                $group1=false;
            }else{
                $group1=true;
            }

            
            if(intval($group2) == 0){
                if(isset($_COOKIE['_fbp'])) setcookie("_fbp","",time()-84600, "/");
                $group2=false;
            }else{
                $group2=true;
            }
            break;    
    }
    return $cookieconsent;
}

function cookieTemplateAnalitics($template,$cookieconsent){
	$group1 = cookie_repgroup($_COOKIE['cookieconsent'],'group1');
    $group2 = cookie_repgroup($_COOKIE['cookieconsent'],'group2');

	if($cookieconsent){
	   $template->evaltemplate("Analytics","analytics"); 
	}else{
	    if($group1){
	       $template->evaltemplate("Analytics","analytics");
	    }
	    if($group2){
	       //$template->evaltemplate("Pixel_FB", "pixel_fb"); 
	    }
	}
}
function table_typearticle($DB_site,$artid){
	$table = $DB_site->query_first("SELECT bdd_table FROM typearticle INNER JOIN article a USING(typearticleid) WHERE a.artid= '$artid'");
	if($table[0]=='') $table[0]='article_cave'; //defaut si pas encore configuré
	return $table[0];
}

function typearticleid($DB_site,$artid){
	$type = $DB_site->query_first("SELECT typearticleid FROM article WHERE artid='$artid'");
	return $type[0];
}

function champs_typearticle($DB_site, $artid){
	$tab_champs=array();
	$table = table_typearticle($DB_site,$artid);
	$champs = $DB_site->query("SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' AND COLUMN_NAME NOT IN ('artid', 'typearticleid','id') ORDER BY ORDINAL_POSITION");
	while ($champ = $DB_site->fetch_array($champs)) {
		$tab_champs[]="spec.".$champ[0];
	}
	return $tab_champs;
}

