<?php

class xlexport{

    var $name="export";
    var $buffer;
    var $csv=false;
    var $csvsep=",";
    var $striptags=true;
    var $writeauto=false;

    function xlexport($type="xls",$name="export",$writeauto=false){
        $this->name=$name;
        $this->writeauto=$writeauto;
        if($type=="csv") $this->csv=true;
        
        $ext="xls";
        if($this->csv) $ext="csv";
        
        if($this->writeauto){
            header("Content-disposition: attachment; filename=\"".$this->name.".".$ext."\"");
            header("Content-Type: application/force-download");
            header("Content-Transfer-Encoding: text/html;charset=UTF-8\n"); // Surtout ne pas enlever le \n
            header("Pragma: no-cache");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
        }

        if(!$this->csv) 
            $this->buffer = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
            <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
            <html>
                <head>
                    <meta http-equiv=\"Content-type\" content=\"text/html;charset=UTF-8\" />
                </head>
                <body>
                    <table x:str border=0 cellpadding=0 cellspacing=0 width=100% style=\"border-collapse: collapse\">\n";
    }

    function addrow($cols, $quotes = true, $fnac = false, $point = false, $utf8_decode = false){
        if($this->csv){
            $line="";
            foreach($cols as $num => $c){
    		    if($this->striptags) $c=strip_tags($c);
                $c=str_replace("\n","",$c);
                $c=str_replace("\r","",$c);
                $c=html_entity_decode($c,ENT_QUOTES,"UTF-8");
                if($utf8_decode) $c=utf8_decode($c);
                if(!$fnac || ($fnac && $num != 18)) $c=trim($c);
                $c=str_replace("\"","",$c);
                if(is_numeric($c) && !$point) $c=str_replace(".",",",$c);
                $line .= $this->csvsep;
                if($quotes) $line .= "\"";
                $line .= $c;
                if($quotes) $line .= "\"";
            }
            if($this->buffer!="" && $this->buffer != chr(239) . chr(187) . chr(191)) $this->buffer.="\n";
            $this->buffer.=substr($line,1);
            
            if($this->writeauto)
                print substr($line,1)."\n";
            
        }else{
            $this->buffer.="<tr>";
            foreach($cols as $c)
                $this->buffer.="<td>".strip_tags($c)."</td>";

            $this->buffer.="</tr>\n";
        }
        //nice($line);
    }

    function export(){
        if(!$this->csv) $this->buffer.="</table>
        </body>
        </html>";

        $ext="xls";
        if($this->csv) $ext="csv";
        $file_size=strlen($this->buffer);
		
		if(!$this->writeauto){
			header("Content-disposition: attachment; filename=\"".$this->name.".".$ext."\"");
			header("Content-Type: application/force-download");
			header("Content-Transfer-Encoding: text/html;charset=UTF-8\n"); // Surtout ne pas enlever le \n
			header("Pragma: no-cache");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
			header("Content-Length: $file_size");
			header("Expires: 0");
			print $this->buffer;
		}
    }

    function write($path){
    	$ext="xls";
        if($this->csv) $ext="csv";
    	$f=fopen($path."/".$this->name.".".$ext,"c");
    	if(!$f) return false;
    	fputs($f,$this->buffer);
    	fclose($f);
    	return $path."/".$this->name.".".$ext;
    }
}

?>