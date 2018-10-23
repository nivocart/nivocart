<?php
class ControllerBlogComment extends Controller {
	private $error = array();

	public function index() {
		$url = $this->request->get['route'];

		if ($this->validateDatabase()) {
			$this->language->load('blog/install');

			$this->document->setTitle($this->language->get('error_database'));

			$this->data['install_blog'] = $this->url->link('modification/blog', 'token=' . $this->session->data['token'] . '&url=' . $url, 'SSL');

			$this->data['text_install_message'] = $this->language->get('text_install_message');
			$this->data['text_upgrade'] = $this->language->get('text_upgrade');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);

			$this->template = 'blog/notification.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);

			$this->response->setOutput($this->render());
		} else {
			$this->getData();
		}
	}

	public function validateDatabase() {
		$database_not_found = $this->getChild('blog/install');

		if ($database_not_found) {
			return true;
		}

		return false;
	}

	public function getData() {
		$this->language->load('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		$this->getList();
	}

	public function insert() {
		$this->language->load('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm())) {
			$this->model_blog_comment->addArticleComment($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->post['apply'])) {
				$blog_comment_id = $this->session->data['new_blog_comment_id'];

				if ($blog_comment_id) {
					unset($this->session->data['new_blog_comment_id']);

					$this->redirect($this->url->link('blog/comment/update', 'token=' . $this->session->data['token'] . '&blog_comment_id=' . $blog_comment_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm())) {
			$this->model_blog_comment->editArticleComment($this->request->get['blog_comment_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->post['apply'])) {
				$blog_comment_id = $this->request->get['blog_comment_id'];

				if ($blog_comment_id) {
					$this->redirect($this->url->link('blog/comment/update', 'token=' . $this->session->data['token'] . '&blog_comment_id=' . $blog_comment_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('blog/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/comment');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $blog_comment_id) {
				$this->model_blog_comment->deleteArticleComment($blog_comment_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->redirect($this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function getList() {
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'bc.date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		$this->data['insert'] = $this->url->link('blog/comment/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('blog/comment/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->data['comments'] = array();

		$data = array(
			'sort' => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$comment_total = $this->model_blog_comment->getTotalArticleComments($data);

		$results = $this->model_blog_comment->getArticleComments($data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('blog/comment/update', 'token=' . $this->session->data['token'] . '&blog_comment_id=' . $result['blog_comment_id'] . $url, 'SSL')
			);

			$replies = $this->model_blog_comment->getTotalCommentReplies($result['blog_comment_id']);

			$this->data['comments'][] = array(
				'blog_comment_id' => $result['blog_comment_id'],
				'blog_article_id' => $result['blog_article_id'],
				'article_title'   => $result['article_title'],
				'author_name'     => $result['author'],
				'status'          => $result['status'],
				'replies'         => ($replies == 0) ? '<span style="color:#F50;">' . (int)$replies . '</span>' : (int)$replies,
				'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'        => isset($this->request->post['selected']) && in_array($result['blog_comment_id'], $this->request->post['selected']),
				'action'          => $action
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');

		$this->data['column_article_title'] = $this->language->get('column_article_title');
		$this->data['column_author_name'] = $this->language->get('column_author_name');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_replies'] = $this->language->get('column_replies');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
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

		$this->data['sort_article_title'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=bad.article_title' . $url, 'SSL');
		$this->data['sort_author_name'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=bc.name' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=bc.status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=bc.date_added' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();

		$pagination->total = $comment_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'blog/comment_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_comment'] = $this->language->get('tab_comment');

		$this->data['entry_author'] = $this->language->get('entry_author');
		$this->data['entry_article'] = $this->language->get('entry_article');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_comment'] = $this->language->get('entry_comment');
		$this->data['entry_reply_comment'] = $this->language->get('entry_reply_comment');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_reply'] = $this->language->get('button_add_reply');
		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

	 	if (isset($this->error['article_title'])) {
			$this->data['error_article_title'] = $this->error['article_title'];
		} else {
			$this->data['error_article_title'] = '';
		}

		if (isset($this->error['author'])) {
			$this->data['error_author'] = $this->error['author'];
		} else {
			$this->data['error_author'] = '';
		}

	 	if (isset($this->error['comment'])) {
			$this->data['error_comment'] = $this->error['comment'];
		} else {
			$this->data['error_comment'] = '';
		}

		if (isset($this->error['reply_author'])) {
			$this->data['error_reply_author'] = $this->error['reply_author'];
		} else {
			$this->data['error_reply_author'] = array();
		}

		if (isset($this->error['reply_comment'])) {
			$this->data['error_reply_comment'] = $this->error['reply_comment'];
		} else {
			$this->data['error_reply_comment'] = array();
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		if (isset($this->request->get['blog_comment_id'])) {
			$this->data['action'] = $this->url->link('blog/comment/update', 'token=' . $this->session->data['token'] . '&blog_comment_id=' . $this->request->get['blog_comment_id'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('blog/comment/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if ((isset($this->request->get['blog_comment_id'])) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$comment_info = $this->model_blog_comment->getArticleComment($this->request->get['blog_comment_id']);
		}

		if (isset($this->request->post['article_title'])) {
			$this->data['article_title'] = $this->request->post['article_title'];
		} elseif (!empty($comment_info)) {
			$this->data['article_title'] = $comment_info['article_title'];
		} else {
			$this->data['article_title'] = '';
		}

		if (isset($this->request->post['author_name'])) {
			$this->data['author_name'] = $this->request->post['author_name'];
		} elseif (!empty($comment_info)) {
			$this->data['author_name'] = $comment_info['author'];
		} else {
			$this->data['author_name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($comment_info)) {
			$this->data['status'] = $comment_info['status'];
		} else {
			$this->data['status'] = 1;
		}

		if (isset($this->request->post['comment'])) {
			$this->data['comment'] = $this->request->post['comment'];
		} elseif (!empty($comment_info)) {
			$this->data['comment'] = $comment_info['comment'];
		} else {
			$this->data['comment'] = '';
		}

		if (isset($this->request->post['comment_reply'])) {
			$this->data['comment_reply'] = $this->request->post['comment_reply'];
		} elseif (isset($this->request->get['blog_comment_id'])) {
			$this->data['comment_reply'] = $this->model_blog_comment->getCommentReply($this->request->get['blog_comment_id']);
		} else {
			$this->data['comment_reply'] = array();
		}

		$this->template = 'blog/comment_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/comment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (utf8_strlen($this->request->post['author_name']) < 3 || utf8_strlen($this->request->post['author_name']) > 64) {
			$this->error['author'] = $this->language->get('error_author');
		}

		if ($this->request->post['article_title'] == '') {
			$this->error['article_title'] = $this->language->get('error_article_title');
		} else {
			$found = $this->model_blog_comment->checkArticleTitle($this->request->post['article_title']);

			if (!$found) {
				$this->error['article_title'] = $this->language->get('error_article_title_not_found');
			}
		}

		if (utf8_strlen($this->request->post['comment']) < 3 || utf8_strlen($this->request->post['comment']) > 1000) {
			$this->error['comment'] = $this->language->get('error_comment');
		}

		if (isset($this->request->post['comment_reply'])) {
			foreach ($this->request->post['comment_reply'] as $key => $value) {
				if (utf8_strlen($value['author']) < 3 || utf8_strlen($value['author']) > 64) {
					$this->error['reply_author'][$key] = $this->language->get('error_author');
				}

				if (utf8_strlen($value['comment']) < 3 || utf8_strlen($value['comment']) > 1000) {
					$this->error['reply_comment'][$key] = $this->language->get('error_comment');
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'blog/comment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
