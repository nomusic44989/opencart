<?php
class ModelCatalogGallery extends Model {
	public function getGallery($gallery_id) {
		$query = $this->db->query("SELECT DISTINCT *, `gd`.`name` AS `name`,`gd`.`description` AS `description`, `g`.`image`, `g`.`image_after`, `td`.`name` AS `team`, `g`.`sort_order` FROM `" . DB_PREFIX . "gallery` `g` LEFT JOIN `" . DB_PREFIX . "gallery_description` `gd` ON (`g`.`gallery_id` = `gd`.`gallery_id`) LEFT JOIN `" . DB_PREFIX . "team` `t` ON (`g`.`team_id` = `t`.team_id) LEFT JOIN `" . DB_PREFIX . "team_description` `td` ON (`t`.`team_id` = `td`.team_id) WHERE `g`.`gallery_id` = '" . (int)$gallery_id . "' AND `gd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `g`.`status` = '1' AND `g`.`date_modified` <= NOW()");

		if ($query->num_rows) {
			return [
				'gallery_id'       => $query->row['gallery_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'image'            => $query->row['image'],
				'image_after'      => $query->row['image_after'],
				'team_id'  		   => $query->row['team_id'],
				'team'     		   => $query->row['team'],
				'date_modified'    => $query->row['date_modified'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added']
			];
		} else {
			return false;
		}
	}


	public function getGallerys($data = array()) {
		$result_string = '';
		foreach ($data as $key => $value) {
			$result_string .= $key . '_' . $value . '.';
		}
		$result_string = rtrim($result_string, '.');

		$gallery_data = $this->cache->get('gallery.data.' . $result_string);
		
		if (!$gallery_data) {
			$sql = "SELECT `g`.gallery_id";

			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$sql .= " FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "gallery_to_category` `g2c` ON (`cp.`category_id` = `g2c`.`category_id`)";
				} else {
					$sql .= " FROM `" . DB_PREFIX . "gallery_to_category` `g2c`";
				}

				$sql .= " LEFT JOIN `" . DB_PREFIX . "gallery` `g` ON (`g2c`.`gallery_id` = `g`.`gallery_id`)";
			} else {
				$sql .= " FROM `" . DB_PREFIX . "gallery` `g`";
			}

			$sql .= " LEFT JOIN `" . DB_PREFIX . "gallery_description` `gd` ON (`g`.`gallery_id` = `gd`.`gallery_id`) WHERE `gd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `g`.`status` = '1' AND `g`.`date_modified` <= NOW() ";

			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$sql .= " AND `cp`.`path_id` = '" . (int)$data['filter_category_id'] . "'";
				} else {
					$sql .= " AND `g2c`.`category_id` = '" . (int)$data['filter_category_id'] . "'";
				}
			}

			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";

				if (!empty($data['filter_name'])) {
					$implode = [];

					$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

					foreach ($words as $word) {
						$implode[] = "`gd`.`name` LIKE '%" . $this->db->escape($word) . "%'";
					}

					if ($implode) {
						$sql .= " " . implode(" AND ", $implode) . "";
					}
				}

				$sql .= ")";
			}

			if (!empty($data['filter_team_id'])) {
				$sql .= " AND `g`.`team_id` = '" . (int)$data['filter_team_id'] . "'";
			}

			$sql .= " GROUP BY `g`.`gallery_id`";

			$sort_data = [
				'`gd`.`name`',
				'`g`.`sort_order`',
				'`g`.`date_added`'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == '`gd`.`name`') {
					$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
				} else {
					$sql .= " ORDER BY " . $data['sort'];
				}
			} else {
				$sql .= " ORDER BY `g`.`sort_order`";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC, LCASE(`gd`.`name`) DESC";
			} else {
				$sql .= " ASC, LCASE(`gd`.`name`) ASC";
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

			$gallery_data = [];

			$query = $this->db->query($sql);

			foreach ($query->rows as $result) {
				$gallery_data[$result['gallery_id']] = $this->getGallery($result['gallery_id']);
			}
			
			$this->cache->set('gallery.data.' . $result_string, $gallery_data);
			
		}
		
		return $gallery_data;
	}

	public function getTotal($data = array()) {
		$sql = "SELECT COUNT(DISTINCT `g`.`gallery_id`) AS `total`";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM `" . DB_PREFIX . "category_path` `cp` LEFT JOIN `" . DB_PREFIX . "gallery_to_category` `g2c` ON (`cp`.`category_id` = `g2c`.`category_id`)";
			} else {
				$sql .= " FROM `" . DB_PREFIX . "gallery_to_category` `g2c`";
			}

			$sql .= " LEFT JOIN `" . DB_PREFIX . "gallery` `g` ON (`g2c`.`gallery_id` = `g`.`gallery_id`)";
		} else {
			$sql .= " FROM `" . DB_PREFIX . "gallery` `g`";
		}

		$sql .= " LEFT JOIN `" . DB_PREFIX . "gallery_description` `gd` ON (`g`.`gallery_id` = `gd`.`gallery_id`) WHERE `gd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `g`.`status` = '1' AND `g`.`date_modified` <= NOW()";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND g2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "`gd`.`name` LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			$sql .= ")";
		}

		if (!empty($data['filter_team_id'])) {
			$sql .= " AND `g`.`team_id` = '" . (int)$data['filter_team_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

}