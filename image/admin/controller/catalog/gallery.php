<?php
class ControllerCatalogGallery extends Controller {
	public function index(): void {
		$this->load->language('catalog/gallery');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/gallery', 'user_token=' . $this->session->data['user_token'] . $url)
		];
		
		$data['add'] = $this->url->link('catalog/gallery/form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['modal'] = $this->url->link('catalog/gallery/modal', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link('catalog/gallery/delete', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$data['list'] = $this->getList();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/gallery', $data));		
	}
	
	public function list(): void {
		$this->load->language('catalog/gallery');

		$this->response->setOutput($this->getList());
	}
	
	protected function getList() {
		$this->load->model('catalog/gallery');
		$this->load->model('tool/image');
				
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('catalog/gallery/list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['gallerys'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$gallery_total = $this->model_catalog_gallery->getTotalGallery();
		$results = $this->model_catalog_gallery->getGallerys($filter_data);

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($results as $result) {
			if($result['image'] &&  $result['image_after']){
				$image = $this->model_tool_image->resize($result['image'], 300, 518);
				$image_after = $this->model_tool_image->resize($result['image_after'], 300, 518);
			}else{
				$image = $this->model_tool_image->resizeMerge($result['image'], 300, 300);
				$image_after = '';
			}
			
			$data['gallerys'][] = [
				'gallery_id' 		=> $result['gallery_id'],
				'name'          	=> $result['name'],
				'image'				=> $image,
				'image_after'		=> $image_after,
				'sort_order'    	=> $result['sort_order'],
				'team'				=> $result['team'],
				'edit'          	=> $this->url->link('catalog/gallery/form', 'user_token=' . $this->session->data['user_token'] . '&gallery_id=' . $result['gallery_id'] . $url)
			];
		}
		
		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/gallery/list', 'user_token=' . $this->session->data['user_token'] . '&sort=fgd.name' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/gallery/list', 'user_token=' . $this->session->data['user_token'] . '&sort=fg.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $gallery_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('catalog/gallery/list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($gallery_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($gallery_total - $this->config->get('config_pagination_admin'))) ? $gallery_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $gallery_total, ceil($gallery_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		return $this->load->view('catalog/gallery_list', $data);
	}
	
	public function form() {
		$this->load->language('catalog/gallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !isset($this->request->get['gallery_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/gallery', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['save'] = $this->url->link('catalog/gallery/save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('catalog/gallery', 'user_token=' . $this->session->data['user_token'] . $url);
		
		if (isset($this->request->get['gallery_id'])) {
			$this->load->model('catalog/gallery');

			$gallery_info = $this->model_catalog_gallery->getGallery($this->request->get['gallery_id']);
		}

		if (isset($this->request->get['gallery_id'])) {
			$data['gallery_id'] = (int)$this->request->get['gallery_id'];
		} else {
			$data['gallery_id'] = 0;
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['gallery_id'])) {
			$data['gallery_description'] = $this->model_catalog_gallery->getDescriptions($this->request->get['gallery_id']);
		} else {
			$data['gallery_description'] = [];
		}
		
		//Team
		$this->load->model('catalog/team');
		
		if (!empty($gallery_info)) {
			$data['team_id'] = $gallery_info['team_id'];
		} else {
			$data['team_id'] = '';
		}
		
		if (!empty($gallery_info)) {
			$team_info = $this->model_catalog_team->getTeam($gallery_info['team_id']);

			if ($team_info) {
				$data['team'] = $team_info['name'];
			} else {
				$data['team'] = '';
			}
		} else {
			$data['team'] = '';
		}
		
		// Categories
		$this->load->model('catalog/category');

		if (!empty($gallery_id)) {
			$categories = $this->model_catalog_product->getCategories($gallery_id);
		} else {
			$categories = [];
		}

		$data['gallery_categories'] = [];

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['gallery_categories'][] = [
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				];
			}
		}
		
		if (!empty($gallery_info)) {
			$data['status'] = $gallery_info['status'];
		} else {
			$data['status'] = 1;
		}
		
		if (!empty($gallery_info)) {
			$data['sort_order'] = $gallery_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}
		
		//images
		if (!empty($gallery_info)) {
			$data['image'] = $gallery_info['image'];
		} else {
			$data['image'] = '';
		}
		
		if (!empty($gallery_info)) {
			$data['image_after'] = $gallery_info['image_after'];
		} else {
			$data['image_after'] = '';
		}
		
		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if ($data['image'] && is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize($data['image'], 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}
		
		if ($data['image_after'] && is_file(DIR_IMAGE . html_entity_decode($data['image_after'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb_after'] = $this->model_tool_image->resize($data['image_after'], 100, 100);
		} else {
			$data['thumb_after'] = $data['placeholder'];
		}
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/gallery_form', $data));
	}
	
	public function save() {
		$this->load->language('catalog/gallery');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/gallery')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['gallery_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 136)) {
				$json['error']['name_' . $language_id] = $this->language->get('error_name');
			}
		}

		if (empty($this->request->post['team_id'])) {
			$json['error']['team'] = $this->language->get('error_team');
		}
		
		if (oc_strlen($this->request->post['image']) < 1) {
			$json['error']['image'] = $this->language->get('error_image');
		}
		
		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->load->model('catalog/gallery');

			if (!$this->request->post['gallery_id']) {
				$json['gallery_id'] = $this->model_catalog_gallery->addGallery($this->request->post);
			} else {
				$this->model_catalog_gallery->editGallery($this->request->post['gallery_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function delete() {
		$this->load->language('catalog/gallery');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/gallery')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/gallery');

			foreach ($selected as $price_id) {
				$this->model_catalog_gallery->deleteGallery($gallery_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
