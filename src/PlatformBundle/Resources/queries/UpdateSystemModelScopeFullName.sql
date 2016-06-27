UPDATE AppGearPlatformModelScope sm
SET sm.`fullName` = sm.name;

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id` AND s1.`id` != 10
SET sm.`fullName` = CONCAT(s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id AND s2.`id` != 10
SET sm.`fullName` = CONCAT(s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id AND s3.`id` != 10
SET sm.`fullName` = CONCAT(s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id
  JOIN AppGearPlatformModelScope s4 ON s4.`id` = s3.parent_id AND s4.`id` != 10
SET sm.`fullName` = CONCAT(s4.name, '\\', s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id
  JOIN AppGearPlatformModelScope s4 ON s4.`id` = s3.parent_id
  JOIN AppGearPlatformModelScope s5 ON s5.`id` = s4.parent_id AND s5.`id` != 10
SET sm.`fullName` = CONCAT(s5.name, '\\', s4.name, '\\', s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id
  JOIN AppGearPlatformModelScope s4 ON s4.`id` = s3.parent_id
  JOIN AppGearPlatformModelScope s5 ON s5.`id` = s4.parent_id
  JOIN AppGearPlatformModelScope s6 ON s6.`id` = s5.parent_id AND s6.`id` != 10
SET sm.`fullName` = CONCAT(s6.name, '\\', s5.name, '\\', s4.name, '\\', s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id
  JOIN AppGearPlatformModelScope s4 ON s4.`id` = s3.parent_id
  JOIN AppGearPlatformModelScope s5 ON s5.`id` = s4.parent_id
  JOIN AppGearPlatformModelScope s6 ON s6.`id` = s5.parent_id
  JOIN AppGearPlatformModelScope s7 ON s7.`id` = s6.parent_id AND s7.`id` != 10
SET sm.`fullName` = CONCAT(s7.name, '\\', s6.name, '\\', s5.name, '\\', s4.name, '\\', s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id
  JOIN AppGearPlatformModelScope s4 ON s4.`id` = s3.parent_id
  JOIN AppGearPlatformModelScope s5 ON s5.`id` = s4.parent_id
  JOIN AppGearPlatformModelScope s6 ON s6.`id` = s5.parent_id
  JOIN AppGearPlatformModelScope s7 ON s7.`id` = s6.parent_id
  JOIN AppGearPlatformModelScope s8 ON s8.`id` = s7.parent_id AND s8.`id` != 10
SET sm.`fullName` = CONCAT(s8.name, '\\', s7.name, '\\', s6.name, '\\', s5.name, '\\', s4.name, '\\', s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE AppGearPlatformModelScope sm
  JOIN AppGearPlatformModelScope s1 ON s1.`id` = sm.`parent_id`
  JOIN AppGearPlatformModelScope s2 ON s2.`id` = s1.parent_id
  JOIN AppGearPlatformModelScope s3 ON s3.`id` = s2.parent_id
  JOIN AppGearPlatformModelScope s4 ON s4.`id` = s3.parent_id
  JOIN AppGearPlatformModelScope s5 ON s5.`id` = s4.parent_id
  JOIN AppGearPlatformModelScope s6 ON s6.`id` = s5.parent_id
  JOIN AppGearPlatformModelScope s7 ON s7.`id` = s6.parent_id
  JOIN AppGearPlatformModelScope s8 ON s8.`id` = s7.parent_id
  JOIN AppGearPlatformModelScope s9 ON s9.`id` = s8.parent_id AND s9.`id` != 10
SET sm.`fullName` = CONCAT(s9.name, '\\', s8.name, '\\', s7.name, '\\', s6.name, '\\', s5.name, '\\', s4.name, '\\', s3.name, '\\', s2.name, '\\', s1.`name`, '\\', sm.name);

UPDATE
    AppGearPlatformModel sm
    JOIN AppGearPlatformModelScope smc ON smc.id = sm.`scope_id`
SET
  sm.fullName = TRIM(BOTH '\\' FROM CONCAT(smc.`fullName`, '\\', sm.name));