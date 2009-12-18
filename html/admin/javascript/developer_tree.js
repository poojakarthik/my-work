
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
		var	oTreeData	=	{
								'img'	:	{
												'template'	:	{
																	'tree_open.png'		: {},
																	'tree_closed.png'	: {}
																}
											},
								'admin'	:	{
												'classes':	{
																'application'	:	{
																						'handler':	{
																										'Application_Handler_Developer.php'	: {}
																									}
																					}
															}
											}
							};
		
		for (sLabel in oTreeData)
		{
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

Developer_Tree._addTreeNode	= function(sName, oChildren)
{
	var	oTreeNode	= new Control_Tree_Node({label: sName});
	
	for (sLabel in oChildren)
	{
		oTreeNode.addChild(Developer_Tree._addTreeNode(sLabel, oChildren[sLabel]));
	}
	
	return oTreeNode;
};
