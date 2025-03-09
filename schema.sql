--
-- PostgreSQL database dump
--

-- Dumped from database version 17.3
-- Dumped by pg_dump version 17.3

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: contributions; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.contributions (
    id integer NOT NULL,
    project_id integer NOT NULL,
    contributor_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone_number character varying(20),
    amount numeric(12,2) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.contributions OWNER TO cieran;

--
-- Name: contributions_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.contributions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contributions_id_seq OWNER TO cieran;

--
-- Name: contributions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.contributions_id_seq OWNED BY public.contributions.id;


--
-- Name: file_versions; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.file_versions (
    id integer NOT NULL,
    file_id integer NOT NULL,
    version_number integer NOT NULL,
    content bytea,
    mime_type character varying(255),
    file_extension character varying(10),
    size bigint,
    commit_message text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.file_versions OWNER TO cieran;

--
-- Name: file_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.file_versions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE file_versions_id_seq OWNER TO cieran;

--
-- Name: file_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.file_versions_id_seq OWNED BY public.file_versions.id;


--
-- Name: files; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.files (
    id integer NOT NULL,
    folder_id integer NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.files OWNER TO cieran;

--
-- Name: files_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.files_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.files_id_seq OWNER TO cieran;

--
-- Name: files_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.files_id_seq OWNED BY public.files.id;


--
-- Name: folders; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.folders (
    id integer NOT NULL,
    project_id integer NOT NULL,
    parent_folder_id integer,
    name character varying(255) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.folders OWNER TO cieran;

--
-- Name: folders_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.folders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.folders_id_seq OWNER TO cieran;

--
-- Name: folders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.folders_id_seq OWNED BY public.folders.id;


--
-- Name: projects; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.projects (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT now(),
    volunteers integer DEFAULT 0
);


ALTER TABLE public.projects OWNER TO cieran;

--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.projects_id_seq OWNER TO cieran;

--
-- Name: projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.projects_id_seq OWNED BY public.projects.id;


--
-- Name: session_tokens; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.session_tokens (
    id integer NOT NULL,
    user_id integer NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    expires_at timestamp with time zone
);


ALTER TABLE public.session_tokens OWNER TO cieran;

--
-- Name: session_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.session_tokens_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.session_tokens_id_seq OWNER TO cieran;

--
-- Name: session_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.session_tokens_id_seq OWNED BY public.session_tokens.id;


--
-- Name: user_projects; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.user_projects (
    user_id integer NOT NULL,
    project_id integer NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_projects OWNER TO cieran;

--
-- Name: users; Type: TABLE; Schema: public; Owner: cieran
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    hashed_password text NOT NULL,
    phone_number character varying(20),
    email character varying(255) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.users OWNER TO cieran;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: cieran
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO cieran;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cieran
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: contributions id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.contributions ALTER COLUMN id SET DEFAULT nextval('public.contributions_id_seq'::regclass);


--
-- Name: file_versions id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.file_versions ALTER COLUMN id SET DEFAULT nextval('public.file_versions_id_seq'::regclass);


--
-- Name: files id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.files ALTER COLUMN id SET DEFAULT nextval('public.files_id_seq'::regclass);


--
-- Name: folders id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.folders ALTER COLUMN id SET DEFAULT nextval('public.folders_id_seq'::regclass);


--
-- Name: projects id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.projects ALTER COLUMN id SET DEFAULT nextval('public.projects_id_seq'::regclass);


--
-- Name: session_tokens id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.session_tokens ALTER COLUMN id SET DEFAULT nextval('public.session_tokens_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: contributions contributions_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_pkey PRIMARY KEY (id);


--
-- Name: file_versions file_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.file_versions
    ADD CONSTRAINT file_versions_pkey PRIMARY KEY (id);


--
-- Name: files files_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- Name: folders folders_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_pkey PRIMARY KEY (id);


--
-- Name: projects projects_name_key; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_name_key UNIQUE (name);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: session_tokens session_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.session_tokens
    ADD CONSTRAINT session_tokens_pkey PRIMARY KEY (id);


--
-- Name: session_tokens session_tokens_token_key; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.session_tokens
    ADD CONSTRAINT session_tokens_token_key UNIQUE (token);


--
-- Name: file_versions unique_file_version; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.file_versions
    ADD CONSTRAINT unique_file_version UNIQUE (file_id, version_number);


--
-- Name: user_projects user_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.user_projects
    ADD CONSTRAINT user_projects_pkey PRIMARY KEY (user_id, project_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: file_versions fk_file; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.file_versions
    ADD CONSTRAINT fk_file FOREIGN KEY (file_id) REFERENCES public.files(id) ON DELETE CASCADE;


--
-- Name: files fk_folder; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT fk_folder FOREIGN KEY (folder_id) REFERENCES public.folders(id) ON DELETE CASCADE;


--
-- Name: folders fk_parent_folder; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT fk_parent_folder FOREIGN KEY (parent_folder_id) REFERENCES public.folders(id) ON DELETE CASCADE;


--
-- Name: folders fk_project; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT fk_project FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: contributions fk_project; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT fk_project FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: user_projects fk_project_followed; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.user_projects
    ADD CONSTRAINT fk_project_followed FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: user_projects fk_user; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.user_projects
    ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: session_tokens fk_user; Type: FK CONSTRAINT; Schema: public; Owner: cieran
--

ALTER TABLE ONLY public.session_tokens
    ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

