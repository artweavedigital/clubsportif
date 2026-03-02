<?php include 'module/multisport/view/_partials/nav.php'; ?>
<?php $edit = multisport::$editStaff; $teams = multisport::$teamsSelect ?? [''=>'—']; ?>

<?php echo template::formOpen('msStaff'); ?>
<div class="row"><div class="col12"><div class="block">
	<h3><?php echo $edit ? 'Éditer un membre du staff' : 'Ajouter un membre du staff'; ?></h3>
	<?php echo template::hidden('clubStaffId', ['value'=>$edit['id'] ?? '']); ?>

	<div class="row">
		<div class="col3"><?php echo template::file('clubStaffPhoto', ['label'=>'Photo','type'=>1,'value'=>$edit['photo'] ?? '']); ?></div>
		<div class="col3"><?php echo template::text('clubStaffLast', ['label'=>'Nom','value'=>$edit['lastName'] ?? '']); ?></div>
		<div class="col3"><?php echo template::text('clubStaffFirst', ['label'=>'Prénom','value'=>$edit['firstName'] ?? '']); ?></div>
		<div class="col3"><?php echo template::text('clubStaffRole', ['label'=>'Fonction','value'=>$edit['role'] ?? '']); ?></div>
	</div>

	<div class="row">
		<div class="col4"><?php echo template::text('clubStaffMail', ['label'=>'Email','value'=>$edit['mail'] ?? '' ]); ?></div>
		<div class="col4"><?php echo template::text('clubStaffPhone', ['label'=>'Téléphone','value'=>$edit['phone'] ?? '' ]); ?></div>
		<div class="col2"><?php echo template::select('clubStaffTeam', $teams, ['label'=>'Équipe','selected'=>$edit['teamId'] ?? '' ]); ?></div>
		<div class="col2"><?php echo template::text('clubStaffTeamCategory', ['label'=>'Catégorie','placeholder'=>'U8','value'=>$edit['teamCategory'] ?? '' ]); ?></div>
	</div>

	<div class="row"><div class="col3 offset9"><?php echo template::submit('msStaffSubmit',['value'=>$edit?'Mettre à jour':'Créer']); ?></div></div>
</div></div></div>

<script>
(function(){
	const sel = document.getElementsByName('clubStaffTeam')[0];
	const cat = document.getElementsByName('clubStaffTeamCategory')[0];
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
	<h3>Staff</h3>
	<?php if(!empty(multisport::$staffTable)): echo template::table([1,3,3,3,1,1], multisport::$staffTable, ['','Nom','Fonction','Équipe','','']); else: echo template::speech('Aucun staff.'); endif; ?>
</div></div></div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
