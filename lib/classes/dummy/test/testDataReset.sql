
DELETE FROM account_status_history where id > 15115 AND account in (1000004777, 1000004849, 1000176068);
DELETE FROM account_oca_referral;
DELETE FROM account_collection_event_history;
INSERT INTO account_collection_event_history (id, account_id, collectable_id, collection_event_id, collection_scenario_collection_event_id, scheduled_datetime, completed_datetime, completed_employee_id, account_collection_event_status_id) VALUES
(1, 1000004777, 277736, 1, 4, '2011-01-11 11:55:38', '2011-01-11 11:55:38', 237, 2);

update Account set collection_severity_id = 1 where Id in (1000004777, 1000004849, 1000176068);

delete from Charge where Id >=  2849927 and Account in (1000004777, 1000004849, 1000176068);

delete from InvoiceRun where Id in (select invoice_run_id from Invoice where Id>3002656581 AND Account in (1000004777, 1000004849, 1000176068));

delete from Invoice where Id > 3002656581 and Account in (1000004777, 1000004849, 1000176068);

update Service S set Status = 400 where Id in (71, 72, 43045,71111, 88513, 71114);

delete from Service where Id > 88569 and Account in (1000004777, 1000004849,1000176068);

delete from ServiceRatePlan where Id > 133998 and Service in (71, 72, 43045,71111, 88513, 71114);

update collectable set collection_promise_id = null;

DELETE FROM collection_promise_instalment;

DELETE FROM collection_promise;

INSERT INTO collection_promise (id, account_id,  collection_promise_reason_id, created_datetime, created_employee_id, completed_datetime, collection_promise_completion_id, completed_employee_id) VALUES
(1, 1000004777,  1, '2011-01-01 00:00:00', 237, NULL, NULL, NULL),
(2, 1000004777, 1, '2011-01-01 00:00:00', 237, '2011-01-02 00:00:00', 3, 237),
(3, 1000004849,  1, '2011-01-01 00:00:00', 0, NULL, NULL, NULL);

update collectable set collection_promise_id = 1 where id = 651383;
update collectable set collection_promise_id = 3 where id = 651384;

--
-- Dumping data for table 'collection_promise_instalment'
--

INSERT INTO collection_promise_instalment (id, collection_promise_id, due_date, amount) VALUES
(1, 1, '2011-01-05', '25.0000'),
(2, 1, '2011-01-10', '25.0000'),
(3, 3, '2011-01-10', '100.0000'),
(4, 3, '2011-01-10', '100.0000'),
(5, 3, '2011-01-10', '100.0000');



update collectable set balance = 0 where account_id not in (1000004777, 1000004849);

update Account set collection_severity_id = 1 where Id in (1000004777, 1000004849);
