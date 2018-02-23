<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/nochex.php');
$id_cart = $_GET["id_cart"];
// $id_cart = intval(Tools::getValue('id_cart', 0));
// $id_module = intval(Tools::getValue('id_module', 0));

$myid_order = Order::getOrderByCartId(intval($id_cart));
$smarty->assign('nochexorder', $myid_order);
$smarty->display(_PS_MODULE_DIR_.'nochex/payment_return.tpl');
include(dirname(__FILE__).'/../../footer.php');
?>
