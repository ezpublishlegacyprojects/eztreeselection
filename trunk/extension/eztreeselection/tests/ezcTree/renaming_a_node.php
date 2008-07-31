<?php
// require 'autoload.php';

$store = new ezcTreeXmlInternalDataStore();
$tree = ezcTreeMemoryXml::create( '', $store );
// $tree = ezcTreeXml::create( '/tmp/test.xml', $store );
$tree->autoId = true;

$rootNode = $tree->createNode( null, 'Elements' );
$tree->setRootNode( $rootNode );

$nonMetal = $tree->createNode( 'NonMetals', 'Non-Metals' );
$rootNode->addChild( $nonMetal );
$nobleGasses = $tree->createNode( null, 'Noble Gasses' );
$rootNode->addChild( $nobleGasses );

$nonMetal->addChild( $tree->createNode( null, 'Hydrogen' ) );
$nonMetal->addChild( $tree->createNode( null, 'Carbon' ) );
$tree->saveFile();
echo '<pre>';
echo $tree->getXmlString();
echo '</pre>';

echo '<hr />'; 

$NonMetals = $tree->fetchNodeById( 'NonMetals' );
$NonMetals->dataFetched = true;
$NonMetals->data = "Non-Metals renamed";
$tree->saveFile();

$NonMetals = $tree->fetchNodeById( 'NonMetals' );

$newXml = $tree->getXmlString();
echo '<pre>';
echo $newXml;
echo '</pre>';

$tree = new ezcTreeMemoryXml( $newXml, $store );


?>