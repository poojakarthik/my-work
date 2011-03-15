--COLLECTABLE BALANCE

SELECT SUM(balance)
FROM collectable
WHERE account_id = 1000004854
AND balance>0

--CREDIT DISTRIBUTABLE BALANCE


SELECT SUM(x.balance)
FROM
(
Select SUM(p.balance) balance
FROM Account a
LEFT JOIN payment p ON (p.account_id = a.Id and a.Id = 1000004854 and p.payment_nature_id = 1)

UNION

SELECT SUM(ad.balance) balance
FROM Account a LEFT JOIN adjustment ad ON (ad.account_id = a.Id AND a.Id = 1000004854  )
JOIN adjustment_nature an ON (ad.adjustment_nature_id = an.id)
                                JOIN adjustment_type at ON (ad.adjustment_type_id = at.id)
                        JOIN transaction_nature tn ON (at.transaction_nature_id = tn.id AND an.value_multiplier * tn.value_multiplier = -1)

UNION


SELECT SUM(c.balance*-1)
FROM Account a
JOIN collectable c ON (c.account_id = a.Id AND a.Id = 1000004854 and c.balance<0)

) x

--DEBIT DISTRIBUTABLE BALANCE
SELECT SUM(x.balance)
FROM
(
Select   sum(ad.balance) as balance
FROM  Account a JOIN adjustment ad ON (ad.account_id = a.Id AND a.Id = 1000004854  )
JOIN adjustment_nature an ON (ad.adjustment_nature_id = an.id)
                                JOIN adjustment_type at ON (ad.adjustment_type_id = at.id)
                        JOIN transaction_nature tn ON (at.transaction_nature_id = tn.id AND an.value_multiplier * tn.value_multiplier = 1)

UNION

SELECT SUM(p.balance) as balance
FROM Account a JOIN payment p ON (p.account_id = a.Id AND a.Id = 1000004854  AND p.payment_nature_id = (select id from payment_nature where const_name = 'PAYMENT_NATURE_REVERSAL'))
) x