<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once($_SERVER['DOCUMENT_ROOT']."/class/stock.class.php");

$stock = new stock($DB_site2);
$stock_item = new stock_item($DB_site2);

if(isset($_GET['artid'])){

	$items = $stock->search($_GET['artid']);
	$lieux = $stock->lieux();

	$optde = "<option></option>";
	$stock_lieux=array();
	foreach($items as $item){
		$stock_lieux[$item->lieu]++;
	}

	foreach($stock_lieux as $lieu => $qte){
	    $optde .= "<option value=\"".$lieu."\">".$lieux[$lieu]." (".$qte.")</option>";
	}
	
    foreach($lieux as $id => $lieu){
    	$optvers .= "<option value=\"".$id."\">".$lieu."</option>";
    }
	if($optde!="<option></option>"){
		?>
		<p>Stock : <select name="tDep" id="tDep" onchange="doLieu(this.value);"><?php echo $optde; ?></select></p>
		<?php
	} else {
		echo "cet article n'est pas en stock.";
	}

}elseif(isset($_GET['tartid'])){
	$items = $stock->search($_GET['tartid']);
	$lieux = $stock->lieux();

	foreach($items as $item){
		$stock_lieux[$item->lieu]++;
	}

	for($i=1; $i<=$stock_lieux[$_GET['tlieu']]; $i++){
		$optqte .= "<option>".$i."</option>";
	}

	$optvers = "<option></option>";
	foreach($lieux as $id => $lieu){
		if($id != $_GET['tlieu'])
    		$optvers .= "<option value=\"".$id."\">".$lieu."</option>";
    }

    ?>

    <p>Quantité : <select name="tQte"><?php echo $optqte; ?></select></p>
    <p>Transférer vers : <select name="tArr" id="tArr" onchange="document.getElementById('submit').style.display=(this.value==''?'none':'inline');"><?php echo $optvers; ?></select></p>

    <?php
}
