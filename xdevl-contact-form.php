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

$load_resources=false ;
	
class Constant
{
	const PLUGIN_NAMESPACE='xdevl_contactform' ;
	
	// Form setting
	const FORM_SETTINGS=self::PLUGIN_NAMESPACE ;
	const FORM_SETTINGS_SEND_TO=self::FORM_SETTINGS.'_sendto' ;
	const FORM_SETTINGS_SUBJECT_PREFIX=self::FORM_SETTINGS.'_subjectprefix' ;
	const FORM_SETTINGS_PUBLIC_KEY=self::FORM_SETTINGS.'_publickey' ;
	const FORM_SETTINGS_PRIVATE_KEY=self::FORM_SETTINGS.'_privatekey' ;
	const FORM_SETTINGS_CAPTCHA_THEME=self::FORM_SETTINGS.'_captchatheme' ;
	const FORM_SETTINGS_FOUNDATION_ALERT=self::FORM_SETTINGS.'_foundationalert' ;
	
	// Form fields
	const FIELD_NAME=self::PLUGIN_NAMESPACE.'_name' ;
	const FIELD_EMAIL=self::PLUGIN_NAMESPACE.'_email' ;
	const FIELD_SUBJECT=self::PLUGIN_NAMESPACE.'_subject' ;
	const FIELD_MESSAGE=self::PLUGIN_NAMESPACE.'_message' ;
	const FIELD_CAPTCHA=self::PLUGIN_NAMESPACE.'_captcha' ;
	
	// Javascript constants
	const AJAX_ACTION=self::PLUGIN_NAMESPACE ;
	const FORM_ID=self::PLUGIN_NAMESPACE ;
	const FORM_ALERT_ID=self::PLUGIN_NAMESPACE.'_alert' ;
}

function admin_init()
{
	register_setting(Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_SEND_TO) ;
	register_setting(Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_SUBJECT_PREFIX) ;
	register_setting(Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_PUBLIC_KEY) ;
	register_setting(Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_PRIVATE_KEY) ;
	register_setting(Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_CAPTCHA_THEME) ;
	register_setting(Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_FOUNDATION_ALERT) ;
	
	add_settings_section(Constant::FORM_SETTINGS,'Settings',null,Constant::FORM_SETTINGS) ;
	add_settings_field(Constant::FORM_SETTINGS_SEND_TO,'Send email to:', __NAMESPACE__.'\input_callback',Constant::FORM_SETTINGS,Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_SEND_TO) ;
	add_settings_field(Constant::FORM_SETTINGS_SUBJECT_PREFIX,'Prefix email subject with:', __NAMESPACE__.'\input_callback',Constant::FORM_SETTINGS,Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_SUBJECT_PREFIX) ;
	add_settings_field(Constant::FORM_SETTINGS_PUBLIC_KEY,'Recaptcha public key:', __NAMESPACE__.'\input_callback',Constant::FORM_SETTINGS,Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_PUBLIC_KEY) ;
	add_settings_field(Constant::FORM_SETTINGS_PRIVATE_KEY,'Recaptcha private key:', __NAMESPACE__.'\input_callback',Constant::FORM_SETTINGS,Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_PRIVATE_KEY) ;
	add_settings_field(Constant::FORM_SETTINGS_CAPTCHA_THEME,'Recaptcha theme:', __NAMESPACE__.'\captcha_theme_callback',Constant::FORM_SETTINGS,Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_CAPTCHA_THEME) ;
	add_settings_field(Constant::FORM_SETTINGS_FOUNDATION_ALERT,'Use foundation alert styles:', __NAMESPACE__.'\foundation_styles_callback',Constant::FORM_SETTINGS,Constant::FORM_SETTINGS,Constant::FORM_SETTINGS_FOUNDATION_ALERT) ;
}

function admin_menu()
{
	add_options_page('XdevL contact form setup','XdevL contact form','manage_options',Constant::FORM_SETTINGS, __NAMESPACE__.'\options_page') ;
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
<div>
	<h2>XdevL contact form setup</h2>
	<form method="post" action="options.php">
		<?php settings_fields(Constant::FORM_SETTINGS) ;
			do_settings_sections(Constant::FORM_SETTINGS) ;
			submit_button() ; ?>
	</form>
</div>

<?php
}

function shortcode()
{
	ob_start() ;
?>
<script type="text/javascript">
	var xdevl=xdevl || {} ;
	xdevl.contactform=xdevl.contactform || {} ;
	xdevl.contactform.AJAX_URL="<?php echo admin_url('admin-ajax.php'); ?>" ;
	xdevl.contactform.AJAX_ACTION="<?php echo Constant::AJAX_ACTION; ?>" ;
	xdevl.contactform.FORM_ID="<?php echo Constant::FORM_ID; ?>" ;
	xdevl.contactform.FORM_ALERT_ID="<?php echo Constant::FORM_ALERT_ID; ?>" ;
	xdevl.contactform.FIELD_CAPTCHA="<?php echo Constant::FIELD_CAPTCHA; ?>" ;
	xdevl.contactform.ALERT_SUCCESS_CLASSES="<?php if(get_option(Constant::FORM_SETTINGS_FOUNDATION_ALERT))echo 'alert-box success'; else echo 'xdevl_alert-box xdevl_success' ?>" ;
	xdevl.contactform.ALERT_ERROR_CLASSES="<?php if(get_option(Constant::FORM_SETTINGS_FOUNDATION_ALERT))echo 'alert-box alert'; else echo 'xdevl_alert-box xdevl_alert' ?>" ;
</script>

<form id="<?php echo Constant::FORM_ID; ?>">

	<div id="<?php echo Constant::FORM_ALERT_ID; ?>"></div>
	<input type="hidden" name="action" value="<?php echo Constant::AJAX_ACTION; ?>" />
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="<?php echo Constant::FIELD_NAME; ?>">Name:</label></th>
				<td>
					<input id="<?php echo Constant::FIELD_NAME; ?>" name="<?php echo Constant::FIELD_NAME; ?>" type="text" size="32" />
					<div id="<?php echo Constant::FIELD_NAME; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo Constant::FIELD_EMAIL; ?>">Email:</label></th>
				<td>
					<input id="<?php echo Constant::FIELD_EMAIL; ?>" name="<?php echo Constant::FIELD_EMAIL; ?>" type="text" />
					<div id="<?php echo Constant::FIELD_EMAIL; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo Constant::FIELD_SUBJECT; ?>">Subject:</label></th>
				<td>
					<input id="<?php echo Constant::FIELD_SUBJECT; ?>" name="<?php echo Constant::FIELD_SUBJECT; ?>" type="text" size="64" />
					<div id="<?php echo Constant::FIELD_SUBJECT; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo Constant::FIELD_MESSAGE; ?>">Message:</label></th>
				<td>
					<textarea id="<?php echo Constant::FIELD_MESSAGE; ?>" name="<?php echo Constant::FIELD_MESSAGE; ?>"></textarea>
					<div id="<?php echo Constant::FIELD_MESSAGE; ?>_error" class="error"></div>
				</td>
			</tr>
			<tr><th></th><td><div class="g-recaptcha" data-sitekey="<?php echo get_option(Constant::FORM_SETTINGS_PUBLIC_KEY); ?>"
					data-theme="<?php echo get_option(Constant::FORM_SETTINGS_CAPTCHA_THEME,'light'); ?>"></div></td></tr>
			<tr><th></th><td><input id="submitButton" type="submit" onclick="xdevl.contactform.submit(); return false;"  class="button small" value="Send message" /></td></tr>
		</tbody>
	</table>
</form>
<script src='https://www.google.com/recaptcha/api.js'></script>
<?php 
	return ob_get_clean() ;
}	

function wp_enqueue_scripts()
{
	wp_register_script(Constant::PLUGIN_NAMESPACE.'_script',plugins_url('script.js',__FILE__),array('jquery','jquery-form','underscore')) ;
	wp_enqueue_script(Constant::PLUGIN_NAMESPACE.'_script') ;
	
	wp_register_style(Constant::PLUGIN_NAMESPACE.'_style',plugins_url('style.css',__FILE__)) ;
	wp_enqueue_style(Constant::PLUGIN_NAMESPACE.'_style') ;
}

function wp_ajax()
{
	$result=new \stdClass ;
	$result->fieldErrors=array() ;
	$fields=array(Constant::FIELD_NAME,Constant::FIELD_EMAIL,Constant::FIELD_SUBJECT,Constant::FIELD_MESSAGE) ;
	$error=false ;
	foreach($fields as $field)
	{
		$error|=empty($_POST[$field]) ;
		$result->fieldErrors[$field.'_error']=empty($_POST[$field])?'this field can\'t be blank':'' ;
	}
	
	if(!$error && ($error=!filter_var($_POST[Constant::FIELD_EMAIL],FILTER_VALIDATE_EMAIL)))
		$result->fieldErrors[Constant::FIELD_EMAIL.'_error']='this is not a valid email address' ;
	
	if($error)
		$result->formError='Please fix the errors below' ;
	else
	{
		if(empty($_POST[Constant::FIELD_CAPTCHA]))
			$result->formError='Please prove you are not a bot by ticking the appropriate checkbox' ;
		else
		{
			require_once(plugin_dir_path (__FILE__).'recaptchalib.php');
			$recaptcha=new \ReCaptcha(get_option(Constant::FORM_SETTINGS_PRIVATE_KEY)) ;
			$response=$recaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST[Constant::FIELD_CAPTCHA]) ;
			if($response==null || !$response->success)
				$result->formError='Captcha verifaction failed' ;
			else
			{	
				$name=filter_var($_POST[Constant::FIELD_NAME],FILTER_SANITIZE_STRING) ;
				$email=filter_var($_POST[Constant::FIELD_EMAIL],FILTER_SANITIZE_EMAIL) ;
				$subject=filter_var($_POST[Constant::FIELD_SUBJECT],FILTER_SANITIZE_STRING) ;
				$message=filter_var($_POST[Constant::FIELD_MESSAGE],FILTER_SANITIZE_STRING) ;
				
				$header='From: '.$name.'<'.$email.'>\r\n'
						.'Reply-to: '.$email.'\r\n'
						.'Content-type: text/plain; charset=UTF-8\r\n'
						.'X-mailer: PHP/'.phpversion().'\r\n' ;
						
				if(mail(get_option(Constant::FORM_SETTINGS_SEND_TO),get_option(Constant::FORM_SETTINGS_SUBJECT_PREFIX).$subject,$message,$header))
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
	add_action('wp_ajax_'.Constant::AJAX_ACTION,__NAMESPACE__.'\wp_ajax') ;
	add_action('wp_ajax_nopriv_'.Constant::AJAX_ACTION,__NAMESPACE__.'\wp_ajax') ;
	add_action('admin_menu',__NAMESPACE__.'\admin_menu') ;
	add_action('admin_init',__NAMESPACE__.'\admin_init') ;
}

}
?>
