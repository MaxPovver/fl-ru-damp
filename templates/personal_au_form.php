                    <div class="b-dropdown b-identification-dropdown b-identification-dropdown-hide" data-antiuser="true" data-dropdown="true" data-dropdown-descriptor="identification">
                        <a href="/registration/" class="b-dropdown-opener" data-dropdown-opener="true" title="Перейти в аккаунт работодателя">Перейти в аккаунт работодателя</a>
                        <div class="b-dropdown-concealment" data-dropdown-concealment="true">
                            
                            <form class="b-form b-authorization-form g-cleared" method="post" action="<?= $host ?>/" onsubmit="return Bar_Ext.antiuserSubmit(this,'<?= $anti_login ?>');">
                                
                                <input type="hidden" name="redirect" value="<?= urlencode($_SERVER['REQUEST_URI']);?>" />
                                
                                <section class="b-form-section b-form-login-section">
                                    <div class="b-text-field">
                                        <input type="text" name="a_login" value="<?= ($anti_login != 'Логин')?$anti_login:'' ?>" placeholder="Логин" class="b-text-field-entity"  size="80" />
                                    </div>
                                </section>

                                <section class="b-form-section b-form-password-section">
                                    <div class="b-text-field">
                                        <input type="password" name="passwd" placeholder="Пароль" class="b-text-field-entity"  size="80" value="<?= ($anti_login != 'Логин')?'******':'' ?>" <?php if($anti_login != 'Логин'): ?>onfocus="if(this.value=='******') {this.value='';}" onblur="if(this.value==''){this.value='******';}"<?php endif; ?> />
                                    </div>
                                </section>

                                <section class="b-form-section b-form-send-section">
                                    <input type="submit" value="Перейти" class="b-medium-button b-green-medium-button"  />
                                </section>

                                <section class="b-form-section b-form-registration-section">
                                    <a data-toggle-action="antiuser" href="javascript:void(0);" class="b-form-cancellation-link" title="Отмена">Отмена</a>
                                </section>
                            </form>

                        </div>
                    </div>