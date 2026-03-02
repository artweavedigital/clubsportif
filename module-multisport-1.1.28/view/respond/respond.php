<?php
$r = multisport::$respond;
?>
<style>
.clubCenter{max-width:720px;margin:0 auto}
.clubBtnRow{display:flex;gap:10px;flex-wrap:wrap}
.clubBtnRow a{display:inline-block;padding:10px 14px;border-radius:12px;border:1px solid rgba(0,0,0,.12);text-decoration:none}
.clubBtnGreen{background:rgba(0,128,0,.08)}
.clubBtnRed{background:rgba(200,0,0,.06)}
</style>

<div class="clubCenter">
	<div class="block">
		<h3>Réponse à la convocation</h3>

		<?php if(!$r['ok']): ?>
			<?php echo template::speech($r['message'] ?: 'Lien invalide.'); ?>
		<?php else: ?>
			<p><b>Joueur :</b> <?php echo htmlspecialchars(($r['player']['lastName'] ?? '') . ' ' . ($r['player']['firstName'] ?? '')); ?></p>
			<p><b>Événement :</b> <?php echo htmlspecialchars($r['event']['title'] ?? ''); ?><br>
			<b>Quand :</b> <?php echo date('d/m/Y H:i', (int)($r['event']['start'] ?? 0)); ?><br>
			<b>Lieu :</b> <?php echo htmlspecialchars($r['event']['location'] ?? ''); ?></p>

			<?php if($r['message']): ?>
				<?php echo template::speech($r['message']); ?>
			<?php endif; ?>

			<div class="clubBtnRow">
				<a class="clubBtnGreen" href="<?php echo helper::baseUrl() . $this->getUrl(0) . '/respond/' . $this->getUrl(2) . '/present'; ?>">Présent</a>
				<a class="clubBtnRed" href="<?php echo helper::baseUrl() . $this->getUrl(0) . '/respond/' . $this->getUrl(2) . '/absent'; ?>">Absent</a>
			</div>

			<div style="margin-top:14px;opacity:.85;">
				<b>Statut actuel :</b> <?php echo htmlspecialchars($r['status'] ?? 'pending'); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
