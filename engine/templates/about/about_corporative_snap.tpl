<div id='add_comment' style="display:none; width: 600px;">
	<br>
	<h2>Комментировать:</h2>
	<form action="<?=$form_uri?>#new" method="post" enctype="multipart/form-data" name="frm" id="frm" onkeypress="" onSubmit="if (!Locksubmit) { this.btn.value='Подождите'; this.btn.disabled=true; Locksubmit=1; } else { return false; }">
	<table cellpadding="5" style="cell-padding:10px;">
		<tr>
			<td style='width:150px;'>Заголовок:</td>
			<td><input type="text" name="title" id="title" style="width:500px;" value="<?=$$post['title']?>"><br/><br/></td>
		</tr>
		<tr>
			<td>Комментарий:</td>
			<td><textarea style="width:500px;height:200px;" name="msg" id="msg"><?=$$post['msg']?></textarea><br/>
			<? if ($$alert[2]) print((view_error($$alert[2]))."<br>"); ?>
			Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;&lt;cut&gt;&lt;h&gt;
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><br/><a href="javascript:void(0);" class="blue" onClick="toggle_box('attach')">+ Прикрепить файл к сообщению (<?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб)</a></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<div id="attach" style="display:<?=($$alert[3]?'block':'none')?>;padding-top:4px">
					<input type="hidden" name="MAX_FILE_SIZE" value="<?=blogs::MAX_FILE_SIZE?>">
					<div id="ad_button">
						<div>	
							<div id="attaches">
								<input type="file" name="attach[]" class="input-file" size="50"><span class="addButton" style="font-size: 12px;">&nbsp;</span>
							</div>
						</div>
					</div>
					<? /*if ($$alert[2]) print((view_error($$alert[2]))); */?>
					
					С помощью этого поля возможно загрузить:
					<ul style="padding: 0;margin-left:20px;">
						<li>Картинку: 600x1000 пикселей. 300 Кб. </li>
						<li>Файл: <?=(blogs::MAX_FILE_SIZE / (1024*1024))?> Мб.</li>
					</ul>
					Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
				</div>	
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><? if(($$alert[3])) print(view_error($$alert[3]) . '<br><br>');?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><a href="javascript:void(null);" class="blue" onClick="toggle_box('yt_link')">+ Добавить ссылку на YouTube видео</a></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<div id="yt_link" style="padding-top:4px;<? if(!$$alert[4]): ?>display:none<? endif; ?>">
					<input type="text" class="wdh100" name="yt_link" id="yt_link_val" value="<?=$$post['yt_link']?>" style="width:500px;" onfocus="isFocus = true;" onblur="isFocus = false;"><br/>
				</div>
				<? if($$alert[4]) print((view_error($$alert[4]))); ?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<br/><input type="submit" name="btn" class="i-btn" value="Комментировать" style="padding: 2px 15px !important; overflow:visible;" />
				<input type="hidden" name="parent" id="parentID" value="0" />
				<input type="hidden" name="blogID" value="<?=$$blog['id']?>" />
				<input type="hidden" name="action" value="addcmt" />
			</td>
		</tr>	
	</table>
	</form>
</div>
