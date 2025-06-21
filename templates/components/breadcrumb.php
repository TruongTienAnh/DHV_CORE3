<div class="d-flex align-items-center justify-content-between mb-3">
	<div>
		<h4 class="text-body fw-bold mb-0"><?= $vars['title'] ?></h4>
		<ul class="breadcrumb mb-0 small">
			<li class="breadcrumb-item small">
				<a
					href="/"
					class="link-secondary pjax-load"
				><?= $jatbi->lang("Trang chủ") ?></a>
			</li>
			<li
				class="text-body breadcrumb-item small"
				aria-current="page"
			><?= $vars['title'] ?></li>
		</ul>
	</div>
</div>