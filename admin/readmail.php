<?php

function getvar($data,$name){
        $p=preg_match("/".$name.": (.+)(;|\n|$)/iU",$data,$m);

        if($p>0)
            return trim($m[1]);
        
        return false;
}

function getboundary($data) {
      $p=preg_match('/boundary=(.+)/i',$data,$m);
      if($p) return str_replace("\"","",$m[1]);
      return false;
}

function getparts($data){
      $msgpos=strpos($data,"\n\n")+2;
      $parthead=trim(substr($data,0,$msgpos));
      $partbody=substr($data,$msgpos);
      $ret['head']=$parthead;
      $contenttype=getvar($parthead,"Content-Type");
      if($contenttype==false || $contenttype=="text/plain")
	  $spsource=array($partbody);
      else {
	  $boundary=getboundary($parthead);
	  if($boundary==false)
	    $spsource=array($partbody);
	  else
	    $spsource=explode($boundary,$partbody);
      }
      
      $i=0;
      $html=false;

      foreach($spsource as $part){
              $partprops=getpart($part);
              foreach($partprops as $partprop){
		    if($partprop['content-type']=="text/html"){
                        $html=true;
                        $ret['html'].=$partprop['message'];
                    }
                    if($partprop['content-type']=="text/plain" || !$partprop['content-type']) $ret['text'].=nl2br($partprop['message']);
                    if($partprop['name']!="") $ret[$i]['files'][$partprop['name']]=$partprop;
                    $i++;
              }
      }

      return $ret;

}


function getpart($part){
         $i=0;
         $part=trim($part);
         $msgpos=strpos($part,"\n\n")+2;
         $parthead=trim(substr($part,0,$msgpos));
	 $partbody=substr($part,$msgpos);
	 
         $content=getvar($parthead,"Content-Type");
         if($content==false){
	    $partbody=$part;
	    $parthead="";
	 }
	 
         $ret=array();
         $ret[$i]['content-type']=$content;

         if($content=="multipart/alternative" || $content=="multipart/related"){
             $boundary=getboundary($parthead);
             $sppart=explode($boundary,$partbody);

             foreach($sppart as $subpart){
                  //print "<part id=".$i.">".$subpart."</part>";
                  $subdata=getpart($subpart);
                  if($subdata!=false){
		    foreach($subdata as $subdata2) {
			$ret[$i]['name']=$subdata2['name'];
			$ret[$i]['message']=decode($subdata2['head'],$subdata2['message']);
			$ret[$i]['content-type']=$subdata2['content-type'];
			$i++;
		    }
		  }
             }
             return $ret;
         }else {
	     $ret[$i]['message']=decode($parthead,$partbody);
// 	     $ret[$i]['message']=$partbody;
	     $ret[$i]['head']=$parthead;
	 }
         $npos=strpos($parthead,"name",$pvpos);
         $ret[$i]['name']="";
         if($npos>-1){
                  $npos=strpos($parthead,"=",$npos)+1;
                  $ctend=strpos($parthead,"\n",$npos);
                  $ret[$i]['name']=substr($parthead,$npos,$ctend-$npos);
                  $ret[$i]['name']=trim(str_replace("\"","",$ret[$i]['name']));
	 }

         return $ret;

}

function decode($head,$str) {
	 $encoding=getvar($head,"Content-Transfer-Encoding");

// var_dump($str);
         switch($encoding){

	    case "quoted-printable":
	    $str=quoted_printable_decode($str);
	    break;

	    case "base64":
	    $str=base64_decode($str);
	    break;
         }

         if(strpos(strtolower($head),"utf-8")>-1) $str=utf8_decode($str);
	 return $str;
}

function maildate($date){
         //Thu, 04 Jun 2009 21:18:43 +0200
         $date=trim($date);
	 $date=str_replace("  "," ",$date);

         $mois=array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
         if(strpos($date,",")>-1)
	    list($jsemaine, $jour, $nom_mois, $annee, $time) = explode(' ',$date);
	 else
	    list($jour, $nom_mois, $annee, $time) = explode(' ',$date);
         list($heure, $minute, $seconde) = explode(':',$time);

         return mktime(intval($heure), intval($minute), intval($seconde), intval($mois[$nom_mois]), abs($jour), abs($annee));

}

function emailaddress($data){
	$pattern="/([\s]*)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*([ ]+|)@([ ]+|)([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,}))([\s]*)/i";
	$ret=preg_match($pattern,$data,$m);
	return $m[0];
}

function getsubject($txt){
	$return=preg_match("#\=\?(.+)\?(.+)\?(.+)\?\=#",$txt,$matches);
	$ret="";
	if($return>0){
	    switch($matches[2]){
            case "Q":
            $ret=quoted_printable_decode($matches[3]);
            break;

            case "B":
            $ret=base64_decode($matches[3]);
            break;
	    }
	    if(strtoupper($matches[1])=="UTF-8") $ret=utf8_decode($ret);
	    return $ret;
	}else
	    return $txt;
}

?>