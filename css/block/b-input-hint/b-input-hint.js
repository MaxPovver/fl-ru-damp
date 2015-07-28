var initHideInput = function() {
		$$(".b-input-hint__label").addEvent("click", function() {
				this.addClass("b-input-hint__label_hide");
                if(this.getNext(".b-input") != undefined) {
                    this.getNext(".b-input").getChildren(".b-input__text").setStyle("color","#000");	
                    this.getNext(".b-input").getChildren(".b-input__text").addEvent("blur", function() {
                        if(!(this.get('value'))){
                        this.getParent(".b-input").getPrevious(".b-input-hint__label").removeClass("b-input-hint__label_hide");	
                        this.setStyle("color","#fff");	
                        }
                    });
				}
                if(this.getNext(".b-combo__input") != undefined) {
                    this.getNext(".b-combo__input").getChildren(".b-combo__input-text ").setStyle("color","#000");	
                    this.getNext(".b-combo__input").getChildren(".b-combo__input-text ").addEvent("blur", function() {
                        if(!(this.get('value')) && !this.hasClass('b-combo__input_nohintblur')){
                        this.getParent(".b-combo__input").getPrevious(".b-input-hint__label").removeClass("b-input-hint__label_hide");	
                        this.setStyle("color","#fff");	
                        }
                    });
                }
			});
		$$(".b-input-hint .b-input__text",".b-input-hint .b-combo__input-text").addEvent("click", function() {
				this.getParent('.b-input-hint').getChildren('.b-input-hint__label').addClass("b-input-hint__label_hide");
				this.setStyle("color","#000");	
				this.addEvent("blur", function() {
                    var temp;
					if(!(this.get('value')) && !this.hasClass('b-combo__input_nohintblur')){
					if (temp = this.getParent(".b-combo__input").getPrevious(".b-input-hint__label")) {
                        temp.removeClass("b-input-hint__label_hide");
                    }
					this.setStyle("color","#fff");	
					}
				});
			});
}
window.addEvent('domready', initHideInput);


		
