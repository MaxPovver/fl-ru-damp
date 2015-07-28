/* 
 * Сюда выношу функции относящиеся с авторизации которые невозможно использовать там где они описаны
 * Новый функционал сюда не писать!
 */

/**
 * @todo: копия без фигни из /scripts/wizard/wizard.js
 * 
 * меняет type для поля пароль (text/password)
 * @param string id - id элемента input для ввода пароля
 */
function show_password(id) 
{
    // добавил возможность задавать свой id (на случай если на странице несколько паролей)
    var v = id ? $(id) : $('reg_password');
    if (!v) return;
    
    if (Browser.ie) {
        if(v.type == 'password') {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'text',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        } else {
            var inputText = new Element('input', {'class'  :'b-combo__input-text', 
                                                'value'  : v.value, 
                                                'name'   : 'password',
                                                'size'   : '80',
                                                'type'   : 'password',
                                                'id'     : 'reg_password'});
            var parent = v.getParent();
            v.dispose();
            parent.adopt(inputText);
        }
    } else {
        if(v.getProperty('type') == 'password') {
            v.setProperty('type', 'text');
        } else {
            v.setProperty('type', 'password');
        }
    }
}