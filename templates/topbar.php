			<div class="main">
				<nav class="navbar navbar-expand navbar-light navbar-bg">
					<a class="sidebar-toggle js-sidebar-toggle">
						<i class="hamburger align-self-center"></i>
					</a>
					<div class="navbar-collapse collapse">
						<ul class="navbar-nav navbar-align">
							<?php if (!$_SESSION['user']) { ?>
								<li class="nav-item dropdown">
									<button style="min-width: 125px;" onclick="location.href='/?p=gateway&a=login'" class="btn btn-sm btn-danger">Вход</button>
								</li>
							<?php } else { ?>
								<?php if ($_SESSION['user']['dpl']) { ?>
									<li class="nav-item dropdown">
										<button id="hb1" title="Панель Дипломата" style="margin-right: 15px; padding-top: 2px;" class="btn btn-danger btn-rounded btn-icon" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDipl" aria-controls="offcanvasDipl">
											<i class="align-middle" data-feather="star"></i>
										</button>
									</li>
								<?php } ?>
								<?php if ($_SESSION['user']['dpl'] || $_SESSION['user']['adm'] || $_SESSION['user']['md']) { ?>
									<li class="nav-item dropdown">
										<button id="hb2" title="Панель Модератора" style="margin-right: 15px; padding-top: 2px;" class="btn btn-danger btn-rounded btn-icon" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMod" aria-controls="offcanvasMod">
											<i class="align-middle" data-feather="settings"></i>
										</button>
									</li>

								<?php } ?>
								<li class="nav-item dropdown">
									<button id="hb3" onclick="time()" title="Панель Событий" style="margin-right: 15px; padding-top: 2px;" class="btn btn-danger btn-rounded btn-icon" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAdmin" aria-controls="offcanvasAdmin">
										<i class="align-middle" data-feather="file-plus"></i>
									</button>
								</li>
								<?php if ($_SESSION['user']['party']) { ?>
									<li class="nav-item dropdown">
										<button id="hb4" title="Панель Боевой Группы" style="margin-right: 15px; padding-top: 2px;" class="btn btn-danger btn-rounded btn-icon" data-bs-toggle="offcanvas" data-bs-target="#offcanvasConst" aria-controls="offcanvasConst">
											<i class="align-middle" data-feather="users"></i>
										</button>
									</li>

								<?php } ?>
								<li class="nav-item dropdown">
									<button id="hb5" title="Панель Персонажа" style="padding-top: 2px;" class="btn btn-danger btn-rounded btn-icon" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPlayer" aria-controls="offcanvasPlayer">
										<i class="align-middle" data-feather="user"></i>
									</button>
								</li>
							<?php }  ?>
						</ul>
					</div>
				</nav>