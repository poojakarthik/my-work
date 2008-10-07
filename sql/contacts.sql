create table contact_salutation (
	id serial,
	name character varying
);

insert into contact_salutation (name) values ('Dr');
insert into contact_salutation (name) values ('Miss');
insert into contact_salutation (name) values ('Mr');
insert into contact_salutation (name) values ('Mrs');

create table contact (
	id serial,
	salutation smallint references contact_salutation(id),
	first_name character varying,
	middle_name character varying,
	last_name character varying,
	position_title character varying
	username character varying,
	password character varying,
);

create table contact_method_type (
	id serial,
	name character varying
);

insert into contact_method_type (name) values ('E-mail');
insert into contact_method_type (name) values ('Fax');
insert into contact_method_type (name) values ('Phone');
insert into contact_method_type (name) values ('Mobile');

create table contact_method (
	id serial,
	contact_id int references contact(id),
	details character varying,
	contact_method_type_id smallint references contact_method_type(id),
	primary boolean
);

create table contact_type (
	id serial,
	name character varying
);
insert into contact_type (name) values ('Primary');
insert into contact_type (name) values ('Billing');
insert into contact_type (name) values ('Technical');

create table contact_account_association (
	id serial,
	contact_id int references contact(id),
	account_id int,
	contact_type_id smallint references contact_type(id)
);

create table contact_service_association (
	id serial,
	contact_id int references contact(id),
	service_id int,
	contact_type_id smallint references contact_type(id)
);

create table contact_ticket_association (
	id serial,
	contact_id int references contact(id),
	ticket_id int,
	contact_type_id smallint references contact_type(id)
);
