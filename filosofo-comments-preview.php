<?php
/*
Plugin Name: Filosofo Comments Preview
Plugin URI: http://www.ilfilosofo.com/blog/comments-preview/
Description: Filosofo Comments Preview lets you preview WordPress comments before you submit them.  It's highly configurable from the <a href="options-general.php?page=filosofo-comments-preview.php">admin control panel</a>, including optional <a href="http://en.wikipedia.org/wiki/Captcha">captcha</a> and JavaScript alert features.    
Version: 0.6.6
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

// initialize functions
if(!function_exists(get_settings)) {
  require_once(realpath('../../wp-config.php'));
}

//********************************************************************************
// Default values
//********************************************************************************



$comments_template = <<<COMMENTSTEMPLATE
<li class="%alt_class" id="comment-%comment_id">
 %comment_entire  
<p><cite>%comment_type <?php _e('by'); ?> <a href="%comment_link">%comment_author</a> &#8212; %comment_date @ <a href="%comment_link">%comment_time</a></cite></p>
</li>
COMMENTSTEMPLATE;
$comments_template = str_replace('$','\$',addslashes($comments_template));

$filosofo_cp_preview_template_default = <<<TEMPLATEDEFAULT
<?php get_header(); ?>

<div id="content" class="widecolumn">
<h2 id="comments">Your Comment Preview:</h2>

  %previewed_comment<p>by %previewed_author_link</p>

  <hr />
  <form action="%previewed_form_submit_path" method="post" id="commentform">
  <?php if ( \$user_ID ) : ?>
  <p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo \$user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout &raquo;</a></p>
  <?php else : ?>
  <p>
  <input type="text" name="author" id="author" class="textarea" value="%previewed_author" size="28" tabindex="1" />
  <label for="author"><?php _e('Name'); ?></label>
  </p>
  <p>
  <input type="text" name="email" id="email" value="%previewed_email" size="28" tabindex="2" />
  <label for="email"><?php _e('E-mail'); ?></label>
  </p>
  <p>
  <input type="text" name="url" id="url" value="%previewed_url" size="28" tabindex="3" />
  <label for="url"><?php _e('<acronym title="Uniform Resource Identifier">URI</acronym>'); ?></label>
  </p>
  <?php endif; ?>
  <p>
  <label for="comment"><?php _e('Your Comment'); ?></label>
  <br />
  <textarea name="comment" id="comment" cols="60" rows="10" tabindex="4">%previewed_raw_comment</textarea>
  </p>
  <p>
   %previewed_buttons
  </p>
  </form>
      %previewed_prev_comments
  </div>  
  <?php get_footer();
TEMPLATEDEFAULT;

$filosofo_cp_preview_template_classic = <<<TEMPLATECLASSIC
<?php get_header(); ?>


<h2 id="comments">Your Comment Preview:</h2>

  %previewed_comment<p>by %previewed_author_link</p>

  <hr />
  <form action="%previewed_form_submit_path" method="post" id="commentform">
  <?php if ( \$user_ID ) : ?>
  <p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo \$user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout &raquo;</a></p>
  <?php else : ?>
  <p>
  <input type="text" name="author" id="author" class="textarea" value="%previewed_author" size="28" tabindex="1" />
  <label for="author"><?php _e('Name'); ?></label>
  </p>
  <p>
  <input type="text" name="email" id="email" value="%previewed_email" size="28" tabindex="2" />
  <label for="email"><?php _e('E-mail'); ?></label>
  </p>
  <p>
  <input type="text" name="url" id="url" value="%previewed_url" size="28" tabindex="3" />
  <label for="url"><?php _e('<acronym title="Uniform Resource Identifier">URI</acronym>'); ?></label>
  </p>
  <?php endif; ?>
  <p>
  <label for="comment"><?php _e('Your Comment'); ?></label>
  <br />
  <textarea name="comment" id="comment" cols="60" rows="10" tabindex="4">%previewed_raw_comment</textarea>
  </p>
  <p>
   %previewed_buttons
  </p>
  </form>
      %previewed_prev_comments
  </div>  
  <?php get_footer();			
TEMPLATECLASSIC;

$filosofo_cp_preview_pop_up_template_classic = <<<TEMPLATE_POP_UP_CLASSIC
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
     <title><?php echo get_settings('blogname'); ?> - <?php echo sprintf(__("Comments on %s"), the_title('','',false)); ?></title>

	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_settings('blog_charset'); ?>" />
	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?> );
		body { margin: 3px; }
	</style>

</head>
<body id="commentspopup">

<h1 id="header"><a href="" title="<?php echo get_settings('blogname'); ?>"><?php echo get_settings('blogname'); ?></a></h1>

<p><a href="<?php echo get_settings('siteurl'); ?>/wp-commentsrss2.php?p=<?php echo \$post->ID; ?>"><?php _e("<abbr title=\"Really Simple Syndication\">RSS</abbr> feed for comments on this post."); ?></a></p>

<h2 id="comments">Your Comment Preview:</h2>

  %previewed_comment<p>by %previewed_author_link</p>

  <hr />
  <form action="%previewed_form_submit_path" method="post" id="commentform">
  <?php if ( \$user_ID ) : ?>
  <p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo \$user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout &raquo;</a></p>
  <?php else : ?>
  <p>
  <input type="text" name="author" id="author" class="textarea" value="%previewed_author" size="28" tabindex="1" />
  <label for="author"><?php _e('Name'); ?></label>
  </p>
  <p>
  <input type="text" name="email" id="email" value="%previewed_email" size="28" tabindex="2" />
  <label for="email"><?php _e('E-mail'); ?></label>
  </p>
  <p>
  <input type="text" name="url" id="url" value="%previewed_url" size="28" tabindex="3" />
  <label for="url"><?php _e('<acronym title="Uniform Resource Identifier">URI</acronym>'); ?></label>
  </p>
  <?php endif; ?>
  <p>
  <label for="comment"><?php _e('Your Comment'); ?></label>
  <br />
  <textarea name="comment" id="comment" cols="60" rows="10" tabindex="4">%previewed_raw_comment</textarea>
  </p>
  <p>
   %previewed_buttons
  </p>
  </form>
      %previewed_prev_comments
  </div>  


<div><strong><a href="javascript:window.close()"><?php _e("Close this window."); ?></a></strong></div>


<!-- // this is just the end of the motor - don't touch that line either :) -->
<?php //} ?> 
<p class="credit"><?php timer_stop(1); ?> <?php echo sprintf(__("<cite>Powered by <a href=\"http://wordpress.org\" title=\"%s\"><strong>Wordpress</strong></a></cite>"),__("Powered by WordPress, state-of-the-art semantic personal publishing platform.")); ?></p>
<?php // Seen at http://www.mijnkopthee.nl/log2/archive/2003/05/28/esc(18) ?>
<script type="text/javascript">
<!--
document.onkeypress = function esc(e) {	
	if(typeof(e) == "undefined") { e=event; }
	if (e.keyCode == 27) { self.close(); }
}
// -->
</script>
</body>
</html>
TEMPLATE_POP_UP_CLASSIC;



$filosofo_cp_version = .6;

$filosofo_cp_preview_template_default = str_replace('$','\$',addslashes($filosofo_cp_preview_template_default));

$filosofo_cp_preview_template_classic = str_replace('$','\$',addslashes($filosofo_cp_preview_template_classic));

$filosofo_cp_preview_pop_up_template_classic = str_replace('$','\$',addslashes($filosofo_cp_preview_pop_up_template_classic));


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
	'comments_settings_time_format' => $time_format,
	'comments_settings_date_format' => $date_format,
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

$salt = 'filosofo_cp' . rand();
$filosofo_cp_captcha_array_default = array('show_captcha' => 0,
	'captcha_label' => 'Enter the code that you see in the image',
     'salt' => $salt,
	'num_length' => 6,
	'circles' => 5,
	'lines' => 1,
	'width' => 100,
	'height' => 40,
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
	'filosofo_cp_captcha_array' => $filosofo_cp_captcha_array_default,
	'filosofo_cp_preview_template' => $filosofo_cp_preview_template_default,
	'filosofo_cp_preview_template_default' => $filosofo_cp_preview_template_default,
	'filosofo_cp_preview_template_classic' => $filosofo_cp_preview_template_classic,
	'filosofo_cp_preview_pop_up_template_default' => $filosofo_cp_preview_pop_up_template_classic,
	'filosofo_cp_preview_pop_up_template_classic' => $filosofo_cp_preview_pop_up_template_classic,
	'filosofo_cp_preview_pop_up_template' => $filosofo_cp_preview_pop_up_template_classic);
//********************************************************************************
// end default values
//********************************************************************************


if (!class_exists('filosofo_cp')) {
class filosofo_cp {



//********************************************************************************
function upgrade () {
global $filosofo_cp_version;
	update_option('filosofo_cp_version',$filosofo_cp_version);
} //end upgrade






//********************************************************************************
function replace_comments_file ($comments_path) {
// replaces the comments.php template values with the required ones
// only works on versions of WP > 1.5, which have the 'comments_template' filter hook
global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity;
//********************************************************************************

//make up for variables that aren't passed and aren't global
	if ( is_single() || is_page() || $withcomments ) :
		$req = get_settings('require_name_email');
		$comment_author = isset($_COOKIE['comment_author_'.COOKIEHASH]) ? trim(stripslashes($_COOKIE['comment_author_'.COOKIEHASH])) : '';
		$comment_author_email = isset($_COOKIE['comment_author_email_'.COOKIEHASH]) ? trim(stripslashes($_COOKIE['comment_author_email_'.COOKIEHASH])) : '';
		$comment_author_url = isset($_COOKIE['comment_author_url_'.COOKIEHASH]) ? trim(stripslashes($_COOKIE['comment_author_url_'.COOKIEHASH])) : '';
	if ( empty($comment_author) ) {
		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved = '1' ORDER BY comment_date");
	} else {
		$author_db = $wpdb->escape($comment_author);
		$email_db  = $wpdb->escape($comment_author_email);
		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND ( comment_approved = '1' OR ( comment_author = '$author_db' AND comment_author_email = '$email_db' AND comment_approved = '0' ) ) ORDER BY comment_date");
	}
	endif;
//end of make-up

$comments_template = $this->get_the_files_content($comments_path);
$comments_template = str_replace("/wp-comments-post.php","/wp-content/plugins/filosofo-comments-preview.php",$comments_template);

//don't replace the input buttons if someone's already done it
if(!preg_match('/filosofo_cp_submitbuttons/',$comments_template)) 
	$comments_template = preg_replace('/<input.*submit.*\/>/i','<?php $this->submitbuttons(\'plain\') ?>',$comments_template);


eval('?>' . $comments_template );

return __FILE__;
}  //end function replace_comments_file


var $evalonce = 0;

//********************************************************************************
function replace_popup_file ($popup_template) {
// replaces the popup form values with the required ones
global $wp_query, $withcomments, $post, $posts, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity;
if (1 == $this->evalonce) {
//********************************************************************************
//make up for variables that aren't passed and aren't global
	if ( is_single() || is_page() || $withcomments ) :
		$req = get_settings('require_name_email');
		$comment_author = isset($_COOKIE['comment_author_'.COOKIEHASH]) ? trim(stripslashes($_COOKIE['comment_author_'.COOKIEHASH])) : '';
		$comment_author_email = isset($_COOKIE['comment_author_email_'.COOKIEHASH]) ? trim(stripslashes($_COOKIE['comment_author_email_'.COOKIEHASH])) : '';
		$comment_author_url = isset($_COOKIE['comment_author_url_'.COOKIEHASH]) ? trim(stripslashes($_COOKIE['comment_author_url_'.COOKIEHASH])) : '';
	if ( empty($comment_author) ) {
		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved = '1' ORDER BY comment_date");
	} else {
		$author_db = $wpdb->escape($comment_author);
		$email_db  = $wpdb->escape($comment_author_email);
		$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND ( comment_approved = '1' OR ( comment_author = '$author_db' AND comment_author_email = '$email_db' AND comment_approved = '0' ) ) ORDER BY comment_date");
	}
	endif;
//end of make-up

$comments_template = $this->get_the_files_content($popup_template);

$comments_template = str_replace("/wp-comments-post.php","/wp-content/plugins/filosofo-comments-preview.php",$comments_template);

//don't replace the input buttons if someone's already done it
if(!preg_match('/filosofo_cp_submitbuttons/',$comments_template))
	$comments_template = preg_replace('/<input.*submit.*\/>/i','<?php $this->submitbuttons(\'popup\') ?>',$comments_template);

eval('?>' . $comments_template );
}
$this->evalonce = $this->evalonce + 1;

return __FILE__;
}  //end function replace_popup_file



//********************************************************************************
// options page stuff
//********************************************************************************
var $options = array();



//********************************************************************************
function get_option($option) {
// Looks up the setting for the name of the $option argument;  if it's not there it uses the default
// calls: add_option, update_option, get_option
global $filosofo_cp_default_options;
//********************************************************************************
$orig_option = $option;
//set filosofo_cp_preview_template option to be specific to the current stylesheet 
if($option == 'filosofo_cp_preview_template') {
	$option = $option . '_' . $this->dirify(get_option('stylesheet'));
}
//set filosofo_cp_preview_pop_up_template option to be specific to the current stylesheet
elseif($option == 'filosofo_cp_preview_pop_up_template') {
	$option = $option . '_' . $this->dirify(get_option('stylesheet'));
}

//check to see if the value has not already been loaded into the options array
if (!array_key_exists($option, $this->options)) {
	//if the option doesn't exist yet in the db, then use default
	if (!get_option($option)) {  //warning: for options with value 0 it returns false, hence always the default!
		//special case of the variously named templates
		if (preg_match('/^filosofo_cp_preview_template/',$option)) {
			//if there's not a default set for that template
			if (!array_key_exists($option, $filosofo_cp_default_options)) {
				$filosofo_cp_default_options[$option] = $filosofo_cp_default_options['filosofo_cp_preview_template']; 
			}
			//add it to the db and the options array
			add_option($option,$filosofo_cp_default_options[$option]);
			$this->options[$option] =  $filosofo_cp_default_options[$option];
		}
		elseif (preg_match('/^filosofo_cp_preview_pop_up_template/',$option)) {
			//if there's not a default set for that template
			if (!array_key_exists($option, $filosofo_cp_default_options)) {
				$filosofo_cp_default_options[$option] = $filosofo_cp_default_options['filosofo_cp_preview_pop_up_template'];
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



//**********************************************************************
function add_options_page() { 
// adds the plugin options page to the admin options menu
// calls: add_options_page
global $wp_version;
//**********************************************************************
if (function_exists('add_options_page')) {
	add_options_page('Filosofo Comments Preview Plugin', 'Comments Preview', 6, __FILE__,array(&$this,'options_page'));
	
} 


} //end add_options_page



//*********************************************************************
function options_page() {
// configures the Filosofo Comments Preview admin options page
// calls subpage_header, subpage_general, subpage_preview_template, subpage_captcha, subpage_alerts, update_option, 
global $filosofo_cp_default_options;
//*********************************************************************
//if the form has been submitted to be updated
if (isset($_GET['updated']) && ($_GET['updated'] == 'true')) {
	$possible_options = array_keys($_POST);
	//if the options are part of an array
	if (isset($_GET['array'])) {
		foreach($possible_options as $option) {
			$temparray[$option] = trim($_POST[$option]);
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
				if (preg_match('/^filosofo_cp_preview_template/',$option)) {
					//if there's not a default set for that template
					if (!array_key_exists($option, $filosofo_cp_default_options)) {
						$this->options[$option] = $filosofo_cp_default_options['filosofo_cp_preview_template'];
					}
					else {
						$this->options[$option] = $filosofo_cp_default_options[$option];
					}
				}
				//if a pop up template
				elseif (preg_match('/^filosofo_cp_preview_pop_up_template/',$option)) {
					//if there's not a default set for that template
					if (!array_key_exists($option, $filosofo_cp_default_options)) {
						$this->options[$option] = $filosofo_cp_default_options['filosofo_cp_preview_pop_up_template'];
					}
					else {
						$this->options[$option] = $filosofo_cp_default_options[$option];
					}
				}
				update_option($option,$this->options[$option]);
			}
			else {
				$this->options[$option] =  $_POST[$option];
				update_option($option,$_POST[$option]);
			}
		}
	}  //end else options not part of an array
}

$subpage = 1;
if (isset($_GET['subpage'])) {
	$subpage = $_GET['subpage'];
}
$this->subpage_header($subpage);
if ($subpage == 1) {
	$this->subpage_general(); 
} 
elseif ($subpage == 2) {
	$this->subpage_preview_template();
} 
elseif ($subpage == 3) {
	$this->subpage_captcha();
} 
elseif ($subpage == 4) {
	$this->subpage_alerts();
}
} //end options_page function



//***********************************************************************************
function subpage_header ($selected_tab) {
// prints the header for the admin options pages
//***********************************************************************************
$current_tab[$selected_tab] = "class=\"current\"";
?>
<style>
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
-->
</style>
<ul id="adminmenu3">
	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=1" <?php echo $current_tab[1] ?>>General</a></li>
	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=2" <?php echo $current_tab[2] ?>>Preview Page Templates</a></li>
	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=3" <?php echo $current_tab[3] ?>>Captcha Options</a></li>
	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=4" <?php echo $current_tab[4] ?>>Set Alerts</a></li>
</ul>
<div class="wrap">
<h2><?php _e('Options for the Filosofo Comments Preview Plugin') ?></h2>
<?php
}  //end function subpage_header 




//***********************************************************************************
function subpage_general()  {
// prints the general options subpage
global $filosofo_cp_default_options;
// calls: filosofo_cp_get_option, e_, 
//***********************************************************************************
$subpage_general_array = $this->get_option('filosofo_cp_subpage_general_array');
?> 
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=1&amp;updated=true&amp;array=filosofo_cp_subpage_general_array">
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
			<tr><th><?php _e('Preview Button'); ?></th><td><input name="prev_button_text" type="text" id="prev_button_text" value="<?php echo $subpage_general_array['prev_button_text'] ?>" size="15" /></td><td><input name="prev_button_class" type="text" id="prev_button_class" value="<?php echo $subpage_general_array['prev_button_class'] ?>" size="15" /></td><td><input name="prev_button_id" type="text" id="prev_button_id" value="<?php echo $subpage_general_array['prev_button_id'] ?>" size="15" /></td></tr>
			<tr><th><?php _e('Submit Button'); ?></th><td><input name="submit_button_text" type="text" id="submit_button_text" value="<?php echo $subpage_general_array['submit_button_text']; ?>" size="15" /></td><td><input name="submit_button_class" type="text" id="submit_button_class" value="<?php echo $subpage_general_array['submit_button_class']; ?>" size="15" /></td><td><input name="submit_button_id" type="text" id="submit_button_id" value="<?php echo $subpage_general_array['submit_button_id']; ?>" size="15" /></td></tr>
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
				<td><select name="comments_settings_reverse" id="filosofo_cp_comments_settings_reverse">
					<option value="1" <?php if ($subpage_general_array['comments_settings_reverse']== 1) {echo 'selected="selected"';} ?>><?php _e('Newest to oldest'); ?></option>
					<option value="0" <?php if ($subpage_general_array['comments_settings_reverse']== 0) {echo 'selected="selected"';} ?>><?php _e('Oldest to newest'); ?></option>
				</select></td>
			</tr>
		</table>
		<hr />
		<table>
			<tr><th colspan="3"><?php _e('Miscellaneous Comments Settings'); ?></th></tr>
			<tr><td><?php _e('The format for a comment\'s date, as called by <code>%comment_date</code> below:'); ?></td><td><input name="comments_settings_date_format" type="text" id="comments_settings_date_format" value="<?php echo $subpage_general_array['comments_settings_date_format']; ?>" size="50" /></td><td rowspan="2"><?php _e('Use the same syntax as the '); ?><a href="http://php.net/date">PHP <code>date()</code> function</a>.</td></tr>
			<tr><td><?php _e('The format for a comment\'s time, as called by <code>%comment_time</code> below:'); ?></td><td><input name="comments_settings_time_format" type="text" id="comments_settings_time_format" value="<?php echo $subpage_general_array['comments_settings_time_format']; ?>" size="50" /></td></tr>
			<tr><td rowspan="2"><?php _e('Alternating classes for every other comment, called by <code>%alt_class</code> below:'); ?><br /><?php _e('(For styling every other comment)'); ?></td><td><input name="comments_settings_oddcomment_class" type="text" id="comments_settings_oddcomment_class" value="<?php echo $subpage_general_array['comments_settings_oddcomment_class']; ?>" size="15" /></td><td></td></tr>
			<tr><td><input name="comments_settings_evencomment_class" type="text" id="comments_settings_evencomment_class" value="<?php echo $subpage_general_array['comments_settings_evencomment_class']; ?>" size="15" /></td><td></td></tr>
		</table>
		<hr />
		<table>
			<tr><th colspan="2"><?php _e('Customize the template for previous comments'); ?></th></tr>
			<tr>
				<td colspan="2">
					<pre>
 %alt_class       - Set above, this class name alternates with every comment
 %author_url      - URL of author or trackback
 %comment_author  - Name left by the commenter
 %comment_entire  - The comment text
 %comment_id      - The WordPress id of the comment
 %comment_link    - Link to the comment
 %comment_date    - Date of comment
 %comment_time    - Time of comment
 %comment_type    - Type of comment; the default is "Comment"
 %userid          - UserID of the commenter
 					</pre>
				</td>
			</tr>
			<tr>
				<td><label for="comments_header"><?php _e('The markup for the top of the previous comments'); ?></label></td>
				<td><textarea class="filosofo_cp_edittext" cols="70" rows="3" name="comments_header" id="comments_header"><?php echo htmlspecialchars(stripslashes($subpage_general_array['comments_header'])); ?>
				</textarea></td>
			</tr>
			<tr>
				<td><label for="comments_template"><?php _e('The template for each previous comment (see above)'); ?></label></td>
				<td><textarea class="filosofo_cp_edittext" cols="70" rows="13" name="comments_template" id="comments_template" ><?php echo htmlspecialchars(stripslashes($subpage_general_array['comments_template'])); ?>
				</textarea></td>
			</tr>
			<tr>
				<td><label for="comments_footer"><?php _e('The markup for the bottom of the previous comments'); ?></label></td>
				<td><textarea class="filosofo_cp_edittext" cols="70" rows="3" name="comments_footer" id="comments_footer"><?php echo htmlspecialchars(stripslashes($subpage_general_array['comments_footer'])); ?>
				</textarea></td>
			</tr>
		</table>
		<p><?php _e('Edit the complete template for this theme at the '); ?><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=2"><?php _e('template page'); ?></a>.</p>
	</fieldset>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save General Comments Preview Options') ?> &raquo;" />
		<input class="filosofo_cp_deletepost" type="submit" id="deletepost" name="reset" value="<?php _e('Reset General Comments Preview Options to default') ?> &raquo;" onclick="return confirm('You are about to reset your options for \'General Comments Preview\'.\n  \'Cancel\' to stop, \'OK\' to delete.')" />
	</p>
</form>
<?php
} //end function subpage_general


//***************************************************************************************
function subpage_preview_template() {
// prints the preview options subpage 
global $filosofo_cp_default_options;
// calls: get_option, get_themes, filosofo_cp_get_option, _e,  
//***************************************************************************************
$current_stylesheet = get_option('stylesheet');
$themetoedit = $this->dirify($current_stylesheet);
if (!empty($_POST['themetoedit'])) $themetoedit = $_POST['themetoedit'];
$themes = get_themes(); 
?>
<form name="theme" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=2"> 
	<label for="themetoedit"><?php _e('Select corresponding theme:') ?></label>
	<select name="themetoedit" id="themetoedit">
		<?php
		foreach ($themes as $a_theme) {
			$theme_name = $a_theme['Name'];
			$theme_id = $this->dirify($a_theme['Stylesheet']);
			//if ($theme_name == $theme) $selected = " selected='selected'";
			if ($theme_id == $themetoedit) $selected = " selected='selected'";
			else $selected = '';
			$theme_name = wp_specialchars($theme_name, true);
			echo "\n\t<option value=\"$theme_id\" $selected>$theme_name</option>";
		}
		?>
	</select>
	<input type="submit" name="submittheme" id="submittheme" value="<?php _e('Select') ?> &raquo;" />
</form>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=2&amp;updated=true">
<input type="hidden" name="themetoedit" id="themetoedit2" value="<?php echo $themetoedit ?>" />
	<fieldset class="options">
		<legend>Comments Preview Template for the <?php echo $themetoedit; ?> Stylesheet</legend>
		<p><?php _e('You can edit the template below, using these variables, XHTML, or PHP'); ?></p>
		<p><?php _e('Look for more theme templates or add your own '); ?><a href="http://www.ilfilosofo.com/blog/filosofo-comments-preview-templates/"><?php _e('here'); ?></a>.
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
			</pre>
		</p>
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
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=2&amp;updated=true">
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



//********************************************************************************
function subpage_captcha() {
// prints the captcha options subpage
// calls: 
//********************************************************************************
$captcha_array = $this->get_option('filosofo_cp_captcha_array');
?>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=3&amp;updated=true&amp;array=filosofo_cp_captcha_array">
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
		<div style="float:right;"><p><?php _e('Current Captcha appearance'); ?>:</p><br /><img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/filosofo-comments-preview.php?captcha_image=yes&amp;random_num=123456" title="Sample Captcha Image" id="sample_captcha_image" /></div>
		<div>
			<table>
				<tr><th colspan="2"><?php _e('Fine-tune the captcha features'); ?></th></tr>
				<tr><td><label for="captcha_label"><?php _e('The label for the captcha text box'); ?></label></td><td><input name="captcha_label" type="text" id="captcha_label" value="<?php echo $captcha_array['captcha_label']; ?>" size="50" /></td></tr>
                    <tr><td><label for="num_length"><?php _e('The length of the number that appears'); ?></label></td><td><input name="num_length" type="text" id="num_length" value="<?php echo $captcha_array['num_length']; ?>" size="10" /></td></tr>
				<tr><td><label for="circles"><?php _e('The number of background ellipses'); ?></label></td><td><input name="circles" type="text" id="circles" value="<?php echo $captcha_array['circles']; ?>" size="5" /></td></tr>
				<tr><td><label for="lines"><?php _e('The number of horizontal lines'); ?></label></td><td><input name="lines" type="text" id="lines" value="<?php echo $captcha_array['lines']; ?>" size="5" /></td></tr>
				<tr><td><label for="width"><?php _e('The width in pixels of the captcha image'); ?></label></td><td><input name="width" type="text" id="width" value="<?php echo $captcha_array['width']; ?>" size="10" /></td></tr>
				<tr><td><label for="height"><?php _e('The height in pixels of the captcha image'); ?></label></td><td><input name="height" type="text" id="height" value="<?php echo $captcha_array['height']; ?>" size="10" /></td></tr>
				<tr><td><label for="font"><?php _e('Font size'); ?></label></td>
					<td><select name="font" id="font" size=""><?php
						for ($i=1;$i<6;$i++) {
							if ($captcha_array['font']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="bgred"><?php _e('RGB red setting for the background'); ?></label></td>
					<td><select name="bgred" id="bgred" size=""><?php
						for ($i=0;$i<256;$i++) {
							if ($captcha_array['bgred']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="bggreen"><?php _e('RGB green setting for the background'); ?></label></td>
					<td><select name="bggreen" id="bggreen" size=""><?php
						for ($i=0;$i<256;$i++) {
							if ($captcha_array['bggreen']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="bgblue"><?php _e('RGB green setting for the background'); ?></label></td>
					<td><select name="bgblue" id="bgblue" size=""><?php
						for ($i=0;$i<256;$i++) {
							if ($captcha_array['bgblue']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="txred"><?php _e('RGB red setting for the text'); ?></label></td>
					<td><select name="txred" id="txred" size=""><?php
						for ($i=0;$i<256;$i++) {
							if ($captcha_array['txred']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="txgreen"><?php _e('RGB green setting for the text'); ?></label></td>
					<td><select name="txgreen" id="txgreen" size=""><?php
						for ($i=0;$i<256;$i++) {
							if ($captcha_array['txgreen']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="txblue"><?php _e('RGB blue setting for the text'); ?></label></td>
					<td><select name="txblue" id="txblue" size=""><?php
						for ($i=0;$i<256;$i++) {
							if ($captcha_array['txblue']==$i) {
								$selected = 'selected="selected"';
							}
							else {
								$selected = '';
							}
							echo '<option value="' . $i . '" ' . $selected . ' >' . $i . '</option>';
						} ?>
					</select></td>
				</tr>
				<tr><td><label for="rperc"><?php _e('Variation in background color of RGB red'); ?></td><td><input name="rperc" type="text" id="rperc" value="<?php echo $captcha_array['rperc']; ?>" size="10" /></td></tr>
				<tr><td><label for="gperc"><?php _e('Variation in background color of RGB green'); ?></td><td><input name="gperc" type="text" id="gperc" value="<?php echo $captcha_array['gperc']; ?>" size="10" /></td></tr>
				<tr><td><label for="bperc"><?php _e('Variation in background color of RGB blue'); ?></td><td><input name="bperc" type="text" id="bperc" value="<?php echo $captcha_array['bperc']; ?>" size="10" /></td></tr>
			</table>
		</div>
	</fieldset>
	<p class="submit">
		<input type="submit" name="submit" value="<?php _e('Save changes to the Captcha settings'); ?> &raquo;" />
		<input type="submit" class="filosofo_cp_deletepost" id="reset" name="reset" value="<?php _e('Reset the Captcha settings to Default') ?> &raquo;" onclick="return confirm('You are about to reset the Captcha settings.\n  \'Cancel\' to stop, \'OK\' to reset.')" />
	</p>
</form>
<?php
} //end function subpage_captcha


//********************************************************************************
function subpage_alerts() {
// prints the alerts options subpage
global $filosofo_cp_default_options;
// calls: e_, filosofo_cp_get_option, 
//********************************************************************************
$alerts_array = $this->get_option('filosofo_cp_alerts_array');
?>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=4&amp;updated=true&amp;array=filosofo_cp_alerts_array">
	<fieldset class="options">
		<legend><?php _e('Set Alerts'); ?></legend>
		<p><?php _e('Alerts allow you to warn your commenters before they submit their comments with required fields left blank.'); ?></p>
		<label for="activate"><?php _e('Activate JavaScript Alerts?'); ?></label>
		<select name="activate" id="activate">
			<option value="1" <?php if ($alerts_array['activate']== 1) {echo 'selected="selected"';} ?>>Yes</option>
			<option value="0" <?php if ($alerts_array['activate']== 0) {echo 'selected="selected"';} ?>>No</option>
		</select>
		<hr />
		<label for="form_id"><?php _e('The comment form\'s "id" attribute value') ?>: <small><?php _e('(Most likely the default is correct)') ?></small>
		<input name="form_id" type="text" id="form_id" value="<?php echo htmlspecialchars(stripslashes($alerts_array['form_id'])); ?>"  size="20" />
		<hr />
		<table>
			<tr><td><label for="name"><?php _e('Alert commenters that they have not filled in the "Name" field?'); ?></label></td>
				<td>
					<select name="name" id="name" >
						<option value="1" <?php if ($alerts_array['name']== 1) {echo 'selected="selected"';} ?>>Yes</option>
						<option value="0" <?php if ($alerts_array['name']== 0) {echo 'selected="selected"';} ?>>No</option>
					</select>
				</td>
				<td><label for="name_text"><?php _e('Text for warning:'); ?></label></td>
				<td><input name="name_text" type="text" id="name_text" value="<?php echo htmlspecialchars(stripslashes($alerts_array['name_text'])); ?>" size="50"  /></td>
				<td><label for="name_id"><?php _e('The "Name" field\'s "id" attribute value') ?>:<br /><small><?php _e('(Most likely the default is correct)') ?></small></td>
				<td><input name="name_id" type="text" id="name_id" value="<?php echo htmlspecialchars(stripslashes($alerts_array['name_id'])); ?>" size="10"  /></td>
			</tr>
			<tr><td><label for="alerts_email"><?php _e('Alert commenters that they have not filled in the "Email" field?'); ?></label></td>
				<td>
					<select name="email" id="email" >
						<option value="1" <?php if ($alerts_array['email']== 1) {echo 'selected="selected"';} ?>>Yes</option>
						<option value="0" <?php if ($alerts_array['email']== 0) {echo 'selected="selected"';} ?>>No</option>
					</select>
				</td>
				<td><label for="email_text"><?php _e('Text for warning:'); ?></label></td>
				<td><input name="email_text" type="text" id="email_text" value="<?php echo htmlspecialchars(stripslashes($alerts_array['email_text'])); ?>" size="50"  /></td>
				<td><label for="email_id"><?php _e('The "Email" field\'s "id" attribute value') ?>:<br /><small><?php _e('(Most likely the default is correct)') ?></small></td>
				<td><input name="email_id" type="text" id="email_id" value="<?php echo htmlspecialchars(stripslashes($alerts_array['email_id'])); ?>" size="10"  /></td>
			</tr>
			<tr><td><label for="captcha"><?php _e('Alert commenters that they have not filled in the "Chaptcha" field code?'); ?><br /><small><?php _e('(This applies only if you\'ve activated the '); ?><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=filosofo-comments-preview.php&amp;subpage=3"><?php _e('captcha feature'); ?></a>)</small></label></td>
				<td>
					<select name="captcha" id="captcha" >
						<option value="1" <?php if ($alerts_array['captcha']== 1) {echo 'selected="selected"';} ?>>Yes</option>
						<option value="0" <?php if ($alerts_array['captcha']== 0) {echo 'selected="selected"';} ?>>No</option>
					</select>
				</td>
				<td><label for="captcha_text"><?php _e('Text for warning:'); ?></label></td>
				<td><input name="captcha_text" type="text" id="captcha_text" value="<?php echo htmlspecialchars(stripslashes($alerts_array['captcha_text'])); ?>" size="50"  /></td>
				<td><label for="captcha_id"><?php _e('The "Captcha" field\'s "id" attribute value') ?>:<br /><small><?php _e('(Most likely the default is correct)') ?></small></td>
				<td><input name="captcha_id" type="text" id="captcha_id" value="<?php echo htmlspecialchars(stripslashes($alerts_array['captcha_id'])); ?>" size="10"  /></td>
			</tr>
		</table>
	</fieldset>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save changes to the Alerts'); ?> &raquo;" />
		<input type="submit" class="filosofo_cp_deletepost" id="reset" name="reset" value="<?php _e('Reset the Alerts to Default') ?> &raquo;" onclick="return confirm('You are about to reset the Alerts.\n  \'Cancel\' to stop, \'OK\' to reset.')" />
	</p>
</form>
<?php
} // end subpage_alerts()


//********************************************************************************
function alert_scripts() {
// prints out JavaScript alert scripts as needed
global $filosofo_cp_default_options;
// calls: filosofo_cp_get_option, 
//********************************************************************************
$alerts_array = $this->get_option('filosofo_cp_alerts_array');
if ($alerts_array['activate']) {
	$javascript_text = <<<JAVASCRIPT
<script type="text/javascript">
//******************************************************************************
function addEvent(obj, evType, fn){
// from http://www.sitepoint.com/article/structural-markup-javascript
//******************************************************************************
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

//*****************************************************************************
function giveidFocus(idname){
// gives a given id focus
// arg: idname--the id to give focus
//*****************************************************************************

        if (document.getElementById) {  
            var thingtogetfocus=document.getElementById(idname);  
            thingtogetfocus.focus();
            } 
} //end giveidFocus

function checker () {}
//***********************************************************************
checker.fieldAlert = function(fieldid,alertmsg) {
// alerts a user if she hasn't filled in a given field, then takes her to the field
// args: fieldid--the id of the field
//  alertmsg--the message to be alerted to
//***********************************************************************
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

//***********************************************************************
checker.noBlankRequireds = function() {
// returns false on a submit if certain fields are left blank
//***********************************************************************
var return_value = true;
JAVASCRIPT;
	if ($alerts_array['captcha']) {
		$javascript_text .= 'if (checker.fieldAlert(\'' .  $alerts_array['captcha_id'] . '\',\'' . htmlspecialchars($alerts_array['captcha_text']) . '\')) { return_value = false; }' . "\n";
	}
	if ($alerts_array['email']) {
		$javascript_text .= 'if (checker.fieldAlert(\'' .  $alerts_array['email_id'] . '\',\'' . htmlspecialchars($alerts_array['email_text']) . '\')) { return_value = false; }'. "\n";
	}
	if ($alerts_array['name']) {
		$javascript_text .= 'if (checker.fieldAlert(\'' .  $alerts_array['name_id'] . '\',\'' . htmlspecialchars($alerts_array['name_text']) . '\')) { return_value = false; }'. "\n";
	}
	$javascript_text .= "\n return return_value; }\n";
	$javascript_text .= 'checker.assignCommentSubmitEvent = function () {  var w = document.getElementById("';
	$javascript_text .=  $alerts_array['form_id'] . '");';
	$javascript_text .= "\n if (!w) return; \n w.onsubmit = checker.noBlankRequireds; } \n addEvent(window, 'load', checker.assignCommentSubmitEvent);\n</script>";
echo $javascript_text;
} 
else {
	return false;
}
} //end alert_scripts





//********************************************************************************
// end options page stuff
//********************************************************************************


//********************************************************************************
function submitbuttons($page = 'plain') {
// prints out the submit buttons and extra stuff such as the captcha
// args: page--the page that's calling this function
// calls: filosofo_cp_get_option, filosofo_cp_alert_scripts, filosofo_cp_display_captcha
//********************************************************************************
$subpage_general_array = $this->get_option('filosofo_cp_subpage_general_array');
$this->display_captcha($page);
//if the buttons are for a popup comments form
if ('popup' == $page) { ?>
	<input type="hidden" name="filosofo_cp_is_popup" id="filosofo_cp_is_popup" value="true" />
	<?php 
}
if ($subpage_general_array['show_prev_button']) { ?>
	<input type="hidden" name="filosofo_cp_post_permalink" id="filosofo_cp_post_permalink" value="<?php the_permalink() ?>" />
	<input type="hidden" name="filosofo_cp_post_id" id="filosofo_cp_post_id" value="<?php the_ID() ?>" />
	<input class="<?php echo $subpage_general_array['prev_button_class']; ?>" name="submit" id="<?php echo $subpage_general_array['prev_button_id']; ?>" type="submit" tabindex="5" value="<?php echo $subpage_general_array['prev_button_text']; ?>" /><?php 
} 
if ($subpage_general_array['show_submit_button']) { ?>
<input class="<?php echo $subpage_general_array['submit_button_class']; ?>" name="submit" id="<?php echo $subpage_general_array['submit_button_id']; ?>" type="submit" tabindex="6" value="<?php echo $subpage_general_array['submit_button_text']; ?>" style="font-weight: bold;" /><?php
}
} //end submitbuttons



//*****************************************************************************
//  Captcha stuff
//    based on the Trencaspammers plugin
//  http://coffelius.arabandalucia.com
//*****************************************************************************


//*****************************************************************************
function captcha_process_number($number) {
// passed a number, it returns an encoded number that will be used in the captcha image
// args: number--the number to process into the displayed number
// calls: $this->get_option, 
//*****************************************************************************
$captcha_array = $this->get_option('filosofo_cp_captcha_array');
$datekey = date("F j");
$salt = $captcha_array['salt'];
$rcode = hexdec(md5($_SERVER[HTTP_USER_AGENT] . $salt . $number . $datekey));
$code = substr($rcode, 2, $captcha_array['num_length']);
return $code;
} //end function captcha_process_number



//*****************************************************************************
function display_captcha($page=false) { 
// displays the captcha and associated input values
// arg: page--the page calling the function
// calls: filosofo_cp_get_option, get_option,filosofo_cp_captcha_process_number 
//*****************************************************************************
$captcha_array = $this->get_option('filosofo_cp_captcha_array');
$alerts_array = $this->get_option('filosofo_cp_alerts_array');
$number = rand();
//if captcha is set to be on
if ($captcha_array['show_captcha'] > 0) {
	//if the captcha should show up on every page
	if (($captcha_array['show_captcha'] == 2) || ($page != false)) { ?>
		<input type="hidden" name="filosofo_cp_captcha_number" id="filosofo_cp_captcha_number" value="<?php echo $number; ?>" />
		<img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/filosofo-comments-preview.php?captcha_image=yes&amp;random_num=<?php echo $number; ?>" alt="<?php echo $captcha_array['captcha_label']; ?>" title="<?php echo $captcha_array['captcha_label']; ?>" />
		<label for="<?php echo $alerts_array['captcha_id']; ?>"><?php echo $captcha_array['captcha_label']; ?></label>
		<input type="text" name="<?php echo $alerts_array['captcha_id']; ?>" id="<?php echo $alerts_array['captcha_id']; ?>" size="28" /><?php
	} //end if captcha should show up on every page
	//elseif captcha should just show up the first time
	elseif (!$page) {
		?><input type="hidden" name="filosofo_cp_captcha_number" id="filosofo_cp_captcha_number" value="<?php echo $number; ?>" />
		<input type="hidden" name="<?php echo $alerts_array['captcha_id']; ?>" id="<?php echo $alerts_array['captcha_id']; ?>" value="<?php echo $this->captcha_process_number($number); ?>" /><?php
	}
} //end if captcha is set to be on
} //end function display_captcha



//*****************************************************************************
function captcha_image($random_num,$num_length = 6,$circles = 5,$lines = 1,$width=100,$height=40,$font=5,$bgred=10,$bggreen=102,$bgblue=174,$txred=255,$txgreen=255,$txblue=255,$rperc=0.01,$gperc=0.51,$bperc=0.87) {
// creates the captcha image
// args: $random_num--the random number sent to the image and included in the input
//       $salt--a string to add to the "randomness" of the generated number
//       $num_length--the length of the resulting image code
//       $circles--the number of background circles
//       $lines--the number of lines appearing in the captcha image
//       $width--the width of the image
//       $height--the height of the image
//       $font--a number representing the font
//       $bgred--of RGB, between 0 and 255, inclusive, for background
//       $bggreen--of RGB, between 0 and 255, inclusive, for background
//       $bgblue--of RGB, between 0 and 255, inclusive, for background
//       $txred--of RGB, between 0 and 255, inclusive, for text
//       $txgreen--of RGB, between 0 and 255, inclusive, for text
//       $txblue--of RGB, between 0 and 255, inclusive, for text
//       $rperc--the percentage of variation
//       $gperc--the percentage of variation
//       $bperc--the percentage of variation
//
// calls: captcha_process_number
//*****************************************************************************
$code = $this->captcha_process_number($random_num);
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
//imagettftext($im, 15, 15, 11, 35, $text_color, 'Kids.ttf', $code);
imagestring ($im, $font, 22, 12,$code,$text_color);
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



//*****************************************************************************
function captcha_human_check($random_num, $string) {
// checks that the code-enterer is a person, not a spammer
// args: $string--the string returned in the form
//	random_num--the original number before processing
// calls: captcha_process_number
//*****************************************************************************
$code = $this->captcha_process_number($random_num);
return $string==$code;
} //end captcha_human_check






//*****************************************************************************
function filter_comment($comment) {
// applies filters to the $comment, so that in the preview it appears as it will finally
// arg: comment--the comment to be filtered
// calls: apply_filters, 
//*****************************************************************************
// filter explanations from http://codex.wordpress.org/Plugin_API
//preprocessing a new comment's content prior to saving it in the database, called with the comment content passed as a string. Should return a string.
$comment = apply_filters('pre_comment_content', $comment);
$comment = apply_filters('comment_content_presave', $comment); // Deprecated

$comment = stripslashes($comment);

$comment = apply_filters('post_comment_text', $comment); // Deprecated
// applied to comment content prior to rendering. Passed the comment as a string.
$comment = apply_filters('comment_text', $comment);
return $comment;
} //end filter_comment




//*****************************************************************************
function template_format($template) {
// replaces template variables such as %comment_entire with PHP, etc.
// arg: template--the text through which to search for replacable variables
//*****************************************************************************
/*  %alt_class       - Set above, this class name alternates with every comment
 %author_url      - URL of author or trackback
 %comment_author  - Name left by the commenter
 %comment_entire  - The comment text
 %comment_id      - The WordPress id of the comment
 %comment_link    - Link to the comment
 %comment_date    - Date of comment
 %comment_time    - Time of comment
 %comment_type    - Type of comment: default is "Comment"
 %previewed_author_link      - The previewed comment's author as a link, if applicable
 %previewed_author           - The previewed comment's author
 %previewed_buttons          - The submit buttons
 %previewed_comment          - The previewed comment, formatted and filtered
 %previewed_email            - The previewed comment author's email address
 %previewed_form_submit_path - The path to which the form submits
 %previewed_prev_comments    - The previous comments, if activated
 %previewed_raw_comment      - The comment in raw XHTML form, for the &lt;textarea&gt;
 %previewed_url              - The previewed comment author's URL, if applicable
 %userid                     - UserID of the commenter    */

$previewed_buttons = '<?php $filosofo_cp_class->display_captcha(); ?>
   <?php do_action(\'comment_form\', $comment_post_ID); ?>
   <?php $subpage_general_array = $filosofo_cp_class->get_option(\'filosofo_cp_subpage_general_array\'); ?>
     <input type="hidden" name="comment_post_ID" value="<?php echo $comment_post_ID; ?>" />
  <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />
  <input type="hidden" name="filosofo_cp_post_permalink" id="filosofo_cp_post_permalink" value="<?php echo $filosofo_cp_post_permalink ?>" />
  <input type="hidden" name="filosofo_cp_post_id" id="filosofo_cp_post_id" value="<?php echo $filosofo_cp_post_id ?>" />
  <input class="button" name="submit" id="preview" type="submit" tabindex="5" value="<?php echo $subpage_general_array[\'prev_button_text\']; ?>" />
  <input class="button" name="submit" id="submit" type="submit" tabindex="6" value="<?php echo $subpage_general_array[\'submit_button_text\']; ?>" style="font-weight: bold;" />';
if (isset($_POST['filosofo_cp_is_popup'])) {
	$previewed_buttons = '<input type="hidden" name="filosofo_cp_is_popup" id="filosofo_cp_is_popup" value="true" />' . $previewed_buttons;
}



$template = str_replace("%alt_class",'<?php echo $oddcomment; ?>', $template);
$template = str_replace("%author_url",'<?php echo $comment->comment_author_url; ?>',$template);
$template = str_replace("%comment_author",'<?php echo $comment->comment_author; ?>',$template);
$template = str_replace("%comment_entire",'<?php echo $filosofo_cp_class->filter_comment($comment->comment_content); ?>',$template);
$template = str_replace("%comment_id",'<?php echo $comment->comment_ID; ?>',$template);
$template = str_replace("%comment_link",'<?php echo $filosofo_cp_post_permalink ?>#comment-<?php echo $comment->comment_ID; ?>',$template);
$template = str_replace("%comment_date",'<?php echo mysql2date( $subpage_general_array[\'comments_settings_date_format\'], $comment->comment_date); ?>',$template);
$template = str_replace("%comment_time",'<?php echo mysql2date( $subpage_general_array[\'comments_settings_time_format\'], $comment->comment_date); ?>',$template);
$template = str_replace("%comment_type",'<?php comment_type(__(\'Comment\'), __(\'Trackback\'), __(\'Pingback\')); ?>',$template);
$template = str_replace("%previewed_author_link",'<?php if (empty($url)) :  echo $author; else: echo "<a href=\'$url\' rel=\'external\'>$author</a>"; endif; ?>',$template);
$template = str_replace("%previewed_author",'<?php echo $author; ?>',$template);
$template = str_replace("%previewed_buttons",$previewed_buttons,$template);
$template = str_replace("%previewed_comment",'<?php echo $fcp_comment; ?>',$template);
$template = str_replace("%previewed_email",'<?php echo $email; ?>',$template);
$template = str_replace("%previewed_form_submit_path",'<?php echo get_settings(\'siteurl\'); ?>/wp-content/plugins/filosofo-comments-preview.php',$template);
$template = str_replace("%previewed_prev_comments",'<?php $subpage_general_array = $filosofo_cp_class->get_option(\'filosofo_cp_subpage_general_array\'); if ($subpage_general_array[\'comments_settings_show\'] == 1) { echo filosofo_cp_display_previous_comments(); } ?>',$template);
$template = str_replace("%previewed_raw_comment",'<?php echo stripslashes($raw_comment); ?>',$template);
$template = str_replace("%previewed_url",'<?php echo $url; ?>',$template);
$template = str_replace("%userid",'<?php echo $comment->user_id; ?>',$template);
return $template;
} //end   template_format


//***************************************************************
function dirify($s) {
// takes out problematic characters for URLs (or DB entries)
//***************************************************************
     $s = sanitize_title($s); 			## take out weird characters		
     $s = strtolower($s);           		## lower-case.
     $s = strip_tags($s);       		## remove HTML tags.
     $s = preg_replace('!&[^;\s]+;!','',$s);    ## remove HTML entities.
     $s = preg_replace('![^\w\s]!','',$s);      ## remove non-word/space chars.
     $s = preg_replace('!\s+!','_',$s); 	## change space chars to underscores.
     return $s;    
} //end dirify


//***************************************************************
function get_the_files_content($comments_path) {
//gets the file's content, even for PHP 4.2
//***************************************************************
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
	return $filosofo_cp_class->submitbuttons($variable);
}
}

if((!get_option('filosofo_cp_version')) || ($filosofo_cp_version > get_option('filosofo_cp_version'))) 
	$filosofo_cp_class->upgrade();
	
if(get_settings('date_format')) $date_format = get_settings('date_format');
else $date_format = 'F j, Y';
if(get_settings('time_format')) $time_format = get_settings('time_format');
else $time_format = 'g:i a';

if(!function_exists('filosofo_cp_display_previous_comments')) {
//*****************************************************************************
function filosofo_cp_display_previous_comments() {
// displays the previous comments in the preview
global $filosofo_cp_class, $wpdb, $filosofo_cp_post_id, $filosofo_cp_post_permalink, $user_ID, $comment_post_ID;
// calls: filosofo_cp_get_option, _e, 
//*****************************************************************************
$subpage_general_array = $filosofo_cp_class->get_option('filosofo_cp_subpage_general_array');
//if we are to show the previous comments on the preview page
if($subpage_general_array['comments_settings_show']) {
	$id = $filosofo_cp_post_id;
	$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = $id AND comment_approved = '1' ORDER BY comment_date");
	//if there actually are comments
	if(is_array($comments)) {
		//if the previous comments should be shown in reverse order
		if($subpage_general_array['comments_settings_reverse']) {
			$comments = array_reverse($comments);
		}
		// These variables are for alternating comment background */
		$oddcommentA = $subpage_general_array['comments_settings_oddcomment_class'];
		$oddcommentB = $subpage_general_array['comments_settings_evencomment_class'];
		$oddcomment = $oddcommentA;

		if ($comments) :
			eval('?> ' . stripslashes($subpage_general_array['comments_header']));
			foreach ($comments as $comment) : ?>
				<?php /* Changes every other comment to a different class */    
				if ($oddcommentA == $oddcomment) $oddcomment = $oddcommentB;
				else $oddcomment = $oddcommentA;
				eval('?> ' . stripslashes($filosofo_cp_class->template_format($subpage_general_array['comments_template'])));
				if (user_can_edit_post_comments($user_ID, $comment_post_ID)) {
					$location = get_settings('siteurl') . "/wp-admin/post.php?action=editcomment&amp;comment=$comment->comment_ID";
					echo " | <a href='$location'>";
					_e('Edit');
					echo "</a> |";
				}
			endforeach;
			eval('?> ' . stripslashes($subpage_general_array['comments_footer']));
		else : // this is displayed if there are no comments so far ?>
			<?php if ( comments_open() ) : ?>
				<!-- If comments are open, but there are no comments. -->
				<p class="nocomments"><?php _e('No comments yet.'); ?></p>
			<?php else : // comments are closed ?>
				<!-- If comments are closed. -->
				<p class="nocomments"><?php _e('Comments are closed.'); ?></p>
			<?php endif; ?>
		<?php endif;       
	} //end if there actually are comments
} //end if we are to show the previous comments on the preview page

} //end display_previous_comments
}




if ($_GET['captcha_image']) {
	$filosofo_cp_captcha_array = $filosofo_cp_class->get_option('filosofo_cp_captcha_array');
	$pos= strpos($_SERVER['REQUEST_URI'], '?');
	$basename = basename(substr($_SERVER['REQUEST_URI'], 0, $pos));
	if($basename==basename(__FILE__)) {
		$filosofo_cp_class->captcha_image($_GET['random_num'],$filosofo_cp_captcha_array['num_length'],$filosofo_cp_captcha_array['circles'],$filosofo_cp_captcha_array['lines'],$filosofo_cp_captcha_array['width'],$filosofo_cp_captcha_array['height'],$filosofo_cp_captcha_array['font'],$filosofo_cp_captcha_array['bgred'],$filosofo_cp_captcha_array['bggreen'],$filosofo_cp_captcha_array['bgblue'],$filosofo_cp_captcha_array['txred'],$filosofo_cp_captcha_array['txgreen'],$filosofo_cp_captcha_array['txblue'],$filosofo_cp_captcha_array['rperc'],$filosofo_cp_captcha_array['gperc'],$filosofo_cp_captcha_array['bperc']);
	}
}

elseif ($_GET['test_num']) {
	$filosofo_cp_class->captcha_image($_GET['test_num'],$_GET['num_length'],$_GET['cirles'],$_GET['lines'],$_GET['width'],$_GET['height'],$_GET['font'],$_GET['bgred'],$_GET['bggreen'],$_GET['bgblue'],$_GET['txred'],$_GET['txgreen'],$_GET['txblue'],$_GET['rperc'],$_GET['gperc'],$_GET['bperc']);
}
//*****************************************************************************
//  End Captcha stuff
//*****************************************************************************



//if someone's submitting a comment (both for previewing and direct submit)
if (isset($_POST['comment']) && isset($_POST['comment_post_ID'])) {
	$filosofo_cp_subpage_general_array = $filosofo_cp_class->get_option('filosofo_cp_subpage_general_array');

	
	$filosofo_cp_post_permalink = trim($_POST['filosofo_cp_post_permalink']);
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
		if ( !$filosofo_cp_class->captcha_human_check($random_num, $code, $filosofo_cp_captcha_array['salt'],$filosofo_cp_captcha_array['num_length']))
			die( __('Error: please type the security code.'));
	}
	//end captcha action

	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');
	//if someone submits a preview
	if($_POST['submit'] == $filosofo_cp_subpage_general_array['prev_button_text']){
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
  
	setcookie('comment_author_' . COOKIEHASH, stripslashes($comment_author), time() + 30000000, COOKIEPATH);
	setcookie('comment_author_email_' . COOKIEHASH, stripslashes($comment_author_email), time() + 30000000, COOKIEPATH);
	setcookie('comment_author_url_' . COOKIEHASH, stripslashes($comment_author_url), time() + 30000000, COOKIEPATH);
  
	header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	
	//send the viewer back to the post with the comment now added
	if (isset($_POST['filosofo_cp_is_popup'])) 
		$location = get_settings('siteurl') . '?comments_popup=' . $comment_post_ID;
	else	
		$location = get_permalink($comment_post_ID);

	if(function_exists('wp_redirect')) {  
		wp_redirect($location);
	}
	//pre-WordPress 1.5.1.3
	else {
		header("Location: $location");
	}
}

//else someone's not submitting a comment
else {
	add_action('options_page_filosofo-comments-preview', array(&$filosofo_cp_class,'options_page'));
	add_action('admin_menu', array(&$filosofo_cp_class,'add_options_page'),1);
	add_action('wp_head', array(&$filosofo_cp_class,'alert_scripts'));
	
	add_filter('comments_template', array(&$filosofo_cp_class,'replace_comments_file'));
	add_filter('comments_popup_template', array(&$filosofo_cp_class,'replace_popup_file'));	
}






?>
