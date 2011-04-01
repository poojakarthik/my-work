
Component_Account_Links	= Class.create(/* extends */Reflex_Component, {

	initialize	: function ($super) {
		this.CONFIG	= Object.extend({
			'iAccountId'	: {}
		}, this.CONFIG || {});

		$super.apply(this, $A(arguments).slice(1));
	},

	_buildUI	: function () {
		this.NODE	= $T.article(
			$T.h2(
				'Linked Accounts'
			),
			$T.ul({'class':'component-account-links-list'})
		);
	},

	_syncUI	: function () {
		this.NODE.select('component-account-links-list').innerHTML	= "Linked Accounts for Account #" + this.get('iAccountId');
	}
});
