"use strict";
var     H               = require('fw/dom/factory'), // HTML
        Class           = require('fw/class'),
        Component       = require('fw/component'),
        Alert = require('fw/component/popup/alert'),
        xhrrequest      = require('fw/xhrrequest'),
        Popup           = require('fw/component/popup'),
        Form            = require('fw/component/form'),
        Text			= require('fw/component/control/text'),
        jsonForm = require('json-form');

var     self = new Class({
        'extends' : Component,

        construct : function() {
			this.CONFIG = Object.extend({
				aConstraints : {},
				constraintData: {}
			}, this.CONFIG || {});

			// Call the parent constructor
			this._super.apply(this, arguments);

			// Class specific to our component
			this.NODE.addClassName('flex-component-report-constraint-add');
		},

		_buildUI : function() {
			this.NODE = 
				H.section(
					this._oForm = H.form(
						H.label({class: 'flex-component-report-constraint-add-name'},
							H.span({class: 'flex-component-report-constraint-add-name-label'}, 'Name'),
							H.input({type: 'text', name: 'name', maxlength: '256', required: ''})
						),
						H.label({class: 'flex-component-report-constraint-add-type'},
							H.span({class: 'flex-component-report-constraint-add-type-label'}, 'Type'),
							this._typeSelect = H.select({type:'select', name: 'type', required: ''})
						),
						H.label({class: 'flex-component-report-constraint-add-sourcequery'},
							H.span({class: 'flex-component-report-constraint-add-sourcequery-label'}, 'Source Query'),
							H.textarea({name: 'sourcequery', maxlength: '10000'})
						),
						H.label({class: 'flex-component-report-constraint-add-validationregex'},
							H.span({class: 'flex-component-report-constraint-add-validationregex-label'}, 'Regex Pattern'),
							H.input({type: 'text', name: 'validationregex', maxlength: '200'})
						),
						H.label({class: 'flex-component-report-constraint-add-placeholder'},
							H.span({class: 'flex-component-report-constraint-add-placeholder-label'}, 'Hint Text'),
							H.input({type: 'text', name: 'placeholder', maxlength: '100'})
						),
						H.fieldset({class: 'flex-component-report-add-buttonset'},
							H.button({type: 'button', name: 'save', onclick: this._save.bind(this)}, 'Save')
						)
					),
					H.table({class: 'reflex highlight-rows'},
						H.caption(
							H.div({id: 'caption_bar', class: 'caption_bar'},
								H.div({id: "caption_title", class: "caption_title"}, 'Constraint List'),
								H.div({id: 'caption_options', class: 'caption_options'})
							)
						),
						H.thead(
							H.tr({class: 'First'},
								H.th({align: 'Left'}, 'Name'),
								H.th({align: 'Left'}, 'Type'),
								H.th({align: 'Left'}, 'Action')
							)
						),
						this._constraintList = H.tbody({class: 'flex-component-report-constraint-list'})
					)
				);
			
			//this.NODE = this._oForm.getNode();
		},

		_syncUI : function() {
			if (!this._bInitialised || !this._onReady) {
				this._typeSelect.appendChild(H.option('Free Text'));
				this._typeSelect.appendChild(H.option('Data Source'));
				
				if(this.get('aConstraints')) {
					// Clear existing list
					this._constraintList = H.tbody({class: 'flex-component-report-constraint-list'});
					// Populate existing constraints
					
				}
				this._onReady();	
			}
		},

		_remove : function() {
			// Remove constraint from list.
		},

		_add : function(formData) {
			// Add constraint to the list
			if(!this.get('aConstraints')) {
				var aNewArray = Array();
				this.set('aConstraints',aNewArray);
			}
			var aConstraints = this.get('aConstraints');
				aConstraints.push(formData);
		},

		_save : function(event) {
			var formData = jsonForm(this._oForm);
			this._add(formData);
			this.set('constraintData',formData);
			this.fire('complete');
        },

        statics : {
			createAsPopup : function() {
				var oComponent      = self.applyAsConstructor($A(arguments)),
					oPopup                  = new Popup({
					sExtraClass             : 'css-class-name',
					sTitle                  : 'Edit Report Constraints',
					sIconURI                : './img/template/pencil.png',
					bCloseButton    : true
				},
				oComponent.getNode()
			);
			return oPopup;
		}
	}
});

return self;
