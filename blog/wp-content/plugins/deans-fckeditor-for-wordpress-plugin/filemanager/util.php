<?php
/*
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
 */

function RemoveExtension( $fileName )
{
	return substr( $fileName, 0, strrpos( $fileName, '.' ) ) ;
}

function GetRootPath()
{
	$sRealPath = realpath( './' ) ;

	$sSelfPath = $_SERVER['PHP_SELF'] ;
	$sSelfPath = substr( $sSelfPath, 0, strrpos( $sSelfPath, '/' ) ) ;

	return substr( $sRealPath, 0, strlen( $sRealPath ) - strlen( $sSelfPath ) ) ;
}

function RemoveFromStart( $sourceString, $charToRemove )
{
	$sPattern = '|^' . $charToRemove . '+|' ;
	return preg_replace( $sPattern, '', $sourceString ) ;
}

function RemoveFromEnd( $sourceString, $charToRemove )
{
	$sPattern = '|' . $charToRemove . '+$|' ;
	return preg_replace( $sPattern, '', $sourceString ) ;
}

function ConvertToXmlAttribute( $value )
{
	return utf8_encode( htmlspecialchars( $value ) ) ;
}

?>