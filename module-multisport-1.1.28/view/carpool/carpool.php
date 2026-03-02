<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$events = multisport::$eventsTable ?? [''=>'—'];
$eventId = multisport::$selectedEventId ?? '';
?>

<?php echo template::formOpen('clubCarpoolSelect'); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Covoiturage</h3>
			<div class="row">
				<div class="col8">
					<?php echo template::select('clubCarpoolEventGo', $events, [
						'label' => 'Événement',
						'selected' => $eventId
					]); ?>
				</div>
				<div class="col2">
					<?php echo template::button('clubCarpoolGo', [
						'value' => 'Afficher',
						'href' => 'javascript:void(0);'
					]); ?>
				</div>
				<div class="col2">
					<?php echo template::speech('Choisir un événement.'); ?>
				</div>
			</div>
			<script>
			document.getElementById('clubCarpoolGo').addEventListener('click', function(){
				const sel = document.getElementsByName('clubCarpoolEventGo')[0];
				if(sel && sel.value){
					window.location = "<?php echo helper::baseUrl() . $this->getUrl(0) . '/carpool/'; ?>" + sel.value;
				}
			});
			</script>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<?php echo template::formOpen('clubCarpoolForm'); ?>
<?php echo template::hidden('clubCarpoolEvent', ['value'=>$eventId]); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Ajouter un véhicule</h3>
			<div class="row">
				<div class="col4"><?php echo template::text('clubCarpoolDriver', ['label'=>'Conducteur','required'=>true]); ?></div>
				<div class="col3"><?php echo template::text('clubCarpoolPhone', ['label'=>'Téléphone']); ?></div>
				<div class="col2"><?php echo template::number('clubCarpoolSeats', ['label'=>'Places','min'=>1,'max'=>12,'value'=>4]); ?></div>
				<div class="col3"><?php echo template::submit('clubCarpoolSubmit', ['value'=>'Enregistrer']); ?></div>
			</div>
			<div class="row">
				<div class="col12"><?php echo template::textarea('clubCarpoolPassengers', ['label'=>'Qui emmène qui ? (texte libre)','placeholder'=>'Ex : Alex (U11) + Sam (U11)…']); ?></div>
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Liste (événement)</h3>
			<?php if(!empty(multisport::$carpoolTable)): ?>
				<?php echo template::table([3,2,1,5,1], multisport::$carpoolTable, ['Conducteur', 'Téléphone', 'Places', 'Passagers', '']); ?>
			<?php else: ?>
				<?php echo template::speech('Aucune entrée.'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
