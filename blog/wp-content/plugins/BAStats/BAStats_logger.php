<?php

/*
Version: 1.0&beta; build 8

BAStats - Calculates statistics for a WordPress weblog.
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

class BAStats
{

	function BAStats_logger()
	{
		global $wpdb;
		
		
	}
	
	function log($label = '')
	{
		global $wpdb, $table_prefix;
		
		$create_session = false;
		
		//Find user session
		if(isset($_COOKIE['basp']))
		{
			$sid = $_COOKIE['basp'];
			$sdata = $wpdb->get_row("SELECT * FROM {$wpdb->visitors}, {$wpdb->refer}, {$wpdb->ua}, {$wpdb->os} WHERE visit_id = {$sid} AND referer = referer_id AND osystem = os_id AND useragent = ua_id");
			if(!$sdata) $create_session = true;
		}
		elseif($sdata = $wpdb->get_row("SELECT * FROM {$wpdb->visitors}, {$wpdb->refer}, {$wpdb->ua}, {$wpdb->os} WHERE referer = referer_id AND osystem = os_id AND useragent = ua_id AND lasthere > DATE_SUB(NOW(), INTERVAL 20 MINUTE) AND visit_ip = " . ip2long($_SERVER['REMOTE_ADDR']) . " AND ua_string = '" . addslashes($_SERVER['HTTP_USER_AGENT']) . "' ORDER BY lasthere DESC LIMIT 1"))
		{
			$sid = $sdata->visit_id;
		}
		elseif(strpos($_SERVER['HTTP_REFERER'], get_settings('site_url')) && ($sdata = $wpdb->get_row("SELECT * FROM {$wpdb->visitors}, {$wpdb->refer}, {$wpdb->ua}, {$wpdb->os} WHERE referer = referer_id AND osystem = os_id AND useragent = ua_id AND visit_ip = " . ip2long($_SERVER['REMOTE_ADDR']) . " AND ua_string = '" . addslashes($_SERVER['HTTP_USER_AGENT']) . "' ORDER BY lasthere DESC LIMIT 1")))
		{
			$sid = $sdata->visit_id;
		}
		else
		{
			$create_session = true;
		}
		if($create_session)
		{
			//Create new session
			$refer_id = $wpdb->get_var("SELECT referer_id FROM {$wpdb->refer} WHERE referer_string = '" . addslashes($_SERVER['HTTP_REFERER']) . "';");
			if($refer_id == '')
			{
				$wpdb->query("INSERT INTO {$wpdb->refer} (referer_string) VALUES ('" . addslashes($_SERVER['HTTP_REFERER']) . "');");
				$refer_id =$wpdb->insert_id;
			}
			$os_id = $wpdb->get_var("SELECT os_id FROM {$wpdb->os} WHERE os_string = '" . addslashes(BAStats::OSMap($_SERVER['HTTP_USER_AGENT'])) . "';");
			if($os_id == '')
			{
				$wpdb->query("INSERT INTO {$wpdb->os} (os_string) VALUES ('" . addslashes(BAStats::OSMap($_SERVER['HTTP_USER_AGENT'])) . "');");
				$os_id = $wpdb->insert_id;
			}
			$ua_id = $wpdb->get_var("SELECT ua_id FROM {$wpdb->ua} WHERE ua_string = '" . addslashes($_SERVER['HTTP_USER_AGENT']) . "';");
			if($ua_id == '')
			{
				$wpdb->query("INSERT INTO {$wpdb->ua} (ua_string) VALUES ('" . addslashes($_SERVER['HTTP_USER_AGENT']) . "');");
				$ua_id = $wpdb->insert_id;
			}
			$qry = "INSERT INTO {$wpdb->visitors} (visit_ip, referer, osystem, useragent,	lasthere) VALUES (";
			$qry .= ip2long($_SERVER['REMOTE_ADDR']);
			$qry .= ", " . $refer_id;
			$qry .= ", " . $os_id;
			$qry .= ", " . $ua_id;
			$qry .= ", '" . date('Y-m-d H:i:s') . "'";
			$qry .= ");";
			$wpdb->query($qry);
			$sid = $wpdb->insert_id;
			$sdata = $wpdb->get_row("SELECT * FROM {$wpdb->visitors}, {$wpdb->refer}, {$wpdb->ua}, {$wpdb->os} WHERE visit_id = {$sid} AND referer = referer_id AND osystem = os_id AND useragent = ua_id");
		}
		setcookie('basp', $sid, time()+60*20, '/');

		$page_string = '';
		$permalink = '';
		BAStats::getPageString($page_string, $permalink);

		$page = $wpdb->get_row("SELECT * FROM {$wpdb->pages} WHERE page_string = '" . addslashes($page_string) . "';");
		if(!$page)
		{
			$wpdb->query("INSERT INTO {$wpdb->pages} (page_string, permalink, page_label) VALUES ('" . addslashes($page_string) . "', '" . addslashes($permalink) . "', '" . addslashes($label) . "');");
			$page = $wpdb->get_row("SELECT * FROM {$wpdb->pages} WHERE page_string = '" . addslashes($page_string) . "';");
		}
		
		$options = get_settings('bas_options');
		$settings = get_settings('bas_settings');
		if(in_array('log_spam', $options) && in_array('referer_spam', $settings))
		{
			$logok = true;
			$badlist = explode("\n", $settings['referer_spam']);
			foreach($badlist as $entry)
			{
				if(isset($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'], $entry) !== false)
				{
					$logok = false;
					break;
				}
			}
		}
		else
		{
			$logok = true;
		}

		if($logok)
		{
			BAStats::log_search($_SERVER['HTTP_REFERER'], $sdata->referer);

			if($page->page_id != '')
			{
				$wpdb->query("INSERT INTO {$wpdb->log} (visit, stamp, outbound, page) VALUES ({$sid}, '" . date('Y-m-d H:i:s') . "', 0, {$page->page_id});");
			}
		}
		else
		{
			if(in_array('die_spam', $options))
			{
				die('Sorry.  Your referral domain is in a spam wordlist, and may not view this site.');
			}
		}

		/*echo "<pre>";
		print_r($sid);
		print_r($sdata);
		print_r($page);
		echo "<br/>{$wpdb->last_query}";
		echo "</pre>";*/
	}
	
	function getPageString(&$page_string, &$permalink)
	{
		if(isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			$page_string = $_SERVER['HTTP_X_REWRITE_URL'];
			$permalink = $_SERVER['REQUEST_URI'];
		}
		elseif(isset($_SERVER['ORIG_PATH_INFO']))
		{
			$page_string = $_SERVER['ORIG_PATH_INFO'];
			$permalink = $_SERVER['ORIG_PATH_INFO'];
		}
		else
		{
			$page_string = $_SERVER['REQUEST_URI'];
			$permalink = $_SERVER['REQUEST_URI'];
		}			
	}
	
	function label($label = '')
	{
		global $wpdb;
		$page_string = '';
		$permalink = '';
		BAStats::getPageString($page_string, $permalink);
		if($label != '') $wpdb->query("UPDATE {$wpdb->pages} SET page_label = '" . addslashes($label) . "' WHERE page_string = '" . addslashes($page_string) . "';");
	}
	
	function iptoint($ip)
	{
		return ip2long($ip);
	}
	
	function inttoip($int)
	{
		return long2ip($int);
	}
	
	function OSMap($ua)
	{
		$matches = array(
			'Win.*NT 5\.0'=>'Windows 2000',
			'Win.*NT 5.1'=>'Windows XP',
			'Win.*(XP|2000|ME|NT|9.?)'=>'Windows $1',
			'Windows .*(3\.11|NT)'=>'Windows $1',
			'Win32'=>'Windows [unknown version]',
			'Linux 2\.(.?)\.'=>'Linux 2.$1.x',
			'Linux'=>'Linux [unknown version]',
			'FreeBSD .*-CURRENT$'=>'FreeBSD -CURRENT',
			'FreeBSD (.?)\.'=>'FreeBSD $1.x',
			'NetBSD 1\.(.?)\.'=>'NetBSD 1.$1.x',
			'(Free|Net|Open)BSD'=>'$1BSD [unknown version]',
			'HP-UX B\.(10|11)\.'=>'HP-UX B.$1.x',
			'IRIX(64)? 6\.'=>'IRIX 6.x',
			'SunOS 4\.1'=>'SunOS 4.1.x',
			'SunOS 5\.([4-6])'=>'Solaris 2.$1.x',
			'SunOS 5\.([78])'=>'Solaris $1.x',
			'Mac_PowerPC'=>'Mac OS [PowerPC]',
			'Mac'=>'Mac OS',
			'X11'=>'UNIX [unknown version]',
			'Unix'=>'UNIX [unknown version]',
			'BeOS'=>'BeOS [unknown version]',
			'QNX'=>'QNX [unknown version]',
		);
		$uas = array_map(create_function('$a', 'return "#.*$a.*#";'), array_keys($matches));
		return preg_replace($uas, array_values($matches), $ua);
	}
	
	function BrowserMap($ua)
	{
		$matches = array(
			'^Mozilla/\d+\.\d+ \(compatible; iCab ([^;]); ([^;]); [NUI]; ([^;])\)'=>'iCab $1',
			'^Opera/(\d+\.\d+) \(([^;]+); [^)]+\)'=>'Opera $1',
			'^Mozilla/\d+\.\d+ \(compatible; MSIE [^;]+; ([^)]+)\) Opera (\d+\.\d+)'=>'Opera $2',
			'^Mozilla/\d+\.\d+ \(([^;]+); [^)]+\) Opera (\d+\.\d+)'=>'Opera $2',
			'^Mozilla/[1-9]\.0 ?\(compatible; MSIE ([1-9]\.[0-9b]+);(?: ?[^;]+;)*? (Mac_[^;)]+|Windows [^;)]+)(?:; [^;]+)*\)'=>'MSIE $1',
			'^Mozilla/\d+\.\d+ \([^;]+; [NIU]; ([^;]+); [^;]+; Galeon\) Gecko/\d{8}$'=>'Galeon',
			'^Mozilla/\d+\.\d+ \([^;]+; [NIU]; Galeon; [^;]+; ([^;)]+)\)$'=>'Galeon $1',
			'^Mozilla/\d+\.\d+ Galeon/([0-9.]+) \(([^;)]+)\) Gecko/\d{8}$'=>'Galeon $1',
			'^Mozilla/\d+\.\d+ \([^;]+; [NIU]; ([^;]+); [^;]+; rv:[^;]+(?:; [^;]+)*\) Gecko/\d{8} ([a-zA-Z ]+/[0-9.b]+)'=>'$2',
			'^Mozilla/\d+\.\d+ \([^;]+; [NIU]; ([^;]+); [^;]+; rv:([^;]+)(?:; [^;]+)*\) Gecko/\d{8}$'=>'Mozilla $2',
			'^Mozilla/\d+\.\d+ \([^;]+; [NIU]; ([^;]+); [^;]+; (m\d+)(?:; [^;]+)*\) Gecko/\d{8}$'=>'Mozilla $2',
			'^Mozilla/\d+\.\d+ \([^;]+; [NIU]; ([^;]+)(?:; [^;]+)*\) Mozilla/(.+)$'=>'Mozilla $2',
			'^Mozilla/4\.(\d+)[^(]+\(X11; [NIU] ?; ([^;]+)(?:; [^;]+)*\)'=>'Netscape 4.$1',
			'^Mozilla/4\.(\d+)[^(]+\((OS/2|Linux|Macintosh|Win[^;]*)[;,] [NUI] ?[^)]*\)'=>'Netscape 4.$1',
			'^Mozilla/3\.(\d+)\S*[^(]+\(X11; [NIU] ?; ([^;]+)(?:; [^;)]+)*\)'=>'Netscape 3.$1',
			'^Mozilla/3\.(\d+)\S*[^(]+\(([^;]+); [NIU] ?(?:; [^;)]+)*\)'=>'Netscape 3.$1',
			'^Mozilla/2\.(\d+)\S*[^(]+\(([^;]+); [NIU] ?(?:; [^;)]+)*\)'=>'Netscape 2.$1',
			'^Mozilla \(X11; [NIU] ?; ([^;)]+)\)'=>'Netscape',
			'^Mozilla/3.0 \(compatible; StarOffice/(\d+)\.\d+; ([^)]+)\)$'=>'StarOffice $1',
			'^ELinks \((.+); (.+); .+\)$'=>'ELinks $1',
			'^Mozilla/3\.0 \(compatible; NetPositive/([0-9.]+); BeOS\)$'=>'NetPositive $1',
			'^Konqueror/(\S+)$'=>'Konqueror $1',
			'^Mozilla/5\.0 \(compatible; Konqueror/([^;]); ([^)]+)\).*$'=>'Konqueror $1',
			'^Lynx/(\S+)'=>'Lynx/$1',
			'^Mozilla/4.0 WebTV/(\d+\.\d+) \(compatible; MSIE 4.0\)$'=>'WebTV $1',
			'^Mozilla/4.0 \(compatible; MSIE 5.0; (Win98 A); (ATHMWWW1.1); MSOCD;\)$'=>'$2',
			'^(RMA/1.0) \(compatible; RealMedia\)$'=>'$1',
			'^antibot\D+([0-9.]+)/(\S+)'=>'antibot $1',
			'^Mozilla/[1-9]\.\d+ \(compatible; ([^;]+); ([^)]+)\)$'=>'$1',
			'^Mozilla/([1-9]\.\d+)'=>'compatible Mozilla/$1',
			'\bmsnbot/([0-9.]+)'=>'MSN Search Bot $1',
			'^Mozilla/\d+\.\d+ \(Macintosh; U; PPC Mac OS X; en\) AppleWebKit/\d+\.\d+ \(KHTML, like Gecko\) Safari/(\d+\.\d+)'=>'Safari $1',
			'^([^;]+)$'=>'$1',
		);
		foreach($matches as $srch => $repl)
		{
			if(preg_match('#' . $srch . '#i', $ua, $m))
			{
				return preg_replace('/\$([0-9])/e', '"$m[\1]"', $repl);
			}
		}
		return 'Unknown';
	}
	
	function search_engines()
	{
		$ret = array(
			'/images\\.google(\\.[a-z]{2,3})+.*(\\?|&)prev=\/images%3Fq%3D(.+?)(%26.*|$)/i'=> array('Google Image Search', 3),
			'/www\\.google(\\.[a-z]{2,3})+.*(\\?|&)q=([^&]+).*/i'=> array('Google Search', 3),
			'/search\\.msn\\.com.*(\?|&)q=([^&]+).*/i'=>array('MSN Search', 2),
			'/video\\.search\\.yahoo(\\.[a-z]{2,3})+.*(\\?|&)back=p%3D(.*?)(%26.*|$)/i'=>array('Yahoo Video Search', 3),
			'/video\.search\.yahoo(\\.[a-z]{2,3}).*(\?|&)va=([^&]+).*/i'=>array('Yahoo Video Search', 3),
			'/alltheweb\\.com.*(\?|&)q=([^&]+).*/i'=>array('AllTheWeb Search', 2),
			'/search\.yahoo\.com.*(\?|&)p=([^&]+).*/i'=>array('Yahoo Search', 2),
			'/web\.ask\.com.*(\?|&)ask=([^&]+).*/i'=>array('Ask Jeeves Search', 2),
			'/aolsearch\.aol(\\.[a-z]{2,3})+.*(\?|&)query=([^&]+).*/i'=>array('AOL Search', 3),
			'/www\.dogpile\.com.*\/([^\/]+)/i'=>array('Dogpile', 1),
			'/www\\.mywebsearch\\.com.*(\\?|&)searchfor=(.+?)(&|$).*/i'=>array('MyWay', 2),
			'/search\\.wanadoo(\\.[a-z]{2,3})+.*(\\?|&)q=(.+?)(&|$).*/i'=>array('Wanadoo', 3),
			'/www\\.technorati\\.com\/tag\/(.*)/i'=>array('Technorati Tags', 1),
		);
		$ret['/' . get_settings('site_url') . '.*(\?|&)s=([^&]+).*/i'] = array('Local Search', 2);
		return $ret;
	}
	
	function log_search($refer, $rid)
	{
		global $wpdb;
		
		$engs = BAStats::search_engines();
		foreach($engs as $keng => $eng)
		{
			if(preg_match($keng, $refer, $matches))
			{
				$wpdb->query("INSERT INTO {$wpdb->searches} (search_refer, search_phrase, search_engine) VALUES ({$rid}, '" . addslashes(urldecode($matches[$eng[1]])) . "', '" . addslashes($eng[0]) . "');");
				break;
			}
		}
	}

}

?>