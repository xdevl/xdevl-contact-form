<?php
/**
 * Plugin Name: XdevL contact form
 * Plugin URI: http://www.xdevl.com/blog
 * Description: Ajax contact form with google recaptcha no-captcha
 * Version: 1.0
 * Date: 31 May 2015
 * Author: XdevL
 * Author URI: http://www.xdevl.com/blog
 * License: GPL2
 * 
 * @copyright Copyright (c) 2015, XdevL
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace xdevl\contactform
{

defined('ABSPATH') or die('No script kiddies please!') ;

define(__NAMESPACE__.'\PLUGIN_NAMESPACE','xdevl_contactform') ;

// Form setting
define(__NAMESPACE__.'\FORM_SETTINGS',PLUGIN_NAMESPACE) ;
define(__NAMESPACE__.'\FORM_SETTINGS_SEND_TO',PLUGIN_NAMESPACE.'_sendto') ;
define(__NAMESPACE__.'\FORM_SETTINGS_SUBJECT_PREFIX',PLUGIN_NAMESPACE.'_subjectprefix') ;
define(__NAMESPACE__.'\FORM_SETTINGS_PUBLIC_KEY',PLUGIN_NAMESPACE.'_publickey') ;
define(__NAMESPACE__.'\FORM_SETTINGS_PRIVATE_KEY',PLUGIN_NAMESPACE.'_privatekey') ;
define(__NAMESPACE__.'\FORM_SETTINGS_CAPTCHA_THEME',PLUGIN_NAMESPACE.'_captchatheme') ;
define(__NAMESPACE__.'\FORM_SETTINGS_FOUNDATION_ALERT',PLUGIN_NAMESPACE.'_foundationalert') ;

// Form fields
define(__NAMESPACE__.'\FIELD_NAME',PLUGIN_NAMESPACE.'_name') ;
define(__NAMESPACE__.'\FIELD_EMAIL',PLUGIN_NAMESPACE.'_email') ;
define(__NAMESPACE__.'\FIELD_SUBJECT',PLUGIN_NAMESPACE.'_subject') ;
define(__NAMESPACE__.'\FIELD_MESSAGE',PLUGIN_NAMESPACE.'_message') ;
define(__NAMESPACE__.'\FIELD_CAPTCHA',PLUGIN_NAMESPACE.'_captcha') ;

// Javascript constants
define(__NAMESPACE__.'\AJAX_ACTION',PLUGIN_NAMESPACE) ;
define(__NAMESPACE__.'\FORM_ID',PLUGIN_NAMESPACE) ;
define(__NAMESPACE__.'\FORM_ALERT_ID',PLUGIN_NAMESPACE.'_alert') ;

define(__NAMESPACE__.'\URL_RECAPTCHA_ADMIN',"https://www.google.com/recaptcha/admin") ;

function admin_init()
{
	register_setting(FORM_SETTINGS,FORM_SETTINGS_SEND_TO,__NAMESPACE__.'\validate_email') ;
	register_setting(FORM_SETTINGS,FORM_SETTINGS_SUBJECT_PREFIX) ;
	register_setting(FORM_SETTINGS,FORM_SETTINGS_PUBLIC_KEY,function($value){return validate_recaptcha_key($value,FORM_SETTINGS_PUBLIC_KEY,'public');}) ;
	register_setting(FORM_SETTINGS,FORM_SETTINGS_PRIVATE_KEY,function($value){return validate_recaptcha_key($value,FORM_SETTINGS_PRIVATE_KEY,'private');}) ;
	register_setting(FORM_SETTINGS,FORM_SETTINGS_CAPTCHA_THEME) ;
	register_setting(FORM_SETTINGS,FORM_SETTINGS_FOUNDATION_ALERT) ;
	
	add_settings_section(FORM_SETTINGS,null,null,FORM_SETTINGS) ;
	add_settings_field(FORM_SETTINGS_SEND_TO,'Send email to:', __NAMESPACE__.'\input_callback',FORM_SETTINGS,FORM_SETTINGS,FORM_SETTINGS_SEND_TO) ;
	add_settings_field(FORM_SETTINGS_SUBJECT_PREFIX,'Prefix email subject with:', __NAMESPACE__.'\input_callback',FORM_SETTINGS,FORM_SETTINGS,FORM_SETTINGS_SUBJECT_PREFIX) ;
	add_settings_field(FORM_SETTINGS_PUBLIC_KEY,'Recaptcha public key:', __NAMESPACE__.'\input_callback',FORM_SETTINGS,FORM_SETTINGS,FORM_SETTINGS_PUBLIC_KEY) ;
	add_settings_field(FORM_SETTINGS_PRIVATE_KEY,'Recaptcha private key:', __NAMESPACE__.'\input_callback',FORM_SETTINGS,FORM_SETTINGS,FORM_SETTINGS_PRIVATE_KEY) ;
	add_settings_field(FORM_SETTINGS_CAPTCHA_THEME,'Recaptcha theme:', __NAMESPACE__.'\captcha_theme_callback',FORM_SETTINGS,FORM_SETTINGS,FORM_SETTINGS_CAPTCHA_THEME) ;
	add_settings_field(FORM_SETTINGS_FOUNDATION_ALERT,'Use foundation alert styles:', __NAMESPACE__.'\foundation_styles_callback',FORM_SETTINGS,FORM_SETTINGS,FORM_SETTINGS_FOUNDATION_ALERT) ;
}

function admin_menu()
{
	add_options_page('XdevL contact form setup','XdevL contact form','manage_options',FORM_SETTINGS, __NAMESPACE__.'\options_page') ;
}

function validate_email($value)
{
	if(!filter_var($value,FILTER_VALIDATE_EMAIL))
	{
		add_settings_error(FORM_SETTINGS_SEND_TO,'email','Invalid email address: "'.htmlspecialchars($value).'"') ;
		return get_option(FORM_SETTINGS_SEND_TO) ;
	}
	else return filter_var($value,FILTER_SANITIZE_EMAIL) ;
}

function validate_recaptcha_key($value, $settings_key, $type)
{
	if(empty($value))
	{
		add_settings_error($settings_key,$type.'_key','Please enter your Google recaptcha '.$type.' key') ;
		return get_option($settings_key) ;
	}
	else return trim($value) ;
}


function input_callback($option)
{
	$value=get_option($option) ;
	echo "<input id=\"$option\" name=\"$option\" type=\"text\" size=\"64\" value=\"$value\" />" ;
}

function captcha_theme_callback($option)
{
	$value=get_option($option) ;
	echo "<fieldset><label><input id=\"$option\" name=\"$option\" type=\"radio\" value=\"light\" ".($value=='dark'?'':'checked').'/>Light</label><br />' ;
	echo "<label><input id=\"$option\" name=\"$option\" type=\"radio\" value=\"dark\" ".($value=='dark'?'checked':'').'/>Dark</label><br /></fieldset>' ;
}

function foundation_styles_callback($option)
{
	$value=get_option($option) ;
	echo "<input id=\"$option\" name=\"$option\" type=\"checkbox\" ".($value?'checked':'').' />' ;
}

function options_page()
{
?>
<div class="wrap">
	<h1>XdevL contact form setup</h1>
	<p>Register your website on the <a href="<?php echo URL_RECAPTCHA_ADMIN; ?>">Google recaptcha admin page</a> in order to get your pair of public/private keys.</p>
	<form method="post" action="options.php">
		<?php settings_fields(FORM_SETTINGS) ;
			do_settings_sections(FORM_SETTINGS) ;
			submit_button() ; ?>
	</form>
</div>

<?php
}

function shortcode()
{
	wp_enqueue_script(PLUGIN_NAMESPACE.'_script') ;
	wp_enqueue_script('recaptcha') ;
	ob_start() ;
?>
<script type="text/javascript">
	var xdevl=xdevl || {} ;
	xdevl.contactform=xdevl.contactform || {} ;
	xdevl.contactform.AJAX_URL="<?php echo admin_url('admin-ajax.php'); ?>" ;
	xdevl.contactform.AJAX_ACTION="<?php echo AJAX_ACTION; ?>" ;
	xdevl.contactform.FORM_ID="<?php echo FORM_ID; ?>" ;
	xdevl.contactform.FORM_ALERT_ID="<?php echo FORM_ALERT_ID; ?>" ;
	xdevl.contactform.FIELD_CAPTCHA="<?php echo FIELD_CAPTCHA; ?>" ;
	xdevl.contactform.ALERT_SUCCESS_CLASSES="<?php if(get_option(FORM_SETTINGS_FOUNDATION_ALERT))echo 'alert-box success'; else echo 'xdevl_alert-box xdevl_success' ?>" ;
	xdevl.contactform.ALERT_ERROR_CLASSES="<?php if(get_option(FORM_SETTINGS_FOUNDATION_ALERT))echo 'alert-box alert'; else echo 'xdevl_alert-box xdevl_alert' ?>" ;
</script>

<form id="<?php echo FORM_ID; ?>">

	<div id="<?php echo FORM_ALERT_ID; ?>"></div>
	<input type="hidden" name="action" value="<?php echo AJAX_ACTION; ?>" />
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="<?php echo FIELD_NAME; ?>">Name:</label></th>
				<td>
					<input id="<?php echo FIELD_NAME; ?>" name="<?php echo FIELD_NAME; ?>" type="text" size="32" />
					<div id="<?php echo FIELD_NAME; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo FIELD_EMAIL; ?>">Email:</label></th>
				<td>
					<input id="<?php echo FIELD_EMAIL; ?>" name="<?php echo FIELD_EMAIL; ?>" type="text" />
					<div id="<?php echo FIELD_EMAIL; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo FIELD_SUBJECT; ?>">Subject:</label></th>
				<td>
					<input id="<?php echo FIELD_SUBJECT; ?>" name="<?php echo FIELD_SUBJECT; ?>" type="text" size="64" />
					<div id="<?php echo FIELD_SUBJECT; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo FIELD_MESSAGE; ?>">Message:</label></th>
				<td>
					<textarea id="<?php echo FIELD_MESSAGE; ?>" name="<?php echo FIELD_MESSAGE; ?>"></textarea>
					<div id="<?php echo FIELD_MESSAGE; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr><th></th><td><div class="g-recaptcha" data-sitekey="<?php echo get_option(FORM_SETTINGS_PUBLIC_KEY); ?>"
					data-theme="<?php echo get_option(FORM_SETTINGS_CAPTCHA_THEME,'light'); ?>"></div></td></tr>
			<tr><th></th><td><input id="submitButton" type="submit" onclick="xdevl.contactform.submit(); return false;"  class="button small" value="Send message" /></td></tr>
		</tbody>
	</table>
</form>
<?php 
	return ob_get_clean() ;
}	

function wp_enqueue_scripts()
{
	wp_register_script(PLUGIN_NAMESPACE.'_script',plugins_url('script.js',__FILE__),array('jquery','jquery-form','underscore'),null,true) ;
	wp_register_script('recaptcha','https://www.google.com/recaptcha/api.js',array(),null,true) ;
	
	wp_register_style(PLUGIN_NAMESPACE.'_style',plugins_url('style.css',__FILE__)) ;
	wp_enqueue_style(PLUGIN_NAMESPACE.'_style') ;
}

function wp_ajax()
{
	$result=new \stdClass ;
	$result->fieldErrors=array() ;
	$fields=array(FIELD_NAME,FIELD_EMAIL,FIELD_SUBJECT,FIELD_MESSAGE) ;
	$error=false ;
	foreach($fields as $field)
	{
		$error|=empty($_POST[$field]) ;
		$result->fieldErrors[$field.'_error']=empty($_POST[$field])?'this field can\'t be blank':'' ;
	}
	
	if(!$error && ($error=!filter_var($_POST[FIELD_EMAIL],FILTER_VALIDATE_EMAIL)))
		$result->fieldErrors[FIELD_EMAIL.'_error']='this is not a valid email address' ;
	
	if($error)
		$result->formError='Please fix the errors below' ;
	else
	{
		if(empty($_POST[FIELD_CAPTCHA]))
			$result->formError='Please prove you are not a bot by ticking the appropriate checkbox' ;
		else
		{
			require_once(plugin_dir_path (__FILE__).'vendor/autoload.php');
			$recaptcha=new \ReCaptcha\ReCaptcha(get_option(FORM_SETTINGS_PRIVATE_KEY)) ;
			$response=$recaptcha->verify($_POST[FIELD_CAPTCHA],$_SERVER["REMOTE_ADDR"]) ;
			if($response==null || !$response->isSuccess())
				$result->formError='Captcha verifaction failed' ;
			else
			{	
				$name=filter_var($_POST[FIELD_NAME],FILTER_SANITIZE_STRING) ;
				$email=filter_var($_POST[FIELD_EMAIL],FILTER_SANITIZE_EMAIL) ;
				$subject=filter_var($_POST[FIELD_SUBJECT],FILTER_SANITIZE_STRING) ;
				$message=filter_var($_POST[FIELD_MESSAGE],FILTER_SANITIZE_STRING) ;
				
				$header="From: $name <do_not_reply@".$_SERVER['SERVER_NAME'].">\r\n"
						."Reply-to: $email\r\n"
						."Content-type: text/plain; charset=UTF-8\r\n"
						.'X-mailer: PHP/'.phpversion()."\r\n" ;
						
				if(mail(get_option(FORM_SETTINGS_SEND_TO),get_option(FORM_SETTINGS_SUBJECT_PREFIX).$subject,$message,$header))
					$result->formSuccess='Your message has been sent successfully, thank you' ;
				else $result->formError='An error occured while trying to send your message, please retry in a few minutes' ;
			}
		} 
	}
	
	echo json_encode($result) ;
	wp_die() ;
}

add_shortcode('xdevl_contact_form',__NAMESPACE__.'\shortcode') ;
add_action('wp_enqueue_scripts',__NAMESPACE__.'\wp_enqueue_scripts') ;

if(is_admin())
{
	add_action('wp_ajax_'.AJAX_ACTION,__NAMESPACE__.'\wp_ajax') ;
	add_action('wp_ajax_nopriv_'.AJAX_ACTION,__NAMESPACE__.'\wp_ajax') ;
	add_action('admin_menu',__NAMESPACE__.'\admin_menu') ;
	add_action('admin_init',__NAMESPACE__.'\admin_init') ;
}

}
?>
