<?php
if(!defined('IN_STDF')) 
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Разметка страницы с правым сайдбаром и табличной разметкой для фона
 */

?>
<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
    <tbody>
       <tr class="b-layout__tr">
          <td class="b-layout__td b-layout__td_padright_20">
              <?php echo $content ?>
          </td>
          <td class="b-layout__td b-layout__td_width_270 b-fon_bg_fa b-layout__td_pad_10 b-fon__puble">
              <?php echo $this->renderClip('sidebar') ?>
          </td>
       </tr>
    </tbody>
 </table>