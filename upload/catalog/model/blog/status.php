<?php
/**
 * Class ModelBlogStatus
 *
 * @package NivoCart
 */
class ModelBlogStatus extends Model {
	/**
	 * Functions Check
	 */
	public function checkBlog(): bool {
		$table_name = $this->db->escape('blog_%');

		$table = DB_PREFIX . $table_name;

		$query = $this->db->query("SHOW TABLES LIKE '{$table}'");

		return ($query->num_rows) ? true : false;
	}

	//	$table_names = [
	//		'blog_article',
	//		'blog_article_description',
	//		'blog_article_description_additional',
	//		'blog_article_product_related',
	//		'blog_article_to_category',
	//		'blog_article_to_layout',
	//		'blog_article_to_store',
	//		'blog_author',
	//		'blog_author_description',
	//		'blog_category',
	//		'blog_category_description',
	//		'blog_category_to_layout',
	//		'blog_category_to_store',
	//		'blog_comment',
	//		'blog_related_article',
	//		'blog_view'
	//	];
}
