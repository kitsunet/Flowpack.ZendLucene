<?php
namespace Flowpack\ZendLucene\Mapping;

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
 * @Flow\Scope("singleton")
 */
class DataMapper {

	/**
	 * @var array
	 */
	protected $mappingConfigurations = array();

	/**
	 * @param string $configurationName
	 * @param array $configuration
	 */
	public function addMappingConfiguration($configurationName, array $configuration) {
		$this->mappingConfigurations[$configurationName] = $configuration;
	}

	/**
	 * @param string $configurationName
	 * @return boolean
	 */
	public function hasMappingConfiguration($configurationName) {
		return isset($this->mappingConfigurations[$configurationName]);
	}

	/**
	 * @param \ZendSearch\Lucene\Document $document
	 * @param array $data
	 * @param string $configurationName
	 * @return Document
	 */
	public function mapToDocument(\ZendSearch\Lucene\Document $document, array $data, $configurationName = NULL) {
		foreach ($data as $fieldName => $fieldValue) {
			if (is_array($fieldValue) || is_object($fieldValue)) {
				$fieldValue = json_encode($fieldValue);
			}
			$zendField = $this->createFieldAccordingToConfiguration($fieldName, $fieldValue, $configurationName);
			$document->addField($zendField);
		}

		return $document;
	}

	/**
	 * @param string $fieldName
	 * @param string $fieldValue
	 * @param string $configurationName
	 * @return \ZendSearch\Lucene\Document\Field
	 */
	protected function createFieldAccordingToConfiguration($fieldName, $fieldValue, $configurationName) {
		if (isset($this->mappingConfigurations[$configurationName][$fieldName]['index'])) {
			$fieldMapping = $this->mappingConfigurations[$configurationName][$fieldName]['index'];
		} elseif (isset($this->mappingConfigurations[$configurationName]['_DEFAULT']['index'])) {
			$fieldMapping = $this->mappingConfigurations[$configurationName]['_DEFAULT']['index'];
		} else {
			$fieldMapping = 'text';
		}

		return \ZendSearch\Lucene\Document\Field::$fieldMapping($fieldName, $fieldValue);
	}


}

