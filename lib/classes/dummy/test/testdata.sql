--
-- Dumping data for table 'collection_event_type'
--

INSERT INTO collection_event_type (id, name, description, system_name, collection_event_type_implementation_id, collection_event_invocation_id, status_id) VALUES

(11, 'Call', 'Call', 'CALL', 3, NULL, 1);

--
-- Dumping data for table 'collection_event'
--

INSERT INTO collection_event (id, name, description, collection_event_type_id, collection_event_invocation_id, status_id) VALUES
(1, 'Friendly Reminder', 'Friendly Reminder', 1, 1, 1),
(2, 'Overdue Notice', 'Overdue Notice', 1, 1, 1),
(3, 'Late Fees List', 'Late Fees List', 2, 1, 1),
(4, 'Call', 'Call', 11, 2, 1),
(5, 'TDC', 'TDC', 7, 1, 1),
(6, 'OCA', 'OCA', 6, 2, 1),
(7, 'Late Fee', 'Late Fee', 8, 1, 1),
(8, 'Barring', 'Barring', 5, 1, 1),
(9, 'Unbarring', 'Unbarring', 10, 1, 1),
(10, 'Medium Severity', 'Medium Severity', 4, 1, 1),
(11, 'Exit Collections', 'Exit Collections', 9, 1, 1),
(12, 'High Severity', 'Hight Severity', 4, 1, 1);

INSERT INTO account_collection_event_history (id, account_id, collectable_id, collection_event_id, collection_scenario_collection_event_id, scheduled_datetime, completed_datetime, completed_employee_id, account_collection_event_status_id) VALUES
(1, 1000008822, 37766, 2, NULL, '2011-01-11 11:55:38', '2011-01-11 11:55:38', 237, 2);



--
-- Dumping data for table 'collection_event_action'
--

INSERT INTO collection_event_action (id, collection_event_id, action_type_id) VALUES
(1, 4, 5);


--
-- Dumping data for table 'collection_event_report'
--

INSERT INTO collection_event_report (id, collection_event_id, report_sql, email_notification_id, collection_event_report_output_id) VALUES
(1, 3, 'Select whatever', 4, 3);



--
--
-- Dumping data for table 'collection_severity'
--

INSERT INTO collection_severity (id, name, description, status_id) VALUES

(2, 'Medium', 'Medium', 1),
(3, 'High', 'High', 1);




--
--
-- Dumping data for table 'collection_severity'
--

INSERT INTO collection_severity (id, name, description, status_id) VALUES

(2, 'Medium', 'Medium', 1),
(3, 'High', 'High', 1);

--
-- Dumping data for table 'collection_event_severity'
--

INSERT INTO collection_event_severity (id, collection_event_id, collection_severity_id) VALUES
(1, 10, 2),
(2, 12, 3);




--
-- Dumping data for table 'collection_promise'
--

INSERT INTO collection_promise (id, account_id,  collection_promise_reason_id, created_datetime, created_employee_id, completed_datetime, collection_promise_completion_id, completed_employee_id) VALUES
(1, 1000008822,  1, '2011-01-01 00:00:00', 237, NULL, NULL, NULL),
(2, 1000008822, 1, '2011-01-01 00:00:00', 237, '2011-01-02 00:00:00', 3, 237),
(3, 1000004847,  1, '2011-01-01 00:00:00', 0, NULL, NULL, NULL);



--
-- Dumping data for table 'collection_promise_instalment'
--

INSERT INTO collection_promise_instalment (id, collection_promise_id, due_date, amount) VALUES
(1, 1, '2011-01-05', '25.0000'),
(2, 1, '2011-01-10', '25.0000'),
(3, 3, '2011-01-10', '100.0000'),
(4, 3, '2011-01-10', '100.0000'),
(5, 3, '2011-01-10', '100.0000');

--
-- Dumping data for table 'collection_scenario'
--

INSERT INTO collection_scenario (id, name, description, day_offset, status_id, threshold_percentage, threshold_amount, initial_collection_severity_id, allow_automatic_unbar) VALUES

(2, 'Promise Unmet', 'Promise Unmet', 0, 1, 3, '19', NULL, 1);

--
-- Dumping data for table 'collection_scenario_collection_event'
--

INSERT INTO collection_scenario_collection_event (id, collection_scenario_id, collection_event_id, collection_event_invocation_id, day_offset, prerequisite_collection_scenario_collection_event_id) VALUES
(4, 1, 1, NULL, 5, NULL),
(5, 1, 2, NULL, 5, 4),
(6, 1, 3, NULL, 5, 5),
(7, 2, 1, NULL, 2, NULL),
(8, 2, 5, NULL, 2, 7),
(9, 2, 6, NULL, 2, 8);


--
-- Dumping data for table `collection_scenario_system_config`
--

INSERT INTO `collection_scenario_system_config` (`id`, `collection_scenario_system_id`, `collection_scenario_id`, `start_datetime`, `end_datetime`) VALUES
(1, 1, 2, '2013-01-10 00:00:00', '2013-01-10 00:00:00');