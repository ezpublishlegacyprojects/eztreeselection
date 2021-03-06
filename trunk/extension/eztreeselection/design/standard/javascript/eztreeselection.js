/**
 * The check box marks a task complete.  It is a simulated form field 
 * with three states ...
 * 0=unchecked, 1=some children checked, 2=all children checked
 * When a task is clicked, the state of the nodes and parent and children
 * are updated, and this behavior cascades.
 *
 * @extends YAHOO.widget.TextNode
 * @constructor
 * @param oData    {object}  A string or object containing the data that will
 *                           be used to render this node.
 * @param oParent  {Node}    This node's parent node
 * @param expanded {boolean} The initial expanded/collapsed state
 * @param checked  {boolean} The initial checked/unchecked state
 */
YAHOO.widget.CheckBoxNode = function(oData, oParent, expanded, checked) {

    if (YAHOO.widget.LogWriter) {
        this.logger = new YAHOO.widget.LogWriter(this.toString());
    } else {
        this.logger = YAHOO;
    }

    if (oData) { 
        this.init(oData, oParent, expanded);
        this.setUpLabel(oData);
        // this.setUpCheck(checked);
        this.checkElementPrefix = ( typeof( oData.checkElementPrefix ) == 'undefined' ) ? '' : oData.checkElementPrefix ;
        this.inputElementPrefix = ( typeof( oData.inputElementPrefix ) == 'undefined' ) ? '' : oData.inputElementPrefix ;        
        this.ezcTreeID = ( typeof( oData.ezcTreeID ) == 'undefined' ) ? '' : oData.ezcTreeID ;
        this.isSelectable = ( typeof( oData.isSelectable ) == 'undefined' ) ? false : oData.isSelectable ;
        this.isSelected = ( typeof( oData.isSelected ) == 'undefined' ) ? false : oData.isSelected ;
		this.setUpCheck( this.isSelected );        
        this.isMultiSelect = ( typeof( oData.isMultiSelect ) == 'undefined' ) ? true : oData.isMultiSelect ;
        this.isEditable = ( typeof( oData.isEditable ) == 'undefined' ) ? false : oData.isEditable ;
        this.imagePaths = ( typeof( oData.imagePaths ) == 'undefined' ) ? null : oData.imagePaths ;
        this.actionButtons = ( typeof( oData.actionButtons ) == 'undefined' ) ? null : oData.actionButtons ;
    }
};

YAHOO.extend(YAHOO.widget.CheckBoxNode, YAHOO.widget.TextNode, {

    /**
     * Contains an object associating a key ( ex : "removeButtonName", or "addChildButtonName" ) to an input name.
     * This mapping can be used build the edit widget.
     * @type array
     */
    actionButtons: null,
    
    /**
     * Contains an object associating a key ( ex : "renameImage", or "addChildImage" ) to an image path.
     * These images can among other be used to build the edit interface of a node.
     * @type array
     */
    imagePaths: null,

    /**
     * True if the node is editable. This can result in having an edit widget ( rename, delete, add child ) beside the node's label.
     * @type boolean
     */
    isEditable: false,

    /**
     * True if the tree can have multiple options selected at once.
     * @type boolean
     */
    isMultiSelect: true,
        
    /**
     * True if the node is selected. This can result in having a checked checkbox beside the node's label.
     * This is taken into account only if this.isSelectable is true.
     * @type boolean
     */
    isSelected: false,
        
    /**
     * True if the node should be selectable. This can result in having a checkbox beside the node's label
     * @type boolean
     */
    isSelectable: false,

    /**
     * ID generated by the ezc Tree component. Can be any unique ID in your Tree backend
     * @type string
     */
    ezcTreeID: '',

    /**
     * Prefix for the input element ( edit interface, input used to rename a node ) names
     * @type string
     */
    inputElementPrefix: '',
        
    /**
     * Prefix for checkboxes' names
     * @type string
     */
    checkElementPrefix: '',
    
    /**
     * True if checkstate is 1 (some children checked) or 2 (all children checked),
     * false if 0.
     * @type boolean
     */
    checked: false,

    /**
     * checkState
     * 0=unchecked, 1=some children checked, 2=all children checked
     * @type int
     */
    checkState: 0,

    taskNodeParentChange: function() {
        //this.updateParent();
    },

    setUpCheck: function(checked) {
        // if this node is checked by default, run the check code to update
        // the parent's display state
        if (checked && checked === true) {
            this.check();
        // otherwise the parent needs to be updated only if its checkstate 
        // needs to change from fully selected to partially selected
        }

        // set up the custom event on the tree for checkClick
        /**
         * Custom event that is fired when the check box is clicked.  The
         * custom event is defined on the tree instance, so there is a single
         * event that handles all nodes in the tree.  The node clicked is 
         * provided as an argument.  Note, your custom node implentation can
         * implement its own node specific events this way.
         *
         * @event checkClick
         * @for YAHOO.widget.TreeView
         * @param {YAHOO.widget.Node} node the node clicked
         */
        if (this.tree && !this.tree.hasEvent("checkClick")) {
            this.tree.createEvent("checkClick", { scope: this.tree } );
        }
        this.subscribe("parentChange", this.taskNodeParentChange);
        this.tree.subscribe( "checkClick", this.onCheckClick, this );        
    },

    /**
     * The id of the check element
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getCheckElId: function() { 
        return this.checkElementPrefix + '[' +  this.index + ']'; 
    },

    /**
     * The name of the check element
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getCheckElName: function() { 
        return this.checkElementPrefix + '[' + this.ezcTreeID + ']'; 
    },

    /**
     * Returns the check box element
     * @return the check html element (img)
     */
    getCheckEl: function() { 
        return document.getElementById(this.getCheckElId()); 
    },

    /**
     * The style of the check element, derived from its current state
     * @return {string} the css style for the current check state
     */
    getCheckStyle: function() { 
        return "ygtvcheck" + this.checkState;
    },

    /**
     * Returns the link that will invoke this node's check toggle
     * @return {string} returns the link required to adjust the checkbox state
     */
    getCheckLink: function() { 
        return "YAHOO.widget.TreeView.getNode(\'" + this.tree.id + "\'," + 
            this.index + ").checkClick()";
    },

    /**
     * The id of the div comprising the elements used in the edit interface to add a new option
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getNewOptionElId: function() { 
        return this.inputElementPrefix + '_new_option_container_' +  this.index; 
    },

    /**
     * The id of the input element used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getNewOptionInputElId: function() { 
        return this.inputElementPrefix + '_new_option_' +  this.index; 
    },

    /**
     * The name of the input element used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getNewOptionInputName: function() { 
        return this.inputElementPrefix + '_new_option_' + this.ezcTreeID; 
    },
        
    /**
     * The id of the input element used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getAddChildImageElId: function() { 
        return this.inputElementPrefix + '_add_child_image' +  this.index; 
    },
                
    /**
     * The id of the div comprising the elements used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getEditInputsElId: function() { 
        return this.inputElementPrefix + '_inputs_container_' +  this.index; 
    },

    /**
     * The style of the div comprising the elements used in the edit interface to rename a node
     * @return {string} the css style for the current check state
     */
    getEditInputsStyle: function() { 
        return "ygtvinputscontainer";
    },
        
    /**
     * The id of the input element used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getLabelEditElId: function() { 
        return this.inputElementPrefix + '_' +  this.index; 
    },
    
    /**
     * The id of the input element used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getRenameImageElId: function() { 
        return this.inputElementPrefix + '_rename_image' +  this.index; 
    },


    /**
     * The name of the input element used in the edit interface to rename a node
     * @for YAHOO.widget.CheckBoxNode
     * @type string
     */
    getLabelEditName: function() { 
        return this.inputElementPrefix + this.ezcTreeID; 
    },
    
    /**
     * The style of the input element used in the edit interface to rename a node.
     * @return {string} the css style for the current check state
     */
    getLabelEditStyle: function() { 
        return "ygtvlabeledit";
    },    
    
    /**
     * Invoked when the user clicks the check box
     */
    checkClick: function() { 
        this.logger.log("previous checkstate: " + this.checked);
        if ( this.checked == false  ) {
            this.check();
        } else {
            this.uncheck();
        }
		
        // this.onCheckClick(this, this);
        this.tree.fireEvent("checkClick", this );
    },
    
    /**
     * Override to get the check click event
     */
    onCheckClick: function( src, localScope ) {
	    if ( localScope.isMultiSelect === false && ( src.index != localScope.index ) )
	    {
	        if ( src.checked === true && localScope.checked === true )
	        {
	        	localScope.uncheck();
	        }
        }
    },

    /**
     * If the node has been rendered, update the html to reflect the current
     * state of the node.
     */
    updateCheckHtml: function() { 
		if ( this.parent && this.parent.childrenRendered )
        {
	        if ( this.checked == true )
			{
				this.getCheckEl().checked = true;
			}
			else
			{
				this.getCheckEl().checked = false;
			}
		} 
        /*
        if (this.parent && this.parent.childrenRendered) {
            this.getCheckEl().className = this.getCheckStyle();
        }*/
		// @FIXME
    },

    /**
     * Updates the state.  The checked property is true if the state is 1 or 2
     * 
     * @param the new check state
     */
    setCheckState: function(state) { 
        // this.checkState = state;
        this.checked = (state > 0);
    },

    /**
     * Check this node
     */
    check: function() { 
        this.logger.log("check");
        this.setCheckState(2);
        this.updateCheckHtml();
    },

    /**
     * Uncheck this node
     */
    uncheck: function() { 
        this.setCheckState(0);
        this.updateCheckHtml();
    },

    // Overrides YAHOO.widget.TextNode
    getNodeHtml: function() { 
        this.logger.log("Generating html");
        var sb = [];

        var getNode = 'YAHOO.widget.TreeView.getNode(\'' +
                        this.tree.id + '\',' + this.index + ')';


        sb[sb.length] = '<table border="0" cellpadding="0" cellspacing="0">';
        sb[sb.length] = '<tr>';
        
        for (var i=0;i<this.depth;++i) {
            //sb[sb.length] = '<td class="' + this.getDepthStyle(i) + '"> </td>';
            sb[sb.length] = '<td class="' + this.getDepthStyle(i) + '"><div class="ygtvspacer"></div></td>';
        }

        sb[sb.length] = '<td';
        sb[sb.length] = ' id="' + this.getToggleElId() + '"';
        sb[sb.length] = ' class="' + this.getStyle() + '"';
        if (this.hasChildren(true)) {
            sb[sb.length] = ' onmouseover="this.className=';
            sb[sb.length] = 'YAHOO.widget.TreeView.getNode(\'';
            sb[sb.length] = this.tree.id + '\',' + this.index +  ').getHoverStyle()"';
            sb[sb.length] = ' onmouseout="this.className=';
            sb[sb.length] = 'YAHOO.widget.TreeView.getNode(\'';
            sb[sb.length] = this.tree.id + '\',' + this.index +  ').getStyle()"';
        }
        sb[sb.length] = ' onclick="javascript:' + this.getToggleLink() + '"> ';
        //sb[sb.length] = '</td>';
        sb[sb.length] = '<div class="ygtvspacer"></div></td>';

		if ( this.isSelectable )
		{
	        // check box
	        sb[sb.length] = '<td>';
	        sb[sb.length] = '<input type="checkbox" ';
	        sb[sb.length] = ' id="' + this.getCheckElId() + '"';
	        sb[sb.length] = ' name="' + this.getCheckElName() + '"';
	        sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
	        sb[sb.length] = ' onclick="javascript:' + this.getCheckLink() + '"';
	        
	        // if the node is editable, the checkbox is not visible. It is instead used as an auxiliary input to send data/actions to the backend.
	        if ( this.isEditable )
	        {
	        	sb[sb.length] = ' style="display: none;"';
	        }
	        	        
	        //check the checkbox if required :
			if ( this.isSelected )
	        {
	        	sb[sb.length] = ' checked="checked"';	        
			}

	        sb[sb.length] = ' />';
	        sb[sb.length] = ' </td>';
        }

        sb[sb.length] = '<td>';
        sb[sb.length] = '<a';
        sb[sb.length] = ' id="' + this.labelElId + '"';
        sb[sb.length] = ' class="' + this.labelStyle + '"';
        sb[sb.length] = ' href="' + this.href + '"';
        sb[sb.length] = ' target="' + this.target + '"';
        sb[sb.length] = ' onclick="return ' + getNode + '.onLabelClick(' + getNode +')"';
        if (this.hasChildren(true)) {
            sb[sb.length] = ' onmouseover="document.getElementById(\'';
            sb[sb.length] = this.getToggleElId() + '\').className=';
            sb[sb.length] = 'YAHOO.widget.TreeView.getNode(\'';
            sb[sb.length] = this.tree.id + '\',' + this.index +  ').getHoverStyle()"';
            sb[sb.length] = ' onmouseout="document.getElementById(\'';
            sb[sb.length] = this.getToggleElId() + '\').className=';
            sb[sb.length] = 'YAHOO.widget.TreeView.getNode(\'';
            sb[sb.length] = this.tree.id + '\',' + this.index +  ').getStyle()"';
        }
        sb[sb.length] = (this.nowrap) ? ' nowrap="nowrap" ' : '';
        sb[sb.length] = ' >';
        sb[sb.length] = this.label;
        sb[sb.length] = '</a>';

		if ( this.isEditable )
		{        
	        // insert inputs/elements necessary for renaming an option
	        sb[sb.length] = '<div id="' + this.getEditInputsElId() + '"';
	        sb[sb.length] = ' class="' + this.getEditInputsStyle() + '"';
	        sb[sb.length] = ' >';
	        sb[sb.length] = '<input type="text" ';
	        sb[sb.length] = ' id="' + this.getLabelEditElId() + '"';
	        sb[sb.length] = ' name="' + this.getLabelEditName() + '"';
		    sb[sb.length] = ' class="' + this.getLabelEditStyle() + '"';
		    sb[sb.length] = ' onblur="return ' + getNode + '.rename();"';
		    sb[sb.length] = ' onkeypress="return ' + getNode + '.catchKeyPress( event, \'rename\' );"';		    
		    sb[sb.length] = ' value="' + this.label + '"';
		    sb[sb.length] = ' />';
		    sb[sb.length] = '<img src="' + this.imagePaths.validateLabelModificationImage + '"';
	        // clicking on the image automatically blurs the input :)
	        // sb[sb.length] = ' onclick="return document.getElementById(' + this.getLabelEditElId() + ').blur();"';	    
		    sb[sb.length] = ' />';
		    sb[sb.length] = '</div>';
		    
	        // insert inputs/elements necessary for adding a new option
	        sb[sb.length] = '<div id="' + this.getNewOptionElId() + '"';
	        sb[sb.length] = ' class="' + this.getEditInputsStyle() + '"';
	        sb[sb.length] = ' >';
	        sb[sb.length] = '<input type="text" ';
	        sb[sb.length] = ' id="' + this.getNewOptionInputElId() + '"';
	        sb[sb.length] = ' name="' + this.getNewOptionInputName() + '"';
		    sb[sb.length] = ' class="' + this.getLabelEditStyle() + '"';
		    sb[sb.length] = ' onblur="return ' + getNode + '.addChild();"';
		    sb[sb.length] = ' onkeypress="return ' + getNode + '.catchKeyPress( event, \'addChild\' );"';		    
		    sb[sb.length] = ' value=""';
		    sb[sb.length] = ' />';
		    sb[sb.length] = '<img src="' + this.imagePaths.validateLabelModificationImage + '"';
		    // clicking on the image automatically blurs the input :) 
	        // sb[sb.length] = ' onclick="javascript:document.getElementById(' + this.getNewOptionInputElId() + ').blur();"';	    
		    sb[sb.length] = ' />';
		    sb[sb.length] = '</div>';		    
        }
        
        sb[sb.length] = '</td>';

	    sb[sb.length] = this.getEditWidgetHtml();
        
        sb[sb.length] = '</tr>';
        sb[sb.length] = '</table>';
        
        return sb.join("");

    },

    getEditWidgetHtml: function() 
    {
    	var sb = [];
        var getNode = 'YAHOO.widget.TreeView.getNode(\'' +
                        this.tree.id + '\',' + this.index + ')';
                            	
    	if ( this.isEditable )
		{
			sb[sb.length] = '<td><div style="margin-left: 30px;">';

			if ( typeof( this.imagePaths.renameImage ) != 'undefined' )
			{
				sb[sb.length] = '<img src="' + this.imagePaths.renameImage + '"';
				sb[sb.length] = ' id="' + this.getRenameImageElId() + '"';
				sb[sb.length] = ' alt="Rename option"';
				sb[sb.length] = ' title="Rename option"';
				sb[sb.length] = ' onclick="return ' + getNode + '.toggleLabelEdition();"';
				sb[sb.length] =	' />';
			}

			if ( typeof( this.imagePaths.addChildImage ) != 'undefined' )
			{
				sb[sb.length] = '<img src="' + this.imagePaths.addChildImage + '"';
				sb[sb.length] = ' id="' + this.getAddChildImageElId() + '"';				
				sb[sb.length] = ' alt="Add child option under this option"';
				sb[sb.length] = ' title="Add child option under this option"';
				sb[sb.length] = ' onclick="return ' + getNode + '.toggleNewOptionEdition();"';				
				sb[sb.length] =	' />';												
			}

			if ( typeof( this.imagePaths.removeChildImage ) != 'undefined' )
			{
				sb[sb.length] = '<img src="' + this.imagePaths.removeChildImage + '"';
				sb[sb.length] = ' onclick="return ' + getNode + '.remove();"';
				sb[sb.length] = ' alt="Remove this option and all its children"';
				sb[sb.length] = ' title="Remove this option and all its children"';								
				sb[sb.length] = ' />';
			}
			
			sb[sb.length] = '</div></td>';
		}
		return sb.join("");
    }, 

	toggleNewOptionEdition: function() 
    {
		var addChildImage = document.getElementById( this.getAddChildImageElId() );
		var editDiv = document.getElementById( this.getNewOptionElId() );
    	var newOptionInput = document.getElementById( this.getNewOptionInputElId() );
		
		if ( addChildImage.style.display == "none" )
		{
			addChildImage.style.display = 'inline';			
			editDiv.style.display = 'none';
		}
		else
		{
			addChildImage.style.display = 'none';
			editDiv.style.display = 'inline';
			newOptionInput.focus();
		}
    },
    
	toggleLabelEdition: function() 
    {
		var renameImage = document.getElementById( this.getRenameImageElId() );
		var editDiv = document.getElementById( this.getEditInputsElId() );
		var labelEditInput = document.getElementById( this.getLabelEditElId() );
		var labelElement = document.getElementById( this.labelElId );
		
		if ( renameImage.style.display == "none" )
		{
			renameImage.style.display = 'inline';
			labelElement.style.display = 'inline';			
			editDiv.style.display = 'none';
		}
		else
		{
			renameImage.style.display = 'none';
			labelElement.style.display = 'none';
			editDiv.style.display = 'inline';
			labelEditInput.focus();
		}
    }, 

    addChild: function() 
    {
    	var newOptionInput = document.getElementById( this.getNewOptionInputElId() );

		if ( newOptionInput.value != '' )
		{
	    	// check the checkbox first.
	    	var checkbox = document.getElementById( this.getCheckElId() );
	    	checkbox.checked = true;
	    	
	    	// then click the hidden 'New option' button
    		var newOptionButton = document.getElementById( this.actionButtons.addChildButtonName );
    		newOptionButton.click();			
		}
		else
		{
			this.toggleNewOptionEdition();	
		}
    },
    
    rename: function() 
    {
    	var labelEditInput = document.getElementById( this.getLabelEditElId() );
		var labelElement = document.getElementById( this.labelElId );
		// var question = 'Rename "' + labelElement.innerHTML + '" into "' + labelEditInput.value + '" ?'; 

		if ( labelEditInput.value != labelElement.innerHTML /* && confirm( question )*/ )
		{
	    	// check the checkbox first.
	    	var checkbox = document.getElementById( this.getCheckElId() );
	    	checkbox.checked = true;
	    	
	    	// then click the hidden 'Rename selected' button
    		var renameButton = document.getElementById( this.actionButtons.renameButtonName );
    		renameButton.click();			
		}
		else
		{
			this.toggleLabelEdition();	
		}
    },
    	
    remove: function() 
    {
    	var labelElement = document.getElementById( this.labelElId );
		var question = 'Remove "' + labelElement.innerHTML + '" ?';
		if ( confirm( question ) )
		{		
	    	// check the checkbox first.
	    	var checkbox = document.getElementById( this.getCheckElId() );
	    	checkbox.checked = true;
			    	
	    	// then click the hidden 'Remove' button
	    	var removeButton = document.getElementById( this.actionButtons.removeButtonName );
	    	
	    	// @TODO : add a confirmation dialog prior to actually clicking the remove action ?
	    	removeButton.click();
    	}
    },
    
    catchKeyPress: function( event, callback ) 
    {
    	var keyCodeList = new Array( /* return */ '13', /* tab */'9' );
    	
    	for ( var i=0; i < keyCodeList.length; i++ )
    	{
    		if ( keyCodeList[i] == event.keyCode )
    		{
    			var testString = 'typeof( this.' + callback + ' )'; 
    			if( eval( testString ) == 'function' )
    			{
	    			// prevent default action on event here
	    			YAHOO.util.Event.preventDefault( event );

	    			// trigger callback here
	    			var execString = 'this.' + callback + '()';
	    			eval( execString );
    			}
    			break;
    		}
    	}
    },    
        
    toString: function() {
        return "CheckBoxNode (" + this.index + ") " + this.label;
    }

});