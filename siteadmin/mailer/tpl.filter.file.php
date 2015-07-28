<div id="extra_recievers" class="b-layout__inner b-layout__inner_bordtop_c6 b-layout__inner_bordbot_c6 b-layout__inner_margbot_30 b-layout__inner_padtb_20 <?= ($message['filter_file'] ? "":"b-layout__inner_hide")?>">
    <?php if ($message['filter_file']) { ?>
    <div class="b-layout__txt_padbot_20 b-layout__txt_margleft_130 b-layout__txt_fontsize_13 b-layout__txt_bold">
        <a href="<?=WDCPREFIX.'/mailer/'.$message['filter_file']?>" target="_blank">Загруженный файл</a>
	</div>
    <?php } ?>
    <div class="b-layout__txt_margleft_130">
		<input type="file" name="extra_recievers_file" value="Загрузить файл" /><br/>
            <span style="color:red;font-size:8pt" id="uploadError"></span>
	</div>
	  
    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_margleft_130">
		Формат файла .csv, структура - 5 колонок данных (ID;Имя;Фамилия;Логин;Рег.почта)
    </div>
	
</div><!-- b-layout__inner -->