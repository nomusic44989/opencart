<?php
class ModelCatalogTeam extends Model {
	public function addTeam($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "team` SET `link` = '" . $this->db->escape($data['link']) . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "'");

		$team_id = $this->db->getLastId();
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "team` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `team_id` = '" . (int)$team_id . "'");
		}
		
		foreach ($data['team_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "team_description SET `team_id` = '" . (int)$team_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `post` = '" . $this->db->escape($value['post']) . "', `text` = '" . $this->db->escape($value['text']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		if (isset($data['team_seo_url'])) {
			foreach ($data['team_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'team_id=" . (int)$team_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		// Set which layout to use with this category
		if (isset($data['team_layout'])) {
			foreach ($data['team_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "team_to_layout SET team_id = '" . (int)$team_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		return $team_id;
	}
	
	public function editTeam ($team_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "team` SET `link` = '" . $this->db->escape($data['link']) . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "' WHERE `team_id` = '" . (int)$team_id . "'");
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "team` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `team_id` = '" . (int)$team_id . "'");
		}
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "team_description` WHERE `team_id` = '" . (int)$team_id . "'");

		foreach ($data['team_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "team_description SET `team_id` = '" . (int)$team_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `post` = '" . $this->db->escape($value['post']) . "', `text` = '" . $this->db->escape($value['text']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'team_id=" . (int)$team_id . "'");

		if (isset($data['team_seo_url'])) {
			foreach ($data['team_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'team_id=" . (int)$team_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "team_to_layout WHERE team_id = '" . (int)$team_id . "'");

		if (isset($data['team_layout'])) {
			foreach ($data['team_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "team_to_layout SET team_id = '" . (int)$team_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		
		$this->cache->delete('team');
	}
	
	public function deleteTeam ($team_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "team` WHERE `team_id` = '" . (int)$team_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "team_description` WHERE `team_id` = '" . (int)$team_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "team_to_layout` WHERE `team_id` = '" . (int)$category_id . "'");
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'team_id=" . (int)$team_id . "'");
		
		$this->cache->delete('team');
	}
	
	public function getTeam($team_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "team` `t` LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.`team_id`) WHERE `t`.`team_id` = '" . (int)$team_id . "' AND `td`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
	
	public function getTeams ($data = []) {		
		$sql = "SELECT * FROM `" . DB_PREFIX . "team` `t` LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.`team_id`) WHERE `td`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `td`.`name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		$sort_data = [
			'`td`.`name`',
			'`t`.`sort_order`'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `td`.`name`";
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
	}

	public function getDescriptions($team_id) {
		$team_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "team_description` WHERE team_id = '" . (int)$team_id . "'");

		foreach ($query->rows as $result) {
			$team_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'post'             => $result['post'],
				'text'      => $result['text'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			];
		}

		return $team_description_data;
	}
	
	public function getSeoUrls($team_id) {
		$team_seo_url_data = [];
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'team_id=" . (int)$team_id . "'");

		foreach ($query->rows as $result) {
			$team_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $team_seo_url_data;
	}
	
	public function getLayouts($team_id) {
		$team_layout_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "team_to_layout WHERE team_id = '" . (int)$team_id . "'");

		foreach ($query->rows as $result) {
			$team_layout_data[0] = $result['layout_id'];
		}

		return $team_layout_data;
	}
	
	public function getTotals () {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "team`");

		return $query->row['total'];
	}
}