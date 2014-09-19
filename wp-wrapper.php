<?php
/*
Plugin Name: WP Wrapper
Plugin URI: http://www.nabtron.com/wp-wrapper/
Description: Wrapper plugin for wordpress
Version: 1.1.3
Author: Nabtron
Author URI: http://nabtron.com/
Min WP Version: 2.5
Max WP Version: 4.0
*/

// declaring classes and functions

class Walker_PageDropdownnew extends Walker {

	var $tree_type = 'page';

	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	function start_el(&$output, $page, $depth, $args) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"$page->ID\"";
		if ( $page->ID == get_option("nabwrap_page") )
			$output .= ' selected="selected"';
		$output .= '>';
		$title = esc_html($page->post_title);
		$title = apply_filters( 'list_pages', $page->post_title );
		$output .= "$pad$title";
		$output .= "</option>\n";
	}
}

function walk_page_dropdown_treenew() {
	$args = func_get_args();
	if ( empty($args[2]['walker']) ) // the user's options are the third parameter
		$walker = new Walker_PageDropdownnew;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array(&$walker, 'walk'), $args);
}

function wp_dropdown_pagesnab($args = '') {
	$defaults = array(
          'depth' => 0, 'child_of' => 0,
          'selected' => 0, 'echo' => 1,
          'name' => 'page_id', 'id' => '',
          'show_option_none' => '', 'show_option_no_change' => '',
          'option_none_value' => ''
        );

	$r = wp_parse_args( $args, $defaults );
        extract( $r, EXTR_SKIP );

        $pages = get_pages($r);
        $output = '';
        $name = esc_attr($name);
        // Back-compat with old system where both id and name were based on $name argument
        if ( empty($id) )
                $id = $name;

        if ( ! empty($pages) ) {
                $output = "<select name=\"$name\" id=\"$id\">\n";
                $output .= walk_page_dropdown_treenew($pages, $depth, $r);
                $output .= "</select>\n";
        }

        $output = apply_filters('wp_dropdown_pages', $output);

        if ( $echo )
                echo $output;
}

function nabwrap_protocol_select() {
	$output = '<select name="nabwrap_protocol">';
	$selected_protocol =  get_option("nabwrap_protocol");
	$protocol_list = array('http://','https://');
	foreach($protocol_list as $key=>$value) {
		$selected = '';
		if($value == $selected_protocol){
			$selected = ' selected = "selected" ';
		}
		$output .= '<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
	}
	$output .= '</select>';
	echo $output;
}

function nabwrap_addlink() {
	$addlink_or_not = get_option("nabwrap_addlink");
	$checked = '';
	if($addlink_or_not == '1'){
		$checked = ' checked="yes" ';
	}
	$output = '<input type="checkbox" name="nabwrap_addlink" value="1" '.$checked.'/>';
	echo $output;
}

// Update routines
if ('insert' == $_POST['action_nabwrap']) {
        update_option("nabwrap_protocol",$_POST['nabwrap_protocol']);
        update_option("nabwrap_url",$_POST['nabwrap_url']);
        update_option("nabwrap_page",$_POST['nabwrap_page']);
        update_option("nabwrap_width",$_POST['nabwrap_width']);
        update_option("nabwrap_height",$_POST['nabwrap_height']);
        update_option("nabwrap_border",$_POST['nabwrap_border']);
        update_option("nabwrap_scroll",$_POST['nabwrap_scroll']);
        update_option("nabwrap_addlink",$_POST['nabwrap_addlink']);
}

if (!class_exists('nabwrap_main')) {
	class nabwrap_main {
		// PHP 4 Compatible Constructor
		function nabwrap_main(){$this->__construct();}

		// PHP 5 Constructor
		function __construct(){
			add_action('admin_menu', 'nabwrap_description_add_menu');
			add_filter('the_content', 'get_nabwrapper_id');
		}
	}

	function nabwrap_description_option_page() {
	?>

		<!-- Start Options Admin area -->
		<div class="wrap">
		  <h2>WP Wrapper Options</h2>
		  <div style="margin-top:20px;">
		    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>&amp;updated=true">
		      <div style="">
		      <table class="form-table">
		        <tr>
		          <th scope="col" colspan="3" cellpadding="15"><strong>Settings</strong></th>
		        </tr>
		        <tr>
		          <th scope="row"><strong>Url</strong></th>
		          <td><?php nabwrap_protocol_select(); ?><input name="nabwrap_url" size="25" value="<?=get_option("nabwrap_url");?>" type="text" />
		            Enter url without http://</td>
		        </tr>
		        <tr>
		          <th scope="row"><strong>Page</strong></th>
		          <td><?php wp_dropdown_pagesnab('name=nabwrap_page'); ?>
		            Select the page on which you want the wrapper to appear</td>
		        </tr>
		        <tr>
		          <th scope="row"><strong>width</strong></th>
		          <td><input name="nabwrap_width" size="10" value="<?=get_option("nabwrap_width");?>" type="text" />
		            specify width in px for the wrapper (can be in % too)</td>
		        </tr>
		        <tr>
		          <th scope="row"><strong>height</strong></th>
		          <td><input name="nabwrap_height" size="10" value="<?=get_option("nabwrap_height");?>" type="text" />
		            specify height in px for the wrapper (can be in % too)</td>
		        </tr>
		        <tr>
		          <th scope="row"><strong>border</strong></th>
		          <td><input name="nabwrap_border" size="10" value="<?=get_option("nabwrap_border");?>" type="text" />
		            Either 1 (yes) or 0 (no)</td>
		        </tr>
		        <tr>
		          <th scope="row"><strong>scroll</strong></th>
		          <td><input name="nabwrap_scroll" size="10" value="<?=get_option("nabwrap_scroll");?>" type="text" /> 
		            yes | no | auto</td>
		        </tr>
		        <tr>
		          <th scope="row"><strong>add link to nabtron</strong></th>
		          <td><?php nabwrap_addlink(); ?>
		            ( checking this will add "Powered by <a href="http://nabtron.com/" target="_blank">Nabtron</a>" below the wrapper )</td>
		        </tr>
		      </table>
		      <br>
		      <p class="submit_nabwrap">
		        <input name="submit_nabwrap" type="submit" id="submit_nabwrap" value="Save changes &raquo;">
		        <input class="submit" name="action_nabwrap" value="insert" type="hidden" />
		      </p>
		    </form>
		    <br /><br /><hr />
		    <center><h4>Developed by <a href="http://nabtron.com/" target="_blank">Nabtron</a>.</h4></center>
		  </div>
		</div>

	<?php
	} // End function nabwrap_description_option_page()

	// Admin menu Option
	function nabwrap_description_add_menu() {
		add_options_page('WP Wrapper Options', 'WP Wrapper', 'manage_options', __FILE__, 'nabwrap_description_option_page');
	}

	function get_nabwrapper_id($content) {
        $nabwrap_page  = get_option('nabwrap_page');
		if(is_page($nabwrap_page)) { 
			$nabwrap_protocol  = get_option('nabwrap_protocol');
			$nabwrap_url  = get_option('nabwrap_url');
			$nabwrap_width  = get_option('nabwrap_width');
			$nabwrap_height  = get_option('nabwrap_height');
			$nabwrap_border  = get_option('nabwrap_border');
			$nabwrap_scroll  = get_option('nabwrap_scroll');
			$nabwrap_addlink  = get_option('nabwrap_addlink');

			// standard initialisation (default values)	
			if ( $nabwrap_protocol == null) { $nabwrap_protocol  = "http://"; }
			if ( $nabwrap_url == null) { $nabwrap_url  = "google.com/"; }

			$content .= '<iframe width="' . $nabwrap_width . '" height="' . $nabwrap_height . '" src="'.$nabwrap_protocol.$nabwrap_url.'" frameBorder="' . $nabwrap_border . '" scrolling="' . $nabwrap_scroll . '"></iframe>';
			if($nabwrap_addlink == '1'){
				$content .= '<p style="text-align:center">Powered by <a href="http://nabtron.com" target="_blank">Nabtron</a></p>';
			}
		}
		return $content;
	}
}

//instantiate the class
if (class_exists('nabwrap_main')) {
	$nabwrap_main = new nabwrap_main();
}
?>