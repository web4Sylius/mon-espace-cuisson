<?php
include("global.php");
/*if(!isset($_COOKIE['admin_username'])){
    header("Location: maintenance.html");
    exit;
}*/
include_once("mailconfirmation.php");
include_once("admin/dissociation.php");

$title = "title".$template->langue;
$unlogged = 1;
$checkPwd = true;

if(!isset($_COOKIE['userid']) || $_COOKIE['userid'] == '')
{
    if(isset($_GET['factureid']) && $_GET['factureid'] != '')
    {
        $sql = "SELECT userid";
        $sql .= " FROM facture";
        $sql .= " WHERE facturid = " . $_GET['factureid'];
        $userid = $DB_site->query_first($sql)['userid'];

        setcookie("userid",$userid,time()+3600000,"/", $template->host);

        $checkPwd = false;
        $unlogged = 0;
    }
}

$sql = "SELECT *";
$sql .= " FROM utilisateur";
$sql .= " WHERE userid = '".$_COOKIE['userid']."'";
$user_info = $DB_site->query_first($sql);

if($checkPwd)
{
    if($user_info['password'] == ""){
     
         $raison    = $multilangue['mailinconnu'];
         $user_info = "";
         
    } elseif($user_info['password'] == $_COOKIE['password']){
         
         setcookie("userid","$user_info[userid]",time()+3600000,"/", $template->host);
         setcookie("password","$user_info[password]",time()+3600000,"/", $template->host);
         $unlogged = 0;
         
    } else {
         
         $raison    = $multilangue['motpasseincorrect'];
         $user_info = "";
    }
}

$criteocat = array();
$nxtckitems = array();
$fbpixelitems = array();

$sql = "SELECT *";
$sql .= " FROM display_categorie";
$sql .= " WHERE display = 'criteo'";
$requete = $DB_site->query($sql);
while($dc = $DB_site->fetch_array($requete)) $criteocat[] = $dc['catid'];

if($unlogged == 0){
     $criteoitems = array();
     $facture = new facture($DB_site,$factureid);
     $totalmontant = $facture->totalttc;
     $ecommerce2="\n\n";
     //$test="";
     //on enregistre les totaux
     $facture->savetotal();
     $products = 0;
     $ecommerce2="window.dataLayer = window.dataLayer || [];
                  window.dataLayer.push({
                  'event': 'purchase',
                  'transactionId': '".$facture->facturid."',
                  'transactionAffiliation': '',
                  'transactionTotal': ".$facture->totalttc.",
                  'transactionTax': ".($facture->totalttc-$facture->totalht).",
                  'transactionShipping': ".$facture->portttc.",
                  'transactionCoupon': 0,
                  'transactionProducts': ";


     foreach($facture->lignes as $lignefacture){
          
        $gaitems .= "pageTracker._addItem('".$facture->facturid."','".$lignefacture->artcode."','".html_entity_decode($lignefacture->libelle)."','','".$lignefacture->prix."','".$lignefacture->qte."');\n";
          
        $categorie=new categorie($DB_site,$lignefacture->catid,"FR");
          // tracking google analytics
        $products ++;
        if($products>1) $ecommerce2.=",";
        $ecommerce2.="{
          'sku': '".$lignefacture->artcode."',
          'name': \"".html_entity_decode(str_replace("\"","",$lignefacture->libelle))."\",
          'category': \"".$categorie->libelle."\",
          'brand': '".$lignefacture->marque."',
          'variant': '',
          'price': ".$lignefacture->prix.",
          'quantity': ".$lignefacture->qte."
        }";
        
        $fbpixelitems[] = $lignefacture->artid;
        
        $nxtckitems[] = $lignefacture->artid;

          if(in_array($article['catid'], $criteocat)){
               
               $itmnb++;
               $criteoitems[] = array("id"=>strval($lignefacture->artid),"price"=>(float)number_format($lignefacture->prix,2,'.',''),"quantity"=>(int)$lignefacture->qte);
          }
     }

    $ecommerce2.="
                   \n});\n";

     $template->parts["factureid"]    = $facture->facturid;
     $template->parts["totalmontant"] = $template->price_format($facture->totalttc);

     $jsoncriteoitems = json_encode($criteoitems);
     
     $template->parts["criteo"] = <<<JS
          <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
          <script type="text/javascript">
          window.criteo_q = window.criteo_q || [];
          window.criteo_q.push(
          { event: "setAccount", account: 2995 },
          { event: "setEmail", email: ["$user_info[mail]"] },
          { event: "setSiteType", type: "d" },
          { event: "trackTransaction", id: "$facture->facturid", item: $jsoncriteoitems }
          );
          </script>
JS;
  
     /* tracking cycbercite */
     $cybercite = "
     <script id='tg_vente_cybercite' type='text/javascript' src='http://tracking.veille-referencement.com/TAG/TAG_vente.js'></script>
     <script type='text/javascript'>
     var id_site = '$idcybercite';
     var id_vente=''; var contenu=''; var montant=''; 
     id_vente = '$factureid';
     contenu += generer_contenu('Nom','$user_info[nom]');\n
     contenu += generer_contenu('E-mail','$user_info[mail]');\n
     montant = $totalmontant;
     tracking_cybercite(id_site, id_vente, contenu, montant);
     </script>
     ";
     /* fin tracking cycbercite */

     /* tracking shopzilla */
     $shopzilla = "
             <script language='JavaScript' src='https://evaleu.shopzilla.com/js/pos_".$idshopzilla.".js' type='text/javascript'></script>
     ";
     /* fin tracking shopzilla */ 



     /* TRACKING NEXTPERFORMANCE */

     //Mise en place des tags Nextperformance
    $cpt   = 0;
    $nxtck = '';
    foreach($nxtckitems as $l_item){
        if($cpt > 0) $nxtck .= ',';
        $nxtck .= $l_item;
        $cpt++;
    }
    $totalmontant2 = str_replace('.',',' , $totalmontant); //il faut remplacer le point par une virgule pour nextperformance...........
    $template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43958&pid=".$nxtck."&id=".$facture->facturid."&mt=".$facture->totalttc."&tvalid=".(isset($_COOKIE['nxtck']) && $_COOKIE['nxtck'] == 1 ? 1 : 0)."&f1=".$totalmontant2."\" async></script>";

      /* FIN TRACKING NEXTPERFORMANCE */


    /* TRACKING FACEBOOK */

    /**** PIXEL FACEBOOK***/
    $pxl_items="";
    foreach ($fbpixelitems as $item ) {
      $pxl_items .= "'".$item."',";
    }
    $pxl_items = substr($pxl_items,0, strlen($pxl_items)-1);

    $fbpixel = "fbq('track', 'Purchase', { content_ids: [".$pxl_items."], content_type: 'product', value: ".$totalmontant.", currency: 'EUR'});";
    
    /* FIN TRACKING FACEBOOK */

    /* BEGIN IDEALO PARTNER TRACKING CODE */

    $template->parts['idealo'] = "<img src=\"//marketing.net.idealo-partner.com/ts/i5033562/tsa?typ=i&tst=".time()."&trc=basket&ctg=sale&sid=checkout&cid=".$facture->facturid."&orv=".$totalmontant."&orc=EUR\" width=\"1\" height=\"1\" border=\"0\" style=\"display:none;\">" ;

    /* END IDEALO PARTNER TRACKING CODE */

     //CB - MCAV FR & MCAV COM
     if($moyen == "1"){
          $sql = "UPDATE facture SET";
          $sql .= " moyenid = '1'";
          $sql .= " WHERE facturid = '".$facture->facturid."'";
          $DB_site->query($sql);
          $facturid = $facture->facturid;

          include ($pagepaiementpaybox);
          
          $message=$template->translate("/commande/choix_carte","F");
          $template->parts["paiement"]  = <<<HTML
          <div class="frame">
          $pagepaiementcb
          </div>
HTML;
          $template->parts["etape"]     = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
          //$template->parts["ecommerce"] = $ecommerce2; // tracking google analytics
          //$template->parts["fbpixel"] = $fbpixel;
     }

     //CB - MCAV UK
     if($moyen == "16"){

          $sql = "UPDATE facture SET";
          $sql .= " moyenid = '16'";
          $sql .= " WHERE facturid = '".$facture->facturid."' ";
          $DB_site->query($sql);
          $facturid=$facture->facturid;

          $pagepaiement = "vad/macavavin/adyen.php";
          include ($pagepaiement);

          $template->parts["paiement"]  = $pagepaiementcb;
          $template->parts["etape"]     = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
          $template->parts["ecommerce"] = $ecommerce2;
          $template->parts["fbpixel"] = $fbpixel;
     }

     //Tout autre moyen de paiement que "CB"
     if($moyen != "1" and $moyen != "10" and $moyen != "11" and $moyen != "12"){

          $sql = "SELECT mail_contact mail, username, password, raison_sociale, nom_contact, adresse, cp codepostal, ville, RIBetab, RIBguichet, RIBrice, IBAN, BIC";
          $sql .= " FROM parametresgeneraux";
          $coord_marchand = $DB_site->query_first($sql);

          if($moyen != "3" and $moyen != "6"){
               $totalmontant = number_format($totalmontant, 2, ',', ' ');
          }

          //CHEQUE
          if($moyen == "2"){

               $sql = "UPDATE facture SET";
               $sql .= " moyenid = '2'";
               $sql .= " WHERE facturid = '".$facture->facturid."'";
               $DB_site->query($sql);

               $template->evaltemplate("Cde3PaiementCheque","paiement");

               $template->parts["etape"]     = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
               $template->parts["ecommerce"] = $ecommerce2; // tracking google analytics
               $template->parts["fbpixel"] = $fbpixel;

               mailconfirmation($moyen,$facture,$user_info,$template);
               //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
                $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
                verif_export($facture,$template);
          }

          //MANDAT POSTAL
          if($moyen == "4"){

               $sql = "UPDATE facture SET";
               $sql .= " moyenid = '4'";
               $sql .= " WHERE facturid = '".$facture->facturid."'";
               $DB_site->query($sql);

               $template->evaltemplate("Cde3PaiementMandatPostal","paiement");

               $template->parts["etape"]     = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
               $template->parts["ecommerce"] = $ecommerce2; // tracking google analytics
               $template->parts["fbpixel"] = $fbpixel;

               mailconfirmation($moyen,$facture,$user_info,$template);
               $diss = new dissociation($DB_site,$facture->facturid);
               //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
                $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
                verif_export($facture,$template);
          }

          //VIREMENT
          if($moyen == "5"){

               $sql = "UPDATE facture SET";
               $sql .= " moyenid = '5'";
               $sql .= " WHERE facturid = '".$facture->facturid."'";
               $DB_site->query($sql);

               $template->evaltemplate("Cde3PaiementVirement","paiement");

               $template->parts["etape"]     = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
               $template->parts["ecommerce"] = $ecommerce2; // tracking google analytics
               $template->parts["fbpixel"] = $fbpixel;
               //$template->parts['test'] = $test;
               mailconfirmation($moyen,$facture,$user_info,$template);
               $diss = new dissociation($DB_site,$facture->facturid);
               //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
                $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
                verif_export($facture,$template);
          }

          //LOCATION FINANCIERE
          if($moyen == "14"){

               $sql = "UPDATE facture SET";
               $sql .= " moyenid = '14'";
               $sql .= " WHERE facturid = '".$facture->facturid."'";
               $DB_site->query($sql);

               $template->evaltemplate("Cde3PaiementLocation","paiement");

               $template->parts["etape"]     = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
               $template->parts["ecommerce"] = $ecommerce2; // tracking google analytics
               $template->parts["fbpixel"] = $fbpixel;

               mailconfirmation($moyen,$facture,$user_info,$template);
               $diss = new dissociation($DB_site,$facture->facturid);
               //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
                $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
                verif_export($facture,$template);
          }

          //PAYPAL
          if ($moyen == "6"){
              
              $template->parts["etape"]    = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
              
              if(isset($_GET['paymentstatus'])){ //retour de la plateforme paypal
                  
                  $moyen_paiement = 'paypal';

                  switch($_GET['paymentstatus']){

                      case "COMPLETED":
                          $status = 'succes';
                          setcookie('popup_exit',1,time()+86400);
                      break;

                      case "PENDING":
                          $status = 'pending';
                      break;

                      default:
                          $status = 'erreur';

                  }
                  
                  $TemplateCde3 = ''
                  . '<h1 style="margin-left: 10px;">'.$template->translate("/commande/paiement_par").' '.$facture->getMoyen($moyen).'</h1>'
                  . '<div class="frame retour_paiement">'
                      . '<h2 style="text-align: center;">'.$template->translate("/commande/paiement_".$status).'</h2>'
                      . '<br><br>'
                      . sprintf($template->translate("/commande/".$moyen_paiement."_".$status), $facture->facturid)
                      . $template->translate("/commande/message_fin")
                      . '<div class="panier"><div class="row"><div class="col-sm-4 col-md-3 no-padding"> <a class="retour" style="float:none;margin:9px 0 10px 14px;" href="/">'.$template->translate("/commande/retour_accueil").'</a></div></div></div>'
                  . '</div>';

                  $template->parts["paiement"] = $TemplateCde3;
                  if($_GET['paymentstatus'] == 'COMPLETED') {
                    $template->parts["ecommerce"]=$ecommerce2; // tracking google analytics
                    $template->parts["fbpixel"] = $fbpixel;
                  }

                }else{
                  //Redirection vers la plateforme paypal
                    $sql = "UPDATE facture SET";
                    $sql .= " moyenid = '6'";
                    $sql .= " WHERE facturid = '".$facture->facturid."'";
                    $DB_site->query($sql);
                    include ($pagepaiement);
                    $template->parts["paiement"] = $pagepaiementpaypal;
                    $template->parts["ecommerce"]=$ecommerce2;
                }
    
              }

          /*if ($moyen == "8"){ // receive and pay

                  $DB_site->query("UPDATE facture SET moyenid = '8' WHERE facturid = '".$facture->facturid."'");
                  include ($pagepaiementkwixo);
                  $template->parts["paiement"]=$pagepaiementreceiveandpay;
                  $template->parts["etape"]="<img src=\"images/templates/".$template->langue."/commande4.jpg\" alt=\"Paiement\" class=\"img img-responsive hidden-xs\">";
                  $template->parts["ecommerce"]=$ecommerce;
          }

          if ($moyen == "9"){ // receive and pay en plusieurs fois

                  $DB_site->query("UPDATE facture SET moyenid = '9' WHERE facturid = '".$facture->facturid."'");
                  include ($pagepaiementkwixo);
                  $template->parts["paiement"]=$pagepaiementreceiveandpayplusieursfois;
                  $template->parts["etape"]="<img src=\"images/templates/".$template->langue."/commande4.jpg\" alt=\"Paiement\" class=\"img img-responsive hidden-xs\">";
                  $template->parts["ecommerce"]=$ecommerce;
          }*/

          if($moyen == 17){ //Sofinco : 4x sans frais ou financement

              $sql = "UPDATE facture SET";
              $sql .= " moyenid = '".$moyen."'";
              $sql .= " WHERE facturid = '".$facture->facturid."'";
              $DB_site->query($sql);

              include ($page_paiement_sofinco);

              $template->parts["paiement"] = $pagepaiementsofinco;
              $template->parts["etape"]    = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";

              $template->parts["ecommerce"]=$ecommerce2;
         }

         if($moyen == 18){ //Sofinco : 10x sans frais ou financement

              $sql = "UPDATE facture SET";
              $sql .= " moyenid = '".$moyen."'";
              $sql .= " WHERE facturid = '".$facture->facturid."'";
              $DB_site->query($sql);

              include ($page_paiement_sofinco);

              $template->parts["paiement"] = $pagepaiementsofinco;
              $template->parts["etape"]    = "<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";

              $template->parts["ecommerce"]=$ecommerce2;
         }

          if($moyen == 99){ // plusieurs paiements

               //On vide le panier (suppression du cookie associé)
               setcookie('s',"false",(time() - 3600));
               setcookie('popup_exit',1,time()+86400);

               $sql = "UPDATE facture SET";
               $sql .= " moyenid = '99'";
               $sql .= " WHERE facturid = '".$facture->facturid."'";
               $DB_site->query($sql); //, valide='0'
               
               $DB_site->query("DELETE FROM reglement WHERE facturid=".$facture->facturid);

               $nbpaiements = intval($_POST['nbpaiements']);
               for($i=0;$i<$nbpaiements;$i++){

                    $params = array();
                    $params['moyen']   = intval($_POST['moyens'][$i]);
                    $params['delais']  = intval($_POST['delais'][$i]);
                    $params['montant'] = floatval($_POST['paiement'][$i]);

                    $d = date_create_from_format("d/m/Y",$_POST['date'][$i]);
                    $d->settime(0,0,0);
                    $params['date']  = $d->format("Y-m-d H:i:s");
                    $params['nocmd'] = $facture->facturid;

                    $sql = "INSERT INTO reglement (";
                    $sql .= "reglement_nom";
                    $sql .= ", reglement_prenom";
                    $sql .= ", reglement_societe";
                    $sql .= ", reglement_email";
                    $sql .= ", reglement_tel";
                    $sql .= ", reglement_montant";
                    $sql .= ", reglement_commentaires";
                    $sql .= ", reglement_moyen";
                    $sql .= ", reglement_date";
                    $sql .= ", reglement_delais_id";
                    $sql .= ", facturid";
                    $sql .= ", userid";
                    $sql .= ") VALUES (";
                    $sql .= "'".mysqli_real_escape_string($DB_site->link_id,strtoupper($params['nom']))."'";
                    $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$params['prenom'])."'";
                    $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$params['raisonsociale'])."'";
                    $sql .= ", '".$params['mail']."'";
                    $sql .= ", '".$params['telephone']."'";
                    $sql .= ", '".$params['montant']."'";
                    $sql .= ", '".mysqli_real_escape_string($DB_site->link_id,$params['commentaire'])."'";
                    $sql .= ", '".intval($params['moyen'])."'";
                    $sql .= ", '".$params['date']."'";
                    $sql .= ", '".$params['delais']."'";
                    $sql .= ", '".$params['nocmd']."'";
                    $sql .= ", '".$user_info['userid']."'";
                    $sql .= ")";
                    $DB_site->query($sql);
                    $reglement_id = $DB_site->insert_id();

                    $sql = "INSERT INTO reglement_historique (";
                    $sql .= " reglement_reception_id";
                    $sql .= ", reglement_id";
                    $sql .= ", reglement_reception_date";
                    $sql .= ", reglement_historique_date";
                    $sql .= ", reglement_historique_username";
                    $sql .= ") VALUES (";
                    $sql .= "3";
                    $sql .= ", ".$reglement_id;
                    $sql .= ", NOW()";
                    $sql .= ", NOW()";
                    $sql .= ", '".$_COOKIE['admin_username']."'";
                    $sql .= ")";
                    $DB_site->query($sql);
               }

               $template->parts["etape"]="<img src=\"images/templates/commande5.jpg\" alt=\"Paiement\" class=\"img img-responsive\">";
        
                $message_paiement = mailconfirmation($moyen,$facture,$user_info,$template);
        
               //mailconfirmation($moyen,$facture,$user_info);
               $template->parts["paiement"] = "<div class=\"frame\">"
               .$message_paiement.'
               <a href="/" class="retour"><i class="fa fa-chevron-left"></i>Retour à la page d\'accueil</a>
               <div class="clear"></div>
               </div>';
               $diss = new dissociation($DB_site,$facture->facturid);
               //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
                $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
                verif_export($facture,$template);

               $template->parts["ecommerce"]=$ecommerce2;
          }
     }

     //On vérifie que le numéro de TVA intra a été renseigne
    /* $mailtva = false;
     if(trim($user_info['raisonsociale']) != ""){

          $sql = "SELECT *";
          $sql .= " FROM pays";
          $sql .= " WHERE paysid = ".$user_info['paysid'];
          $pays = $DB_site->query_first($sql);

          if($pays['cee'] == 1 && $user_info['tvaintracom'] == "" && ($pays['paysid']==57 || $pays['paysid']==181)){

               $mail=new email();
               $mail->destinataire="webmaster3@mon-espace-cuisson.com";
               //$mail->bcc="webmaster3@mon-espace-cuisson.com";
               $mail->expediteur=$template->translate("/site/[site_code]/mail_expediteur");
               $mail->expname=$template->translate("/site/nom");
               $mail->subject="Manque info tva intra";
               $mail->body="Attention le client n°".$user_info['userid'].", société ".$user_info['raisonsociale'].", ".$user_info['prenom']." ".$user_info['nom']." n'a pas indiqué son numéro de TVA";
               $mail->sendmail();
          }
     }*/
     
} else {
     
//      eval( "\$TemplateCde3 .= \"".$templatecache['logging']."\";" );
}

//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url])
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/cde3.php\">\n";
    }
}

$template->parts["remarketing"] = google_remarketing('',"purchase",'');

//Paramétrage des META référencement de la page
if($sitepro)
{   
    $title_trad = '/index/meta_title_pro';
    $title_desc = '/index/meta_description_pro';
}
else
{
    $title_trad = '/index/meta_title';
    $title_desc = '/index/meta_description';
}
//BarreCookie
$template->parts["stylePanier"]="style=\"bottom:0;\"";

$template->pageparams("www.mon-espace-cuisson.com", $template->translate($title_trad), $template->translate($title_desc));

$template->evaltemplate("PanierPaiement2","pagecommande");
$template->evaltemplate("PanierPage","main");

$template->out();


?>
