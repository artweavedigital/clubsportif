<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$players = multisport::$playerLabels ?? [''=>'—'];
$playerId = multisport::$selectedPlayerId ?? '';
?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Suivi médical</h3>
			<div class="row">
				<div class="col8">
					<?php echo template::select('clubMedPlayerGo', $players, [
						'label' => 'Joueur',
						'selected' => $playerId
					]); ?>
				</div>
				<div class="col2">
					<?php echo template::button('clubMedGo', [
						'value' => 'Afficher',
						'href' => 'javascript:void(0);'
					]); ?>
				</div>
				<div class="col2"><?php echo template::speech('Historique blessures.'); ?></div>
			</div>
			<script>
			document.getElementById('clubMedGo').addEventListener('click', function(){
				const sel = document.getElementsByName('clubMedPlayerGo')[0];
				if(sel && sel.value){
					window.location = "<?php echo helper::baseUrl() . $this->getUrl(0) . '/medical/'; ?>" + sel.value;
				}
			});
			</script>
		</div>
	</div>
</div>

<?php echo template::formOpen('clubMedForm'); ?>
<?php echo template::hidden('clubMedPlayer', ['value'=>$playerId]); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Ajouter une entrée</h3>
			<div class="row">
				<div class="col3"><?php echo template::date('clubMedInjuryDate', ['label'=>'Date blessure','type'=>'date','required'=>true]); ?></div>
				<div class="col6"><?php echo template::textarea('clubMedDesc', ['label'=>'Description','required'=>true]); ?></div>
				<div class="col3"><?php echo template::date('clubMedReturnDate', ['label'=>'Date reprise (optionnel)','type'=>'date']); ?></div>
			</div>
			<div class="row">
				<div class="col2 offset10"><?php echo template::submit('clubMedSubmit', ['value'=>'Enregistrer']); ?></div>
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Historique</h3>
			<?php if(!empty(multisport::$medicalTable)): ?>
				<?php echo template::table([2,7,2,1], multisport::$medicalTable, ['Date', 'Description', 'Reprise', '']); ?>
			<?php else: ?>
				<?php echo template::speech('Aucune entrée.'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
