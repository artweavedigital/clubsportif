<?php include 'module/multisport/view/_partials/nav.php'; ?>

<?php
$d = (array) (multisport::$dash ?? []);
$logo = (string)($d['logo'] ?? '');
$logoUrl = $logo ? (helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($logo)) : '';
?>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Vue d’ensemble</h3>
			<p style="opacity:.75;margin-top:0;">Les informations essentielles du club, en un coup d’œil.</p>
		</div>
	</div>
</div>

<div class="row">
	<div class="col6">
		<div class="block">
			<h3>Club</h3>

			<div style="display:flex;gap:14px;align-items:center;">
				<?php if($logoUrl): ?>
					<img src="<?php echo $logoUrl; ?>" alt="" style="width:72px;height:72px;object-fit:contain;border-radius:16px;border:1px solid rgba(0,0,0,.10);background:#fff;padding:8px;">
				<?php else: ?>
					<div style="width:72px;height:72px;border-radius:16px;border:1px dashed rgba(0,0,0,.20);display:flex;align-items:center;justify-content:center;opacity:.55;">
						<?php echo template::ico('image'); ?>
					</div>
				<?php endif; ?>

				<div style="flex:1;">
					<div style="opacity:.72;">TVA</div>
					<div style="font-weight:800;"><?php echo htmlspecialchars((string)($d['vat'] ?? '—')); ?></div>
				</div>

				<div style="flex:1;">
					<div style="opacity:.72;">IBAN</div>
					<div style="font-weight:800;"><?php echo htmlspecialchars((string)($d['iban'] ?? '—')); ?></div>
				</div>
			</div>

			<hr style="border:0;border-top:1px solid rgba(0,0,0,.08);margin:14px 0;">

			<div style="display:grid;grid-template-columns:1fr;gap:8px;">
				<div><span style="opacity:.72;">Adresse — </span><?php echo htmlspecialchars((string)($d['addr'] ?? '—')); ?></div>
				<div><span style="opacity:.72;">Téléphone — </span><?php echo htmlspecialchars((string)($d['phone'] ?? '—')); ?></div>
				<div><span style="opacity:.72;">Email — </span><?php echo htmlspecialchars((string)($d['mail'] ?? '—')); ?></div>
			</div>

			<div style="margin-top:12px;">
				<?php echo template::button('msGoConfig', [
					'href' => helper::baseUrl() . $this->getUrl(0) . '/config',
					'value' => template::ico('cog') . ' Paramètres'
				]); ?>
			</div>
		</div>
	</div>

	<div class="col6">
		<div class="block">
			<h3>Membres</h3>

			<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
				<div>
					<div style="opacity:.72;">Total membres</div>
					<div style="font-weight:900;font-size:1.6rem;"><?php echo (int)($d['playersCount'] ?? 0); ?></div>
				</div>

				<div>
					<?php echo template::button('msNewPlayer', [
						'href' => helper::baseUrl() . $this->getUrl(0) . '/players',
						'value' => template::ico('plus') . ' Nouvelle inscription'
					]); ?>
				</div>
			</div>

			<hr style="border:0;border-top:1px solid rgba(0,0,0,.08);margin:14px 0;">

			<h4 style="margin:0 0 10px;">Prochaines rencontres</h4>
			<?php $up = (array)($d['upcoming'] ?? []); ?>
			<?php if(empty($up)): ?>
				<?php echo template::speech('Aucune rencontre à venir.'); ?>
			<?php else: ?>
				<ul style="margin:0;padding-left:18px;">
					<?php foreach($up as $eid => $e): ?>
						<li style="margin:6px 0;">
							<b><?php echo htmlspecialchars(date('d/m/Y H:i', (int)($e['start'] ?? 0))); ?></b>
							— <?php echo htmlspecialchars((string)($e['opponent'] ?? 'Rencontre')); ?>
							<?php if(!empty($e['homeAway'])): ?>
								<span style="opacity:.7;">(<?php echo ($e['homeAway']==='away'?'Extérieur':($e['homeAway']==='home'?'Domicile':$e['homeAway'])); ?>)</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>

				<div style="margin-top:10px;">
					<?php echo template::button('msGoCal', [
						'href' => helper::baseUrl() . $this->getUrl(0) . '/calendar',
						'value' => template::ico('calendar') . ' Rencontres'
					]); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Événements</h3>
			<p style="opacity:.75;margin-top:0;">(Réserve — on le remplit au prochain pas.)</p>
		</div>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
