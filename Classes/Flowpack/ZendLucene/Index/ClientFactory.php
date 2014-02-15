<?php
namespace Flowpack\ZendLucene\Index;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ZendLucene".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Flowpack\ZendLucene\Exception as Exception;

/**
 * Client factory
 * @Flow\Scope("singleton")
 */
class ClientFactory {

	/**
	 * @var array
	 */
	protected $indexCollection = array();

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param string $index
	 * @param boolean $allowIndexCreation
	 * @throws \Exception
	 * @throws \ZendSearch\Lucene\Exception\RuntimeException
	 * @throws \Flowpack\ZendLucene\Exception
	 * @return Client
	 */
	public function create($index = 'default', $allowIndexCreation = TRUE) {
		\ZendSearch\Lucene\Analysis\Analyzer\Analyzer::setDefault(new \ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive());
		if ($index[0] === '/') {
			throw new Exception('A valid index name starts with alpha numeric characters.', 1392221874);
		}

		if (isset($this->indexCollection[$index])) {
			return $this->indexCollection[$index];
		}

		$indexPath = $this->constructIndexStoragePath($index);
		try {
			$newIndex = new \ZendSearch\Lucene\Index($indexPath, FALSE);
		} catch (\ZendSearch\Lucene\Exception\RuntimeException $exception) {
			if ($allowIndexCreation) {
				// Index didn't exist yet, creating...
				$newIndex = new \ZendSearch\Lucene\Index($indexPath, TRUE);
			} else {
				throw $exception;
			}
		}
		$client = new Client($newIndex);
		$this->indexCollection[$index] = $client;

		return $client;
	}

	protected function constructIndexStoragePath($index) {
		return FLOW_PATH_DATA . 'Persistent/ZendLucene/' . $index;
	}
}

