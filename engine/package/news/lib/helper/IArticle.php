<?php
namespace wfw\engine\package\news\lib\helper;

/**
 * Article
 */
interface IArticle {
	/**
	 * @return string
	 */
	public function getCreationDate():string;

	/**
	 * @return string
	 */
	public function getEditDate():string;

	/**
	 * @return string
	 */
	public function getContent():string;

	/**
	 * @return string
	 */
	public function getTitle():string;

	/**
	 * @return string
	 */
	public function getImage():string;

	/**
	 * @return string
	 */
	public function getId():string;

	/**
	 * @return string
	 */
	public function getDescription():string;

	/**
	 * @return string
	 */
	public function getSlug():string;
}