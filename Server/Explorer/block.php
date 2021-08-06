<?php // (c) 2021 Pttn
require_once("../BlockchainExplorer.php");

if (isset($blockchainInfo['Error'])) {
	htmlTop('Error');
	echo '<p class="errorMessage">The Explorer is experiencing technical difficulties, please retry later</p>';
}
else if (isset($_GET['height'], $_GET['hash'])) {
	htmlTop('Error');
	echo '<p class="errorMessage">Please provide either a height or a hash, not both</p>';
}
else if (!isset($_GET['height']) && !isset($_GET['hash'])) {
	htmlTop('Error');
	echo '<p class="errorMessage">No block height nor hash provided</p>';
}
else {
	if (isset($_GET['height'])) {
		$blockHash = $blockchainExplorer->getBlockHash($_GET['height']);
		if (isset($blockHash['Error'])) {
			htmlTop('Error');
			echo '<p class="errorMessage">' . $blockHash['Error'] . '</p>';
			htmlBottom();
			return;
		}
	}
	else if (isset($_GET['hash']))
		$blockHash = $_GET['hash'];
	$block = $blockchainExplorer->getBlock($blockHash);
	if (isset($block['Error'])) {
		htmlTop('Error');
		echo '<p class="errorMessage">' . $block['Error'] . '</p>';
	}
	else {
		$blockHeight = $block['height'];
		htmlTop('Block ' . $blockHeight);
		echo '<h2>General Information</h2>';
		
		echo '<table style="white-space:normal;">';
		echo '<tr>';
		echo '<th style="text-align: left;">Height</th>';
		echo '<td>' . $block['height'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Hash</th>';
		echo '<td>' . $block['hash'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Confirmation(s)</th>';
		echo '<td>' . $block['confirmations'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Size</th>';
		echo '<td>' . ($block['size']/1000) . ' kB (stripped: ' . ($block['strippedsize']/1000) . ' kB)</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Timestamp (UTC)</th>';
		echo '<td>' . gmdate("Y-m-d H:i:s", $block['time']) . ', ' . $block['time'];
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Version</th>';
		echo '<td>' . $block['version'] . '<sub>10</sub> = ' . $block['versionHex'] . '<sub>16</sub> = <br>';
		echo str_pad(base_convert($block['versionHex'], 16, 2), 32, '0', STR_PAD_LEFT) . '<sub>2</sub></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Difficulty</th>';
		echo '<td>' . $block['difficulty'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Transaction(s)</th>';
		echo '<td>' . $block['nTx'] . ', including Coinbase transaction</td>';
		echo '</tr>';
		
		$totalOutputsValue = 0;
		if ($blockHeight == 0)
			$transactionContent = '<p>The genesis block coinbase is not considered an ordinary transaction and cannot be retrieved.</p>';
		else {
			$transactionContent = '<h2>Transactions</h2>';
			$transactionContent .= '<table style="white-space:normal;">';
			$transactionContent .= '<tr><th>Inputs</th><th>Outputs</th></tr>';
			$txIndex = 0;
			foreach ($block['tx'] as &$transactionId) {
				$transaction = $blockchainExplorer->getTransaction($transactionId, true);
				$transactionContent .= '<tr><th colspan="2">' . $txIndex . '. <a href="transaction.php?txid=' . $transaction['txid'] . '">' . $transaction['txid'] . '</a></th></tr>';
				$transactionContent .= '<tr>';
				$outputsValue = 0;
				$transactionContent .= '<td>';
				if ($txIndex == 0)
					$transactionContent .= '(Coinbase Transaction)';
				else {
					$i = 0;
					foreach ($transaction['vin'] as &$inputs) {
						if ($i >= 10) {
							$transactionContent .= '... <i>click on the TxId to show all</i>';
							break;
						}
						if (isset($inputs['txid'])) {
							$inputTransaction = $blockchainExplorer->getTransaction($inputs['txid'], true);
							$transactionContent .= $inputTransaction['vout'][$inputs['vout']]['scriptPubKey']['addresses'][0] ?? 'N/A';
							$inputValue = $inputTransaction['vout'][$inputs['vout']]['value'];
							$transactionContent .= '<span style="float:right; padding-left: 16px;">' . sprintf('%.8f', $inputValue) . ' RIC</span><br>';
						}
						$i++;
					}
				}
				$transactionContent .= '</td>';
				$transactionContent .= '<td>';
				$i = 0;
				foreach ($transaction['vout'] as &$outputs) {
					$outputValue = $outputs['value'];
					$outputsValue += $outputValue;
					if ($i < 10) {
						$transactionContent .= $outputs['scriptPubKey']['addresses'][0] ?? 'N/A';
						$transactionContent .= '<span style="float:right; padding-left: 16px;">' . sprintf('%.8f', $outputValue) . ' RIC</span><br>';
					}
					else if ($i == 10)
						$transactionContent .= '... <i>click on the TxId to show all</i>';
					$i++;
				}
				$transactionContent .= '</td>';
				$transactionContent .= '</tr>';
				$totalOutputsValue += $outputsValue;
				$txIndex++;
			}
			$transactionContent .= '</table>';
		}
		echo '<tr>';
		echo '<th style="text-align: left;">Output Value</th>';
		echo '<td>' . sprintf('%.8f', $totalOutputsValue) . ' RIC</td>';
		echo '</tr>';
		if (isset($block['previousblockhash'])) {
			echo '<tr>';
			echo '<th style="text-align: left;">Previous Block</th>';
			echo '<td><a href="?height=' . ($blockHeight - 1) . '">' . $block['previousblockhash'] . '</a></td>';
			echo '</tr>';
		}
		if (isset($block['nextblockhash'])) {
			echo '<tr>';
			echo '<th style="text-align: left;">Next Block</th>';
			echo '<td><a href="?height=' . ($blockHeight + 1) . '">' . $block['nextblockhash'] . '</a></td>';
			echo '</tr>';
		}
		echo '<tr>';
		echo '<th style="text-align: left;">Raw Block Header</th>';
		$blockheader = $blockchainExplorer->getBlockheader($blockHash, false);
		echo '<td style="overflow-wrap: anywhere;">' . $blockheader . '</td>';
		echo '</tr>';
		echo '</table>';
		echo $transactionContent;
	}
}
htmlBottom();
?>
