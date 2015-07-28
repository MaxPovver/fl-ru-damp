            					<table id="attachedfiles_table" class="b-icon-layout__table" cellpadding="0" cellspacing="0" border="0">
					                <tr id="attachedfiles_template" style="display:none" class="b-icon-layout__tr">
					                    <td class="b-icon-layout__icon"><i class="b-icon"></i></td>
					                    <td class="b-icon-layout__files"><a href="javascript:void(0)" class="b-icon-layout__link">&nbsp;</a>&nbsp;</td>
					                    <td class="b-icon-layout__operate b-icon-layout__operate_padleft_10"><a href="javascript:void(0)" class="b-button b-button_m_delete"></a></td>
					                </tr>
					            </table>
					            <div id='attachedfiles_error' style='display: none;'>
					                <table class='b-icon-layout wdh100'>
					                    <tr>
					                        <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>
					                        <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>
					                        <td class='b-icon-layout__operate'><a id="attachedfiles_hide_error" class='b-icon-layout__link b-icon-layout__link_dot_666' href='javascript:void(0)' onClick="attachedFiles.hideError(); return false;">Скрыть</a></td>
					                    </tr>
					                </table>
					            </div>
					            <div id='attachedfiles_uploadingfile' style='display:none'>
					                <table class='b-icon-layout wdh100'>
					                    <tr>
					                        <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-white.gif' alt='' width='24' height='24'></td>
					                        <td class='b-icon-layout__files'>Идет загрузка файла…</td>
					                        <td class='b-icon-layout__size'>&nbsp;</td>
					                        <td class='b-icon-layout__operate'>&nbsp;</td>
					                    </tr>
					                </table>
					            </div>
					            <div id='attachedfiles_deletingfile' style='display: none;'>
					                <table class='b-icon-layout wdh100'>
					                    <tr>
					                        <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-white.gif' alt='' width='24' height='24'></td>
					                        <td class='b-icon-layout__files'>Идет удаление файла…</td>
					                        <td class='b-icon-layout__size'>&nbsp;</td>
					                        <td class='b-icon-layout__operate'>&nbsp;</td>
					                    </tr>
					                </table>
					            </div>
					            <div class='b-fon__item' id='attachedfiles_error' style='display: none;'>
					                <table class='b-icon-layout wdh100'>
					                    <tr>
					                        <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>
					                        <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>
					                        <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_666' href='#' onClick='attachedFiles.hideError(); return false;'>Скрыть</a></td>
					                    </tr>
					                </table>
					            </div>
					            <table id="wd_file_add" class="b-file_layout" cellpadding="0" cellspacing="0" border="0">
					                <tr>
					                    <td class="b-file__button">            
					                        <div class="b-file__wrap" id="attachedfiles_file_div">
					                            <input id="attachedfiles_file" name='attachedfiles_file' class="b-file__input" type="file" />
					                            <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_grey">Прикрепить файл</a>
					                        </div>
					                    </td>
					                    <td class="b-file__text">
					                        <div class="b-filter">
												<div class="b-file__descript b-file__descript_padtop_10 b-file__descript_color_41">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
					                        </div>
					                    </td>
					                </tr>
					            </table>


