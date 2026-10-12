<?php

require_once "admin_global.php";
include "./marge.php";
include "../class/stock.class.php";
include_once "adv/adv.class.php";
include_once "../class/offre_fournisseur.php";

function get_prix_achat_offre_fournisseur($artid, $paht, $date){
	global $DB_site2;
	if(is_null($artid)) return $article_paht = null; //Dans le cas d'un produit sans artid comme la Recuperation de cave...
	$sql="	SELECT foa.offre, foa.value FROM fournisseur_offre AS fo
			INNER JOIN fournisseur_offre_article AS foa ON(fo.id=foa.offreid)
			WHERE foa.artid=".$artid." AND fo.du < '".$date."' AND fo.au > '".$date."' LIMIT 1";
	$offre_fournisseur=$DB_site2->query_first($sql);
	if($offre_fournisseur[offre]!=""){
		if($offre_fournisseur[offre]=="temp"){
			$article_paht=$offre_fournisseur[value];
		}
		if($offre_fournisseur[offre]=="reduc"){
			$article_paht=$paht-$offre_fournisseur[value];
		}
		if($offre_fournisseur[offre]=="pc"){
			$article_paht=$paht-($paht*$offre_fournisseur[value]/100);
		}
	} else {
		$article_paht=$paht;
	}
	return $article_paht;
}
$types=array(
				"fourn"=>"Fournisseur",
				"trspt"=>"Transporteur",
				"sls"=>"Installateur",
				"sfg"=>"Garantie SFG",
				"garantie"=>"Garantie fournisseur");
$icones=array(
				"fourn"=>"cave.png",
				"stock"=>"cave.png",
				"trspt"=>"livraison.png",
				"sls"=>"carton.png",
				"sfg"=>"sfg.png",
				"garantie"=>"sfg.png");

if(isset($_GET['factureid']) && isset($_GET['src'])){
	$tr=array();
	$assoc=array();
	$showbtn=false;
	$facture=$DB_site2->query_first("SELECT * FROM facture WHERE facturid='".$_GET['factureid']."' ");
	if($facture==""){
        $facture=$DB_site2->query_first("SELECT * FROM facture WHERE facturid=(SELECT facturid FROM adv WHERE id=".intval($_GET['factureid']).") ");
	}
	
	if($facture!=""){
        $requete=$DB_site2->query("	SELECT lf.*,efl.*,ef.comment,ef.destinataire,ef.enlevement FROM lignefacture AS lf
                    INNER JOIN envoi_fournisseur_ligne AS efl ON(lf.lignefactureid=efl.ligne_facture_id)
                    INNER JOIN envoi_fournisseur AS ef ON(efl.efid=ef.id)
                    WHERE lf.factureid=".$facture['facturid']." AND ef.destinataire!='0'");
        
        $of=new offre_fournisseur($DB_site2);
        $artids=$of->artids_facture($facture['facturid']);
        $offres=$of->search_artids($artids,null,$facture['heurefacture']);
        $offrepaht=array();
        foreach($offres as $offre){
            if($offre->fournisseur==$_GET['fournisseur'])
                $offrepaht=$offre->paht_articles($artids);
        }
        
        while($ligne=$DB_site2->fetch_array($requete)){
            $sp="site";
            $comment="";
            $showbtn=true;
            $article=$DB_site2->query_first("SELECT * FROM article WHERE artid='".$ligne['artid']."'");
            if($ligne['comment']!=""){
                    $comment="<span title=\"".$ligne['comment']."\" style=\"color:red;font-weight:bold;cursor:help;\">&#9888;</span> ";
            }
            $conditions=$DB_site2->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$ligne['destinataire'])."'");
            $devise=$devises->symbole($conditions['devise']);  
            $pricedetail=$qtedetail=$descdetail=$ecodetail=$infoexp="";
            if($ligne['type']=="fourn"){
                    $advf=new adv($DB_site2);
                    $advf->load_from_efid($ligne['efid']);
                    if(!is_null($advf->id)){
                        
                        $prix_achat_attendu="";
                        if(isset($offrepaht[$ligne['artid']])){
                            $pahtu=reset(array_keys($offrepaht[$ligne['artid']]));
                            if($pahtu!=$ligne['paht']){
                                $prix_achat_attendu="<strong>".$pahtu." ".$devise." à controler</strong>";
                            } else {
                                $prix_achat_attendu=$pahtu." ".$devise;
                            }
                        }
                        /*$prix_achat_attendu=get_prix_achat_offre_fournisseur($article['artid'], $ligne['paht'], $facture['heurefacture']);
                        if($prix_achat_attendu!=$ligne['paht']){
                            $prix_achat_attendu="<strong> !!! ".$prix_achat_attendu." ".$devise." !!!</strong>";
                        } else {
                            $prix_achat_attendu.=" ".$devise;
                        }*/
                        
                        $descdetail.="Produit<br>";
                        $qtedetail.=$ligne['qte']."<br>";
                        $pricedetail.=$advf->fields['price1_'.$ligne['lignefactureid']]." ".$devise."<br>";
                        $ecodetail.=$advf->fields['ecopart_'.$ligne['lignefactureid']]." ".$devise."<br>";
                        if($advf->fields['gar_'.$ligne['lignefactureid']]=="1"){
                            $descdetail.="Garantie constructeur<br>";
                            $qtedetail.=$ligne['qte']."<br>";
                            $pricedetail.=$advf->fields['garval_'.$ligne['lignefactureid']]." ".$devise."<br>";
                            $ecodetail.="<br>";
                        }
                        if(isset($advf->fields['caract_'.$ligne['lignefactureid']])){
                            foreach($advf->fields['caract_'.$ligne['lignefactureid']] as $caractvalid=>$value){
                                $caractval=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
                                $descdetail.=substr($caractval[0],0,40)."<br>";
                                $qtedetail.=$ligne['qte']."<br>";
                                $pricedetail.=$advf->fields['caractval_'.$ligne['lignefactureid']][$caractvalid]." &euro;<br>";
                                $ecodetail.="<br>";
                            }
                        }
                    } else {
                        $prix_achat_attendu="";
                    }
                    // le prix des options est déjà inclu dans le prix de la ligne
                    /*$req=$DB_site2->query("SELECT paht FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']);
                    while($option=$DB_site2->fetch_array($req)){
                        $ligne['paht'] = $ligne['paht'] + $option['paht'];
                    }*/
            }else {
                    $prix_achat_attendu="";
            }
            // Ajout de la condition sur "livraison" car certaines fois la date enlevement n'est pas renseignée (ex: installateur)
            //if($ligne['enlevement']==0 && $ligne['destinataire']!="0") $infoexp="<span style=\"color:red;\">⚠ Produit non expédié</span><br>";
            if($ligne['enlevement']==0 && $ligne['destinataire']!="0"){
                    if($ligne['livraison']==0){
                        $infoexp="<span style=\"color:red;\">⚠ Produit non expédié</span><br>";
                    }
            }
            
            $optavoirqte="";
            for($i=1;$i<=$ligne['qte'];$i++) $optavoirqte.="<option value=\"".$i."\">".$i."</option>";
                // $prix_achat_attendu=get_prix_achat_offre_fournisseur($article['artid'], $ligne['paht'], $facture['heurefacture']); // "ici le PA HT"($ligne['paht'], ;
            $tr[$ligne['type']].="
            <tr".($_GET['fournisseur']==$ligne['destinataire']?" style=\"background-color:#FFAAAA;\"":"").">
                <td>".($ligne['facture']==""?"<input type=\"checkbox\" name=\"chkl[]\" value=\"".$ligne['id']."\" onclick=\"document.getElementById('travoir_".$ligne['id']."').style.display=(this.checked?'table-row':'none');\" />":"Associé à <a href=\"fournisseur.php?action=editer&facturid=".$ligne['facture']."\" target=\"_blank\">".$ligne['facture']."</a>")."</td>
                <td>".$comment.$ligne['artcode']."<br>".$article['artcode_eurocave']."</td>
                <td width=\"300\">$infoexp<span style=\"color:#7777cc;\">".$descdetail."</span>".$ligne['libelle']."</td>
                <td>".$ligne['destinataire']."</td><td><span style=\"color:#7777cc;\">".$qtedetail."</span><input type=\"text\" name=\"qte_".$ligne['id']."\" value=\"".$ligne['qte']."\" size=\"1\"/></td>
                <td align=\"right\" nowrap><span style=\"color:#7777cc;\">".$pricedetail."</span><input type=\"text\" name=\"price_".$ligne['id']."\" value=\"".$ligne['paht']."\" size=\"4\"/>".$devise."</td>
                <td align=\"right\" nowrap><span style=\"color:#7777cc;\">".$ecodetail."</span><input type=\"text\" name=\"ecopart_".$ligne['id']."\" value=\"".$ligne['ecopart']."\" size=\"4\"/></td>
                <td align=\"right\" nowrap>".$prix_achat_attendu."</td>
            </tr>
            <tr id=\"travoir_".$ligne['id']."\" style=\"display:none;\">
                <td></td>
                <td colspan=\"7\">
                <label for=\"createavoir_".$ligne['id']."\"><input type=\"checkbox\" id=\"createavoir_".$ligne['id']."\" value=\"1\" onclick=\"document.getElementById('infoavoir_".$ligne['id']."').style.display=(this.checked?'block':'none')\"> Demande d'avoir</label>
                <div id=\"infoavoir_".$ligne['id']."\" style=\"display:none;\">
                                <input type=\"hidden\" name=\"avoirtype_".$ligne['id']."\" value=\"".$ligne['type']."\">
                    Quantité : <select name=\"avoirqte_".$ligne['id']."\">".$optavoirqte."</select> &nbsp; PU HT réel avec écopart : <input type=\"text\" name=\"avoirprix_".$ligne['id']."\" value=\"0\" size=\"4\">
                    <br>Infos : <textarea name=\"avoirtext_".$ligne['id']."\" rows=\"3\" cols=\"60\"></textarea>
                </div>
                </td>
            </tr>";
        }
        
        $adv=new adv($DB_site2);
        $list=$adv->listadv($_GET['factureid'],"fourn");
        foreach($list as $advf){
            if($advf->efid>0 && floatval($advf->fields['mazetcout'])>0){
                $showbtn=true;
                $ff=$advf->facture_field("mazetcout");
                $tr["fourn"].="
                    <tr".($_GET['fournisseur']==$advf->destinataire?" style=\"background-color:#FFAAAA;\"":"").">
                        <td>".($ff==""?"<input type=\"checkbox\" name=\"chka[]\" value=\"".$advf->id."\">":"Associé à <a href=\"fournisseur.php?action=editer&facturid=".$ff."\" target=\"_blank\">".$ff."</a>")."</td>
                        <td>Transport</td>
                        <td></td>
                        <td><input type=\"hidden\" name=\"field_".$advf->id."\" value=\"mazetcout\"><input type=\"hidden\" name=\"description_".$advf->id."\" value=\"Transport ".$advf->destinataire."\">".$advf->destinataire."</td>
                        <td><input type=\"hidden\" name=\"qte_".$advf->id."\" value=\"1\"></td><td nowrap><input type=\"hidden\" name=\"type_".$advf->id."\" value=\"trspt\"><input type=\"hidden\" name=\"facturid_".$advf->id."\" value=\"".$_GET['factureid']."\"><input type=\"text\" name=\"price_".$advf->id."\" value=\"".$advf->fields['mazetcout']."\" size=\"3\"/>".$devise."</td>
                        <td></td>
                        <td></td>
                    </tr>";
            }
        }
        $requete=$DB_site2->query("	SELECT *,lc.paht AS caract_paht FROM lignefacturecaracteristique AS lc
                                    INNER JOIN lignefacture AS l ON(lc.lignefactureid = l.lignefactureid)
                                    WHERE l.factureid='".$_GET['factureid']."' 
                                    AND (lc.caractvalid =10 OR lc.caractvalid =11) ");
        while($produit=$DB_site2->fetch_array($requete)){
            $showbtn=true;
            $lid=$produit['lignefactureid'];
            $recu=$DB_site2->query_first("SELECT receive, annule FROM sfg WHERE facturid=".intval($_GET['factureid']));
            $ff=$DB_site2->query_first("SELECT facture FROM facture_fournisseur_autre WHERE assoc='sfg".$lid."'");
            $comment=($recu['annule']>0?"<span title=\"Contrat annulé le ".date("d/m/Y",$recu['annule'])."\" style=\"color:red;font-weight:bold;cursor:help;\">&#9888;</span> ":"");
            $tr['sfg'].="
                <tr".($_GET['fournisseur']=="SFG"?" style=\"background-color:#FFAAAA;\"":"").">
                <td>".($ff[0]==""?"<input type=\"checkbox\" name=\"chka[]\" value=\"".$lid."\">":"Associé à <a href=\"fournisseur.php?action=editer&facturid=".$ff[0]."\" target=\"_blank\">".$ff[0]."</a>")."</td>
                    <td>".($recu==""?"<br><span style=\"color:red;font-weight:bold;cursor:help;\" title=\"Contrat pas re&ccirc;u\">â </span><br>":"").$comment.$produit['artcode']."</td>
                    <td>".$produit['libelle_caractval']."</td>
                    <td><input type=\"hidden\" name=\"field_".$lid."\" value=\"SFG\"><input type=\"hidden\" name=\"description_".$lid."\" value=\"".$produit['libelle_caractval']." ".$produit['artcode']."\">SFG</td>
                    <td><input type=\"hidden\" name=\"qte_$lid\" value=\"".$produit['qte']."\">".$produit['qte']."</td>
                    <td nowrap><input type=\"hidden\" name=\"type_".$lid."\" value=\"gar\"><input type=\"hidden\" name=\"facturid_".$lid."\" value=\"".$produit['factureid']."\"><input type=\"text\" name=\"price_".$lid."\" value=\"".$produit['caract_paht']."\" size=\"3\"/>".$devise."</td>
                    <td></td>
                    <td></td>
                </tr>";
        }
        $requete2=$DB_site2->query("	SELECT * FROM mp_order_line AS ol
                                        INNER JOIN mp_envoi_fournisseur_ligne AS efl ON(ol.id=efl.ligne_facture_id)
                                        WHERE efl.facture=''
                                        AND ol.order='".$_GET['factureid']."' ");
        while($ligne=$DB_site2->fetch_array($requete2)){
            $sp="mp";
            $article=$DB_site2->query_first("SELECT * FROM article WHERE artcode='".$ligne['ref_tin']."'");
            $tr[$ligne['type']].="<tr>
                <td>".($ligne['facture']==""?"<input type=\"checkbox\" name=\"chkl[]\" value=\"".$ligne['id']."\"/>":"Associé à <a href=\"fournisseur.php?action=editer&facturid=".$ligne['facture']."\" target=\"_blank\">".$ligne['facture']."</a>")."</td>
                <td>".$ligne['ref_tin']."</td><td>".$article['artcode_eurocave']."</td>
                <td width=\"300\">".$ligne['product_name']."</td>
                <td><input type=\"text\" name=\"qte_".$ligne['id']."\" value=\"".$ligne['qte']."\" size=\"1\"/></td>
                <td><input type=\"text\" name=\"price_".$ligne['id']."\" value=\"".$ligne['paht']."\" size=\"3\"/> ".$devise."</td>
                <td><input type=\"text\" name=\"ecopart_".$ligne['id']."\" value=\"".$ligne['ecopart']."\" size=\"3\"/></td>
                <td align=\"right\" nowrap>".$prix_achat_attendu."</td>
                </tr>";
        }

        print "<table class=\"detail\">
        <thead>
            <tr>
                <td></td>
                <td>Réf</td>
                <td>Description</td>
                <td>Facturation</td>
                <td>Qte</td>
                <td>P.U HT</td>
                <td>dont eco part HT</td>
                <td>prix achat attendu</td>
            </tr>
        </thead>";
        foreach($tr as $type=>$l){
            print "<tr>
                    <td colspan=\"8\" style=\"background-color:#CCC;\"><img src=\"images/".$icones[$type]."\" width=\"20\" align=\"absmiddle\"> ".$types[$type]."</td>
                </tr>".$l;
        }
        print "</table>";
	}
	
	if($showbtn){
        $savcount=$DB_site2->query_first("SELECT COUNT(*) FROM sav WHERE facturid=".$facture['facturid']);
        if($facture['provenance']=="sav" || $savcount[0]>0)
            print "<p style=\"color:red;\"><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i> Cette commande est liée à un SAV, <a href=\"facture.php?action=editer&facturid=".$facture['facturid']."#detail_marge\" target=\"_blank\">vérifier ce qui doit être facturé</a></p>";
		print "<p><input type=\"hidden\" name=\"sp\" value=\"$sp\"/><input type=\"button\" value=\"Associer\" onclick=\"assoc()\"/></p>";
	}else{
		print "<p>Aucun résultat</p>";
	}
//   print "<p><input type=\"button\" value=\"Ajouter une ligne\" onclick=\"createline('".$_GET['factureid']."')\"/></p>";
}

if(isset($_GET['createline'])){
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"facturid\" value=\"".$_GET['facturid']."\"/>
    <input type=\"hidden\" name=\"action\" value=\"createline\"/>";
    $requete=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid='".intval($_GET['createline'])."'");

    print "<table class=\"detail\">
    <thead>
    <tr>
        <td></td>
        <td>Réf. Acilyon</td>
        <td>Réf. fourn</td>
        <td>Description</td>
        <td>Qte</td>
        <td>P.U HT</td>
        <td>dont eco part HT</td>
    </tr>
    </thead>";
    
    while($row=$DB_site2->fetch_array($requete)){
        $lid=$row['lignefactureid'];
        $article=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM article WHERE artcode='".$row['artcode']."'"));
        $requete2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne WHERE ligne_facture_id=".$lid);
        $back=$disabled="";
        if($DB_site2->num_rows($requete2)>0){
            $back=" style=\"background-color:red;\" title=\"Cette ligne existe déjà\"";
        // $disabled=" disabled";
        }
        print "<tr>
        <td$back><input type=\"radio\" name=\"fline\" value=\"".$lid."\"$disabled/></td>
        <td><input type=\"hidden\" name=\"sp_".$lid."\" value=\"site\"/><input type=\"hidden\" name=\"artcode_".$lid."\" value=\"".$row['artcode']."\"/>".$row['artcode']."</td>
        <td>".$article['artcode_eurocave']."</td>
        <td width=\"300\">".$row['libelle']."</td>
        <td><input type=\"text\" name=\"qte_$lid\" value=\"".$row['qte']."\" size=\"1\"/></td>
        <td><input type=\"text\" name=\"price_$lid\" value=\"".$row['prix']."\" size=\"5\"/> &euro;</td>
        <td><input type=\"text\" name=\"ecopart_$lid\" value=\"".$article['ecopartht']."\" size=\"5\"/></td>
        </tr>";
    }
    $requetemp=$DB_site2->query("SELECT * FROM mp_order_line WHERE `order`='".$_GET['createline']."'");
    while($row=$DB_site2->fetch_array($requetemp)){
        $lid=$row['id'];
        $article=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM article WHERE artcode='".$row['ref_tin']."'"));
        $requete2=$DB_site2->query("SELECT * FROM mp_envoi_fournisseur_ligne WHERE ligne_facture_id=".$lid);
        $back=$disabled="";
        if($DB_site2->num_rows($requete2)>0){
            $back=" style=\"background-color:red;\" title=\"Cette ligne existe déjà\"";
        //$disabled=" disabled";
        }
        print "<tr>
        <td$back><input type=\"radio\" name=\"fline\" value=\"".$lid."\"$disabled/></td>
        <td><input type=\"hidden\" name=\"sp_".$lid."\" value=\"mp\"/><input type=\"hidden\" name=\"artcode_".$lid."\" value=\"".$row['ref_tin']."\"/>".$row['ref_tin']."</td>
        <td>".$article['artcode_eurocave']."</td>
        <td width=\"300\">".$row['product_name']."</td>
        <td><input type=\"text\" name=\"qte_$lid\" value=\"1\" size=\"1\"/></td>
        <td><input type=\"text\" name=\"price_$lid\" value=\"".$row['price']."\" size=\"5\"/> &euro;</td>
        <td><input type=\"text\" name=\"ecopart_$lid\" value=\"".$article['ecopartht']."\" size=\"5\"/></td>
        </tr>";
    }
    print "</table>";

    if($DB_site2->num_rows($requete)>0 || $DB_site2->num_rows($requetemp)>0){
        print "<p>Type de fournisseur : <select name=\"type\"><option value=\"fourn\">Fournisseur</option><option value=\"trspt\">Transporteur</option><option value=\"sls\">Installateur</option></select></p>
        <p><input type=\"submit\" value=\"Créer\"/></p>";
    }else
        print "<p>Aucun résultat</p>";
    print "</form>";
}

if(isset($_GET['addline']) || isset($_GET['delline'])){

    $facturid=0;
    $refarticle="";
    $destinataire="";
    $stockcmdid=0;
    $ecopartht=0;
    $eflid=0;

    if($_GET['sp']=="au"){
	$af=$DB_site2->query_first("SELECT * FROM facture_fournisseur_autre WHERE id=".intval($_GET['delline']));
	if(preg_match("/^avoir(\d+)$/",$af['assoc'],$m)) $DB_site2->query("UPDATE avoir_ligne SET date_recu=0 WHERE id=".intval($m[1]));
	$DB_site2->query("DELETE FROM facture_fournisseur_autre WHERE id=".intval($_GET['delline']));
	
    }elseif($_GET['sp']=="stock"){
        $stock=new stock($DB_site2);
        if(isset($_GET['addline'])){
            $cmd=new stock_cmd($DB_site2,$_GET['cmdid']);
            $stockcmdid=$cmd->id;
            $items=$cmd->items();
            if($_GET['type']=="fourn"){
                $destinataire=$cmd->destinataire;
                
                $i=0;
                foreach($items as $item){
                if($item->artid==$_GET['addline'] && $item->facture=="" && $item->state >=0){
                    $i++;
                    if($i>intval($_GET['qte'])) break;
                    $item->paht=floatval($_GET['price']);
                    $item->facture=$_GET['factureid'];
                    $item->update();
                    $refarticle=$item->ref;
                    $ecopartht=$item->ecopartht;
                }
                }
            }
            if($_GET['type']=="trspt"){
                $destinataire=$cmd->transporteur;
                
                $i=0;
                foreach($items as $item){
                if($item->artid==$_GET['addline'] && $item->facture_trpst=="" && $item->trspt!=""){
                    $i++;
                    if($i>intval($_GET['qte'])) break;
                    $item->prix_trspt=floatval($_GET['price']);
                    $item->facture_trspt=$_GET['factureid'];
                    $item->update();
                    $refarticle=$item->ref;
                    $ecopartht=$item->ecopartht;
                }
                }
            }
            /*else{
            $cmd->facturetrspt=$_GET['factureid'];
            $cmd->tariftrspt=$_GET['price'];
            $cmd->update();
            }*/
            
        }
        if(isset($_GET['delline'])){
            $stock->removefacture($_GET['factureid'],$_GET['delline'],$_GET['stock_cmd_id']);
        }
    }else{
        $tbl="envoi_fournisseur_ligne";
        if($_GET['sp']=="mp") $tbl="mp_envoi_fournisseur_ligne";
        if(isset($_GET['addline'])){
            $facture=$DB_site2->query_first("SELECT ef.facturid,ef.destinataire,lf.artid FROM envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON(ef.id=efl.efid) INNER JOIN lignefacture lf ON(lignefactureid=efl.ligne_facture_id) WHERE efl.id=".$_GET['addline']);
            $facturid=$facture['facturid'];
            $destinataire=$facture['destinataire'];
            $eflid=$_GET['addline'];
            
            if($facture['artid']>0){
                $article=$DB_site2->query_first("SELECT artcode_eurocave,ecopartht FROM article WHERE artid=".$facture['artid']);
                $refarticle=$article['artcode_eurocave'];
                $ecopartht=floatval($article['ecopartht']);
            }
            $DB_site2->query("UPDATE $tbl SET facture='".$_GET['factureid']."',paht=".floatval($_GET['price']).", ecopart=".floatval($_GET['ecopart']).", qte=".intval($_GET['qte'])." WHERE id=".$_GET['addline']);
        
        }
        if(isset($_GET['delline'])) $DB_site2->query("UPDATE $tbl SET facture='' WHERE id=".$_GET['delline']);
    }
    
    if(isset($_GET['avoirqte'])){
        $prixavoir=round($_GET['price']+$ecopartht-$_GET['avoirprix'],2);
        $DB_site2->query("INSERT INTO avoir_ligne SET ffid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['factureid'])."', facturid='".$facturid."', eflid=".$eflid.", stock_cmd_id=".$stockcmdid.", destinataire='".mysqli_real_escape_string($DB_site2->link_id,$destinataire)."', article='".mysqli_real_escape_string($DB_site2->link_id,$refarticle)."', paht=".$prixavoir.", ecopart=".$ecopartht.", qte=".intval($_GET['avoirqte']).", description='".mysqli_real_escape_string($DB_site2->link_id,$_GET['avoirtext'])."', type='".mysqli_real_escape_string($DB_site2->link_id,$_GET['avoirtype'])."'");
    }

    tableau();
}

if(isset($_GET['addautre'])){
    $DB_site2->query("INSERT INTO facture_fournisseur_autre SET facturid='".$_GET['facturid']."', facture='".$_GET['factureid']."', type='".$_GET['type']."', prix=".floatval($_GET['price']).", description='".mysqli_real_escape_string($DB_site2->link_id,utf8_decode($_GET['description']))."', assoc='".$_GET['field'].$_GET['addautre']."'");
    tableau();
}

if(isset($_GET['associnfos'])){
    $nbl=0;
    
    $sql="SELECT facturid FROM facture WHERE mp_facturid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['associnfos'])."'";
    $facture=$DB_site2->query_first($sql);
    if($facture!="") $_GET['associnfos']=$facture['facturid'];
    
    $sql="SELECT efl.type,efl.paht,efl.qte,efl.facture,lf.artid,lf.artcode,lf.libelle,ff.fournisseur,ff.devise,lf.factureid
    FROM envoi_fournisseur_ligne efl
    INNER JOIN lignefacture lf ON(ligne_facture_id=lignefactureid)
    INNER JOIN facture_fournisseur ff ON(efl.facture=ff.numero)
    WHERE efl.facture!='' AND lf.factureid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['associnfos'])."'";
    
    $req=$DB_site2->query($sql);
    if($DB_site2->num_rows($req)>0){
        print "<p><img src=\"images/information.jpg\"> Commandes fournisseur associées à ".$_GET['associnfos']."</p>";
        while($efl=$DB_site2->fetch_array($req)){
            $nbl++;
            $infosart=$efl['libelle'];
            if($efl['artid']!=0){
                $article=$DB_site2->query_first("SELECT artcode,artcode_eurocave FROM article WHERE artid=".$efl['artid']);
                $infosart=$article['artcode']." (".$article['artcode_eurocave'].")";
            }
            print "<a href=\"fournisseur.php?action=editer&facturid=".$efl['facture']."\" target=\"_blank\">".$efl['fournisseur']." ".$efl['facture']."</a> : ".$types[$efl['type']]." ".$efl['qte']." ".$infosart." ".$efl['paht'].$devises->symbole($efl['devise'])."<br>";
        }
    }
    
    $cmd=new stock_cmd($DB_site2,$_GET['associnfos']);
    if($cmd->id==$_GET['associnfos']){
        $ff=$cmd->factures();
        if(count($ff)>0){
            print "<p><img src=\"images/information.jpg\"> Commandes fournisseur associées</p>";
            foreach($ff as $type=>$factures){
                $prix="paht";
                if($type=="trspt") $prix="prix_trspt";
                foreach($factures as $facturenb=>$items){
                    $sql="SELECT ff.fournisseur,ff.devise FROM facture_fournisseur ff WHERE numero='".mysqli_real_escape_string($DB_site2->link_id,$facturenb)."'";
                    $factureinfos=$DB_site2->query_first($sql);
                    $nbl++;
                    print "<a href=\"fournisseur.php?action=editer&facturid=".$facturenb."\" target=\"_blank\">".$factureinfos['fournisseur']." ".$facturenb."</a> : ";
                    foreach($items as $item) print $item->qte." ".$item->artcode." (".$item->ref.") ".$item->$prix.$devises->symbole($factureinfos['devise'])."<br>";
                }
            }
        }
    }
    
    $sql="SELECT ffa.*,ff.fournisseur,ff.devise FROM facture_fournisseur_autre ffa INNER JOIN facture_fournisseur ff ON(ffa.facture=ff.numero) WHERE facturid=".mysqli_real_escape_string($DB_site2->link_id,$_GET['associnfos']);
    $req=$DB_site2->query($sql);
    if($DB_site2->num_rows($req)>0){
        print "<p><img src=\"images/information.jpg\"> Autres frais associés à ".$_GET['associnfos']."</p>";
        while($af=$DB_site2->fetch_array($req)){
            $nbl++;
            print "<a href=\"fournisseur.php?action=editer&facturid=".$af['facture']."\" target=\"_blank\">".$af['fournisseur']." ".$af['facture']."</a> : ".$types[$af['type']]." ".$af['description']." ".$af['prix'].$devises->symbole($af['devise'])."<br>";
        }
    }
    
    if($nbl==0){
        print "Aucune association sur cette commande";
    }
    print "<input type=\"hidden\" name=\"sitefacture\" value=\"".$_GET['associnfos']."\">";
}

if(isset($_GET['tableau'])) tableau();

if(isset($_GET['stockassoc'])){
    $stock=new stock($DB_site2);
    $cmds=$stock->stocknonfacture($_GET['stockassoc']);
    ksort($cmds);
    foreach($cmds as $cmd){
	    $items=$cmd->nonfacture();
	    foreach($items as $item){
            $total=$cmd->getitems($item->artid);
            print "<span id=\"selstock".$cmd->id.$item->artid."\" onclick=\"selectstock(".$cmd->id.",".$item->artid.",".$item->qte.",".($item->paht/$item->qte).",".($item->ecopartht/$item->qte).",'fourn','".addslashes(htmlentities($item->comment))."')\">Commande n°".$cmd->id." : ".$item->qte." / ".count($total)." ".$item->ref."</span>";
	    }
    }
    $cmds=$stock->stocktrsptnonfacture($_GET['stockassoc']);
    foreach($cmds as $cmd){
        $cmd->groupitems();
        print "<p>Commande n°".$cmd->id." :";
        foreach($cmd->items as $item){
            print "<span id=\"selstock".$cmd->id.$item->artid."\" onclick=\"selectstock(".$cmd->id.",".$item->artid.",".$item->qte.",".($item->prix_trspt/$item->qte).",0,'trspt')\">".$item->qte." ".$item->artcode." ".number_format($item->prix_trspt,2,',','')." &euro;</span>";
        }
        print "</p>";
    }
    /*foreach($cmds as $cmd){
	  $pahtu=$cmd->tariftrspt;
	  print "<span id=\"selstock".$cmd->id."0\" onclick=\"selectstock(".$cmd->id.",0,0,".$pahtu.")\">Transport commande nÂ°".$cmd->id." ".$cmd->transporteur."</span>";
    }*/
}

if(isset($_GET['avoirlist'])){
    avoirlist($_GET['avoirlist']);
}

function getLundiPrecedent($TimeStampActuel){ // renvoi la date du dernier lundi à 19h.
	if (date('w',$TimeStampActuel)=='0'){
		$joursAsoustraire=6;
	} elseif(date('w',$TimeStampActuel)=='1') {
		if(date("H", $TimeStampActuel)<18){
			$joursAsoustraire=7;
		} else {
			$joursAsoustraire=0;
		}
	} else {
		$joursAsoustraire=date('w',$TimeStampActuel) -1;
	}
	return mktime(19, 0, 0, date("n",$TimeStampActuel) , date("j", $TimeStampActuel) - $joursAsoustraire, date("Y", $TimeStampActuel));
}


function tableau(){
     
    global $DB_site2;
    global $devises;

    $sql = "SELECT *";
    $sql .= " FROM facture_fournisseur";
    $sql .= " WHERE numero = '".$_GET['factureid']."'";
    $facture = $DB_site2->query_first($sql);
    print ""
    . "<table class=\"detail\">"
        . "<thead>"
        . "<tr>"
            . "<td></td>"
            . "<td>Commande client</td>"
            . "<td>Réf. Acilyon</td>"
            . "<td>Réf. fourn</td>"
            . "<td>Description</td>"
            . "<td>Qte</td>"
            . "<td>P.U HT</td>"
            . "<td>Eco part.</td>"
        . "</tr>"
        . "</thead>";
    
    $total = $articles = 0;
    $date_limite_suppression=getLundiPrecedent(time());
    
    $sql = "SELECT efl.id, lf.factureid, lf.artcode, lf.libelle, efl.qte, efl.paht, efl.ecopart, ef.comment";
    $sql .= " FROM envoi_fournisseur_ligne efl, envoi_fournisseur ef, lignefacture lf";
    $sql .= " WHERE lf.lignefactureid = efl.ligne_facture_id";
    $sql .= " AND ef.id = efl.efid";
    $sql .= " AND efl.facture = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['factureid'])."'";
    $requete = $DB_site2->query($sql);
    
    while($assoc = $DB_site2->fetch_array($requete)){
        $sql = "SELECT *";
        $sql .= " FROM article";
        $sql .= " WHERE artcode = '".$assoc['artcode']."'";
        $article = $DB_site2->fetch_array($DB_site2->query($sql));
        
        $comment = "";
        
        if($assoc['comment']!="") $comment = "<p style=\"color:red;\">&#9888; ".$assoc['comment']."</p>";
        
        $texte = ""
        . "<tr>"
            . "<td>";
    //  if($facture['date_saisie']>$date_limite_suppression){
            $texte .="<a href=\"javascript:delline(".$assoc['id'].",'site');\" class=\"del\">X</a>";
    //  } else {
    //	  $texte .="trop tard";
        // }
        $texte .="</td>"
            . "<td>".$assoc['factureid']."</td>"
            . "<td>".$assoc['artcode']."</td>"
            . "<td>".$article['artcode_eurocave']."</td>"
            . "<td>".$assoc['libelle'].$comment."</td>"
            . "<td>".$assoc['qte']."</td>"
            . "<td nowrap>".number_format($assoc['paht'],2,",","")." ".$devises->symbole($facture['devise'])."</td>"
            . "<td nowrap>".number_format($assoc['ecopart'],3,",","")." ".$devises->symbole($facture['devise'])."</td>"
        . "</tr>";
        
        print $texte;
        
        $total    += $assoc['qte'] * ($assoc['paht'] + round($assoc['ecopart'], 3));
        $articles += $assoc['qte'];
    }
    
    $sql = "SELECT efl.id, ol.order, ol.ref_tin, ol.product_name, efl.qte, efl.paht, efl.ecopart";
    $sql .= " FROM mp_envoi_fournisseur_ligne efl, mp_order_line ol";
    $sql .= " WHERE ol.id = efl.ligne_facture_id";
    $sql .= " AND efl.facture = '".mysqli_real_escape_string($DB_site2->link_id,$_GET['factureid'])."'";
    $requete2 = $DB_site2->query($sql);
    
    while($assoc = $DB_site2->fetch_array($requete2)){
        
        $sql = "SELECT *";
        $sql .= " FROM article";
        $sql .= " WHERE artcode = '".$assoc['ref_tin']."'";
        $article = $DB_site2->fetch_array($DB_site2->query($sql));
        
        $texte = ""
        . "<tr>"
            . "<td>";
    //  if($facture['date_saisie']>$date_limite_suppression){
            $texte .="<a href=\"javascript:delline(".$assoc['id'].",'mp');\" class=\"del\">X</a>";
    //  } else {
    //	  $texte .="trop tard";
        // }
        $texte.="</td>"
            . "<td>".$assoc['order']."</td>"
            . "<td>".$assoc['ref_tin']."</td>"
            . "<td>".$article['artcode_eurocave']."</td>"
            . "<td>".$assoc['product_name']."</td>"
            . "<td>".$assoc['qte']."</td>"
            . "<td>".number_format($assoc['paht'],2,",","")." ".$devises->symbole($facture['devise'])."</td>"
            . "<td nowrap>".number_format($assoc['ecopart'],2,",","")." ".$devises->symbole($facture['devise'])."</td>"
        . "</tr>";
        
        print $texte;
        
        $total    += $assoc['qte'] * ($assoc['paht'] + $assoc['ecopart']);
        $articles += $assoc['qte'];
    }
    
    $req=$DB_site2->query("SELECT type,info FROM code_comptable GROUP BY type");
    while($cc=$DB_site2->fetch_array($req))
        $type_compta[$cc['type']]=ucfirst($cc['info']);
    
    $sql = "SELECT *";
    $sql .= " FROM facture_fournisseur_autre";
    $sql .= " WHERE facture = '".$_GET['factureid']."'";
    $requete3 = $DB_site2->query($sql);
    
    while($row = $DB_site2->fetch_array($requete3)){
        $sql = "SELECT 	artcode_eurocave";
        $sql .= " FROM article";
        $sql .= " WHERE artcode = '".$row['artcode']."'";
        $article = $DB_site2->query_first($sql);
        
        if(strlen($row['facturid'])>4){
            $factureMp=$DB_site2->query_first("SELECT mp_facturid FROM facture WHERE facturid='".mysqli_real_escape_string($DB_site2->link_id,$row['facturid'])."'");
            if($factureMp['mp_facturid']!="") $row['facturid'].=sprintf(" <span style=\"color:#808080;\">(%s)</span>",$factureMp['mp_facturid']);
        }
        
        $texte = ""
        . "<tr>"
            . "<td>";
        //  if($facture['date_saisie']>$date_limite_suppression){
                $texte .="<a href=\"javascript:delline(".$row['id'].",'au');\" class=\"del\">X</a>";
        //  } else {
        //	  $texte .="trop tard";
        //  }
        if($row['type'] == 'fourn' || $row['type'] == 'gar')
            $type = "<td colspan=\"1\">".$row['artcode']."</td><td>".$article['artcode_eurocave']."</td>";
        else
            $type = "<td colspan=\"2\">".$type_compta[$row['type']]."</td>"; 

        if($row['facturid']==0)
            $facturid = 'Non associée';
        else
            $facturid = $row['facturid'];

        $texte.="</td>"
                . "<td>".$facturid."</td>"
                .$type
                . "<td width=\"300\"> ".$row['description']."</td>"
                . "<td>".$row['qte']."</td>"
                . "<td nowrap>".number_format($row['prix'],2,",","")." ".$devises->symbole($facture['devise'])."</td><td> ";

        $texte .="</td>"
                . "</tr>";
            
        print $texte;
        
        $total += $row['prix'];
    }

    $stock = new stock($DB_site2);
    
    $cmdlist = $stock->getfacture($_GET['factureid']);
    
    foreach($cmdlist as $cmdid => $items){
        if(is_array($items)){
            foreach($items as $item){
                if($item->facture == $_GET['factureid']){
                        
                        $texte = ""
                        . "<tr>"
                            . "<td>";
                    //  if($facture['date_saisie']>$date_limite_suppression){
                            $texte .="<a href=\"javascript:delline(".$item->artid.",'stock',".$item->stock_cmd_id.");\" class=\"del\">X</a>";
                    //  } else {
                    //	  $texte .="trop tard";
                        // }
                        $texte.="</td>"
                            . "<td>STOCK n°".$item->stock_cmd_id."</td>"
                            . "<td>".$item->artcode."</td>"
                            . "<td>".$item->ref."</td>"
                            . "<td width=\"300\"> ".$item->libelle."</td>"
                            . "<td>".$item->qte."</td>"
                            . "<td align=\"right\" nowrap>".number_format(($item->paht/$item->qte),2)." ".$devises->symbole($facture['devise'])."</td>"
                            . "<td nowrap>".number_format(($item->ecopartht/$item->qte),3)." ".$devises->symbole($facture['devise'])."</td>"
                        . "</tr>";
                        
                        print $texte;
                        
                        $total += $item->paht + $item->ecopartht;
                    
                }
                
                if($item->facture_trspt == $_GET['factureid']){
            
                        $texte = ""
                        . "<tr>"
                            . "<td>";
                        // if($facture['date_saisie']>$date_limite_suppression){
                            $texte .="<a href=\"javascript:delline(".$item->artid.",'stock',".$item->stock_cmd_id.");\" class=\"del\">X</a>";
                        // } else {
                        //  $texte .="trop tard";
                        // }
                        $texte.="</td>"
                            . "<td>STOCK n°".$item->stock_cmd_id."</td>"
                            . "<td>".$item->artcode."</td>"
                            . "<td>".$item->ref."</td>"
                            . "<td width=\"300\"> ".$item->libelle."</td>"
                            . "<td>".$item->qte."</td>"
                            . "<td align=\"right\" nowrap>".number_format(($item->paht/$item->qte),2)." ".$devises->symbole($facture['devise'])."</td>"
                            . "<td nowrap></td>"
                        . "</tr>";
                        
                        print $texte;
                        
                        $total += $item->paht;
                } 		
            }
            
        } elseif(is_a($items,"stock_cmd")){
            
            $texte = ""
            . "<tr>"
                . "<td>";
            // if($facture['date_saisie']>$date_limite_suppression){
                $texte .="<a href=\"javascript:delline(".$item->artid.",'stock');\" class=\"del\">X</a></td>";
        //   } else {
        //	   $texte .="trop tard";
            //  }
            $texte.="</td>"
                . "<td>STOCK n°".$item->id."</td>"
                . "<td></td>"
                . "<td></td>"
                . "<td width=\"300\">Transport ".$item->transporteur."</td>"
                . "<td>1</td>"
                . "<td align=\"right\" nowrap>".number_format($item->tariftrspt,2)." ".$devises->symbole($facture['devise'])."</td>"
                . "<td nowrap></td>"
            . "</tr>";
            
            print $texte;

            $total += $item->tariftrspt;
        }
    }
    
    $totalttc = round($total*(1+$facture['tva']/100),2);
    
    if($DB_site2->num_rows($requete)==0 && $DB_site2->num_rows($requete2)==0 && $DB_site2->num_rows($requete3)==0 && count($items)==0)
        print "<tr><td colspan=\"8\">Aucun produit associé</td></tr>";
    else
        print "<tr><td colspan=\"6\" align=\"right\"><b>Total H.T</b></td><td colspan=\"2\" align=\"right\" style=\"color:".(round($total,2)==round($facture['montant_ht'],2)?"green":"red")."\">".number_format($total,2,".","")." ".$devises->symbole($facture['devise'])."</td></tr>
    <tr><td colspan=\"6\" align=\"right\"><b>Total T.T.C</b></td><td colspan=\"2\" align=\"right\" style=\"color:".($totalttc==$facture['montant_ttc']?"green":"red")."\">".number_format($totalttc,2,".","")." ".$devises->symbole($facture['devise'])."</td></tr>";

    print "</table><a name=\"ancre\"></a>";
}

function avoirlist($fournisseur){
    global $DB_site2;
    global $devises;

    $sql = "SELECT *";
    $sql .= " FROM avoir_ligne";
    $sql .= " WHERE date_recu = 0";
    $sql .= " AND destinataire = '".addslashes($fournisseur)."'";
    $avoirs = $DB_site2->query($sql);
    if($DB_site2->num_rows($avoirs) > 0){
	print "<option></option>";
	while($avoir=$DB_site2->fetch_array($avoirs)){
		$idorigin=($avoir['facturid']!=0?$avoir['facturid']:$avoir['stock_cmd_id']);
		$origintype=$avoir['stock_cmd_id']!=0?"stock ":"";
		print "<option value=\"".$avoir['id']."\" data-desc=\"Avoir ".$avoir['article']."\" data-prix=\"".$avoir['paht']."\" data-qte=\"".$avoir['qte']."\" data-ecopart=\"".$avoir['ecopart']."\" data-facturid=\"".$idorigin."\" data-type=\"".$avoir['type']."\">facture ".$avoir['ffid']." (cmd ".$origintype.$idorigin.") : ".$avoir['qte']." ".$avoir['article']." ".number_format($avoir['paht'],2,',','')." &euro;</option>";
	}
    }
}

?>
