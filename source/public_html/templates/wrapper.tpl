<html>
        <head>
		<title>House Finder</title>
		<!-- Pretty font -->
		<link href="http://fonts.googleapis.com/css?family=Droid+Sans" rel="stylesheet">
		<link href="css/libraries/ui-lightness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
		<link href="css/libraries/fancybox.css" rel="stylesheet">
		<link href="css/main.css" rel="stylesheet">
        </head> 
        <body>
		{include file=$_content}
		<script type="text/javascript">
                {literal}
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-36753326-1']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
                {/literal}
		</script>
		<!-- Google Maps -->
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBIeGZ0goJKoy7ixt2ARwaGt6VBJyUXY1I&sensor=false"></script>
		<!-- Libraries -->
		<script src="js/libraries/jquery-1.8.3.min.js"></script>
		<script src="js/libraries/jquery-ui-1.9.2.custom.min.js"></script>
		<script src="js/libraries/fancybox.min.js"></script>
		<script src="js/libraries/tablesorter.min.js"></script>
		<!-- Our stuff -->
		<script src="js/cookies.js"></script>
		<script src="js/map.js"></script>
		<script src="js/main.js"></script>
		<script src="js/ajax.js"></script>
		<script src="js/alerts.js"></script>
		<script src="js/markers.js"></script>
		<script src="js/listings.js"></script>
		</script>
        </body>
</html>