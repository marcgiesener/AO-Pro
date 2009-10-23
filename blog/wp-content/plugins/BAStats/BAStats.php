<?php
/*
Plugin Name: BAStats
Plugin URI: http://asymptomatic.net/wp-hacks
Description: This plugin calculates statistics for a WordPress weblog.
Author: Owen Winkler
Version: 1.0&beta; build 8
Author URI: http://asymptomatic.net
*/

/*
BAStats - calculates statistics for a WordPress weblog.
Copyright (c) 2004 Owen Winkler

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

include_once('BAStats_logger.php');

class BASP
{
	var $settings = array();
	var $table_version = 6;
	var $added_tables = false;
	var $colors = array('#FF0000', '#FF1E00', '#FF3C00', '#FF5900', '#FF7700', '#FF9500', '#FFB300', '#FFD000', '#FFEE00', '#F2FF00', '#D5FF00', '#B7FF00', '#99FF00', '#7BFF00', '#5EFF00', '#40FF00', '#22FF00', '#04FF00', '#00FF1A', '#00FF37', '#00FF55', '#00FF73', '#00FF91', '#00FFAE', '#00FFCC', '#00FFEA', '#00F7FF', '#00D9FF', '#00BBFF', '#009DFF', '#0080FF', '#0062FF', '#0044FF', '#0026FF', '#0009FF', '#1500FF', '#3300FF', '#5100FF', '#6F00FF', '#8C00FF', '#AA00FF', '#C800FF', '#E600FF', '#FF00FB', '#FF00DD', '#FF00BF', '#FF00A2', '#FF0084', '#FF0066', '#FF0048', '#FF002B', '#FF000D',);


	function BASP()
	{
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_footer', array(&$this, 'admin_footer'));
		add_action('wp_head', array(&$this, 'wp_head'));
		add_action('template_redirect', array(&$this, 'template_redirect'));

		$this->settings = get_settings('bastats');
		$this->wpdb_tables();
		
		if($this->settings['table_version'] != $this->table_version)
		{
			$this->make_tables();
			$this->added_tables = true;
		}
	}
	
	function admin_footer()
	{
		update_option('bastats', $this->settings);
	}
	
	function admin_menu()
	{
		$pfile = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
		add_submenu_page('index.php', 'BA Stats', 'Stats', 8, $pfile, array(&$this, 'plugin_content'));
		add_options_page('BA Stats', 'Stats', 8, $pfile, array(&$this, 'plugin_options'));
	}
	
	function plugin_options()
	{
		include('BAStats_options.php');
	}

	function plugin_content()
	{
		global $wpdb;
	
		if($this->added_tables)
		{
			echo '<div class="updated"><p>Modified BA Stats Tables.</p></div>';
		}
		
		$reports = $this->get_reports();
		$report_tags = array_keys($reports);

		$rangeset = isset($_GET['rangeset']) ? $_GET['rangeset'] : 'ten_minutes';
		list($start, $end) = $this->get_range($rangeset);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : '10';
		$report = isset($_GET['report']) ? $_GET['report'] : $report_tags[0];
?>
<div class="wrap">
<h2>Site Statistics</h2>
<?php
  $totalPages = $wpdb->get_var("SELECT count(page_id) FROM {$wpdb->pages}");
  $totalSessions = $wpdb->get_var("SELECT count(visit_id) FROM {$wpdb->visitors}");
  $totalHits = $wpdb->get_var("SELECT count(visit) FROM {$wpdb->log}");


	echo "<p><strong>Unique Pages Served:</strong> {$totalPages}";
	echo "<strong style=\"margin-left:50px;\">Total Sessions:</strong> {$totalSessions}";
	echo "<strong style=\"margin-left:50px;\">Total Page Hits:</strong> {$totalHits}</p>";


	echo '<form method="get">
		<input type="hidden" name="page" value="' . $_GET['page'] .'"/>';  

	if(isset($_GET['filter']) > 0)
	{
		list($filt_type,$filt_crit) = explode('=', $_GET['filter'], 2);
		$filter = stripslashes($_GET['filter']);
		switch($filt_type)
		{
		case 'host':
			$filt_crit_output = BAStats::inttoip($filt_crit);
			$filt_label = "Host is " . $filt_crit_output;
			break;
		case 'search':			
			$filt_crit_output = $wpdb->get_var("SELECT search_engine FROM {$wpdb->searches} WHERE search_refer = {$filt_crit};");
			$filt_label = "Search Engine is " . $filt_crit_output;
			break;
		case 'referer':
			$filt_crit_output = $wpdb->get_var("SELECT referer_string FROM {$wpdb->refer} WHERE referer_id= {$filt_crit};");
			$filt_label = "Referer is " . $filt_crit_output;
			break;
		}
		echo '<fieldset id="stat_filter" style="clear:both;">
			<legend>Filter:</legend>';
		echo "<label style=\"display:block;\"><input type=\"checkbox\" name=\"filter\" value=\"{$filter}\" checked=\"checked\"/> {$filt_label}</label>\n";
		if(!is_array($reports[$report]['filters']) || !in_array($filt_type, $reports[$report]['filters']))
		{
			echo "<div style=\"text-indent:3em;\"><em>Filter does not apply to this report</em></div>";
		}
		echo '</fieldset>';
	}

	foreach($reports as $kreport => $vreport)
	{
		if($filt_type != '')
		{
			if(isset($vreport['filters']) && in_array($filt_type, $vreport['filters']))
			{
				$methods[$kreport] = array(sprintf($vreport['title'], $filt_crit_output), 10, 'background-color:#CCCCCC;');
			}
			elseif(!isset($vreport['filters']))
			{
				$methods[$kreport] = $vreport['title'];
			}
		}
		else
		{
			if(!isset($vreport['filters']))
			{
				$methods[$kreport] = $vreport['title'];
			}
		}
	}
	if(!in_array($report, array_keys($methods)))
	{
		$report = $report_tags[0];
	}
  echo '<fieldset id="stat_method" style="float: left; margin-right: 1em;"><legend>Report:</legend>' 
  	. $this->build_select('report', $methods, $report) 
  	. '</fieldset>';

  $limits = array(
  	'10' => '10',
  	'20' => '20',
  	'50' => '50',
  	'-1' => 'Everything',
  );
  echo '<fieldset id="stat_limit" style="float: left; margin-right: 1em;"><legend>Count:</legend>'
  	. $this->build_select('limit', $limits, $limit)
  	. '</fieldset>';

  $dateranges = array(
  	'ten_minutes' => '10 Minutes',
  	'last_hour' => 'Last Hour',
  	'last_6hours' => 'Last 6 Hours',
  	'twenty_four' => 'Last 24 Hours',
  	'today' => 'Since Midnight Today',
  	'yesterday' => 'Yesterday',
  	'last_week' => 'Last 7 Days',
  );
  if($timestamps = $wpdb->get_col("select distinct date_format(stamp, '%Y-%m') from {$wpdb->log};"))
  {
		foreach($timestamps as $item)
		{
			$dateranges[$item] = date('F Y', strtotime($item . '-01'));
		}
	}
	$dateranges[''] = 'Everything';
  echo '<fieldset id="stat_daterange" style="float: left; margin-right: 1em;"><legend>Date Range:</legend>'
  	. $this->build_select('rangeset', $dateranges, $rangeset)
  	. '</fieldset>';

  echo '<p class="submit" style="clear:both;"><input type="submit" value="Show" /></p>
  	</form>
  	</div>';
  
  switch($reports[$report]['report_type'])
  {
  case 'graph':
	  echo '<div class="wrap"><h2>Result Graph</h2><div style="text-align:center;"><img src="' . get_bloginfo('siteurl') . '/wp-content/plugins/BAStats/BAStats_graph.php?graph=' . $report . '&amp;rangeset=' . $rangeset . '&amp;limit=' . $limit . '" /></div>';
	  
		$query = $reports[$report]['series'];
		$qry = preg_replace('/{([^}]+)}/e', '\$\1', $query);
		$data = $wpdb->get_results($qry);
		
		$label_filter = create_function('$row', $reports[$report]['label_filter']);

		echo "<ul>";
		$count = 0;
		foreach($data as $row)
		{
			echo "<li style=\"list-style:none;\"><span style=\"color:" . $this->get_color($count, count($data)) . ";font-size:x-large;\">&bull;</span> {$count}: " . $label_filter($row) . "</li>";
			//echo "<li>" . print_r($row, 1)."<br/>{$reports[$report]['label_filter']}</li>";
			$count ++;
		}
	  echo "</ul>";
	  echo '</div>';
	  break;
	}
	
	$stamp = '';
	$lasthere = '';
	if($start != false)
	{
		$stamp .= " AND stamp >= '" . date('Y-m-d H:i:s', $start) . "'";
		$lasthere .= " AND lasthere >= '" . date('Y-m-d H:i:s', $start) . "'";
	}
	if($end != false)
	{
		$stamp .= " AND stamp <= '" . date('Y-m-d H:i:s', $end) . "'";
		$lasthere .= " AND lasthere <= '" . date('Y-m-d H:i:s', $end) . "'";
	}
	$qrylimit = '';
	if($limit != -1)
	{
		$qrylimit = " LIMIT {$limit}";
	}

	foreach($reports[$report]['queries'] as $query)
	{
		if(($query != $reports[$report]['queries'][0]) && is_array($data))
		{
			extract($data[0]);
		}
		$qry = preg_replace('/{([^}]+)}/e', '\$\1', $query);
		//$qry = sprintf($reports[$report]['query'], $where);
	
		$data = $wpdb->get_results($qry, ARRAY_A);
	}

	echo '<div class="wrap"><h2>Query Results</h2>';
  
  echo '<table id="stats_output" width="100%" cellpadding="3" cellspacing="3">
  	<thead>
  		<tr>
  ';
  foreach($reports[$report]['columns'] as $caption => $field)
  {
  	echo "<th scope=\"col\">{$caption}</th>\n";
  	if(isset($reports[$report]['column_filter'][$caption]))
  	{
  		//echo "<strong>Filter Function:</strong>{$reports[$report]['column_filter'][$caption]}";
	  	$column_filter[$caption] = create_function('$row', $reports[$report]['column_filter'][$caption]);
  	}
  }
  echo '</tr>
  	</thead>
  	<tbody>
  ';
  
  
	if(is_array($data) && count($data) > 0)
	{
	  foreach($data as $item)
	  {
	  	$alternate = ($alternate == '')? ' class="alternate"' : '';
	  	echo "<tr{$alternate}>\n";
		  foreach($reports[$report]['columns'] as $caption => $field)
		  {
		  	$output = $item[$field];
		  	if(isset($column_filter[$caption]))
		  	{
		  		$output = $column_filter[$caption]($item);
		  	}
		  	echo "<td>{$output}</td>\n";
		  }
		  echo "</tr>\n";
	  }
	}
	else
	{
		echo "<tr class=\"alternate\"><td colspan=\"3\">No results</td></tr>";
	}
  echo '</tbody>
  	</table>';
  echo "<div style=\"background-color:#EEE;margin:15px;font-size:xx-small;\">
  You have downloaded BETA software.  Provide this information to the developer only upon request:<br />
	{$qry}</div><!--div><pre>". /*print_r(array_keys($data[0]), 1) .*/ "</pre></div-->";
?>
</div>
<?php		
	}
	
	function wpdb_tables() {
		global $wpdb, $table_prefix;
		
		$wpdb->visitors = "{$table_prefix}bas_visitors";
		$wpdb->refer = "{$table_prefix}bas_refer";
		$wpdb->os = "{$table_prefix}bas_os";
		$wpdb->ua = "{$table_prefix}bas_ua";
		$wpdb->log = "{$table_prefix}bas_log";
		$wpdb->pages = "{$table_prefix}bas_pages";
		$wpdb->searches = "{$table_prefix}bas_searches";
	}

	function make_tables() {
		global $wpdb, $table_prefix;
		if(!include_once(ABSPATH . 'wp-admin/upgrade-functions.php')) {
			die(_e('There is was error adding the required tables to the database.  Please refer to the documentation regarding this issue.', 'BAStats'));
		}
		$qry = "CREATE TABLE {$table_prefix}bas_visitors (
			visit_id BIGINT(11) NOT NULL AUTO_INCREMENT, 
			visit_ip BIGINT(11) UNSIGNED, 
			referer INT(4), 
			osystem INT(4), 
			useragent INT(4), 
			lasthere DATETIME,
			PRIMARY KEY  (visit_id),
			KEY refer (referer),
			KEY os (osystem),
			KEY ua (useragent)
			);
			CREATE TABLE {$table_prefix}bas_refer (
			referer_id INT(4) NOT NULL AUTO_INCREMENT,
			referer_string tinytext,
			PRIMARY KEY  (referer_id)
			);
			CREATE TABLE {$table_prefix}bas_os (
			os_id INT(4) NOT NULL AUTO_INCREMENT,
			os_string varchar(255),
			PRIMARY KEY  (os_id)
			);
			CREATE TABLE {$table_prefix}bas_ua (
			ua_id INT(4) NOT NULL AUTO_INCREMENT,
			ua_string varchar(255),
			PRIMARY KEY  (ua_id)
			);
			CREATE TABLE {$table_prefix}bas_log (
			visit BIGINT(11) NOT NULL, 
			stamp DATETIME,
			outbound TINYINT,
			page BIGINT(11),
			KEY visit (visit),
			KEY page (page),
			KEY stamp (stamp)
			);
			CREATE TABLE {$table_prefix}bas_pages (
			page_id BIGINT(11) NOT NULL AUTO_INCREMENT,
			page_string varchar(255),
			permalink varchar(255),
			page_label varchar(255),
			PRIMARY KEY  (page_id)
			);
			CREATE TABLE {$table_prefix}bas_searches (
			search_refer BIGINT(11),
			search_phrase varchar(255),
			search_engine varchar(255),
			KEY search_refer (search_refer)
			);
			";
		dbDelta($qry);

		$this->settings['table_version'] = $this->table_version;
		update_option('bastats', $this->settings);
	}
	
	function get_range($rangeset)
	{
		if(preg_match('/([0-9]{4})-([0-9]{2})(-([0-9]{2}))?(->([0-9]{4})-([0-9]{2})(-([0-9]{2}))?)?/', $rangeset, $dateset))
		{
			if($dateset[5] == '')
			{
				if($dateset[4] == '')
				{
			    $start = mktime(0,   0,  0, $dateset[2], 1, $dateset[1]);
			    $end   = mktime(23, 59, 59, $dateset[2], date('t', $start), $dateset[1]);
				}
				else
				{
			    $start = mktime(0,   0,  0, $dateset[2], $dateset[4], $dateset[1]);
			    $end   = mktime(23, 59, 59, $dateset[2], $dateset[4], $dateset[1]);
				}
			}
			else
			{
				if($dateset[4] == '')
				{
			    $start = mktime(0,   0,  0, $dateset[2], 1, $dateset[1]);
				}
				else
				{
			    $start = mktime(0,   0,  0, $dateset[2], $dateset[4], $dateset[1]);
				}
				if($dateset[9] == '')
				{
			    $end   = mktime(23, 59, 59, $dateset[7], date('t', mktime(23, 59, 59, $dateset[7], 1, $dateset[6])), $dateset[6]);
				}
				else
				{
			    $end   = mktime(23, 59, 59, $dateset[7], $dateset[9], $dateset[6]);
				}
			}
		}
		else if(preg_match('/(today|last_week|yesterday|last_hour|last_6hours|ten_minutes|twenty_four)/', $rangeset, $dateset))
		{
			$dateinfo = getdate();
			extract($dateinfo);
			switch($dateset[1])
			{
			case 'today':
				$start = mktime(0, 0, 0, $mon, $mday, $year);
				$end = mktime(23, 59, 59, $mon, $mday, $year);
				break;
			case 'last_week':
				$start = mktime(0, 0, 0, $mon, $mday - 7, $year);
				$end = mktime(23, 59, 59, $mon, $mday, $year);
				break;
			case 'yesterday':
				$start = mktime(0, 0, 0, $mon, $mday - 1, $year);
				$end = mktime(23, 59, 59, $mon, $mday - 1, $year);
				break;
			case 'last_hour':
				$start = mktime($hours - 1, $minutes, $seconds, $mon, $mday, $year);
				$end = mktime($hours, $minutes, $seconds, $mon, $mday, $year);
				break;
			case 'last_6hours':
				$start = mktime($hours - 6, $minutes, $seconds, $mon, $mday, $year);
				$end = mktime($hours, $minutes, $seconds, $mon, $mday, $year);
				break;
			case 'ten_minutes':
				$start = mktime($hours, $minutes - 10, $seconds, $mon, $mday, $year);
				$end = mktime($hours, $minutes, $seconds, $mon, $mday, $year);
				break;		
			case 'twenty_four':
				$start = mktime($hours - 24, $minutes, $seconds, $mon, $mday, $year);
				$end = mktime($hours, $minutes, $seconds, $mon, $mday, $year);
				break;
			}
		}
		else
		{
			$start = false;
			$end   = false;
		}
		
		return array($start, $end);
	}

	function build_select($name, $valuedisplay, $check)
	{
		$ret = "<select id=\"{$name}\" name=\"{$name}\">\n";
		$options = array();
	  foreach($valuedisplay as $value => $display)
	  {
	  	$selected = ($value == $check) ? ' selected="selected"' : '';
	  	if(is_array($display))
	  	{
	  		$style = ($display[2] != '') ? " style=\"$display[2]\" " : '';
	  		$options[$display[1]][] = "<option value=\"{$value}\"{$style}{$selected}>{$display[0]}</option>";
	  	}
	  	else
	  	{
	  		$options[100][] = "<option value=\"{$value}\"{$selected}>{$display}</option>";
	  	}
	  }
	  ksort($options, SORT_NUMERIC);
	  foreach($options as $option)
	  {
	  	$ret .= implode("\n", $option);
	  }
	  $ret .= "</select>\n";
	  return $ret;
	}
	
	function refer_replace($url)
	{
		$engs = BAStats::search_engines();
		foreach($engs as $keng => $eng)
		{
			$repl[$keng] = "<strong class=\"BAStats_seng\">{$eng[0]}:</strong> \\" . $eng[1];
		}
		$url = preg_replace(array_keys($repl), array_values($repl), $url);
		$url = preg_replace('/^http:\/\//', '', $url);
		$url = urldecode($url);
		if($url == '') $url = '[No Referrer]';
		return $url;
	}	
	
	function wp_head()
	{
		$title = trim(wp_title(' ', false));
		BAStats::label($title);
		return true;
	}
	
	function get_reports()
	{
		global $wpdb;
		include_once('BAStats_reports.php');
		return $reports;
	}
	
	function get_color($index, $total)
	{
		$out = count($this->colors) / $total * $index;
		return $this->colors[$out];
	}
	
	function template_redirect()
	{
		$bas_options = get_settings('bas_options');
		if(!is_array($bas_options)) $bas_options = array();
		/* Actually DO statistics logging */
		$do_logging = true;
		if(!in_array('log_admins', $bas_options))
		{
			global $user_level;
			get_currentuserinfo();
			if (isset($user_level) && ($user_level >= 8)) $do_logging = false;
		}
		if(!in_array('log_console', $bas_options))
		{
			if(strstr($_SERVER['REQUEST_URI'], '/wp-admin')) $do_logging = false;
		}
		if(!in_array('log_content', $bas_options))
		{
			if(strstr($_SERVER['REQUEST_URI'], '/wp-content')) $do_logging = false;
		}
		if(!in_array('log_self', $bas_options))
		{
			$serveraddr = $_SERVER['LOCAL_ADDR'];
			if($serveraddr == '') $serveraddr = $_SERVER['SERVER_ADDR'];
			if($_SERVER['REMOTE_ADDR'] == $serveraddr) $do_logging = false;
		}
		if($do_logging)
		{
			BAStats::log($title);
		}
		//echo "<!-- BAStats Logged -->";
	}
}

$basp = new BASP();

?>
