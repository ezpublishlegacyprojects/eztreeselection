<?php
/**
 * File containing the ezcTreeVisitorYUIOptions class.
 *
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Tree
 */

/**
 * Class containing the options for the ezcTreeVisitorYUIOptions class.
 *
 * @property string $basePath
 *           Which string to prefix the href-targets with.
 * @property bool $displayRootNode
 *           Whether the root node should be displayed. The root node will
 *           still be disabled from the links that the visitor create when
 *           $selectedNodeLink is set to true.
 * @property array(string) $highlightNodeIds
 *           Which IDs should have the 'highlight' CSS class added.
 * @property bool $selectedNodeLink
 *           If enabled, only the requested node is shown in links, and not the full path.
 *
 * @package Tree
 * @version //autogentag//
 */
class ezcTreeVisitorYUITreeViewOptions extends ezcBaseOptions
{
    /**
     * Constructs an object with the specified values.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if $options contains a property not defined
     * @throws ezcBaseValueException
     *         if $options contains a property with a value not allowed
     * @param array(string=>mixed) $options
     */
    public function __construct( array $options = array() )
    {
        $this->basePath = '';
        $this->displayRootNode = false;
        $this->highlightNodeIds = array();
        $this->highlightColor = 'orange';
        $this->selectedNodes = array();        
        $this->selectedNodeLink = false;
        $this->yuiNodeClass = 'TextNode';
        $this->treeSelection = ezcTreeVisitorYUITreeView::NOT_SELECTABLE;   
        $this->checkElementPrefix = '';
        $this->inputElementPrefix = '';
        $this->isEditable = false;
        $this->imagePaths = array();
        $this->actionButtons = array();
        $this->isMultiSelect = true;
        $this->dataFields = array();        
        
        parent::__construct( $options );
    }

    /**
     * Sets the option $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     * @throws ezcBaseValueException
     *         if $value is not correct for the property $name
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'treeSelection':
                if ( !is_int( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'integer' );
                }
                $this->properties[$name] = $value;
                break;            
            
            case 'highlightColor':
            case 'inputElementPrefix':
            case 'checkElementPrefix':
            case 'yuiNodeClass':
            case 'basePath':
                if ( !is_string( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'string' );
                }
                $this->properties[$name] = $value;
                break;

            case 'isMultiSelect':
            case 'isEditable':
            case 'displayRootNode':
            case 'selectedNodeLink':
                if ( !is_bool( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'bool' );
                }
                $this->properties[$name] = $value;
                break;

            case 'dataFields':
            case 'actionButtons':
            case 'imagePaths':
            case 'selectedNodes':
            case 'highlightNodeIds':
                if ( !is_array( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'array(string)' );
                }
                $this->properties[$name] = $value;
                break;

            case 'tree':
                if ( !is_object( $value ) )
                {
                    throw new ezcBaseValueException( $name, $value, 'object' );
                }
                $this->properties[$name] = $value;
                break;
                
            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }
}
?>
