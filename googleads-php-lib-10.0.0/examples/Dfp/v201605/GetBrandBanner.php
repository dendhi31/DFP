<?php
$path = dirname(__FILE__) . '/../../../src';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';
require_once 'Google/Api/Ads/Dfp/Util/v201605/StatementBuilder.php';
require_once dirname(__FILE__) . '/../../Common/ExampleUtils.php';
class GetBrandBanner{
    private $user;
    private $limit;
    private $orderName;
    private $statementBuilder;
    private $orderDetail;
    private $lineItem;
    private $adUnit;

    public function __construct() {
        // relative to the DfpUser.php file's directory.
        $this->user = new DfpUser();
        
        // Log SOAP XML request and response.
        $this->user->LogDefaults();
        
        
        /*
        // Get the LineItemService.
        $this->lineItemService = $user->GetService('LineItemService', 'v201605');
        // Get the LineItemCreativeAssociationService.
        $this->licaService = $user->GetService('LineItemCreativeAssociationService', 'v201605');
        // Get the CreativeService.
        $this->creativeService = $user->GetService('CreativeService', 'v201605');
        */
        $this->limit = StatementBuilder::SUGGESTED_PAGE_LIMIT;

        $this->orderDetail = array();

        $this->lineItem = array();

        $this->adUnit = array();

    }
    
    public function setOrder($order) {
        $this->orderName = $order;
    }

    public function getOrderDetail() {
        return $this->orderDetail;
    }

    public function getLineItem() {
        return $this->lineItem;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function getCurrentUser() {
        $userService = $this->user->GetService('UserService', 'v201605');
        $usr = $userService->getCurrentUser();
        return $usr;
    }

    public function getOrder(){
        $statementBuilder = new StatementBuilder();
        $orderService = $this->user->GetService('OrderService', 'v201605');
        $statementBuilder->Where('name = :name')
          ->OrderBy('id ASC')
          ->WithBindVariableValue('name', $this->orderName)
          ->Limit($this->limit);
        // Default for total result set size.
        $totalResultSetSize = 0;
        do {
            // Get orders by statement.
            $page = $orderService->getOrdersByStatement($statementBuilder->ToStatement());
            //$orderDetail = array();
            if (isset($page->results)) {
                $totalResultSetSize = $page->totalResultSetSize;
                $i = $page->startIndex;
                foreach ($page->results as $order) {
                    $this->orderDetail[] = $order; 
                    //printf("Order with ID %d, name '%s', and advertiser ID %d was found.\n", $order->id, $order->name, $order->advertiserId);
                    $i++;
                }
            }
            $statementBuilder->IncreaseOffsetBy($this->limit);
        } while ($statementBuilder->GetOffset() < $totalResultSetSize);
    }

    public function setLineDetail($orderId){
        $statementBuilder = new StatementBuilder();
        $lineItemService = $this->user->GetService('LineItemService', 'v201605');
        $statementBuilder->Where('orderId = :orderId')
            ->OrderBy('orderId ASC')
            ->Limit($this->limit)
            ->WithBindVariableValue('orderId', $orderId);
        $totalResultSetSize = 0;
        do {
            $page = $lineItemService->getLineItemsByStatement($statementBuilder->ToStatement());
            if (isset($page->results)) {
                $totalResultSetSize = $page->totalResultSetSize;
                $i = $page->startIndex;
                foreach ($page->results as $lineItem) {
                    $this->lineItem[] = $lineItem;
                }
            }
            $statementBuilder->IncreaseOffsetBy($this->limit);
        } while ($statementBuilder->GetOffset() < $totalResultSetSize);
             
    }

    public function getAdUnitDetail($adUnitId) {
        print "aaaa";exit;
    }

    public function getBanner() {
        $this->getOrder();
        if(count($this->orderDetail) > 0){
            foreach($this->orderDetail as $idx => $val) {
                $this->setLineDetail($val->id);
            }
        }
    }

}
$orderName = "brand_250x250";
$DFPId = "105176465";
$DFPAdUnit = "250x250";
$bannerObj = new GetBrandBanner();
//$bannerObj->setLimit(1);
$bannerObj->setOrder($orderName);
$bannerObj->getBanner();
//print "<PRE>";print_r($bannerObj->getOrderDetail());exit;
$orderDetail = $bannerObj->getLineItem();
$width = '0';
$height = '0';
$adUnitId = '';
$userId = '';
$currentUser = $bannerObj->getCurrentUser();
//print json_encode($currentUser);exit;

//$bannerObj->getAdUnitDetail();

if($currentUser) {
    $userId = $currentUser->id;
}
//print "<PRE>";print_r($orderDetail);exit;
if(count($orderDetail) > 0) {
    //print "<PRE>";
    foreach($orderDetail as $idx => $val) {
        //print_r($val);
        //print_r($placeHolder = $val->creativePlaceholders[0]->size);
        //print $placeHolder->size->width." x ".$placeHolder->size->height;
        $height = $val->creativePlaceholders[0]->size->height;
        $width = $val->creativePlaceholders[0]->size->width;
        $adUnitId = $val->targeting->inventoryTargeting->targetedAdUnits[0]->adUnitId;
        //$bannerObj->getAdUnitDetail($adUnitId);
    }
}
//print $adUnitId;exit;
//exit;


?>
<HTML>
<HEAD>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title>Widgets Magazine</title>
<style type="text/css" media="screen">
</style>
<script type='text/javascript'>
  (function() {
    var useSSL = 'https:' == document.location.protocol;
    var src = (useSSL ? 'https:' : 'http:') +
        '//www.googletagservices.com/tag/js/gpt.js';
    document.write('<scr' + 'ipt src="' + src + '"></scr' + 'ipt>');
  })();
</script>

<script type='text/javascript'>
  googletag.cmd.push(function() {
    googletag.defineSlot('/<?php print $DFPId."/".$DFPAdUnit;?>', [<?php print $width.",".$height;?>], 'div-gpt-ad-1466160370039-0').addService(googletag.pubads());
    googletag.pubads().enableSingleRequest();
    googletag.pubads().enableSyncRendering();
    googletag.enableServices();
  });
</script>
</HEAD>
<BODY>
<!-- /105176465/250x250 -->
<div id='div-gpt-ad-1466160370039-0' style='height:<?php print $height;?>px; width:<?php print $width;?>px;'>
<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('div-gpt-ad-1466160370039-0'); });
</script>
</div>
</BODY>
</HTML>