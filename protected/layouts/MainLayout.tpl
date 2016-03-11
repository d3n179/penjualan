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
	<script>
		jQuery.noConflict();
		
		function loadContent() { 
		jQuery.blockUI({ 
			message:  '<img src="<%=$this->Page->Theme->BaseUrl.'/assets/images/ajax-loader(1).gif'%>" />',
			fadeIn:200,
			fadeOut:200,
			baseZ: 4000,
			css: { 
            border: 'none', 
            padding: 'none', 
            backgroundColor: '#000', 
            '-webkit-border-radius': '5px', 
            '-moz-border-radius': '5px', 
            opacity: .5, 
            color: '#fff' ,
						'font-size':'8px'
        } 
		}); 
	}
	function unloadContent() { //jQuery("#process").bind("click", function () {
		//jQuery("#wrap").unmasklayout();//});
		jQuery.unblockUI();
	}
	</script>

	<!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->


</com:THead>

<body class="page-body page-left-in" data-url="http://neon.dev">

<div class="page-container"><!-- add class "sidebar-collapsed" to close sidebar by default, "chat-visible" to make chat appear always -->
	<com:Application.Portlets.SidePortlet ID="SidePortlet"/>
	<div class="main-content">
		<hr />
		<com:TForm>
		<com:TContentPlaceHolder ID="Main" />
		</com:TForm>
		
		<!-- Footer -->
		<!--<footer class="main">
			&copy; 2014 <strong>Neon</strong> Admin Theme by <a href="http://laborator.co" target="_blank">Laborator</a>
		</footer>-->
	</div>
	
	
</div>

	<!-- Imported styles on this page -->
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/datatables/responsive/css/datatables.responsive.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/select2/select2-bootstrap.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/select2/select2.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/jvectormap/jquery-jvectormap-1.2.2.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/rickshaw/rickshaw.min.css'%>">
	<link rel="stylesheet" type="text/css" href="<%=$this->Page->Theme->BaseUrl.'/assets/js/daterangepicker/daterangepicker-bs3.css'%>">

	<!-- Bottom scripts (common) -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/gsap/main-gsap.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/bootstrap.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/joinable.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/resizeable.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/neon-api.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jvectormap/jquery-jvectormap-1.2.2.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery.dataTables.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/datatables/TableTools.min.js'%>" type="text/javascript"></script>

	<!-- Imported scripts on this page -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jvectormap/jquery-jvectormap-europe-merc-en.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jvectormap/jquery-jvectormap-world-mill-en.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery.sparkline.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/rickshaw/vendor/d3.v3.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/rickshaw/rickshaw.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery.validate.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/select2/select2.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/dataTables.bootstrap.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/datatables/jquery.dataTables.columnFilter.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/datatables/lodash.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/datatables/responsive/js/datatables.responsive.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/toastr.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/bootstrap-switch.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery.inputmask.bundle.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/jquery.numeric.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/bootstrap-datepicker.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/bootstrap-timepicker.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/bootstrap-colorpicker.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/daterangepicker/moment.min.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/daterangepicker/daterangepicker.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/autoNumeric.js'%>" type="text/javascript"></script>
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/accounting.js'%>" type="text/javascript"></script>
	
	<!-- JavaScripts initializations and stuff -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/neon-custom.js'%>" type="text/javascript"></script>


	<!-- Demo Settings -->
	<script src="<%=$this->Page->Theme->BaseUrl.'/assets/js/neon-demo.js'%>" type="text/javascript"></script>

</body>

</html>
