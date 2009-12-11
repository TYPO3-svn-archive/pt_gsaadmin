/** 
 * JavaScript/jQuery logic for the BE module 'Articles' of the 'pt_gsaadmin' extension.
 * This requires the jQuery library to be included in the page _before_ including this script (this is done by default in tx_ptgsaadmin_submodules::init()).
 *
 * $Id: mod_articles.js,v 1.4 2008/03/03 17:04:37 ry37 Exp $
 *
 * @author  Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
 * @since   2008-02-21
 */ 

// execute init() callback function when document is loaded
jQuery(document).ready(init);



/**
 * jQuery page initialization when document is ready
 *
 * @param       void
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-02-27
 */
function init() {
		
	// on document load: show only scale price for quantity 1 (default), select quantity 1 in scaleSelector and collect possibly existent form validation error messages
	hideScalePriceBlocks(1);
	jQuery('#scaleSelector')
		.children('*:first')
		.attr('selected', 'selected');
	jQuery('.tx_ptgsaadmin_scalePriceElem')
		.siblings('span')
		.clone()
		.append('<br />')
		.insertBefore(jQuery('#scaleSelector'));
	
	// on scaleSelector change: show only scale price for selected quantity
	jQuery('#scaleSelector').change(
		function() {
			//alert('Code executed on scaleSelector change');
			hideScalePriceBlocks(jQuery('#scaleSelector').val());
		}
	);
	
	// on quantityScaleNewButton click: show only "new" scale price (quantity 0)
	jQuery('#quantityScaleNewButton').click(
		function() {
			//alert('Code executed on quantityScaleNewButton click');
			var clonedOption;
			var newQuantity = jQuery('.tx_ptgsaadmin_newQuantityScale').val();
			
			if (checkNewScalePriceQuantity(newQuantity) == true) {
				
				// create and display new scale price form block
				createNewScalePriceBlock(newQuantity);
				hideScalePriceBlocks(newQuantity);
				
				// create new selector option
				jQuery('#scaleSelector')
					.children('*:last')
					.clone()
					.val(newQuantity)
					.attr('selected', 'selected')
					.text(newQuantity)
					.insertAfter(jQuery('#scaleSelector').children('*:last'));
					
				// empty scale input
				jQuery('.tx_ptgsaadmin_newQuantityScale')
					.val('');
					
			} else {
				alert('Invalid quantity!');  // TODO: use TYPO3 locallang for this
			}
		}
	);
	
	// on quantityScaleDeleteButton click:set "quantityScaleDeleted" flag for selected quantity & remove from scaleSelector option list
	jQuery('#quantityScaleDeleteButton').click(
		function() {

			//alert('Code executed on quantityScaleDeleteButton click');
			var selectedQuantity = jQuery('#scaleSelector').val();
			console.info('Deleting ' + selectedQuantity);
			if (selectedQuantity > 1) {
				// remove selected qty / mark as deleted (hidden input)
				jQuery('#quantityScaleDeleted_' + selectedQuantity).val('1');
				jQuery('#scaleSelector')
					.children('*:selected')
					.remove();
				hideSingleScalePriceBlock(selectedQuantity)
			}
		}
	);
	
}

/**
 * Checks if a requested new scale price qty is valid
 *
 * @param       integer		quantity of new price scale to check
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
 * @since       2008-02-28
 */
function checkNewScalePriceQuantity(newQuantity) {
	
	var isValid = false;
	
	if (!isNaN(newQuantity) && newQuantity > 1) {
		// check if the requested quantity already exists
		if (jQuery('#scalePrice_' + newQuantity).size() == 0) {
			isValid = true;
		}
	}
	
	return isValid;
	
}

/**
 * Creates a new scale price form elements block for the given quantity
 *
 * @param       integer		quantity of new price scale to create
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>, Fabrizio Branca <branca@punkt.de>
 * @since       2008-02-27
 */
function createNewScalePriceBlock(newQuantity) {
	
	var baseDomElement = jQuery('#scalePrice_NEW_QTY').parent().parent(); // get base DOM element to hide (<tr>)
	var clonedElement;
	var clonedHidden;
	
	// clone visible form fields block	
	for (var i=1; i<=7; i++) {
		if (i == 1) {
			clonedElement = baseDomElement
				.clone()
				.insertBefore(baseDomElement);
		} else {
			clonedElement = baseDomElement
				.clone()
				.insertAfter(clonedElement);
		}		
		clonedElement
			.html(clonedElement.html().replace(/NEW_QTY/g, newQuantity));
		baseDomElement = baseDomElement.next();
	}
	
	// clone deleted flag (hidden input) -  TODO: to be improved :)	
	clonedHidden = jQuery('#quantityScaleDeleted_NEW_QTY')
					.clone()
					.insertAfter(jQuery('#quantityScaleDeleted_NEW_QTY'));
	clonedHidden.attr('id', 'quantityScaleDeleted_' + newQuantity);
	clonedHidden.attr('name', jQuery('#quantityScaleDeleted_NEW_QTY').attr('name').replace(/NEW_QTY/g, newQuantity));
	
	// console.info(clonedElement); // Firebug dev ouptput (FF only)
	
}

/**
 * Hides all scale price form elements blocks except the one with the quantity given in the 1st param
 *
 * @param       integer		quantity not to hide (use 0 to hide all)
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-02-27
 */
function hideScalePriceBlocks(exceptQty) {
	
	jQuery('.tx_ptgsaadmin_scalePriceElem')
		.parent()
		.parent()
		.hide(); // get base DOM element to hide (<tr>)
		
	if (exceptQty > 0) {
		showSingleScalePriceBlock(exceptQty);
	}
	
}
    
/**
 * Hides all scale price form elements blocks
 *
 * @param       void
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-02-27
 */
function hideAllScalePriceBlocks_OLD() {
	
	var baseDomElement = jQuery('.tx_ptgsaadmin_scalePriceQty')
							.parent()
							.parent(); // get base DOM element to hide (<tr>)
	
	for (var i=1; i<=7; i++) {
		baseDomElement.hide();
		baseDomElement = baseDomElement.next();
	}
	
}
    
/**
 * Hides a single scale price's form elements block
 *
 * @param       integer		quantity of price scale to hide it's form elements
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-02-26
 */
function hideSingleScalePriceBlock(quantityToHide) {
	
	var baseDomElement = jQuery('#scalePrice_' + quantityToHide)
							.parent()
							.parent(); // get base DOM element to hide (<tr>)
	
	for (var i=1; i<=7; i++) {
		baseDomElement.hide();
		baseDomElement = baseDomElement.next();
	}
	
}

/**
 * Shows a single scale price's form elements block
 *
 * @param       integer		quantity of price scale to hide it's form elements
 * @return      void
 * @author      Rainer Kuhn <kuhn@punkt.de>
 * @since       2008-02-26
 */
function showSingleScalePriceBlock(quantityToShow) {
	
	var baseDomElement = jQuery('#scalePrice_' + quantityToShow)
							.parent()
							.parent(); // get base DOM element to hide (<tr>)
	
	for (var i=1; i<=7; i++) {
		baseDomElement.show();
		baseDomElement = baseDomElement.next();
	}
	
}
