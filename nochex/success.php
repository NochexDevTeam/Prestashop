<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/nochex.php');

$id_cart = Tools::getValue("id_cart");

$myid_order = Order::getOrderByCartId($id_cart);

$smarty->assign('nochexorder', $myid_order);
$smarty->display(_PS_MODULE_DIR_.'nochex/views/payment_return.tpl');

include(dirname(__FILE__).'/../../footer.php');

?>
