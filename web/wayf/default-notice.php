<?php // Copyright (c) 2016, SWITCH ?>

<!-- Identity Provider Selection: Start -->
<h1><?php echo getLocalString('settings'); ?></h1> 
<form id="IdPList" name="IdPList" method="post" onSubmit="return checkForm()" action="<?php echo $actionURL ?>">
	<div id="userInputArea">
		<p class="promptMessage"><?php echo getLocalString('confirm_permanent_selection'); ?></p>
		<p><?php echo getLocalString('permanent_cookie_notice'); ?></p>
		<div style="text-align: center">
			<select name="permanent_user_idp" id="userIdPSelection">
				<option value="<?php echo $permanentUserIdP ?>" logo="<?php echo $permanentUserIdPLogo ?>"><?php echo $permanentUserIdPName ?></option>
			</select>
			<input type="submit" accesskey="c" name="clear_user_idp" value="<?php echo getLocalString('delete_permanent_cookie_button') ?>">
			<?php if (isValidShibRequest()) : ?>
			<br /><br />
			<input type="submit" accesskey="s" name="Select" name="permanent" value="<?php echo getLocalString('goto_sp') ?>">
			<?php endif ?>
			<p>
			<?php $scriptURL = "https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ?>
			<?php $fullURL = "<br /><a href=".$scriptURL.">".$scriptURL."</a>" ?>
			<?php echo sprintf(getLocalString('permanent_cookie_note'), $fullURL) ?>
			</p>
		</div>
	</div>
</form>

<?php if (getLocalString('additional_info') != '') { ?>
<p><?php echo getLocalString('additional_info') ?></p>
<?php } ?>
<!-- Identity Provider Selection: End -->
