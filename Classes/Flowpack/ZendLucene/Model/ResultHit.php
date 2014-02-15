<?php
namespace Flowpack\ZendLucene\Model;

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

/**
 * ZendLucene Result hit
 */
class ResultHit extends \ZendSearch\Lucene\Search\QueryHit {

	/**
	 * @var \Flowpack\ZendLucene\Index\Client
	 */
	protected $client;

	/**
	 * @param \Flowpack\ZendLucene\Index\Client $client
	 * @param integer $documentIdentifier
	 */
	public function __construct(\Flowpack\ZendLucene\Index\Client $client, $documentIdentifier) {
		parent::__construct($client->getZendIndex());
		$this->client = $client;
		$this->document_id = $documentIdentifier;
	}

	/**
	 * @param float $score
	 */
	public function setScore($score) {
		$this->score = $score;
	}

	/**
	 * @return float
	 */
	public function getScore() {
		return $this->score;
	}

	/**
	 * @param integer $document_id
	 */
	public function setDocumentIdentifier($document_id) {
		$this->document_id = $document_id;
	}

	/**
	 * @return integer
	 */
	public function getDocumentIdentifier() {
		return $this->document_id;
	}

}