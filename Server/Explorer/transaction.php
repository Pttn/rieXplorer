<?php // (c) 2021 Pttn
require_once("../BlockchainExplorer.php");

if (isset($blockchainInfo['Error'])) {
	htmlTop('Error');
	echo '<p class="errorMessage">The Explorer is experiencing technical difficulties, please retry later</p>';
}
else if (!isset($_GET['txid'])) {
	htmlTop('Error');
	echo '<p class="errorMessage">No TxId provided</p>';
}
else {
	$transactionHash = $_GET['txid'];
	$transaction = $blockchainExplorer->getTransaction($transactionHash, true);
	if (isset($transaction['Error'])) {
		htmlTop('Error');
		echo '<p class="errorMessage">' . $transaction['Error'] . '</p>';
	}
	else {
		htmlTop('Transaction ' . $transaction['txid']);
		echo '<h2>General Information</h2>';
		
		echo '<table>';
		echo '<tr>';
		echo '<th style="text-align: left;">Transaction Id</th>';
		echo '<td>' . $transaction['txid'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Hash</th>';
		echo '<td>' . $transaction['hash'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Version</th>';
		echo '<td>' . $transaction['version'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Size</th>';
		echo '<td>' . ($transaction['size']/1000) . ' kB (virtual: ' . ($transaction['vsize']/1000) . ' kB)</td>';
		echo '</tr>';
		echo '<tr>';
		if (isset($transaction['blockhash'])) {
			echo '<th style="text-align: left;">Confirmed in Block</th>';
			echo '<td><a href="block.php?hash=' . $transaction['blockhash'] . '">' . $transaction['blockhash'] . '</a></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th style="text-align: left;">Confirmation(s)</th>';
			echo '<td>' . $transaction['confirmations'] . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th style="text-align: left;">Block Time</th>';
			echo '<td>' . gmdate("Y-m-d H:i:s", $transaction['blocktime']) . ', ' . $transaction['blocktime'] . '</td>';
			echo '</tr>';
		}
		else {
			echo '<tr>';
			echo '<th style="text-align: left;">Confirmation(s)</th>';
			echo '<td>0 - Unconfirmed!</td>';
			echo '</tr>';
		}
		
		$inputsValue = 0;
		$inputsContent = '<h2>Inputs</h2>';
		$inputsContent .= '<table>';
		$inputsContent .= '<tr><th>Original Transaction</th><th>Output Index</th><th>Address</th><th>Value</th></tr>';
		foreach ($transaction['vin'] as &$inputs) {
			$inputsContent .= '<tr style="text-align: right;">';
			if (isset($inputs['txid'])) {
				$originalTransactionId = $inputs['txid'];
				$inputsContent .= '<td><a href="transaction.php?txid=' . $originalTransactionId . '">' . $originalTransactionId . '</a></td>';
				$inputsContent .= '<td>' . $inputs['vout'] . '</td>';
				$originalTransaction = $blockchainExplorer->getTransaction($originalTransactionId, true);
				$inputsContent .= '<td>' . ($originalTransaction['vout'][$inputs['vout']]['scriptPubKey']['addresses'][0] ?? 'N/A') . '</td>';
				$inputValue = $originalTransaction['vout'][$inputs['vout']]['value'];
				$inputsContent .= '<td>' . sprintf('%.8f', $inputValue) . ' RIC</td>';
				$inputsValue += $inputValue;
			}
			else {
				$inputsContent .= '<td>(Coinbase Transaction)</td>';
				$inputsContent .= '<td>N/A</td>';
				$inputsContent .= '<td>N/A</td>';
				$inputsContent .= '<td>N/A</td>';
			}
			$inputsContent .= '</tr>';
		}
		$inputsContent .= '</table>';
		
		$outputsValue = 0;
		$outputsContent = '<h2>Outputs</h2>';
		$outputsContent .= '<table>';
		$outputsContent .= '<tr><th>Index</th><th>Address</th><th>Value</th></tr>';
		foreach ($transaction['vout'] as &$output) {
			$outputsContent .= '<tr style="text-align: right;">';
			$outputsContent .= '<td>' . $output['n'] . '</td>';
			$outputsContent .= '<td>' . ($output['scriptPubKey']['addresses'][0] ?? 'N/A') . '</td>';
			$outputValue = $output['value'];
			$outputsContent .= '<td>' . sprintf('%.8f', $outputValue) . ' RIC</td>';
			$outputsValue += $outputValue;
		}
		$outputsContent .= '</table>';
		
		echo '<tr>';
		echo '<th style="text-align: left;">Input Value</th>';
		echo '<td>' . sprintf('%.8f', $inputsValue) . ' RIC</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th style="text-align: left;">Output Value</th>';
		echo '<td>' . sprintf('%.8f', $outputsValue) . ' RIC</td>';
		echo '</tr>';
		if ($outputsValue <= $inputsValue) {
			echo '<tr>';
			echo '<th style="text-align: left;">Fee</th>';
			echo '<td>' . sprintf('%.8f', $inputsValue - $outputsValue) . ' RIC</td>';
			echo '</tr>';
		}
		echo '</table>';
		
		echo $inputsContent;
		echo $outputsContent;
	}
}
htmlBottom();
?>
