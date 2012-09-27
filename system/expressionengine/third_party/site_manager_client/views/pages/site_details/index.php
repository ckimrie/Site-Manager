
<div class="columns nav">
	<div class="left">
		<ul>
		<?php foreach ($navigation as $nav) : ?>
			<li class="<?php if($nav['active']) echo "active"; ?>"><a href="<?php echo $nav['url'] ?>"><?php echo $nav['label'] ?></a></li>
		<?php endforeach; ?>
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
			<div class="connection-stats">
				<ul>
					<li>Total Time: <span class="total_time">--</span></li>
				</ul>
			</div>
			<p class="meta"><a href="<?php echo $site->base_url() ?>" target="_blank"><?php echo $site->base_url() ?></a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php echo $site->cp_url() ?>" target="_blank">Control Panel</a></p>

		</div>
	</div>
</div>


<script type="text/javascript">
	window.SM = {};
	window.SM.js_api = "<?php echo $js_api ?>";
	window.SM.site_id = <?php echo $site->id() ?>;
</script>