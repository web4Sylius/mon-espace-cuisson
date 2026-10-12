<?php
class PDF extends FPDF
{
    
    public $colors = array("dore" => array(168, 141, 92), "bleu" => array(0, 150, 202), "black" => array(0, 0, 0), "white" => array(255, 255, 255), "light gray" => array(230, 226, 224), "orange" => array(255, 87, 13),"rouge"=>array(172,13,46));
    
    function Header() {
        global $host,$template,$rootpath, $langue,$cde, $nomboutique,$facture,$avoir,$proforma,$autocolor ;
        //include_once $rootpath."configurations/".$host."/config.php";
        $this->SetFont('Arial','B',25);
        $this->image($rootpath."configurations/$host/images/templates/".$template->site_code()."/".$template->langue."/logo".($template->pro?"_pro":"").".jpg",5,5,84,0,"JPG",$template->translate("/site/[site_code]/url"));
        $this->SetFont('Arial','B',8);
        $this->image($rootpath."configurations/$host/images/templates/".$template->site_code()."/".$template->langue."/contact_devis2016".($template->pro?"_pro":"").".jpg",161,4,46,0,"jpg",$template->translate("/site/[site_code]/url"));
        $this->setXY(0,27);
        $this->setfont('arial','B',18);
        $this->textColor($autocolor);
        if($cde=="1"){
            $colis=isset($_GET['colis'])?" Colis ".str_replace("-","/",$_GET['colis']):"";
            $this->Cell(210,8,$template->translate("/facture/bon_livraison","F").$colis,0,0,'C');
        }else {
            if(isset($_GET['annule']))
               $this->Cell(210,8,$template->translate("/facture/facture_annulee","F"),0,0,'C');
            elseif($avoir){
                $txt=$template->translate("/facture/avoir","F");
           
               if($facture['numerofacture']>15550) $txt.=" n°".$facture['numerofacture'];
               $this->Cell(210,8,$txt,0,0,'C');
            }elseif($proforma){
               $txt=$template->translate("/devis/facture_proforma_n","F");
               $txt.=$facture['facturid'];
               $this->Cell(210,8,$txt,0,0,'C');
            }elseif(isset($_GET['devisid'])){

            }else{
               $txt=$template->translate("/facture/facture","F");
               if($facture['numerofacture']>15550) $txt.=" n°".$facture['numerofacture'];
               $this->Cell(210,8,$txt,0,0,'C');
            }
        }

        $this->setfont('arial','',8);
        $this->textColor("black");
        if($this->PageNo() > 1) $this->setY(40);
    }
    function Footer()
    {
        //  $this->Image("$rootpath/configurations/www.ma-cave-a-vin.fr/factures/bas_page.jpg",10,280,190);
    }
    
    function cell($w = 0, $h = 0, $txt = "", $border = 0, $ln = 0, $align = "", $fill = false, $link = null)
    {
        if (preg_match('!!u', $txt))
            $txt = utf8_decode($txt);
        return parent::cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }
    
    function textColor($name)
    {
        $color = $this->colors[$name];
        return parent::setTextColor($color[0], $color[1], $color[2]);
    }
    
    function fillColor($name)
    {
        $color = $this->colors[$name];
        return parent::setfillcolor($color[0], $color[1], $color[2]);
    }
    
    function drawColor($name)
    {
        $color = $this->colors[$name];
        return parent::setdrawcolor($color[0], $color[1], $color[2]);
    }
    
    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s    = str_replace("\r", '', $txt);
        $nb   = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $nl  = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
    /**
     * Description
     * @param type $x top left x
     * @param type $y top left y
     * @param type $w width
     * @param type $h height
     * @param type $r radius of the rounded corners.
     * @param type|string $corners numbers of the corners to be rounded: 1, 2, 3, 4 or any combination (1=top left, 2=top right, 3=bottom right, 4=bottom left).
     * @param type|string $style same as Rect(): F, D (default), FD or DF.
     * @return type
     */
    function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        if (strpos($corners, '2')===false)
            $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
        else
            $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($corners, '3')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($corners, '4')===false)
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        if (strpos($corners, '1')===false)
        {
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
            $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
        }
        else
            $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    //rotation du text
    //http://www.fpdf.org/en/script/script2.php
    function Rotate($angle,$x=-1,$y=-1){
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0) {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

        function RotatedText($x,$y,$txt,$angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
        $this->Rotate(0);
    }

    function RotatedImage($file,$x,$y,$w,$h,$angle)
    {
        //Image rotated around its upper-left corner
        $this->Rotate($angle,$x,$y);
        $this->Image($file,$x,$y,$w,$h);
        $this->Rotate(0);
    }
}
?>