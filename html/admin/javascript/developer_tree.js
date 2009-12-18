
Developer_Tree	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(20);
		
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
		
		var oContent	= document.createElement('div');
		oContent.setStyle({margin : '0.25em'});
		oContent.appendChild(this.oTreePanel.getElement());
		
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle("Tree Control");
	},
	
	_addTreeNode	: function(oTreeData)
	{
		var	oTreeNode	= new Control_Tree_Node();
		this.oTreePanel.getRootNode().addChild();
	}
});
