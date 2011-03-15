/*SELECT a.Id, c.id, c.balance, c.due_date
FROM Account a
JOIN collectable c ON (a.Id = c.account_id
                        AND c.balance <> c.amount AND c.amount <>0
                        AND c.due_date <  (SELECT max(due_date)
                                            FROM collectable c2
                                            where c2.account_id = c.account_id
                                            AND ((c2.balance = c2.amount) OR (c2.balance <> 0 AND c2.balance <> c2.amount))
                                            AND c2.amount <> 0)
                        )
*/


SELECT    c.account_id, c.amount,
                      MIN(IF(c.balance = 0, c.due_date, NULL))                           AS min_fully_paid,
                      MIN(IF(c.balance > 0 AND c.balance < c.amount, c.due_date, NULL))  AS min_partially_paid,
                      MIN(IF(c.balance = c.amount, c.due_date, NULL))                    AS min_fully_unpaid,
                      MAX(IF(c.balance = 0, c.due_date, NULL))                           AS max_fully_paid,
                      MAX(IF(c.balance > 0 AND c.balance < c.amount, c.due_date, NULL))  AS max_partially_paid,
                      MAX(IF(c.balance = c.amount, c.due_date, NULL))                    AS max_fully_unpaid
            FROM      collectable c
            where     c.amount <> 0
            GROUP BY  c.account_id
            HAVING   min_partially_paid != max_partially_paid
                     OR max_fully_paid > min_partially_paid
                     OR min_fully_paid < max_fully_unpaid
                      OR min_fully_paid < max_partially_paid
                      OR min_partially_paid < max_fully_unpaid
                      OR min_fully_unpaid < max_partially_paid
                      OR min_fully_unpaid < max_fully_paid
