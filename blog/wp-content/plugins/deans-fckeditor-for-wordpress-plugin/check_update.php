<?php
/*
Copyright (c) 2007 Dean Lee

This file is part of Dean's fckeditor for wordpress.
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
function check_for_update($url, $plugin_name, $current_version)
{
	$url_to_this_path = trailingslashit(get_option('siteurl')) . '/wp-content/plugins/'. basename(dirname(__FILE__)) .'/';
	?>
<style type="text/css">
.alert {
	background: #fff6bf url(<?php echo $url_to_this_path?>new.png) no-repeat center;
	background-position: 15px 50%; 
	text-align: left;
	padding: 5px 20px 5px 45px;
	border-top: 2px solid #ffd324;
	border-bottom: 2px solid #ffd324;
	margin-bottom:20px;
	}
	</style>
		<div class="alert" id="dean_update_notifier">&nbsp;</div>
<script type="text/javascript">
document.getElementById("dean_update_notifier").style.display='none';
function createRequestObject() {
	var ro;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
		ro = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		ro = new XMLHttpRequest();
	}
	return ro;
}

function sndReqGenResp() {
	var http = createRequestObject();

	http.open('get', '<?php echo $url ?>?p=<?php echo $plugin_name;?>&v=<?php echo $current_version;?>');
	http.onreadystatechange = function () {
		if(http.readyState == 4 && http.status== 200){
			var response = http.responseText;
			if (response != '')
			{
				document.getElementById("dean_update_notifier").innerHTML = response;
				document.getElementById("dean_update_notifier").style.display='block';
			}
		}
	};

	http.send(null);
}
sndReqGenResp();
</script>
<?php } 

?>