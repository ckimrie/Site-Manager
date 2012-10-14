<?php echo $navigation_top ?>

<!--<a href="#" id="sm-refresh">Refresh</a>-->
<div class="syncWrapper" id="syncWrapper">

	<div class="site left">
		<div class="site-header">
			<select id="sm-site1">
				<option value="">- Select Site</option>
			</select>
		</div>
		<div id="sm-site1-body"></div>
	</div>

	<div class="gutter">
		<div class="gutter-header">
			<select id="sm-sync_type" disabled>
				<option value="channels">Channels</option>
				<option value="fields">Fields</option>
				<option value="fieldgroups">Field Groups</option>
				<option value="categorygroups">Category Groups</option>
			</select>
			<select id="sm-fieldgroup">
			</select>
		</div>
		<div id="sm-gutter-body"></div>
	</div>

	<div class="site right">
		<div class="site-header">
			<select id="sm-site2">
				<option value="">- Select Site</option>
			</select>
		</div>
		<div id="sm-site2-body"></div>
	</div>
	<div class="no-results">
		<p>No Items Found</p>
	</div>
</div>



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