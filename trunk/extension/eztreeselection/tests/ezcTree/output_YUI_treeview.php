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

echo $tree->getRootNode()->id; 

$viewOptions = new ezcTreeVisitorYUITreeViewOptions( array( 'displayRootNode' => true,
                                                            'yuiNodeClass' => 'CheckBoxNode',
                                                            'treeSelection' => ezcTreeVisitorYUITreeView::SELECTABLE_TREE,
                                                            'checkElementPrefix' => 'options',
                                                            'tree' => $tree ) );                
$visitor = new ezcTreeVisitorYUITreeView( 'tree', $viewOptions );
$tree->accept( $visitor );

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.5.2/build/treeview/assets/skins/sam/treeview.css">
<script src = "http://yui.yahooapis.com/2.5.2/build/yahoo/yahoo-min.js" ></script>
<script src = "http://yui.yahooapis.com/2.5.2/build/event/event-min.js" ></script>
<script src = "http://yui.yahooapis.com/2.5.2/build/treeview/treeview-min.js" ></script>
<script src = "http://localhost/rba_pc_actual/extension/eztreeselection/design/standard/javascript/eztreeselection.js" ></script>
<script type="text/javascript">
';

echo (string) $visitor; // print the javascript    

echo '</script>
</head>
<body class="yui-skin-sam">
<form action="' .  join( '/', array_slice( explode( '/', $_SERVER['REQUEST_URI'] ), 2  ) );


echo '" method="post">
	<div id="tree"></div>
	<input type="submit" value="submit"/>
</form>';

echo '<pre>'; 
print_r( $_POST );
echo '</pre>';

echo "\n</body></html>";
?>