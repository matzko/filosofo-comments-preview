<?php
/*
Plugin Name: Filosofo Comments Preview
Plugin URI: http://www.ilfilosofo.com/blog/comments-preview/
Description: Filosofo Comments Preview lets you preview WordPress comments before you submit them.  It's highly configurable from the <a href="options-general.php?page=filosofo-comments-preview.php">admin control panel</a>, including optional <a href="http://en.wikipedia.org/wiki/Captcha">captcha</a> and JavaScript alert features.    
Version: 0.76
Author: Austin Matzko
Author URI: http://www.ilfilosofo.com/blog/
*/

/*  Copyright 2005  Austin Matzko  (email : if.website at gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define("FILOSOFOCPNAME", basename(__FILE__));

// initialize functions
if(!function_exists(get_settings))
	require_once(realpath('../../wp-config.php'));
//********************************************************************************
// Default values
//********************************************************************************

$filosofo_cp_version = .76;

$comments_template = <<<COMMENTSTEMPLATE
<li class="%alt_class" id="comment-<?php comment_ID() ?>">
<?php comment_text(); ?>  
<p><cite><?php comment_type(); ?> <?php _e('by'); ?> <a href="<?php comment_author_url(); ?>"><?php comment_author(); ?></a> &#8212; 
<?php comment_date(); ?> @ <a href="<?php echo get_comment_link(); ?>"><?php comment_time(); ?></a></cite><?php 
edit_comment_link('Edit This',' | ',' | '); ?></p>
</li>
COMMENTSTEMPLATE;
$comments_template = str_replace('$','\$',addslashes($comments_template));

$filosofo_cp_subpage_general_array_default = array('show_prev_button' => 1,
	'show_submit_button' => 1,
	'prev_button_text' => 'Preview',
	'prev_button_class' => 'button',
	'prev_button_id' => 'preview',
	'submit_button_text' => 'Post',
	'submit_button_class' => 'button',
	'submit_button_id' => 'submit',
	'comments_settings_show' => 1,
	'comments_settings_reverse' => 1,
	'comments_settings_oddcomment_class' => 'alt',
	'comments_settings_evencomment_class' => 'altB',
	'comments_header' => '<h3>Previous Comments</h3><ul class="commentlist" id="commentlist">',
	'comments_template' => $comments_template,
	'comments_footer' => '</ul>');

$filosofo_cp_alerts_array_default = array('activate' => 0,
	'form_id' => 'commentform',
	'name' => 1,
	'name_id' => 'author',
	'name_text' => 'Don\'t forget to fill in your name before submitting your comment.',
	'email' => 1,
	'email_id' => 'email',
	'email_text' => 'Don\'t forget to fill in your email address (it will not be shown publicly) before submitting your comment.',
	'captcha' => 1,
	'captcha_id' => 'captcha_field',
	'captcha_text' => 'Don\'t forget to fill in the \'captcha\' security code before submitting your comment.');

$filosofo_cp_captcha_array_default = array('show_captcha' => 0,
	'captcha_label' => 'Enter the code that you see in the image',
	'salt' => 'filosofo_cp' . rand(),
	'num_length' => 6,
	'field_length' => 28,
	'circles' => 5,
	'lines' => 1,
	'width' => 100,
	'height' => 40,
	'x_ord' => 20,
	'y_ord' => 10,
	'use_font' => 0,
	'font_path' => dirname(__FILE__),
	'angle' => 0,
	'font' => 5,
	'bgred' => 10,
	'bggreen' => 102,
	'bgblue' => 174,
	'txred' => 255,
	'txgreen' => 255,
	'txblue' => 255,
	'rperc' => 0.01,
	'gperc' => 0.51,
	'bperc' => 0.87);

$filosofo_cp_default_options = array('filosofo_cp_subpage_general_array' => $filosofo_cp_subpage_general_array_default,
	'filosofo_cp_alerts_array' => $filosofo_cp_alerts_array_default,
	'filosofo_cp_captcha_array' => $filosofo_cp_captcha_array_default);
//********************************************************************************
// end default values
//********************************************************************************

if (!class_exists('filosofo_cp')) {
class filosofo_cp {

function upgrade () {
global $filosofo_cp_version, $filosofo_cp_default_options;
	if (.72 > get_option('filosofo_cp_version'))
		update_option('filosofo_cp_subpage_general_array',$filosofo_cp_default_options['filosofo_cp_subpage_general_array']);
	update_option('filosofo_cp_version',$filosofo_cp_version);
} //end upgrade

function create_template($kind = 'standard') {
	if ('popup' == $kind) {
		$template_text = $this->get_the_files_content( TEMPLATEPATH . '/comments-popup.php');
		if ('' == $template_text)
			$template_text = $this->get_the_files_content( get_theme_root() . '/default/comments-popup.php');
	}
	else $template_text = $this->get_the_files_content( TEMPLATEPATH . '/index.php');

	$head_pos = strpos($template_text,'get_header(');
	$body_pos = strpos($template_text,'<body');
	$foot_pos = strpos($template_text,'get_footer(');
	$body_end_pos = strpos($template_text,'</body');

	if ($head_pos > $body_pos) 
		$header = substr($template_text,0,$head_pos) . 'get_header(); ?>';
	else
		$header = substr($template_text,0,$body_pos) . '<body>';

	if ($foot_pos && (($foot_pos < $body_end_pos) || !$body_end_pos))
        	$footer = '<?php ' . substr($template_text,-(strlen($template_text)-$foot_pos));
	elseif ($body_end_pos)
        	$footer = substr($template_text,-(strlen($template_text)-$body_end_pos));

	if ('popup' == $kind) {
		$divs[1] = <<<TEMPLATE_POP_UP

<div><strong><a href="javascript:window.close()"><?php _e("Close this window."); ?></a></strong></div>

<!-- // this is just the end of the motor - don't touch that line either :) -->
<?php } ?>
<p class="credit"><?php timer_stop(1); ?>
<?php echo sprintf(__("<cite>Powered by <a href=\"http://wordpress.org\"title=\"%s\"><strong>Wordpress</strong></a></cite>"),__("Powered by WordPress,  state-of-the-art semantic personal publishing platform.")); ?></p>
<?php // Seen at http://www.mijnkopthee.nl/log2/archive/2003/05/28/esc(18) ?>
<script type="text/javascript">
<!--
document.onkeypress = function esc(e) {
        if(typeof(e) == "undefined") { e=event; }
        if (e.keyCode == 27) { self.close(); }
}
// -->
</script>
TEMPLATE_POP_UP;

	}
	else {
		$div_attribs = array('id="wrapper"','id="pagecontent"','id="main"','id="content"','class="post"','class="widecolumn"');
		foreach ($div_attribs as $text) {
			if (!strpos($header,$text)) { 
				$divs[0] = $divs[0] . '<div ' . $text . '>';
				$divs[1] = $divs[1] . '</div>';
			} 
		}
	}
	$main_template = <<<TEMPLATE
<h2 id="comments">Your Comment Preview on %previewed_post_title:</h2>
%previewed_comment<p>by %previewed_author_link</p>
<hr />
<form action="%previewed_form_submit_path" method="post" id="commentform">
<?php if ( \$user_ID ) : ?>
<p>Logged in as <a href="<?php echo get_option('siteurl');  ?>/wp-admin/profile.php"><?php echo \$user_identity;?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout"  title="<?php _e('Log out of this account') ?>">Logout &raquo;</a></p>
<?php else : ?>
<p><input type="text" name="author" id="author" class="textarea" value="%previewed_author" size="28" tabindex="1" />
<label for="author"><?php _e('Name'); ?></label></p>
<p><input type="text" name="email" id="email" value="%previewed_email" size="28" tabindex="2" />
<label for="email"><?php _e('E-mail'); ?></label></p>
<p><input type="text" name="url" id="url" value="%previewed_url" size="28" tabindex="3" />
<label for="url"><?php _e('<acronym title="Uniform Resource Identifier">URI</acronym>'); ?></label></p>
<?php endif; ?>
<p><label for="comment"><?php _e('Your Comment'); ?></label><br />
<textarea name="comment" id="comment" cols="60" rows="10" tabindex="4">%previewed_raw_comment</textarea></p>
<p>%previewed_buttons</p>
</form>
%previewed_prev_comments
TEMPLATE;
return $header . $divs[0] . $main_template . $divs[1] . $footer;
}

function replace_button($content) {
	$content = str_replace("/wp-comments-post.php","/wp-content/plugins/" . FILOSOFOCPNAME, $content);
	return preg_replace('/<input.*name=("|\')submit("|\').*\/>/i',$this->submitbuttons(),$content);
} 

var $options = array();
function get_option($option) { // Looks up the setting for the name of the $option argument;  if it's not there it uses the default
global $filosofo_cp_default_options;
	$orig_option = $option;
	//set filosofo_cp_preview_template option to be specific to the current stylesheet 
	if($option == 'filosofo_cp_preview_template') { 
		$option = $option . '_' . $this->dirify(get_stylesheet());
	}
	//set filosofo_cp_preview_pop_up_template option to be specific to the current stylesheet
	elseif($option == 'filosofo_cp_preview_pop_up_template') {
		$option = $option . '_' . $this->dirify(get_stylesheet());
	}

	//check to see if the value has not already been loaded into the options array
	if (!array_key_exists($option, $this->options)) {
		//if the option doesn't exist yet in the db, then use default
		if (!get_option($option)) {  //warning: for options with value 0 it returns false, hence always the default!
			//special case of the variously named templates
			if ((preg_match('/^filosofo_cp_preview_template/',$option)) || (preg_match('/^filosofo_cp_preview_pop_up_template/',$option))) {
				if (preg_match('/^filosofo_cp_preview_pop_up_template/',$option))
					$kind = 'popup';
				else $kind = 'standard';
				//if there's not a default set for that template
				if (!array_key_exists($option, $filosofo_cp_default_options)) {
					$filosofo_cp_default_options[$option] = str_replace('$','\$',addslashes($this->create_template($kind))); 
				}
				//add it to the db and the options array
				add_option($option,$filosofo_cp_default_options[$option]);
				$this->options[$option] =  $filosofo_cp_default_options[$option];
			}
			//not dealing with a preview template
			else {    
				add_option($option,$filosofo_cp_default_options[$orig_option]);
				$this->options[$option] =  $filosofo_cp_default_options[$orig_option];
			}
		} 
		//else pull it from the database
		else {
			$this->options[$option] =  get_option($option);
		}
	} 
	return $this->options[$option];
} //end get_option

function add_options_page() { // adds the plugin options page to the admin options menu
global $wp_version;
	if (function_exists('add_options_page'))
		add_options_page('Filosofo Comments Preview Plugin', 'Comments Preview', 6, __FILE__,array(&$this,'options_page'));
} //end add_options_page

function options_page() { // configures the Filosofo Comments Preview admin options page
global $filosofo_cp_default_options, $filosofo_cp_subpage;
	//if the form has been submitted to be updated
	if (isset($_GET['updated']) && ($_GET['updated'] == 'true')) {
		$possible_options = array_keys($_POST);
		//if the options are part of an array
		if (isset($_GET['array'])) {
			foreach($possible_options as $option) {
				$temparray[$option] = trim($_POST[$option]);
				if (('rperc' == $option) || ('gperc' == $option) || ('bperc' == $option)) 
					$temparray[$option] = $temparray[$option]/100;
			}
			//if the reset button was pushed
			if (!empty($_POST['reset'])) {
				$this->options[$_GET['array']] =  $filosofo_cp_default_options[$_GET['array']];
				update_option($_GET['array'],$filosofo_cp_default_options[$_GET['array']]);
			}
			else {
				$this->options[$_GET['array']] =  $temparray;
				update_option($_GET['array'],$temparray);
			}
		}
		//else the options are not part of an array
		else {
			foreach($possible_options as $option) {
				$option = trim($option);
				//if the reset button was pushed
				if (!empty($_POST['reset'])) {
					//if a template
					if ((preg_match('/^filosofo_cp_preview_template/',$option)) || (preg_match('/^filosofo_cp_preview_pop_up_template/',$option))) {
						if (preg_match('/^filosofo_cp_preview_pop_up_template/',$option))
							$kind = 'popup';
						else $kind = 'standard';
						//if there's not a default set for that template
						if (!array_key_exists($option, $filosofo_cp_default_options))
							$this->options[$option] = str_replace('$','\$',addslashes($this->create_template($kind))); 
						else
							$this->options[$option] = $filosofo_cp_default_options[$option];
					}
					update_option($option,$this->options[$option]);
				}
				else {
					$_POST[$option] = trim($_POST[$option]);
					$this->options[$option] =  $_POST[$option];
					update_option($option,$_POST[$option]);
				}
			}
		}  //end else options not part of an array
	}
$subpage_array = array(1 => __('General'), 2 => __('Preview Page Templates'), 3 => __('Captcha Options'), 4 => __('Set Alerts'));
?><ul id="adminmenu3"><?php
	foreach ($subpage_array as $id => $subpage_name) {
		echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?page=' . FILOSOFOCPNAME . '&amp;subpage=' . $id . '"'; 
		if ($id == $filosofo_cp_subpage) echo 'class="current"'; 
		echo ">$subpage_name</a></li>\n";
	}
?></ul>
<div class="wrap">
<h2><?php _e('Options for the Filosofo Comments Preview Plugin') ?></h2><?php
	if (1 == $filosofo_cp_subpage) $this->subpage_general(); 
	elseif (2 == $filosofo_cp_subpage) $this->subpage_preview_template();
	elseif (3 == $filosofo_cp_subpage) $this->subpage_captcha();
	elseif (4 == $filosofo_cp_subpage) $this->subpage_alerts();
?></div><?php
} //end options_page function

function subpage_header () { // prints the header for the admin options pages
global $filosofo_cp_subpage;
	$filosofo_cp_subpage = 1;
	if (isset($_GET['subpage'])) $filosofo_cp_subpage = $_GET['subpage'];
$captcha_array = $this->get_option('filosofo_cp_captcha_array');
?>
<style type="text/css">
<!--
#adminmenu3 li {
	display: inline;
	line-height: 100%;
	list-style: none;
	text-align: center;
}

#adminmenu3 {
	background: black;
	border-top: 2px solid #707070;
	border-bottom: none;
	height: 21px;
	margin: 0;
	padding: 0 4em;
}
                         
#adminmenu3 .current {
	background: #f2f2f2;
	border-right: 2px solid #4f4f4f;
	color: #000;
}
                         
#adminmenu3 a {
	border: none;
	color: #fff;
	font-size: 12px;
	padding: 3px 5px 4px;
}
                         
#adminmenu3 a:hover {
	background: #f0f0f0;
	color: #393939;
}
                         
.filosofo_cp_deletepost:hover {
	background: #ce0000;
	color: #fff;
}

.filosofo_cp_edittext div {
	margin-right: 190px;
}

textarea.filosofo_cp_edittext  {
	font: small 'Courier New', Courier, monospace;
	width: 99%;
}

<?php 
if (3 == $filosofo_cp_subpage) {
?>
div.slidercasing {
	margin: 2px;
	padding: 0px;
	border: 2px solid;
        border-bottom-color: black;
        border-top-color: #999999;
        border-left-color: #757575;
        border-right-color: #545454;
	background-color:#DDDDDD;
	width: 280px;
	z-index:4;
	-moz-border-radius: 4px;
}

div.slidertrack {
	border:1px solid #000000;
	background-color:#FFFFFF;
	z-index:5;
	padding: 1px;
	margin: 1px;
	-moz-border-radius: 2px;
}

div.sliderbar {
	position:absolute;
	z-index:6;
	border-bottom-color: black;
	border-top-color: #999999;
	border-left-color: #757575;
	border-right-color: #545454; 
        border-style: solid;
        border-width: 2px;
	background-color: #5E5E5E;
	width:25px;		
	padding:0px;
	height:10px;
	cursor: pointer;
	-moz-border-radius: 9px;
}

div.shader {
	height:7px; 
	width: 22px;
	background-color: #5E5E5E; 
	-moz-border-radius: 7px; 
	margin-top: 1px;
	margin-left: 2px;
}
<?php } ?>
-->
</style>
<?php
if (3 == $filosofo_cp_subpage) {
?>
<script language="JavaScript" type="text/javascript">
//<![CDATA[
function findPosX(obj) {
	var curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft;
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function findPosY(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop;
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
	curtop += obj.y;
	return curtop;
}

function filosofo_cp_addEvent(obj, evType, fn){
// from http://www.sitepoint.com/article/structural-markup-javascript
if (obj.addEventListener){
   obj.addEventListener(evType, fn, false); //false to make sure it happens during event bubbling, not capturing.  
   return true;
 } else if (obj.attachEvent){
   var r = obj.attachEvent("on"+evType, fn);
   return r;
 } else {
   return false;
 }
}

function slider(slider_name,casing_name,formfield_name,width,field_max) {
	var casing = document.getElementById(casing_name);
	var formfield  = document.getElementById(formfield_name);
	formfield.origvalue = formfield.value;
	var currentslider = document.getElementById(slider_name);
	if ((0 == width) || (0 == field_max)) var ratio = 1; else var ratio = field_max/width || 1;
	if (!currentslider || !casing || !formfield) return;

	var rangeMin = findPosX(casing);
	var rangeMax = findPosX(casing) + width;
	var tempX = 0;
	var posX = 0;
	var adjuststate = 0;
	var tempA = 0;
	var tempB = 0;
	var offsetPosX = 0;
	var mouseoffset = 0;
	
	currentslider.style.top = findPosY(casing)-2 + 'px';
	currentslider.style.left = findPosX(casing)*1 + parseInt((formfield.value*1)/ratio) + 'px';

	this.cap = function () {		
		if (adjuststate == 0) { 
			adjuststate = 1; 
			tempA = posX; 
		} else { 
			adjuststate = 0;
		}
	}

	this.mouse = function(e) {
		if(navigator.appName == "Netscape"){
			tempX = e.pageX;
		} else {
			tempX = window.event.clientX + window.document.body.scrollLeft;
		}
		if (tempX <= 0) {tempX = 0} 
		posX = tempX
	
		if (adjuststate == 1){ 	// we need to move the slider
			// having moved we have the captured frozen point.
			tempB = tempA;
			tempA = posX;

			// work out the mouse in relation to the topos
			mouseoffset = tempB - findPosX(currentslider);
			offsetPosX = posX - mouseoffset;
		
			if (offsetPosX < rangeMin){
                       		offsetPosX = rangeMin;
                	}
			if (offsetPosX > rangeMax){
                        	offsetPosX = rangeMax;
                	}
			currentslider.style.left = offsetPosX + 'px';
			formfield.value = parseInt((offsetPosX-rangeMin)*ratio);	
			formfield.selectedIndex = parseInt((offsetPosX-rangeMin)*ratio);
		}
		return true
	}
	filosofo_cp_addEvent(currentslider,'mousedown',this.cap);
	filosofo_cp_addEvent(document, 'mouseup',function () { adjuststate = 0; });
	filosofo_cp_addEvent(document, 'mousemove',this.mouse);
	filosofo_cp_addEvent(formfield, 'change',function () { 
		var position = rangeMin*1 + parseInt((formfield.value*1)/ratio);
		if (rangeMax < position) position = rangeMax;
		if (rangeMin > position) position = rangeMin;
		currentslider.style.left = position + 'px';
	});
	if (document.getElementById('captcha_settings_form')) {
		var captcha_form = document.getElementById('captcha_settings_form');
		        filosofo_cp_addEvent(captcha_form, 'reset',function () {
		                var position = rangeMin*1 + parseInt((formfield.origvalue*1)/ratio);
                		if (rangeMax < position) position = rangeMax;
                		if (rangeMin > position) position = rangeMin;
                		currentslider.style.left = position + 'px';
			});

	}

}

filosofo_cp_addEvent(window, 'load', setItUp);
filosofo_cp_addEvent(document, 'mouseup',function () { changePreview(); });

function setItUp() {
	s1 = new slider("sliderbar1","bgred_casing","bgred",255,255);
	s2 = new slider("sliderbar2","bggreen_casing","bggreen",255,255);
	s3 = new slider("sliderbar3","bgblue_casing","bgblue",255,255);
	s4 = new slider("sliderbar4","txred_casing","txred",255,255);
	s5 = new slider("sliderbar5","txgreen_casing","txgreen",255,255);
	s6 = new slider("sliderbar6","txblue_casing","txblue",255,255);
	s7 = new slider("sliderbar7","rperc_casing","rperc",255,100);
	s8 = new slider("sliderbar8","gperc_casing","gperc",255,100);
	s9 = new slider("sliderbar9","bperc_casing","bperc",255,100);

	<?php foreach ($captcha_array as $key => $value) {
		echo 'if (document.getElementById("' . $key . '")) { ' . "\n";
		echo 'var ' . $key . ' =  document.getElementById("' . $key . '");' . "\n";
		echo 'filosofo_cp_addEvent(' . $key . ',"change",changePreview);' . "\n";
		echo "}\n";
	}
	?>
	if (document.getElementById('restore')) {
		var restore_button = document.getElementById('restore');	
		filosofo_cp_addEvent(restore_button,"mouseup",function() { setTimeout('changePreview()',10); });
	}
}

function encodeMyHtml(html) {
	var encodedHtml = escape(html);
	encodedHtml = encodedHtml.replace(/\//g,"%2F");
	encodedHtml = encodedHtml.replace(/\?/g,"%3F");
	encodedHtml = encodedHtml.replace(/=/g,"%3D");
	encodedHtml = encodedHtml.replace(/&/g,"%26");
	encodedHtml = encodedHtml.replace(/@/g,"%40");
	return encodedHtml;
} 

function changePreview() {
	var srcstring = '<?php echo get_option('siteurl'); ?>/wp-content/plugins/<?php echo FILOSOFOCPNAME; ?>?test_num=1&the_num=123456789012345678901234567890';
	var picture = document.getElementById("current_captcha_image");
	if (!picture) return;
	<?php foreach ($captcha_array as $key => $value) {
		echo 'if (document.getElementById("' . $key . '")) { ' . "\n";
		echo $key . ' =  document.getElementById("' . $key . '");' . "\n";
		echo 'srcstring = srcstring + \'&' . $key . '=\' + encodeMyHtml(' . $key . '.value);' . "\n";
		echo "}\n";
	}
	?>
	picture.setAttribute('src', srcstring);
}
//]]>
</script>
<?php } 
}  //end function subpage_header 

function subpage_general()  { // prints the general options subpage
global $filosofo_cp_default_options; 
$subpage_general_array = $this->get_option('filosofo_cp_subpage_general_array');
?> 
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=1&amp;updated=true&amp;array=filosofo_cp_subpage_general_array">
	<fieldset class="options">
		<legend><?php _e('Button settings'); ?></legend>
		<table>
			<tr>
				<td><label for="show_prev_button"><?php _e('Show preview button?'); ?></label></td>
				<td><select name="show_prev_button" id="show_prev_button">
					<option value="1" <?php if ($subpage_general_array['show_prev_button']== 1) {echo 'selected="selected"';} ?>><?php _e('Yes'); ?></option>
					<option value="0" <?php if ($subpage_general_array['show_prev_button']== 0) {echo 'selected="selected"';} ?>><?php _e('No'); ?></option>
				</select></td>
			</tr>
			<tr>
				<td><label for="show_submit_button"><?php _e('Show submit button where?'); ?></label></td>
				<td><select name="show_submit_button" id="show_submit_button">
					<option value="1" <?php if ($subpage_general_array['show_submit_button']== 1) {echo 'selected="selected"';} ?>><?php _e('All pages'); ?></option>
					<option value="0" <?php if ($subpage_general_array['show_submit_button']== 0) {echo 'selected="selected"';} ?>><?php _e('Just preview page'); ?></option>
				</select></td>
			</tr>
		</table>
		<table>
			<tr><td></td><th><?php _e('Button Text'); ?></th><th><?php _e('Button Class'); ?></th><th><?php _e('Button Id'); ?></th></tr>
			<tr><th><?php _e('Preview Button'); ?></th><td><input name="prev_button_text" type="text" id="prev_button_text" value="<?php echo htmlspecialchars(stripslashes($subpage_general_array['prev_button_text'])); ?>" size="15" /></td><td><input name="prev_button_class" type="text" id="prev_button_class" value="<?php echo $subpage_general_array['prev_button_class'] ?>" size="15" /></td><td><input name="prev_button_id" type="text" id="prev_button_id" value="<?php echo $subpage_general_array['prev_button_id'] ?>" size="15" /></td></tr>
			<tr><th><?php _e('Submit Button'); ?></th><td><input name="submit_button_text" type="text" id="submit_button_text" value="<?php echo htmlspecialchars(stripslashes($subpage_general_array['submit_button_text'])); ?>" size="15" /></td><td><input name="submit_button_class" type="text" id="submit_button_class" value="<?php echo $subpage_general_array['submit_button_class']; ?>" size="15" /></td><td><input name="submit_button_id" type="text" id="submit_button_id" value="<?php echo $subpage_general_array['submit_button_id']; ?>" size="15" /></td></tr>
		</table>
		
		
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Comments settings'); ?></legend>
		<table>
			<tr>
				<td><label for="comments_settings_show"><?php _e('Show previous comments on the preview page?'); ?></label></td>
				<td><select name="comments_settings_show" id="comments_settings_show">
					<option value="1" <?php if ($subpage_general_array['comments_settings_show']== 1) {echo 'selected="selected"';} ?>><?php _e('Yes'); ?></option>
					<option value="0" <?php if ($subpage_general_array['comments_settings_show']== 0) {echo 'selected="selected"';} ?>><?php _e('No'); ?></option>
				</select></td>
			</tr>
			<tr>
				<td><label for="comments_settings_reverse"><?php _e('In what order should we display the previous comments?'); ?></label></td>
				<td><select name="comments_settings_reverse" id="comments_settings_reverse">
					<option value="1" <?php if ($subpage_general_array['comments_settings_reverse']== 1) {echo 'selected="selected"';} ?>><?php _e('Newest to oldest'); ?></option>
					<option value="0" <?php if ($subpage_general_array['comments_settings_reverse']== 0) {echo 'selected="selected"';} ?>><?php _e('Oldest to newest'); ?></option>
				</select></td>
			</tr>
		</table>
		<hr />
		<table>
			<tr><th colspan="2"><?php _e('Customize the template for previous comments'); ?></th></tr>
			<tr><td rowspan="2"><?php _e('Alternate classes for every other comment,<br /> called by inserting <code>%alt_class</code> in the template below:'); ?></td><td><input name="comments_settings_oddcomment_class" type="text" id="comments_settings_oddcomment_class" value="<?php echo $subpage_general_array['comments_settings_oddcomment_class']; ?>" size="15" /></td></tr>
			<tr><td><input name="comments_settings_evencomment_class" type="text" id="comments_settings_evencomment_class" value="<?php echo $subpage_general_array['comments_settings_evencomment_class']; ?>" size="15" /></td></tr>
			<tr>
				<td><label for="comments_header"><?php _e('The markup for the top of the previous comments'); ?></label></td>
				<td><textarea class="filosofo_cp_edittext" cols="70" rows="3" name="comments_header" id="comments_header"><?php echo htmlspecialchars(stripslashes($subpage_general_array['comments_header'])); ?>
				</textarea></td>
			</tr>
			<tr>
				<td><label for="comments_template"><?php _e('The template for each previous comment'); ?></label></td>
				<td><textarea class="filosofo_cp_edittext" cols="70" rows="13" name="comments_template" id="comments_template" ><?php echo htmlspecialchars(stripslashes($subpage_general_array['comments_template'])); ?>
				</textarea></td>
			</tr>
			<tr>
				<td><label for="comments_footer"><?php _e('The markup for the bottom of the previous comments'); ?></label></td>
				<td><textarea class="filosofo_cp_edittext" cols="70" rows="3" name="comments_footer" id="comments_footer"><?php echo htmlspecialchars(stripslashes($subpage_general_array['comments_footer'])); ?>
				</textarea></td>
			</tr>
		</table>
		<p><?php _e('Edit the complete template for this theme at the '); ?><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=2"><?php _e('template page'); ?></a>.</p>
	</fieldset>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save General Comments Preview Options') ?> &raquo;" />
		<input class="filosofo_cp_deletepost" type="submit" id="deletepost" name="reset" value="<?php _e('Reset General Comments Preview Options to default') ?> &raquo;" onclick="return confirm('You are about to reset your options for \'General Comments Preview\'.\n  \'Cancel\' to stop, \'OK\' to delete.')" />
	</p>
</form>
<?php
} //end function subpage_general

function subpage_preview_template() { // prints the preview options subpage 
global $filosofo_cp_default_options;
	$current_stylesheet = get_stylesheet(); 
	$themetoedit = $this->dirify($current_stylesheet);
	if (!empty($_POST['themetoedit'])) $themetoedit = $_POST['themetoedit'];
	$themes = get_themes(); 
?>
<form name="theme" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=2"> 
	<label for="themetoedit"><?php _e('Select corresponding theme:') ?></label>
	<select name="themetoedit" id="themetoedit">
		<?php
		foreach ($themes as $a_theme) {
			$theme_name = $a_theme['Name'];
			$theme_id = $this->dirify($a_theme['Stylesheet']);
			if ($theme_id == $themetoedit) $selected = " selected='selected'";
			else $selected = '';
			$theme_name = wp_specialchars($theme_name, true);
			echo "\n\t<option value=\"$theme_id\" $selected>$theme_name</option>";
		}
		?>
	</select>
	<input type="submit" name="submittheme" id="submittheme" value="<?php _e('Select') ?> &raquo;" />
</form>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=2&amp;updated=true">
<input type="hidden" name="themetoedit" id="themetoedit2" value="<?php echo $themetoedit ?>" />
	<fieldset class="options">
		<legend>Comments Preview Template for the <?php echo $themetoedit; ?> Stylesheet</legend>
		<p><?php _e('You can edit the template below, using these variables, XHTML, or PHP'); ?></p>
		<p><?php _e('Look for more theme templates or add your own '); ?><a href="http://www.ilfilosofo.com/blog/filosofo-comments-preview-templates/"><?php _e('here'); ?></a>.</p>
			<pre>
 %previewed_author_link      - The previewed comment's author as a link, if applicable
 %previewed_author           - The previewed comment's author
 %previewed_buttons          - The submit buttons
 %previewed_comment          - The previewed comment, formatted and filtered
 %previewed_email            - The previewed comment author's email address
 %previewed_form_submit_path - The path to which the form submits
 %previewed_prev_comments    - The previous comments, if activated
 %previewed_raw_comment      - The comment in raw XHTML form, for the &lt;textarea&gt;
 %previewed_url              - The previewed comment author's URL, if applicable
 %previewed_post_title       - The title of the post for which one is leaving a comment
			</pre>
		<div class="filosofo_cp_edittext">
			<textarea class="filosofo_cp_edittext" cols="70" rows="25" name="<?php echo 'filosofo_cp_preview_template_' . $themetoedit ?>" id="<?php echo 'filosofo_cp_preview_template_' . $themetoedit ?>" tabindex="2">
			<?php echo trim(htmlspecialchars(stripslashes($this->get_option('filosofo_cp_preview_template_' . $themetoedit)))); ?>
			</textarea>
		</div>
	</fieldset>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save changes to the Preview template for the '); echo $themetoedit; _e(' stylesheet'); ?> &raquo;" />
		<input type="submit" class="filosofo_cp_deletepost" id="deletepost" name="reset" value="<?php _e('Reset this Preview Template to Default') ?> &raquo;" onclick="return confirm('You are about to reset your Preview Template.\n  \'Cancel\' to stop, \'OK\' to reset.')" />
	</p>
</form>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=2&amp;updated=true">
	<fieldset class="options">
		<legend><?php _e('Pop-up Comments Preview Template for the '); ?><?php echo $themetoedit; ?> <?php _e('Stylesheet'); ?></legend>
		<div class="filosofo_cp_edittext">
			<textarea class="filosofo_cp_edittext" cols="70" rows="25" name="<?php echo 'filosofo_cp_preview_pop_up_template_' . $themetoedit ?>" id="<?php echo 'filosofo_cp_preview_pop_up_template_' . $themetoedit ?>" tabindex="2">
			<?php echo trim(htmlspecialchars(stripslashes($this->get_option('filosofo_cp_preview_pop_up_template_' . $themetoedit)))); ?>
			</textarea>
		</div>
	</fieldset>
	<p class="submit">
		<input type="submit" name="Submit2" value="<?php _e('Save changes to the Pop-up Comments Preview template for the '); echo $themetoedit; _e(' stylesheet'); ?> &raquo;" />
		<input type="submit" class="filosofo_cp_deletepost" id="deletepost2" name="reset" value="<?php _e('Reset this Pop-up Comments Preview Template to Default') ?> &raquo;" onclick="return confirm('You are about to reset your Preview Template.\n  \'Cancel\' to stop, \'OK\' to reset.')" />
	</p>
</form>
<?php
} //end function subpage_preview_template

function subpage_captcha() { // prints the captcha options subpage
	$captcha_array = $this->get_option('filosofo_cp_captcha_array');
	$i = 1;
	while ($i <= 9) {
		echo '<div id="sliderbar' . $i . '" class="sliderbar" style="top: 6px; left: -500px;"><div class="shader"></div></div>' . "\n";
		$i++;
	}
?>
<form method="post" id="captcha_settings_form" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=3&amp;updated=true&amp;array=filosofo_cp_captcha_array">
	<fieldset class="options">
		<legend><?php _e('Captcha Settings'); ?></legend>
		<p><?php _e('A "<acronym title="completely automated public Turing test to tell computers and humans apart">captcha</acronym>" requires commenters to enter a code displayed in an image before submitting their comments.'); ?>
		</p>
		<label for="show_captcha"><?php _e('Use the captcha?'); ?></label>
		<select name="show_captcha" id="show_captcha">
			<option value="0" <?php if ($captcha_array['show_captcha']== 0) {echo 'selected="selected"';} ?>><?php _e('No'); ?></option>
			<option value="1" <?php if ($captcha_array['show_captcha']== 1) {echo 'selected="selected"';} ?>><?php _e('Yes: on the initial page'); ?></option>
			<option value="2" <?php if ($captcha_array['show_captcha']== 2) {echo 'selected="selected"';} ?>><?php _e('Yes: on every comment page'); ?></option>
		</select>
		<p><?php _e('Choosing "No" disables the captcha. <br />Choosing "Yes: on the initial page" requires the captcha once, but it does not require it after the first preview.  <br />Choosing "Yes: on every comment page" requires commenters to enter the captcha code every time they preview or submit.'); ?>
		</p>
		<hr />
		<div style="float:right;"><p><?php _e('Saved Captcha appearance:'); ?></p><br /><img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/<?php echo FILOSOFOCPNAME; ?>?captcha_image=yes&amp;random_num=123456" title="Sample Captcha Image" id="sample_captcha_image" alt="" />
		<p><?php _e('Captcha Preview:'); ?></p><br />
		<img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/<?php echo FILOSOFOCPNAME; ?>?test_num=true&amp;the_num=1234567890<?php $temp_array = $captcha_array; $temp_array['rperc'] = $temp_array['rperc']*100; $temp_array['gperc'] = $temp_array['gperc']*100; $temp_array['bperc'] = $temp_array['bperc']*100; foreach ($temp_array as $key => $value)	echo '&amp;' . $key . '=' . $value; ?>" title="Sample Current Captcha Image" id="current_captcha_image" alt='' />
		</div>
		<div>
			<?php if (!function_exists('imagestring')) { ?>
				<p><strong style="color:red"><?php _e('Your server does not appear to have the <a href="http://www.boutell.com/gd/">GD image library</a> installed, so the captcha probably will not appear correctly.'); ?></strong></p>
			<?php } ?>
			<table>
				<tr><th colspan="3"><?php _e('Fine-tune the captcha features'); ?></th></tr>
				<?php $captcha_features = array('captcha_label' => __('The label for the captcha text box'),
				'num_length' => __('The length of the number that appears'),
				'field_length' => __('The length of the captcha text box'),
				'circles' => __('The number of background ellipses'),
				'lines' => __('The number of horizontal lines'),
				'width' => __('The width in pixels of the captcha image'),
				'height' => __('The height in pixels of the captcha image'),
				'x_ord' => __('The x-coordinate of the image text'),
				'y_ord' => __('The y-coordinate of the image text'));
				foreach ($captcha_features as $id => $feature_name) {
					echo '<tr><td><label for="' . $id . '">' . $feature_name . '</label></td><td colspan="2"><input name="' . $id . '" type="text"  id="' . $id . '" value="' . htmlspecialchars(stripslashes($captcha_array[$id])) . '"';
					if ('captcha_label' == $id) echo 'size="50"'; else echo 'size="10"'; 
					echo '/></td></tr>';	
				}
				if (!function_exists('imagettftext')) { 
				 	$captcha_array['use_font'] = 0;
					$use_font_disabled = 'disabled="disabled"';		
					$disabled_style = 'style="background-color: #ccc"'; ?>		 
					<tr <?php echo $disabled_style; ?>><td colspan="3"><?php _e('In order to create custom fonts, your server must have the <a href="http://www.boutell.com/gd/">GD image library</a> and the <a href="http://www.freetype.org/">TrueType</a> libraries installed; however, it seems at least one is not.'); ?></td></tr>
				<?php } ?>
				<tr <?php echo $disabled_style; ?>><td><label for="use_font"><?php _e('Use a custom TrueType font for the captcha image'); ?></label></td><td colspan="2">	
					<select name="use_font" id="use_font" <?php echo $use_font_disabled; ?>>
						<option value="0" <?php if ($captcha_array['use_font']== 0) {echo 'selected="selected"';} ?>><?php _e('No'); ?></option>
						<option value="1" <?php if ($captcha_array['use_font']== 1) {echo 'selected="selected"';} ?>><?php _e('Yes'); ?></option>
					</select></td></tr>
				<?php if ($captcha_array['use_font'] && !is_file($captcha_array['font_path'])) { ?>
				<tr style="background-color: #ccc"><td colspan="3"><?php _e('The path below does not seem to point to a file.'); ?></td></tr>
 				<?php } ?>
				<tr <?php echo $disabled_style; ?>><td><label for="font_path"><?php _e('The path to the custom TrueType font file'); ?></label></td><td colspan="2"><input name="font_path" type="text" id="font_path" <?php echo $use_font_disabled; echo $disabled_style; ?> value="<?php echo $captcha_array['font_path']; ?>" size="50" /></td></tr>
				<tr <?php echo $disabled_style; ?>><td><label for="angle"><?php _e('The angle of the custom font text'); ?></label></td><td colspan="2"><input name="angle" type="text" id="angle" value="<?php echo $captcha_array['angle']; ?>" size="10" /></td></tr>
				<tr><td><label for="font"><?php _e('Font size'); ?></label></td>
					<td colspan="2"><select name="font" id="font" size=""><?php
						for ($i=1;$i<6;$i++) {
							$selected = ($captcha_array['font']==$i) ? 'selected="selected"' : '';				
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<?php $rgb_dropdowns = array('bgred' => __('RGB red setting for the background'),
				'bggreen' => __('RGB green setting for the background'),
				'bgblue'  => __('RGB blue setting for the background'),
				'txred'   => __('RGB red setting for the text'),
				'txgreen' => __('RGB green setting for the text'),
				'txblue'  => __('RGB blue setting for the text'));
				foreach ($rgb_dropdowns as $id => $label) {
					echo "<tr><td><label for='$id'>$label</label></td><td><select name='$id' id='$id' size=''>";
					for ($i=0;$i<256;$i++) {
						$selected = ($captcha_array[$id] == $i) ? 'selected="selected"' : '';
						echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
					}
					echo "</select></td><td><div id='" . $id . "_casing' class='slidercasing'><div id='" . $id . "_track' class='slidertrack'></div></div></td></tr>";
				} ?>
				<tr><td><label for="rperc"><?php _e('% Variation in background color of RGB red'); ?></label></td><td><input name="rperc" type="text" id="rperc" value="<?php echo $captcha_array['rperc']*100; ?>" size="10" /></td><td><div id="rperc_casing" class="slidercasing"><div id="rperc_track" class="slidertrack"></div></div></td></tr>
				<tr><td><label for="gperc"><?php _e('% Variation in background color of RGB green'); ?></label></td><td><input name="gperc" type="text" id="gperc" value="<?php echo $captcha_array['gperc']*100; ?>" size="10" /></td><td><div id="gperc_casing" class="slidercasing"><div id="gperc_track" class="slidertrack"></div></div></td></tr>
				<tr><td><label for="bperc"><?php _e('% Variation in background color of RGB blue'); ?></label></td><td><input name="bperc" type="text" id="bperc" value="<?php echo $captcha_array['bperc']*100; ?>" size="10" /></td><td><div id="bperc_casing" class="slidercasing"><div id="bperc_track" class="slidertrack"></div></div></td></tr>
			</table>
		</div>
	</fieldset>
	<p class="submit">
		<input type="reset" name="restore" id="restore" value="<?php _e('Restore Captcha settings to their saved values'); ?> &raquo;" />
		<input type="submit" name="submit" value="<?php _e('Save changes to the Captcha settings'); ?> &raquo;" />
		<input type="submit" class="filosofo_cp_deletepost" id="reset" name="reset" value="<?php _e('Reset the Captcha settings to Default') ?> &raquo;" onclick="return confirm('You are about to reset the Captcha settings.\n  \'Cancel\' to stop, \'OK\' to reset.')" />
	</p>
</form>
<?php
} //end function subpage_captcha

function subpage_alerts() { // prints the alerts options subpage
global $filosofo_cp_default_options; 
	$alerts_array = $this->get_option('filosofo_cp_alerts_array');
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo FILOSOFOCPNAME; ?>&amp;subpage=4&amp;updated=true&amp;array=filosofo_cp_alerts_array">
	<fieldset class="options">
		<legend><?php _e('Set Alerts'); ?></legend>
		<p><?php _e('Alerts allow you to warn your commenters before they submit their comments with required fields left blank.'); ?></p>
		<label for="activate"><?php _e('Activate JavaScript Alerts?'); ?></label>
		<select name="activate" id="activate">
			<option value="1" <?php if ($alerts_array['activate']== 1) {echo 'selected="selected"';} ?>>Yes</option>
			<option value="0" <?php if ($alerts_array['activate']== 0) {echo 'selected="selected"';} ?>>No</option>
		</select>
		<hr />
		<label for="form_id"><?php _e('The comment form\'s "id" attribute value') ?>: <small><?php _e('(Most likely the default is correct)') ?></small></label>
		<input name="form_id" type="text" id="form_id" value="<?php echo htmlspecialchars(stripslashes($alerts_array['form_id'])); ?>"  size="20" />
		<hr />
		<table>
		<?php $alert_fields = array('name' => 'Name', 'email' => 'Email', 'captcha' => 'Captcha');
		foreach ($alert_fields as $id => $field_name) {
			echo "<tr><td><label for='$id'>" . sprintf(__('Alert commenters that they have not filled in the "%s" field?'),$field_name) . '</label></td>';
			echo "<td><select name='$id' id='$id'>";
			foreach (array(1 => 'Yes', 0 => 'No') as $binary => $binary_text) {
				echo "<option value='$binary'";
				if ($alerts_array[$id]== $binary) echo 'selected="selected"'; 
				echo ">$binary_text</option>";
			}
			echo '</select></td><td><label for="' . $id . '_text">' . __('Text for warning:') . '</label></td>';
			echo '<td><input name="' . $id . '_text" type="text" id="' . $id . '_text" value="' . htmlspecialchars(stripslashes($alerts_array[$id . '_text'])) . '" size="50"  /></td>'; 
			echo '<td><label for="' . $id . '_id">' . sprintf(__('The "%s" field\'s "id" attribute value'), $field_name) . ':<br /><small>' . __('(Most likely the default is correct)') . '</small></label></td>';
			echo '<td><input name="' . $id . '_id" type="text" id="' . $id . '_id" value="' . htmlspecialchars(stripslashes($alerts_array[$id . '_id'])) . '" size="10"  /></td></tr>';
		} ?>		
		</table>
	</fieldset>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save changes to the Alerts'); ?> &raquo;" />
		<input type="submit" class="filosofo_cp_deletepost" id="reset" name="reset" value="<?php _e('Reset the Alerts to Default') ?> &raquo;" onclick="return confirm('You are about to reset the Alerts.\n  \'Cancel\' to stop, \'OK\' to reset.')" />
	</p>
</form>
<?php
} // end subpage_alerts()

function alert_scripts() { // prints out JavaScript alert scripts as needed
global $filosofo_cp_default_options;
	$alerts_array = $this->get_option('filosofo_cp_alerts_array');
	if ($alerts_array['activate']) {
		$javascript_text = <<<JAVASCRIPT
<script type="text/javascript">
//<![CDATA[
function addEvent(obj, evType, fn){
// from http://www.sitepoint.com/article/structural-markup-javascript
	if (obj.addEventListener){
   		obj.addEventListener(evType, fn, false); //false to make sure it happens during event bubbling, not capturing.  see http://www.quirksmode.org/js/events_order.html and http://www.quirksmode.org/js/events_advanced.html
   		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	} else {
		return false;
	}
}

function giveidFocus(idname){
	if (document.getElementById) {  
		var thingtogetfocus=document.getElementById(idname);  
		thingtogetfocus.focus();
	} 
} //end giveidFocus

function checker () {}
checker.fieldAlert = function(fieldid,alertmsg) {
// alerts a user if she hasn't filled in a given field, then takes her to the field
// args: fieldid--the id of the field
//  alertmsg--the message to be alerted to

//check that the id exists
if (document.getElementById(fieldid)) {
	//check if it's blank
	var field = document.getElementById(fieldid);
	if (field.value == '') {
		alert(alertmsg);
		giveidFocus(fieldid);
		return true;
	}
	else {return false}
}

} //end function fieldAlert

checker.noBlankRequireds = function() {
// returns false on a submit if certain fields are left blank
var return_value = true;
JAVASCRIPT;
	if ($alerts_array['captcha'])
		$javascript_text .= 'if (checker.fieldAlert(\'' .  $alerts_array['captcha_id'] . '\',\'' . htmlspecialchars($alerts_array['captcha_text']) . '\')) { return_value = false; }' . "\n";
	if (get_settings('require_name_email') && $alerts_array['email']) 
		$javascript_text .= 'if (checker.fieldAlert(\'' .  $alerts_array['email_id'] . '\',\'' . htmlspecialchars($alerts_array['email_text']) . '\')) { return_value = false; }'. "\n";
	if (get_settings('require_name_email') && $alerts_array['name']) 
		$javascript_text .= 'if (checker.fieldAlert(\'' .  $alerts_array['name_id'] . '\',\'' . htmlspecialchars($alerts_array['name_text']) . '\')) { return_value = false; }'. "\n";
	$javascript_text .= "\n return return_value; }\n";
	$javascript_text .= 'checker.assignCommentSubmitEvent = function () {  var w = document.getElementById("';
	$javascript_text .=  $alerts_array['form_id'] . '");';
	$javascript_text .= "\n if (!w) return; \n w.onsubmit = checker.noBlankRequireds; } \n addEvent(window, 'load', checker.assignCommentSubmitEvent);\n//]]>\n</script>";
	echo $javascript_text;
	} 
	else {
		return false;
	}
} //end alert_scripts

//********************************************************************************
// end options page stuff
//********************************************************************************
var $pagekind;
function submitbuttons() { // prints out the submit buttons and extra stuff such as the captcha
global $id;
	$buttons = '';
	$subpage_general_array = $this->get_option('filosofo_cp_subpage_general_array');
	$buttons .= $this->display_captcha($this->pagekind);
	//if the buttons are for a popup comments form
	if ('popup' == $this->pagekind) { 
		$buttons .= '<input type="hidden" name="filosofo_cp_is_popup" id="filosofo_cp_is_popup" value="true" />';
	}
	if ($subpage_general_array['show_prev_button']) { 
		$buttons .= '<input type="hidden" name="filosofo_cp_post_id" id="filosofo_cp_post_id" value="';
		$buttons .= $id . '" /><input class="';
		$buttons .= $subpage_general_array['prev_button_class'] . '" name="submit" id="';
		$buttons .= $subpage_general_array['prev_button_id'] . '" type="submit" tabindex="5" value="';
		$buttons .= stripslashes($subpage_general_array['prev_button_text']) . '" />'; 	
	} 
	if ($subpage_general_array['show_submit_button']) {
		$buttons .= "\n" . '<input class="' . $subpage_general_array['submit_button_class']; 
		$buttons .= '" name="submit" id="' . $subpage_general_array['submit_button_id'];
		$buttons .= '" type="submit" tabindex="6" value="' . stripslashes($subpage_general_array['submit_button_text']) . '" style="font-weight: bold;" />';
	}
return $buttons;
} //end submitbuttons 

//  Captcha stuff based on the Trencaspammers plugin http://coffelius.arabandalucia.com

function captcha_process_number($number) { // passed a number, it returns an encoded number that will be used in the captcha image
// args: number--the number to process into the displayed number
	$captcha_array = $this->get_option('filosofo_cp_captcha_array');
	$datekey = date("F j");
	$salt = $captcha_array['salt'];
	$rcode = hexdec(md5($_SERVER[HTTP_USER_AGENT] . $salt . $number . $datekey));
	$code = substr($rcode, 2, $captcha_array['num_length']);
return $code;
} //end function captcha_process_number

function display_captcha($page=false) { // displays the captcha and associated input values
global $user_ID;
// arg: page--the page calling the function
	$text = '';
	$captcha_array = $this->get_option('filosofo_cp_captcha_array');
	$alerts_array = $this->get_option('filosofo_cp_alerts_array');
	$number = rand();
	if ( $user_ID ) $page = false;
	//if captcha is set to be on
	if ($captcha_array['show_captcha'] > 0) {
		//if the captcha should show up on every page
		if (($captcha_array['show_captcha'] == 2) || ($page != false)) {
			$text .= '<input type="hidden" name="filosofo_cp_captcha_number" id="filosofo_cp_captcha_number" value="' . $number . '" />';
			$text .= '<img src="' . get_option('siteurl') . '/wp-content/plugins/' . FILOSOFOCPNAME . '?captcha_image=yes&amp;random_num=' . $number . '" alt="' . stripslashes($captcha_array['captcha_label']) . '" title="' . stripslashes($captcha_array['captcha_label']) . '" />';
			$text .= '<label for="' . $alerts_array['captcha_id'] . '">' . stripslashes($captcha_array['captcha_label']) . '</label>';
			$text .= '<input type="text" name="' . $alerts_array['captcha_id'] . '" id="' . $alerts_array['captcha_id'] . '" size="' . $captcha_array['field_length'] . '" />';
		} //end if captcha should show up on every page
		//elseif captcha should just show up the first time
		elseif (!$page) {
			$text .= '<input type="hidden" name="filosofo_cp_captcha_number" id="filosofo_cp_captcha_number" value="' . $number . '" />';
			$text .= '<input type="hidden" name="' . $alerts_array['captcha_id'] . '" id="' . $alerts_array['captcha_id'] . '" value="' . $this->captcha_process_number($number) . '" />';
		}
	} //end if captcha is set to be on
return $text;
} //end function display_captcha

function captcha_image($random_num,$num_length = 6,$circles = 5,$lines = 1,$width=100,$height=40,$font=5,$bgred=10,$bggreen=102,$bgblue=174,$txred=255,$txgreen=255,$txblue=255,$rperc=0.01,$gperc=0.51,$bperc=0.87,$use_font=0,$font_path='',$angle=0,$x_ord=20,$y_ord=10,$test_num=0) 
{
// creates the captcha image
// args: $random_num--the random number sent to the image and included in the input
//       $num_length--the length of the resulting image code
//       $circles--the number of background circles
//       $lines--the number of lines appearing in the captcha image
//       $width, $height--of the image
//       $font--a number representing the font
//       $bgred, $bggreen, $bgblue, $txred, $txgreen, $txblue--of RGB between 0 and 255 for background and text
//       $rperc, $gperc, $bperc--the percentage of variation
//	 $use_font--whether to use a TrueType font file
//	 $font_path--the path to a TrueType font file
//	 $angle--text angle
//	 $x_ord, $y_ord--x and y coordinates of the text
	if ($test_num) {	
		$code = substr($random_num,0,$num_length);
		$rperc = $rperc * .01;
		$gperc = $gperc * .01;
		$bperc = $bperc * .01;
	}
	else $code = $this->captcha_process_number($random_num);
	$fontwidth = ImageFontWidth($font) * $num_length;
	$fontheight = ImageFontHeight($font);
	$im = @imagecreate ($width,$height);
	$background_color = imagecolorallocate ($im, $bgred, $bggreen, $bgblue);
	$text_color = imagecolorallocate ($im, $txred, $txgreen, $txblue);
	for ($i=1;$i<=$circles;$i++) {
		$value=rand(200, 255);
		$randomcolor = imagecolorallocate ($im , $value*$rperc, $value*$gperc,$value*$bperc);
		imagefilledellipse($im,rand(0,$width-10),rand(0,$height-3),rand(20,70),rand(20,70),$randomcolor);
	}
	//draws a border for the box with the color of the text
	imagerectangle($im,0,0,$width-1,$height-1,$text_color);
	//writes out the text string
	if ($use_font && is_file($font_path) && function_exists('imagettftext'))
		imagettftext($im, 3*$font, $angle, $x_ord, $y_ord, $text_color, $font_path, $code);
	else imagestring ($im, $font, $x_ord, $y_ord, $code, $text_color);
	//create lines
	for ($i=0;$i<$lines;$i++) {
		$y=rand(14, 24);
		//$randomcolor=imagecolorallocate($im, 0,0, rand(100, 255));
		imageline($im, 0, $y, $width, $y, $text_color);
	}
	header ("Content-type: image/jpeg");
	imagejpeg ($im,'',80);
	ImageDestroy($im);
	die();
}  // end captcha image

function captcha_human_check($random_num, $string) { // checks that the code-enterer is a person, not a spammer
// args: $string--the string returned in the form
//	random_num--the original number before processing
	$code = $this->captcha_process_number($random_num);
return $string==$code;
} //end captcha_human_check

function filter_comment($comment) { // applies filters to the $comment, so that in the preview it appears as it will finally
	$comment = apply_filters('pre_comment_content', $comment);
	$comment = apply_filters('comment_content_presave', $comment); // Deprecated
	$comment = stripslashes($comment);
	$comment = apply_filters('post_comment_text', $comment); // Deprecated
	$comment = apply_filters('comment_text', $comment);
return $comment;
} //end filter_comment

function template_format($template) { // replaces template variables with PHP, etc.
// arg: template--the text through which to search for replacable variables
	$subpage_general_array = $this->get_option('filosofo_cp_subpage_general_array');
	$previewed_buttons = '<?php echo $filosofo_cp_class->display_captcha(); ?>
   	<?php do_action(\'comment_form\', $comment_post_ID); ?>
   	<?php $subpage_general_array = $filosofo_cp_class->get_option(\'filosofo_cp_subpage_general_array\'); ?>
     	<input type="hidden" name="comment_post_ID" value="<?php echo $comment_post_ID; ?>" />
  	<input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />
  	<input type="hidden" name="filosofo_cp_post_id" id="filosofo_cp_post_id" value="<?php echo $filosofo_cp_post_id ?>" />
  	<input class="<?php echo stripslashes($subpage_general_array[\'prev_button_class\']); ?>" name="submit" id="<?php echo stripslashes($subpage_general_array[\'prev_button_id\']); ?>" type="submit" tabindex="5" value="<?php echo stripslashes($subpage_general_array[\'prev_button_text\']); ?>" />
  	<input class="<?php echo stripslashes($subpage_general_array[\'submit_button_class\']); ?>" name="submit" id="<?php echo stripslashes($subpage_general_array[\'submit_button_id\']); ?>" type="submit" tabindex="6" value="<?php echo stripslashes($subpage_general_array[\'submit_button_text\']); ?>" style="font-weight: bold;" />';
	if (isset($_POST['filosofo_cp_is_popup']))
		$previewed_buttons = '<input type="hidden" name="filosofo_cp_is_popup" id="filosofo_cp_is_popup" value="true" />' . $previewed_buttons;
	$template = str_replace("%alt_class",'<?php echo $oddcomment; ?>', $template);
	$template = str_replace("%previewed_author_link",'<?php  $author_filtered = $author; if (\'\' == $author_filtered) $author_filtered = __(\'Anonymous\'); if (empty($url)) : echo apply_filters(\'comment_author\',$author_filtered); else: $url = apply_filters(\'comment_url\',$url); $link = "<a href=\'$url\' rel=\'external\'>" . apply_filters(\'comment_author\',$author_filtered) . "</a>"; echo apply_filters(\'get_comment_author_link\',$link); endif; ?>',$template);
	$template = str_replace("%previewed_author",'<?php echo apply_filters(\'comment_author\',$author); ?>',$template);
	$template = str_replace("%previewed_buttons",$previewed_buttons,$template);
	$template = str_replace("%previewed_comment",'<?php echo $fcp_comment; ?>',$template);
	$template = str_replace("%previewed_email",'<?php echo apply_filters(\'comment_email\',$email); ?>',$template);
	$template = str_replace("%previewed_form_submit_path",'<?php echo get_settings(\'siteurl\'); ?>/wp-content/plugins/<?php echo FILOSOFOCPNAME; ?>',$template);
	$template = str_replace("%previewed_prev_comments",'<?php $subpage_general_array = $filosofo_cp_class->get_option(\'filosofo_cp_subpage_general_array\'); if ($subpage_general_array[\'comments_settings_show\'] == 1) { echo filosofo_cp_display_previous_comments(); } ?>',$template);
	$template = str_replace("%previewed_raw_comment",'<?php echo stripslashes($raw_comment); ?>',$template);
	$template = str_replace("%previewed_url",'<?php echo apply_filters(\'comment_url\',$url); ?>',$template);
	$template = str_replace("%previewed_post_title",'<?php echo apply_filters(\'the_title\',get_the_title($filosofo_cp_post_id)); ?>',$template);
return $template;
} //end template_format

function dirify($s) { // takes out problematic characters for URLs (or DB entries)
	$s = sanitize_title($s); 		## take out weird characters		
	$s = strtolower($s);           		## lower-case.
	$s = strip_tags($s);       		## remove HTML tags.
	$s = preg_replace('!&[^;\s]+;!','',$s); ## remove HTML entities.
	$s = preg_replace('![^\w\s]!','',$s);   ## remove non-word/space chars.
	$s = preg_replace('!\s+!','_',$s); 	## change space chars to underscores.
return $s;    
} //end dirify

function get_the_files_content($comments_path) { //gets the file's content, even for PHP 4.2
	if (!file_exists($comments_path))
        	return false;
	$content = '';
	if (function_exists(file_get_contents)) 
		$content = file_get_contents($comments_path);
	else {
		$content_array = file($comments_path);
		foreach ($content_array as $line)
			$content .= $line; 
	} 
return $content;
} //end get_the_files_content

} //end filosofo_cp class
} //end if filosofo_cp class exists

$filosofo_cp_class = new filosofo_cp();

//for backwards compatibility
if(!function_exists('filosofo_cp_submitbuttons')) {
	function filosofo_cp_submitbuttons($variable) {
	global $filosofo_cp_class;
	$filosofo_cp_class->pagekind = $variable;
	echo $filosofo_cp_class->submitbuttons();
	}
}

if((!get_option('filosofo_cp_version')) || ($filosofo_cp_version > get_option('filosofo_cp_version'))) 
	$filosofo_cp_class->upgrade();
	
if(!function_exists('filosofo_cp_display_previous_comments')) {
function filosofo_cp_display_previous_comments() { // displays the previous comments in the preview
global $filosofo_cp_class, $wpdb, $filosofo_cp_post_id, $user_ID, $comment_post_ID, $post;
	$subpage_general_array = $filosofo_cp_class->get_option('filosofo_cp_subpage_general_array');
	//if we are to show the previous comments on the preview page
	if($subpage_general_array['comments_settings_show']) {
		$id = $filosofo_cp_post_id;
		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = $id AND comment_approved = '1' ORDER BY comment_date");
		//if there actually are comments
		if(is_array($comments)) {
			//if the previous comments should be shown in reverse order
			if($subpage_general_array['comments_settings_reverse']) 
				$comments = array_reverse($comments);
			// These variables are for alternating comment background
			$oddcommentA = $subpage_general_array['comments_settings_oddcomment_class'];
			$oddcommentB = $subpage_general_array['comments_settings_evencomment_class'];
			$oddcomment = $oddcommentA;

			if ($comments) :
				eval('?> ' . stripslashes($subpage_general_array['comments_header']));
				global $comment; //allows us to use WP comment functions
				foreach ($comments as $comment) : 
					/* Changes every other comment to a different class */    
					if ($oddcommentA == $oddcomment) $oddcomment = $oddcommentB;
					else $oddcomment = $oddcommentA;
					$post->ID = $comment_post_ID;
					eval('?> ' . stripslashes($filosofo_cp_class->template_format($subpage_general_array['comments_template'])));
				endforeach;
				eval('?> ' . stripslashes($subpage_general_array['comments_footer']));
			else : // this is displayed if there are no comments so far ?>
				<p class="nocomments"><?php _e('No comments yet.'); ?></p>
			<?php endif;       
		} //end if there actually are comments
	} //end if we are to show the previous comments on the preview page
} //end display_previous_comments
}

if ($_GET['captcha_image']) {
	$filosofo_cp_captcha_array = $filosofo_cp_class->get_option('filosofo_cp_captcha_array');
	$pos= strpos($_SERVER['REQUEST_URI'], '?');
	$basename = basename(substr($_SERVER['REQUEST_URI'], 0, $pos));
	if($basename==FILOSOFOCPNAME)
		$filosofo_cp_class->captcha_image($_GET['random_num'],$filosofo_cp_captcha_array['num_length'],$filosofo_cp_captcha_array['circles'],$filosofo_cp_captcha_array['lines'],$filosofo_cp_captcha_array['width'],$filosofo_cp_captcha_array['height'],$filosofo_cp_captcha_array['font'],$filosofo_cp_captcha_array['bgred'],$filosofo_cp_captcha_array['bggreen'],$filosofo_cp_captcha_array['bgblue'],$filosofo_cp_captcha_array['txred'],$filosofo_cp_captcha_array['txgreen'],$filosofo_cp_captcha_array['txblue'],$filosofo_cp_captcha_array['rperc'],$filosofo_cp_captcha_array['gperc'],$filosofo_cp_captcha_array['bperc'],$filosofo_cp_captcha_array['use_font'],$filosofo_cp_captcha_array['font_path'],$filosofo_cp_captcha_array['angle'],$filosofo_cp_captcha_array['x_ord'],$filosofo_cp_captcha_array['y_ord']);
}
elseif ($_GET['test_num']) {
	$filosofo_cp_class->captcha_image($_GET['the_num'],$_GET['num_length'],$_GET['circles'],$_GET['lines'],$_GET['width'],$_GET['height'],$_GET['font'],$_GET['bgred'],$_GET['bggreen'],$_GET['bgblue'],$_GET['txred'],$_GET['txgreen'],$_GET['txblue'],$_GET['rperc'],$_GET['gperc'],$_GET['bperc'],$_GET['use_font'],$_GET['font_path'],$_GET['angle'],$_GET['x_ord'],$_GET['y_ord'],$_GET['test_num']);
}

//if someone's submitting a comment (both for previewing and direct submit)
if (isset($_POST['comment']) && isset($_POST['comment_post_ID'])) {
	$filosofo_cp_subpage_general_array = $filosofo_cp_class->get_option('filosofo_cp_subpage_general_array');
	$filosofo_cp_post_id = trim($_POST['filosofo_cp_post_id']);
	$comment_post_ID = (int) trim($_POST['comment_post_ID']);
	$status = $wpdb->get_row("SELECT post_status, comment_status FROM $wpdb->posts WHERE ID = '$comment_post_ID'");

	if ( empty($status->comment_status) ) {
		do_action('comment_id_not_found', $comment_post_ID);
		exit;
	} 
	elseif ( 'closed' ==  $status->comment_status ) {
		do_action('comment_closed', $comment_post_ID);
		die( __('Sorry, comments are closed for this item.') );
	} 
	elseif ( 'draft' == $status->post_status ) {
		do_action('comment_on_draft', $comment_post_ID);
		exit;
	}
	$comment_author       = trim($_POST['author']);
	$comment_author_email = trim($_POST['email']);
	$comment_author_url   = trim($_POST['url']);
	$comment_content      = trim($_POST['comment']);
	// If the user is logged in
	if(!function_exists(get_currentuserinfo) && defined('ABSPATH') && defined('WPINC'))
		require_once(ABSPATH . WPINC . '/pluggable-functions.php');
	get_currentuserinfo();
	if ( $user_ID ) :
		$comment_author       = addslashes($user_identity);
		$comment_author_email = addslashes($user_email);
		$comment_author_url   = addslashes($user_url);
	else :
		if ( get_option('comment_registration') )
			die( __('Sorry, you must be logged in to post a comment.') );
	endif;
	$comment_type = '';
	if ( get_settings('require_name_email') && !$user_ID ) {
		if ( 6 > strlen($comment_author_email) || '' == $comment_author )
			die( __('Error: please fill the required fields (name, email).') );
		elseif ( !is_email($comment_author_email))
			die( __('Error: please enter a valid email address.') );
	}
	if ( '' == $comment_content )
		die( __('Error: please type a comment.') );

	//if there's a captcha code submitted
	$filosofo_cp_captcha_array = $filosofo_cp_class->get_option('filosofo_cp_captcha_array');
	$filosofo_cp_alerts_array = $filosofo_cp_class->get_option('filosofo_cp_alerts_array');
	if($filosofo_cp_captcha_array['show_captcha']>0) {
		$code=trim($_POST[$filosofo_cp_alerts_array['captcha_id']]);
		$random_num=$_POST['filosofo_cp_captcha_number'];
		add_action('filosofo_cp_captcha_error', create_function('','die(__("Error: please type the security code."));'),10);
		if ( !$filosofo_cp_class->captcha_human_check($random_num, $code, $filosofo_cp_captcha_array['salt'],$filosofo_cp_captcha_array['num_length']))
			do_action('filosofo_cp_captcha_error');
	}
	//end captcha action

	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');
	//if someone submits a preview
	if(htmlentities($_POST['submit'],ENT_COMPAT,"UTF-8") == stripslashes($filosofo_cp_subpage_general_array['prev_button_text'])) {
		$raw_comment = htmlspecialchars($comment_content);
		$fcp_comment = $filosofo_cp_class->filter_comment($comment_content);
		//make logged in users show up in the preview
		if ( $user_ID ) {
			$author = $comment_author;
			$email = addslashes($user_email);
			$url   = addslashes($user_url);
		}
		else {
			$author = $comment_author;
			$email = addslashes($comment_author_email);
			$url   = addslashes($comment_author_url);	
		}
		//a hack necessary to avoid an extract error
		$wp_query->query_vars = array(); 
		//a hack to make WP think it's a single post
		$wp_query->is_single = true;
		$single = true;
		// another hack
		$posts[0]->ID = $comment_post_ID;
		
		//print the preview template
		
		//if it's for a pop-up
		if (isset($_POST['filosofo_cp_is_popup'])) {
			eval('?>' . $filosofo_cp_class->template_format(stripslashes($filosofo_cp_class->get_option('filosofo_cp_preview_pop_up_template')))); 
			exit();
		}
		else {
			eval('?>' . $filosofo_cp_class->template_format(stripslashes($filosofo_cp_class->get_option('filosofo_cp_preview_template')))); 
			exit();
		}
	} //end if someone submits a preview
	
	wp_new_comment($commentdata);
	if ( !$user_ID ) {
		setcookie('comment_author_' . COOKIEHASH, stripslashes($comment_author), time() + 30000000, COOKIEPATH);
		setcookie('comment_author_email_' . COOKIEHASH, stripslashes($comment_author_email), time() + 30000000, COOKIEPATH);
		setcookie('comment_author_url_' . COOKIEHASH, stripslashes($comment_author_url), time() + 30000000, COOKIEPATH);
	}
	//send the viewer back to the post with the comment now added
	if (isset($_POST['filosofo_cp_is_popup'])) $location = get_settings('siteurl') . '?comments_popup=' . $comment_post_ID;
	else $location = get_permalink($comment_post_ID);

	if(function_exists('wp_redirect')) wp_redirect($location);
	else header("Location: $location"); //pre-WordPress 1.5.1.3
}

//else someone's not submitting a comment
else {
	add_action('options_page_filosofo-comments-preview', array(&$filosofo_cp_class,'options_page'));
	add_action('admin_menu', array(&$filosofo_cp_class,'add_options_page'),1);
	add_action('admin_head', array(&$filosofo_cp_class,'subpage_header'));
	add_action('wp_head', array(&$filosofo_cp_class,'alert_scripts'));	
	add_filter('comments_template', create_function('$a','global $filosofo_cp_class; $filosofo_cp_class->pagekind = "standard"; ob_start(array(&$filosofo_cp_class,"replace_button")); return $a;'));
	add_filter('comments_popup_template', create_function('$a','global $filosofo_cp_class; $filosofo_cp_class->pagekind = "popup"; ob_start(array(&$filosofo_cp_class,"replace_button")); return $a;'));	
}
?>
