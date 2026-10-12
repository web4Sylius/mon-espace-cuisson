/**
 * Code Syntax Highlighter. Version 1.0.4
 * Copyright (C) 2004 Dream Projections Inc.
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 **
 * Usage example:
 *
 * <script src="dpSyntaxHighlighter.js" language="javascript"></script>
 *
 * <textarea name="code" language="html">
 * <img src="myimage.gif" border="0">
 * </textarea>
 *
 * <script>dp.SyntaxHighlighter.HighlightAll('code', 'value');</script>
 *
 **
 * History:
 *
 * 1.0.4 - February 2nd, 2005
 *    - added Delphi & Python
 *    - multi-line comments fixed
 *    - language name can be set through w3c valid 'class' attribute
 *    - HighlightAll(name, [showGutter], [showTools])
 *
 * 1.0.3 - December 31th, 2004 (added PHP & SQL)
 * 1.0.2 - December 28th, 2004 (refactoring with namespaces)
 * 1.0.1 - December 14th, 2004
 * 1.0.0 - November 13th, 2004
 */

// create namespaces
var dp = {
	SyntaxHighlighter :			// dp.SyntaxHighlighter
	{
			Utils	: {},		// dp.SyntaxHighlighter.Utils
			Brushes	: {}		// dp.SyntaxHighlighter.Brushes
	}
};

dp.SyntaxHighlighter.Config = {
	Version	: '1.0.4',
	About	: '<html><head><title>About...</title></head><body class="dp-about"><table cellspacing="0"><tr><td class="copy"><div class="para title">dp.SyntaxHighlighter</div><div class="para">Version: {V}</div><div class="para"><a href="http://www.dreamprojections.com/syntaxhighlighter/?ref=about" target="_blank">http://www.dreamprojections.com/syntaxhighlighter</a></div>&copy;2004-2005 Dream Projections Inc. All right reserved.</td></tr><tr><td class="footer"><input type="button" class="close" value="OK" onClick="window.close()"/></td></tr></table></body></html>'
};





// opens a new windows and puts the original unformatted source code inside.
dp.SyntaxHighlighter.Utils.ViewSource = function(sender)
{
	var code = sender.parentNode.originalCode;
	var wnd = window.open('', '_blank', 'width=750, height=400, location=0, resizable=1, menubar=0, scrollbars=1');
	
	code = code.replace(/</g, '&lt;');
	
	wnd.document.write('<pre>' + code + '</pre>');
	wnd.document.close();
}

// copies the original source code in to the clipboard (IE only)
dp.SyntaxHighlighter.Utils.ToClipboard = function(sender)
{
	var code = sender.parentNode.originalCode;
	
	// This works only for IE. There's a way to make it work with Mozilla as well,
	// but it requires security settings changed on the client, which isn't by
	// default, so 99% of users won't have it working anyways.
	if(window.clipboardData)
	{
		window.clipboardData.setData('text', code);
		
		alert('The code is in your clipboard now.');
	}
}

// creates an invisible iframe, puts the original source code inside and prints it
dp.SyntaxHighlighter.Utils.PrintSource = function(sender)
{
	var td		= sender.parentNode;
	var code	= td.processedCode;
	var iframe	= document.createElement('IFRAME');
	var doc		= null;
	var wnd		= 

	// this hides the iframe
	iframe.style.cssText = 'position:absolute; width:0px; height:0px; left:-5px; top:-5px;';
	
	td.appendChild(iframe);
	
	doc		= iframe.contentWindow.document;
	code	= code.replace(/</g, '&lt;');
	
	doc.open();
	doc.write('<pre>' + code + '</pre>');
	doc.close();
	
	iframe.contentWindow.focus();
	iframe.contentWindow.print();
	
	td.removeChild(iframe);
}

dp.SyntaxHighlighter.Utils.About = function()
{
	var wnd	= window.open('', '_blank', 'dialog, width=320, height=150');
	var doc	= wnd.document;
	
	var styles	= document.getElementsByTagName('style');
	var links	= document.getElementsByTagName('link');
	
	doc.write(dp.SyntaxHighlighter.Config.About.replace('{V}', dp.SyntaxHighlighter.Config.Version));
	
	// copy over ALL the styles from the parent page
	for(var i = 0; i < styles.length; i++)
		doc.write('<style>' + styles[i].innerHTML + '</style>');

	for(var i = 0; i < links.length; i++)
		if(links[i].rel.toLowerCase() == 'stylesheet')
			doc.write('<link type="text/css" rel="stylesheet" href="' + links[i].href + '"></link>');
	
	doc.close();
	wnd.focus();
}





// creates a new match object
dp.SyntaxHighlighter.Match = function(value, index, css)
{
	this.value		= value;
	this.index		= index;
	this.length		= value.length;
	this.css		= css;
}









dp.SyntaxHighlighter.Highlighter = function()
{
	this.addGutter		= true;
	this.addControls	= true;
	this.tabsToSpaces	= true;
}

// static callback for the match sorting
dp.SyntaxHighlighter.Highlighter.SortCallback = function(m1, m2)
{
	// sort matches by index first
	if(m1.index < m2.index)
		return -1;
	else if(m1.index > m2.index)
		return 1;
	else
	{
		// if index is the same, sort by length
		if(m1.length < m2.length)
			return -1;
		else if(m1.length > m2.length)
			return 1;
	}
	return 0;
}

// gets a list of all matches for a given regular expression
dp.SyntaxHighlighter.Highlighter.prototype.GetMatches = function(regex, css)
{
	var index = 0;
	var match = null;

	while((match = regex.exec(this.code)) != null)
	{
		this.matches[this.matches.length] = new dp.SyntaxHighlighter.Match(match[0], match.index, css);
	}
}

dp.SyntaxHighlighter.Highlighter.prototype.AddBit = function(str, css)
{
	var span = document.createElement('span');
	
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/ /g, '&nbsp;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/\n/gm, '&nbsp;<br>');

	if(css != null)
	{
		var regex = new RegExp('<br>', 'gi');
		
		if(regex.test(str))
		{
			var lines = str.split('&nbsp;<br>');
			
			str = '';
			
			for(var i = 0; i < lines.length; i++)
			{
				span			= document.createElement('SPAN');
				span.className	= css;
				span.innerHTML	= lines[i];
				
				this.div.appendChild(span);
				
				// don't add a <BR> for the last line
				if(i + 1 < lines.length)
				{
					this.div.appendChild(document.createElement('BR'));
				}
			}
		}
		else
		{
			span.className = css;
			span.innerHTML = str;
			this.div.appendChild(span);
		}
	}
	else
	{
		span.innerHTML = str;
		this.div.appendChild(span);
	}
}

// checks if one match is inside another
dp.SyntaxHighlighter.Highlighter.prototype.IsInside = function(match)
{
	if(match == null || match.length == 0)
	{
		return;
	}
	
	for(var i = 0; i < this.matches.length; i++)
	{
		var c = this.matches[i];
		
		if(c == null)
		{
			continue;
		}
		
		if((match.index > c.index) && (match.index <= c.index + c.length))
		{
			return true;
		}
	}
	
	return false;
}

dp.SyntaxHighlighter.Highlighter.prototype.ProcessRegexList = function()
{
	for(var i = 0; i < this.regexList.length; i++)
	{
		this.GetMatches(this.regexList[i].regex, this.regexList[i].css);
	}
}

dp.SyntaxHighlighter.Highlighter.prototype.ProcessSmartTabs = function(code)
{
	var lines	= code.split('\n');
	var result	= '';
	var tabSize	= 4;
	var tab		= '\t';

	// This function inserts specified amount of spaces in the string
	// where a tab is while removing that given tab. 
	function InsertSpaces(line, pos, count)
	{
		var left	= line.substr(0, pos);
		var right	= line.substr(pos + 1, line.length);	// pos + 1 will get rid of the tab
		var spaces	= '';
		
		for(var i = 0; i < count; i++)
		{
			spaces += ' ';
		}
		
		return left + spaces + right;
	}

	// This function process one line for 'smart tabs'
	function ProcessLine(line, tabSize)
	{
		if(line.indexOf(tab) == -1)
		{
			return line;
		}

		var pos = 0;

		while((pos = line.indexOf(tab)) != -1)
		{
			// This is pretty much all there is to the 'smart tabs' logic.
			// Based on the position within the line and size of a tab, 
			// calculate the amount of spaces we need to insert.
			var spaces = tabSize - pos % tabSize;
			
			line = InsertSpaces(line, pos, spaces);
		}
		
		return line;
	}

	// Go through all the lines and do the 'smart tabs' magic.
	for(var i = 0; i < lines.length; i++)
	{
		var line = lines[i];
		result += ProcessLine(line, tabSize) + '\n';
	}
	
	return result;
}

dp.SyntaxHighlighter.Highlighter.prototype.SwitchToTable = function()
{
	var lines	= this.div.innerHTML.split(/<br>/gi);
	var row		= null;
	var cell	= null;
	var html	= '';
	var pipe	= ' | ';

	// creates an anchor to a utility
	function UtilHref(util, text)
	{
		return '<a href="#" onclick="dp.SyntaxHighlighter.Utils.' + util + '(this); return false;">' + text + '</a>';
	}
	
	row = this.table.insertRow(-1);
	
	if(this.addGutter == true)
	{
		cell			= row.insertCell(-1);
		cell.className	= 'tools-corner';
	}

	if(this.addControls == true)
	{
		cell			= row.insertCell(-1);
		
		cell.originalCode	= this.originalCode;
		cell.processedCode	= this.code;
		
		cell.className		= 'tools';
		cell.innerHTML		= UtilHref('ViewSource', 'view plain') + pipe + UtilHref('PrintSource', 'print');
		
		if(window.clipboardData)
		{
			cell.innerHTML += pipe + UtilHref('ToClipboard', 'copy to clipboard');
		}
		
		cell.innerHTML += pipe + UtilHref('About', '?');
	}
	
	for(var i = 0; i < lines.length - 1; i++)
	{
		row = this.table.insertRow(-1);
		
		if(this.addGutter == true)
		{
			cell			= row.insertCell(-1);
			cell.className	= 'gutter';
			cell.innerHTML	= i + 1;
		}

		cell			= row.insertCell(-1);
		cell.className	= 'line';
		cell.innerHTML	= lines[i];
	}
	
	this.div.innerHTML	= '';
}

dp.SyntaxHighlighter.Highlighter.prototype.Highlight = function(code)
{
	// This function strips all new lines and spaces
	// from the beging and end of the string .
	function Trim(str)
	{
		var begining	= new RegExp('^[\\s\\n]', 'g');
		var end			= new RegExp('[\\s\\n]$', 'g');

		while(begining.test(str))
		{
			str = str.substr(1);
		}

		while(end.test(str))
		{
			str = str.substr(0, str.length - 1);
		}
		
		return str;
	}
	
	// This function returns a portions of the string 
	// from pos1 to pos2 inclusive.
	function Copy(string, pos1, pos2)
	{
		return string.substr(pos1, pos2 - pos1);
	}

	var pos	= 0;
	
	this.originalCode	= code;
	this.code			= Trim(code);
	this.div			= document.createElement('DIV');
	this.table			= document.createElement('TABLE');
	this.matches		= new Array();
	
	if(this.CssClass != null)
	{
		this.table.className = this.CssClass;
	}

	// replace tabs with spaces
	if(this.tabsToSpaces == true)
	{
		this.code = this.ProcessSmartTabs(this.code);
	}

	this.table.border		= 0;
	this.table.cellSpacing	= 0;
	this.table.cellPadding	= 0;

	this.ProcessRegexList();	

	// if no matches found, do nothing
	if(this.matches.length == 0)
	{
		return;
	}

	// sort the matches
	this.matches = this.matches.sort(dp.SyntaxHighlighter.Highlighter.SortCallback);

	// The following loop checks to see if any of the matches are inside
	// of other matches. This process would get rid of highligting strings
	// inside comments, keywords inside strings and so on.
	for(var i = 0; i < this.matches.length; i++)
	{
		if(this.IsInside(this.matches[i]))
		{
			this.matches[i] = null;
		}
	}

	// Finally, go through the final list of matches and pull the all
	// together adding everything in between that isn't a match.
	for(var i = 0; i < this.matches.length; i++)
	{
		var match = this.matches[i];

		if(match == null || match.length == 0)
		{
			continue;
		}
		
		this.AddBit(Copy(this.code, pos, match.index), null);
		this.AddBit(match.value, match.css);
		
		pos = match.index + match.length;
	}
	
	this.AddBit(this.code.substr(pos), null);
	
	this.SwitchToTable();	
}

dp.SyntaxHighlighter.Highlighter.prototype.GetKeywords = function(str) 
{
	return '\\b' + str.replace(/ /g, '\\b|\\b') + '\\b';
}

// highlightes all elements identified by name and gets source code from specified property
dp.SyntaxHighlighter.HighlightAll = function(name, showGutter /* optional */, showControls /* optional */)
{
	var elements		= document.getElementsByName(name);
	var highlighter		= null;
	var registered		= new Object();
	var propertyName	= 'value';
	
	if(elements == null)
	{
		return;
	}

	// if showGutter isn't set, default to TRUE
	if(showGutter == null)
	{
		showGutter = true;
	}
	
	// if showControls isn't set, default to TRUE
	if(showControls == null)
	{
		showControls = true;
	}

	// register all brushes
	for(var brush in dp.SyntaxHighlighter.Brushes)
	{
		var aliases = dp.SyntaxHighlighter.Brushes[brush].Aliases;
		
		if(aliases == null)
		{
			continue;
		}
		
		for(var i = 0; i < aliases.length; i++)
		{
			registered[aliases[i]] = brush;
		}
	}
	
	for(var i = 0; i < elements.length; i++)
	{
		var element		= elements[i];
		var language	= element.attributes['language'];

		// seems like in IE, language attribute is returned even if it's not set,
		// so checking for an empty value will catch that problem
		if(language == null || language.value == '')
		{
			// allow to have w3c valid code by putting language name into the 'class' attribute
			language = element.attributes['class'];

			if(language == null)
				continue;
		}
		
		language = language.value.toLowerCase();
		
		if(registered[language] == null)
		{
			continue;
		}
		
		// instantiate a brush
		highlighter = new dp.SyntaxHighlighter.Brushes[registered[language]]();
		
		// hide the original element
		element.style.display = 'none';

		highlighter.addGutter	= showGutter;
		highlighter.addControls	= showControls;
		highlighter.Highlight(element[propertyName]);
		
		// place the result table inside a div
		var div = document.createElement('DIV');
		
		div.className = 'dp-highlighter';
		div.appendChild(highlighter.table);

		element.parentNode.insertBefore(div, element);		
	}
}






dp.SyntaxHighlighter.Brushes.CSharp = function()
{
	var keywords =	'abstract as base bool break byte case catch char checked class const ' +
					'continue decimal default delegate do double else enum event explicit ' +
					'extern false finally fixed float for foreach get goto if implicit in int ' +
					'interface internal is lock long namespace new null object operator out ' +
					'override params private protected public readonly ref return sbyte sealed set ' +
					'short sizeof stackalloc static string struct switch this throw true try ' +
					'typeof uint ulong unchecked unsafe ushort using virtual void while';

	this.regexList = [
		// There's a slight problem with matching single line comments and figuring out
		// a difference between // and ///. Using lookahead and lookbehind solves the
		// problem, unfortunately JavaScript doesn't support lookbehind. So I'm at a 
		// loss how to translate that regular expression to JavaScript compatible one.
//		{ regex: new RegExp('(?<!/)//(?!/).*$|(?<!/)////(?!/).*$|/\\*[^\\*]*(.)*?\\*/', 'gm'),	css: 'comment' },			// one line comments starting with anything BUT '///' and multiline comments
//		{ regex: new RegExp('(?<!/)///(?!/).*$', 'gm'),											css: 'comments' },		// XML comments starting with ///

		{ regex: new RegExp('//.*$', 'gm'),							css: 'comment' },			// one line comments
		{ regex: new RegExp('/\\*[\\s\\S]*?\\*/', 'g'),				css: 'comment' },			// multiline comments
		{ regex: new RegExp('"(?:\\.|[^\\""])*"', 'g'),				css: 'string' },			// strings
		{ regex: new RegExp('^\\s*#.*', 'gm'),						css: 'preprocessor' },		// preprocessor tags like #region and #endregion
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' }			// c# keyword
		];

	this.CssClass = 'dp-c';
}

dp.SyntaxHighlighter.Brushes.CSharp.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.CSharp.Aliases		= ['c#', 'c-sharp', 'csharp'];






dp.SyntaxHighlighter.Brushes.Php = function()
{
	var keywords =	'and or xor __FILE__ __LINE__ array as break case ' +
					'cfunction class const continue declare default die do echo else ' +
					'elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exit ' +
					'extends for foreach function global if include include_once isset list ' +
					'new old_function print require require_once return static switch unset use ' +
					'var while __FUNCTION__ __CLASS__';

	this.regexList = [
		{ regex: new RegExp('//.*$', 'gm'),							css: 'comment' },			// one line comments
		{ regex: new RegExp('/\\*[\\s\\S]*?\\*/', 'g'),				css: 'comment' },			// multiline comments
		{ regex: new RegExp('\\$\\w+', 'g'),						css: 'vars' },				// variables
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' }			// keyword
		];

	this.CssClass = 'dp-c';
}

dp.SyntaxHighlighter.Brushes.Php.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Php.Aliases	= ['php'];





/* Edited by Rodolphe Breton */
dp.SyntaxHighlighter.Brushes.JScript = function()
{
	var keywords =    	'abstract boolean break byte case catch char class const continue debugger ' +
				    	'default delete do double else enum export extends false final finally float ' +
				    	'for function goto if implements import in instanceof int interface long native ' +
					    'new null package private protected public return short static super switch ' +
					    'synchronized this throw throws transient true try typeof var void volatile while with';

	var commonlibs =	'Array Date Function Image Math navigator Object RegExp String window';

	var builtins =		'ActiveXObject alert createPopup document event onreadystatechange open readyState ' +
						'responseText responseXML send setRequestHeader status XMLHttpRequest';

	this.regexList = [
		{ regex: new RegExp('//.*$', 'gm'),							css: 'comment' },			// one line comments
		{ regex: new RegExp('/\\*[\\s\\S]*?\\*/', 'g'),				css: 'comment' },			// multiline comments
		{ regex: new RegExp('"(?:\\.|[^\\""])*"', 'g'),				css: 'string' },			// strings
		{ regex: new RegExp('\'(?:\\.|[^\\\'\'])*\'', 'g'),			css: 'string' },			// strings
		{ regex: new RegExp('^\\s*#.*', 'gm'),						css: 'preprocessor' },		// preprocessor tags like #region and #endregion
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' },			// keywords
		{ regex: new RegExp(this.GetKeywords(builtins), 'gm'),		css: 'builtins' },			// builtin objects, functions, methods, magic attributes
		{ regex: new RegExp(this.GetKeywords(commonlibs), 'gm'),	css: 'commonlibs' }			// common standard library modules
		];

	this.CssClass = 'dp-jscript';
}

dp.SyntaxHighlighter.Brushes.JScript.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.JScript.Aliases	= ['js', 'jscript', 'javascript'];






dp.SyntaxHighlighter.Brushes.Vb = function()
{
	var keywords =	'AddHandler AddressOf AndAlso Alias And Ansi As Assembly Auto ' +
					'Boolean ByRef Byte ByVal Call Case Catch CBool CByte CChar CDate ' +
					'CDec CDbl Char CInt Class CLng CObj Const CShort CSng CStr CType ' +
					'Date Decimal Declare Default Delegate Dim DirectCast Do Double Each ' +
					'Else ElseIf End Enum Erase Error Event Exit False Finally For Friend ' +
					'Function Get GetType GoSub GoTo Handles If Implements Imports In ' +
					'Inherits Integer Interface Is Let Lib Like Long Loop Me Mod Module ' +
					'MustInherit MustOverride MyBase MyClass Namespace New Next Not Nothing ' +
					'NotInheritable NotOverridable Object On Option Optional Or OrElse ' +
					'Overloads Overridable Overrides ParamArray Preserve Private Property ' +
					'Protected Public RaiseEvent ReadOnly ReDim REM RemoveHandler Resume ' +
					'Return Select Set Shadows Shared Short Single Static Step Stop String ' +
					'Structure Sub SyncLock Then Throw To True Try TypeOf Unicode Until ' +
					'Variant When While With WithEvents WriteOnly Xor';

	this.regexList = [
		{ regex: new RegExp('\'.*$', 'gm'),							css: 'comment' },			// one line comments
		{ regex: new RegExp('"(?:\\.|[^\\""])*"', 'g'),				css: 'string' },			// strings
		{ regex: new RegExp('^\\s*#.*', 'gm'),						css: 'preprocessor' },		// preprocessor tags like #region and #endregion
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' }			// c# keyword
		];

	this.CssClass = 'dp-vb';
}

dp.SyntaxHighlighter.Brushes.Vb.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Vb.Aliases		= ['vb', 'vb.net'];








dp.SyntaxHighlighter.Brushes.Sql = function()
{
	var funcs	=	'abs avg case cast coalesce convert count current_timestamp ' +
					'current_user day isnull left lower month nullif replace right ' +
					'session_user space substring sum system_user upper user year';

	var keywords =	'absolute action add after alter as asc at authorization begin bigint ' +
					'binary bit by cascade char character check checkpoint close collate ' +
					'column commit committed connect connection constraint contains continue ' +
					'create cube current current_date current_time cursor database date ' +
					'deallocate dec decimal declare default delete desc distinct double drop ' +
					'dynamic else end end-exec escape except exec execute false fetch first ' +
					'float for force foreign forward free from full function global goto grant ' +
					'group grouping having hour ignore index inner insensitive insert instead ' +
					'int integer intersect into is isolation key last level load local max min ' +
					'minute modify move name national nchar next no numeric of off on only ' +
					'open option order out output partial password precision prepare primary ' +
					'prior privileges procedure public read real references relative repeatable ' +
					'restrict return returns revoke rollback rollup rows rule schema scroll ' +
					'second section select sequence serializable set size smallint static ' +
					'statistics table temp temporary then time timestamp to top transaction ' +
					'translation trigger true truncate uncommitted union unique update values ' +
					'varchar varying view when where with work';

	var operators =	'all and any between cross in join like not null or outer some';

	this.regexList = [
		{ regex: new RegExp('--(.*)$', 'gm'),						css: 'comment' },			// one line and multiline comments
		{ regex: new RegExp('"(?:\\.|[^\\""])*"', 'g'),				css: 'string' },			// strings
		{ regex: new RegExp('\'(?:\\.|[^\\\'\'])*\'', 'g'),			css: 'string' },			// strings
		{ regex: new RegExp(this.GetKeywords(funcs), 'gmi'),		css: 'func' },				// functions
		{ regex: new RegExp(this.GetKeywords(operators), 'gmi'),	css: 'op' },				// operators and such
		{ regex: new RegExp(this.GetKeywords(keywords), 'gmi'),		css: 'keyword' }			// keyword
		];

	this.CssClass = 'dp-sql';
}

dp.SyntaxHighlighter.Brushes.Sql.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Sql.Aliases	= ['sql'];








dp.SyntaxHighlighter.Brushes.Xml = function()
{
	this.CssClass = 'dp-xml';
}

dp.SyntaxHighlighter.Brushes.Xml.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Xml.Aliases	= ['xml', 'xhtml', 'xslt', 'html', 'xhtml'];

dp.SyntaxHighlighter.Brushes.Xml.prototype.ProcessRegexList = function()
{
	function push(array, value)
	{
		array[array.length] = value;
	}
	
	/* If only there was a way to get index of a group within a match, the whole XML
	   could be matched with the expression looking something like that:
	
	   (<!\[CDATA\[\s*.*\s*\]\]>)
	   | (<!--\s*.*\s*?-->)
	   | (<)*(\w+)*\s*(\w+)\s*=\s*(".*?"|'.*?'|\w+)(/*>)*
	   | (</?)(.*?)(/?>)
	*/
	var index	= 0;
	var match	= null;
	var regex	= null;

	// Match CDATA in the following format <![ ... [ ... ]]>
	// <\!\[[\w\s]*?\[(.|\s)*?\]\]>
	this.GetMatches(new RegExp('<\\!\\[[\\w\\s]*?\\[(.|\\s)*?\\]\\]>', 'gm'), 'cdata');
	
	// Match comments
	// <!--\s*.*\s*?-->
	this.GetMatches(new RegExp('<!--\\s*.*\\s*?-->', 'gm'), 'comments');

	// Match attributes and their values
	// (\w+)\s*=\s*(".*?"|\'.*?\'|\w+)*
	regex = new RegExp('([\\w-\.]+)\\s*=\\s*(".*?"|\'.*?\'|\\w+)*', 'gm');
	while((match = regex.exec(this.code)) != null)
	{
		push(this.matches, new dp.SyntaxHighlighter.Match(match[1], match.index, 'attribute'));
	
		// if xml is invalid and attribute has no property value, ignore it	
		if(match[2] != undefined)
		{
			push(this.matches, new dp.SyntaxHighlighter.Match(match[2], match.index + match[0].indexOf(match[2]), 'attribute-value'));
		}
	}

	// Match opening and closing tag brackets
	// </*\?*(?!\!)|/*\?*>
	this.GetMatches(new RegExp('</*\\?*(?!\\!)|/*\\?*>', 'gm'), 'tag');

	// Match tag names
	// </*\?*\s*(\w+)
	regex = new RegExp('</*\\?*\\s*([\\w-\.]+)', 'gm');
	while((match = regex.exec(this.code)) != null)
	{
		push(this.matches, new dp.SyntaxHighlighter.Match(match[1], match.index + match[0].indexOf(match[1]), 'tag-name'));
	}
}







/* Delphi brush is contributed by Eddie Shipman */
dp.SyntaxHighlighter.Brushes.Delphi = function()
{
	var keywords =	'abs addr and ansichar ansistring array as asm begin boolean byte cardinal ' +
					'case char class comp const constructor currency destructor div do double ' +
					'downto else end except exports extended false file finalization finally ' +
					'for function goto if implementation in inherited int64 initialization ' +
					'integer interface is label library longint longword mod nil not object ' +
					'of on or packed pansichar pansistring pchar pcurrency pdatetime pextended ' + 
					'pint64 pointer private procedure program property pshortstring pstring ' + 
					'pvariant pwidechar pwidestring protected public published raise real real48 ' +
					'record repeat set shl shortint shortstring shr single smallint string then ' +
					'threadvar to true try type unit until uses val var varirnt while widechar ' +
					'widestring with word write writeln xor';

	this.regexList = [
		{ regex: new RegExp('\\(\\*[\\s\\S]*?\\*\\)', 'gm'),		css: 'comment' },  			// multiline comments (* *)
		{ regex: new RegExp('{(?!\\$)[\\s\\S]*?}', 'gm'),			css: 'comment' },  			// multiline comments { }
		{ regex: new RegExp('//.*$', 'gm'),							css: 'comment' },  			// one line
		{ regex: new RegExp('\'(?:\\.|[^\\\'\'])*\'', 'g'),			css: 'string' },			// strings
		{ regex: new RegExp('\\{\\$[a-zA-Z]+ .+\\}', 'g'),			css: 'directive' },			// Compiler Directives and Region tags
		{ regex: new RegExp('\\b[\\d\\.]+\\b', 'g'),				css: 'number' },			// numbers 12345
		{ regex: new RegExp('\\$[a-zA-Z0-9]+\\b', 'g'),				css: 'number' },			// numbers $F5D3
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' }			// keyword
		];

	this.CssClass = 'dp-delphi';
}

dp.SyntaxHighlighter.Brushes.Delphi.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Delphi.Aliases		= ['delphi', 'pascal'];







/* Python 2.3 syntax contributed by Gheorghe Milas */
dp.SyntaxHighlighter.Brushes.Python = function()
{
	var keywords =		'and assert break class continue def del elif else except exec ' +
						'finally for from global if import in is lambda not or object pass print ' +
						'raise return try yield while';
	
	var builtins =		'self __builtin__ __dict__ __future__ __methods__ __members__ __author__ __email__ __version__' +
						'__class__ __bases__ __import__ __main__ __name__ __doc__ __self__ __debug__ __slots__ ' +
						'abs append apply basestring bool buffer callable chr classmethod clear close cmp coerce compile complex ' +
						'conjugate copy count delattr dict dir divmod enumerate Ellipsis eval execfile extend False file fileno filter float flush ' +
						'get getattr globals has_key hasarttr hash hex id index input insert int intern isatty isinstance isubclass ' +
						'items iter keys len list locals long map max min mode oct open ord pop pow property range ' +
						'raw_input read readline readlines reduce reload remove repr reverse round seek setattr slice sum ' +
						'staticmethod str super tell True truncate tuple type unichr unicode update values write writelines xrange zip';
	
	var magicmethods =	'__abs__ __add__ __and__ __call__ __cmp__ __coerce__ __complex__ __concat__ __contains__ __del__ __delattr__ __delitem__ ' +
						'__delslice__ __div__ __divmod__ __float__ __getattr__ __getitem__ __getslice__ __hash__ __hex__ __eq__ __le__ __lt__ __gt__ __ge__ ' +
						'__iadd__ __isub__ __imod__ __idiv__ __ipow__ __iand__ __ior__ __ixor__ __ilshift__ __irshift__ ' +
						'__invert__ __init__ __int__ __inv__ __iter__ __len__ __long__ __lshift__ __mod__ __mul__ __new__ __neg__ __nonzero__ __oct__ __or__ ' +
						'__pos__ __pow__ __radd__ __rand__ __rcmp__ __rdiv__ __rdivmod__ __repeat__ __repr__ __rlshift__ __rmod__ __rmul__ ' +
						'__ror__ __rpow__ __rrshift__ __rshift__ __rsub__ __rxor__ __setattr__ __setitem__ __setslice__ __str__ __sub__ __xor__';
	
	var exceptions =	'Exception StandardError ArithmeticError LookupError EnvironmentError AssertionError AttributeError EOFError ' +
						'FutureWarning IndentationError OverflowWarning PendingDeprecationWarning ReferenceError RuntimeWarning ' +
						'SyntaxWarning TabError UnicodeDecodeError UnicodeEncodeError UnicodeTranslateError UserWarning Warning ' +
						'IOError ImportError IndexError KeyError KeyboardInterrupt MemoryError NameError NotImplementedError OSError ' +
						'RuntimeError StopIteration SyntaxError SystemError SystemExit TypeError UnboundLocalError UnicodeError ValueError ' +
						'FloatingPointError OverflowError WindowsError ZeroDivisionError';
	
	var types =			'NoneType TypeType IntType LongType FloatType ComplexType StringType UnicodeType BufferType TupleType ListType ' +
						'DictType FunctionType LambdaType CodeType ClassType UnboundMethodType InstanceType MethodType BuiltinFunctionType BuiltinMethodType ' +
						'ModuleType FileType XRangeType TracebackType FrameType SliceType EllipsisType';
	
	var commonlibs =	'anydbm array asynchat asyncore AST base64 binascii binhex bisect bsddb buildtools bz2 ' +
						'BaseHTTPServer Bastion calendar cgi cmath cmd codecs codeop commands compiler copy copy_reg ' +
						'cPickle crypt cStringIO csv curses Carbon CGIHTTPServer ConfigParser Cookie datetime dbhash ' +
						'dbm difflib dircache distutils doctest DocXMLRPCServer email encodings errno exceptions fcntl ' +
						'filecmp fileinput ftplib gc gdbm getopt getpass glob gopherlib gzip heapq htmlentitydefs ' +
						'htmllib httplib HTMLParser imageop imaplib imgfile imghdr imp inspect itertools jpeg keyword ' +
						'linecache locale logging mailbox mailcap marshal math md5 mhlib mimetools mimetypes mimify mmap ' +
						'mpz multifile mutex MimeWriter netrc new nis nntplib nsremote operator optparse os parser pickle pipes ' +
						'popen2 poplib posix posixfile pprint preferences profile pstats pwd pydoc pythonprefs quietconsole ' +
						'quopri Queue random re readline resource rexec rfc822 rgbimg sched select sets sgmllib sha shelve shutil ' +
						'signal site smtplib socket stat statcache string struct symbol sys syslog SimpleHTTPServer ' +
						'SimpleXMLRPCServer SocketServer StringIO tabnanny tarfile telnetlib tempfile termios textwrap ' +
						'thread threading time timeit token tokenize traceback tty types Tkinter unicodedata unittest ' +
						'urllib urllib2 urlparse user UserDict UserList UserString warnings weakref webbrowser whichdb ' +
						'xml xmllib xmlrpclib xreadlines zipfile zlib';

	this.regexList = [
		{ regex: new RegExp('#.*$', 'gm'),								css: 'comment' },			// comments
		{ regex: new RegExp('^\\s*"""(.|\n)*?"""\\s*$', 'gm'),			css: 'docstring' },			// documentation string "
		{ regex: new RegExp('^\\s*\'\'\'(.|\n)*?\'\'\'\\s*$', 'gm'),	css: 'docstring' },			// documentation string '
		{ regex: new RegExp('"""(.|\n)*?"""', 'g'),						css: 'string' },			// multi-line strings "
		{ regex: new RegExp('\'\'\'(.|\n)*?\'\'\'', 'g'),				css: 'string' },			// multi-line strings '
		{ regex: new RegExp('"(?:\\.|[^\\""])*"', 'g'),					css: 'string' },			// strings "
		{ regex: new RegExp('\'(?:\\.|[^\\\'\'])*\'', 'g'),				css: 'string' },			// strings '
		{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),			css: 'keyword' },			// keywords
		{ regex: new RegExp(this.GetKeywords(builtins), 'gm'),			css: 'builtins' },			// builtin objects, functions, methods, magic attributes
		{ regex: new RegExp(this.GetKeywords(magicmethods), 'gm'),		css: 'magicmethods' },		// special methods
		{ regex: new RegExp(this.GetKeywords(exceptions), 'gm'),		css: 'exceptions' },		// standard exception classes
		{ regex: new RegExp(this.GetKeywords(types), 'gm'),				css: 'types' },				// types from types.py
		{ regex: new RegExp(this.GetKeywords(commonlibs), 'gm'),		css: 'commonlibs' }			// common standard library modules
		];

	this.CssClass = 'dp-py';
}

dp.SyntaxHighlighter.Brushes.Python.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Python.Aliases		= ['py', 'python'];










/* Plain text brush is contributed by Rodolphe Breton */
dp.SyntaxHighlighter.Brushes.Text = function()
{
	this.regexList = [
		{ regex: new RegExp('.', 'g'),								css: 'all' }			// all
		];

	this.CssClass = 'dp-text';
}

dp.SyntaxHighlighter.Brushes.Text.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Text.Aliases   = ['text', 'plain-text','plain'];










/* Written by Rodolphe Breton */
dp.SyntaxHighlighter.Brushes.Css = function()
{
	var keywords =    	'absolute background background-color black border bottom clear center color display ' +
						'fixed float font-face font-family font-size font-style font-weight font height left ' +
						'list normal position relative right src text-align top url width';
						
	var elements =      'h1 h2 h3 h4 p pre textarea input div';
	
	var units =			'em px pt';

	this.regexList = [
		{ regex: new RegExp('/\\*[\\s\\S]*?\\*/', 'g'),				css: 'comment' },			// multiline comments
		/*{ regex: new RegExp(this.GetKeywords(keywords), 'gm'),		css: 'keyword' },*/			// keywords
		{ regex: new RegExp(this.GetKeywords(elements), 'gm'),		css: 'element' },			// elements
		{ regex: new RegExp(this.GetKeywords(units), 'gm'),			css: 'unit' }				// units
		];

	this.CssClass = 'dp-css';
}

dp.SyntaxHighlighter.Brushes.Css.prototype	= new dp.SyntaxHighlighter.Highlighter();
dp.SyntaxHighlighter.Brushes.Css.Aliases	= ['css'];
