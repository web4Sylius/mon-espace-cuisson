<?php

class ral {

private $DB;
public $langue="FR";
public $caractid;
public $caractvalid;
public $cols=10;
public $artid;
public $select_function="ral_select";
//private $ral_caractid=array(33,34,102,101);

function __construct($DB, $artid=0){
    $this->DB=$DB;
    $this->artid = $artid;
}

function generate($caractid = '',$caracval=null){
    if($caracval!==null){
        $sql = "SELECT marqueid";
        $sql .= " FROM article_marque";
        $sql .= " WHERE artid = '".$this->artid."'";
        $article_marque = $this->DB->query_first($sql)[0];
        $sql=$this->DB->query_first("SELECT c.caractid FROM `caracteristique` c INNER JOIN caracteristiquevaleur cv ON cv.libelleFR = c.libelleFR WHERE `marqueid` = ".$article_marque." AND cv.caractvalid = ".$caracval);
        if(!is_null($sql)){
            $req=$this->DB->query("SELECT libelle".$this->langue." AS libelle, caractvalid,ral.hex,ral.ral FROM caracteristiquevaleur cv INNER JOIN caractval_ral USING(caractvalid) INNER JOIN ral USING(ral) WHERE cv.caractid=".$sql['caractid']." ORDER BY ral");
             if($this->DB->num_rows($req) == 0){
                $subcaractid = $this->DB->query_first("SELECT subcaractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$caracval."' AND caractid=".$sql['caractid']);
                $req=$this->DB->query("SELECT libelle".$this->langue." AS libelle, caractvalid,ral.hex,ral.ral FROM caracteristiquevaleur cv INNER JOIN caractval_ral USING(caractvalid) INNER JOIN ral USING(ral) WHERE cv.caractid=".$subcaractid['subcaractid']." ORDER BY ral");
            }
        }
        else{
            $req=$this->DB->query("SELECT libelle".$this->langue." AS libelle, caractvalid,ral.hex,ral.ral FROM caracteristiquevaleur cv INNER JOIN caractval_ral USING(caractvalid) INNER JOIN ral USING(ral) WHERE cv.caractid=".$this->caractid." ORDER BY ral");
            if($this->DB->num_rows($req) == 0){
                $subcaractid = $this->DB->query_first("SELECT subcaractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$caracval."' AND caractid=".$this->caractid);
                $req=$this->DB->query("SELECT libelle".$this->langue." AS libelle, caractvalid,ral.hex,ral.ral FROM caracteristiquevaleur cv INNER JOIN caractval_ral USING(caractvalid) INNER JOIN ral USING(ral) WHERE cv.caractid=".$subcaractid['subcaractid']." ORDER BY ral");
            }
        }
    }
    else{
        $req=$this->DB->query("SELECT libelle".$this->langue." AS libelle, caractvalid,ral.hex,ral.ral FROM caracteristiquevaleur cv INNER JOIN caractval_ral USING(caractvalid) INNER JOIN ral USING(ral) WHERE cv.caractid=".$this->caractid." ORDER BY ral");
    }

    $c=0;
    $html="<table><tr>";
    while($ral=$this->DB->fetch_array($req)){
	$html.="<td style=\"background:".$ral['hex'].";color:".$this->text_color($ral['hex']).";\" onclick=\"".$this->select_function."(".($caractid != '' ? $caractid : $this->caractid).",".$this->caractvalid.",".$ral['caractvalid'].",'".$ral['hex']."',".$this->artid.")\" title=\"".$ral['libelle']."\">".$ral['ral']."</td>";
	$c++;
	if($c==$this->cols){
	    $c=0;
	    $html.="</tr><tr>";
	}
    }
    $html.="</tr></table>";

    return $html;
}

function text_color($hex){
    $hex=substr($hex,1);
    $hexvalues=str_split($hex,2);
    $decvalue=0;
    for($i=0;$i<=2;$i++) $decvalue+=hexdec($hexvalues[$i]);

    if($decvalue<180) return "white";
    return "black";
}

function is_ral_option($caractvalid){
//     $result=array_key_exists($caractvalid,$this->ral_caractvalid);
    $ral_colors = $this->DB->query("SELECT c.caractid FROM caracteristique c INNER JOIN caracteristiquevaleur cv ON cv.subcaractid = c.caractid");
    $ral=array();
    while($ral_color = mysqli_fetch_array($ral_colors)) {
        $ral[] = $ral_color['caractid'];
    }
    $ral = implode("','",$ral);

    $result="";
    if($ral != ""){
        $result=$this->DB->query_first("SELECT * FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid." AND subcaractid IN ('".$ral."')");
        if($result!=""){
          $this->caractid=$result['subcaractid'];
          $this->caractvalid=$caractvalid;
        }
    }
    return $result!="";
}

function is_ral_carac($caractid){
    $ral_colors = $this->DB->query("SELECT c.caractid FROM caracteristique c INNER JOIN caracteristiquevaleur cv ON cv.subcaractid = c.caractid");
    $ral=array();
    while($ral_color = mysqli_fetch_array($ral_colors)) {
        $ral[] = $ral_color['caractid'];
    }

    if(!in_array($caractid, $ral)){
        return false;
    }
    return true;
}

function update_ral_price($artid,$prix,$prixht,$paht){
    $sql=array();
    $req=$this->DB->query("SELECT * FROM caracteristiquevaleur WHERE caractid=".$this->caractid);
    while($ral_caract=$this->DB->fetch_array($req)) $sql[]="(".intval($artid).",".intval($ral_caract['caractvalid']).",'".serialize($prix)."','".serialize($prixht)."',".floatval($paht).")";

    if(count($sql)>0) $this->DB->query("INSERT INTO article_caractval (artid,caractvalid,differenceprix,differenceprixht,achat) VALUES ".implode(",",$sql));
}

}