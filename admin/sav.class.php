<?php

class sav {

private $DB;
public $id;
public $facturid;
public $userid;
public $lignes=array();
public $date_creation=0;
public $utilisateur="";
public $date_fin=0;
public $typelitige=false;
public $fields=array();
public $steps=array();
public $maxstep=0;
public $files=array();

public $types=array(0=>"Transport/installation",1=>"panne",2=>"erreur produit",3=>"rétractation/annulation",4=>"manque produit",5=>"fraude",6=>"avarie occulte",7=>"perte de colis");

function __construct($DB,$savid=null){
    $this->DB=$DB;
    if(is_null($savid)) return false;
    
    if($savid==0) $savid=$this->create();
    $sav=$DB->query_first("SELECT *, DATE_FORMAT(demande_cloture, '%d/%m/%Y %H:%i') as demande_cloture FROM sav LEFT JOIN facture USING(facturid) WHERE id=".$savid);
    $this->id=$sav['id'];
    $this->facturid=$sav['facturid'];
    $this->userid=$sav['userid'];
    $this->date_creation=$sav['date'];
    $this->utilisateur=$sav['utilisateur'];
    $this->date_fin=intval($sav['fin']);
    $this->date_demande_cloture=$sav['demande_cloture'];
    $this->sav_analyse = $sav['sav_analyse'];
    $lignes=explode(",",$sav['lignes']);
    foreach($lignes as $l){
      $m=preg_match("/(\d+)-(\d+)/",$l,$matches);
      if($m==0){ $matches[1]=1; $matches[2]=$l; }
      $this->lignes[$matches[2]]=$matches[1];
    }
    
    $r=$DB->query("SELECT * FROM sav_steps WHERE savid=$savid");
    while($step=$DB->fetch_array($r)){
	$s=array("utilisateur"=>$step['utilisateur'],"date"=>$step['date']);
	$this->steps[$step['step']]=$s;
	if($step['step']>$this->maxstep) $this->maxstep=$step['step'];
    }

    $r=$DB->query("SELECT * FROM sav_files WHERE savid=".$this->id);
    while($f=$DB->fetch_array($r)){
    	$array=array();
    	$array['date']=$f['date'];
    	$array['utilisateur']=$f['utilisateur'];
    	$array['name']=$f['name'];
    	$array['size']=$f['size'];
    	$array['ext']=$f['ext'];
    	$array['path']="sav/".$f['id'].".".$f['ext'];
    	$this->files[$f['id']]=$array;
    }

    $this->loadfields();
}

function loadfields(){
    $r=$this->DB->query("SELECT * FROM sav_fields WHERE savid=".$this->id);
    while($f=$this->DB->fetch_array($r)){
      $this->fields[$f['name']]=$f['value'];
      $this->utfields[$f['name']]=$f['utilisateur'];
      $this->datefields[$f['name']]=$f['date'];
    }

    $this->typelitige=$this->fields['sav_typelitige'];
}

function get_from_facturid($facturid){
    $return=false;
    $sql="SELECT id FROM sav WHERE facturid=".intval($facturid);
    $r=$this->DB->query($sql);
    if($this->DB->num_rows($r)>0){
	$return=array();
	while($s=$this->DB->fetch_array($r))
	    $return[]=new sav($this->DB,$s['id']);
    }
	
    return $return;
}

function radio_inputs($name,$values,$action=""){
    $return="<input type=\"hidden\" name=\"$name\" value=\"".$this->fields[$name]."\">";
    foreach($values as $lbl=>$value) $return.=" <input type=\"radio\" name=\"opt$name\" value=\"$value\" onclick=\"document.getElementsByName('$name')[0].value=this.value;$action;\" ".($this->fields[$name]==$value?"checked":"").">".$lbl;

    return $return;
}

function radio($name,$value,$action=""){
    $data=each($value);
    $return="<input type=\"radio\" name=\"opt$name\" value=\"$data[value]\" onclick=\"document.getElementsByName('$name')[0].value=this.value;$action;\" ".($this->fields[$name]==$data[value]?"checked":"").">".$data['key'];

    return $return;
}

function checkbox($name,$action=""){
    return "<input type=\"hidden\" name=\"$name\" value=\"".$this->fields[$name]."\"><input type=\"checkbox\" onclick=\"document.getElementsByName('$name')[0].value=(this.checked?1:0);$action;\" ".($this->fields[$name]==1?"checked":"").">";
}

function ouinon($name){
    return ($this->fields[$name]==1?"oui":"non");
}

function cal_input($name,$action="undefined"){
    return "<input type=\"text\" name=\"$name\" id=\"".str_replace("-","",$name)."\" value=\"".$this->fields[$name]."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,$action);\" readonly>";
}

function text_input($name,$action){
    return "<input type=\"text\" name=\"".$name."\" value=\"".$this->fields[$name]."\" onchange=\"$action\" onkeyup=\"$action\" onmouseup=\"$action\">";
}

function nb_input($name,$action){
    return "<input type=\"text\" name=\"".$name."\" value=\"".floatval($this->fields[$name])."\" onkeypress=\"nbonly(event,true,this);\" onchange=\"$action\" onclick=\"this.select()\" size=\"5\">";
}

function textarea($name,$action,$options=""){
    $optionstr="";
    if($options!="") foreach($options as $attr=>$value) $optionstr.=" ".$attr."=\"".$value."\"";
    return "<textarea name=\"$name\" onchange=\"$action\" onkeyup=\"$action\" onmouseup=\"$action\"$optionstr>".$this->fields[$name]."</textarea>";
}

function select($name,$values,$action){
    $return="<select name=\"$name\" onchange=\"$action\">";
    foreach($values as $value=>$txt) $return.="<option value=\"$value\"".($this->fields[$name]==$value?" selected":"").">$txt</option>";
    $return.="</select>";

    return $return;
}

function sendfile($action=""){
    return "<iframe src=\"sav2_ul.php?savid=".$this->id."&action=$action\" style=\"width:250px;height:60px;border:none;\" scrolling=\"no\"></iframe>";
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
      return "<span class=\"fileboxbutton\" onclick=\"fb.show('user_files.php?userid=".$this->userid."&facturid=".$this->facturid."&savid=".$this->id."');\"><img src=\"images/icon/jpg.png\"> Voir les fichiers joints (".count($this->files).")</span>";
}

function verif($name,$type="text",$action=""){
    switch($type){
	case "text":
	$input="<input type=\"text\" name=\"".$name."_verif\" value=\"".$this->fields[$name."_verif"]."\" size=\"20\" onchange=\"sav_autosave('".$name."_verif')\"> <span id=\"".$name."_verif-ok\"></span></span>";
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

    if(isset($_POST['date_fin'])){

        if(isset($_POST['action_sav']) && $_POST['action_sav'] == 'precloture'){

            //Avant de mettre à jour, on vérifie que la date de commande de clôture n'a pas déjà été renseignée
            $sql = "SELECT demande_cloture";
            $sql .= " FROM sav";
            $sql .= " WHERE id = ".$this->id;
            $date_demande_cloture = $this->DB->query_first($sql)['demande_cloture'];

            $sqlDateCloture = '';
            $suivi_cloture  = '';
            if($date_demande_cloture == 0)
            {
                //Renseignement de la date de cloture du SAV
                $sqlDateCloture = ", demande_cloture = NOW()";

                $this->date_demande_cloture = date('d/m/Y H:i');

                $suivi_cloture = "<br>Demande de clôture ".($step=='cloture_rapide'?'rapide (aucun retour client) ':'')."par " . $user_info['username'];
            }

            $sqlAnalyse    = '';
            $suivi_analyse = '';
            if(isset($_POST['precloture']) && in_array($_POST['precloture'], array(1,2)))
            {
                //Demande d'analyse avant cloture
                $sqlAnalyse = ", sav_analyse = '" . $_POST['precloture'] . "'";

                $this->sav_analyse = $_POST['precloture'];

                switch($_POST['precloture'])
                {
                    case 1:
                        $suivi_analyse = "<br>Marge du litige à analyser";
                        break;

                    case 2:
                        $suivi_analyse = "<br>Litige à clôturer sans analyse";
                        break;
                }
            }
            
            $sql = "UPDATE sav SET";
            $sql .= " id = " . $this->id;
            $sql .= $sqlDateCloture;
            $sql .= $sqlAnalyse;
            $sql .= " WHERE id = ".$this->id;
            $this->DB->query($sql); 

            $message_clot_rapide = ($step=='cloture_rapide'?'rapide (aucun retour client) ':'');

            $mail = new email();
            $mail->destinataire = "pr@ma-cave-a-vin.fr";
            $mail->expediteur = MAIL_ADV;
            $mail->to = array(MAIL_ADV);
            $mail->subject = "DEMANDE DE CLOTURE ".($step=='cloture_rapide'?'RAPIDE ':'')."SAV #SAV".$this->facturid."-".$this->id."#";
            $mail->body = "La clôture ".$message_clot_rapide."de ce SAV a été demandé par le service qualité. #SAV".$this->facturid."-".$this->id."# , je <a href=\"https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$this->facturid."&savid=".$this->id."#sav\">clôture</a>";
            $mail->sendmail();


            $suivi_commande = "Dossier SAV ".$this->types[$this->typelitige] . " du " . date("d/m/Y",$this->date_creation) . " cde " . $this->facturid . " : " . $suivi_cloture . $suivi_analyse;
        }

        if(isset($_POST['action_sav']) && $_POST['action_sav'] == 'cloture_annule')
        {
            $sql = "UPDATE sav SET";
            $sql .= " demande_cloture = 0";
            $sql .= " WHERE id = ".$this->id;
            $this->DB->query($sql); 

            $this->date_demande_cloture = 0;

            $suivi_commande = "Dossier SAV ".$this->types[$this->typelitige] . " du " . date("d/m/Y",$this->date_creation) . " cde " . $this->facturid . " :<br>Demande de clôture ".($step=='cloture_rapide'?'rapide (aucun retour client) ':'')."annulée";
        }

        if(isset($_POST['action_sav']) && $_POST['action_sav'] == 'cloture'){

            //Renseignement de la date de cloture du SAV
            $sql = "UPDATE sav SET";
            $sql .= " fin = ".time();
            $sql .= " WHERE id = ".$this->id;
            $this->DB->query($sql); 

            $this->date_fin = time();

            $suivi_commande = "Dossier SAV ".$this->types[$this->typelitige]." du ".date("d/m/Y",$this->date_creation)." cde ".$this->facturid." cloturé ".($step=='cloture_rapide'?'(rapide - aucun retour client) ':'')."par ".$user_info['username'];
        }

        //Insertion dans le suivi
        $sql = "INSERT INTO suivi_commande SET";
        $sql .= " facturid    = ".$this->facturid."";
        $sql .= ", date       = ".time()."";
        $sql .= ", commercial = '".$user_info['username']."'";
        $sql .= ", suivi      = '".mysqli_real_escape_string($this->DB->link_id,$suivi_commande)."'";
        $this->DB->query($sql);
    }

    if($step !==  'cloture_rapide'){
        $rec=$this->DB->query_first("SELECT id FROM sav_steps WHERE savid=".$this->id." AND step=".$step);
        if($rec['id']=="")
           $result=$this->DB->query("INSERT INTO sav_steps SET savid=".$this->id.", step=".$step.", utilisateur='".$user_info['username']."', date=".time());
       else
           $result=$this->DB->query("UPDATE sav_steps SET utilisateur='".$user_info['username']."', date=".time()." WHERE id=".$rec['id']);
       if($step>$this->maxstep) $this->maxstep=$step;
       $this->steps[$step]['utilisateur']=$user_info['username'];
       $this->steps[$step]['date']=time();
   }

    if(isset($_POST['savligne'])){
      $lignes=array();
      foreach($_POST['savligne'] as $ligneid=>$qte) $lignes[]=$qte."-".$ligneid;
      $this->DB->query("UPDATE sav SET lignes='".implode(",",$lignes)."' WHERE id=".$this->id);
      $this->lignes=$_POST['savligne'];
    }

    if($step == 'cloture_rapide'){
        print "<script>window.location = '/admin/facture.php?action=editer&facturid=".$this->facturid."';</script>";
    }
    else{
        return $result;
    }
}

function create(){
    global $user_info;

    $this->DB->query("INSERT INTO sav SET facturid='".$_GET['facturid']."', date=".time().", utilisateur='".$user_info['username']."'");
    $this->id=$this->DB->insert_id();
    $this->typelitige=intval($_GET['typelitige']);

	// Ajout dans le suivis de la commande (25/04/2016)
	$suiviCommande = "Création du SAV ".$this->id.", le ".date("d/m/Y" ,time())." : ".$this->types[$this->typelitige]."";
	$sql = "INSERT INTO suivi_commande SET";
	$sql.= " facturid=".$_GET['facturid'];
	$sql.= ", date=".time();
	$sql.= ", commercial='".$user_info['username']."'";
	$sql.= ", suivi='".$suiviCommande."'";
	$this->DB->query($sql);
	
    $this->DB->query("INSERT INTO sav_fields SET savid=".$this->id.", name='sav_typelitige', value='".$this->typelitige."', date=".time().", utilisateur='".$user_info['username']."'");

    $userid = $this->DB->query_first("SELECT userid FROM facture WHERE facturid ='".$_GET['facturid']."'");

   return $this->id;
}

}


?>
