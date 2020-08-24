<?php
/**
 * 2007-2021 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CrontaskExecuteModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->ajax = true;

        if ((int)Configuration::get('CRONTASK_DELETEORDERS_HOURS') > 0) {
            $deleteInHours = Configuration::get('CRONTASK_DELETEORDERS_HOURS');
            $now = new DateTime();
            $now->sub(new DateInterval("PT{$deleteInHours}H"));
            $dateToDelete = $now->format('Y-m-d H:i:s');

            $queryToOrdersDelete = "SELECT id_order, reference, module, total_paid_tax_incl, current_state, date_add  FROM "._DB_PREFIX_."orders WHERE current_state IN (1, 10, 12, 13, 14, 15, 16, 19) AND date_add < '".$dateToDelete."'";
            $rowsToDelete = Db::getInstance()->ExecuteS($queryToOrdersDelete);

            foreach ($rowsToDelete as $row) {
                $id_order = (int)$row['id_order'];
                $history = new OrderHistory();
                $history->id_order = $id_order;
                $history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), $id_order);
            }

            $this->response(200, 'Pedidos actualizados.', $rowsToDelete);
        }
    }

    private function response($code, $message, $payload = null) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['message' => $message, 'payload' => $payload]);
        die;
    }
}
