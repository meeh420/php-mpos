<?php

// Make sure we are called from index.php
if (!defined('SECURITY')) die('Hacking attempt');

if (!$smarty->isCached('master.tpl', $smarty_cache_key)) {
  $debug->append('No cached version available, fetching from backend', 3);

  if (@$_REQUEST['search']) {
    $_REQUEST['height'] = $roundstats->searchForBlockHeight($_REQUEST['search']);
  }
  if (@$_REQUEST['next'] && !empty($_REQUEST['height'])) {
    $iHeight = @$roundstats->getNextBlock($_REQUEST['height']);
      if (!$iHeight) {
        $iBlock = $block->getLast();
        $iHeight = $iBlock['height']; 
      }
  } else if (@$_REQUEST['prev'] && !empty($_REQUEST['height'])) {
    $iHeight = $roundstats->getPreviousBlock($_REQUEST['height']);
  } else if (empty($_REQUEST['height'])) {
      $iBlock = $block->getLast();
      $iHeight = $iBlock['height'];  
  } else {
      $iHeight = $_REQUEST['height'];
  }
  $_REQUEST['height'] = $iHeight;

  $iPPLNSShares = 0;
  $aDetailsForBlockHeight = $roundstats->getDetailsForBlockHeight($iHeight);
  $aRoundShareStats = $roundstats->getRoundStatsForAccounts($iHeight);
  $aUserRoundTransactions = $roundstats->getAllRoundTransactions($iHeight);

  if ($config['payout_system'] == 'pplns') {
    $aPPLNSRoundShares = $roundstats->getPPLNSRoundStatsForAccounts($iHeight);
    foreach($aPPLNSRoundShares as $aData) {
      $iPPLNSShares += $aData['pplns_valid'];
    }
    $block_avg = $block->getAvgBlockShares($iHeight, $config['pplns']['blockavg']['blockcount']);
    $smarty->assign('PPLNSROUNDSHARES', $aPPLNSRoundShares);
    $smarty->assign("PPLNSSHARES", $iPPLNSShares);
    $smarty->assign("BLOCKAVGCOUNT", $config['pplns']['blockavg']['blockcount']);
    $smarty->assign("BLOCKAVERAGE", $block_avg );
  }

  // Propagate content our template
  $smarty->assign('BLOCKDETAILS', $aDetailsForBlockHeight);
  $smarty->assign('ROUNDSHARES', $aRoundShareStats);
  $smarty->assign("ROUNDTRANSACTIONS", $aUserRoundTransactions);
} else {
  $debug->append('Using cached page', 3);
}

if ($setting->getValue('acl_round_statistics')) {
  $smarty->assign("CONTENT", "default.tpl");
} else if ($user->isAuthenticated(false)) {
  $smarty->assign("CONTENT", "default.tpl");
} else {
  $smarty->assign("CONTENT", "empty");
}
?>
