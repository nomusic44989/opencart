<?php
class ControllerCatalogStreak extends Controller {
	private $cache_data = null;
	
	public function index() {
		$this->load->language('catalog/streak');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->document->addStyle('view/javascript/jquery/simplebar/simplebar.min.css');
		$this->document->addScript('view/javascript/jquery/simplebar/simplebar.min.js');
		
		$filter = [
			'sort'        => 'name',
			'order'       => 'ASC'
		];
		
		$data['manufacturers'][] = [
			'manufacturer_id'   => '0',
			'name'              => '- Unknown -',
			'sort_order'        => '0'
		];

		$this->load->model('catalog/manufacturer');
		$data['manufacturers'] = array_merge($data['manufacturers'], $this->model_catalog_manufacturer->getManufacturers($filter));
		
		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories($filter);
		
		$url = '';
		
		if (isset($this->request->get['filter_product_id'])) {
			$url .= '&filter_product_id=' . urlencode(html_entity_decode($this->request->get['filter_product_id'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_sku'])) {
			$url .= '&filter_sku=' . urlencode(html_entity_decode($this->request->get['filter_sku'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_mpn'])) {
			$url .= '&filter_mpn=' . urlencode(html_entity_decode($this->request->get['filter_mpn'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_show'])) {
			$url .= '&filter_show=' . $this->request->get['filter_show'];
		}

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
			'href' => $this->url->link('catalog/streak', 'user_token=' . $this->session->data['user_token'] . $url)
		];
			
		$data['list'] = $this->getListProduct();
		$data['clear'] = $this->url->link('catalog/streak/clearSelect', 'user_token=' . $this->session->data['user_token']);
		

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['selects'] = !empty($this->session->data['pselect']) ? $this->session->data['pselect'] : '';
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('catalog/streak', $data));
	}
	
	public function listProduct() {
		$this->load->language('catalog/streak');

		$this->response->setOutput($this->getListProduct());
	}
	
	public function EditBbarcode(){
		$this->load->language('catalog/streak');
		$this->load->model('catalog/product');
		$this->load->model('localisation/language');
		
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		}

		$data['languages'] = $this->model_localisation_language->getLanguages();		
		$data['products'] = $this->model_catalog_product->getProduct($product_id);
		$data['product_description'] = $this->model_catalog_product->getDescriptions($product_id);
		$data['user_token'] = $this->session->data['user_token'];
		
		$this->response->setOutput($this->load->view('catalog/streak/streak_modal', $data));
	}
	
	public function print(){
		$this->load->language('catalog/streak');
		$this->load->model('catalog/product');
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/category');
		
		if (isset($this->request->get['size'])) {
			$data['size'] = $this->request->get['size'];
		}
		
		$data['pselects'] = !empty($this->session->data['pselect']) ? $this->session->data['pselect'] : '';
		$data['date'] = date("d.m.Y");
		
		$generator = '';$manufacturer = '';
		$generator = new \Picqer\Barcode\BarcodeGeneratorHTML();		
		$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();
		//$Qr = 'http://chart.apis.google.com/chart?cht=qr&amp;chs=140x140&amp;chld=H&amp;chl=';;

		foreach ($data['pselects'] as $product) {
			if ($product['price']){
				$price = floor($product['price']);
				$mprice = floor(($product['price'] - $price) * 100 );
				if($mprice >=1  && $mprice<=9){$mprice = $mprice.'00';}
			}
			if (!empty($product['mpn'])){
				$barcode = $product['mpn'];
			}else{
				$barcode = $product['sku'];
			}
			
			for ($i = 1; $i <= $product['quantity']; $i++) {
				if ( $data['size'] == 'sp140_60' ){
					$desc = $this->model_catalog_product->getProductDesc($product['product_id']);
					$main_category_id = $this->model_catalog_product->getProductMainCategoryId($product['product_id']);
					$category = $this->model_catalog_category->getCategory($main_category_id);
				}else{
					$desc = '';
					$main_category_id = $this->model_catalog_product->getProductMainCategoryId($product['product_id']);
					$category = $this->model_catalog_category->getCategory($main_category_id);
				}
				if(isset($product_info['manufacturer_id'])){
					$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
					$manufacturer = !empty($manufacturer_info['name']) ? $manufacturer_info['name'] : ''; 
				}
				$product_data = [
					'name'		=> (!empty($product['shotname']) ? $product['shotname'] : $product['name']),
					'date' 		=> date("d.m.Y"),
					'price'		=> (int)$product['price'],
					'manufacture' => $manufacturer,
					'mprice'	=> $mprice,
					'sku'		=> $product['sku'],
					'mpn'		=> $product['mpn'],
					'barcode'	=> $barcode,
					'desc'		=> $desc,
					'categoris'	=> $category['name'],
					'minimum'	=> sprintf($this->language->get('text_minimum'), $product['minimum']),
					'imagecode'	=> 'data:image/png;base64,' . base64_encode($generatorPNG->getBarcode($barcode, $generatorPNG::TYPE_CODE_128)),
					'image'		=> $generator->getBarcode($barcode, $generator::TYPE_CODE_128),
					'qr'		=> ''//$Qr . $this->link($product['product_id'])
				];
				$data['products'][] = $this->getViewPrint($data['size'],$product_data);
			}
		}
		
		$this->response->setOutput($this->load->view('catalog/streak/print_modal', $data));
	}
	
	public function getViewPrint($size,$data) {
		$this->load->language('catalog/streak');

		
		return $this->load->view('catalog/streak/' . $size, $data);
	}
	
	protected function getListProduct() {
		if (isset($this->request->get['filter_product_id'])) {
			$filter_product_id = $this->request->get['filter_product_id'];
		} else {
			$filter_product_id = '';
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		
		if (isset($this->request->get['filter_sku'])) {
			$filter_sku = $this->request->get['filter_sku'];
		} else {
			$filter_sku = '';
		}
		if (isset($this->request->get['filter_mpn'])) {
			$filter_mpn = $this->request->get['filter_mpn'];
		} else {
			$filter_mpn = '';
		}
		if (isset($this->request->get['filter_manufacturer'])) {
			$filter_manufacturer = $this->request->get['filter_manufacturer'];
		} else {
			$filter_manufacturer = '';
		}
		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}
		if (isset($this->request->get['filter_show'])) {
			$filter_show = $this->request->get['filter_show'];
		} else {
			$filter_show = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
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
		if (isset($this->request->get['filter_product_id'])) {
			$url .= '&filter_product_id=' . urlencode(html_entity_decode($this->request->get['filter_product_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_sku'])) {
			$url .= '&filter_sku=' . urlencode(html_entity_decode($this->request->get['filter_sku'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_mpn'])) {
			$url .= '&filter_mpn=' . urlencode(html_entity_decode($this->request->get['filter_mpn'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		if (isset($this->request->get['filter_show'])) {
			$url .= '&filter_show=' . $this->request->get['filter_show'];
		}
			
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['action'] = $this->url->link('catalog/streak/listProduct', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['products'] = [];

		$filter_data = [
		    'filter_product_id' => $filter_product_id,
			'filter_name'     => $filter_name,
			'filter_sku'   	  => $filter_sku,
			'filter_mpn'   	  => $filter_mpn,
			'filter_show'     => $filter_show,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'           => $this->config->get('config_pagination_admin')
		];
		
		$filter = [
			'sort'        => 'name',
			'order'       => 'ASC'
		];

		$data['manufacturers'][] = [
			'manufacturer_id'   => '0',
			'name'              => '- Unknown -',
			'sort_order'        => '0'
		];

		$this->load->model('catalog/manufacturer');
		$data['manufacturers'] = array_merge($data['manufacturers'], $this->model_catalog_manufacturer->getManufacturers($filter));
		$filter_data['filter_manufacturer'] = $data['filter_manufacturer'] = $filter_manufacturer;

		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories($filter);
		$filter_data['filter_category'] = $data['filter_category'] = $filter_category;
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);
		$results = $this->model_catalog_product->getProducts($filter_data);
		
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$data['products'][] = [
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),
				'sku'   	 => $result['sku'],
				'mpn'		 => $result['mpn'],
				'status'     => $result['status'],
				'barcode'	 => $this->url->link('catalog/streak/listProduct', 'user_token=' . $this->session->data['user_token'] . $url),
				'edit'		 => $this->url->link('catalog/product/form', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'])
			];
		}

		$url = '';
		
		if (isset($this->request->get['filter_product_id'])) {
			$url .= '&filter_product_id=' . urlencode(html_entity_decode($this->request->get['filter_product_id'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_sku'])) {
			$url .= '&filter_sku=' . urlencode(html_entity_decode($this->request->get['filter_sku'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_mpn'])) {
			$url .= '&filter_mpn=' . urlencode(html_entity_decode($this->request->get['filter_mpn'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		if (isset($this->request->get['filter_show'])) {
			$url .= '&filter_show=' . $this->request->get['filter_show'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_name'] = $this->url->link('catalog/streak/listProduct', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url);
		$data['sort_order'] = $this->url->link('catalog/streak/listProduct', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url);
		
		$url = '';
		if (isset($this->request->get['filter_product_id'])) {
			$url .= '&filter_product_id=' . urlencode(html_entity_decode($this->request->get['filter_product_id'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_sku'])) {
			$url .= '&filter_sku=' . urlencode(html_entity_decode($this->request->get['filter_sku'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_mpn'])) {
			$url .= '&filter_mpn=' . urlencode(html_entity_decode($this->request->get['filter_mpn'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_show'])) {
			$url .= '&filter_show=' . $this->request->get['filter_show'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $product_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('catalog/streak/listProduct', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination_2'), ($product_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($product_total - $this->config->get('config_pagination_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $product_total, ceil($product_total / $this->config->get('config_pagination_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_sku']  = $filter_sku;

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('catalog/streak/streak_list_product', $data);
	}
	
	protected function getListSelect(){
		$data['selects'] = !empty($this->session->data['pselect']) ? $this->session->data['pselect'] : '';
		
		return $this->load->view('catalog/streak/streak_select_product', $data);

	}

	public function saveBarcode(){
		$this->load->language('catalog/streak');
		$json = [];
		
		$keys = [
			'sku',
			'mpn',
			'product_id',
			'price',
		];
		
		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}
		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		}else{
			$json['error'] = $this->language->get('error_product');
		}
		
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}
		
		if (empty($this->request->post['sku'])) {
			$json['error']['sku'] = $this->language->get('error_barcode');
		}
		
		if (empty($this->request->post['price'])) {
			$json['error']['price'] = $this->language->get('error_price');
		}
		
		if (!$json) {
			$this->load->model('catalog/product');
			$this->model_catalog_product->sqlSaveBarcode($this->request->post['product_id'], $this->request->post);
			
			$json['success'] = $this->language->get('text_succes_modal');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function select(){
		$this->load->language('catalog/streak');
		$json = [];
		
		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}
		
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} 
		
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}
		
		if (!$json) {
			$this->load->model('catalog/product');
			$this->load->model('tool/image');
			
			if(isset($product_id)){
			  $result = $this->model_catalog_product->getProduct($product_id);
			  
			  if(!empty($result['sku'])){
				
				if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 40, 40);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 40, 40);
				}
				
				$json['product'][] = [
					'product_id' => $result['product_id'],
					'name' 		=> $result['name'],
					'shotname'	=> $result['shotname'],
					'image'		=> $image,
					'sku'		=> $result['sku'],
					'mpn'		=> $result['mpn'],
					'price'		=> $result['price'],
					'minimum'	=> $result['minimum'],
					'quantity'  => 1,
					'delete'	=> $this->url->link('catalog/streak/deleteProduct', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'])
				  ];
				
				$this->session->data['pselect'][$result['product_id']] = [
				    'product_id' => $result['product_id'],
					'name' 		=> $result['name'],
					'shotname'	=> $result['shotname'],
					'image'		=> $image,
					'sku'		=> $result['sku'],
					'mpn'		=> $result['mpn'],
					'price'		=> $result['price'],
					'minimum'	=> $result['minimum'],
					'quantity'  => 1,
					'delete'	=> $this->url->link('catalog/streak/deleteProduct', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'])
				];
			  }else{
				$json['error'] = $this->language->get('error_sku'); 
			  }
			}else{
			  foreach ($selected as $product_id) {
				$json['selected'][] = $product_id;
			  }
			}
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function quantity(){
		$this->load->language('catalog/streak');
		$json = [];
		
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		}else{
			$json['error'] = $this->language->get('error_product');
		}
		
		if (isset($this->request->get['quantity'])) {
			$quantity = $this->request->get['quantity'];
		}else{
			$quantity = '1';
		}
		
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}
		
		if (!$json) {
			$this->session->data['pselect'][$product_id]['quantity'] = $quantity;
			
			$json['quantity'] = $this->session->data['pselect'][$product_id]['quantity']; 
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function deleteProduct(){
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		}
		
		unset($this->session->data['pselect'][$product_id]); 
		
		$this->response->setOutput($this->getListSelect());
	}
	
	public function clearSelect(){
		unset($this->session->data['pselect']); 
		
		$this->response->setOutput($this->getListSelect());
	}

	public function modal(){
		$this->load->language('catalog/product');
		$this->load->language('catalog/streak');
		$this->load->model('catalog/product');
		
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$product_id = 0;
		}
		
		if ($product_id) {
			$this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($product_id);
		}
		
		if(isset($product_info)){
			$data['name'] = $product_info['name'];
			$data['date'] = date("Y-m-d H:i:s");
			$data['sku'] = $product_info['sku'];
			
			$generator = '';
			$generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
			
			$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();
			
			$data['generator'] = $generator->getBarcode($product_info['sku'], $generator::TYPE_CODE_128);
			$data['image'] = 'data:image/png;base64,' . base64_encode($generatorPNG->getBarcode($product_info['sku'], $generatorPNG::TYPE_CODE_128));
		}
		
		
		$this->response->setOutput($this->load->view('catalog/picqer_modal', $data));
	}
	
	private function link (int $product_id): string{
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$url = '';
			
			if (isset($this->cache_data['url'][$product_id]) && $this->cache_data['url'][$product_id]){	
				return $this->cache_data['url'][$product_id];
			}else{
				$path = $this->getPathByProduct($product_id);			
				$categories = explode('_', $path);
				
				foreach ($categories as $category) {
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'category_id=" . (int)$category . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
					} else {
						$url = '';
						break;
					}
				}
				
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE query = 'product_id=" . (int)$product_id . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "'");
				
				if ($query->num_rows && $query->row['keyword']) {
					$url .= '/' . $query->row['keyword'];
				}
				
				$prefix = $this->config->get('module_uni_seo_pro_prefix_product');
				$url = trim($prefix, '/') . $url;
				
				$uni_seo_pro_postfix = trim($this->config->get('module_uni_seo_pro_postfix'));
				if ($uni_seo_pro_postfix) {
					$url .= '.' . $uni_seo_pro_postfix;
				} else {
					if 	($this->config->get('module_uni_seo_pro_postfix_slash_product')) {
						$url .= '/';
					}
				}
				return HTTPS_CATALOG . $url;
			}			
		} else {
			return 'no';
		}
	}
	
	private function getPathByProduct(int $product_id) {
		if ($product_id < 1) return false;
		
		$query_ = 'product_id=' . $product_id;

		if (isset($this->cache_data['seopath'][$query_]) && $this->cache_data['seopath'][$query_])
			return $this->cache_data['seopath'][$query_];

		$query = $this->db->query("	SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' ORDER BY main_category DESC LIMIT 1");
		if ($query->num_rows) {
			$path_product_id = $this->getPathByCategory($query->row['category_id']);
		} else {
			return false;
		}

		if ($path_product_id) {
			$query = $this->db->query("UPDATE " . DB_PREFIX . "seo_url SET seopath = '" . $this->db->escape($path_product_id) . "'	WHERE query = 'product_id=" .(int)$product_id . "'");
		}

		$this->cache_data['seopath'][$query_] = $path_product_id;
		
		return $path_product_id;
	}
	
	private function getPathByCategory($category_id) {
		$category_id = (int)$category_id;

		if ($category_id < 1) return false;

		$query_ = 'category_id=' . $category_id;
		if (isset($this->cache_data['seopath'][$query_]) && $this->cache_data['seopath'][$query_])
			return $this->cache_data['seopath'][$query_];
		$sql = "SELECT GROUP_CONCAT(c1.category_id ORDER BY level SEPARATOR '_') path
		FROM " . DB_PREFIX . "category_path cp
		LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.path_id = c1.category_id)
		WHERE cp.category_id = " . (int)$category_id . "
		GROUP BY cp.category_id";
		$query = $this->db->query($sql);

		$path_category_id = (isset($query->row['path']) && $query->row['path']) ? $query->row['path'] : false;
		if ($path_category_id) {
			$query = $this->db->query("
			UPDATE " . DB_PREFIX . "seo_url
			SET seopath = '" . $this->db->escape($path_category_id) . "'
			WHERE query = 'category_id=" . (int)$category_id . "'");
		}
		$this->cache_data['seopath'][$query_] = $path_category_id;
		
		return $path_category_id;
	}
}