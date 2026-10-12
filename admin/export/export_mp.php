<?php

include("ooo.php");
include("../admin_global.php");

$doc=new ooo();

$doc->name="export market place ".date("Y-m-d");

$sname=new ooo_style("name");
$sname->family="table-cell";

$s0=new ooo_style("title");
$s0->family="table-cell";
$s0->font_color="#000000";
$s0->font_bold="bold";
$s0->border_bottom="0.5pt solid #000000";
$s0->border_top=$s0->border_left=$s0->border_right=$s0->border_bottom;
$s0->background_color="#AAAAFF";
$s0->text_align="center";

$s1=new ooo_style("enabled1");
$s1->family="table-cell";
$s1->font_color="#000000";
$s1->border_right="0.5pt solid #000000";
$s1->border_left=$s1->border_right;

$s2=new ooo_style("enabled0");
$s2->family="table-cell";
$s2->font_color="#888888";
$s2->border_right="0.5pt solid #000000";
$s2->border_left=$s2->border_right;

$sb=new ooo_style("bottom");
$sb->family="table-cell";
$sb->font_color="#000000";
$sb->font_bold="bold";
$sb->border_bottom="0.5pt solid #000000";
$sb->border_top=$sb->border_left=$sb->border_right=$sb->border_bottom;

$rdctable=new ooo_table("RDC");
$rdctable->set_col(1,"3cm");
$rdctable->set_col(2,"4cm");
$rdctable->set_col(3,"20cm");

$c0=new ooo_cell("Rue Du Commerce");
$c0->style="name";

$rdctable->set_row(1,"1cm");
$rdctable->addcell(1,1,$c0);

$c1=new ooo_cell("Code");
$c1->style="title";
$c2=new ooo_cell("Champ");
$c2->style="title";
$c3=new ooo_cell("Valeur");
$c3->style="title";

$doc->addstyle($sname);
$doc->addstyle($s0);
$doc->addstyle($s1);
$doc->addstyle($s2);
$doc->addstyle($sb);

$rdctable->set_row(2,"1cm");
$rdctable->addcell(1,2,$c1);
$rdctable->addcell(2,2,$c2);
$rdctable->addcell(3,2,$c3);

$line=2;
$prevartcode="";
$curstyle=1;
$requete=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='rdc' AND enabled=1 ORDER BY artcode");
while($row=$DB_site2->fetch_array($requete)){
    if($row['artcode']!=$prevartcode) $curstyle=abs($curstyle-1);

    $rdctable->set_row($line+1,"0.5cm");
    $c1=new ooo_cell($row['artcode']);
    $c1->style="enabled".$curstyle;
    $c2=new ooo_cell(utf8_encode($row['champ']));
    $c2->style="enabled".$curstyle;
    $c3=new ooo_cell(utf8_encode(strip_tags($row['valeur'])));
    $c3->style="enabled".$curstyle;
    $rdctable->addcell(1,$line+1,$c1);
    $rdctable->addcell(2,$line+1,$c2);
    $rdctable->addcell(3,$line+1,$c3);

    $line++;
    $prevartcode=$row['artcode'];
}

$requete=$DB_site2->query("SELECT COUNT(*) FROM `market_place_champs` WHERE `market_place` = 'rdc' AND enabled =1 GROUP BY artcode");
$total=$DB_site2->num_rows($requete);
$rdctable->set_row($line+1,"0.5cm");
$c1=new ooo_cell("Total");
$c1->style="bottom";
$c2=new ooo_cell($total." articles");
$c2->style="bottom";
$rdctable->addcell(1,$line+1,$c1);
$rdctable->addcell(2,$line+1,$c2);

$doc->addtable($rdctable);


//amazon
$rdctable=new ooo_table("Amazon");
$rdctable->set_col(1,"3cm");
$rdctable->set_col(2,"4cm");
$rdctable->set_col(3,"20cm");

$c0=new ooo_cell("Amazon");
$c0->style="name";

$rdctable->set_row(1,"1cm");
$rdctable->addcell(1,1,$c0);

$c1=new ooo_cell("Code");
$c1->style="title";
$c2=new ooo_cell("Champ");
$c2->style="title";
$c3=new ooo_cell("Valeur");
$c3->style="title";

$doc->addstyle($sname);
$doc->addstyle($s0);
$doc->addstyle($s1);
$doc->addstyle($s2);

$rdctable->set_row(2,"1cm");
$rdctable->addcell(1,2,$c1);
$rdctable->addcell(2,2,$c2);
$rdctable->addcell(3,2,$c3);

$line=2;
$prevartcode="";
$curstyle=1;
$requete=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='amazon' AND enabled=1 ORDER BY artcode");
while($row=$DB_site2->fetch_array($requete)){
    if($row['artcode']!=$prevartcode) $curstyle=abs($curstyle-1);

    $rdctable->set_row($line+1,"0.5cm");
    $c1=new ooo_cell($row['artcode']);
    $c1->style="enabled".$curstyle;
    $c2=new ooo_cell(utf8_encode($row['champ']));
    $c2->style="enabled".$curstyle;
    $c3=new ooo_cell(utf8_encode(html_entity_decode(strip_tags($row['valeur']),ENT_COMPAT,"ISO-8859-15")));
    $c3->style="enabled".$curstyle;
    $rdctable->addcell(1,$line+1,$c1);
    $rdctable->addcell(2,$line+1,$c2);
    $rdctable->addcell(3,$line+1,$c3);

    $line++;
    $prevartcode=$row['artcode'];
}

$requete=$DB_site2->query("SELECT COUNT(*) FROM `market_place_champs` WHERE `market_place` = 'amazon' AND enabled=1 GROUP BY artcode");
$total=$DB_site2->num_rows($requete);
$rdctable->set_row($line+1,"0.5cm");
$c1=new ooo_cell("Total");
$c1->style="bottom";
$c2=new ooo_cell($total." articles");
$c2->style="bottom";
$rdctable->addcell(1,$line+1,$c1);
$rdctable->addcell(2,$line+1,$c2);

$doc->addtable($rdctable);


//priceminister
$rdctable=new ooo_table("Priceminister");
$rdctable->set_col(1,"3cm");
$rdctable->set_col(2,"4cm");
$rdctable->set_col(3,"20cm");

$c0=new ooo_cell("Priceminister");
$c0->style="name";

$rdctable->set_row(1,"1cm");
$rdctable->addcell(1,1,$c0);

$c1=new ooo_cell("Code");
$c1->style="title";
$c2=new ooo_cell("Champ");
$c2->style="title";
$c3=new ooo_cell("Valeur");
$c3->style="title";

$doc->addstyle($sname);
$doc->addstyle($s0);
$doc->addstyle($s1);
$doc->addstyle($s2);

$rdctable->set_row(2,"1cm");
$rdctable->addcell(1,2,$c1);
$rdctable->addcell(2,2,$c2);
$rdctable->addcell(3,2,$c3);

$line=2;
$prevartcode="";
$curstyle=1;
$requete=$DB_site2->query("SELECT * FROM market_place_champs WHERE market_place='priceminister' AND enabled=1 ORDER BY artcode");
while($row=$DB_site2->fetch_array($requete)){
    if($row['artcode']!=$prevartcode) $curstyle=abs($curstyle-1);

    $rdctable->set_row($line+1,"0.5cm");
    $c1=new ooo_cell($row['artcode']);
    $c1->style="enabled".$curstyle;
    $c2=new ooo_cell(utf8_encode($row['champ']));
    $c2->style="enabled".$curstyle;
    $c3=new ooo_cell(utf8_encode(strip_tags($row['valeur'])));
    $c3->style="enabled".$curstyle;
    $rdctable->addcell(1,$line+1,$c1);
    $rdctable->addcell(2,$line+1,$c2);
    $rdctable->addcell(3,$line+1,$c3);

    $line++;
    $prevartcode=$row['artcode'];
}

$requete=$DB_site2->query("SELECT COUNT(*) FROM `market_place_champs` WHERE `market_place` = 'priceminister' AND enabled=1 GROUP BY artcode");
$total=$DB_site2->num_rows($requete);
$rdctable->set_row($line+1,"0.5cm");
$c1=new ooo_cell("Total");
$c1->style="bottom";
$c2=new ooo_cell($total." articles");
$c2->style="bottom";
$rdctable->addcell(1,$line+1,$c1);
$rdctable->addcell(2,$line+1,$c2);

$doc->addtable($rdctable);



$doc->generate();

?>