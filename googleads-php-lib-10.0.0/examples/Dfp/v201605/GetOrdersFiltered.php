<?php
/**
 * This example gets all orders. To create orders, run CreateOrders.php.
 *
 * PHP version 5
 *
 * Copyright 2014, Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    GoogleApiAdsDfp
 * @subpackage v201605
 * @category   WebServices
 * @copyright  2014, Google Inc. All Rights Reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *             Version 2.0
 */
error_reporting(E_STRICT | E_ALL);

// You can set the include path to src directory or reference
// DfpUser.php directly via require_once.
// $path = '/path/to/dfp_api_php_lib/src';
$path = dirname(__FILE__) . '/../../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';
require_once 'Google/Api/Ads/Dfp/Util/v201605/StatementBuilder.php';
require_once dirname(__FILE__) . '/../../Common/ExampleUtils.php';

try {
  // Get DfpUser from credentials in "../auth.ini"
  // relative to the DfpUser.php file's directory.
  $user = new DfpUser();

  // Log SOAP XML request and response.
  $user->LogDefaults();

  // Get the OrderService.
  $orderService = $user->GetService('OrderService', 'v201605');
  // Get the LineItemService.
  $lineItemService = $user->GetService('LineItemService', 'v201605');
  // Get the LineItemCreativeAssociationService.
  $licaService = $user->GetService('LineItemCreativeAssociationService', 'v201605');
  // Get the CreativeService.
  $creativeService = $user->GetService('CreativeService', 'v201605');

  // Create a statement to select all orders.
  $statementBuilder = new StatementBuilder();
  $statementBuilder->Where('name = :name')
      ->OrderBy('id ASC')
      ->WithBindVariableValue('name', 'spot3')
      ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);

  // Default for total result set size.
  $totalResultSetSize = 0;

  do {
    // Get orders by statement.
    $page = $orderService->getOrdersByStatement(
        $statementBuilder->ToStatement());

    // Display order results.
    if (isset($page->results)) {
      $totalResultSetSize = $page->totalResultSetSize;
      $i = $page->startIndex;
      foreach ($page->results as $order) {
        //print_r($order);exit;
        $rollNumber = $i;
        printf("%d) Order with ID %d, name '%s', and advertiser ID %d was found.<br>", $i++, $order->id, $order->name, $order->advertiserId);
        
        $totalResultSetSize2 = 0;
        $statementBuilder2 = new StatementBuilder();
        $statementBuilder2->Where('orderId = :orderId')
            ->OrderBy('orderId ASC')
            ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT)
            ->WithBindVariableValue('orderId', $order->id);
        do {
          $page2 = $lineItemService->getLineItemsByStatement($statementBuilder2->ToStatement());
          if (isset($page2->results)) {

            $totalResultSetSize2 = $page2->totalResultSetSize;
            $j = $page2->startIndex;
            foreach ($page2->results as $lineItem) {
                //print_r($lineItem);exit;
                $rollNumber2 = $j;
                printf("&nbsp;%d.%d) Line item with ID %d, belonging to order %d, and name '%s' "
                  . "was found.<br>", $rollNumber,$j++, $lineItem->id, $lineItem->orderId,
                  $lineItem->name);
                
                $statementBuilder3 = new StatementBuilder();
                $statementBuilder3->Where('lineItemId = :lineItemId')
                        ->OrderBy('lineItemId ASC, creativeId ASC')
                        ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT)
                        ->WithBindVariableValue('lineItemId', $lineItem->id);
                $totalResultSetSize3 = 0;
                do {
                    // Get LICAs by statement.
                    $page3 = $licaService->getLineItemCreativeAssociationsByStatement(
                        $statementBuilder3->ToStatement());

                    // Display results.
                    if (isset($page3->results)) {
                        $totalResultSetSize3 = $page3->totalResultSetSize;
                        $k = $page3->startIndex;
                        foreach ($page3->results as $lica) {
                            //print json_encode($lica)."<br>";
                            $rollNumber3 = $k;
                            if (isset($lica->creativeSetId)) {
                              printf("&nbsp;&nbsp;%d.%d.%d) LICA with line item ID %d, and creative set ID %d was "
                                  . "found.<br>", $rollNumber,$rollNumber2,$k++, $lica->lineItemId, $lica->creativeSetId);
                            } else {
                              printf("&nbsp;&nbsp;%d.%d.%d) LICA with line item ID %d, and creative ID %d was "
                                  . "found.<br>", $rollNumber,$rollNumber2,$k++, $lica->lineItemId, $lica->creativeId);
                            }
                            // Create a statement to select only image creatives.
                            $statementBuilder4 = new StatementBuilder();
                            $statementBuilder4->Where('creativeType = :creativeType AND id = :id')
                                ->OrderBy('id ASC')
                                ->Limit(StatementBuilder::SUGGESTED_PAGE_LIMIT)
                                ->WithBindVariableValue('creativeType', 'ImageCreative')
                                ->WithBindVariableValue('id', $lica->creativeId);
                            // Default for total result set size.
                            $totalResultSetSize4 = 0;
                            do {
                            // Get creatives by statement.
                            $page4 = $creativeService->getCreativesByStatement(
                                $statementBuilder4->ToStatement());

                            // Display results.
                            if (isset($page4->results)) {
                              $totalResultSetSize4 = $page4->totalResultSetSize;
                              $k = $page4->startIndex;
                              foreach ($page4->results as $creative) {
                                //print_r($creative);//exit;
                                
                                printf("&nbsp;&nbsp;&nbsp;%d.%d.%d) Creative with ID %d, and name '%s' was found.<br>", $rollNumber,$rollNumber2,$k++,$creative->id, $creative->name);
                              }
                            }

                            $statementBuilder4->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
                            } while ($statementBuilder4->GetOffset() < $totalResultSetSize4);
                            
                      }
                    }
                    $statementBuilder3->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
                } while ($statementBuilder3->GetOffset() < $totalResultSetSize);
            }

          }
          $statementBuilder2->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
        } while ($statementBuilder2->GetOffset() < $totalResultSetSize2);
        
      }
    }

    $statementBuilder->IncreaseOffsetBy(StatementBuilder::SUGGESTED_PAGE_LIMIT);
  } while ($statementBuilder->GetOffset() < $totalResultSetSize);

  //printf("Number of results found: %d<br>", $totalResultSetSize);
  print "<br><br>";
} catch (OAuth2Exception $e) {
  ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
  ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
  printf("%s<br>", $e->getMessage());
}

