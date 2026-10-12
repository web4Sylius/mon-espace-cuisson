<?php

class ccstat{

var $tblprefix;

var $start=0;
var $mode="d";
var $sst;
var $est;
var $len;
var $fdata=0;
var $int=24;

var $user=0;
var $uservisit=array();

var $periode;

var $osbase=array();
var $browserbase=array();
var $mrbase=array();
var $faibase=array();
var $fainame=array();
var $failoc=array();
var $faiserver=array();
var $mrstatid=array();


var $os=array();
var $membre=array();
var $browser=array();
var $hmembre=array();
var $countries=array();
var $countrylist=array();

var $membrecount=0;

var $hostcount;

var $ihostcount;
var $ihost=array();
var $unkfai;

var $chost=array();
var $sitehost=array();
var $loc=array();
var $chostcount;
var $iphostcount;

var $mrhost=array();
var $mrhostcount;

var $directlink;
var $mrlink=array();
var $sitelink=array();
var $rechstr=array();
var $mrsrc=array();

var $real=array();

var $vpage=array();
var $pagecount;
var $indexpage=array();

var $orders=array();
var $orderscount;
var $orderssum;
var $paymenttype;
var $orderstat=array();

var $link;

function __construct(){
   global $DB_site;
   global $DB_site2;

   $this->start=date("d/m/Y");
   if(isset($DB_site)){
       $this->link = $DB_site->link_id;
   } else {
       $this->link = $DB_site2->link_id;
   }
   
   
  // $this->load_fai();
  // $this->load_mr();
  // $this->load_os();
  // $this->load_browser();
 //  $this->load_countries();
}

function init(){

         switch($this->mode){
             case "d":
             $this->len=(3600*24);
             $this->mode="G";
             $this->int=24;
             $this->fdata=0;
             break;

             case "m":
             $this->int=date("t",$start);
             $this->len=3600*24*$this->int;
             $this->mode="j";
             $this->fdata=1;
             break;

             case "y":
             $this->int=12;
             $this->len=3600*24*365+date("n",$start);
             $this->mode="n";
             $this->fdata=1;
             break;
        }

        $today=getdate();

    $sp=explode("/",$this->start);
    $this->sst=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
        

        $this->est=$this->sst+$this->len;
        $this->sst2=$this->sst-$this->len;

        $this->periode=date("d/m/Y",$this->sst)." - ".date("d/m/Y",$this->est);

}

function load_fai(){
 //

     $requete=mysqli_query($this->link,"SELECT * FROM ".$this->tblprefix."fai");
     while($row=mysqli_fetch_array($requete)){
         $this->faibase[$row['id']]=$row['fai'];
         $this->fainame[$row['id']]=$row['name'];
         $this->faiserver[$row['id']]=$row['server'];
         $this->failoc[$row['id']]=$row['localisation'];

     }
}

function load_os(){
         $requete=mysqli_query($this->link,"SELECT kw,name FROM ".$this->tblprefix."os");
         while($row=mysqli_fetch_array($requete)){
             $this->osbase[$row['kw']]=$row['name'];
         }
}

function load_mr(){
         $requete=mysqli_query($this->link,"SELECT keyword,name,param FROM ".$this->tblprefix."mr");
         while($row=mysqli_fetch_array($requete)){
             $this->mrbase[$row['keyword']]=$row['name'];
             $this->mrparam[$row['name']]=$row['param'];
         }
}

function load_browser(){
         $requete=mysqli_query($this->link,"SELECT name,kw FROM ".$this->tblprefix."browser");
         while($row=mysqli_fetch_array($requete)){
             $this->browserbase[$row['kw']]=$row['name'];
         }
}

function load_countries(){
         $requete=mysqli_query($this->link,"SELECT LCASE(COUNTRY_CODE2) AS code,LCASE(COUNTRY_NAME) AS name FROM ip2country GROUP BY COUNTRY_CODE2");
         while($row=mysqli_fetch_array($requete)){
             $this->countrylist[$row['code']]=$row['name'];
         }
}


//
function getstatvisit($statid){
    $r=mysqli_query($this->link,"SELECT * FROM stat WHERE id=$statid");
    if(mysqli_num_rows($r)==0) return false;
    $row=mysqli_fetch_object($r);
    $return=new visit();
    $return->statid=$row->id;
    $return->timestamp=$row->timestamp;
    $return->provenance=$row->referant;
    $return->os=$this->os_name($row->agent);
    $return->browser=$this->browser_name($row->agent);
    $return->fai=$this->getfai($row->host);
    $return->ip=$row->ip;
    $return->country=$this->ip2country($row->ip);
    $return->bot=$this->mrech($row->agent);
    $return->mrprovenance=$this->mrech($row->provenance);
  
  return $return;
    
}

function calcstat($save=false){
      $now=getdate();
      $now0=mktime(0,0,0,$now['mon'],$now['mday'],$now['year']);
      if($this->sst<$now0 && $save==false){
        $this->loadxml();
        return true;
      }


     $req="SELECT * FROM ".$this->tblprefix."stat WHERE ";
         if($this->sst>0 && $this->est>0) $req.="timestamp > ".$this->sst." AND timestamp < ".$this->est;

         $requete=mysqli_query($this->link,$req);
         $this->hostcount=mysqli_num_rows($requete);
         $i=0;

         while($row=mysqli_fetch_array($requete)){

             $vfai=$this->getfai($row['host']);

             if($vfai['htype']==0){
                 $mr=$this->mrech($row['host']);
                 if($mr){ $fai['fai']=$mr;$vfai['htype']=2; }
                 $ip=$this->is_ip($row['host']);
                 if($ip) $vfai['htype']=3;
             }


             //$host=realhost($row['host']);
             $osname=$this->os_name($row['agent']);
             if($osname==""){
                 mysqli_query($this->link,"INSERT IGNORE INTO stat_unknown SET data='".$row['agent']."', type='os'");
                 $osname="Inconnu";
             }
             //if(!in_array($osname,$this->os)) $this->os[$osname]=0;
             $this->os[$osname]++;
             $browsername=$this->browser_name($row['agent']);
             if($browsername==""){
                 mysqli_query($this->link,"INSERT IGNORE INTO stat_unknown SET data='".$row['agent']."', type='browser'");
                 $browsername="Inconnu";
             }
             //if(!in_array($browsername,$this->browser)) $this->browser[$browsername]=0;
             $this->browser[$browsername]++;

         if($row['user']!=0) $this->membre[$row['user']]++; 
         
             if($row['referant']==""){
                $this->directlink++;
             }else{
                $ref=$row['referant'];
                $a=parse_url($row['referant']);
                $host=$a['host'];
                $ismr=$this->mrech($host);
                if($ismr!=false){
                    parse_str($a['query'], $aq);
                    $param="q";//$GLOBALS['mrparam'][$mr];
                    $param=$this->mrparam[$ismr];
            $kw=$aq[$param];
                    $this->rechstr[$kw]++;
                    //$this->mrlink[$mr]['count']++;
                    $this->mrlink[$ismr][$ref]++;
                    $this->mrsrc[$ref]=$kw;
                }else{
                    $this->sitelink[$host][$ref]++;
                }
            }

             switch($vfai['htype']){
                 case 0:
                 //"Inconnu";
                 $unkhost=$row['host'];
                 if(!in_array($unkhost,$this->ihost)) $this->ihost[$unkhost]=0;
                 $this->ihost[$unkhost]++;
                 $this->ihostcount++;
                 if(isset($_GET['ifai'])) $this->unkfai.="<br>".$row['host'];
                 mysqli_query($this->link,"INSERT IGNORE INTO stat_unknown SET data='".$row['host']."', type='fai'");
                 break;

                 case 3:
                 //"Adresse IP";
                 $this->iphostcount++;
          break;

                 case 1:
                 //"Visiteur";
                 if($vfai['htype']==1) $this->chost[$vfai['fai']]++;
                 $this->sitehost[$fai['fai']]=$host['site'];
                 $this->loc[$vfai['local']]++;
                 $this->chostcount++;
                 if($row['cid']==$row['id']) $this->newvisitor++;
                 // $ccode=country($row['ip']);
                 $country[$ccode]++;
                 $this->real[date($this->mode,$row['timestamp'])]++;
                 $this->countries[$this->ip2country($row['ip'])]++;
                 break;

                 case 2:
                 //"Moteur de recherche";
                 $this->mrstatid[]=$row['id'];
                 $this->mrhost[$vfai['fai']]++;
                 $this->mrhostcount++;
                 break;

             }
             $i++;
        }

    $this->calcpages();
    $this->calcorders();

        return true;

}

function calcpages(){
         $pc=new pagecontrol();
         // $requete=mysqli_query($this->link,"SELECT * FROM ".$this->tblprefix."stat_page WHERE (timestamp > ".$this->sst.") AND (timestamp < ".$this->est.")");
         
         $requete=mysqli_query($this->link," SELECT * FROM stat_page WHERE (timestamp > ".$this->sst.") AND (timestamp < ".$this->est.") UNION SELECT * FROM stat_page_arch2 WHERE (timestamp > ".$this->sst.") AND (timestamp < ".$this->est.") ");
         
         
         while($row=mysqli_fetch_array($requete)){
             if($row['page']=="") $row['page']="index";
             $pagename=$pc->getpagename($row['page']);
             if(!in_array($row['statid'],$this->mrstatid)){
                 //if(!in_array($pagename,$this->vpage)) $this->vpage[$pagename]=array();
                 //if(!in_array($pagename,$this->vpage[$pagename])) $this->vpage[$pagename][$row['detail']]=0;
                 $this->vpage[$pagename][$row['detail']]++;
                 $this->pagecount++;
             }else{
                 $this->indexpage[$pagename][$row['detail']]++;
             }
         }

}



function getpages($statid){
     $pages=array();
         // $requete2=mysqli_query($this->link,"SELECT timestamp,page FROM ".$this->tblprefix."stat_page WHERE statid=".$statid);
         $requete2=mysqli_query($this->link," SELECT * FROM stat_page WHERE statid='".$statid."' UNION SELECT * FROM stat_page_arch2 WHERE statid='".$statid."' ");
         
         
         if($requete2==false) return $pages;
         while($p=mysqli_fetch_array($requete2)){
                    
                    $pages[$p['timestamp']]=$p['page'];

         }
     ksort($pages);
         return $pages;

}


function graphdata(){

         $sep="";
         $graphdata="";
         for($i=$this->fdata;$i<$this->int;$i++){
            $graphdata.=$sep.abs($this->real[$i]);
            $sep=",";
        }
        return $graphdata;

}


function getfai($host){
   
         $mfai=$host;
         $local="";
         $faiid2=false;
         $htype=0;
         $ret=array();

     $requete=mysqli_query($this->link,"SELECT * FROM fai WHERE '".$host."' REGEXP fai LIMIT 10");
     $fai=mysqli_fetch_array($requete);
     $ret['fai']=$fai['name'];
     if(mysqli_num_rows($requete)>1){
         $requete=mysqli_query($this->link,"SELECT * FROM fai WHERE fai='".$fai['fai']."' AND '".$host."' REGEXP server LIMIT 1");
         $loc=mysqli_fetch_array($requete);
         $ret['loc']=$loc['localisation'];
     }
    
     return $ret;
         /*if($faiid2!==false){
               foreach($faiid2 as $servid)
                           if(strpos($sp[0],$this->faiserver[$servid])!==false) $local=$this->failoc[$servid];

         } */

         $ret['htype']=$htype;
         $ret['fai']=$mfai;
         $ret['local']=$local;

         return $ret;

}

function os_name($a){
     $requete=mysqli_query($this->link,"SELECT name FROM os WHERE '".$a."' REGEXP kw");
     if($requete==false) return false;
     $os=mysqli_fetch_array($requete);
     if($os==false) return false;
     return $os['name'];
}

function mrech($host){
     if($host=="") return false;
     
     $ret=array();
     $requete=mysqli_query($this->link,"SELECT name,param FROM mr WHERE '".mysqli_real_escape_string($this->link,$host)."' REGEXP keyword");
     if($requete==false) return false;
     $mr=mysqli_fetch_array($requete);
     if($mr==false){
        $ret['type']="autres";
        if(strpos($host,"criteo.com")>-1) $ret['name']=$ret['type']="criteo";
        if(strpos($host,"nxtck.com")>-1) $ret['name']=$ret['type']="next performance";
        if(strpos($host,"adroll.com")>-1) $ret['name']=$ret['type']="adroll";
     }else{
        $ret['name']=$mr['name'];
        $ret['type']="naturel";
        
        if($mr['param']!="" && substr($host,0,7)=="http://"){
          $a=parse_url($host);
          parse_str($a['query'], $aq);
          $ret['q']=$aq[$mr['param']];
        }
        
        if(strpos($host,"/aclk?")>-1){
          $mr['name'].=" adwords";
          $ret['type']="adword";
        }
     }
     
     return $ret;
}

function browser_name($agent){
     $requete=mysqli_query($this->link,"SELECT name FROM browser WHERE '".$agent."' REGEXP kw");
     if($requete==false) return false;
     $br=mysqli_fetch_array($requete);
     if($br==false) return false;
     return $br['name'];
}

function is_ip($h){
         if(is_numeric(strtr($h,".","0"))) return true;
         return false;
}

function calcorders($prov=false,$allowed_status=false,$orderby=false){
         $ret=Array();
      $this->orderstat=array();
     $sql="SELECT id,order_date,UNIX_TIMESTAMP(order_date),customer FROM orders WHERE order_date BETWEEN FROM_UNIXTIME(".$this->sst.") AND FROM_UNIXTIME(".$this->est.")";
     if($prov!=false){
        if(is_numeric($prov))
        $sql.=" AND partner=".$prov;
        else
        $sql.=" AND referer like '%".$prov."%'";
     }
     if($allowed_status!=false) $sql.=" AND (order_status=".implode(" OR order_status=",$allowed_status).")";
     if($orderby!=false) $sql.=" ORDER BY ".$orderby;
     $requete=mysqli_query($this->link,$sql);
         if($requete==false) return $ret;
         $this->orderscount=mysqli_num_rows($requete);
         while($row=mysqli_fetch_array($requete)){
         
        $order=new order($row['id']);
        $ret[$row['order_date']]=$order;
        $this->orders[date($this->mode,$row['order_date'])]++;
        $this->orderssum+=($order->items_price+$order->shipping_price)*TVA_COEFF;
        $this->paymenttype[$order->payment_method]++;
        $visit=$this->get_visit($order->customer,$order->order_date);
        $this->orderstat[]=$visit;
     
          
         }
         $this->orderssum=round($this->orderssum,2);
         return $ret;
}

function ip2country($ip){
         global $DB_site;
         $dotted = preg_split( "/[.]+/", $ip);
         $ip2 = @(double) ($dotted[0]*16777216)+($dotted[1]*65536)+($dotted[2]*256)+($dotted[3]);

         $requete= mysqli_query($this->link,"SELECT COUNTRY_CODE2 FROM ip2country WHERE ".$ip2." BETWEEN `IP_FROM` AND `IP_TO`");
         $row=mysqli_fetch_array($requete);

         return strtolower($row['COUNTRY_CODE2']);

}

function loadxml(){
         $this->os=array();
         $this->browser=array();
         $this->fai=array();
         $this->chost=array();
         $this->mr=array();
         $this->real=array();
         $this->orders=array();
         $this->vpage=array();
         $this->pagecount=0;
         $this->orderscount=0;
         $this->orderssum=0;
         $this->mrhostcount=0;

         $this->periode=date("d/m/Y",$this->sst)." - ".date("d/m/Y",$this->est);

         $xmldir="stat/xml";
         $d=opendir($xmldir);
         //print $this->sst."<br>".$this->est;
         while($file=readdir($d)){
              $name=pathinfo($file,PATHINFO_FILENAME);
              //print "<br>".intval($name)." ".$this->sst;
              
          if(intval($name)>=$this->sst && intval($name)<$this->est){
                  $this->loadxmlfile($xmldir."/".$file);
         // print "<br>load $file";
              }
         }
         $this->chostcount=array_sum($this->chost);
}

function loadxmlfile($file){
         $xml=simplexml_load_file($file);
     $sstfloat=floatval($xml->time[0]['sst']);

         $this->periode=$xml->date;
         foreach($xml->os[0]->item as $os){
                $this->os[(string) $os['name']]+=intval($os['nb']);
         }
         foreach($xml->browser[0]->item as $browser){

                $this->browser[(string) $browser['name']]+=intval($browser['nb']);
         }
         foreach($xml->fai[0]->item as $host){

                $this->chost[(string) $host['name']]+=intval($host['nb']);
         }
         foreach($xml->mr[0]->item as $mr){
                $this->mrhostcount+=intval($mr['nb']);
                $this->mrhost[(string) $mr['name']]+=intval($mr['nb']);
         }
         foreach($xml->real[0]->time as $real){     
         switch($this->mode){
          case "G":
          $this->real[(string) $real['h']]=intval($real['nb']);
          break;
          case "j":
          $this->real[date("j",$sstfloat)]+=intval($real['nb']);
          break;
          case "n":
          $this->real[date("n",$sstfloat)]+=intval($real['nb']);
          break;
        }
         }
         foreach($xml->order[0]->time as $order){
                $this->orders[(string) $order['h']]=intval($order['nb']);
                $this->orderscount+=intval($order['nb']);
         }
         $this->orderssum+=floatval($xml->order[0]->total);
         foreach($xml->pages[0]->item as $page){
                $this->vpage[(string) $page['page']][urldecode($page['detail'])]=intval($page['nb']);
                $this->pagecount+=intval($page['nb']);
         }
         foreach($xml->referant[0]->item as $ref){
               $this->sitelink[(string) $ref['host']][urldecode($ref['link'])]+=intval($ref['nb']);
         }
         foreach($xml->src[0]->item as $ref){
               $this->mrlink[(string) $ref['host']][urldecode($ref['link'])]+=intval($ref['nb']);
               $this->mrsrc[urldecode($ref['link'])]=urldecode($ref['kw']);
         }



}

function savexml(){
         if(!is_dir("xml")) mkdir("xml");
         $ts0=getdate($this->sst);
         $file="xml/".mktime(0,0,0,$ts0['mon'],$ts0['mday'],$ts0['year']).".xml";
         $f=fopen($file,"w");
         fwrite($f,"<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n");
         fwrite($f,"<root>\n");

         fwrite($f,"<time sst=\"".$this->sst."\" est=\"".$this->est."\" />\n");
         fwrite($f,"<hostcount>$this->hostcount</hostcount>\n");
         fwrite($f,"<date>".$this->periode."</date>\n");

         fwrite($f,"<os>\n");
         foreach($this->os as $name=>$nb)
                fwrite($f,"<item name=\"".$name."\" nb=\"".$nb."\" />\n");
         fwrite($f,"</os>\n");

         fwrite($f,"<membre>\n");
         foreach($this->membre as $id=>$nb)
                fwrite($f,"<item id=\"".$id."\" nb=\"".$nb."\" />\n");
         fwrite($f,"</membre>\n");

         fwrite($f,"<browser>\n");
         foreach($this->browser as $name=>$nb)
                fwrite($f,"<item name=\"".$name."\" nb=\"".$nb."\" />\n");
         fwrite($f,"</browser>\n");

         fwrite($f,"<fai>\n");
         foreach($this->chost as $name=>$nb)
                fwrite($f,"<item name=\"".htmlentities($name,ENT_COMPAT,"ISO-8859-15")."\" nb=\"".$nb."\" />\n");
         fwrite($f,"</fai>\n");

         fwrite($f,"<loc>\n");
         foreach($this->countries as $name=>$nb)
                fwrite($f,"<item name=\"".$name."\" nb=\"".$nb."\" />\n");
         fwrite($f,"</loc>\n");

         fwrite($f,"<mr>\n");
         foreach($this->mrhost as $name=>$nb)
                fwrite($f,"<item name=\"".$name."\" nb=\"".$nb."\" />\n");
         fwrite($f,"</mr>\n");

         fwrite($f,"<real>\n");
         foreach($this->real as $h=>$nb)
                fwrite($f,"<time h=\"".$h."\" nb=\"$nb\" />\n");
         fwrite($f,"</real>\n");

         fwrite($f,"<order>\n");
         foreach($this->orders as $h=>$nb){
               fwrite($f,"<time h=\"$h\" nb=\"$nb\" />\n");
         }
     foreach($this->paymenttype as $id=>$nb)
        fwrite($f,"<method=\"$id\" nb=\"$nb\" />");
     foreach($this->orderstat as $visit){
        
     }
         fwrite($f,"<count>".array_sum($this->orders)."</count>\n");
         fwrite($f,"<total>".$this->orderssum."</total>\n");
         fwrite($f,"</order>\n");

         fwrite($f,"<pages>");
         foreach($this->vpage as $name=>$details){
                 foreach($details as $detail=>$nb){
                       fwrite($f,"<item page=\"$name\" detail=\"".urlencode($detail)."\" nb=\"$nb\" />\n");
                 }
         }
         fwrite($f,"</pages>\n");
         fwrite($f,"<referant>\n");
         foreach($this->sitelink as $host=>$links){
                 foreach($links as $link=>$nb){
                      fwrite($f,"<item host=\"$host\" link=\"".urlencode($link)."\" nb=\"$nb\" />\n");
                 }
         }
         fwrite($f,"</referant>\n");
         fwrite($f,"<src>\n");
         foreach($this->mrlink as $mr=>$refs){
                foreach($refs as $ref=>$nb){
                      fwrite($f,"<item mr=\"$mr\" link=\"".urlencode($ref)."\" kw=\"".urlencode($this->mrsrc[$ref])."\" nb=\"$nb\" />\n");
                }
         }
         fwrite($f,"</src>\n");

         fwrite($f,"</root>");
         fclose($f);
}

function formattime($sec){

         $temp = $sec % 3600;
         $time['hours'] = ( $sec - $temp ) / 3600 ;
         $time['seconds'] = $temp % 60 ;
         $time['minutes'] = ( $temp - $time['seconds'] ) / 60;
         if($time['hours']>0) $time['str']=$time['hours']." h ";
         $time['str'].=$time['minutes']." min ".round($time['seconds']). "s";
         return $time;
}

function get_visit($user,$time,$tbl="stat",$ip_acilyon=true,$DB){
  $returns=array();
  $sql = "SELECT *";
  $sql .= " FROM ".$tbl;
  $sql .= " WHERE user = ".$user;
  $sql .= " AND timestamp < ".$time;
  if(!$ip_acilyon) $sql .= " AND ip != '80.12.82.212'";
  $sql .= " ORDER BY timestamp DESC";
  $requete=$DB->query($sql);
  if($requete==false || $DB->num_rows($requete)==0) return false;
  while($row=mysqli_fetch_object($requete)){
    $return=new visit();
    $return->statid=$row->id;
    $return->timestamp=$row->timestamp;
    $return->provenance=$row->referant;
    $return->mrprovenance=$this->mrech($row->referant);
    $return->os=$this->os_name($row->agent);
    $return->browser=$this->browser_name($row->agent);
    $return->fai=$this->getfai($row->host);
    $return->ip=$row->ip;
    $return->country=$this->ip2country($this->ip);
    $return->pages=$this->getpages($row->id);
    $returns[]=$return;
  }
  return $returns;
  
}

function get_all_visit($user){
  $returns=array();
  $requete=mysqli_query($this->link,"(SELECT * FROM stat as s where s.user=$user) UNION (SELECT * FROM stat_arch2 as a where a.user=$user) ORDER BY timestamp DESC");
  if($requete==false || mysqli_num_rows($requete)==0) return false;
  while($row=mysqli_fetch_object($requete)){
    $return=new visit();
    $return->statid=$row->id;
    $return->timestamp=$row->timestamp;
    $return->provenance=$row->referant;
    $return->mrprovenance=$this->mrech($row->referant);
    $return->os=$this->os_name($row->agent);
    $return->browser=$this->browser_name($row->agent);
    $return->fai=$this->getfai($row->host);
    $return->ip=$row->ip;
    $return->country=$this->ip2country($this->ip);
    $return->pages=$this->getpages($row->id);
    $returns[]=$return;
  }
  return $returns;
}

function get_prov($site,$cid=0){
  $return=array();
  $sql="SELECT * FROM stat WHERE referant LIKE '%".$site."%' AND timestamp > ".$this->sst." AND timestamp < ".$this->est;
  if($cid!=0) $sql.=" AND cid=".$cid;
  $sql.=" ORDER BY timestamp";
  $requete=mysqli_query($this->link,$sql);
  if(mysqli_num_rows($requete)==0) return false;
  while($row=mysqli_fetch_object($requete)){
    $v=new visit();
    $v->statid=$row->id;
    $v->timestamp=$row->timestamp;
    $v->provenance=$row->referant;
    $v->os=$this->os_name($row->agent);
    $v->browser=$this->browser_name($row->agent);
    $v->fai=$this->getfai($row->host);
    $v->ip==$this->ip;
    $return[]=$v;
  }

  return $return;
}

function livestat(){
  $requete=mysqli_query($this->link,"SELECT sp.statid,s.referant,s.agent,s.host,s.ip,s.referant,s.user,s.timestamp AS enterdate FROM stat s INNER JOIN stat_page sp ON (sp.statid=s.id) WHERE sp.timestamp>".(time()-60)." GROUP BY ip");
//  $requete=mysqli_query($this->link,"SELECT *,s.timestamp AS enterdate FROM stat s INNER JOIN stat_page sp ON (sp.statid=s.id) WHERE sp.timestamp>".(time()-60)." GROUP BY ip");
  if($requete==false || mysqli_num_rows($requete)==0) return false;
  while($row=mysqli_fetch_object($requete)){
    $v=new visit();
    $v->statid=$row->statid;
    $v->timestamp=$row->enterdate;
    $v->provenance=$row->referant;
    $v->os=$this->os_name($row->agent);
    $v->browser=$this->browser_name($row->agent);
    $v->fai=$this->getfai($row->host);
    $v->ip=$row->ip;
    $v->host=$row->host;
    /*if($v->fai['fai']==""){ 
    $whois=$this->whoconnexion("whois.ripe.net",$row->ip);
    $v->fai['fai']=$this->whofield($whois,"address");
    }*/
    $v->country=$this->ip2country($row->ip);
    $v->bot=$this->mrech($row->agent);
    $v->mrprovenance=$this->mrech($row->provenance);
    $v->user=$row->user;
    $v->pages=$this->getpages($v->statid);
    $return[]=$v;
  }

  return $return;
}

function temporary_table($sql){
    mysqli_query($this->link,"CREATE TEMPORARY TABLE `stat_temp` (
  `id` int(10) NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `referant` varchar(150) NOT NULL DEFAULT '',
  `agent` varchar(255) NOT NULL,
  `host` varchar(50) NOT NULL DEFAULT '',
  `timestamp` int(10) NOT NULL DEFAULT '0',
  `cid` int(10) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0'
)");
  
  $return=mysqli_query($this->link,"INSERT INTO stat_temp ".$sql);
  return $return;

}

function whofield($data,$name){
    $name=strtolower($name).":";
    $p=strpos($data,$name);
    if($p==-1) return $data;

    $p+=strlen($name);
    $e=strpos($data,"\n",$p);

    return trim(substr($data,$p,$e-$p));
}

function whoconnexion($serveur,$ip_recherche){
            $socket=fsockopen($serveur, 43);
           
            if ($socket!=0){

                        fwrite($socket, "$ip_recherche\n");
           
                        while (feof($socket)==0)
                                    $tampon = $tampon . fgets($socket, 1000);
           
          
                        fclose($socket);
        }else{
                        return false;
        }

        if (eregi("www.iana.org", $tampon))
          $tampon=$this->whoconnexion("whois.arin.net",$ip_recherche);
           
        elseif (eregi("whois.apnic.net", $tampon))
          $tampon=$this->whoconnexion("whois.apnic.net",$ip_recherche);
          
        elseif (eregi("whois.registro.br", $tampon))
          $tampon=$this->whoconnexion("whois.registro.br",$ip_recherche);
            
        elseif (eregi("nic.ad.jp", $tampon))
          $tampon=$this->whoconnexion("whois.nic.ad.jp",$ip_recherche);

           
            return ($tampon);
}

}


class visit{

var $statid;
var $timestamp;
var $provenance;
var $os;
var $browser;
var $fai;
var $loc;
var $ip;
var $host;
var $country;
var $pages;
var $bot;
var $mrprovenance;

}

?>