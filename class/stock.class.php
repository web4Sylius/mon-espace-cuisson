<?php

// *********************************************************************************
// Class stock *********************************************************************
// *********************************************************************************
class stock {
    private $DB;

    //***************************************************************************************
    function __construct($DB){
	     $this->DB=$DB;
    }

    //***************************************************************************************
    function stockbyqte($state=1,$lieu=null){
    	$return=array();
    	
      $state=(is_array($state)?implode("','",$state):intval($state));
    	$sql="SELECT si.*,mini,alert,artids,delai FROM stock_item si LEFT JOIN stock_mini USING(artid) LEFT JOIN stock_cmd sc ON sc.id = si.stock_cmd_id WHERE state IN ('".$state."')";
    	if(!is_null($lieu)) $sql.=" AND ((si.lieu='' AND sc.livraison = ".$lieu.") OR (si.lieu=".$lieu."))";
    	$sql.=" GROUP BY si.id";
    	$r=$this->DB->query($sql);
      
    	while($data=$this->DB->fetch_array($r)){
    	    if(empty($return[$data['artid']]) === false && !is_a($return[$data['artid']],"stock_item")){
    	      $si=new stock_item($this->DB,$data);
    	      $si->qte=1;
    	      $si->id=0;
    	      $si->lieu="";
    	      $si->origin="";
    	      $si->dest="";
    	      $si->utilisateur="";
    	      $si->date=0;
    	      $return[$data['artid']]=$si;
    	    }else{
				if(empty($return[$data['artid']]->paht) === false) {
					$return[$data['artid']]->qte++;
				}
			  if(empty($return[$data['artid']]->paht) === false) {
				  $return[$data['artid']]->paht+=$data['paht'];
			  }

            }
	    }

	    return $return;
    }

    //***************************************************************************************
    function stockbyartid($artid){
    	$return=array();
    	$r=$this->DB->query("SELECT si.*, sm.mini,sm.alert,sm.artids,sm.delai,sc.livraison FROM stock_item si LEFT JOIN stock_mini sm ON sm.artid = si.artid AND sm.lieu = si.lieu LEFT JOIN stock_cmd sc ON sc.id = si.stock_cmd_id WHERE si.artid=".intval($artid)." ORDER BY date");
      
    	while($data=$this->DB->fetch_array($r)){
    	    $return[$data['id']]=new stock_item($this->DB,$data);
    	}

	    return $return;
    }

    //***************************************************************************************
    function stockbyartid_sim($artid,$lieu=null,$state=1){
    	$return=array();
      $state=(is_array($state)?implode("','",$state):intval($state));
    	$sql="SELECT si.*,sm.mini,sm.alert,sm.delai FROM stock_item si LEFT JOIN stock_mini sm ON (sm.artids LIKE '%,si.artid%' OR sm.artids LIKE '%si.artid,%' OR sm.artids = 'si.artid') LEFT JOIN stock_mini sm2 ON sm2.lieu = 'si.lieu' LEFT JOIN stock_cmd sc ON sc.id = si.stock_cmd_id WHERE si.artid = '".$artid."' AND si.state IN ('".$state."')";
    	if(!is_null($lieu) && $lieu !== '') $sql.=" AND ((si.lieu='' AND sc.livraison = ".$lieu.") OR (si.lieu=".$lieu."))";
    	$sql.=" GROUP BY si.id";
    	$r=$this->DB->query($sql);

    	while($data=$this->DB->fetch_array($r)){
    		$return['qte']+=1;
    		$return['paht']+=$data['paht'];
    	}

	    return $return;
    }

    //***************************************************************************************
    function stockbylieu($state){
    	$return=array();
    	$r=$this->DB->query("SELECT *,SUM(artid) AS qte FROM stock_item INNER JOIN stock_mini USING (artid) WHERE state=".intval($state)." GROUP BY lieu");
      
    	while($data=$this->DB->fetch_array($r)){
    	    $si=new stock_item($this->DB,$data);
    	    $si->qte=$data['qte'];
    	    $si->id=0;
    	    $si->origin="";
    	    $si->dest="";
    	    $si->utilisateur="";
    	    $si->date=0;
    	    $return[$data['lieu']]=$si;
    	}

	    return $return;
    }

    //***************************************************************************************
    function stock0($state=null,$lieu=null,$mini=null){
    	$return=array();
    	if(!is_null($lieu)) $sqllieu=" AND lieu=".$lieu;
    	if(!is_null($state)) $sqlstate=" AND state IN ('".(is_array($state)?implode("','",$state):intval($state))."')";
    	if(!is_null($mini)) $sqlmini=" AND (mini+alert)>0";
    	$sql="SELECT * FROM stock_mini WHERE (SELECT COUNT(artid) FROM stock_item LEFT JOIN stock_cmd sc ON sc.id = stock_item.stock_cmd_id WHERE artid=stock_mini.artid ".$sqlstate .(!is_null($lieu)?" AND (lieu=".$lieu." OR sc.livraison=".$lieu.")":'').")=0".$sqlmini.$sqllieu;
    
    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
    	    if(empty($return[$data['artid']]) === false && !is_a($return[$data['artid']],"stock_item")){
        		$item=new stock_item($this->DB);
        		$result=$item->loadartid($data['artid']);
        		$item->qte=0;
        		if($result!=false) $return[$data['artid']]=$item;
    	    }
    	}
      return $return;
    }

    //***************************************************************************************
    function in_stock($artid, $amazon = true){
      if(!$amazon) 
        $sqlAmazon = " AND lieu NOT IN (SELECT id FROM fournisseur WHERE type='fourn' AND societe LIKE '%amazon%')";
    	else
        $sqlAmazon = "";
      
      $sartid=$this->similaire($artid);
    	if($sartid!=false) $artid=$sartid;
    	$r=$this->DB->query_first("SELECT si.*,sm.*,(SELECT COUNT(*) FROM stock_item WHERE artid=si.artid AND state=1 ".$sqlAmazon.") AS qte FROM stock_item si INNER JOIN stock_mini sm using(artid) INNER JOIN article a USING(artid) WHERE artid=".$artid." GROUP BY si.artid");
      //if($r['artid']=="") $r=$this->DB->query_first("SELECT * FROM stock_mini WHERE artid=".$artid);
    	if($r['artid']=="" || ($r['qte']==0 && $r['mini']==0)) return false;
    	$item=new stock_item($this->DB,$r);
    	$item->qte=$r['qte'];
    	return $item;
    }

    //***************************************************************************************
    function setmini($artid,$mini,$alert,$artids,$lieu,$delai){
		global $user_info;
    	$r=$this->DB->query_first("SELECT * FROM stock_mini WHERE artid=".intval($artid)." AND lieu=".$lieu);
    	$update=($r['artid']==$artid);
    	if($update){
    	    $sql="UPDATE stock_mini SET mini=".intval($mini).", alert=".intval($alert).", artids='".$artids."', lieu='".$lieu."', delai=".intval($delai)." WHERE artid=".$artid." AND lieu=".$lieu;
			
			$result=$this->DB->query($sql);
			
			$sqllog="INSERT INTO stock_log
							(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
							VALUES 
							(NULL, '".$artid."', '".time()."', 'stock_mini', 'update', 0 ,0, 0, 0, '".$user_info['username']."', '".$lieu."', '".$mini."', '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
			$this->DB->query($sqllog);
			
    	}else{
    	    $sql="INSERT INTO stock_mini SET artid=".$artid.", mini=".intval($mini).", alert=".intval($alert).", artids='".$artids."', lieu='".$lieu."', delai=".intval($delai);
			
			$result=$this->DB->query($sql);
			
			$sqllog="INSERT INTO stock_log
							(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
							VALUES 
							(NULL, '".$artid."', '".time()."', 'stock_mini', 'insert', 0 ,0, 0, 0, '".$user_info['username']."', '".$lieu."', '".$mini."', '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
			$this->DB->query($sqllog);
		}
    	return $result;
    }

    //***************************************************************************************
    function delmini($artid,$lieu){
    	$sql="DELETE FROM stock_mini WHERE artid=".intval($artid)." AND lieu=".intval($lieu);
		
		$result=$this->DB->query($sql);
			
		$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$artid."', '".time()."', 'stock_mini', 'delete', 0 ,0, 0, 0, '".$user_info['username']."', '".$lieu."', 0, '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
		$this->DB->query($sqllog);
		
    	return $result;
    }

    //***************************************************************************************
    function lieux(){
    	$return=array();
    	$requete=$this->DB->query("SELECT f.societe,f.ville,f.id FROM fournisseur f INNER JOIN stock_mini ON(stock_mini.lieu=f.id) GROUP By lieu ORDER BY f.societe");
    	while($lieu=$this->DB->fetch_array($requete))
			$return[$lieu[2]]=$lieu[0]." ".$lieu[1];
    	return $return;
    }

    //***************************************************************************************
    function marques_liste(){
    	$return=array();
    	$requete=$this->DB->query("SELECT m.libelle FROM article_marque am INNER JOIN marque m ON m.marqueid = am.marqueid INNER JOIN stock_mini s ON am.artid = s.artid GROUP BY m.libelle ORDER BY m.libelle");
    	while($marque=$this->DB->fetch_array($requete))
			$return[]=$marque[0];
    	return $return;
    }
	
	//***************************************************************************************
    function lieudetail($lieuid){
    	$return="";
    	$lieu=$this->DB->query_first("SELECT societe, ville FROM fournisseur WHERE id='".$lieuid."' ");
		$return=$lieu[societe]." ".$lieu[ville];
    	return $return;
    }

    //***************************************************************************************
    function artids($sortkey="artid"){
    	$return=array();
    	$r=$this->DB->query("SELECT artid,mini,alert,artids,lieu,delai FROM stock_mini");
    	while($a=$this->DB->fetch_array($r)){
    	    $item=new stock_item($this->DB,$a);
    	    $return[$a['lieu']][$item->$sortkey]=$item;
    	}
    	ksort($return);
    	return $return;
    }

    //***************************************************************************************
    function search($artid=null,$lieu=null,$dest=null,$state=1){
    	$return=array();
    	if(is_array($state))
    	   $state=implode("','",$state);
    	else
    	   $state=intval($state);
    	$sql="SELECT si.*,sm.mini,sm.alert,sm.artids,sm.delai FROM stock_item si
				LEFT JOIN stock_mini sm USING (artid)
				LEFT JOIN stock_cmd sc ON sc.id = si.stock_cmd_id
				WHERE state IN ('".$state."')";
    	if(!is_null($artid))
			$sql.=" AND (artid='$artid' OR LOCATE('$artid,',CONCAT(artids,',')))";
    	if(!is_null($lieu)){
                if(is_array($lieu)){
                    $sql.=" AND ((si.lieu='' AND sc.livraison IN (".implode(",",$lieu).")) OR (si.lieu IN (".implode(",",$lieu).")))";
                }else{
                    $sql.=" AND ((si.lieu='' AND sc.livraison = '".$lieu."') OR (si.lieu='".$lieu."'))";
                }
        }
    	if(!is_null($dest))
			$sql.=" AND dest LIKE '".$dest."'";
    	$sql.=" GROUP BY id";
    	$requete=$this->DB->query($sql);
    	while($item=$this->DB->fetch_array($requete)){
    	    $artids=explode(',',$item['artids']);
    	    if(is_null($artid) || $artid==$item['artid'] || in_array($artid,$artids)){
				$return[$item['id']]=new stock_item($this->DB,$item);
			}
  	    }
  	    return $return;
    }

    //***************************************************************************************
    function cmd_nb(){
    	$req=$this->DB->query_first("SELECT MAX(id) FROM stock_cmd");
    	return $req[0];
    }

    //***************************************************************************************
    function stocknonfacture($destinataire=null){
    	$return=array();
    	$destinataire=stripslashes($destinataire);
    	$r=$this->DB->query("SELECT stock_cmd_id FROM stock_item WHERE facture='' AND state >=0");
    	while($cmd=$this->DB->fetch_array($r)){
    	    $stockcmd=new stock_cmd($this->DB,$cmd[0]);
    	    if($destinataire===null || $destinataire==$stockcmd->destinataire) $return[$cmd[0]]=$stockcmd;
    	}
    	return $return;
    }

    //***************************************************************************************
    function stocktrsptnonfacture($destinataire=null){
    	$return=array();
    	/*$sql="SELECT * FROM stock_cmd WHERE trspt<>'0' AND facture=''";
    	if($destinataire!==null) $sql.=" AND trspt='".addslashes($destinataire)."'";
    	$r=$this->DB->query($sql);
    	while($cmd=$this->DB->fetch_array($r)){
    	    $stockcmd=new stock_cmd($this->DB,$cmd);
    	    $return[$cmd['id']]=$stockcmd;
    	}*/
    	$sql="SELECT * FROM stock_item WHERE trspt<>'' AND facture_trspt=''";
    	if($destinataire!==null) $sql.=" AND trspt='".addslashes($destinataire)."'";
    	$r=$this->DB->query($sql);
    	while($item=$this->DB->fetch_array($r)){
    	    if(is_a($return[$item['stock_cmd_id']],"stock_cmd")){
    		    $return[$item['stock_cmd_id']]->items[]=new stock_item($this->DB,$item);
    	    }else{
        		$return[$item['stock_cmd_id']]=new stock_cmd($this->DB,$item['stock_cmd_id']);
        		$return[$item['stock_cmd_id']]->items[]=new stock_item($this->DB,$item);
    	    }
    	}
    	return $return;
    }

    //***************************************************************************************
    function getfacture($ffid,$type=null){
    	$return=array();
    	if(is_null($type) || $type=="fourn"){
  	    $r=$this->DB->query("SELECT * FROM stock_item WHERE facture='".$ffid."'");
  	    while($data=$this->DB->fetch_array($r)){
      		$item=new stock_item($this->DB,$data);
      		if(is_a($return[$data['stock_cmd_id']][$data['artid']],"stock_item")){
      		    $return[$data['stock_cmd_id']][$data['artid']]->qte++;
      		    $return[$data['stock_cmd_id']][$data['artid']]->paht+=$item->paht;
      		    $return[$data['stock_cmd_id']][$data['artid']]->ecopartht+=$item->ecopartht;
      		    $return[$data['stock_cmd_id']][$data['artid']]->prix_trspt+=$item->prix_trspt;
      		}else{
      		    $return[$data['stock_cmd_id']][$item->artid]=$item;
      		}
  	    }
	   }

	   if(is_null($type) || $type=="trspt"){
  	    $r=$this->DB->query("SELECT * FROM stock_item WHERE facture_trspt='".$ffid."'");
  	    while($data=$this->DB->fetch_array($r)){
      		$item=new stock_item($this->DB,$data);
      		if(is_a($return[$data['stock_cmd_id']][$data['artid']],"stock_item")){
      		    $return[$data['stock_cmd_id']][$data['artid']]->qte++;
      		    $return[$data['stock_cmd_id']][$data['artid']]->paht+=$item->prix_trspt;
      		}else{
      		    $return[$data['stock_cmd_id']][$item->artid]=$item;
      		    $return[$data['stock_cmd_id']][$item->artid]->paht=$item->prix_trspt;
      		}
  	   }
  	}

  	if(is_null($type) || $type=="cmdtrspt"){
  	    $r=$this->DB->query("SELECT * FROM stock_cmd WHERE facture='".$ffid."'");
  	    while($data=$this->DB->fetch_array($r)){
      		$cmd=new stock_cmd($this->DB,$data);
      		$return[$cmd->id]=$cmd;
  	    }
  	}

	  return $return;
  }

  //***************************************************************************************
  function removefacture($ffid,$artid,$cmdid,$type=""){
  	if($ffid=="")
		return false;
	
	$sql="UPDATE stock_item SET facture='' WHERE facture='".mysqli_real_escape_string($this->DB->link_id,$ffid)."' AND stock_cmd_id=".$cmdid." AND artid=".intval($artid);
  	$r=$this->DB->query($sql);
	
	$sqllog="INSERT INTO stock_log
					(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
					VALUES 
					(NULL, '".$artid."', '".time()."', 'stock_item', 'update', '".$cmdid."', 0, 0, 0, '".$user_info['username']."', 0, 0, '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
	$this->DB->query($sqllog);
	
  	$sql="UPDATE stock_item SET facture_trspt='' WHERE facture_trspt='".mysqli_real_escape_string($this->DB->link_id,$ffid)."' AND stock_cmd_id=".$cmdid." AND artid=".intval($artid);
	
	$r=$this->DB->query($sql);
	
	$sqllog="INSERT INTO stock_log
					(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
					VALUES 
					(NULL, '".$artid."', '".time()."', 'stock_item', 'update', '".$cmdid."', 0, 0, 0, '".$user_info['username']."', 0, 0, '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
	$this->DB->query($sqllog);
	
	
  	return $r;
  }

  //*************************************************************************************** 
  function getdest($artid,$lieu=null,$state=3,$similaire=null){
  	$return=array();
  	$sqllieu="";
  	if(!is_null($lieu)) $sqllieu=" AND si.lieu='$lieu'";
  	if(!is_null($similaire)){
  		$artid="artid IN (".$artid.",".$similaire.")";
  	}
  	else{
  		$artid="artid=".$artid;
  	}
  	$req=$this->DB->query("SELECT dest FROM stock_item si WHERE ".$artid.$sqllieu." AND state=".$state." GROUP BY dest");
  	while($cmd=$this->DB->fetch_array($req)){
  	    if(substr($cmd[0],0,7)=="facture") $return[]=substr($cmd[0],8);
  	}
  	return $return;
  }

  //***************************************************************************************
  function similaire($artid){
  	$r=$this->DB->query("SELECT artid,artids FROM stock_mini WHERE (artids LIKE '%,".$artid."%' OR artids LIKE '%".$artid.",%' OR artids = '".$artid."') OR artid=$artid");
  	while($a=$this->DB->fetch_array($r)){
  	    $spartid=explode(",",$a['artids']);
  	    if(in_array($artid,$spartid) || $a['artid']==$artid) return $a['artid'];
  	}
  	return $artid;
  }

  //***************************************************************************************
  function is_similaire($artid){
  	$r=$this->DB->query_first("SELECT artid FROM stock_mini WHERE artids LIKE '%,".$artid."%' OR artids LIKE '%".$artid.",%' OR artids = '".$artid."'");
  	if(count($r)>0){
  		return $r['artid'];
  	}
  	else{
  		return false;
  	}
  }

  //***************************************************************************************
  function suivi($artid=null,$lieu=null,$from=null,$to=null,$state=null){
    	if(intval($lieu)==0)
			$lieu=null;
    	$returncmd=array();
      
    	$sql="SELECT sc.* FROM stock_cmd sc LEFT JOIN stock_item si ON (sc.id=si.stock_cmd_id) WHERE 1";
    	if(!is_null($artid))
			$sql.=" AND artid=".$artid;
    	if(!is_null($lieu))
			$sql.=" AND lieu=".intval($lieu);
    	if(!is_null($from) && !is_null($to))
			$sql.=" AND sc.date BETWEEN ".intval($from)." AND ".intval($to);
    	if(!is_null($state))
			$sql.=" AND si.state='".$state."'";
      
    	$r=$this->DB->query($sql);
    	while($cmd=$this->DB->fetch_array($r))
			$returncmd[$cmd['date']]=new stock_cmd($this->DB,$cmd);
    
    	$returnin=array();
    	$sql="SELECT * FROM stock_item WHERE state>0";
    	if(!is_null($artid)) $sql.=" AND artid=".$artid;
    	if(!is_null($lieu)) $sql.=" AND lieu=".intval($lieu);
    	if(!is_null($from) && !is_null($to)) $sql.=" AND date BETWEEN ".intval($from)." AND ".intval($to);
    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
            $item=new stock_item($this->DB,$data);
            $item->qte=1;
            
            $d=$data['date']+$data['artid'];
            if(isset($returnin[$d]))
                $returnin[$d]->qte++;
            else{
                $returnin[$d]=$item;
            }
    	}
    	
    	$sql="SELECT si.id,si.stock_cmd_id,si.artid,st.from AS lieu,si.origin,si.dest,si.utilisateur,si.state,si.date FROM stock_item si INNER JOIN stock_transfer st ON (si.id=st.itemid)
    	WHERE st.from=".intval($lieu)." AND si.date BETWEEN ".intval($from)." AND ".intval($to);
    	if(!is_null($artid)) $sql.=" AND si.artid=".$artid;
    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
            $item=new stock_item($this->DB,$data);
            $item->qte=1;
            
            $d=$data['date']+$data['artid'];
            if(isset($returnin[$d]))
                $returnin[$d]->qte++;
            else{
                $returnin[$d]=$item;
            }
    	}
    	
    
    	$returnfacture=array();
    	$sql="SELECT stock_item.*,stock_mini.mini,stock_mini.artids,stock_mini.alert,stock_mini.delai FROM stock_item LEFT JOIN stock_mini ON (stock_item.artid=stock_mini.artid AND stock_item.lieu=stock_mini.lieu) WHERE dest LIKE 'facture%'";
    	if(!is_null($artid))
			$sql.=" AND stock_item.artid=".$artid;
    	if(!is_null($lieu))
			$sql.=" AND stock_item.lieu=".intval($lieu);
    	$sql.=" AND state=3 GROUP BY stock_item.id";

    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
    	    $item=new stock_item($this->DB,$data);
    	    $item->destination();
    	    $item->qte=1;
    	   
    	    if(is_null($from) || ($item->date_expedition>=$from && $item->date_expedition<$to)){
                    if($item->date_expedition>0){
                        $d=$item->date_expedition+$item->facturid+$item->artid;
                        if(isset($returnfacture[$d]))
                            $returnfacture[$d]->qte++;
                        else
                            $returnfacture[$d]=clone $item;
                    }/*else
                        $returnfacture[0]=clone $item;*/
    	    }
    	    /*$item->date_expedition=0;
    	    if(isset($returnfacture[$item->date_adv]))
    		    $returnfacture[$item->date_adv]->qte++;
    	    else
    		    $returnfacture[$item->date_adv]=$item;*/
        }
    
    	$returnperdu=array();
    	$sql="SELECT si.*,sm.mini,sm.artids,sm.alert,sm.delai FROM stock_item si LEFT JOIN stock_mini sm ON (si.artid=sm.artid AND si.lieu=sm.lieu) WHERE dest IN ('perdu','autre','maj','cadeau')";
    	if(!is_null($artid))
			$sql.=" AND si.artid=".$artid;
    	if(!is_null($lieu))
			$sql.=" AND si.lieu=".intval($lieu);
    	if(!is_null($from) && !is_null($to))
			$sql.=" AND date_sortie BETWEEN ".intval($from)." AND ".intval($to);
      
    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
    	     $item=new stock_item($this->DB,$data);
    	     $item->destination();
    	     $item->qte=1;
    
    	     $d=$item->date_sortie+$item->artid;
    	     if(isset($returnperdu[$d]))
    		      $returnperdu[$d]->qte++;
    	     else
    		      $returnperdu[$d]=clone $item;
    	}
    	/*$r=$this->DB->query("SELECT lf.artid,efl.date,efl.expediteur,f.facturid,f.dateexpedition,f.valide FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON(lf.lignefactureid=efl.ligne_facture_id) INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE efl.facture='STOCK'");
    	while($efl=$this->DB->fetch_array($r)){
    	    $artid=$this->similaire($efl['artid']);
    	    $item=new stock_item($this->DB,$artid);
    	    $item->utilisateur_adv=$efl['expediteur'];
    	    $item->date_adv=$efl['date'];
    	    $item->facturid=$efl['facturid'];
    	    $return[$efl['date']]=$item;
    
    	    if($efl['valide']==5 || $efl['valide']==8){
    		$sp=explode("-",$efl['dateexpedition']);
    		$dateexpedition=mktime(0,0,0,$sp[1],$sp[2],$sp[0]);
    		$item=new stock_item($this->DB,$artid);
    		$item->utilisateur_adv=$efl['expediteur'];
    		$item->date_adv=$efl['date'];
    		$item->facturid=$efl['facturid'];
    		$item->date_expedition=$dateexpedition;
    		$return[$dateexpedition]=$item;
    	    }
    	}*/
    	
    	//TSI SB#2308: ajout des informations suivi article pour les mouvements de stock 
    	/*$returnsuiviart=array();
    	$reqSuiviArt = "SELECT artid, commercial, date, suivi FROM suivi_article";
    	$reqSuiviArt .= " WHERE suivi LIKE \"%produits du stock%\" ";
    	$reqSuiviArt .=	" AND DATE BETWEEN ".intval($from)." AND ".intval($to);
        if(!is_null($artid))
			$reqSuiviArt.=" AND artid=".$artid;
    	
    	$r=$this->DB->query($reqSuiviArt);
    	
    	while($data=$this->DB->fetch_array($r)){
    		$item=new suivi_transfert($this->DB,$data);
    		$returnsuiviart[$item->date]=clone $item;
    	}*/
    	
    	$returnsuiviart=array();
    	$sql="SELECT si.*,st.date AS trdate FROM stock_transfer st INNER JOIN stock_item si ON (si.id=st.itemid)
    	WHERE st.date BETWEEN ".intval($from)." AND ".intval($to)." AND si.artid=".intval($artid)." AND st.from=".intval($lieu);
    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
            $item=new stock_item($this->DB,$data);
            $item->destination();
            $item->qte=1;
            
            $d=$data['trdate'];
            if(isset($returnsuiviart[$d]))
                    $returnsuiviart[$d]->qte++;
            else
                    $returnsuiviart[$d]=clone $item;
    	}

      $returnsuivimaj = array();
      $sql = "SELECT * FROM stock_maj WHERE artid=".intval($artid)." AND lieu =".intval($lieu);
      $r=$this->DB->query($sql);
      while($data=$this->DB->fetch_array($r)){
        $d=$data['date'];
        if(isset($returnsuivimaj[$d])){
            $returnsuivimaj[$d]['qte'] = $returnsuivimaj[$d]['qte']++;
        }else{
            $returnsuivimaj[$d]= array("artid" => $artid, "date" => $d, "qte" => $data['qte'], "lieu" => $data['lieu'], "commentaire" => $data['commentaire'], "user" => $data['user']);
        }
      }
    	
    	ksort($returnin);
    	ksort($returnfacture);
    	ksort($returnperdu);
    	ksort($returnsuiviart);
      ksort($returnsuivimaj);
      
    	return array("cmd"=>$returncmd, "in"=>$returnin, "facture"=>$returnfacture, "perdu"=>$returnperdu, "suivi"=>$returnsuiviart, "maj"=>$returnsuivimaj);
    	//return array("cmd"=>$returncmd,"in"=>$returnin,"facture"=>$returnfacture,"perdu"=>$returnperdu);
   }
    
    
   //***************************************************************************************
   function manu($artid=null,$lieu=null,$from=null,$to=null){
    
    	$returnin=array();
    	$sql="SELECT * FROM stock_item WHERE origin='manu'";
    	if(!is_null($artid)) $sql.=" AND artid=".$artid;
    	if(!is_null($lieu)) $sql.=" AND lieu=".intval($lieu);
    	if(!is_null($from) && !is_null($to)) $sql.=" AND date BETWEEN ".intval($from)." AND ".intval($to);
      
    	$r=$this->DB->query($sql);
    	while($data=$this->DB->fetch_array($r)){
    	  $d=$data['date']+$data['artid'];
    	  if(isset($returnin[$d]))
    	    $returnin[$d]->qte++;
    	  else{
    	    $item=new stock_item($this->DB,$data);
    	    $item->qte=1;
    	    $returnin[$d]=$item;
    	  }
    	}
	
	    return $returnin;
   }
	
  //***************************************************************************************
  function value($date=null,$details=false){
  	if(is_null($date)){
  	  if($details){
  	     $req=$this->DB->query("SELECT * FROM stock_item WHERE state IN (1,2)");
  	     while($itm=$this->DB->fetch_array($req)) $total[$itm['id']]=new stock_item($this->DB,$itm);
  	     return $total;
  	  }else{
  	  	/*$sql = "SELECT SUM(af.prix)";
  	  	$sql .= " FROM stock_item si";
  	  	$sql .= " INNER JOIN article_fournisseur af ON (af.artid = si.artid)";
  	  	$sql .= " WHERE af.principal LIKE '%base%'";
  	  	$sql .= " AND si.state IN (1,2)";
  	  	$req = $this->DB->query_first($sql);*/
  	    $req=$this->DB->query_first("SELECT SUM(paht) FROM stock_item WHERE state IN (1,2)");
  	    return $req[0];
  	  }
  	}else{
  	  $total=0;
  	  if($details) $total=array();
  	  $req=$this->DB->query("SELECT * FROM stock_item WHERE date<".intval($date));
  	  while($itm=$this->DB->fetch_array($req)){
  	      $add=false;
  	      if(substr($itm['dest'],0,7)=="facture"){
      		  $facturid=substr($itm['dest'],8);
      		  $facture=$this->DB->query_first("SELECT UNIX_TIMESTAMP(dateexpedition) FROM facture WHERE facturid='".intval($facturid)."'");
      		  if($facture[0]>$date || $itm['state']==2) $add=true;
  	      }elseif($itm['state']==1)
  		      $add=true;
  
  	      if($add){
    		    if($details){
    		      $total[$itm['id']]=new stock_item($this->DB,$itm);
    		    }else
    		      $total+=$itm['paht'];
  	      }
  	  }
  	  return $total;
  	}
  }

  //***************************************************************************************
  function updatecache($artid=null){
    	$this->DB->query("UPDATE article SET stock=0");
    	$sql="SELECT artid,artids,(SELECT COUNT(*) FROM stock_item WHERE artid=sm.artid AND state=1) AS qte FROM stock_mini sm";
    	if(!is_null($artid)) $sql.=" WHERE artid=".$artid;
    	
    	$sqlartids=array();
    	$req=$this->DB->query($sql);
    	while($art=$this->DB->fetch_array($req)){
    	    if($art['qte']>0){
        		$sqlartids[]=$art['artid'];
        		if($art['artids']!="") $sqlartids[]=$art['artids'];
    	    }
    	}
    	
    	if(count($sqlartids)>0)
    	    $r=$this->DB->query("UPDATE article SET stock=1 WHERE artid IN (".implode(",",$sqlartids).")");
    	$sqlartids=array();
    
    	$sql="SELECT al.lotid,a.stock,sum(a.stock*al.qte),sum(al.qte) FROM article_lot al INNER JOIN article a WHERE al.artid=a.artid group by al.lotid";
    	$req=$this->DB->query($sql);
    	while($lot=$this->DB->fetch_array($req)){
    	    if($lot[2]==$lot[3]) $sqlartids[]=$lot[0];
    	}
    	if(count($sqlartids)>0)
    	    $r=$this->DB->query("UPDATE article SET stock=1 WHERE artid IN (".implode(",",$sqlartids).")");	
  }
    
  //***************************************************************************************
  function ajust_date($lieu){
    	$return=array();
    	$req=$this->DB->query("SELECT * FROM stock_ajust WHERE lieu=".$lieu." GROUP BY date");
    	while($ajust=$this->DB->fetch_array($req)){
    	    $return[$ajust['date']]=$ajust['utilisateur'];
    	}
    	return $return;
  }

  //***************************************************************************************
  function ajust_list($lieu,$date){
    	$return=array();
    	$req=$this->DB->query("SELECT stock_ajust.* FROM stock_ajust
								INNER JOIN article ON(stock_ajust.artid=article.artid)
								WHERE lieu=".$lieu." AND date=".$date." GROUP BY stock_ajust.artid
								ORDER BY article.artcode ASC");
    	while($data=$this->DB->fetch_array($req)){
    	    $return[]=new stock_ajust($this->DB,$data);
    	}
	    return $return;
    }

  //***************************************************************************************
  function enlevements($destinataire=null,$date=null,$late=null,$days=1,$typedate=null){
  	$return=array();
  	if(is_null($date)) $date=mktime(0,0,0);
    
  	$sql="SELECT si.* FROM stock_item si INNER JOIN stock_cmd sc ON(sc.id=si.stock_cmd_id) WHERE ".(is_null($late)?"date_enlevement>=".$date." AND date_enlevement<".($date+($days*86400)):"date_enlevement BETWEEN 0 AND ".$date)." AND state=0".($typedate=='indate'?' AND enlevement_fait!=1' :'').(!is_null($destinataire)?" AND (destinataire='".mysqli_real_escape_string($this->DB->link_id,$destinataire)."' OR si.trspt='".mysqli_real_escape_string($this->DB->link_id,$destinataire)."')":"");
  	$req=$this->DB->query($sql);
  	while($item=$this->DB->fetch_array($req)){
  	    if(!is_a($return[$item['stock_cmd_id']],"stock_cmd")) $return[$item['stock_cmd_id']]=new stock_cmd($this->DB,$item['stock_cmd_id']);
  	    $return[$item['stock_cmd_id']]->items[]=new stock_item($this->DB,$item);
  	}

	  return $return;
  }

  //***************************************************************************************
  function non_commande($type){
  	if($type=="fourn") $sql="SELECT * FROM stock_item WHERE state=-1 AND date_enlevement=0";
  	if($type=="trspt") $sql="SELECT * FROM stock_item WHERE  state=0 AND trspt='' AND date_livraison=0";
  
  	$return=array();
  	$req=$this->DB->query($sql);
  	while($item=$this->DB->fetch_array($req)){
  	    if(!is_a($return[$item['stock_cmd_id']],"stock_cmd")) $return[$item['stock_cmd_id']]=new stock_cmd($this->DB,$item['stock_cmd_id']);
  	    $return[$item['stock_cmd_id']]->items[]=new stock_item($this->DB,$item);
  	}

	  return $return;
  }
  
  //***************************************************************************************  
  function facture_date($from,$to){
  	$return=array();
  	$sql="SELECT si.* 
  	FROM stock_item si INNER JOIN facture_fournisseur ff on(numero=si.facture) 
  	WHERE ff.date_facture BETWEEN ".intval($from)." AND ".intval($to);
  	$req=$this->DB->query($sql);
  	while($data=$this->DB->fetch_array($req)){
  	    $return[$data['stock_cmd_id']][]=new stock_item($this->DB,$data);
  	}
  	return $return;
  }
    
  //***************************************************************************************
  function livraison_items($from,$to){
  	$return=array();
  	$sql="SELECT * FROM stock_item WHERE stock_cmd_id>0 AND state>0 AND date BETWEEN ".intval($from)." AND ".intval($to);
  	$req=$this->DB->query($sql);
  	while($data=$this->DB->fetch_array($req)){
  	    $return[]=new stock_item($this->DB,$data);
  	}
  	return $return;
  }
    
}
// Fin class stock ******************************************************************

// *********************************************************************************
// Class stock_item ****************************************************************
// *********************************************************************************
class stock_item {
    private $DB;

    public $id=0;
    public $stock_cmd_id;
    public $artid;
    public $paht;
    public $ecopartht;
    public $lieu;
    public $trspt;
    public $prix_trspt;
    public $rayon;
    public $origin;
    public $dest;
    public $utilisateur;
    public $date;
    public $date_enlevement;
    public $date_livraison;
    public $date_enlevement_origin;
    public $date_livraison_origin;
    public $state;
    public $enlevement_fait;
    public $facture;
    public $facture_trspt;
    public $comment;
    public $comment_trspt;
    public $visible;
    public $commandable;

    public $facturid;
    public $date_adv;
    public $utilisateur_adv;
    public $date_expedition;
    public $utilisateur_sortie;

    public $artcode;
    public $libelle;
    public $ref;
    public $marqueid;
    public $marque;
    public $typearticleid;
    public $artpaht=0;
    public $artecopartht;
    public $qte=1;
    public $infos;
    public $mini;
    public $alert;
    public $delai;
    public $artids;
    public $img;
    public $poids_brut;
    public $hauteur_brut;
    public $profondeur_brut;
    public $largeur_brut;
    public $volume_brut;
    public $delailivraison;
    public $similaire=array();

    public $statetxt=array(0=>"en commande",1=>"en stock",2=>"réservé",3=>"expédié");

    //***************************************************************************************
    function __construct($DB,$data=null){
    	$this->DB=$DB;
    	if($data==null || intval($data)==0) return false;
    
    	if(is_array($data))
    	  $this->load($data);
    	else{
    	  $item=$this->DB->query_first("SELECT * FROM stock_item LEFT JOIN stock_mini USING (artid) WHERE id=".intval($data));
    	  if($item==false) return false;
    	  $this->load($item);
    	}
    }

    //***************************************************************************************
    function loadartid($artid){
    	$item=$this->DB->query_first("SELECT * FROM stock_mini LEFT JOIN stock_item USING (artid) WHERE stock_mini.artid=".intval($artid));
    	if($item==false) return false;
    	$this->load($item);
    	return true;
    }

    //***************************************************************************************
    function load($data){
    	$this->id=$data['id'];
    	$this->stock_cmd_id=$data['stock_cmd_id'];
    	$this->artid=$data['artid'];
    	$this->paht=$data['paht'];
      $this->ecopartht=$data['ecopartht'];
    	$this->lieu=$data['lieu'];
    	$this->trspt=$data['trspt'];
    	$this->prix_trspt=$data['prix_trspt'];
    	$this->rayon=$data['rayon'];
    	$this->origin=$data['origin'];
    	$this->dest=$data['dest'];
    	$this->utilisateur=$data['utilisateur'];
    	$this->date=$data['date'];
    	$this->date_enlevement=$data['date_enlevement'];
    	$this->date_livraison=$data['date_livraison'];
    	$this->date_enlevement_origin=$data['date_enlevement_origin'];
    	$this->date_livraison_origin=$data['date_livraison_origin'];
    	$this->date_sortie=$data['date_sortie'];
    	$this->enlevement_fait=$data['enlevement_fait'];
    	$this->utilisateur_sortie=$data['utilisateur_sortie'];
    	$this->state=$data['state'];
    	$this->facture=$data['facture'];
    	$this->facture_trspt=$data['facture_trspt'];
    	$this->infos=$data['infos'];
    	$this->mini=$data['mini'];
    	$this->alert=$data['alert'];
    	$this->delai=$data['delai'];
    	$this->artids=$data['artids'];
    	$this->similaire=array();
      
    	if($this->artids!=""){
    	    $artids=explode(",",$this->artids);
    	    $r=$this->DB->query("SELECT artid,artcode FROM article WHERE artid IN ('".implode("','",$artids)."')");
    	    while($a=$this->DB->fetch_array($r)) $this->similaire[$a['artid']]=$a['artcode'];
    	}
      
    	$this->comment=$data['comment'];
    	$this->comment_trspt=$data['comment_trspt'];
    
    	$article=$this->DB->query_first("SELECT a.artcode,a.artcode_eurocave,a.libelleFR,a.paht,a.ecopartht,marqueid,a.typearticleid,m.libelle AS marque,a.emballage_poids1,a.emballage_poids2,a.emballage_poids3,a.emballage_hauteur1,a.emballage_hauteur2,a.emballage_hauteur3,a.emballage_largeur1,a.emballage_largeur2,a.emballage_largeur3,a.emballage_profondeur1,a.emballage_profondeur2,a.emballage_profondeur3,delailivraison,a.active, a.commandable FROM article a INNER JOIN article_marque am USING (artid) INNER JOIN marque m USING (marqueid) WHERE a.artid=".$this->artid);
      
    	$this->artcode=$article['artcode'];
    	$this->libelle=$article['libelleFR'];
    	$this->ref=$article['artcode_eurocave'];
    	$this->artpaht=round($article['paht'],2);
    	$this->artecopartht=round($article['ecopartht'],3);
    	$this->marque=$article['marque'];
    	$this->marqueid=$article['marqueid'];
    	$this->typearticleid=$article['typearticleid'];
    	$this->img="/configurations/www.mon-espace-cuisson.com/images/produits/br/".$this->artid.".jpg";
    	$this->poids_brut=$article['emballage_poids1']+$article['emballage_poids2']+$article['emballage_poids3'];
    	$this->hauteur_brut=intval($article['emballage_hauteur1'])+intval($article['emballage_hauteur2'])+intval($article['emballage_hauteur3']);
    	$this->largeur_brut=intval($article['emballage_largeur1'])+intval($article['emballage_largeur2'])+intval($article['emballage_largeur3']);
    	$this->profondeur_brut=intval($article['emballage_profondeur1'])+intval($article['emballage_profondeur2'])+intval($article['emballage_profondeur3']);
    	$this->volume_brut=(intval($article['emballage_hauteur1'])*intval($article['emballage_largeur1'])*intval($article['emballage_profondeur1']))/pow(10,9)
			+ ((intval($article['emballage_hauteur2'])*intval($article['emballage_largeur2'])*intval($article['emballage_profondeur2']))/pow(10,9))
			+ ((intval($article['emballage_hauteur3'])*intval($article['emballage_largeur3'])*intval($article['emballage_profondeur3']))/pow(10,9));
    	$this->delailivraison=$article['delailivraison'];
    	$this->active=$article['active'];
    	$this->commandable=$article['commandable'];
      
    }

    //***************************************************************************************
    function insert(){
		$sql="INSERT INTO stock_item
				SET
					stock_cmd_id=".intval($this->stock_cmd_id).",
					artid=".intval($this->artid).",
					paht=".floatval($this->paht).",
					ecopartht=".floatval($this->ecopartht).",
          lieu='".mysqli_real_escape_string($this->DB->link_id,$this->lieu)."',
					rayon='".mysqli_real_escape_string($this->DB->link_id,$this->rayon)."',
					trspt='".mysqli_real_escape_string($this->DB->link_id,$this->trspt)."',
					prix_trspt=".floatval($this->prix_trspt).",
					origin='".mysqli_real_escape_string($this->DB->link_id,$this->origin)."',
					dest='".mysqli_real_escape_string($this->DB->link_id,$this->dest)."',
					utilisateur='".$this->utilisateur."',
					date=".intval($this->date).",
					date_enlevement=".intval($this->date_enlevement).",
					date_livraison=".intval($this->date_livraison).",
					date_enlevement_origin=".intval($this->date_enlevement_origin).",
					date_livraison_origin=".intval($this->date_livraison_origin).",
					date_sortie=".intval($this->date_sortie).",
					utilisateur_sortie='".$this->utilisateur_sortie."',
					state=".intval($this->state).",
					infos='".mysqli_real_escape_string($this->DB->link_id,$this->infos)."',
					facture='".mysqli_real_escape_string($this->DB->link_id,$this->facture)."',
					facture_trspt='".mysqli_real_escape_string($this->DB->link_id,$this->facture_trspt)."',
					comment='".mysqli_real_escape_string($this->DB->link_id,$this->comment)."',
					comment_trspt='".mysqli_real_escape_string($this->DB->link_id,$this->comment_trspt)."'";
    	$result=$this->DB->query($sql);
		
		$insert_id=$this->DB->insert_id();
		
		$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$this->artid."', '".$this->date."', 'stock_item', 'insert', 0, '".$insert_id."', 0, 0, '".$this->utilisateur."', '".$this->lieu."',1 ,'".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
		$this->DB->query($sqllog);
    	return $result;
    }

    //***************************************************************************************
    function update(){
		$sql="UPDATE stock_item
				SET
					stock_cmd_id=".intval($this->stock_cmd_id).",
					artid=".intval($this->artid).",
					paht=".floatval($this->paht).",
					ecopartht=".floatval($this->ecopartht).",
					lieu='".mysqli_real_escape_string($this->DB->link_id,$this->lieu)."',
					rayon='".mysqli_real_escape_string($this->DB->link_id,$this->rayon)."',
					trspt='".mysqli_real_escape_string($this->DB->link_id,$this->trspt)."',
					prix_trspt=".floatval($this->prix_trspt).",
					origin='".mysqli_real_escape_string($this->DB->link_id,$this->origin)."',
					dest='".mysqli_real_escape_string($this->DB->link_id,$this->dest)."',
					utilisateur='".$this->utilisateur."',
					date=".intval($this->date).",
					date_enlevement=".intval($this->date_enlevement).",
					date_livraison=".intval($this->date_livraison).",
					date_enlevement_origin=".intval($this->date_enlevement_origin).",
					date_livraison_origin=".intval($this->date_livraison_origin).",
					date_sortie=".intval($this->date_sortie).",
					enlevement_fait=".intval($this->enlevement_fait).",
					utilisateur_sortie='".$this->utilisateur_sortie."',
					state=".intval($this->state).",
					infos='".mysqli_real_escape_string($this->DB->link_id,$this->infos)."',
					facture='".mysqli_real_escape_string($this->DB->link_id,$this->facture)."',
					facture_trspt='".mysqli_real_escape_string($this->DB->link_id,$this->facture_trspt)."',
                    comment='".mysqli_real_escape_string($this->DB->link_id,$this->comment)."',
                    comment_trspt='".mysqli_real_escape_string($this->DB->link_id,$this->comment_trspt)."'
					WHERE id=".intval($this->id);
    	$result=$this->DB->query($sql);
		
		$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$this->artid."', '".$this->date."', 'stock_item', 'update', 0, '".$this->id."', 0, 0, '".$this->utilisateur."', '".$this->lieu."', 1, '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
		$this->DB->query($sqllog);
    	return $result;
    }
    
    //***************************************************************************************
    function delete(){
		$sql="DELETE FROM stock_item WHERE artid=".$this->artid." AND dest='".$this->dest."' AND origin='".$this->origin."'";
    	$result=$this->DB->query($sql);
		
		$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$this->artid."', '".time()."', 'stock_item', 'delete', 0, '".$this->id."', 0, 0, '".$this->utilisateur."', 0, 0, '".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
		$this->DB->query($sqllog);
    	return $result;
    }

    //***************************************************************************************
    function origine(){
		
  	  //Modif 08/04/2016 : Affich origin pour le cas rentrée de stock
  	  $origin = explode(":", $this->origin);
  
  	  switch($origin[0]){
	    
  	    case "":
  		    $retour = '?';
          break;
  
  	    case "manu":
      		$retour = "entrée manuelle";
      		break;
  	    
  	    case "cmd":
      		$retour = "commande ";
  		
      		if($this->stock_cmd_id > 0){
      		    $sql = "SELECT *";
      		    $sql .= "  FROM stock_cmd";
      		    $sql .= " WHERE id = ".$this->stock_cmd_id;
              
    			    $cmd = $this->DB->query_first($sql);
    			    
    			    $retour .= "n°".$cmd['id']." du ".date("d/m/Y",$cmd['date']);
      		}
  		    break;
  		
  	    case "retour": // cas rentrée de stock
  		    $retour = "retour cmd ".$origin[1];
  		    break;
	    }

	    return $retour;
    }

    //***************************************************************************************
    function destination(){
    	if($this->dest=="") return "?";
      
    	if(substr($this->dest,0,7)=="facture"){
    	    $this->facturid=intval(substr($this->dest,8));
    	    $f=$this->DB->query_first("SELECT UNIX_TIMESTAMP(f.dateexpedition) AS dateexpedition,artcode,efl.date,efl.expediteur FROM facture f INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) INNER JOIN envoi_fournisseur_ligne efl ON(efl.ligne_facture_id=lf.lignefactureid) WHERE f.facturid=".$this->facturid." AND (lf.artid=".$this->artid.($this->artids!=""?" OR lf.artid IN (".$this->artids.")":"").") AND efl.facture='STOCK'");
    	    $this->date_expedition=$f['dateexpedition'];
    	    $this->date_adv=$f['date'];
    	    $this->utilisateur_adv=$f['expediteur'];
    	    return "Commande ".$this->facturid;
        }
	
    	switch($this->dest){
    	    case 'perdu':
    		$motif = 'Perte';
    		break;
    	    case 'autre':
    		$motif = 'Autre motif';
    		break;
    	    case 'maj':
    		$motif = 'Mise à jour stock';
    		break;
    	    case 'cadeau':
    		$motif = 'Cadeau';
    		break;
    	    default:
    		$motif = '';
    		break;
      }
    	
	    return "";
    }

    //***************************************************************************************
    function cmd_non_reserve($valide=array(1,7),$moyen=null,&$qte=0,$similaire=null){
      	$qte=0;
      	$return=array();
      	if($this->artid==null) return false;
      	
      	if($similaire !== null){
      		$sqlartid="artid IN ('".$similaire."','".$this->artid."')";
      	}
      	else{
      		$sqlartid="(artid=".$this->artid;
      		if($this->artids!="") $sqlartid.=" OR artid IN (".$this->artids.")";
      		$sqlartid.=")";
      	}

      	$sql="SELECT factureid,qte FROM lignefacture INNER JOIN facture ON(facturid=factureid) WHERE (select count(*) from envoi_fournisseur_ligne where ligne_facture_id=lignefactureid AND type IN ('fourn','stock'))=0 and valide in (".implode(",",$valide).") and supprimee='0' and ".$sqlartid;
      	if(!is_null($moyen)) $sql.=" and moyenid IN (".implode(",",$moyen).")";
      	
      	$req=$this->DB->query($sql);
      	while($facture=$this->DB->fetch_array($req)){
      	    $reserve=$this->DB->query_first("SELECT COUNT(*) FROM stock_item WHERE ".$sqlartid." AND dest='facture ".$facture['factureid']."' AND state=2");
      	    if($reserve[0]<$facture['qte']){
          		$qte+=$facture['qte'];
          		$return[]=$facture[0];
      	    }
      	}
        
      	return $return;
    }

    //***************************************************************************************
    function ventes($ts,$similaire=null){
    	if(!is_null($similaire)){
    		$artid = " AND artid =".$similaire;
    	}
    	else{
    		$artid = " AND (artid=".$this->artid;
      	if($this->artids!="") $artid.=" OR artid IN (".$this->artids.")";
      	$artid.=")";
    	}
      	$sql="SELECT sum(qte) FROM `lignefacture` inner join facture on (factureid=facturid) where valide in (1,5,7,8) and heurefacture>".intval($ts).$artid;
        
      	$req=$this->DB->query_first($sql);
      	return intval($req[0]);
    }

    //***************************************************************************************
    function commandes($lieu=null){
      	$return=array();
      	$sqllieu="";
        
      	if(!is_null($lieu)) $sqllieu=" AND si.lieu='$lieu'";
      	$req=$this->DB->query("SELECT count(*),si.date_livraison AS item_livraison,sc.* FROM stock_cmd sc inner join stock_item si on(si.stock_cmd_id=sc.id) WHERE artid=".$this->artid." AND origin='cmd' AND state=0$sqllieu group by sc.id");
        
      	while($cmd=$this->DB->fetch_array($req)){
      	    $sc=new stock_cmd($this->DB,$cmd);
      	    $sc->qte=$cmd[0];
      	    $sc->date_livraison=$cmd['item_livraison'];
      	    $sc->id=$cmd['id'];
      	    $return[]=$sc;
      	}
      	return $return;
    }
	
    //***************************************************************************************
    function commandes_suite_retract($lieu=null){
          
          $return  = array();
          $sqllieu = "";
          
          if(!is_null($lieu)) $sqllieu = " AND si.lieu = '".$lieu."'";
          
          $sql = "SELECT COUNT(*), si.date_livraison AS item_livraison, sc.*";
          $sql .= " FROM stock_item si";
          $sql .= " LEFT JOIN stock_cmd sc ON (sc.id = si.stock_cmd_id)";
          $sql .= " WHERE si.artid = ".$this->artid;
          //$sql .= " AND si.origin = 'cmd'";
          $sql .= " AND si.state = 1";
          $sql .= $sqllieu;
          //$sql .= " GROUP BY sc.id"; 
          $req = $this->DB->query($sql);
          while($cmd = $this->DB->fetch_array($req)){
               
              $sc = new stock_cmd($this->DB,$cmd);
              
              $sc->qte            = $cmd[0];
              $sc->date_livraison = $cmd['item_livraison'];
              
              $return[] = $sc;
          }
          
          return $return;
     }
     
     function transfer($user,$tolieu){
            $sql="INSERT INTO stock_transfer SET date=".time().", user='".mysqli_real_escape_string($this->DB->link_id,$user)."', `from`=".$this->lieu.", `to`=".intval($tolieu).", itemid=".$this->id;
            $return=$this->DB->query($sql);
            $this->lieu=intval($tolieu);
            $this->update();
            return $return;
     }

     function last_transfer($date=null){
        $sql="SELECT date,user,`from`,`to` FROM stock_transfer WHERE itemid=".$this->id;
        if(!is_null($date)) $sql.=" AND date<=".intval($date);
        $sql.=" ORDER BY date DESC";

        $data=$this->DB->query_first($sql);
        return $data;
     }
}
// Fin class stock_item ********************************************************************



// *********************************************************************************
// Class stock_cmd ****************************************************************
// *********************************************************************************
class stock_cmd {
    private $DB;
    
    public $id=0;
    public $destinataire;
    public $utilisateur;
    public $date;
    public $date_livraison;
    public $transporteur="";
    public $tariftrspt=0;
    public $enlevement;
    public $livraison;
    public $facturetrspt;
    public $date_relance;
	  public $pu;
    public $pauht;
    public $items=array();

    public $statetxt=array(-2=>"Annulée",-1=>"Non commandée",0=>"En attente de livraison",1=>"Livrée",10=>"Partiellement livrée");

    //***************************************************************************************
    function __construct($DB,$data=null){
    		$this->DB=$DB;
    
    		if($data!=null){
    			if(!is_array($data)) $data=$this->DB->query_first("SELECT * FROM stock_cmd WHERE id=".intval($data));
    			$this->load($data);
    		}	
    }

    //***************************************************************************************
    function load($data){
    		$this->id				=$data['id'];
    		$this->destinataire		=$data['destinataire'];
    		$this->utilisateur		=$data['utilisateur'];
    		$this->date				=$data['date'];
    		$this->date_livraison	=$data['date_livraison'];
    		$this->transporteur		=$data['trspt'];
    		$this->tariftrspt		=$data['tarif'];
    		$this->enlevement		=$data['enlevement'];
    		$this->livraison		=$data['livraison'];
    		$this->facturetrspt		=$data['facture'];
    		$this->date_relance		=$data['date_relance'];
    }

    //***************************************************************************************
    function items($state=null){
    		$this->items=array();
    		$sql="SELECT * FROM stock_item WHERE stock_cmd_id=".intval($this->id);
    		if(!is_null($state)){
    			if(is_array($state))
    				$sql.=" AND state IN(".implode(",",$state).")";
    			else
    				$sql.=" AND state=".intval($state);
		    }
		
    		$r=$this->DB->query($sql);
    		while($data=$this->DB->fetch_array($r)){
    			$item=new stock_item($this->DB,$data);
    			$this->items[$data['id']]=$item;
                $this->items[$data['id']]->pauht = $item->paht;
    		}
		
		    return $this->items;
    }

    //***************************************************************************************
    function getcmd($state=0,$destinataire=null,$from=null,$to=null,$orderby=null,$sens=null,$listcmd=null){
    		$return=array();
    		$cmdlist=array();
    		if(is_array($state)) $state=implode(",",$state);
        
        if($listcmd != null) { /// dans cette condition, ça permet de trier correctement les commandes de stock par "date de livraison prévue" tâche 6832 dans stock.php 
        	$r=$this->DB->query("SELECT cmd.*,item.date_livraison AS itemdate, item.state, COUNT(*) AS qte, a.artcode, a.artcode_eurocave AS ref, item.id AS itemid, item.trspt 
    			FROM stock_cmd cmd INNER JOIN stock_item item ON (item.stock_cmd_id=cmd.id) INNER JOIN article a ON (a.artid = item.artid)
    			WHERE item.state IN (".$state.")
    			".(!is_null($destinataire)?" AND cmd.destinataire='".mysqli_real_escape_string($this->DB->link_id,$destinataire)."'":"")
    			.( (!is_null($from) && !is_null($to)) ? " AND (cmd.date>=".$from." AND cmd.date<=".$to.")" : ""). " GROUP BY cmd.id, itemdate " .(!is_null($orderby) ? " ORDER BY ".$orderby." ".$sens : "")."");

    			return $r;
        }

        else {
        	$r=$this->DB->query("SELECT cmd.*,item.id AS itemid 
        		FROM stock_cmd cmd INNER JOIN stock_item item ON (item.stock_cmd_id=cmd.id) 
        		WHERE item.state IN (".$state.")
        		".(!is_null($destinataire)?" AND cmd.destinataire='".mysqli_real_escape_string($this->DB->link_id,$destinataire)."'":"")
        		.( (!is_null($from) && !is_null($to)) ? " AND (cmd.date>=".$from." AND cmd.date<=".$to.")" : "").(!is_null($orderby) ? " ORDER BY ".$orderby." ".$sens : "")."");

        	while($cmd=$this->DB->fetch_array($r)){
        		$cmditem[$cmd['id']][]=new stock_item($this->DB,$cmd['itemid']);
        		$cmdlist[$cmd['id']]=new stock_cmd($this->DB,$cmd);
        	}
        	foreach($cmdlist as $id=>$stock_cmd){
        		$stock_cmd->items=$cmditem[$id];
        		$stock_cmd->groupitems();
        		$return[$id]=$stock_cmd;
        	}
        	return $return;
        }

    }

    //***************************************************************************************
    function groupitems(){
    		$items=array();
    		if(!is_array($this->items)) return false;
    		
        foreach($this->items as $id=>$item){
    			if(!is_a($items[$item->artid],"stock_item")){
    				$items[$item->artid]=$item;
    				$items[$item->artid]->facture="";
    				$items[$item->artid]->state=array($item->state=>1);
    			}else{
    				$items[$item->artid]->qte+=$item->qte;
    				$items[$item->artid]->paht+=$item->paht;
    				$items[$item->artid]->prix_trspt+=$item->prix_trspt;
    				$items[$item->artid]->state[$item->state]++;
    			}
    		}
        
    		$this->items=$items;
    }
	
    //***************************************************************************************
  	// 14/04/2016
  	// function groupitems() modifiée afin de faire afficher les articles dans stock_commande, si l'article est similaire mais la date_livraison différente
  	// pour qu'il y est une nouvelle ligne de l'article avec la date_livraison différente (rajout simplement d'un ELSEIF et de $i)
  	function groupitems2(){
    		$items=array();
    		if(!is_array($this->items)) return false;
        
    		$i = 1;
    		foreach($this->items as $id=>$item){
          if($item->state == -2) continue ;
    			if(!is_a($items[$item->artid.":".$item->date_livraison.":".$item->paht],"stock_item")){
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]=$item;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->facture="";
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->state=array($item->state=>1);
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->pu=$item->paht;
    			}elseif($items[$item->artid.":".$item->date_livraison.":".$item->paht]->date_livraison != $item->date_livraison && $items[$item->artid.":".$item->date_livraison.":".$item->paht]->paht != $item->paht){
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]=$item;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->facture="";
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->state=array($item->state=>1);
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->qte=$item->qte;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->paht=$item->paht;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->prix_trspt=$item->prix_trspt;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->pu=$item->paht;
    			}elseif($items[$item->artid.":".$item->date_livraison.":".$item->paht]->date_livraison != $item->date_livraison){
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]=$item;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->facture="";
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->state=array($item->state=>1);
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->qte=$item->qte;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->paht=$item->paht;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->prix_trspt=$item->prix_trspt;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->pu=$item->paht;
    			}else{
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->qte+=$item->qte;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->paht+=$item->paht;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->ecopartht+=$item->ecopartht;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->prix_trspt+=$item->prix_trspt;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->state[$item->state]++;
    				$items[$item->artid.":".$item->date_livraison.":".$item->paht]->pu=$item->paht;
    			}
    		}
    		$this->items=$items;
    }

    //***************************************************************************************
    function create(){
    		$this->destinataire=stripslashes($this->destinataire);
			// modification de benoit pour loguer les stocks
    		if($this->id==0){
				$sql="INSERT INTO stock_cmd
						SET
							destinataire='".mysqli_real_escape_string($this->DB->link_id,$this->destinataire)."',
							utilisateur='".$this->utilisateur."',
							date=".intval($this->date).",
							date_livraison=".intval($this->date_livraison).",
							trspt='".mysqli_real_escape_string($this->DB->link_id,$this->transporteur)."',
							tarif=".floatval($this->tariftrspt).",
							enlevement=".intval($this->enlevement).",
							livraison=".intval($this->livraison).",
							facture='".mysqli_real_escape_string($this->DB->link_id,$this->facturetrspt)."' ";
				$this->DB->query($sql);
				$this->id=$this->DB->insert_id();
				$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, 0, '".$this->date."', 'stock_cmd', 'insert', '".$this->id."',0 ,0, 0, '".$this->utilisateur."', '".$this->livraison."',0 ,'".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
				$this->DB->query($sqllog);
			}
    		foreach($this->items as $cmditem){
    			$cmditem->stock_cmd_id=$this->id;
    			$cmditem->insert();
    		}
    }

    //***************************************************************************************
    function update(){
		$this->destinataire=stripslashes($this->destinataire);
		if($this->id>0){
			$sql="UPDATE stock_cmd
					SET
					destinataire='".mysqli_real_escape_string($this->DB->link_id,$this->destinataire)."',
					utilisateur='".$this->utilisateur."',
					date=".intval($this->date).",
					date_livraison=".intval($this->date_livraison).",
					trspt='".mysqli_real_escape_string($this->DB->link_id,$this->transporteur)."',
					tarif=".floatval($this->tariftrspt).",
					enlevement=".intval($this->enlevement).",
					livraison=".intval($this->livraison).",
					facture='".mysqli_real_escape_string($this->DB->link_id,$this->facturetrspt)."',
					date_relance=".intval($this->date_relance)."
					WHERE id=".$this->id;
			$this->DB->query($sql);
			$sqllog="INSERT INTO stock_log
					(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
					VALUES 
					(NULL, 0, '".$this->date."', 'stock_cmd', 'update', '".$this->id."',0 ,0, 0, '".$this->utilisateur."', '".$this->livraison."',0 ,'".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
			$this->DB->query($sqllog);
		}
    }

    //***************************************************************************************
    function getitems($artid=null,$states=null,$date=null,$typedate="date_livraison"){
    		$return=array();
    		if($this->items==array()) $this->items();
    		if(!is_null($states) && !is_array($states)) $states=array($states);
        
    		foreach($this->items as $item){
    			if(($artid===null || $item->artid==$artid) && (is_null($states) || in_array($item->state,$states)) && ($date===null || $item->$typedate==$date)) $return[$item->id]=$item;
    		}
    		return $return;
    }

    //***************************************************************************************
    function nonfacture($type="fourn"){
    		$return=array();
    		$this->items();
        
    		foreach($this->items as $item)
          switch($type){
    				case "fourn":
    					if($item->facture=="" && $item->state>=0){
    						if(!is_a($return[$item->artid],"stock_item")){
    							$return[$item->artid]=$item;
    						}else{
    							$return[$item->artid]->qte++;
    							$return[$item->artid]->paht+=$item->paht;
    							$return[$item->artid]->ecopartht+=$item->ecopartht;
    						}
    					}
    					break;
    
    				case "trspt":
    					if($item->facture_trspt=="" && $item->trspt<>"" && $item->state>=0){
    						if(!is_a($return[$item->artid],"stock_item")){
    							$return[$item->artid]=$item;
    							$return[$item->artid]->paht=$item->prix_trspt;
    						}else{
    							$return[$item->artid]->qte++;
    							$return[$item->artid]->paht+=$item->prix_trspt;
    						}
    					}
    					break;
    			}
          
    		return $return;
    }

    //***************************************************************************************
    function suivis(){
    		$return=array();
    		$req=$this->DB->query("SELECT * FROM suivi_stock WHERE stock_cmd_id=".$this->id);
    		
        while($data=$this->DB->fetch_array($req))
    			$return[$data['date'].rand(1,9)]=new stock_suivi($this->DB,$data);
    
    		$req=$this->DB->query("SELECT * FROM mail_outbox WHERE stock_cmd_id=".$this->id);
    		while($data=$this->DB->fetch_array($req))
    			$return[$data['date'].rand(1,9)]=new stock_suivi($this->DB,$data);

        $req=$this->DB->query("SELECT * FROM mail_inbox WHERE stock_cmd_id=".$this->id);
        while($data=$this->DB->fetch_array($req))
          $return[$data['date'].rand(1,9)]=new stock_suivi($this->DB,$data);
    
    		krsort($return);
    		return $return;
    }

    //***************************************************************************************
    function state(){
  		$sum=0;
  		$state0=0;
  		$total=$this->DB->query_first("SELECT COUNT(state),SUM(state) FROM stock_item WHERE stock_cmd_id=".$this->id);
      
  		if($total[1]==0) return 0;
  
  		$state1=$this->DB->query_first("SELECT COUNT(state) FROM stock_item WHERE stock_cmd_id=".$this->id." AND state>0");
  		if($state1[0]==$total[0]) return 1;
  
  		$statem1=$this->DB->query_first("SELECT COUNT(state) FROM stock_item WHERE stock_cmd_id=".$this->id." AND state=-1");
  		if($statem1[0]==$total[0]) return -1;
  		
  		$statem2=$this->DB->query_first("SELECT COUNT(state) FROM stock_item WHERE stock_cmd_id=".$this->id." AND state=-2");
  		if($statem2[0]==$total[0]) return -2;
  		
  		return 10;
    }

    //***************************************************************************************
    function factures($type=null){
    		$return=array();
    		$sql="SELECT * FROM stock_item WHERE (facture<>''|| facture_trspt<>'') AND stock_cmd_id=".$this->id;
    
    		$requete=$this->DB->query($sql);
    		while($item=$this->DB->fetch_array($requete)){
    			if(is_null($type) || $type=="fourn"){
    				if(is_a($return['fourn'][$item['facture']][$item['artid']],"stock_item")){
    					$return['fourn'][$item['facture']][$item['artid']]->qte++;
    					$return['fourn'][$item['facture']][$item['artid']]->paht+=$item['paht'];
    				}else{
    					$return['fourn'][$item['facture']][$item['artid']]=new stock_item($this->DB,$item);
    					$return['fourn'][$item['facture']][$item['artid']]->paht=$item['paht'];
    				}
    			}
    	    
    			if((is_null($type) || $type=="trspt") && $item['prix_trspt']>0){
    				if(is_a($return['trspt'][$item['facture_trspt']][$item['artid']],"stock_item")){
    					$return['trspt'][$item['facture_trspt']][$item['artid']]->qte++;
    					$return['trspt'][$item['facture_trspt']][$item['artid']]->prix_trspt+=$item['prix_trspt'];
    				}else{
    					$return['trspt'][$item['facture_trspt']][$item['artid']]=new stock_item($this->DB,$item);
    					$return['trspt'][$item['facture_trspt']][$item['artid']]->prix_trspt=$item['prix_trspt'];
    				}
    			}
    		}
    		
    		$sql="SELECT * FROM avoir_ligne WHERE facturid=".$this->id;
    		$requete=$this->DB->query($sql);
    		while($item=$this->DB->fetch_array($requete)){
    			$si=new stock_item($this->DB);
    			$si->paht=$item['paht'];
    			$si->ref=$si->artcode=$item['article'];
    			$return['avoir'][$item['ffid']][$item['description']]=$si;
    		}
    
    		$sql="SELECT * FROM facture_fournisseur_autre WHERE facturid=".$this->id.(!is_null($type)?" AND `type`='".$type."'":"");
    		$requete=$this->DB->query($sql);
    		while($item=$this->DB->fetch_array($requete)){
    			$si=new stock_item($this->DB);
    			$si->paht=$si->prix_trspt=$item['prix'];
    			$si->ref=$si->artcode=$item['description'];
    			$return[$item['type']][$item['facture']][$item['description']]=$si;
    		}
    
    		return $return;
    }
    
    //***************************************************************************************
    function livraison_date($from){
    		$return=array();
    		$sql="SELECT * FROM stock_item WHERE date>".intval($from)." AND state>0 AND stock_cmd_id=".$this->id;
    		$req=$this->DB->query($sql);
    		while($data=$this->DB->fetch_array($req)){
    			if(isset($return[$data['artid']])){
    				$return[$data['artid']]->qte++;
    			}else{
    				$return[$data['artid']]=new stock_item($this->DB,$data);
    				$return[$data['artid']]->qte=1;
    			}
    		}
    		return $return;
    }
}
// Fin class stock_cmd ********************************************************************


// *********************************************************************************
// Class stock_suivi ****************************************************************
// *********************************************************************************
class stock_suivi{
    public $id;
    public $stock_cmd_id;
    public $moyen;
    public $date;
    public $commercial;
    public $suivi;
    public $rappel;
    public $pour;
    public $hide;
    public $to;
 
    private $DB;

    //***************************************************************************************
    function __construct($DB,$data=null){
      	$this->DB=$DB;
      	if(isset($data['to'])) $this->loadmail($data);
      	if(isset($data['destinataire'])) $this->load($data);
    }

    //***************************************************************************************
    function load($data){
      	$this->id=$data['id'];
      	$this->stock_cmd_id=$data['stock_cmd_id'];
      	$this->date=$data['date'];
      	$this->moyen=$data['moyen'];
      	$this->to=$data['destinataire'];
      	$this->commercial=$data['commercial'];
      	$this->suivi=$data['suivi'];
      	$this->rappel=$data['rappel'];
      	$this->pour=$data['pour'];
      	$this->hide=$data['hide'];
    }

    //***************************************************************************************
    function loadmail($data){
    		$this->id=$data['id'];
      	$this->stock_cmd_id=$data['suiviid'];
      	$this->date=$data['date'];
      	$this->commercial=$data['utilisateur'];
      	$this->suivi="<p>".$data['subject']."</p>".$data['body'];
      	$this->moyen="mail_out";
      	$this->to=array();
      	if($data['to']!=""){
      	  $mails=preg_split("/[;,]/",$data['to']);
      	  $mails=array_unique(array_filter($mails));
      	  $r=$this->DB->query("SELECT id FROM fournisseur WHERE mail IN ('".implode("','",$mails)."')");
      	  while($to=$this->DB->fetch_array($r))
      	    $this->to[]=$to['id'];
      	}
    }

    //***************************************************************************************
    function create(){
		$sql="INSERT INTO suivi_stock
				SET
					stock_cmd_id=".intval($this->stock_cmd_id).",
					date=".time().",
					commercial='".mysqli_real_escape_string($this->DB->link_id,$this->commercial)."',
					destinataire='".$this->to."',
					moyen='".$this->moyen."',
					suivi='".mysqli_real_escape_string($this->DB->link_id,$this->suivi)."',
					rappel=".intval($this->rappel).",
					pour='".mysqli_real_escape_string($this->DB->link_id,$this->pour)."',
					hide=".intval($this->hide);
	    $return=$this->DB->query($sql);
		
		$this->id=$this->DB->insert_id();
		
		$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, 0, '".$this->date."', 'suivi_stock', 'insert', 0 ,0, 0,'".$this->id."', '".$this->utilisateur."', '".$this->to."',0 ,'".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
		$this->DB->query($sqllog);
		
		return $return;
    }
}
// Fin class stock_suivi ********************************************************************


// *********************************************************************************
// Class stock_ajust ****************************************************************
// *********************************************************************************
class stock_ajust{
    private $DB;

    public $id;
    public $date;
    public $utilisateur;
    public $artid;
    public $lieu;
    public $qte;
    public $qte_compte;
    public $commentaire;

    //***************************************************************************************
    function __construct($DB,$data=null){
      	$this->DB=$DB;
      	if(!is_null($data)) $this->load($data);
    }
    
    //***************************************************************************************  
    function load($data){
      	$this->id=$data['id'];
      	$this->date=$data['date'];
      	$this->utilisateur=$data['utilisateur'];
      	$this->artid=$data['artid'];
      	$this->lieu=$data['lieu'];
      	$this->qte=$data['qte'];
      	$this->qte_compte=$data['qte_compte'];
      	$this->commentaire=$data['commentaire'];
    }

    //***************************************************************************************
    function create(){
      	$this->commentaire=stripslashes($this->commentaire);
		$sql="INSERT INTO stock_ajust
				SET
					date=".$this->date.",
					utilisateur='".$this->utilisateur."',
					artid=".intval($this->artid).",
					lieu=".intval($this->lieu).",
					qte=".intval($this->qte).",
					qte_compte=".intval($this->qte_compte).",
					commentaire='".mysqli_real_escape_string($this->DB->link_id,$this->commentaire)."' ";
      	$result=$this->DB->query($sql);
		$this->id=$this->DB->insert_id();
		
		$sqllog="INSERT INTO stock_log
						(stock_logid, artid, dateline, stock_table, stock_table_operation, stock_cmd_id, stock_item_id, stock_ajust_id, suivi_stock_id, username, lieu_stockage_id, mouvement, log_sql)
						VALUES 
						(NULL, '".$this->artid."', '".$this->date."', 'stock_ajust', 'insert', 0 ,0, '".$this->id."', 0, '".$this->utilisateur."', '".$this->lieu."','".$this->qte."' ,'".mysqli_real_escape_string($this->DB->link_id,$sql)."' )";
		$this->DB->query($sqllog);
      	return $result;
    }
    
    function searchlast($artid,$date=null){
        $return=array();
    
        if(is_null($date)) $date=time();
    
        $sql="SELECT sa.lieu,sa2.datemax date,sa.artid,sa.qte FROM stock_ajust sa 
        INNER JOIN (
            SELECT MAX(date) datemax,lieu,artid FROM stock_ajust WHERE date<".intval($date)." AND artid = ".intval($artid)." GROUP BY lieu
        ) sa2 ON(sa.lieu=sa2.lieu AND sa.date=sa2.datemax AND sa.artid=sa2.artid)
        WHERE 1 GROUP BY lieu";
        
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $return[$data['lieu']]=new stock_ajust($this->DB,$data);
        }
        return $return;
    }
    
    function searchfirst($artid,$date=null){
        $return=array();
    
        if(is_null($date)) $date=0;
    
        $sql="SELECT lieu,date,artid,qte,utilisateur FROM stock_ajust
        WHERE date>".intval($date)." AND artid = ".intval($artid)."
        GROUP BY lieu";
        
        $req=$this->DB->query($sql);
        while($data=$this->DB->fetch_array($req)){
            $return[$data['lieu']]=new stock_ajust($this->DB,$data);
        }
        return $return;
    }

    //***************************************************************************************
    function item(){
    	$data=$this->DB->query_first("SELECT * FROM stock_item LEFT JOIN stock_mini USING (artid) WHERE stock_item.artid=".$this->artid." AND date<".$this->date." ORDER BY date DESC");
    	$item=new stock_item($this->DB,$data);
    	return $item;
    }
}
// Fin class stock_ajust ********************************************************************

// *********************************************************************************
// Class suivi_transfert ****************************************************************
// *********************************************************************************
class suivi_transfert{
	private $DB;

	public $date;
	public $utilisateur;
	public $artid;
	public $artref;
	public $suivi;
	public $quantite;
	public $commentaire;
	
	//***************************************************************************************
	function __construct($DB,$data=null){
		$this->DB=$DB;
		if(!is_null($data)) $this->load($data);
	}

	//***************************************************************************************
	function load($data){
		$this->id=$data['id'];
		$this->date=$data['date'];
		$this->utilisateur=$data['commercial'];
		$this->artid=$data['artid'];
		
		$this->commentaire=$data['suivi'];
		
		$place = strpos($this->commentaire, "x");
		$this->quantite = substr($this->commentaire,0,$place);
		
		$res=$this->DB->query_first("SELECT artcode FROM article WHERE artid=".$this->artid."");
		$this->artref=$res['artcode'];
	}

}
// Fin class suivi_transfert ********************************************************************

?>
