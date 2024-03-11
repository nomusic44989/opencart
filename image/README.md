В файле confing добавляем HTTP_IMAGE

<code>
/?php
// HTTP
define('HTTP_SERVER', 'https://xn--e1agaappmacldee4pe.xn--p1ai/');
define('HTTP_IMAGE', 'https://image.xn--e1agaappmacldee4pe.xn--p1ai/');

// HTTPS
define('HTTPS_SERVER', 'https://xn--e1agaappmacldee4pe.xn--p1ai/');
define('HTTPS_IMAGE', 'https://image.xn--e1agaappmacldee4pe.xn--p1ai/');
</code>

в файле \catalog и admin\controller\startup\startup.php добавляем 
<code>
$this->config->set('config_url_image', HTTP_IMAGE);
$this->config->set('config_ssl_image', HTTPS_IMAGE);
</code>
