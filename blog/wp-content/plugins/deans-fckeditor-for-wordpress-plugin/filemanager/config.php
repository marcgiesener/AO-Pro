<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration file for the PHP File Uploader.
 */
require_once(dirname(__FILE__). '/../deans_fckeditor_class.php');
global $Config ;

// SECURITY: You must explicitelly enable this "uploader".
$Config['Enabled'] = $deans_fckeditor->can_upload();

// Set if the file type must be considere in the target path.
// Ex: /userfiles/image/ or /userfiles/file/
$Config['UseFileType'] = true ;

// Path to uploaded files relative to the document root.
$Config['UserFilesPath'] = $deans_fckeditor->user_files_url;

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Usefull if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
$Config['UserFilesAbsolutePath'] = $deans_fckeditor->user_files_absolute_path;

// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

$Config['AllowedExtensions']['File']	= array() ;
$Config['DeniedExtensions']['File']		= explode(',', $deans_fckeditor->file_denied_ext);

$Config['AllowedExtensions']['Image']	= explode(',', $deans_fckeditor->image_allowed_ext);
$Config['DeniedExtensions']['Image']	= array() ;

$Config['AllowedExtensions']['Flash']	= explode(',', $deans_fckeditor->flash_allowed_ext);;
$Config['DeniedExtensions']['Flash']	= array() ;

?>


