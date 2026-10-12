<?php

include ("/home/arobases/www/scripts/JPGraph/jpgraph.php");
include ("/home/arobases/www/scripts/JPGraph/jpgraph_pie.php");
include ("/home/arobases/www/scripts/JPGraph/jpgraph_pie3d.php");

$data = explode( ",", $donnee );

$graph = new PieGraph( 700, 200, "auto" );
//$graph->SetShadow();

$graph->title->Set( $donnee_titre );
$graph->title->SetFont( FF_FONT1, FS_BOLD );

$p1 = new PiePlot3D( $data );
$p1->SetSize( 0.5 );
$p1->SetCenter( 0.45 );
$data_lib=explode( "," , $donnee_lib );
$p1->SetLegends( $data_lib );

$graph->Add($p1);
$graph->Stroke();	

?>