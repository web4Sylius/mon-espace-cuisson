<?php
require ("global.php");

$instit_id = $_GET['id'];

$libelle     = "libelle".$template->langue;
$contenu     = "contenu".$template->langue;
$description = "description".$template->langue;

$url_actuelle = $_SERVER[REQUEST_URI];
$url_actuelle = explode(",",$url_actuelle);
$last_niveau  = count($url_actuelle) - 1;

// on récupère le réel dernier niveau de l'URL
/*if (substr($_SERVER[REQUEST_URI],-4)!="html"){
    $instit=$DB_site->query_first("SELECT $libelle, bandeau_defilant FROM institutionnel WHERE institutionnelid = '$id'");
    if (url_rewrite($instit[$libelle]) == "")
        $url_reel = "-.htm";
    else
        $url_reel = url_rewrite($instit[$libelle]).".htm";
    
    if ($url_actuelle[$last_niveau] != $url_reel){
        if ($url_actuelle[$last_niveau] == "")
            $url_redirect = "int,$id,$url_reel.htm";
        else
            $url_redirect = "$host".str_replace("$url_actuelle[$last_niveau]","$url_reel",$_SERVER[REQUEST_URI]);
        header("Status: 301 Moved Permanently");
        header("Location: http://$url_redirect");
        exit(); 
    }
}*/

//Détails sur la page instit à afficher
$sql = "SELECT ".$libelle.", libelleFR,libelleEN, bandeau_defilant, site_code, active, pro";
$sql .= " FROM institutionnel";
$sql .= " WHERE institutionnelid = '".$instit_id."'";
$instit = $DB_site->query_first($sql);

//Vérification de l'URL => Si elle est fausse, redirection sur la bonne
preg_match("/\/int,([0-9]+),([0-9a-z\-]+).htm/",$_SERVER['REQUEST_URI'],$match);
if($match[2] != url_rewrite($instit[$libelle])){
    
    $url_redirect = "/int,".$instit_id.",".url_rewrite($instit[$libelle]).".htm";
    
    header("Status: 301 Moved Permanently");
    header("Location: ".$url_redirect);
    exit(); 
}
if($instit['active'] == 0 ){
    header("Status: 301 Moved Permanently");
    header("Location: /");
    exit(); 
}
// sur quel site la page doit etre affcihée
if($template->pro)
    $state = array(2,3);
else
    $state =array(1,3);

if(!in_array($instit['pro'], $state)){
    header("Status: 301 Moved Permanently");
    header("Location: /");
    exit(); 
}

//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url]){
        if($instit['libelle'.$deflangue]!='')
            $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/int,".$instit_id.",".url_rewrite($instit['libelle'.$deflangue]).".htm\">\n";
        }
    }
}

//Avec fermeture du UK, les pages instit sont redirigées vers leur version française si elle existe. Si ce n'est pas le cas, redirection vers la home
if($instit['site_code'] == "uk"){
    $redir_page=$DB_site->query_first("SELECT institutionnelid, $libelle FROM institutionnel WHERE libelleFR='".mysqli_real_escape_string($DB_site->link_id,$instit['libelleFR'])."' AND site_code='base'");
    if($redir_page['institutionnelid']!="")
            $template->redirect($template->mkurl("institutionnel",$redir_page['institutionnelid'],$redir_page),301);
    else
            $template->redirect("/",301);
}

$ref_title       = "ref_title".$template->langue;
$ref_description = "ref_description".$template->langue ;

if (isset($instit_id) and $instit_id != ""){
    
    //Récupération de la liste des pages instit du 1er menu du footer, pour les afficher dans la page
    $sql = "SELECT *, libelle".$template->langue." as libelle";
    $sql .= " FROM institutionnel";
    $sql .= " WHERE menu IN (1,6)";
    $sql .= " AND active = '1'";
    $sql .= " AND site_code = '".$template->site_code()."'";
    $sql .= " AND libelle".$template->langue." != ''";
    $sql .= " AND pro IN(".implode(",", $state).")";
    $sql .= " AND institutionnelparent = 0";
    $sql .= " ORDER BY position";
    $req = $DB_site->query($sql);
    $i = 1;
    while($row = $DB_site->fetch_array($req)){

        $url_menu = $template->mkurl("institutionnel",$row['institutionnelid'],$row);

        if($i==1){
            $menu .= "<ul><li style=\"margin-top:10px;margin-bottom:2px;font-weight:bold;\">".($row['sans_lien']==false?"<a href=\"".$url_menu."\" ".($row['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$row['libelle']."</a>":$row['libelle'])."</li>";
            $childs = "SELECT *, libelle".$template->langue." as libelle FROM institutionnel WHERE institutionnelparent = ".$row['institutionnelid']." AND active = '1' AND pro IN(".implode(",", $state).")";
            $req_childs = $DB_site->query($childs);
            while($child = $DB_site->fetch_array($req_childs)){
                $url_menu_child = $template->mkurl("institutionnel",$child['institutionnelid'],$child);
                $menu .= "<li style=\"margin-left:16px;list-style-type:circle;\">".($child['sans_lien']==false?"<a href=\"".$url_menu_child."\" ".($child['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$child['libelle']."</a>":$child['libelle'])."</li>";
            }
        }
        elseif($i==2){
            $menu .= "<li style=\"margin-top:10px;margin-bottom:2px;font-weight:bold;\">".($row['sans_lien']==false?"<a href=\"".$url_menu."\" ".($row['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$row['libelle']."</a>":$row['libelle'])."</li>";
            $childs = "SELECT *, libelle".$template->langue." as libelle FROM institutionnel WHERE institutionnelparent = ".$row['institutionnelid']." AND active = '1' AND pro IN(".implode(",", $state).")";
            $req_childs = $DB_site->query($childs);
            while($child = $DB_site->fetch_array($req_childs)){
                $url_menu_child = $template->mkurl("institutionnel",$child['institutionnelid'],$child);
                $menu .= "<li style=\"margin-left:16px;list-style-type:circle;\">".($child['sans_lien']==false?"<a href=\"".$url_menu_child."\" ".($child['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$child['libelle']."</a>":$child['libelle'])."</li>";
            }
            $menu .= "</ul>";
        }
        elseif($i==4){
            $menu .= "<ul><li style=\"margin-top:10px;margin-bottom:2px;font-weight:bold;\">".($row['sans_lien']==false?"<a href=\"".$url_menu."\" ".($row['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$row['libelle']."</a>":$row['libelle'])."</li>";
            $childs = "SELECT *, libelle".$template->langue." as libelle FROM institutionnel WHERE institutionnelparent = ".$row['institutionnelid']." AND active = '1' AND pro IN(".implode(",", $state).")";
            $req_childs = $DB_site->query($childs);
            while($child = $DB_site->fetch_array($req_childs)){
                $url_menu_child = $template->mkurl("institutionnel",$child['institutionnelid'],$child);
                $menu .= "<li style=\"margin-left:16px;list-style-type:circle;\">".($child['sans_lien']==false?"<a href=\"".$url_menu_child."\" ".($child['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$child['libelle']."</a>":$child['libelle'])."</li>";
            }

            $menu .= "</ul>";
        }
        elseif($i==5){
            $menu .= "<ul><li style=\"margin-top:10px;margin-bottom:2px;font-weight:bold;\">".($row['sans_lien']==false?"<a href=\"".$url_menu."\" ".($row['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$row['libelle']."</a>":$row['libelle'])."</li>";
            $childs = "SELECT *, libelle".$template->langue." as libelle FROM institutionnel WHERE institutionnelparent = ".$row['institutionnelid']." AND active = '1' AND pro IN(".implode(",", $state).")";
            $req_childs = $DB_site->query($childs);
            while($child = $DB_site->fetch_array($req_childs)){
                $url_menu_child = $template->mkurl("institutionnel",$child['institutionnelid'],$child);
                $menu .= "<li style=\"margin-left:16px;list-style-type:circle;\">".($child['sans_lien']==false?"<a href=\"".$url_menu_child."\" ".($child['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$child['libelle']."</a>":$child['libelle'])."</li>";
            }
            $menu .= "</ul>";
        }
        else{
            $menu .= "<ul><li style=\"margin-top:10px;margin-bottom:2px;font-weight:bold;\">".($row['sans_lien']==false?"<a href=\"".$url_menu."\" ".($row['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$row['libelle']."</a>":$row['libelle'])."</li>";
            $childs = "SELECT *, libelle".$template->langue." as libelle FROM institutionnel WHERE institutionnelparent = ".$row['institutionnelid']." AND active = '1' AND pro IN(".implode(",", $state).")";
            $req_childs = $DB_site->query($childs);
            while($child = $DB_site->fetch_array($req_childs)){
                $url_menu_child = $template->mkurl("institutionnel",$child['institutionnelid'],$child);
                $menu .= "<li style=\"margin-left:16px;list-style-type:circle;\">".($child['sans_lien']==false?"<a href=\"".$url_menu_child."\" ".($child['institutionnelid'] == $instit_id ? 'class=\"active\"' : '').">".$child['libelle']."</a>":$child['libelle'])."</li>";
            }
            $menu .= "</ul>";
        }

        $i++;

    }

    $template->parts["institumenu"] = "<div class=\"menu_instit col-xs-12 menu_instit_padding\">".$menu."</div>";

    //Traitement du formulaire de la page instit de M. Vial
    if(!empty($_POST)){

        $msgErreur = '';
        if(!isset($_POST['nom']) || empty($_POST['nom'])){
            $msgErreur = $template->translate("/client/erreur_nom");
        }
        if(!isset($_POST['prenom']) || empty($_POST['prenom'])){
            $msgErreur = $template->translate("/client/erreur_prenom");
        }
        if(!isset($_POST['tel']) || empty($_POST['tel'])){
            $msgErreur = $template->translate("/client/erreur_telephone");
        }
        if(!isset($_POST['mail']) || empty($_POST['mail']) || !filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)){
            $msgErreur = $template->translate("/client/indiquer_mail_valide");
        }
        if(!isset($_POST['msg']) || empty($_POST['msg'])){
            $msgErreur = 'Veuillez saisir un message';
        }
        if(isset($_POST['cp']) && !empty($_POST['cp'])){
            $msgErreur = '...';
        }

        //Si informations formulaire OK, on envoi le message
        if(empty($msgErreur)) {

		    $mail = new email();
            $mail->expediteur   = $_POST['mail'];
            $mail->expname      = $_POST['nom'];
            $mail->destinataire = $template->translate("/site/[site_code]/mail_commercial");
            $mail->subject      = $template->translate("/site/nom")." > Contact Mathieu Vial";
            $mail->body ="<p>Bonjour,</p>
            <p>Une personne vient de remplir le formulaire pour contacter Mathieu Vial</p>
            <p><b>Nom, prénom</b> : ".strip_tags($_POST['nom'])." ".strip_tags($_POST['prenom'])."
            <br><b>E-mail expéditeur</b> : ".strip_tags($_POST['mail'])."
            <br><b>Téléphone</b> : ".strip_tags($_POST['tel'])."
            <br><b>Message</b> : ".strip_tags($_POST['msg']);
		    $mail->sendmail();
            
            $template->parts["message"] = "<p style=\"color:red; text-align: center;\">".$template->translate("/contact/envoi_mvial")."</p>";

        } else {
            $template->parts["post_nom"]     = $_POST['nom'];
            $template->parts["post_prenom"]  = $_POST['prenom'];
            $template->parts["post_mail"]    = $_POST['mail'];
            $template->parts["post_tel"]     = $_POST['tel'];
            $template->parts["post_message"] = $_POST['msg'];
            $template->parts["message"]      = "<p style=\"color:red; text-align: center;\">".$msgErreur."</p>";
	    }
	}

    if ($instit_id == 0){ // espace liens
        
        $contenu = "Espace-liens" ;
        $institutionnel[$libelle] = "Espace liens" ;
        $institutionnel[$ref_title]="Espace liens";
        $institutionnel[$ref_description]="";

        $liens = $DB_site->query("SELECT * FROM liens order by lien asc");
        if ($DB_site->num_rows($liens) > 0){
            $institutionnel[$contenu] .= "<table align=\"center\" width=\"753\" style=\"background-color:red;\">" ;
        }else{
            $institutionnel[$contenu] .= "" ;
        }
        $institutionnel[$contenu] .= "<br></center>" ;
        if ($liens_dynamiques == 1){
            $institutionnel[$contenu] = "" ;
            eval( "\$TemplateLiens = \"$templatecache[Liens]\";" );
        }

        eval("\$framedroite=stripslashes(\"$templatecache[FrameDroite]\");");

    }elseif ($id == 500){

        $contenu = "Espace-partenaires" ;
        $institutionnel[$libelle] = "Espace partenaires" ;
        $institutionnel[$ref_title]="Espace partenaires";
        $institutionnel[$ref_description]="";

        $liens = $DB_site->query("SELECT * FROM partenaires WHERE liste=1 order by lien asc");
        if ($DB_site->num_rows($liens) > 0){
            $institutionnel[$contenu] .= "<table align=\"center\">" ;
            while ($lien=$DB_site->fetch_array($liens)){
                $lien_image = "" ;
                if ($lien[logo] != ""){
                    $lien_image = "<a href=\"$lien[lien]\" target=\"_blank\"><img src=\"/configurations/$host/images/partenaires/$lien[partenaireid].$lien[logo]\"></a><br><br>" ;
                    if($lien['logo']=="swf"){
                        $swf="configurations/$host/images/partenaires/$lien[partenaireid].$lien[logo]";
                        $size=getimagesize($swf);
                        $lien_image="<object type=\"application/x-shockwave-flash\" data=\"$swf\" width=\"$size[0]\" height=\"$size[1]\">
                            <param name=\"movie\" value=\"$swf\">
                            <param name=\"quality\" value=\"high\">
                            <param name=\"wmode\" value=\"transparent\">
                        </object>";
                    }
                }else{
                    $lien_image = "" ;
                }

                $institutionnel[$contenu] .= "<tr><td width=\"20%\" align=\"left\" valign=\"middle\" style=\"padding-top:10px;\">$lien_image<br><a href=\"$lien[lien]\" target=\"_blank\">$lien[lien]</a></td><td align=\"justify\" valign=\"middle\" style=\"padding-left:5px; padding-bottom:10px;\">$lien[$description] $lien[script]</td></tr>" ;
            }

            $institutionnel[$contenu] .= "</table>" ;
        }else{
            $institutionnel[$contenu] .= "Aucun partenaire n'a été rentré" ;
        }

        $institutionnel[$contenu] .= "<br></center>" ;
        
    } elseif ($id == 501){
        $contenu = "Espace-partenaires" ;
        $institutionnel[$libelle] = "Les femmes et Ma cave à Vin" ;
        $institutionnel[$ref_title]="Les femmes et Ma Cave à Vin";
        $institutionnel[$ref_description]="Les femmes sont de plus en plus nombreuses à acheter, à consommer, mais aussi à pratiquer les métiers du vin. Leur approche vis-à-vis du vin est différente : plus sensible, plus libre et plus féminine et donc complémentaire à celle des hommes.";
        $institutionnel[$contenu] .="<table height=\"140\">
        <tr><td style=\"padding:10px; text-align:justify;\">Retrouvez sur cette page, nos partenaires féminins qui travaillent dans le domaine du vin ou, tout simplement, qui aiment le vin. Cette rubrique est animée par Magalie et Laurence.</b></p>
        <p>&nbsp;</p>
        <p>Les femmes sont de plus en plus nombreuses à acheter, à consommer, mais aussi à pratiquer les métiers du vin. Leur approche vis-à-vis du vin est différente : plus sensible, plus libre et plus féminine et donc complémentaire à celle des hommes.
        <br><br>Il est donc évident que le site Ma Cave A Vin s'adresse aussi aux femmes qui souhaitent pouvoir stocker, faire vieillir et conserver leurs bouteilles de vin afin de les consommer dans les meilleures conditions.</td>
        <td style=\"padding:10px;\"><img src=\"/images/templates/bannieres/banniere 159x129_femmes.jpg\" align=\"right\"></td></tr>
        </table><hr>";

        $liens = $DB_site->query("SELECT * FROM partenaires WHERE liste=2 order by lien asc");
        if ($DB_site->num_rows($liens) > 0){
            $institutionnel[$contenu] .= "<table align=\"center\">" ;
            while ($lien=$DB_site->fetch_array($liens)){
                $lien_image = "" ;
                if ($lien[logo] != ""){
                    $lien_image = "<a href=\"$lien[lien]\" target=\"_blank\"><img src=\"/images/partenaires/$lien[partenaireid].$lien[logo]\"></a><br><br>" ;
                    if($lien['logo']=="swf"){
                        $swf="configurations/".$host."/images/partenaires/$lien[partenaireid].$lien[logo]";
                        $size=getimagesize($swf);
                        $lien_image="<object type=\"application/x-shockwave-flash\" data=\"$swf\" width=\"$size[0]\" height=\"$size[1]\">
                        <param name=\"movie\" value=\"$swf\">
                        <param name=\"quality\" value=\"high\">
                        <param name=\"wmode\" value=\"transparent\">
                        </object>";
		            }
                }else{
                    $lien_image = "" ;
                }

                $lien[$description]=nl2br($lien[$description]);
                $institutionnel[$contenu] .= "<tr><td width=\"20%\" align=\"left\" valign=\"top\" style=\"padding-top:10px;\">$lien_image<br><a href=\"$lien[lien]\" target=\"_blank\">$lien[lien]</a></td><td align=\"left\" valign=\"middle\" style=\"padding-left:5px;\">$lien[$description] $lien[script]</td></tr>" ;
            }

            $institutionnel[$contenu] .= "</table>" ;
        }else{
            $institutionnel[$contenu] .= "Aucun partenaire n'a été rentré" ;
        }

        $institutionnel[$contenu] .= "<br></center>" ;
      
    }else{
        $institutionnel = $DB_site->query_first(" SELECT $contenu,$libelle,$ref_title,$ref_description,institutionnelid, bandeau_defilant FROM institutionnel WHERE institutionnelid = '$id' ");
    }
}else{
    $TemplateInstitutionnelBit="";
    // espace liens
        
    $espaceliensid = 0 ;
    $espacelienslibelle = "$multilangue[espacelienlibelle]" ;
    $url_rewriting=url_rewrite($espacelienslibelle);
    eval( "\$TemplateEspaceLiens .= \"$templatecache[EspaceLiens]\";" );
    eval( "\$TemplateEspaceLiensBit .= \"$templatecache[EspaceLiensBit]\";" );
        
    $institutionnelts = $DB_site->query("SELECT institutionnelid , $contenu , $libelle FROM institutionnel 
                                        WHERE active='1' ");
    while ($institutionnelt=$DB_site->fetch_array($institutionnelts)){
        $url_rewriting=url_rewrite($institutionnelt[$libelle]);
        eval( "\$TemplateInstitutionnelBit .= \"$templatecache[InstitutionnelBit]\";" );
     }
    //$params=$DB_site->query_first("SELECT pagedocuments_active from parametresgeneraux");

    $formus=$DB_site->query("SELECT * from formulaire where formulaireid != '1' and actif = '1'");
    if ($DB_site->num_rows($formus) > 0){
        while ($formu=$DB_site->fetch_array($formus)){
            $lien = "for,$formu[formulaireid],".url_rewrite($formu[nom]).".html" ;
            $TemplateFormulaire .= "<a href=\"$lien\">Formulaire personnel</a><br>" ;
	    }
    }
}
//Récupération des articles correspondants à la page
$limit = " LIMIT 4";
if(intval($instit_id) == 221 || intval($instit_id) == 234) $limit ="";
if($template->pro){ //si on est sur le site pro 
    $articles_page =$DB_site->query("SELECT i_a.artid FROM institutionnel_article i_a INNER JOIN article a USING(artid) WHERE i_a.institutionnelid=".intval($instit_id)." AND a.active=\"1\" AND (a.pro=\"3\" OR a.pro=\"2\")   ORDER BY ordre ASC".$limit);
}else{// si on est sur le site particulier
    $articles_page =$DB_site->query("SELECT i_a.artid FROM institutionnel_article i_a INNER JOIN article a USING(artid) WHERE i_a.institutionnelid=".intval($instit_id)." AND a.active=\"1\" AND (a.pro =\"1\" OR a.pro=\"3\")  ORDER BY ordre ASC".$limit);
}

$c=1;
while($row = $DB_site->fetch_array($articles_page) ){
    $class = '';
    
    //affichages des articles dans la page (template InstituPage.html)
    vignette(intval($row['artid']),"categorie",$class);
    $c++;
}
//on recupère les blocs de la page :
$template->parts["content"]='';
$path = "/images/bloc_instit/";
$image='';
$blocs = $DB_site->query("SELECT * FROM institutionnel_bloc WHERE institutionnelid=".$id." AND langue ='".$template->langue."' ORDER by ordre ASC");
while ($leBloc = $DB_site->fetch_array($blocs)) {
  $eval = $DB_site->query_first("SELECT bloc_eval FROM institutionnel_bloc_type WHERE ibtid =".$leBloc['typeid']."");
  if(($leBloc['image'] != 'NULL') && ($leBloc['image'] != '')){
    $imageSrc = $path.$leBloc['institutionnel_blocid'].".".$leBloc['image'];
	$imagesize = getimagesize($rootpath."configurations/".HOST.$imageSrc);
    if($leBloc['url_img']!==''){
        ///ici, pour les images avec un lien téléchargeable (comme les cgv)
        preg_match('/(\|\|\|).*(\|\|\|)/', $leBloc['url_img'],$url_host);
        $leBloc['url_img']=str_replace($url_host,$host,$leBloc['url_img']);
        //
      $image = "<a href=\"".$leBloc['url_img']."\" style=\"display:block;\"><img src=\"".$imageSrc."\" width=\"".$imagesize[0]."\" height=\"".$imagesize[1]."\" alt=\"".$leBloc['alt_img']."\"></a>";
    }
    else{
      $image = "<img src=\"".$imageSrc."\" width=\"".$imagesize[0]."\" height=\"".$imagesize[1]."\" alt=\"".$leBloc['alt_img']."\">";
    }
  }
  $content ="";
  if($leBloc['typeid'] == 3 || $leBloc['typeid'] == 12 ) 
    $contenu = str_replace(array('<p>','</p>','<ul>','</ul>'), '', $leBloc['contenu']);
  else
    $contenu = $leBloc['contenu'] ;

  $recherche = array('{contenu}','{image}');
  $remplace = array($contenu,$image);
  $content  = "<div class=\"row bloc_row\">";
  $content .= str_replace($recherche, $remplace, $eval[0]);
  $content .= "</div>";
  $template->parts["content"] .= html_entity_decode($content);
}

$encarts=encarts($template->site_code(),$template->langue,"instit",$id);
$i=1;
foreach($encarts as $encart){
    $template->parts['marginright0'] = '';
    $template->parts['boots'] = 'encarts_width';
    if($template->pro)
        $template->parts['conseil-1'] = $template->translate("/encart/conseil_pro");
    else
        $template->parts['conseil-1'] = $template->translate("/encart/conseil_part");

    if($i==3) $template->parts['boots'] .= " hidden-xs hidden-sm";
    if($i==4){ 
      $template->parts['marginright0'] = 'margin-right: 0;';
      $template->parts['boots'] .= " hidden-xs hidden-sm hidden-md encarts_disp";
    }
    $template->evaltemplate($encart,"encarts");
    $i++;
}

//$template->parts["content"]=$template->short_links($institutionnel[$contenu]);
$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$institutionnel[$libelle]."</span><meta property=\"position\" content=\"2\"></span>";
$template->parts["title"]=$institutionnel[$libelle];

include("barrefixe.php");
$template->parts["style_institutionnel"] = 'div.institutionnel img { max-width: 260px; }';

$template->pageparams($host,$institutionnel[$ref_title],$institutionnel[$ref_description]);
$template->evaltemplate("InstituPage","main");
$template->parts["remarketing"]=google_remarketing('',"siteview",'');
$template->out();


?>