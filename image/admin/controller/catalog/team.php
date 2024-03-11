<?php
class ControllerCatalogTeam extends Controller {
	public function index(): void {
		$this->load->language('catalog/team');

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
			'href' => $this->url->link('catalog/team', 'user_token=' . $this->session->data['user_token'] . $url)
		];
		
		$data['add'] = $this->url->link('catalog/team/form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link('catalog/team/delete', 'user_token=' . $this->session->data['user_token']);
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['list'] = $this->getList();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/team', $data));		
	}
	
	public function list(): void {
		$this->load->language('catalog/team');

		$this->response->setOutput($this->getList());
	}
	
	protected function getList() {
		$this->load->model('catalog/team');
				
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

		$data['action'] = $this->url->link('catalog/team/list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['teams'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$team_total = $this->model_catalog_team->getTotals();

		$results = $this->model_catalog_team->getTeams($filter_data);
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($results as $result) {
			if ($result['image'] && is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize($result['image'], 100, 100);
			} else {
				$image = $data['placeholder'];
			}	
			$data['teams'][] = [
				'team_id' 		=> $result['team_id'],
				'name'          => $result['name'],
				'image'			=> $image,
				'sort_order'    => $result['sort_order'],
				'edit'          => $this->url->link('catalog/team/form', 'user_token=' . $this->session->data['user_token'] . '&team_id=' . $result['team_id'] . $url)
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

		$data['sort_name'] = $this->url->link('catalog/team/list', 'user_token=' . $this->session->data['user_token'] . '&sort=fgd.name' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/team/list', 'user_token=' . $this->session->data['user_token'] . '&sort=fg.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $team_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('catalog/team/list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($team_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($team_total - $this->config->get('config_pagination_admin'))) ? $team_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $team_total, ceil($team_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		return $this->load->view('catalog/team_list', $data);
	}
	
	public function form() {
		$this->load->language('catalog/team');
		
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !isset($this->request->get['team_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('catalog/team', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['save'] = $this->url->link('catalog/team/save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('catalog/team', 'user_token=' . $this->session->data['user_token'] . $url);
		
		if (isset($this->request->get['team_id'])) {
			$this->load->model('catalog/team');

			$team_info = $this->model_catalog_team->getTeam($this->request->get['team_id']);
		}

		if (isset($this->request->get['team_id'])) {
			$data['team_id'] = (int)$this->request->get['team_id'];
		} else {
			$data['team_id'] = 0;
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['team_id'])) {
			$data['team_description'] = $this->model_catalog_team->getDescriptions($this->request->get['team_id']);
		} else {
			$data['team_description'] = [];
		}
		
		if (!empty($team_info)) {
			$data['image'] = $team_info['image'];
		} else {
			$data['image'] = '';
		}
		
		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if ($data['image'] && is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize($data['image'], 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}	

		if (!empty($team_info)) {
			$data['link'] = $team_info['link'];
		} else {
			$data['link'] = '';
		}		
		
		if (!empty($team_info)) {
			$data['status'] = $team_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if (!empty($team_info)) {
			$data['sort_order'] = $team_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}
		
		$data['category_seo_url'] = [];
		
		if (isset($this->request->get['team_id'])) {
			$data['team_seo_url'] = $this->model_catalog_team->getSeoUrls($this->request->get['team_id']);
		} else {
			$data['team_seo_url'] = [];
		}
		
		if (isset($this->request->get['team_id'])) {
			$data['team_layout'] = $this->model_catalog_team->getLayouts($this->request->get['team_id']);
		} else {
			$data['team_layout'] = [];
		}
		
		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/team_form', $data));
	}
	
	public function save(): void {
		$this->load->language('catalog/team');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/team')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['team_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 255)) {
				$json['error']['name_' . $language_id] = $this->language->get('error_name');
			}
			
			if ((oc_strlen(trim($value['post'])) < 1) || (oc_strlen($value['name']) > 130)) {
				$json['error']['post_' . $language_id] = $this->language->get('error_post');
			}

			if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}
		
		$this->load->model('catalog/team');
		
		if ($this->request->post['team_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['team_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						if (count(array_keys($language, $keyword)) > 1) {
							$json['error']['keyword_' . $store_id . '_' . $language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->post['team_id']) || (($seo_url['query'] != 'team_id=' . $this->request->post['team_id'])))) {
								$json['error']['keyword_' . $store_id . '_' . $language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					} else {
						$json['error']['keyword_' . $store_id . '_' . $language_id] = $this->language->get('error_seo');
					}
				}
			}
		}
		
		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}
		
		if (!$json) {
			if (!$this->request->post['team_id']) {
				$json['team_id'] = $this->model_catalog_team->addTeam($this->request->post);
			} else {
				$this->model_catalog_team->editTeam($this->request->post['team_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
	
	public function autocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/team');

			$filter_data = [
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			];

			$teams = $this->model_catalog_team->getTeams($filter_data);

			foreach ($teams as $team) {
				$json[] = [
					'team_id' => $team['team_id'],
					'name'      => strip_tags(html_entity_decode($team['name'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}