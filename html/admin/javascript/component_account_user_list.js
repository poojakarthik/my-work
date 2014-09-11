
var Component_Account_User_List = Class.create(Reflex_Component, {
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			iAccountId	: {
				fnSetter : function(iAccountId) {
					if (this._oFilter) {
						this._oFilter.setFilterValue('account_id', iAccountId);
						this._oComponent.refresh(true);
					}
					return iAccountId;
				}.bind(this)
			},
			iProposalListId	: {},
			iPageSize	 	: {
				fnGetter : function(mValue) {
					return (mValue || Component_Account_User_List.PAGE_SIZE);
				}
			}, 
			oComponent : {
				fnSetter : function() {
					throw "Cannot modify oComponent.";
				},
				fnGetter : function() {
					return this._oComponent;
				}.bind(this)
			},
			bCancelInitialRefresh : {
				fnGetter : function(mValue) {
					return (mValue === false ? false : true);
				}
			}
		}, this.CONFIG || {});
		
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this.NODE.addClassName('component-account-user-list');
	},
	
	_buildUI : function() {
		this.NODE = $T.div();
	},
	
	_syncUI : function(bConstantsLoaded) {
		if (!bConstantsLoaded) {
			// Get constants
			Flex.Constant.loadConstantGroup(Component_Account_User_List.REQUIRED_CONSTANT_GROUPS, this._syncUI.bind(this, true));
			return;
		}
		
		var oDataset 	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, new Reflex_AJAX_Request('Account_User', 'getDataset'));
		var oPagination = new Pagination(null, this.get('iPageSize'), oDataset);
		var oFilter		= new Filter(oDataset, oPagination);
		
		// Add account id filter
		oFilter.addFilter('account_id', {iType: Filter.FILTER_TYPE_VALUE});
		if (this.get('iAccountId')) {
			oFilter.setFilterValue('account_id', this.get('iAccountId'));
		}
		
		var oSort 		= new Sort(oDataset, oPagination, true);
		var oComponent 	= new Component_Dataset_AJAX_Table({
			sTitle				: 'Customer Portal Users',
			oPagination			: oPagination,
			oFilter				: oFilter,
			oSort				: oSort,
			hFields				: this._getFields(),
			onready				: this._onReady.bind(this),
			sExtraClass			: 'component-account-user-list',
			bRefreshable		: true,
			bCancelInitialRefresh : this.get('bCancelInitialRefresh')
		});
		
		oComponent.get('oSection').getAttachmentNode('header-actions').appendChild(
			$T.button({'class': 'icon-button component-account-user-list-new-user', onclick: this._editUser.bind(this, null)},
				$T.img({src: '../admin/img/template/new.png'}),
				$T.span('New User')
			)
		);
		
		this.NODE.appendChild(oComponent.getNode());
		this._oComponent	= oComponent;
		this._oFilter 		= oFilter;
	},
		
	_getFields : function() {
		var hFields = {
			username : {
				sDisplayName 	: 'Username',
				mSortField		: true
			},
			given_name : {
				sDisplayName	: 'Given Name',
				mSortField		: true
			},
			family_name : {
				sDisplayName	: 'Family Name',
				mSortField		: true
			},
			email : {
				sDisplayName 	: 'Email',
				mSortField		: true
			},
			status_id : {
				sDisplayName	: 'Status',
				mSortField		: 'status_name',
				oFilterConfig	: {
					iType	: Filter.FILTER_TYPE_VALUE,
					oOption	: {
						sType		: 'select',
						oDefinition	: {
							fnPopulate : Flex.Constant.getConstantGroupOptions.curry('status')
						}
					},
					fnGetDisplayText : this._getFilterValueDisplayText.bind(this, 'status_id')
				},
				fnCreateCell : this._getCell.bind(this, 'status_id')
			},
			actions : {
				sDisplayName	: '',
				fnCreateCell	: this._getCell.bind(this, 'actions')
			}
		};
		
		return hFields;
	},
	
	_getFilterValueDisplayText : function(sFieldName) {
		var aControls = $A(arguments);
		aControls.shift();
		
		switch (sFieldName) {
			case 'status_id':
				return aControls[0].getElementText();
		}
	},
	
	_getCell : function(sFieldName, oData) {
		switch (sFieldName) {
			case 'status_id':
				return $T.td(oData.status_name);
			
			case 'actions':
				return $T.td(
					$T.img({
						class	: 'pointer', 
						src		: '../admin/img/template/pencil.png', 
						alt		: 'Edit User', 
						title	: 'Edit User', 
						onclick	: this._editUser.bind(this, oData.id)
					})
				);
		}
	},

	_editUser : function(iAccountUserId) {
		var oPopup = Component_Account_User_Edit.createAsPopup({
			iAccountId 		: this.get('iAccountId'),
			iAccountUserId 	: iAccountUserId,
			onready 		: function() {
				oPopup.display();
			},
			oncancel : function() {
				oPopup.hide();
			},
			oncomplete : function() {
				this._oComponent.refresh(true);
				oPopup.hide();
			}.bind(this)
		});
	}
});

Object.extend(Component_Account_User_List, {
	REQUIRED_CONSTANT_GROUPS 	: [],
	PAGE_SIZE 					: 10,

	createAsPopup : function() {
		var	oComponent	= Component_Account_User_List.constructApply($A(arguments)),
		oPopup			= new Reflex_Popup(70);
		oPopup.setTitle('Customer Portal Users');
		oPopup.addCloseButton();
		oPopup.setContent(oComponent.getNode());	
		return oPopup;
	}
});
