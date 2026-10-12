<?php

echo "<style type=\"text/css\">
.popperlink { POSITION: absolute; VISIBILITY: hidden }
</style>
<DIV class=popperlink id=topdecklink></DIV>
<SCRIPT language=\"JavaScript\">
domok = document.getElementById;

if (domok)
	{
	skn = document.getElementById(\"topdecklink\").style;
	}

function poplink(msg,ident){
var content =\"<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 BGCOLOR=#000000><TR><TD><TABLE WIDTH=100% BORDER=0 CELLPADDING=2 CELLSPACING=1><TR><TD BGCOLOR=#FFFFCC><FONT COLOR=#000000><CENTER>\"+msg+\"</CENTER></TD></TR></TABLE></TD></TR></TABLE>\";
	if (domok)
		{
	  	document.getElementById(\"topdecklink\").innerHTML = content;
	  	skn.visibility = \"visible\";
		get_mouse;
  		}
}

function get_mouse(e){
	var x = (navigator.appName.substring(0,3) == \"Net\") ? e.pageX : event.x+document.body.scrollLeft;
	var y = (navigator.appName.substring(0,3) == \"Net\") ? e.pageY : event.y+document.body.scrollTop;
	skn.left = x + 30;
	skn.top = y + 15;
	}
function killlink(){
	if (domok)
  		document.getElementById(\"topdecklink\").innerHTML=\"\";
		skn.visibility = \"hidden\";
	}
</SCRIPT>";

?>