<?php
namespace Flowpack\ZendLucene\Object;

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
 * Provides information about the index rules of Objects
 * @Flow\Scope("singleton")
 */
class IndexingConfiguration {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $indexAnnotations = array();

	/**
	 */
	public function initializeObject() {
		$this->indexAnnotations = $this->buildIndexClassesAndProperties($this->objectManager);
	}

	/**
	 * Returns the to-index classes and their annotation
	 *
	 * @return array
	 */
	public function getClassesAndAnnotations() {
		static $classesAndAnnotations;
		if ($classesAndAnnotations === NULL) {
			foreach (array_keys($this->indexAnnotations) AS $className) {
				$classesAndAnnotations[$className] = $this->indexAnnotations[$className]['annotation'];
			}
		}

		return $classesAndAnnotations;
	}

	/**
	 * @param string $className
	 * @return \Flowpack\ZendLucene\Annotations\Indexable The annotation for this class
	 */
	public function getClassAnnotation($className) {
		if (!isset($this->indexAnnotations[$className])) {
			return NULL;
		}

		return $this->indexAnnotations[$className]['annotation'];
	}

	/**
	 * @param string $className
	 * @return array
	 */
	public function getClassProperties($className) {
		if (!isset($this->indexAnnotations[$className])) {
			return NULL;
		}

		return $this->indexAnnotations[$className]['properties'];
	}

	/**
	 * Creates the source array of what classes and properties have to be annotated.
	 * The returned array consists of class names, with a sub-key having both 'annotation' and 'properties' set.
	 * The annotation contains the class's annotation, while properties contains each property that has to be indexed.
	 * Each property might either have TRUE as value, or also an annotation instance, if given.
	 *
	 * @param \TYPO3\Flow\Object\ObjectManager $objectManager
	 * @return array
	 * @throws \Flowpack\ZendLucene\Exception
	 * @Flow\CompileStatic
	 */
	public static function buildIndexClassesAndProperties(\TYPO3\Flow\Object\ObjectManager $objectManager) {
		$reflectionService = $objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
		$annotationClassName = 'Flowpack\ZendLucene\Annotations\Indexable';
		foreach ($reflectionService->getClassNamesByAnnotation($annotationClassName) AS $className) {
			if ($reflectionService->isClassAbstract($className)) {
				throw new \Flowpack\ZendLucene\Exception(sprintf('The class with name "%s" is annotated with %s, but is abstract. Indexable classes must not be abstract.', $className, $annotationClassName), 1339595182);
			}
			$indexAnnotations[$className]['annotation'] = $reflectionService->getClassAnnotation($className, $annotationClassName);

			// if no single properties are set to be indexed, consider all properties to be indexed.
			$annotatedProperties = $reflectionService->getPropertyNamesByAnnotation($className, $annotationClassName);
			if (!empty($annotatedProperties)) {
				$indexAnnotations[$className]['properties'] = $annotatedProperties;
			} else {
				foreach ($reflectionService->getClassPropertyNames($className) AS $propertyName) {
					$indexAnnotations[$className]['properties'][] = $propertyName;
				}
			}
		}

		return $indexAnnotations;
	}
}

