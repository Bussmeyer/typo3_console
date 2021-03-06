<?php
namespace Helhum\Typo3Console\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\SingletonInterface;
use Helhum\Typo3Console\Mvc\Controller\CommandController;
use Helhum\Typo3Console\Service;

/**
 * Command controller for Fluid documentation rendering
 *
 */
class DocumentationCommandController extends CommandController implements SingletonInterface {

	/**
	 * @var \Helhum\Typo3Console\Service\XsdGenerator
	 * @inject
	 */
	protected $xsdGenerator;

	/**
	 * Generate Fluid ViewHelper XSD Schema
	 *
	 * Generates Schema documentation (XSD) for your ViewHelpers, preparing the
	 * file to be placed online and used by any XSD-aware editor.
	 * After creating the XSD file, reference it in your IDE and import the namespace
	 * in your Fluid template by adding the xmlns:* attribute(s):
	 * <html xmlns="http://www.w3.org/1999/xhtml" xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers" ...>
	 *
	 * @param string $phpNamespace Namespace of the Fluid ViewHelpers without leading backslash (for example 'TYPO3\Fluid\ViewHelpers' or 'Tx_News_ViewHelpers'). NOTE: Quote and/or escape this argument as needed to avoid backslashes from being interpreted!
	 * @param string $xsdNamespace Unique target namespace used in the XSD schema (for example "http://yourdomain.org/ns/viewhelpers"). Defaults to "http://typo3.org/ns/<php namespace>".
	 * @param string $targetFile File path and name of the generated XSD schema. If not specified the schema will be output to standard output.
	 * @return void
	 */
	public function generateXsdCommand($phpNamespace, $xsdNamespace = NULL, $targetFile = NULL) {
		if ($xsdNamespace === NULL) {
			$phpNamespace = rtrim($phpNamespace, '_\\');
			if (strpos($phpNamespace, '\\') === FALSE) {
				$search = array('Tx_', '_');
				$replace = array('', '/');
			} else {
				$search = '\\';
				$replace = '/';
			}
			$xsdNamespace = sprintf('http://typo3.org/ns/%s', str_replace($search, $replace, $phpNamespace));
		}
		$xsdSchema = '';
		try {
			$xsdSchema = $this->xsdGenerator->generateXsd($phpNamespace, $xsdNamespace);
		} catch (Service\Exception $exception) {
			$this->outputLine('An error occurred while trying to generate the XSD schema:');
			$this->outputLine('%s', array($exception->getMessage()));
			$this->sendAndExit(1);
		}
		if ($targetFile === NULL) {
			echo $xsdSchema;
		} else {
			file_put_contents($targetFile, $xsdSchema);
		}
	}
}