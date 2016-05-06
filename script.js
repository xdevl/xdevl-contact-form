/*
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

var xdevl=xdevl || {} ;
xdevl.contactform=xdevl.contactform || {} ;

jQuery(function($)
{
	xdevl.contactform.formToObject=function (id)
	{
		var obj={} ;
		$("#"+id).serializeArray().map(function(x){obj[x.name]=x.value}) ;
		obj[xdevl.contactform.FIELD_CAPTCHA]=$("#g-recaptcha-response").val() ;
		return obj ;
	} ;
	
	xdevl.contactform.submit=function ()
	{
		// clear the form alert message to notify the user something is going on
		$("#"+xdevl.contactform.FORM_ALERT_ID).text("") ;
		$("#"+xdevl.contactform.FORM_ALERT_ID).attr("class",null) ;
		
		$.post(xdevl.contactform.AJAX_URL,
			xdevl.contactform.formToObject(xdevl.contactform.FORM_ID)
			, function (data,textStatus,jqXHR) {
				try {
					var response=JSON.parse(data) ;
				} catch(error) {
					var response={formError: 'A internal server error occured, please try again in a few minutes', fieldErrors: []} ;
				}
				xdevl.contactform.updateForm(response) ;
			}
		).fail(function(xhr,textStatus,errorThrown) {
			xdevl.contactform.updateForm({formError: 'Looks like the server is unavailable, please try again in a few minutes', fieldErrors: []}) ;
		}) ;
	} ;
	
	xdevl.contactform.updateForm=function (result)
	{
		$.map(result.fieldErrors,function(value,key) {
			$("#"+key).text(value) ;
		}) ;
		
		if(result.formSuccess)
		{
			$("#"+xdevl.contactform.FORM_ALERT_ID).text(result.formSuccess) ;
			$("#"+xdevl.contactform.FORM_ALERT_ID).attr("class",xdevl.contactform.ALERT_SUCCESS_CLASSES) ;
			
			$("#"+xdevl.contactform.FORM_ID).trigger("reset") ;
			grecaptcha.reset() ;
		}
		else
		{
			$("#"+xdevl.contactform.FORM_ALERT_ID).text(result.formError) ;
			$("#"+xdevl.contactform.FORM_ALERT_ID).attr("class",xdevl.contactform.ALERT_ERROR_CLASSES) ;
		}
		
		// we scroll up to the form alert to let the user know what happened
		$('html,body').animate({
			scrollTop: $("#"+xdevl.contactform.FORM_ALERT_ID).offset().top
		},100) ;
	} ;
}) ;
