			<nav id="sidebar" class="sidebar js-sidebar">
				<div class="sidebar-content js-simplebar">
					<a class="sidebar-brand" href="javascript:void(0);">
						<img src="img/logo.png" class="img-fluid" width="40"><span style="font-family: 'EB Garamond', serif; font-size: 1.3rem;" class="align-middle">&nbsp;&nbsp;&nbsp;Альянс «Justice»</span>
					</a>
					<ul class="sidebar-nav">
						<?php if ($_SESSION['user']) { ?>
							<li class="sidebar-item <?= ($_GET['p'] == 'profile') ? 'active' : ''; ?>">
								<a class="sidebar-link" href="/?p=profile">
									<i class="align-middle" data-feather="home"></i> <span class="align-middle">Профиль</span>
								</a>
							</li>
							<li class="sidebar-item <?= ($_GET['p'] == 'events') ? 'active' : ''; ?> ">
								<a class="sidebar-link" href="/?p=events">
									<i class="align-middle" data-feather="alert-circle"></i> <span class="align-middle">События</span>
								</a>
							</li>
							<li class="sidebar-item <?= ($_GET['p'] == 'loot') ? 'active' : ''; ?> ">
								<a class="sidebar-link" href="/?p=loot">
									<i class="align-middle" data-feather="shopping-cart"></i> <span class="align-middle">Распределение</span>
								</a>
							</li>
							<li class="sidebar-item <?= ($_GET['p'] == 'ratings') ? 'active' : ''; ?>">
								<a class="sidebar-link" href="/?p=ratings">
									<i class="align-middle" data-feather="trending-up"></i> <span class="align-middle">Рейтинги</span>
								</a>
							</li>
							<?php if ($_SESSION['user']['adm'] || $_SESSION['user']['md']) { ?>
								<li class="sidebar-item <?= ($_GET['p'] == 'mods') ? 'active' : ''; ?>">
									<a class="sidebar-link" href="/?p=mods">
										<i class="align-middle" data-feather="tool"></i> <span class="align-middle">Управление</span>
									</a>
								</li>
							<?php } ?>
							<?php if ($_SESSION['user']['adm'] || $_SESSION['user']['dpl']) { ?>
								<li class="sidebar-item <?= ($_GET['p'] == 'admin') ? 'active' : ''; ?>">
									<a class="sidebar-link" href="/?p=admin">
										<i class="align-middle" data-feather="activity"></i> <span class="align-middle">Статистика</span>
									</a>
								</li>
							<?php } ?>
							<li class="sidebar-item">
								<a class="sidebar-link" href="/?a=logout">
									<i class="align-middle" data-feather="log-out"></i> <span class="align-middle">Выход</span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</nav>