<?php
/*
Copyright (c) 2004
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

This file is part of WordPress.
WordPress is free software; you can redistribute it and/or modify
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

$falbum_options = get_option('falbum_options');

define ('FALBUM_THUMBNAIL_SIZE', $falbum_options['falbum_tsize']); //Size of the thumbnail you want to appear in the album thumbnail page ('s' = 75px x 75px, 't' = 100px x 75px, 'm' = 240px x 180px)
define ('FALBUM_SHOW_PRIVATE', $falbum_options['falbum_show_private']); //Whether or not to show your "private" Flickr photos
define ('FALBUM_USE_FRIENDLY_URLS', $falbum_options['falbum_friendly_urls']); //Whether or not to use friendly URLs
define ('FALBUM_URL_ROOT', $falbum_options['falbum_url_root']); //URL to use as the root for all navigational links

define ('FALBUM_ALBUMS_PER_PAGE', $falbum_options['falbum_albums_per_page']); //How many albums to show on a page (0 for no paging)
define ('FALBUM_PHOTOS_PER_PAGE', $falbum_options['falbum_photos_per_page']); //How many photos to show on a page (0 for no paging)
define ('FALBUM_MAX_PHOTO_WIDTH', $falbum_options['falbum_max_photo_width']);
define ('FALBUM_DISPLAY_DROPSHADOW', $falbum_options['falbum_display_dropshadows']);

define ('FALBUM_CACHE_EXPIRE_SHORT', 3600); //How many seconds to wait between refreshing cache (default = 3600 seconds - hour)
define ('FALBUM_CACHE_EXPIRE_LONG', 604800); //How many seconds to wait between refreshing cache (default = 604800 seconds - 1 week)

define ('FALBUM_API_KEY', 'e746ede606c9ebb66ef79605ec834c07');
define ('FALBUM_SECRET', '46d7a532dd766c9e');

define ('FALBUM_TOKEN', $falbum_options['falbum_token']);
define ('FALBUM_NSID', $falbum_options['falbum_nsid']);

/* Main function that builds the output for the album/photosets and individual photos */
function fa_show_photos ($album = null, $photo = null, $page = 0, $tags = null, $recent = null)
{
	$output = '';

	if (!is_null($recent)){
		$tags = '';
	}

	// Show list of albums/photosets (none have been selected yet)
	if (is_null($album) && is_null($tags) && is_null($photo)) {
		$output = fa_showAlbums($page);
	}
	// Show list of photos in the selected album/photoset
	elseif (!is_null($album) && is_null($photo)) {
		$output = fa_showAlbumThumbnails($album, $page);
	}
	// Show list of photos of the selected tags
	elseif (!is_null($tags) && is_null($photo))	{
		$output = fa_showTagsThumbnails($tags, $page);
	}
	// Show the selected photo in the slected album/photoset
	elseif ((!is_null($album) || !is_null($tags)) && !is_null($photo)) {
		$output = fa_showPhoto($album, $tags, $photo, $page);
	}

	echo $output;
}


function fa_showAlbums($page = 1) {

	if ($page == '') {
		$page = 1;
	}

	list($output, $expired) = fa_getCacheData("showAlbums-$page");
	if (!isset($output) || $expired) {

		$output = '';

		//

		$xpath = fa_callFlickr('flickr.photosets.getList','user_id='.FALBUM_NSID);

		if (!$xpath->getNode('/rsp/err')) {
			$result = $xpath->match("/rsp/photosets/photoset");
			$countResult = count($result);

			$count = 0;

			$output2 = '';

			$output .= "<div class=\"falbum-album\">\n";
			//$output .= "<h3 class=\"falbum-title\">Home</h3>\n";
			$output .= "<div class=\"falbum-description\"></div>\n";


			if ($page == 1) {
				//$output2 .= "<div class=\"falbum-album\">\n";
				$output2 .= fa_show_recent(1,1);
				$output2 .= "<h3 class=\"falbum-title\">";
				$output2 .= '<a href="'.fa_createURL("show/recent").'" title="'.__('View all recent photos', FALBUM_DOMAIN).'">';
				$output2 .= __('Recent Photos', FALBUM_DOMAIN)."</a>\n";
				$output2 .= "</h3>\n";
				//$output2 .= "</div>\n";
			}
			$count++;

			for ($i = 0; $i < $countResult; $i++) {
				if ((FALBUM_ALBUMS_PER_PAGE == 0) || (($count >= ($page - 1) * FALBUM_ALBUMS_PER_PAGE) && ($count < ($page * FALBUM_ALBUMS_PER_PAGE)))) {

					$photos = $xpath->getData($result[$i]."/@photos");

					if($photos != 0) {
						$id = $xpath->getData($result[$i]."/@id");
						$server = $xpath->getData($result[$i]."/@server");
						$primary = $xpath->getData($result[$i]."/@primary");
						$secret = $xpath->getData($result[$i]."/@secret");
						$title = $xpath->getData($result[$i]."/title");
						$description = $xpath->getData($result[$i]."/description");

						$thumbnail = "http://photos{$server}.flickr.com/{$primary}_{$secret}_".FALBUM_THUMBNAIL_SIZE.".jpg"; // Build URL to small square thumbnail

						$output2 .= "<div class=\"falbum-album\">\n";

						//$output2 .= "<div class=\"falbum-title2\">";

						$output2 .= '<div class="falbum-tn-border-'.FALBUM_THUMBNAIL_SIZE.'">';

						$output2 .= "<div class=\"falbum-thumbnail".FALBUM_DISPLAY_DROPSHADOW."\">";

						$output2 .= "<a href=\"";
						$output2 .= fa_createURL("album/$id");
						$output2 .="\" title=\"$title\">";
						$output2 .= "<img src=\"$thumbnail\" alt=\"$title\" />";
						$output2 .= "</a>\n";

						//$output2 .= "</div>\n";
						$output2 .= "</div>\n";
						$output2 .= "</div>\n";


						$output2 .= "<h3 class=\"falbum-title\">";
						$output2 .= "<a href=\"".fa_createURL("album/$id")."\" title=\"".strtr(__('View all pictures in #title#', FALBUM_DOMAIN),array("#title#"=>$title));
						$output2 .= "\">$title</a>\n";
						$output2 .= "</h3>\n";
						$output2 .= "<div class=\"falbum-meta\">".strtr(__('This photoset has #num_photots# pictures', FALBUM_DOMAIN),array("#num_photots#"=>$photos))."</div>\n";
						$output2 .= "<div class=\"falbum-description\">$description</div>\n";
						$output2 .= "</div>\n";

					} else {
						$count--;
					}
				}
				$count++;
			}



			if (FALBUM_ALBUMS_PER_PAGE != 0) {
				$pages = ceil($count / FALBUM_ALBUMS_PER_PAGE);
				if ($pages > 1) {
					$output .= fa_buildPaging($page, $pages, "page/");
				}
			}

			$output .= $output2;

			if (FALBUM_ALBUMS_PER_PAGE != 0) {
				if ($pages > 1) {
					$output .= fa_buildPaging($page, $pages, "page/");
				}
			}
			
			$output .= '<div class="falbum-clear"></div>';

			$output .= "</div>\n";

			fa_setCacheData("showAlbums-$page",$output);
		}
	}

	return $output;
}

function fa_showAlbumThumbnails($album, $page = 1) {

	if ($page == '') {
		$page = 1;
	}


	list($output, $expired) = fa_getCacheData("showAlbumThumbnails-$album-$page");
	if (!isset($output) || $expired) {

		$output = '';

		$top_level_xpath = fa_callFlickr('flickr.photosets.getList','user_id='.FALBUM_NSID);
		$xpath = fa_callFlickr('flickr.photosets.getPhotos','photoset_id='.$album);

		if (!$xpath->getNode('/rsp/err')) {
			$title =  $top_level_xpath->getData("//photoset[@id='$album']/title");

			$output .= "<div class=\"falbum-album\">\n";
			$output .= "<h3 class=\"falbum-title\"><a href=\"".fa_createURL()."\">".__('Home', FALBUM_DOMAIN)."</a> &raquo; {$title}</h3>\n";
			$output .= "<div class=\"falbum-description\"><a href=\"#\"  onclick=\"window.open('http://www.flickr.com/slideShow/index.gne?set_id={$album}','slideShowWin','width=500,height=500,top=150,left=70,scrollbars=no, status=no, resizable=no')\">".__('View as a slide show', FALBUM_DOMAIN)."</a></div>\n";

			$result = $xpath->match("/rsp/photoset/photo");
			$countResult = count($result);

			if (FALBUM_PHOTOS_PER_PAGE != 0) {
				$pages = ceil($countResult / FALBUM_PHOTOS_PER_PAGE);

				if ($pages > 1 ) {
					$output .= fa_buildPaging($page, $pages, "album/$album/page/");
				}
			}

			$count = 0;
			for ($i = 0; $i < $countResult; $i++) {

				if ((FALBUM_PHOTOS_PER_PAGE == 0) || (($count >= ($page - 1) * FALBUM_PHOTOS_PER_PAGE) && ($count < ($page * FALBUM_PHOTOS_PER_PAGE)))) {
					$server = $xpath->getData($result[$i]."/@server");
					$secret = $xpath->getData($result[$i]."/@secret");
					$photo_id = $xpath->getData($result[$i]."/@id");

					$output .= '<div class="falbum-tn-border-'.FALBUM_THUMBNAIL_SIZE.'">';

					$output .= "<div class=\"falbum-thumbnail".FALBUM_DISPLAY_DROPSHADOW."\">";

					$thumbnail = "http://photos{$server}.flickr.com/{$photo_id}_{$secret}_".FALBUM_THUMBNAIL_SIZE.".jpg"; // Build URL to thumbnail
					$output .= "<a href=\"".fa_createURL("album/$album/page/$page/photo/$photo_id")."\">";

					$output .= "<img src=\"$thumbnail\" alt=\"\" />";
					$output .= "</a></div></div>\n";
				}
				$count++;
			}

			$output .= "<br />";

			if (FALBUM_PHOTOS_PER_PAGE != 0 && $pages > 1) {
				$output .= fa_buildPaging($page, $pages, "album/$album/page/");
			}

			$output .= '<div class="falbum-clear"></div>';
			
			$output .= "</div>\n";



		}

		fa_setCacheData("showAlbumThumbnails-$album-$page",$output);
	}

	return $output;
}

function fa_showTagsThumbnails($tags, $page = 1) {

	if ($page == '') {
		$page = 1;
	}

	list($output, $expired) = fa_getCacheData("showTagsThumbnails-$tags-$page");
	if (!isset($output) || $expired) {

		$output = '';

		if ($tags == '') {
			// Get recent photos
			$xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$page);
		} else {
			//$xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&tags='.$tags.'&tag_mode=all&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$page);
			$xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&tags='.$tags.'&tag_mode=all&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$page);
		}

		if (!$xpath->getNode('/rsp/err')) {
			$output .= "<div class=\"falbum-album\">\n";
			$output .= "<h3 class=\"falbum-title\"><a href=\"".fa_createURL()."\">".__('Home', FALBUM_DOMAIN)."</a> &raquo; ";


			if ($tags == '') {
				$output .= __('Recent Photos', FALBUM_DOMAIN);
			} else {
				$output .= __('Tags', FALBUM_DOMAIN).": $tags";
			}
			$output .= "</h3>\n";

			$output .= "<div class=\"falbum-description\"></div>\n";


			$total_pages = $xpath->getData("/rsp/photos/@pages");
			$total_photos = $xpath->getData("/rsp/photos/@total");

			$result = $xpath->match("/rsp/photos/photo");
			$countResult = count($result);


			////////////////////

			$pages = $total_pages;

			if ($tags == '') {
				$urlPrefix = "show/recent/page/";
			} else {
				$urlPrefix = "tags/$tags/page/";
			}

			if ($pages > 1) {
				$output .= fa_buildPaging($page, $pages, $urlPrefix);
			}

			$count = 0;

			for ($i = 0; $i < $countResult; $i++) {
				$server = $xpath->getData($result[$i]."/@server");
				$secret = $xpath->getData($result[$i]."/@secret");
				$photo_id = $xpath->getData($result[$i]."/@id");


				$thumbnail = "http://photos{$server}.flickr.com/{$photo_id}_{$secret}_".FALBUM_THUMBNAIL_SIZE.".jpg"; // Build URL to thumbnail

				$output .= '<div class="falbum-tn-border-'.FALBUM_THUMBNAIL_SIZE.'">';

				$output .= "<div class=\"falbum-thumbnail".FALBUM_DISPLAY_DROPSHADOW."\">";

				if ($tags == '') {
					$output .= "<a href=\"".fa_createURL("show/recent/page/$page/photo/$photo_id")."\">";
				} else {
					$output .= "<a href=\"".fa_createURL("tags/$tags/page/$page/photo/$photo_id")."\">";
				}

				$output .= "<img src=\"$thumbnail\" alt=\"\" />";

				$output .= '</a>';
				$output .= '</div>';
				$output .= '</div>';
			}

			if ($pages > 1) {
				$output .= fa_buildPaging($page, $pages, $urlPrefix);
			}

			$output .= '<div class="falbum-clear"></div>';
			
			$output .= "</div>\n";


		}

		fa_setCacheData("showTagsThumbnails-$tags-$page",$output);
	}

	return $output;
}

function fa_showPhoto($album, $tags, $photo, $page = 1) {

	if ($page == '') {
		$page = 1;
	}

	list($output, $expired) = fa_getCacheData("showPhoto-$album-$tags-$photo-$page");
	if (!isset($output) || $expired) {

		$output = '';

		// Get Prev and Next Photos

		if (!is_null($album)) {
			$url_prefix = "album/$album";
			$top_level_xpath = fa_callFlickr('flickr.photosets.getPhotos','photoset_id='.$album);
			$result = $top_level_xpath->match("/rsp/photoset/photo");
		} else {
			if ($tags == '') {
				$url_prefix = "show/recent";
				$top_level_xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$page);
			}else {
				$url_prefix = "tags/$tags";
				$top_level_xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&tags='.$tags.'&tag_mode=all&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$page);
			}
			$result = $top_level_xpath->match("/rsp/photos/photo");

			$total_pages = $top_level_xpath->getData("/rsp/photos/@pages");
			$total_photos = $top_level_xpath->getData("/rsp/photos/@total");

		}

		if (!$top_level_xpath->getNode('/rsp/err'))	{
			$prev = $tmp_prev = $next = $photo;
			$prevPage = $nextPage = $page;

			$control = 1;

			$countResult = count($result);

			//echo '<pre>$countResult-'.$countResult.'</pre>';

			//<photos page="1" pages="1" perpage="100" total="10">


			for ($i = 0; $i < $countResult; $i++) {
				$photo_id = $top_level_xpath->getData($result[$i]."/@id");
				$secret = $top_level_xpath->getData($result[$i]."/@secret");
				$server = $top_level_xpath->getData($result[$i]."/@server");

				if ($control == 0) {
					// Selected photo was the last one, so this one is the next one
					$next = $photo_id; // Set ID of the next photo
					$next_sec = $secret; // Set ID of the next photo
					$next_server = $server; // Set ID of the next photo
					break; // Break out of the foreach loop
				}

				//echo '<pre>$photo_id-'.$photo_id.'</pre>';
				//echo '<pre>$photo-'.$photo.'</pre>';

				if ($photo_id == $photo) {

					//echo '<pre>match-'.$photo_id.'</pre>';
					//echo '<pre>i-'.$i.'</pre>';
					//echo '<pre>page-'.$page.'</pre>';
					//echo '<pre>countResult-'.$countResult.'</pre>';
					//echo '<pre>total_pages-'.$total_pages.'</pre>';

					// This is the selected photo
					$prev = $tmp_prev; // Set ID of the previous photo
					$control--; // Decrement control variable to tell next iteration of loop that the selected photo was found

					if (is_null($album)) {

						if ($i == 0 && $page > 1) {
							$findPrev = true;
						}

						if ($i == ($countResult - 1) && $page < $total_pages) {
							$findNext = true;
						}

					} else {

						//echo '<pre>$pages-'.($countResult / FALBUM_PHOTOS_PER_PAGE).'</pre>';
						//echo '<pre>1-'.(($i - 1) % FALBUM_PHOTOS_PER_PAGE).'</pre>';
						//echo '<pre>2-'.($i % FALBUM_PHOTOS_PER_PAGE).'</pre>';
						if (FALBUM_PHOTOS_PER_PAGE > 0) {
							$pages = ($countResult / FALBUM_PHOTOS_PER_PAGE);

							if ($page > 1 && (($i - 1) % FALBUM_PHOTOS_PER_PAGE) == 0 ) {
								$prevPage = $prevPage - 1;
							}

							if ($page < $pages && ($i % FALBUM_PHOTOS_PER_PAGE) == 0) {
								$nextPage = $nextPage + 1;
							}
						} else {
							$pages = $prevPage = $nextPage = 1;
						}
					}

				}
				$tmp_prev = $photo_id; // Keep the last photo in a temporary variable in case next photo is the selected on
			}


			if ($findPrev) {
				$prevPage = $prevPage - 1;

				if ($tags == '') {
					$url_prefix = "show/recent";
					$top_level_xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$prevPage);
				}else {
					$url_prefix = "tags/$tags";
					$top_level_xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&tags='.$tags.'&tag_mode=all&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$prevPage);
				}
				$result = $top_level_xpath->match("/rsp/photos/photo");
				$countResult = count($result);

				$photo_id = $top_level_xpath->getData($result[($countResult - 1)]."/@id");
				//$secret = $top_level_xpath->getData($result[0]."/@secret");
				//$server = $top_level_xpath->getData($result[0]."/@server");

				$prev = $photo_id; // Set ID of the next photo
				//$next_sec = $secret; // Set ID of the next photo
				//$next_server = $server; // Set ID of the next photo
			}

			if ($findNext) {

				$nextPage = $nextPage + 1;

				if ($tags == '') {
					$url_prefix = "show/recent";
					$top_level_xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$nextPage);
				}else {
					$url_prefix = "tags/$tags";
					$top_level_xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&tags='.$tags.'&tag_mode=all&sort=date-taken-desc&per_page='.FALBUM_PHOTOS_PER_PAGE.'&page='.$nextPage);
				}
				$result = $top_level_xpath->match("/rsp/photos/photo");

				$photo_id = $top_level_xpath->getData($result[0]."/@id");
				$secret = $top_level_xpath->getData($result[0]."/@secret");
				$server = $top_level_xpath->getData($result[0]."/@server");

				$next = $photo_id; // Set ID of the next photo
				$next_sec = $secret; // Set ID of the next photo
				$next_server = $server; // Set ID of the next photo
			}
		}

		//

		$xpath = fa_callFlickr('flickr.photos.getInfo','photo_id='.$photo);

		if (!$xpath->getNode('/rsp/err')) {

			$xpath_sizes = fa_callFlickr('flickr.photos.getSizes','photo_id='.$photo);

			$orig_w_m = $xpath_sizes->getData("//size[@label='Medium']/@width");
			$orig_h_m = $xpath_sizes->getData("//size[@label='Medium']/@height");

			if (!$xpath_sizes->getNode("//size[@label='Original']")) {
				$orig_w_o = $xpath_sizes->getData("//size[@label='Original']/@width");
				$orig_h_o = $xpath_sizes->getData("//size[@label='Original']/@height");
			}

			$server = $xpath->getData("/rsp/photo/@server");
			$secret = $xpath->getData("/rsp/photo/@secret");
			$photo_id = $xpath->getData("/rsp/photo/@id");
			$title = $xpath->getData("/rsp/photo/title");
			$date_taken =$xpath->getData("/rsp/photo/dates/@taken");
			$description =  nl2br($xpath->getData("/rsp/photo/description"));
			$comments =  $xpath->getData("/rsp/photo/comments");

			$image = "http://photos{$server}.flickr.com/{$photo}_{$secret}.jpg"; // Build URL to medium size image
			$original = "http://photos{$server}.flickr.com/{$photo}_{$secret}_o.jpg"; // Build URL to original size image

			$next_image = "http://photos{$next_server}.flickr.com/{$next}_{$next_sec}.jpg"; // Build URL to medium size image

			$output .= "<div class=\"falbum-album\">\n";

			//$output .= "<h3 class=\"falbum-title\">{$title}</h3>\n";
		
			
			$output .= "<h3 class=\"falbum-title\"><a href=\"".fa_createURL()."\">".__('Home', FALBUM_DOMAIN)."</a> &raquo; ";
			$output .=  "<a href=\"".fa_createURL("$url_prefix/page/{$page}")."\">".__('Index', FALBUM_DOMAIN)."</a>";
			$output .= " &raquo; {$title}</h3>\n";
			

			$date_taken = (date(__('M j, Y - g:i A', FALBUM_DOMAIN), strtotime($date_taken)));

			$output .= "<div class=\"falbum-date-taken\">".
			strtr(__('Taken on: #date_taken#', FALBUM_DOMAIN),array("#date_taken#"=>$date_taken))."</div>\n";

			$output .= "<div class=\"falbum-photo-block\">\n";

			$output .= "<div class=\"falbum-photo".FALBUM_DISPLAY_DROPSHADOW."\">\n";

			if ($next != $photo) {
				$output .= "<a href=\"".fa_createURL("$url_prefix/page/$nextPage/photo/$next")."\" title=\"".__('Click to view next image', FALBUM_DOMAIN)."\">\n";
			} else {
				$output .= "<a href=\"".fa_createURL("$url_prefix/page/$page")."\" title=\"".__('Click to return to album', FALBUM_DOMAIN)."\">\n";
			}
			$output .= "<img src=\"$image\" alt=\"\" usemap=\"imgmap\" id=\"flickr-photo\" class=\"annotated\" ";

			$output .= "width=\"";
			if (FALBUM_MAX_PHOTO_WIDTH != '0' && FALBUM_MAX_PHOTO_WIDTH < $orig_w_m) {
				$output .= FALBUM_MAX_PHOTO_WIDTH;
			} else {
				$output .= $orig_w_m;
			}
			$output .= "\"";
			$output .= "/></a>\n";
			$output .= "</div>";


			$output .= "<div class=\"falbum-nav\">";
			if (($prev != $photo) && ($next != $photo)) {
				// Show both previous and next navigation
				$output .= fa_getButton('pageprev',fa_createURL("$url_prefix/page/$prevPage/photo/$prev"),"&laquo; ".__('Previous', FALBUM_DOMAIN),__('Previous Photo', FALBUM_DOMAIN),1);
				$output .= '&nbsp;&nbsp;';
				$output .= fa_getButton('return',fa_createURL("$url_prefix/page/$page"),__('Index', FALBUM_DOMAIN),__('Return to album index', FALBUM_DOMAIN),1);
				$output .= '&nbsp;&nbsp;';
				$output .= fa_getButton('pagenext',fa_createURL("$url_prefix/page/$nextPage/photo/$next"),"&nbsp;&nbsp; ".__('Next', FALBUM_DOMAIN)." &raquo;&nbsp;&nbsp;",__('Next Photo', FALBUM_DOMAIN),1);

			} elseif (($prev != $photo) && ($next == $photo)) {
				// Show only previous navigation
				$output .= fa_getButton('pageprev',fa_createURL("$url_prefix/page/$prevPage/photo/$prev"),"&laquo; ".__('Previous', FALBUM_DOMAIN),__('Previous Photo', FALBUM_DOMAIN),1);
				$output .= '&nbsp;&nbsp;';
				$output .= fa_getButton('return',fa_createURL("$url_prefix/page/$page"),__('Index', FALBUM_DOMAIN),__('Return to album index', FALBUM_DOMAIN),1);

			} elseif (($prev == $photo) && ($next != $photo)) {
				// Show only next navigation
				$output .= fa_getButton('return',fa_createURL("$url_prefix/page/$page"),__('Index', FALBUM_DOMAIN),__('Return to album index', FALBUM_DOMAIN),1);
				$output .= '&nbsp;&nbsp;';
				$output .= fa_getButton('pagenext',fa_createURL("$url_prefix/page/$nextPage/photo/$next"),"&nbsp;&nbsp; ".__('Next', FALBUM_DOMAIN)." &raquo;&nbsp;&nbsp;",__('Next Photo', FALBUM_DOMAIN),1);

			} else {
				$output .= fa_getButton('return',fa_createURL("$url_prefix/page/$page"),__('Index', FALBUM_DOMAIN),__('Return to album index', FALBUM_DOMAIN),1);
			}

			$output .= "</div>";
			$output .= "</div>";

			$output .= "<div class=\"falbum-description\">\n";
			$output .= "<p>{$description}</p>\n";
			$output .= "</div>\n";
			$output .= "<div class=\"falbum-meta\">\n";

			$output .= "<p>".__('View the', FALBUM_DOMAIN)." <a href=\"$original\" title=\"{$title}\">".__('original photo', FALBUM_DOMAIN);
			if (isset($orig_w_o)) {
				$output .= " ({$orig_w_o}x{$orig_h_o})";
			}
			$output .= "</a></p>\n";

			$result = $xpath->match("/rsp/photo/tags/tag");
			$countResult = count($result);

			if ($countResult > 0) {
				// Output "see similar" links if tags exist
				/*
				$output .= "<p>See similar photos on Flickr: ";
				foreach ($tree['rsp']['photo']['tags'] as $tag_id => $tag_details) // Loop through tags
				{
				$output .= "<a href=\"http://www.flickr.com/photos/tags/{$tag_details['value']}/\">{$tag_details['value']}</a> ";
				}
				$output .= "</p>\n";
				*/

				$output .= "<p>".__('See similar photos', FALBUM_DOMAIN).": ";

				for ($i = 0; $i < $countResult; $i++) {
					$value = $xpath->getData($result[$i]);
					$output .= "<a href=\"".fa_createURL("tags/{$value}/")."\">{$value}</a> ";
				}
				$output .= "</p>\n";
			}

			$output .= "<p><a href=\"http://www.flickr.com/photos/".FALBUM_NSID."/$photo\">".
			__('See this photo on Flickr', FALBUM_DOMAIN)."&nbsp;";

			if ($comments == 1) {
				$output .= strtr(__('(#num_comments# comment)', FALBUM_DOMAIN),array("#num_comments#"=>$comments));
			} else {
				$output .=  strtr(__('(#num_comments# comments)', FALBUM_DOMAIN),array("#num_comments#"=>$comments));
			}

			$output .= "</a></p>\n";
			$output .= "</div>\n";

			$result = $xpath->match("/rsp/photo/notes/note");
			$countResult = count($result);


			if (FALBUM_MAX_PHOTO_WIDTH > 0 && FALBUM_MAX_PHOTO_WIDTH < $orig_w_m) {
				$scale = FALBUM_MAX_PHOTO_WIDTH / $orig_w_m; // Notes are relative to Medium Size
			} else {
				$scale = 1;
			}

			if ($countResult > 0) {
				// Output "see similar" links if tags exist
				$output .= "<map id=\"imgmap\">\n";
				for ($i = 0; $i < $countResult; $i++) {
					$value = nl2br($xpath->getData($result[$i]));
					$x = 5 + $xpath->getData($result[$i]."/@x") * $scale;
					$y = 5 + $xpath->getData($result[$i]."/@y") * $scale;
					$w = $xpath->getData($result[$i]."/@w") * $scale;
					$h = $xpath->getData($result[$i]."/@h") * $scale;

					$output .= "<area alt=\"\" title=\"".htmlentities($value)."\" nohref=\"nohref\" shape=\"rect\" coords=\"{$x},{$y},".($x + $w - 1).",".($y + $h - 1)."\" />\n";
				}
				$output .= "</map>\n";
			}

			$remote_url = get_settings('siteurl')."/wp-content/plugins/falbum/falbum-remote.php";

			$output .= "<div id=\"exif\" class=\"falbum-exif\"><a href=\"javascript:showExif('{$photo_id}','{$secret}','{$remote_url}');\">".__('Show Exif Data', FALBUM_DOMAIN)."</a></div>";
			$output .= "</div>\n";

			$output .= "<script type=\"text/javascript\">\n";
			$output .= "//<!--\n";
			$output .= "falbum_prefetch('$next_image');\n";
			$output .= "//-->\n";
			$output .= "</script>\n";

		}

		fa_setCacheData("showPhoto-$album-$tags-$photo-$page",$output);
	}

	return $output;
}

function fa_getExif($photo_id,$secret) {

	list($output, $expired) = fa_getCacheData("getExif-$photo_id-$secret");
	if (!isset($output) || $expired) {

		$output = '';

		$exif_xpath = fa_callFlickr('flickr.photos.getExif','photo_id='.$photo_id.'&secret='.$secret, FALBUM_CACHE_OPTIONS_LONG);

		$result = $exif_xpath->match("//EXIF");
		$countResult = count($result);

		$output .= "<table>";
		for ($i = 0; $i < $countResult; $i++) {
			$label = $exif_xpath->getData($result[$i]."/@label");
			$raw = $exif_xpath->getData($result[$i]."/raw");

			$output .= "<tr ";

			if ($i % 2 == 0) {
				$output .= 'class="even"';
			} else {
				$output .= 'class="odd"';
			}

			$output .= "><td>$label</td><td>";
			$r1 = $exif_xpath->match($result[$i]."/clean");
			if (count($r1) > 0) {
				$output .= $exif_xpath->getData($result[$i]."/clean");
			}else{
				$output .= $raw;
			}

			$output .= "</td></tr>";
		}
		$output .= "</table>";

		fa_setCacheData("getExif-$photo_id-$secret",$output);
	}

	return $output;
}

function fa_show_recent($num = 5, $style = 0) {

	list($output, $expired) = fa_getCacheData("show_recent-$num-$style");
	if (!isset($output) || $expired) {

		$output = '';

		$xpath = fa_callFlickr('flickr.photos.search','user_id='.FALBUM_NSID.'&per_page='.$num.'&sort=date-taken-desc');

		if (!$xpath->getNode('/rsp/err')) {

			if ($style == 0) {

				$output .= "<div class=\"falbum-recent\">\n";
				$output .= "<ul>\n";

				$result = $xpath->match("//photo");
				$countResult = count($result);

				for ($i = 0; $i < $countResult; $i++) {
					$server = $xpath->getData($result[$i]."/@server");
					$secret = $xpath->getData($result[$i]."/@secret");
					$photo_id = $xpath->getData($result[$i]."/@id");

					$output .= "<li>\n";

					$thumbnail = "http://photos{$server}.flickr.com/{$photo_id}_{$secret}_".FALBUM_THUMBNAIL_SIZE.".jpg"; // Build URL to thumbnail

					$output .= "<a href=\"".fa_createURL("show/recent/photo/$photo_id")."\">";

					$output .= "<img src=\"$thumbnail\" alt=\"\" class=\"falbum-recent-thumbnail\" />";
					$output .= "</a><br />\n";
					$output .= "</li>\n";
				}
				$output .= "</ul>\n";
				$output .= "</div>\n";

			} elseif ($style == 1) {

				$result = $xpath->match("//photo");
				$countResult = count($result);

				for ($i = 0; $i < $countResult; $i++) {
					$server = $xpath->getData($result[$i]."/@server");
					$secret = $xpath->getData($result[$i]."/@secret");
					$photo_id = $xpath->getData($result[$i]."/@id");

					$thumbnail = "http://photos{$server}.flickr.com/{$photo_id}_{$secret}_".FALBUM_THUMBNAIL_SIZE.".jpg"; // Build URL to thumbnail

					$output .= '<div class="falbum-tn-border-'.FALBUM_THUMBNAIL_SIZE.'">';
					$output .= "<div class=\"falbum-thumbnail".FALBUM_DISPLAY_DROPSHADOW."\">";
					$output .= "<a href=\"".fa_createURL("show/recent")."\">";
					$output .= "<img src=\"$thumbnail\" alt=\"\" />";
					$output .= "</a></div></div>\n";
				}

			}

		}

		fa_setCacheData("show_recent-$num-$style",$output);
	}

	return $output;
}

function fa_callFlickr($method, $parms='', $cache_option = FALBUM_FALBUM_CACHE_OPTIONS_SHORT) {

	if (strtolower(FALBUM_SHOW_PRIVATE) == "true" ) {

		$np = 'method='.$method.'&api_key='.FALBUM_API_KEY.'&auth_token='.FALBUM_TOKEN;

		if ($parms != '') {
			$np .= '&'.$parms;
		}

		$p = explode("&", $np);
		sort($p);
		$m = FALBUM_SECRET;
		foreach ($p as $key => $val) {
			$m .= str_replace("=","",$val);
		}

		//echo "<pre>{$m}</pre>";

		$url = 'http://www.flickr.com/services/rest/?'.implode('&',$p);
		$url .= '&api_sig='.md5($m);

	} else {

		$np = 'method='.$method.'&api_key='.FALBUM_API_KEY;
		if ($parms != '') {
			$np .= '&'.$parms;
		}
		$url = 'http://www.flickr.com/services/rest/?'.$np;
	}

	//echo '<pre>'.htmlentities($url).'</pre>';

	$resp = fa_fopen_url($url, $cache_option); // Do the Flickr API call

	//echo '<pre>'.htmlentities($resp).'</pre>';

	$xpath = fa_parseXPath($resp); // Parse the results of the Flickr API call

	if ($xpath->getNode('/rsp/err')) {
		$output .= "<div class=\"falbum-album\">\n";
		$output .= "<pre>$url\n\n".
		strtr(__('An error occurred.  Here is the response from Flickr:\n\n#error#\n\nAnd here is the response after passing through the parser:\n\n#response#', FALBUM_DOMAIN),array("#error#"=>htmlentities($resp),"#response#"=>$xpath->exportAsHtml()));
		$output .= "</div>\n";
		echo $output;
	}

	return $xpath;
}

function fa_getCacheData($key) {
	global $wpdb;

	$fp_cache_exp = 0;

	//echo "<pre>fa_getCacheData key-".addslashes(serialize($key))."</pre>";

	//get existing data from db
	$output = $wpdb->get_row("SELECT data, (UNIX_TIMESTAMP(expires) - UNIX_TIMESTAMP()) expires FROM falbum_cache WHERE ID='" . md5($key) . "'");

	if (isset($output)) {
		$data = unserialize(stripslashes($output->data));
		$expires_value = $output->expires;

		//echo '<pre>data-'.htmlentities($data).'</pre>';
		//echo '<pre>$expires_value-'.htmlentities($expires_value).'</pre>';

		if ($expires_value < 0) {
			$expired = true;
		}else {
			$expired = false;
		}
	}

	//echo "<pre>expired-$expired</pre>";

	return array($data, $expired);
}


function fa_setCacheData($key, $data, $cache_option = FALBUM_CACHE_EXPIRE_SHORT) {
	global $wpdb;
	$wpdb->query("REPLACE INTO falbum_cache SET ID='" . md5($key) . "', data='" . addslashes(serialize($data)) . "', expires=DATE_ADD(NOW(), INTERVAL ".$cache_option." SECOND)");
}

function fa_fopen_url($url, $cache_option = FALBUM_CACHE_EXPIRE_SHORT, $fsocket_timeout = 120) {
	//echo "<pre>$url</pre>";

	list($data, $expired) = fa_getCacheData($url);

	//echo '<pre>data-'.htmlentities($data).'</pre>';
	//echo '<pre>expired-'.htmlentities($expired).'</pre>';

	if (!isset($data) || $expired) {

		$urlParts = parse_url($url);
		$host = $urlParts['host'];
		$port = (isset($urlParts['port'])) ? $urlParts['port'] : 80;

		if( !$fp = @fsockopen( $host, $port, $errno, $errstr, $fsocket_timeout )) {
			$data = __('Flickr server not responding', FALBUM_DOMAIN);
		} else {

			if( !fputs( $fp, "GET $url HTTP/1.0\r\nHost:$host\r\n\r\n" )) {
				$data = __('Unable to send request', FALBUM_DOMAIN);
			}

			$ndata = null;
			stream_set_timeout($fp, $fsocket_timeout);
			$status = socket_get_status($fp);
			while( !feof($fp) && !$status['timed_out'])
			{
				$ndata .= fgets ($fp,8192);
				$status = socket_get_status($fp);
			}
			fclose ($fp);

			// strip headers
			$sData = split("\r\n\r\n", $ndata, 2);
			$ndata = $sData[1];

			//echo '<pre>'.htmlentities($ndata).'</pre>';

			$pos = strrpos($ndata, "<rsp stat=\"ok\">");

			//echo '<pre>'.htmlentities($pos).'</pre>';

			if ($pos !== false) {
				$data = $ndata;
				fa_setCacheData($url,$data);
			}
		}

	} else {
		//echo "<pre>Using cache - $url</pre>'";
	}

	return $data;
}

function fa_createURL($parms = '') {
	if ($parms != '') {
		if (strtolower(FALBUM_USE_FRIENDLY_URLS) == 'false')	{
			$pattern = '`^/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?$`i';
			$replacement = '?$1=$2&$3=$4&$5=$6&$7=$8';
			$parms = preg_replace($pattern, $replacement, $parms);
			$parms = str_replace('&=','',$parms);
			
		}
	}
	return FALBUM_URL_ROOT.$parms;
}

/* Function that parses the XML results from the Flickr API (based on torsten@jserver.de's fa_parseXPath function found at http://www.php.net/manual/en/ref.xml.php) */
function fa_parseXPath ($data) {

	require_once("XPath.class.php");

	$xmlOptions = array(XML_OPTION_CASE_FOLDING => TRUE, XML_OPTION_SKIP_WHITE => FALSE);
	$xPath =& new XPath(FALSE, $xmlOptions);
	//$xPath->bDebugXmlParse = TRUE;
	if (!$xPath->importFromString($data)) { echo $xPath->getLastError(); }

	//echo $xPath->exportAsHtml();

	return $xPath;
}

function fa_buildPaging($page, $pages, $urlPrefix) {

	$sAlbHeader.="\n\n<!-- Paging -->";
	$sAlbHeader.="<div class='falbum-navigationBar' id='pages'>".__('Page:', FALBUM_DOMAIN)."&nbsp;";

	if ($page > 1 && $pages > 1) {
		$title=strtr(__('Go to previous page (#page#)', FALBUM_DOMAIN),array("#page#"=>$page-1));
		$sAlbHeader.= fa_getButton('pageprev',fa_createURL($urlPrefix.($page-1)),__('Previous', FALBUM_DOMAIN),$title,0);
	}

	for ($i=1; $i<=$pages; $i++) {
		// We display 1 ... 14 15 16 17 18 ... 29 when there are too many pages
		if ($pages>10) {

			$mn=$page-4;
			$mx=$page+4;

			if ($i<=$mn) {
				if ($i==2)
				$sAlbHeader.="<span class='pagedots'>&nbsp;&hellip;&nbsp;</span>";
				if ($i!=1)
				continue;
			}
			if ($i>=$mx) {
				if ($i==$pages - 1)
				$sAlbHeader.="<span class='pagedots'>&nbsp;&hellip;&nbsp;</span>";
				if ($i!=$pages)
				continue;
			}
		}
		$id="page$i";
		if ($i==$page)
		$id="curpage";

		$sAlbHeader.= fa_getButton($id,fa_createURL($urlPrefix.$i),$i,"",($i?0:1));
	}
	if ($page < $pages) {
		$title=strtr(__('Go to next page (#page#)', FALBUM_DOMAIN),array("#page#"=>$page+1));
		$sAlbHeader.= fa_getButton('pagenext',fa_createURL($urlPrefix.($page+1)),__('Next', FALBUM_DOMAIN),$title,1);
	}
	$sAlbHeader.="</div>\n\n";

	return $sAlbHeader;

}


function fa_getButton($id, $href, $text, $title, $nSpacer, $target="_self", $bCallCustom=true)
{
	//if ($bCallCustom && function_exists('customGetButton'))
	//    return customGetButton($id, $href, $text, $title, $nSpacer, $target);

	// Begin toolbar/end toolbar stuff
	if (substr($id,0,1)=='#')
	return "";

	$class='buttonLink';
	if ($id=='curpage')
	{
		$class='curPageLink';
	}
	else if (preg_match('/^page[0-9]+$/',$id))
	{
		$class='otherPageLink';
	}


	$x="";

	if ($nSpacer==1)
	$space='&nbsp;';
	if ($nSpacer==2)
	$space='&nbsp;&nbsp;&nbsp;';

	if (!empty($space))
	$x.="<span id='space_$id' class='buttonspace'>$space</span>";

	if (!empty($href))
	$x.="<a class='$class' href='$href' id='$id' title='$title' target='$target'>" . $text . "</a>";
	else
	$x.="<span class='disabledButtonLink' id='$id' >" . $text . "</span>";
	return $x;
}


/*
function getAsXhtml($text) {
$text = str_replace("&", "&amp;", $text);
$text = str_replace("<", "&lt;", $text);
$text = str_replace(">", "&gt;", $text);
return $text;
}

function getAsXhtmlFromCdata($text) {
$text = str_replace("<![CDATA[", "", $text);
$text = str_replace("]]>", "", $text);
return $text;
}
*/

?>
