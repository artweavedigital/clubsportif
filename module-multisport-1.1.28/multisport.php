<?php
// HOTFIX 1.1.10 — parse safety + clear opcache timestamps

/**
 * MultiSport — Gestion privée (parents/joueurs)
 * PHP 8.1 → 8.3
 */
class multisport extends common {

	const VERSION = '1.1.28';
	const REALNAME = 'MultiSport — Espace club';
	const DATADIRECTORY = '';
	const UPDATE = '0.0';
	const DELETE = true;

	const FILE_DIR = 'site/file/';
	const UPLOAD_ROOT = 'club';

	public static $actions = [
		'index' => self::ROLE_VISITOR,
		'config' => self::ROLE_EDITOR,
		'club' => self::ROLE_EDITOR,
		'clubDel' => self::ROLE_EDITOR,
		'diag' => self::ROLE_EDITOR,


		'players' => self::ROLE_EDITOR,
		'playerSave' => self::ROLE_EDITOR,
		'playerDelete' => self::ROLE_EDITOR,
		'playerDocDelete' => self::ROLE_EDITOR,

		'signatureCreate' => self::ROLE_EDITOR,
		'signatureDelete' => self::ROLE_EDITOR,

		'staff' => self::ROLE_EDITOR,
		'staffSave' => self::ROLE_EDITOR,
		'staffDelete' => self::ROLE_EDITOR,

		'teams' => self::ROLE_EDITOR,
		'teamSave' => self::ROLE_EDITOR,
		'teamDelete' => self::ROLE_EDITOR,

		'events' => self::ROLE_EDITOR,
		'eventSave' => self::ROLE_EDITOR,
		'eventDelete' => self::ROLE_EDITOR,
		'calendar' => self::ROLE_EDITOR,
		'calendarSave' => self::ROLE_EDITOR,

		'convocations' => self::ROLE_EDITOR,
		'convocationSend' => self::ROLE_EDITOR,
		'convocationDelete' => self::ROLE_EDITOR,

		'carpool' => self::ROLE_EDITOR,
		'carpoolSave' => self::ROLE_EDITOR,
		'carpoolDelete' => self::ROLE_EDITOR,

		'volunteers' => self::ROLE_EDITOR,
		'volunteerSave' => self::ROLE_EDITOR,
		'volunteerDelete' => self::ROLE_EDITOR,

		'medical' => self::ROLE_EDITOR,
		'medicalSave' => self::ROLE_EDITOR,
		'medicalDelete' => self::ROLE_EDITOR,

		'respond' => self::ROLE_VISITOR,
		'sign' => self::ROLE_VISITOR
	];

	public static $config = [];
	public static $clubName = '';
	public static $clubProfile = [];
	public static $diag = [];

	public static $teams = [];
	public static $players = [];
	public static $playerLabels = [];
	public static $staff = [];
	public static $staffTable = [];
	public static $editStaff = null;
	public static $teamsSelect = [];

	public static $events = [];
	public static $convocations = [];

	public static $playersTable = [];
	public static $teamsTable = [];
	public static $eventsTable = [];
	public static $convocationsTable = [];
	public static $carpoolTable = [];
	public static $volunteerTable = [];
	public static $medicalTable = [];

	public static $editPlayer = null;
	public static $editTeam = null;
	public static $editEvent = null;
	public static $calendarMatchList = [];
	public static $calendarLatestConv = null;
	public static $calendarMonthItems = [];
	public static $volunteerData = [];
	public static $dash = [];

	public static $selectedEventId = '';
	public static $selectedPlayerId = '';

	public static $respond = [
		'ok' => false,
		'message' => '',
		'event' => null,
		'player' => null,
		'status' => null
	];

	public static $sign = [
		'ok' => false,
		'message' => '',
		'label' => '',
		'player' => null,
		'signatureFile' => null
	];

	private function modPath(array $suffix): array {
		return array_merge(['module', $this->getUrl(0)], $suffix);
	}

	private function init(): void {
		$cfg = $this->getData($this->modPath(['config']));
		if ($cfg === null) {
			$cfg = [
				'clubName' => 'Mon club',
				'mailMode' => 'simulation', // simulation | email
				'mailFrom' => '',
				'tokenTtlDays' => 45,
				'clubLogo' => '',
				'clubTagline' => '' ,
				'clubBanner' => ''
			];
			$this->setData($this->modPath(['config', $cfg]));
		}
		self::$config = (array)$cfg;
		self::$clubName = (string)(($cfg['clubSigle'] ?? '') ?: ($cfg['clubName'] ?? 'Mon club'));

		$club = $this->getData($this->modPath(['club']));
		if ($club === null) {
			$club = [
				'fullName' => '',
				'sigle' => '',
				'logo' => (string)($cfg['clubLogo'] ?? ''),
				'banner' => (string)($cfg['clubBanner'] ?? ''),
				'colorPrimary' => '',
				'colorSecondary' => '',
				'affiliation' => '',
				'siret' => '',
				'rna' => '',
				'addrStreet' => '',
				'addrZip' => '',
				'addrCity' => '',
				'addrCountry' => '',
				'contactEmail' => '',
				'contactPhone' => '',
				'web' => '',
				'facebook' => '',
				'instagram' => '',
				'rulesPdf' => '',
				'projectPdf' => '',
				'infrastructures' => [],
				'organigramme' => [],
				'finances' => [
					'iban' => '',
					'bic' => '',
					'rib' => '',
					'tarifs' => [],
					'reductions' => []
				],
				'sponsors' => []
			];
			$this->setData($this->modPath(['club', $club]));
		}
		self::$clubProfile = (array)$club;

		foreach (['club','teams','players','staff','events','convocations','carpool','volunteers','medical','tokenIndex','signIndex'] as $k) {
			if ($this->getData($this->modPath([$k])) === null) {
				$this->setData($this->modPath([$k, []]));
			}
		}

		self::$teams = (array)$this->getData($this->modPath(['teams']));
		self::$players = (array)$this->getData($this->modPath(['players']));
		self::$events = (array)$this->getData($this->modPath(['events']));
		self::$convocations = (array)$this->getData($this->modPath(['convocations']));
		self::$staff = (array)$this->getData($this->modPath(['staff']));
	}

	private function newId(string $prefix): string {
		try { return $prefix . bin2hex(random_bytes(6)); }
		catch (\Throwable $e) { return $prefix . uniqid(); }
	}

	private function safeRelPath(string $rel): string {
		$rel = str_replace('\\','/',$rel);
		$rel = preg_replace('#^/+?#','',$rel);
		$rel = preg_replace('#\.\.+#','',$rel);
		$rel = preg_replace('#^site/file/source/#','',$rel);
		$rel = preg_replace('#^file/source/#','',$rel);
		return $rel;
	}
	private function absSourcePath(string $rel): string {
		$rel = $this->safeRelPath($rel);
		return self::FILE_DIR . 'source/' . $rel;
	}
	private function ensureDir(string $absDir): void {
		if (!is_dir($absDir)) { mkdir($absDir, 0755, true); }
	}

	private function uploadMany(string $fieldName, array $allowedExt, int $maxBytes, string $destRelDir): array {
		if (!isset($_FILES[$fieldName])) return [];
		$files = $_FILES[$fieldName];
		if (!is_array($files['name'])) return [];

		$destRelDir = $this->safeRelPath($destRelDir);
		$destAbsDir = self::FILE_DIR . 'source/' . $destRelDir;
		$this->ensureDir($destAbsDir);

		$saved = [];
		for ($i=0; $i<count($files['name']); $i++) {
			$name = (string)$files['name'][$i];
			$tmp  = (string)$files['tmp_name'][$i];
			$size = (int)$files['size'][$i];
			$err  = (int)$files['error'][$i];

			if ($err !== UPLOAD_ERR_OK || $name==='' || $tmp==='') continue;
			if ($size <= 0 || $size > $maxBytes) continue;

			$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
			if (!in_array($ext, $allowedExt, true)) continue;

			$base = preg_replace('/[^a-zA-Z0-9\-_\.]+/', '-', pathinfo($name, PATHINFO_FILENAME));
			$base = trim($base,'-'); if ($base==='') $base='fichier';

			$final = $base . '-' . date('YmdHis') . '-' . substr($this->newId(''),0,6) . '.' . $ext;
			$destAbs = $destAbsDir . '/' . $final;

			if (move_uploaded_file($tmp, $destAbs)) {
				$saved[] = $destRelDir . '/' . $final;
			}
		}
		return $saved;
	}

	private function tokenIndex(): array { return (array)$this->getData($this->modPath(['tokenIndex'])); }
	private function setTokenIndex(array $idx): void { $this->setData($this->modPath(['tokenIndex', $idx])); }
	private function signIndex(): array { return (array)$this->getData($this->modPath(['signIndex'])); }
	private function setSignIndex(array $idx): void { $this->setData($this->modPath(['signIndex', $idx])); }

	private function eventLabel(array $e): string {
		$dt = isset($e['start']) ? date('d/m/Y H:i', (int)$e['start']) : '';
		$type = strtoupper((string)($e['type'] ?? ''));
		$t = (string)($e['title'] ?? '');
		return trim($dt . ' — ' . $type . ' — ' . $t);
	}

	private function roleIsEditor(): bool {
		return (int)$this->getUser('role') >= (int)self::ROLE_EDITOR;
	}

	private function sendIfEnabled(array $to, string $subject, string $html, ?string $replyTo=null): bool|string {
		$mode = (string)(self::$config['mailMode'] ?? 'simulation');
		if ($mode !== 'email') return true;

		$from = (string)(self::$config['mailFrom'] ?? '');
		if ($from === '') $from = (string)$this->getData(['config','smtp','from']);

		return $this->sendMail($to, $subject, $html, $replyTo, $from);
	}

	public function index() {
		$this->init();
		$logged = $this->getUser('id') ? true : false;

		$now = time();
		$max = $now + (30*86400);
		$matches = []; $trainings = []; $events = [];

		foreach (self::$events as $id => $e) {
			$start = (int)($e['start'] ?? 0);
			if ($start < $now || $start > $max) continue;
			$item = [
				'id' => $id,
				'start' => $start,
				'title' => (string)($e['title'] ?? ''),
				'teamId' => (string)($e['teamId'] ?? ''),
				'location' => (string)($e['location'] ?? ''),
				'type' => (string)($e['type'] ?? 'event'),
			];
			if ($item['type'] === 'match') $matches[] = $item;
			elseif ($item['type'] === 'training') $trainings[] = $item;
			else $events[] = $item;
		}
		usort($matches, fn($a,$b)=>$a['start']<=>$b['start']);
		usort($trainings, fn($a,$b)=>$a['start']<=>$b['start']);
		usort($events, fn($a,$b)=>$a['start']<=>$b['start']);

		self::$config['dash'] = [
			'logged' => $logged,
			'isEditor' => $this->roleIsEditor(),
			'matches' => $matches,
			'trainings' => $trainings,
			'events' => $events
		];

		
		$cfg = (array) self::$config;
		$club = (array) self::$clubProfile;

		$logo = (string)($cfg['clubLogo'] ?? '');
		$vat  = (string)($cfg['clubVat'] ?? '');
		$iban = (string)($cfg['clubIban'] ?? '');

		$addr = trim((string)($club['addrStreet'] ?? '') . ' ' . (string)($club['addrZip'] ?? '') . ' ' . (string)($club['addrCity'] ?? '') . ' ' . (string)($club['addrCountry'] ?? ''));
		$phone = (string)($club['contactPhone'] ?? '');
		$mail  = (string)($club['contactEmail'] ?? '');

		$playersCount = is_array(self::$players) ? count(self::$players) : 0;

		// Événements (type event)
		$events = [];
		foreach ((array)self::$events as $eid => $e) {
			if ((string)($e['type'] ?? '') !== 'event') continue;
			$events[$eid] = $e;
		}
		$eventsCount = count($events);
		uasort($events, fn($a,$b)=>(int)($b['createdOn']??0) <=> (int)($a['createdOn']??0));
		$events = array_slice($events, 0, 5, true);

		// Prochaines rencontres (type match)
		$now = time();
		$upcoming = [];
		foreach ((array)self::$events as $eid => $e) {
			if ((string)($e['type'] ?? '') !== 'match') continue;
			$ts = (int)($e['start'] ?? 0);
			if (!$ts || $ts < $now) continue;
			$upcoming[$eid] = $e;
		}
		uasort($upcoming, fn($a,$b)=>(int)($a['start']??0) <=> (int)($b['start']??0));
		$upcoming = array_slice($upcoming, 0, 5, true);

		self::$dash = [
			'logo' => $logo,
			'vat' => $vat,
			'iban' => $iban,
			'addr' => $addr,
			'phone' => $phone,
			'mail' => $mail,
			'playersCount' => $playersCount,
			'eventsCount' => $eventsCount,
			'recentEvents' => $events,
			'upcoming' => $upcoming
		];

$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Espace club'),
			'view' => 'index'
		]);
	}

	public function config() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		if ($this->isPost()) {
			$action = (string)$this->getInput('clubAction', helper::FILTER_STRING_SHORT);
			$c = (array)self::$clubProfile;

			// Ajouts rapides (listes)
			if ($action === 'infraAdd') {
				$id = $this->newId('i_');
				$infra = [
					'id' => $id,
					'name' => (string)$this->getInput('infraName', helper::FILTER_STRING_SHORT, true),
					'surface' => (string)$this->getInput('infraSurface', helper::FILTER_STRING_SHORT),
					'locker' => (string)$this->getInput('infraLocker', helper::FILTER_STRING_SHORT),
					'clubhouse' => (string)$this->getInput('infraClubhouse', helper::FILTER_STRING_SHORT),
					'lat' => (string)$this->getInput('infraLat', helper::FILTER_STRING_SHORT),
					'lng' => (string)$this->getInput('infraLng', helper::FILTER_STRING_SHORT),
				];
				$c['infrastructures'] = array_values(array_merge((array)($c['infrastructures'] ?? []), [$infra]));
				$this->setData($this->modPath(['club', $c]));
				$this->addOutput(['notification'=>helper::translate('Infrastructure ajoutée'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/club','state'=>true]);
				return;
			}

			if ($action === 'orgAdd') {
				$id = $this->newId('o_');
				$item = [
					'id' => $id,
					'name' => (string)$this->getInput('orgName', helper::FILTER_STRING_SHORT, true),
					'role' => (string)$this->getInput('orgRole', helper::FILTER_STRING_SHORT, true),
					'mail' => (string)$this->getInput('orgMail', helper::FILTER_MAIL),
					'phone' => (string)$this->getInput('orgPhone', helper::FILTER_STRING_SHORT),
					'photo' => $this->safeRelPath((string)$this->getInput('orgPhoto', helper::FILTER_STRING_SHORT)),
				];
				$c['organigramme'] = array_values(array_merge((array)($c['organigramme'] ?? []), [$item]));
				$this->setData($this->modPath(['club', $c]));
				$this->addOutput(['notification'=>helper::translate('Responsable ajouté'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/club','state'=>true]);
				return;
			}

			if ($action === 'tariffAdd') {
				$fin = (array)($c['finances'] ?? []);
				$fin['tarifs'] = (array)($fin['tarifs'] ?? []);
				$fin['tarifs'][] = [
					'id' => $this->newId('t_'),
					'category' => (string)$this->getInput('tarifCategory', helper::FILTER_STRING_SHORT, true),
					'price' => (string)$this->getInput('tarifPrice', helper::FILTER_STRING_SHORT, true),
				];
				$c['finances'] = $fin;
				$this->setData($this->modPath(['club', $c]));
				$this->addOutput(['notification'=>helper::translate('Tarif ajouté'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/club','state'=>true]);
				return;
			}

			if ($action === 'reductionAdd') {
				$fin = (array)($c['finances'] ?? []);
				$fin['reductions'] = (array)($fin['reductions'] ?? []);
				$fin['reductions'][] = [
					'id' => $this->newId('r_'),
					'label' => (string)$this->getInput('redLabel', helper::FILTER_STRING_SHORT, true),
					'value' => (string)$this->getInput('redValue', helper::FILTER_STRING_SHORT, true),
				];
				$c['finances'] = $fin;
				$this->setData($this->modPath(['club', $c]));
				$this->addOutput(['notification'=>helper::translate('Réduction ajoutée'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/club','state'=>true]);
				return;
			}

			if ($action === 'sponsorAdd') {
				$c['sponsors'] = (array)($c['sponsors'] ?? []);
				$c['sponsors'][] = [
					'id' => $this->newId('s_'),
					'name' => (string)$this->getInput('spName', helper::FILTER_STRING_SHORT),
					'url' => (string)$this->getInput('spUrl', helper::FILTER_STRING_SHORT),
					'logo' => $this->safeRelPath((string)$this->getInput('spLogo', helper::FILTER_STRING_SHORT, true)),
				];
				$this->setData($this->modPath(['club', $c]));
				$this->addOutput(['notification'=>helper::translate('Sponsor ajouté'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/club','state'=>true]);
				return;
			}

			// Enregistrement fiche club complète
			// (poursuit le code existant ci-dessous)

			$cfg = [
				'clubName' => (string)$this->getInput('clubConfigName', helper::FILTER_STRING_SHORT, true),
				'mailMode' => (string)$this->getInput('clubConfigMailMode', helper::FILTER_STRING_SHORT, true),
				'mailFrom' => (string)$this->getInput('clubConfigMailFrom', helper::FILTER_MAIL),
				'tokenTtlDays' => (int)$this->getInput('clubConfigTokenTtl', helper::FILTER_INT, true),
				'clubLogo' => $this->safeRelPath((string)$this->getInput('clubConfigLogo', helper::FILTER_STRING_SHORT)),
				'clubTagline' => (string)$this->getInput('clubConfigTagline', helper::FILTER_STRING_SHORT),
				'clubFullName' => (string)$this->getInput('clubConfigFullName', helper::FILTER_STRING_SHORT),
				'clubSigle' => (string)$this->getInput('clubConfigSigle', helper::FILTER_STRING_SHORT),
				'clubSport' => (string)$this->getInput('clubConfigSport', helper::FILTER_STRING_SHORT),
				'clubColorPrimary' => (string)$this->getInput('clubConfigColorPrimary', helper::FILTER_STRING_SHORT),
				'clubColorSecondary' => (string)$this->getInput('clubConfigColorSecondary', helper::FILTER_STRING_SHORT),
				'clubFederation' => (string)$this->getInput('clubConfigFederation', helper::FILTER_STRING_SHORT),
				'clubVat' => (string)$this->getInput('clubConfigVat', helper::FILTER_STRING_SHORT),
				'clubIban' => (string)$this->getInput('clubConfigIban', helper::FILTER_STRING_SHORT),
				'clubBanner' => $this->safeRelPath((string)$this->getInput('clubConfigBanner', helper::FILTER_STRING_SHORT)),
			];
			$this->setData($this->modPath(['config', $cfg]));
			$this->addOutput([
				'notification' => helper::translate('Configuration enregistrée'),
				'redirect' => helper::baseUrl() . $this->getUrl(0) . '/config',
				'state' => true
			]);
			return;
		}

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Configuration'),
			'view' => 'config'
		]);
	}

	
	public function club() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		if ($this->isPost()) {
			$c = (array)self::$clubProfile;

			$c['affiliation'] = (string)$this->getInput('clubAffiliation', helper::FILTER_STRING_SHORT);
			$c['siret'] = (string)$this->getInput('clubSiret', helper::FILTER_STRING_SHORT);
			$c['rna'] = (string)$this->getInput('clubRna', helper::FILTER_STRING_SHORT);

			$c['addrStreet'] = (string)$this->getInput('clubAddrStreet', helper::FILTER_STRING_SHORT);
			$c['addrZip'] = (string)$this->getInput('clubAddrZip', helper::FILTER_STRING_SHORT);
			$c['addrCity'] = (string)$this->getInput('clubAddrCity', helper::FILTER_STRING_SHORT);
			$c['addrCountry'] = (string)$this->getInput('clubAddrCountry', helper::FILTER_STRING_SHORT);

			$c['lat'] = (string)$this->getInput('clubLat', helper::FILTER_STRING_SHORT);
			$c['lng'] = (string)$this->getInput('clubLng', helper::FILTER_STRING_SHORT);

			$c['contactEmail'] = (string)$this->getInput('clubContactEmail', helper::FILTER_MAIL);
			$c['contactPhone'] = (string)$this->getInput('clubContactPhone', helper::FILTER_STRING_SHORT);

			$c['web'] = (string)$this->getInput('clubWeb', helper::FILTER_STRING_SHORT);
			$c['facebook'] = (string)$this->getInput('clubFacebook', helper::FILTER_STRING_SHORT);
			$c['instagram'] = (string)$this->getInput('clubInstagram', helper::FILTER_STRING_SHORT);

			$fin = (array)($c['finances'] ?? []);
			$fin['iban'] = (string)$this->getInput('clubIban', helper::FILTER_STRING_SHORT);
			$fin['bic'] = (string)$this->getInput('clubBic', helper::FILTER_STRING_SHORT);
			$fin['rib'] = (string)$this->getInput('clubRib', helper::FILTER_STRING_SHORT);
			// Préserve tarifs / réductions
			$fin['tarifs'] = (array)($fin['tarifs'] ?? []);
			$fin['reductions'] = (array)($fin['reductions'] ?? []);
			$c['finances'] = $fin;

			$c['rulesPdf'] = $this->safeRelPath((string)$this->getInput('clubRulesPdf', helper::FILTER_STRING_SHORT));
			$c['projectPdf'] = $this->safeRelPath((string)$this->getInput('clubProjectPdf', helper::FILTER_STRING_SHORT));

			$this->setData($this->modPath(['club', $c]));


			$this->addOutput([
				'notification' => helper::translate('Fiche club enregistrée'),
				'redirect' => helper::baseUrl() . $this->getUrl(0) . '/club',
				'state' => true
			]);
			return;
		}

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Le club'),
			'view' => 'club'
		]);
	}


	
	public function clubDel() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$type = (string)($this->getUrl(2) ?? '');
		$id = (string)($this->getUrl(3) ?? '');
		$c = (array)self::$clubProfile;

		if ($type === 'infra') {
			$c['infrastructures'] = array_values(array_filter((array)($c['infrastructures'] ?? []), fn($x)=> (string)($x['id'] ?? '') !== $id));
		} elseif ($type === 'org') {
			$c['organigramme'] = array_values(array_filter((array)($c['organigramme'] ?? []), fn($x)=> (string)($x['id'] ?? '') !== $id));
		} elseif ($type === 'tarif') {
			$fin = (array)($c['finances'] ?? []);
			$fin['tarifs'] = array_values(array_filter((array)($fin['tarifs'] ?? []), fn($x)=> (string)($x['id'] ?? '') !== $id));
			$c['finances'] = $fin;
		} elseif ($type === 'reduction') {
			$fin = (array)($c['finances'] ?? []);
			$fin['reductions'] = array_values(array_filter((array)($fin['reductions'] ?? []), fn($x)=> (string)($x['id'] ?? '') !== $id));
			$c['finances'] = $fin;
		} elseif ($type === 'sponsor') {
			$c['sponsors'] = array_values(array_filter((array)($c['sponsors'] ?? []), fn($x)=> (string)($x['id'] ?? '') !== $id));
		}

		$this->setData($this->modPath(['club', $c]));
		$this->addOutput([
			'notification' => helper::translate('Entrée supprimée'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/club',
			'state' => true
		]);
	}


	
	public function diag() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$moduleDir = __DIR__;
		$clubView = $moduleDir . '/view/club/club.php';
		$playersView = $moduleDir . '/view/players/players.php';
		$teamsView = $moduleDir . '/view/teams/teams.php';

		$clubHasFormSubmit = null;
		$playersHasBadCheckbox = null;

		if (is_file($clubView)) {
			$txt = @file_get_contents($clubView);
			$clubHasFormSubmit = ($txt !== false) ? (strpos($txt, 'formSubmit') !== false) : null;
		}
		if (is_file($playersView)) {
			$txt = @file_get_contents($playersView);
			// “mauvais” si on trouve template::checkbox('clubPlayerFeesPaid', [ ... ]) à 2 args
			$playersHasBadCheckbox = ($txt !== false) ? (strpos($txt, "template::checkbox('clubPlayerFeesPaid', [") !== false) : null;
		}

		self::$diag = [
			'version' => self::VERSION,
			'module_dir' => $moduleDir,
			'club_view' => $clubView,
			'club_has_formSubmit' => $clubHasFormSubmit,
			'players_view' => $playersView,
			'players_has_bad_checkbox' => $playersHasBadCheckbox,
			'teams_view' => $teamsView,
		];

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Diagnostic MultiSport'),
			'view' => 'diag'
		]);
	}


	public function players() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)($this->getUrl(2) ?? '');
		if ($playerId && isset(self::$players[$playerId])) {
			self::$editPlayer = self::$players[$playerId];
			self::$editPlayer['id'] = $playerId;
			// Compat : anciennes données pouvaient stocker la date en string.
			if (isset(self::$editPlayer['birth']) && is_string(self::$editPlayer['birth']) && self::$editPlayer['birth'] !== '') {
				$ts = strtotime(self::$editPlayer['birth']);
				if ($ts) { self::$editPlayer['birth'] = (int) $ts; }
			}

		} else self::$editPlayer = null;

		self::$playersTable = [];
		foreach (self::$players as $id => $p) {
			$teamName = '';
			$tid = $p['teamId'] ?? '';
			if ($tid && isset(self::$teams[$tid])) $teamName = (string)(self::$teams[$tid]['name'] ?? '');

			$parent1 = trim((string)($p['parent1Name'] ?? '') . ' ' . (string)($p['parent1Phone'] ?? ''));
			$parent2 = trim((string)($p['parent2Name'] ?? '') . ' ' . (string)($p['parent2Phone'] ?? ''));

			$teamLabel = $teamName;
			if ($tid && isset(self::$teams[$tid]) && !empty(self::$teams[$tid]['category'])) $teamLabel .= ' — ' . (string)(self::$teams[$tid]['category'] ?? '');

			$photo = '';
			if (!empty($p['photo'])) {
				$photoUrl = helper::baseUrl(false) . self::FILE_DIR . 'source/' . htmlspecialchars((string)$p['photo']);
				$photo = '<img src="' . $photoUrl . '" alt="" style="width:46px;height:46px;object-fit:cover;border-radius:12px;border:1px solid rgba(0,0,0,.10);">';
			} else {
				$photo = '<div style="width:46px;height:46px;border-radius:12px;border:1px dashed rgba(0,0,0,.20);display:flex;align-items:center;justify-content:center;opacity:.55;">' . template::ico('user') . '</div>';
			}

			self::$playersTable[] = [
				$photo,
				htmlspecialchars((string)($p['lastName'] ?? '')) . ' ' . htmlspecialchars((string)($p['firstName'] ?? '')),
				htmlspecialchars($teamLabel),
				htmlspecialchars($parent1),
				htmlspecialchars($parent2),
				template::button('msPlayerEdit' . $id, [
					'href' => helper::baseUrl() . $this->getUrl(0) . '/players/' . $id,
					'value' => template::ico('pencil'),
					'help' => 'Éditer'
				]),
				template::button('msPlayerDelete' . $id, [
					'class' => 'buttonRed',
					'href' => helper::baseUrl() . $this->getUrl(0) . '/playerDelete/' . $id,
					'value' => template::ico('trash'),
					'help' => 'Supprimer'
				])
			];
		}

		/* POST DISPATCH */
		if ($this->isPost()) {
			// Deux formulaires sur la page : joueur + signature
			if (isset($_POST['clubSignPlayerId'])) { $this->signatureCreate(); return; }
			$this->playerSave();
			return;
		}

		self::$teamsTable = ['' => '—'];
		foreach (self::$teams as $id => $t) self::$teamsTable[$id] = (string)($t['name'] ?? $id);

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Joueurs'),
			'view' => 'players'
		]);
	}

	public function playerSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$id = (string)$this->getInput('clubPlayerId', helper::FILTER_STRING_SHORT);
		$isNew = false;
		if ($id === '' || !isset(self::$players[$id])) { $id = $this->newId('p_'); $isNew = true; }

		$player = self::$players[$id] ?? [];
		$player = array_merge($player, [
			'firstName' => (string)$this->getInput('clubPlayerFirst', helper::FILTER_STRING_SHORT, true),
			'lastName' => (string)$this->getInput('clubPlayerLast', helper::FILTER_STRING_SHORT, true),
			'birth' => 0,
			'teamId' => (string)$this->getInput('clubPlayerTeam', helper::FILTER_STRING_SHORT),
			'teamCategory' => '' ,
			'parent1Name' => (string)$this->getInput('clubPlayerP1Name', helper::FILTER_STRING_SHORT),
			'parent1Mail' => (string)$this->getInput('clubPlayerP1Mail', helper::FILTER_MAIL),
			'parent1Phone' => (string)$this->getInput('clubPlayerP1Phone', helper::FILTER_STRING_SHORT),

			'parent2Name' => (string)$this->getInput('clubPlayerP2Name', helper::FILTER_STRING_SHORT),
			'parent2Mail' => (string)$this->getInput('clubPlayerP2Mail', helper::FILTER_MAIL),
			'parent2Phone' => (string)$this->getInput('clubPlayerP2Phone', helper::FILTER_STRING_SHORT),

						'notes' => (string)$this->getInput('clubPlayerNotes', helper::FILTER_STRING_LONG),
			'feesStatus' => (string)$this->getInput('clubPlayerFeesStatus', helper::FILTER_STRING_SHORT),
			'feesNote' => (string)$this->getInput('clubPlayerFeesNote', helper::FILTER_STRING_SHORT),
			'feesNote' => (string) $this->getInput('clubPlayerFeesNote', helper::FILTER_STRING_SHORT),
		]);

		// Date de naissance : l'input HTML retourne YYYY-MM-DD (string). Zwii attend un timestamp int.
		$birthRaw = (string) $this->getInput('clubPlayerBirth', helper::FILTER_STRING_SHORT);
		$birthTs = $birthRaw !== '' ? strtotime($birthRaw) : 0;
		$player['birth'] = $birthTs ? (int) $birthTs : 0;
		// teamCategory (auto) + override si saisi
		$player['teamCategory'] = $this->teamCategory((string)($player['teamId'] ?? ''));
		$postedCat = (string) $this->getInput('clubPlayerTeamCategory', helper::FILTER_STRING_SHORT);
		if (trim($postedCat) !== '') { $player['teamCategory'] = trim($postedCat); }

		$pageId = $this->getUrl(0);
		$baseRelDir = self::UPLOAD_ROOT . '/' . $pageId . '/players/' . $id;

		$selectedPhoto = (string)$this->getInput('clubPlayerPhotoSelect', helper::FILTER_STRING_SHORT);
		$selectedPhoto = $selectedPhoto ? $this->safeRelPath($selectedPhoto) : '';

		$uploaded = $this->uploadMany('clubPlayerPhotoUpload', ['jpg','jpeg','png','webp'], 4*1024*1024, $baseRelDir . '/photo');
		if (!empty($uploaded)) $player['photo'] = $uploaded[0];
		elseif ($selectedPhoto !== '') $player['photo'] = $selectedPhoto;

		$docs = (array)($player['docs'] ?? []);
		$upDocs = $this->uploadMany('clubPlayerDocsUpload', ['pdf','jpg','jpeg','png'], 10*1024*1024, $baseRelDir . '/docs');
		if (!empty($upDocs)) $docs = array_values(array_unique(array_merge($docs, $upDocs)));
		$player['docs'] = $docs;

		self::$players[$id] = $player;
		$this->setData($this->modPath(['players', $id, $player]));

		$this->addOutput([
			'notification' => $isNew ? helper::translate('Joueur créé') : helper::translate('Joueur mis à jour'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/players/' . $id,
			'state' => true
		]);
	}

	public function playerDocDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)($this->getUrl(2) ?? '');
		$idx = (int)($this->getUrl(3) ?? -1);

		if (!$playerId || !isset(self::$players[$playerId])) { $this->addOutput(['access'=>false]); return; }
		$p = self::$players[$playerId];
		$docs = (array)($p['docs'] ?? []);
		if (!isset($docs[$idx])) { $this->addOutput(['access'=>false]); return; }

		$rel = (string)$docs[$idx];
		unset($docs[$idx]);
		$p['docs'] = array_values($docs);

		self::$players[$playerId] = $p;
		$this->setData($this->modPath(['players', $playerId, $p]));

		$abs = $this->absSourcePath($rel);
		if (is_file($abs)) @unlink($abs);

		$this->addOutput([
			'notification' => helper::translate('Pièce supprimée'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/players/' . $playerId,
			'state' => true
		]);
	}

	public function playerDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$id = (string)($this->getUrl(2) ?? '');
		if (!$id || !isset(self::$players[$id])) { $this->addOutput(['access'=>false]); return; }

		$this->deleteData($this->modPath(['players', $id]));
		unset(self::$players[$id]);

		$this->addOutput([
			'notification' => helper::translate('Joueur supprimé'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/players',
			'state' => true
		]);
	}

	public function signatureCreate() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)$this->getInput('clubSignPlayerId', helper::FILTER_STRING_SHORT, true);
		$label = (string)$this->getInput('clubSignLabel', helper::FILTER_STRING_SHORT, true);

		if (!isset(self::$players[$playerId])) { $this->addOutput(['access'=>false]); return; }

		$token = $this->newId('s_');
		$idx = $this->signIndex();
		$idx[$token] = [
			'playerId' => $playerId,
			'label' => $label,
			'createdOn' => time(),
			'signedOn' => 0,
			'signFile' => ''
		];
		$this->setSignIndex($idx);

		$link = helper::baseUrl() . $this->getUrl(0) . '/sign/' . $token;

		$p = self::$players[$playerId];
		$to = [];
		if (!empty($p['parent1Mail'])) $to[] = $p['parent1Mail'];
		if (!empty($p['parent2Mail'])) $to[] = $p['parent2Mail'];

		$sent = true;
		if (!empty($to)) {
			$sent = $this->sendIfEnabled(
				$to,
				self::$clubName . ' — Signature requise',
				'Bonjour,<br><br>Merci de signer : <b>' . htmlspecialchars($label) . '</b><br><br>' .
				'Lien sécurisé : <a href="' . $link . '">' . $link . '</a><br><br>' .
				'Cordialement,<br>' . htmlspecialchars(self::$clubName)
			);
		}

		$msg = (!empty($to) ? ($sent === true ? 'Lien de signature envoyé (ou simulé)' : (string)$sent) : 'Lien de signature créé (aucun email parent renseigné)');
		$this->addOutput([
			'notification' => helper::translate($msg),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/players/' . $playerId,
			'state' => ($sent === true)
		]);
	}

	public function signatureDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$token = (string)($this->getUrl(2) ?? '');
		$idx = $this->signIndex();
		if (!$token || !isset($idx[$token])) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)($idx[$token]['playerId'] ?? '');
		$file = (string)($idx[$token]['signFile'] ?? '');
		unset($idx[$token]);
		$this->setSignIndex($idx);

		if ($file) {
			$abs = $this->absSourcePath($file);
			if (is_file($abs)) @unlink($abs);
		}

		$this->addOutput([
			'notification' => helper::translate('Lien / signature supprimé'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/players/' . $playerId,
			'state' => true
		]);
	}

	
	public function staff() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		if ($this->isPost()) { $this->staffSave(); return; }

		$staffId = (string)($this->getUrl(2) ?? '');
		if ($staffId && isset(self::$staff[$staffId])) { self::$editStaff = self::$staff[$staffId]; self::$editStaff['id'] = $staffId; }
		else self::$editStaff = null;

		self::$teamsSelect = ['' => '—'];
		foreach (self::$teams as $id => $t) {
			self::$teamsSelect[$id] = (string)($t['name'] ?? $id);
		}
		self::$staffTable = [];
		foreach (self::$staff as $id => $s) {
			$tid = (string)($s['teamId'] ?? '');
			$team = ($tid && isset(self::$teams[$tid])) ? (string)(self::$teams[$tid]['name'] ?? '') : '';
			$cat  = ($tid && isset(self::$teams[$tid])) ? (string)(self::$teams[$tid]['category'] ?? '') : (string)($s['teamCategory'] ?? '');

			$photo = '';
			if (!empty($s['photo'])) {
				$url = helper::baseUrl(false) . self::FILE_DIR . 'source/' . htmlspecialchars((string)$s['photo']);
				$photo = '<img src="' . $url . '" alt="" style="width:46px;height:46px;object-fit:cover;border-radius:12px;border:1px solid rgba(0,0,0,.10);">';
			} else {
				$photo = '<div style="width:46px;height:46px;border-radius:12px;border:1px dashed rgba(0,0,0,.20);display:flex;align-items:center;justify-content:center;opacity:.55;">' . template::ico('user') . '</div>';
			}

			self::$staffTable[] = [
				$photo,
				htmlspecialchars(trim((string)($s['lastName'] ?? '') . ' ' . (string)($s['firstName'] ?? ''))),
				htmlspecialchars((string)($s['role'] ?? '')),
				htmlspecialchars(trim($team . ' ' . $cat)),
				template::button('msStaffEdit' . $id, [
					'href' => helper::baseUrl() . $this->getUrl(0) . '/staff/' . $id,
					'value' => template::ico('pencil'),
					'help' => 'Éditer'
				]),
				template::button('msStaffDelete' . $id, [
					'class' => 'buttonRed',
					'href' => helper::baseUrl() . $this->getUrl(0) . '/staffDelete/' . $id,
					'value' => template::ico('trash'),
					'help' => 'Supprimer'
				])
			];
		}

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Staff'),
			'view' => 'staff'
		]);
	}

	public function staffSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$id = (string)$this->getInput('clubStaffId', helper::FILTER_STRING_SHORT);
		$isNew = false;
		if ($id === '' || !isset(self::$staff[$id])) { $id = $this->newId('st_'); $isNew = true; }

		$teamId = (string)$this->getInput('clubStaffTeam', helper::FILTER_STRING_SHORT);
		$cat = $this->teamCategory($teamId);
		$postedCat = (string)$this->getInput('clubStaffTeamCategory', helper::FILTER_STRING_SHORT);
		if (trim($postedCat) !== '') { $cat = trim($postedCat); }

		$s = self::$staff[$id] ?? [];
		$s = array_merge($s, [
			'photo' => $this->safeRelPath((string)$this->getInput('clubStaffPhoto', helper::FILTER_STRING_SHORT)),
			'lastName' => (string)$this->getInput('clubStaffLast', helper::FILTER_STRING_SHORT, true),
			'firstName' => (string)$this->getInput('clubStaffFirst', helper::FILTER_STRING_SHORT, true),
			'role' => (string)$this->getInput('clubStaffRole', helper::FILTER_STRING_SHORT, true),
			'mail' => (string)$this->getInput('clubStaffMail', helper::FILTER_MAIL),
			'phone' => (string)$this->getInput('clubStaffPhone', helper::FILTER_STRING_SHORT),
			'teamId' => $teamId,
			'teamCategory' => $cat
		]);

		self::$staff[$id] = $s;
		$this->setData($this->modPath(['staff', $id, $s]));

		$this->addOutput([
			'notification' => $isNew ? helper::translate('Staff créé') : helper::translate('Staff mis à jour'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/staff/' . $id,
			'state' => true
		]);
	}

	public function staffDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$id = (string)($this->getUrl(2) ?? '');
		if (!$id || !isset(self::$staff[$id])) { $this->addOutput(['access'=>false]); return; }

		$this->deleteData($this->modPath(['staff', $id]));
		unset(self::$staff[$id]);

		$this->addOutput([
			'notification' => helper::translate('Entrée supprimée'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/staff',
			'state' => true
		]);
	}


	public function teams() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$teamId = (string)($this->getUrl(2) ?? '');
		if ($teamId && isset(self::$teams[$teamId])) { self::$editTeam = self::$teams[$teamId]; self::$editTeam['id'] = $teamId; }
		else self::$editTeam = null;

		self::$teamsTable = [];
		foreach (self::$teams as $id => $t) {
			$staffCount = is_array($t['staff'] ?? null) ? count((array)$t['staff']) : 0;
			self::$teamsTable[] = [
				htmlspecialchars((string)($t['name'] ?? '')),
				htmlspecialchars((string)($t['category'] ?? '')),
				template::button('msTeamEdit' . $id, [
					'href' => helper::baseUrl() . $this->getUrl(0) . '/teams/' . $id,
					'value' => template::ico('pencil'),
					'help' => 'Éditer'
				]),
				template::button('msTeamDelete' . $id, [
					'class' => 'buttonRed',
					'href' => helper::baseUrl() . $this->getUrl(0) . '/teamDelete/' . $id,
					'value' => template::ico('trash'),
					'help' => 'Supprimer'
				])
			];
		}

		/* POST DISPATCH */
		if ($this->isPost()) { $this->teamSave(); return; }

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Équipes & staff'),
			'view' => 'teams'
		]);
	}

	public function teamSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$id = (string)$this->getInput('clubTeamId', helper::FILTER_STRING_SHORT);
		$isNew = false;
		if ($id === '' || !isset(self::$teams[$id])) { $id = $this->newId('t_'); $isNew = true; }

		$name = (string)$this->getInput('clubTeamName', helper::FILTER_STRING_SHORT, true);
		$cat = (string)$this->getInput('clubTeamCategory', helper::FILTER_STRING_SHORT);

		$t = ['name'=>$name,'category'=>$cat];
		self::$teams[$id] = $t;
		$this->setData($this->modPath(['teams', $id, $t]));

		$this->addOutput([
			'notification' => $isNew ? helper::translate('Équipe créée') : helper::translate('Équipe mise à jour'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/teams/' . $id,
			'state' => true
		]);
	}

	public function teamDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$id = (string)($this->getUrl(2) ?? '');
		if (!$id || !isset(self::$teams[$id])) { $this->addOutput(['access'=>false]); return; }

		$this->deleteData($this->modPath(['teams', $id]));
		unset(self::$teams[$id]);

		$this->addOutput([
			'notification' => helper::translate('Équipe supprimée'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/teams',
			'state' => true
		]);
	}

	public function events() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)($this->getUrl(2) ?? '');
		if ($eventId && isset(self::$events[$eventId])) { self::$editEvent = self::$events[$eventId]; self::$editEvent['id'] = $eventId; }
		else self::$editEvent = null;

		self::$volunteerData = [];
		if (self::$editEvent && !empty(self::$editEvent['id'])) {
			$eid = (string)self::$editEvent['id'];
			self::$volunteerData = (array)$this->getData($this->modPath(['volunteers', $eid]));
		}


		self::$teamsTable = ['' => '—'];
		foreach (self::$teams as $id => $t) self::$teamsTable[$id] = (string)($t['name'] ?? $id);

		$tmp = self::$events; uasort($tmp, fn($a,$b)=>(int)($a['start']??0) <=> (int)($b['start']??0));
		self::$eventsTable = [];
		foreach ($tmp as $id => $e) {
			$team = '';
			$tid = $e['teamId'] ?? '';
			if ($tid && isset(self::$teams[$tid])) $team = (string)(self::$teams[$tid]['name'] ?? '');

			self::$eventsTable[] = [
				htmlspecialchars(date('d/m/Y H:i', (int)($e['start'] ?? 0))),
				htmlspecialchars((string)($e['title'] ?? '')),
				template::button('msEventEdit'.$id, ['href'=>helper::baseUrl().$this->getUrl(0).'/events/'.$id,'value'=>template::ico('pencil'),'help'=>'Éditer']),
				template::button('msEventDelete'.$id, ['class'=>'buttonRed','href'=>helper::baseUrl().$this->getUrl(0).'/eventDelete/'.$id,'value'=>template::ico('trash'),'help'=>'Supprimer'])
			];
		}

		/* POST DISPATCH */
		if ($this->isPost()) { $this->eventSave(); return; }

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Événements'),
			'view' => 'events'
		]);
	}

	private function parseDateTimeLocal(?string $s): int {
		$s = trim((string)$s);
		if ($s === '') return 0;
		$ts = strtotime($s);
		return $ts ? (int)$ts : 0;
	}

	public function eventSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$id = (string)$this->getInput('clubEventId', helper::FILTER_STRING_SHORT);
		$isNew = false;
		if ($id === '' || !isset(self::$events[$id])) { $id = $this->newId('e_'); $isNew = true; }

		$type = 'event';
		$title = (string)$this->getInput('clubEventTitle', helper::FILTER_STRING_LONG, true);

		// Page Événements : on garde un événement simple (pas de match / entraînement ici).
		$now = time();
		$e = self::$events[$id] ?? [];
		$e = array_merge($e, [
			'type' => $type,
			'title' => $title,
			'createdOn' => (int)($e['createdOn'] ?? $now),
			'start' => (int)($e['start'] ?? $now),
			'end' => (int)($e['end'] ?? 0),
			'teamId' => (string)($e['teamId'] ?? ''),
			'location' => (string)($e['location'] ?? ''),
			'opponent' => (string)($e['opponent'] ?? ''),
			'homeAway' => (string)($e['homeAway'] ?? ''),
			'notes' => (string)($e['notes'] ?? '')
		]);

		self::$events[$id] = $e;
		$this->setData($this->modPath(['events', $id, $e]));

		$this->addOutput([
			'notification' => $isNew ? helper::translate('Événement créé') : helper::translate('Événement mis à jour'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/events/' . $id,
			'state' => true
		]);
	}

	public function eventDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$id = (string)($this->getUrl(2) ?? '');
		if (!$id || !isset(self::$events[$id])) { $this->addOutput(['access'=>false]); return; }

		$this->deleteData($this->modPath(['events', $id]));
		unset(self::$events[$id]);

		$this->addOutput([
			'notification' => helper::translate('Événement supprimé'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/events',
			'state' => true
		]);
	}

	public function convocations() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$eventsList = ['' => '—'];
		$tmp = self::$events; uasort($tmp, fn($a,$b)=>(int)($a['start']??0) <=> (int)($b['start']??0));
		foreach ($tmp as $id => $e) $eventsList[$id] = $this->eventLabel($e);
		self::$eventsTable = $eventsList;

		$playersList = [];
		foreach (self::$players as $id => $p) $playersList[$id] = trim((string)($p['lastName'] ?? '') . ' ' . (string)($p['firstName'] ?? ''));
		self::$playerLabels = $playersList;

		$tmp2 = self::$convocations; uasort($tmp2, fn($a,$b)=>(int)($b['createdOn']??0) <=> (int)($a['createdOn']??0));
		self::$convocationsTable = [];
		foreach ($tmp2 as $cid => $c) {
			$eId = (string)($c['eventId'] ?? '');
			$eLabel = $eId && isset(self::$events[$eId]) ? $this->eventLabel(self::$events[$eId]) : $eId;

			$present=0; $absent=0; $pending=0;
			foreach ((array)($c['players'] ?? []) as $pid => $info) {
				$s = (string)($info['status'] ?? 'pending');
				if ($s === 'present') $present++;
				elseif ($s === 'absent') $absent++;
				else $pending++;
			}
			self::$convocationsTable[] = [
				htmlspecialchars(date('d/m/Y H:i', (int)($c['createdOn'] ?? 0))),
				htmlspecialchars($eLabel),
				$present . ' / ' . $absent . ' / ' . $pending,
				template::button('msConvDelete' . $cid, [
					'class' => 'buttonRed',
					'href' => helper::baseUrl() . $this->getUrl(0) . '/convocationDelete/' . $cid,
					'value' => template::ico('trash'),
					'help' => 'Supprimer'
				]),
			];
		}

		/* POST DISPATCH */
		if ($this->isPost()) { $this->convocationSend(); return; }

		$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Convocations'),
			'view' => 'convocations'
		]);
	}

	public function convocationSend() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)$this->getInput('clubConvEvent', helper::FILTER_STRING_SHORT, true);
		$message = (string)$this->getInput('clubConvMessage', helper::FILTER_STRING_LONG);
		$return = (string)$this->getInput('clubConvReturn', helper::FILTER_STRING_SHORT);

		if (!isset(self::$events[$eventId])) {
			$this->addOutput(['notification'=>helper::translate('Événement introuvable'),'redirect'=>($return ? (helper::baseUrl().$this->getUrl(0).'/'.$return) : (helper::baseUrl().$this->getUrl(0).'/convocations'))]); return;
		}

		$selected = isset($_POST['clubConvPlayers']) && is_array($_POST['clubConvPlayers']) ? $_POST['clubConvPlayers'] : [];
		$statuses = isset($_POST['clubConvStatus']) && is_array($_POST['clubConvStatus']) ? $_POST['clubConvStatus'] : [];
		if (empty($selected)) {
			$this->addOutput(['notification'=>helper::translate('Aucun joueur sélectionné'),'redirect'=>($return ? (helper::baseUrl().$this->getUrl(0).'/'.$return) : (helper::baseUrl().$this->getUrl(0).'/convocations'))]); return;
		}

		$cid = $this->newId('c_');
		$players = [];
		$tokenIdx = $this->tokenIndex();

		foreach ($selected as $pid) {
			$pid = (string)$pid;
			if (!isset(self::$players[$pid])) continue;

			$token = $this->newId('r_');
			$players[$pid] = ['token'=>$token,'status'=>'pending','respondedOn'=>0];

			$tokenIdx[$token] = ['convId'=>$cid,'playerId'=>$pid,'eventId'=>$eventId,'createdOn'=>time()];

			$p = self::$players[$pid];
			$to = [];
			if (!empty($p['parent1Mail'])) $to[] = $p['parent1Mail'];
			if (!empty($p['parent2Mail'])) $to[] = $p['parent2Mail'];

			if (!empty($to)) {
				$linkYes = helper::baseUrl() . $this->getUrl(0) . '/respond/' . $token . '/present';
				$linkNo  = helper::baseUrl() . $this->getUrl(0) . '/respond/' . $token . '/absent';

				$e = self::$events[$eventId];
				$when = date('d/m/Y H:i', (int)($e['start'] ?? 0));

				$subject = self::$clubName . ' — Convocation';
				$html = 'Bonjour,<br><br>' .
					'Convocation pour : <b>' . htmlspecialchars((string)($e['title'] ?? '')) . '</b><br>' .
					'Quand : <b>' . $when . '</b><br>' .
					'Lieu : ' . htmlspecialchars((string)($e['location'] ?? '')) . '<br><br>' .
					($message ? ('Message :<br>' . nl2br(htmlspecialchars($message)) . '<br><br>') : '') .
					'Répondre :<br>' .
					'— <a href="' . $linkYes . '">Présent</a><br>' .
					'— <a href="' . $linkNo . '">Absent</a><br><br>' .
					'Cordialement,<br>' . htmlspecialchars(self::$clubName);

				$this->sendIfEnabled($to, $subject, $html);
			}
		}

		$this->setTokenIndex($tokenIdx);

		$conv = ['eventId'=>$eventId,'createdOn'=>time(),'message'=>$message,'players'=>$players];
		self::$convocations[$cid] = $conv;
		$this->setData($this->modPath(['convocations', $cid, $conv]));

		$this->addOutput([
			'notification' => helper::translate('Convocation créée (envoi selon mode)'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/convocations',
			'state' => true
		]);
	}

	public function convocationDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$cid = (string)($this->getUrl(2) ?? '');
		if (!$cid || !isset(self::$convocations[$cid])) { $this->addOutput(['access'=>false]); return; }

		$tokenIdx = $this->tokenIndex();
		foreach ((array)(self::$convocations[$cid]['players'] ?? []) as $pid => $info) {
			$token = (string)($info['token'] ?? '');
			if ($token && isset($tokenIdx[$token])) unset($tokenIdx[$token]);
		}
		$this->setTokenIndex($tokenIdx);

		$this->deleteData($this->modPath(['convocations', $cid]));
		unset(self::$convocations[$cid]);

		$this->addOutput([
			'notification'=>helper::translate('Convocation supprimée'),
			'redirect'=>($return ? (helper::baseUrl().$this->getUrl(0).'/'.$return) : (helper::baseUrl().$this->getUrl(0).'/convocations')),
			'state'=>true
		]);
	}

	public function respond() {
		$this->init();
		$token = (string)($this->getUrl(2) ?? '');
		$status = (string)($this->getUrl(3) ?? '');
		$status = in_array($status, ['present','absent'], true) ? $status : '';

		$idx = $this->tokenIndex();
		if (!$token || !isset($idx[$token])) {
			self::$respond = ['ok'=>false,'message'=>'Lien invalide ou expiré.','event'=>null,'player'=>null,'status'=>null];
			$this->addOutput(['showBarEditButton'=>false,'showPageContent'=>false,'title'=>helper::translate('Réponse convocation'),'view'=>'respond']);
			return;
		}

		$convId = (string)($idx[$token]['convId'] ?? '');
		$playerId = (string)($idx[$token]['playerId'] ?? '');
		$eventId = (string)($idx[$token]['eventId'] ?? '');

		if (!$convId || !isset(self::$convocations[$convId]) || !isset(self::$players[$playerId]) || !isset(self::$events[$eventId])) {
			self::$respond = ['ok'=>false,'message'=>'Lien invalide ou expiré.','event'=>null,'player'=>null,'status'=>null];
			$this->addOutput(['showBarEditButton'=>false,'showPageContent'=>false,'title'=>helper::translate('Réponse convocation'),'view'=>'respond']);
			return;
		}

		if ($status) {
			$conv = self::$convocations[$convId];
			if (isset($conv['players'][$playerId])) {
				$conv['players'][$playerId]['status'] = $status;
				$conv['players'][$playerId]['respondedOn'] = time();
				self::$convocations[$convId] = $conv;
				$this->setData($this->modPath(['convocations', $convId, $conv]));
			}
		}

		$current = (string)(self::$convocations[$convId]['players'][$playerId]['status'] ?? 'pending');

		self::$respond = [
			'ok'=>true,
			'message'=>($current==='present'?'Réponse enregistrée : Présent.':($current==='absent'?'Réponse enregistrée : Absent.':'Merci de répondre via les boutons.')),
			'event'=>self::$events[$eventId],
			'player'=>self::$players[$playerId],
			'status'=>$current
		];

		$this->addOutput(['showBarEditButton'=>false,'showPageContent'=>false,'title'=>helper::translate('Réponse convocation'),'view'=>'respond']);
	}

	public function sign() {
		$this->init();
		$token = (string)($this->getUrl(2) ?? '');
		$idx = $this->signIndex();

		if (!$token || !isset($idx[$token])) {
			self::$sign = ['ok'=>false,'message'=>'Lien invalide ou expiré.','label'=>'','player'=>null,'signatureFile'=>null];
			$this->addOutput(['showBarEditButton'=>false,'showPageContent'=>false,'title'=>helper::translate('Signature'),'view'=>'sign']);
			return;
		}

		$entry = $idx[$token];
		$playerId = (string)($entry['playerId'] ?? '');
		if (!$playerId || !isset(self::$players[$playerId])) {
			self::$sign = ['ok'=>false,'message'=>'Lien invalide ou expiré.','label'=>'','player'=>null,'signatureFile'=>null];
			$this->addOutput(['showBarEditButton'=>false,'showPageContent'=>false,'title'=>helper::translate('Signature'),'view'=>'sign']);
			return;
		}

		if ($this->isPost()) {
			$dataUrl = trim((string)$this->getInput('clubSignData', helper::FILTER_STRING_LONG, true));
			if (strpos($dataUrl, 'data:image/png;base64,') !== 0) {
				self::$sign = ['ok'=>false,'message'=>'Signature invalide.','label'=>(string)($entry['label']??''),'player'=>self::$players[$playerId],'signatureFile'=>null];
			} else {
				$bin = base64_decode(substr($dataUrl, strlen('data:image/png;base64,')));
				if ($bin === false || strlen($bin) < 200) {
					self::$sign = ['ok'=>false,'message'=>'Signature invalide.','label'=>(string)($entry['label']??''),'player'=>self::$players[$playerId],'signatureFile'=>null];
				} else {
					$pageId = $this->getUrl(0);
					$destRelDir = self::UPLOAD_ROOT . '/' . $pageId . '/signatures/' . $playerId;
					$destAbsDir = self::FILE_DIR . 'source/' . $destRelDir;
					$this->ensureDir($destAbsDir);

					$fn = 'signature-' . date('YmdHis') . '-' . substr($this->newId(''),0,6) . '.png';
					$abs = $destAbsDir . '/' . $fn;
					@file_put_contents($abs, $bin);

					$rel = $destRelDir . '/' . $fn;
					$idx[$token]['signedOn'] = time();
					$idx[$token]['signFile'] = $rel;
					$this->setSignIndex($idx);

					self::$sign = ['ok'=>true,'message'=>'Merci, votre signature a bien été enregistrée.','label'=>(string)($entry['label']??''),'player'=>self::$players[$playerId],'signatureFile'=>$rel];

					$this->addOutput(['notification'=>helper::translate('Signature enregistrée'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/sign/'.$token,'state'=>true]);
					return;
				}
			}
		} else {
			self::$sign = [
				'ok'=>true,'message'=>'','label'=>(string)($entry['label'] ?? ''),
				'player'=>self::$players[$playerId],
				'signatureFile'=>(string)($entry['signFile'] ?? '')
			];
		}

		$this->addOutput(['showBarEditButton'=>false,'showPageContent'=>false,'title'=>helper::translate('Signature'),'view'=>'sign']);
	}

	public function carpool() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)($this->getUrl(2) ?? '');
		if ($eventId === '') {
			$min = PHP_INT_MAX; $best = '';
			foreach (self::$events as $id => $e) {
				$s = (int)($e['start'] ?? 0);
				if ($s > time() && $s < $min) { $min = $s; $best = $id; }
			}
			$eventId = $best;
		}

		/* POST DISPATCH */
		if ($this->isPost()) { $this->carpoolSave(); return; }
		self::$selectedEventId = $eventId;

		$eventsList = ['' => '—'];
		$tmp = self::$events; uasort($tmp, fn($a,$b)=>(int)($a['start']??0) <=> (int)($b['start']??0));
		foreach ($tmp as $id => $e) $eventsList[$id] = $this->eventLabel($e);
		self::$eventsTable = $eventsList;

		$carpool = (array)$this->getData($this->modPath(['carpool', $eventId]));
		self::$carpoolTable = [];
		foreach ($carpool as $rid => $r) {
			self::$carpoolTable[] = [
				htmlspecialchars((string)($r['driver'] ?? '')),
				htmlspecialchars((string)($r['phone'] ?? '')),
				(string)($r['seats'] ?? ''),
				nl2br(htmlspecialchars((string)($r['passengers'] ?? ''))),
				template::button('msCarpoolDel'.$rid, [
					'class'=>'buttonRed',
					'href'=>helper::baseUrl().$this->getUrl(0).'/carpoolDelete/'.$eventId.'/'.$rid,
					'value'=>template::ico('trash'),
					'help'=>'Supprimer'
				])
			];
		}

		$this->addOutput(['showBarEditButton'=>true,'showPageContent'=>false,'title'=>helper::translate('Covoiturage'),'view'=>'carpool']);
	}

	public function carpoolSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)$this->getInput('clubCarpoolEvent', helper::FILTER_STRING_SHORT, true);
		$r = [
			'driver'=>(string)$this->getInput('clubCarpoolDriver', helper::FILTER_STRING_SHORT, true),
			'phone'=>(string)$this->getInput('clubCarpoolPhone', helper::FILTER_STRING_SHORT),
			'seats'=>(int)$this->getInput('clubCarpoolSeats', helper::FILTER_INT, true),
			'passengers'=>(string)$this->getInput('clubCarpoolPassengers', helper::FILTER_STRING_LONG),
		];

		$carpool = (array)$this->getData($this->modPath(['carpool', $eventId]));
		$rid = $this->newId('cp_');
		$carpool[$rid] = $r;

		$this->setData($this->modPath(['carpool', $eventId, $carpool]));
		$this->addOutput(['notification'=>helper::translate('Covoiturage enregistré'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/carpool/'.$eventId,'state'=>true]);
	}

	public function carpoolDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)($this->getUrl(2) ?? '');
		$rid = (string)($this->getUrl(3) ?? '');
		$carpool = (array)$this->getData($this->modPath(['carpool', $eventId]));
		if (!$eventId || !$rid || !isset($carpool[$rid])) { $this->addOutput(['access'=>false]); return; }

		unset($carpool[$rid]);
		$this->setData($this->modPath(['carpool', $eventId, $carpool]));
		$this->addOutput(['notification'=>helper::translate('Entrée supprimée'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/carpool/'.$eventId,'state'=>true]);
	}

	public function volunteers() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)($this->getUrl(2) ?? '');
		if ($eventId === '') {
			$min = PHP_INT_MAX; $best = '';
			foreach (self::$events as $id => $e) {
				$s = (int)($e['start'] ?? 0);
				if ($s > time() && $s < $min) { $min = $s; $best = $id; }
			}
			$eventId = $best;
		}

		/* POST DISPATCH */
		if ($this->isPost()) { $this->volunteerSave(); return; }
		self::$selectedEventId = $eventId;

		$eventsList = ['' => '—'];
		$tmp = self::$events; uasort($tmp, fn($a,$b)=>(int)($a['start']??0) <=> (int)($b['start']??0));
		foreach ($tmp as $id => $e) $eventsList[$id] = $this->eventLabel($e);
		self::$eventsTable = $eventsList;

		$vol = (array)$this->getData($this->modPath(['volunteers', $eventId]));
		self::$volunteerTable = [];
		foreach ($vol as $vid => $v) {
			self::$volunteerTable[] = [
				htmlspecialchars((string)($v['task'] ?? '')),
				htmlspecialchars((string)($v['time'] ?? '')),
				(string)($v['needed'] ?? ''),
				nl2br(htmlspecialchars((string)($v['assigned'] ?? ''))),
				template::button('msVolDel'.$vid, [
					'class'=>'buttonRed',
					'href'=>helper::baseUrl().$this->getUrl(0).'/volunteerDelete/'.$eventId.'/'.$vid,
					'value'=>template::ico('trash'),
					'help'=>'Supprimer'
				])
			];
		}

		$this->addOutput(['showBarEditButton'=>true,'showPageContent'=>false,'title'=>helper::translate('Bénévoles'),'view'=>'volunteers']);
	}

	public function volunteerSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)$this->getInput('clubVolEvent', helper::FILTER_STRING_SHORT, true);
		$return = (string)$this->getInput('clubVolReturn', helper::FILTER_STRING_SHORT);
		$v = [
			'task'=>(string)$this->getInput('clubVolTask', helper::FILTER_STRING_SHORT, true),
			'time'=>(string)$this->getInput('clubVolTime', helper::FILTER_STRING_SHORT),
			'needed'=>(int)$this->getInput('clubVolNeeded', helper::FILTER_INT, true),
			'assigned'=>(string)$this->getInput('clubVolAssigned', helper::FILTER_STRING_LONG),
		];

		$vol = (array)$this->getData($this->modPath(['volunteers', $eventId]));
		$vid = $this->newId('v_');
		$vol[$vid] = $v;

		$this->setData($this->modPath(['volunteers', $eventId, $vol]));
		$this->addOutput(['notification'=>helper::translate('Créneau bénévole enregistré'),'redirect'=>($return ? (helper::baseUrl().$this->getUrl(0).'/'.$return) : (helper::baseUrl().$this->getUrl(0).'/volunteers/'.$eventId)),'state'=>true]);
	}

	public function volunteerDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)($this->getUrl(2) ?? '');
		$vid = (string)($this->getUrl(3) ?? '');
		$vol = (array)$this->getData($this->modPath(['volunteers', $eventId]));
		if (!$eventId || !$vid || !isset($vol[$vid])) { $this->addOutput(['access'=>false]); return; }

		unset($vol[$vid]);
		$this->setData($this->modPath(['volunteers', $eventId, $vol]));
		$this->addOutput(['notification'=>helper::translate('Entrée supprimée'),'redirect'=>($return ? (helper::baseUrl().$this->getUrl(0).'/'.$return) : (helper::baseUrl().$this->getUrl(0).'/volunteers/'.$eventId)),'state'=>true]);
	}

	public function medical() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)($this->getUrl(2) ?? '');
		if ($playerId === '' || !isset(self::$players[$playerId])) $playerId = array_key_first(self::$players) ?? '';
		self::$selectedPlayerId = $playerId;

		$playersList = ['' => '—'];
		foreach (self::$players as $id => $p) $playersList[$id] = trim((string)($p['lastName'] ?? '') . ' ' . (string)($p['firstName'] ?? ''));
		self::$playerLabels = $playersList;

		$med = (array)$this->getData($this->modPath(['medical', $playerId]));
		self::$medicalTable = [];
		foreach ($med as $mid => $m) {
			self::$medicalTable[] = [
				htmlspecialchars((string)($m['injuryDate'] ?? '')),
				htmlspecialchars((string)($m['desc'] ?? '')),
				htmlspecialchars((string)($m['returnDate'] ?? '')),
				template::button('msMedDel'.$mid, [
					'class'=>'buttonRed',
					'href'=>helper::baseUrl().$this->getUrl(0).'/medicalDelete/'.$playerId.'/'.$mid,
					'value'=>template::ico('trash'),
					'help'=>'Supprimer'
				])
			];
		}

		/* POST DISPATCH */
		if ($this->isPost()) { $this->medicalSave(); return; }

		$this->addOutput(['showBarEditButton'=>true,'showPageContent'=>false,'title'=>helper::translate('Suivi médical'),'view'=>'medical']);
	}

	public function medicalSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)$this->getInput('clubMedPlayer', helper::FILTER_STRING_SHORT, true);
		$m = [
			'injuryDate'=>(string)$this->getInput('clubMedInjuryDate', helper::FILTER_STRING_SHORT),
			'desc'=>(string)$this->getInput('clubMedDesc', helper::FILTER_STRING_LONG, true),
			'returnDate'=>(string)$this->getInput('clubMedReturnDate', helper::FILTER_STRING_SHORT),
		];

		$med = (array)$this->getData($this->modPath(['medical', $playerId]));
		$mid = $this->newId('m_');
		$med[$mid] = $m;

		$this->setData($this->modPath(['medical', $playerId, $med]));
		$this->addOutput(['notification'=>helper::translate('Entrée médicale enregistrée'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/medical/'.$playerId,'state'=>true]);
	}

	public function medicalDelete() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		$playerId = (string)($this->getUrl(2) ?? '');
		$mid = (string)($this->getUrl(3) ?? '');
		$med = (array)$this->getData($this->modPath(['medical', $playerId]));
		if (!$playerId || !$mid || !isset($med[$mid])) { $this->addOutput(['access'=>false]); return; }

		unset($med[$mid]);
		$this->setData($this->modPath(['medical', $playerId, $med]));
		$this->addOutput(['notification'=>helper::translate('Entrée supprimée'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/medical/'.$playerId,'state'=>true]);
	}

	private function teamCategory(string $teamId): string {
		if ($teamId !== '' && isset(self::$teams[$teamId])) {
			return (string)(self::$teams[$teamId]['category'] ?? '');
		}
		return '';
	}

	public function calendar() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true) { $this->addOutput(['access'=>false]); return; }

		if ($this->isPost()) {
			$act = (string)$this->getInput('calAction', helper::FILTER_STRING_SHORT);
			if ($act === 'convocation') { $this->convocationSend(); return; }
			if ($act === 'result') { $this->calendarResultSave(); return; }
			$this->calendarSave();
			return;
		}

		// édition
		$eventId = (string)($this->getUrl(2) ?? '');
		if ($eventId && isset(self::$events[$eventId]) && (string)(self::$events[$eventId]['type'] ?? '') === 'match') {
			self::$editEvent = self::$events[$eventId];
			self::$editEvent['id'] = $eventId;
		} else {
			self::$editEvent = null;
		}

		self::$teamsTable = ['' => '—'];
		foreach (self::$teams as $id => $t) self::$teamsTable[$id] = (string)($t['name'] ?? $id);

		self::$eventsTable = [];
		foreach (self::$events as $id => $e) {
			if ((string)($e['type'] ?? '') !== 'match') continue;
			$team = '';
			$tid = (string)($e['teamId'] ?? '');
			if ($tid && isset(self::$teams[$tid])) $team = (string)(self::$teams[$tid]['name'] ?? '');
			self::$eventsTable[] = [
				htmlspecialchars(date('d/m/Y H:i', (int)($e['start'] ?? 0))),
				htmlspecialchars((string)($e['title'] ?? '')),
				htmlspecialchars($team),
				template::button('msCalEdit'.$id, [
					'href' => helper::baseUrl() . $this->getUrl(0) . '/calendar/' . $id,
					'value' => template::ico('pencil'),
					'help' => 'Éditer'
				]),
				template::button('msCalDelete'.$id, [
					'class' => 'buttonRed',
					'href' => helper::baseUrl() . $this->getUrl(0) . '/eventDelete/' . $id,
					'value' => template::ico('trash'),
					'help' => 'Supprimer'
				])
			];
		}

		
		// Liste rencontres (select)
		self::$calendarMatchList = ['' => '—'];
		$tmp = self::$events; uasort($tmp, fn($a,$b)=>(int)($a['start']??0) <=> (int)($b['start']??0));
		foreach ($tmp as $id => $e) {
			if ((string)($e['type'] ?? '') !== 'match') continue;
			$label = date('d/m H:i',(int)($e['start']??0)) . ' — ' . (string)($e['opponent'] ?? '') . ' ' . ((string)($e['homeAway'] ?? '') === 'away' ? '(Ext.)' : ((string)($e['homeAway'] ?? '') === 'home' ? '(Dom.)' : ''));
			self::$calendarMatchList[$id] = trim($label);
		}

		// Dernière convocation pour la rencontre éditée
		self::$calendarLatestConv = null;
		if (self::$editEvent && !empty(self::$editEvent['id'])) {
			$eid = (string)self::$editEvent['id'];
			$best = null; $bestT = 0;
			foreach (self::$convocations as $cid => $c) {
				if ((string)($c['eventId'] ?? '') !== $eid) continue;
				$t = (int)($c['createdOn'] ?? 0);
				if ($t > $bestT) { $bestT = $t; $best = $c; }
			}
			self::$calendarLatestConv = $best;
		}

		// Items du mois pour le calendrier JS (YYYY-MM-DD => [ {label, id} ])
		$items = [];
		foreach (self::$events as $id => $e) {
			if ((string)($e['type'] ?? '') !== 'match') continue;
			$ts = (int)($e['start'] ?? 0); if (!$ts) continue;
			$key = date('Y-m-d', $ts);
			$lab = (string)($e['opponent'] ?? '');
			$items[$key][] = ['label' => $lab ?: 'Rencontre', 'id' => $id];
		}
		self::$calendarMonthItems = $items;

$this->addOutput([
			'showBarEditButton' => true,
			'showPageContent' => false,
			'title' => helper::translate('Calendrier — rencontres'),
			'view' => 'calendar'
		]);
	}

	public function calendarSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$id = (string)$this->getInput('clubCalId', helper::FILTER_STRING_SHORT);
		$isNew = false;
		if ($id === '' || !isset(self::$events[$id]) || (string)(self::$events[$id]['type'] ?? '') !== 'match') { $id = $this->newId('e_'); $isNew = true; }

		$title = (string)$this->getInput('clubCalTitle', helper::FILTER_STRING_LONG);
		$teamId = (string)$this->getInput('clubCalTeam', helper::FILTER_STRING_SHORT);

		$startRaw = (string)$this->getInput('clubCalStart', helper::FILTER_STRING_SHORT, true);
		$endRaw = (string)$this->getInput('clubCalEnd', helper::FILTER_STRING_SHORT);
		$startTs = $this->parseDateTimeLocal($startRaw);
		$endTs = $this->parseDateTimeLocal($endRaw);

		$location = (string)$this->getInput('clubCalLocation', helper::FILTER_STRING_SHORT, true);
		$opponent = (string)$this->getInput('clubCalOpponent', helper::FILTER_STRING_SHORT, true);
		$homeAway = (string)$this->getInput('clubCalHomeAway', helper::FILTER_STRING_SHORT, true);
		$category = (string)$this->getInput('clubCalCategory', helper::FILTER_STRING_SHORT, true);

		$e = self::$events[$id] ?? [];
		$e = array_merge($e, [
			'type' => 'match',
			'title' => $title,
			'teamId' => $teamId,
			'start' => (int)$startTs,
			'end' => (int)$endTs,
			'location' => $location,
			'opponent' => $opponent,
			'homeAway' => $homeAway,
			'category' => $category,
			'notes' => ''
		]);

		self::$events[$id] = $e;
		$this->setData($this->modPath(['events', $id, $e]));

		$this->addOutput([
			'notification' => $isNew ? helper::translate('Rencontre créée') : helper::translate('Rencontre mise à jour'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/calendar/' . $id,
			'state' => true
		]);
	}


	public function calendarResultSave() {
		$this->init();
		if ($this->getUser('permission', __CLASS__, __FUNCTION__) !== true || !$this->isPost()) { $this->addOutput(['access'=>false]); return; }

		$eventId = (string)$this->getInput('clubResEvent', helper::FILTER_STRING_SHORT, true);
		if (!$eventId || !isset(self::$events[$eventId])) {
			$this->addOutput(['notification'=>helper::translate('Rencontre introuvable'),'redirect'=>helper::baseUrl().$this->getUrl(0).'/calendar','state'=>false]);
			return;
		}

		$e = self::$events[$eventId];
		$e['resultHomeTeam'] = (string)$this->getInput('clubResHomeTeam', helper::FILTER_STRING_SHORT);
		$e['resultAwayTeam'] = (string)$this->getInput('clubResAwayTeam', helper::FILTER_STRING_SHORT);
		$e['resultHomeScore'] = (string)$this->getInput('clubResHomeScore', helper::FILTER_STRING_SHORT);
		$e['resultAwayScore'] = (string)$this->getInput('clubResAwayScore', helper::FILTER_STRING_SHORT);
		$e['resultNotes'] = (string)$this->getInput('clubResNotes', helper::FILTER_STRING_LONG);

		self::$events[$eventId] = $e;
		$this->setData($this->modPath(['events', $eventId, $e]));

		$this->addOutput([
			'notification' => helper::translate('Résultat enregistré'),
			'redirect' => helper::baseUrl() . $this->getUrl(0) . '/calendar/' . $eventId,
			'state' => true
		]);
	}



}
