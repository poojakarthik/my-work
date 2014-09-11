-- 1st way...

CREATE TABLE tag (
	id INT AUTO INCREMENTING,
	name varchar(255) NOT NULL UNIQUE
);

CREATE TABLE tag_group (
	id INT AUTO INCREMENTING,
	tag_group_id INT,
	tag_id INT REFERENCES tag.id
);

ALTER TABLE RatePlan ADD tag_group_id INT REFERENCES tag_group.tag_group_id COMMENT 'Link to a grouping of tags.';

-- 2nd way...
CREATE TABLE tag (
	id INT AUTO INCREMENTING,
	name varchar(255) NOT NULL UNIQUE
);

CREATE TABLE rate_plan_tag (
	id INT AUTO INCREMENTING,
	rate_plan_id INT REFERENCES RatePlan.id 'Link to the Rate Group.',
	tag_id INT REFERENCES tag.id COMMENT 'Link to an individual tag.'
);

CREATE TABLE xxx_tag (
	id INT AUTO INCREMENTING,
	rate_plan_id INT REFERENCES RatePlan.id 'Link to the Rate Group.',
	tag_id INT REFERENCES tag.id COMMENT 'Link to an individual tag.'
);
