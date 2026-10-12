<?php

$rootpath="/homez.368/macaveav/www/admin/";

$preview=isset($_GET['preview']);

$slsemails=array("0"=>"",
  "acilyon"=>"pr@mon-espace-cuisson.com",
  "slsblois"=>"dsaintluc@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsbordeaux"=>"ldelteil@s-l-s.fr;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsbordeaux2"=>"ldelteil@s-l-s.fr;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsclermont"=>"dtrawinski@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slslille"=>"asalhi@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slslyon"=>"dtrawinski@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slslyon2"=>"dtrawinski@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsmetz"=>"asalhi@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsmetz2"=>"asalhi@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsmetz3"=>"asalhi@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsmiramas"=>"jmaidoun@s-l-s.fr;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsmiramas2"=>"jmaidoun@s-l-s.fr;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsrennes"=>"rdavy@s-l-s.fr;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slstoulouse"=>"smastellone@s-l-s.fr;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slstoulouse2"=>"vroucairol@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu",
  "slsvillabe"=>"pmur@sls-gci.eu;earchidec@sls-gci.eu;bbauer@sls-gci.eu;jlamart@sls-gci.eu");

$slsagences=array("acilyon"=>"Lyon","slsblois"=>"Blois","slsbordeaux"=>"Bordeaux","slsbordeaux2"=>"Bordeaux Crossdock","slsclermont"=>"Clermont","slslille"=>"Lille","slslyon"=>"Lyon","slslyon2"=>"Lyon Crossdock","slsmetz"=>"Metz","slsmetz2"=>"Metz Crossdock","slsmetz3"=>"Metz Crossdock","slsmiramas"=>"Miramas","slsmiramas2"=>"Miramas Crossdock","slsrennes"=>"Rennes","slstoulouse"=>"Toulouse","slstoulouse2"=>"Toulouse Crossdock","slsvillabe"=>"Villabe");


if($preview){
  require_once $rootpath."admin_global.php";
  require_once "order.php";
  $mporder=new MP_Order(0,$DB_site2);  
  $mporder->load($_GET['facturid']);
}

$orderlines=$mporder->list_lines();

$pays=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM pays WHERE paysid='$mporder->shipping_country'"));

//print_r($_POST);
    // enregistrement suivi commande
if($action=="advsuivi"){
     if(!$preview) $DB_site2->query("UPDATE mp_orders SET suivi2='".$_POST['suivitxt']."' WHERE id='".$mporder->id."'");
     $action="editer";
}

    // envoi commande au fournisseur
if($action=="advfournisseur" || $action=="advfournisseur2"){
    $subject=""; 
    switch($_POST['mailsubject']){
         case "cmd":
         $subject="Commande n�".$mporder->id;
         break;
         case "other":
         $subject=$_POST['othershbject'];
         break;
    }
    $text="";
    switch($_POST['intro']){
         case "bonjour":
         $text.="Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande n� ".$mporder->id;
         break;
         case "annule":
         $text.="Bonjour, <br>Ce mail annule et remplace le mail pr�c�demment envoy� pour la commande n� ".$mporder->id;
         break;
         case "other":
         $text.=$_POST['otherintro'];
         break;
    }                                               
    $text.="<p><b>Produits :</b>";
    $paht=0;
    $ecopart=0;
    $garantie=0;
    foreach($_POST['produits'] as $p){
	    $orderline=$orderlines[$p];
            $article=$DB_site2->query_first("SELECT * FROM article a WHERE a.artcode='".$orderline->ref_tin."'");
            $text.="<br>qte : ".$orderline->number." - ".$_POST['desc_'.$p]." R�f : ".$_POST['ref_'.$p];
            $paht+=$orderline->number*floatval($_POST['price1_'.$p]);
            $ecopart+=$orderline->number*$article['ecopartht'];
	    if(isset($_POST['gar_'.$p])){
                $garantie+=$_POST['garval_'.$p];
                $text.="<br>+ Garantie fournisseur";
            }
            if(!$preview) $DB_site2->query("INSERT INTO mp_envoi_fournisseur_ligne SET date=".mktime().", expediteur='".$user_info['username']."', type='fourn', paht=".floatval($_POST['price1_'.$p]).", ecopart=".floatval($article['ecopartht']).", qte=".$orderline->number.", ligne_facture_id=".$p);
    }
    $text.="</p>";
    if(!isset($_POST['nopj'])){
         $text.="<p><b>Identification � mettre sur le colis</b>";
         if(isset($_POST['blpj'])) $text.="<br>Bon de livraison";
         if(isset($_POST['facturepj'])) $text.="<br>Facture";
         if(isset($_POST['tntpj'])) $text.="<br>Bon FEDEX Express";
	 if(isset($_POST['glspj'])) $text.="<br>Bon GLS";
         if(isset($_POST['fragilepj'])) $text.="<br>Etiquette fragile";
         if(isset($_POST['otherpj'])) $text.="<br>".$_POST['otherpjname'];
	 if(isset($_POST['identpj'])) $text.="<br>Texte � inscrire sur colis : Commande N� $mporder->id De Ma cave � vin - ACILYON pour $mporder->shipping_last_name $mporder->shipping_first_name $mporder->shipping_zip $mporder->shipping_city $mporder->shipping_country"; 
    }
    $text.="</p>";
    $trsptval=0;
    $text.="<p><b>D�lai de mise � disposition / livraison</b><br>";
    switch($_POST['trspt']){
        case "mazet":
        $text.="Enlevement � notre charge en vos locaux par ".$_POST['selecttrspt']." le ".$_POST['mazetdepart'];
        break;
        case "fournisseur":
        $text.="Livraison par vos soins par votre transporteur directement chez notre client professionnel";
        if($_POST['fournisseurlivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['fournisseurlivraison'];
        $trsptval=floatval($_POST['fournisseurcout']);
        break;
        case "colissimo":
        $text.="Livraison par vos soins par colissimo directement chez notre client.";
        if($_POST['colissimodepart']!="") $text.="<br>D�part maximum : ".$_POST['colissimodepart'];
        if($_POST['colissimolivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['colissimolivraison'];
        $trsptval=floatval($_POST['colissimocout']);
        break;
        case "asls":
        $text.="Livraison par vos soins par votre transporteur directement chez SLS (adresse sur BL)";
        if($_POST['aslsdepart']!="") $text.="<br>D�part maximum : ".$_POST['aslsdepart'];
        if($_POST['aslslivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['aslslivraison'];
        $trsptval=floatval($_POST['aslscout']);
        break;
         case "livraisonmazet":
        $text.="Livraison par vos soins par votre transporteur directement chez ".$_POST['selectmazet'];
        if($_POST['livraisonmazetdepart']!="") $text.="<br>D�part maximum : ".$_POST['livraisonmazetdepart'];
        if($_POST['mazetdatelivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['mazetdatelivraison'];
        $trsptval=floatval($_POST['mazetcout']);
        break;
        case "installsls":
        $text.="Livraison par vos soins avec installation par SLS";
        if($_POST['installslsdepart']!="") $text.="<br>D�part maximum : ".$_POST['installslsdepart'];
        if($_POST['installslslivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['installslslivraison'];
        $trsptval=floatval($_POST['installslscout']);
        break;
        case "acilyon":
        $text.="Livraison par vos soins directement chez ACILYON<br>150 rue paul Bert<br>69003 Lyon<br>T�l : 04.26.03.05.40 / fax :04.26.03.05.49";
        if($_POST['acilyonlivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['acilyonlivraison'];
        $trsptval=floatval($_POST['acilyoncout']);
        break;
        case "other":
        $text.=$_POST['othertrspt'];
        if($_POST['othertrsptdepart']!="") $text.="<br>D�part maximum : ".$_POST['othertrsptdepart'];
        if($_POST['othertrsptlivraison']!="") $text.="<br>Livraison au plus tard : ".$_POST['othertrsptlivraison'];
        $trsptval=floatval($_POST['othertrsptcout']);
        break;
    }
    $text.="</p>";
    
    $total=$paht+$ecopart+$trsptval+$garantie;
    $text.="<p><b>Prix :</b> $paht � HT";
    if($ecopart!=0) $text.="<br>+ $ecopart � HT �co part.";
    if($trsptval!=0) $text.="<br>+ $trsptval � HT transport";
    if($garantie!=0) $text.="<br>+ $garantie � HT garantie constructeur";
    $text.="<br>=<b>$total � HT</b></p>";

    if(isset($_POST['endtxt1'])) $text.="<p>".$_POST['otherendtxt']."</p>";
    if(isset($_POST['endtxt2'])) $text.="Nous vous remercions de votre envoi, par mail ou fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - ACILYON - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.43 -  Fax: +33.4.26.03.05.49";

    if(!$preview){
        $DB_site2->query("INSERT INTO mp_envoi_fournisseur SET facturid='$mporder->id', date=".time().", destinataire='".$_POST['fournisseurname']."', tarif=".$total.", type='fourn'");

        $mail=new email();
        $mail->destinataire=$_POST[fournisseur];
	$mail->copymode="bcc";
	$mail->to=array("pr@mon-espace-cuisson.com","adv@ma-cave-a-vin.fr","mark1@ma-cave-a-vin.fr");
	$mail->expediteur="adv@ma-cave-a-vin.fr";
        $mail->expname="Ma Cave � Vin";
        $mail->subject=$subject;
        $mail->body=$text;
        if(!isset($_POST['nopj'])){
            if(isset($_POST['facturepj'])){
                $f=fopen("http://www.ma-cave-a-vin.fr/facture_mp.php?facturid=$facturid","r");
		$data="";
		while(!feof($f)) $data.=fgets($f,1024);
		fclose($f);
                $mail->attachstring($data,"Facture $mporder->id.pdf","application/pdf");
            }
            if(isset($_POST['blpj'])){
                $_GET['join']=true;
                $cde=1;
                $mail->attachstring(include("../../facture_mp.php"),"Bon $mporder->id.pdf","application/pdf");
            }
            if(isset($_POST['tntpj'])){
                $f=fopen($_FILES['bontnt']['tmp_name'],"r");
                $data="";
                while(!feof($f))
                     $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"Bon FEDEX Express","application/pdf");
            }
	    if(isset($_POST['glspj'])){
                $f=fopen($_FILES['bongls']['tmp_name'],"r");
                $data="";
                while(!feof($f))
                     $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"Bon GLS","application/pdf");
            }
            if(isset($_POST['fragilepj'])){
                $f=fopen("../pdf/fragile.pdf","r");
                $data="";
                while(!feof($f))
                    $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"fragile.pdf","application/pdf");
            }
	    if(isset($_POST['trsptpj'])){
                $f=fopen("../pdf/transport.pdf","r");
                $data="";
                while(!feof($f))
                    $data.=fread($f,1024);
                fclose($f);
                $mail->attachstring($data,"note_importante.pdf","application/pdf");
            }
        }
        $result=$mail->sendmail();
        if($result!=false) $action="editer";
    }else{
        print "sujet:$subject<br>destinataire:$_POST[fournisseur]<br>mail:$text";
    }
}

// envoi commande au transporteur
if($action=="advtransport"){
    $subject="";
    switch($_POST['trsptobj']){
         case "cmd":
         $subject="Commande n�".$mporder->id;
         break;
         case "other":
         $subject=$_POST['otherobj'];
         break;
    }
    $text="";
    switch($_POST['trsptintro']){
         case "bonjour":
         $text.=" Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande de transport n�$mporder->id";
         break;
         case "annule":
         $text.="Bonjour,<br>Ce mail annule et remplace le mail pr�c�demment envoy�";
         break;
         case "other":
         $text.=$_POST['othertrsptintro'];
         break;
    }  
    $text.="<p><b>Produits :</b>";
    $paht=0;
    $ecopart=0;
    foreach($_POST['produits2'] as $p){
	    $orderline=$orderlines[$p];
            $article=$DB_site2->query_first("SELECT * FROM article a WHERE a.artcode='".$orderline->ref_tin."'");
            $text.="<br>qte : ".$orderline->number." - ".$orderline->product_name." ".$article['emballage_poids']." kg";
            if(!$preview) $DB_site2->query("INSERT INTO mp_envoi_fournisseur_ligne SET date=".mktime().", expediteur='".$user_info['username']."', type='trspt', ligne_facture_id=".$p);
    }
    $text.="</p>";
    $text.="<p><b>Lieu d'enl�vement :</b><br>";
    switch($_POST['lieutrspt']){
    case "eurocave":
    $text.="EUROCAVE<br>Demander Marion BRUN<br>26140 Albon<br>Tel 04.75.03.72.90 / Fax: 04.75.03.72.99";
    break;
    case "vinosafe":
    $text.="VINOSAFE<br>Demander Huguette FEHRENBACH<br>2 rue des artisans<br>68280 SUNDHOFFEN<br>T�l. : +33 3 89 71 45 35 / Fax : +33 3 89 71 49 73<br>H.O. : du lundi au jeudi : 8h-12h et 13h30-16h30 maxi et le vendredi  : 8h-12h maxi";
    break;
    case "dometic":
    $text.="TRANSPORT MAZET<br>BATIMENT STOCKAGE<br>(pour le compte de DOMETIC)<br>Demander St�phanie ou Pierre Marie<br>1 RD 370 <br>95500 GONESSE <br>tel 01 34 07 72 02<br>Indiquer le n� Dometic imp�rativement ".$_POST['dometictrsptnb'];
    break;
    case "lasommeliere":
    $text.="LA SOMMELIERE<br>Demander Isabelle REZE<br>ZA LES PORTES DE L OCEANE OUEST<br>72650 ST SATURNIN<br>T�l. : 02 43 21 69 80 / Fax : 02 43 21 69 92";
    break;
    case "liebherr":
    $text.="Livraison par Liebherr<br>Le ou les colis vous seront livr�s directement � votre entrep�t de ".$_POST['livraisoneb']." par le transporteur de la soci�t� EBERHARDT fr�res";
    break;
    case "other":
    $text.=$_POST['otherlieutrspt'];
    break;
    }
    $text.="</p>";

    switch($_POST['trsptenl']){
         case "0":

         break;
         case "fournisseur";
         $text.="<p><b>Date d'enl�vement / mise � disposition : </b> ".$_POST['trsptenlfourndate']."</p>";
         break;
         case "eberhardt":
         $text.="<p>Date livraison en vos locaux par le transporteur de Eberhardt : ".$_POST['trsptlivreberhardtdate'];
         break;
         case "other":
         $text.=$_POST['trsptenlother'];
         break;
    }

    $text.="<p><b>Identification � mettre sur le colis</b> :";
    if(isset($_POST['trsptbl'])) $text.="<br>BL fournit ci-joint";
    if(isset($_POST['trsptfacture'])) $text.="<br>Facture ci-jointe";
    if(isset($_POST['trsptfragile'])) $text.="<br>Etiquette Fragile ci-jointe";
    if(isset($_POST['trspttext'])) $text.="<br>Texte sur le colis : commande n�$mporder->id de Ma Cave � Vin - Acilyon pour $mporder->shipping_first_name $mporder->shipping_last_name $mporder->shipping_zip $mporder->shipping_city";
    if(isset($_POST['trsptimportant'])) $text.="<br>Important : enlever tous les documents de livraison de Eberhardt fr�res";
    if(isset($_POST['trspttextother'])) $text.="<br>".$_POST['trsptothertext'];
    $text.="</p>";

    
    switch($_POST['trsptdatemax']){
         case "0":

         break;
         case "trsptdateauto":
         $text.="<p><b>Date livraison imp�rative</b> : ".$_POST['trsptdate']."</p>";
         break;
         case "trsptdateother":
         $text.="<p><b>Date livraison maximum</b> : ".$_POST['trsptotherdate']."</p>";
         break;
    }
    $text.="<p><b>Adresse livraison : </b>";
    switch($_POST['trsptlivr']){
         case "particulier":
         $text.="Adresse client particulier = sur RDV <br>$mporder->shipping_last_name $mporder->shipping_first_name<br>$mporder->shipping_address_1<br>$mporder->shipping_zip $mporder->shipping_city<br>$pays[libelle]<br>$mporder->shipping_phone";
         break;
         case "pro":
         $text.="Adresse client professionnel <br>$mporder->shipping_company<br>$mporder->shipping_last_name $mporder->shipping_first_name<br>$mporder->shipping_address_1<br>$mporder->shipping_zip $mporder->shipping_city<br>$pays[libelle]<br>$mporder->shipping_phone";
         break;
	 case "slsblois":
         $text.="SLS BLOIS <br>133 avenue de Vendome<br>41000 blois <br>A l'attention de Delphine SAINT LUC<br>tel: 02 54 56 08 80 fax: 02 54 56 05 55 <br>dsaintluc@sls-gci.eu";
         break;
         case "slsbordeaux":
         $text.="SLS CESTAS <br>65 Avenue Mar�chal de Lattre de Tassigny <br>33610 CESTAS <br>A l'attention de Laurent DELTEIL <br>tel: 05 56 07 57 57 fax: 05 56 36 67 23 <br>ldelteil@s-l-s.fr";
         break;
	 case "slsbordeaux2":
	 $text.="SLS MARSAC SUR L'ISLE<br>16 Impasse de l'Artisanat<br>24430 MARSAC SUR L ISLE <br>A l'attention de Laurent DELTEIL <br>tel: 05 56 07 57 57 fax: 05 56 36 67 23 <br>ldelteil@s-l-s.fr";
	 break;
         case "slsclermont":
         $text."SLS CLERMONT<br>5 Rue Gutenberg <br>63100 CLERMONT FERRAND  <br>A l'attention de David MENDES <br>tel: 04 73 90 43 87 fax: 04 73 90 47 43 <br>dtrawinski@sls-gci.eu";
         break;
         case "slslille":
         $text.="SLS FEUCHY <br>120 All�e du Danemark  - ZI Artipole<br>62223 FEUCHY<br>A l'attention de Abdou SALHI<br>tel: 03 91 19 19 12 fax: 03 21 22 21 22 <br>asalhi@s-l-s.fr";
         break;
         case "slslyon":
         $text.="SLS CORBAS <br>11 Avenue du 24 AOUT 1944<br>69960 CORBAS<br>A l'attention de Didier TRAWINSKI<br>tel: 04 37 25 61 80 fax: 04 37 25 62 01 <br>dtrawinski@sls-gci.eu";
         break;
	 case "slslyon2":
         $text.="SLS DIJON<br>25 rue du Bailly<br>21000 DIJON<br>A l'attention de Didier TRAWINSKI<br>tel: 04 37 25 61 80 fax: 04 37 25 62 01 <br>dtrawinski@sls-gci.eu";
         break;
         case "slsmetz":
         $text.="SLS SCY CHAZELLES<br>7 Chemin de la Moselle <br>57160 SCY CHAZELLES <br>A l'attention de Abdou SALHI <br>tel: 03 87 16 42 70 fax: 03 87 16 42 78 <br>asalhi@sls-gci.eu";
         break;
	 case "slsmetz2":
         $text.="SLS SAINT-MARTIN-SUR-LE-PRE<br> Rue Charles Marie Ravel<br>51120 SAINT-MARTIN-SUR-LE-PRE<br>A l'attention de Abdou SALHI <br>tel: 03 87 16 42 70 fax: 03 87 16 42 78 <br>asalhi@sls-gci.eu";
         break;
	 case "slsmetz3":
         $text.="SLS GOLBEY<br>Rue du Xay<br>ZI n�2<br>88191 GOLBEY<br>A l'attention de Abdou SALHI <br>tel: 03 87 16 42 70 fax: 03 87 16 42 78 <br>asalhi@sls-gci.eu";
         break;
         case "slsmiramas":
         $text.="SLS MIRAMAS<br>Avenue Ferdinand Magellan<br>Bat4 Cellule 4<br>13140 MIRAMAS<br>A l'attention de Jean Marc AIDOUN <br>tel:  04 42 46 05 04 fax: 04 42 46 06 09 <br>jmaidoun@s-l-s.fr";
         break;
	 case "slsmiramas2":
         $text.="SLS ST LAURENT DU VAR<br>all�e des g�ometres<br>06700 St Laurent du Var<br>A l'attention de Jean Marc AIDOUN <br>tel:  04 42 46 05 04 fax: 04 42 46 06 09 <br>jmaidoun@s-l-s.fr";
         break;
         case "slsrennes":
         $text.="SLS GOVEN<br>Entrepots l'Hermine, ZA les Corbi�res <br>35580 GOVEN    <br>A l'attention de Regine DAVY <br>tel: 02 99 05 42 05 fax: 02 99 05 42 06 <br>rdavy@s-l-s.fr";
         break;
         case "slstoulouse":
         $text.="SLS COLOMIERS<br>26 Chemin de Garrabot, ZI EN JACCA <br>31770 COLOMIERS <br>A l'attention de St�phane MASTELLONE <br>tel: 05 61 16 36 10 fax: 05 61 71 48 75 <br>smastellone@s-l-s.fr";
         break;
	 case "slstoulouse2":
         $text.="SLS LATTES<br>ZA Puech RADIER,<br>22 rue Montels �glise<br>34970 LATTES<br>A l'attention de Virginie ROUCAIROL<br>tel: 04 99 06 47 35 fax: 04 99 06 47 22 <br>vroucairol@sls-gci.eu";
         break;
         case "slsvillabe":
         $text.="SLS VILLABE<br>Centre d'Activit�s EUROLOGISTICS Bat E,<br>44 rue des Arpents ZAC des Brateaux<br>91100 VILLABE<br>A l'attention de Patrice MUR<br>tel: 01 60 76 13 12 fax: 01 60 76 13 30<br>pmur@sls-gci.eu";
         break;
    }
    switch($_POST['infotrspt']){
         case "0":

         break;
         case "rdv":
         $text.="<p><b>Informations pour transporteur</b> : prendre RDV avec le client pour livraison</p>";
         break;
         case "other":
         $text.="<p><b>Informations pour transporteur</b> : ".$_POST['othertrsptinfo']."</p>";
         break;
    }


    $text.="<p><b>Tarif :</b>";
    $tarif=0;
    if(isset($_POST['trsptcout'])){ $tarif+=floatval($_POST['trsptcoutval']); $text.=$tarif." �"; }
    if(isset($_POST['trsptsaison'])){ $tarifsaison=$tarif*(1+floatval($_POST['trsptsaison']/100)); $tarif+=$tarifsaison; $text.="<br>+ ".$tarifsaison." &euro; (taxe saisonni�re)"; }
    if(isset($_POST['trsptecotax'])){ $tarif+=floatval($_POST['trsptecotax']); $text.="<br>+ $_POST[trsptecotax] � (ecotax)"; }
    if(isset($_POST['trsptenlext'])){ $tarif+=floatval($_POST['trsptenlext']); $text.="<br>+ $_POST[trsptenlext] � (enl�vement ext.)"; }
    if(isset($_POST['trsptparis'])){ $tarif+=floatval($_POST['trsptparis']); $text.="<br>+ $_POST[trsptparis] � (grande m�tropole Paris)"; }
    if(isset($_POST['trsptlyon'])){ $tarif+=floatval($_POST['trsptlyon']); $text.="<br>+ $_POST[trsptlyon] � (grande m�tropole Lyon)"; }
    if(isset($_POST['trsptmarseille'])){ $tarif+=floatval($_POST['trsptmarseille']); $text.="<br>+ $_POST[trsptmarseille] � (grande m�tropole Marseille)"; }
    if(isset($_POST['trsptalloinville'])){ $tarif+=floatval($_POST['trsptalloinville']); $text.="<br>+ ".$_POST['trsptalloinville']." &euro; (centre ville)"; }
    if(isset($_POST['trsptcorse'])){ $tarif+=floatval($_POST['trsptcorse']); $text.="<br>+ ".$_POST['trsptcorse']." &euro; (frais Corse)"; }
    if(isset($_POST['trsptclientpart'])){ $tarif+=floatval($_POST['trsptclientpart']); $text.="<br>+ $_POST[trsptclientpart] (1 client particulier pour RDV)"; }
    if(isset($_POST['trsptclientpro'])){ $tarif+=floatval($_POST['trsptclientpro']); $text.="<br>+ $_POST[trsptclientpro] (1 client professionnel)"; }
    if(isset($_POST['trsptetage'])){ $tarif+=floatval($_POST['trsptetage']); $text.="<br>+ $_POST[trsptetage] � (livraison �tage simple)"; }
    if(isset($_POST['trsptassurcomp'])){ $tarif+=floatval($_POST['trsptassurcompval']); $text.="<br>+ $_POST[trsptassurcompval] (assurance : valeur d�clar�e : $_POST[valeurdeclaree])"; }
    if(isset($_POST['trsptcoutother'])){ $tarif+=floatval($_POST['trsptcoutotherval']); $text.="<br>+ $_POST[trsptcoutotherval] ($_POST[trsptcoutothername])"; }
    $text.="<br><b>= $tarif � HT (hors taxe gas oil)</b></p>";

    if($_POST['trsptfin']=="remercie") $text.="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande<br><br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : + 33.4.26.03.05.43 - Fax : + 33.4.26.03.05.49";

    if($_POST['typetrspt']=="othertypetrspt") $_POST['typetrspt']=$_POST['othertrspttype'];
    if(!$preview){
        $DB_site2->query("INSERT INTO mp_envoi_fournisseur SET facturid='$mporder->id', date=".time().", destinataire='".$_POST['trsptname']."', tarif=".$tarif.", type='trspt'");

        $mail=new email();
        $mail->destinataire=$_POST['typetrspt'];
	$mail->copymode="bcc";
        $mail->to=array("pr@mon-espace-cuisson.com","adv@ma-cave-a-vin.fr","mark1@ma-cave-a-vin.fr");
        $mail->expediteur="adv@ma-cave-a-vin.fr";
        $mail->expname="Ma Cave � Vin";
        $mail->subject=$subject;
        $mail->body=$text;

        if(!isset($_POST['notrsptpj'])){
            if(isset($_POST['facturetrsptpj'])){
                $_GET['join']=true;
                $mail->attachstring(include("../../facture_mp.php"),"Facture $mporder->id.pdf","application/pdf");
            }
            if(isset($_POST['bonstrsptpj'])){
                $_GET['join']=true;
                $cde=1;
                $mail->attachstring(include("../../facture_mp.php"),"Bon $mporder->id.pdf","application/pdf");
            }
            if(isset($_POST['othertrsptpj']) && !empty($_FILES['othertrsptpjfile'])){
                $f=fopen($_FILES['othertrsptpjfile']['tmp_name'],"r");
                if($f!=false){
                 while(!feof($f))
                      $data.=fread($f,1024);
                 fclose($f);
                 $mime=$DB_site2->fetch_array($DB_site2->query("SELECT * FROM MimeType WHERE g_extension='".pathinfo($_FILES['othertrsptpjfile']['tmp_name'], PATHINFO_EXTENSION)."'"));
                 $mail->attachstring($data,$_FILES['othertrsptpjfile']['name'],$mime['g_mimeType']);
                }
            }
        }
        $action="editer";
        if($_POST['typetrspt']!="0") $mail->sendmail();  // on envoie pas de mail s'il n'y a pas de transport
    }else{
        if($_POST['typetrspt']=="0")
            print "Pas d'envoi de mail";
        else
            print "Destinataire:".$_POST['typetrspt']."<br>Sujet:".$subject."<br>".$text;
    }
}

// envoi commande sls
if($action=="advsls"){
        $subject="";
        switch($_POST['slsobj']){
         case "cmd":
         $subject="Commande n�".$mporder->id;
         break;
         case "other":
         $subject=$_POST['slsotherobj'];
         break;
        }
        $text="";
        switch($_POST['slsintro']){
         case "bonjour":
         $text.="Bonjour,<br>Veuillez trouver ci-joint une nouvelle commande pour livraison et intallation n�".$mporder->id;
         break;
         case "annule":
         $text.="Bonjour, <br>Ce mail annule et remplace le mail pr�c�demment envoy� pour la commande n�".$mporder->id;
         break;
         case "otherslsintro":
         $text.=$_POST['otherslsintro'];
         break;
        }
        $text.="<p><b>Produits :</b>";
        if(isset($_POST['produits3'])){
	  foreach($_POST['produits3'] as $p){
            $orderline=$orderlines[$p];
            $article=$DB_site2->query_first("SELECT * FROM article a WHERE a.artcode='".$orderline->ref_tin."'");
            $text.="<br>qte :".$orderline->number." - ".$orderline->product_name." ".$article['emballage_poids']." kg (Voir BL ci-joint)";
            if(!$preview) $DB_site2->query("INSERT INTO mp_envoi_fournisseur_ligne SET date=".mktime().", expediteur='".$user_info['username']."', type='sls', ligne_facture_id=".$p);
	  }
	}
        $text.="</p>";
        $text.="<p><b>Livraison en vos entrepots de ".$slsagences[$_POST['livrsls']]."</b><br>";
        switch($_POST['slslivr']){
         case "0":
         break;
         case "mory":
         $text.="Le ou les colis vous seront livr�s directement � votre entrepot par Mory Group ".$_POST['slslivrmorydate'];
         break;
         case "vinosafe":
         $text.="Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de Vinosafe ".$_POST['slslivrvinosafedate'];
         break;
         case "eberhadt":
         $text.="Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de Eberhadt fr�res ".$_POST['slslivreberhadtdate'];
         break;
         case "dometic":
         $text.="Le ou les colis vous seront livr�s directement � votre entrepot par transporteur de Dometic ".$_POST['slslivrdometicdate'];
         break;
         case "other":
         $text.=$_POST['otherslslvr']." ".$_POST['slslivrotherdate'];
         break;
        }

        $text.="</p>";
        $text.="<p><b>Identification sur le colis</b>";
        if(isset($_POST['slsidentbl'])) $text.="<br>BL fournit ci-joint";
        if(isset($_POST['slsidenttxt'])) $text.="<br>Texte sur colis : commande n�$mporder->id de Ma Cave � Vin - Acilyon pour ".nl2br($_POST['slsidentpour']);
        if(isset($_POST['slsidentimportant'])) $text.="<br>Important : enlever tous les documents de livraison de EBERHARDT Fr�res";
        if(isset($_POST['slsidentother'])) $text.="<br>".$_POST['slsidentothertxt'];
        $text.="</p>";

        $text.="<p><b>Adresse de livraison </b>:";
        switch($_POST['slsadrlivr']){
            case "particulier":
            $text.=" Adresse client particulier = sur RDV (voir BL)";
            break;
            case "pro":
            $text.=" Adresse client professionnel (voir BL)";
            break;
        }
        $text.="</p>";
        switch($_POST['maxslslivr']){
         case "0":
         break;
         case "imperatif":
         $text.="<br>Date installation imp�rative ".$_POST['slsimperatifdate'];
         break;
         case "maxslslivr":
         $text.="<br>Date installation maximum ".$_POST['slsmaxdate'];
         break;
         case "other":
         $text.="<br>".$_POST['slslivrotherdate'];
         break;
        }
        if(isset($_POST['slsinfo'])){
            $text.="<p><b>Informations pour transporteur et installateur</b> :";
            if(isset($_POST['slsrdv'])) $text.="<br>Prendre RDV avec le client pour livraison";
            if(isset($_POST['slsbranchement'])) $text.="<br>Important : ne pas brancher �lectriquement la cave a vin. Branchement a effectuer par le client 48h apres votre instalation";
            if(isset($_POST['slsmiseniveau'])) $text.="<br>Important : mettre � niveau la cave � vin";
            if(isset($_POST['slsmontage'])) $text.="<br>Important : Si besoin, assurer le montage de la poign�e de porte et installer les clayettes a l interieur de la cave.";
            if(isset($_POST['slsinfoother'])) $text.=$_POST['slsinfoothertxt'];
            $text.="</p>";
        }

        if($_POST['recup']!="0"){
             if($_POST['recup']=="1") $text.="<p><b>R�cup�ration ancienne cave</b> : Attention vous devrez r�cup�rer une ancienne cave (poids de ".$_POST['recuppoids']."kg) qui remplace celle que vous installerez</p>";
             if($_POST['recup']=="other") $text.="<p>".$_POST['recupother']."</p>";
        }
    
        $totalsls=0;
        $text.="<p><b>Tarif SLS :</b>";
        if(isset($_POST['slscouttrspt'])){
            $totalsls+=floatval($_POST['slscouttrsptval']);
            $text.="<br>".$_POST['slscouttrsptval']." �";
        }
        if(isset($_POST['slscoutcorse'])){
            $totalsls+=floatval($_POST['slscorsetotal']);
            $text.="<br>+ ".$_POST['slscorsetotal']." � Passage Corse";
        }
        if(isset($_POST['slscoutreprise'])){
            $totalsls+=floatval($_POST['slscoutrepriseval']);
            $text.="<br>+ ".$_POST['slscoutrepriseval']." � Reprise ancienne cave";
        }
        if(isset($_POST['slscoutassur'])){
            $totalsls+=floatval($_POST['slscoutassurval']);
            $text.="<br>+ ".$_POST['slscoutassurval']." � Assurance compl�mentaire";
        }
        if(isset($_POST['slscoutother'])){
            $totalsls+=floatval($_POST['slscoutotherval']);
            $text.="<br>+ ".$_POST['slscoutotherval']." � ".$_POST['slscoutothername'];
        }
        $text.="<br>= ".$totalsls." �</p>";

        if($_POST['slsfin']=="1") $text.="Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>T�l : +33.4.26.03.05.43 - fax : +33.4.26.03.05.49";
        
        if(!$preview){
            $DB_site2->query("INSERT INTO mp_envoi_fournisseur SET facturid='$mporder->id', date=".time().", destinataire='".$_POST['livrsls']."', tarif=".$totalsls.", type='sls'");

            $mail=new email();
            $mail->destinataire=$slsemails[$_POST['livrsls']];
	    $mail->copymode="bcc";
            $mail->to=array("pr@mon-espace-cuisson.com","adv@ma-cave-a-vin.fr","mark1@ma-cave-a-vin.fr");
            $mail->expediteur="adv@ma-cave-a-vin.fr";
            $mail->expname="Ma Cave � Vin";
            $mail->subject=$subject;
            $mail->body=$text;
            if(!isset($_POST['slspj'])){
                if(isset($_POST['factureslspj'])){
                    $_GET['join']=true;
                    $mail->attachstring(include("../../facture_mp.php"),"Facture $mporder->id.pdf","application/pdf");
                }
                if(isset($_POST['bonslspj'])){
                    $_GET['join']=true;
                    $cde=1;
                    $mail->attachstring(include("../../facture_mp.php"),"Bon $mporder->id.pdf","application/pdf");
                }
                if(isset($_POST['otherslspj']) && !empty($_FILES['otherslspjfile'])){
                    $f=fopen($_FILES['otherslspjfile']['tmp_name'],"r");
                    if($f!=false){
                    while(!feof($f))
                      $data.=fread($f,1024);
                    fclose($f);
                    $mail->attachstring($data,$_FILES['otherslspjfile']['name'],"application/octet-stream");
                    }
                }
            }
            $action="editer";
            if($_POST['livrsls']!="0") $mail->sendmail();    // on envoie le mail seulement s'il y a une livraison
        }else{
           if($_POST['livrsls']=="0")
               print "Pas d'envoi de mail";
           else
               print "Destinataire:".$slsemails[$_POST['livrsls']]."<br>Sujet:".$subject."<br>".$text;
       }

}


?>