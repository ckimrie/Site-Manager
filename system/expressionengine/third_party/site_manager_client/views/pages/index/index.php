<?php echo $navigation ?>

<?php foreach ($sites as $site) : ?>
	<div class="sm-site" data-site-id="<?php echo $site->id() ?>">
		<h3><a href="<?php echo $site->local_url() ?>"><?php echo $site->name() ?></a></h3>
		<span class="status live"></span>
		<span class="status connecting active"></span>
		<span class="status offline"></span>
		<a href="<?php echo $site->base_url() ?>" target="_blank" class="button view">View</a>
		<a href="<?php echo $site->cp_url() ?>" target="_blank" class="button login">Login</a>
	</div>
<?php endforeach; ?>


<script type="text/javascript">
	window.SM = {};
	window.SM.js_api = "<?php echo $js_api ?>"
</script>