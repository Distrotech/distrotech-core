<form name="f" method="get" autocomplete="off" action="<?=LOGINPATH?>">
<input type="hidden" name="chal" value="<?=$_GET['challenge']?>">
<input type="hidden" name="uamip" value="<?=$_GET['uamip']?>">
<input type="hidden" name="uamport" value="<?=$_GET['uamport']?>">
<input type="hidden" name="userurl" value="<?=urldecode($_GET['userurl'])?>">
<center>
<table class="form_table">
<tbody>
<tr>
	<td class="form_label"><?=_t('labelLogin')?></td>
	<td><input class="form_input" type="text" name="uid" size="20" maxlength="255" value="<?=$username?>"></td>
</tr>
<tr>
	<td class="form_label"><?=_t('labelPassword')?></td>
	<td><input class="form_input" type="password" name="pwd" size="20" maxlength="255" value="<?=$password?>"></td>
</tr>
<? if (ENABLE_LOGIN_COOKIE): ?>
<tr>
	<td>&nbsp;</td>
	<td><input class="form_check" type="checkbox" name="save_login"><?=_t('rememberlogin')?></td>
</tr>
<? endif; ?>
<tr>
	<td class="form_submit" colspan="2"><input type="submit" name="login" value="<?=_t('login')?>" onclick="popUpWindow('<?=LOGINPATH?>?res=popup1&uamip=<?=UAMIP?>&uamport=<?=UAMPORT?>','GLS','272','262',0,0,0,0)"></td>
</tr>
</tbody>
</table>
</center>
</form>
