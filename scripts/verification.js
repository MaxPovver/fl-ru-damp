
/**
 * Обработка событий попапа верификации
 * 
 * @type Class
 */
var VerificationPopup = new Class({

    quick_window: null,
    quick_window_open: false,
    quick_window_timer: null,
    error_window_blocked: 'Браузер заблокировал всплывающее окно верификации. Добавьте исключение и попробуйте ещё раз.',

    initialize: function()
    {

        //Показываем по необходимости попап 
        //верификации после перезагрузки страницы
        var popup = $('quick_ver_window');
        if (popup) {
            if (popup.get('data-show')) {
                quickVerShow();
            }
        }
        
    },
    
    
    
    openWindowWM: function(url)
    {
        this.quick_window = window.open(url);
        this.quick_window_open = (typeof this.quick_window !== "undefined")?true:false;
        this.setTimerCheckVerify('wm');
    },
    
    
    /**
     * Окрыть в новом окне верификацию по банковской карте
     */
    openWindowYandexKassaAC: function(form)
    {
        var element = new Element('div').set('html', form);
        $('quick_ver_main').adopt(element);
        var form = element.getElement('form');
        if (form) {
            form.set('target','paymentWindowYandexKassaAC');
            this.quick_window = window.open('about:blank', 'paymentWindowYandexKassaAC');
            this.quick_window_open = (typeof this.quick_window !== "undefined")?true:false;
            form.submit();
            this.setTimerCheckVerify('card');
        }
    },
            
            
    setTimerCheckVerify: function(type)
    {
        var _this = this;
        
        if (this.quick_window_timer) {
            clearInterval(this.quick_window_timer);
        }
        
        this.quick_window_timer = setInterval(function() {
            if (typeof _this.quick_window === "undefined" && 
                !_this.quick_window_open) {
                    _this.showError(_this.error_window_blocked);
                    clearInterval(_this.quick_window_timer);
                } else if (_this.quick_window.closed) {
                    xajax_checkIsVerify(
                        $('quick_ver_f_fname').get('value'), 
                        $('quick_ver_f_lname').get('value'), 
                        type);

                    clearInterval(_this.quick_window_timer);
            }
        }, 100);         
    },
            
    
    /**
     * В попапе показываем сообщение об ошибке
     */
    showError: function(message)
    {
        if (message.length) {
            $('quick_ver_error_txt_1').set('html', message);
        }
        
        $('quick_ver_error_1').removeClass('b-layout_hide');
        $('quick_ver_main').removeClass('b-layout_waiting');
        $('quick_ver_waiting_1').addClass('b-layout_hide');
        $('quick_ver_block_1').show();
        $('quick_ver_block_2').hide();
    }
    
});

window.addEvent('domready', function() {
    window.verification_popup = new VerificationPopup();
});


//------------------------------------------------------------------------------
// Код ниже вынес из шаблона попап верификации.
// Постепенно перерабатываем его.
//------------------------------------------------------------------------------

var quickwindow = null;

function quickVerShow() {
  $('quick_ver_main').removeClass('b-layout_waiting');
  $('quick_ver_waiting_1').addClass('b-layout_hide');

  $('quick_ver_window').removeClass('b-shadow_hide');
  $$('.b-shadow_center').each(function(popup_elm) { var winSize = $(document).getSize(); var elemSize = popup_elm.getSize(); popup_elm.setPosition({x: (winSize.x - elemSize.x) / 2, y: (winSize.y - elemSize.y) / 2})});
  quickVerCheckFIO();
}

function quickVerStartWebmoney(step, obj) 
{
  if(obj!=null) { if(obj.hasClass('b-button_disabled')) return false; }
  $('quick_ver_f_lname').set('disabled', true);
  $('quick_ver_f_fname').set('disabled', true);
  
  switch(step) {
    case 1:
      $('quick_ver_error_1').addClass('b-layout_hide');
      $('quick_ver_block_1').hide();
      $('quick_ver_block_2').show();
      break;
      
    case 2:
      $('quick_ver_main').addClass('b-layout_waiting');
      $('quick_ver_waiting_1').removeClass('b-layout_hide');
      $('quick_ver_waiting_1_txt').set('html', 'WebMoney');
      xajax_checkWebmoneyWMID($('quick_ver_f_wmid').get('value'));
     break;
  }
}

function quickVerStartYandex(step, obj) {
    
  if (typeof _YD_URI_AUTH === "undefined") {
      return false;
  }
    
  if(obj.hasClass('b-button_disabled')) return false;
  $('quick_ver_f_lname').set('disabled', true);
  $('quick_ver_f_fname').set('disabled', true);
  switch(step) {
    case 1:
      xajax_storeFIO( $('quick_ver_f_fname').get('value'), $('quick_ver_f_lname').get('value') );
      $('quick_ver_main').addClass('b-layout_waiting');
      $('quick_ver_waiting_1').removeClass('b-layout_hide');
      $('quick_ver_waiting_1_txt').set('html', 'Яндекс деньги');
      quickwindow = window.open(_YD_URI_AUTH + encodeURIComponent("&fname=" + $('quick_ver_f_fname').get('value') + "&lname="+$('quick_ver_f_lname').get('value')));
      var new_interval = setInterval(function() {
        if(quickwindow.closed) {
          xajax_checkIsVerify($('quick_ver_f_fname').get('value'), $('quick_ver_f_lname').get('value'), 'yd');
          clearInterval(new_interval);
        }
      }, 100);
      break;
  }
}

function quickVerStartYandexKassaAC(obj) 
{
  if(obj.hasClass('b-button_disabled')) return false;
  $('quick_ver_f_lname').set('disabled', true);
  $('quick_ver_f_fname').set('disabled', true);
  
  $('quick_ver_main').addClass('b-layout_waiting');
  $('quick_ver_waiting_1').removeClass('b-layout_hide');
  $('quick_ver_waiting_1_txt').set('html', 'банковскую карту');
  
  xajax_quickYandexKassaAC($('quick_ver_f_fname').get('value'), $('quick_ver_f_lname').get('value'));
}

function quickVerCheckFIO() {
  if($('quick_ver_f_lname').get('value').length>=2 && $('quick_ver_f_fname').get('value').length>=2) {
    $('quick_ver_block_1').getChildren().removeClass('b-button_disabled');
  } else {
    $('quick_ver_block_1').getChildren().addClass('b-button_disabled');
  }
}

function quickVerCheckLetterOnly( evt ) {
      evt = ( evt ) ? evt : ( ( window.event ) ? event : null );        
        if(
                ( evt.keyCode < 97 || evt.keyCode > 122 ) &&
                ( evt.keyCode < 1040 || evt.keyCode > 1103 ) &&
                evt.keyCode != 1105 && evt.keyCode != 32 && evt.keyCode != 1025
        ) {
                evt.CancelBubble = true;
                evt.returnValue = false;
                return false;
        }
        quickVerCheckFIO();
        return true;                
}

function quickVerCheckNumOnly( evt ) {
      evt = ( evt ) ? evt : ( ( window.event ) ? event : null );        
        if(
                ( evt.keyCode < 48 || evt.keyCode > 57 ) 
        ) {
                evt.CancelBubble = true;
                evt.returnValue = false;
                return false;
        }

        return true;                
}