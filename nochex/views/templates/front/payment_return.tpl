{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Nochex
*  @copyright  2007-2019 Nochex
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<h2>Order history</h2>

<p>Your Order Number <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}order-detail.php?id_order={$nochexorder|escape:'htmlall':'UTF-8'}">{$nochexorder|escape:'htmlall':'UTF-8'}</a> has been processed and payment has been accepted via Nochex.</p>

<p>Your order will be dispatched within 24 hours.</p>

<p>Thank you for choosing {$shop_name|escape:'htmlall':'UTF-8'}</p>
<p>{l s='For any questions or for further information, please contact our' mod='nochex'} <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}contact-form.php">{l s='customer support' mod='nochex'}</a></p>
