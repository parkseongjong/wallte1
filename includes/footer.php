</div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    

    <!-- Bootstrap Core JavaScript -->
    
        <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/metisMenu/metisMenu.min.js"></script>

   
   

    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>
    <script src="js/jquery.validate.min.js"></script>


<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-127069169-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-127069169-1');
  
    
  // for language
  function changeLanguage(getThis){
	var getThisVal = $(getThis).val();
	
	  	$.ajax({
			url : 'changelang.php',
			type : 'POST',
			data : {lang:getThisVal},
			dataType : 'json',
			success : function(resp){
				window.location.reload();
			},
			error : function(resp){
				window.location.reload();
			}
		}) 
  }
  // for language
</script>


</body>

</html>
