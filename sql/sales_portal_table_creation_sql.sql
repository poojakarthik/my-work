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
COMMENT ON TABLE vendor IS 'Vendors used by the sale''s portal';


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
COMMENT ON TABLE product_category IS 'Defines possible product categories such as ''Service'' and ''Hardware''';
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
COMMENT ON TABLE product_type IS 'Defines possible product types such as ''Landline (service)'' and ''Mobile Handset (hardware)''';
COMMENT ON COLUMN product_type.product_category_id IS 'FK into product_category table, defining the product_category that the product_type belongs to';
COMMENT ON COLUMN product_type.module IS 'reference to the code module that facilitates products of this particular product_type';
INSERT INTO product_type (id, name, description, product_category_id, module)
VALUES
(1, 'Landline', 'Landline', 1, 'ServiceLandline'),
(2, 'Mobile', 'Mobile', 1, 'ServiceMobile'),
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
	CONSTRAINT un_product_status_name UNIQUE (name)
);
COMMENT ON TABLE product_status IS 'Defines statuses that products can be set to, such as ''Active'', ''Inactive''';
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
COMMENT ON TABLE product IS 'Defines a purchasable product of a particular product_type, and sold by a particular vendor';
COMMENT ON COlUMN product.vendor_id IS 'FK into vendor table, defining the vendor who carries the product';
COMMENT ON COLUMN product.product_type_id IS 'FK into the product_type table, defining the ''type'' of the product';
COMMENT ON COLUMN product.product_status_id IS 'FK into the product_status table, defining the current status of the product';
COMMENT ON COLUMN product.reference IS 'reference used by any external entities when dealing with this product.  For example, if the product is a service plan, then this value would be the plan''s identifier in the system that models/manages the service';

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
COMMENT ON TABLE sale_status IS 'Defines statuses that sales can be set to, such as ''Submitted'', ''Verified'', ''Rejected''';
INSERT INTO sale_status (id, name, description)
VALUES
(1, 'Submitted', 'Submitted'),
(2, 'Verified', 'Verfied'),
(3, 'Rejected', 'Rejected'),
(4, 'Cancelled', 'Cancelled'),
(5, 'Provisioned', 'Provisioned');

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
COMMENT ON TABLE sale_item_status IS 'Defines statuses that sales can be set to, such as ''Submitted'', ''Verified'', ''Rejected''';
INSERT INTO sale_item_status (id, name, description)
VALUES
(1, 'Submitted', 'Submitted'),
(2, 'Verified', 'Verfied'),
(3, 'Rejected', 'Rejected'),
(4, 'Cancelled', 'Cancelled'),
(5, 'Provisioned', 'Provisioned');


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
COMMENT ON TABLE sale_type IS 'Defines the various types of sales that can be performed, such as ''New Customer'', ''Existing Customer'', ''Win Back''';
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
COMMENT ON TABLE country IS 'Defines various countries';
COMMENT ON COLUMN country.code IS 'Abbreviation of the country''s name';
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
COMMENT ON TABLE state IS 'Defines various geographical states';
COMMENT ON COLUMN state.country_id IS 'FK into the country table, defining the country that the state belongs to';
COMMENT ON cOLUMN state.code IS 'Abbreviation of the state''s name';
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
COMMENT ON TABLE contact_title IS 'Defines the various titles/salutations that a contact can have, such as ''Mr'' or ''Mrs''';
INSERT INTO contact_title (id, name, description)
VALUES
(1, 'Dr', 'Doctor'),
(2, 'Mr', 'Mister'),
(3, 'Mrs', 'Missus'),
(4, 'Mstr', 'Master'),
(5, 'Miss', 'Miss'),
(6, 'Ms', 'Ms'),
(7, 'Esq', 'Esquire'),
(8, 'Prof', 'Professor');

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
COMMENT ON TABLE contact_status IS 'Defines the various statuses that a contact can have, such as ''active'' or ''inactive''';
INSERT INTO contact_status (id, name, description)
VALUES
(1, 'Active', 'Active'),
(2, 'Inactive', 'Inactive');

/* contact table
 */
CREATE TABLE contact
(
	id SERIAL,
	reference_id INTEGER NULL,
	created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	contact_title_id INTEGER NULL,
	first_name CHARACTER VARYING NOT NULL,
	middle_names CHARACTER VARYING NULL,
	last_name CHARACTER VARYING NOT NULL,
	position_title CHARACTER VARYING NULL,
	username CHARACTER VARYING NULL,
	password CHARACTER VARYING NULL,
	contact_status_id INTEGER NOT NULL,
	date_of_birth DATE NULL,
	contact_reference_id INTEGER NULL,

	CONSTRAINT pk_contact PRIMARY KEY (id),
	CONSTRAINT un_contact_reference_id UNIQUE (contact_reference_id),
	CONSTRAINT fk_contact_contact_title_id FOREIGN KEY (contact_title_id) REFERENCES contact_title(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_contact_contact_status_id FOREIGN KEY (contact_status_id) REFERENCES contact_status(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE contact IS 'Defines the contacts, associated with the sales';
COMMENT ON COLUMN contact.reference_id IS 'Identifier used by external entities to uniquely identify this contact. This value would be set to the contact''s contact id in Flex, if this record represented a contact in Flex';
COMMENT ON COLUMN contact.created_on IS 'Time at which the contact record was created';
COMMENT ON COLUMN contact.contact_title_id IS 'FK into the contact_title table, defining the title used by the contact';
COMMENT ON COLUMN contact.position_title IS 'Position title of job held by the contact';
COMMENT ON COLUMN contact.contact_status_id IS 'FK into the contact_status table, defining the current status of the contact';

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
COMMENT ON TABLE contact_method_type IS 'Defines the various methods by which a contact can be contacted, such as ''Email'', ''Phone''';
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
COMMENT ON TABLE contact_method IS 'defines the methods with which a contact can be contacted';
COMMENT ON COLUMN contact_method.contact_id IS 'FK into the contact table';
COMMENT ON COLUMN contact_method.contact_method_type_id IS 'FK into the contact_method_type table';
COMMENT ON COLUMN contact_method.details IS 'The specific details of the contact method, such as a specific phone number or email address';
COMMENT ON COLUMN contact_method.is_primary IS 'TRUE = this contact method is the contact''s primary method of contact.  FALSE = it isn''t';

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
COMMENT ON TABLE contact_association_type IS 'Defines the various association types that can exist between a contact and an entity such as a sale, account or service';
INSERT INTO contact_association_type (id, name, description)
VALUES
(1, 'Primary', 'Primary'),
(2, 'Billing', 'Billing'),
(3, 'Technical', 'Technical');

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
COMMENT ON TABLE dealer_status IS 'Defines the various statuses that a dealer can have assigned to them, such as ''Active'' or ''Inactive''';
INSERT INTO dealer_status (id, name, description)
VALUES
(1, 'Active', 'Active'),
(2, 'Inactive', 'Inactive');


/* dealer table
 */
CREATE TABLE dealer
(
	id SERIAL,
	up_line_id INTEGER DEFAULT NULL,
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
	post_code CHARACTER VARYING,
	postal_address_line_1 CHARACTER VARYING NULL,
	postal_address_line_2 CHARACTER VARYING NULL,
	postal_suburb CHARACTER VARYING NULL,
	postal_state_id INTEGER NULL,
	postal_country_id INTEGER NULL,
	postal_post_code CHARACTER VARYING NULL,
	phone CHARACTER VARYING NULL,
	mobile CHARACTER VARYING NULL,
	fax CHARACTER VARYING NULL,
	email CHARACTER VARYING NULL,
	commission_scale INTEGER NULL,
	royalty_scale INTEGER NULL,
	bank_account_bsb CHARACTER(6) NULL,
	bank_account_number CHARACTER VARYING NULL,
	bank_account_name CHARACTER VARYING NULL,
	gst_registered BOOLEAN NULL,
	termination_date DATE NULL,
	dealer_status_id INTEGER NOT NULL,
	created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

	CONSTRAINT pk_dealer PRIMARY KEY (id),
	CONSTRAINT fk_dealer_title_id_contact_title_id FOREIGN KEY (title_id) REFERENCES contact_title(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_state_id FOREIGN KEY (state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_country_id FOREIGN KEY (country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_postal_state_id_state_id FOREIGN KEY (postal_state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_postal_country_id_country_id FOREIGN KEY (postal_country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_dealer_status_id FOREIGN KEY (dealer_status_id) REFERENCES dealer_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_dealer_up_line_id_dealer_id FOREIGN KEY (up_line_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE dealer IS 'Defines a dealer, who can conduct sales on behalf of the vendors';
COMMENT ON COLUMN dealer.up_line_manager_dealer_id IS 'FK into the dealer table, defining the direct ''up line'' manager of the dealer';
COMMENT ON COLUMN dealer.can_verify IS 'TRUE = dealer can verify sales other than those made by dealers under their management; FALSE = dealer can only verify sales made by dealers under their management';
COMMENT ON COLUMN dealer.title_id IS 'FK into the contact_title table, defining the title/salutation used by the dealer';
COMMENT ON COLUMN dealer.state_id IS 'FK into the state table, defining the state in which the dealer is primarily located';
COMMENT ON COLUMN dealer.country_id IS 'FK into the country table, defining the country in which the dealer is primarily located';
COMMENT ON COLUMN dealer.postal_state_id IS 'FK into the state table, defining the state used by the postal address of the dealer';
COMMENT ON COLUMN dealer.postal_country_id IS 'FK into the country table, defining the country used by the postal address of the dealer';
COMMENT ON COLUMN dealer.dealer_status_id IS 'FK into the dealer_status table, defininng the current status of the dealer';
COMMENT ON COLUMN dealer.created_on IS 'Time at which the dealer record was created';

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
COMMENT ON TABLE dealer_vendor IS 'Defines the various vendors that a dealer can sell on behalf of';
COMMENT ON COLUMN dealer_vendor.dealer_id IS 'FK into the dealer table';
COMMENT ON COLUMN dealer_vendor.vendor_id IS 'FK into the vendor table';

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
COMMENT ON TABLE dealer_sale_type IS 'Defines the various sale_types that a dealer can perform sales of';
COMMENT ON COLUMN dealer_sale_type.dealer_id IS 'FK into the dealer table';
COMMENT ON COLUMN dealer_sale_type.sale_type_id IS 'FK into the sale_type table';

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
COMMENT ON TABLE dealer_product IS 'Defines the various products that a dealer can sell';
COMMENT ON COLUMN dealer_product.dealer_id IS 'FK into the dealer table';
COMMENT ON COLUMN dealer_product.product_id IS 'FK into the product table';


/* sale table
 */
CREATE TABLE sale
(
	id SERIAL,
	sale_type_id INTEGER NOT NULL,
	created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	created_by INTEGER NOT NULL,
	sale_status_id INTEGER NOT NULL,
	commission_paid_on TIMESTAMP NULL,

	CONSTRAINT pk_sale PRIMARY KEY (id),
	CONSTRAINT fk_sale_sale_type_id FOREIGN KEY (sale_type_id) REFERENCES sale_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_created_by_dealer_id FOREIGN KEY (created_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_sale_status_id FOREIGN KEY (sale_status_id) REFERENCES sale_status(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale IS 'Defines a sale';
COMMENT ON COLUMN sale.sale_type_id IS 'FK into the sale_type table, defining the sale_type of the sale';
COMMENT ON COLUMN sale.created_by IS 'FK into the dealer table, defining the dealer who instigated the sale';
COMMENT ON COLUMN sale.sale_status_id IS 'FK into the sale_status table, defining the current status of the sale';
COMMENT ON COLUMN sale.commission_paid_on IS 'Time at which the dealer, associated with the sale, was paid their commission';

/* sale_voice_recording table
 */
CREATE TABLE sale_voice_recording
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	dealer_id INTEGER NOT NULL,
	uploaded_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	recording_created_on TIMESTAMP NOT NULL,
	recording BYTEA NOT NULL,
	description CHARACTER VARYING NULL,
	
	CONSTRAINT pk_sale_voice_recording PRIMARY KEY (id),
	CONSTRAINT fk_sale_voice_recording_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_voice_recording_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_voice_recording IS 'Defines recordings attached to a sale, such as coice authorisations';
COMMENT ON COLUMN sale_voice_recording.sale_id IS 'FK into the sale table';
COMMENT ON COLUMN sale_voice_recording.dealer_id IS 'FK into the dealer table, defining the dealer who uploaded the recording';
COMMENT ON COLUMN sale_voice_recording.uploaded_on IS 'Timestamp at which the recording was uploaded';
COMMENT ON COLUMN sale_voice_recording.recording_created_on IS 'Timestamp at which the recording was originally recorded';
COMMENT ON COLUMN sale_voice_recording.recording IS 'The actual voice recording (binary data)';
COMMENT ON COLUMN sale_voice_recording.description IS 'Description of the voice recording';

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
COMMENT ON TABLE bill_payment_type IS 'Defines the various methods of how a bill can be paid, such as ''Account'' or ''Direct Debit''';
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
COMMENT ON TABLE bill_delivery_type IS 'Defines the various methods of how a bill can be sent to a customer, such as ''Post'' or ''Email''';
INSERT INTO bill_delivery_type (id, name, description)
VALUES
(1, 'Post', 'Post'),
(2, 'Email', 'Email');

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
COMMENT ON TABLE direct_debit_type IS 'Defines the various types of direct debit bill payment';
INSERT INTO direct_debit_type (id, name, description)
VALUES
(1, 'Bank Account', 'Bank Account'),
(2, 'Credit Card', 'Credit Card');


/* sale_account table
 */
CREATE TABLE sale_account
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	vendor_id INTEGER NOT NULL,
	reference_id INTEGER NULL,
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
	direct_debit_type_id INTEGER NULL,
	bill_delivery_type_id INTEGER NOT NULL,

	CONSTRAINT pk_sale_account PRIMARY KEY (id),
	CONSTRAINT un_sale_account_sale_id UNIQUE (sale_id),
	CONSTRAINT fk_sale_account_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_vendor_id FOREIGN KEY (vendor_id) REFERENCES vendor(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_state_id FOREIGN KEY (state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_bill_payment_type_id FOREIGN KEY (bill_payment_type_id) REFERENCES bill_payment_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_bill_delivery_type_id FOREIGN KEY (bill_delivery_type_id) REFERENCES bill_delivery_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_direct_debit_type_id FOREIGN KEY (direct_debit_type_id) REFERENCES direct_debit_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_account IS 'Defines the customer''s account details';
COMMENT ON COLUMN sale_account.sale_id IS 'FK into the sale table, defining the sale that these account details are specific to.  This is a 1 to 1 relationship';
COMMENT ON COLUMN sale_account.vendor_id IS 'FK into the vendor table.  And account can only be associated with the one vendor';
COMMENT ON COLUMN sale_account.reference_id IS 'identifier used by external entities to uniquely identify the account that this sale is with. This value would be set to the customer''s account number in Flex, if this record represented an account in Flex';
COMMENT ON COLUMN sale_account.state_id IS 'FK into the state table.  Forms part of the billing postal address';
COMMENT ON COLUMN sale_account.bill_payment_type_id IS 'FK into the bill_payment_type table, defining how the customer will pay their bills';
COMMENT ON COLUMN sale_account.bill_delivery_type_id IS 'FK into the bill_delivery_type table, defining how the customer will receive their bills';
COMMENT ON COLUMN sale_account.direct_debit_type_id IS 'FK into the direct_debit_type table, defining the type of direct debit, if bill_payment_type_id is Direct Debit.  Otherwise this should be set to NULL';

/* sale_account_history table
 */
CREATE TABLE sale_account_history
(
	id SERIAL,
	sale_account_id INTEGER NOT NULL,
	changed_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by INTEGER NULL,
	bill_payment_type_id INTEGER NOT NULL,
	direct_debit_type_id INTEGER NULL,
	bill_delivery_type_id INTEGER NOT NULL,

	CONSTRAINT pk_sale_account_history PRIMARY KEY (id),
	CONSTRAINT fk_sale_account_history_sale_account_id FOREIGN KEY (sale_account_id) REFERENCES sale_account(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_history_changed_by_dealer_id FOREIGN KEY (changed_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_history_bill_payment_type_id FOREIGN KEY (bill_payment_type_id) REFERENCES bill_payment_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_history_bill_delivery_type_id FOREIGN KEY (bill_delivery_type_id) REFERENCES bill_delivery_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_account_history_direct_debit_type_id FOREIGN KEY (direct_debit_type_id) REFERENCES direct_debit_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_account_history IS 'Records the history of particular properties of the sale_account table, and who changed them and when.  This table will always contain the current state of each record in the sale_account table';
COMMENT ON COLUMN sale_account_history.sale_account_id IS 'FK into the sale_account table';
COMMENT ON COLUMN sale_account_history.changed_on IS 'Time at which the state of the sale_account record was changed. Defaults to NOW()';
COMMENT ON COLUMN sale_account_history.changed_by IS 'FK into dealer table, defining who made the change to the sale_account record.  This can be set to NULL if the change was automatically performed in an effort to sync the record with details external to this database';
COMMENT ON COLUMN sale_account_history.bill_payment_type_id IS 'FK into bill_payment_type table. Reflects the state of sale_account.bill_payment_type_id at time ''changed_on''';
COMMENT ON COLUMN sale_account_history.bill_delivery_type_id IS 'FK into bill_delivery_type table. Reflects the state of sale_account.bill_payment_type_id at time ''changed_on''';
COMMENT ON COLUMN sale_account_history.direct_debit_type_id IS 'FK into the direct_debit_type table. Reflects the state of sale_account.direct_debit_type_id at time ''changed_on''';

/* sale_account_direct_debit_bank_account table
 */
CREATE TABLE sale_account_direct_debit_bank_account
(
	id SERIAL,
	sale_account_id INTEGER NOT NULL,
	bank_name CHARACTER VARYING(255) NOT NULL,
	bank_bsb CHARACTER(6) NOT NULL,
	account_number CHARACTER VARYING NOT NULL,
	account_name CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_account_direct_debit_bank_account PRIMARY KEY (id),
	CONSTRAINT un_sale_account_direct_debit_bank_account_sale_account_id UNIQUE (sale_account_id),
	CONSTRAINT fk_sale_account_direct_debit_bank_account_sale_account_id FOREIGN KEY (sale_account_id) REFERENCES sale_account(id) ON UPDATE CASCADE ON DELETE CASCADE
);
COMMENT ON TABLE sale_account_direct_debit_bank_account IS 'Defines the bank account details used by an account for direct debit bill payment';
COMMENT ON COLUMN sale_account_direct_debit_bank_account.sale_account_id IS 'FK into the sale_account table, that these Direct Debit details relate to';

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
	CONSTRAINT chk_format_credit_card_type_valid_lengths CHECK (valid_lengths ~* E'^\\d{1,2}(,(\\d){1,2})*$'),
	CONSTRAINT chk_format_credit_card_type_valid_prefixes CHECK (valid_prefixes ~* E'^\\d{1,2}(,(\\d){1,2})*$')
);
COMMENT ON TABLE credit_card_type IS 'Defines the various types of credit cards available, such as VISA and AMEX, and their validation properties';
COMMENT ON COLUMN credit_card_type.valid_lengths IS 'comma separated list of valid lengths of the credit card''s number. i.e. ''12,13,14''';
COMMENT ON COLUMN credit_card_type.valid_prefixes IS 'comma separated list of valid prefixes of the credit card''s number. i.e. ''51,52''';
COMMENT ON COLUMN credit_card_type.cvv_length IS 'length in digits, of the card''s cvv number';
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
	sale_account_id INTEGER NOT NULL,
	credit_card_type_id INTEGER NOT NULL,
	card_name CHARACTER VARYING NOT NULL,
	card_number CHARACTER VARYING NOT NULL,
	expiry_month INTEGER NOT NULL,
	expiry_year INTEGER NOT NULL,
	cvv CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_sale_account_direct_debit_credit_card PRIMARY KEY (id),
	CONSTRAINT un_sale_account_direct_debit_credit_card_sale_account_id UNIQUE (sale_account_id),
	CONSTRAINT fk_sale_account_direct_debit_credit_card_sale_account_id FOREIGN KEY (sale_account_id) REFERENCES sale_account(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_account_direct_debit_credit_card_credit_card_type_id FOREIGN KEY (credit_card_type_id) REFERENCES credit_card_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_account_direct_debit_credit_card IS 'Defines the credit card details used by an account for direct debit bill payment';
COMMENT ON COLUMN sale_account_direct_debit_credit_card.sale_account_id IS 'FK into the sale_account table, that these Direct Debit details relate to';
COMMENT ON COLUMN sale_account_direct_debit_credit_card.credit_card_type_id IS 'FK into the credit_card_type table';

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
COMMENT ON TABLE contact_sale IS 'Defines which contacts are associated with which sales';
COMMENT ON COLUMN contact_sale.sale_id IS 'FK into the sale table';
COMMENT ON COLUMN contact_sale.contact_id IS 'FK into the contact table';
COMMENT ON COLUMN contact_sale.contact_association_type_id IS 'FK into the contact_association_type table, defining the type of association that the contact has with the sale';

/* sale_status_history table
 */
CREATE TABLE sale_status_history
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	sale_status_id INTEGER NOT NULL,
	changed_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by INTEGER NOT NULL,
	description CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_status_history PRIMARY KEY (id),
	CONSTRAINT fk_sale_status_history_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_status_history_sale_status_id FOREIGN KEY (sale_status_id) REFERENCES sale_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_status_history_changed_by_dealer_id FOREIGN KEY (changed_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_status_history IS 'Records the history of the status of a sale and who changed the status and when and even why';
COMMENT ON COLUMN sale_status_history.sale_id IS 'FK into the sale table';
COMMENT ON COLUMN sale_status_history.sale_status_id IS 'FK into the sale_status table, defining the status that the sale was changed to at time changed_on';
COMMENT ON COLUMN sale_status_history.changed_on IS 'Time at which the change was made';
COMMENT ON COLUMN sale_status_history.changed_by IS 'FK into dealer table defining who changed the status of the sale';
COMMENT ON COLUMN sale_status_history.description IS 'description as to why the status was changed.  can be NULL';

/* sale_item table
 */
CREATE TABLE sale_item
(
	id SERIAL,
	sale_id INTEGER NOT NULL,
	sale_item_status_id INTEGER NOT NULL,
	created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	created_by INTEGER NOT NULL,
	product_id INTEGER NOT NULL,
	commission_paid_on TIMESTAMP NULL,

	CONSTRAINT pk_sale_item PRIMARY KEY (id),
	CONSTRAINT fk_sale_item_sale_id FOREIGN KEY (sale_id) REFERENCES sale(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_sale_item_status_id FOREIGN KEY (sale_item_status_id) REFERENCES sale_item_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_created_by_dealer_id FOREIGN KEY (created_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_product_id FOREIGN KEY (product_id) REFERENCES product(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_item IS 'Defines the specific products purchased in a sale';
COMMENT ON COLUMN sale_item.sale_id IS 'FK into the sale table';
COMMENT ON COLUMN sale_item.sale_item_status_id IS 'FK into the sale_item_status table, defining the current status of the sale item';
COMMENT ON COLUMN sale_item.created_on IS 'Time at which the item was added to the sale';
COMMENT ON COLUMN sale_item.created_by IS 'FK into the dealer table, defining the dealer who addded the item to the sale';
COMMENT ON COLUMN sale_item.product_id IS 'FK into the product table, defining the product that the sale_item represents';
COMMENT ON COLUMN sale_item.commission_paid_on IS 'Time at which commission was paid to the dealer, regarding this sale item';

/* sale_item_status_history table
 */
CREATE TABLE sale_item_status_history
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	sale_item_status_id INTEGER NOT NULL,
	changed_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by INTEGER NOT NULL,
	description CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_item_status_history PRIMARY KEY (id),
	CONSTRAINT fk_sale_item_status_history_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_status_history_sale_item_status_id FOREIGN KEY (sale_item_status_id) REFERENCES sale_item_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_status_history_changed_by_dealer_id FOREIGN KEY (changed_by) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_item_status_history IS 'Records the history of the status of a sale_item and who changed the status and when and even why';
COMMENT ON COLUMN sale_item_status_history.sale_item_id IS 'FK into the sale_item table';
COMMENT ON COLUMN sale_item_status_history.sale_item_status_id IS 'FK into the sale_item_status table, defining the status that the sale_item was changed to at time changed_on';
COMMENT ON COLUMN sale_item_status_history.changed_on IS 'Time at which the change was made';
COMMENT ON COLUMN sale_item_status_history.changed_by IS 'FK into dealer table defining who changed the status of the sale_item';
COMMENT ON COLUMN sale_item_status_history.description IS 'Description as to why the status was changed.  can be NULL';

/************************************************************************************************************************/
/************************ START OF sale_item_<product_category>_<product_type> TABLES ***********************************/
/************************************************************************************************************************/
/* Each Product Type that requires specific details defined, will have a table named ''sale_item_<ProductCategory>_<ProductType>''
 */

/* sale_item_service_adsl table
 */
CREATE TABLE sale_item_service_adsl
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING NOT NULL,
	address_line_1 CHARACTER VARYING(255) NOT NULL,
	address_line_2 CHARACTER VARYING(255) NULL,
	suburb CHARACTER VARYING(255) NULL,
	postcode CHARACTER(4) NOT NULL,
	state_id INTEGER NOT NULL,

	CONSTRAINT pk_sale_item_service_adsl PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_adsl_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_adsl_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_service_adsl_state_id FOREIGN KEY (state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT chk_format_sale_item_service_adsl_fnn CHECK (fnn ~* E'^0[12378]\\d{8}$')
);
COMMENT ON TABLE sale_item_service_adsl IS 'Defines specific sale_item details for products based on the ''ADSL service'' product_type';
COMMENT ON COLUMN sale_item_service_adsl.sale_item_id IS 'FK into the sale_item table';
COMMENT ON COLUMN sale_item_service_adsl.fnn IS 'FNN of the ADSL service';
COMMENT ON COLUMN sale_item_service_adsl.state_id IS 'FK into state table';

/* sale_item_service_inbound table
 */
CREATE TABLE sale_item_service_inbound
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING NOT NULL,
	answer_point CHARACTER VARYING NULL,
	has_complex_configuration BOOLEAN DEFAULT FALSE NOT NULL,
	configuration CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_item_service_inbound PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_inbound_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_inbound_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT chk_format_sale_item_service_inbound_fnn CHECK (fnn ~* E'^((13\\d{4})|(1[389]00\\d{6}))$')
);
COMMENT ON TABLE sale_item_service_inbound IS 'Defines specific sale_item details for products based on the ''Inbound 13/1300/1800 service'' product_type';
COMMENT ON COLUMN sale_item_service_inbound.sale_item_id IS 'FK into the sale_item table';
COMMENT ON COLUMN sale_item_service_inbound.fnn IS 'FNN of the Inbound service';
COMMENT ON COLUMN sale_item_service_inbound.has_complex_configuration IS 'TRUE = configuration is too complex to define here';
COMMENT ON COLUMN sale_item_service_inbound.configuration IS 'The configuration details of the inbound service, assuming they aren''t too complex to define here';

/* sale_item_service_mobile table
 */
CREATE TABLE sale_item_service_mobile
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING NOT NULL,
	sim_puk CHARACTER VARYING(50) NULL,
	sim_esn CHARACTER VARYING(15) NULL,
	sim_state_id INTEGER NULL,
	dob TIMESTAMP NULL,
	comments CHARACTER VARYING NULL,

	CONSTRAINT pk_sale_item_service_mobile PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_mobile_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_mobile_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_service_mobile_sim_state_id_state_id FOREIGN KEY (sim_state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT chk_format_sale_item_service_mobile_fnn CHECK (fnn ~* E'^04\\d{8}$')
);
COMMENT ON TABLE sale_item_service_mobile IS 'Defines specific sale_item details for products based on the ''Mobile phone service'' product_type';
COMMENT ON COLUMN sale_item_service_mobile.sale_item_id IS 'FK into the sale_item table';
COMMENT ON COLUMN sale_item_service_mobile.fnn IS 'FNN of the mobile service';
COMMENT ON COLUMN sale_item_service_mobile.sim_puk IS 'The SIM''s Personal Unblocking Key code';
COMMENT ON COLUMN sale_item_service_mobile.sim_esn IS 'The SIM''s Electronic Serial Number';
COMMENT ON COLUMN sale_item_service_mobile.sim_state_id IS 'FK into state table';
COMMENT ON COLUMN sale_item_service_mobile.dob IS 'Date of birth of the person purchasing the phone';

/* landline_type table
 */
CREATE TABLE landline_type
(
	id INTEGER NOT NULL,
	name CHARACTER VARYING NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_type PRIMARY KEY (id),
	CONSTRAINT un_landline_type_name UNIQUE (name)
);
COMMENT ON TABLE landline_type IS 'Defines the various types of landline services, such as ''Business'' and ''Residential''';
INSERT INTO landline_type (id, name, description)
VALUES
(1, 'Residential', 'Residential Landline Service'),
(2, 'Business', 'Business Landline Service');

/* landline_service_street_type table
 */
CREATE TABLE landline_service_street_type
(
	id INTEGER NOT NULL,
	code CHARACTER VARYING(4) NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_service_street_type PRIMARY KEY (id),
	CONSTRAINT un_landline_service_street_type_code UNIQUE (code)
);
COMMENT ON TABLE landline_service_street_type IS 'Defines the various street type codes used by the WeBill system when provisioning landline services';
INSERT INTO landline_service_street_type (id, code, description)
VALUES
(1, 'NR', 'Not Required'),
(2, 'ACCS', 'Access'),
(3, 'ALLY', 'Alley'),
(4, 'ALWY', 'Alleyway'),
(5, 'AMBL', 'Amble'),
(6, 'ANCG', 'Anchorage'),
(7, 'APP', 'Approach'),
(8, 'ARC', 'Arcade'),
(9, 'ARTL', 'Arterial'),
(10, 'ART', 'Artery'),
(11, 'AV', 'Avenue'),
(12, 'AVE', 'Avenue'),
(13, 'BNK', 'Bank'),
(14, 'BRKS', 'Barracks'),
(15, 'BASN', 'Basin'),
(16, 'BAY', 'Bay'),
(17, 'BY', 'Bay'),
(18, 'BCH', 'Beach'),
(19, 'BEND', 'Bend'),
(20, 'BLK', 'Block'),
(21, 'BLV', 'Boulevard'),
(22, 'BVD', 'Boulevard'),
(23, 'BNDY', 'Boundary'),
(24, 'BWL', 'Bowl'),
(25, 'BR', 'Brace'),
(26, 'BRCE', 'Brace'),
(27, 'BRAE', 'Brae'),
(28, 'BRCH', 'Branch'),
(29, 'BREA', 'Brea'),
(30, 'BRK', 'Break'),
(31, 'BDGE', 'Bridge'),
(32, 'BRDG', 'Bridge'),
(33, 'BDWY', 'Broadway'),
(34, 'BROW', 'Brow'),
(35, 'BYPA', 'Bypass'),
(36, 'BYWY', 'Byway'),
(37, 'CAUS', 'Causeway'),
(38, 'CNTR', 'Centre'),
(39, 'CTR', 'Centre'),
(40, 'CNWY', 'Centreway'),
(41, 'CH', 'Chase'),
(42, 'CIR', 'Circle'),
(43, 'CLT', 'Circlet'),
(44, 'CCT', 'Circuit'),
(45, 'CRCT', 'Circuit'),
(46, 'CRCS', 'Circus'),
(47, 'CL', 'Close'),
(48, 'CLDE', 'Colonnade'),
(49, 'CMMN', 'Common'),
(50, 'COMM', 'Community'),
(51, 'CON', 'Concourse'),
(52, 'CNTN', 'Connection'),
(53, 'CPS', 'Copse'),
(54, 'CNR', 'Corner'),
(55, 'CSO', 'Corso'),
(56, 'CORS', 'Course'),
(57, 'CT', 'Court'),
(58, 'CTYD', 'Courtyard'),
(59, 'COVE', 'Cove'),
(60, 'CK', 'Creek'),
(61, 'CRK', 'Creek'),
(62, 'CR', 'Crescent'),
(63, 'CRES', 'Crescent'),
(64, 'CRST', 'Crest'),
(65, 'CRF', 'Crief'),
(66, 'CRSS', 'Cross'),
(67, 'CRSG', 'Crossing'),
(68, 'CRD', 'Crossroads'),
(69, 'COWY', 'Crossway'),
(70, 'CUWY', 'Cruiseway'),
(71, 'CDS', 'Cul De Sac'),
(72, 'CTTG', 'Cutting'),
(73, 'DALE', 'Dale'),
(74, 'DELL', 'Dell'),
(75, 'DEVN', 'Deviation'),
(76, 'DIP', 'Dip'),
(77, 'DSTR', 'Distributor'),
(78, 'DWNS', 'Downs'),
(79, 'DR', 'Drive'),
(80, 'DRV', 'Drive'),
(81, 'DRWY', 'Driveway'),
(82, 'EMNT', 'Easement'),
(83, 'EDGE', 'Edge'),
(84, 'ELB', 'Elbow'),
(85, 'END', 'End'),
(86, 'ENT', 'Entrance'),
(87, 'ESP', 'Esplanade'),
(88, 'EST', 'Estate'),
(89, 'EXP', 'Expressway'),
(90, 'EXWY', 'Expressway'),
(91, 'EXT', 'Extension'),
(92, 'EXTN', 'Extension'),
(93, 'FAIR', 'Fair'),
(94, 'FAWY', 'Fairway'),
(95, 'FTRK', 'Fire Track'),
(96, 'FITR', 'Firetrail'),
(97, 'FTRL', 'Firetrall'),
(98, 'FLAT', 'Flat'),
(99, 'FOWL', 'Follow'),
(100, 'FTWY', 'Footway'),
(101, 'FSHR', 'Foreshore'),
(102, 'FORM', 'Formation'),
(103, 'FRWY', 'Freeway'),
(104, 'FWY', 'Freeway'),
(105, 'FRNT', 'Front'),
(106, 'FRTG', 'Frontage'),
(107, 'GAP', 'Gap'),
(108, 'GDN', 'Garden'),
(109, 'GDNS', 'Gardens'),
(110, 'GTE', 'Gate'),
(111, 'GTES', 'Gates'),
(112, 'GTWY', 'Gateway'),
(113, 'GLD', 'Glade'),
(114, 'GLEN', 'Glen'),
(115, 'GRA', 'Grange'),
(116, 'GRN', 'Green'),
(117, 'GRND', 'Ground'),
(118, 'GR', 'Grove'),
(119, 'GV', 'Grove'),
(120, 'GLY', 'Gully'),
(121, 'HTH', 'Heath'),
(122, 'HTS', 'Heights'),
(123, 'HRD', 'Highroad'),
(124, 'HWY', 'Highway'),
(125, 'HILL', 'Hill'),
(126, 'HLSD', 'Hillside'),
(127, 'HSE', 'House'),
(128, 'INTG', 'Interchange'),
(129, 'INTN', 'Intersection'),
(130, 'IS', 'Island'),
(131, 'JNC', 'Junction'),
(132, 'JNCT', 'Junction'),
(133, 'KEY', 'Key'),
(134, 'KNLL', 'Knoll'),
(135, 'LDG', 'Landing'),
(136, 'L', 'Lane'),
(137, 'LANE', 'Lane'),
(138, 'LN', 'Lane'),
(139, 'LNWY', 'Laneway'),
(140, 'LEES', 'Lees'),
(141, 'LINE', 'Line'),
(142, 'LINK', 'Link'),
(143, 'LT', 'Little'),
(144, 'LOCN', 'Location'),
(145, 'LKT', 'Lookout'),
(146, 'LOOP', 'Loop'),
(147, 'LWR', 'Lower'),
(148, 'MALL', 'Mall'),
(149, 'MKLD', 'Marketland'),
(150, 'MKTN', 'Markettown'),
(151, 'MEAD', 'Mead'),
(152, 'MNDR', 'Meander'),
(153, 'MEW', 'Mew'),
(154, 'MEWS', 'Mews'),
(155, 'MWY', 'Motorway'),
(156, 'MT', 'Mount'),
(157, 'MTN', 'Mountain'),
(158, 'NOOK', 'Nook'),
(159, 'OTLK', 'Outlook'),
(160, 'OVAL', 'Oval'),
(161, 'PDE', 'Parade'),
(162, 'PDSE', 'Paradise'),
(163, 'PARK', 'Park'),
(164, 'PK', 'Park'),
(165, 'PKLD', 'Parklands'),
(166, 'PKWY', 'Parkway'),
(167, 'PART', 'Part'),
(168, 'PASS', 'Pass'),
(169, 'PATH', 'Path'),
(170, 'PWAY', 'Pathway'),
(171, 'PWY', 'Pathway'),
(172, 'PEN', 'Peninsula'),
(173, 'PIAZ', 'Piazza'),
(174, 'PR', 'Pier'),
(175, 'PL', 'Place'),
(176, 'PLAT', 'Plateau'),
(177, 'PLZA', 'Plaza'),
(178, 'PKT', 'Pocket'),
(179, 'PNT', 'Point'),
(180, 'PORT', 'Port'),
(181, 'PRT', 'Port'),
(182, 'PROM', 'Promenade'),
(183, 'PUR', 'Pursuit'),
(184, 'QUAD', 'Quad'),
(185, 'QDGL', 'Quadrangle'),
(186, 'QDRT', 'Quadrant'),
(187, 'QY', 'Quay'),
(188, 'QYS', 'Quays'),
(189, 'RCSE', 'Racecourse'),
(190, 'RMBL', 'Ramble'),
(191, 'RAMP', 'Ramp'),
(192, 'RNGE', 'Range'),
(193, 'RCH', 'Reach'),
(194, 'RES', 'Reserve'),
(195, 'REST', 'Rest'),
(196, 'RTT', 'Retreat'),
(197, 'RTRN', 'Return'),
(198, 'RIDE', 'Ride'),
(199, 'RDGE', 'Ridge'),
(200, 'RGWY', 'Ridgeway'),
(201, 'ROWY', 'Right Of Way'),
(202, 'RING', 'Ring'),
(203, 'RISE', 'Rise'),
(204, 'RVR', 'River'),
(205, 'RVWY', 'Riverway'),
(206, 'RVRA', 'Riviera'),
(207, 'RD', 'Road'),
(208, 'RDS', 'Roads'),
(209, 'RDSD', 'Roadside'),
(210, 'RDWY', 'Roadway'),
(211, 'RNDE', 'Ronde'),
(212, 'RSBL', 'Rosebowl'),
(213, 'RTY', 'Rotary'),
(214, 'RND', 'Round'),
(215, 'RTE', 'Route'),
(216, 'ROW', 'Row'),
(217, 'RWE', 'Rowe'),
(218, 'RUE', 'Rue'),
(219, 'RUN', 'Run'),
(220, 'SEC', 'Section'),
(221, 'SWY', 'Service Way'),
(222, 'SDNG', 'Siding'),
(223, 'SLPE', 'Slope'),
(224, 'SND', 'Sound'),
(225, 'SPUR', 'Spur'),
(226, 'SQ', 'Square'),
(227, 'STRS', 'Stairs'),
(228, 'SHWY', 'State Highway'),
(229, 'STN', 'Station'),
(230, 'STPS', 'Steps'),
(231, 'STOP', 'Stop'),
(232, 'STGT', 'Straight'),
(233, 'STRA', 'Strand'),
(234, 'ST', 'Street'),
(235, 'STP', 'Strip'),
(236, 'STRP', 'Strip'),
(237, 'SBWY', 'Subway'),
(238, 'TARN', 'Tarn'),
(239, 'TCE', 'Terrace'),
(240, 'THOR', 'Thoroughfare'),
(241, 'TLWY', 'Tollway'),
(242, 'TOP', 'Top'),
(243, 'TOR', 'Tor'),
(244, 'TWR', 'Tower'),
(245, 'TWRS', 'Towers'),
(246, 'TRK', 'Track'),
(247, 'TRL', 'Trail'),
(248, 'TRLR', 'Trailer'),
(249, 'TRI', 'Triangle'),
(250, 'TKWY', 'Trunkway'),
(251, 'TURN', 'Turn'),
(252, 'UPAS', 'Underpass'),
(253, 'UPR', 'Upper'),
(254, 'VALE', 'Vale'),
(255, 'VLY', 'Valley'),
(256, 'VDCT', 'Viaduct'),
(257, 'VIEW', 'View'),
(258, 'VLGE', 'Village'),
(259, 'VLLS', 'Villas'),
(260, 'VSTA', 'Vista'),
(261, 'WADE', 'Wade'),
(262, 'WALK', 'Walk'),
(263, 'WK', 'Walk'),
(264, 'WKWY', 'Walkway'),
(265, 'WTRS', 'Waters'),
(266, 'WAY', 'Way'),
(267, 'WY', 'Way'),
(268, 'WEST', 'West'),
(269, 'WHF', 'Wharf'),
(270, 'WHRF', 'Wharf'),
(271, 'WOOD', 'Wood'),
(272, 'WYND', 'Wynd'),
(273, 'YARD', 'Yard'),
(274, 'YRD', 'Yard');

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
COMMENT ON TABLE landline_service_address_type_category IS 'Defines the various Address Type Categories used by the WeBill system when provisioning landline services';
INSERT INTO landline_service_address_type_category (id, name, description)
VALUES
(1, 'Standard', 'Standard'),
(2, 'Postal', 'Postal'),
(3, 'Allotment', 'Allotment');

/* landline_service_address_type table
 */
CREATE TABLE landline_service_address_type
(
	id INTEGER NOT NULL,
	code CHARACTER VARYING(3) NOT NULL,
	description CHARACTER VARYING NOT NULL,
	landline_service_address_type_category_id INTEGER NOT NULL,
	
	CONSTRAINT pk_landline_service_address_type PRIMARY KEY (id),
	CONSTRAINT un_landline_service_address_type_code UNIQUE (code),
	CONSTRAINT fk_landline_service_address_type_landline_service_address_type_category_id FOREIGN KEY (landline_service_address_type_category_id) REFERENCES landline_service_address_type_category(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE landline_service_address_type IS 'Defines the various Address Type codes used by the WeBill system when provisioning landline services';
COMMENT ON COLUMN landline_service_address_type.landline_service_address_type_category_id IS 'FK into the landline_service_address_type_category table';
INSERT INTO landline_service_address_type (id, code, description, landline_service_address_type_category_id)
VALUES
(1, 'LOT', 'Allotment', 3),
(2, 'POB', 'PO Box', 2),
(3, 'PO', 'Post Office', 2),
(4, 'BAG', 'Private Bag', 2),
(5, 'CMA', 'Community Mail Agent', 2),
(6, 'CMB', 'Community Mail Bag', 2),
(7, 'PB', 'Private Bag', 2),
(8, 'GPO', 'GPO Box', 2),
(9, 'MS', 'Mail Service', 2),
(10, 'RMD', 'Rural Mail Delivery', 2),
(11, 'RMB', 'Roadside Mail Bag / Box', 2),
(12, 'LB', 'Locked Bag', 2),
(13, 'RMS', 'Roadside Mail Service', 2),
(14, 'RD', 'Roadside Delivery', 2),
(15, 'APT', 'Apartment', 1),
(16, 'ATC', 'ATCO Portable Dwelling', 1),
(17, 'BMT', 'Basement', 1),
(18, 'BAY', 'Bay', 1),
(19, 'BT', 'Berth', 1),
(20, 'BLK', 'Block', 1),
(21, 'BG', 'Building', 1),
(22, 'BLG', 'Building', 1),
(23, 'CRV', 'Caravan', 1),
(24, 'CPO', 'Care PO', 1),
(25, 'CB', 'Chambers', 1),
(26, 'CX', 'Complex', 1),
(27, 'CTG', 'Cottage', 1),
(28, 'CN', 'Counter', 1),
(29, 'DUP', 'Duplex', 1),
(30, 'ENT', 'Entrance', 1),
(31, 'FY', 'Factory', 1),
(32, 'FAR', 'Farm', 1),
(33, 'FL', 'Flat', 1),
(34, 'FLA', 'Flat', 1),
(35, 'FLT', 'Flat', 1),
(36, 'FLR', 'Floor', 1),
(37, 'GT', 'Gate', 1),
(38, 'GTE', 'Gate', 1),
(39, 'G', 'Ground / Ground Floor', 1),
(40, 'HG', 'Hangar', 1),
(41, 'HSE', 'House', 1),
(42, 'IG', 'Igloo', 1),
(43, 'JT', 'Jetty', 1),
(44, 'KSK', 'Kiosk', 1),
(45, 'LN', 'Lane', 1),
(46, 'LV', 'Level', 1),
(47, 'LVL', 'Level', 1),
(48, 'LG', 'Lower Ground Floor', 1),
(49, 'MST', 'Maisonette', 1),
(50, 'M', 'Mezzanine', 1),
(51, 'OF', 'Office', 1),
(52, 'OFC', 'Office', 1),
(53, 'PHS', 'Penthouse', 1),
(54, 'PR', 'Pier', 1),
(55, 'RM', 'Room', 1),
(56, 'RSD', 'Roadside Delivery', 1),
(57, 'SD', 'Shed', 1),
(58, 'SHD', 'Shed', 1),
(59, 'SHP', 'Shop', 1),
(60, 'SP', 'Shop', 1),
(61, 'SIT', 'Site', 1),
(62, 'SL', 'Stall', 1),
(63, 'STL', 'Stall', 1),
(64, 'STU', 'Studio', 1),
(65, 'STE', 'Suite', 1),
(66, 'TR', 'Tier', 1),
(67, 'TW', 'Tower', 1),
(68, 'TWR', 'Tower', 1),
(69, 'THS', 'Townhouse', 1),
(70, 'UN', 'Unit', 1),
(71, 'UNT', 'Unit', 1),
(72, 'UG', 'Upper Ground Floor', 1),
(73, 'VIL', 'Villa', 1),
(74, 'WRD', 'Ward', 1),
(75, 'WF', 'Wharf', 1);

/* landline_service_street_type_suffix table
 */
CREATE TABLE landline_service_street_type_suffix
(
	id INTEGER NOT NULL,
	code CHARACTER VARYING(2) NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_service_street_type_suffix PRIMARY KEY (id),
	CONSTRAINT un_landline_service_street_type_suffix_code UNIQUE (code)
);
COMMENT ON TABLE landline_service_street_type_suffix IS 'Defines the various Street Type Suffix codes used by the WeBill system when provisioning landline services';
INSERT INTO landline_service_street_type_suffix (id, code, description)
VALUES
(1, 'CN', 'Central'),
(2, 'E', 'East'),
(3, 'EX', 'Extension'),
(4, 'L', 'Lower'),
(5, 'N', 'North'),
(6, 'NE', 'North East'),
(7, 'NW', 'North West'),
(8, 'S', 'South'),
(9, 'SE', 'South East'),
(10, 'SW', 'South West'),
(11, 'U', 'Upper'),
(12, 'W', 'West');

/* landline_service_state table
 */
CREATE TABLE landline_service_state
(
	id INTEGER NOT NULL,
	code CHARACTER VARYING(3) NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_service_state PRIMARY KEY (id),
	CONSTRAINT un_landline_service_state_code UNIQUE (code)
);
COMMENT ON TABLE landline_service_state IS 'Defines the various Australian State codes used by the WeBill system when provisioning landline services';
INSERT INTO landline_service_state (id, code, description)
VALUES
(1, 'ACT', 'Australian Capital Territory'),
(2, 'NSW', 'New South Wales'),
(3, 'NT', 'Northern Territory'),
(4, 'QLD', 'Queensland'),
(5, 'SA', 'South Australia'),
(6, 'TAS', 'Tasmania'),
(7, 'VIC', 'Victoria'),
(8, 'WA', 'Western Australia');

/* landline_end_user_title table
 */
CREATE TABLE landline_end_user_title
(
	id INTEGER NOT NULL,
	code CHARACTER VARYING(4) NOT NULL,
	description CHARACTER VARYING NOT NULL,

	CONSTRAINT pk_landline_end_user_title PRIMARY KEY (id),
	CONSTRAINT un_landline_end_user_title_code UNIQUE (code)
);
COMMENT ON TABLE landline_end_user_title IS 'Defines the various ''end user title'' codes used by the WeBill system when provisioning landline services';
INSERT INTO landline_end_user_title (id, code, description)
VALUES
(1, 'DR', 'Dr'),
(2, 'MSTR', 'Master'),
(3, 'MISS', 'Miss'),
(4, 'MR', 'Mr'),
(5, 'MRS', 'Mrs'),
(6, 'MS', 'Ms'),
(7, 'PROF', 'Professor');

/* sale_item_service_landline table
 */
CREATE TABLE sale_item_service_landline
(
	id SERIAL,
	sale_item_id INTEGER NOT NULL,
	fnn CHARACTER VARYING NOT NULL,
	is_indial_100 BOOLEAN NOT NULL,
	has_extension_level_billing BOOLEAN NOT NULL,
	landline_type_id INTEGER NOT NULL,
	bill_name CHARACTER VARYING(30) NOT NULL,
	bill_address_line_1 CHARACTER VARYING(30) NOT NULL,
	bill_address_line_2 CHARACTER VARYING(30) NULL,
	bill_locality CHARACTER VARYING(23) NOT NULL,
	bill_postcode CHARACTER(4) NOT NULL,
	landline_service_address_type_id INTEGER NULL,
	service_address_type_number INTEGER NULL,
	service_address_type_suffix CHARACTER VARYING(2) NULL,
	service_street_number_start INTEGER NULL,
	service_street_number_end INTEGER NULL,
	service_street_number_suffix CHARACTER(1) NULL,
	service_street_name CHARACTER VARYING(30) NULL,
	landline_service_street_type_id INTEGER NULL,
	landline_service_street_type_suffix_id INTEGER NULL,
	service_property_name CHARACTER VARYING(30) NULL,
	service_locality CHARACTER VARYING(30) NOT NULL,
	landline_service_state_id INTEGER NOT NULL,
	service_postcode CHARACTER(4) NOT NULL,
	
	CONSTRAINT pk_sale_item_service_landline PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_landline_sale_item_id UNIQUE (sale_item_id),
	CONSTRAINT fk_sale_item_service_landline_sale_item_id FOREIGN KEY (sale_item_id) REFERENCES sale_item(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_service_landline_landline_type_id FOREIGN KEY (landline_type_id) REFERENCES landline_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_service_landline_landline_service_address_type_id FOREIGN KEY (landline_service_address_type_id) REFERENCES landline_service_address_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_service_landline_landline_service_street_type_id FOREIGN KEY (landline_service_street_type_id) REFERENCES landline_service_street_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_service_landline_landline_service_street_type_suffix_id FOREIGN KEY (landline_service_street_type_suffix_id) REFERENCES landline_service_street_type_suffix(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT fk_sale_item_service_landline_landline_service_state_id FOREIGN KEY (landline_service_state_id) REFERENCES landline_service_state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT chk_format_sale_item_service_landline_fnn CHECK (fnn ~* E'^0[12378]\\d{8}$')

);
COMMENT ON TABLE sale_item_service_landline IS 'Defines specific sale_item details for products based on the ''Landline service'' product_type.  All the address details are a requirement of the WeBill provisioning system';
COMMENT ON COLUMN sale_item_service_landline.sale_item_id IS 'FK into the sale_item table';
COMMENT ON COLUMN sale_item_service_landline.fnn IS 'FNN used by the landline service';
COMMENT ON COLUMN sale_item_service_landline.is_indial_100 IS 'TRUE = landline is an indial100 service (the service represents 100 fnns). FALSE = it''s not';
COMMENT ON COLUMN sale_item_service_landline.has_extension_level_billing IS 'Billing is grouped by extension when displayed on an invoice.  This is only applicable to indial100 landline services';
COMMENT ON COLUMN sale_item_service_landline.landline_type_id IS 'FK into the landline_type table, defining the type of landline.  (Business or Residential)';
COMMENT ON COLUMN sale_item_service_landline.bill_name IS 'The name of the bill.  This is a requirement of the WeBill provisioning system';
COMMENT ON COLUMN sale_item_service_landline.bill_address_line_1 IS 'Billing address line 1.  This is a requirement of the WeBill provisioning system';
COMMENT ON COLUMN sale_item_service_landline.bill_address_line_2 IS 'Billing address line 2.  This is a requirement of the WeBill provisioning system';
COMMENT ON COLUMN sale_item_service_landline.bill_locality IS 'Billing address locality (suburb/town?).  This is a requirement of the WeBill provisioning system';
COMMENT ON COLUMN sale_item_service_landline.bill_postcode IS 'Billing address postcode.  This is a requirement of the WeBill provisioning system';
COMMENT ON COLUMN sale_item_service_landline.landline_service_address_type_id IS 'FK into the landline_service_address_type table';
COMMENT ON COLUMN sale_item_service_landline.landline_service_street_type_id IS 'FK into the landline_service_street_type table';
COMMENT ON COLUMN sale_item_service_landline.landline_service_street_type_suffix_id IS 'FK into the landline_service_street_type_suffix table';
COMMENT ON COLUMN sale_item_service_landline.landline_service_state_id IS 'FK into the landline_service_state table';

/* sale_item_service_landline_business table
 */
CREATE TABLE sale_item_service_landline_business
(
	id SERIAL,
	sale_item_service_landline_id INTEGER NOT NULL,
	company_name CHARACTER VARYING(50) NOT NULL,
	abn CHARACTER(11) NOT NULL,
	trading_name CHARACTER VARYING(50) NULL,
	
	CONSTRAINT pk_sale_item_service_landline_business PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_landline_business_sale_item_service_landline_id UNIQUE (sale_item_service_landline_id),
	CONSTRAINT fk_sale_item_service_landline_business_sale_item_service_landline_id FOREIGN KEY (sale_item_service_landline_id) REFERENCES sale_item_service_landline(id) ON UPDATE CASCADE ON DELETE CASCADE
);
COMMENT ON TABLE sale_item_service_landline_business IS 'Details required of WeBill for a landline service of landline_type ''Business''';
COMMENT ON COLUMN sale_item_service_landline_business.sale_item_service_landline_id IS 'FK into sale_item_service_landline table';

/* sale_item_service_landline_residential table
 */
CREATE TABLE sale_item_service_landline_residential
(
	id SERIAL,
	sale_item_service_landline_id INTEGER NOT NULL,
	landline_end_user_title_id INTEGER NOT NULL,
	end_user_given_name CHARACTER VARYING(30) NOT NULL,
	end_user_family_name CHARACTER VARYING(50) NOT NULL,
	end_user_dob DATE NOT NULL,
	end_user_employer CHARACTER VARYING(30) NULL,
	end_user_occupation CHARACTER VARYING(30) NULL,

	CONSTRAINT pk_sale_item_service_landline_residential PRIMARY KEY (id),
	CONSTRAINT un_sale_item_service_landline_residential_sale_item_service_landline_id UNIQUE (sale_item_service_landline_id),
	CONSTRAINT fk_sale_item_service_landline_residential_sale_item_service_landline_id FOREIGN KEY (sale_item_service_landline_id) REFERENCES sale_item_service_landline(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sale_item_service_landline_residential_landline_end_user_title_id FOREIGN KEY (landline_end_user_title_id) REFERENCES landline_end_user_title(id) ON UPDATE CASCADE ON DELETE RESTRICT
);
COMMENT ON TABLE sale_item_service_landline_residential IS 'Details required of WeBill for a landline service of landline_type ''Residential''';
COMMENT ON COLUMN sale_item_service_landline_residential.sale_item_service_landline_id IS 'FK into sale_item_service_landline table';

/************************************************************************************************************************/
/************************** END OF sale_item_<product_category>_<product_type> TABLES ***********************************/
/************************************************************************************************************************/
