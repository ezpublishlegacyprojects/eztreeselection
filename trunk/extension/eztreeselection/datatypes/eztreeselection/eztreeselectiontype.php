<?php
//
// Definition of classname class
//
// Created on: <Jul 4, 2008 2008 5:44:10 PM nfrp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.10.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//


class eZTreeSelectionType extends eZDataType
{
    const DATA_TYPE_STRING = "eztreeselection";
    const OPTIONS_CHECKBOX_INPUT_NAME = '_eztreeselection_option_array_';
    const NEW_LABEL_INPUT_NAME = '_eztreeselection_newoptionlabel_';
    const RENAME_OPTION_BUTTON_NAME = '_eztreeselection_renameoption_button_';
    const NEW_OPTION_BUTTON_NAME = '_eztreeselection_newoption_button_';    
    const REMOVE_OPTION_BUTTON_NAME = '_eztreeselection_removeoption_button_';
    const NEW_OPTION_LABEL_INPUT_NAME_SUFFIX = '_new_option_';
    
    public $treeSelectionIni = null;
    
    // @TODO : make this translatable ?
    const ROOT_NODE_ID = "Options";

    /*!
      Constructor
    */
    function eZTreeSelectionType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezi18n( 'kernel/classes/datatypes', "Tree Selection", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
        $this->treeSelectionIni = eZINI::instance( 'eztreeselection.ini' );
    }

    /*!
     Validates all variables given on content class level
     \return eZInputValidator::STATE_ACCEPTED or eZInputValidator::STATE_INVALID if
             the values are accepted or not
    */
    function validateClassAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches all variables inputed on content class level
     \return true if fetching of class attributes are successfull, false if not
    */
    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $attributeContent = $this->classAttributeContent( $classAttribute );
        $classAttributeID = $classAttribute->attribute( 'id' );
        $isMultiSelectWasModified = false;

        if ( $http->hasPostVariable( $base . "_eztreeselection_ismultiple_value_" . $classAttributeID ) )
        {
            $isMultiSelectWasModified = true;
            $isMultipleSelection = false;
            if( $http->postVariable( $base . "_eztreeselection_ismultiple_value_" . $classAttributeID ) != 0 )
            {
                $isMultipleSelection = true;
            }
        }

        $hasPostData = false;

        if ( $http->hasPostVariable( $base . self::RENAME_OPTION_BUTTON_NAME . $classAttributeID ) )
        {
            if ( $http->hasPostVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttributeID ) )
            {
                $tree = $this->getTree( $classAttribute );
                $selectedOptions = $http->postVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttributeID );
                // eZDebug::writeDebug( '$_POST : ' . print_r( $_POST, true ), 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );
                
                foreach ( $selectedOptions as $key => $value )
                {
                    $path = ezcTreeVisitorYUITreeView::decodePath( $key );
                    $id = array_pop( @array_slice( explode( '/', $path ), -1 ) );
                    $ezcNode = $tree->fetchNodeById( $id );
                    
                    if ( $http->hasPostVariable( $base . self::NEW_LABEL_INPUT_NAME . $classAttributeID . $key ) )
                    {
                        $newLabel = $http->postVariable( $base . self::NEW_LABEL_INPUT_NAME . $classAttributeID . $key );
                                                    
                        if ( $ezcNode->id != $tree->getRootNode()->id and
                             $newLabel != ''                          and
                             $newLabel != $ezcNode->data 
                            )
                        {
                            $newLabel = htmlspecialchars( $newLabel, ENT_QUOTES  );
                            // Do not allow renaming the root node
                            eZDebug::writeDebug( "renamed node #{$ezcNode->id} \"{$ezcNode->data}\" into \"$newLabel\"", 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );
                            // the line below should be removed eventually, awaiting the bug fix for http://issues.ez.no/13332
                            $ezcNode->dataFetched = true;
                            $ezcNode->data = $newLabel;
                            $tree->save();
                            $hasPostData = true;                                                        
                        }
                        else
                        {
                            eZDebug::writeWarning( "Renaming the root not is not permitted", 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );                            
                        }
                    }
                }
            }
        }

        if ( $http->hasPostVariable( $base . self::NEW_OPTION_BUTTON_NAME . $classAttributeID ) )
        {       
            if ( $http->hasPostVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttributeID ) )
            {                
                $tree = $this->getTree( $classAttribute );
                $selectedOptions = $http->postVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttributeID );
                // eZDebug::writeDebug( $selectedOptions, 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );
                foreach ( $selectedOptions as $key => $value )
                {
                    if ( $http->hasPostVariable( $base . self::NEW_LABEL_INPUT_NAME . $classAttributeID . self::NEW_OPTION_LABEL_INPUT_NAME_SUFFIX . $key ) )
                    {
                        $newLabel = htmlspecialchars( $http->postVariable( $base . self::NEW_LABEL_INPUT_NAME . $classAttributeID . self::NEW_OPTION_LABEL_INPUT_NAME_SUFFIX . $key  ), ENT_QUOTES  );                                                
                        $path = ezcTreeVisitorYUITreeView::decodePath( $key );
                        $id = array_pop( @array_slice( explode( '/', $path ), -1 ) );
                        $ezcNode = $tree->fetchNodeById( $id );
                        eZDebug::writeDebug( 'New option will be added under : $path ' . $path . "\n" . print_r( $ezcNode, true ), 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );                        
                        $newNode = $tree->createNode( null, $newLabel );
                        eZDebug::writeDebug( $newNode, 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );                        
                        $ezcNode->addChild( $newNode );
                        
                        $tree->save();
                        $hasPostData = true;                        
                    }
                }
            }
        }

        if ( $http->hasPostVariable( $base . self::REMOVE_OPTION_BUTTON_NAME . $classAttributeID ) )
        {
            if ( $http->hasPostVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttributeID ) )
            {
                $tree = $this->getTree( $classAttribute );
                $selectedOptions = $http->postVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttributeID );
                eZDebug::writeDebug( $selectedOptions, 'eZTreeSelectionType::fetchClassAttributeHTTPInput()' );
                
                // @TODO : make sure here that the deepest children are removed first. can this be achieved by sorting the $selectedOptions array on its keys
                //         given that they are derived from the node IDs ?
                krsort( $selectedOptions );
                foreach ( $selectedOptions as $key => $value )
                {
                    $path = ezcTreeVisitorYUITreeView::decodePath( $key );
                    $id = array_pop( @array_slice( explode( '/', $path ), -1 ) );
                    $ezcNode = $tree->fetchNodeById( $id );
                    if ( $ezcNode->id != $tree->getRootNode()->id )
                    {
                        $tree->delete( $ezcNode->id );
                    }                        
                }
                $tree->save();
                $hasPostData = true;                
            }
        }

        if ( $hasPostData and $tree )
        {
            $classAttribute->setAttribute( "data_text5", $tree->getXmlString() );
        }
        
        if ( $isMultiSelectWasModified )
        {
            if ( $isMultipleSelection == true )
            {
                $classAttribute->setAttribute( "data_int1", 1 );
            }
            else
            {
                $classAttribute->setAttribute( "data_int1", 0 );
            }
        }
                    
        return true;
    }
    /*!
     Validates input on content object level
     \return eZInputValidator::STATE_ACCEPTED or eZInputValidator::STATE_INVALID if
             the values are accepted or not
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        $classAttributeContent = $classAttribute->attribute( 'content' );
        $isMultiSelect = $classAttributeContent['is_multiselect'];
        
        if ( $http->hasPostVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $selectedOptions = $http->postVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $contentObjectAttribute->attribute( 'id' ) );
            if ( count( $selectedOptions ) > 1 and $isMultiSelect == 0 )
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                     'Please select one single option.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        }
        else
        {            
            if ( !$classAttribute->attribute( 'is_information_collector' ) )
            {
                if( $classAttribute->attribute( 'is_required' ))
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }
        }        
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches all variables from the object
     \return true if fetching of class attributes are successfull, false if not
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $classAttribute = $contentObjectAttribute->contentClassAttribute();
            $selectedOptions = $http->postVariable( $base . self::OPTIONS_CHECKBOX_INPUT_NAME . $contentObjectAttribute->attribute( 'id' ) );
            $tree = $this->getTree( $classAttribute );
            $selectedOptionsIDs = array();
            
            foreach ( $selectedOptions as $key => $value )
            {
                $path = ezcTreeVisitorYUITreeView::decodePath( $key );
                $id = array_pop( @array_slice( explode( '/', $path ), -1 ) );
                
                if ( $ezcNode = $tree->fetchNodeById( $id ) )
                {
                    $selectedOptionsIDs[] = $id;    
                }
            }
            $contentObjectAttribute->setAttribute( 'data_text', serialize( $selectedOptionsIDs ) );            
            return true;
        }        
        return false;
    }

    /*!
     \reimp
    */
    function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        /*
        if ( $http->hasPostVariable( $base . '_eztreeselection_selected_array_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_eztreeselection_selected_array_' . $contentObjectAttribute->attribute( 'id' ) );

            if ( $data == "" && $contentObjectAttribute->validateIsRequired() )
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes', 'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }
            else
            {
                return eZInputValidator::STATE_ACCEPTED;
            }
        }
        else
        {
            return eZInputValidator::STATE_INVALID;
        }
        */
        // @FIXME : implement this method 
        return eZInputValidator::STATE_ACCEPTED;        
    }

   /*!
    \reimp
    Fetches the http post variables for collected information
   */
    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_eztreeselection_selected_array_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $selectOptions = $http->postVariable( $base . '_eztreeselection_selected_array_' . $contentObjectAttribute->attribute( 'id' ) );
            $idString = ( is_array( $selectOptions ) ? implode( '-', $selectOptions ) : "" );
            $collectionAttribute->setAttribute( 'data_text', $idString );
            return true;
        }
        return false;
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $idString = $originalContentObjectAttribute->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $idString );
            $contentObjectAttribute->store();
        }
    }

    /*!
     Returns the selected options by id.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->attribute( 'contentclass_attribute' );
        $checkElementsInputName = 'ContentObjectAttribute' . self::OPTIONS_CHECKBOX_INPUT_NAME . $contentObjectAttribute->attribute( 'id' );        
        $divID = 'ContentObjectAttribute' . '_eztreeselection_main_' . $contentObjectAttribute->attribute( 'id' );         
        
        $tree = $this->getTree( $classAttribute );   
        if ( $tree === false )
        {
            $errorMessage = ezi18n( 'kernel/classes/datatypes', "<em><span style='color: red;'>Error in loading the Options tree</span></em>", 'Error message' );
            $contentViewYuiTreeViewJavascript = $contentEditYuiTreeViewJavascript = 'document.writeln( "' . $errorMessage . '" );';
        }
        else
        {
            $classAttributeContent = $classAttribute->attribute( 'content' );
            
            $selectedOptionsIDsArray = array();
            if ( $this->hasObjectAttributeContent( $contentObjectAttribute ) )
            {
                $selectedOptionsIDsArray = ( $classAttributeContent['is_multiselect'] == 1 ) ? unserialize( $contentObjectAttribute->attribute( 'data_text' ) ) : array_slice( unserialize( $contentObjectAttribute->attribute( 'data_text' ) ), 0, 1 );
            }
            
            // first get the content view javascript code
            $viewOptions = new ezcTreeVisitorYUITreeViewOptions( array( 'displayRootNode' => true,
                                                                        'tree' => $tree,
                                                                        'highlightNodeIds' => $selectedOptionsIDsArray ) );
            $visitor = new ezcTreeVisitorYUITreeView( $divID, $viewOptions );
            $tree->accept( $visitor );
            $contentViewYuiTreeViewJavascript = (string) $visitor . '';

            $isMultiSelect = (boolean)( $classAttributeContent['is_multiselect'] == 1 );
            // then get the content edit javascript code
            $viewOptions = new ezcTreeVisitorYUITreeViewOptions( array( 'displayRootNode' => true,
                                                                        'yuiNodeClass' => 'CheckBoxNode',
                                                                        'treeSelection' => ezcTreeVisitorYUITreeView::SELECTABLE_LEAFS_ONLY,
                                                                        'checkElementPrefix' => $checkElementsInputName,
                                                                        'tree' => $tree,
                                                                        'selectedNodes' => $selectedOptionsIDsArray,
                                                                        'isMultiSelect' => $isMultiSelect ) );
            $visitor = new ezcTreeVisitorYUITreeView( $divID, $viewOptions );
            $tree->accept( $visitor );
            $contentEditYuiTreeViewJavascript = (string) $visitor . '';
        }
        
        $attrValue = array( 'contentViewYuiTreeViewJavascript' => $contentViewYuiTreeViewJavascript,
                            'contentEditYuiTreeViewJavascript' => $contentEditYuiTreeViewJavascript   
                          );
        return $attrValue;                                 
    }

    /*!
     Returns the content data for the given content class attribute.
    */
    function classAttributeContent( $classAttribute )
    {
        $divID = 'ContentClass_eztreeselection_main_' . $classAttribute->attribute( 'id' );
        $checkElementsInputName = 'ContentClass' . self::OPTIONS_CHECKBOX_INPUT_NAME . $classAttribute->attribute( 'id' );
        $inputElementsInputName = 'ContentClass' . self::NEW_LABEL_INPUT_NAME . $classAttribute->attribute( 'id' );        
        $hasOptions = false;

        // eZDebug::writeDebug( 'XML : ' . print_r( $classAttribute->attribute( 'data_text5' ), true ),  'eZTreeSelectionType::classAttributeContent()' );
        
        $tree = $this->getTree( $classAttribute );   
        if ( $tree === false )
        {
            $errorMessage = ezi18n( 'kernel/classes/datatypes', "<em><span style='color: red;'>Error in loading the Options tree</span></em>", 'Error message' );
            $classViewYuiTreeViewJavascript = $classEditYuiTreeViewJavascript = 'document.writeln( "' . $errorMessage . '" );';
        }
        else
        {
            $base = 'ContentClass';
            // first get the class view javascript code
            $viewOptions = new ezcTreeVisitorYUITreeViewOptions( array( 'displayRootNode' => true,
                                                                        'tree' => $tree ) );
            $visitor = new ezcTreeVisitorYUITreeView( $divID, $viewOptions );
            $tree->accept( $visitor );
            $classViewYuiTreeViewJavascript = (string) $visitor . '';

            // then get the class edit javascript code
            $http = eZHTTPTool::instance();
            $imagePaths = array();
            $actionButtons = array();
            
            $renameImageName = $this->treeSelectionIni->variable( 'ImageSettings', 'RenameImage' );
            $renameImageName = eZURLOperator::eZImage( null, $renameImageName, 'ezimage', false );
            $renameImageName = $http->createRedirectUrl( $renameImageName, array( 'pre_url' => false ) );
            $imagePaths['renameImage'] = $renameImageName; 

            $addChildImageName = $this->treeSelectionIni->variable( 'ImageSettings', 'AddChildImage' );
            $addChildImageName = eZURLOperator::eZImage( null, $addChildImageName, 'ezimage', false );
            $addChildImageName = $http->createRedirectUrl( $addChildImageName, array( 'pre_url' => false ) );
            $imagePaths['addChildImage'] = $addChildImageName;
            
            $removeChildImageName = $this->treeSelectionIni->variable( 'ImageSettings', 'RemoveChildImage' );
            $removeChildImageName = eZURLOperator::eZImage( null, $removeChildImageName, 'ezimage', false );
            $removeChildImageName = $http->createRedirectUrl( $removeChildImageName, array( 'pre_url' => false ) );
            $imagePaths['removeChildImage'] = $removeChildImageName;
            
            $validateLabelModificationImage = $this->treeSelectionIni->variable( 'ImageSettings', 'ValidateLabelModificationImage' );
            $validateLabelModificationImage = eZURLOperator::eZImage( null, $validateLabelModificationImage, 'ezimage', false );
            $validateLabelModificationImage = $http->createRedirectUrl( $validateLabelModificationImage, array( 'pre_url' => false ) );
            $imagePaths['validateLabelModificationImage'] = $validateLabelModificationImage;            

            $removeButtonName = $base . self::REMOVE_OPTION_BUTTON_NAME . $classAttribute->attribute( 'id' );
            $actionButtons['removeButtonName'] = $removeButtonName;
            
            $renameButtonName = $base . self::RENAME_OPTION_BUTTON_NAME . $classAttribute->attribute( 'id' );
            $actionButtons['renameButtonName'] = $renameButtonName;
            
            $addChildButtonName = $base . self::NEW_OPTION_BUTTON_NAME . $classAttribute->attribute( 'id' );
            $actionButtons['addChildButtonName'] = $addChildButtonName;
            
            $viewOptions = new ezcTreeVisitorYUITreeViewOptions( array( 'displayRootNode' => true,
                                                                        'yuiNodeClass' => 'CheckBoxNode',
                                                                        'treeSelection' => ezcTreeVisitorYUITreeView::SELECTABLE_TREE,
                                                                        'isEditable' => true,
                                                                        'imagePaths' => $imagePaths,
                                                                        'actionButtons' => $actionButtons, 
                                                                        'checkElementPrefix' => $checkElementsInputName,
                                                                        'inputElementPrefix' => $inputElementsInputName,
                                                                        'tree' => $tree  ) );
            $visitor = new ezcTreeVisitorYUITreeView( $divID, $viewOptions );
            $tree->accept( $visitor );
            $classEditYuiTreeViewJavascript = (string) $visitor . '';
            $hasOptions = ( $tree->getChildCount( $tree->getRootNode()->id ) > 0 ) ? true : false ;                        
        }
        
        $attrValue = array( /*'options' => $optionArray,*/
                            'has_options' => $hasOptions,
                            'is_multiselect' => $classAttribute->attribute( 'data_int1' ), 
                            'classViewYuiTreeViewJavascript' => $classViewYuiTreeViewJavascript,
                            'classEditYuiTreeViewJavascript' => $classEditYuiTreeViewJavascript 
                          );
        // eZDebug::writeDebug( print_r( $attrValue, true ),  'eZTreeSelectionType::classAttributeContent()' );
        return $attrValue;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->attribute( 'contentclass_attribute' );
        $metaDataElements = array();
        $tree = $this->getTree( $classAttribute );   
        if ( $tree !== false )
        {
            $selectedOptions = unserialize( $contentObjectAttribute->attribute( 'data_text' ) );
            foreach( $selectedOptions as $option )
            {
                $node = $tree->fetchNodeById( $option );
                $metaDataElements[] = $node->data;         
            }
        }
        return implode( ' ', $metaDataElements );
    }

    function toString( $contentObjectAttribute )
    {
        $selected = $this->objectAttributeContent( $contentObjectAttribute );
        $classContent = $this->classAttributeContent( $contentObjectAttribute->attribute( 'contentclass_attribute' ) );

        if ( count( $selected ) )
        {
            $optionArray = $classContent['options'];
            foreach ( $selected as $id )
            {
                foreach ( $optionArray as $option )
                {
                    $optionID = $option['id'];
                    if ( $optionID == $id )
                        $returnData[] = $option['name'];
                }
            }
            return eZStringUtils::implodeStr( $returnData, '|' );
        }
        return '';
    }


    function fromString( $contentObjectAttribute, $string )
    {
        if ( $string == '' )
            return true;
        $selectedNames = eZStringUtils::explodeStr( $string, '|' );
        $selectedIDList = array();
        $classContent = $this->classAttributeContent( $contentObjectAttribute->attribute( 'contentclass_attribute' ) );
        $optionArray = $classContent['options'];
        foreach ( $selectedNames as $name )
        {
            foreach ( $optionArray as $option )
            {
                $optionName = $option['name'];
                if ( $optionName == $name )
                    $selectedIDList[] = $option['id'];
            }
        }
        $idString = ( is_array( $selectedIDList ) ? implode( '-', $selectedIDList ) : "" );
        $contentObjectAttribute->setAttribute( 'data_text', $idString );
        return true;
    }

    /*!
     Returns the value as it will be shown if this attribute is used in the object name pattern.
    */
    function title( $contentObjectAttribute, $name = null )
    {
        $classAttribute = $contentObjectAttribute->attribute( 'contentclass_attribute' );
        $titleElements = array();
        $tree = $this->getTree( $classAttribute );   
        if ( $tree !== false )
        {
            $selectedOptions = unserialize( $contentObjectAttribute->attribute( 'data_text' ) );
            foreach( $selectedOptions as $option )
            {
                $node = $tree->fetchNodeById( $option );
                $titleElements[] = $node->data;         
            }
        }
        return implode( ' ', $titleElements );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        if ( $contentObjectAttribute->attribute( 'data_text' ) != '' )
        {
            return true;   
        }
        return false;
    }

    /*!
     \reimp
    */
    function sortKey( $contentObjectAttribute )
    {
        //return strtolower( $contentObjectAttribute->attribute( 'data_text' ) );
        // @FIXME : implement this method
        return true;
    }

    /*!
     \reimp
    */
    function sortKeyType()
    {
        // @FIXME : check this method
        return 'string';
    }

    /*!
     \return true if the datatype can be indexed
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     \reimp
    */
    function isInformationCollector()
    {
        return true;
    }

    /*!
     \reimp
    */
    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        // @FIXME : check this method, re implement if needed
        /*
        $isMultipleSelection = $classAttribute->attribute( 'data_int1'  );
        $xmlString = $classAttribute->attribute( 'data_text5' );

        $dom = new DOMDocument( '1.0', 'utf-8' );
        $success = $dom->loadXML( $xmlString );
        $domRoot = $dom->documentElement;
        $options = $domRoot->getElementsByTagName( 'options' )->item( 0 );

        $importedOptionsNode = $attributeParametersNode->ownerDocument->importNode( $options, true );
        $attributeParametersNode->appendChild( $importedOptionsNode );
        $isMultiSelectNode = $attributeParametersNode->ownerDocument->createElement( 'is-multiselect', $isMultipleSelection );
        $attributeParametersNode->appendChild( $isMultiSelectNode );
		*/
    }

    /*!
     \reimp
    */
    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        // @FIXME : check this method, re implement if needed        
        /*
        $options = $attributeParametersNode->getElementsByTagName( 'options' )->item( 0 );

        $doc = new DOMDocument( '1.0', 'utf-8' );
        $root = $doc->createElement( 'eztreeselection' );
        $doc->appendChild( $root );

        $importedOptions = $doc->importNode( $options, true );
        $root->appendChild( $importedOptions );

        $xml = $doc->saveXML();
        $classAttribute->setAttribute( 'data_text5', $xml );

        if ( $attributeParametersNode->getElementsByTagName( 'is-multiselect' )->item( 0 )->textContent == 0 )
            $classAttribute->setAttribute( 'data_int1', 0 );
        else
            $classAttribute->setAttribute( 'data_int1', 1 );
        */
    }

    /*!
     \reimp
    */
    function serializeContentObjectAttribute( $package, $objectAttribute )
    {
       // @FIXME : check this method, re implement if needed        
       /*
       $node = $this->createContentObjectAttributeDOMNode( $objectAttribute );
       $idString = $objectAttribute->attribute( 'data_text' );

       $idStringNode = $node->ownerDocument->createElement( 'idstring', $idString );
       $node->appendChild( $idStringNode );
       return $node;
	   */
    }

    /*!
     \reimp
    */
    function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
        // @FIXME : check this method, re implement if needed        
        /*
        $idStringNode = $attributeNode->getElementsByTagName( 'idstring' )->item( 0 );
        $idString = $idStringNode ? $idStringNode->textContent : '';
        $objectAttribute->setAttribute( 'data_text', $idString );
        */ 
   }

    
    public function getTree( $contentClassAttribute )
    {
        $xmlString = $contentClassAttribute->attribute( 'data_text5' );
        // eZDebug::writeDebug( 'building tree based on the string : ' . print_r( $xmlString, true ), 'eZTreeSelectionType::getTree()' );        
        $tree = null;
        $store = new ezcTreeXmlInternalDataStore();

        if ( $xmlString != '' )
        {
            try {
                $tree = new ezcTreeMemoryXml( $xmlString, $store );
            }                        
            catch ( ezcTreeInvalidXmlFormatException $e )
            {
                eZDebug::writeError( 'ezcTreeInvalidXmlFormatException : ' . print_r( $e->getMessage(), true ), 'eZTreeSelectionType::getTree()' );                
                return false;        
            }
        }
        else
        {
            $tree = ezcTreeMemoryXml::create( '', $store );
            $this->initiateTreeStructure( $tree );
        }
        return $tree;            
    }
    
    public function initiateTreeStructure( ezcTreeMemoryXml $tree )
    {
        $tree->autoId = true; 
        $rootNode = $tree->createNode( null /*self::ROOT_NODE_ID*/, self::ROOT_NODE_ID );
        // eZDebug::writeDebug( $rootNode, 'eZTreeSelectionType::initiateTreeStructure()' );
        $tree->setRootNode( $rootNode );            
        return $tree;
    }
}

eZDataType::register( eZTreeSelectionType::DATA_TYPE_STRING, "eZTreeSelectionType" );
?>
