<?php 
	ini_set("display_errors", 0);
	
	$file = 'monitor.buf';
	
	$recstoshow = $_POST['recstoshow'];
	if(!$recstoshow)
		$recstoshow = 100;	

	$error = '';		
	$path = "./";
	$file2 = $path . $file;
	if(!file_exists($file2))
		$error = "File not found...";
	else 
	{
		$fp = fopen($file2, 'r');
		if(!$fp)
			$error = "File doesn't have read permissions......";
		fclose($fp);
	}
	$page_arr = array(25,50,75,100,150,200,250,300);
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta http-equiv="pragma" content="no-cache"/>
<meta name="viewport" content="user-scalable=0, width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
<link href="fire.png" rel="shortcut icon" type="image/x-icon">
<link href="fire.png" rel="apple-touch-icon" sizes="">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="new_style.css" type="text/css" />
<title>FireIncidents</title>
<script src="js/jquery.js"
    type="text/javascript"></script>
    <script type="text/javascript">
		var currenttime = '<?php print date("F d, Y H:i:s", time());?>';
		var montharray=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
		var serverdate=new Date(currenttime);

        $(document).ready(function() {
			//setInterval("displaytime()", 1000);
			//setTimeout(blinkText, 500);
        });       

		function padlength(what)
		{
			var output=(what.toString().length==1)? "0"+what : what
			return output
		}

		function displaytime()
		{
			serverdate.setSeconds(serverdate.getSeconds()+1)
			var datestring=montharray[serverdate.getMonth()]+" "+padlength(serverdate.getDate())+", "+serverdate.getFullYear()
			var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes())+":"+padlength(serverdate.getSeconds())
			document.getElementById("servertime").innerHTML=datestring+" "+timestring
		}
		      
        function getlogs()
        {
            fl = $("Select[name='lf']").val();
            rsh = $("Select[name='recstoshow']").val();
            var data = {"lf": fl, "recstoshow":rsh};
            $.post('icc_log.php', data, function(resp) 
            {
               if(resp.length)
               {
                   	//$("#myTable").find("tr:gt(0)").remove();
                    //$('#myTable tr:nth-child(1)').after(resp);
					$('.main_Pager').html(resp);
               }
            });
            tv = window.setTimeout(getlogs, 2000);
        }
	  
		function blinkText()
		{
			if($(".UNIT_0").hasClass('blink_on'))
				$(".UNIT_0").removeClass('blink_on');
			else
				$(".UNIT_0").addClass('blink_on');
		
			setTimeout(blinkText, 500);
		}
    </script>
</head>

<body style="margin-left:10px; background:#000; color:#fff" <?php if(!$error) echo 'onload="getlogs();"';?>>
<div class="main_Pager"></div>

<div width='96%' align='center'>

<!-- p align='left' style="font-size:16px;font-weight:bold"><span class='text'>&nbsp; Current Server Time is <span id="servertime"></span></span></p -->
<table width="96%" border="0" cellpadding="1" cellspacing="2" style="margin-left: 3px" align="center">
<tr>
    <th class='M' align='left'>
	<form id="form1" name="form1" method="post" action="">
	PAGING: <select name='recstoshow'  onChange="document.form1.submit();">
    <?php 
    	foreach($page_arr as $pg)
    	{
    		echo "<option value='$pg'";
    		if($pg == $recstoshow)
    			echo " selected='selected'";
    		echo ">$pg</option>";
    	}
    		
    ?>
    </select></th>
    <th  colspan='2' class='M'>
    </form>
    </th>
  </tr>
</table>
<?php //include_once('menu.php')?>
<?php if($error) echo "<p align='center'>Error: $error </p>";?>
</div>
</body>
</html>