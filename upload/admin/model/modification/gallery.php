<?php
/**
 * Class ModelModificationGallery
 *
 * Moderation helpers for the Gallery modification (reads and writes nc_gallery and the gallery image folder).
 *
 * Location: admin/model/modification/gallery.php
 *
 * @package NivoCart
 */
class ModelModificationGallery extends Model {
	public const STATUS_PENDING = 0;
	public const STATUS_APPROVED = 1;
	public const STATUS_REFUSED = 2;

	/**
	 * Folder (inside the image folder) of the published images. Pending images wait in the "pending" sub folder,
	 * which is blocked from the web and only shown to the administrator by modification/gallery/preview.
	 */
	public const FOLDER = 'data/gallery/';
	public const FOLDER_PENDING = 'data/gallery/pending/';

	/**
	 * Check whether the nc_gallery table exists.
	 *
	 * @return bool
	 */
	public function tableExists(): bool {
		$result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "gallery'");

		return (bool)$result->num_rows;
	}

	/**
	 * Create the table, the image folders and the default settings.
	 *
	 * @return void
	 */
	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "gallery` (
			`gallery_id` int NOT NULL AUTO_INCREMENT,
			`pet_name` varchar(64) NOT NULL,
			`filter_id` int NOT NULL DEFAULT '0',
			`description` varchar(255) NOT NULL DEFAULT '',
			`customer_name` varchar(128) NOT NULL,
			`email` varchar(96) NOT NULL,
			`contact_optin` tinyint(1) NOT NULL DEFAULT '0',
			`image` varchar(255) NOT NULL DEFAULT '',
			`status` tinyint(1) NOT NULL DEFAULT '0',
			`token` varchar(32) NOT NULL,
			`ip` varchar(45) NOT NULL DEFAULT '',
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`gallery_id`),
			KEY `status_date` (`status`,`date_added`),
			KEY `filter_status` (`filter_id`,`status`),
			KEY `ip_date` (`ip`,`date_added`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->createFolders();

		// Page address of the gallery list (only when the keyword is free)
		$query = $this->db->query("SELECT `url_alias_id` FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'modification/gallery' OR `keyword` = 'gallery'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = 'modification/gallery', `keyword` = 'gallery'");
		}

		// Default settings, the gallery stays switched off until the administrator has checked the settings and the texts
		$this->load->model('setting/setting');

		if (!$this->model_setting_setting->getSetting('gallery_page')) {
			$host = (string)parse_url(HTTP_CATALOG, PHP_URL_HOST);

			// Default filters (editable in the settings), the same names for every language until they are translated
			$this->load->model('localisation/language');

			$filters = [];

			foreach ([1 => 'General'] as $filter_id => $name) {
				$names = [];

				foreach ($this->model_localisation_language->getLanguages() as $language) {
					$names[(int)$language['language_id']] = $name;
				}

				$filters[] = ['filter_id' => $filter_id, 'sort_order' => $filter_id, 'name' => $names];
			}

			$this->model_setting_setting->editSetting('gallery_page', [
				'gallery_page_status'     => 0,
				'gallery_page_limit'      => 12,
				'gallery_page_rate_limit' => 5,
				'gallery_page_notify'     => 1,
				'gallery_page_watermark'  => $host,
				'gallery_page_filters'    => $filters
			]);
		}
	}

	/**
	 * Drop the table and forget the submitters. The unpublished (pending) images are removed with it, because nobody can
	 * review them any more. The published images are left where they are, they are only removed by hand.
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "gallery`");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'modification/gallery' OR `query` LIKE 'gallery_id=%'");

		// The settings have their own group (the module uses the group "gallery")
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group` = 'gallery_page'");

		foreach (glob(DIR_IMAGE . self::FOLDER_PENDING . '*.webp') ?: [] as $file) {
			unlink($file);
		}
	}

	/**
	 * Create the gallery image folders, with the .htaccess that keeps the pending images private.
	 *
	 * @return void
	 */
	public function createFolders(): void {
		foreach ([self::FOLDER, self::FOLDER_PENDING] as $folder) {
			if (!is_dir(DIR_IMAGE . $folder)) {
				mkdir(DIR_IMAGE . $folder, 0755, true);
			}
		}

		$htaccess = DIR_IMAGE . self::FOLDER_PENDING . '.htaccess';

		if (!is_file($htaccess)) {
			file_put_contents($htaccess, "# NivoCart Gallery: images waiting for approval are never served directly.\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n");
		}
	}

	/**
	 * Total submissions per status.
	 *
	 * @return array<int, int>
	 */
	public function getTotalsByStatus(): array {
		$totals = [0 => 0, 1 => 0, 2 => 0];

		$query = $this->db->query("SELECT `status`, COUNT(*) AS `total` FROM `" . DB_PREFIX . "gallery` GROUP BY `status`");

		foreach ($query->rows as $row) {
			$totals[(int)$row['status']] = (int)$row['total'];
		}

		return $totals;
	}

	/**
	 * @param array<string, mixed> $data
	 *
	 * @return string
	 */
	private function getWhere(array $data): string {
		$where = [];

		if (!empty($data['filter_name'])) {
			$where[] = "(`pet_name` LIKE '" . $this->db->escape($data['filter_name']) . "%' OR `customer_name` LIKE '" . $this->db->escape($data['filter_name']) . "%')";
		}

		if (isset($data['filter_status']) && ($data['filter_status'] !== '')) {
			$where[] = "`status` = '" . (int)$data['filter_status'] . "'";
		}

		return $where ? ' WHERE ' . implode(' AND ', $where) : '';
	}

	/**
	 * How many submissions use a filter (a filter that is in use cannot be removed).
	 *
	 * @param int $filter_id
	 *
	 * @return int
	 */
	public function getTotalByFilter(int $filter_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "gallery` WHERE `filter_id` = '" . (int)$filter_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * @return int The highest filter number used by a submission
	 */
	public function getMaxFilter(): int {
		$query = $this->db->query("SELECT MAX(`filter_id`) AS `max` FROM `" . DB_PREFIX . "gallery`");

		return (int)$query->row['max'];
	}

	/**
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getImages(array $data = []): array {
		$sort_data = ['pet_name', 'customer_name', 'filter_id', 'status', 'date_added'];

		$sort = (isset($data['sort']) && in_array($data['sort'], $sort_data, true)) ? $data['sort'] : 'date_added';
		$order = (isset($data['order']) && ($data['order'] === 'ASC')) ? 'ASC' : 'DESC';

		$start = max(0, (int)($data['start'] ?? 0));
		$limit = max(1, (int)($data['limit'] ?? 20));

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gallery`" . $this->getWhere($data) . " ORDER BY `" . $sort . "` " . $order . ", `gallery_id` DESC LIMIT " . $start . "," . $limit);

		return $query->rows;
	}

	/**
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalImages(array $data = []): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "gallery`" . $this->getWhere($data));

		return (int)$query->row['total'];
	}

	/**
	 * @param int $gallery_id
	 *
	 * @return array<string, mixed>
	 */
	public function getImage(int $gallery_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "'");

		return $query->row;
	}

	/**
	 * Edit what the administrator may correct: the name, the type and the short description.
	 * The details of the submitter are never edited.
	 *
	 * @param int                  $gallery_id
	 * @param array<string, mixed> $data
	 *
	 * @return void
	 */
	public function editImage(int $gallery_id, array $data): void {
		$info = $this->getImage($gallery_id);

		if (!$info) {
			return;
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `pet_name` = '" . $this->db->escape((string)$data['pet_name']) . "', `filter_id` = '" . (int)$data['filter_id'] . "', `description` = '" . $this->db->escape((string)$data['description']) . "', `date_modified` = NOW() WHERE `gallery_id` = '" . (int)$gallery_id . "'");

		// A published image keeps a file name and a page address that follow the name
		if ((int)$info['status'] === self::STATUS_APPROVED) {
			$this->unpublish($info);

			$this->publish($this->getImage($gallery_id));
		}
	}

	/**
	 * Change the status of one submission: approve (publish), set pending (unpublish) or refuse (the image is deleted).
	 * A refused submission cannot be approved again, because its image is gone.
	 *
	 * @param int $gallery_id
	 * @param int $status
	 *
	 * @return bool True when the status is now the requested one
	 */
	public function setStatus(int $gallery_id, int $status): bool {
		$info = $this->getImage($gallery_id);

		if (!$info) {
			return false;
		}

		$current = (int)$info['status'];

		if ($current === $status) {
			return true;
		}

		if ($current === self::STATUS_REFUSED) {
			return false;
		}

		if ($status === self::STATUS_APPROVED) {
			return $this->publish($info);
		}

		if ($status === self::STATUS_PENDING) {
			return $this->unpublish($info);
		}

		if ($status === self::STATUS_REFUSED) {
			$this->removeFiles((string)$info['image']);

			$this->removeAlias($gallery_id);

			// The image is deleted and so are the personal details of the submitter. Only the name, type and dates stay as a log.
			$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image` = '', `customer_name` = '', `email` = '', `ip` = '', `token` = '', `contact_optin` = '0', `status` = '2', `date_modified` = NOW() WHERE `gallery_id` = '" . (int)$gallery_id . "'");

			return true;
		}

		return false;
	}

	/**
	 * Delete one submission completely: record, image files and page address.
	 *
	 * @param int $gallery_id
	 *
	 * @return void
	 */
	public function deleteImage(int $gallery_id): void {
		$info = $this->getImage($gallery_id);

		if (!$info) {
			return;
		}

		$this->removeFiles((string)$info['image']);

		$this->removeAlias($gallery_id);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "'");
	}

	/**
	 * Absolute path of an image file of the gallery, or an empty string if the stored value is not a gallery image.
	 *
	 * @param string $image
	 *
	 * @return string
	 */
	public function getPath(string $image): string {
		if (!preg_match('#^data/gallery/(pending/)?[a-z0-9\-]+\.webp$#', $image)) {
			return '';
		}

		return DIR_IMAGE . $image;
	}

	/**
	 * Page address (SEO keyword) of a published image.
	 *
	 * @param int $gallery_id
	 *
	 * @return string
	 */
	public function getKeyword(int $gallery_id): string {
		$query = $this->db->query("SELECT `keyword` FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'gallery_id=" . (int)$gallery_id . "'");

		return $query->num_rows ? (string)$query->row['keyword'] : '';
	}

	/**
	 * Move the pending image to the public folder with a descriptive file name and give it a page address.
	 *
	 * @param array<string, mixed> $info
	 *
	 * @return bool
	 */
	private function publish(array $info): bool {
		$source = $this->getPath((string)$info['image']);

		if (!$source || !is_file($source)) {
			return false;
		}

		$slug = $this->slug(html_entity_decode((string)$info['pet_name'], ENT_QUOTES, 'UTF-8'));

		$target = self::FOLDER . $slug . '-' . (int)$info['gallery_id'] . '.webp';

		if (!rename($source, DIR_IMAGE . $target)) {
			return false;
		}

		$this->removeAlias((int)$info['gallery_id']);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = 'gallery_id=" . (int)$info['gallery_id'] . "', `keyword` = '" . $this->db->escape('gallery-' . $slug . '-' . (int)$info['gallery_id']) . "'");

		$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image` = '" . $this->db->escape($target) . "', `status` = '1', `date_modified` = NOW() WHERE `gallery_id` = '" . (int)$info['gallery_id'] . "'");

		return true;
	}

	/**
	 * Move a published image back to the private folder.
	 *
	 * @param array<string, mixed> $info
	 *
	 * @return bool
	 */
	private function unpublish(array $info): bool {
		$source = $this->getPath((string)$info['image']);

		if (!$source || !is_file($source)) {
			return false;
		}

		$this->createFolders();

		$target = self::FOLDER_PENDING . bin2hex(random_bytes(16)) . '.webp';

		if (!rename($source, DIR_IMAGE . $target)) {
			return false;
		}

		$this->removeCache((string)$info['image']);

		$this->removeAlias((int)$info['gallery_id']);

		$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image` = '" . $this->db->escape($target) . "', `status` = '0', `date_modified` = NOW() WHERE `gallery_id` = '" . (int)$info['gallery_id'] . "'");

		return true;
	}

	/**
	 * @param int $gallery_id
	 *
	 * @return void
	 */
	private function removeAlias(int $gallery_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'gallery_id=" . (int)$gallery_id . "'");
	}

	/**
	 * Delete an image file and the resized copies made from it.
	 *
	 * @param string $image
	 *
	 * @return void
	 */
	private function removeFiles(string $image): void {
		$path = $this->getPath($image);

		if ($path && is_file($path)) {
			unlink($path);
		}

		$this->removeCache($image);
	}

	/**
	 * @param string $image
	 *
	 * @return void
	 */
	private function removeCache(string $image): void {
		if (!$this->getPath($image)) {
			return;
		}

		$name = pathinfo($image, PATHINFO_FILENAME);

		foreach (glob(DIR_IMAGE . 'cache/data/gallery/' . $name . '-*.webp') ?: [] as $file) {
			unlink($file);
		}
	}

	/**
	 * Lower case, letters, digits and hyphens only.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function slug(string $text): string {
		$ascii = function_exists('iconv') ? (string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) : $text;

		$slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)), '-');

		return ($slug !== '') ? substr($slug, 0, 40) : 'photo';
	}
}
