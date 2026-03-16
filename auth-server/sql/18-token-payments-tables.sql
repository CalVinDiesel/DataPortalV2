-- Token & payment support for 3DHub Data Portal
-- Run this once on Temadigital_Data_Portal in pgAdmin (or via psql).
-- Depends on:
--   - public."DataPortalUsers" (09-data-portal-users-table.sql)
--   - public."ClientUploads"    (03-admin-tables-postgres.sql)
--   - public."MapData"          (Temadigital_Data_Portal_PostgreSQL.sql)

------------------------------
-- 1) TokenWallet
------------------------------

CREATE TABLE IF NOT EXISTS public."TokenWallet" (
  id               SERIAL PRIMARY KEY,
  user_email       VARCHAR(255) NOT NULL,
  token_balance    NUMERIC(12,2) NOT NULL DEFAULT 0,
  stripe_customer_id VARCHAR(255),
  created_at       TIMESTAMPTZ DEFAULT NOW(),
  updated_at       TIMESTAMPTZ DEFAULT NOW()
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_tokenwallet_user_email
  ON public."TokenWallet"(LOWER(user_email));

COMMENT ON TABLE public."TokenWallet" IS 'Per-user token wallet balance for Data Portal payments (uploads and 3D model purchases).';
COMMENT ON COLUMN public."TokenWallet".token_balance IS 'Current token balance (1 token = 2 MYR by default).';

------------------------------
-- 2) TokenTransactions
------------------------------

CREATE TABLE IF NOT EXISTS public."TokenTransactions" (
  id                 SERIAL PRIMARY KEY,
  user_email         VARCHAR(255) NOT NULL,
  amount             NUMERIC(12,2) NOT NULL, -- positive = credit, negative = debit
  balance_after      NUMERIC(12,2),
  type               VARCHAR(32)  NOT NULL,  -- 'topup' | 'upload_charge' | 'purchase_3d' | 'refund' | 'admin_adjust'
  reference_type     VARCHAR(32),            -- 'client_upload' | 'map_data_purchase' | 'stripe_payment'
  reference_id       VARCHAR(128),
  stripe_payment_intent_id VARCHAR(255),
  myr_amount         NUMERIC(10,2),
  metadata           JSONB,
  created_at         TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_tokentx_user_email
  ON public."TokenTransactions"(LOWER(user_email), created_at DESC);

CREATE INDEX IF NOT EXISTS idx_tokentx_reference
  ON public."TokenTransactions"(reference_type, reference_id);

COMMENT ON TABLE public."TokenTransactions" IS 'Audit log of all token movements (top-ups, upload charges, 3D model purchases, refunds).';

------------------------------
-- 3) StripePayments
------------------------------

CREATE TABLE IF NOT EXISTS public."StripePayments" (
  id                        SERIAL PRIMARY KEY,
  user_email                VARCHAR(255) NOT NULL,
  stripe_payment_intent_id  VARCHAR(255) NOT NULL,
  stripe_customer_id        VARCHAR(255),
  amount_myr                NUMERIC(10,2) NOT NULL,
  tokens_credited           NUMERIC(12,2) NOT NULL,
  status                    VARCHAR(32) NOT NULL DEFAULT 'pending', -- 'pending' | 'succeeded' | 'failed' | 'refunded'
  created_at                TIMESTAMPTZ DEFAULT NOW(),
  updated_at                TIMESTAMPTZ DEFAULT NOW()
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_stripepayments_pi
  ON public."StripePayments"(stripe_payment_intent_id);

COMMENT ON TABLE public."StripePayments" IS 'Stripe PaymentIntents used to top up token wallets.';

------------------------------
-- 4) MapDataPurchases (who bought which 3D model)
------------------------------

CREATE TABLE IF NOT EXISTS public."MapDataPurchases" (
  id                   SERIAL PRIMARY KEY,
  user_email           VARCHAR(255) NOT NULL,
  map_data_id          VARCHAR(64)  NOT NULL, -- references public."MapData"("mapDataID") logically
  tokens_paid          NUMERIC(12,2) NOT NULL,
  token_transaction_id INTEGER,
  purchased_at         TIMESTAMPTZ DEFAULT NOW()
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_mapdatapurchases_user_model
  ON public."MapDataPurchases"(LOWER(user_email), map_data_id);

CREATE INDEX IF NOT EXISTS idx_mapdatapurchases_model
  ON public."MapDataPurchases"(map_data_id);

COMMENT ON TABLE public."MapDataPurchases" IS 'Records of which users purchased which 3D models (MapData entries).';

------------------------------
-- 5) Column additions on existing tables
------------------------------

-- 5a) DataPortalUsers: optional Stripe customer id for linking wallets to Stripe
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'public' AND table_name = 'DataPortalUsers' AND column_name = 'stripe_customer_id'
  ) THEN
    ALTER TABLE public."DataPortalUsers"
      ADD COLUMN stripe_customer_id VARCHAR(255);
  END IF;
END$$;

-- 5b) MapData: per-model purchase price in tokens (NULL = not purchasable)
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'public' AND table_name = 'MapData' AND column_name = 'purchase_price_tokens'
  ) THEN
    ALTER TABLE public."MapData"
      ADD COLUMN purchase_price_tokens NUMERIC(12,2);
  END IF;
END$$;

-- 5c) ClientUploads: tokens charged for this upload (for reporting)
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'public' AND table_name = 'ClientUploads' AND column_name = 'tokens_charged'
  ) THEN
    ALTER TABLE public."ClientUploads"
      ADD COLUMN tokens_charged NUMERIC(12,2);
  END IF;
END$$;

