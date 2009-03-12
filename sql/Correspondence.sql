CREATE TABLE correspondence_method (
	id int,
	name character varying -- Email, Post, Phone Call, FAX, Voice SMS, Text SMS, Meeting, Instant Message, Website.
);

CREATE TABLE correspondence_direction (
	id int,
	name character varying -- Incoming, Outgoing, Both.
);

CREATE TABLE correspondence_type (
	id int,
	name character varying -- Invoice Reminder, Invoice, 
);

CREATE TABLE account_correspondence_history (
	id int,
	account_id references Account(Id),
	invoice_id references Invoice(Id),
	created_on timestamp,
	created_by int references employee(Id),
	sent_on timestamp,
	sent_by int references employee(Id),
	contact_id int references contact(id),
	correspondence_method_id references correspondence_method(id),
	correspondence_type_id references correspondence_type(id),
	content blob, -- ????
	direction_id int references correspondence_direction(id)
);


-- Merge account_letter_log with this.
