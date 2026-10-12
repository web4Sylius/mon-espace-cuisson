<?php
if(!function_exists('vignette_mail')){
     function vignette_mail($artid, $center_vignette = false){
     
          global $DB_site2;
          global $template;
          global $sitepro;

          //Récupération des données concernant l'article
          $article = getInfosArticle($artid);
          if($article[0]=="") return "";

          //Récupération des données concernant la marque
          $mq = getInfosMarque($artid);

          //Mise en forme de la vignette article
          formatVignette($article, $mq, 'conseil');

          //Récupération tarifs
          $taxtxt = ($sitepro?"/site/ht":"/site/ttc");
          $article_prices = article_prices($article['artid'],$template->tva_ref(),$template->site_code(),$sitepro);

          $sql = "SELECT COUNT(*)";
          $sql .= " FROM promo";
          $sql .= " WHERE artid = ".$article['artid'];
          $promo = $DB_site2->query_first($sql);

          $sql = "SELECT COUNT(*)";
          $sql .= " FROM secondchoix";
          $sql .= " WHERE artid = ".$article['artid'];
          $secondchoix = $DB_site2->query_first($sql);

          $pc = "";
          if(round($article_prices['prix'])!=round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){

              $strikeout = "<s>".$template->price_format($article_prices['prixconseille'])." ".$template->translate($taxtxt,"U")."</s>";
              $pc        = "<img src=\"http://".$_SERVER['HTTP_HOST']."/images/mail/reduc.php?size=40&pc=".$article_prices['remise']."\">";
          }

          $template->parts['prix'] = ""
          . "<table width=\"100%\" border=\"0\" style=\"font-size:12px;\">"
               . "<tr>"
                    . "<td height=\"20\">".$strikeout."</td>"
                    . "<td rowspan=\"2\">".$pc."</td>"
               . "</tr>"
               . "<tr>"
                    . "<td height=\"20\" style=\"color:#E5124F;font-size:13px;font-weight:bold;\">".$template->price_format($article_prices['prix'])." ".$template->translate($taxtxt,"U")."</td>"
               . "</tr>"
          . "</table>";

          $template->parts["articles_content"] .= "<td ".($center_vignette ? 'align=\"center\"' : '')." text-align=\"center\" ".($center_vignette ? 'colspan=\"2\"' : '').">";
          $template->evaltemplate("vignetteMail","articles_content");
          $template->parts["articles_content"] .= "</td>";
     }
}

if(!function_exists('getInfosArticle')){
     function getInfosArticle($artid){
     
          global $DB_site2;
          global $template;

          $sqlSub = "SELECT active";
          $sql .= " FROM article_site";
          $sql .= " WHERE artid = ".intval($artid);
          $sql .= " AND site_code = '".$template->site_code()."'";
          $sql .= " LIMIT 1";

          $sql = "SELECT artid, typearticleid, libelle".$template->langue." AS libelle, artcode, (".$sqlSub.") AS active, commandable, gamme".$template->langue." AS gamme, modele, nbr_temperature, capacite_bouteilles, coloris, hauteur, largeur, profondeur";
          $sql .= " FROM article";
          $sql .= " WHERE artid = ".intval($artid);
          $article = $DB_site2->query_first($sql);

          return $article;
     }
}

if(!function_exists('getInfosMarque')){
     function getInfosMarque($artid){
     
          global $DB_site2;
          global $template;

          $sql = "SELECT libelle, m.marqueid, image, m.type".$template->langue." AS mtype";
          $sql .= " FROM marque m, article_marque am";
          $sql .= " WHERE m.marqueid = am.marqueid";
          $sql .= " AND am.artid = '".$artid."'";
          $mq = $DB_site2->query_first($sql) ;  

          return $mq;
     }
}

if(!function_exists('formatVignette')){
     function formatVignette($article, $mq, $type){
     
          global $DB_site2;
          global $template;
          global $sitepro;

          switch($type){
               case 'conseil':

                    $width_logomarque      = '90';
                    $max_height_logomarque = '24';
                    $img_height            = 202;
                    $width_limit           = 132;

                    break;
               case 'selection':

                    $width_logomarque      = '128';
                    $max_height_logomarque = '40';
                    $img_height            = 220;
                    $width_limit           = 160;

                    break;
          }

          $host = $template->translate("/site/[site_code]/url");
          if($template->parts["pro"]!="") $host = str_replace("www","pro",$host);

          $template->parts["arturl"]     = $host."/art,".$article['artid'].",".url_rewrite($article['libelle']).".htm";
          $template->parts['libelle']    = $article['libelle'];
          $template->parts['ref']        = strtoupper($mq['libelle']." ".$article['gamme']."<br>".$article['artcode']);
          $template->parts["logomarque"] = "<img src=\"../configurations/www.ma-cave-a-vin.fr/images/marques/br/".$mq['marqueid'].".jpg\" alt=\"".$mq['mtype']."\" width=\"".$width_logomarque."\" style=\"width:".$width_logomarque."px; max-height:".$max_height_logomarque."px;\">";

          //Vignette article
          $sql = "SELECT *";
          $sql .= " FROM articlephoto";
          $sql .= " WHERE artid = ".$article['artid'];
          $sql .= " AND visible = 1";
          $sql .= " AND (langue='' OR langue='".$template->langue."')";
          $autreimg = $DB_site2->query_first($sql);

          $autre = "";
          if($autreimg!="") $autre = "_".$autreimg['articlephotoid'];
          $imgpath = "../configurations/www.ma-cave-a-vin.fr/images/produits/br/".$article['artid'].$autre.".jpg";
          $size   = @getimagesize($_SERVER['DOCUMENT_ROOT'].substr($imgpath,2));
          if(!empty($size)){
               $height = $img_height;
               $width  = round($height*$size[0]/$size[1]);
               if($width > $width_limit){
                   $width  = $width_limit;
                   $height = round($width*$size[1]/$size[0]);
               }
               $template->parts["artimg"]="<a href=\"".$template->parts["arturl"]."\"><img src=\"".$imgpath."\" alt=\"".$article['libelle']." ".$mq[0]."\" width=\"".$width."\" height=\"".$height."\"></a>";
          } else {
               $template->parts["artimg"]="";
          }
          

     }
}

if(!function_exists('selection_vignette')){
     function selection_vignette($artid){
     
          global $DB_site2;
          global $template;
          global $sitepro;

          //Récupération des données concernant l'article
          $article = getInfosArticle($artid);
          if($article[0]=="") return "";

          //Récupération des données concernant la marque
          $mq = getInfosMarque($artid);

          //Mise en forme de la vignette article
          formatVignette($article, $mq, 'conseil');

          //Récupération tarifs
          $taxtxt = ($sitepro?"/site/ht":"/site/ttc");
          $article_prices = article_prices($article['artid'],$template->tva_ref(),$template->site_code(),$sitepro);

          $sql = "SELECT COUNT(*)";
          $sql .= " FROM promo";
          $sql .= " WHERE artid = ".$article['artid'];
          $promo = $DB_site2->query_first($sql);

          $sql = "SELECT COUNT(*)";
          $sql .= " FROM secondchoix";
          $sql .= " WHERE artid = ".$article['artid'];
          $secondchoix = $DB_site2->query_first($sql);

          $template->parts['pc'] = "";

          if(round($article_prices['prix']) != round($article_prices['prixconseille']) && ($promo[0]>0 || $secondchoix[0]>0)){

               $strikeout = "<s style=\"font-size:12px;\">".$template->price_format($article_prices['prixconseille'])." ".$template->translate($taxtxt,"U")."</s>";
               $template->parts['pc'] = "<img src=\"http://".$_SERVER['HTTP_HOST']."/images/mail/reduc.php?size=80&pc=".$article_prices['remise']."\">";
          }

          $template->parts['prix'] = ""
          . "<div style=\"margin:10px 0 0 0;\">"
               . $strikeout
               . "<br>"
               . "<span style=\"color:#E5124F;font-weight:bold;font-size:16px;\">".$template->price_format($article_prices['prix'])." ".$template->translate($taxtxt,"U")."</span>"
          . "</div>";

          $desc = "";
          if($article['nbr_temperature'] != "") $desc .= "<li>".$template->translate($article['nbr_temperature'])." ".$template->translate("/article/vignette/temperatures")."</li>";
          if($article['capacite_bouteilles']!="") $desc .= "<li>".$template->translate("/article/vignette/capacite","F")." : ".$article['capacite_bouteilles']." ".$template->translate($article['capacite_unite'])."</li>";
          if($article['coloris'] != "") $desc .= "<li>".$template->translate("/article/vignette/coloris","F")." : ".$template->translate($article['coloris'])."</li>";
          if($article['hauteur']>0) $desc .= "<li>".$template->translate("/article/vignette/dim","F")." : ".sprintf($template->translate("/article/champs/dimensions_format"),$article['hauteur'],$article['largeur'],$article['profondeur'])."</li>";

          $template->parts["liste"] = "<ul style=\"padding:0;font-size:12px;list-style-type:none;\">".$desc."</ul>";

          $template->evaltemplate("SelectionMail","selection");
     }
}

//Dernier mail de relance avec classement du contact en "perdu"
if(isset($_POST['last_relance']) && $_POST['last_relance'] == 1){
     
     $email_id = $_POST['email_relance'];
     $etat_id  = $_POST['statut_relance'];

     //Construction du mail
     $sql = "SELECT langue, site_code";
     $sql .= " FROM utilisateur";
     $sql .= " WHERE userid = ".intval($_POST['utilisateur_id']);
     $langue_user = $DB_site2->query_first($sql);
     $lang = $langue_user['langue'];
     
     $template = new template($lang,$DB_site2);
     $template->changesite($langue_user['site_code'] == '' ? 'base' : $langue_user['site_code'], $lang);
     
     //Préparation du template de mail (header classique/pro, FR/EN...)
     $sitepro = false;
     $pro     = "";
     $template->parts["pro"] = "";
     $urlsite = $template->translate("/site/[site_code]/url");
     $urlsite = str_replace("http://","",$urlsite);
     $template->parts["urlsite"] = $urlsite;
     
     //Récupération des données de l'admin "CONTACT"
     $sql = "SELECT p.nom_contact, ac.nom_contact".$lang." as nom_contact_new, ac.mail".$lang." AS mail, ac.tel".$lang." AS tel";
     $sql .= " FROM admin_contact ac";
     $sql .= " INNER JOIN parametresgeneraux p USING(username)";
     $sql .= " WHERE ac.username = 'contact'";
     $sql .= " AND ac.site_code = 'base'";
     $admin_contact = $DB_site2->query_first($sql);
     
     //Mise en forme signature
     $template->parts["telephone"]    = $admin_contact['tel'];
     $template->parts["mail_contact"] = $admin_contact['mail'];
     $template->parts["utilisateur"]  = $admin_contact['nom_contact_new'];
     
     //Récupération du contenu du mail à intégrer
     $sql = "SELECT emailid, libelle, sujet".$lang." AS sujet, contenu".$lang." AS contenu";
     $sql .= " FROM emails_clients";
     $sql .= " WHERE emailid = ".$email_id;
     $email_client = $DB_site2->query_first($sql);
     
     //Remplissage du corps du mail
     $template->parts['texte'] = $email_client['contenu'];
     
     //Récupération de la liste des articles vus
     $sql = "SELECT DISTINCT(artid)";
     $sql .= " FROM article_vu";
     $sql .= " WHERE userid = ".$utilisateur['userid'];
     $sql .= " ORDER BY nb DESC";
     $sql .= " LIMIT 0,6";
     $req = $DB_site2->query($sql);
     $search_config = true;
     if($DB_site2->num_rows($req) > 0){
          $search_config = false;
          while($row = $DB_site2->fetch_array($req)){
               $tabArticle[] = $row;
          }
     }
     
     //Si aucun article vu, on récupère les articles du configurateur
     if($search_config){
          //Récupération de la liste des articles des configurations
          $sql = "SELECT artids";
          $sql .= " FROM configurateur";
          $sql .= " WHERE userid = ".$utilisateur['userid'];
          $sql .= " AND artids != ''";
          $sql .= " ORDER BY date DESC";
          $sql .= " LIMIT 0,1";
          $tabResult = $DB_site2->query_first($sql);
          if(is_array($tabResult) && count($tabResult) > 0){
               $liste_article = unserialize($tabResult['artids']);
               $tabArticle = array();
               $cpt = 0;
               foreach($liste_article as $l_article){
                    if(!in_array($l_article, $tabArticle) && $cpt < 6){
                         $tabArticle[]['artid'] = $l_article;
                         $cpt++;
                    }
               }
          }
     }

     //Insertion, s'il y a plus d'1 article
     if(count($tabArticle) > 1){
          
          $template->parts["selection"] = "";
          $template->parts["articles_content"] = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
          $i = 0;
          $cpt = 0;
          foreach($tabArticle as $l_article){
                    
               $center_vignette = false;
               if($i == 2){

                    $template->parts["articles_content"] .= ""
                    . "</tr>"
                    . "<tr>"
                         . "<td colspan=\"2\" style=\"height:1px;border-top:solid 1px #808080;\"></td>"
                    . "</tr>"
                    . "<tr>";

                    $i=0;

                    if(!isset($tabArticle[$cpt+1])) $center_vignette = true;
               }

               $i++;

               vignette_mail($l_article['artid'], $center_vignette);

               $cpt++;
          }
          $template->parts["articles_content"] .= "</table>";
          $template->evaltemplate("MailCommercial_articles","articles");
          
     } else if(count($tabArticle) == 1) {
          
          $template->parts["articles"] = "";
          selection_vignette($tabArticle[0]['artid']);
     }
     
     //Mise en place du footer
     $template->parts["footer"] = ""
     . "<tr>"
          . "<td align=\"center\"><img src=\"../images/mail/".$template->site_code()."/".$template->langue."/footer.jpg\" usemap=\"#footer\"></td>"
     . "</tr>";
     $template->parts["ext_footer"] = "<br>".$template->translate("/mail/asterisques");
     
     //Vidage des dernières variables, que l'on ne veut pas envoyer dans le mail
     $template->parts["codepromo"] = "";
     $template->parts["univers"] = "";
     
     $template->parts["pourquoi"] = ""
               . "<tr>"
                    . "<td height=\"10\"></td>"
               . "</tr>"
               . "<tr>"
                    . "<td height=\"50\"><img src=\"../images/mail/".$template->site_code()."/".$template->langue."/titre_pourquoi".$pro.".png\" align=\"center\"></td>"
               . "</tr>"
               . "<tr>"
                    . "<td><img src=\"../images/mail/".$template->site_code()."/".$template->langue."/pourquoi_choisir".$pro.".png\"></td>"
               . "</tr>";
     $template->parts['tracking'] = "#CLI".$utilisateur['userid']."#";
     $template->evaltemplate("MailCommercial_marques","marques");
     $template->evaltemplate("MailCommercial_signature","texte");
     $template->evaltemplate("MailCommercial","mailcommercial");
     $body = stripslashes($template->parts['mailcommercial']);

     if(isset($apercu) && $apercu){
          ?>
		  <html>
			  <head>
				<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf8">
				<style type="text/css">
					span.dore { font-weight:bold; color:#A88D5C; }
					span.rouge { display:inline!important; font-weight:bold; color:#800000!important; }
			   </style> 
			  </head>
			  <body>
			  <form action="/admin/utilisateur.php?action=editer&userid=<?php echo $_POST['utilisateur_id']; ?>" method="post" style="text-align: center;" onsubmit="if(check_envoi() === false){ return false; }">
				   <input type="hidden" name="last_relance" value="<?php echo $_POST['last_relance']; ?>">
				   <input type="hidden" name="email_relance" value="<?php echo $_POST['email_relance']; ?>">
				   <input type="hidden" name="utilisateur_id" value="<?php echo $_POST['utilisateur_id']; ?>">
				   <input type="hidden" name="statut_relance" value="<?php echo $_POST['statut_relance']; ?>">
				   <input type="submit" value="Envoyer">
				   <div id="apercu_relance" style="margin: 10px 0;">
						<?php
						echo str_replace('../',SITE_URL.'/',$body);
						?>
				   </div>
				   <input type="submit" value="Envoyer">
			  </form>
			  </body>
		  </html>
          <?php
     } else {
          //Envoi du mail
          $mail=new email();
          $mail->stylesheet($_SERVER['DOCUMENT_ROOT']."/configurations/www.ma-cave-a-vin.fr/mails.css");
          $mail->expediteur   = $template->translate("/site/[site_code]/mail_expediteur");
          $mail->expname      = $template->translate("/site/nom");
          $mail->destinataire = $utilisateur['mail'];
          $mail->copymode     = "bcc";
          $mail->to           = "contact@ma-cave-a-vin.fr";

          $mail->subject = stripslashes($email_client['sujet']);
          $mail->body    = stripslashes($body);

          $mail->sendmail();
          
          $heure_mail  = time() - 1;
          $heure_suivi = time() + 1;
          
          //Ajout du mail dans le suivi du client et classement du client "perdu"
          $sql = "INSERT INTO mail_outbox SET";
          $sql .= " date         = ".$heure_mail;
          $sql .= ", utilisateur = '".$user_info['username']."'";
          $sql .= ", `to`        = '".$utilisateur['mail'].";".$user_info['mailFR']."'";
          $sql .= ", subject     = '".addslashes($mail->subject)."'";
          $sql .= ", body        = '".addslashes($mail->body)."'";
          $sql .= ", pj          = ''";
          $sql .= ", userid     = '".$utilisateur['userid']."'";
          $sql .= ", uniqueid    = '".$mail->uniqueid."'";
          $sql .= ", compta      ='non'";
          $DB_site2->query($sql);

          if($etat_id >= 0){

               $aStatuts = array(
                    0 => 'Perdu',
                    1 => 'En cours',
                    4 => 'Dossier sous-traité',
                    5 => 'En commande (plus de suivi)',
                    6 => 'Sans conseils'
                    );

               //Détails du suivi
               $suivi_details = ""
               . "Le client <a href=\"http://www.ma-cave-a-vin.fr/admin/utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">".$utilisateur['prenom']." ".$utilisateur['nom']."</a> a été marqué \"".$aStatuts[$etat_id]."\" par ".$user_info['username']." le ".date('d/m/Y à H:i', mktime(date('H'), date('i'), date('s'), date('n'), date('j'), date('Y')))."<p>";
               
               if($etat_id == 0) $suivi_details .= "</p>Cause de la perte : projet abandonné<br>Lieu d'achat :<br>Article :<br>Marque :<br>Prix : 0";
          
               //Insertion dans suivi
               $sql = "INSERT INTO suivi_client SET";
               $sql .= " type          = ''";
               $sql .= ", moyen        = ''";
               $sql .= ", destinataire = '0'";
               $sql .= ", userid       = '".$utilisateur['userid']."'";
               $sql .= ", commercial   = '".$user_info['username']."'";
               $sql .= ", date         = ".$heure_suivi;
               $sql .= ", suivi        = '".mysqli_real_escape_string($DB_site2->link_id,$suivi_details)."'";
               $sql .= ", rappel       = '0'";
               $sql .= ", rappeltype   = 'int'";
               $sql .= ", jour         = '0'";
               //Mise à jour de l'état uniquement si demande de modification
               $sql .= ", etat         = '".$etat_id."'";
               $sql .= ", pour         = ''";
               $sql .= ", hide         = '0'";
               $DB_site2->query($sql);
               
               if($etat_id == 0){

                    $sql = "INSERT INTO causeperte SET";
                    $sql .= " userid       = '".$utilisateur['userid']."'";
                    $sql .= ", date        = ".$heure_suivi;
                    $sql .= ", cause       = '2'";
                    $sql .= ", lieu        = ''";
                    $sql .= ", article     = ''";
                    $sql .= ", marque      = ''";
                    $sql .= ", prix        = '0'";
                    $sql .= ", commentaire = ''";
                    $DB_site2->query($sql);
               }
          }
          ?>
          <script type="text/javascript">

               window.opener.location.reload(true);
               window.close();

          </script>
          <?php
     }
     ?>
     <script type="text/javascript">

          function charge_loader(div){
               document.getElementById(div).style.height = '400px';
               document.getElementById(div).style.margin = '300px auto 0';
               document.getElementById(div).innerHTML = '<img src="<?php echo BACK_URL; ?>/images/loader.gif" width="60">';
          }

          function check_envoi(){
               var reponse = confirm('Confirmer l\'envoi ?');
               if(reponse){
                   charge_loader('apercu_relance');
               } else {
                    return false;
               }
          }

     </script>
     <?php
}