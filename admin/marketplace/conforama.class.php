<?php

class conforama {

    private $Authorization="2ed2c8dd-29ef-4cbb-9ca1-a1a4b65addd3";
    
    function __construct(){
    
    }
    
    function sendInfos($methode, $getparams=array(), $postfields = array()){
        
        $oCurl = curl_init();
        
        $get="";
        if(count($getparams)>0) $get="?".http_build_query($getparams);

        curl_setopt( $oCurl, CURLOPT_URL, 'https://conforama-prod.mirakl.net/api/'.$methode.$get  );

        //Authentification
        curl_setopt( $oCurl, CURLOPT_HTTPHEADER, array("Authorization: ".$this->Authorization) );

        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, true );

        if(count($postfields) > 0){
            curl_setopt( $oCurl, CURLOPT_POST, 1);
            curl_setopt( $oCurl, CURLOPT_POSTFIELDS, $postfields );
        }

        $sCurlResponse = curl_exec( $oCurl );
        $sCurlResponse = json_decode($sCurlResponse);

        return $sCurlResponse;
    }

    function orders_bydate($from,$to){
    
        $return=array();
        
        $dt=new datetime();
        $dt->modify("1 month ago");
        $start_date=$dt->format("c");
        if(is_a($from,"datetime")) 
            $start_date=$from->format("c");
        elseif(is_int($from))
            $start_date=date("c",$from);
            
        $end_date=date("c");
        if(is_a($to,"datetime")) 
            $end_date=$to->format("c");
        elseif(is_int($to))
            $end_date=date("c",$to);
            
        $response=$this->sendInfos("orders",array("start_date"=>$start_date,"end_date"=>$end_date));
        
        if($response->total_count>0){
            return $response->orders;
        }
        return $return;
    }
    
    function order($orderid){
        return $this->sendInfos("orders",array("order_ids"=>$orderid));
    }
}



?>
