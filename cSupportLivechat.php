<?php
/*
Plugin Name: cSupport Live Chat Button
Plugin URI: http://csupporthq.com/
Description: Adds a cSupport live chat button to your wordpress site
Author: cSupport
Version: 1.3.4
Author URI: http://csupporthq.com/
*/

if (class_exists('cSupportLiveChat')) {
	new cSupportLiveChat();
}

class cSupportLiveChat {
	public function __construct() {
		// Add hook for localization
		add_action('plugins_loaded', array($this,'l10n'));

		// Add hook for attaching cSupport tag
		add_action('wp_footer',  array($this,'printTag'));

		// Add uninstall/install hooks
		register_activation_hook(__FILE__, array($this,'install')); 
		register_deactivation_hook( __FILE__,  array($this,'remove'));

		if(is_admin()){
			add_action('admin_menu', array($this,'adminMenu'));

			// Function to add farbtastic
			add_action('init', array($this,'ilc_farbtastic_script'));
		}
	}

	// Localization of text strings
	public function l10n() {
		error_log('yes');
		$loaded = load_plugin_textdomain('csupport-live-chat', false, dirname(plugin_basename(__FILE__)) . '/languages/');
		if (!$loaded) error_log('no loading');
		else error_log('loaded' . WPLANG);
	}

	// Function to build cSupport tag
	public function getTag()
	{

		// Load in data
		$csupport_domain	= get_option('cSupportLivechat_data_website');
		$csupport_type		= get_option('cSupportLivechat_data_type');
		$csupport_autofill	= get_option('cSupportLivechat_data_autofill');
		$csupport_autostart	= get_option('cSupportLivechat_data_autostart');
		$csupport_margin	= get_option('cSupportLivechat_data_margin');
		$csupport_position	= get_option('cSupportLivechat_data_position');
		$csupport_bgcolor	= get_option('cSupportLivechat_data_bgcolor');

		$tag = '';

		// Async script
		$tag .= '<!--  cSupport Live Chat Plugin --><script type="text/javascript">(function(d,c){var scrs=d.getElementsByTagName("script");var scr=scrs[scrs.length-1];var e=d.createElement("script");e.async=true;e.src=("https:"==document.location.protocol?"https://":"http://")+"' . $csupport_domain . '/external/' . $csupport_type . '.js?"+scrs.length;if(typeof c=="function")if(e.addEventListener)e.addEventListener("load",c,false);else if(e.readyState)e.onreadystatechange=function(){if(this.readyState=="loaded")c();};scr.parentNode.insertBefore(e,scr);})(document,null);';

		// Add auto-fill
		if ($csupport_autofill=='true')
		{
			// Get user information
			global $current_user;
			get_currentuserinfo();

			// Add information
			$tag .= "var cs_name=\"".($current_user->user_login)."\";var cs__mail=\"".($current_user->user__mail)."\";";

			// Add auto start
			if ($csupport_autostart=='true')
			{
				$tag .= "var cs_autostart=true;";
			}
		}

		// Add positioning
		if ($csupport_position == 't-l' || $csupport_position == 't-r' || $csupport_position == 'b-l' || $csupport_position == 'b-r')
		{
			$tag .= 'var cs_position="' . $csupport_position . '";';
		}

		// Add margin
		if (strlen($csupport_margin)>0 && is_numeric($csupport_margin))
		{
			if ($csupport_margin>100) $csupport_margin=100;
			elseif ($csupport_margin<-10) $csupport_margin=-10;
			$tag .= 'var cs_margin="' . intVal($csupport_margin) . 'px";';	
		}

		// Add color
		if (preg_match('/^#[a-fA-F0-9]{6}(\/#[a-fA-F0-9]{6})?$/i', $csupport_bgcolor))
		{
			$tag .= 'var cs_bgcolor="' . $csupport_bgcolor . '";';
		}

		$tag .= "</script><!--	//cSupport Live chat Plugin -->";

		return $tag;
	}

	// Function to print cSupport chat
	public function printTag()
	{
		echo $this->getTag();
	}

	// Which data fields to install
	public function install() {
		add_option("cSupportLivechat_data_website", '', '', 'yes');
		add_option("cSupportLivechat_data_type", '', '', 'yes');
		add_option("cSupportLivechat_data_autofill", 'true', '', 'yes');
		add_option("cSupportLivechat_data_autostart", 'false', '', 'yes');
		add_option("cSupportLivechat_data_margin", '', 'b-r', 'yes');
		add_option("cSupportLivechat_data_position", '', '', 'yes');
		add_option("cSupportLivechat_data_bgcolor", '', '', 'yes');
	}

	// Which data fields to remove
	public function remove() {
		delete_option('cSupportLivechat_data_website');
		delete_option('cSupportLivechat_data_type');
		delete_option('cSupportLivechat_data_autofill');
		delete_option('cSupportLivechat_data_autotart');
		delete_option('cSupportLivechat_data_margin');
		delete_option('cSupportLivechat_data_position');
		delete_option('cSupportLivechat_data_bgcolor');
	}

	// Admin menu
	public function adminMenu() {
		add_options_page('cSupport ' . __('Live Chat Options', 'csupport-live-chat'), 'cSupport ' . __('Options', 'csupport-live-chat'), 'administrator', 'cSupportLivechat', array($this,'adminHTMLPage'));
	}

	// Add admin settings page
	public function adminHTMLPage() {

		echo '
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>cSupport ' . __('Live Chat Options', 'csupport-live-chat') . '</h2>
	
	<form method="post" action="options.php">
	' . wp_nonce_field('update-options') . '
	
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="cSupportLivechat_data_website, cSupportLivechat_data_type, cSupportLivechat_data_autofill, cSupportLivechat_data_autostart, cSupportLivechat_data_margin, cSupportLivechat_data_position, cSupportLivechat_data_bgcolor " />
	
	<h3>' . __('General', 'csupport-live-chat') . '</h3>
	<table class="form-table">
			<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_website">' . __('Your cSupport domain', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				http://
				<input name="cSupportLivechat_data_website" type="text" id="cSupportLivechat_data_website" value="' . get_option('cSupportLivechat_data_website') . '" /><br />
				<span style="font-size: 8pt;"><strong>' . __('Note', 'csupport-live-chat') . ':</strong> ' . sprintf(__('Your cSupport domain is your unique address that you registered at cSupport, eg. %s', 'csupport-live-chat'), 'subdomain.csupporthq.com') . '</span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_type">' . __('Type', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				<select name="cSupportLivechat_data_type" id="cSupportLivechat_data_type">
					<option value="chat-float-inline" ' . (get_option('cSupportLivechat_data_type') == 'chat-float-inline' ? 'selected="selected"' : '') . '>' . __('Floating Inline Chat Button', 'csupport-live-chat') . '</option>
					<option value="chat-float" ' . (get_option('cSupportLivechat_data_type') == 'chat-float' ? 'selected="selected"' : '') . '>' . __('Floating Popup Chat Button', 'csupport-live-chat') . '</option>
				</select><br/>
				<span style="font-size: 8pt;"><strong>' . __('Help', 'csupport-live-chat') . ':</strong> ' . __('Inline means that the chat will stay inside the window. Popup means that the chat will popup in a new window.', 'csupport-live-chat') . '</span>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_autofill">' . __('Auto-fill', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				<input name="cSupportLivechat_data_autofill" type="checkbox" value="true" ' . (get_option('cSupportLivechat_data_autofill') == 'true' ? 'checked="checked"' : '') . 'id="cSupportLivechat_data_autofill" /> ' . __('When users are logged in, auto-fill with their information.', 'csupport-live-chat') . '
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_autostart">' . __('Auto start', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				<input name="cSupportLivechat_data_autostart" type="checkbox" value="true" ' . (get_option('cSupportLivechat_data_autostart') == 'true' ? 'checked="checked"' : '') . 'id="cSupportLivechat_data_autostart" /> ' . __('Try start the chat session right away when user clicks on chat button.', 'csupport-live-chat') . '<br/>
				<span style="font-size: 8pt;"><strong>' . __('Note', 'csupport-live-chat') . ':</strong> ' . __('Will only be used if the \'Auto-fill\' checkbox has been checked off.', 'csupport-live-chat') . '</span>
			</td>
		</tr>
		<tr>
			<th scope="row"></th>
			<td colspan="2">
				' . sprintf(__('No account? Start a free trial right away at %s', 'csupport-live-chat'), '<a href="http://csupporthq.com/pricing">http://csupporthq.com/pricing</a>') . '
			</td>
		</tr>
	</table>
	
	<h3>' . __('Layout', 'csupport-live-chat') . '</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_position">' . __('Position', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				<select name="cSupportLivechat_data_position" id="cSupportLivechat_data_position">
					<option value="b-r" ' . (get_option('cSupportLivechat_data_position') == 'b-r' ? 'selected' : '') . '>' . __('Bottom Right', 'csupport-live-chat') . '</option>
					<option value="b-l" ' . (get_option('cSupportLivechat_data_position') == 'b-l' ? 'selected' : '') . '>' . __('Bottom Left', 'csupport-live-chat') . '</option>
					<option value="t-r" ' . (get_option('cSupportLivechat_data_position') == 't-r' ? 'selected' : '') . '>' . __('Top Right', 'csupport-live-chat') . '</option>
					<option value="t-l" ' . (get_option('cSupportLivechat_data_position') == 't-l' ? 'selected' : '') . '>' . __('Top Left', 'csupport-live-chat') . '</option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_margin">' . __('Margin in pixels (only digits)', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				<input name="cSupportLivechat_data_margin" type="text" id="cSupportLivechat_data_margin" value="' . get_option('cSupportLivechat_data_margin') . '" /><br />
				<span style="font-size: 8pt;">' . sprintf(__('The margin in pixels from the edge of the window to the chat button. Use only digits (no \'px\' or \'%\'), in the range of %s to %s.', 'csupport-live-chat'), '-10', '100') . '</span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="cSupportLivechat_data_bgcolor">' . __('Color (HEX)', 'csupport-live-chat') . ':</label>
			</th>
			<td>
				<input name="cSupportLivechat_data_bgcolor" type="text" id="cSupportLivechat_data_bgcolor" value="' . get_option('cSupportLivechat_data_bgcolor') . '" /><br />
				<div id="ilctabscolorpicker"></div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		var colorPicker = jQuery.farbtastic("#ilctabscolorpicker",function(color){
													jQuery("#cSupportLivechat_data_bgcolor").css({"background-color":color,"color":(this.hsl[2]>0.5?"#000":"#fff")});
													jQuery("#cSupportLivechat_data_bgcolor").val(color);
												});
		if(/^(#[0-9a-fA-F]{6})$/.test(jQuery("#cSupportLivechat_data_bgcolor").val())) colorPicker.setColor(jQuery("#cSupportLivechat_data_bgcolor").val());
	});
</script>
				<span style="font-size: 8pt;">' . __('The base HEX color to use in the chat. Use a slash (/) with a second HEX color, to add a different color for offline mode.', 'csupport-live-chat') . '</span>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<input type="submit" id="submit" class="button-primary" value="' . __('Save Changes', 'csupport-live-chat') . '" />
	</p>
	</form>
</div>';
	}

	public function ilc_farbtastic_script(){
		wp_enqueue_style('farbtastic');
		wp_enqueue_script('farbtastic');
	}
}