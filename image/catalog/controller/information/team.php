<?php
class ControllerInformationTeam extends Controller {
	public function index() {
		$this->load->language('information/team');
		$this->load->model('catalog/team');
		$this->load->model('tool/image');
		
		$this->document->addScript('catalog/view/javascript/jquery/parallax-scroll.js','footer');
		$this->document->addScript('catalog/view/javascript/jquery/materialize/materialize.js','footer');
		
		$this->document->setTitle($this->config->get('config_meta_title_team'));
		$this->document->setDescription($this->config->get('config_meta_description_team'));
		$this->document->setKeywords($this->config->get('config_meta_keyword_team'));
		
		
		$this->document->addOG('og:title', $this->config->get('config_meta_title_team'));
		$this->document->addOG('og:site_name',$this->config->get('config_name'));
		$this->document->addOG('og:description', $this->config->get('config_meta_description_team'));
		$this->document->addOG('og:type','article');
		$this->document->addOG('og:image', $this->model_tool_image->link($this->config->get('config_logo')));
		$this->document->addOG('og:url', $this->url->link('information/team'));
		
		$data['heading_title'] = $this->language->get('text_title');
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/team')
		];
		
		$results = $this->model_catalog_team->getTeams();
		$data['teams'] = [];
		
		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->crop($result['image'], 400, 400);
			} else {
				$image = $this->model_tool_image->crop('placeholder.png', 400, 400);
			}
			
			$data['teams'][] = [
				'team_id' 	=> $result['team_id'],
				'name' 		=> $result['name'],
				'post'		=> $result['post'],
				'only'		=> $result['link'],
				'image' 	=> $image,
				'href' 		=> $this->url->link('information/team/info', 'team_id=' . $result['team_id'])
			];
		}
		
		$data['btns'] = [];
		$data['btns'][] = [
			'name' => $this->language->get('btn_dikidi'),
			'href' => $this->config->get('config_social')['4']['href'],
			'class' => 'border-dikidi'
			];
		$data['btns'][] = [
			'name' => $this->language->get('btn_phone'),
			'href' => 'tel:'.$this->config->get('config_telephone'),
			'class' => 'border-phone'
			];
		$data['btns'][] = [
			'name' => $this->language->get('btn_whatsapp'),
			'href' => $this->config->get('config_social')['3']['href'],
			'class' => 'border-whatsapp'
			];		
		$data['btns'][] = [
			'name' => $this->language->get('btn_vk'),
			'href' => $this->config->get('config_social')['1']['href'],
			'class' => 'border-vk'
			];
		
		
		$data['main_image'] = $this->model_tool_image->link($this->config->get('config_team_image'));
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/team', $data));
	}
	
	public function info(){
		$this->load->language('information/team');
		$this->load->model('catalog/team');
		$this->load->model('tool/image');
		
		if (isset($this->request->get['team_id'])) {
			$team_id = (int)$this->request->get['team_id'];
		} else {
			$team_id = 0;
		}
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/team')
		];
		
		$team_info = $this->model_catalog_team->getTeam($team_id);
		
		if ($team_info) {
			$this->document->addScript('catalog/view/javascript/jquery/parallax-scroll.js','footer');
			$this->document->addScript('catalog/view/javascript/jquery/materialize/materialize.js','footer');
			//$this->document->addStyle('catalog/view/javascript/jquery/gallery/justifiedGallery.css');
			//$this->document->addScript('catalog/view/javascript/jquery/gallery/jquery.justifiedGallery.js','footer');
			$this->document->addScript('https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js','footer');
			
			$this->document->setTitle($team_info['meta_title']);
			$this->document->setDescription($team_info['meta_description']);
			$this->document->setKeywords($team_info['meta_keyword']);			
			$this->document->addLink($this->url->link('information/team/info', 'team_id=' . $this->request->get['team_id']), 'canonical');
			
			$this->document->addOG('og:title', $team_info['meta_title']);
			$this->document->addOG('og:site_name',$this->config->get('config_name'));
			$this->document->addOG('og:description', $team_info['meta_description']);
			$this->document->addOG('og:type','article');
			$this->document->addOG('og:image', $this->model_tool_image->link($this->config->get('config_logo')));
			$this->document->addOG('og:url', $this->url->link('information/team/info', 'team_id=' . $this->request->get['team_id']));
			
			$data['breadcrumbs'][] = [
				'text' => $team_info['name'],
				'href' => $this->url->link('information/team/info', 'team_id=' . $this->request->get['team_id'])
			];

			$data['heading_title'] = $team_info['name'];
			
			$data['name'] = $team_info['name'];
			$data['post'] = $team_info['post'];
			$data['link'] = $team_info['link'];
			
			if ($team_info['image']) {
				$data['photo'] = $this->model_tool_image->resize($team_info['image'], 600, 600);
			}
			
			$data['gallery'] = $this->getGallery();
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/team_info', $data));
		}else{
			
		}
	}
	
	public function list(): void {
		$this->load->language('information/team');

		$this->response->setOutput($this->getGallery());
	}
	
	protected function getGallery(){
		$this->load->language('information/team');
		$this->load->model('catalog/gallery');
		$this->load->model('tool/image');
		
		$data['team_id'] = $this->request->get['team_id'];
		
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}
		
		$filter_data = [
			'filter_team_id' 		=> $data['team_id'],
			'start'         => ($page - 1) * $limit,
			'limit'         => $limit
			];
			
		$gallery_total = $this->model_catalog_gallery->getTotal($filter_data);
		$results = $this->model_catalog_gallery->getGallerys($filter_data);
		
		$data['gallerys'] = [];
		foreach ($results as $result) {
			$gallery = [];
			
			if(!empty($result['image']) &&  !empty($result['image_after'])){
				$image = $this->model_tool_image->resize($result['image'], 300, 518);
			} else if ($result['image']) {
				$image = $this->model_tool_image->resizeMerge($result['image'], 400, 400);
			}else{
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			}
			
			if ($result['image_after']) {
				$image_after = $this->model_tool_image->resize($result['image_after'], 300, 518);
			} else {
				$image_after = '';
			}
			
			$gallery = [
				'gallery_id'	=> $result['gallery_id'],
				'name'			=> $result['name'],
				'description'	=> html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
				'image'			=> $this->model_tool_image->link($result['image']),
				'image_after'	=> !empty($result['image_after']) ? $this->model_tool_image->link($result['image_after']) : '',
				'thumb'			=> $image,
				'thumb_after'	=> $image_after,
				'team'			=> $result['team']
			];
			$data['gallerys'][] = $this->load->view('product/thumb', $gallery);			
		}
		
		
		return $this->load->view('product/gallery_list', $data);
	}
}