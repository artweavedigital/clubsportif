<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$events = multisport::$eventsTable ?? [''=>'—'];
$players = multisport::$playerLabels ?? [];
?>

<?php echo template::formOpen('clubConvForm'); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Créer une convocation</h3>

			<div class="row">
				<div class="col8">
					<?php echo template::select('clubConvEvent', $events, [
						'label' => 'Événement',
						'required' => true
					]); ?>
				</div>
				<div class="col4">
					<?php echo template::textarea('clubConvMessage', [
						'label' => 'Message (optionnel)',
						'placeholder' => 'Consignes, heure de rendez-vous, tenue…'
					]); ?>
				</div>
			</div>

			<h4>Joueurs convoqués</h4>
			<div class="row">
				<div class="col12">
					<div class="block" style="margin-top:0;">
						<?php if(empty($players)): ?>
							<?php echo template::speech('Aucun joueur à sélectionner.'); ?>
						<?php else: ?>
							<div class="row">
								<?php
								$i=0;
								foreach($players as $pid => $label):
									$i++;
									?>
									<div class="col4" style="margin:6px 0;">
										<label style="display:flex;gap:8px;align-items:center;">
											<input type="checkbox" name="clubConvPlayers[]" value="<?php echo htmlspecialchars($pid); ?>">
											<span><?php echo htmlspecialchars($label); ?></span>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col2 offset10">
					<?php echo template::submit('clubConvSubmit', ['value' => 'Créer & envoyer']); ?>
				</div>
			</div>
			<div style="opacity:.85;margin-top:8px;">
				— Si le mode email est “simulation”, aucun email n’est expédié, mais les tokens sont créés.
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Historique des convocations</h3>
			<p style="opacity:.75;margin-top:0;">Statuts : Présent / Absent / En attente</p>
			<?php if(!empty(multisport::$convocationsTable)): ?>
				<?php echo template::table([2,8,1,1], multisport::$convocationsTable, ['Créée', 'Événement', 'P/A/Att', '']); ?>
			<?php else: ?>
				<?php echo template::speech('Aucune convocation.'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
