<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$edit = multisport::$editEvent;
$hasId = ($edit && !empty($edit['id']));
?>

<?php echo template::formOpen('clubEventForm'); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3><?php echo $hasId ? 'Éditer un événement' : 'Créer un événement'; ?></h3>
			<?php echo template::hidden('clubEventId', ['value' => $edit['id'] ?? '']); ?>

			<div class="row">
				<div class="col12">
					<?php echo template::textarea('clubEventTitle', [
						'label' => 'Événement (texte)',
						'value' => $edit['title'] ?? ''
					]); ?>
				</div>
			</div>

			<div class="row">
				<div class="col2 offset10">
					<?php echo template::submit('clubEventSubmit', ['value' => $hasId ? 'Mettre à jour' : 'Créer']); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Bénévoles — tâches & planning</h3>

			<?php if(!$hasId): ?>
				<?php echo template::speech('Enregistre d’abord l’événement pour pouvoir ajouter les bénévoles.'); ?>
			<?php else: ?>
				<form method="post" action="<?php echo helper::baseUrl() . $this->getUrl(0) . '/volunteers/' . htmlspecialchars((string)$edit['id']); ?>">
					<input type="hidden" name="clubVolEvent" value="<?php echo htmlspecialchars((string)$edit['id']); ?>">
					<input type="hidden" name="clubVolReturn" value="events/<?php echo htmlspecialchars((string)$edit['id']); ?>">

					<div class="row">
						<div class="col4"><?php echo template::text('clubVolTask', ['label'=>'Tâche','required'=>true]); ?></div>
						<div class="col2"><?php echo template::text('clubVolTime', ['label'=>'Horaire']); ?></div>
						<div class="col2"><?php echo template::text('clubVolNeeded', ['label'=>'Bénévoles nécessaires','type'=>'number','value'=>1,'min'=>1]); ?></div>
						<div class="col4"><?php echo template::textarea('clubVolAssigned', ['label'=>'Affectations (texte libre)']); ?></div>
					</div>

					<div class="row">
						<div class="col2 offset10"><?php echo template::submit('clubVolSubmit', ['value'=>'Ajouter']); ?></div>
					</div>
				</form>

				<?php $vol = (array) multisport::$volunteerData; ?>
				<?php if(!empty($vol)): ?>
					<?php
					$rows=[];
					foreach($vol as $vid=>$v){
						$rows[]=[
							htmlspecialchars((string)($v['task']??'')),
							htmlspecialchars((string)($v['time']??'')),
							(string)($v['needed']??''),
							nl2br(htmlspecialchars((string)($v['assigned']??''))),
							template::button('msVolDelE'.$vid, [
								'class'=>'buttonRed',
								'href'=>helper::baseUrl().$this->getUrl(0).'/volunteerDelete/'.htmlspecialchars((string)$edit['id']).'/'.$vid.'/events/'.htmlspecialchars((string)$edit['id']),
								'value'=>template::ico('trash'),
								'help'=>'Supprimer'
							])
						];
					}
					echo template::table([3,2,1,5,1], $rows, ['Tâche','Horaire','Besoin','Affectations','']);
					?>
				<?php else: ?>
					<?php echo template::speech('Aucun créneau bénévole.'); ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Liste des événements</h3>
			<?php if(!empty(multisport::$eventsTable)): ?>
				<?php echo template::table([2,8,1,1], multisport::$eventsTable, ['Début', 'Événement', '', '']); ?>
			<?php else: ?>
				<?php echo template::speech('Aucun événement.'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
