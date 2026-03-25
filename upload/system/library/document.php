<?php
class Document {
	/**
	 * @var string
	 */
	private string $title = '';
	/**
	 * @var string
	 */
	private string $description = '';
	/**
	 * @var string
	 */
	private string $keywords = '';
	/**
	 * @var array<string, array<string, string>>
	 */
	private array $links = [];
	/**
	 * @var array<string, array<string, string>>
	 */
	private array $styles = [];
	/**
	 * @var array<string, array<string, array<string, string>>>
	 */
	private array $scripts = [];
	/**
	 * @var array<string, string>
	 */
	private array $meta = [];

	/**
	 * Set Title
	 *
	 * @param string $title
	 *
	 * @return void
	 */
	public function setTitle(string $title): void {
		$this->title = $title;
	}

	/**
	 * Get Title
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Set Description
	 *
	 * @param string $description
	 *
	 * @return void
	 */
	public function setDescription(string $description): void {
		$this->description = $description;
	}

	/**
	 * Get Description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set Keywords
	 *
	 * @param string $keywords
	 */
	public function setKeywords(string $keywords): void {
		$this->keywords = $keywords;
	}

	/**
	 * Get Keywords
	 *
	 * @return string
	 */
	public function getKeywords(): string {
		return $this->keywords;
	}

	/**
	 * Add Link
	 *
	 * @param string $href
	 * @param string $rel
	 *
	 * @return void
	 */
	public function addLink(string $href, string $rel): void {
		$this->links[md5($href)] = [
			'href' => $href,
			'rel'  => $rel
		];
	}

	/**
	 * Get Links
	 *
	 * @return array<string, array<string, string>>
	 */
	public function getLinks(): array {
		return $this->links;
	}

	/**
	 * Add Style
	 *
	 * @param string $href
	 * @param string $rel
	 * @param string $media
	 *
	 * @return void
	 */
	public function addStyle(string $href, string $rel = 'stylesheet', string $media = 'screen'): void {
		$this->styles[md5($href)] = [
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		];
	}

	/**
	 * Get Styles
	 *
	 * @return array<string, array<string, string>>
	 */
	public function getStyles(): array {
		return $this->styles;
	}

	/**
	 * Add Script
	 *
	 * @param string script
	 *
	 * @return void
	 */
	public function addScript(string $script): void {
		$this->scripts[md5($script)] = $script;
	}

	/**
	 * Get Scripts
	 *
	 * @return array<string, array<string, string>>
	 */
	public function getScripts(): array {
		return $this->scripts;
	}

	/**
	 * Add Meta
	 *
	 * @param string $meta
	 *
	 * @return void
	 */
	public function addMeta(string $meta): void {
		$this->meta[$meta] = $meta;
	}

	/**
	 * Get Meta
	 *
	 * @return array<string, string>
	 */
	public function getMeta(): array {
		return $this->meta;
	}
}
