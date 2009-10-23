/*
 * File Name: fckplugin.js
 *      Plugin to insert wordpress more Tag into the editor.
 * 
 * Version: 1.0
 *
 * File Authors:
 *	      Dean Lee (http://www.deanlee.cn)
 */
var FCKwpMore = function(name) 
{
	this.Name = name;
}

FCKwpMore.prototype.Execute=function()
{
	var oMore = FCK.EditorDocument.createComment( 'more' );
	var oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'FCK__PageBreak', oMore) ;
	oFakeImage	= FCK.InsertElement( oFakeImage ) ;
}

FCKwpMore.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
}

var FCKwpMoreProcessor = FCKDocumentProcessor.AppendNew() ;
FCKwpMoreProcessor.ProcessDocument = function( document )
{
	function _Replace( m, opener, index )
	{
		var protectedValue = FCKTempBin.Elements[ index ] ;
		if (protectedValue == '<!--more-->')
		{
			var index2 = FCKTempBin.AddElement(FCK.EditorDocument.createComment( 'more' ));
			var oFakeImg = "<img class='FCK__PageBreak' src='images/spacer.gif'  _fckfakelement='true' _fckrealelement='"+ index2 + "'/>";
			return oFakeImg;
		}
		else
		{
			return m;
		}
	}
	// Parse all <!--more--> tags and replace them with images
	var content = FCK.EditorDocument.body.innerHTML;
	FCK.EditorDocument.body.innerHTML = content.replace(/(<|&lt;)!--\{PS..(\d+)\}--(>|&gt;)/g, _Replace);
}

FCKCommands.RegisterCommand('wpmore', new FCKwpMore('wpmore'));
var owpMore = new FCKToolbarButton('wpmore', 'WordPress Read More');
owpMore.IconPath = FCKConfig.PluginsPath + 'wpmore/images/more.gif';
FCKToolbarItems.RegisterItem( 'wpmore', owpMore );