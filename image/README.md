В файле confing добавляем HTTP_IMAGE

#php
// HTTP
define('HTTP_SERVER', 'https://xn--e1agaappmacldee4pe.xn--p1ai/');
define('HTTP_IMAGE', 'https://image.xn--e1agaappmacldee4pe.xn--p1ai/');

// HTTPS
define('HTTPS_SERVER', 'https://xn--e1agaappmacldee4pe.xn--p1ai/');
define('HTTPS_IMAGE', 'https://image.xn--e1agaappmacldee4pe.xn--p1ai/');

в файле \catalog и admin\controller\startup\startup.php добавляем 
#php
$this->config->set('config_url_image', HTTP_IMAGE);
$this->config->set('config_ssl_image', HTTPS_IMAGE);

Добавить в css
#style
.gallery-item {
    position: relative;
    text-align:center;
    border: 3px solid #d4d4d4;
	background-color: #d4d4d4;
    transition: all 0.4s ease-in-out;
}
.gallery-image.w-50, .gallery-image-after {
    width:50%;
}
.gallery-image-after{
    margin-left:-5px;
}
figcaption {
  background-color: rgba(255, 255, 255, 0.5);
  width: 100%;
  text-align: center;
  color: #5e423f;
  z-index: 1;
  padding: 9px 0px 11px 0px;
  text-transform: uppercase;
  font-size: 1vw;
  font-weight: 600;
  position: absolute;
  bottom: 38px;
}
.gallery-team{
  display:block;
  padding: 9px 0px 11px 0px;
  text-align: center;
  background-color: #607D8B;
  color:#fff;
}
.gallery-top {
    position: absolute;
    top: 10px;
	right: 10px;
    padding:0 10px;
}
.gallery-check{
  width: 2em;
  height: 2em;
  border-color: #E91E63 !important;
  background-color: color(srgb 1 1 1 / 0.7);
}
.gallery-check:checked {
    background-color: #E91E63;
    border-color: #E91E63;
}
.gallery-btn{
  color:#2e2e2e;
  margin-right: 0.5rem;
}
.gallery-bottom {
    position: absolute;
    width: calc(100% + 4px);
    left: -2px;
    display: flex;
    justify-content: center;
    background: #d4d4d4;
    padding: 9px 0px 11px 0px;
    transition: all 0.4s ease-in-out;
}
.gallery-item:hover {
    border-color: #adadad;
	background-color: #adadad;
}
.gallery-item:hover .gallery-bottom{
	background: #adadad;
}
#style#