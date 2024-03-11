<?php
class ModelCatalogTeam extends Model {
	public function getTeam($team_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "team` `t` LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.`team_id`) WHERE `t`.`team_id` = '" . (int)$team_id . "'");

		return $query->row;
	}

	public function getTeams($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "team` `t` LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.`team_id`) WHERE `t`.`status` = '1'";

			$sort_data = [
				'name',
				'sort_order'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `t`.`name`";
			}

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
		} else {
			$team_data = $this->cache->get('team.all');

			if (!$team_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "team` `t` LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.`team_id`) WHERE `t`.`status` = '1' ORDER BY `sort_order` ASC");

				$team_data = $query->rows;

				$this->cache->set('team.all', $team_data);
			}

			return $team_data;
		}
	}

	
}