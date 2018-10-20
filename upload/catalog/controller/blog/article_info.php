<?php
class ControllerBlogArticleInfo extends Controller {

	public function index() {
		$this->language->load('blog/article');

		$this->document->setTitle(($this->config->get('blog_heading')) ? $this->config->get('blog_heading') : $this->language->get('heading_title'));

		$this->document->addStyle('catalog/view/theme/default/stylesheet/blog-system.css');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/article_list', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$this->load->model('blog/article');
		$this->load->model('tool/image');

		if (isset($this->request->get['blog_article_id'])) {
			$blog_article_id = $this->request->get['blog_article_id'];
		} else {
			$blog_article_id = 0;
		}

		$this->data['blog_article_id'] = $blog_article_id;

		if ($this->config->get('product_related_heading')) {
			$this->data['text_related_product'] = $this->config->get('product_related_heading');
		} else {
			$this->data['text_related_product'] = $this->language->get('text_related_product');
		}

		if ($this->config->get('comment_related_heading')) {
			$this->data['text_related_comment'] = $this->config->get('comment_related_heading');
		} else {
			$this->data['text_related_comment'] = $this->language->get('text_related_comment');
		}

		$this->data['text_author_information'] = $this->language->get('text_author_information');
		$this->data['text_write_comment'] = $this->language->get('text_write_comment');
		$this->data['text_note'] = $this->language->get('text_note');
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_from'] = $this->language->get('text_from');
		$this->data['text_no_comment'] = $this->language->get('text_no_comment');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_captcha'] = $this->language->get('entry_captcha');
		$this->data['entry_review'] = $this->language->get('entry_review');

		$this->data['button_cart'] = $this->language->get('button_cart');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_quote'] = $this->language->get('button_quote');
		$this->data['button_wishlist'] = $this->language->get('button_wishlist');
		$this->data['button_submit'] = $this->language->get('button_submit');

		$article_info = $this->model_blog_article->getArticle($blog_article_id);

		if ($article_info) {
			$this->data['heading_title'] = $article_info['article_title'];

			$this->document->setDescription($article_info['meta_description']);
			$this->document->setKeywords($article_info['meta_keyword']);

			$this->data['breadcrumbs'][] = array(
				'text'      => $article_info['article_title'],
				'href'      => $this->url->link('blog/article_info', 'blog_article_id=' . $article_info['blog_article_id'], 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['article_info'] = $article_info;
			$this->data['article_info_found'] = $article_info;

			if ($article_info['image']) {
				$this->data['image'] = $this->model_tool_image->resize($article_info['image'], 180, 180);
			} else {
				$this->data['image'] = '';
			}

			$this->data['minimum_height'] = 200;

			$this->data['author_url'] = $this->url->link('blog/article_author', 'blog_author_id=' . $article_info['blog_author_id'], 'SSL');

			$total_comments = $this->model_blog_article->getTotalComments($blog_article_id);

			if ($total_comments != 1) {
				$this->data['total_comment'] = $total_comments . " " . $this->language->get('text_comments');
			} else {
				$this->data['total_comment'] = $total_comments . " " . $this->language->get('text_comment');
			}

			$this->data['stock_checkout'] = $this->config->get('config_stock_checkout');

			// Related products
			$this->load->model('catalog/product');
			$this->load->model('catalog/offer');
			$this->load->model('account/customer');

			$offers = $this->model_catalog_offer->getListProductOffers(0);

			$related_product = $this->model_blog_article->getArticleProductRelated($blog_article_id);

			$this->data['products'] = array();

			foreach ($related_product as $product) {
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], 100, 100);
				} else {
					$image = false;
				}

				if ($product_info['manufacturer']) {
					$manufacturer = $product_info['manufacturer'];
				} else {
					$manufacturer = false;
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					if (($product_info['price'] == '0.0000') && $this->config->get('config_price_free')) {
						$price = $this->language->get('text_free');
					} else {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
					}
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special_label = $this->model_tool_image->resize($this->config->get('config_label_special'), 50, 50);
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special_label = false;
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$product_info['rating'];
				} else {
					$rating = false;
				}

				if ($product_info['quantity'] <= 0) {
					$stock_label = $this->model_tool_image->resize($this->config->get('config_label_stock'), 50, 50);
				} else {
					$stock_label = false;
				}

				if (in_array($product_info['product_id'], $offers, true)) {
					$offer_label = $this->model_tool_image->resize($this->config->get('config_label_offer'), 50, 50);
					$offer = true;
				} else {
					$offer_label = false;
					$offer = false;
				}

				$age_logged = false;
				$age_checked = false;

				if ($this->config->get('config_customer_dob') && ($product_info['age_minimum'] > 0)) {
					if ($this->customer->isLogged() && $this->customer->isSecure()) {
						$age_logged = true;

						$date_of_birth = $this->model_account_customer->getCustomerDateOfBirth($this->customer->getId());

						if ($date_of_birth && ($date_of_birth != '0000-00-00')) {
							$customer_age = date_diff(date_create($date_of_birth), date_create('today'))->y;

							if ($customer_age >= $product_info['age_minimum']) {
								$age_checked = true;
							}
						}
					}
				}

				if ($product_info['quote']) {
					$quote = $this->url->link('information/quote', '', 'SSL');
				} else {
					$quote = false;
				}

				$this->data['products'][] = array(
					'product_id'      => $product_info['product_id'],
					'thumb'           => $image,
					'stock_label'     => $stock_label,
					'offer_label'     => $offer_label,
					'special_label'   => $special_label,
					'offer'           => $offer,
					'manufacturer'    => $manufacturer,
					'name'            => $product_info['name'],
					'age_minimum'     => ($product_info['age_minimum'] > 0) ? (int)$product_info['age_minimum'] : '',
					'age_logged'      => $age_logged,
					'age_checked'     => $age_checked,
					'stock_status'    => $product_info['stock_status'],
					'stock_quantity'  => $product_info['quantity'],
					'stock_remaining' => ($product_info['subtract']) ? sprintf($this->language->get('text_remaining'), $product_info['quantity']) : '',
					'quote'           => $quote,
					'price'           => $price,
					'price_option'    => $this->model_catalog_product->hasOptionPriceIncrease($product_info['product_id']),
					'special'         => $special,
					'tax'             => $tax,
					'rating'          => $rating,
					'reviews'         => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
					'href'            => $this->url->link('product/product', 'product_id=' . $product_info['product_id'], 'SSL')
				);
			}

			$author_info = $this->model_blog_article->getAuthorInformation($article_info['blog_author_id']);

			if ($author_info) {
				$this->data['blog_author_id'] = $article_info['blog_author_id'];

				$this->data['author_name'] = $author_info['name'];

				if ($author_info['image']) {
					$this->data['author_image'] = $this->model_tool_image->resize($author_info['image'], 80, 80);
				} else {
					$this->data['author_image'] = $this->model_tool_image->resize('no_image.png', 80, 80);
				}

				if ($author_info['description']) {
					$this->data['author_description'] = html_entity_decode($author_info['description'], ENT_QUOTES, 'UTF-8');
				} else {
					$this->data['author_description'] = '';
				}
			}

			$this->data['article_date_modified'] = date($this->language->get('text_date_format'), strtotime($article_info['date_modified']));

			$this->data['article_additional_description'] = $this->model_blog_article->getArticleAdditionalDescription($blog_article_id);

			// Related article information
			$related_articles = $this->model_blog_article->getRelatedArticles($article_info['blog_article_id']);

			$this->data['related_articles'] = $related_articles;

			$this->data['text_related_article'] = $this->language->get('text_related_article');
			$this->data['text_posted_by'] = $this->language->get('text_posted_by');
			$this->data['text_updated'] = $this->language->get('text_updated');
			$this->data['text_comment_on_article'] = $this->language->get('text_comment_on_article');
			$this->data['text_view_comment'] = $this->language->get('text_view_comment');

			$this->data['button_continue_reading'] = $this->language->get('button_continue_reading');

			// Add view
			$this->model_blog_article->addBlogView($article_info['blog_article_id']);

			// AddThis
			if ($this->config->get('config_addthis')) {
				$this->data['addthis'] = $this->config->get('config_addthis');
			} else {
				$this->data['addthis'] = false;
			}

			// Captcha
			if (isset($this->request->post['captcha'])) {
				$this->data['captcha'] = $this->request->post['captcha'];
			} else {
				$this->data['captcha'] = '';
			}

			$this->data['captcha_image'] = $this->url->link('blog/article_info/captcha', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/article_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/blog/article_info.tpl';
			} else {
				$this->template = 'default/template/blog/article_info.tpl';
			}

			$this->children = array(
				'common/content_higher',
				'common/content_high',
				'common/content_left',
				'common/content_right',
				'common/content_low',
				'common/content_lower',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());

		} else {
			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('blog/article_list', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->document->setTitle($this->language->get('text_article_error'));

			$this->data['heading_title'] = $this->language->get('text_article_error');

			$this->data['text_error'] = $this->language->get('text_article_error');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('common/home', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}

			$this->children = array(
				'common/content_higher',
				'common/content_high',
				'common/content_left',
				'common/content_right',
				'common/content_low',
				'common/content_lower',
				'common/footer',
				'common/header'
			);

			$this->response->addheader($this->request->server['SERVER_PROTOCOL'] . ' 404 not found');
			$this->response->setOutput($this->render());
		}
	}

	public function comment() {
		$this->language->load('blog/article');

		$this->load->model('blog/article');

		$this->data['text_on'] = $this->language->get('text_on');
		$this->data['text_said'] = $this->language->get('text_said');
		$this->data['text_no_comment'] = $this->language->get('text_no_comment');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 5;
		}

		$this->data['text_reply_comment'] = $this->language->get('text_reply_comment');

		$this->data['comments'] = array();

		$comment_total = $this->model_blog_article->getTotalCommentsByArticleId($this->request->get['blog_article_id']);

		$results = $this->model_blog_article->getCommentsByArticle($this->request->get['blog_article_id'], ($page - 1) * 10, 10, 0);

		foreach ($results as $result) {
			$comment_reply = '';

			$comment_reply = $this->model_blog_article->getCommentsByArticle($this->request->get['blog_article_id'], 0, 1000, $result['blog_comment_id']);

			$this->data['comments'][] = array(
				'blog_article_id' => $result['blog_article_id'],
				'blog_comment_id' => $result['blog_comment_id'],
				'comment_reply'   => $comment_reply,
				'author'          => ucwords($result['author']),
				'comment'         => $result['comment'],
				'date_added'      => date($this->language->get('text_date_format_long'), strtotime($result['date_added']))
			);
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $comment_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('blog/article_info/comment', 'blog_article_id=' . $this->request->get['blog_article_id'] . $url, 'SSL');

		$this->data['pagination'] = $pagination->render();

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/article_comment.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/blog/article_comment.tpl';
		} else {
			$this->template = 'default/template/blog/article_comment.tpl';
		}

		$this->response->setOutput($this->render());
	}

	public function writeComment() {
		$this->language->load('blog/article');

		$this->load->model('blog/article');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 3) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
				$json['error'] = $this->language->get('error_captcha');
			}

			if (!isset($json['error'])) {
				$this->model_blog_article->addArticleComment($this->request->get['blog_article_id'], $this->request->post);

				if ($this->config->get('blog_comment_auto_approval')) {
					$json['success'] = $this->language->get('text_success');
				} else {
					$json['success'] = $this->language->get('text_success_approval');
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function captcha() {
		$this->load->library('captcha');

		$captcha = new Captcha();

		$this->session->data['captcha'] = $captcha->getCode();

		$captcha->showImage($this->config->get('config_captcha_font'));
	}
}
