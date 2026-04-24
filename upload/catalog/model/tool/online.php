<?php
/**
 * Class ModelToolOnline
 *
 * @package NivoCart
 */
class ModelToolOnline extends Model {
	/**
	 * Functions Check, Get
	 */
	public function whosOnline($ip, int $customer_id, $url, $referer, $user_agent): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_online` WHERE date_added < '" . date('Y-m-d H:i:s', strtotime('-1 hour')) . "'");

		$this->db->query("REPLACE INTO `" . DB_PREFIX . "customer_online` SET `ip` = '" . $ip . "', customer_id = '" . (int)$customer_id . "', `url` = '" . $this->db->escape($url) . "', referer = '" . $this->db->escape($referer) . "', user_agent = '" . $this->db->escape($user_agent) . "', date_added = NOW()");
	}

	// Ban
	public function isBlockedIp($ip): bool {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "block_ip` WHERE INET_ATON('" . $ip . "') BETWEEN INET_ATON(from_ip) AND INET_ATON(to_ip)");

		$status = $query->num_rows ? true : false;

		return $status;
	}

	// Robots
	public function robotsOnline($ip, $robot, $user_agent): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "robot_online` WHERE date_added < '" . date('Y-m-d H:i:s', strtotime('-6 hour')) . "'");

		$this->db->query("REPLACE INTO `" . DB_PREFIX . "robot_online` SET `ip` = '" . $ip . "', robot = '" . $this->db->escape($robot) . "', user_agent = '" . $this->db->escape($user_agent) . "', date_added = NOW()");
	}

	public function getRobotSignatures() {
		$robots = [];

		$robots[] = ['signature' => 'Yandex', 'name' => 'Yandex'];
		$robots[] = ['signature' => 'Googlebot', 'name' => 'Google'];
		$robots[] = ['signature' => 'Mediapartners-Google', 'name' => 'Mediapartners-Google (Adsense)'];
		$robots[] = ['signature' => 'askjeeves', 'name' => 'AskJeeves'];
		$robots[] = ['signature' => 'fastcrawler', 'name' => 'FastCrawler'];
		$robots[] = ['signature' => 'infoseek', 'name' => 'InfoSeek Robot 1.0'];
		$robots[] = ['signature' => 'facebot', 'name' => 'Facebook'];
		$robots[] = ['signature' => 'WebCrawler', 'name' => 'WebCrawler search'];
		$robots[] = ['signature' => 'ZyBorg', 'name' => 'Wisenut search'];
		$robots[] = ['signature' => 'scooter', 'name' => 'AltaVista'];
		$robots[] = ['signature' => 'StackRambler', 'name' => 'Rambler'];
		$robots[] = ['signature' => 'Aport', 'name' => 'Aport'];
		$robots[] = ['signature' => 'lycos', 'name' => 'Lycos'];
		$robots[] = ['signature' => 'WebAlta', 'name' => 'WebAlta'];
		$robots[] = ['signature' => 'yahoo', 'name' => 'Yahoo'];
		$robots[] = ['signature' => 'msnbot', 'name' => 'msnbot 1.0'];
		$robots[] = ['signature' => 'ia_archiver', 'name' => 'Alexa search engine'];
		$robots[] = ['signature' => 'FAST', 'name' => 'AllTheWeb'];
		$robots[] = ['signature' => 'Slurp', 'name' => 'Hot Bot search'];
		$robots[] = ['signature' => 'Teoma', 'name' => 'Ask'];
		$robots[] = ['signature' => 'Baiduspider', 'name' => 'Baidu'];
		$robots[] = ['signature' => 'Gigabot', 'name' => 'Gigabot'];
		$robots[] = ['signature' => 'AdsBot-Google', 'name' => 'Google-Adwords'];
		$robots[] = ['signature' => 'gsa-crawler', 'name' => 'Google-SA'];
		$robots[] = ['signature' => 'Googlebot-Image', 'name' => 'Googlebot-Image'];
		$robots[] = ['signature' => 'ia_archiver-web.archive.org', 'name' => 'InternetArchive'];
		$robots[] = ['signature' => 'omgilibot', 'name' => 'Omgili'];
		$robots[] = ['signature' => 'Speedy Spider', 'name' => 'Speedy Spider'];
		$robots[] = ['signature' => 'Y!J', 'name' => 'Yahoo JP'];
		$robots[] = ['signature' => 'link validator', 'name' => 'DeadLinksChecker'];
		$robots[] = ['signature' => 'W3C_Validator', 'name' => 'W3C Validator'];
		$robots[] = ['signature' => 'W3C_CSS_Validator', 'name' => 'W3C CSSValidator'];
		$robots[] = ['signature' => 'FeedValidator', 'name' => 'W3C FeedValidator'];
		$robots[] = ['signature' => 'W3C-checklink', 'name' => 'W3C LinkChecker'];
		$robots[] = ['signature' => 'W3C-mobileOK', 'name' => 'W3C mobileOK'];
		$robots[] = ['signature' => 'P3P Validator', 'name' => 'W3C P3PValidator'];
		$robots[] = ['signature' => 'Bloglines', 'name' => 'Bloglines'];
		$robots[] = ['signature' => 'Feedburner', 'name' => 'Feedburner'];
		$robots[] = ['signature' => 'Snapbot', 'name' => 'SnapBot'];
		$robots[] = ['signature' => 'psbot', 'name' => 'Picsearch'];
		$robots[] = ['signature' => 'Websnapr', 'name' => 'Websnapr'];
		$robots[] = ['signature' => 'asterias', 'name' => 'Asterias'];
		$robots[] = ['signature' => '192.comAgent', 'name' => '192bot'];
		$robots[] = ['signature' => 'ABACHOBot', 'name' => 'AbachoBot'];
		$robots[] = ['signature' => 'ABCdatos', 'name' => 'Abcdatos'];
		$robots[] = ['signature' => 'Acoon', 'name' => 'Acoon'];
		$robots[] = ['signature' => 'Accoona', 'name' => 'Accoona'];
		$robots[] = ['signature' => 'BecomeBot', 'name' => 'BecomeBot'];
		$robots[] = ['signature' => 'BlogRefsBot', 'name' => 'BlogRefsBot'];
		$robots[] = ['signature' => 'Daumoa', 'name' => 'Daumoa'];
		$robots[] = ['signature' => 'DuckDuckBot', 'name' => 'DuckDuckBot'];
		$robots[] = ['signature' => 'Exabot', 'name' => 'Exabot'];
		$robots[] = ['signature' => 'Furlbot', 'name' => 'Furl'];
		$robots[] = ['signature' => 'FyberSpider', 'name' => 'FyberSpider'];
		$robots[] = ['signature' => 'GeonaBot', 'name' => 'Geona'];
		$robots[] = ['signature' => 'Girafabot', 'name' => 'GirafaBot'];
		$robots[] = ['signature' => 'GoSeeBot', 'name' => 'GoSeeBot'];
		$robots[] = ['signature' => 'ichiro', 'name' => 'Ichiro'];
		$robots[] = ['signature' => 'LapozzBot', 'name' => 'LapozzBot'];
		$robots[] = ['signature' => 'WISENutbot', 'name' => 'Looksmart'];
		$robots[] = ['signature' => 'MJ12bot/v2', 'name' => 'Majestic12'];
		$robots[] = ['signature' => 'MLBot', 'name' => 'MLBot'];
		$robots[] = ['signature' => 'msrbot', 'name' => 'MSRBOT'];
		$robots[] = ['signature' => 'MSR-ISRCCrawler', 'name' => 'MSR-ISRCCrawler'];
		$robots[] = ['signature' => 'NaverBot', 'name' => 'Naver'];
		$robots[] = ['signature' => 'Yeti', 'name' => 'Yeti'];
		$robots[] = ['signature' => 'noxtrumbot', 'name' => 'NoxTrumBot'];
		$robots[] = ['signature' => 'OmniExplorer_Bot', 'name' => 'OmniExplorer'];
		$robots[] = ['signature' => 'OnetSzukaj', 'name' => 'OnetSzukaj'];
		$robots[] = ['signature' => 'Scrubby', 'name' => 'ScrubTheWeb'];
		$robots[] = ['signature' => 'SearchSight', 'name' => 'SearchSight'];
		$robots[] = ['signature' => 'Seeqpod', 'name' => 'Seeqpod'];
		$robots[] = ['signature' => 'ShablastBot', 'name' => 'Shablast'];
		$robots[] = ['signature' => 'SitiDiBot', 'name' => 'SitiDiBot'];
		$robots[] = ['signature' => 'silk/1.0', 'name' => 'Slider'];
		$robots[] = ['signature' => 'Sogou', 'name' => 'Sogou'];
		$robots[] = ['signature' => 'Sosospider', 'name' => 'Sosospider'];
		$robots[] = ['signature' => 'StackRambler', 'name' => 'StackRambler'];
		$robots[] = ['signature' => 'SurveyBot', 'name' => 'SurveyBot'];
		$robots[] = ['signature' => 'Touche', 'name' => 'Touche'];
		$robots[] = ['signature' => 'appie', 'name' => 'Walhello'];
		$robots[] = ['signature' => 'wisponbot', 'name' => 'Wisponbot'];
		$robots[] = ['signature' => 'yacybot', 'name' => 'YacyBot'];
		$robots[] = ['signature' => 'YodaoBot', 'name' => 'YodaoBot'];
		$robots[] = ['signature' => 'Charlotte', 'name' => 'Charlotte'];
		$robots[] = ['signature' => 'DiscoBot', 'name' => 'DiscoBot'];
		$robots[] = ['signature' => 'EnaBot', 'name' => 'EnaBot'];
		$robots[] = ['signature' => 'Gaisbot', 'name' => 'Gaisbot'];
		$robots[] = ['signature' => 'kalooga', 'name' => 'Kalooga'];
		$robots[] = ['signature' => 'ScoutJet', 'name' => 'ScoutJet'];
		$robots[] = ['signature' => 'TinEye', 'name' => 'TinEye'];
		$robots[] = ['signature' => 'twiceler', 'name' => 'Twiceler'];
		$robots[] = ['signature' => 'GSiteCrawler', 'name' => 'GSiteCrawler'];
		$robots[] = ['signature' => 'HTTrack', 'name' => 'HTTrack'];
		$robots[] = ['signature' => 'Wget', 'name' => 'Wget'];

		return $robots;
	}
}
