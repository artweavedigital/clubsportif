<?php
$base = helper::baseUrl() . $this->getUrl(0) . '/';
$action = (string) ($this->getUrl(1) ?? 'index');
if ($action === '') { $action = 'index'; }

$cfg = multisport::$config ?? [];
$logo = (string) ($cfg['clubLogo'] ?? '');
$tagline = (string) ($cfg['clubTagline'] ?? '');

$banner = (string) ($cfg['clubBanner'] ?? '');
$bannerUrl = '';
if ($banner !== '') {
	$bannerUrl = helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($banner);
}

$logoUrl = '';
if ($logo !== '') {
	$logoUrl = helper::baseUrl(false) . multisport::FILE_DIR . 'source/' . htmlspecialchars($logo);
}

$tabs = [
	['slug'=>'index', 'label'=>'', 'ico'=>'home', 'title'=>'Vue d’ensemble'],
	['slug'=>'club', 'label'=>'Le club', 'ico'=>'flag'],
	['slug'=>'teams', 'label'=>'Équipes', 'ico'=>'tag'],
	['slug'=>'players', 'label'=>'Membres', 'ico'=>'users'],
	['slug'=>'staff', 'label'=>'Staff', 'ico'=>'user'],
	['slug'=>'events', 'label'=>'Événements', 'ico'=>'calendar'],
	['slug'=>'calendar', 'label'=>'Calendrier — rencontres', 'ico'=>'calendar'],
	['slug'=>'config', 'label'=>'Paramètres', 'ico'=>'sliders'],
];
?>
<style>
/* UI “RC Mouscron” — sobre, card, tabs */
.msShell{max-width:1040px;margin:0 auto}
.msHero{max-width:1200px;height:300px;margin:0 auto 14px;border-radius:18px;overflow:hidden;border:1px solid rgba(0,0,0,.08);background:linear-gradient(135deg,#ffffff,#eef1f6);position:relative}
.msHero img{width:100%;height:100%;object-fit:cover;display:block}
.msHero::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.0),rgba(0,0,0,.06))}
.msCard{border:1px solid rgba(0,0,0,.08);border-radius:18px;background:#fff;box-shadow:0 10px 28px rgba(0,0,0,.06);overflow:hidden}
.msHeader{display:flex;gap:14px;align-items:center;padding:16px 18px;background:linear-gradient(135deg,#ffffff,#f5f6f8)}
.msLogo{width:52px;height:52px;border-radius:16px;object-fit:cover;border:1px solid rgba(0,0,0,.12);background:#fff}
.msName{font-weight:700;font-size:1.18rem;color:<?php echo htmlspecialchars((string)($cfg['clubColorPrimary'] ?? '#2b5bd7')); ?>;line-height:1.1}
.msTag{opacity:.72;margin-top:2px}
.msTabs{display:flex;gap:10px;flex-wrap:wrap;padding:10px 14px 14px;background:#fff;border-top:1px solid rgba(0,0,0,.06)}
.msTabs a{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;text-decoration:none;color:inherit;border:1px solid transparent}
.msTabs a:hover{background:#f4f5f7;border-color:rgba(0,0,0,.08)}
.msTabs a.isActive{background:#f2f4f8;border-color:rgba(0,0,0,.10)}
.msTabs .ico{opacity:.8}
.msTabs a.msHome{padding:8px 10px}
.msTabs a.msHome .ico{transform:scale(1.35);transform-origin:center}
.msTabs a.msHome span.label{display:none}
.msTopActions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin:16px 0 6px}
.msBtn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:14px;border:1px solid rgba(0,0,0,.10);background:#fff;text-decoration:none;color:inherit}
.msBtn:hover{background:#f5f6f8}
.msBtnPrimary{background:#1f2937;color:#fff;border-color:#1f2937}
.msBtnPrimary:hover{background:#111827}
</style>

<div class="msShell">
	<?php if ($bannerUrl !== ''): ?>
		<div class="msHero"><img src="<?php echo $bannerUrl; ?>" alt=""></div>
	<?php endif; ?>
	<div class="msCard">
		<div class="msHeader">
			<?php if ($logoUrl !== ''): ?>
				<img class="msLogo" src="<?php echo $logoUrl; ?>" alt="">
			<?php else: ?>
				<div class="msLogo" style="display:flex;align-items:center;justify-content:center;">
					<span style="opacity:.45;"><?php echo template::ico('shield'); ?></span>
				</div>
			<?php endif; ?>

			<div style="flex:1;">
				<div class="msName"><?php echo htmlspecialchars(multisport::$clubName); ?></div>
				<div class="msTag"><?php echo $tagline !== '' ? htmlspecialchars($tagline) : 'Sections, événements, membres — tout au même endroit.'; ?></div>
			</div>
		</div>

		<nav class="msTabs" aria-label="Navigation club">
			<?php foreach ($tabs as $t):
				$href = $t['slug'] === 'index' ? $base : $base . $t['slug'];
				$active = ($action === $t['slug']) || ($action === '' && $t['slug'] === 'index');
			?>
				<a class="<?php echo ($t['slug']==='index'?'msHome ':'') . ($active ? 'isActive' : ''); ?>" href="<?php echo $href; ?>" <?php echo !empty($t['title']) ? 'title="'.htmlspecialchars($t['title']).'"' : ''; ?>>
					<span class="ico"><?php echo template::ico($t['ico']); ?></span>
					<?php if (!empty($t['label'])): ?><span class="label"><?php echo htmlspecialchars($t['label']); ?></span><?php endif; ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>
</div>
