<?php
/**
 *     ---------------       DO NOT DELETE!!!     ---------------
 * 
 *     Plugin Name:  User Level Themes
 *     Plugin URI:   http://www.doubleblackdesign.com/categories/wordpress-plugins/user-level-themes/
 *     Description:  Allows a site admin to specify the site's theme based on the user's access level. You can modify the specified themes on the <a href="themes.php?page=user-level-themes-options">User-Level-Themes Options Page</a>.
 *     Version:      1.03
 *     Author:       Double Black Design
 *     Author URI:   http://www.doubleblackdesign.com
 *
 *     ---------------       DO NOT DELETE!!!     ---------------
 *
 *    This is the required license information for a Wordpress plugin.
 *
 *    Copyright 2007  Keith Huster  (email : husterk@doubleblackdesign.com)
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program; if not, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *     ---------------       DO NOT DELETE!!!     ---------------
 */


/**
 * Include the WordpressPluginFramework.
 */
require_once( "wordpress-plugin-framework.php" ); 



/**
 * UserLevelThemesPlugin - Allows a site admin to specify the site's theme based on the user's access level.
 *
 * NOTE: This plugin can be used to allow a site administrator to safely test a new theme while redirecting
 * visitors to a known working theme.
 * 
 * @package user-level-themes
 * @since {WP 2.3} 
 * @author Keith Huster
 */
class UserLevelThemesPlugin extends UserLevelThemes_WordpressPluginFramework
{
   // ---------------------------------------------------------------------------
   // Methods used to display content block within the plugin administration page.
   // ---------------------------------------------------------------------------

   /**
	 * HTML_DisplayPluginDescriptionBlock() - Displays the "Plugin Description" content block.
	 *
	 * This function generates the markup required to display the specified content block.
	 *
	 * @param void      None.
	 * 
    * @return void     None.  	 
	 * 
	 * @access private  Access via internal callback only.
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function HTML_DisplayPluginDescriptionBlock()
   {
      $wpfString = 'Wordpress Plugin Framework (v' . $this->PLUGIN_FRAMEWORK_VERSION . ')';
      
      ?>
      <strong>Plugin Description</strong>
      <ul>
      <li>The User Level Themes plugin allows a site administrator to select different themes for users with and without administrative
      access rights. This feature can be used to safely test a new theme while redirecting visitors to a known working theme.</li>
      </ul>
      <br />
      <strong>Wordpress Plugin Framework (WPF)</strong>
      <ul>
      <li>This plugin utilizes the <a href="http://www.doubleblackdesign.com/categories/wordpress-plugins/wordpress-plugin-framework/"><?php echo( $wpfString ); ?></a> to
      provide a standard administration interface and allow for simplified plugin management.</li>
      </ul>
      <?php
   }

   /**
	 * HTML_DisplayPluginOptionsBlock() - Displays the "Plugin Options Displayed" content block.
	 *
	 * This function generates the markup required to display the specified content block.
	 *
	 * @param void      None.
	 * 
    * @return void     None.  	 
	 * 
	 * @access private  Access via internal callback only.
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function HTML_DisplayPluginOptionsBlock()
   {
      $this->DisplayPluginOption( 'userLevelThemes_defaultThemeOption' );
      ?>
      <ul>
         <li>The default theme was selected to be the currently active theme at the time of activation of this plugin. This
      value cannot be changed is only displayed as a reference so that you will be aware of which theme will be displayed
      should you decide to uninstall or deactivate this plugin.</li>
      </ul>
      <br />
      <?php
      $this->DisplayPluginOption( 'userLevelThemes_adminThemeOption' );
      ?>
      <ul>
         <li>The administrator theme is the theme that will be displayed when a user with administrative access rights browses
      this website.</li>
      </ul>
      <br />
      <?php
      $this->DisplayPluginOption( 'userLevelThemes_visitorThemeOption' );
      ?>
      <ul>
         <li>The visitor theme is the theme that will be displayed when a user without administrative access rights browses
      this website.</li>
      </ul>
      <?php
   }

   /**
	 * HTML_DisplaySupportThisPluginBlock() - Displays the "Support This Plugin" content block.
	 *
	 * This function generates the markup required to display the specified content block.
	 *
	 * @param void      None.
	 * 
    * @return void     None.
	 * 
	 * @access private  Access via internal callback only.
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function HTML_DisplaySupportThisPluginBlock()
   {
      ?>
      Please make a donation to help support development of this plugin.
      <br />
      <br />
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=husterk%40doubleblackdesign%2ecom&item_name=Wordpress%20Plugin%3a%20User%20Level%20Themes&no_shipping=1&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8">
         <img src="http://www.doubleblackdesign.com/wp-content/images/paypal_donate.gif" alt="Make a Donation!" />
      </a>
      <br />
      <?php
   }
   
   /**
	 * HTML_DisplayAboutThisPluginBlock() - Displays the "About This Plugin" content block.
	 *
	 * This function generates the markup required to display the specified content block.
	 *
	 * @param void      None.
	 * 
    * @return void     None.
	 * 
	 * @access private  Access via internal callback only.
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function HTML_DisplayAboutThisPluginBlock()
   {
      ?>
      &raquo; <a href="http://www.doubleblackdesign.com/categories/wordpress-plugins/user-level-themes/">Plugin Homepage</a>
      <br />
      <br />
      &raquo; <a href="http://doubleblackdesign.com/forums/user-level-themes-plugin/0/">Plugin Support Forum</a>
      <br />
      <br />
      &raquo; <a href="http://www.doubleblackdesign.com/categories/wordpress-plugins/wordpress-plugin-framework/">WPF Homepage</a>
      <br />
      <br />
      &raquo; <a href="http://doubleblackdesign.com/forums/wordpress-plugin-framework/0/">WPF Support Forum</a>
      <?php
   }
   
   
   
   // ---------------------------------------------------------------------------
   // Methods used to process themes.
   // ---------------------------------------------------------------------------
         
   /**
	 * GetCurrentThemeName() - Gets the name of the current theme.
	 *
	 * This function gets the name of the current theme.
	 *
	 * @param void      None.
	 * 
    * @return string   Name of the currently active theme.  	 
	 * 
	 * @access public
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function GetCurrentThemeName()
   {
      // Get the current theme name from the Wordpress core.
      $currentThemeName = get_current_theme();
      
      return $currentThemeName; 
   }
   
   
   /**
	 * GetAvailableThemeNames() - Gets an array of the names of the available themes.
	 *
	 * This function gets an array of the available theme names that have been published.
	 *
	 * @param void      None.
	 * 
    * @return array    Array of available theme names.  	 
	 * 
	 * @access public
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function GetAvailableThemeNames()
   {
      $availableThemesArray = array();
      
      // Get a list of the currently available themes from the Wordpress core.
      $themesArray = get_themes();
      if( is_array( $themesArray ) )
      {
         // Extract the theme names from the array and sort the names logically.
         $themeNamesArray = array_keys($themesArray);
         natcasesort($themeNamesArray);
         
         // Loop through each of the available theme names but only process the published themes.
         $availableThemesIndex = 0;
         foreach( $themeNamesArray AS $themeName )
         {
			   if( isset( $themesArray[$themeName]['Status'] ) && ( $themesArray[$themeName]['Status'] == 'publish' ) )
			   {
               $availableThemesArray[$availableThemesIndex] = $themeName;
            }
            
            $availableThemesIndex++;
         }
      }
      else
      {
         // The only available theme is the one that is currently in use.
         $currentThemeName = $this->GetCurrentThemeName();
         $availableThemesArray[0] = $currentThemeName;
      }
      
      return $availableThemesArray;
   }
   
   
   /**
	 * _GetUserLevelStylesheet() - Gets the appropriate stylesheet based on the user level.
	 *
	 * This function gets the appropriate stylesheet based on the user level and the plugin options.
	 *
	 * @param mixed     Theme stylesheet to be utilized by the Wordpress core.
	 * 
    * @return mixed    Updated theme stylesheet to be utilized by the Wordpress core.  	 
	 * 
	 * @access private  Accessed via Wordpress "stylesheet" filter callback only.
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function _GetUserLevelStylesheet( $stylesheet )
   {
      global $user_level;
      get_currentuserinfo();
      
      $themesArray = get_themes();
      if( is_array( $themesArray ) )
      {
         // Select the appropriate theme name for the specified user access level.
         if( $user_level >= $this->ACCESS_LEVEL_ADMINISTRATOR )
         {
            $themeName = $this->GetOptionValue('userLevelThemes_adminThemeOption');
         }
         else
         {
            $themeName = $this->GetOptionValue('userLevelThemes_visitorThemeOption');
         }
         
         // Verify that a valid theme was returned from the database. If it wasn't then we will need to revert back
         // to the default theme that was specified when the plugin was installed.
         if( !empty( $themeName ) )
         {
            $stylesheet = $themesArray[$themeName]['Stylesheet'];
         }
         else
         {
            $optionsArray = $this->GetOptionsArray();
            $themeName = $optionsArray['userLevelThemes_defaultThemeOption'][$this->OPTION_INDEX_VALUE];
            
            // Verify that the default theme is still valid. If it is not, then we will just have to leave the
            // stylesheet alone.
            if( array_key_exists( $themeName, $themesArray ) )
            {
               $stylesheet = $themesArray[$themeName]['Stylesheet'];
            }
         }
      }
   
      return $stylesheet;
   }
   
   
   /**
	 * _GetUserLevelTemplate() - Gets the appropriate template based on the user level.
	 *
	 * This function gets the appropriate template based on the user level and the plugin options.
	 *
	 * @param mixed     Theme template to be utilized by the Wordpress core.
	 * 
    * @return mixed    Updated theme template to be utilized by the Wordpress core.  	 
	 * 
	 * @access private  Accessed via Wordpress "template" filter callback only.
    * @since {WP 2.3}
	 * @author Keith Huster
	 */
   function _GetUserLevelTemplate( $template )
   {
      global $user_level;
      get_currentuserinfo();
      
      $themesArray = get_themes();
      if( is_array( $themesArray ) )
      {
         // Select the appropriate theme name for the specified user access level.
         if( $user_level >= $this->ACCESS_LEVEL_ADMINISTRATOR )
         {
            $themeName = $this->GetOptionValue('userLevelThemes_adminThemeOption');
         }
         else
         {
            $themeName = $this->GetOptionValue('userLevelThemes_visitorThemeOption');
         }
         
         // Verify that a valid theme was returned from the database. If it wasn't then we will need to revert back
         // to the default theme that was specified when the plugin was installed.
         if( !empty( $themeName ) )
         {
            $template = $themesArray[$themeName]['Template'];
         }
         else
         {
            $optionsArray = $this->GetOptionsArray();
            $themeName = $optionsArray['userLevelThemes_defaultThemeOption'][$this->OPTION_INDEX_VALUE];
            
            // Verify that the default theme is still valid. If it is not, then we will just have to leave the
            // template alone.
            if( array_key_exists( $themeName, $themesArray ) )
            {
               $template = $themesArray[$themeName]['Template'];
            }
         }
      }
   
      return $template;
   }
}



/**
 * Create and manage the User Level Themes plugin.
 */
if( !$userLevelThemesPlugin  )
{
   // ---------------------------------------------------------------------------
   // User Level Themes plugin initialization.
   // ---------------------------------------------------------------------------
   
   // Create a new instance of and initialize the User Level Themes plugin.
   $userLevelThemesPlugin = new UserLevelThemesPlugin();
   $userLevelThemesPlugin->Initialize( 'User Level Themes',
                                       '1.03',
                                       'user-level-themes',
                                       'user-level-themes' );
  
   // ---------------------------------------------------------------------------
   // User Level Themes options configuration and initialization.
   // ---------------------------------------------------------------------------
   
   // Create the combobox option for the default theme to be utilized when the plugin is uninstalled.
   $currentThemeNameArray = array();
   $currentThemeNameArray[0] = $userLevelThemesPlugin->GetCurrentThemeName();
   $userLevelThemesPlugin->AddOption( $userLevelThemesPlugin->OPTION_TYPE_COMBOBOX,
                                      'userLevelThemes_defaultThemeOption',
                                      $currentThemeName,
                                      '<strong>Default Theme:</strong>',
                                      $currentThemeNameArray );
        
   // Create the combobox options for the admin and visitor themes (the default value for both should be the current theme).
   $availableThemeNamesArray = $userLevelThemesPlugin->GetAvailableThemeNames();
   $userLevelThemesPlugin->AddOption( $userLevelThemesPlugin->OPTION_TYPE_COMBOBOX,
                                      'userLevelThemes_adminThemeOption',
                                      $currentThemeName,
                                      '<strong>Administrator Theme:</strong>',
                                      $availableThemeNamesArray );
   $userLevelThemesPlugin->AddOption( $userLevelThemesPlugin->OPTION_TYPE_COMBOBOX,
                                      'userLevelThemes_visitorThemeOption',
                                      $currentThemeName,
                                      '<strong>Visitor Theme:</strong>',
                                      $availableThemeNamesArray );

   // Register the plugin options with the WPF core.
   $userLevelThemesPlugin->RegisterOptions( __FILE__ );
  
   // ---------------------------------------------------------------------------
   // User Level Themes administration page configuration and initialization.
   // ---------------------------------------------------------------------------
   
   // Add the SIDEBAR content blocks to the plugin's administration page.
   $userLevelThemesPlugin->AddAdministrationPageBlock( 'block-support-this-plugin',
                                                       'Support This Plugin',
                                                       $userLevelThemesPlugin->CONTENT_BLOCK_TYPE_SIDEBAR,
                                                       array( $userLevelThemesPlugin, 'HTML_DisplaySupportThisPluginBlock' ) );
   $userLevelThemesPlugin->AddAdministrationPageBlock( 'block-about-this-plugin',
                                                       'About This Plugin',
                                                       $userLevelThemesPlugin->CONTENT_BLOCK_TYPE_SIDEBAR,
                                                       array( $userLevelThemesPlugin, 'HTML_DisplayAboutThisPluginBlock' ) );
   
   // Add the MAIN content blocks to the plugin's administration page.
   $userLevelThemesPlugin->AddAdministrationPageBlock( 'block-description',
                                                       'User Level Themes - Description',
                                                       $userLevelThemesPlugin->CONTENT_BLOCK_TYPE_MAIN,
                                                       array( $userLevelThemesPlugin, 'HTML_DisplayPluginDescriptionBlock' ) );
   $userLevelThemesPlugin->AddAdministrationPageBlock( 'block-options',
                                                       'User Level Themes - Options',
                                                       $userLevelThemesPlugin->CONTENT_BLOCK_TYPE_MAIN,
                                                       array( $userLevelThemesPlugin, 'HTML_DisplayPluginOptionsBlock' ) );
      
   // Register the plugin's administration page with the WPF core.
   $userLevelThemesPlugin->RegisterAdministrationPage( $userLevelThemesPlugin->PARENT_MENU_PRESENTATION,
                                                       $userLevelThemesPlugin->ACCESS_LEVEL_ADMINISTRATOR,
                                                       'User-Level-Themes',
                                                       'User Level Themes Options',
                                                       'user-level-themes-options' );
   
   // ---------------------------------------------------------------------------
   // User Level Themes stylesheet and template redirection filters.
   // ---------------------------------------------------------------------------
   
   // Finally we need to add the filters required to redirect the Wordpress core to the appropriate theme.
   add_filter( 'stylesheet', array( $userLevelThemesPlugin, '_GetUserLevelStylesheet' ) );
   add_filter( 'template', array( $userLevelThemesPlugin, '_GetUserLevelTemplate' ) );
}

?>
