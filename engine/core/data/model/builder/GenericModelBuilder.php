<?php
namespace wfw\engine\core\data\model\builder;

use wfw\engine\core\data\model\arithmeticSearch\ArithmeticParser;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSearcher;
use wfw\engine\core\data\model\arithmeticSearch\ArithmeticSolver;
use wfw\engine\core\data\model\IModel;

/**
 * Class GenericModelBuilder
 *
 * @package wfw\daemons\model\builder
 */
class GenericModelBuilder implements IModelBuilder {
	/**
	 * @param string $model
	 *
	 * @param array  $params
	 * @return IModel
	 */
	public function buildModel(string $model, array $params = []): IModel {
		if(count($params) > 0) return new $model(...$params);
		else return new $model(
			new ArithmeticSearcher(new ArithmeticSolver(new ArithmeticParser()))
		);
	}
}