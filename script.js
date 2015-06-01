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
	} ;
}) ;
