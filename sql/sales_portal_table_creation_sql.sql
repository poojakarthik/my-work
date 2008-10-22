/* First do all the tables representing enumerated types that have no foreign keys
 */


/* vendor table
 */
CREATE TABLE vendor
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_vendor PRIMARY KEY (id),
	CONSTRAINT un_vendor_name UNIQUE (name)
);

/* product_category table
 */
CREATE TABLE product_category
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_product_category PRIMARY KEY (id),
	CONSTRAINT un_product_category_name UNIQUE (name)
);
INSERT INTO product_category (id, name, description)
VALUES 
(1, 'Service', 'Service'),
(2, 'Hardware', 'Hardware');

/* product_type table
 */
CREATE TABLE product_type
(
	id INTEGER NOT NULL,
	product_category_id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,
	module CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_product_type PRIMARY KEY (id),
	CONSTRAINT un_product_type_product_category_id_name UNIQUE (product_category_id, name),
	CONSTRAINT un_product_type_module UNIQUE (module),
	CONSTRAINT fk_product_type_product_category_id FOREIGN KEY (product_category_id) REFERENCES product_category(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
INSERT INTO product_type (id, name, description, product_category_id, module)
VALUES
(1, 'Landline', 'Landline', 1, 'ServiceLandline'),
(2, 'Mobile', 'Mobile', 1, 'ServiceLandline'),
(3, 'ADSL', 'ADSL', 1, 'ServiceADSL'),
(4, 'Inbound', 'Inbound 13/1300/1800', 1, 'ServiceInbound');

/* product_status table
 */
CREATE TABLE product_status
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_product_status PRIMARY KEY (id),
	CONSTRAINT un_product_status_name UNIQUE (name),
);
INSERT INTO product_status (id, name, description)
VALUES
(1, 'Active', 'Active'),
(2, 'Inactive', 'Inactive');

/* product table
 */
CREATE TABLE product
(
	id INTEGER NOT NULL,
	vendor_id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,
	product_type_id INTEGER NOT NULL,
	product_status_id INTEGER NOT NULL,
	reference CHARACTER VARYING NULL,

	CONSTRAINT pk_product PRIMARY KEY (id),
	CONSTRAINT un_product_vendor_id_name UNIQUE (vendor_id, name),
	CONSTRAINT fk_product_product_type_id FOREIGN KEY (product_type_id) REFERENCES product_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_product_product_status_id FOREIGN KEY (product_status_id) REFERENCES product_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_product_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* sale_status table
 */
CREATE TABLE sale_status
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_status PRIMARY KEY (id),
	CONSTRAINT un_sale_status_name UNIQUE (name)
);
INSERT INTO sale_status (id, name, description)
VALUES
(1, 'Submitted', 'Submitted'),
(2, 'Verified', 'Verfied'),
(3, 'Rejected', 'Rejected'),
(4, 'Cancelled', 'Cancelled'),
(5, 'Committed', 'Committed');

/* sale_item_status table
 */
CREATE TABLE sale_item_status
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_item_status PRIMARY KEY (id),
	CONSTRAINT un_sale_item_status_name UNIQUE (name)
);
INSERT INTO sale_item_status (id, name, description)
VALUES
(1, 'Submitted', 'Submitted'),
(2, 'Verified', 'Verfied'),
(3, 'Rejected', 'Rejected'),
(4, 'Cancelled', 'Cancelled'),
(5, 'Committed', 'Committed');


/* sale_type table
 */
CREATE TABLE sale_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_type PRIMARY KEY (id),
	CONSTRAINT un_sale_type_name UNIQUE (name)
);
INSERT INTO sale_type (id, name, description)
VALUES
(1, 'New Customer', 'New Customer'),
(2, 'Existing Customer', 'Existing Customer'),
(3, 'Win Back', 'Win Back');


/* country table
 */
CREATE TABLE country
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,
	code CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_country PRIMARY KEY (id),
	CONSTRAINT un_country_name UNIQUE (name)
);
INSERT INTO country (id, name, description, code)
VALUES
(1, 'Australia', 'Australia', 'AU');

/* state table
 */
CREATE TABLE state
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,
	country_id INTEGER NOT NULL,
	code CHARACTER VARYING NOT NULL, 

	CONSTRAINT pk_state PRIMARY KEY (id),
	CONSTRAINT un_country_id_name UNIQUE (country_id, name),
	CONSTRAINT fk_state_country_id FOREIGN KEY (country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
INSERT INTO state (id, name, description, country_id, code)
VALUES
(1, 'Australian Capital Territory', 'Australian Capital Territory', 1, 'ACT'),
(2, 'New South Wales', 'New South Wales', 1, 'NSW'),
(3, 'Northern Territory', 'Northern Territory', 1, 'NT'),
(4, 'Queensland', 'Queensland', 1, 'QLD'),
(5, 'South Australia', 'South Australia', 1, 'SA'),
(6, 'Tasmania', 'Tasmania', 1, 'TAS'),
(7, 'Victoria', 'Victoria', 1, 'VIC'),
(8, 'Western Australia', 'Western Australia', 1, 'WA');


/* contact_title table
 */
CREATE TABLE contact_title
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_contact_title PRIMARY KEY (id),
	CONSTRAINT un_contact_title_name UNIQUE (name)
);
INSERT INTO contact_title (id, name, description)
VALUES
(1, 'Dr', 'Doctor'),
(2, 'Mr', 'Mister'),
(3, 'Mrs', 'Missus'),
(4, 'Mstr', 'Master'),
(5, 'Miss', 'Miss'),
(6, 'Ms', 'Ms'),
(7, 'Esq', 'Esquire');

/* contact_status table
 */
CREATE TABLE contact_status
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_contact_status PRIMARY KEY (id),
	CONSTRAINT un_contact_status_name UNIQUE (name)
);
INSERT INTO contact_status (id, name, description)
VALUES
(1, 'Active', 'Active'),
(2, 'Archived', 'Archived');

/* contact table
 */
CREATE TABLE contact
(
	id SERIAL,
	contact_title_id INTEGER NULL,
	first_name CHARACTER VARYING NOT NULL,
	middle_names CHARACTER VARYING NULL,
	last_name CHARACTER VARYING NOT NULL,
	position_title CHARACTER VARYING NULL,
	username CHARACTER VARYING NOT NULL,
	password CHARACTER VARYING NOT NULL,
	contact_status_id INTEGER NOT NULL,

	CONSTRAINT pk_contact PRIMARY KEY (id),
	CONSTRAINT fk_contact_contact_title_id FOREIGN KEY (contact_title_id) REFERENCES contact_title(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_contact_contact_status_id FOREIGN KEY (contact_status_id) REFERENCES contact_status(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* contact_method_type
 */
CREATE TABLE contact_method_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_contact_method_type PRIMARY KEY (id),
	CONSTRAINT un_contact_method_type_name UNIQUE (name)
);
INSERT INTO contact_method_type (id, name, description)
VALUES
(1, 'Email', 'Email'),
(2, 'Fax', 'Fax'),
(3, 'Phone', 'Phone'),
(4, 'Mobile', 'Mobile');

/* contact_method table
 */
CREATE TABLE contact_method
(
	id SERIAL,
	contact_id INTEGER NOT NULL,
	contact_method_type_id INTEGER NOT NULL,
	details CHARACTER VARYING NOT NULL,
	is_primary BOOLEAN NOT NULL,
	
	CONSTRAINT pk_contact_method PRIMARY KEY (id),
	CONSTRAINT fk_contact_method_contact_id FOREIGN KEY (contact_id) REFERENCES contact(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_contact_method_contact_method_type_id FOREIGN KEY (contact_method_type_id) REFERENCES contact_method_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* contact_association_type table
 */
CREATE TABLE contact_association_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_contact_association_type PRIMARY KEY (id),
	CONSTRAINT un_contact_association_type_name UNIQUE (name)
);

/* dealer_status table
 */
CREATE TABLE dealer_status
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_dealer_status PRIMARY KEY (id),
	CONSTRAINT un_dealer_status_name UNIQUE (name)
);
INSERT INTO dealer_status (id, name, description)
VALUES
(1, 'Active', 'Active'),
(2, 'Inactive', 'Inactive');


/* dealer table
 */
CREATE TABLE dealer
(
	id SERIAL,
	up_line_manager_dealer_id INTEGER DEFAULT NULL,
	username CHARACTER VARYING NOT NULL,
	password CHARACTER VARYING NOT NULL,
	can_verify BOOLEAN DEFAULT FALSE NOT NULL,
	first_name CHARACTER VARYING NOT NULL,
	last_name CHARACTER VARYING NOT NULL,
	title_id INTEGER NULL,
	business_name CHARACTER VARYING NULL,
	trading_name CHARACTER VARYING NULL,
	abn CHARACTER(11) NULL,
	abn_registered BOOLEAN NULL,
	address_line_1 CHARACTER VARYING NULL,
	address_line_2 CHARACTER VARYING NULL,
	suburb CHARACTER VARYING NULL,
	state_id INTEGER NULL,
	country_id INTEGER NULL,
	post_code CHARACTER(4),
	postal_address_line_1 CHARACTER VARYING NULL,
	postal_address_line_2 CHARACTER VARYING NULL,
	postal_suburb CHARACTER VARYING NULL,
	postal_state_id INTEGER NULL,
	postal_country_id INTEGER NULL,
	postal_post_code CHARACTER(4) NULL,
	phone CHARACTER VARYING(25) NULL,
	mobile CHARACTER VARYING(25) NULL,
	fax CHARACTER VARYING(25) NULL,
	email CHARACTER VARYING NULL,
	commission_scale INTEGER NULL,
	royalty_scale INTEGER NULL,
	bank_account_bsb CHARACTER(6) NULL,
	bank_account_number CHARACTER VARYING(9) NULL,
	bank_account_name CHARACTER VARYING NULL,
	gst_registered BOOLEAN NULL,
	termination_date TIMESTAMP NULL,
	dealer_status_id INTEGER NOT NULL,

	CONSTRAINT pk_dealer PRIMARY KEY (id),
	CONSTRAINT fk_dealer_title_id_contact_title_id FOREIGN KEY (title_id) REFERENCES contact_title(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_state_id FOREIGN KEY (state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_country_id FOREIGN KEY (country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_postal_state_id_state_id FOREIGN KEY (postal_state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_postal_country_id_country_id FOREIGN KEY (postal_country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_dealer_status_id FOREIGN KEY (dealer_status_id) REFERENCES dealer_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_up_line_manager_dealer_id_dealer_id FOREIGN KEY (up_line_manager_dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* dealer_vendor table
 */
CREATE TABLE dealer_vendor
(
	id SERIAL,
	dealer_id INTEGER NOT NULL,
	vendor_id INTEGER NOT NULL,

	CONSTRAINT pk_dealer_vendor PRIMARY KEY (id),
	CONSTRAINT un_dealer_vendor_dealer_id_vendor_id UNIQUE (dealer_id, vendor_id),
	CONSTRAINT fk_dealer_vendor_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_dealer_vendor_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id) ON UPDATE CASCADE ON DELETE CASCADE
);

/* dealer_sale_type table
 */
CREATE TABLE dealer_sale_type
(
	id SERIAL,
	dealer_id INTEGER NOT NULL,
	sale_type_id INTEGER NOT NULL,

	CONSTRAINT pk_dealer_sale_type PRIMARY KEY (id),
	CONSTRAINT un_dealer_sale_type_dealer_id_sale_type_id UNIQUE (dealer_id, sale_type_id),
	CONSTRAINT fk_dealer_sale_type_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_dealer_sale_type_sale_type_id FOREIGN KEY (sale_type_id) REFERENCES sale_type(id) ON UPDATE CASCADE ON DELETE CASCADE
);

/* dealer_product table
 */
CREATE TABLE dealer_product
(
	id SERIAL,
	dealer_id INTEGER NOT NULL,
	product_id INTEGER NOT NULL,

	CONSTRAINT pk_dealer_product PRIMARY KEY (id),
	CONSTRAINT un_dealer_product_dealer_id_product_id UNIQUE (dealer_id, product_id),
	CONSTRAINT fk_dealer_product_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_dealer_product_product_id FOREIGN KEY (product_id) REFERENCES product(id) ON UPDATE CASCADE ON DELETE CASCADE
);

/* sale table
 */
CREATE TABLE sale
(
	id SERIAL,
	sale_type_id INTEGER NOT NULL,
	vendor_id INTEGER NOT NULL,
	created_on TIMESTAMP NOT NULL,
	created_by INTEGER NOT NULL,
	sale_status_id INTEGER NOT NULL,
	existing_account_id INTEGER NULL,
	commission_paid_on TIMESTAMP NULL,

	CONSTRAINT pk_sale PRIMARY KEY (id),
	CONSTRAINT fk_sale_sale_type_id FOREIGN KEY (sale_type_id) REFERENCES sale_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_created_by_dealer_id FOREIGN KEY (created_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_sale_status_id FOREIGN KEY (sale_status_id) REFERENCES sale_status(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* sale_voice_recording table
 */
CREATE TABLE sale_voice_recording
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	dealer_id INTEGER NOT NULL,
	uploaded_on TIMESTAMP NOT NULL,
	recording_created_on TIMESTAMP NOT NULL,
	recording BYTEA NOT NULL,
	description CHARACTER VARYING NULL,
	
	CONSTRAINT pk_sale_voice_recording PRIMARY KEY (id),
	CONSTRAINT fk_sale_voice_recording_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_voice_recording_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
);

/* bill_payment_type table
 */
CREATE TABLE bill_payment_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_bill_payment_type PRIMARY KEY (id),
	CONSTRAINT un_bill_payment_type_name UNIQUE (name)
);
INSERT INTO bill_payment_type (id, name, description)
VALUES
(1, 'Account', 'Account'),
(2, 'Direct Debit', 'Direct Debit');

/* bill_delivery_type table
 */
CREATE TABLE bill_delivery_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_bill_delivery_type PRIMARY KEY (id),
	CONSTRAINT un_bill_delivery_type_name UNIQUE (name)
);
INSERT INTO bill_delivery_type (id, name, description)
VALUES
(1, 'Post', 'Post'),
(2, 'Email', 'Email');


/* sale_account table
 */
CREATE TABLE sale_account
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	business_name CHARACTER VARYING(255) NULL,
	trading_name CHARACTER VARYING(255) NULL,
	abn CHARACTER(11),
	acn CHARACTER(9),
	address_line_1 CHARACTER VARYING(255) NOT NULL,
	address_line_2 CHARACTER VARYING(255) NULL,
	suburb CHARACTER VARYING(255) NULL,
	postcode CHARACTER(4) NOT NULL,
	state_id INTEGER NOT NULL,
	bill_payment_type_id INTEGER NOT NULL,
	bill_delivery_type_id INTEGER NOT NULL,

	CONSTRAINT pk_sale_account PRIMARY KEY (id),
	CONSTRAINT un_sale_account_sale_id UNIQUE (sale_id),
	CONSTRAINT fk_sale_account_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_state_id FOREIGN KEY (state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_bill_payment_type_id FOREIGN KEY (bill_payment_type_id) REFERENCES bill_payment_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_bill_delivery_type_id FOREIGN KEY (bill_delivery_type_id) REFERENCES bill_delivery_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* sale_account_history table
 */
CREATE TABLE sale_account_history
(
	id SERIAL,
	sale_account_id INTEGER NOT NULL,
	changed_on DATETIME NOT NULL,
	changed_by INTEGER NOT NULL,
	bill_payment_type_id INTEGER NOT NULL,
	bill_delivery_type_id INTEGER NOT NULL,

	CONSTRAINT pk_sale_account_history PRIMARY KEY (id),
	CONSTRAINT fk_sale_account_history_sale_account_id FOREIGN KEY (sale_account_id) REFERENCES sale_account(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_history_changed_by_dealer_id FOREIGN KEY (changed_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_history_bill_payment_type_id FOREIGN KEY (bill_payment_type_id) REFERENCES bill_payment_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_history_bill_delivery_type_id FOREIGN KEY (bill_delivery_type_id) REFERENCES bill_delivery_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* direct_debit_type table
 */
CREATE TABLE direct_debit_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_direct_debit_type PRIMARY KEY (id),
	CONSTRAINT un_direct_debit_type_name UNIQUE (name)
);
INSERT INTO direct_debit_type (id, name, description)
VALUES
(1, 'Bank Account', 'Bank Account'),
(2, 'Credit Card', 'Credit Card');

/* sale_account_direct_debit table
 */
CREATE TABLE sale_account_direct_debit
(
	id SERIAL,
	sale_account_id INTEGER NOT NULL,
	direct_debit_type_id INTEGER NOT NULL,

	CONSTRAINT pk_sale_account_direct_debit PRIMARY KEY (id),
	CONSTRAINT un_sale_account_direct_debit_sale_account_id UNIQUE (sale_account_id),
	CONSTRAINT fk_sale_account_direct_debit_sale_account_id FOREIGN KEY (sale_account_id) REFERENCES sale_account(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_direct_debit_direct_debit_type_id FOREIGN KEY (direct_debit_type_id) REFERENCES direct_debit_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* sale_account_direct_debit_bank_account table
 */
CREATE TABLE sale_account_direct_debit_bank_account
(
	id SERIAL,
	sale_account_direct_debit_id INTEGER NOT NULL,
	bank_name CHARACTER VARYING(255) NOT NULL,
	bank_bsb CHARACTER(6) NOT NULL,
	account_number CHARACTER VARYING NOT NULL,
	account_name CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_account_direct_debit_bank_account PRIMARY KEY (id),
	CONSTRAINT un_sale_account_direct_debit_bank_account_sale_account_direct_debit_id UNIQUE (sale_account_direct_debit_id),
	CONSTRAINT fk_sale_account_direct_debit_bank_account_sale_account_direct_debit_id FOREIGN KEY (sale_account_direct_debit_id) REFERENCES sale_account_direct_debit(id) ON UPDATE CASCADE ON DELETE CASCADE
);

/* credit_card_type table
 */
CREATE TABLE credit_card_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,
	valid_lengths CHARACTER VARYING NOT NULL,
	valid_prefixes CHARACTER VARYING NOT NULL,
	cvv_length INTEGER NOT NULL,

	CONSTRAINT pk_credit_card_type PRIMARY KEY (id),
	CONSTRAINT un_credit_card_type_name UNIQUE (name),
	CONSTRAINT chk_format_credit_card_type_valid_lengths CHECK (valid_lengths ~* '^\d{1,2}(,\d{1,2})*$'),
	CONSTRAINT chk_format_credit_card_type_valid_prefixes CHECK (valid_prefixes ~* '^\d{1,2}(,\d{1,2})*$'),
);
INSERT INTO credit_card_type (id, name, description, valid_lengths, valid_prefixes, cvv_length)
VALUES
(1, 'VISA', 'VISA', '13,16', '4', 3),
(2, 'MasterCard', 'MasterCard', '16', '51,52,53,54,55', 3),
(4, 'American Express', 'American Express', '15', '34,37', 4),
(5, 'Diners Club', 'Diners Club', '14', '30,36,38', 3);

/* sale_account_direct_debit_credit_card table
 */
CREATE TABLE sale_account_direct_debit_credit_card
(
	id SERIAL,
	sale_account_direct_debit_credit_card_id INTEGER NOT NULL,
	credit_card_type_id INTEGER NOT NULL,
	card_name CHARACTER VARYING NOT NULL,
	card_number CHARACTER VARYING NOT NULL,
	expiry_month INTEGER NOT NULL,
	expiry_year INTEGER NOT NULL,
	cvv CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_account_direct_debit_credit_card PRIMARY KEY (id),
	CONSTRAINT un_sale_account_direct_debit_credit_card_sale_account_direct_debit_id UNIQUE (sale_account_direct_debit_id),
	CONSTRAINT fk_sale_account_direct_debit_credit_card_sale_account_direct_debit_id FOREIGN KEY (sale_account_direct_debit_id) REFERENCES sale_account_direct_debit(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_direct_debit_credit_card_credit_card_type_id FOREIGN KEY (credit_card_type_id) REFERENCES credit_card_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);


/* contact_sale table
 */
CREATE TABLE contact_sale
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	contact_id INTEGER NOT NULL,
	contact_association_type_id INTEGER NOT NULL,

	CONSTRAINT pk_contact_sale PRIMARY KEY (id),
	CONSTRAINT un_contact_sale_sale_id_contact_id_contact_association_type_id UNIQUE (sale_id, contact_id, contact_association_type_id),
	CONSTRAINT fk_contact_sale_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_contact_sale_contact_id FOREIGN KEY (contact_id) REFERENCES contact(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_contact_sale_contact_association_type_id FOREIGN KEY (contact_association_type_id) REFERENCES contact_association_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);


/* sale_status_history table
 */
CREATE TABLE sale_status_history
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	sale_status_id INTEGER NOT NULL,
	changed_on TIMESTAMP NOT NULL,
	changed_by INTEGER NOT NULL,
	description CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_status_history PRIMARY KEY (id),
	CONSTRAINT fk_sale_status_history_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_status_history_sale_status_id FOREIGN KEY (sale_status_id) REFERENCES sale_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_status_history_changed_by_dealer_id FOREIGN KEY (changed_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* sale_item table
 */
CREATE TABLE sale_item
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	sale_item_status_id INTEGER NOT NULL,
	created_on TIMESTAMP NOT NULL,
	created_by INTEGER NOT NULL,
	product_id INTEGER NOT NULL,
	commission_paid_on TIMESTAMP NULL,

	CONSTRAINT pk_sale_item PRIMARY KEY (id),
	CONSTRAINT fk_sale_item_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_sale_item_status_id FOREIGN KEY (sale_item_status_id) REFERENCES sale_item_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_created_by_dealer_id FOREIGN KEY (created_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_product_id FOREIGN KEY (product_id) REFERENCES product(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* sale_item_status_history table
 */
CREATE TABLE sale_item_status_history
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	sale_item_status_id INTEGER NOT NULL,
	changed_on TIMESTAMP NOT NULL,
	changed_by INTEGER NOT NULL,
	description CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_item_status_history PRIMARY KEY (id),
	CONSTRAINT fk_sale_item_status_history_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_status_history_sale_item_status_id FOREIGN KEY (sale_item_status_id) REFERENCES sale_item_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_status_history_changed_by_dealer_id FOREIGN KEY (changed_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/************************************************************************************************************************/
/************************ START OF sale_item_<product_category>_<product_type> TABLES ***********************************/
/************************************************************************************************************************/
/* Each Product Type that requires specific details defined, will have a table named "sale_item_<ProductCategory>_<ProductType>"
 */


/* sale_item_service_adsl table
 */
CREATE TABLE sale_item_service_adsl
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING(25) NOT NULL,

	CONSTRAINT pk_sale_item_service_adsl PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_adsl_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_adsl_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE
);

/* sale_item_service_inbound table
 */
CREATE TABLE sale_item_service_inbound
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING(25) NOT NULL,
	answer_point CHARACTER VARYING(25) NULL,
	has_complex_configuration BOOLEAN DEFAULT FALSE NOT NULL,
	configuration CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_item_service_inbound PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_inbound_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_inbound_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE
);

/* sale_item_service_mobile table
 */
CREATE TABLE sale_item_service_mobile
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING(25) NOT NULL,
	sim_puk CHARACTER VARYING(50) NULL,
	sim_esn CHARACTER VARYING(15) NULL,
	sim_state_id INTEGER NULL,
	dob TIMESTAMP NULL,
	comments CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_item_service_mobile PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_mobile_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_mobile_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_service_mobile_sim_state_id_state_id FOREIGN KEY (sim_state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

/* landline_type table
 */
CREATE TABLE landline_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_type PRIMARY KEY (id),
	CONSTRAINT un_landline_type_name UNIQUE (name),
);
INSERT INTO landline_type (id, name, description)
VALUES
(1, 'Residential', 'Residential Landline Service'),
(2, 'Business', 'Business Landline Service');

/* landline_service_street_type table
 */
CREATE TABLE landline_service_street_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,
	code CHARACTER VARYING(4) NOT NULL,

	CONSTRAINT pk_landline_service_street_type PRIMARY KEY (id),
	CONSTRAINT un_landline_service_street_type_name UNIQUE (name),
	CONSTRAINT un_landline_service_street_type_code UNIQUE (code)
);
INSERT INTO landline_service_street_type (id, name, description, code)
VALUES
(1, 'Not Required', 'Not Required', 'NR'),
(2, 'Access', 'Access', 'ACCS'),
(3, 'Alley', 'Alley', 'ALLY'),
(4, 'Alleyway', 'Alleyway', 'ALWY'),
(5, 'Amble', 'Amble', 'AMBL'),
(6, 'Anchorage', 'Anchorage', 'ANCG'),
(7, 'Approach', 'Approach', 'APP'),
(8, 'Arcade', 'Arcade', 'ARC'),
(9, 'Arterial', 'Arterial', 'ARTL'),
(10, 'Artery', 'Artery', 'ART'),
(11, 'Avenue', 'Avenue', 'AV'),
(12, 'Avenue2', 'Avenue', 'AVE'),
(13, 'Bank', 'Bank', 'BNK'),
(14, 'Barracks', 'Barracks', 'BRKS'),
(15, 'Basin', 'Basin', 'BASN'),
(16, 'Bay', 'Bay', 'BAY'),
(17, 'Bay2', 'Bay', 'BY'),
(18, 'Beach', 'Beach', 'BCH'),
(19, 'Bend', 'Bend', 'BEND'),
(20, 'Block', 'Block', 'BLK'),
(21, 'Boulevard', 'Boulevard', 'BLV'),
(22, 'Boulevard2', 'Boulevard', 'BVD'),
(23, 'Boundary', 'Boundary', 'BNDY'),
(24, 'Bowl', 'Bowl', 'BWL'),
(25, 'Brace', 'Brace', 'BR'),
(26, 'Brace2', 'Brace', 'BRCE'),
(27, 'Brae', 'Brae', 'BRAE'),
(28, 'Branch', 'Branch', 'BRCH'),
(29, 'Brea', 'Brea', 'BREA'),
(30, 'Break', 'Break', 'BRK'),
(31, 'Bridge', 'Bridge', 'BDGE'),
(32, 'Bridge2', 'Bridge', 'BRDG'),
(33, 'Broadway', 'Broadway', 'BDWY'),
(34, 'Brow', 'Brow', 'BROW'),
(35, 'Bypass', 'Bypass', 'BYPA'),
(36, 'Byway', 'Byway', 'BYWY'),
(37, 'Causeway', 'Causeway', 'CAUS'),
(38, 'Centre', 'Centre', 'CNTR'),
(39, 'Centre2', 'Centre', 'CTR'),
(40, 'Centreway', 'Centreway', 'CNWY'),
(41, 'Chase', 'Chase', 'CH'),
(42, 'Circle', 'Circle', 'CIR'),
(43, 'Circlet', 'Circlet', 'CLT'),
(44, 'Circuit', 'Circuit', 'CCT'),
(45, 'Circuit2', 'Circuit', 'CRCT'),
(46, 'Circus', 'Circus', 'CRCS'),
(47, 'Close', 'Close', 'CL'),
(48, 'Colonnade', 'Colonnade', 'CLDE'),
(49, 'Common', 'Common', 'CMMN'),
(50, 'Community', 'Community', 'COMM'),
(51, 'Concourse', 'Concourse', 'CON'),
(52, 'Connection', 'Connection', 'CNTN'),
(53, 'Copse', 'Copse', 'CPS'),
(54, 'Corner', 'Corner', 'CNR'),
(55, 'Corso', 'Corso', 'CSO'),
(56, 'Course', 'Course', 'CORS'),
(57, 'Court', 'Court', 'CT'),
(58, 'Courtyard', 'Courtyard', 'CTYD'),
(59, 'Cove', 'Cove', 'COVE'),
(60, 'Creek', 'Creek', 'CK'),
(61, 'Creek2', 'Creek', 'CRK'),
(62, 'Crescent', 'Crescent', 'CR'),
(63, 'Crescent2', 'Crescent', 'CRES'),
(64, 'Crest', 'Crest', 'CRST'),
(65, 'Crief', 'Crief', 'CRF'),
(66, 'Cross', 'Cross', 'CRSS'),
(67, 'Crossing', 'Crossing', 'CRSG'),
(68, 'Crossroads', 'Crossroads', 'CRD'),
(69, 'Crossway', 'Crossway', 'COWY'),
(70, 'Cruiseway', 'Cruiseway', 'CUWY'),
(71, 'Cul De Sac', 'Cul De Sac', 'CDS'),
(72, 'Cutting', 'Cutting', 'CTTG'),
(73, 'Dale', 'Dale', 'DALE'),
(74, 'Dell', 'Dell', 'DELL'),
(75, 'Deviation', 'Deviation', 'DEVN'),
(76, 'Dip', 'Dip', 'DIP'),
(77, 'Distributor', 'Distributor', 'DSTR'),
(78, 'Downs', 'Downs', 'DWNS'),
(79, 'Drive', 'Drive', 'DR'),
(80, 'Drive2', 'Drive', 'DRV'),
(81, 'Driveway', 'Driveway', 'DRWY'),
(82, 'Easement', 'Easement', 'EMNT'),
(83, 'Edge', 'Edge', 'EDGE'),
(84, 'Elbow', 'Elbow', 'ELB'),
(85, 'End', 'End', 'END'),
(86, 'Entrance', 'Entrance', 'ENT'),
(87, 'Esplanade', 'Esplanade', 'ESP'),
(88, 'Estate', 'Estate', 'EST'),
(89, 'Expressway', 'Expressway', 'EXP'),
(90, 'Expressway2', 'Expressway', 'EXWY'),
(91, 'Extension', 'Extension', 'EXT'),
(92, 'Extension2', 'Extension', 'EXTN'),
(93, 'Fair', 'Fair', 'FAIR'),
(94, 'Fairway', 'Fairway', 'FAWY'),
(95, 'Fire Track', 'Fire Track', 'FTRK'),
(96, 'Firetrail', 'Firetrail', 'FITR'),
(97, 'Firetrall', 'Firetrall', 'FTRL'),
(98, 'Flat', 'Flat', 'FLAT'),
(99, 'Follow', 'Follow', 'FOWL'),
(100, 'Footway', 'Footway', 'FTWY'),
(101, 'Foreshore', 'Foreshore', 'FSHR'),
(102, 'Formation', 'Formation', 'FORM'),
(103, 'Freeway', 'Freeway', 'FRWY'),
(104, 'Freeway2', 'Freeway', 'FWY'),
(105, 'Front', 'Front', 'FRNT'),
(106, 'Frontage', 'Frontage', 'FRTG'),
(107, 'Gap', 'Gap', 'GAP'),
(108, 'Garden', 'Garden', 'GDN'),
(109, 'Gardens', 'Gardens', 'GDNS'),
(110, 'Gate', 'Gate', 'GTE'),
(111, 'Gates', 'Gates', 'GTES'),
(112, 'Gateway', 'Gateway', 'GTWY'),
(113, 'Glade', 'Glade', 'GLD'),
(114, 'Glen', 'Glen', 'GLEN'),
(115, 'Grange', 'Grange', 'GRA'),
(116, 'Green', 'Green', 'GRN'),
(117, 'Ground', 'Ground', 'GRND'),
(118, 'Grove', 'Grove', 'GR'),
(119, 'Grove2', 'Grove', 'GV'),
(120, 'Gully', 'Gully', 'GLY'),
(121, 'Heath', 'Heath', 'HTH'),
(122, 'Heights', 'Heights', 'HTS'),
(123, 'Highroad', 'Highroad', 'HRD'),
(124, 'Highway', 'Highway', 'HWY'),
(125, 'Hill', 'Hill', 'HILL'),
(126, 'Hillside', 'Hillside', 'HLSD'),
(127, 'House', 'House', 'HSE'),
(128, 'Interchange', 'Interchange', 'INTG'),
(129, 'Intersection', 'Intersection', 'INTN'),
(130, 'Island', 'Island', 'IS'),
(131, 'Junction', 'Junction', 'JNC'),
(132, 'Junction2', 'Junction', 'JNCT'),
(133, 'Key', 'Key', 'KEY'),
(134, 'Knoll', 'Knoll', 'KNLL'),
(135, 'Landing', 'Landing', 'LDG'),
(136, 'Lane', 'Lane', 'L'),
(137, 'Lane2', 'Lane', 'LANE'),
(138, 'Lane3', 'Lane', 'LN'),
(139, 'Laneway', 'Laneway', 'LNWY'),
(140, 'Lees', 'Lees', 'LEES'),
(141, 'Line', 'Line', 'LINE'),
(142, 'Link', 'Link', 'LINK'),
(143, 'Little', 'Little', 'LT'),
(144, 'Location', 'Location', 'LOCN'),
(145, 'Lookout', 'Lookout', 'LKT'),
(146, 'Loop', 'Loop', 'LOOP'),
(147, 'Lower', 'Lower', 'LWR'),
(148, 'Mall', 'Mall', 'MALL'),
(149, 'Marketland', 'Marketland', 'MKLD'),
(150, 'Markettown', 'Markettown', 'MKTN'),
(151, 'Mead', 'Mead', 'MEAD'),
(152, 'Meander', 'Meander', 'MNDR'),
(153, 'Mew', 'Mew', 'MEW'),
(154, 'Mews', 'Mews', 'MEWS'),
(155, 'Motorway', 'Motorway', 'MWY'),
(156, 'Mount', 'Mount', 'MT'),
(157, 'Mountain', 'Mountain', 'MTN'),
(158, 'Nook', 'Nook', 'NOOK'),
(159, 'Outlook', 'Outlook', 'OTLK'),
(160, 'Oval', 'Oval', 'OVAL'),
(161, 'Parade', 'Parade', 'PDE'),
(162, 'Paradise', 'Paradise', 'PDSE'),
(163, 'Park', 'Park', 'PARK'),
(164, 'Park2', 'Park', 'PK'),
(165, 'Parklands', 'Parklands', 'PKLD'),
(166, 'Parkway', 'Parkway', 'PKWY'),
(167, 'Part', 'Part', 'PART'),
(168, 'Pass', 'Pass', 'PASS'),
(169, 'Path', 'Path', 'PATH'),
(170, 'Pathway', 'Pathway', 'PWAY'),
(171, 'Pathway2', 'Pathway', 'PWY'),
(172, 'Peninsula', 'Peninsula', 'PEN'),
(173, 'Piazza', 'Piazza', 'PIAZ'),
(174, 'Pier', 'Pier', 'PR'),
(175, 'Place', 'Place', 'PL'),
(176, 'Plateau', 'Plateau', 'PLAT'),
(177, 'Plaza', 'Plaza', 'PLZA'),
(178, 'Pocket', 'Pocket', 'PKT'),
(179, 'Point', 'Point', 'PNT'),
(180, 'Port', 'Port', 'PORT'),
(181, 'Port2', 'Port', 'PRT'),
(182, 'Promenade', 'Promenade', 'PROM'),
(183, 'Pursuit', 'Pursuit', 'PUR'),
(184, 'Quad', 'Quad', 'QUAD'),
(185, 'Quadrangle', 'Quadrangle', 'QDGL'),
(186, 'Quadrant', 'Quadrant', 'QDRT'),
(187, 'Quay', 'Quay', 'QY'),
(188, 'Quays', 'Quays', 'QYS'),
(189, 'Racecourse', 'Racecourse', 'RCSE'),
(190, 'Ramble', 'Ramble', 'RMBL'),
(191, 'Ramp', 'Ramp', 'RAMP'),
(192, 'Range', 'Range', 'RNGE'),
(193, 'Reach', 'Reach', 'RCH'),
(194, 'Reserve', 'Reserve', 'RES'),
(195, 'Rest', 'Rest', 'REST'),
(196, 'Retreat', 'Retreat', 'RTT'),
(197, 'Return', 'Return', 'RTRN'),
(198, 'Ride', 'Ride', 'RIDE'),
(199, 'Ridge', 'Ridge', 'RDGE'),
(200, 'Ridgeway', 'Ridgeway', 'RGWY'),
(201, 'Right Of Way', 'Right Of Way', 'ROWY'),
(202, 'Ring', 'Ring', 'RING'),
(203, 'Rise', 'Rise', 'RISE'),
(204, 'River', 'River', 'RVR'),
(205, 'Riverway', 'Riverway', 'RVWY'),
(206, 'Riviera', 'Riviera', 'RVRA'),
(207, 'Road', 'Road', 'RD'),
(208, 'Roads', 'Roads', 'RDS'),
(209, 'Roadside', 'Roadside', 'RDSD'),
(210, 'Roadway', 'Roadway', 'RDWY'),
(211, 'Ronde', 'Ronde', 'RNDE'),
(212, 'Rosebowl', 'Rosebowl', 'RSBL'),
(213, 'Rotary', 'Rotary', 'RTY'),
(214, 'Round', 'Round', 'RND'),
(215, 'Route', 'Route', 'RTE'),
(216, 'Row', 'Row', 'ROW'),
(217, 'Rowe', 'Rowe', 'RWE'),
(218, 'Rue', 'Rue', 'RUE'),
(219, 'Run', 'Run', 'RUN'),
(220, 'Section', 'Section', 'SEC'),
(221, 'Service Way', 'Service Way', 'SWY'),
(222, 'Siding', 'Siding', 'SDNG'),
(223, 'Slope', 'Slope', 'SLPE'),
(224, 'Sound', 'Sound', 'SND'),
(225, 'Spur', 'Spur', 'SPUR'),
(226, 'Square', 'Square', 'SQ'),
(227, 'Stairs', 'Stairs', 'STRS'),
(228, 'State Highway', 'State Highway', 'SHWY'),
(229, 'Station', 'Station', 'STN'),
(230, 'Steps', 'Steps', 'STPS'),
(231, 'Stop', 'Stop', 'STOP'),
(232, 'Straight', 'Straight', 'STGT'),
(233, 'Strand', 'Strand', 'STRA'),
(234, 'Street', 'Street', 'ST'),
(235, 'Strip', 'Strip', 'STP'),
(236, 'Strip2', 'Strip', 'STRP'),
(237, 'Subway', 'Subway', 'SBWY'),
(238, 'Tarn', 'Tarn', 'TARN'),
(239, 'Terrace', 'Terrace', 'TCE'),
(240, 'Thoroughfare', 'Thoroughfare', 'THOR'),
(241, 'Tollway', 'Tollway', 'TLWY'),
(242, 'Top', 'Top', 'TOP'),
(243, 'Tor', 'Tor', 'TOR'),
(244, 'Tower', 'Tower', 'TWR'),
(245, 'Towers', 'Towers', 'TWRS'),
(246, 'Track', 'Track', 'TRK'),
(247, 'Trail', 'Trail', 'TRL'),
(248, 'Trailer', 'Trailer', 'TRLR'),
(249, 'Triangle', 'Triangle', 'TRI'),
(250, 'Trunkway', 'Trunkway', 'TKWY'),
(251, 'Turn', 'Turn', 'TURN'),
(252, 'Underpass', 'Underpass', 'UPAS'),
(253, 'Upper', 'Upper', 'UPR'),
(254, 'Vale', 'Vale', 'VALE'),
(255, 'Valley', 'Valley', 'VLY'),
(256, 'Viaduct', 'Viaduct', 'VDCT'),
(257, 'View', 'View', 'VIEW'),
(258, 'Village', 'Village', 'VLGE'),
(259, 'Villas', 'Villas', 'VLLS'),
(260, 'Vista', 'Vista', 'VSTA'),
(261, 'Wade', 'Wade', 'WADE'),
(262, 'Walk', 'Walk', 'WALK'),
(263, 'Walk2', 'Walk', 'WK'),
(264, 'Walkway', 'Walkway', 'WKWY'),
(265, 'Waters', 'Waters', 'WTRS'),
(266, 'Way', 'Way', 'WAY'),
(267, 'Way2', 'Way', 'WY'),
(268, 'West', 'West', 'WEST'),
(269, 'Wharf', 'Wharf', 'WHF'),
(270, 'Wharf2', 'Wharf', 'WHRF'),
(271, 'Wood', 'Wood', 'WOOD'),
(272, 'Wynd', 'Wynd', 'WYND'),
(273, 'Yard', 'Yard', 'YARD'),
(274, 'Yard2', 'Yard', 'YRD');

/* landline_service_address_type_category table
 */
CREATE TABLE landline_service_address_type_category
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_service_address_type_category PRIMARY KEY (id),
	CONSTRAINT un_landline_service_street_type_name UNIQUE (name)
);
INSERT INTO landline_service_address_type_category (id, name, description)
VALUES
()


/************************************************************************************************************************/
/************************** END OF sale_item_<product_category>_<product_type> TABLES ***********************************/
/************************************************************************************************************************/
