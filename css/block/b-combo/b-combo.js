window.addEvent('domready', 
function() {
	$$( ".b-combo__input-text" ).addEvents({
		
	//focus на поле ввода
	focus: function (){
			this.getParent('.b-combo__input').addClass('b-combo__input_current');
            
            // �������� ������� ��������� ��������� �� ��������
			if (!this.getNext('.b-combo__label')) return;
            
            this.getNext('.b-combo__label').set('text',this.get('value'));
			//this.getParent('.b-combo__input').getChildren('.b-combo__input-text').set('value','')
			
			//длина блока .b-combo__input
			var input_width = this.getParent('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
					input_width=input_width[0].match(/\d+/gi);
					
			//максимальная длина блока .b-combo__input
			var input_max_width = this.getParent('.b-combo__input').getProperty('class').match(/b-combo__input_max-width_(\d+)/gi);
					input_max_width = input_max_width[0].match(/\d+/gi);
			
			if((parseInt(this.getNext('.b-combo__label').getStyle('width')))>input_width){
				if((parseInt(this.getNext('.b-combo__label').getStyle('width')))>input_max_width){
						this.getParent('.b-combo__input').setStyle('width',input_max_width+"px");
					}
					else{
						this.getParent('.b-combo__input').setStyle('width',parseInt(this.getNext('.b-combo__label').getStyle('width'))+5);
					}
				}
		
	},
	
	//набор текста с клавиатуры
	keyup: function() {
            // �������� ������� ��������� ��������� �� ��������
            if (!this.getNext('.b-combo__label')) return;
            
			this.getNext('.b-combo__label').set('text',this.get('value'));
			
			//длина блока .b-combo__input
			var input_width = this.getParent('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
					input_width = input_width[0].match(/\d+/gi);
					
			//максимальная длина блока .b-combo__input
			var input_max_width = this.getParent('.b-combo__input').getProperty('class').match(/b-combo__input_max-width_(\d+)/gi);
					input_max_width = input_max_width[0].match(/\d+/gi);
					
					
			if(
				// проверяем длину label, и если он шире блока b-combo__input то увеличиваем его
				input_width<=(parseInt(this.getNext('.b-combo__label').getStyle('width')))&&((this.getNext('.b-combo__label').getStyle('width')).toInt())<input_max_width
				){
				this.getParent('.b-combo__input').setStyle('width',this.getNext('.b-combo__label').getStyle('width').toInt());
			}
			//иначе, если label короче блока .b-combo__input устанавливаем ему его начальную ширину
			else if((((this.getNext('.b-combo__label').getStyle('width')).toInt())<=input_width)){
				this.getParent('.b-combo__input').setStyle('width',input_width);
				}
		 },
	
	
		//потеря фокуса после набора в поле ввода
		blur: function() {
			this.getParent('.b-combo__input').removeClass('b-combo__input_current');
            // �������� ������� ��������� ��������� �� ��������
            if (!this.getParent('.b-combo__input').getChildren('.b-combo__input-text') || !this.getNext('.b-combo__label')) return;
            
			this.getParent('.b-combo__input').getChildren('.b-combo__input-text').set('value',this.getNext('.b-combo__label').get('text'));
			this.getNext('.b-combo__label').set('text',this.get('value'));
				
			var input_width = this.getParent('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
					input_width=input_width[0].match(/\d+/gi);
			
			if((parseInt(this.getNext('.b-combo__label').getStyle('width')))<input_width){
				this.getParent('.b-combo__input').setStyle('width',input_width+'px');
				}
		}
	})
	
var spec //создаем переменную, в которой будем хранить текст выбранный при клике по левой колонке. требуется для сохранения в инпуте только левого значения (с левой колонки) при изменении  правого (из правой колонки).

	// тогглер выпадающего окна и оверлея
	$$('.b-combo__arrow', '.b-combo__arrow-date', '.b-combo__arrow-user').addEvent('click',function(){
	// проверка высоты выпадающего окна (первая колонка) и если оно больше 300пх, то добавляем к нему скролл
		if(parseInt(this.getParent('.b-combo__input').getNext('.b-shadow').getElement('.b-combo__body').getStyle('height'))>300){this.getParent('.b-combo__input').getNext('.b-shadow').getElement('.b-combo__body').addClass('b-combo__body_overflow-x_yes');}
		
		if(this.getParent('.b-combo__input').getNext('.b-shadow').hasClass('b-shadow_hide')){
			this.getParent('.b-combo__input').addClass('b-combo__input_current');
			this.getParent('.b-combo__input').getElement('.b-combo__input-text').addClass('b-combo__input-text_color_a7').focus();
			this.getParent('.b-combo__input').getNext('.b-shadow').removeClass('b-shadow_hide');
			
			//добавляем оверлей
			var overlay=document.createElement('div');
			overlay.className='b-combo__overlay';
			this.getParent('.b-combo').grab(overlay, 'top');
			$$('.b-combo__overlay').addEvent('click',function(){
				//подсветка шрифта
				if(this.getParent('.b-combo').getChildren('.b-combo__input-text_color_a7')){
						this.getParent('.b-combo').getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');
					}
					
				//сохранение данных выбраных пунктов в окошке, если они введены в инпут
				if((this.getParent('.b-combo').getElement('.b-combo__item_active')&&(this.getParent('.b-combo').getElement('.b-combo__label').get('text')==''))){
					this.getParent('.b-combo').getElement('.b-combo__item_active').removeClass('b-combo__item_active');
					this.getParent('.b-combo').getElement('.b-layout__right').addClass('b-layout__right_hide');
					}
				this.getParent('.b-combo').getElement('.b-shadow').addClass('b-shadow_hide');
				this.dispose();
				});
				
				
				
			// динамика внутри выпадающего окошка, клик по левой колонке
			$$('.b-layout__left .b-combo__item-inner').addEvent('click',function(){
				
				if(this.getParent('.b-combo').getElement('.b-combo__input-text').hasClass('b-combo__input-text_color_a7')){
					this.getParent('.b-combo').getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');
				}

				//меняем значение в поле ввода при клике по пунктам меню
				this.getParent('.b-combo').getElement('.b-combo__input-text').setProperty('value', this.get('text')+' →');
				this.getParent('.b-combo').getElement('.b-combo__label').set('text', this.get('text')+' →');
				
				//заносим текст в переменную
				spec = this.getParent('.b-combo').getElement('.b-combo__label').get('text');

				// меняем длину поля ввода
				var input_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
						input_width=input_width[0].match(/\d+/gi);
				//максимальная длина блока .b-combo__input
				var input_max_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_max-width_(\d+)/gi);
						input_max_width = input_max_width[0].match(/\d+/gi);
						
						
				//увеличиваем поле
				if((parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width')))>input_width){
					// если длина больше, чем максимально допустимая для этого блока, то ставим максимально допустимую
					if(parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))>input_max_width){
						this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_max_width +'px');
						}
					else{
						this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))+5);
					}
				}
				else{
						this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_width+'px');
					}
				
				
				// сама динамика
				this.getParent('.b-combo__list').getChildren('.b-combo__item').removeClass('b-combo__item_active');
				this.getParent('.b-combo__item').addClass('b-combo__item_active');
				this.getParent('.b-layout__table').getElement('.b-layout__right').removeClass('b-layout__right_hide');

					// проверка высоты выпадающего окна (вторая колонка) и если оно больше 300пх, то добавляем к нему скролл
					if(parseInt(this.getParent('.b-shadow').getElement('.b-combo__body').getStyle('height'))>300){this.getParent('.b-shadow').getElement('.b-combo__body').addClass('b-combo__body_overflow-x_yes');}
				
				})
				
				//обрабатываем клик по элементам правой колонки
				$$('.b-layout__right .b-combo__item-inner').addEvent('click',function(){
					
					//меняем правое значения в инпуте и label 
					this.getParent('.b-combo').getElement('.b-combo__input-text').setProperty('value',spec+' '+this.get('text'))
					this.getParent('.b-combo').getElement('.b-combo__label').setProperty('text',spec+' '+this.get('text'))
					
					// меняем длину поля ввода
					var input_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
							input_width=input_width[0].match(/\d+/gi);
					//максимальная длина блока .b-combo__input
					var input_max_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_max-width_(\d+)/gi);
							input_max_width = input_max_width[0].match(/\d+/gi);
							
					//увеличиваем поле
					if((parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width')))>input_width){
						// если длина больше, чем максимально допустимая для этого блока, то ставим максимально допустимую
						if(parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))>input_max_width){
							this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_max_width +'px');
							}
						else{
							this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))+5);
						}
					}
					else{
							this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_width+'px');
						}
					//сворачиваем окошко
					if(this.getParent('.b-combo').getElement('.b-combo__overlay')) {this.getParent('.b-combo').getElement('.b-combo__overlay').dispose();}
					this.getParent('.b-combo').getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');
					this.getParent('.b-combo').getElement('.b-shadow').addClass('b-shadow_hide');
					});
					
					
				//обрабатываем клик по элементам единственной колонки
				$$('.b-layout__one .b-combo__item-inner').addEvent('click',function(){
					
					//меняем  значения в инпуте и label 
					this.getParent('.b-combo').getElement('.b-combo__input-text').setProperty('value',this.get('text'))
					this.getParent('.b-combo').getElement('.b-combo__label').setProperty('text',this.get('text'))
					
					// меняем длину поля ввода
					var input_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
							input_width=input_width[0].match(/\d+/gi);
					//максимальная длина блока .b-combo__input
					var input_max_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_max-width_(\d+)/gi);
							input_max_width = input_max_width[0].match(/\d+/gi);
							
					//увеличиваем поле
					if((parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width')))>input_width){
						// если длина больше, чем максимально допустимая для этого блока, то ставим максимально допустимую
						if(parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))>input_max_width){
							this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_max_width +'px');
							}
						else{
							this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))+5);
						}
					}
					else{
							this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_width+'px');
						}
					//сворачиваем окошко
					if(this.getParent('.b-combo').getElement('.b-combo__overlay')) {this.getParent('.b-combo').getElement('.b-combo__overlay').dispose();}
					this.getParent('.b-combo').getElement('.b-combo__input-text').removeClass('b-combo__input-text_color_a7');
					this.getParent('.b-combo').getElement('.b-shadow').addClass('b-shadow_hide');
					});
				
		}
		else{
			this.getParent('.b-combo__input').getNext('.b-shadow').addClass('b-shadow_hide');
				if(this.getParent('.b-combo').getElement('.b-combo__overlay')) {this.getParent('.b-combo').getElement('.b-combo__overlay').dispose();}
			
				if((this.getParent('.b-combo').getElement('.b-combo__item_active')&&(this.getParent('.b-combo').getElement('.b-combo__label').get('text')==''))){
					this.getParent('.b-combo').getElement('.b-combo__item_active').removeClass('b-combo__item_active');
					this.getParent('.b-combo').getElement('.b-layout__right').addClass('b-layout__right_hide');
					}
			}
	})






	$$( ".b-combo__user" ).addEvents({
		
	//focus на поле ввода
	click: function (){
			this.getParent('.b-combo').getElement('.b-combo__label').set('html',this.get('html'));
			this.getParent('.b-combo').getElement('.b-combo__label').addClass('b-combo__label_show');
			this.getParent('.b-combo').getElement('.b-shadow').addClass('b-shadow_hide');
			this.getParent('.b-combo').getElement('.b-combo__overlay').dispose();
			
			//длина блока .b-combo__input
			var input_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_width_(\d+)/gi);
					input_width=input_width[0].match(/\d+/gi);
					
			//максимальная длина блока .b-combo__input
			var input_max_width = this.getParent('.b-combo').getElement('.b-combo__input').getProperty('class').match(/b-combo__input_max-width_(\d+)/gi);
					input_max_width = input_max_width[0].match(/\d+/gi);
			
			if((parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width')))>input_width){
				if((parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width')))>input_max_width){
						this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',input_max_width+"px");
					}
					else{
						this.getParent('.b-combo').getElement('.b-combo__input').setStyle('width',parseInt(this.getParent('.b-combo').getElement('.b-combo__label').getStyle('width'))+5);
					}
				}
				
			return false;
		
		}
	
	
	})

	
})







