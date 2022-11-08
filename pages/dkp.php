<main class="content">
	<div class="container-fluid p-0">
		<div class="row">
			<div class="col-md-12 col-xl-12">
				<div class="card mb-3">
					<div class="card-body">
						<?php
						$parsedown = new Parsedown();
						echo $parsedown->text('# Все о ДКП системе');
						echo $parsedown->text(file_get_contents('text/dkp.md'));
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>