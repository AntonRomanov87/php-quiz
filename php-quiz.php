<?php 

class ProductManager
{
	private $id;
 
	public function setId($id)
	{
		$this->id = $id;
	}
 
	public function getProduct()
	{
		$db = DB::getInstance();	
		return $db->fetchAssoc("SELECT * FROM products WHERE id = '{$this->id}'");
	}

	public function getDp($product)
	{
		if ($product['discount'] > 0) {
			return $product['price'] - $product['discount'] / 100 * $product['price'];
		} else {
			return $product['price'];
		}
	}
 
	public function getProductList($type)
	{
		$db = DB::getInstance();
		$result = $db->fetchAll("SELECT id FROM products WHERE type = '$type' LIMIT 15");
		$list = [];
 
		foreach ($result as $row) {
			$this->setId($row['id']);
			$list[] = $this->getProduct();
		}
 
		return $list;
	}
 
	public static function incrementViews($id)
	{
		try {
			$db = DB::getInstance();
			$db->executeQuery("UPDATE products SET views_count = views_count + 1 WHERE id = $id");
		} catch (Exception $e) {
			print("Database error!");
		}	
	}

	public static function updateLastView($id)
	{
		try {
			$db = DB::getInstance();
			$db->executeQuery("UPDATE products SET last_view_at = NOW() WHERE id = $id");
		} catch (Exception $e) {
			print("Database error!");
		}	
	}
}

class ProductController
{
	// Контроллер страницы просмотра товара
	public function viewAction()
	{
		$itemId = $_GET['itemId'];
 
		$productManager = new ProductManager();
		$productManager->setId($itemId);
		$product = $productManager->getProduct();
		$dp = $productManager->getDp($product);
 
		ProductManager::incrementViews($itemId);
		ProductManager::updateLastView($itemId);
 
		View::renderTemplate('product/view.html.twig', [
			'product'		=>	$product,
			'dp'			=>	$dp,
			'recommended'	=>	$productManager->getProductList('recommended'),
		]);
	}
} 
