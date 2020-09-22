/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

;(function ($) {
	$(window).on( 'load', function () {
		
		if (window.prestashop !== undefined) {
			
			prestashop.on('updateCart', function (event) {
				
				console.log('EVENT: updateCart');
				
				if (!event) {
					return;
				}
				
				var productId, productValue;
				
				if (event.resp && event.resp.success) {
					
					var response = event.resp;
					productId = response.id_product;
					var cartProducts = response.cart && response.cart.products ? response.cart.products : [];
					
					if (!productId || !cartProducts || !cartProducts.length) {
						console.log('updateCart: no expected data: ', {productId: productId, cartProducts: cartProducts});
						return;
					}
					
					var product = getProductById(productId, cartProducts);
					productValue = product.price_wt !== undefined ? product.price_wt : false;
					
					callAdscaleAddToCart(productId, productValue);
					
				} else {  // ps 1.7.0 -1.7.2
					
					console.log('[PrestaShop 1.7][<1.7.3]');
					
					var reason = event.reason;
					if (!reason) {
						return;
					}
					productId = reason.idProduct;
					console.log('productId', productId);
					// TODO: Need a solution
					
				}
				
				
			});
			
		} else {   // presta 1.6
			
			console.log('[PrestaShop 1.6]');
			
			//for every 'add' buttons...
			$(document).on('click', '.ajax_add_to_cart_button', function (e) {
				e.preventDefault();
				var productId = parseInt($(this).data('id-product'));
				var minimalQuantity = parseInt($(this).data('minimal_quantity'));
				var quantity = minimalQuantity ? minimalQuantity : 1;
				var callerElement = this;
				var priceText = $(callerElement).closest('.product-container').find('.price.product-price').text();
				var price = getPriceFromText(priceText);
				var productValue = price ? price : 0;
				
				console.log('[1.6] ADD_TO_CARD(every):', {productId: productId, quantity: quantity, productValue: productValue, callerElement: callerElement});
				
				callAdscaleAddToCart(productId, productValue);
			});
			
			//for product page 'add' button...
			$(document).on('click', '#add_to_cart button', function (e) {
				e.preventDefault();
				var productId = $('#product_page_product_id').val();
				var quantity = $('#quantity_wanted').val() || 1;
				var callerElement = this;
				var priceText = $('#our_price_display').text();
				var price = getPriceFromText(priceText);
				var productValue = price ? price : 0;
				
				console.log('[1.6] ADD_TO_CARD(product page):', {productId: productId, quantity: quantity, productValue: productValue, callerElement: callerElement});
				
				callAdscaleAddToCart(productId, productValue);
			});
			
			
		}
		
		
		function callAdscaleAddToCart(productId, productValue) {
			console.log('productId: ', productId);
			console.log('productValue: ', productValue);
			
			if (!productId) {
				console.log('No productId !');
				return;
			}
			
			if (!productValue) {
				console.log('No productValue !');
				return;
			}
			
			if (isFunction(window.adscaleAddToCart)) {
				adscaleAddToCart(productId, productValue);
			} else {
				console.log('func adscaleAddToCart not found!');
			}
		}
		
		
		function getProductById(id, products) {
			for (var i = products.length - 1; i >= 0; --i) {
				if (+products[i].id_product === +id) {
					return products[i];
				}
			}
			return {};
		}
		
		
		function isFunction(functionToCheck) {
			return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
		}
		
		
		function getPriceFromText(priceText) {
			return parseFloat(priceText.replace(' ', '').replace(/[^.0-9\s]/g, ''));
		}
		
		
	});
})(jQuery);