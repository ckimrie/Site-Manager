<div class="btn-bar">
	<a class="submit" href="<?= $add_url ?>">Add Site</a>
</div>
<?php foreach ($sites as $site) : ?>
	<div class="sm-site" data-site-id="<?php echo $site->id() ?>">
		<h3><?php echo $site->name() ?></h3>
		<span class="status live"></span>
		<span class="status connecting active"></span>
		<span class="status offline"></span>
		<a href="<?php echo $site->base_url() ?>" target="_blank" class="button view">View</a>
		<a href="<?php echo $site->cp_url() ?>" target="_blank" class="button login">Login</a>
	</div>
<?php endforeach; ?>


<script type="text/javascript">
	window.siteManager = {};
	window.siteManager.js_api = "<?php echo $js_api ?>"
</script>