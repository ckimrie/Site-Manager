<div class="payload_settings_wrapper">
	<?php echo validation_errors(); ?>
	<?= $add_site_form_declaration ?>
		<div class="label" onclick="document.getElementById('payload_settings').select();">&darr; Paste Settings</div>
		<textarea  id="payload_settings" name="site_settings" rows="9" onclick="document.getElementById('payload_settings').select();"></textarea>
		<div class="btn-bar">
			<input type="submit" class="submit" value="Continue" />
		</div>
	</form>
</div>
<h1 class="mainHeading">Site Configuration</h1>
<p>In order to add a site you will need to install the "Site Manager Server" module in each site you wish to install remotely.</p>
<p>Once installed, the module will provide you with some encoded site settings data that you will need to copy and paste into the textbox on the right.</p>