-- Set purchase_price_tokens for each 3D model in MapData
-- Run once in pgAdmin (or psql) against your Temadigital_Data_Portal database.

UPDATE public."MapData" SET purchase_price_tokens = 50  WHERE "mapDataID" = 'KK_OSPREY';
UPDATE public."MapData" SET purchase_price_tokens = 30  WHERE "mapDataID" = 'wisma-merdeka';
UPDATE public."MapData" SET purchase_price_tokens = 70  WHERE "mapDataID" = 'kb-3dtiles-lite';
UPDATE public."MapData" SET purchase_price_tokens = 50  WHERE "mapDataID" = 'kolombong-fisheye';
UPDATE public."MapData" SET purchase_price_tokens = 60  WHERE "mapDataID" = 'ppns-ys';
