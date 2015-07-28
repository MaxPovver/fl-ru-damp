window.addEvent('domready', function() {
    $$('.button_macbook_contest').addEvent('click', function() {
        ga('send', 'event', 'freelancer', 'button_pro_macbook_june');
    });

    $$('.create_project_button').addEvent('click', function() {
        ga('send', 'event', 'projects', 'create_button_click');
    });

    $$('.create_vacancy_button').addEvent('click', function() {
        ga('send', 'event', 'projects', 'create_landing_vacancy');
    });

    $$('.create_contest_button').addEvent('click', function() {
        ga('send', 'event', 'projects', 'create_langind_contest');
    });

    $$('.create_tu_button').addEvent('click', function() {
        ga('send', 'event', 'services', 'create_button_click');
    });

    $$('.choose_freelancer_button').addEvent('click', function() {
        ga('send', 'event', 'catalog', 'choose_freelancer_click');
    });
    
    $$('.__ga__landing__buy_pro_click').addEvent('click', function() {
        ga('send', 'event', 'landing', 'buy_pro_click');
    });
    
    $$('.__ga__sidebar__add_project').addEvent('click', function() {
        _gaq.push(['_trackPageview', '/virtual/employer/button_project_create']); 
        ga('send', 'pageview', '/virtual/employer/button_project_create'); 
        yaCounter6051055reachGoal('proekt_dobavlen');
    });
    $$('.__ga__sidebar__add_vacancy').addEvent('click', function() {
        _gaq.push(['_trackPageview', '/virtual/employer/button_vacancy_create']); 
        ga('send', 'pageview', '/virtual/employer/button_vacancy_create'); 
    });
    $$('.__ga__sidebar__add_contest').addEvent('click', function() {
        _gaq.push(['_trackPageview', '/virtual/employer/button_competition_create']); 
        ga('send', 'pageview', '/virtual/employer/button_competition_create');
    });
    
    $$('.__ga__commune__new_post_emp').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Employer', 'button_write_post_community']); 
        ga('send', 'event', 'employer', 'button_write_post_community'); 
    });
    
    $$('.__ga__commune__new_post_frl').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Freelance', 'button_write_post_community']); 
        ga('send', 'event', 'freelance', 'button_write_post_community');
    });
    
    if ($('mass_btn_submit')) {
        $('mass_btn_submit').addEvent('click', function() {
            _gaq.push(['_trackEvent', 'User', 'Employer', 'button_moderation_send_sub']); 
            ga('send', 'event', 'employer', 'button_moderation_send_sub');
        });
    }
    
    $$('.__ga__pro__frl_buy').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Freelance', 'button_buy_pro']);
        ga('send', 'event', 'freelance', 'button_buy_pro');
    });
    
    $$('.__ga__pro__emp_buy').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Employer', 'button_buy_pro']); 
        ga('send', 'event', 'employer', 'button_buy_pro');
    });
    
    $$('.__ga__project__candidate').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Employer', 'button_project_candidate']);
        ga('send', 'event', 'employer', 'button_project_candidate');
    });
    
    $$('.__ga__project__performer').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Employer', 'button_project_performer']); 
        ga('send', 'event', 'employer', 'button_project_performer');
    });
    
    $$('.__ga__project__contest_candidate').addEvent('click', function() {
        _gaq.push(['_trackEvent', 'User', 'Employer', 'button_contest_candidate']);
        ga('send', 'event', 'employer', 'button_contest_candidate');
    });    
    
    if ($('btn_spec_change')) {
        $('btn_spec_change').addEvent('click', function() {
            _gaq.push(['_trackPageview', '/virtual/freelance/spec_profile']);
            ga('send', 'pageview', '/virtual/freelance/spec_profile');
        });
    }
    
    /**
     * Обрабатываем указания на GA события
     * Пример указания: <a href="/" data-ga-event="{ec: 'user', ea: 'authorization_started',el: 'vk'}">Ссылка</a>
     */
    var ga_events = $$('[data-ga-event]');
    if (ga_events.length) {
        ga_events.addEvent('click', function() {
            
            if (this.hasClass('b-button_disabled')) {
                return false;
            }
            
            var data = JSON.decode(this.get('data-ga-event'));
            if (typeof data.ec !== "undefined") {
                
                //Обработка специфики
                if (!data.el) {
                    switch (data.ea) {
                        
                        case 'registration_form_edited':
                        case 'registration_captcha_edited':
                        case 'registration_regbutton1_clicked':
                        case 'registration_login_edited':
                        case 'registration_regbutton2_clicked':
                        case 'registration_switcher_used':
                            
                            var checker = $('freelancer');
                            if (checker) {
                                data.el = checker.get('checked')?'freelancer':'customer';
                            } else {
                                var el = $$('[data-ga-el]')[0];
                                if (el) {
                                    data.el = el.get('data-ga-el');
                                } else {
                                    var role_db_id = $('role_db_id');
                                    if (role_db_id) {
                                        data.el = role_db_id.get('value') == 1?'freelancer':'customer';
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    
                    switch (data.ea) {
                        
                        //Если не главная игнорируем события меню
                        case 'main_menu_clicked':
                            if (window.location.pathname !== '/') {
                                data = null;
                            }
                            break;

                    }
                }

                if (data) {
                    ga('send', 'event', data.ec, data.ea, data.el);
                    //console.log(data);
                }
            }            
        });
    }
    
    //Обработчик специально для выпадающего списка смены роли при регистрации через соцсеть
    var role = ComboboxManager.getInput("role");
    if (role) {
        role.b_input.addEvent('bcombochange', function() {
            var el = role.id_input.get('value') == 1?'freelancer':'customer';
            ga('send', 'event', 'user', 'registration_switcher_used', el);
            //console.log(el);
        });
    }
});