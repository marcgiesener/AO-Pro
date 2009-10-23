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
require_once(dirname(__FILE__).'/../../../wp-config.php');
require_once('check_update.php');
class deans_fckeditor {
    var $version = '2.2';
    var $default_options = array();
	var $fckeditor_path = "";
	var $plugin_path ="";

	//var $smiley_images = array();
    function deans_fckeditor()
    {
		$siteurl = trailingslashit(get_option('siteurl'));
		$this->plugin_path =  $siteurl .'wp-content/plugins/' . basename(dirname(__FILE__)) .'/';
		$this->fckeditor_path = $siteurl .'wp-content/plugins/' . basename(dirname(__FILE__)) .'/fckeditor/';
        $this->default_options['user_file_path'] = 'wp-content/uploads/';
        $this->default_options['EditorHeight'] = '400';
		$this->default_options['file_denied_ext'] = 'php,php2,php3,php4,php5,phtml,pwml,inc,asp,aspx,ascx,jsp,cfm,cfc,pl,bat,exe,com,dll,vbs,js,reg,cgi,htaccess';
		$this->default_options['image_allowed_ext'] = 'jpg,gif,jpeg,png';
		$this->default_options['flash_allowed_ext'] = 'swf,fla';
//		$this->default_options['media_allowed_ext'] = 'swf,fla,jpg,gif,jpeg,png,avi,mpg,mpeg';
		$this->default_options['smiley_path'] = 'wp-content/plugins/' . basename(dirname(__FILE__)) .'/smiles/msn/';
		$this->default_options['toolbar_set'] = 'Default';
		$this->default_options['skin'] = 'default';
		$this->default_options['enable_preview'] = 'yes';
		$this->default_options['FirefoxSpellChecker'] = 'true';
		$this->default_options['default_link_target'] = '';

		$options = get_option('deans_fckeditor');
		if (!$options) {
			$this->build_smiley_images($this->default_options['smiley_path']);
			$this->default_options['smiley_images'] = $this->smiley_images;
			add_option('deans_fckeditor', $this->default_options);
			$options = $this->default_options;
		}
		foreach ($options as $option_name => $option_value)
	        $this-> {$option_name} = $option_value;
		
		$path = str_replace(ABSPATH, '', trim($this->user_file_path));
		$dir = ABSPATH . $path;
		if ( $dir == ABSPATH ) { //the option was empty
			$dir = ABSPATH . 'wp-content/uploads';
		}
		$this->user_files_absolute_path = $dir;
		$this->user_files_url = $siteurl . $path;
    }

	function can_upload()
	{
		if ((function_exists('current_user_can') && current_user_can('upload_files')) || (isset($user_level) && $user_level >= 3))
		{
			return true;
		}
		return false;
	}

	function deactivate()
	{
		global $current_user;
		update_user_option($current_user->id, 'rich_editing', 'true', true);
	}

	function activate()
	{
		global $current_user;
		update_user_option($current_user->id, 'rich_editing', 'false', true);
	}

	function build_smiley_images($smiley_path = '')
	{
		if ($smiley_path == '')
		{
			$smiley_path = $this->smiley_path;
		}
		$sServerDir = ABSPATH . $smiley_path;
		$aFiles		= array() ;
		$oCurrentFolder = opendir( $sServerDir ) ;
		while ( $sFile = readdir( $oCurrentFolder ) )
		{
			if ( $sFile != '.' && $sFile != '..' )
			{
				if ( !is_dir( $sServerDir . $sFile ) )
				{
					$path_parts = pathinfo($sFile);
					if (in_array($path_parts['extension'], array('gif','jpeg','jpg','png')))
					{
						$aFiles[] = '\''.$sFile.'\'';
					}
				}
			}
		}
		natcasesort( $aFiles ) ;
		$this->smiley_images = $aFiles;
	}

    function option_page()
    {
        $message = "";
        if (!empty($_POST['submit_update']) || !empty($_POST['doRebuild'])) {
			$new_options = array (
				'user_file_path' => trim($_POST['ch_str_UserFilesPath']),
				'EditorHeight' => trim($_POST['EditorHeight']),
				'file_denied_ext' =>trim($_POST['file_denied_ext']),
				'image_allowed_ext' =>trim($_POST['image_allowed_ext']),
				'flash_allowed_ext' =>trim($_POST['flash_allowed_ext']),
				'smiley_path'=>trim($_POST['ch_smiley_folder']),
				'toolbar_set' =>trim($_POST['cmbToolbars']),
				'skin' =>trim($_POST['cmbSkins']),
				'enable_preview' => trim($_POST['ckEnablePreview']),
				'FirefoxSpellChecker' => trim($_POST['ckFirefoxSpellChecker']),
				'default_link_target' => trim($_POST['cmdefault_link_target'])
				);

			if (empty($new_options['user_file_path']))
			{
				$new_options['user_file_path'] = 'wp-content/uploads';
			}
			if ( ! ereg( '/$', $new_options["user_file_path"] ) )
				$new_options["user_file_path"] .= '/' ;
			
			if (empty($new_options['smiley_path']))
			{
				$new_options['smiley_path'] = $this->default_options['smiley_path'];
			}
			$new_options['smiley_path'] = trailingslashit($new_options['smiley_path']);
			
			$this->build_smiley_images($new_options['smiley_path']);
			$new_options['smiley_images'] = $this->smiley_images;
			update_option("deans_fckeditor", $new_options);

			foreach ($new_options as $option_name => $option_value)
		        $this-> {$option_name} = $option_value;

			echo '<div class="updated"><p>' . __('Configuration updated!') . '</p></div>';
        }
		else if (isset($_POST['submit_reset'])) {
				$this->build_smiley_images($this->default_options['smiley_path']);
				$this->default_options['smiley_images'] = $this->smiley_images;
				update_option('deans_fckeditor', $this->default_options);
				foreach ($this->default_options as $option_name => $option_value)
					$this-> {$option_name} = $option_value;
				echo '<div class="updated"><p>' . __('Configuration updated!') . '</p></div>';
		}
        ?>
		<div class=wrap>
		<?php check_for_update('http://www.deanlee.cn/plugin_update.php', 'deans_fckeditor', $this->version) ?>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"];
        ?>">
		<h2><?php _e('Dean\'s FCKEditor', 'deans_fckeditor') ?> <?php echo $this->version?></h2>
			<div style="float:right"><a href="http://www.deanlee.cn" title="www.deanlee.cn"><img src="<?php echo $this->plugin_path?>cw.png"/></a></div><fieldset name="sm_basic_options"  class="options">
			<legend><?php _e('Basic Options', 'deans_fckeditor') ?></legend>
			<ul>
			<li><?php _e('Editor height:')?><input type="text" name="EditorHeight"  value="<?php echo $this->EditorHeight;
        ?>"/>px
				</li>
				<li>
				<?php _e('Enable Post Preview:')?><input type="checkbox" name="ckEnablePreview"  value="yes" <?php if ($this->enable_preview == 'yes'){echo ' checked="checked"';}?> />
				</li>
				<li>
				<?php _e('Enable the Firefox built-in spellchecker:')?><input type="checkbox" name="ckFirefoxSpellChecker"  value="true" <?php if ($this->FirefoxSpellChecker == 'true'){echo ' checked="checked"';}?> /><br />
				enable/disable the Firefox built-in spellchecker while typing. Even if word suggestions will not appear in the FCKeditor context menu, this feature is useful to quickly identify misspelled words<br />
				</li>
				<li><?php _e('Select the toolbar to load:')?>
				<select name="cmbToolbars">
				<option value="Default" <?php if ($this->toolbar_set == 'Default') { ?> selected="selected"<?php } ?>>Default</option>
				<option value="Basic" <?php if ($this->toolbar_set == 'Basic') { ?> selected="selected"<?php } ?>>Basic</option>
			</select>
				</li>
		<li><?php _e('Select the skin to load:')?>
				<select name="cmbSkins"">
				<option value="default"  <?php if ($this->skin == 'default') { ?> selected="selected"<?php } ?>>Default</option>
				<option value="office2003" <?php if ($this->skin == 'office2003') { ?> selected="selected"<?php } ?>>Office 2003</option>
				<option value="silver" <?php if ($this->skin == 'silver') { ?> selected="selected"<?php } ?>>Silver</option>
			</select>
				</li>
		<li>
		<span fckLang="DlgLnkTarget">Default link Target</span><br />
						<select name="cmdefault_link_target">
							<option value="" <?php if ($this->default_link_target == '') { ?> selected="selected"<?php } ?>>&lt;not set&gt;</option>
							<option value="frame" <?php if ($this->default_link_target == 'frame') { ?> selected="selected"<?php } ?>>&lt;frame&gt;</option>
							<option value="popup" <?php if ($this->default_link_target == 'popup') { ?> selected="selected"<?php } ?>>&lt;popup window&gt;</option>
							<option value="_blank" <?php if ($this->default_link_target == '_blank') { ?> selected="selected"<?php } ?>>New Window (_blank)</option>
							<option value="_top" <?php if ($this->default_link_target == '_top') { ?> selected="selected"<?php } ?>>Topmost Window (_top)</option>
							<option value="_self" <?php if ($this->default_link_target == '_self') { ?> selected="selected"<?php } ?>>Same Window (_self)</option>
							<option value="_parent" <?php if ($this->default_link_target == '_parent') { ?> selected="selected"<?php } ?>>Parent Window (_parent)</option>
						</select>
		</li>
				</ul>
			</fieldset>
		<fieldset name="sm_upload_options"  class="options">
			<legend><?php _e('Upload Options', 'deans_fckeditor') ?></legend>
			<ul>
			<li><?php _e('Store uploads in this folder:')?>:<br /><input type="text" style="width: 60%;" size="50" name="ch_str_UserFilesPath" value="<?php echo htmlentities($this->user_file_path);?>"/><br/><?php _e('Default is')?> <code>wp-content/uploads</code>
				</li>
				<li>Denied file extension:<br /><input type="text" style="width: 60%;" size="50" name="file_denied_ext" value="<?php echo htmlentities($this->file_denied_ext);?>"/></li>
				<li>Allowed image extension:<br /><input type="text" style="width: 60%;" size="50" name="image_allowed_ext" value="<?php echo htmlentities($this->image_allowed_ext);?>"/></li>
				<li>Allowed flash extension:<br /><input type="text" style="width: 60%;" size="50" name="flash_allowed_ext" value="<?php echo htmlentities($this->flash_allowed_ext);?>"/></li>
				</ul>
			</fieldset>
			<fieldset name="df_rebuild" class="options">
					<legend><?php _e('Smiles Options', 'deans_fckeditor') ?></legend>
					<ul><li>
					<?php _e('Store smiles in this folder:')?>:<br /><input type="text" style="width: 60%;" size="50" name="ch_smiley_folder" value="<?php echo htmlentities($this->smiley_path);?>"/><br/><?php _e('Default is')?> <code><?php echo $this->default_options['smiley_path']?></code><br /><input type="submit" id="doRebuild" name="doRebuild" Value="<?php _e('Rebuild smiles Cache','deans_fckeditor'); ?>" />
				</li>
					</li><li>
					<?php _e('current smiles in cache: (you will need to rebuild the cache if you have uploaded new smiles for the change to take effect)', 'deans_fckeditor') ?>
						<br />
					<?php foreach ($this->smiley_images as $smiley)
		{
						$imgurl = trailingslashit(get_option('siteurl')) . trailingslashit($this->smiley_path);
						echo '<img src="' . $imgurl . str_replace('\'', '', $smiley) . '" />';
		}
		?>
					</li>
					</ul>
			</fieldset>
				<p class="submit">
				<input type="hidden" name="df_submit" value="1" />
				<input type="submit" value="Reset to defaults" name="submit_reset" id="default-reset" />
				<input type="submit" value="Update Options &#187;" name="submit_update" />
				</p>
						
			<fieldset class="options">
			<legend><?php _e('Informations and support', 'deans_fckeditor') ?></legend>
			<p><?php echo str_replace("%s", "<a href=\"http://www.deanlee.cn/wordpress/fckeditor-for-wordpress-plugin/\">http://www.deanlee.cn/wordpress/fckeditor-for-wordpress-plugin/</a>", __("Check %s for updates and comment there if you have any problems / questions / suggestions.", 'deans_fckeditor'));
        ?></p>
		</fieldset>
		</form></div>
		<?php
    }

    function add_admin_head()
    {
    ?>
		<script type="text/javascript" src="<?php echo $this->fckeditor_path;?>fckeditor.js"></script>
		<style type="text/css">
					#quicktags { display: none; }
		</style>
	<?php
    }

	function load_fckeditor()
	{
		?>
	<script type="text/javascript">
		//<![CDATA[
		function _deans_fckeditor_load(){
			var oFCKeditor = new FCKeditor( 'content' ) ;
			oFCKeditor.Config["CustomConfigurationsPath"] = "<?php echo $this->plugin_path . 'custom_config_js.php';?>";
			oFCKeditor.BasePath = "<?php echo $this->fckeditor_path;?>" ;
			oFCKeditor.Height = "<?php echo $this->EditorHeight;?>" ;
			oFCKeditor.Config[ "BaseHref"] = "<?php echo get_settings('siteurl');?>" ;
			oFCKeditor.ToolbarSet = '<?php echo $this->toolbar_set?>'; 
			oFCKeditor.ReplaceTextarea() ;
		}
		_deans_fckeditor_load();

	//]]>
	</script>
		<?php
	}

    function user_personalopts_update()
    {
        global $current_user;
        update_user_option($current_user->id, 'rich_editing', 'false', true);
    }

    function add_option_page()
    {
		add_options_page('FCKEditor', 'FCKEditor', 8, 'deans_fckeditor', array(&$this, 'option_page'));
		/*add_submenu_page('post.php', 'FCKEditor','FCKEditor',1, 'deans_fckeditor', array(&$this, 'option_page'));*/
    }

	function disable_preview()
	{
		if ($this->enable_preview != 'yes')
		{
		?>
		<script type="text/javascript">
		var oPreview = document.getElementById('preview');
		if (oPreview)
			{
			oPreview.innerHTML = '&nbsp;';
			}
		</script>
		<?php
		}
	}
}

$deans_fckeditor = new deans_fckeditor();
?>