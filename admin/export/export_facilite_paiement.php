<?php

ini_set("display_errors","on");

$host="www.mon-espace-cuisson.com";

// requis pour la recuperation des parametres d'execution
extract($_REQUEST);
extract($_COOKIE);

include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");
include("xlexport.php");
include("ooo.php");

// connection a la base de donnee, attention semble requis pour l'utilisation librairie ooo.php ! 
$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

// Verification du user qui execute le script, pour eviter des telechargement par le site web
// A desactiver pour les tests manuels !!!
if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
      }
    }
}else
{
  print_r ("Erreur !! ");
  exit;
}


// Creation de l'Objet Open Office
$ooo = new ooo();

$lastMonth = "".date("Ym") - 1;

// Nom du fichier
$ooo->name = "FacilitePaiement_".$lastMonth;
// Nom de l'onglet
$ooo_1=new ooo_table("FacilitePaiement_".$lastMonth);

// Mise en place du formatage ligne et colonnes
$ooo_1->set_row(1,"8mm");

$ooo_1->set_col(1,"3cm"); //Fact id
$ooo_1->set_col(2,"3cm"); // Fact num
$ooo_1->set_col(3,"3cm"); // Reglmt num
$ooo_1->set_col(4,"8cm"); // Nom
$ooo_1->set_col(5,"8cm"); // Prenom
$ooo_1->set_col(6,"10cm");// Adresse
$ooo_1->set_col(7,"3cm"); // CP
$ooo_1->set_col(8,"8cm"); // Ville
$ooo_1->set_col(9,"4cm"); // Montant
$ooo_1->set_col(10,"4cm"); // Reglmt date
$ooo_1->set_col(11,"3cm"); // Reglmt Type

// Recuperation des factures en facilites de reglements pour le mois N-1 (precedent)
$sql="select f.facturid, f.numerofacture, concat("."0".",r.reglement_id) as numeroreglement, f.nom, f.prenom, f.adresse, f.codepostal, f.ville, r.reglement_montant, r.reglement_date, m.libelleFR ";
$sql .="from reglement r, facture f, moyenpaiement m ";
$sql .="where r.facturid = f.facturid and r.reglement_moyen = m.moyenid and f.moyenid=99 ";
$sql .="and f.valide in (1,5,7,8) and r.reglement_moyen in (1,10,12,8,9,13,16) ";
$sql .="and month(r.reglement_date)= (month(curdate())-1) ";
$sql .="and year(r.reglement_date)= year(curdate()) ";
$sql .="order by f.facturid, f.nom, f.numerofacture";

$requete=$DB_site->query($sql);

// Insertion dans l'onglet ooo_1 des donnees recuperees
$i=2;
while($ligne=$DB_site->fetch_array($requete))
 {
    if(isset($ligne))
    {
        $r=array();
        $r[]=new ooo_cell($ligne['facturid']);
        $factNumber = $ligne['numerofacture'] != 15550 ? $ligne['numerofacture'] : '';
        $r[] = new ooo_cell($factNumber);
        $r[]=new ooo_cell($ligne['numeroreglement']);
        $r[]=new ooo_cell($ligne['nom']);
        $r[]=new ooo_cell($ligne['prenom']);
        $r[]=new ooo_cell($ligne['adresse']);
        $r[]=new ooo_cell($ligne['codepostal']);
        $r[]=new ooo_cell($ligne['ville']);
        $r[]=new ooo_cell($ligne['reglement_montant']);
        $r[]=new ooo_cell($ligne['reglement_date']);
        $r[]=new ooo_cell($ligne['libelleFR']);

        /*$r[]=new ooo_cell(date("d/m/Y",$ligne['date']));
        $r[]=new ooo_cell(utf8_encode($ligne['destinataire']));
        $r[]=new ooo_cell($ligne['facturid']);
        $r[]=new ooo_cell(date("d/m/Y",$ligne['dateexpedition']));
        $r[]=new ooo_cell($ligneht,"prix","currency");*/

        $ooo_1->addrow($i,$r);
        $i++;
    }
}

// Creation de l'entete des colonnes
$cells=array();
$cells[]=new ooo_cell("N° Commande","cell");
$cells[]=new ooo_cell("Fact Numero","cell");
$cells[]=new ooo_cell("Reglmt Numero","cell");
$cells[]=new ooo_cell("Nom","cell");
$cells[]=new ooo_cell("Prenom","cell");
$cells[]=new ooo_cell("Adresse","cell");
$cells[]=new ooo_cell("C. Postal","cell");
$cells[]=new ooo_cell("Ville","cell");
$cells[]=new ooo_cell("Montant TTC","cell");
$cells[]=new ooo_cell("Reglmt Date","cell");
$cells[]=new ooo_cell("Reglmt Type","cell");

$ooo_1->addrow(1,$cells);

// Ajout de l'onglet ooo_1 dans le classeur ooo
$ooo->addtable($ooo_1);

// Creation du fichier .ods contenant les donnees
$ooo->generate();

?>