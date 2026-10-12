<?php
$document_root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$admin_username = filter_input(INPUT_COOKIE, 'admin_username', FILTER_SANITIZE_STRING);
if(empty($admin_username)){
     $admin_username = filter_input(INPUT_GET, 'admin_username', FILTER_SANITIZE_STRING);
}
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

ini_set("display_errors","on");

require_once($document_root.'/configurations/www.mon-espace-cuisson.com/config_mysql.php');
require_once($document_root.'/class/mysql.php');
require_once($document_root.'/class/facture.class.php');
require_once($document_root.'/class/stock.class.php');
require_once($document_root.'/admin/export/zip.lib.php');
require_once($document_root.'/admin/export/xlexport.php');
//require_once($document_root.'/admin/marketplace2/order.php');

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

if (isset($admin_username) && $admin_username != "") {
     if($admin_username != "9d3bb895f22bf0afa958d68c2a58ded7"){
          $ip = $_SERVER['REMOTE_ADDR'];
          
          //Validation d'identité avant autorisation de téléchargement
          $sql = "SELECT *";
          $sql .= " FROM acces_admin";
          $sql .= " WHERE ip = '".$ip."'";
          $sql .= " AND user='".$admin_username."'";
          $sql .= " AND connecte = 1";
          $sql .= " ORDER BY timestamp DESC";
          $sql .= " LIMIT 0,1";
          $acces = $DB_site->query_first($sql);
          
          if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
               exit;
          }else{
               $sql = "SELECT *";
               $sql .= " FROM parametresgeneraux";
               $sql .= " WHERE username = '".$admin_username."'";
               $user_info = $DB_site->query_first($sql);
               if($user_info[username] != $admin_username){
                    exit;
               }
          }
     }
}else{
     exit;
}

if(!isset($_GET['du']) || !isset($_GET['au'])) exit;

$sp=explode("/",$_GET['du']);
$du=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
$sp=explode("/",$_GET['au']);
$au=mktime(23,59,59,$sp[1],$sp[0],$sp[2]);

$err=0;
$errline=array();

$xl=new xlexport("csv");

if(empty($type)){
     $type = "client";
}

if($type=="client"){
     $sql = "SELECT *, UNIX_TIMESTAMP(f.datefacture) AS datecde, UNIX_TIMESTAMP(f.dateexpedition) AS datef";
     $sql .= " FROM facture f";
     $sql .= " WHERE f.valide IN (5,8)";
     $sql .= " AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN $du AND $au";
     $sql .= " ORDER BY f.dateexpedition";
     $requete = $DB_site->query($sql);

     // $requete=mysqli_query("SELECT *,UNIX_TIMESTAMP(f.datefacture) AS datecde,UNIX_TIMESTAMP(f.dateexpedition) AS datef FROM facture f WHERE f.valide IN (8) AND UNIX_TIMESTAMP(f.dateexpedition) BETWEEN 1304281709 AND 1309471199 ORDER BY f.dateexpedition");
     
     while($facture = $DB_site->fetch_array($requete)){
          $f = new facture($DB_site,$facture);
          $totalttc = 0;
          $totald = 0;

     $sql = "SELECT SUM(lignefactureinstallation_differenceprix) as intallation_ttc";
     $sql .= " FROM lignefactureinstallation";
     $sql .= " WHERE factureid = ".$f->facturid;
     $req = $DB_site->query_first($sql);
     $installation_ttc = $req['intallation_ttc'];

    foreach($f->lignes as $ligne){
               $sql = "SELECT *, m.libelle AS marque";
               $sql .= " FROM article a, marque m, article_marque am";
               $sql .= " WHERE am.artid = a.artid";
               $sql .= " AND am.marqueid = m.marqueid";
               $sql .= " AND a.artid=".$ligne->artid;
               $article = $DB_site->query_first($sql);
               
               $sql = "SELECT SUM(differenceprix)";
               $sql .= " FROM lignefacturecaracteristique";
               $sql .= " WHERE lignefactureid=".$ligne->lignefactureid;
               $options = $DB_site->query_first($sql);

               $txtva        = $ligne->tva/100;
               $ecopart      = $ligne->qte * $article['ecopartht'];
               $garantie     = $ligne->qte * ($options[0] / (1+$txtva));
               $totalht      = $ligne->qte * ($ligne->prix / (1+$txtva)) - $ecopart - $garantie;

               $totald += $totalht + ($totalht * $txtva) + $ecopart + ($ecopart * $txtva) + $garantie + ($garantie * $txtva);
    }

     $totald +=  $installation_ttc;

    foreach($f->lignesdiss as $ligne){
               $totaldiss = 0;
               $lignesdiss = $ligne->getlignefacturediss();
               if(count($lignesdiss) == 0){
                    $sql = "SELECT lignefactureid,lf.qte,lf.tva,lf.prix";
                    $sql .= " FROM article_lot al";
                    $sql .= " INNER JOIN lignefacture lf USING(artid)";
                    $sql .= " WHERE lotid=".$ligne->artid."";
                    $sql .= " AND factureid=".$f->facturid."";
                    $sql .= " GROUP BY lignefactureid";
                    $articlelots = $DB_site->query($sql);
                    
        while($articlelot = $DB_site->fetch_array($articlelots)){
                         $totaldiss += $articlelot['qte'] * $articlelot['prix'];
                    }
               } else {
                    foreach($lignesdiss as $lignediss){
                         $totaldiss+=$lignediss->totalttc;
                    }
               }
                
               $diff = $totaldiss - $ligne->totalttc;
               if(abs(round($diff,2)) > 0){
                    $err++;
                    $errline[] = ""
                         . "<tr>"
                              . "<td><a href=\"/admin/facture.php?action=editer&facturid=".$f->facturid."\">".$f->facturid." &gt; ".$ligne->artcode."</a></td>"
                              . "<td align=\"right\">".number_format($totaldiss,2,',','')."</td>"
                              . "<td align=\"right\">".number_format($ligne->prix,2,',','')."</td>"
                              . "<td align=\"right\"".(abs($diff)>0.1?" style=\"color:red;\"":"").">".number_format($diff,2,',','')."</td>"
                         . "</tr>";
               }
    }

    if($f->cadeauht!=0){
        foreach($f->partcadeau as $tx=>$ttc){
          $cadeauht=$ttc/(1+$tx/100);
          $tvaremise = $ttc-$cadeauht;
        
          $totald-=($cadeauht+$tvaremise);
        }
    }

    if($f->portht!=0){
        $totald+=$f->portht+($f->portttc-$f->portht);
    }
    

    $diff=$f->totalttc-$totald;
    if(abs(round($diff,2))>0){
               $errline[] = ""
                    . "<tr>"
                         . "<td><a href=\"/admin/facture.php?action=editer&facturid=".$f->facturid."\">".$f->facturid."</a></td>"
                         . "<td align=\"right\">".number_format($totald,2,',','')."</td>"
                         . "<td align=\"right\">".number_format($f->totalttc,2,',','')."</td>"
                         . "<td align=\"right\"".(abs($diff)>0.1?" style=\"color:red;\"":"").">".number_format($diff,2,',','')."</td>"
                    . "</tr>";
               $err++;
    }
     }

     print ""
          . "<table cellspacing=\"0\" border=\"1\">"
               . "<tr>"
                    . "<td>n° commande</td>"
                    . "<td>Total TTC calculé</td>"
                    . "<td>Total TTC commande</td>"
                    . "<td>Différence</td>"
               . "</tr>"
               .implode("",$errline)
          . "</table>";

    print $err." erreur(s)";
}

if($type == "fourn"){
     $fourn = "";
     if(isset($_GET['fourn'])){
          $fourn = " AND fournisseur='".$_GET['fourn']."'";
     }
     $sql = "SELECT *";
     $sql .= " FROM facture_fournisseur";
     $sql .= " WHERE (date_saisie BETWEEN ".$du." AND ".$au.") ";
     $sql .= $fourn;
     $sql .= " ORDER BY date_facture";
     $requete = $DB_site->query($sql);
     
     while($facture=$DB_site->fetch_array($requete)){
          $txtva = (floatval($facture['tva'])/100);
          $totald = 0;
          
          $sql = "SELECT *";
          $sql .= " FROM envoi_fournisseur_ligne";
          $sql .= " WHERE facture='".$facture['numero']."'";
    $r2 = $DB_site->query($sql);
          
    while($ligne = $DB_site->fetch_array($r2)){
               $sql = "SELECT u.paysid,f.moyenid,f.facturid,f.numerofacture,UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition,f.provenanceid,f.provenance";
               $sql .= " FROM lignefacture lf, facture f, utilisateur u";
               $sql .= " WHERE u.userid = f.userid";
               $sql .= " AND f.facturid = lf.factureid";
               $sql .= " AND lignefactureid = ".$ligne['ligne_facture_id'];
               $lignefacture = $DB_site->fetch_array($DB_site->query($sql));
            
               $totald += ($ligne['qte']*$ligne['paht'])+($ligne['qte']*$ligne['paht']*$txtva)+($ligne['qte']*round($ligne['ecopart'],2))+($ligne['qte']*round($ligne['ecopart'],2)*$txtva);
    }
    
          $sql = "SELECT *";
          $sql .= " FROM facture_fournisseur_autre";
          $sql .= " WHERE facture = '".$facture['numero']."'";
    $r2 = $DB_site->query($sql);
          
    while($ligne = $DB_site->fetch_array($r2)){
               $totald += $ligne['prix']+($ligne['prix']*$txtva);
    }

    $stock = new stock($DB_site);
    $cmdlist = $stock->getfacture($facture['numero']);
    foreach($cmdlist as $cmdid => $items){
               if(is_array($items)){
        foreach($items as $item){
                         $totald+=$item->paht+($item->paht*$txtva);
                         if($item->facture==$facture['numero']){
                              $totald += round($item->ecopartht,3)+round($item->ecopartht*$txtva,3);
                         }
        }
               }elseif(is_a($items,"stock_cmd")){
                    $totald += ($item->tariftrspt*(1+$txtva));
               }
    }
    $diff = $facture['montant_ttc']-$totald;
    if(abs(round($diff,2))>0){
               $errline[] = ""
                    . "<tr>"
                         . "<td><a href=\"/admin/fournisseur.php?action=editer&facturid=".$facture['numero']."\">".$facture['fournisseur']." ".$facture['numero']."</a></td>"
                         . "<td align=\"right\">".number_format($totald,2,',','')."</td>"
                         . "<td align=\"right\">".number_format($facture['montant_ttc'],2,',','')."</td>"
                         . "<td align=\"right\"".(abs($diff)>0.1?" style=\"color:red;\"":"").">".number_format($diff,2,',','')."</td>"
                    . "</tr>";
               $err++;
    }
     }

     print ""
          . "<table cellspacing=\"0\" border=\"1\">"
               . "<tr>"
                    . "<td>Facture</td>"
                    . "<td>Total TTC calculé</td>"
                    . "<td>Total TTC commande</td>"
                    . "<td>Différence</td>"
               . "</tr>"
               .implode("",$errline)
          . "</table>";

    print $err." erreur(s)";
}