var Developer_Animation	= Class.create(/* extends */Reflex_Popup,
{
	initialize	: function($super)
	{
		$super();
		
		this.fx	=	{
						display	: null,
						hide	: null
					};
		this.container.style.opacity	= 0;
	}
});
