<?php
/**
 * Class ControllerProductVideo
 *
 * The page listing all hosted product videos (route product/video) and the watch page
 * of one video (route product/video/watch).
 *
 * @package NivoCart
 */
class ControllerProductVideo extends Controller {
	private const LIMIT = 24;

	private const OPTIONS_MAX = 100;

	/**
	 * The page listing all videos.
	 */
	public function index() {
		$videos = videoWatchList($this->db, (int)$this->config->get('config_store_id'));

		$page = (isset($this->request->get['page']) && is_scalar($this->request->get['page'])) ? max(1, (int)$this->request->get['page']) : 1;

		$total = count($videos);

		if (!$total || $page > (int)ceil($total / self::LIMIT)) {
			return $this->forward('error/not_found');
		}

		$this->enforceAddress('videos', $this->url->link('product/video', ($page > 1) ? 'page=' . $page : '', 'SSL'));

		$this->language->load('product/video');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$heading = $this->language->get('heading_title');
		$store = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$this->document->setTitle(($page > 1) ? sprintf($this->language->get('text_title_page'), $heading, $page) : $heading);
		$this->document->setDescription($this->escape(sprintf($this->language->get('text_meta_description'), $store)));
		$this->document->addLink($this->url->link('product/video', ($page > 1) ? 'page=' . $page : '', 'SSL'), 'canonical');

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			['text' => $heading, 'href' => $this->url->link('product/video', '', 'SSL')]
		]);

		$this->data['heading_title'] = $heading;
		$this->data['text_intro'] = $this->language->get('text_intro');
		$this->data['text_play'] = $this->language->get('text_play');

		$base = $this->getBase();

		$this->data['videos'] = [];

		foreach (array_slice($videos, ($page - 1) * self::LIMIT, self::LIMIT) as $video) {
			$product_info = $this->model_catalog_product->getProduct((int)$video['product_id']);

			$name = $product_info ? videoWatchName((string)$product_info['name']) : $video['title'];

			$thumb = $this->model_tool_image->resize($video['poster'], 300, 300);

			$this->data['videos'][] = [
				'name'     => $this->escape($name),
				'thumb'    => $thumb ?: videoWatchImageUrl($base, $video['poster']),
				'duration' => ($video['duration'] > 0) ? sprintf('%d:%02d', intdiv($video['duration'], 60), $video['duration'] % 60) : '',
				'href'     => $this->url->link('product/video/watch', 'video=' . $video['code'], 'SSL')
			];
		}

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = self::LIMIT;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('product/video', 'page={page}', 'SSL');

		$this->data['pagination'] = ($total > self::LIMIT) ? $pagination->render() : '';

		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('product/video_list');

		$this->renderPage();
	}

	/**
	 * The watch page of one video.
	 */
	public function watch() {
		$code = (isset($this->request->get['video']) && is_string($this->request->get['video'])) ? $this->request->get['video'] : '';

		$videos = videoWatchList($this->db, (int)$this->config->get('config_store_id'));

		if (!preg_match('/^[a-f0-9]{8}$/', $code) || !isset($videos[$code])) {
			return $this->forward('error/not_found');
		}

		$video = $videos[$code];

		$this->load->model('catalog/product');

		$products = [];

		foreach (array_slice($video['products'], 0, self::OPTIONS_MAX) as $product_id) {
			$product_info = $this->model_catalog_product->getProduct((int)$product_id);

			if ($product_info) {
				$products[(int)$product_id] = $product_info;
			}
		}

		if (!$products) {
			return $this->forward('error/not_found');
		}

		$url = $this->url->link('product/video/watch', 'video=' . $code, 'SSL');

		// An old or mistyped name in the address is sent to the current one
		$this->enforceAddress(videoWatchPath($video), $url);

		$this->language->load('product/video');

		$main = $products[$video['product_id']] ?? reset($products);

		$name = videoWatchName((string)$main['name']);
		$description = videoWatchText((string)$main['description']);
		$store = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$meta_description = trim(mb_strimwidth(sprintf($this->language->get('text_meta_watch'), $name, ($description !== '') ? $description : $store), 0, 158, '...', 'UTF-8'));

		$this->document->setTitle($this->escape(sprintf($this->language->get('text_title'), $name)));
		$this->document->setDescription($this->escape($meta_description));
		$this->document->addLink($url, 'canonical');

		$this->document->addStyle('catalog/view/javascript/jquery/plyr/dist/plyr.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/plyr/dist/plyr.min.js');

		$base = $this->getBase();

		$video_url = videoWatchImageUrl($base, $video['video']);
		$poster_url = videoWatchImageUrl($base, $video['poster']);
		$mime_type = 'video/' . strtolower(pathinfo($video['video'], PATHINFO_EXTENSION));

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			['text' => $this->language->get('heading_title'), 'href' => $this->url->link('product/video', '', 'SSL')],
			['text' => $this->escape($name), 'href' => $url]
		]);

		$this->data['heading_title'] = $this->escape($name);
		$this->data['description'] = $this->escape(mb_strimwidth($description, 0, 400, '...', 'UTF-8'));

		$this->data['video_file'] = $video_url;
		$this->data['video_poster'] = $poster_url;
		$this->data['video_type'] = $mime_type;
		$this->data['video_width'] = $video['width'];
		$this->data['video_height'] = $video['height'];
		$this->data['video_icons'] = $base . 'catalog/view/javascript/jquery/plyr/dist/plyr.svg';

		// The shape of the video decides how wide the player may be
		if ($video['width'] > 0 && $video['height'] > $video['width']) {
			$this->data['video_shape'] = 'portrait';
		} elseif ($video['width'] > 0 && $video['height'] > 0 && ($video['width'] / $video['height']) < 1.2) {
			$this->data['video_shape'] = 'square';
		} else {
			$this->data['video_shape'] = 'landscape';
		}

		$price = $this->getPrice($products);

		$this->data['price'] = $price['price'];
		$this->data['price_old'] = $price['price_old'];
		$this->data['price_from'] = $price['from'];

		$this->data['product'] = $this->url->link('product/product', 'product_id=' . (int)$main['product_id'], 'SSL');

		$this->data['options'] = [];

		if (count($products) > 1) {
			foreach ($products as $product_id => $product_info) {
				$this->data['options'][] = [
					'name'  => $this->escape(videoWatchVariant((string)$product_info['name'])),
					'title' => $this->escape(videoWatchText((string)$product_info['name'])),
					'href'  => $this->url->link('product/product', 'product_id=' . $product_id, 'SSL')
				];
			}
		}

		$this->data['videos_page'] = $this->url->link('product/video', '', 'SSL');

		$this->data['text_from'] = $this->language->get('text_from');
		$this->data['text_options'] = $this->language->get('text_options');
		$this->data['text_all_videos'] = $this->language->get('text_all_videos');
		$this->data['button_view_product'] = $this->language->get('button_view_product');

		// schema.org VideoObject
		$page_url = str_replace('&amp;', '&', $url);

		$schema = [
			'@context'     => 'https://schema.org',
			'@type'        => 'VideoObject',
			'name'         => $name,
			'description'  => ($description !== '') ? mb_substr($description, 0, 500) : $name,
			'thumbnailUrl' => $poster_url,
			'uploadDate'   => date(DATE_ATOM, (int)filemtime(DIR_IMAGE . $video['video'])),
			'contentUrl'   => $video_url,
			'url'          => $page_url
		];

		if ($video['duration'] >= 1) {
			$schema['duration'] = 'PT' . (($video['duration'] >= 60) ? intdiv($video['duration'], 60) . 'M' : '') . ($video['duration'] % 60) . 'S';
		}

		if ($video['width'] > 0 && $video['height'] > 0) {
			$schema['width'] = $video['width'];
			$schema['height'] = $video['height'];
		}

		$this->data['schema'] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?: '';

		// Open Graph tags
		$tags = [
			'og:type'        => 'video.other',
			'og:title'       => $name,
			'og:description' => $meta_description,
			'og:url'         => $page_url,
			'og:site_name'   => $store,
			'og:image'       => $poster_url,
			'og:video'       => $video_url,
			'og:video:type'  => $mime_type
		];

		if ($video['width'] > 0 && $video['height'] > 0) {
			$tags['og:video:width'] = $video['width'];
			$tags['og:video:height'] = $video['height'];
		}

		foreach ($tags as $property => $content) {
			$this->document->addMeta('<meta property="' . $property . '" content="' . $this->escape((string)$content) . '">');
		}

		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('product/video_watch');

		$this->renderPage();
	}

	/**
	 * The price shown on a watch page: the price of the options, or the lowest one when they differ.
	 * Nothing is shown when prices are hidden, reserved for logged in customers or given on request.
	 *
	 * @param array<int, array<string, mixed>> $products
	 *
	 * @return array{price: string, price_old: string, from: bool}
	 */
	private function getPrice(array $products): array {
		$result = ['price' => '', 'price_old' => '', 'from' => false];

		if ($this->config->get('config_price_hide') || ($this->config->get('config_customer_price') && !$this->customer->isLogged())) {
			return $result;
		}

		$prices = [];

		foreach ($products as $product_info) {
			$amount = (float)$product_info['special'] ? (float)$product_info['special'] : (float)$product_info['price'];

			if ($product_info['quote'] || $amount <= 0) {
				continue;
			}

			$prices[] = [
				'amount'       => (float)$this->tax->calculate($amount, $product_info['tax_class_id'], $this->config->get('config_tax')),
				'product_info' => $product_info
			];
		}

		if (!$prices) {
			return $result;
		}

		usort($prices, fn(array $a, array $b): int => $a['amount'] <=> $b['amount']);

		$lowest = $prices[0];
		$highest = $prices[count($prices) - 1];

		$currency = $this->config->get('config_currency');

		$result['price'] = $this->currency->format($lowest['amount'], $currency);
		$result['from'] = (round($lowest['amount'], 2) < round($highest['amount'], 2));

		// The old price is only shown for a special offer that applies to every option
		if (!$result['from'] && (float)$lowest['product_info']['special']) {
			$old = $this->tax->calculate($lowest['product_info']['price'], $lowest['product_info']['tax_class_id'], $this->config->get('config_tax'));

			$result['price_old'] = $this->currency->format($old, $currency);
		}

		return $result;
	}

	/**
	 * With SEO URLs enabled, each page has one address: any other one is redirected to it.
	 *
	 * @param string $path SEO path of the page
	 * @param string $url  Link to the page
	 */
	private function enforceAddress(string $path, string $url): void {
		if (!$this->config->get('config_seo_url') || ($this->request->server['REQUEST_METHOD'] !== 'GET')) {
			return;
		}

		$requested = (isset($this->request->get['_route_']) && is_string($this->request->get['_route_'])) ? trim($this->request->get['_route_'], '/') : '';

		if ($requested !== $path) {
			$this->redirect($url, 301);
		}
	}

	private function getBase(): string {
		return (string)($this->config->get('config_secure') ? $this->config->get('config_ssl') : $this->config->get('config_url'));
	}

	private function escape(string $text): string {
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * @param array<int, array<string, string>> $crumbs
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getBreadcrumbs(array $crumbs): array {
		$breadcrumbs = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			]
		];

		foreach ($crumbs as $crumb) {
			$breadcrumbs[] = [
				'text'      => $crumb['text'],
				'href'      => $crumb['href'],
				'separator' => $this->language->get('text_separator')
			];
		}

		return $breadcrumbs;
	}

	/**
	 * No column or content modules: the video stays the main subject of these pages.
	 */
	private function renderPage(): void {
		$this->children = [
			'common/footer',
			'common/header'
		];

		$this->response->setOutput($this->render());
	}
}
