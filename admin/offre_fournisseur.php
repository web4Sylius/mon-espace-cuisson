<?php

require "admin_global.php";

include "./header.php";
include "./menu.php";

include "../class/offre_fournisseur.php";
include "../class/pack.class.php";


if(isset($_POST['offreid'])){
    $offre=new offre_fournisseur($DB_site2);
    $offre->id=$_POST['offreid'];
    $offre->du=(isset($_POST['illimdu']) ? 0 : $_POST['du']);
    $offre->au=(isset($_POST['illimau']) ? 0 : $_POST['au']);
    $offre->fournisseur=$_POST['fournisseur'];
    $_GET['fournisseur']=$_POST['fournisseur'];
    
    foreach($_POST as $key=>$value){
		if(substr($key,0,5)=="artid"){
			$artid=intval(substr($key,6));
			$offre->new_article($value,$_POST['qte_'.$artid],$_POST['offre_'.$artid],$_POST['valeur_'.$artid]);
		}
    }
    $result=$offre->save();
    if($result)
		$msg="L'offre a bien été ".($_POST['offreid']==0?"ajoutée":"modifiée");
    else
		$msg="Une erreur s'est produit lors de l'enregistrement de l'offre";
}


if(isset($_GET['del'])){
    $offre=new offre_fournisseur($DB_site2,$_GET['del']);
    $offre->delete();
    $msg="Cette a été supprimée";
}

if(isset($_POST['offres'])){
    $offre=new offre_fournisseur($DB_site2);
    $offre->id=$_POST['offreid'];
    $offre->du=(isset($_POST['illimdu']) ? 0 : $_POST['du']);
    $offre->au=(isset($_POST['illimau']) ? 0 : $_POST['au']);
    $offre->fournisseur=$_POST['fournisseur'];
    $ok=0;
    if(!isset($_POST['articles_all'])){
    	foreach($_POST['artid'] as $artid){
			$offre->id=0;
			$offre->articles=array();
			$offre->new_article($artid,1,$_POST['offre'],$_POST['valeur']);
			if($offre->save()) $ok++;
	    }
    }else{
    	$sql = "SELECT a.artid";
		$sql .= " FROM article a";
		$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
		$sql .= " INNER JOIN article_fournisseur af ON (af.artid = a.artid)";
		$sql .= " INNER JOIN article_site asite ON (asite.artid = a.artid)";
		$sql .= " WHERE af.fournisseur = '".$_POST['fournisseur']."'";
		$sql .= $_POST['marque'] != '' ? " AND am.marqueid = ".$_POST['marque'] : "";
		$sql .= " AND asite.active = 1";
		$sql .= " GROUP BY a.artid";
		$req = $DB_site2->query($sql);
		while($row = $DB_site2->fetch_array($req)){
			$offre->id=0;
			$offre->articles=array();
			$offre->new_article($row['artid'],1,$_POST['offre'],$_POST['valeur']);
			if($offre->save()){
				$ok++;
			}
		}
    }
    $msg=$ok." offres ajoutées";
}

$offres=new offre_fournisseur($DB_site2);
$optfourn="<option></option>";
foreach($offres->fournisseurs() as $fournisseur){
    $optfourn.="<option value=\"".$fournisseur."\"".(isset($_GET['fournisseur']) && $_GET['fournisseur']==$fournisseur?" selected":"").">".$fournisseur."</option>";
}

$chkexpire=isset($_GET['expire'])?"checked":"";
$encours=true;

print <<<HTML
<h1>Offres achat fournisseur</h1>
<p>$msg</p>
<div>
  <form method="get" name="src">
  Trier par fournisseur : <select name="fournisseur" onchange="document.forms.src.submit()">$optfourn</select> <input type="checkbox" name="expire" value="1" onchange="document.forms.src.submit()" $chkexpire> Offres expirées
  <div class="separateur_vertical"></div>
  <a href="offre_fournisseur.php?add=1"><img src="images/icon/edit_add_22.png" align="absmiddle"> Ajouter une offre</a>
  <div class="separateur_vertical"></div>
  <a href="offre_fournisseur.php?offres=1" style="position:relative;padding-left:25px;"><img src="images/icon/edit_add_22.png" align="absmiddle" style="position:absolute;left:-6px;top:-10px;"><img src="images/icon/edit_add_22.png" align="absmiddle" style="position:absolute;left:6px;top:4px;"> Ajouter des offres similaires</a>
  <div class="separateur_vertical"></div>
  <a href="offre_fournisseur.php?simule=1">Simulateur</a>
  </form>
</div>
HTML;


if(isset($_GET['add']) || isset($_GET['modif'])){
    $encours=false;
    $req=$DB_site2->query("SELECT devise,societe FROM fournisseur_condition WHERE 1");
    $jsdevise="var devises=new Array();\n";
    while($condition=$DB_site2->fetch_array($req)){
		$jsdevise.="devises[\"".$condition['societe']."\"]=\"".$devises->symbole($condition['devise'])."\"\n";
    }
    if(isset($_GET['modif'])){
		$h2="Modifier une offre";
		$submit="Modifier l'offre";
		$offreid=$_GET['modif'];
		$offre=new offre_fournisseur($DB_site2,$offreid);
		$du=$au="";
		$illimau=$illimdu="";
		if($offre->du>0)
			$du=date("d/m/Y",$offre->du);
		else
			$illimdu="checked";
		if($offre->au>0)
			$au=date("d/m/Y",$offre->au);
		else
			$illimau="checked";
		
		$conditions=$DB_site2->query_first("SELECT * FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$offre->fournisseur)."'");
		$symbole=$devises->symbole($conditions['devise']);
    }else{
		$h2="Nouvelle offre";
		$submit="Ajouter l'offre";
		$offreid=0;
		$du=$au="";
		$symbole="";
		$offre=new offre_fournisseur($DB_site2);
    }

    $articles="";
    $artinputs="";
    if(!is_null($offre->articles)){
		foreach($offre->articles as $artid=>$infos){
			$article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$artid);
			$txtreduc="";
			if($infos['offre']=="temp") $txtreduc=" => ".$infos['value'].$symbole;
			if($infos['offre']=="reduc") $txtreduc=" => -".$infos['value'].$symbole;
			if($infos['offre']=="pc") $txtreduc=" => -".$infos['value']."%";
			if($infos['offre']=="offert") $txtreduc=" => ".$infos['value']." offert";
			$articles.="<div id=\"art_".$artid."\" style=\"border:solid 1px #ccc;border-radius:4px;padding:4px;margin:4px;\">".$infos['qte']." ".$article['artcode'].$txtreduc." <img src=\"images/supprimer.gif\" onclick=\"rm_article(".$artid.")\" style=\"cursor:pointer;vertical-align:middle;\"></div>";
			$artinputs.="<input type=\"hidden\" name=\"artid_".$artid."\" value=\"".$artid."\">
			<input type=\"hidden\" name=\"qte_".$artid."\" value=\"".$infos['qte']."\">
			<input type=\"hidden\" name=\"offre_".$artid."\" value=\"".$infos['offre']."\">
			<input type=\"hidden\" name=\"valeur_".$artid."\" value=\"".$infos['value']."\">";
		}
    }

    $optfourn="<option></option>";
    $req=$DB_site2->query("SELECT societe FROM fournisseur WHERE type='fournadv' GROUP BY societe ORDER BY societe");
    while($f=$DB_site2->fetch_array($req)){
		$optfourn.="<option value=\"".$f['societe']."\"".($f['societe']==$offre->fournisseur?" selected":"").">".$f['societe']."</option>";
	}
	/*
    $optqte="";
    for($i=1;$i<=10;$i++) $optqte.="<option value=\"".$i."\">".$i."</option>";
    */
	
    $cbfunction="selectprod";
    
    print <<<HTML
    <h2>$h2</h2>
    <script language="javascript" src="calendrier.js"></script>
    <form name="form" action="offre_fournisseur.php" method="post" onsubmit="return verif_offre();">
    <input type="hidden" name="offreid" value="$offreid">
    <table>
    <tr>
      <td>Fournisseur :</td>
      <td><select name="fournisseur" onchange="document.getElementById('new_article').style.display='none';">$optfourn</select></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Valable du (inclus) :</td>
      <td><input type="text" name="du" id="du" value="$du" onclick="calendrier_show(this.id,this.value)" size="8" autocomplete="off" readonly> <input type="checkbox" name="illimdu" value="1" $illimdu>Illimité</td>
    </tr>
    <tr>
      <td>au (exclus) :</td>
      <td><input type="text" name="au" id="au" value="$au" onclick="calendrier_show(this.id,this.value)" size="8" autocomplete="off" readonly> <input type="checkbox" name="illimau" value="1" $illimau>Illimité</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Articles :</td>
      <td>
	  <span onclick="new_article()" style="cursor:pointer;"><img src="images/icon/edit_add_22.png" align="absmiddle"> Ajouter un article</span>
	  <div id="select_articles">$articles</div>
	  <table id="new_article" style="display:none;border:solid 1px #CCC;margin:5px;">
	    <input type="hidden" id="qtemin" value="1">
	    <tr>
	      <td>Produit :</td>
	      <td>
		<input type="text" id="artcode" onkeyup="srcarticle(this.value)">
		<input type="hidden" id="artid">
		<div id="resultlist"></div>
	      </td>
	    </tr>
	    <tr>
	      <td>Type d'offre :</td>
	      <td>
			<select id="typeoffre" onchange="change_type(this.value)">
				<option value="">Aucune</option>
				<option value="temp">Prix temporaire</option>
				<option value="reduc">Remise en €</option>
				<option value="pc">Remise en %</option>
			</select>
		  </td>
	    </tr>
	    <tr id="tr_valeur">
	      <td>Valeur :</td>
	      <td><input type="text" id="valeur" size="4"><span id="valsign"></span></td>
	    </tr>
	    <tr>
	      <td></td>
	      <td><input type="button" value="Ajouter" onclick="add_article()"></td>
	    </tr>
	  </table>
      </td>
    </tr>
    <tr>
      <td></td>
      <td><a href="offre_fournisseur.php">Annuler</a> <input type="submit" value="$submit"></td>
    </tr>
    </table>
    $artinputs
    </form>
    
HTML;
}

if(isset($_GET['offres'])){
    $encours = false;
    $optfourn = "<option>-- Choisir --</option>";
    
    $sql = "SELECT societe";
    $sql .= " FROM fournisseur";
    $sql .= " WHERE type = 'fournadv'";
    $sql .= " GROUP BY societe";
    $sql .= " ORDER BY societe";
    $req = $DB_site2->query($sql);
    while($f = $DB_site2->fetch_array($req)){
		$optfourn .= "<option value=\"".$f['societe']."\"".($f['societe']==$offre->fournisseur?" selected":"").">".$f['societe']."</option>";
	}

    $sql = "SELECT devise, societe";
    $sql .= " FROM fournisseur_condition";
    $sql .= " WHERE 1";
    $req = $DB_site2->query($sql);

    $jsdevise = "var devises=new Array();\n";

    while($condition = $DB_site2->fetch_array($req)){
		$jsdevise .= "devises[\"".$condition['societe']."\"]=\"".$devises->symbole($condition['devise'])."\"\n";
	}

    $optmarque = "<option value=\"\">-- Toutes --</option>";

    $cbfunction = "addoffres";
    ?>
    <h2>Ajouter des offres similaires</h2>
    <p>Ajoute des offres identiques sur plusieurs articles simultanément</p>
    <script language="javascript" src="calendrier.js"></script>
    <form name="form" action="offre_fournisseur.php" method="post" onsubmit="return verif_offre();">
	    <input type="hidden" name="offres" value="1">
	    <table>
			<tr>
				<td>Fournisseur :</td>
				<td>
					<select name="fournisseur" onchange="upd_select(this.value,'fournisseur-marque','offre_marque');"><?php echo $optfourn; ?></select>
					<!--<select name="fournisseur"><?php echo $optfourn; ?></select>-->
				</td>
			</tr>
			<tr>
				<td>Marque :</td>
				<td><select name="marque" id="offre_marque"><?php echo $optmarque; ?></select></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Valable du (inclus) :</td>
				<td><input type="text" name="du" id="du" value="<?php echo $du; ?>" onclick="calendrier_show(this.id,this.value)" size="8" autocomplete="off" readonly> <input type="checkbox" name="illimdu" value="1" $illimdu>Illimité</td>
			</tr>
			<tr>
				<td>au (exclus) :</td>
				<td><input type="text" name="au" id="au" value="<?php echo $au; ?>" onclick="calendrier_show(this.id,this.value)" size="8" autocomplete="off" readonly> <input type="checkbox" name="illimau" value="1" $illimau>Illimité</td>
			</tr>
			<tr>
				<td>Type d'offre :</td>
				<td><select name="offre" id="typeoffre" onchange="change_type(this.value)"><option value="reduc">Remise en €</option><option value="pc">Remise en %</option></select></td>
			</tr>
			<tr id="tr_valeur">
				<td>Valeur :</td>
				<td><input type="text" name="valeur" id="valeur" size="4"><span id="valsign"></span></td>
			</tr>
			<tr>
				<td>Articles :</td>
				<td>Rechercher : 
					<input type="text" id="artcode" onkeyup="srcarticle(this.value)">
					<div id="resultlist"></div>
					<div id="select_articles"></div>
				</td>
			</tr>
			<tr>
				<td><input type="checkbox" name="articles_all"> Tous les articles</td>
			</tr>
	    </table>
	    <input type="submit" value="Ajouter">
    </form>
    <?php
}

if(isset($_GET['simule'])){
    $encours=false;
    $optqte="";
    for($i=1;$i<=10;$i++){
		$optqte.="<option value=\"".$i."\">".$i."</option>";
	}
    $cbfunction="selectprod";
    $select_articles=$input_articles="";
    if(isset($_POST['simule'])){
		$articles=array();
		foreach($_POST as $key=>$value){
			if(substr($key,0,5)=="artid"){
				$artid=intval(substr($key,6));
				$articles[$artid]=intval($_POST['qte_'.$artid]);
				$article[$artid]=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$artid);
				$select_articles.="<div id=\"art_".$artid."\" style=\"border:solid 1px #ccc;border-radius:4px;padding:4px;margin:4px;\">".$articles[$artid]." ".$article[$artid]['artcode']." <img src=\"images/supprimer.gif\" onclick=\"rm_article(".$artid.")\" style=\"cursor:pointer;vertical-align:middle;\"></div>";
				$input_articles.="<input type=\"hidden\" name=\"artid_".$artid."\" value=\"".$artid."\"><input type=\"hidden\" name=\"qte_".$artid."\" value=\"".$articles[$artid]."\">";
			}
		}
		$offres=new offre_fournisseur($DB_site2);
		$liste=$offres->search_artids($articles);
		if(count($liste)==0){
			$tbloffres="<p>Aucune offre correspondante</p>";
		}else{
			$tbloffres=<<<HTML
			<h3>Offres correspondantes</h3>
			<table class="tableau_pdm">
HTML;
			foreach($liste as $offre){
				$du=($offre->du>0?"du ".date("d/m/Y",$offre->du):"");
				$au=($offre->au>0?"jusqu'au ".date("d/m/Y",$offre->au):"");
				if($du.$au=="") $du="Illimitée";
				$devise=$devises->symbole($offre->reduc_devise());
				$advart=$offre->paht_articles($articles);
				$totalcmd=0;
				
				$tbloffres.=<<<HTML
				<tr class="entete_categorie">
				  <td colspan="5" class="produit_pdm">$offre->nboffre Offre $offre->fournisseur $du $au</td>
				</tr>
HTML;
				foreach($advart as $artid=>$listeprix){
					switch($offre->articles[$artid]['offre']){
						case "temp":
							$txtreduc="";
							$paorigin=$offre->articles[$artid]['paorigin'].$devise;
						break;
						
						case "reduc":
							$txtreduc="-".$offre->articles[$artid]['value'].$devise;
							$paorigin=$offre->articles[$artid]['paorigin'].$devise;
						break;
						
						case "pc":
							$txtreduc="-".$offre->articles[$artid]['value']."%";
							$paorigin=$offre->articles[$artid]['paorigin'].$devise;
						break;
						
						case "offert":
							$txtreduc=$offre->articles[$artid]['value']." offert";
							$paorigin=$offre->articles[$artid]['paorigin'].$devise;
						break;
						
						default:
							$txtreduc="";
							$paorigin="";
					}
					
					foreach($listeprix as $paht=>$qte){
						$totalligne=number_format($qte*$paht,2,',','').$devise;
						$totalcmd+=$qte*$paht;
						$tbloffres.=<<<HTML
						  <tr>
							<td>$qte {$article[$artid][artcode]}</td>
							<td>$txtreduc</td>
							<td align="right">$paht $devise</td>
							<td align="right"><s>$paorigin</s></td>
							<td align="right">$totalligne</td>
						  </tr>
HTML;
						$paorigin="";
						$txtreduc="";
					}
				}
				$totalcmd=number_format($totalcmd,2,',','').$devise;
				$tbloffres.=<<<HTML
				<tr>
				  <td colspan="4"></td>
				  <td align="right"><b>$totalcmd</b></td>
				</tr>
HTML;
			}
			$tbloffres.="</table>";
		}
    }
    
    print <<<HTML
    <h2>Simulation d'achat</h2>
    <form method="post" name="form">
		<input type="hidden" name="simule" value="1">
		<div id="select_articles">$select_articles</div>
		<input type="submit" id="submit" value="Calculer le prix d'achat" style="display:none;">
		<p>Ajouter un article : <input type="hidden" id="qtemin" value="1"> <input type="text" id="artcode" onkeyup="srcarticle(this.value)"> <input type="button" value="OK" onclick="add_simule()"></p>
		<input type="hidden" id="artid">
		<div id="resultlist"></div>
		$input_articles
    </form>
        
    $tbloffres
HTML;
}

if(isset($_GET['fournisseur']) || $encours){

    $packs=new pack($DB_site2);
    $symbole_vente=$devises->symbole($sites['base']['devise']);
    $tauxfrance=$DB_site2->query_first("SELECT 0,TVAtauxnormal as taux1,TVAtauxreduit as taux2 FROM pays WHERE paysid=57");

    if(isset($_GET['fournisseur']) && !empty($_GET['fournisseur']))
	$liste=$offres->search_fournisseur($_GET['fournisseur'],isset($_GET['expire']));
    else
	$liste=$offres->search_active();
    foreach($liste as $offre){
		if($offre->id != ""){
			$artlist=array();
			$offrelist=array();
			$prixlist=array();
			$strikeoutlist=array();
			$liens=array();
			$conditions=$DB_site2->query_first("SELECT * FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$offre->fournisseur)."'");
			foreach($offre->articles as $artid=>$article){
				$art=$DB_site2->query_first("	SELECT artcode,artcode_eurocave,libelleFR,m.libelle AS marque,typearticleid FROM article
												INNER JOIN article_marque am USING(artid)
												INNER JOIN marque m USING(marqueid)
												WHERE artid=".$artid);
				$achat=$DB_site2->query_first("SELECT prix FROM article_fournisseur WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$offre->fournisseur)."' AND artid=".$artid);
				$artlist[]=$article['qte']." ".$art['artcode']." <i>(".$art['artcode_eurocave'].")</i> ".$art['marque'];
				switch($article['offre']){
					case "temp":
						$offrelist[]=$art['artcode']." <i>(".$art['artcode_eurocave'].")</i>";
						$prixlist[]=$article['value'].$devises->symbole($conditions['devise']);
						$strikeoutlist[]=$achat['prix'].$devises->symbole($conditions['devise']);
					break;
					
					case "reduc":
						$offrelist[]="-".$article['value'].$devises->symbole($conditions['devise'])." sur ".$art['artcode'];
						$prixlist[]=($achat['prix']-$article['value']).$devises->symbole($conditions['devise']);
						$strikeoutlist[]=$achat['prix'].$devises->symbole($conditions['devise']);
					break;
					
					case "pc":
						$offrelist[]="-".$article['value']."% sur ".$art['artcode'];
						$prixlist[]=round($achat['prix']*(1-$article['value']/100),2).$devises->symbole($conditions['devise']);
						$strikeoutlist[]=$achat['prix'].$devises->symbole($conditions['devise']);
					break;
					
					case "offert":
						$offrelist[]=$art['artcode']." <i>(".$art['artcode_eurocave'].") ".$article['value']." offert";
						$prixlist[]="0".$devises->symbole($conditions['devise']);
						$strikeoutlist[]=$achat['prix'].$devises->symbole($conditions['devise']);
					break;
					
					case "":
						$offrelist[]="";
						$prixlist[]="";
						$strikeoutlist[]="";
					break;
				}
				if($art['typearticleid']==1){
					$packlist=$packs->contains($artid);
					foreach($packlist as $packid){
						$pack=$packs->getDetails($packid);
						$liens[]="<tr><td><a href=\"ajout_pack.php?artid=".$pack['artid']."\" target=\"_blank\">".$pack['artcode']."</a></td></tr>";
					}
				}
			}
			
			
			$artids=array_keys($offre->articles);
			$nbartids=count($artids);
			if($nbartids==1){
				$article=$DB_site2->query_first("	SELECT artcode,artcode_eurocave,a_s.active,a_s.prix,a_s.tauxchoisi FROM article
													INNER JOIN article_site a_s USING(artid)
													WHERE site_code='base' AND a_s.active IN(0,1) AND artid=".$artids[0]);
				$temp=$DB_site2->query_first("	SELECT prix,prixconseilleht FROM temp_price
												WHERE artid=".$artids[0]."
												AND du=".$offre->du."
												AND au=".$offre->au);
				$txttemp="";
				if($temp!="") $txttemp="<td>".$article['prix'].$symbole_vente."</td><td>=&gt;".$temp['prix'].$symbole_vente."</td><td>".round(-100*(1-$temp['prix']/((1+$tauxfrance['taux'.$article['tauxchoisi']]/100)*$temp['prixconseilleht'])))."%</td>";
				$liens[]="<tr><td><a href=\"ajout_produit.php?artid=".$artids[0]."&action2=prix\" target=\"_blank\" style=\"".($article['active']==0?"color:red;":"")."\">".$article['artcode']."</a></td>".$txttemp."</tr>";
			}
			if($nbartids!=0){
				$reqlots=$DB_site2->query("	SELECT a.artid,artcode,artcode_eurocave,count(lotid) AS count,a_s.active,a_s.prix,a_s.tauxchoisi FROM article_lot al
											INNER JOIN article a ON(a.artid=al.lotid)
											INNER JOIN article_site a_s ON(a_s.artid=a.artid)
											WHERE a_s.site_code='base'
											AND a_s.active IN(0,1)
											AND al.artid IN (".implode(",",$artids).")
											GROUP BY lotid");
				while($article=$DB_site2->fetch_array($reqlots)){
					if($article['count']>=$nbartids){
					$txttemp="";
					$temp=$DB_site2->query_first("SELECT prix,prixconseilleht FROM temp_price
												WHERE artid=".$article['artid']."
												AND du=".$offre->du."
												AND au=".$offre->au);
					if($temp!="") $txttemp="<td>".$article['prix'].$symbole_vente."</td><td>=&gt;".$temp['prix'].$symbole_vente."</td><td>".round(-100*(1-$temp['prix']/((1+$tauxfrance['taux'.$article['tauxchoisi']]/100)*$temp['prixconseilleht'])))."%</td>";
					$liens[]="<tr><td><a href=\"ajout_produit.php?artid=".$article['artid']."&action2=prix\" target=\"_blank\" style=\"".($article['active']==0?"color:red;":"")."\">".$article['artcode']."</a></td>".$txttemp."</tr>";
					}
				}
			} else {
				$txttemp="<td colspan=\"3\">ERREUR SUR CETTE OFFRE : AUCUN ARTICLE !!!!</td>";
				$liens[]="";
			}
			$articles=implode("<br>",$artlist);
			$tdoffres=implode("<br>",$offrelist);
			$tdprix=implode("<br>",$prixlist);
			$tdstrikeout=implode("<br>",$strikeoutlist);
			$tdliens="<table>".implode("",$liens)."</table>";
			$du=$au="-";
			if($offre->du>0){
				$du=date("d/m/Y",$offre->du);
			}
			if($offre->au>0){
				$au=date("d/m/Y",$offre->au);
			}
			
			$bgcolor=($bgcolor=="" ? "#ddd" : "");
			
			$troffres[$offre->fournisseur].=<<<HTML
			<tr style="background:$bgcolor">
			  <td>$articles</td>
			  <td>$tdoffres</td>
			  <td>$tdprix</td>
			  <td style="text-decoration:line-through;">$tdstrikeout</td>
			  <td>$du</td>
			  <td>$au</td>
			  <td><img src="images/icon/edit_pt.png" title="Modifier" onclick="document.location='offre_fournisseur.php?modif=$offre->id'"> <img src="images/icon/cancel.png" width="20" title="Supprimer" onclick="rm_offre($offre->id)"></td>
			  <td>$tdliens</td>
			</tr>
HTML;
		}
    }
    
    if(count($liste)==0)
		$trgroup="<tr><td colspan=\"5\">Aucune offre</td></tr>";
    else{
		ksort($troffres);
		foreach($troffres as $fournisseur=>$trs)
			$trgroup.=<<<HTML
			<tr>
			  <td></td>
			</tr>
			<tr class="entete_categorie">
			  <td colspan="8" class="produit_pdm">$fournisseur</td>
			</tr>
			<tr class="entete_categorie">
			  <td class="produit_pdm">Articles</td>
			  <td class="produit_pdm">Offre</td>
			  <td class="produit_pdm">Prix HT</td>
			  <td class="produit_pdm">Ancien prix HT</td>
			  <td class="produit_pdm">Du<br>(inclus)</td>
			  <td class="produit_pdm">Au<br>(exclus)</td>
			  <td></td>
			  <td></td>
			</tr>
			$trs
HTML;
    }
    print <<<HTML
    <h2>Offres $_GET[fournisseur]</h2>
    <table class="tableau_pdm">
		$trgroup
    </table>
HTML;
}

print <<<HTML
<script language="javascript">
  var cbfunction="$cbfunction";
  function new_article(){
      document.getElementById('new_article').style.display="table";
      document.getElementById('qtemin').value="1";
      document.getElementById('artcode').value="";
      document.getElementById('artid').value="";
      document.getElementById('resultlist').innerHTML="";
      document.getElementById('valeur').value="";
      document.getElementById('typeoffre').options[0].selected=true;
      document.getElementById('tr_valeur').style.display="none";
  }

  function change_type(type){
      switch(type){
	  case "":
	  document.getElementById('tr_valeur').style.display="none";
	  break;

	  case "reduc":
	  case "temp":
	  document.getElementById('tr_valeur').style.display="table-row";
	  document.getElementById('tr_valeur').firstChild.nextSibling.innerHTML="Valeur :";
	  document.getElementById('valsign').innerHTML=devises[document.getElementsByName('fournisseur')[1].value];
	  break;

	  
	  case "pc":
	  document.getElementById('tr_valeur').style.display="table-row";
	  document.getElementById('tr_valeur').firstChild.nextSibling.innerHTML="Valeur :";
	  document.getElementById('valsign').innerHTML="%";
	  break;
	  
	  case "offert":
	  document.getElementById('tr_valeur').style.display="table-row";
	  document.getElementById('tr_valeur').firstChild.nextSibling.innerHTML="Qté offert :";
	  document.getElementById('valsign').innerHTML="";
	  break;
      }
  }
  
  function srcarticle(txt){
      if(txt.length<3) return;
      ajaxrequest('marketplace/src.php?txt='+txt+'&lot=0',srccb);
      document.getElementById('resultlist').innerHTML="<img src=\"../images/templates/wait.gif\">";
  }
  
  function srccb(httprequest){
        var xml=httprequest.responseXML.documentElement;
	var html="";
	if(xml.getElementsByTagName('prod')[0]){
	    html+="<table width='100%' style='border:solid thin #808080;'>";
	    for(i=0;i<xml.getElementsByTagName('prod').length;i++){
		  var description=xml.getElementsByTagName('prod')[i].firstChild.nodeValue;
		  var id=xml.getElementsByTagName('prod')[i].getAttribute('id');
		  var ref=xml.getElementsByTagName('prod')[i].getAttribute('ref');
		  var price=xml.getElementsByTagName('prod')[i].getAttribute('price');
		  var buyprice=xml.getElementsByTagName('prod')[i].getAttribute('paht');
		  var artid=xml.getElementsByTagName('prod')[i].getAttribute('artid');
		  var active=xml.getElementsByTagName('prod')[i].getAttribute('active');
		  color='black';
		  if(active=='0' || active=='2') color='red';
		  html+="<tr><td style='border-bottom:solid thin #cccccc;color:"+color+";'>"+id+"<br>"+ref+"</td><td style='border-bottom:solid thin #cccccc;'>"+description+"</span></td><td style='border-bottom:solid thin #cccccc;'><a href=\"javascript:"+cbfunction+"('"+artid+"','"+id+" "+ref+"')\">Sélectionner</a></td></tr>";
	    }
	    html+="</table>";
	}else
	    html="Pas de résultat";

      document.getElementById('resultlist').innerHTML=html;
      document.getElementById('resultlist').style.display='block';
  }
  
  function selectprod(artid,artcode){
      document.getElementById('artcode').value=artcode;
      document.getElementById('artid').value=artid;
      document.getElementById('resultlist').innerHTML="";
  }
  
  function add_article(){
      var artid=document.getElementById('artid').value;
      
      if(artid==""){
	  alert("Aucun article n'est sélectionné");
	  return;
      }
      
      if(document.getElementById('tr_valeur').style.display=="table-row" && document.getElementById('valeur').value==""){
	  alert("Il faut donner le montant de l'offre");
	  return;
      }
      
      if(document.getElementsByName('artid_'+artid).length>0){
	  alert("Ce produit est déjà présent dans l'offre");
	  return;
      }
  
      var artidinp=document.createElement("input");
      artidinp.type="hidden";
      artidinp.name="artid_"+artid;
      artidinp.value=artid;
      document.forms.form.appendChild(artidinp);
      
      var qte=document.createElement("input");
      qte.type="hidden";
      qte.name="qte_"+artid;
      qte.value=document.getElementById('qtemin').value;
      document.forms.form.appendChild(qte);
      
      var offre=document.createElement("input");
      offre.type="hidden";
      offre.name="offre_"+artid;
      offre.value=document.getElementById('typeoffre').value;
      document.forms.form.appendChild(offre);
      
      var valeur=document.createElement("input");
      valeur.type="hidden";
      valeur.name="valeur_"+artid;
      valeur.value=document.getElementById('valeur').value;
      document.forms.form.appendChild(valeur);
      
      var txtreduc="";
      if(offre.value=="reduc") txtreduc=" => -"+valeur.value+"&euro;";
      if(offre.value=="pc") txtreduc=" => -"+valeur.value+"%";
      if(offre.value=="offert") txtreduc=" => offert";
      
      var div=document.createElement('div');
      div.id="art_"+artid;
      div.style.borderRadius="4px";
      div.style.border="solid 1px #ccc";
      div.style.padding="4px";
      div.style.margin="4px";
      div.innerHTML=document.getElementById('artcode').value+txtreduc+" <img src=\"images/supprimer.gif\" onclick=\"rm_article("+artid+")\" style=\"cursor:pointer;vertical-align:middle;\">";
      document.getElementById('select_articles').appendChild(div);
      
      document.getElementById('new_article').style.display="none";
   }
   
   function rm_article(artid){
      if(confirm("Etes vous sur de vouloir enlever cet article ?")){
	  var artidinp=document.getElementsByName('artid_'+artid)[0];
	  if(artidinp!=undefined) document.forms.form.removeChild(artidinp);
	  var qte=document.getElementsByName('qte_'+artid)[0];
	  if(qte!=undefined) document.forms.form.removeChild(qte);
	  var offre=document.getElementsByName('offre_'+artid)[0];
	  if(offre!=undefined) document.forms.form.removeChild(offre);
	  var valeur=document.getElementsByName('valeur_'+artid)[0];
	  if(valeur!=undefined) document.forms.form.removeChild(valeur);
	  var div=document.getElementById('art_'+artid);
	  document.getElementById('select_articles').removeChild(div);
	  
      }
   }
   
   function addoffres(artid,artcode){
      var artids=document.getElementsByName('artid[]');
      for(i=0;i<artids.length;i++){
	  if(artids[i].value==artid){
	      alert("Ce produit est déjà dans la liste");
	      return;
	  }
      }
      
      var div=document.createElement('div');
      div.id="art_"+artid;
      div.style.borderRadius="4px";
      div.style.border="solid 1px #ccc";
      div.style.padding="4px";
      div.style.margin="4px";
      div.innerHTML=artcode+" <img src=\"images/supprimer.gif\" onclick=\"rm_article("+artid+")\" style=\"cursor:pointer;vertical-align:middle;\"><input type=\"hidden\" name=\"artid[]\" value=\""+artid+"\">";
      document.getElementById('select_articles').appendChild(div);
      document.getElementById('resultlist').style.display='none';
   }
   
   function verif_offre(){
      if(document.getElementsByName('fournisseur')[1].value==""){
	  alert("Il faut sélectionner un fournisseur");
	  return false;
      }
      
      if(document.getElementsByName('du')[0].value=="")
	  document.getElementsByName('illimdu')[0].checked=true;
	  
      if(document.getElementsByName('au')[0].value=="")
	  document.getElementsByName('illimau')[0].checked=true;

      if(document.getElementById('select_articles').innerHTML=="" && document.getElementsByName('articles_all')[0].checked == false){
	  alert("Aucun article n'est sélectionné");
	  return false;
      }
      return true;
   }
   
   function rm_offre(offreid){
      if(confirm("Etes vous sur de vouloir supprimer cette offre ?")){
	  var query=document.location.search;
	  var vars=query.substr(1).split("&");
	  for(i=0;i<vars.length;i++){
	      var pair=vars[i].split("=");
	      if(pair[0]=="fournisseur") var fournisseur=pair[1];
	  }
	  document.location="offre_fournisseur.php?del="+offreid+(fournisseur!=undefined?"&fournisseur="+fournisseur:"");
      }
   }
   
   function add_simule(){
      var artid=document.getElementById('artid').value;
      if(artid==""){
	  alert("Il faut sélectioner un produit");
	  return;
      }
   
      var qte=document.createElement("input");
      qte.type="hidden";
      qte.name="qte_"+artid;
      qte.value=document.getElementById('qtemin').value;
      document.forms.form.appendChild(qte);
      
      var artidinp=document.createElement("input");
      artidinp.type="hidden";
      artidinp.name="artid_"+artid;
      artidinp.value=artid;
      document.forms.form.appendChild(artidinp);
      
      var div=document.createElement('div');
      div.id="art_"+artid;
      div.style.borderRadius="4px";
      div.style.border="solid 1px #ccc";
      div.style.padding="4px";
      div.style.margin="4px";
      div.innerHTML=qte.value+" "+document.getElementById('artcode').value+" <img src=\"images/supprimer.gif\" onclick=\"rm_article("+artid+")\" style=\"cursor:pointer;vertical-align:middle;\">";
      document.getElementById('select_articles').appendChild(div);
      
      document.getElementById('artid').value="";
      document.getElementById('artcode').value="";
      document.getElementById('submit').style.display="inline";
   }
   
   $jsdevise
</script>
HTML;

?>