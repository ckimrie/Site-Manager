<?php echo $navigation_top ?>
<div class="columns nav">
	<div class="left">
		<ul class="top">
		<?php foreach ($navigation as $nav) : ?>
			<li class="<?php if($nav['active']) echo "active"; ?>"><a href="<?php echo $nav['url'] ?>"><?php echo $nav['label'] ?></a></li>
		<?php endforeach; ?>
		</ul>

		<ul class="bottom">
			<li><a href="<?php echo $delete_url ?>" onclick="return confirm('Are you sure you want to delete this site?');">Delete Site</a></li>
		</ul>
	</div>
	<div class="right">
		<div class="site-details">
			<h1 class="mainHeading"><?php echo $site->name() ?></h1>
			<div class="statusBlock">
				<span class="status live"></span>
				<span class="status connecting active"></span>
				<span class="status offline"></span>
			</div>

			<p class="meta"><a href="<?php echo $site->base_url() ?>" target="_blank"><?php echo $site->base_url() ?></a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php echo $site->login_url() ?>" target="_blank">Login to CP</a> &nbsp; &nbsp; &nbsp; &nbsp; EE: <span class="app_version">-</span></p>

			<?php echo $form_declaration ?>
				<table class="mainTable padTable " border="0" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th width="50%">Setting</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Site Name</td>
							<td><input type="text" class="input fullfield" name="site_name" value="<?php echo $site->name() ?>"/></td>
						</tr>
						<tr>
							<td>Remote Site ID</td>
							<td><input type="text" class="input fullfield" name="site_id" value="<?php echo $site->setting("site_id") ?>"/></td>
						</tr>
						<tr>
							<td>Base URL</td>
							<td><input type="text" class="input fullfield" name="base_url" value="<?php echo $site->base_url() ?>"/></td>
						</tr>
						<tr>
							<td>Index Page</td>
							<td><input type="text" class="input fullfield" name="index_page" value="<?php echo $site->setting('index_page') ?>"/></td>
						</tr>
						<tr>
							<td>CP URL</td>
							<td><input type="text" class="input fullfield" name="cp_url" value="<?php echo $site->setting('cp_url') ?>"/></td>
						</tr>
						<tr>
							<td>API Action ID</td>
							<td><input type="text" class="input fullfield" name="action_id" value="<?php echo $site->setting('action_id') ?>"/></td>
						</tr>
						<tr>
							<td>User ID</td>
							<td><input type="text" class="input fullfield" name="user_id" value="<?php echo $site->setting('user_id') ?>"/></td>
						</tr>
						<tr>
							<td>Channel Nomenclature</td>
							<td><input type="text" class="input fullfield" name="channel_nomenclature" value="<?php echo $site->setting('channel_nomenclature') ?>"/></td>
						</tr>
						<tr>
							<td>Public Key</td>
							<td><input type="text" class="input fullfield" name="public_key" value="<?php echo $site->setting('public_key') ?>"/></td>
						</tr>
						<tr>
							<td>Private Key</td>
							<td><input type="text" class="input fullfield" name="private_key" value="<?php echo $site->setting('private_key') ?>"/></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" class="submit" value="Update"/></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>
</div>


<script type="text/javascript">
	window.SM = {};
	window.SM.XID = "<?php echo $XID ?>";
	window.SM.js_api = "<?php echo $js_api ?>";
	window.SM.js_decryption_api = "<?php echo $js_decryption_api ?>";
	window.SM.js_encryption_api = "<?php echo $js_encryption_api ?>";
	window.SM.site_id = <?php echo $site->id() ?>;

	define('site_config', [], function() {
		return <?php echo $site->js_config() ?>;
	});

</script>



