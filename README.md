# rieXplorer

rieXplorer is a Riecoin Blockchain Explorer. The [Riecoin.dev Explorers](https://riecoin.dev/Explorer/) are based on rieXplorer.

## Requirements

* Synced and properly configured Riecoin Core, with TxIndex enabled;
* Recent enough PHP server with Curl extension.

## Configuration

Edit the `Config.php` file. The configuration should be straightforward.

## Install

Put the `Server/Explorer` files into a public folder of your server. Files outside `Explorer` should be inaccessible from clients. Do not forget to change the `require_once`s if needed.

## Developers and license

* Pttn, author and maintainer

You can discuss about rieXplorer on the [Riecoin.dev forum](https://forum.riecoin.dev/).

This work is released under the MIT license.
