<?php echo $navigation ?>

<table class="mainTable padTable" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th width="50"></th>
			<th >Name</th>
			<th width="250">License Number</th>
			<th width="80">EE Version</th>
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
			<td><?php echo $site->name() ?></td>
			<td class="license_number"></td>
			<td class="app_version"></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>



<script type="text/javascript">
	window.SM = {};
	window.SM.js_api = "<?php echo $js_api ?>"

	define('site_configs', [], function() {
		var a = [];
		<?php foreach ($sites as $site) : ?>
			a.push(<?php echo $site->js_config() ?>); 
		<?php endforeach; ?>

		return a;
	});
</script>