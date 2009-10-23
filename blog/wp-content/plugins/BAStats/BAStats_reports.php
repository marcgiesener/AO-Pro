<?php
/*
Version: 1.0&beta; build 8

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


$reports = array(
	// Each element of the $reports array is an array describing a single report.
	// Each element of the $reports array must have a unique key.
	'top_page_hits'=> array (
		//The 'title' key is the name that appears in the report dropdown
		'title'=>'Top Page Hits',
		//The 'report_type' key can be 'table' or 'graph', depending on what it displays.
		'report_type'=>'table',
		//The 'query' key contains an array of queries to execute in order
		//to retrieve the report data.
		//{stamp} is replaced with the date range selected in the UI.
		//{qrylimit} is replaced with the limit setting from the UI.
		'queries'=> array(
			"SELECT *, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id {stamp} GROUP BY page_id ORDER BY `Count` DESC {qrylimit}",
		),
		//The 'columns' key contains an array of 'Column Caption'=>'field_from_query' values.
		'columns'=>array('Page'=>'page_string', 'Count'=>'Count'),
		//The 'column_filter' key contains an array of filters indexed by column caption.
		//For the values in that column, instead of the raw data value, the filter is applied
		//to the value of $row, which contains the row data from the query for that result row.
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
		)
	),
	'top_page_hits_referer'=> array (
		'title'=>'Top Page Hits From Referrer',
		'report_type'=>'table',
		'filters'=>array('referer'),
		'queries'=> array(
			"SELECT *, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log}, {$wpdb->visitors} WHERE visit = visit_id AND page = page_id AND referer = {filt_crit} {stamp} GROUP BY page_id ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Page'=>'page_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
		)
	),
	'top_page_hits_host'=> array (
		'title'=>'Top Page Hits From %s',
		'report_type'=>'table',
		'filters'=>array('host'),
		'queries'=>array(
			"SELECT *, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log}, {$wpdb->visitors} WHERE page = page_id AND visit= visit_id AND visit_ip = {filt_crit} {stamp} GROUP BY page_id ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Page'=>'page_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
		)
	),
	'top_hosts'=> array (
		'title'=>'Top Hosts',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT *, count(visit_id) AS `Count` FROM {$wpdb->visitors}, {$wpdb->log} WHERE visit_id = visit {stamp} GROUP BY visit_ip ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Host IP'=>'visit_ip', 'Count'=>'Count'),
		'column_filter'=>array(
			'Host IP'=>'return "<a href=\"" . add_query_arg(array("filter"=>"host={$row[\'visit_ip\']}", "report"=>"host_profile"), $_SERVER[\'REQUEST_URI\']) . "\">" . long2ip($row[\'visit_ip\']) . "</a>";',
		)
	),
	'top_refer'=> array (
		'title'=>'Top Referring Pages',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT *, count(visit_id) AS `Count` FROM {$wpdb->refer}, {$wpdb->visitors} WHERE referer_id = referer AND referer_string <> '' {lasthere} GROUP BY referer ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Referring Page'=>'referer_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'Referring Page'=>'return "<a href=\"{$row[\'referer_string\']}\">" . substr(BASP::refer_replace($row[\'referer_string\']), 0, 100) . "</a>";',
			'Count'=>'return "<a href=\"" . add_query_arg(array("filter"=>"referer={$row[\'referer\']}", "report"=>"top_page_hits_referer"), $_SERVER[\'REQUEST_URI\']) . "\">{$row[\'Count\']}</a>";',
		)
	),
	'top_refer_host'=> array (
		'title'=>'Top Referring Pages for %s',
		'report_type'=>'table',
		'filters'=>array('host'),
		'queries'=>array(
			"SELECT *, count(visit_id) AS `Count` FROM {$wpdb->refer}, {$wpdb->visitors} WHERE referer_id = referer AND visit_ip = {filt_crit} {lasthere} GROUP BY referer ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Referring Page'=>'referer_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'Referring Page'=>'return "<a href=\"{$row[\'referer_string\']}\">" . substr(BASP::refer_replace($row[\'referer_string\']), 0, 100) . "</a>";',
		)
	),
	'top_os'=> array (
		'title'=>'Top Operating Systems',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT *, count(visit_id) AS `Count` FROM {$wpdb->os}, {$wpdb->visitors} WHERE os_id = osystem {lasthere} GROUP BY os_id ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Operating System'=>'os_string', 'Count'=>'Count'),
		'column_filter'=>array(
		)
	),
	'top_ua'=> array (
		'title'=>'Top User Agents',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT *, count(ua_id) AS `Count` FROM {$wpdb->ua}, {$wpdb->visitors} WHERE ua_id = useragent {lasthere} GROUP BY ua_id ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('User Agent'=>'ua_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'User Agent'=>'return BAStats::BrowserMap($row["ua_string"]) . "<div style=\"font-size:xx-small;text-indent:3em;\">{$row["ua_string"]}</div>";',
		)
	),
	'top_searches'=> array (
		'title'=>'Top Search Phrases',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT *, count(search_refer) AS `Count` FROM {$wpdb->searches}, {$wpdb->visitors}, {$wpdb->refer} WHERE search_refer = referer_id AND search_refer = referer AND referer_string <> '' {lasthere} GROUP BY search_refer  ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Search Phrase'=>'search_phrase', 'Search Engine'=>'search_engine', 'Count'=>'Count'),
		'column_filter'=>array(
			'Search Phrase'=>'return "<a href=\"{$row[\'referer_string\']}\">{$row[\'search_phrase\']}</a>";',
			'Search Engine'=>'return "<a href=\"" . add_query_arg(array("filter"=>"search={$row[\'search_refer\']}", "report"=>"search_profile"), $_SERVER[\'REQUEST_URI\']) . "\">{$row[\'search_engine\']}</a>";',
		)
	),

	'recent_page_hits'=> array (
		'title'=>'Recent Page Hits',
		'report_type'=>'table',
		'queries'=> array(
			"SELECT * FROM {$wpdb->pages}, {$wpdb->log}, {$wpdb->visitors} WHERE page = page_id AND visit = visit_id {stamp} ORDER BY stamp DESC {qrylimit}",
		),
		'columns'=>array('Page'=>'page_string', 'Date'=>'stamp', 'Host IP'=>'visit_ip'),
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
			'Host IP'=>'return "<a href=\"" . add_query_arg(array("filter"=>"host={$row[\'visit_ip\']}", "report"=>"host_profile"), $_SERVER[\'REQUEST_URI\']) . "\">" . long2ip($row[\'visit_ip\']) . "</a>";',
		)
	),
	'recent_page_hits_host'=> array (
		'title'=>'Recent Page Hits From %s',
		'report_type'=>'table',
		'filters'=>array('host'),
		'queries'=>array(
			"SELECT * FROM {$wpdb->pages}, {$wpdb->log}, {$wpdb->visitors} WHERE page = page_id AND visit= visit_id AND visit_ip = {filt_crit} {stamp} ORDER BY stamp DESC {qrylimit}",
		),
		'columns'=>array('Page'=>'page_string', 'Date'=>'stamp'),
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
		)
	),
	'recent_hosts'=> array (
		'title'=>'Recent Hosts',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT * FROM {$wpdb->visitors}, {$wpdb->log} WHERE visit_id = visit {stamp} ORDER BY stamp DESC {qrylimit}",
		),
		'columns'=>array('Host IP'=>'visit_ip', 'Date'=>'stamp'),
		'column_filter'=>array(
			'Host IP'=>'return "<a href=\"" . add_query_arg(array("filter"=>"host={$row[\'visit_ip\']}", "report"=>"host_profile"), $_SERVER[\'REQUEST_URI\']) . "\">" . long2ip($row[\'visit_ip\']) . "</a>";',
		)
	),
	/*
	'recent_refer'=> array (
		'title'=>'Recent Referring Pages',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT * FROM {$wpdb->refer}, {$wpdb->visitors} WHERE referer_id = referer AND referer_string <> '' {lasthere} ORDER BY lasthere DESC {qrylimit}",
		),
		'columns'=>array('Referring Page'=>'referer_string', 'Date'=>'lasthere'),
		'column_filter'=>array(
			'Referring Page'=>'return "<a href=\"{$row[\'referer_string\']}\">" . substr(BASP::refer_replace($row[\'referer_string\']), 0, 100) . "</a>";',
		)
	),
	*/
	/* New recet_refer report, thanks to Steve Smith @ www.orderedlist.com  */
	'recent_refer'=> array (
      'title'=>'Recent Referring Pages',
      'report_type'=>'table',
      'queries'=>array(
          "SELECT * FROM {$wpdb->refer}, {$wpdb->visitors}, {$wpdb->pages}  right join {$wpdb->log} on visit_id = visit WHERE referer_id = referer  AND referer_string <> '' {lasthere} and page_id = page group by visit  ORDER BY lasthere desc, stamp {qrylimit}",
      ),
      'columns'=>array('Referring Page'=>'referer_string', 'Page' =>  'page_string', 'Date'=>'lasthere'),
      'column_filter'=>array(
          'Referring Page'=>'return "<a href=\"{$row[\'referer_string\']}\">"  . substr(BASP::refer_replace($row[\'referer_string\']), 0, 100) .  "</a>";',
          'Page'=>'return ($row["page_label"] == \'\') ? "<a  href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a  href=\"{$row[\'page_string\']}\"  style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div  style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</ div>";',
      )
  ), 
	'recent_os'=> array (
		'title'=>'Recent Operating Systems',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT * FROM {$wpdb->os}, {$wpdb->visitors} WHERE os_id = osystem {lasthere} ORDER BY lasthere DESC {qrylimit}",
		),
		'columns'=>array('Operating System'=>'os_string', 'Date'=>'lasthere'),
		'column_filter'=>array(
		)
	),
	'recent_ua'=> array (
		'title'=>'Recent User Agents',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT * FROM {$wpdb->ua}, {$wpdb->visitors} WHERE ua_id = useragent {lasthere} ORDER BY lasthere DESC {qrylimit}",
		),
		'columns'=>array('User Agent'=>'ua_string', 'Date'=>'lasthere'),
		'column_filter'=>array(
			'User Agent'=>'return BAStats::BrowserMap($row["ua_string"]) . "<div style=\"font-size:xx-small;text-indent:3em;\">{$row["ua_string"]}</div>";',
		)
	),
	'recent_searches'=> array (
		'title'=>'Recent Search Phrases',
		'report_type'=>'table',
		'queries'=>array(
			"SELECT * FROM {$wpdb->searches}, {$wpdb->visitors}, {$wpdb->refer} WHERE referer_id = search_refer AND search_refer = referer AND referer_string <> '' {lasthere} ORDER BY lasthere DESC {qrylimit}",
		),
		'columns'=>array('Search Phrase'=>'search_phrase', 'Search Engine'=>'search_engine', 'Date'=>'lasthere'),
		'column_filter'=>array(
			'Search Phrase'=>'return "<a href=\"{$row[\'referer_string\']}\">{$row[\'search_phrase\']}</a>";',
			'Search Engine'=>'return "<a href=\"" . add_query_arg(array("filter"=>"search={$row[\'search_refer\']}", "report"=>"search_profile"), $_SERVER[\'REQUEST_URI\']) . "\">{$row[\'search_engine\']}</a>";',
		)
	),

	'host_profile'=> array (
		'title'=>'Host Profile for %s',
		'report_type'=>'table',
		'filters'=>array('host'),
		'queries'=> array(
			"SELECT * FROM {$wpdb->visitors}, {$wpdb->refer}, {$wpdb->ua} WHERE ua_id = useragent AND referer = referer_id AND visit_ip = {filt_crit} {lasthere} ORDER BY lasthere DESC {qrylimit}",
		),
		'columns'=>array('Host IP'=>'visit_ip', 'Date'=>'lasthere'),
		'column_filter'=>array(
			'Host IP'=>'return "<a href=\"" . add_query_arg(array("filter"=>"host={$row[\'visit_ip\']}", "report"=>"top_page_hits_host"), $_SERVER[\'REQUEST_URI\']) . "\">" . long2ip($row[\'visit_ip\']) . "</a>
				<div style=\"font-size:xx-small;text-indent:3em;\">Referring Page: {$row["referer_string"]}</div>
				<div style=\"font-size:xx-small;text-indent:3em;\">User Agent: {$row["ua_string"]}</div>
			";',
		)
	),
	'search_profile'=> array (
		'title'=>'Search Results from %s',
		'report_type'=>'table',
		'filters'=>array('search'),
		'queries'=>array(
			"SELECT DISTINCT search_engine FROM {$wpdb->searches} WHERE search_refer = {filt_crit}",
			"SELECT * FROM {$wpdb->searches}, {$wpdb->visitors}, {$wpdb->refer} WHERE search_refer = referer_id AND search_refer = referer AND search_engine = '{search_engine}' {lasthere} ORDER BY lasthere DESC {qrylimit}",
		),
		'columns'=>array('Search Phrase'=>'search_phrase', 'Search Engine'=>'search_engine', 'Date'=>'lasthere'),
		'column_filter'=>array(					
			'Search Phrase'=>'return "<a href=\"{$row[\'referer_string\']}\">{$row[\'search_phrase\']}</a>";',
		)
	),

	'graph_top_hits_segment'=> array (
		'title'=>'Graph Top 5 Page Hits - Segmented',
		'report_type'=>'graph',
		'queries'=> array(
			"SELECT *, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id {stamp} GROUP BY page_id ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Page'=>'page_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
		),
		'series'=>"SELECT *, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id {stamp} GROUP BY page_id ORDER BY `Count` DESC LIMIT 5",
		'series_index'=>'page_id',
		'series_label'=>'page_string',
		'label_filter'=>'return ($row->page_label == \'\') ? "<a href=\"{$row->page_string}\">{$row->page_string}</a>" : "<a href=\"{$row->page_string}\" style=\"font-weight:bold;\">{$row->page_label}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row->page_string}</div>";',
		'data'=> array(
			"SELECT {_grouping} AS Label, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id AND page_id = {series_index} {_stamp} GROUP BY {_grouping} ORDER BY {_ordering}",
		),
	),
	'graph_top_hits'=> array (
		'title'=>'Graph Top Page Hits',
		'report_type'=>'graph',
		'queries'=> array(
			"SELECT *, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id {stamp} GROUP BY page_id ORDER BY `Count` DESC {qrylimit}",
		),
		'columns'=>array('Page'=>'page_string', 'Count'=>'Count'),
		'column_filter'=>array(
			'Page'=>'return ($row["page_label"] == \'\') ? "<a href=\"{$row[\'page_string\']}\">{$row[\'page_string\']}</a>" : "<a href=\"{$row[\'page_string\']}\" style=\"font-weight:bold;\">{$row[\'page_label\']}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row[\'page_string\']}</div>";',
		),
		'series'=>"SELECT page_id, page_string, page_label, count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id {stamp} GROUP BY page_id ORDER BY `Count` DESC LIMIT {limit}",
		'series_index'=>'page_id',
		'series_label'=>'page_string',
		'label_filter'=>'return ($row->page_label == \'\') ? "<a href=\"{$row->page_string}\">{$row->page_string}</a>" : "<a href=\"{$row->page_string}\" style=\"font-weight:bold;\">{$row->page_label}</a><br><div style=\"font-size:xx-small;text-indent:3em;\">{$row->page_string}</div>";',
		'data'=> array(
			"SELECT count(page_id) AS `Count` FROM {$wpdb->pages}, {$wpdb->log} WHERE page = page_id AND page_id = {series_index} {_stamp} ORDER BY {_ordering}",
		),
	),
	/*
	// Spam Graph not ready for primetime.  :(
	'graph_spam'=> array (
		'title'=>'Graph Spam Frequency',
		'report_type'=>'graph',
		'queries'=> array(
			"SELECT count(comment_id) AS `Spam` FROM {$wpdb->comments} WHERE comment_approved='spam' {_stamp};",
		),
		'columns'=>array('Spam Comments'=>'Spam'),
		'column_filter'=>array(
		),
		'series'=>"SELECT 'Spam Comments' as Spam",
		'series_index'=>'Spam',
		'series_label'=>'Spam',
		'label_filter'=>'return "Spam Comments";',
		'data'=> array(
			"SELECT DATE_FORMAT(comment_date, '%Y-%m-%d %H:%i') AS Label, count(comment_id) AS `Count` FROM {$wpdb->comments} WHERE comment_approved='spam' AND comment_date >= {_starttext} AND comment_date <= {_endtext} GROUP BY DATE_FORMAT(comment_date, '%Y-%m-%d %H:%i') ORDER BY DATE_FORMAT(comment_date, {_datechunk})",
		),
	),
	*/
);
?>