
Component_Section	= Class.create(/* extends */Reflex_Component, {

	initialize	: function ($super) {
		//debugger;
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'sTitle'	: {
				fnSetter	: (function (mValue) {
					this.NODE.select('.component-section-header-text').first().innerHTML	= String(mValue).escapeHTML();
					return mValue;
				}).bind(this)
			},
			'sIcon'	: {
				fnSetter	: (function (mValue) {
					this.NODE.select('.component-section-header-icon').first().src	= String(mValue);
					return mValue;
				}).bind(this)
			}
		}, this.CONFIG || {});

		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));

		this.NODE.addClassName('component-section');
	},

	_buildUI	: function () {
		//debugger;
		this.NODE	= $T.section(
			$T.header({'class':'component-section-header'},
				$T.h3(
					$T.img({'class':'component-section-header-icon'}),
					$T.span({'class':'component-section-header-text'})
				),
				$T.div({'class':'component-section-header-actions'})
			),
			$T.div({'class':'component-section-body'}),
			$T.footer(
				$T.div({'class':'component-section-footer-actions'})
			)
		);

		// Set default attachment node
		this.ATTACHMENTS['default']			= this.NODE.select('.component-section-body').first();
		this.ATTACHMENTS['header-actions']	= this.NODE.select('.component-section-header-actions').first();
		this.ATTACHMENTS['footer-actions']	= this.NODE.select('.component-section-footer-actions').first();
	}

});
