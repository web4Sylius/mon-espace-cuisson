<?php

class adv {

private $DB;
public $id;
public $facturid;
public $lignes=array();
public $date_creation=0;
public $utilisateur="";
public $type;
public $destinataire;
public $devise;
public $envoi;
public $envoi_heure;
public $efid;
public $fields=array();
public $steps=array();
public $maxstep=0;
public $files=array();
public $locked=false;
public $modif=false;



function __construct($DB,$advid=null){
    $this->DB=$DB;
    if($advid!=null) $this->load($advid);
}

function load($advid){
    $adv=$this->DB->query_first("SELECT * FROM adv WHERE id=".intval($advid));
    if($adv['id']=="") return false;
    $this->loaddata($adv);
    return true;
}

function load_from_efid($efid){
    $adv=$this->DB->query_first("SELECT * FROM adv WHERE efid=".intval($efid));
    if($adv['id']=="") return false;
    $this->loaddata($adv);
    return true;
}

function load_from_lignefactureid($lignefactureid,$type){
    $adv=$this->DB->query_first("SELECT * FROM adv WHERE lignes LIKE '%-".$lignefactureid."%' AND type='".$type."'");
    if($adv['id']=="") return false;
    $this->loaddata($adv);
    return true;
}

function loaddata($adv){
    $this->id=$adv['id'];
    $this->facturid=$adv['facturid'];
    $this->date_creation=$adv['date'];
    $this->utilisateur=$adv['utilisateur'];
    $this->type=$adv['type'];
    $this->destinataire=$adv['destinataire'];
    $this->devise=$adv['devise'];
    $this->envoi=$adv['envoi'];
    if($this->envoi>0){
	$advh=$this->DB->query_first("SELECT * FROM adv_heure WHERE fournisseur='".$this->destinataire."'");
	$this->envoi_heure=intval($advh['heure']);
    }
    $this->efid=$adv['efid'];
    $lignes=explode(",",$adv['lignes']);
    foreach($lignes as $l){
      $m=preg_match("/(\d+)-(\d+)/",$l,$matches);
      if($m==0){ $matches[1]=1; $matches[2]=$l; }
      if($matches[2]!="") $this->lignes[$matches[2]]=$matches[1];
    }
    
    $r=$this->DB->query("SELECT * FROM adv_steps WHERE advid=".$this->id);
    while($step=$this->DB->fetch_array($r)){
	$s=array("utilisateur"=>$step['utilisateur'],"date"=>$step['date']);
	$this->steps[$step['step']]=$s;
	if($step['step']>$this->maxstep) $this->maxstep=$step['step'];
    }

    $this->loadfields();
}

function loadfields(){
    $r=$this->DB->query("SELECT * FROM adv_fields WHERE advid=".$this->id);
    while($f=$this->DB->fetch_array($r)){
      $this->fields[$f['name']]=$f['value'];
      $this->utfields[$f['name']]=$f['utilisateur'];
      $this->datefields[$f['name']]=$f['date'];
    }
    
    foreach($this->fields as $name=>$value){
	if(preg_match("/(\w+)\[(.+)\]/",$name,$m)>0) $this->fields[$m[1]][$m[2]]=$value;
    }
}

//Fonction qui crée un input radio ainsi qu'un input hidden pour stocker la valeur du bouton radio
//Utiliser 1 fois par étape pour créer l'input hidden, ensuite c'est la fonction radio() ci-dessous qui est utilisée (éviter de créer plusieurs input hidden pour la même étape)
function radio_inputs($name,$values,$action="",$options="")
{
    $return = "<input type=\"hidden\" name=\"$name\" value=\"".$this->fields[$name]."\">";
    
    $optionstr = "";
    if($options!="") foreach($options as $attr=>$value) $optionstr.=" ".$attr."=\"".$value."\"";
    $action = str_replace("%name","'".$name."'",$action);
    
    foreach($values as $lbl => $value){
        $uniq = uniqid();
        $return .= " "
        . "<label for=\"opt" . $name . $uniq . "\"" . $optionstr . " style=\"cursor: pointer;\">"
        . "<input type=\"radio\" name=\"opt" . $name . "\" id=\"opt" . $name . $uniq . "\" value=\"" . $value . "\" onclick=\"document.getElementsByName('" . $name . "')[0].value = this.value; " . $action . ";\" " . (isset($this->fields[$name]) && $this->fields[$name] == $value ? "checked" : "") . ($this->locked ? " disabled" : "") . " style=\"cursor:pointer;\">" . $lbl . "</label>";
    }

    return $return;
}

function radio($name,$value,$action="",$options=""){
    $data=each($value);
    $optionstr="";
    if($options!="") foreach($options as $attr=>$v) $optionstr.=" ".$attr."=\"".$v."\"";
    $uniq=uniqid();
    $return="<label for=\"opt$name$uniq\"".$optionstr." style=\"cursor: pointer;\"><input type=\"radio\" name=\"opt$name\" id=\"opt$name$uniq\" value=\"$data[value]\" onclick=\"document.getElementsByName('$name')[0].value=this.value;$action;\" ".($this->fields[$name]==$data[value]?"checked":"").($this->locked?" disabled":"")." style=\"cursor:pointer;\">".$data['key']."</label>";

    return $return;
}

function checkbox($name,$action="",$value,$default=null){
    if($default!=null && !isset($this->fields[$name])) $this->save_field($name,$value);
    $action=str_replace("%name","'".$name."'",$action);
    return "<input type=\"hidden\" name=\"$name\" value=\"".$this->fields[$name]."\"><input type=\"checkbox\" id=\"".$name."\" onclick=\"document.getElementsByName('$name')[0].value=(this.checked?'".$value."':'');$action;\" ".($this->fields[$name]==$value?"checked":"").($this->locked?" disabled":"").">";
}

function ouinon($name){
    return ($this->fields[$name]==1?"oui":"non");
}

function cal_input($name,$action="undefined",$default=null){
    if($default==0) $default=null;
    if($default!=null && !isset($this->fields[$name])) $this->save_field($name,$default);
    return "<input type=\"text\" name=\"$name\" id=\"".str_replace("-","",$name)."\" value=\"".$this->fields[$name]."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,$action);\" readonly".($this->locked?" disabled":"").">";
}

function text_input($name,$action,$options="",$default=null){
    if($default!=null && !isset($this->fields[$name])) $this->save_field($name,$default);
    $optionstr="";
    if($options!="") foreach($options as $attr=>$value) $optionstr.=" ".$attr."=\"".$value."\"";
    return "<input type=\"text\" name=\"".$name."\" value=\"".$this->fields[$name]."\" onblur=\"$action\"$optionstr".($this->locked?" disabled":"").">";
}

function nb_input($name,$action,$options="",$default=null){
    if($default!=null && !isset($this->fields[$name])) $this->save_field($name,$default);
    if($options!="") foreach($options as $attr=>$value) $optionstr.=" ".$attr."=\"".$value."\"";
    return "<input type=\"text\" name=\"".$name."\" value=\"".floatval($this->fields[$name])."\" onkeypress=\"nbonly(event,true,this);\" onblur=\"$action\" onclick=\"this.select()\" size=\"5\"$optionstr".($this->locked?" disabled":"").">";
}

function textarea($name,$action,$options="",$default=null){
    if($default!=null && !isset($this->fields[$name])) $this->save_field($name,$default);
    $optionstr="";
    if($options!="") foreach($options as $attr=>$value) $optionstr.=" ".$attr."=\"".$value."\"";
    $action=str_replace("%name","'".$name."'",$action);
    return "<textarea name=\"$name\" onblur=\"$action\"$optionstr".($this->locked && !isset($options['allow'])?" disabled":"").">".$this->fields[$name]."</textarea>";
}

function select($name,$values,$action){
    $return="<select name=\"$name\" onchange=\"$action\"".($this->locked?" disabled":"").">";
    if(!is_null($values)) foreach($values as $value=>$txt) $return.="<option value=\"$value\"".($this->fields[$name]==$value?" selected":"").">$txt</option>";
    $return.="</select>";

    return $return;
}

function sendfile($name,$action=""){
    return "<iframe src=\"adv/adv_ul.php?advid=".$this->id."&field=".$name."&action=$action\" style=\"width:250px;height:40px;border:none;\" scrolling=\"no\"></iframe>";
}

function modiffield($name){
    return $this->utfields[$name]." ".($this->datefields[$name]>0?date("d/m/Y",$this->datefields[$name]):"");
}

function wait($name){
    return "<span id=\"".$name."-ok\"></span>";
}

function checkbox_files(){
    $return="";
    foreach($this->files as $fid=>$fdata){
	$return.="<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$fid\"><input type=\"hidden\" id=\"filename$fid\" value=\"".$fdata['name']."\"><input type=\"hidden\" id=\"fileurl$fid\" value=\"".$fdata['path']."\"> <a href=\"".$fdata['path']."\" target=\"_blank\">".$fdata['name']."</a>";
    }
    return $return;
}

function filebox(){
  return "<div id=\"filebox\">
  <div id=\"fileboxtitle\">Fichiers envoyés<img src=\"images/icon-x.svg\" onclick=\"showfilebox(false);\"></div>
  <div id=\"fileboxlist\"></div>
  ".$this->sendfile("loadfilebox()")."
</div>";
}

function filebox_button(){
//     return "<span class=\"fileboxbutton\" onclick=\"showfilebox(true);\"><img src=\"images/icon/jpg.png\"> Voir les fichiers joints</span>";
      return "<span class=\"fileboxbutton\" onclick=\"fb.show();\"><img src=\"images/icon/jpg.png\"> Voir les fichiers joints</span>";
}

function coche($name,$action=""){
    return "<input type=\"hidden\" name=\"".$name."\">
    <img src=\"images/icon-x.svg\" width=\"15\" onclick=\"document.getElementsByName('".$name."')[0].value=0;".$action."\" style=\"cursor:pointer;\">&nbsp;<img src=\"images/coche_verte.svg\" width=\"15\" onclick=\"document.getElementsByName('".$name."')[0].value=1;".$action."\" style=\"cursor:pointer;\">";
}

function verif($name,$type="text",$action=""){
    switch($type){
	case "text":
	$input="<input type=\"text\" name=\"".$name."_verif\" value=\"".$this->fields[$name."_verif"]."\" size=\"20\" onkeyup=\"sav_autosave('".$name."_verif')\"> <span id=\"".$name."_verif-ok\"></span></span>";
	break;
	case "ouinon":
	$input=$this->radio_inputs($name."_verif",array("Oui"=>1,"Non"=>0),"sav_save('".$name."_verif')")." <span id=\"".$name."_verif-ok\"></span>";
	break;
	case "cal":
	$input=$this->cal_input($name."_verif","sav_calsave")." <span id=\"".$name."_verif-ok\"></span>";
	break;
    }
    if(is_null($this->fields[$name."_verif"]))
	$return="<img src=\"images/icon-x.svg\" width=\"16\" onclick=\"showverif('".$name."','');$action;\"> <img src=\"images/coche_verte.svg\" width=\"16\" onclick=\"showverif('".$name."','".$this->fields[$name]."');sav_save('".$name."_verif');$action;\"> <span id=\"".$name."_span\" style=\"display:none;\">$input";
    else
	$return=$input." <small>".$this->utfields[$name."_verif"]." ".($this->datefields[$name."_verif"]>0?date("d/m/Y",$this->datefields[$name."_verif"]):"")."</small>";

    return $return;
}

function update_step($step){
    global $user_info;

    $rec=$this->DB->query_first("SELECT id FROM adv_steps WHERE advid=".$this->id." AND step=".$step);
    if($rec['id']=="")
	$result=$this->DB->query("INSERT INTO adv_steps SET advid=".$this->id.", step=".$step.", utilisateur='".$user_info['username']."', date=".time());
    else
	$result=$this->DB->query("UPDATE adv_steps SET utilisateur='".$user_info['username']."', date=".time()." WHERE id=".$rec['id']);
    if($step>$this->maxstep) $this->maxstep=$step;
    $this->steps[$step]['utilisateur']=$user_info['username'];
    $this->steps[$step]['date']=time();
    
    return $result;
}

function save_field($name,$value){
    global $user_info;
    $value=stripslashes($value);
    $field=$this->DB->query_first("SELECT * FROM adv_fields WHERE advid='".$this->id."' AND name='".$name."'");
    if($field['id']=="")
	$this->DB->query("INSERT INTO adv_fields SET advid='".$this->id."', name='".$name."', value='".mysqli_real_escape_string($this->DB->link_id,$value)."', utilisateur='".$user_info['username']."', date=".time());
    else
	$this->DB->query("UPDATE adv_fields SET value='".mysqli_real_escape_string($this->DB->link_id,$value)."', utilisateur='".$user_info['username']."', date=".time()." WHERE id=".$field['id']);
    
    $this->fields[$name]=$value;
}

function del_field($name){
    return $this->DB->query("DELETE FROM adv_fields WHERE advid='".$this->id."' AND name like '".$name."'");
}

function facture_field($name){
    $result=$this->DB->query_first("SELECT facture FROM facture_fournisseur_autre WHERE assoc='".$name.$this->id."'");
    return $result[0];
}

//Fonction qui sert à nettoyer les anomalies de saisie de l'ADV automatiquement
function clean()
{
    //Récupération des produits déjà commandés pour le type de commande en cours
    $sql = "SELECT a.id, a.lignes, a.efid";
    $sql .= " FROM adv a";
    $sql .= " WHERE a.facturid = " . $this->facturid;
    $sql .= " AND a.type = '" . $this->type . "'";
    $sql .= " AND a.efid > 0";
    $req = $this->DB->query($sql);

    $aLignes = array();
    while($row = $this->DB->fetch_assoc($req)) $aLignes[] = $row;
    
    //Pour chacun des produits, on vérifie s'il est bien associé à une commande déjà envoyé au fournisseur
    //Si ce n'est pas le cas, on nettoie pour que l'ADV puisse commander le produit en question
    foreach($aLignes as $la_ligne)
    {
        $sql = "SELECT efl.ligne_facture_id";
        $sql .= " FROM envoi_fournisseur ef";
        $sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
        $sql .= " WHERE ef.id = " . $la_ligne['efid'];
        $req = $this->DB->query($sql);

        $aLignefacture = array();
        while($row = $this->DB->fetch_assoc($req)) $aLignefacture[] = $row['ligne_facture_id'];

        if(!empty($aLignefacture))
        {
            $aTemp1 = explode(',',$la_ligne['lignes']);

            $aLignesADV = array();
            for($i=0;$i<count($aTemp1);$i++)
            {
                $aTemp2 = explode('-',$aTemp1[$i]);
                $aLignesADV[$i]['qte'] = $aTemp2[0];
                $aLignesADV[$i]['val'] = $aTemp2[1];
            } 

            $chaine = '';
            foreach($aLignesADV as $la_ligne_adv)
            {
                if(in_array($la_ligne_adv['val'], $aLignefacture))
                {
                    if($chaine != '') $chaine .= ',';
                    $chaine .= $la_ligne_adv['qte'] . '-' . $la_ligne_adv['val']; 
                }
            }

            $sql = "UPDATE adv SET";
            $sql .= " lignes = '" . $chaine . "'";
            $sql .= " WHERE id = " . $la_ligne['id'];
            $this->DB->query($sql);
        }
    }
}

function create(){
    global $user_info;

    $this->clean();
    
    $this->DB->query("INSERT INTO adv SET facturid='".$this->facturid."', date=".time().", utilisateur='".$user_info['username']."', type='".$this->type."', destinataire='".$this->destinataire."', devise='".$this->devise."'");
    $this->id=$this->DB->insert_id();
    $this->load($this->id);
   return $this->id;
}

function add_lignefacture($qte,$lignefactureid){
    $qte=intval($qte);
    $lignefactureid=intval($lignefactureid);
    if($qte==0 || $lignefactureid==0) return false;
    
    $this->lignes[$lignefactureid]=$qte;
    $str=array();
    foreach($this->lignes as $lfid=>$lqte)
	$str[]=$lqte."-".$lfid;

    return $this->DB->query("UPDATE adv SET lignes='".implode(",",$str)."' WHERE id=".$this->id);
}

function update(){
    $result=$this->DB->query("UPDATE adv SET destinataire='".mysqli_real_escape_string($this->DB->link_id,$this->destinataire)."', devise='".$this->devise."', envoi=".intval($this->envoi).", efid=".intval($this->efid)." WHERE id=".$this->id);
    return $result;
}

function delete($fields=true,$steps=true,$adv=true){
    if($this->id=="" || $this->id==null) return false;
    if($adv) $this->DB->query("DELETE FROM adv WHERE id=".$this->id);
    if($steps) $this->DB->query("DELETE FROM adv_steps WHERE advid=".$this->id);
    if($fields) $this->DB->query("DELETE FROM adv_fields WHERE advid=".$this->id);
}

function cancel_efid($efid){
    $this->DB->query("UPDATE adv SET efid=0 WHERE efid=".intval($efid));
}

function listadv($facturid,$type=null,$ligneid=null){
    $return=array();
    $sql="SELECT id FROM adv WHERE facturid='".$facturid."'";
    if($ligneid!=null) $sql.=" AND lignes LIKE '%".$ligneid."%'";
    if($type!=null) $sql.=" AND type='".$type."'";
    $req=$this->DB->query($sql);
    while($adv=$this->DB->fetch_array($req)){
      $return[]=new adv($this->DB,$adv['id']);
	}
    return $return;
}

function envoiauto($h=null,$d=null){
    if($h==null) $h=date("H");
    if($d==null) $d=time();
    $date=mktime(0,0,0,date("m",$d),date("d",$d),date("Y",$d));
    $return=array();
    $sql="SELECT id FROM adv INNER JOIN adv_heure ON (destinataire=fournisseur) WHERE (envoi>=$date AND envoi<".($date+86400).") AND heure=".$h;
    $req=$this->DB->query($sql);
    while($adv=$this->DB->fetch_array($req))
      $return[]=new adv($this->DB,$adv['id']);

    if($h==0){
      $sql="SELECT id FROM adv WHERE (envoi>=$date AND envoi<".($date+86400).") AND (SELECT COUNT(*) FROM adv_heure WHERE fournisseur=adv.destinataire)=0";
      $req=$this->DB->query($sql);
      while($adv=$this->DB->fetch_array($req))
	 $return[]=new adv($this->DB,$adv['id']);
    }

    return $return;
}

function toutcmd($facturid,$type){
    $lignesnb=0;
    $sql="SELECT lignes FROM adv WHERE facturid=$facturid AND type='".$type."'";
    $req=$this->DB->query($sql);
    while($adv=$this->DB->fetch_array($req)){
	$lignes=explode(",",$adv['lignes']);
	$lignesnb+=count($lignes);
    }
    $lf=$this->DB->query_first("SELECT COUNT(*) FROM lignefacture WHERE factureid=".$facturid);
    
    return ($lf[0]==$lignesnb);
}

function date2ts($date){
    if($date==0) return 0;
    $d=preg_split("/[\/h:]/",$date);
    if($d==false) return 0;
    if(!isset($d[3])) $d[3]=$d[4]=$d[5]=0;
    return mktime($d[3],$d[4],$d[5],intval($d[1]),intval($d[0]),intval($d[2]));
}

function ligne_facture($lignefactureid){
    $ret=new lignefacture($this->DB,$lignefactureid);
    return $ret;
}

function colis(){
    $return=array();
    foreach($this->fields as $key=>$value){
	if(preg_match("/colis_([0-9]+)/",$key,$m) && $value!=""){
	    $lines=explode(",",$value);
	    foreach($lines as $line){
		list($qte,$lid)=explode("-",$line);
		$return[$m[1]][$lid]=$qte;
	    }
	}
    }
    ksort($return);
    return $return;
}

function list_ar(){
    $return=array();
    if(is_null($this->id)) return $return;
    $sql="SELECT id FROM adv_ar WHERE advid=".$this->id;
    $req=$this->DB->query($sql);
    while($arid=$this->DB->fetch_array($req)){
	$ar=new adv_ar($this->DB,$arid[0]);
	$return[$arid[0]]=$ar;
    }
    ksort($return);
    return $return;
}

function no_ar(){
    $return=array();
    $sql="SELECT a.* 
    FROM `adv` a INNER JOIN `facture` f USING(facturid)
    WHERE destinataire IN (SELECT societe FROM fournisseur_condition where ar=1) 
    AND (SELECT enlevement FROM envoi_fournisseur WHERE id=a.efid)=0
    AND (SELECT COUNT(*) FROM adv_ar WHERE advid=a.id AND valide=1)=0
    AND f.valide IN (1,7)";
      
    $req=$this->DB->query($sql);
    while($data=$this->DB->fetch_array($req)){
	$adv=new adv($this->DB);
	$adv->loaddata($data);
	$adv->origin="facture";
	$return[]=$adv;
    }
    
    $sql="SELECT adv.*
    FROM `adv`
    INNER JOIN stock_item ON stock_cmd_id = facturid
    WHERE state =0
    AND destinataire IN (SELECT societe FROM fournisseur_condition where ar=1) 
    AND (SELECT COUNT(*) FROM adv_ar WHERE advid=adv.id AND valide=1)=0
    GROUP BY facturid";
    
    $req=$this->DB->query($sql);
    while($data=$this->DB->fetch_array($req)){
	$adv=new adv($this->DB);
	$adv->loaddata($data);
	$adv->origin="stock";
	$return[]=$adv;
    }
    
    return $return;
}

    function coutARetTransport($efl) {
      if($efl->type == 'fourn' && isset($this->fields['mazetcout'])) return $this->fields['mazetcout'] + $efl->paht;
      else return $efl->paht;
    }

}


class adv_ar{
    private $DB;
    private $id;
    private $advid;
    private $date;
    private $date_ar;
    private $utilisateur;
    private $numero;
    private $valide;
    private $fields;
    
    function __construct($DB,$id=null){
	$this->DB=$DB;
	if(!is_null($id)) $this->load($id);
    }
    
    function load($id){
	$data=$this->DB->query_first("SELECT * FROM adv_ar WHERE id=".intval($id));
	$this->id=$data['id'];
	$this->prefix="ar".$this->id."_";
	$this->advid=$data['advid'];
	$this->date=$data['date'];
	$this->date_ar=$data['date_ar'];
	$this->utilisateur=$data['utilisateur'];
	$this->numero=$data['numero'];
	$this->valide=$data['valide'];
	$this->load_fields();
    }
    
    function load_fields(){
	$sql="SELECT * FROM adv_fields WHERE advid=".$this->advid." AND name LIKE '".$this->prefix."%'";
	$req=$this->DB->query($sql);
	while($f=$this->DB->fetch_array($req)){
	    $name=substr($f['name'],strlen($this->prefix));
	    $this->fields[$name]=$f['value'];
	}
    }
    
    function __get($var){
	if(isset($this->$var))
	    return $this->$var;
    }
    
    function __set($var,$value){
	$this->$var=$value;
    }
    
    function save(){
	if(is_null($this->id))
	    $sql="INSERT INTO adv_ar SET advid=".intval($this->advid).", date=".intval($this->date).", date_ar=".intval($this->date_ar).",
	    utilisateur='".mysqli_real_escape_string($this->DB->link_id,$this->utilisateur)."', numero='".mysqli_real_escape_string($this->DB->link_id,$this->numero)."', valide=".intval($this->valide);
	else
	    $sql="UPDATE adv_ar SET date=".intval($this->date).", date_ar=".intval($this->date_ar).",
	    numero='".mysqli_real_escape_string($this->DB->link_id,$this->numero)."', valide=".intval($this->valide)."
	    WHERE id=".$this->id;
	    
	$return=$this->DB->query($sql);
	if(is_null($this->id)){
	    $this->id=$this->DB->insert_id();
	    $this->prefix="ar".$this->id."_";
	}
	return $return;
    }
    
    function save_field($name,$value){
	if(isset($this->fields[$name])){
	    return $this->DB->query("UPDATE adv_fields SET value='".mysqli_real_escape_string($this->DB->link_id,$name)."' WHERE name='".$this->prefix.$name."' AND advid=".$this->advid);
	}else{
	    $this->fields[$name]=$value;
	    return $this->DB->query("INSERT INTO adv_fields SET advid=".intval($this->advid).", name='".mysqli_real_escape_string($this->DB->link_id,$this->prefix.$name)."', value='".mysqli_real_escape_string($this->DB->link_id,$value)."', utilisateur='".$this->utilisateur."', date=".time());
	}
    }
    
    function line_fields($lfid){
	$return=array();
	$keys=array_keys($this->fields);
	$lfkeys=preg_grep("/_".$lfid."$/",$keys);
	foreach($lfkeys as $key){
	    if(preg_match("/(.+)_".$lfid."$/",$key,$m)){
		$return[$m[1]]=$this->fields[$key];
	    }
	    if(preg_match("/^option_([0-9]+)_".$lfid."$/",$key,$m)){
		$return["options"][$m[1]]=$this->fields[$key];
	    }
	}
	return $return;
    }
}

?>
