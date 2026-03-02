<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$edit = multisport::$editEvent;
$calStartVal = !empty($edit['start']) ? date('Y-m-d\TH:i', (int)$edit['start']) : date('Y-m-d\TH:i');
$calEndVal = !empty($edit['end']) ? date('Y-m-d\TH:i', (int)$edit['end']) : '';

$teams = multisport::$teamsTable ?? [''=>'—'];
$matches = multisport::$calendarMatchList ?? [''=>'—'];
$players = (array)multisport::$players;
$conv = multisport::$calendarLatestConv ?? null;
?>

<link rel="stylesheet" href="<?php echo helper::baseUrl(false); ?>module/multisport/vendor/animated-calendar/animated-calendar.css">
<script src="<?php echo helper::baseUrl(false); ?>module/multisport/vendor/animated-calendar/animated-calendar.js"></script>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Créer une rencontre</h3>

			<?php echo template::formOpen('clubCalendarForm'); ?>
			<?php echo template::hidden('calAction', ['value'=>'matchSave']); ?>
			<?php echo template::hidden('clubCalId', ['value' => $edit['id'] ?? '' ]); ?>

			<div class="row">
				<div class="col4">
					<?php echo template::text('clubCalOpponent', [
						'label' => 'Adversaire',
						'required' => true,
						'value' => $edit['opponent'] ?? ''
					]); ?>
				</div>
				<div class="col4">
					<?php echo template::select('clubCalHomeAway', [
						''=>'—',
						'home'=>'Domicile',
						'away'=>'Extérieur'
					], [
						'label' => 'Domicile / Extérieur',
						'required' => true,
						'selected' => $edit['homeAway'] ?? ''
					]); ?>
				</div>
				<div class="col4">
					<?php echo template::text('clubCalCategory', [
						'label' => 'Catégorie',
						'required' => true,
						'placeholder' => 'U8',
						'value' => $edit['category'] ?? ''
					]); ?>
				</div>
			</div>

			<div class="row">
				<div class="col4">
					<?php echo template::text('clubCalStart', ['label'=>'Début',
						'required'=>true,'type'=>'datetime-local','value'=>$calStartVal]); ?>
				</div>
				<div class="col4">
					<?php echo template::text('clubCalEnd', ['label'=>'Fin (optionnel)','type'=>'datetime-local','value'=>$calEndVal]); ?>
				</div>
				<div class="col4">
					<?php echo template::text('clubCalLocation', [
						'label' => 'Adresse / lieu',
						'required' => true,
						'value' => $edit['location'] ?? ''
					]); ?>
				</div>
			</div>

			<div class="row">
				<div class="col10">
					<?php echo template::textarea('clubCalTitle', [
						'label' => 'Rencontre (texte)',
						'value' => $edit['title'] ?? ''
					]); ?>
				</div>
				<div class="col2" style="padding-top:26px;">
					<?php echo template::submit('clubCalSubmit', ['value' => 'Enregistrer']); ?>
				</div>
			</div>

			<?php echo template::formClose(); ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Convocations</h3>

			<?php echo template::formOpen('msConvInCalendar'); ?>
			<?php echo template::hidden('calAction', ['value'=>'convocation']); ?>
			<?php echo template::hidden('clubConvReturn', ['value' => 'calendar/' . ($edit['id'] ?? '')]); ?>

			<div class="row">
				<div class="col6">
					<?php echo template::select('clubConvEvent', $matches, [
						'label' => 'Rencontre',
						'selected' => $edit['id'] ?? ''
					]); ?>
				</div>
</div>

			<?php
			$cat = $edit['category'] ?? '';
			?>
			<div class="row">
				<div class="col12">
					<?php echo template::formOpen('msResult'); ?>
			<?php echo template::hidden('calAction', ['value'=>'result']); ?>
			<?php echo template::hidden('clubResEvent', ['value' => $edit['id'] ?? '' ]); ?>

			
			<div class="row">
				<div class="col4"><?php echo template::text('clubResHomeTeam', ['label'=>'Équipe (texte)','value'=>$edit['resultHomeTeam'] ?? (multisport::$config['clubFullName'] ?? multisport::$clubName) ]); ?></div>
				<div class="col2"><?php echo template::text('clubResHomeScore', ['label'=>'Score','type'=>'number','value'=>$edit['resultHomeScore'] ?? '' ]); ?></div>
				<div class="col4"><?php echo template::text('clubResAwayTeam', ['label'=>'Adversaire (texte)','value'=>$edit['resultAwayTeam'] ?? ($edit['opponent'] ?? '') ]); ?></div>
				<div class="col2"><?php echo template::text('clubResAwayScore', ['label'=>'Score adverse','type'=>'number','value'=>$edit['resultAwayScore'] ?? '' ]); ?></div>
			</div>
<div class="row">
				<div class="col2"><?php echo template::text('clubResAwayScore', ['label'=>'Score adverse','type'=>'number','value'=>$edit['resultAwayScore'] ?? '' ]); ?></div>
				<div class="col10"><?php echo template::textarea('clubResNotes', ['label'=>'Notes','value'=>$edit['resultNotes'] ?? '' ]); ?></div>
			</div>
			<div class="row">
				<div class="col2 offset10"><?php echo template::submit('clubResSubmit', ['value'=>'Enregistrer']); ?></div>
			</div>

			<?php echo template::formClose(); ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Calendrier mensuel</h3>
			<div id="ac"></div>
			<script>
			(function(){
				var items = <?php echo json_encode(multisport::$calendarMonthItems ?? new stdClass()); ?>;
				var el = document.getElementById('ac');
				if(!el || typeof AnimatedCalendar === 'undefined') return;
				AnimatedCalendar.mount(el, {
					items: items,
					onSelect: function(ymd){ /* no-op */ },
					footer: "Cliquer sur une date pour repérer les rencontres."
				});
			})();
			</script>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
