<?php
$s = multisport::$sign;
$token = $this->getUrl(2);
?>
<style>
.clubCenter{max-width:860px;margin:0 auto}
.sigWrap{border:1px solid rgba(0,0,0,.12);border-radius:14px;padding:12px}
canvas{width:100%;height:260px;border:1px dashed rgba(0,0,0,.25);border-radius:12px;touch-action:none}
.sigBtns{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
.sigBtns button{padding:10px 14px;border-radius:12px;border:1px solid rgba(0,0,0,.12);cursor:pointer}
</style>

<div class="clubCenter">
	<div class="block">
		<h3>Signature électronique</h3>

		<?php if(!$s['ok']): ?>
			<?php echo template::speech($s['message'] ?: 'Lien invalide.'); ?>
		<?php else: ?>
			<p><b>Joueur :</b> <?php echo htmlspecialchars(($s['player']['lastName'] ?? '') . ' ' . ($s['player']['firstName'] ?? '')); ?></p>
			<p><b>Document :</b> <?php echo htmlspecialchars($s['label'] ?? ''); ?></p>

			<?php if(!empty($s['signatureFile'])): ?>
				<?php echo template::speech('Signature déjà enregistrée.'); ?>
				<p>
					<a target="_blank" href="<?php echo helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($s['signatureFile']); ?>">Voir la signature (PNG)</a>
				</p>
			<?php endif; ?>

			<?php echo template::formOpen('clubSignForm'); ?>
				<?php echo template::hidden('clubSignData'); ?>

				<div class="sigWrap">
					<p style="margin-top:0;opacity:.85;">Signez dans le cadre puis validez.</p>
					<canvas id="sigCanvas" width="1200" height="420"></canvas>

					<div class="sigBtns">
						<button type="button" id="sigClear">Effacer</button>
						<button type="submit" id="sigSubmit">Valider</button>
					</div>
				</div>
			<?php echo template::formClose(); ?>

			<script>
			(function(){
				const canvas = document.getElementById('sigCanvas');
				const ctx = canvas.getContext('2d');
				ctx.lineWidth = 3;
				ctx.lineCap = 'round';

				let drawing = false;
				let last = null;

				function pos(e){
					const r = canvas.getBoundingClientRect();
					if(e.touches && e.touches[0]){
						return {x:(e.touches[0].clientX - r.left) * (canvas.width / r.width),
								y:(e.touches[0].clientY - r.top)  * (canvas.height/ r.height)};
					}
					return {x:(e.clientX - r.left) * (canvas.width / r.width),
							y:(e.clientY - r.top)  * (canvas.height/ r.height)};
				}

				function start(e){ drawing = true; last = pos(e); e.preventDefault(); }
				function move(e){
					if(!drawing) return;
					const p = pos(e);
					ctx.beginPath();
					ctx.moveTo(last.x,last.y);
					ctx.lineTo(p.x,p.y);
					ctx.stroke();
					last = p;
					e.preventDefault();
				}
				function end(e){ drawing=false; last=null; e.preventDefault(); }

				canvas.addEventListener('mousedown', start);
				canvas.addEventListener('mousemove', move);
				canvas.addEventListener('mouseup', end);
				canvas.addEventListener('mouseleave', end);

				canvas.addEventListener('touchstart', start, {passive:false});
				canvas.addEventListener('touchmove', move, {passive:false});
				canvas.addEventListener('touchend', end, {passive:false});

				document.getElementById('sigClear').addEventListener('click', function(){
					ctx.clearRect(0,0,canvas.width,canvas.height);
				});

				document.getElementById('clubSignForm').addEventListener('submit', function(){
					document.getElementsByName('clubSignData')[0].value = canvas.toDataURL('image/png');
				});
			})();
			</script>
		<?php endif; ?>
	</div>
</div>

<div class="moduleVersion">Version n° <?php echo multisport::VERSION; ?></div>
