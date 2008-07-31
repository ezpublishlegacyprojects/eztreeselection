<?php

// require 'autoload.php';

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


$visitor = new ezcTreeVisitorYUI( 'menu' );
$tree->accept( $visitor );

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.3.1/build/menu/assets/skins/sam/menu.css"/>

<script type="text/javascript" src="http://yui.yahooapis.com/2.3.1/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.3.1/build/container/container_core-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.3.1/build/menu/menu-min.js"></script>
<script type="text/javascript">
YAHOO.util.Event.onContentReady( \'menu\', function () {
var oMenu = new YAHOO.widget.MenuBar("menu", { autosubmenudisplay: true, showdelay: 200 });

oMenu.render();
});
</script>
</head>
<body class="yui-skin-sam">';

echo (string) $visitor; // print the plot

echo '</body></html>';
?>