<?php include 'module/multisport/view/_partials/nav.php'; ?>
<div class="row">
	<div class="col12">
		<div class="block">
			<h3>Diagnostic MultiSport (1.1.10)</h3>
			<p style="opacity:.75;margin-top:0;">
				Si tu vois <b>club_has_formSubmit = true</b> ou <b>players_has_bad_checkbox = true</b>,
				alors ton site exécute encore d’anciens fichiers (le ZIP n’a pas remplacé le dossier).
			</p>
			<pre style="white-space:pre-wrap;word-break:break-word;"><?php echo htmlspecialchars(json_encode(multisport::$diag, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
		</div>
	</div>
</div>
<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
