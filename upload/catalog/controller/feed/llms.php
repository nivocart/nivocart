<?php
/**
 * Class ControllerFeedLlms
 *
 * Serves /llms.txt: a plain text, Markdown formatted summary of the store for AI agents and crawlers.
 *
 * @package NivoCart
 */
class ControllerFeedLlms extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->load->model('catalog/information');
		$this->load->model('catalog/category');

		$output = '# ' . $this->clean((string)$this->config->get('config_name')) . "\n\n";

		$description = $this->clean((string)$this->config->get('config_meta_description'));

		if ($description !== '') {
			$output .= '> ' . $description . "\n\n";
		}

		// Shop
		$output .= "## Shop\n\n";
		$output .= $this->line('All products', $this->url->link('product/product_list', '', 'SSL'));
		$output .= $this->line('Special offers', $this->url->link('product/special', '', 'SSL'));
		$output .= $this->line('Brands', $this->url->link('product/manufacturer', '', 'SSL'));
		$output .= "\n";

		// Top level categories
		$categories = $this->model_catalog_category->getCategories(0);

		if ($categories) {
			$output .= "## Categories\n\n";

			foreach ($categories as $category) {
				$output .= $this->line($category['name'], $this->url->link('product/category', 'path=' . (int)$category['category_id'], 'SSL'));
			}

			$output .= "\n";
		}

		// Information pages and contact
		$output .= "## Information\n\n";

		foreach ($this->model_catalog_information->getInformations() as $information) {
			$output .= $this->line($information['title'], $this->url->link('information/information', 'information_id=' . (int)$information['information_id'], 'SSL'));
		}

		$output .= $this->line('Contact us', $this->url->link('information/contact', '', 'SSL'));
		$output .= $this->line('Site map', $this->url->link('information/sitemap', '', 'SSL'));

		$this->response->addHeader('Content-Type: text/plain; charset=utf-8');
		$this->response->setOutput($output);
	}

	/**
	 * Plain single line text (no HTML, entities or line breaks)
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	protected function clean(string $text): string {
		return trim((string)preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'))));
	}

	/**
	 * Markdown list item with a link
	 *
	 * @param string $title
	 * @param string $url
	 *
	 * @return string
	 */
	protected function line(string $title, string $url): string {
		return '- [' . str_replace(['[', ']'], ['\\[', '\\]'], $this->clean($title)) . '](' . html_entity_decode($url, ENT_QUOTES, 'UTF-8') . ")\n";
	}
}
