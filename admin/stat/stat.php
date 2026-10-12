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

function ccstat(){
   $this->start=date("d/m/Y");

   $this->load_fai();
   $this->load_mr();
   $this->load_os();
   $this->load_browser();
   //$this->load_countries();
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

     $requete=mysql_query("SELECT * FROM ".$this->tblprefix."fai");
     while($row=mysql_fetch_array($requete)){
         $this->faibase[$row['id']]=$row['fai'];
         $this->fainame[$row['id']]=$row['name'];
         $this->faiserver[$row['id']]=$row['server'];
         $this->failoc[$row['id']]=$row['localisation'];

     }
}

function load_os(){
         $requete=mysql_query("SELECT kw,name FROM ".$this->tblprefix."os");
         while($row=mysql_fetch_array($requete)){
             $this->osbase[$row['kw']]=$row['name'];
         }
}

function load_mr(){
         $requete=mysql_query("SELECT keyword,name,param FROM ".$this->tblprefix."mr");
         while($row=mysql_fetch_array($requete)){
             $this->mrbase[$row['keyword']]=$row['name'];
             $this->mrparam[$row['name']]=$row['param'];
         }
}

function load_browser(){
         $requete=mysql_query("SELECT name,kw FROM ".$this->tblprefix."browser");
         while($row=mysql_fetch_array($requete)){
             $this->browserbase[$row['kw']]=$row['name'];
         }
}

function load_countries(){
         $requete=mysql_query("SELECT LCASE(COUNTRY_CODE2) AS code,LCASE(COUNTRY_NAME) AS name FROM ip2country GROUP BY COUNTRY_CODE2");
         while($row=mysql_fetch_array($requete)){
             $this->countrylist[$row['code']]=$row['name'];
         }
}


//


function calcstat($save=false){
	  $now=getdate();
	  $now0=mktime(0,0,0,$now['mon'],$now['mday'],$now['year']);
	 /* if($this->sst<$now0 && $save==false){
	    $this->loadxml();
	    return true;
	  }*/


	 $req="SELECT * FROM ".$this->tblprefix."stat WHERE ";
         if($this->sst>0 && $this->est>0) $req.="timestamp > ".$this->sst." AND timestamp < ".$this->est;

         $requete=mysql_query($req);
         $this->hostcount=mysql_num_rows($requete);
         $i=0;

         while($row=mysql_fetch_array($requete)){

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
                 mysql_query("INSERT IGNORE INTO stat_unknown SET data='".$row['agent']."', type='os'");
                 $osname="Inconnu";
             }
             //if(!in_array($osname,$this->os)) $this->os[$osname]=0;
             $this->os[$osname]++;
             $browsername=$this->browser_name($row['agent']);
             if($browsername==""){
                 mysql_query("INSERT IGNORE INTO stat_unknown SET data='".$row['agent']."', type='browser'");
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
                 mysql_query("INSERT IGNORE INTO stat_unknown SET data='".$row['host']."', type='fai'");
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
      
         $requete=mysql_query("SELECT * FROM ".$this->tblprefix."stat_page WHERE (timestamp > ".$this->sst.") AND (timestamp < ".$this->est.")");
         while($row=mysql_fetch_array($requete)){
             if($row['page']=="") $row['page']="index";
            
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
         $pi=new pagename();
         $requete2=mysql_query("SELECT * FROM ".$this->tblprefix."stat_page WHERE statid=".$statid);
         while($p=mysql_fetch_object($requete2)){
                    $pi->pageinfos($p->page,$p->view,$p->pageid);
                    $pages[$p->timestamp]=$pi->pagename;
                    //if($pi->etab!=false) $this->etabinterest[$pi->etab]++;
                    //if($pi->member!=false) $this->memberinterest[$pi->member]++;
         }
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
         $sp=explode(".",$host);
         $mfai=$host;
         $local="";
         $faiid2=false;
         $htype=0;
         $ret=array();

         foreach($sp as $spi){
                     $faiid=array_keys($this->faibase,$spi);
                     if($faiid!=false){
                       $mfai=$this->fainame[$faiid[0]];
                       $faiid2=$faiid;
                       $htype=1;
                     }
         }

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
         foreach($this->osbase as $kw=>$name){
            if(strpos($a,$kw)!==false) return $name;
         }
}

function mrech($host){
         foreach($this->mrbase as $kw=>$name){
           if(strpos($host,$kw)!==false) return $name;
         }
         return false;
}

function browser_name($agent){
         foreach($this->browserbase as $kw=>$name){
              if(strpos($agent,$kw)!==false) return $name;
         }
         return false;
}

function is_ip($h){
         if(is_numeric(strtr($h,".","0"))) return true;
         return false;
}

function calcorders($prov=false,$allowed_status=false,$orderby=false){
         $ret=Array();
	  $this->orderstat=array();
	 $sql="SELECT id,order_date,UNIX_TIMESTAMP(order_date),customer FROM orders WHERE order_date BETWEEN FROM_UNIXTIME($this->sst) AND FROM_UNIXTIME($this->est)";
	 if($prov!=false){
	    if(is_numeric($prov))
		$sql.=" AND partner=".$prov;
	    else
		$sql.=" AND referer like '%".$prov."%'";
	 }
	 if($allowed_status!=false) $sql.=" AND (order_status=".implode(" OR order_status=",$allowed_status).")";
	 if($orderby!=false) $sql.=" ORDER BY ".$orderby;
	 $requete=mysql_query($sql);
         if($requete==false) return $ret;
         $this->orderscount=mysql_num_rows($requete);
         while($row=mysql_fetch_array($requete)){
	     
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
         $ip2 = (double) ($dotted[0]*16777216)+($dotted[1]*65536)+($dotted[2]*256)+($dotted[3]);

         $requete= mysql_query("SELECT COUNTRY_CODE2 FROM ip2country WHERE $ip2 BETWEEN `IP_FROM` AND `IP_TO`");
         $row=mysql_fetch_array($requete);

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
                fwrite($f,"<item name=\"".htmlentities($name)."\" nb=\"".$nb."\" />\n");
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
		fwrite($f,"<pay method=\"$id\" nb=\"$nb\" />");
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

function get_visit($user,$time){
  $requete=mysql_query("SELECT * FROM stat WHERE user=".$user." AND timestamp<".$time." ORDER BY timestamp DESC LIMIT 0,1");
  if(mysql_num_rows($requete)==0) return false;
  $row=mysql_fetch_object($requete);
  $return=new visit();
  $return->statid=$row->id;
  $return->timestamp=$row->timestamp;
  $return->provenance=$row->referant;
  $return->os=$this->os_name($row->agent);
  $return->browser=$this->browser_name($row->agent);
  $return->fai=$this->getfai($row->host);
  $return->ip==$this->ip;
  
  return $return;
  
  
}

function get_prov($site,$cid=0){
  $return=array();
  $sql="SELECT * FROM stat WHERE referant LIKE '%".$site."%' AND timestamp > ".$this->sst." AND timestamp < ".$this->est;
  if($cid!=0) $sql.=" AND cid=".$cid;
  $sql.=" ORDER BY timestamp";
  $requete=mysql_query($sql);
  if(mysql_num_rows($requete)==0) return false;
  while($row=mysql_fetch_object($requete)){
    $v=new visit();
    $v->statid=$row->id;
    $v->timestamp=$row->timestamp;
    $v->provenance=$row->referant;
    $v->os=$this->os_name($row->agent);
    $v->browser=$this->browser_name($row->agent);
    $v->fai=$this->getfai($row->host);
    $v->ip=$row->ip;
    $return[]=$v;
  }

  return $return;
}

function livestat(){
  $requete=mysql_query("SELECT * FROM stat s, stat_page sp WHERE sp.statid=s.id AND sp.timestamp>".(time()-60)." GROUP BY ip");
  if(mysql_num_rows($requete)==0) return false;
  while($row=mysql_fetch_object($requete)){
    $v=new visit();
    $v->statid=$row->id;
    $v->timestamp=$row->timestamp;
    $v->provenance=$row->referant;
    //$v->os=$this->os_name($row->agent);
    //$v->browser=$this->browser_name($row->agent);
    //$v->fai=$this->getfai($row->host);
    $v->ip=$row->ip;
    $v->user=$row->user;
    $return[]=$v;
  }

  return $return;
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
var $pages;
var $user;

}

?>