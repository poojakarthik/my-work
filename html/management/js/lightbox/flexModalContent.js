/**
 * FlexModalContent - jQuery Plugin
 *
 * @version	 0.1
 * @since		 2006-11-28
 * @copyright Copyright (c) 2006 Glyphix Studio, Inc. http://www.glyphix.com
 * @author		Gavin M. Roy <gmr@glyphix.com>
 * @license	 MIT http://www.opensource.org/licenses/mit-license.php
 * @requires	>= jQuery 1.0.3 http://jquery.com/
 * @requires	dimensions.js http://jquery.com/dev/svn/trunk/plugins/dimensions/dimensions.js?format=raw
 *
 * History:
 *	0.1:
 *	 2008-03-14 Hadrian
 *		1) Created to allow flex style modal pop-ups from within the main framework pages
 *
 * Call modalContent() on a DOM object and it will make a centered modal box over a div overlay preventing access to the page.
 * Create an element (anchor/img/etc) with the class "close" in your content to close the modal box on click.
 */

/**
 * FlexModalContent 
 * @param url string to page to display in the content box
 * @param css obj of css attributes
 * @param animation (fadeIn, slideDown, show)
 * @param speed (valid animation speeds slow, medium, fast or # in ms)
 */
var FlexModalContent = {

	currentModal: null,

	display: function(url, width, height, id, css, animation, speed) 
	{
		// if we already have a modalContent, remove it
		if ( $('#modalBackdrop') ) $('#modalBackdrop').remove();
		if ( $('#modalContent') ) $('#modalContent').remove();
		FlexModalContent.remove();

		if (id == undefined || typeof id != "string") id = "FlexModalContent";

		// Create the new modal content
		var backdrop = document.createElement("DIV");
		backdrop.id = "modalBackdrop";
		backdrop.style.zIndex = 1000;
		backdrop.style.display = "block";
		backdrop.style.opacity = 0.5;
		backdrop.style.margin = "0px"; 
		backdrop.style.top = "0px"; 
		backdrop.style.left = "0px"; 
		backdrop.style.background = "rgb(0, 0, 0) none repeat scroll 0%"; 
		backdrop.style.position = "absolute"; 
		document.body.appendChild(backdrop);

		// Create the iframe
		FlexModalContent.currentModal = document.createElement("IFRAME");
		FlexModalContent.currentModal.id = id;
		FlexModalContent.currentModal.className = "flexModalContent";
		FlexModalContent.currentModal.style.zIndex = 1001;
		FlexModalContent.currentModal.style.visibility = "hidden";
		// Append the iframe to the page
		document.body.appendChild(FlexModalContent.currentModal);
		FlexModalContent.modalContentResize();
		// Load the target page
		FlexModalContent.currentModal.src = url;
		FlexModalContent.currentModal.style.visibility = "visible";

		if (animation == undefined || typeof animation != "string") animation = 'fadeIn';
		if (css == undefined || typeof animation != "object") css = {};
		if (speed == undefined || speed == null) speed = 'slow';
		$('#modalBackdrop').top(0).height('100%').width('100%').show()['fadeIn'](speed);

		$('body').bind('focus',	FlexModalContent.modalEventHandler);
		$('body').bind('keypress',	FlexModalContent.modalEventHandler);
		$(window).bind('resize',	FlexModalContent.modalContentResize);

		return false;
	},

	remove: function()
	{
		if (FlexModalContent.currentModal == null) return;

		$(window).unbind('resize',	FlexModalContent.modalContentResize);
		$('body').unbind('focus',	FlexModalContent.modalEventHandler);
		$('body').unbind('keypress',	FlexModalContent.modalEventHandler);

		FlexModalContent.currentModal.parentNode.removeChild(FlexModalContent.currentModal);
		if ( $('#modalBackdrop') ) $('#modalBackdrop').remove();

		FlexModalContent.currentModal = null;
	},

	modalEventHandler: function( event ) 
	{
		// Don't allow any events as they would not propagate from the iframe to this frame
		if (FlexModalContent.currentModal != null) FlexModalContent.currentModal.focus();
		return false;
	},

	// Move and resize the modalBackdrop and modalContent on resize of the window
	modalContentResize: function()
	{
		if (FlexModalContent.currentModal == null) return;
		FlexModalContent.currentModal.focus();
	}

};
