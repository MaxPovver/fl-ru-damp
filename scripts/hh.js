window.addEvent( 'domready', function(){

	function getUserType()
	{ // определяет, для какого типа пользователя отображается сейчас блок быстрого доступа
	  // emp - работодатель
	  // frl - фрилансер
		return $$( '.fast-switch .selected' ).getProperty('class' ).toString().replace( 'selected', '' ).replace( ' ', '' ).replace( 'fs-', '' )
	}

	function switchFunc()
	{ // переключает класс в соответствии с текущей видимой функцией
		$$( '.fast-info' ).setProperty( 'class',
			'fast-info fai-' + $$( '.fast-funcs-' + getUserType() + ' .selected' ).getProperty( 'class' ).toString().replace( 'ff-', '' ).replace( ' ', '').replace( 'selected', '' )
		);
	}

	// если не залогинились, определяем начальную отображаемую функцию
	if ( ! $$( '.logged-in' ).length )
		switchFunc();

	// переключения между блоками быстрого доступа фрилансерским и работодателя

	$$( '.fast-switch li span span' ).addEvent( 'click', function(){

		var li = this.getParent().getParent();

		// если данный пункт не выбран
		if ( ! li.hasClass( 'selected' ) || $$( '.fast-content' ).getStyle( 'display' ) == 'none' ) {

			// переключаем класс у блока быстрого доступа
			$$( '.fast' ).removeClass('fast-emp').removeClass('fast-frl').addClass(
				'fast-' + li.getProperty( 'class' ).replace( 'fs-', '' )
			);

			// делаем данный пункт меню выбранным
			li.getSiblings().removeClass( 'selected' );
			li.addClass( 'selected' );

			// переключаем класс отображаемой функции
			switchFunc();

			// отображаем контент блока (на случай, если он был скрыт)
			$$( '.fast-content' ).setStyle( 'display', 'block' );

		} else {

			// скрываем контент блока
			$$( '.fast-content' ).setStyle( 'display', 'none' );

		}

	});

	// переключение функций в блоке быстрого доступа

	$$( '.fast-funcs li' ).addEvent( 'click', function(){

		// если данная функция не выбрана
		if ( ! this.hasClass( 'selected' ) ) {

			// фрилансер или работодатель?
			var userType = getUserType();

			// какая функция?
			var newFunc = this.getProperty( 'class' ).replace( 'ff-', '' );

			// переключаем соответствующий блок
			$$( '.fi-layers-' + userType + ' .visible' ).removeClass( 'visible' );
			$$( '.fil-' + newFunc ).addClass( 'visible' );

			// делаем триггер выбранным
			$$( '.fast-funcs-' + userType + ' .selected' ).removeClass( 'selected' );
			this.addClass( 'selected' );

			// переключаем класс отображаемой функции
			switchFunc();

		}

	});

	// скрытие блока быстрого доступа

	$$( '.fast-close' ).addEvent( 'click', function(){

		$$( '.fast' ).setStyle( 'display', 'none' );

	});

	// резиним футер

	var footerHeight = parseInt( $$( '.footer' ).getHeight() );
	$$( '.footer' ).setStyle( 'height', footerHeight );
	$$( '.footer-fantom' ).setStyle( 'height', footerHeight );
	$$( '.footer' ).setStyle( 'margin-top', '-' + ( footerHeight + 17 ) + 'px' );

	// переключение типа проектов

	$$( '.project-type em' ).addEvent( 'click', function(){

		var li = this.getParent( 'li' );

		// если этот пункт не выбран
		if ( ! li.hasClass( 'selected' ) ) {

			// выбираем этот пункт
			$$( '.project-type .selected' ).removeClass( 'selected' );
			li.addClass( 'selected' );

			// отображаем соответствующий список проектов
			$$( '.project-list.visible' ).removeClass( 'visible' )
			var index = $$( '.project-type li' ).indexOf( li );
			$$( '.project-list')[index].addClass( 'visible' )

		}
	});

	// открытие-закрытие формы входа

	$$( '.trigger-login' ).addEvent( 'click', function(){
		$$( '.login-form' ).toggleClass( 'lf-hide' );
	})

	// раскрытие подменю

	$$( '.logged-in .catalog-list ul em' ).addEvent( 'click', function(){

		var submenu = this.getNext( 'ul' );
		var visible = submenu.hasClass( 'visible' );

		$$( '.catalog-list ul' ).removeClass( 'visible' );
		submenu.toggleClass( 'visible', ! visible );

	});


});
















