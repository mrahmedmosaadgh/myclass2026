-- Check current behaviors
SELECT id, name, type, points 
FROM behaviors 
ORDER BY id;

-- Fix behaviors where type doesn't match points sign
-- If points are negative but type is 'positive', change to 'negative'
UPDATE behaviors 
SET type = 'negative' 
WHERE points < 0 AND type = 'positive';

-- If points are positive but type is 'negative', change to 'positive'
UPDATE behaviors 
SET type = 'positive' 
WHERE points > 0 AND type = 'negative';

-- Ensure all negative behaviors have negative points
UPDATE behaviors 
SET points = -ABS(points) 
WHERE type = 'negative' AND points > 0;

-- Ensure all positive behaviors have positive points
UPDATE behaviors 
SET points = ABS(points) 
WHERE type = 'positive' AND points < 0;

-- Verify the fix
SELECT id, name, type, points,
    CASE 
        WHEN type = 'positive' AND points > 0 THEN '✅ Correct'
        WHEN type = 'negative' AND points < 0 THEN '✅ Correct'
        ELSE '❌ Still wrong'
    END as status
FROM behaviors 
ORDER BY id;
