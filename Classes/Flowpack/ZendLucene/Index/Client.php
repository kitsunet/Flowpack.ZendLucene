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

use Flowpack\ZendLucene\Model\Document;
use Flowpack\ZendLucene\Model\ResultHit;
use Flowpack\ZendLucene\Exception;
use ZendSearch\Lucene\Search as ZendSearch;
use TYPO3\Flow\Annotations as Flow;

/**
 * A Client representation
 */
class Client {

	/**
	 * @var \ZendSearch\Lucene\Index
	 */
	protected $zendIndex;

	/**
	 * @Flow\Inject(setting="client.defaultResultSetLimit")
	 * @var integer
	 */
	protected $resultSetLimit;

	/**
	 * @param \ZendSearch\Lucene\Index $zendIndex
	 */
	public function __construct(\ZendSearch\Lucene\Index $zendIndex) {
		$this->zendIndex = $zendIndex;
	}

	/**
	 * @param int $resultSetLimit
	 */
	public function setResultSetLimit($resultSetLimit) {
		$this->resultSetLimit = $resultSetLimit;
	}

	/**
	 * @return int
	 */
	public function getResultSetLimit() {
		return $this->resultSetLimit;
	}

	/**
	 * @return \ZendSearch\Lucene\Index
	 */
	public function getZendIndex() {
		return $this->zendIndex;
	}

	/**
	 * @param \ZendSearch\Lucene\Document $document
	 */
	public function addDocument(\ZendSearch\Lucene\Document $document) {
		$this->zendIndex->addDocument($document);
	}

	/**
	 * @param Document $document
	 */
	public function removeDocument(Document $document) {
		$this->zendIndex->delete($document->getLuceneDocumentIdentifier());
	}

	/**
	 * @param Document $document
	 */
	public function replaceDocument(Document $document) {
		$this->zendIndex->delete($document->getLuceneDocumentIdentifier());
		$this->addDocument($document);
	}

	/**
	 * Overwritten to exchange the QueryHit with the ResultHit from this package to have more mapping options.
	 * Very ugly to do that but no other way.
	 *
	 *
	 * @param ZendSearch\QueryParser|string $query
	 * @return array
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	public function find($query) {
		if (is_string($query)) {
			$query = ZendSearch\QueryParser::parse($query);
		} elseif (!$query instanceof ZendSearch\Query\AbstractQuery) {
			throw new \InvalidArgumentException('Query must be a string or ZendSearch\Lucene\Search\Query object');
		}

		$this->zendIndex->commit();

		$hits = array();
		$scores = array();
		$ids = array();

		$query = $query->rewrite($this->zendIndex)->optimize($this->zendIndex);

		$query->execute($this->zendIndex);

		$topScore = 0;

		foreach ($query->matchedDocs() as $id => $num) {
			$docScore = $query->score($id, $this->zendIndex);
			if ($docScore != 0) {
				$hit = new ResultHit($this, $id);
				$hit->setScore($docScore);

				$hits[] = $hit;
				$ids[] = $id;
				$scores[] = $docScore;

				if ($docScore > $topScore) {
					$topScore = $docScore;
				}
			}

			if ($this->resultSetLimit != 0 && count($hits) >= $this->resultSetLimit) {
				break;
			}
		}

		if (count($hits) == 0) {
			// skip sorting, which may cause a error on empty index
			return array();
		}

		if ($topScore > 1) {
			foreach ($hits as $hit) {
				$hit->setScore($hit->getScore() / $topScore);
			}
		}

		if (func_num_args() == 1) {
			// sort by scores
			array_multisort($scores, SORT_DESC, SORT_NUMERIC,
				$ids, SORT_ASC, SORT_NUMERIC,
				$hits);
		} else {
			// sort by given field names

			$argList = func_get_args();
			$fieldNames = $this->zendIndex->getFieldNames();
			$sortArgs = array();

			// PHP 5.3 now expects all arguments to array_multisort be passed by
			// reference (if it's invoked through call_user_func_array());
			// since constants can't be passed by reference, create some placeholder variables.
			$sortReg = SORT_REGULAR;
			$sortAsc = SORT_ASC;
			$sortNum = SORT_NUMERIC;

			$sortFieldValues = array();

			for ($count = 1; $count < count($argList); $count++) {
				$fieldName = $argList[$count];

				if (!is_string($fieldName)) {
					throw new \RuntimeException('Field name must be a string.', 1392328169);
				}

				if (strtolower($fieldName) == 'score') {
					$sortArgs[] = & $scores;
				} else {
					if (!in_array($fieldName, $fieldNames)) {
						throw new \RuntimeException('Wrong field name.', 1392328178);
					}

					if (!isset($sortFieldValues[$fieldName])) {
						$valuesArray = array();
						/**
						 * @var $hit ResultHit
						 */
						foreach ($hits as $hit) {
							try {
								$value = $hit->getDocument()->getFieldValue($fieldName, TRUE);
							} catch (\Exception $e) {
								if (strpos($e->getMessage(), 'not found') === FALSE) {
									throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
								} else {
									$value = NULL;
								}
							}

							$valuesArray[] = $value;
						}

						// Collect loaded values in $sortFieldValues
						// Required for PHP 5.3 which translates references into values when source
						// variable is destroyed
						$sortFieldValues[$fieldName] = $valuesArray;
					}

					$sortArgs[] = & $sortFieldValues[$fieldName];
				}

				if ($count + 1 < count($argList) && is_integer($argList[$count + 1])) {
					$count++;
					$sortArgs[] = & $argList[$count];

					if ($count + 1 < count($argList) && is_integer($argList[$count + 1])) {
						$count++;
						$sortArgs[] = & $argList[$count];
					} else {
						if ($argList[$count] == SORT_ASC || $argList[$count] == SORT_DESC) {
							$sortArgs[] = & $sortReg;
						} else {
							$sortArgs[] = & $sortAsc;
						}
					}
				} else {
					$sortArgs[] = & $sortAsc;
					$sortArgs[] = & $sortReg;
				}
			}

			// Sort by id's if values are equal
			$sortArgs[] = & $ids;
			$sortArgs[] = & $sortAsc;
			$sortArgs[] = & $sortNum;

			// Array to be sorted
			$sortArgs[] = & $hits;

			// Do sort
			call_user_func_array('array_multisort', $sortArgs);
		}

		return $hits;
	}

	/**
	 * Returns a Zend_Search_Lucene_Document object for the document
	 * number $id in this index.
	 *
	 * @param integer|ResultHit $id
	 * @return Document
	 */
	public function getDocument($id) {
		if ($id instanceof ResultHit) {
			/* @var $id ResultHit */
			$id = $id->getDocumentIdentifier();
		}
		$luceneDocument = $this->zendIndex->getDocument($id);
		return $luceneDocument;
	}

}

