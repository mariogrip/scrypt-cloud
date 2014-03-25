#!/usr/bin/php
<?php

require_once('load.php');

if ( $bitcoin->can_connect() !== true ) {
  $log->logFatal("Failed to connect to RPC server\n");
  $monitoring->endCronjob($cron_name, 'E0006', 1, true);
}
$aAllBlocks = $block->getAllUnconfirmed(max($config['network_confirmations'],$config['confirmations']));
$header = false;
foreach ($aAllBlocks as $iIndex => $aBlock) {
  $strLogMask = "| %10.10s | %10.10s | %-64.64s | %5.5s | %5.5s | %-8.8s";
  $aBlockInfo = $bitcoin->getblock($aBlock['blockhash']);
  $aTxDetails = $bitcoin->gettransaction($aBlockInfo['tx'][0]);
  if ($aTxDetails['details'][0]['category'] == 'orphan') {
    if ($block->setConfirmations($aBlock['id'], -1)) {
      $status = 'ORPHAN';
    } else {
      $status = 'ERROR';
    }
    if (!$header) {
      $header = true;
    }
    continue;
  }
  if (isset($aBlockInfo['confirmations'])) {
    $iRPCConfirmations = $aBlockInfo['confirmations'];
  } else if (isset($aTxDetails['confirmations'])) {
    $iRPCConfirmations = $aTxDetails['confirmations'];
  } else {
  }
  if ($iRPCConfirmations == $aBlock['confirmations']) {
    continue;
  } else {
    if (!$block->setConfirmations($aBlock['id'], $iRPCConfirmations)) {
      $status = 'ERROR';
    } else {
      $status = 'UPDATED';
    }
    if (!$header) {
      $header = true;
    }
  }
}
?>
