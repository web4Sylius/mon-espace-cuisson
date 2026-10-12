<?php

$rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";

include_once($_SERVER['DOCUMENT_ROOT']."/global.php");
$pdf=new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle("Bon d'achat");
$pdf->SetAuthor("mon-espace-cuisson");
$pdf->SetAutoPageBreak(false,10);

//if(!isset($_GET['facturid'])) exit;

$facture=$DB_site->query_first("SELECT *, UNIX_TIMESTAMP(datefacture) AS datefacture FROM facture f, pays p WHERE facturid = '$facturid' and f.paysid = p.paysid");
$pays=$DB_site->query_first("SELECT * FROM pays WHERE paysid=".$facture['paysid']);

if($facture['cadeauid']=="0") exit;

$code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
if($code_caviste['caviste']=="" || $code_caviste['type']!=1) exit;

$caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid=".$code_caviste['caviste']);

$coord_commercant=$DB_site->query_first("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux");

// $pdf->Image($rootpath."configurations/$host/factures/logo.jpg",15,10,50);
$pdf->image($rootpath."configurations/$host/images/templates/logo2010.jpg",120,0,80);

$pdf->SetFont('Arial','',8);
$pdf->setxy(15,30);
$pdf->multicell(100,4,"$coord_commercant[raison_sociale]\n$coord_commercant[adresse]\n$coord_commercant[codepostal] $coord_commercant[ville]\nFRANCE\nTel : $coord_commercant[telephone]\nFax : $coord_commercant[fax]\ncontact@mon-espace-cuisson.com\nN° SIRET $coord_commercant[siret]");

$pdf->setfont('arial','',10);
$pdf->setxy(120,48);
$pdf->multicell(100,5,($facture['raisonsociale']!=""?$facture['raisonsociale']."\n":"")."$facture[nom] $facture[prenom]\n$facture[adresse]\n$facture[codepostal] $facture[ville]\n$facture[libelle]");

$pdf->setfont('arial','',10);
$pdf->setxy(80,73);
$pdf->multicell(180,5,"Bonjour,");
$pdf->setxy(15,80);
$pdf->multicell(180,4,"Comme convenu, votre commande n°".$facture['facturid']." passée le ".date("d/m/Y",$facture['datefacture'])." sur notre site $host avec le code partenaire ".$code_caviste['code']." vous donne droit au bon d’achat ci-dessous.");

$pdf->setxy(15,90);
$pdf->multicell(180,5,"Ce bon d’achat est à imprimer et à présenter lors de votre achat chez notre partenaire :");

$pdf->setfont('arial','b',10);
$pdf->setxy(0,96);
$pdf->multicell(210,5,"$caviste[raisonsociale]\n$caviste[adresse]\n$caviste[codepostal] $caviste[ville] $caviste[pays]",0,'C',0);

$pdf->setfont('arial','',10);
$pdf->setxy(15,115);
$pdf->multicell(180,4,"PS : Ce bon d’achat ne peut pas donner droit à la fourniture d'une facture par notre partenaire. A défaut, les ".$facture['montantcadeau']." € seront déduits de la facture. Il est non remboursable, non échangeable et valable chez notre partenaire.");

$pdf->setxy(15,125);
$pdf->multicell(180,5,"Nous vous remercions de votre confiance et de l’intérêt que vous portez à nos produits.");

$pdf->setxy(15,133);
$pdf->multicell(180,5,"A très bientôt");

$pdf->setfont('arial','',10);
$pdf->settextcolor(102,20,22);
$pdf->setxy(15,139);
$pdf->multicell(100,4,"L’équipe de Mon Espace Cuisson");

$yoff=145;
$pdf->image($rootpath."configurations/$host/images/templates/bondachat.jpg",5,$yoff,200);

$pdf->setfont('arial','b',32);
$pdf->setxy(110,$yoff+5);
$pdf->multicell(90,15,"Bon d'achat",0,'C',0);

$pdf->setfont('arial','b',12.5);
$pdf->settextcolor(255);
$pdf->setxy(15.5,42.5+$yoff);
$pdf->multicell(180,6,"Ma Cave à Vin est heureux de vous offrir ce bon d'achat d'un montant de :");
$pdf->settextcolor(0);
$pdf->setxy(15,42+$yoff);
$pdf->multicell(180,6,"Ma Cave à Vin est heureux de vous offrir ce bon d'achat d'un montant de :");

$pdf->settextcolor(0);
$pdf->setfillcolor(255);
$pdf->setdrawcolor(112,21,26);
$pdf->SetLineWidth(1.5);
$pdf->rect(145,50+$yoff,50,15,'FD');
$pdf->setfont('arial','B',22);
$pdf->setxy(145,50+$yoff);
$pdf->multicell(50,15,$facture['montantcadeau']." €",0,'C',0);

$pdf->SetLineWidth(0.2);
$pdf->rect(15,50+$yoff,120,15,'FD');
$pdf->setfont('arial','B',12);
$pdf->setxy(15,50+$yoff);
$pdf->multicell(120,15,val2str($facture['montantcadeau']),0,'C',0);

$pdf->setfont('arial','',12);
$pdf->setxy(16,65+$yoff);
$pdf->multicell(120,10,"Utilisable par :");
$pdf->setfont('arial','B',12);
$pdf->setxy(45,68+$yoff);
$pdf->multicell(120,5,strtoupper($facture['nom'])." ".ucfirst($facture['prenom']));
$pdf->setxy(45,73+$yoff);
$pdf->setfont('arial','',12);
$pdf->multicell(100,5,$facture['adresse']."\n".$facture['codepostal']." ".$facture['ville']."\n".$pays['libelle']);

$pdf->setfont('arial','',12);
$pdf->setxy(50,89+$yoff);
$pdf->multicell(115,10,"A valoir chez :",0,'C',0);

$pdf->setxy(50,97+$yoff);
$pdf->setfont('arial','B',14);
$pdf->settextcolor(169,123,80);
$pdf->multicell(115,6,"$caviste[raisonsociale]\n$caviste[adresse]\n$caviste[codepostal] $caviste[ville]\n$caviste[pays]",0,'C',0);

$pdf->settextcolor(0);
$pdf->setfont('arial','',12);
$pdf->setxy(150,116+$yoff);
$pdf->multicell(100,8,"Fait à Lyon, le ".date("d/m/Y"));

$pdf->settextcolor(102,20,22);
$pdf->setfont('arial','',14);
$pdf->setxy(140,129+$yoff);
$pdf->multicell(150,8,"N° $facture[facturid] / $code_caviste[code]");

$pdf->settextcolor(0);
$pdf->setfont('arial','',10);
$pdf->setxy(10,128+$yoff);
$fin=mktime()+182*86400;
$pdf->multicell(200,10,"Valable une fois par la personne indiquée jusqu'au ".date("d/m/Y",$fin));

$pdf->setxy(5,288);
$pdf->SetFont('Arial','',6);
$pdf->Cell(200,4,"$host - Copyright © 2004 - ".date("Y")." Acilyon - Tous droits réservés - Siège Social Lyon - France - Rcs Lyon 452 947 385 - Tva FR 13 452 947 385",0,0,'C',1);
$pdf->Ln(3);
$pdf->Cell(200,4,"Réserve de propriété : nous nous réservons la propriété des marchandises jusqu'au paiement intégral de nos factures (loi N°80335 du 12 mai 1980)",0,0,'C',1);

$pdf->output();



function decimale($nb){
         if(strlen($nb)>2) $nb=substr($nb,0,2);
         if($nb==0) return " euro";
         if(substr($nb,0,1)=="0"){
             return " euro ".$nb." ct".($nb>1?"s":"");
         }else
             if($nb<10) $nb*=10;

         return " euro ".$nb." ct".($nb>1?"s":"");
}

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

function centaine($nb){
         if($nb==0) return "";
         if($nb==1) return "cent";
         return unite($nb,false)." cent";
}

function millier($nb){
        if($nb==0) return "";
        if($nb==1) return "mille";
        if($nb<100) return unite($nb)." mille";
        return centaine(substr($nb,0,1))." ".unite(substr($nb,1,2),false)." mille";
        return $nb;
}

function million($nb){
        if($nb==0) return "";
        if($nb==1) return "un million";
        if($nb<100) return unite($nb)." millions";
        return centaine(substr($nb,0,1))." ".unite(substr($nb,1,2),false)." millions";
        return $nb;
}


function val2str($nb){
         $moins="";
         if($nb<0) $moins="moins ";
         $nb=str_replace(",",".",$nb);
         $nb=abs($nb);
         $int=(int) abs($nb);
         $dec=0;
         if(strpos($nb,".")>-1) $dec=substr($nb,strpos($nb,".")+1);

         if($nb<100) return $moins.unite($int).decimale($dec);
         $nblen=strlen($int);
         if($nb<1000) return $moins.centaine(substr($int,$nblen-3,1))." ".unite(substr($int,$nblen-2,2),false).decimale($dec);
         if($nb<1000000) return $moins.millier(substr($int,0,$nblen-3))." ".centaine(substr($int,$nblen-3,1))." ".unite(substr($int,$nblen-2,2),false).decimale($dec);
         return $moins.million(substr($int,0,$nblen-6))." ".millier(substr($int,$nblen-6,$nblen-3))." ".centaine(substr($int,$nblen-3,1))." ".unite(substr($int,$nblen-2,2),false).decimale($dec);
}

?>