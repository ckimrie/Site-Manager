<?php echo $navigation_top ?>

<?php foreach ($sites as $site) : ?>
	<div class="sm-site" id="site-<?php echo $site->id() ?>" data-site-id="<?php echo $site->id() ?>">
		<div class="thumbnail" style="background-image:url(<?php echo $site->thumbnail() ?>)"></div>
		<h3><?php echo $site->name() ?></h3>
		<a class="details" href="<?php echo $site->local_url() ?>"></a>
		<span class="sitesStatusBlock">
			<span class="status live"></span>
			<span class="status connecting active"></span>
			<span class="status offline"></span>
		</span>
		<div class="app_version_wrapper"><span class="app_version"></span></div>
		<a href="<?php echo $site->base_url() ?>" target="_blank" class="button view">View</a>
		<a href="<?php echo $site->login_url() ?>" target="_blank" class="button login">Login</a>
	</div>
<?php endforeach; ?>


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