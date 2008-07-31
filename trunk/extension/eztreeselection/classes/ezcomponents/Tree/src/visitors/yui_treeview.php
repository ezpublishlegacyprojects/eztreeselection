<?php
/**
 * File containing the ezcTreeVisitorYUITreeView class.
 *
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Tree
 */

class ezcTreeVisitorYUITreeView implements ezcTreeVisitor
{
    /**
     * All elements displayed should be selectable
     */
    const SELECTABLE_TREE        = 0;

    /**
     * Only the leafs should be selectable
     */
    const SELECTABLE_LEAFS_ONLY        = 1;    

    /**
     * No element in the tree is selectable
     */
    const NOT_SELECTABLE        = 2;
        
    /**
     * Holds all the edges of the graph.
     *
     * @var array(string=>array(string))
     */
    protected $edges = array();

    /**
     * Holds the root ID.
     *
     * @var string
     */
    protected $root = null;

    /**
     * Holds the XML ID.
     *
     * @var string
     */
    protected $xmlId;

    /**
     * Holds the XHTML class.
     *
     * @var string
     */
    protected $class;

    /**
     * Whether the XML ID has been set.
     *
     * @var bool
     */
    private $treeIdSet;

    /**
     * Holds the options for this class
     *
     * @var ezcTreeVisitorYUIOptions
     */
    public $options;

    /**
     * Constructs a new ezcTreeVisitorYUI visualizer.
     *
     * @param ezcTreeVisitorYUIOptions $options
     */
    public function __construct( $xmlId, ezcTreeVisitorYUITreeViewOptions $options = null )
    {
        if ( !is_string( $xmlId ) || strlen( $xmlId ) === 0 )
        {
            throw new ezcBaseValueException( 'xmlId', $xmlId, 'non-empty string' );
        }
        $this->xmlId = $xmlId;
        if ( $options === null )
        {
            $this->options = new ezcTreeVisitorYUIOptions;
        }
        else
        {
            $this->options = $options;
        }
    }

    /**
     * Formats a node's data.
     *
     * It is just a simple method, that provide an easy way to change the way
     * on how data is formatted when this class is extended. The data is passed
     * in the $data argument, and whether the node should be highlighted is
     * passed in the $highlight argument.
     *
     * @param mixed $data
     * @param bool  $highlight
     * @return string
     */
    protected function formatData( $data, $highlight )
    {
        if ( !empty( $this->options->dataFields ) and is_array( $data ) )
        {
            foreach ( $this->options->dataFields as $field )
            {
                if ( array_key_exists( $field, $data ) )
                {
                    $data[$field] = htmlspecialchars( $data[$field] );
                    if ( $highlight )
                        $data[$field] = '<strong><span style=\"color: orange;\">' . $data[$field] . '</span></strong>';                    
                }
            }            
        }
        else
        {
            $data = htmlspecialchars( $data );
            if ( $highlight )            
                $data = '<strong><span style=\"color: orange;\">' . $data . '</span></strong>';
        }
        
        return $data;
    }

    /**
     * Visits the node and sets the the member variables according to the node
     * type and contents.
     *
     * @param ezcTreeVisitable $visitable
     * @return bool
     */
    public function visit( ezcTreeVisitable $visitable )
    {
        if ( $visitable instanceof ezcTreeNode )
        {
            if ( $this->root === null )
            {
                $this->root = $visitable->id;
            }

            $parent = $visitable->fetchParent();
            if ( $parent )
            {
                $this->edges[$parent->id][] = array( $visitable->id, $visitable->data, $visitable->fetchPath() );
            }
        }

        return true;
    }

    /**
     * Loops over the children of the node with ID $id.
     *
     * This methods loops over all the node's children and adds the correct
     * layout for each node depending on the state that is collected in the
     * $level and $levelLast variables.
     *
     * @param string $id
     * @param int    $level
     * @param array(int=>bool) $levelLast
     *
     * @return string
     */
    protected function doChildren( $id, $level = 0, $levelLast = array() )
    {
        // attach to parent element here.
        $text = "";
        $YuiNodeClass = $this->options->yuiNodeClass;
        $children = @$this->edges[$id];
        $numChildren = count( $children );

        if ( $numChildren > 0 )
        {
            foreach ( $children as $child )
            {
                // $child[2] is an ezcTreeNodeList object. The 'nodes' property is an array.
                $path = $child[2]->nodes;
                if ( !$this->options->displayRootNode )
                {
                    array_shift( $path );
                }
                if ( $this->options->selectedNodeLink )
                {
                    $slice = array_slice( $path, -1 );
                    $path = $this->createPathString( array_pop( $slice ) );
                }
                else
                {
                    $path = $this->createPathString( $path );
                }
                $text .= str_repeat( '  ', $level + 4 );
                
                $data = $this->formatData( $child[1], in_array( $child[0], $this->options->highlightNodeIds ) );
                
                $jsVarName = $levelLast['variableNamePrefix'] . $this->encodePath( $path );
                if ( isset( $this->edges[$child[0]] ) )
                {
                    $YuiNodeOptions = $this->buildYuiNodeOptions( $child[0], $path, $data, false );                    
                    $text .= "var {$jsVarName} = new YAHOO.widget.{$YuiNodeClass}( {$YuiNodeOptions}, {$levelLast['parentJSVariableName']}, false); \n";
                    $text .= $this->doChildren( $child[0], $level++, array( 'parentJSVariableName' => $jsVarName,
                                                                            'variableNamePrefix'   => $levelLast['variableNamePrefix'] ) ) . "\n";
                }
                else
                {
                    // pass something else than $id here  ?
                    $YuiNodeOptions = $this->buildYuiNodeOptions( $child[0], $path, $data, true );                    
                    $text .= "var {$jsVarName} = new YAHOO.widget.{$YuiNodeClass}( {$YuiNodeOptions}, {$levelLast['parentJSVariableName']}, false); \n";
                }
            }
        }

        return $text;
    }

    /**
     * Returns the XHTML representation of a tree.
     *
     * @return string
     * @ignore
     */
    public function __toString()
    {
        $tree = '';
        $this->treeIdSet = false;

        $idPart = "\"{$this->xmlId}\"";
        $tree .= "var {$this->xmlId} = new YAHOO.widget.TreeView( $idPart );" . "\n";
        $levelLast = array( 'parentJSVariableName' => "{$this->xmlId}.getRoot()",
                            'variableNamePrefix'   => "{$this->xmlId}_" );
        if ( $this->options->displayRootNode and $this->options->tree !== null )
        {
            $rootNode = $this->options->tree->getRootNode();
            $YuiNodeOptions = $this->buildYuiNodeOptions( $rootNode->id, $this->createPathString( $rootNode->id ), $rootNode->data );                
            $jsVarName = $levelLast['variableNamePrefix'] . $this->root;            
            $tree .= "var {$jsVarName} = new YAHOO.widget.{$this->options->yuiNodeClass}( $YuiNodeOptions, {$levelLast['parentJSVariableName']}, false); \n";            
            $levelLast = array( 'parentJSVariableName' => $jsVarName,
                                'variableNamePrefix'   => $levelLast['variableNamePrefix'] );
        }

        $tree .= $this->doChildren( $this->root, 1 * (bool) $this->options->displayRootNode, $levelLast );
        $treeInitJSFunctionName = $this->xmlId . '_treeInit';
        // $tree .= "YAHOO.util.Event.addListener( window, \"load\", {$treeInitJSFunctionName} );
        $tree .= "YAHOO.util.Event.onDOMReady( {$treeInitJSFunctionName} );
function {$treeInitJSFunctionName}()
{
    {$this->xmlId}.draw();
    {$this->xmlId}.expandAll();
}\n";
        return $tree;
    }
    
    /**
     * Builds the options array passed to the constructor of the YUI Tree View class. 
     *
     * @param int $id The id of the current element
     * @param string $path The path of the current element
     * @param string $label The label for the Tree node
     * @param bool $isLeaf true if the current element is a leaf of the tree      
     *
     * @return string a valid Javascript array containing valid options
     */    
    protected function buildYuiNodeOptions( $id, $path, $data, $isLeaf = false )
    {
        if ( !empty( $this->options->dataFields ) and is_array( $data ) )
        {
            $options = "{ label: \"";
            foreach ( $this->options->dataFields as $field )
            {
                if ( array_key_exists( $field, $data ) )
                {
                    $options .= $data[$field] . " ";
                }
            }
            $options .= "\"";
        }
        else
        {
            $options = "{ label: \"{$data}\"";            
        }
        
        $processedPath = $this->encodePath( $path );        
        $options .= ", ezcTreeID: \"{$processedPath}\"";
        $options .= ", checkElementPrefix: \"{$this->options->checkElementPrefix}\""; 
        $options .= ", inputElementPrefix: \"{$this->options->inputElementPrefix}\"";        
        
        switch ( $this->options->treeSelection )
        {
            case self::SELECTABLE_TREE :
                $options .= ", isSelectable: true";
                break;
                
            case self::SELECTABLE_LEAFS_ONLY :
                $options .= ", isSelectable:" ;
                $options .= $isLeaf ? ' true': ' false';
                break;
                
            case self::NOT_SELECTABLE :
            default:
                $options .= ", isSelectable: false";
                break;
        }
                
        if ( in_array( $id, $this->options->selectedNodes ) )
        {
            $options .= ", isSelected: true";
        }
            
        if ( $this->options->isEditable )
        {
            $options .= ", isEditable: true";
        }

        if ( $this->options->isMultiSelect === true )
        {
            $options .= ", isMultiSelect: true";
        }
        else
        {
            $options .= ", isMultiSelect: false";
        }
        
        if ( !empty( $this->options->imagePaths ) )
        {
            $i = 0;
            $options .= ", imagePaths: { ";
            foreach ( $this->options->imagePaths as $imgName => $imgPath )
            {
                if ( $i != 0)
                    $options .= ", ";
                $options .= " $imgName: \"{$imgPath}\"";
                $i++;
            }
            $options .= " }";            
        }

        if ( !empty( $this->options->actionButtons ) )
        {
            $i = 0;
            $options .= ", actionButtons: { ";
            foreach ( $this->options->actionButtons as $actionName => $actionInputName )
            {
                if ( $i != 0)
                    $options .= ", ";
                $options .= " $actionName: \"{$actionInputName}\"";
                $i++;
            }
            $options .= " }";            
        }
        
        $options .= " }";
        return $options;
    }
    
    /**
     * Encodes a node path into javascript compliant variable names or input name part.  
     *
     * @param string $path The path of the current element      
     *
     * @return string a valid Javascript array containing valid options
     */    
    public static function encodePath( $path, $separator = '_' )
    {
        return join( $separator,   explode( '/', $path ) );
    }

    /**
     * Decodes javascript compliant variable names or input name part into a node path ( slash-separated )  
     *
     * @param string $string The flat string to decode      
     *
     * @return string a slash-separated path
     */    
    public static function decodePath( $string, $separator = '_' )
    {
        return join( '/',   explode( $separator, $string ) );
    }
        
    public function createPathString( $pathElements = null )
    {
        if ( is_string( $pathElements ) )
        {
            $path = htmlspecialchars( $this->options->basePath . '/' . $pathElements , ENT_QUOTES );            
        }
        else
        {
            $path = htmlspecialchars( $this->options->basePath . '/' . join( '/', $pathElements ), ENT_QUOTES );            
        }
        return $path;
    }    
}
?>
