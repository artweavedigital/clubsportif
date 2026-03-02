<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php echo template::formOpen('clubConfigForm'); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Configuration</h3>
			<div class="row">
				<div class="col6">
					<?php echo template::text('clubConfigName', [
						'label' => 'Nom du club',
						'value' => multisport::$config['clubName'] ?? 'Mon club'
					]); ?>
				</div>
				<div class="col3">
					<?php echo template::select('clubConfigMailMode', [
						'simulation' => 'Simulation (aucun email envoyé)',
						'email' => 'Email (envoi réel)'
					], [
						'label' => 'Mode email',
						'selected' => multisport::$config['mailMode'] ?? 'simulation'
					]); ?>
				</div>
				<div class="col3">
					<?php echo template::text('clubConfigMailFrom', [
						'label' => 'Email “from” (optionnel)',
						'placeholder' => 'Sinon : config SMTP du site',
						'value' => multisport::$config['mailFrom'] ?? ''
					]); ?>
				</div>
			</div>

			<div class="row">
				<div class="col6">
					<?php echo template::text('clubConfigFullName', [
						'label' => 'Nom complet',
						'value' => multisport::$config['clubFullName'] ?? ''
					]); ?>
				</div>
				<div class="col6">
					<?php echo template::text('clubConfigSigle', [
						'label' => 'Sigle',
						'value' => multisport::$config['clubSigle'] ?? ''
					]); ?>
				</div>
			</div>

			<div class="row">
				<div class="col6">
					<?php echo template::text('clubConfigSport', [
						'label' => 'Sport',
						'placeholder' => 'Ex : football, basket, hand, hockey…',
						'value' => multisport::$config['clubSport'] ?? ''
					]); ?>
				</div>
				<div class="col6"></div>
			</div>


			<div class="row">
				<div class="col6">
					<?php echo template::text('clubConfigFederation', [
						'label' => 'Fédération',
						'value' => multisport::$config['clubFederation'] ?? ''
					]); ?>
				</div>
				<div class="col3">
					<?php echo template::text('clubConfigVat', [
						'label' => 'Numéro de TVA',
						'value' => multisport::$config['clubVat'] ?? ''
					]); ?>
				</div>
				<div class="col3">
					<?php echo template::text('clubConfigIban', [
						'label' => 'IBAN',
						'value' => multisport::$config['clubIban'] ?? ''
					]); ?>
				</div>
			</div>


			<div class="row">
				<div class="col6">
					<?php echo template::text('clubConfigColorPrimary', [
						'label' => 'Couleur principale (hex)',
						'placeholder' => '#2b5bd7',
						'value' => multisport::$config['clubColorPrimary'] ?? ''
					]); ?>
				</div>
				<div class="col6">
					<?php echo template::text('clubConfigColorSecondary', [
						'label' => 'Couleur secondaire (hex)',
						'placeholder' => '#111827',
						'value' => multisport::$config['clubColorSecondary'] ?? ''
					]); ?>
				</div>
			</div>


			<div class="row">
				<div class="col6">
					<?php echo template::file('clubConfigLogo', [
						'label' => 'Logo (médiathèque)',
						'type'  => 1,
						'value' => multisport::$config['clubLogo'] ?? ''
					]); ?>
				</div>
				<div class="col6">
					<?php echo template::text('clubConfigTagline', [
						'label' => 'Accroche (optionnel)',
						'placeholder' => 'Ex : École de football — Saison 2026',
						'value' => multisport::$config['clubTagline'] ?? ''
					]); ?>
				</div>
			</div>
			
			<div class="row">
				<div class="col12">
					<?php echo template::file('clubConfigBanner', [
						'label' => 'Bannière (1200 × 300) — sélection médiathèque',
						'type'  => 1,
						'value' => multisport::$config['clubBanner'] ?? ''
					]); ?>
				</div>
			</div>
<div class="row">
				<div class="col3">
					<?php echo template::number('clubConfigTokenTtl', [
						'label' => 'Durée de validité des tokens (jours)',
						'min' => 1,
						'max' => 365,
						'value' => multisport::$config['tokenTtlDays'] ?? 45
					]); ?>
				</div>
				<div class="col9">
					<?php echo template::speech('Astuce : laissez en “simulation” tant que vous testez. Les liens token restent fonctionnels.'); ?>
				</div>
			</div>
			<div class="row">
				<div class="col2 offset10">
					<?php echo template::submit('clubConfigSubmit', ['value' => 'Enregistrer']); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
