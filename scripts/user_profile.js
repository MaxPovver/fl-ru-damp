
/**
 * ќбработка событий профил€ пользовател€
 * 
 * @type Class
 */
var UserProfile = new Class({
    
    show_contacts_id: 'show_contacts',
    contacts_info_block_id: 'contacts_info_block',
    
    initialize: function()
    {
        //ѕоказываем контакты по запросу
        var contacts_info_block = $(this.contacts_info_block_id);
        var show_contacts = $(this.show_contacts_id);
        
        if (contacts_info_block && 
            show_contacts) {
            
            show_contacts.addEvent('click', function(){
                alert("ќбращаем ваше внимание: при сотрудничестве напр€мую (вне сервиса \"Ѕезопасна€ сделка\") стороны несут все риски самосто€тельно.");
                this.addClass('b-button_disabled');
                var login = this.get('data-login');
                var hash = this.get('data-hash');
                xajax_getContactsInfo(login, hash);
            });
        }
    }
});

window.addEvent('domready', function() {
    window.user_profile = new UserProfile();
});