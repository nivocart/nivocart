<?php
class ModelUserUserGroup extends Model {

	public function addUserGroup($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "user_group SET `name` = '" . $this->db->escape($data['name']) . "', permission = '" . (isset($data['permission']) ? serialize($data['permission']) : '') . "'");

		$user_group_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_user_group_id'] = $user_group_id;
	}

	public function editUserGroup($user_group_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "user_group SET `name` = '" . $this->db->escape($data['name']) . "', permission = '" . (isset($data['permission']) ? serialize($data['permission']) : '') . "' WHERE user_group_id = '" . (int)$user_group_id . "'");
	}

	public function deleteUserGroup($user_group_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_group_id . "'");
	}

	public function addPermission($user_id, $type, $page) {
		$user_query = $this->db->query("SELECT DISTINCT user_group_id FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");

		if ($user_query->num_rows) {
			$user_group_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

			if ($user_group_query->num_rows) {
				$data = unserialize($user_group_query->row['permission']);

				$data[$type][] = $page;

				$this->db->query("UPDATE " . DB_PREFIX . "user_group SET permission = '" . serialize($data) . "' WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");
			}
		}
	}

	public function getUserGroup($user_group_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_group_id . "'");

		$user_group = array(
			'name'       => $query->row['name'],
			'permission' => unserialize($query->row['permission'])
		);

		return $user_group;
	}

	public function getUserGroups($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "user_group";

		$sql .= " ORDER BY `name`";

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getUserGroupNames() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_group ORDER BY `name` ASC");

		$user_group_names = array_column($query->rows, 'name');

		return $user_group_names;
	}

	public function getTotalUserGroups() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "user_group");

		return $query->row['total'];
	}

	public function getTotalUsersByUserGroups($user_group_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "user` WHERE user_group_id = '" . (int)$user_group_id . "'");

		return $query->row['total'];
	}
}
