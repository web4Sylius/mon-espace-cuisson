<?php
include("../admin_global.php");
include("adv.class.php");
include("envoi_fournisseur.php");
include("../../class/stock.class.php");
include("../../class/tnt.class.php");
include("../../class/facture.class.php");
require_once(SITE_PATH."/class/fournisseur.class.php");
require_once(SITE_PATH."/configurations/".$host."/transporteurs/tnt/tnt.php");

header("Cache-Control: no-cache");
header("Content-type:text/xml");

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
print "<xml>";
if(isset($_POST['requestid'])) print "<request>".$_POST['requestid']."</request>";

if(isset($_POST['action'])){
	//$_GET['value']=utf8_decode($_GET['value']);
	switch($_POST['action']){

		case "updatecomment":
			$DB_site2->query("INSERT INTO suivi_commande SET type='adv".$_POST['commenttype']."', facturid=".$_POST['facturid'].", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$_POST['advcomment'])."'");
		break;
		
		case "update":

			$adv = new adv($DB_site2,$_POST['advid']); 
			
			if(is_array($_POST['name'])){
				foreach($_POST['name'] as $id=>$name) $adv->save_field($name,$_POST['value'][$id]);
			} else
				$adv->save_field($_POST['name'], $_POST['value']);

			if(isset($_POST['step'])){
				if(strpos($_POST['step'],",")>-1){
					$steps = explode(",",$_POST['step']);
					foreach($steps as $step) $adv->update_step(intval($step));
				} else
					$adv->update_step($_POST['step']);
			}
			
			print "<field name=\"".$_POST['name']."\" value=\"".$_POST['value']."\" utilisateur=\"".$user_info['username']."\" date=\"".date("d/m/Y H:i")."\" />";

			break;

		case "delfield":
			$adv=new adv($DB_site2,$_POST['advid']);
			$adv->del_field($_POST['name']);
			break;
			
		case "ligne":
			$nlignes=array();
			$adv=$DB_site2->query_first("SELECT * FROM adv WHERE id=".$_POST['advid']);
			$lignes=explode(",",$adv['lignes']);
			foreach($lignes as $l){
				if($l!=""){
					$m=preg_match("/(\d+)-(\d+)/",$l,$matches);

					if($matches[2]!=$_POST['ligneid']) $nlignes[]=$l;
				}
			}
			if($_POST['qte']>0) $nlignes[]=$_POST['qte']."-".$_POST['ligneid'];
			$DB_site2->query("UPDATE adv SET lignes='".implode(",",$nlignes)."' WHERE id=".$_POST['advid']);
			$adv=new adv($DB_site2,$_POST['advid']);
			if(isset($_POST['step'])) $adv->update_step($_POST['step']);
			break;

		case "fournisseur":

			$adv = new adv($DB_site2,$_POST['advid']);

			$adv->destinataire = $_POST['destinataire'];
			if(isset($_POST['devise'])) $adv->devise = $_POST['devise'];
			$adv->update();
			
			$sql = "SELECT heure";
			$sql .= " FROM adv_heure";
			$sql .= " WHERE fournisseur = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['destinataire'])."'";
			$h = $DB_site2->query_first($sql);

			print "<fournisseur name=\"".$_POST['destinataire']."\" heure=\"".intval($h[0])."\" />";

		break;

		case "agences":

			$sql = "SELECT *";
			$sql .= " FROM fournisseur";
			$sql .= " INNER JOIN pays ON (pays.paysid = fournisseur.paysid)";
    		$sql .= " WHERE type = 'sls' AND societe = '".$_POST['slsname']."'"; /// ici recup la societe quand on a cliqué sur le btn radio correspondant
    		$requete = $DB_site2->query($sql);
    		if($DB_site2->num_rows($requete)<2) {
    			$adv=new adv($DB_site2,$_POST['advid']);
    			$adv->save_field('name_agence','');
    		}
    		print "<liste liste_societe=\"".$_POST['slsname']."\" liste_ville=\"".$villes."\" liste_fonction=\"".$fonction."\" liste_id=\"".$id."\" id_div=\"".$_POST['iddiv']."\" />";
    		break;

		case "fields":
			$adv=new adv($DB_site2,$_POST['advid']);
			foreach($adv->fields as $name=>$value){
				$value=html_entity_decode($value,ENT_NOQUOTES,"ISO-8859-15");
				$value=str_replace("&","&amp;",$value);
				print "<field name=\"".$name."\" value=\"".$value."\" />";
			}
			foreach($adv->lignes as $lfid=>$qte)
				print "<field name=\"qte_".$lfid."\" value=\"".$qte."\" />";
			break;

		case "reserve":
			$stock=new stock($DB_site2);
			$array_artid=explode(",",$_POST['artid']);
			$array_lieu=explode(",",$_POST['lieuid']);
			$array_qte=explode(",",$_POST['qte']);
			$array_annule=explode(",",$_POST['annule']);
			
			$nb=0;
			foreach($array_artid as $artid){
				
				//annule la réservation des articles dans tous les cas, s'il y a réservation (pour éviter les doublons car ça récupère tous les produits cochés à chaque fois) :
				$items=$stock->search($artid,$array_lieu[$nb],"facture ".$_POST['facturid'],2);
				$newstate=1;
				$dest="";
				$i=0;
				foreach($items as $item){
					$item->dest=$dest;
					$item->state=$newstate;
					$item->update();
					$i++;
					if($array_qte[$nb]==$i) break;
				}
				
				if($array_annule[$nb]=='false'){
					//réserve les articles si produits cochés
					$newstate=2;
					$dest="facture ".$_POST['facturid'];
					$items=$stock->search($artid,$array_lieu[$nb]);
					$i=0;

					foreach($items as $item){
						$item->dest=$dest;
						$item->state=$newstate;
						$item->update();
						$i++;
						if($array_qte[$nb]==$i) break;
					}
				}
				$nb++;
			}
			
			print "<stock facturid=\"".$_POST['facturid']."\" advid=\"".$_POST['advid']."\"></stock>";
			break;

		case "envoi":
			$adv=new adv($DB_site2,$_POST['advid']);
			$adv->envoi=$adv->date2ts($_POST['value']);
			$adv->update();
			$adv->save_field("envoidate",$_POST['value']);
			break;
		
		case "mail":
			include("mail_fourn.php");
			$adv=new adv($DB_site2,$_POST['advid']);
			mail_fourn($adv,true);
			print "<to>".$adv->fields['fournisseur']."</to>";
			break;


		case "transport_tarif":

			$fournisseur_id  = $_POST['fournisseur_id'];
			$transport_poids = $_POST['transport_poids'];
			$transport_type  = $_POST['transport_type'];
			$transport_dep   = $_POST['transport_dep'];
			$transport_pays  = $_POST['transport_pays'];

			$oFournisseur = new fournisseur($DB_site2,'',$fournisseur_id); 

			$params = array('poids' => $transport_poids, 'type' => $transport_type, 'pays' => $transport_pays);
            if(in_array($transport_pays,array(57,181))) $params['departement'] = $transport_dep;
			$tarif = $oFournisseur->getTarifTransport($params);

			echo "<tarif>" . $tarif . "</tarif>";

			break;
			
                case "trspt_infos":
                        $adv=new adv($DB_site2,$_POST['advid']);
                        $coutTrspt=0;
                        
                        switch($adv->destinataire){
                            case "FEDEX Express":
                                $tnt = new tnt($DB_site2, $adv,$username,$pass);
                                $tnt->test_mode = true;
                                $coutTrspt = $tnt->getCout();var_dump($tnt->getError());
                            break;
                        }
                        
                        echo "<tarif>".$coutTrspt."</tarif>";
                break;
	}

}

print "</xml>";

function apercu($destinataire,$subject,$body){
	//$body=html_entity_decode($body,ENT_NOQUOTES,"ISO-8859-15");
	//$body=str_replace("&euro;","&#8364;",$body);
	print "<subject>".$subject."</subject>
	<body><![CDATA[".$body."]]></body>";
}

?>
