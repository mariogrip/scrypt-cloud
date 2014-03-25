#!/usr/bin/php
<?php

require_once('load.php');


$aAllBlocks = $block->getAllUnaccounted('ASC');
if (empty($aAllBlocks)) {

}
$count = 0;
foreach ($aAllBlocks as $iIndex => $aBlock) {
  if (!$aBlock['share_id']) {
  }

  if ($config['pplns']['shares']['type'] == 'blockavg' && $block->getBlockCount() > 0) {
    $pplns_target = round($block->getAvgBlockShares($aBlock['height'], $config['pplns']['blockavg']['blockcount']));
  } else if ($config['pplns']['shares']['type'] == 'dynamic' && $block->getBlockCount() > 0) {
    $pplns_target = round($block->getAvgBlockShares($aBlock['height'], $config['pplns']['blockavg']['blockcount']) * (100 - $config['pplns']['dynamic']['percent'])/100 + $aBlock['shares'] * $config['pplns']['dynamic']['percent']/100);
  } else {
    $pplns_target = $config['pplns']['shares']['default'];
  }

  if ($iLastBlockId = $setting->getValue('last_accounted_block_id')) {
    $aLastAccountedBlock = $block->getBlockById($iLastBlockId);
  } else {
    $iLastBlockId = 0;
    $aLastAccountedBlock = array('height' => 0, 'confirmations' => 1);
  }
  if ((!$aBlock['accounted'] && $aBlock['height'] > $aLastAccountedBlock['height']) || (@$aLastAccountedBlock['confirmations'] == -1)) {
    $iPreviousShareId = @$aAllBlocks[$iIndex - 1]['share_id'] ? $aAllBlocks[$iIndex - 1]['share_id'] : 0;
    $iCurrentUpstreamId = $aBlock['share_id'];
    if (!is_numeric($iCurrentUpstreamId)) {
      $monitoring->endCronjob($cron_name, 'E0012', 1, true);
    }
    $iRoundShares = $share->getRoundShares($iPreviousShareId, $aBlock['share_id']);
    $iNewRoundShares = 0;
    $config['reward_type'] == 'block' ? $dReward = $aBlock['amount'] : $dReward = $config['reward'];
    $aRoundAccountShares = $share->getSharesForAccounts($iPreviousShareId, $aBlock['share_id']);

    $strLogMask = "| %20.20s | %20.20s | %8.8s | %10.10s | %15.15s |";


    if ($iRoundShares >= $pplns_target) {
      $iMinimumShareId = $share->getMinimumShareId($pplns_target, $aBlock['share_id']);
      // We need to go one ID lower due to `id >` or we won't match if minimum share ID == $aBlock['share_id']
      $aAccountShares = $share->getSharesForAccounts($iMinimumShareId - 1, $aBlock['share_id']);
      if (empty($aAccountShares)) {
        $monitoring->endCronjob($cron_name, 'E0013', 1, true);
      }
      foreach($aAccountShares as $key => $aData) {
        $iNewRoundShares += $aData['valid'];
      }
      $iRoundShares = $iNewRoundShares;
    } else {
      // We need to fill up with archived shares
      // Grab the full current round shares since we didn't match target
      $aAccountShares = $aRoundAccountShares;
      if (empty($aAccountShares)) {
        $monitoring->endCronjob($cron_name, 'E0013', 1, true);
      }

      // Grab only the most recent shares from Archive that fill the missing shares
      if (!$aArchiveShares = $share->getArchiveShares($pplns_target - $iRoundShares)) {
        $pplns_target = $iRoundShares;
      } else {
        // Add archived shares to users current shares, if we have any in archive
        if (is_array($aArchiveShares)) {
          $strLogMask = "| %-20.20s | %15.15s | %15.15s | %15.15s | %15.15s | %15.15s | %15.15s |";
          foreach($aAccountShares as $key => $aData) {
            if (array_key_exists($aData['username'], $aArchiveShares)) {
              $aAccountShares[$key]['valid'] += $aArchiveShares[$aData['username']]['valid'];
              $aAccountShares[$key]['invalid'] += $aArchiveShares[$aData['username']]['invalid'];
            }
          }
          // reverse payout
          if ($config['pplns']['reverse_payout']) {
            $aSharesData = NULL;
            foreach($aAccountShares as $key => $aData) {
              $aSharesData[$aData['username']] = $aData;
            }
            // Add users from archive not in current round
            $strLogMask = "| %-20.20s | %15.15s | %15.15s |";
            foreach($aArchiveShares as $key => $aArchData) {
              if (!array_key_exists($aArchData['account'], $aSharesData)) {
                $aArchData['username'] = $aArchData['account'];
                $aSharesData[$aArchData['account']] = $aArchData;
              }
            }
            $aAccountShares = $aSharesData;
          }
        }
        // We tried to fill up to PPLNS target, now we need to check the actual shares to properly payout users
        foreach($aAccountShares as $key => $aData) {
          $iNewRoundShares += $aData['valid'];
        }
      }
    }

    // We filled from archive but still are not able to match PPLNS target, re-adjust
    if ($iRoundShares < $iNewRoundShares) {
      $iRoundShares = $iNewRoundShares;
    }

    // Merge round shares and pplns shares arrays
    $aTotalAccountShares = NULL;
    foreach($aAccountShares as $key => $aData) {
      $aData['pplns_valid'] = $aData['valid'];
      $aData['pplns_invalid'] = $aData['invalid'];
      $aData['valid'] = 0;
      $aData['invalid'] = 0;
      $aTotalAccountShares[$aData['username']] = $aData;
    }
    foreach($aRoundAccountShares as $key => $aTempData) {
      if (array_key_exists($aTempData['username'], $aTotalAccountShares)) {
        $aTotalAccountShares[$aTempData['username']]['valid'] = $aTempData['valid'];
        $aTotalAccountShares[$aTempData['username']]['invalid'] = $aTempData['invalid'];
      } else {
        $aTempData['pplns_valid'] = 0;
        $aTempData['pplns_invalid'] = 0;
        $aTotalAccountShares[$aTempData['username']] = $aTempData;
      }
    }

    // Table header for account shares
    $strLogMask = "| %5.5s | %-15.15s | %15.15s | %15.15s | %12.12s | %15.15s | %15.15s | %15.15s | %15.15s |";

    // Loop through all accounts that have found shares for this round
    foreach ($aTotalAccountShares as $key => $aData) {
      // Skip entries that have no account ID, user deleted?
      if (empty($aData['id'])) {
        continue;
      }
      if ($aData['pplns_valid'] == 0) {
        continue;
      }


      $aData['percentage'] = round(( 100 / $iRoundShares) * $aData['pplns_valid'], 8);
      $aData['payout'] = round(( $aData['percentage'] / 100 ) * $dReward, 8);
      $aData['fee' ] = 0;
      $aData['donation'] = 0;
      $aData['pool_bonus'] = 0;

      // Calculate pool fees
      if ($config['fees'] > 0 && $aData['no_fees'] == 0)
        $aData['fee'] = round($config['fees'] / 100 * $aData['payout'], 8);

      // Calculate pool bonus if it applies, will be paid from liquid assets!
      if ($config['pool_bonus'] > 0) {
        if ($config['pool_bonus_type'] == 'block') {
          $aData['pool_bonus'] = round(( $config['pool_bonus'] / 100 ) * $dReward, 8);
        } else {
          $aData['pool_bonus'] = round(( $config['pool_bonus'] / 100 ) * $aData['payout'], 8);
        }
      }

      // Calculate donation amount, fees not included
      $aData['donation'] = round($user->getDonatePercent($user->getUserId($aData['username'])) / 100 * ( $aData['payout'] - $aData['fee']), 8);

      // Verbose output of this users calculations

      // Add new credit transaction
      if (!$transaction->addTransaction($aData['id'], $aData['payout'], 'Credit', $aBlock['id']))
       
      // Add new fee debit for this block
      if ($aData['fee'] > 0 && $config['fees'] > 0)
        if (!$transaction->addTransaction($aData['id'], $aData['fee'], 'Fee', $aBlock['id']))
          
      // Add new donation debit
      if ($aData['donation'] > 0)
        if (!$transaction->addTransaction($aData['id'], $aData['donation'], 'Donation', $aBlock['id']))
          
      // Add new bonus credit
      if ($aData['pool_bonus'] > 0)
        if (!$transaction->addTransaction($aData['id'], $aData['pool_bonus'], 'Bonus', $aBlock['id']))
    }

    // Add full round share statistics
    foreach ($aTotalAccountShares as $key => $aRoundData) {
      if (empty($aRoundData['id'])) {
        continue;
      }
      if (!$statistics->insertPPLNSStatistics($aRoundData, $aBlock['id']))
    }

    // Store this blocks height as last accounted for
    $setting->setValue('last_accounted_block_id', $aBlock['id']);

    if (!$share->moveArchive($iCurrentUpstreamId, $aBlock['id'], $iPreviousShareId))
     
    if (!$share->deleteAccountedShares($iCurrentUpstreamId, $iPreviousShareId)) {
     
    }
    if (!$block->setAccounted($aBlock['id'])) {
    }
  } else {
//Big error!
  }
}

require_once('cron_end.inc.php');
?>
