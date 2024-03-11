<?php
class ModelCatalogGallery extends Model {
	public function addGallery($data){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "gallery` SET `team_id` = '" . (int)$data['team_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW()");

		$gallery_id = $this->db->getLastId();
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		}
		
		if (isset($data['image_after'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image_after` = '" . $this->db->escape($data['image_after']) . "' WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		}
		
		foreach ($data['gallery_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "gallery_description` SET `gallery_id` = '" . (int)$gallery_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "'");
		}
		
		if (isset($data['gallery_category'])) {
			foreach ($data['gallery_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "gallery_to_category` SET `gallery_id` = '" . (int)$gallery_id . "', `category_id` = '" . (int)$category_id . "'");
			}
		}
		
		return $gallery_id;
		$this->cache->delete('gallery');
	}
	
	public function editGallery($gallery_id, $data){
		$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `team_id` = '" . (int)$data['team_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW() WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image` = '" . $this->db->escape($data['image']) . "' WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		}
		
		if (isset($data['image_after'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "gallery` SET `image_after` = '" . $this->db->escape($data['image_after']) . "' WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "gallery_description WHERE `gallery_id` = '" . (int)$gallery_id . "'");

		foreach ($data['gallery_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "gallery_description` SET `gallery_id` = '" . (int)$gallery_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "gallery_to_category` WHERE `gallery_id` = '" . (int)$gallery_id . "'");

		if (isset($data['gallery_category'])) {
			foreach ($data['gallery_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "gallery_to_category SET gallery_id = '" . (int)$gallery_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		$this->cache->delete('gallery');
	}
	
	public function deleteGallery($gallery_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gallery_description` WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gallery_to_category` WHERE `gallery_id` = '" . (int)$gallery_id . "'");
		
		$this->cache->delete('product');
	}
	
	public function getGallery($gallery_id){
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "gallery` `g` LEFT JOIN `" . DB_PREFIX . "gallery_description` `gd` ON (`g`.`gallery_id` = `gd`.`gallery_id`) WHERE `g`.`gallery_id` = '" . (int)$gallery_id . "' AND `gd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getGallerys($data = []) {		
		$team = "(SELECT `td`.`name`
		   FROM `" . DB_PREFIX . "team` `t` 
		   LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.`team_id`) 
		   WHERE `t`.`team_id` = `g`.`team_id` AND td.language_id = gd.language_id) AS `team`";
	
		$sql = "SELECT *, ". $team ." FROM `" . DB_PREFIX . "gallery` `g` LEFT JOIN `" . DB_PREFIX . "gallery_description` `gd` ON (`g`.`gallery_id` = `gd`.`gallery_id`) WHERE `gd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND `gd`.`name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		$sort_data = [
			'`gd`.`name`',
			'`g`.`sort_order`'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `gd`.`name`";
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
	
	public function getDescriptions($gallery_id){
		$gallery_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gallery_description` WHERE `gallery_id` = '" . (int)$gallery_id . "'");

		foreach ($query->rows as $result) {
			$gallery_data[$result['language_id']] = [
				'name' => $result['name'],
				'description' => $result['description']
				];
		}

		return $gallery_data;
	}
	
	public function getTotalGallery() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "gallery`");

		return $query->row['total'];
	}
}