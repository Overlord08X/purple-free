--
-- PostgreSQL database dump
--

\restrict offw806dVExQJ6leU8wvI3ZaBf0aqeqA4IQ3h9O8U24YV86AReSPB2ddnNmkmqD

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: generate_guest_name(); Type: FUNCTION; Schema: public; Owner: admin
--

CREATE FUNCTION public.generate_guest_name() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    last_id INT;
BEGIN
    -- Ambil nomor terakhir dari nama Guest
    SELECT COALESCE(MAX(CAST(SUBSTRING(nama FROM 7) AS INT)), 0)
    INTO last_id
    FROM pesanan
    WHERE nama LIKE 'Guest_%';

    -- Generate nama baru
    NEW.nama := 'Guest_' || LPAD((last_id + 1)::TEXT, 6, '0');

    RETURN NEW;
END;
$$;


ALTER FUNCTION public.generate_guest_name() OWNER TO admin;

--
-- Name: generate_idbarang(); Type: FUNCTION; Schema: public; Owner: admin
--

CREATE FUNCTION public.generate_idbarang() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    last_number INTEGER;
BEGIN
    SELECT COALESCE(MAX(CAST(SUBSTRING(idbarang FROM 4) AS INTEGER)), 0)
    INTO last_number
    FROM barang;

    NEW.idbarang := 'BRG' || LPAD((last_number + 1)::TEXT, 5, '0');

    RETURN NEW;
END;
$$;


ALTER FUNCTION public.generate_idbarang() OWNER TO admin;

--
-- Name: generate_idpenjualan(); Type: FUNCTION; Schema: public; Owner: admin
--

CREATE FUNCTION public.generate_idpenjualan() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

    NEW.idpenjualan := nextval('penjualan_seq');

    RETURN NEW;

END;
$$;


ALTER FUNCTION public.generate_idpenjualan() OWNER TO admin;

--
-- Name: generate_idpenjualan_detail(); Type: FUNCTION; Schema: public; Owner: admin
--

CREATE FUNCTION public.generate_idpenjualan_detail() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

    NEW.idpenjualan_detail := nextval('penjualan_detail_seq');

    RETURN NEW;

END;
$$;


ALTER FUNCTION public.generate_idpenjualan_detail() OWNER TO admin;

--
-- Name: hitung_subtotal(); Type: FUNCTION; Schema: public; Owner: admin
--

CREATE FUNCTION public.hitung_subtotal() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.subtotal := NEW.jumlah * NEW.harga;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.hitung_subtotal() OWNER TO admin;

--
-- Name: update_total_pesanan(); Type: FUNCTION; Schema: public; Owner: admin
--

CREATE FUNCTION public.update_total_pesanan() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    target_id INT;
BEGIN
    -- Tentukan idpesanan dari NEW atau OLD
    IF TG_OP = 'DELETE' THEN
        target_id := OLD.idpesanan;
    ELSE
        target_id := NEW.idpesanan;
    END IF;

    UPDATE pesanan
    SET total = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM detail_pesanan
        WHERE idpesanan = target_id
    )
    WHERE idpesanan = target_id;

-- Insert pesanan tanpa nama → auto Guest
INSERT INTO pesanan (total) VALUES (0);

-- Tambah detail → subtotal & total auto
INSERT INTO detail_pesanan (idmenu, idpesanan, jumlah, harga)
VALUES (1, 1, 2, 15000);

-- Cek hasil
SELECT * FROM pesanan;
SELECT * FROM detail_pesanan;

    RETURN NULL;
END;
$$;


ALTER FUNCTION public.update_total_pesanan() OWNER TO admin;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: barang; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.barang (
    idbarang character varying(8) NOT NULL,
    nama_barang character varying(50) NOT NULL,
    harga_barang integer NOT NULL,
    created_at timestamp without time zone NOT NULL
);


ALTER TABLE public.barang OWNER TO admin;

--
-- Name: bukus; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bukus (
    idbuku bigint NOT NULL,
    idkategori bigint NOT NULL,
    kode character varying(20) NOT NULL,
    judul character varying(500) NOT NULL,
    pengarang character varying(200) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bukus OWNER TO postgres;

--
-- Name: bukus_idbuku_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bukus_idbuku_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bukus_idbuku_seq OWNER TO postgres;

--
-- Name: bukus_idbuku_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bukus_idbuku_seq OWNED BY public.bukus.idbuku;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: customers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    nama character varying(255) NOT NULL,
    foto_blob text,
    foto_path character varying(255),
    qr_code_path character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.customers OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.customers_id_seq OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- Name: detail_pesanan; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.detail_pesanan (
    iddetail_pesanan integer NOT NULL,
    idmenu integer NOT NULL,
    idpesanan integer NOT NULL,
    jumlah integer NOT NULL,
    harga integer NOT NULL,
    subtotal integer NOT NULL,
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    catatan character varying(255)
);


ALTER TABLE public.detail_pesanan OWNER TO admin;

--
-- Name: detail_pesanan_iddetail_pesanan_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.detail_pesanan_iddetail_pesanan_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.detail_pesanan_iddetail_pesanan_seq OWNER TO admin;

--
-- Name: detail_pesanan_iddetail_pesanan_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE public.detail_pesanan_iddetail_pesanan_seq OWNED BY public.detail_pesanan.iddetail_pesanan;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: kategoris; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.kategoris (
    idkategori bigint NOT NULL,
    nama_kategori character varying(100) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.kategoris OWNER TO postgres;

--
-- Name: kategoris_idkategori_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.kategoris_idkategori_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.kategoris_idkategori_seq OWNER TO postgres;

--
-- Name: kategoris_idkategori_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kategoris_idkategori_seq OWNED BY public.kategoris.idkategori;


--
-- Name: menu; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.menu (
    idmenu integer NOT NULL,
    nama_menu character varying(255) NOT NULL,
    harga integer NOT NULL,
    path_gambar character varying(255),
    idvendor integer NOT NULL
);


ALTER TABLE public.menu OWNER TO admin;

--
-- Name: menu_idmenu_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.menu_idmenu_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.menu_idmenu_seq OWNER TO admin;

--
-- Name: menu_idmenu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE public.menu_idmenu_seq OWNED BY public.menu.idmenu;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: penjualan; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.penjualan (
    idpenjualan integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    total integer NOT NULL,
    order_id character varying(255),
    transaction_id character varying(255),
    payment_type character varying(255),
    payment_details json,
    status_bayar smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE public.penjualan OWNER TO admin;

--
-- Name: penjualan_detail; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.penjualan_detail (
    idpenjualan_detail integer NOT NULL,
    idpenjualan integer NOT NULL,
    idbarang character varying(8) NOT NULL,
    jumlah integer NOT NULL,
    subtotal integer NOT NULL
);


ALTER TABLE public.penjualan_detail OWNER TO admin;

--
-- Name: penjualan_detail_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.penjualan_detail_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.penjualan_detail_seq OWNER TO admin;

--
-- Name: penjualan_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.penjualan_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.penjualan_seq OWNER TO admin;

--
-- Name: pesanan; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.pesanan (
    idpesanan integer NOT NULL,
    nama character varying(255) NOT NULL,
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    total integer NOT NULL,
    metode_bayar integer,
    status_bayar smallint DEFAULT 0,
    transaction_id character varying(255),
    payment_type character varying(255),
    order_id character varying(255),
    payment_details json,
    qr_code text
);


ALTER TABLE public.pesanan OWNER TO admin;

--
-- Name: pesanan_idpesanan_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.pesanan_idpesanan_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pesanan_idpesanan_seq OWNER TO admin;

--
-- Name: pesanan_idpesanan_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE public.pesanan_idpesanan_seq OWNED BY public.pesanan.idpesanan;


--
-- Name: reg_districts; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.reg_districts (
    id character(6) NOT NULL,
    regency_id character(4) NOT NULL,
    name character varying(255) NOT NULL
);


ALTER TABLE public.reg_districts OWNER TO admin;

--
-- Name: reg_provinces; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.reg_provinces (
    id character(2) NOT NULL,
    name character varying(255) NOT NULL
);


ALTER TABLE public.reg_provinces OWNER TO admin;

--
-- Name: reg_regencies; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.reg_regencies (
    id character(4) NOT NULL,
    province_id character(2) NOT NULL,
    name character varying(255) NOT NULL
);


ALTER TABLE public.reg_regencies OWNER TO admin;

--
-- Name: reg_villages; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.reg_villages (
    id character(10) NOT NULL,
    district_id character(6) NOT NULL,
    name character varying(255) NOT NULL
);


ALTER TABLE public.reg_villages OWNER TO admin;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    id_google character varying(256),
    otp character varying(6),
    role character varying(255) DEFAULT 'customer'::character varying NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vendor; Type: TABLE; Schema: public; Owner: admin
--

CREATE TABLE public.vendor (
    idvendor integer NOT NULL,
    nama_vendor character varying(255) NOT NULL
);


ALTER TABLE public.vendor OWNER TO admin;

--
-- Name: vendor_idvendor_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE public.vendor_idvendor_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vendor_idvendor_seq OWNER TO admin;

--
-- Name: vendor_idvendor_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE public.vendor_idvendor_seq OWNED BY public.vendor.idvendor;


--
-- Name: bukus idbuku; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bukus ALTER COLUMN idbuku SET DEFAULT nextval('public.bukus_idbuku_seq'::regclass);


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: detail_pesanan iddetail_pesanan; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.detail_pesanan ALTER COLUMN iddetail_pesanan SET DEFAULT nextval('public.detail_pesanan_iddetail_pesanan_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: kategoris idkategori; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategoris ALTER COLUMN idkategori SET DEFAULT nextval('public.kategoris_idkategori_seq'::regclass);


--
-- Name: menu idmenu; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.menu ALTER COLUMN idmenu SET DEFAULT nextval('public.menu_idmenu_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: pesanan idpesanan; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.pesanan ALTER COLUMN idpesanan SET DEFAULT nextval('public.pesanan_idpesanan_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vendor idvendor; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.vendor ALTER COLUMN idvendor SET DEFAULT nextval('public.vendor_idvendor_seq'::regclass);


--
-- Name: bukus bukus_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bukus
    ADD CONSTRAINT bukus_pkey PRIMARY KEY (idbuku);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: detail_pesanan detail_pesanan_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.detail_pesanan
    ADD CONSTRAINT detail_pesanan_pkey PRIMARY KEY (iddetail_pesanan);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: kategoris kategoris_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategoris
    ADD CONSTRAINT kategoris_pkey PRIMARY KEY (idkategori);


--
-- Name: menu menu_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.menu
    ADD CONSTRAINT menu_pkey PRIMARY KEY (idmenu);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: pesanan pesanan_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.pesanan
    ADD CONSTRAINT pesanan_pkey PRIMARY KEY (idpesanan);


--
-- Name: barang pk_barang; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.barang
    ADD CONSTRAINT pk_barang PRIMARY KEY (idbarang);


--
-- Name: penjualan pk_penjualan; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.penjualan
    ADD CONSTRAINT pk_penjualan PRIMARY KEY (idpenjualan);


--
-- Name: penjualan_detail pk_penjualan_detail; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.penjualan_detail
    ADD CONSTRAINT pk_penjualan_detail PRIMARY KEY (idpenjualan_detail);


--
-- Name: reg_districts reg_districts_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_districts
    ADD CONSTRAINT reg_districts_pkey PRIMARY KEY (id);


--
-- Name: reg_provinces reg_provinces_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_provinces
    ADD CONSTRAINT reg_provinces_pkey PRIMARY KEY (id);


--
-- Name: reg_regencies reg_regencies_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_regencies
    ADD CONSTRAINT reg_regencies_pkey PRIMARY KEY (id);


--
-- Name: reg_villages reg_villages_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_villages
    ADD CONSTRAINT reg_villages_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vendor vendor_pkey; Type: CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.vendor
    ADD CONSTRAINT vendor_pkey PRIMARY KEY (idvendor);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: idx_detail_pesanan; Type: INDEX; Schema: public; Owner: admin
--

CREATE INDEX idx_detail_pesanan ON public.detail_pesanan USING btree (idpesanan);


--
-- Name: idx_menu_vendor; Type: INDEX; Schema: public; Owner: admin
--

CREATE INDEX idx_menu_vendor ON public.menu USING btree (idvendor);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: pesanan trg_generate_guest; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_generate_guest BEFORE INSERT ON public.pesanan FOR EACH ROW WHEN (((new.nama IS NULL) OR ((new.nama)::text = ''::text))) EXECUTE FUNCTION public.generate_guest_name();


--
-- Name: barang trg_generate_idbarang; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_generate_idbarang BEFORE INSERT ON public.barang FOR EACH ROW EXECUTE FUNCTION public.generate_idbarang();


--
-- Name: penjualan trg_generate_idpenjualan; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_generate_idpenjualan BEFORE INSERT ON public.penjualan FOR EACH ROW EXECUTE FUNCTION public.generate_idpenjualan();


--
-- Name: penjualan_detail trg_generate_idpenjualan_detail; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_generate_idpenjualan_detail BEFORE INSERT ON public.penjualan_detail FOR EACH ROW EXECUTE FUNCTION public.generate_idpenjualan_detail();


--
-- Name: detail_pesanan trg_hitung_subtotal; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_hitung_subtotal BEFORE INSERT OR UPDATE ON public.detail_pesanan FOR EACH ROW EXECUTE FUNCTION public.hitung_subtotal();


--
-- Name: detail_pesanan trg_total_after_delete; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_total_after_delete AFTER DELETE ON public.detail_pesanan FOR EACH ROW EXECUTE FUNCTION public.update_total_pesanan();


--
-- Name: detail_pesanan trg_total_after_insert; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_total_after_insert AFTER INSERT ON public.detail_pesanan FOR EACH ROW EXECUTE FUNCTION public.update_total_pesanan();


--
-- Name: detail_pesanan trg_total_after_update; Type: TRIGGER; Schema: public; Owner: admin
--

CREATE TRIGGER trg_total_after_update AFTER UPDATE ON public.detail_pesanan FOR EACH ROW EXECUTE FUNCTION public.update_total_pesanan();


--
-- Name: bukus bukus_idkategori_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bukus
    ADD CONSTRAINT bukus_idkategori_foreign FOREIGN KEY (idkategori) REFERENCES public.kategoris(idkategori) ON DELETE CASCADE;


--
-- Name: detail_pesanan fk_detail_menu; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.detail_pesanan
    ADD CONSTRAINT fk_detail_menu FOREIGN KEY (idmenu) REFERENCES public.menu(idmenu) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: detail_pesanan fk_detail_pesanan; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.detail_pesanan
    ADD CONSTRAINT fk_detail_pesanan FOREIGN KEY (idpesanan) REFERENCES public.pesanan(idpesanan) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: reg_villages fk_district; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_villages
    ADD CONSTRAINT fk_district FOREIGN KEY (district_id) REFERENCES public.reg_districts(id);


--
-- Name: menu fk_menu_vendor; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.menu
    ADD CONSTRAINT fk_menu_vendor FOREIGN KEY (idvendor) REFERENCES public.vendor(idvendor) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: penjualan_detail fk_penjualan_detail_barang; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.penjualan_detail
    ADD CONSTRAINT fk_penjualan_detail_barang FOREIGN KEY (idbarang) REFERENCES public.barang(idbarang);


--
-- Name: penjualan_detail fk_penjualan_detail_penjualan; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.penjualan_detail
    ADD CONSTRAINT fk_penjualan_detail_penjualan FOREIGN KEY (idpenjualan) REFERENCES public.penjualan(idpenjualan);


--
-- Name: reg_regencies fk_province; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_regencies
    ADD CONSTRAINT fk_province FOREIGN KEY (province_id) REFERENCES public.reg_provinces(id);


--
-- Name: reg_districts fk_regency; Type: FK CONSTRAINT; Schema: public; Owner: admin
--

ALTER TABLE ONLY public.reg_districts
    ADD CONSTRAINT fk_regency FOREIGN KEY (regency_id) REFERENCES public.reg_regencies(id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO admin;


--
-- Name: TABLE bukus; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.bukus TO admin;


--
-- Name: SEQUENCE bukus_idbuku_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.bukus_idbuku_seq TO admin;


--
-- Name: TABLE cache; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.cache TO admin;


--
-- Name: TABLE cache_locks; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.cache_locks TO admin;


--
-- Name: TABLE failed_jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.failed_jobs TO admin;


--
-- Name: SEQUENCE failed_jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.failed_jobs_id_seq TO admin;


--
-- Name: TABLE job_batches; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.job_batches TO admin;


--
-- Name: TABLE jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.jobs TO admin;


--
-- Name: SEQUENCE jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.jobs_id_seq TO admin;


--
-- Name: TABLE kategoris; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.kategoris TO admin;


--
-- Name: SEQUENCE kategoris_idkategori_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.kategoris_idkategori_seq TO admin;


--
-- Name: TABLE migrations; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.migrations TO admin;


--
-- Name: SEQUENCE migrations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.migrations_id_seq TO admin;


--
-- Name: TABLE password_reset_tokens; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.password_reset_tokens TO admin;


--
-- Name: TABLE sessions; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.sessions TO admin;


--
-- Name: TABLE users; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.users TO admin;


--
-- Name: SEQUENCE users_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.users_id_seq TO admin;


--
-- PostgreSQL database dump complete
--

\unrestrict offw806dVExQJ6leU8wvI3ZaBf0aqeqA4IQ3h9O8U24YV86AReSPB2ddnNmkmqD

