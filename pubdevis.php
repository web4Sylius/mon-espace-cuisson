<?php

include_once("global.php");


if(isset($mkpdf)){
        $pdf=new fpdf();
        pubdevis($pdf,$mkpdf);
        $pdf->output();
}

function pubdevis($pdf,$code,$codecaviste=false,$ligne=false){

         global $rootpath,$template,$host,$DB_site;
         $codepromo=intval($code);

         $pdf->addpage();
         $pdf->SetAutoPageBreak(false,10);

         if($codepromo!=0 && $codecaviste==false){
                $img="pub2011_deviscode.jpg";
                $pdf->image($rootpath."images/templates/".$template->langue."/".$img,0,0,210);     
                $kdo=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid=".$code);
                $pdf->settextcolor(255);
                $pdf->setfont('arial','B',16);
                $pdf->setxy(5,36);
                $pdf->multicell(55,5,$kdo['code'],0,'C',0);
                $pdf->setfont('arial','',9);

                $article=$DB_site->query_first("SELECT a.artid,a.artcode from article a, article_cadeau ac where a.artid = ac.artid and ac.cadeauid = '$kdo[cadeauid]'");
                if ($article[0] != ""){
                   $marque=$DB_site->query_first("SELECT m.libelle FROM marque m, article_marque am WHERE am.artid=$article[0] AND m.marqueid=am.marqueid");
                   $applicable= "$marque[0] $article[1]" ;
                }else{
                    $rayon=$DB_site->query_first("SELECT c.libelleFR from categorie c, categorie_cadeau cc where c.catid = cc.catid and cc.cadeauid = '$kdo[cadeauid]'");
                    if ($rayon[0] != ""){
                              $applicable = $rayon[0] ;
                    }else{
                        $applicable = $template->translate("/devis/sur_tout_site","F") ;
                    }
                }
		$pdf->settextcolor(110,19,18);
                $pdf->setxy(89,37.5);
                $pdf->multicell(42,3,$applicable,0,'C');

                $applicable=$template->translate("/devis/cumulable_promo");
                if($kdo['appliquer']=="PPC") $applicable=$template->translate("/devis/non_cumulable_promo");
                $pdf->setfont('arial','',10);
                $pdf->setxy(162,34);
                $pdf->multicell(45,5,$applicable,0,'C',0);

                $pdf->setfont('arial','B',16);
                
                $signe="€";
                if($kdo['typecadeauid']==1) $signe="%";
                $pdf->setxy(62,36);
                $pdf->multicell(25,5,"$kdo[valeurcadeau] $signe",0,'C',0);

                $pdf->setfont('arial','B',13);
                $pdf->setxy(134,38);
                if($kdo['datefin']!=0)
		      $pdf->cell(26,5,date("d/m/Y",$kdo['datefin']),0,0,'C',0);
		else
		      $pdf->cell(26,5,utf8_decode($template->translate("/devis/illimite","F")),0,0,'C',0);
         }

         if($codecaviste!=false){


               $code=$DB_site->query_first("SELECT * FROM code_caviste WHERE `code`='$codecaviste'");
               $caviste=$DB_site->query_first("SELECT * FROM caviste WHERE cavisteid=".$code['caviste']);

               if($code['type']==1 && $codepromo==0){
                    $img="pub_deviscaviste".$code['type'].".jpg";
                    $pdf->image($rootpath."images/templates/".$template->langue."/".$img,0,0,210);

                    $pdf->settextcolor(0);
                    $pdf->setfont('arial','B',12);
                    $pdf->setxy(42,63);
                    $pdf->multicell(50,10,$code['code'],0,'C',0);

		    $pdf->setfont('arial','B',10);
                    $pdf->setxy(111,62);
                    $pdf->multicell(55,5,"$caviste[raisonsociale]\n$caviste[ville] ($caviste[codepostal])",0,'C',0);

                    $pdf->settextcolor(255);
		    $pdf->setfont('arial','B',12);
                    preg_match("#d'une valeur de (.+) #iU",$ligne['libelle'],$valeur);
                    $pdf->setxy(91,63);
                    $pdf->multicell(24,10,"$valeur[1] €",0,'C',0);


                    $pdf->setxy(162,63);
                    $pdf->multicell(38,10,date("d/m/Y",$code['validite']),0,'C',0);
               }
               if($code['type']==2 || $codepromo!=0){
                    $img="pub_deviscode1.jpg";
                    $pdf->image($rootpath."images/templates/".$template->langue."/".$img,0,0,210);

                    $pdf->settextcolor(0);
                    $pdf->setfont('arial','B',12);
                    $pdf->setxy(10,65);
                    $pdf->multicell(40,5,$code['code'],0,'C',0);

		    $pdf->setxy(78,65);
		    $pdf->multicell(40,5,$template->translate("/devis/sur_tout_site","F"),0,'C');

 		    $pdf->settextcolor(255);
                    preg_match("#d'une valeur de (.+) #iU",$ligne['libelle'],$valeur);
                    $pdf->setxy(55,65);
                    $pdf->multicell(20,5,"$valeur[1] €",0,'C',0);

		    
		    $pdf->setxy(119,65);
                    $pdf->multicell(37,5,date("d/m/Y",$code['validite']),0,'C',0);
               }

         }
         if($code==0 && $codecaviste==false){
                $img="pub_devis1.jpg";
                $pdf->image($rootpath."images/templates/".$template->langue."/".$img,0,0,210);
         }


         $pdf->addpage();
         $pdf->image($rootpath."images/templates/".$template->langue."/pub2011_devis2.jpg",0,0,210);



}

?>