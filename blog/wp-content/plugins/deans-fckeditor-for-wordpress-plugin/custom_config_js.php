<?php
require_once('deans_fckeditor_class.php');
$fck_browser_url = $deans_fckeditor->plugin_path .'filemanager/browser/browser.html?Connector=connectors/connector.php';
$fck_upload_url = $deans_fckeditor->plugin_path .'filemanager/upload/upload.php';
$fck_can_upload = $deans_fckeditor->can_upload() ? 'true' : 'false';
?>
FCKConfig.Plugins.Add( 'wpmore');
FCKConfig.ToolbarSets["Default"] = [
	['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak','-','wpmore'],
	'/',
	['Style','FontFormat','FontName','FontSize'],
	['TextColor','BGColor'],
	['FitWindow','-','About']
] ;

FCKConfig.ToolbarSets["Basic"] = [
	['Source', 'Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','Smiley','-','wpmore','-','About']
] ;
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/<?php echo $deans_fckeditor->skin;?>/' ;
FCKConfig.FirefoxSpellChecker = <?php echo (empty($deans_fckeditor->FirefoxSpellChecker) ? 'false' : 'true');?> ;
FCKConfig.DefaultLinkTarget = '<?php echo (empty($deans_fckeditor->default_link_target) ? '' : $deans_fckeditor->default_link_target);?>' ;

FCKConfig.LinkBrowser = <?php echo $fck_can_upload?> ;
FCKConfig.LinkBrowserURL =  '<?php echo $fck_browser_url; ?>';
FCKConfig.LinkBrowserWindowWidth	= FCKConfig.ScreenWidth * 0.7 ;		// 70%
FCKConfig.LinkBrowserWindowHeight	= FCKConfig.ScreenHeight * 0.7 ;	// 70%

FCKConfig.ImageBrowser = <?php echo $fck_can_upload?> ;
FCKConfig.ImageBrowserURL = '<?php echo $fck_browser_url; ?>&Type=Image';
FCKConfig.ImageBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;	// 70% ;
FCKConfig.ImageBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;	// 70% ;

FCKConfig.FlashBrowser = <?php echo $fck_can_upload?> ;
FCKConfig.FlashBrowserURL = '<?php echo $fck_browser_url; ?>&Type=Flash';
FCKConfig.FlashBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;	//70% ;
FCKConfig.FlashBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;	//70% ;

FCKConfig.LinkUpload = <?php echo $fck_can_upload?> ;
FCKConfig.LinkUploadURL = '<?php echo $fck_upload_url;?>';
FCKConfig.LinkUploadAllowedExtensions	= "" ;			// empty for all
FCKConfig.LinkUploadDeniedExtensions	= ".(<?php echo str_replace(',','|', $deans_fckeditor->file_denied_ext)?>)$" ;	// empty for no one

FCKConfig.ImageUpload = <?php echo $fck_can_upload?> ;
FCKConfig.ImageUploadURL = '<?php echo $fck_upload_url;?>?Type=Image';
FCKConfig.ImageUploadAllowedExtensions	= ".(<?php echo str_replace(',','|', $deans_fckeditor->image_allowed_ext)?>)$" ;		// empty for all
FCKConfig.ImageUploadDeniedExtensions	= "" ;							// empty for no one

FCKConfig.FlashUpload = <?php echo $fck_can_upload?>;
FCKConfig.FlashUploadURL = '<?php echo $fck_upload_url;?>?Type=Flash';
FCKConfig.FlashUploadAllowedExtensions	= ".(<?php echo str_replace(',','|', $deans_fckeditor->flash_allowed_ext)?>)$" ;		// empty for all
FCKConfig.FlashUploadDeniedExtensions	= "" ;					// empty for no one

FCKConfig.SmileyPath	= '<?php echo trailingslashit(get_option('siteurl')).$deans_fckeditor->smiley_path;?>';
FCKConfig.SmileyImages	= [<?php echo implode(',',$deans_fckeditor->smiley_images)?>];
FCKConfig.SmileyColumns = 8 ;
FCKConfig.SmileyWindowWidth		= 320 ;
FCKConfig.SmileyWindowHeight	= 260 ;
