;(
	function ( $, window, document, undefined ) {
		
		var pluginName = 'svgInject';
		
		// The actual plugin constructor
		
		function Plugin( element, options ) {
			this.element = element;
			this._name   = pluginName;
			this.init();
		}
		
		Plugin.prototype = {
			init   : function () {
				$( this.element ).css( 'visibility', 'hidden' );
				this.swapSVG( this.element );
			},
			swapSVG: function ( el ) {
				
				// This technique is based in part on:
				// http://www.snippetlib.com/jquery/replace_all_svg_images_with_inline_svg
				var imgURL   = $( el ).attr( 'src' );
				var imgID    = $( el ).attr( 'id' );
				var imgClass = $( el ).attr( 'class' );
				var imgData  = $( el ).clone( true ).data();
				
				var dimensions = {
					w: $( el ).attr( 'width' ),
					h: $( el ).attr( 'height' )
				};
				
				$.get( imgURL, function ( data ) {
					
					var svg = $( data ).find( 'svg' );
					if ( typeof imgID !== undefined ) {
						svg = svg.attr( 'id', imgID );
					}
					
					if ( typeof imgClass !== undefined ) {
						var cls = (
							          svg.attr( 'class' ) !== undefined
						          ) ? svg.attr( 'class' ) : '';
						svg     = svg.attr( 'class', imgClass + ' ' + cls + ' replaced-svg' );
					}
					
					// copy all the data elements from the img to the svg
					$.each( imgData, function ( name, value ) {
						//svg.data(name, value);
						//jQuery.data(svg, name, value);
						//svg[0].dataset[name] = value;
						svg[ 0 ].setAttribute( 'data-' + name, value );
					} );
					
					svg = svg.removeAttr( 'xmlns:a' );
					
					//Get original dimensions of SVG file to use as foundation for scaling based on img tag dimensions
					var ow = parseFloat( svg.attr( 'width' ) );
					var oh = parseFloat( svg.attr( 'height' ) );
					
					//Scale absolutely if both width and height attributes exist
					if ( dimensions.w && dimensions.h ) {
						$( svg ).attr( 'width', dimensions.w );
						$( svg ).attr( 'height', dimensions.h );
					}
					//Scale proportionally based on width
					else if ( dimensions.w ) {
						$( svg ).attr( 'width', dimensions.w );
						$( svg ).attr( 'height', (
							                         oh / ow
						                         ) * dimensions.w );
					}
					//Scale proportionally based on height
					else if ( dimensions.h ) {
						$( svg ).attr( 'height', dimensions.h );
						$( svg ).attr( 'width', (
							                        ow / oh
						                        ) * dimensions.h );
					}
					
					$( el ).replaceWith( svg );
					
					//Wrap all SVG-associated JS within a function and run it - this will need some more work
					var js = new Function( svg.find( 'script' ).text() );
					js();
					
				} );
			}
		};
		
		$.fn[ pluginName ] = function ( options ) {
			return this.each( function () {
				if ( !$.data( this, 'plugin_' + pluginName ) ) {
					$.data( this, 'plugin_' + pluginName, new Plugin( this, options ) );
				}
			} );
		};
		
	}
)( jQuery, window, document );
