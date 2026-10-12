<?php

include "export/ooo.php";

$doc=new ooo();
$doc->name="export";
if(isset($typearticle) && $typearticle!=""){
    $type=$DB_site2->query_first("SELECT libelle FROM typearticle WHERE typearticleid=".$typearticle);
    $doc->name.="_".$type['libelle'];
}
if(isset($_POST['marque']) && $_POST['marque']!=0)
    $doc->name.="_".$marques[$_POST['marque']];
    
if(isset($_POST['site']) && $_POST['site']!="")
    $doc->name.="_".$_POST['site'];
    
$doc->name.="_".$dfrom->format("Y-m-d")."_".$dto->format("Y-m-d");

$table=new ooo_table("Stats");

$style_sep=new ooo_style("sep");
$style_sep->family="table-cell";
$style_sep->background_color="#FFFF00";
$style_sep->setborders("thin solid #888888");
$doc->addstyle($style_sep);

$style_red=new ooo_style("rouge");
$style_red->family="table-cell";
$style_red->font_color="#FF0000";
$style_red->text_align="right";
$style_red->setborders("thin solid #888888");
$doc->addstyle($style_red);

$right=new ooo_style("right");
$right->family="table-cell";
$right->text_align="right";
$right->setborders("thin solid #888888");
$doc->addstyle($right);

$head=array();
$head[]=new ooo_cell("Code");
$head[]=new ooo_cell("Marque");

$tdvar3="<td>Nb concur.</td>";

if($typesrc==3){
    foreach($periodes as $periode=>$ptxt)
	$head[]=new ooo_cell("Nb vus $ptxt");

    foreach($periodes as $periode=>$ptxt)
	$head[]=new ooo_cell("Nb vendus $ptxt");

    $head[]=new ooo_cell("","sep");
    $head[]=new ooo_cell("Ancien prix");
    
}else{
    $head[]=new ooo_cell("Nb vus");
    $head[]=new ooo_cell("Nb vendus");
    $head[]=new ooo_cell("CA HT");
    $head[]=new ooo_cell("","sep");
    $head[]=new ooo_cell("Prix achat HT");
    $head[]=new ooo_cell("Prix achat HT total");
    $head[]=new ooo_cell("PPI TTC");
}

$head[]=new ooo_cell("PV MCAV TTC");
$head[]=new ooo_cell("Stock");
$head[]=new ooo_cell("Marge HT MCAV");
$head[]=new ooo_cell("","sep");
$head[]=new ooo_cell("Prix le plus bas");

if($typesrc!=3) $head[]=new ooo_cell("Nb concur.");

$head[]=new ooo_cell("Site mini");
$head[]=new ooo_cell("Marge si prix + bas");
$head[]=new ooo_cell("","sep");

foreach($spysitelist as $spysite)
    $head[]=new ooo_cell(ucwords($spysite));;
    
    
$table->addrow(1,$head);
for($i=0;$i<count($head);$i++)
    if($head[$i]->style=="sep") $table->set_col($i+1,"5mm");

$table->set_col(1,"50mm");

$i=1;
$now=time();

if($showtable){
    foreach($mainlist as $code=>$value){
	  $artid=$artids[$code];
	  $prix=$listeprix[$artid];

	  $instock=$stock->in_stock($artid);
	  $qtestock=intval($instock->qte);
	  
	  $reqlot=$DB_site2->query("SELECT a.artcode,al.qte FROM article_lot al INNER JOIN article a USING(artid) WHERE al.lotid=".$artids[$code]);
	  $lotartnb=$DB_site2->num_rows($reqlot);
	  if($lotartnb==0)
	      $spycodes=array($code=>1);
	  else{
	      $spycodes=array();
	      while($al=$DB_site2->fetch_array($reqlot)) $spycodes[$al['artcode']]=$al['qte'];
	  }
	  
	  
	  $spyrow=array();
	  foreach($spysitelist as $site){
	      $content=$red="";
	      $totalspy=0;
	      $qtespy=0;
	      foreach($spycodes as $spycode=>$qte){
		  if(isset($spylist[$spycode][$site])){
		      $qtespy++;
		      $pc=$pc2="";
		      if($prix['prix']>0 && $lotartnb==0) $pc=round(-100*($prix['prix']-$spylist[$spycode][$site])/$prix['prix']);
		      if($prix['prixconseille']>0 && $lotartnb==0) $pc2=round(-100*($prix['prixconseille']-$spylist[$spycode][$site])/$prix['prixconseille']);
		      
		      $red=$pc<0?"rouge":"";
		      if($pc!=="") $pc="\n".($pc>0?"+":"").$pc."%";
		      if($pc2!="") $pc.="\n(".($pc2>0?"+":"").$pc2."%)";
		      $totalspy+=$spylist[$spycode][$site]*$qte;
		      $content=number_format($spylist[$spycode][$site],2,',','').$pc;
		  }
	      }
	      if($lotartnb>0){
		  if($qtespy==$lotartnb){
		      $pc="";
		      if($prix['prix']>0)
			  $pc=round(-100*($prix['prix']-$totalspy)/$prix['prix'],1)."%";
		      $red=$pc<0?"rouge":"";
		      $content=number_format($totalspy,2,',','')."\n".($pc>0?"+":"").$pc;
		      
		      if($totalspy>0 && (!isset($spymini[$code]) || $totalspy<$spymini[$code])){
			  $spymini[$code]=$totalspy;
			  $spyminisite[$code]=$site;
		      }
		  }else
		      $content="";
	      }else{
		  if(isset($spydata[$code][$site])){
		      $error=$spydata[$code][$site][5];
		      if($error!="")
			  $content="Erreur !!!";
		      elseif(!isset($spylist[$code][$site]))
			  $content="En attente";
		  }
	      }

	      $spyrow[]=new ooo_cell($content,$red);
	      
	  }
	  
	  if(isset($spymini[$code])){
	      $spyht=$spymini[$code]/(1+$prix['tva']/100);
	      $margespy=$spyht-$prix['totalachat'];
	      $pcmargespy=100*$margespy/$spyht;
	  }
	  
	  if($prix['prix']>0)
	      $pcspymini=round(-100*($prix['prix']-$spymini[$code])/$prix['prix']);
	  else
	      $pcspymini="";
	      
	  $i++;
	  $artrow=array();
	  $artrow[]=new ooo_cell($code."\n".$pfab[$code]);
	  $artrow[]=new ooo_cell($pmarq[$code]);
	  
	  
	  if($typesrc!=3){
	    $artrow[]=new ooo_cell($pvus[$code]);
	    $artrow[]=new ooo_cell($plist[$code]);
	    $artrow[]=new ooo_cell($pcaht[$code],"right","currency");
	   
	  }else{
	    foreach($tsperiode as $periode=>$ts)
		$artrow[]=new ooo_cell(intval($vus_periode[$artid][$periode]));
		
	    foreach($tsperiode as $periode=>$ts)
		$artrow[]=new ooo_cell(intval($ventes_periode[$artid][$periode]['']['qte']));
	  }
	    
	  $artrow[]=new ooo_cell("","sep");
	  
	  if($typesrc!=3){
	    $artrow[]=new ooo_cell($prix['paht'],"right","currency");
	    $artrow[]=new ooo_cell($prix['totalachat'],"right","currency");
	    $artrow[]=new ooo_cell($prix['prixconseille'],"right","currency");
	  }else{
	    $artrow[]=new ooo_cell($price_history[$artid]."\n".date("d/m/Y",$price_change[$artid]),"right");
	  }
	    
	  $artrow[]=new ooo_cell(number_format($prix['prix'],2,',','')."\n".$prix['pcremise']."%","right");
	  $artrow[]=new ooo_cell($qtestock);
	  $artrow[]=new ooo_cell(round($prix['pcmarge'])."%\n".number_format($prix['marge'],2,',',''),"right");
	  $artrow[]=new ooo_cell("","sep");
	  $artrow[]=new ooo_cell(($spymini[$code]!=""?number_format($spymini[$code],2,',','')."\n".$pcspymini."%":""),($pcspymini<0?"rouge":"right"));
	
	  if($typesrc!=3)
	    $artrow[]=new ooo_cell(count($spylist[$code]));
	    
	  $artrow[]=new ooo_cell($spyminisite[$code]);
	  $artrow[]=new ooo_cell((isset($spymini[$code])?round($pcmargespy)."%\n".number_format($margespy,2,',',''):""),"right");
	  $artrow[]=new ooo_cell("","sep");
	  
	  $table->addrow($i,array_merge($artrow,$spyrow));
    }
}

$doc->addtable($table);
$doc->generate();



?>