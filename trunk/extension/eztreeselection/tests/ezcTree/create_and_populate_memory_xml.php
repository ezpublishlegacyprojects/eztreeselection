<?php

require 'autoload.php';

$store = new ezcTreeXmlInternalDataStore();
$tree = ezcTreeMemoryXml::create( '', $store );

$rootNode = $tree->createNode( 'Elements', 'Elements' );
$tree->setRootNode( $rootNode );

$nonMetal = $tree->createNode( 'NonMetals', 'Non-Metals' );
$rootNode->addChild( $nonMetal );
$nobleGasses = $tree->createNode( 'NobleGasses', 'Noble Gasses' );
$rootNode->addChild( $nobleGasses );

$nonMetal->addChild( $tree->createNode( 'H', 'Hydrogen' ) );
$nonMetal->addChild( $tree->createNode( 'C', 'Carbon' ) );
$nonMetal->addChild( $tree->createNode( 'N', 'Nitrogen' ) );
$nonMetal->addChild( $tree->createNode( 'O', 'Oxygen' ) );
$nonMetal->addChild( $tree->createNode( 'P', 'Phosphorus' ) );
$nonMetal->addChild( $tree->createNode( 'S', 'Sulfur' ) );
$nonMetal->addChild( $tree->createNode( 'Se', 'Selenium' ) );

$miscGases = $tree->createNode( 'Hallogens', 'Hallogen Gases' );
$nonMetal->addChild( $miscGases );
$miscGases->addChild( $tree->createNode( 'Fr', 'Freon' ) );
$miscGases->addChild( $tree->createNode( 'Ne', 'Neon' ) );


$nobleGasses->addChild( $tree->createNode( 'F', 'Fluorine' ) );
$nobleGasses->addChild( $tree->createNode( 'Cl', 'Chlorine' ) );
$nobleGasses->addChild( $tree->createNode( 'Br', 'Bromine' ) );
$nobleGasses->addChild( $tree->createNode( 'I', 'Iodine' ) ); 

// var_dump( $tree );
$xmlString = $tree->getXmlString();
echo  $xmlString . "\n";
echo "------------------------\n";

$store = new ezcTreeXmlInternalDataStore();
$tree = new ezcTreeMemoryXml( $xmlString, $store );

$f = $tree->fetchNodeById( 'F' );
echo $f->data, "<br/>\n"; // echos Fluorine 

?>