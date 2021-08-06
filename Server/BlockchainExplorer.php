<?php // (c) 2021 Pttn
/*ini_set("display_errors", "1");
error_reporting(-1);*/

require_once("RiecoinRPC.php");
require_once("Config.php");

class BlockchainExplorer {
	private $riecoinRPC;
	
	public function __construct($daemonConf) {
		$this->riecoinRPC = new RiecoinRPC($daemonConf);
	}
	
	public function getBlockchainInfo() {
		$blockchainInfo = $this->riecoinRPC->getblockchaininfo()['result'] ?? null;
		if ($blockchainInfo == null)
			return array('Error' => 'Something went wrong while getting the blockchain info :|');
		$blockchainInfo['miningpower'] = $this->riecoinRPC->getnetworkminingpower()['result'] ?? null;
		if ($blockchainInfo['miningpower'] == null)
			return array('Error' => 'Something went wrong while getting the mining power :|');
		$blockchainInfo['txoutsetinfo'] = $this->riecoinRPC->gettxoutsetinfo()['result'] ?? null;
		if ($blockchainInfo['txoutsetinfo'] == null)
			return array('Error' => 'Something went wrong while getting the Tx outset info :|');
		$blockchainInfo['connections'] = $this->riecoinRPC->getconnectioncount()['result'] ?? null;
		if ($blockchainInfo['connections'] == null)
			return array('Error' => 'Something went wrong while getting the number of connections :|');
		return $blockchainInfo;
	}
	
	public function getBlockHash($height) {
		if (!ctype_digit($height))
			return array('Error' => 'Invalid Block Height');
		$getblockhashResponse = $this->riecoinRPC->getblockhash(intval($height));
		if ($getblockhashResponse == null)
			return array('Error' => 'Something went wrong while getting the block hash :|');
		else {
			$blockHash = $getblockhashResponse['result'] ?? null;
			if ($blockHash == null)
				return array('Error' => 'Block Height out of range');
			return $blockHash;
		}
	}
	
	public function getBlock($hash) {
		if (!ctype_xdigit($hash) || strlen($hash) != 64)
			return array('Error' => 'Invalid Block Hash, must be 64 hex digits');
		$getblockResponse = $this->riecoinRPC->getblock($hash);
		if ($getblockResponse == null)
			return array('Error' => 'Something went wrong while getting the block :|');
		else {
			$blockHash = $getblockResponse['result'] ?? null;
			if ($blockHash == null)
				return array('Error' => 'The hash does not refer to a block');
			return $blockHash;
		}
	}
	
	public function getBlockHeader($hash, $formatted) {
		if (!ctype_xdigit($hash) || strlen($hash) != 64)
			return array('Error' => 'Invalid Block Hash, must be 64 hex digits');
		$getblockheaderResponse = $this->riecoinRPC->getblockheader($hash, $formatted);
		if ($getblockheaderResponse == null)
			return array('Error' => 'Something went wrong while getting the blockheader :|');
		else {
			$blockheader = $getblockheaderResponse['result'] ?? null;
			if ($blockheader == null)
				return array('Error' => 'The hash does not refer to a block');
			return $blockheader;
		}
	}
	
	public function getTransaction($txid, $formatted) {
		if (!ctype_xdigit($txid) || strlen($txid) != 64)
			return array('Error' => 'Invalid TxId, must be 64 hex digits');
		$getrawtransactionResponse = $this->riecoinRPC->getrawtransaction($txid, $formatted);
		if ($getrawtransactionResponse == null)
			return array('Error' => 'Something went wrong while getting the transaction :|');
		else {
			$transaction = $getrawtransactionResponse['result'] ?? null;
			if ($transaction == null)
				return array('Error' => 'The TxId does not refer to a transaction');
			return $transaction;
		}
	}
}

$blockchainExplorer = new BlockchainExplorer($daemonConf);
$blockchainInfo = $blockchainExplorer->getBlockchainInfo();

if (isset($_POST['input']) && !isset($blockchainInfo['Error'])) {
	if (ctype_digit($_POST['input']))
		$hash = $blockchainExplorer->getBlockHash($_POST['input']);
	else
		$hash = $_POST['input'];
	$block = $blockchainExplorer->getBlock($hash);
	if (!isset($block['Error']))
		header('Location: block.php?height=' . $block['height']);
	$transaction = $blockchainExplorer->getTransaction($hash, true);
	if (!isset($transaction['Error']))
		header('Location: transaction.php?txid=' . $transaction['txid']);
	$invalidSearch = true;
}

function htmlTop($title) {
	global $blockchainInfo;
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<style>
			body, input, button {
				color: #C0C0C0;
				background: #000000;
			}
			a {
				color: #C0C0C0;
			}
			.errorMessage {
				background-color: rgba(255, 0, 0, 0.25);
				padding: 8px;
			}
			table {
				border-collapse: collapse;
				overflow-x: auto;
				white-space: nowrap;
			}
			th, td {
				border: 1px solid rgba(255, 255, 255, 0.5);
				padding: 4px;
			}
			th {
				background: rgba(255, 255, 255, 0.25);
			}
		</style>
		<link rel="shortcut icon" href="Riecoin.svg"/>
		<title>rieXplorer - <?php echo $title;?></title>
	</head>
	<body>
		<header><b style="font-size: 3em">rieXplorer</b></header>
		<hr>
		<nav>
			<a href="index.php">Main page</a><div style="float:right;"><form method="post"><label for="input">Search: </label><input name="input" id="input" style="width: 50%;"/> <button>Search</button></form></div>
		</nav>
		<hr>
<?php
	global $invalidSearch;
	if (isset($invalidSearch))
		echo '<p class="errorMessage">Invalid Block Height, Block Hash, or Transaction Id; note that Addresses cannot be searched nor browsed for now</p>';
	if (isset($blockchainInfo['Error']))
		echo '<p class="errorMessage">' . $blockchainInfo['Error'] . '</p>';
	else
		echo $blockchainInfo['chain'] . ' chain | <a href="block.php?height=' . $blockchainInfo['blocks'] . '">' . $blockchainInfo['blocks'] . '</a> blocks | Difficulty: ' . sprintf('%.3f', $blockchainInfo['difficulty']) . ' | Mining Power: ' . sprintf('%.3f', $blockchainInfo['miningpower']) . ' | Supply: ' . sprintf('%.3f', $blockchainInfo['txoutsetinfo']['total_amount']/1000000) . 'M RIC | Connections: ' . $blockchainInfo['connections'];
?>
		<hr>
		<h1><?php echo $title;?></h1>
		<main>
<?php
}

function htmlBottom() {?>
		</main>
		<hr>
		<footer>(c) 2021 - Pttn | <a href="https://riecoin.dev/en/rieXplorer">Project Page</a> | <a href="https://github.com/Pttn/rieXplorer">Source Code</a></footer>
	</body>
</html>
<?php
}?>
