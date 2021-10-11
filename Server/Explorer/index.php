<?php // (c) 2021 Pttn
require_once("../BlockchainExplorer.php");

if (!isset($blockchainInfo['chain'])) {
	htmlTop('Error');
	echo '<p class="errorMessage">The Explorer is experiencing technical difficulties, please retry later</p>';
}
else {
	htmlTop('Main Page');
	echo "<h2>Latest Blocks</h2>";
	
	echo '<table>';
	echo '<tr>';
	echo '<th>Height</th>';
	echo '<th>Timestamp (UTC)</th>';
	echo '<th>Size</th>';
	echo '<th>Difficulty</th>';
	echo '<th>Tx</th>';
	echo '<th>Output Value</th>';
	echo '<th>Assumed Finder</th>';
	echo '</tr>';
	for ($i = 0 ; $i < 20 ; $i++) {
		$blockheight = $blockchainInfo['blocks'] - $i;
		$blockhash = $blockchainExplorer->getBlockhash($blockheight);
		$block = $blockchainExplorer->getBlock($blockhash);
		echo '<tr style="text-align: right;">';
		echo '<td><a href="block.php?height=' . $blockheight . '">' . $blockheight . '</a></td>';
		echo '<td>' . gmdate("Y-m-d H:i:s", $block['time']) . '</td>';
		echo '<td>' . ($block['size']/1000) . ' kB</td>';
		echo '<td>' . sprintf('%.3f', $block['difficulty']) . '</td>';
		echo '<td>' . count($block['tx']) . '</td>';
		$outputValue = 0;
		foreach ($block['tx'] as &$transactionHash) {
			$transaction = $blockchainExplorer->getTransaction($transactionHash, true);
			foreach ($transaction['vout'] as &$outputs)
				$outputValue += $outputs['value'];
		}
		echo '<td>' . sprintf('%.8f', $outputValue) . ' RIC</td>';
		$finder = 'Unknown';
		$coinbase = $blockchainExplorer->getTransaction($block['tx'][0], true);
		foreach ($coinbase['vout'] as &$output) {
			if (isset($output['scriptPubKey']['address'])) {
				if ($output['value'] > 0) {
					$finder = $output['scriptPubKey']['address'];
					break;
				}
			}
		}
		echo '<td>' . $finder . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
htmlBottom();
?>
