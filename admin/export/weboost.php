<?php

include("../admin_global.php");

include_once("xlexport.php");

$xl=new xlexport("csv");
$xl->name="export_weboost_".date("Ymd");

$req="SELECT a.artcode,a.artcode_eurocave,a.libelleFR AS title, a.refs_fabricant,a.artcode_fabricant,m.libelle AS marque,c.libelleFR AS cat,a.artid,a.commandable,a.gamme,a.modele FROM `article` a inner join article_marque am on(am.artid=a.artid) inner join marque m on (m.marqueid=am.marqueid) inner join categorie c on (c.catid=a.catid)";
$requete=mysql_query($req);

while($art=mysql_fetch_object($requete)){

  $xl->addrow(array($art->artcode,$art->artcode_eurocave,$art->artcode_fabricant,html_entity_decode($art->title),$art->marque,html_entity_decode($art->cat),str_replace("\r\n",",",$art->refs_fabricant),$art->gamme,$art->modele,"art,".$art->artid.",".url_rewrite($art->title).".htm",$art->commandable));
}

$xl->export();



    function url_rewrite($string) {
        $string=html_entity_decode($string , ENT_NOQUOTES , "ISO-8859-1" );
        $caracteres = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
        "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê"=> "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
        "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
        "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
        "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å"=> "a", "æ" => "a", "ç"=> "c",
        "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï"=> "i",
        "ð"=> "o", "ñ"=> "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
        "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y");
        $string = strtr($string , $caracteres);
        $string=strtolower($string);
        $len = strlen($string);
        for($a=0; $a<$len; $a++){
            $p = ord($string[$a]);
            if ( ($p > 96 && $p < 123) or ($p > 47 && $p < 58) ) {
                $return .= $string[$a];
            } else {
                $return .= "-";
            }
        }
        while(strpos($return,"--")!==FALSE) {
            $return=str_replace("--","-",$return);
        }
        $return=trim($return,"-");
        return $return;
    }


?>