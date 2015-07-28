window.addEvent('domready',
	function() {
		if($chk($('l-switch'))){
			$('l-switch').addEvents({
				'click': function(){
					$('b-switch').setStyle('display', 'block');
					return false;
				}
			});
		}
		if($chk($('l-cancel'))){
			$('l-cancel').addEvents({
				'click': function(){
					$('b-switch').setStyle('display', 'none');
					return false;
				}
			});
		}
		if($chk($('lnk-ftitle'))){
			$('ftitle').set('text', $('lnk-ftitle').getProperty('title'));
			$('lnk-ftitle').removeProperty('title');
			$('lnk-ftitle').addEvents({
					'mouseenter': function(){
						$('ftitle').setStyle('display', 'block');
					},
					'mouseleave': function(){
						$('ftitle').setStyle('display', 'none');
					},
					'mousemove': function(event){
						$('ftitle').setStyles({
							'top': event.client.y-25+'px',
							'left': event.client.x-380+'px'
						})
					}
			});
		}

		// выбор типа бюджета

		function moveSlider( element, x, slideTime, parent, state, newState, callback )
		{
			new Fx.Morph( element, {
				duration: slideTime,
				transition: Fx.Transitions.Sine.easeOut,
				onComplete: function() {
				 	if ( newState != state ) {
						parent.removeClass("budget-low").removeClass("budget-middle").removeClass("budget-high");
						parent.addClass( "budget-" + newState );
						var newClass;
                        var btype;
						switch ( newState ) {
							case "low":
								newClass = "p";
                                btype = 1;
								break;
							case "middle":
								newClass = "o";
                                btype = 2;
								break;
							case "high":
								newClass = "lg";
                                btype = 3;
								break;
						}
						parent.getElements(
							".fl-form"
						).removeClass(
							"fl-form-p"
						).removeClass(
							"fl-form-o"
						).removeClass(
							"fl-form-lg"
						).addClass(
							"fl-form-" + newClass
						);
                        if(isBudgetSliderChangePrice == 1 || $("f3").get("value").length === 0) {
    						$("f3").set( "value", getBudgetFromFM($( "hb-" + newState ).get( "text" )) );
                        }
                        isBudgetSliderChangePrice = 1;
                        $("fbudget_type").set("value", btype);
						callback();
					}
                    isBudgetSliderChangePrice = 1;
				}
			}).start({
				"left": x
			});
		}

		$$( ".budget-select" ).each( function( el ) {

			var slider = el.getElement( ".budget-pointer" );
			var max = slider.getParent().getWidth() - slider.getWidth();
			var state = "middle";

			slider.setStyle( "left", max / 2 );

			new Drag.Move( slider, {

				container: slider.getParent(),
                precalculate: true,

				onDrop: function( element, droppable ) {

					var x = parseInt( element.getStyle( "left" ) );
					var slideTime = ( x == 0 || x == max / 2 || x == max ? 0 : 250);
					var newState;

					if ( x < max / 4 ) {
						x = 0;
						newState = "low";
					}
					else
						if ( x > 3 * max / 4 ) {
							x = max;
							newState = "high";
						}
						else {
							x = max / 2;
							newState = "middle";
						}

					moveSlider( element, x, slideTime, el, state, newState, function() {
						state = newState;
					});

				}

			});

			el.getElements( ".budget-point" ).addEvent( "click", function() {

				var x;
				var newState;

				if ( $( this ).hasClass( "budget-point-l" ) ) {
					x = 0;
					newState = "low";
				}
				else if ( $( this ).hasClass( "budget-point-m" ) ) {
					x = max / 2;
					newState = "middle";
				}
				else if ( $( this ).hasClass( "budget-point-h" ) ) {
					x = max;
					newState = "high";
				}

				moveSlider( slider, x, 250, el, state, newState, function() {
					state = newState;
				});

			});

			el.getElements( ".budget-levels span em" ).addEvent( "click", function() {

				var className = $( this ).getParent().getProperty( "class" ).replace( "budget-", "budget-point-" );
				$$( "." + className ).fireEvent( "click" );

			});
			//$("f3").set( "value", $( "hb-middle" ).get( "text" ) );

		});


	});
function logout() {var f=document.getElementById('___logout_frm___');if(f)f.submit()}
