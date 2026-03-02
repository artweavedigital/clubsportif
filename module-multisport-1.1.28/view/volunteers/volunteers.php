<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$events = multisport::$eventsTable ?? [''=>'—'];
$eventId = multisport::$selectedEventId ?? '';
?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Bénévoles — événement</h3>
			<div class="row">
				<div class="col8">
					<?php echo template::select('clubVolEventGo', $events, [
						'label' => 'Événement',
						'selected' => $eventId
					]); ?>
				</div>
				<div class="col2">
					<?php echo template::button('clubVolGo', [
						'value' => 'Afficher',
						'href' => 'javascript:void(0);'
					]); ?>
				</div>
				<div class="col2"><?php echo template::speech('Planning bénévoles.'); ?></div>
			</div>
			<script>
			document.getElementById('clubVolGo').addEventListener('click', function(){
				const sel = document.getElementsByName('clubVolEventGo')[0];
				if(sel && sel.value){
					window.location = "<?php echo helper::baseUrl() . $this->getUrl(0) . '/volunteers/'; ?>" + sel.value;
				}
			});
			</script>
		</div>
	</div>
</div>

<?php echo template::formOpen('clubVolForm'); ?>
<?php echo template::hidden('clubVolEvent', ['value'=>$eventId]); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Ajouter un créneau</h3>
			<div class="row">
				<div class="col5"><?php echo template::text('clubVolTask', ['label'=>'Tâche','required'=>true,'placeholder'=>'Buvette, entrée, arbitrage…']); ?></div>
				<div class="col3"><?php echo template::text('clubVolTime', ['label'=>'Horaire','placeholder'=>'Ex : 09:00–11:00']); ?></div>
				<div class="col2"><?php echo template::number('clubVolNeeded', ['label'=>'Bénévoles nécessaires','min'=>1,'max'=>50,'value'=>2]); ?></div>
				<div class="col2"><?php echo template::submit('clubVolSubmit', ['value'=>'Enregistrer']); ?></div>
			</div>
			<div class="row">
				<div class="col12"><?php echo template::textarea('clubVolAssigned', ['label'=>'Affectations (texte libre)','placeholder'=>'Ex : Parent Alex, Parent Sam…']); ?></div>
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Planning</h3>
			<?php if(!empty(multisport::$volunteerTable)): ?>
				<?php echo template::table([4,2,1,4,1], multisport::$volunteerTable, ['Tâche', 'Horaire', 'Besoin', 'Affectés', '']); ?>
			<?php else: ?>
				<?php echo template::speech('Aucun créneau.'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
