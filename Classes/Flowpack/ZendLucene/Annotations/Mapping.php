<?php
namespace Flowpack\ZendLucene\Annotations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;

/**
 * @Annotation
 * @DoctrineAnnotation\Target("PROPERTY")
 */
final class Mapping {

	/**
	 * The name of the field that will be stored in the index.
	 * Defaults to the property/field name.
	 *
	 * @var string
	 */
	public $index_name;

	/**
	 *
	 * @var boolean
	 */
	public $isStored;

	/**
	 *
	 * @var boolean
	 */
	public $isIndexed;

	/**
	 *
	 * @var boolean
	 */
	public $storeTermVector;

	/**
	 * The boost value. Defaults to `1.0`.
	 *
	 * @var float
	 */
	public $boost;

	/**
	 *
	 * @var boolean
	 */
	public $isTokenized;

	/**
	 * The date format.
	 * Defaults to `dateOptionalTime`.
	 *
	 * @var string
	 */
	public $format;

	/**
	 * Returns this class's properties as type/value array in order to directly use it for mapping information
	 */
	public function getPropertiesArray() {
		return get_object_vars($this);
	}
}

