<?php 
/**
 * JPGraph Extend to make Stats Graphs
 * 
 * Needed Files : 
 * verdana.ttf, verdanab.ttf, verdanai.ttf, verdanaz.ttf
 * IN : /admin/scripts/
 * 
 */

DEFINE('TTF_DIR', dirname(__FILE__).'/scripts/');

switch($_GET['type']) {
	case 'line' :
		include (dirname(__FILE__)."/scripts/JPGraph/jpgraph.php");
		include (dirname(__FILE__)."/scripts/JPGraph/jpgraph_bar.php");
		include (dirname(__FILE__)."/scripts/JPGraph/jpgraph_line.php");
		
		//couleur de bar
		$bar_color_array = array(array('lightblue', 'blue'), array('orange', 'red'), array('lightgreen', 'darkgreen'), array('yellow', 'darkorange'), array('white', 'black'), array('green', 'darkgreen'), array('lightred', 'red'), array('red', 'purple'));
		$line_color_array = array('darkred@0.4', 'darkblue@0.4', 'darkgreen@0.4', 'black@0.4', 'yellow@0.4');
		
		//legend
		$legend_array = explode(";", base64_decode($_GET['legends']));
		$x_values = array_reverse(explode(";", base64_decode($_GET['values'])));
		
		$bar_val_array = array();
		$line_val_array = array();
		
		//recup les donnée de bar
		foreach($_GET as $gk => $gv) {
			if(substr($gk, 0,3) != 'bar') {
				continue;
			}
			else {
				$bar_val_array[] = explode(';', base64_decode($gv));
			}
		}
		
		//recup les donnée de line
		foreach($_GET as $gk => $gv) {
			if(substr($gk, 0,4) != 'line') {
				continue;
			}
			else {
				$line_val_array[] = explode(';', base64_decode($gv));
			}
		}
		
		$bar_array = array();
		
		//generer les bar
		for($i = 0; $i < sizeof($bar_val_array) ; $i++) {
			// Create a bar pot
			$bplot = new BarPlot(array_reverse($bar_val_array[$i]));
			// Adjust fill color
			$bplot->SetFillgradient($bar_color_array[$i][0],$bar_color_array[$i][1],GRAD_VER);
			$bplot->value->SetFormat('%d');
			$bplot->value->SetFont(FF_VERDANA,FS_BOLD,6);
			$bplot->value->SetColor('black');
			$bplot->value->SetAngle(60);
			$bplot->value->Show();
			$bplot->SetLegend(array_shift($legend_array));
			
			$bar_array[] = $bplot;
		}
		
		$line_array = array();
		
		//generer les lignes
		for($i = 0 ; $i < sizeof($line_val_array) ; $i++) {
			$lineplot = New LinePlot(array_reverse($line_val_array[$i]));
			$lineplot->SetLegend(array_shift($legend_array));
			$lineplot->SetColor($line_color_array[$i]);
			$lineplot->SetWeight(3);
			$lineplot->SetBarCenter();
			$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
			$lineplot->mark->SetWidth(5);
			$lineplot->mark->SetFillColor($line_color_array[$i]);
			$line_array[] = $lineplot;
		}
		
		//generer le graph
		
		$graph = new Graph(550,270,"auto");
		$graph->SetScale("textint");
		$graph->SetY2Scale( "lin");
		// Adjust the margin a bit to make more room for titles
		$graph->img->SetMargin(10,30,20,70);
		$graph->SetAlphaBlending(true);
		
		//group bars and adding
		for($i = 0 ; $i < sizeof($line_array) ; $i++) {
			$graph->Add($line_array[$i]);
		}
		if(sizeof($bar_array)) {
		
			$gbplot  = new GroupBarPlot ($bar_array); 
			$gbplot->SetWidth(0.8);
			
			
			$graph->AddY2($gbplot);
		
		}

		//setup the legend
		$graph->legend->SetFont(FF_VERDANA,FS_NORMAL,10);
		$graph->legend->Pos(0.02,0.97,"left","bottom");
		$graph->legend->SetColumns(3);
		
		
		
		// Setup the titles
		$graph->title->Set(base64_decode($_GET['title']));
		$graph->title->SetFont(FF_VERDANA,FS_BOLD,8);
		
		//Setup the axis
		
		$graph->xaxis->SetTickLabels($x_values);
		$graph->xaxis->SetFont(FF_VERDANA,FS_BOLD,6);
		$graph->xaxis->SetLabelAlign('right');

		$graph->y2axis->Hide();
		$graph->y2axis->scale->SetGrace(10);

		$graph->yaxis->SetFont(FF_VERDANA,FS_BOLD,6);
		$graph->yaxis->SetLabelAngle(60);
		$graph->yaxis->SetPos('max');
		$graph->yaxis->SetLabelSide(SIDE_RIGHT);
		$graph->yaxis->SetLabelFormat('%0.1f');
		// Display the graph
		$graph->Stroke();
		
	break;
	case 'pie' :
		include (dirname(__FILE__)."/scripts/JPGraph/jpgraph.php");
		include (dirname(__FILE__)."/scripts/JPGraph/jpgraph_pie.php");
		include (dirname(__FILE__)."/scripts/JPGraph/jpgraph_pie3d.php");
		
		$data = explode(";", base64_decode($_GET['val']));
		$legend = explode(";", html_entity_decode(base64_decode($_GET['lib']), null, "ISO-8859-15"));
		
		$total = base64_decode($_GET['tot']);
		$sum = array_sum($data);
		$delta = floor(abs($total-$sum));
		if($delta) {
			$data[] = $delta;
			$legend[] = "Autre...";	
		}
		
		$data = array_reverse($data);
		
		$graph  = new PieGraph (540,220,"auto");
		
		
		
		$graph->title->Set(base64_decode($_GET['title']));
		$graph->title->SetFont(FF_VERDANA, FS_BOLD,10); 
		$graph->title->SetColor("darkblue");
		$graph->legend->Pos(0.02,0.15);
		$graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
		
		$p1 = new PiePlot3d($data);
		
		$p1->SetTheme("earth");
		$p1->SetCenter(0.35);
		$p1->SetAngle(60);
		$p1->SetStartAngle(315);
		$p1->value->SetFont(FF_VERDANA,FS_BOLD,8);
		$p1->value->HideZero(false);
		$p1->SetLegends($legend);
		$p1->ExplodeSlice(sizeof($data)-1);
		$p1->ExplodeSlice(0);
		
		
		$graph->Add($p1);
		$graph->Stroke();
		
	break;
	
	
	
	
	
}