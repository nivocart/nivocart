<?php
class ModelDesignMedia extends Model {

	public function getMedia(int $media_id) {
		$query = $this->db->query("SELECT DISTINCT media FROM `" . DB_PREFIX . "media` WHERE media_id = '" . (int)$media_id . "' AND status = '1'");

		return $query->row['media'];
	}

	public function getCredit(int $media_id) {
		$query = $this->db->query("SELECT DISTINCT credit FROM `" . DB_PREFIX . "media` WHERE media_id = '" . (int)$media_id . "' AND status = '1'");

		if ($query->row['credit']) {
			return $query->row['credit'];
		} else {
			return false;
		}
	}

	public function getMediaType(int $media_id) {
		$type = 'video';

		$filename = $this->getFilename($media_id);

		$ext = substr(strrchr($filename, '.'), 1);

		$video = array('mp4','ogv','ogg','webm','m4v','wmv','flv');

		if (!in_array(strtolower($ext), $video)) {
			$type = 'audio';
		} else {
			$type = 'video';
		}

		return $type;
	}

	public function getMediaMimeType(int $media_id) {
		$mime_type = '';

		$filename = $this->getFilename($media_id);

		$ext = substr(strrchr($filename, '.'), 1);

		if (mb_strtolower($ext, 'UTF-8') == 'mp3') {
			$mime_type = 'audio/mp3';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'mp4') {
			$mime_type = 'video/mp4';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'oga') {
			$mime_type = 'audio/ogg';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'ogv') {
			$mime_type = 'video/ogg';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'ogg') {
			$mime_type = 'video/ogg';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'webm') {
			$mime_type = 'video/webm';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'm4a') {
			$mime_type = 'audio/m4a';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'm4v') {
			$mime_type = 'video/m4v';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'wav') {
			$mime_type = 'audio/wav';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'wmv') {
			$mime_type = 'video/x-ms-wmv';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'wma') {
			$mime_type = 'audio/x-ms-wma';
		}

		if (mb_strtolower($ext, 'UTF-8') == 'flv') {
			$mime_type = 'application/x-shockwave-flash';
		}

		return $mime_type;
	}

	protected function getFilename(int $media_id) {
		$query = $this->db->query("SELECT DISTINCT media AS `filename` FROM `" . DB_PREFIX . "media` WHERE media_id = '" . (int)$media_id . "'");

		return $query->row['filename'];
	}
}
