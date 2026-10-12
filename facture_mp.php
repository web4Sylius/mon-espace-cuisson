<?php

//include $DOCUMENT_ROOT."global.php";

error_reporting(E_ALL ^ E_NOTICE);

if (!isset($langue))
    {
    $langue = "FR" ;
    }

$rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
$rootpath="./";
$HTTP_HOST="www.mon-espace-cuisson.com";
$host="www.mon-espace-cuisson.com";

if(!isset($_GET['join'])){
  require_once ("global.php");
}else
  $facturid=$mporder->id;

/*
require ("config_arobases.php");
$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$dbname2;
$DB_site2->server="localhost";
$DB_site2->user=$dbusername2;
$DB_site2->password=$dbpassword2;
$DB_site2->connect();
$dbpassword2="";*/

function CleanFiles($dir) 
    {
    $t=time();
    $h=opendir($dir);
    while($file=readdir($h))
        {
        if(substr($file,0,3)=='tmp' and substr($file,-4)=='.pdf')
            {
            $path=$dir.'/'.$file;
            if($t-filemtime($path)>30)
                {
                @unlink($path);
                }
            }
        }
    closedir($h);
    } 

function french_date($joindate )
    { 
    $monthes = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'); 
    $days = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'); 
    return $days[date("w",$joindate)].' '.date("d",$joindate).' '.$monthes[date("n",$joindate) ].' '.date("Y",$joindate); 
    }

$marketplaces=array(""=>"Autre","01"=>"Amazon","04"=>"Price Minister","05"=>"Pixmania","06"=>"Windill","07"=>"Rue du Commerce","08"=>"Edengo","09"=>"Ebuyclub","10"=>"Ebay","11"=>"Top Achat");


$libelle = "libelle$langue";

$facture=$DB_site->query_first("SELECT * FROM mp_orders WHERE id = '$facturid'");
$numerofacture = $facture['billing_number'];
$moyenpaiement=$DB_site->query_first("SELECT * FROM moyenpaiement WHERE moyenid = '$facture[moyenid]'");
$coord_commercant=$DB_site->query_first("SELECT mail_contact mail, raison_sociale, nom_contact, adresse, cp codepostal, ville, siret, tva, telephone, fax FROM parametresgeneraux");
$paysfacture=$DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".$facture['billing_country']);
$payslivraison=$DB_site->query_first("SELECT libelle FROM pays WHERE paysid=".$facture['shipping_country']);

$nomboutique = ${$title} ;
$dectva=1.196;

if ($passe==$facture['passefacture']) 
    {
    define('FPDF_FONTPATH',"$rootpath/class/font/");
    include("$rootpath/class/fpdf.php");

    class PDF extends FPDF 
        {
        function Header()
            {
            global $host,$rootpath, $langue,$cde, $nomboutique ;
            include $rootpath."configurations/www.mon-espace-cuisson.com/config.php";
            include $rootpath."configurations/www.mon-espace-cuisson.com/langue$langue.php";
            $this->SetFont('Arial','B',25);
            $this->Cell(100);
            if (@file_exists($rootpath."configurations/www.mon-espace-cuisson.com/factures/logo.jpg"))
                {
                $this->Image($rootpath."configurations/www.mon-espace-cuisson.com/factures/logo.jpg",15,10,50);
                }
            $this->setXY(60,24);
            if($cde=="1") 
                {
                $this->Cell(80,35,"Bon de livraison",0,0,'C');
                } 
            else 
                {
                $this->Cell(80,35,"Facture",0,0,'C');
                }
            $this->Ln(5);
            $this->SetFont('Arial','',8);
            $this->Cell(100);
            //$this->Cell(80,25,"$nomboutique - $host",0,0,'C');
            $this->image($rootpath."configurations/www.mon-espace-cuisson.com/images/templates/logo2010.jpg",120,0,80);
            $this->setTextColor(83,37,40);
            $this->setXY(135,26);
            $this->SetFont('Arial','',12);
            //$this->cell(60,5,"www.mon-espace-cuisson.com");
            $this->Ln(20);
            }
        function Footer() 
            {
        //  $this->Image("$rootpath/configurations/$host/factures/bas_page.jpg",10,280,190);
            }

        function NbLines($w, $txt){
        //Computes the number of lines a MultiCell of width w will take
                   $cw=&$this->CurrentFont['cw'];
                   if($w==0)
                       $w=$this->w-$this->rMargin-$this->x;
                   $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
                   $s=str_replace("\r", '', $txt);
                   $nb=strlen($s);
                   if($nb>0 and $s[$nb-1]=="\n")
                   $nb--;
                   $sep=-1;
                   $i=0;
                   $j=0;
                   $l=0;
                   $nl=1;
                   while($i<$nb){
                       $c=$s[$i];
                       if($c=="\n"){
                           $i++;
                           $sep=-1;
                           $j=$i;
                           $l=0;
                           $nl++;
                           continue;
                       }
                       if($c==' ')
                                $sep=$i;
                       $l+=$cw[$c];
                       if($l>$wmax){
                           if($sep==-1){
                               if($i==$j)
                                   $i++;
                           }else
                               $i=$sep+1;
                           $sep=-1;
                           $j=$i;
                           $l=0;
                           $nl++;
                       }else
                           $i++;
                   }
                   return $nl;
        }
}

    $pdf=new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetTitle("$multilangue[facture]");
    $pdf->SetAuthor("Arobases");
    $pdf->SetAutoPageBreak(true,10);
    //$pdf->SetFillColor(230,230,230);

    $pdf->SetFont('Arial','BU',14);
    //$pdf->Cell(100,5,"$multilangue[expediteur] :");

     if ($cde == "1") {
       $adresse["nom"]=$facture["shipping_last_name"];
       $adresse["prenom"]=$facture["shipping_first_name"];
       $adresse["raisonsociale"]=$facture["shipping_company"];
       $adresse["adresse"]=$facture["shipping_address_1"];
       $adresse["codepostal"]=$facture["shipping_zip"];
       $adresse["ville"]=$facture["shipping_city"];
	$adresse["pays"]=$payslivraison["libelle"];
       $adresse["telephone"]=$facture["shipping_phone"];

      // $pdf->Cell(60,5,"Adresse de livraison :");
    }else{
        $adresse["nom"]=$facture["billing_last_name"];
       $adresse["prenom"]=$facture["billing_first_name"];
       $adresse["raisonsociale"]=$facture["billing_company"];
       $adresse["adresse"]=$facture["billing_address_1"];
       $adresse["codepostal"]=$facture["billing_zip"];
       $adresse["ville"]=$facture["billing_city"];
	$adresse["pays"]=$paysfacture["libelle"];
       $adresse["telephone"]=$facture["billing_phone"];
      //  $pdf->Cell(60,5,"Adresse de facturation :");
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(100,5,"$coord_commercant[raison_sociale]");
    $pdf->Cell(100,5,"$adresse[raisonsociale]");
    $pdf->Ln(5);

    $pdf->Cell(100,5,"$coord_commercant[adresse]");
    $pdf->Cell(60,5,"$adresse[nom] $adresse[prenom]");
    $pdf->Ln(5);

    $pdf->Cell(100,5,"$coord_commercant[codepostal] $coord_commercant[ville]");
    $pdf->Cell(60,5,"$adresse[adresse]");
    $pdf->Ln(5);

    $pdf->Cell(100,5,"FRANCE");
    $pdf->Cell(60,5,"$adresse[codepostal] $adresse[ville]");
    $pdf->Ln(6);

    $pdf->Cell(100,5,"Tel : $coord_commercant[telephone]");
    $pdf->Cell(60,5,"$adresse[pays]");
    $pdf->Ln(5);

    $pdf->Cell(100,5,"Fax : $coord_commercant[fax]");
    $pdf->Cell(60,5,"Tél : $adresse[telephone]");
    $pdf->Ln(5);

    $ly=$pdf->getY();
    $pdf->Cell(100,5,"pr@mon-espace-cuisson.com");
    if($cde==1){
        $pdf->setXY(110,$ly+5);
        $pdf->multiCell(80,4,"Informations pour transporteur : $facture[shipping_details]");
    }
    $pdf->setY($ly);
    $pdf->Ln(5);

    $pdf->Cell(100,5,"N° SIRET $coord_commercant[siret]");
    //$pdf->Cell(60,5,"",0,0,'',1);
    //$pdf->Cell(60,5,"",0,0,'',1);


    $wcol=array(95,20,20,10,20,10,20);
    if($cde==1) $wcol=array(165,0,0,0,0,20,0);

    toptable($pdf);

    //$pdf->Cell(30,5,"$multilangue[ref]",1,0,'C',1);
    //$pdf->Cell(80,5,"$multilangue[designation]",1,0,'C',1);
    //$pdf->Cell(20,5,"$multilangue[puht]",1,0,'C',1);
    //$pdf->Cell(20,5,"$multilangue[quantite]",1,0,'C',1);
    //$pdf->Cell(15,5,"$multilangue[tva]",1,0,'C',1);
    //$pdf->Cell(25,5,"$multilangue[montantttc]",1,0,'C',1);


    $lignefactures=$DB_site->query("SELECT * FROM mp_order_line WHERE `order` = '$facturid' order by id");
    $total = 0 ;
    $tva = 0 ;
    $i=0;
    $j=1;
    $poidstotal=0;

    while ($lignefacture=$DB_site->fetch_array($lignefactures))
        {

        $diffprix=0;
        $des_caracts = array(); ;
        $libmodele = $DB_site->query_first("SELECT * FROM article WHERE artcode = '$lignefacture[ref_tin]'");
        $libmarque=$DB_site->query_first("SELECT * from article_marque am, marque m where am.marqueid = m.marqueid and am.artid = '$libmodele[artid]'");
        $caracts=$DB_site->query("SELECT * FROM mp_lignefacturecaracteristique WHERE lignefactureid = '$lignefacture[id]' ORDER BY libelle_caract");
        while ($caract=$DB_site->fetch_array($caracts))
            {
            $des_caracts["$caract[libelle_caract] : $caract[libelle_caractval]"] = $caract[differenceprix] ;
            $diffprix+=$caract[differenceprix];
            }
        ksort($des_caracts);
        $diffprixht=round($diffprix/$dectva,2);

        $designation = html_entity_decode($lignefacture[product_name]." ".$libmarque[libelle].($libmodele[gamme]!=""?" ".$libmodele[gamme]:"")." Ref. ".$libmodele[modele]." (".$libmodele[artcode_eurocave].")");
        $heightlibele=$pdf->nblines($wcol[0],$designation)*4;

        $heighteco=0;
        $ecopart=0;
        $ecopartht=0;
        $ecoparthttotal=0;
        //$qte = $lignefacture[qte] ;
        $qte=1;
        if($libmodele[ecopart]>0 && $cde<>1){
            $ecopart=$libmodele[ecopart];
            $ecopartht=$ecopart/$dectva;
            $ecoparthttotal=$ecopartht*$qte;
        }

        $designation2="";
        if($libmodele[rangements_txt]!="") $designation2.="\n- Rangements inclus : ".str_replace("<br>","\n",$libmodele[rangements_txt]);
        if($libmodele[garantie_incluse]!="" && $cde!=1) $designation2.="\n- Garantie incluse : ".$libmodele[garantie_incluse];
        $designation2.="\n\n- Dim. (mm) / poids (kg) Brut : H".$libmodele[emballage_hauteur]."*L".$libmodele[emballage_largeur]."*P".$libmodele[emballage_profondeur]." / ".$libmodele[emballage_poids]."\n- Dim. (mm) / poids (kg) Net : H".$libmodele[hauteur]."*L".$libmodele[largeur]."*P".$libmodele[profondeur]." / ".round($libmodele[poid]/1000,1) ;

        $puht = floatval($lignefacture[strikeout_price])-$ecopartht ;
        $pvente=$lignefacture[price]-$ecopartht-$diffprixht;
        if($puht>0)
            $remise=-round(($puht-$pvente)/$puht*100);

        $montant_ht=number_format(($pvente*$qte),2,',',' ');
        $pvente=number_format($pvente,2,',',' ')." €";
        $puttc=number_format(floatval($lignefacture[strikeout_price])*($dectva)-$ecopart,2,',',' ');
        $puht = number_format($puht, 2, ',', ' ') ;
        $txtva = $facture[vat_100] ;
        $tva += ($lignefacture[price] * $qte*($dectva-1)) ;
        $montant_ttc = $lignefacture[price] * $qte * $dectva ;
        $total += ($qte*$lignefacture[price]) ;

        $montant_ttc = number_format($montant_ttc, 2, ',', ' ') ;
        $poidstotal+=(floatval(str_replace(",",".",$libmodele[emballage_poids]))*$qte);

        $pdf->SetFillColor(255,255,255);
        $i++ ;
        if (isset($affichetitre2facture) and $affichetitre2facture == 1)
            {
            $titre = $DB_site->query_first("SELECT $titre2 FROM article WHERE artid = '$libmodele[artid]'");
            if ($titre[$titre2] != "")
                {
                $titre[$titre2] = "(".$titre[$titre2].")";
                }
            $height = 5 ;
            $nbre = ceil(strlen($designation." ".$titre[$titre2])/60) ;
            if ($nbre > 1)
                {
                $height = $nbre * 5 ;
                }
            $pdf->SetFont('Arial','',8);
            //$pdf->Cell(30,$height,stripslashes("$lignefacture[artcode]"),1,0,'',1);
    
            $ly=$pdf->getY();
            if ($nbre > 1)
                {
                $pdf->MultiCell(95,4,"$designation $titre[$titre2]" ,1,'',1);
                $pdf->Enleve($height-5);
                }
            else
                {
                $pdf->Cell(95,5,"$designation $titre[$titre2]" ,1, 0, '',1);
                }
            $pdf->SetFont('Arial','',10);
            $pdf->setY($ly);
            $pdf->Cell(15,$height,"$puht € " ,1,0,'R',0);
            $pdf->Cell(15,$height,"$puttc € " ,1,0,'R',0);
            $pdf->Cell(15,$height,"$remise" ,1,0,'R',0);
            $pdf->Cell(15,$height,"$pvente" ,1,0,'R',0);
            $pdf->Cell(10,$height,"$qte" ,1,0,'C',0);
            $pdf->Cell(25,$height,"$montant_ht €",1,0,'R',0);
            if ($nbre > 1)
                {
                $pdf->Ajoute($height-5);
                }
            $pdf->Ln(5);
            }
        else
            {
            $height = 5 ;
            $nbre = ceil(strlen($designation)/80) ;
            if ($nbre > 1)
                $height = $nbre * 5 ;
            $pdf->SetFont('Arial','',8);
            //$pdf->Cell(30,$height,stripslashes("$lignefacture[artcode]"),1,0,'',1);
    
            $ly=$pdf->getY();
            $lx=10;
            if($height+$ly>=250){
                footer($pdf);
                $pdf->addpage();
                toptable($pdf);
                $ly=$pdf->getY();
                $lx=10;
            }
            $pdf->multiCell($wcol[0],4,"$designation" ,0,'',1);
            $posY=$pdf->nblines($wcol[0],$designation)*4+$ly;
            foreach($des_caracts as $txt=>$prix){
                    $pdf->setY($posY);
                    $pdf->multicell($wcol[0],4,"- ".$txt,0,'',0);
                    if($prix!=0 && $cde<>1){
                          $posX=$wcol[0]+10;
                          $pdf->setXY($posX,$posY);
                          $pdf->multiCell($wcol[1],4,number_format($prix,2,",","")." €" ,0,'R',1);
                          $posX+=$wcol[1];
                          $pdf->setXY($posX,$posY);
                          $pdf->multiCell($wcol[2],4,number_format($prix*$dectva,2,",","")." € " ,0,'R',1);
                          $pdf->setfont('arial','B',8);
                          $posX+=$wcol[2]+$wcol[3];
                          $pdf->setXY($posX,$posY);
                          $pdf->multiCell($wcol[4],4,number_format($prix*$dectva,2,",","")." €" ,0,'R',1);
                          $posX+=$wcol[4];
                          $pdf->setXY($posX,$posY);
                          $pdf->multiCell($wcol[5],4,$qte ,0,'C',1);
                          $posX+=$wcol[5];
                          $pdf->setXY($posX,$posY);
                          $pdf->setfont('arial','',8);
                          $pdf->multiCell($wcol[6],4,number_format(($prix*$qte)/$dectva,2,",","")." €",0,'R',1);
                    }
                    $designation.="\n".$txt;
                    $posY=$pdf->nblines($wcol[0],$designation)*4+$ly;
            }

            if($ecopart!=0){
                  $lx=10;
                  $pdf->setXY($lx,$posY);
                  $pdf->multicell($wcol[0],4,"- Eco participation",0,'',1);
                  $lx=$wcol[0]+10;
                  $pdf->setXY($lx,$posY);
                  $pdf->multiCell($wcol[1],4,number_format($ecopartht,2,",","")." €" ,0,'R',1);
                  $lx+=$wcol[1];
                  $pdf->setXY($lx,$posY);
                  $pdf->multiCell($wcol[2],4,number_format($ecopart,2,",","")." € " ,0,'R',1);
                  $pdf->setfont('arial','B',8);
                  $lx+=$wcol[2]+$wcol[3];
                  $pdf->setXY($lx,$posY);
                  $pdf->multiCell($wcol[4],4,number_format($ecopartht,2,",","")." €" ,0,'R',1);
                  $lx+=$wcol[4];
                  $pdf->setXY($lx,$posY);
                  $pdf->multiCell($wcol[5],4,$qte ,0,'C',1);
                  $lx+=$wcol[5];
                  $pdf->setXY($lx,$posY);
                  $pdf->setfont('arial','',8);
                  $pdf->multiCell($wcol[6],4,number_format($ecoparthttotal,2,",","")." €",0,'R',1);
            }

            $lx=10;
            $pdf->setXY($lx,$posY);
            $pdf->multicell($wcol[0],4,$designation2,0,'',0);
            $height=$pdf->nblines($wcol[0],$designation."\n".$designation2)*4;

            $lx+=$wcol[0];
            if($cde<>1){
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[1],$heightlibele,"$puht € " ,0,'R',1);
                $lx+=$wcol[1];
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[2],$heightlibele,"$puttc € " ,0,'R',1);
                $lx+=$wcol[2];
                $pdf->setXY($lx,$ly);
                $pdf->setfont('arial','B',8);
                if($remise!=0) $pdf->multiCell($wcol[3],$heightlibele,"$remise %" ,0,'C',1);
                $lx+=$wcol[3];
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[4],$heightlibele,"$pvente" ,0,'R',1);
            }
            $lx+=$wcol[4];
            $pdf->setXY($lx,$ly);
            $pdf->multiCell($wcol[5],$heightlibele,"$qte" ,0,'C',1);
            $pdf->setfont('arial','',8);
            if($cde<>1){
                $lx+=$wcol[5];
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[6],$heightlibele,"$montant_ht €",0,'R',1);
            }
            
            $lx=10;
            $pdf->setXY($lx,$ly);
            $pdf->multicell($wcol[0],$height,"",1,'',0);
            $lx=$wcol[0]+10;
            $pdf->setXY($lx,$ly);
            if($cde<>1){
                $pdf->multiCell($wcol[1],$height,"" ,1,'R',0);
                $lx+=$wcol[1];
                $pdf->setXY($lx,$ly);;
                $pdf->multiCell($wcol[2],$height,"" ,1,'R',0);
                $lx+=$wcol[2];
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[3],$height,"" ,1,'',0);
                $lx+=$wcol[3];
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[4],$height,"" ,1,'R',0);
            }
            $lx+=$wcol[4];
            $pdf->setXY($lx,$ly);
            $pdf->multiCell($wcol[5],$height,"" ,1,'C',0);
            if($cde<>1){
                $lx+=$wcol[5];
                $pdf->setXY($lx,$ly);
                $pdf->multiCell($wcol[6],$height,"",1,'R',0);
            }


            $pdf->Ln(5);
            }

        
        if ($lignefacture[remboursement] == '1')
            {
            $dateremb = explode("-", $lignefacture[date_remboursement]) ;
            $pdf->SetFont('Arial','',8);
            if ($nbre > 1)
                {
                $pdf->MultiCell(165,5,"- Remboursement $designation le $dateremb[2]/$dateremb[1]/$dateremb[0]" ,1,'R',1);
                $pdf->Enleve($height-5);
                }
            else
                {
                $pdf->Cell(165,5,"- Remboursement $designation le $dateremb[2]/$dateremb[1]/$dateremb[0]" ,1,0,'R',1);
                }
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(25,$height,"- $montant_ttc €",1,0,'R',1);
            if ($nbre > 1)
                {
                $pdf->Ajoute($height-5);
                }
            $pdf->Ln(5);
            $i++;
            }
        
        }

        
    $i++ ;
    /*$pdf->SetFillColor(255,255,255);
    $pdf->Cell(85,5,"" ,1,0,'',1);
    $pdf->Cell(15,5,"" ,1,0,'R',1);
    $pdf->Cell(15,5,"" ,1,0,'C',1);
    $pdf->Cell(15,5,"" ,1,0,'C',1);
    $pdf->Cell(15,5,"",1,0,'R',1);
    $pdf->Cell(12,5,"",1,0,'R',1);
    $pdf->Cell(25,5,"",1,0,'R',1);
    $pdf->Ln(5);                   */


    // remise sut tout catalogue 
    /*if (in_array ("106", $modules))
        {
        if ($facture[pctremise] != 0)
            {
            $i++ ;
            $fmttremise = $total * ($facture[pctremise] / 100) ;
            $total -= $fmttremise ;
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(110,5, "$multilangue[remisede] $facture[pctremise] % $multilangue[surtoutlecat]",1,0,'',1);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(20,5,"" ,1,0,'R',1);
            $pdf->Cell(20,5,"" ,1,0,'C',1);
            $pdf->Cell(15,5,"" ,1,0,'C',1);
            $pdf->Cell(25,5,"-".number_format($fmttremise, 2, ',', ' ')." €",1,0,'R',1);
            $pdf->Ln(5);
            }
        }  */
        
    // remise
    if (in_array ("3", $modules))
        {
        if ($facture[remiseid] != 0)
            {
            $infosremise=$DB_site->query_first("SELECT * from remise where remiseid = '$facture[remiseid]'");
            $article=$DB_site->query_first("SELECT $libelle from article a, remisearticle ra where a.artid = ra.artid and remiseid = '$facture[remiseid]'");
            if ($article[0] != "")
                {
                $fin = " $multilangue[surlarticle] $article[0]." ;
                }
            else
                {
                $rayon=$DB_site->query_first("SELECT $libelle from categorie c, remisecategorie rc where c.catid = rc.catid and remiseid = '$facture[remiseid]'");
                if ($rayon[0] != "")
                    {
                    $fin = " $multilangue[surlesarticlesdurayon] $rayon[0]." ;
                    }
                else
                    {
                    $fin = "." ;
                    }
                }
            switch ($infosremise[typeremise])
                {
                case '0': // remise sur le poids
                    $libremise = "$multilangue[remise] $infosremise[pctremise] % : $multilangue[poids] > $infosremise[valeur] $multilangue[grammes] $fin" ;
                    break;
                case '1': // remise sur le prix
                    $libremise = "$multilangue[remise] $infosremise[pctremise] % : $multilangue[prix] > $infosremise[valeur] € $fin" ;
                    break;
                case '2': // remise sur la quantité
                    $libremise = "$multilangue[remise] $infosremise[pctremise] % : $multilangue[quantite] > $infosremise[valeur] $multilangue[articlesoupas] $fin" ;
                    break;
                } // fin du switch
            $i++ ;
            $total -= $facture[montantremise] ;
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(110,5, "$libremise",1,0,'',1);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(20,5,"" ,1,0,'R',1);
            $pdf->Cell(20,5,"" ,1,0,'C',1);
            $pdf->Cell(15,5,"" ,1,0,'C',1);
            $pdf->Cell(25,5,"-".number_format($facture[montantremise], 2, ',', ' ')." €",1,0,'R',1);
            $pdf->Ln(5);
            }
        }

    $tvaremise = 0;
    // Bons de réduction 
    if (in_array("13", $modules) && $cde<>1)
        {
        if ($facture[cadeauid] != 0)
            {
            $infoscadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
            $article=$DB_site->query_first("SELECT $libelle from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '$facture[cadeauid]'");
            if ($article[0] != "")
                {
                $fin = " $multilangue[surlarticle] $article[0]." ;
                }
            else
                {
                $rayon=$DB_site->query_first("SELECT $libelle from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '$facture[cadeauid]'");
                if ($rayon[0] != "")
                    {
                    $fin = " $multilangue[surlesarticlesdurayon] $rayon[0]." ;
                    }
                else
                    {
                    $fin = "." ;
                    }
                }
            switch ($infoscadeau[typecadeauid])
                {
                case '0': // remise sur le poids
                    $libcadeau = "$multilangue[bonreduction] $multilangue[de] $infoscadeau[valeurcadeau] € $fin" ;
                    break;
                case '1': // remise sur le prix
                    $libcadeau = "$multilangue[bonreduction] $multilangue[de] $infoscadeau[valeurcadeau] % $fin" ;
                    break;
                }
            $i++ ;
            //$total -= ($facture[montantcadeau]/1.196) ;
            
            $tvaremise = $facture[montantcadeau]-($facture[montantcadeau]/1.196);
            
            $nbre = ceil(strlen($libcadeau)/60)*5 ;
            $cadeauttc="-".number_format($facture[montantcadeau],2,","," ")." €";
            $cadeauht="-".number_format($facture[montantcadeau]/$dectva,2,","," ")." €";
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',8);
            $hautcadeau=$pdf->getY();
            $pdf->setX($wcol[0]+10);
            $pdf->Cell($wcol[1],$nbre,$cadeauht ,1,0,'R',1);
            $pdf->Cell($wcol[2],$nbre,$cadeauttc ,1,0,'R',1);
            $pdf->Cell($wcol[3],$nbre,"" ,1,0,'R',1);
            $pdf->Cell($wcol[4],$nbre,$cadeauht,1,0,'R',1);
            $pdf->Cell($wcol[5],$nbre,"1" ,1,0,'C',1);
            $pdf->Cell($wcol[6],$nbre,$cadeauht,1,0,'R',1);
            $pdf->setXY(10,$hautcadeau);
            $pdf->multiCell($wcol[0],5, "$libcadeau" ,1,'',1);
            $pdf->Ln(5);
            }
        }


     /*
    $i++ ;
    $pdf->SetFillColor(255,255,255);
    $pdf->Cell(30,5,"",1,0,'',1);
    $pdf->Cell(80,5,"" ,1,0,'',1);
    $pdf->Cell(20,5,"" ,1,0,'R',1);
    $pdf->Cell(20,5,"" ,1,0,'C',1);
    $pdf->Cell(15,5,"" ,1,0,'C',1);
    $pdf->Cell(25,5,"",1,0,'R',1);
    $pdf->Ln(5);
       */
    // frais de port
    if($cde<>1){
	$ly=$pdf->getY();
        $total += ($facture[shipping_price]);
        $tva += $facture[shipping_price]*($dectva-1) ;
        $prixport_ht=$facture[shipping_price] ;
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);

        $livr="Frais de port";

        $lx=10;
        $pdf->multiCell($wcol[0],5,"$livr (poids total brut : $poidstotal kg)" ,1,'',1);
        $lx+=$wcol[0];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[1],5, number_format($prixport_ht,2,',',' ')." €",1,'R',1);
        $lx+=$wcol[1];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[2],5,number_format($facture[shipping_price]*$dectva, 2, ',', ' ')." €" ,1,'R',1);
        $lx+=$wcol[2];
        $pdf->setXY($lx,$ly);
        $pdf->multicell($wcol[3],5,"",1,'C',1);
        $lx+=$wcol[3];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[4],5, number_format($prixport_ht,2,',',' ')." €",1,'R',1);
        $lx+=$wcol[4];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[5],5,"1" ,1,'C',1);
        $lx+=$wcol[5];
        $pdf->setXY($lx,$ly);
        $pdf->multiCell($wcol[6],5, number_format($prixport_ht,2,',',' ')." €",1,'R',1);
        $pdf->Ln(6);
}

if($cde==1){
     $pdf->cell($wcol[0]+$wcol[5],5,"Poids total brut : $poidstotal kg",1);
}

    // contre remboursement
    /*if ($facture[moyenid] == '3' and $facture[prix_contre_remboursement] != 0)
        {
        $i++ ;
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(30,5,"",1,0,'',1);
        $pdf->Cell(80,5,"" ,1,0,'',1);
        $pdf->Cell(20,5,"" ,1,0,'R',1);
        $pdf->Cell(20,5,"" ,1,0,'C',1);
        $pdf->Cell(15,5,"" ,1,0,'C',1);
        $pdf->Cell(25,5,"",1,0,'R',1);
        $pdf->Ln(5);
    
        $total += $facture[prix_contre_remboursement] ;
        $i++;
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(110,5,"$multilangue[supplementcontreremb]" ,1,0,'',1);
        $pdf->Cell(20,5,"" ,1,0,'R',1);
        $pdf->Cell(20,5,"" ,1,0,'C',1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(15,5,"" ,1,0,'C',1);
        $pdf->Cell(25,5,number_format($facture[prix_contre_remboursement], 2, ',', ' ')." €",1,0,'R',1);
        $pdf->Ln(5);
        }     */

        // ajout des lignes
        /*
        while ($i<10) 
            {
            $i++;
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(30,5,"",1,0,'',1);
            $pdf->Cell(80,5,"",1,0,'',1);
            $pdf->Cell(20,5,"",1,0,'',1);
            $pdf->Cell(20,5,"",1,0,'',1);
            $pdf->Cell(15,5,"",1,0,'',1);
            $pdf->Cell(25,5,"",1,0,'',1);
            $pdf->Ln(5);
            }
        while( $j>1 && $i<30 )
            {
            $i++;
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(30,5,"",1,0,'',1);
            $pdf->Cell(80,5,"",1,0,'',1);
            $pdf->Cell(20,5,"",1,0,'',1);
            $pdf->Cell(20,5,"",1,0,'',1);
            $pdf->Cell(15,5,"",1,0,'',1);
            $pdf->Cell(25,5,"",1,0,'',1);
            $pdf->Ln(5);    
            }
                */

    $pdf->SetFillColor(255,255,255);

    if($cde<>1){

        $totalwidth=0;
        for($i=0;$i<=5;$i++) $totalwidth+=$wcol[$i];
        $pdf->Cell($totalwidth,5,"Total H.T.",0,0,'R',1);
        $pdf->SetFillColor(250,250,200);
        $pdf->setfont('arial','B',10);
        $pdf->Cell($wcol[6],5,number_format($total, 2, ',', ' ')." €",1,0,'R',1);
        $pdf->Ln(5);
        $pdf->SetFillColor(255,255,255);
        $pdf->setfont('arial','',8);
        $pdf->cell($wcol[0],5,"",0,0,"L",1);
        $pdf->Cell($totalwidth-$wcol[0],5,"T.V.A. 19,6%",0,0,'R',1);
        $pdf->Cell($wcol[6],5,number_format($tva-$tvaremise, 2, ',', ' ')." €",1,0,'R',1);
        $pdf->Ln(5);
        $dateexp=explode("-",substr($facture[billing_date],0,10));
        $pdf->Cell($wcol[0],5,"Date expédition : $dateexp[2]/$dateexp[1]/$dateexp[0]",0,0,'',1);

        $pdf->Cell($totalwidth-$wcol[0],5,"Montant Total T.T.C.",0,0,'R',1);
        $pdf->SetFillColor(255,155,100);
        $pdf->setfont('arial','B',10);
        $pdf->Cell($wcol[6],5,number_format($total*1.196, 2, ',', ' ')." €",1,0,'R',1);
        $pdf->Ln(5);

        $pdf->setx(10);
        $pdf->setfont('arial','',8);
        $pdf->cell($wcol[0],5,"Paiement par ".$marketplaces[$facture['marketplace']]);
	$pdf->ln(5);
	$pdf->cell($wcol[0],5,"Compte analytique : ".compte_analytique($facture['marketplace']));
    }

    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',8);

    $pdf->Ln(5);

    footer($pdf);

    // fin
   // $file=basename(tempnam("$rootpath/configurations/$host/factures" , "tmp"));
   // $pdffile="$outputrootpath/configurations/$host/factures/" . $file . ".pdf";
   // rename("$rootpath/configurations/$host/factures/$file" , "$rootpath/configurations/$host/factures/$file.pdf");
    //$pdf->Output($pdffile);
    if(isset($_GET['join']))
        return $pdf->output('','S');
    else
        $pdf->output();

    CleanFiles("$rootpath/configurations/$host/factures/");
    echo "<HTML><SCRIPT type=\"text/javascript\">document.location='$pdffile';</SCRIPT></HTML>";
    } 
else 
    {
    echo "$multilangue[erreur] !!!";
    }


function toptable($pdf){
             global $cde;
             global $wcol;
             global $facture;
             global $multilangue;

             $pdf->setfont('arial','',8);
            $pdf->SetFillColor(250,200,150);
            $pdf->Ln(10);
            if ($cde == "1") 
            {
            $pdf->Cell(30,5,"Bon de livraison N°",1,0,'R',1);
            } 
            else 
            {
            $pdf->Cell(30,5,"Facture N°",1,0,'R',1);
            }
            $pdf->SetFillColor(255,255,255);
            if ($cde == "1") 
            {
            $pdf->Cell(50,5,$facture[id],1,0,'C',1);
            } 
            else 
            {
            $pdf->Cell(50,5,$facture[billing_number],1,0,'C',1);
            $pdf->Ln(5);
            $pdf->SetFillColor(250,200,150);
            $pdf->Cell(30,5,"Bon de livraison N°",1,0,'R',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(50,5,$facture[id],1,0,'C',1);
            }
            $pdf->Ln(5);

            $pdf->SetFillColor(250,200,150);
            $pdf->Cell(30,5,"$multilangue[dateeedition]",1,0,'R',1);
            $pdf->SetFillColor(255,255,255);
            $dateafftmp=split("-",substr($facture[order_date],0,10));
            $dateaff="$dateafftmp[2]/$dateafftmp[1]/$dateafftmp[0]";
            $pdf->Cell(50,5,"$dateaff",1,0,'C',1);
            $pdf->Ln(5);
            $pdf->SetFillColor(250,200,150);
            $pdf->Cell(30,5,"$multilangue[page]",1,0,'R',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(50,5,$pdf->page,1,0,'C',1);
            $pdf->Ln(10);

            $hauttableau=$pdf->getY();
            $pdf->SetFillColor(250,150,100);
            $pdf->setfont('arial','B',8);
            $pdf->cell($wcol[0],8,"Désignation",1,0,"C",1);

            if($cde<>1){
              $pdf->cell(($wcol[1]+$wcol[2]),4,"Prix Tarif",1,0,"C",1);
              $pdf->cell($wcol[3],8,"",1,0,"C",1);

              $pdf->multicell($wcol[4],4,"Prix Unit.\nNet H.T.",1,"C",1);
            }
            $pdf->setXY($wcol[0]+$wcol[1]+$wcol[2]+$wcol[3]+$wcol[4]+10,$hauttableau);
            $pdf->cell($wcol[5],8,"Qté",1,0,"C",1);

            if($cde<>1){
              $pdf->cell($wcol[6],8,"Total H.T.",1,0,"C",1);
              $pdf->setXY($wcol[0]+10,$hauttableau+4);
              $pdf->Cell($wcol[1],4,"H.T.",1,0,"C",1);
              $pdf->Cell($wcol[2],4,"T.T.C.",1,0,"C",1);
            }
              $pdf->SetFillColor(255,255,255);
              $pdf->setfont('arial','',8);
              $pdf->setY($hauttableau+8);
}

function footer($pdf){
             $pdf->setY(270);
             $pdf->SetFont('Arial','',6);
             $pdf->Cell(200,4,"www.mon-espace-cuisson.com - Copyright © 2009 Acilyon - Tous droits réservés - Siège Social Lyon - France - Rcs Lyon 452 947 385 - Tva FR 13 452 947 385",0,0,'C',1);
             $pdf->Ln(4);
             $pdf->Cell(200,4,"Réserve de propriété : nous nous réservons la propriété des marchandises jusqu'au paiement intégral de nos factures (loi N°80335 du 12 mai 1980)",0,0,'C',1);
             $pdf->Ln(4);
             $pdf->Cell(200,4,"Loi n° 92.1992 du 31/12/1992, un intérêt de retard égal à 1,5 fois le taux d'intérêt légal sera appliqué au montant de la facture lors d'un paiement tardif",0,0,'C',1);
             $pdf->SetFont('Arial','',8);
}

function compte_analytique($mpid){
	     global $DB_site;
	     $ca=$DB_site->query_first("SELECT * FROM compte_analytique WHERE paysid=0 AND site='".$mpid."'");
		 return $ca['code'];
}

?>