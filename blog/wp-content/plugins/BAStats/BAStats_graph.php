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

class BAS_graph
{
	var $width = 600;
	var $height = 400;
	var $colors = array();
	var $caption = "";

	var $series = array();
	var $xlabels = array();
	
	var $image;
	var $graphtype = 'bar';
	
	function BAS_graph()
	{
		$this->image = ImageCreate($this->width, $this->height);
		$this->InitColors();	
	}
	
	function InitColors()
	{
		$this->SetColor('y_grade', '#EEEEEE');
		$this->SetColor('x_grade', '#FFFFFF');
		$this->SetColor('background', '#FFFFFF');
		$this->SetColor('frame', '#EEEEEE');
		$this->SetColor('values', '#999999');
		$this->SetColor('black', '#000000');
		$this->SetColor('white', '#FFFFFF');
		$this->SetColor('light_gray', '#CCCCCC');
	}
	
	function SetColor($name, $hcolor)
	{
		if(isset($this->colors[$name]))
		{
			ImageColorDeallocate($this->image, $this->colors[$name]);
		}
		$this->colors[$name] = $this->hexcolor($hcolor);
	}
	
	function hexcolor($h)
	{
		if($h{0} == '#') $h = substr($h, 1);
		sscanf($h, "%2x%2x%2x", $red, $green, $blue);
		return ImageColorAllocate($this->image, $red, $green, $blue);
	}
	
	function AddSeries($name, $s)
	{
		$this->series[$name] = $s;
	}
	
	function DrawRect($rectary, $color)
	{
		ImageRectangle($this->image, $rectary[0], $rectary[1], $rectary[2], $rectary[3], $color);
	}
	
	function Build()
	{
		ImageFill($this->image, 0, 0, $this->colors['background']);
		ImageRectangle($this->image, 0, 0, $this->width - 1, $this->height - 1, $this->colors['frame']);
		
		$fwidth = ImageFontWidth(1);
		$fheight = ImageFontHeight(1);
		
		$graph_max = 0;
		$graph_min = 9999;
		foreach($this->series as $seriesname => $series)
		{
			foreach($this->xlabels as $label)
			{
				if(!isset($series[$label]))
				{
					$this->series[$seriesname][$label] = 0;
				}
			}
		}
		foreach($this->series as $s)
		{
			$av = array_values($s);
			$series_max = count($s) > 1 ? max($s) : $av[0];
			$graph_max = max($series_max, $graph_max);
			$series_min = count($s) > 1 ? min($s) : $av[0];
			$graph_min = min($series_min, $graph_min);
		}
		//owd($this->series);
		if($graph_max == $graph_min)
		{
			$graph_max += 10;
			$graph_min = 0;
		}
		$graph_min = 0;
		
		ImageString($this->image, 1, 60, 2, $graph_min . ' - ' . $graph_max, $this->colors['black']);
		$cw_graph_max = floor(log10(abs($graph_max)) + 1) * $fwidth;
		
		$graph_rect = array($cw_graph_max + 2 * $fwidth, $fheight * 2, $this->width - $cw_graph_max, $this->height - $fwidth * 17);
		$this->DrawRect($graph_rect, $this->colors['frame']);
		$graph_height = $graph_rect[3] - $graph_rect[1];
		$graph_width = $graph_rect[2] - $graph_rect[0];
		for($z=0;$z<=4;$z++)
		{
			$y = $graph_rect[1] + ($z * $graph_height / 4);
			ImageLine($this->image, $graph_rect[0], $y, $graph_rect[2], $y, $this->colors['frame']);
			ImageString($this->image, 1, 4, $y - $fheight / 2, round(($graph_height - ($y - $graph_rect[1])) / $graph_height * ($graph_max - $graph_min) + $graph_min), $this->colors['black']);		
		}
		
		$x_spot = 0;
		$graph_inc = $graph_width / (count($series));
		$bar_width = ($graph_inc - 1) / count($this->series);
		foreach($this->xlabels as $label)
		{
			//$x_spot = 0;
			$series_index = 0;
			foreach($this->series as $seriesname => $series)
			{
				$value = isset($series[$label])? $series[$label] : 0;
				$x = $x_spot * $graph_inc + $graph_rect[0];
				$y = $graph_rect[1] + $graph_height - ($value - $graph_min) * (($graph_height) / ($graph_max - $graph_min));

				switch($this->graphtype)
				{
				case 'bar':
					ImageFilledRectangle($this->image, $x + $series_index * $bar_width, $y, $x + ($series_index + 1) * $bar_width - 1, $graph_rect[3], $this->colors[$seriesname]);
					$labely = min($y + ($fheight * 1), $graph_rect[3] - ($fheight * 1.5));				
					ImageString($this->image, 1, $x + $series_index * $bar_width + $fwidth + 1, $labely + 1, $value, $this->colors['black']);
					ImageString($this->image, 1, $x + $series_index * $bar_width + $fwidth, $labely, $value, $this->colors['light_gray']);
					break;
				case 'line';
					ImageFilledRectangle($this->image, $x-2, $y-2, $x+2, $y+2, $this->colors[$seriesname]);
					ImageString($this->image, 1, $x - ($fwidth * log10($value) / 2), $y - ($fheight * 1.2), $value, $this->colors['values']);
					if($lx[$seriesname] != 0)
					{
						ImageLine($this->image, $lx[$seriesname], $ly[$seriesname], $x, $y, $this->colors[$seriesname]);
					}
					else
					{
						ImageString($this->image, 1, $x + $fwidth, $y - ($fheight * .5), $seriesname, $this->colors[$seriesname]);
						//ImageString($this->image, 1, $x + $series_index * $bar_width, $graph_rect[3] - ($fheight * 2), $seriesname, $this->colors[$seriesname]);
					}
					$lx[$seriesname] = $x;
					$ly[$seriesname] = $y;
					break;
				}

				$series_index ++;
			}		
		
			$x = $x_spot * $graph_inc + $graph_rect[0] - ($fheight / 2);
			$y = min($graph_rect[3] + $fwidth * strlen($label), $this->height);
			ImageStringUp($this->image, 1, $x, $y + $fwidth /2, $label, $this->colors['black']);
			$x_spot ++;
		}
	}
	
	function Output()
	{
		$this->Build();
		if(function_exists('ImageGif'))
		{
			Header("Content-type: image/gif");
			ImageGif($this->image);	
		}
		else
		{
			Header("Content-type: image/jpeg");
			ImageJpeg($this->image);	
		}
	}
}

?>
<?php

class BAS_grapher extends BAS_graph
{


	function BAS_grapher($graph)
	{
		BAS_graph::BAS_graph();
	
		global $user_level;
		get_currentuserinfo();

		// Authorize the user to operate this page solo
		if($user_level == 0)
		{
			die("Unauthorized.");
		}

		$this->do_graph($graph);
	}
	
	function do_graph($graph)
	{
		global $basp, $wpdb;
		
		$reports = $basp->get_reports();

		$report = $reports[$graph];

		$rangeset = isset($_GET['rangeset']) ? $_GET['rangeset'] : 'ten_minutes';
		list($start, $end) = $basp->get_range($rangeset);
		$stamp = '';
		$lasthere = '';
		$_starttext = '';
		$_endtext = '';
		$limit = isset($_GET['limit']) ? $_GET['limit'] : '10';
		if($start != false)
		{
			$_starttext = "'" . date('Y-m-d H:i:s', $start) . "'";
			$stamp .= " AND stamp >= '" . date('Y-m-d H:i:s', $start) . "'";
			$lasthere .= " AND lasthere >= '" . date('Y-m-d H:i:s', $start) . "'";
		}
		if($end != false)
		{
			$_endtext = "'" . date('Y-m-d H:i:s', $end) . "'";
			$stamp .= " AND stamp <= '" . date('Y-m-d H:i:s', $end) . "'";
			$lasthere .= " AND lasthere <= '" . date('Y-m-d H:i:s', $end) . "'";
		}
		$_stamp = $stamp;
		$_lasthere = $lasthere;
		//Range increments
		$timespan = $end - $start;

		if($timespan > 60*60*24*365*2) { //1 year
			$_grouping = "DATE_FORMAT(stamp, '%Y')";
			$_ordering = "DATE_FORMAT(stamp, '%Y')";
			$_datechunk = "'%Y'";
		}
		elseif($timespan > 60*60*24*31) { //1 month
			$_grouping = "DATE_FORMAT(stamp, '%Y-%m')";
			$_ordering = "DATE_FORMAT(stamp, '%Y-%m')";
			$_datechunk = "'%Y-%m'";
		}
		elseif($timespan > 60*60*24) { //1 day
			$_grouping = "DATE_FORMAT(stamp, '%Y-%m-%d')";
			$_ordering = "DATE_FORMAT(stamp, '%Y-%m-%d')";
			$_datechunk = "'%Y-%m-%d'";
		}
		elseif($timespan > 60*60) { //1 hour
			$_grouping = "DATE_FORMAT(stamp, '%Y-%m-%d %H')";
			$_ordering = "DATE_FORMAT(stamp, '%Y-%m-%d %H')";
			$_datechunk = "'%Y-%m-%d %H'";
		}
		elseif($timespan > 60) { //1 minute
			$_grouping = "DATE_FORMAT(stamp, '%Y-%m-%d %H:%i')";
			$_ordering = "DATE_FORMAT(stamp, '%Y-%m-%d %H:%i')";
			$_datechunk = "'%Y-%m-%d %H:%i'";
		}
		else {
			$_grouping = "DATE_FORMAT(stamp, '%Y-%m-%d')";
			$_ordering = "DATE_FORMAT(stamp, '%Y-%m-%d')";
			$_datechunk = "'%Y-%m-%d'";
		}


		$query = $report['series'];
		$qry = preg_replace('/{([^}]+)}/e', '\$\1', $query);
		$data = $wpdb->get_results($qry);

		$series_filter = create_function('$row', $report['label_filter']);



		$newcolor = 0;
		if($data)
		{
			foreach($data as $s_data)
			{
				$series_index = $s_data->$report['series_index'];
				$series_caption = $series_index . ':' . $series_filter($s_data->$report['series_label']);
				$qresult = '';

				foreach($report['data'] as $query)
				{
					if(($query != $report['data'][0]) && is_array($qresult))
					{
						extract($qresult[0]);
					}
					//$qry = preg_replace_callback('/{([^}]+)}/e', 'graph_query', $query);
					$qry = preg_replace('/{([^}]+)}/e', 'substr(\'\1\', 0, 1)!=\'_\'?addslashes(\$\1):\$\1', $query);
					//$qry = preg_replace('/{([^}]+)}/e', '\$\1', $query);
					$qresult = $wpdb->get_results($qry);		
				}
				if($qresult)
				{
					foreach($qresult as $res)
					{
						$this->xlabels[$res->Label] = $res->Label;
						$series[$res->Label] = $res->Count;
					}
				}
				else
				{
					die('No Results: ' . $qry);
				}

				$this->AddSeries($series_caption, $series);
				$this->SetColor($series_caption, $basp->get_color($newcolor, count($data)));
				$newcolor++;
			}
		}

		/*
		// TEST DATA //
		$this->xlabels = array('2005-02-16'=>'2005-02-16', '2005-02-17'=>'2005-02-17', '2005-02-18'=>'2005-02-18');
		$this->AddSeries('test', array('2005-02-16'=>10, '2005-02-17'=>35, '2005-02-18'=>13));
		$this->SetColor('test', $colors[0]);
		*/

		//$g->graphtype = 'line';
		$this->Build();
		//owd($g->series);
		$this->output();
	}
}


function include_up($filename)
{
	$c=0;
	while(!is_file($filename))
	{
		$filename = '../' . $filename;
		$c++;
		if($c==30) {
			echo 'Could not find ' . basename($filename) . '.';
			return '';
		}
	}
	return $filename;
}

function owd($v)
{
	echo "<pre>".print_r($v,1)."</pre>";
}

require_once(include_up('wp-config.php'));
require_once("BAStats.php");

$g = new BAS_grapher($_GET['graph']);
?>