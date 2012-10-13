<?php echo $navigation_top ?>

<table class="mainTable padTable" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th width="50">Status</th>
			<th width="80">Version</th>
			<th >Name</th>
			<th width="250">License Number</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody>

		<?php foreach ($sites as $site) : ?>
		<tr id="site-<?php echo $site->id() ?>" data-site-id="<?php echo $site->id() ?>">

			<td>
				<span class="status live"></span>
				<span class="status connecting active"></span>
				<span class="status offline"></span>
			</td>
			<td class="app_version"></td>
			<td><a href="<?php echo $site->local_url() ?>"><?php echo $site->name() ?></a></td>
			<td class="license_number"></td>
			<td width="50"><a href="<?php echo $site->base_url() ?>">View</a></td>
			<td width="50"><a href="<?php echo $site->login_url() ?>">Login</a></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>



<script type="text/javascript">
	window.SM = {};
	window.SM.XID = "<?php echo $XID ?>";
	window.SM.js_api = "<?php echo $js_api ?>";
	window.SM.js_decryption_api = "<?php echo $js_decryption_api ?>";
	window.SM.js_encryption_api = "<?php echo $js_encryption_api ?>";

	define('site_configs', [], function() {
		var a = [];
		<?php foreach ($sites as $site) : ?>
			a.push(<?php echo $site->js_config() ?>);
		<?php endforeach; ?>

		return a;
	});
</script>