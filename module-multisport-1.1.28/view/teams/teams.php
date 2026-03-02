<?php include 'module/multisport/view/_partials/nav.php'; ?>
<?php $edit = multisport::$editTeam; ?>

<?php echo template::formOpen('msTeam'); ?>
<div class="row"><div class="col12"><div class="block">
	<h3><?php echo $edit ? 'Éditer une équipe' : 'Ajouter une équipe'; ?></h3>
	<?php echo template::hidden('clubTeamId',['value'=>$edit['id'] ?? '']); ?>
			<div class="row">
				<div class="col5">
					<?php echo template::text('clubTeamName', [
						'label' => 'Équipe (ex : Cadets)',
						'value' => $edit['name'] ?? ''
					]); ?>
				</div>
				<div class="col2">
					<?php echo template::text('clubTeamCategory', [
						'label' => 'Catégorie (ex : U8)',
						'placeholder' => 'U8',
						'value' => $edit['category'] ?? ''
					]); ?>
				</div>
				<div class="col2" style="padding-top:26px;">
					<?php echo template::submit('msTeamSubmit', ['value' => $edit ? 'Mettre à jour' : 'Créer']); ?>
				</div>
				<div class="col3" style="padding-top:26px;text-align:right;">
					<?php echo template::button('teamAddNew', [
						'href' => helper::baseUrl() . $this->getUrl(0) . '/teams',
						'value' => template::ico('plus') . ' Rajouter 1 équipe'
					]); ?>
				</div>
			</div>
			<?php echo template::formClose(); ?>

<?php if($edit): ?>
	<div class="row"><div class="col12"><div class="block">
		<h3>Staff — photos</h3>
		<?php
		$cards = [];
		foreach ((array)multisport::$staff as $sid => $st) {
			if (($st['teamId'] ?? '') === ($edit['id'] ?? '')) $cards[] = $st;
		}
		?>
		<?php if(empty($cards)): ?>
			<?php echo template::speech('Aucun staff associé. Ajoute-le via le menu Staff.'); ?>
		<?php else: ?>
			<div style="display:flex;gap:12px;flex-wrap:wrap;">
				<?php foreach($cards as $st):
					$ph = (string)($st['photo'] ?? '');
					$url = $ph ? (helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($ph)) : '';
				?>
					<div style="width:180px;border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:10px;background:#fff;">
						<?php if($url): ?>
							<img src="<?php echo $url; ?>" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:12px;border:1px solid rgba(0,0,0,.10);">
						<?php else: ?>
							<div style="width:100%;height:120px;border-radius:12px;border:1px dashed rgba(0,0,0,.20);display:flex;align-items:center;justify-content:center;opacity:.55;">
								<?php echo template::ico('user'); ?>
							</div>
						<?php endif; ?>
						<div style="margin-top:8px;font-weight:700;"><?php echo htmlspecialchars(trim(($st['lastName'] ?? '').' '.($st['firstName'] ?? ''))); ?></div>
						<div style="opacity:.75;"><?php echo htmlspecialchars((string)($st['role'] ?? '')); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div></div></div>

	<div class="row"><div class="col12"><div class="block">
		<h3>Équipe — photos (5 par ligne)</h3>
		<?php
		$members = [];
		foreach ((array)multisport::$players as $pid => $p) {
			if (($p['teamId'] ?? '') === ($edit['id'] ?? '')) $members[] = $p;
		}
		?>
		<?php if(empty($members)): ?>
			<?php echo template::speech('Aucun membre associé à cette équipe.'); ?>
		<?php else: ?>
			<div class="msTeamGrid" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;">
				<?php foreach($members as $p):
					$ph = (string)($p['photo'] ?? '');
					$url = $ph ? (helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($ph)) : '';
				?>
					<div style="border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:10px;background:#fff;">
						<?php if($url): ?>
							<img src="<?php echo $url; ?>" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:12px;border:1px solid rgba(0,0,0,.10);">
						<?php else: ?>
							<div style="width:100%;height:120px;border-radius:12px;border:1px dashed rgba(0,0,0,.20);display:flex;align-items:center;justify-content:center;opacity:.55;">
								<?php echo template::ico('user'); ?>
							</div>
						<?php endif; ?>
						<div style="margin-top:8px;font-weight:700;"><?php echo htmlspecialchars(trim(($p['lastName'] ?? '').' '.($p['firstName'] ?? ''))); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<style>
				@media (max-width: 1100px){ .msTeamGrid{grid-template-columns:repeat(3,minmax(0,1fr))!important;} }
				@media (max-width: 600px){ .msTeamGrid{grid-template-columns:repeat(2,minmax(0,1fr))!important;} }
			</style>
		<?php endif; ?>
	</div></div></div>
<?php endif; ?>

<div class="row"><div class="col12"><div class="block">
	<h3>Équipes</h3>
	<?php if(!empty(multisport::$teamsTable)): echo template::table([5,3,1,1], multisport::$teamsTable, ['Équipe','Catégorie','','']); else: echo template::speech('Aucune équipe.'); endif; ?>
</div></div></div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
