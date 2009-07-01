/* Complete rollback scripts (don't forget to delete the records from the database_version table, relating to these rollouts)
Note that this has to be done in reverse order.  

Rollback 2 should only be run after rollback 3 has been run
Rollback 1 should only be run after rollback 2 has been run

*/

--For rollout 3
DELETE FROM action_type WHERE name IN ('Adjustment Requested', 'Adjustment Request Outcome', 'Recurring Adjustment Requested', 'Recurring Adjustment Request Outcome');

--For rollout 2
ALTER TABLE RecurringCharge ADD COLUMN Archived TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = Is Archived; 0 = IS Not Archived' AFTER TotalRecursions;
UPDATE RecurringCharge
SET Archived = 1
WHERE recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'CANCELLED')
OR (recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'COMPLETED') AND Continuable = 1);


--For rollout 1
DROP TABLE IF EXISTS recurring_charge_status;
DROP TABLE IF EXISTS charge_recurring_charge;
ALTER TABLE RecurringCharge DROP FOREIGN KEY fk_recurring_charge_recurring_charge_status_id, DROP COLUMN recurring_charge_status_id;



