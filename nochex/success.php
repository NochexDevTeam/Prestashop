<?php
/** 2007-2019 PrestaShop** NOTICE OF LICENSE** This source file is subject to the Academic Free License (AFL 3.0)* that is bundled with this package in the file LICENSE.txt.* It is also available through the world-wide-web at this URL:* http://opensource.org/licenses/afl-3.0.php* If you did not receive a copy of the license and are unable to* obtain it through the world-wide-web, please send an email* to license@prestashop.com so we can send you a copy immediately.** DISCLAIMER** Do not edit or add to this file if you wish to upgrade PrestaShop to newer* versions in the future. If you wish to customize PrestaShop for your* needs please refer to http://www.prestashop.com for more information.
*  @author Nochex
*  @copyright  2007-2019 Nochex
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*  Plugin Name: Nochex Payment Gateway for Prestashop
*  Description: Accept Nochex Payments, orders are updated using APC.
*  Version: 2.1.1
*  License: GPL2
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/nochex.php');
$nochexDebug = new nochex();
$controller = new FrontController();
$controller->init();
$id_cart = Tools::getValue("id_cart");
$myid_order = Order::getOrderByCartId($id_cart);

$order = new Order((int) $myid_order);
$id_customer = $order->id_customer;
$customer = new Customer((int)$id_customer);
                
$smarty->assign('nochexorder', $myid_order);
$smarty->display(_PS_MODULE_DIR_.'nochex/views/templates/front/payment_return.tpl');
		
include(dirname(__FILE__).'/../../footer.php');
