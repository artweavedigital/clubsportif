<?php include 'module/multisport/view/_partials/nav.php'; ?>
<style>.clubPhoto{width:100%;max-width:220px;border-radius:14px;border:1px solid rgba(0,0,0,.12)}</style>
<?php
$edit = multisport::$editPlayer;
$teams = multisport::$teamsTable ?? ['' => '—'];
$pageId = $this->getUrl(0);
$signIdx = (array) $this->getData(['module',$pageId,'signIndex']);
?>

<?php echo template::formOpen('msPlayer', ['enctype'=>'multipart/form-data']); ?>
<div class="row"><div class="col12"><div class="block">
	<h3><?php echo $edit ? 'Éditer un membre' : 'Ajouter un membre'; ?></h3>
	<?php echo template::hidden('clubPlayerId', ['value'=>$edit['id'] ?? '']); ?>

	<div class="row">
		<div class="col3"><?php echo template::file('clubPlayerPhotoSelect', ['label'=>'Photo (médiathèque)','type'=>1,'value'=>$edit['photo'] ?? '' ]); ?></div>
		<div class="col3"><?php echo template::text('clubPlayerLast',['label'=>'Nom','value'=>$edit['lastName'] ?? '' ]); ?></div>
		<div class="col3"><?php echo template::text('clubPlayerFirst',['label'=>'Prénom','value'=>$edit['firstName'] ?? '' ]); ?></div>
		<div class="col3"><?php echo template::date('clubPlayerBirth',['label'=>'Date de naissance','type'=>'date','value'=>(!empty($edit['birth']) ? (int)$edit['birth'] : '') ]); ?></div>
	</div>

	<div class="row">
		<div class="col4"><?php echo template::select('clubPlayerTeam', $teams, ['label'=>'Équipe','selected'=>$edit['teamId'] ?? '' ]); ?></div>
		<div class="col2"><?php echo template::text('clubPlayerTeamCategory', ['label'=>'Catégorie','placeholder'=>'U8','value'=>$edit['teamCategory'] ?? '' ]); ?></div>
		<div class="col3"><?php echo template::select('clubPlayerFeesStatus', [''=>'—','yes'=>'Oui','no'=>'Non'], ['label'=>'Cotisation','selected'=>$edit['feesStatus'] ?? '' ]); ?></div>
		<div class="col3"><?php echo template::text('clubPlayerFeesNote', ['label'=>'Note cotisation','value'=>$edit['feesNote'] ?? '' ]); ?></div>
	</div>

	<div class="row">
		<div class="col6"><?php echo template::upload('clubPlayerPhotoUpload',['label'=>'Uploader une photo','accept'=>'image/*','multiple'=>false,'uploadText'=>'Choisir une image']); ?></div>
		<div class="col6">
			<?php if(!empty($edit['photo'])): ?>
				<div style="padding-top:26px;"><img class="clubPhoto" src="<?php echo helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($edit['photo']); ?>" alt=""></div>
			<?php endif; ?>
		</div>
	</div>

	<h4>Parents</h4>
	<div class="row">
		<div class="col4"><?php echo template::text('clubPlayerP1Name',['label'=>'Parent 1 — Nom','value'=>$edit['parent1Name'] ?? '' ]); ?></div>
		<div class="col4"><?php echo template::text('clubPlayerP1Phone',['label'=>'Parent 1 — Téléphone','value'=>$edit['parent1Phone'] ?? '' ]); ?></div>
		<div class="col4"><?php echo template::text('clubPlayerP1Mail',['label'=>'Parent 1 — Email','value'=>$edit['parent1Mail'] ?? '' ]); ?></div>
	</div>
	<div class="row">
		<div class="col4"><?php echo template::text('clubPlayerP2Name',['label'=>'Parent 2 — Nom','value'=>$edit['parent2Name'] ?? '' ]); ?></div>
		<div class="col4"><?php echo template::text('clubPlayerP2Phone',['label'=>'Parent 2 — Téléphone','value'=>$edit['parent2Phone'] ?? '' ]); ?></div>
		<div class="col4"><?php echo template::text('clubPlayerP2Mail',['label'=>'Parent 2 — Email','value'=>$edit['parent2Mail'] ?? '' ]); ?></div>
	</div>

	<div class="row">
		<div class="col12"><?php echo template::textarea('clubPlayerNotes',['label'=>'Notes internes','value'=>$edit['notes'] ?? '' ]); ?></div>
	</div>

	<h4>Dossier — documents & justificatifs</h4>
	<div class="row">
		<div class="col6"><?php echo template::upload('clubPlayerDocsUpload',['label'=>'Ajouter des fichiers','accept'=>'.pdf,.jpg,.jpeg,.png','multiple'=>true,'uploadText'=>'Ajouter des fichiers']); ?></div>
		<div class="col6">
			<?php if(!empty($edit['docs'])): ?>
				<div class="block" style="margin-top:0;">
					<h4>Fichiers</h4>
					<?php foreach($edit['docs'] as $i => $rel): ?>
						<div style="display:flex;gap:8px;align-items:center;margin:6px 0;">
							<div style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
								<a href="<?php echo helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($rel); ?>" target="_blank"><?php echo htmlspecialchars(basename($rel)); ?></a>
							</div>
							<div><?php echo template::button('msDocDel'.$i,['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/playerDocDelete/'.($edit['id'] ?? '').'/'.$i,'value'=>template::ico('trash'),'help'=>'Supprimer']); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: echo template::speech('Aucun fichier sélectionné'); endif; ?>
		</div>
	</div>

	<div class="row"><div class="col3 offset9"><?php echo template::submit('msPlayerSubmit',['value'=>$edit?'Mettre à jour':'Créer']); ?></div></div>
</div></div></div>

<script>
(function(){
	const sel = document.getElementsByName('clubPlayerTeam')[0];
	const cat = document.getElementsByName('clubPlayerTeamCategory')[0];
	if(!sel || !cat) return;
	const map = <?php $m=[]; foreach((array)multisport::$teams as $id=>$t){ $m[$id]=(string)($t['category']??''); } echo json_encode($m); ?>;
	function sync(){
		if(!cat.value && sel.value && map[sel.value]) cat.value = map[sel.value];
	}
	sel.addEventListener('change', function(){
		if(map[sel.value]) cat.value = map[sel.value];
	});
	sync();
})();
</script>

<?php echo template::formClose(); ?>

<div class="row"><div class="col12"><div class="block">
	<h3>Membres</h3>
	<?php if(!empty(multisport::$playersTable)): echo template::table([1,3,3,2,2,1,1], multisport::$playersTable, ['','Membre','Équipe','Parent 1','Parent 2','','']); else: echo template::speech('Aucun membre.'); endif; ?>
</div></div></div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
