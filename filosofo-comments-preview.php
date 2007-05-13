<?php
/*
Plugin Name: Filosofo Comments Preview
Plugin URI: http://www.ilfilosofo.com/blog/comments-preview/
Description: Filosofo Comments Preview lets you preview WordPress comments before you submit them.  
Version: 1.0.2
Author: Austin Matzko
Author URI: http://www.ilfilosofo.com/blog/
*/

/*  Copyright 2007  Austin Matzko  (email : if.website at gmail.com)

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

class filosofo_cp {
	function filosofo_cp() {
		global $wpdb;

		$this->version = 1.0;
		$this->preview_comment_id = round($wpdb->get_var("SELECT MAX(comment_ID) FROM $wpdb->comments") + 1000, -3);
		$this->options_page_id = 'filosofo-comments-preview-page';
		$this->counter = 0;

		add_action('init', array(&$this,'init'));
		add_action('activate_' . basename(__FILE__), array(&$this,'activate_plugin'));
		add_action('admin_menu', array(&$this,'menu'));
		add_action('comment_form', create_function('$a','global $filosofo_cp_class; ob_end_flush(); $filosofo_cp_class->flush = false;'));
		// flush if not already done
		add_action('wp_footer', create_function('$a','global $filosofo_cp_class; if ( true == $filosofo_cp_class->flush ) { ob_end_flush(); $filosofo_cp_class->flush = false; }'));
		if ( ! $this->older_system() ) 
			add_filter('comments_array', array(&$this,'add_previewed_comment'));
		if( isset( $_POST['comment_post_ID'] ) && isset( $_POST['author'] ) ) {
			$_POST['filosofo_cp_author'] = $_POST['author'];
			unset( $_POST['author'] );
		}
		if( 'ACTIVE' == get_option('filosofo_cp_styling') ) 
			add_action('wp_head', array(&$this,'header_style'));

		// add the preview button
		add_filter('comments_template', create_function('$a','global $filosofo_cp_class; $filosofo_cp_class->pagekind = "standard"; ob_start(array(&$filosofo_cp_class,"replace_button")); $filosofo_cp_class->flush = true; return $a;'));
		add_filter('comments_popup_template', create_function('$a','global $filosofo_cp_class; $filosofo_cp_class->pagekind = "popup"; ob_start(array(&$filosofo_cp_class,"replace_button")); $filosofo_cp_class->flush = true; return $a;'));
	}

	function activate_plugin() {
		if ( '' == get_option('filosofo_cp_styling') ) {
			update_option('filosofo_cp_styling','ACTIVE');	
			update_option('filosofo_cp_bgcolor','#FFFF33');
		}
		update_option('filosofo_cp_version',$this->version);	
	}

	function is_popup_template() {
		if ( isset( $_REQUEST['comments_popup'] ) ) return true;
		else return false;
	}

	function using_kubrick() { // a hack to make kubrick preview buttons look good
		if ( function_exists('kubrick_head') ) return true;
		else return false;
	}

	function menu() {
		add_options_page(__('Filosofo Comments Preview','filosofo-comments-preview'), __('Comments Preview','filosofo-comments-preview'), 'edit_posts', $this->options_page_id, array(&$this,'options_page'));
	}

	function options_page() {
		if ( isset( $_POST['bgcolor'] ) ) :
			if ( '' == $_POST['bgcolor'] ) :
				update_option('filosofo_cp_styling','NONE');
			else :
				update_option('filosofo_cp_styling','ACTIVE');
			endif;
			update_option('filosofo_cp_bgcolor',$_POST['bgcolor']);
		endif;
		?>
		<div class="wrap"><h2><?php _e('Comments Preview','filosofo-comments-preview') ?></h2>
			<form name="preview_styling" method="post" action="?page=<?php 
			echo $this->options_page_id ?>&amp;updated=true"> 
			<input type="hidden" name="updated" id="updated" value="true" />
			<fieldset class="options">
				<legend><?php _e('Automatic Styling','filosofo-comments-preview') ?></legend>
				<div><div style="background-color: <?php echo get_option('filosofo_cp_bgcolor') ?>; border: 1px solid gray; width: 20px; height: 20px; margin-right: 3px; float: left;" title="<?php _e('This box displays the color for the preview&rsquo;s background.','filosofo-comments-preview') ?>">&nbsp;</div>
				<p><label <?php 
				if ( 'NONE' === get_option('filosofo_cp_styling') ) echo 'style="color: gray"';
				?>>
				<input type="text" value="<?php echo get_option('filosofo_cp_bgcolor') ?>" size="20" class="code<?php
				if ( 'NONE' === get_option('filosofo_cp_styling') ) echo ' disabled'; 
				?>" id="bgcolor" name="bgcolor" /> <?php 
				if ( 'ACTIVE' === get_option('filosofo_cp_styling') ) : 
					_e('Set the automatic preview&rsquo;s background color with a <acronym title="Cascading Style Sheets">CSS</acronym> color value.  Clear the input field to disable automatic styling.','filosofo-comments-preview');
				else : 
					_e('Automatic styling is disabled. Enter a <acronym title="Cascading Style Sheets">CSS</acronym> color value to style the preview&rsquo;s background color.','filosofo-comments-preview');
				endif;
				?></label></p></div>
				<p class="submit"><input type="submit" name="Update" value="<?php _e('Update Options &raquo;') ?>" /></p>
			</fieldset>
			<?php do_action('filosofo-comments-preview_options_form'); ?>
			</form>
		</div>
		<?php
	}

	function check_query($query) {
		global $post, $wpdb;
		$commenter = (array) wp_get_current_commenter();
		extract($commenter);
		if ( empty($comment_author) ) : 
			$the_query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved = '1' ORDER BY comment_date";
		else :
			$author_db = $wpdb->escape($comment_author);
			$email_db  = $wpdb->escape($comment_author_email);
			$the_query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND ( comment_approved = '1' OR ( comment_author = '$author_db' AND comment_author_email = '$email_db' AND comment_approved = '0' ) ) ORDER BY comment_date";
		endif;
		$the_query_two = '';
		if ( $this->is_popup_template() ) : 
			$the_query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved = '1' ORDER BY comment_date";
			// messed up query in older version of get_approved_comments()
			$the_query_two = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_approved = '1' ORDER BY comment_date";
		endif;
		if ( $query == $the_query ) : return true; 
		elseif ( '' != $the_query_two && $query == $the_query_two ) : return true;
		else : return false;
		endif;
	}

	function add_previewed_comment( $comments = array() , $comment_post_ID = 0 ) {
		return array_merge( $comments , $this->previewed_comment( $comment_post_ID ) );
	}

	function header_style() {
		$template = get_template_directory();
		if ( $this->preview_submitted() || $this->using_kubrick() ) :
		?>	
		<style type="text/css">
			<?php if ( $this->using_kubrick() ) : ?>
			#commentform #preview {
				float:left;
				margin:0pt;
			}
			<?php endif; ?>
			<?php if ( $this->preview_submitted() ) : ?>
			#comment-<?php echo $this->preview_comment_id ?> {
				background-color: <?php echo get_option('filosofo_cp_bgcolor') ?>;
			}
			<?php endif; ?>
		</style>
		<?php
		endif; 
	}

	function previewed_comment( $comment_post_ID = 0 ) {
		global $fcp_comment_author, $fcp_comment_author_email, $fcp_comment_author_url, $fcp_comment_content, $fcp_comment_post_ID, $fcp_comment_type, $fcp_user_ID;
		if ( $this->preview_submitted() ) :
			$preview_header = '<strong id="previewed-comment-header">' . apply_filters('filosofo-comments-preview_previewed_comment_header',__('Previewed comment:','filosofo-comments-preview')) . "</strong>\n\n";
			$c['comment_ID'] = $this->preview_comment_id;
			$c['comment_post_ID'] = $fcp_comment_post_ID;
			$c['comment_author'] = $fcp_comment_author;
			$c['comment_author_email'] = $fcp_comment_author_email;
			$c['comment_author_url'] = $fcp_comment_author_url;
			$c['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
			$c['comment_date'] = current_time('mysql');
			$c['comment_date_gmt'] = current_time('mysql',1);
			$c['comment_content'] = $fcp_comment_content;
			$c['comment_karma'] = 0;
			$c['comment_approved'] = 1;
			$c['comment_agent'] = $_SERVER[HTTP_USER_AGENT];
			$c['comment_type'] = '';
			$c['comment_parent'] = 0;
			$c['user_id'] = $fcp_user_ID;
			$c['comment_is_preview'] = true;
			$ca = wp_filter_comment($c); // apply WP pre-save filters
			foreach ( (array) $ca as $k => $v )
				$comment->{$k} = stripslashes($v);
			$comment->comment_content = $preview_header . $comment->comment_content;
			$comment = apply_filters('filosofo-comments-preview_comment', $comment);
			return array(0 => $comment);
		else :
			return array();
		endif;
	}

	function preview_submitted() {
		if (isset($_POST['comment']) && isset($_POST['comment_post_ID']) && isset($_POST['preview']))
			return true;
		else return false;
	}

	function replace_button($content) {
		global $id, $raw_comment;
		if ( 0 < (int) $this->counter ) return $content; // popup calls this twice in older versions of WP
		$this->counter++;  
		$link = add_query_arg(	
			(( 'popup' == $this->pagekind ) ? array('comments_popup' => $id ) : array()),
			get_permalink($id)) . '#comment-' . $this->preview_comment_id;
		$content = str_replace(array( get_option('siteurl') . '/wp-comments-post.php', '/wp-comments-post.php'), $link, $content);
		if ( !strpos($content,'id="preview"') && strpos($content,'comment_post_ID')) { 
			// search reversed strings to get last input first
			$p1 = array('#>/[^>]*("|\')timbus("|\')=eman[^>]*tupni<#i');
			$p2 = array('#>nottub/<.*>[^>]*("|\')timbus("|\')=epyt[^>]*nottub<#i');	
			if ( false !== strpos( $content, '<button' )) {
				add_filter('filosofo-comments-preview_input_array', array(&$this,'use_buttons'));	
				$p1 = array_merge( $p1, $p2 );
			}
			$content = strrev(preg_replace($p1,strrev($this->submitbuttons()),strrev($content),1));
		}
		if ( $this->preview_submitted() )
			$content = str_replace('</textarea>',stripslashes($raw_comment) . '</textarea>',$content);	
		return apply_filters('filosofo-comments-preview_form', $content);
	} 

	function use_buttons( $inputs = array() ) { // use buttons instead of inputs for the submit tags
		foreach ( array( 'preview','submit' ) as $tag ) {
			$t = $inputs[$tag];
			$t['element'] = 'button';
			$t['childnode'] = array( 'text' => $t['attribs']['value'] );
			unset( $t['attribs']['value'] );
			$inputs[$tag] = $t;
		}
		return $inputs;
	}

	function generate_markup( $nodes = array() ) { 
		$str = '';
		foreach( $nodes as $id => $dom ) {
			if ( 'text' == $id ) return (string) $dom;
			$str .= "<{$dom['element']} id=\"{$id}\" ";
			foreach ( (array) $dom['attribs'] as $attrib => $value ) 
				$str .= "{$attrib}=\"{$value}\" ";
			if (isset($dom['childnode']))
				$str .= '>' . $this->generate_markup( $dom['childnode'] ) . "</{$dom['element']}>";
			else $str .= '/>';
		}
		return $str;
	}

	function submitbuttons() { 
		global $id;
		$input_array = array(
			'preview' => array(
				'element' => 'input',
				'attribs' => array(
					'type' => 'submit',
					'name' => 'preview',
					'tabindex' => '5',
					'value' => apply_filters('filosofo-comments-preview_preview_text',__('Preview','filosofo-comments-preview')),
				),
			),
			'submit' => array(
				'element' => 'input',
				'attribs' => array(
					'type' => 'submit',
					'name' => 'submit',
					'tabindex' => '6',
					'style' => 'font-weight: bold',
					'value' =>  apply_filters('filosofo-comments-preview_submit_text',__('Post','filosofo-comments-preview')),
				),
			),
		);
		$input_array = apply_filters('filosofo-comments-preview_input_array', $input_array);
		return $this->generate_markup( $input_array );
	} //end submitbuttons 

	function older_system() {
		if ( ! function_exists('wp_schedule_event') ) return true;
		else return false;
	}

	function init() {
		global $fcp_comment_author, $fcp_comment_author_email, $fcp_comment_author_url, $fcp_comment_content, $fcp_comment_post_ID, $fcp_comment_type, $fcp_user_ID, $raw_comment, $wpdb;
		load_plugin_textdomain('filosofo-comments-preview');
		//if someone's submitting a comment (both for previewing and direct submit)
		if (isset($_POST['comment']) && isset($_POST['comment_post_ID'])) {
			$comment_post_ID = (int) trim($_POST['comment_post_ID']);
			$status = $wpdb->get_row("SELECT post_status, comment_status FROM $wpdb->posts WHERE ID = '$comment_post_ID'");

			if ( empty($status->comment_status) ) {
				do_action('comment_id_not_found', $comment_post_ID);
				exit;
			} 
			elseif ( 'closed' ==  $status->comment_status ) {
				do_action('comment_closed', $comment_post_ID);
				wp_die( __('Sorry, comments are closed for this item.') );
			} 
			elseif ( 'draft' == $status->post_status ) {
				do_action('comment_on_draft', $comment_post_ID);
				exit;
			}
			
			$comment_author       = trim($_POST['filosofo_cp_author']);
			$comment_author_email = trim($_POST['email']);
			$comment_author_url   = trim($_POST['url']);
			$comment_content      = trim($_POST['comment']);

			// If the user is logged in
			$user = wp_get_current_user();
			$user_ID = $user->ID;
			if ( $user_ID ) :
				$comment_author       = $wpdb->escape($user->display_name);
				$comment_author_email = $wpdb->escape($user->user_email);
				$comment_author_url   = $wpdb->escape($user->user_url);
			else :
				if ( get_option('comment_registration') ) 
					wp_die( __('Sorry, you must be logged in to post a comment.') );
			endif;
			$comment_type = '';
			if ( get_option('require_name_email') && !$user_ID ) { 
				if ( 6 > strlen($comment_author_email) || '' == $comment_author )
					wp_die( __('Error: please fill the required fields (name, email).') );
				elseif ( !is_email($comment_author_email))
					wp_die( __('Error: please enter a valid email address.') );
			}
			if ( '' == $comment_content )
				wp_die( __('Error: please type a comment.') );

			$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');
			$fcp_comment_post_ID 		= $comment_post_ID;
			$fcp_comment_author 		= $comment_author;
			$fcp_comment_author_email	= $comment_author_email;
			$fcp_comment_author_url		= $comment_author_url;
			$fcp_comment_content		= $comment_content;
			$fcp_comment_type		= $comment_type;
			$fcp_user_ID			= $user_ID;
			if ( !$user_ID ) {
				setcookie('comment_author_' . COOKIEHASH, $fcp_comment_author, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
				setcookie('comment_author_email_' . COOKIEHASH, $fcp_comment_author_email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
				setcookie('comment_author_url_' . COOKIEHASH, clean_url($fcp_comment_author_url), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
			}
			//if someone submits a preview
			if( $this->preview_submitted() ) :
				// set cookie server variables for preview
				$_COOKIE['comment_author_' . COOKIEHASH] = $fcp_comment_author;
				$_COOKIE['comment_author_email_' . COOKIEHASH] = $fcp_comment_author_email;
				$_COOKIE['comment_author_url_' . COOKIEHASH] = $fcp_comment_author_url;

				$raw_comment = htmlspecialchars($comment_content);
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
			else : 
				$comment_id = wp_new_comment( $commentdata );
				$comment = get_comment($comment_id);
				//send the viewer back to the post with the comment now added
				$location = ( empty($_POST['redirect_to']) ? get_permalink($comment_post_ID) : $_POST['redirect_to'] ) . '#comment-' . $comment_id;
				$location = apply_filters('comment_post_redirect', $location, $comment);
				wp_redirect($location);
				exit;
			endif; //end if someone submits a preview
		}
	}
} //end filosofo_cp class

$filosofo_cp_class = new filosofo_cp();

//for backwards compatibility
if ( $filosofo_cp_class->older_system() || $filosofo_cp_class->is_popup_template() ) :
	class filosofo_cp_wpdb extends wpdb {
		function get_results($query = null, $output = OBJECT) {
			global $filosofo_cp_class, $id;
			$this->func_call = "\$db->get_results(\"$query\", $output)";
			if ( $query )
				$this->query($query);

			// where the magic hack happens 
			if ( $filosofo_cp_class->check_query( $query ) ) :
				$this->last_result = $filosofo_cp_class->add_previewed_comment( $this->last_result );	
			endif;
			// end the magic hack

			// Send back array of objects. Each row is an object
			if ( $output == OBJECT ) {
				return $this->last_result;
			} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
				if ( $this->last_result ) {
					$i = 0;
					foreach( $this->last_result as $row ) {
						$new_array[$i] = (array) $row;
						if ( $output == ARRAY_N ) {
							$new_array[$i] = array_values($new_array[$i]);
						}
						$i++;
					}
					return $new_array;
				} else {
					return null;
				}
			}
		}
	}
	$wpdb =& new filosofo_cp_wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

	// Table names
	$wpdb->posts            = $table_prefix . 'posts';
	$wpdb->users            = $table_prefix . 'users';
	$wpdb->categories       = $table_prefix . 'categories';
	$wpdb->post2cat         = $table_prefix . 'post2cat';
	$wpdb->comments         = $table_prefix . 'comments';
	$wpdb->links            = $table_prefix . 'links';
	$wpdb->link2cat		= $table_prefix . 'link2cat';
	$wpdb->linkcategories   = $table_prefix . 'linkcategories';
	$wpdb->options          = $table_prefix . 'options';
	$wpdb->postmeta         = $table_prefix . 'postmeta';
	$wpdb->usermeta         = $table_prefix . 'usermeta';
	$wpdb->prefix           = $table_prefix;

endif; 

if(!function_exists('wp_die')) {
	function wp_die($msg = '') {
		die($msg);
	}
}

if(!function_exists('wp_get_current_commenter')) :
	function wp_get_current_commenter() {
		$comment_author = '';
		if ( isset($_COOKIE['comment_author_'.COOKIEHASH]) )
			$comment_author = $_COOKIE['comment_author_'.COOKIEHASH];
		$comment_author_email = '';
		if ( isset($_COOKIE['comment_author_email_'.COOKIEHASH]) )
			$comment_author_email = $_COOKIE['comment_author_email_'.COOKIEHASH];
		$comment_author_url = '';
		if ( isset($_COOKIE['comment_author_url_'.COOKIEHASH]) )
			$comment_author_url = $_COOKIE['comment_author_url_'.COOKIEHASH];
		return compact('comment_author', 'comment_author_email', 'comment_author_url');
	}
endif;

if ( !function_exists('wp_get_current_user') ) {
	function wp_get_current_user() {
		global $current_user;
		get_currentuserinfo();
		return $current_user;
	}
}

if(!function_exists('filosofo_cp_submitbuttons')) {
	function filosofo_cp_submitbuttons($variable) {
		global $filosofo_cp_class;
		$filosofo_cp_class->pagekind = $variable;
		echo $filosofo_cp_class->submitbuttons();
	}
}
//end backwards compatibility

if(!function_exists('comment_is_preview')) {
	function comment_is_preview( $id = 0 ) {
		global $comment, $filosofo_cp_class;
		$id = (int) $id;
		if ( ( 0 == $id && isset( $comment->comment_is_preview ) && true == $comment->comment_is_preview ) ||
			$filosofo_cp_class->preview_comment_id == $id )
			return true;
		else return false;
	}
}
add_filter('comment_is_preview', 'comment_is_preview');
?>
