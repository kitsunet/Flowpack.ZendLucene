Flowpack.ZendLucene
===================

Installation
------------

Currently this package is not available on packagist.org so you have to include the repository
in your root composer.json. Additionally also Zend Lucene is not available via packagist.org so
this has to be added to the root composer.json as well!

"repositories": [
	{
		"type": "vcs",
		"url": "https://github.com/kitsunet/Flowpack.ZendLucene.git"
	},
	{
		"type": "vcs",
		"url": "https://github.com/zendframework/ZendSearch"
	}
],

After that you should be able to use composer require to get this package as usual.

Usage
-----
This package currently is just a base to enable the ContentRepositoryAdaptor, you might
have a look at it for an example. Some classes are not yet used an indexer for Flow domain
models will follow.