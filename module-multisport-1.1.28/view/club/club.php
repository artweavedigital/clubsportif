<?php include 'module/multisport/view/_partials/nav.php'; ?>
<?php
$c = (array)(multisport::$clubProfile ?? []);
$infra = (array)($c['infrastructures'] ?? []);
$org = (array)($c['organigramme'] ?? []);
$fin = (array)($c['finances'] ?? []);
$tarifs = (array)($fin['tarifs'] ?? []);
$reds = (array)($fin['reductions'] ?? []);
$sps = (array)($c['sponsors'] ?? []);
?>

<?php echo template::formOpen('clubProfileForm'); ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Le club — fiche</h3>
			<h4>2) Coordonnées & siège social</h4>
			<div class="row">
				<div class="col8"><?php echo template::text('clubAddrStreet', ['label'=>'Adresse','value'=>$c['addrStreet'] ?? '']); ?></div>
				<div class="col2"><?php echo template::text('clubAddrZip', ['label'=>'Code postal','value'=>$c['addrZip'] ?? '']); ?></div>
				<div class="col2"><?php echo template::text('clubAddrCity', ['label'=>'Ville','value'=>$c['addrCity'] ?? '']); ?></div>
			</div>
			<div class="row">
				<div class="col4"><?php echo template::text('clubAddrCountry', ['label'=>'Pays','value'=>$c['addrCountry'] ?? '']); ?></div>
				<div class="col4"><?php echo template::text('clubContactEmail', ['label'=>'Email officiel','value'=>$c['contactEmail'] ?? '']); ?></div>
				<div class="col4"><?php echo template::text('clubContactPhone', ['label'=>'Téléphone secrétariat','value'=>$c['contactPhone'] ?? '']); ?></div>
			</div>
			<div class="row">
				<div class="col6"><?php echo template::text('clubLat', ['label'=>'Latitude','value'=>$c['lat'] ?? '' ]); ?></div>
				<div class="col6"><?php echo template::text('clubLng', ['label'=>'Longitude','value'=>$c['lng'] ?? '' ]); ?></div>
			</div>

			<div class="row">
				<div class="col4"><?php echo template::text('clubWeb', ['label'=>'Site web','placeholder'=>'https://…','value'=>$c['web'] ?? '']); ?></div>
				<div class="col4"><?php echo template::text('clubFacebook', ['label'=>'Facebook','placeholder'=>'https://…','value'=>$c['facebook'] ?? '']); ?></div>
				<div class="col4"><?php echo template::text('clubInstagram', ['label'=>'Instagram','placeholder'=>'https://…','value'=>$c['instagram'] ?? '']); ?></div>
			</div>

			<h4>5) Paramètres financiers</h4>
			<div class="row">
				<div class="col4"><?php echo template::text('clubIban', ['label'=>'IBAN','value'=>$fin['iban'] ?? '']); ?></div>
				<div class="col4"><?php echo template::text('clubBic', ['label'=>'BIC','value'=>$fin['bic'] ?? '']); ?></div>
				<div class="col4"><?php echo template::text('clubRib', ['label'=>'RIB (optionnel)','value'=>$fin['rib'] ?? '']); ?></div>
			</div>

			<h4>6) Documents & chartes</h4>
			<div class="row">
				<div class="col6"><?php echo template::file('clubRulesPdf', ['label'=>'Règlement intérieur (PDF)','type'=>1,'value'=>$c['rulesPdf'] ?? '']); ?></div>
				<div class="col6"><?php echo template::file('clubProjectPdf', ['label'=>'Projet de club (PDF)','type'=>1,'value'=>$c['projectPdf'] ?? '']); ?></div>
			</div>

			<div class="row">
				<div class="col3 offset9"><?php echo template::submit('clubProfileSave', ['value'=>'Enregistrer']); ?></div>
			</div>
		</div>
	</div>
</div>
<?php echo template::formClose(); ?>

<div class="row"><div class="col12"><div class="block">
	<h3>3) Infrastructures</h3>

	<?php if(empty($infra)): ?>
		<?php echo template::speech('Aucune infrastructure.'); ?>
	<?php else: foreach($infra as $x): ?>
		<div style="display:flex;gap:10px;align-items:flex-start;margin:10px 0;padding:12px;border:1px solid rgba(0,0,0,.08);border-radius:14px;background:#fff;">
			<div style="flex:1;">
				<div style="font-weight:700;"><?php echo htmlspecialchars((string)($x['name'] ?? '')); ?></div>
				<div style="opacity:.8;">Adresse : <?php echo htmlspecialchars((string)($x['address'] ?? '')); ?><br><span style="opacity:.85;"><?php echo !empty($x['hasBar']) ? '☑ Buvette' : '☐ Buvette'; ?> — <?php echo !empty($x['hasLocker']) ? '☑ Vestiaires' : '☐ Vestiaires'; ?></span></div>
				<div style="opacity:.75;">GPS : <?php echo htmlspecialchars((string)($x['lat'] ?? '')); ?>, <?php echo htmlspecialchars((string)($x['lng'] ?? '')); ?></div>
			</div>
			<div><?php echo template::button('delInfra'.$x['id'], ['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/clubDel/infra/'.($x['id'] ?? ''),'value'=>template::ico('trash')]); ?></div>
		</div>
	<?php endforeach; endif; ?>

	<?php echo template::formOpen('infraAddForm'); ?>
	<?php echo template::hidden('clubAction', ['value'=>'infraAdd']); ?>
	<h4>Ajouter</h4>
	<div class="row">
		<div class="col6"><?php echo template::text('infraName', ['label'=>'Nom du terrain','required'=>true]); ?></div>
		<div class="col6"><?php echo template::text('infraAddress', ['label'=>'Adresse']); ?></div>
	</div>
	<div class="row">
		<div class="col3" style="padding-top:28px;"><?php echo template::checkbox('infraHasLocker', false, 'Vestiaires'); ?></div>
		<div class="col3" style="padding-top:28px;"><?php echo template::checkbox('infraHasBar', false, 'Buvette'); ?></div>
		<div class="col2"><?php echo template::text('infraLat', ['label'=>'Latitude']); ?></div>
		<div class="col2"><?php echo template::text('infraLng', ['label'=>'Longitude']); ?></div>
	</div>
	<div class="row">
		<div class="col3 offset9"><?php echo template::submit('infraAddBtn', ['value'=>'Ajouter']); ?></div>
	</div>
	<?php echo template::formClose(); ?>
</div></div></div>

<div class="row"><div class="col12"><div class="block">
	<h3>4) Organigramme</h3>

	<?php if(empty($org)): ?>
		<?php echo template::speech('Aucun responsable.'); ?>
	<?php else: ?>
		<div style="display:flex;gap:12px;flex-wrap:wrap;">
			<?php foreach($org as $x):
				$ph=(string)($x['photo'] ?? '');
				$url=$ph ? (helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($ph)) : '';
			?>
				<div style="width:220px;border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:12px;background:#fff;">
					<?php if($url): ?><img src="<?php echo $url; ?>" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:12px;border:1px solid rgba(0,0,0,.10);"><?php endif; ?>
					<div style="margin-top:8px;font-weight:800;"><?php echo htmlspecialchars((string)($x['role'] ?? '')); ?></div>
					<div style="font-weight:700;"><?php echo htmlspecialchars((string)($x['name'] ?? '')); ?></div>
					<div style="opacity:.75;"><?php echo htmlspecialchars((string)($x['mail'] ?? '')); ?></div>
					<div style="opacity:.75;"><?php echo htmlspecialchars((string)($x['phone'] ?? '')); ?></div>
					<div style="margin-top:8px;"><?php echo template::button('delOrg'.$x['id'], ['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/clubDel/org/'.($x['id'] ?? ''),'value'=>template::ico('trash')]); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php echo template::formOpen('orgAddForm'); ?>
	<?php echo template::hidden('clubAction', ['value'=>'orgAdd']); ?>
	<h4>Ajouter</h4>
	<div class="row">
		<div class="col4"><?php echo template::text('orgRole', ['label'=>'Rôle (Président, Trésorier, Secrétaire…)','required'=>true]); ?></div>
		<div class="col4"><?php echo template::text('orgLast', ['label'=>'Nom','required'=>true]); ?></div>
		<div class="col4"><?php echo template::text('orgFirst', ['label'=>'Prénom','required'=>true]); ?></div>
		<div class="col4"><?php echo template::file('orgPhoto', ['label'=>'Photo — Sélectionner un fichier','type'=>1]); ?></div>
	</div>
	<div class="row">
		<div class="col6"><?php echo template::text('orgMail', ['label'=>'Email']); ?></div>
		<div class="col6"><?php echo template::text('orgPhone', ['label'=>'Téléphone']); ?></div>
	</div>
	<div class="row">
		<div class="col3 offset9"><?php echo template::submit('orgAddBtn', ['value'=>'Ajouter']); ?></div>
	</div>
	<?php echo template::formClose(); ?>
</div></div></div>

<div class="row"><div class="col12"><div class="block">
	<h3>Tarifs licences</h3>

	<?php if(empty($tarifs)): echo template::speech('Aucun tarif.'); else: ?>
		<ul style="margin:0;padding-left:18px;">
			<?php foreach($tarifs as $t): ?>
				<li><?php echo htmlspecialchars((string)($t['category'] ?? '')); ?> — <?php echo htmlspecialchars((string)($t['price'] ?? '')); ?>
					<?php echo template::button('delTar'.$t['id'], ['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/clubDel/tarif/'.($t['id'] ?? ''),'value'=>template::ico('trash')]); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php echo template::formOpen('tarifAddForm'); ?>
	<?php echo template::hidden('clubAction', ['value'=>'tariffAdd']); ?>
	<div class="row">
		<div class="col6"><?php echo template::text('tarifCategory', ['label'=>'Catégorie (U11, Senior…)','required'=>true]); ?></div>
		<div class="col4"><?php echo template::text('tarifPrice', ['label'=>'Prix (ex : 120€)','required'=>true]); ?></div>
		<div class="col2" style="padding-top:26px;"><?php echo template::submit('tarifAddBtn', ['value'=>'Ajouter']); ?></div>
	</div>
	<?php echo template::formClose(); ?>

	<h3>Réductions automatiques</h3>

	<?php if(empty($reds)): echo template::speech('Aucune réduction.'); else: ?>
		<ul style="margin:0;padding-left:18px;">
			<?php foreach($reds as $r): ?>
				<li><?php echo htmlspecialchars((string)($r['label'] ?? '')); ?> — <?php echo htmlspecialchars((string)($r['value'] ?? '')); ?>
					<?php echo template::button('delRed'.$r['id'], ['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/clubDel/reduction/'.($r['id'] ?? ''),'value'=>template::ico('trash')]); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php echo template::formOpen('redAddForm'); ?>
	<?php echo template::hidden('clubAction', ['value'=>'reductionAdd']); ?>
	<div class="row">
		<div class="col6"><?php echo template::text('redLabel', ['label'=>'Libellé','placeholder'=>'2e enfant','required'=>true]); ?></div>
		<div class="col4"><?php echo template::text('redValue', ['label'=>'Valeur (ex : -10%)','required'=>true]); ?></div>
		<div class="col2" style="padding-top:26px;"><?php echo template::submit('redAddBtn', ['value'=>'Ajouter']); ?></div>
	</div>
	<?php echo template::formClose(); ?>
</div></div></div>

<div class="row"><div class="col12"><div class="block">
	<h3>Partenaires / sponsors</h3>

	<?php if(empty($sps)): echo template::speech('Aucun sponsor.'); else: ?>
		<div style="display:flex;gap:12px;flex-wrap:wrap;">
			<?php foreach($sps as $s):
				$ph=(string)($s['logo'] ?? '');
				$url=$ph ? (helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($ph)) : '';
			?>
				<div style="width:220px;border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:12px;background:#fff;">
					<?php if($url): ?><img src="<?php echo $url; ?>" alt="" style="width:100%;height:110px;object-fit:contain;border-radius:12px;border:1px solid rgba(0,0,0,.10);background:#fff;"><?php endif; ?>
					<div style="margin-top:8px;font-weight:700;"><?php echo htmlspecialchars((string)($s['name'] ?? '')); ?></div>
					<div style="opacity:.75;"><?php echo htmlspecialchars((string)($s['mail'] ?? '')); ?><br><span style="opacity:.75;"><?php echo htmlspecialchars((string)($s['phone'] ?? '')); ?></span></div>
					<div style="margin-top:8px;"><?php echo template::button('delSp'.$s['id'], ['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/clubDel/sponsor/'.($s['id'] ?? ''),'value'=>template::ico('trash')]); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php echo template::formOpen('spAddForm'); ?>
	<?php echo template::hidden('clubAction', ['value'=>'sponsorAdd']); ?>
	<div class="row">
		<div class="col4"><?php echo template::file('spLogo', ['label'=>'Logo','type'=>1,'required'=>true]); ?></div>
		<div class="col4"><?php echo template::text('spName', ['label'=>'Nom']); ?></div>
		<div class="col4"><?php echo template::text('spMail', ['label'=>'Email']); ?></div>
	</div>
	<div class="row">
		<div class="col6"><?php echo template::text('spPhone', ['label'=>'Téléphone']); ?></div>
		<div class="col6"></div>
	</div>
	<div class="row">
		<div class="col3 offset9"><?php echo template::submit('spAddBtn', ['value'=>'Ajouter']); ?></div>
	</div>
	<?php echo template::formClose(); ?>
</div></div></div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
