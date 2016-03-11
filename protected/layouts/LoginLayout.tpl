<!DOCTYPE html>
<html lang="en">
	<com:THead Title="<%$ title %>" >
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="description" content="Neon Admin Panel" />
	<meta name="author" content="" />
	
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/css/font-icons/entypo/css/entypo.css'%>">
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/css/bootstrap.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/css/neon-core.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/css/neon-theme.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/css/neon-forms.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/css/custom.css'%>">
	
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery-1.11.0.min.js'%>" type="text/javascript"></script>
	<script>$.noConflict();</script>

	<!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->


</com:THead>
<body class="page-body login-page login-form-fall" data-url="http://neon.dev">


<!-- This is needed when you send requests via Ajax -->
<script type="text/javascript">
var baseurl = '';
</script>

<div class="login-container">
	
	<div class="login-header login-caret">
		
		<div class="login-content">
			<a href="index.html" class="logo">
				<img src="<%=$this->Page->Theme->BaseUrl.'/assets/images/kaos-01.png'%>" width="350" alt="" />
			</a>
			
			<p class="description"></p>
			
			<!-- progress bar indicator -->
			<div class="login-progressbar-indicator">
				<h3>43%</h3>
				<span>logging in...</span>
			</div>
		</div>
		
	</div>
	
	<div class="login-progressbar">
		<div></div>
	</div>
	
	<div class="login-form">
		
		<div class="login-content">
			
			<div class="form-login-error">
				<h3>Invalid login</h3>
				<p>Incorrect Username or Password.</p>
			</div>
			
			<form method="post" role="form" id="form_login">
				<com:TForm >
					
					<com:TContentPlaceHolder ID="Main" />
				
				</com:TForm>
			</form>
			
			
			<div class="login-bottom-links">
				Copyright of SOSA 2015
				<br>
				<br>
				<%= PRADO::poweredByPrado(1) %>
			</div>
			
		</div>
		
	</div>
	
</div>


	<!-- Bottom scripts (common) -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/gsap/main-gsap.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/bootstrap.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/joinable.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/resizeable.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/neon-api.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery.validate.min.js'%>" type="text/javascript"></script>


	<!-- JavaScripts initializations and stuff -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/neon-custom.js'%>" type="text/javascript"></script>


	<!-- Demo Settings -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/neon-demo.js'%>" type="text/javascript"></script>

</body>
</html>
