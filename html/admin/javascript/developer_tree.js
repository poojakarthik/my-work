
Developer_Tree	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(40);
		
		// Init Tree
		this.oTreePanel	= new Control_Tree();
		this.oTreePanel.setColumns(	{
										'label'			:	{
																sTitle	: 'File',
																fWidth	: 50
															},
										'size'			:	{
																sTitle	: 'Size',
																fWidth	: 25
															},
										'modified-on'	:	{
																sTitle	: 'Modified On',
																fWidth	: 25
															}
									});
		
		// Add some Tree Data
		var oTreeData	=	{
								'img'	:	{
												_children	:	{
																	'template'	:	{
																						_children	:	{
																											'tree_open.png'		:	{
																																		_icon	: '../admin/img/template/mime/png_small.png',
																																		size	: '1KB'
																																	},
																											'tree_closed.png'	:	{
																																		_icon	: '../admin/img/template/mime/png_small.png',
																																		size	: '1KB'
																																	},
																										}
																					}
																}
											},
								'admin'	:	{
												_children	:	{
																	'classes'	:	{
																						_children	:	{
																											'application'	:	{
																																	_children	:	{
																																						'handler'	:	{
																																											_children	:	{
																																																'Application_Handler_Developer.php'	:	{
																																																															_icon	: '../admin/img/template/mime/text_small.png',
																																																															size	: '4KB'
																																																														},
																																															}
																																										}
																																					}
																																}
																										}
																					}
																}
											}
							};
		
		for (sLabel in oTreeData)
		{
			//alert("Adding '"+sLabel+"'");
			this.oTreePanel.getRootNode().addChild(Developer_Tree._addTreeNode(sLabel, oTreeData[sLabel]));
		}
		this.oTreePanel.paint();
		
		var oContent	= document.createElement('div');
		oContent.setStyle({margin : '0.25em'});
		oContent.appendChild(this.oTreePanel.getElement());
		
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle("Tree Control");
	}
});

Developer_Tree._addTreeNode	= function(sName, oDefinition)
{
	var	oTreeNode	= new Control_Tree_Node({label: sName});
	
	var	oData		=	{
							label	: sName
						};
	for (sProperty in oDefinition)
	{
		//alert("Adding '"+sLabel+"'");
		if (sProperty == '_children')
		{
			for (sChildName in oDefinition._children)
			{
				oTreeNode.addChild(Developer_Tree._addTreeNode(sChildName, oDefinition._children[sChildName]));
			}
		}
		else if (sProperty == '_icon')
		{
			oTreeNode.setIcon(oDefinition._icon);
		}
		else
		{
			oData[sProperty]	= oDefinition[sProperty];
		}
	}
	oTreeNode.setData(oData);
	
	oTreeNode.setIcon();
	
	return oTreeNode;
};
