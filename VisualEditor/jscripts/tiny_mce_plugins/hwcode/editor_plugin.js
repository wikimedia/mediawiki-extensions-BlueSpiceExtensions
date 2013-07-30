/**
 * VisualEditor extension
 * 
 * Wiki code to HTML and vice versa parser
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @version    1.20.0
 * @version    $Id: editor_plugin.js 9501 2013-05-23 21:00:39Z mglaser $
 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MagicWords are marked with apropriate css classes
 * v1.16
 * - special tags now can have line breaks
 * v1.15
 * - tinymce 3.4.9
 * - support for nested tables
 * - support for missing first row marker in table
 * - strike now works
 * - pres allow for classes
 * - all tests pass in ff and ie
 * v1.14
 * - preserves &nbsp;
 * - allow for entities in direct succession
 * v1.13
 * - nested templates
 * - links with templates
 * - most variables now with limited scope, should gain some performance
 * - support math tag
 * - restored umlaut support in images
 * v1.12
 * - restored empty cell handling in tables
 * - tinymce 3.3.9.1
 * - all tests pass in ff and ie
 * v1.11
 * - allows pre with spaces
 * - can handle templates with line breaks
 * v1.10.5
 * - prevent marking of entities in external links
 * v1.10.4
 * - do not tamper with line-breaks in pre
 * v1.10.3
 * - removed blockquote to div conversion because it could not handle nested blockquotes
 * v1.10.2
 * - improved single empty line handling for empty lines with only whitespace
 * - improved pre handling when containing newlines
 * - fixed bug in entity handling where consecutive entities would not be handled
 * - fixed bug where inline comments would not be rendered
 * v1.10.1
 * - moved check for unsupported tokens to tinymce.js
 * - added support for multiline comments
 * - added cleanup section at beginning of processing
 * v1.10.0
 * - support for simple definition lists
 * - comments are easier handleable
 * - htmlentities survive the roundtrip in original encoding
 * - headings at the end of text are correctly rendered
 * - empty pages do not have a linebreak
 * - linebreaks within paragraphs survive the roundtrip
 * - enter in headings produces paragraph
 * v1.9.4
 * - prerequisites are only checked on load
 * - infinite loops on p with multiline content are eliminated
 * v1.9.3
 * - improved pre handling
 * - improved blockquote handling
 * v1.9.2
 * - started indentation handling
 * - prevent infinite loops by better regexes
 * v1.9.1
 * - preserve special tags before pre in order to support GeSHi (indented text)
 * v1.9
 * - completely reworked the blocklevel engine
 * - refuse to be started with unsupported tokens
 * v1.8.4
 * - empty table cells are now being displayed
 * v1.8.3
 * - fixed center tags
 * - multiple empty lines are now possible
 * v1.8.2
 * - fixed various blockquote issues
 * - fixed comments
 */

/* Still open
Indents werden mit p erzeugt
check for .*? : .*? does not match newline, however, [\s\S] does.
parse categories to the end
*/

(function() {
	tinymce.create('tinymce.plugins.HWCodePlugin', {
		init : function(ed, url) {
			/**
			 * Reference to current editor
			 * @var TinyMCE TinyMCE object.
			 */
			var t = this;
			/**
			 * List of original pre tag content
			 * @var array Strings of the original content
			 */
			var _pres = false;
			/**
			 * List of original template content
			 * @var array Strings of the original content
			 */
			var _templates = false;
			/**
			 * List of original comment content
			 * @var array Strings of the original content
			 */
			var _comments = false;
			/**
			 * List of original comment content
			 * @var array Strings of the original content
			 */
			var _specialtags = false;
			
			/**
			 * List of original behavior switch content
			 * @var array Strings of the original content
			 */
			var _switches = false;
			
			var _thumbsizes = [ 
				'120',
				'150',
				'180',
				'200',
				'250',
				'300'
			];
			
			this._userThumbsize = mw.user ? mw.user.options.get('thumbsize') : 3; //Version switch and fallback for MW 1.17
			this._userThumbsize = _thumbsizes[this._userThumbsize];

			/**
			 * Initialize content. This function is some kind of preprocessor for Wiki to HTML conversion.
			 * @param TinyMCE ed Reference to TinyMCE instance
			 * @param object o Contains status information and the text that should be parsed.
			 */
			ed.onBeforeSetContent.add(function(ed, o) {
				BlueSpice.mouseWait(true);
				if (o.load) {
					//normalize line endings to \n
					o.content = o.content.replace(/\r\n/gmi, "\n");

					//cleanup tables
					o.content = o.content.replace(/(\{\|[^\n]*?)\n+/gmi, "$1\n");
					o.content = o.content.replace(/(\|-[^\n]*?)\n+/gmi, "$1\n");
					
					//cleanup old entity markers
					while (o.content.match(/<span class="hw_htmlentity">.+?<\/span>/gmi)) {
						o.content = o.content.replace(/(<span class="hw_htmlentity">)(.+?)(<\/span>)/gmi, '$2');
					}

					o.content = o.content.replace(/(<span class="hw_htmlentity">)/gmi, '');

					while (o.content.match(/(<span ([^>]*?)>)(\1)[^>]*?<\/span><\/span>/gmi)) {
						o.content = o.content.replace(/(<span [^>]*?>)(\1)([^>]*?)(<\/span>)<\/span>/gmi, '$1$3$4');
					}

					while (o.content.match(/<span class="toggletext">[\s\S]*?<\/span>/gmi)) {
						o.content = o.content.replace(/<span class="toggletext">([\s\S]*?)<\/span>/gmi, '$1');
					}

					//special tags before pres prevents spaces in special tags like GeSHi to take effect
					o.content = t['_preserveSpecialTags'](o.content);
					
					//cleanup linebreaks in tags except comments
					o.content = o.content.replace(/(<[^!][^>]+?)(\n)([^<]+?>)/gi, "$1$3");

					//preserve entities that were orignially html entities
					o.content = o.content.replace(/(&[^\s;]+;)/gmi, '<span class="hw_htmlentity">$1</span>');
					// remove replacement in external links. this must be done in a loop since there might be more
					// & in an url
					while ( o.content.match(/(\[[^\]]+?)<span class="hw_htmlentity">(.+?)<\/span>([^\]]+?])/gmi) ) {
						o.content = o.content.replace(/(\[[^\]]+?)<span class="hw_htmlentity">(.+?)<\/span>([^\]]+?])/gmi, '$1$2$3');
					}

					o.content = t['_convertPreWithSpacesToTinyMCE'](o.content);
					//preserve single linebreaks
					//text = text.replace(/(\S)\n(\s*[^\|\{#\*:<=\n-])/gmi, "$1<span class='single_linebreak'>.<\/span>$2");
					// Replacement in loop because regex is consuming $3 which is hence no match for next pattern
					// With single pass, every second line is not processed
					o.content = t['_preservePres'](o.content);
					//console.log(o.content);
					do {
						var linematch = false;
						//o.content = o.content.replace(/(^|\n)([^\n]+)($|(\n([^\n]{1,5})))/gmi, function($0,$1,$2,$3){
						o.content = o.content.replace(/(^|\n)([^\n]+)\n([^\n]{1,5})/gi, function($0,$1,$2,$3){
							//console.log($0);
							//console.log($2);
							//console.log($3);
							// hr table heading comment div end-table | ol ul dl dt comment cell row
							// there was hw_comment:@@@ in there: |@@@PRE.*?@@@$|\|$|hw_comment:@@@|^
							if ($2.match(/(----$|\|\}$|=$|-->$|<\/div>$|<\/pre>$|@@@PRE.*?@@@$|\|$|^(#|\*|:|;|<\!--|\|\||\|-)|(^\s*$))/i)) return $0;
							// careful: only considers the first 5 characters in a line
							if ($3.match(/(^(----|\||!|\{\||#|\*|:|;|=|<\!--|<div|<pre|@@@PR)|(^\s*$))/i)) return $0;
							linematch = true;
							return $1+$2+" <span class='single_linebreak' style='background-color:lightgray'>&para;<\/span> "+$3;
						});
					} while (linematch);
					//console.log(o.content);
					o.content = t['_recoverPres'](o.content);

				}
				o.content = t['_wiki2html'](o.content);

				BlueSpice.mouseWait(false);
			});

			/**
			 * Retrieve content. This function is some kind of preprocessor for HTML to Wiki conversion.
			 * @param TinyMCE ed Reference to TinyMCE instance
			 * @param object o Contains status information and the text that should be parsed.
			 */
			ed.onGetContent.add(function(ed, o) {
				BlueSpice.mouseWait(true);

				o.format = 'wiki';
				o.content = t['_html2wiki'](o.content);

				if (o.save) {
					o.content = t['_convertTinyMCEToPreWithSpaces'](o.content);
					//recover linebreaks
					// use . here: blank would not match in IE
					o.content = o.content.replace(/ ?<span[^>]*class="single_linebreak"[^>]*>(&nbsp;|.|&para;)<\/span> ?/g, "\n");

					o.content = t['_preserveEntities'](o.content);

					o.content = o.content.replace(/(&[^\s]*?;)/gmi, function($0) {
						return ed.dom.decode($0);
					});
					//do not use o.content = ed.dom.decode(o.content);
					// it breaks conversion from html to wiki
					o.content = t['_recoverEntities'](o.content);
					//cleanup entity markers
					while (o.content.match(/<span class="hw_htmlentity">.+?<\/span>/gmi)) {
						o.content = o.content.replace(/(<span class="hw_htmlentity">)(.+?)(<\/span>)/gmi, '$2');
					}

					o.content = t['_recoverSpecialTags'](o.content);

				}
				BlueSpice.mouseWait(false);
			});

		},

		getInfo : function() {
			return {
				longname : 'HWCode Plugin',
				author : 'Hallo Welt! - Medienwerkstatt GmbH / Markus Glaser',
				authorurl : 'http://www.hallowelt.biz',
				infourl : 'http://www.hallowiki.biz',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		/**
		 * Converts MW list markers to HTML list open tags
		 * @param string lastList Sequence of list markers in previous line
		 * @param string cur Sequence of list markers in current line
		 * @return string Sequence of open list tags.
		 */
		_openList: function(lastList, cur) {
			var listTags = '';
			for (var k=lastList.length; k<cur.length; k++) {
				switch (cur.charAt(k)) {
					case '*' :
						listTags = listTags + "<ul><li>";
						break;
					case '#' :
						listTags = listTags + '<ol><li>';
						break;
					case ';' :
						listTags = listTags + '<dl><dt>';
						break;
					case ':' :
						listTags = listTags + '<blockquote>';
						break;
				}
			}
			return listTags;
		},

		/**
		 * Converts MW list markers to HTML list item tags
		 * @param string lastList Sequence of list markers in previous line
		 * @param string cur Sequence of list markers in current line
		 * @return string Sequence of list item tags.
		 */
		_continueList: function(lastList, cur) {
			var listTags = '';
			var lastTag = lastList.charAt(lastList.length-1);
			var curTag = cur.charAt(cur.length-1);
			if (lastTag == curTag) {
				switch (lastTag) {
					case '*' :
					case '#' :
						listTags = '</li><li>';
						break;
					case ';' :
						listTags = listTags + '</dt><dt>';
						break;
					case ':' :
						listTags = '</blockquote><blockquote>';
						break;
				}
			} else {
				switch (lastTag) {
					case '*' :
						listTags = listTags + '</li></ul>';
						break;
					case '#' :
						listTags = listTags + '</li></ol>';
						break;
					case ';' :
						listTags = listTags + '</dt></dl>';
						break;
					case ':' :
						listTags = listTags + '</blockquote>';
						break;
				}
				switch (curTag) {
					case '*' :
						listTags = listTags + '<ul><li>';
						break;
					case '#' :
						listTags = listTags + '<ol><li>';
						break;
					case ';' :
						listTags = listTags + '<dl><dt>';
						break;
					case ':' :
						listTags = listTags + '<blockquote>';
						break;
				}
			}
			return listTags;
		},

		/**
		 * Converts MW list markers to HTML list end tags
		 * @param string lastList Sequence of list markers in previous line
		 * @param string cur Sequence of list markers in current line
		 * @return string Sequence of closing list tags.
		 */
		_closeList: function(lastList, cur) {
			var listTags = '';
			for (var k=lastList.length; k>cur.length; k--) {
				switch (lastList.charAt(k-1)) {
					case '*' :
						listTags = listTags + '</li></ul>';
						break;
					case '#' :
						listTags = listTags + '</li></ol>';
						break;
					case ';' :
						listTags = listTags + '</dt></dl>';
						break;
					case ':' :
						listTags = listTags + '</blockquote>';
						break;
				}
			}
			return listTags;
		},

		/**
		 * Converts MW block level elements to HTML. It also handles a lot of empty line behaviour. This is a very sensitive function. On any changes, make sure to run all the editor tests. 
		 * @param string text Current state of the text to be processed.
		 * @return string new state of the text to be processed.
		 */
		_blockLevels2html: function(text)
		{
			var lines = text.split('\n');
			var lastList = '';
			var inParagraph = false;
			var inBlock = false;
			var emptyline_first = false;
			var emptyline_count = 0;
			var afterBlock = false;
			var emptyline_before = false;
			var emptyline = false;
			var emptylineAfter = false;
			var l;

			for (var i=0; i<lines.length; i++) {
				// Prevent REDIRECT from being rendered as list
				if (l = lines[i].match(/^(\*|#(?!REDIRECT)|:|;)+/)) {
					lines[i] = lines[i].replace(/^(\*|#|:|;)*\s*(.*?)$/gmi, "$2");
					if (l[0].indexOf(':') == 0)
					{
						//if (l[0].length == lastList.length) lines[i] =  '</dd><dd>'+lines[i];
						if (l[0].length == lastList.length) lines[i] =  this._continueList(lastList, l[0])+lines[i]; //'</blockquote><blockquote>'+lines[i];
						if (l[0].length > lastList.length) lines[i] = this._openList(lastList, l[0])+lines[i];
						//if (l[0].length < lastList.length) lines[i] = _closeList(lastList, l[0])+'<dd>'+lines[i];
						if (l[0].length < lastList.length) lines[i] = this._closeList(lastList, l[0])+lines[i];
						lastList = l[0];
					}
					else
					{
						if (l[0].length == lastList.length) lines[i] =  this._continueList(lastList, l[0])+lines[i]; //'</li><li>'+lines[i];
						if (l[0].length > lastList.length) lines[i] = this._openList(lastList, l[0])+lines[i];
						if (l[0].length < lastList.length) lines[i] = this._closeList(lastList, l[0])+'<li>'+lines[i];
						lastList = l[0];
					}
					lastList = l[0];
					if (inParagraph)
					{
						lines[i]='</p>'+lines[i];
						inParagraph = false;
					}

				}
				else
				{
					emptyline = lines[i].match(/^(\s|&nbsp;)*$/);
					if (emptyline) emptyline_count++;
					else emptyline_count = 0;

					emptylineAfter = false;
					if (i < lines.length-1) emptylineAfter = lines[i+1].match(/^(\s|&nbsp;)*$/);

					if (lastList.length>0)
					{
						lines[i-1] = lines[i-1]+this._closeList(lastList, '');
						//lines[i] = +"\n"+lines[i];
						lastList = '';
						if (emptyline)
						{
							emptyline_before = true;
							continue;
						}
					}
					//}
					//else
					//{
					var openmatch = false;
					var closematch = false;
					var nlopenmatch = false;					//FS#133: Habe hier <td eingefügt

					openmatch = lines[i].match(/^(<table|<blockquote|<h1|<h2|<h3|<h4|<h5|<h6|<pre|@@@PRE|<tr|<td|<p|<div|<ul|<ol|<li|<\/tr|<\/td|<\/th|<hr)/gi);
					//Experimental: \*|#|:|{\| am Schluss verhindert, dass zwischen Blocklevels und Aufzählungen eine Leerzeile reinkommt.
					//if (i < lines.length-1) nlopenmatch = lines[i+1].match(/^(<table|<blockquote|<h1|<h2|<h3|<h4|<h5|<h6|<pre|<tr|<ul|<ol|<li|<div|<\/tr|<\/td|<\/th|<hr|\*|#|:|\{\|)/gi);
					//else nlopenmatch = false;
					// Achtung!! Habe gegenüber MW-Parser hier td und th und /table rausgenommen. Wenn das mal gut geht... Nachtrag: ist mom. obsolet
					closematch = lines[i].match(/(<\/blockquote|<\/h1|<\/h2|<\/h3|<\/h4|<\/h5|<\/h6|<\/?div|<hr|<\/pre|@@@PRE|<\/p|<\/li|<\/ul|<\/ol|<\/?center|<td|<th|<\/table)/gi);

					// hopefully temporary measure. divs with one or two empty lines in between are rendered correctly using these two variables
					var specialClosematchBefore = false;
					if (i > 0) specialClosematchBefore = lines[i-1].match(/(<\/div)/gi);
					var specialClosematchTwoBefore = false;
					if (i > 1) specialClosematchTwoBefore = lines[i-2].match(/(<\/div)/gi);

					// if a tag is in beforeBlock, an extra linebreak is inserted on second empty line.
					// use this, if lines seem to vanish
					// do not use hr here
					var beforeBlock = false;
					if (i < lines.length-1) beforeBlock = lines[i+1].match(/^(<blockquote|<ul|<ol|<h1|<h2|<h3|<h4|<h5|<h6|<pre|@@@PRE|<td|<table|<tr|<div|\*|#|:|\{\|)/gi);
					//if (i < lines.length-1) beforeBlock = lines[i+1].match(/^(<blockquote|<ul|<ol|<td|<table|<tr|\{\||\*|#|:)/gi);

					if (openmatch) inBlock = true;
					if (closematch) inBlock = false;

					/*
					if (emptyline && afterBlock && (emptyline_count == 1))
					{
						//lines[i]='<br class="hw_emptyline_afterblock"/>';
						emptyline_count = 0;
						continue;
					}
					*/

					/*
					if (emptyline && nlopenmatch && (emptyline_count > 2))
					{
						//lines[i]='<br class="hw_emptyline_first"/>';
						emptyline_count--;
						continue;
					}
					*/
					if (emptyline)
					{
						//if (afterBlock)
						//    {
						//emptyline_count--;
						//        afterBlock = false;
						//        continue;
						//    }
						emptyline_before = true;

						if (inParagraph)
						{
							lines[i]=lines[i]+'</p>';
							inParagraph = false;
						}
						//else if (afterBlock)
						//{   }
						else
						{
							//this is experimental (09.07.2009 MRG)
							if (emptyline_count == 1 && (emptylineAfter||specialClosematchBefore)) continue;
							//alert(lines[i+1] + beforeBlock+emptyline_count);
							if ((emptyline_count  % 2 == 0) && (emptylineAfter || beforeBlock || specialClosematchTwoBefore)) //&& !beforeBlock
								lines[i] = lines[i]+'<p class="hw_emptyline_first"><br class="hw_emptyline_first"/>'; //'<p class="first">';//+lines[i];
							else
								lines[i] = lines[i]+'<p class="hw_emptyline"><br class="hw_emptyline"/>';			    
							// use this if direct bypass of empty textfield causes problems
							//if (i>lines.length-1) lines[i] = lines[i]+'<p><br class="hw_emptyline"/>';//+lines[i];
							inParagraph = true;
						}
						//emptyline_before = true;
						continue;

					}

					// Todo: any emptyline will lead to "countinue". no need to check here
					if (!openmatch && !inParagraph && !emptyline && !inBlock &&!closematch)
					{
						lines[i] = '<p>'+lines[i];
						inParagraph = true;
					} else if (!openmatch && !emptyline && emptyline_before && !inBlock && !closematch && inParagraph)
{
						lines[i]='</p><p>'+lines[i];
						inParagraph = true;
					}
					if (openmatch && inParagraph)
					{
						lines[i]='</p>'+lines[i];
						inParagraph = false;
					}

					// 090929-- MRG. this was deactivated. Highly experimental!!
					// Todo: remove nlopenmatch
					if (false && closematch && !inParagraph && !inBlock && !nlopenmatch)
					{
						lines[i]=lines[i]+'<p>';
						inParagraph = true;
					}

					if (i == lines.length-1)
					{
						if (inParagraph)
						{
							inParagraph = false;
							lines[i]=lines[i]+'</p>';
						}
					}

					//if (!emptyline) emptyline_first = false;
					//if (emptyline && !inParagraph && !emptyline_first) emptyline_first = true;
					//if (emptyline && emptyline_first ) emptyline_first = false;
					//disabled empty lines
					emptyline_before = false;
					afterBlock = false;
				}

			}

			text = lines.join('\n');

			return text;
		},

		/**
		* Converts a MW image link to HTML
		* @param string link The inner part of a MW link to a file.
		* @return string Rendered HTML
		*/
		_image2html: function (link)
		{
			//TODO: inline stylings taken from MediaWiki and adapted to TinyMCE markup. Not nice...
			var htmlImageObject = $('<img border="0" style="display:block; margin: 0.5em 0 0.8em 0; padding: 3px"/>');
			var wikiImageObject = {
				imagename: '',
				thumb: false,
				thumbsize: this._userThumbsize,
				right: false,
				left: false,
				center: false,
				align: '',
				none: false,
				frameless: false,
				frame: false,
				border: false,
				upright: false,
				alt: '',
				caption: '',
				link: '',
				sizewidth: false,
				sizeheight: false
			}

			var parts = link.split("|");
			wikiImageObject.imagename = parts[0];

			for( var i = 1; i < parts.length; i++ ) {
				var part = parts[i];
				if( part.endsWith('px') ) { //Dependency BlueSpiceFramework.js
					var unsuffixedValue = part.substr( 0, part.length - 2 ); //"100x100px" --> "100x100"
					wikiImageObject.sizewidth= unsuffixedValue;
					var dimensions = unsuffixedValue.split('x'); //"100x100"
					if( dimensions.length == 2 ) {
						wikiImageObject.sizewidth = dimensions[0] == '' ? false : dimensions[0]; //"x100"
						wikiImageObject.sizeheight= dimensions[1];
					}
					wikiImageObject.frame = false; //Only size _or_ frame: see MW doc
					continue;
				}
				
				if( $.inArray( part, ['thumb', 'mini', 'miniatur'] ) != -1 ) {
					wikiImageObject.thumb = true;
					continue;
				}
				if( $.inArray( part, ['right', 'rechts'] ) != -1 ) {
					wikiImageObject.right = true;
					wikiImageObject.align = 'right';
					continue;
				}
				if( $.inArray( part, ['left', 'links'] ) != -1 ) {
					wikiImageObject.left = true;
					wikiImageObject.align = 'left';
					continue;
				}
				if( $.inArray( part, ['center', 'zentriert'] ) != -1 ) {
					wikiImageObject.center = true;
					wikiImageObject.align = 'center';
					continue;
				}
				if( $.inArray( part, ['none', 'ohne'] ) != -1 ) {
					wikiImageObject.none = true;
					continue;
				}
				if( $.inArray( part, ['frame', 'gerahmt'] ) != -1 ) {
					wikiImageObject.frame = true;
					wikiImageObject.sizewidth  = false;
					wikiImageObject.sizeheight = false; //Only size _or_ frame: see MW doc
					continue;
				}
				if( $.inArray( part, ['frameless', 'rahmenlos'] ) != -1 ) {
					wikiImageObject.frameless = true;
					continue;
				}
				if( $.inArray( part, ['border', 'rand'] ) != -1 ) {
					wikiImageObject.border = true;
					continue;
				}
				
				var kvpair = part.split('=');
				if( kvpair.length == 1 ) {
					wikiImageObject.caption = part; //hopefully
					continue;
				}
				
				var key   = kvpair[0];
				var value = kvpair[1];
				
				if( $.inArray( key, ['link', 'verweis'] ) != -1 ) {
					wikiImageObject.link = value;
					continue;
				}
				
				if( $.inArray( key, ['upright', 'hochkant'] ) != -1 ) {
					wikiImageObject.upright = value;
					continue;
				}
				
				if( key == 'alt' ) {
					wikiImageObject.alt = value;
					continue;
				}
			}

			var src = '';
			var useGetRealLink = true;
			if ((typeof(bsSecureFileStoreActiveJS)=="undefined") || !bsSecureFileStoreActiveJS) {
				var imgname = parts[0].split(':');
				if(imgname[1].indexOf('_BN_REPLACE') == -1) {
					src='_BN_REPLACE'+imgname[1];
				}
				else {
					src=imgname[1];
					useGetRealLink = false;
				}
			}
			else {
				var imgparts  = parts[0].split(':');
				var imgnsname = imgparts[0];
				var imgname   = imgparts[1];
				var date = new Date();
				src=wgScriptPath+"/index.php?action=remote&title=-&mod=SecureFileStore&rf=getFile&f="+imgnsname+":"+encodeURIComponent(imgname)+"&t="+date.getTime();
			}

			htmlImageObject.attr( 'src', src ); //This may be not good as it will immediately try to fetch the image, resulting in 404 error
			
			if( wikiImageObject.alt ) {
				htmlImageObject.attr( 'alt', wikiImageObject.alt );
			}
			
			//Workaround for HW#2013020710000217. This is more a bug of InsertFile and should be fixed there
			if( wikiImageObject.caption ) {
				htmlImageObject.attr( 'alt', wikiImageObject.caption );
			}

			if( wikiImageObject.sizewidth != false ) {
				htmlImageObject.width( wikiImageObject.sizewidth );
			}
			if( wikiImageObject.sizeheight != false ) {
				htmlImageObject.height( wikiImageObject.sizeheight );
			}

			//TODO: make sure that tranlating back works with linked images, because class "thumb" is applied to <img> and not to <a>
			if( wikiImageObject.thumb == true ) {
				htmlImageObject.addClass( 'thumb' );
				htmlImageObject.css('border', '1px solid #CCCCCC');
				if(wikiImageObject.sizewidth == false ) {
					htmlImageObject.width( wikiImageObject.thumbsize );
				}
			}

			if( wikiImageObject.link != false) {
				htmlImageObject.wrap('<a style="display:inline-block"></a>'); //IE needs closing tag
				htmlImageObject = htmlImageObject.parent();
				htmlImageObject.attr( 'href', wikiImageObject.link );
			}
			
			if( wikiImageObject.center == true ) {
				htmlImageObject.addClass('center');
				htmlImageObject.css({
					'display': 'block',
					'margin-left':'auto',
					'margin-right':'auto'
				});
			}
			else if( wikiImageObject.right == true ) {
				htmlImageObject.addClass( 'tright' );
				htmlImageObject.css('float', 'right');
				htmlImageObject.css('clear', 'right');
				htmlImageObject.css('margin-left', '1.4em');
			}
			else if( wikiImageObject.left == true ) {
				htmlImageObject.addClass( 'tleft' );
				htmlImageObject.css('float', 'left');
				htmlImageObject.css('clear', 'left');
				htmlImageObject.css('margin-right', '1.4em');
			}

			for(property in wikiImageObject ){
				htmlImageObject.attr( 'data-bs-'+property, wikiImageObject[property] );
			}

			if (useGetRealLink && ((typeof(bsSecureFileStoreActiveJS)=="undefined") || !bsSecureFileStoreActiveJS)) {
				BsFileManager.getFileUrl(imgname[1]); //TODO: Move to core or adapter
			}

			return htmlImageObject[0].outerHTML;
		},

		/**
		* Converts a HTML image link to MW
		* @param string text The current state of the text to be processed.
		* @return string Text to be processed with HTML image links.
		*/
		_images2wiki: function (text)
		{
			var images = text.match(/(<a([^>]*?)>)?<img([^>]*?)\/>(<\/a>)?/gi);
			if ( !images ) return text;

			for (var i=0; i<images.length; i++) {
				var image = images[i];
				var htmlImageObject = $( image );
				var wikiImageObject = {};
				var attributes = htmlImageObject[0].attributes;
				for (var j = 0; j < attributes.length; j++ ) {
					var attribute = attributes[j].name;
					if( attribute.startsWith( 'data-bs-' ) == false ) continue;
					var property = attribute.substr( 8, attribute.length );
					wikiImageObject[property] = attributes[j].value;
				}
				
				//Update things that might have changed in markup but not in "data"
				if( htmlImageObject.css('display') == 'block' && htmlImageObject.css('margin-left') == 'auto' && htmlImageObject.css('margin-right') == 'auto'){
					wikiImageObject.align = 'center';
				}
				if( htmlImageObject[0].nodeName.toUpperCase() == 'A' ) {
					wikiImageObject.link = htmlImageObject.attr( 'href' );
					htmlImageObject = htmlImageObject.find('img').first();
				}
				if( htmlImageObject.attr('width') && htmlImageObject.attr('width') != wikiImageObject.sizewidth ){
					wikiImageObject.sizewidth = htmlImageObject.attr('width');
				}
				if( htmlImageObject.attr('height') && htmlImageObject.attr('height') != wikiImageObject.sizeheight ){
					wikiImageObject.sizeheight = htmlImageObject.attr('height');
				}

				if( htmlImageObject.css('float') ) {
					if( htmlImageObject.css('float') == 'left' ) {
						wikiImageObject.left= true;
						wikiImageObject.align = 'left';
					}
					else if( htmlImageObject.css('float') == 'right' ) {
						wikiImageObject.right= true;
						wikiImageObject.align = 'right';
					}
				}
				
				//Build wikitext
				var wikiText = [];
				wikiText.push( wikiImageObject.imagename );
				for( property in wikiImageObject ) {
					if( $.inArray(property, ['imagename','thumbsize'])  != -1 ) continue; //Filter non-wiki data
					if( $.inArray(property, ['left','right', 'center']) != -1 ) continue; //Not used stuff
					var value = wikiImageObject[property];
					if( value == "" || typeof value == "undefined" ) continue;

					if( property == 'sizewidth' ) {
						var size = '';
						if( wikiImageObject.sizewidth && wikiImageObject.sizewidth != "false" ) {
							size = wikiImageObject.sizewidth;
						}
						if( wikiImageObject.sizeheight && wikiImageObject.sizeheight != "false" ) {
							size += 'x' + wikiImageObject.sizeheight;
						}
						if( size.length > 0 ) size += 'px';
						wikiText.push(size);
						continue;
					}
					if( $.inArray( property, [ 'alt', 'link' ] ) != -1 ) {
						wikiText.push(property +'='+value);
						continue;
					}
					if( $.inArray( property, ['caption', 'align'] ) != -1 ) {
						wikiText.push( value );
						continue;
					}
					if( value == "true" ) {
						wikiText.push( property ); //frame, border, thumb, left, right...
					}
				}
				
				text = text.replace(image, '[[' + wikiText.join('|') + ']]');
			}
			return text;
		},

		/**
		 * Converts external and internal MW links to HTML
		 * @param string text Current state of the text to be converted
		 * @return string Text fo be converted with links in HTML
		 */
		_links2html: function(text)
		{
			// internal links
			var links = text.match(/\[\[([^\]]*?)\]\]/gi);
			if (links){
				for (var i=0; i < links.length; i++) {
					var link   = links[i].substr(2, links[i].length-4);
					var parts  = link.split("|");
					var target = parts[0];
					var label  = parts[0];

					//FS#134: Cleanup specials within Link
					target = target.replace(/\<.*?\>/g, "");
					if (parts.length > 1) label = parts[1];
					var linkhtml = '<a href="'+escape(target)+'" type="hw_internal_link" class="internal">'+label+'</a>';

					var targetparts = target.split(":");
					var namespaces = mw.config ? mw.config.get('wgNamespaceIds') : wgNamespaceIds; //TODO: move to init?

					if ( targetparts.length > 1 ) {
						var nmspPrefix = targetparts[0];
						var nmspId = namespaces[nmspPrefix.toLowerCase()];
						if( nmspId == 6 ) { //NS_IMAGE
							var targetextparts = target.split(".");
							var targetext = targetextparts[targetextparts.length-1];
							if ( $.inArray(targetext, bsImageExtensions ) != -1 ) {
								linkhtml = this._image2html(link);
							}
						}
					}

					text = text.replace("[["+link+"]]", linkhtml);
				}
			}

			// only recognize as link if protocol is given
			links = text.match(/\[([^\]]*):(\/\/)?([^\]]*?)\]/gi);
			if (links) {
				for (i=0; i<links.length; i++) {
					link   = links[i].substr(1, links[i].length-2);
					parts  = link.split(" ");
					target = parts[0];
					label  = parts[0];

					//FS#134: Cleanup specials within Link
					target = target.replace(/\<.*?\>/g, "");

					if (parts.length > 1) {
						parts.shift();
						label = parts.join(" ");
					}
					target = escape(target);
					linkhtml = "<a href='"+target+"'>"+label+"</a>";
					text = text.replace("["+link+"]", linkhtml)
				}
			}
			return text;
		},

		/**
		 * Converts HTML links to internal and external MW link
		 * @param string text Current state of the text to be converted
		 * @return string Text to be converted with MW links
		 */
		_links2wiki: function(text)
		{
			var links = text.match(/<a(.*?)<\/a>/gi);
			var linkwiki = '';
			if (links) {
				for (var i=0; i<links.length; i++) {
					var type= false;
					var target=false;
					var label = false;

					var link = links[i];

					var hrefAttr = link.match(/href="(.*?)"/i);
					if (hrefAttr) {
						target = hrefAttr[1];
						target = unescape(target);
					}
					// TODO: <br /> br-tags bereits in insertLink abfangen oder hier einfügen
					var inner = link.match(/>(.*?)<\/a>/i);
					if (inner) { 
						label = inner[1];
						label = label.replace(/<br.*?\/>/gi, '');
					}

					var typeAttr = link.match(/type="(.*?)"/i);
					if (typeAttr) type = typeAttr[1];

					if (type=="hw_internal_link") {
						if (target==tinymce.activeEditor.dom.decode(label)) linkwiki = "[["+target+"]]";
						else linkwiki = "[["+target+"|"+label+"]]";
					}
					else {
						/*
						//Former processing: if target=label like "http://www.example.com" -> WikiParser: "[1]"-style link.
						if (target==label) linkwiki = "["+target+"]";
						else linkwiki = "["+target+" "+label+"]";
						*/
						//QuickFix:  always use label, even if identical to target.
						linkwiki = "["+target+" "+label+"]";
						/*
						// Alternative: do not put "[", "]" around target -> WikiParser: "http://www.example.com"-style link. BUT: Does wiki2html parser recognize anymore?
						if (target==label) linkwiki = target";
						else linkwiki = "["+target+" "+label+"]";
						*/
					}
					text = text.replace(links[i], linkwiki);
				}
			}
			return text;
		},

		/**
		 * Normalizes some MW table syntax shorthand to HTML attributes
		 * @param string attr Attribute line in MW cell or row definition
		 * @param string elm Switch btw. cell and row, takes two values: "cell" and "row".
		 * @return string Normalized attribute string.
		 */
		// TODO MRG (18.06.11 11:17): This is not reversible. Question is, should there be a marker?
		// TODO MRG (18.06.11 11:18): Write transformation test for this.
		_tablesAttrCleanUp: function (attr, elm) {
			switch(elm) {
				case 'row':
					attr = attr.replace(/al="*?(.*)"*?/g, "align=\"$1\"");
					attr = attr.replace(/bc="*?(.*)"*?/g, "background-color=\"$1\"");
					attr = attr.replace(/va="*?(.*)"*?/g, "valign=\"$1\"");
					return attr;
					break;
				case 'cell':
					attr = attr.replace(/al="*?(.*)"*?/g, "align=\"$1\"");
					attr = attr.replace(/bc="*?(.*)"*?/g, "background-color=\"$1\"");
					attr = attr.replace(/cs="*?(.*)"*?/g, "colspan=\"$1\"");
					attr = attr.replace(/rs="*?(.*)"*?/g, "rowspan=\"$1\"");
					attr = attr.replace(/va="*?(.*)"*?/g, "valign=\"$1\"");
					attr = attr.replace(/wd="*?(.*)"*?/g, "width=\"$1\"");
					return attr;
					break;
			}
		},

		/**
		 * Convert MW tables to HTML
		 * @param string text Current state of the text to be converted.
		 * @return string Text to be converted with tables replaced to HTML
		 */
		_tables2html: function (text)
		{
			// there is an IE bug with split: split(\n) will not produce an extra element with \n\n.
			// therefore, some blindtext is inserted which is removed at the end of this section
			// in first pass, some double empty lines remain, therefore, a second pass is necessary
			text = text.replace(/\n\n/gmi, "\n@@blindline@@\n");
			text = text.replace(/\n\n/gmi, "\n@@blindline@@\n");
			var lines = text.split(/\n/);
			var inTr = false;
			var inTd = false;
			var start = 0;
			var inTable = false;
			//this._debugMsg("_inTablesLinks", lines.length);
			for (var i=0; i<lines.length; i++)
			{
				var l;
				if (l = lines[i].match(/^\{\|(.*)/gi))
				{
					// nested table support, beware: recursive
					//console.log(inTable);
					if ( inTable ) {
						//console.log( 'nested' );
						var innerlines = '';
						var nestLevel = 0;
						for (; i<lines.length; i++)
						{
							if ( lines[i].match(/^\{\|(.*)/gi) ) {
								nestLevel++;
								innerlines = innerlines + lines[i] + "\n";
								lines.splice(i,1);
								i--;
							}
							else if ( lines[i].match(/^\|\}/gi ) ) {
								if ( nestLevel > 1 ) {
									innerlines = innerlines + lines[i] + "\n";
									lines.splice(i,1);
									i--;
									nestLevel--;
								} else {
									innerlines = innerlines + lines[i];
									lines.splice(i,1);
									i--;
									break;
								}
							} else {
								innerlines = innerlines + lines[i] + "\n";
								lines.splice(i,1);
								i--;
							}
						}
						i++;
						var innertable = this._tables2html( innerlines );
						//console.log(innertable);
						lines.splice(i,0,innertable);
						continue;
					}
					var tableattr = l[0].substr(2, l[0].length);
					if ( tableattr != '' ) tableattr = " "+tableattr;
					lines[i] = "<table"+tableattr+">";
					start = i;
					inTable = true;
				//_debugMsg("table", lines[i]);
				}
				else if (l = lines[i].match(/^\|\}/gi))
				{
					//this._debugMsg("line", l[0]);
					var closeline = "";
					if (inTd) closeline = "</td>";
					if (inTr) closeline += "</tr>";
					lines[i] = closeline + "</table>"+l[0].substr(2, l[0].length);
					inTr = inTd = inTable = false;
				}
				else if ((i == (start+1)) && (l = lines[i].match(/^\|\+(.*)/gi)))
				{
					lines[i] = "<caption>"+l[0].substr(2)+"</caption>";

				//_debugMsg("row", lines[i]);
				}
				else if (l = lines[i].match(/^\|\-(.*)/gi))
				{
					var endTd = "";
					var attr = this._tablesAttrCleanUp(l[0].substr(2, l[0].length), 'row');
					if (attr != "") attr = " "+attr;
					if (inTd)
					{
						endTd = "</td>";
						inTd = false;
					}
					if (inTr) lines[i] = endTd+"</tr><tr"+attr+">";
					else
					{
						lines[i] = endTd+"<tr"+attr+">";
						inTr = true;
					}

				//_debugMsg("row", lines[i]);
				}
				else if (l = lines[i].match(/^\|(.*)/gi))
				{
					// Todo: also implement for !!
					//console.debug(lines[i]);
					var cells = l[0].substr(1, l[0].length).split(/(\|\|)/);
					//console.log(l[0].substr(1, l[0].length));
					var curLine = "";
					for (var k = 0; k<cells.length; k++)
					{

						var tdText = "";
						var tdAttr = "";

						if (k>0 && (cells[k].indexOf("|")==0)) cells[k]=cells[k].substr(1, cells[k].length);
						var cont = cells[k].split("|");
						//console.log(cells[k]);
						//console.log(cont.length);
						if (cont.length > 1)
						{

							// This reflects the case where a pipe is within the table content
							var tempcont = new Array();
							for (var j=1; j<cont.length; j++)
							{
								tempcont[j-1] = cont[j];
							}
							tdText = tempcont.join("|");
							tdAttr = this._tablesAttrCleanUp(cont[0], 'cell');
							if (tdAttr != '') tdAttr = " "+tdAttr;
						}
						else tdText = cont[0];
						if (!inTr) {
							inTr = true;
							curLine = "<tr>" + curLine;
						}
						if (inTd) curLine += "</td><td"+tdAttr+">"+tdText;
						else
						{
							curLine += "<td"+tdAttr+">"+tdText;
							inTd = true;
						}
					}
					lines[i] = curLine;
				}
				else if (l = lines[i].match(/^\!(.*)/gi))
				{
					var cells = l[0].substr(1, l[0].length).split(/!!/);
					//console.log(l[0].substr(1, l[0].length));
					var curLine = "";
					for (var k = 0; k<cells.length; k++)
					{
						if (cells[k] == "!!") continue;
						var tdText = "";
						var tdAttr = "";

						if (k>0 && (cells[k].indexOf("!")==0 || cells[k].indexOf("|")==0)) cells[k]=cells[k].substr(1, cells[k].length);
						var cont = cells[k].split(/!|\|/);
						//console.log(cells[k]);
						//console.log(cont.length);
						if (cont.length > 1)
						{

							// This reflects the case where a pipe is within the table content
							var tempcont = new Array();
							for (var j=1; j<cont.length; j++)
							{
								tempcont[j-1] = cont[j];
							}
							tdText = tempcont.join("|");
							tdAttr = this._tablesAttrCleanUp(cont[0], 'cell');
							if (tdAttr != '') tdAttr = " "+tdAttr;
						}
						else tdText = cont[0];
						if (!inTr) {
							inTr = true;
							curLine = "<tr>" + curLine;
						}
						if (inTd) curLine += "</th><th"+tdAttr+">"+tdText;
						else
						{
							curLine += "<th"+tdAttr+">"+tdText;
							inTd = true;
						}
					}
					lines[i] = curLine;
				}
			}

			text = lines.join("\n");
			//this._debugMsg("before_replace:table", text);
			text = text.replace(/@@blindline@@/gmi, "");
			//this._debugMsg("table", text);
			//console.debug(text);
			return text;
		},


		/**
		 * Converts HTML tables to MW code
		 * @param string text Current state of the text to be converted
		 * @return string Text to be converted with Wiki tables.
		 */
		_tables2wiki: function (text)
		{
			//cleanup MCE-Code
			//text = text.replace(/class="mceVisualAid"/gi, "");
			
			//cleanup thead and tbody tags. Caution: Must be placed before th cleanup because of 
			//regex collision
			text = text.replace(/<(\/)?tbody([^>]*)>/gmi, "");
			text = text.replace(/<(\/)?thead([^>]*)>/gmi, "");
			text = text.replace(/<(\/)?tfoot([^>]*)>/gmi, "");

			text = text.replace(/\n?<table([^>]*)>/gmi, "\n{|$1");
			text = text.replace(/\n?<\/table([^>]*)>/gi, "\n|}");
			//console.log(text);
			text = text.replace(/\n?<caption([^>]*)>/gmi, "\n|+$1");
			text = text.replace(/\n?<\/caption([^>]*)>/gmi, "");

			text = text.replace(/\n?<tr([^>]*)>/gmi, "\n|-$1");
			text = text.replace(/\n?<\/tr([^>]*)>/gmi, "");

			text = text.replace(/\n?<th([^>]*)>/gmi, "\n!$1|");
			text = text.replace(/\n?<\/th([^>]*)>/gmi, "");

			text = text.replace(/\n?<td([^>]*)>/gmi, "\n|$1|");
			// Todo: \n raus??
			text = text.replace(/\n?<\/td([^>]*)>/gmi, "");



			//text = text.replace(/\n+\|-/gmi, "\n|-");
			//cleanup bogus brs that make empty cells show in editor
			//tiny 3.4.9 not needed anymore
			//text = text.replace(/\|\|(.*?)<br \/>/gi, "||$1");
			text = text.replace(/\|\|&nbsp;/gi, "||");
			//this._debugMsg("nachTable", text);
			return text;
		},

		/**
	 * Removes pre sections that begin with spaces before processing the text. These are temporarily put into an array this._pres_space.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with position markers for space indicated pre areas.
	 */
		_convertPreWithSpacesToTinyMCE: function(text) {
			//this is experimental support for pres with spaces. Some spaces, at least one character, not multi line
			this._pres_space = new Array();
			text = this._preservePres( text );
			// careful: this is greedy and goes on until it finds a line ending.
			// originally ended in (\n|$), however, this would result in only every other
			// line being recognized since the regex then matches line endings at the beginning
			// and at the end.
			// There is a lookahead for tables: ?!<t
			this._pres_space = text.match(/(^|\n)( +(?!<t)\S[^\n]*)/gi);
			//console.log(this._pres_space);
			if (this._pres_space)
			{
				for (var i=0; i<this._pres_space.length; i++)
				{
					//prevent HTML-Tables from being rendered as pre
					//if (this._pres_space[i].match(/(<table|<\/table|<tr|<\/tr|<td|<\/td|<th|<\/th)/gi)) continue;
					text = text.replace(this._pres_space[i], "@@@PRE_SPACE"+i+"@@@");
					this._pres_space[i] = this._pres_space[i].replace("\n", "").substr(1, this._pres_space[i].length);
					text = text.replace("@@@PRE_SPACE"+i+"@@@", '<pre class="bs_pre_from_space">'+this._pres_space[i]+'</pre>');

				}
			//console.log(this._pres_space);
			//console.log(text);
			}

			// can be pre with a marker attribute "space"
			text = text.replace(/<\/pre>\s*?<pre[^>]*>/gmi, '\n');
			//console.log(text);
			//this._debugMsg("recoverPres_space", text);
			text = this._recoverPres( text );
			return text;

		},

		/**
	 * Restores pre sections that begin with spaces before processing the text. These are fetched from an array this._pres_space.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with space indicated pre areas restored.
	 */
		_convertTinyMCEToPreWithSpaces: function(text) {
			this._pres_space = text.match(/<pre[^>]+bs_pre_from_space[^>]+>([\S\s]*?)<\/pre>/gmi);
			//console.log(this._pres_space);
			if (this._pres_space)
			{
				for (var i=0; i<this._pres_space.length; i++)
				{
					var innerPre = this._pres_space[i];
					innerPre = innerPre.replace(/<pre[^>]*>/i, "");
					innerPre = innerPre.replace(/<\/pre>/i, "");
					var innerPreLines = innerPre.split(/\n/i);
					// This is ugly, however, sometimes tiny uses br instead of line breaks
					if (innerPreLines.length == 1) innerPreLines = innerPre.split(/<br \/>/i);
					for(var j=0; j<innerPreLines.length; j++) {
						innerPreLines[j] = " "+innerPreLines[j];
					}
					innerPre = innerPreLines.join("\n");
					//console.log(innerPreLines);
					text = text.replace(this._pres_space[i], innerPre);
				}
			}
			return text;
		},

	/**
	 * Removes pre sections before processing the text. These are temporarily put into an array this._pres.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with position markers for pre areas.
	 */
		_preservePres: function(text)
		{
			this._pres = false;
			this._pres = text.match(/<pre[^>]*?(?!bs_pre_from_space)[^>]*?>([\S\s]*?)<\/pre>/gmi);
			if (this._pres)
			{
				for (var i=0; i<this._pres.length; i++) text = text.replace(this._pres[i], "@@@PRE"+i+"@@@");
			}

			this._pres_space = false;
			// TODO MRG (22.10.10 19:28): This should match pre class="space", narrow down (now matches everything)
			this._pres_space = text.match(/<pre[^>]+bs_pre_from_space[^>]+>([\S\s]*?)<\/pre>/gmi);
			if (this._pres_space)
			{
				for (var i=0; i<this._pres_space.length; i++) text = text.replace(this._pres_space[i], "@@@PRE_SPACE"+i+"@@@");
			}

 
			/*
	    specialtaglist = bsVisualEditorConfigStandard.specialtaglist;
            //regex = '^( +(?!<(t|'+specialtaglist+'))\\S[\\S\\s]*?)$';
	    regex = '^( +(?!(\\<table)))\\S*';
	    //console.log(regex);
            matcher = new RegExp(regex, 'gi');

            i=0;
            while ((presp = matcher.exec(text)) != null)
            {
                //console.log(presp);
		text = text.replace(presp[0], "@@@PRE_SPACE"+i+"@@@");
                this._pres_space[i]=presp[0];
                i++;
            }
            return text;
	    */



			return text;
		},
		
		/**
	 * Removes entities before processing the text. These are temporarily put into an array this._entities.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with position markers for entities.
	 */
		_preserveEntities: function(text)
		{
			if (!this._entities) this._entities = new Array();
			// Tiny replaces &nbsp; by space, so we need to undo this
			text = text.replace(/<span class="hw_htmlentity">[\s\u00a0]<\/span>/gi, '<span class="hw_htmlentity">&nbsp;<\/span>');
			var regex = '<span class="hw_htmlentity">(&[^;]*?;)<\/span>';
			var matcher = new RegExp(regex, 'gmi');

			var mtext = text;

			var i=0;
			var ent;
			while ((ent = matcher.exec(mtext)) != null)
			{
				//console.log(ent[1]);
				text = text.replace(ent[0], "@@@ENTITY"+i+"@@@");
				this._entities[i]=ent[1];
				i++;
			}
			return text;

		},

		/**
	 * Removes templates, special tags and comments before processing the text. These are temporarily put into an array this._templates.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with position markers for special tag areas, templates and comments.
	 */
		_preserveSpecialTags: function(text)
		{
			if (!this._switches) {
				this._switches = new Array();
			}

			mtext = text;
			regex = "__(.*?)__";
			matcher = new RegExp(regex, 'gmi');
			i=0;
			var swt = '';
			while ((swt = matcher.exec(mtext)) != null) {
				this._switches[i] = swt[0];

				text = text.replace(
					swt[0],
					'<span class="mceNonEditable switch" id="hw_switch:@@@SWT'+i+'@@@" data-bs-name="'+swt[1]+'" data-bs-type="switch" data-bs-id="'+i+'">'
						+ '__ '+ swt[1] + ' __'
						+ '</span>'
				);
				i++;
			}
			
			var curlyBraceDepth = 0;
			var squareBraceDepth = 0;
			var templateDepth = 0;
			var squareBraceFirst = false;
			var tempTemplate = '';
			this._templates = new Array();
			for ( var pos = 0; pos < text.length; pos++ ) {
				if ( text[pos] == '{' ) {
					curlyBraceDepth++;
					if ( text[pos+1] == '{' ) {
						templateDepth++;
					}
				}
				if ( text[pos] == '[' ) {
					if ( curlyBraceDepth == 0 ) {
						squareBraceFirst = true;
					}
					squareBraceDepth++;
				}
				// Caution: this matches only from the second curly brace.
				if ( templateDepth && !squareBraceFirst ) {
					tempTemplate = tempTemplate + text[pos];
				}
				if ( text[pos] == '}' ) {
					curlyBraceDepth--;
					if ( text[pos-1] == '}' ) {
						templateDepth--;
					}
					if ( templateDepth == 0 && !squareBraceFirst ) {
						//console.log( tempTemplate )
						if ( tempTemplate != '' ) this._templates.push( tempTemplate );
						tempTemplate = '';
					}
				}
				if ( text[pos] == ']' ) {
					squareBraceDepth--;
					if ( squareBraceDepth == 0 ) {
						squareBraceFirst = false;
					}
				}
				//console.log( "pos: " + pos + " depth: " + braceDepth );
			}
			
			//console.log( this._templates );
			
			//this._templates = false;
			//this._templates = text.match(/\{\{([\S\s]*?)\}\}/gmi);
			if (this._templates)
			{
				for (var i=0; i<this._templates.length; i++) {
					var templatename = this._templates[i];
					templatename = templatename.replace(/[\{\}]/gmi, "");
					var templatenameLines = templatename.split(/\n/i);
					templatename = templatenameLines[0];
					text = text.replace(
						this._templates[i], 
						'<span class="mceNonEditable template" id="hw_template:@@@TPL'+i+'@@@" data-bs-name="'+templatename+'" data-bs-type="template" data-bs-id="'+i+'">'
							+'{{ '+templatename+' }}'
							+'</span>'
					);
				}
			}

			//quirky. Needs to be there for the occasional second pass of cleanup
			//if (!text.match(/hw_specialtag:/gmi)) this._specialtags = new Array();
			if (!this._specialtags) this._specialtags = new Array();

			var specialtaglist = bsVisualEditorConfigStandard.specialtaglist;
			// Tags without innerHTML need /> as end marker. Maybe this should be task of a preprocessor, in order to allow mw style tags without /.
			var regex = '<('+specialtaglist+')[\\S\\s]*?((/>)|(>([\\S\\s]*?<\\/\\1>)))';

			var matcher = new RegExp(regex, 'gmi');
			//this._specialtags = matcher.exec(text);
			//this._specialtags = text.match(/(\n?<(video)>[\S\s]*?<\/\2>\n?)/gmi);
			var mtext = text;
			i=0;
			var st = '';
			while ((st = matcher.exec(mtext)) != null) {
				//for (i=0; i<this._specialtags.length; i+=2)
				text = text.replace(
					st[0], 
					'<span class="mceNonEditable tag" id="hw_specialtag:@@@ST'+i+'@@@" data-bs-name="'+st[1]+'" data-bs-type="tag" data-bs-id="'+i+'">'
						+ '&lt; '+st[1]+' &gt;'
						+'</span>'
				);
				//this._specialtags.push(st[0]);
				this._specialtags[i]=st[0];
				i++;
			}
			//this._debugMsg("_preserveST",this._specialtags);

			//this._comments = false;
			if (!this._comments)
			{
				this._comments = new Array();
			}
			//this._comments = text.match(/<!--([\S\s]*?)-->/gmi);
			//text = text.replace(/\r/gmi, "");
			mtext = text;
			regex = "(^|\\n)?<!--([\\S\\s]+?)-->(\\n|$)?";
			matcher = new RegExp(regex, 'gmi');
			i=0;
			var cmt = '';
			while ((cmt = matcher.exec(mtext)) != null)
			{
				this._comments[i] = cmt[0];
				//alert(cmt[1]+cmt[2]);
				//console.debug(cmt[3].charCodeAt(0));
				if (cmt[3])
				{
					if (cmt[3].charCodeAt(0) == 10) cmt[3] = "\n";

				}
				else cmt[3] = '';
				if (cmt[1])
				{
					if (cmt[1].charCodeAt(0) == 10) cmt[1] = "\n";

				}
				else cmt[1] = '';

				//console.debug(cmt[0].replace("\n", "NEWLINE"));
				//cmt[3] = cmt[3].replace("\n", "NEWLINE");
				//console.debug((cmt[1]+cmt[2]+cmt[3]+"a").replace("\n", "NEWLINE"));
				//text = text.replace(cmt[0], cmt[1]+"<span id='hw_comment:@@@CMT"+i+"@@@' class='hw_comment_wrapper' style='background-color:#DDDDDD;display:inline;' >&nbsp;<span class='mceNonEditable'  >"+(cmt[2]).replace("\n", "<br />")+"</span>&nbsp;</span>"+cmt[3]);
				var innerText = cmt[2]+cmt[3];
				// TODO MRG (20.12.12 01:49): This is adapted to german needs. Other languages might want other characters
				innerText = innerText.replace(/[^a-zA-Z0-9äöüÄÖÜß\(\)_]/gmi, " ");
				text = text.replace(
					cmt[0],
					cmt[1] 
						+ '<span class="mceNonEditable comment" id="hw_comment:@@@CMT'+i+'@@@" data-bs-type="comment" data-bs-id="'+i+'">'
						+ innerText
						+ '</span>'
						+ cmt[3]
				);
				i++;
			}



			//if (this._comments)
			//{
			//for (i=0; i<this._comments.length; i++) text = text.replace(this._comments[i], "@@@CMT"+i+"@@@");
			//for (i=0; i<this._comments.length; i++) text = text.replace(this._comments[i], "<span style='background-color:#DDDDDD;display:inline;' class='mceNonEditable' id='hw_comment:@@@CMT"+i+"@@@' >"+this._comments[i].slice(4, this._comments[i].length-3)+"</span>");
			//for (i=0; i<this._comments.length; i++) text = text.replace(this._comments[i][0], "<span style='background-color:#DDDDDD;display:inline;' class='mceNonEditable' id='hw_comment:@@@CMT"+i+"@@@' >"+this._comments[i][1]+this._comments[i][2]+"</span>");
			//}
			//this._debugMsg("Comment", text);

			return text;
		},

		/**
	 * Restores entities after processing the text. These are fetched from an array this._entities.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with recovered entities.
	 */
		_recoverEntities: function(text)
		{
			if (this._entities)
			{
				for (var i=0; i<this._entities.length; i++)
				{
					var regex = '@@@ENTITY'+i+'@@@';
					var replacer = new RegExp(regex, 'gmi');
					text = text.replace(replacer, this._entities[i]);
				}
			}
			this._entities = false;
			return text;
		},

		/**
	 * Restores pres after processing the text. These are fetched from an array this._pres.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with recovered pres.
	 */
		_recoverPres: function(text)
		{
			if (this._pres)
			{
				for (var i=0; i<this._pres.length; i++)
				{
					var regex = '@@@PRE'+i+'@@@';
					var replacer = new RegExp(regex, 'gmi');
					// \n works in IE. In FF, this is not neccessary.
					if (this.isIE) {
						text = text.replace(replacer, "\n"+this._pres[i]);
					} else {
						text = text.replace(replacer, this._pres[i]);
					}
				}
			}
			this._pres = false;

			//this is experimental support for pres with spaces
			if (this._pres_space)
			{
				for (var i=0; i<this._pres_space.length; i++)
				{
					var regex = '@@@PRE_SPACE'+i+'@@@';
					var replacer = new RegExp(regex, 'gmi');
					// \n works in IE. In FF, this is not neccessary.
					if (this.isIE) {
						text = text.replace(replacer, "\n"+this._pres_space[i]);
					} else {
						text = text.replace(replacer, this._pres_space[i]);
					}
				}
			}
			this._pres_space = false;



			//this._debugMsg("CommentRecover1", text);

			//this._comments = false;
			//this._debugMsg("CommentRecover2", text);
			return text;
		},

	/**
	 * Restores templates, special tags and comments after processing the text. These are fetched an array this._templates.
	 * @param string text Current state of the text to be processed
	 * @return string Text to be processed with recovered special tag areas, templates and comments.
	 */
		_recoverSpecialTags: function(text)
		{
			// this must be in inverse order as preserveSpecialTags in order to allow for nested constructions
			//this._debugMsg("_recoverST",this._specialtags);
			if (this._specialtags)
			{
				//this._debugMsg("_recoverST1",this._specialtags);
				for (var i=0; i<this._specialtags.length; i++)
				{
					var matcher = new RegExp('<span[^>]*?id=(\'|")hw_specialtag:@@@ST'+i+'@@@(\'|")[^>]*?>.*?<\\/\s*?span\s*?>', 'gmi');
					text = text.replace(matcher, this._specialtags[i]);
				}
			}
			//this._specialtags = false;
			
			if (this._templates)
			{
				for (i=0; i<this._templates.length; i++) {
					//text = text.replace("@@@TPL"+i+"@@@", this._templates[i]);
					//text = text.replace(this._templates[i], "<span style='background-color:#FDFF00;display:inline;' class='mceNonEditable' id='hw_template:@@@TPL"+i+"@@@'>{{ "+templatename+" }}</span>");
					matcher = new RegExp('<span[^>]*?id=(\'|")hw_template:@@@TPL'+i+'@@@(\'|")[^>]*?>.*?<\\/\s*?span\s*?>', 'gmi');
					text = text.replace(matcher, this._templates[i]);
				}
			}

			if (this._comments)
			{
				for (var i=0; i<this._comments.length; i++)
				{
					//text = text.replace("@@@CMT"+i+"@@@", this._comments[i]);
					var nlBefore = '';
					var nlAfter = '';
					if (this._comments[i].charAt(0) == "\n")
					{
						//this._comments[i] = "\n" + this._comments[i];
						nlBefore = "\\n?";
					}
					if (this._comments[i].charAt(this._comments[i].length-1) == "\n")
					{
						nlAfter = "\\n?"; //Otherwise line feeds would be eaten up. Nom, nom...
						//this._comments[i] += "\n";
					}
					//matcher = new RegExp(nlBefore+'<span[^>]*?id=(\'|")hw_comment:@@@CMT'+i+'@@@(\'|")[^>]*?>(.*?)<\\/\s*?span\s*?>[^<]*?<\\/\s*?span\s*?>'+nlAfter, 'gi');
					matcher = new RegExp(nlBefore+'<span[^>]*?id=(\'|")hw_comment:@@@CMT'+i+'@@@(\'|")[^>]*?>([\\S\\s]*?)<\\/\\s*?span\\s*?>'+nlAfter, 'gi');
					text = text.replace(matcher, this._comments[i]);
				}
				//text = text.replace(/<span class="hw_comment_wrapper"[^>]*?> ?([\s\S]*?) ?<\/span>/gmi, "$1");
				//this._comments = false;
				//console.debug(text);
			}
			
			if (this._switches) {
				for (i = 0; i < this._switches.length; i++ ) {
					matcher = new RegExp('<span[^>]*?id=(\'|")hw_switch:@@@SWT'+i+'@@@(\'|")[^>]*?>([\\S\\s]*?)<\\/\\s*?span\\s*?>', 'gi');
					text = text.replace(matcher, this._switches[i]);
				}
			}
			return text;
		},

		/* obsolete, now in tinymce.js
		*_checkUnsupportedTokens: function(text)
		   {
			   // Todo: Warn...
			   // Todo: Reject open div tags with no closing div tags
			   // Todo: Reject tables that are not fully implemented

			   presstripped = text.replace(/<pre>[\s\S]*?<\/pre>/);

			   editorNotAllowed = false;
			   if (presstripped.match(/<\/?table|<\/?td|<\/?tr|<\/?th/gmi))
			   {
				   alert('Editor does not support html table syntax. Use wiki syntax instead.');
				   editorNotAllowed = true;
			   }
			   //if (text.match(/<\/?pre>/gmi))
			   //{
			   //    alert('Editor does not support pre tags.');
			   //    editorNotAllowed = true;
			   //}
			   if (text.match(/<\/div>\n\n\n\n/gmi))
			   {
				   alert('Editor does not support div tags with more than two empty lines coming after.');
				   editorNotAllowed = true;
			   }
			   if (editorNotAllowed)
			   {
				   //toggleEditorMode('wpTextbox1');
				   //alert('test');
				   return false;
			   }
			   return true;
		   },
		*/

		/**
		* Converts wiki code to HTML. Main parser entry point.
		* @param string text Preprocessed wiki code.
		* @return string HTML version of wiki page.
		*/
		_wiki2html: function(text)
		{
			// bypass for empty pages
			if (text == "") return text;
			var textObject = {text : text};
			$(document).trigger('BSVisualEditorBeforeWikiToHtml', [textObject]);
			text = textObject.text;
			text = tinymce.trim(text);
			//this._debugMsg("_html", text);
			//special tags before pres prevents spaces in special tags like GeSHi to take effect
			//text = this._preserveSpecialTags(text);
			text = this._preservePres(text);

			//text = text.replace(/\S(?!(-|\|}))\S\n(?!(\n))/gmi, "<span class='single_linebreak'>.<\/span>");

			//normalize line endings to \n
			text = text.replace(/\r\n/gi, '\n');

			// table preprocessing -- this is used to make sure every cell begins in a single line
			// do not use m flag here in order to get ^ as line beginning
			// TODO: should this be placed in tables2html?? and is a split at || in this function not obsolete?
			text = text.replace(/(^|.+?)(\|\|)/gi, '$1\n\|');
			text = text.replace(/\n\|\}\n?/gmi, '\n\|\}\n');
			// br preprocessing
			text = text.replace(/<br(.*?)>/gi, function( match, p1, offset, string ) {
				return '<br data-attributes="'+encodeURI( p1 )+'" />'; //TODO: Use JSON.stringify when dropping IE7 support
			});

			// simple formatting
			// the ^' fixes a problem with combined bold and italic markup
			text = text.replace(/'''([^'\n][^\n]*?)'''([^']?)/gmi, '<strong>$1</strong>$2');
			text = text.replace(/''([^'\n][^\n]*?)''([^']?)/gmi, '<em>$1</em>$2');
			//underline needs no conversion
			//no need for this in tiny 3.4.9
			//text = text.replace(/<s>(.*?)<\/s>/gmi, '<strike>$1</strike>');
			//sub and sup need no conversion

			//replaced =([^=]+?)= with =(.+?)= or else tags with attributes in headlines will destroy the layout
			//removed cleaning of whitespace in the end.
			//clean all newlines after headings. this is wikimedia behaviour
			//^$ for pages that contain headings at beginning or end of text
			text = text.replace(/(^|\n)?========(.+?)========(\n+|$)/gmi, '$1<h8>$2</h8>\n');
			text = text.replace(/(^|\n)?=======(.+?)=======(\n+|$)/gmi, '$1<h7>$2</h7>\n');
			text = text.replace(/(^|\n)?======(.+?)======(\n+|$)/gmi, '$1<h6>$2</h6>\n');
			text = text.replace(/(^|\n)?=====(.+?)=====(\n+|$)/gmi, '$1<h5>$2</h5>\n');
			text = text.replace(/(^|\n)?====(.+?)====(\n+|$)/gmi, '$1<h4>$2</h4>\n');
			text = text.replace(/(^|\n)?===(.+?)===(\n+|$)/gmi, '$1<h3>$2</h3>\n');
			text = text.replace(/(^|\n)?==(.+?)==(\n+|$)/gmi, '$1<h2>$2</h2>\n');
			//text = text.replace(/\n?==(.+?)==\s*?(\r\n|\n)?/gmi, '\n<h2>$1</h2>\n');
			//h1 was disabled before because of problems with tags that use =
			text = text.replace(/(^|\n)?=(.+?)=(\n+|$)/gmi, '$1<h1>$2</h1>\n');
			//horizontal rule
			text = text.replace(/^\n?----\n?/gmi, "\n<hr>\n");

			//FS#134
			//This may seem quirky. However, during cleanup, tiny passes this section twice.
			//Second time, replacement has already taken place, but has been partially taken back by links2html
			//So, if you replace once again, you get all the spans in links again. The processing by links2html does not
			//take place anymore, since there are no more wiki tags in the source in second pass.

			//Q: Is this still valid as parsing is now only single pass --MRG
			//20111109 MRG: disabled second pass

			//if (!text.match('<span class="variable">'))
			//text = text.replace(/(__(.*?)__)/gmi, '<span class="variable">$1</span>');
			//if (!text.match('<span class="special">'))
			//text = text.replace(/(\{\{(.*?)\}\})/gmi, '<span class="special">$1</span>');

			text = this._links2html(text);
			//this._debugMsg("_beforeTables", text)

			text = this._tables2html(text);
			//this._debugMsg("_beforeBlockLevels", text)
			// We need this additional line in order to clean up the last blocklevel tag.
			text = text+"\n";
			text = this._blockLevels2html(text);
			this._debugMsg("_afterBlockLevels", text)
			// todo: dynamify this
			//console.debug(text);
			//text = text.replace(/<blockquote><blockquote><blockquote>([\S\s]*?)<\/blockquote><\/blockquote><\/blockquote>/gmi, "<div style='padding-left: 90px;'>$1</div>");
			//text = text.replace(/<blockquote><blockquote>([\S\s]*?)<\/blockquote><\/blockquote>/gmi, "<div style='padding-left: 60px;'>$1</div>");
			//text = text.replace(/<blockquote>([\S\s]*?)<\/blockquote>/gmi, "<div style='padding-left: 30px;'>$1</div>");
			//console.debug(text);

			//Check this, might be unneccessary
			text = text.replace(/<div style='text-align:left'>(.*?)<\/div>/gmi, "<div align='left'>$1</div>");
			text = text.replace(/<div style='text-align:right'>(.*?)<\/div>/gmi, "<div align='right'>$1</div>");
			text = text.replace(/<div style='text-align:center'>(.*?)<\/div>/gmi, "<div align='center'>$1</div>");
			text = text.replace(/<div style='text-align:justify'>(.*?)<\/div>/gmi, "<div align='justify'>$1</div>");

			//replace lines with p-tags
			//	text = text.replace(/\n(*?)\n\n/gmi, "<p>$1</p>");
			//	text = text.replace(/\n(.*?)\n\n/gi, "<p>$1</p>");
			//	text = text.replace(/<\/p>(.*?)\n\n/gi, "<p>$1</p>");

			text = text.replace(/\n/gi, "");

			//fill empty table cells
			//this._debugMsg("_beforeTableCleanup", text)
			text = text.replace(/<td([^>]*)>\s*<\/td>/gmi, '<td$1><br mce_bogus="1"/></td>');
			text = text.replace(/<th([^>]*)>\s*<\/th>/gmi, '<th$1><br mce_bogus="1"/></th>');
			//this._debugMsg("_afterTableCleanup", text);

			//clean up bogus code when spans are in a single line
			text = text.replace(/<p>((<span([^>]*)>\s*)+)<\/p>/gmi, '$1');
			text = text.replace(/<p>((<\/span>\s*)+)<\/p>/gmi, '$1');
			//Write back content of <pre> tags.
			text = this._recoverPres(text);

			// p is neccessary to fix Ticket#2010111510000021. do not use p in the complementary line in html2wiki
			text = text + '<p><br class="hw_lastline" /></p>';
			this._debugMsg("_wiki2html", text);
			textObject = {text : text};
			$(document).trigger('BSVisualEditorAfterWikiToHtml', [textObject]);
			text = textObject.text;
			//console.debug(text);
			return text;
		},

		/**
	 * Converts HTML to wiki code. Main parser entry point.
	 * @param string text Preprocessed wiki code.
	 * @return string Wiki code version of editor HTML.
	 */
		_html2wiki: function(text)
		{
			this._debugMsg("_html2wiki", text);
			//console.debug(text);
			text = tinymce.trim(text);
			var textObject = {text : text};
			$(document).trigger('BSVisualEditorBeforeHtmlToWiki', [textObject]);
			text = textObject.text;
			// Normalize UTF8 spaces as aof TinyMCE 3.4.9
			text = text.replace(/\u00a0/gi, '');
			//Save content of pre tags
			text = this._preservePres(text);
			//text = text.replace(/<table/, "<table hw_marker='htmlstyle'");

			text = text.replace(/\n/gi, "");

			text = text.replace(/<strong>(.*?)<\/strong>/gmi, "'''$1'''");
			text = text.replace(/<b>(.*?)<\/b>/gmi, "'''$1'''");
			text = text.replace(/<em>(.*?)<\/em>/gmi, "''$1''");
			text = text.replace(/<i>(.*?)<\/i>/gmi, "''$1''");
			//underline needs no conversion
			text = text.replace(/<strike>(.*?)<\/strike>/gi, "<s>$1</s>");
			text = text.replace(/<span style="text-decoration: line-through;">(.*?)<\/span>/gi, "<s>$1</s>");
			//sub and sup need no conversion

			//horizontal rule
			//text = text.replace(/\n?<hr.*?>/gmi, "\n----\n");
			//Achtung
			//this._debugMsg("before emptyline", text);
			text = text.replace(/<br class="hw_emptyline_first"[^>]*>/gmi, "@@br_emptyline_first@@");
			// if emptyline_first is no longer empty, change it to a normal p
			// This did not work in my tests :(. However, I think when we need to check for non-empty empty_lines below,
			// it is a matter of consistency to also check emptyline_first.
			//text = text.replace(/<p class="hw_emptyline_first"[^>]*>(.*?\S+.*?)<\/p>/gmi, "<p>$1</p>");
			// if emptyline_first is no longer empty, change it to a normal p
			text = text.replace(/<p class="hw_emptyline_first"[^>]*>(.*?\S+.*?)<\/p>/gmi, "<p>$1</p>");
			text = text.replace(/<p class="hw_emptyline_first"[^>]*>.*?<\/p>/gmi, "<p>@@br_emptyline_first@@</p>");
			text = text.replace(/<br class="hw_emptyline"[^>]*>/gmi, "@@br_emptyline@@");
			// if emptyline is no longer empty, change it to a normal p
			text = text.replace(/<p class="hw_emptyline"[^>]*>(.*?\S+.*?)<\/p>/gmi, "<p>$1</p>");
			text = text.replace(/<p class="hw_emptyline"[^>]*>(.*?)<\/p>/gmi, "<p>@@br_emptyline@@</p>");
			text = text.replace(/<br mce_bogus="1"\/>/gmi, "");
			text = text.replace(/<br.*?>/gi, function( match, offset, string ){
				var attributes = $(match).attr('data-attributes');
				if( typeof attributes == 'undefined' ) attributes = ' /';
				return '<br'+decodeURI(attributes)+'>';
			});
			//this._debugMsg("after br-norm", text);
			text = text.replace(/(<span class="variable">(.*?)<\/span>)/gmi, "$2");
			text = text.replace(/(<span class="special">(.*?)<\/span>)/gmi, "$2");

			text = this._images2wiki(text);
			text = this._links2wiki(text);
			//console.log(text);
			//Todo: this needs to be placed in front of the blocklevel or put within
			text = text.replace(/\n?<p style="([^"]*?)">(.*?)<\/p>/gmi, "\n<div style='$1'>$2</div>\n");
			text = text.replace(/\n?<p style="text-align:\s?left;?">(.*?)<\/p>/gmi, "\n<div style='text-align: left'>$1</div>\n");
			text = text.replace(/\n?<p style="text-align:\s?right;?">(.*?)<\/p>/gmi, "\n<div style='text-align: right'>$1</div>\n");
			text = text.replace(/\n?<p style="text-align:\s?center;?">(.*?)<\/p>/gmi, "\n<div style='text-align: center'>$1</div>\n");
			text = text.replace(/\n?<p style="text-align:\s?justify;?">(.*?)<\/p>/gmi, "\n<div style='text-align: justify'>$1</div>\n")

			text = text.replace(/<\/div>\n?/gmi, "</div>\n");

			// replace indents but not for div tags
			//text = text.replace(/<[^d]([^>]*) style=('|")padding-left: 30px;('|")>/gmi, "<blockquote><$1>");
			//text = text.replace(/<[^d]([^>]*) style=('|")padding-left: 60px;('|")>/gmi, "<blockquote><blockquote><$1>");
			//text = text.replace(/<[^d]([^>]*) style=('|")padding-left: 90px;('|")>/gmi, "<blockquote><blockquote><blockquote><$1>");
			text = text.replace(/\n?<p style=('|")padding-left: 30px;('|")>([\S\s]*?)<\/p>/gmi, "<blockquote>$3</blockquote>");
			text = text.replace(/\n?<p style=('|")padding-left: 60px;('|")>([\S\s]*?)<\/p>/gmi, "<blockquote><blockquote>$3</blockquote>");
			text = text.replace(/\n?<p style=('|")padding-left: 90px;('|")>([\S\s]*?)<\/p>/gmi, "<blockquote><blockquote><blockquote>$3</blockquote>");

			text = text.replace(/\n?<div style=('|")padding-left: 30px;('|")>([\S\s]*?)<\/div>/gmi, "<blockquote>$3</blockquote>");
			text = text.replace(/\n?<div style=('|")padding-left: 60px;('|")>([\S\s]*?)<\/div>/gmi, "<blockquote><blockquote>$3</blockquote>");
			text = text.replace(/\n?<div style=('|")padding-left: 90px;('|")>([\S\s]*?)<\/div>/gmi, "<blockquote><blockquote><blockquote>$3</blockquote>");

			//replace simple divs by p
			text = text.replace(/<div>(.*?)<\/div>/gmi, "<p>$1</p>");

			//lists
			var listTag = '';

			//careful here: .*? does not match newline, however, [\s\S] does.
			//console.debug(text);
			//geht leider nicht eleganter, weil bei Liste am Artikelanfang -> nextPos = 0.
			// => Bedingung direkt in der While-Schleife würde hier abbrechen.
			var nextPos = text.search(/(<ul|<ol|<li( |>)|<\/?dl|<\/?dt|<blockquote[^>]*?>|<\/li( |>)|<\/ul|<\/ol|<\/blockquote|<p( |>)|<\/p( |>)|<h[1-8]|<hr)/);//|<p>|<\/p>
			while (nextPos >=0)
			{
				var oldText = text;     //used for prevention of infinite loops. see at end of loop
				switch (text.substr(nextPos, 2).toLowerCase())
				{
					case '<p' :

						// Todo: putting these lines straight in row might lead to strange behaviour
						//this._debugMsg("before paragraph", text);
						var thePos = text.search(/<p[^>]*>(<span[^>]*hw_comment[^>]*>[\s\S]*?<\/span>[\s\S]*?)<\/p>/mi);
						if (thePos == nextPos) text = text.replace(/<p[^>]*>(<span[^>]*hw_comment[^>]*>[\s\S]*?<\/span>[\s\S]*?)<\/p>/mi, "$1");
						thePos = text.search(/<p(\s+[^>]*?)?>\s*(\s|<br ?\/>)\s*<\/p>/mi);
						if (thePos == nextPos) text = text.replace(/\n?<p(\s+[^>]*?)?>\s*(\s|<br ?\/>)\s*<\/p>/mi, "\n\n");
						thePos = text.search(/<p(\s+[^>]*?)?>(\s| |&nbsp;)*?<\/p>/mi);
						if (thePos == nextPos) text = text.replace(/\n?<p(\s+[^>]*?)?>(\s| |&nbsp;)*?<\/p>/mi, "\n\n");
						//THIS IS EXPERIMENTAL: If anything breaks, put in a second \n at the end
						//this._debugMsg("before paragraph", text);
						thePos = text.search(/<p(\s+[^>]*?)?>([\s\S]*?)<\/p>/mi);
						if (thePos == nextPos) text = text.replace(/\n?<p(\s+[^>]*?)?>([\s\S]*?)<\/p>/mi, "\n$2\n\n");

						break;
				}
				switch (text.substr(nextPos, 3))
				{

					case '</p' :
						text=text.replace(/<\/p>/, "");
						break;
					case '<h1' :
						text = text.replace(/\n?<h1.*?>(.*?)<\/h1>\n?/mi, "\n=$1=\n");
						break;
					case '<h2' :
						text = text.replace(/\n?<h2.*?>(.*?)<\/h2>\n?/mi, "\n==$1==\n");
						break;
					case '<h3' :
						text = text.replace(/\n?<h3.*?>(.*?)<\/h3>\n?/mi, "\n===$1===\n");
						break;
					case '<h4' :
						text = text.replace(/\n?<h4.*?>(.*?)<\/h4>\n?/mi, "\n====$1====\n");
						break;
					case '<h5' :
						text = text.replace(/\n?<h5.*?>(.*?)<\/h5>\n?/mi, "\n=====$1=====\n");
						break;
					case '<h6' :
						text = text.replace(/\n?<h6.*?>(.*?)<\/h6>\n?/mi, "\n======$1======\n");
						break;
					case '<h7' :
						text = text.replace(/\n?<h7.*?>(.*?)<\/h7>\n?/mi, "\n=======$1=======\n");
						break;
					case '<h8' :
						text = text.replace(/\n?<h8.*?>(.*?)<\/h8>\n?/mi, "\n========$1========\n");
						break;
					case '<hr' :
						text = text.replace(/\n?<hr.*?>/mi, "\n----");
						break;
					case '<ul'	:
						listTag = listTag + '*';
						text=text.replace(/<ul[^>]*?>/, "");
						break;
					case '<ol' :
						listTag = listTag + '#';
						text=text.replace(/<ol[^>]*?>/, "");
						break;
					case '<dl' :
						//listTag = listTag + '#';
						text=text.replace(/<dl[^>]*?>/, "");
						break;
					case '<dt' :
						listTag = listTag + ';';
						text=text.replace(/<dt[^>]*?>/, "\n"+listTag+" ");
						break;
					case '<li' :
						if (text.search(/<li[^>]*?>\s*(<ul[^>]*?>|<ol[^>]*?>)/)==nextPos)
							text=text.replace(/<li[^>]*?>/, "");
						else
							text=text.replace(/\n?<li[^>]*?>/mi, "\n"+listTag+" ");
						break;
				}

				switch (text.substr(nextPos, 4))
				{
					case '<blo' :
						listTag = listTag + ':';
						if (text.search(/(<blockquote[^>]*?>\s*(<ul>|<ol>))|(<blockquote[^>]*?>\s*<blockquote[^>]*?>)/)==nextPos)
							text=text.replace(/<blockquote[^>]*?>/, "");
						else
							text=text.replace(/\n?<blockquote[^>]*?>/mi, "\n"+listTag+" ");
						break;
					case '</ul'	:
						listTag = listTag.substr(0, listTag.length-1);
						//if (text.search(/<\/ul>\s*(<\/li><\/ul>|<\/ol>)/)==nextPos)
						//   text=text.replace(/<\/ul>/, "");
						//else
						//text=text.replace(/<\/ul>/, '\n');
						//prevent newline after last blockquote
						if (listTag.length > 0)
							text=text.replace(/<\/ul>/, "");  // was \n
						else
							text=text.replace(/<\/ul>/, "\n");

						break;
					case '</ol' :
						listTag = listTag.substr(0, listTag.length-1);
						//prevent newline after last blockquote
						if (listTag.length > 0)
							text=text.replace(/<\/ol>/, "");
						else
							text=text.replace(/<\/ol>/, "\n");
						break;
					case '</dl' :
						listTag = listTag.substr(0, listTag.length-1);
						//prevent newline after last blockquote
						if (listTag.length > 0)
							text=text.replace(/<\/dl>/, "");
						else
							text=text.replace(/<\/dl>/, "\n");
						break;
					case '</dt' :
						text=text.replace(/<\/dt>/, "");
					case '</li' :
						text=text.replace(/\n?<\/li>/mi, "\n");
						break;
					case '</bl' :
						listTag = listTag.substr(0, listTag.length-1);
						if (text.search(/<\/blockquote>\s*<blockquote[^>]*?>/)==nextPos)
							text=text.replace(/\n?<\/blockquote>\s*<blockquote[^>]*?>/, "\n<blockquote>");
						else if (text.search(/<\/blockquote>\s*<\/blockquote>/)==nextPos)
							text=text.replace(/<\/blockquote>/, "");
						else
						//prevent newline after last blockquote //if no * or # is present
						if (listTag.length > 0 ) //&& (listTag.indexOf('#') >= 0 || listTag.indexOf('*') >= 0)
							text=text.replace(/<\/blockquote>/, "\n"+listTag+" ");
						else
							text=text.replace(/<\/blockquote>/, "");
						break;

				}
				nextPos = text.search(/(<ul|<ol|<li( |>)|<\/?dl|<\/?dt|<blockquote[^>]*?>|<\/li( |>)|<\/ul|<\/ol|<\/blockquote|<p( |>)|<\/p( |>)|<h[1-8]|<hr)/);
				// this is a rather expensive function in order to prevent system crashes.
				// if the text has not changed, text.search will find the same tag over and over again
				// Todo: Identify infinite loops and prevent
				if (oldText == text) {
					// Todo: i18n
					alert('Sorry, an infinite loop occurred. The editor had to shut down.\nPlease check your wiki page for errors.');
					break;
				}
			}
			//this._debugMsg("before tables", text);
			text = this._tables2wiki(text);

			//text = text.replace(/<div align="right">(.*?)<\/div>\n?/gmi, "\n<div style='text-align:right'>$1</div>\n")
			//text = text.replace(/<div align="center">(.*?)<\/div>\n?/gmi, "\n<div style='text-align:center'>$1</div>\n")
			//text = text.replace(/<div align="justify">(.*?)<\/div>\n?/gmi, "\n<div style='text-align:justify'>$1</div>\n")

			// alle Leerzeilen raus...
			//bin mir nicht sicher, ob der * bei \n* hier nicht zuviel des guten ist. Aber NB: Das wird der Reihe nach abgearbeitet, überflüssige Leerzeilen sollte es also nicht geben.
			// Syntax ist a bisserl kompliziert, aber ansonsten wird <pre> mit gelesen...
			//			text = text.replace(/\n*<p(\s+[^>]*?)?>(\s|<br \/>)<\/p>\s*/gi, "\n&nbsp;\n\n");
			//			text = text.replace(/\n*<p(\s+[^>]*?)?>(\s|&nbsp;)*?<\/p>\s*/gi, "\n");
			//    		text = text.replace(/\n*<p([^>]*?)?>(.*?)<\/p>\s*/gmi, "\n$2\n\n");

			//text = text.replace(/\n?<p[^>]*>(<span[^>]*hw_comment[^>]*>.*?<\/span>)<\/p>/gmi, "\n$1");
			//this._debugMsg("before cleanlines", text);
			//text = text.replace(/\n?<p class="hw_emptyline_first"[^>]*>(\s| |&nbsp;)*?<\/p>/gmi, "\n\n");
			//text = text.replace(/<br class="hw_emptyline"[^>]*>/g, "\n\n");
			//text = text.replace(/\n?<p class="hw_emptyline"[^>]*>(\s| |&nbsp;)*?<\/p>/gmi, "\n\n");
			//
			//this._debugMsg("before paragraph", text);
			//text = text.replace(/\n?<p(\s+[^>]*?)?>\s*(\s|<br ?\/>)\s*<\/p>/gmi, "\n\n");
			//text = text.replace(/\n?<p(\s+[^>]*?)?>(\s| |&nbsp;)*?<\/p>/gmi, "\n\n");
			//
			//THIS IS EXPERIMENTAL: If anything breaks, put in a second \n at the end
			// this._debugMsg("before paragraph", text);
			//text = text.replace(/\n?<p(\s+[^>]*?)?>(.*?)<\/p>/gmi, "\n$2\n");
			//text = text.replace(/\n\n\n+$/gmi, "\n\n");
			//alert (text.match(/\n/gmi));
			//this._debugMsg("before paragraph", text);
			//text = text.replace(/@@br_emptyline_first@@/gmi, "\n");
			//console.log(text);
			text = text.replace(/\n?@@br_emptyline_first@@/gmi, "\n");
			//text = text.replace(/@@br_emptyline@@/gmi, "");
			text = text.replace(/\n?@@br_emptyline@@/gmi, "");
			//console.log(text);
			//this._debugMsg("after paragraph", text);
			//Cleanup von falschen Image-URLs
			// TODO MRG (02.11.10 23:44): i18n
			text = text.replace(/\/Image:/g, "Image:");
			text = text.replace(/\/Bild:/g, "Bild:");
			text = text.replace(/\/File:/g, "File:");
			text = text.replace(/\/Datei:/g, "Datei:");
			//console.debug(text);
			//
			//Write back content of <pre> tags.
			//text = this._recoverSpecialTags(text);
			text = this._recoverPres(text);
			// make sure pre starts in a separate line
			text = text.replace(/([^^])?\n?<pre/gi, "$1\n<pre");
			//text = this._recoverSpecialTags(text);

			//Cleanup empty lines that exists if enter was pressed within an aligned paragraph
			text = text.replace(/<div[^>]*?>(\s|&nbsp;)*<\/div>/gmi, "");
			//Cleanup am Schluss löscht alle Zeilenumbrüche und Leerzeilen/-Zeichen am Ende.
			//Important: do not use m flag, since this makes $ react to any line ending instead of text ending
			text = text.replace(/((<p( [^>]*?)?>(\s|&nbsp;|<br\s?\/>)*?<\/p>)|<br\s?\/>|\s)*$/gi, "");
			text = text.replace(/<br class="hw_lastline"[^>]*>/g, '');
			text = text.replace(/^\n*/gi, '');
			textObject = {text : text};
			$(document).trigger('BSVisualEditorAfterHtmlToWiki', [textObject]);
			text = textObject.text;

			return text;
		},

		/**
		 * Output of debug messages.
		 * @param string headline Some context information.
		 * @param strin text The content that shall be debugged.
		 */
		_debugMsg : function(headline, text) {
			if (bsVisualEditorDebugMode) alert(headline+"\n"+text);
		}

	});

	// Register plugin
	tinymce.PluginManager.add('hwcode', tinymce.plugins.HWCodePlugin);
})();
