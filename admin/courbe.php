<?php
include ("/home/arobases/www/scripts/JPGraph/jpgraph.php");
include ("/home/arobases/www/scripts/JPGraph/jpgraph_line.php");
// array(136,34,40,45,46,62,63,134,74,10,120,136,141,168,180,77,209,218,346,395,89,430)

$couleur = array( "goldenrod2", "burlywood2", "cadetblue3", "chartreuse3", "chartreuse4", "cyan3"
				, "cyan4", "goldenrod", "darkmagenta",
				  "aquamarine3", "dodgerblue4", "goldenrod2", "gray2", "khaki1", "lemonchiffon1"
				, "darkolivegreen2", "lightsalmon", "lightskyblue3", "salmon1", "steelblue4",
				"darkorchid4", "yellow" );
				  
$donnee_temp = explode( "|", $donnee );
$donnee_lib = explode( ",", $donnee_lib_barre );

$graph = new Graph(700,300,"auto");	
$graph->SetScale("textlin");
$i=1;
$j=count($donnee_temp)-1;
while (list($key, $val) = each( $donnee_temp )) 
{
	$ydata = explode( ",", $val );
	$temp = "lineplot$i";
	${$temp}=new LinePlot($ydata);	
	
	$graph->Add(${$temp});
	${$temp}->SetColor($couleur[$j]);
	${$temp}->SetWeight(2);
	${$temp}->SetLegend( $donnee_lib[$i-1] );
	$i++;
	$j--;
}



//$ydata = array(11,3,8,12,5,1,9,13,5,7);
//$ydata2 = array(1,19,15,7,22,14,5,9,21,13);

// Create the graph. These two calls are always required

// Create the linear plot
//$lineplot=new LinePlot($ydata);
//$lineplot2=new LinePlot($ydata2);

// Add the plot to the graph
//$graph->Add($lineplot);
//$graph->Add($lineplot2);

$graph->img->SetMargin(40,180,20,40);
$graph->title->Set( $donnee_titre );
$graph->xaxis->title->Set( $donnee_axex );
$graph->yaxis->title->Set("CA en Euros");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
/*
$lineplot->SetColor("blue");
$lineplot->SetWeight(2);

$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);
*/
$graph->yaxis->SetColor("red");
$graph->yaxis->SetWeight(2);
//$graph->SetShadow();
//$graph->SetFillColor("white");

//$lineplot->SetLegend("Plot 1");
//$lineplot2->SetLegend("Plot 2");


// Display the graph
$graph->Stroke();
?>
